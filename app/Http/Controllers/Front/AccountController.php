<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AccountController extends Controller
{
    protected array $view = ['htmlClass' => 'route-account-account page-account layout-6 one-column column-right'];

    public function index()
    {
        $user = Auth::guard('web')->user();

        return view('front.account.index', $this->view + [
            'user' => $user,
            'orders' => $user->orders()->latest()->limit(5)->get(),
        ]);
    }

    public function edit()
    {
        return view('front.account.edit', $this->view + ['user' => Auth::guard('web')->user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::guard('web')->user();
        $data = $request->validate([
            'name' => 'required|string|max:128',
            'email' => ['required', 'email', 'max:120', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'newsletter' => 'nullable|boolean',
        ]);
        $user->update($data + ['newsletter' => $request->boolean('newsletter')]);

        return redirect()->route('front.account')->with('success', __('Məlumatlarınız yeniləndi.'));
    }

    public function password()
    {
        return view('front.account.password', $this->view);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('web')->user();
        $request->validate([
            'current_password' => $user->password ? 'required|string' : 'nullable',
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
        ]);

        if ($user->password && ! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => __('Cari şifrə yanlışdır.')]);
        }

        $user->update(['password' => $request->input('password')]);

        return redirect()->route('front.account')->with('success', __('Şifrəniz dəyişdirildi.'));
    }

    public function addresses()
    {
        return view('front.account.addresses', $this->view + [
            'addresses' => Auth::guard('web')->user()->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:64',
            'last_name' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:30',
            'city' => 'nullable|string|max:120',
            'address' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);
        $user = Auth::guard('web')->user();
        if ($request->boolean('is_default')) {
            $user->addresses()->update(['is_default' => false]);
        }
        $user->addresses()->create($data + ['is_default' => $request->boolean('is_default')]);

        return back()->with('success', __('Ünvan əlavə edildi.'));
    }

    public function deleteAddress(Address $address)
    {
        abort_unless($address->user_id === Auth::guard('web')->id(), 403);
        $address->delete();

        return back()->with('success', __('Ünvan silindi.'));
    }

    public function orders()
    {
        return view('front.account.orders', $this->view + [
            'orders' => Auth::guard('web')->user()->orders()->latest()->paginate(10),
        ]);
    }

    public function order(string $number)
    {
        $order = Auth::guard('web')->user()->orders()->where('number', $number)->with(['items.components', 'histories'])->firstOrFail();

        return view('front.account.order', $this->view + ['order' => $order]);
    }

    /** Qonaqlar üçün sessiyada, istifadəçilər üçün bazada */
    public function wishlist()
    {
        $user = Auth::guard('web')->user();
        $ids = $user ? $user->wishlist()->pluck('products.id')->all() : session('wishlist', []);

        return view('front.account.wishlist', $this->view + [
            'products' => Product::query()->visible()->forListing()->whereIn('id', $ids)->get(),
        ]);
    }

    public function wishlistRemove(Product $product)
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            $user->wishlist()->detach($product->id);
        } else {
            session(['wishlist' => array_values(array_diff(session('wishlist', []), [$product->id]))]);
        }

        return redirect()->route('front.wishlist')->with('success', __('Məhsul arzu siyahısından silindi.'));
    }
}
