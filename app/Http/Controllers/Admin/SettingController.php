<?php

namespace App\Http\Controllers\Admin;

use App\Mail\TestMail;
use App\Models\Page;
use App\Services\ImageService;
use App\Services\MenuBuilder;
use App\Services\SettingsRepository;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SettingController extends Controller
{
    public function __construct(protected SettingsRepository $settings) {}

    /** Parametr sxemi: qrup → [açar => [tip, etiket, kömək mətni, seçimlər]] */
    protected function schema(): array
    {
        return [
            'general' => ['Ümumi', [
                'site.name' => ['text', 'Saytın adı'],
                'site.logo' => ['image', 'Loqo', 'Boş qalarsa standart loqo istifadə olunur.'],
                'site.favicon' => ['image', 'Favicon'],
                'site.meta_title' => ['trans', 'Ana səhifə meta başlığı'],
                'site.meta_description' => ['trans_textarea', 'Ana səhifə meta təsviri'],
                'site.whatsapp' => ['text', 'WhatsApp nömrəsi', 'Məs. 994702177121 — sağ aşağı küncdəki düymə. Boş qalarsa gizlənir.'],
                'site.notification_text' => ['trans_textarea', 'Aşağı bildiriş zolağının mətni', 'Boş qalarsa göstərilmir.'],
                'site.footer_banner' => ['image', 'Footer banneri'],
                'site.footer_banner_link' => ['text', 'Footer banner linki'],
                'site.footer_contact_title' => ['trans', 'Footer: "Bizimlə əlaqə" başlığı'],
                'checkout.terms_page_id' => ['page', 'Sifarişdə razılaşdırılan qaydalar səhifəsi', 'Seçilərsə checkout-da "razıyam" qutusu məcburi olur.'],
                'checkout.default_city' => ['trans', 'Checkout: standart şəhər'],
                'blog.title' => ['trans', 'Bloq başlığı'],
            ]],
            'contact' => ['Əlaqə', [
                'contact.address' => ['trans_textarea', 'Ünvan'],
                'contact.phones' => ['trans', 'Telefon / e-poçt mətni'],
                'contact.phone_link' => ['text', 'Klikləndikdə zəng ediləcək nömrə'],
                'contact.hours' => ['trans_textarea', 'İş saatları'],
                'contact.stores' => ['trans_textarea', 'Mağaza əraziləri'],
                'contact.subjects' => ['trans_textarea', 'Əlaqə formasının mövzuları', 'Hər sətirdə bir mövzu.'],
                'contact.map_embed' => ['code', 'Xəritə (iframe embed kodu)'],
            ]],
            'mail' => ['E-poçt (SMTP)', [
                'mail.admin_emails' => ['text', 'Sifariş bildirişləri gələcək e-poçtlar', 'Vergüllə bir neçə ünvan: info@bizimoda.az, satis@bizimoda.az'],
                'mail.customer_confirmation' => ['bool', 'Müştəriyə sifariş təsdiqi göndər'],
                'mail.mailer' => ['select', 'Göndərmə üsulu', null, ['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'log' => 'Log (test üçün, göndərmir)']],
                'mail.host' => ['text', 'SMTP host', 'Məs. smtp.gmail.com, smtp.yandex.com, mail.bizimoda.az'],
                'mail.port' => ['number', 'SMTP port', '587 (TLS) və ya 465 (SSL)'],
                'mail.encryption' => ['select', 'Şifrələmə', null, ['tls' => 'TLS', 'ssl' => 'SSL', '' => 'Yoxdur']],
                'mail.username' => ['text', 'SMTP istifadəçi adı'],
                'mail.password' => ['password', 'SMTP şifrəsi', 'Dəyişmək istəmirsinizsə boş qoyun.'],
                'mail.from_address' => ['text', 'Göndərən e-poçt (From)'],
                'mail.from_name' => ['text', 'Göndərən adı'],
            ]],
            'payment' => ['Ödəniş', [
                'payment.cod_enabled' => ['bool', 'Qapıda nağd ödəniş aktivdir'],
                'payment.cod_title' => ['trans', 'Qapıda ödəniş — adı'],
                'payment.kapital_enabled' => ['bool', 'Kapital Bank kartla ödəniş aktivdir'],
                'payment.kapital_title' => ['trans', 'Kartla ödəniş — adı'],
                'payment.kapital_mode' => ['select', 'Kapital Bank rejimi', null, ['test' => 'Test (txpgtst.kapitalbank.az)', 'live' => 'Real (e-commerce.kapitalbank.az)']],
                'payment.kapital_username' => ['text', 'Kapital API istifadəçi adı', 'Bankın verdiyi e-commerce API login.'],
                'payment.kapital_password' => ['password', 'Kapital API şifrəsi', 'Dəyişmək istəmirsinizsə boş qoyun.'],
                'payment.kapital_type_rid' => ['select', 'Əməliyyat tipi', null, ['Order_SMS' => 'Order_SMS (birbaşa ödəniş)', 'Order_DMS' => 'Order_DMS (bloklama + təsdiq)']],
            ]],
            'delivery' => ['Çatdırılma', [
                'delivery.title' => ['trans', 'Çatdırılma üsulunun adı'],
                'delivery.fee' => ['number', 'Çatdırılma haqqı (₼)', '0 — pulsuz'],
                'delivery.free_from' => ['number', 'Bu məbləğdən yuxarı pulsuz (₼)', '0 — həmişə yuxarıdakı haqq'],
            ]],
            'social' => ['Sosial giriş', [
                'social.google_client_id' => ['text', 'Google Client ID'],
                'social.google_client_secret' => ['password', 'Google Client Secret'],
                'social.facebook_client_id' => ['text', 'Facebook App ID'],
                'social.facebook_client_secret' => ['password', 'Facebook App Secret'],
            ]],
            'scripts' => ['Skriptlər', [
                'site.head_scripts' => ['code', '<head> içinə kod (Google Tag Manager, Meta Pixel...)'],
                'site.body_scripts' => ['code', '<body> başlanğıcına kod (GTM noscript...)'],
            ]],
        ];
    }

    public function edit(?string $group = 'general')
    {
        $schema = $this->schema();
        abort_unless(isset($schema[$group]), 404);

        return view('admin.settings.edit', [
            'schema' => $schema,
            'group' => $group,
            'settings' => $this->settings,
            'pages' => Page::query()->orderBy('sort')->get(),
            'callbacks' => ['google' => url('/auth/google/callback'), 'facebook' => url('/auth/facebook/callback')],
        ]);
    }

    public function update(Request $request, string $group)
    {
        $schema = $this->schema();
        abort_unless(isset($schema[$group]), 404);
        $images = app(ImageService::class);

        foreach ($schema[$group][1] as $key => $def) {
            $field = str_replace('.', '__', $key);
            $type = $def[0];

            $value = match ($type) {
                'bool' => $request->boolean($field),
                'number' => (float) $request->input($field, 0),
                'trans', 'trans_textarea' => array_filter(array_intersect_key((array) $request->input($field, []), array_flip(Locales::codes())), fn ($v) => trim((string) $v) !== ''),
                'image' => $this->imageSetting($request, $field, $key, $images),
                'page' => $request->integer($field) ?: null,
                default => $request->input($field),
            };

            $this->settings->set($key, $value);
        }

        Cache::forget('sitemap.xml');
        MenuBuilder::flush();

        return redirect()->route('admin.settings.edit', $group)->with('success', 'Parametrlər yadda saxlanıldı.');
    }

    protected function imageSetting(Request $request, string $field, string $key, ImageService $images): ?string
    {
        $current = $this->settings->get($key);
        if ($request->hasFile($field)) {
            $request->validate([$field => 'image|max:4096']);

            return $images->store($request->file($field), 'settings');
        }

        return $request->boolean($field.'_remove') ? null : $current;
    }

    public function testMail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Mail::to($request->input('email'))->send(new TestMail);
        } catch (Throwable $e) {
            return back()->with('error', 'Göndərilmədi: '.$e->getMessage());
        }

        return back()->with('success', 'Test məktubu '.$request->input('email').' ünvanına göndərildi.');
    }

    public function clearCache()
    {
        Artisan::call('optimize:clear');
        $this->settings->flush();
        Locales::flush();
        MenuBuilder::flush();

        return back()->with('success', 'Keş təmizləndi.');
    }
}
