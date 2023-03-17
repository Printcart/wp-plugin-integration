<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly  
?>
<div class="wrap">
    <h1>
        <?php esc_html_e('Products', 'printcart-integration'); ?>
    </h1>
    <div class="description">
        <?php esc_html_e("Below are all the products that you have entered on Printcart Dashboard, You can import more products into Printcart Dashboard ", "printcart-integration"); ?>
        <a href="<?php echo esc_url(PRINTCART_BACKOFFICE_URL . '/inventory'); ?>">
            <?php esc_html_e("here", "printcart-integration"); ?>
        </a>
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