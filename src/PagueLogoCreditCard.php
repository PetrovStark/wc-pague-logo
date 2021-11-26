<?php

use PagueLogo\Interfaces\PaymentMethodInterface;

/**
 * Método de pagamento de cartão de crédito.
 */
class PagueLogoCreditCard implements PaymentMethodInterface
{
    public function __construct($request)
    {
        $this->number = $request['billing_card_number'];
        $this->holder_name = $request['billing_card_name'];
        $this->expiration_date = $request['billing_card_expiry'];
        $this->cvv = $request['billing_card_cvv'];
    }

    public function processPayment($order, $authorization)
    {
        // Processar o endpoint de pagamento do cartão de crédito.
    }
}