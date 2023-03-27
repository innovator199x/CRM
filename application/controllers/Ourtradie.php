<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ourtradie extends CI_Controller {

    function __construct(){
        parent::__construct();
        ini_set('max_execution_time', 900);
        $this->load->model('ourtradie_model');
        $this->load->model('agency_api_model');
        $this->load->model('api_model');
        $this->load->model('cron_model');
        $this->load->model('properties_model');
    }

    public function index(){
        echo "TEST";
    }

    public function properties(){
        $agency_id = $this->input->get_post('agency_id');
        $api_id    = 6;

        $api = new OurtradieApi();

        $data['agency_name']  = $this->ourtradie_model->getAgencyEmail($agency_id);
        $agency_name = $data['agency_name'][0]->agency_name;
        
        $token['token'] = $this->ourtradie_model->getToken($agency_id, $api_id);
        $access_token   = $token['token'][0]->access_token;
        $tmp_ref_token   = $token['token'][0]->refresh_token;
        $tmp_arr_ref_token = explode("+/-]",$tmp_ref_token);
        //echo $tmp_ref_token;

        $agency_id = $tmp_arr_ref_token[1];
        $_SESSION['ot_agency_id'] = $agency_id;
        //exit();

        $token = array('access_token' => $access_token);

        /*
        $api = new OurtradieApi();

        $token = array('access_token' => $access_token);

        //GetAgencies
		$params = array(
            'Skip' 	 		=> 'No',
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
                    $agency_id = $item['AgencyID'];
                    $_SESSION['ot_agency_id'] = $agency_id;
                }
            }
        }
        */

        //GetAllResidentialProperties
		$params = array(
            'Skip' 	 		=> 'No',
            'Count'     => 'No',
            'AgencyID'  => $agency_id
        );
        $property = $api->query('GetAllResidentialProperties', $params, '', $token, true);

        $data_property = array();
        $data_property = json_decode($property, true);

        $data['property_list'] = array_filter($data_property, function ($v) {
        return $v !== 'OK';
        });

        if(empty($data['property_list'])){
            $this->properties();
        }
        //print_r($data['property_list']);
        //exit();

        return $data['property_list'];
    }

    public function bulk_connect() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = 'Our Tradie Bulk Match';
        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');

        $data['agencyConnectedList'] = $this->ourtradie_model->getConnectedAgencies(); 
        //echo $this->db->last_query();
        //exit();     

        $this->load->view('templates/inner_header', $data);
        $this->load->view('authentication/ourtradie_bulk_connect',$data);
        $this->load->view('templates/inner_footer', $data);
    }

    // GET Ourtradie CRM Property List
    public function ajax_bulk_connect_get_crm_list(){
     
        //$agency_id = 4211;
        $agency_id = $this->input->get_post('agency_id');
        
        $data['propertyList'] = $this->ourtradie_model->getPropertyList($agency_id); 
        //echo $this->db->last_query();
        //exit();

        //print_r($data['propertyList']);

        //echo $this->db->last_query();
        //exit();

        ?>

        <table id="crmProp" class="display table table-striped table-borderless" cellspacing="0" width="100%">
            <thead>
                <tr>		
                    <th class="chk_col">
                        <span class="checkbox">
                            <input type="checkbox" id="check-all" class="check-all">
                            <label for="check-all" class="chk_lbl"></label>
                        </span>
                    </th>							
                    <th class="address_col">Address</th>
                    <th style="display: none;"></th>						
                    <th style="display: none;"></th>
                    <th class="col_crm_btn"></th>
                    <th></th>
                </tr>
            </thead>							
            <tbody>
                <?php 
                    $note_ctr=1;
                    foreach ($data['propertyList'] as $row) { 

                    // sales property
					$sales_txt = ( $row->is_sales == 1 )?'(Sales)':null;
            
                    //$prop_address = trim("{$row->address_2}, {$row->address_3} {$row->state} {$row->postcode} {$sales_txt}");
                    $prop_address = $row->address_2." ".$row->address_3." ".$row->state." ".$row->postcode." ".$sales_txt;
                ?>
                    <tr>	
                        <td class="chk_col">
                            <span class="checkbox">
                                <input type="checkbox" id="check-<?php echo $note_ctr; ?>" class="chk_prop">
                                <label for="check-<?php echo $note_ctr; ?>" class="chk_lbl"></label>
                            </span>
                        </td>										
                        <td class="crmAdd" data-crm_prop_link="<?php echo "{$this->config->item('crm_link')}/view_property_details.php?id={$row->property_id}"; ?>"><?=$prop_address?></td>
                        <td style="display: none;"><?=$row->property_id?></td>											
                        <td style="display: none;" class="sort_index">0</td>   
                        <td>
                            <input type="hidden" class="crm_full_address" value="<?php echo $prop_address; ?>" />
                            <input type="hidden" class="crm_addr_street_num" value="<?php echo $row->address_1; ?>" />
                            <input type="hidden" class="crm_addr_street_name" value="<?php echo $row->address_2; ?>" />                            
                            <input type="hidden" class="crm_addr_suburb" value="<?php echo $row->address_3; ?>" />
                            <input type="hidden" class="crm_addr_state" value="<?php echo $row->state; ?>" />
                            <input type="hidden" class="crm_addr_postcode" value="<?php echo $row->postcode; ?>" />
                            <input type="hidden" class="crm_prop_id" value="<?php echo $row->property_id; ?>" />   
                            
                            <input type="hidden" class="note_btn_class" value="<?php echo "crm_note_btn{$note_ctr}"; ?>" />

                            <?php 
                             // check if already exist
                            $sv_notes_params = array(
                                'property_id' => $row->property_id,
                                'property_source' => 1
                            );
                            if( $this->if_notes_already_exist($sv_notes_params) == true ){ ?>
                                <button type="button" class="btn btn-primary jFaded" disabled="">Pending Verification</button>
                            <?php
                            }else{?>
                                <button type="button" class="btn btn-primary verify_nlm_btn crm_note_btn<?php echo $note_ctr; ?>">PNV</button>
                            <?php
                            }
                            ?>
                        </td>
                        <td><span class="fa fa-arrows-h match_arrow"></span></td>                  								
                    </tr>
                <?php
                    $note_ctr++;
                    }
                ?>
            </tbody>
        </table>
        <script>
        // crm datatable initialize
        $.fn.DataTable.ext.pager.numbers_length = 5;
        var crmTable = $('#crmProp').DataTable( {
        
            'bPaginate': true,
            'pageLength': 50,
            'lengthChange': true,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            'columnDefs': [
                {
                    'targets': [0, 4, 5],
                    'orderable': false
                }
            ],           
            'order': [[1, 'asc']]

        });
        
        
        // sortable rows
        $( "#crmProp" ).sortable({

			items: "tr",
			cursor: 'move',
			opacity: 0.6,
			update: function() {
			}
            
        });
    

        </script>
        <?php

    }//endfct

    //GET Ourtradie Property List
    public function ajax_bulk_connect_get_ourtradie_list(){ 
        $agency_id = $this->input->get_post('agency_id');

        //$agency_id = 4211;
        $hide_pme_archived_prop = $this->input->get_post('hide_pme_archived_prop');
        $show_all_hidden_prop = $this->input->get_post('show_all_hidden_prop');

        $uri = "ajax_bulk_connect_get_ourtradie_list";

        //$pmeList = $this->get_ourtradie_api_property($agency_id);
        $this->checkToken($uri,$agency_id);
        //exit();

        $propertyList = $this->properties();

        if(empty($propertyList)){
            $this->properties();
        }

        else{
            //echo "<pre>";
            //print_r($propertyList);
            //echo "</pre>";
        ?>
        <table id="pmeProp" class="display table table-striped table-borderless" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th class="address_col">Address</th>
                    <th style="display: none;"></th>
                    <th style="display: none;"></th>
                    <th class="col_pme_prop_chk">
                         <span class="checkbox">
                            <input type="checkbox" id="pme_prop_chk_all" />
                            <label for="pme_prop_chk_all"></label>
                        </span>
                    </th>
                    <th class="col_pme_btn"></th>
                </tr>
            </thead>                        
            <tbody>
            <?php 
            $note_ctr = 1;

            //$pme_unique_arr = $this->unique_key($pmeList,'AddressText');
            $pme_unique_arr = $propertyList;

            $isConnectedCheck_str = "
                SELECT `apd`.`api_prop_id` , `p`.`property_id`
                FROM `property` AS `p`
                LEFT JOIN `api_property_data` AS `apd` ON p.`property_id` = apd.`crm_prop_id`
                WHERE apd.api_prop_id IS NOT NULL
                AND `apd`.`api` = 6
            ";

            $isConnectedCheck_sql = $this->db->query($isConnectedCheck_str);
            $arrConnected = $isConnectedCheck_sql->result_array();

            $isConnectArr = array();
            foreach ($arrConnected as $val) {
                array_push($isConnectArr, $val['api_prop_id']);
            }

            $pme_unique_arr_new = array();
            foreach ($pme_unique_arr['data'] as $key => $row) {
               
                // hide connected properties
                if (!in_array($row['ID'], $isConnectArr)) {
                    //echo "NOT FOUND";
                    array_push($pme_unique_arr_new, $row);
                }

                // exclude sales property
                /*if ( $row->SaleAgreementUpdatedOn !== "0001-01-01" ) {
                    unset($pme_unique_arr[$key]);
                }*/
            }  
        
            //echo "CONNECTED!!";
            foreach($pme_unique_arr_new as $pme_prop_obj ){
            }


            // re-create PMe array for joining archived list
            $pme_prop_arr = [];
            foreach( $pme_unique_arr_new as $pme_prop_obj ){
                $pme_prop_arr[] = $pme_prop_obj;
            }

            /*
            // get PMe archived properties                        
            $pme_archived_prop = $this->ourtradie_model->get_all_archived_properties($agency_id);                        
            foreach( $pme_archived_prop as $pme_archived_prop_obj ){
                $pme_prop_arr[] = $pme_archived_prop_obj;
            }
            */
            //$pme_prop_arr[] = $data['propertyList'];
            foreach ($pme_prop_arr as $item) { 
                //print_r($item);

                $pnv_id = null;
                $ignore_issue = null;
                $pnv_note = null;

                // get pnv data
                $sel_query = "
                    pnv.`pnv_id`,
                    pnv.`note`,
                    pnv.`ignore_issue`
                ";
                $params = array(
                    'sel_query' => $sel_query,                                                                
                    'active' => 1,

                    'property_id' => $item['ID'],
                    //'property_source' => 7,

                    'limit' => 1,
                    'offset' => 0,

                    'sort_list' => array(   
                        array(
                            'order_by' => 'pnv.`pnv_id`',
                            'sort' => 'DESC'
                        )               
                    ),
                                
                    'display_query' => 0
                );   
                $pnv_sql = $this->properties_model->get_properties_needs_verification($params);      
                //echo $this->db->last_query();
                
                //exit();

                if( $pnv_sql->num_rows() > 0 ){
                    $pnv_row = $pnv_sql->row();
                    $pnv_id = $pnv_row->pnv_id;
                    $ignore_issue = $pnv_row->ignore_issue;
                    $pnv_note = $pnv_row->note;
                }

                /*$hide_row = false;
                if( $hide_pme_archived_prop == 1 ){
                    if( $row->IsArchived == true ){
                        $hide_row = true;
                    }
                }
                */

                // check if property is set as hidden
                $api_id = 6; // Ourtradie
                $agency_api_model_params = array(
                    'api_prop_id' => $item['ID'],                                                                
                    'agency_id' => $agency_id,
                    'api_id' => $api_id,
                );                                                       
                

                $is_api_property_hidden = $this->agency_api_model->is_api_property_hidden($agency_api_model_params);
                //exit();

                if( $is_api_property_hidden == true && $show_all_hidden_prop != 1 ){
                    $hide_row = true;
                }  
                else{
                    $hide_row = false;
                }
                /*
                echo "All Show hidden >>>>>>>>".$show_all_hidden_prop;
                */

                if( $ignore_issue != 1 && $hide_row == false ){
                    $ot_full_address1 = trim("{$item->address_1} {$item->address_2}, {$item->address_3} {$item->state} {$item->postcode} {$sales_txt}");
                    //$address2 = trim($item->address_2,"Street");
                ?>
                    <tr id="tenant-list-tbl-row-<?php echo $item['ID']; ?>" class="<?php echo ( $row->IsArchived == true )?'pme_archived_row bg-danger text-white':null; ?>">
                        <td class="pmeAdd"><?php echo $item['Address1']." ".$item['Address2']." ".$item['Suburb']." ".$item['State']." ".$item['Postcode']; ?></td>
                        <td id="tenant-list-id-tbl-row" style="display: none;"><?php echo $item['ID']; ?></td>
                        <td style="display: none;" class="sort_index">0</td>
                        <td>
                            <span class="checkbox">
                                <input type="checkbox" id="pme_prop_chk-<?php echo $item['ID']; ?>" class="pme_prop_chk">
                                <label for="pme_prop_chk-<?php echo $item['ID']; ?>"></label>
                            </span>
                        </td>
                        <td>
                            <input type="hidden" class="ot_full_address" value="<?php echo trim($item['Address1'],"Street").$item['Suburb']." ".$item['State']." ".$item['Postcode']; ?>" />
                            <input type="hidden" class="ot_addr_address1" value="<?php echo $item['Address1']; ?>" />
                            <input type="hidden" class="ot_addr_address2" value="<?php echo $item['Address2']; ?>" />
                            <input type="hidden" class="ot_addr_suburb" value="<?php echo $item['Suburb']; ?>" />                            
                            <input type="hidden" class="ot_addr_postalcode" value="<?php echo $item['Postcode']; ?>" />
                            <input type="hidden" class="ot_addr_state" value="<?php echo $item['State']; ?>" />
                            <input type="hidden" class="ot_addr_country" value="<?php echo 'Australia'; ?>" />
                            <!--<input type="hidden" class="ot_addr_country" value="<?php //echo 'Australia'; ?>" /> -->

                            <input type="hidden" class="ot_prop_id" value="<?php echo $item['ID']; ?>" />
                            <input type="hidden" class="ot_api_prop_id" value="<?php echo $item['ID']; ?>" />
                            <input type="hidden" class="key_number" value="<?php echo $item['KeyNumber']; ?>" />

                            <?php 
                                //print_r($item['Tenant_Contacts']);
                            ?>

<input type="hidden" class="ot_tenant_list_hidden_table" value='<?php echo json_encode($item['Tenant_Contacts']); ?>' />

                            <input type="hidden" class="ot_tenant_fname" value="<?php echo $item['Tenant_Contacts']['0']['FirstName']; ?>" />
                            <input type="hidden" class="ot_tenant_lname" value="<?php echo $item['Tenant_Contacts']['0']['LastName']; ?>" />
                            <input type="hidden" class="ot_tenant_email" value="<?php echo $item['Tenant_Contacts']['0']['Email']; ?>" />
                            <input type="hidden" class="ot_tenant_mobile" value="<?php echo $item['Tenant_Contacts']['0']['Mobile']; ?>" />
                            <!--
                            <input type="hidden" class="is_archived" value="<?php //echo $row->IsArchived; ?>" />
                            -->

                            <input type="hidden" class="note_btn_class" value="<?php echo "pme_note_btn{$note_ctr}"; ?>" />
                            
                        
                            <button type="button" class="btn btn-primary btn_add_prop_indiv my-1">Add Property</button>

                            <?php                                                                                     
                            if( $pnv_note != '' ){  // has notes                             
                            ?>                                
                                <button type="button" class="btn btn-primary view_note_btn jFaded my-1">View</button>
                            <?php
                            }else{ ?>
                                <button type="button" class="btn btn-primary btn_note pme_note_btn<?php echo $note_ctr; ?>">Note</button>
                            <?php
                            }
                            
                            if( $is_api_property_hidden == true ){ ?>
                                <button type="button" class="btn btn-primary btn_unhide_api_prop my-1">Unhide</button>
                            <?php
                            }else{ ?>
                                <button type="button" class="btn btn-success btn_hide_api_prop my-1">Hide</button>
                            <?php
                            }
                            ?>   

                            <input type="hidden" class="pnv_id" value="<?php echo $pnv_id; ?>" />
                            
                        </td>
                    </tr>
                <?php
                }
                $i++;
                $note_ctr++;
            }
            ?>
            </tbody>
        </table>
        <div id="btn_add_prop_div">
            <button type="button" id="btn_add_prop" class="btn btn-primary">Add Property</button>
        </div>
        <?php
            }
        ?>
        <script>
        // pme datatable initialize
        $.fn.DataTable.ext.pager.numbers_length = 5;
        var pmeTable = $('#pmeProp').DataTable({

            'bPaginate': true,
            'pageLength': 50,
            'lengthChange': true,  
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],  
            'columnDefs': [
                {
                    'targets': [3,4],
                    'orderable': false
                }
            ],       
            'order': [[0, 'asc']]

        });

      
        // sortable rows
        $( "#pmeProp" ).sortable({

            items: "tr",
            cursor: 'move',
            opacity: 0.6,
            update: function() {
            }

        }); 
      
        
        </script>
        <?php

    }//endfct    

    public function checkToken($uri,$agency_id){

        $unixtime 	= time();
        $now 		= date("Y-m-d H:i:s",$unixtime);

        $api_id = 6;
        //$agency_id = 4211;
        //$agency_id  = $this->session->agency_id;

        $token['token'] = $this->ourtradie_model->getToken($agency_id, $api_id);

        $created         = $token['token'][0]->created;
        $expiry          = $token['token'][0]->expiry;
        $expired         = strtotime($now) - strtotime($expiry);

        $tmp_refresh_token   = $token['token'][0]->refresh_token;
        $tmp_arr_refresh_token = explode("+/-]",$tmp_refresh_token);
        $refresh_token = $tmp_arr_refresh_token[0];

        //$refresh_token   = $token['token'][0]->refresh_token;
        //$refresh_token = "1654578cef286cf59e4dad1634129c56e42cfbe6-d7156da53a0ec07cd4970f76abb9def081ac61d9";

        if($expired > 0){

            $options = array(
                'grant_type'      => 'refresh_token',
                'refresh_token'   =>  $refresh_token,
                'client_id'		  => 'br6ucKvcPRqDNA1V2s7x',
                'client_secret'	  => 'd5YOJHb6EYRw5oypl73CJFWGLob5KB9A',
                'redirect_uri'	  => 'https://crmdevci.sats.com.au/ourtradie/checkToken/'.$uri
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
                
                /*
                if($uri == "ajax_bulk_connect_get_ourtradie_list"){
                    $this->ajax_bulk_connect_get_ourtradie_list();
                }
                */

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

    }//endfct

    public function refreshToken(){
        $agency_id  = $this->session->agency_id;
        $uri = $this->uri->segment(3);

        //$agency_id = $this->uri->segment(4);
        $api_id = 6;

        $unixtime = time();
        $now = date("Y-m-d H:i:s",$unixtime);

        $refTokenArray = array();
        $refTokenArray = $_GET;

        if(!empty($refTokenArray['access_token'])){
        $access_token   = $refTokenArray['access_token'];
        $refresh_token  = $refTokenArray['refresh_token'];
        $expiry         = date('Y-m-d H:i:s',strtotime('+3600 seconds'));
        $created        = $now;

        $update_data = array(
            'access_token'    => $access_token,
            'refresh_token'   => $refresh_token,
            'created'         => $created,
            'expiry'          => $expiry,
        );

        $this->ourtradie_model->updateToken($agency_id, $api_id, $update_data);
        }

        if($uri == "ajax_bulk_connect_get_ourtradie_list"){
            $this->ajax_bulk_connect_get_ourtradie_list();
        }

        if($uri == "property"){
            $contactId = $_SESSION['contactId'];
            $agency_id = $_SESSION['agency_id'];
            $this->property($contactId, $agency_id);
        }

        if($uri == "properties_needs_verification"){
            redirect('/property_me/'.$uri);
        }

    }//endfct

    // check if note already exist
    public function if_notes_already_exist($params) {

        if( $params['property_id'] != '' && $params['property_source'] !='' ){

            $this->db->select('pnv_id');
            $this->db->from('properties_needs_verification');
            $this->db->where('property_id', $params['property_id']);
            $this->db->where('property_source', $params['property_source']);
            $query = $this->db->get();

            $pnv_count = $query->num_rows();

            if ($pnv_count > 0 ) {
                return true;
            }else {
                return false;
                
            }

        }         

    }//endfct

    // save note
    public function bulk_connect_save_note(){

        $note   = $this->input->get_post('note');
        $pnv_id = $this->input->get_post('pnv_id');
        $property_id = $this->input->get_post('property_id');
        $agency_id   = $this->input->get_post('agency_id');
        $property_address = $this->input->get_post('property_address');
        $property_source  = $this->input->get_post('property_source');

        //echo $note;
        //echo $pnv_id;
        //exit();

        /* For testing data
        $pnv_id           = "";
        $property_source  = 1;
        $property_id      = 39;
        $property_address = "02 Brennan Street, SATS Town NSW 2015";
        $agency_id        = 1448;
        $note             = "Contact agency to verfiy NLM";
        */

        if( $pnv_id > 0 ){ // update

            $data = array(
                'note' => $note
            );
            
            $this->db->where('pnv_id', $pnv_id);
            $this->db->update('properties_needs_verification', $data);

        }else{ // add

            // check if already exist
            $sv_notes_params = array(
                'property_id' => $property_id,
                'property_source' => $property_source
            );
            $res = $this->if_notes_already_exist($sv_notes_params);

            if( $res == false ) {
               
                $data = array(
                    'property_source' => $property_source,
                    'property_id' => $property_id,
                    'property_address' => $property_address,
                    'agency_id' => $agency_id,
                    'note' => $note,
                    'created_date' => date('Y-m-d H:i:s'),
                );

                $this->db->insert('properties_needs_verification', $data);
                echo $pnv_id = $this->db->insert_id(); 
                
            }

        }

    }//endfct

    // bulk connect add property function 
    public function bulk_connect_add_property(){

        $this->load->model('properties_model');
        $this->load->model('agency_model');

        $agency_id = $this->input->get_post('agency_id');
        $ot_prop_arr = $this->input->get_post('ot_prop_arr');
        //echo $agency_id;
        /*
        echo "<pre>";
        print_r($ot_prop_arr);
        echo "</pre>";
        exit();
        */

        //$agency_id = 4211;
        //$ot_prop_arr[] = json_decode('{"ot_full_address":"Sampe Address1 Property","ot_addr_suburb":"Sample Suburb","ot_addr_state":"VIC","ot_addr_postalcode":"1234","ot_addr_country":"Australia","ot_prop_id":"1132441","key_number":"0","tenants_name":"Leonardo","tenants_email":"chopstick.toto@gmail.com","tenants_mobile":"0926132947"}');
        //$ot_prop_arr[] = json_decode("'".$ot_prop_data."'");
        $disable_add = 0;
        $dup_arr = [];
        $ret_str = '';

        foreach( $ot_prop_arr as $index => $item){
            $row = json_decode($item);
            $address1  = $row->ot_addr_address1;
            $address2  = $row->ot_addr_address2;
            $suburb   = $row->ot_addr_suburb;
            $state    = $row->ot_addr_state;
            $postcode = $row->ot_addr_postalcode;
            $country  = $row->ot_addr_country;
            $ot_prop_id = $row->ot_prop_id;
            $key_number = $row->key_number;
            $tenants_name   = $row->tenants_name;
            $tenants_email  = $row->tenants_email;  
            $tenants_mobile = $row->tenants_mobile;  
            
            //echo $tenants_name;
            //exit();
           
            $street_arr = [];

            //$duplicate_query = $this->properties_model->check_duplicate_property($street_num_fin,$street_name_fin,$suburb,$state,$postcode);
            
            $check_dup_params = array(
                'address_1'  => $address1,
                'address_2'  => $address2,
                'suburb'   => $suburb,
                'state'    => $state,
                'postcode' => $postcode
            );
            $duplicate_query = $this->ourtradie_model->check_duplicate_full_address($check_dup_params);
            //echo $this->db->last_query();
            //exit();

    
            if( $duplicate_query->num_rows()>0 ){ // existing property found
    
                $duplicate_row = $duplicate_query->row_array();            
                $dup_agency_id = $duplicate_row['agency_id'];

                //$dup_agency_name = (  $dup_agency_id == $agency_id )?'this Agency':$duplicate_row['agency_name'];
                //$dup_arr[] = "<a href='/property_me/property/{$duplicate_row['property_id']}/{$dup_agency_id}' target='_blank'>{$pme_full_address}</a> in agency <a href='{$this->config->item('crm_link')}/view_agency_details.php?id={$duplicate_row['agency_id']}'>{$dup_agency_name}</a>";
    
                
                $dup_arr[] = array(
                    'dup_property_id'      => $duplicate_row['property_id'], 
                    'dup_property_address' => "{$duplicate_row['p_address_1']} {$duplicate_row['p_address_2']}, {$duplicate_row['p_address_3']} {$duplicate_row['p_state']} {$duplicate_row['p_postcode']}", 
                    'dup_prop_deleted'     => $duplicate_row['is_nlm'],      
                    'dup_agency_id'        => $duplicate_row['agency_id'],                                          
                    'dup_agency_name'      => $duplicate_row['agency_name'],
                    'ot_prop_id'           => $ot_prop_id                                                 
                );
           
            }
            else{

                if( $disable_add != 1 ){

                    // Hume Community Housing Association
                    $prop_comments = '';
                    if( $agency_id==1598 ){            
                        $prop_comments = 'Please install 9vLi or 240v only. DO NOT INSTALL 240vLi';            
                    }
        
                    // INSERT PROPERTY
                    // removed inserting lat and lng from API bec sometimes they have some weird coordinate like -999
                    // better leave it empty bec tech runs has auto-inserts of coordinate if they are empty on load                  
                    $property_data = array(
                        'agency_id' => $agency_id,
                        'address_1'   => $address1,
                        'address_2'   => $address2,
                        'address_3'    => $suburb,
                        'state'     => $state,
                        'postcode'  => $postcode,
                        'added_by'  => $this->session->staff_id,
                        'key_number'         => $key_number,
                        'landlord_firstname' => $tenants_name,
                        'landlord_mob'       => $tenants_mobile,
                        'landlord_email'     => $tenants_email,
                        'comments'           => $prop_comments
                    );
                    //print_r($property_data);
                    //exit();

                    $add_property = $this->properties_model->add_property($property_data);
                    //echo $this->db->last_query();
                    //exit();
                    
                    $prop_insert_id = $this->db->insert_id();
                    
                    $separated_data = array(
                        'crm_prop_id' => $prop_insert_id,
                        'api'         => 6,
                        'api_prop_id' => $ot_prop_id
                    );
                    
                    $add_separated_data = $this->properties_model->add_data_property($separated_data);

                    if( $add_property && !empty($prop_insert_id) ){
        
                        // insert property log
                        $params = array(
                            'title' => 2, //New Property Added
                            'details' => 'Added from Ourtradie Bulk Match',
                            'display_in_vpd' => 1,
                            'agency_id' => $agency_id,
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $prop_insert_id
                        );
                        $this->system_model->insert_log($params); 
        
                        // get agency services
                        /*$params = array(
                            'sel_query' => "
                                agen_serv.`service_id`,
                                agen_serv.`price`
                            ",
                            'agency_id' => $agency_id
                        );
                        $agency_services_sql = $this->agency_model->get_agency_services($params);

                        //echo $this->db->last_query();
                        //exit();
                        
                        foreach( $agency_services_sql->result() as $agen_serv ){
        
                            // insert property services ----- 
                            // insert all services as No Response
                            $ps_service = 2; // No Response
                            $prop_services_array = array(
                                'property_id' => $prop_insert_id,
                                'alarm_job_type_id' => $agen_serv->service_id,
                                'service' => $ps_service,
                                'price' => $agen_serv->price,
                                'status_changed' => date("Y-m-d H:i:s"),
                            );
        
                            $this->properties_model->add_property_services($prop_services_array);
                        } 
                        */           
                    }
                }                                                          
            }           
        }        

        echo json_encode($dup_arr);
    }


    /**
     * Load Ourtradie connect page, this allow you to connect CRM properties to PMe properties
     * @param  int $contactId The int to find CRM property by ID
    */ 
    public function property($contactId = 0, $agency_id) {

        $api_id = 6; // Ourtradie
        $flag = 0;

        $_SESSION['contactId'] = $contactId;
        $_SESSION['agency_id'] = $agency_id;

        $uri = "property";

        /*
        echo $contactId;
        echo "<br /><br />";
        echo $agency_id;
        */
        //exit();


        $this->checkToken($uri,$agency_id);

        $tokens = $this->ourtradie_model->getToken($agency_id, $api_id);
        $access_token = $tokens[0]->access_token;

        $realPropId = $contactId;
        $data['title'] = 'OurTradie Tenant & Property Details';

        $accessTokenUrl = $this->input->get_post('apiUrl');

        $tmp_otprop_id = $this->ourtradie_model->get_ourtradie_prop_id($contactId);
        //echo $this->db->last_query();

        $ourtradie_prop_id = $tmp_otprop_id[0]->api_prop_id;

        //echo $ourtradie_prop_id;
        //exit();

        if($flag == 0){
            $_SESSION['ourtradie_prop_id'] = $ourtradie_prop_id;
        }

        if ($ourtradie_prop_id != '') { // if Ourtradie prop ID exist, means already connected

            $flag = 1;
            $contactId = $pmeId;
            $_SESSION['ourtradie_prop_id'] = $ourtradie_prop_id;
            $_SESSION['flag'] = $flag;

            if (isset($access_token)) {

                $api = new OurtradieApi();

                $token = array('access_token' => $access_token);
                
                $data['agency_name']  = $this->ourtradie_model->getAgencyEmail($agency_id);
                $agency_name = $data['agency_name'][0]->agency_name;

                $token['token'] = $this->ourtradie_model->getToken($agency_id, $api_id);
                $access_token   = $token['token'][0]->access_token;
                $tmp_ref_token   = $token['token'][0]->refresh_token;
                $tmp_arr_ref_token = explode("+/-]",$tmp_ref_token);
                //echo $tmp_ref_token;

                $ot_agency_id = $tmp_arr_ref_token[1];
                $_SESSION['ot_agency_id'] = $agency_id;

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

                        if($row['ID'] == $ourtradie_prop_id){
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
                            $data['api_agency'] = $row['Agency_Contacts']['0'];
                            $data['api_tenants'] = $row['Tenant_Contacts'];
                        }
                    }
                }

                $data['crm_property'] = $this->ourtradie_model->getProperty($realPropId);
                $data['crm_tenants'] = $this->ourtradie_model->getTenants($realPropId);
                $data['property_id'] = $realPropId;                 
                
                $this->load->view('templates/inner_header', $data);
                $this->load->view('authentication/ajax_tenant_detail_ourtradie',$data); // already connected page
                $this->load->view('templates/inner_footer', $data);

                
            }else {
                echo "access token doesn't exist";
            }
        }

        else { 
            //not connected yet
            //echo "ELSE";
            //exit();

            $tmp_contactID = $this->uri->segment(3);

            if (isset($access_token)) {
                $api_id = 6; // Ourtradie

                $tokens = $this->ourtradie_model->getToken($agency_id, $api_id);
                //print_r($tokens);

                $access_token   = $tokens[0]->access_token;
                $tmp_ref_token   = $tokens[0]->refresh_token;
                $tmp_arr_ref_token = explode("+/-]",$tmp_ref_token);
                //echo $tmp_ref_token;

                $agency_id = $tmp_arr_ref_token[1];
                $_SESSION['ot_agency_id'] = $agency_id;

                $api = new OurtradieApi();

                $token = array('access_token' => $access_token);

                //GetAllResidentialProperties
                $params = array(
                    'Skip' 	 		=> 'No',
                    'Count'     => 'No',
                    'AgencyID'  => $agency_id
                );
                $property = $api->query('GetAllResidentialProperties', $params, '', $token, true);

                $data_property = array();
                $data_property = json_decode($property, true);

                $data['property_list'] = array_filter($data_property, function ($v) {
                return $v !== 'OK';
                });

                //print_r($data['property_list']);
                //exit();

                $tmp_propertyID = $_SESSION['contactId'];

                $ourtradie_prop_id = $_SESSION['ourtradie_prop_id'];

                if($ourtradie_prop_id == ""){

                    //echo "TEST";
                    //exit();

                    $data['prop_details'] = $this->ourtradie_model->getProperty($tmp_contactID);

                    $address2 = $data['prop_details'][0]->p_address_2;
                    $address3 = $data['prop_details'][0]->p_address_3;
                    $state    = $data['prop_details'][0]->p_state;
                    $postcode = $data['prop_details'][0]->p_postcode;

                    foreach ($data['property_list'] as $prop1) {
                        foreach ($prop1 as $row) {
                            
                            if($row['Address1'] == $address2 && $row['Suburb'] == $address3 && $row['State'] == $state && $row['Postcode'] == $postcode){
                                /*
                                [ID] => 1132441
                                [Address1] => Sampe Address1 Property
                                [Address2] => Sampe Address2 Property
                                [Suburb] => Sample Suburb
                                [State] => VIC
                                [Postcode] => 1234
                                [KeyNumber] => 0
                                */
                                //echo "PROPERTY ID=>>>";
                                $ourtradie_prop_id = $row['ID'];
                            }
                        }
                    }

                }
                    
                $cart = array();
                foreach ($data['property_list'] as $prop) {
                    foreach ($prop as $row) {

                        $data['property'] = $array = array('ID' => $row['ID'], 'Address1' =>$row['Address1'], 'Address2' =>$row['Address2'], 'Suburb' =>$row['Suburb'], 'State' =>$row['State'], 'Postcode' =>$row['Postcode'], 'KeyNumber' =>$row['KeyNumber'], 'tenants' => $row['Tenant_Contacts']);
                        //$data['property'] = Array ( [0] => stdClass Object ( [property_id] => 490 [p_address_1] => 500 George Street, Sydney NSW, [p_address_2] => Street Name [p_address_3] => NSW [p_state] => QLD [p_postcode] => 1223 [lat] => -33.8723996 [lng] => 151.2071178 [country_name] => Australia ) );

                        array_push($cart, $data['property']);

                        //$data['api_property'] = $data['property'];
                        array_push($data['api_property'],$data['property']);
                        //if($row['ID'] == $ourtradie_prop_id){
                            /*
                            [ID] => 1132441
                            [Address1] => Sampe Address1 Property
                            [Address2] => Sampe Address2 Property
                            [Suburb] => Sample Suburb
                            [State] => VIC
                            [Postcode] => 1234
                            [KeyNumber] => 0
                            */
                            //$data['property'] = $array = array('ID' => $row['ID'], 'Address1' =>$row['Address1'], 'Address2' =>$row['Address2'], 'Suburb' =>$row['Suburb'], 'State' =>$row['State'], 'Postcode' =>$row['Postcode'], 'KeyNumber' =>$row['KeyNumber']);
                            //$data['property'] = Array ( [0] => stdClass Object ( [property_id] => 490 [p_address_1] => 500 George Street, Sydney NSW, [p_address_2] => Street Name [p_address_3] => NSW [p_state] => QLD [p_postcode] => 1223 [lat] => -33.8723996 [lng] => 151.2071178 [country_name] => Australia ) );
                            //$data['api_property'] = $data['property'];
                        //}
                    }
                }
                

                $this->db->select('p.property_id, p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, a.agency_id');
                $this->db->from('`property` AS p');
                $this->db->join('`agency` AS a', 'p.`agency_id` = a.`agency_id`', 'left');
                $this->db->where('p.property_id', $realPropId);
                $query = $this->db->get();
                $propDet =  $query->row();

                //$data['otList'] = $data['api_property'];
                $data['otList'] = $cart;
                $data['propDet'] = $propDet;
                $this->load->view('templates/inner_header', $data);
                $this->load->view('authentication/ourtradie_connect',$data); // search and match Pme properties
                $this->load->view('templates/inner_footer', $data);

            }else {
                if ($contactId !== 0) {
                    $sess_arr = array(
                        'pme_property_id' => $realPropId
                    );
                    $this->session->set_userdata($sess_arr);
                    redirect('/ourtradie/bulk_connect', 'refresh');
                }else {
                    //die('You do not have permission');
                    echo "contactId 2: {$contactId}";
                }
            }
        }
    }

    /**
     * Remove Ourtradie ID associated with a CRM property
     * @return true or false
    */ 
    public function ajax_function_unlink_property() {
        //$ourtradieId = $this->input->get_post('ourtradieId');
        $crmId       = $this->input->get_post('crmId');

        $updateData = array(
            'api_prop_id' => null
        );
        $this->db->where('crm_prop_id', $crmId);
        $this->db->update('api_property_data', $updateData);

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));

        // insert property log
        $params = array(
            'title' => 69, // Ourtradie API
            'details' => 'Property <b>Unlinked</b> to <b/>Ourtradie</b>',
            'display_in_vpd' => 1,            
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $crmId
        );
        $this->system_model->insert_log($params);
    }

    /**
     * This add PMe ID to the CRM properties that is bulk connected
    */ 
    public function bulk_connect_all() {

        $agency_filter = $this->input->get_post('agency_id');
        $crmArr = $this->input->get_post('crmArr');
        $pmeArr = $this->input->get_post('pmeArr');
        $connect_deleted_nlm_prop = $this->input->get_post('connect_deleted_nlm_prop');

        //$property_id = 688;
        //$status = $this->properties_model->payableCheck($property_id);


        for ($i=0; $i < count($pmeArr); $i++) { 

            //$status = $this->properties_model->payableCheck($crmArr[$i]);

            // if property service serviced to SATS and propert is NLM, update property service to NR and clear NLM
            $this->db->query("
            UPDATE `property_services` AS ps
            LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
            SET 
                ps.`service` = 2,
                ps.`is_payable` = 0,
                p.`is_nlm` = NULL,
                p.agency_deleted = 0,
                p.`nlm_timestamp` = NULL
            WHERE ps.`property_id` = {$crmArr[$i]}
            AND ps.`service` = 1
            AND p.`is_nlm` = 1
            ");
            $updated = $this->db->affected_rows();

            if( $updated > 0 ){

                // insert property log
                $params = array(
                    'title' => 69, // PMe API
                    'details' => "Property services were updated to No Response if the service was SATS, and the property restored from it's NLM status",
                    'display_in_vpd' => 1,            
                    'created_by_staff' => $this->session->staff_id,
                    'property_id' => $crmArr[$i]
                );
                $this->system_model->insert_log($params);

            }

            // Insert job log
            if( $connect_deleted_nlm_prop == 1 ){
                
                $log_title = 65; // Property Update
                $log_details = "Property was restored from NLM by connecting on <b>OurProperty</b> bulk match.";
                $log_params = array(
                    'title' => $log_title, 
                    'details' => $log_details,
                    'display_in_vpd' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'property_id' => $crmArr[$i]
                );
                $this->system_model->insert_log($log_params);

            }

            // insert property log
            $params = array(
                'title' => 89, // OurProperty API
                'details' => 'Property <b>Linked</b> to <b/>OurProperty</b> on Bulk Match',
                'display_in_vpd' => 1,            
                'created_by_staff' => $this->session->staff_id,
                'property_id' => $crmArr[$i]
            );
            $this->system_model->insert_log($params);

            $check = $this->properties_model->apiCheck($crmArr[$i]);

            if(empty($check)){
                $updateData_Api = array(
                    'api_prop_id' => $pmeArr[$i],
                    'crm_prop_id' => $crmArr[$i],
                    'api'         => 6
                );
                $this->db->insert('api_property_data', $updateData_Api);
            }
            else{
                $updateData_Api = array(
                    'api_prop_id' => $pmeArr[$i]
                );
                $this->db->where('crm_prop_id', $crmArr[$i]);
                $this->db->update('api_property_data', $updateData_Api);
            }
        }

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));
    }

    /**
     * Add new tenant to a CRM property
     * @return true or false
    */ 
    public function ajax_function_tenants() {
        $property_id      = $this->input->get_post('property_id');
        $tenant_firstname = $this->input->get_post('tenant_firstname');
        $tenant_lastname = $this->input->get_post('tenant_lastname');
        $tenant_mobile    = $this->input->get_post('tenant_mobile');
        $tenant_email     = $this->input->get_post('tenant_email');
        $active           = $this->input->get_post('active');

        //echo $property_id.$tenant_firstname.$tenant_mobile.$tenant_email.$active;
        //exit();

        $insertStat = false;

        $params = array(
                'tenant_firstname' => $tenant_firstname,
                'tenant_lastname' => $tenant_lastname,
                'property_id'      => $property_id
            );
        $isExist = $this->check_tenant_if_exist($params);
        //echo $isExist;
        //exit();

        if ($isExist == false) {
            $data = array(
                'property_id'      => $property_id,
                'tenant_firstname' => $tenant_firstname,
                'tenant_lastname' => $tenant_lastname,
                'tenant_mobile'    => $tenant_mobile,
                'tenant_email'     => $tenant_email,
                'active'           => $active
            );
            $this->db->insert('property_tenants', $data);
        }
        $insert_id = $this->db->insert_id();
        $insertStat = ($this->db->affected_rows() != 1) ? false : true;

        echo json_encode(array("isExist" => $isExist, "insertStat" => $insertStat, "insertId" => $insert_id));
    }

    /**
     * Check if a tenant in a CRM property exist
     * @return true or false
    */ 
    public function check_tenant_if_exist($params) {
        $isExist = false;
        $this->db->select('*');
        $this->db->from('property_tenants');
        $this->db->where('property_id', $params['property_id']);
        $this->db->where('active', 1);
        $query = $this->db->get();
        $res = $query->result_array();
        foreach ($res as $val) {
            if ($val['tenant_firstname'] == $params['tenant_firstname']) {
                if ($val['tenant_lastname'] == $params['tenant_lastname']) {
                    $isExist = true;
                }
            }
        }
        return $isExist;
    }

    /**
     * Update a tenant in a CRM property
     * @return true or false
    */ 
    public function ajax_function_tenants_edit() {

        $tenant_id        = $this->input->get_post('tenant_id');
        $tenant_firstname = $this->input->get_post('tenant_firstname');
        $tenant_mobile    = $this->input->get_post('tenant_mobile');
        $tenant_email     = $this->input->get_post('tenant_email');
        $active           = $this->input->get_post('active');

        $updateData = array(
            'tenant_firstname' => $tenant_firstname,
            'tenant_mobile'    => $tenant_mobile,
            'tenant_email'     => $tenant_email
        );

        $this->db->where('property_tenant_id', $tenant_id);
        $this->db->update('property_tenants', $updateData);

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));
    }

    /**
     * Delete a tenant in a CRM property
     * @return true or false
    */ 
    public function ajax_function_tenants_delete() {
        $tenant_id = $this->input->get_post('tenant_id');
        $updateData = array(
            'active' => 0
        );
        $this->db->where('property_tenant_id', $tenant_id);
        $this->db->update('property_tenants', $updateData);
        // $updateStat = true;
        // echo json_encode(array("updateStat" => $updateStat));
        $updateStat = ($this->db->affected_rows() != 1) ? false : true;
        echo json_encode(array("updateStat" => $updateStat, "updatedId" => $tenant_id));
    }

    /**
     * Add a Ourtradie ID to a CRM property
     * @return true or false
    */ 
    public function ajax_function_link_property() {
        $otId = $this->input->get_post('otId');
        $crmId = $this->input->get_post('crmId');

        /*echo $otId;
        echo "<br />";
        echo $crmId;
        exit();
        */

        $updateData = array(
            'api_prop_id' => $otId
        );
        $this->db->where('crm_prop_id', $crmId);
        $this->db->update('api_property_data', $updateData);

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));

        // insert property log
        $params = array(
            'title' => 69, // Ourtradie API
            'details' => 'Property <b>Linked</b> to <b/>Ourtradie</b>',
            'display_in_vpd' => 1,            
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $crmId
        );
        $this->system_model->insert_log($params);
    }

    public function get_ourtradie_property(){

        $ot_prop_id = $this->input->get_post('ot_prop_id');
        $agency_id  = $this->input->get_post('agency_id');

        //echo $ot_prop_id;
        //echo $agency_id;
        //exit();


        $api_id = 6; // Ourtradie

        //$tokens = $this->ourtradie_model->getToken($agency_id, $api_id);
        $token['token'] = $this->ourtradie_model->getToken($agency_id, $api_id);
        $access_token   = $token['token'][0]->access_token;
        $tmp_ref_token   = $token['token'][0]->refresh_token;
        $tmp_arr_ref_token = explode("+/-]",$tmp_ref_token);
        //echo $tmp_ref_token;

        $ot_agency_id = $tmp_arr_ref_token[1];

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
                //print_r($row);

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
                    $data['api_agency'] = $row['Agency_Contacts']['0'];
                    $data['api_tenants'] = $row['Tenant_Contacts'];
                }
            }
        }
        echo json_encode($data['api_property']);
    }

    // check if note already exist
    public function ajax_get_pnv_note() {

        $this->load->model('properties_model');

        $pnv_id = $this->input->get_post('pnv_id');

        $sel_query = "pnv.`note`";
        $params = array(
            'sel_query' => $sel_query,                                                                
            'active' => 1,
            'pnv_id' => $pnv_id,

            'limit' => 1,
            'offset' => 0,

            'sort_list' => array(	
				array(
					'order_by' => 'pnv.`pnv_id`',
					'sort' => 'DESC'
				)				
            ),
                        
            'display_query' => 0
        );
        
        $sql = $this->properties_model->get_properties_needs_verification($params);     
        $row = $sql->row();  
        echo $row->note;

    }
    
    public function ajax_pnv_connect_pme_prop() {

        $agency_id        = $this->input->get_post('agency_id');
        $from_agency_name = $this->input->get_post('from_agency_name');
        $to_agency_name   = $this->input->get_post('to_agency_name');
        $crm_prop_id      = $this->input->get_post('crm_prop_id');
        $ot_prop_id      = $this->input->get_post('ot_prop_id');
        $pnv_id           = $this->input->get_post('pnv_id');

        if ( $pnv_id > 0 && $crm_prop_id > 0 && $ot_prop_id != '' && $agency_id > 0 ) { 
            
            // update PMe Property ID and Agency
            $updateData = array(
                'api_prop_id' => $ot_prop_id
            );
            $this->db->where('crm_prop_id', $crm_prop_id);
            $this->db->update('api_property_data', $updateData);

            // delete pnv
            if( $pnv_id > 0 ){

                $this->db->where('pnv_id', $pnv_id);
                $this->db->delete('properties_needs_verification');

            }         
            
            // Insert job log
            $log_title = 46; // Agency Update 
            $log_details = "Agency Updated From {$from_agency_name} To {$to_agency_name}";
           	
            $log_params = array(
                'title' => $log_title, 
                'details' => $log_details,
                'display_in_vpd' => 1,
                'created_by_staff' => $this->session->staff_id,
                'property_id' => $crm_prop_id,
            );
            $this->system_model->insert_log($log_params);

            // success
            $updateStat = true;
            echo json_encode(array("updateStat" => $updateStat));

        }

    }

    // new, no old crm orig file
    public function ourtradie_find_unmatched_properties(){

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->ourtradie_model->ourtradie_find_unmatched_properties();  

            echo "Cron job has finished executing"; 

        }                                         

    }

    public function ajax_bulk_move_nlm_property(){
        
        $this->load->model('properties_model');
        
        $jdata['status'] = false;
        $property_id_arr = $this->input->post('property_id_arr');
        $ourtradie_arr = $this->input->post('ourtradie_arr');
        $agency_id = $this->input->post('sel_agency_id');
        $old_agency_id = $this->input->post('old_agency_id');
        
        if( !empty($property_id_arr) && !empty($ourtradie_arr) ){

            for ($i=0; $i < count($property_id_arr); $i++) { 

                ## payable check
                $this->properties_model->payableCheck($property_id_arr[$i]);

                ## Update propert moved from old to new-----
                $updateData = array(
                    'agency_id' => $agency_id,
                    //'ourtradie_prop_id' => $ourtradie_arr[$i],
                    'is_nlm' => NULL,
                    'nlm_display' => NULL,
                    'nlm_timestamp' => NULL,
                    'nlm_by_sats_staff' => NULL,
                    'nlm_by_agency' => NULL,
                    'agency_deleted' => 0
                );
                $this->db->where('property_id', $property_id_arr[$i]);
                $this->db->update('property', $updateData);
                $this->db->reset_query();
                ## Update propert moved from old to new end-----

                ##check api_property_data
                $check = $this->properties_model->apiCheck($property_id_arr[$i]);

                ##Update api_property_data table-----
                if(empty($check)){

                    $updateData_Api = array(
                        'api_prop_id' => $ourtradie_arr[$i],
                        'crm_prop_id' => $property_id_arr[$i],
                        'api'         => 6
                    );
                    $this->db->insert('api_property_data', $updateData_Api);
                    $this->db->reset_query();

                }else{

                    $updateData_Api = array(
                        'api_prop_id' => $ourtradie_arr[$i]
                    );
                    $this->db->where('crm_prop_id', $property_id_arr[$i]);
                    $this->db->where('api', 6);
                    $this->db->update('api_property_data', $updateData_Api);
                    $this->db->reset_query();

                }
                ##Update api_property_data table end-----

                ##Insert log-----
                $old_agency_name = $this->db->select('agency_name')->from('agency')->where('agency_id',$old_agency_id)->get()->row()->agency_name;
                $new_agency_name = $this->db->select('agency_name')->from('agency')->where('agency_id',$agency_id)->get()->row()->agency_name;
                $details = "Property moved from <strong>{$old_agency_name}</strong> to <strong>{$new_agency_name}</strong>, status changed from NLM to Active.";
                $params = array(
                    'title' => 2, ## New Property Added
                    'details' => $details,
                    'display_in_vpd' => 1,
                    'agency_id' => $agency_id,
                    'created_by_staff' => $this->session->staff_id,
                    'property_id' => $property_id_arr[$i]
                );
                $this->system_model->insert_log($params);
                ##Insert log end-----

            }

            $jdata['status'] = true;

        }

        echo json_encode($jdata);

    }
    

}//endClass
