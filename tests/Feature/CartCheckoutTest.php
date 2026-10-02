<?php

namespace Tests\Feature;

use App\Mail\NewOrderAdminMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Option;
use App\Models\Order;
use App\Models\Product;
use App\Services\SettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setPayload(int $nightstandQty): array
    {
        $option = Option::query()->first();

        return [
            'quantity' => 1,
            'components' => [Product::query()->where('sku', 'AYP-TUMB')->value('id') => $nightstandQty],
            'options' => [$option->id => $option->values->first()->id],
        ];
    }

    public function test_set_with_single_nightstand_cannot_be_added_to_cart(): void
    {
        $set = Product::query()->where('sku', 'AYP-SET')->first();

        $this->postJson(route('front.cart.add', ['locale' => 'az', 'product' => $set->id]), $this->setPayload(1))
            ->assertOk()
            ->assertJsonStructure(['error']);

        $this->getJson('/ajax/common/cart/info')->assertSee('Səbətiniz boşdur!');
    }

    public function test_cod_checkout_creates_order_with_set_components_and_sends_emails(): void
    {
        Mail::fake();
        app(SettingsRepository::class)->set('mail.admin_emails', 'orders@bizimoda.test');
        $set = Product::query()->where('sku', 'AYP-SET')->first();

        $this->postJson(route('front.cart.add', ['locale' => 'az', 'product' => $set->id]), $this->setPayload(2))
            ->assertOk()->assertJson(['items_count' => 1]);

        $this->post(route('front.checkout.store', ['locale' => 'az']), [
            'first_name' => 'Test',
            'phone' => '+994 50 111 22 33',
            'email' => 'client@example.com',
            'address' => 'Nizami 1',
            'payment_method' => 'cod',
            'agree' => 1,
        ])->assertRedirect(route('front.checkout.success', ['locale' => 'az', 'number' => 'BM000001']));

        $order = Order::query()->with('items.components')->first();
        $this->assertEquals(887.50, (float) $order->total);
        $this->assertSame('cod', $order->payment_method);
        $this->assertCount(4, $order->items->first()->components);
        $this->assertEquals(2, $order->items->first()->components->firstWhere('name', 'Aypara tumba')->quantity);

        Mail::assertSent(NewOrderAdminMail::class, fn ($m) => $m->hasTo('orders@bizimoda.test'));
        Mail::assertSent(OrderConfirmationMail::class, fn ($m) => $m->hasTo('client@example.com'));
    }

    public function test_checkout_requires_phone_and_address(): void
    {
        $set = Product::query()->where('sku', 'AYP-SET')->first();
        $this->postJson(route('front.cart.add', ['locale' => 'az', 'product' => $set->id]), $this->setPayload(2));

        $this->post(route('front.checkout.store', ['locale' => 'az']), ['first_name' => 'X', 'payment_method' => 'cod'])
            ->assertSessionHasErrors(['phone', 'address', 'agree']);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_legacy_journal_cart_add_endpoint_adds_simple_product(): void
    {
        $bodrum = Product::query()->where('sku', 'BDR-SET')->first();

        $this->postJson('/ajax/checkout/cart/add', ['product_id' => $bodrum->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson(['items_count' => 2])
            ->assertJsonPath('notification.title', 'Bodrum yataq dəsti');
    }
}
