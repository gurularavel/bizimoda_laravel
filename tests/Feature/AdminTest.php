<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function admin(string $role = 'super_admin'): Admin
    {
        return Admin::query()->create(['name' => 'T', 'email' => $role.'@t.test', 'password' => 'password123', 'role' => $role, 'is_active' => true]);
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_product_requires_main_category_and_main_is_attached_to_categories(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $payload = ['type' => 'simple', 'name' => ['az' => 'Test masa'], 'price' => 100, 'stock_status' => 'in_stock', 'is_active' => 1];

        $this->post(route('admin.products.store'), $payload)->assertSessionHasErrors('main_category_id');

        $main = Category::query()->whereSlug('masalar-q', 'az')->first();
        $extra = Category::query()->whereSlug('2026-kolleksiyasi', 'az')->first();
        $this->post(route('admin.products.store'), $payload + ['main_category_id' => $main->id, 'categories' => [$extra->id]])
            ->assertRedirect();

        $product = Product::query()->where('name->az', 'Test masa')->first();
        $this->assertEqualsCanonicalizing([$main->id, $extra->id], $product->categories->pluck('id')->all());
        $this->assertSame('test-masa', $product->getTranslation('slug', 'az'));
    }

    public function test_set_module_default_below_minimum_is_rejected(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $module = Product::query()->where('sku', 'AYP-TUMB')->first();

        $this->post(route('admin.products.store'), [
            'type' => 'set', 'name' => ['az' => 'Yeni dəst'], 'stock_status' => 'in_stock',
            'main_category_id' => $module->main_category_id,
            'set_items' => [['component_id' => $module->id, 'default_qty' => 1, 'min_qty' => 2]],
        ])->assertSessionHasErrors('set_items.0.default_qty');
    }

    public function test_menu_item_with_external_link_appears_on_storefront(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $menu = Menu::query()->where('key', 'main')->first();

        $this->post(route('admin.menus.items.store', $menu), [
            'type' => 'url', 'url' => 'https://example.com/promo', 'title' => ['az' => 'Promo link'],
            'display' => 'link', 'is_active' => 1, 'target_blank' => 1,
        ])->assertRedirect();

        $this->get('/az')->assertSee('https://example.com/promo', false)->assertSee('Promo link');
    }

    public function test_content_manager_cannot_open_orders_or_settings(): void
    {
        $this->actingAs($this->admin('content'), 'admin');

        $this->get(route('admin.pages.index'))->assertOk();
        $this->get(route('admin.orders.index'))->assertForbidden();
        $this->get(route('admin.settings.edit'))->assertForbidden();
    }
}
