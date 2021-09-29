<?php

namespace PagueLogo\Models;

/**
 * Representa um cartão de crédito no fluxo de checkout da Pague Logo.
 */
class Card
{
    public $campos = [
        'billing_card_name',
        'billing_card_number',
        'billing_card_expiry',
        'billing_card_cvc'
    ];

    public $campos_excecoes = [
        'required_billing_card_name' => '<strong>Nome do proprietário</strong> do cartão é obrigatório.',
        'required_billing_card_number' => '<strong>Número do cartão</strong> é obrigatório.',
        'required_billing_card_expiry' => '<strong>Data de expiração</strong> do cartão é obrigatória.',
        'required_billing_card_cvc' => '<strong>CVV</strong> do cartão é obrigatório.',
        'expired_billing_card_expiry' => 'O cartão inserido está expirado, modifique a data de expiração ou insira um novo cartão.'
    ];
}