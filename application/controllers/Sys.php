<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sys extends CI_Controller {

    public function __construct() {

        parent::__construct();
    }

    public function logout() {
        $this->system_model->logout();
    }

    public function refreshSession() {
        //session_start();
        //echo 'Refresh Session';
    }

    public function header_filters() {

        $rf_class = $this->input->get_post('rf_class');
        $header_filter_type = $this->input->get_post('header_filter_type');
        $json_data = $this->input->get_post('json_data');
        $searched_val = $this->input->get_post('searched_val');
        $dp = null;

        if ($rf_class == 'jobs') {
            $this->load->model('jobs_model');
            $header_filter = $this->jobs_model->get_jobs($json_data);
        } else if ($rf_class == 'property') {
            $this->load->model('properties_model');
            $header_filter = $this->properties_model->get_properties($json_data);
        } elseif ($rf_class == 'agency') {
            $this->load->model('agency_model');
            $header_filter = $this->agency_model->get_agency($json_data);
        }
        // elseif ($rf_class == 'agency_priority') {
        //     $this->load->model('agency_model');
        //     $header_filter = $this->agency_model->get_agency($json_data);
        // }


        // if only index row works on CI, i dont need this switch sad :(
        switch ($header_filter_type) {
            case 'agency':
                foreach ($header_filter->result() as $row) {
                    $abb = "";

                    if($row->priority > 0){
                        $abb = "($row->abbreviation)";
                    }

                    if (isset($row->agency_id) && $row->agency_id != '') {
                        
                        if ($this->input->get_post('isServiceDuePage') == 1) {
                            $auto_renew = ($row->auto_renew == 0) ? '(No Auto Renew)' : null;
                            $dp .= "<option value='{$row->agency_id}' " . ( ( $row->agency_id == $searched_val ) ? 'selected="selected"' : null ) . ">{$row->agency_name} {$abb} {$auto_renew}</option>";
                        } 
                        else {
                            $dp .= "<option value='{$row->agency_id}'" . ( ( $row->agency_id == $searched_val ) ? 'selected="selected"' : null ) . ">{$row->agency_name} {$abb}</option>";
                        }
                    }
                }
                break;
            case 'job_type':
                foreach ($header_filter->result() as $row) {
                    if (isset($row->job_type) && $row->job_type != '') {
                        $dp .= "<option value='{$row->job_type}' " . ( ( $row->job_type == $searched_val ) ? 'selected="selected"' : null ) . ">{$row->job_type}</option>";
                    }
                }
                break;
            case 'service':
                foreach ($header_filter->result() as $row) {
                    if (isset($row->id) && $row->id != '') {
                        $dp .= "<option value='{$row->id}' " . ( ( $row->id == $searched_val ) ? 'selected="selected"' : null ) . ">{$row->type}</option>";
                    }
                }
                break;
            case 'state':
                foreach ($header_filter->result() as $row) {
                    if (isset($row->state) && $row->state != '') {
                        $dp .= "<option value='{$row->state}' " . ( ( $row->state == $searched_val ) ? 'selected="selected"' : null ) . ">{$row->state}</option>";
                    }
                }
                break;
            case 'maint_prog':
                foreach ($header_filter->result() as $row) {
                    if (isset($row->maintenance_id) && $row->maintenance_id != '') {
                        $dp .= "<option value='{$row->maintenance_id}' " . ( ( $row->maintenance_id == $searched_val ) ? 'selected="selected"' : null ) . ">{$row->m_name}</option>";
                    }
                }
                break;
            case 'escalate_reason':
                foreach ($header_filter->result() as $row) {
                    if (isset($row->escalate_job_reasons_id) && $row->escalate_job_reasons_id != '') {
                        $dp .= "<option value='{$row->escalate_job_reasons_id}' " . ( ( $row->escalate_job_reasons_id == $searched_val ) ? 'selected="selected"' : null ) . ">{$row->reason}</option>";
                    }
                }
            case 'allocated_by':
                foreach ($header_filter->result() as $row) {
                    if (isset($row->allocated_by) && $row->allocated_by != '') {
                        $dp .= "<option value='{$row->allocated_by}' " . ( ( $row->allocated_by == $searched_val ) ? 'selected="selected"' : null ) . ">" . $this->system_model->formatStaffName($row->FirstName, $row->LastName) . "</option>";
                    }
                }
                break;
            // case 'agency_priority':
            //     foreach ($header_filter->result() as $row) {
            //         if (isset($row->escalate_job_reasons_id) && $row->escalate_job_reasons_id != '') {
            //             $dp .= "<option value='{$row->escalate_job_reasons_id}' " . ( ( $row->escalate_job_reasons_id == $searched_val ) ? 'selected="selected"' : null ) . ">{$row->reason}</option>";
            //         }
            //     }
            //     break;
        }

        echo $dp;
    }

    public function getRegionFilterState() {

        $rf_class = $this->input->get_post('rf_class');
        $region_filter_json = $this->input->get_post('region_filter_json');

        $params = array(
            'rf_class' => $rf_class,
            'region_filter_json' => $region_filter_json
        );
        echo $this->system_model->getRegionFilterStateListings($params);
    }

    public function getMainRegion() {

        $state = $this->input->get_post('state');
        $rf_class = $this->input->get_post('rf_class');
        $region_filter_json = $this->input->get_post('region_filter_json');

        $params = array(
            'state' => $state,
            'rf_class' => $rf_class,
            'region_filter_json' => $region_filter_json
        );
        echo $this->system_model->getMainRegionListings($params);
    }

    public function getSubRegion() {

        $region_id = $this->input->get_post('region_id');
        $rf_class = $this->input->get_post('rf_class');
        $region_filter_json = $this->input->get_post('region_filter_json');

        $params = array(
            'region_id' => $region_id,
            'rf_class' => $rf_class,
            'region_filter_json' => $region_filter_json
        );
        echo $this->system_model->getSubRegionListings($params);
    }

    public function search_results() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Search Results";

        $this->load->model('jobs_model');
        $this->load->model('properties_model');

        $search_type = $this->input->get_post('search_type');
        $search_val = $this->input->get_post('search_val');
        $country_id = $this->config->item('country');

        if ($search_type == 1) { //  Job ID
            // $job_id = $search_val;
            $job_id = str_replace(' ', '', $search_val);

            $sel_query = "
				j.`id` AS jid,
				j.`job_type`,
				j.`status` AS jstatus,

				p.`property_id`,
				p.`address_1` AS p_address_1,
				p.`address_2` AS p_address_2,
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`is_sales`,

				a.`agency_id`,
				a.`agency_name`
			";

            // paginated list
            $params = array(
                'sel_query' => $sel_query,
                'job_id' => $job_id,
                'is_nlm_include' => 0,
                'country_id' => $country_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`address_2`',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'p.`address_1`',
                        'sort' => 'ASC',
                    )
                ),
                'p_deleted' => 0,
                'display_query' => 0
            );
            $data['search_data'] = $this->jobs_model->get_jobs($params);
            $data['last_query'] = $this->db->last_query();
        } else if ($search_type == 2) { //  Property ID
            $property_id = $search_val;

            $sel_query = "
				j.`id` AS jid,
				j.`job_type`,
				j.`status` AS jstatus,
                j.invoice_amount,
                j.date AS jdate,
                j.job_type AS jtype,
                j.invoice_balance AS jbalance,
				p.`property_id`,
				p.`address_1` AS p_address_1,
				p.`address_2` AS p_address_2,
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`is_sales`,

				a.`agency_id`,
				a.`agency_name`
			";

            // paginated list
            $params = array(
                'sel_query' => $sel_query,
                'property_id' => $property_id,
                'is_nlm_include' => 0,
                'country_id' => $country_id,
                'custom_joins' => [
                    "join_table" => "jobs j",
                    "join_on" => 'p.`property_id` = j.`property_id`',
                    "join_type" => "left"
                ],
                'sort_list' => array(
                    array(
                        'order_by' => 'j.`id`',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'p.`address_2`',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'p.`address_1`',
                        'sort' => 'ASC',
                    )
                ),
                'display_query' => 0
            );
            $data['search_data'] = $this->properties_model->get_properties($params);
            $data['last_query'] = $this->db->last_query();
        } else if ($search_type == 3) { // Phone Number
            $phone_number = str_replace(' ', '', trim($search_val));

            $sel_query = "
					p.`property_id`,
					p.`address_1` AS p_address_1,
					p.`address_2` AS p_address_2,
					p.`address_3` AS p_address_3,
					p.`state` AS p_state,
                    p.`postcode` AS p_postcode,
                    p.`is_sales`
				";

            $custom_where = "(
					REPLACE(pt.`tenant_mobile`, ' ', '') LIKE '%{$phone_number}%' OR
					REPLACE(pt.`tenant_landline`, ' ', '') LIKE '%{$phone_number}%'
				)
				";

            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'display_query' => 0
            );
            $data['search_data'] = $this->properties_model->get_property_tenants($params);
            $data['last_query'] = $this->db->last_query();
        } else if ($search_type == 4 || $search_type == 5) { // Address OR landord
            if ($search_type == 4) { // Address
                $sel_query = "
							p.`property_id`,
							p.`address_1` AS p_address_1,
							p.`address_2` AS p_address_2,
							p.`address_3` AS p_address_3,
							p.`state` AS p_state,
                            p.`postcode` AS p_postcode,
                            p.`is_sales`
						";

                $address = strtolower(trim($search_val));
                $custom_where = "
							CONCAT_WS(
								' ',
								LOWER(p.`address_1`),
								LOWER(p.`address_2`),
								LOWER(p.`address_3`),
								LOWER(p.`state`),
								LOWER(p.`postcode`)
							)
							LIKE '%{$address}%'
						";
            } else if ($search_type == 5) { // Landlord
                $sel_query = "
							p.`property_id`,
							p.`address_1` AS p_address_1,
							p.`address_2` AS p_address_2,
							p.`address_3` AS p_address_3,
							p.`state` AS p_state,
							p.`postcode` AS p_postcode,
							p.`landlord_firstname`,
							p.`landlord_lastname`,
							p.`landlord_mob`,
							p.`landlord_ph`,
                            p.`landlord_email`,
                            p.`is_sales`
						";

                $landlord = strtolower(trim($search_val));
                $custom_where = "
							CONCAT_WS(
								' ',
								LOWER(p.`landlord_firstname`),
								LOWER(p.`landlord_lastname`)
							)
							LIKE '%{$landlord}%'
						";
            }

            // get property data
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'is_nlm_include' => 0,
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`address_2`',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'p.`address_1`',
                        'sort' => 'ASC',
                    )
                ),
                'display_query' => 0
            );
            $data['search_data'] = $this->properties_model->get_properties($params);
            $data['last_query'] = $this->db->last_query();
        } else if ($search_type == 6) { // Agency
            
            $agency_sql_str = "
            SELECT 
                `agency_id`,
                `agency_name`,
                `address_1`,
                `address_2`,
                `address_3`,
                `state`,
                `postcode`
            FROM `agency`
            WHERE CONCAT(
                LOWER(`agency_name`),
                LOWER(`address_1`),
                LOWER(`address_2`),
                LOWER(`address_3`),
                LOWER(`state`),
                LOWER(`postcode`)
            )
            LIKE '%{$search_val}%'
            AND deleted = 0
            ";
            $data['search_data'] = $this->db->query($agency_sql_str);
            $data['last_query'] = $this->db->last_query();
        } else if ($search_type == 7) { //  Building Name
            $building_name = $search_val;

            $sel_query = "
				p.`property_id`,
				p.`address_1` AS p_address_1,
				p.`address_2` AS p_address_2,
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`is_sales`,
                p.`agentname`,
                opd.`building_name`,
                p.`landlord_firstname` AS p_landlord_firstname,
                p.`landlord_email` AS p_landlord_email,
                p.`tenant_firstname1` AS p_tenant_firstname,
                p.created AS pdate,

				a.`agency_id`,
				a.`agency_name`
			";

            // paginated list
            $params = array(
                'sel_query' => $sel_query,
                'building_name' => $building_name,
                'is_nlm_include' => 0,
                'country_id' => $country_id,
                'search_type' => 7,
                
                'custom_joins' => [
                    "join_table" => "jobs j",
                    "join_on" => 'p.`property_id` = j.`property_id`',
                    "join_type" => "left"
                ],
                'custom_joins_bn' => [
                    "join_table" => "other_property_details opd",
                    "join_on" => 'p.`property_id` = opd.`property_id`',
                    "join_type" => "left"
                ],
                /*
                'custom_joins' => [
                    "join_table" => "jobs j",
                    "join_on" => 'p.`property_id` = j.`property_id`',
                    "join_type" => "left"
                ],
                */
                'sort_list' => array(
                    array(
                        'order_by' => 'j.`id`',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'p.`address_2`',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'p.`address_1`',
                        'sort' => 'ASC',
                    )
                ),
                'display_query' => 0
            );
            $data['search_data'] = $this->properties_model->get_properties($params);
            $data['last_query'] = $this->db->last_query();
            //echo $this->db->last_query();
            //exit();
        }

        $this->load->view('templates/inner_header', $data);
        $this->load->view('sys/search_results', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_get_notifications() {
        $data['notif_type'] = $this->input->get_post('notifType');
        $data['staff_id'] = $this->session->staff_id;
        echo $this->load->view('notifications/general_notif', $data, true);
    }

    public function ajax_update_sound_notification() {
        $staff_id = $this->session->staff_id;
        $updateData = array(
            'sound_notification' => 0
        );
        $this->db->where('StaffID', $staff_id);
        $this->db->update('staff_accounts', $updateData);
    }

    public function ajax_get_notif_count() {
        $jparams = array(
            'notf_type' => $this->input->get_post('notifType'),
            'return_count' => 1,
            'notify_to' => $this->session->staff_id,
            'read' => 0
        );
        $notf_count = $this->system_model->getOverallNotification($jparams);
        echo $notf_count;
    }

    public function ajax_message_mark_as_read() {
        $notifIds = $this->input->get_post('notifIds');
        $updateRes = $this->system_model->updateNotifiationRead($notifIds);
        echo $updateRes;
    }

    public function check_agency_session() {
        $isExist = true;
        $sess_id = $this->session->userdata('staff_id');

        if (empty($sess_id) || !$sess_id) {
            $isExist = false;
        }
        echo json_encode($isExist);
    }

    public function link_user_to_ci() {

        // staff accounts data
        $staff_id = $this->input->get_post('staff_id');
        $password = trim(rawurldecode($this->input->get_post('password')));

        // hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // update staff accounts password
        if ($staff_id > 0) {

            $data = [];
            $data = array(
                'password_new' => $password_hash
            );

            $this->db->where('StaffID', $staff_id);
            $this->db->update('staff_accounts', $data);

            //echo "success";
            // old crm users page url
            $url = "/users?link_success=1";
            redirect($url);
        }
    }

    /*
    public function split_table_per_year(){

        // GET jobs by year
        echo $sql_str = "
        SELECT DATE_FORMAT(`date`,'%Y') AS j_year
        FROM `jobs`
        WHERE `status` = 'Completed'
        AND YEAR(`date`) != '0000'
        AND YEAR(`date`) != ''
        GROUP BY YEAR(`date`) ASC;
        ";
        echo "<br /><br />";

        $this->db->trans_start();
        $sql = $this->db->query($sql_str);
        $this->db->trans_complete();

        // split jobs per year
        foreach( $sql->result() as $row ){

            echo "{$row->j_year}: <br /><br />";

            if( $row->j_year == '2019' || $row->j_year == '2020' ){ // skip these years

                echo "Year {$row->j_year} is skipped...<br /><br />";

            }else{ // process this years

                // create new table
                echo $create_table_query = "CREATE TABLE IF NOT EXISTS jobs_{$row->j_year} LIKE `jobs`";
                echo "<br /><br />";

                // copy rows to new table
                echo $copy_rows_query = "
                INSERT jobs_{$row->j_year}
                SELECT *
                FROM `jobs`
                WHERE `status` = 'Completed'
                AND YEAR(`date`) = '{$row->j_year}'
                ";
                echo "<br /><br />";

                // delete copied rows
                echo $delete_rows_query = "
                DELETE
                FROM `jobs`
                WHERE `status` = 'Completed'
                AND YEAR(`date`) = '{$row->j_year}'
                ";
                echo "<br /><br />";

                $this->db->trans_start();
                $this->db->query($create_table_query);
                $this->db->query($copy_rows_query);
                $this->db->query($delete_rows_query);
                $this->db->trans_complete();

            }

        }

    }
    */

    /*
    // rejoin all based on year array
    public function rejoin_table_per_year(){

        // jobs table; UPDATE THIS ON LIVE
        $jobs_year_table_arr = array(
            'jobs_2014',
            'jobs_2015',
            'jobs_2016',
            'jobs_2017',
            'jobs_2018'
        );

        // split jobs per year
        foreach( $jobs_year_table_arr as $jobs_year_table ){

            // move to other table
            echo $copy_rows_query = "
            INSERT `jobs`
            SELECT *
            FROM {$jobs_year_table}
            ";
            echo "<br /><br />";

            // delete copied rows
            echo $delete_rows_query = "
            DELETE
            FROM {$jobs_year_table}
            ";
            echo "<br /><br />";


            $this->db->trans_start();
            $this->db->query($copy_rows_query);
            $this->db->query($delete_rows_query);
            $this->db->trans_complete();


        }

    }
    */

    // rejoin one by one
    public function rejoin_table_one_by_one($table_name){

        if( $table_name != '' && strpos($table_name, 'jobs_') !== false ){

            // move to other table
            echo $copy_rows_query = "
            INSERT `jobs`
            SELECT *
            FROM `{$table_name}`
            ";
            echo "<br /><br />";

            // delete copied rows
            echo $delete_rows_query = "
            DELETE
            FROM `{$table_name}`
            ";
            echo "<br /><br />";


            $this->db->trans_start();
            $this->db->query($copy_rows_query);
            $this->db->query($delete_rows_query);
            $this->db->trans_complete();



        }else{
            echo "table name is not allowed";
        }

    }


    // tenant update
    public function ajax_update_property_coordinates(){
        
        $property_id = $this->db->escape_str($this->input->get_post('property_id'));   
        $db_table_field = $this->db->escape_str($this->input->get_post('coordinate'));    
        $db_table_value = $this->db->escape_str($this->input->get_post('coord_val'));  
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
        
        // allowed field for update
        $allowed_field = array('lat','lng');
           
        // update property
        if( $property_id > 0 && in_array($db_table_field, $allowed_field) && $db_table_value != '' ){
            
            $update_query_str = "
            UPDATE `property` 
            SET `{$db_table_field}` = '{$db_table_value}'
            WHERE `property_id` = {$property_id}
            ";	
            $this->db->query($update_query_str);

        }        

    }

}
