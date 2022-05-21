<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Pc_Product_Hook')) {

    class Pc_Product_Hook
    {

        protected static $instance;

        protected $config = array();

        public static function instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            $printcart_account = get_option('printcart_account');
            if ($printcart_account) {
                $this->config   = array(
                    'Username'  => isset($printcart_account['sid']) ? $printcart_account['sid'] : '',
                    'Password'  => isset($printcart_account['secret']) ? $printcart_account['secret'] : '',
                );
            }
        }

        public function init()
        {

            add_action('woocommerce_after_single_product', array($this, 'printcart_add_sdk'));

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
            $this->ajax();
        }

        public function ajax()
        {
            $ajax_events = array(
                'printcart_get_product_integration_by_variation' => true
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
        public function printcart_get_product_integration($product_id = '', $isVariant = false)
        {
            global $product;
            if (!$isVariant && !$product_id) {
                $product_id = $product->get_id();
            }
            try {
                if (empty($this->config)) return [];
                $printcart = new PHPPrintcart\PrintcartSDK($this->config);
                $data    = json_decode($printcart->Integration('woocommerce/products/' . $product_id)->get(), 'ARRAY_A');
                if (isset($data['data']) && isset($data['data']['id']) && $data['data']['id']) {
                    $integration_product_id                 = $data['data']['id'];
                    $integration_product                    = array();
                    $integration_product['id']              = $integration_product_id;
                    $integration_product['enable_design']   = $data['data']['enable_design'];
                    return $integration_product;
                }
            } catch (Exception $e) {
                return [];
            }
        }

        /**
         *  Create a new preview design in product page after pressing processing in design tool
         */
        public function printcart_options_design()
        {
?>
            <div id="printcart-options-design"></div>
            <?php
        }

        public function printcart_add_cart_item_data($cart_item_data)
        {
            $post_data = $_POST;
            if (isset($post_data['printcart_options_design']) && $post_data['printcart_options_design']) {
                $designs = $post_data['printcart_options_design'];
                $cart_item_data['printcart_options'] = array();
                $cart_item_data['printcart_options']['designs'] = $designs; // save Data design ids
            }
            return $cart_item_data;
        }

        /**
         *  Update data item
         */
        public function printcart_order_line_item($item, $cart_item_key, $values)
        {
            if (isset($values['printcart_options'])) {
                if (isset($values['printcart_options']['designs']) && $values['printcart_options']['designs']) {
                    $item->add_meta_data('_printcart_designs', serialize($values['printcart_options']['designs']));
                }
            }
        }

        public function printcart_add_sdk()
        {
            global $product;
            $product_integration    = $this->printcart_get_product_integration();
            $product_id             = isset($product_integration['id']) ? $product_integration['id'] : '';
            $enable_design          = isset($product_integration['enable_design']) ? $product_integration['enable_design'] : '';

            echo '<div id="printcart-design-tool-sdk-wrap">';
            if ($product_id) {
            ?>
                <script type="text/javascript" async="" id="printcart-design-tool-sdk" data-unauthtoken="<?= get_option('printcart_account')['unauth_token']; ?>" data-productid="<?= $product_id; ?>" src="<?php echo PRINTCART_JS_SDK_URL; ?>"></script>
                <?php
            }
            echo '</div>';

            if (!empty($product->get_children())) {
                wp_enqueue_script('pc-product-variation');
            }
        }

        public function printcart_add_design_box()
        {
            add_meta_box('printcart_order_design', esc_html__('Printcart Customer Design', 'web-to-print-online-designer'), array($this, 'printcart_order_design'), 'shop_order', 'side', 'default');
        }

        public function printcart_order_design($post)
        {
            $order_id       = $post->ID;
            $order          = wc_get_order($order_id);
            $project_id = get_post_meta($order_id, '_printcart_project_id', true);
            foreach ($order->get_items() as $order_item_id => $order_item) {
                $has_design     = false;
                echo '<p><b>Product: </b>' . $order_item->get_name() . '</p>';
                if (isset(get_option('printcart_account')['unauth_token'])) {
                    if (wc_get_order_item_meta($order_item_id, '_printcart_designs')) {
                        $printcart_designs = unserialize(wc_get_order_item_meta($order_item_id, '_printcart_designs'));
                        if (count($printcart_designs) > 0) {
                            echo '<div class="printcart_container_item_designs">';
                            foreach ($printcart_designs as $printcart_design) {
                                $has_design = true;
                                echo '<div class="button button-small button-secondary" onclick="openDesign(this)" title="View design" style="margin: 0 4px 4px 0;" data-url="' . PRINTCART_DESIGNTOOL . '/?api_key=' . get_option('printcart_account')['unauth_token'] . '&design_id=' . $printcart_design['id'] . '&task=edit"><img style="max-width: 60px; max-height: 50px" src="' . $printcart_design['preview'] . '"></div>';
                            }
                            echo '</div>';
                        }
                    }
                    if (!$has_design) echo '<p>No design in this order</p>';
                } else {
                    echo 'Invalid Api Token';
                }
                if ($project_id) {
                ?>
                    <div class="printcart-view-project">
                        <button type="button" class="button">
                            <a href="<?php echo PRINTCART_BACKOFFICE_URL . '/project/' . $project_id; ?>" target="_blank" style="text-decoration: none;">
                                View project
                            </a>
                        </button>
                    </div>
                <?php
                }
            }
            if ($has_design) {
                ?>
                <div id="pc-designtool-box">
                    <div class="pc-designtool">
                        <div class="pc-close-iframe">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#0d6efd" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z" />
                            </svg>
                        </div>
                        <iframe id="pc-designtool-iframe" src="" width="100%" height="100%" title="Design tool"></iframe>
                    </div>
                </div>
<?php
            }
        }

        /**
         *  Create a new project in the backoffice
         */
        public function printcart_create_project($order_id)
        {
            $order = wc_get_order($order_id);
            $project_id = get_post_meta($order_id, '_printcart_project_id', true);
            if ($order && !$project_id) {
                $name = 'Order ' . $order_id . ' details';
                $note = '';
                $design_ids = array();
                foreach ($order->get_items() as $order_item_id => $order_item) {
                    if (wc_get_order_item_meta($order_item_id, '_printcart_designs')) {
                        $printcart_designs = unserialize(wc_get_order_item_meta($order_item_id, '_printcart_designs'));
                        if (count($printcart_designs) > 0) {
                            foreach ($printcart_designs as $printcart_design) {
                                $design_ids[] = $printcart_design['id'];
                            }
                        }
                    }
                }
                if (count($design_ids) > 0) {
                    $project = array(
                        'name' => 'Order #' . $order_id . ' details',
                        'note' => '',
                        'design_ids' => $design_ids,
                    );
                    $printcart_account = get_option('printcart_account');
                    if (isset($printcart_account['sid']) && isset($printcart_account['secret'])) {
                        try {
                            if (empty($this->config)) return;
                            $printcart = new PHPPrintcart\PrintcartSDK($this->config);
                            $projectSave = json_decode($printcart->Project()->post($project));
                            if (isset($projectSave->data) && isset($projectSave->data->id)) {
                                $project_id = $projectSave->data->id;
                                update_post_meta($order_id, '_printcart_project_id', $project_id);
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
        public function printcart_add_preview_designs($title, $cart_item)
        {
            if (isset($cart_item['printcart_options']) && isset($cart_item['printcart_options']['designs']) && is_array($cart_item['printcart_options']['designs'])) {
                $html = '<div><b>Preview designs</b></div><table><tbody><tr>';
                foreach ($cart_item['printcart_options']['designs'] as $design) {
                    $html .= '<td style="padding: 0"><div style="border: 1px solid #ddd;margin: 0 5px 5px 0;display: inline-block;text-align: center; vertical-align: top; background: #ddd; height: 100px; width: 100px"><img src="' . $design['preview'] . '"></td>';
                }
                $html .= '</tr></tbody></table>';
                $title .= $html;
            }
            return $title;
        }

        /**
         *  Add preview design in page thankyou
         */
        public function printcart_add_preview_designs_thankyou($title, $item)
        {
            if ($item->get_meta('_printcart_designs', true)) {
                $designs = unserialize($item->get_meta('_printcart_designs', true));
                if (is_array($designs)) {
                    $html = '<div><b>Preview designs</b></div><table><tbody><tr>';
                    foreach ($designs as $design) {
                        $html .= '<td style="padding: 0"><div style="border: 1px solid #ddd;margin: 0 5px 5px 0;display: inline-block;text-align: center; vertical-align: top; background: #ddd; height: 100px; width: 100px"><img src="' . $design['preview'] . '"></td>';
                    }
                    $html .= '</tr></tbody></table>';
                    $title .= $html;
                }
            }
            return $title;
        }

        /**
         *  Callback ajax
         */
        public function printcart_get_product_integration_by_variation()
        {
            $variation_id = isset($_POST['variation_id']) ? $_POST['variation_id'] : '';
            $result = '';
            if ($variation_id && isset(get_option('printcart_account')['unauth_token'])) {
                $integration = $this->printcart_get_product_integration($variation_id, true);
                if (isset($integration['id']) && $integration['id']) {
                    $product_id     = isset($integration['id']) ? $integration['id'] : '';
                    $enable_design  = isset($integration['enable_design']) ? $integration['enable_design'] : '';
                    $result         = '<script type="text/javascript" async="" id="printcart-design-tool-sdk" data-unauthtoken="' . get_option('printcart_account')['unauth_token'] . '" data-productid="' . $product_id . '" src="' . PRINTCART_JS_SDK_URL . '"></script>';
                }
            }
            wp_send_json_success($result);
            die();
        }

        /**
         *  Hide item _printcart_designs
         */
        public function printcart_add_hidden_order_items($order_items)
        {
            $order_items[] = '_printcart_designs';
            // and so on...
            return $order_items;
        }

        public function printcart_frontend_enqueue_scripts()
        {

            wp_enqueue_style('printcart', PRINTCART_PLUGIN_URL . 'assets/css/pc-style.css', array(), PRINTCART_VERSION);

            $depends    = apply_filters('printcart_depend_js', array());

            wp_register_script('printcart', PRINTCART_PLUGIN_URL . 'assets/js/printcart.js', $depends, PRINTCART_VERSION);

            wp_register_script('pc-product-variation', PRINTCART_PLUGIN_URL . 'assets/js/pc-product-variation.js', array(), PRINTCART_VERSION);

            $args = array(
                'url'   => admin_url('admin-ajax.php'),
            );
            wp_localize_script('printcart', 'pc_frontend', $args);

            wp_enqueue_script('printcart');
        }

        public function printcart_admin_enqueue_scripts()
        {
            wp_enqueue_script('printcart', PRINTCART_PLUGIN_URL . 'assets/js/pc-admin.js', array(), PRINTCART_VERSION);

            wp_enqueue_style('printcart', PRINTCART_PLUGIN_URL . 'assets/css/pc-admin.css', array(), PRINTCART_VERSION);
        }
    }
}

$printcart_product_hook = Pc_Product_Hook::instance();
$printcart_product_hook->init();
