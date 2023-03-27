<?php

class Pme_model extends CI_Model
{

    private $clientId;
    private $clientSecret;
    private $clientScope;
    private $urlCallBack;
    private $accessTokenUrl;
    private $authorizeUrl; 

    public function __construct()
    {
        $this->load->database();

        $this->clientId = $this->config->item('PME_CLIENT_ID');
        $this->clientSecret = $this->config->item('PME_CLIENT_SECRET');
        $this->clientScope = $this->config->item('PME_CLIENT_Scope');
        $this->urlCallBack = urlencode($this->config->item('PME_URL_CALLBACK'));
        $this->accessTokenUrl = $this->config->item('PME_ACCESS_TOKEN_URL');
        $this->authorizeUrl = $this->config->item('PME_AUTHORIZE_URL');

        $this->load->model('Pme_model'); 
        $this->load->model('/inc/job_functions_model');
        $this->load->model('/inc/pdf_template');
        $this->load->model('/inc/alarm_functions_model');
        $this->load->model('/inc/functions_model');

    }

    public function call_end_points($params)
    {

        $curl = curl_init();

        // HTTP headers
        $http_header = array(
            "Authorization: Bearer {$this->session->userdata('access_token')}",
            "Content-Type: application/json"
        );

        // curl options
        $curl_opt = array(
            CURLOPT_URL => $params['api_end_points'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $http_header
        );            

        // display - debug
        if( $params['display'] == 1 ){
            print_r($curl_opt);
        }

        curl_setopt_array($curl, $curl_opt);

        $response = curl_exec($curl);
        curl_close($curl);

        $response_decode = json_decode($response);

        if( $response_decode->ResponseStatus != '' ){
            $this->session->unset_userdata('access_token');
            return false;  
        }else{
            return $response;
        }
        
		
    }  

    public function call_end_points_v2($params)
    {

        $curl = curl_init();

        // HTTP headers
        $http_header = array(
            "Authorization: Bearer {$params['access_token']}",
            "Content-Type: application/json"
        );

        // URL to call
        $url = $params['end_points'];

        // GET parameters
        if( count($params['get_param_data']) > 0 ){  
            $url = $params['end_points'] . '?' . http_build_query($params['get_param_data']);            
        }

        // curl options
        $curl_opt = array(
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $http_header
        );     
        
        // POST parameters
        if( count($params['param_data']) > 0 ){  

            $curl_opt[CURLOPT_POST] = true;                                                        
		    $data_string = json_encode($params['param_data']);  
            $curl_opt[CURLOPT_POSTFIELDS] = $data_string;
            
        }
        
        
              

        // display - debug
        if( $params['display'] == 1 ){
            print_r($curl_opt);
        }

        curl_setopt_array($curl, $curl_opt);

        $response = curl_exec($curl);
        curl_close($curl);

        //$response_decode = json_decode($response);

        return $response;
        
		
    }

    public function getAccessToken($params){

        $agency_id = $params['agency_id'];
        $api_id = ( $params['api_id'] != '' )?$params['api_id']:1; // default is Pme

        if( $agency_id > 0 ){

            // get Pme tokens
                $sel_query = "
                access_token,
                expiry,
                refresh_token
            ";
            $this->db->select($sel_query);
            $this->db->from('agency_api_tokens');
            $this->db->where('agency_id', $agency_id);
            $this->db->where('api_id', $api_id);
            $pme_sql = $this->db->get();
            $pme_row = $pme_sql->row();
            
            // data        
            $token_expiry = $pme_row->expiry;
            $current_datetime = date('Y-m-d H:i:s');

            if( $current_datetime >= date('Y-m-d H:i:s',strtotime("{$token_expiry} -10 minutes"))   ){ // refresh token 10 minutes before expiration

                // get new access token from refresh token request
                $refresh_token = $pme_row->refresh_token;
                $refresh_token_json = $this->refreshToken($refresh_token);
                $access_token = json_decode($refresh_token_json)->access_token;
                $refresh_token = json_decode($refresh_token_json)->refresh_token;
                $token_expiry = date('Y-m-d H:i:s',strtotime('+3600 seconds'));

                if( $access_token != '' &&  $refresh_token != '' ){

                    // update new expiry for new token
                    $data = array(
                        'access_token' => $access_token,
                        'expiry' => $token_expiry,
                        'refresh_token' => $refresh_token,
                    );
                    
                    $this->db->where('agency_id', $agency_id);
                    $this->db->where('api_id', $api_id);
                    $this->db->update('agency_api_tokens', $data);

                }                

            }else{
                $access_token = $pme_row->access_token;
            }

            return $access_token;

        }        

    }

    public function refreshToken($refresh_token = "") {

        $token_url = $this->accessTokenUrl;
        $client_id = $this->clientId;
        $client_secret = $this->clientSecret;
        $callback_uri = $this->urlCallBack;

        $authorization = base64_encode("$client_id:$client_secret");
        $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        $content = "grant_type=refresh_token&refresh_token=$refresh_token&redirect_uri=$callback_uri";

        $curl_opt = array(
            CURLOPT_URL => $token_url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $content
        );

        $curl = curl_init();
        
        curl_setopt_array($curl, $curl_opt);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;

    }

    public function get_pme_tenant($params){

		$agency_id = $params['agency_id'];
		$tenants_contact_id = $params['tenants_contact_id'];

		$api_id = 1; // PMe

		// get tenants
		if( $tenants_contact_id ){
	
			// get Pme contacts
			$end_points = "https://app.propertyme.com/api/v1/contacts/{$tenants_contact_id}";

			// get access token
			$pme_params = array(
				'agency_id' => $agency_id,
				'api_id' => $api_id
			);
			$access_token = $this->getAccessToken($pme_params);

			$pme_params = array(
				'access_token' => $access_token,
				'end_points' => $end_points
			);

			return $this->call_end_points_v2($pme_params);

		}	

    }
    
    public function get_pme_landlord($params){

        $agency_id = $params['agency_id'];
		$owner_contact_id = $params['owner_contact_id'];

		$api_id = 1; // PMe

		// get landlord
		if( $owner_contact_id ){

			// get Pme contacts
			$end_points = "https://app.propertyme.com/api/v1/contacts/{$owner_contact_id}";

			// get access token
			$pme_params = array(
				'agency_id' => $agency_id,
				'api_id' => $api_id
			);
			$access_token = $this->getAccessToken($pme_params);

			$pme_params = array(
				'access_token' => $access_token,
				'end_points' => $end_points
			);

			return $this->call_end_points_v2($pme_params);			

		}


    }
    
    public function get_property($params){

        $agency_id = $params['agency_id'];
		$prop_id = $params['prop_id'];


        $end_points = "https://app.propertyme.com/api/v1/lots/{$prop_id}";

        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );

        return $this->call_end_points_v2($pme_params);	

    }

    public function get_property_details($params){

        $agency_id = $params['agency_id'];
		$prop_id = $params['prop_id'];


        $end_points = "https://app.propertyme.com/api/v1/lots/{$prop_id}/detail";

        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );

        return $this->call_end_points_v2($pme_params);	

    }

    public function get_jobs_with_pme_connect($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`jobs` AS j');
        $this->db->join('`property` AS p', 'j.`property_id` = p.`property_id`', 'left');
        $this->db->join('`agency` AS a', ' p.`agency_id` = a.`agency_id`', 'left');

        $this->db->join('`alarm_job_type` AS ajt', 'j.`service` = ajt.`id`', 'left');
        $this->db->join('`job_type` AS jt', 'j.`job_type` = jt.`job_type`', 'left');

        $this->db->join('`agency_api_tokens` AS aat', 'a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1', 'left');

        if ($params['join_table'] > 0) {

            /*
            foreach ($params['join_table'] as $join_table) {
                if ($join_table == 'agency_api_logs') {
                    $this->db->join('`agency_api_logs` AS api_logs', 'j.`id` = api_logs.`job_id`', 'left');
                }
            }
            */
            
        }

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // multiple custom joins
        if( count($params['custom_joins_arr']) > 0 ){

            foreach( $params['custom_joins_arr'] as $custom_joins ){
                $this->db->join($custom_joins['join_table'], $custom_joins['join_on'], $custom_joins['join_type']);
            }            

        }

        if (is_numeric($params['p_deleted'])) {
            $this->db->where('p.`deleted`', $params['p_deleted']);
            if($params['p_deleted'] == 0){
                $this->db->where('( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )');
            }
        }
        if (isset($params['a_status']) && $params['a_status'] != '') {
            $this->db->where('a.`status`', $params['a_status']);
        }
        if (is_numeric($params['del_job'])) {
            $this->db->where('j.`del_job`', $params['del_job']);
        }
        if (is_numeric($params['country_id'])) {
            $this->db->where('a.`country_id`', $params['country_id']);
        }
        if (isset($params['job_status']) && $params['job_status'] != '') {
            $this->db->where('j.`status`', $params['job_status']);
            //$this->db->or('j.`status`', 'Pre Completion');
        }

        if (isset($params['agency_filter']) && $params['agency_filter'] != '') {
            $this->db->where('a.`agency_id`', $params['agency_filter']);
        }
        if (isset($params['job_type']) && $params['job_type'] != '') {
                $this->db->where('j.`job_type`', $params['job_type']);
        }
        if (isset($params['service_filter']) && $params['service_filter'] != '') {
            $this->db->where('j.`service`', $params['service_filter']);
        }
        if (isset($params['state_filter']) && $params['state_filter'] != '') {
            $this->db->where('p.`state`', $params['state_filter']);
        }
        if (isset($params['date']) && $params['date'] != '') {
            $this->db->where('j.`date`', $params['date']);
        }

        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }
        
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // group by
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
        }

        // having
        if (isset($params['having']) && $params['having'] != '') {
            $this->db->having($params['having']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function send_all_certificates_and_invoices($is_get_data = false) {

        ini_set('max_execution_time', 900); 

        $job_status = "Merged Certificates";
        $country_id = $this->config->item('country');
        $pme_api = 1; // PMe

        $sel_query_pme = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`at_myob`,
        j.`sms_sent_merge`,
        j.`client_emailed`,
        j.`prop_comp_with_state_leg`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments,
        p.`propertyme_prop_id`, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`send_emails`,
        a.`account_emails`,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type,

        apd_pme.`api` AS pme_api,
        apd_pme.`api_prop_id` AS pme_prop_id
        ";

        $custom_where = "
        ( 
            j.`is_pme_invoice_upload` IS NULL AND 
            j.`is_pme_bill_create` IS NULL AND 
            ( j.`client_emailed` IS NULL || j.`client_emailed` = '' ) ) AND 
            (
                apd_pme.`api_prop_id` IS NOT NULL AND 
                apd_pme.`api_prop_id` != '' AND 
                apd_pme.`api` = {$pme_api}
            ) AND 
            (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id <> '') AND 
            (aat.connection_date IS NOT NULL AND aat.connection_date != '') AND 
            p.`send_to_email_not_api` = 0 AND 
            ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
        ";
        $paramsPmeSent = array(
            'sel_query' => $sel_query_pme,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type', 'agency_api_logs'),

            'custom_joins_arr' => array(

                array(
                    'join_table' => '`api_property_data` AS apd_pme',
                    'join_on' => "( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )",
                    'join_type' => 'left'
                )

            ),
            
            'custom_where' => $custom_where,
        );
        $pmeQuerySent = $this->Pme_model->get_jobs_with_pme_connect($paramsPmeSent);
        $listsPme = $pmeQuerySent->result_array();

        if ($is_get_data) {
            return $listsPme;
        }

        $isFail = array();
        $isFailUpload = false;

        if (count($listsPme) <= 0) {
            return array("err" => $isFailUpload, "msg" => "All appropriate jobs have already been uploaded an invoice.");
        }

        foreach ($listsPme as $val) {

            // techsheet "Is this Property compliant with current State Legislation?" checkbox to NO
            $is_not_compliant = ( is_numeric($val['prop_comp_with_state_leg']) && $val['prop_comp_with_state_leg'] == 0 )?true:false;

            
            if( $is_not_compliant == false ){ // do not upload API, if not compliant
  
                // optimized moved to 1 function so vjd can use it too
                if( $val['pme_api'] == $pme_api && $val['pme_prop_id'] != '' ){

                    $upload_inv_params = array(
                        'propertyme_prop_id' => $val['pme_prop_id'],
                        'a_id' => $val['a_id'],
                        'jid' => $val['jid'],
                    );
                    $isFailUpload = $this->upload_invoice($upload_inv_params);

                }                

            }

        }
            
        return array("err" => $isFailUpload);

    }

    public function send_all_certificates_and_invoices_via_vjd($job_id_by_vjd = "") {

        if ($job_id_by_vjd == "" || !is_numeric($job_id_by_vjd)) {
            exit();
        }

        ini_set('max_execution_time', 900); 

        $country_id = $this->config->item('country');
        $pme_api = 1; // PMe

        $sel_query_pme = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`at_myob`,
        j.`sms_sent_merge`,
        j.`client_emailed`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments,
        p.`propertyme_prop_id`, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`send_emails`,
        a.`account_emails`,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type,

        apd_pme.`api` AS pme_api,
	    apd_pme.`api_prop_id` AS pme_prop_id
        ";        

        $custom_where = "
        (
            apd_pme.`api_prop_id` IS NOT NULL AND 
			apd_pme.`api_prop_id` != '' AND 
			apd_pme.`api` = {$pme_api}
        ) AND 
        (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id <> '') AND 
        (aat.connection_date IS NOT NULL AND aat.connection_date != '') AND 
        p.`send_to_email_not_api` = 0 AND
        j.`status` IN('Merged Certificates','Completed') AND 
        j.`id` = {$job_id_by_vjd}
        ";
        
        $paramsPmeSent = array(
            'sel_query' => $sel_query_pme,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type', 'agency_api_logs'),
            
            'custom_joins_arr' => array(

                array(
                    'join_table' => '`api_property_data` AS apd_pme',
                    'join_on' => "( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )",
                    'join_type' => 'left'
                )

            ),
            
            'custom_where' => $custom_where,
        );
        $pmeQuerySent = $this->Pme_model->get_jobs_with_pme_connect($paramsPmeSent);
        $listsPme = $pmeQuerySent->result_array();
        $isFail = array();
        $isFailUpload = false;

        if (count($listsPme) <= 0) {
            $ret['status'] = false;
            $ret['msg'] = "This job is not appropriate for uploading invoice to PMe.";
            return $ret;
        }

        foreach ($listsPme as $val) {    

            // optimized moved to 1 function so vjd can use it too
            if( $val['pme_api'] == $pme_api && $val['pme_prop_id'] != '' ){

                $upload_inv_params = array(
                    'propertyme_prop_id' => $val['pme_prop_id'],
                    'a_id' => $val['a_id'],
                    'jid' => $val['jid'],
                );
                $isFailUpload = $this->upload_invoice($upload_inv_params);

            }            

        }
            
        $ret['status'] = $isFailUpload ? false : true;
        $ret['msg'] = $isFailUpload ? "There is a problem uploading to pme. Contact developers." : "Successfully Uploaded Invoice/Bill to PMe.";
        return $ret;

    }

    // wrap up upload API function from merge and vjd to one
    public function upload_invoice($params){

        // variables
        $pmePropId = $params['propertyme_prop_id'];
        $agencyId = $params['a_id'];
        $job_id = $params['jid'];
        $country_id = $this->config->item('country');
        $isFailUpload = false;
        $random_string = date('YmdHis').rand();

        $job_details = $this->job_functions_model->getJobDetails2($job_id,$query_only = false);

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}"; 

        # Alarm Details
        $alarm_details = $this->alarm_functions_model->getPropertyAlarms($job_id, 1, 0, 2);
        $num_alarms = is_null($alarm_details) ? 0 : sizeof($alarm_details);

        # Property + Agent Details
        $property_details = $this->functions_model->getPropertyAgentDetails($job_details['property_id']);
        
        // do not upload certificate pdf for upfront bill(2)
        if( $job_details['assigned_tech'] != 2 ){

            // upload certificate pdf to property documents API
            //$invoice_pdf = $this->pdf_template->pdf_certificate_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id);
            $invoice_pdf = $this->pdf_template->pdf_certificate_template_v2($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id);
            $pdf_name = 'certificate_' . $bpay_ref_code.'_'.$random_string . '.pdf';         
            $res = $this->upload_pdf_to_property($agencyId, $pmePropId, $invoice_pdf, $pdf_name, $job_details);

            $this->add_api_logs($job_id, $res['response'], true, "v1/lots/documents");
            $this->add_upload_pme_documents($job_id);

        }
        
        
        // upload invoice pdf to bills API
        $suppRes = $this->upload_pdf_to_bill($agencyId, $pmePropId, $job_id, $country_id, $job_details, $property_details, $alarm_details, $num_alarms);                            
        if ( $suppRes !== false && !empty($suppRes) ) {

            $stat = $suppRes['response'] == 200 ? 1 : 0;
            $this->add_api_logs($job_id, $suppRes['response'], $stat, "v1/bills");
            if ($stat) {                        

                $this->add_create_pme_bills($job_id);
            }
        }

        return $isFailUpload;

    }

    public function upload_pdf_to_property($agencyId, $pmePropId, $file, $fileName, $job_details) {

        $job_id = $job_details['id'];

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}"; 

        $temp = tmpfile();
        fwrite($temp, $file);
        $path = stream_get_meta_data($temp)['uri'];

        $end_points = "https://app.propertyme.com/api/v1/lots/{$pmePropId}/documents";

        // get access token
        $pme_params = array(
            'agency_id' => $agencyId,
            'api_id' => 1
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);
        $fileName = "sats_".$fileName;
        $params = array(
            'body'=>new CurlFile($path,'application/pdf',$fileName)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $end_points);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12');

        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errNo = curl_errno($ch);
        $errStr = curl_error($ch);
        curl_close($ch);
        fclose($temp);

        // capture API data
        if( $job_id > 0 ){

            $payload_final = ( count($params) > 0 )?json_encode($params):null;

            $api_data_params = array(
                'job_id' => $job_id,
                'api_endpoint' => $end_points,
                'http_header' => json_encode(array("Authorization: Bearer ".$access_token)),
                'payload' => $payload_final,
                'http_status_code' => $responseCode,
                'raw_response' => $response
            );
            $this->system_model->capture_api_data($api_data_params);

        }

        // job log
        $encrypt = rawurlencode($this->encryption_model->encrypt($job_id));
        $baseUrl = $_SERVER["SERVER_NAME"];
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else{
            $protocol = 'http';
        }
        $log_details = "<a href='".$protocol."://{$baseUrl}/pdf/view_certificate/?job_id={$encrypt}'>Certificate</a>, #{$bpay_ref_code} to the linked PMe property";
        $log_params = array(
            'title' => 69,  // PMe API
            'details' => $log_details,
            'display_in_vjd' => 1,
            'property_id' => $job_details['property_id'],
            'job_id' => $job_id,
            'agency_id' => $job_details['agency_id']
        );

        // if not CRON, user logged
        if($this->session->staff_id !='' ){
            $append_jlval = $this->session->staff_id;
            $log_params['created_by_staff'] = $append_jlval;
        }else{
            $append_jlval = 1;
            $log_params['auto_process'] = $append_jlval;
        }

        $this->system_model->insert_log($log_params); 

        return array("errNo" => $errNo, "response" => $responseCode);
    }

    public function add_api_logs($jobId, $apiResponse, $status, $apiUrl = "") {
        $data = array(
            'agency_api_id' => 1, //Pme Logs
            'job_id' => $jobId,
            'api_response' => $apiResponse,
            'status' => $status,
            'api_url' => $apiUrl,
            'date_created' => date('Y-m-d H:i:s')
        );
        $this->db->insert('agency_api_logs', $data);
    }

    public function add_upload_pme_documents($jobId) {
        $ret = $this->db->query("UPDATE jobs SET is_pme_invoice_upload = 1 WHERE `id` = {$jobId}");
        return $ret;
    }    

    public function add_create_pme_bills($jobId) {
        $ret = $this->db->query("UPDATE jobs SET is_pme_bill_create = 1 WHERE `id` = {$jobId}");
        return $ret;
    }

    public function upload_pdf_to_bill($agencyId, $pmePropId, $jobId, $countryId, $job_details, $property_details, $alarm_details, $num_alarms) {

        $bill_error = false;
        $errNo = null;
        $random_string = date('YmdHis').rand();
        $other_errors = [];

        $check_digit = $this->gherxlib->getCheckDigit(trim($jobId));
        $invoice_number = "{$jobId}{$check_digit}";

        // get access token
        $pme_params = array(
            'agency_id' => $agencyId,
            'api_id' => 1 // PMe
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $end_points = "https://app.propertyme.com/api/v1/lots/{$pmePropId}/detail";
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        $lotDetails = $this->pme_model->call_end_points_v2($pme_params);
        $lotDetails = json_decode($lotDetails);

        $getTotalAmount = $this->system_model->getJobAmountGrandTotal($jobId, $countryId);

        $suppId = $this->get_supplier_id_by_agency_id($agencyId);
        if (!is_null($suppId->pme_supplier_id) && floatval($getTotalAmount) > 0) {
            $end_points = "https://app.propertyme.com/api/v1/contacts/{$suppId->pme_supplier_id}";
            $pme_params = array(
                'access_token' => $access_token,
                'end_points' => $end_points
            );
            $chartAccDetails = $this->pme_model->call_end_points_v2($pme_params);
            $chartAccDetails = json_decode($chartAccDetails);

            $supplier_folio_id = $chartAccDetails->FolioId;
            $char_accounts_id = $chartAccDetails->Contact->SupplierChartAccountId; 
            $owner_folio_id = $lotDetails->Ownership->FolioId; 
            $owner_id = $lotDetails->ActiveOwnershipId;

            if ( $char_accounts_id == "00000000-0000-0000-0000-000000000000" || $char_accounts_id == "" ) {

                $log_details = "The Combined Invoice/Cert could not be uploaded to bills, as the supplier is missing Chart Account ID";
                $log_params = array(
                    'title' => 69,  // PMe API
                    'details' => $log_details,
                    'display_in_vjd' => 1,
                    'property_id' => $job_details['property_id'],
                    'job_id' => $jobId,
                    'agency_id' => $job_details['agency_id']
                );

                // if not CRON, user logged
                if($this->session->staff_id !='' ){
                    $append_jlval = $this->session->staff_id;
                    $log_params['created_by_staff'] = $append_jlval;
                }else{
                    $append_jlval = 1;
                    $log_params['auto_process'] = $append_jlval;
                }
                $this->system_model->insert_log($log_params);
                $errNo = array("errNo" => 0, "response" => "Supplier's ChartAccountId is empty");
                $bill_error = true;
                $other_errors[] = "Supplier's ChartAccountId is empty";
            }

            // owners folio is required for creating bill, add logs if missing
            if ( $owner_folio_id == "00000000-0000-0000-0000-000000000000" || $owner_folio_id == "" ) { 

                $log_details = "The Combined Invoice/Cert <b>failed to create bill</b>, as there is no owner attached to this property in PMe";
                $log_params = array(
                    'title' => 69,  // PMe API
                    'details' => $log_details,
                    'display_in_vjd' => 1,
                    'property_id' => $job_details['property_id'],
                    'job_id' => $jobId,
                    'agency_id' => $job_details['agency_id']
                );

                // if not CRON, user logged
                if($this->session->staff_id !='' ){
                    $append_jlval = $this->session->staff_id;
                    $log_params['created_by_staff'] = $append_jlval;
                }else{
                    $append_jlval = 1;
                    $log_params['auto_process'] = $append_jlval;
                }
                $this->system_model->insert_log($log_params);
                $errNo = array("errNo" => 0, "response" => "Owner Folio ID is empty");
                $bill_error = true;
                $other_errors[] = "Owner Folio ID is empty";
            }


            if( $bill_error == true || $property_details['send_to_email_not_api'] == 1 ){ // bill creation will fail on PMe, send invoice to email instead

                // send invoice through email
                $job_params = array(
                    'job_id' => $jobId
                );
                $this->email_functions_model->send_invoice_email($job_params);

            }else{ // create bill through PMe                 

                // create bill API call
                $create_bill_params = array(
                    'job_details' => $job_details,
                    'invoice_number' => $invoice_number,
                    'agencyId' => $agencyId,
                    'jobId' => $jobId,
                    'property_details' => $property_details,
                    'alarm_details' => $alarm_details,
                    'num_alarms' => $num_alarms,
                    'countryId' => $countryId,
                    'random_string' => $random_string,
                    
                    'supplier_folio_id' => $supplier_folio_id,
                    'owner_folio_id' => $owner_folio_id,
                    'owner_id' => $owner_id,
                    'getTotalAmount' => $getTotalAmount,
                    'char_accounts_id' => $char_accounts_id,

                    'other_errors' => $other_errors
                );
                $create_bill_ret = $this->create_bill($create_bill_params);   
                
                $response = $create_bill_ret['response'];
                $responseCode = $create_bill_ret['responseCode'];
                $errNo_curl = $create_bill_ret['errNo_curl'];
                $errStr = $create_bill_ret['errStr'];

                if( $responseCode == 200 || $responseCode == 204 ){ // OK, success

                    // job log1
                    $encrypt = rawurlencode($this->encryption_model->encrypt($jobId));
                    $baseUrl = $_SERVER["SERVER_NAME"];
                    if(isset($_SERVER['HTTPS'])){
                        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
                    } else{
                        $protocol = 'http';
                    }
                    $getTotalAmount = number_format($getTotalAmount,2);
                    $log_details = "<a href='".$protocol."://{$baseUrl}/pdf/view_invoice/?job_id={$encrypt}'>Invoice</a>, #{$invoice_number} uploaded to PMe Agency as a bill of {$getTotalAmount}";
                    $log_params = array(
                        'title' => 69,  // PMe API
                        'details' => $log_details,
                        'display_in_vjd' => 1,
                        'property_id' => $job_details['property_id'],
                        'job_id' => $jobId,
                        'agency_id' => $job_details['agency_id']
                    );

                    // if not CRON, user logged
                    if($this->session->staff_id !='' ){
                        $append_jlval = $this->session->staff_id;
                        $log_params['created_by_staff'] = $append_jlval;
                    }else{
                        $append_jlval = 1;
                        $log_params['auto_process'] = $append_jlval;
                    }

                    $this->system_model->insert_log($log_params);

                }else{ // create bill again but this time without the biller code

                    // create bill API call
                    $create_bill_params = array(
                        'job_details' => $job_details,
                        'invoice_number' => $invoice_number,
                        'agencyId' => $agencyId,
                        'jobId' => $jobId,
                        'property_details' => $property_details,
                        'alarm_details' => $alarm_details,
                        'num_alarms' => $num_alarms,
                        'countryId' => $countryId,
                        'random_string' => $random_string,
                        
                        'supplier_folio_id' => $supplier_folio_id,
                        'owner_folio_id' => $owner_folio_id,
                        'owner_id' => $owner_id,
                        'getTotalAmount' => $getTotalAmount,
                        'char_accounts_id' => $char_accounts_id,

                        'other_errors' => $other_errors,
                        'exclude_biller_code' => true
                    );
                    $create_bill_ret = $this->create_bill($create_bill_params);   
                    
                    $response = $create_bill_ret['response'];
                    $responseCode = $create_bill_ret['responseCode'];
                    $errNo_curl = $create_bill_ret['errNo_curl'];
                    $errStr = $create_bill_ret['errStr'];

                }
                
                $errNo = array("errNo" => $errNo_curl, "response" => $responseCode);
            
            }

            return $errNo;

            
        }else {
            return false;
        }

    }

    public function create_bill($params){

        $job_details = $params['job_details'];
        $invoice_number = $params['invoice_number'];
        $agencyId = $params['agencyId'];
        $jobId = $params['jobId'];
        $property_details = $params['property_details'];
        $alarm_details = $params['alarm_details'];
        $num_alarms = $params['num_alarms'];
        $countryId = $params['countryId'];
        $random_string = $params['random_string'];

        $supplier_folio_id = $params['supplier_folio_id'];
        $owner_folio_id = $params['owner_folio_id'];
        $owner_id = $params['owner_id'];
        $getTotalAmount = $params['getTotalAmount'];
        $char_accounts_id = $params['char_accounts_id'];

        $other_errors = $params['other_errors'];
        $exclude_biller_code = $params['exclude_biller_code'];

        $end_points = "https://app.propertyme.com/api/v1/bills";
        $p_address = $job_details['address_1'] . " " . $job_details['address_2'] . " " . $job_details['address_3'] . " " . $job_details['state'] . " " . $job_details['postcode'];
        $detail = 'Smoke Alarm Testing';

        // get access token
        $pme_params = array(
            'agency_id' => $agencyId,
            'api_id' => 1 // PMe
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);        
        
        // updated due date value from job date + 30 days to job date only
        $dueDate = date('m/d/Y' , strtotime($job_details['jdate']));

        $invoice_pdf = $this->pdf_template->pdf_invoice_template($jobId, $job_details, $property_details, $alarm_details, $num_alarms, $countryId);

        $fileName = 'invoice' . $invoice_number.'_'.$random_string . '.pdf';

        $temp = tmpfile();
        fwrite($temp, $invoice_pdf);
        $path = stream_get_meta_data($temp)['uri'];

        $param_data = array(
            'DueDate' => $dueDate,
            'FromFolioId' => $supplier_folio_id, // v1/contacts/suppliers - FolioId
            'ToFolioId' => $owner_folio_id,
            'OwnershipId' => $owner_id,
            'Priority' => '1',
            'Detail' => $detail,
            'Amount' => $getTotalAmount,
            'ChartAccountId' => $char_accounts_id, // v1/contacts/{Id} - SupplierChartAccountId
            'Reference' => 'Invoice #' .$invoice_number,
            'body' => new CurlFile($path,'application/pdf',$fileName),
            'IsTaxed' => 1
        ); 

        if ( ( $job_details['display_bpay'] && $countryId == 1 ) && $exclude_biller_code == false ) {
            $param_data['BillerCode'] = '264291';
            $param_data['PaymentCode'] = $invoice_number;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $end_points);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param_data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12');
        
        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errNo_curl = curl_errno($ch);
        $errStr = curl_error($ch);
        curl_close($ch);
        fclose($temp);

        // capture API data
        if( $jobId > 0 ){

            $payload_final = ( count($param_data) > 0 )?json_encode($param_data):null;
            $other_errors_final = ( count($other_errors) > 0 )?json_encode($other_errors):null;

            $api_data_params = array(
                'job_id' => $jobId,
                'api_endpoint' => $end_points,
                'http_header' => json_encode(array("Authorization: Bearer ".$access_token)),
                'payload' => $payload_final,
                'http_status_code' => $responseCode,
                'raw_response' => $response,
                'other_errors' => $other_errors_final
            );
            $this->system_model->capture_api_data($api_data_params);

        }

        return array(
            'response' => $response,
            'responseCode' => $responseCode,
            'errNo_curl' => $errNo_curl,
            'errStr' => $errStr
        );

    }

    public function get_supplier_id_by_agency_id($agencyId) {
        $q_supp = "
            SELECT 
                `pme_supplier_id`
            FROM `agency`
            WHERE `agency_id` = {$agencyId} 
        ";
        $get_supp_id = $this->db->query($q_supp);
        $id = $get_supp_id->row();

        return $id;
    }

    public function check_if_present_in_pme_tab($job_id) {

        $country_id = $this->config->item('country');
        $sql_str = "SELECT j.id
        FROM jobs AS j
        LEFT JOIN property AS p ON j.property_id = p.property_id
        LEFT JOIN api_property_data AS apd ON p.property_id = apd.crm_prop_id
        LEFT JOIN agency AS a ON p.agency_id = a.agency_id
        LEFT JOIN staff_accounts AS sa ON j.assigned_tech = sa.StaffID
        LEFT JOIN agency_api_tokens AS aat ON (a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1)
        WHERE j.status = 'Merged Certificates'        
        AND a.account_emails LIKE '%@%'
        AND j.client_emailed IS NULL
        AND a.`country_id` = {$country_id}
        AND p.`deleted` =0
        AND a.`status` = 'active'
        AND j.`del_job` = 0
        AND (apd.`api_prop_id` IS NOT NULL AND apd.`api_prop_id` != '') AND (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id <> '') AND (aat.connection_date IS NOT NULL AND aat.connection_date != '') AND p.`send_to_email_not_api` = 0
        AND p.`send_to_email_not_api` = 0
        AND j.id = {$job_id}";

        //AND (p.`propertyme_prop_id` IS NOT NULL AND p.`propertyme_prop_id` != '') AND (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id <> '') AND (aat.connection_date IS NOT NULL AND aat.connection_date != '') AND p.`send_to_email_not_api` = 0

        $query = $this->db->query($sql_str);
        $res = $query->row();
        if (empty($res->id)) {
            return false;
        }else {
            return true;
        }

    }

    public function get_all_properties($agency_id){


        if( $agency_id > 0 ){

            $end_points = "https://app.propertyme.com/api/v1/lots";

            // get access token
            $api_id = 1; // PMe
            $pme_params = array(
                'agency_id' => $agency_id,
                'api_id' => $api_id
            );
            $access_token = $this->getAccessToken($pme_params);

            $pme_params = array(
                'access_token' => $access_token,
                'end_points' => $end_points
            );            
            return $this->call_end_points_v2($pme_params);                       

        }        

    }


    public function get_archived_properties($agency_id,$offset,$limit){


        if( $agency_id > 0 ){

            $end_points = "https://app.propertyme.com/api/v1/lots/archived";

            // get access token
            $api_id = 1; // PMe
            $pme_params = array(
                'agency_id' => $agency_id,
                'api_id' => $api_id
            );
            $access_token = $this->getAccessToken($pme_params);

            $get_param_data = array(
                "Offset" => $offset,
                "Limit" => $limit
            );
            $pme_params = array(
                'access_token' => $access_token,
                'end_points' => $end_points,
                'get_param_data' => $get_param_data
            );            
            return $this->call_end_points_v2($pme_params);                       

        }        

    }

    public function get_all_archived_properties($agency_id){

        $pme_arch_prop_arr = [];

        $offset = 0;
        $per_page = 100; // PMe archived list limit

        do{

            // get archived PMe properties            
            $response = $this->get_archived_properties($agency_id,$offset,$per_page);	
            $pme_arch_prop_dec = json_decode($response);            
            
            // get count
            $pme_arch_prop_dec_count = count($pme_arch_prop_dec);

            // next page
            $offset += $per_page;

            if( $pme_arch_prop_dec_count > 0 ){
            
                foreach( $pme_arch_prop_dec as $pme_arch_prop_obj ){
                    $pme_arch_prop_arr[] = $pme_arch_prop_obj; // add to array
                }

            }

        }while( $pme_arch_prop_dec_count > 0 );

        return $pme_arch_prop_arr;

    }


}
