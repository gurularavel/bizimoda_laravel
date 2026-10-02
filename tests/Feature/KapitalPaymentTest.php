<?php

namespace Tests\Feature;

use App\Mail\NewOrderAdminMail;
use App\Models\Order;
use App\Models\Product;
use App\Services\SettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class KapitalPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingsRepository::class)->setMany([
            'payment.kapital_enabled' => true,
            'payment.kapital_mode' => 'test',
            'payment.kapital_username' => 'TerminalSys/kapital',
            'payment.kapital_password' => 'secret',
            'mail.admin_emails' => 'orders@bizimoda.test',
        ]);
    }

    public function test_card_payment_redirects_to_bank_and_confirms_on_return(): void
    {
        Mail::fake();
        Http::fake([
            'txpgtst.kapitalbank.az/api/order' => Http::response(['order' => ['id' => 777, 'password' => 'pwd', 'hppUrl' => 'https://txpgtst.kapitalbank.az/flex', 'status' => 'Preparing']]),
            'txpgtst.kapitalbank.az/api/order/777*' => Http::response(['order' => ['id' => 777, 'status' => 'FullyPaid']]),
        ]);

        $bodrum = Product::query()->where('sku', 'BDR-SET')->first();
        $this->postJson('/ajax/checkout/cart/add', ['product_id' => $bodrum->id, 'quantity' => 1]);

        $response = $this->post(route('front.checkout.store', ['locale' => 'az']), [
            'first_name' => 'Kart', 'phone' => '+994501112233', 'address' => 'Bakı', 'payment_method' => 'kapital', 'agree' => 1,
        ]);

        $response->assertRedirect('https://txpgtst.kapitalbank.az/flex?id=777&password=pwd');
        Http::assertSent(fn ($r) => $r->url() === 'https://txpgtst.kapitalbank.az/api/order'
            && $r['order']['amount'] === '989.00' && $r['order']['currency'] === 'AZN'
            && $r->hasHeader('Authorization'));

        // Ödənişdən əvvəl admin bildirişi göndərilmir
        Mail::assertNotSent(NewOrderAdminMail::class);

        $order = Order::query()->first();
        $this->get(URL::signedRoute('payment.kapital.return', ['order' => $order->number]).'&ID=777&STATUS=FullyPaid')
            ->assertRedirect(route('front.checkout.success', ['locale' => 'az', 'number' => $order->number]));

        $this->assertSame('paid', $order->fresh()->payment_status);
        Mail::assertSent(NewOrderAdminMail::class);
    }

    public function test_unsigned_return_url_is_rejected(): void
    {
        $this->get('/payment/kapital/return?order=BM000001')->assertForbidden();
    }
}
