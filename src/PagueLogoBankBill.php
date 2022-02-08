<?php
namespace PagueLogo\Source;

use WC_Order;
use PagueLogo\Interfaces\PaymentMethodInterface;

/**
 * PagueLogoBankBill
 * 
 * Integração com a API de pagamento na opção "Boleto Bancário".
 */
class PagueLogoBankBill implements PaymentMethodInterface
{
    /**
     * __construct
     * 
     * @param WC_Order $Order Pedido do WooCommerce
     * @param PagueLogoPayer $Payer Pagador do pedido.
     * @param PagueLogoAuthentication $Authentication Autenticação do gateway.
     * 
     * @return void
     */
    public function __construct(WC_Order $Order, PagueLogoPayer $Payer, PagueLogoAuthentication $Authentication)
    {
        $this->Order = $Order;
        $this->Payer = $Payer;
        $this->Authentication = $Authentication;
    }

    /**
     * processPayment
     * 
     * Gera o boleto bancário.
     */
    public function processPayment()
    {
        date_default_timezone_set('America/Sao_Paulo');

        $body = json_encode([
            "valor" => $this->Order->get_total(),
            "nossoNumero" => "",
            "digitoNossoNumero" => "",
            "dataVencimento" => "30/12/2022", # Adicionar opção no admin do método de pagamento "Dias para vencimento".
            "instrucaoLocalPagamento" => "",
            "instrucaoAoPagante" => "",
            "instrucoesGerais" => "Apenas um teste de desenvolvimento por enquanto.", # Adicionar opção no admin do método de pagamento "Instruções de pagamento".
            "enviarBoletoPorEmailParaPagador" => "true",
            "gerarPdf" => "true",
            "pagador" => $this->getPayer()
        ]);

        update_post_meta($this->Order->get_id(), 'pague_logo_request_body', json_encode($body));

        $response = PagueLogoRequestMaker::endpoint('boleto/gerar', 'POST', $body, $this->Authentication->getHeaders());

        PagueLogoRequestValidator::validate($response);
    }

    /**
     * getPayer
     * 
     * Obtém o pagador do pedido.
     */
    private function getPayer()
    {
        return [
            "id" => "",
            "nome" => $this->Payer->full_name,
            "tipo" => $this->Payer->person_type,
            "cpfCnpj" => $this->Payer->document,
            "email" => $this->Payer->email,
            "endereco" => [
                "id" => "",
                "logradouro" => $this->Payer->address_1,
                "numero" => $this->Payer->address_number,
                "bairro" => $this->Payer->neighborhood,
                "cep" => $this->Payer->postcode,
                "cidade" => $this->Payer->city,
                "complemento" => $this->Payer->address_2,
                "siglaEstado" => $this->Payer->state,
            ],
        ];
    }
}