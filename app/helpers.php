<?php

use App\Services\ImageService;
use App\Services\SettingsRepository;
use App\Support\Locales;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsRepository::class)->get($key, $default);
    }
}

if (! function_exists('setting_t')) {
    /** Dilə görə tərcümə olunan parametr */
    function setting_t(string $key, mixed $default = null): mixed
    {
        return app(SettingsRepository::class)->translated($key, null, $default);
    }
}

if (! function_exists('money')) {
    /**
     * Saytdakı format: "1,041 ₼", kəsr hissə varsa "316.3 ₼".
     */
    function money(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $amount = round((float) $amount, 2);
        $decimals = fmod($amount, 1.0) == 0.0 ? 0 : 2;
        $formatted = number_format($amount, $decimals, '.', ',');
        if ($decimals) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $withSymbol ? $formatted.' ₼' : $formatted;
    }
}

if (! function_exists('money_plain')) {
    /** Kəsr hissəsi ilə, minlik ayırıcısız (data-atributları üçün) */
    function money_plain(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}

if (! function_exists('thumb')) {
    function thumb(?string $path, int $width, int $height, string $mode = 'contain'): string
    {
        return app(ImageService::class)->thumb($path, $width, $height, $mode);
    }
}

if (! function_exists('image_url')) {
    function image_url(?string $path): string
    {
        return app(ImageService::class)->url($path);
    }
}

if (! function_exists('lroute')) {
    /** Cari dil prefiksi ilə route */
    function lroute(string $name, array $parameters = [], ?string $locale = null): string
    {
        return route($name, ['locale' => $locale ?? app()->getLocale()] + $parameters);
    }
}

if (! function_exists('locales')) {
    function locales(): array
    {
        return Locales::all();
    }
}

if (! function_exists('tr')) {
    /** JSON/massiv tərcümə dəyərindən cari dili götürür */
    function tr(mixed $value, ?string $locale = null): ?string
    {
        if (! is_array($value)) {
            return $value;
        }
        $locale ??= app()->getLocale();

        return $value[$locale] ?? $value[Locales::default()] ?? (array_values(array_filter($value))[0] ?? null);
    }
}
