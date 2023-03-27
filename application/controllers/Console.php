<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Console extends CI_Controller {

    function __construct(){

        parent::__construct();
		$this->load->model('console_model');
        $this->load->model('properties_model');
        $this->load->model('inc/job_functions_model');
        $this->load->model('inc/alarm_functions_model');        
        $this->load->model('inc/pdf_template');
        
    }

    public function index(){
        
        
    }

    public function display_webhook_data()
    {        
        
        $sql = $this->db->query("
        SELECT `json`
        FROM `console_webhooks_data`        
        ");
        $row = $sql->result();     
       
        foreach( $sql->result() as $row ){

            $json_dec = json_decode($row->json);

            echo "<table>";
            echo "<tr>";
            echo "<th>eventId</th><td>{$json_dec->eventId}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<th>Event Type</th><td>{$json_dec->eventType}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<th>officeId</th><td>{$json_dec->officeId}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<th>recipientPartnerCode</th><td>{$json_dec->event->recipientPartnerCode}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<th>lastUpdatedDateTime</th><td>".date('Y-m-d H:i',strtotime($json_dec->event->lastUpdatedDateTime))."</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<th>propertyId</th><td>{$json_dec->event->relatedResources->property->propertyId}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<th>Full Address</th><td>{$json_dec->event->relatedResources->property->displayName}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<th>Compliance Notes</th><td>{$json_dec->event->relatedResources->propertyCompliance->notes}</td>";
            echo "</tr>";
            echo "</table>";

            echo "<pre>";
            print_r($json_dec);
            echo "</pre>";

            echo "---------------<br /><br />";
          
        }
      
        
        
    }


    public function catch_webhook_data(){     
        
        //$json_data = file_get_contents("php://input"); // native 
        $json_data = $this->input->raw_input_stream; // CI                        
        
        if( $json_data != '' ){
            $this->console_model->process_webhook_data($json_data);
        }          
        
    }



    public function bulk_connect() {

        $this->load->model('api_model');
        $data['start_load_time'] = microtime(true);
        $data['title'] = 'Console Bulk Match';
        $country_id = $this->config->item('country');
        $uri = '/console/bulk_connect';
        $data['uri'] = $uri;

        $agency_filter = $this->input->get_post('agency_filter');    
        
        // get agency connected/integrated to console
        $agencyQuery = $this->db->query("
        SELECT *
        FROM `agency` AS a
        INNER JOIN `console_api_keys` AS cak ON a.`agency_id` = cak.`agency_id`
        WHERE a.`status` = 'active'
        AND cak.`active` = 1 
        ");  

        $data['agenList'] = $agencyQuery;      

        $this->load->view('templates/inner_header', $data);
        $this->load->view('api/console_bulk_connect',$data);
        $this->load->view('templates/inner_footer', $data);
       
    }


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

    }


    // get CRM list
    public function ajax_bulk_connect_get_crm_list(){
        
        //$agency_id = 1448;
        $agency_id = $this->input->get_post('agency_id');
        $sel_query = "
            p.`property_id`,
            p.`address_1`,
            p.`address_2`,
            p.`address_3`,
            p.`state`,
            p.`postcode`,
            p.`is_sales`
        ";
        $this->db->select($sel_query);
        $this->db->from('property AS p');
        $this->db->join('console_properties AS cp', 'p.property_id = cp.crm_prop_id','left');
        $this->db->where('p.agency_id', $agency_id);
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where("( cp.crm_prop_id = '' OR cp.crm_prop_id IS NULL )");
        $lists = $this->db->get();    
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
                    foreach ($lists->result() as $index => $row) { 

                    // sales property
					$sales_txt = ( $row->is_sales == 1 )?'(Sales)':null;
            
                    $prop_address = trim("{$row->address_1} {$row->address_2}, {$row->address_3} {$row->state} {$row->postcode} {$sales_txt}");
                ?>
                    <tr>	
                        <td class="chk_col">
                            <span class="checkbox">
                                <input type="checkbox" id="check-<?php echo $index; ?>" class="chk_prop">
                                <label for="check-<?php echo $index; ?>" class="chk_lbl"></label>
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

    }


    // get PMe list
    public function ajax_bulk_connect_get_console_list(){ 

        $agency_id = $this->input->get_post('agency_id');
        $hide_pme_archived_prop = $this->input->get_post('hide_pme_archived_prop');
        $show_all_hidden_prop = $this->input->get_post('show_all_hidden_prop');

        // get office ID
        $cak_sql = $this->db->query("
        SELECT `office_id`
        FROM `console_api_keys`
        WHERE `agency_id` = {$agency_id}
        AND `active` = 1
        ");
        $cak_row = $cak_sql->row();

        $console_prop_list = $this->db->query("
        SELECT *
        FROM `console_properties`
        WHERE `active` = 1
        AND ( 
            crm_prop_id = '' OR 
            crm_prop_id IS NULL
        )   
        AND `office_id` = {$cak_row->office_id}     
        ");
        
        ?>
        <table id="pmeProp" class="display table table-borderless" cellspacing="0" width="100%">
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

            foreach ( $console_prop_list->result() as $key => $row ) {  
                                                             
                $hide_row = false;
                $row_hl_class = null;                
                
                // street
                if( $row->unit_num != '' && $row->street_num != '' ){
                    $street_unit_num = "{$row->unit_num}/{$row->street_num}";
                }else if( $row->unit_num != '' ){
                    $street_unit_num = "{$row->unit_num}";
                }else if( $row->street_num != '' ){
                    $street_unit_num = "{$row->street_num}";
                }

                $street_full = "{$row->street_name} {$row->street_type}";  
                    
                $console_prop_add = "{$street_unit_num} {$street_full}, {$row->suburb} {$row->state} {$row->postcode}";    
                
                if( $hide_row == false ){
            ?>
            <tr class="<?php echo $row_hl_class; ?>">
                <td class="pmeAdd"><?php echo $console_prop_add; ?></td>
                <td style="display: none;"><?=$row->Id?></td>
                <td style="display: none;" class="sort_index">0</td>
                <td>
                    <span class="checkbox">
                        <input type="checkbox" id="pme_prop_chk-<?php echo $key; ?>" class="pme_prop_chk api_prop_chk">
                        <label for="pme_prop_chk-<?php echo $key; ?>"></label>
                    </span>
                </td>
                <td>
                    <input type="hidden" class="pme_full_address" value="<?php echo $console_prop_add; ?>" />

                    <input type="hidden" class="pme_addr_unit" value="<?php echo $row->unit_num; ?>" />
                    <input type="hidden" class="pme_addr_number" value="<?php echo $row->street_num; ?>" />
                    <input type="hidden" class="pme_addr_street" value="<?php echo $street_full; ?>" />
                    <input type="hidden" class="pme_addr_suburb" value="<?php echo $row->suburb; ?>" />                            
                    <input type="hidden" class="pme_addr_postalcode" value="<?php echo $row->postcode; ?>" />
                    <input type="hidden" class="pme_addr_state" value="<?php echo $row->state; ?>" />

                    <input type="hidden" class="pme_addr_text" value="<?php echo $console_prop_add; ?>" />                                                                          

                    <input type="hidden" class="console_prop_id" value="<?php echo $row->console_prop_id; ?>" />
                    <input type="hidden" class="api_prop_id" value="<?php echo $row->console_prop_id; ?>" />    
                    <input type="hidden" class="pme_prop_id" value="<?php echo $row->console_prop_id; ?>" />                

                    <input type="hidden" class="note_btn_class" value="<?php echo "pme_note_btn{$note_ctr}"; ?>" />
                
                    <button type="button" class="btn btn-primary btn_add_prop_indiv">Add Property</button>                                                   
                    <input type="hidden" class="pnv_id" value="<?php echo $pnv_id; ?>" />                    
                </td>
            </tr>
            <?php    
                }         
            }
            ?>
            </tbody>
        </table>
        <div id="btn_add_prop_div">
            <button type="button" id="btn_add_prop" class="btn btn-primary">Add Property</button>
            <button type="button" id="btn_hide_prop_bulk" class="btn btn-primary">Hide Property</button>
            <button type="button" id="btn_unhide_prop_bulk" class="btn btn-primary">Unhide Property</button>
        </div>
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

    }

    public function bulk_connect_all() {

        $agency_filter = $this->input->get_post('agency_id');
        $crmArr = $this->input->get_post('crmArr');
        $pmeArr = $this->input->get_post('pmeArr');

        for ($i=0; $i < count($pmeArr); $i++) { 

            if( $pmeArr[$i] > 0 ){

                $updateData = array(
                    'crm_prop_id' => $crmArr[$i]
                );
                $this->db->where('console_prop_id', $pmeArr[$i]);
                $this->db->update('console_properties', $updateData);

            }
            
        }

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));
    }

    // bulk connect add property function 
    public function bulk_connect_add_property(){

        $this->load->model('properties_model');
        $this->load->model('agency_model');

        $agency_id = $this->input->get_post('agency_id');
        $pme_prop_arr = $this->input->get_post('pme_prop_arr');
        $disable_add = $this->input->get_post('disable_add');
        $dup_arr = [];
        $ret_str = '';

        foreach( $pme_prop_arr as $index => $pme_prop ){

            // decodes json string to actual json object
            $pme_prop_dec = json_decode($pme_prop);
            
            $pme_full_address = $pme_prop_dec->pme_full_address;
            $street_unit = $pme_prop_dec->pme_addr_unit;
            $street_num = $pme_prop_dec->pme_addr_number;
            $street_name = $pme_prop_dec->pme_addr_street;
            $suburb = $pme_prop_dec->pme_addr_suburb;
            $state = $pme_prop_dec->pme_addr_state;
            $postcode = $pme_prop_dec->pme_addr_postalcode;
    
            $lat = $pme_prop_dec->lat;
            $lng = $pme_prop_dec->lng;
            
            $pme_prop_id = $pme_prop_dec->pme_prop_id;
            $console_prop_id = $pme_prop_dec->pme_prop_id;
            $key_number = $pme_prop_dec->key_number;
            $tenants_contact_id = $pme_prop_dec->tenants_contact_id;
            $owner_contact_id = $pme_prop_dec->owner_contact_id;         
           
            $street_arr = [];
           
    
            // address
            // join unit and streen num
            if( $street_unit !='' ){
                $street_arr[] = $street_unit;
            }
            if( $street_num !='' ){
                $street_arr[] = $street_num;
            }
    
            // combine
            $street_num_fin = implode("/",$street_arr);

            // split street name
            $street_name_imp = explode(" ",strtolower($street_name));

            // if st or st. is first word in street name then its 'Saint' else its 'Street'
            if( $street_name_imp[0] == 'st' || $street_name_imp[0] == 'st.' ){

                $street_name_fin = preg_replace("/\b{$street_name_imp[0]}\b/i", 'Saint', $street_name);

            }else{ // default

                $street_name_fin = $this->system_model->getStreetAbrvFullName($street_name);

            }
            
    
            //$duplicate_query = $this->properties_model->check_duplicate_property($street_num_fin,$street_name_fin,$suburb,$state,$postcode);
            
            $check_dup_params = array(
                'street_num_fin' => $street_num_fin,
                'street_name_fin' => $street_name_fin,
                'suburb' => $suburb,
                'state' => $state,
                'postcode' => $postcode
            );
            $duplicate_query = $this->properties_model->check_duplicate_full_address($check_dup_params);
    
            if( $duplicate_query->num_rows()>0 ){ // existing property found
    
                $duplicate_row = $duplicate_query->row_array();            
                $dup_agency_id = $duplicate_row['agency_id'];

                //$dup_agency_name = (  $dup_agency_id == $agency_id )?'this Agency':$duplicate_row['agency_name'];
                //$dup_arr[] = "<a href='/property_me/property/{$duplicate_row['property_id']}/{$dup_agency_id}' target='_blank'>{$pme_full_address}</a> in agency <a href='{$this->config->item('crm_link')}/view_agency_details.php?id={$duplicate_row['agency_id']}'>{$dup_agency_name}</a>";
    
                
                $dup_arr[] = array(
                    'dup_property_id' => $duplicate_row['property_id'], 
                    'dup_property_address' => "{$duplicate_row['p_address_1']} {$duplicate_row['p_address_2']}, {$duplicate_row['p_address_3']} {$duplicate_row['p_state']} {$duplicate_row['p_postcode']}", 
                    'dup_prop_deleted' => $duplicate_row['deleted'],      
                    'dup_agency_id' => $duplicate_row['agency_id'],                                          
                    'dup_agency_name' => $duplicate_row['agency_name'],
                    'pme_prop_id' => $console_prop_id,
                    'console_prop_id' => $console_prop_id                                                                   
                );
           
            }else{


                if( $disable_add != 1 ){

                    // Hume Community Housing Association
                    $prop_comments = '';
                    if( $agency_id==1598 ){            
                        $prop_comments = 'Please install 9vLi or 240v only. DO NOT INSTALL 240vLi';            
                    }
        
                    /*
                    // get Pme landlord
                    $pme_params = array(
                        'agency_id' => $agency_id,
                        'owner_contact_id' => $owner_contact_id
                    );
                    $pme_landlord_json = $this->pme_model->get_pme_landlord($pme_params);
                    $pme_landlord_decode = json_decode($pme_landlord_json);
        
                    foreach( $pme_landlord_decode->ContactPersons as $pme_tenant ){
                        
                        $landlord_firstname = $pme_tenant->FirstName;
                        $landlord_lastname = $pme_tenant->LastName;
        
                    }  
                    */ 
        
        
                    // INSERT PROPERTY
                    // removed inserting lat and lng from API bec sometimes they have some weird coordinate like -999
                    // better leave it empty bec tech runs has auto-inserts of coordinate if they are empty on load                  
                    $property_data = array(
                        'agency_id' => $agency_id,
                        'address_1' => $street_num_fin,
                        'address_2' => $street_name_fin,
                        'address_3' => $suburb,
                        'state' => $state,
                        'postcode' => $postcode,
                        'added_by' => $this->session->staff_id,
                        'key_number' => $key_number,
                        'landlord_firstname' => $landlord_firstname,
                        'landlord_lastname' => $landlord_lastname,            
                        'comments' => $prop_comments
                    );
                    $add_property = $this->properties_model->add_property($property_data);
                    $prop_insert_id = $this->db->insert_id();
        
                    if( $add_property && !empty($prop_insert_id) ){

                        // connect to console property
                        if( $console_prop_id > 0 ){

                            // update
                            $update_data = array(                        
                                'crm_prop_id' => $prop_insert_id
                            );                            
                            $this->db->where('console_prop_id', $console_prop_id);
                            $this->db->update('console_properties', $update_data);
                            
                        }
                             
        
                        // insert property log
                        $params = array(
                            'title' => 2, //New Property Added
                            'details' => 'Added from Console Bulk Match',
                            'display_in_vpd' => 1,
                            'agency_id' => $agency_id,
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $prop_insert_id
                        );
                        $this->system_model->insert_log($params); 
        
                        /*
                        // get Pme tenants
                        $pme_params = array(
                            'agency_id' => $agency_id,
                            'tenants_contact_id' => $tenants_contact_id
                        );
                        $pme_tenant_json = $this->pme_model->get_pme_tenant($pme_params);
                        $pme_tenant_decode = json_decode($pme_tenant_json);

                        $tenant_arr = [];
                        foreach( $pme_tenant_decode->ContactPersons as $pme_tenant ){
        
                            $tenant_arr[] = array(
                                'property_id' =>  $prop_insert_id,
                                'tenant_firstname' => $pme_tenant->FirstName,
                                'tenant_lastname' => $pme_tenant->LastName,
                                'tenant_mobile' => $pme_tenant->CellPhone,
                                'tenant_landline' => $pme_tenant->HomePhone,
                                'tenant_email' => $pme_tenant->Email,
                                'active' => 1
                            );
                        }
                        
        
                        if(!empty($tenant_arr)){
                            $this->properties_model->add_tenants($tenant_arr, 'batch'); //  param insert batch otherwise 0 for normal
                        }
                        */
        
                        // get agency services
                        /*$params = array(
                            'sel_query' => "
                                agen_serv.`service_id`,
                                agen_serv.`price`
                            ",
                            'agency_id' => $agency_id
                        );
                        $agency_services_sql = $this->agency_model->get_agency_services($params);
                        
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


    public function connection_details($property_id) {

        $data['title'] = 'Console Connection Details Page';
        $uri = "/console/connection_details/{$property_id}";
        $data['uri'] = $uri;
 
        if( $property_id > 0 ){

            // get crm property
            $sel_query = "
            p.`property_id`, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.lat,
            p.lng,

            c.`country` AS country_name
            ";

            $params = array(
                'sel_query' => $sel_query,                                                                
                'property_id' => $property_id,
                'join_table' => array('countries'),
                'display_query' => 0
            );
            $crm_prop_sql = $this->properties_model->get_properties($params);
            $data['crm_prop'] = $crm_prop_sql->row();

            // get crm tenants            
            $this->db->select('*');
            $this->db->from('property_tenants');
            $this->db->where('property_id', $property_id);
            $this->db->where('active', 1);
            $query = $this->db->get();
            $data['crmTenant'] = $query->result_array();  

            // get console property
            $console_prop_sql = $this->db->query("
            SELECT *
            FROM `property` AS p
            INNER JOIN `console_properties` AS cp ON p.`property_id` = cp.`crm_prop_id`
            WHERE cp.`active` = 1	
            AND cp.`crm_prop_id` = {$property_id}															       
            ");
            $data['console_prop_row'] = $console_prop_sql->row();     
            
            // get console tenants                  
            $this->db->select('*');
            $this->db->from('console_property_tenants AS cpt');
            $this->db->join('console_properties AS cp', 'cpt.`console_prop_id` = cp.`console_prop_id`', 'inner');
            $this->db->where('cp.crm_prop_id', $property_id);
            $this->db->where('cpt.active', 1);
            $data['console_tenant_sql'] = $this->db->get();
            
            $data['property_id'] = $property_id;
            $data['crm_prop_id'] = $property_id;

            $this->load->view('templates/inner_header', $data);
            $this->load->view('api/console_connection_page',$data); // already connected page
            $this->load->view('templates/inner_footer', $data);   

        }                                     

    }


    public function property_info() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Console Property Info";
        $country_id = $this->config->item('country');
        $uri = '/console/property_info';
        $data['uri'] = $uri;

        $office_id_filter = $this->input->get_post('office_id_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // get agency connected/integrated to console
        $data['agency_filter'] = $this->db->query("
        SELECT 
            a.`agency_id`,
            a.`agency_name`,

            cak.`office_id`
        FROM `agency` AS a
        INNER JOIN `console_api_keys` AS cak ON a.`agency_id` = cak.`agency_id`
        WHERE a.`status` = 'active'
        AND cak.`active` = 1 
        "); 

        // get console property                
        $this->db->select('*');
        $this->db->from('console_properties AS cp');   
        $this->db->join('console_property_compliance AS cpc', 'cp.`console_prop_id` = cpc.`console_prop_id`', 'left');
        $this->db->join('console_property_other_info AS cpoi', 'cp.`console_prop_id` = cpoi.`console_prop_id`', 'left');             
        $this->db->where('cp.active', 1);
        if( $office_id_filter > 0 ){            
            $this->db->where('cp.office_id', $office_id_filter);
        }
        $data['console_prop_sql'] = $this->db->get();    

        $pagi_links_params_arr = array(
            'office_id_filter' => $office_id_filter
        );
        $pagi_link_params = $uri.'?'.http_build_query($pagi_links_params_arr);


        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

       // pagination count
       $pc_params = array(
        'total_rows' => $total_rows,
        'offset' => $offset,
        'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('api/property_info', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function tenants_info() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Console Tenants Info";
        $country_id = $this->config->item('country');
        $uri = '/console/tenants_info';
        $data['uri'] = $uri;

        $office_id_filter = $this->input->get_post('office_id_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // get agency connected/integrated to console
        $data['agency_filter'] = $this->db->query("
        SELECT 
            a.`agency_id`,
            a.`agency_name`,

            cak.`office_id`
        FROM `agency` AS a
        INNER JOIN `console_api_keys` AS cak ON a.`agency_id` = cak.`agency_id`
        WHERE a.`status` = 'active'
        AND cak.`active` = 1 
        "); 

        // get webhook data                
        $this->db->select('
        cwd.`id` AS cwd_id,
        cwd.`event_type`,
        cwd.`json`,
        cwd.`date` AS cwd_date,

        cp.`crm_prop_id`
        ');
        $this->db->from('console_webhooks_data AS cwd');  
        $this->db->join('console_properties AS cp', 'cwd.`console_prop_id` = cp.`console_prop_id`', 'left');
        $this->db->like('cwd.`event_type`', 'PROPERTY_COMPLIANCE_TENANCY');  
        $this->db->or_like('cwd.`event_type`', 'PROPERTY_COMPLIANCE_CONTACT'); 
        $this->db->where('cwd.`active`', 1);      
        if( $office_id_filter > 0 ){            
            $this->db->where('cwd.office_id', $office_id_filter);
        }
        $this->db->order_by('cwd.`date`', 'DESC');
        $data['webhooks_data_sql'] = $this->db->get();    
        $data['page_query'] = $this->db->last_query();

        $pagi_links_params_arr = array(
            'office_id_filter' => $office_id_filter
        );
        $pagi_link_params = $uri.'?'.http_build_query($pagi_links_params_arr);


        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

       // pagination count
       $pc_params = array(
        'total_rows' => $total_rows,
        'offset' => $offset,
        'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('api/tenants_info', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function compliance_info() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Console Compliance Info";
        $country_id = $this->config->item('country');
        $uri = '/console/compliance_info';
        $data['uri'] = $uri;

        $office_id_filter = $this->input->get_post('office_id_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // get agency connected/integrated to console
        $data['agency_filter'] = $this->db->query("
        SELECT 
            a.`agency_id`,
            a.`agency_name`,

            cak.`office_id`
        FROM `agency` AS a
        INNER JOIN `console_api_keys` AS cak ON a.`agency_id` = cak.`agency_id`
        WHERE a.`status` = 'active'
        AND cak.`active` = 1 
        "); 

        // get webhook data                
        $this->db->select('
        cwd.`id` AS cwd_id,
        cwd.`event_type`,
        cwd.`json`,
        cwd.`date` AS cwd_date,

        cp.`crm_prop_id`
        ');
        $this->db->from('console_webhooks_data AS cwd');  
        $this->db->join('console_properties AS cp', 'cwd.`console_prop_id` = cp.`console_prop_id`', 'left');
        $this->db->not_like('cwd.`event_type`', 'PROPERTY_COMPLIANCE_TENANCY');  
        $this->db->not_like('cwd.`event_type`', 'PROPERTY_COMPLIANCE_CONTACT'); 
        $this->db->where('cwd.`active`', 1);      
        if( $office_id_filter > 0 ){            
            $this->db->where('cwd.office_id', $office_id_filter);
        }
        $this->db->order_by('cwd.`date`', 'DESC');
        $data['webhooks_data_sql'] = $this->db->get();    
        $data['page_query'] = $this->db->last_query();

        $pagi_links_params_arr = array(
            'office_id_filter' => $office_id_filter
        );
        $pagi_link_params = $uri.'?'.http_build_query($pagi_links_params_arr);


        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

       // pagination count
       $pc_params = array(
        'total_rows' => $total_rows,
        'offset' => $offset,
        'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('api/compliance_info', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function deactivate_webhook_data(){

        $cwd_id = $this->input->get_post('cwd_id');

        if( $cwd_id > 0 ){

            $update_data = array(
                'active' => 0
            );            
            $this->db->where('id', $cwd_id);
            $this->db->update('console_webhooks_data', $update_data);

        }        

    }


    public function display_webhook_data_breakdown(){

        $cwd_id = $this->input->get_post('cwd_id');

        if( $cwd_id > 0 ){

            // get webhook data                
            $this->db->select('
            cwd.`id` AS cwd_id,
            cwd.`event_type`,
            cwd.`json`,
            cwd.`date` AS cwd_date
            ');
            $this->db->from('console_webhooks_data AS cwd');   
            $this->db->where('cwd.`id`', $cwd_id);     
            $webhooks_data_sql = $this->db->get();    
            $data['webhooks_row'] = $webhooks_data_sql->row();
                  
            $this->load->view('api/console_webhooks_data_breakdown', $data);

        }        

    }


    public function test_upload_to_console_aws(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Test Console Upload";
        $country_id = $this->config->item('country');
        $uri = '/console/test_upload_to_console_aws';
        $data['uri'] = $uri;


        if(isset($_FILES['file'])){

            $uuid = $this->console_model->guidv4();  

            // https://www.uuidgenerator.net/
            //$uuid = '07809666-2e00-4642-af0b-aacdcbe5e285';

            echo "<h3>UUID:</h3>";
            echo "uuid: {$uuid}<br />";

            $file_name = 'console_file'.rand().date('YmdHis') . '.pdf';

            echo "<h3>FILE data:</h3>";
            echo "<pre>";
            print_r($_FILES['file']);
            echo "</pre>";

            // console agency
            $agency_id = 4222;

            if( $agency_id > 0 ){

                // get API key from console API connected agency
                $sql_str = "
                SELECT `api_key`
                FROM `console_api_keys`
                WHERE `agency_id` = {$agency_id}
                ";
                $sql = $this->db->query($sql_str);
                $sql_row = $sql->row();

                if( $sql_row->api_key != '' ){

                    // create file object
                    $cons_mod_params = array(
                        'uuid' => $uuid,   
                        'api_key' => $sql_row->api_key,
                        'file_name' => $file_name
                    );
                    $res_json_dec = $this->console_model->create_file($cons_mod_params);

                    echo "<h3>Create File:</h3>";
                    echo "<pre>";
                    print_r($res_json_dec);
                    echo "</pre>";

                    echo "-----------------<br /><br />";

                    if( $res_json_dec->link->url != '' ){

                        // upload 
                        $cons_mod_params = array(                                                     
                            'pre_signed_url' => $res_json_dec->link->url,
                            'content_type' => $res_json_dec->link->headers->{'Content-Type'}[0],
                            'x_amz_encr' => $res_json_dec->link->headers->{'x-amz-server-side-encryption'}[0],
                            'file' => $_FILES['file']
                        );

                        echo "<h3>Upload File:</h3>";
                        echo "<pre>";
                        print_r($cons_mod_params);
                        echo "</pre>";

                        echo "urlencode: ".urlencode($res_json_dec->link->url)."<br /><br />";
                        echo "urldecode: ".urldecode($res_json_dec->link->url)."<br /><br />";
                        echo "urlencode: ".urlencode($res_json_dec->link->url)."<br /><br />";
                        echo "rawurlencode: ".rawurlencode($res_json_dec->link->url)."<br /><br />";
                        echo "rawurldecode: ".rawurldecode($res_json_dec->link->url)."<br /><br />";        
                        echo "myUrlEncode: ".$this->console_model->myUrlEncode($res_json_dec->link->url)."<br /><br />";                                    
                        
                        $http_status_code = $this->console_model->upload_file($cons_mod_params);

                        echo 'HTTP status code: ' . $http_status_code."<br />";

                        echo "<h3>Link a file to a Compliance Process:</h3>";
                        if( $http_status_code == 200 && $res_json_dec->fileId ){ // OK

                             // compliance process ID from 105 Marathon Cnr, Camooweal QLD 4828
                            $prop_comp_proc_id = '36549135-81f2-4e66-af92-2f854f25a7cc';

                            // link a file to a compliance process
                            $link_file_params = array(
                                'api_key' => $sql_row->api_key,
                                'file_id' => $res_json_dec->fileId,                                
                                'prop_comp_proc_id' => $prop_comp_proc_id
                            );

                            echo "<h3>Parameter:</h3>";
                            echo "<pre>";
                            print_r($link_file_params);
                            echo "</pre>";
                            $link_file_dec = $this->console_model->link_file_comp_process($link_file_params);

                            echo "<h3>Response:</h3>";
                            echo "<pre>";
                            print_r($link_file_dec);
                            echo "</pre>";

                        } 

                        
                        echo "-----------------<br /><br />";

                        
                        // get file
                        $cons_mod_params2 = array(     
                            'uuid' => $uuid,
                            'api_key' => $sql_row->api_key
                        );

                        echo "<h3>Get File:</h3>";
                        echo "<pre>";
                        print_r($cons_mod_params2);
                        echo "</pre>";

                        
                        $res_json_dec2 = $this->console_model->get_file($cons_mod_params2);

                        echo "<pre>";
                        print_r($res_json_dec2);
                        echo "</pre>";

                        $donwload_link = $res_json_dec2->results[0]->resource->link->url;

                        if( $res_json_dec2->results[0]->status == 'SUCCESS' && $donwload_link != '' ){

                            echo "<a href='/console/download_file?dlink=".urlencode($donwload_link)."&file_type={$res_json_dec2->results[0]->resource->attributes[1]->attributeValue}' target='_blank'>download file</a>";

                        }
                        
                        
                                                                       

                    }
                    

                }
                

            }            

            
    
            /*
            require 'vendor/autoload.php';
    
            $s3 = new Aws\S3\S3Client([
                'region'  => '-- your region --',
                'version' => 'latest',
                'credentials' => [
                    'key'    => "-- access key id --",
                    'secret' => "-- secret access key --",
                ]
            ]);		
    
            $result = $s3->putObject([
                'Bucket' => '-- bucket name --',
                'Key'    => $file_name,
                'SourceFile' => $temp_file_location			
            ]);
    
            var_dump($result);
            */

        }else{

            $this->load->view('templates/inner_header', $data);
            $this->load->view('test/test_upload_to_console_aws', $data);
            $this->load->view('templates/inner_footer', $data);

        }        
        

    }


    function download_file(){

        $dlink = $this->input->get_post('dlink');
        $file_type = $this->input->get_post('file_type'); 
        
        //echo "download link: ".urldecode($dlink);

        // download 
        $cons_mod_params = array(                                                     
            'pre_signed_url' => $dlink,
            'file_type' => $file_type
        );
        
        echo $this->console_model->download_file($cons_mod_params);        

    }


    function test(){

        $post_params = array(
            'fileIds' => array('b8422db6-7aa8-4671-b8dd-fada5012ed50'),
            'references' => array(
                array(
                    'resourceType' => 'test file upload',
                    'resourceId' => '1'
                )
            )
        );

        echo "<pre>";
        print_r(json_encode($post_params));
        echo "</pre>";

    }

    public function upload_invoice_and_certificate(){

        $job_id = $this->input->get_post('job_id');     
        
        if( $job_id > 0 ){
            
            $ret_arr = $this->console_model->upload_invoice_and_certificate($job_id);
            redirect("{$this->config->item('crm_link')}/view_job_details.php?id={$job_id}&invoice_uploaded={$ret_arr['invoice_uploaded']}&certificate_uploaded={$ret_arr['certificate_uploaded']}");

        }

    }

    
}