<?php
/**
 * wc-pague-logo-payment-methods.php
 * 
 * Gerenciador de métodos de pagamento.
 * 
 * Estes links podem lhe ajudar:
 * @see https://woocommerce.com/document/payment-gateway-api/ Payment Gateway API documentação.
 * 
 * @author The Samurai Petrus (http://github.com/SamuraiPetrus)
 */

/**
 * init_pague_logo_gateway_classes
 * 
 * Instancia as classes de métodos de pagamento da Pague Logo.
 */
function init_pague_logo_gateway_classes() 
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    
    $plugin_dir_path = plugin_dir_path(dirname(__FILE__));

    foreach (glob($plugin_dir_path . '/payment-methods/*.php') as $payment_method) {
        include_once $payment_method;
    }
}
add_action( 'plugins_loaded', 'init_pague_logo_gateway_classes' );

/**
 * add_pague_logo_gateway_class
 * 
 * Endereça a classe do método de pagamento para ser incluída na lista de métodos de pagamento.
 * 
 * Estes links podem lhe ajudar:
 * @see https://woocommerce.com/document/payment-gateway-api/ Payment Gateway API documentação.
 * 
 * @author The Samurai Petrus (http://github.com/SamuraiPetrus)
 */
function add_pague_logo_payment_methods($payment_methods)
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    $pague_logo_methods = [
        'WC_Pague_Logo_Credit_Card',
    ];

    foreach ($pague_logo_methods as $pague_logo_method) {
        $payment_methods[] = $pague_logo_method;
    }

    return $payment_methods;
}
add_filter('woocommerce_payment_gateways', 'add_pague_logo_payment_methods');