<?php

namespace App\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use RuntimeException;

/**
 * Kapital Bank E-commerce (REST) inteqrasiyası.
 *
 *  1. POST {base}/order  (Basic auth)  → order.id, order.password, order.hppUrl
 *  2. Müştəri {hppUrl}?id={id}&password={password} ünvanına yönləndirilir
 *  3. Bank müştərini hppRedirectUrl-ə qaytarır, status GET {base}/order/{id} ilə yoxlanılır
 *
 * Parametrlər admin → Parametrlər → Ödəniş bölməsindən gəlir.
 */
class KapitalBankGateway implements PaymentGateway
{
    public const PAID_STATUSES = ['FullyPaid', 'Funded'];

    public const FAILED_STATUSES = ['Declined', 'Rejected', 'Cancelled', 'Expired', 'Refused', 'Closed'];

    public function baseUrl(): string
    {
        return setting('payment.kapital_mode', 'test') === 'live'
            ? 'https://e-commerce.kapitalbank.az/api'
            : 'https://txpgtst.kapitalbank.az/api';
    }

    protected function client(): PendingRequest
    {
        $username = (string) setting('payment.kapital_username');
        $password = (string) setting('payment.kapital_password');
        if ($username === '' || $password === '') {
            throw new RuntimeException('Kapital Bank credentials are not configured.');
        }

        return Http::withBasicAuth($username, $password)->acceptJson()->asJson()->timeout(30);
    }

    public function start(Order $order): ?string
    {
        $body = [
            'order' => [
                'typeRid' => setting('payment.kapital_type_rid', 'Order_SMS'),
                'amount' => number_format((float) $order->total, 2, '.', ''),
                'currency' => 'AZN',
                'language' => $order->locale,
                'title' => 'Sifariş '.$order->number,
                'description' => 'Sifariş '.$order->number,
                'hppRedirectUrl' => URL::signedRoute('payment.kapital.return', ['order' => $order->number]),
            ],
        ];

        $payment = $order->payments()->create([
            'provider' => 'kapital',
            'amount' => $order->total,
            'status' => 'created',
            'request' => $body,
        ]);

        $response = $this->client()->post($this->baseUrl().'/order', $body);
        $data = $response->json();
        $payment->update(['response' => $data]);

        if (! $response->successful() || empty($data['order']['id']) || empty($data['order']['hppUrl'])) {
            $payment->update(['status' => 'error']);
            throw new RuntimeException('Kapital Bank order create failed: '.$response->body());
        }

        $payment->update([
            'provider_order_id' => (string) $data['order']['id'],
            'provider_password' => $data['order']['password'] ?? null,
            'status' => $data['order']['status'] ?? 'Preparing',
        ]);

        return $data['order']['hppUrl'].'?'.http_build_query([
            'id' => $data['order']['id'],
            'password' => $data['order']['password'] ?? '',
        ]);
    }

    public function verify(Payment $payment): Payment
    {
        $response = $this->client()->get($this->baseUrl().'/order/'.$payment->provider_order_id, [
            'password' => $payment->provider_password,
            'tranDetailLevel' => 1,
        ]);
        $data = $response->json();
        $status = $data['order']['status'] ?? 'Unknown';

        $payment->update(['status' => $status, 'response' => $data]);

        $order = $payment->order;
        if (in_array($status, self::PAID_STATUSES, true)) {
            $order->update(['payment_status' => 'paid']);
        } elseif (in_array($status, self::FAILED_STATUSES, true)) {
            $order->update(['payment_status' => 'failed']);
        }

        return $payment;
    }

    public function isPaid(Payment $payment): bool
    {
        return in_array($payment->status, self::PAID_STATUSES, true);
    }
}
