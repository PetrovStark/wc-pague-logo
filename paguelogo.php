<?php

/**
 * Plugin Name: Pague Logo
 * Description: Integração para WooCommerce do gateway Pague Logo.
 * Author: SamuraiPetrus
 * Author URI: https://github.com/SamuraiPetrus
 */

add_filter('woocommerce_payment_gateways', 'add_pague_logo_gateway_class');
function add_pague_logo_gateway_class($methods) 
{
    $methods[] = 'WC_Pague_Logo_Gateway';

    return $methods;
}

add_action('wp_enqueue_scripts', 'enqueue_michelangelo_scripts');
function enqueue_michelangelo_scripts()
{
    wp_enqueue_style( 'pague-logo-style', plugin_dir_url(__FILE__) . '/assets/css/index.css', array() );
    wp_enqueue_script( 'pague-logo-card-js-script', plugin_dir_url(__FILE__) . '/assets/js/card.js', array( 'jquery' ) );
}


add_action('plugins_loaded', 'pague_logo_gateway_init');
function pague_logo_gateway_init()
{
    if (!class_exists('WC_Payment_Gateway')) die;
    
    /**
     * Classe referente a integração WooCommerce do Gateway Pague Logo.
     */
    class WC_Pague_Logo_Gateway extends WC_Payment_Gateway
    {
        /**
         * Define as propriedades do gateway.
         */
        public function __construct()
        {
            $this->id = 'paguelogo';
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
            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->private_key = $this->testmode ? $this->get_option('test_private_key') : $this->get_option('private_key');
            $this->publishable_key = $this->testmode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable Michelangelo Gateway',
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
                    'default'     => 'Pague com cartão de crédito através da Pague Logo.',
                ),
                'testmode' => array(
                    'title'       => 'Ambiente de testes',
                    'label'       => 'Habilitar ambiente de testes',
                    'type'        => 'checkbox',
                    'description' => 'Habilita o ambiente de testes da Pague Logo',
                    'default'     => 'yes',
                    'desc_tip'    => true,
                ),
                'test_publishable_key' => array(
                    'title'       => 'Chave pública (Sandbox)',
                    'type'        => 'text'
                ),
                'test_private_key' => array(
                    'title'       => 'Chave secreta (Sandbox)',
                    'type'        => 'password',
                ),
                'publishable_key' => array(
                    'title'       => 'Chave pública',
                    'type'        => 'text'
                ),
                'private_key' => array(
                    'title'       => 'Chave secreta',
                    'type'        => 'password'
                )
            );
        }
        
        /**
         * Gera a interface do gateway.
         */
        public function payment_fields()
        {
            include 'src/payment-fields.php';
        }

        public function validate_scripts()
        {

        }

        public function validate_fields()
        {
            
        }

        public function process_payment($order_id)
        {

        }

        public function webhook()
        {

        }
    }
}
