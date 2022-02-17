<?php

namespace PagueLogo\Source;

use PagueLogo\Source\PagueLogoRequestMaker;

/**
 * Responsável pela autenticação com o gateway de pagamento.
 */
class PagueLogoAuthentication
{
    public function __construct($usuario, $senha)
    {
        $this->usuario = $usuario;
        $this->senha = $senha;
        $this->response = $this->processaAuth();
    }

    /**
     * Obtém o token de autenticação.
     */
    public function getToken()
    {
        return $this->response->token;
    }

    /**
     * Obtém o código WHOIS do usuário.
     */
    public function getWhois()
    {
        return $this->response->whois;
    }

    /**
     * getHeaders
     * 
     * Obtém os headers de autenticação.
     * 
     * @return array
     */
    public function getHeaders()
    {
        return [
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->response->token,
            'Whois: '.$this->response->whois
        ]; 
    }
    
    /**
     * Processa a autenticação, retornando um token de autenticação.
     * 
     * @return string Bearer Token
     */
    private function processaAuth()
    {
        $body = json_encode([
            'usuario' => $this->usuario,
            'senha' => $this->senha
        ]);

        $headers = [
            'Content-Type: application/json'
        ];

        $response = PagueLogoRequestMaker::endpoint('auth', 'POST', $body, $headers);

        PagueLogoRequestValidator::validate($response);

        return $response->data;
    }
}