<?php

namespace PagueLogo\Source;

/**
 * Responsável por realizar requisições para a API da Pague Logo.
 */
class PagueLogoRequestMaker
{
    /**
     * Realiza uma requisição para a API da Pague Logo.
     * 
     * @param string $endpoint Endpoint da API
     * @param string $method Método da requisição
     * @param string $body Corpo da requisição
     * @param array $headers Cabeçalhos da requisição
     * 
     * @return object Resposta da requisição
     * 
     * @throws Exception Caso ocorra algum erro na requisição
     */
    public static function endpoint($resource, $method, $body = [], $headers = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $GLOBALS['PAGUE_LOGO_ENDPOINT'] . $resource,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = "cURL Error #:" . $err;
            throw new \Exception($message);
        }

        return json_decode($response);
    }
}