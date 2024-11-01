<?php
//ajax handle
if( !session_id() )session_start();
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(isset($_POST['plugin']) && $_POST['plugin'] == 'WB'){
    if(isset($_POST['getlist']) && $_POST['getlist']== 'Y'){
        require_once WB_PLUGIN_DIR .'/APIClient2.php';
        global $wpOption;
        global $wpOptionCustomerList;
        global $wpOptionProductList; 
        global $customerListName;
        $apikey = trim($_POST['WB_apikey']);
        $secret = trim($_POST['WB_apisecret']);
        $recivernumber = trim($_POST['WB_adminNumber']);
        $selectedId = trim($_POST['selected']);
        $api=new transmitsmsAPI($apikey,$secret);
        $offset=1;
        $limit=100;
        $result=$api->getLists($offset,$limit);
        if($result->error->code=='SUCCESS')
        {
            $selected = '';
            
            $arrResult = array();
            $arrResult['status'] = 1;
            $takeId = '';
            foreach ($result->lists as $list) {
               $takeId = $list->id;
                if($selectedId == (int)$list->id) {
                    $selected = 'selected="selected"';
                 }
            }
            if( !get_option($wpOption)) {
              
                //sync for the first time installation
                 //create list on burst sms on first installtion plugin
                $customFields=array('transmit sms woocommerce','client');
                $resultC=$api->addList($customerListName,$customFields); 
                //save ID of BURST SMS LIST
                if(!empty($resultC->id)){
                    update_option($wpOptionCustomerList, $resultC->id);  
                }
                $cusData = get_users('role=Customer');             
                foreach ($cusData as $user) {
                    $customFields=array('description'=> WB_addtoListDesc .' '.get_bloginfo('wpurl'));
                    if(empty($resultC->id))$resultC->id = 0;
                    $resultL=$api->addToList($resultC->id,  $user->billing_phone, $user->first_name,$user->last_name, $customFields);
                }
                //add option at first time
                $statuses = (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) );
                $burstcc = array();
                if(isset($_POST['burstcc']) && is_array($_POST['burstcc'])){
                    foreach($_POST['burstcc'] as $key => $val){
                        $arrBccTemp = explode('|',$val);
                        $burstcc[$arrBccTemp[1]] = $arrBccTemp[0];        
                    }
                }
                 $arrSEtting = array('apikey'=>base64_encode($apikey),'apisecret'=>base64_encode($secret),'reciver_number' =>base64_encode($recivernumber),
                    'receivedCustom'=> @$_POST['WB_receivedCustom'],
                    'enaReceivedCustom' => 1, 
                   'sendToBilling'=>1,
                   'sendToShipping'=>1,'addToCustomerL'=>1,'addToPCategory'=>1);
                 update_option($wpOption, addslashes(serialize($arrSEtting)));
                 //set cookies
                setcookie('woocomerceTransmitSmsApiKey',  base64_encode($apikey), time() + (86400 * 30), "/");
                setcookie('woocomerceTransmitSmsSecret',  base64_encode($apisecret), time() + (86400 * 30), "/");
                setcookie('woocomerceTransmitSmsAdminNumber',  base64_encode($recivernumber), time() + (86400 * 30), "/");
                return true;
            }
        }
        else
        {
            delete_option('WBSmsSettings');
            $arrResult['status'] = 0;
            $arrResult['result'] = $result->error->description;
        }
        
        echo json_encode($arrResult);
        exit();
     
    }
    
    //delete country phone code
    if(isset($_POST['action']) && $_POST['action'] == 'deleteCPC' ){
        $WBSms = unserialize(stripslashes(get_option('WBSmsSettings')));
        $burstSmsCountryCode=$WBSms['country_code'];
        unset($burstSmsCountryCode[(int)$_POST['id']]);
        $WBSms['country_code'] = $burstSmsCountryCode;
        update_option( 'WBSmsSettings', addslashes(serialize($WBSms)));
        exit();
    }
    if(isset($_POST['action']) && $_POST['action'] == 'testSMS' ){
        require_once WB_PLUGIN_DIR . '/APIClient2.php';
        $phone = trim($_POST['phone']);
        $arrPhone = explode(',',$phone);
        $msg = trim($_POST['message']);
        $burstSmsApiKey  = trim($_POST['key']);
        $burstSmsApiSecret  = trim($_POST['secret']);
        $WBmsAPI = new transmitsmsAPI($burstSmsApiKey, $burstSmsApiSecret);
        foreach($arrPhone as $key =>$tPhone){
            $result=$WBmsAPI->sendSms($msg, trim($tPhone)); 
        }
        if($result->error->code=='SUCCESS'){ 
            echo 'success';   
        }else{
           echo $result->error->description;
        } 
        exit();
    }
}
if(isset($_POST['WB_hidden']) && $_POST['WB_hidden'] == "Y"){
	if(WBSMSC_handleSubmit()){
	   echo 'success';
	}else echo 'fail';
    exit();
}



function  WBSMSC_handleSubmit(){
    global $wpOption;
    $apikey = base64_encode(trim($_POST['WB_apikey']));
    $apisecret = base64_encode(trim($_POST['WB_apisecret']));
    $recivernumber = base64_encode(trim($_POST['WB_adminNumber']));
    $ownerCostum = empty($_POST['WB_ownerCostum'])?'':$_POST['WB_ownerCostum'];
    $receivedCustom = trim($_POST['WB_receivedCustom']);
    $listId = $_POST['WB_addToList'];
    $statuses = unserialize($_POST['statuses']);
    $burstcc = array();
    if(isset($_POST['burstcc']) && is_array($_POST['burstcc'])){
        foreach($_POST['burstcc'] as $key => $val){
            $arrBccTemp = explode('|',$val);
            $burstcc[$arrBccTemp[1]] = $arrBccTemp[0];        
        }
    }
    $arrSEtting = array('apikey'=>$apikey,'apisecret'=>$apisecret,'reciver_number' =>$recivernumber,
                    'ownerCostum'=>$ownerCostum,'receivedCustom'=> $receivedCustom,
                    'enaReceivedCustom' => empty($_POST['WB_enaReceivedCustom'])?'':$_POST['WB_enaReceivedCustom'], 
                   'list_id'=>$listId,
                   'sendToBilling' =>empty($_POST['toBilling'])?'':$_POST['toBilling'], 
                   'sendToShipping' =>empty($_POST['toShipping'])?'':$_POST['toShipping'],
                   'addToCustomerL' =>empty($_POST['addToCustomerL'])?'':$_POST['addToCustomerL'],
                   'addToPCategory' =>empty($_POST['addToPCategory'])?'':$_POST['addToPCategory']
                   );
                   
    //add order status
   
    foreach($statuses as $ks => $orderStatus){
        if($orderStatus->slug == 'on-hold')  $orderStatus->slug = 'onhold';
        $arrSEtting[$orderStatus->slug.'Custom'] =   trim($_POST['WB_'.$orderStatus->slug.'Custom']);
        $arrSEtting['ena'.ucfirst($orderStatus->slug).'Custom'] =  empty($_POST['WB_ena'.ucfirst($orderStatus->slug).'Custom'])?'':$_POST['WB_ena'.ucfirst($orderStatus->slug).'Custom'];
        
    }
    update_option($wpOption, base64_encode(serialize($arrSEtting)));
    return true;
   
}



?>
