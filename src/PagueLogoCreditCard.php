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
        $this->cpf = $request['billing_cpf'];
        $this->cnpj = $request['billing_cnpj'];
        $this->company = $request['billing_company'];
        $this->person_type = $this->getPersonType($request['billing_persontype']);
        $this->address_1 = $request['billing_address_1'];
        $this->address_2 = $request['billing_address_2'];
        $this->postcode = $request['billing_postcode'];
        $this->neighborhood = $request['billing_neighborhood'];
        $this->city = $request['billing_city'];
        $this->state = strtolower($request['billing_state']);

        # Informações do cartão.
        $this->card_flag = $request['billing_card_flag'];
        $this->number = $request['billing_card_number'];
        $this->holder_name = $request['billing_card_name'];
        $this->expiration_date = str_replace(' ', '', $request['billing_card_expiry']);
        $this->cvv = $request['billing_card_cvv'];
        $this->installments = $request['billing_installments'];
    }

    public function processPayment()
    {
        date_default_timezone_set('America/Sao_Paulo');

        $body = json_encode([
            'tipo' => 'credito',
            'valor' => number_format($this->price, 2, ',', '.'),
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

        $response = PagueLogoRequestMaker::endpoint('cartao/pagar', 'POST', $body, $headers);

        PagueLogoRequestValidator::validate($response);

        throw new \Exception('PagueLogo: ' . json_encode($response->data));
        return $response->data;
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
            'numero' => $this->number,
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
}