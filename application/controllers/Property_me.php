<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Property_Me extends CI_Controller {
    
    private $clientId;
    private $clientSecret;
    private $clientScope;
    private $urlCallBack;
    private $accessTokenUrl;
    private $authorizeUrl; 
    private $suppArr = array();

    function __construct(){
        parent::__construct();
        ini_set('max_execution_time', 900); 

        $this->clientId = $this->config->item('PME_CLIENT_ID');
        $this->clientSecret = $this->config->item('PME_CLIENT_SECRET');
        $this->clientScope = $this->config->item('PME_CLIENT_Scope');
        $this->urlCallBack = urlencode($this->config->item('PME_URL_CALLBACK'));
        $this->accessTokenUrl = $this->config->item('PME_ACCESS_TOKEN_URL');
        $this->authorizeUrl = $this->config->item('PME_AUTHORIZE_URL');

        $this->load->model('pme_model');
        $this->load->model('encryption_model');
        $this->load->model('agency_api_model');
        $this->load->model('daily_model');
        $this->load->model('ourtradie_model');
    }

    /**
     * Load index page where you can login in PMe
    */ 
    public function index(){
        $data['title'] = 'PMe API Integration';
        $data['loginURL'] = $this->authorizeUrl . "?response_type=code&state=abc123&client_id=".$this->clientId."&scope=".$this->clientScope."&redirect_uri=".$this->urlCallBack;
        
        // Load propertymeapi login view
        $data['token_dead'] = $this->check_token_status();

        $this->load->view('templates/inner_header', $data);
        $this->load->view('authentication/propertyme',$data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Check if token status is still alive from the first login
     *
     * @return true or false
    */ 
    public function check_token_status() {
        $tok =  $this->session->userdata('access_token');
        if ($tok) {
            $checkTok = $this->check_if_token_expired();
            if ($checkTok->ResponseStatus->ErrorCode == "TokenException") {
                return "true";
            }else {
                return "false";
            }
        }else {
            return "true";
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

    /**
     * GET the property list on the PMe properties
     * @param  int  $display The int to toggle between data and page
     * @return PMe property or false
    */ 
    public function getResource($display = 0) {
        $accessTokenUrl = ($display == 0) ? $this->input->get_post('apiUrl') : "https://app.propertyme.com/api/v1/lots";
        $n = ($display == 0) ? $this->input->get_post('n') : "false";
        $access_token = $this->session->userdata('access_token');        

        $header = array("Authorization: Bearer {$access_token}",
                        "Content-Type: application/json");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $accessTokenUrl,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        if ($n === "true") {
            $loopData = json_decode($response);
            foreach ($loopData as $key => $value) {
                if ($value->TenantContactId !== NULL) {
                    $loopData[$key]->tenant_det = $this->get_tenant($value->id);
                }else {
                    $loopData[$key]->tenant_det = NULL;
                }
            }
            $data["list"] = json_encode($loopData);

            echo  $this->load->view('authentication/ajax_rep_table',$data,true);
        }else {
            if (json_encode($response)) {
                if ($display !== 0) {
                    return json_encode($response);
                }else {
                    echo json_encode($response);
                }
            }else {
                return false;
            }
        }

    }


    public function getResource_v2($display = 0) {
        
        $end_points = "https://app.propertyme.com/api/v1/lots";
        $n = ($display == 0) ? $this->input->get_post('n') : "false";

        $api_id = 1; // PMe

        // get access token
        $agency_id = 1448;
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        // call end point
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        $response = $this->pme_model->call_end_points_v2($pme_params);

        
        if ($n === "true") {
            $loopData = json_decode($response);
            foreach ($loopData as $key => $value) {
                if ($value->TenantContactId !== NULL) {
                    $loopData[$key]->tenant_det = $this->get_tenant($value->id);
                }else {
                    $loopData[$key]->tenant_det = NULL;
                }
            }
            $data["list"] = json_encode($loopData);

            echo  $this->load->view('authentication/ajax_rep_table',$data,true);
        }else {
            if (json_encode($response)) {
                if ($display !== 0) {
                    return json_encode($response);
                }else {
                    echo json_encode($response);
                }
            }else {
                return false;
            }
        }
        

    }

    /**
     * Load bulk connect page and get all PMe property and CRM property to compare
     *
     * @return authentication token or false
    */ 
    public function bulk_connect_old() {
        // $data['start_load_time'] = microtime(true);
        $data['title'] = 'Bulk Connect';
        $accessTokenUrl = $this->input->get_post('apiUrl');
        $access_token = $this->session->userdata('access_token');
        $country_id = $this->config->item('country');
        if (isset($access_token)) {

            $pmeList = $this->get_pme_lot_details();
            if ($pmeList->ResponseStatus->ErrorCode !== "TokenException") {
                if ($pmeList) {
                    $agency_filter = $this->input->get_post('agency_filter');

                    if (isset($agency_filter) && $agency_filter != '') {
                        $sel_query = "property_id,address_1,address_2,address_3,state,postcode";
                        $this->db->select($sel_query);
                        $this->db->from('property');
                        $this->db->where('agency_id', $agency_filter);
                        $this->db->where('propertyme_prop_id', null);
                        $query = $this->db->get();
                        $data['lists'] = $query;
                        $data['selected'] = $agency_filter;
                    }

                    $this->db->select('agency_id, agency_name, address_3, `franchise_groups_id`, `allow_indiv_pm`');
                    $this->db->from('agency');
                    $this->db->where('status', 'active');
                    $this->db->where('country_id', $country_id);
                    $this->db->where('agency_id !=', 1);
                    $this->db->where('trust_account_software', 7);
                    $this->db->order_by("agency_name", "asc");
                    $agencyQuery = $this->db->get();

                    if (isset($agency_filter) && $agency_filter != '') {
                        foreach ($pmeList as $key => $val) {
                            $pmeEx = $this->check_if_pmeId_exist($val->Id, $agency_filter);
                            if ($pmeEx) {
                                unset($pmeList[$key]);
                            }
                        }
                    }

                    $data['pmeList'] = $pmeList;
                    $data['agenList'] = $agencyQuery;

                    $this->load->view('templates/inner_header', $data);
                    $this->load->view('authentication/bulk_connect',$data);
                    $this->load->view('templates/inner_footer', $data);
                }else {
                    $sess_arr = array(
                        'pme_bulk_con' => 1
                    );
                    $this->session->set_userdata($sess_arr);
                    redirect('/property_me/index', 'refresh');
                }
            }else {
                $sess_arr = array(
                    'pme_bulk_con' => 1
                );
                $this->session->set_userdata($sess_arr);
                redirect('/property_me/index', 'refresh');
            }
        }else {
            $sess_arr = array(
                'pme_bulk_con' => 1
            );
            $this->session->set_userdata($sess_arr);
            redirect('/property_me/index', 'refresh');
        }
    }



    public function bulk_connect() {


        $this->load->model('api_model');
        $data['start_load_time'] = microtime(true);
        $data['title'] = 'PMe Bulk Match';
        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');

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
            'api_id' => 1,
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
        $this->load->view('authentication/bulk_connect',$data);
        $this->load->view('templates/inner_footer', $data);
       
    }

    // get CRM list
    public function ajax_bulk_connect_get_crm_list(){
        
        //$agency_id = 1448;
        $agency_id = $this->input->get_post('agency_id');
        $pme_api = 1; // PMe

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
        $this->db->join("api_property_data AS apd_pme", "p.property_id = apd_pme.crm_prop_id AND apd_pme.api= {$pme_api}", 'left');
        $this->db->where('p.agency_id', $agency_id);
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where("(
            apd_pme.api_prop_id IS NULL OR 
            apd_pme.api_prop_id = ''
        )");
        //$this->db->where("( propertyme_prop_id = '' OR propertyme_prop_id IS NULL )");
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
    public function ajax_bulk_connect_get_pme_list(){ 

        $agency_id = $this->input->get_post('agency_id');
        $hide_pme_archived_prop = $this->input->get_post('hide_pme_archived_prop');
        $show_all_hidden_prop = $this->input->get_post('show_all_hidden_prop');
        $pmeList = $this->get_pme_lot_details_v2($agency_id);
        $pme_api = 1; // PMe
        
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
            $pme_unique_arr = $pmeList;

            /*
            $isConnectedCheck_str = "
                SELECT 
                    `propertyme_prop_id`
                FROM `property`
                WHERE `propertyme_prop_id` IS NOT NULL
                ORDER BY `address_2` ASC, `address_3` ASC, `address_1` ASC
            ";
            */

            /*
            $isConnectedCheck_str = "
                SELECT `apd`.`api_prop_id` , `p`.`property_id`
                FROM `property` AS `p`
                LEFT JOIN `api_property_data` AS `apd` ON p.`property_id` = apd.`crm_prop_id`
                WHERE apd.api_prop_id IS NOT NULL
                AND `apd`.`api` = 1
            ";
            */

            $isConnectedCheck_str = "
            SELECT apd_pme.`api_prop_id`
            FROM `property` AS p
            LEFT JOIN `api_property_data` AS apd_pme ON ( p.property_id = apd_pme.crm_prop_id AND apd_pme.api= {$pme_api} )
            WHERE (
                apd_pme.`api_prop_id` IS NOT NULL OR
                apd_pme.`api_prop_id` != ''
            )
            AND p.`agency_id` = '{$agency_id}'
            ORDER BY p.`address_2` ASC, p.`address_3` ASC, p.`address_1` ASC
            ";

            $isConnectedCheck_sql = $this->db->query($isConnectedCheck_str);
            $arrConnected = $isConnectedCheck_sql->result_array();

            if (!is_array($pme_unique_arr)) {
                echo "ErrorCode: ". $pme_unique_arr->ResponseStatus->ErrorCode ."</br>";
                echo "Message: ". $pme_unique_arr->ResponseStatus->Message;
                exit();
            }

            $isConnectArr = array();
            foreach ($arrConnected as $val) {
                array_push($isConnectArr, $val['api_prop_id']);
            }

            foreach ($pme_unique_arr as $key => $row) {

                // hide connected properties
                if (in_array($row->Id, $isConnectArr)) {
                    unset($pme_unique_arr[$key]);
                }

                // exclude sales property
                if ( $row->SaleAgreementUpdatedOn !== "0001-01-01" ) {
                    unset($pme_unique_arr[$key]);
                }

            }   

            // re-create PMe array for joining archived list
            $pme_prop_arr = [];
            foreach( $pme_unique_arr as $pme_prop_obj ){
                $pme_prop_arr[] = $pme_prop_obj;
            }


            // get PMe archived properties                        
            $pme_archived_prop = $this->pme_model->get_all_archived_properties($agency_id);                        
            foreach( $pme_archived_prop as $pme_archived_prop_obj ){
                $pme_prop_arr[] = $pme_archived_prop_obj;
            }


            foreach ($pme_prop_arr as $key => $row) { 
                
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

                        'property_id' => $row->Id,
                        'property_source' => 2,

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

                    if( $pnv_sql->num_rows() > 0 ){
                        $pnv_row = $pnv_sql->row();
                        $pnv_id = $pnv_row->pnv_id;
                        $ignore_issue = $pnv_row->ignore_issue;
                        $pnv_note = $pnv_row->note;
                    }


                    $hide_row = false;
                    if( $hide_pme_archived_prop == 1 ){
                        if( $row->IsArchived == true ){
                            $hide_row = true;
                        }
                    }

                    
                    // check if property is set as hidden
                    $api_id = 1; // PropertyMe
                    $agency_api_model_params = array(
                        'api_prop_id' => $row->Id,                                                                
                        'agency_id' => $agency_id,
                        'api_id' => $api_id,
                    );                                                       
                    

                    $is_api_property_hidden = $this->agency_api_model->is_api_property_hidden($agency_api_model_params);
                    
                    if( $is_api_property_hidden == true && $show_all_hidden_prop != 1 ){
                        $hide_row = true;
                    }    
                    
                    $row_hl_class = null;

                    if( $is_api_property_hidden == true ){
                        $row_hl_class = 'pme_hidden_row';
                    }

                    if( $row->IsArchived == true ){
                        $row_hl_class = 'pme_archived_row';
                    }
                    

                    if( $ignore_issue != 1 && $hide_row == false ){
                    ?>
                        <tr class="<?php echo $row_hl_class; ?>">
                            <td class="pmeAdd"><?=$row->AddressText?></td>
                            <td style="display: none;"><?=$row->Id?></td>
                            <td style="display: none;" class="sort_index">0</td>
                            <td>
                                <span class="checkbox">
                                    <input type="checkbox" id="pme_prop_chk-<?php echo $key; ?>" class="pme_prop_chk api_prop_chk">
                                    <label for="pme_prop_chk-<?php echo $key; ?>"></label>
                                </span>
                            </td>
                            <td>
                                <input type="hidden" class="pme_full_address" value="<?php echo $row->AddressText; ?>" />

                                <input type="hidden" class="pme_addr_unit" value="<?php echo $row->Address->Unit; ?>" />
                                <input type="hidden" class="pme_addr_number" value="<?php echo $row->Address->Number; ?>" />
                                <input type="hidden" class="pme_addr_street" value="<?php echo $row->Address->Street; ?>" />
                                <input type="hidden" class="pme_addr_suburb" value="<?php echo $row->Address->Suburb; ?>" />                            
                                <input type="hidden" class="pme_addr_postalcode" value="<?php echo $row->Address->PostalCode; ?>" />
                                <input type="hidden" class="pme_addr_state" value="<?php echo $row->Address->State; ?>" />

                                <input type="hidden" class="pme_addr_country" value="<?php echo $row->Address->Country; ?>" />
                                <input type="hidden" class="pme_addr_bldg_name" value="<?php echo $row->Address->BuildingName; ?>" />
                                <input type="hidden" class="pme_addr_mailbox" value="<?php echo $row->Address->MailboxName; ?>" />
                                <input type="hidden" class="lat" value="<?php echo $row->Address->Latitude; ?>" />
                                <input type="hidden" class="lng" value="<?php echo $row->Address->Longitude; ?>" />
                                <input type="hidden" class="pme_addr_text" value="<?php echo $row->Address->Text; ?>" />   
                                <input type="hidden" class="pme_addr_reference" value="<?php echo $row->Address->Reference; ?>" />                                                    

                                <input type="hidden" class="pme_prop_id" value="<?php echo $row->Id; ?>" />
                                <input type="hidden" class="api_prop_id" value="<?php echo $row->Id; ?>" />
                                <input type="hidden" class="key_number" value="<?php echo $row->KeyNumber; ?>" />
                                <input type="hidden" class="tenants_contact_id" value="<?php echo $row->TenantContactId; ?>" />
                                <input type="hidden" class="owner_contact_id" value="<?php echo $row->OwnerContactId; ?>" />
                                <input type="hidden" class="is_archived" value="<?php echo $row->IsArchived; ?>" />

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

    public function unique_key($array,$keyname){

        $new_array = array();
        foreach($array as $key=>$value){
            $new_array[$key] = $value->$keyname;
        }

        $uarr = array_unique($new_array);
        $dups = array_diff($new_array, array_diff($uarr, array_diff_assoc($new_array, $uarr)));

        foreach ($dups as $key => $value) {
            //If there hasn't been a sales agreement added SaleAgreementUpdatedOn is "0001-01-01"
            if ($array[$key]->SaleAgreementUpdatedOn == "0001-01-01") {
                unset($array[$key]);
            }
        }

        return $array;

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
                    
                    $separated_data = array(
                        'crm_prop_id' => $prop_insert_id,
                        'api'         => 1,
                        'api_prop_id' => $pme_prop_id
                    );
                    
                    $add_separated_data = $this->properties_model->add_data_property($separated_data);

                    if( $add_property && !empty($prop_insert_id) ){
        
                        // insert property log
                        $params = array(
                            'title' => 2, //New Property Added
                            'details' => 'Added from PropertyMe Bulk Match',
                            'display_in_vpd' => 1,
                            'agency_id' => $agency_id,
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $prop_insert_id
                        );
                        $this->system_model->insert_log($params); 
        
                        
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

    public function spam_property_has_jobs_check($property_id){

        $sql_str = "
        SELECT COUNT(j.`id`) AS jcount          
        FROM `jobs` AS j
        WHERE j.`property_id` = {$property_id}    
        ";
        $sql = $this->db->query($sql_str);
        return $sql->row()->jcount; 

    }

    public function clear_property_tenants($property_id){

        if( $property_id > 0 ){

            $sql_str = "
            DELETE        
            FROM `property_tenants` 
            WHERE `property_id` = {$property_id}    
            ";
            $this->db->query($sql_str);            

        }         

    }

    public function bulk_add_property_spam_tenants_fix(){

        echo $sql_str = "
        SELECT 
            l.`log_id`, 
            l.`details`, 
            l.`created_by_staff`, 
            l.`created_date`,
            l.`property_id`,
            l.`agency_id`
        FROM `logs` AS l
        INNER JOIN `property` AS p ON l.`property_id` = p.`property_id`
        WHERE l.`details` = 'Added from PropertyMe Bulk Match'    
        AND l.`created_by_staff` = 2025
        AND l.`created_date` BETWEEN '2019-11-20' AND '2019-11-21'
        AND l.`agency_id` IN(1339,1492,3317,3926,4212,4318,4724,5718,6310,6394,6858)                 
        ";
        echo "<br /><br />";
        $sql = $this->db->query($sql_str);
        
        echo "<table>
        <tr>
            <th>#</th>
            <th>Property ID</th>
            <th>Agency ID</th>
            <th>Number of Jobs</th>
        </tr>
        ";
        foreach ($sql->result() as $index => $row)
        {

            $num_jobs = $this->spam_property_has_jobs_check($row->property_id);

            // if property has 0 jobs
            if( $num_jobs == 0 ){
                //$this->clear_property_tenants($row->property_id);
            }

            echo "
            <tr>
                <td>
                ".($index+1).".)
                </td>
                <td>
                    <a href='{$this->config->item('crm_link')}/view_property_details.php?id={$row->property_id}'>
                        {$row->property_id}
                    </a>
                </td>
                <td>               
                    <a href='/agency/view_agency_details/{$row->agency_id}'>
                        {$row->agency_id}
                    </a>
                </td>
                <td>".$num_jobs."</td>
            </tr>";
        }
        echo "</table>";

    }


    // NML property 
    public function nlm_property(){

        $this->load->model('properties_model');
        
        $property_id_arr = $this->input->get_post('property_id_arr');
        $reason_they_left = $this->input->post('reason_they_left');
        $other_reason = $this->input->post('other_reason');
        $pnv_id = $this->input->get_post('pnv_id');

        $nlm_success_prop_id_arr = [];
        $nlm_success_address_arr = [];
        $cannot_nlm_prop_id_arr = [];
        $cannot_nlm_address_arr = [];

        foreach( $property_id_arr as $property_id ){

            if( $property_id > 0 ){

                // get property address
                $prop_sql = $this->db->query("
                SELECT 
                `property_id`,
                    `address_1` AS p_street_num,
                    `address_2` AS p_street_name,
                    `address_3` AS p_suburb,
                    `state` AS p_state,
                    `postcode` AS p_postcode
                FROM `property`
                WHERE `property_id` = {$property_id}                    
                ");
                $prop_row = $prop_sql->row();

                $p_address = "{$prop_row->p_street_num} {$prop_row->p_street_name} {$prop_row->p_suburb}  {$prop_row->p_state}  {$prop_row->p_postcode}";                        

                // NLM property
                // $ret = $this->properties_model->nlm_property($property_id);
                $nlm_params = array(
                    'reason_they_left'=> $reason_they_left,
                    'other_reason'=> $other_reason
                );
                $ret = $this->properties_model->nlm_property($property_id, $nlm_params);
                
                if( $ret == false ){ // cannnot NLM property, has active jobs                     

                    $cannot_nlm_prop_id_arr[] =  $property_id;
                    $cannot_nlm_address_arr[] =  $p_address;
                    
                }else{ // success

                    // delete pnv
                    if( $pnv_id > 0 ){

                        $this->db->where('pnv_id', $pnv_id);
                        $this->db->delete('properties_needs_verification');

                    }  

                    $nlm_success_prop_id_arr[] =  $property_id;
                    $nlm_success_address_arr[] =  $p_address;
                    
                }

            }            

        }
        
        // PHP (server side)
        $ret_arr = array(
            "cannot_nlm_prop_id_arr" => $cannot_nlm_prop_id_arr,
            "cannot_nlm_address_arr" => $cannot_nlm_address_arr,
            "nlm_success_prop_id_arr" => $nlm_success_prop_id_arr,
            "nlm_success_address_arr" => $nlm_success_address_arr
        );
        echo json_encode($ret_arr);

    }


    // save note
    public function bulk_connect_save_note(){

        $pnv_id = $this->input->get_post('pnv_id');
        $property_source = $this->input->get_post('property_source');
        $property_id = $this->input->get_post('property_id');
        $property_address = $this->input->get_post('property_address');
        $agency_id = $this->input->get_post('agency_id');
        $note = $this->input->get_post('note');

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


    // get properties that needs agency verification from bulk connect page
    public function properties_needs_verification(){

        $this->load->model('properties_model');
        $this->load->model('palace_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Properties need verification";
        $uri = '/property_me/properties_needs_verification';
        $export = $this->input->get_post('export');
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $agency_filter = $this->input->get_post('agency_filter');
        $note_filter = $this->input->get_post('note_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        // exclude already NLM properties and do not show already linked properties
        //$custom_where = "( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL ) AND (  p.`propertyme_prop_id` = '' OR p.`propertyme_prop_id` IS NULL )";
        $custom_where_note = "pnv.note LIKE '%{$note_filter}%'";

        

        $tt_q = $this->db->query(
            "SELECT p.postpone_due_job, p.property_id
            FROM `properties_needs_verification` AS `pnv`
            LEFT JOIN `property` AS `p` ON pnv.`property_id` = p.`property_id` AND pnv.`property_source`=1
            INNER JOIN `agency` AS `a` ON CASE WHEN pnv.`property_source`= 1 THEN p.`agency_id` = a.`agency_id` WHEN ( pnv.`property_source`= 2 OR pnv.`property_source`= 3 OR pnv.`property_source`= 7) THEN pnv.`agency_id` = a.`agency_id` END
            LEFT JOIN `agency_priority` as `aght` ON `a`.`agency_id` = `aght`.`agency_id`
            LEFT JOIN `api_property_data` AS `apd` ON p.`property_id` = apd.`crm_prop_id`
            LEFT JOIN `property_services` AS `ps` ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )
            WHERE `pnv`.`active` = 1
            AND `pnv`.`ignore_issue` = 0
            AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL) AND (`apd`.`api_prop_id` = '' OR `apd`.`api_prop_id` IS NULL)"
        );
        $ttmo = array();
        foreach( $tt_q->result_array() as $tt_row ){
            $postpone_due_job_date = date('Y-m-d', strtotime($tt_row['postpone_due_job']));
            $date_now = date('Y-m-d');
            if ($postpone_due_job_date <= $date_now) {
                // do nothing
            } else {
                $ttmo[] = intval($tt_row['property_id']);
            }
        }

        if( !empty($ttmo) ){
            $ttmo_implode = implode(", ", $ttmo);
            $property_id_not_in = "AND p.property_id NOT IN(".$ttmo_implode.")";
        }

        $custom_where = "( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL ) AND (  apd.`api_prop_id` = '' OR apd.`api_prop_id` IS NULL ) {$property_id_not_in}";
        
        // paginated list               
        $sel_query = "
            pnv.`pnv_id`,
            pnv.`property_source`,
            pnv.`property_id`,         
            pnv.`agency_id`,
            pnv.`note`,
            pnv.`agency_verified`,
            pnv.`last_contact_info`,

            p.`address_1` AS p_address_1,
            p.`address_2` AS p_address_2,
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`is_nlm`,
            p.`propertyme_prop_id`,
            p.`is_sales`,

            ps.`property_services_id`,
            ps.`alarm_job_type_id`,
            ps.`service`,

            a.`agency_id`,
            a.`agency_name`,
            aght.priority,
            apmd.`abbreviation`,
            apd.`api_prop_id`
        ";
        $params = array(
            'sel_query' => $sel_query,                                                                
            'active' => 1,
            'agency_id' => $agency_filter,
            'ignore_issue' => 0,
            'pnv_stat'     => 1,

            'custom_where' => $custom_where, 
            'custom_where_arr' => array($custom_where_note),   
            'custom_joins' => array(
                'join_table' => '`property_services` AS ps',
                'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )',
                'join_type' => 'left'
            ), 

            /*
            'custom_joins' => array(
                'join_table' => '`api_property_data` AS apd',
                'join_on' => '( p.`property_id` = apd.`crm_prop_id` )',
                'join_type' => 'left'
            ), 
            */
            
            'limit' => $per_page,
            'offset' => $offset,
                        
            'display_query' => 0
        );

        //export start
        if($export==1){
            unset($params['limit'], $params['offset']); //remove limit/offset for export query

            $sql = $this->properties_model->get_properties_needs_verification($params);

            // file name
            $datestamp = date('d-m-y');
            $filename = "properties_needs_verification_{$datestamp}.csv";

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename={$filename}");
            header("Pragma: no-cache");

            // headers
            $str = "Source,Property,Agency,Note\n";
            foreach($sql->result() as $row) {

                //Source
                $source =  ( $row->property_source == 1 )?'CRM':'PMe';
               
                //address/property
                $p_address = '';

                if(  $row->property_source == 1 ){ // crm

                    $crm_full_address = "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$row->p_state} {$row->p_postcode}";

                    $p_address = "
                    <a href='{$this->config->item('crm_link')}/view_property_details.php?id={$row->property_id}' target='_blank'>
                        {$crm_full_address}
                    </a>";

                    $export_address = $crm_full_address;

                }else if(  $row->property_source == 2 ){	// PMe
                                    
                    $pme_params = array(
                        'agency_id' => $row->agency_id,
                        'prop_id' => $row->property_id
                    );
            
                    $pme_prop_json = $this->pme_model->get_property($pme_params);				
                    $pme_prop_json_dec = json_decode($pme_prop_json);		
                    $p_address = $pme_prop_json_dec->AddressText;

                    $export_address = $p_address;

                }

                //agency
                $agency_name = $row->agency_name;

                //note
                $note = $row->note;


                $str .= "{$source},\"{$export_address}\",\"{$agency_name}\",\"{$note}\"\n";
            }

            echo $str;
            //export end
            
        }else{ 

             //main list
            $data['list'] = $this->properties_model->get_properties_needs_verification($params);
            $data['last_query'] = $this->db->last_query();

            // total row
            $sel_query = "COUNT(pnv.`pnv_id`) AS pnv_count";
            $params = array(
                'sel_query' => $sel_query,                                                                
                'active' => 1,
                'agency_id' => $agency_filter,
                'ignore_issue' => 0,
                'pnv_stat'     => 1,

                'custom_where' => $custom_where,  
                'custom_joins' => array(
                    'join_table' => '`property_services` AS ps',
                    'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )',
                    'join_type' => 'left'
                ),              
                            
                'display_query' => 0
            );
            $tot_row_sql = $this->properties_model->get_properties_needs_verification($params);
            $total_rows = $tot_row_sql->row()->pnv_count;


            // update page total
            $page_tot_params = array(
                'page' => $uri,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);


            // distinct agency
            $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
            $params = array(
                'sel_query' => $sel_query,                                                                
                'active' => 1,   
                'ignore_issue' => 0,
                'pnv_stat'     => 1,
                
                'custom_where' => $custom_where,      
                'custom_joins' => array(
                    'join_table' => '`property_services` AS ps',
                    'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )',
                    'join_type' => 'left'
                ),         
                
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    )
                ),
                            
                'display_query' => 0
            );            
            $data['agency_filter'] =$this->properties_model->get_properties_needs_verification($params);

            $pagi_links_params_arr = array(
                'agency_filter' => $agency_filter,
            );
            $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);


            // pagination
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = $total_rows;
            $config['per_page'] = $per_page;
            $config['base_url'] = $pagi_link_params;
    
            $this->pagination->initialize($config);
    
            $data['pagination'] = $this->pagination->create_links();
    
            $pc_params = array(
                'total_rows' => $total_rows,
                'offset' => $offset,
                'per_page' => $per_page
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
            $data['uri'] = $uri;

            $this->load->view('templates/inner_header', $data);
            $this->load->view('/authentication/properties_needs_verification', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }
        

       
    }

    function pnv_ajax_snooze(){
        $property_id = $this->input->get_post('property_id');
        $pnv_id = $this->input->get_post('pnv_id');
        $snooze_reason = $this->input->get_post('snooze_reason');

        $snooze_days = 15;
		//update property
		$postpone_due_job_date = date('Y-m-d H:i:s', strtotime("+ {$snooze_days} days"));
		$this->db->where('property_id', $property_id);
		$this->db->set('postpone_due_job', $postpone_due_job_date);
		$this->db->update('property');
		//update property end

		//get property active jobs
		$active_job_query = $this->daily_model->get_properties_active_jobs_for_overdue_nsw_jobs($property_id);

		//insert job log
		$log_details = "Due date postponed for <strong>{$snooze_days}</strong> days because <strong>{$snooze_reason},</strong> affected jobs:({$active_job_query}).";
		$log_params = array(
			'title' => 68,  //Snooze day
			'details' => $log_details,
			'display_in_vpd' => 1,
			'created_by_staff' => $this->session->staff_id,
			'property_id' => $property_id
		);
		$this->system_model->insert_log($log_params);
    }

    // display properties that is already connected to PMe
    public function linked_properties(){

        $this->load->model('properties_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Linked Properties";
        $uri = '/property_me/linked_properties';        

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');
        $search_p_address = $this->input->get_post('search_p_address');

        // pagination
        //$per_page = $this->config->item('pagi_per_page');
        $per_page = 100;
        $offset = $this->input->get_post('offset');

        // exclude not linked and not active properties
        $custom_where = "
        (

            (

                apd_pme.`api_prop_id` != '' AND 
                apd_pme.`api` = 1

            ) OR (

                apd_palace.`api_prop_id` != '' AND 
                apd_palace.`api` = 4

            ) OR (

                apd_pt.`api_prop_id` != '' AND 
                apd_pt.`api` = 3

            ) OR p.`ourtradie_prop_id` != ''

        ) 
        AND  p.`deleted` = 0 
        AND  (

            p.`is_nlm` = 0 OR
            p.`is_nlm` IS NULL
            
        )
        ";
        
        // paginated list               
        $sel_query = "
            p.`property_id`,
            p.`address_1` AS p_address_1,
            p.`address_2` AS p_address_2,
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`is_nlm`,            
            p.`ourtradie_prop_id`,
            p.`deleted`,

            apd_pme.`api` AS pme_api,
            apd_pme.`api_prop_id` AS pme_prop_id,

            apd_palace.`api` AS palace_api,
            apd_palace.`api_prop_id` AS palace_prop_id,

            apd_pt.`api` AS pt_api,
            apd_pt.`api_prop_id` AS pt_prop_id,

            a.`agency_id`,
            a.`agency_name`            
        ";
        $params = array(
            'sel_query' => $sel_query,                                                                
            'active' => 1,
            'agency_filter' => $agency_filter,
            'ignore_issue' => 0,
            'search' => $search_p_address,

            'custom_where' => $custom_where,

            'join_table' => array('api_property_data_pme','api_property_data_palace','api_property_data_pt'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`address_2`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.`address_3`',
                    'sort' => 'ASC',
                )
            ),

            'limit' => $per_page,
            'offset' => $offset,
                        
            'display_query' => 0
        );
        $data['list'] = $this->properties_model->get_properties($params);
        $data['last_query'] = $this->db->last_query();

        // total row
        $sel_query = "COUNT(p.`property_id`) AS p_count";
        $params = array(
            'sel_query' => $sel_query,                                                                
            'active' => 1,
            'agency_filter' => $agency_filter,
            'ignore_issue' => 0,

            'custom_where' => $custom_where,

            'join_table' => array('api_property_data_pme','api_property_data_palace','api_property_data_pt'),
                        
            'display_query' => 0
        );
        $tot_row_sql = $this->properties_model->get_properties($params);
        $total_rows = $tot_row_sql->row()->p_count;



        // distinct agency
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,                                                                
            'active' => 1,   
            'ignore_issue' => 0,
            
            'custom_where' => $custom_where,

            'join_table' => array('api_property_data_pme','api_property_data_palace','api_property_data_pt'),
            
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                )
            ),
                        
            'display_query' => 0
        );            
        $data['agency_filter'] =$this->properties_model->get_properties($params);

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
        );
        $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);


         // pagination
         $config['page_query_string'] = TRUE;
         $config['query_string_segment'] = 'offset';
         $config['total_rows'] = $total_rows;
         $config['per_page'] = $per_page;
         $config['base_url'] = $pagi_link_params;
 
         $this->pagination->initialize($config);
 
         $data['pagination'] = $this->pagination->create_links();
 
         $pc_params = array(
             'total_rows' => $total_rows,
             'offset' => $offset,
             'per_page' => $per_page
         );
         $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
         $data['uri'] = $uri;

        $this->load->view('templates/inner_header', $data);
        $this->load->view('/authentication/linked_properties', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    // upload invoice to PME
    public function upload_invoice(){

        $this->load->model('api_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Upload Invoice";
        $uri = '/property_me/upload_invoice';        

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');

        $sel_query = "
            agen_api_tok.`agency_api_token_id`, 
            
            a.`agency_id`,
            a.`agency_name`
        ";
        $api_token_params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'api_id' => 1,
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
        $this->load->view('/authentication/upload_invoice', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_get_connected_prop(){

        $agency_id = $this->input->get_post('agency_id');
        $sel_query = "
            property_id,
            address_1,
            address_2,
            address_3,
            state,
            postcode,
            propertyme_prop_id
        ";
        $this->db->select($sel_query);
        $this->db->from('property');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('deleted', 0);
        $this->db->where("propertyme_prop_id IS NOT NULL");
        $lists = $this->db->get();
        $res = $lists->result_array();
        echo json_encode($res);
    }

    public function ajax_upload_file() {

        $agencyId = $this->input->get_post('agencyId');
        $selected = $this->input->get_post('selected');
        $jId = $this->input->get_post('jId');

        if ( 0 < $_FILES['file']['error'] ) {
            echo json_encode(array("err" => true, "agencyId" => $agencyId, "selected" => $selected, "file" => $_FILES['file']['name']));
        }
        else {
            $res = $this->ajax_upload_pdf_to_pme($agencyId, $selected, $_FILES['file']);
            if ($res['errNo'] === 0) { // Api response no error
                $successUpload = false;
                $this->add_api_logs($jId, $res['response'], true, "v1/lots/documents");
            }else {
                $successUpload = true;
                $this->add_api_logs($jId, $res['response'], false, "v1/lots/documents");
            }

            echo json_encode(array("err" => $successUpload, "agencyId" => $agencyId, "selected" => $selected, "file" => $_FILES['file']['name']));
        }
    }
    
    
    public function ajax_upload_pdf_to_pme($agencyId, $selected, $file) {

        

            $end_points = "https://app.propertyme.com/api/v1/lots/{$selected}/documents";
            
            // get access token
            $pme_params = array(
                'agency_id' => $agencyId,
                'api_id' => 1
            );
            $access_token = $this->pme_model->getAccessToken($pme_params);

            $fileName = $file['name'];
            $tmpName  = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileType = $file['type'];

            $fp      = fopen($tmpName, 'r');
            $content = fread($fp, filesize($tmpName));
            $fileData = base64_encode($content);
            $data = array("body" => $fileData);
            $data_string = json_encode($data);

            $params = array(
                'body'=>new CurlFile($tmpName,'application/pdf',$fileName)
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

            return array("errNo" => $errNo, "response" => $response);

    }
        

    public function send_all_certificates_and_invoices() {
        $this->load->model('Pme_model'); 
        $result = $this->Pme_model->send_all_certificates_and_invoices();
        echo $result;
    }

    public function send_all_certificates_and_invoices_via_vjd() {
        $this->load->model('Pme_model'); 

        $job_id = $this->input->get_post('job_id');
        $url = $this->input->get_post('url');
        $isUploaded = true;
        if ($job_id == "" || !is_numeric($job_id)) {
            $status = false;
            $msg = "Invalid Access";
        }else {
            $result = $this->Pme_model->send_all_certificates_and_invoices_via_vjd($job_id);
            $status = $result['status'];
            $msg = $result['msg'];
        } 
        $isUploaded = $isUploaded ? 1 : 0;
        $url = $this->config->item("crm_link") . '/view_job_details.php?id=' . $job_id;
        header("Location: ". $url . "&pme_upload_status=". $status . "&pme_msg=".$msg);
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

    public function add_billing_on_upload_invoice($agencyId, $pmePropId, $jobId, $countryId, $job_details, $property_details, $alarm_details, $num_alarms) {

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

            // $supplier_id = 'a8ef0002-f809-47e1-97c6-86346d264a59'; // jc supplier
            $supplier_folio_id = $chartAccDetails->FolioId;
            $char_accounts_id = $chartAccDetails->Contact->SupplierChartAccountId; // Fire protection
            $owner_folio_id = $lotDetails->Ownership->FolioId; // 1 manna road owner folio
            $owner_id = $lotDetails->ActiveOwnershipId; // 1 manna road owner 

            $end_points = "https://app.propertyme.com/api/v1/bills";
            $p_address = $job_details['address_1'] . " " . $job_details['address_2'] . " " . $job_details['address_3'] . " " . $job_details['state'] . " " . $job_details['postcode'];
            $detail = 'Smoke Alarm Testing';

            // get access token
            $pme_params = array(
                'agency_id' => $agencyId,
                'api_id' => 1 // PMe
            );
            $access_token = $this->pme_model->getAccessToken($pme_params);

            $check_digit = $this->gherxlib->getCheckDigit(trim($jobId));
            $invoice_number = "{$jobId}{$check_digit}"; 

            // updated due date value from job date + 30 days to job date only
            $dueDate = date('m/d/Y' , strtotime($job_details['jdate']));

            $invoice_pdf = $this->pdf_template->pdf_combined_template($jobId, $job_details, $property_details, $alarm_details, $num_alarms, $countryId);

            $fileName = 'invoice' . $invoice_number . '.pdf';

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

            if ($job_details['display_bpay'] && $countryId == 1) {
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
            $errNo = curl_errno($ch);
            $errStr = curl_error($ch);
            curl_close($ch);
            fclose($temp);

            // job log1
            $encrypt = rawurlencode($this->encryption_model->encrypt($jobId));
            $baseUrl = $_SERVER["SERVER_NAME"];
            if(isset($_SERVER['HTTPS'])){
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else{
                $protocol = 'http';
            }
            $log_details = "<a href='".$protocol."://{$baseUrl}/pdf/view_combined/?job_id={$encrypt}'>Combined Invoice/Cert</a>, #{$invoice_number} uploaded to PMe Agency as a bill of {$getTotalAmount}";
            $log_params = array(
                'title' => 52,  //PMe Combined Post
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

            return array("errNo" => $errNo, "response" => $responseCode);
        }else {
            return false;
        }

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

    // check if PMe property is already connected to a property on crm
    public function ajax_delete_pnv() {

        $pnv_id = $this->input->get_post('pnv_id');

        if( $pnv_id > 0 ){
        // foreach($pnv_id as $val){

            $this->db->where('pnv_id', $pnv_id);
            $this->db->delete('properties_needs_verification');
        // }
        }       

    }


    // ignore issue
    public function ajax_ignore_issue() {

        $pnv_id = $this->input->get_post('pnv_id');

        if( $pnv_id > 0 ){

            $data = array(
                'ignore_issue' => 1
            );
            
            $this->db->where('pnv_id', $pnv_id);
            $this->db->update('properties_needs_verification', $data);

        }       

    }


    // update note
    public function ajax_update_note() {

        $pnv_id = $this->input->get_post('pnv_id');
        $note = $this->input->get_post('note');

        if( $pnv_id > 0 && $note != '' ){

            $data = array(
                'note' => $note
            );
            
            $this->db->where('pnv_id', $pnv_id);
            $this->db->update('properties_needs_verification', $data);

        }       

    }

    // mark property as sales
    public function ajax_property_to_sales() {

        $property_id = $this->input->get_post('property_id');        
        $pnv_id = $this->input->get_post('pnv_id'); 

        if( $property_id > 0 && $pnv_id > 0 ){           

            // update property as sales
            $this->db->query("
            UPDATE `property`
            SET `is_sales` = 1
            WHERE `property_id` = {$property_id}
            ");

            // delete pnv
            if( $pnv_id > 0 ){
                
                $this->db->query("
                DELETE 
                FROM properties_needs_verification
                WHERE `pnv_id` = {$pnv_id}
                ");

            }  

            // Insert property log
            $log_title = 65; // Property Update
            $log_details = "This property was marked as a <b>sales</b> property on the <b>PNV</b> page.";
           	
            $log_params = array(
                'title' => $log_title, 
                'details' => $log_details,
                'display_in_vpd' => 1,
                'created_by_staff' => $this->session->staff_id,
                'property_id' => $property_id,
            );
            $this->system_model->insert_log($log_params);

        }       

    }

    public function get_pme_property(){

        $pme_prop_id = $this->input->get_post('pme_prop_id');
        $agency_id = $this->input->get_post('agency_id');

        /*
        echo $pme_prop_id;
        echo "<br />";
        echo $agency_id;
        exit();
        */

        $end_points = "https://app.propertyme.com/api/v1/lots/{$pme_prop_id}";

        $api_id = 1; // PMe

        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        echo $this->pme_model->call_end_points_v2($pme_params);

    }

    public function get_palace_property(){

        $this->load->model('palace_model');
        $palace_prop_id = $this->input->get_post('palace_prop_id');
        $agency_id = $this->input->get_post('agency_id');

        $palace_params = array(
            'agency_id' => $agency_id,
            'palace_prop_id' => $palace_prop_id
        );							

        echo $this->palace_model->get_property($palace_params);

    }


    // get tenants
    public function ajax_get_pme_tenants(){

        $agency_id = $this->input->get_post('agency_id');
        $tenants_contact_id = $this->input->get_post('tenants_contact_id');

        $pme_params = array(
            'agency_id' => $agency_id,
            'tenants_contact_id' => $tenants_contact_id
        );

        echo $this->pme_model->get_pme_tenant($pme_params);
        
    }


    // check if PMe property is already connected to a property on crm
    public function is_pme_prop_connected($pmeId) {

        $sel_query = "property_id";
        $this->db->select($sel_query);
        $this->db->from('property');
        $this->db->where('propertyme_prop_id', $pmeId);
        $query = $this->db->get();
        $res = $query->num_rows();
        if ($res > 0 ) {
            return true;
        }else {
            return false;
        }

    }


    /**
     * Check if PMe Id already exist in the system to prevent it from displaying again
     * @param  string $pmeId The string to look for
     * @param  int $angecyId The int to know what agency to look for
     * @return PMe property or false
    */ 
    public function check_if_pmeId_exist($pmeId, $angecyId) {
        $sel_query = "property_id";
        $this->db->select($sel_query);
        $this->db->from('property');
        $this->db->where('propertyme_prop_id', $pmeId);
        $this->db->where('agency_id', $angecyId);
        $query = $this->db->get();
        $res = $query->num_rows();
        if ($res > 0 ) {
            return true;
        }else {
            return false;
        }
    }

    /**
     * Get all CRM property list by agency id 
     * @return property list
    */ 
    public function get_crm_prop_list() {
        $agency_filter = $this->input->get_post('agency_filter');
        $pme_prop = $this->input->get_post('pme_prop');
        // $pme_prop = array_reverse($pme_prop);

        $sel_query = "property_id,address_1,address_2,address_3,state,postcode";
        $this->db->select($sel_query);
        $this->db->from('property');
        $this->db->where('agency_id', $agency_filter);
        $query = $this->db->get();
        $res = $query->result_array();
        $keyArr = array();

        foreach ($res as $key => $value) {
            $fullAdd = $value['address_1']." ".$value['address_2']." ".$value['address_3']." ".$value['state']." ".$value['postcode'];
            foreach ($pme_prop as $val) {
                if ($fullAdd == $val) {
                    array_push($keyArr, $key);
                }
            }
        }
        foreach ($keyArr as $val) {
            $z = $res[$val];
            // array_unshift($res , $z);
        }
        $data['lists'] = $res;
        $data['pme_prop'] = $pme_prop;
        echo  $this->load->view('authentication/bulk_crm_prop_list',$data,true);
    }

    /**
     * Load PMe connect page, this allow you to connect CRM properties to PMe properties
     * @param  int $contactId The int to find CRM property by ID
    */ 
    public function property($contactId = 0, $agency_id) {

        $this->load->model('api_model');

        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);
        
        
        $realPropId = $contactId;
        $data['realPropId'] = $realPropId;
        $data['title'] = 'PMe Tenant & Property Details';
        $accessTokenUrl = $this->input->get_post('apiUrl');
        //$access_token = $this->session->userdata('access_token');
        //$pmeId = $this->get_propertyme_prop_id($contactId);
        $pmeId = $this->get_pme_api_prop_id($contactId);

        //if pmeId above is empty. It will check to the api new table
        if(empty($pmeId)){
            $pmeId = $this->get_pme_api_prop_id($contactId);
        }

        if ($pmeId != '') { // if PME prop ID exist, means already connected
            $contactId = $pmeId;
            if (isset($access_token)) {
                if ($contactId !== 0) {

                    //$tenList = $this->get_tenant($contactId);
                    $tenList = $this->get_tenant_v2($agency_id,$contactId);
                    //$lotList = $this->get_lot($contactId);
                    $lotList = $this->get_lot_v2($agency_id,$contactId);

                    if ( $lotList->ResponseStatus->ErrorCode == "" ) { // no error

                        //$conList = $this->get_contact($tenList[0]->ContactId);
                        $conList = $this->get_contact_v2($agency_id,$tenList[0]->ContactId);
                        $data['tenList'] = json_encode($tenList);
                        $data['lotList'] = json_encode($lotList);
                        $data['conList'] = json_encode($conList);
                        $data['result'] = true;

                        /*
                        echo "<pre>";
                        print_r($lotList);
                        echo "</pre>";
                        echo "key number: {$lotList->KeyNumber}<br />";
                        echo "OwnerContactId: {$lotList->OwnerContactId}<br />";
                        echo "realPropId: {$realPropId}<br />";
                        */


                        $end_points = "https://app.propertyme.com/api/v1/contacts";
                        $api_id = 1; // PMe

                        // get access token
                        $pme_params = array(
                            'agency_id' => $agency_id,
                            'api_id' => $api_id
                        );
                        $access_token = $this->pme_model->getAccessToken($pme_params);
                        //echo "access_token: {$access_token}";

                        
                        // call end point
                        $pme_params = array(
                            'access_token' => $access_token,
                            'end_points' => $end_points
                        );
                        $contacts_json = $this->pme_model->call_end_points_v2($pme_params);
                        //print_r($contacts_json);

                        
                        
                        if( $realPropId > 0 && $lotList->KeyNumber != '' ){
                            // update key number
                            $key_update_data = array(
                                'key_number' => $lotList->KeyNumber
                            );                        
                            $this->db->where('property_id', $realPropId);
                            $this->db->update('property', $key_update_data);
                        }                                                

                        /*
                        $this->db->select('property_id');
                        $this->db->from('property');
                        $this->db->where('propertyme_prop_id', $contactId);
                        $query = $this->db->get();
                        $tmp_data = $query->result_array();
                        $propId =  $tmp_data[0]['property_id'];

                        if(empty($propId)){
                            $this->db->select('crm_prop_id');
                            $this->db->from('api_property_data');
                            $this->db->where('api_prop_id', $contactId);
                            $query = $this->db->get();
                            $tmp_data = $query->result_array();
                            $propId = $tmp_data[0]['crm_prop_id'];
                        }
                        */

                        $this->db->select('crm_prop_id');
                        $this->db->from('api_property_data');
                        $this->db->where('api_prop_id', $contactId);
                        $query = $this->db->get();
                        $tmp_data = $query->result_array();
                        $propId = $tmp_data[0]['crm_prop_id'];

                        if ($query->num_rows() > 0) {
                            $this->db->select('*');
                            $this->db->from('property_tenants');
                            $this->db->where('property_id', $propId);
                            $this->db->where('active', 1);
                            $query = $this->db->get();
                            $data['crmTenant'] = $query->result_array();
                            $data['propId'] = $propId;
                        }

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

                        if( $realPropId > 0 ){

                            $params = array(
                                'sel_query' => $sel_query,                                                                
                                'property_id' => $realPropId,
                                'join_table' => array('countries'),
                                'display_query' => 0
                            );
                            $crm_prop_sql = $this->properties_model->get_properties($params);
                            $data['crm_prop'] = $crm_prop_sql->row();

                        }                        

                        $this->load->view('templates/inner_header', $data);
                        $this->load->view('authentication/ajax_tenant_detail',$data); // already connected page
                        $this->load->view('templates/inner_footer', $data);
                    }

                }else {
                    //die('You do not have permission');
                    echo "contactId 1: {$contactId}";
                }
            }else {
                /*
                if ($contactId !== 0) {
                    $sess_arr = array(
                        'pme_property_id' => $realPropId
                    );
                    $this->session->set_userdata($sess_arr);
                    redirect('/property_me/index', 'refresh');
                }else {
                    die('You do not have permission');
                }
                */
                echo "access token doesn't exist";
            }
        }else { // not connected yet

            if (isset($access_token)) {

                //echo "access token: {$access_token}<br />";
                //echo "agency ID: {$agency_id}<br />";

                
                //$pmeList = $this->get_pme_lot_details();
                $pmeList = $this->get_pme_lot_details_v2($agency_id);
                if ($pmeList->ResponseStatus->ErrorCode == "TokenException") {
                    $sess_arr = array(
                        'pme_property_id' => $realPropId
                    );
                    $this->session->set_userdata($sess_arr);
                    redirect('/property_me/index', 'refresh');
                }else {

                    $this->db->select('p.property_id, p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, a.agency_id');
                    $this->db->from('`property` AS p');
                    $this->db->join('`agency` AS a', 'p.`agency_id` = a.`agency_id`', 'left');
                    $this->db->where('p.property_id', $realPropId);
                    $query = $this->db->get();
                    $propDet =  $query->row();

                    $data['pmeList'] = $pmeList;
                    $data['propDet'] = $propDet;
                    $this->load->view('templates/inner_header', $data);
                    $this->load->view('authentication/pme_connect',$data); // search and match Pme properties
                    $this->load->view('templates/inner_footer', $data);

                }
                

            }else {
                if ($contactId !== 0) {
                    $sess_arr = array(
                        'pme_property_id' => $realPropId
                    );
                    $this->session->set_userdata($sess_arr);
                    redirect('/property_me/index', 'refresh');
                }else {
                    //die('You do not have permission');
                    echo "contactId 2: {$contactId}";
                }
            }

        }
        

    }

    /**
     * This add PMe ID to the CRM properties that is bulk connected
    */ 
    public function bulk_connect_all() {

        $agency_filter = $this->input->get_post('agency_id');
        $crmArr = $this->input->get_post('crmArr');
        $pmeArr = $this->input->get_post('pmeArr');
        $connect_deleted_nlm_prop = $this->input->get_post('connect_deleted_nlm_prop');

        /*
        for ($i=0; $i < count($pmeArr); $i++) { 
            $updateData = array(
                'propertyme_prop_id' => $pmeArr[$i]
            );
            $this->db->where('property_id', $crmArr[$i]);
            $this->db->update('property', $updateData);
        }

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));
        */

        for ($i=0; $i < count($pmeArr); $i++) { 

            //$status = $this->properties_model->payableCheck($crmArr[$i]);
            //print_r($status);
            //exit();

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
               $log_details = "Property was restored from NLM by connecting on <b>PropertyMe</b> bulk match.";
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
                'title' => 69, // PMe API
                'details' => 'Property <b>Linked</b> to <b/>PMe</b> on Bulk Match',
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
                    'api'         => 1
                );
                $this->db->insert('api_property_data', $updateData_Api);
            }
            else{
                $updateData_Api = array(
                    'api_prop_id' => $pmeArr[$i]
                );
                $this->db->where('crm_prop_id', $crmArr[$i]);
                $this->db->where('api', 1);
                $this->db->update('api_property_data', $updateData_Api);
            }
        }

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));
    }


    public function ajax_pnv_connect_pme_prop() {

        $agency_id = $this->input->get_post('agency_id');
        $from_agency_name = $this->input->get_post('from_agency_name');
        $to_agency_name = $this->input->get_post('to_agency_name');
        $crm_prop_id = $this->input->get_post('crm_prop_id');
        $pme_prop_id = $this->input->get_post('pme_prop_id');
        $pnv_id = $this->input->get_post('pnv_id');

        if ( $pnv_id > 0 && $crm_prop_id > 0 && $pme_prop_id != '' && $agency_id > 0 ) { 

            // update PMe Property ID and Agency
            $updateData = array(
                'agency_id' => $agency_id
            );
            $this->db->where('property_id', $crm_prop_id);
            $this->db->update('property', $updateData);

            // update PMe Property ID in Generic API Table
            $updateApiData = array(
                'api_prop_id' => $pme_prop_id
            );
            $this->db->where('crm_prop_id', $crm_prop_id);
            $this->db->update('api_property_data', $updateApiData);

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

    public function ajax_pnv_connect_palace_prop() {

        $agency_id = $this->input->get_post('agency_id');
        $from_agency_name = $this->input->get_post('from_agency_name');
        $to_agency_name = $this->input->get_post('to_agency_name');
        $crm_prop_id = $this->input->get_post('crm_prop_id');
        $palace_prop_id = $this->input->get_post('palace_prop_id');
        $pnv_id = $this->input->get_post('pnv_id');

        if ( $pnv_id > 0 && $crm_prop_id > 0 && $palace_prop_id != '' && $agency_id > 0 ) { 
            
            // update Palace Agency ID
            $updateData = array(
                'agency_id' => $agency_id
            );
            $this->db->where('property_id', $crm_prop_id);
            $this->db->update('property', $updateData);

            // update PALACE Property ID in Generic API Table
            $updateApiData = array(
                'api_prop_id' => $palace_prop_id
            );
            $this->db->where('crm_prop_id', $crm_prop_id);
            $this->db->update('api_property_data', $updateApiData);

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

    /**
     * Get PMe ID associated with a CRM property
     * @param  int $propId The int to know what property to get
     * @return PMe ID
    */ 
    public function get_propertyme_prop_id($propId) {
        $this->db->select('propertyme_prop_id');
        $this->db->from('property');
        $this->db->where('property_id', $propId);
        $query = $this->db->get();
        $propId =  $query->row();
        return $propId->propertyme_prop_id;
    }

    /**
     * Get PMe ID associated with a API property data table
     * @param  int $propId The int to know what property to get
     * @return PMe ID
    */ 
    public function get_pme_api_prop_id($propId) {
        $this->db->select('api_prop_id');
        $this->db->from('api_property_data');
        $this->db->where('crm_prop_id', $propId);
        $query = $this->db->get();
        $propId =  $query->row();
        return $propId->api_prop_id;
    }

    /**
     * Get PMe property lists
     * @return list of properties
    */ 
    public function get_pme_lot_details(){
        $tenantUrl = "https://app.propertyme.com/api/v1/lots";
        $access_token = $this->session->userdata('access_token');

        $header = array("Authorization: Bearer {$access_token}",
                        "Content-Type: application/json");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $tenantUrl,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function get_pme_lot_details_v2($agency_id){

        $end_points = "https://app.propertyme.com/api/v1/lots";

        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);
        return json_decode($response);

    }

    /**
     * Get PMe Contact list of a PMe property
     * @param  int $contactId The int to know what property to look for
     * @return Contact list
    */ 
    public function get_contact($contactId){
        $tenantUrl = "https://app.propertyme.com/api/v1/contacts/".$contactId;
        $access_token = $this->session->userdata('access_token');

        $header = array("Authorization: Bearer {$access_token}",
                        "Content-Type: application/json");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $tenantUrl,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function get_contact_v2($agency_id,$contact_id){

        $end_points = "https://app.propertyme.com/api/v1/contacts/{$contact_id}";

        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);
        return json_decode($response);

    }

    /**
     * Get PMe Tenant list of a PMe property
     * @param  int $contactId The int to know what property to look for
     * @return Tenant list
    */ 
    public function get_tenant($contactId){
        $tenantUrl = "https://app.propertyme.com/api/v1/tenancies?LotId=".$contactId;
        $access_token = $this->session->userdata('access_token');

        $header = array("Authorization: Bearer {$access_token}",
                        "Content-Type: application/json");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $tenantUrl,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function get_tenant_v2($agency_id,$prop_id){

        $end_points = "https://app.propertyme.com/api/v1/tenancies?LotId={$prop_id}";

        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);
        return json_decode($response);

    }

    /**
     * Get PMe property list by PME id
     * @param  int $contactId The int to know what property to get
     * @return property list
    */ 
    public function get_lot($contactId){
        $tenantUrl = "https://app.propertyme.com/api/v1/lots/".$contactId;
        $access_token = $this->session->userdata('access_token');

        $header = array("Authorization: Bearer {$access_token}",
                        "Content-Type: application/json");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $tenantUrl,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function get_lot_v2($agency_id,$prop_id){

        $end_points = "https://app.propertyme.com/api/v1/lots/{$prop_id}";

        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);
        return json_decode($response);

    }

    /**
     * Check token is still alive
     * @return true or false
    */ 
    public function check_if_token_expired() {
        $tenantUrl = "https://app.propertyme.com/api/v1/lots";
        $access_token = $this->session->userdata('access_token');

        $header = array("Authorization: Bearer {$access_token}",
                        "Content-Type: application/json");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $tenantUrl,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    /**
     * Add new tenant to a CRM property
     * @return true or false
    */ 
    public function ajax_function_tenants() {
        $property_id = $this->input->get_post('property_id');
        $tenant_firstname = $this->input->get_post('tenant_firstname');
        $tenant_lastname = $this->input->get_post('tenant_lastname');
        $tenant_mobile = $this->input->get_post('tenant_mobile');
        $tenant_landline = $this->input->get_post('tenant_landline');
        $tenant_email = $this->input->get_post('tenant_email');
        $active = $this->input->get_post('active');

        $insertStat = false;
        $params = array(
                'tenant_firstname' => $tenant_firstname,
                'tenant_lastname' => $tenant_lastname,
                'property_id' => $property_id
            );
        $isExist = $this->check_tenant_if_exist($params);

        if ($isExist == false) {
            $data = array(
                'property_id' => $property_id,
                'tenant_firstname' => $tenant_firstname,
                'tenant_lastname' => $tenant_lastname,
                'tenant_mobile' => $tenant_mobile,
                'tenant_landline' => $tenant_landline,
                'tenant_email' => $tenant_email,
                'active' => $active
            );
            $this->db->insert('property_tenants', $data);
        }
        $insert_id = $this->db->insert_id();
        $insertStat = ($this->db->affected_rows() != 1) ? false : true;

        echo json_encode(array("isExist" => $isExist, "insertStat" => $insertStat, "insertId" => $insert_id));
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
     * Add a PMe ID to a CRM property
     * @return true or false
    */ 
    public function ajax_function_link_property() {
        $pmeId = $this->input->get_post('pmeId');
        $crmId = $this->input->get_post('crmId');

        $this->db->where('crm_prop_id', $crmId);
        $this->db->delete('api_property_data');

        $insertData = array(
            'api_prop_id' => $pmeId,
            'active' => 1,
            'crm_prop_id' => $crmId,
            'api' => 1
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
                'api_prop_id' => $pmeId,
                'active' => 1
            );
            $this->db->where('crm_prop_id', $crmId);
            $this->db->update('api_property_data', $updateData);
            $updateStat = true;
            echo json_encode(array("updateStat" => $updateStat));
        }
        else{
            $insertData = array(
                'api_prop_id' => $pmeId,
                'active' => 1,
                'crm_prop_id' => $crmId,
                'api' => 1
            );
            $this->db->insert('api_property_data', $insertData);
            $insertStat = true;
            echo json_encode(array("updateStat" => $insertStat));
            /*
            $updateData = array(
                'propertyme_prop_id' => $pmeId
            );
            $this->db->where('property_id', $crmId);
            $this->db->update('property', $updateData);
            $updateStat = true;
            echo json_encode(array("updateStat" => $updateStat));
            */
        //}

        // insert property log
        $params = array(
            'title' => 69, // PMe API
            'details' => 'Property <b>Linked</b> to <b/>PMe</b>',
            'display_in_vpd' => 1,            
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $crmId
        );
        $this->system_model->insert_log($params);
    }

    /**
     * Remove PMe ID associated with a CRM property
     * @return true or false
    */ 
    public function ajax_function_unlink_property() {
        $pmeId = $this->input->get_post('pmeId');
        $crmId = $this->input->get_post('crmId');

        /*
        //Setting api_prop_id => NULL

        $updateData = array(
            'propertyme_prop_id' => null
        );
        $this->db->where('property_id', $crmId);
        $this->db->update('property', $updateData);
        
        $updateApiData = array(
            'api_prop_id' => null
        );
        $this->db->where('crm_prop_id', $crmId);
        $this->db->update('api_property_data', $updateApiData);
        */

        $this->db->where('crm_prop_id', $crmId);
        $this->db->delete('api_property_data');

        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));

        // insert property log
        $params = array(
            'title' => 69, // PMe API
            'details' => 'Property <b>Unlinked</b> to <b/>PMe</b>',
            'display_in_vpd' => 1,            
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $crmId
        );
        $this->system_model->insert_log($params);
    }

    /**
     * Update a tenant in a CRM property
     * @return true or false
    */ 
    public function ajax_function_tenants_edit() {
        $tenant_id = $this->input->get_post('tenant_id');
        $tenant_firstname = $this->input->get_post('tenant_firstname');
        $tenant_lastname = $this->input->get_post('tenant_lastname');
        $tenant_mobile = $this->input->get_post('tenant_mobile');
        $tenant_landline = $this->input->get_post('tenant_landline');
        $tenant_email = $this->input->get_post('tenant_email');
        $active = $this->input->get_post('active');

        $updateData = array(
            'tenant_firstname' => $tenant_firstname,
            'tenant_lastname' => $tenant_lastname,
            'tenant_mobile' => $tenant_mobile,
            'tenant_landline' => $tenant_landline,
            'tenant_email' => $tenant_email
        );

        $this->db->where('property_tenant_id', $tenant_id);
        $this->db->update('property_tenants', $updateData);
        $updateStat = true;
        echo json_encode(array("updateStat" => $updateStat));
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

    public function routes(){

        $uri = $this->input->get_post('uri');
        
        $pme_login_url = $this->config->item('PME_AUTHORIZE_URL') . "?response_type=code&state=abc123&client_id=".$this->config->item('PME_CLIENT_ID')."&scope=".$this->config->item('PME_CLIENT_Scope')."&redirect_uri=".$this->config->item('PME_URL_CALLBACK');
        $this->session->set_userdata('pme_triggered_from', $uri);
        header("location:{$pme_login_url}");
    
    }


    public function get_all_propeties($agency_id,$display_by_list=0){


        if( $agency_id > 0 ){

            $end_points = "https://app.propertyme.com/api/v1/lots";

            $api_id = 1; // PMe

            // get access token
            //$agency_id = 1448;
            //$agency_id = 3446;
            $pme_params = array(
                'agency_id' => $agency_id,
                'api_id' => $api_id
            );
            $access_token = $this->pme_model->getAccessToken($pme_params);

            //echo "access token: {$access_token}";

            //echo "<br /><br /> -------------------------------------";

            $pme_params = array(
                'access_token' => $access_token,
                'end_points' => $end_points
            );
            
            $response =  $this->pme_model->call_end_points_v2($pme_params);
           

            if( $display_by_list == 1 ){

                $json_dec = json_decode($response);
                //echo $json_dec[0]->AddressText;

                $i = 1;
                foreach ($json_dec as $key => $row){

                    echo "{$i}.) {$row->AddressText}<br />";
                    $i++;
                    
                }

            }else{
                echo $response;
            }
            

            

        }        

    }

    public function test_get_all_propeties(){


        $end_points = "https://app.propertyme.com/api/v1/lots";

        /*
        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);
        */

        // expired
        //$access_token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYmYiOjE1NzM2MTIzMTAsImV4cCI6MTU3MzYxNTkxMCwiaXNzIjoiaHR0cHM6Ly9sb2dpbi5wcm9wZXJ0eW1lLmNvbSIsImF1ZCI6WyJodHRwczovL2xvZ2luLnByb3BlcnR5bWUuY29tL3Jlc291cmNlcyIsImh0dHBzOi8vYXBwLnByb3BlcnR5bWUuY29tL2FwaSJdLCJjbGllbnRfaWQiOiI1ZmYzMjZlMS0xOGYzLTRjOWUtOTA5Mi02MDdhZDExNmM4MWUiLCJzdWIiOiJDdXN0b21lcklkX2E4ZWYwMDAyLWY1YWEtNGNkZC1hNGM3LTdlNjUwYjExZDNhZiIsImF1dGhfdGltZSI6MTU2ODc2MzQyMSwiaWRwIjoibG9jYWwiLCJjdXN0b21lcl9pZCI6ImE4ZWYwMDAyLWY1YWEtNGNkZC1hNGM3LTdlNjUwYjExZDNhZiIsIm1lbWJlcl9pZCI6ImE4ZWYwMDAyLWY3YWYtNDgzZS05ZGJmLTU1N2U3ODMwZDFhZCIsIm1lbWJlcl9hY2Nlc3NfaWQiOiJhOGVmMDAwMi1mN2FmLTRkOGEtYTczMi1jZjk5MDI1NmMxOWUiLCJzY29wZSI6WyJwcm9wZXJ0eTpyZWFkIiwiY29tbXVuaWNhdGlvbjpyZWFkIiwiYWN0aXZpdHk6cmVhZCIsInRyYW5zYWN0aW9uOndyaXRlIiwiY29udGFjdDpyZWFkIiwib2ZmbGluZV9hY2Nlc3MiXSwiYW1yIjpbInB3ZCJdfQ.u8DlcXoJMLn3liZxwkOFYfNFAQ9bdYBNs9UX4t7q8r2TzceT9a7tUqZ0OZFiLHoUAnBz6apG_Px-9H6C5nLjIqXzHGysHIuhkQdIDL_gZ4QrTSqOhVgW0iKzusWHenk_cUawWl7MeOte_oH7dRECd_sPa8CsnQa_0ou2ZsPXkZVJ_MyBhj7IoaX_VSgi0as3s76ruu7bvc-39_jBSKMJ3bst19ExZtB_8fVrjemm9u4r0RiVAObPuxFyP6ehrIgN-0d25s30bzexNbn18AsqkGauAQlD0GYhDjWJYusTnkzb8IyRY5FjB3E9zibF4jqLEqYbx1XVxaexU5NywGvuQg';
        
        // active
        // SATS - test
        //$access_token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYmYiOjE1NzM2OTEwNDQsImV4cCI6MTU3MzY5NDY0NCwiaXNzIjoiaHR0cHM6Ly9sb2dpbi5wcm9wZXJ0eW1lLmNvbSIsImF1ZCI6WyJodHRwczovL2xvZ2luLnByb3BlcnR5bWUuY29tL3Jlc291cmNlcyIsImh0dHBzOi8vYXBwLnByb3BlcnR5bWUuY29tL2FwaSJdLCJjbGllbnRfaWQiOiI1ZmYzMjZlMS0xOGYzLTRjOWUtOTA5Mi02MDdhZDExNmM4MWUiLCJzdWIiOiJDdXN0b21lcklkX2E4ZWYwMDAyLWY1YWEtNGNkZC1hNGM3LTdlNjUwYjExZDNhZiIsImF1dGhfdGltZSI6MTU2ODc2MzQyMSwiaWRwIjoibG9jYWwiLCJjdXN0b21lcl9pZCI6ImE4ZWYwMDAyLWY1YWEtNGNkZC1hNGM3LTdlNjUwYjExZDNhZiIsIm1lbWJlcl9pZCI6ImE4ZWYwMDAyLWY3YWYtNDgzZS05ZGJmLTU1N2U3ODMwZDFhZCIsIm1lbWJlcl9hY2Nlc3NfaWQiOiJhOGVmMDAwMi1mN2FmLTRkOGEtYTczMi1jZjk5MDI1NmMxOWUiLCJzY29wZSI6WyJwcm9wZXJ0eTpyZWFkIiwiY29tbXVuaWNhdGlvbjpyZWFkIiwiYWN0aXZpdHk6cmVhZCIsInRyYW5zYWN0aW9uOndyaXRlIiwiY29udGFjdDpyZWFkIiwib2ZmbGluZV9hY2Nlc3MiXSwiYW1yIjpbInB3ZCJdfQ.skcdS0eFUoaoL2OjW1gkWDOyPuJ8mNg7Ovi-vudp0DOweGwk_Cls0ECy-B44ko8CnXZtXlzp20EMJxrTK1TLGJ2WKwMp1cxKWe40co_X0t7Fxw6jnv8Tu1mYVS48RcUqbUBFtwpzkd-6aGCTEvHUY91w6IUZCscqex0DQ0o8T-83eGyxYtUWbywPuaFPmBO_9rfuYakcreAJrx9Eq4wiVBZqfcU8qhOH4f_QmbzkYAiUb2NUw0dZq_BZAE30vNrO0AP6f519Yj58iwxpudhPFa4d_CGCHpMcMqs-PL1G6f2CCCAtpCrCkG-Dch9ao7l1etlT6DaFMMcrUqze7ohBmw';


        // 2773 - LJ Hooker Coomera/Ormeau
        $access_token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYmYiOjE1NzM2OTMwMTMsImV4cCI6MTU3MzY5NjYxMywiaXNzIjoiaHR0cHM6Ly9sb2dpbi5wcm9wZXJ0eW1lLmNvbSIsImF1ZCI6WyJodHRwczovL2xvZ2luLnByb3BlcnR5bWUuY29tL3Jlc291cmNlcyIsImh0dHBzOi8vYXBwLnByb3BlcnR5bWUuY29tL2FwaSJdLCJjbGllbnRfaWQiOiI1ZmYzMjZlMS0xOGYzLTRjOWUtOTA5Mi02MDdhZDExNmM4MWUiLCJzdWIiOiJDdXN0b21lcklkX2E3ODIwMDM0LTU1NDAtNDBkMi1hMGFjLWRjYzIyNDk3Njk4MiIsImF1dGhfdGltZSI6MTU3MDc1MzgwMywiaWRwIjoibG9jYWwiLCJjdXN0b21lcl9pZCI6ImE3ODIwMDM0LTU1NDAtNDBkMi1hMGFjLWRjYzIyNDk3Njk4MiIsIm1lbWJlcl9pZCI6ImE5ODIwMGRhLTVmMzUtNDJlNy04ODU0LTJiMTg0NGEwMDdkOSIsIm1lbWJlcl9hY2Nlc3NfaWQiOiJhOWVkMDE2Ny1mNTJkLTQxOTctOWVmNS0zNzFhZmZhNTk3NzQiLCJzY29wZSI6WyJwcm9wZXJ0eTpyZWFkIiwiY29tbXVuaWNhdGlvbjpyZWFkIiwiYWN0aXZpdHk6cmVhZCIsInRyYW5zYWN0aW9uOndyaXRlIiwiY29udGFjdDpyZWFkIiwib2ZmbGluZV9hY2Nlc3MiXSwiYW1yIjpbInB3ZCJdfQ.WMD1d0rRc_lj2UziVLz1Phd_0fTI8oLbBpJLIGka2sgOSjyb5_h-rnFeCiOILkwVYDtsDx2yHU2p5v47vrIUvVsoGijpWIRePE1qm-6Nf2PfuheoOvZ8JxZkV4ask_FnpXJi3fv0AYh1E9pURFcEXIbw49HGoXDlvga7HcdceeblL1SK6178o517gd9EAKKVAk-geQmqtYDQ_i3OX53y6zhBf12wwI5pjo-20no122hisM3YmrPkZMtUB17iT5WLD838n6kn_zo3Nnp1mLgp4iUXJmXyvkG47yPPDYf7Zgx1bUhN_XtyMCcBJtyDfcHazsxfJy_n4dyeyvXW01KUMg';

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);

        /*
        $response_decode = json_decode($response);
        echo "ResponseStatus ErrorCode: ".$response_decode->ResponseStatus->ErrorCode."<br />";
        echo "ResponseStatus Message: ".$response_decode->ResponseStatus->Message."<br />";
        */
       
        
        $pmeList = json_decode($response);

        $i = 1;
        foreach ($pmeList as $key => $row){

            echo "{$i}.) {$row->AddressText}<br />";
            $i++;
            
        }
        
       

    }


    public function get_tenant_info(){

        $tenants_contact_id = 'aa86013c-7631-4262-9e3b-bf61b5f5b99d';

        $end_points = "https://app.propertyme.com/api/v1/contacts/{$tenants_contact_id}";

        $api_id = 1; // PMe        

        // get access token
        $agency_id = 1448;
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);

        echo "<pre>";
        print_r($response);
        echo "</pre>";

    }


    public function get_propety($agency_id,$prop_id){

        //$prop_id = 'aa850012-31f2-4b1b-b3ef-83f97e9c76ad'; // 10 Wewak Rd, Holsworthy NSW 2173

        $end_points = "https://app.propertyme.com/api/v1/lots/{$prop_id}";

        $api_id = 1; // PMe


        // get access token
        //$agency_id = 1448;
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);

        //echo "<pre>";
        print_r($response);
        //echo "</pre>";

    }

    public function add_property_comments(){

        $prop_id = 'aa850012-31f2-4b1b-b3ef-83f97e9c76ad'; // 10 Wewak Rd, Holsworthy NSW 2173
        $comment = 'jc comments 555';

        $end_points = "https://app.propertyme.com/api/v1/lots/{$prop_id}/comments";

        $api_id = 1; // PMe

        // get access token
        $agency_id = 1448;
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $param_data = array(
			"Comment" => $comment
		);  
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points,
            'param_data' => $param_data
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);

        echo "<pre>";
        print_r($response);
        echo "</pre>";

    }


    public function create_bills(){

        $supplier_id = 'a8ef0002-f809-47e1-97c6-86346d264a59'; // jc supplier
        $supplier_folio_id = 'a8ef0002-f824-45ef-8973-7ec155381b6d';
        $char_accounts_id = 'a8ef0002-f7d8-471e-bc48-19162ed498ff'; // Fire protection
        

        $lotid = 'aa850012-31f2-4b1b-b3ef-83f97e9c76ad'; // 10 wewak
        $ownership_id = 'aa850016-1e55-41fc-8aa6-c5be6199aaf6';  // 10 wewak

        $ownder_id = 'aa850015-f497-4817-a238-7da0ef0557e2';      
        $owner_folio_id = 'aa850016-1e52-416a-a716-e558c39fd3bc'; // Mr Crab
        
        

        $end_points = "https://app.propertyme.com/api/v1/bills";

        //$detail = 'this bill is created from crm through PMe API. test insert link https://www.google.com';
        $detail = 'this is a test supplier by joe';

        $api_id = 1; // PMe

        // get access token
        $agency_id = 1448;
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $param_data = array(
            'DueDate' => '2019-09-04',
            'FromFolioId' => $supplier_folio_id,
            'ToFolioId' => $owner_folio_id,
            'OwnershipId' => $ownership_id,
            'Priority' => '1',
            'Detail' => $detail,
            'Amount' => '99.99',
            'SupplierReference' => $supplier_id,
            'ChartAccountId' => $char_accounts_id,
            'Reference' => 'joe-ref-1'
        );    

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points,
            'param_data' => $param_data
        );
        
        $response = $this->pme_model->call_end_points_v2($pme_params);

        echo "<pre>";
        print_r($response);
        echo "</pre>";
        

    }



    public function get_contacts(){

        $end_points = "https://app.propertyme.com/api/v1/contacts";

        $api_id = 1; // PMe


        // get access token
        $agency_id = 1448;
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);

        echo "<pre>";
        print_r($response);
        echo "</pre>";

    }



    public function get_bills(){

        $end_points = "https://app.propertyme.com/api/v1/dashboards/transactions/Bills";

        $api_id = 1; // PMe

        // get access token
        $agency_id = 1448;
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);

        echo "<pre>";
        print_r($response);
        echo "</pre>";

    }


    // Get supplier to PME
    public function supplier_pme(){

        $this->load->model('api_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "PMe Suppliers";
        $uri = '/property_me/supplier_pme';        

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');

        $sel_query = "
            agen_api_tok.`agency_api_token_id`, 
            agen_api_tok.`connection_date`,
            a.`agency_id`,
            a.`agency_name`,
            a.`pme_supplier_id`,
            a.`api_billable`
        ";
        $api_token_params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'api_id' => 1,
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
        $agencyQuery = $agencyQuery->result_array();
        foreach ($agencyQuery as $key => $value) {
            if ($value['pme_supplier_id'] == "" || is_null($value['pme_supplier_id'])) {
                $agencyQuery[$key]['SupplierChartAccountId'] = "0000";
            }else {
                $ret = $this->get_contact_v2($value['agency_id'], $value['pme_supplier_id']);
                $agencyQuery[$key]['SupplierChartAccountId'] = $ret->Contact->SupplierChartAccountId;
            }
        }
        
        $data['agenList'] = $agencyQuery;   

        $this->load->view('templates/inner_header', $data);
        $this->load->view('/authentication/pme_supplier', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    // GET PMe Suppliers per agency
    public function get_pme_supplier(){
        $agency_id = $this->input->get_post('agency_id');
        $num = array(1);
        $limit = 100;
        $this->suppArr = array();
        $result = $this->recursive_supplier($num, 0, $limit, $agency_id);
        foreach ($result as $key => $value) {
            $ret = $this->get_contact_v2($agency_id, $value->Id);
            $result[$key]->{"SupplierChartAccountId"} = $ret->Contact->SupplierChartAccountId;
        }
        
        $id = $this->get_pme_supplier_id_by_agency($agency_id);
        $contact = $this->get_contact_v2($agency_id, $id->pme_supplier_id);
        echo json_encode(array("supp"=>$id->pme_supplier_id, "pme"=> $result, "suppName" =>$contact->Contact->Reference));
    }

    public function recursive_supplier($num, $offset, $limit, $agency_id){

        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => 1
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        if(!empty($num)){

            $end_points = "https://app.propertyme.com/api/v1/contacts/suppliers?Offset={$offset}&Limit={$limit}";

            $pme_params = array(
                'access_token' => $access_token,
                'end_points' => $end_points
            );
            
            $response = $this->pme_model->call_end_points_v2($pme_params);
            $supplierArr = json_decode($response);
            foreach ($supplierArr as $val) {
                array_push($this->suppArr, $val);
            }
            return $this->recursive_supplier($supplierArr, $offset+100, 100, $agency_id);
        }

        return $this->suppArr;
    }

    public function get_pme_supplier_id_by_agency($agencyId) {
        $sql_str = "
        SELECT pme_supplier_id   
        FROM `agency`
        WHERE agency_id = {$agencyId}    
        ";
        $sql = $this->db->query($sql_str);
        return $sql->row(); 

    }

    public function update_supplier_id_by_agency() {

        $id = $this->input->get_post('id');
        $agencyId = $this->input->get_post('agencyId');

        $query = "UPDATE agency SET pme_supplier_id = '{$id}' WHERE agency_id = {$agencyId}";

        if($this->db->query($query)){
            echo true;
        }
        else {
            echo false;
        }
    }

    public function remove_supplier_id_by_agency() {

        $agencyId = $this->input->get_post('agencyId');

        if (isset($agencyId) && !empty($agencyId) ) {
            $query = "UPDATE agency SET pme_supplier_id = NULL WHERE agency_id = {$agencyId}";

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

    public function agency_connections() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency PropertyMe Connections";
        $uri = '/property_me/agency_connections';
        $data['uri'] = $uri;

        $agency_filter = $this->input->get_post('agency_filter');
        $date_from_filter = $this->input->get_post('date_from_filter');
        $date_to_filter = $this->input->get_post('date_to_filter');
        $query_filter = '';

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;        

        if ( $date_from_filter != '' && $date_to_filter != '' ) {

            $from = $this->system_model->formatDate($date_from_filter);
            $to = $this->system_model->formatDate($date_to_filter);

            $query_filter .= "AND CAST(agen_tok.`connection_date` AS DATE) BETWEEN '{$from}' AND '{$to}'";
        }

        if( $agency_filter != '' ){
            $query_filter .= "AND a.`agency_id` = {$agency_filter}";
        }

        // get only Pme integrated agency
        $agency_sql_str = "
            SELECT 
                a.`agency_id`,
                a.`agency_name`,
                
                agen_tok.`agency_api_token_id`,
                agen_tok.`connection_date`,
                agen_tok.`access_token`
            FROM `agency` AS a 
            LEFT JOIN `agency_api_integration` AS agen_api ON (a.`agency_id` = agen_api.`agency_id` AND agen_api.`connected_service` = 1 )
            LEFT JOIN `agency_api_tokens` AS agen_tok ON ( a.`agency_id` = agen_tok.`agency_id` AND agen_tok.`api_id` = 1 )
            WHERE a.`status` = 'active'    
            AND agen_api.`connected_service` = 1   
            AND agen_api.`active` = 1       
            {$query_filter}
            LIMIT {$offset}, {$per_page}
        ";
        $data['agency_sql'] = $this->db->query($agency_sql_str);
        $data['last_query'] = $this->db->last_query();

        $agency_sql_str_counter = "
            SELECT 
                a.`agency_id`,
                a.`agency_name`,
                
                agen_tok.`agency_api_token_id`,
                agen_tok.`connection_date`,
                agen_tok.`access_token`
            FROM `agency` AS a 
            LEFT JOIN `agency_api_integration` AS agen_api ON (a.`agency_id` = agen_api.`agency_id` AND agen_api.`connected_service` = 1 )
            LEFT JOIN `agency_api_tokens` AS agen_tok ON ( a.`agency_id` = agen_tok.`agency_id` AND agen_tok.`api_id` = 1 )
            WHERE a.`status` = 'active'    
            AND agen_api.`connected_service` = 1   
            AND agen_api.`active` = 1       
            {$query_filter}
        ";
        $agencyList = $this->db->query($agency_sql_str_counter);

        $ableToCon = 0;
        $needToCon = 0;
        $fullCon = 0;
        foreach($agencyList->result() as $agency_row){

            if ($agency_row->access_token == "" || is_null($agency_row->access_token)) {
                $ableToCon++;
            }else {
                if($this->system_model->isDateNotEmpty($agency_row->connection_date) == false){ 
                    $fullCon++;
                }else { 
                    $needToCon++;
                }
            }

        }

        $data['ableToCon'] = $ableToCon;
        $data['needToCon'] = $needToCon;
        $data['fullCon'] = $fullCon;

        // get total row
        $agency_sql_str = "
            SELECT COUNT(a.`agency_id`) AS a_count
            FROM `agency` AS a 
            LEFT JOIN `agency_api_integration` AS agen_api ON (a.`agency_id` = agen_api.`agency_id` AND agen_api.`connected_service` = 1 )
            LEFT JOIN `agency_api_tokens` AS agen_tok ON ( a.`agency_id` = agen_tok.`agency_id` AND agen_tok.`api_id` = 1 )
            WHERE a.`status` = 'active'                   
            AND agen_api.`connected_service` = 1
            AND agen_api.`active` = 1       
        ";
        $total_rows = $this->db->query($agency_sql_str)->row()->a_count;


        // get distinct agency
        $agency_sql_str = "
            SELECT DISTINCT(a.`agency_id`), a.`agency_name` 
            FROM `agency` AS a 
            LEFT JOIN `agency_api_integration` AS agen_api ON (a.`agency_id` = agen_api.`agency_id` AND agen_api.`connected_service` = 1 )
            LEFT JOIN `agency_api_tokens` AS agen_tok ON ( a.`agency_id` = agen_tok.`agency_id` AND agen_tok.`api_id` = 1 )
            WHERE a.`status` = 'active'     
            AND agen_api.`connected_service` = 1
            ORDER BY a.`agency_name` ASC                 
        ";
        $data['distinct_agency_sql'] = $this->db->query($agency_sql_str);

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'date_from_filter' => $date_from_filter,
            'date_to_filter' => $date_to_filter,
        );
        $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);


        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
        

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('pme/agency_pme_connections', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function export_agency_pme_connections(){

        // file name 
        $filename = 'Agency_Pme_Connections_'.date('Y-m-d').'.csv';

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $date_from_filter = $this->input->get_post('date_from_filter');
        $date_to_filter = $this->input->get_post('date_to_filter');
        $query_filter = '';

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;        

        if ( $date_from_filter != '' && $date_to_filter != '' ) {

            $from = $this->system_model->formatDate($date_from_filter);
            $to = $this->system_model->formatDate($date_to_filter);

            $query_filter .= "AND CAST(agen_tok.`connection_date` AS DATE) BETWEEN '{$from}' AND '{$to}'";
        }

        if( $agency_filter != '' ){
            $query_filter .= "AND a.`agency_id` = {$agency_filter}";
        }

        // get only Pme integrated agency
        $agency_sql_str = "
            SELECT 
                a.`agency_id`,
                a.`agency_name`,
                agen_tok.`agency_api_token_id`,
                agen_tok.`connection_date`,
                agen_tok.`access_token`
            FROM `agency` AS a 
            LEFT JOIN `agency_api_integration` AS agen_api ON (a.`agency_id` = agen_api.`agency_id` AND agen_api.`connected_service` = 1 )
            LEFT JOIN `agency_api_tokens` AS agen_tok ON ( a.`agency_id` = agen_tok.`agency_id` AND agen_tok.`api_id` = 1 )
            WHERE a.`status` = 'active'    
            AND agen_api.`connected_service` = 1
            AND agen_api.`active` = 1   
            {$query_filter}
        ";
        $list = $this->db->query($agency_sql_str);
       
        // file creation 
        $file = fopen('php://output', 'w');
    
        $header = array("Agency","Connection Date","Deliver Invoice via API");
        fputcsv($file, $header);

        foreach ($list->result() as $row){ 
            $data['agency'] = $row->agency_name;

            if ($row->access_token == "" || is_null($row->access_token)) {
                $data['connection_date'] = 'No';
            }else {
                $data['connection_date'] = $this->system_model->isDateNotEmpty($row->connection_date) == true ? $this->system_model->formatDate($row->connection_date, 'd/m/Y H:i') : 'Yes';
            }

            if($this->system_model->isDateNotEmpty($row->connection_date) == true){ 
                $data['deliver_invoice_via_api'] = "Yes";
            }else {
                $data['deliver_invoice_via_api'] = "No";
            }
            fputcsv($file,$data); 
        }

        fclose($file); 
        exit; 
    }

    public function ajax_pme_upload_toggle_cron_on_off(){

        $data['status'] = false;
        $cron_status = $this->input->post('cron_status');
        $db_field = $this->input->post('db_field');
        $country_id = $this->config->item('country');

        //update crm setting
        $db_data = array($db_field=>$cron_status);
        $this->db->where('country_id',$country_id);
        $this->db->update('crm_settings',$db_data);
        $this->db->limit(1);

        if($this->db->affected_rows()>0){
            $data['status'] = false;
        }

        echo json_encode($data);
        

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
                        'api'         => 1
                    );
                    $this->db->insert('api_property_data', $updateData_Api);
                    $this->db->reset_query();

                }else{

                    $updateData_Api = array(
                        'api_prop_id' => $pmeArr[$i]
                    );
                    $this->db->where('crm_prop_id', $property_id_arr[$i]);
                    $this->db->where('api', 1);
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


    public function save_pnv_last_contact_info(){

        $pnv_id = $this->input->post('pnv_id');
        $last_contact_info = $this->input->post('last_contact_info');
        $country_id = $this->config->item('country');

        if( $pnv_id > 0 ){

            // update
            $update_data = array(
                'last_contact_info' => $last_contact_info
            );            
            $this->db->where('pnv_id', $pnv_id);
            $this->db->update('properties_needs_verification', $update_data);

        }        

    }


    public function updated_tenants() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Updated Tenants";
        $uri = '/property_me/updated_tenants';
        $data['uri'] = $uri;

        $api_id = 1; // PMe
        
        $agency_filter = $this->input->get_post('agency_filter');
        $btn_search = $this->input->get_post('btn_search');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        // header filters
        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        // select query
        $sel_query = "
        SELECT      
            p.`property_id`,
            p.`address_1` AS p_address_1,
            p.`address_2` AS p_address_2,
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,

            apd_pme.`api_prop_id`,
            
            a.`agency_id`,
            a.`agency_name`,

            aht.`priority`,
            apmd.`abbreviation`,

            altu.`last_updated_ts`
        ";
        
        // main query body
        $main_query = "
        FROM `property` AS p
        INNER JOIN `api_property_data` AS apd_pme ON p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$api_id}
        LEFT JOIN `api_last_tenant_update` AS altu ON apd_pme.`api_prop_id` = altu.`api_prop_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        INNER JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id`
        LEFT JOIN `agency_priority` AS aht ON a.`agency_id` = aht.`agency_id`
        LEFT JOIN `agency_priority_marker_definition` AS apmd ON aht.`priority` = apmd.`priority`
        WHERE p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND (
            apd_pme.`api_prop_id` != '' AND
            apd_pme.`api_prop_id` IS NOT NULL
        )
        ";        

        if ($export == 1) { //EXPORT         
            
            /*
            // file name
            $date_export = date('YmdHis');
            $filename = "property_variation_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = array('Amount','Reason','Type','Property','Agency','BDM','Landlord');

            fputcsv($csv_file, $header);

            // main listing
            $main_list = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$type_filter_sql_str}
            {$apvr_filter_sql_str}
            ");
            
            foreach ( $main_list->result() as $row ){ 

                $csv_row = [];                              

                $csv_row[] = number_format($row->amount,2);
                $csv_row[] = $row->apvr_reason;
                $csv_row[] = ( $row->type == 1 )?'Discount':'Surcharge';                
                $csv_row[] = "{$row->address_1} {$row->address_2}, {$row->address_3}";
                $csv_row[] = $row->agency_name;
                $csv_row[] = $this->system_model->formatStaffName($row->sr_fname,$row->sr_lname);
                $csv_row[] = "{$row->landlord_firstname} {$row->landlord_lastname}";
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit; 
            */
            
        }else{   
            
            $this->load->model('pme_model');

            if( $btn_search ){

                 // get DISTINCT agency         
                $dist_agency_sql = $this->db->query("
                SELECT DISTINCT(a.`agency_id`)
                {$main_query}
                {$agency_filter_sql_str}
                ");
            
                $pme_agency_arr = [];
                $pme_prop_arr = [];
                foreach( $dist_agency_sql->result() as $dist_agency_row ){

                    // get api properties per agency       
                    $json_response = $this->pme_model->get_all_properties($dist_agency_row->agency_id);
                    $json_response_dec = json_decode($json_response);

                    foreach( $json_response_dec as $json_res_row ){                

                        // put pme properties in an array
                        $pme_prop_arr[] = $json_res_row;

                    }

                    // put in array of objects
                    $pme_agency_arr[] = (object) [
                        'agency_id' => $dist_agency_row->agency_id,
                        'pme_prop' => $pme_prop_arr
                    ];

                }     

                $data['pme_agency_arr'] = $pme_agency_arr;

                // main listing
                $data['lists'] = $this->db->query("
                {$sel_query}
                {$main_query}
                {$agency_filter_sql_str}    
                ORDER BY altu.`last_updated_ts` DESC  
                LIMIT {$offset}, {$per_page}                
                ");
                $data['sql_query'] = $this->db->last_query();

                // total rows            
                $total_rows_sql = $this->db->query("
                SELECT COUNT(p.`property_id`) AS p_count
                {$main_query}
                {$agency_filter_sql_str}
                ");
                $total_rows = $total_rows_sql->row()->p_count;                               

                // pagination
                $pagi_links_params_arr = array(            
                    'agency_filter' => $agency_filter,
                    'btn_search' => $btn_search
                );
                $pagi_link_params = "{$uri}/?".http_build_query($pagi_links_params_arr);
                $data['pagi_links_params_arr'] = $pagi_links_params_arr;

                // export link
                $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);

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

            }   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT 
                DISTINCT(a.`agency_id`), 
                a.`agency_name`, 
                aht.`priority`,
                apmd.`abbreviation`
            {$main_query}
            ORDER BY a.`agency_name` ASC            
            ");

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('api/updated_tenants', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }


    public function display_tenants(){

        $this->load->model('Pme_model');

        $property_id = $this->input->get_post('property_id');

        $api_id = 1; // PMe

        $col_count = 6;

        if( $property_id > 0 ){

            // get property data
            $prop_sql = $this->db->query("
            SELECT
                p.`property_id`,
                apd_pme.`api_prop_id`,

                a.`agency_id`
            FROM `property` AS p
            INNER JOIN `api_property_data` AS apd_pme ON p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$api_id}            
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE `property_id` = {$property_id}
            ");
            $prop_row = $prop_sql->row();

            // get property tenants
            $pt_sql = $this->db->query("
            SELECT 
                `property_tenant_id`,
                `tenant_firstname`,
                `tenant_lastname`,
                `tenant_mobile`,
                `tenant_landline`,
                `tenant_email`
            FROM `property_tenants` 
            WHERE `property_id` = {$property_id}
            AND `active` = 1
            ");

            $tr_str = '
            <h5 class="heading">CRM Tenants</h5>
            <table id="crm_tenants_table" class="table main-table">
                <thead>           
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Mobile</th>
                        <th>Landline</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="crm_tenants_table_body">';

                if( $pt_sql->num_rows() > 0 ){

                    foreach( $pt_sql->result() as $pt_row ){

                        $tr_str .= "            
                        <tr>
                            <td>
                                <span class='edit_tenant_display crm_tenant_fname_lbl'>{$pt_row->tenant_firstname}</span>
                                <input type='text' class='form-control crm_tenant_fname edit_tenant_hidden' value='{$pt_row->tenant_firstname}' />
                            </td>
                            <td>
                                <span class='edit_tenant_display crm_tenant_lname_lbl'>{$pt_row->tenant_lastname}</span>
                                <input type='text' class='form-control crm_tenant_lname edit_tenant_hidden' value='{$pt_row->tenant_lastname}' />
                            </td>
                            <td>
                                <span class='edit_tenant_display crm_tenant_mobile_lbl'>{$pt_row->tenant_mobile}</span>
                                <input type='text' class='form-control crm_tenant_mobile edit_tenant_hidden' value='{$pt_row->tenant_mobile}' />
                            </td>
                            <td>
                                <span class='edit_tenant_display crm_tenant_landline_lbl'>{$pt_row->tenant_landline}</span>
                                <input type='text' class='form-control crm_tenant_landline edit_tenant_hidden' value='{$pt_row->tenant_landline}' />
                            </td>
                            <td>
                                <span class='edit_tenant_display crm_tenant_email_lbl'>{$pt_row->tenant_email}</span>
                                <input type='text' class='form-control crm_tenant_email edit_tenant_hidden' value='{$pt_row->tenant_email}' />
                            </td>
                            <td class='text-center'>
                                <a href='javascript:void(0)'><span class='fa fa-trash text-danger mr-1 delete_crm_tenant'></span></a>
                                <a href='javascript:void(0)'><span class='fa fa-pencil text-warning edit_crm_tenant'></span></a>
                                <input type='hidden' class='pt_id' value='{$pt_row->property_tenant_id}' />
                            </td>
                        </tr>
                        ";

                    }

                }else{ // no tenants

                    $tr_str .= "<tr><td colspan='{$col_count}'>No CRM tenants</td></tr>"; 

                }

                $tr_str .= '
                <tbody>
            </table>
            ';

            // PME tenant section
            $tr_str .= '
            <h5 class="heading">PMe Tenants</h5>
            <table class="table main-table">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Mobile</th>
                        <th>Landline</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>';
                
                // get PME property detail
                $pme_params = array(
                    'agency_id' => $prop_row->agency_id,
                    'prop_id' => $prop_row->api_prop_id
                );        
                $pme_prop_json = $this->pme_model->get_property_details($pme_params);
                $pme_prop_json_dec = json_decode($pme_prop_json);	
                
                // get tenancy contact ID
                $tenants_contact_id = $pme_prop_json_dec->Tenancy->ContactId;	

                // get Pme tenants
                $pme_params = array(
                    'agency_id' => $prop_row->agency_id,
                    'tenants_contact_id' => $tenants_contact_id
                );
                $pme_tenant_json = $this->pme_model->get_pme_tenant($pme_params);
                $pme_tenant_decode = json_decode($pme_tenant_json);              

                if( count($pme_tenant_decode->Contact->ContactPersons) > 0 ){                   
                    
                    foreach( $pme_tenant_decode->Contact->ContactPersons as $pme_tenant ){

                        
                        $tr_str .= "            
                        <tr>
                            <td class='pme_tenant_fname'>{$pme_tenant->FirstName}</td>
                            <td class='pme_tenant_lname'>{$pme_tenant->LastName}</td>
                            <td class='pme_tenant_mobile'>{$pme_tenant->CellPhone}</td>
                            <td class='pme_tenant_landline'>{$pme_tenant->HomePhone}</td>
                            <td class='pme_tenant_email'>{$pme_tenant->Email}</td>
                            <td class='text-center'>
                                <a href='javascript:void(0)'><span class='fa fa-plus add_pme_tenant'></span></a>
                            </td>
                        </tr>
                        ";
                        
                    }

                }else{ // no tenants

                    $tr_str .= "<tr><td colspan='{$col_count}'>No PME tenants found</td></tr>"; 

                }     
                
                $tr_str .= '
                <tbody>
            </table>
            <input type="hidden" id="property_id" value="'.$property_id.'" />
            <input type="hidden" id="row_id" />
            <div class="text-right">
                <button type="button" class="btn" id="mark_as_checked">Mark as Checked</button>
            </div>            
            ';

            echo $tr_str;

        }        

    }


    public function delete_tenant(){

        $pt_id = $this->input->get_post('pt_id');
        $property_id = $this->input->get_post('property_id');

        if( $pt_id > 0 ){

            $this->db->where('property_id', $property_id);
            $this->db->where('property_tenant_id', $pt_id);
            $this->db->delete('property_tenants');

        }
        
    }

    public function update_tenant(){

        $pt_id = $this->input->get_post('pt_id');
        $property_id = $this->input->get_post('property_id');

        $fname = $this->input->get_post('fname');
        $lname = $this->input->get_post('lname');
        $mobile = $this->input->get_post('mobile');
        $landline = $this->input->get_post('landline');
        $email = $this->input->get_post('email');

        if( $pt_id > 0 && $property_id > 0 ){

            $update_data = array(
                'tenant_firstname' => $fname,
                'tenant_lastname' => $lname,
                'tenant_mobile' => $mobile,
                'tenant_landline' => $landline,
                'tenant_email' => $email
            );
            
            $this->db->where('property_id', $property_id);
            $this->db->where('property_tenant_id', $pt_id);
            $this->db->update('property_tenants', $update_data);

        }
        
    }


    public function mark_as_checked(){

        $property_id = $this->input->get_post('property_id');
        $date_full = date('Y-m-d H:i:s');

        if( $property_id > 0 ){

            // update modified date to mark is as checked
            $update_data = array(
                'modifiedDate' => $date_full
            );
            
            $this->db->where('property_id', $property_id);
            $this->db->update('property_tenants', $update_data);

        }
        
    }
    
    
}