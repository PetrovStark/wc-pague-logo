<?php

namespace PagueLogo\Source;

/**
 * Responsável por validar as respostas da API da Pague Logo.
 */
class PagueLogoRequestValidator
{
    public static function validate($response)
    {
        $status = $response->responseStatus[0]->status;
        $mensagem = $response->responseStatus[0]->mensagem;
        
        if (empty($response->data) || $status !== 'ok') {
            throw new \Exception($mensagem);
        }
    }
}