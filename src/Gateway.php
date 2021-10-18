<?php

namespace PagueLogo\Source;

/**
 * Classe que realiza a conexão com a API do gateway.
 * 
 * Deve ser inserido em um contexto de try catch.
 */
class Gateway
{
    public function __construct($usuario, $senha)
    {
        $this->usuario = $usuario;
        $this->senha = $senha;
    }
    
    /**
     * Processa a autenticação.
     * 
     * @return string Bearer Token
     */
    public function processaAuth()
    {
        $body = json_encode([
            'usuario' => $this->usuario,
            'senha' => $this->senha
        ]);

        $headers = [
            'Content-Type: application/json'
        ];

        $response = $this->getEndpoint('auth', 'POST', $body, $headers);

        $response = json_decode($response);

        $this->validaResposta($response);

        return $response->data[0]->token;
    }

    /**
     * Valida a resposta do gateway de pagamento.
     */
    private function validaResposta($response)
    {
        if (empty($response->data) || $response->responseStatus[0]->status !== 'ok') {
            throw new \Exception('Erro de integração com gateway de pagamento: '. $response->responseStatus[0]->mensagem);
        }
    }

    private function getEndpoint($endpoint, $method, $body = [], $headers = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://paguelogo.com.br/api/" . $endpoint,
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

        return $response;
    }
}