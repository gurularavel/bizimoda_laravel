<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Slider;
use App\Services\SettingsRepository;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    protected array $pages = [];

    public function run(): void
    {
        $this->pages();
        $this->menus();
        $this->home();
        $this->blog();
    }

    protected function pages(): void
    {
        $defs = [
            'magaza-unvanlari' => ['Mağaza ünvanları', 'Адреса магазинов', 'Store addresses', '<p>Həzi Aslanov metrosu, Neftçilər metrosu, 3-cü mikrorayon dairəsi, Xırdalan.</p>'],
            'catdirilma-ve-qurasdirilma' => ['Çatdırılma və quraşdırılma', 'Доставка и сборка', 'Delivery and assembly', '<p>Bakı daxilində çatdırılma və quraşdırılma pulsuzdur.</p>'],
            'geri-qaytarma-siyaseti' => ['Geri qaytarma siyasəti', 'Политика возврата', 'Return policy', '<p>Geri qaytarma qaydaları.</p>'],
            'zemanet-sertleri' => ['Zəmanət şərtləri', 'Условия гарантии', 'Warranty terms', '<p>Zəmanət şərtləri.</p>'],
            'sirket-haqqinda' => ['Şirkət haqqında', 'О компании', 'About us', '<p>bizimoda — mebel və ev tekstili.</p>'],
            'vakansiya' => ['Karyera imkanları', 'Вакансии', 'Careers', '<p>Açıq vakansiyalar.</p>'],
            'b2b' => ['B2B', 'B2B', 'B2B', '<p>Korporativ müştərilər üçün xüsusi təkliflər.</p>'],
            'qaydalar' => ['Qaydalar', 'Правила', 'Terms', '<p>Saytdan istifadə və sifariş qaydaları.</p>'],
        ];

        $i = 0;
        foreach ($defs as $slug => [$az, $ru, $en, $content]) {
            $this->pages[$slug] = Page::query()->create([
                'title' => compact('az', 'ru', 'en'),
                'slug' => ['az' => $slug],
                'content' => ['az' => $content, 'ru' => $content, 'en' => $content],
                'sort' => $i++,
            ]);
        }

        $this->pages['elaqe-yaradin'] = Page::query()->create([
            'title' => ['az' => 'Əlaqə yaradın', 'ru' => 'Контакты', 'en' => 'Contact us'],
            'slug' => ['az' => 'elaqe-yaradin', 'ru' => 'kontakty', 'en' => 'contact-us'],
            'content' => ['az' => '<p>E-mail: info@bizimoda.az</p><p>WP|Zəng: +994702177121</p>'],
            'template' => 'contact',
            'sort' => $i,
        ]);

        app(SettingsRepository::class)->set('checkout.terms_page_id', $this->pages['qaydalar']->id);
    }

    protected function item(Menu $menu, array $title, array $attrs = [], ?MenuItem $parent = null): MenuItem
    {
        static $sort = 0;

        return MenuItem::query()->create(array_merge([
            'menu_id' => $menu->id,
            'parent_id' => $parent?->id,
            'title' => $title,
            'type' => 'none',
            'sort' => $sort++,
        ], $attrs));
    }

    protected function menus(): void
    {
        $menus = [];
        foreach (Menu::LOCATIONS as $key => $name) {
            $menus[$key] = Menu::query()->create(['key' => $key, 'name' => $name]);
        }

        $page = fn (string $slug) => ['type' => 'page', 'linkable_id' => $this->pages[$slug]->id];
        $category = fn (string $slug) => ['type' => 'category', 'linkable_id' => Category::query()->whereSlug($slug, 'az')->value('id')];
        $t = fn ($az, $ru = null, $en = null) => ['az' => $az, 'ru' => $ru ?? $az, 'en' => $en ?? $az];

        // Yuxarı zolaq
        $this->item($menus['top'], $t('Mağaza ünvanları', 'Адреса магазинов', 'Stores'), $page('magaza-unvanlari'));
        $this->item($menus['top'], $t('Çatdırılma və quraşdırılma', 'Доставка и сборка', 'Delivery'), $page('catdirilma-ve-qurasdirilma'));
        $this->item($menus['mobile_top'], $t('Mağazalar', 'Магазины', 'Stores'), $page('magaza-unvanlari'));

        // Header: qeydiyyat / giriş (Journal popup-ları)
        $this->item($menus['account'], $t('Qeydiyyat', 'Регистрация', 'Register'), ['type' => 'route', 'url' => 'javascript:open_register_popup()']);
        $this->item($menus['account'], $t('Daxil ol', 'Войти', 'Login'), ['type' => 'route', 'url' => 'javascript:open_login_popup()']);

        // Əsas meqa menyu: "Kateqoriyalar" (flyout) → otaqlar (mega) → qruplar (sütunlar) → kateqoriyalar
        $main = $menus['main'];
        $root = $this->item($main, $t('Kateqoriyalar', 'Категории', 'Categories'), ['display' => 'flyout']);
        $tree = json_decode(file_get_contents(database_path('seeders/data/menu_tree.json')), true);
        foreach ($tree as $room) {
            if (! str_starts_with($room['href'], 'http')) {
                $this->item($main, $t($room['title'], 'Все товары', 'All products'), ['type' => 'route', 'url' => 'specials'], $root);
                continue;
            }
            $roomCategory = Category::query()->whereSlug(basename(urldecode($room['href'])), 'az')->first();
            $roomItem = $this->item($main, [], [
                'type' => 'category', 'linkable_id' => $roomCategory?->id, 'display' => 'mega',
                'banner_image' => $room['banner'] ? 'uploads/demo/banner-small-2.png' : null,
                'banner_url' => $room['banner'] ? '/az/specials' : null,
            ], $root);

            foreach ($room['groups'] as $gi => $group) {
                $groupItem = $this->item($main, $t($group['title']), ['display' => 'group', 'column' => $gi + 1], $roomItem);
                foreach ($group['links'] as [$title, $href]) {
                    if (trim($title) === '' || $href === '') {
                        continue;
                    }
                    $this->item($main, [], $category(basename(urldecode($href))), $groupItem);
                }
            }
        }

        $this->item($main, [], $category('aktiv-kampaniyalar'));
        $this->item($main, [], $category('heftenin-teklifi'));
        $this->item($main, [], $category('2026-kolleksiyasi'));
        $this->item($main, $t('Hədiyyə seçin', 'Выбрать подарок', 'Choose a gift'), ['type' => 'route', 'url' => 'specials']);
        $this->item($main, $t('Toyqabağı', 'К свадьбе', 'Before wedding'), ['type' => 'route', 'url' => 'specials']);
        $this->item($main, $t('B2B'), $page('b2b'));
        $this->item($main, $t('Bloq', 'Блог', 'Blog'), ['type' => 'blog']);

        // Footer
        $this->item($menus['footer_1'], $t('Əlaqə yaradın', 'Связаться с нами', 'Contact us'), $page('elaqe-yaradin'));
        $this->item($menus['footer_1'], $t('Servis xidməti', 'Сервис', 'Service'));
        $this->item($menus['footer_1'], [], $page('geri-qaytarma-siyaseti'));
        $this->item($menus['footer_1'], [], $page('zemanet-sertleri'));
        $this->item($menus['footer_2'], $t('Mağazaların ünvanı', 'Адреса магазинов', 'Store addresses'), $page('magaza-unvanlari'));
        $this->item($menus['footer_2'], $t('Sifarişin izlənilməsi', 'Отслеживание заказа', 'Order tracking'), ['type' => 'route', 'url' => 'account/orders']);
        $this->item($menus['footer_2'], $t('Anbar qalıqları', 'Складские остатки', 'Stock'));
        $this->item($menus['footer_2'], [], $page('catdirilma-ve-qurasdirilma'));
        $this->item($menus['footer_3'], [], $page('sirket-haqqinda'));
        $this->item($menus['footer_3'], [], $page('vakansiya'));

        foreach ([
            ['Facebook', 'https://www.facebook.com/bizimodamebel'],
            ['Twitter', '#'],
            ['Instagram', 'https://www.instagram.com/bizimoda_'],
            ['YouTube', 'https://youtube.com/@bizimodamebel'],
            ['Tik Tok', 'https://www.tiktok.com/@bizimoda_'],
            ['LinkedIN', 'https://www.linkedin.com/company/bizimoda'],
        ] as [$name, $url]) {
            $this->item($menus['social'], $t($name), ['type' => 'url', 'url' => $url, 'target_blank' => true]);
        }
    }

    protected function home(): void
    {
        $slider = Slider::query()->create(['key' => 'home', 'name' => 'Ana səhifə slayderi', 'width' => 1590, 'height' => 458]);
        $slider->slides()->create(['image' => 'uploads/demo/slider-1.jpg', 'link' => null, 'sort' => 1]);
        $slider->slides()->create(['image' => 'uploads/demo/slider-2.jpg', 'link' => '/az/specials', 'sort' => 2]);

        $heftenin = Category::query()->whereSlug('heftenin-teklifi', 'az')->value('id');

        $sections = [
            ['slider', null, ['slider_id' => $slider->id], null],
            ['products', ['az' => 'Xüsusi təkliflərimiz', 'ru' => 'Специальные предложения', 'en' => 'Special offers'], ['source' => 'special', 'limit' => 12], 'module-products-310'],
            ['products', ['az' => 'Həftənin təklifi', 'ru' => 'Предложение недели', 'en' => 'Deal of the week'], ['source' => 'category', 'category_id' => $heftenin, 'limit' => 12], 'module-products-27'],
            ['products', ['az' => 'Ev üçün aktual çeşidlər', 'ru' => 'Актуальное для дома', 'en' => 'Trending for home'], ['source' => 'featured', 'limit' => 12], 'module-products-287'],
            ['products', ['az' => 'Ən çox baxılanlar', 'ru' => 'Самые просматриваемые', 'en' => 'Most viewed'], ['source' => 'popular', 'limit' => 12], 'module-products-321'],
        ];
        foreach ($sections as $i => [$type, $title, $data, $class]) {
            HomeSection::query()->create(['type' => $type, 'title' => $title, 'data' => $data, 'module_class' => $class, 'sort' => $i]);
        }
    }

    protected function blog(): void
    {
        $category = BlogCategory::query()->create(['name' => ['az' => 'Dizayn məsləhətləri', 'ru' => 'Советы по дизайну', 'en' => 'Design tips']]);

        BlogPost::query()->create([
            'blog_category_id' => $category->id,
            'title' => ['az' => 'Yataq otağı üçün düzgün mebel seçimi', 'ru' => 'Как выбрать мебель для спальни', 'en' => 'Choosing bedroom furniture'],
            'excerpt' => ['az' => 'Yataq dəsti seçərkən ölçü, material və modulların sayına diqqət edin.'],
            'content' => ['az' => '<p>Yataq dəsti seçərkən otağın ölçüsünü, materialın keyfiyyətini və modulların sayını nəzərə alın. Dəstlərimizdə modulları istədiyiniz say ilə seçə bilərsiniz.</p>'],
            'cover' => 'uploads/demo/aypara-desti.jpg',
            'published_at' => now()->subDays(3),
        ]);
        BlogPost::query()->create([
            'blog_category_id' => $category->id,
            'title' => ['az' => 'Qonaq otağında masa və stul uyğunluğu', 'ru' => 'Стол и стулья для гостиной', 'en' => 'Matching table and chairs'],
            'excerpt' => ['az' => 'Masa və stulların rəng və üslub baxımından uyğunlaşdırılması.'],
            'content' => ['az' => '<p>Masa və stullar otağın əsas elementidir. Rəng və üslub uyğunluğuna diqqət edin.</p>'],
            'cover' => 'uploads/demo/afyon-masa.jpg',
            'published_at' => now()->subDay(),
        ]);
    }
}
