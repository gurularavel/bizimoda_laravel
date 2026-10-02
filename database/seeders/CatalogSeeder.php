<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\CategoryFilter;
use App\Models\Option;
use App\Models\Product;
use App\Models\ProductOption;
use App\Services\SetPricingService;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /** Kateqoriya adlarının ru/en tərcümələri (qalanları admin paneldən tərcümə olunur) */
    protected array $dict = [
        'Yataq otağı' => ['Спальня', 'Bedroom'],
        'İstirahət otağı' => ['Гостиная', 'Living room'],
        'Qonaq otağı' => ['Столовая', 'Dining room'],
        'Mətbəx/Yemək otağı' => ['Кухня', 'Kitchen'],
        'Uşaq otağı' => ['Детская', 'Kids room'],
        'Evdə ofis' => ['Домашний офис', 'Home office'],
        'Tək-tək mebellər' => ['Отдельная мебель', 'Single furniture'],
        'Ev tekstili' => ['Домашний текстиль', 'Home textile'],
        'Ev tekstilləri' => ['Домашний текстиль', 'Home textiles'],
        'Yataq mebelləri' => ['Мебель для спальни', 'Bedroom furniture'],
        'Dekorlar' => ['Декор', 'Decor'],
        'Mebellər' => ['Мебель', 'Furniture'],
        'Yumşaq mebellər' => ['Мягкая мебель', 'Upholstered furniture'],
        'Ofis mebelləri' => ['Офисная мебель', 'Office furniture'],
        'Yataq dəstləri' => ['Спальные гарнитуры', 'Bedroom sets'],
        'Çarpayılar' => ['Кровати', 'Beds'],
        'Dolablar' => ['Шкафы', 'Wardrobes'],
        'Trümolar' => ['Комоды с зеркалом', 'Dressers'],
        'Tumbalar' => ['Тумбы', 'Nightstands'],
        'Matraslar' => ['Матрасы', 'Mattresses'],
        'Masalar' => ['Столы', 'Tables'],
        'Stullar' => ['Стулья', 'Chairs'],
        'Divanlar' => ['Диваны', 'Sofas'],
        'Kreslolar' => ['Кресла', 'Armchairs'],
        'Qonaq dəstləri' => ['Столовые гарнитуры', 'Dining sets'],
        'Divan dəstləri' => ['Комплекты диванов', 'Sofa sets'],
        'Aktiv kampaniyalar' => ['Активные акции', 'Active campaigns'],
        'Həftənin təklifi' => ['Предложение недели', 'Deal of the week'],
        '2026 kolleksiyası' => ['Коллекция 2026', '2026 collection'],
    ];

    protected array $bySlug = [];

    public function run(): void
    {
        $this->categories();
        [$color, $material, $style] = $this->optionsAndAttributes();
        $this->products($color, $material, $style);
    }

    protected function t(string $az): array
    {
        [$ru, $en] = $this->dict[$az] ?? [$az, $az];

        return ['az' => $az, 'ru' => $ru, 'en' => $en];
    }

    protected function categories(): void
    {
        $tree = json_decode(file_get_contents(database_path('seeders/data/menu_tree.json')), true);
        $sort = 0;

        foreach ($tree as $room) {
            if (! str_starts_with($room['href'], 'http')) {
                continue; // "Bütün məhsullar" kimi xüsusi linklər
            }
            $roomSlug = basename(urldecode($room['href']));
            $root = Category::query()->create(['name' => $this->t($room['title']), 'slug' => ['az' => $roomSlug], 'sort' => $sort++]);
            $this->bySlug[$roomSlug] = $root;

            foreach ($room['groups'] as $gi => $group) {
                $groupSlug = $roomSlug.'-'.\Illuminate\Support\Str::slug($group['title']);
                $groupCat = Category::query()->create(['parent_id' => $root->id, 'name' => $this->t($group['title']), 'slug' => ['az' => $groupSlug], 'sort' => $gi]);
                $this->bySlug[$groupSlug] = $groupCat;

                foreach ($group['links'] as $li => [$title, $href]) {
                    if (trim($title) === '' || $href === '') {
                        continue;
                    }
                    $slug = basename(urldecode($href));
                    $leaf = Category::query()->create(['parent_id' => $groupCat->id, 'name' => $this->t($title), 'slug' => ['az' => $slug], 'sort' => $li]);
                    $this->bySlug[$slug] = $leaf;
                }
            }
        }

        foreach (['Aktiv kampaniyalar' => 'aktiv-kampaniyalar', 'Həftənin təklifi' => 'heftenin-teklifi', '2026 kolleksiyası' => '2026-kolleksiyasi'] as $name => $slug) {
            $this->bySlug[$slug] = Category::query()->create(['name' => $this->t($name), 'slug' => ['az' => $slug], 'sort' => $sort++, 'show_in_filter' => false]);
        }

        Category::fixTree();
    }

    protected function optionsAndAttributes(): array
    {
        $color = Option::query()->create(['name' => ['az' => 'Rəng', 'ru' => 'Цвет', 'en' => 'Color'], 'type' => 'color', 'sort' => 1]);
        foreach ([['Bəyaz', 'Белый', 'White', '#ffffff'], ['Boz', 'Серый', 'Grey', '#9e9e9e'], ['Qəhvəyi', 'Коричневый', 'Brown', '#6d4c41'], ['Qara', 'Чёрный', 'Black', '#222222']] as $i => [$az, $ru, $en, $hex]) {
            $color->values()->create(['name' => compact('az', 'ru', 'en'), 'color' => $hex, 'sort' => $i]);
        }

        $group = AttributeGroup::query()->create(['name' => ['az' => 'Əsas xüsusiyyətlər', 'ru' => 'Основные характеристики', 'en' => 'Main specs'], 'sort' => 1]);
        $material = Attribute::query()->create(['attribute_group_id' => $group->id, 'name' => ['az' => 'Material', 'ru' => 'Материал', 'en' => 'Material'], 'sort' => 1]);
        foreach ([['Rusiya laminatı', 'Российский ламинат', 'Russian laminate'], ['MDF', 'МДФ', 'MDF'], ['Masiv ağac', 'Массив дерева', 'Solid wood']] as $i => [$az, $ru, $en]) {
            $material->values()->create(['value' => compact('az', 'ru', 'en'), 'sort' => $i]);
        }
        $style = Attribute::query()->create(['attribute_group_id' => $group->id, 'name' => ['az' => 'Tərz', 'ru' => 'Стиль', 'en' => 'Style'], 'sort' => 2]);
        foreach ([['Minimalist', 'Минимализм', 'Minimalist'], ['Klassik', 'Классика', 'Classic'], ['Modern', 'Модерн', 'Modern']] as $i => [$az, $ru, $en]) {
            $style->values()->create(['value' => compact('az', 'ru', 'en'), 'sort' => $i]);
        }

        // Filtr ayarları: otaqlar üçün; alt kateqoriyalar miras alır
        foreach (['yataq-otagi', 'qonaq-otagi-q', 'istirahet-otagi'] as $slug) {
            $category = $this->bySlug[$slug] ?? null;
            if (! $category) {
                continue;
            }
            $filters = [['price', null], ['subcategory', null], ['option', $color->id], ['attribute', $material->id], ['attribute', $style->id], ['stock', null]];
            foreach ($filters as $i => [$type, $ref]) {
                CategoryFilter::query()->create(['category_id' => $category->id, 'type' => $type, 'ref_id' => $ref, 'sort' => $i]);
            }
        }

        return [$color, $material, $style];
    }

    protected function products(Option $color, Attribute $material, Attribute $style): void
    {
        $cat = fn (string $slug) => $this->bySlug[$slug]->id;
        $mat = fn (string $az) => $material->values->first(fn ($v) => $v->getTranslation('value', 'az') === $az)->id;
        $sty = fn (string $az) => $style->values->first(fn ($v) => $v->getTranslation('value', 'az') === $az)->id;
        $col = fn (string $az) => $color->values->first(fn ($v) => $v->getTranslation('name', 'az') === $az)->id;
        $material->load('values');
        $style->load('values');
        $color->load('values');

        $module = function (string $az, string $ru, string $en, string $category, float $price, float $old, string $image, string $dims) use ($cat) {
            $p = Product::query()->create([
                'type' => 'module',
                'sku' => 'AYP-'.strtoupper(substr(\Illuminate\Support\Str::slug($az), 7, 4)),
                'name' => compact('az', 'ru', 'en'),
                'dimensions' => ['az' => "<p>{$dims}</p>", 'ru' => "<p>{$dims}</p>", 'en' => "<p>{$dims}</p>"],
                'main_category_id' => $cat($category),
                'price' => $price,
                'old_price' => $old,
                'stock_qty' => 20,
                'sold_separately' => true,
            ]);
            $p->images()->create(['path' => 'uploads/demo/'.$image]);

            return $p;
        };

        $bed = $module('Aypara çarpayı', 'Кровать Айпара', 'Aypara bed', 'carpayilar-y', 316.30, 333, 'aypara-carpayi.jpg', 'Çarpayı (E*D): 160 * 200 sm');
        $nightstand = $module('Aypara tumba', 'Тумба Айпара', 'Aypara nightstand', 'tumbalar-y', 35.10, 37, 'aypara-tumba.jpg', 'Tumba (E*H*D): 50 * 35 * 42 sm');
        $wardrobe = $module('Aypara dolab', 'Шкаф Айпара', 'Aypara wardrobe', 'dolablar-y', 368, 386, 'aypara-dolab.jpg', 'Dolab (E*H*D): 150 * 210 * 50 sm');
        $dresser = $module('Aypara trümo', 'Трюмо Айпара', 'Aypara dresser', 'trumolar-y', 133, 140, 'aypara-trumo.jpg', 'Trümo (E*H*D): 80 * 152 * 37 sm');
        $wardrobe->images()->create(['path' => 'uploads/demo/aypara-dolab-aciq.jpg', 'sort' => 1]);

        $set = Product::query()->create([
            'type' => 'set',
            'sku' => 'AYP-SET',
            'name' => ['az' => 'Aypara yataq dəsti', 'ru' => 'Спальный гарнитур Айпара', 'en' => 'Aypara bedroom set'],
            'short_description' => ['az' => 'Minimalist üslubda, Rusiya laminatından yataq dəsti.', 'ru' => 'Спальный гарнитур в стиле минимализм.', 'en' => 'Minimalist bedroom set.'],
            'dimensions' => ['az' => '<p>Çarpayı (E*D): 160 * 200 sm</p><p>Dolab (E*H*D): 150 * 210 * 50 sm</p><p>Trümo (E*H*D): 80 * 152 * 37 sm</p><p>2 Tumba (E*H*D): 50 * 35 * 42 sm</p>'],
            'description' => ['az' => '<ul><li>Çarpayı metal üzəri MDF dikt bərkidilmiş karkazladır, baza əlavə etmək mümkündür.</li><li>Başlıq yumşaq, işləməli və ləkəsaxlamayan parçadandır.</li><li>3 qapılı dolab, stop mexanizmli qapılar.</li><li>Dolabda həm asılqan, həm də rəflər mövcuddur.</li><li>3 çəkməcəli trümo.</li></ul>'],
            'main_category_id' => $cat('yataq-destleri'),
            'stock_qty' => 10,
            'label' => ['az' => 'Hot', 'ru' => 'Hot', 'en' => 'Hot'],
            'is_featured' => true,
        ]);
        $set->categories()->syncWithoutDetaching([$cat('heftenin-teklifi'), $cat('aktiv-kampaniyalar')]);
        foreach (['aypara-desti.jpg', 'aypara-carpayi.jpg', 'aypara-tumba.jpg', 'aypara-trumo.jpg', 'aypara-dolab-aciq.jpg', 'aypara-dolab.jpg'] as $i => $img) {
            $set->images()->create(['path' => 'uploads/demo/'.$img, 'sort' => $i]);
        }

        // Dəstin modulları: tumbanı minimum 2 ədəd almaq olar (1 ədəd seçmək mümkün deyil)
        $set->setItems()->createMany([
            ['component_id' => $bed->id, 'default_qty' => 1, 'min_qty' => 1, 'max_qty' => 1, 'is_required' => true, 'sort' => 1],
            ['component_id' => $nightstand->id, 'default_qty' => 2, 'min_qty' => 2, 'max_qty' => 4, 'is_required' => false, 'sort' => 2],
            ['component_id' => $wardrobe->id, 'default_qty' => 1, 'min_qty' => 1, 'max_qty' => 2, 'is_required' => false, 'sort' => 3],
            ['component_id' => $dresser->id, 'default_qty' => 1, 'min_qty' => 1, 'max_qty' => 1, 'is_required' => false, 'sort' => 4],
        ]);

        $po = ProductOption::query()->create(['product_id' => $set->id, 'option_id' => $color->id, 'is_required' => true, 'sort' => 1]);
        $po->values()->create(['product_id' => $set->id, 'option_value_id' => $col('Bəyaz'), 'price_modifier' => 0, 'is_default' => true, 'sort' => 1]);
        $po->values()->create(['product_id' => $set->id, 'option_value_id' => $col('Boz'), 'price_modifier' => 3, 'modifier_type' => 'percent', 'sort' => 2]);
        $set->attributeValues()->sync([$mat('Rusiya laminatı'), $sty('Minimalist')]);
        foreach ([$bed, $nightstand, $wardrobe, $dresser] as $m) {
            $m->attributeValues()->sync([$mat('Rusiya laminatı'), $sty('Minimalist')]);
        }
        app(SetPricingService::class)->refreshComputed($set);

        $bodrum = Product::query()->create([
            'type' => 'simple',
            'sku' => 'BDR-SET',
            'name' => ['az' => 'Bodrum yataq dəsti', 'ru' => 'Спальный гарнитур Бодрум', 'en' => 'Bodrum bedroom set'],
            'dimensions' => ['az' => '<p>Çarpayı (E*D): 160 * 200 sm</p>'],
            'main_category_id' => $cat('yataq-destleri'),
            'price' => 989, 'old_price' => 1041, 'stock_qty' => 5, 'is_featured' => true,
        ]);
        $bodrum->images()->create(['path' => 'uploads/demo/bodrum-desti.jpg']);
        $bodrum->attributeValues()->sync([$mat('MDF'), $sty('Klassik')]);
        $bodrum->categories()->syncWithoutDetaching([$cat('2026-kolleksiyasi')]);

        $afyon = Product::query()->create([
            'type' => 'simple',
            'sku' => 'AFY-MASA',
            'name' => ['az' => 'Afyon masa', 'ru' => 'Стол Афьон', 'en' => 'Afyon table'],
            'short_description' => ['az' => 'Masa açılan (E*H*D): 140*76*80 sm'],
            'dimensions' => ['az' => '<p>Masa açılan (E*H*D): 140*76*80 sm</p>'],
            'main_category_id' => $cat('masalar-q'),
            'price' => 599, 'old_price' => 630, 'stock_qty' => 8, 'is_featured' => true,
        ]);
        $afyon->images()->create(['path' => 'uploads/demo/afyon-masa.jpg']);
        $afyon->attributeValues()->sync([$mat('Masiv ağac'), $sty('Modern')]);
        $po = ProductOption::query()->create(['product_id' => $afyon->id, 'option_id' => $color->id, 'is_required' => true, 'sort' => 1]);
        $po->values()->create(['product_id' => $afyon->id, 'option_value_id' => $col('Qəhvəyi'), 'price_modifier' => 0, 'is_default' => true, 'sort' => 1]);
        $po->values()->create(['product_id' => $afyon->id, 'option_value_id' => $col('Qara'), 'price_modifier' => 20, 'sort' => 2]);
        $afyon->categories()->syncWithoutDetaching([$cat('heftenin-teklifi')]);

        $set->related()->sync([$bodrum->id, $afyon->id]);
    }
}
