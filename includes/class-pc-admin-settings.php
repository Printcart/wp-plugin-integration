<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Printcart_Admin_Settings')) {

    class Printcart_Admin_Settings {

        protected static $instance;

        protected $limit = 100;

        private $basic_auth = array();

        public static function instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct() {
            $printcart_account = get_option('printcart_w2p_account');

            if ($printcart_account) {
                $printcart_sid      = isset($printcart_account['sid']) ? $printcart_account['sid'] : '';
                $printcart_secret   = isset($printcart_account['secret']) ? $printcart_account['secret'] : '';
                $this->basic_auth =  array(
                    "Authorization" => 'Basic ' . base64_encode($printcart_sid . ':' . $printcart_secret),
                );
            }
        }

        public function init() {
            add_action('admin_menu', array($this, 'printcart_add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 30, 1);
        }

        /**
         *  Add to the Settings menu
         */
        public function printcart_add_admin_menu() {
            add_menu_page(
                esc_html__('Printcart WebToPrint', 'printcart-integration'),
                esc_html__('PC Web2Print', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print',
                array($this, 'printcart_overview'),
                PRINTCART_W2P_PLUGIN_URL . 'assets/images/logo.svg'
            );

            add_submenu_page(
                'pc-integration-web2print',
                esc_html__('Printcart Overview', 'printcart-integration'),
                esc_html__('Overview', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print',
                array($this, 'printcart_overview')
            );

            add_submenu_page(
                'pc-integration-web2print',
                esc_html__('Printcart Products', 'printcart-integration'),
                esc_html__('Products', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print/products',
                array($this, 'printcart_products')
            );

            add_submenu_page(
                'pc-integration-web2print',
                esc_html__('Printcart Orders', 'printcart-integration'),
                esc_html__('Orders', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print/orders',
                array($this, 'printcart_orders')
            );

            add_submenu_page(
                'pc-integration-web2print',
                esc_html__('Printcart Cliparts', 'printcart-integration'),
                esc_html__('Cliparts', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print/cliparts',
                array($this, 'printcart_cliparts')
            );

            add_submenu_page(
                'pc-integration-web2print',
                esc_html__('Printcart Images', 'printcart-integration'),
                esc_html__('Images', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print/images',
                array($this, 'printcart_images')
            );

            add_submenu_page(
                'pc-integration-web2print',
                esc_html__('Printcart Fonts', 'printcart-integration'),
                esc_html__('Fonts', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print/fonts',
                array($this, 'printcart_fonts')
            );

            add_submenu_page(
                'pc-integration-web2print',
                esc_html__('Printcart Templates', 'printcart-integration'),
                esc_html__('Templates', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print/templates',
                array($this, 'printcart_templates')
            );

            add_submenu_page(
                'pc-integration-web2print',
                esc_html__('Printcart Settings', 'printcart-integration'),
                esc_html__('Settings', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print/settings',
                array($this, 'printcart_settings')
            );
        }
        public function printcart_overview() {
            $printcart_account = get_option('printcart_w2p_account');
            
            $printcart_sid  = !empty($printcart_account['sid']) ? $printcart_account['sid'] : '';
            $jwtData        =  PC_W2P_API::fetJwt();
            $jwt            = !empty($jwtData['data']['access_token']) ? $jwtData['data']['access_token'] : '';

            if(!$jwt || !$printcart_sid) {
                 echo '<div id="printcart-design">';
                include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-setting-header.php');
                $this->printcart_api_status();
                echo '</div>';
                return;
            }

            $iframe_src = PRINTCART_BACKOFFICE_URL . '/setup-wizard?pc-sid=' . $printcart_sid . '&pc-token=' . $jwt;

            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-overview.php');
        }
        public function printcart_products() {
            require_once PRINTCART_W2P_PLUGIN_DIR . 'includes/class-pc-product-table.php';
            $pc_table = new Printcart_Options_List_Table();
            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-products.php');
        }
        public function printcart_orders() {
            require_once PRINTCART_W2P_PLUGIN_DIR . 'includes/class-pc-order-table.php';
            $pc_table = new Printcart_Options_List_Table();
            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-orders.php');
        }
        /**
         *  Create settings to setup API key in WP Dashboard
         */
        public function printcart_settings() {
            $printcart_account = get_option('printcart_w2p_account');
            $message = '';
            $status = '';

            if (isset($_POST['_action']) && $_POST['_action'] === 'submit') {
                $printcart_sid      = isset($_POST['printcart_sid']) ? sanitize_text_field($_POST['printcart_sid']) : '';
                $printcart_secret   = isset($_POST['printcart_secret']) ? sanitize_text_field($_POST['printcart_secret']) : '';

                $unauth_token   = '';

                if ($printcart_sid && $printcart_secret) {
                    try {
                        $this->basic_auth =  array(
                            "Authorization" => 'Basic ' . base64_encode($printcart_sid . ':' . $printcart_secret),
                        );
                        $store_detail = PC_W2P_API::fetchStoreDetailWithAuth($this->basic_auth);
                        $unauth_token = isset($store_detail['data']) && isset($store_detail['data']['unauth_token']) ? $store_detail['data']['unauth_token'] : '';
                        if ($unauth_token) {
                            $message        = esc_html__('Your settings have been saved.', 'printcart-integration');
                            $status         = 'updated';
                        }
                    } catch (Exception $e) {
                        $message = esc_html__('You have entered incorrect sid or secret. Please try again!', 'printcart-integration');
                        $status = 'error';
                    }
                }


                $printcart_account  = array(
                    'sid'           => $printcart_sid,
                    'secret'        => $printcart_secret,
                    'unauth_token'  => $unauth_token,
                );

                update_option('printcart_w2p_account', $printcart_account);
            }

            $result = array(
                'message'       => $message,
                'status'    => $status,
            );
            echo '<div id="printcart-design">';
            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-setting-header.php');
            $this->printcart_api_status();
            $this->printcart_account_details();
            $this->printcart_setting_button_design();
            $this->printcart_api_form($printcart_account, $result);
            do_action('printcart_custom_settings');
            echo '</div>';
        }
        public function printcart_account_details() {
            $account = PC_W2P_API::fetchAccount();
            $tier = isset($account['data']) && isset($account['data']['tier']) ? $account['data']['tier'] : 'N/A';
            $email = isset($account['data']) && isset($account['data']['email']) ? $account['data']['email'] : 'N/A';
            $name = isset($account['data']) && isset($account['data']['name']) ? $account['data']['name'] : 'N/A';

            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-account-detail.php');
        }
        public function printcart_api_status() {
            $store_detail = PC_W2P_API::fetchStoreDetail();
            if (count($this->basic_auth) > 1) {
                $store_detail = PC_W2P_API::fetchStoreDetailWithAuth($this->basic_auth);
            }
            $unauth_token = isset($store_detail['data']) && isset($store_detail['data']['unauth_token']) ? $store_detail['data']['unauth_token'] : '';
            $user       = wp_get_current_user();
            $user_email = $user->user_email;
            $user_name  = ($user->user_firstname ? $user->user_firstname . ' ' : '') . $user->user_lastname;
            $name       = $user->display_name ? $user->display_name : $user_name;
            $url        = PRINTCART_BACKOFFICE_URL . '/authorize';
            $site_title = get_bloginfo();

            $return_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $home_url   = home_url();

            $callback_url = trim(home_url(), '/') . '/wp-json/wc/v3/printcart/api-key';

            $url .= '?return_url=' . urlencode($return_url) . '&home_url=' . urlencode($home_url) . '&callback_url=' . urlencode($callback_url);


            if ($user_email) {
                $url .= '&email=' .  $user_email;
            }

            if ($name) {
                $url .= '&name=' .  $name;
            }

            if ($site_title) {
                $url .= '&site_title=' .  $site_title;
            }
            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-store-detail.php');
        }
        public function printcart_api_form($printcart_account, $result) {
            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-form-api.php');
        }
        public function printcart_setting_button_design() {
            $message = '';
            $status = '';
            $printcart_class_button = get_option('printcart_w2p_class_design');
            $printcart_label_button_design = get_option('printcart_w2p_label_design');
            $printcart_label_button_upload = get_option('printcart_w2p_label_upload');
            $printcart_button_posititon = get_option('printcart_w2p_button_posititon') ? get_option('printcart_w2p_button_posititon') : 1;
            $printcart_separate_design_buttons = get_option('printcart_w2p_separate_design_buttons') ? get_option('printcart_w2p_separate_design_buttons') : 'yes';
            if (isset($_POST['_action_button_design']) && $_POST['_action_button_design'] === 'submit') {
                $printcart_class_button = isset($_POST['printcart_class_button']) ? sanitize_text_field($_POST['printcart_class_button']) : '';
                $printcart_label_button_design = isset($_POST['printcart_w2p_label_design']) ? sanitize_text_field($_POST['printcart_w2p_label_design']) : '';
                $printcart_label_button_upload = isset($_POST['printcart_w2p_label_upload']) ? sanitize_text_field($_POST['printcart_w2p_label_upload']) : '';
                $printcart_button_posititon = isset($_POST['printcart_button_posititon']) ? sanitize_text_field($_POST['printcart_button_posititon']) : '';
                $printcart_separate_design_buttons = isset($_POST['printcart_separate_design_buttons']) ? sanitize_text_field($_POST['printcart_separate_design_buttons']) : '';
                $message        = __('Your settings have been saved.', 'printcart-integration');
                $status         = 'updated';
                update_option('printcart_w2p_class_design', $printcart_class_button);
                update_option('printcart_w2p_label_design', $printcart_label_button_design);
                update_option('printcart_w2p_label_upload', $printcart_label_button_upload);
                update_option('printcart_w2p_button_posititon', $printcart_button_posititon);
                update_option('printcart_w2p_separate_design_buttons', $printcart_separate_design_buttons);
            }
            $result = array(
                'message'       => $message,
                'status'    => $status,
            );
            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-form-settings.php');
        }

        public function printcart_cliparts() {
            $args               = array('page' => 'pc-integration-web2print%2Fcliparts');
            $pre_url            = add_query_arg($args, admin_url('admin.php'));
            $art_id             = 0;
            $limit              = $this->limit;
            $current_cat_id     = 0;
            $cursor             = isset($_GET['cursor']) ? wc_clean($_GET['cursor']) : '';
            $default            = isset($_GET['default']) && $_GET['default'] ? true : false;
            $prev_page          = '';
            $next_page          = '';
            $current_cat        = isset($_GET['cat_id']) ? wc_clean($_GET['cat_id']) : '';
            if (isset($current_cat)) {
                $current_cat_id = $current_cat;
            }
            $list_data      = PC_W2P_API::fetchClipartByStorageId($current_cat, $cursor, $limit, $default);
            $list           = isset($list_data['data']) ? $list_data['data'] : array();
            $cat_data       = PC_W2P_API::fetchClipartStorage($limit, $default);
            $cat            = isset($cat_data['data']) ? $cat_data['data'] : array();
            $clipart_count  = PC_W2P_API::fetchClipartCount($default);
            $total          = isset($clipart_count['data']) && isset($clipart_count['data']['count']) ? $clipart_count['data']['count'] : 0;
            if (isset($list_data['links'])) {
                if (isset($list_data['links']['next']) && $list_data['links']['next']) {
                    $parts_url = parse_url($list_data['links']['next']);
                    parse_str($parts_url['query'], $query_url);
                    $next_page = $query_url['cursor'];
                }
                if (isset($list_data['links']['prev']) && $list_data['links']['prev']) {
                    $parts_url = parse_url($list_data['links']['prev']);
                    parse_str($parts_url['query'], $query_url);
                    $prev_page = $query_url['cursor'];
                }
            }

            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-cliparts.php');
        }
        public function printcart_images() {
            $args               = array('page' => 'pc-integration-web2print%2Fimages');
            $pre_url            = add_query_arg($args, admin_url('admin.php'));
            $limit              = $this->limit;
            $cursor             = isset($_GET['cursor']) ? wc_clean($_GET['cursor']) : '';
            $prev_page          = '';
            $next_page          = '';
            $list_data          = PC_W2P_API::fetchImages($cursor, $limit);
            $list               = isset($list_data['data']) ? $list_data['data'] : array();
            $image_count        = PC_W2P_API::fetchImageCount();
            $total              = isset($image_count['data']) && isset($image_count['data']['count']) ? $image_count['data']['count'] : 0;
            if (isset($list_data['links'])) {
                if (isset($list_data['links']['next']) && $list_data['links']['next']) {
                    $parts_url = parse_url($list_data['links']['next']);
                    parse_str($parts_url['query'], $query_url);
                    $next_page = $query_url['cursor'];
                }
                if (isset($list_data['links']['prev']) && $list_data['links']['prev']) {
                    $parts_url = parse_url($list_data['links']['prev']);
                    parse_str($parts_url['query'], $query_url);
                    $prev_page = $query_url['cursor'];
                }
            }

            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-images.php');
        }
        public function printcart_fonts() {
            $args               = array('page' => 'pc-integration-web2print%2Ffonts');
            $pre_url            = add_query_arg($args, admin_url('admin.php'));
            $limit              = $this->limit;
            $cursor             = isset($_GET['cursor']) ? wc_clean($_GET['cursor']) : '';
            $default            = isset($_GET['default']) && $_GET['default'] ? true : false;
            $prev_page          = '';
            $next_page          = '';
            $font_subsets       = PC_W2P_UTILITIES::get_font_subsets();
            $list_data          = PC_W2P_API::fetchFonts($cursor, $limit, $default);
            $list               = isset($list_data['data']) ? $list_data['data'] : array();
            $image_count        = PC_W2P_API::fetchFontCount($default);
            $total              = isset($image_count['data']) && isset($image_count['data']['count']) ? $image_count['data']['count'] : 0;
            if (isset($list_data['links'])) {
                if (isset($list_data['links']['next']) && $list_data['links']['next']) {
                    $parts_url = parse_url($list_data['links']['next']);
                    parse_str($parts_url['query'], $query_url);
                    $next_page = $query_url['cursor'];
                }
                if (isset($list_data['links']['prev']) && $list_data['links']['prev']) {
                    $parts_url = parse_url($list_data['links']['prev']);
                    parse_str($parts_url['query'], $query_url);
                    $prev_page = $query_url['cursor'];
                }
            }

            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-fonts.php');
        }
        public function printcart_templates() {
            $args               = array('page' => 'pc-integration-web2print%2Ftemplates');
            $pre_url            = add_query_arg($args, admin_url('admin.php'));
            $limit              = $this->limit;
            $cursor             = isset($_GET['cursor']) ? wc_clean($_GET['cursor']) : '';
            $default            = isset($_GET['default']) && $_GET['default'] ? true : false;
            $prev_page          = '';
            $next_page          = '';
            $list_data          = PC_W2P_API::fetchTemplates($cursor, $limit, $default);
            $list               = isset($list_data['data']) ? $list_data['data'] : array();
            $image_count        = PC_W2P_API::fetchTemplateCount($default);
            $total              = isset($image_count['data']) && isset($image_count['data']['count']) ? $image_count['data']['count'] : 0;
            if (isset($list_data['links'])) {
                if (isset($list_data['links']['next']) && $list_data['links']['next']) {
                    $parts_url = parse_url($list_data['links']['next']);
                    parse_str($parts_url['query'], $query_url);
                    $next_page = $query_url['cursor'];
                }
                if (isset($list_data['links']['prev']) && $list_data['links']['prev']) {
                    $parts_url = parse_url($list_data['links']['prev']);
                    parse_str($parts_url['query'], $query_url);
                    $prev_page = $query_url['cursor'];
                }
            }

            include_once(PRINTCART_W2P_PLUGIN_DIR . 'views/printcart-templates.php');
        }
        public function admin_enqueue_scripts($hook) {
            if (is_admin()) {
                if($hook === 'toplevel_page_pc-integration-web2print') {
                    wp_enqueue_style('printcart-overview', PRINTCART_W2P_PLUGIN_URL . 'assets/css/pc-admin-overview.css', array(), PRINTCART_VERSION);
                }
            }
        }
    }
}

$printcart_admin_settings = Printcart_Admin_Settings::instance();
$printcart_admin_settings->init();
