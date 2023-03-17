<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly  
?>
<h1><?php esc_html_e('Cliparts', 'printcart-integration'); ?></h1>
<div class="pc-tabs-wrapper">
    <nav class="pc-tabs">
        <a href="<?php echo esc_url($pre_url); ?>" class="pc-tab <?php echo esc_attr(!$default ? 'active' : ''); ?>">
            <?php esc_html_e('My cliparts', 'printcart-integration'); ?>
        </a>
        <a href="<?php echo esc_url($pre_url . '&amp;default=1'); ?>" class="pc-tab <?php echo esc_attr($default ? 'active' : ''); ?>" aria-current="true">
            <?php esc_html_e('Default cliparts', 'printcart-integration'); ?>
        </a>
    </nav>
</div>
<div class="wrap printcart-container">
    <div class="postbox" id="pc-list-arts">
        <h3 class="pc-admin-line-height">
            <div>
                <?php esc_html_e('List clipart ', 'printcart-integration'); ?>
                <?php if (is_array($cat) && (sizeof($cat) > 0)) : ?>
                    <select onchange="if (this.value) window.location.href=this.value+'#pc-list-arts'">
                        <option value="<?php echo admin_url('admin.php?page=pc-integration-web2print%2Fcliparts'); ?>"><?php esc_html_e('Select a category', 'printcart-integration'); ?></option>
                        <?php foreach ($cat as $cat_index => $val) : ?>
                            <option value="<?php echo add_query_arg(array('cat_id' => $val['id']), admin_url('admin.php?page=pc-integration-web2print%2Fcliparts')); ?>" <?php selected($val['id'], $current_cat_id); ?>>
                                <?php echo esc_html($val['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <span class="printcart-right">
                <a class="button button-outline-primary" href="<?php echo admin_url('admin.php?page=pc-integration-web2print%2Fcliparts'); ?>"><?php esc_html_e('All clipart', 'printcart-integration'); ?></a>
                <a class="button button-outline-primary" href="<?php esc_attr_e(PRINTCART_BACKOFFICE_URL . '/cliparts'); ?>"><?php esc_html_e('View on dashboard', 'printcart-integration'); ?></a>
            </span>
        </h3>
        <div class="printcart-list inside">
            <div class="printcart-list-arts-container">
                <?php if (is_array($list) && (sizeof($list) > 0)) : ?>
                    <?php
                    foreach ($list as $val) :
                        $art_url = isset($val['url']) ? $val['url'] : '';
                    ?>
                        <span class="printcart_art_link "><img src="<?php echo esc_url($art_url); ?>" /></span>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php esc_html_e('You don\'t have any clipart.', 'printcart-integration'); ?>
                <?php endif; ?>
            </div>
            <div class="tablenav top">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo esc_html($total) . ' ' . esc_html__('clipart', 'printcart-integration'); ?></span>

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