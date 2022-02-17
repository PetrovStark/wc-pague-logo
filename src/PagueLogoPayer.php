<?php

namespace PagueLogo\Source;

use PagueLogo\Source\PagueLogoFormatter;

/**
 * PagueLogoPayer
 * 
 * Entidade que representa o pagador do pedido.
 */
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

        # Informações do cartão.
        $this->card_installments = $request['billing_installments'];
        $this->card_holder_name = $request['billing_card_name'];
        $this->card_number = $Formatter->filterSpaces($request['billing_card_number']);
        $this->card_expiration = $Formatter->filterSpaces($request['billing_card_expiry']);
        $this->card_cvv = $request['billing_card_cvv'];
        $this->card_flag = $request['billing_card_flag'];
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
     * get
     * 
     * Obtém os dados do pagador.
     */
    public function get()
    {
        return [
            "id" => "",
            "nome" => $this->full_name,
            "nomeSocial" => $this->full_name,
            "tipo" => $this->person_type,
            "dataNascimento" => "",
            "cpfCnpj" => $this->document,
            "email" => $this->email,
            "telefone" => $this->phone,
            "endereco" => [
                "id" => "",
                "logradouro" => $this->address_1,
                "numero" => $this->address_number,
                "bairro" => $this->neighborhood,
                "cep" => $this->postcode,
                "cidade" => $this->city,
                "complemento" => $this->address_2,
                "siglaEstado" => $this->state,
            ],
        ];
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