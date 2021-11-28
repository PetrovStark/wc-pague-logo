<?php

namespace PagueLogo\Interfaces;

/**
 * Interface para métodos de pagamento.
 */
interface PaymentMethodInterface
{
    /**
     * Processa o pagamento.
     */
    public function processPayment();
}