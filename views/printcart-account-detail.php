<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly  
?>
<div class="printcart-box">
    <div class="manually-key">
        <h3 class="manually-title">
            <?php esc_html_e('Store details', 'printcart-integration'); ?>
        </h3>
        <div>
            <a href="<?php echo esc_url(PRINTCART_BACKOFFICE_URL . '/settings'); ?>" target="_blank"><?php esc_html_e(' Store settings', 'printcart-integration'); ?></a>
        </div>
    </div>
    <hr>
    <table class="form-table pc-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><label><?php esc_html_e('Name:', 'printcart-integration'); ?></label></th>
                <td>
                    <div class="printcart-account-info <?php echo $name == 'N/A' ? 'printcart-nan' : '';  ?>"><?php echo esc_html($name); ?></div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php esc_html_e('Email:', 'printcart-integration'); ?></label></th>
                <td>
                    <div class="printcart-account-info <?php echo $email == 'N/A' ? 'printcart-nan' : '';  ?>"><?php echo esc_html($email); ?></div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php esc_html_e('Tier:', 'printcart-integration'); ?></label></th>
                <td>
                    <div class="printcart-account-info <?php echo $tier == 'N/A' ? 'printcart-nan' : '';  ?>"><?php echo esc_html($tier); ?></div>
                </td>
            </tr>
        </tbody>
    </table>
</div>