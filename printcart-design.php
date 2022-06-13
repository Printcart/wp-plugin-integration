<?php

/**
 * @package Printcart
 */
/*
Plugin Name: Printcart Integration
Plugin URI: https://printcart.com
Description: Create design buttons for WC products
Version: 1.0.0
Author: Printcart Team
Author URI: https://printcart.com
Text Domain: printcart-design
WC requires at least: 6.0.0
WC tested up to: 6.5.1
PHP: >=7.0
*/

define('PRINTCART_VERSION',            '1.0.0');
define('PRINTCART_PLUGIN_URL',         plugin_dir_url(__FILE__));
define('PRINTCART_PLUGIN_DIR',         plugin_dir_path(__FILE__));

require_once(PRINTCART_PLUGIN_DIR .    'includes/class-pc-admin-settings.php');
require_once(PRINTCART_PLUGIN_DIR .    'includes/class-pc-hook.php');
require_once(PRINTCART_PLUGIN_DIR .    'vendor/autoload.php');

register_activation_hook(__FILE__, 'printcart_plugin_activation');

function printcart_plugin_activation($network_wide)
{
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        $message = '<div class="error"><p>' . esc_html__('WooCommerce is not active. Please activate WooCommerce before using', 'printcart-integration') . ' <b>
        ' . esc_html__('Printcart Integration', 'printcart-integration') . '</b></p></div>';
        die($message);
    }
}

/**
 *  Designtool Url
 */
define('PRINTCART_DESIGNTOOL',          'https://customizer.printcart.com');

/**
 *  JS SDK Url
 */
define('PRINTCART_JS_SDK_URL',            'https://sdk.printcart.com/customizer/1.0.0/main.js');

/**
 *  backoffice Url
 */
define('PRINTCART_BACKOFFICE_URL',         'https://dashboard.printcart.com');
