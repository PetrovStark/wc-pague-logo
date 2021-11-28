<?php

namespace PagueLogo\Source;

use PagueLogo\Interfaces\PaymentMethodInterface;

/**
 * Responsável por gerenciar as operações de pagamento. (Processar, reembolsar, etc.)
 */
class PagueLogoPaymentGateway
{
    public static function processPayment(\WC_Order $order, PaymentMethodInterface $payment_method, array $authorization)
    {
        $payment_method->processPayment($order, $authorization);
    }
}