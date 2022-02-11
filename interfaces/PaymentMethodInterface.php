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

    /**
     * Insere os dados do pagamento no banco de dados.
     */
    public function insertPaymentMetaData($response);
}