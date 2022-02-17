<?php
/**
 * Hooks de boleto bancário
 * 
 * Esses links podem lhe ajudar:
 * @see https://rudrastyh.com/woocommerce/thank-you-page.html
 * @author The Samurai Petrus (https://github.com/SamuraiPetrus)
 */

/**
 * add_bank_bill_thank_you_session
 * 
 * Adiciona o link para o download do boleto bancário na página de agradecimento do pedido.
 * 
 * @param int $order_id
 * 
 * @return void
 */
function add_bank_bill_thank_you_session( $order_id ) 
{
    $order = wc_get_order($order_id);
    if ($order->get_payment_method() !== 'wc-pague-logo-bank-bill') {
        return;
    }
    
    $plugin_path = plugin_dir_path(dirname(__FILE__));
    $boleto_download_url = get_post_meta($order->get_id(), 'pague_logo_linkVisualizacao', true);
    $thankyou = true;
    
    include $plugin_path . 'views/download-bank-bill.php';
}
add_action( 'woocommerce_thankyou', 'add_bank_bill_thank_you_session', 5 );

/**
 * add_bank_bill_download_order_action
 * 
 * Adiciona a ação de download do boleto bancário na listagem de pedidos.
 * 
 * @param int $order_id
 * 
 * @return void
 */
function add_bank_bill_download_order_action( $actions, $order ) 
{
    if ($order->get_payment_method() === 'wc-pague-logo-bank-bill') {
        $action_slug = 'pague_logo_download_bank_bill';
        $boleto_download_url = get_post_meta($order->get_id(), 'pague_logo_linkVisualizacao', true);

        $actions[$action_slug] = array(
            'url'  => $boleto_download_url,
            'name' => 'Baixar boleto (PDF)',
        );
    }

    return $actions;
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'add_bank_bill_download_order_action', 10, 2 );

/**
 * add_bank_bill_view_order_session
 */
function add_bank_bill_view_order_session( $order_id ){
    $order = wc_get_order($order_id);
    if ($order->get_payment_method() !== 'wc-pague-logo-bank-bill') {
        return;
    }
    
    $plugin_path = plugin_dir_path(dirname(__FILE__));
    $boleto_download_url = get_post_meta($order->get_id(), 'pague_logo_linkVisualizacao', true);
    $thankyou = true;
    
    include $plugin_path . 'views/download-bank-bill.php';
}
add_action( 'woocommerce_view_order', 'add_bank_bill_view_order_session' );
