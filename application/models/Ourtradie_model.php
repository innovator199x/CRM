<?php

class Ourtradie_model extends CI_Model
{
    public function __construct() {
        $this->load->database();
    }

    //Get all connected agencies
    public function getConnectedAgencies(){
        return $this->db->select('`agen_api_tok`.`agency_api_token_id` , `a`.`agency_id` , `a`.`agency_name` , `a`.`no_bulk_match` , `pme_upc`.`count`')
        ->from('`agency_api_tokens` AS agen_api_tok')
        ->join('agency_api AS agen_api', 'agen_api_tok.api_id = agen_api.agency_api_id', 'left')
        ->join('agency AS a', 'agen_api_tok.agency_id = a.agency_id', 'left')
        ->join('pme_unmatched_property_count AS pme_upc', 'agen_api_tok.agency_id = pme_upc.agency_id', 'left')
        ->where('agen_api_tok.api_id', 6)
        ->where('agen_api_tok.active', 1)
        ->where('a.status !=', "deactivated")
        ->where('a.status !=', "target")
        ->group_by('agen_api_tok.agency_id')
        ->order_by('a.agency_name','asc')
        ->get()->result_object();
        $this->db->get('agency_api_tokens');
    }//endfct

    //Get all property list
    public function getPropertyList($agency_id){
        return $this->db->select('`property_id` , `address_1` , `address_2` , `address_3` , `state` , `postcode` , `is_sales`, `deleted`, `ourtradie_prop_id`, `agency_id`')
        ->from('property')
        ->where('agency_id', $agency_id)
        ->where('deleted', 0)
        ->where('(is_nlm IS NULL OR is_nlm=0)')
        ->where('`ourtradie_prop_id` IS NULL')
        ->get()->result_object();
        $this->db->get('property');
    }//endfct

    //Get token
	public function getToken($agency_id, $api_id){
        return $this->db->select('expiry,refresh_token,created,access_token')
        ->from('agency_api_tokens')
        ->where('agency_id', $agency_id)
        ->where('api_id', $api_id)
        ->get()->result_object();
        $this->db->get('agency_api_tokens');
    }

    //Get ourtradie PROPERTY ID
	public function get_ourtradie_prop_id($prop_id){
        return $this->db->select('api_prop_id')
        ->from('api_property_data')
        ->where('crm_prop_id', $prop_id)
        ->get()->result_object();
        $this->db->get('api_property_data');
    }

    //Get ourtradie PROPERTY ID
	public function getAgencyEmail($agency_id){
        return $this->db->select('agency_name')
        ->from('agency')
        ->where('agency_id', $agency_id)
        ->get()->result_object();
        $this->db->get('agency');
    }

	//Insert token
    public function insertToken($insert_data) {
        $this->db->insert('agency_api_tokens', $insert_data);
        return $this->db->insert_id();
    }

    //Update token
    public function updateToken($agency_id, $api_id, $update_data) {
        $this->db->where('agency_id', $agency_id);
        $this->db->where('api_id', $api_id);
        $this->db->update('agency_api_tokens', $update_data);

        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return false;
        }
    }//endfct

    public function check_duplicate_full_address($params) {

        $full_address = "{$params['address_1']} {$params['address_2']} {$params['suburb']} {$params['state']} {$params['postcode']}";

        $exist_in_crm_sql_str = "
            SELECT 
                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`deleted`,
                p.is_nlm,
                
                a.`agency_id`,
                a.`agency_name`
            FROM `property`AS p
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`                                                     
            WHERE CONCAT_WS( ' ', TRIM(LOWER(p.`address_1`)), TRIM(LOWER(p.`address_2`)), TRIM(LOWER(p.`address_3`)), TRIM(LOWER(p.`state`)), TRIM(LOWER(p.`postcode`)) ) = '" . $this->db->escape_str(strtolower(trim($full_address))) . "'  
            AND p.deleted = 0      
        ";
        return $this->db->query($exist_in_crm_sql_str);

    }

    public function getTenants($propertyID){
        //echo $propertyID;
        //exit();

        return $this->db->select('property_tenant_id,tenant_firstname,tenant_lastname,tenant_email,tenant_mobile')
        ->from('property_tenants')
        ->where('property_id', $propertyID)
        ->where('active', 1)
        ->get()->result_object();
        $this->db->get('property_tenants');
    }

    public function getProperty($property_id){
        //echo $property_id;
        //exit();

        return $this->db->select('`p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`lat`, `p`.`lng`, `c`.`country` AS `country_name`')
        ->from('`property` AS p')
        ->join('agency AS a', 'p.agency_id = a.agency_id', 'left')
        ->join('agency_user_accounts AS aua', 'p.pm_id_new = aua.agency_user_account_id', 'left')
        ->join('countries AS c', 'a.country_id = c.country_id', 'left')
        ->where('p.property_id', $property_id)
        ->get()->result_object();
        $this->db->get('property');
    }

    public function getApiproperty($ourtradie_params) {

        $api_id = 6;

        $agency_id = $ourtradie_params['agency_id'];
		$ot_prop_id   = $ourtradie_params['ot_prop_id'];
        $this->checkToken($agency_id);

        $tokens = $this->getToken($agency_id, $api_id);
        $access_token = $tokens[0]->access_token;
        $refresh_token = $tokens[0]->refresh_token;

        $tmp_arr_ref_token = explode("+/-]",$refresh_token);

        $ot_agency_id = $tmp_arr_ref_token[1];
        $_SESSION['ot_agency_id'] = $ot_agency_id;
        //exit();

        //$token = array('access_token' => $access_token);
        //exit();

        if (isset($access_token)) {
            $api = new OurtradieApi();

            $token = array('access_token' => $access_token);

            //GetAllResidentialProperties
            $params = array(
                'Skip' 	 		=> 'No',
                'Count'     => 'No',
                'AgencyID'  => $ot_agency_id
            );
            $property = $api->query('GetAllResidentialProperties', $params, '', $token, true);

            $data_property = array();
            $data_property = json_decode($property, true);

            $data['property_list'] = array_filter($data_property, function ($v) {
            return $v !== 'OK';
            });

            foreach ($data['property_list'] as $prop) {
                foreach ($prop as $row) {

                    if($row['ID'] == $ot_prop_id){
                        /*
                        [ID] => 1132441
                        [Address1] => Sampe Address1 Property
                        [Address2] => Sampe Address2 Property
                        [Suburb] => Sample Suburb
                        [State] => VIC
                        [Postcode] => 1234
                        [KeyNumber] => 0
                        */
                        $data['property'] = $array = array('ID' => $row['ID'], 'Address1' =>$row['Address1'], 'Address2' =>$row['Address2'], 'Suburb' =>$row['Suburb'], 'State' =>$row['State'], 'Postcode' =>$row['Postcode'], 'KeyNumber' =>$row['KeyNumber']);

                        $data['api_property'] = $data['property'];
                    }
                }
            }
        }
        return $data['api_property'];
    }

    public function checkToken($agency_id){

        $unixtime 	= time();
        $now 		= date("Y-m-d H:i:s",$unixtime);

        $api_id = 6;
        $token['token'] = $this->ourtradie_model->getToken($agency_id, $api_id);

        $created         = $token['token'][0]->created;
        $expiry          = $token['token'][0]->expiry;
        $expired         = strtotime($now) - strtotime($expiry);
        $tmp_refresh_token   = $token['token'][0]->refresh_token;
        $tmp_arr_refresh_token = explode("+/-]",$tmp_refresh_token);
        $refresh_token = $tmp_arr_refresh_token[0];

        //$refresh_token = "1654578cef286cf59e4dad1634129c56e42cfbe6-d7156da53a0ec07cd4970f76abb9def081ac61d9";

        if($expired > 0){

            $options = array(
                'grant_type'      => 'refresh_token',
                'refresh_token'   =>  $refresh_token,
                'client_id'		  => 'br6ucKvcPRqDNA1V2s7x',
                'client_secret'	  => 'd5YOJHb6EYRw5oypl73CJFWGLob5KB9A',
                'redirect_uri'	  => 'https://agencydev.sats.com.au/ourtradie/refreshToken'
                );

            $api = new OurtradieApi($options, $_REQUEST);
            $token = $refresh_token;

            $response = $api->refreshToken($token);

            if(!empty($response)){
                $access_token   = $response->access_token;
                $refresh_token  = $response->refresh_token;
                $expiry         = date('Y-m-d H:i:s',strtotime('+3600 seconds'));
                $created        = $now;

                $update_data = array(
                    'access_token'    => $access_token,
                    'refresh_token'   => $refresh_token."+/-]".$tmp_arr_refresh_token[1],
                    'created'         => $created,
                    'expiry'          => $expiry,
                );

                $this->ourtradie_model->updateToken($agency_id, $api_id, $update_data);
                
                if($uri == "property"){
                    $contactId = $_SESSION['contactId'];
                    $agency_id = $_SESSION['agency_id'];
                    $this->property($contactId, $agency_id);
                }

                if($uri == "properties_needs_verification"){
                    redirect('/property_me/'.$uri);
                }
            }
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

    /*
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
    */

    public function ourtradie_find_unmatched_properties(){

        $this->load->model('pme_model');        

        $today = date('Y-m-d H:i:s');

        $agency_sql_str = "
            SELECT 
                a.`agency_id`,
                a.`agency_name`,
                
                agen_tok.`agency_api_token_id`,
                agen_tok.`connection_date`,
                agen_tok.`access_token`
            FROM `agency` AS a 
            LEFT JOIN `agency_api_integration` AS agen_api ON (a.`agency_id` = agen_api.`agency_id` AND agen_api.`connected_service` = 6 )
            LEFT JOIN `agency_api_tokens` AS agen_tok ON ( a.`agency_id` = agen_tok.`agency_id` AND agen_tok.`api_id` = 6 )
            WHERE a.`status` = 'active'    
            AND agen_api.`connected_service` = 6   
            AND agen_api.`active` = 1       
        ";
        
        //echo $agency_sql_str;
        
        $agency_sql = $this->db->query($agency_sql_str);
        $tmp_data = $agency_sql->result();

        foreach( $agency_sql->result() as $agency_row ){

            $agency_id = $agency_row->agency_id;
            $api_id = 6;

            if( $agency_id > 0 ){

                echo "<h1>{$agency_row->agency_name}($agency_id)</h1>";

                // get CRM properties
                $property_sql_str = "
                SELECT 
                    `address_1` AS p_street_num,
                    `address_2` AS p_street_name,
                    `address_3` AS p_suburb,
                    `state` AS p_state,
                    `postcode` AS p_postcode,
                    `ourtradie_prop_id`
                FROM `property`
                WHERE `agency_id` = {$agency_id}      
                AND ( `ourtradie_prop_id` IS NOT NULL AND `ourtradie_prop_id` != '' )                       
                ";
                
                $property_sql = $this->db->query($property_sql_str);
                $crm_prop = $property_sql->result();

                
                // get Ourtradie properties
                $data['agency_name']  = $this->ourtradie_model->getAgencyEmail($agency_id);
                $agency_name = $data['agency_name'][0]->agency_name;
                
                $this->checkToken($agency_id);

                $token['token'] = $this->ourtradie_model->getToken($agency_id, $api_id);

                $access_token   = $token['token'][0]->access_token;

                $api = new OurtradieApi();

                $token = array('access_token' => $access_token);

                //GetAgencies
                $params = array(
                    'Skip' 	 	=> 'No',
                    'Count'     => 'No'
                );
                $agency = $api->query('GetAgencies', $params, '', $token, true);

                $data_agency = array();
                $data_agency = json_decode($agency, true);

                $data['agency_list'] = array_filter($data_agency, function ($v) {
                return $v !== 'OK';
                });

                foreach ($data['agency_list'] as $key) {
                    foreach ($key as $item) {

                        if($item['AgencyName'] == $agency_name){
                            $ot_agency_id = $item['AgencyID'];
                        }
                    }
                }

                //GetAllResidentialProperties
                $params = array(
                    'Skip' 	 		=> 'No',
                    'Count'     => 'No',
                    'AgencyID'  => $ot_agency_id
                );
                $property = $api->query('GetAllResidentialProperties', $params, '', $token, true);

                $data_property = array();
                $data_property = json_decode($property, true);

                $property_list = array_filter($data_property, function ($v) {
                return $v !== 'OK';
                });

                $ot_agency_id = '';

                // PNV
                // exclude already NLM properties and do not show already linked properties
                $pnv_sql_str = "
                SELECT 
                    pnv.`pnv_id`, 
                    pnv.`property_source`, 
                    pnv.`property_id`, 
                    pnv.`agency_id`, 
                    pnv.`note`, 
                    pnv.`agency_verified`,
                    pnv.`property_address`,

                    a.`agency_id`, 
                    a.`agency_name`
                FROM `properties_needs_verification` AS `pnv`                
                INNER JOIN `agency` AS `a` ON pnv.`agency_id` = a.`agency_id`
                WHERE pnv.`active` = 1
                AND a.`agency_id` = {$agency_id}
                AND pnv.`property_source` = 7
                AND pnv.`ignore_issue` = 0
                ";
                $pnv_sql = $this->db->query($pnv_sql_str);
                $pnv_row_res = $pnv_sql->result();
                //print_r($pnv_row_res);


                // hidden properties
                $hap_sql_str = "
                SELECT id AS hap_id, `api_prop_id`
                FROM `hidden_api_property`
                WHERE`agency_id` = {$agency_id}           
                ";
                $hap_sql = $this->db->query($hap_sql_str);
                $hap_res = $hap_sql->result();


                // CRM Table
                $table_html = "
                <div style='float: left;'>
                <h2>CRM Properties</h2>
                <table style='border:1px solid; border:1px solid; border-collapse: collapse; margin-right: 50px;'>
                <tr>
                    <th style='text-align:left; border:1px solid;'>#</th>
                    <th style='text-align:left; border:1px solid;'>Address</th>
                    <th style='text-align:left; border:1px solid;'>Stored PMe Prop ID</th>
                </tr>
                ";
                foreach( $crm_prop as $index => $prop_row ){

                    $p_address = "{$prop_row->p_street_num} {$prop_row->p_street_name} {$prop_row->p_suburb} {$prop_row->p_state} {$prop_row->p_postcode}";

                    $table_html .= "<tr>
                        <td style='text-align:left; border:1px solid;'>".( $index+1 ).".)</td>
                        <td style='text-align:left; border:1px solid;'>{$p_address}</td>
                        <td style='text-align:left; border:1px solid;'>{$prop_row->ourtradie_prop_id}</td>
                    </tr>";            

                }
                $table_html .= "</table>
                </div>
                ";

                echo $table_html;
                //exit();
                
                // PMe Table
                $table_html = "
                <div style='float: left;'>
                <h2>Ourtradie Properties</h2>
                <table style='border:1px solid; border:1px solid; border-collapse: collapse;'>
                <tr>
                    <th style='text-align:left; border:1px solid;'>Count</th>
                    <th style='text-align:left; border:1px solid;'>Address</th>                    
                    <th style='text-align:left; border:1px solid;'>CRM property Connected to</th>
                    <th style='text-align:left; border:1px solid;'>OurTradie Prop ID</th>
                    <th style='text-align:left; border:1px solid;'>Has notes on PNV</th>     
                    <th style='text-align:left; border:1px solid;'>Property Sales?</th>
                    <th style='text-align:left; border:1px solid;'>Is Hidden?</th>               
                </tr>
                ";
                // pme
                $pnv_need_process_count = 0;
                foreach ( $property_list as $pme_list ){
                    foreach ( $pme_list as $pme_row ){

                        $has_connected = false;
                        $pme_prop_id = null;
                        $crm_connected_prop = null;
                        $has_pnv_notes = false;
                        $pme_prop_with_notes = null;
                        $highlight_red = false;
                        $is_sales_prop = false;
                        $is_prop_hidden = false;
                    
                        // pme             
                        $pme_address = $pme_row['Address1'];
                        
                        // crm        
                        foreach( $crm_prop as $index => $crm_row ){   
                                                

                            $crm_address = "{$prop_row->p_street_num} {$prop_row->p_street_name} {$prop_row->p_suburb} {$prop_row->p_state} {$prop_row->p_postcode}";             
                            
                            if( $pme_row['ID'] == $crm_row->ourtradie_prop_id ){
                                //echo "Connected";
                                $has_connected = true;
                                //$pme_prop_id = $pme_row->Id;
                                $crm_connected_prop = $crm_address;
                            }
                        }

                        // PNV        
                        foreach( $pnv_row_res as $index => $pnv_row ){                       

                            $pnv_prop_add = $pnv_row->property_address;
                            
                            if( $pme_row['ID'] == $pnv_row->property_id ){
                                //echo "PNV";
                                $has_pnv_notes = true;                            
                                $pme_prop_with_notes = $pnv_prop_add;
                            }
                        }

                        // hidden        
                        foreach( $hap_res as $index => $hap_row ){   
                                                
                            if( $pme_row['ID'] == $hap_row->api_prop_id ){
                                //echo "Hidden";
                                $is_prop_hidden = true;                                                        
                            }                      

                        }                    


                        // exclude sales property
                        /*if ( $pme_row['SaleAgreementUpdatedOn'] !== "0001-01-01" ) {
                            $is_sales_prop = true;
                        }*/

                        // not connected, no PNV notes and not property sales
                        if( $has_connected == false && $has_pnv_notes == false && $is_sales_prop == false && $is_prop_hidden == false ){
                            //echo "Count";
                            $pnv_need_process_count++;
                            $highlight_red = true;
                        }
                        
                    
                        $table_html .= "<tr style='".( ( $highlight_red == true )?'background-color: green;':'background-color: red;' )."'>
                            <td style='text-align:left; border:1px solid;'>".( ( $highlight_red == true )?$pnv_need_process_count:null )."</td>
                            <td style='text-align:left; border:1px solid;'>{$pme_address}</td>                        
                            <td style='text-align:left; border:1px solid;'>{$crm_connected_prop}</td>
                            <td style='text-align:left; border:1px solid;'>{$pme_row['ID']}</td>
                            <td style='text-align:left; border:1px solid;'>{$pme_prop_with_notes}</td>   
                            <td style='text-align:left; border:1px solid;'>".( ( $is_sales_prop == true )?'Yes':'No' )."</td> 
                            <td style='text-align:left; border:1px solid;'>".( ( $is_prop_hidden == true )?'Yes':'No' )."</td>                    
                        </tr>";            
                        
                    }
                }

                $table_html .= "
                <tr>
                    <td style='text-align:left; border:1px solid;' colspan='2'><b>BULK MATCH NEEDS TO PROCESS COUNT</b></td>
                    <td style='text-align:left; border:1px solid;'><b>{$pnv_need_process_count}</b></td>
                    <td style='text-align:left; border:1px solid;'></td>
                </tr>
                </table>
                </div>
                <div style='clear:both;'></div>
                ";

                echo $table_html;

                
                // check if agency already exist
                $check_sql_str = "
                SELECT COUNT(`pme_upc_id`) AS pme_upc_count
                FROM `pme_unmatched_property_count` 
                WHERE `agency_id` = {$agency_id}
                ";

                $check_sql = $this->db->query($check_sql_str);
                $pme_upc_count = $check_sql->row()->pme_upc_count;

                if( $pme_upc_count > 0 ){ // record found
        
                    // update
                    $update_sql_str = "
                    UPDATE `pme_unmatched_property_count` 
                    SET `count` = {$pnv_need_process_count}
                    WHERE `agency_id` = {$agency_id}
                    ";
                    $this->db->query($update_sql_str);

                    
                }else{

                    // insert 
                    $insert_sql_str = "
                    INSERT INTO 
                    `pme_unmatched_property_count` (
                        `agency_id`,
                        `count`,
                        `created_date`
                    )
                    VALUES (
                        {$agency_id},
                        {$pnv_need_process_count},
                        '{$today}'
                    )
                    ";
                    $this->db->query($insert_sql_str);

                } 
                              

            }

        }

    }
}
