<?php
/**
 * Métodos de pagamento.
 * 
 * Adicione aqui os métodos de pagamentos que serão aceitos pelo gateway.
 */
add_filter('woocommerce_payment_gateways', 'add_pague_logo_gateway_class');
function add_pague_logo_gateway_class($methods) 
{
    $payment_methods = [
        'WC_Pague_Logo_Credit_Card'
    ];

    foreach ($payment_methods as $method) {
        $methods[] = $method;
    }

    return $methods;
}