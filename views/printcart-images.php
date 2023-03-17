<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly  
?>
<h1><?php esc_html_e('Images', 'printcart-integration'); ?></h1>
<div class="wrap printcart-container">
    <div class="postbox" id="pc-list-arts">
        <h3 class="pc-admin-line-height">
            <?php esc_html_e('List images ', 'printcart-integration'); ?>
            <a class="button button-outline-primary" href="<?php esc_attr_e(PRINTCART_BACKOFFICE_URL . '/images'); ?>"><?php esc_html_e('View on dashboard', 'printcart-integration'); ?></a>
        </h3>
        <div class="printcart-list inside">
            <div class="printcart-list-arts-container">
                <?php if (is_array($list) && (sizeof($list) > 0)) : ?>
                    <?php
                    foreach ($list as $val) :
                        $art_url     = isset($val['url']) ? $val['url'] : '';
                    ?>
                        <span class="printcart_art_link "><img src="<?php echo esc_url($art_url); ?>" /></span>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php esc_html_e('You don\'t have any image.', 'printcart-integration'); ?>
                <?php endif; ?>
            </div>
            <div class="tablenav top">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo esc_html($total) . ' ' . esc_html__('images', 'printcart-integration'); ?></span>

                    <span class="pagination-links">
                        <?php
                        if ($prev_page) {
                            echo '<a class="prev-page" href="' . esc_url($pre_url) . '&amp;cursor=' . $prev_page . '">≪</a>';
                        }
                        if ($next_page) {
                            echo '<a class="next-page" href="' . esc_url($pre_url)  . '&amp;cursor=' . $next_page . '">≫</a>';
                        }
                        ?>
                    </span>

                </div>
            </div>
        </div>
    </div>
</div>