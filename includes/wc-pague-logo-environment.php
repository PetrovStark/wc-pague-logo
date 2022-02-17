<?php
/**
 * wc-pague-logo-environment.php
 * 
 * Configurações do ambiente de execução do plugin.
 */

/**
 * set_pague_logo_environment
 * 
 * Define a URL de consumo do gateway de pagamento baseado no ambiente de execução do plugin.
 */
function set_pague_logo_environment()
{
    $admin_options = get_option('wc_pague_logo_settings');
    $sandbox_is_enabled = isset($admin_options['wc_pague_logo_sandbox']) && $admin_options['wc_pague_logo_sandbox'] === 'on';         

    $endpoint = 'https://paguelogo.com.br/api/';
    if ($sandbox_is_enabled) {
        $endpoint = 'https://sandbox.paguelogo.com.br/api/';
    }

    $GLOBALS['PAGUE_LOGO_ENDPOINT'] = $endpoint;
}
add_action('init', 'set_pague_logo_environment');