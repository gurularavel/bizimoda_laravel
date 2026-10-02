<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::query()->withCount('orders')->withSum('orders', 'total')
            ->when($q = trim((string) $request->query('q')), fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function edit(User $customer)
    {
        return view('admin.customers.edit', [
            'customer' => $customer->load(['addresses']),
            'orders' => $customer->orders()->latest()->get(),
        ]);
    }

    public function update(Request $request, User $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:128',
            'email' => ['required', 'email', Rule::unique('users')->ignore($customer->id)],
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
        ]);
        $customer->fill(collect($data)->except('password')->all() + ['is_active' => $request->boolean('is_active'), 'newsletter' => $request->boolean('newsletter')]);
        if (! empty($data['password'])) {
            $customer->password = $data['password'];
        }
        $customer->save();

        return back()->with('success', 'Müştəri yeniləndi.');
    }

    public function destroy(User $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Müştəri silindi.');
    }
}
