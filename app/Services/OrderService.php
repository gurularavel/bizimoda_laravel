<?php

namespace App\Services;

use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(protected CartService $cart, protected SetPricingService $pricing) {}

    /**
     * Səbətdən sifariş yaradır. Qapıda ödənişdə bildiriş dərhal göndərilir,
     * kartla ödənişdə isə ödəniş təsdiqləndikdən sonra (PaymentController).
     *
     * @param  array{first_name:string,last_name?:string,email?:string,phone:string,city?:string,address?:string,comment?:string}  $customer
     */
    public function placeFromCart(array $customer, string $paymentMethod, ?User $user = null): Order
    {
        $lines = $this->cart->lines();
        if ($lines->isEmpty()) {
            throw new RuntimeException('Cart is empty');
        }

        $subtotal = $this->cart->subtotal();
        $delivery = $this->cart->deliveryFee($subtotal);

        $order = DB::transaction(function () use ($lines, $customer, $paymentMethod, $user, $subtotal, $delivery) {
            $order = $this->createOrder($customer + [
                'user_id' => $user?->id,
                'subtotal' => $subtotal,
                'delivery_fee' => $delivery,
                'total' => round($subtotal + $delivery, 2),
                'payment_method' => $paymentMethod,
                'source' => 'checkout',
            ]);

            foreach ($lines as $line) {
                $this->addItem($order, $line['product'], $line['quantity'], $line);
            }

            return $order;
        });

        $this->cart->clear();

        if ($paymentMethod === 'cod') {
            event(new OrderPlaced($order));
        }

        return $order;
    }

    /** "Bir kliklə al": ad + telefon ilə standart konfiqurasiyada sifariş */
    public function placeOneClick(Product $product, string $name, string $phone, ?string $comment = null, ?User $user = null): Order
    {
        $config = $this->pricing->defaultConfiguration($product);
        $price = $this->pricing->price($product, $config);
        $quantity = $product->isSet() ? 1 : max(1, (int) $product->min_qty);
        $subtotal = round($price['unit_price'] * $quantity, 2);
        $delivery = $this->cart->deliveryFee($subtotal);

        $order = DB::transaction(function () use ($product, $name, $phone, $comment, $user, $price, $quantity, $subtotal, $delivery) {
            $order = $this->createOrder([
                'user_id' => $user?->id,
                'first_name' => $name,
                'phone' => $phone,
                'email' => $user?->email,
                'comment' => $comment,
                'subtotal' => $subtotal,
                'delivery_fee' => $delivery,
                'total' => round($subtotal + $delivery, 2),
                'payment_method' => 'cod',
                'source' => 'one_click',
            ]);
            $this->addItem($order, $product, $quantity, $price);

            return $order;
        });

        event(new OrderPlaced($order));

        return $order;
    }

    public function changeStatus(Order $order, string $status, ?string $comment = null, ?int $adminId = null): void
    {
        $order->update(['status' => $status]);
        OrderHistory::query()->create(['order_id' => $order->id, 'status' => $status, 'comment' => $comment, 'admin_id' => $adminId]);
    }

    protected function createOrder(array $data): Order
    {
        $order = Order::query()->create($data + [
            'number' => 'TMP-'.uniqid(),
            'locale' => app()->getLocale(),
            'status' => 'new',
            'payment_status' => 'pending',
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
        $order->update(['number' => 'BM'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)]);
        OrderHistory::query()->create(['order_id' => $order->id, 'status' => 'new']);

        return $order;
    }

    protected function addItem(Order $order, Product $product, int $quantity, array $price): void
    {
        $locale = $order->locale;
        $item = $order->items()->create([
            'product_id' => $product->id,
            'type' => $product->type,
            'name' => $product->getTranslation('name', $locale),
            'sku' => $product->sku,
            'quantity' => $quantity,
            'unit_price' => $price['unit_price'],
            'unit_old_price' => $price['unit_old_price'],
            'unit_discount' => $price['discount'] ?? 0,
            'discount_name' => $price['discount_name'] ?? null,
            'total' => round($price['unit_price'] * $quantity, 2),
            'options' => collect($price['options'])->map(fn ($o) => [
                'name' => $o['name'][$locale] ?? reset($o['name']),
                'value' => $o['value'][$locale] ?? reset($o['value']),
                'modifier' => $o['modifier'],
            ])->values()->all(),
        ]);

        foreach ($price['components'] as $component) {
            $item->components()->create([
                'product_id' => $component['product']->id,
                'name' => $component['product']->getTranslation('name', $locale),
                'quantity' => $component['qty'],
                'unit_price' => $component['unit_price'],
                'total' => $component['total'],
            ]);
        }
    }
}
