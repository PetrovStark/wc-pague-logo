<?php

namespace PagueLogo\Interfaces;

/**
 * Interface para métodos de pagamento.
 */
interface PaymentMethodInterface
{
    /**
     * Processa o pagamento.
     * 
     * @param \WC_Order $order
     * @param array $authorization
     */
    public function processPayment($order, $authorization);
}