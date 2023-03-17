<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly  
?>
<h1><?php esc_html_e('Fonts', 'printcart-integration'); ?></h1>
<div class="pc-tabs-wrapper">
    <nav class="pc-tabs">
        <a href="<?php echo esc_url($pre_url); ?>" class="pc-tab <?php echo esc_attr(!$default ? 'active' : ''); ?>">
            <?php esc_html_e('My fonts', 'printcart-integration'); ?>
        </a>
        <a href="<?php echo esc_url($pre_url . '&amp;default=1'); ?>" class="pc-tab <?php echo esc_attr($default ? 'active' : ''); ?>" aria-current="true">
            <?php esc_html_e('Default fonts', 'printcart-integration'); ?>
        </a>
    </nav>
</div>
<div class="wrap printcart-container">
    <div class="postbox" id="pc-list-arts">
        <h3 class="pc-admin-line-height">
            <?php esc_html_e('List fonts ', 'printcart-integration'); ?>
            <a class="button button-outline-primary" href="<?php esc_attr_e(PRINTCART_BACKOFFICE_URL . '/fonts'); ?>">
                <?php esc_html_e('View on dashboard', 'printcart-integration'); ?>
            </a>
        </h3>
        <div class="printcart-list inside">
            <div class="printcart-list-arts-container">
                <?php if (is_array($list) && (sizeof($list) > 0)) : ?>
                    <?php foreach ($list as $val) :
                        $preview_text   = 'Abc Xyz';
                        $subset         = isset($val['subset']) ? $val['subset'] : '';
                        if ($subset && isset($font_subsets[$subset])) {
                            $preview_text       = isset($font_subsets[$subset]['preview_text']) ? $font_subsets[$subset]['preview_text'] : $preview_text;
                        }
                    ?>
                        <div class="gg-font-preview">
                            <div class="gg-font-preview-inner-wrap" style="font-family: <?php echo esc_attr($val['alias']); ?>,-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif">
                                <div class="gg-font-preview-inner">
                                    <p class="gg-font-name"><?php echo esc_attr($val['name']); ?></p>
                                    <span class="font-preview ng-binding" style="" contenteditable="true"><?php esc_html_e($preview_text); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php esc_html_e('You don\'t have any custom font.', 'web-to-print-online-designer'); ?>
                <?php endif; ?>
            </div>
            <div class="tablenav top">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo esc_html($total) . ' ' . esc_html__('font', 'printcart-integration'); ?></span>

                    <span class="pagination-links">
                        <?php
                        if ($prev_page) {
                            echo '<a class="prev-page" href="' . esc_url($pre_url) . '&amp;cursor=' . $prev_page . '&default=' . ($default ? 1 : 0) . '">≪</a>';
                        }
                        if ($next_page) {
                            echo '<a class="next-page" href="' . esc_url($pre_url)  . '&amp;cursor=' . $next_page . '&default=' . ($default ? 1 : 0) . '">≫</a>';
                        }
                        ?>
                    </span>

                </div>
            </div>
        </div>
    </div>
</div>