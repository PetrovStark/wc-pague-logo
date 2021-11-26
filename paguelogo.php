<?php

/**
 * Plugin Name: Pague Logo
 * Description: Plugin de integração com gateway de pagamento "Pague Logo" para a plataforma WooCommerce.
 * Author: SamuraiPetrus
 * Author URI: https://github.com/SamuraiPetrus
 */

require 'vendor/autoload.php';

use PagueLogo\Source\CardFieldsInfo;
use PagueLogo\Source\CardValidator;
use PagueLogo\Source\PagueLogoAuthentication;

register_activation_hook( __FILE__, 'activation_hook' );
function activation_hook()
{
    if ( ! class_exists( 'WooCommerce' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( 'Este plugin requer a instalação do WooCommerce.' );
    }
}


add_filter('woocommerce_payment_gateways', 'add_pague_logo_gateway_class');
function add_pague_logo_gateway_class($methods) 
{
    $methods[] = 'WC_Pague_Logo_Gateway';

    return $methods;
}


add_action('plugins_loaded', 'pague_logo_gateway_init');
function pague_logo_gateway_init()
{
    /**
     * Classe referente a integração WooCommerce do Gateway Pague Logo.
     * 
     * @see https://rudrastyh.com/woocommerce/payment-gateway-plugin.html Artigo que ensina a criar gateways de pagamento.
     */
    class WC_Pague_Logo_Gateway extends WC_Payment_Gateway
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
            $this->usuario = $this->get_option('usuario');
            $this->senha = $this->get_option('senha');

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
            );
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
            wp_enqueue_script('pague-logo-validate-fields-js', plugin_dir_url(__FILE__) . '/assets/js/payment-fields.js', array(), false, true);
        }

        /**
         * Gera a interface front-end do gateway.
         */
        public function payment_fields()
        {
            include 'views/payment-fields.php';
        }

        /**
         * Valida as regras de negócio do cartão.
         */
        public function validate_fields()
        {
            $CardFieldsInfo = new CardFieldsInfo();
            $CardValidator = new CardValidator();

            $excecoes = $CardFieldsInfo->getExcecoesDoCartao();

            $errors = 0;
            foreach ($CardFieldsInfo->getCamposDoCartao() as $key) {

                $field_name = 'billing_'.$key['slug'];

                $value = $_POST[$field_name];

                if (empty($value)) {
                    wc_add_notice($excecoes['required_'.$field_name], 'error');
                    $errors++;

                    continue;
                }

                if ('billing_card_expiry' === $field_name) {
                    if ($CardValidator->verificaDataExpiracao($value)) {
                        wc_add_notice($excecoes['expired_billing_card_expiry'], 'error');
                        $errors++;

                        continue;
                    }
                }
            }

            if ($errors > 0) {
                return false;
            }

            return true;
        }

        /**
         * Processa o pagamento.
         */
        public function process_payment($order_id)
        {
            global $woocommerce;

            $order = wc_get_order($order_id);

            try {
                // throw new Exception(json_encode($_POST));
                $PagueLogoAuth = new PagueLogoAuthentication($this->usuario, $this->senha);
                $authorization = [
                    'token' => $PagueLogoAuth->getToken(),
                    'whois' => $PagueLogoAuth->getWhois()
                ];

                $CreditCard = new PagueLogoCreditCard($_POST);

                PagueLogoPaymentProcessor::processPayment($order, $CreditCard, $authorization);

            } catch (Exception $e) {
                wc_add_notice($e->getMessage(), 'error');
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