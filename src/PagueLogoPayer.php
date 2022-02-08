<?php

namespace PagueLogo\Source;

use PagueLogo\Source\PagueLogoFormatter;

class PagueLogoPayer
{
    /**
     * __construct
     * 
     * @param array $request variável global $_POST
     */
    public function __construct($request)
    {
        $Formatter = new PagueLogoFormatter();

        $this->full_name = $request['billing_first_name'] . ' ' . $request['billing_last_name'];
        $this->email = $request['billing_email'];
        $this->person_type = $this->getPersonType($request['billing_persontype']);
        $this->cpf = $Formatter->filterNumbers($request['billing_cpf']);
        $this->cnpj = $Formatter->filterNumbers($request['billing_cnpj']);
        $this->document = $this->getDocumentByPersonType();
        $this->phone = $Formatter->filterNumbers($request['billing_phone']);
        $this->company = $request['billing_company'];
        $this->address_1 = $request['billing_address_1'];
        $this->address_2 = $request['billing_address_2'];
        $this->address_number = $request['billing_number'];
        $this->postcode = $request['billing_postcode'];
        $this->neighborhood = $request['billing_neighborhood'];
        $this->city = $request['billing_city'];
        $this->state = strtolower($request['billing_state']);
    }

    /**
     * __get
     * 
     * @param string $atrib Atributo a ser retornado
     * 
     * @return mixed
     */
    public function __get($atrib)
    {
        return $this->$atrib;
    }

    /**
     * getPersonType
     * 
     * Obtém o tipo de pessoa.
     * 
     * @param int $person_type_id ID do Tipo de pessoa
     * 
     * @return string
     */
    private function getPersonType($person_type_id)
    {
        switch ($person_type_id) {
            case 1:
                return 'fisica';
            case 2:
                return 'juridica';
            default:
                return 'fisica';

        }
    }

    /**
     * getAvailableDocument
     * 
     * Obtém o documento adequado baseado no tipo de pessoa.
     * 
     * @return string
     */
    private function getDocumentByPersonType()
    {
        if ($this->person_type == 'fisica') {
            return $this->cpf;
        }

        return $this->cnpj;
    }
}