<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\Concerns\CartResponses;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    use CartResponses;

    public function __construct(protected CartService $cart) {}

    public function index()
    {
        return view('front.cart.index', [
            'lines' => $this->cart->lines(),
            'subtotal' => $this->cart->subtotal(),
            'oldSubtotal' => $this->cart->oldSubtotal(),
            'deliveryFee' => $this->cart->deliveryFee(),
            'total' => $this->cart->total(),
            'htmlClass' => 'route-checkout-cart layout-7',
        ]);
    }

    /** Məhsul səhifəsindən (dəst modulları və opsiyonlarla) səbətə əlavə */
    public function add(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        try {
            $this->cart->add($product, (int) $request->input('quantity', 1), $request->only(['components', 'options']));
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()]);
        }

        return response()->json($this->cartAddedResponse($product, $this->cart));
    }

    public function update(Request $request)
    {
        $this->cart->update((int) $request->input('key'), (int) $request->input('quantity'));

        return response()->json(['success' => true] + $this->cartTotals($this->cart));
    }
}
