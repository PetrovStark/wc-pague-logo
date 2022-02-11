<?php

namespace PagueLogo\Source;

use PagueLogo\Interfaces\PaymentMethodInterface;

/**
 * Responsável por gerenciar as operações de pagamento. (Processar, reembolsar, etc.)
 */
class PagueLogoPaymentGateway implements PaymentMethodInterface
{
    public function __construct($payment_method)
    {
        if (!($payment_method instanceof PaymentMethodInterface)) {
            throw new \Exception('O método de pagamento informado não possui uma interface válida.');
        }

        $this->payment_method = $payment_method;
    }

    public function processPayment()
    {
        return $this->payment_method->processPayment();
    }

    public function insertPaymentMetaData($response)
    {
        $this->payment_method->insertPaymentMetaData($response);
    }
}