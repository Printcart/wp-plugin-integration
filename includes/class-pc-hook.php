<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Printcart_Product_Hook')) {

    class Printcart_Product_Hook {

        protected static $instance;

        protected $basic_auth = array();

        public static function instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct() {
            $printcart_account = get_option('printcart_w2p_account');

            if ($printcart_account) {
                if ($printcart_account) {
                    $printcart_sid      = isset($printcart_account['sid']) ? $printcart_account['sid'] : '';
                    $printcart_secret   = isset($printcart_account['secret']) ? $printcart_account['secret'] : '';
                    $this->basic_auth =  array(
                        "Authorization" => 'Basic ' . base64_encode($printcart_sid . ':' . $printcart_secret),
                    );
                }
            }
        }

        public function init() {
            $printcart_button_design_posititon = get_option('printcart_w2p_button_posititon') ? get_option('printcart_w2p_button_posititon') : 1;
            if ($printcart_button_design_posititon == 1) {
                add_action('woocommerce_before_add_to_cart_button', array($this, 'printcart_add_sdk'), 30);
            } else if ($printcart_button_design_posititon == 2) {
                add_action('woocommerce_before_add_to_cart_form', array($this, 'printcart_add_sdk'), 30);
            } else if ($printcart_button_design_posititon == 3) {
                add_action('woocommerce_after_add_to_cart_button', array($this, 'printcart_add_sdk'), 30);
            } else {
                add_action('woocommerce_after_single_product', array($this, 'printcart_add_sdk'));
            }

            // Add list design id
            add_action('woocommerce_before_add_to_cart_button', array($this, 'printcart_options_design'), 25);

            // Add item data design in the cart
            add_filter('woocommerce_add_cart_item_data', array($this, 'printcart_add_cart_item_data'), 15, 1);

            // Add meta to order
            add_action('woocommerce_checkout_create_order_line_item', array($this, 'printcart_order_line_item'), 50, 3);

            // Create new an order after a successful payment
            add_action('woocommerce_thankyou', array($this, 'printcart_create_project'), 15, 1);

            // Add preview design in the cart and check out
            add_filter('woocommerce_cart_item_name', array($this, 'printcart_add_preview_designs'), 1, 2);

            add_filter('woocommerce_order_item_name', array($this, 'printcart_add_preview_designs_thankyou'), 1, 2);

            // Add options design in Order WC
            add_action('add_meta_boxes', array($this, 'printcart_add_design_box'), 35);

            // Hidden item name in order
            add_filter('woocommerce_hidden_order_itemmeta', array($this, 'printcart_add_hidden_order_items'), 10, 1);

            // Hook frontend scripts
            add_action('wp_enqueue_scripts', array($this, 'printcart_frontend_enqueue_scripts'));

            // Hook admin scripts
            add_action('admin_enqueue_scripts', array($this, 'printcart_admin_enqueue_scripts'));

            /* AJAX hook */
            $this->printcart_ajax();
        }

        public function printcart_ajax() {
            $ajax_events = array(
                'printcart_get_product_integration_by_variation' => true,
                'printcart_generate_key' => true,
                'printcart_w2p_check_connection_dashboard' => true
            );

            foreach ($ajax_events as $ajax_event => $nopriv) {
                add_action('wp_ajax_' . $ajax_event, array($this, $ajax_event));

                if ($nopriv) {
                    add_action('wp_ajax_nopriv_' . $ajax_event, array($this, $ajax_event));
                }
            }
        }

        /**
         *   Get integration id
         */
        public function printcart_get_product_integration($product_id = '', $isVariant = false) {
            global $product;
            if (!$isVariant && !$product_id) {
                $product_id = $product->get_id();
            }

            try {
                if (empty($this->basic_auth)) return array();
                $product_data  = PC_W2P_API::fetchIntegrationProductById($product_id);
                $integration_product = array();
                if (isset($product_data['data']) && isset($product_data['data']['id']) && $product_data['data']['id']) {
                    $integration_product_id                 = $product_data['data']['id'];
                    $integration_product['id']              = $integration_product_id;
                    $integration_product['enable_design']   = $product_data['data']['enable_design'];
                    $integration_product['enable_upload']   = $product_data['data']['enable_upload'];
                }
                return $integration_product;
            } catch (Exception $e) {
                return array();
            }
        }

        /**
         *  Create a new preview design in product page after pressing processing in design tool
         */
        public function printcart_options_design() {
            echo '<div id="printcart-options-design"></div>';
        }

        public function printcart_add_cart_item_data($cart_item_data) {
            if (isset($_POST['printcart_options_design']) && $_POST['printcart_options_design']) {
                $designs = $_POST['printcart_options_design'];
                $cart_item_data['printcart_options'] = array();
                $cart_item_data['printcart_options']['designs'] = $designs; // save Data design ids
            }
            return $cart_item_data;
        }

        /**
         *  Update data item
         */
        public function printcart_order_line_item($item, $cart_item_key, $values) {
            if (isset($values['printcart_options'])) {

                if (isset($values['printcart_options']['designs']) && $values['printcart_options']['designs']) {
                    $item->add_meta_data('_printcart_designs', serialize($values['printcart_options']['designs']));
                }
            }
        }

        public function printcart_add_sdk() {
            if (is_single()) {
                global $product;
                $product_integration    = $this->printcart_get_product_integration();
                $product_id             = isset($product_integration['id']) ? $product_integration['id'] : '';
                $enable_design          = isset($product_integration['enable_design']) ? $product_integration['enable_design'] : '';
                $printcart_class_button = get_option('printcart_w2p_class_design') ? get_option('printcart_w2p_class_design') : '';
                $printcart_label_button = get_option('printcart_w2p_label_design') ? get_option('printcart_w2p_label_design') : esc_html__('Start Design', 'printcart-integration');
                $posititon = get_option('printcart_w2p_button_posititon') ? get_option('printcart_w2p_button_posititon') : 1;
                $class_stick = '';
                if ($posititon == 4) {
                    $class_stick = ' printcart-stick';
                }
                $product_variation = $product->get_children();
                echo '<div id="printcart-design-tool-sdk-wrap">';
                if ($product_id && $enable_design) {
                    echo '<button data-productid="' . esc_attr($product_id) . '" class="button printcart-button-design alt ' . $printcart_class_button . $class_stick . '">' . $printcart_label_button . '</button>';
                } else if (!empty($product_variation)) {
                    echo '<button data-productid="' . esc_attr($product_id) . '" class="button printcart-button-design alt ' . $printcart_class_button . $class_stick . '" disabled>' . $printcart_label_button . '</button>';
                }
                echo '</div>';

                if (!empty($product_variation)) {
                    wp_enqueue_script('pc-product-variation');
                }
            }
        }

        public function printcart_add_design_box() {
            add_meta_box(
                'printcart_order_design',
                esc_html__('Printcart Customer Design', 'printcart-integration'),
                array($this, 'printcart_order_design'),
                'shop_order',
                'side',
                'default'
            );
        }

        public function printcart_order_design($post) {
            $order_id       = $post->ID;
            $order          = wc_get_order($order_id);
            $project_id     = get_post_meta($order_id, '_printcart_project_id', true);
            $order_items    = $order->get_items();

            if (is_array($order_items)) {

                foreach ($order_items as $order_item_id => $order_item) {
                    $has_design     = false;
                    echo '<p><b>' . esc_html__('Product:', 'printcart-integration') . ' </b>' . esc_html($order_item->get_name()) . '</p>';
                    $printcart_account = get_option('printcart_w2p_account');

                    if (isset($printcart_account['unauth_token'])) {
                        $_printcart_designs = wc_get_order_item_meta($order_item_id, '_printcart_designs');

                        if ($_printcart_designs) {
                            $printcart_designs = unserialize($_printcart_designs);

                            if (is_array($printcart_designs)) {
                                echo '<div class="printcart_container_item_designs">';
                                foreach ($printcart_designs as $printcart_design) {
                                    if (isset($printcart_design['id']) && $printcart_design['preview']) {
                                        $has_design = true;
                                        $data_url = PRINTCART_BACKOFFICE_URL . '/design/' . $printcart_design['id'];

                                        echo '<div><a class="button button-small button-secondary" title="' . esc_html__('View design', 'printcart-integration') . '" style="margin: 0 4px 4px 0;" 
                                        href="' . esc_url($data_url) . '" target="_blank"><img style="max-width: 60px; max-height: 50px" src="' . esc_url($printcart_design['preview']) . '"></a></div>';
                                    }
                                }

                                echo '</div>';
                            }
                        }

                        if (!$has_design) echo '<p>' . esc_html__('No design in this order', 'printcart-integration') . '</p>';
                    } else {
                        esc_html_e('Invalid Api Token', 'printcart-integration');
                    }
                    $this->printcart_button_view_order($project_id);
                }
            }

            if ($has_design) {
                $this->printcart_iframe_design_in_order();
            }
        }

        public function printcart_button_view_order($project_id) {

            if ($project_id) {
                $project_link = PRINTCART_BACKOFFICE_URL . '/project/' . $project_id;
?>
                <div class="printcart-view-project">
                    <button type="button" class="button">
                        <a href="<?php echo esc_url($project_link); ?>" target="_blank" style="text-decoration: none;">
                            <?php esc_html_e('View project', 'printcart-integration'); ?>
                        </a>
                    </button>
                </div>
            <?php
            }
        }

        public function printcart_iframe_design_in_order() {
            ?>
            <div id="pc-designtool-box">
                <div class="pc-designtool">
                    <div class="pc-close-iframe">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#0d6efd" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 
                            8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z" />
                        </svg>
                    </div>
                    <iframe id="pc-designtool-iframe" src="" width="100%" height="100%" title="Design tool"></iframe>
                </div>
            </div>
<?php
        }

        /**
         *  Create a new project in the backoffice
         */
        public function printcart_create_project($order_id) {
            $order = wc_get_order($order_id);
            $project_id = get_post_meta($order_id, '_printcart_project_id', true);

            if ($order && !$project_id) {
                $name           = '#' . $order_id;
                $note           = '';
                $design_ids     = array();
                $order_items    = $order->get_items();

                if ($order_items && is_array($order_items)) {
                    foreach ($order_items as $order_item_id => $order_item) {
                        $printcart_designs = unserialize(wc_get_order_item_meta($order_item_id, '_printcart_designs'));

                        if ($printcart_designs && is_array($printcart_designs)) {
                            foreach ($printcart_designs as $printcart_design) {
                                $design_ids[] = $printcart_design['id'];
                            }
                        }
                    }
                }

                if (is_array($design_ids)) {
                    $project = array(
                        'name'          => $name,
                        'note'          => $note,
                        'design_ids'    => $design_ids,
                    );
                    $printcart_account = get_option('printcart_w2p_account');

                    if (isset($printcart_account['sid']) && isset($printcart_account['secret'])) {
                        try {
                            if (empty($this->basic_auth)) return;

                            $projectSave  = PC_W2P_API::createOrder($project);
                            if (isset($projectSave['data']) && isset($projectSave['data']['id'])) {
                                update_post_meta($order_id, '_printcart_project_id', $projectSave['data']['id']);
                            }
                        } catch (Exception $e) {
                            return;
                        }
                    }
                }
            }
        }

        /**
         *  Add preview
         */
        public function printcart_add_preview_designs($title, $cart_item) {
            if (isset($cart_item['printcart_options']) && isset($cart_item['printcart_options']['designs']) && is_array($cart_item['printcart_options']['designs'])) {
                $html = '<div style="text-align: left"><b>' . esc_html__('Preview designs', 'printcart-integration') . '</b></div>';

                foreach ($cart_item['printcart_options']['designs'] as $design) {
                    if (isset($design['preview'])) {
                        $html .= '<div style="padding: 0"><div style="border: 1px solid #ddd; background: #ddd; margin: 0 5px 5px 0; max-width: 120px"><img src="' . esc_url($design['preview']) . '" style="width: 100%; object-fit:contain;"></div>';
                    }
                }
                $title .= $html;
            }
            return $title;
        }

        /**
         *  Add preview design in page thankyou
         */
        public function printcart_add_preview_designs_thankyou($title, $item) {
            if ('line_item' !== $item->get_type()) {
                return $title;
            }

            $_printcart_designs = $item->get_meta('_printcart_designs', true);

            if ($_printcart_designs) {
                $printcart_designs = unserialize($_printcart_designs);

                if (is_array($printcart_designs)) {
                    $html = '<div style="text-align: left"><b>' . esc_html__('Preview designs', 'printcart-integration') . '</b></div>';
                    foreach ($printcart_designs as $design) {
                        $html .= '<div style="padding: 0"><div style="border: 1px solid #ddd; background: #ddd; margin: 0 5px 5px 0; max-width: 120px"><img src="' . esc_url($design['preview']) . '" style="width: 100%; object-fit:contain;"></div>';
                    }
                    $title .= $html;
                }
            }
            return $title;
        }

        /**
         *  Callback ajax
         */
        public function printcart_get_product_integration_by_variation() {
            $variation_id = isset($_POST['variation_id']) ? sanitize_text_field($_POST['variation_id']) : '';
            $result = array(
                'product_id' => '',
                'enable_design' => '',
            );
            $printcart_account = get_option('printcart_w2p_account');

            if ($variation_id && isset($printcart_account['unauth_token'])) {
                $product_integration = $this->printcart_get_product_integration($variation_id, true);
                $product_id             = isset($product_integration['id']) ? $product_integration['id'] : '';
                $enable_design          = isset($product_integration['enable_design']) ? $product_integration['enable_design'] : '';
                $result = array(
                    'product_id' => $product_id,
                    'enable_design' => $enable_design,
                );
            }
            wp_send_json_success($result);
            die();
        }

        /**
         *  Callback ajax generate key
         */
        public function printcart_generate_key() {
            global $wpdb;
            $description = __('Printcart integration', 'printcart-integration');
            $permissions = 'read_write';
            $user_id     = get_current_user_id();
            $response      = array();
            $consumer_key    = 'ck_' . wc_rand_hash();
            $consumer_secret = 'cs_' . wc_rand_hash();

            if (!$user_id || ($user_id && !current_user_can('edit_user', $user_id))) {
                throw new Exception(__('You do not have permission to assign API Keys to the selected user.', 'printcart-integration'));
            }

            $data = array(
                'user_id'         => $user_id,
                'description'     => $description,
                'permissions'     => $permissions,
                'consumer_key'    => wc_api_hash($consumer_key),
                'consumer_secret' => $consumer_secret,
                'truncated_key'   => substr($consumer_key, -7),
            );

            // Delete all previously generated keys
            $wpdb->delete(
                $wpdb->prefix . 'woocommerce_api_keys',
                array(
                    'user_id'         => $user_id,
                    'description'     => $description,
                )
            );

            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_api_keys',
                $data,
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                )
            );

            $response['consumer_key']    = $consumer_key;
            $response['consumer_secret'] = $consumer_secret;
            wp_send_json_success($response);
            die();
        }
        /**
         *  Callback ajax check connection to dashboard
         */
        public function printcart_w2p_check_connection_dashboard() {
            $result = array(
                'connected' => false,
                'unauth_token' => ''
            );
            $sid           = isset($_POST['sid']) ? $_POST['sid'] : '';
            $secret        = isset($_POST['secret']) ? $_POST['secret'] : '';
            if ($sid || $secret) {
                $respone = PC_W2P_API::check_connection_with_printcart_by_key($sid, $secret);
                if ($respone) {
                    $result = $respone;
                }
            }
            wp_send_json_success($result);
            die();
        }

        /**
         *  Hide item _printcart_designs
         */
        public function printcart_add_hidden_order_items($order_items) {
            $order_items[] = '_printcart_designs';
            // and so on...
            return $order_items;
        }

        public function printcart_frontend_enqueue_scripts() {

            wp_enqueue_style('printcart', PRINTCART_PLUGIN_URL . 'assets/css/pc-style.css', array(), PRINTCART_VERSION);

            $depends = apply_filters('printcart_depend_js', array());

            wp_register_script('printcart', PRINTCART_PLUGIN_URL . 'assets/js/printcart.js', $depends, PRINTCART_VERSION);

            wp_register_script('printcart-sdk', PRINTCART_JS_SDK_URL, $depends, PRINTCART_VERSION);

            wp_register_script('pc-product-variation', PRINTCART_PLUGIN_URL . 'assets/js/pc-product-variation.js', array(), PRINTCART_VERSION);

            $printcart_account = get_option('printcart_w2p_account');

            $args = array(
                'url'           => admin_url('admin-ajax.php'),
                'unauth_token'  => '',
            );

            if (isset($printcart_account['unauth_token'])) {
                $args['unauth_token'] = $printcart_account['unauth_token'];
            }
            $args['options'] = array(
                'showRuler'         => true,
                'showGrid'          => false,
                'showBleedLine'     => false,
                'showDimensions'    => false,
            );
            wp_localize_script('printcart', 'pc_frontend', $args);

            wp_enqueue_script(array('printcart', 'printcart-sdk'));
        }

        public function printcart_admin_enqueue_scripts() {
            wp_register_script('printcart-admin', PRINTCART_PLUGIN_URL . 'assets/js/pc-admin.js', array(), PRINTCART_VERSION);

            $args = array(
                'url'   => admin_url('admin-ajax.php'),
            );
            wp_localize_script('printcart-admin', 'pc_admin', $args);

            wp_enqueue_script('printcart-admin');

            wp_enqueue_style('printcart', PRINTCART_PLUGIN_URL . 'assets/css/pc-admin.css', array(), PRINTCART_VERSION);
        }
    }
}

$printcart_product_hook = Printcart_Product_Hook::instance();
$printcart_product_hook->init();
