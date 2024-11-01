<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
add_action( 'admin_init', 'WBjQueryUi' );
add_action('admin_footer', 'WB_admin_footer_function');
function WB_admin_footer_function() {
	// wp_deregister_style('jquery-ui-style-css');
}
function WBjQueryUi() {
    //wp_enqueue_style( 'css-jquery-ui', WB_PLUGIN_URL . '/jquery-ui/css/ui-lightness/jquery-ui-1.10.4.custom.css' );
    if(isset($_GET['tab']) && $_GET['tab'] == 'settings_tab_demo'){
        //load css only on self plugin page
        wp_enqueue_style( 'baseCSS', WB_PLUGIN_URL . '/assets/style.css' );
    }
   // wp_register_script('jquery-ui',  WB_PLUGIN_URL . '/jquery-ui/jquery-ui-1.10.4.custom.js', false, null);
  //  wp_enqueue_script('jquery-ui');
}
function WB_admin_menu() {
    //add_submenu_page('woocommerce', __('Transmit SMS Notifications', 'burst_sms'),  __('Transmit SMS Notifications', 'burst_sms') , 'manage_woocommerce', 'WBSMSC_options', 'WBSMSC_options');
    add_filter( 'woocommerce_settings_tabs_array','WB_add_settings_tab', 50 );
    add_action( 'woocommerce_settings_tabs_settings_tab_demo','WB_settings_tab');
    add_action( 'woocommerce_update_options_settings_tab_demo', 'WB_update_settings');
}
function WB_add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_tab_demo'] = __( 'Transmit SMS Notification', 'woocommerce-settings-tab-demo' );
        return $settings_tabs;
    }
function WB_settings_tab() {
        woocommerce_admin_fields(WB_get_settings() );
    }
function WB_update_settings() {
        woocommerce_update_options(WB_get_settings() );
    }
function WB_get_settings() {
        $settings = array();
        $settings[] = WBSMSC_options();
        return apply_filters( 'wc_settings_tab_demo_settings', $settings );
        
    }
    
//---------------------------------------------------------    

function WBSMSC_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
        echo WBSMSC_settingForm();
}
function WBSMSC_settingForm(){
    global $arrPhoneCodeCountry;
    global $defPhoneCodeCountry;
    global $wpdb;
    global $wpOption; global $WB_orderProcessingMsg;
    $WBSms = unserialize(base64_decode(get_option($wpOption)));  
    $burstSmsApiKey  = base64_decode($WBSms['apikey']);
    $burstSmsApiSecret  = base64_decode($WBSms['apisecret']);
    $burstSmsAdminNumber = base64_decode($WBSms['reciver_number']);
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
    
    foreach($statuses as $key => $os){
       if(is_object($os)){
            if($os->slug == 'on-hold')  $os->slug = 'onhold';
            $defvar = 'burstSms'.$os->slug.'Custom';
            $default = '';
            switch($os->slug){
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
             empty($WBSms[$os->slug.'Custom'])?$$defvar= $default:$$defvar=trim($WBSms[$os->slug.'Custom']);
       }
    }
     ob_start();  
  
?>
    <script type="text/javascript">
        var selectedInput = null;
        jQuery(document).ready(function(){
            jQuery('textarea').focus(function() {
                    selectedInput = this;
                  });
        //remove submit element in tab
        jQuery('#postbox-container-3').css('display','none');
        jQuery('.submit').remove();
        jQuery('[rel="Transmitparamdata"]').css('display','none');
        //jQuery('.syncListHelper').tooltip();
       //sidebar follow scroll
        var el = jQuery('#postbox-container-3');
        //var originalelpos=el.offset().top; // take it where it originally is on the page
        var originalelpos = 0;
        //run on scroll
         jQuery(window).scroll(function(){
            var el =  jQuery('#postbox-container-3'); // important! (local)
            var elpos = el.offset().top; // take current situation
            var windowpos =  jQuery(window).scrollTop();
            var finaldestination = windowpos+originalelpos;
            el.stop().animate({'top':finaldestination},400);
         });
                
            //dialog add CC
            var ccvalue = jQuery( "#ccvalue" ),
    			cclabel = jQuery( "#cclabel" ),
    			allFields = jQuery( [] ).add( ccvalue ).add( cclabel ),
    			tips = jQuery( ".validateTips" );
                
            function updateTips( t ) {
    			tips
    				.text( t )
    				.addClass( "ui-state-highlight" );
    			setTimeout(function() {
    				tips.removeClass( "ui-state-highlight", 1500 );
    			}, 500 );
    		}
    
    		function checkLength( o, n, min, max ) {
    			if ( o.val().length > max || o.val().length < min ) {
    				o.addClass( "ui-state-error" );
    				updateTips( "Length of " + n + " must be between " +
    					min + " and " + max + "." );
    				return false;
    			} else {
    				return true;
    			}
    		}
    
    		function checkRegexp( o, regexp, n ) {
    			if ( !( regexp.test( o.val() ) ) ) {
    				o.addClass( "ui-state-error" );
    				updateTips( n );
    				return false;
    			} else {
    				return true;
    			}
    		}
            jQuery('[rel="Transmitparamdata"]').css('display','none');
            jQuery('[rel="Transmitparamdata2"]').css('display','none');
            var toogleOthrSetting = 0;
            jQuery('#otherSetting').click(function(){
                if(toogleOthrSetting >0){
                    jQuery('[rel="Transmitparamdata2"]').slideUp('slow');
                     jQuery('[rel="Transmitparamdata2"]').css('display','block');
                     <?PHP
                     foreach($statuses as $ks => $orderStatus){
                        if((int)@$WBSms['ena'.ucfirst($orderStatus->slug).'Custom'] > 0){
                            ?> jQuery("#WB_<?=$orderStatus->slug?>Custom").parent("li").css('display','block'); <?PHP
                        }else{
                           ?> jQuery("#WB_<?=$orderStatus->slug?>Custom").parent("li").css('display','none'); <?PHP
                        }
                      }  
                                    ?>
                    toogleOthrSetting = 0;
                     jQuery(this).html('Other Settings &#9660');
                }else {
                    jQuery('[rel="Transmitparamdata2"]').slideDown('slow');
                    
                         <?PHP
                     foreach($statuses as $ks => $orderStatus){
                        if((int)@$WBSms['ena'.ucfirst($orderStatus->slug).'Custom'] > 0){
                            ?> jQuery("#WB_<?=$orderStatus->slug?>Custom").parent("li").css('display','block'); <?PHP
                        }else{
                           ?> jQuery("#WB_<?=$orderStatus->slug?>Custom").parent("li").css('display','none'); <?PHP
                        }
                      }  
                                    ?>
                    
                    toogleOthrSetting = 1
                    jQuery(this).html('Other Settings &#9650');
                    jQuery('#WB_pendingCustom').focus();
                }
               
            });
            renderlist();
            required = ["WB_apikey", "WB_apisecret", "WB_adminNumber","WB_submitLabel"];
            errornotice = jQuery("#error");
            emptyerror = "Please fill out this field.";
            jQuery("#mainform").submit(function(){
              for (i=0;i<required.length;i++) {
    			var input = jQuery('#'+required[i]);
    			if ((input.val() == "") || (input.val() == emptyerror)) {
    				input.addClass("needsfilled");
    				input.val(emptyerror);
    				errornotice.fadeIn(750);
    			} else {
    				input.removeClass("needsfilled");
    			}
    		  }
    		//if any inputs on the page have the class 'needsfilled' the form will not submit
        		if (jQuery(":input").hasClass("needsfilled")) {
                     return false;
        		} else {
                    errornotice.hide();
                	jQuery.ajax({
                       url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
                       type:'POST',
                       beforeSend: function(){
                            jQuery('#wbloader').fadeIn('fast');
                       },
                       data:jQuery(this).serialize() + '&statuses='+ '<?= serialize($statuses)?>',
                       success: function(result){
                            if(result == 'success'){
                                jQuery('#postbox-container-2').before('<div id="WB_saveSetting" class="success">Success : Data has been saved</div>');
                                setTimeout(function() {
                                        jQuery('#WB_saveSetting').remove();
                                    }, 5000);
                            }else{
                                jQuery('#postbox-container-2').before('<div id="WB_saveSetting" class="error">Error : oops we got problems, data cannot be saved</div>');
                                    setTimeout(function() {
                                        jQuery('#WB_saveSetting').remove();
                                    }, 5000);
                            }
                            jQuery('#wbloader').fadeOut('fast');
                          }
    	              });
                     return false;
    		      }
             });
             // Clears any fields in the form when the user clicks on them
        	jQuery(":input").focus(function(){		
        	   if (jQuery(this).hasClass("needsfilled") ) {
        			jQuery(this).val("");
        			jQuery(this).removeClass("needsfilled");
        	   }
        	});
          jQuery("#dialogTestSMS")
            .button()
    			.click(function(event) {
    			     if(jQuery('#WB_adminNumber').val().length < 1){
    			         jQuery('#WB_adminNumber').addClass("needsfilled");
    				    jQuery('#WB_adminNumber').val(emptyerror);
    				    errornotice.fadeIn(750);
                        return false;
    			     }
    				jQuery.ajax({
                           url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
                           type:'POST',
                            beforeSend:function(){
                              jQuery('#testsmsPreloader').fadeIn('fast');  
                            },
                           data:'plugin=WB&action=testSMS&phone=' + jQuery('#WB_adminNumber').val() + '&message='+ jQuery('#WB_receivedCustom').val() + '&key='+ jQuery('#WB_apikey').val() + '&secret='+ jQuery('#WB_apisecret').val(),
                           success: function(result){
                                jQuery('#testsmsPreloader').fadeOut('fast');  
                                 if(result== 'success'){
                                    jQuery('#dialogTestSMS').after('<div class="success">Message has been sent</div>');
                                    setTimeout(function() {
                                        jQuery('.success').remove();
                                    }, 5000);
                                 }else {
                                    jQuery('#dialogTestSMS').after('<div class="error">'+result +'</div>');
                                    setTimeout(function() {
                                        jQuery('.error').remove();
                                    }, 5000);
                                 }     
                              }
        	              });
                          return false;
    			});
         
         });
         function textAreaToogle(thisE,varId){
                if(jQuery(thisE).is(":checked")){
                    jQuery('#' + varId).parent('li').css('display','block');
                }else jQuery('#' + varId).parent('li').css('display','none');
            }
         function renderlist(){
             var apikey = jQuery('#WB_apikey').val();
             var apisecret = jQuery('#WB_apisecret').val();
              if(apikey != "" && apisecret != ""){
                jQuery("#msgVerify").fadeIn('fast');
                jQuery.ajax({
                       url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
                       type:'POST',
                       data:'WB_apikey=' + apikey + '&WB_apisecret='+ apisecret + '&WB_adminNumber=' + jQuery('#WB_adminNumber').val()+'&plugin=WB&getlist=Y&selected=' + <?php echo empty($burstSmsList)?"'N'":$burstSmsList ?>,
                       success: function(result){
                            obj = JSON.parse(result);
                            if(parseInt(obj.status) > 0){
                                //jQuery('#WB_addToList').html(obj.result);
                                jQuery("#msgVerify").css("color","green");
                                jQuery('[rel="Transmitparamdata"]').css('display','block');
                                jQuery('[rel="Transmitparamdata"]').css('display','block');
                                <?PHP
                                 foreach($statuses as $ks => $orderStatus){
                                        if((int)@$WBSms['ena'.ucfirst($orderStatus->slug).'Custom'] > 0){
                                            ?> jQuery("#WB_<?=$orderStatus->slug?>Custom").parent("li").css('display','block'); <?PHP
                                        }else{
                                           ?> jQuery("#WB_<?=$orderStatus->slug?>Custom").parent("li").css('display','none'); <?PHP
                                        }
                                  }  
                                    ?>
                                
                                jQuery("#msgVerify").html("<?php echo WB_successVerify ?>");
                            }else{
                                  jQuery("#msgVerify").css("color","red");
                                  jQuery('[rel="Transmitparamdata"]').css('display','none');
                                  jQuery("#msgVerify").html("<?php echo WB_failVerify ?>");
                               } 
                            }
    		});
                   }
          return false;
        }
        function moveShortcode(shortCode){
            jQuery(selectedInput).val(jQuery(selectedInput).val() + ' ' + shortCode);
        }
        
     </script>

<style>
    #error {
	color:red;
	font-size:10px;
	display:none;
    }
    .needsfilled {
	
	color:red !important;
        border: 1px solid red !important; 
    }
    form ul{list-style-type: none;}
        form ul li{clear: both;height: auto;padding-bottom: 30px;position: relative;}
        .clearfix:after {clear: both;content: ".";display: block;height: 0;margin-bottom: -17px;visibility: hidden;}
        .clearfix {display: block;}
    label {padding-left: 0px;width: 120px;color: #3C3C3C;float: left;text-align:left;}
    input[type="text"], select, textarea, .textarea {background: none repeat scroll 0 0 #F0EFE9;border: 1px solid #938F77;color:#666;float: left;outline: medium none;padding: 4px;width:550px;}
    textarea{height:120px;}
    form em{
        font-size: 11px;
        margin-left: 120px;
    }
    
</style>
<div id="post-body" class="metabox-holder columns-1">
<div class="wrap"> 
    <div id="icon-options-general" class="icon32">
        <br> </div>
<h2> <?php echo  __( 'Transmit SMS Enquiry', 'WB_trdom' );?> </h2><br>

    <div id="postbox-container-2" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="revisionsdiv" class="postbox ">
                <h3 class="hndle"><span style="float:left; margin:0 7px 20px 0;" class="ui-icon ui-icon-gear"></span> <span>
                        <?php    echo "<span>" . __( 'Settings', 'WB_trdom' ) . "</span></h3>"; ?>
            <div class="inside">

            <div style="width:96%; padding:10px">
                    <input type="hidden" name="WB_hidden" value="Y">  
                    
                    <div style=" width:700px">
                        <ul style=" width:700px">
                            <li class="clearfix" style="margin-bottom:-7px !important"> <label for="WB_apikey"><?php _e("API Key : " ); ?> </label><input type="text" name="WB_apikey" id="WB_apikey" value="<?php echo $burstSmsApiKey; ?>" >
                        </li>
                        <li class="clearfix" style="margin-bottom:-7px !important"><label for="WB_apisecret"><?php _e("API Secret : " ); ?></label><input type="text" name="WB_apisecret"  id="WB_apisecret" value="<?php echo $burstSmsApiSecret; ?>" >
                            <em> Get these details from the API settings section of your account.</em>
                        </li>
                         <li class="clearfix">
                             <label for="verify">&nbsp;</label>
                               <input id="verify" class="button button-primary button-large" type="button" onclick="renderlist();" accesskey="p" value="Verify key" name="verify">
                               <em id="msgVerify" style="margin-left:10px !important;display:none"><img src="<?php echo WB_PLUGIN_URL ?>/images/loading.gif" title="still loading" > </em>
                         </li>
                      
                         <li class="clearfix" rel="Transmitparamdata"><label style='height:40px' for="WB_addtolits"><?php _e("Add to list: " ); ?></label>
                        <p> <input value="1" type="checkbox" name="addToPCategory" id="addToPCategory"
                         <?PHP
                            if(!get_option($wpOption)){
                                  echo ' checked="checked"';
                             }elseif( (int)@$WBSms['addToPCategory'] > 0 ){
                                  echo ' checked="checked"';
                              }
                        ?>
                        /> <?PHP _e('Create new lists for each category and add on purchase. <img style="cursor:help" class="help_tip" data-tip="Create new lists for each order category and add on purchase." src="'. WC()->plugin_url() . '/assets/images/help.png" height="16" width="16" />'); ?>
                          </p> <p>
                        <input value="1" type="checkbox" name="addToCustomerL" id="addToCustomerL" <?PHP
                             if(!get_option($wpOption)){
                                        echo ' checked="checked"';
                             }elseif( (int)@$WBSms['addToCustomerL'] > 0 ){
                                         echo ' checked="checked"';
                              }
                      ?> /><?PHP _e('Sync existing customers to "All Customers" List.<img style="cursor:help" class="help_tip" data-tip="Synchronize all existing customer to list \'All Customers\' in your Burst SMS account." src="'. WC()->plugin_url() . '/assets/images/help.png" height="16" width="16" />'); ?>
                         </p> 
                         <input  type="hidden" name="WB_addToList" id="WB_addToList"><!--
                        <em> List ID can be found just before the list name when viewing the list.</em> -->
                     </li>
                    
                     </ul>
                      <h3 rel="Transmitparamdata" class="hndle"><span>
                        <?php    echo "<span>" . __( 'Admin Notifications', 'WB_trdom' ) . "</span></h3>"; ?>
                        <ul>
                         <li class="clearfix" rel="Transmitparamdata"><label for="WB_enaReceivedCustom"> &nbsp;</label>
                            <input type="checkbox" value="1"  name="WB_enaReceivedCustom" <?PHP
                                if(!get_option($wpOption)){
                                    echo ' checked="checked"';
                                }elseif( (int)@$WBSms['enaReceivedCustom'] > 0 ){
                                     echo ' checked="checked"';
                                }
                            ?>
                             /> <?php _e("Enable new order SMS admin notifications " ); ?>

                         </li>
                             <li class="clearfix" rel="Transmitparamdata"><label for="WB_adminNumber"><?php _e("Admin mobile number : " ); ?></label><input type="text" name="WB_adminNumber" id="WB_adminNumber" value="<?php echo $burstSmsAdminNumber; ?>">
                           <em style=" margin-left: 13px !important"> The mobile number you wish to receive messages on in international format eg. 614XXXXXXXX, 447XXXXXXXX</em>
                            <em style=" margin-left: 132px !important">(separated by commas for multiple number) </em>
                       </li>
                            
                       <li class="clearfix" rel="Transmitparamdata"><label for="WB_receivedCustom"><?php _e("Order received custom message : " ); ?></label><textarea type="text" name="WB_receivedCustom"  id="WB_receivedCustom" ><?=$burstSmsreceivedCustom?></textarea>
                       <img style="cursor:pointer" class="help_tip" data-tip="view avalaible short code." onclick="jQuery('#postbox-container-3').toggle();" src="<?= WC()->plugin_url() . '/assets/images/help.png'?>" height="16" width="16" />
                       
                       
                            <em> This message would be sent to the admin.</em>
                   
                       </li>
                        </li>
                       <li class="clearfix" rel="Transmitparamdata"><label for="testSMS"><?php _e("Test sending SMS: " ); ?></label><button id="dialogTestSMS" name="testSMS">Send SMS</button> <img id="testsmsPreloader" style="margin-left:20px;display: none;" src="<?=  WB_PLUGIN_URL.'/images/loader.gif' ?>" />
                     </li>
                       </ul>
                       
                       
                      <h3 rel="Transmitparamdata" class="hndle"><span>
                        <?php    echo "<span>" . __( 'Customer Notifications', 'WB_trdom' ) . "</span></h3>"; ?>
                       <ul>
                        <li style="border-bottom: 1px solid #EFEFEF;" class="clearfix" rel="Transmitparamdata"><label for="sendToCustomerAttr"><?php _e("Send to: " ); ?></label>
                        <input value="1" type="checkbox" name="toBilling" id="toBilling" 
                        <?PHP
                            if(!get_option($wpOption)){
                                  echo ' checked="checked"';
                             }elseif( (int)@$WBSms['sendToBilling'] > 0 ){
                                  echo ' checked="checked"';
                              }
                        ?>
                        /> <?PHP _e('Send to billing number');?> &nbsp;&nbsp;
                        <input value="1" type="checkbox" name="toShipping" id="toShipping" <?PHP
                             if(!get_option($wpOption)){
                                        echo ' checked="checked"';
                             }elseif( (int)@$WBSms['sendToShipping'] > 0 ){
                                         echo ' checked="checked"';
                              }
                      ?> /><?PHP _e('Send to shipping number');?>
                     </li>
                       <!--NEW one-->
                        <?PHP
                        foreach($statuses as $ks => $orderStatus){
                            if($orderStatus->slug == 'processing' || $orderStatus->slug == 'completed' ){
                                $defvar = 'burstSms'.$orderStatus->slug.'Custom';
                       ?> 
                       <li class="clearfix" rel="Transmitparamdata">
                       <label>&nbsp;</label>
                                    <input type="checkbox" value="1" onclick="textAreaToogle(this,'WB_<?=$orderStatus->slug?>Custom')" name="WB_ena<?= ucfirst($orderStatus->slug)?>Custom" id="WB_ena<?= ucfirst($orderStatus->slug)?>Custom" <?= (int)@$WBSms['ena'.ucfirst($orderStatus->slug).'Custom'] < 1?'':' checked="checked"' ?> /> <?php _e("Enable ".strtolower($orderStatus->slug)." order SMS  notifications " ); ?>
                          </li> 
                            <li class="clearfix" rel="Transmitparamdata" style="<?= (int)@$WBSms['ena'.ucfirst($orderStatus->slug).'Custom'] < 1?' display:none':' display:block' ?>" >
                             <label for="WB_<?=$orderStatus->slug?>Custom"><?php _e("Order ".$orderStatus->slug." custom message : " ); ?></label><textarea type="text" name="WB_<?=$orderStatus->slug?>Custom"  id="WB_<?=$orderStatus->slug?>Custom"> <?=@$$defvar?></textarea>
                              <img style="cursor:pointer" class="help_tip" data-tip="view avalaible short code." onclick="jQuery('#postbox-container-3').toggle();" src="<?= WC()->plugin_url() . '/assets/images/help.png'?>" height="16" width="16" />
                       
                                 <em> This message would be sent to the customer.</em>
                            </li>
                            
                       <?PHP }
                           } 
                            foreach($statuses as $ks => $orderStatus){
                                if($orderStatus->slug != 'processing' && $orderStatus->slug != 'completed' ){
                                    if($orderStatus->slug == 'on-hold')  $orderStatus->slug = 'onhold';
                                    $defvar = 'burstSms'.$orderStatus->slug.'Custom';
                             ?> 
                              <li class="clearfix" rel="Transmitparamdata"> <label>&nbsp;</label>
                                    <input onclick="textAreaToogle(this,'WB_<?= $orderStatus->slug ?>Custom')" type="checkbox" value="1" name="WB_ena<?= ucfirst($orderStatus->slug);?>Custom" id="WB_ena<?= ucfirst($orderStatus->slug);?>Custom" <?= (int)@$WBSms['ena'.ucfirst($orderStatus->slug).'Custom'] < 1?'':' checked="checked"' ?> /> <?php _e("Enable $orderStatus->slug order SMS  notifications " ); ?>
                            </li>
                         <li class="clearfix" rel="Transmitparamdata" style="<?= (int)@$WBSms['ena'.ucfirst($orderStatus->slug).'Custom'] < 1?' display:none':' display:block' ?>"  >
                         <label for="WB_<?= $orderStatus->slug?>Custom"><?php _e("Order $orderStatus->slug custom message : " ); ?></label><textarea type="text" name="WB_<?= $orderStatus->slug ?>Custom"  id="WB_<?=$orderStatus->slug ?>Custom"><?PHP if(isset($$defvar))echo $$defvar?></textarea>
                         <img style="cursor:pointer" class="help_tip" data-tip="view avalaible short code." onclick="jQuery('#postbox-container-3').toggle();" src="<?= WC()->plugin_url() . '/assets/images/help.png'?>" height="16" width="16" />
                        <em> This message would be sent to the customer.</em></li>
                             <?PHP
                                }
                             } ?>
                       
                        <li class="clearfix" rel="Transmitparamdata" style="border-top: 1px solid #EFEFEF;padding-top:10px"> <label for="publish">&nbsp;</label>
                            <input id="publish" class="button button-primary button-large" type="submit" accesskey="p" value="&nbsp;&nbsp;&nbsp;SAVE SETTINGS&nbsp;&nbsp;&nbsp" name="publish">

                      </li>
                      </ul>
                        <div id="wbloader" style="display: none;"> <img src="<?= WB_PLUGIN_URL?>/images/loader.gif"> </div>
                        </div>  
                </div>
                </div>
        </div>

    </div>
    </div>

     <div id="postbox-container-3" class="postbox-container" style="margin-left:20px;position: relative;width: 355px;">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="revisionsdiv" class="postbox ">
                 <h3 class="hndle"><span>
                        Avalaible Shortcode </span> </h3>
                    <div class="inside" style="height: 200px;overflow: auto; margin-right: 8px;">
                        <ul>
                            <li style="height: 339px;list-style:bullet;margin-left: 10px;margin-bottom: 10px; width: 300px;">
                             <div onclick="moveShortcode('[order_number]');" title="click to add on last focus textarea"  style=" cursor: pointer;float: left; width: 105px; padding: 4px;background-color: #D7FFF4; margin: 4px;">[order_number]</div>
                            <div onclick="moveShortcode('[order_date]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 90px; padding: 4px;background-color: #D7FFF4;margin: 4px;">[order_date]</div>
                            <div onclick="moveShortcode('[order_total]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 90px; padding: 4px;background-color: #D7FFF4;margin: 4px;">[order_total]</div>
                            <div onclick="moveShortcode('[order_payment_method]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 160px; padding: 4px;background-color: #D7FFF4;margin: 4px;">[order_payment_method]</div>
                           <div onclick="moveShortcode('[order_billing_first_name]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 160px; padding: 4px;background-color: #D7FFF4;margin: 4px;"> [order_billing_first_name]</div>
                             <div onclick="moveShortcode('[order_billing_last_name]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 160px; padding: 4px;background-color: #D7FFF4;margin: 4px;">[order_billing_last_name]</div>
                            <div onclick="moveShortcode('[order_billing_phone]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 135px; padding: 4px;background-color: #D7FFF4;margin: 4px;"> [order_billing_phone]</div>
                            <div onclick="moveShortcode('[order_billing_email]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 130px; padding: 4px;background-color: #D7FFF4;margin: 4px;"> [order_billing_email]</div>
                             <div onclick="moveShortcode('[order_billing_company]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 160px; padding: 4px;background-color: #D7FFF4;margin: 4px;"> [order_billing_company]</div>
                              <div onclick="moveShortcode('[order_billing_address_1]]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 160px; padding: 4px;background-color: #D7FFF4;margin: 4px;"> [order_billing_address_1]</div>
                              <div onclick="moveShortcode('[order_billing_address_2]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 160px; padding: 4px;background-color: #D7FFF4;margin: 4px;"> [order_billing_address_2]</div>
                              <div onclick="moveShortcode('[order_billing_city]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 130px; padding: 4px;background-color: #D7FFF4;margin: 4px;"> [order_billing_city]</div>
                            <div onclick="moveShortcode('[order_billing_state]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 130px; padding: 4px;background-color: #D7FFF4;margin: 4px;">   [order_billing_state]</div>
                            <div onclick="moveShortcode('[order_billing_postcode]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 160px; padding: 4px;background-color: #D7FFF4;margin: 4px;"> [order_billing_postcode]</div>
                              <div onclick="moveShortcode('[order_billing_country]');" title="click to add on last focus textarea" style="cursor: pointer;float: left; width: 160px; padding: 4px;background-color: #D7FFF4;margin: 4px;"> [order_billing_country]</div>
                             </li>
                        </ul>
                    </div>
                 <h3 class="hndle"><span>  How to use </span> </h3>
                    <div class="inside">
                        <ul style="list-style:decimal; margin-left:10px">
                          <li style="width: 340px;"> Fill the textarea with text message depend on type of order status</li>
                        </ul>
                 </div>
            </div>
            </div>
        </div>                
            
</div> 
</div>
<!--- confirm dialog----->

<div id="dialog-confirm" title="Delete International Phone Code" style="display: none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>These items will be delete. Are you sure?</p>
    <br />
</div>
    <?php
    $FORM = ob_get_contents();
    ob_end_clean();
    
   return $FORM;
    
}
?>