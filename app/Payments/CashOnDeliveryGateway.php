<?php

namespace App\Payments;

use App\Models\Order;
use App\Models\Payment;

class CashOnDeliveryGateway implements PaymentGateway
{
    public function start(Order $order): ?string
    {
        return null;
    }

    public function verify(Payment $payment): Payment
    {
        return $payment;
    }
}
