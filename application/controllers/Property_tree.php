<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Property_Tree extends CI_Controller {

    function __construct(){

        parent::__construct();
		$this->load->model('property_tree_model');
        $this->load->model('properties_model');
        $this->load->model('inc/job_functions_model');
        $this->load->model('inc/alarm_functions_model');        
        $this->load->model('inc/pdf_template');
        
    }


    public function bulk_connect() {

        $this->load->model('api_model');
        $data['start_load_time'] = microtime(true);
        $data['title'] = 'Property Tree Bulk Match';
        $country_id = $this->config->item('country');
        $uri = '/property_tree/bulk_connect';
        $data['uri'] = $uri;

        $api_id = 3; // Property Tree 
        
        $sel_query = "
            agen_api_tok.`agency_api_token_id`, 
            
            a.`agency_id`,
            a.`agency_name`,
            a.`no_bulk_match`,

            pme_upc.`count` AS upc_count
        ";
        $api_token_params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'api_id' => $api_id,
            'deactivated' => 1,
            'target' => 1,
            'group_by' => 'agen_api_tok.`agency_id`',
            'join_table' => array('agency','pme_unmatched_property_count'),
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0            
        );
        $agencyQuery = $this->api_model->get_agency_api_tokens($api_token_params);        
        $data['agenList'] = $agencyQuery;

        $this->load->view('templates/inner_header', $data);
        $this->load->view('api/prop_tree_bulk_connect',$data);
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
        $this->db->join('api_property_data AS apd', 'p.property_id = apd.crm_prop_id','left');
        $this->db->where('p.agency_id', $agency_id);
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where("( apd.crm_prop_id = '' OR apd.crm_prop_id IS NULL )");
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
                                <button type="button" class="btn btn-primary verify_nlm_btn crm_note_btn<?php echo $note_ctr; ?>" >PNV</button>
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


    // get Property Tree list
    public function ajax_bulk_connect_get_api_list(){ 

        $this->load->model('agency_api_model');
        $show_all_hidden_prop = $this->input->get_post('show_all_hidden_prop');
        $agency_id = $this->input->get_post('agency_id');
        $hide_pme_archived_prop = $this->input->get_post('hide_pme_archived_prop');

        $prop_tree_list = $this->property_tree_model->get_all_properties($agency_id);
        
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
            
            // get all connected properties
            $crm_connected_prop_sql_str = "
            SELECT `api_prop_id`
            FROM `api_property_data`
            WHERE `crm_prop_id` != ''
            ";
            $crm_connected_prop_sql = $this->db->query($crm_connected_prop_sql_str);

            $api_prop_id_arr = [];
            foreach( $crm_connected_prop_sql->result() as $crm_conn_prop_row ){
                $api_prop_id_arr[] = $crm_conn_prop_row->api_prop_id; 
            }

            foreach ( $prop_tree_list as $key => $address_obj_row ) {  
                                                             
                $hide_row = false;
                $address_obj_row_hl_class = null;              
                
                $api_prop_id = $address_obj_row->id;
                $address_obj = $address_obj_row->address;
                
                // street
                if( $address_obj->unit != '' && $address_obj->street_number != '' ){
                    $street_unit_num = "{$address_obj->unit}/{$address_obj->street_number}";
                }else if( $address_obj->unit != '' ){
                    $street_unit_num = "{$address_obj->unit}";
                }else if( $address_obj->street_number != '' ){
                    $street_unit_num = "{$address_obj->street_number}";
                }
                    
                $pt_prop_add = "{$street_unit_num} {$address_obj->address_line_1}, {$address_obj->suburb} {$address_obj->state} {$address_obj->post_code}";    

                //hide archive
                if( $hide_pme_archived_prop == 1 ){
                    if( $address_obj_row->archived == true ){
                        $hide_row = true;
                    }
                }

                // if API property already connected
                if( in_array($api_prop_id, $api_prop_id_arr) ){
                    $hide_row = true; // hide row
                }

                // check if property is set as hidden
                $api_id = 3; // PropertyMe
                $agency_api_model_params = array(
                    'api_prop_id' => $api_prop_id,                                                                
                    'agency_id' => $agency_id,
                    'api_id' => $api_id,
                );                                                       
                

                $is_api_property_hidden = $this->agency_api_model->is_api_property_hidden($agency_api_model_params);
                if( $is_api_property_hidden == true ){
                    $address_obj_row_hl_class = 'pme_hidden_row';
                }    

                $hide_row_api_property = false;  
                if( $is_api_property_hidden == true && $show_all_hidden_prop != 1 ){
                    $hide_row_api_property = true;
                }

                if( $address_obj_row->archived == true || $address_obj_row->deleted == true ){
                    $address_obj_row_hl_class = 'pme_archived_row';
                }

                if( $hide_row == false && $hide_row_api_property == false){
            ?>
            <tr class="<?php echo $address_obj_row_hl_class; ?>">
                <td class="pmeAdd"><?php echo $pt_prop_add; ?></td>
                <td style="display: none;"><?=$api_prop_id?></td>
                <td style="display: none;" class="sort_index">0</td>
                <td>
                    <span class="checkbox">
                        <input type="checkbox" id="pme_prop_chk-<?php echo $key; ?>" class="pme_prop_chk api_prop_chk">
                        <label for="pme_prop_chk-<?php echo $key; ?>"></label>
                    </span>
                </td>
                <td>
                    <input type="hidden" class="pme_full_address" value="<?php echo $pt_prop_add; ?>" />

                    <input type="hidden" class="pme_addr_unit" value="<?php echo $address_obj->unit; ?>" />
                    <input type="hidden" class="pme_addr_number" value="<?php echo $address_obj->street_number; ?>" />
                    <input type="hidden" class="pme_addr_street" value="<?php echo $address_obj->address_line_1; ?>" />
                    <input type="hidden" class="pme_addr_suburb" value="<?php echo $address_obj->suburb; ?>" />                            
                    <input type="hidden" class="pme_addr_postalcode" value="<?php echo $address_obj->post_code; ?>" />
                    <input type="hidden" class="pme_addr_state" value="<?php echo $address_obj->state; ?>" />

                    <input type="hidden" class="pme_addr_text" value="<?php echo $pt_prop_add; ?>" />                                                                          
                    
                    <input type="hidden" class="pme_prop_id" value="<?php echo $api_prop_id; ?>" />
                    <input type="hidden" class="api_prop_id" value="<?php echo $api_prop_id; ?>" />                                         

                    <input type="hidden" class="note_btn_class" value="<?php echo "pme_note_btn{$note_ctr}"; ?>" />
                
                    <button type="button" class="btn btn-primary btn_add_prop_indiv">Add Property</button>   
                    <?php 
                    if( $is_api_property_hidden == true ){ ?>
                        <button type="button" class="btn btn-primary btn_unhide_api_prop">Unhide</button>
                    <?php
                    }else{ ?>
                        <button type="button" class="btn btn-success btn_hide_api_prop">Hide</button>
                    <?php
                    }
                    ?>                                                      
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
        $connect_deleted_nlm_prop = $this->input->get_post('connect_deleted_nlm_prop');

        $api = 3; // Property Tree API

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
                    'title' => 91, // PropertyTree API
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
               $log_details = "Property was restored from NLM by connecting on <b>PropertyTree</b> bulk match.";
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
                'title' => 91, // PropertyTree API
                'details' => 'Property <b>Linked</b> to <b/>PropertyTree</b> on Bulk Match',
                'display_in_vpd' => 1,            
                'created_by_staff' => $this->session->staff_id,
                'property_id' => $crmArr[$i]
            );
            $this->system_model->insert_log($params);

            $check = $this->properties_model->apiCheck($crmArr[$i]);

            if( $pmeArr[$i] != '' ){

                // check if API prop Id already exist
                $apd_sql = $this->db->query("
                SELECT COUNT(`id`) AS apd_count
                FROM `api_property_data`
                WHERE `crm_prop_id` = {$crmArr[$i]}
                AND `api` = {$api}
                ");
                $apd_row = $apd_sql->row();

                if( $apd_row->apd_count > 0 ){ // update

                    $update_data = array(
                        'api_prop_id' => $pmeArr[$i]
                    );
                    
                    $this->db->where('crm_prop_id', $crmArr[$i]);
                    $this->db->where('api', $api);
                    $this->db->update('api_property_data', $update_data);

                }else{ // insert

                    $data = array(
                        'crm_prop_id' => $crmArr[$i],
                        'api' => $api,
                        'api_prop_id' => $pmeArr[$i]
                    );                    
                    $this->db->insert('api_property_data', $data);

                }                

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
        $api_id = 3; // Property Tree

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
            $api_prop_id = $pme_prop_dec->api_prop_id;
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
                
                $dup_arr[] = array(
                    'dup_property_id' => $duplicate_row['property_id'], 
                    'dup_property_address' => "{$duplicate_row['p_address_1']} {$duplicate_row['p_address_2']}, {$duplicate_row['p_address_3']} {$duplicate_row['p_state']} {$duplicate_row['p_postcode']}", 
                    'dup_prop_deleted' => $duplicate_row['is_nlm'],      
                    'dup_agency_id' => $duplicate_row['agency_id'],                                          
                    'dup_agency_name' => $duplicate_row['agency_name'],
                    'pme_prop_id' => $api_prop_id,
                    'api_prop_id' => $api_prop_id                                                              
                );
           
            }else{


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
                        'address_1' => $street_num_fin,
                        'address_2' => $street_name_fin,
                        'address_3' => $suburb,
                        'state' => $state,
                        'postcode' => $postcode,
                        'added_by' => $this->session->staff_id,
                        'key_number' => $key_number,            
                        'comments' => $prop_comments
                    );
                    $add_property = $this->properties_model->add_property($property_data);
                    $prop_insert_id = $this->db->insert_id();
        
                    if( $add_property && !empty($prop_insert_id) ){

                        // connect to Property Tree property
                        if( $api_prop_id != '' ){
    
                            $data = array(
                                'crm_prop_id' => $prop_insert_id,
                                'api' => $api_id,
                                'api_prop_id' => $api_prop_id
                            );                            
                            $this->db->insert('api_property_data', $data);
                            
                        }
                                     
                        // insert property log
                        $params = array(
                            'title' => 2, //New Property Added
                            'details' => 'Added from Property Tree Bulk Match',
                            'display_in_vpd' => 1,
                            'agency_id' => $agency_id,
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $prop_insert_id
                        );
                        $this->system_model->insert_log($params);                             
        
                    }
                    
                }                                                          
                
    
            }           
            
        }        

        echo json_encode($dup_arr);
        
    }


    public function connection_details($property_id) {

        $this->load->model('api_model');
        
        $data['title'] = 'Property Tree Connection Details Page';
        $uri = "/property_tree/connection_details/{$property_id}";
        $data['uri'] = $uri;
        
        $api_id = 3; // Property Tree
         
        if( $property_id > 0 ){

            // get API property ID
            $crm_connected_prop_sql_str = "
            SELECT `api_prop_id`
            FROM `api_property_data`
            WHERE `crm_prop_id` = {$property_id}
            AND `api` = {$api_id}
            ";
            $crm_connected_prop_sql = $this->db->query($crm_connected_prop_sql_str);
            $crm_connected_prop_row = $crm_connected_prop_sql->row();
            $api_prop_id = $crm_connected_prop_row->api_prop_id;

            // get crm property
            $sel_query = "
            p.`property_id`, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`lat`,
            p.`lng`,
            p.`comments`,

            a.`agency_id`,
            a.`agency_name`,

            c.`country` AS country_name
            ";

            $params = array(
                'sel_query' => $sel_query,                                                                
                'property_id' => $property_id,
                'join_table' => array('countries'),
                'display_query' => 0
            );
            $crm_prop_sql = $this->properties_model->get_properties($params);
            $crm_prop_row = $crm_prop_sql->row();
            $agency_id = $crm_prop_row->agency_id;

            if( $api_prop_id != '' ){
                                
                $data['crm_prop'] = $crm_prop_row;

                // get crm tenants            
                $this->db->select('*');
                $this->db->from('property_tenants');
                $this->db->where('property_id', $property_id);
                $this->db->where('active', 1);
                $query = $this->db->get();
                $data['crmTenant'] = $query->result_array();  

                // get property tree, property data            
                $api_prop_json = $this->property_tree_model->get_property($property_id);
                $api_prop_obj = $api_prop_json[0];

                $data['api_prop_data'] = $api_prop_obj;

                if( $api_prop_obj->tenancy != '' ){

                    // get property tree, tenant data           
                    $api_tenant_json = $this->property_tree_model->get_tenant($agency_id,$api_prop_obj->tenancy);
                    $data['contact_arr'] = $api_tenant_json->contacts;

                }            
                
                $data['property_id'] = $property_id;            

                $this->load->view('templates/inner_header', $data);
                $this->load->view('api/prop_tree_connection_page',$data); // already connected page
                $this->load->view('templates/inner_footer', $data); 

            }else { // not connected yet

                $data['prop_tree_list'] = $this->property_tree_model->get_all_properties($agency_id);
                $data['propDet'] = $crm_prop_row;
                $this->load->view('templates/inner_header', $data);
                $this->load->view('api/prop_tree_manual_connect_page',$data); // search and match Pme properties
                $this->load->view('templates/inner_footer', $data);
    
            }
              
        }                                 

    }

    public function connect_agency() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Propery Tree - Connect Agency";        
        $uri = '/property_tree/connect_agency';
        $data['uri'] = $uri;

        $api_id = 3; // Property Tree API
        
        // get already stored authentication keys
        $auth_keys_sql_str = "
        SELECT apt.`access_token`
        FROM `agency_api_tokens` AS apt
        WHERE apt.`api_id` = {$api_id}
        AND apt.`access_token` != ''        
        ";
        $auth_keys_sql = $this->db->query($auth_keys_sql_str);

        $current_auth_keys_arr = [];
        foreach( $auth_keys_sql->result() as $auth_keys_row ){
            $current_auth_keys_arr[] = $auth_keys_row->access_token;
        }
        $data['current_auth_keys_arr'] = $current_auth_keys_arr;

        // get company authentication keys
        $data['json_dec_arr'] = $this->property_tree_model->get_property_tree_auth_keys();
        
        $agency_sql_str = "
        SELECT 
            a.`agency_id`, 
            a.`agency_name`
        FROM `agency` AS a
        LEFT JOIN `agency_api_tokens` AS apt ON ( a.`agency_id` = apt.`agency_id` AND apt.`api_id` = {$api_id} )
        WHERE a.`status` = 'active'
        AND ( apt.`access_token` = '' OR apt.`access_token` IS NULL )
        ORDER BY a.`agency_name` ASC
        ";
        $data['agency_sql'] = $this->db->query($agency_sql_str);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('api/connect_agency', $data);
        $this->load->view('templates/inner_footer', $data);                     

    }

    public function ajax_connect_agency(){

        $agency = $this->input->get_post('agency');
        $auth_key = $this->input->get_post('auth_key');

        $api_id = 3; // property tree API

        if( $agency > 0 && $auth_key != '' ){
            
            // insert agency/auth token
            $data = array(
                'api_id' => $api_id,
                'agency_id' => $agency,
                'access_token' => $auth_key
            );            
            $this->db->insert('agency_api_tokens', $data);

        }

    }


    public function ajax_function_link_property() {

        $pt_prop_id = $this->input->get_post('pt_prop_id');
        $crmId = $this->input->get_post('crmId');

        $api_id = 3; // property tree API

        if( $pt_prop_id != '' ){

            // check if API prop Id already exist
            $apd_sql = $this->db->query("
            SELECT COUNT(`id`) AS apd_count
            FROM `api_property_data`
            WHERE `crm_prop_id` = {$crmId}
            AND `api` = {$api_id}
            ");
            $apd_row = $apd_sql->row();

            if( $apd_row->apd_count > 0 ){ // update

                $update_data = array(
                    'api_prop_id' => $pt_prop_id
                );
                
                $this->db->where('crm_prop_id', $crmId);
                $this->db->where('api', $api_id);
                $this->db->update('api_property_data', $update_data);

            }else{ // insert

                $data = array(
                    'crm_prop_id' => $crmId,
                    'api' => $api_id,
                    'api_prop_id' => $pt_prop_id
                );                    
                $this->db->insert('api_property_data', $data);

            }

            // insert property log
            $params = array(
                'title' => 91, // PropertyTree API
                'details' => 'Property <b>Linked</b> to <b/>PropertyTree</b>',
                'display_in_vpd' => 1,            
                'created_by_staff' => $this->session->staff_id,
                'property_id' => $crmId
            );
            $this->system_model->insert_log($params);

            echo json_encode(array("updateStat" => true));

        }        

    }

    public function ajax_function_unlink_property() {
        
        $crmId = $this->input->get_post('crmId');

        $api_id = 3; // property tree API

        if( $crmId > 0 ){

            $this->db->where('crm_prop_id', $crmId);
            $this->db->where('api', $api_id);
            $this->db->delete('api_property_data');            
    
            // insert property log
            $params = array(
                'title' => 91, // PropertyTree API
                'details' => 'Property <b>Unlinked</b> to <b/>PropertyTree</b>',
                'display_in_vpd' => 1,            
                'created_by_staff' => $this->session->staff_id,
                'property_id' => $crmId
            );
            $this->system_model->insert_log($params);
            
            echo json_encode(array("updateStat" => true));

        } 
       
    }

    public function ajax_bulk_move_nlm_property(){

        $this->load->model('properties_model');
        
        $jdata['status'] = false;
        $property_id_arr = $this->input->post('property_id_arr');
        $pmeArr = $this->input->post('pmeArr');
        $agency_id = $this->input->post('sel_agency_id');
        $old_agency_id = $this->input->post('old_agency_id');
        
        if( !empty($property_id_arr) && !empty($pmeArr) ){

            for ($i=0; $i < count($property_id_arr); $i++) { 

                ## payable check
                $this->properties_model->payableCheck($property_id_arr[$i]);

                ## Update propert moved from old to new----- 
                $updateData = array(
                    'agency_id' => $agency_id,
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
                        'api_prop_id' => $pmeArr[$i],
                        'crm_prop_id' => $property_id_arr[$i],
                        'api'         => 3
                    );
                    $this->db->insert('api_property_data', $updateData_Api);
                    $this->db->reset_query();

                }else{

                    $updateData_Api = array(
                        'api_prop_id' => $pmeArr[$i]
                    );
                    $this->db->where('crm_prop_id', $property_id_arr[$i]);
                    $this->db->where('api', 3);
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

    public function get_property_tree_tenancy(){

        $tenancy_id = $this->input->get_post('tenancy_id');
        $agency_id = $this->input->get_post('agency_id');
        $ttarr = [];

        $tt =  $this->property_tree_model->get_tenant($agency_id, $tenancy_id);
        $ttmo = $tt->contacts;

       $tt = array(
        'name' => $tt->name,
        'fname' => $ttmo[0]->first_name,
        'lname' => $ttmo[0]->last_name,
        'email' => $ttmo[0]->email_address,
        'phone' => $ttmo[0]->mobile_phone_number
       );

       echo json_encode($tt);

    }

    
}