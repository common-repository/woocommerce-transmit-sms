<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once WB_PLUGIN_DIR . '/admin/admin-functions.php';
require_once WB_PLUGIN_DIR . '/admin/admin-controller.php';
add_action( 'admin_menu', 'WB_admin_menu');