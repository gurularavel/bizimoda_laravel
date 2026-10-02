<?php

namespace App\Http\Controllers\Front;

use App\Events\OrderPlaced;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Payments\KapitalBankGateway;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Kapital Bank HPP-dən qayıdış. URL imzalıdır (KapitalBankGateway::start), status isə
     * həmişə bankın API-si ilə yoxlanılır — query parametrlərinə etibar edilmir.
     */
    public function kapitalReturn(Request $request, KapitalBankGateway $gateway)
    {
        abort_unless($request->hasValidSignatureWhileIgnoring(['ID', 'STATUS', 'id', 'status']), 403);

        $order = Order::query()->where('number', (string) $request->query('order'))->firstOrFail();
        $payment = $order->payments()->where('provider', 'kapital')->latest('id')->first();
        abort_unless($payment, 404);

        $wasPaid = $order->payment_status === 'paid';

        try {
            $gateway->verify($payment);
        } catch (Throwable $e) {
            report($e);
        }

        $order->refresh();
        session()->push('my_orders', $order->number);

        if ($order->payment_status === 'paid') {
            if (! $wasPaid) {
                event(new OrderPlaced($order));
            }

            return redirect()->route('front.checkout.success', ['locale' => $order->locale, 'number' => $order->number]);
        }

        return redirect()->route('front.checkout.failed', ['locale' => $order->locale, 'number' => $order->number]);
    }
}
