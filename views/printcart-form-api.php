<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly  
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
                            <p class="button button-secondary printcart-w2p-button-check-connection" value="<?php esc_attr_e('Test connection', 'printcart-integration'); ?>">
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
        <p class="submit">
            <button name="save" class="pc-button-dashboard pc-button-primary button button-primary" type="submit" value="<?php esc_attr_e('Save changes', 'printcart-integration'); ?>">
                <?php esc_html_e('Save changes', 'printcart-integration'); ?>
            </button>
            <input type="hidden" id="_action" name="_action" value="submit">
        </p>
    </form>
</div>