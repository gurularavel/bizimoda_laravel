<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        return view($request->query('popup') ? 'front.auth.login-popup' : 'front.auth.login', [
            'htmlClass' => 'route-account-login page-account bz-auth-route',
        ]);
    }

    public function login(Request $request, CartService $cart)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [], ['email' => __('E-mail'), 'password' => __('Şifrə')]);

        if (! Auth::guard('web')->attempt($credentials + ['is_active' => true], $request->boolean('remember', true))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('E-mail və ya şifrə yanlışdır.')]);
        }

        $request->session()->regenerate();
        $cart->mergeGuestCartInto(Auth::guard('web')->user());

        return $this->afterAuth($request);
    }

    public function showRegister(Request $request)
    {
        return view($request->query('popup') ? 'front.auth.register-popup' : 'front.auth.register', [
            'htmlClass' => 'route-account-register page-account bz-auth-route',
        ]);
    }

    public function register(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:64',
            'last_name' => 'nullable|string|max:64',
            'email' => 'required|email|max:120|unique:users,email',
            'phone' => 'required|string|max:30',
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
            'newsletter' => 'nullable|boolean',
        ], [], [
            'first_name' => __('Ad'), 'last_name' => __('Soyad'), 'email' => __('E-mail'),
            'phone' => __('Telefon'), 'password' => __('Şifrə'),
        ]);

        $user = User::query()->create([
            'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'newsletter' => (bool) ($data['newsletter'] ?? false),
        ]);

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();
        $cart->mergeGuestCartInto($user);

        return $this->afterAuth($request, __('Qeydiyyat uğurla tamamlandı!'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->forget(['cart_token']);
        $request->session()->regenerateToken();

        return redirect()->route('front.home');
    }

    public function showForgot()
    {
        return view('front.auth.forgot', ['htmlClass' => 'route-account-forgotten page-account bz-auth-route']);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        Password::broker('users')->sendResetLink($request->only('email'));

        // Hesabın mövcudluğunu açıqlamamaq üçün həmişə eyni cavab
        return back()->with('reset_link_sent', $request->input('email'));
    }

    public function showReset(Request $request, string $token)
    {
        return view('front.auth.reset', [
            'token' => $token,
            'email' => $request->query('email'),
            'htmlClass' => 'route-account-reset page-account bz-auth-route',
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        return redirect()->route('front.login')->with('success', __('Şifrəniz yeniləndi. İndi daxil ola bilərsiniz.'));
    }

    protected function afterAuth(Request $request, ?string $message = null)
    {
        if ($request->input('popup')) {
            return response('<script>parent.location.reload();</script>');
        }

        return redirect()->intended(lroute('front.account'))->with('success', $message);
    }
}
