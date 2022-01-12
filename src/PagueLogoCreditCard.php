<?php

namespace PagueLogo\Source;

use Exception;
use PagueLogo\Interfaces\PaymentMethodInterface;

/**
 * Método de pagamento de cartão de crédito.
 */
class PagueLogoCreditCard implements PaymentMethodInterface
{
    public function __construct($request, $order, $authorization)
    {
        # Autenticação.
        $this->authorization = $authorization;

        # Informações do pedido.
        $this->order = $order;
        $this->order_id = $order->get_id();
        $this->price = (int) $this->order->get_total();

        # Informações do pagador.
        $this->full_name = $request['billing_first_name'] . ' ' . $request['billing_last_name'];
        $this->email = $request['billing_email'];
        $this->cpf = $this->filterNumbers($request['billing_cpf']);
        $this->cnpj = $this->filterNumbers($request['billing_cnpj']);
        $this->phone = $this->filterNumbers($request['billing_phone']);
        $this->company = $request['billing_company'];
        $this->person_type = $this->getPersonType($request['billing_persontype']);
        $this->address_1 = $request['billing_address_1'];
        $this->address_2 = $request['billing_address_2'];
        $this->address_number = $request['billing_number'];
        $this->postcode = $request['billing_postcode'];
        $this->neighborhood = $request['billing_neighborhood'];
        $this->city = $request['billing_city'];
        $this->state = strtolower($request['billing_state']);

        # Informações do cartão.
        $this->card_flag = $request['billing_card_flag'];
        $this->number = str_replace(' ', '', $request['billing_card_number']);
        $this->holder_name = $request['billing_card_name'];
        $this->expiration_date = str_replace(' ', '', $request['billing_card_expiry']);
        $this->cvv = $request['billing_card_cvv'];
        $this->installments = $request['billing_installments'];
    }

    /**
     * Fluxo de pagamento do cartão de crédito.
     */
    public function processPayment()
    {
        $response = $this->paymentRequest();

        $this->insertPaymentMetaData($response);
        
        return $response->data;
    }

    /**
     * Realiza a requisição de pagamento.
     */
    private function paymentRequest()
    {
        date_default_timezone_set('America/Sao_Paulo');

        $body = json_encode([
            'tipo' => 'credito',
            'valor' => $this->formatPrice($this->price),
            'dataPagamento' => date('d/m/Y'),
            'quantidadeParcelas' => $this->installments,
            'descricaoPagamento' => $this->order->get_customer_note(),
            'cancelarAntesDeEfetivar' => 'false',
            'pagador' => $this->getOrderPayerInfo(),
            'cartao' => $this->getCardInfo(),
        ]);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->authorization['token'],
            'Whois: '.$this->authorization['whois']
        ];

        update_post_meta($this->order->get_id(), 'pague_logo_request_body', json_encode($body));

        $response = PagueLogoRequestMaker::endpoint('cartao/pagar', 'POST', $body, $headers);

        PagueLogoRequestValidator::validate($response);

        return $response;
    }

    /**
     * Insere os metadados de pagamento do pedido.
     * 
     * @return void
     */
    private function insertPaymentMetaData($response)
    {
        $payment_meta_data = [
                'pague_logo_created_at' => $response->data->dataPagamento,
                'pague_logo_payment_total' => $response->data->valor,
            'pague_logo_transaction_id' => $response->data->codigoTransacaoMaquina,
            'pague_logo_authentication_number' => $response->data->numeroAutorizacao,
            'pague_logo_payment_type' => $response->data->tipoPagamento,
                'pague_logo_card_flag' => $response->data->bandeiraCartaoPagamento,
                'pague_logo_installments_number' => $response->data->quantidadeParcelas,
        ];

        foreach ($payment_meta_data as $meta_key => $meta_value) {
            update_post_meta($this->order_id, $meta_key, $meta_value);
        } 
    }

    /**
     * Obtém informações do pagador do pedido.
     */
    private function getOrderPayerInfo()
    {
        return [
            'id' => '',
            'nome' => $this->full_name,
            'tipo' => $this->person_type,
            'email' => $this->email,
            'telefone' => $this->phone,
            'cpfCnpj' => $this->getPersonDocument(),
            'endereco' => $this->getOrderAddressInfo(),
        ];
    }

    /**
     * Obtém o tipo de pessoa.
     */
    private function getPersonType(int $person_type)
    {
        switch ($person_type) {
            case 1:
                return 'fisica';
            case 2:
                return 'juridica';
            default:
                return 'fisica';

        }
    }

    /**
     * Obtém o documento do pagador.
     */
    private function getPersonDocument()
    {
        if ($this->person_type == 'fisica') {
            return $this->cpf;
        }

        return $this->cnpj;
    }

    /**
     * Obtém informações de endereço do pagador.
     */
    private function getOrderAddressInfo()
    {
        return [
            'id' => '',
            'logradouro' => $this->address_1,
            'numero' => $this->address_number,
            'bairro' => $this->neighborhood,
            'cep' => $this->postcode,
            'cidade' => $this->city,
            'complemento' => $this->address_2,
            'siglaEstado' => $this->state,
        ];
    }

    /**
     * Obtém informações do cartão de crédito.
     */
    private function getCardInfo()
    {
        return [
            'bandeira' => $this->card_flag,
            'numero' => $this->number,
            'titular' => $this->holder_name,
            'codigoSeguranca' => $this->cvv,
            'validade' => $this->expiration_date,
            'salvarCartao' => '',
            'tokenApi' => ''
        ];
    }

    /**
     * Filtra apenas os números de uma string
     */
    private function filterNumbers($string)
    {
        return preg_replace('/[^0-9]/', '', $string);
    }

    /**
     * Formata o valor para o formato do PagueLogo.
     */
    private function formatPrice($price)
    {
        return number_format($price, 2, ',', '.');
    }

    /**
     * Função para simular a resposta de sucesso da PagueLogo.
     */
    private function mockResponse()
    {
        return json_decode('{"data":{"id":"1123269","tipo":"debito","valor":"10,00","valorLiquido":"9,95","codigoCielo":null,"quantidadeParcelas":"1","situacao":"efetivado","dataPagamento":"01/03/2021 05:08:38","dataCadastro":null,"codigoTransacaoMaquina":"432164177","codigoAutorizacao":null,"numeroAutorizacao":"502954","bandeiraCartaoPagamento":"elodebito","descricaoPagamento":"Pagamento efetuado por Máquina de Cartão. Máquina => 6M230428","loja":null,"cartao":{"bandeira":null,"titular":null,"tokenApi":null},"beneficiado":{"id":"1490","nome":"CLIENTE 1","nomeSocial":null,"cpfCnpj":"19XXXXXXX0107","tipo":"juridica","dataNascimento":null,"email":"CLIENTE1@gmail.com","endereco":null},"pagador":{"id":null,"nome":null,"nomeSocial":null,"cpfCnpj":null,"tipo":null,"dataNascimento":null,"email":null,"endereco":null},"maquinaPagamento":{"id":"478","codigo":"478","serial":"6M230428","dataCadastro":"15/11/2020","descricao":"MQ02 - CLIENTE 1 - NEW"}},"responseStatus":[{"status":"ok","codigo":null,"mensagem":"Pagamento via cartão realizado com sucesso! [API REST]"}]}');
    }
}