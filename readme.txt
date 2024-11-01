===WooCommerce Transmit SMS ===
Contributors: Transmit SMS Team
Donate link: 
Plugin URI:https://wordpress.org/plugins/woocommerce-transmit-sms/
Tags:  SMS, Notifications, Order Confirmations, Delivery Notifications, Text Message Notifications, Text Message Alerts, WooCommerce 
Requires at least: 3.5
Stable tag: 2.6
Tested :3.8
Tested up to: 3.9.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Send SMS updates to customers when their order status is updated and receive an SMS message when a customer places a new order

== Installation ==

1. Upload the 'woocommerce-transmit-sms' directory to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Klik menu Woocomerce > settings > integration (tab)> Transmit SMS Notifications.
3. Enter your Burst SMS API key and secret in the Settings tab.
4. Set your options for 'woocommerce-transmit-sms'

= What is a Burst SMS API key? = 

To send SMS you will need to sign up for a BURST SMS account
and purchase some SMS credit. When you sign up you'll be given an API key.

= What format should the mobile number be in? =

All mobile numbers Format would accepted, you can entered with in international format or local format, but remember to choose right country.

== Frequently asked questions ==

= A question that someone might have =

An answer to that question.
== Screenshots ==
1. Woocommerce Transmit Sms backend 1.
2. Woocommerce Transmit Sms SMS backend 2.
3. Woocommerce Transmit Sms SMS frontend.


== Changelog ==

= 1.0 =
 * Basic code development
= 1.1 =
 * Changing code who calling woowcomerce meta (not avalible on new version woocomerce)
= 1.2 =
 * Removing calling jquery from external source
 * Changing and modify some code to fit with wordpress.org
= 1.3 =
 * Adding function to detect changing status order automatically by other plugin
= 1.4 =
 * Decoding message
= 1.5 =
 * Decode symbol &#36; cannor decode by PHP usual function
= 1.6 =
 * Adding function to detect order statuses if 2 function cannot detect that before
 * Adding fuction sending email  to developer, if order statuses cannot detect and rendering
= 1.7 =
 * Fixing bug for data option not  displaying after clicking save update button
 * Fixing other small bug on front end 
= 1.8 =
 * Adding short code for order product data
= 1.9 =
 * Remove + mark on mobile number format
 * Testing with new wordpress version 4.5.1 and new woocomerce 2.5.5
= 2.0 =
 * Ignore error when creating session
 * Fixing calling function get_items error has non object
= 2.1 =
 * Change label on admin option.
 * Change label on alert message
= 2.2 =
 * Adding delay to prevent maximun API calling per second 
= 2.3 =
 * Adding shortcode order_note
= 2.4 =
 * Adding cookies to prevent duplicate sms created
= 2.5 =
 * Changing order_note shortcode only sending note from admin
 * Filter html tags from order_note shortcode
= 2.6 =
 * Fixing bug order_note not takinng

== Upgrade notice ==
Latest stable version is 2.6, please upgrade to version 2.6