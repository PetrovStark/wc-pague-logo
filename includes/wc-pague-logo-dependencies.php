<?php
/**
 * Dependências do plugin.
 * 
 * Adicione aqui os plugins que serão necessários para que este funcione.
 */
register_activation_hook( plugin_dir_path(dirname(__FILE__)) . 'wc-pague-logo.php', 'check_dependencies' );
function check_dependencies()
{
    $dependencies = [
        'woocommerce/woocommerce.php' => 'WooCommerce',
        'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php' => 'Brazilian Market on WooCommerce',
    ];

    foreach ($dependencies as $required_plugin => $plugin_name) {
        if ( ! is_plugin_active( $required_plugin ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( 'Este plugin requer a ativação do seguinte plugin: '.$plugin_name.'<br><br><a href="#" onclick="history.back()"><-Voltar para plugins</a>' );
        }
    }
}