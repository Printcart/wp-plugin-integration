<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('PRINTCARTDESIGN') ){

    class PRINTCARTDESIGN {

        protected static $instance;

        public $designtool_url = 'http://designtool.loc';

        protected $config = array();

        private $username = 'printcart@gmail.com';

        private $password = 'printcart';

        protected $api_url = 'https://02b8-117-7-237-55.ngrok.io/v1/integration/product/';

        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        public function __construct() {
            $this->config = array(
                'auth' => [
                    $this->username,
                    $this->password
                ]
            );
        }
        public function init(){
            add_action( 'admin_menu' , array( $this , 'printcart_add_admin_menu' ) );
            add_action( 'woocommerce_after_single_product' , array( $this , 'printcart_add_sdk' ) );
            // Add list design id
            add_action( 'woocommerce_before_add_to_cart_button' , array( $this , 'printcart_options_design' ), 25 );
            // Add item data design in the cart
            add_filter( 'woocommerce_add_cart_item_data', array( $this, 'printcart_add_cart_item_data' ), 15, 1 );
            // Add meta to order
            add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'printcart_order_line_item' ), 50, 3 );
            // Tạo project trên printcart backoffice
            add_action( 'woocommerce_checkout_order_processed', array( $this, 'printcart_create_project' ), 15, 3 );
            // Add preview design in the cart and check out
            add_filter( 'woocommerce_cart_item_name', array( $this, 'printcart_add_preview_designs' ), 1, 3 );
            // add options design in Order WC
            add_action( 'add_meta_boxes', array( $this, 'printcart_add_design_box' ), 35 );
            // hidden item name in order
            add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'printcart_add_hidden_order_items'), 10 , 1 );

            /* AJAX hook */
            $this->ajax();
        }
        public function ajax(){
            $ajax_events = array(
                'printcart_get_product_integration_by_variation' => true
            );
            foreach ($ajax_events as $ajax_event => $nopriv) {
                add_action('wp_ajax_' . $ajax_event, array($this, $ajax_event));
                if ($nopriv) {
                    add_action('wp_ajax_nopriv_' . $ajax_event, array($this, $ajax_event));
                }
            }
        }
        public function printcart_add_admin_menu() {
            add_submenu_page( "options-general.php", "Printcart Settings", "Printcart Settings", "manage_options", 'printcart-design', array( $this ,'printcart_settings' ));
        }
        public function printcart_settings() {
            $access_token = get_option('printcart_access_token');
            if( isset($_POST['_action']) && $_POST['_action'] == 'submit' ) {
                $access_token         = isset($_POST['printcart_access_token']) ? $_POST['printcart_access_token'] : '';
                update_option('printcart_access_token' , $access_token );
                ?>
                <div id="message" class="updated inline"><p><strong>Your settings have been saved.</strong></p></div>
                <?php
            }
            ?>
            <style type="text/css">
                #printcart-design div.description {
                    color: #909090;
                    font-style: italic;
                }
            </style>
            <div id="printcart-design">
                <h1 class="title">Printcart Settings</h1>
                <form method="post" action="" enctype="multipart/form-data">
                    <table class="form-table table table-striped">
                        <tbody>
                            <tr valign="top">
                                <th class="titledesc">
                                    <label>Access Token: <span class="printcart-help-tip"></span></label>
                                </th>
                                <td>
                                    <input name="printcart_access_token" value="<?php echo esc_attr($access_token); ?>" type="text" style="width: 400px" class="">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save changes</button>
                        <input type="hidden" id="_action" name="_action" value="submit">
                    </p>
                </form>
            </div>
            <?php
        }
        public function printcart_get_access_token() {
            return get_option('printcart_access_token');
        }
        public function printcart_get_product_integration($product_id = '') {
            global $product;
            if(!$product_id) {
                $product_id = $product->get_id();
            }
            $client = new \GuzzleHttp\Client();
            try {
                $response = $client->request('GET', $this->api_url.$product_id, $this->config);
                // $response = $client->request('GET', $this->api_url.'173', $this->config); // test
                $data = json_decode($response->getBody()->getContents() , 'ARRAY_A');
                if( isset($data['data']) ) {
                    $integration_product_id = $data['data']['id'];
                }
                if( !$integration_product_id ) return;
                $integration_product = array();
                $integration_product['id'] = $integration_product_id;
                $integration_product['enable_design'] = $data['data']['enable_design'];
                return $integration_product;
            } catch (Exception $e) {
                return;
            }
            
        }
        public function printcart_options_design() {
            ?>
            <style type="text/css">
                .printcart-options-design .design-thumbail {
                    border:  1px solid #ddd;
                    margin: 0 5px 5px 0;
                    display: inline-block;
                    text-align: center;
                    vertical-align: top;
                    background: #ddd;
                    height: 100px;
                }
                .printcart-options-design .design-thumbail img{
                    width: 100%;
                    height: auto;
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                }
            </style>
            <div id="printcart-options-design">
                <!-- <input id="design-ids" type="hidden" name="printcart_options_design" value=""> -->
                <!-- <input id="design-ids" type="hidden" name="printcart_options_design" value="dfc15d9f-09dd-408c-bccf-b4d867d84bf2,659f584f-4149-40f1-96a0-436c6b93bde2,f611df77-0496-4d42-8c25-db468b549d5b"> -->
            </div>
            <?php
        }
        public function printcart_add_cart_item_data( $cart_item_data ){
            $post_data = $_POST;
            if( isset($post_data['printcart_options_design']) || $post_data['printcart_options_design'] ){
                $designs = $post_data['printcart_options_design'];
                $cart_item_data['printcart_options'] = array();
                $cart_item_data['printcart_options']['designs'] = $designs; // save Data design ids
            }
            return $cart_item_data;
        }
        public function printcart_order_line_item( $item, $cart_item_key, $values ){
            if ( isset( $values['printcart_options'] ) ) {
                if( isset( $values['printcart_options']['designs'] ) && $values['printcart_options']['designs'] ) {
                    $item->add_meta_data('_printcart_designs', serialize($values['printcart_options']['designs']));
                }
            }
        }
        public function printcart_add_sdk() {
            global $product;
            $product_id = $this->printcart_get_product_integration()['id'];
            $enable_design = $this->printcart_get_product_integration()['enable_design'];

            /**
             *  Tạo thẻ div ở trong trang sản phẩm để hook các script
             */
            echo '<div id="printcart-design-tool-sdk-wrap">';
                if( $this->printcart_get_access_token() && $product_id && $enable_design  ) {
                    ?>
                        <script type="text/javascript" async="" id="printcart-design-tool-sdk" data-unauthtoken="<?= $this->printcart_get_access_token();?>" data-productid="<?= $product_id;?>" src="<?php echo $this->designtool_url.'/main.js'; ?>"></script>
                    <?php
                }
                ?>

                <!-- Bắt sự kiện khi nhấn process trong design tool -->
                <script type="text/javascript">
                    window.addEventListener("message", function(event){
                        var designs = event.data.designs;
                        var html = '';
                        if( designs.length <= 0 ) return;
                        html += '<div><b>Preview designs</b></div><table><tbody><tr>';
                        designs.forEach(function(design, index) {
                            html += '<td><div class="design-thumbail" style="border: 1px solid #ddd;margin: 0 5px 5px 0;display: inline-block;text-align: center; vertical-align: top; background: #ddd; height: 100px; width: 100px"><img src="'+design.url+'"></div><input id="design-id" type="hidden" name="printcart_options_design['+index+'][id]" value="'+design.id+'"><input id="design-preview" type="hidden" name="printcart_options_design['+index+'][preview]" value="'+design.url+'"></td>';
                        })
                        html += '</tr></tbody></table>';
                        document.getElementById('printcart-options-design').innerHTML = html;
                        document.getElementById('pcdesigntool-iframe-wrapper').style.display = 'none';
                        
                    } , false);
                </script>
                <?php
            echo '</div>';

            /**
             *  Nếu product có variation thì thêm script để thay đổi product id khi chọn variation options
             */
            if( !empty($product->get_children()) ) {
                ?>
                <script type="text/javascript">
                    jQuery('.variations_form').on('show_variation' , function() {
                        var variation_id = jQuery('.variations_form .variation_id').val();
                        if(variation_id) {
                            printcart_get_integration_id(variation_id);
                        }
                    });
                    function printcart_trigger_button_design( disabled = false ) {
                        if(disabled) {
                            jQuery('#pcdesigntool-design-btn').prop('disabled', true);
                            jQuery('#pcdesigntool-design-btn').addClass('buttonload');
                            jQuery('#pcdesigntool-design-btn').html('<i class="fa fa-spinner fa-spin"></i>Loading');
                        } else {
                            jQuery('#pcdesigntool-design-btn').prop('disabled', false);
                            jQuery('#pcdesigntool-design-btn').removeClass('buttonload');
                            jQuery('#pcdesigntool-design-btn').html('Start Design');
                        }
                    }
                    function printcart_get_integration_id(variation_id) {
                        jQuery.ajax({
                            type : "post",
                            dataType : "json",
                            url : '<?php echo admin_url('admin-ajax.php');?>',
                            data : {
                                action: "printcart_get_product_integration_by_variation",
                                variation_id : variation_id
                            },
                            context: this,
                            beforeSend: function(){
                                printcart_trigger_button_design(true);
                            },
                            success: function(response) {
                                if(response.success && response.data) {
                                    jQuery('#printcart-design-tool-sdk-wrap').append(response.data);
                                    printcart_trigger_button_design();
                                }
                                else {
                                    jQuery('#printcart-design-tool-sdk').remove();
                                    jQuery('#pcdesigntool-design-btn').remove( );
                                }
                            }
                        })
                    }
                </script>
                <?php
            }
        }
        public function printcart_add_design_box() {
            add_meta_box( 'printcart_order_design', esc_html__( 'Printcart Customer Design', 'web-to-print-online-designer' ), array( $this, 'printcart_order_design' ), 'shop_order', 'side', 'default' );
        }
        public function printcart_order_design($post) {
            $order_id       = $post->ID;
            $order          = wc_get_order($order_id);
            foreach( $order->get_items() as $order_item_id => $order_item ){
                $has_design     = false;
                echo '<p><b>Product: </b>'.$order_item->get_name().'</p>';
                if( wc_get_order_item_meta($order_item_id, '_printcart_designs') ){
                    $printcart_designs = unserialize(wc_get_order_item_meta($order_item_id, '_printcart_designs'));
                    if( count($printcart_designs) > 0 ) {
                        echo '<div class="printcart_container_item_designs">';
                        foreach($printcart_designs as $printcart_design) {
                            $has_design = true;
                            echo '<a class="button button-small button-secondary" title="View design" style="margin: 0 4px 4px 0;" href="'.$this->designtool_url.'/?api_key='.$this->printcart_get_access_token().'&design_id='.$printcart_design['id'].'&task=edit"><img style="max-width: 60px; max-height: 50px" src="'.$printcart_design['preview'].'"></a>';
                        }
                        echo '</div>';
                    }                  
                }
                if(!$has_design) echo '<p>No design in this order</p>';
            }
        }
        public function printcart_create_project($order_id, $posted_data, $order) {
            if($order) {
                $name = 'Order '.$order_id.' details';
                $note = '';
                $design_ids = array();
                foreach( $order->get_items() as $order_item_id => $order_item ){
                    if( wc_get_order_item_meta($order_item_id, '_printcart_designs') ){
                        $printcart_designs = unserialize(wc_get_order_item_meta($order_item_id, '_printcart_designs'));
                        if( count($printcart_designs) > 0 ) {
                            foreach($printcart_designs as $printcart_design) {
                                $design_ids[] = $printcart_design['id'];
                            }
                        }                  
                    }
                }
                if(count($design_ids) > 0) {
                    $project = array(
                        'name' => 'Order #'.$order_id.' details',
                        'note' => '',
                        'design_ids' => $design_ids,
                    );
                    $config = array(
                        'Username' => $this->username,
                        'Password' => $this->password
                    );
                    $printcart = new PHPPrintcart\PrintcartSDK($config);
                    $printcart->Project()->post($project);
                }
            }
        }
        public function printcart_add_preview_designs( $title, $cart_item, $cart_item_key ) {
            if( isset($cart_item['printcart_options']) && isset($cart_item['printcart_options']['designs'])) {
                $html = '<div><b>Preview designs</b></div><table><tbody><tr>';
                foreach($cart_item['printcart_options']['designs'] as $design ) {
                    $html .= '<td style="padding: 0"><div style="border: 1px solid #ddd;margin: 0 5px 5px 0;display: inline-block;text-align: center; vertical-align: top; background: #ddd; height: 100px; width: 100px"><img src="'.$design['preview'].'"></td>';
                }
                $html .= '</tr></tbody></table>';
                $title .= $html;
            }
            return $title;
        }
        public function printcart_get_product_integration_by_variation() {
            $variation_id = isset($_POST['variation_id']) ? $_POST['variation_id'] : '';
            $result = '';
            if($variation_id) {
                $integration = $this->printcart_get_product_integration($variation_id);
                if($integration['id'] && $integration['enable_design']) {
                    $product_id = $integration['id'];
                    $enable_design = $integration['enable_design'];
                    $result = '<script type="text/javascript" async="" id="printcart-design-tool-sdk" data-unauthtoken="'.$this->printcart_get_access_token().'" data-productid="'.$product_id.'" src="'.$this->designtool_url.'/main.js"></script>';
                }
            }
            wp_send_json_success($result);
            die();
        }
        public function printcart_add_hidden_order_items( $order_items ) {
            $order_items[] = '_printcart_designs';
            // and so on...
            return $order_items;
        }
    }
}

$printcart = PRINTCARTDESIGN::instance();
$printcart->init();