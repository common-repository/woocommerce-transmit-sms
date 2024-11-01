<?php
/**
 * Integration Demo Integration.
 *
 * @package  WC_Integration_Demo_Integration
 * @category Integration
 * @author   WooThemes
 */

if ( ! class_exists( 'TranmsitsSMS_Integration' ) ) :

class TranmsitsSMS_Integration extends WC_Integration {

	/**
	 * Init and hook in the integration.
	 */
    	public function __construct() {
            global $wpOption;     
    		global $woocommerce;
            $wp_session['test'] = 'halo';
            $this->id                 = 'integration-transmitsms';
    		$this->method_title       = __( 'Transmit SMS Notifications.', 'transmitSMS-integration' );
    	   //	$this->method_description = __( 'Transmit SMS Notifications.', 'transmitsSMS-integration' );
            add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ),1);
           	$this->init_settings();
          	// Define user set variables.
     		$this->api_key = $this->get_option( 'api_key' );
            $this->debug = $this->get_option( 'debug' );
            // Actions.
            add_action( 'admin_init', array(&$this,'WBjQueryUi' ),2);
            add_action('admin_footer', array(&$this,'WB_admin_footer_function'),3);
        	// Filters.
    		add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );
            $this->init_form_fields();
           
	}
    
    public function sentErrortoAdmin($errorMsg){
        $adminEmail = 'adi@burstsms.com';
        $siteUrl = get_site_url();
        $subject = 'Sending error info woocomerce plugin';
        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        // Additional headers
        $headers .= 'To: adi '.$adminEmail . "\r\n";
        $msgText = 'Hi Adi <br> '.$errorMsg;
        // Mail it
        @mail($to, $subject, $msgText, $headers);
        return;
    }
    public function process_admin_options(){
        global $wpOption;
          global $wpdb;    
        if(!$this->validate_phone_number('WB_adminNumber')){
            $this->display_errors();
        }elseif(!$this->validateAPIKey($_POST[$this->plugin_id . $this->id . '_'.'WB_apikey'],$_POST[$this->plugin_id . $this->id . '_'.'WB_apisecret'])){
            $this->display_errors();
        }else {
            $data = array();
            foreach($_POST as $key=>$val){
                $newKey = str_replace($this->plugin_id . $this->id . '_','',$key);
                $data[$newKey] = $val;
            }
            delete_option($wpOption);
            $statuses = (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) );
             foreach($statuses as $key => $val){
                if(!isset($val->term_id)){
                    $query = "SELECT * FROM wp_terms wt INNER JOIN wp_term_taxonomy wtt ON wtt.term_id = wt.term_id 
                    WHERE wtt.taxonomy = 'shop_order_status' order by wtt.term_taxonomy_id";
                    $statuses = $wpdb->get_results( $query);
                    break;
                }
            
            }          
            if(sizeof($statuses) === 0){
                 $newStatuses = array(); 
                 $statuses = wc_get_order_statuses();
                 $i=0;
                 foreach($statuses as $keyS =>$valS){
                    if($valS === 'Pending Payment')$valS = 'pending';
                    $valS = str_replace(' ','-',$valS);
                    $newStatuses[$i]['slug']=strtolower($valS);
                    $i++;
                  }
                  $statuses = $newStatuses;
                   
            }
            $this->WBSMSC_handleSubmitIntegration($data,$statuses);
            //$this->display_success('Settings has been saved');
            $messageCustomAdmin = preg_replace('~[\r\n]+~', '', $data['WB_receivedCustom']);
            $messageCustomAdmin = trim($messageCustomAdmin);
           // $messageCustomAdmin = preg_replace('~[\n]+~', '', $data['WB_receivedCustom']);
           //$messageCustomAdmin = preg_replace('~[\r]+~', '', $data['WB_receivedCustom']);
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery('#woocommerce_integration-transmitsms_WB_apikey').val('<?= trim($data['WB_apikey']) ?>');
                    jQuery('#woocommerce_integration-transmitsms_WB_apisecret').val('<?= trim($data['WB_apisecret']) ?>');
                    jQuery('#woocommerce_integration-transmitsms_WB_adminNumber').val('<?= trim($data['WB_adminNumber']) ?>');
                    jQuery('#woocommerce_integration-transmitsms_WB_receivedCustom').html("<?= $messageCustomAdmin ?>");
                   
                    <?PHP
                    if((int)@$data['WB_enaReceivedCustom'] === 1){
                            ?>
                               jQuery('#woocommerce_integration-transmitsms_WB_enaReceivedCustom').prop('checked',true);
                            <?PHP
                        }else {
                         ?>
                               jQuery('#woocommerce_integration-transmitsms_WB_enaReceivedCustom').prop('checked',false);
                            <?PHP
                            }
                        if((int)@$data['addToPCategory'] === 1){
                            ?>
                               jQuery('#woocommerce_integration-transmitsms_addToPCategory').prop('checked',true);
                            <?PHP
                        }else {
                         ?>
                               jQuery('#woocommerce_integration-transmitsms_addToPCategory').prop('checked',false);
                            <?PHP
                            }
                         if((int)@$data['addToCustomerL'] === 1){
                            ?>
                               jQuery('#woocommerce_integration-transmitsms_addToCustomerL').prop('checked',true);
                            <?PHP
                        }else  { ?>
                               jQuery('#woocommerce_integration-transmitsms_addToCustomerL').prop('checked',false);
                            <?PHP
                        }
                        if((int)@$data['WB_toBilling'] === 1){
                            ?>
                               jQuery('#woocommerce_integration-transmitsms_WB_toBilling').prop('checked',true);
                            <?PHP
                        }else {  ?>
                               jQuery('#woocommerce_integration-transmitsms_WB_toBilling').prop('checked',false);
                            <?PHP
                        }
                         if((int)@$data['WB_toShipping'] === 1){
                            ?>
                               jQuery('#woocommerce_integration-transmitsms_WB_toShipping').prop('checked',true);
                            <?PHP
                        }else {  ?>
                               jQuery('#woocommerce_integration-transmitsms_WB_toShipping').prop('checked',false);
                            <?PHP
                        }
                          //add order status
                        foreach($statuses as $ks => $orderStatus){
                            if(is_array($orderStatus)){
                                $slugName =  $orderStatus['slug'];  
                            }else {
                                $slugName =  $orderStatus->slug;
                            }
                            if($slugName == 'on-hold') $slugName = 'onhold';
                            $enaNAme = 'WB_ena'.ucfirst($slugName).'Custom';
                            $textAreaName = 'burstSms'.$slugName.'Custom';
                            $messageCustomCus = preg_replace('~[\r\n]+~', '', $data[$textAreaName]);
                            $messageCustomCus = trim($messageCustomCus);
                            if((int)@$data[$enaNAme] === 1){
                                ?>
                                    jQuery('#woocommerce_integration-transmitsms_<?= $enaNAme ?>').prop('checked',true);
                                    jQuery('#woocommerce_integration-transmitsms_<?= $textAreaName ?>').html("<?= addslashes($messageCustomCus);?>");
                                        jQuery('#woocommerce_integration-transmitsms_<?= $textAreaName ?>').parent().parent().parent().fadeIn('fast');
                               
                                <?PHP
                            }else {
                                ?>
                                    jQuery('#woocommerce_integration-transmitsms_<?= $enaNAme ?>').prop('checked',false);
                                    jQuery('#woocommerce_integration-transmitsms_<?= $textAreaName ?>').parent().parent().parent().fadeOut('fast');
                                 <?PHP
                            }
                        }
                    
                    ?>
                    
                   //alert('here');
                    jQuery('#message').removeClass('fade');

                })
            </script>
            <?PHP
           // add_action('admin_notices', array($this,'my_admin_notice'));
            /*echo '<div class="alert alert-success">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>
                    <strong>Success!</strong> Settings has been saved.
                    </div>';
                    */
        }
        
    }
    
    public function   WBSMSC_handleSubmitIntegration($data,$statuses){
        global $wpOption;
        global $wpdb;    
        
        $apikey = base64_encode(trim($data['WB_apikey']));
        $apisecret = base64_encode(trim($data['WB_apisecret']));
        $recivernumber = base64_encode(trim($data['WB_adminNumber']));
        $ownerCostum = empty($data['WB_ownerCostum'])?'':$data['WB_ownerCostum'];
        $receivedCustom = trim($data['WB_receivedCustom']);
        $arrSEtting = array('apikey'=>$apikey,'apisecret'=>$apisecret,'reciver_number' =>$recivernumber,
                    'ownerCostum'=>$ownerCostum,'receivedCustom'=> $receivedCustom,
                        'enaReceivedCustom' => empty($data['WB_enaReceivedCustom'])?'':$data['WB_enaReceivedCustom'], 
                       'sendToBilling' =>empty($data['WB_toBilling'])?'':$data['WB_toBilling'], 
                       'sendToShipping' =>empty($data['WB_toShipping'])?'':$data['WB_toShipping'],
                       'addToCustomerL' =>empty($data['addToCustomerL'])?'':$data['addToCustomerL'],
                       'addToPCategory' =>empty($data['addToPCategory'])?'':$data['addToPCategory']
                       );
        //add order status
        foreach($statuses as $ks => $orderStatus){
            if(is_array($orderStatus)){
                $slugName =  $orderStatus['slug'];  
            }else {
                $slugName =  $orderStatus->slug;
            }
            if($slugName == 'on-hold') $slugName = 'onhold';
            $arrSEtting[$slugName.'Custom'] =   empty($data['burstSms'.$slugName.'Custom'])?'':trim($data['burstSms'.$slugName.'Custom']);
            $arrSEtting['ena'.ucfirst($slugName).'Custom'] =  empty($data['WB_ena'.ucfirst($slugName).'Custom'])?'':$data['WB_ena'.ucfirst($slugName).'Custom'];
            
        }
       //delete_option($wpOption);
       update_option($wpOption, base64_encode(serialize($arrSEtting)));
       //$_SESSION[$wpOption] = base64_encode(serialize($arrSEtting));
        return true;
    }
    public function WB_admin_footer_function() {
    	 wp_deregister_style('jquery-ui-style-css');
        wp_register_script('bootstrap-js',  WB_PLUGIN_URL . '/assets/bootstrap-3/js/bootstrap.min.js', false, null);
        wp_enqueue_script('bootstrap-js');
    }
    public function WBjQueryUi() {
      //  wp_enqueue_style( 'css-jquery-ui', WB_PLUGIN_URL . '/jquery-ui/css/ui-lightness/jquery-ui-1.10.4.custom.css' );
        if(isset($_GET['tab']) && $_GET['tab'] == 'integration' ){
            //&& isset($_GET['section']) && $_GET['section']=='integration-transmitsms'){
            //load css only on self plugin page
            wp_enqueue_style( 'bootsrapCSS', WB_PLUGIN_URL . '/assets/bootstrap-3/css/bootstrap.css' );
            wp_enqueue_style( 'baseCSS', WB_PLUGIN_URL . '/admin/assets/style.css' );
            wp_register_script('adminJS',  WB_PLUGIN_URL . '/admin/assets/admin.js', false, null);
            wp_enqueue_script('adminJS');
        }
       // wp_register_script('jquery-ui',  WB_PLUGIN_URL . '/jquery-ui/jquery-ui-1.10.4.custom.js', false, null);
       // wp_enqueue_script('jquery-ui');
    }

	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
        global $wpdb;
        global $wpOption;     
        if(isset($_SESSION[$wpOption])) {
            $WBSms = unserialize(base64_decode($_SESSION[$wpOption]));
            unset($_SESSION[$wpOption]);
        }else {
            $WBSms = unserialize(base64_decode(get_option($wpOption)));   
        }
        $burstSmsApiKey  = empty($WBSms['apikey'])?'':base64_decode($WBSms['apikey']);
        $burstSmsApiSecret  = empty($WBSms['apisecret'])?'':base64_decode($WBSms['apisecret']);
        $burstSmsAdminNumber = empty($WBSms['reciver_number'])?'':base64_decode($WBSms['reciver_number']);
        //take value from cookies if still empty
        /*
        if(empty($burstSmsApiKey)) $burstSmsApiKey = base64_decode($_COOKIE['woocomerceTransmitSmsApiKey']);
        if(empty($burstSmsApiSecret)) $burstSmsApiSecret =base64_decode($_COOKIE['woocomerceTransmitSmsSecret']);
        if(empty($burstSmsAdminNumber)) $burstSmsAdminNumber =base64_decode($_COOKIE['woocomerceTransmitSmsAdminNumber']);
          */     
        
        empty($WBSms['receivedCustom'])?$burstSmsreceivedCustom=WB_orderRecivedMsg:$burstSmsreceivedCustom=trim($WBSms['receivedCustom']);
        $statuses = (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) );
        foreach($statuses as $key => $val){
            if(!isset($val->term_id)){
                $query = "SELECT * FROM wp_terms wt INNER JOIN wp_term_taxonomy wtt ON wtt.term_id = wt.term_id 
                WHERE wtt.taxonomy = 'shop_order_status' order by wtt.term_taxonomy_id";
                $statuses = $wpdb->get_results( $query);
                break;
            }
        }      
        if(sizeof($statuses) === 0){
             $newStatuses = array(); 
             $statuses = wc_get_order_statuses();
             $i=0;
             foreach($statuses as $keyS =>$valS){
                if($valS === 'Pending Payment')$valS = 'pending';
                $valS = str_replace(' ','-',$valS);
                $newStatuses[$i]['slug']=strtolower($valS);
                $i++;
              }
              $statuses = $newStatuses;
               
        }
        
        //default value
         foreach($statuses as $key => $os){
           if(is_object($os) || is_array($os)){
                if(is_array($os))  $sulgName = $os['slug'];
                if(is_object($os))  $sulgName = $os->slug;
               
                if($sulgName == 'on-hold')  $sulgName = 'onhold';
                $defvar = 'burstSms'.$sulgName.'Custom';
                $default = '';
                switch($sulgName){
                    case 'onhold':
                        $default = WB_orderOnholdMsg; break;
                    case 'processing':
                        $default = WB_orderProccessingMsg; break;
                    case 'completed':
                        $default = WB_orderCompletedMsg; break;
                    case 'pending':
                        $default = WB_orderPendingMsg; break;
                    case 'failed':
                        $default = WB_orderFailedMsg; break;
                    case 'refunded':
                        $default = WB_orderRefundedMsg; break;
                    case 'cancelled':
                        $default = WB_orderCancelledMsg; break;
                }
                $$defvar = '';
                 empty($WBSms[$sulgName.'Custom'])?$$defvar= $default:$$defvar=trim($WBSms[$sulgName.'Custom']);
                 
           }else {
                $erMessage = 'System Cannot render statuses, status not object <br>'.print_r($statuses,true);
                $this->sentErrortoAdmin($erMessage);
                $this->display_errors('Sorry we cann\'t find woowcomerce order statuses on your site.');
                
           }
        }
        //$erMessage = 'just sending status <br>'.print_r($statuses,true);
        //$this->sentErrortoAdmin($erMessage);
        
        
        $arrCutomOrder = array();
        foreach($statuses as $ks => $orderStatus){
            if(is_array($orderStatus))  $sulgName = $orderStatus['slug'];
            if(is_object($orderStatus))  $sulgName = $orderStatus->slug;
             if($sulgName == 'on-hold')  $sulgName = 'onhold';
            $defvar = 'burstSms'.$sulgName.'Custom';
            $customAttr = array();
            $customAttr['onclick'] ="textAreaToogle(this,'woocommerce_integration-transmitsms_".$defvar."')";
            $textAreacss = '';
            if(!empty($WBSms['ena'.ucfirst($sulgName).'Custom'])){
                $customAttr['checked'] ="checked";
            }else {
                $textAreacss = "display:none"; 
            }                             
            /*array(
            					'onclick' => "textAreaToogle(this,'woocommerce_integration-transmitsms_".$defvar."')",
                                'checked' =>'checked', */
            $this->form_fields['WB_ena'.ucfirst($sulgName).'Custom'] = array(
           	                
                            'label'             => __('Enable '.$sulgName.' notifications ', 'woocommerce' ),
            				'type' 				=> 'checkbox',
                            'class'             =>'Transmitparamdata',
                            'custom_attributes' => $customAttr,  
                           // 'default'           =>empty($WBSms['ena'.ucfirst($orderStatus->slug).'Custom'])?'no':'yes',                                    
            			);
            if($ks == 0){
                $this->form_fields['WB_ena'.ucfirst($sulgName).'Custom']['title'] = 'Notifications' ;
            }
            $this->form_fields[$defvar] = array(
				'title'             => __( 'Order '.$sulgName.' custom message : ', 'woocommerce' ),
                 'default'      => $$defvar,
				'type'              => 'textarea',
				'description'       => __( 'This message would be sent to the customer.  avalible shortcode : <kbd>[order_billing_country]</kbd>, 
                                     <kbd>[order_number]</kbd>, <kbd>[order_date]</kbd>, <kbd>[order_total]</kbd>, <kbd>[order_payment_method]</kbd>, <kbd>[order_billing_first_name]</kbd>, 
                                     <kbd>[order_billing_last_name]</kbd>, <kbd>[order_billing_phone]</kbd>, <kbd>[order_billing_email]</kbd>, 
                                     <kbd>[order_billing_company]</kbd>, <kbd>[order_billing_address_1]</kbd>, <kbd>[order_billing_address_2]</kbd>, 
                                    <kbd> [order_billing_city]</kbd>, <kbd>[order_billing_state]</kbd>, <kbd>[order_billing_postcode]</kbd>, 
                                     <kbd>[order_billing_country]</kbd>,  <kbd style="cursor:pointer">[order_product_name]</kbd>, <kbd style="cursor:pointer">[order_product_qty]</kbd>, <kbd> [order_note]</kbd>','woocommerce' ),
				'class'             =>'Transmitparamdata form-control',
                'css'               =>$textAreacss,
                     );
           
        } 
 
	  	$arrDefaultSetting = array(
			'WB_apikey' => array(
				'title'             => __( 'API Key', 'woocommerce' ),
				'type'              => 'text',
				'description'       => __( 'API Key can be found in the SETTINGS section of your Transmit SMS account.','woocommerce'),
				'desc_tip'          => true,
				'default'           =>$burstSmsApiKey,
                'css'               =>'width:400px',
                'class'             =>'form-control',
			),
            'WB_apisecret' => array(
				'title'             => __( 'Secret', 'woocommerce' ),
				'type'              => 'text',
				'description'       => __( 'API Secret is defined in the SETTINGS section of your Transmit SMS account.','woocommerce' ),
				'desc_tip'          => true,
				'default'           =>$burstSmsApiSecret,
                 'css'               =>'width:400px',
                 'class'             =>'form-control',
			),
            /*
            'WB_verify' => array(
				'title'             => __( 'Verify', 'woocommerce' ),
                'label'             => __( 'Verify', 'woocommerce' ),
				'type'              => 'button',
                'class'             =>'button button-primary button-large',
				'custom_attributes' => array(
					'onclick' => "renderlist('".str_replace( '%7E', '~', $_SERVER['REQUEST_URI'])."');",
                    'value' => "Verify",
                    	),
			),  */
           'addToPCategory' => array(
				'title' 			=> __( 'Add to list', 'woocommerce' ),
				'label' 			=> __( 'Create new list on Transmit SMS for each category.', 'woocommerce' ),
				'type' 				=> 'checkbox',
				'checkboxgroup'		=> 'start',
                'class'             =>'Transmitparamdata form-control',
   	            'description'       => __( 'Creates new list for category on first purchase.','woocommerce' ),
				'desc_tip'          => true,
                'default'           => 'yes',
  	         ),
			'addToCustomerL' => array(
				'label' 			=> __( 'Sync existing customers to "All Customers" List.', 'woocommerce' ),
				'type' 				=> 'checkbox',
				'checkboxgroup'		=> '',
                'class'             =>'Transmitparamdata form-control',
                 'description'       => __( 'Synchronize all existing customer to list \'All Customers\' in your Transmit SMS account.','woocommerce' ),
				'desc_tip'          => true,
                'default'           => 'yes',
				),
            //admin notification
              'WB_adminSideTitle' => array(
              	'title' 			=> __( 'label', 'woocommerce' ),
				'type' 				=> 'hidden',
                'default'           =>'',
                'class'             =>'Transmitparamdata',
                 'description'       => __( '<h3> Admin Notifications</h3>','woocommerce' ),
 	      ),
            'WB_enaReceivedCustom' => array(
				'label' 			=> __( 'Enable new order SMS admin notifications .', 'woocommerce' ),
				'type' 				=> 'checkbox',
                'default' 			=> 'yes', 
                'class'             =>'Transmitparamdata',
				'checkboxgroup'		=> '', 
			),
            
            
            'WB_adminNumber' => array(
				'title'             => __( 'Admin mobile number : ', 'woocommerce' ),
                'default'           => $burstSmsAdminNumber,
                'type'              => 'text',
                'class'             =>'Transmitparamdata form-control',
				'description'       => __( '<em style="font-size:12px">The mobile number you wish to receive messages on in international format eg. 614XXXXXXXX, 447XXXXXXXX (separated by commas for multiple number) .</em>','woocommerce' ),
			     'css'               =>'width:400px',
            	),
            
            'WB_receivedCustom' => array(
				'title'             => __( 'Order received custom message : ', 'woocommerce' ),
				'type'              => 'textarea',
                'default'           => $burstSmsreceivedCustom,
                'class'             =>'Transmitparamdata form-control',
				'description'       => __( 'This message would be sent to the admin.  avalible shortcode :<kbd>[order_billing_country]</kbd>,
                                      <kbd>[order_number]</kbd>, <kbd>[order_date]</kbd>, <kbd>[order_total]</kbd>, <kbd>[order_payment_method]</kbd>, <kbd>[order_billing_first_name]</kbd>, 
                                      <kbd>[order_billing_last_name]</kbd>, <kbd>[order_billing_phone]</kbd>, <kbd>[order_billing_email]</kbd>, <kbd>[order_billing_company]</kbd>, 
                                      <kbd>[order_billing_address_1]</kbd>, <kbd>[order_billing_address_2]</kbd>, <kbd>[order_billing_city]</kbd>, <kbd>[order_billing_state]</kbd>,
                                      <kbd>[order_billing_postcode]</kbd>, <kbd style="cursor:pointer">[order_billing_country]</kbd>, 
                                      <kbd style="cursor:pointer">[order_product_name]</kbd>, <kbd style="cursor:pointer">[order_product_qty]</kbd>, <kbd> order_note</kbd>','woocommerce' ),
                //'desc_tip'          => true,
				
			),
            
            'dialogTestSMS' => array(
				'title'             => __( 'Test sending SMS', 'woocommerce' ),
				'type'              => 'button',
                'class'             =>'Transmitparamdata btn btn-success',
                'custom_attributes' => array(
					'onclick' => " sendTestSMS();",
                    	),
                'description'       => __( '<img style="display:none" id="testsmsPreloader"  src="'.WB_PLUGIN_URL.'/images/loader.gif" />','woocommerce' ),
			),
            
             //customer notification------------------------------------------------------------------------
             'WB_customerSideTitle' => array(
              	'title' 			=> __( 'label', 'woocommerce' ),
				'type' 				=> 'hidden',
                'default'           =>'',
                'class'             =>'Transmitparamdata',
                 'description'       => __( '<h3> Customer Notifications</h3>','woocommerce' ),
 	      ),
             'WB_toBilling' => array(
				'title' 			=> __( 'Send to', 'woocommerce' ),
				'label' 			=> __( 'Send to billing number.', 'woocommerce' ),
				'type' 				=> 'checkbox',
                'class'             =>'Transmitparamdata',
                'default'           =>'yes',
                'custom_attributes' => array(
					'checked' => "",
                    	),
			 ),
			'WB_toShipping' => array(
				'label' 			=> __( 'Send to shipping number .', 'woocommerce' ),
				'type' 				=> 'checkbox',
                'class'             =>'Transmitparamdata',
				'checkboxgroup'		=> '',
            ),        );
            
        if(empty($burstSmsAdminNumber) || $arrDefaultSetting['WB_adminNumber']['default'] == 'undefined'){
            unset($arrDefaultSetting['WB_adminNumber']['default']);
        }
         $this->form_fields = array_merge($arrDefaultSetting,$this->form_fields);
	}

	/**
	 * Generate Button HTML.
	 */
	public function generate_button_html( $key, $data ) {
		$field    = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'class'             => 'button-secondary',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'description'       => '',
			'title'             => '',
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field ); ?>"></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>                
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<button class="<?php echo esc_attr( $data['class'] ); ?>" type="button" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo wp_kses_post( $data['title'] ); ?></button>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}
    
    public function generate_hidden_html( $key, $data ) {
       if($data['title'] == 'label'){
            $field    = $this->plugin_id . $this->id . '_' . $key;
    		$defaults = array(
    			'class'             => 'form-control',
    			'css'               => '',
    			'custom_attributes' => array(),
    			'desc_tip'          => false,
    			'title'             => '',
    		);
    
    		$data = wp_parse_args( $data, $defaults );
             
    		ob_start();
		?>
		<tr valign="top" style="<?= $data['css'] ?>">
			<th colspan="2" scope="row" class="titledesc">
				<?php echo wp_kses_post( $data['description'] ); ?>
			</th>
			
		</tr>
		<?php
		return ob_get_clean();
	}
 }
    
    
    public function generate_textarea_html( $key, $data ) {
		$field    = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'class'             => 'form-control',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'title'             => '',
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top" style="<?= $data['css'] ?>">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>                
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<textarea  class="<?php echo esc_attr( $data['class'] ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>"><?php echo esc_attr( $data['default'] ); ?>
                    </textarea>
                    <span style="font-size:12px">	<?php echo $this->get_description_html($data); ?></span>
                </fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}
    
	/**
	 * Santize our settings
	 * @see process_admin_options()
	 */
	public function sanitize_settings( $settings ) {
		// We're just going to make the api key all upper case characters since that's how our imaginary API works
        /*
		if ( isset( $settings ) &&
		     isset( $settings['api_key'] ) ) {
			$settings['api_key'] = strtoupper( $settings['api_key'] );
		}*/
		return $settings;
	}
    public function validate_phone_number($key) {
        // get the posted value
        $value = $_POST[ $this->plugin_id . $this->id . '_' . $key ];
         
        // check if the API key is longer than 20 characters. Our imaginary API doesn't create keys that large so something must be wrong. Throw an error which will prevent the user from saving.
        if ( empty( $value ) || substr($value,0,1) == '0') {
            $this->errors[] = 'Admin mobile number must in  international format eg. 614XXXXXXXX';
            return false;
        }
        return $value;
    }
    public function validateAPIKey($APIKey,$secret){
        require_once WB_PLUGIN_DIR."/APIClient2.php";
        $api=new transmitsmsAPI($APIKey,$secret);
        $offset=1;
        $limit=100;
        $result=$api->getLists($offset,$limit);
        if($result->error->code=='SUCCESS'){
            return true;
        }
        $this->errors[] = $result->error->description;
        return false;
    }
    
	public function display_errors() {

		// loop through each error and display it
		foreach ( $this->errors as $key => $value ) {
			?>
			<div class="error">
				<p><?php _e( $value, 'transmitsSMS-integration' ); ?></p>
			</div>
			<?php
		}
	}

}

endif;
