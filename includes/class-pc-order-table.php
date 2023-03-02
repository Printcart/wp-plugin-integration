<?php if (!defined('ABSPATH')) exit;
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class Printcart_Options_List_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct(array(
            'singular'  => esc_html__('Orders', 'printcart-integration'),
            'plural'    => esc_html__('Orders', 'printcart-integration'),
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
        $orders = PC_W2P_API::fetchOrders($per_page, $cusor);
        if (isset($orders['links']) && isset($orders['links']['next'])) {
            $parts_url = parse_url($orders['links']['next']);
            parse_str($parts_url['query'], $query_url);
            $next_page = $query_url['cursor'];
            $this->next_link = $next_page;
        }

        if (isset($orders['links']) && isset($orders['links']['prev'])) {
            $parts_url = parse_url($orders['links']['prev']);
            parse_str($parts_url['query'], $query_url);
            $prev_page = $query_url['cursor'];
            $this->prev_link = $prev_page;
        }
        $this->items = isset($orders['data']) ? $orders['data'] : array();
    }
    function get_columns() {
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'order'              => esc_html__('Order', 'printcart-integration'),
            'created_at'        => esc_html__('Date', 'printcart-integration'),
            'updated_at'        => esc_html__('Modified', 'printcart-integration'),
            'status'            => esc_html__('Status', 'printcart-integration'),
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
    function column_order($item) {
        $title      = $item['name'];
        $product_link = isset($item['id'])  ? PRINTCART_BACKOFFICE_URL . '/order/' . $item['id'] : '';
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
        $status = isset($item['status']) ? $item['status'] : 'Processing';
        $class = 'success';

        switch ($status) {
            case 'processing':
                $class = 'pc-status-success';
                break;
            case 'reviewing':
                $class = 'pc-status-warning';
                break;
            case 'accepted':
                $class = 'pc-status-primary';
                break;
            case 'canceled':
                $class = 'pc-status-secondary';
                break;
            case 'trashed':
                $class = 'pc-status-secondary';
                break;
            case 'deleted':
                $class = 'pc-status-danger';
                break;
            default:
                $class = 'pc-status-success';
        }
        return '<mark class="pc-order-status ' . $class . '" style="text-transform: capitalize;"><span>' . $status . '</span></mark>';
    }
}
