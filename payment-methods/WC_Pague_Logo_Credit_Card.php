<?php

use PagueLogo\Source\CardValidator;
use PagueLogo\Source\PagueLogoAuthentication;
use PagueLogo\Source\PagueLogoPaymentGateway;
use PagueLogo\Source\PagueLogoCreditCard;
use PagueLogo\Source\PagueLogoEnvironmentHelper;

/**
 * WC_Pague_Logo_Credit_Card
 * 
 * Método de pagamento desenvolvido para a integração com cartão de crédito do gateway de pagamento "Pague Logo".
 * 
 * Estes links podem lhe ajudar
 * @see https://rudrastyh.com/woocommerce/payment-gateway-plugin.html Artigo que ensina a criar gateways de pagamento no WooCommerce.
 * 
 * @author The Samurai Petrus (https://github.com/SamuraiPetrus)
 */
class WC_Pague_Logo_Credit_Card extends \WC_Payment_Gateway
{
    /**
     * Define as propriedades do gateway.
     */
    public function __construct()
    {
        $this->plugin_dir_path = plugin_dir_path(dirname(__FILE__));
        $this->plugin_dir_url = plugin_dir_url(dirname(__FILE__));

        $admin_options = get_option('wc_pague_logo_settings');
        $this->usuario = $admin_options['wc_pague_logo_usuario'];
        $this->senha = $admin_options['wc_pague_logo_senha'];

        $this->id = 'wc-pague-logo-credit-card';
        $this->icon = '';
        $this->has_fields = true;
        $this->method_title = 'Pague Logo - Cartão de Crédito';
        $this->method_description = 'Cartão de crédito';
        $this->supports = [
            'products'
        ];
        
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->desctiption = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->parcelas = $this->get_option('parcelas');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('wp_enqueue_scripts', [$this, 'payment_scripts']);
    }

    /**
     * Define as opções do painel de configuração do gateway.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => 'Habilitar Cartão de crédito',
                'label'       => 'Ativar o método de pagamento de cartão de crédito.',
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => 'Título',
                'type'        => 'text',
                'description' => 'Essa opção gerencia o texto que aparece no título do gateway no momento do checkout.',
                'default'     => 'Cartão de crédito',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Descrição',
                'type'        => 'textarea',
                'description' => 'Essa opção gerencia a descrição do gateway no momento do checkout.',
                'default'     => 'Realize o pagamento com cartão de crédito através da Pague Logo.',
            ),
            'parcelas' => array(
                'title' => 'Nº de Parcelas',
                'type' => 'number',
                'description' => 'Número de parcelas a serem exibidas no checkout.',
                'default' => '1',
                'desc_tip' => true
            )
        );
    }

    /**
     * payment_scripts
     * 
     * Carrega os scripts e as folhas de estilo do gateway.
     * 
     * @see https://github.com/jessepollak/card Biblioteca Javascript que gera o cartão interativo.
     */
    public function payment_scripts()
    {
        if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
            return;
        }

        if ('no' === $this->enabled) {
            return;
        }
        
        wp_enqueue_style('pague-logo-index-css', $this->plugin_dir_url . 'assets/css/index.css');
        wp_enqueue_script('pague-logo-card-js', $this->plugin_dir_url . 'assets/js/card.js', array('jquery'));
        wp_enqueue_script('pague-logo-insert-card-flag-js', $this->plugin_dir_url . 'assets/js/insert-card-flag.js', array(), false, true);
    }

    /**
     * payment_fields
     * 
     * Gera a interface front-end do gateway.
     */
    public function payment_fields()
    {
        $admin_options = [
            'parcelas'
        ];

        foreach ($admin_options as $option) {
            $$option = $this->$option;
        }

        include $this->plugin_dir_path . 'views/credit-card.php';
    }

    /**
     * validate_fields
     * 
     * Valida o preenchimento dos campos de cartão de crédito informados pelo usuário 
     * após a requisição da criação do pedido.
     */
    public function validate_fields()
    {
        $CardValidator = new CardValidator($_POST);

        try {
            $CardValidator->validaCamposDoCartao();
            $CardValidator->validaParcelas();

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * process_payment
     * 
     * Realiza a integração com a API de pagamento da Pague Logo na opção de cartão de crédito.
     * 
     * @param int $order_id
     * 
     * @return array | void
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        try {
            $PagueLogoAuth = new PagueLogoAuthentication($this->usuario, $this->senha);
            $authorization = [
                'token' => $PagueLogoAuth->getToken(),
                'whois' => $PagueLogoAuth->getWhois()
            ];

            $CreditCard = new PagueLogoCreditCard($_POST, $order, $authorization);

            $response = PagueLogoPaymentGateway::processPayment($order, $CreditCard, $authorization);

            update_post_meta($order->get_id(), 'pague_logo_response_log', json_encode($response));

        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');

            update_post_meta($order->get_id(), 'pague_logo_error_log', $e->getMessage());
            $order->update_status('failed', $e->getMessage());

            return;
        }

        // we received the payment
        $order->payment_complete();

        $order->add_order_note( 'Hey, your order is paid! Thank you!', true );

        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order )
        );

    }
}