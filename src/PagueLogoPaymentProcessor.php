<?php

use PagueLogo\Interfaces\PaymentMethodInterface;

class PagueLogoPaymentProcessor
{
    public static function processPayment(WC_Order $order, PaymentMethodInterface $payment_method, array $authorization)
    {
        $payment_method->processPayment($order, $authorization);
    }
}