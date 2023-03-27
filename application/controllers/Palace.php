<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Palace extends CI_Controller {

    function __construct(){
        parent::__construct();

        $this->load->model('palace_model');
        $this->load->model('api_model');
        $this->load->model('agency_api_model');
    }

    public function index(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = 'Palace Bulk Match';
        $country_id = $this->config->item('country');

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
            'api_id' => 4,
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
        //echo $this->db->last_query();
        //exit();

        $this->load->view('templates/inner_header', $data);
        $this->load->view('authentication/palace',$data);
        $this->load->view('templates/inner_footer', $data);
    }


    // get Palace list
    public function ajax_bulk_connect_get_palace_list(){ 
        //echo "TEST";
        //exit();

        $agency_id = $this->input->get_post('agency_id');
        $hide_palace_archived_prop = $this->input->get_post('hide_palace_archived_prop');
        $show_all_hidden_prop = $this->input->get_post('show_all_hidden_prop');

        //$palaceList = $this->palace_model->get_all_palace_property($agency_id);
        $palaceList = $this->palace_model->get_all_properties($agency_id);

        //print_r($palaceList);

        $palace_api = 4; // Palace
        
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

            /*
            $isConnectedCheck_str = "
                SELECT 
                    `palace_prop_id`
                FROM `property`
                WHERE `palace_prop_id` IS NOT NULL
                AND `agency_id` = '{$agency_id}'
                ORDER BY `address_2` ASC, `address_3` ASC, `address_1` ASC
            ";
            */

            /*
            $isConnectedCheck_str = "
                SELECT `apd`.`api_prop_id` , `p`.`property_id`
                FROM `property` AS `p`
                LEFT JOIN `api_property_data` AS `apd` ON p.`property_id` = apd.`crm_prop_id`
                WHERE apd.api_prop_id IS NOT NULL
                AND `apd`.`api` = 4
            ";
            */

            $isConnectedCheck_str = "
            SELECT apd_palace.`api_prop_id`
            FROM `property` AS p
            LEFT JOIN `api_property_data` AS apd_palace ON ( p.property_id = apd_palace.crm_prop_id AND apd_palace.api= {$palace_api} )
            WHERE (
                apd_palace.`api_prop_id` IS NOT NULL AND 
                apd_palace.`api_prop_id` != '' AND 
                apd_palace.`api` = {$palace_api}
            )
            AND p.`agency_id` = '{$agency_id}'
            ORDER BY p.`address_2` ASC, p.`address_3` ASC, p.`address_1` ASC
            ";

            $isConnectedCheck_sql = $this->db->query($isConnectedCheck_str);
            $arrConnected = $isConnectedCheck_sql->result_array();

            $isConnectArr = array();
            foreach ($arrConnected as $val) {
                array_push($isConnectArr, $val['api_prop_id']);
            }

            foreach ($palaceList as $key => $row) {
                if (in_array($row->PropertyCode, $isConnectArr)) {
                    unset($palaceList[$key]);
                }
            }   

            foreach ($palaceList as $key => $row) { 
                
                // if( $this->is_pme_prop_connected($row->Id) == false ){

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

                        'property_id' => $row->PropertyCode,
                        'agency_id' => $agency_id,

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


                    $hide_row = false;
                    if( $hide_palace_archived_prop == 1 ){
                        if( $row->PropertyArchived == true ){
                            $hide_row = true;
                        }
                    }


                    // check if property is set as hidden
                    $api_id = 4; // Palace
                    $agency_api_model_params = array(
                        'api_prop_id' => $row->PropertyCode,                                                                
                        'agency_id' => $agency_id,
                        'api_id' => $api_id,
                    ); 
                    
                    $is_api_property_hidden = $this->agency_api_model->is_api_property_hidden($agency_api_model_params);
                    
                    if( $is_api_property_hidden == true && $show_all_hidden_prop != 1 ){
                        $hide_row = true;
                    } 
                     
                   

                    if( $ignore_issue != 1 && $hide_row == false ){
                    ?>
                        <tr class="<?php echo ( $row->PropertyArchived == 1 )?'palace_archived_row':null; ?>">
                            <?php 
                                if (trim($row->PropertyUnit) != "") {
                                    $addUnit = $row->PropertyUnit . "/";
                                }else {
                                    $addUnit = "";
                                }
                            ?>
                            <td class="pmeAdd palaceAdd"><?=$addUnit.$row->PropertyAddress1 . " " . $row->PropertyAddress2 . ", " . $row->PropertyAddress3 . " " . $row->PropertyAddress4 . " " . $row->PropertyPostCode ?></td>
                            <td style="display: none;"><?=$row->PropertyCode?></td>
                            <td style="display: none;" class="sort_index">0</td>
                            <td>
                                <span class="checkbox">
                                    <input type="checkbox" id="pme_prop_chk-<?php echo $key; ?>" class="pme_prop_chk api_prop_chk">
                                    <label for="pme_prop_chk-<?php echo $key; ?>"></label>
                                </span>
                            </td>
                            <td>
                                <input type="hidden" class="pme_full_address" value="<?php echo $row->PropertyAddress1; ?>" />

                                <input type="hidden" class="pme_addr_unit" value="<?php echo $row->PropertyUnit; ?>" />
                                <input type="hidden" class="pme_addr_number" value="<?php echo $row->PropertyAddress1 ?>" />
                                <input type="hidden" class="pme_addr_street" value="<?php echo $row->PropertyAddress2; ?>" />
                                <input type="hidden" class="pme_addr_suburb" value="<?php echo $row->PropertyAddress3; ?>" />                            
                                <input type="hidden" class="pme_addr_postalcode" value="<?php echo $row->PropertyPostCode ?>" />
                                <input type="hidden" class="pme_addr_state" value="<?php echo $row->PropertyAddress4; ?>" />

                                <input type="hidden" class="pme_addr_country" value="<?php echo "Country"; ?>" />
                                <input type="hidden" class="pme_addr_bldg_name" value="<?php echo "BuildingName"; ?>" />
                                <input type="hidden" class="pme_addr_mailbox" value="<?php echo "MailboxName"; ?>" />
                                <input type="hidden" class="lat" />
                                <input type="hidden" class="lng" />
                                <input type="hidden" class="pme_addr_text" value="<?php echo "Text"; ?>" />   
                                <input type="hidden" class="pme_addr_reference" value="<?php echo $row->Address->Reference; ?>" />                                                    

                                <input type="hidden" class="pme_prop_id" value="<?php echo $row->PropertyCode; ?>" />
                                <input type="hidden" class="api_prop_id" value="<?php echo $row->PropertyCode; ?>" />
                                <input type="hidden" class="key_number" value="<?php echo $row->PropertyKeyNo; ?>" />
                                <input type="hidden" class="tenants_contact_id" value="" />
                                <input type="hidden" class="owner_contact_id" value="<?=$row->PropertyOwnerCode?>" />
                                <input type="hidden" class="is_archived" value="<?php echo $row->PropertyArchived; ?>" />

                                <input type="hidden" class="note_btn_class" value="<?php echo "pme_note_btn{$note_ctr}"; ?>" />
                            
                                <button type="button" class="btn btn-primary btn_add_prop_indiv">Add Property</button>

                                <?php                                                                                     
                                if( $pnv_note != '' ){  // has notes                             
                                ?>                                
                                    <button type="button" class="btn btn-primary view_note_btn jFaded">View</button>
                                <?php
                                }else{ ?>
                                    <button type="button" class="btn btn-primary btn_note pme_note_btn<?php echo $note_ctr; ?>">Note</button>
                                <?php
                                }
                                
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
                    $note_ctr++;
                    }
                // }
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

    // get CRM list
    public function ajax_bulk_connect_get_crm_list(){
        
        //$agency_id = 1448;
        $agency_id = $this->input->get_post('agency_id');
        $palace_api = 4; // Palace

        $sel_query = "
            `property_id`,
            `address_1`,
            `address_2`,
            `address_3`,
            `state`,
            `postcode`,
            `is_sales`
        ";
        $this->db->select($sel_query);
        $this->db->from('property AS p');
        $this->db->join("api_property_data AS apd_palace", "p.property_id = apd_palace.crm_prop_id AND apd_palace.api= {$palace_api}", 'left');
        $this->db->where('p.agency_id', $agency_id);
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where("(
            apd_palace.api_prop_id IS NULL OR 
            apd_palace.api_prop_id = '' 
        )");
        //$this->db->where("( palace_prop_id = '' OR palace_prop_id IS NULL )");
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

    /**
     * This add Palace ID to the CRM properties that is bulk connected
    */ 
    public function bulk_connect_all() {

        $agency_filter = $this->input->get_post('agency_id');
        $crmArr = $this->input->get_post('crmArr');
        $pmeArr = $this->input->get_post('pmeArr');
        $connect_deleted_nlm_prop = $this->input->get_post('connect_deleted_nlm_prop');

        /*
        for ($i=0; $i < count($pmeArr); $i++) { 
            $updateData = array(
                'palace_prop_id' => $pmeArr[$i]
            );
            $this->db->where('property_id', $crmArr[$i]);
            $this->db->update('property', $updateData);
        }

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));
        */

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
                $log_details = "Property was restored from NLM by connecting on <b>Palace</b> bulk match.";
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
                'title' => 70, // Palace API
                'details' => 'Property <b>Linked</b> to <b/>Palace</b> on Bulk Match',
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
                    'api'         => 4
                );
                $this->db->insert('api_property_data', $updateData_Api);
            }
            else{
                $updateData_Api = array(
                    'api_prop_id' => $pmeArr[$i]
                );
                $this->db->where('crm_prop_id', $crmArr[$i]);
                $this->db->where('api', 4);
                $this->db->update('api_property_data', $updateData_Api);
            }
        }

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));
    }

    public function property($property_id = 0, $agency_id) {

        $data['title'] = 'Palace Tenant & Property Details';
        $uri = "/palace/property/{$this->uri->segment(3)}/{$this->uri->segment(4)}";
        $data['uri'] = $uri;

        $this->db->select('p.property_id, p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, p.palace_prop_id, a.agency_id');
        $this->db->from('property as p');
        $this->db->join('`agency` AS a', 'p.`agency_id` = a.`agency_id`', 'left');
        $this->db->where('property_id', $property_id);
        $query = $this->db->get();
        $propDet =  $query->row();

        if(!empty($propDet)){
            $api_prop_id = $this->get_palace_api_prop_id($property_id);
        }

        if ($api_prop_id != '') { 
            /*
            $params = array(
                'agency_id' => $agency_id,
                'palace_id' => $propDet->palace_prop_id,
                'palace_prop_id' => $propDet->palace_prop_id
            );
            */

            $params = array(
                'agency_id' => $agency_id,
                'palace_id' => $api_prop_id,
                'palace_prop_id' => $api_prop_id
            );

            $lotList = $this->palace_model->get_all_property_by_prop_code($params);
            
            //$tenant_json_dec = $this->palace_model->get_all_tenant_by_prop_code_v2($params);

            // get tenants
            $params = array(
                'agency_id' => $agency_id,            
                'palace_prop_id' => $api_prop_id
            );   
                     
            $tenant_json_dec = $this->palace_model->get_tenants_by_property($params);            

            $data['lotList'] = $lotList[0];
            $data['tenant_json_dec'] = $tenant_json_dec;

            // update property key number
            if( $property_id > 0 && !empty((array) $lotList[0]->PropertyKeyNo)){
                $key_update_data = array(
                    'key_number' => $lotList[0]->PropertyKeyNo
                );                        
                $this->db->where('property_id', $property_id);
                $this->db->update('property', $key_update_data);
            }                                                
            
            $this->db->select('*');
            $this->db->from('property_tenants');
            $this->db->where('property_id', $property_id);
            $this->db->where('active', 1);
            $query = $this->db->get();
            $data['crmTenant'] = $query->result_array();
            $data['propId'] = $property_id;

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

            if( $property_id > 0 ){

                $params = array(
                    'sel_query' => $sel_query,                                                                
                    'property_id' => $property_id,
                    'join_table' => array('countries'),
                    'display_query' => 0
                );
                $crm_prop_sql = $this->properties_model->get_properties($params);
                $data['crm_prop'] = $crm_prop_sql->row();

            }                        
            
            $this->load->view('templates/inner_header', $data);
            $this->load->view('authentication/ajax_tenant_detail_palace',$data); // already connected page
            $this->load->view('templates/inner_footer', $data);
        }else {
            $palaceList = $this->palace_model->get_all_palace_property($agency_id);
            $data['palaceList'] = $palaceList;
            $data['propDet'] = $propDet;
            $this->load->view('templates/inner_header', $data);
            $this->load->view('authentication/palace_connect',$data);  // not connected page
            $this->load->view('templates/inner_footer', $data);
        }

    }

    /**
     * Get PMe ID associated with a API property data table
     * @param  int $propId The int to know what property to get
     * @return PMe ID
    */ 
    public function get_palace_api_prop_id($propId) {
        $this->db->select('api_prop_id');
        $this->db->from('api_property_data');
        $this->db->where('crm_prop_id', $propId);
        $query = $this->db->get();
        $propId =  $query->row();
        return $propId->api_prop_id;
    }

    public function get_all_tenant_by_prop_code_via_json() {

        $agency_id = $this->input->get_post('agency_id');
        $palace_id = $this->input->get_post('palace_id');
        $api_owner_code = $this->input->get_post('api_owner_code');
        
        $params['agency_id'] = $agency_id;
        $params['palace_id'] = $palace_id;
        $resArr = $this->palace_model->get_all_tenant_by_prop_code($params);

        $data['tenants'] = $resArr;

        $ownerList = $this->palace_model->get_all_palace_owner($agency_id);
        $filteredOwner = array_filter($ownerList, function($elementPal)  use ($api_owner_code) {
            return $elementPal->OwnerCode === $api_owner_code;
        });

        $filteredOwner = array_values($filteredOwner);

        $data['landlord'] = $filteredOwner;

        echo json_encode($data);
    }

    public function ajax_function_link_property() {
        $palaceId = $this->input->get_post('palaceId');
        $crmId = $this->input->get_post('crmId');

        $this->db->where('crm_prop_id', $crmId);
        $this->db->delete('api_property_data');

        $insertData = array(
            'api_prop_id' => $palaceId,
            'active' => 1,
            'crm_prop_id' => $crmId,
            'api' => 4
        );
        $this->db->insert('api_property_data', $insertData);
        $insertStat = true;
        echo json_encode(array("updateStat" => $insertStat));

        /*
        //Checking the existing property then set the API property ID

        $this->db->select('*');
        $this->db->from('api_property_data');
        $this->db->where('crm_prop_id', $crmId);
        $query = $this->db->get();
        $res = $query->result_array();
        
        if(!empty($res)){
            $updateData = array(
                'api_prop_id' => $palaceId,
                'active' => 1
            );
            $this->db->where('crm_prop_id', $crmId);
            $this->db->update('api_property_data', $updateData);
            $updateStat = true;
            echo json_encode(array("updateStat" => $updateStat));
        }
        else{
            $insertData = array(
                'api_prop_id' => $palaceId,
                'active' => 1,
                'crm_prop_id' => $crmId,
                'api' => 4
            );
            $this->db->insert('api_property_data', $insertData);
            $insertStat = true;
            echo json_encode(array("updateStat" => $insertStat));
            /*
            $updateData = array(
                'palace_prop_id' => $palaceId
            );
            $this->db->where('property_id', $crmId);
            $this->db->update('property', $updateData);
            $updateStat = ($this->db->affected_rows() != 1) ? false : true;
            echo json_encode(array("updateStat" => $updateStat));
            */
        //}

        // insert property log
        $params = array(
            'title' => 70, // Palace API
            'details' => 'Property <b>Linked</b> to <b/>Palace</b>',
            'display_in_vpd' => 1,            
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $crmId
        );
        $this->system_model->insert_log($params);

    }

    public function get_palace_prop_id($propId) {
        $this->db->select('palace_prop_id');
        $this->db->from('property');
        $this->db->where('property_id', $propId);
        $query = $this->db->get();
        $propId =  $query->row();
        return $propId->palace_prop_id;
    }

    public function ajax_function_unlink_property() {
        $palaceId = $this->input->get_post('palaceId');
        $crmId = $this->input->get_post('crmId');

        $this->db->where('crm_prop_id', $crmId);
        $this->db->delete('api_property_data');

        /*
        //Setting api_prop_id => NULL
        
        $updateData = array(
            'palace_prop_id' => null
        );
        $this->db->where('property_id', $crmId);
        $this->db->update('property', $updateData);

        $updateStat = ($this->db->affected_rows() != 1) ? false : true;
        
        if($updateStat == 0){
            
            $updateApiData = array(
                'api_prop_id' => null
            );
            $this->db->where('crm_prop_id', $crmId);
            $this->db->update('api_property_data', $updateApiData);
    
            $updateStat = ($this->db->affected_rows() != 1) ? false : true;
        }
        */

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));

        // insert property log
        $params = array(
            'title' => 70, // Palace API
            'details' => 'Property <b>Unlinked</b> to <b/>Palace</b>',
            'display_in_vpd' => 1,            
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $crmId
        );
        $this->system_model->insert_log($params);

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
            //$street_name = $pme_prop_dec->pme_addr_street;
            $street_name = $this->db->escape_str(str_replace("’","'",$pme_prop_dec->pme_addr_street)); //replace bad apostrophe to correct one
            //$suburb = $pme_prop_dec->pme_addr_suburb;
            $suburb = $this->db->escape_str(str_replace("’","'",$pme_prop_dec->pme_addr_suburb)); //replace bad apostrophe to correct one
           // $state = $pme_prop_dec->pme_addr_state;
            $state = $this->db->escape_str(str_replace("’","'",$pme_prop_dec->pme_addr_state)); //replace bad apostrophe to correct one
            $postcode = $pme_prop_dec->pme_addr_postalcode;
    
            //$lat = $pme_prop_dec->lat;
            //$lng = $pme_prop_dec->lng;

            $address = "{$street_unit} {$street_num} {$street_name} {$suburb} {$state} {$postcode}";                                                  
            $coordinate = $this->system_model->getGoogleMapCoordinates($address);	
            $lat = $coordinate['lat'];
            $lng = $coordinate['lng'];     
            
            $pme_prop_id = $pme_prop_dec->pme_prop_id;
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
    
            $street_num_fin = implode("/",$street_arr);
            $street_name_fin = $this->system_model->getStreetAbrvFullName($street_name);
    
            //$duplicate_query = $this->properties_model->check_duplicate_property($street_num_fin,$street_name_fin,$suburb,$state,$postcode);
            
            $check_dup_params = array(
                'street_num_fin' => $street_num_fin,
                'street_name_fin' => $street_name_fin,
                'suburb' => $suburb,
                'state' => $state,
                'postcode' => $postcode
            );
            $duplicate_query = $this->properties_model->check_duplicate_full_address($check_dup_params);
            //echo $this->db->last_query();
            //exit();

            if( $duplicate_query->num_rows()>0 ){ // existing property found
    
                $duplicate_row = $duplicate_query->row_array(); 

                $dup_agency_id = $duplicate_row['agency_id'];

                //$dup_agency_name = (  $dup_agency_id == $agency_id )?'this Agency':$duplicate_row['agency_name'];
                //$dup_arr[] = "<a href='/property_me/property/{$duplicate_row['property_id']}/{$dup_agency_id}' target='_blank'>{$pme_full_address}</a> in agency <a href='{$this->config->item('crm_link')}/view_agency_details.php?id={$duplicate_row['agency_id']}'>{$dup_agency_name}</a>";
    
                
                $dup_arr[] = array(
                    'dup_property_id' => $duplicate_row['property_id'], 
                    'dup_property_address' => "{$duplicate_row['p_address_1']} {$duplicate_row['p_address_2']}, {$duplicate_row['p_address_3']} {$duplicate_row['p_state']} {$duplicate_row['p_postcode']}", 
                    'dup_prop_deleted' => $duplicate_row['is_nlm'],      
                    'dup_agency_id' => $duplicate_row['agency_id'],                                          
                    'dup_agency_name' => $duplicate_row['agency_name'],
                    'pme_prop_id' => $pme_prop_id                                                                   
                );
           
            }else{


                if( $disable_add != 1 ){

                    // Hume Community Housing Association
                    $prop_comments = '';
                    if( $agency_id==1598 ){            
                        $prop_comments = 'Please install 9vLi or 240v only. DO NOT INSTALL 240vLi';            
                    }

                    $ownerList = $this->palace_model->get_all_palace_owner($agency_id);
                    $filteredOwner = array_filter($ownerList, function($elementPal)  use ($owner_contact_id) {
                        return $elementPal->OwnerCode === $owner_contact_id;
                    });

                    $filteredOwner = array_values($filteredOwner);
                    foreach( $filteredOwner as $palace_landlord ){
                        $landlord_firstname = empty((array)$palace_landlord->OwnerFirstName) ? "" : $palace_landlord->OwnerFirstName;
                        $landlord_lastname = empty((array)$palace_landlord->OwnerLastName) ? "" : $palace_landlord->OwnerLastName;
                    }
        
                    // INSERT PROPERTY
                    // removed inserting lat and lng from API bec sometimes they have some weird coordinate like -999
                    // better leave it empty bec tech runs has auto-inserts of coordinate if they are empty on load

                    /*
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
                        'comments' => $prop_comments,                     
                        'palace_prop_id' => $pme_prop_id
                    );
                    $add_property = $this->properties_model->add_property($property_data);
                    */

                    // replace some state, only on NZ
                    if( $this->config->item('country') == 2 ){ // NZ
                        $state = $this->system_model->replace_state($state);
                    }                    

                    $insert_prop_sql_str = "
                    INSERT INTO
                    `property` (
                        `agency_id`,
                        `address_1`,
                        `address_2`,
                        `address_3`,
                        `state`,
                        `postcode`,
                        `added_by`,
                        `key_number`,
                        `landlord_firstname`,
                        `landlord_lastname`,
                        `comments`
                    )
                    VALUES(
                        '{$agency_id}',
                        '{$street_num_fin}',
                        '{$street_name_fin}',
                        '{$suburb}',
                        '{$state}',
                        '{$postcode}',
                        '{$this->session->staff_id}',
                        '{$key_number}',
                        '{$landlord_firstname}',
                        '{$landlord_lastname}',
                        '{$prop_comments}'
                    ) 
                    ";
                    $this->db->query($insert_prop_sql_str);

                    if ($this->db->affected_rows() > 0) {
                        $add_property =  true;
                        $prop_insert_id = $this->db->insert_id();
                    } else {
                        $add_property =  false;
                    }                    
                    
                    $separated_data = array(
                        'crm_prop_id' => $prop_insert_id,
                        'api'         => 4,
                        'api_prop_id' => $pme_prop_id
                    );
                    
                    $add_separated_data = $this->properties_model->add_data_property($separated_data);

                    if( $add_property && !empty($prop_insert_id) ){
        
                        // insert property log
                        $params = array(
                            'title' => 2, //New Property Added
                            'details' => 'Added from Palace Bulk Match',
                            'display_in_vpd' => 1,
                            'agency_id' => $agency_id,
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $prop_insert_id
                        );
                        $this->system_model->insert_log($params); 
        
                        
                        // get Palace tenants
                        $palace_params = array(
                            'agency_id' => $agency_id,
                            'palace_id' => $pme_prop_id
                        );
                        $tenant_res = $this->palace_model->get_all_tenant_by_prop_code($palace_params);
                        $tenant_arr = [];
                        foreach( $tenant_res as $palace_tenant ){
        
                            $tenant_arr[] = array(
                                'property_id' =>  $prop_insert_id,
                                'tenant_firstname' => empty((array)$palace_tenant->TenancyTenants->DetailedTenant->TenantFirstName) ? "" : $palace_tenant->TenancyTenants->DetailedTenant->TenantFirstName,
                                'tenant_lastname' => empty((array)$palace_tenant->TenancyTenants->DetailedTenant->TenantLastName) ? "" : $palace_tenant->TenancyTenants->DetailedTenant->TenantLastName,
                                'tenant_mobile' => empty((array)$palace_tenant->TenancyTenants->DetailedTenant->TenantPhoneMobile) ? "" : $palace_tenant->TenancyTenants->DetailedTenant->TenantPhoneMobile,
                                'tenant_landline' => empty((array)$palace_tenant->TenancyTenants->DetailedTenant->TenantPhoneHome) ? "" : $palace_tenant->TenancyTenants->DetailedTenant->TenantPhoneHome,
                                'tenant_email' => empty((array)$palace_tenant->TenancyTenants->DetailedTenant->TenantEmail) ? "" : $palace_tenant->TenancyTenants->DetailedTenant->TenantEmail,
                                'active' => 1
                            );
                        }
        
                        if(!empty($tenant_arr)){
                            $this->properties_model->add_tenants($tenant_arr, 'batch'); //  param insert batch otherwise 0 for normal
                        }
        
                        // get agency services
                        /* ## disable as per Ben's request > reason:  No need to insert NR service status
                        $params = array(
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

    public function ajax_upload_file() {

        $this->load->model('/inc/job_functions_model');
        $agencyId = $this->input->get_post('agencyId');
        $selected = $this->input->get_post('selected');
        $jId = $this->input->get_post('jId');

        if ( 0 < $_FILES['file']['error'] ) {
            echo json_encode(array("err" => true, "agencyId" => $agencyId, "selected" => $selected, "file" => $_FILES['file']['name']));
        }
        else {

            $job_details = $this->job_functions_model->getJobDetails2(501,$query_only = false);
            $res = $this->palace_model->send_all_pdf_to_palace($agencyId, $selected, $_FILES['file'], "sample.pdf", $job_details);

            echo json_encode(array("err" => $res, "agencyId" => $agencyId, "selected" => $selected, "file" => $_FILES['file']['name']));
        }
    }

    public function send_all_certificates_and_invoices() {
        $result = $this->palace_model->send_all_certificates_and_invoices();
        echo $result;
    }

    public function send_all_certificates_and_invoices_via_vjd() {
        $this->load->model('Palace_model'); 

        $job_id = $this->input->get_post('job_id');
        $url = $this->input->get_post('url');
        $isUploaded = true;
        if ($job_id == "" || !is_numeric($job_id)) {
            $status = false;
            $msg = "Invalid Access";
        }else {
            $result = $this->Palace_model->send_all_certificates_and_invoices_via_vjd($job_id);
            $status = $result['status'];
            $msg = $result['msg'];
        } 
        $isUploaded = $isUploaded ? 1 : 0;
        $url = $this->config->item("crm_link") . '/view_job_details.php?id=' . $job_id;
        header("Location: ". $url . "&palace_upload_status=". $status . "&palace_msg=".$msg);
    }

    public function send_all_certificates_and_invoices_via_vjd_payload_only() {
        $this->load->model('Palace_model'); 

        $job_id = $this->input->get_post('job_id');
        $url = $this->input->get_post('url');
        $isUploaded = true;
        if ($job_id == "" || !is_numeric($job_id)) {
            $status = false;
            $msg = "Invalid Access";
        }else {
            $result = $this->Palace_model->send_all_certificates_and_invoices_via_vjd_payload_only($job_id);
            $status = $result['status'];
            $msg = $result['msg'];
        } 
        $isUploaded = $isUploaded ? 1 : 0;
        $url = $this->config->item("crm_link") . '/view_job_details.php?id=' . $job_id;
        //header("Location: ". $url . "&palace_upload_status=". $status . "&palace_msg=".$msg);
    }

    // Get supplier to Palace
    public function supplier_palace(){

        $this->load->model('api_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Palace Suppliers";
        $uri = '/palace/supplier_palace';        

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');

        $sel_query = "
            agen_api_tok.`agency_api_token_id`, 
            agen_api_tok.`connection_date`,
            a.`agency_id`,
            a.`agency_name`,
            a.`palace_supplier_id`,
            a.`palace_agent_id`,
            a.`palace_diary_id`
        ";
        $api_token_params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'api_id' => 4, // palace
            'group_by' => 'agen_api_tok.`agency_id`',
            'join_table' => array('agency'),
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
        $this->load->view('/authentication/palace_supplier', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    // GET Palace Suppliers per agency
    public function get_palace_supplier(){
        $agency_id = $this->input->get_post('agency_id');
        $result = $this->palace_model->get_all_palace_supplier($agency_id);
        $id = $this->get_palace_supplier_id_by_agency($agency_id);
        $contact = $this->palace_model->get_palace_supplier_by_id($agency_id, $id->palace_supplier_id);
        echo json_encode(array("supp"=>$id->palace_supplier_id, "palace"=> $result, "suppName" =>$contact[0], "agentId" => $id->palace_agent_id, "diaryId" => $id->palace_diary_id));
    }

    public function get_palace_supplier_id_by_agency($agencyId) {
        $sql_str = "
        SELECT palace_supplier_id, palace_agent_id, palace_diary_id
        FROM `agency`
        WHERE agency_id = {$agencyId}    
        ";
        $sql = $this->db->query($sql_str);
        return $sql->row(); 

    }

    public function remove_supplier_id_by_agency() {

        $agencyId = $this->input->get_post('agencyId');

        $agenConn = $this->get_palace_supplier_id_by_agency($agencyId);
        if ((!is_null($agenConn->palace_agent_id) && $agenConn->palace_agent_id !== "")
            && (!is_null($agenConn->palace_diary_id) && $agenConn->palace_diary_id !== "")) {
            echo false;
        }else {
            if (isset($agencyId) && !empty($agencyId) ) {
                $query = "UPDATE agency SET palace_supplier_id = NULL WHERE agency_id = {$agencyId}";

                if($this->db->query($query)){
                    echo true;
                }
                else {
                    echo false;
                }
            }else {
                echo false;
            }
        }
    }

    public function update_supplier_id_by_agency() {

        $id = $this->input->get_post('id');
        $agencyId = $this->input->get_post('agencyId');

        $agenConn = $this->get_palace_supplier_id_by_agency($agencyId);
        // if (is_null($agenConn->palace_agent_id) || $agenConn->palace_diary_id == "") {
            // echo false;
        // }else {
            $query = "UPDATE agency SET palace_supplier_id = '{$id}' WHERE agency_id = {$agencyId}";

            if($this->db->query($query)){
                echo true;
            }
            else {
                echo false;
            }
        // }

    }


    // Get supplier to Palace
    public function agent_palace(){

        $this->load->model('api_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Palace Agents";
        $uri = '/palace/agent_palace';        

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');

        $sel_query = "
            agen_api_tok.`agency_api_token_id`, 
            agen_api_tok.`connection_date`,
            a.`agency_id`,
            a.`agency_name`,
            a.`palace_supplier_id`,
            a.`palace_agent_id`
        ";
        $api_token_params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'api_id' => 4, // palace
            'group_by' => 'agen_api_tok.`agency_id`',
            'join_table' => array('agency'),
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
        $this->load->view('/authentication/palace_agent', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    // GET Palace Agent per agency
    public function get_palace_agent(){
        $agency_id = $this->input->get_post('agency_id');
        $result = $this->palace_model->get_all_palace_agent($agency_id);
        $id = $this->get_palace_agent_id_by_agency($agency_id);
        $contact = $this->palace_model->get_palace_agent_by_id($agency_id, $id->palace_agent_id);
        echo json_encode(array("agent"=>$id->palace_agent_id, "palace"=> $result, "agentName" =>$contact[0], "diaryId" => $id->palace_diary_id));
    }

    public function get_palace_agent_id_by_agency($agencyId) {
        $sql_str = "
        SELECT palace_agent_id, palace_supplier_id, palace_diary_id
        FROM `agency`
        WHERE agency_id = {$agencyId}    
        ";
        $sql = $this->db->query($sql_str);
        return $sql->row(); 

    }

    public function update_agent_id_by_agency() {

        $id = $this->input->get_post('id');
        $agencyId = $this->input->get_post('agencyId');
        
        $agenConn = $this->get_palace_agent_id_by_agency($agencyId);
        if (is_null($agenConn->palace_supplier_id) || $agenConn->palace_supplier_id == "") {
            echo false;
        }else {
            $query = "UPDATE agency SET palace_agent_id = '{$id}' WHERE agency_id = {$agencyId}";

            if($this->db->query($query)){
                echo true;
            }
            else {
                echo false;
        }
        }
    }

    public function remove_agent_id_by_agency() {

        $agencyId = $this->input->get_post('agencyId');

        $agenConn = $this->get_palace_agent_id_by_agency($agencyId);
        if ((!is_null($agenConn->palace_diary_id) && $agenConn->palace_diary_id !== "")) {
            echo false;
        }else {
            if (isset($agencyId) && !empty($agencyId) ) {
                $query = "UPDATE agency SET palace_agent_id = NULL WHERE agency_id = {$agencyId}";

                if($this->db->query($query)){
                    echo true;
                }
                else {
                    echo false;
                }
            }else {
                echo false;
            }
        }

    }

    public function processMergedSendToEmails() {
        $this->load->model('/inc/email_functions_model');
        $job['invoice_pm_only'] = 0;
        $job['pm_id_new'] = 2440;
        $job['allow_indiv_pm_email_cc'] = 1;
        $emails = $this->email_functions_model->processMergedSendToEmails(1261, "rentals.salisbury@ljh.com.au", $job);

        if(!is_array($emails)) $emails = array($emails);

        $agency_for_check = 1261;
        $am_sql = $this->db->query("
            SELECT * 
            FROM `agency_maintenance` 
            WHERE `agency_id` = {$agency_for_check}
            AND `maintenance_id` > 0
        ");
        $pm_email = "";
        if( $job['allow_indiv_pm_email_cc']==1 && $am_sql->num_rows() <= 0 ){
            $pm_id = $job['pm_id_new'];

            if (!is_null($pm_id)) {
                $pm_sql = $this->db->query("
                    SELECT `email`
                    FROM `agency_user_accounts`
                    WHERE `agency_user_account_id` = {$pm_id}
                    AND `email` != ''
                    AND `email` IS NOT NULL
                ");
                if( $pm_sql->num_rows()>0 ){
                    $pm = $pm_sql->row_array();
                    $pm_email = trim($pm['email']);
                    $emails = array_diff($emails, array($pm_email));
                }
            }
        }


        $finalListToEamil = array();
        // array_push($finalListToEamil, $emails);
        $finalListToEamil['email_not_pm'] = $emails;
        if ($pm_email != "") {
            // array_push($finalListToEamil, $pm_email);
            $finalListToEamil['email_is_pm'] = $pm_email;
        }

        foreach ($finalListToEamil as $key => $value) {
            if (empty($value)) {
                unset($finalListToEamil[$key]);
            }
        }

                    echo "JOB ID: 614090";
        foreach ($finalListToEamil as $key => $emails) {

            if ($key == "email_is_pm") {

                if(true){
                    $email_is_pm_sent = true;
                    if (is_array($emails)) {
                        $sent_to_imp = implode(", ",$emails);
                    }else {
                        $sent_to_imp = $emails;
                    }
                    echo "<br/>";
                    echo "<br/>";
                    echo "<b>PM email</b> should have been sent an Invoice" . "<br/>";
                    echo $sent_to_imp;
                }

            }else {

                if(true){
                    $email_not_pm_sent = true;
                    if (is_array($emails)) {
                        $sent_to_imp = implode(", ",$emails);
                    }else {
                        $sent_to_imp = $emails;
                    }
                    echo "<br/><br/>";
                    echo "<b>Account email</b> should have been sent an Invoice" . "<br/>";
                    echo $sent_to_imp;
                }
            }

        }

    }

    // Get supplier to Palace
    public function diary_palace(){

        $this->load->model('api_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Palace Diary";
        $uri = '/palace/diary_palace';        

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');

        $sel_query = "
            agen_api_tok.`agency_api_token_id`, 
            agen_api_tok.`connection_date`,
            a.`agency_id`,
            a.`agency_name`,
            a.`palace_supplier_id`,
            a.`palace_diary_id`,
            a.`api_billable`
        ";
        $api_token_params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'api_id' => 4, // palace
            'group_by' => 'agen_api_tok.`agency_id`',
            'join_table' => array('agency'),
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
        $this->load->view('/authentication/palace_diary', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    // GET Palace diary per agency
    public function get_palace_diary(){
        $agency_id = $this->input->get_post('agency_id');
        $result = $this->palace_model->get_all_palace_diary($agency_id);
        $id = $this->get_palace_diary_id_by_agency($agency_id);
        $diaryCode = $this->palace_model->get_palace_diary_by_id($agency_id, $id->palace_diary_id);
        echo json_encode(array("diary"=>$id->palace_diary_id, "palace"=> $result, "diaryName" =>$diaryCode[0]));
    }

    public function get_palace_diary_id_by_agency($agencyId) {
        $sql_str = "
        SELECT palace_diary_id, palace_agent_id, palace_supplier_id
        FROM `agency`
        WHERE agency_id = {$agencyId}    
        ";
        $sql = $this->db->query($sql_str);
        return $sql->row(); 

    }

    public function update_diary_id_by_agency() {

        $id = $this->input->get_post('id');
        $agencyId = $this->input->get_post('agencyId');

        $agenConn = $this->get_palace_diary_id_by_agency($agencyId);
        if ((is_null($agenConn->palace_agent_id) || $agenConn->palace_agent_id == "") 
            && (is_null($agenConn->palace_supplier_id) || $agenConn->palace_supplier_id == "")) {
            echo false;
        }else {
            $query = "UPDATE agency SET palace_diary_id = '{$id}' WHERE agency_id = {$agencyId}";

            if($this->db->query($query)){
                echo true;
            }
            else {
                echo false;
            }
        }
    }

    public function remove_diary_id_by_agency() {

        $agencyId = $this->input->get_post('agencyId');

        // $agenConn = $this->get_palace_diary_id_by_agency($agencyId);
        // if ((!is_null($agenConn->palace_agent_id) && $agenConn->palace_agent_id !== "")) {
            // echo false;
        // }else {
            if (isset($agencyId) && !empty($agencyId) ) {
                $query = "UPDATE agency SET palace_diary_id = NULL WHERE agency_id = {$agencyId}";

                if($this->db->query($query)){
                    echo true;
                }
                else {
                    echo false;
                }
            }else {
                echo false;
            }
        // }
    }

    public function view_tenant_property(){

        $agency_id = $this->input->get_post('agency_id');
        $palace_prop_id = $this->input->get_post('palace_prop_id');

        if( $agency_id > 0 && $palace_prop_id != '' ){

            $params = array(
                'agency_id' => $agency_id,            
                'palace_prop_id' => $palace_prop_id
            );            
            $tenant_json_decoded = $this->palace_model->get_tenants_by_property($params);            

            echo "<pre>";
            print_r($tenant_json_decoded);
            echo "</pre>";

        }

       

    }

    public function export_properties(){

        $agency_id = $this->input->get_post('agency_id');

        if( $agency_id > 0 ){

            // get palace properties
            $palaceList = $this->palace_model->get_all_properties($agency_id);            

            $active_properties_arr = [];
            $inactive_properties_arr = [];

            foreach ($palaceList as $row){

                if( $row->PropertyArchived != 1 ){ // active

                    $active_properties_arr[] = array(
                        'property_id' => $row->PropertyCode,

                        'street_unit' => $row->PropertyUnit,
                        'street_number' => $row->PropertyAddress1,
                        'street_name' => $row->PropertyAddress2,
                        'suburb' => $row->PropertyAddress3,
                        'state' => $row->PropertyAddress4,
                        'postcode' => $row->PropertyPostCode,

                        'archived' => $row->PropertyArchived
                    );

                }else{ // inactive

                    $inactive_properties_arr[] = array(
                        'property_id' => $row->PropertyCode,

                        'street_unit' => $row->PropertyUnit,
                        'street_number' => $row->PropertyAddress1,
                        'street_name' => $row->PropertyAddress2,
                        'suburb' => $row->PropertyAddress3,
                        'state' => $row->PropertyAddress4,
                        'postcode' => $row->PropertyPostCode,

                        'archived' => $row->PropertyArchived
                    );                    

                }
                
            }


            // export to csv
            // file name
            $date_export = date('YmdHis');
            $filename = "palace_api_property_export{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            
            $csv_header = array("Street Unit","Street Number","Street Name","Suburb","State","Postcode");
            fputcsv($csv_file, $csv_header);
            
            foreach($active_properties_arr as $list_item){ 

                $csv_row = []; // clear                                                                                
                
                $csv_row[] = $list_item['street_unit'];
                $csv_row[] = $list_item['street_number'];
                $csv_row[] = $list_item['street_name'];
                $csv_row[] = $list_item['suburb'];
                $csv_row[] = $list_item['state'];
                $csv_row[] = $list_item['postcode'];                
                
                fputcsv($csv_file,$csv_row); 

            }
        
            fclose($csv_file); 
            exit; 

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
                        'api'         => 4
                    );
                    $this->db->insert('api_property_data', $updateData_Api);
                    $this->db->reset_query();

                }else{

                    $updateData_Api = array(
                        'api_prop_id' => $pmeArr[$i]
                    );
                    $this->db->where('crm_prop_id', $property_id_arr[$i]);
                    $this->db->where('api', 4);
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
    
}