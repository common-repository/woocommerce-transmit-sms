<?php
/*
Plugin Name: WooCommerce Transmit SMS
Plugin URI: 
Description: Send SMS updates to customers when their order status is updated and receive an SMS message when a customer places a new order.
Version: 2.6
Author: WB SMS >> Transmit SMS
Author URI: 
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
define( 'WB_VERSION', '2.6' );
global $pagenow;
define( 'WB_REQUIRED_WP_VERSION', '3.5' );

if ( ! defined( 'WB_PLUGIN_BASENAME' ) )
	define( 'WB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WB_PLUGIN_NAME' ) )
	define( 'WB_PLUGIN_NAME', trim( dirname( WB_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'WB_PLUGIN_DIR' ) )
	define( 'WB_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'WB_PLUGIN_URL' ) )
	define( 'WB_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

if ( ! defined( 'WB_PLUGIN_MODULES_DIR' ) )
	define( 'WB_PLUGIN_MODULES_DIR', WB_PLUGIN_DIR . '/modules' );

if ( ! defined( 'WB_LOAD_JS' ) )
	define( 'WB_LOAD_JS', true );

if ( ! defined( 'WB_LOAD_CSS' ) )
	define( 'WB_LOAD_CSS', true );

if ( ! defined( 'WB_AUTOP' ) )
	define( 'WB_AUTOP', true );
    

if ( ! defined( 'WB_TEMPDIR' ) )
	define( 'WB_TEMPDIR', sys_get_temp_dir() );

if ( ! defined( 'WB_USE_PIPE' ) )
	define( 'WB_USE_PIPE', true );
if(!defined('WB_orderRecivedMsg'))
    define('WB_orderRecivedMsg','Order has been received No [order_number], Date: [order_date],Total: [order_total], PAYMENT METHOD: [order_payment_method], Name:[order_billing_first_name] [order_billing_last_name], phone: [order_billing_phone], email: [order_billing_email]'. "\r\n".'Thank you');
if(!defined('WB_orderProccessingMsg'))
    define('WB_orderProccessingMsg','Hi [order_billing_first_name], Your order transaction [order_number] on [order_date]  is currently processing. thank you');    
if(!defined('WB_orderCompletedMsg'))
    define('WB_orderCompletedMsg','Hi [order_billing_first_name], Your order transaction [order_number] on [order_date] is currently completed, thank you');   
if(!defined('WB_orderPendingMsg'))
    define('WB_orderPendingMsg','Hi [order_billing_first_name], Your order transaction [order_number] on [order_date] is currently pending, thank you');   
if(!defined('WB_orderFailedMsg'))
    define('WB_orderFailedMsg','Hi [order_billing_first_name], Your order transaction [order_number] on [order_date] is currently failed, thank you');   
if(!defined('WB_orderOnholdMsg'))
    define('WB_orderOnholdMsg','Hi [order_billing_first_name], Your order transaction [order_number] on [order_date] is currently on-hold, thank you');   
if(!defined('WB_orderRefundedMsg'))
    define('WB_orderRefundedMsg','Hi [order_billing_first_name], Your order transaction [order_number] on [order_date] is currently refunded, thank you');   
if(!defined('WB_orderCancelledMsg'))
    define('WB_orderCancelledMsg','Hi [order_billing_first_name], Your order transaction [order_number] on [order_date] is currently cancelled, thank you');   
if(!defined('WB_notesCustom'))
    define('WB_notesCustom','note for customer about this order');
if(!defined('WB_failMsg'))
    define('WB_failMsg',"Oops! Sorry, looks like we have run into some problems. Please try again later, or contact via email");
if(!defined('WB_successVerify'))
    define('WB_successVerify',"Your key has been verified successfully");
if(!defined('WB_failVerify'))
    define('WB_failVerify',"Sorry..api key and secret you entered  still invalid");
if(!defined('WB_TemplateMsg'))
    define('WB_TemplateMsg','Website Enquiry From: [NAME]'."\n".'[MESSAGE]'); 
if(!defined('WB_addtoListDesc'))
    define('WB_addtoListDesc','Added from Transmit SMS woowcommerce plugin');     

$wpOption = 'WBSmsSettings'; 
$wpOptionCustomerList = 'WBSmsListCustomer';
$wpOptionProductList = 'WBSmsListProduct'; 
$customerListName = 'Customer List';
$arrShortcode = array('[order_number]','[order_date]','[order_total]','[order_payment_method]','[order_billing_first_name]',
                    '[order_billing_last_name]','[order_billing_phone]','[order_billing_email]','[order_billing_company]',
                        '[order_billing_address_1]','[order_billing_address_2]',
                    '[order_billing_city]','[order_billing_state]','[order_billing_postcode]','[order_billing_country]',
                    '[order_product_name]','[order_product_qty]','[order_note]');
$arrPhoneCodeCountry = array(61=>'AU', 44=>'UK', 1=>'US', 64=>'NZ', 65 => 'SG');    
$defPhoneCodeCountry = 61;

register_deactivation_hook(__FILE__, '_WBplugin_deactivation');
function _WBplugin_deactivation(){
    global $wpOption;
    global $wpOptionCustomerList;
    global $wpOptionProductList;
    delete_option($wpOption); 
   delete_option($wpOptionCustomerList); 
   delete_option($wpOptionProductList); 
} 
function WooComerceTransmitsSMS_add_integration( $integrations ) {
	global $woocommerce;
	if ( is_object( $woocommerce ) && version_compare( $woocommerce->version, '2.1-beta-1', '>=' ) ) {
		//include_once( 'includes/class-wc-google-analytics-integration.php' );
        setcookie('WB_PLUGIN_URL',WB_PLUGIN_URL);
        require_once WB_PLUGIN_DIR.'/admin/admin-controller.php';
		include_once WB_PLUGIN_DIR.'/admin/admin_integration.php';
		$integrations[] = 'TranmsitsSMS_Integration';
	}

	return $integrations;
}

add_filter( 'woocommerce_integrations', 'WooComerceTransmitsSMS_add_integration', 10 );


if ( 'plugins.php' === $pagenow )
{
    // Better update message
    $file   = basename( __FILE__ );
    $folder = basename( dirname( __FILE__ ) );
    $hook = "in_plugin_update_message-{$folder}/{$file}";
    add_action( $hook, 'your_update_message_wbc', 20, 2 );
}

function your_update_message_wbc( $plugin_data, $r )
{
    // readme contents
    $data       = file_get_contents( 'https://plugins.svn.wordpress.org/woocommerce-transmit-sms/trunk/readme.txt' );
    $arrUpgardeNotice = explode('Upgrade notice',$data);
    $upgradeNotice= trim($arrUpgardeNotice[1]);
    $upgradeNotice = substr($upgradeNotice,2);
    if($separateString =  strpos($upgradeNotice,'==') !== false){
        if($separateString > 5){
            $upgradeNotice =  substr($upgradeNotice,0,$separateString);
        }
    }
	$upgradeNotice = str_replace('=','',$upgradeNotice);
    $output = '<div style="margin-top:10px" class="alert alert-info"><i class="fa fa-info-circle fa-lg"></i> '.$upgradeNotice.'</div>';
    return print $output;
}


require_once WB_PLUGIN_DIR . '/settings.php';

?>
