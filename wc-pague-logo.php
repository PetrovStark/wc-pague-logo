<?php

/**
 * Plugin Name: Pague Logo
 * Description: Plugin de integração com gateway de pagamento "Pague Logo" para a plataforma WooCommerce.
 * Author: SamuraiPetrus
 * Author URI: https://github.com/SamuraiPetrus
 */
require 'vendor/autoload.php';

include 'includes/wc-pague-logo-dependencies.php';
include 'includes/wc-pague-logo-admin-panel.php';
include 'includes/wc-pague-logo-environment.php';
include 'includes/wc-pague-logo-enqueue-global-scripts.php';
include 'includes/wc-pague-logo-payment-methods.php';