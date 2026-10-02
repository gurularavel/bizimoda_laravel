<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Google / Facebook ilə giriş. Açarlar admin → Parametrlər → Sosial giriş bölməsindən.
 * Callback URL-lər: {site}/auth/google/callback, {site}/auth/facebook/callback
 */
class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        abort_unless(config("services.{$provider}.client_id"), 404);
        session(['social_return' => url()->previous()]);

        $driver = Socialite::driver($provider);
        if ($provider === 'facebook') {
            $driver->scopes(['email']);
        }

        return $driver->redirect();
    }

    public function callback(string $provider, CartService $cart)
    {
        try {
            $social = Socialite::driver($provider)->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('front.login')->with('error', __('Sosial şəbəkə ilə giriş alınmadı. Yenidən cəhd edin.'));
        }

        $email = $social->getEmail();
        $user = User::query()->where('provider', $provider)->where('provider_id', $social->getId())->first()
            ?? ($email ? User::query()->where('email', $email)->first() : null);

        if (! $user) {
            $user = User::query()->create([
                'name' => $social->getName() ?: Str::before((string) $email, '@'),
                'email' => $email ?: $provider.'_'.$social->getId().'@users.noreply',
                'provider' => $provider,
                'provider_id' => $social->getId(),
                'avatar' => $social->getAvatar(),
                'email_verified_at' => $email ? now() : null,
            ]);
        } elseif (! $user->provider_id) {
            $user->update(['provider' => $provider, 'provider_id' => $social->getId(), 'avatar' => $user->avatar ?: $social->getAvatar()]);
        }

        if (! $user->is_active) {
            return redirect()->route('front.login')->with('error', __('Hesabınız deaktiv edilib.'));
        }

        Auth::guard('web')->login($user, true);
        session()->regenerate();
        $cart->mergeGuestCartInto($user);

        $return = session()->pull('social_return');

        return redirect($return && ! str_contains($return, '/auth/') ? $return : lroute('front.account'));
    }
}
