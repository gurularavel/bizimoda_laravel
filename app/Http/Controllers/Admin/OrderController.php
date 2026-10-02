<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Payments\KapitalBankGateway;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->withCount('items')->latest();

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('number', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('first_name', 'like', "%{$q}%")->orWhere('last_name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }
        foreach (['status', 'payment_method', 'payment_status', 'source'] as $field) {
            if ($value = $request->query($field)) {
                $query->where($field, $value);
            }
        }
        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(30)->withQueryString(),
            'sum' => (clone $query)->getQuery()->cloneWithout(['orders', 'limit', 'offset'])->sum('total'),
        ]);
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', ['order' => $order->load(['items.components', 'items.product', 'histories.admin', 'payments', 'user'])]);
    }

    public function print(Order $order)
    {
        return view('admin.orders.print', ['order' => $order->load('items.components')]);
    }

    public function status(Request $request, Order $order, OrderService $orders)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'comment' => 'nullable|string|max:1000',
        ]);
        $orders->changeStatus($order, $data['status'], $data['comment'] ?? null, auth('admin')->id());

        return back()->with('success', 'Sifariş statusu yeniləndi.');
    }

    public function paymentStatus(Request $request, Order $order)
    {
        $data = $request->validate(['payment_status' => ['required', Rule::in(array_keys(Order::PAYMENT_STATUSES))]]);
        $order->update($data);

        return back()->with('success', 'Ödəniş statusu yeniləndi.');
    }

    /** Kapital Bank-dan ödəniş statusunu yenidən soruş */
    public function verifyPayment(Order $order, KapitalBankGateway $gateway)
    {
        $payment = $order->payments()->where('provider', 'kapital')->latest('id')->first();
        if (! $payment) {
            return back()->with('error', 'Bu sifariş üçün kart ödənişi tapılmadı.');
        }

        try {
            $gateway->verify($payment);
        } catch (Throwable $e) {
            return back()->with('error', 'Bankdan cavab alınmadı: '.$e->getMessage());
        }

        return back()->with('success', 'Bank statusu: '.$payment->fresh()->status);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Sifariş silindi.');
    }
}
