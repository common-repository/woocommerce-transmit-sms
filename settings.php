<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ||
in_array( 'woocommerce-master/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once WB_PLUGIN_DIR . '/classTransmitWoocommerce.php'; 
    $GLOBALS['WCB'] = new WC_BurstSMS();
}else{
   // echo "wowcommerce not active yet !!!";
}
register_uninstall_hook(WB_PLUGIN_DIR . '/uninstall.php', 'delete_plugin');
?>
