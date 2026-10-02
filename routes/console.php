<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/*
 | İnterfeys tərcümələrini (database/seeders/data/ui_translations.php) lang/{az,ru,en}.json fayllarına yazır.
 | Mövcud (admin-dən redaktə olunmuş) tərcümələr --force olmadan dəyişdirilmir.
 */
Artisan::command('bizimoda:translations {--force : Mövcud tərcümələrin üzərinə yaz}', function () {
    $map = require database_path('seeders/data/ui_translations.php');
    $files = ['az' => null, 'ru' => 0, 'en' => 1];

    foreach ($files as $locale => $index) {
        $path = lang_path($locale.'.json');
        $data = is_file($path) ? (json_decode(File::get($path), true) ?: []) : [];

        foreach ($map as $key => $translations) {
            $value = $index === null ? $key : $translations[$index];
            if ($this->option('force') || ! isset($data[$key]) || $data[$key] === '') {
                $data[$key] = $value;
            }
        }

        ksort($data, SORT_NATURAL | SORT_FLAG_CASE);
        File::ensureDirectoryExists(lang_path());
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
        $this->info("{$locale}.json: ".count($data).' sətir');
    }
})->purpose('İnterfeys tərcümələrini lang/*.json fayllarına yazır');

/*
 | Endirim kampaniyalarının başlama/bitmə vaxtında siyahı qiymətlərini (computed_price) yeniləyir.
 | Serverdə cron: * * * * * php artisan schedule:run
 */
Artisan::command('bizimoda:refresh-prices', function () {
    $count = app(\App\Services\DiscountService::class)->refreshAll();
    $this->info("{$count} məhsulun qiyməti yeniləndi.");
})->purpose('Endirimlərə görə məhsul qiymətlərini yeniləyir');

\Illuminate\Support\Facades\Schedule::command('bizimoda:refresh-prices')->everyFifteenMinutes()->withoutOverlapping();
