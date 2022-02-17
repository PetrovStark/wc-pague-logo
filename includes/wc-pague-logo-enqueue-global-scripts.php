<?php
/**
 * pague_logo_enqueue_global_scripts
 * 
 * Inclui os scripts e as folhas de estilo globais.
 */
function pague_logo_enqueue_global_scripts()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    $plugin_dir_url = plugin_dir_url(dirname(__FILE__));

    $global_scripts = [
        [
            'script_type' => 'style',
            'script_id' => 'pague-logo-payment-methods-css',
            'script_url' => $plugin_dir_url . 'assets/css/payment-methods.css',
        ],
        [
            'script_type' => 'style',
            'script_id' => 'pague-logo-download-bank-bill',
            'script_url' => $plugin_dir_url . 'assets/css/download-bank-bill.css'
        ]
    ];

    foreach ($global_scripts as $global_script) {
        switch ($global_script['script_type']) {
            case 'script' :
                wp_enqueue_script($global_script['script_id'], $global_script['script_url'], array(), false, true);
                break;
            case 'style' :
                wp_enqueue_style($global_script['script_id'], $global_script['script_url']);
                break;
            default :
                break;
        }
    }
}
add_action('wp_enqueue_scripts', 'pague_logo_enqueue_global_scripts');