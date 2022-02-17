<?php

namespace PagueLogo\Source;

use WC_Order;
use Exception;
use PagueLogo\Interfaces\PaymentMethodInterface;

/**
 * Método de pagamento de cartão de crédito.
 */
class PagueLogoCreditCard implements PaymentMethodInterface
{
    public function __construct(WC_Order $Order, PagueLogoPayer $Payer, PagueLogoAuthentication $Authentication, $admin_options = [])
    {
        $this->Order = $Order;
        $this->Payer = $Payer;
        $this->Authentication = $Authentication;
        $this->Formatter = new PagueLogoFormatter();
        $this->admin_options = $admin_options;
    }

    public function processPayment()
    {
        date_default_timezone_set('America/Sao_Paulo');

        $this->body = json_encode([
            "tipo" => "credito",
            "valor" => $this->Formatter->formatPrice($this->Order->get_total()),
            "quantidadeParcelas" => $this->admin_options['installments'],
            "dataPagamento" => $this->Order->get_date_created()->format('d/m/Y H:i:s'),
            "descricaoPagamento" => "",
            "cancelarAntesDeEfetivar" => "false",
            "pagador" => $this->Payer->get(),
            "cartao" => [
                "bandeira" => $this->Payer->card_flag,
                "numero" => $this->Payer->card_number,
                "titular" => $this->Payer->card_holder_name,
                "codigoSeguranca" => $this->Payer->card_cvv,
                "validade" => $this->Payer->card_expiration,
                "salvarCartao" => "",
                "tokenApi" => ""
            ]
        ]);
        update_post_meta($this->Order->get_id(), 'pague_logo_request_body', $this->body);

        // $this->response = PagueLogoRequestMaker::endpoint('cartao/pagar', 'POST', $this->body, $this->Authentication->getHeaders());
        $this->response = $this->mockResponse();
        update_post_meta($this->Order->get_id(), 'pague_logo_response_body', json_encode($this->response));

        $this->response = $this->mockResponse();

        PagueLogoRequestValidator::validate($this->response);

        return $this->response;
    }

    public function insertPaymentMetaData($response)
    {
        $payment_meta_data = [
            'dataPagamento',
            'valor',
            'codigoTransacaoMaquina',
            'numeroAutorizacao',
            'tipoPagamento',
            'bandeiraCartaoPagamento',
            'quantidadeParcelas',
        ];

        foreach ($response->data as $meta_key => $meta_value) {
            if (!in_array($meta_key, $payment_meta_data)) {
                continue;
            }

            update_post_meta($this->Order->get_id(), 'pague_logo_'.$meta_key, $meta_value);
        }
    }
    

    /**
     * Função para simular a resposta de sucesso da PagueLogo.
     */
    private function mockResponse()
    {
        return json_decode('{"data":{"id":"1123269","tipo":"debito","valor":"10,00","valorLiquido":"9,95","codigoCielo":null,"quantidadeParcelas":"1","situacao":"efetivado","dataPagamento":"01/03/2021 05:08:38","dataCadastro":null,"codigoTransacaoMaquina":"432164177","codigoAutorizacao":null,"numeroAutorizacao":"502954","bandeiraCartaoPagamento":"elodebito","descricaoPagamento":"Pagamento efetuado por Máquina de Cartão. Máquina => 6M230428","loja":null,"cartao":{"bandeira":null,"titular":null,"tokenApi":null},"beneficiado":{"id":"1490","nome":"CLIENTE 1","nomeSocial":null,"cpfCnpj":"19XXXXXXX0107","tipo":"juridica","dataNascimento":null,"email":"CLIENTE1@gmail.com","endereco":null},"pagador":{"id":null,"nome":null,"nomeSocial":null,"cpfCnpj":null,"tipo":null,"dataNascimento":null,"email":null,"endereco":null},"maquinaPagamento":{"id":"478","codigo":"478","serial":"6M230428","dataCadastro":"15/11/2020","descricao":"MQ02 - CLIENTE 1 - NEW"}},"responseStatus":[{"status":"ok","codigo":null,"mensagem":"Pagamento via cartão realizado com sucesso! [API REST]"}]}');
    }
}