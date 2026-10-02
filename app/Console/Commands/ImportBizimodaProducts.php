<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSetItem;
use App\Services\SetPricingService;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Köhnə saytdan (bizimoda.az, OpenCart + Journal) məhsulları oxuyub import edir.
 *
 * Kateqoriyalar artıq eyni slug-larla mövcuddur: hər kateqoriyanın siyahı səhifəsi
 * oxunur, məhsul səhifələrindən ad/qiymət/stok/şəkil/ölçülər/xüsusiyyətlər (az + ru)
 * və dəstlərin modul cədvəli götürülür. Məhsullar external_id (OpenCart product_id)
 * ilə tanınır, ona görə əmr təkrar işlədilə bilər.
 */
class ImportBizimodaProducts extends Command
{
    protected $signature = 'bizimoda:import-products
        {--limit=0 : Yalnız ilk N məhsulu import et (test üçün)}
        {--refresh : Keşlənmiş HTML-i nəzərə alma, səhifələri yenidən yüklə}
        {--no-images : Şəkilləri yükləmə}
        {--refresh-images : Mövcud məhsulların şəkillərini yenidən yüklə}
        {--purge-demo : external_id olmayan (demo) məhsulları sil}
        {--delay=150 : Sorğular arası gözləmə (ms)}';

    protected $description = 'bizimoda.az saytından məhsulları import edir';

    protected const BASE = 'https://bizimoda.az/';

    /** Aksiya/seçmə kateqoriyaları — əsas kateqoriya kimi seçilmir */
    protected const PROMO_SLUGS = ['aktiv-kampaniyalar', 'heftenin-teklifi', '2026-kolleksiyasi'];

    protected array $imageCache = [];

    public function handle(): int
    {
        if ($this->option('purge-demo')) {
            $demo = Product::withTrashed()->whereNull('external_id')->get();
            $demo->each(fn (Product $p) => $p->forceDelete());
            $this->info("Demo məhsullar silindi: {$demo->count()}");
        }

        // 1) Kateqoriya siyahıları → məhsul URL-ləri və onların kateqoriyaları
        $categories = Category::query()->withDepth()->get();
        $listed = []; // məhsul slug-ı => [category_id, ...]
        $links = [];  // məhsul slug-ı => URL (eyni məhsul müxtəlif kateqoriya yolları ilə gəlir)

        $this->info('Kateqoriyalar oxunur...');
        $bar = $this->output->createProgressBar($categories->count());
        foreach ($categories as $category) {
            $slug = $category->getTranslation('slug', 'az');
            $html = $this->fetch(self::BASE.$slug.'?limit=1000');
            if ($html) {
                $xp = $this->xpath($html);
                foreach ($xp->query('//div[contains(@class,"main-products")]//div[contains(@class,"product-thumb")]//div[@class="name"]/a') as $a) {
                    $url = strtok($a->getAttribute('href'), '?');
                    $key = Str::afterLast(rtrim($url, '/'), '/');
                    $links[$key] ??= $url;
                    $listed[$key][] = $category->id;
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('Siyahılarda tapılan məhsullar: '.count($listed));

        // 2) Məhsul səhifələri (az) — OpenCart id ilə təkrarlar birləşdirilir
        $items = []; // ocId => data
        $urls = $links;
        if ($limit = (int) $this->option('limit')) {
            $urls = array_slice($urls, 0, $limit);
        }

        $this->info('Məhsul səhifələri oxunur...');
        $bar = $this->output->createProgressBar(count($urls));
        foreach ($urls as $key => $url) {
            $data = $this->parseProduct($url);
            if ($data) {
                $id = $data['id'];
                $items[$id] ??= $data + ['categories' => [], 'listed' => true];
                $items[$id]['categories'] = array_values(array_unique([...$items[$id]['categories'], ...$listed[$key]]));
            } else {
                $this->warn(" Oxunmadı: {$url}");
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        // 3) Yalnız dəst daxilində görünən modullar (siyahılarda olmayanlar)
        foreach ($items as $set) {
            foreach ($set['modules'] as $module) {
                if (isset($items[$module['id']]) || $module['id'] === $set['id']) {
                    continue;
                }
                $data = $this->parseProduct(self::BASE.'index.php?route=product/product&product_id='.$module['id']);
                if ($data) {
                    $items[$data['id']] = $data + ['categories' => $set['categories'], 'listed' => false];
                }
            }
        }

        // Tip: modul cədvəlində başqa məhsullar varsa dəst; dəstdə iştirak edirsə modul
        $componentIds = [];
        foreach ($items as $id => $item) {
            $modules = array_filter($item['modules'], fn ($m) => $m['id'] !== $id);
            $items[$id]['is_set'] = (bool) $modules;
            foreach ($modules as $m) {
                $componentIds[$m['id']] = true;
            }
        }

        // 4) Bazaya yazılış
        $this->info('Bazaya yazılır ('.count($items).' məhsul)...');
        $depth = $categories->pluck('depth', 'id');
        $promoIds = $categories->filter(fn ($c) => in_array($c->getTranslation('slug', 'az'), self::PROMO_SLUGS))->pluck('id')->all();
        $map = []; // ocId => local Product
        $stats = ['created' => 0, 'updated' => 0, 'images' => 0];

        $bar = $this->output->createProgressBar(count($items));
        foreach ($items as $id => $item) {
            try {
                DB::transaction(function () use ($id, $item, $componentIds, $depth, $promoIds, &$map, &$stats) {
                    $product = Product::withTrashed()->firstWhere('external_id', $id) ?? new Product(['external_id' => $id]);
                    $isNew = ! $product->exists;

                    $type = $item['is_set'] ? 'set' : (isset($componentIds[$id]) ? 'module' : 'simple');
                    $categoryIds = $item['categories'];
                    $mainCandidates = array_diff($categoryIds, $promoIds) ?: $categoryIds;
                    usort($mainCandidates, fn ($a, $b) => ($depth[$b] ?? 0) <=> ($depth[$a] ?? 0));

                    $product->fill([
                        'type' => $type,
                        'sku' => $product->sku ?: 'BO-'.$id,
                        'name' => $this->translations($item, 'name'),
                        'description' => $this->translations($item, 'description'),
                        'dimensions' => $this->translations($item, 'dimensions'),
                        'meta_title' => $this->translations($item, 'meta_title'),
                        'meta_description' => $this->translations($item, 'meta_description'),
                        'label' => $item['label'] ? ['az' => $item['label'], 'ru' => $item['label'], 'en' => $item['label']] : null,
                        'main_category_id' => $mainCandidates[0],
                        'price' => $item['price'],
                        'old_price' => $item['old_price'],
                        'stock_status' => $item['stock_status'],
                        'is_active' => true,
                        'sold_separately' => $type !== 'module' || $item['listed'],
                    ]);
                    if ($isNew) {
                        // Slug köhnə saytdakı ilə eyni qalsın (SEO)
                        $product->setTranslations('slug', ['az' => $item['slug'], 'ru' => $item['slug'], 'en' => $item['slug']]);
                    }
                    if ($product->trashed()) {
                        $product->deleted_at = null;
                    }
                    $product->save();
                    $product->categories()->sync($categoryIds);

                    if (! $this->option('no-images') && ($isNew || $this->option('refresh-images') || ! $product->images()->exists())) {
                        $paths = array_values(array_filter(array_map(fn ($u) => $this->downloadImage($u), $item['images'])));
                        $product->images()->delete();
                        foreach ($paths as $i => $path) {
                            $product->images()->create(['path' => $path, 'sort' => $i]);
                        }
                        $stats['images'] += count($paths);
                    }

                    $map[$id] = $product;
                    $stats[$isNew ? 'created' : 'updated']++;
                });
            } catch (Throwable $e) {
                $this->newLine();
                $this->error("#{$id} {$item['name']['az']}: ".$e->getMessage());
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        // 5) Dəst → modul əlaqələri və dəstlərin hesablanmış qiyməti
        $pricing = app(SetPricingService::class);
        foreach ($items as $id => $item) {
            if (! $item['is_set'] || ! isset($map[$id])) {
                continue;
            }
            $set = $map[$id];
            $set->setItems()->delete();
            $sort = 0;
            foreach ($item['modules'] as $m) {
                if ($m['id'] === $id || ! isset($map[$m['id']])) {
                    continue;
                }
                $component = $map[$m['id']];
                ProductSetItem::query()->create([
                    'set_id' => $set->id,
                    'component_id' => $component->id,
                    'default_qty' => max(0, $m['qty']),
                    'min_qty' => max(1, $m['min_qty']),
                    'is_required' => false,
                    // Dəst daxilindəki qiymət modulun öz qiymətindən fərqlənirsə saxlanılır
                    'price_override' => abs($m['price'] - (float) $component->price) > 0.009 ? $m['price'] : null,
                    'old_price_override' => abs($m['price'] - (float) $component->price) > 0.009 && $m['old_price'] > $m['price'] ? $m['old_price'] : null,
                    'sort' => $sort++,
                ]);
            }
            $pricing->refreshComputed($set->fresh());
        }

        $this->info("Hazırdır. Yeni: {$stats['created']}, yenilənən: {$stats['updated']}, şəkil: {$stats['images']}");

        return self::SUCCESS;
    }

    // ---- Məhsul səhifəsinin təhlili ----

    protected function parseProduct(string $url): ?array
    {
        $html = $this->fetch($url);
        if (! $html || ! preg_match('/product\/product\/review&(?:amp;)?product_id=(\d+)/', $html, $m)) {
            return null;
        }
        $id = (int) $m[1];
        $xp = $this->xpath($html);

        $ld = $this->productJsonLd($html);
        $name = $this->text($xp, '//h1[contains(@class,"page-title")]/span') ?: ($ld['name'] ?? '');
        $canonical = $this->attr($xp, '//meta[@property="og:url"]', 'content') ?: $url;

        $price = isset($ld['offers']['price']) ? (float) $ld['offers']['price'] : $this->money($this->text($xp, '//*[contains(@class,"product-price-new") or contains(@class,"product-price ")]'));
        $old = $this->money($this->text($xp, '//div[contains(@class,"product-price-old")]'));

        $stockNode = $xp->query('//li[contains(@class,"product-stock")]')->item(0);
        $stockText = $stockNode ? mb_strtolower($stockNode->textContent) : '';
        $stock = match (true) {
            ! $stockNode, str_contains($stockNode->getAttribute('class'), 'in-stock') => 'in_stock',
            str_contains($stockText, 'sifariş') => 'preorder',
            default => 'out_of_stock',
        };

        $label = null;
        foreach ($xp->query('//div[contains(@class,"product-left")]//span[contains(@class,"product-label")]/b') as $b) {
            $t = trim($b->textContent);
            if ($t !== '' && ! str_contains($t, '%')) {
                $label = $t;
                break;
            }
        }

        $images = [];
        $gallery = $this->attr($xp, '//div[contains(@class,"lightgallery-product-images")]', 'data-images');
        foreach (json_decode($gallery ?: '[]', true) ?: [] as $img) {
            if (! empty($img['src'])) {
                $images[] = $img['src'];
            }
        }
        if (! $images && ($og = $this->attr($xp, '//meta[@property="og:image"]', 'content'))) {
            $images[] = $og;
        }

        $modules = [];
        foreach ($xp->query('//table[contains(@class,"product-module-table")]//tr[contains(@class,"pr-wrapper")]') as $tr) {
            /** @var DOMElement $tr */
            $input = $xp->query('.//input[contains(@class,"count-input")]', $tr)->item(0);
            $cells = $xp->query('./td', $tr);
            $modules[] = [
                'id' => (int) preg_replace('/\D/', '', $tr->getAttribute('id')),
                'name' => $cells->length > 1 ? trim($cells->item(1)->textContent) : '',
                'price' => (float) $tr->getAttribute('data-module-price'),
                'old_price' => (float) $tr->getAttribute('data-module-old-price'),
                'qty' => $input ? (int) $input->getAttribute('value') : 1,
                'min_qty' => $input ? (int) $input->getAttribute('data-min_qty') : 1,
            ];
        }

        $data = [
            'id' => $id,
            'slug' => Str::afterLast(rtrim(parse_url($canonical, PHP_URL_PATH) ?? '', '/'), '/') ?: Str::slug($name),
            'name' => ['az' => $name],
            'price' => $price,
            'old_price' => $old && $old > $price ? $old : null,
            'stock_status' => $stock,
            'label' => $label,
            'images' => array_values(array_unique($images)),
            'modules' => $modules,
        ] + $this->localizedFields($xp, 'az');

        // Rus dili: OpenCart dili cookie ilə seçir
        $ruHtml = $this->fetch(self::BASE.'index.php?route=product/product&product_id='.$id, ['language' => 'ru-ru']);
        if ($ruHtml) {
            $ruXp = $this->xpath($ruHtml);
            $data['name']['ru'] = $this->text($ruXp, '//h1[contains(@class,"page-title")]/span') ?: $name;
            foreach ($this->localizedFields($ruXp, 'ru') as $field => $values) {
                $data[$field] = ($data[$field] ?? []) + $values;
            }
        }

        return $data;
    }

    /** Ölçülər / Xüsusiyyətlər tabları və meta sahələri */
    protected function localizedFields(DOMXPath $xp, string $locale): array
    {
        $out = ['dimensions' => [], 'description' => [], 'meta_title' => [], 'meta_description' => []];

        foreach ($xp->query('//div[contains(@class,"product_tabs")]/ul[contains(@class,"nav-tabs")]/li/a') as $a) {
            $paneId = ltrim($a->getAttribute('href'), '#');
            $pane = $xp->query('//div[@id="'.$paneId.'"]')->item(0);
            if (! $pane) {
                continue;
            }
            $content = $xp->query('.//div[contains(@class,"block-content")]', $pane)->item(0) ?? $pane;
            $html = $this->cleanHtml($content);
            if ($html === '') {
                continue;
            }
            // Birinci (məhsulun əsas təsviri) tab "Ölçülər", custom_tab isə "Xüsusiyyətlər"
            $field = $paneId === 'custom_tab' ? 'description' : 'dimensions';
            $out[$field][$locale] = isset($out[$field][$locale]) ? $out[$field][$locale].$html : $html;
        }

        if ($t = $this->text($xp, '//title')) {
            $out['meta_title'][$locale] = $t;
        }
        if ($d = $this->attr($xp, '//meta[@name="description"]', 'content')) {
            $out['meta_description'][$locale] = trim($d);
        }

        return $out;
    }

    /** Daxili HTML: style/class atributları və boş teqlər təmizlənir */
    protected function cleanHtml(DOMNode $node): string
    {
        $allowed = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'span', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'h3', 'h4', 'h5', 'a'];
        $doc = $node->ownerDocument;
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $doc->saveHTML($child);
        }
        $html = strip_tags($html, '<'.implode('><', $allowed).'>');
        $html = preg_replace('/\s(style|class|id|data-[\w-]+|dir|lang)="[^"]*"/i', '', $html);
        $html = preg_replace('/<span>(.*?)<\/span>/s', '$1', $html);
        $html = preg_replace('/(?:\s|&nbsp;|\x{A0}){2,}/u', ' ', $html);
        $html = preg_replace('/<p>(?:\s|&nbsp;|\x{A0}|<br>)*<\/p>/u', '', $html);

        return trim($html);
    }

    protected function translations(array $item, string $field): ?array
    {
        $values = array_filter($item[$field] ?? [], fn ($v) => $v !== null && $v !== '');

        return $values ?: null;
    }

    protected function productJsonLd(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);
        foreach ($m[1] as $json) {
            $data = json_decode($json, true);
            if (($data['@type'] ?? null) === 'Product') {
                return $data;
            }
        }

        return [];
    }

    protected function money(?string $text): ?float
    {
        if (! $text) {
            return null;
        }
        $n = preg_replace('/[^\d.,]/', '', $text);
        $n = str_replace(',', '.', $n);

        return $n === '' ? null : (float) $n;
    }

    // ---- Şəkillər ----

    protected function downloadImage(string $url): ?string
    {
        if (isset($this->imageCache[$url])) {
            return $this->imageCache[$url];
        }

        // Keş ölçüsündən (…-1000x1000.jpg) orijinal fayla keçid
        $original = preg_replace('#/image/cache/#', '/image/', $url);
        $original = preg_replace('/-\d+x\d+[a-z]?(\.\w+)$/i', '$1', $original);

        $relative = rawurldecode(Str::after(parse_url($original, PHP_URL_PATH), '/image/catalog/'));
        $ext = strtolower(pathinfo($relative, PATHINFO_EXTENSION) ?: 'jpg');
        $name = Str::slug(Str::beforeLast($relative, '.'));
        $path = 'uploads/products/'.($name ?: md5($original)).'.'.$ext;

        if (Storage::disk('public')->exists($path)) {
            return $this->imageCache[$url] = $path;
        }

        foreach ([$original, $url] as $source) {
            try {
                $response = Http::withUserAgent('Mozilla/5.0 (bizimoda importer)')->timeout(60)->retry(2, 1000, throw: false)->get($source);
                if ($response->successful() && str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                    Storage::disk('public')->put($path, $response->body());

                    return $this->imageCache[$url] = $path;
                }
            } catch (Throwable) {
                // növbəti mənbəyə keç
            }
        }
        $this->warn(" Şəkil yüklənmədi: {$url}");

        return $this->imageCache[$url] = null;
    }

    // ---- HTTP / DOM ----

    protected function fetch(string $url, array $cookies = []): ?string
    {
        $cacheFile = storage_path('app/bizimoda-import/'.md5($url.json_encode($cookies)).'.html');
        if (! $this->option('refresh') && is_file($cacheFile)) {
            return File::get($cacheFile);
        }

        try {
            usleep((int) $this->option('delay') * 1000);
            $request = Http::withUserAgent('Mozilla/5.0 (bizimoda importer)')->timeout(60)->retry(3, 1500, throw: false);
            if ($cookies) {
                $request = $request->withCookies($cookies, 'bizimoda.az');
            }
            $response = $request->get($url);
            if (! $response->successful()) {
                return null;
            }
            File::ensureDirectoryExists(dirname($cacheFile));
            File::put($cacheFile, $response->body());

            return $response->body();
        } catch (Throwable $e) {
            $this->warn(" {$url}: ".$e->getMessage());

            return null;
        }
    }

    protected function xpath(string $html): DOMXPath
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        return new DOMXPath($doc);
    }

    protected function text(DOMXPath $xp, string $query): ?string
    {
        $node = $xp->query($query)->item(0);
        $text = $node ? trim(preg_replace('/\s+/u', ' ', $node->textContent)) : '';

        return $text === '' ? null : $text;
    }

    protected function attr(DOMXPath $xp, string $query, string $attr): ?string
    {
        $node = $xp->query($query)->item(0);

        return $node instanceof DOMElement ? $node->getAttribute($attr) : null;
    }
}
