<?php
class Console_model extends CI_Model {

    private $api_gateway;
    private $client_id;
    private $secret;
    private $creditor_id;
    private $display_http_status_code;

	public function __construct(){
        $this->load->database();
        
        if( ENVIRONMENT == 'production' ){ // live
            
            if( $this->config->item('country') == 1 ){ // AU
                
                $this->api_gateway = 'https://api.console.com.au';
                $this->client_id = 'partner_sats';
                $this->secret = 'mZiUl5IUE2hCha49NSNL14MhABbm9M';
                $this->creditor_id = 'e8cfeb6c-5334-4c39-8d65-de39a7859281';  

            }else if( $this->config->item('country') == 2 ){ // NZ
                
                $this->api_gateway = 'https://api.console.com.au';
                $this->client_id = 'partner_sats_nz';
                $this->secret = 'fvDUAh0UPQ8wa9cE4383Ke6ZBmKKxcjr';
                $this->creditor_id = 'e8cfeb6c-5334-4c39-8d65-de39a7859281';  

            }              
    
        }else{ // dev

            // sandbox test
            $this->api_gateway = 'https://sandbox-apigw.saas-uat.console.com.au';
            $this->client_id = 'partner_sats';
            $this->secret = 'password';
            $this->creditor_id = 'e8cfeb6c-5334-4c39-8d65-de39a7859281';    

        }
      
        
        $this->display_http_status_code = false;

        $this->load->model('Pme_model');
        $this->load->model('/inc/job_functions_model');
        $this->load->model('/inc/pdf_template');
        $this->load->model('/inc/alarm_functions_model');
        $this->load->model('/inc/functions_model');

    }
	
	public function verify_integration($api_key) {

        if( $api_key != '' ){

            // init curl object        
            $ch = curl_init();

            $token_url = "{$this->api_gateway}/integration/v1/integrations/_verify";
            $client_id = $this->client_id;
            $secret = $this->secret;    

            $authorization = base64_encode("$client_id:$secret");
            $header = array("Authorization: Basic {$authorization}", "API-Key: {$api_key}","Content-Type: application/json");        

            $optArray = array(
                CURLOPT_URL => $token_url,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true
            );

            // apply those options
            curl_setopt_array($ch, $optArray);

            // execute request and get response
            $result = curl_exec($ch);
            $result_json = json_decode($result);
            return json_encode($result_json);

        }                

    }	


    // UUID generator
    // source: https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid/15875555#15875555
    function guidv4($data = null)
    {
        assert(strlen($data) == 16);

        $data = $data ?? random_bytes(16);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }   


    public function create_file($params) {

        $api_key = $params['api_key'];
        $file_name = $params['file_name'];
        $uuid = $params['uuid'];

        if( $api_key != '' ){

            // init curl object        
            $ch = curl_init();

            $token_url = "{$this->api_gateway}/storage/v1/files";
            $client_id = $this->client_id;
            $secret = $this->secret;    

            $authorization = base64_encode("$client_id:$secret");
            $header = array("Authorization: Basic {$authorization}", "API-Key: {$api_key}","Content-Type: application/json");                           

            $post_params = array(
                'fileId' => $uuid,
                'fileName' => $file_name
            );

            $optArray = array(
                CURLOPT_URL => $token_url,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($post_params)
            );

            // apply those options
            curl_setopt_array($ch, $optArray);            

            // execute request and get response
            $response = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if( $this->display_http_status_code == true ){                
                echo "<p>Create File - HTTP status code: {$httpcode}</p>";                
            }            

            curl_close($ch);           
            
            $result_json = json_decode($response);

            return $result_json;

        }                

    }


    public function create_bill($params) {

        $api_key = $params['api_key'];                
        $file_id = $params['file_id'];
        $console_prop_id = $params['console_prop_id'];
        $invoice_amount = $params['invoice_amount'];
        $date_due = $params['date_due'];
        $invoice_num = $params['invoice_num']; 

        if( $api_key != '' ){

            // init curl object        
            $ch = curl_init();

            $token_url = "{$this->api_gateway}/bill/v1/bills/_create";
            $client_id = $this->client_id;
            $secret = $this->secret;    

            $authorization = base64_encode("$client_id:$secret");
            $header = array("Authorization: Basic {$authorization}", "API-Key: {$api_key}","Content-Type: application/json"); 
            
            // genarate UUID
            $bill_id = $this->guidv4();               

            // create bill
            $post_params = array(
                'billId' => $bill_id,
                'relatedTo' => array(
                    'id' => $console_prop_id,
                    'type' => 'PROPERTY'
                ),
                'payTo' => array(
                    'type' => 'CREDITOR',
                    'externalCreditor' => array(
                        'externalCreditorId' => $this->creditor_id,
                        'mainContact' => array(
                            'businessName' => 'Smoke Alarms and Testing Services'
                        )
                    )
                ),
                'amount' => $invoice_amount,
                'dueDate' => $date_due,
                'invoiceNumber' => $invoice_num,
                'invoiceFileId' => $file_id
            );

            $optArray = array(
                CURLOPT_URL => $token_url,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($post_params)
            );

            // apply those options
            curl_setopt_array($ch, $optArray);            

            // execute request and get response
            $response = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if( $this->display_http_status_code == true ){                
                echo "<p>Create Bill - HTTP status code: {$httpcode}</p>";
            }            

            curl_close($ch);           
            
            $result_json = json_decode($response);

            return $result_json;

        }                

    }

    public function upload_invoice_and_certificate($job_id){
                                         
        $country_id = $this->config->item('country');

        $this->system_model->updateInvoiceDetails($job_id); ## Run updateInvoiceDetails first

        $job_details = $this->job_functions_model->getJobDetails2($job_id,$query_only = false);
        $agency_id = $job_details['agency_id'];
        $api_key = $this->get_api_keys($agency_id);

        $invoice_uploaded = 0;
        $certificate_uploaded = 0;
        $api = 5; // Console

        if( $api_key != '' ){

            // COPIED FROM PDF MODEL
            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
            $bpay_ref_code = "{$job_id}{$check_digit}"; 

            # Alarm Details
            $alarm_details = $this->alarm_functions_model->getPropertyAlarms($job_id, 1, 0, 2);
            $num_alarms = is_null($alarm_details) ? 0 : sizeof($alarm_details);

            # Property + Agent Details
            $property_details = $this->functions_model->getPropertyAgentDetails($job_details['property_id']);
            
            // upload invoice pdf
            $invoice_file_id = $this->guidv4(); // UUID   
            $invoice_pdf = $this->pdf_template->pdf_invoice_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id); 

            // create temporary file
            $temp = tmpfile();
            fwrite($temp, $invoice_pdf);
            $invoice_pdf_path = stream_get_meta_data($temp)['uri'];            
            
            // create file object
            $file_name = 'invoice_'.rand().date('YmdHis') . '.pdf';
            $cons_mod_params = array(
                'uuid' => $invoice_file_id,   
                'api_key' => $api_key,
                'file_name' => $file_name
            );
            $invoice_res_json_dec = $this->create_file($cons_mod_params);

            if( $invoice_res_json_dec->link->url != '' ){             

                // upload 
                $cons_mod_params = array(                                                     
                    'pre_signed_url' => $invoice_res_json_dec->link->url,
                    'content_type' => $invoice_res_json_dec->link->headers->{'Content-Type'}[0],
                    'x_amz_encr' => $invoice_res_json_dec->link->headers->{'x-amz-server-side-encryption'}[0],
                    'path_to_file' => $invoice_pdf_path
                );                                              
                $upload_file_res = $this->upload_file($cons_mod_params);
                $upload_file_status_code = $upload_file_res['httpcode'];

                if( $job_details['property_id'] > 0 ){

                    // get console property ID
                    $cons_prop_sql = $this->db->query("
                    SELECT `console_prop_id`
                    FROM `console_properties` 
                    WHERE `crm_prop_id` = {$job_details['property_id']}       
                    ");
                    $cons_prop_row = $cons_prop_sql->row();
                    $console_prop_id = $cons_prop_row->console_prop_id;

                }            

                // HTTP status code 200 - OK
                if( $upload_file_status_code == 200 && $invoice_res_json_dec->fileId && $console_prop_id != '' ){  
  
                    $date_due = date('Y-m-d', strtotime("{$job_details['jdate']} +30 days"));
                    
                    // console's amount format have no decimal points and thousand commas
                    $invoice_amount = ( $job_details['invoice_amount'] > 0 )?number_format($job_details['invoice_amount'],2,'',''):null;
                    
                    // create bill and attach invoice pdf
                    $create_bill_params = array(
                        'api_key' => $api_key,
                        'file_id' => $invoice_res_json_dec->fileId,                                
                        'console_prop_id' => $console_prop_id,
                        'invoice_amount' => $invoice_amount,
                        'date_due' => $date_due,
                        'invoice_num' => $bpay_ref_code
                    );                   
                    $this->create_bill($create_bill_params);        
                    
                    // upload compliance certificate, by uploading the file and linking it to a compliance process
                    if( $console_prop_id > 0 ){

                        // get console property ID
                        $cons_prop_comp_sql = $this->db->query("
                        SELECT `prop_comp_proc_id`
                        FROM `console_property_compliance` 
                        WHERE `console_prop_id` = {$console_prop_id}       
                        ");
                        $cons_prop_comp_row = $cons_prop_comp_sql->row();
                        $prop_comp_proc_id = $cons_prop_comp_row->prop_comp_proc_id;
    
                    }
                    
                    // upload certificate pdf
                    $certificate_file_id = $this->guidv4(); // UUID   
                    $certificate_pdf = $this->pdf_template->pdf_certificate_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id); 
            
                    // create temporary file
                    $temp = tmpfile();
                    fwrite($temp, $certificate_pdf);
                    $certificate_pdf_path = stream_get_meta_data($temp)['uri'];

                    // create file object
                    $file_name = 'certificate_'.rand().date('YmdHis') . '.pdf';
                    $cons_mod_params = array(
                        'uuid' => $certificate_file_id,   
                        'api_key' => $api_key,
                        'file_name' => $file_name
                    );
                    $cert_res_json_dec = $this->create_file($cons_mod_params);               

                    if( $cert_res_json_dec->link->url != '' ){             

                        // upload 
                        $cons_mod_params = array(                                                     
                            'pre_signed_url' => $cert_res_json_dec->link->url,
                            'content_type' => $cert_res_json_dec->link->headers->{'Content-Type'}[0],
                            'x_amz_encr' => $cert_res_json_dec->link->headers->{'x-amz-server-side-encryption'}[0],
                            'path_to_file' => $certificate_pdf_path
                        );                                              
                        $upload_file_res = $this->upload_file($cons_mod_params);
                        $upload_file_status_code = $upload_file_res['httpcode'];

                    }

                    if( $upload_file_status_code == 200 && $cert_res_json_dec->fileId && $prop_comp_proc_id != '' ){

                        if( $job_id > 0 ){

                            // mark as invoice uploaded
                            // check marker
                            $api_job_data_sql = $this->db->query("
                            SELECT COUNT(`id`) AS ajd_count
                            FROM `api_job_data`
                            WHERE `crm_job_id` = {$job_id}
                            ");
                            $ajd_count = $api_job_data_sql->row()->ajd_count;

                            if( $ajd_count > 0 ){ // already exist

                                // update
                                $this->db->query("
                                UPDATE `api_job_data`
                                SET `api_inv_uploaded` = 1
                                WHERE `crm_job_id` = {$job_id}
                                AND  `api` = {$api}
                                ");                                
    
                            }else{
    
                                // insert
                                $data = array(
                                    'crm_job_id' => $job_id,
                                    'api' => $api,
                                    'api_inv_uploaded' => 1
                                );                                
                                $this->db->insert('api_job_data', $data);
    
                            }     
                            
                            $invoice_uploaded = 1;
    
                            // expiry date is jobs date + 365 days
                            $expiry_date = ( $job_details['jdate'] != '' )?date('Y-m-d',strtotime("{$job_details['jdate']} +365 days")):null;                            
    
                            // Attach File to a Compliance
                            $comp_proc_params = array(
                                'api_key' => $api_key,
                                'file_id' => $cert_res_json_dec->fileId,                                
                                'prop_comp_proc_id' => $prop_comp_proc_id,
                                'expiry_date' => $expiry_date,
                                'last_inspect_date' => date('Y-m-d')
                            );             
                            
                            $link_file_res = $this->link_file_comp_process($comp_proc_params);   
                            $upload_file_status_code = $link_file_res['httpcode'];
    
                            if( $upload_file_status_code == 200 ){

                                // mark as certificate uploaded
                                // check api_job_data again if data exist in a certain job
                                $api_job_data_sql = $this->db->query("
                                SELECT COUNT(`id`) AS ajd_count
                                FROM `api_job_data`
                                WHERE `crm_job_id` = {$job_id}
                                ");
                                $ajd_count = $api_job_data_sql->row()->ajd_count;
    
                                // mark as certificate uploaded
                                if( $ajd_count > 0 ){ // already exist
    
                                    // update
                                    $this->db->query("
                                    UPDATE `api_job_data`
                                    SET `api_cert_uploaded` = 1
                                    WHERE `crm_job_id` = {$job_id}
                                    AND  `api` = {$api}
                                    ");                                
    
                                }else{
    
                                    // insert
                                    $data = array(
                                        'crm_job_id' => $job_id,
                                        'api' => $api,
                                        'api_cert_uploaded' => 1
                                    );                                
                                    $this->db->insert('api_job_data', $data);
    
                                }   
    
                                $certificate_uploaded = 1;
    
                            }

                        }                                             

                    }                    

                }

            }

        }   
                
        return array(
            'invoice_uploaded' => $invoice_uploaded,
            'certificate_uploaded' => $certificate_uploaded
        );        

    }

    public function get_api_keys($agency_id){

        if( $agency_id > 0 ){

            // get API key from console API connected agency
            $sql_str = "
            SELECT `api_key`
            FROM `console_api_keys`
            WHERE `agency_id` = {$agency_id}
            ";
            $sql = $this->db->query($sql_str);
            $sql_row = $sql->row();

            return $sql_row->api_key;

        }        

    }


    public function upload_file_browse($params) {
        
        $pre_signed_url = $params['pre_signed_url'];
        $content_type = $params['content_type'];
        $x_amz_encr = $params['x_amz_encr'];

        $file = $params['file'];
        $tmp_file = $file['tmp_name'];  
        
        if( $pre_signed_url != '' ){

            // init curl object        
            $ch = curl_init();
                    
            $header = array(               
                "Content-Type: {$content_type}",
                "x-amz-server-side-encryption: {$x_amz_encr}"
            );  

            $upload = file_get_contents($tmp_file);
            
            $optArray = array(
                CURLOPT_URL => $this->myUrlEncode($pre_signed_url),
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,                              
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $upload
            );            

            // apply those options
            curl_setopt_array($ch, $optArray);

            // execute request and get response
            $response = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if( $this->display_http_status_code == true ){                
                echo 'HTTP code: ' . $httpcode;
            }           

            curl_close($ch);
            
            //$result_json = json_decode($response);

            return $httpcode;

        }                               

    }


    public function upload_file($params) {
        
        $pre_signed_url = $params['pre_signed_url'];
        $content_type = $params['content_type'];
        $x_amz_encr = $params['x_amz_encr'];
        $path_to_file = $params['path_to_file'];    
        
        if( $pre_signed_url != '' ){

            // init curl object        
            $ch = curl_init();
                    
            $header = array(               
                "Content-Type: {$content_type}",
                "x-amz-server-side-encryption: {$x_amz_encr}"
            );  

            $upload = file_get_contents($path_to_file);
            
            $optArray = array(
                CURLOPT_URL => $this->myUrlEncode($pre_signed_url),
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,                              
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $upload
            );            

            // apply those options
            curl_setopt_array($ch, $optArray);

            // execute request and get response
            $response = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if( $this->display_http_status_code == true ){                
                echo "<p>Upload File - HTTP status code: {$httpcode}</p>";
            }            

            curl_close($ch);
            
            //$result_json = json_decode($response);

            //return $httpcode;

            $ret_arr = [];
            $ret_arr = array(
                'response' => $response,
                'httpcode' => $httpcode,
            );

            return $ret_arr;

        }                       
        

    }

    // urlencode and rawurlencode doesn't work on console url
    function myUrlEncode($string) {
        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return str_replace($entities, $replacements, urlencode($string));
    }


    public function get_file($params) {

        $api_key = $params['api_key'];        
        $uuid = $params['uuid'];

        if( $api_key != '' && $uuid != '' ){

            // init curl object        
            $ch = curl_init();

            $token_url = "{$this->api_gateway}/storage/v1/files/_bulk?fileIds={$uuid}";
            $client_id = $this->client_id;
            $secret = $this->secret;    

            $authorization = base64_encode("$client_id:$secret");
            $header = array("Authorization: Basic {$authorization}", "API-Key: {$api_key}");                           

            $optArray = array(
                CURLOPT_URL => $token_url,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true
            );

            // apply those options
            curl_setopt_array($ch, $optArray);

            // execute request and get response
            $response = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if( $this->display_http_status_code == true ){                
                echo 'HTTP code: ' . $httpcode;
            }            

            curl_close($ch);
            
            $result_json = json_decode($response);

            return $result_json;

        }             

    }


    public function download_file($params) {
        
        $pre_signed_url = $params['pre_signed_url'];
        $file_type = $params['file_type']; 
        $file_type_exp = explode("/",$file_type);
        $file_type_short = $file_type_exp[1];

        if( $pre_signed_url != '' ){

            // init curl object        
            $ch = curl_init();
           
            $optArray = array(
                CURLOPT_URL => $this->myUrlEncode($pre_signed_url),                
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true
            );

            // apply those options
            curl_setopt_array($ch, $optArray);

            // execute request and get response
            $response = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if( $this->display_http_status_code == true ){                
                echo 'HTTP code: ' . $httpcode;
            }            

            curl_close($ch);
            
            //$result_json = json_decode($response);

            //return $response;

            // file name            
            $filename = "test_file_".rand().date('YmdHis').".{$file_type_short}";
                    
            header("Content-type: {$file_type}");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            return $response;

        }                

    }

    // link a file to a compliance process
    public function link_file_comp_process($params) {
              
        $api_key = $params['api_key'];        
        $file_id = $params['file_id'];
        $prop_comp_proc_id = $params['prop_comp_proc_id'];
        $expiry_date = $params['expiry_date'];
        $last_inspect_date = $params['last_inspect_date'];

        if( $api_key != '' && $prop_comp_proc_id != '' && $file_id != '' ){

            // init curl object        
            $ch = curl_init();

            $token_url = "{$this->api_gateway}/compliance/v1/property-compliance-processes/_update-new-compliance-details";
            $client_id = $this->client_id;
            $secret = $this->secret; 
            
            $authorization = base64_encode("$client_id:$secret");
            $header = array("Authorization: Basic {$authorization}", "API-Key: {$api_key}","Content-Type: application/json");  
                    
            $post_params = array(
                'propertyComplianceProcessId' => $prop_comp_proc_id,
                'newPropertyComplianceDetails' => array(
                    'expiryDate' => $expiry_date,
                    'lastInspectionDate' => $last_inspect_date,
                    'certificateFileId' => $file_id
                )
            );

            $optArray = array(
                CURLOPT_URL => $token_url,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($post_params)
            );

            // apply those options
            curl_setopt_array($ch, $optArray);            

            // execute request and get response
            $response = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if( $this->display_http_status_code == true ){                            
                echo "<p>Link File to a Compliance Process - HTTP status code: {$httpcode}</p>";
            }            

            curl_close($ch);           
            
            $result_json = json_decode($response);

            //return $httpcode;

            $ret_arr = [];
            $ret_arr = array(
                'response' => $response,
                'httpcode' => $httpcode,
            );

            return $ret_arr;

        }                

    }
    
    public function process_webhook_data($json_data){


        $json_dec = json_decode($json_data);     
        
        $event_obj = $json_dec->event;
        $rel_res_obj = $event_obj->relatedResources;
        $prop_comp_obj = $rel_res_obj->propertyCompliance;
        $manage_agree_obj = $rel_res_obj->managementAgreement;
        $landlords_obj_arr = $manage_agree_obj->landlords;
        $ten_agree_arr_obj = $rel_res_obj->tenantAgreements;
        $prop_obj = $rel_res_obj->property;      
        $portfolio_obj = $rel_res_obj->portfolio;
        $users_arr_obj = $rel_res_obj->users;
        $address_obj = $prop_obj->address;

        $event_id = $json_dec->eventId;
        $office_id = $json_dec->officeId;
        $event_type = $json_dec->eventType;        
        if( $event_type == 'PROPERTY_COMPLIANCE_CONTACT_UPDATED' ){

            // console prop ID is located here on this type of webhook
            $console_prop_id = $ten_agree_arr_obj[0]->propertyId;

        }else{
            $console_prop_id = $prop_obj->propertyId; 
        } 

        $prop_comp_proc_obj = $event_obj->propertyComplianceProcess;
        $prop_comp_proc_id = $prop_comp_proc_obj->propertyComplianceProcessId;  
        
        $last_updated_date_time = date('Y-m-d H:i:s',strtotime($event_obj->lastUpdatedDateTime));   
        
        // get landlords
        $landlords_arr = [];
        foreach( $landlords_obj_arr as $landlords_obj ){
            $landlords_arr[] = $landlords_obj->contactId;
        }         

        if( $event_id != '' ){

            
            // check if event ID exist
            $sql = $this->db->query("
            SELECT COUNT(`id`) AS wh_count
            FROM `console_webhooks_data` 
            WHERE `event_id` = '{$event_id}'       
            ");
            $wh_count = $sql->row()->wh_count;  
                      

            // event ID doesnt exist and even date is newest
            if( $wh_count == 0  ){

                
                // get latest webhook request
                $sql2 = $this->db->query("
                SELECT `last_updated_date_time`
                FROM `console_webhooks_data`                 
                ORDER BY `last_updated_date_time` DESC
                LIMIT 1   
                "); 
                     
                
                $cwd_num_rows = $sql2->num_rows();

                // is event latest?
                $is_latest = false;
                if( $cwd_num_rows > 0 ){

                    $row2 = $sql2->row();

                    if( strtotime($last_updated_date_time) > strtotime($row2->last_updated_date_time) ){
                        $is_latest = true;
                    }else{
                        $is_latest = false;
                    }
                    
                }

                //if( $cwd_num_rows == 0 || $is_latest == true ){

                   // catch webhook data
                    $insert_data = array(
                        'console_prop_id' => $console_prop_id,
                        'event_id' => $event_id,
                        'last_updated_date_time' => $last_updated_date_time,
                        'json' => $json_data,
                        'office_id' => $office_id,
                        'event_type' => $event_type
                    );        
                    $this->db->insert('console_webhooks_data', $insert_data);   

                    if(

                        $event_type == 'PROPERTY_COMPLIANCE_REQUESTED' ||
                        $event_type == 'PROPERTY_COMPLIANCE_UPDATED' ||
                        $event_type == 'PROPERTY_COMPLIANCE_CANCELLED' 
        
                    ){ // property events
        
                        if( $event_type == 'PROPERTY_COMPLIANCE_CANCELLED' ){ // deleted compliance
        
                            // remove property from console property db
                            // TODO: needs to notify staff
                            if( $console_prop_id ){
            
                                $this->db->where('console_prop_id', $console_prop_id);
                                $this->db->delete('console_properties');
            
                            }                
                            
                        }else{ // property added or edited
        
                            if( $console_prop_id > 0 ){
        
                                // check if property already exist
                                $cons_prop_sql = $this->db->query("
                                SELECT COUNT(`id`) AS cons_prop_count
                                FROM `console_properties` 
                                WHERE `console_prop_id` = {$console_prop_id}       
                                ");
                                $cons_prop_count = $cons_prop_sql->row()->cons_prop_count; 
            
                                // existing property found
                                if( $cons_prop_count > 0 ){
            
                                    // update
                                    $update_data = array(   
                                        'office_id' => $office_id,
                                        'full_address' => $prop_obj->displayName,                     
                                        'unit_num' => $address_obj->unitNumber,
                                        'street_num' => $address_obj->streetNumber,
                                        'street_name' => $address_obj->streetName,
                                        'street_type' => $address_obj->streetType,
                                        'suburb' => $address_obj->suburb,
                                        'postcode' => $address_obj->postCode,
                                        'state' => $address_obj->stateCode
                                    );
                                    
                                    $this->db->where('console_prop_id', $console_prop_id);
                                    $this->db->update('console_properties', $update_data);                        
            
                                }else{
            
                                    // insert
                                    $insert_data = array(
                                        'office_id' => $office_id,
                                        'console_prop_id' => $console_prop_id,
                                        'full_address' => $prop_obj->displayName,
                                        'unit_num' => $address_obj->unitNumber,
                                        'street_num' => $address_obj->streetNumber,
                                        'street_name' => $address_obj->streetName,
                                        'street_type' => $address_obj->streetType,
                                        'suburb' => $address_obj->suburb,
                                        'postcode' => $address_obj->postCode,
                                        'state' => $address_obj->stateCode
                                    );        
                                    $this->db->insert('console_properties', $insert_data);  
                                
                                }
        
                                // loop through tenants
                                foreach( $rel_res_obj->contacts as $contacts_obj ){

                                    // add/update tenants
                                    $params = array(
                                        'contacts_obj' => $contacts_obj,
                                        'console_prop_id' => $console_prop_id,
                                        'landlords_arr' => $landlords_arr
                                    );
                                    $this->add_update_tenants($params);  

                                }
                                
                                // property compliance
                                $expiry_date = ( $prop_comp_obj->expiryDate != '' )?date('Y-m-d',strtotime($prop_comp_obj->expiryDate)):null;
                                $last_ins_date = ( $prop_comp_obj->lastInspectionDate != '' )?date('Y-m-d',strtotime($prop_comp_obj->lastInspectionDate)):null;
                                
                                // check if property compliance exist
                                $cpc_prop_sql = $this->db->query("
                                SELECT COUNT(`id`) AS cpc_count
                                FROM `console_property_compliance` 
                                WHERE `console_prop_id` = {$console_prop_id}       
                                ");
                                $cpc_count = $cpc_prop_sql->row()->cpc_count; 
        
                                if( $cpc_count > 0 ){ // update
                                    
                                    $update_data = array(                                                        
                                        'compliance_notes' => $prop_comp_obj->notes,
                                        'expiry_date' => $expiry_date,
                                        'last_inspection' => $last_ins_date,
                                        'qld_2020_comp' => $prop_comp_obj->has2022LegislationCompliance,
                                        'prop_comp_proc_id' => $prop_comp_proc_id
                                    );
                                    
                                    $this->db->where('console_prop_id', $console_prop_id);
                                    $this->db->update('console_property_compliance', $update_data);  
        
                                }else{ // add
                                    
                                    $insert_data = array(
                                        'console_prop_id' => $console_prop_id,
                                        'compliance_notes' => $prop_comp_obj->notes,
                                        'expiry_date' => $expiry_date,
                                        'last_inspection' => $last_ins_date,
                                        'qld_2020_comp' => $prop_comp_obj->has2022LegislationCompliance,
                                        'prop_comp_proc_id' => $prop_comp_proc_id
                                    );        
                                    $this->db->insert('console_property_compliance', $insert_data); 
                                } 
                                
                                
                                
                                // property other info
                                // check if property other info exist
                                $cpoi_prop_sql = $this->db->query("
                                SELECT COUNT(`id`) AS cpoi_count
                                FROM `console_property_other_info` 
                                WHERE `console_prop_id` = {$console_prop_id}       
                                ");
                                $cpoi_count = $cpoi_prop_sql->row()->cpoi_count; 
        
                                if( $cpoi_count > 0 ){ // update
                                    
                                    $update_data = array(                                                        
                                        'key_number' => $prop_obj->keyNumber,
                                        'access_details' => $prop_obj->accessDetails,
                                        'property_type' => ucwords(strtolower($prop_obj->propertyType))
                                    );
                                    
                                    $this->db->where('console_prop_id', $console_prop_id);
                                    $this->db->update('console_property_other_info', $update_data);  
        
                                }else{ // add
                                    
                                    $insert_data = array(
                                        'console_prop_id' => $console_prop_id,
                                        'key_number' => $prop_obj->keyNumber,
                                        'access_details' => $prop_obj->accessDetails,
                                        'property_type' => ucwords(strtolower($prop_obj->propertyType))
                                    );        
                                    $this->db->insert('console_property_other_info', $insert_data); 
                                }
        
        
                                
                                // tenancy agreement
                                // clear
                                if( $console_prop_id > 0 ){
                                    $this->db->where('console_prop_id', $console_prop_id);
                                    $this->db->delete('console_tenant_agreements');
                                }
                                
                                // re-insert
                                foreach( $ten_agree_arr_obj as $ten_agree_obj ){ 
        
                                    $lease_obj = $ten_agree_obj->lease;    
                                    
                                    $inaugural_date = ( $lease_obj->inauguralDate != '' )?date('Y-m-d',strtotime($lease_obj->inauguralDate)):null;
                                    $start_date = ( $lease_obj->startDate != '' )?date('Y-m-d',strtotime($lease_obj->startDate)):null;
                                    $end_date = ( $lease_obj->endDate != '' )?date('Y-m-d',strtotime($lease_obj->endDate)):null;
                                    $vacating_date = ( $lease_obj->vacatingDate != '' )?date('Y-m-d',strtotime($lease_obj->vacatingDate)):null;
        
                                    $insert_data = array(
                                        'console_prop_id' => $console_prop_id,
                                        'lease_name' => $ten_agree_obj->leaseName,
                                        'inaugural_date' => $inaugural_date,
                                        'start_date' => $start_date,
                                        'end_date' => $end_date,
                                        'vacating_date' => $vacating_date
                                    );        
                                    $this->db->insert('console_tenant_agreements', $insert_data);                         
                                
                                }    
                                
                                
                                // users                        
                                // clear
                                if( $console_prop_id > 0 ){
                                    $this->db->where('console_prop_id', $console_prop_id);
                                    $this->db->delete('console_users');
                                }
                                
                                // re-insert
                                foreach( $users_arr_obj as $users_obj ){ 
        
                                    $insert_data = array(
                                        'console_prop_id' => $console_prop_id,
                                        'first_name' => $users_obj->firstName,
                                        'last_name' => $users_obj->lastName,
                                        'email' => $users_obj->email
                                    );        
                                    $this->db->insert('console_users', $insert_data);                         
                                
                                }
            
                            }
        
                        }                                            
        
                    }else if(
        
                        $event_type == 'PROPERTY_COMPLIANCE_TENANCY_CREATED' ||
                        $event_type == 'PROPERTY_COMPLIANCE_TENANCY_UPDATED' ||
                        $event_type == 'PROPERTY_COMPLIANCE_TENANCY_CANCELLED' ||

                        $event_type == 'PROPERTY_COMPLIANCE_CONTACT_UPDATED'
        
                    ){ // tenant events  
                                                
                        // clear markers
                        $this->db->query("
                        UPDATE `console_property_tenants`
                        SET 
                            `new_tenants_ts` = NULL,
                            `first_name_updated_ts` = NULL,
                            `last_name_updated_ts` = NULL
                        WHERE `console_prop_id` = {$console_prop_id}
                        "); 

                        if( $event_type == 'PROPERTY_COMPLIANCE_CONTACT_UPDATED' ){

                            // add/update tenant
                            $params = array(
                                'contacts_obj' => $event_obj->contact,
                                'console_prop_id' => $console_prop_id,
                                'landlords_arr' => $landlords_arr
                            );
                            $this->add_update_tenants($params);  
                            

                        }else{

                            // loop through tenants
                            foreach( $rel_res_obj->contacts as $contacts_obj ){

                                // add/update tenants
                                $params = array(
                                    'contacts_obj' => $contacts_obj,
                                    'console_prop_id' => $console_prop_id,
                                    'landlords_arr' => $landlords_arr
                                );
                                $this->add_update_tenants($params);  

                            }

                        }                                                
        
                    }

                //}                                

            }                                        

        }

    }


    public function add_update_tenants($params){

        $contacts_obj = $params['contacts_obj'];
        $console_prop_id = $params['console_prop_id'];
        $landlords_arr = $params['landlords_arr'];

        $today_full = date('Y-m-d H:i:s');        

        $contact_id = $contacts_obj->contactId;

        if( !in_array($contact_id,$landlords_arr) ){ // exclude landlords

            $person_det_obj = $contacts_obj->personDetail;
            $phones_arr_obj = $contacts_obj->phones;
            $emails_arr_obj = $contacts_obj->emails;

            if( $contact_id > 0 ){

                // check if tenant already exist
                $tenants_sql = $this->db->query("
                SELECT 
                    `first_name`,
                    `last_name`
                FROM `console_property_tenants` 
                WHERE `contact_id` = {$contact_id}       
                ");
                $tenants_row = $tenants_sql->row(); 

                // existing tenant found
                if( $tenants_sql->num_rows() > 0 ){                    
                    
                    // update
                    $update_data = array(                                                        
                        'first_name' => $person_det_obj->firstName,
                        'last_name' => $person_det_obj->lastName                        
                    );

                    // check if tenant first name is updated
                    if( $tenants_row->first_name != $person_det_obj->firstName ){
                        $update_data['first_name_updated_ts'] = $today_full;
                    }

                    // check if tenant last name is updated
                    if( $tenants_row->last_name != $person_det_obj->lastName ){
                        $update_data['last_name_updated_ts'] = $today_full;
                    }
                    
                    $this->db->where('contact_id', $contact_id);
                    $this->db->where('console_prop_id', $console_prop_id);
                    $this->db->update('console_property_tenants', $update_data);     

                }else{   
                                
                    // insert
                    $insert_data = array(
                        'contact_id' => $contact_id,
                        'console_prop_id' => $console_prop_id,
                        'first_name' => $person_det_obj->firstName,
                        'last_name' => $person_det_obj->lastName
                    );   
                    
                    // marked as new tenant if its not the first webhook, used on highlighting new rows                                
                    $sql = $this->db->query("
                    SELECT COUNT(`id`) AS wh_count
                    FROM `console_webhooks_data` 
                    WHERE `console_prop_id` = '{$console_prop_id}'       
                    ");
                    $wh_count = $sql->row()->wh_count;
                    if( $wh_count > 0 ){
                        $insert_data['new_tenants_ts']  = $today_full; 
                    }
                    
                    $this->db->insert('console_property_tenants', $insert_data);  
                
                }                

                // add tenant phones
                // clear first
                $this->db->query("
                DELETE cpt_phone
                FROM `console_property_tenant_phones` AS cpt_phone
                INNER JOIN `console_property_tenants` AS cpt ON cpt_phone.`contact_id` = cpt.`contact_id`
                WHERE cpt.`contact_id` = {$contact_id} 
                AND cpt.`console_prop_id` = {$console_prop_id} 
                ");

                // insert all
                foreach( $phones_arr_obj as $phones_obj ){
                    
                    // insert
                    $insert_data = array(
                        'contact_id' => $contact_id,
                        'number' => $phones_obj->phoneNumber,
                        'type' => $phones_obj->type,
                        'is_primary' => $phones_obj->primary
                    );        
                    $this->db->insert('console_property_tenant_phones', $insert_data); 

                }

                // add tenant email
                // clear first
                $this->db->query("
                DELETE cpt_email
                FROM `console_property_tenant_emails` AS cpt_email
                INNER JOIN `console_property_tenants` AS cpt ON cpt_email.`contact_id` = cpt.`contact_id`
                WHERE cpt.`contact_id` = {$contact_id} 
                AND cpt.`console_prop_id` = {$console_prop_id} 
                ");
                
                // insert all
                foreach( $emails_arr_obj as $emails_obj ){
                    
                    // insert
                    $insert_data = array(
                        'contact_id' => $contact_id,
                        'email' => $emails_obj->emailAddress,
                        'type' => $emails_obj->type,
                        'is_primary' => $emails_obj->primary
                    );        
                    $this->db->insert('console_property_tenant_emails', $insert_data); 

                }

            }

        }

    }


    public function send_all_certificates_and_invoices($is_get_data = false) {

        ini_set('max_execution_time', 900); 

        $job_status = "Merged Certificates";
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

        cp.`crm_prop_id`
        ";

        $custom_where = "( cp.`crm_prop_id` IS NOT NULL AND cp.`crm_prop_id` != '' )";
        $paramsPmeSent = array(
            'sel_query' => $sel_query_pme,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_joins_arr' => array(

                array(
                    'join_table' => '`console_properties` AS cp',
                    'join_on' => 'p.`property_id` = cp.`crm_prop_id`',
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

            if( $val['jid'] > 0 ){

                $this->upload_invoice_and_certificate($val['jid']);

            }            

        }
            
        return array("err" => $isFailUpload);

    }
		
}
?>