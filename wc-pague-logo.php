<?php

/**
 * Plugin Name: Pague Logo
 * Description: Plugin de integração com gateway de pagamento "Pague Logo" para a plataforma WooCommerce.
 * Author: SamuraiPetrus
 * Author URI: https://github.com/SamuraiPetrus
 */

require 'vendor/autoload.php';

include 'includes/wc-pague-logo-dependencies.php';
include 'includes/wc-pague-logo-payment-methods.php';
include 'includes/wc-pague-logo-admin-panel.php';

use PagueLogo\Source\CardFieldsInfo;
use PagueLogo\Source\CardValidator;
use PagueLogo\Source\PagueLogoAuthentication;
use PagueLogo\Source\PagueLogoPaymentGateway;
use PagueLogo\Source\PagueLogoCreditCard;


add_action('plugins_loaded', 'pague_logo_gateway_init');
function pague_logo_gateway_init()
{
    /**
     * Classe referente a integração WooCommerce do Gateway Pague Logo.
     * 
     * @see https://rudrastyh.com/woocommerce/payment-gateway-plugin.html Artigo que ensina a criar gateways de pagamento.
     */
    class WC_Pague_Logo_Credit_Card extends WC_Payment_Gateway
    {
        /**
         * Define as propriedades do gateway.
         */
        public function __construct()
        {
            $this->id = 'pague-logo';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = 'Pague Logo';
            $this->method_description = 'Integração para WooCommerce do gateway Pague Logo.';

            $this->supports = [
                'products'
            ];

            $this->init_form_fields();

            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->desctiption = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->sandbox = $this->get_option('sandbox');
            $this->usuario = $this->get_option('usuario');
            $this->senha = $this->get_option('senha');
            $this->parcelas = $this->get_option('parcelas');

            $this->set_environment();

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
                    'title'       => 'Habilitar Pague Logo',
                    'label'       => 'Ativar o gateway de pagamento',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'sandbox' => array(
                    'title'       => 'Habilitar Sandbox',
                    'label'       => 'Ativar o ambiente de testes da API da Pague Logo',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => 'Título',
                    'type'        => 'text',
                    'description' => 'Essa opção gerencia o texto que aparece no título do gateway no momento do checkout.',
                    'default'     => 'Pague Logo',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Descrição',
                    'type'        => 'textarea',
                    'description' => 'Essa opção gerencia a descrição do gateway no momento do checkout.',
                    'default'     => 'Disponibilize pagamentos com cartão de crédito para os clientes do seu e-commerce com o gateway da Pague Logo.',
                ),
                'usuario' => array(
                    'title'       => 'Usuário',
                    'type'        => 'text',
                    'description' => 'Usuário a ser utilizado no processo de autenticação do gateway da Pague Logo.',
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'senha' => array(
                    'title'       => 'Senha',
                    'type'        => 'password',
                    'description' => 'Senha a ser utilizada no processo de autenticação do gateway da Pague Logo.',
                    'default'     => '',
                    'desc_tip'    => true,
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
         * Define o endpoint de consumo da API de acordo com a configuração de sandbox.
         */
        public function set_environment()
        {
            $endpoint = 'https://paguelogo.com.br/api/';
            if ($this->sandbox === 'yes') {
                $endpoint = 'https://sandbox.paguelogo.com.br/api/';
            }

            $GLOBALS['PAGUE_LOGO_ENDPOINT'] = $endpoint;
        }

        /**
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
            
            wp_enqueue_style('pague-logo-index-css', plugin_dir_url(__FILE__) . '/assets/css/index.css');

            wp_enqueue_script('pague-logo-card-js', plugin_dir_url(__FILE__) . '/assets/js/card.js', array('jquery'));
            // wp_enqueue_script('pague-logo-validate-fields-js', plugin_dir_url(__FILE__) . '/assets/js/payment-fields.js', array(), false, true);
            wp_enqueue_script('pague-logo-insert-card-flag-js', plugin_dir_url(__FILE__) . '/assets/js/insert-card-flag.js', array(), false, true);
        }

        /**
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

            include 'views/payment-fields.php';
        }

        /**
         * Valida as regras de negócio do cartão.
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
         * Processa o pagamento.
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
}