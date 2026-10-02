<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    protected ?Cart $cart = null;

    protected ?Collection $lines = null;

    public function __construct(protected SetPricingService $pricing) {}

    public function token(): string
    {
        if (! session()->has('cart_token')) {
            session()->put('cart_token', (string) Str::uuid());
        }

        return session('cart_token');
    }

    public function cart(bool $create = true): ?Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        $user = Auth::guard('web')->user();
        $query = $user ? Cart::query()->where('user_id', $user->id) : Cart::query()->where('session_id', $this->token())->whereNull('user_id');
        $cart = $query->latest('id')->first();

        if (! $cart && $create) {
            $cart = Cart::query()->create(['user_id' => $user?->id, 'session_id' => $this->token()]);
        }

        return $this->cart = $cart;
    }

    /**
     * @throws ValidationException
     */
    public function add(Product $product, int $quantity = 1, array $input = []): CartItem
    {
        $config = $this->pricing->validate($product, $input);
        $quantity = max(1, $quantity);

        if (! $product->isSet() && $quantity < $product->min_qty) {
            throw ValidationException::withMessages(['quantity' => __('Bu məhsul üçün minimum say: :min.', ['min' => $product->min_qty])]);
        }

        $hash = $this->hash($config);
        $cart = $this->cart();

        /** @var CartItem|null $item */
        $item = $cart->items()->where('product_id', $product->id)->where('config_hash', $hash)->first();
        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $item = $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'configuration' => $config,
                'config_hash' => $hash,
            ]);
        }

        $cart->touch();
        $this->lines = null;

        return $item;
    }

    public function update(int $itemId, int $quantity): void
    {
        $item = $this->findItem($itemId);
        if (! $item) {
            return;
        }

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $min = $item->product && ! $item->product->isSet() ? max(1, (int) $item->product->min_qty) : 1;
            $item->update(['quantity' => max($min, $quantity)]);
        }
        $this->lines = null;
    }

    public function remove(int $itemId): void
    {
        $this->findItem($itemId)?->delete();
        $this->lines = null;
    }

    public function clear(): void
    {
        $this->cart(false)?->items()->delete();
        $this->lines = null;
    }

    /**
     * Səbət sətirləri qiymətləri ilə birlikdə.
     *
     * @return Collection<int, array{item: CartItem, product: Product, quantity: int, unit_price: float, unit_old_price: ?float, total: float, components: array, options: array}>
     */
    public function lines(): Collection
    {
        if ($this->lines !== null) {
            return $this->lines;
        }

        $cart = $this->cart(false);
        if (! $cart) {
            return $this->lines = collect();
        }

        $items = $cart->items()->with(['product.images', 'product.mainCategory', 'product.setItems.component'])->get();

        return $this->lines = $items->map(function (CartItem $item) {
            $product = $item->product;
            if (! $product || ! $product->is_active) {
                $item->delete();

                return null;
            }

            $price = $this->pricing->price($product, $item->configuration ?? []);

            return [
                'item' => $item,
                'product' => $product,
                'quantity' => (int) $item->quantity,
                'unit_price' => $price['unit_price'],
                'unit_old_price' => $price['unit_old_price'],
                'total' => round($price['unit_price'] * $item->quantity, 2),
                'old_total' => $price['unit_old_price'] ? round($price['unit_old_price'] * $item->quantity, 2) : null,
                'components' => $price['components'],
                'options' => $price['options'],
                'discount' => $price['discount'] ?? 0,
                'discount_name' => $price['discount_name'] ?? null,
            ];
        })->filter()->values();
    }

    public function count(): int
    {
        return (int) $this->lines()->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->lines()->isEmpty();
    }

    public function subtotal(): float
    {
        return round((float) $this->lines()->sum('total'), 2);
    }

    /** Endirimsiz (köhnə qiymətlərlə) məbləğ */
    public function oldSubtotal(): float
    {
        return round((float) $this->lines()->sum(fn ($l) => $l['old_total'] ?? $l['total']), 2);
    }

    public function deliveryFee(?float $subtotal = null): float
    {
        $subtotal ??= $this->subtotal();
        $fee = (float) setting('delivery.fee', 0);
        $freeFrom = (float) setting('delivery.free_from', 0);

        if ($fee <= 0 || ($freeFrom > 0 && $subtotal >= $freeFrom)) {
            return 0.0;
        }

        return $fee;
    }

    public function total(): float
    {
        $subtotal = $this->subtotal();

        return round($subtotal + $this->deliveryFee($subtotal), 2);
    }

    /** Daxil olduqda qonaq səbətini istifadəçi səbətinə köçürür */
    public function mergeGuestCartInto(User $user): void
    {
        $guest = Cart::query()->where('session_id', $this->token())->whereNull('user_id')->first();
        if (! $guest) {
            return;
        }

        $userCart = Cart::query()->firstOrCreate(['user_id' => $user->id], ['session_id' => $this->token()]);
        foreach ($guest->items as $item) {
            $existing = $userCart->items()->where('product_id', $item->product_id)->where('config_hash', $item->config_hash)->first();
            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                $item->update(['cart_id' => $userCart->id]);
            }
        }
        $guest->delete();
        $this->cart = null;
        $this->lines = null;
    }

    protected function findItem(int $itemId): ?CartItem
    {
        $cart = $this->cart(false);

        return $cart ? $cart->items()->with('product')->whereKey($itemId)->first() : null;
    }

    protected function hash(array $config): string
    {
        $components = $config['components'] ?? [];
        $options = $config['options'] ?? [];
        ksort($components);
        ksort($options);

        return sha1(json_encode([$components, $options]));
    }
}
