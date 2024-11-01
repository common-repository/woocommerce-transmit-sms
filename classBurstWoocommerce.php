<?PHP
class WC_BurstSMS {
    public function __construct() {
        // indicates we are running the admin
        add_action('woocommerce_checkout_update_order_meta', array($this,'custom_checkout_field_update_order_meta'));
         add_action('woocommerce_order_status_changed', array($this,'sentNotification'));
        if ( is_admin() ) {
          
            //require_once WB_PLUGIN_DIR . '/admin/admin.php';         
        }else{
            add_filter('woocommerce_before_checkout_form', array($this,'checkedSentNotification'));
            add_filter( 'woocommerce_checkout_fields' , array(&$this,'custom_override_checkout_fields'));
            add_action('woocommerce_thankyou', array($this,'sentNotificationforOrderRecive'));
          }
    }
    public function checkedSentNotification(){
         ob_start();
            ?>
           <script type="text/javascript">
            jQuery(document).ready(function(){
                var avalaibleCountry = ["AU", "US", "NZ", "SG","GB"]; 
                var billing_country = jQuery('#billing_country');
                var inArray = avalaibleCountry.indexOf(billing_country.val());
               
                if(inArray >= 0){
                    jQuery('#sendChangeStatusNotif_field').fadeIn('fast');
                    jQuery('#sendChangeStatusNotif').attr('checked','checked');
                }else {
                    jQuery('#sendChangeStatusNotif').removeAttr('checked');
                    jQuery('#sendChangeStatusNotif_field').fadeOut('fast');
                }
                billing_country.change(function(){
                    var inArray2 = avalaibleCountry.indexOf(billing_country.val());
                    if(inArray2 >= 0){
                        jQuery('#sendChangeStatusNotif_field').fadeIn('fast');
                        jQuery('#sendChangeStatusNotif').attr('checked','checked');
                    }else {
                        jQuery('#sendChangeStatusNotif').removeAttr('checked');
                        jQuery('#sendChangeStatusNotif_field').fadeOut('fast');
                    }
                })
            });
            </script>
         <?PHP
            $jsscript = ob_get_contents();
            ob_end_clean();
            echo $jsscript;
     }
    
     public function sentNotification($order_id){
        global $wpOption;
        global $woocommerce;
        $WBSms = unserialize(base64_decode(get_option($wpOption)));  
        if(empty($order_id) || (int)$order_id === 0){
            $order_id = isset($_GET['order_id']) && (int) $_GET['order_id'] ? (int) $_GET['order_id'] : '';
            if(isset($_POST['post_ID'])) {
                 $order_id = (int)$_POST['post_ID']; 
            }
        }
        $order = new WC_Order( $order_id );
        $items = $order->get_items();
        $product_name = '';
        $product_qty = '';
        foreach ($items as $item ) {
            $product_name .= $item['name'].', ';
            $product_qty .= $item['qty'].', ';
        }
        $product_name = substr(trim($product_name),0,-1);
        
        $product_qty = substr(trim($product_qty),0,-1);
        $shippingPhoneNumber = get_post_meta( $order_id, 'shipping_phone', true ) ;   
        $sendChangeStatusNotif = get_post_meta( $order_id, 'sendChangeStatusNotif', true ) ;  
        
        if((int)$sendChangeStatusNotif == 1 ){ 
            if($order->status == 'on-hold') $order->status = 'onhold';
            $this->sendSMS($order,$order->status.'Custom',  false,$product_name,$product_qty);
            if(isset($WBSms['sendToShipping']) && (int)$WBSms['sendToShipping'] > 0){
                if(!empty($shippingPhoneNumber)){
                    $order->billing_phone = $shippingPhoneNumber;
                    $order->billing_first_name = $order->shipping_first_name; 
                    $order->billing_last_name =$order->shipping_last_name;
                    $this->sendSMS($order,$order->status.'Custom', false,$product_name,$product_qty);
                }
            }
        }
    }
  
    public function sendSMS($order,$type,  $toAdmin = false,$product_name = null,$product_qty=null){ //type = Custom
        require_once WB_PLUGIN_DIR . '/APIClient2.php';
        global $wpOption;
        global $arrShortcode;
        $WBSms = unserialize(base64_decode(get_option($wpOption))); 
        $burstSmsApiKey  = base64_decode($WBSms['apikey']);
        $burstSmsApiSecret  = base64_decode($WBSms['apisecret']);
        $adminMobile = base64_decode($WBSms['reciver_number']);
        $arryReplaceShortcode= array($order->get_order_number(),date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ), strip_tags($order->get_formatted_order_total()),
                                    $order->payment_method_title,$order->billing_first_name,$order->billing_last_name,$order->billing_phone,$order->billing_email,$order->billing_company,$order->billing_address_1,$order->billing_address_2,
                                        $order->billing_city, $order->billing_state, $order->billing_postcode,$order->billing_country,
                                        $product_name,$product_qty);
        
		$textMessage = str_replace($arrShortcode,$arryReplaceShortcode,$WBSms[$type]);
        
        //data product 
        $textMessage = urldecode($textMessage);
		//skip char who cannot slip by url decode
		$textMessage = html_entity_decode($textMessage);
		$textMessage = str_replace('&#36;','&',$textMessage);
        $WBmsAPI = new transmitsmsAPI($burstSmsApiKey, $burstSmsApiSecret);
        if($toAdmin){
            $tonumber = explode(',',$adminMobile);
        }else{
            $tonumber = $order->billing_phone;
             //reformating number
            $formatTonumber = $WBmsAPI->formatNumber($tonumber,$order->billing_country);
            if($formatTonumber->error->code == 'SUCCESS') {
                $tonumber =  $formatTonumber->number->international;
            }                       
        }
        
        if((int)$WBSms['ena'.ucfirst($type)] == 1 ){ //sending sms if checkbox for current order type active
            if($toAdmin){
                foreach($tonumber as $key => $numberTo){
                    $result=$WBmsAPI->sendSms($textMessage, trim($numberTo));
                }
                if($result->error->code=='SUCCESS'){ 
                   //success
                }
            }else{
                $result=$WBmsAPI->sendSms($textMessage, $tonumber);
                if($result->error->code=='SUCCESS'){ 
                   // echo 'success';
                }else{
                    // fail
                }
              } 
         }
    }
  
    public function addToList($phoneNumber,$firstname, $lastName,$productListCat= array()){
        //get list option
        require_once WB_PLUGIN_DIR . '/APIClient2.php';
        global $wpOptionCustomerList;
        global $wpOptionProductList; 
        global $wpOption;
        global $customerListName;
        $WBSms =unserialize(base64_decode(get_option($wpOption))); 
        $burstSmsApiKey  = base64_decode($WBSms['apikey']);
        $burstSmsApiSecret  = base64_decode($WBSms['apisecret']);
         $apiConn = new transmitsmsAPI($burstSmsApiKey, $burstSmsApiSecret);
        
        $customFields=array('description'=> WB_addtoListDesc .' '.get_bloginfo('wpurl'));
        $customFieldsList=array('transmit sms woocommerce','client');
        //add to customer list
        if((int)$WBSms['addToCustomerL'] > 0){
            if($listID = get_option($wpOptionCustomerList)){
                $result=$apiConn->addToList($listID, $phoneNumber, $firstname,  $lastName, $customFields);
            }else{ 
                //create list
                $resultC=$apiConn->addList($customerListName,$customFieldsList);
                if(!empty($resultC->id)){
                    update_option($wpOptionCustomerList, $resultC->id);  
                    $result=$apiConn->addToList($resultC->id,  $phoneNumber, $firstname,$lastName, $customFields);
                }
            }
        }
        if((int)$WBSms['addToPCategory'] > 0){
            $arrListProduct = array();
            //check if option exist
            if(!get_option($wpOptionProductList)){
                $arrProductCategory = $this->getProductCategory();
                foreach($arrProductCategory as $key => $val){
                    $resultC=$apiConn->addList($val,$customFieldsList);
                    $productSlugName = strtolower(str_replace(' ','_',$val));
                    if(!empty($resultC->id)){
                        $arrListProduct[$productSlugName] = $resultC->id; 
                        $result=$apiConn->addToList($resultC->id,  $phoneNumber, $firstname,$lastName, $customFields);
                    }
                }
                update_option($wpOptionProductList,$arrListProduct);
            }else{
                $arrListProduct = get_option($wpOptionProductList);
                if(sizeof($productListCat) > 0){
                    foreach($productListCat  as $key =>$val){
                        
                        if(isset($arrListProduct[$key])) {
                             $result=$apiConn->addToList($arrListProduct[$key],  $phoneNumber, $firstname,$lastName, $customFields);
                        }else{
                             //create list
                            $resultC=$apiConn->addList(str_replace('_',' ',$key),$customFieldsList);
                            if(!empty($resultC->id)){
                                $arrListProduct[$key] = $resultC->id;
                                $result=$apiConn->addToList($resultC->id,  $phoneNumber, $firstname,$lastName, $customFields);
                            }
                        }    
                    }
                    update_option($wpOptionProductList,$arrListProduct); 
                }
                //check product category
            }
        }
      }
    public function sentNotificationforOrderRecive(){
        require_once WB_PLUGIN_DIR . '/APIClient2.php';
        global $wpOption;
        global $bsMsgResult;        
        $WBSms = unserialize(base64_decode(get_option($wpOption))); 
        $burstSmsApiKey  = base64_decode($WBSms['apikey']);
        $burstSmsApiSecret  = base64_decode($WBSms['apisecret']);        
        $order_id = '';
        $order_key = '';
        $order= '';
        $order_id  = apply_filters( 'woocommerce_thankyou_order_id', empty( $_GET['order-received'] ) ? '' : wc_clean( $_GET['order-received'] ) );
        $order_key = apply_filters( 'woocommerce_thankyou_order_key', empty( $_GET['key'] ) ? '' : wc_clean( $_GET['key'] ) );
         //handle for difirent type of permalink
        if(empty($order_id) || $order_id == 0){
             $arrUri = explode('/',$_SERVER['REQUEST_URI']);
            $keyOrderRecive =  array_search('order-received', $arrUri);
            $order_id = $arrUri[$keyOrderRecive + 1];
        }
        if ( $order_id > 0 ) {
            $order = new WC_Order( $order_id );
            if ( $order->order_key != $order_key )
    			unset( $order );
        }
        //prevent sending sms twice
        if( !session_id() )session_start();
        $shippingPhoneNumber =  get_post_meta( $order_id, 'shipping_phone', true );     
        if(!isset($_SESSION['BSMSorderReciveSentNotification'])){
           //add to list
            $items = $order->get_items(); 
            $product_name = '';
            $product_qty = '';
            foreach ($items as $item ) {
                $product_name .= $item['name'].', ';
                $product_qty .= $item['qty'].', ';
            }
            $product_name = substr(trim($product_name),0,-1);
            $product_qty = substr(trim($product_qty),0,-1);
            $this->sendSMS($order,'receivedCustom',true,$product_name,$product_qty);
            $arrCategoryTemp = array();
            $arrCategory= array();
            foreach ( $items as $item ) {
                $product_id = $item['product_id'];
                $productTS = new WC_Product($item['product_id']);
                $category =  strip_tags($productTS->get_categories(","));
                $arrCategoryTemp = explode(',',$category);
                foreach($arrCategoryTemp as $k =>$cat){
                    $cat = strtolower(str_replace(' ','_',$cat));
                    $arrCategory[$cat] = 1;        
                } 
            }
            
            $WBmsAPI = new transmitsmsAPI($burstSmsApiKey, $burstSmsApiSecret);
            //reformating
            $formatTonumber = $WBmsAPI->formatNumber($order->billing_phone,$order->billing_country); 
            if($formatTonumber->error->code == 'SUCCESS') {
                $order->billing_phone =  $formatTonumber->number->international;
                $this->addToList($order->billing_phone, $order->billing_first_name, $order->billing_last_name,$arrCategory);    
            }  
            $_SESSION['BSMSorderReciveSentNotification'] =1;
        }else {
            unset($_SESSION['BSMSorderReciveSentNotification']);
        }
        //add shipping number
        if(!empty($shippingPhoneNumber)){
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery('.customer_details').append('<dt>Shipping Telephone:</dt><dd><?=$shippingPhoneNumber?></dd>');
            })
            
        </script>
        <?PHP
        }
    }
    public function dispalyMsgOut() {
        global $bsMsgResult;
        ob_start();
        ?>
       <script type="text/javascript">
        jQuery(document).ready(function(){
                    jQuery('.entry-header').prepend('<?=$bsMsgResult ?>');
        });
        </script>
        <?PHP
        $jsscript = ob_get_contents();
        ob_end_clean();
        echo $jsscript;
    
    }
    public function my_enqueue() {
       wp_enqueue_style('my-script-slug', WB_PLUGIN_URL. '/style.css');
       wp_deregister_script('jquery');
       wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://code.jquery.com/jquery-latest.min.js", false, null);
       wp_enqueue_script('jquery');
    }
    public function sentNotificationforNote(){
        echo 'dont come here, i am Ghost';
        exit();
    }
  
    //add phone field on shipping
    public function custom_override_checkout_fields( $fields ) {
         $fields['shipping']['shipping_phone'] = array(
            'label'     => __('Mobile', 'woocommerce'),
            'placeholder'   => _x('Mobile', 'placeholder', 'woocommerce'),
            'required'  => false,
            'class'     => array('form-row-wide'),
            'clear'     => true
         );

         $fields['billing']['sendChangeStatusNotif'] = array(
            'type' => 'checkbox',
            'class'=> array('input-checkbox'),
            'id' =>'sendChangeStatusNotif',
            'checked'=>'checked',
            'label'     => __('Send me status updates by SMS', 'woocommerce'),
            'required'  => false,
          );
         
         $fields['billing']['billing_phone']['label'] = __('Mobile', 'woocommerce');
         $fields['billing']['billing_phone']['placeholder'] = _x('Mobile', 'placeholder', 'woocommerce');
        return $fields;
    }
    public function custom_checkout_field_update_order_meta( $order_id ) {
         if (isset($_POST['shipping_phone'])){
            update_post_meta( $order_id, 'shipping_phone',  sanitize_text_field($_POST['shipping_phone']));
        }else {
            update_post_meta( $order_id, 'shipping_phone', '');
        }
         if (isset($_POST['sendChangeStatusNotif'])){
            update_post_meta( $order_id, 'sendChangeStatusNotif',  (int)$_POST['sendChangeStatusNotif']);
        }else {
            update_post_meta( $order_id, 'sendChangeStatusNotif',  0);
        }
      }
    public function getProductCategory(){
          $taxonomy     = 'product_cat';
          $orderby      = 'name';  
          $show_count   = 0;      // 1 for yes, 0 for no
          $pad_counts   = 0;      // 1 for yes, 0 for no
          $hierarchical = 1;      // 1 for yes, 0 for no  
          $title        = '';  
          $empty        = 0;
          $args = array(
          'taxonomy'     => $taxonomy,
          'orderby'      => $orderby,
          'show_count'   => $show_count,
          'pad_counts'   => $pad_counts,
          'hierarchical' => $hierarchical,
          'title_li'     => $title,
          'hide_empty'   => $empty
        );
        
        $all_categories = get_categories( $args );
        //print_r($all_categories);
        $productcategory = array();
        foreach ($all_categories as $cat) {
            //print_r($cat);
            if($cat->category_parent == 0) {
               // $productcategory[]['id'] = $cat->term_id;
                $productcategory[] = $cat->name;
            }
        }
        return $productcategory;
    }
}