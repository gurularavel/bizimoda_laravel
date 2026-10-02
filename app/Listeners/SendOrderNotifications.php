<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\NewOrderAdminMail;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Yeni sifariş: admin(lər)ə (Parametrlər → Mail → bildiriş e-poçtları) və müştəriyə e-poçt.
 * Mail xətası sifarişi pozmasın deyə istisnalar yalnız loglanır.
 */
class SendOrderNotifications
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->load('items.components');

        $admins = array_filter(array_map('trim', preg_split('/[,;\s]+/', (string) setting('mail.admin_emails', ''))));
        if ($admins) {
            try {
                Mail::to($admins)->send(new NewOrderAdminMail($order));
            } catch (Throwable $e) {
                report($e);
            }
        }

        if ($order->email && setting('mail.customer_confirmation', true)) {
            try {
                Mail::to($order->email)->locale($order->locale)->send(new OrderConfirmationMail($order));
            } catch (Throwable $e) {
                report($e);
            }
        }
    }
}
