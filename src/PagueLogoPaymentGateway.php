<?php

namespace PagueLogo\Source;

use PagueLogo\Interfaces\PaymentMethodInterface;

/**
 * Responsável por gerenciar as operações de pagamento. (Processar, reembolsar, etc.)
 */
class PagueLogoPaymentGateway
{
    public static function processPayment($payment_method)
    {
        return $payment_method->processPayment();
    }
}