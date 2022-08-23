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
            $status = '';

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
            }

            $result = array(
                'message'       => $message,
                'status'    => $status,
            );

            $this->printcart_form_settings($printcart_account, $result);
        }

        public function printcart_form_settings($printcart_account, $result) {
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
            <div id="printcart-design">
                <a href="<?php echo esc_attr(PRINTCART_BACKOFFICE_URL); ?>">
                    <img src="<?php echo esc_attr(PRINTCART_PLUGIN_URL . 'assets/images/logo-printcart.svg'); ?>" class="printcart-logo" />
                </a>
                <div class="printcart-box">
                    <?php
                    if (isset($printcart_account['unauth_token']) && $printcart_account['unauth_token']) {
                    ?>
                        <div class="printcart-setup-instructions">
                            <h1><?php esc_html_e('Congratulations', 'printcart-integration'); ?></h1>
                        </div>
                        <div class="printcart-connected-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#008000" class="bi bi-check-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z" />
                            </svg>
                            <span class="printcart-connected-text" style="color: #008000"><b><?php esc_html_e('Your website has been successfully connected to the Printcart dashboard.', 'printcart-integration'); ?>
                                </b></span>
                        </div>
                        <a href="<?php echo esc_attr(PRINTCART_BACKOFFICE_URL); ?>" class="pc-button-dashboard button-primary" target="_blank"><?php esc_html_e('Go to Dashboard', 'printcart-integration'); ?></a>
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
                <div class="printcart-box">
                    <a class="manually-key" href=""><?php esc_html_e('Manually enter an API key', 'printcart-integration'); ?></a>
                    <?php
                    if (isset($result['message']) && $result['message'] && isset($result['status']) && $result['status']) {
                        echo '<div id="message" class="inline ' . esc_attr($result['status']) . '" style="margin-left: 0;"><p><strong>' . esc_html($result['message']) . '</strong></p></div>';
                    }
                    ?>
                    <form class="printcart-form" method="post" action="" enctype="multipart/form-data">
                        <table class="form-table table table-striped">
                            <tbody>
                                <tr valign="top">
                                    <th class="titledesc">
                                        <label><?php esc_html_e('Sid: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                    </th>
                                    <td>
                                        <input name="printcart_sid" value="<?php echo isset($printcart_account['sid']) ? esc_attr($printcart_account['sid']) : ''; ?>" type="text" style="width: 400px" class="">
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th class="titledesc">
                                        <label><?php esc_html_e('Secret: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                                    </th>
                                    <td>
                                        <input name="printcart_secret" value="<?php echo isset($printcart_account['secret']) ? esc_attr($printcart_account['secret']) : ''; ?>" type="text" style="width: 400px" class="">
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
                            <button name="save" class="pc-button-dashboard button-primary" type="submit" value="Save changes"><?php esc_html_e('Save changes', 'printcart-integration'); ?></button>
                            <input type="hidden" id="_action" name="_action" value="submit">
                        </p>
                    </form>
                </div>
            </div>
<?php
        }
    }
}

$printcart_admin_settings = Printcart_Admin_Settings::instance();
$printcart_admin_settings->init();
