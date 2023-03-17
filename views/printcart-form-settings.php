<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly  
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
                            <input placeholder="<?php echo esc_html_e('Start design', 'printcart-integration'); ?>" name="printcart_w2p_label_design" value="<?php echo esc_attr($printcart_label_button_design); ?>" type="text" style="width: 400px" class="">
                        </p>
                        <label class="description"><?php esc_html_e('Enter your label to replace the default label "Start design".', 'printcart-integration'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th class="titledesc">
                        <label><?php esc_html_e('Label for "Upload design" button in product page: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                    </th>
                    <td>
                        <p>
                            <input placeholder="<?php echo esc_html_e('Upload design', 'printcart-integration'); ?>" name="printcart_w2p_label_upload" value="<?php echo esc_attr($printcart_label_button_upload); ?>" type="text" style="width: 400px" class="">
                        </p>
                        <label class="description"><?php esc_html_e('Enter your label to replace the default label "Upload design".', 'printcart-integration'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th class="titledesc">
                        <label><?php esc_html_e('Class for "Button design" button in product page: ', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
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
                        <label><?php esc_html_e('Separate artwork action buttons', 'printcart-integration'); ?><span class="printcart-help-tip"></span></label>
                    </th>
                    <td>
                        <p class="row">
                            <input type="radio" name="printcart_separate_design_buttons" value="yes" <?php echo esc_attr($printcart_separate_design_buttons == 'yes' ? 'checked' : ''); ?> /><?php esc_html_e('Yes', 'printcart-integration'); ?>
                        </p>
                        <p class="row">
                            <input type="radio" name="printcart_separate_design_buttons" value="no" <?php echo esc_attr($printcart_separate_design_buttons == 'no' ? 'checked' : '');  ?> /><?php esc_html_e('No', 'printcart-integration'); ?>
                        </p>
                        <label class="description"><?php esc_html_e('Show artwork actions as buttons directly on the product page instead of wrap them on the popup.', 'printcart-integration'); ?></label>
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
                            <input type="radio" name="printcart_button_posititon" value="4" <?php echo esc_attr($printcart_button_posititon == 4 ? 'checked' : '');  ?> /><?php esc_html_e('Stick right side.', 'printcart-integration'); ?>
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