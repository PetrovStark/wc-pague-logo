<?php

namespace PagueLogo\Source;

/**
 * Responsável por fornecer as informações dos campos de cartão de crédito.
 */
class CardFieldsInfo
{
    private $campos = [
        [
            'slug' => 'card_name',
            'name' => 'Nome completo'
        ],
        [
            'slug' => 'card_number',
            'name' => '•••• •••• •••• ••••'
        ],
        [
            'slug' => 'card_expiry',
            'name' => '••/••'
        ],
        [
            'slug' => 'card_cvv',
            'name' => 'CVV'
        ]
    ];

    private $campos_excecoes = [
        'required_billing_card_name' => '<strong>Nome do proprietário</strong> do cartão é obrigatório.',
        'required_billing_card_number' => '<strong>Número do cartão</strong> é obrigatório.',
        'required_billing_card_expiry' => '<strong>Data de expiração</strong> do cartão é obrigatória.',
        'required_billing_card_cvv' => '<strong>CVV</strong> do cartão é obrigatório.',
        'expired_billing_card_expiry' => 'O cartão inserido está expirado, modifique a data de expiração ou insira um novo cartão.'
    ];

    /**
     * Obtém a referência dos campos de pagamento.
     */
    public function getCamposDoCartao()
    {
        return $this->campos;
    }

    public function getExcecoesDoCartao()
    {
        return $this->campos_excecoes;
    }
}