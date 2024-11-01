jQuery(document).ready(function(){
    jQuery('#testsmsPreloader').fadeOut('fast');
    jQuery('#WB_LoadingRender').fadeOut('fast');
    //renderlist();
    jQuery('.submit').after('<div id="wbloader" style="display:none"> <img src="'+ decodeURIComponent(getCookie('WB_PLUGIN_URL')) +'/images/loader.gif"> </div>');
    jQuery('.wb_customOrder').parent('fieldset').parent('.forminp').parent('tr').css('display','none');
   // defaultChecked();
    required = ["woocommerce_integration-transmitsms_WB_apikey", "woocommerce_integration-transmitsms_WB_apisecret", "woocommerce_integration-transmitsms_WB_adminNumber"];
    errornotice = jQuery("#error");
    emptyerror = "Please fill out this field.";
    /*
    jQuery("#mainform").submit(function(){
       for (i=0;i<required.length;i++) {
		var input = jQuery('#'+required[i]);
		if ((input.val() == "") || (input.val() == emptyerror)) {
			input.addClass("needsfilled");
            input.css('color','red !important');        
        	input.css('border','1px solid red !important');
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
               url: document.URL,
               type:'POST',
               beforeSend: function(){
                    jQuery('#wbloader').fadeIn('fast');
               },
               data:jQuery(this).serialize()+'&postSubmit=Y',
               success: function(result){
                    if(result == 'success'){
                        jQuery('.form-table').before('<div id="WB_saveSetting" class="success">Success : Data has been saved</div>');
                        setTimeout(function() {
                                jQuery('#WB_saveSetting').remove();
                            }, 5000);
                    }else{
                        jQuery('.form-table').before('<div id="WB_saveSetting" class="error">Error : oops we got problems, data cannot be saved</div>');
                            setTimeout(function() {
                                jQuery('#WB_saveSetting').remove();
                            }, 5000);
                    }
                    jQuery('#wbloader').fadeOut('fast');
                  }
              });
             return false;
	      }
     }); */
     // Clears any fields in the form when the user clicks on them
	jQuery(":input").focus(function(){		
	   if (jQuery(this).hasClass("needsfilled") ) {
			jQuery(this).val("");
			jQuery(this).removeClass("needsfilled");
	   }
	});
    
});
function defaultChecked(){
     jQuery('#woocommerce_integration-transmitsms_WB_adminNumber').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
     jQuery('#woocommerce_integration-transmitsms_WB_receivedCustom').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
     jQuery('#woocommerce_integration-transmitsms_dialogTestSMS').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
     if(jQuery('#woocommerce_integration-transmitsms_WB_enaPendingCustom').is(":checked")){
         jQuery('#woocommerce_integration-transmitsms_burstSmspendingCustom').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
    }
    if(jQuery('#woocommerce_integration-transmitsms_WB_enaFailedCustom').is(":checked")){
         jQuery('#woocommerce_integration-transmitsms_burstSmsfailedCustom').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
    }
    if(jQuery('#woocommerce_integration-transmitsms_WB_enaProcessingCustom').is(":checked")){
         jQuery('#woocommerce_integration-transmitsms_burstSmsprocessingCustom').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
    }
    if(jQuery('#woocommerce_integration-transmitsms_WB_enaPendingCustom').is(":checked")){
         jQuery('#woocommerce_integration-transmitsms_burstSmspendingCustom').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
    }
    if(jQuery('#woocommerce_integration-transmitsms_WB_enaOnholdCustom').is(":checked")){
         jQuery('#woocommerce_integration-transmitsms_burstSmsonholdCustom').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
    }
    if(jQuery('#woocommerce_integration-transmitsms_WB_enaCompletedCustom').is(":checked")){
         jQuery('#woocommerce_integration-transmitsms_burstSmscompletedCustom').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
    }
     if(jQuery('#woocommerce_integration-transmitsms_WB_enaRefundedCustom').is(":checked")){
         jQuery('#woocommerce_integration-transmitsms_burstSmsrefundedCustom').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
    }
     if(jQuery('#woocommerce_integration-transmitsms_WB_enaCancelledCustom').is(":checked")){
         jQuery('#woocommerce_integration-transmitsms_burstSmscancelledCustom').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');  
    }
}

function textAreaToogle(thisE,varId){
   if(jQuery(thisE).is(":checked")){
        jQuery('#'+varId).parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');
    }else {
        jQuery('#'+varId).parent('fieldset').parent('.forminp').parent('tr').css('display','none');
    }
}

function renderlist(ajaxUrl){
 var apikey = jQuery('#woocommerce_integration-transmitsms_WB_apikey').val();
 var apisecret = jQuery('#woocommerce_integration-transmitsms_WB_apisecret').val();
 jQuery('.Transmitparamdata').parent('fieldset').parent('.forminp').parent('tr').fadeOut('fast');
 jQuery('.Transmitparamdata').parent('label').parent('fieldset').parent('.forminp').parent('tr').fadeOut('fast');
 var conResult = jQuery('#woocommerce_integration-transmitsms_WB_verify').parent('fieldset').parent('.forminp');
 conResult.append('<img id="WB_LoadingRender" style="display:none"  src="'+ decodeURIComponent(getCookie('WB_PLUGIN_URL')) +'/images/loading.gif" /><div id="renderResult"> </div>');
 if(apikey != "" && apisecret != ""){
        jQuery("#msgVerify").fadeIn('fast');
        jQuery.ajax({
               url: ajaxUrl,
               type:'POST',
               beforeSend:function(){
                    jQuery('#WB_LoadingRender').fadeIn('fast');
               },
               data:'WB_apikey=' + apikey + '&WB_apisecret='+ apisecret + '&WB_adminNumber=' + jQuery('#WB_adminNumber').val()+'&plugin=WB&getlist=Y&selected=N',
               success: function(result){
                    jQuery('#WB_LoadingRender').remove();
                    obj = JSON.parse(result);
                    if(parseInt(obj.status) > 0){
                        //jQuery('.Transmitparamdata').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');
                        jQuery('.Transmitparamdata').parent('label').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');
                        jQuery('#woocommerce_integration-transmitsms_WB_adminSideTitle').parent('fieldset').parent('.forminp').parent('tr').fadeIn('fast');
                        defaultChecked();
                        jQuery('#renderResult').html("<span  style='color:green;font-size:11px'>Your key has been verified successfully</span>");
                    }else{
                         // jQuery("#msgVerify").css("color","red");
                         jQuery('#renderResult').html("<span style='color:red;font-size:11px'>Sorry..api key and secret you entered still invalid</span>");
                       } 
                    }
            });
           }
    return false;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
    }
    return "";
} 

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
} 
function urldecode(str) {
   return decodeURIComponent((str+'').replace(/\+/g, '%20'));
}

function sendTestSMS(){
   if(jQuery('#woocommerce_integration-transmitsms_WB_adminNumber').val().length < 1){
         jQuery('#woocommerce_integration-transmitsms_WB_adminNumber').addClass("needsfilled");
	    jQuery('#woocommerce_integration-transmitsms_WB_adminNumber').val(emptyerror);
	    errornotice.fadeIn(750);
        return false;
			     }
	jQuery.ajax({
           url:  document.URL,
           type:'POST',
            beforeSend:function(){
              jQuery('#testsmsPreloader').fadeIn('fast');  
            },
           data:'plugin=WB&action=testSMS&phone=' + jQuery('#woocommerce_integration-transmitsms_WB_adminNumber').val() + '&message='+ jQuery('#woocommerce_integration-transmitsms_WB_receivedCustom').val() + '&key='+ jQuery('#woocommerce_integration-transmitsms_WB_apikey').val() + '&secret='+ jQuery('#woocommerce_integration-transmitsms_WB_apisecret').val(),
           success: function(result){ 
                 if(result== 'success'){
                 //   jQuery('#woocommerce_integration-transmitsms_dialogTestSMS').after('<div class="success msTEst">Message has been sent</div>');
                   jQuery('#woocommerce_integration-transmitsms_dialogTestSMS').after('<div class="updated msgSentSuccess"><p><strong>Message has been sent.</strong></p></div>');
                    setTimeout(function() {
                        jQuery('.msgSentSuccess').remove();
                    }, 5000);
                 }else {
                    jQuery('#woocommerce_integration-transmitsms_dialogTestSMS').after('<div class="error">'+result +'</div>');
                    setTimeout(function() {
                        jQuery('.error').remove();
                    }, 5000);
                 } 
                jQuery('#testsmsPreloader').fadeOut('fast');     
              }
          });
          return false;
}