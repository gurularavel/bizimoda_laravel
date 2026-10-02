<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Support\Locales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_root_redirects_to_default_locale(): void
    {
        $this->get('/')->assertRedirect('/az');
    }

    public function test_main_pages_render_in_all_languages(): void
    {
        $set = Product::query()->where('sku', 'AYP-SET')->first();

        foreach (['az', 'ru', 'en'] as $locale) {
            app()->setLocale($locale);
            $this->get("/{$locale}")->assertOk()->assertSee('module-products-310', false);
            $this->get($set->url($locale))->assertOk()->assertSee($set->getTranslation('name', $locale));
        }

        $this->get('/ru/spalnye-garnitury/spalnyi-garnitur-aipara')->assertOk()->assertSee('Состав комплекта');
        $this->get('/en/bedroom-sets/aypara-bedroom-set')->assertOk()->assertSee('minimum 2 pcs');
    }

    public function test_product_url_with_slug_of_another_language_redirects_to_canonical(): void
    {
        $this->get('/ru/yataq-destleri/aypara-yataq-desti')
            ->assertRedirect('/ru/spalnye-garnitury/spalnyi-garnitur-aipara');
    }

    public function test_disabled_language_redirects_to_default(): void
    {
        Language::query()->where('code', 'en')->update(['is_active' => false]);
        Locales::flush();

        $this->get('/en/specials')->assertRedirect('/az/specials');
    }

    public function test_category_filters_by_option_and_shows_configured_filters(): void
    {
        $category = Category::query()->whereSlug('yataq-otagi', 'az')->first();

        $this->get($category->url('az'))
            ->assertOk()
            ->assertSee('Qiymət aralığı')
            ->assertSee('Rəng')
            ->assertSee('Material');

        $color = \App\Models\OptionValue::query()->get()->first(fn ($v) => $v->getTranslation('name', 'az') === 'Boz');

        $html = $this->get($category->url('az').'?fo'.$color->option_id.'='.$color->id)->assertOk()->getContent();
        // Yalnız əsas məhsul siyahısı (aşağıdakı "Xüsusi təkliflər" karuseli xaric)
        $main = \Illuminate\Support\Str::between($html, 'main-products product-grid', 'pagination-results');

        $this->assertStringContainsString('Aypara yataq dəsti', $main);
        $this->assertStringNotContainsString('Bodrum yataq dəsti', $main);
    }

    public function test_module_not_sold_separately_is_hidden_from_listing(): void
    {
        $nightstand = Product::query()->where('sku', 'AYP-TUMB')->first();
        $nightstand->update(['sold_separately' => false]);

        $this->get(Category::query()->whereSlug('tumbalar-y', 'az')->first()->url('az'))
            ->assertOk()
            ->assertDontSee('Aypara tumba</a>', false);
    }

    public function test_search_and_autocomplete(): void
    {
        $this->get('/az/search?search=aypara')->assertOk()->assertSee('Aypara yataq dəsti');
        $this->getJson('/ajax/journal3/search?search=bodrum')->assertOk()->assertJsonPath('response.0.name', 'Bodrum yataq dəsti');
    }

    public function test_sitemap_contains_hreflang_alternates(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee('hreflang="ru"', false);
    }
}
