<?php

/**
 * @package Printcart
 */
/*
Plugin Name: Printcart Integration
Plugin URI: https://printcart.com
Description: Create design buttons for WC products
Version: 1.2.0
Author: Printcart Team
Author URI: https://printcart.com
Text Domain: printcart-integration
WC requires at least: 6.0.0
WC tested up to: 6.5.1
PHP: >=7.0
*/

define('PRINTCART_VERSION',            '1.2.0');
define('PRINTCART_PLUGIN_URL',         plugin_dir_url(__FILE__));
define('PRINTCART_PLUGIN_DIR',         plugin_dir_path(__FILE__));

require_once(PRINTCART_PLUGIN_DIR .    'includes/class-pc-admin-settings.php');
require_once(PRINTCART_PLUGIN_DIR .    'includes/class-pc-hook.php');
require_once(PRINTCART_PLUGIN_DIR .    'includes/class-pc-custom-api.php');
require_once(PRINTCART_PLUGIN_DIR .    'vendor/autoload.php');

register_activation_hook(__FILE__, 'printcart_plugin_activation');

function printcart_plugin_activation() {
    delete_option('printcart_account');
    add_option('printcart_plugin_do_activation_redirect', true);
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        $message = '<div class="error"><p>' . esc_html__('WooCommerce is not active. Please activate WooCommerce before using', 'printcart-integration') . ' <b>
        ' . esc_html__('Printcart Integration', 'printcart-integration') . '</b></p></div>';
        die($message);
    }
}

add_action('admin_init', 'printcart_plugin_redirect');
function printcart_plugin_redirect() {
    if (get_option('printcart_plugin_do_activation_redirect', false)) {
        delete_option('printcart_plugin_do_activation_redirect');
        wp_redirect( add_query_arg( array( 'page' => 'printcart-design' ), admin_url( 'options-general.php' ) ) );
    }
}

add_filter( 'plugin_action_links_'.plugin_basename( plugin_dir_path( __FILE__ ) . 'printcart-design.php'), 'printcart_plugin_settings_link' );
function printcart_plugin_settings_link( $links ) {
    $args = array( 'page' => 'printcart-design' );

    $url = add_query_arg( $args, admin_url( 'options-general.php' ) );

    $settings_link = '<a href="'.esc_url( $url ).'">'.__('Settings', 'printcart-integration').'</a>';
    array_unshift( $links, $settings_link ); 
    return $links; 
}


/**
 *  Designtool Url
 */
define('PRINTCART_DESIGNTOOL',          'https://customizer.printcart.com');
// define('PRINTCART_DESIGNTOOL',          'http://designtool.loc');

/**
 *  JS SDK Url
 */
define('PRINTCART_JS_SDK_URL',            'https://unpkg.com/@printcart/design-tool-sdk@1.5.0/dist/main.js');
// define('PRINTCART_JS_SDK_URL',            'http://designtool.loc/main.js');

/**
 *  backoffice Url
 */
define('PRINTCART_BACKOFFICE_URL',         'https://dashboard.printcart.com');
// define('PRINTCART_BACKOFFICE_URL',         'http://localhost:3000');

