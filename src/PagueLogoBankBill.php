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
     * @param array $admin_options Opções do painel administrativo.
     * 
     * @return void
     */
    public function __construct(WC_Order $Order, PagueLogoPayer $Payer, PagueLogoAuthentication $Authentication, $admin_options = [])
    {
        $this->Order = $Order;
        $this->Payer = $Payer;
        $this->Authentication = $Authentication;
        $this->admin_options = $admin_options;
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
            "dataVencimento" => $this->calculateDueDate(),
            "instrucaoLocalPagamento" => "",
            "instrucaoAoPagante" => "",
            "instrucoesGerais" => $this->admin_options['general_instructions'],
            "enviarBoletoPorEmailParaPagador" => "true",
            "gerarPdf" => "true",
            "pagador" => $this->getPayer()
        ]);

        update_post_meta($this->Order->get_id(), 'pague_logo_request_body', json_encode($body));

        // $response = PagueLogoRequestMaker::endpoint('boleto/gerar', 'POST', $body, $this->Authentication->getHeaders());
        // PagueLogoRequestValidator::validate($response);

        $response = $this->mockResponse();

        return $response;
    }

    /**
     * insertPaymentMetaData
     * 
     * Insere os metadados de pagamento do pedido.
     */
    public function insertPaymentMetaData($response)
    {
        $payment_meta_data = [
            'pague_logo_due_date' => $response->data->dataVencimento
        ];

        foreach ($payment_meta_data as $meta_key => $meta_value) {
            update_post_meta($this->Order->get_id(), $meta_key, $meta_value);
        } 
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

    /**
     * calculateDueDate
     * 
     * Calcula a data de vencimento do boleto.
     */
    private function calculateDueDate()
    {
        date_default_timezone_get('America/Sao_Paulo');

        $due_date = date('d/m/Y', strtotime('+'.$this->admin_options['due_date'].' days'));

        return $due_date;
    }

    /**
     * mockResponse
     * 
     * Retorna um objeto de sucesso 'mockado', para fins de desenvolvimento.
     * 
     * @return object
     */
    private function mockResponse()
    {
        return json_decode('{
            "data": {
                "codigoTitulo": "9398",
                "codigoBoleto": "8325",
                "valor": "10,05",
                "valorLiquido": null,
                "valorPago": null,
                "numeroDocumento": "9398",
                "dataDocumento": "02/12/2021",
                "nossoNumero": null,
                "digitoNossoNumero": null,
                "dataVencimento": "30/12/2022",
                "dataCadastro": "02/12/2021",
                "dataCancelamento": null,
                "dataPagamento": null,
                "situacao": "pendente",
                "linhaDigitavel": null,
                "codigoDeBarras": null,
                "valorMultaPorcentagem": null,
                "diasParaCobrancaDeMulta": null,
                "dataRegistro": null,
                "numeroParcelaCarne": null,
                "instrucaoLocalPagamento": "",
                "instrucaoAoPagante": "",
                "instrucoesGerais": "",
                "linkVisualizacao": "https://www.paguelogo.com.br/api/boleto/XXX/YYYY/ZZZZ/U",
                "pdfBoletoBytesBase64": null,
                "pagador": {
                    "id": "18852",
                    "nome": "CALEB DE ALMEIDA FELIX",
                    "nomeSocial": null,
                    "cpfCnpj": "35801444130",
                    "tipo": "fisica",
                    "dataNascimento": null,
                    "email": "CALEB@EMAIL.COM.BR",
                    "endereco": {
                        "id": "5701",
                        "logradouro": "RUA DAS FLORES",
                        "numero": "200",
                        "bairro": "COLINAS",
                        "cep": "63660000",
                        "cidade": "TAUA",
                        "complemento": "CASA",
                        "siglaEstado": "CE"
                    }
                }
            },
            "responseStatus": [
                {
                    "status": "ok",
                    "codigo": null,
                    "mensagem": "Boleto gerado com sucesso!"
                }
            ]
        }');
    }
}