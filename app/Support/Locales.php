<?php

namespace App\Support;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Saytın dilləri. Mənbə — admin paneldəki "Dillər" bölməsi (languages cədvəli),
 * cədvəl hələ yoxdursa config/locales.php.
 */
class Locales
{
    protected static ?array $cache = null;

    /** @return array<string, array{code:string,name:string,is_default:bool}> */
    public static function all(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        try {
            $rows = Cache::rememberForever('languages.active', function () {
                if (! Schema::hasTable('languages')) {
                    return [];
                }

                return Language::query()->where('is_active', true)->orderBy('sort')
                    ->get(['code', 'name', 'is_default'])->keyBy('code')->map->toArray()->all();
            });
        } catch (Throwable) {
            $rows = [];
        }

        if (! $rows) {
            $rows = collect(config('locales.supported'))
                ->mapWithKeys(fn ($name, $code) => [$code => ['code' => $code, 'name' => $name, 'is_default' => $code === config('locales.default')]])
                ->all();
        }

        return static::$cache = $rows;
    }

    public static function codes(): array
    {
        return array_keys(static::all());
    }

    public static function default(): string
    {
        foreach (static::all() as $code => $row) {
            if ($row['is_default']) {
                return $code;
            }
        }

        return config('locales.default', 'az');
    }

    public static function isSupported(?string $code): bool
    {
        return $code !== null && array_key_exists($code, static::all());
    }

    public static function pattern(): string
    {
        // Route pattern hər zaman bütün konfiqurasiya dillərini qəbul edir; aktivlik middleware-də yoxlanılır.
        return implode('|', array_keys(config('locales.supported')));
    }

    public static function flush(): void
    {
        static::$cache = null;
        Cache::forget('languages.active');
    }
}
