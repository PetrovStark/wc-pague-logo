<?php

namespace PagueLogo\Source;

/**
 * PagueLogoFormatter
 * 
 * Este Helper é responsável por formatações de dados.
 */
class PagueLogoFormatter
{
    /**
     * Filtra apenas os números de uma string
     */
    public function filterNumbers($string)
    {
        return preg_replace('/[^0-9]/', '', $string);
    }

    /**
     * Formata o valor para o formato do PagueLogo.
     */
    public function formatPrice($price)
    {
        return number_format($price, 2, ',', '.');
    }
}