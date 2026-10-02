<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Models\PageBlock;
use App\Services\ImageService;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    protected array $translatable = ['title', 'slug', 'content', 'meta_title', 'meta_description'];

    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::query()->withCount('blocks')->orderBy('sort')->get()]);
    }

    public function create()
    {
        return $this->form(new Page(['is_active' => true, 'template' => 'default']));
    }

    public function edit(Page $page)
    {
        return $this->form($page->load('blocks'));
    }

    protected function form(Page $page)
    {
        return view('admin.pages.form', [
            'page' => $page,
            'categories' => CategoryController::options(),
        ]);
    }

    public function store(Request $request)
    {
        $page = new Page;
        $this->save($request, $page);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Səhifə yaradıldı.');
    }

    public function update(Request $request, Page $page)
    {
        $this->save($request, $page);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, Page $page): void
    {
        $request->validate($this->translationRules($this->translatable, ['title']) + [
            'template' => ['required', Rule::in(array_keys(Page::TEMPLATES))],
            'blocks' => 'array',
            'blocks.*.type' => ['required', Rule::in(array_keys(PageBlock::TYPES))],
        ]);

        DB::transaction(function () use ($request, $page) {
            $page->fill($this->transMany($request, $this->translatable) + [
                'template' => $request->input('template'),
                'is_active' => $request->boolean('is_active'),
                'sort' => $request->integer('sort'),
            ])->save();

            $page->blocks()->delete();
            foreach (array_values((array) $request->input('blocks', [])) as $i => $block) {
                $key = array_keys((array) $request->input('blocks'))[$i];
                $page->blocks()->create([
                    'type' => $block['type'],
                    'data' => $this->blockData($request, $block, "blocks.{$key}"),
                    'sort' => $i,
                ]);
            }
        });
    }

    /** Blok tipinə görə data JSON-u (şəkil yükləmələri daxil) */
    protected function blockData(Request $request, array $block, string $path): array
    {
        $data = (array) ($block['data'] ?? []);
        $images = app(ImageService::class);
        $clean = fn ($arr) => array_filter((array) $arr, fn ($v) => trim((string) $v) !== '');

        switch ($block['type']) {
            case 'image':
                if ($request->hasFile("{$path}.image_file")) {
                    $data['image'] = $images->store($request->file("{$path}.image_file"), 'pages');
                }
                $data['alt'] = $clean($data['alt'] ?? []);
                break;

            case 'banner':
                $items = [];
                foreach (array_keys((array) ($data['items'] ?? [])) as $j) {
                    $item = $data['items'][$j];
                    if ($request->hasFile("{$path}.item_files.{$j}")) {
                        $item['image'] = $images->store($request->file("{$path}.item_files.{$j}"), 'pages');
                    }
                    if (! empty($item['image']) && empty($item['remove'])) {
                        $items[] = ['image' => $item['image'], 'link' => $item['link'] ?? null];
                    }
                }
                foreach ((array) $request->file("{$path}.new_files", []) as $file) {
                    $items[] = ['image' => $images->store($file, 'pages'), 'link' => null];
                }
                $data['items'] = $items;
                $data['title'] = $clean($data['title'] ?? []);
                break;

            case 'faq':
                // Hər dil üçün sətirlər: "Sual :: Cavab"
                $items = [];
                foreach (Locales::codes() as $locale) {
                    $lines = preg_split('/\r?\n/', (string) ($data['raw'][$locale] ?? ''));
                    $n = 0;
                    foreach ($lines as $line) {
                        if (! str_contains($line, '::')) {
                            continue;
                        }
                        [$q, $a] = array_map('trim', explode('::', $line, 2));
                        $items[$n]['q'][$locale] = $q;
                        $items[$n]['a'][$locale] = $a;
                        $n++;
                    }
                }
                $data = ['title' => $clean($data['title'] ?? []), 'items' => array_values($items)];
                break;

            case 'products':
                $data['ids'] = array_values(array_map('intval', (array) ($data['ids'] ?? [])));
                $data['limit'] = max(1, (int) ($data['limit'] ?? 10));
                $data['title'] = $clean($data['title'] ?? []);
                break;

            case 'text':
                $data['content'] = $clean($data['content'] ?? []);
                break;

            case 'form':
                $data['title'] = $clean($data['title'] ?? []);
                break;
        }

        return $data;
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Səhifə silindi.');
    }
}
