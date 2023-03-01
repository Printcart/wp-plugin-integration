<?php if (!defined('ABSPATH')) exit;
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class Printcart_Options_List_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct(array(
            'singular'  => esc_html__('Products', 'printcart-integration'),
            'plural'    => esc_html__('Products', 'printcart-integration'),
            'ajax'      => false
        ));
    }
    public $next_link = '';
    public $prev_link = '';
    public $count = 0;
    public function prepare_items() {
        $columns    = $this->get_columns();
        $hidden     = array();
        $sortable   = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $per_page       = $this->get_items_per_page('options_per_page', 10);
        $cusor = isset($_GET['cusor']) ? $_GET['cusor'] : '';
        $products = PC_W2P_API::fetchProducts($per_page, $cusor);

        if (isset($products['links']) && isset($products['links']['next'])) {
            $parts_url = parse_url($products['links']['next']);
            parse_str($parts_url['query'], $query_url);
            $next_page = $query_url['cursor'];
            $this->next_link = $next_page;
        }

        if (isset($products['links']) && isset($products['links']['prev'])) {
            $parts_url = parse_url($products['links']['prev']);
            parse_str($parts_url['query'], $query_url);
            $prev_page = $query_url['cursor'];
            $this->prev_link = $prev_page;
        }
        $this->items = isset($products['data']) ? $products['data'] : array();
    }
    function get_columns() {
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'product_image'     => esc_html__('Image', 'printcart-integration'),
            'name'              => esc_html__('Name', 'printcart-integration'),
            'created_at'        => esc_html__('Date', 'printcart-integration'),
            'updated_at'        => esc_html__('Modified', 'printcart-integration'),
            'status'            => esc_html__('Status', 'printcart-integration'),
            'enable_design'     => esc_html__('Design tool', 'printcart-integration'),
            'enable_upload'     => esc_html__('Design Upload', 'printcart-integration'),
            'view'              => esc_html__('View', 'printcart-integration'),
        );
        return $columns;
    }
    public function pc_display_pagination() {
?>
        <div class="tablenav bottom show">

            <?php
            $this->pc_pagination();
            ?>

            <br class="clear" />
        </div>
<?php
    }
    public function pc_pagination() {
        $count_data = PC_W2P_API::fetchProductCount();
        $count = isset($count_data['data']) && isset($count_data['data']['count']) && $count_data['data']['count'] ? $count_data['data']['count'] : 0;
        $removable_query_args = wp_removable_query_args();
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg($removable_query_args, $current_url);
        $page_links = array();
        $output = '<span class="displaying-num">' . sprintf(
            _n('%s item', '%s items', $count),
            number_format_i18n($count)
        ) . '</span>';

        if (!$this->prev_link) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url(add_query_arg('cusor', $this->prev_link, $current_url)),
                __('Previous page'),
                '&lsaquo;'
            );
        }
        if (!$this->next_link) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url(add_query_arg('cusor', $this->next_link, $current_url)),
                __('Next page'),
                '&rsaquo;'
            );
        }
        $pagination_links_class = 'pagination-links';

        $output .= "\n<span class='$pagination_links_class'>" . implode("\n", $page_links) . '</span>';

        echo "<div class='tablenav-pages'>" . $output . "</div>";
    }
    function column_default($item, $column_name) {
        return $item[$column_name];
    }
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']);
    }
    function column_product_image($item) {
        $product_link = isset($item['id'])  ? PRINTCART_BACKOFFICE_URL . '/product/' . $item['id'] : '';
        $product_image = isset($item['product_image']) && $item['product_image']['url'] ? $item['product_image']['url'] : PRINTCART_PLUGIN_URL . 'assets/images/place-holder.jpg';
        return '<a href="' . $product_link . '" target="_blank"><img style="max-width: 40px; max-height: 40px" width="150" height="150" src="' . $product_image . '" /></a>';
    }
    function column_name($item) {
        $title      = $item['name'];
        $product_link = isset($item['id'])  ? PRINTCART_BACKOFFICE_URL . '/product/' . $item['id'] : '';
        return '<a href="' . $product_link . '" target="_blank">' . $title . '</a>';
    }
    function column_created_at($item) {
        $created_at = date('Y-m-d H:i:s', strtotime($item['created_at']));
        return $created_at;
    }
    function column_updated_at($item) {
        $update_at = (!empty($item['updated_at']) && $item['updated_at'] != '0000-00-00 00:00:00') ? $item['updated_at'] : $item['created_at'];
        $update_at = date('Y-m-d H:i:s', strtotime($update_at));
        return $update_at;
    }
    function column_status($item) {
        return '<span style="text-transform: capitalize;">' . $item['status'] . '</span>';
    }
    function column_enable_upload($item) {
        $enable_upload = isset($item['enable_upload']) ? $item['enable_upload'] : false;
        $checked = $enable_upload ? 'checked' : '';
        return '<input disabled type="checkbox" ' . $checked  . ' />';
    }
    function column_enable_design($item) {
        $enable_design = isset($item['enable_design']) ? $item['enable_design'] : false;
        $checked = $enable_design ? 'checked' : '';
        return '<input disabled type="checkbox" ' . $checked . ' />';
    }
    function column_view($item) {
        $integration_product_id = isset($item['integration_product_id']) ? $item['integration_product_id'] : '';
        if (!$integration_product_id) {
            return '';
        }
        $preview_link = get_permalink($integration_product_id);
        return '<a href="' . $preview_link . '" target="_blank">' . esc_html__('View') . '</a>';
    }
}
