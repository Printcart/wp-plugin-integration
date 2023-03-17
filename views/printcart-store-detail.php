<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly  
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
        <a href="<?php echo esc_attr(PRINTCART_BACKOFFICE_URL . '/inventory'); ?>" class="pc-button-dashboard pc-button-primary button-primary" target="_blank"><?php esc_html_e('Go to Dashboard to Import', 'printcart-integration'); ?></a>
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
        <div class="pc-connect-dashboard pc-button-dashboard pc-button-primary button-primary" data-url="<?php echo esc_attr($url); ?>"><?php esc_html_e('Connect to Dashboard', 'printcart-integration'); ?></div>
    <?php
    }
    ?>
</div>