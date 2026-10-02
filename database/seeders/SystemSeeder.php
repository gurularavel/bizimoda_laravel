<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Language;
use App\Services\SettingsRepository;
use App\Support\Locales;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['az', 'Azərbaycan', true], ['ru', 'Русский', false], ['en', 'English', false]] as $i => [$code, $name, $default]) {
            Language::query()->updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true, 'is_default' => $default, 'sort' => $i]);
        }
        Locales::flush();

        Admin::query()->updateOrCreate(['email' => 'admin@bizimoda.az'], [
            'name' => 'Administrator',
            'password' => env('ADMIN_PASSWORD', 'admin12345'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        app(SettingsRepository::class)->setMany([
            'site.name' => 'bizimoda',
            'site.logo' => null,
            'site.favicon' => null,
            'site.meta_title' => ['az' => 'bizimOda — mebel və ev tekstili', 'ru' => 'bizimOda — мебель и домашний текстиль', 'en' => 'bizimOda — furniture and home textiles'],
            'site.meta_description' => ['az' => 'Mebel və ev tekstili, kreditlə mebellər. Yataq otağı, gənc otağı, qonaq otağı, divan, masa və s.', 'ru' => 'Мебель и домашний текстиль', 'en' => 'Furniture and home textiles'],
            'site.whatsapp' => '994702177121',
            'site.notification_text' => ['az' => "Mebel və ev tekstili, kreditlə mebellər\nYataq otağı, gənc otağı, qonaq otağı, divan, masa və s.", 'ru' => "Мебель и домашний текстиль, мебель в кредит", 'en' => 'Furniture and home textiles'],
            'site.footer_banner' => 'uploads/demo/slider-2.jpg',
            'site.footer_banner_link' => '/az/specials',
            'site.footer_contact_title' => ['az' => 'Bizimlə əlaqə', 'ru' => 'Свяжитесь с нами', 'en' => 'Contact us'],
            'site.head_scripts' => '',
            'site.body_scripts' => '',

            'contact.address' => ['az' => "Qara Qarayev prospekti 47,\nBakı, Azərbaycan", 'ru' => "пр. Гара Гараева 47,\nБаку, Азербайджан", 'en' => "47 Gara Garayev ave,\nBaku, Azerbaijan"],
            'contact.phones' => ['az' => 'info@bizimoda.az,  +994702177121, +994124215511', 'ru' => 'info@bizimoda.az,  +994702177121, +994124215511', 'en' => 'info@bizimoda.az,  +994702177121, +994124215511'],
            'contact.phone_link' => '+994702177121',
            'contact.hours' => ['az' => "Həftəiçi: 10:00 - 19:00\nHəftəsonu: 11:00 - 20:00", 'ru' => "Будни: 10:00 - 19:00\nВыходные: 11:00 - 20:00", 'en' => "Weekdays: 10:00 - 19:00\nWeekends: 11:00 - 20:00"],
            'contact.stores' => ['az' => 'Həzi Aslanov metrosu, Neftçilər metrosu, 3-cü mikrorayon dairəsi, Xırdalan', 'ru' => 'м. Гази Асланов, м. Нефтчиляр, круг 3-го микрорайона, Хырдалан', 'en' => 'Hazi Aslanov metro, Neftchilar metro, 3rd microdistrict, Khirdalan'],
            'contact.subjects' => ['az' => "Ünvanlar haqqında\nBizə təklifiniz\nŞikayətiniz", 'ru' => "Об адресах\nВаше предложение\nЖалоба", 'en' => "About addresses\nYour suggestion\nComplaint"],

            'mail.admin_emails' => 'info@bizimoda.az',
            'mail.customer_confirmation' => true,

            'delivery.fee' => 0,
            'delivery.free_from' => 0,
            'delivery.title' => ['az' => 'Kuryer ilə çatdırılma və quraşdırılma', 'ru' => 'Доставка курьером и сборка', 'en' => 'Courier delivery and assembly'],

            'payment.cod_enabled' => true,
            'payment.cod_title' => ['az' => 'Qapıda nağd ödəniş', 'ru' => 'Оплата наличными при доставке', 'en' => 'Cash on delivery'],
            'payment.kapital_enabled' => false,
            'payment.kapital_mode' => 'test',
            'payment.kapital_type_rid' => 'Order_SMS',
            'payment.kapital_title' => ['az' => 'Kapital Bank kartı ilə onlayn ödəniş', 'ru' => 'Онлайн оплата картой Kapital Bank', 'en' => 'Online payment with Kapital Bank card'],

            'blog.title' => ['az' => 'Bloq', 'ru' => 'Блог', 'en' => 'Blog'],
        ]);
    }
}
