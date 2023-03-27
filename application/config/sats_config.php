<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//ini_set("error_reporting", E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING & ~E_STRICT);

$env = ENVIRONMENT; 
$country = COUNTRY; 

$config['country'] = $country;

// set timezone
if( $country == 1 ){ // AU
	date_default_timezone_set('Australia/Sydney');
	$config['country_code'] = '+61';
}else if( $country == 2 ){ // NZ
	date_default_timezone_set('Pacific/Auckland');
	$config['country_code'] = '+64';
}

$config['user_photo'] = '/uploads/user_accounts/photo';
$config['photo_empty'] = 'blank/avatar-2-64.png';
$config['pagi_per_page'] = 50; // pagination per page

// encryption
$config['encrpytion_cipher'] = 'aes-128-gcm';
$config['encrpytion_key'] = 'sats123';

// set this if agency CI is now using the agency.sats.com domain
$agency_domain_used = 0;

// CRM link
$config['crm'] = ( $env == 'production' )?'crm':'crmdev';
$config['crmci'] = ( $env == 'production' )?'crmci':'crmdevci';
$config['agencyci'] = ( $env == 'production' )?'agency':'agencydev';
$config['country_domain'] = ( $country == 1 )?'com.au':'co.nz';
$config['sats_domain'] = 'sats.'.$config['country_domain'];
$config['crm_link'] = 'https://'.$config['crm'].'.'.$config['sats_domain'];
$config['crmci_link'] = 'https://'.$config['crmci'].'.'.$config['sats_domain'];
$config['agencyci_link'] = 'https://'.$config['agencyci'].'.'.$config['sats_domain'];


//agency link
$config['agency'] = ( $env == 'production' )?'agency':'agencydev';
$config['agency_link'] = 'https://'.$config['agency'].'.'.$config['sats_domain'];


// default 240v RF alarm price
$config['default_qld_upgrade_quote_price'] = 200;

// test emails
$qa_email = "bent@sats.com.au";
//$qa_email = "pokemaniacs123@yahoo.com";
//$qa_email = "vaultdweller123@gmail.com";
//$qa_email = "danielk@sats.com.au";
//$qa_email = "itsmegherx@gmail.com";
//$qa_email = "shielahrose123@gmail.com";
//$qa_email = "qa@sats.com.au";

$dev_key_word = 'dev'; // ben's request

// SATS emails
if( $env == 'production' ){ // LIVE
		
	$cc_email = "cc@{$config['sats_domain']}";
	$auth_email = "authority@{$config['sats_domain']}";
	$urgent_email = "urgent@{$config['sats_domain']}";
	$accounts_email = "accounts@{$config['sats_domain']}";
	$sales_email = "sales@{$config['sats_domain']}";
	$hr_email = "hr@{$config['sats_domain']}";
	$info_email = "info@{$config['sats_domain']}";
	$mm_email = "maintenance_manager@{$config['sats_domain']}";
	$keys_email = "keys@{$config['sats_domain']}";
	$reports_email = "reports@{$config['sats_domain']}";
	$no_show_email = "noshow@{$config['sats_domain']}";
	$it_email = "it@{$config['sats_domain']}";
		
}else{ // DEV
	
	$cc_email = "{$dev_key_word}cc@{$config['sats_domain']}";
	$auth_email = "{$dev_key_word}authority@{$config['sats_domain']}";
	$urgent_email = "{$dev_key_word}urgent@{$config['sats_domain']}";
	$accounts_email = "{$dev_key_word}accounts@{$config['sats_domain']}";
	$sales_email = "{$dev_key_word}sales@{$config['sats_domain']}";
	$hr_email = "{$dev_key_word}hr@{$config['sats_domain']}";
	$info_email = "{$dev_key_word}info@{$config['sats_domain']}";
	$mm_email = "{$dev_key_word}maintenance_manager@{$config['sats_domain']}";
	$keys_email = "{$dev_key_word}keys@{$config['sats_domain']}";
	$reports_email = "{$dev_key_word}reports@{$config['sats_domain']}";
	$no_show_email = "{$dev_key_word}noshow@{$config['sats_domain']}";
	$it_email = "{$dev_key_word}it@{$config['sats_domain']}";
	
}

// set email
$config['sats_cc_email'] = $cc_email;
$config['sats_auth_email'] = $auth_email;
$config['sats_urgent_email'] = $urgent_email;
$config['sats_accounts_email'] = $accounts_email;
$config['sats_sales_email'] = $sales_email;
$config['sats_info_email'] = $info_email;
$config['sats_mm_email'] = $mm_email;
$config['sats_keys_email'] = $keys_email;
$config['sats_reports_email'] = $reports_email;
$config['sats_hr_email'] = $hr_email;
$config['sats_no_show_email'] = $no_show_email;
$config['sats_it_email'] = $it_email;
$config['sats_new_tenant'] = 1;


// Company Info/Details
$config['COMPANY_FULL_NAME'] = "Smoke Alarm Testing Services";



//Pusher Details
if( $country == 1 ){ // AU
	$config['PUSHER_APP_ID'] = "717445";
	$config['PUSHER_KEY'] = "b40d8e7ff097144dac0c";
	$config['PUSHER_SECRET'] = "52933bc92f4871f2074a";
	$config['PUSHER_CLUSTER'] = "ap1";

}else if( $country == 2 ){ // NZ
	$config['PUSHER_APP_ID'] = "717446";
	$config['PUSHER_KEY'] = "0e2496c39cbe369a9ddf";
	$config['PUSHER_SECRET'] = "3ce1f0954b70d36e397b";
	$config['PUSHER_CLUSTER'] = "ap1";
}



//PropertyMe Details
if( $country == 1 ){ // AU
	$config['PME_CLIENT_ID'] = "5ff326e1-18f3-4c9e-9092-607ad116c81e";
	$config['PME_CLIENT_SECRET'] = "e8383f6a-8340-4905-8f5c-21fca8702a9a";
}else if( $country == 2 ){ // NZ
	/* The same as above, because no PMe API NZ account registered yet. Change when registered. 
	 * You can still use this but you need to email David, follow note PME_URL_CALLBACK.
	 */
	$config['PME_CLIENT_ID'] = "5ff326e1-18f3-4c9e-9092-607ad116c81e";
	$config['PME_CLIENT_SECRET'] = "e8383f6a-8340-4905-8f5c-21fca8702a9a";
}
$config['PME_URL_CALLBACK'] = BASEURL."property_me/callback"; // No need to switch
/*
 *	Note for PME_URL_CALLBACK: contact David to change the accepted redirect url in their system.
 *  To use AU - https://crmci.sats.com.au/property_me/callback
 *  To use NZ - https://crmci.sats.co.nz//property_me/callback
 */
$config['PME_CLIENT_Scope'] = "contact:read%20property:read%20property:write%20activity:read%20communication:read%20transaction:read%20transaction:write%20offline_access";
$config['PME_ACCESS_TOKEN_URL'] = "https://login.propertyme.com/connect/token";
$config['PME_AUTHORIZE_URL'] = "https://login.propertyme.com/connect/authorize";

// Blink API
if( $country == 1 ){ // AU

	$config['blink_email'] = 'info@sats.com.au';
	$config['blink_pass'] = 'smoke123';
	$config['blink_refresh_token'] = '3t23CdM3glcuZQ3oMsleTB3LzHK8-NhpsqT061Vm0IRAr';
	$config['blink_domain_id'] = '39327';

}else if( $country == 2 ){ // NZ

	$config['blink_email'] = 'info@sats.co.nz';
	$config['blink_pass'] = 'smoke123';
	$config['blink_refresh_token'] = '4eKxWVuUkKpwPfnzvo3HnaOqsQnmqH5mfCl_0oDr58Gqc';
	$config['blink_domain_id'] = '39331';

}

// Wholesale SMS
$config['ws_sms_reply_url'] = "{$config['crm_link']}/sms_replies_catch.php";
$config['ws_sms_dlvr_url'] = "{$config['crm_link']}/sms_delivered_catch.php";

//1 = On, 0 = Off
$config['yabbr_switch'] = 1;
if( $country == 1 ){ // AU

	$config['ws_sms_api_key'] = '57300872d42a549a242e9a4886bf10da';
	$config['ws_sms_api_secret'] = '666f8e3d50e3ca340c7fade14dd82442';
	//$config['ws_sms_auth_header'] = 'NTczMDA4NzJkNDJhNTQ5YTI0MmU5YTQ4ODZiZjEwZGE6NjY2ZjhlM2Q1MGUzY2EzNDBjN2ZhZGUxNGRkODI0NDI=';

	//yabbr
	if( $env=='production' ){ ## LIVE
		##Live SMS API
		##$config['yabbr_sms_api_key'] = 'YXBpOjFhZjQ2NjRmYjQzMzc0NzczOGFlNmQ4NmY3YzkzNjM0MDljMjBjZmQ5ZGFkYmI0MmExZjJlMWZjYTc1NWM3NjA='; ##LIVE disabled
		$config['yabbr_sms_api_key'] = 'YXBpOmI0MDJmMTU1NWZkMDFjNmMyZDRjOTliYTI0NmQ0MmRjYjM1OGY3ZDg5MDQxZDRmOTJlMGRmYjUyNDM5MWFmODQ=';
		$config['yabbr_virtual_number'] = '61485817467';
	}else{ ## DEV
		##Dev SMS API
		$config['yabbr_sms_api_key'] = ''; ## disable dev for now as per Joe's instrunction
		$config['yabbr_virtual_number'] = '';
	}
	
}else if( $country == 2 ){ // NZ

	$config['ws_sms_api_key'] = 'a294fdcf898af6af131f825f30dec91c';
	$config['ws_sms_api_secret'] = '9f4caf7c4f53eba3de7ffe77bbd8c0c4';
	//$config['ws_sms_auth_header'] = 'YTI5NGZkY2Y4OThhZjZhZjEzMWY4MjVmMzBkZWM5MWM6OWY0Y2FmN2M0ZjUzZWJhM2RlN2ZmZTc3YmJkOGMwYzQ=';

	//yabbr
	if( $env=='production' ){ ## LIVE
		##Live SMS API
		//$config['yabbr_sms_api_key'] = 'YXBpOjFhZjQ2NjRmYjQzMzc0NzczOGFlNmQ4NmY3YzkzNjM0MDljMjBjZmQ5ZGFkYmI0MmExZjJlMWZjYTc1NWM3NjA=';  ##LIVE disabled
		$config['yabbr_sms_api_key'] = 'YXBpOmI0MDJmMTU1NWZkMDFjNmMyZDRjOTliYTI0NmQ0MmRjYjM1OGY3ZDg5MDQxZDRmOTJlMGRmYjUyNDM5MWFmODQ=';
	}else{ ## DEV
		##Dev SMS API
		//$config['yabbr_sms_api_key'] = 'YXBpOmI0MDJmMTU1NWZkMDFjNmMyZDRjOTliYTI0NmQ0MmRjYjM1OGY3ZDg5MDQxZDRmOTJlMGRmYjUyNDM5MWFmODQ='; ## disable dev as per Joe's instrunction
	}
}

//swal 
$config['showConfirmButton'] = 'false';
$config['timer'] = 2000; //2sec

// accounts date filter
if( $country == 1 ){ // AU
	$config['accounts_financial_year'] = '2020-06-01'; 
}else if( $country == 2 ){ // NZ
	$config['accounts_financial_year'] = '2019-12-01'; 
}

// google map API
if( $country == 1 ){ // AU
	$config['gmap_api_key'] = 'AIzaSyAUHcKVPXD_kJQyPCC-bvTNEPsxC8LAUmA';
}else if( $country == 2 ){ // NZ
	$config['gmap_api_key'] = 'AIzaSyBSCcImRAb-7OggYHpIhuHuFeLujZwscAo';
}

// PALACE API base url
if( $country == 1 ){ // AU
	$config['palace_api_base_liquid'] = 'https://api.getpalace.com'; // liquid system (new)
	$config['palace_api_base_legacy'] = 'https://serviceapia.realbaselive.com'; // legacy system (old)
}else if( $country == 2 ){ // NZ
	$config['palace_api_base_liquid'] = 'https://api.getpalace.com'; // liquid system (new)
	$config['palace_api_base_legacy'] = 'https://serviceapi.realbaselive.com'; // legacy system (old)
}

## VAD users allowed to Unlink COnnected API Properties button
if( $country == 1 ){ //AU
	$config['allowed_people_to_pme_unlink'] = array(11,2070,2025,2175);
}else if( $country == 2 ){ // NZ
	$config['allowed_people_to_pme_unlink'] = array(11,2070,2025,2193);
}

## VAD API ALLOWED TO EDIT/UPDATE > CHECKBOX
if( $country == 1 ){ //AU
	$config['user_can_edit_api'] = array(2025,11,2175,2070); //Daniel,Ness,Thalia id in AU
}else if( $country == 2 ){ // NZ
	$config['user_can_edit_api'] = array(2025,2193,11,2191); //Daniel,Ness,Thalia,Tayler in in NZ
}

## GHERX: VAD PRICING AND ADD AGENCY page > alarms allowed to add with 0 price
if($country==1){ #AU allowed zero price
	$config['alarm_allowed_zero_price'] = array(1,2,4,7,18,19,20,21); ##Allow 0 price for 240V,9V,240vLi,9v(EP),240v(EP)
}else{ #NZ allowed zero price
	$config['alarm_allowed_zero_price'] = array(2,7,11,12); ##Allow 0 price for 240V,3Vli,3vLi(Orc)
}

// sales commission version switch
$config['sales_commission_ver'] = 'new';

##renewal_start_offset
$config['renewal_start_offset_default'] = 15;	##15 days
$config['renewal_start_offset_nsw'] = 30;	##30 days

##Allowed to edit/assign homepage content block per user class
if($country==1){
	$config['allow_to_edit_user_class_block'] = array(2025,2070,2428); //Daniel, DevTest, Charlote B
}else{
	$config['allow_to_edit_user_class_block'] = array(2025,2070,2289); //Daniel, DevTest, Charlote B
}
?>
