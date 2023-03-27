<?php

class JcClass {

	protected $CI;

	// We'll use a constructor, as you can't directly call a function
	// from a property definition.
	public function __construct(){
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function formatStaffName($fname,$lname){
		return "{$fname}".( ($lname!="")?' '.strtoupper(substr($lname,0,1)).'.':'' );
	}

	public function formatDate($date,$format='Y-m-d'){
		return date($format,strtotime(str_replace("/","-",$date)));
	}

	function displayUserImage($user_photo){
		return ( isset($user_photo) && $user_photo != '' )?"/uploads/user_accounts/photo/{$user_photo}":'/images/avatar-2-64.png';
	}

	public function getServiceIcons($service,$color=''){

		switch($color){
			case 1:
				$color_str = 'white';
			break;
			case 2:
				$color_str = 'grey';
			break;
			default:
				$color_str = 'colored';
		}

		switch($service){
			case 2:
				$serv_icon = 'smoke_'.$color_str.'.png';
			break;
			case 5:
				$serv_icon = 'safety_'.$color_str.'.png';
			break;
			case 6:
				$serv_icon = 'corded_'.$color_str.'.png';
			break;
			case 7:
				$serv_icon = 'water_'.$color_str.'.png';
			break;
			case 8:
				$serv_icon = 'sa_ss_'.$color_str.'.png';
			break;
			case 9:
				$serv_icon = 'sa_cw_ss_'.$color_str.'.png';
			break;
			case 11:
				$serv_icon = 'sa_wm_'.$color_str.'.png';
			break;
			case 12:
				$serv_icon = 'sa_'.$color_str.'_IC.png';
			break;
			case 13:
				$serv_icon = 'sa_ss_'.$color_str.'_IC.png';
			break;
			case 14:
				$serv_icon = 'sa_cw_ss_'.$color_str.'_IC.png';
			break;
		}

		return $serv_icon;

	}


	// get escalate jobs
	function get_escalate_jobs(){

		$this->CI->load->model('jobs_model');

		$country_id = $this->CI->session->country_id;
		$agency_id = $this->CI->session->agency_id;

		$sel_query = "
			DISTINCT (
				j.`property_id`
			),
			j.`id` AS j_id,
			j.`property_id`,
			j.`work_order`,
			j.`service` AS j_service,

			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,
			p.`property_managers_id`,
			p.`alarm_code`,
			p.`key_number`,

			a.`agency_id`,

			aua.`agency_user_account_id`,
			aua.`fname` AS pm_fname,
			aua.`lname` AS pm_lname,
			aua.`email` AS pm_email
		";

		$custom_where = "
			sejr.`escalate_job_reasons_id` != 4
			AND (
				j.`agency_approve_en` = 0 ||
				j.`agency_approve_en` IS NULL
			)
		";

		$params = array(
			'sel_query' => $sel_query,
			'p_deleted' => 0,
			'a_status' => 'active',
			'del_job' => 0,
			'agency_id' => $agency_id,
			'j_status' => 'Escalate',
			'country_id' => $country_id,
			'custom_where' => $custom_where
		);
		$query = $this->CI->jobs_model->get_escalated_jobs($params);

		return $query->num_rows();

	}


	// get escalate jobs
	function get_service_due_jobs(){

		$this->CI->load->model('jobs_model');

		$country_id = $this->CI->session->country_id;
		$agency_id = $this->CI->session->agency_id;

		$sel_query = "
			j.`id` AS j_id,
			j.`service` AS j_service,
			j.`property_id` AS j_property_id,
			j.`work_order`,
			j.`property_vacant`,

			p.`property_id`,
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,

			p.`alarm_code`,
			p.`key_number`,

			aua.`agency_user_account_id`,
			aua.`fname` AS pm_fname,
			aua.`lname` AS pm_lname,
			aua.`email` AS pm_email
		";

		$params = array(
			'sel_query' => $sel_query,
			'p_deleted' => 0,
			'a_status' => 'active',
			'del_job' => 0,
			'agency_id' => $agency_id,
			'j_status' => 'Pending',
			'country_id' => $country_id
		);
		$query = $this->CI->jobs_model->get_jobs($params);

		return $query->num_rows();

	}


	//get agency contact phone (added by gherx)
    function get_agency_phone(){
		$this->CI->load->model('user_accounts_model');
		return $this->CI->user_accounts_model->get_agency_phone();
	}

	function delete_image($photo){

		if( file_exists($photo) ){
			//echo 'file exist';
			unlink($photo);
		}else{
			//echo 'file doesnt exist';
		}

	}

	public function insert_log($params){

		$data = [];

		$data = array(
			'title' => $params['title'],
			'details' => $params['details'],
			'created_date' => date('Y-m-d H:i:s')
		);

		if( isset($params['created_by']) && $params['created_by'] > 0 ){
			$data['created_by'] = $params['created_by'];
		}

		// ID's
		if( isset($params['job_id']) && $params['job_id'] > 0 ){
			$data['job_id'] = $params['job_id'];
		}

		if( isset($params['property_id']) && $params['property_id'] > 0 ){
			$data['property_id'] = $params['property_id'];
		}

		if( isset($params['agency_id']) && $params['agency_id'] > 0 ){
			$data['agency_id'] = $params['agency_id'];
		}

		// markers
		if( isset($params['display_in_vjd']) && is_numeric($params['display_in_vjd']) ){
			$data['display_in_vjd'] = $params['display_in_vjd'];
		}

		if( isset($params['display_in_vpd']) && is_numeric($params['display_in_vpd']) ){
			$data['display_in_vpd'] = $params['display_in_vpd'];
		}

		if( isset($params['display_in_vad']) && is_numeric($params['display_in_vad']) ){
			$data['display_in_vad'] = $params['display_in_vad'];
		}

		if( isset($params['display_in_portal']) && is_numeric($params['display_in_portal']) ){
			$data['display_in_portal'] = $params['display_in_portal'];
		}

		if( isset($params['display_in_accounts']) && is_numeric($params['display_in_accounts']) ){
			$data['display_in_accounts'] = $params['display_in_accounts'];
		}

		if( isset($params['display_in_accounts_hid']) && is_numeric($params['display_in_accounts_hid']) ){
			$data['display_in_accounts_hid'] = $params['display_in_accounts_hid'];
		}

		if( isset($params['display_in_sales']) && is_numeric($params['display_in_sales']) ){
			$data['display_in_sales'] = $params['display_in_sales'];
		}

		if( $this->CI->db->insert('logs', $data) ){
			return true;
		}else{
			return false;
		}


	}


	// get escalate jobs
	function get_tenants($params){

		$this->CI->load->model('tenants_model');

		$sel_query = "
			pt.`property_tenant_id`,
			pt.`property_id`,
			pt.`tenant_firstname`,
			pt.`tenant_lastname`,
			pt.`tenant_mobile`,
			pt.`tenant_landline`,
			pt.`tenant_email`
		";

		$params = array(
			'sel_query' => $sel_query,
			'active' => $params['active'],
			'property_id' => $params['property_id'],
			'limit' => $params['limit'],
			'offset' => $params['offset']
		);

		return $this->CI->tenants_model->get_tenants($params);

	}


	// get last yearly maintenance
	function get_ym_service($params){

		$this->CI->load->model('jobs_model');

		$country_id = $this->CI->session->country_id;
		$agency_id = $this->CI->session->agency_id;

		$sel_query = "
		j.`id` AS j_id,
		j.`date` AS jdate
		";

		$j_params = array(
			'sel_query' => $sel_query,
			'p_deleted' => 0,
			'a_status' => 'active',
			'del_job' => 0,
			'property_id' => $params['property_id'],
			'j_status' => 'Completed',
			'job_type' => 'Yearly Maintenance',
			'j_service' => $params['j_service'],
			'sort_list' => array(
				array(
					'order_by' => 'j.`date`',
					'sort' => 'DESC'
				)
			),
			'limit' => 1,
			'offset' => 0,

		);
		return $this->CI->jobs_model->get_jobs($j_params);

	}


	public function pagination_count($params){

		//pagi count
		$sakone = floor($params['total_rows']/($params['offset']+$params['per_page']));
		$saktwo = floor($params['total_rows']/$params['per_page']);
		$pagi_x = ($saktwo!=0) ? (!$params['offset'])? '1': $params['offset'] +1 : '1' ;
		$pagi_y = ($params['offset'] == floor($params['total_rows']/ $params['per_page']))? $params['total_rows'] : (!$params['offset'] ? $params['offset'] * $params['per_page'] + $params['per_page'] : ($sakone != 0) ? $params['per_page'] + $params['offset'] :  $params['total_rows'] );

		return "Showing ".(($params['total_rows']!=0)?$pagi_x.' - ':'') .$pagi_y." of ".$params['total_rows']." Items" ;

	}


	function isDateNotEmpty($date){
		if(
			$date!='' &&
			$date!='0000-00-00' &&
			$date!='0000-00-00 00:00:00' &&
			$date!='1970-01-01'
		){
			return true;
		} else{
			return false;
		}
	}



	// ENCRYPTION FUNCTIONS
	// encrpyt
	public function encrypt($plaintext){

		$cipher = $this->CI->config->item('encrpytion_cipher');
		$key = $this->CI->config->item('encrpytion_key');

		$encrypt_arr = [];
		if (in_array($cipher, openssl_get_cipher_methods()))
		{
			$ivlen = openssl_cipher_iv_length($cipher);
			$iv = openssl_random_pseudo_bytes($ivlen);
			$ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=0, $iv, $tag);

			// store in array
			return $encrypt_arr = array(
				'ciphertext' => $ciphertext,
				'iv' => $iv,
				'tag' => $tag
			);
		}

	}

	// decrypt
	public function decrypt($encrypt){

		$cipher = $this->CI->config->item('encrpytion_cipher');
		$key = $this->CI->config->item('encrpytion_key');

		// unpack array
		$ciphertext = $encrypt['ciphertext'];
		$iv = $encrypt['iv'];
		$tag = $encrypt['tag'];

		$original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv, $tag);
		return $original_plaintext;

	}

	/*
	// parse the tags on logs link
	public function parseDynamicLink($params){

		$log_details = $params['log_details'];

		$this->CI->load->model('logs_model');
		$this->CI->load->model('properties_model');
		$this->CI->load->model('user_accounts_model');

		// property
		$tag = '{p_address}';
		// find the tag
		if( strpos($log_details,$tag) !== false ){

			// get logs data
			$l_params = array(
				'sel_query' => 'l.`property_id`',
				'log_id' => $params['log_id']
			);
			$query = $this->CI->logs_model->get_logs($l_params);
			$l_row = $query->row();

			if( isset($l_row->property_id) && $l_row->property_id > 0 ){

				// get property data
				$p_params = array(
					'sel_query' => '
						p.`property_id`,
						p.`address_1`,
						p.`address_2`,
						p.`address_3`,
						p.`state`,
						p.`postcode`
					',
					'property_id' => $l_row->property_id
				);
				$query = $this->CI->properties_model->get_property_data($p_params);

				if( $query->num_rows() > 0 ){
					$p_row = $query->row();
					$vpd_link = "<a href='/properties/property_detail/{$p_row->property_id}'>{$p_row->address_1} {$p_row->address_2} {$p_row->address_3}</a>";

					// replace tags
					$log_details =  str_replace($tag, $vpd_link, $log_details);
				}


			}

		}


		// agency user
		$tag = 'agency_user';
		// find the tag
		if( strpos($log_details,$tag) !== false ){

			// break down the tag to get the agency user ID
			$tag_string = $this->get_part_of_string($log_details,'{','}');
			$str_exp = explode(':',$tag_string);
			$aua_id = $str_exp[1];


			// get agency user data
			$sel_query = "
				aua.`agency_user_account_id`,
				aua.`fname`,
				aua.`lname`
			";

			$aua_params = array(
				'sel_query' => $sel_query,
				'aua_id' => $aua_id
			);
			$aua_sql = $this->CI->user_accounts_model->get_user_accounts($aua_params);
			if( $aua_sql->num_rows() > 0 ){
				$aua_row = $aua_sql->row();
				$aua_link = "<a href='/user_accounts/my_profile/{$aua_row->agency_user_account_id}'>{$aua_row->fname} {$aua_row->lname}</a>";

				// replace tags
				$log_details =  str_replace('{'.$tag_string.'}', $aua_link, $log_details);
			}

		}


		// created by
		$tag = '{created_by}';
		// find the tag
		if( strpos($log_details,$tag) !== false ){

			// get logs data
			$l_params = array(
				'sel_query' => 'l.`created_by`',
				'log_id' => $params['log_id']
			);
			$query = $this->CI->logs_model->get_logs($l_params);
			$l_row = $query->row();

			if( isset($l_row->created_by) && $l_row->created_by > 0 ){

				// get agency user data
				$sel_query = "
					aua.`agency_user_account_id`,
					aua.`fname`,
					aua.`lname`
				";

				$aua_params = array(
					'sel_query' => $sel_query,
					'aua_id' => $l_row->created_by
				);
				$aua_sql = $this->CI->user_accounts_model->get_user_accounts($aua_params);

				if( $aua_sql->num_rows() > 0 ){
					$aua_row = $aua_sql->row();
					$aua_link = "<a href='/user_accounts/edit/{$aua_row->agency_user_account_id}'>{$aua_row->fname} {$aua_row->lname}</a>";

					// replace tags
					$log_details =  str_replace($tag, $aua_link, $log_details);
				}

			}

		}

		return $log_details;

	}
	*/

	// parse the tags on logs link
	public function parseDynamicLink2($logObj){

		$crmLink = $this->CI->config->item("crm_link");

		$log_details = $logObj->details;

		// property
		$tag = '{p_address}';
		// find the tag
		if( strpos($log_details,$tag) !== false ){

			if( $logObj->p_address_1 ){
				$vpd_link = "<a href='{$crmLink}/view_property_details.php?id={$logObj->property_id}'>{$logObj->p_address_1} {$logObj->p_address_2} {$logObj->p_address_3}</a>";

				// replace tags
				$log_details =  str_replace($tag, $vpd_link, $log_details);
			}

		}

		// agency user
		if(isset($logObj->taggedAgencyUser) ){
			$taggedAgencyUser = $logObj->taggedAgencyUser;
			$pattern = "/agency_user:\d+/";
			$matches = [];
			preg_match($pattern, $log_details, $matches);
			$firstMatch = $matches[0];

			$aua_link = "{$taggedAgencyUser->fname} {$taggedAgencyUser->lname}";

			// replace tags
			$log_details =  str_replace("{{$firstMatch}}", $aua_link, $log_details);

		}

		// agency user
		if(isset($logObj->taggedStaff) ){
			$taggedStaff = $logObj->taggedStaff;
			$pattern = "/staff_user:\d+/";
			$matches = [];
			preg_match($pattern, $log_details, $matches);
			$firstMatch = $matches[0];

			$toReplace = "<a href='/users/view/{$taggedStaff->StaffID}'>{$taggedStaff->FirstName} {$taggedStaff->LastName}</a>";

			// replace tags
			$log_details =  str_replace("{{$firstMatch}}", $toReplace, $log_details);
		}


		// created by
		$tag = '{created_by}';
		// find the tag
		if( strpos($log_details,$tag) !== false ){

			if( $logObj->fname ){
				$aua_link = "<a href='/user_accounts/edit/{$logObj->created_by}'>{$logObj->fname} {$logObj->lname}</a>";

				// replace tags
				$log_details =  str_replace($tag, $aua_link, $log_details);
			}

		}

		return $log_details;

	}

	// get part of string from start to end
	public function get_part_of_string($string,$start_str,$end_str){

		$startpos= strpos($string,$start_str);
		$endpos= strpos($string,$end_str);

		$length = $endpos-$startpos;
		return substr($string,$startpos+1,$length-1);

	}

	// get country data
	public function get_country_data(){

		if( isset($this->CI->session->country_id) && $this->CI->session->country_id > 0 ){
			$country_id = $this->CI->session->country_id;
		}else{

			if( strpos(base_url(),'sats.com.au') !== false ){ // AU
				$country_id = 1;
			}else if( strpos(base_url(),'sats.co.nz') !== false ){ // NZ
				$country_id = 2;
			}else if( strpos(base_url(),'ci.loc') !== false ){ // FOR LOCAL TEST ONLY
				$country_id = 1;
			}

		}

		if( isset($country_id) && $country_id > 0 ){
			// get country data
			$c_params = array('country_id' => $country_id);
			return $this->CI->mixed_db_model->get_countries($c_params);
		}

	}


	public function export_csv($params){

		// SAMPLE passed params
		/*
		$export_header = 'Address,Property Manager,Job Type,Completed Date\r\n';
		foreach ($jobs_sql->result() as $row){
			$p_address = "{$row->p_address_1} {$row->p_address_2} {$row->p_address_3} {$row->p_state} {$row->p_postcode}";
			$export_body_row[] = "\"{$p_address}\",\"{$p_address}\",\"{$p_address}\",\"{$p_address}\"";
		}
		$export_body_row_imp = implode('\r\n',$export_body_row);

		$export_params = array(
			'header' => $export_header,
			'body_row' => $export_body_row_imp
		);
		$this->jcclass->export_csv($export_params);
		*/

		$filename = "completed_jobs_".rand()."_".date("d/m/Y").".csv";

		// send headers for download
		header("Content-Type: text/csv");
		header("Content-Disposition: Attachment; filename={$filename}");
		header("Pragma: no-cache");

		echo $params['export_header'].$params['body_row'];

	}

	// Random Password
	public function randomPassword() {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}

	// QLD quote upgrade
	public function get240vRfAgencyAlarm($agency_id){

		$this->CI->db->select('aa.`price`');
		$this->CI->db->from('`agency_alarms` AS aa');
		$this->CI->db->where('aa.`agency_id`', $agency_id);
		$this->CI->db->where('aa.`alarm_pwr_id`', 10);
		$this->CI->db->limit( 1, 0);
		$query = $this->CI->db->get();
		$row = $query->row();

		return $row->price;

	}

	public function getQldUpgradeQuoteAmount($params){

		$quote_qty = $params['qld_new_leg_alarm_num']; // get the IC alarm number(from techsheet)
		$price_240vrf = $this->CI->mixed_db_model->get240vRfAgencyAlarmPrice($params['agency_id']); // get 240v RF alarm price
		$quote_price = ( $price_240vrf > 0 )?$price_240vrf:$this->CI->config->item('default_qld_upgrade_quote_price');;
		return $quote_total = $quote_price*$quote_qty;

	}


	// Get Operating System
	public function getOS() {

		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		$os_platform  = "Unknown OS Platform";

		$os_array     = array(
							  '/windows nt 10/i'      =>  'Windows 10',
							  '/windows nt 6.3/i'     =>  'Windows 8.1',
							  '/windows nt 6.2/i'     =>  'Windows 8',
							  '/windows nt 6.1/i'     =>  'Windows 7',
							  '/windows nt 6.0/i'     =>  'Windows Vista',
							  '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
							  '/windows nt 5.1/i'     =>  'Windows XP',
							  '/windows xp/i'         =>  'Windows XP',
							  '/windows nt 5.0/i'     =>  'Windows 2000',
							  '/windows me/i'         =>  'Windows ME',
							  '/win98/i'              =>  'Windows 98',
							  '/win95/i'              =>  'Windows 95',
							  '/win16/i'              =>  'Windows 3.11',
							  '/macintosh|mac os x/i' =>  'Mac OS X',
							  '/mac_powerpc/i'        =>  'Mac OS 9',
							  '/linux/i'              =>  'Linux',
							  '/ubuntu/i'             =>  'Ubuntu',
							  '/iphone/i'             =>  'iPhone',
							  '/ipod/i'               =>  'iPod',
							  '/ipad/i'               =>  'iPad',
							  '/android/i'            =>  'Android',
							  '/blackberry/i'         =>  'BlackBerry',
							  '/webos/i'              =>  'Mobile'
						);

		foreach ($os_array as $regex => $value)
			if (preg_match($regex, $user_agent))
				$os_platform = $value;

		return $os_platform;
	}

	// Get Browser
	public function getBrowser() {

		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		$browser        = "Unknown Browser";

		$browser_array = array(
								'/msie/i'      => 'Internet Explorer',
								'/firefox/i'   => 'Firefox',
								'/safari/i'    => 'Safari',
								'/chrome/i'    => 'Chrome',
								'/edge/i'      => 'Edge',
								'/opera/i'     => 'Opera',
								'/netscape/i'  => 'Netscape',
								'/maxthon/i'   => 'Maxthon',
								'/konqueror/i' => 'Konqueror',
								'/mobile/i'    => 'Handheld Browser'
						 );

		foreach ($browser_array as $regex => $value)
			if (preg_match($regex, $user_agent))
				$browser = $value;

		return $browser;
	}

	public function getIPaddress(){
		return $_SERVER['REMOTE_ADDR'];
	}

}

?>