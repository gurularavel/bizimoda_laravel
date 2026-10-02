<?php

namespace App\Http\Controllers\Admin;

use App\Models\FormSubmission;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $valid = fn ($q) => $q->where('status', '!=', 'cancelled');

        return view('admin.dashboard', [
            'stats' => [
                'today_orders' => Order::query()->whereDate('created_at', today())->count(),
                'today_sales' => (float) Order::query()->whereDate('created_at', today())->tap($valid)->sum('total'),
                'month_sales' => (float) Order::query()->where('created_at', '>=', now()->startOfMonth())->tap($valid)->sum('total'),
                'new_orders' => Order::query()->where('status', 'new')->count(),
                'products' => Product::query()->where('is_active', true)->count(),
                'customers' => User::query()->count(),
                'unread_forms' => FormSubmission::query()->where('is_read', false)->count(),
            ],
            'latestOrders' => Order::query()->latest()->limit(10)->get(),
            'topProducts' => \App\Models\OrderItem::query()
                ->selectRaw('product_id, name, SUM(quantity) as qty, SUM(total) as revenue')
                ->groupBy('product_id', 'name')->orderByDesc('qty')->limit(5)->get(),
        ]);
    }
}
