<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('PC_W2P_API')) {

    class PC_W2P_API {
        public static $api_url = 'https://api.printcart.com/v1';
        // public static $api_url = 'http://localhost:8001/v1';
        public function __construct() {
        }

        public static function get_basic_auth() {
            $printcart_account = get_option('printcart_w2p_account');

            $sid = isset($printcart_account['sid']) ? $printcart_account['sid'] : '';

            $secret = isset($printcart_account['secret']) ? $printcart_account['secret'] : '';

            return array(
                "Authorization" => 'Basic ' . base64_encode($sid . ':' . $secret),
            );
        }

        public static function get_header_unauth_token() {
            $printcart_account = get_option('printcart_w2p_account');

            $unauth_token = isset($printcart_account['unauth_token']) ? $printcart_account['unauth_token'] : '';

            return array(
                'X-PrintCart-Unauth-Token' => $unauth_token
            );
        }

        public static function response($response, $format = true) {
            $body = wp_remote_retrieve_body($response);

            return json_decode($body, $format);
        }

        public static function fetchData($url) {
            $headers  = self::get_basic_auth();

            $response =  wp_remote_get($url, array(
                'headers'    => $headers,
            ));

            return self::response($response);
        }
        public static function postData($url, $object) {
            $headers  = self::get_basic_auth();

            $response =  wp_remote_post($url, array(
                'headers'    => $headers,
                'body'  => $object
            ));

            return self::response($response);
        }
        public static function fetchDataWithAuth($url, $auth) {

            $response =  wp_remote_get($url, array(
                'headers'    => $auth,
            ));

            return self::response($response);
        }
        public static function fetchProducts($limit = 99, $cursor = '') {
            $url = self::$api_url . '/products?limit=' . $limit;

            if ($cursor) {
                $url = self::$api_url . '/products?cursor=' . $cursor . '&limit=' . $limit;
            }

            return self::fetchData($url);
        }
        public static function fetchOrders($limit = 99, $cursor = '') {
            $url = self::$api_url . '/projects?limit=' . $limit;

            if ($cursor) {
                $url = self::$api_url . '/projects?cursor=' . $cursor . '&limit=' . $limit;
            }

            return self::fetchData($url);
        }
        public static function fetchProductCount() {
            $url = self::$api_url . '/products/count';

            return self::fetchData($url);
        }
        public static function fetchClipartByStorageId($cat_id = '', $cursor = '', $limit = 99, $is_default = false) {
            $pre_api = '/cliparts';
            $pre_storage_api = '/cliparts';
            if ($is_default) {
                $pre_api = '/cliparts/default';
                $pre_storage_api = '/cliparts-default';
            }

            $url = self::$api_url . $pre_api .  '?limit=' . $limit;

            if ($cat_id) {
                $url = self::$api_url . '/clipart-storages/' . $cat_id . $pre_storage_api . '?limit=' . $limit;
            }

            if ($cursor) {
                $url = self::$api_url . $pre_api . '?cursor=' . $cursor . '&limit=' . $limit;
            }

            return self::fetchData($url);
        }
        public static function fetchClipartStorage($limit = 99,  $is_default = false) {
            $pre_api = '/clipart-storages';
            if ($is_default) {
                $pre_api = '/clipart-storages/default';
            }

            $url = self::$api_url .  $pre_api . '?limit=' . $limit;

            return self::fetchData($url);
        }
        public static function fetchClipartCount($is_default = false) {
            $pre_api = '/cliparts';
            if ($is_default) {
                $pre_api = '/cliparts/default';
            }

            $url = self::$api_url . $pre_api . '/count';

            return self::fetchData($url);
        }
        public static function fetchImages($cursor = '', $limit = 99) {
            $url = self::$api_url . '/images?limit=' . $limit;

            if ($cursor) {
                $url = self::$api_url . '/images?cursor=' . $cursor . '&limit=' . $limit;
            }

            return self::fetchData($url);
        }
        public static function fetchImageCount() {
            $url = self::$api_url . '/images/count';

            return self::fetchData($url);
        }
        public static function fetchFonts($cursor = '', $limit = 99, $is_default = false) {
            $pre_api = '/fonts';
            if ($is_default) {
                $pre_api = '/fonts/default';
            }

            $url = self::$api_url . $pre_api . '?limit=' . $limit . '&sort=asc&sortBy=name';

            if ($cursor) {
                $url = self::$api_url . $pre_api . '?cursor=' . $cursor . '&limit=' . $limit . '&sort=asc&sortBy=name';
            }

            return self::fetchData($url);
        }
        public static function fetchFontCount($is_default = false) {
            $pre_api = '/fonts';
            if ($is_default) {
                $pre_api = '/fonts/default';
            }

            $url = self::$api_url . $pre_api . '/count';

            return self::fetchData($url);
        }
        public static function fetchTemplates($cursor = '', $limit = 99, $is_default = false) {
            $pre_api = '/templates';
            if ($is_default) {
                $pre_api = '/templates/template-default';
            }

            $url = self::$api_url . $pre_api . '?limit=' . $limit;

            if ($cursor) {
                $url = self::$api_url . $pre_api .  '?cursor=' . $cursor . '&limit=' . $limit;
            }

            return self::fetchData($url);
        }
        public static function fetchTemplateCount($is_default = false) {
            $pre_api = '/templates';
            if ($is_default) {
                $pre_api = '/templates/default';
            }

            $url = self::$api_url . $pre_api . '/count';

            return self::fetchData($url);
        }
        public static function fetchOrderCount() {
            $url = self::$api_url . '/projects/count';

            return self::fetchData($url);
        }
        public static function fetchStoreDetail() {
            $url = self::$api_url . '/stores/store-details';

            return self::fetchData($url);
        }
        public static function fetchAccount() {
            $url = self::$api_url . '/account';

            return self::fetchData($url);
        }
        public static function fetchStoreDetailWithAuth($auth) {
            $url = self::$api_url . '/stores/store-details';

            return self::fetchDataWithAuth($url, $auth);
        }
        public static function fetchIntegrationProductById($product_id) {
            $url = self::$api_url . '/integration/woocommerce/products/' . $product_id;

            return self::fetchData($url);
        }
        public static function createOrder($project) {
            $url = self::$api_url . '/projects';

            return self::postData($url, $project);
        }
        public static function check_connection_with_printcart_by_key($sid = '', $secret = '') {
            $result = array(
                'connected' => false,
                'unauth_token' => ''
            );
            if (!$sid || !$secret) {
                return false;
            }
            $url = self::$api_url . '/stores/store-details';
            $response =  wp_remote_request($url,     array(
                'headers'   => array(
                    "Authorization" => 'Basic ' . base64_encode($sid . ':' . $secret)
                ),
                'method'    => "GET",
            ));

            $store_detail = self::response($response);

            if (isset($store_detail['data']) && isset($store_detail['data']['unauth_token']) && $store_detail['data']['unauth_token']) {
                $result = array(
                    'connected' => true,
                    'unauth_token' => $store_detail['data']['unauth_token']
                );
            }
            return $result;
        }
    }
}
