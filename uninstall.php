<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    $option_name = 'WBSmsSettings';
    delete_option( $option_name );
 
?>