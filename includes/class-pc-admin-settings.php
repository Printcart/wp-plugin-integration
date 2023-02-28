<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Printcart_Admin_Settings')) {

    class Printcart_Admin_Settings {

        protected static $instance;

        private $basic_auth = array();

        public static function instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct() {
            $printcart_account = get_option('printcart_account');

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
                array($this, 'printcart_products'),
                PRINTCART_PLUGIN_URL . 'assets/images/logo.svg'
            );

            add_submenu_page(
                'pc-integration-web2print',
                esc_html__('Printcart Products', 'printcart-integration'),
                esc_html__('Products', 'printcart-integration'),
                'manage_options',
                'pc-integration-web2print',
                array($this, 'printcart_products')
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

        /**
         *  Create settings to setup API key in WP Dashboard
         */
        public function printcart_products() {
            require_once PRINTCART_PLUGIN_DIR . 'includes/class-pc-product-table.php';
            $pc_table = new Printcart_Options_List_Table(); ?>

            <div class="wrap">
                <h1>
                    <?php esc_html_e('Products', 'printcart-integration'); ?>
                </h1>
                <div class="description">
                    <?php esc_html_e("Below are all the products that you have entered on Printcart Dashboard, You can import more products into Printcart Dashboard ", "printcart-integration"); ?>
                    <a href="<?php echo esc_url(PRINTCART_BACKOFFICE_URL . '/inventory') ?>"><?php esc_html_e("here", "printcart-integration") ?></a>
                </div>
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder">
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable pc-product-table">
                                <form method="post">
                                    <?php
                                    $pc_table->prepare_items();
                                    $pc_table->display();
                                    $pc_table->pc_display_pagination();
                                    ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <br class="clear">
                </div>
            </div>
        <?php
        }

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
                        $store_detail = PC_W2P_API::fetchStoreDetailsWithAuth($this->basic_auth);
                        $unauth_token = isset($store_detail['data']) && isset($store_detail['data']['unauth_token']) ? $store_detail['data']['unauth_token'] : '';
                        if ($unauth_token) {
                            $message        = __('Your settings have been saved.', 'printcart-integration');
                            $status         = 'updated';
                        }
                    } catch (Exception $e) {
                        $message = __('You have entered incorrect sid or secret. Please try again!', 'printcart-integration');
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
        ?>
            <div id="printcart-design">
                <a href="<?php echo esc_attr(PRINTCART_BACKOFFICE_URL); ?>">
                    <img src="<?php echo esc_attr(PRINTCART_PLUGIN_URL . 'assets/images/logo-printcart.svg'); ?>" class="printcart-logo" />
                </a>
                <?php
                $this->printcart_api_status();
                $this->printcart_api_form($printcart_account, $result);
                $this->printcart_setting_button_design();
                $this->printcart_account_details();
                do_action('printcart_custom_settings');
                ?>
            </div>
        <?php
        }
        public function printcart_account_details() {
            $account = PC_W2P_API::fetchAccount();
            $tier = isset($account['data']) && isset($account['data']['tier']) ? $account['data']['tier'] : '<b class="printcart-nan">N/A</b>';
            $email = isset($account['data']) && isset($account['data']['email']) ? $account['data']['email'] : '<b class="printcart-nan">N/A</b>';
            $name = isset($account['data']) && isset($account['data']['name']) ? $account['data']['name'] : '<b class="printcart-nan">N/A</b>';


        ?>
            <div class="printcart-box">
                <div class="manually-key">
                    <h3 class="manually-title">
                        <?php esc_html_e('Store details', 'printcart-integration'); ?>
                    </h3>
                    <div>
                        <a href="<?php echo esc_url(PRINTCART_BACKOFFICE_URL . '/settings') ?>" target="_blank"><?php esc_html_e(' Store settings', 'printcart-integration'); ?></a>
                    </div>
                </div>
                <hr>
                <table class="form-table pc-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label><?php esc_html_e('Name:', 'printcart-integration'); ?></label></th>
                            <td>
                                <div class="printcart-account-name"><?php echo esc_attr($name); ?></div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php esc_html_e('Email:', 'printcart-integration'); ?></label></th>
                            <td>
                                <div class="printcart-account-email"><?php echo esc_attr($email); ?></div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php esc_html_e('Tier:', 'printcart-integration'); ?></label></th>
                            <td>
                                <div class="printcart-account-tier"><?php echo esc_attr($tier); ?></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php
        }
        public function printcart_api_status() {
            $store_detail = PC_W2P_API::fetchStoreDetail();
            if (count($this->basic_auth) > 1) {
                $store_detail = PC_W2P_API::fetchStoreDetailsWithAuth($this->basic_auth);
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
        ?>

            <div class="printcart-box">
                <?php
                if ($unauth_token) {
                ?>
                    <div class="printcart-setup-instructions">
                        <h1><?php esc_html_e('Congratulations!', 'printcart-integration'); ?></h1>
                    </div>
                    <div class="printcart-connected-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#008000" class="bi bi-check-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z" />
                        </svg>
                        <span class="printcart-connected-text" style="color: #008000"><b><?php esc_html_e('Your website has been successfully connected to the Printcart dashboard.', 'printcart-integration'); ?>
                            </b></span>
                    </div>
                    <a href="<?php echo esc_attr(PRINTCART_BACKOFFICE_URL); ?>" class="pc-button-dashboard pc-button-primary button-primary" target="_blank"><?php esc_html_e('Go to Dashboard', 'printcart-integration'); ?></a>
                <?php
                } else {
                ?>
                    <div class="printcart-setup-instructions">
                        <h1><?php esc_html_e('Connect the Printcart Dashboard to your site', 'printcart-integration'); ?></h1>
                    </div>
                    <div class="printcart-connected-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ffa500" class="bi bi-x-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                        </svg>
                        <span class="printcart-connected-text" style="color: #ffa500"><b><?php esc_html_e('Your website has failed to connect to the Printcart dashboard!', 'printcart-integration'); ?></b></span>
                    </div>
                    <div class="pc-connect-dashboard button-primary" data-url="<?php echo esc_attr($url); ?>"><?php esc_html_e('Connect to Dashboard', 'printcart-integration'); ?></div>
                <?php
                }
                ?>
            </div>

        <?php
        }
        public function printcart_api_form($printcart_account, $result) {
        ?>
            <div class="printcart-box">
                <h3 class="manually-key"><?php esc_html_e('Manually enter an API key', 'printcart-integration'); ?></h3>
                <hr>
                <?php
                if (isset($result['message']) && $result['message'] && isset($result['status']) && $result['status']) {
                    echo '<div id="message" class="inline ' . esc_attr($result['status']) . '" style="margin-left: 0;"><p><strong>' . esc_html($result['message']) . '</strong></p></div>';
                }
                ?>
                <form class="printcart-form" method="post" action="" enctype="multipart/form-data">
                    <table class="form-table pc-table">
                        <tbody>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Sid: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <p>
                                        <input name="printcart_sid" value="<?php echo isset($printcart_account['sid']) ? esc_attr($printcart_account['sid']) : ''; ?>" type="text" style="width: 400px" class="">
                                    </p>
                                    <label class="description"><?php esc_html_e('Enter your Printcart sid API key.', 'printcart-integration'); ?></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Secret: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <p>
                                        <input name="printcart_secret" value="<?php echo isset($printcart_account['secret']) ? esc_attr($printcart_account['secret']) : ''; ?>" type="text" style="width: 400px" class="">
                                    </p>
                                    <label class="description"><?php esc_html_e('Enter your Printcart secret API key.', 'printcart-integration'); ?></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Unauth token: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <p>
                                        <input name="printcart_unauth_token" value="<?php echo isset($printcart_account['unauth_token']) ? esc_attr($printcart_account['unauth_token']) : ''; ?>" disabled type="text" style="width: 400px" class="">
                                    </p>
                                    <label class="description"><?php esc_html_e('Unauth token of store Printcart (Automatically generated when you enter valid sid and secret).', 'printcart-integration'); ?></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Check connection to Printcart Dashboard: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <div>
                                        <p class="button button-secondary printcart-w2p-button-check-connection" value="Test connection">
                                            <?php esc_html_e('Test connection', 'printcart-integration'); ?>
                                            <span>
                                                <div class="printcart-w2p-result-check"></div>
                                            </span>
                                        </p>
                                    </div>
                                    <label class="description"><?php esc_html_e('Enter both Sid and Secret to check.', 'printcart-integration'); ?></label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </p>
                    <p class="submit">
                        <button name="save" class="pc-button-dashboard pc-button-primary button button-primary" type="submit" value="Save changes">
                            <?php esc_html_e('Save changes', 'printcart-integration'); ?>
                        </button>
                        <input type="hidden" id="_action" name="_action" value="submit">
                    </p>
                </form>
            </div>
        <?
        }
        public function printcart_setting_button_design() {
            $message = '';
            $status = '';
            $printcart_class_button = get_option('printcart_w2p_class_design');
            $printcart_label_button = get_option('printcart_w2p_label_design');
            $printcart_button_posititon = get_option('printcart_w2p_button_posititon') ? get_option('printcart_w2p_button_posititon') : 1;
            if (isset($_POST['_action_button_design']) && $_POST['_action_button_design'] === 'submit') {
                $printcart_button_posititon = isset($_POST['printcart_button_posititon']) ? sanitize_text_field($_POST['printcart_button_posititon']) : '';
                $printcart_class_button = isset($_POST['printcart_class_button']) ? sanitize_text_field($_POST['printcart_class_button']) : '';
                $printcart_label_button = isset($_POST['printcart_label_button']) ? sanitize_text_field($_POST['printcart_label_button']) : '';
                $message        = __('Your settings have been saved.', 'printcart-integration');
                $status         = 'updated';
                update_option('printcart_w2p_class_design', $printcart_class_button);
                update_option('printcart_w2p_label_design', $printcart_label_button);
                update_option('printcart_w2p_button_posititon', $printcart_button_posititon);
            }
            $result = array(
                'message'       => $message,
                'status'    => $status,
            );
        ?>
            <div class="printcart-box">
                <h3 class="manually-key"><?php esc_html_e('Setting Button design', 'printcart-integration'); ?></h3>
                <hr>
                <?php
                if (isset($result['message']) && $result['message'] && isset($result['status']) && $result['status']) {
                    echo '<div id="message" class="inline ' . esc_attr($result['status']) . '" style="margin-left: 0;"><p><strong>' . esc_html($result['message']) . '</strong></p></div>';
                }
                ?>
                <form class="printcart-form" method="post" action="" enctype="multipart/form-data">
                    <table class="form-table pc-table">
                        <tbody>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Label for "Start design" button in product page: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <p>
                                        <input placeholder="Start design" name="printcart_label_button" value="<?php echo esc_attr($printcart_label_button); ?>" type="text" style="width: 400px" class="">
                                    </p>
                                    <label class="description"><?php esc_html_e('Enter your label to replace the default label "Start design".', 'printcart-integration'); ?></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Class for "Start design" button in product page: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <p>
                                        <input placeholder="printcart-btn-design" name="printcart_class_button" value="<?php echo esc_attr($printcart_class_button); ?>" type="text" style="width: 400px" class="">
                                    </p>
                                    <label class="description"><?php esc_html_e('Enter your class to show "Start design" button with your style.', 'printcart-integration'); ?></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Position of design button: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <p class="row">
                                        <input type="radio" name="printcart_button_posititon" value="1" <?php echo esc_attr($printcart_button_posititon == 1 ? 'checked' : ''); ?> /><?php esc_html_e('Before add to cart button and after variantions option.', 'printcart-integration'); ?>
                                    </p>
                                    <p class="row">
                                        <input type="radio" name="printcart_button_posititon" value="2" <?php echo esc_attr($printcart_button_posititon == 2 ? 'checked' : '');  ?> /><?php esc_html_e('Before variantions option.', 'printcart-integration'); ?>
                                    </p>
                                    <p class="row">
                                        <input type="radio" name="printcart_button_posititon" value="3" <?php echo esc_attr($printcart_button_posititon == 3 ? 'checked' : '');  ?> /><?php esc_html_e('After add to cart button.', 'printcart-integration'); ?>
                                    </p>
                                    <p class="row">
                                        <input type="radio" name="printcart_button_posititon" value="4" <?php echo esc_attr($printcart_button_posititon == 4 ? 'checked' : '');  ?> /><?php esc_html_e('Stick left side.', 'printcart-integration'); ?>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button name="save" class="pc-button-dashboard pc-button-primary button-primary" type="submit" value="Save changes"><?php esc_html_e('Save changes', 'printcart-integration'); ?></button>
                        <input type="hidden" name="_action_button_design" value="submit">
                    </p>
                </form>
            </div>
<?
        }
        public function admin_enqueue_scripts() {
            if (is_admin()) {
                //Todo
            }
        }
    }
}

$printcart_admin_settings = Printcart_Admin_Settings::instance();
$printcart_admin_settings->init();
