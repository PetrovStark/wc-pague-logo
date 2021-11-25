<?php

namespace PagueLogo\Source;

/**
 * Responsável por validar os dados do cartão de crédito.
 */
class CardValidator
{
    /**
     * Valida se o cartão está expirado.
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