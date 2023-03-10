<div id="printcart-sdk-wrap" class="<?php esc_attr_e($class_stick); ?>">
    <?php
    $start_and_upload_design_button = esc_html__('Start and Upload Design', 'printcart-integration');
    if ($printcart_separate_design_buttons === 'yes') {
        if ($product_id && !empty($product_variation)) {
            echo '<div id="pc-select_btn_design" data-productid="' . esc_attr($product_id) . '" class="button printcart-button alt pc-disabled ' . $printcart_class_button . '">' . $printcart_label_button_design . '</div>';
        } else if ($product_id && $enable_design) {
            echo '<div id="pc-select_btn_design" data-productid="' . esc_attr($product_id) . '" class="button printcart-button alt ' . $printcart_class_button . '">' . $printcart_label_button_design . '</div>';
        }
        if ($product_id && !empty($product_variation)) {
            echo '<div id="pc-select_btn_upload" data-productid="' . esc_attr($product_id) . '" class="button printcart-button alt pc-disabled ' . $printcart_class_button  . '">' . $printcart_label_button_upload . '</div>';
        } else if ($product_id && $enable_upload) {
            echo '<div id="pc-select_btn_upload" data-productid="' . esc_attr($product_id) . '" class="button printcart-button alt ' . $printcart_class_button  . '">' . $printcart_label_button_upload . '</div>';
        }
    } else {
        if ($product_id && !empty($product_variation)) {
            echo '<div id="pc-select_btn_upload-and-design" class="button printcart-button alt pc-disabled ' . $printcart_class_button . '">' . $start_and_upload_design_button . '</div>';
        } else if ($product_id && ($enable_design || $enable_upload)) {
            echo '<div id="pc-select_btn_upload-and-design" class="button printcart-button alt ' . $printcart_class_button . '">' . $start_and_upload_design_button . '</div>';
        }
    }
    ?>
</div>
<div id="pc-select_wrap">
    <div aria-label="Close" id="pc-select_close-btn"><span data-modal-x=""></span></div>
    <div class="pc-popup-wrap" id="pc-content-overlay">
        <div class="pc-popup-inner">
            <h2 id="pc-select_header"><?php esc_html_e('Choose a way to design this product', 'printcart-integration'); ?></h2>
            <div id="pc-select_container">
                <div class="pc-select_btn <?php echo esc_attr(!$enable_upload ? 'pc-disabled' : ''); ?>" id="pc-select_btn_upload" data-productid="<?php echo esc_attr($enable_upload ? $product_id : '');  ?>">
                    <div aria-hidden="true" class="pc-select_btn_wrap">
                        <div class="pc-select_btn_img">
                            <div class="pc-select_btn_img_inner">
                                <img src="https://files.printcart.com/common/upload.svg" alt="<?php esc_html_e('Printcart Uploader', 'printcart-integration'); ?>">
                            </div>
                        </div>
                        <div class="pc-select_btn_content">
                            <div class="pc-select_btn_content_inner">
                                <h3 class="pc-title"><?php esc_html_e('Upload a full design', 'printcart-integration'); ?></h3>
                                <ul>
                                    <li><?php esc_html_e('- Have a complete design', 'printcart-integration'); ?></li>
                                    <li><?php esc_html_e('- Have your own designer', 'printcart-integration'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pc-select_btn <?php echo esc_attr(!$enable_design ? 'pc-disabled' : ''); ?>" id="pc-select_btn_design" data-productid="<?php echo esc_attr($enable_design ? $product_id : '');  ?>">
                    <div aria-hidden="true" class="pc-select_btn_wrap">
                        <div class="pc-select_btn_img">
                            <div class="pc-select_btn_img_inner">
                                <img src="https://files.printcart.com/common/design.svg" alt="<?php esc_html_e('Printcart Designer', 'printcart-integration'); ?>">
                            </div>
                        </div>
                        <div class="pc-select_btn_content">
                            <div class="pc-select_btn_content_inner">
                                <h3 class="pc-title"><?php esc_html_e('Design here online', 'printcart-integration'); ?></h3>
                                <ul>
                                    <li><?php esc_html_e('- Already have your concept', 'printcart-integration'); ?></li>
                                    <li><?php esc_html_e('- Customize every details', 'printcart-integration'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>