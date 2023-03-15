<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly  
?>
<h1><?php esc_html_e('Cliparts', 'printcart-integration'); ?></h1>
<?php echo ($notice); ?>
<div class="wrap printcart-container">
    <div class="postbox" id="pc-list-arts">
        <h3 class="pc-admin-line-height"><?php esc_html_e('List arts ', 'printcart-integration'); ?>
            <?php if (is_array($cat) && (sizeof($cat) > 0)) : ?>
                <select onchange="if (this.value) window.location.href=this.value+'#pc-list-arts'">
                    <option value="<?php echo admin_url('admin.php?page=pc-integration-web2print%2Fcliparts'); ?>"><?php esc_html_e('Select a category', 'printcart-integration'); ?></option>
                    <?php foreach ($cat as $cat_index => $val) : ?>
                        <option value="<?php echo add_query_arg(array('cat_id' => $val['id']), admin_url('admin.php?page=pc-integration-web2print%2Fcliparts')) ?>" <?php selected($val['id'], $current_cat_id); ?>><?php echo ($val['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <span class="printcart-right">
                <a href="<?php echo admin_url('admin.php?page=pc-integration-web2print%2Fcliparts'); ?>"><?php esc_html_e('All arts', 'printcart-integration'); ?></a>
            </span>
        </h3>
        <div class="printcart-list inside">
            <div class="printcart-list-arts-container">
                <?php if (is_array($list) && (sizeof($list) > 0)) : ?>
                    <?php
                    foreach ($list as $val) :
                        $art_url     = isset($val['url']) ? $val['url'] : '';
                        $art_id     = isset($val['id']) ? $val['id'] : '';;
                    ?>
                        <span class="printcart_art_link "><img src="<?php echo esc_url($art_url); ?>" /></span>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php esc_html_e('You don\'t have any art.', 'printcart-integration'); ?>
                <?php endif; ?>
            </div>
            <div class="tablenav top">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo esc_html($total) . ' ' . esc_html__('arts', 'printcart-integration'); ?></span>

                    <span class="pagination-links">
                        <?php
                        if ($prev_page) {
                            echo '<a class="prev-page" href="' . home_url() . '/wp-admin/admin.php?page=pc-integration-web2print%2Fcliparts&amp;cursor=' . $prev_page . '">≪</a>';
                        }
                        if ($next_page) {
                            echo '<a class="next-page" href="' . home_url() . '/wp-admin/admin.php?page=pc-integration-web2print%2Fcliparts&amp;cursor=' . $next_page . '">≫</a>';
                        }
                        ?>
                    </span>

                </div>
            </div>
        </div>
    </div>
</div>