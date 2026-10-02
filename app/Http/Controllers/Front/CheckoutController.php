<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Page;
use App\Payments\KapitalBankGateway;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(protected CartService $cart, protected OrderService $orders) {}

    public static function paymentMethods(): array
    {
        $methods = [];
        if (setting('payment.cod_enabled', true)) {
            $methods['cod'] = setting_t('payment.cod_title') ?: __('Qapıda nağd ödəniş');
        }
        if (setting('payment.kapital_enabled', false)) {
            $methods['kapital'] = setting_t('payment.kapital_title') ?: __('Kapital Bank kartı ilə onlayn ödəniş');
        }

        return $methods;
    }

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('front.cart');
        }

        $user = Auth::guard('web')->user();

        return view('front.checkout.index', [
            'lines' => $this->cart->lines(),
            'subtotal' => $this->cart->subtotal(),
            'deliveryFee' => $this->cart->deliveryFee(),
            'total' => $this->cart->total(),
            'paymentMethods' => self::paymentMethods(),
            'user' => $user,
            'addresses' => $user?->addresses()->orderByDesc('is_default')->get() ?? collect(),
            'termsPage' => ($id = setting('checkout.terms_page_id')) ? Page::query()->find($id) : null,
            'htmlClass' => 'route-checkout-checkout layout-7',
        ]);
    }

    public function store(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('front.cart');
        }

        $methods = self::paymentMethods();
        $termsRequired = (bool) setting('checkout.terms_page_id');

        $data = $request->validate([
            'first_name' => 'required|string|max:64',
            'last_name' => 'nullable|string|max:64',
            'email' => 'nullable|email|max:120',
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+994[0-9]{9}$/'],
            'city' => 'nullable|string|max:120',
            'address' => 'required|string|max:255',
            'comment' => 'nullable|string|max:2000',
            'payment_method' => ['required', Rule::in(array_keys($methods))],
            'agree' => $termsRequired ? 'accepted' : 'nullable',
        ], [], [
            'first_name' => __('Ad'), 'last_name' => __('Soyad'), 'email' => __('E-mail'), 'phone' => __('Telefon'),
            'city' => __('Şəhər'), 'address' => __('Ünvan'), 'payment_method' => __('Ödəniş üsulu'), 'agree' => __('Qaydalar'),
        ]);

        $user = Auth::guard('web')->user();
        $order = $this->orders->placeFromCart(collect($data)->except(['payment_method', 'agree'])->all(), $data['payment_method'], $user);

        if ($user && $request->boolean('save_address')) {
            $user->addresses()->firstOrCreate(
                ['address' => $data['address'], 'city' => $data['city'] ?? null],
                ['first_name' => $data['first_name'], 'last_name' => $data['last_name'] ?? null, 'phone' => $data['phone']]
            );
        }

        session()->push('my_orders', $order->number);

        if ($order->payment_method === 'kapital') {
            try {
                $redirect = app(KapitalBankGateway::class)->start($order);

                return redirect()->away($redirect);
            } catch (Throwable $e) {
                report($e);
                $order->update(['payment_status' => 'failed']);

                return redirect()->route('front.checkout.failed', $order->number);
            }
        }

        return redirect()->route('front.checkout.success', $order->number);
    }

    public function success(string $number)
    {
        $order = $this->ownOrder($number);

        return view('front.checkout.result', [
            'order' => $order,
            'success' => true,
            'htmlClass' => 'route-checkout-success layout-7',
        ]);
    }

    public function failed(string $number)
    {
        $order = $this->ownOrder($number);

        return view('front.checkout.result', [
            'order' => $order,
            'success' => false,
            'htmlClass' => 'route-checkout-failure layout-7',
        ]);
    }

    /** Sifarişi yalnız onu verən sessiya/istifadəçi görə bilər */
    protected function ownOrder(string $number): Order
    {
        $order = Order::query()->where('number', $number)->with(['items.components', 'items.product.images'])->firstOrFail();
        $user = Auth::guard('web')->user();

        abort_unless(in_array($number, session('my_orders', []), true) || ($user && $order->user_id === $user->id), 404);

        return $order;
    }
}
