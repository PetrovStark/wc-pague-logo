<?php

namespace PagueLogo\Source;

use PagueLogo\Source\CardFieldsInfo;

/**
 * Responsável por validar os dados do cartão de crédito.
 */
class CardValidator
{
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Valida os dados do cartão.
     */
    public function validaCamposDoCartao()
    {
        $CardFieldsInfo = new CardFieldsInfo();
        $excecoes = $CardFieldsInfo->getExcecoesDoCartao();
        
        $errors = 0;
        foreach ($CardFieldsInfo->getCamposDoCartao() as $key) {

            $field_name = 'billing_'.$key['slug'];

            $value = $this->request[$field_name];

            if (empty($value)) {
                wc_add_notice($excecoes['required_'.$field_name], 'error');
                $errors++;

                continue;
            }

            if ('billing_card_expiry' === $field_name) {
                if ($this->verificaDataExpiracao($value)) {
                    wc_add_notice($excecoes['expired_billing_card_expiry'], 'error');
                    $errors++;

                    continue;
                }
            }
        }

        if ($errors > 0) {
            throw new \Exception('Validation error');
        }
    }

    /**
     * Valida se o cartão está expirado.
     * 
     * @param $date
     * 
     * @return bool true se estiver expirado
     */
    private function verificaDataExpiracao($date)
    {
        date_default_timezone_set('America/Sao_Paulo');

        $date = str_replace(' ', '', $date);
        $date = explode('/', $date);
        $date = $date[1].'-'.$date[0].'-01';

        $expiry_date = new \DateTime($date);
        $now = new \DateTime(date('Y-m'));

        return $expiry_date < $now;
    }

    /**
     * Valida as parcelas informadas.
     */
    public function validaParcelas()
    {
        $installments = $this->request['billing_installments'];

        if (empty($installments)) {
            wc_add_notice('Selecione um número válido de parcelas para sua compra.', 'error');
            throw new \Exception('Validation error');
        }
    }
    
}