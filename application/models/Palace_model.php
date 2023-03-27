<?php

class Palace_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function call_end_points($params, $is_json = false)
    {
        $curl = curl_init();

        // HTTP headers

        if ($is_json) {
            $http_header = array(
                "Authorization: Basic {$params['access_token']}",
                "Content-Type: application/json"
            );
        }else {
            $http_header = array(
                "Authorization: Basic {$params['access_token']}",
                "Content-Type: application/xml"
            );
        }

        curl_setopt_array($curl, array(
          CURLOPT_URL => $params['end_points'],
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => $http_header,
          CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $xml_snippet = simplexml_load_string( $response );
        $json_convert = json_encode( $xml_snippet );
        $json = json_decode( $json_convert );
        if ($is_json) {
            return $response;
        }else {
            return (array)($json);
        }

    }


    public function get_end_points($params)
    {

        $curl = curl_init();

        // HTTP headers
        $http_header = array(
            "Authorization: Basic {$params['access_token']}",
            "Content-Type: application/json"
        );

        // curl options
        $curl_opt = array(
            CURLOPT_URL => $params['end_points'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $http_header
        );     
        
        // parameters
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
        $api_id = ( $params['api_id'] != '' )?$params['api_id']:4; // default is Palace

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

            $access_token = $pme_row->access_token;

            return $access_token;

        }        

    }

    public function get_all_palace_property($agency_id) {

      $api_id = 4; // Palace
      $pme_params = array(
          'agency_id' => $agency_id,
          'api_id' => $api_id
      );
      $access_token = $this->getAccessToken($pme_params);
      $system_use = $this->get_system_use_url($agency_id);
      $end_points = $system_use."/Service.svc/RestService/ViewAllDetailedProperty";

      // call end point
      $pme_params = array(
          'access_token' => $access_token,
          'end_points' => $end_points
      );
      $palaceList = $this->call_end_points($pme_params);
      $propList = $palaceList['ViewAllDetailedProperty'];

      $resArr = array();
      foreach ($propList as $key => $value) {
          if ($value->PropertyArchived == 'false') {
              array_push($resArr, $propList[$key]);
          }
      }

      return $resArr;

    }

    public function get_all_property_by_prop_code($params) {

        $agency_id = $params['agency_id'];

        $api_id = 4; // Palace
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);
        $system_use = $this->get_system_use_url($agency_id);
        $end_points = $system_use."/Service.svc/RestService/ViewAllDetailedProperty";

        // call end point
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        $tenantList = $this->call_end_points($pme_params);
        $tenantList = $tenantList['ViewAllDetailedProperty'];

        $resArr = array();
        foreach ($tenantList as $key => $value) {
            if ($value->PropertyCode == $params['palace_id']) {
                array_push($resArr, $tenantList[$key]);
                break;
            }
        }
        return $resArr;
    }

    public function get_all_tenant_by_prop_code($params) {

        $agency_id = $params['agency_id'];

        $api_id = 4; // Palace
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);
        $system_use = $this->get_system_use_url($agency_id);
        $end_points = $system_use."/Service.svc/RestService/ViewAllDetailedTenancy";

        // call end point
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        $tenantList = $this->call_end_points($pme_params);
        $tenantList = $tenantList['ViewAllDetailedTenancy'];

        $resArr = array();
        foreach ($tenantList as $key => $value) {
            if ($value->PropertyCode == $params['palace_id']) {
                if ($value->TenancyArchived == 'false') {
                    array_push($resArr, $tenantList[$key]);
                }
            }
        }
        return $resArr;
    }


    public function get_all_tenant_by_prop_code_v2($params) {

        $agency_id = $params['agency_id'];
        $palace_prop_id = $params['palace_prop_id'];

        // get access token
        $api_id = 4; // Palace
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);
        $system_use = $this->get_system_use_url($agency_id);        
        
        $end_points = "{$system_use}/Service.svc/RestService/v2ViewAllDetailedTenancyByProperty/JSON/{$palace_prop_id}";

        // call end point
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        $tenant_json = $this->get_end_points($pme_params);        

        $tenant_json_decoded = json_decode($tenant_json);
        return $tenant_json_decoded[0]->TenancyTenants;

        //print_r($tenant_json_decoded[0]->TenancyTenants);

    }

    public function get_tenants_by_property($params) {

        $agency_id = $params['agency_id'];
        $palace_prop_id = $params['palace_prop_id'];

        // get access token
        $api_id = 4; // Palace
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);
        $system_use = $this->get_system_use_url($agency_id);        
        
        $end_points = "{$system_use}/Service.svc/RestService/v2ViewAllDetailedTenancyByProperty/JSON/{$palace_prop_id}";

        // call end point
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        $tenant_json = $this->get_end_points($pme_params);        

        return json_decode($tenant_json);

    }

    public function get_all_palace_owner($agency_id) {

        $api_id = 4; // Palace
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);
        $system_use = $this->get_system_use_url($agency_id);
        $end_points = $system_use."/Service.svc/RestService/ViewAllDetailedOwner";

        // call end point
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );

        $palaceList = $this->call_end_points($pme_params);

        return $palaceList['ViewAllDetailedOwner'];
    }

    public function get_all_palace_supplier($agency_id) {

      $api_id = 4; // Palace
      $pme_params = array(
          'agency_id' => $agency_id,
          'api_id' => $api_id
      );
      $access_token = $this->getAccessToken($pme_params);
      $system_use = $this->get_system_use_url($agency_id);
      $end_points = $system_use."/Service.svc/RestService/v2ViewAllDetailedSupplier/JSON";

      // call end point
      $pme_params = array(
          'access_token' => $access_token,
          'end_points' => $end_points
      );
      $palaceList = $this->call_end_points($pme_params, true);

      $suppList = json_decode($palaceList);

      $resArr = array();
      foreach ($suppList as $key => $value) {
          if ($value->SupplierArchived == false) {
              array_push($resArr, $suppList[$key]);
          }
      }

      return $resArr;

    }

    public function get_all_palace_agent($agency_id) {

      $api_id = 4; // Palace
      $pme_params = array(
          'agency_id' => $agency_id,
          'api_id' => $api_id
      );
      $access_token = $this->getAccessToken($pme_params);
      $system_use = $this->get_system_use_url($agency_id);
      $end_points = $system_use."/Service.svc/RestService/v2ViewAllDetailedAgent/JSON";

      // call end point
      $pme_params = array(
          'access_token' => $access_token,
          'end_points' => $end_points
      );
      $palaceList = $this->call_end_points($pme_params, true);

      $suppList = json_decode($palaceList);

      $resArr = array();
      foreach ($suppList as $key => $value) {
          if ($value->AgentArchived == false) {
              array_push($resArr, $suppList[$key]);
          }
      }

      return $resArr;

    }

    public function get_palace_supplier_by_id($agency_id, $code) {

      $api_id = 4; // Palace
      $pme_params = array(
          'agency_id' => $agency_id,
          'api_id' => $api_id
      );
      $access_token = $this->getAccessToken($pme_params);
      $system_use = $this->get_system_use_url($agency_id);
      $end_points = $system_use."/Service.svc/RestService/v2ViewAllDetailedSupplier/JSON";

      // call end point
      $pme_params = array(
          'access_token' => $access_token,
          'end_points' => $end_points
      );
      $palaceList = $this->call_end_points($pme_params, true);

      $suppList = json_decode($palaceList);

      $resArr = array();
      foreach ($suppList as $key => $value) {
          if ($value->SupplierCode == $code) {
              array_push($resArr, $suppList[$key]);
          }
      }

      return $resArr;

    }

    public function get_palace_agent_by_id($agency_id, $code) {

      $api_id = 4; // Palace
      $pme_params = array(
          'agency_id' => $agency_id,
          'api_id' => $api_id
      );
      $access_token = $this->getAccessToken($pme_params);
      $system_use = $this->get_system_use_url($agency_id);
      $end_points = $system_use."/Service.svc/RestService/v2ViewAllDetailedAgent/JSON";

      // call end point
      $pme_params = array(
          'access_token' => $access_token,
          'end_points' => $end_points
      );
      $palaceList = $this->call_end_points($pme_params, true);

      $suppList = json_decode($palaceList);

      $resArr = array();
      foreach ($suppList as $key => $value) {
          if ($value->AgentCode == $code) {
              array_push($resArr, $suppList[$key]);
          }
      }

      return $resArr;

    }

    public function send_all_pdf_to_palace($agencyId, $selected, $file, $fileName, $job_details) {

        $country_id = $this->config->item('country');

        $job_id = $job_details['id'];

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $invoice_number = "{$job_id}{$check_digit}"; 

        $system_use = $this->get_system_use_url($agencyId);
        $end_points = $system_use."/Service.svc/RestService/v2PropertyDiaryInvoiceFileValidated/JSON";

        // get access token
        $pme_params = array(
            'agency_id' => $agencyId,
            'api_id' => 4
        );
        $access_token = $this->getAccessToken($pme_params);

        // get agency name to fill invoice account number insctructed by Ben T.
        $agency_name = '';
        if( $agencyId > 0 ){
                
            $agency_name_sql = $this->db->query("
            SELECT `agency_name`
            FROM `agency`
            WHERE `agency_id` = {$agencyId}
            ");
            $agency_row = $agency_name_sql->row();
            $agency_name = $agency_row->agency_name;

        }

        // HTTP headers
        $http_header = array(
            "Authorization: Basic {$access_token}",
            "Content-Type: application/json"
        );

        $temp = tmpfile();
        fwrite($temp, $file);
        $path = stream_get_meta_data($temp)['uri'];

        // change it to $file['tmp_name'] to $path only when live
        $encode = base64_encode(file_get_contents($file['tmp_name']));

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $end_points,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>"{\r\n  \"newPropertyDiaryInvoiceFile\": {\r\n    \"DiaryInvoiceAccountNumber\": \"{$agency_name}\",\r\n    \"DiaryInvoiceCustomDescription01\": \"Fixed Charge\",\r\n    \"DiaryInvoiceCustomDescription02\": \"Description\",\r\n    \"DiaryInvoiceCustomDescription03\": \"Property Code\",\r\n    \"DiaryInvoiceCustomDescription04\": \"Supplier Code\",\r\n    \"DiaryInvoiceCustomDescription05\": \"\",\r\n    \"DiaryInvoiceCustomDescription06\": \"\",\r\n    \"DiaryInvoiceCustomDescription07\": \"\",\r\n    \"DiaryInvoiceCustomDescription08\": \"\",\r\n    \"DiaryInvoiceCustomDescription09\": \"\",\r\n    \"DiaryInvoiceCustomDescription10\": \"\",\r\n    \"DiaryInvoiceCustomValue01\": \"80.85\",\r\n    \"DiaryInvoiceCustomValue02\": \"Water Rates\",\r\n    \"DiaryInvoiceCustomValue03\": \"RBPR000232\",\r\n    \"DiaryInvoiceCustomValue04\": \"RBCR000343\",\r\n    \"DiaryInvoiceCustomValue05\": \"\",\r\n    \"DiaryInvoiceCustomValue06\": \"\",\r\n    \"DiaryInvoiceCustomValue07\": \"\",\r\n    \"DiaryInvoiceCustomValue08\": \"\",\r\n    \"DiaryInvoiceCustomValue09\": \"\",\r\n    \"DiaryInvoiceCustomValue10\": \"\",\r\n    \"DiaryInvoiceDate\": \"2017-09-15\",\r\n    \"DiaryInvoiceLastMeterReading\": \"1234567\",\r\n    \"DiaryInvoiceLastMeterReadingDate\": \"2017-10-01\",\r\n    \"DiaryInvoicePropertyAddress\": \"10 Johnson Road, Rosedale, Auckland 065\",\r\n    \"DiaryInvoiceReferenceNumber\": \"{$invoice_number}\",\r\n    \"DiaryInvoiceSupplierName\": \"SATS {$job_details['job_type']}\",\r\n    \"DiaryInvoiceSupplierTaxNumber\": \"01-023-0232\",\r\n    \"DiaryInvoiceThisMeterReading\": \"1234567\",\r\n    \"DiaryInvoiceThisMeterReadingDate\": \"2017-10-01\",\r\n    \"DiaryInvoiceTotalAmountIncludingGST\": \"500\",\r\n    \"DiaryInvoiceTotalGSTAmount\": \"60\",\r\n    \"DiaryInvoiceImage\": \"{$encode}\"\r\n  },\r\n  \"newPropertyDiaryInvoiceFileException\": {\r\n    \"DiaryInvoiceFileExceptionNo\": 106,\r\n    \"DiaryInvoiceFileExceptionMessage\": \"The water readings have increased by more than 80%\"\r\n  }\r\n}",
          CURLOPT_HTTPHEADER => $http_header
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        fclose($temp);
        return $response;

    }

    public function send_all_certificates_and_invoices($is_get_data = false) {

         
        $this->load->model('/inc/job_functions_model');
        $this->load->model('/inc/pdf_template');
        $this->load->model('/inc/alarm_functions_model');
        $this->load->model('/inc/functions_model');
        ini_set('max_execution_time', 900); 

        $job_status = "Merged Certificates";
        $country_id = $this->config->item('country');
        $palace_api = 4; // Palace

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
        p.`palace_prop_id`, 
        
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

        apd_palace.`api` AS palace_api,
        apd_palace.`api_prop_id` AS palace_prop_id
        ";

        $custom_where = "
        ( 
            j.`is_palace_invoice_upload` IS NULL AND 
            j.`is_palace_bill_create` IS NULL AND 
            ( j.`client_emailed` IS NULL || j.`client_emailed` = '' ) 
        ) AND 
        (
            apd_palace.`api_prop_id` IS NOT NULL AND 
            apd_palace.`api_prop_id` != '' AND 
            apd_palace.`api` = {$palace_api}
        ) AND 
        (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id <> '') AND 
        (a.palace_diary_id IS NOT NULL AND a.palace_diary_id <> '')  AND
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
                    'join_table' => '`api_property_data` AS apd_palace',
                    'join_on' => "( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )",
                    'join_type' => 'left'
                )

            ),
            
            'custom_where' => $custom_where,
        );
        $pmeQuerySent = $this->get_jobs_with_pme_connect($paramsPmeSent);
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
                if( $val['palace_api'] == $palace_api && $val['palace_prop_id'] != '' ){

                    // optimized moved to 1 function so vjd can use it too
                    $upload_inv_params = array(
                        'palace_prop_id' => $val['palace_prop_id'],
                        'a_id' => $val['a_id'],
                        'jid' => $val['jid'],
                    );
                    $this->upload_invoice($upload_inv_params);

                }                

            }
            
        }
            
        return array("err" => $isFailUpload);

    }

    public function send_all_certificates_and_invoices_via_vjd($job_id_by_vjd = "") {

        if ($job_id_by_vjd == "" || !is_numeric($job_id_by_vjd)) {
            exit();
        }

         
        $this->load->model('/inc/job_functions_model');
        $this->load->model('/inc/pdf_template');
        $this->load->model('/inc/alarm_functions_model');
        $this->load->model('/inc/functions_model');
        ini_set('max_execution_time', 900); 
        
        $country_id = $this->config->item('country');
        $palace_api = 4; // Palace

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
        p.`palace_prop_id`, 
        
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

        apd_palace.`api` AS palace_api,
	    apd_palace.`api_prop_id` AS palace_prop_id
        ";        

        $custom_where = "
        ( 
            apd_palace.`api_prop_id` IS NOT NULL AND 
            apd_palace.`api_prop_id` != '' AND 
            apd_palace.`api` = {$palace_api}
        ) AND 
        ( a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id <> '' ) AND 
        ( a.palace_diary_id IS NOT NULL AND a.palace_diary_id <> '' AND p.`send_to_email_not_api` = 0 ) AND 
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
                    'join_table' => '`api_property_data` AS apd_palace',
                    'join_on' => "( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )",
                    'join_type' => 'left'
                )
    
            ),
            
            'custom_where' => $custom_where,
        );
        $pmeQuerySent = $this->get_jobs_with_pme_connect($paramsPmeSent);
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
            if( $val['palace_api'] == $palace_api && $val['palace_prop_id'] != '' ){

                $upload_inv_params = array(
                    'palace_prop_id' => $val['palace_prop_id'],
                    'a_id' => $val['a_id'],
                    'jid' => $val['jid'],
                );
                $this->upload_invoice($upload_inv_params);

            }            

        }
            
        $ret['status'] = $isFailUpload ? false : true;
        $ret['msg'] = $isFailUpload ? "There is a problem uploading to Palace. Contact developers." : "Successfully Uploaded Invoice/Bill to Palace.";
        return $ret;

    }


    public function send_all_certificates_and_invoices_via_vjd_payload_only($job_id_by_vjd = "") {

        if ($job_id_by_vjd == "" || !is_numeric($job_id_by_vjd)) {
            exit();
        }

         
        $this->load->model('/inc/job_functions_model');
        $this->load->model('/inc/pdf_template');
        $this->load->model('/inc/alarm_functions_model');
        $this->load->model('/inc/functions_model');
        ini_set('max_execution_time', 900); 
        
        $country_id = $this->config->item('country');

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
        p.`palace_prop_id`, 
        
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
        ";        

        $custom_where = "
        ( p.`palace_prop_id` IS NOT NULL AND p.`palace_prop_id` != '') AND 
        ( a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id <> '' ) AND 
        ( a.palace_diary_id IS NOT NULL AND a.palace_diary_id <> '' AND p.`send_to_email_not_api` = 0 ) AND 
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
            
            'custom_where' => $custom_where,
        );
        $pmeQuerySent = $this->get_jobs_with_pme_connect($paramsPmeSent);
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
            $upload_inv_params = array(
                'palace_prop_id' => $val['palace_prop_id'],
                'a_id' => $val['a_id'],
                'jid' => $val['jid'],
            );
            $this->upload_invoice_payload_only($upload_inv_params);

        }
            
        $ret['status'] = $isFailUpload ? false : true;
        $ret['msg'] = $isFailUpload ? "There is a problem uploading to Palace. Contact developers." : "Successfully Uploaded Invoice/Bill to Palace.";
        return $ret;

    }


    public function upload_invoice($params){

        $palace_prop_id = $params['palace_prop_id'];
        $agencyId = $params['a_id'];
        $job_id = $params['jid'];
        $country_id = $this->config->item('country');
        $isFailUpload = false;
        $random_string = date('YmdHis').rand();

        $this->system_model->updateInvoiceDetails($job_id); ## Run updateInvoiceDetails first

        $job_details = $this->job_functions_model->getJobDetails2($job_id,$query_only = false);

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}"; 

        # Alarm Details
        $alarm_details = $this->alarm_functions_model->getPropertyAlarms($job_id, 1, 0, 2);
        $num_alarms = is_null($alarm_details) ? 0 : sizeof($alarm_details);

        # Property + Agent Details
        $property_details = $this->functions_model->getPropertyAgentDetails($job_details['property_id']);

        // upload on property safety tab, alarm section
        // get system used
        $sel_query = "system_use";
        $this->db->select($sel_query);
        $this->db->from('agency_api_tokens');
        $this->db->where('agency_id', $agencyId);
        $this->db->where('api_id', 4);
        $aat_sql = $this->db->get();
        $aat_row = $aat_sql->row();
        
        // only on liquid systems and do not upload certificate pdf for upfront bill(2)
        if( $aat_row->system_use == 'Liquid' && $job_details['assigned_tech'] != 2 ){

            // certificate pdf
            $pdf_name = 'certificate_' . $bpay_ref_code.'_'.$random_string . '.pdf';
            $certificate_pdf = $this->pdf_template->pdf_certificate_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id);
            
            $palace_params = array(
                'agency_id' => $agencyId,
                'palace_prop_id' => $palace_prop_id,
                'pdf_file' => $certificate_pdf,
                'pdf_name' => $pdf_name,
                'job_details' => $job_details
            );
            $this->upload_pdf_to_property($palace_params);

        }   

        // invoice pdf
        $invoice_pdf = $this->pdf_template->pdf_invoice_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id);            
        $pdf_name = 'invoice_' . $bpay_ref_code.'_'.$random_string . '.pdf';
        
        // upload pdf
        $res = $this->upload_external_file($agencyId, $palace_prop_id, $invoice_pdf, $pdf_name, $job_details);     
        $this->add_api_logs($job_id, $res['res_string'], true, "v2InsertExternalFile/JSON");
        $this->add_upload_pme_documents($job_id);
            
        
        if ($res['response']) { // Api response no error
            
            // add bill
            $suppRes = $this->add_billing_on_upload_invoice($agencyId, $palace_prop_id, $job_id, $country_id, $job_details, $property_details, $alarm_details, $num_alarms);

            if ( $suppRes['response'] && !empty($suppRes) ) {
                $this->add_api_logs($job_id, $suppRes['res_string'], true, "v2PropertyDiaryInvoiceFileValidated/JSON");
                $this->add_create_pme_bills($job_id);
            }

        }else {

            $isFailUpload = true;
            $this->add_api_logs($job_id, json_encode($res), false, "v2InsertExternalFile/JSON");

        }

        return $isFailUpload;

    }


    public function upload_invoice_payload_only($params){

        $palace_prop_id = $params['palace_prop_id'];
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

        // upload on property safety tab, alarm section
        // get system used
        $sel_query = "system_use";
        $this->db->select($sel_query);
        $this->db->from('agency_api_tokens');
        $this->db->where('agency_id', $agencyId);
        $this->db->where('api_id', 4);
        $aat_sql = $this->db->get();
        $aat_row = $aat_sql->row();
        
        // only on liquid systems and do not upload certificate pdf for upfront bill(2)
        if( $aat_row->system_use == 'Liquid' && $job_details['assigned_tech'] != 2 ){

            // certificate pdf
            $pdf_name = 'certificate_' . $bpay_ref_code.'_'.$random_string . '.pdf';
            $certificate_pdf = $this->pdf_template->pdf_certificate_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id);
            
            $palace_params = array(
                'agency_id' => $agencyId,
                'palace_prop_id' => $palace_prop_id,
                'pdf_file' => $certificate_pdf,
                'pdf_name' => $pdf_name,
                'job_details' => $job_details
            );
            $this->upload_pdf_to_property_payload_only($palace_params);

        }else{

            echo "Only on Liquid Systems and Assigned Tech must not be Upfront Bill<br />";

        }   

        /*
        // invoice pdf
        $invoice_pdf = $this->pdf_template->pdf_invoice_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id);            
        $pdf_name = 'invoice_' . $bpay_ref_code.'_'.$random_string . '.pdf';
        
        // upload pdf
        $res = $this->upload_external_file($agencyId, $palace_prop_id, $invoice_pdf, $pdf_name, $job_details);     
        $this->add_api_logs($job_id, $res['res_string'], true, "v2InsertExternalFile/JSON");
        $this->add_upload_pme_documents($job_id);
            
        
        if ($res['response']) { // Api response no error
            
            // add bill
            $suppRes = $this->add_billing_on_upload_invoice($agencyId, $palace_prop_id, $job_id, $country_id, $job_details, $property_details, $alarm_details, $num_alarms);

            if ( $suppRes['response'] && !empty($suppRes) ) {
                $this->add_api_logs($job_id, $suppRes['res_string'], true, "v2PropertyDiaryInvoiceFileValidated/JSON");
                $this->add_create_pme_bills($job_id);
            }

        }else {

            $isFailUpload = true;
            $this->add_api_logs($job_id, json_encode($res), false, "v2InsertExternalFile/JSON");

        }

        return $isFailUpload;
        */

    }


    public function upload_external_file($agencyId, $selected, $file, $fileName, $job_details) {

        $agentId = $this->get_agent_id_by_agency_id($agencyId);
        $agentId = $agentId->palace_agent_id;

        $diaryCode = $this->get_diary_id_by_agency_id($agencyId);
        $diaryCode = $diaryCode->palace_diary_id;

        if (is_null($agentId) || is_null($diaryCode)) {
            return array("errNo" => 0, "response" => false, "res_string" => "No agent ID connected.");
        }

        $job_id = $job_details['id'];

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}"; 

        $system_use = $this->get_system_use_url($agencyId);
        $end_points = $system_use."/Service.svc/RestService/v2InsertExternalFile/JSON";

        // get access token
        $pme_params = array(
            'agency_id' => $agencyId,
            'api_id' => 4
        );
        $access_token = $this->getAccessToken($pme_params);

        // HTTP headers
        $http_header = array(
            "Authorization: Basic {$access_token}",
            "Content-Type: application/json"
        );

        $temp = tmpfile();
        fwrite($temp, $file);
        $path = stream_get_meta_data($temp)['uri'];

        $encode = base64_encode(file_get_contents($path));

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $end_points,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>"{\r\n  \"DiaryType\": \"property\",\r\n  \"DiaryTypeCode\": \"{$selected}\",\r\n  \"DiaryFileExtension\": \"PDF\",\r\n  \"DiaryDescription\": \"Smoke Alarm Testing Services\",\r\n  \"DiaryAgentCode\": \"{$agentId}\",\r\n  \"DiaryGroupCode\": \"{$diaryCode}\",\r\n  \"DiaryOnline\": \"True\",\r\n  \"DiaryImage\": \"{$encode}\"\r\n}",
          CURLOPT_HTTPHEADER => $http_header,
        ));

        $response = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errNo = curl_errno($curl);
        $errStr = curl_error($curl);
        curl_close($curl);
        fclose($temp);

        $totalRes = false;
        $word = "Diary Insert (Successful)";
        if(strpos($response, $word) !== false){

            $totalRes = true;            

        }

        return array("errNo" => $errNo, "response" => $totalRes, "res_string" => $response);
    }


    public function test_palace_upload($job_id) {
	
        $country_id = $this->config->item('country');
        
        $job_details = $this->job_functions_model->getJobDetails2($job_id,$query_only = false);
        
        $property_id = $job_details['property_id'];
        $agency_id = $job_details['agency_id'];
        $palace_prop_id = $job_details['palace_prop_id'];
        
        if( $agency_id > 0 ){
    
            // diary
            echo "DIARY: <br /> <br />";
            $agentId = $this->get_agent_id_by_agency_id($agency_id);
            $agentId = $agentId->palace_agent_id;
    
            $diaryCode = $this->get_diary_id_by_agency_id($agency_id);
            $diaryCode = $diaryCode->palace_diary_id;
    
            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));            
            $invoice_number = "{$job_id}{$check_digit}"; 
    
            $system_use = $this->get_system_use_url($agency_id);
            echo "API endpoint<br />";
            echo $end_points = $system_use."/Service.svc/RestService/v2InsertExternalFile/JSON";
            echo "<br /><br />";
    
            // get access token
            $pme_params = array(
                'agency_id' => $agency_id,
                'api_id' => 4
            );
            $access_token = $this->getAccessToken($pme_params);
    
            // HTTP headers
            $http_header = array(
                "Authorization: Basic {$access_token}",
                "Content-Type: application/json"
            );
            
            # Alarm Details
            $alarm_details = $this->alarm_functions_model->getPropertyAlarms($job_id, 1, 0, 2);
            $num_alarms = is_null($alarm_details) ? 0 : sizeof($alarm_details);
    
            # Property + Agent Details
            $property_details = $this->functions_model->getPropertyAgentDetails($property_id);
            
            
            $invoice_pdf = $this->pdf_template->pdf_invoice_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id);
    
            $temp = tmpfile();
            fwrite($temp, $invoice_pdf);
            $path = stream_get_meta_data($temp)['uri'];
    
            $encode = base64_encode(file_get_contents($path));
    
            $curl = curl_init();

            echo "API params<br />";
            echo $api_params = "{\r\n  \"DiaryType\": \"property\",\r\n  \"DiaryTypeCode\": \"{$palace_prop_id}\",\r\n  \"DiaryFileExtension\": \"PDF\",\r\n  \"DiaryDescription\": \"Smoke Alarm Testing Services\",\r\n  \"DiaryAgentCode\": \"{$agentId}\",\r\n  \"DiaryGroupCode\": \"{$diaryCode}\",\r\n  \"DiaryOnline\": \"True\",\r\n  \"DiaryImage\": \"{$encode}\"\r\n}";
            echo "<br /><br />";

            curl_setopt_array($curl, array(
              CURLOPT_URL => $end_points,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $api_params,
              CURLOPT_HTTPHEADER => $http_header,
            ));
    
            $response = curl_exec($curl);
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errNo = curl_errno($curl);
            $errStr = curl_error($curl);
            curl_close($curl);
            fclose($temp);
            
            echo "returned data from API:<br />";
            print_r($response);
            echo "<br /><br /><br /><br />
            <hr>";






            // add document workflow
            echo "DOCUMENT FLOW: <br /> <br />";
            $getTotalAmount = $this->system_model->getJobAmountGrandTotal($job_id, $country_id);

            if($country_id==1){
                $gst = $getTotalAmount / 11;
            }else if($country_id==2){
                $gst = ($getTotalAmount*3) / 23;
            }   
            $gst = number_format($gst, 2);
    
            $suppId = $this->get_supplier_id_by_agency_id($agency_id);
            $suppId = $suppId->palace_supplier_id;
    
            $agentId = $this->get_agent_id_by_agency_id($agency_id);
            $agentId = $agentId->palace_agent_id;

            // get agency name to fill invoice account number insctructed by Ben T.
            $agency_name = '';
            if( $agency_id > 0 ){
                
                $agency_name_sql = $this->db->query("
                SELECT `agency_name`
                FROM `agency`
                WHERE `agency_id` = {$agency_id}
                ");
                $agency_row = $agency_name_sql->row();
                $agency_name = $agency_row->agency_name;

            }
           
    
            if (!is_null($suppId) && !is_null($agentId) && floatval($getTotalAmount) > 0) {
    
                $system_use = $this->get_system_use_url($agency_id);
                echo "API endpoint<br />";
                echo $end_points = $system_use."/Service.svc/RestService/v2PropertyDiaryInvoiceFileValidated/JSON";
                echo "<br /><br />";
                    
                $prop_address = $property_details['address_1']." ".$property_details['address_2'].", ".$property_details['address_3'];    
    
                $formDate = $this->system_model->formatDate($job_details['date'],'Y-m-d');              
        
                $temp = tmpfile();
                fwrite($temp, $invoice_pdf);
                $path = stream_get_meta_data($temp)['uri'];    
                $encode = base64_encode(file_get_contents($path));
    
                $curl = curl_init();

                echo "API params<br />";
                echo $api_params = "{
                    \r\n  \"newPropertyDiaryInvoiceFile\": {
                        \r\n    \"DiaryInvoiceAccountNumber\": \"{$agency_name}\",
                        \r\n    \"DiaryInvoiceCustomDescription01\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription02\": \"Description\",
                        \r\n    \"DiaryInvoiceCustomDescription03\": \"Property Code\",
                        \r\n    \"DiaryInvoiceCustomDescription04\": \"Supplier Code\",
                        \r\n    \"DiaryInvoiceCustomDescription05\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription06\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription07\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription08\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription09\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription10\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue01\": \"0\",
                        \r\n    \"DiaryInvoiceCustomValue02\": \"Smoke Alarm Testing Services\",
                        \r\n    \"DiaryInvoiceCustomValue03\": \"{$palace_prop_id}\",
                        \r\n    \"DiaryInvoiceCustomValue04\": \"{$suppId}\",
                        \r\n    \"DiaryInvoiceCustomValue05\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue06\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue07\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue08\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue09\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue10\": \"\",
                        \r\n    \"DiaryInvoiceDate\": \"{$formDate}\",
                        \r\n    \"DiaryInvoiceLastMeterReading\": \"\",
                        \r\n    \"DiaryInvoiceLastMeterReadingDate\": \"\",
                        \r\n    \"DiaryInvoicePropertyAddress\": \"{$prop_address}\",
                        \r\n    \"DiaryInvoiceReferenceNumber\": \"{$invoice_number}\",
                        \r\n    \"DiaryInvoiceSupplierName\": \"SATS\",
                        \r\n    \"DiaryInvoiceSupplierTaxNumber\": \"\",
                        \r\n    \"DiaryInvoiceThisMeterReading\": \"\",
                        \r\n    \"DiaryInvoiceThisMeterReadingDate\": \"\",
                        \r\n    \"DiaryInvoiceTotalAmountIncludingGST\": \"{$getTotalAmount}\",
                        \r\n    \"DiaryInvoiceTotalGSTAmount\": \"{$gst}\",
                        \r\n    \"DiaryInvoiceImage\": \"{$encode}\"
                        \r\n  },
                        \r\n  \"newPropertyDiaryInvoiceFileException\": {
                            \r\n    \"DiaryInvoiceFileExceptionNo\": 0,
                        \r\n    \"DiaryInvoiceFileExceptionMessage\": \"\"
                        \r\n  }
                        \r\n}";
                echo "<br /><br />";
    
                curl_setopt_array($curl, array(
                  CURLOPT_URL => $end_points,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "POST",
                  CURLOPT_POSTFIELDS => $api_params,
                  CURLOPT_HTTPHEADER => $http_header,
                ));
    
                $response = curl_exec($curl);
                $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $errNo_curl = curl_errno($curl);
                $errStr = curl_error($curl);
                curl_close($curl);
                fclose($temp);
    
                $totalRes = false;
                $word = "Process Property Diary Invoice File Validated (Successful)";
                if(strpos($response, $word) !== false){
                  $totalRes = true;
                }


                echo "returned data from API:<br />";
                print_r($response);
                echo "<br /><br /><br /><br />
                <hr>";
				
							
            }


            echo "H&S certficate upload: <br /> <br />";
            $pdf_name = 'certificate_' . $invoice_number . '.pdf';
            $pdf_file = $this->pdf_template->pdf_certificate_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id);

            echo "API endpoint<br />";
            echo $end_points = $system_use."/Service.svc/RestService/v2InsertPropertyHealthyHomesSafetyFile/JSON";   
            echo "<br /><br />";     
        
            // encode document string to base64
            $base64_encode = base64_encode($pdf_file);        
            
            // API params
            $param_data = array(
                "HealthyHomesSafetyPropertyCode" => $palace_prop_id,
                "HealthyHomesSafetyNameFileType" => 'SafetySmokeAlarmsCurrentFile',
                "HealthyHomesSafetyNameData" => $base64_encode,
                "HealthyHomesSafetyNameFile" => $pdf_name
            ); 

            echo "API params<br />"; 
            print_r($param_data);
            echo "<br /><br />";
            
            // call API endpoint
            $palace_params = array(
                'access_token' => $access_token,
                'end_points' => $end_points,
                'param_data' => $param_data
            );        
            $response =  $this->get_end_points($palace_params);
        
            echo "returned data from API:<br />";
            print_r($response);
            
            
        }
        
    }



    public function upload_pdf_to_property($params) {

        // variables
        $agency_id = $params['agency_id'];
        $palace_prop_id = $params['palace_prop_id'];
        $pdf_file = $params['pdf_file']; 
        $pdf_name = $params['pdf_name'];
        $job_details = $params['job_details'];

        // job ID
        $job_id = $job_details['id'];

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}"; 

        // get system used url
        $system_use = $this->get_system_use_url($agency_id);
        $end_points = $system_use."/Service.svc/RestService/v2InsertPropertyHealthyHomesSafetyFile/JSON";        
       
        // encode document string to base64
        $base64_encode = base64_encode($pdf_file);        
        
        // API params
        $param_data = array(
            "HealthyHomesSafetyPropertyCode" => $palace_prop_id,
            "HealthyHomesSafetyNameFileType" => 'SafetySmokeAlarmsCurrentFile',
            "HealthyHomesSafetyNameData" => $base64_encode,
            "HealthyHomesSafetyNameFile" => $pdf_name
        );  

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => 4
        );
        $access_token = $this->getAccessToken($pme_params);
        
        // call API endpoint
        $palace_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points,
            'param_data' => $param_data
        );        
        $response =  $this->get_end_points($palace_params);
        
        // insert job log
        $encrypt = rawurlencode($this->encryption_model->encrypt($job_id));
        $baseUrl = $_SERVER["SERVER_NAME"];
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else{
            $protocol = 'http';
        }

        // lod details
        $log_details = "<a href='".$protocol."://{$baseUrl}/pdf/view_certificate/?job_id={$encrypt}'>Certificate</a>, #{$bpay_ref_code} to the linked Palace property";
        $log_params = array(
            'title' => 70,  // Palace API
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


        
        // update healthy home and safety
        $end_points = $system_use."/Service.svc/RestService/v2UpdatePropertyHealthyHomesSafety/JSON";        
        
        // get tech
        $tech_name = "{$job_details['FirstName']} {$job_details['LastName']}";
        
        // get alarms
        $alarm_sql = $this->db->query("
        SELECT 
            `make`,
            `expiry`,            
            `ts_position`,
            `new`
        FROM `alarm` 
        WHERE `job_id` = {$job_id}
        AND `ts_discarded` = 0
        ");

        $alarms_arr = []; // clear
        foreach( $alarm_sql->result() as $alarm_row ){

            $alarm_expiry = date('Y-m-t',strtotime("December {$alarm_row->expiry}"));
            $new_or_ex = ( $alarm_row->new == 1 )?'New':'Existing';

            // add alarms
            $alarms_arr[] = array(
                'PropertySmokeAlarmDescription' => $alarm_row->make,
                'PropertySmokeAlarmExpiryDate' => $alarm_expiry,
                'PropertySmokeAlarmLocation' => $alarm_row->ts_position,
                'PropertySmokeAlarmNewOrExisting' => $new_or_ex
            );
            
        }

        // API params
        $param_data = array(
            'PropertyCode' => $palace_prop_id,
            'Property_Safety_SmokeAlarm' => array(
				'PropertySmokeAlarmLastCheckedBy' => "SATS - {$tech_name}",
				'PropertySmokeAlarmLastCheckedDate' => $job_details['jdate'],
				'PropertySmokeAlarmList' => $alarms_arr
			)
        );   
              
        // call API endpoint
        $palace_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points,
            'param_data' => $param_data
        );        
        $response =  $this->get_end_points($palace_params);        

       
    }


    public function upload_pdf_to_property_payload_only($params) {

        // variables
        $agency_id = $params['agency_id'];
        $palace_prop_id = $params['palace_prop_id'];
        $pdf_file = $params['pdf_file']; 
        $pdf_name = $params['pdf_name'];
        $job_details = $params['job_details'];

        // job ID
        $job_id = $job_details['id'];

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}"; 

        // get system used url
        $system_use = $this->get_system_use_url($agency_id);
        $end_points = $system_use."/Service.svc/RestService/v2InsertPropertyHealthyHomesSafetyFile/JSON";        
       
        // encode document string to base64
        $base64_encode = base64_encode($pdf_file);        
        
        // API params
        $param_data = array(
            "HealthyHomesSafetyPropertyCode" => $palace_prop_id,
            "HealthyHomesSafetyNameFileType" => 'SafetySmokeAlarmsCurrentFile',
            "HealthyHomesSafetyNameData" => $base64_encode,
            "HealthyHomesSafetyNameFile" => $pdf_name
        );  

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => 4
        );
        $access_token = $this->getAccessToken($pme_params);
        
        /*
        // call API endpoint
        $palace_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points,
            'param_data' => $param_data
        );        
        $response =  $this->get_end_points($palace_params);
        
        // insert job log
        $encrypt = rawurlencode($this->encryption_model->encrypt($job_id));
        $baseUrl = $_SERVER["SERVER_NAME"];
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else{
            $protocol = 'http';
        }

        // lod details
        $log_details = "<a href='".$protocol."://{$baseUrl}/pdf/view_certificate/?job_id={$encrypt}'>Certificate</a>, #{$bpay_ref_code} to the linked Palace property";
        $log_params = array(
            'title' => 70,  // Palace API
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
        */


        
        // update healthy home and safety
        $end_points = $system_use."/Service.svc/RestService/v2UpdatePropertyHealthyHomesSafety/JSON";        
        
        // get tech
        $tech_name = "{$job_details['FirstName']} {$job_details['LastName']}";
        
        // get alarms
        $alarm_sql = $this->db->query("
        SELECT 
            `make`,
            `expiry`,            
            `ts_position`,
            `new`
        FROM `alarm` 
        WHERE `job_id` = {$job_id}
        AND `ts_discarded` = 0
        ");

        $alarms_arr = []; // clear
        foreach( $alarm_sql->result() as $alarm_row ){

            $alarm_expiry = date('Y-m-t',strtotime("December {$alarm_row->expiry}"));
            $new_or_ex = ( $alarm_row->new == 1 )?'New':'Existing';

            // add alarms
            $alarms_arr[] = array(
                'PropertySmokeAlarmDescription' => $alarm_row->make,
                'PropertySmokeAlarmExpiryDate' => $alarm_expiry,
                'PropertySmokeAlarmLocation' => $alarm_row->ts_position,
                'PropertySmokeAlarmNewOrExisting' => $new_or_ex
            );
            
        }

        // API params
        $param_data = array(
            'PropertyCode' => $palace_prop_id,
            'Property_Safety_SmokeAlarm' => array(
				'PropertySmokeAlarmLastCheckedBy' => "SATS - {$tech_name}",
				'PropertySmokeAlarmLastCheckedDate' => $job_details['jdate'],
				'PropertySmokeAlarmList' => $alarms_arr
			)
        );   
              
        // call API endpoint
        $palace_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points,
            'param_data' => $param_data
        );        
        $response =  $this->get_end_points($palace_params);        

        echo "<pre>";
        print_r($param_data);
        echo "</pre>";
       
    }



    public function add_upload_pme_documents($jobId) {
        $ret = $this->db->query("UPDATE jobs SET is_palace_invoice_upload = 1 WHERE `id` = {$jobId}");
        return $ret;
    }    

    public function add_create_pme_bills($jobId) {
        $ret = $this->db->query("UPDATE jobs SET is_palace_bill_create = 1 WHERE `id` = {$jobId}");
        return $ret;
    }

    public function add_billing_on_upload_invoice($agencyId, $pmePropId, $jobId, $countryId, $job_details, $property_details, $alarm_details, $num_alarms) {


        if( $property_details['send_to_email_not_api'] == 1 ){ // send invoce to email instead of API

            // send invoice through email
            $job_params = array(
                'job_id' => $jobId
            );
            $this->email_functions_model->send_invoice_email($job_params);

        }else{ // create diary for invoice document worflow

            $getTotalAmount = $this->system_model->getJobAmountGrandTotal($jobId, $countryId);

            if($countryId==1){
                $gst = $getTotalAmount / 11;
            }else if($countryId==2){
                $gst = ($getTotalAmount*3) / 23;
            }   
            $gst = number_format($gst, 2);
    
            $suppId = $this->get_supplier_id_by_agency_id($agencyId);
            $suppId = $suppId->palace_supplier_id;
    
            $agentId = $this->get_agent_id_by_agency_id($agencyId);
            $agentId = $agentId->palace_agent_id;

            // get agency name to fill invoice account number insctructed by Ben T.
            $agency_name = '';
            if( $agencyId > 0 ){
                
                $agency_name_sql = $this->db->query("
                SELECT `agency_name`
                FROM `agency`
                WHERE `agency_id` = {$agencyId}
                ");
                $agency_row = $agency_name_sql->row();
                $agency_name = $agency_row->agency_name;

            }
           
    
            if (!is_null($suppId) && !is_null($agentId) && floatval($getTotalAmount) > 0) {
    
                $system_use = $this->get_system_use_url($agencyId);
                $end_points = $system_use."/Service.svc/RestService/v2PropertyDiaryInvoiceFileValidated/JSON";
    
                // get access token
                $pme_params = array(
                    'agency_id' => $agencyId,
                    'api_id' => 4
                );
                $access_token = $this->getAccessToken($pme_params);
    
                // HTTP headers
                $http_header = array(
                    "Authorization: Basic {$access_token}",
                    "Content-Type: application/json"
                );
                
                $prop_address = $property_details['address_1']." ".$property_details['address_2'].", ".$property_details['address_3'];
    
                $check_digit = $this->gherxlib->getCheckDigit(trim($jobId));
                $invoice_number = "{$jobId}{$check_digit}"; 
    
                $formDate = $this->system_model->formatDate($job_details['date'],'Y-m-d');
                $newDate = strtotime ('30 day', strtotime ($formDate));
                $dueDate = date('m/d/Y' , $newDate);
    
                $invoice_pdf = $this->pdf_template->pdf_combined_template($jobId, $job_details, $property_details, $alarm_details, $num_alarms, $countryId);
    
                $fileName = 'invoice' . $invoice_number . '.pdf';
    
                $temp = tmpfile();
                fwrite($temp, $invoice_pdf);
                $path = stream_get_meta_data($temp)['uri'];
    
                $bpay_code = "";
                if ($job_details['display_bpay'] && $countryId == 1) {
                    $bpay_code = '264291';
                }
    
                $encode = base64_encode(file_get_contents($path));
    
                $curl = curl_init();
    
                curl_setopt_array($curl, array(
                  CURLOPT_URL => $end_points,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "POST",
                  CURLOPT_POSTFIELDS =>"{
                    \r\n  \"newPropertyDiaryInvoiceFile\": {
                        \r\n    \"DiaryInvoiceAccountNumber\": \"{$agency_name}\",
                        \r\n    \"DiaryInvoiceCustomDescription01\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription02\": \"Description\",
                        \r\n    \"DiaryInvoiceCustomDescription03\": \"Property Code\",
                        \r\n    \"DiaryInvoiceCustomDescription04\": \"Supplier Code\",
                        \r\n    \"DiaryInvoiceCustomDescription05\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription06\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription07\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription08\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription09\": \"\",
                        \r\n    \"DiaryInvoiceCustomDescription10\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue01\": \"0\",
                        \r\n    \"DiaryInvoiceCustomValue02\": \"Smoke Alarm Testing Services\",
                        \r\n    \"DiaryInvoiceCustomValue03\": \"{$pmePropId}\",
                        \r\n    \"DiaryInvoiceCustomValue04\": \"{$suppId}\",
                        \r\n    \"DiaryInvoiceCustomValue05\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue06\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue07\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue08\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue09\": \"\",
                        \r\n    \"DiaryInvoiceCustomValue10\": \"\",
                        \r\n    \"DiaryInvoiceDate\": \"{$formDate}\",
                        \r\n    \"DiaryInvoiceLastMeterReading\": \"\",
                        \r\n    \"DiaryInvoiceLastMeterReadingDate\": \"\",
                        \r\n    \"DiaryInvoicePropertyAddress\": \"{$prop_address}\",
                        \r\n    \"DiaryInvoiceReferenceNumber\": \"{$invoice_number}\",
                        \r\n    \"DiaryInvoiceSupplierName\": \"SATS {$job_details['job_type']}\",
                        \r\n    \"DiaryInvoiceSupplierTaxNumber\": \"\",
                        \r\n    \"DiaryInvoiceThisMeterReading\": \"\",
                        \r\n    \"DiaryInvoiceThisMeterReadingDate\": \"\",
                        \r\n    \"DiaryInvoiceTotalAmountIncludingGST\": \"{$getTotalAmount}\",
                        \r\n    \"DiaryInvoiceTotalGSTAmount\": \"{$gst}\",
                        \r\n    \"DiaryInvoiceImage\": \"{$encode}\"
                        \r\n  },
                        \r\n  \"newPropertyDiaryInvoiceFileException\": {
                            \r\n    \"DiaryInvoiceFileExceptionNo\": 0,
                        \r\n    \"DiaryInvoiceFileExceptionMessage\": \"\"
                        \r\n  }
                        \r\n}",
                  CURLOPT_HTTPHEADER => $http_header,
                ));
    
                $response = curl_exec($curl);
                $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $errNo_curl = curl_errno($curl);
                $errStr = curl_error($curl);
                curl_close($curl);
                fclose($temp);
    
                $totalRes = false;
                $word = "Process Property Diary Invoice File Validated (Successful)";
                if(strpos($response, $word) !== false){
                  $totalRes = true;
                }
    
                // job log1
                $encrypt = rawurlencode($this->encryption_model->encrypt($jobId));
                $baseUrl = $_SERVER["SERVER_NAME"];
                if(isset($_SERVER['HTTPS'])){
                    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
                } else{
                    $protocol = 'http';
                }
                
                $getTotalAmount = number_format($getTotalAmount,2);
                $log_details = "<a href='".$protocol."://{$baseUrl}/pdf/view_invoice/?job_id={$encrypt}'>Invoice</a>, #{$invoice_number} uploaded to Palace Agency as a bill of {$getTotalAmount}";
                $log_params = array(
                    'title' => 70,  // Palace API
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
    
                $errNo = array("errNo" => $errNo_curl, "response" => $totalRes, "res_string" => $response);
            }else {
                $errNo = array("errNo" => 0, "response" => false, "res_string" => "No agent ID connected.");
            }

        }     
        
        return $errNo;

    }

    public function add_api_logs($jobId, $apiResponse, $status, $apiUrl = "") {
        $data = array(
            'agency_api_id' => 4, //Palace Logs
            'job_id' => $jobId,
            'api_response' => $apiResponse,
            'status' => $status,
            'api_url' => $apiUrl,
            'date_created' => date('Y-m-d H:i:s')
        );
        $this->db->insert('agency_api_logs', $data);
    }

    public function get_supplier_id_by_agency_id($agencyId) {
        $q_supp = "
            SELECT 
                `palace_supplier_id`
            FROM `agency`
            WHERE `agency_id` = {$agencyId} 
        ";
        $get_supp_id = $this->db->query($q_supp);
        $id = $get_supp_id->row();

        return $id;
    }

    public function get_agent_id_by_agency_id($agencyId) {
        $q_supp = "
            SELECT 
                `palace_agent_id`
            FROM `agency`
            WHERE `agency_id` = {$agencyId} 
        ";
        $get_agent_id = $this->db->query($q_supp);
        $id = $get_agent_id->row();

        return $id;
    }

    public function get_all_palace_diary($agency_id) {

      $api_id = 4; // Palace
      $pme_params = array(
          'agency_id' => $agency_id,
          'api_id' => $api_id
      );
      $access_token = $this->getAccessToken($pme_params);
      $system_use = $this->get_system_use_url($agency_id);
      $end_points = $system_use."/Service.svc/RestService/v2ViewAllDetailedDiaryGroup/JSON";

      // call end point
      $pme_params = array(
          'access_token' => $access_token,
          'end_points' => $end_points
      );
      $diaryList = $this->call_end_points($pme_params, true);

      $diaryList = json_decode($diaryList);

      $resArr = array();
      foreach ($diaryList as $key => $value) {
          array_push($resArr, $diaryList[$key]);
      }

      return $resArr;

    }

    public function get_palace_diary_by_id($agency_id, $code) {

      $api_id = 4; // Palace
      $pme_params = array(
          'agency_id' => $agency_id,
          'api_id' => $api_id
      );
      $access_token = $this->getAccessToken($pme_params);
      $system_use = $this->get_system_use_url($agency_id);
      $end_points = $system_use."/Service.svc/RestService/v2ViewAllDetailedDiaryGroup/JSON";

      // call end point
      $pme_params = array(
          'access_token' => $access_token,
          'end_points' => $end_points
      );
      $diaryList = $this->call_end_points($pme_params, true);

      $diaryList = json_decode($diaryList);

      $resArr = array();
      foreach ($diaryList as $key => $value) {
          if ($value->DiaryGroupCode == $code) {
              array_push($resArr, $diaryList[$key]);
          }
      }

      return $resArr;

    }

    public function get_diary_id_by_agency_id($agencyId) {
        $q_supp = "
            SELECT 
                `palace_diary_id`
            FROM `agency`
            WHERE `agency_id` = {$agencyId} 
        ";
        $get_diary_id = $this->db->query($q_supp);
        $id = $get_diary_id->row();

        return $id;
    }

    public function get_system_use_url($agencyId) {

        $sel_query = "system_use";
        $this->db->select($sel_query);
        $this->db->from('agency_api_tokens');
        $this->db->where('agency_id', $agencyId);
        $this->db->where('api_id', 4);
        $pme_sql = $this->db->get();
        $pme_row = $pme_sql->row();

        $system = $pme_row->system_use;
        if ($system == "Legacy" || is_null($system)) {
          $basePalace = $this->config->item('palace_api_base_legacy');
        }else {
          $basePalace = $this->config->item('palace_api_base_liquid');
        }
        return $basePalace;
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

    public function get_all_properties($agency_id) {  

        // get access token
        $api_id = 4; // Palace
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);
        $system_use = $this->get_system_use_url($agency_id);        
        
        $end_points = "{$system_use}/Service.svc/RestService/v2ViewAllDetailedProperty/JSON";

        // call end point
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        $tenant_json = $this->get_end_points($pme_params);        

        return json_decode($tenant_json);

    }


    public function get_workorders($agency_id) {  

        // get access token
        $api_id = 4; // Palace
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);
        $system_use = $this->get_system_use_url($agency_id);        
        
        $end_points = "{$system_use}/Service.svc/RestService/v2ViewAllWorksOrder/JSON";

        // call end point
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        $tenant_json = $this->get_end_points($pme_params);        

        return json_decode($tenant_json);

    }

    public function get_property($params) {

        $agency_id = $params['agency_id'];
        $palace_prop_id = $params['palace_prop_id'];

        // get access token
        $api_id = 4; // Palace
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);
        $system_use = $this->get_system_use_url($agency_id);        
        
        $end_points = "{$system_use}/Service.svc/RestService//v2DetailedProperty/JSON/{$palace_prop_id}";

        // call end point
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        return $this->get_end_points($pme_params);        
        
    }
    
}
