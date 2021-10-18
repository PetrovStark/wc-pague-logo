<?php

namespace PagueLogo\Source;

/**
 * Representa um cartão de crédito no fluxo de checkout da Pague Logo.
 */
class Card
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
            'slug' => 'card_cvc',
            'name' => 'CVV'
        ]
    ];

    private $campos_excecoes = [
        'required_billing_card_name' => '<strong>Nome do proprietário</strong> do cartão é obrigatório.',
        'required_billing_card_number' => '<strong>Número do cartão</strong> é obrigatório.',
        'required_billing_card_expiry' => '<strong>Data de expiração</strong> do cartão é obrigatória.',
        'required_billing_card_cvc' => '<strong>CVV</strong> do cartão é obrigatório.',
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
    
    /**
     * Verifica se o cartão está expirado.
     * 
     * @param $date
     * 
     * @return bool true se estiver expirado
     */
    public function verificaDataExpiracao($date)
    {
        date_default_timezone_set('America/Sao_Paulo');

        $date = str_replace(' ', '', $date);
        $date = explode('/', $date);
        $date = $date[1].'-'.$date[0].'-01';

        $expiry_date = new \DateTime($date);
        $now = new \DateTime(date('Y-m'));

        return $expiry_date < $now;
    }
}