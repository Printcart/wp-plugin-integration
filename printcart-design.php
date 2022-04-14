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
WC requires at least: 3.0.0
WC tested up to: 5.0.0
PHP: >=7.0
*/

define( 'PRINTCART_PLUGIN_URL',             plugin_dir_url( __FILE__ ) );
define( 'PRINTCART_PLUGIN_DIR',             plugin_dir_path( __FILE__ ) );
require_once( PRINTCART_PLUGIN_DIR . 'includes/class-admin-options.php' );
require_once( PRINTCART_PLUGIN_DIR . 'vendor/autoload.php' );