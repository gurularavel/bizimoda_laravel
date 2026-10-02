<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Admin → Parametrlər bölməsindən idarə olunan açar/dəyər parametrləri.
 * Açarlar "group.key" formasındadır, məs. mail.host, payment.kapital_username.
 */
class SettingsRepository
{
    protected ?array $items = null;

    // Şifrələnmiş saxlanılan açarlar
    public const ENCRYPTED = ['mail.password', 'payment.kapital_password', 'social.google_client_secret', 'social.facebook_client_secret'];

    public function all(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }

        try {
            $this->items = Cache::rememberForever('settings.all', function () {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return Setting::query()->pluck('value', 'key')->all();
            });
        } catch (Throwable) {
            $this->items = [];
        }

        return $this->items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = Arr::get($this->all(), $key, $default);

        if ($value !== null && $value !== '' && in_array($key, self::ENCRYPTED, true)) {
            try {
                return decrypt($value);
            } catch (Throwable) {
                return $default;
            }
        }

        return $value ?? $default;
    }

    /** Tərcümə olunan parametr (dəyər dil açarlı massivdir) */
    public function translated(string $key, ?string $locale = null, mixed $default = null): mixed
    {
        $value = $this->get($key);
        if (is_array($value)) {
            $locale ??= app()->getLocale();

            return $value[$locale] ?? $value[config('app.fallback_locale')] ?? $default;
        }

        return $value ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        if (in_array($key, self::ENCRYPTED, true)) {
            if ($value === null || $value === '') {
                return; // boş göndərilibsə köhnə şifrə qalsın
            }
            $value = encrypt($value);
        }

        Setting::query()->updateOrCreate(['key' => $key], ['group' => explode('.', $key)[0], 'value' => $value]);
        $this->flush();
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function flush(): void
    {
        $this->items = null;
        Cache::forget('settings.all');
    }
}
