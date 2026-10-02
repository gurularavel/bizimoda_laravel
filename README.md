# bizimoda.az — Laravel + Blade

OpenCart 3 (Journal3 teması) üzərindəki bizimoda.az saytının Laravel 12 + Blade ilə yenidən yazılmış versiyası.
Dizayn orijinal Journal3 CSS/JS asset-ləri ilə eynilə saxlanılıb.

## Quraşdırma

```bash
composer install
cp .env.example .env && php artisan key:generate
# .env: DB_*, APP_URL, ADMIN_PASSWORD
php artisan migrate --seed          # demo kataloq, menyular, səhifələr, admin
php artisan storage:link
php artisan bizimoda:translations   # interfeys tərcümələri (lang/*.json)
```

Admin panel: `/admin` — `admin@bizimoda.az` / `.env`-dəki `ADMIN_PASSWORD` (ilk girişdən sonra dəyişin).

Web server document root: `public/`. Apache üçün Laravel-in standart `public/.htaccess` faylı kifayətdir.

## Əsas imkanlar

| Bölmə | Harada |
|---|---|
| Dillər `/az`, `/ru`, `/en` | Admin → Dillər; mətnlər hər formada AZ/RU/EN tabları; interfeys sözləri Admin → Tərcümələr |
| Dəst / modul məntiqi | Məhsul tipi "Dəst" → "Dəst modulları" tabı: standart/min/maks say, məcburi, qiymət override. Qiymət `app/Services/SetPricingService.php`-də hesablanır |
| Əsas + əlavə kateqoriyalar | Məhsul → "Kateqoriyalar" tabı (əsas kateqoriya məcburidir, URL ona görə qurulur) |
| Filtrlər | Kateqoriya → "Filtrlər" tabı (qiymət, altbaşlıq, stok, brend, xüsusiyyət, opsiyon/rəng); alt kateqoriyalar miras alır |
| Opsiyonlar (rəng və s.) | Admin → Opsiyonlar; məhsulda qiymət əlavəsi ₼ və ya % |
| Meqa menyu | Admin → Menyular: kateqoriya/məhsul/səhifə/bloq/daxili/xarici link, flyout → mega → sütun qrupları, banner |
| Səhifələr | Admin → Səhifələr: redaktor + bloklar (mətn, şəkil, banner, məhsul karuseli, FAQ, HTML, forma) |
| Bloq | Admin → Bloq yazıları / kateqoriyaları |
| Ödəniş | Qapıda nağd + Kapital Bank E-commerce REST (Admin → Parametrlər → Ödəniş) |
| Sifariş e-poçtları | Admin → Parametrlər → E-poçt (SMTP): bildiriş ünvanları, SMTP, test məktubu |
| Google / Facebook giriş | Admin → Parametrlər → Sosial giriş (callback URL-lər orada göstərilir) |
| Köhnə URL-lər | Admin → Redirect-lər (301/302) |

## Texniki qeydlər

- Dizayn: `public/catalog/view/...` — canlı saytdan götürülmüş Journal3 asset-ləri. Səhifəyə xas generasiya olunmuş CSS
  `stylesheet/generated.css`-də route siniflərinə (`.route-product-category` və s.) bağlanıb.
- Journal3 JS-in AJAX sorğuları təmiz `/ajax/{route}` ünvanlarına gedir (`index.php` yoxdur) və `LegacyController`-də emal olunur,
  (orijinal JS-də yalnız URL-lər dəyişdirilib). Canlı saytın köhnə BundleExpert say kodu `js/common.js`-dən çıxarılıb,
  əvəzində `js/bizimoda-shop.js` (min/max say + server qiyməti) işləyir.
- Testlər MySQL `bizimoda_test` bazasında işləyir: `php artisan test`.
