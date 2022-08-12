<?php 
/**
 * REST API Custom controller
 *
 * Handles requests to the /orders endpoint.
 *
 */
if (!defined('ABSPATH')) exit;

if (!class_exists('Printcart_REST_Custom_Controller')) {
	class Printcart_REST_Custom_Controller {
		/**
		 * You can extend this class with
		 * WP_REST_Controller / WC_REST_Controller / WC_REST_Products_V2_Controller / WC_REST_CRUD_Controller etc.
		 * Found in packages/woocommerce-rest-api/src/Controllers/
		 */
		protected $namespace = 'wc/v3';

		protected $rest_base = 'printcart';

		public function __construct() {
	       	add_action( 'rest_api_init' , array( $this , 'register_routes') );
	    }

		public function register_routes() {
			register_rest_route( $this->namespace, '/' . $this->rest_base.'/api-key',
				array(
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => array( $this, 'update_api_key' ),
					'permission_callback' => array( $this, 'check_permission_unauth' ),
				)
			);
		}

		public function update_api_key( $request ) {
			$sid 			= isset($request['sid']) ? $request['sid'] : '';
			$secret 		= isset($request['secret']) ? $request['secret'] : '';
			$unauth_token 	= isset($request['unauth_token']) ? $request['unauth_token'] : '';

			$data = array(
				'result' => false,
			);

			if($sid && $secret && $unauth_token) {
				$printcart_account  = array(
                    'sid'           => $sid,
                    'secret'        => $secret,
                    'unauth_token'  => $unauth_token,
                );
				update_option('printcart_account', $printcart_account);
				$data['result'] = true;
			}

			$response = rest_ensure_response( $data );

			return $response;
		}

		public function check_permission_unauth() {
			return true;
		}

	}
}

add_filter( 'woocommerce_rest_api_get_rest_namespaces', 'pintcart_custom_api' );

function pintcart_custom_api( $controllers ) {
	$controllers['wc/v3']['printcart'] = 'Printcart_REST_Custom_Controller';

	return $controllers;
}