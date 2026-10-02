<?php

namespace App\Payments;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGateway
{
    /** Ödənişi başladır və müştərinin yönləndiriləcəyi URL-i qaytarır (null — yönləndirmə yoxdur) */
    public function start(Order $order): ?string;

    /** Bankdan qayıdışda ödəniş statusunu yoxlayır və yeniləyir */
    public function verify(Payment $payment): Payment;
}
