<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Option;
use App\Models\Order;
use App\Models\Product;
use App\Services\DiscountService;
use App\Services\SetPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function admin(): Admin
    {
        return Admin::query()->create(['name' => 'T', 'email' => 'd@t.test', 'password' => 'password123', 'role' => 'super_admin', 'is_active' => true]);
    }

    protected function product(string $sku): Product
    {
        return Product::query()->where('sku', $sku)->firstOrFail();
    }

    public function test_percent_discount_on_selected_product_via_admin(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $bodrum = $this->product('BDR-SET'); // 989 (köhnə 1041)

        $this->post(route('admin.discounts.store'), [
            'name' => 'Bodrum -10%', 'type' => 'percent', 'value' => 10, 'applies_to' => 'products',
            'products' => [$bodrum->id], 'is_active' => 1,
        ])->assertRedirect(route('admin.discounts.index'));

        $bodrum->refresh();
        $this->assertEquals(890.10, (float) $bodrum->computed_price);
        $this->assertEquals(1041.00, (float) $bodrum->computed_old_price);

        $price = app(SetPricingService::class)->price($bodrum, []);
        $this->assertEquals(890.10, $price['unit_price']);
        $this->assertEquals(98.90, $price['discount']);
        $this->assertSame('Bodrum -10%', $price['discount_name']);
    }

    public function test_fixed_discount_on_category_applies_to_subcategory_products_and_sets(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $room = Category::query()->whereSlug('yataq-otagi', 'az')->first(); // dəst "yataq-destleri" alt kateqoriyasındadır

        $this->post(route('admin.discounts.store'), [
            'name' => 'Yataq otağı -50 ₼', 'type' => 'fixed', 'value' => 50, 'applies_to' => 'categories',
            'categories' => [$room->id], 'is_active' => 1,
        ])->assertRedirect();

        $this->assertEquals(837.50, (float) $this->product('AYP-SET')->computed_price); // 887.5 - 50
        $this->assertEquals(939.00, (float) $this->product('BDR-SET')->computed_price);  // 989 - 50
        $this->assertEquals(599.00, (float) $this->product('AFY-MASA')->computed_price); // başqa otaq — dəyişmir
    }

    public function test_best_discount_wins_and_price_never_goes_negative(): void
    {
        $bodrum = $this->product('BDR-SET');
        Discount::query()->create(['name' => 'Az', 'type' => 'fixed', 'value' => 20, 'applies_to' => 'all'])->save();
        $big = Discount::query()->create(['name' => 'Çox', 'type' => 'percent', 'value' => 30, 'applies_to' => 'products']);
        $big->products()->attach($bodrum->id);
        Discount::query()->create(['name' => 'Həddən çox', 'type' => 'fixed', 'value' => 999999, 'applies_to' => 'products'])
            ->products()->attach($this->product('AFY-MASA')->id);
        app(DiscountService::class)->flush();

        $this->assertSame('Çox', app(SetPricingService::class)->price($bodrum, [])['discount_name']);
        $this->assertEquals(0.0, app(SetPricingService::class)->price($this->product('AFY-MASA'), [])['unit_price']);
    }

    public function test_scheduled_and_expired_discounts_are_ignored(): void
    {
        $bodrum = $this->product('BDR-SET');
        Discount::query()->create(['name' => 'Gələcək', 'type' => 'percent', 'value' => 50, 'applies_to' => 'all', 'starts_at' => now()->addDay()]);
        Discount::query()->create(['name' => 'Keçmiş', 'type' => 'percent', 'value' => 50, 'applies_to' => 'all', 'ends_at' => now()->subDay()]);
        Discount::query()->create(['name' => 'Söndürülmüş', 'type' => 'percent', 'value' => 50, 'applies_to' => 'all', 'is_active' => false]);
        app(DiscountService::class)->flush();

        $this->assertEquals(989.00, app(SetPricingService::class)->price($bodrum, [])['unit_price']);
    }

    public function test_discount_is_stored_on_order_item_and_removed_after_delete(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $set = $this->product('AYP-SET');
        $this->post(route('admin.discounts.store'), [
            'name' => 'Dəst -10%', 'type' => 'percent', 'value' => 10, 'applies_to' => 'products', 'products' => [$set->id], 'is_active' => 1,
        ]);
        $discount = Discount::query()->first();

        $option = Option::query()->first();
        $this->postJson(route('front.cart.add', ['locale' => 'az', 'product' => $set->id]), [
            'quantity' => 1, 'options' => [$option->id => $option->values->first()->id],
        ])->assertJson(['items_count' => 1]);
        $this->post(route('front.checkout.store', ['locale' => 'az']), [
            'first_name' => 'A', 'phone' => '+994501112233', 'address' => 'Bakı', 'payment_method' => 'cod', 'agree' => 1,
        ]);

        $item = Order::query()->first()->items->first();
        $this->assertEquals(798.75, (float) $item->unit_price); // 887.5 × 0.9
        $this->assertEquals(88.75, (float) $item->unit_discount);
        $this->assertSame('Dəst -10%', $item->discount_name);

        $this->delete(route('admin.discounts.destroy', $discount))->assertRedirect();
        $this->assertEquals(887.50, (float) $set->fresh()->computed_price);
    }

    public function test_percent_over_100_is_rejected(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->post(route('admin.discounts.store'), ['name' => 'X', 'type' => 'percent', 'value' => 150, 'applies_to' => 'all'])
            ->assertSessionHasErrors('value');
    }
}
