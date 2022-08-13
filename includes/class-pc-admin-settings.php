<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Printcart_Admin_Settings')) {

    class Printcart_Admin_Settings {

        protected static $instance;

        protected $config = array();

        public static function instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct() {
            $printcart_account = get_option('printcart_account');

            if ($printcart_account) {
                $this->config   = array(
                    'Username'  => isset($printcart_account['sid']) ? $printcart_account['sid'] : '',
                    'Password'  => isset($printcart_account['secret']) ? $printcart_account['secret'] : '',
                );
            }
        }

        public function init() {
            add_action('admin_menu', array($this, 'printcart_add_admin_menu'));
        }

        /**
         *  Add to the Settings menu
         */
        public function printcart_add_admin_menu() {
            add_submenu_page(
                'options-general.php',
                esc_html__('Printcart Settings', 'printcart-integration'),
                esc_html__('Printcart Settings', 'printcart-integration'),
                'manage_options',
                'printcart-design',
                array($this, 'printcart_settings')
            );
        }

        /**
         *  Create settings to setup API key in WP Dashboard
         */
        public function printcart_settings() {
            $printcart_account = get_option('printcart_account');
            $message = '';

            if (isset($_POST['_action']) && $_POST['_action'] === 'submit') {
                $printcart_sid      = isset($_POST['printcart_sid']) ? sanitize_text_field($_POST['printcart_sid']) : '';
                $printcart_secret   = isset($_POST['printcart_secret']) ? sanitize_text_field($_POST['printcart_secret']) : '';

                $config = array(
                    'Username'  => $printcart_sid,
                    'Password'  => $printcart_secret,
                );

                $unauth_token   = '';

                if ($printcart_sid && $printcart_secret) {
                    try {
                        $printcart      = new PHPPrintcart\PrintcartSDK($config);
                        $storeDetail    = json_decode($printcart->Store()->get());

                        if ($storeDetail && isset($storeDetail->data) && isset($storeDetail->data->unauth_token)) {
                            $message        = __('Your settings have been saved.', 'printcart-integration');
                            $status         = 'updated';
                            $unauth_token   = $storeDetail->data->unauth_token;
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

                update_option('printcart_account', $printcart_account);

                if ($message) echo '<div id="message" class="inline ' . esc_attr($status) . '" style="margin-left: 0;"><p><strong>' . esc_html($message) . '</strong></p></div>';
            }

            $this->printcart_form_settings($printcart_account);
        }

        public function printcart_form_settings($printcart_account) {
            $user = wp_get_current_user();
            $user_email = $user->user_email;
            $user_name = ($user->user_firstname ? $user->user_firstname . ' ' : '') . $user->user_lastname;
            $name = $user->display_name ? $user->display_name : $user_name;
            $url = 'http://dashboard.printcart.com/authorize';

            $return_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $home_url = home_url();

            $callback_url = trim(home_url(), '/') . '/wp-json/wc/v3/printcart/api-key';

            $url .= '?return_url=' . urlencode($return_url) . '&home_url=' . urlencode($home_url) . '&callback_url=' . urlencode($callback_url);


            if ($user_email) {
                $url .= '&email=' .  $user_email;
            }

            if ($name) {
                $url .= '&name=' .  $name;
            }
?>
            <div id="printcart-design">
                <h1 class="title"><?php esc_html_e('Printcart Settings', 'printcart-integration') ?></h1>
                <form method="post" action="" enctype="multipart/form-data">
                    <table class="form-table table table-striped">
                        <tbody>
                            <tr valign="top">
                                <td colspan="2">
                                    <p><?php esc_html_e('To start using Princart, please insert your Printcart API keys to this form below.', 'printcart-integration'); ?></p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th>
                                    <span><?php esc_html_e('You can get those keys by', 'printcart-integration'); ?></span>
                                </th>
                                <td>
                                    <div class="pc-connect-dashboard button-primary" data-url="<?php echo esc_attr($url); ?>" target="_blank"><?php esc_html_e('Generate key and Connect to Dashboard', 'printcart-integration'); ?></div>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Sid: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <input name="printcart_sid" value="<?php echo isset($printcart_account['sid']) ? esc_attr($printcart_account['sid']) : ''; ?>" disabled type="text" style="width: 400px" class="">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Secret: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <input name="printcart_secret" value="<?php echo isset($printcart_account['secret']) ? esc_attr($printcart_account['secret']) : ''; ?>" disabled type="text" style="width: 400px" class="">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label><?php esc_html_e('Unauth token: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <input name="printcart_unauth_token" value="<?php echo isset($printcart_account['unauth_token']) ? esc_attr($printcart_account['unauth_token']) : ''; ?>" disabled type="text" style="width: 400px" class="">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button name="save" class="button-primary" type="submit" value="Save changes"><?php esc_html_e('Save changes', 'printcart-integration'); ?></button>
                        <input type="hidden" id="_action" name="_action" value="submit">
                    </p>
                </form>
            </div>
<?php
        }
    }
}

$printcart_admin_settings = Printcart_Admin_Settings::instance();
$printcart_admin_settings->init();
