<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SystemSeeder::class,      // dillər, admin, parametrlər
            CatalogSeeder::class,     // kateqoriya ağacı, opsiyonlar, xüsusiyyətlər, demo məhsullar (dəst + modullar)
            ContentSeeder::class,     // səhifələr, menyular, slayder, ana səhifə bölmələri, bloq
        ]);
    }
}
