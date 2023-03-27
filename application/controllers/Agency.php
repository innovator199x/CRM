<?php

class Agency extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('agency_model');
        $this->load->model('staff_accounts_model');
        $this->load->model('inc/email_functions_model');
        $this->load->model('system_model');
        $this->load->library('pagination');
    }

    public function salesrep_update() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Salesrep Update";

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $salesrep_filter = $this->input->get_post('salesrep_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        // paginatied results
        $sel_query = "
            a.`agency_id`,
            a.`agency_name`,
            a.`status`,

            sa.`StaffID`,
			sa.`FirstName`, 
			sa.`LastName`		
        ";

        $params = array(
            'sel_query' => $sel_query,
            'limit' => $per_page,
            'offset' => $offset,
            'salesrep' => $salesrep_filter,
            'join_table' => array('salesrep'),
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'country_id' => $country_id,
            'display_query' => 0
        );
        $data['lists'] = $this->agency_model->get_agency($params);

        // total rows
        $sel_query = "COUNT(a.`agency_id`) as a_count";
        $params = array(
            'sel_query' => $sel_query,
            'salesrep' => $salesrep_filter,
            'join_table' => array('salesrep'),
            'country_id' => $country_id,
            'display_query' => 0
        );
        $query = $this->agency_model->get_agency($params);
        $total_rows = $query->row()->a_count;


        // Salesrep filter
        $sel_query = "DISTINCT(sa.`StaffID`), sa.`FirstName`, sa.`LastName`";
        $custom_where = 'sa.`StaffID` IS NOT NULL';
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'join_table' => array('salesrep'),
            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC'
                ),
                array(
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC'
                )
            ),
            'country_id' => $country_id,
            'display_query' => 0
        );
        $data['salesrep_filter_dp'] = $this->agency_model->get_agency($params);


        // get salesrep        
        $params = array(
            'sel_query' => '
				sa.`StaffID`,
                sa.`FirstName`, 
                sa.`LastName`,
                sa.`active`
            ',
            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC'
                ),
                array(
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC'
                )
            ),
            'deleted' => 0,
            'display_query' => 0
        );

        // get user details
        $data['staff_accounts_sql'] = $this->staff_accounts_model->get_staff_accounts($params);


        // pagination settings
        $pagi_links_params_arr = array(
            'salesrep_filter' => $salesrep_filter
        );
        $pagi_link_params = '/agency/salesrep_update/?' . http_build_query($pagi_links_params_arr);

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
        $this->load->view('agency/salesrep_update', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function salesrep_update_process($update_type) {

        $salesrep_from = $this->input->get_post('salesrep_from');
        $salesrep_to = $this->input->get_post('salesrep_to');

        if ($update_type == 'all') {

            if (isset($salesrep_from) && $salesrep_from > 0) {

                $data = array(
                    'salesrep' => $salesrep_to
                );

                $this->db->where('salesrep', $salesrep_from);
                //echo $this->db->set($data)->get_compiled_update('agency');
                $this->db->update('agency', $data);
            }
        } else if ($update_type == 'indiv') {

            $chk_agency = $this->input->get_post('chk_agency');

            if (!empty($chk_agency) && ( isset($salesrep_from) && $salesrep_from > 0 && isset($salesrep_to) && $salesrep_to > 0 )) {   // if selected agency
                $data = array(
                    'salesrep' => $salesrep_to
                );

                $this->db->where('salesrep', $salesrep_from);
                $this->db->where_in('agency_id', $chk_agency);
                //echo $this->db->set($data)->get_compiled_update('agency');
                $this->db->update('agency', $data);
            }
        }

        $this->session->set_flashdata('salesrep_update_success', 1);
        redirect('/agency/salesrep_update');
    }

    public function view_target_agencies() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "View Target Agencies";
        $country_id = $this->config->item('country');

        $submitPost = $this->input->get_post('btn_search');

        $state_filter = $this->input->get_post('state_filter');
        $sales_rep_filter = $this->input->get_post('sales_rep_filter');
        $search_filter = $this->input->get_post('search_filter');
        $using_filter = $this->input->get_post('using_filter');
        $agency_filter = $this->input->get_post('agency_filter');


        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if (!empty($sub_region_ms)) {
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }


        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        if ($offset === null) {
            $offset = 0;
        }

        $sel_query = "
        a.agency_name as a_name, 
        a.address_1, 
        a.address_2, 
        a.address_3, 
        a.state, 
        a.postcode, 
        a.status, 
        a.agency_id as a_id, 
        a.tot_properties,
        a.phone,
        a.account_emails,
        a.agency_emails,
        a.contact_first_name,
        a.contact_last_name,
        a.contact_phone,
        a.contact_email,
        a.tot_properties,
        sr.subregion_name as postcode_region_name,
        sa.FirstName,
        sa.LastName,
        fg.name as fg_name,
        c.country,
        au.name as au_name,
        aght.priority
        ";
        $params = array(
            'sel_query' => $sel_query,
            //'join_table' => array('postcode_regions'),
            'join_table' => array('postcode_regions', 'salesrep', 'franchise_groups', 'country', 'agency_using', 'agency_priority'),
            'a_status' => "target",
            'country_id' => $country_id,
            'state' => $state_filter,
            'salesrep' => $sales_rep_filter,
            'agency_name' => $search_filter,
            'agency_using_id' => $using_filter,
            'postcodes' => $postcodes,
            'agency_id' => $agency_filter,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        if($this->input->get_post('export')==1){ //remove limit on export
            unset($params['limit']);
            unset($params['offset']);
        }
        $list_query = $this->agency_model->get_agency($params)->result_array();
        $data['sql_query'] = $this->db->last_query(); //Show query on About

        $agencyByID = []; // make an object/map so you can access them by id easier later
        
        for ($x = 0; $x < count($list_query); $x++) {
            $agency = &$list_query[$x];

            $agency['last_contact'] = null;
            $agencyByID[$agency['a_id']] = &$agency; // take note with the &. reference the id to the object
        }

        $agencyIDs = array_keys($agencyByID); //ge agency ids

        if(!empty($agencyIDs)){ 
            ##get last contact
            $last_contact = $this->db->select('log_id,agency_id, MAX(created_date) as last_contact')
            ->from('logs')
            ->where_in('agency_id', $agencyIDs)
            ->where('display_in_vad', 1)
            ->group_by('agency_id')
            ->get()->result_array();
        }
      
        foreach ($last_contact as $d) {
            $agencyByID[$d['agency_id']]['last_contact'] = $d['last_contact'];
        }   
        
        if($this->input->get_post('export')==1){ ## EXPORT
              
            // file name 
           $filename = 'Target_Agencies_' . date('Y-m-d') . '.csv';

           header("Content-type: application/csv");
           header("Content-Disposition: attachment; filename={$filename}");
           header("Pragma: no-cache");
           header("Expires: 0");

           $country_id = $this->config->item('country');
           $submitPost = $this->input->post('btn_search');

           $state_filter = $this->input->get_post('state_filter');
           $sales_rep_filter = $this->input->get_post('sales_rep_filter');
           $search_filter = $this->input->get_post('search_filter');
           $using_filter = $this->input->get_post('using_filter');


           $state_ms = $this->input->get_post('state_ms');
           // $data['state_ms_json'] = json_encode($state_ms);
           $region_ms = $this->input->get_post('region_ms');
           //$data['region_ms_json'] = json_encode($region_ms);
           $sub_region_ms = $this->input->get_post('sub_region_ms');
           //$data['sub_region_ms_json'] = json_encode($sub_region_ms);

           if (!empty($sub_region_ms)) {
               $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
           }

           /*$params = array(
               'sel_query' => $sel_query,
               'join_table' => array('postcode_regions', 'salesrep', 'franchise_groups', 'country', 'agency_using'),
               'a_status' => "target",
               'country_id' => $country_id,
               'state' => $state_filter,
               'salesrep' => $sales_rep_filter,
               'agency_name' => $search_filter,
               'agency_using_id' => $using_filter,
               'postcodes' => $postcodes,
               'limit' => $per_page,
               'offset' => $offset,
               'sort_list' => array(
                   array(
                       'order_by' => 'a.`agency_name`',
                       'sort' => 'ASC'
                   )
               )
           );*/
           $lists = $list_query;

           //GET SERVICE HEADER
           $serv_type_str = array();
           $ajt_sql = $this->db->select('id,type')->where('active', 1)->get('alarm_job_type');
           foreach ($ajt_sql->result_array() as $ajt) {
               //$serv_type_str .= '","'.$ajt['type'];
               $serv_type_str[] = $ajt['type'];
           }

           // file creation 
           $file = fopen('php://output', 'w');

           //header
           $state = $this->gherxlib->getDynamicState($this->config->item('country'));
           $region = $this->customlib->getDynamicRegionViaCountry($this->config->item('country'));
           $header = array("Agency Name", "Address", "Postcode", "{$state}", "{$region}", "Phone", "Accounts Email", "Agency Email", "Agency Contact", "Contact Phone", "Contact Email", "Sales Rep", "Properties", "Last Contact", "Franchise Group", "Country", "Agency Using");

           $merge_header = array_merge($header, $serv_type_str);

           fputcsv($file, $merge_header);

           foreach ($lists as $row) {

               //GET LAST CONTACT
               ##$lastContact_query = $this->agency_model->get_agency_last_contact($row['a_id'])->row_array(); //comment out possible reason for slow loading page
               ##$lc = ($this->system_model->isDateNotEmpty($lastContact_query['eventdate'])) ? $this->system_model->formatDate($lastContact_query['eventdate'], 'd/m/Y') : NULL;
               
               $lc = ($this->system_model->isDateNotEmpty($row['last_contact'])) ? $this->system_model->formatDate($row['last_contact'], 'd/m/Y') : NULL;

               //GET SERVICE COUNT
               $serviceCount = array();
               $serviceCountSql = $this->db->select('id')->where('active', 1)->get('alarm_job_type');
               foreach ($serviceCountSql->result_array() as $serviceCountSqlRow) {
                   $serviceCount[] = array('id' => $this->system_model->getServiceCount($row['a_id'], $serviceCountSqlRow['id']));
               }

               $export_data['agency_name'] = $row['a_name'];
               $export_data['address'] = "{$row['address_1']} {$row['address_2']}, {$row['address_3']}";
               $export_data['postcode'] = $row['postcode'];
               $export_data['state'] = $row['state'];
               $export_data['region'] = $row['postcode_region_name'];
               $export_data['phone'] = $row['phone'];
               $export_data['account_emails'] = $row['account_emails'];
               $export_data['agency_emails'] = $row['agency_emails'];
               $export_data['agency_contact'] = "{$row['contact_first_name']} {$row['contact_last_name']}";
               $export_data['c_phone'] = $row['contact_phone'];
               $export_data['c_email'] = $row['contact_email'];
               $export_data['salesrep'] = "{$row['FirstName']} {$row['LastName']}";
               $export_data['tot_prop'] = $row['tot_properties'];
               $export_data['lastContact'] = $lc;
               $export_data['fg_name'] = $row['fg_name'];
               $export_data['country'] = $row['country'];
               $export_data['au_name'] = $row['au_name'];

               $counter = 1;
               foreach ($serviceCount as $aws) {
                   $export_data[$counter] = $aws['id'];
                   $counter ++;
               }

               fputcsv($file, $export_data);
           }


           fclose($file);
           exit;

       }else{ ## NORMAL LISTING

        if ($submitPost) {
            $data['lists'] = $list_query;
            $data['last_contact'] = $last_contact;

            // all rows
            $total_sel_query = "COUNT(a.`agency_id`) as a_count";
            $total_params = array(
                'sel_query' => $total_sel_query,
                'join_table' => array('postcode_regions'),
                'a_status' => "target",
                'country_id' => $country_id,
                'state' => $state_filter,
                'salesrep' => $sales_rep_filter,
                'agency_name' => $search_filter,
                'agency_using_id' => $using_filter,
                'postcodes' => $postcodes,
                'agency_id' => $agency_filter,
            );
            $query = $this->agency_model->get_agency($total_params);
            $total_rows = $query->row()->a_count;
 
 
            //base url params
            $pagi_links_params_arr = array(
                'agency_filter' => $agency_filter,
                'state_filter' => $state_filter,
                'sales_rep_filter' => $sales_rep_filter,
                'sub_region_ms' => $sub_region_ms,
                'search_filter' => $search_filter,
                'using_filter' => $using_filter,
                'btn_search' => $submitPost
            );
            $pagi_link_params = '/agency/view_target_agencies/?' . http_build_query($pagi_links_params_arr);
 
            // pagination settings
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = (int) $total_rows;
            $config['per_page'] = (int) $per_page;
            $config['base_url'] = $pagi_link_params;
 
            $this->pagination->initialize($config);
 
            $data['pagination'] = $this->pagination->create_links();
 
            // pagination count
            $pc_params = array(
                'total_rows' => (int) $total_rows,
                'offset' => (int) $offset,
                'per_page' => (int) $per_page
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
        }
          
       }



        // state filter
        $sel_query = "DISTINCT(a.`state`),
        a.`state`";
        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('postcode_regions'),
            'a_status' => "target",
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`state`',
                    'sort' => 'ASC',
                )
            ),
        );
        $data['state_filter_json'] = json_encode($params);


        //salesrep filter
        $data['salesrep'] = $this->agency_model->getAgencySalesRep('target');


        // Region Filter ( get distinct state )
        $sel_query = "DISTINCT(a.`state`),a.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'join_table' => array('postcode_regions'),
            'a_status' => "target",
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`state`',
                    'sort' => 'ASC',
                )
            ),
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);


        //Agency name filter
        $sel_query_agency = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query_agency,
            'join_table' => array('postcode_regions'),
            'a_status' => "target",
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $data['agency_filter_list'] = $this->agency_model->get_agency($params);


        //Using Filter
        $data['agency_using'] = $this->agency_model->getAgencyUsing();


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/view_target_agencies', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function is_sales_target() {
        $agency_id = $this->input->get_post('agency_id');
        $mark_id = 2; // Sales only Show

        $query = $this->db->select('marker_id')->from('agency_markers')->where('agency_id', $agency_id)->where('marker_id', '2')->get();
        $data = $query->row_array();
        // $marker_id = $data['marker_id'];

        if ($query->num_rows()>0) { // means it has a record
            $this->db->where('agency_id', $agency_id);
            $this->db->where('marker_id', 2); ## Delete from agency_markers
            $this->db->delete('agency_markers'); 
        } else {
            $insert_data = array(
                'agency_id' => $agency_id,
                'marker_id' => $mark_id
            );                            
            $this->db->insert('agency_markers', $insert_data);
        }

    }

    public function export_target_agencies() { ## AL: not used anymore > move function to view_target_agencies()

        // file name 
        $filename = 'Target_Agencies_' . date('Y-m-d') . '.csv';

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $country_id = $this->config->item('country');
        $submitPost = $this->input->post('btn_search');

        $state_filter = $this->input->get_post('state_filter');
        $sales_rep_filter = $this->input->get_post('sales_rep_filter');
        $search_filter = $this->input->get_post('search_filter');
        $using_filter = $this->input->get_post('using_filter');


        $state_ms = $this->input->get_post('state_ms');
        // $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        //$data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        //$data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if (!empty($sub_region_ms)) {
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }


        //GET LIST HERE
        $sel_query = "
            a.agency_name as a_name, 
            a.address_1, 
            a.address_2, 
            a.address_3, 
            a.state, 
            a.postcode, 
            a.status, 
            a.agency_id as a_id, 
            a.tot_properties, 
            a.phone,
            a.account_emails,
            a.agency_emails,
            a.contact_first_name,
            a.contact_last_name,
            a.contact_phone,
            a.contact_email,
            a.tot_properties,
            sr.subregion_name as postcode_region_name,
            sa.FirstName,
            sa.LastName,
            fg.name as fg_name,
            c.country,
            au.name as au_name

        ";
        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('postcode_regions', 'salesrep', 'franchise_groups', 'country', 'agency_using'),
            'a_status' => "target",
            'country_id' => $country_id,
            'state' => $state_filter,
            'salesrep' => $sales_rep_filter,
            'agency_name' => $search_filter,
            'agency_using_id' => $using_filter,
            'postcodes' => $postcodes,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $lists = $this->agency_model->get_agency($params);



        //GET SERVICE HEADER
        $serv_type_str = array();
        $ajt_sql = $this->db->select('id,type')->where('active', 1)->get('alarm_job_type');
        foreach ($ajt_sql->result_array() as $ajt) {
            //$serv_type_str .= '","'.$ajt['type'];
            $serv_type_str[] = $ajt['type'];
        }




        // file creation 
        $file = fopen('php://output', 'w');

        //header
        $state = $this->gherxlib->getDynamicState($this->config->item('country'));
        $region = $this->customlib->getDynamicRegionViaCountry($this->config->item('country'));
        $header = array("Agency Name", "Address", "Postcode", "{$state}", "{$region}", "Phone", "Accounts Email", "Agency Email", "Agency Contact", "Contact Phone", "Contact Email", "Sales Rep", "Properties", "Last Contact", "Franchise Group", "Country", "Agency Using");

        $merge_header = array_merge($header, $serv_type_str);

        fputcsv($file, $merge_header);


        foreach ($lists->result_array() as $row) {

            //GET LAST CONTACT
            $lastContact_query = $this->agency_model->get_agency_last_contact($row['a_id'])->row_array();
            $lc = ($this->system_model->isDateNotEmpty($lastContact_query['eventdate'])) ? $this->system_model->formatDate($lastContact_query['eventdate'], 'd/m/Y') : NULL;

            //GET SERVICE COUNT
            $serviceCount = array();
            $serviceCountSql = $this->db->select('id')->where('active', 1)->get('alarm_job_type');
            foreach ($serviceCountSql->result_array() as $serviceCountSqlRow) {
                $serviceCount[] = array('id' => $this->system_model->getServiceCount($row['a_id'], $serviceCountSqlRow['id']));
            }

            $data['agency_name'] = $row['a_name'];
            $data['address'] = "{$row['address_1']} {$row['address_2']}, {$row['address_3']}";
            $data['postcode'] = $row['postcode'];
            $data['state'] = $row['state'];
            $data['region'] = $row['postcode_region_name'];
            $data['phone'] = $row['phone'];
            $data['account_emails'] = $row['account_emails'];
            $data['agency_emails'] = $row['agency_emails'];
            $data['agency_contact'] = "{$row['contact_first_name']} {$row['contact_last_name']}";
            $data['c_phone'] = $row['contact_phone'];
            $data['c_email'] = $row['contact_email'];
            $data['salesrep'] = "{$row['FirstName']} {$row['LastName']}";
            $data['tot_prop'] = $row['tot_properties'];
            $data['lastContact'] = $lc;
            $data['fg_name'] = $row['fg_name'];
            $data['country'] = $row['country'];
            $data['au_name'] = $row['au_name'];

            $counter = 1;
            foreach ($serviceCount as $aws) {
                $data[$counter] = $aws['id'];
                $counter ++;
            }

            fputcsv($file, $data);
        }


        fclose($file);
        exit;
    }

    //EXPORT trusted account By ID - Chops
    public function export_account() {
        $country_id = $this->config->item('country');

        $agency = strtolower($this->uri->segment(3));
        $tas_filter = $this->uri->segment(4);

        $list['trusted_accounts'] = $this->agency_model->getTrustedAccounts($country_id, $agency, $tas_filter);
        //echo $this->db->last_query();
        //exit();
        
        //File name 
        $filename = 'export_trusted_accounts' . date('Y-m-d') . '.csv';

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        //File creation 
        $file = fopen('php://output', 'w');

        //Header
        $header = array("Agency", "Trust Account Software", "Available to Connect", "API Connected", "Marker ID");
        
        fputcsv($file, $header);

        foreach ($list['trusted_accounts'] as $row) {

            $data['Agency']      = $row->agency_name;
            $data['Trust Account Software']        = $row->tsa_name;

            $avc['available_api'] = $this->agency_model->getAvailableApi($row->agency_id);

            $api_array = array();
            foreach ($avc['available_api'] as $key) {
                array_push($api_array, $key->api_name);
            }

            $data['Available to Connect']     = implode(", ",$api_array);

            $apc['connected_api'] = $this->agency_model->getConnectedApi($row->agency_id);

            $capi_array = array();
            foreach ($apc['connected_api'] as $key1) {
                array_push($capi_array, $key1->api_name);
            }

            $data['API Connected']     = implode(", ",$capi_array);

            if( $row->pme_supplier_id == ""){
                $marker_id = $row->palace_diary_id;
            }
            else{
                $marker_id = $row->pme_supplier_id;
            }
            $data['Marker ID']     = strtoupper($marker_id);

            fputcsv($file, $data);
        }
        fclose($file);
        exit;
    }//endfct

    public function view_all_agencies() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "All Agencies";
        $country_id = $this->config->item('country');

        $status_filter = $this->input->get_post('status_filter');
        $state_filter = $this->input->get_post('state_filter');
        $sales_rep_filter = $this->input->get_post('sales_rep_filter');
        $search_filter = $this->input->get_post('search_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if (!empty($sub_region_ms)) {
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');


        $sel_query = "a.agency_name as a_name, a.address_1, a.address_2, a.address_3, a.state, a.postcode, a.status, a.agency_id as a_id, a.tot_properties, sa.FirstName, sa.LastName, aght.priority";



        if ($this->input->get_post('export') && $this->input->get_post('export') == 1) { //EXPORT
            $params = array(
                'sel_query' => $sel_query,
                'join_table' => array('postcode_regions', 'salesrep', 'country', 'agency_priority'),
                'country_id' => $country_id,
                'a_status' => $status_filter,
                'state' => $state_filter,
                'salesrep' => $sales_rep_filter,
                'agency_name' => $search_filter,
                'postcodes' => $postcodes,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC'
                    )
                )
            );
            $lists_export_query = $this->agency_model->get_agency($params);

            $filename = 'all_agencies' . date('Y-m-d') . '.csv';

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");

            //header
            echo "Agency Name,Sub Region,Status,Sales Rep\n";

            foreach ($lists_export_query->result_array() as $list_item) {

                $params_get_region = array(
                    'sel_query' => "sr.subregion_name as postcode_region_name",
                    'postcode' => $list_item['postcode'],
                    'display_query' => 0
                );
                $getRegion = $this->system_model->get_postcodes($params_get_region)->row();


                $region = $getRegion->postcode_region_name;
                $status = ucfirst($list_item['status']);
                $agency_name = $list_item['a_name'];
                $sales_rep = $this->system_model->formatStaffName($list_item['FirstName'], $list_item['LastName']);

                echo "\"{$agency_name}\",{$region},\"$status\",{$sales_rep}\n";
            }
        } else { //LIST VIEW
            $params = array(
                'sel_query' => $sel_query,
                'join_table' => array('postcode_regions', 'salesrep', 'country','agency_priority'),
                'country_id' => $country_id,
                'a_status' => $status_filter,
                'state' => $state_filter,
                'salesrep' => $sales_rep_filter,
                'agency_name' => $search_filter,
                'postcodes' => $postcodes,
                'limit' => $per_page,
                'offset' => $offset,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['lists'] = $this->agency_model->get_agency($params);

            // all rows
            $total_sel_query = "COUNT(a.`agency_id`) as a_count";
            $total_params = array(
                'sel_query' => $total_sel_query,
                'join_table' => array('postcode_regions', 'salesrep', 'country'),
                'country_id' => $country_id,
                'a_status' => $status_filter,
                'state' => $state_filter,
                'salesrep' => $sales_rep_filter,
                'agency_name' => $search_filter,
                'postcodes' => $postcodes,
            );
            $query = $this->agency_model->get_agency($total_params);
            $total_rows = $query->row()->a_count;


            // status filter
            $status_sel_query = "DISTINCT(a.`status`),
            a.`status`";
            $status_params = array(
                'sel_query' => $status_sel_query,
                'join_table' => array('postcode_regions', 'salesrep', 'country'),
                'country_id' => $country_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`status`',
                        'sort' => 'ASC',
                    )
                ),
            );
            $data['status_filter'] = $this->agency_model->get_agency($status_params);


            // Region Filter ( get distinct state )
            $sel_query = "DISTINCT(a.`state`),a.`state`";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                'join_table' => array('postcode_regions', 'salesrep', 'country'),
                'country_id' => $country_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`state`',
                        'sort' => 'ASC',
                    )
                ),
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);


            // state filter
            $sel_query = "DISTINCT(a.`state`),
            a.`state`";
            $params = array(
                'sel_query' => $sel_query,
                'join_table' => array('postcode_regions', 'salesrep', 'country'),
                'country_id' => $country_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`state`',
                        'sort' => 'ASC',
                    )
                ),
            );
            $data['state_filter_json'] = json_encode($params);


            //salesrep filter
            $data['salesrep'] = $this->agency_model->getAgencySalesRep('target');


            //base url params
            $pagi_links_params_arr = array(
                'status_filter' => $status_filter,
                'state_filter' => $state_filter,
                'sales_rep_filter' => $sales_rep_filter,
                'sub_region_ms' => $sub_region_ms,
                'search_filter' => $search_filter
            );
            $pagi_link_params = '/agency/view_all_agencies/?' . http_build_query($pagi_links_params_arr);

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


            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('agency/view_all_agencies', $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }

    public function agency_keys() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Keys";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $data['from'] = ($this->input->get_post('date_from_filter') != "") ? $this->input->get_post('date_from_filter') : date('d/m/Y');
        $data['to'] = ($this->input->get_post('date_to_filter') != "") ? $this->input->get_post('date_to_filter') : date('d/m/Y');
        $agency = $this->input->get_post('agency_filter');
        $tech_id = $this->input->get_post('tech_filter');

        //list
        $sel_query = "
            j.id as jid,
            j.created as jcreated,
            j.`service` AS jservice, 			
			j.`status` AS jstatus, 
            j.`date` AS jdate, 
            j.booked_with,
            j.service as j_service,
            j.ts_completed,
            
            p.property_id,
			p.`address_1` AS p_address_1, 
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3, 
			p.`state` AS p_state, 			
			
			jr.`name` AS jr_name, 
			
            a.`phone` AS a_phone,
            a.agency_name,
            a.agency_id,
            a.phone as a_phone,
            aght.priority,

            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type,
            
            sa.FirstName,
            sa.LastName
            
        ";
        $params = array(
            'sel_query' => $sel_query,
            'from_date' => $data['from'],
            'to_date' => $data['to'],
            'agency' => $agency,
            'tech_id' => $tech_id,
            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'DESC'
                )
            ),
            'limit' => $per_page,
            'offset' => $offset,
            'display_query' => 0
        );
        $data['lists'] = $this->agency_model->jgetAgencyKeys($params);

        //list count
        $params = array(
            'sel_query' => "COUNT(j.`id`) AS jcount",
            'from_date' => $data['from'],
            'to_date' => $data['to'],
            'agency' => $agency,
            'tech_id' => $tech_id
        );
        $query = $this->agency_model->jgetAgencyKeys($params);
        $total_rows = $query->row()->jcount;


        //Agency filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'from_date' => $data['from'],
            'to_date' => $data['to'],
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC'
                )
            )
        );
        $data['agency_filter'] = $this->agency_model->jgetAgencyKeys($params);

        //Tech filter
        $sel_query = "DISTINCT(sa.`StaffID`), sa.`FirstName`, sa.LastName";
        $params = array(
            'sel_query' => $sel_query,
            'from_date' => $data['from'],
            'to_date' => $data['to'],
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                )
            )
        );
        $data['tech_filter'] = $this->agency_model->jgetAgencyKeys($params);


        $pagi_links_params_arr = array(
            'date_from_filter' => $data['from'],
            'date_to_filter' => $data['to'],
            'agency_filter' => $agency,
            'tech_filter' => $tech_id
        );
        $pagi_link_params = '/agency/agency_keys/?' . http_build_query($pagi_links_params_arr);

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


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/agency_keys', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function agency_portal_data() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Portal Data";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get_post
        $agency = $this->input->get_post('agency_filter');
        $user = $this->input->get_post('user_filter');
        $from = $this->input->get_post('date_from_filter');
        $to = $this->input->get_post('date_to_filter');

        $from2 = ( $this->system_model->isDateNotEmpty($from) ) ? $this->system_model->formatDate($from) : NULL;
        $to2 = ( $this->system_model->isDateNotEmpty($to) ) ? $this->system_model->formatDate($to) : NULL;


        //get list
        $sel_query = '
            aul.`agency_user_login_id`,
            aul.`ip`,
            aul.`date_created`,

            aua.agency_user_account_id,
            aua.`fname`,
            aua.`lname`,

            a.`agency_id`,
            a.`agency_name`,
            aght.priority,
            apmd.abbreviation
        ';
        $params = array(
            'sel_query' => $sel_query,
            'agency' => $agency,
            'user' => $user,
            'from' => $from2,
            'to' => $to2,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'aul.date_created',
                    'sort' => 'DESC'
                )
            ),
            'display_query' => 0
        );
        $data['lists'] = $this->agency_model->getAgencyUserLogins($params);

        //all rows
        $params = array(
            'sel_query' => "COUNT(aul.`agency_user_login_id`) as c_agency_user_login_id",
            'agency' => $agency,
            'user' => $user,
            'from' => $from2,
            'to' => $to2
        );
        $query = $this->agency_model->getAgencyUserLogins($params);
        $total_rows = $query->row()->c_agency_user_login_id;


        //AGENCY FILTER
        $params = array(
            'sel_query' => "DISTINCT(a.agency_id), a.agency_name",
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );
        $data['agency_filter'] = $this->agency_model->getAgencyUserLogins($params);

        //User FILTER
        $params = array(
            'sel_query' => "DISTINCT(aua.agency_user_account_id), aua.fname, aua.lname",
            'sort_list' => array(
                array(
                    'order_by' => 'aua.fname',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );
        $data['user_filter'] = $this->agency_model->getAgencyUserLogins($params);



        //base url params
        $pagi_links_params_arr = array(
            'agency_filter' => $agency,
            'user_filter' => $user,
            'date_from_filter' => $from2,
            'date_to_filter' => $to2
        );
        $pagi_link_params = '/agency/agency_portal_data/?' . http_build_query($pagi_links_params_arr);

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


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/agency_portal_data', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view_deactivated_agencies() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Deactivated Agencies";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get-post
        $country_id = $this->config->item('country');
        $state = $this->input->get_post('state_filter');
        $sales_rep = $this->input->get_post('salesrep_filter');
        $using = $this->input->get_post('agency_using_filter');
        $search = $this->input->get_post('search');
        $date_from = ( $this->input->get_post('date_from') !='' )?$this->system_model->formatDate($this->input->get_post('date_from')):'';
        $date_to = ( $this->input->get_post('date_to') !='' )?$this->system_model->formatDate($this->input->get_post('date_to')):'';   
        //$status_filter = $this->input->get_post('status_filter') ?? 'deactivated';
        //$status_filter = $this->input->get_post('status_filter') ?? '';

        //$status_filter = ( $this->input->get_post('status_filter') != '' )?$this->input->get_post('status_filter'):'deactivated';

        if( $this->input->get_post('status_filter') != '' ){

            if( $this->input->get_post('status_filter') == 'all' ){ // all
                $status_filter = '';
            }else{ // deactivated or target
                $status_filter = $this->input->get_post('status_filter');
            }

        }else{ // default is deactivated

            $status_filter = 'deactivated';

        }

        $data['status_filter'] = $status_filter;

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if (!empty($sub_region_ms)) {
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }

        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;

        //get deactivated agency list
        $sel_query = "
        a.agency_name as a_name, 
        a.address_1, a.address_2, a.address_3, 
        a.state, 
        a.postcode, 
        a.status, 
        a.agency_id as a_id, 
        a.deactivated_ts,
        a.deactivated_reason,
        a.active_prop_with_sats,
        a.joined_sats,
        a.tot_properties, 
        a.salesrep,
        sr.sub_region_id as postcode_region_id, 
        sr.subregion_name as postcode_region_name,
        au.name as agency_using,
        sa.FirstName,
        sa.LastName,
        aght.priority
        ";
        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('postcode_regions', 'agency_using','salesrep','agency_priority'),
            'a_status' => $status_filter,
            'a_deactivated_ts' => true,
            'date_from_deac' => $date_from,
            'date_to_deac' => $date_to,
            'country_id' => $country_id,
            'state' => $state,
            'salesrep' => $sales_rep,
            'agency_using_id' => $using,
            'search' => $search,
            'postcodes' => $postcodes,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`deactivated_ts`',
                    'sort' => 'DESC'
                )
            ),
            'display_query' => 0
        );

        if($this->input->get_post('export') && $this->input->get_post('export')==1){

            unset($params['limit']);
            unset($params['offset']);
            
            $lists_export_query = $this->agency_model->get_agency($params);

            $filename = "Deactivated_Agencies_".date("d/m/Y").".csv";
            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");
            echo "Agency Name,Sales Rep,Region,Date Activated,Date Deactivated,Reason,Total Active Properties,New Supplier\n";
            
            foreach ($lists_export_query->result_array() as $list_item) {

                $salesrep = $this->system_model->formatStaffName($list_item['FirstName'], $list_item['LastName']);
                $region = ( $list_item['postcode_region_id']!="" )?$list_item['postcode_region_name']:'';
                $date_activated = $this->system_model->isDateNotEmpty($list_item['joined_sats']) ? $this->system_model->formatDate($list_item['joined_sats'],'d/m/y') : NULL;
                $date_deactivated = $this->system_model->isDateNotEmpty($list_item['deactivated_ts']) ? $this->system_model->formatDate($list_item['deactivated_ts'],'d/m/y') : NULL;

                echo "\"".trim($list_item['a_name'])."\",\"{$salesrep}\",\"{$region}\",\"{$date_activated}\",\"{$date_deactivated}\",\"".$list_item['deactivated_reason']."\",\"{$list_item['active_prop_with_sats']}\",\"{$list_item['agency_using']}\"\n";
            
            }

        }else{

            $params['limit'] = $per_page;
			$params['offset'] = $offset;
            
            $data['lists'] = $this->agency_model->get_agency($params);
            $data['sql_query'] = $this->db->last_query();

            //all rows
            $params = array(
                'sel_query' => "COUNT(a.`agency_id`) AS acount",
                'join_table' => array('postcode_regions', 'agency_using','salesrep'),
                'a_status' => $status_filter,
                'a_deactivated_ts' => true,
                'date_from_deac' => $date_from,
                'date_to_deac' => $date_to,
                'country_id' => $country_id,
                'state' => $state,
                'salesrep' => $sales_rep,
                'agency_using_id' => $using,
                'search' => $search,
                'postcodes' => $postcodes,
                'display_query' => 0
            );
            $list_query_total = $this->agency_model->get_agency($params);
            $total_rows = $list_query_total->row()->acount;

            //State FILTER
            $sel_query = "DISTINCT(a.`state`)";
            $params = array(
                'sel_query' => $sel_query,
                'join_table' => array('postcode_regions', 'agency_using'),
                'a_status' => "deactivated",
                'country_id' => $country_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`state`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);

            // Region Filter ( get distinct state )
            $sel_query = "DISTINCT(a.`state`)";
            $region_filter_arr = array(
                'sel_query' => $sel_query,

                'join_table' => array('postcode_regions', 'agency_using','salesrep'),
                'a_status' => "deactivated",
                'country_id' => $country_id,              

                'sort_list' => array(
                    array(
                        'order_by' => 'a.`state`',
                        'sort' => 'ASC',
                    )
                ),                
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);


            //SALES REP FILTER
            $data['sales_rep_filter'] = $this->agency_model->getAgencySalesRep('deactivated');


            //USING FILTER
            $data['using_filter'] = $this->agency_model->getAgencyUsing();


            //base url params
            $pagi_links_params_arr = array(
                'state_filter' => $state,
                'salesrep_filter' => $sales_rep,
                'agency_using_filter' => $using,
                'search' => $search,
                'sub_region_ms' => $sub_region_ms,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'status_filter' => $status_filter
            );
            $pagi_link_params = '/agency/view_deactivated_agencies/?' . http_build_query($pagi_links_params_arr);

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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('agency/view_deactivated_agencies', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }

    }

    public function view_price_increase_excluded_agencies() 
    {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Price Increase Excluded Agencies";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get-post
        $country_id = $this->config->item('country');
        $state = $this->input->get_post('state_filter');
        $sales_rep = $this->input->get_post('salesrep_filter');
        $search = $this->input->get_post('search');
        $date_from = ( $this->input->get_post('date_from') !='' )?$this->system_model->formatDate($this->input->get_post('date_from')):'';
        $date_to = ( $this->input->get_post('date_to') !='' )?$this->system_model->formatDate($this->input->get_post('date_to')):'';   

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if (!empty($sub_region_ms)) {
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }

        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;

        if ($date_from != '') {
            $custom_where = "(exa.`exclude_until` BETWEEN '$date_from' AND '$date_to')";
        } else {
            $custom_where = "(exa.`exclude_until` >= NOW() OR exa.`exclude_until` = '0000-00-00' OR exa.`exclude_until` IS NULL)";
        }

        //get deactivated agency list
        $sel_query = "
        a.agency_name as a_name, 
        a.address_1, a.address_2, a.address_3, 
        a.agency_id as a_id, 
        a.salesrep,
        a.state,
        a.active_prop_with_sats,
        sr.sub_region_id as postcode_region_id, 
        sr.subregion_name as postcode_region_name,
        au.name as agency_using,
        sa.FirstName,
        sa.LastName,
        aght.priority,
        exa.exclude_until
        ";

        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('agency', 'postcode_regions', 'agency_using','salesrep', 'agency_priority'),
            'custom_where' => $custom_where,
            'country_id' => $country_id,
            'state' => $state,
            'salesrep' => $sales_rep,
            'agency_using_id' => $using,
            'search' => $search,
            'postcodes' => $postcodes,
            'sort_list' => array(
                array(
                    'order_by' => 'exa.`exclude_until`',
                    'sort' => 'DESC'
                )
            ),
            'display_query' => 0
        );

        if($this->input->get_post('export') && $this->input->get_post('export')==1){

            unset($params['limit']);
            unset($params['offset']);
            
            $lists_export_query = $this->agency_model->get_price_increase_excluded_agency($params);

            $filename = "Price_Increase_Excluded_Agencies_".date("d/m/Y").".csv";
            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");
            echo "Agency ID,Agency Name,Excluded Until,Sales Rep,State,Region,Total Active Properties\n";
            
            foreach ($lists_export_query->result_array() as $list_item) {

                $salesrep = $this->system_model->formatStaffName($list_item['FirstName'], $list_item['LastName']);
                $region = ( $list_item['postcode_region_id']!="" )?$list_item['postcode_region_name']:'';
                $excluded_until = $this->system_model->isDateNotEmpty($list_item['exclude_until']) ? $this->system_model->formatDate($list_item['exclude_until'],'d/m/y') : NULL;

                echo "\"{$list_item['a_id']}\",\"".trim($list_item['a_name'])."\",\"{$excluded_until}\",\"{$salesrep}\",\"{$list_item['state']}\",\"{$region}\",\"{$list_item['active_prop_with_sats']}\"\n";
            
            }

        }else{

            $params['limit'] = $per_page;
			$params['offset'] = $offset;
            
            $data['lists'] = $this->agency_model->get_price_increase_excluded_agency($params);
            $total_row_count = count($data['lists']->result());
            $data['sql_query'] = $this->db->last_query();

            //all rows
            // $params = array(
            //     'sel_query' => "COUNT(exa.`agency_id`) AS acount",
            //     'join_table' => array('postcode_regions', 'agency_using','salesrep'),
            //     'custom_where' => $custom_where,
            //     'country_id' => $country_id,
            //     'state' => $state,
            //     'salesrep' => $sales_rep,
            //     'agency_using_id' => $using,
            //     'search' => $search,
            //     'postcodes' => $postcodes,
            //     'display_query' => 0
            // );
            // $list_query_total = $this->agency_model->get_price_increase_excluded_agency($params);
            // $total_rows = $list_query_total->row()->acount;
            $total_rows = $total_row_count;

            //State FILTER
            $sel_query = "DISTINCT(a.`state`)";
            $params = array(
                'sel_query' => $sel_query,
                'join_table' => array('postcode_regions', 'agency_using'),
                'a_status' => "active",
                'country_id' => $country_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`state`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);

            // Region Filter ( get distinct state )
            $sel_query = "DISTINCT(a.`state`)";
            $region_filter_arr = array(
                'sel_query' => $sel_query,

                'join_table' => array('postcode_regions', 'agency_using','salesrep'),
                'a_status' => "active",
                'country_id' => $country_id,              

                'sort_list' => array(
                    array(
                        'order_by' => 'a.`state`',
                        'sort' => 'ASC',
                    )
                ),                
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);


            //SALES REP FILTER
            $data['sales_rep_filter'] = $this->agency_model->getAgencySalesRep('active');


            //USING FILTER
            $data['using_filter'] = $this->agency_model->getAgencyUsing();


            //base url params
            $pagi_links_params_arr = array(
                'state_filter' => $state,
                'salesrep_filter' => $sales_rep,
                'agency_using_filter' => $using,
                'search' => $search,
                'sub_region_ms' => $sub_region_ms,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'status_filter' => $status_filter
            );
            $pagi_link_params = '/agency/view_price_increase_excluded_agencies/?' . http_build_query($pagi_links_params_arr);

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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('agency/view_price_increase_excluded_agencies', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }

    }

    public function maintenance_program_agencies() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Maintenance Program Agencies";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get/post
        $country_id = $this->config->item('country');
        $agency = $this->input->get_post('agency_filter');
        $software = $this->input->get_post('software_filter');


        //LIST
        $params = array(
            'sel_query' => "a.agency_id, a.agency_name, m.name as m_name, am.price, am.surcharge_msg, aght.priority",
            'mm_id' => $software,
            'search' => $agency,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );
        $data['lists'] = $this->agency_model->getMaintenanceProgramAgencies($params);

        //all rows
        $params = array(
            'sel_query' => "COUNT(a.`agency_id`) as a_count",
            'mm_id' => $software,
            'search' => $agency,
            'limit' => $per_page,
            'offset' => $offset
        );
        $query = $this->agency_model->getMaintenanceProgramAgencies($params);
        $total_rows = $query->row()->a_count;


        //Software Filter
        $params = array(
            'sel_query' => "DISTINCT(m.maintenance_id), m.name",
            'sort_list' => array(
                array(
                    'order_by' => 'm.`name`',
                    'sort' => 'ASC'
                )
            )
        );
        $data['software_filter'] = $this->agency_model->getMaintenanceProgramAgencies($params);


        //base url params
        $pagi_links_params_arr = array(
            'agency_filter' => $agency,
            'software_filter' => $software
        );
        $pagi_link_params = '/agency/maintenance_program_agencies/?' . http_build_query($pagi_links_params_arr);

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


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/maintenance_program_agencies', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function non_auto_renew_agencies() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Non Auto Renew Agencies";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get/post
        $agency = $this->input->get_post('agency_filter');

        //get list
        $custom_where = "a.auto_renew = 0";
        $custom_where2 = "a.agency_name LIKE '%{$agency}%' ";
        $sel_query = "
            a.`agency_id`,
            a.`agency_name`,
            a.`status`,
            a.tenant_details_contact_name,
            a.tenant_details_contact_phone,
            aght.priority
        ";
        $params = array(
            'sel_query' => $sel_query,
            'a_status' => 'active',
            'custom_where_arr' => array($custom_where, $custom_where2),
            'join_table'    => array('agency_priority'),
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'country_id' => $country_id,
            'display_query' => 0
        );
        $data['lists'] = $this->agency_model->get_agency($params);

        // total rows
        $sel_query = "COUNT(a.`agency_id`) as a_count";
        $params = array(
            'sel_query' => $sel_query,
            'a_status' => 'active',
            'custom_where_arr' => array($custom_where, $custom_where2),
            'country_id' => $country_id,
            'display_query' => 0
        );
        $query = $this->agency_model->get_agency($params);
        $total_rows = $query->row()->a_count;




        //base url params
        $pagi_links_params_arr = array(
            'agency_filter' => $agency
        );
        $pagi_link_params = '/agency/non_auto_renew_agencies/?' . http_build_query($pagi_links_params_arr);

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

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/no_auto_renew_agencies', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function subscription_billing_agencies() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Subscription Billing";
        $country_id = $this->config->item('country');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get/post
        $agency = $this->input->get_post('agency_filter');
        $subscription = $this->input->get_post('subscription_filter');

        //get list
        $sel_query = "
            a.`agency_id`,
            a.`agency_name`,
            a.`allow_upfront_billing`,
            a.`subscription_notes`,
            a.`subscription_notes_update_ts`,
            a.`subscription_notes_update_by`,
            aght.priority,

            snub.`FirstName` AS snub_fname,
            snub.`LastName` AS snub_lname
        ";

        if ($agency != '') {
            $custom_where_search = "a.agency_name LIKE '%{$agency}%' ";  //filter
        }

        $params = array(
            'sel_query' => $sel_query,
            'custom_joins_ver2' => array(
                'join_table' => 'staff_accounts as snub',
                'join_query' => "snub.StaffID = a.subscription_notes_update_by",
                'join_type' => "left"
            ),
            'join_table'    => array('agency_priority'),
            'a_status' => 'active',
            'custom_where_arr' => array($custom_where_search),
            'allow_upfront_billing' => $subscription,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`allow_upfront_billing`',
                    'sort' => 'DESC'
                )
            ),
            'country_id' => $country_id,
            'display_query' => 0
        );
        $data['lists'] = $this->agency_model->get_agency($params);

        //list count
        $params = array(
            'sel_query' => "COUNT(a.`agency_id`) AS acount",
            'custom_joins_ver2' => array(
                'join_table' => "staff_accounts as snub",
                'join_query' => "snub.StaffID = a.subscription_notes_update_by",
                'join_type' => "left"
            ),
            'a_status' => 'active',
            'custom_where_arr' => array($custom_where_search),
            'allow_upfront_billing' => $subscription,
            'country_id' => $country_id
        );
        $query = $this->agency_model->get_agency($params);
        $total_rows = $query->row()->acount;


        //base url params
        $pagi_links_params_arr = array(
            'agency_filter' => $agency,
            'subscription_filter' => $subscription
        );
        $pagi_link_params = '/agency/subscription_billing_agencies/?' . http_build_query($pagi_links_params_arr);

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


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/subscription_billing_agencies', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_update_agency_subscription_notes() {

        $agency_id = $this->input->post('agency_id');
        $subscription_notes = $this->input->post('subscription_notes');

        if ($agency_id && !empty($agency_id) && is_numeric($agency_id)) {

            $update_data = array(
                'subscription_notes' => $subscription_notes,
                'subscription_notes_update_ts' => date('Y-m-d H:i:s'),
                'subscription_notes_update_by' => $this->session->staff_id
            );
            $this->db->where('agency_id', $agency_id);
            $this->db->update('agency', $update_data);
            $this->db->limit(1);
        }
    }

    public function services() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Services";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $country_id = $this->config->item('country');

        //get/post
        $status_filter = "active";
        $state_filter = $this->input->get_post('state_filter');
        $sales_rep_filter = $this->input->get_post('sales_rep_filter');
        $search_filter = $this->input->get_post('search_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if (!empty($sub_region_ms)) {
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }


        //GET LIST
        $custom_where_search = "a.agency_name LIKE '%{$search_filter}%' ";  //filter
        $sel_query = "a.agency_id as a_id, a.agency_name as a_name, a.state, a.postcode, a.status, a.tot_properties, aght.priority, apmd.abbreviation";
        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('postcode_regions', 'country', 'agency_priority', 'agency_priority_marker_definition'),
            'country_id' => $country_id,
            'a_status' => $status_filter,
            'state' => $state_filter,
            'salesrep' => $sales_rep_filter,
            'postcodes' => $postcodes,
            'custom_where_arr' => array($custom_where_search),
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $data['lists'] = $this->agency_model->get_agency($params);

        // all rows
        $total_sel_query = "COUNT(a.`agency_id`) as a_count";
        $total_params = array(
            'sel_query' => $total_sel_query,
            'join_table' => array('postcode_regions', 'salesrep', 'country', 'agency_priority'),
            'country_id' => $country_id,
            'custom_where_arr' => array($custom_where_search),
            'a_status' => $status_filter,
            'state' => $state_filter,
            'salesrep' => $sales_rep_filter,
            'postcodes' => $postcodes,
        );
        $query = $this->agency_model->get_agency($total_params);
        $total_rows = $query->row()->a_count;


        // Region Filter ( get distinct state )
        $sel_query = "DISTINCT(a.`state`),a.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'join_table' => array('postcode_regions', 'salesrep', 'country'),
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`state`',
                    'sort' => 'ASC',
                )
            ),
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);


        // state filter
        $sel_query = "DISTINCT(a.`state`),
        a.`state`";
        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('postcode_regions', 'salesrep', 'country'),
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`state`',
                    'sort' => 'ASC',
                )
            ),
        );
        $data['state_filter_json'] = json_encode($params);


        //salesrep filter
        $data['salesrep'] = $this->agency_model->getAgencySalesRep('target');



        $service_params = array(
            'sel_query' => "id,type,short_name,",
            'active' => 1
        );
        $data['services'] = $this->system_model->getServiceTypes($service_params);


        $services_count_params = array(
            'sel_query' => "COUNT(id) as agency_count",
            'active' => 1
        );
        $data['services_count'] = $this->system_model->getServiceTypes($services_count_params)->row()->agency_count;




        //base url params
        $pagi_links_params_arr = array(
            'state_filter' => $state_filter,
            'sales_rep_filter' => $sales_rep_filter,
            'sub_region_ms' => $sub_region_ms,
            'search_filter' => $search_filter
        );
        $pagi_link_params = '/agency/services/?' . http_build_query($pagi_links_params_arr);

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


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/services', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function multi_agency_users() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Multi-Agency Users";
        $uri = '/agency/multi_agency_users';

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;


        $this->form_validation->set_rules('user', 'User', 'required');

        if ($this->form_validation->run() == true) {

            $aua_id = $this->input->get_post('user');
            $alt_agency_arr = $this->input->get_post('alt_agency'); // new agency connection
            $existing_alt_agencies = $this->input->get_post('alt_agencies'); // existing agency connection

            if ($aua_id > 0 && count($alt_agency_arr) > 0) {

                $alt_agency_imp = implode(",", $alt_agency_arr);

                // if connected agencies exist, append new connected agencies
                $alt_agencies_str = null;
                if ($existing_alt_agencies != '') {
                    $alt_agencies_str = "{$existing_alt_agencies},{$alt_agency_imp}";
                } else {
                    $alt_agencies_str = "{$alt_agency_imp}";
                }

                $update_data = array(
                    'alt_agencies' => $alt_agencies_str
                );

                $this->db->where('agency_user_account_id', $aua_id);
                $this->db->update('agency_user_accounts', $update_data);

                $this->session->set_flashdata('add_mauc_success', 1);
                redirect($uri);
            }
        }

        // main list
        $sel_query = "
            aua.`agency_user_account_id`,
            aua.`fname`,
            aua.`lname`,
            aua.`email`,           
            aua.`photo`,
            aua.`active`,
            aua.`alt_agencies`,

            auat.`user_type_name`,
            auat.`agency_user_account_type_id`,

            a.`agency_id`,
            a.`agency_name`            
		";

        $custom_where = "aua.`alt_agencies` IS NOT NULL AND a.deleted = 0";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'join_table' => array('agency_user_account_types', 'agency'),
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                ),
                array(
                    'order_by' => 'aua.`fname`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'aua.`lname`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['list'] = $this->agency_model->get_users($params);

        // get all agencies
        $sel_query = "
            a.`agency_id`,
            a.`agency_name`,
            a.`status`,
            a.`initial_setup_done`
        ";

        $alt_agen_params = array(
            'sel_query' => $sel_query,
            'a_status' => 'active',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'country_id' => $this->config->item('country'),
            'display_query' => 0
        );
        $data['agency_sql'] = $this->agency_model->get_agency($alt_agen_params);

        $data['uri'] = $uri;

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function disconnect_alt_agency() {

        $aua_id = $this->input->get_post('user');  // aua ID
        $agency_id = $this->input->get_post('agency_id'); // agency to disconnect       
        $alt_agencies = $this->input->get_post('alt_agencies');

        $alt_agencies_imp = explode(",", $alt_agencies);
        $new_alt_agencies_arr = [];


        foreach ($alt_agencies_imp as $alt_agency) {

            if ($alt_agency != $agency_id) { // exclude disconnected agency to new alt agency data
                $new_alt_agencies_arr[] = $alt_agency;
            }
        }

        // insert new alt agency without the disconnected agency ID
        if ($aua_id > 0) {

            if (count($new_alt_agencies_arr) > 0) {
                $alt_agency_imp = implode(',', $new_alt_agencies_arr);
            } else {
                $alt_agency_imp = null;
            }

            $update_data = array(
                'alt_agencies' => $alt_agency_imp
            );

            $this->db->where('agency_user_account_id', $aua_id);
            $this->db->update('agency_user_accounts', $update_data);
        }

        redirect('/agency/multi_agency_users');
    }

    public function get_users() {

        $agency_id = $this->input->get_post('agency_id');
        $display_user_id = $this->input->get_post('display_user_id');
        $exclude_id = $this->input->get_post('exclude_id');

        $sel_query = '
            aua.`agency_user_account_id`,
            aua.`fname`,
            aua.`lname`
        ';

        if ($exclude_id > 0) {
            $custom_where = "aua.`agency_user_account_id` != {$exclude_id}";
        } else {
            $custom_where = null;
        }

        // paginated results
        $user_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'agency_id' => $agency_id,
            'active' => 1,
            'display_query' => 0
        );

        $user_sql = $this->agency_model->get_users($user_params);




        $option_str = '<option value="">SELECT</option>';
        foreach ($user_sql->result() as $user) {

            // append user ID
            $user_id_str = ( $display_user_id == 1 ) ? " (#{$user->agency_user_account_id})" : null;

            $fullname = "{$user->fname} {$user->lname}";

            $option_str .= '<option value="' . $user->agency_user_account_id . '">' . $fullname . $user_id_str . '</option>';
        }

        echo $option_str;
    }

    public function duplicate_users() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Duplicate Users";
        $uri = '/agency/duplicate_users';

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $this->form_validation->set_rules('move_to_user', 'Move To User', 'required');

        if ($this->form_validation->run() == true) {

            $move_property = $this->input->get_post('move_property');
            $move_to_user = $this->input->get_post('move_to_user');

            // move properties to user
            if ($move_to_user > 0 && count($move_property) > 0) {

                $update_data = array(
                    'pm_id_new' => $move_to_user
                );

                $this->db->where_in('property_id', $move_property);
                //echo $this->db->set($update_data)->get_compiled_update('property');
                $this->db->update('property', $update_data);
            }

            $this->session->set_flashdata('move_to_user', 1);
            redirect($uri);
        }


        // main list
        $sel_query = "            
            aua.`agency_user_account_id`,
            aua.`fname`,
            aua.`lname`,
            aua.`email`,           
            aua.`photo`,
            aua.`active` AS aua_active,
            aua.`alt_agencies`,

            auat.`user_type_name`,
            auat.`agency_user_account_type_id`,

            a.`agency_id`,
            a.`agency_name`            
        ";

        $custom_where = "
            aua.`email` IN(

                SELECT `email`
                FROM `agency_user_accounts`
                WHERE `email` != ''
                GROUP BY `email`
                HAVING COUNT(`email`) > 1
            
            )
        ";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'join_table' => array('agency_user_account_types', 'agency'),
            'sort_list' => array(
                array(
                    'order_by' => 'aua.`email`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'aua.`fname`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'aua.`lname`',
                    'sort' => 'ASC',
                )
            ),
            'limit' => $per_page,
            'offset' => $offset,
            'display_query' => 0
        );
        $data['list'] = $this->agency_model->get_users($params);


        // total rows
        $sel_query = "COUNT(aua.`agency_user_account_id`) as jcount";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'join_table' => array('agency_user_account_types', 'agency'),
            'display_query' => 0
        );
        $tot_row_query = $this->agency_model->get_users($params);
        $total_rows = $tot_row_query->row()->jcount;


        // get all agencies
        $sel_query = "
            a.`agency_id`,
            a.`agency_name`,
            a.`status`
        ";

        $alt_agen_params = array(
            'sel_query' => $sel_query,
            'a_status' => 'active',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'country_id' => $this->config->item('country'),
            'display_query' => 0
        );
        $data['agency_sql'] = $this->agency_model->get_agency($alt_agen_params);


        // pagination settings
        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter
        );
        $pagi_link_params = $uri . '/?' . http_build_query($pagi_links_params_arr);

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

        $data['uri'] = $uri;

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function toggle_user_status() {

        $aua_id = $this->input->get_post('aua_id');
        $active = $this->input->get_post('active');

        // move properties to user
        if ($aua_id > 0 && is_numeric($active)) {

            $update_status_to = ( $active == 1 ) ? 0 : 1;

            $update_data = array(
                'active' => $update_status_to
            );

            $this->db->where('agency_user_account_id', $aua_id);
            //echo $this->db->set($update_data)->get_compiled_update('agency_user_accounts');
            $this->db->update('agency_user_accounts', $update_data);
        }
    }

    public function add_agency_row() {

        $existing_agency_arr = $this->input->get_post('existing_agency_arr');
        $option_str = null;

        // get all agencies
        $sel_query = "
            a.`agency_id`,
            a.`agency_name`,
            a.`status`,
            a.`initial_setup_done`
        ";

        if (count($existing_agency_arr) > 0) {

            $existing_agency_imp = implode(",", $existing_agency_arr);
            $custom_where = "a.`agency_ID` NOT IN({$existing_agency_imp})";
        } else {
            $custom_where = null;
        }


        $alt_agen_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'a_status' => 'active',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'country_id' => $this->config->item('country'),
            'display_query' => 0
        );
        $agency_sql = $this->agency_model->get_agency($alt_agen_params);



        $option_str .= '
        <div class="form-group row alt_agency_row">
            <div class="col-md-11">
                <select name="alt_agency[]" class="form-control alt_agency_arr" data-validation="[NOTEMPTY]">
                    <option value="">SELECT</option>';
        foreach ($agency_sql->result() as $agency) {
            $option_str .= '<option value="' . $agency->agency_id . '">' . $agency->agency_name . '</option>';
        }
        $option_str .= '
                </select>
            </div>
                <div class="col-md-1">
                    <a href="javascript:void(0);">
                        <i class="fa fa-remove"></i>
                    </a>
                </div>
        </div>';

        echo $option_str;
    }

    public function trust_account_software() {

        $this->load->model('api_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Trust Account Software";
        $data['uri'] = "/agency/trust_account_software";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get/post
        $country_id = $this->config->item('country');
        $agency = $this->input->get_post('agency_filter');
        $tas_filter = $this->input->get_post('tas_filter');

        $data['agency'] = $agency;
        $data['tas_filter'] = $tas_filter;


        //LIST
        $sel_query = "
            a.`agency_id`, 
            a.`agency_name`,
            aght.priority,
             
            tas.`trust_account_software_id`,
            tas.`tsa_name`
        ";
        $custom_where = "a.`trust_account_software` > 0";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'country_id' => $country_id,
            'trust_account_software' => $tas_filter,
            'agency_name' => $agency,
            'limit' => $per_page,
            'offset' => $offset,
            'join_table' => array('trust_account_software', 'agency_priority'),
            'a_status' => 'active',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0,
        );
        $data['lists'] = $this->agency_model->get_agency($params);
        //echo $this->db->last_query();

        //all rows
        $params = array(
            'sel_query' => "COUNT(a.`agency_id`) as a_count",
            'custom_where' => $custom_where,
            'country_id' => $country_id,
            'trust_account_software' => $tas_filter,
            'agency_name' => $agency,
            'a_status' => 'active',
            'join_table' => array('trust_account_software')
        );
        $query = $this->agency_model->get_agency($params);
        $total_rows = $query->row()->a_count;


        // Trust Account Software Filter
        $params = array(
            'sel_query' => "
                tas.`trust_account_software_id`,
                tas.`tsa_name`
            ",
            'custom_where' => $custom_where,
            'country_id' => $country_id,
            'a_status' => 'active',
            'sort_list' => array(
                array(
                    'order_by' => 'tas.`tsa_name`',
                    'sort' => 'ASC'
                )
            ),
            'join_table' => array('trust_account_software'),
            'group_by' => 'tas.`tsa_name`'
        );
        $data['tas_sql'] = $this->agency_model->get_agency($params);


        //base url params
        $pagi_links_params_arr = array(
            'agency_filter' => $agency,
            'tas_filter' => $tas_filter
        );
        $pagi_link_params = $data['uri'] . '/?' . http_build_query($pagi_links_params_arr);

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
        $data['country_id'] =  $country_id;


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view($data['uri'], $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function servicedue() {

        $state = $this->input->get_post('state');
        $sales_rep = $this->input->get_post('sales_rep');
        $region = $this->input->get_post('region');
        $search = $this->input->get_post('search');


        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get agency    
        $group_by = "a.agency_id";
        $params = array(
            'sel_query' => 'count(j.`id`) AS jcount, a.agency_id, a.agency_name, sa.FirstName, sa.LastName, aght.priority',
            'a_region' => $region,
            'state' => $state,
            'salesrep' => $sales_rep,
            'search' => $search,
            'limit' => $per_page,
            'offset' => $offset,
            'group_by' => $group_by,
            'sort_list' => array(
                array(
                    'order_by' => 'jcount',
                    'sort' => 'DESC'
                )
            )
        );
        $sql_query = $this->agency_model->get_agency_service_due($params);

        if ($this->input->get_post('export') == 1) { //EXPORT
            // file name
            $filename = "service_due_" . date("d/m/Y") . ".csv";

            // send headers for download
            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename={$filename}");
            header("Pragma: no-cache");

            // headers
            $str = "Agency,Sales Rep, Service Due\n";

            $a_sql = $sql_query;

            foreach ($a_sql->result_array() as $a) {
                $str .= "{$a['agency_name']},{$a['FirstName']} {$a['LastName']}," . $a['jcount'] . "\n";
            }

            echo $str;
        } else { // LIST VIEW
            $data['lists'] = $sql_query;

            // total rows
            $sel_query_total = "COUNT(DISTINCT(a.`agency_id`)) as a_count";
            $params_total = array(
                'sel_query' => $sel_query_total,
                'a_region' => $region,
                'state' => $state,
                'salesrep' => $sales_rep,
                'search' => $search
            );
            $query = $this->agency_model->get_agency_service_due($params_total);
            $total_rows = $query->row()->a_count;

            //state filter
            $state_filter_params = array(
                'sel_query' => "a.state",
                'group_by' => $group_by,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.state',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['state_filter'] = $this->agency_model->get_agency_service_due($state_filter_params);

            //sales rep filter
            $salesrep_params = array(
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName",
                'group_by' => $group_by,
                'sort_list' => array(
                    array(
                        'order_by' => 'sa.FirstName',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['salesrep_filter'] = $this->agency_model->get_agency_service_due($salesrep_params);

            //region filter dropdown
            $region_params = array(
                'sel_query' => "ar.agency_region_id, ar.agency_region_name",
                'group_by' => $group_by,
                'sort_list' => array(
                    array(
                        'order_by' => 'ar.agency_region_name',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['region_filter'] = $this->agency_model->get_agency_service_due($region_params);

            // pagination settings
            $pagi_links_params_arr = array(
                'state' => $sales_rep,
                'sales_rep' => $state,
                'region' => $region,
                'search' => $search
            );
            $pagi_link_params = '/agency/servicedue/?' . http_build_query($pagi_links_params_arr);

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

            $data['start_load_time'] = microtime(true);
            $data['title'] = "Service Due";
            $this->load->view('templates/inner_header', $data);
            $this->load->view('/agency/servicedue', $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }

    public function agency_audits(){

        $submitted_by_filter = $this->input->get_post('submitted_by_filter');
        $status_filter = (empty($this->input->get_post('status_filter'))) ? 1 : $this->input->get_post('status_filter') ;
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        $uri = '/agency/agency_audits';

        $sel_query = "
            ad.`agency_audit_id`,
            ad.`date_created` AS ad_date_created,
            ad.`submitted_by`,
            ad.`comments` AS ad_comments,
            ad.`status` AS ad_status,
            ad.`completion_date`,
        
            a.`agency_id`,
            a.`agency_name`,
            
            sb.`StaffID` AS sb_staff_id,
            sb.`FirstName` AS sb_FirstName,
            sb.`LastName` AS sb_LastName,
            
            at.`StaffID` AS at_staff_id,
            at.`FirstName` AS at_FirstName,
            at.`LastName` AS at_LastName
        ";
        $params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'status' => $status_filter,
            'submitted_by' => $submitted_by_filter,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'ad.date_created',
                    'sort' => 'DESC'
                )
            ),
            'display_query' => 0
        );
        $data['lists'] = $this->agency_model->getAgencyAudits($params);

        //total rows
        $total_rows_params = array(
            'sel_query' =>"COUNT(ad.agency_audit_id) as count",
            'active' => 1,
            'status' => $status_filter,
            'submitted_by' => $submitted_by_filter
        );
        $tot_row_query = $this->agency_model->getAgencyAudits($total_rows_params);
        $total_rows = $tot_row_query->row()->count;

        //Submitted By filter
        $submitted_by_params = array(
            'sel_query' => "DISTINCT(ad.submitted_by) as ad_submitted_by, sb.FirstName as sb_FirstName, sb.LastName as sb_LastName",
            'active' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'sb.FirstName',
                    'sort' => 'ASC'
                )
            )
        );
        $data['submitted_by_filter'] = $this->agency_model->getAgencyAudits($submitted_by_params);

        //status filter
        $status_by_params = array(
            'sel_query' => "DISTINCT(ad.status)",
            'active' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'sb.FirstName',
                    'sort' => 'ASC'
                )
            )
        );
        $data['status_filter'] = $this->agency_model->getAgencyAudits($status_by_params);

         //get all agency list
         $agency_params = array(
            'sel_query' => 'a.agency_id, a.agency_name',
            'country_id' => $this->config->item('country'),
            'a_status' => 'active',
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC'
                )
            )
        );
        $data['agency_list'] = $this->agency_model->get_agency($agency_params);

        //get all staff list
        $staff_params = array(
            'sel_query' => 'sa.StaffID, sa.FirstName, sa.LastName',
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                )
            )
        );
        $data['staff_list'] = $this->gherxlib->getStaffInfo($staff_params);

        // pagination settings
        $pagi_links_params_arr = array(
            'status' => $status_filter,
            'submitted_by' => $submitted_by_filter
        );
        $pagi_link_params = $uri . '/?' . http_build_query($pagi_links_params_arr);

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

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Audits";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('/agency/agency_audits', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    //delete agency audit via ajax
    public function ajax_delete_agency_audit(){
        $jsondata['status'] = false;
        $au_id = $this->input->post('au_id');

        if($au_id && !empty($au_id) && is_numeric($au_id)){
            $sql = "
                UPDATE `agency_audits`
                SET 
                    `active` = 0
                WHERE `agency_audit_id` = {$au_id}
            ";
            $this->db->query($sql);
            $jsondata['status'] = true;
        }

        echo json_encode($jsondata);
    }

    //delete agency audit via ajax
    public function update_price_variation(){
    
        $agency_id = $this->input->post('agency_id');
        $apv_discount_amount = $this->input->post('apv_discount_amount');
        $apv_discount_reason = $this->input->post('apv_discount_reason');
        $apv_surcharge_amount = $this->input->post('apv_surcharge_amount');
        $apv_surcharge_reason = $this->input->post('apv_surcharge_reason');

        $date = date('Y-m-d H:i:s');

        // agency price variation - discount
        $apv_sql = $this->db->query("
        SELECT COUNT(apv.`id`) AS apv_count
        FROM `agency_price_variation` AS apv
        LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`price_variation_reason` = apvr.`id`
        WHERE apv.`agency_id` = {$agency_id}                    
        AND apv.`active` = 1
        AND apvr.`is_discount` = 1
        ");
        $apv_row = $apv_sql->row();

        if( $apv_row->apv_count > 0 ){ // it exist, update

            $this->db->query("
            UPDATE `agency_price_variation` AS apv
            LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`price_variation_reason` = apvr.`id`
            SET 
                apv.`price_variation_amount` = {$apv_discount_amount},
                apv.`price_variation_reason` = '{$apv_discount_reason}',
                apv.`updated_date` = '{$date}'
            WHERE apv.`agency_id` = {$agency_id}                                        
            AND apvr.`is_discount` = 1
            AND apv.`active` = 1
            ");

        }else{ // insert

            if( $apv_discount_amount > 0 && $apv_discount_reason > 0 ){
                
                $insert_data = array(
                    'price_variation_amount' => $apv_discount_amount,
                    'price_variation_reason' => $apv_discount_reason,
                    'agency_id' => $agency_id,
                    'created_date' => $date
                );                        
                $this->db->insert('agency_price_variation', $insert_data);

            }                        

        }


        // agency price variation - surcharge
        $apv_sql = $this->db->query("
        SELECT COUNT(apv.`id`) AS apv_count
        FROM `agency_price_variation` AS apv
        LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`price_variation_reason` = apvr.`id`
        WHERE apv.`agency_id` = {$agency_id}                    
        AND apv.`active` = 1
        AND apvr.`is_discount` = 0
        ");
        $apv_row = $apv_sql->row();

        if( $apv_row->apv_count > 0 ){ // it exist, update

            $this->db->query("
            UPDATE `agency_price_variation` AS apv
            LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`price_variation_reason` = apvr.`id`
            SET 
                apv.`price_variation_amount` = {$apv_surcharge_amount},
                apv.`price_variation_reason` = '{$apv_surcharge_reason}',
                apv.`updated_date` = '{$date}'
            WHERE apv.`agency_id` = {$agency_id}                                        
            AND apvr.`is_discount` = 0
            AND apv.`active` = 1
            ");

        }else{ // insert

            if( $apv_surcharge_amount > 0 && $apv_surcharge_reason > 0 ){

                $insert_data = array(
                    'price_variation_amount' => $apv_surcharge_amount,
                    'price_variation_reason' => $apv_surcharge_reason,
                    'agency_id' => $agency_id,
                    'created_date' => $date
                );                        
                $this->db->insert('agency_price_variation', $insert_data);

            }                        

        }
    }

    //add agency audit via ajax
    public function ajax_add_agency_audit(){
        $jsondata['status'] = false;

        $agency_id = $this->input->post('agency_id');
        $comments = $this->input->post('comments');
        $added_by = $this->input->post('added_by');

        //insert query
        $insert_data = array(
            'agency_id' => $agency_id,
            'submitted_by' => $added_by,
            'comments' => $comments,
            'date_created' => date("Y-m-d H:i:s")
        );
        $this->db->insert('agency_audits', $insert_data);
        $this->db->limit(1);
        $jsondata['status'] = true;

        echo json_encode($jsondata);
    }

    //update/edit agency audit via ajax
    public function ajax_update_agency_audit(){
        $jsondata['status'] = false;
        $au_id = $this->input->post('au_id');
        $agency_id = $this->input->post('agency_id');
        $assigned_to = $this->input->post('assigned_to');
        $ad_comments = $this->input->post('ad_comments');
        $ad_status = $this->input->post('ad_status');
        $ad_comp_date = $this->input->post('ad_comp_date');
        $ad_comp_date_formated = (!empty($ad_comp_date)) ? $this->system_model->formatDate($ad_comp_date) : NULL;

        $update_data = array(
            'agency_id' => $agency_id,
            'assigned_to' => $assigned_to,
            'comments' => $ad_comments,
            'status' => $ad_status,
            'completion_date' => $ad_comp_date_formated
        );
        $this->db->where('agency_audit_id', $au_id);
        $this->db->update('agency_audits', $update_data);
        $this->db->limit(1);
        $jsondata['status'] = true;

        echo json_encode($jsondata);
    }

    /**
     * Add new agency form
     */
    public function add_agency(){

        $this->load->model('properties_model');
        $data['uri'] = "/agency/add_agency";

        $data['title'] = "Add Agency";
        $this->load->view('templates/inner_header', $data);
        $this->load->view($data['uri'], $data);
        $this->load->view('templates/inner_footer', $data);

    }

    /**
     * Add new agency code
     */
    public function add_new_agency(){

         //-----FORM SUBMIT
         $this->load->library('form_validation');

        // Agency Status Post
        $agen_stat = $this->input->post('agen_stat');

        // Agency Details Post
        $agency_name = $this->input->post('agency_name');
        $legal_name = $this->input->post('legal_name');
        $franchise_group = $this->input->post('franchise_group');
        $abn = $this->input->post('abn'); 
        $street_number = $this->input->post('street_number'); 
        $street_name = $this->input->post('street_name'); 
        $suburb = $this->input->post('suburb');
        $state = $this->input->post('state');
        $postcode = $this->input->post('postcode');
        $phone = $this->input->post('phone');
        $totprop = $this->input->post('totprop');
        $agency_hours =  $this->input->post('agency_hours');
        $comment = $this->input->post('comment');
        $agency_specific_notes = $this->input->post('agency_specific_notes');
        $website = $this->input->post('website');
        $agency_using = $this->input->post('agency_using');
        $agency_special_deal = $this->input->post('agency_special_deal');
        
        // Agency Contact Post
        $ac_fname = $this->input->post('ac_fname');
        $ac_lname = $this->input->post('ac_lname');
        $ac_phone = $this->input->post('ac_phone');
        $ac_email = $this->input->post('ac_email');
        $acc_name = $this->input->post('acc_name');
        $acc_phone = $this->input->post('acc_phone');

        // Agency Email
        $agency_emails = $this->input->post('agency_emails');
        $account_emails = $this->input->post('account_emails');
        
        // Preferences Post
        $allow_indiv_pm_email_cc = $this->input->post('allow_indiv_pm_email_cc');
        $allow_en = $this->input->post('allow_en');
        $new_job_email_to_agent = $this->input->post('new_job_email_to_agent');
        $allow_upfront_billing =  $this->input->post('allow_upfront_billing');

        if($state == 'QLD' && $this->config->item('country') == 1){
            $allow_upfront_billing = 1;
        }
        
        // Sales Rep Post
        $salesrep = $this->input->post('salesrep');

        // Maintenance Program Post
        $maintenance = $this->input->post('maintenance');
        $m_surcharge = $this->input->post('m_surcharge');
        $m_price = $this->input->post('m_price');
        $m_disp_surcharge = $this->input->post('m_disp_surcharge');
        $m_surcharge_msg = $this->input->post('m_surcharge_msg');

        // Agency alarms Post
        $alarm_pwr_id = $this->input->post('alarm_pwr_id');
        $alarm_is_approved = $this->input->post('alarm_is_approved');
        $alarm_price = $this->input->post('alarm_price');

        //Add services posts
        $service_id = $this->input->post('service_id');
        $service_is_approved = $this->input->post('service_is_approved');
        $service_price = $this->input->post('service_price');
        
        // Google lat/lng
        $country_name = ($this->config->item('country')==1) ? 'Australia' : 'New Zealand';
        $address = "{$street_number} {$street_name} {$suburb} {$state} {$postcode}, {$country_name}";
        $coordinate = $this->system_model->getGoogleMapCoordinates($address);

        // Form validation
        if($agen_stat == 'active'){
            $this->form_validation->set_rules('agency_name', 'Agency Name', 'required');
            $this->form_validation->set_rules('franchise_group', 'Franchise Group', 'required');
            $this->form_validation->set_rules('state', 'State', 'required');
            $this->form_validation->set_rules('postcode', 'Postcode', 'required');
            $this->form_validation->set_rules('agency_emails', 'Agency Emails', 'required');
            $this->form_validation->set_rules('account_emails', 'Accounts Emails', 'required');
            $this->form_validation->set_rules('salesrep', 'Sales rep', 'required');
            $this->form_validation->set_rules('alarm_approve[]', 'Alarm', 'required');
            $this->form_validation->set_rules('service_approve[]', 'Service', 'required');
        }else{
            $this->form_validation->set_rules('agency_name', 'Agency Name', 'required');
            $this->form_validation->set_rules('franchise_group', 'Franchise Group', 'required');
            $this->form_validation->set_rules('state', 'State', 'required');
            $this->form_validation->set_rules('postcode', 'Postcode', 'required');
            $this->form_validation->set_rules('salesrep', 'Sales rep', 'required');
        }
        
        if ($this->form_validation->run() == true) {

            if($agen_stat=='active'){ // Add Active Agency (add agency,maintenance,send email)
                // get sub region via postcode
                $pcr_sql = $this->agency_model->getRegionViaPostCode($postcode)->row_array();
                $pcr_id = $pcr_sql['postcode_region_id'];

                // Add agency
                $agency_data = array(
                    'agency_name' => $agency_name,
                    'franchise_groups_id' =>  $franchise_group,
                    'address_1' => $street_number,
                    'address_2' => $street_name,
                    'address_3' => $suburb,
                    'phone' => $phone,
                    'state' => $state,
                    'postcode' => $postcode,
                    'lat' => $coordinate['lat'],
                    'lng' => $coordinate['lng'],
                    'postcode_region_id' => $pcr_id,
                    'tot_properties' => $totprop,
                    'agency_hours' => $agency_hours,
                    'comment' => $comment,
                    'status' => $agen_stat,
                    'contact_first_name' => $ac_fname,
                    'contact_last_name' => $ac_lname,
                    'contact_phone' => $ac_phone,
                    'contact_email' => $ac_email,
                    'agency_emails' => $agency_emails,
                    'account_emails' => $account_emails,                    
                    'send_combined_invoice' => 1,
                    'send_entry_notice' => 1,
                    'require_work_order' => 0,
                    'allow_indiv_pm' => 1,
                    'allow_indiv_pm_email_cc' => $allow_indiv_pm_email_cc,
                    'salesrep' => $salesrep,
                    'pass_timestamp' => date('Y-m-d H:i:s'),
                    'tot_prop_timestamp' => date('Y-m-d H:i:s'),
                    'agency_using_id' => $agency_using,
                    'legal_name' => $legal_name,
                    'country_id' => $this->config->item('country'),
                    'auto_renew' => 1,
                    'key_allowed' => 1,
                    'key_email_req' => 0,
                    'abn' => $abn,
                    'accounts_name' => $acc_name,
                    'accounts_phone' => $acc_phone,
                    'allow_dk' => 1,
                    'website' => $website,
                    'allow_en' => $allow_en,
                    'agency_specific_notes' => $agency_specific_notes,
                    'new_job_email_to_agent' => $new_job_email_to_agent,
                    'display_bpay' => 0,
                    'allow_upfront_billing' => $allow_upfront_billing,
                    'invoice_pm_only' => 0,
                    'electrician_only' => 0,
                    'agency_special_deal' => $agency_special_deal,
                    'joined_sats' => date('Y-m-d')
                );
                $this->agency_model->add_agency_data($agency_data);
                $agency_last_id = $this->db->insert_id();
                // Add agency end

                // Add maintenance
                $maintenance_data = array(
                    'agency_id' => $agency_last_id,
                    'maintenance_id' => $maintenance,
                    'price' => $m_price,
                    'surcharge' => $m_surcharge,
                    'display_surcharge' => $m_disp_surcharge,
                    'surcharge_msg' => $m_surcharge_msg,
                    'status' => 1
                );
                $this->agency_model->add_agency_maintenance($maintenance_data);
                // Add maintenance end

                // Send email
                $email_params = array(
                    'agency_name' => $agency_name,
                    'legal_name' => $legal_name,
                    'abn_number' => $abn,
                    'street_number' => $street_number,
                    'street_name' => $street_name,
                    'suburb' => $suburb,
                    'state' => $state,
                    'postcode' => $postcode,
                    'phone' => $phone,
                    'tot_properties' => $totprop,
                    'ac_fname' => $ac_fname,
                    'ac_lname' => $ac_lname,
                    'ac_phone' => $ac_phone,
                    'ac_email' => $ac_email,
                    'acc_name' => $acc_name,
                    'acc_phone' => $acc_phone,
                    'agency_emails' => $agency_emails,
                    'account_emails' => $account_emails,
                    'salesrep' => $salesrep
                );
                $this->agency_send_mail($email_params);
                // Send email end                

               //redirect(base_url('/agency/add_agency'));

            }else{ // Add Target Agency
                $agency_data = array(
                    'agency_name' => $agency_name,
                    'franchise_groups_id' =>  $franchise_group,
                    'address_1' => $street_number,
                    'address_2' => $street_name,
                    'address_3' => $suburb,
                    'phone' => $phone,
                    'state' => $state,
                    'postcode' => $postcode,
                    'lat' => $coordinate['lat'],
                    'lng' => $coordinate['lng'],
                    'postcode_region_id' => $pcr_id,
                    'tot_properties' => $totprop,
                    'agency_hours' => $agency_hours,
                    'comment' => $comment,
                    'status' => $agen_stat,
                    'contact_first_name' => $ac_fname,
                    'contact_last_name' => $ac_lname,
                    'contact_phone' => $ac_phone,
                    'contact_email' => $ac_email,
                    'agency_emails' => $agency_emails,
                    'salesrep' => $salesrep,
                    'tot_prop_timestamp' => date('Y-m-d H:i:s'),
                    'agency_using_id' => $agency_using,
                    'country_id' => $this->config->item('country'),
                    'abn' => $abn,
                    'accounts_name' => $acc_name,
                    'accounts_phone' => $acc_phone,
                    'website' => $website,
                    'agency_specific_notes' => $agency_specific_notes,
                    'agency_special_deal' => $agency_special_deal,
                    'allow_upfront_billing' => $allow_upfront_billing
                );
                $this->agency_model->add_agency_data($agency_data);
            }

             //Add alarms
             foreach($alarm_pwr_id as $index=>$val){

                $add_alarm_params = array(
                    'agency_id' => $agency_last_id,
                    'alarm_pwr_id' => $val,
                    'price' => $alarm_price[$index]
                );

                if( in_array( $val, $this->config->item('alarm_allowed_zero_price') ) ){

                    if($alarm_is_approved[$index]==1 ){
                        $this->agency_model->add_agency_alarms($add_alarm_params);
                    }
                    
                }else{

                    if($alarm_is_approved[$index]==1 && $val>0){
                        
                        $this->agency_model->add_agency_alarms($add_alarm_params);

                    }

                }
                
            }
            //Add alarms end

            //Add Services
            foreach($service_id as $index=>$val){
                if($service_is_approved[$index]==1){
                    $add_services_params = array(
                        'agency_id' => $agency_last_id,
                        'service_id' => $val
                        //'price' => $service_price[$index] ##Disabled as per Ben's request (regarding on price variation changes)
                    );
                    $this->agency_model->add_agency_services($add_services_params);
                }
            }
            //Add Services end

            //insert log
            $log_details = "Agency added as {$agen_stat} agency";
            $log_params = array(
                'title' => 71,  //New Agency Added
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_last_id
            );
            $this->system_model->insert_log($log_params);

            //set session success message
            $success_message = "New agency has been succesfully added";
			$this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
			redirect(base_url('/agency/add_agency'),'refresh');
        }else{
            $error_msg = "Form Error: Please contact admin";
			$this->session->set_flashdata(array('error_msg'=>$error_msg,'status'=>'error'));
			redirect(base_url('/agency/add_agency'),'refresh');
        }


       //-----FORM SUBMIT END

    }

    public function ajax_getRegionViaPostCode(){
        $postcode = $this->input->post('postcode');

        /* OLD table 
        $sql = $this->db->query("
            SELECT * 
            FROM  `postcode_regions`
            WHERE `postcode_region_postcodes` LIKE '%{$postcode}%'
            AND `country_id` = {$this->config->item('country')}
            AND `deleted` = 0
        ");*/

        $this->db->select('*, sr.subregion_name as postcode_region_name, sr.sub_region_id as postcode_region_id');
        $this->db->from('postcode as pc');
        $this->db->join('sub_regions as sr','sr.sub_region_id = pc.sub_region_id','left');
        $this->db->where('pc.postcode', $postcode);
        $this->db->where('pc.deleted',0);
        $sql = $this->db->get();

        $row = $sql->result_array();
        echo json_encode($row);
    }

    public function agency_send_mail($params){

        $this->load->library('email');

        $staff_params['staff_id'] = $this->session->staff_id;
        $staff_row = $this->gherxlib->getStaffInfo($staff_params)->row_array();
        $getCountryInfo = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));

        //get salesrep
        $salesrep_params['staff_id'] = $params['salesrep'];
        $sales_rep_row = $this->gherxlib->getStaffInfo($salesrep_params)->row_array();

        //Email datas
        $email_data['staff']            = $staff_row['FirstName']." ".$staff_row['LastName'];
        $email_data['agency_name']      = $params['agency_name'];
        $email_data['legal_name']       = $params['legal_name'];
        $email_data['abn_number']       = $params['abn_number'];
        $email_data['street_number']    = $params['street_number'];
        $email_data['street_name']      = $params['street_name'];
        $email_data['suburb']           = $params['suburb'];
        $email_data['state']            = $params['state'];
        $email_data['postcode']         = $params['postcode'];
        $email_data['phone']            = $params['phone'];
        $email_data['tot_properties']   = $params['tot_properties'];
        $email_data['ac_fname']         = $params['ac_fname'];
        $email_data['ac_lname']         = $params['ac_lname'];
        $email_data['ac_phone']         = $params['ac_phone'];
        $email_data['ac_email']         = $params['ac_email'];
        $email_data['acc_name']         = $params['acc_name'];
        $email_data['acc_phone']        = $params['acc_phone'];
        $email_data['agency_emails']    = $params['agency_emails'];
        $email_data['account_emails']   = $params['account_emails'];
        $email_data['salesrep']         = $sales_rep_row['FirstName']." ".$sales_rep_row['LastName'];
        $email_data['status']           = $params['status'];
        $email_data['old_status']       = $params['old_status'];
        $email_data['agency_id']        = $params['agency_id'];

        $email_from = $getCountryInfo->outgoing_email;
        $email_to = $this->config->item('sats_accounts_email');
        $email_subject = "New Agency Added";
        
        if (!empty($email_data['agency_id'])) {
            $email_subject_status = ucfirst($email_data['old_status']);
            $email_subject = "{$email_subject_status} Agency Activated";
        } else {
            $email_subject = "New Agency Added";
        }

        //email config
        $config = Array(
            'mailtype' => 'html',
            'charset' => 'iso-8859-1'
        );
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");

        $this->email->from($email_from, 'CRM');
        $this->email->to($email_to);
        $this->email->cc($this->config->item('sats_sales_email'));      
        $this->email->bcc($this->config->item('sats_info_email'));  
        $this->email->subject($email_subject);
        $body = $this->load->view('emails/add_agency_email.php', $email_data, TRUE);
        $this->email->message($body);
        $this->email->send();

    }

    public function ajax_check_agency_duplicate(){

        $jdata['status'] = false;
        $agency_name = $this->input->post('agency_name');
        $agency_name_esc = $this->db->escape_str($agency_name);
        $where = " TRIM(LCASE(agency_name)) = LCASE('" . $agency_name_esc . "') ";

        $this->db->select('agency_id,agency_name');
        $this->db->from('agency');
        $this->db->where($where);
        $q = $this->db->get();

        if($q->num_rows()>0){
            $jdata['status'] = true;
        }else{
            $jdata['status'] = false;
        }

        echo json_encode($jdata);

    }

    
    public function view_agencies(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "View Agencies";
        $country_id = $this->config->item('country');

        $agency_status = 'active';
        $state_filter = $this->input->get_post('state_filter');
        $sales_rep_filter = $this->input->get_post('sales_rep_filter');
        $search_filter = $this->input->get_post('search_filter');
        $agency_ht_filter = $this->input->get_post('agency_ht_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if (!empty($sub_region_ms)) {
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }

        //agency high touch filter
        $custom_where = "aght.priority = ({$agency_ht_filter})";


        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        a.contact_first_name, 
        a.contact_last_name,
        a.phone, a.abn, 
        a.agency_name as a_name, 
        a.address_1, a.address_2, 
        a.address_3, a.state, 
        a.postcode, a.status, 
        a.agency_id as a_id, 
        a.tot_properties,
        a.legal_name,
        a.account_emails, 
        a.agency_emails, 
        a.contact_phone,
        a.contact_email,
        a.tot_properties,
        a.country_id,
        a.send_emails,
        a.send_combined_invoice,
        a.send_entry_notice,
        a.require_work_order,
        a.auto_renew,
        a.key_allowed,
        a.key_email_req,
        a.trust_account_software,
        aght.`priority`,
        apmd.`abbreviation`,

        sa.FirstName, 
        a.joined_sats,
        sa.LastName,
        sr.subregion_name as postcode_region_name";

        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('postcode_regions', 'salesrep', 'country', 'agency_priority', 'agency_priority_marker_definition'),
            // 'custom_where' => (!empty($agency_ht_filter) ? $custom_where : ""),
            'country_id' => $country_id,
            'a_status' => $agency_status,
            'state' => $state_filter,
            'salesrep' => $sales_rep_filter,
            'agency_name' => $search_filter,
            'postcodes' => $postcodes,
            'high_touch_filter' => $agency_ht_filter,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );

        if ($this->input->get_post('export') && $this->input->get_post('export') == 1) { //EXPORT
            
            if( $this->input->get_post('export_type') == 'a' ){ //Normal export
               
                $this->get_agency_normal_export($params);

            }else if($this->input->get_post('export_type') == 'b'){ //Mailer export
                unset($params['a_status']); // remove agency status filter to show all agencies regardless of status
                $this->get_agency_mailer_export($params);

            }
        }else{ // Normal Listing

             //add offset/perpage params for normal listing
             $params['limit'] = $per_page;
             $params['offset'] = $offset;

             $agencies = $this->agency_model->get_agency($params)->result_array();
             $data['sql_query'] = $this->db->last_query();

             $agenciesById = []; // make an object/map so you can access them by id easier later
     
             $agenciesPerRegion = []; // map agencies per region
     
             for ($x = 0; $x < count($agencies); $x++) {
                 $agency = &$agencies[$x];
                
                 $agency['active_prop'] = null;
                 $agency['region'] = null; // empty for now
                 $agency['ajt_counts'] = []; // will be used later
                 $agency['last_contact'] = [];
                 $agenciesById[$agency['a_id']] = &$agency; // take note with the &. reference the id to the object
     
                 #generate an empty array for later. there could be an unsafe shortcut for this
                 if (!isset($agenciesPerRegion[$agency['postcode']])) {
                     $agenciesPerRegion[$agency['postcode']] = [];
                 }
     
                 $agenciesPerRegion[$agency['postcode']][] = &$agency; // add a reference of agency to the region
             }
     
             # get all agency IDs from the result. you can loop through $agencies or do it like this:
             $agencyIds = array_keys($agenciesById);
     
             $regionCodes = array_keys($agenciesPerRegion); // get postcodes
             # make a function in a model or something that will query for the regions with the given postcodes. use WHERE IN statement to use a single query

            if(!empty($agenciesPerRegion)){
                $regions =  $this->system_model->getRegion_v2($regionCodes)->result_array();
            }
            
             foreach ($regions as $region) {
     
                 # reference the agencies in region.
                 # foreach can be buggy so you may have to use normal for loop
                 /*
                 foreach ($agenciesPerRegion[$region['postcode']] as &$agencyInRegion) {
                     $agenciesInRegion['region'] = $region;
                 }
                 */
                 for ($x = 0; $x < count($agenciesPerRegion[$region['postcode']]); $x++) {
                   $agenciesPerRegion[$region['postcode']][$x]['region'] = $region;
                 }
                 
             }

            ## get total active properties
            if(!empty($agencyIds)){
                $imp_agency_id = implode(', ',$agencyIds);
                $active_prop_custom_where = "a.agency_id IN ({$imp_agency_id}) AND ps.`service` = 1";
                $active_prop_params = array(
                    'sel_query' => "COUNT(p.property_id) as p_count, a.agency_id",
                    'custom_where' => $active_prop_custom_where,
                    'join_table' => array('property_services'),
                    'p_deleted' => 0,
                    'country_id' => COUNTRY,
                    'group_by' => "a.agency_id"
                );
                $active_prop_count_q = $this->properties_model->get_properties($active_prop_params)->result_array();
                foreach($active_prop_count_q as $active_prop_count_q_row){
                    $agenciesById[$active_prop_count_q_row['agency_id']]['active_prop'] = $active_prop_count_q_row['p_count'];
                }

                ##get last contact
                $last_contact = $this->db->select('agency_id, MAX(created_date) as last_contact')
                ->from('logs')
                ->where_in('agency_id', $agencyIds)
                ->where('display_in_vad', 1)
                ->group_by('agency_id')
                ->get()->result_array();

                foreach ($last_contact as $d) {
                    $agenciesById[$d['agency_id']]['last_contact'] = $d['last_contact'];
                }  
                 ##get last contact end 
            }
            ## get total active properties end
              
            # use these 2 variables in the view
            $data['agencies'] = $agencies;

             ## Header Filters
             // state filter
             $sel_query = "DISTINCT(a.`state`),
             a.`state`";
             $params = array(
                 'sel_query' => $sel_query,
                 'join_table' => array('postcode_regions', 'salesrep', 'country'),
                 'country_id' => $country_id,
                 'sort_list' => array(
                     array(
                         'order_by' => 'a.`state`',
                         'sort' => 'ASC',
                     )
                 ),
             );
             $data['state_filter_json'] = json_encode($params);
     
             /*
             //salesrep filter
             $data['salesrep'] = $this->agency_model->getAgencySalesRep('active');
             */

            $salesrep_sql_str = "
            SELECT DISTINCT(sa.`StaffID`), sa.`FirstName`, sa.`LastName`
            FROM `agency` AS a            
            INNER JOIN `staff_accounts` AS sa ON a.`salesrep` = sa.`StaffID`           
            WHERE a.`status` = 'active'
            ORDER BY sa.`FirstName`, sa.`LastName` ASC
            ";

            $data['salesrep'] = $this->db->query($salesrep_sql_str);
                 
             // Region Filter ( get distinct state )
             $sel_query = "DISTINCT(a.`state`),a.`state`";
             $region_filter_arr = array(
                 'sel_query' => $sel_query,
                 'join_table' => array('postcode_regions', 'salesrep', 'country'),
                 'country_id' => $country_id,
                 'a_status' => $agency_status,
                 'sort_list' => array(
                     array(
                         'order_by' => 'a.`state`',
                         'sort' => 'ASC',
                     )
                 ),
             );
             $data['region_filter_json'] = json_encode($region_filter_arr);
     
             ## Pagination Start
             //get total rows
             $params_total = array(
                 'sel_query' => "COUNT(a.`agency_id`) as a_count",
                 'join_table' => array('postcode_regions', 'salesrep', 'country'),
                 'country_id' => $country_id,
                 'a_status' => $agency_status,
                 'state' => $state_filter,
                 'salesrep' => $sales_rep_filter,
                 'agency_name' => $search_filter,
                 'postcodes' => $postcodes
             );
             $total_rows = $this->agency_model->get_agency($params_total)->row()->a_count;
             
             //base url params
             $pagi_links_params_arr = array(
                 'state_filter' => $state_filter,
                 'sales_rep_filter' => $sales_rep_filter,
                 'sub_region_ms' => $sub_region_ms,
                 'search_filter' => $search_filter
             );
             $pagi_link_params = '/agency/view_agencies/?' . http_build_query($pagi_links_params_arr);
     
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
             ## Pagination End
     
             //load views
             $this->load->view('templates/inner_header', $data);
             $this->load->view('agency/view_agencies', $data);
             $this->load->view('templates/inner_footer', $data);
            
        }

    }

    //Agency Normal Export
    public function get_agency_normal_export($params){

        // $lists_export_query = $this->agency_model->get_agency($params);
        $lists_export_query = $this->agency_model->get_agency_export($params)->result_array();

        $agenciesById = [];

        for ($x = 0; $x < count($lists_export_query); $x++){
            $agency = &$lists_export_query[$x];

            $agency['last_contact'] = null;
            $agency['active_prop'] = null;
            $agenciesById[$agency['a_id']] = &$agency;
        }

        $agencyIds = array_keys($agenciesById);

        if(!empty($agencyIds)){

             ##get last contact
             $last_contact = $this->db->select('agency_id, MAX(created_date) as last_contact')
             ->from('logs')
             ->where_in('agency_id', $agencyIds)
             ->where('display_in_vad', 1)
             ->group_by('agency_id')
             ->get()->result_array();

             foreach ($last_contact as $d) {
                 $agenciesById[$d['agency_id']]['last_contact'] = $d['last_contact'];
             }  

            
            ##get active prop
            $imp_agency_id = implode(', ',$agencyIds);
            $active_prop_custom_where = "a.agency_id IN ({$imp_agency_id}) AND ps.`service` = 1";
            $active_prop_params = array(
                'sel_query' => "COUNT(p.property_id) as p_count, a.agency_id",
                'custom_where' => $active_prop_custom_where,
                'join_table' => array('property_services'),
                'p_deleted' => 0,
                'country_id' => COUNTRY,
                'group_by' => "a.agency_id"
            );
            $active_prop_count_q = $this->properties_model->get_properties($active_prop_params)->result_array();
            foreach($active_prop_count_q as $active_prop_count_q_row){
                $agenciesById[$active_prop_count_q_row['agency_id']]['active_prop'] = $active_prop_count_q_row['p_count'];
            }

        }

        // get services
        $serv_type_str = "";
        $ajt_sql = $this->agency_model->getActiveServices();
        foreach($ajt_sql->result_array() as $ajt){
            $serv_type_str .= ',"'.$ajt['type'].'","Price"';
        }
        
        // get services
        $alarm_pwr_str_hdr = "";
        $alarm_pwr_sql = $this->agency_model->getAlarmPower();
        foreach($alarm_pwr_sql->result_array() as $alarm_pwr){
            $alarm_pwr_str_hdr .= ',"'.$alarm_pwr['alarm_pwr'].'"';
        }

        $filename = "Agencies_".date("d/m/Y").".csv";
        header("Content-Type: text/csv");
        header("Content-Disposition: Attachment; filename=$filename");
        header("Pragma: no-cache");
        echo "Agency Name,Legal Name,Address,Suburb,Postcode,State,Region,Phone,Accounts Email,Agency Email,Agency Contact,Contact Phone,Contact Email,Sales Rep,Active Properties,Properties,Last Contact{$serv_type_str}{$alarm_pwr_str_hdr},Franchise Group,Country,Email Certificates?,Combined Cert / Invoice PDF?,Send Entry Notice?,Work Order Required?,Auto Renew?,Key Access Allowed?,Tenant Key Email Required?,Trust Acc,Activated Date\n";
        
        foreach ($lists_export_query as $list_item) {

            $serv_count_str = "";
            $alarm_pwr_str = "";

            //latest log
            //$log_sql = $this->db->select('*')->from('logs')->where('agency_id', $list_item['a_id'])->order_by('created_date','desc')->limit(1)->get();
            //$log_sql = $this->db->select('*')->from('agency_event_log')->where('agency_id', $row['a_id'])->order_by('eventdate','desc')->limit(1)->get(); //Gherx: disabled > updated from old to new table 'logs'
           /* $where_tt = array(
                'agency_id' => $list_item['a_id'],
                'display_in_vad' => 1
            );
            $log_sql = $this->db->select('*')->from('logs')->where($where_tt)->order_by('created_date','desc')->limit(1)->get();
            $log_row = $log_sql->row_array();
            */

            //get all active alarm
            $ajt_sql = $this->agency_model->getActiveServices();
            foreach($ajt_sql->result_array() as $ajt){ 	
                $agency_service_price = $this->agency_model->getAgencyServicePrice($list_item['a_id'],$ajt['id']);
                $serv_count_str .= ",\"".$this->system_model->getServiceCount($list_item['a_id'],$ajt['id'])."\",\"".( ($agency_service_price>0)?"$".number_format($agency_service_price,2):'' )."\"";
            }

            // get agency alarms price
			$alarm_pwr_sql = $this->agency_model->getAlarmPower();
			foreach($alarm_pwr_sql->result_array() as $alarm_pwr){ 	
				$alarm_price = $this->agency_model->getAgencyAlarmsPrice($list_item['a_id'],$alarm_pwr['alarm_pwr_id']);
				$alarm_pwr_str .= ",\"".( ($alarm_price>0)?"$".number_format($alarm_price,2):'' )."\"";
            }
            
            // trust account software
            $tas_sql = $this->db->select('*')->from('trust_account_software')->where('trust_account_software_id',$list_item['trust_account_software'])->get();
            $tsa_row = $tas_sql->row_array();

            $agency_name = $list_item['a_name'];
            $sales_rep = $this->system_model->formatStaffName($list_item['FirstName'], $list_item['LastName']);

            ##get total of active properties by agency_id
           /* $active_prop_params = array(
                'sel_query' => 'p.property_id',
                'agency_filter' => $list_item['a_id'],
                'p_deleted' => 0,
                'country_id' => COUNTRY
            );
            $active_prop_count_q = $this->properties_model->get_properties($active_prop_params);
            $active_prop = $active_prop_count_q->num_rows();
            */

           // echo "\"".trim($agency_name)."\",\"".trim($list_item['legal_name'])."\",\"".trim($list_item['address_1'])." ".trim($list_item['address_2'])."\",\"".trim($list_item['address_3'])."\",\"{$list_item['postcode']}\",\"{$list_item['state']}\",\"".trim($list_item['postcode_region_name'])."\",\"{$list_item['phone']}\",\"".trim($list_item['account_emails'])."\",\"".trim($list_item['agency_emails'])."\",\"{$sales_rep}\",\"{$list_item['contact_phone']}\",\"{$list_item['contact_email']}\",\"{$list_item['FirstName']} {$list_item['LastName']}\",\"{$active_prop}\",\"{$list_item['tot_properties']}\",\"{$log_row['eventdate']}\"".$serv_count_str.$alarm_pwr_str.",\"".trim($list_item['name'])."\",\"".trim($row['country'])."\",".(($list_item['send_emails'])?'Yes':'No').",".(($list_item['send_combined_invoice'])?'Yes':'No').",".(($list_item['send_entry_notice'])?'Yes':'No').",".(($list_item['require_work_order'])?'Yes':'No').",".(($list_item['auto_renew'])?'Yes':'No').",".(($list_item['key_allowed'])?'Yes':'No').",".(($list_item['key_email_req'])?'Yes':'No').",\"{$tsa_row['tsa_name']}\",\"{$tsa_row['joined_sats']}\"\n"; ## Gherx: disabled > agency_event_log eventdate to created_by from new table log
            echo "\"".trim($agency_name)."\",\"".trim($list_item['legal_name'])."\",\"".trim($list_item['address_1'])." ".trim($list_item['address_2'])."\",\"".trim($list_item['address_3'])."\",\"{$list_item['postcode']}\",\"{$list_item['state']}\",\"".trim($list_item['postcode_region_name'])."\",\"{$list_item['phone']}\",\"".trim($list_item['account_emails'])."\",\"".trim($list_item['agency_emails'])."\",\"{$list_item['contact_first_name']} {$list_item['contact_last_name']}\",\"{$list_item['contact_phone']}\",\"{$list_item['contact_email']}\",\"{$list_item['FirstName']} {$list_item['LastName']}\",\"{$list_item['active_prop']}\",\"{$list_item['tot_properties']}\",\"{$list_item['last_contact']}\"".$serv_count_str.$alarm_pwr_str.",\"".trim($list_item['name'])."\",\"".trim($row['country'])."\",".(($list_item['send_emails'])?'Yes':'No').",".(($list_item['send_combined_invoice'])?'Yes':'No').",".(($list_item['send_entry_notice'])?'Yes':'No').",".(($list_item['require_work_order'])?'Yes':'No').",".(($list_item['auto_renew'])?'Yes':'No').",".(($list_item['key_allowed'])?'Yes':'No').",".(($list_item['key_email_req'])?'Yes':'No').",\"{$tsa_row['tsa_name']}\",\"{$tsa_row['joined_sats']}\"\n";
			
        }

    }

    //Agency Mailer Export
    public function get_agency_mailer_export($params){
        
        $lists_export_query = $this->agency_model->get_agency($params);

        // filename
        $filename = "Mailer_Agencies_".date("d/m/Y").".csv";
        
        // send headers for download
        header("Content-Type: text/csv");
        header("Content-Disposition: Attachment; filename=$filename");
        header("Pragma: no-cache");

        echo "Agency Name,State,Country,Agency Email,Sales Rep,Franchise Group,Status, Activated Date\n";

        foreach($lists_export_query->result_array() as $row){

            $ae = explode("\n",$row['agency_emails']);

            foreach($ae as $val){
				echo "\"".trim($row['a_name'])."\",\"{$row['state']}\",\"{$row['country']}\",\"".trim($val)."\",\"{$row['FirstName']} {$row['LastName']}\",\"".trim($row['name'])."\",\"{$row['status']}\",\"{$row['joined_sats']}\"\n";
			}

        }

    }

    public function view_agencies_and_services(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "View Agencies and Services";
        $country_id = $this->config->item('country');

        $agency_status = 'active';
        $state_filter = $this->input->get_post('state_filter');
        $sales_rep_filter = $this->input->get_post('sales_rep_filter');
        $search_filter = $this->input->get_post('search_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if (!empty($sub_region_ms)) {
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');


        $sel_query = "
        a.contact_first_name, 
        a.contact_last_name, 
        a.phone, a.abn, 
        a.agency_name as a_name, 
        a.address_1, a.address_2, 
        a.address_3, a.state, 
        a.postcode, a.status, 
        a.agency_id as a_id, 
        a.tot_properties,
        a.legal_name,
        a.account_emails, 
        a.agency_emails, 
        a.contact_phone,
        a.contact_email,
        a.tot_properties,
        a.country_id,
        a.send_emails,
        a.send_combined_invoice,
        a.send_entry_notice,
        a.require_work_order,
        a.auto_renew,
        a.key_allowed,
        a.key_email_req,
        a.trust_account_software,
        aght.priority,
        sa.FirstName, 
        sa.LastName,
        sr.subregion_name as postcode_region_name";

        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('postcode_regions', 'salesrep', 'country', 'agency_priority'),
            'country_id' => $country_id,
            'a_status' => $agency_status,
            'state' => $state_filter,
            'salesrep' => $sales_rep_filter,
            'agency_name' => $search_filter,
            'postcodes' => $postcodes,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );

        if ($this->input->get_post('export') && $this->input->get_post('export') == 1) { //EXPORT
            
            if( $this->input->get_post('export_type') == 'a' ){ //Normal export
               
                $this->get_agency_normal_export($params);

            }else if($this->input->get_post('export_type') == 'b'){ //Mailer export

                unset($params['a_status']); // remove agency status filter to show all agencies regardless of status
                $this->get_agency_mailer_export($params);
                
            }

        }else{

              //add offset/perpage params for normal listing
              $params['limit'] = $per_page;
              $params['offset'] = $offset;

              $agencies = $this->agency_model->get_agency($params)->result_array();
              $agenciesById = []; // make an object/map so you can access them by id easier later
      
              $agenciesPerRegion = []; // map agencies per region
      
              for ($x = 0; $x < count($agencies); $x++) {
                  $agency = &$agencies[$x];
      
                  $agency['region'] = null; // empty for now
                  $agency['ajt_counts'] = []; // will be used later
                  $agenciesById[$agency['a_id']] = &$agency; // take note with the &. reference the id to the object
      
                  #generate an empty array for later. there could be an unsafe shortcut for this
                  if (!isset($agenciesPerRegion[$agency['postcode']])) {
                      $agenciesPerRegion[$agency['postcode']] = [];
                  }
      
                  $agenciesPerRegion[$agency['postcode']][] = &$agency; // add a reference of agency to the region
              }
      
              # get all agency IDs from the result. you can loop through $agencies or do it like this:
              $agencyIds = array_keys($agenciesById);
      
              $regionCodes = array_keys($agenciesPerRegion); // get postcodes
              # make a function in a model or something that will query for the regions with the given postcodes. use WHERE IN statement to use a single query
              if( !empty($regionCodes) ){
                $regions =  $this->system_model->getRegion_v2($regionCodes)->result_array();
             
                foreach ($regions as $region) {
        
                    # reference the agencies in region.
                    # foreach can be buggy so you may have to use normal for loop
                    /*
                    foreach ($agenciesPerRegion[$region['postcode']] as &$agencyInRegion) {
                        $agenciesInRegion['region'] = $region;
                    }
                    */
                    for ($x = 0; $x < count($agenciesPerRegion[$region['postcode']]); $x++) {
                        $agenciesPerRegion[$region['postcode']][$x]['region'] = $region;
                    }
                    
                }
              }
             
              $alarmJobTypes = $this->agency_model->get_services()->result_array();
              $alarmJobTypeIds = [];
              foreach ($alarmJobTypes as $ajt) {
                  $alarmJobTypeIds[] = $ajt['id'];
              }
              
            if( !empty($agencyIds) ){
                $jobCounts = $this->db->select("
                        p.agency_id AS a_id,
                        ps.alarm_job_type_id AS ajt_id,
                        count(ps.`property_services_id`) as jcount
                    ")
                    ->from('property_services as ps')
                    ->join('property as p', 'ps.property_id = p.property_id', 'left')
                    ->join('agency as a', 'p.agency_id = a.agency_id')
                    ->where_in('p.agency_id', $agencyIds)
                    ->where('p.deleted', 0)
                    ->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )")
                    ->where('a.status', 'active')
                    ->where_in('ps.alarm_job_type_id', $alarmJobTypeIds)
                    ->where('ps.service', 1)
                    ->group_by(['p.agency_id', 'ps.alarm_job_type_id'])
                    ->order_by('p.agency_id ASC, ps.alarm_job_type_id ASC') // optional
                    ->get()->result_array();
        
                foreach ($jobCounts as $jc) {
                    #assign ajt count for each ajt in each agency
                    $agenciesById[ $jc['a_id'] ][ 'ajt_counts' ][ $jc[ 'ajt_id' ] ] = $jc[ 'jcount' ];
                }
            }
      
              # use these 2 variables in the view
              $data['agencies'] = $agencies;
              $data['alarmJobTypes'] = $alarmJobTypes;
           
              ## Header Filters
              // state filter
              $sel_query = "DISTINCT(a.`state`),
              a.`state`";
              $params = array(
                  'sel_query' => $sel_query,
                  'join_table' => array('postcode_regions', 'salesrep', 'country'),
                  'country_id' => $country_id,
                  'sort_list' => array(
                      array(
                          'order_by' => 'a.`state`',
                          'sort' => 'ASC',
                      )
                  ),
              );
              $data['state_filter_json'] = json_encode($params);
      
              //salesrep filter
              $data['salesrep'] = $this->agency_model->getAgencySalesRep('target');
                  
              // Region Filter ( get distinct state )
              $sel_query = "DISTINCT(a.`state`),a.`state`";
              $region_filter_arr = array(
                  'sel_query' => $sel_query,
                  'join_table' => array('postcode_regions', 'salesrep', 'country'),
                  'country_id' => $country_id,
                  'sort_list' => array(
                      array(
                          'order_by' => 'a.`state`',
                          'sort' => 'ASC',
                      )
                  ),
              );
              $data['region_filter_json'] = json_encode($region_filter_arr);
      
              ## Pagination Start
              //get total rows
              $params_total = array(
                  'sel_query' => "COUNT(a.`agency_id`) as a_count",
                  'join_table' => array('postcode_regions', 'salesrep', 'country'),
                  'country_id' => $country_id,
                  'a_status' => $agency_status,
                  'state' => $state_filter,
                  'salesrep' => $sales_rep_filter,
                  'agency_name' => $search_filter,
                  'postcodes' => $postcodes
              );
              $total_rows = $this->agency_model->get_agency($params_total)->row()->a_count;
              
              //base url params
              $pagi_links_params_arr = array(
                  'state_filter' => $state_filter,
                  'sales_rep_filter' => $sales_rep_filter,
                  'sub_region_ms' => $sub_region_ms,
                  'search_filter' => $search_filter
              );
              $pagi_link_params = '/agency/view_agencies_and_services/?' . http_build_query($pagi_links_params_arr);
      
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
              ## Pagination End
      
              //load views
              $this->load->view('templates/inner_header', $data);
              $this->load->view('agency/view_agencies_and_services', $data);
              $this->load->view('templates/inner_footer', $data);

        }

    }


    public function view_agency_details($agency_id=null, $tab=1){
        $this->load->model('properties_model');

        if(!$agency_id && empty($agency_id)){ //catch empty agency_id
            show_404();
        }

        ##pass data
        $data['tab'] = $tab;

        ##pass data
        $data['agency_id'] = $agency_id; #pass to view

        ## staff id
        $staff_id = $this->session->staff_id;
        $data['staff_id'] = $this->session->staff_id; #pass to view

        ##property tab type
        $prop_type = $this->input->get_post('prop_type');
        $data['prop_type'] = $this->input->get_post('prop_type'); #pass to view

        //usertype
        $params_user = array(
            'sel_query' => 'sa.StaffID, sa.ClassID',
            'StaffID' => $staff_id
        );
        $staff_query = $this->gherxlib->getStaffInfo($params_user)->row_array();
        $data['user_type'] = $staff_query['ClassID'];
        //usertype end

        $sel_query = "
        a.agency_id,
        a.agency_name,
        a.contact_first_name, 
        a.contact_last_name, 
        a.phone, 
        a.state,
        a.abn, 
        a.agency_name as a_name, 
        a.address_1, a.address_2, 
        a.address_3, a.state, 
        a.postcode, a.status, 
        a.agency_id as a_id, 
        a.tot_properties,
        a.legal_name,
        a.account_emails, 
        a.agency_emails, 
        a.contact_phone,
        a.contact_email,
        a.country_id,
        a.send_emails,
        a.send_combined_invoice,
        a.send_entry_notice,
        a.require_work_order,
        a.auto_renew,
        a.key_allowed,
        a.key_email_req,
        a.joined_sats,
        a.agency_hours,
        a.agency_specific_notes,
        a.website,
        a.team_meeting,
        a.comment,
        a.postcode_region_id,
        a.trust_account_software,
        a.franchise_groups_id,
        a.salesrep,
        a.statements_agency_comments,
        a.statements_agency_comments_ts,
        a.active_prop_with_sats,
        a.deactivated_reason,
        a.agency_using_id,
        a.accounts_name,
        a.accounts_phone,
        a.tenant_details_contact_name,
        a.tenant_details_contact_phone,
        a. agency_special_deal,
        a.multi_owner_discount,
        a.allow_indiv_pm_email_cc,
        a.allow_dk,
        a.allow_en,
        a.new_job_email_to_agent,
        a.allow_upfront_billing,
        a.display_bpay,
        a.invoice_pm_only,
        a.electrician_only,
        a.en_to_pm,
        a.accounts_reports,
        a.send_en_to_agency,
        a.pme_supplier_id,
        a.palace_diary_id,
        a.exclude_free_invoices,
        a.send_48_hr_key,
        a.add_inv_to_agen,
        a.api_billable,
        a.no_bulk_match,
        a.propertyme_agency_id,
        aght.`priority`,
        apmd.`abbreviation`,
        apmd.`priority_full_name`,

        aop.`renewal_interval`,
        aop.`renewal_start_offset`,

        sr.subregion_name as postcode_region_name,
        sa.FirstName,
        sa.LastName,
        tas.tsa_name,
        fg.name as franchise_name,
        am.marker_id,
        a.deleted,
        a.deleted_timestamp
        ";

        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('franchise_groups', 'country','postcode','postcode_regions','salesrep','trust_account_software','agency_markers','agency_other_pref','agency_priority', 'agency_priority_marker_definition'),
            'country_id' => COUNTRY,
            'agency_id' => $agency_id,
            'a_deleted' => false //ingore filter/no filter
        );

        $agencies = $this->agency_model->get_agency($params)->row_array();

        $pme_supplier_by_id = []; //make object for pme_supplier_id > will use later for agency_api_get_contact sub query

        for($a=0;$a<count($agencies);$a++){
            $agency = &$agencies; //reference
            $agency_pme_supplier_id=null;
            $pme_supplier_by_id[$agency['pme_supplier_id']] = &$agency; //ref
        }

        #get pme_supplier_id
        $pme_supplier_by_ids = array_keys($pme_supplier_by_id); #pme_supplier_id array

        $data['row'] = $agencies; //agency data object

        //getSatsToServicePropertyServices start
        $getSatsToServicePropertyServices = $this->agency_model->getSatsToServicePropertyServices($agency_id);
        $data['getSatsToServicePropertyServices'] = $getSatsToServicePropertyServices;
        //getSatsToServicePropertyServices send

        // trust account software start
        $tas_sql = $this->db->query("
            SELECT *
            FROM `trust_account_software`
            WHERE `active` = 1
        ");	
        $data['tas_sql'] = $tas_sql->result_array();
        // trust account software end

        //agency maintenance start
        $m = $this->db->select('*')
        ->from('maintenance')
        ->where('status', 1)
        ->order_by('name')
        ->get();
        $data['m_array'] = $m->result_array();

        #get selected maintenance
        $this->db->select('am.maintenance_id, am.surcharge,am.display_surcharge,am.price,am.surcharge_msg,m.name');
        $this->db->from('agency_maintenance as am');
        $this->db->join('maintenance as m','am.maintenance_id=m.maintenance_id','left');
        $this->db->where('am.agency_id', $agency_id);
        $am = $this->db->get();
        $data['sel_mp_num_rows'] = $am->num_rows();
        $data['sel_m'] = $am->row_array();
        //agency maintenance end

        //get state start
        $data['getCountryState'] = $this->properties_model->getCountryState();
        //get state end

        //get franchise group
        $data['fg_sql'] = $this->db->select('*')->from('franchise_groups')->where('country_id',COUNTRY)->order_by('name')->get();

        //get all sales rep (display sales only and active include id's below 'sales_not_id')
        //Shaquille smith access FOR AU = 2296 / NZ = 2259
        if(COUNTRY == 1){
            $sales_not_id = "2258,2317";
            $sales_in_id = "2189,2296";
        }else{
            $sales_not_id = "2258,2317";
            $sales_in_id = "2202,2259";
        }
        $salesrep_sql_query = $this->db->query("SELECT DISTINCT(ca.`staff_accounts_id`), `sa`.`FirstName`, `sa`.`LastName` FROM `staff_accounts` as `sa` INNER JOIN `country_access` as `ca` ON `ca`.`staff_accounts_id` = `sa`.`StaffID` WHERE `ca`.`country_id` = {$this->config->item('country')} AND `sa`.`deleted` = 0 AND `sa`.`active` = 1 AND `sa`.`ClassID` = 5 AND `sa`.`StaffID` NOT IN({$sales_not_id}) OR `sa`.`StaffID` IN({$sales_in_id})  ORDER BY `sa`.`FirstName`");
        $data['salesrep_sql'] = $salesrep_sql_query;

        //get agency using
        $data['gency_using_q'] = $this->agency_model->getAgencyUsingByCountry()->result_array();

        //addresses icons
        $data['agency_addresses_q'] = $this->db->select('*')
                ->from('agency_addresses')
                ->where('agency_id', $agency_id)
                ->get();

        //get agency_priority data
        $data['row']['priority'] = $this->agency_model->get_current_agency_ht($agencies['agency_id']);
        $data['row']['priority_reason']   = $this->agency_model->get_current_agency_ht_reason($agencies['agency_id']);
        $data['row']['priority_date_added'] = $this->agency_model->get_current_agency_ht_added_timestamp($agencies['agency_id']);

        // other smoke alarm company
        $data['sa_comp_sql'] = $this->db->query("
        SELECT `sac_id`, `company_name`
        FROM `smoke_alarms_company`
        WHERE `active` = 1
        ");

        // selected
        $afoc_sql = $this->db->query("
        SELECT 
            sac.`sac_id`,
            sac.`company_name`
        FROM `agencies_from_other_company` AS afoc
        LEFT JOIN `smoke_alarms_company` AS sac ON afoc.`company_id` = sac.`sac_id`
        WHERE afoc.`agency_id` = {$agency_id}
        AND afoc.`active` = 1
        ");  
        $data['afoc_row'] = $afoc_sql->row(); 

        if($tab==3){ ##load only for Portal Users tab > get/load portal users datas/items

            $sel_query="
                aua.`agency_user_account_id`,
                aua.`fname`,
                aua.`lname`,
                aua.`phone`,
                aua.`job_title`,
                aua.`email`,
                aua.`password`,
                aua.`user_type`,
                aua.`active`,

                au_2fa.`id` AS au_2fa_id,
                au_2fa.`active` AS au_2fa_active,
                
                auat.`user_type_name`
            ";
            $params_pm = array(
                'sel_query' => $sel_query,
                'agency_id' => $agency_id,
                'active' => 1,
                'sort_list' => array(
                    array(
                        'order_by' => 'aua.`user_type`',
                        'sort' => 'ASC'
                    )
                )
            );
            if($this->input->get_post('show_all')==1){ #show all user > unset active filter
                unset($params_pm['active']);
            }
            $data['agency_pm_sql'] = $this->agency_model->getNewPropertyManagers($params_pm)->result_array();            

            //get user type
            $data['agency_user_account_types_sql'] = $this->agency_model->agency_user_account_types();

        }elseif($tab==4){ ##load only for Pricing tab > get/load pricing datas/items
            
            ## get services
            $servicesById = [];
            $services_sql = $this->agency_model->get_services()->result_array();

            for ($x = 0; $x < count($services_sql); $x++) {
                $services = &$services_sql[$x];

                $services['agency_services_service_id'] = null;
                $services['agency_services_price'] = null;
                $servicesById[$services['id']] = &$services;
            }

            $serviceIds = array_keys($servicesById);

            if(!empty($serviceIds)){
                $approved_agency_services = $this->agency_model->get_approved_agency_services($agency_id, $serviceIds)->result_array();
                
                foreach($approved_agency_services as $approved_agency_services_row){
                    $servicesById[$approved_agency_services_row['service_id']]['agency_services_service_id'] = $approved_agency_services_row['service_id']; # approved services serviced_id
                    $servicesById[$approved_agency_services_row['service_id']]['agency_services_price'] = $approved_agency_services_row['price']; #  approved services price
                }
            }

            $data['services'] = $services_sql;


            ## get alarm
            $alarmById = [];
            $alarm_sql = $this->agency_model->get_alarms()->result_array();

            for ($x = 0; $x < count($alarm_sql); $x++) {
                $alarms = &$alarm_sql[$x];

                $alarms['price'] = null;
                $alarms['agency_alarm_id'] = null;
                $alarmById[$alarms['alarm_pwr_id']] = &$alarms;
            }

            $alarmsIds = array_keys($alarmById);

            if(!empty($alarmsIds)){
                ## get approved alarms
                $approved_agency_alarms = $this->agency_model->get_approved_agency_alarms($agency_id, $alarmsIds)->result_array();

                foreach($approved_agency_alarms as $approved_agency_alarms_row){
                    $alarmById[$approved_agency_alarms_row['alarm_pwr_id']]['price'] = $approved_agency_alarms_row['price']; #get alarm price by alarm_pwr_id
                    $alarmById[$approved_agency_alarms_row['alarm_pwr_id']]['agency_alarm_id'] = $approved_agency_alarms_row['agency_alarm_id']; #agency_alarm_id
                }
            }

            //Get free alarms
            $free_alarm = array();
            $paid_alarm = array();

            $state = $agencies['state'];

            if(COUNTRY == 2){
                $state = "";
            }

            if($state != ""){
                //FREE
                $free = 1;
                $free_alarm = $this->agency_model->get_free_alarms_display($state, $free);

                //PAID
                $free = 0;
                $paid_alarm = $this->agency_model->get_free_alarms_display($state, $free);

            }
            else if($state == ""){
                //FREE
                $free = 1;
                $free_alarm = $this->agency_model->get_free_alarms_display($state, $free);

                //PAID
                //PAID
                $free = 0;
                $paid_alarm = $this->agency_model->get_free_alarms_display($state, $free);
            }
            else{
                $data['free_alarms'] = "";
                $data['paid_alarms'] = "";
            }
            
            $data['free_alarms'] = $free_alarm;
            $data['paid_alarms'] = $paid_alarm;

            /**Get Property Variation Pricing Tab**/
            $data['variation_sql'] = $this->agency_model->get_property_variation_in_agency_pricing_tab($agency_id);
            
            /*
            echo "Free Alarms: <br />";
            print_r($data['free_alarms']);
            echo "<br /><br />";
            
            echo "Paid Alarms: <br />";
            print_r($data['paid_alarms']);
            echo "<br /><br />";
            */

            $data['alarms'] = $alarm_sql;
            
        }elseif($tab==5){ ## load/get preferences datas/items

            //no need get info from main query for agency

        }elseif($tab==6){ // Agency log tab

            $log_limit = $this->config->item('pagi_per_page');
            $log_offset = $this->input->get_post('offset');

            $agency_onboarding_by_id = [];
            $agency_onboarding_q = $this->agency_model->get_agency_onboarding()->result_array();

            for ($x = 0; $x < count($agency_onboarding_q); $x++) {
                $agency_onboarding = &$agency_onboarding_q[$x];

                $agency_onboarding['onboarding_selected_id'] = null;
                $agency_onboarding['onboarding_updated_date'] = null;
                $agency_onboarding['onboarding_updated_by'] = null;
                $agency_onboarding_by_id[$agency_onboarding['onboarding_id']] = &$agency_onboarding;
            }

            $agency_onboarding_ids = array_keys($agency_onboarding_by_id);

            if(!empty($agency_onboarding_by_id)){
                $agency_onboarding_selected_q = $this->agency_model->get_agency_onboarding_selected($agency_id, $agency_onboarding_ids)->result_array();
                foreach($agency_onboarding_selected_q as $row){
                    $agency_onboarding_by_id[$row['onboarding_id']]['onboarding_selected_id'] = $row['onboarding_selected_id'];
                    $agency_onboarding_by_id[$row['onboarding_id']]['onboarding_updated_date'] = date("d/m/Y H:i", strtotime($row['updated_date']));
                    $agency_onboarding_by_id[$row['onboarding_id']]['onboarding_updated_by'] = $this->system_model->formatStaffName($row['FirstName'],$row['LastName']);
                }
            }

            $data['agency_onboarding'] = $agency_onboarding_q;

            #get agency old log
            $old_log_params = array(
                'sel_query' => "c.contact_type, c.eventdate, c.comments, c.agency_event_log_id, sa.FirstName, sa.LastName, c.next_contact, c.important",
                'agency_id' => $agency_id,
                'limit' => $log_limit,
                'offset' => $log_offset
            );
            $agency_log_q = $this->agency_model->get_agency_event_log($old_log_params)->result_array();
            $data['agency_logs'] = $agency_log_q;

            #get total for pagination
            $old_log_totoal_params = array(
                'sel_query' => "COUNT(c.agency_id) as a_count",
                'agency_id' => $agency_id
            );
            $query = $this->agency_model->get_agency_event_log($old_log_totoal_params);
            $total_rows = $query->row()->a_count;
 
            // pagination start
            $pagi_link_params = "/agency/view_agency_details/{$agency_id}/{$tab}?";
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = (int) $total_rows;
            $config['per_page'] = (int) $log_limit;
            $config['base_url'] = $pagi_link_params;

            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links();

             // pagination count
             $pc_params = array(
                'total_rows' => (int) $total_rows,
                'offset' => (int) $log_offset,
                'per_page' => (int) $log_limit
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
            // pagination end

        }elseif($tab == 7){ //New logs

             #get snapshot status > for dropdown
             $this->db->select('*');
             $this->db->from('sales_snapshot_status');
             $q = $this->db->get();
             $data['sales_snapshot_status'] = $q->result_array();

             $log_type = $this->input->post('contact_type');
           
             #get main contact type
             $active_agency_contact_type_params = array('sel_query'=> "main_log_type_id, contact_type");
             $data['active_agency_contact_type'] = $this->agency_model->getMainLogType($active_agency_contact_type_params);

             $in_active_agency_contact_type_params = array('sel_query'=> "main_log_type_id, contact_type",'is_show'=>1);
             $data['in_active_agency_contact_type'] = $this->agency_model->getMainLogType($in_active_agency_contact_type_params);

             #get new log
             $log_limit = $this->config->item('pagi_per_page');
             $log_offset = $this->input->get_post('offset');
             $sel_query = "l.log_id,l.created_date,l.title,l.details,ltit.title_name,aua.fname,aua.lname,aua.photo,sa.StaffID,sa.FirstName,sa.LastName";
 
             $custom_where = 'l.title NOT IN(43,47)';
             $params = array(
                 'sel_query' => $sel_query,
                 'custom_where' => $custom_where,
                 'agency_id' => $agency_id,
                 'log_type' => $log_type,
                 'display_in_vad' => 1,
                 'deleted' => 0,
                 'limit' => $log_limit,
                 'offset' => $log_offset,
                 'sort_list' => array(
                     array(
                         'order_by' => 'l.created_date',
                         'sort' => 'DESC'
                     )
                 ),
                 'display_query' => 0
             );
 
             $data['new_log'] = $this->agency_model->getNewLogs($params)->result_array();
 
             //get new log total count
             $new_log_total_sel_q = "COUNT(l.log_id) as log_count";
             $new_log_total_params = array(
                 'sel_query' => $new_log_total_sel_q,
                 'custom_where' => $custom_where,
                 'agency_id' => $agency_id,
                 'log_type' => $log_type,
                 'display_in_vad' => 1,
                 'deleted' => 0
             );
             $query = $this->agency_model->getNewLogs($new_log_total_params);
             $total_rows = $query->row()->log_count;

            //get log_type/contact_type for filter dropdown
            $new_log_log_type = "DISTINCT(l.log_type)";
             $new_log_log_type_params = array(
                 'sel_query' => $new_log_log_type,
                 'custom_where' => $custom_where,
                 'agency_id' => $agency_id,
                 'display_in_vad' => 1,
                 'deleted' => 0
             );
             $query_log_type = $this->agency_model->getNewLogs($new_log_log_type_params)->result_array();
             $log_type_arr = array();
             foreach($query_log_type as $query_log_type_row){
                if($query_log_type_row['log_type']!=""){
                    $log_type_arr[] = $query_log_type_row['log_type'];
                }
             }
           
             if(!empty($log_type_arr)){
                $this->db->select('main_log_type_id,contact_type');
                $this->db->from('main_log_type');
                $this->db->where_in('main_log_type_id', $log_type_arr);
                $data['main_log_type_q'] = $this->db->get()->result_array();
             }else{
                $data['main_log_type_q'] = null;
             }
            
 
             $pagi_link_params = "/agency/view_agency_details/{$agency_id}/{$tab}?";
 
             // pagination settings
             $config['page_query_string'] = TRUE;
             $config['query_string_segment'] = 'offset';
             $config['total_rows'] = (int) $total_rows;
             $config['per_page'] = (int) $log_limit;
             $config['base_url'] = $pagi_link_params;
 
             $this->pagination->initialize($config);
             $data['pagination'] = $this->pagination->create_links();
 
              // pagination count
              $pc_params = array(
                 'total_rows' => (int) $total_rows,
                 'offset' => (int) $log_offset,
                 'per_page' => (int) $log_limit
             );
             $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        }elseif($tab == 8){ //File tab

            #get agency property files
            $data['property_files'] = $this->agency_model->getPropertyFiles2($agency_id);

            #get agency contractor_appointment files
            $params_getContractorAppointment = array(
                'sel_query' => '*',
                'agency_id' => $agency_id
            );
            $data['getContractorAppointment'] = $this->agency_model->getContractorAppointment($params_getContractorAppointment);

            #get agency specific brochures
            $get_agency_specific_brochures_params = array(
                'sel_query' => '*',
                'agency_id' => $agency_id
            );
            $data['agency_specific_brochures'] = $this->agency_model->get_agency_specific_brochures($get_agency_specific_brochures_params);

        }elseif($tab==9){ //Properties tab

            $this->load->model('properties_model');

            $export = $this->input->get_post('export');

            #get agency properties
            $prop_search = $this->input->get_post('search_filter');
            $prop_status = ($this->input->get_post('prop_status')!="")? $this->input->get_post('prop_status') : "-1";
            $prop_service = $this->input->get_post('service_type');
            $pm_id = $this->input->get_post('pm_id');
            $per_page = 100;
            $offset = $this->input->get_post('offset');

           /* if($prop_service!=""){
                $prop_custom_where = "ps.alarm_job_type_id = {$prop_service} AND ps.service = 1";
            }*/
           
            $prop_sel = "
                p.address_1, 
                p.address_2, 
                p.address_3, 
                p.state,
                p.landlord_firstname, 
                p.landlord_lastname, 
                p.service, 
                p.`property_id`,
                p.`compass_index_num`,
                p.`pm_id_new`, 
                p.`key_number`,
                p.`deleted`,
                p.`nlm_timestamp`,
                p.is_nlm,
                
                aua.`fname` AS pm_fname, 
                aua.`lname` AS pm_lname,
                aua.`email` AS pm_email";
           /* $params_prop = array(
                'sel_query' => $prop_sel,
                'agency_filter' => $agency_id,
                'search' => $prop_search,
                'p_deleted' => $prop_status,
                'join_table' => array('property_services'),
                'custom_where' => $prop_custom_where,
                'group_by' => 'p.property_id',
                'sort_list' => array(
                    array(
                        'order_by' => 'p.address_2',
                        'sort' => 'ASC'
                    )
                ),
                'limit' => $per_page,
                'offset' => $offset,
                'display_query' => 0

            );*/

            ##Service filter
            if($prop_service!=""){
                $alarm_job_type_id_filter = "AND ps.alarm_job_type_id = {$prop_service}";
            }else{
                //$alarm_job_type_id_filter = "AND ps.alarm_job_type_id !=0 AND (p.deleted = 0 OR (p.deleted = 1 AND p.nlm_display = 1))";
                //$alarm_job_type_id_filter = "AND ps.alarm_job_type_id !=0 AND ((p.is_nlm = 0 OR p.is_nlm IS NULL) OR (p.is_nlm = 1 AND p.nlm_display = 1))";
                $alarm_job_type_id_filter = "AND ps.alarm_job_type_id !=0";
            }
           
            ## tab switching > query filter switching per tab type(sats/non sats/onceoff)
            $pm_custom_where = "aua.agency_id = {$agency_id} AND aua.active = 1";
            if($prop_type == 1 || !$prop_type){ ##SATS service

                $prop_custom_where = "ps.service = 1 {$alarm_job_type_id_filter} AND p.is_sales!=1 AND (j.prop_comp_with_state_leg=1 OR j.prop_comp_with_state_leg IS NULL)";

                $params_prop = array(
                    'sel_query' => $prop_sel,
                    'agency_filter' => $agency_id,
                    'search' => $prop_search,
                    //'p_deleted' => $prop_status,
                    //'is_nlm' => $prop_status,
                    'is_nlm' => 0, ##dont display nlm prop in SATS tab
                    'pm_id' => $pm_id,
                    'join_table' => array('property_services','jobs'),
                    'custom_where_arr' => array($prop_custom_where),
                    'group_by' => 'p.property_id',
                    'sort_list' => array(
                        array(
                            'order_by' => 'p.address_2',
                            'sort' => 'ASC'
                        )
                    ),
                    'limit' => $per_page,
                    'offset' => $offset,
                    'display_query' => 0
    
                );

                #params for PM filter > dropdown
                $params_pm = array(
                    'sel_query' => "DISTINCT(p.pm_id_new), aua.fname as pm_fname, aua.lname as pm_lname",
                    'agency_filter' => $agency_id,
                    'custom_where_arr' => array($pm_custom_where),
                    'sort_list' => array(
                        array(
                            'order_by' => 'aua.fname',
                            'sort' => 'ASC'
                        )
                    ),
                    'display_query' => 0
    
                );
                #params for PM filter > dropdown end

                #Active/Inactive count >>>>> disabled in SATS tab and moved to Not Service by SATS
                /*$params_prop_active = array(
                    'sel_query' => "p.`property_id`,",
                    'agency_filter' => $agency_id,
                    'search' => $prop_search,
                    //'p_deleted' => 0,
                    'is_nlm' => 0,
                    'pm_id' => $pm_id,
                    'join_table' => array('property_services','jobs'),
                    'custom_where_arr' => array($prop_custom_where),
                    'group_by' => 'p.property_id',
                    'display_query' => 0
                );
                $data['active_count'] = $this->properties_model->get_properties($params_prop_active)->num_rows();
            
                $params_prop_inactive = array(
                    'sel_query' => "p.`property_id`,",
                    'agency_filter' => $agency_id,
                    'search' => $prop_search,
                    //'p_deleted' => 1,
                    'is_nlm' => 1,
                    'pm_id' => $pm_id,
                    'join_table' => array('property_services','jobs'),
                    'custom_where_arr' => array($prop_custom_where),
                    'group_by' => 'p.property_id',
                    'display_query' => 0
                );
                $data['inactive_count'] = $this->properties_model->get_properties($params_prop_inactive)->num_rows();
                */

            }elseif($prop_type==2){ ##Non Sats

                ##Get sats service and set array for sasts servce (used to exclude on non sats query)
                $params_prop_sats_custom_where = "ps.service = 1";
                $params_prop_sats = array(
                    'sel_query' => 'p.property_id',
                    'agency_filter' => $agency_id,
                    //'p_deleted' => $prop_status,
                    'is_nlm' => $prop_status,
                    'custom_where' => $params_prop_sats_custom_where,
                    'join_table' => array('property_services'),
                    'group_by' => 'p.property_id'
                );
                $excludedPropertyId_q = $this->properties_model->get_properties($params_prop_sats);

                $excludedPropertyIds = [];

                foreach($excludedPropertyId_q->result() as $notInRow){
                    $excludedPropertyIds[] = $notInRow->property_id;
                }

                $excludedPropertyIds_imp = implode(', ',$excludedPropertyIds);

                ## Get Non Sats

                #trap if has exclude properties  
                if(!empty($excludedPropertyIds_imp)){
                   $not_in_where = "p.property_id NOT IN ({$excludedPropertyIds_imp})";
                }

               // $prop_custom_where = "ps.alarm_job_type_id!=0 AND ps.service !=1 {$alarm_job_type_id_filter}";
                $prop_custom_where = "ps.service !=1 {$alarm_job_type_id_filter}";
                
               /* $params_prop = array( //DISABLED BY GHERX 
                    'sel_query' => $prop_sel,
                    'agency_filter' => $agency_id,
                    'search' => $prop_search,
                    'p_deleted' => $prop_status,
                    'pm_id' => $pm_id,
                    'join_table' => array('property_services'),
                    'custom_where_arr' => array($not_in_where,$prop_custom_where),
                    'group_by' => 'p.property_id',
                    'sort_list' => array(
                        array(
                            'order_by' => 'p.address_2',
                            'sort' => 'ASC'
                        )
                    ),
                    'limit' => $per_page,
                    'offset' => $offset,
                    'display_query' => 0
    
                );*/

                ##Updated removed property_services join and $prop_custom_where custom where > GHERX
                $params_prop = array( 
                    'sel_query' => $prop_sel,
                    'agency_filter' => $agency_id,
                    'search' => $prop_search,
                    //'p_deleted' => $prop_status,
                    'is_nlm' => $prop_status,
                    'pm_id' => $pm_id,
                    'custom_where_arr' => array($not_in_where),
                    'group_by' => 'p.property_id',
                    'sort_list' => array(
                        array(
                            'order_by' => 'p.address_2',
                            'sort' => 'ASC'
                        )
                    ),
                    'limit' => $per_page,
                    'offset' => $offset,
                    'display_query' => 0
    
                );

                #params for PM filter > dropdown
                $params_pm = array(
                    'sel_query' => "DISTINCT(p.pm_id_new), aua.fname as pm_fname, aua.lname as pm_lname",
                    'agency_filter' => $agency_id,
                    'join_table' => array('property_services'),
                    'custom_where_arr' => array($not_in_where,$prop_custom_where,$pm_custom_where),
                    'sort_list' => array(
                        array(
                            'order_by' => 'aua.fname',
                            'sort' => 'ASC'
                        )
                    ),
                    'display_query' => 0
    
                );
                #params for PM filter > dropdown end


                $params_prop_active = array(
                    'sel_query' => "p.`property_id`,",
                    'agency_filter' => $agency_id,
                    'search' => $prop_search,
                    'is_nlm' => 0,
                    'pm_id' => $pm_id,
                    'custom_where_arr' => array($not_in_where),
                    'group_by' => 'p.property_id',
                    'sort_list' => array(
                        array(
                            'order_by' => 'p.address_2',
                            'sort' => 'ASC'
                        )
                    ),
                    'display_query' => 0
                );
                $data['active_count'] = $this->properties_model->get_properties($params_prop_active)->num_rows();
            
                $params_prop_inactive = array(
                    'sel_query' => "p.`property_id`,",
                    'agency_filter' => $agency_id,
                    'search' => $prop_search,
                    'is_nlm' => 1,
                    'pm_id' => $pm_id,
                    'custom_where_arr' => array($not_in_where),
                    'group_by' => 'p.property_id',
                    'sort_list' => array(
                        array(
                            'order_by' => 'p.address_2',
                            'sort' => 'ASC'
                        )
                    ),
                    'display_query' => 0
                );
                $data['inactive_count'] = $this->properties_model->get_properties($params_prop_inactive)->num_rows();

            }elseif($prop_type==3){ ##Once Off

                $prop_custom_where = "j.job_type = 'Once-off' {$alarm_job_type_id_filter}";
                $params_prop = array(
                    'sel_query' => $prop_sel,
                    'agency_filter' => $agency_id,
                    'search' => $prop_search,
                    //'p_deleted' => $prop_status,
                    'is_nlm' => $prop_status,
                    'pm_id' => $pm_id,
                    'join_table' => array('property_services','jobs'),
                    'custom_where_arr' => array($prop_custom_where),
                    'group_by' => 'p.property_id',
                    'sort_list' => array(
                        array(
                            'order_by' => 'p.address_2',
                            'sort' => 'ASC'
                        )
                    ),
                    'limit' => $per_page,
                    'offset' => $offset,
                    'display_query' => 0
    
                );

                #params for PM filter > dropdown
                $params_pm = array(
                    'sel_query' => "DISTINCT(p.pm_id_new), aua.fname as pm_fname, aua.lname as pm_lname",
                    'agency_filter' => $agency_id,
                    'join_table' => array('property_services','jobs'),
                    'custom_where_arr' => array($prop_custom_where,$pm_custom_where),
                    'sort_list' => array(
                        array(
                            'order_by' => 'aua.fname',
                            'sort' => 'ASC'
                        )
                    ),
                    'display_query' => 0
    
                );
                #params for PM filter > dropdown end

            }

            
            ## removed limit and offset params for export
            if($export==1){
                unset($params_prop['limit']);
                unset($params_prop['offset']);
                unset($params_prop['sort_list']); //removed temporarily
            }
           
            ## Main Query
            $property = $this->properties_model->get_properties($params_prop)->result_array();

            #get id array just in case for future use
            $propertyById = [];
            $ajtById = [];

            for( $x = 0; $x<count($property) ;$x++ ){
                $properties = &$property[$x];

                $properties['service'] = null;
                $properties['prop_service'] = null;
                $propertyById[$properties['property_id']] = &$properties;

                #get agency services
                $agencyservices_params = array(
                    'sel_query' => 'ajt.id as ajt_id, ajt.type, agen_serv.service_id',
                    'join_table' => array('alarm_job_type'),
                    'agency_id' => $agency_id
                );
                $agency_services = $this->agency_model->get_agency_services($agencyservices_params)->result_array();           
    
                foreach($agency_services as $agency_services_row){
                    $properties['agency_service_arr'][] = $agency_services_row;

                    $atay = &$agency_services_row;
                    $ajtById[$atay['ajt_id']][] = &$atay;
                }
                #get agency services end

            }

            $ajtByIds = array_keys($ajtById);

            $propertyByIds = array_keys($propertyById); # get all property ids

            //create agency service array for services dropdown filter 
            foreach($property as $agency_services_row){
                $data['agency_services'] = $agency_services_row['agency_service_arr'];
            }

            //get property managers
            $data['pm_list'] = $this->properties_model->get_properties($params_pm);
            //get property managers end

            #get property services = SATS
            if(!empty($propertyById)){
                $this->db->select('p.property_id, ps.alarm_job_type_id, ps.service, ajt.type');
                $this->db->from('`property_services` AS ps');
                $this->db->join('`property` AS p', 'ps.`property_id` = p.`property_id`', 'left');
                $this->db->join('`alarm_job_type` AS ajt', 'ps.`alarm_job_type_id` = ajt.`id`', 'left');
                $this->db->where('ps.service', 1);
                $this->db->where_in('ps.property_id', $propertyByIds);
                $this->db->where_in('ajt.id', $ajtByIds);
                $prop_serv_query = $this->db->get()->result_array();

                foreach($prop_serv_query as $prop_serv_row){
                    $propertyById[$prop_serv_row['property_id']]['prop_service'][] = $prop_serv_row;
                }
            }

            if($export==1){ //EXPORT CSV

                $export_property = $property; #main property query
                
                ## set file name
                $fn = "";
                $filename = "Properties_".$fn."_".date("d")."-".date("m")."-".date("y").".csv";

                ## CSV header
                header("Content-Type: text/csv");   
                header("Content-Disposition: Attachment; filename={$filename}");
                header("Pragma: no-cache");
                header("Expires: 0");

                ## services array
                $serv_type_str = array();
                foreach($agency_services as $as){
                   // $serv_str .= ",{$as['type']},last YM";
                  $serv_type_str[] =$as['type'];
                  array_push($serv_type_str,"Last YM");
                }

                ## file creation start
                $file = fopen('php://output', 'w');

                $header = array("Address", "Suburb", "1st Tenant Name", "1st Tenant Ph", "1st Tenant Mobile", "1st Tenant Email", "2nd Tenant Name", "2nd Tenant Ph", "2nd Tenant Mobile", "2nd Tenant Email","3rd Tenant Name","3rd Tenant Ph","3rd Tenant Mobile","3rd Tenant Email","4th Tenant Name","4th Tenant Ph","4th Tenant Mobile","4th Tenant Email","Last Attended","Property Manager","Land Lord","Key Number","Status","NLM Date");
                
                 // Compass Housing QLD insert header to array
                 if( $agencies['franchise_groups_id'] == 39 ){
                    array_push($header,"Compass Index Number");
                 } else if($agency_id == 1598 && COUNTRY==1){
                     array_push($header,"Property Code"); 
                 }

                $header2 = array_merge($header, $serv_type_str);

                fputcsv($file, $header2);

                //loop for listing here....
                foreach($export_property as $row){

                    ##get tenants > 4 tenants only
                    $dd = array();
                    $pt_params = array(
                        'property_id' => $row['property_id'],
                        'active' => 1,
                        'limit' => 4
                    );
                    $prop_tenants_q = $this->gherxlib->getNewTenantsData($pt_params);

                    $tenant_name_arr = [];
                    $tenant_landline_arr = [];
                    $tenant_mobile_arr = [];
                    $tenant_email_arr = [];
                    foreach($prop_tenants_q as $prop_tenants_q_row){
                        $tenant_name_arr[] =  "{$prop_tenants_q_row->tenant_firstname} {$prop_tenants_q_row->tenant_lastname}";
                        $tenant_landline_arr[] = $prop_tenants_q_row->tenant_landline;
                        $tenant_mobile_arr[] =  $prop_tenants_q_row->tenant_mobile;
                        $tenant_email_arr[] =  $prop_tenants_q_row->tenant_email;
                    }
                     ##get tenants > 4 tenants only end

                    ##get last attended
                    $last_attended = $this->agency_model->last_attended($row['property_id']);

                    ## deleted prop
                    $p_deleted = ( $row['deleted'] == 1 )?'Inactive':'Active';

                    ## nlm_timestamp
                    $nlm_date = $this->system_model->isDateNotEmpty($row['nlm_timestamp'])?date("d/m/Y",strtotime($row['nlm_timestamp'])):null;

                    ## Export csv data 
                    $export_data['Address'] = "{$row['address_1']} {$row['address_2']}";
                    $export_data['Suburb'] = $row['address_3'];

                    ##tenant col
                    for($i=0;$i<4;$i++){
                        $export_data['tenant_name'.$i] = ($tenant_name_arr[$i]!="") ? $tenant_name_arr[$i] : "";
                        $export_data['tenant_landline'.$i] = ($tenant_landline_arr[$i]!="") ? $tenant_landline_arr[$i] : "";
                        $export_data['tenant_mobile'.$i] = ($tenant_mobile_arr[$i]!="") ? $tenant_mobile_arr[$i] : "";
                        $export_data['tenan_email'.$i] = ($tenant_email_arr[$i]!="") ? $tenant_email_arr[$i] : "";
                    }
                        
                    $export_data['last_attended'] = $last_attended;
                    $export_data['pm'] = "{$row['pm_fname']} {$row['pm_lname']}";
                    $export_data['landlord'] = "{$row['landlord_firstname']} {$row['landlord_lastname']}";
                    $export_data['key_number'] = "{$row['key_number']}";
                    $export_data['deleted'] = $p_deleted;
                    $export_data['nlm_date'] = $nlm_date;
                    
                    if( $agencies['franchise_groups_id'] == 39 || ($agency_id == 1598 && COUNTRY==1)){
                        $export_data['compass_index_num'] = $row['compass_index_num'];
                    }

                    $serv_cnt = 0;
                    foreach($agency_services as $agency_services_row){
                        $prop_services = $this->properties_model->get_services($row['property_id'],$agency_services_row['service_id']);
                        $lym = $this->agency_model->get_last_ym_by_prop_and_service($row['property_id'], $agency_services_row['service_id']);

                        $export_data['prop_services'.$serv_cnt] = $prop_services;
                        $export_data['last_ym'.$serv_cnt] = $lym;

                        $serv_cnt++;
                    }
                    ## Export csv date end

                    fputcsv($file, $export_data);
                }

                fclose($file);
                exit;
                ## file creation end


            }else{ //NORMAL LISTING
               
                $data['property'] = $property;

                #get agency properties total count
               /* $params_prop_count = array(
                    'sel_query' => 'p.property_id',
                    'agency_filter' => $agency_id,
                    'search' => $prop_search,
                    'p_deleted' => $prop_status,
                    'pm_id' => $pm_id,
                    'join_table' => array('property_services'),
                    'custom_where_arr' => array($not_in_where,$prop_custom_where),
                    'group_by' => 'p.property_id',
                    'display_query' => 0
                );*/ //disabled by Gherx added condition because of NOT SATS QUERY CHANGES > removed property_services join
                if( $prop_type==1 ){ ## Count active/not nlm only
                    $params_prop_count = array(
                        'sel_query' => 'p.property_id',
                        'agency_filter' => $agency_id,
                        'search' => $prop_search,
                        //'p_deleted' => $prop_status,
                        'is_nlm' => 0,
                        'pm_id' => $pm_id,
                        'join_table' => array('property_services','jobs'),
                        'custom_where_arr' => array($not_in_where,$prop_custom_where),
                        'group_by' => 'p.property_id',
                        'display_query' => 0
                    );
                }else if($prop_type==2){ //remove join property_services table for NOT SATS tab
                    $params_prop_count = array(
                        'sel_query' => 'p.property_id',
                        'agency_filter' => $agency_id,
                        'search' => $prop_search,
                        //'p_deleted' => $prop_status,
                        'is_nlm' => intval($prop_status),
                        'pm_id' => $pm_id,
                        'custom_where_arr' => array($not_in_where),
                        'group_by' => 'p.property_id',
                        'display_query' => 0
                    );
                }else{
                    $params_prop_count = array(
                        'sel_query' => 'p.property_id',
                        'agency_filter' => $agency_id,
                        'search' => $prop_search,
                        //'p_deleted' => $prop_status,
                        'is_nlm' => intval($prop_status),
                        'pm_id' => $pm_id,
                        'join_table' => array('property_services','jobs'),
                        'custom_where_arr' => array($not_in_where,$prop_custom_where),
                        'group_by' => 'p.property_id',
                        'display_query' => 0
                    );
                }
        
                ## add element > add job join for Once Off tab/jobs
                /*if($prop_type==3){
                    array_push($params_prop_count['join_table'],'jobs');
                }*/

                $property_count = $this->properties_model->get_properties($params_prop_count);
                $total_rows = $property_count->num_rows();
                
                

                # pagination settings
                $pagi_links_params_arr = array(
                    'search_filter' => $prop_search,
                    'prop_status' => $prop_status,
                    'service_type' => $prop_service,
                    'pm_id' => $pm_id
                );
                $pagi_link_params = "/agency/view_agency_details/{$agency_id}/$tab?prop_type={$prop_type}&" . http_build_query($pagi_links_params_arr);

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

                ## agency services
                $data['agency_services'] = $agency_services;

                #get agency list > for dropdown
                $agency_custom_where = "a.agency_id!={$agency_id}";
                $agency_params = array(
                    'sel_query' => 'a.agency_id, a.agency_name',
                    'a_status' => 'active',
                    'country_id' => COUNTRY,
                    'custom_where' => $agency_custom_where,
                    'sort_list' => array(
                        array(
                            'order_by' => 'a.`agency_name`',
                            'sort' => 'ASC'
                        )
                    ),

                );
                $data['agency_list'] = $this->agency_model->get_agency($agency_params)->result_array();

                ##get all Property Managers > for assign PM dropdown
                $all_pm_params = array(
                    'sel_query' => "aua.agency_user_account_id, aua.fname, aua.lname",
                    'agency_id' => $agency_id,
                    'user_type' => 2,
                    'active' => 1

                );
                $data['all_pm'] = $this->properties_model->get_agency_pm($all_pm_params)->result_array();

            }

        }elseif($tab==10){ //Accounts Tab > Load only relevant data for Accounts tab

            $per_page = $this->config->item('pagi_per_page');
            $offset = $this->input->get_post('offset');
            
            #account main query
            $sel_query = "
                l.`log_id`,
                l.`created_date`,
                l.`title`,
                l.`details`,				
                
                ltit.`title_name`,
                
                aua.`fname`,
                aua.`lname`,
                aua.`photo`,

                sa.StaffID,
                sa.FirstName,
                sa.LastName";
                $custom_where = "l.title IN(43,47)";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'agency_id' => $agency_id,
                'display_in_vad' => 1,
                'deleted' => 0,
                'sort_list' => array(
                    array(
                        'order_by' => 'l.`created_date`',
                        'sort' => 'DESC'
                    )
                ),
                'limit' => $per_page,
                'offset' => $offset,
                'display_query' => 0
            );
            $new_logs_q = $this->agency_model->getNewLogs($params)->result_array();

            $data['new_logs'] = $new_logs_q;

            #Accounts pagination
            #get total
            $acc_total_params = array(
                'sel_query' => 'COUNT(l.log_id) as log_count',
                'custom_where' => $custom_where,
                'agency_id' => $agency_id,
                'display_in_vad' => 1,
                'deleted' => 0,
            );
            $log_count_q = $this->agency_model->getNewLogs($acc_total_params)->row_array();
            $total_rows = $log_count_q['log_count'];

            #pagination settings
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = $total_rows;
            $config['per_page'] = $per_page;
            $config['base_url'] = "/agency/view_agency_details/{$agency_id}/{$tab}";

            $this->pagination->initialize($config);

            $data['pagination'] = $this->pagination->create_links();

            // pagination count
            $pc_params = array(
                'total_rows' => $total_rows,
                'offset' => $offset,
                'per_page' => $per_page
            );

            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        }elseif($tab==1){ //API Tab > from 11 moved to 1 tab as per Dan's request (Gherx)

            $this->load->model('api_model');
            $this->load->model('palace_model');
            
            $api_sel = "
            agen_api_int.`api_integration_id`,		
            agen_api_int.`connected_service`,
            agen_api_int.`active`,
            agen_api_int.`date_activated`,

            agen_api.`api_name`,
            agen_api.`agency_api_id`";

            $api_params = array(
                'sel_query' => $api_sel,
                'agency_id' => $agency_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'agen_api.api_name',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0
            );
            $api_q = $this->api_model->get_agency_api_integration($api_params)->result_array();
            $connected_serviceById = []; //make api_id object

            for( $x = 0; $x<count($api_q) ;$x++ ){
                $api = &$api_q[$x];

                $api['agency_api_token_id'] = null;
                $api['agency_api_id'] = null;

                $connected_serviceById[$api['connected_service']] = &$api;
            }
         
            #get all connected_service_id
            $connected_serviceByIds = array_keys($connected_serviceById);

            #get agency api token
            if(!empty($connected_serviceByIds)){
                $connected_serviceByIds_imp = implode(",",$connected_serviceByIds);
                $api_custom_where = "agen_api_tok.api_id IN({$connected_serviceByIds_imp})";
                $get_agency_api_tokens_params = array(
                    'sel_query' => 'agen_api_tok.agency_api_token_id, agen_api_tok.api_id',
                    'active' => 1,
                    'agency_id' => $agency_id,
                    'custom_where' => $api_custom_where
    
                );
                $agency_api_tokens = $this->api_model->get_agency_api_tokens($get_agency_api_tokens_params)->result_array();
                foreach($agency_api_tokens as $agency_api_token){
                    $connected_serviceById[$agency_api_token['api_id']]['agency_api_token_id'] = $agency_api_token['agency_api_token_id'];
                    $connected_serviceById[$agency_api_token['api_id']]['agency_api_id'] = $agency_api_token['api_id']; // no use yet
                }
            }

            $data['api'] = $api_q;

            #get agency API
            $get_agency_api_params = array(
                'sel_query' => "agency_api_id, api_name",
                'active' => 1,
                'sort_list' => array(
                    array('order_by'=>'api_name', 'sort'=>'ASC')
                )
            );
            $agency_api = $this->api_model->get_agency_api($get_agency_api_params)->result_array();
            $data['agency_api'] = $agency_api;

            $data['row']['priority'] = $this->agency_model->get_current_agency_ht($agencies['agency_id']);
            $data['row']['priority_reason']   = $this->agency_model->get_current_agency_ht_reason($agencies['agency_id']);

        }elseif($tab==12){
            $agency_onboarding_by_id = [];
            $agency_onboarding_q = $this->agency_model->get_agency_onboarding()->result_array();

            for ($x = 0; $x < count($agency_onboarding_q); $x++) {
                $agency_onboarding = &$agency_onboarding_q[$x];

                $agency_onboarding['onboarding_selected_id'] = null;
                $agency_onboarding['onboarding_updated_date'] = null;
                $agency_onboarding['onboarding_updated_by'] = null;
                $agency_onboarding_by_id[$agency_onboarding['onboarding_id']] = &$agency_onboarding;
            }

            $agency_onboarding_ids = array_keys($agency_onboarding_by_id);

            if(!empty($agency_onboarding_by_id)){
                $agency_onboarding_selected_q = $this->agency_model->get_agency_onboarding_selected($agency_id, $agency_onboarding_ids)->result_array();
                foreach($agency_onboarding_selected_q as $row){
                    $agency_onboarding_by_id[$row['onboarding_id']]['onboarding_selected_id'] = $row['onboarding_selected_id'];
                    $agency_onboarding_by_id[$row['onboarding_id']]['onboarding_updated_date'] = date("d/m/Y H:i", strtotime($row['updated_date']));
                    $agency_onboarding_by_id[$row['onboarding_id']]['onboarding_updated_by'] = $this->system_model->formatStaffName($row['FirstName'],$row['LastName']);
                }
            }

            $data['agency_onboarding'] = $agency_onboarding_q;
        }

        $data['agency_priority'] = $this->agency_model->get_agency_priority($agency_id);

        $data['title'] = "Agency Details";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/view_agency_details', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function update_agency(){

        $this->load->library('email');

        $tab = $this->uri->segment(4);
        $country = COUNTRY;

        ## POST DATA >>>>>
        $postData = $this->input->post();
        $fullAdd = $this->input->post('fullAdd');
        $street_number = $this->input->post('address_1');
        $street_name = $this->input->post('address_2');
        $suburb = $this->input->post('address_3');
        $state = $this->input->post('state');
        $postcode = $this->input->post('postcode');
        $franchise_group = $this->input->post('franchise_group');
        $phone = $this->input->post('phone');
        $comment = $this->input->post('comment');

        $status = $this->input->post('status');
        $salesrep = $this->input->post('salesrep');
        $statements_agency_comments = $this->input->post('statements_agency_comments');
        $orig_statements_agency_comments = $this->input->post('orig_statements_agency_comments');
        $statements_agency_comments_ts_post = $this->input->post('statements_agency_comments_ts');

        $agency_name = $this->input->post('agency_name');
        $legal_name = $this->input->post('legal_name');
        $orig_legal_name = $this->input->post('og_legal_name');
        $abn = $this->input->post('abn');
        $orig_abn = $this->input->post('og_abn');
        $totprop = ($this->input->post('tot_properties')=="")?0:$this->input->post('tot_properties');
        $agency_hours = $this->input->post('agency_hours');
        $agency_specific_notes = $this->input->post('agency_specific_notes');
        $team_meeting = $this->input->post('team_meeting');
        $trust_acc_soft = $this->input->post('trust_acc_soft');
        $propertyme_agency_id = ($this->input->post('propertyme_agency_id')!="") ? $this->input->post('propertyme_agency_id') : NULL;
        
        $maintenance = $this->input->post('maintenance');
        $m_surcharge = $this->input->post('m_surcharge');
        $m_disp_surcharge = $this->input->post('m_disp_surcharge');
        $m_price = $this->input->post('m_price');
        $m_surcharge_msg = $this->input->post('m_surcharge_msg');

        ##Hidden Fields
        $agency_id = $this->input->post('agency_id');
        $mm_program_edited = $this->input->post('mm_program_edited');
        $fields_edited = $this->input->post('fields_edited');

        $ac_fname = $this->input->post('ac_fname');
        $ac_lname = $this->input->post('ac_lname');
        $ac_phone = $this->input->post('ac_phone');
        $ac_email = $this->input->post('ac_email');
        $acc_name = $this->input->post('acc_name');
        $acc_phone = $this->input->post('acc_phone');
        $tdc_name = $this->input->post('tdc_name');
        $tdc_phone = $this->input->post('tdc_phone');

        ##Agency Status Tweak (Deactivate)
        $active_prop_with_sats = $this->input->post('active_prop_with_sats');
        $agency_using = $this->input->post('agency_using');
        $deactivate_reason = $this->input->post('deactivate_reason');

        ##Agency Emails Tweak
        $agency_emails = "";
        if (stristr($this->input->post('agency_emails'), "\n")) {
            $agency_emails_exp = explode("\n", $this->input->post('agency_emails'));
            foreach ($agency_emails_exp as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL))
                    $agency_emails .= $email . "\n";
            }
        } else {
            if (filter_var($this->input->post('agency_emails'), FILTER_VALIDATE_EMAIL))
                $agency_emails = $_POST['agency_emails'];
        }        
        $agency_emails = trim($agency_emails);
        $orig_agency_emails = trim($this->input->post('og_agency_emails'));

        ##Accounts Email Tweak
        $account_emails = "";
        if (stristr($this->input->post('account_emails'), "\n")) {
            $account_emails_exp = explode("\n", $this->input->post('account_emails'));
            foreach ($account_emails_exp as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL))
                    $account_emails .= $email . "\n";
            }
        } else {
            if (filter_var($this->input->post('account_emails'), FILTER_VALIDATE_EMAIL))
                $account_emails = $_POST['account_emails'];
        }

        $account_emails = trim($account_emails);
        $orig_account_emails = trim($this->input->post('og_account_emails'));

        ## POST DATA END >>>>>

        ##get current agency status
        $curr_agency_det_params = array('sel_query'=>'a.status', 'country_id'=> COUNTRY, 'agency_id'=>$agency_id );
        $curr_agency_det = $this->agency_model->get_agency($curr_agency_det_params)->row_array();

        ## Update Query
        if($agency_id!="" && is_numeric($agency_id)){ //Validate

            ##EMAIL
            if( ##If fields are edited trigger email
            
                ( $tab == 1 && ( $orig_legal_name!=$legal_name || $orig_abn!=$abn || $orig_agency_emails!=trim($this->input->post('agency_emails')) || $orig_account_emails!=trim($this->input->post('account_emails'))) )
                
            ){

                ##email notification
                if($curr_agency_det['status']=='active'){ #email update changes for active agency only

                    $email_params = array(
                        'agency_id' => $agency_id,
                        'post' => $postData
                    );
                    $this->email_agency_update($email_params);

                }
               
            }
            ##EMAIL END


            ##Update Agency
            if($tab==1){ ## >>>>>>>>>>>>>>>>>>>>>>>>>>>>

                $this->load->model('properties_model');
                /*$agency_data = array(
                    'phone' => $phone,
                    'comment' => $comment,
                    'statements_agency_comments' => $statements_agency_comments,
                    'statements_agency_comments_ts' => $statements_agency_comments_ts,
                    'agency_name' => $agency_name,
                    'legal_name' => $legal_name,
                    'abn' => $abn,
                    'joined_sats' => $joined_sats,
                    'team_meeting' => $team_meeting,
                    'website' => $website
                );*/
                $agency_data = array(
                    'legal_name' => $legal_name,
                    'abn' => $abn,
                    'team_meeting' => $team_meeting,
                    ##'website' => $website,  //tranfered to main page
                    ##'joined_sats' => $joined_sats, //tranfered to main page
                    'agency_emails' => $agency_emails,
                    'account_emails' => $account_emails
                );
                $update_agency = $this->agency_model->update_agency($agency_id,$agency_data);

                ##LOGS > Fields Edited
                $this->_insertAgencyLogs($postData,1);

            }elseif($tab==2){ ##Contact Details >>>>>>>>>>>>>>>>>>>>>>>>>>>> REMOVED

                ##LOGS > Fields Edited
                //$this->_insertAgencyLogs($postData,2);

            }elseif($tab==3){ ##Portal Users Tab >>>>>>>>>>>>>>>>>>>>>>>>>>>>

                $pm_id =$this->input->post('pm_id');
                $pm_user_type =$this->input->post('pm_user_type');
                $pm_fname =$this->input->post('pm_fname');
                $pm_lname =$this->input->post('pm_lname');
                $pm_job_title =$this->input->post('pm_job_title');
                $pm_phone =$this->input->post('pm_phone');
                $pm_email =$this->input->post('pm_email');

                ## orig values
                $og_pm_user_type =$this->input->post('og_pm_user_type');
                $og_pm_fname =$this->input->post('og_pm_fname');
                $og_pm_lname =$this->input->post('og_pm_lname');
                $og_pm_job_title =$this->input->post('og_pm_job_title');
                $og_pm_phone =$this->input->post('og_pm_phone');
                $og_pm_email =$this->input->post('og_pm_email');

                ##New update code
                if($pm_id && $pm_id!=""){

                    ##update agency user
                    $update_data = array(
                        'user_type' => $pm_user_type,
                        'fname' => $pm_fname,
                        'lname' => $pm_lname,
                        'job_title' => $pm_job_title,
                        'phone' => $pm_phone,
                        'email' => $pm_email
                    );
                    $this->db->where('agency_user_account_id', $pm_id);
                    $this->db->update('agency_user_accounts', $update_data);
                    $this->db->limit(1);

                    ##insert log
                    if($og_pm_user_type!=$pm_user_type){
                        $orig_usertype = $this->_userTypeConvertToString($og_pm_user_type);
                        $new_usertype = $this->_userTypeConvertToString($pm_user_type);
                        $log_details = "<strong>Portal user {$og_pm_fname} {$og_pm_lname}</strong> updated User Type from {$orig_usertype} to {$new_usertype}";
                        $pricing_log_params = array(
                            'title' => 46,
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($pricing_log_params);
                    }
                    if($og_pm_fname!=$pm_fname){
                        $log_details = "<strong>Portal user {$og_pm_fname} {$og_pm_lname}</strong> updated First Name from {$og_pm_fname} to {$pm_fname}";
                        $pricing_log_params = array(
                            'title' => 46,
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($pricing_log_params);
                    }
                    if($pm_lname!=$og_pm_lname){
                        $log_details = "<strong>Portal user {$og_pm_fname} {$og_pm_lname}</strong> updated Last Name from {$og_pm_lname} to {$pm_lname}";
                        $pricing_log_params = array(
                            'title' => 46,
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($pricing_log_params);
                    }
                    if($pm_job_title!=$og_pm_job_title){
                        $log_details = "<strong>Portal user {$og_pm_fname} {$og_pm_lname}</strong> updated Position from {$og_pm_job_title} to {$pm_job_title}";
                        $pricing_log_params = array(
                            'title' => 46,
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($pricing_log_params);
                    }
                    if($pm_phone!=$og_pm_phone){
                        $log_details = "<strong>Portal user {$og_pm_fname} {$og_pm_lname}</strong> updated Phone from {$og_pm_phone} to {$pm_phone}";
                        $pricing_log_params = array(
                            'title' => 46,
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($pricing_log_params);
                    }
                    if($pm_email!=$og_pm_email){
                        $log_details = "<strong>Portal user {$og_pm_fname} {$og_pm_lname}</strong> updated Email from {$og_pm_email} to {$pm_email}";
                        $pricing_log_params = array(
                            'title' => 46,
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($pricing_log_params);
                    }
                     ##insert log end

                }else{ ##insert new agency user account

                     ##Insert Query
                     $insert_data = array(
                        'fname' => $pm_fname,
                        'lname' => $pm_lname,
                        'job_title' => $pm_job_title,
                        'phone' => $pm_phone,
                        'email' => $pm_email,
                        'user_type' => $pm_user_type,
                        'date_created' => date('Y-m-d H:i:s'),
                        'agency_id' => $agency_id

                    );
                    $this->db->insert('agency_user_accounts', $insert_data);

                    ##insert log
                    $log_details = "<strong>{$pm_fname} {$pm_lname}</strong> added as a new portal users.";
                    $pricing_log_params = array(
                        'title' => 46,
                        'details' => $log_details,
                        'display_in_vad' => 1,
                        'created_by_staff' => $this->session->staff_id,
                        'agency_id' => $agency_id
                    );
                    $this->system_model->insert_log($pricing_log_params);

                }
                ##New insert code end

                ##Old insert code
                /*foreach($pm_id as $i => $val){ ##disabled > update from array of fields to single fancybox popup
                    if($val!=""){ ## Update

                        ##Update Query
                        $update_data = array(
                            'user_type' => $pm_user_type[$i],
                            'fname' => $pm_fname[$i],
                            'lname' => $pm_lname[$i],
                            'job_title' => $pm_job_title[$i],
                            'phone' => $pm_phone[$i],
                            'email' => $pm_email[$i]
                        );
                        $this->db->where('agency_user_account_id', $val);
                        $this->db->update('agency_user_accounts', $update_data);

                        ##insert log
                        if($og_pm_user_type[$i]!=$pm_user_type[$i]){
                            $orig_usertype = $this->_userTypeConvertToString($og_pm_user_type[$i]);
                            $new_usertype = $this->_userTypeConvertToString($pm_user_type[$i]);
                            $log_details = "<strong>Portal user {$og_pm_fname[$i]} {$og_pm_lname[$i]}</strong> updated User Type from {$orig_usertype} to {$new_usertype}";
                            $pricing_log_params = array(
                                'title' => 46,
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($pricing_log_params);
                        }elseif($og_pm_fname[$i]!=$pm_fname[$i]){
                            $log_details = "<strong>Portal user {$og_pm_fname[$i]} {$og_pm_lname[$i]}</strong> updated First Name from {$og_pm_fname[$i]} to {$pm_fname[$i]}";
                            $pricing_log_params = array(
                                'title' => 46,
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($pricing_log_params);
                        }elseif($pm_lname[$i]!=$og_pm_lname[$i]){
                            $log_details = "<strong>Portal user {$og_pm_fname[$i]} {$og_pm_lname[$i]}</strong> updated Last Name from {$og_pm_lname[$i]} to {$pm_lname[$i]}";
                            $pricing_log_params = array(
                                'title' => 46,
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($pricing_log_params);
                        }elseif($pm_job_title[$i]!=$og_pm_job_title[$i]){
                            $log_details = "<strong>Portal user {$og_pm_fname[$i]} {$og_pm_lname[$i]}</strong> updated Position from {$og_pm_job_title[$i]} to {$pm_job_title[$i]}";
                            $pricing_log_params = array(
                                'title' => 46,
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($pricing_log_params);
                        }elseif($pm_phone[$i]!=$og_pm_phone[$i]){
                            $log_details = "<strong>Portal user {$og_pm_fname[$i]} {$og_pm_lname[$i]}</strong> updated Position from {$og_pm_phone[$i]} to {$pm_phone[$i]}";
                            $pricing_log_params = array(
                                'title' => 46,
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($pricing_log_params);
                        }elseif($pm_email[$i]!=$og_pm_email[$i]){
                            $log_details = "<strong>Portal user {$og_pm_fname[$i]} {$og_pm_lname[$i]}</strong> updated Position from {$og_pm_email[$i]} to {$pm_email[$i]}";
                            $pricing_log_params = array(
                                'title' => 46,
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($pricing_log_params);
                        }
                         ##insert log

                    }else{ ## Insert

                        ##Insert Query
                        $insert_data = array(
                            'fname' => $pm_fname[$i],
                            'lname' => $pm_lname[$i],
                            'job_title' => $pm_job_title[$i],
                            'phone' => $pm_phone[$i],
                            'email' => $pm_email[$i],
                            'user_type' => $pm_user_type[$i],
                            'date_created' => date('Y-m-d H:i:s'),
                            'agency_id' => $agency_id

                        );
                        $this->db->insert('agency_user_accounts', $insert_data);

                        ##insert log
                        $log_details = "<strong>{$pm_fname[$i]} {$pm_lname[$i]}</strong> added as a new portal users.";
                        $pricing_log_params = array(
                            'title' => 46,
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($pricing_log_params);

                    }
                } */

            }elseif($tab==4){ ##Pricing tab >>>>>>>>>>>>>>>>>>>>>>>>>>>>

                ### SERVICES UPDATE
                if($postData['btn-submit-services'] && $postData['btn-submit-services']=="btn-submit-services"){

                    $agency_service_approve = $this->input->post('agency_service_approve');
                    $service_id = $this->input->post('service_id');
                    $service_price = $this->input->post('service_price');
                    $agency_service_orig_price = $this->input->post('agency_service_orig_price');
                    $agency_service_checked_orig = $this->input->post('agency_service_checked_orig');
                    $services_checked = $this->input->post('services_checked');
                    $service_name = $this->input->post('service_name');
                    $is_price_increase_excluded = $this->input->post('is_price_increase_excluded');
                    
                    ##Delete/Clear agency services
                    $this->db->where('agency_id', $agency_id);
                    $this->db->delete('agency_services');

                    foreach($agency_service_approve as $manual_index){

                        //if($service_price[$manual_index]!="" && $service_price[$manual_index]>0){

                            // agency is excluded to price increase (price variation), use original price and make it required
                            if( $is_price_increase_excluded == 1 ){ 

                                if( $service_price[$manual_index] > 0 ){

                                    ##Insert into agency_services
                                    $agency_services_data = array(
                                        'agency_id'=> $agency_id,
                                        'service_id'=> $service_id[$manual_index],
                                        'price'=> $service_price[$manual_index]
                                    );
                                    $add_agency_services = $this->agency_model->add_agency_services($agency_services_data);

                                }                                

                            }else{ // agency uses price increase (price variation), price is not required, it will insert 0 for empty

                                $dynamic_price = ( $service_price[$manual_index] > 0 )?$service_price[$manual_index]:0;
                                
                                ##Insert into agency_services
                                $agency_services_data = array(
                                    'agency_id'=> $agency_id,
                                    'service_id'=> $service_id[$manual_index],
                                    'price'=> $dynamic_price
                                );
                                $add_agency_services = $this->agency_model->add_agency_services($agency_services_data);

                            }
                            
                            

                            ##Add log start
                           /* if($add_agency_services){
                               

                                if( $agency_service_approve['manual_index']!="" && $agency_service_checked_orig['manual_index']==0 ){
                                    $log_details = "<strong>$".number_format($agency_service_orig_price[$manual_index],2)."</strong> <strong>".$service_price[$key]."</strong> approved";
                                }elseif($agency_service_approve['manual_index']=="" && $agency_service_checked_orig['manual_index']==1 ){
                                    $log_details = "<strong>$".number_format($agency_service_orig_price[$manual_index],2)."</strong> <strong>".$service_price[$key]."</strong> unapproved";
                                }

                                if($agency_service_approve['manual_index']!="" && ($agency_service_orig_price[$manual_index] != $service_price[$manual_index])){
                                    $log_details = "Service price has been updated from <strong>\${$agency_service_orig_price[$manual_index]}</strong> to <strong>\$".number_format($service_price[$manual_index],2)."</strong>";
                              
                                }

                                 $pricing_log_params = array(
                                    'title' => 81, // Service Price Updated
                                    'details' => $log_details,
                                    'display_in_vad' => 1,
                                    'created_by_staff' => $this->session->staff_id,
                                    'agency_id' => $agency_id
                                );
                                $this->system_model->insert_log($pricing_log_params);

                            }*/
                            ##Add log end
                        //}
                        
                    }

                    $data_arr = array();
                    $data_arr2 = array();

                    foreach($services_checked as $key => $value){

                        if($service_price[$key]!="" && $service_price[$key]>0){

                            if($agency_service_checked_orig[$key]!= $value){

                                if($value==1){
                                    $data_arr[] = "<strong>$".number_format($service_price[$key],2)."</strong> <strong>".$service_name[$key]."</strong> approved";
                                }else{
                                    $data_arr[] = "<strong>$".number_format($agency_service_orig_price[$key],2)."</strong> <strong>".$service_name[$key]."</strong> unapproved";
                                }

                            }else if( $service_price[$key] != $agency_service_orig_price[$key] &&  $value==1){
                                $data_arr2[] = "Service price has been updated from <strong>$".$agency_service_orig_price[$key]."</strong> to <strong>".number_format($service_price[$key],2)."</strong>";
                            }

                        }

                    }

                    if(!empty($data_arr2)){

                        $log_details = implode(", ", $data_arr2);
                        $pricing_log_params = array(
                            'title' => 81, // Service Price Updated
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($pricing_log_params);

                    }

                    if(!empty($data_arr)){

                        $log_details = implode(", ", $data_arr);
                        $pricing_log_params = array(
                            'title' => 81, // Service Price Updated
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($pricing_log_params);

                    }

                }
                ### SERVICES UPDATE END

                $this->db->reset_query(); ## reset ci builder query

                ### ALARMS PRICE UPDATE
                $notfree = 0;

                if($postData['btn-submit-alarms'] && $postData['btn-submit-alarms']=="btn-submit-alarms"){

                    $agency_alarm_approve = $this->input->post('agency_alarm_approve');
                    $alarm_price = $this->input->post('alarm_price');	
                    $alarm_id = $this->input->post('alarm_id');
                    $agency_alarms_orig_price = $this->input->post('agency_alarms_orig_price');
                    $alarm_name = $this->input->post('alarm_name');
                    $alarm_checked = $this->input->post('alarm_checked');
                    $alarm_orig = $this->input->post('alarm_orig');
                    

                    ##Delete/clear agency alarms
                    $this->db->where('agency_id', $agency_id);
                    $this->db->delete('agency_alarms');
                    
                    foreach( $agency_alarm_approve as $manual_index ){

                        $agency_alarms_data = array(
                            'agency_id' => $agency_id,
                            'alarm_pwr_id' => $alarm_id[$manual_index],
                            'price' => $alarm_price[$manual_index]
                        );

                        $alarm_pwr_id = $alarm_id[$manual_index];

                        //echo $alarm_price[$manual_index];

                        //Check if allowed to set price $0
                        $check_free = $this->agency_model->check_free_alarms($alarm_pwr_id);

                        if(!empty($check_free)){
                            $this->agency_model->add_agency_alarms($agency_alarms_data);
                        }
                        
                        else if(empty($check_free) && $alarm_price[$manual_index] == "0.00"){
                            $notfree = 1;
                        }
                        /*
                        if( in_array( $alarm_id[$manual_index], $this->config->item('alarm_allowed_zero_price') ) ){
                           
                            $this->agency_model->add_agency_alarms($agency_alarms_data);

                        }
                        */

                        else{
                            if($alarm_price[$manual_index]!="" && $alarm_price[$manual_index]>0){
                                $this->agency_model->add_agency_alarms($agency_alarms_data);
                            }

                        }

                    }

                    $data_arr = array();
                    $data_arr2 = array();
                    foreach($alarm_checked as $key => $value){

                        if($alarm_price[$key]!=""){

                            if($alarm_orig[$key]!= $value){

                                if($value==1){
                                    $data_arr[] = "<strong>$".number_format($alarm_price[$key],2)."</strong> <strong>".$alarm_name[$key]."</strong> approved";
                                }else{
                                    $data_arr[] = "<strong>$".$agency_alarms_orig_price[$key]."</strong> <strong>".$alarm_name[$key]."</strong> unapproved";
                                }

                            }else if( $alarm_price[$key] != $agency_alarms_orig_price[$key] &&  $value==1){
                                $data_arr2[] = "Alarm price has been updated from <strong>$".$agency_alarms_orig_price[$key]."</strong> to <strong>".number_format($alarm_price[$key],2)."</strong>";
                            }

                        }

                    }

                    if(!empty($data_arr2)){

                        $log_details = implode(", ", $data_arr2);
                        $log_params = array(
                            'title' => 82, // Alarm Price Updated
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($log_params);

                    }

                    if(!empty($data_arr)){

                        $log_details = implode(", ", $data_arr);
                        $log_params = array(
                            'title' => 83, // Alarm approved/unapproved
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'agency_id' => $agency_id
                        );
                        $this->system_model->insert_log($log_params);

                    }

                }
                ### ALARMS PRICE UPDATE END

                ###Update agency table (Agency Special Deal and Multi-owner Discount)
                if($postData['btn-submit-especial-deal'] && $postData['btn-submit-especial-deal']=="btn-submit-especial-deal"){
                    $agency_data = array(
                        'agency_special_deal' => $this->input->post('agency_special_deal'),
                        'multi_owner_discount' => $this->input->post('multi_owner_discount')
                    );
                    $update_agency = $this->agency_model->update_agency($agency_id,$agency_data);
                }

                ##insert log for pricing (edited fields)
                $this->_insertAgencyLogs($postData,4);

            }elseif($tab==5){ ##Preferences >>>>>>>>>>>>>>>>>>>>>>>>>>>>
                
                ##POST VALUE
                $send_emails = $this->input->post('send_emails');
                $send_combined_invoice = $this->input->post('send_combined_invoice');
                $send_entry_notice = $this->input->post('send_entry_notice');
                $work_order_required = $this->input->post('work_order_required');
                $allow_indiv_pm_email_cc = $this->input->post('allow_indiv_pm_email_cc');
                $auto_renew = $this->input->post('auto_renew');
                $key_allowed = $this->input->post('key_allowed');
                $key_email_req = $this->input->post('key_email_req');
                $allow_dk = $this->input->post('allow_dk');
                $allow_en = $this->input->post('allow_en');
                $new_job_email_to_agent = $this->input->post('new_job_email_to_agent');
                $display_bpay = $this->input->post('display_bpay');
                $allow_upfront_billing = $this->input->post('allow_upfront_billing');
                $invoice_pm_only = $this->input->post('invoice_pm_only');
                $electrician_only = $this->input->post('electrician_only');
                $send_en_to_agency = $this->input->post('send_en_to_agency');
                $en_to_pm = $this->input->post('en_to_pm');
                $accounts_reports = $this->input->post('accounts_reports');
                $exclude_free_invoices = $this->input->post('exclude_free_invoices');
                $send_48_hr_key = $this->input->post('send_48_hr_key');
                $add_inv_to_agen = $this->input->post('add_inv_to_agen');
                $hide_2022_compliant = $this->input->post('hide_2022_compliant');   
                $og_hide_2022_compliant = $this->input->post('og_hide_2022_compliant');  
                $renewal_interval = ( $this->input->post('renewal_interval') > 0 )?$this->input->post('renewal_interval'):null;
                $renewal_start_offset = ( is_numeric($this->input->post('renewal_start_offset')) )?$this->input->post('renewal_start_offset'):null; 
                $agency_state = $this->input->post('agency_state'); 

                ##agency data array
                $agency_data = array(
                    'send_emails' => $send_emails,
                    'send_combined_invoice' => $send_combined_invoice,
                    'send_entry_notice' => $send_entry_notice,
                    'require_work_order' => $work_order_required,
                    'allow_indiv_pm_email_cc' => $allow_indiv_pm_email_cc,
                    'auto_renew' => $auto_renew,
                    'key_allowed' => $key_allowed,
                    'key_email_req' => $key_email_req,
                    'allow_dk' => $allow_dk,
                    'allow_en' => $allow_en,
                    'new_job_email_to_agent' => $new_job_email_to_agent,
                    'display_bpay' => $display_bpay,
                    'allow_upfront_billing' => $allow_upfront_billing,
                    'invoice_pm_only' => $invoice_pm_only,
                    'electrician_only' => $electrician_only,
                    'send_en_to_agency' => $send_en_to_agency,
                    'en_to_pm' => $en_to_pm,
                    'accounts_reports' => $accounts_reports,
                    'exclude_free_invoices' => $exclude_free_invoices,
                    'send_48_hr_key' => $send_48_hr_key,
                    'add_inv_to_agen' => $add_inv_to_agen
                );
                $update_agency = $this->agency_model->update_agency($agency_id,$agency_data);

                // insert/update using new preference table
                $agency_pref_arr = $this->input->post('agency_pref');   
                
                foreach( $agency_pref_arr as $agency_pref_id => $agency_pref_val ){

                    if( $agency_id > 0 && $agency_pref_id > 0 ){

                        // check if agency preference already exist
                        $agency_pref_sel_sql = $this->db->query("
                        SELECT COUNT(`id`) AS aps_count
                        FROM `agency_preference_selected`
                        WHERE `agency_id` = {$agency_id}
                        AND `agency_pref_id` = {$agency_pref_id}
                        ");

                        if( $agency_pref_sel_sql->row()->aps_count > 0 ){ // exist, update

                            $update_data = array(
                                'sel_pref_val' => $agency_pref_val
                            );                        
                            $this->db->where('agency_id', $agency_id);
                            $this->db->where('agency_pref_id', $agency_pref_id);
                            $this->db->update('agency_preference_selected', $update_data);

                            if ($agency_pref_val == 1) {
                                $from = "NO";
                                $to = "YES";
                            } else {
                                $from = "YES";
                                $to = "NO";
                            }
                            $log_details = "<strong>Preferences Paid alarms</strong> Updated from {$from} to {$to}";
                            $log_params = array(
                                'title' => 46, // agency
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($log_params);

                        }else{ // new, insert

                            $insert_data = array(
                                'agency_id' => $agency_id,
                                'agency_pref_id' => $agency_pref_id,
                                'sel_pref_val' => $agency_pref_val
                            );                            
                            $this->db->insert('agency_preference_selected', $insert_data);

                            if ($agency_pref_val == 1) {
                                $from = "";
                                $to = "YES";
                            } else {
                                $from = "";
                                $to = "NO";
                            }
                            $log_details = "<strong>Preferences Paid alarms</strong> Updated from {$from} to {$to}";
                            $log_params = array(
                                'title' => 46, // agency
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($log_params);

                        }

                    }                    

                }

                ##update agency markers (Hide 'Not 2022 Compliant' for short term rentals)
                if( $og_hide_2022_compliant!= $hide_2022_compliant){ ##trigger > validate to prevent duplicate data
                    
                    if( $hide_2022_compliant == 1 ){ //insert to agency_markers table

                        if( $agency_id!="" ){
                            $this->db->where('agency_id', $agency_id);
                            $this->db->where('marker_id', 1); ## hide 'Not 2022 Compliant' for short term rentals?
                            $this->db->delete('agency_markers');
                        }

                    }else{ ##delete from agency_markers table

                        if( $agency_id!="" ){
                            $data_tt = array(
                                'agency_id' => $agency_id,
                                'marker_id' => 1 ## 1 = do not hide else hide Hide 'Not 2022 Compliant' for short term rentals?
                            );
                            $this->db->insert('agency_markers', $data_tt);
                        }

                    }
                
                }

                if( $agency_id > 0 && $agency_state != 'QLD' ){

                    // check if lockbox exist
                    $lb_sql = $this->db->query("
                    SELECT COUNT(`id`) AS aop_count
                    FROM `agency_other_pref`
                    WHERE `agency_id` = {$agency_id}
                    AND `active` = 1
                    ");
                    $lb_row = $lb_sql->row();

                    if( $lb_row->aop_count > 0 ){ // it exist, update

                        $ri_update_data = array(
                            'renewal_interval' => $renewal_interval,
                            'renewal_start_offset' => $renewal_start_offset
                        );
                        
                        $this->db->where('agency_id', $agency_id);
                        $this->db->update('agency_other_pref', $ri_update_data);

                    }else{

                        if( $renewal_interval > 0 || is_numeric($renewal_start_offset) ){

                            // other agency preference
                            $ri_insert_data = array(
                                'renewal_interval' => $renewal_interval,
                                'renewal_start_offset' => $renewal_start_offset,
                                'agency_id' => $agency_id
                            );
                            
                            $this->db->insert('agency_other_pref', $ri_insert_data);

                        }                        

                    }

                }                                

                ##insert log for pricing (edited fields)
                $this->_insertAgencyLogs($postData,5);
            }elseif($tab==10){ //Accounts tab > Statement Message
                
                ##Agency statement commets TS tweak
                if($statements_agency_comments == $orig_statements_agency_comments && $statements_agency_comments!=""){
                    $statements_agency_comments_ts = $statements_agency_comments_ts_post;
                }else{
                    $statements_agency_comments_ts = date('Y-m-d H:i:s');
                }
                
                $agency_data = array(
                    'statements_agency_comments' => $statements_agency_comments,
                    'statements_agency_comments_ts' => $statements_agency_comments_ts,
                );
                $update_agency = $this->agency_model->update_agency($agency_id,$agency_data);

                ##LOGS > Fields Edited
                $this->_insertAgencyLogs($postData,1);
            }

            if(!empty($ret_arr['cannot_nlm_prop_id_arr'])){ ## found property with active job > set session custom message
                
                ##UPDATE SUCCES REDIRECTION, STATUS AND SUCCESS MESSAGE
                $this->session->set_flashdata('update_agency_success', 1);

                $nlm_status_text = "These Properties has an active jobs, cant be deactivated <br/><br/>";

                foreach($ret_arr['cannot_nlm_address_arr'] as $address_val){
                    $nlm_status_text .= $address_val."<br/>";
                }

                $this->session->set_flashdata('update_agency_success_msg', $nlm_status_text);
           
            }else{ ## default update message
                
                if($notfree == 1){
                    ##UPDATE SUCCES REDIRECTION, STATUS AND SUCCESS MESSAGE
                    $this->session->set_flashdata('update_not_free_error', 1);
                    $this->session->set_flashdata('update_not_free_msg', "Error: This alarm is not permitted to be free.");
                }
                else{
                    ##UPDATE SUCCES REDIRECTION, STATUS AND SUCCESS MESSAGE
                    $this->session->set_flashdata('update_agency_success', 1);
                    $this->session->set_flashdata('update_agency_success_msg', "Update Successful");
                }
           
            }
            redirect("/agency/view_agency_details/{$agency_id}/{$tab}");

        }

    }

    private function _insertAgencyLogs($post, $tab){

        $edited_field_arr = [];

        if($tab==1){ ##Agency details tab

                //address
                if( $post['address_type']!="" ){ //log for update new agency_addresses

                    if( $post['fullAdd']!=$post['og_fullAdd'] ){ 
                        $edited_field_arr[] = array(
                            'field' => $this->agency_model->vad_address_type_name($post['address_type']),
                            'msg' => " at <strong>{$post['og_fullAdd']}</strong> updated to <strong>{$post['fullAdd']}</strong>"
                        );
                    }

                }else{ //log for default agency address update

                    if( $post['fullAdd']!=$post['og_fullAdd'] ){ 
                        $edited_field_arr[] = array(
                            'field' => "Agency Address",
                            'msg' => "Updated from {$post['og_fullAdd']} to {$post['fullAdd']}"
                        );
                    }
                    //state
                    if( $post['state']!=$post['og_state'] ){ 
                        $edited_field_arr[] = array(
                            'field' => "State",
                            'msg' => "Updated from {$post['og_state']} to {$post['state']}"
                        );
                    }
    
                    //region
                    if( $post['postcode_region_name']!=$post['og_postcode_region_name'] ){
                        $edited_field_arr[] = array(
                            'field' => "Region",
                            'msg' => "Updated from {$this->format_empty_val($post['og_postcode_region_name'])} to {$this->format_empty_val($post['postcode_region_name'])}"
                        );
                    }

                }

                //franchise group
                if( $post['franchise_group']!=$post['og_franchise_group'] ){

                    $old_franchise_q = $this->db->select('name')->from('franchise_groups')->where('franchise_groups_id', $post['og_franchise_group'])->get()->row_array();
                    $old_franchise_name = $old_franchise_q['name'];

                    $new_franchise_q = $this->db->select('name')->from('franchise_groups')->where('franchise_groups_id', $post['franchise_group'])->get()->row_array();
                    $new_franchise_name = $new_franchise_q['name'];

                    $edited_field_arr[] = array(
                        'field' => "Franchise Group",
                        'msg' => "Updated from {$this->format_empty_val($old_franchise_name)} to {$this->format_empty_val($new_franchise_name)}"
                    );
                }
                //landline
                if( $post['phone']!=$post['og_phone'] ){
                    $edited_field_arr[] = array(
                        'field' => "Landline",
                        'msg' => "Updated from {$this->format_empty_val($post['og_phone'])} to {$this->format_empty_val($post['phone'])}"
                    );
                }
                //agency comments
                if( $post['comment']!=$post['og_comment'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Comments",
                        'msg' => "Updated from {$this->format_empty_val($post['og_comment'])} to {$this->format_empty_val($post['comment'])}"
                    );
                }
                //status
                if( $post['status']!=$post['og_status'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Status",
                        'msg' => "Updated from {$post['og_status']} to {$post['status']}"
                    );
                }
                //sales rep
                if( $post['salesrep']!=$post['og_salesrep'] ){

                    $og_salesrep_params = array('sel_query' => 'FirstName, LastName','staff_id'=>$post['og_salesrep']);
                    $og_salesrep_row = $this->gherxlib->getStaffInfo($og_salesrep_params)->row_array();
                    $og_salesrep = $this->gherxlib->formatStaffName($og_salesrep_row['FirstName'], $og_salesrep_row['LastName']);

                    $salesrep_params = array('sel_query' => 'FirstName, LastName','staff_id'=>$post['salesrep']);
                    $salesrep_row = $this->gherxlib->getStaffInfo($salesrep_params)->row_array();
                    $salesrep = $this->gherxlib->formatStaffName($salesrep_row['FirstName'], $salesrep_row['LastName']);

                    $edited_field_arr[] = array(
                        'field' => "Sales Rep",
                        'msg' => "Updated from {$this->format_empty_val($og_salesrep)} to {$this->format_empty_val($salesrep)}"
                    );
                }
                //Active Properties with SATS
                if( $post['og_active_prop_with_sats']!=$post['active_prop_with_sats'] ){
                    $edited_field_arr[] = array(
                        'field' => "Active Properties with SATS",
                        'msg' => "Updated from {$this->format_empty_val($post['og_active_prop_with_sats'])} to {$this->format_empty_val($post['active_prop_with_sats'])}"
                    );
                }
                //Changing To/Agency using
                if( $post['og_agency_using']!=$post['agency_using'] ){
                    $agency_using = $this->agency_model->getAgencyUsingByCountry_new_od_val($post['agency_using']);
                    $og_agency_using = $this->agency_model->getAgencyUsingByCountry_new_od_val($post['og_agency_using']);
                    $edited_field_arr[] = array(
                        'field' => "Agency Using",
                        'msg' => "Updated from {$this->format_empty_val($og_agency_using)} to {$this->format_empty_val($agency_using)}"
                    );
                }
                //Reason they Left
                if( $post['og_deactivate_reason']!=$post['deactivate_reason'] ){
                    $edited_field_arr[] = array(
                        'field' => "Deactivated Reason",
                        'msg' => "Updated from {$this->format_empty_val($post['og_deactivate_reason'])} to {$this->format_empty_val($post['deactivate_reason'])}"
                    );
                }
                //Agency Statement Message
                if( $post['og_statements_agency_comments']!=$post['statements_agency_comments'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Statement Message",
                        'msg' => "Updated from {$this->format_empty_val($post['og_statements_agency_comments'])} to {$this->format_empty_val($post['statements_agency_comments'])}"
                    );
                }
                //Agency name
                if( $post['agency_name']!=$post['og_agency_name'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Name",
                        'msg' => "Updated from {$this->format_empty_val($post['og_agency_name'])} to {$this->format_empty_val($post['agency_name'])}"
                    );
                }
                //Legal name
                if( $post['legal_name']!=$post['og_legal_name'] ){
                    $edited_field_arr[] = array(
                        'field' => "Legal Name",
                        'msg' => "Updated from {$this->format_empty_val($post['og_legal_name'])} to {$this->format_empty_val($post['legal_name'])}"
                    );
                }
                //ABN name
                if( $post['abn']!=$post['og_abn'] ){
                    $edited_field_arr[] = array(
                        'field' => "ABN Name",
                        'msg' => "Updated from {$this->format_empty_val($post['og_abn'])} to {$this->format_empty_val($post['abn'])}"
                    );
                }
                //Total Properties
                if( $post['tot_properties']!=$post['og_tot_properties'] ){
                    $edited_field_arr[] = array(
                        'field' => "Total Properties",
                        'msg' => "Updated from {$this->format_empty_val($post['og_tot_properties'])} to {$this->format_empty_val($post['tot_properties'])}"
                    );
                }
                //Joined SATS
                if( $post['joined_sats']!=$post['og_joined_sats'] ){
                    $edited_field_arr[] = array(
                        'field' => "Joined SATS",
                        'msg' => "Updated from {$this->format_empty_val($post['og_joined_sats'])} to {$this->format_empty_val($post['joined_sats'])}"
                    );
                }
                //agency_hours
                if( $post['agency_hours']!=$post['og_agency_hours'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Hours",
                        'msg' => "Updated from {$this->format_empty_val($post['og_agency_hours'])} to {$this->format_empty_val($post['agency_hours'])}"
                    );
                }
                //Agency Specific Notes
                if( $post['agency_specific_notes']!=$post['og_agency_specific_notes'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Specific Notes",
                        'msg' => "Updated from {$this->format_empty_val($post['og_agency_specific_notes'])} to {$this->format_empty_val($post['agency_specific_notes'])}"
                    );
                }
                //Team Meeting
                if( $post['team_meeting']!=$post['og_team_meeting'] ){
                    $edited_field_arr[] = array(
                        'field' => "Team Meeting",
                        'msg' => "Updated from {$this->format_empty_val($post['og_team_meeting'])} to {$this->format_empty_val($post['team_meeting'])}"
                    );
                }
                //Website
                if( $post['website']!=$post['og_website'] ){
                    $edited_field_arr[] = array(
                        'field' => "Website",
                        'msg' => "Updated from {$this->format_empty_val($post['og_website'])} to {$this->format_empty_val($post['website'])}"
                    );
                }
                //Trust Acct. Software
                if( $post['trust_acc_soft']!=$post['og_trust_acc_soft'] ){
                    $og_trust_acc_soft = $this->agency_model->get_trusAccountSoftware_new_old_val($post['og_trust_acc_soft']);
                    $trust_acc_soft = $this->agency_model->get_trusAccountSoftware_new_old_val($post['trust_acc_soft']);
                    $edited_field_arr[] = array(
                        'field' => "Trust Acct. Software",
                        'msg' => "Updated from {$this->format_empty_val($og_trust_acc_soft)} to {$this->format_empty_val($trust_acc_soft)}"
                    );
                }
                //Trust Account Agency ID
                if( $post['propertyme_agency_id']!=$post['og_propertyme_agency_id'] ){
                    $edited_field_arr[] = array(
                        'field' => "Trust Account Agency ID",
                        'msg' => "Updated from {$this->format_empty_val($post['og_propertyme_agency_id'])} to {$this->format_empty_val($post['propertyme_agency_id'])}"
                    );
                }
                //Maintenance Provider
                if( $post['maintenance']!=$post['og_maintenance'] ){
                    $og_maintenance = $this->agency_model->get_maintenance_provider_old_new_value($post['og_maintenance']);
                    $new_maintenance = $this->agency_model->get_maintenance_provider_old_new_value($post['maintenance']);

                    $edited_field_arr[] = array(
                        'log_title' => 79, // Maintenance Program
                        'field' => "Maintenance Provider",
                        'msg' => "Updated from {$this->format_empty_val($og_maintenance)} to {$this->format_empty_val($new_maintenance)}"
                    );
                }
                //Surcharge to all Invoices?
                if( $post['m_surcharge']!=$post['og_m_surcharge'] ){
                    $og_m_surcharge = $this->_checkbox_yes_no($post['og_m_surcharge']);
                    $m_surcharge = $this->_checkbox_yes_no($post['m_surcharge']);
                    $edited_field_arr[] = array(
                        'log_title' => 79, // Maintenance Program
                        'field' => "Apply Surcharge to all Invoices",
                        'msg' => "Updated from {$this->format_empty_val($og_m_surcharge)} to {$this->format_empty_val($m_surcharge)}"
                    );
                }
                //Display Message on all Invoices
                if( $post['m_disp_surcharge']!=$post['og_m_disp_surcharge'] ){
                    $og_m_disp_surcharge = $this->_checkbox_yes_no($post['og_m_disp_surcharge']);
                    $m_disp_surcharge = $this->_checkbox_yes_no($post['m_disp_surcharge']);
                    $edited_field_arr[] = array(
                        'log_title' => 79, // Maintenance Program
                        'field' => "Display Message on all Invoices",
                        'msg' => "Updated from {$this->format_empty_val($og_m_disp_surcharge)} to {$this->format_empty_val($m_disp_surcharge)}"
                    );
                }
                //Surcharge
                if( $post['m_price']!=$post['og_m_price'] ){
                    $edited_field_arr[] = array(
                        'log_title' => 79, // Maintenance Program
                        'field' => "Surcharge",
                        'msg' => "Updated from {$this->format_empty_val($post['og_m_price'])} to {$this->format_empty_val($post['m_price'])}"
                    );
                }
                //Invoice Message
                if( $post['m_surcharge_msg']!=$post['og_m_surcharge_msg'] ){
                    $edited_field_arr[] = array(
                        'log_title' => 79, // Maintenance Program
                        'field' => "Invoice Message",
                        'msg' => "Updated from {$this->format_empty_val($post['og_m_surcharge_msg'])} to {$this->format_empty_val($post['m_surcharge_msg'])}"
                    );
                }

                if( $post['ac_fname']!=$post['og_ac_fname'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Contact First Name",
                        'msg' => "Updated from {$this->format_empty_val($post['og_ac_fname'])} to {$this->format_empty_val($post['ac_fname'])}"
                    );
                }
                if( $post['ac_lname']!=$post['og_ac_lname'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Contact Last Name",
                        'msg' => "Updated from {$this->format_empty_val($post['og_ac_lname'])} to {$this->format_empty_val($post['ac_lname'])}"
                    );
                }
                if( $post['ac_phone']!=$post['og_ac_phone'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Contact Phone",
                        'msg' => "Updated from {$this->format_empty_val($post['og_ac_phone'])} to {$this->format_empty_val($post['ac_phone'])}"
                    );
                }
                if( $post['ac_email']!=$post['og_ac_email'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Contact Email",
                        'msg' => "Updated from {$this->format_empty_val($post['og_ac_email'])} to {$this->format_empty_val($post['ac_email'])}"
                    );
                }
                if( $post['acc_name']!=$post['og_acc_name'] ){
                    $edited_field_arr[] = array(
                        'field' => "Acounts Contact Name",
                        'msg' => "Updated from {$this->format_empty_val($this->format_empty_val($post['og_acc_name']))} to {$this->format_empty_val($this->format_empty_val($post['acc_name']))}"
                    );
                }
                if( $post['acc_phone']!=$post['og_acc_phone'] ){
                    $edited_field_arr[] = array(
                        'field' => "Acounts Contact Phone",
                        'msg' => "Updated from {$this->format_empty_val($post['og_acc_phone'])} to {$this->format_empty_val($post['acc_phone'])}"
                    );
                }
                if( $post['tdc_name']!=$post['og_tdc_name'] ){
                    $edited_field_arr[] = array(
                        'field' => "Tenant Details Contact Name",
                        'msg' => "Updated from {$this->format_empty_val($post['og_tdc_name'])} to {$this->format_empty_val($post['tdc_name'])}"
                    );
                }
                if( $post['tdc_phone']!=$post['og_tdc_phone'] ){
                    $edited_field_arr[] = array(
                        'field' => "Tenant Details Contact Phone",
                        'msg' => "Updated from {$this->format_empty_val($post['og_tdc_phone'])} to {$this->format_empty_val($post['tdc_phone'])}"
                    );
                }
                if( $post['agency_emails']!=$post['og_agency_emails'] ){
                    $edited_field_arr[] = array(
                        'field' => "Agency Emails",
                        'msg' => "Updated from {$this->format_empty_val($post['og_agency_emails'])} to {$this->format_empty_val($post['agency_emails'])}"
                    );
                }
                if( $post['account_emails']!=$post['og_account_emails'] ){
                    $edited_field_arr[] = array(
                        'field' => "Accounts Emails",
                        'msg' => "Updated from {$this->format_empty_val($post['og_account_emails'])} to {$this->format_empty_val($post['account_emails'])}"
                    );
                }
                
        }elseif($tab==2){ ##Contact details tab >>>> REMOVED

               

        }elseif($tab==4){ //Pricing tab
            if( $post['agency_special_deal']!=$post['og_agency_special_deal'] ){
                $edited_field_arr[] = array(
                    'field' => "Agency Special Deal",
                    'msg' => "Updated from {$this->format_empty_val($post['og_agency_special_deal'])} to {$this->format_empty_val($post['agency_special_deal'])}"
                );
            }
            if( $post['multi_owner_discount']!=$post['og_multi_owner_discount'] ){
                $edited_field_arr[] = array(
                    'field' => "Multi-owner Discount",
                    'msg' => "Updated from {$this->format_empty_val($post['og_multi_owner_discount'])} to {$this->format_empty_val($post['multi_owner_discount'])}"
                );
            }
        }elseif($tab==5){ //Preferences tab log
            if( $post['send_emails']!=$post['og_send_emails'] ){
                $og_send_emails = $this->_checkbox_yes_no($post['og_send_emails']);
                $send_emails = $this->_checkbox_yes_no($post['send_emails']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Attach invoices to emails",
                    'msg' => "Updated from {$og_send_emails} to {$send_emails}"
                );
            }
            if( $post['send_combined_invoice']!=$post['og_send_combined_invoice'] ){
                $og_send_combined_invoice = $this->_checkbox_yes_no($post['og_send_combined_invoice']);
                $send_combined_invoice = $this->_checkbox_yes_no($post['send_combined_invoice']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Send Combined Certificate and Invoice",
                    'msg' => "Updated from {$og_send_combined_invoice} to {$send_combined_invoice}"
                );
            }
            if( $post['send_entry_notice']!=$post['og_send_entry_notice'] ){
                $og_send_entry_notice = $this->_checkbox_yes_no($post['og_send_entry_notice']);
                $send_entry_notice = $this->_checkbox_yes_no($post['send_entry_notice']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Entry Notice issued by Email",
                    'msg' => "Updated from {$og_send_entry_notice} to {$send_entry_notice}"
                );
            }
            if( $post['work_order_required']!=$post['og_work_order_required'] ){
                $og_work_order_required = $this->_checkbox_yes_no($post['og_work_order_required']);
                $work_order_required = $this->_checkbox_yes_no($post['work_order_required']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Work Order Required For All Jobs",
                    'msg' => "Updated from {$og_work_order_required} to {$work_order_required}"
                );
            }
            if( $post['allow_indiv_pm_email_cc']!=$post['og_allow_indiv_pm_email_cc'] ){
                $og_allow_indiv_pm_email_cc = $this->_checkbox_yes_no($post['og_allow_indiv_pm_email_cc']);
                $allow_indiv_pm_email_cc = $this->_checkbox_yes_no($post['allow_indiv_pm_email_cc']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Individual Property Managers Receive Certificate & Invoice",
                    'msg' => "Updated from {$og_allow_indiv_pm_email_cc} to {$allow_indiv_pm_email_cc}"
                );
            }
            if( $post['auto_renew']!=$post['og_auto_renew'] ){
                $og_auto_renew = $this->_checkbox_yes_no($post['og_auto_renew']);
                $auto_renew = $this->_checkbox_yes_no($post['auto_renew']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Auto Renew Yearly Maintenance Properties",
                    'msg' => "Updated from {$og_auto_renew} to {$auto_renew}"
                );
            }
            if( $post['key_allowed']!=$post['og_key_allowed'] ){
                $og_key_allowed = $this->_checkbox_yes_no($post['og_key_allowed']);
                $key_allowed = $this->_checkbox_yes_no($post['key_allowed']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Key Access Allowed",
                    'msg' => "Updated from {$og_key_allowed} to {$key_allowed}"
                );
            }
            if( $post['key_email_req']!=$post['og_key_email_req'] ){
                $og_key_email_req = $this->_checkbox_yes_no($post['og_key_email_req']);
                $key_email_req = $this->_checkbox_yes_no($post['key_email_req']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Tenant Key Email Required",
                    'msg' => "Updated from {$og_key_email_req} to {$key_email_req}"
                );
            }
            if( $post['allow_dk']!=$post['og_allow_dk'] ){
                $og_allow_dk = $this->_checkbox_yes_no($post['og_allow_dk']);
                $allow_dk = $this->_checkbox_yes_no($post['allow_dk']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Allow Doorknocks",
                    'msg' => "Updated from {$og_allow_dk} to {$allow_dk}"
                );
            }
            if( $post['allow_en']!=$post['og_allow_en'] ){
                $og_allow_en = $this->_checkbox_yes_no($post['og_allow_en']);
                $allow_en = $this->_checkbox_yes_no($post['allow_en']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Allow Entry Notice",
                    'msg' => "Updated from {$og_allow_en} to {$allow_en}"
                );
            }
            if( $post['new_job_email_to_agent']!=$post['og_new_job_email_to_agent'] ){
                $og_new_job_email_to_agent = $this->_checkbox_yes_no($post['og_new_job_email_to_agent']);
                $new_job_email_to_agent = $this->_checkbox_yes_no($post['new_job_email_to_agent']);
                $edited_field_arr[] = array(
                    'field' => "Preferences All New Jobs Emailed to Agency",
                    'msg' => "Updated from {$og_new_job_email_to_agent} to {$new_job_email_to_agent}"
                );
            }
            if( $post['display_bpay']!=$post['og_display_bpay'] ){
                $og_display_bpay = $this->_checkbox_yes_no($post['og_display_bpay']);
                $display_bpay = $this->_checkbox_yes_no($post['display_bpay']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Display BPAY on Invoices",
                    'msg' => "Updated from {$og_display_bpay} to {$display_bpay}"
                );
            }
            if( $post['allow_upfront_billing']!=$post['og_allow_upfront_billing'] ){
                $og_allow_upfront_billing = $this->_checkbox_yes_no($post['og_allow_upfront_billing']);
                $allow_upfront_billing = $this->_checkbox_yes_no($post['allow_upfront_billing']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Subscription Billing",
                    'msg' => "Updated from {$og_allow_upfront_billing} to {$allow_upfront_billing}"
                );
            }
            if( $post['invoice_pm_only']!=$post['og_invoice_pm_only'] ){
                $og_invoice_pm_only = $this->_checkbox_yes_no($post['og_invoice_pm_only']);
                $invoice_pm_only = $this->_checkbox_yes_no($post['invoice_pm_only']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Invoice PM'S Only",
                    'msg' => "Updated from {$og_invoice_pm_only} to {$invoice_pm_only}"
                );
            }
            if( $post['electrician_only']!=$post['og_electrician_only'] ){
                $og_electrician_only = $this->_checkbox_yes_no($post['og_electrician_only']);
                $electrician_only = $this->_checkbox_yes_no($post['electrician_only']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Electrician Only",
                    'msg' => "Updated from {$og_electrician_only} to {$electrician_only}"
                );
            }
            if( $post['send_en_to_agency']!=$post['og_send_en_to_agency'] ){
                $og_send_en_to_agency = $this->_checkbox_yes_no($post['og_send_en_to_agency']);
                $send_en_to_agency = $this->_checkbox_yes_no($post['send_en_to_agency']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Send copy of EN to Agency",
                    'msg' => "Updated from {$og_send_en_to_agency} to {$send_en_to_agency}"
                );
            }
            if( $post['en_to_pm']!=$post['og_en_to_pm'] ){
                $og_en_to_pm = $this->_checkbox_yes_no($post['og_en_to_pm']);
                $en_to_pm = $this->_checkbox_yes_no($post['en_to_pm']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Individual Property Managers Receive EN",
                    'msg' => "Updated from {$og_en_to_pm} to {$en_to_pm}"
                );
            }
            if( $post['accounts_reports']!=$post['og_accounts_reports'] ){
                $og_accounts_reports = $this->_checkbox_yes_no($post['og_accounts_reports']);
                $accounts_reports = $this->_checkbox_yes_no($post['accounts_reports']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Show accounts reports",
                    'msg' => "Updated from {$og_accounts_reports} to {$accounts_reports}"
                );
            }
            if( $post['exclude_free_invoices']!=$post['og_exclude_free_invoices'] ){
                $og_exclude_free_invoices = $this->_checkbox_yes_no($post['og_exclude_free_invoices']);
                $exclude_free_invoices = $this->_checkbox_yes_no($post['exclude_free_invoices']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Exclude $0 invoices",
                    'msg' => "Updated from {$og_exclude_free_invoices} to {$exclude_free_invoices}"
                );
            }
            if( $post['send_48_hr_key']!=$post['og_send_48_hr_key'] ){
                $og_send_48_hr_key = $this->_checkbox_yes_no($post['og_send_48_hr_key']);
                $send_48_hr_key = $this->_checkbox_yes_no($post['send_48_hr_key']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Send 48 hour key email",
                    'msg' => "Updated from {$og_send_48_hr_key} to {$send_48_hr_key}"
                );
            }
            if( $post['hide_2022_compliant']!=$post['og_hide_2022_compliant'] ){
                $og_send_48_hr_key = $this->_checkbox_yes_no($post['og_hide_2022_compliant']);
                $send_48_hr_key = $this->_checkbox_yes_no($post['hide_2022_compliant']);
                $edited_field_arr[] = array(
                    'field' => "Preferences Hide 'Not 2022 Compliant' for short term rentals",
                    'msg' => "Updated from {$og_send_48_hr_key} to {$send_48_hr_key}"
                );
            }

        }elseif($tab==11){// API tab log
            if( $post['connected_service']!=$post['og_edit_api_connected_service'] ){
                $new_edit_api_connected_service = $this->agency_model->getApiSoftweareName($post['connected_service']);
                $og_edit_api_connected_service = $this->agency_model->getApiSoftweareName($post['og_edit_api_connected_service']);
                $edited_field_arr[] = array(
                    'log_title' => 85,
                    'field' => "API integration Software",
                    'msg' => "Updated from {$this->format_empty_val($og_edit_api_connected_service)} to {$this->format_empty_val($new_edit_api_connected_service)}"
                );
            }
            if( $post['status']!=$post['og_edit_api_status'] ){
                $edit_api_status = $this->_checkbox_yes_no($post['status']);
                $og_edit_api_status = $this->_checkbox_yes_no($post['og_edit_api_status']);
                $connected_service = $this->agency_model->getApiSoftweareName($post['connected_service']);
                $edited_field_arr[] = array(
                    'log_title' => 85,
                    'field' => "{$connected_service} Available to Connect",
                    'msg' => "Updated from {$og_edit_api_status} to {$edit_api_status}"
                );
            }
        }

        ##insert logs
        if( !empty($edited_field_arr) ){
            foreach($edited_field_arr as $log_row){
                $title = ($log_row['log_title'] && $log_row['log_title']!="") ? $log_row['log_title'] : 46; // default log title 46 > Agency Update
                $log_details = "<strong>{$log_row['field']}</strong> {$log_row['msg']}";
                $log_params = array(
                    'title' => $title,
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $post['agency_id']
                );
                $this->system_model->insert_log($log_params);
            }
        }
       
    }

    public function ajax_hard_delete_agency(){

        $agency_id = $this->input->post('agency_id');

        if( $agency_id!="" && is_numeric($agency_id) ){

            ##Get property and check agency property exist
            $where_arr = "agency_id = $agency_id AND ( is_nlm = 0 OR is_nlm IS NULL ) AND deleted=0";
            $property_q = $this->db->select('property_id')
                        ->from('property')
                        ->where($where_arr)
                        ->get();

            if($property_q->num_rows()>0){ #Agency has property 

                $json_data['prop_count'] = $property_q->num_rows();

            }else{ #Agency has no property continue delete

                ##Delete agency
               
               /* $this->db->where('agency_id', $agency_id);
                $this->db->delete('agency');
                $this->db->limit(1);*/

                $agency_delete_data = array(
                    'deleted' => 1,
                    'deleted_timestamp' => date("Y-m-d H:i:s")
                );
                $this->db->where('agency_id',$agency_id);
                $this->db->update('agency', $agency_delete_data);

                ##add log
                $s_params = array(
                    'sel_query' => 'sa.FirstName, sa.LastName',
                    'staff_id'  =>$this->session->staff_id
                );
                $s_q = $this->staff_accounts_model->get_staff_accounts($s_params);
                $s_row = $s_q->row_array();
                $s_staffName = $s_row['FirstName']." ".$s_row['LastName'];
                $log_details = "Agency <strong>deleted</strong> by <strong>".$s_staffName."</strong> ";
                $log_params = array(
                    'title' => 84,  
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);
                ##add log end

                $json_data['prop_count'] = 0;

            }

        }

        echo json_encode($json_data);

    }

    public function ajax_activate_deactivate_portal_users(){

        $pm_id = $this->input->post('pm_id');
        $status = $this->input->post('status');
        $agency_id = $this->input->post('agency_id');
        $json_data['status'] = false;
        
        if($pm_id!="" && is_numeric($pm_id)){

            $auc_q = $this->db->select('fname,lname')->from('agency_user_accounts')->where('agency_user_account_id', $pm_id)->get();
            $auc_row = $auc_q->row_array();

            if($status == 1){

                $this->db->where('agency_user_account_id', $pm_id);
                $update_data = array('active'=>1);
                $this->db->update('agency_user_accounts',$update_data);
                $this->db->limit(1);

                ##insert log
                $log_details = "<strong>Portal User {$auc_row['fname']} {$auc_row['lname']}</strong> Restored";
                $log_params = array(
                    'title' => 46,  //Agency Updated
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);
                ##insert log end
    
                $json_data['status'] = true;
                $json_data['msg'] = "User successfully restored";
    
            }else{
    
                $this->db->select('*');
                $this->db->from('property');
                $this->db->where('pm_id_new', $pm_id);
                $this->db->where('deleted',0);
                $q = $this->db->get();
    
                if($q->num_rows()>0){ #has assigned to property
                    $json_data['status'] = false;
                }else{
                    $update_data = array('active'=> 0);
                    $this->db->where('agency_user_account_id', $pm_id);
                    $this->db->update('agency_user_accounts',$update_data);
                    $this->db->limit(1);

                    ##insert log
                    $log_details = "<strong>Portal User {$auc_row['fname']} {$auc_row['lname']}</strong> Deactivated";
                    $log_params = array(
                        'title' => 46,  //Agency Updated
                        'details' => $log_details,
                        'display_in_vad' => 1,
                        'created_by_staff' => $this->session->staff_id,
                        'agency_id' => $agency_id
                    );
                    $this->system_model->insert_log($log_params);
                    ##insert log end
    
                    $json_data['status'] = true;
                    $json_data['msg'] = "User successfully deactivated";
                }
                
            }

        }

        echo json_encode($json_data);

    }

    public function send_sales_emails($agency_id){

        $this->load->model('email_model');
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Send Email";
        $data['agency_id'] = $agency_id;

        if($agency_id!="" && is_numeric($agency_id)){

            ##Get Agency Details
            $sel_query = "
                a.account_emails, 
                a.agency_emails, 
            ";
            $agency_params = array(
                'sel_query' => $sel_query,
                'country_id' => COUNTRY,
                'agency_id' => $agency_id
            );
            $agencies_row = $this->agency_model->get_agency($agency_params)->row_array();

            ##get email templates
            $et_params = array(
                'echo_query' => 0,
                'active' => 1,
                'temp_type' => 1,
                'sort_list' => array(
                    [
                        'order_by' => 'et.`template_name`',
                        'sort' => 'ASC'
                    ]
                )
            );
            $data['email_temp_sql'] = $this->email_model->get_email_templates($et_params);


            ## put account emails into an array
            $account_emails_exp = explode("\n",trim($agencies_row['account_emails']));
            $data['account_emails_imp'] = implode(';',$account_emails_exp);

            ## put agency emails into an array            
            $agency_emails_exp = explode("\n",trim($agencies_row['agency_emails']));
            $data['agency_emails_imp'] = implode(';',$agency_emails_exp);

            // get template tags, reuse joseph's function
            $custom_filter = "`ett`.`email_templates_tag_id` IN(1,12,15,16,17) ";
            $tag_params = array(
                'echo_query' => 0,
                'custom_filter' => $custom_filter,
                'sort_list' => array(
                    array(
                        'order_by' => 'ett.`tag_name`',
                        'sort' => 'ASC'
                    )
                ),
                'active' => 1
            );
            $data['template_tags_sql'] = $this->email_model->get_email_template_tag($tag_params);     
            

            $this->load->view('templates/inner_header', $data);
            $this->load->view('/agency/send_sales_emails', $data);
            $this->load->view('templates/inner_footer', $data);

        }else{
            show_404();
        }

    }

    public function send_sales_email_script(){

        $this->load->model('/inc/email_functions_model');

        $agency_id = $this->input->get_post('agency_id');     
        $from = $this->input->get_post('from');   
        $to = $this->input->get_post('to');   
        $cc = $this->input->get_post('cc');   
        $subject = $this->input->get_post('subject');   
        $body = $this->input->get_post('body');   
        $file_custom_attach = $_FILES["custom_attach"];
        $email_type = $this->input->get_post('email_type');

        $custom_attach_file = null;   
        $attachment_error = null;   

        if($agency_id!="" && $agency_id > 0){

            ##Upload if file exist
            if( $_FILES["custom_attach"]['name'] != '' ){

                #File config
                $upload_path = 'uploads/temp';
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'pdf|msg|doc|docx|csv|xls|xlsx';

                ##Custom filename
                $file = pathinfo($_FILES["custom_attach"]['name']);
                $custom_filename = 'custom_attach_'.date('YmdHis').rand().'.'. $file['extension'];
                $config['file_name'] = $custom_filename; // set custom file name

                ##Load upload library
                $this->load->library('upload', $config);

                if ( $this->upload->do_upload('custom_attach') ){

                    $upload_data = $this->upload->data();

                    if( $upload_data ){

                        $file_name = $upload_data['file_name'];  
                        $custom_attach_file =  "{$_SERVER['DOCUMENT_ROOT']}/{$upload_path}/{$file_name}";
                        
                    }                

                }else{

                    $attachment_error = $this->upload->display_errors();                                                     

                }

            }

            if( $attachment_error == null || $attachment_error =="" ){

                // send email
                $email_params = array(
                    'agency_id' => $agency_id,
                    'from' => $from,
                    'to' => $to,
                    'cc' => $cc,
                    'subject' => $subject,
                    'body' => $body,
                    'custom_attach_file' => $custom_attach_file
                );
                
                if( $this->email_functions_model->send_email_using_template($email_params) ){
                    $this->session->set_flashdata('send_email_success',1);         
                }else{
                    $this->session->set_flashdata('send_email_success',1);         
                }                            

            }   
            
            redirect("/agency/send_sales_emails/{$agency_id}?attachment_error={$attachment_error}");

        }else{
            echo "Error: Please contact system admin.";
        }

    }

    /**
     * Update Agency / Send Email / Add logs
     */
    public function ajax_re_activate(){
        $agency_id = $this->input->post('agency_id');
        $agency_status = $this->input->post('agency_status');
        $j_data['status'] = false;

        if($agency_id && $agency_id!=""){

            ##Update agency to active
            $update_data = array(
                'status' => 'active',
                'send_emails' => 1,
                'send_combined_invoice' => 1,
                'send_entry_notice' => 0,
                'require_work_order'=> 0,
                'allow_indiv_pm' => 1,
                'auto_renew' => 1,
                'key_allowed' => 1,
                'key_email_req' => 0,
                'phone_call_req' => 1,
                'allow_dk' => 1,
                'allow_en' => 1,
                'new_job_email_to_agent' => 0,
                'joined_sats' => date('Y-m-d')
            );
            $update_agency = $this->agency_model->update_agency($agency_id, $update_data);
            ##Update agency to active end

            ## Send Email
            ##get current agency details
            $agency_det_params = array(
                'sel_query' => "*",
                'agency_id' => $agency_id,
                'country_id' => COUNTRY
            );
            $curr_agency = $this->agency_model->get_agency($agency_det_params);
            $agency_row = $curr_agency->row_array();

            $agency_email_params = array(
                'agency_name' => $agency_row['agency_name'],
                'legal_name' => $agency_row['legal_name'],
                'abn_number' => $agency_row['abn'],
                'street_number' => $agency_row['address_1'],
                'street_name' => $agency_row['address_2'],
                'suburb' => $agency_row['address_3'],
                'state' => $agency_row['state'],
                'postcode' => $agency_row['postcode'],
                'phone' => $agency_row['phone'],
                'tot_properties' => $agency_row['tot_properties'],
                'ac_fname' => $agency_row['contact_first_name'],
                'ac_lname' => $agency_row['contact_last_name'],
                'ac_phone' => $agency_row['contact_phone'],
                'ac_email' => $agency_row['contact_email'],
                'acc_name' => $agency_row['accounts_name'],
                'acc_phone' => $agency_row['accounts_phone'],
                'agency_emails' => $agency_row['agency_emails'],
                'account_emails' => $agency_row['account_emails'],
                'salesrep' => $agency_row['salesrep']
            );
            $this->agency_send_mail($agency_email_params);
            ## Send Email End

            ## Add Logs
            $log_details = "Agency Changed to Active";
            $log_params = array(
                'title' => 46,  //Agency Updated
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);
            ## Add Logs End

            $j_data['status'] = true;
            
            
        }
        echo json_encode($j_data);
    }

    public function ajax_update_agency_onboarding_selection(){
        $agency_id = $this->input->post('agency_id');
        $onboarding_id = $this->input->post('onboarding_id');
        $is_ticked = $this->input->post('is_ticked');
        $today = date('Y-m-d H:i:s');

        if($agency_id && $agency_id!=""){

            $agency_onboarding_row = $this->db->select('*')->from('agency_onboarding')->where('onboarding_id', $onboarding_id)->get()->row_array();

            if( $is_ticked == 1 ){    

                $data = array(
                    'onboarding_id' => $onboarding_id,
                    'agency_id' => $agency_id,
                    'updated_date' => $today,
                    'updated_by' => $this->session->staff_id
                );
                $this->db->insert('agency_onboarding_selected', $data);

                ##insert log
                $log_details = "<strong>Agency Onboarding - {$agency_onboarding_row['name']}</strong> Update from NO to YES";
                $log_params = array(
                    'title' => 46,  //Agency Updated
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);
                ##insert log end

            }elseif($is_ticked==0){

                 ## Clear agency_onboarding_selected
                $this->db->where('agency_id', $agency_id);
                $this->db->where('onboarding_id', $onboarding_id);
                $this->db->delete('agency_onboarding_selected');
                $this->db->limit(1);

                ##insert log
                $log_details = "<strong>Agency Onboarding - {$agency_onboarding_row['name']}</strong> Update from YES to NO";
                $log_params = array(
                    'title' => 46,  //Agency Updated
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);
                ##insert log end
                
            }
            
           
            $staff_params = array(
                'sel_query' => "sa.FirstName, sa.LastName",
                'staff_id' => $this->session->staff_id
            );
            $staff_row = $this->gherxlib->getStaffInfo($staff_params)->row_array();
            $updated_by = $this->system_model->formatStaffName($staff_row['FirstName'], $staff_row['LastName']);

            $updated_date = date('d/m/Y H:i',strtotime($today));

            $json_data = array(
                "updated_by" => $updated_by,
                "updated_date" => $updated_date
            );

            echo json_encode($json_data);

        }

    }

    public function vad_upload_agency_file(){

        $agency_id = $this->input->post('agency_id');

        if (!empty($_FILES['fileupload']['name']) && $agency_id!=""){

            $upload_path = "./uploads/agency_files/{$agency_id}";
            $upload_folder = "/uploads/agency_files/{$agency_id}";

            if(!is_dir($upload_folder)){
                mkdir($upload_path,0777,true);
            }

            ##file name
            $filename = preg_replace('/#+/', 'num', $_FILES['fileupload']['name']);
            $filename2 = preg_replace('/\s+/', '_', $filename);
            $append_text = 'af_'.rand().date('YmdHis');
            $file_new_name = "{$append_text}".$filename2;

            $updload_params = array(
                'upload_path' => $upload_path,
                'file_name' => $file_new_name,
                'allowed_types' => "gif|jpg|png|pdf|jped|doc|xls|xlsx|bmp|txt"
            );
            $upload_agency_file = $this->gherxlib->do_upload('fileupload', $updload_params);

            if($upload_agency_file){

                ##Insert log
                $log_details = "<strong>Agency Files</strong> {$file_new_name} Uploaded";
                $log_params = array(
                    'title' => 41, // file upload
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);

                ##set session success message
                $success_message = "Upload Agency File Successfull.";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url("/agency/view_agency_details/{$agency_id}/8"), 'refresh');

            }else{

                ##set session error message
                $error_message = $this->upload->display_errors();
                $this->session->set_flashdata(array('error_msg' => $error_message, 'status' => 'error'));
                redirect(base_url("/agency/view_agency_details/{$agency_id}/8"), 'refresh');

            }
        }
    }

    public function ajax_vad_del_agency_file(){

        $j_data['status'] = false;
        $agency_id = $this->input->post('agency_id');
        $file = $this->input->post('file');

        
        $file_path = FCPATH."uploads/agency_files/{$agency_id}/{$file}";

        if($agency_id && $agency_id!=""){

                if(file_exists($file_path))
                {   
                    ## Delete file
                    @unlink($file_path);

                    ## Add log
                    $log_details = "<strong>Agency Files</strong> {$file} Deleted.";
                    $log_params = array(
                        'title' => 41,  //File Upload
                        'details' => $log_details,
                        'display_in_vad' => 1,
                        'created_by_staff' => $this->session->staff_id,
                        'agency_id' => $agency_id
                    );
                    $this->system_model->insert_log($log_params);

                    $j_data['status'] = true;
                }else{
                    $j_data['status'] = false;
                }
                
        }

        echo json_encode($j_data);

    }

    public function upload_contractor_appointment(){

        $agency_id = $this->input->post('agency_id');

        if (!empty($_FILES['upload_cont_app_frm']['name']) && $agency_id!=""){

            $upload_path = "./uploads/agency_files";
            $upload_folder = "uploads/agency_files";

            if(!is_dir($upload_folder)){
                mkdir($upload_path,0777,true);
            }

            ##file name
            $filename = preg_replace('/#+/', 'num', $_FILES['upload_cont_app_frm']['name']);
            $filename2 = preg_replace('/\s+/', '_', $filename);
            $append_text = 'caf_'.rand().date('YmdHis');
            $file_new_name = "{$append_text}_".$filename2;

            $updload_params = array(
                'upload_path' => $upload_path,
                'file_name' => $file_new_name,
                'allowed_types' => "gif|jpg|png|pdf|jped|doc|xls|xlsx|bmp|txt"
            );
            $upload_Appointment_form = $this->gherxlib->do_upload('upload_cont_app_frm', $updload_params);

            if($upload_Appointment_form){

                ##insert to contractor_appointment table
                $insert_data = array(
                    'file_name' => $file_new_name,
                    'file_path' => "{$upload_folder}/{$file_new_name}",
                    'agency_id' => $agency_id,
                    'country_id' => COUNTRY
                );
                $this->db->insert('contractor_appointment', $insert_data);

                ##Insert log
                $log_details = "<strong>Contractor Appointment Form</strong> {$file_new_name} Uploaded";
                $log_params = array(
                    'title' => 41, // file upload
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);

                ##set session success message
                $success_message = "Upload Contractor Appointment Form Successfull.";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url("/agency/view_agency_details/{$agency_id}/8"), 'refresh');
 
            }else{

                ##set session error message
                $error_message = $this->upload->display_errors();
                $this->session->set_flashdata(array('error_msg' => $error_message, 'status' => 'error'));
                redirect(base_url("/agency/view_agency_details/{$agency_id}/8"), 'refresh');

            }

        }   

    }

    public function ajax_delete_caf(){

        $jdata['status'] = false;
        $ca_id = $this->input->post('ca_id');
        $agency_id = $this->input->post('agency_id');
        $file_name = $this->input->post('file_name');
        
        if($ca_id && $ca_id!=""){

            $this->db->select('file_path,contractor_appointment_id');
            $this->db->from('contractor_appointment');
            $this->db->where('contractor_appointment_id', $ca_id);
            $this->db->limit(1);
            $q = $this->db->get();
            $row = $q->row_array();

            if($row['file_path']!=""){

                ##delete file from server
                $file_path = FCPATH."{$row['file_path']}";
                @unlink($file_path);

                ##delete file from DB table
                $this->db->where('contractor_appointment_id', $row['contractor_appointment_id']);
                $this->db->delete('contractor_appointment');
                $this->db->limit(1);

                //insert log
                $log_details = "<strong>Contractor Appointment Form</strong> {$file_name} Deleted.";
                $log_params = array(
                    'title' => 41,  //File Upload
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);

                $jdata['status'] = true;
            }else{
                $jdata['status'] = false;
            }

        }

        echo json_encode($jdata);

    }

    public function upload_agency_specific_brochures(){

        $agency_id = $this->input->post('agency_id');

        if (!empty($_FILES['upload_brochures']['name']) && $agency_id!=""){

            $upload_path = "./uploads/agency_files";
            $upload_folder = "uploads/agency_files";

            if(!is_dir($upload_folder)){
                mkdir($upload_path,0777,true);
            }

            ##file name
            $filename = preg_replace('/#+/', 'num', $_FILES['upload_brochures']['name']);
            $filename2 = preg_replace('/\s+/', '_', $filename);
            $append_text = 'asb_'.rand().date('YmdHis');
            $file_new_name = "{$append_text}_".$filename2;

            $updload_params = array(
                'upload_path' => $upload_path,
                'file_name' => $file_new_name,
                'allowed_types' => "gif|jpg|png|pdf|jped|doc|xls|xlsx|bmp|txt"
            );
            $upload_specific_brochures = $this->gherxlib->do_upload('upload_brochures', $updload_params);

            if($upload_specific_brochures){

                 ##insert to agency_specific_brochures table
                 $insert_data = array(
                    'file_name' => $file_new_name,
                    'file_path' => "{$upload_folder}/{$file_new_name}",
                    'agency_id' => $agency_id
                );
                $this->db->insert('agency_specific_brochures', $insert_data);

                ##Insert log
                $log_details = "<strong>Agency Specific Brochures</strong> {$file_new_name} Uploaded";
                $log_params = array(
                    'title' => 41, // file upload
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);

                ##set session success message
                $success_message = "Upload Agency Specific Brochure Successfull.";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url("/agency/view_agency_details/{$agency_id}/8"), 'refresh');

            }else{
                 ##set session error message
                 $error_message = $this->upload->display_errors();
                 $this->session->set_flashdata(array('error_msg' => $error_message, 'status' => 'error'));
                 redirect(base_url("/agency/view_agency_details/{$agency_id}/8"), 'refresh');
            }

        }

    }

    public function ajax_delete_asb(){

        $jdata['status'] = false;
        $asb_id = $this->input->post('asb_id');
        $agency_id = $this->input->post('agency_id');
        $file_name = $this->input->post('file_name');

        if($asb_id && $asb_id!=""){

            $this->db->select('agency_specific_brochures_id,file_path,file_name');
            $this->db->from('agency_specific_brochures');
            $this->db->where('agency_specific_brochures_id', $asb_id);
            $this->db->where('agency_id', $agency_id);
            $this->db->limit(1);
            $q = $this->db->get();
            $row = $q->row_array();

            if($row['file_path']!=""){

                ##delete file from server
                $file_path = FCPATH."{$row['file_path']}";
                @unlink($file_path);

                ##delete file from DB table
                $this->db->where('agency_specific_brochures_id', $row['agency_specific_brochures_id']);
                $this->db->delete('agency_specific_brochures');
                $this->db->limit(1);

                ##Insert log
                $log_details = "<strong>Agency Specific Brochures</strong> {$file_name} Deleted";
                $log_params = array(
                    'title' => 41, // file upload
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);

                $jdata['status'] = true;
            }else{
                $jdata['status'] = false;
            }

        }

        echo json_encode($jdata);

    }

    public function ajax_update_property_agency(){

        $jdata['status'] = false;
        $current_agency = $this->input->post('current_agency');
        $new_agency = $this->input->post('new_agency');
        $props = $this->input->post('props');
        $staff_id = $this->session->staff_id;
        $pm = $this->input->post('pm');

        ##get orig agency name
        $agency_params = array(
            'sel_query' => "a.agency_name",
            'agency_id' => $current_agency
        );
        $orig_agency_row = $this->agency_model->get_agency($agency_params)->row_array();
        $current_agency_name = $orig_agency_row['agency_name'];

        ##get new agency name
        $new_agency_params = array(
            'sel_query' => "a.agency_name",
            'agency_id' => $new_agency
        );
        $new_agency_row = $this->agency_model->get_agency($new_agency_params)->row_array();
        $new_agency_name = $new_agency_row['agency_name'];

        if(!empty($props) && $current_agency!="" && $new_agency!=""){

            foreach($props as $prop_id){

                if($prop_id>0){

                    #Update property agency
                    /* ##disabled > updated below removed api field because we already using new genenric table apd
                    $update_data = array(
                        'agency_id' => $new_agency,
                        'propertyme_prop_id' => NULL,
                        'palace_prop_id' => NULL
                    );
                    $this->db->where('property_id', $prop_id);
                    $this->db->update('property', $update_data);
                    */
                    $update_data = array(
                        'agency_id' => $new_agency,
                        'pm_id_new' => $pm
                    );
                    $this->db->where('property_id', $prop_id);
                    $this->db->update('property', $update_data);

                    ## New > when NLM clear api_prop_id from api_property_data table 
                    $updateData_Api = array('api_prop_id' => NULL, 'active' => 0);
                    $this->db->where('crm_prop_id', $prop_id);
                    $this->db->update('api_property_data', $updateData_Api);
                    ## New > when NLM clear api_prop_id from api_property_data table end

                    #Insert Log
                    $log_details = "Property changed from {$current_agency_name} to {$new_agency_name}";
                    $log_params = array(
                        'title' => 84,  //Agency changed
                        'details' => $log_details,
                        'display_in_vpd' => 1,
                        'created_by_staff' => $this->session->staff_id,
					    'property_id' => $prop_id
                    );
                    $this->system_model->insert_log($log_params);

                }
    
            }

            $jdata['status'] = true;

        }else{
            $jdata['status'] = false;
        }

        echo json_encode($jdata);
       
    }

    public function ajax_check_agency_api_integration_selected(){

        $connected_service = $this->input->post('connected_service');
        $agency_id = $this->input->post('agency_id');

        $sel_query = "COUNT(`api_integration_id`) as jcount";
        $this->db->select($sel_query);
        $this->db->from('agency_api_integration');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('connected_service', $connected_service);
        $q = $this->db->get();
        $count = $q->row()->jcount;

        echo $count;

    }

    public function add_agency_api_integration(){

        $connected_service = $this->input->post('connected_service');
        $agency_id = $this->input->post('agency_id');
        $staff_id = $this->session->staff_id;

        if($agency_id!="" && $connected_service!=""){

            ##get agency API name
            $this->db->select('api_name');
            $this->db->from('agency_api');
            $this->db->where('agency_api_id',$connected_service);
            $q = $this->db->get();
            $api_row = $q->row_array();
            $api_name = $api_row['api_name'];

            ##insert api
            $insert_data = array(
                'connected_service' => $connected_service,
                'agency_id' => $agency_id,
                'date_activated' => date('Y-m-d')
            );
            $this->db->insert('agency_api_integration', $insert_data);
            $this->db->limit(1);

            ##insert logs
            $log_details = "{$api_name} API integration added";
            $log_params = array(
                'title' => 85,  //API Integration
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);
            
            $success_message = "{$api_name} API integration added";
			$this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
            redirect(base_url("/agency/view_agency_details/{$agency_id}/1"), 'refresh');

        }else{
            $error_msg = "Form Error: Please contact admin";
			$this->session->set_flashdata(array('error_msg'=>$error_msg,'status'=>'error'));
            redirect(base_url("/agency/view_agency_details/{$agency_id}/1"), 'refresh');
        }

    }

    public function ajax_unlink_connected_api_prop(){

        $jdata['status'] = false;

        $agency_id = $this->input->post('agency_id');

        if($agency_id!="" && $agency_id >0){

            // delete 
            $this->db->query("
            DELETE apd
            FROM `api_property_data` AS apd
            LEFT JOIN `property` AS p ON apd.`crm_prop_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`agency_id` = {$agency_id}
            ");

            ##insert log
            $log_details = "Connected API Properties Unlinked";
            $log_params = array(
                'title' => 85, 
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);

            $jdata['status'] = true;
        }

        echo json_encode($jdata);

    }

    public function ajax_api_billable_toggle(){

        $jdata['status'] = false;
        $agency_id = $this->input->post('agency_id');
        $api_billable = $this->input->post('api_billable');
        $og_api_billable = $this->input->post('og_api_billable');

        if( $agency_id > 0 && $agency_id!="" ){

            $update_data = array(
                'api_billable' => $api_billable
            );
            $this->db->where('agency_id', $agency_id);
            $this->db->update('agency', $update_data);
            $this->db->limit(1);

            ##insert log
            $new_val = $this->_checkbox_yes_no($api_billable);
            $orig_val = $this->_checkbox_yes_no($og_api_billable);
            $log_details = "<strong>API Billable</strong> Updated from {$orig_val} to {$new_val}";
            $log_params = array(
                'title' => 85, 
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);

            $jdata['api_val'] = $api_billable;
            $jdata['status'] = true;
        }else{
            $jdata['status'] = false;
        }

        echo json_encode($jdata);

    }

    public function ajax_no_bulk_match_toggle(){

        $jdata['status'] = false;
        $agency_id = $this->input->post('agency_id');
        $no_bulk_match = $this->input->post('no_bulk_match');
        $og_no_bulk_match = $this->input->post('og_no_bulk_match');

        if( $agency_id > 0 && $agency_id!="" ){
            $update_data = array(
                'no_bulk_match' => $no_bulk_match
            );
            $this->db->where('agency_id', $agency_id);
            $this->db->update('agency', $update_data);
            $this->db->limit(1);

            ##insert log
            $new_val = $this->_checkbox_yes_no($no_bulk_match);
            $orig_val = $this->_checkbox_yes_no($og_no_bulk_match);
            $log_details = "<strong>Generate warning on bulk match</strong> Updated from {$orig_val} to {$new_val}";
            $log_params = array(
                'title' => 85, 
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);

            $jdata['no_bulk_match_val'] = $no_bulk_match;
            $jdata['status'] = true;
        }else{
            $jdata['status'] = false;
        }

        echo json_encode($jdata);

    }

    public function ajax_delete_agency_api_integration(){
        
        $jdata['status'] = false;
        $api_integration_id = $this->input->post('api_integration_id');
        $api_id = $this->input->post('api_id');
        $agency_id = $this->input->post('agency_id');
        $staff_id = $this->session->staff_id;

        if($agency_id!="" && $api_integration_id!="" && $api_id!=""){

            ##get agency API name
            $this->db->select('api_name');
            $this->db->from('agency_api');
            $this->db->where('agency_api_id', $api_id);
            $q = $this->db->get();
            $api_row = $q->row_array();
            $api_name = $api_row['api_name'];
            $this->db->flush_cache();

            ##delete agency API integration
            $this->db->where('api_integration_id',$api_integration_id);
            $this->db->delete('agency_api_integration');
            $this->db->limit(1);
            $this->db->flush_cache();

            ##delete agency API token
            $this->db->where('api_id',$api_id);
            $this->db->where('agency_id',$agency_id);
            $this->db->delete('agency_api_tokens');
            $this->db->limit(1);
            $this->db->flush_cache();

            ##clear API related markers
            switch($api_id){
                case 1: // PME

                    $data = array('pme_supplier_id'=>NULL);
                    $this->db->where('agency_id', $agency_id);
                    $this->db->update('agency',$data);     

                break; 
                case 4: // PALACE         
                    
                    $data = array('palace_supplier_id'=>'','palace_agent_id'=>'','palace_diary_id'=>NULL);
                    $this->db->where('agency_id', $agency_id);
                    $this->db->update('agency',$data);  
          
                break;
            } 

            ##insert log
            $log_details = "{$api_name} API integration deleted";
            $log_params = array(
                'title' => 85, 
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);

            $jdata['status'] = true;

        }else{

            $jdata['status'] = false;

        }
        
        echo json_encode($jdata);

    }

    public function ajax_update_agency_api_integration(){

        $jdata['status'] = false;
        $postData = $this->input->post();
        $api_integration_id = $this->input->post('api_integration_id');
        $connected_service = $this->input->post('connected_service');
        $og_connected_service = $this->input->post('og_connected_service');
        $status = $this->input->post('status');
        $og_edit_api_status = $this->input->post('og_edit_api_status');
        $staff_id = $this->session->staff_id;
        $agency_id = $this->input->post('agency_id');

        if($agency_id!="" && $api_integration_id!=""){

            ##udpate agency_api_integration
            $this->db->set(
                array(
                    'connected_service' => $connected_service,
                    'active' => $status,
                    'date_activated' => NULL
                )
            );
            $this->db->where('api_integration_id', $api_integration_id);
            $this->db->update('agency_api_integration');
            $this->db->limit(1);

            ##insert log for pricing (edited fields) 11 = API Tab
            $this->_insertAgencyLogs($postData,11);

            $jdata['status'] = true;
        }else{
            $jdata['status'] = false;
        }

        echo json_encode($jdata);

    }

    private function _checkbox_yes_no($val){
        if($val==1){
            return "YES";
        }elseif($val==0){
            return "NO";
        }elseif($val<0){
            return "No Response";
        }
    }

    private function _userTypeConvertToString($val){
        if($val==1){
            return "Admin";
        }elseif($val==2){
            return "Property Manager";
        }
    }

    private function format_empty_val($val){
        if($val==""){
            return "NULL";
        }else{
            return $val;
        }
    }

    public function add_event_agency_logs(){

        $post = $this->input->post();
        $agency_id = $post['agency_id'];
        $eventdate = ($this->system_model->isDateNotEmpty($post['eventdate'])) ? $this->system_model->formatDate($post['eventdate']) : ULL ;
        $contact_type = $post['contact_type'];
        $comments = $post['comments'];
        $next_contact = ($this->system_model->isDateNotEmpty($post['next_contact'])) ? $this->system_model->formatDate($post['next_contact']) : NULL ;
        $add_to_snapshot = $post['add_to_snapshot'];
        $ss_status = $post['ss_status'];
        $total_prop = $post['total_prop'];

        if($agency_id && $agency_id>0){
            
            /* Disabled by Gherx > no purpose
            $this->db->where('agency_id', $agency_id);
            $this->db->delete('sales_report');
            */

            ##add to sales_report
            $sale_resport_data = array(
                'contact_type' => $contact_type,
                'comment' => $comments,
                'date' => $eventdate,
                'staff_id' => $this->session->staff_id,
                'agency_id' => $agency_id,
                'next_contact' => $next_contact,
                'created_date' => date('Y-m-d H:i:s')
            );
            $add_sales_report = $this->agency_model->add_sales_report($sale_resport_data);

            if($add_sales_report){

                if($add_to_snapshot==1 && $ss_status!=""){

                    $snapshot_data = array(
                        'agency_id' => $agency_id,
                        'properties' => $total_prop,
                        'sales_snapshot_status_id' => $ss_status,
                        'details' => $comments,
                        'date' => date('Y-m-d H:i:s'),
                        'sales_snapshot_sales_rep_id' => $this->session->staff_id,
                        'country_id' => COUNTRY
                    );
                    $this->agency_model->insert_sales_snapshot($snapshot_data);

                }

                ##add log
                $event_date_tt = ($this->system_model->formatDate($eventdate)!=date('Y-m-d')) ? "<strong>Event Date: </strong>{$this->system_model->formatDate($eventdate,'d/m/Y')}&nbsp;|&nbsp;" : NULL;
                $contact_type_row = $this->agency_model->get_contact_type($contact_type);
                $log_details = $event_date_tt. "<strong>{$contact_type_row['contact_type']}: </strong> $comments";
                $pricing_log_params = array(
                    'title' => 46,
                    'details' => $log_details,
                    'log_type' => $contact_type,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($pricing_log_params);
            }

            //set session success message
            $success_message = "Log event has been succesfully added";
            $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
            redirect(base_url("/agency/view_agency_details/{$agency_id}/7"),'refresh');

        }else{
            $error_msg = "Form Error: Please contact admin";
			$this->session->set_flashdata(array('error_msg'=>$error_msg,'status'=>'error'));
			redirect(base_url("/agency/view_agency_details/{$agency_id}/7"),'refresh');
        }

    }

    public function ajax_delete_agency_api_token(){

        $jdata['status'] = false;
        $agency_api_token_id = $this->input->post('agency_api_token_id');
        $api_id = $this->input->post('api_id');
        $agency_id = $this->input->post('agency_id');

        if( $agency_api_token_id > 0 && $api_id > 0 && $agency_id > 0 ){

            ##get agency API name
            $this->db->select('api_name');
            $this->db->from('agency_api');
            $this->db->where('agency_api_id', $api_id);
            $q = $this->db->get();
            $api_row = $q->row_array();
            $api_name = $api_row['api_name'];
            $this->db->flush_cache();

            ##delete agency API token
            $this->db->where('agency_api_token_id',$agency_api_token_id);
            $this->db->where('api_id',$api_id);
            $this->db->where('agency_id',$agency_id);
            $this->db->delete('agency_api_tokens');
            $this->db->limit(1);
            $this->db->flush_cache();

            ##clear API related markers
            switch($api_id){
                case 1: // PME

                    $data = array('pme_supplier_id'=>NULL);
                    $this->db->where('agency_id', $agency_id);
                    $this->db->update('agency',$data);     

                break; 
                case 4: // PALACE         
                    
                    $data = array('palace_diary_id'=>NULL);
                    $this->db->where('agency_id', $agency_id);
                    $this->db->update('agency',$data);  
          
                break;
            } 

            ##insert log
            $log_details = "{$api_name} API Token deleted";
            $log_params = array(
                'title' => 85, 
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);

            $jdata['status'] = true;

        }else{

            $jdata['status'] = false;

        }
        
        echo json_encode($jdata);

    }

    public function assign_pm(){

        $jdata['status'] = false;
        $agency_id = $this->input->post('agency_id');
        $props = $this->input->post('props');
        $pm = $this->input->post('pm');

        if($agency_id!="" && is_numeric($agency_id)){ //validate

            if($pm!="" && is_numeric($pm)){

                foreach($props as $val){

                    if($val!=""){

                        #update property manager
                        $update_data = array(
                            'pm_id_new' => $pm
                        );
                        $this->db->where('property_id', $val);
                        $this->db->update('property', $update_data);

                        #Insert Log
                        $pm_params = array('sel_query'=>"aua.fname, aua.lname", 'custom_where' => "aua.agency_user_account_id = {$pm}");
                        $pm_name_q = $this->properties_model->get_agency_pm($pm_params)->row_array();
                        $log_details = "Property manager updated to {$pm_name_q['fname']} {$pm_name_q['lname']}";
                        $log_params = array(
                            'title' => 18,
                            'details' => $log_details,
                            'display_in_vpd' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $val
                        );
                        $this->system_model->insert_log($log_params);

                    }

                }

                $jdata['status'] = true;

            }else{
                $jdata['status'] = false;
            }

        }else{
            $jdata['status'] = false;
        }

        echo json_encode($jdata);

    }

    public function ajax_update_agency(){

        $this->load->model('properties_model');
        $jdata['status'] = false;

        $post = $this->input->post();
        $type = $post['type'];
        $agency_id = $post['agency_id'];

        ##get current agency status
        $curr_agency_det_params = array('sel_query'=>'a.status', 'country_id'=> COUNTRY, 'agency_id'=>$agency_id );
        $curr_agency_det = $this->agency_model->get_agency($curr_agency_det_params)->row_array();

        if($agency_id!="" && is_numeric($agency_id) && $agency_id>0){

                if($type=="btn_update_agency_name"){ ##UDPATE AGENCY NAME

                    $agency_name = $post['agency_name'];
                    $og_agency_name = $post['og_agency_name'];
                    
                    if($agency_name!=""){

                        ##email notification
                        if($curr_agency_det['status']=='active'){ #email update changes for active agency only

                            if($agency_name!=$og_agency_name){
                                $email_params = array(
                                    'agency_id' => $agency_id,
                                    'post' => $post
                                );
                                $this->email_agency_update($email_params);
                            }

                        }

                        ##update agency name
                        $agency_data = array('agency_name' => $agency_name);
                        $this->agency_model->update_agency($agency_id,$agency_data);

                        ##LOGS > Fields Edited
                        $this->_insertAgencyLogs($post,1);

                        $jdata['status'] = true;

                    }
        
                }else if($type=="btn_update_agency_address"){ ##UPDATE AGENCY ADDRESS
                    $address_1 = $post['address_1'];
                    $address_2 = $post['address_2'];
                    $address_3 = $post['address_3'];
                    $state = $post['state'];
                    $postcode = $post['postcode'];
                    $fullAdd = $post['fullAdd'];
                    //$og_fullAdd = $post['og_fullAdd'];
                    $address_type = $post['address_type'];

                    ##Get google coordinates
                    $address = "{$address_1} {$address_2} {$address_3} {$state} {$postcode}, {$this->config->item('country')}";
                    $coordinates = $this->system_model->getGoogleMapCoordinates($address);
                    
                    ##get region
                    $pcr_sql = $this->agency_model->getRegionViaPostCode($postcode)->row_array();
                    $pcr_id = $pcr_sql['postcode_region_id'];
                    ##get region end

                    if($fullAdd!=""){

                        if($address_type==""){ //default address update

                            ##update agency name
                            $agency_data = array(
                                'address_1' => $address_1,
                                'address_2' => $address_2,
                                'address_3' => $address_3,
                                'state' => $state,
                                'postcode' => $postcode,
                                'lat' => $coordinates['lat'],
                                'lng' => $coordinates['lng'],
                                'postcode_region_id' => $pcr_id
                            );
                            $this->agency_model->update_agency($agency_id,$agency_data);

                            ##LOGS > Fields Edited
                            $this->_insertAgencyLogs($post,1);

                            $jdata['status'] = true;

                        }else{ //update/insert to agency_addresses table
                            
                            ##check if agency address already exist in agency_addresses table
                            $check_addresses = $this->db->select('COUNT(id) AS count')
                                ->from('agency_addresses')
                                ->where('agency_id',$agency_id)
                                ->where('type',$address_type)
                                ->get()->row()->count;

                            if($check_addresses>0){ //address exist do update/edit

                                $update_data = array(
                                    'address_1' => $address_1,
                                    'address_2' => $address_2,
                                    'address_3' => $address_3,
                                    'state' => $state,
                                    'postcode' => $postcode
                                );
                                $this->db->where('agency_id', $agency_id);
                                $this->db->where('type', $address_type);
                                $this->db->update('agency_addresses',$update_data);

                                ##LOGS > Fields Edited
                                $this->_insertAgencyLogs($post,1);

                                $jdata['status'] = true;

                            }else{ //not exist do add new

                                ##add new agency addresses
                                $add_data = array(
                                    'agency_id' => $agency_id,
                                    'address_1' => $address_1,
                                    'address_2' => $address_2,
                                    'address_3' => $address_3,
                                    'state' => $state,
                                    'postcode' => $postcode,
                                    'type' => $address_type
                                );

                                if($address_1 != "" && $address_2 != "" && $address_3 != "" && $state != "" && $postcode != ""){
                                    $this->db->insert('agency_addresses', $add_data);

                                    ##LOGS >Add new address log
                                    $log_params = array(
                                        'title' => 46,
                                        'details' => "<strong>{$address}</strong> added as a <strong>{$this->agency_model->vad_address_type_name($address_type)}</strong>",
                                        'display_in_vad' => 1,
                                        'created_by_staff' => $this->session->staff_id,
                                        'agency_id' => $agency_id
                                    );
                                    $this->system_model->insert_log($log_params);

                                    $jdata['status'] = true;
                                }

                            }

                        }
                         
                    }

                }else if($type=="btn_update_agency_franchice"){ ##UPDATE Franchise group
                    
                    $franchise_group = $post['franchise_group'];
                    $og_franchise_group = $post['og_franchise_group'];

                    if($franchise_group!=""){

                        ##update agency name
                        $agency_data = array(
                            'franchise_groups_id' => $franchise_group
                        );
                        $this->agency_model->update_agency($agency_id,$agency_data);

                        ##LOGS > Fields Edited
                        $this->_insertAgencyLogs($post,1);

                        $jdata['status'] = true;

                    }

                }else if($type=="btn_update_agency_office_hour"){ ##UPDATE Agency hours

                    $agency_hours = $post['agency_hours'];

                    ##update agency name
                    $agency_data = array(
                        'agency_hours' => $agency_hours
                    );
                    $this->agency_model->update_agency($agency_id,$agency_data);

                    ##LOGS > Fields Edited
                    $this->_insertAgencyLogs($post,1);

                    $jdata['status'] = true;

                }else if($type=="btn_update_agency_salesrep"){ ##Update salesrep

                    $salesrep = $post['salesrep'];
                    $og_salesrep = $post['og_salesrep'];

                    if($salesrep!=""){

                        ##email notification
                        if($curr_agency_det['status']=='active'){ #email update changes for active agency only
                            if($salesrep!=$og_salesrep){
                                $email_params = array(
                                    'agency_id' => $agency_id,
                                    'post' => $post
                                );
                                $this->email_agency_update($email_params);
                            }
                        }

                        ##update agency
                        $agency_data = array(
                            'salesrep' => $salesrep
                        );
                        $this->agency_model->update_agency($agency_id,$agency_data);

                        ##LOGS > Fields Edited
                        $this->_insertAgencyLogs($post,1);

                        $jdata['status'] = true;
                    }
                    
                }else if($type=="btn_update_agency_status"){ ##Update agency status

                    $status = $post['status'];
                    $og_status = $post['og_status'];
                    $active_prop_with_sats = $post['active_prop_with_sats'];
                    $og_active_prop_with_sats = $post['og_active_prop_with_sats'];
                    $agency_using = $post['agency_using'];
                    $og_agency_using = $post['og_agency_using'];
                    $deactivate_reason = $post['deactivate_reason'];
                    $og_deactivate_reason = $post['og_deactivate_reason'];
                    $reason_they_left = $post['reason_they_left'];
                    $other_reason = $post['other_reason'];

                    if($status!=""){

                            if( $status == 'deactivated' || $status == 'target' ){
                                // $status = 'target';
                                $date_deactivated = $this->system_model->formatDate(date('Y-m-d'));
                                $active_prop_with_sats = ($active_prop_with_sats!="") ? $active_prop_with_sats : 'NULL';
                                $deactivate_reason = ($deactivate_reason!="")? $deactivate_reason : 'NULL';
                            }else{
                                $date_deactivated = 'NULL';
                                //$active_prop_with_sats = 'NULL';
                                $deactivate_reason =  'NULL';
                            }

                            if( $og_status=='active' && $status=="target" ) {
                                $date_deactivated = $this->system_model->formatDate(date('Y-m-d'));
                            }

                            ##update agency
                            $agency_data = array(
                                'status' => $status,
                                'deactivated_ts' => $date_deactivated,
                                'deactivated_reason' => $deactivate_reason,
                                'active_prop_with_sats' => $active_prop_with_sats,
                                'agency_using_id' => $agency_using,
                                'joined_sats' => date('Y-m-d')
                            );
                           
                            if($status!='active'){
                                unset($agency_data['joined_sats']);
                            }

                            $this->agency_model->update_agency($agency_id,$agency_data);

                            if ($status == 'active' && ( $og_status=="target" || $og_status=="deactivated" ) ) {

                                $agency_params = array('sel_query'=>'a.*', 'country_id'=> COUNTRY, 'agency_id'=>$agency_id );
                                $data_agency_row = $this->agency_model->get_agency($agency_params)->row_array();

                                $agency_email_params = array(
                                    'agency_name'       => $data_agency_row['agency_name'],
                                    'legal_name'        => $data_agency_row['legal_name'],
                                    'abn_number'        => $data_agency_row['abn'],
                                    'street_number'     => $data_agency_row['address_1'],
                                    'street_name'       => $data_agency_row['address_2'],
                                    'suburb'            => $data_agency_row['address_3'],
                                    'state'             => $data_agency_row['state'],
                                    'postcode'          => $data_agency_row['postcode'],
                                    'phone'             => $data_agency_row['phone'],
                                    'tot_properties'    => $data_agency_row['tot_properties'],
                                    'ac_fname'          => $data_agency_row['contact_first_name'],
                                    'ac_lname'          => $data_agency_row['contact_last_name'],
                                    'ac_phone'          => $data_agency_row['contact_phone'],
                                    'ac_email'          => $data_agency_row['contact_email'],
                                    'acc_name'          => $data_agency_row['accounts_name'],
                                    'acc_phone'         => $data_agency_row['accounts_phone'],
                                    'agency_emails'     => $data_agency_row['agency_emails'],
                                    'account_emails'    => $data_agency_row['account_emails'],
                                    'salesrep'          => $data_agency_row['salesrep'],
                                    'agency_id'         => $data_agency_row['agency_id'],
                                    'status'            => $status,
                                    'old_status'        => $og_status
                                );
                                $this->agency_send_mail($agency_email_params);
                            }

                            ##deactivate property and cancel jobs if status change from active to target or deactivated>>
                            if( $og_status=='active' && ( $status=="target" || $status=="deactivated" ) ){                                

                               // insert agency leaving reason
                               $agency_res_insert_data = array(
                                    'agency_id' => $agency_id,
                                    'reason' => $reason_they_left
                                );

                                // "other" reason
                                if( $reason_they_left == -1 ){
                                    $agency_res_insert_data['other_reason'] = $other_reason;
                                }
                                
                                $this->db->insert('agency_leaving_reason', $agency_res_insert_data);

                                // email active jobs of agency before updating it to cancelled
                                $this->email_functions_model->email_cancelled_active_jobs_of_agency($agency_id);

                                // email NLM'ed properties
                                $this->email_functions_model->email_nlm_properties($agency_id);

                               $qtt = "select * from property where agency_id = {$agency_id} and deleted = 0 AND ( `is_nlm` = 0 OR `is_nlm` IS NULL )";
                               $active_prop_query = $this->db->query($qtt);
                                // get only active properties end

                                if($active_prop_query->num_rows() > 0){

                                    $nlm_success_prop_id_arr = [];
                                    $nlm_success_address_arr = [];
                                    $cannot_nlm_prop_id_arr = [];
                                    $cannot_nlm_address_arr = [];

                                    foreach($active_prop_query->result_array() as $p){
                                        if( $p['property_id'] > 0 ){

                                            ##get property addres info
                                            $params = array(
                                                'sel_query' => "p.address_1 as p_street_num, p.address_2 as p_street_name, p.address_3 as p_suburb, p.state as p_state, p.postcode as p_postcode",
                                                'property_id' => $p['property_id']
                                            );
                                            $prop_q = $this->properties_model->get_properties($params);
                                            $prop_row = $prop_q->row();

                                            $p_address = "{$prop_row->p_street_num} {$prop_row->p_street_name} {$prop_row->p_suburb}  {$prop_row->p_state}  {$prop_row->p_postcode}";        
                                            ##get property addres info end                                            

                                            $nlm_params = [];                                            
                                            $nlm_from_agency = true; // Used to determine what kind of log it inserts

                                            $nlm_params = array(
                                                'nlm_from_agency' => $nlm_from_agency,
                                                'agency_id' => $agency_id,
                                                'agency_status' => $status
                                            );
                                            
                                            $nlm_prop = $this->properties_model->nlm_property($p['property_id'],$nlm_params);                                                                                       

                                            if($nlm_prop == false){ ## has active job

                                                $cannot_nlm_prop_id_arr[] =  $p['property_id'];
                                                $cannot_nlm_address_arr[] =  $p_address;

                                            }else{

                                                $nlm_success_prop_id_arr[] =  $p['property_id'];
                                                $nlm_success_address_arr[] =  $p_address;

                                            }

                                        }	
                                    }

                                    $ret_arr = array(
                                        "cannot_nlm_prop_id_arr" => $cannot_nlm_prop_id_arr,
                                        "cannot_nlm_address_arr" => $cannot_nlm_address_arr,
                                        "nlm_success_prop_id_arr" => $nlm_success_prop_id_arr,
                                        "nlm_success_address_arr" => $nlm_success_address_arr
                                    );

                                    if(!empty($ret_arr['cannot_nlm_prop_id_arr'])){ ## found property with active job > set session custom message
                    
                                        $nlm_status_text = "These Properties has an active jobs, cant be deactivated <br/><br/>";
                        
                                        foreach($ret_arr['cannot_nlm_address_arr'] as $address_val){
                                            $nlm_status_text .= $address_val."<br/>";
                                        }

                                        $jdata['status'] = true;
                                        $jdata['status_text'] = $nlm_status_text;
                                
                                    }
                                    
                                }else{
                                    $jdata['status'] = true;
                                }

                            }

                            ##LOGS > Fields Edited
                            $this->_insertAgencyLogs($post,1);

                            $jdata['status'] = true;

                    }else{
                        $jdata['status'] = false;
                    }

                }elseif($type=="btn_update_agency_trust_account_soft"){ ##Update Trust Account Soft

                    $trust_acc_soft = $post['trust_acc_soft'];
                    $og_trust_acc_soft = $post['og_trust_acc_soft'];
                    $propertyme_agency_id = $post['propertyme_agency_id'];
                    $og_propertyme_agency_id = $post['og_propertyme_agency_id'];

                    if($trust_acc_soft!=""){

                        $agency_data = array(
                            'trust_account_software' => $trust_acc_soft,
                            'propertyme_agency_id' => $propertyme_agency_id
                        );
                        $this->agency_model->update_agency($agency_id,$agency_data);

                        ##LOGS > Fields Edited
                        $this->_insertAgencyLogs($post,1);

                        $jdata['status'] = true;

                    }

                }else if($type=="btn_update_agency_maintenance_prog"){ ##Update Maintenance Program

                    $maintenance = $post['maintenance'];
                    $og_maintenance = $post['og_maintenance'];
                    $m_surcharge = $post['m_surcharge'];
                    $og_m_surcharge = $post['og_m_surcharge'];
                    $m_disp_surcharge = $post['m_disp_surcharge'];
                    $og_m_disp_surcharge = $post['og_m_disp_surcharge'];
                    $m_price = $post['m_price'];
                    $og_m_price = $post['og_m_price'];
                    $m_surcharge_msg = $post['m_surcharge_msg'];
                    $og_m_surcharge_msg = $post['og_m_surcharge_msg'];

                    if($maintenance!=$og_maintenance){

                        // clear agency_maintenance
                        $this->db->delete('agency_maintenance', array('agency_id' => $agency_id));
                        $this->db->limit(1);

                        if($maintenance>0){

                            // insert agency_maintenance
                            $agency_maintenance_data = array(
                                'agency_id' => $agency_id,
                                'maintenance_id' => $maintenance,
                                'price' => $m_price,
                                'surcharge' => $m_surcharge,
                                'display_surcharge' => $m_disp_surcharge,
                                'surcharge_msg' => $m_surcharge_msg,
                                'updated_date' => date("Y-m-d"),
                                'status' => 1
                            );
                            $add_agency_maintenance = $this->agency_model->add_agency_maintenance($agency_maintenance_data);

                            // update all jobs to dha processing
                            $this->db->query("
                            UPDATE `jobs` AS j
                            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                            SET j.`dha_need_processing` = 1
                            WHERE a.`agency_id` = {$agency_id}	
                            AND j.`del_job` = 0
                            AND j.`status` NOT IN('Completed','Cancelled')
                            ");

                        }else{
                            // update all jobs to dha processing
                            $this->db->query("
                            UPDATE `jobs` AS j
                            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                            SET j.`dha_need_processing` = 0
                            WHERE a.`agency_id` = {$agency_id}	
                            AND j.`del_job` = 0
                            AND j.`status` NOT IN('Completed','Cancelled')
                            ");
                        }

                        ##LOGS > Fields Edited
                        $this->_insertAgencyLogs($post,1);

                        $jdata['status'] = true;

                    }

                }else if($type=="btn_update_agency_landline"){ 
                    
                    $phone = $post['phone'];

                    if($phone!=""){

                        $agency_data = array(
                            'phone' => $phone
                        );
                        $this->agency_model->update_agency($agency_id,$agency_data);

                        ##LOGS > Fields Edited
                        $this->_insertAgencyLogs($post,1);

                        $jdata['status'] = true;

                    }
                    
                }else if($type=="btn_update_agency_contact"){ 

                    $ac_fname = $post['ac_fname'];
                    $ac_lname = $post['ac_lname'];
                    $ac_phone = $post['ac_phone'];
                    $ac_email = $post['ac_email'];

                    $agency_data = array(
                        'contact_first_name' => $ac_fname,
                        'contact_last_name' => $ac_lname,
                        'contact_phone' => $ac_phone,
                        'contact_email' => $ac_email
                    );
                    $this->agency_model->update_agency($agency_id,$agency_data);

                    ##LOGS > Fields Edited
                    $this->_insertAgencyLogs($post,1);

                    $jdata['status'] = true;

                }else if($type=="btn_update_agency_account_contact"){ 

                    $acc_name = $post['acc_name'];
                    $acc_phone = $post['acc_phone'];

                    $agency_data = array(
                        'accounts_name' => $acc_name,
                        'accounts_phone' => $acc_phone
                    );
                    $this->agency_model->update_agency($agency_id,$agency_data);

                    ##LOGS > Fields Edited
                    $this->_insertAgencyLogs($post,1);

                    $jdata['status'] = true;

                }else if($type=="btn_update_agency_tenant"){ 

                    $tdc_name = $post['tdc_name'];
                    $tdc_phone = $post['tdc_phone'];

                    $agency_data = array(
                        'tenant_details_contact_name' => $tdc_name,
                        'tenant_details_contact_phone' => $tdc_phone
                    );
                    $this->agency_model->update_agency($agency_id,$agency_data);

                    ##LOGS > Fields Edited
                    $this->_insertAgencyLogs($post,1);

                    $jdata['status'] = true;

                }else if($type=="btn_update_agency_notes"){ 

                    $agency_specific_notes = $post['agency_specific_notes'];
                    $comment = $post['comment'];

                    $agency_data = array(
                        'agency_specific_notes' => $agency_specific_notes,
                        'comment' => $comment
                    );
                    $this->agency_model->update_agency($agency_id,$agency_data);

                    ##LOGS > Fields Edited
                    $this->_insertAgencyLogs($post,1);

                    $jdata['status'] = true;

                }else if($type=="btn-update-website"){

                    $website = $post['website'];

                    $agency_data = array(
                        'website' => $website
                    );
                    $this->agency_model->update_agency($agency_id,$agency_data);

                    ##LOGS > Fields Edited
                    $this->_insertAgencyLogs($post,1);

                    $jdata['status'] = true;

                }else if($type=="btn-update-joined-sats"){

                    $joined_sats = ( $post['joined_sats']!='') ? $this->system_model->formatDate( $post['joined_sats']) : NULL;

                    $agency_data = array(
                        'joined_sats' => $joined_sats
                    );
                    $this->agency_model->update_agency($agency_id,$agency_data);

                    ##LOGS > Fields Edited
                    $this->_insertAgencyLogs($post,1);

                    $jdata['status'] = true;

                }else if($type=="from_other_company_btn_update"){ // from other smoke alarm company update
                    
                    $from_other_company = $this->input->get_post('from_other_company');
                    $today_full = date('Y-m-d H:i:s');

                    // property added from other company
                    // check if data already exist
                    $afoc_sql = $this->db->query("
                    SELECT 
                        afoc.`afoc_id`,
                        sac.`company_name`
                    FROM `agencies_from_other_company` AS afoc
                    LEFT JOIN `smoke_alarms_company` AS sac ON afoc.`company_id` = sac.`sac_id`
                    WHERE afoc.`agency_id` = {$agency_id}
                    AND afoc.`active` = 1
                    ");
                    $afoc_row = $afoc_sql->row();

                    if( $from_other_company > 0 ){

                        // get company name
                        $company_sql = $this->db->query("
                        SELECT `company_name`
                        FROM `smoke_alarms_company`
                        WHERE `sac_id` = {$from_other_company}
                        ");
                        $company_row = $company_sql->row();

                    }

                    if( $afoc_sql->num_rows() > 0 ){ // if exist, update

                        if( $from_other_company > 0 ){ // if company is selected

                            // deactivate current active one
                            $this->db->query("
                            UPDATE `agencies_from_other_company`
                            SET `active` = 0
                            WHERE `agency_id` = {$agency_id}
                            AND `active` = 1
                            AND `afoc_id` = {$afoc_row->afoc_id}
                            ");

                            // insert new
                            $this->db->query("
                            INSERT INTO 
                            `agencies_from_other_company`(
                                `company_id`,
                                `agency_id`,
                                `added_date`
                            )
                            VALUE(
                                {$from_other_company},
                                {$agency_id},
                                '{$today_full}'
                            )	
                            ");

                            //insert log
                            $log_details = "Agency detail <b>From Other Company</b> has been updated from <b>{$afoc_row->company_name}</b> to <b>{$company_row->company_name}</b>";
                            $log_params = array(
                                'title' => 46,  // Agency Update
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($log_params);

                        }else{ // clear

                            $this->db->query("
                            UPDATE `agencies_from_other_company`
                            SET `active` = 0
                            WHERE `agency_id` = {$agency_id}
                            AND `active` = 1
                            ");

                            //insert log
                            $log_details = "This agency has been <b>unmarked</b> as acquired from <b>{$afoc_row->company_name}</b>";
                            $log_params = array(
                                'title' => 46,  // Agency Update
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($log_params);                          

                        }
                        

                    }else{ // doesnt exist, insert

                        if( $from_other_company > 0 ){

                            $this->db->query("
                            INSERT INTO 
                            `agencies_from_other_company`(
                                `company_id`,
                                `agency_id`,
                                `added_date`
                            )
                            VALUE(
                                {$from_other_company},
                                {$agency_id},
                                '{$today_full}'
                            )	
                            ");

                            //insert log
                            $log_details = "This agency has been <b>marked</b> as acquired from <b>{$company_row->company_name}</b>";
                            $log_params = array(
                                'title' => 46,  // Agency Update
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'agency_id' => $agency_id
                            );
                            $this->system_model->insert_log($log_params);

                        }		

                    }
                    
                }
                

        }else{
            $jdata['status'] = false;
        }

       
        echo json_encode($jdata);
        
    }

    private function email_agency_update($params){
        $this->load->library('email');

        ##get salesrep name
        $og_salesrep_params = array('sel_query' => 'FirstName, LastName','staff_id'=>$params['post']['og_salesrep']);
        $og_salesrep_row = $this->gherxlib->getStaffInfo($og_salesrep_params)->row_array();
        $og_salesrep_name = $this->gherxlib->formatStaffName($og_salesrep_row['FirstName'], $og_salesrep_row['LastName']);

        $salesrep_params = array('sel_query' => 'FirstName, LastName','staff_id'=>$params['post']['salesrep']);
        $salesrep_row = $this->gherxlib->getStaffInfo($salesrep_params)->row_array();
        $new_salesrep_name = $this->gherxlib->formatStaffName($salesrep_row['FirstName'], $salesrep_row['LastName']);

        $agency_name_q = $this->db->select('agency_name')->from('agency')->where('agency_id', $params['agency_id'])->get()->row_array();

        ## Email data
       //$email_data['orig_agency_name']  = $params['post']['og_agency_name'];
       $email_data['orig_agency_name']  = $agency_name_q['agency_name'];
        //$email_data['agency_name'] = $params['post']['agency_name'];
        $email_data['agency_name'] = $params['post']['agency_name'];
        $email_data['orig_legal_name'] = $params['post']['og_legal_name'];
        $email_data['legal_name'] = $params['post']['legal_name'];
        $email_data['orig_abn'] = $params['post']['og_abn'];
        $email_data['abn'] = $params['post']['abn'];
        $email_data['orig_agency_emails'] = $params['post']['og_agency_emails'];
        $email_data['agency_emails'] = $params['post']['agency_emails'];
        $email_data['orig_account_emails'] = $params['post']['og_account_emails'];
        $email_data['account_emails'] =$params['post']['account_emails'];
        $email_data['orig_salesrep'] = $params['post']['og_salesrep'];
        $email_data['salesrep'] = $params['post']['salesrep'];
        $email_data['orig_salesrep_name'] = $og_salesrep_name;
        $email_data['new_salesrep_name'] = $new_salesrep_name;

        $getCountryInfo = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));
        $email_from = "SATS - Smoke Alarm Testing Services - ".$getCountryInfo->outgoing_email;
        $email_to = $this->config->item('sats_accounts_email');
        //$email_to = "itsmegherx@gmail.com";
        //$email_to = "antonyk@sats.com.au";
        $email_subject = "MYOB Update Required";

        //email config
        $config = Array(
            'mailtype' => 'html',
            'charset' => 'iso-8859-1'
        );
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");

        $this->email->from($email_from, 'CRM');
        $this->email->to($email_to);
        $this->email->subject($email_subject);
        $body = $this->load->view('emails/update_agency_email.php', $email_data, TRUE);
        $this->email->message($body);
        $this->email->send();

    }

    public function update_total_properties(){

        $jdata['status'] = false;
        $agency_id = $this->input->post('agency_id');
        $total_prop = $this->input->post('total_prop');
        $og_total_prop = $this->input->post('og_total_prop');
        
        if($agency_id>0){
            $update_data = array(
                'tot_properties' => $total_prop
            );
            $this->db->where('agency_id',$agency_id);
            $this->db->update('agency', $update_data);
            $this->db->limit(1);

            //insert log
            if($og_total_prop != $total_prop){

                $log_details = "Agency total properties updated from {$this->format_empty_val($og_total_prop)} to {$this->format_empty_val($total_prop)}";
                $log_params = array(
                    'title' => 46, 
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);

            }

            $jdata['status'] = true;
        }

        echo json_encode($jdata);

    }
    

    public function hume_job_logs() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Hume Job Logs";
        $country_id = $this->config->item('country');
        $uri = '/agency/hume_job_logs';
        $data['uri'] = $uri;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');                    

        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 ){ 
            $limit_sql_str = "LIMIT {$offset}, {$per_page}";
        }    

        $hume_house_agency_id = 1598; // Hume Housing     
        //$hume_house_agency_id = 1448; // adams                         
        
        // get paginated list
        // get old logs via contact type and get new logs via SMS(40) and Email(78) sent
        $job_log_main_sql_str = "
        (
            SELECT
                DISTINCT(p.`property_id`),
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`
            FROM `job_log` AS jl
            LEFT JOIN `jobs` AS j ON jl.`job_id` = j.`id` 
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`agency_id` = {$hume_house_agency_id}                
            AND j.`status` = 'To Be Booked'
            AND j.`del_job` = 0
            AND p.`deleted` = 0                    
            AND jl.`deleted` = 0                                        
            AND jl.`contact_type` IN (
                'Phone Call',
                'E-mail',
                'SMS Sent',
                'Work Order',
                'Unavailable',
                'Problematic',
                'SMS Received',
                'Duplicate Property'
            )                        
        )   
        UNION
        (
            SELECT
                DISTINCT(p.`property_id`),
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`
            FROM `logs` AS l
            LEFT JOIN `jobs` AS j ON l.`job_id` = j.`id` 
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`agency_id` = {$hume_house_agency_id}               
            AND j.`status` = 'To Be Booked'
            AND j.`del_job` = 0
            AND p.`deleted` = 0                    
            AND l.`deleted` = 0                                        
            AND l.`title` IN (40,78)                        
        ) 
        ORDER BY address_2 ASC, address_1 ASC
        {$limit_sql_str}           
        ";   

        $job_log_main_sql = $this->db->query($job_log_main_sql_str)->result();

        // get all property ID
        $property_id_arr = [];
        foreach ( $job_log_main_sql as $row ){          
            $property_id_arr[] = $row->property_id;
        }

        if( count($property_id_arr) > 0 ){

            $property_id_imp = implode(",",$property_id_arr);

            // get old logs via contact type and get new logs via SMS(40) and Email(78) sent 
            $job_log_merge_sql_str = "
            (
                SELECT
                    jl.`comments` AS jl_comments,
                    jl.`eventdate` AS jl_date,
            
                    p.`property_id`,
                    p.`address_1`,
                    p.`address_2`,
                    p.`address_3`,
                    p.`state`,
                    p.`postcode`,
                    p.`compass_index_num`
                FROM `job_log` AS jl
                LEFT JOIN `jobs` AS j ON jl.`job_id` = j.`id` 
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE a.`agency_id` = {$hume_house_agency_id}    
                AND p.`property_id` IN({$property_id_imp})
                AND j.`status` = 'To Be Booked'
                AND j.`del_job` = 0
                AND p.`deleted` = 0                    
                AND jl.`deleted` = 0                                        
                AND jl.`contact_type` IN (
                    'Phone Call',
                    'E-mail',
                    'SMS Sent',
                    'Work Order',
                    'Unavailable',
                    'Problematic',
                    'SMS Received',
                    'Duplicate Property'
                )                        
            )   
            UNION
            (
                SELECT
                    l.`details` AS jl_comments,
                    DATE(l.`created_date`) AS jl_date,
            
                    p.`property_id`,
                    p.`address_1`,
                    p.`address_2`,
                    p.`address_3`,
                    p.`state`,
                    p.`postcode`,
                    p.`compass_index_num`
                FROM `logs` AS l
                LEFT JOIN `jobs` AS j ON l.`job_id` = j.`id` 
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE a.`agency_id` = {$hume_house_agency_id}   
                AND p.`property_id` IN({$property_id_imp})
                AND j.`status` = 'To Be Booked'
                AND j.`del_job` = 0
                AND p.`deleted` = 0                    
                AND l.`deleted` = 0                                        
                AND l.`title` IN (40,78)                        
            ) 
            ORDER BY address_2 ASC, address_1 ASC, jl_date DESC
            ";  

            $job_log_merge_sql = $this->db->query($job_log_merge_sql_str)->result();

        }

        
        // merge/join two queries
        $merge_query_arr = [];
        foreach ($job_log_main_sql as $postcode_row) {

            // merged job logs logs   
            $count = 0;      
            $log_limit = 6;   
            foreach ($job_log_merge_sql as $job_log_merge_row) {
                                
                if ( $postcode_row->property_id == $job_log_merge_row->property_id ) { // match   

                    $count++;
                    
                    // get row object
                    $merge_query_arr[] = $job_log_merge_row; 
                    if( $count == $log_limit ){
                        break;
                    }                                                                            

                }                

            }

        }                 
        
        if ( $export == 1 ) { // EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "hume_job_logs_export_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = array("Property Address","Property Code","Log","Log Date");
            fputcsv($csv_file, $header);            
                                    
            foreach ( $merge_query_arr as $row_inner ){

                $csv_row = []; 

                $prop_address = "{$row_inner->address_1} {$row_inner->address_2}, {$row_inner->address_3}";

                $csv_row[] = $prop_address;
                $csv_row[] = $row_inner->compass_index_num;   
                $csv_row[] = strip_tags($row_inner->jl_comments);          
                $csv_row[] = date("d/m/Y", strtotime($row_inner->jl_date));  
                
                fputcsv($csv_file,$csv_row); 

            }
                                
            fclose($csv_file); 
            exit; 

        }else{             

            $data['list'] = $job_log_main_sql;
            $data['page_query'] = $job_log_main_sql_str;

            // get all
            $property_sql_str = "
            (
                SELECT
                    DISTINCT(p.`property_id`),
                    p.`address_1`,
                    p.`address_2`,
                    p.`address_3`,
                    p.`state`,
                    p.`postcode`
                FROM `job_log` AS jl
                LEFT JOIN `jobs` AS j ON jl.`job_id` = j.`id` 
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE a.`agency_id` = {$hume_house_agency_id}                
                AND j.`status` = 'To Be Booked'
                AND j.`del_job` = 0
                AND p.`deleted` = 0                    
                AND jl.`deleted` = 0                                        
                AND jl.`contact_type` IN (
                    'Phone Call',
                    'E-mail',
                    'SMS Sent',
                    'Work Order',
                    'Unavailable',
                    'Problematic',
                    'SMS Received',
                    'Duplicate Property'
                )                        
            )   
            UNION
            (
                SELECT
                    DISTINCT(p.`property_id`),
                    p.`address_1`,
                    p.`address_2`,
                    p.`address_3`,
                    p.`state`,
                    p.`postcode`
                FROM `logs` AS l
                LEFT JOIN `jobs` AS j ON l.`job_id` = j.`id` 
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE a.`agency_id` = {$hume_house_agency_id}               
                AND j.`status` = 'To Be Booked'
                AND j.`del_job` = 0
                AND p.`deleted` = 0                    
                AND l.`deleted` = 0                                        
                AND l.`title` IN (40,78)                        
            )                                  
            ";
            $property_sql = $this->db->query($property_sql_str);
            $total_rows = $property_sql->num_rows();                

            
            $pagi_links_params_arr = array();
            
            // pagination link
            $pagi_link_params = "{$uri}/?".http_build_query($pagi_links_params_arr);

            // explort link
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

            $this->load->view('templates/inner_header', $data);
            $this->load->view('reports/hume_job_logs', $data);
            $this->load->view('templates/inner_footer', $data);

        }                
                

    }

    public function ajax_load_vad_addresses(){
        $agency_id= $this->input->post('agency_id');
        $type= $this->input->post('type');

        if($type!=""){ //query from agency_addresses table

            $get_agency_addresses = $this->db->select('*')
                ->from('agency_addresses as aa')
                ->where('aa.agency_id', $agency_id)
                ->where('aa.type', $type)
                ->get()->row_array();

            $data['row'] = $get_agency_addresses;

        }else{ //default
            $sel_query = "
            a.agency_id as a_id, 
            a.agency_name,
            a.state,
            a.address_1, 
            a.address_2, 
            a.address_3, 
            a.state, 
            a.postcode,
            a.postcode_region_id,
            sr.subregion_name as postcode_region_name
            ";
            $params = array(
                'sel_query' => $sel_query,
                'join_table' => array('country','postcode','postcode_regions'),
                'country_id' => COUNTRY,
                'agency_id' => $agency_id
            );
            $data['row'] = $this->agency_model->get_agency($params)->row_array();
        }
        
        $data['getCountryState'] = $this->properties_model->getCountryState();

        $this->load->view('agency/tab/vad_ajax_addresses', $data);
    }

    public function ajax_check_agency_addresses_duplicate(){
        $agency_id = $this->input->post('agency_id');
        $address_type = $this->input->post('address_type');
        
        $status  = false;
        $data  = null;
        $tt = $this->db->select('COUNT(id) AS count')
            ->from('agency_addresses')
            ->where('agency_id',$agency_id)
            ->where('type',$address_type)
            ->get()->row()->count;

        if($address_type == 2){
            $data = $this->db->select('*')
                ->from('agency_addresses')
                ->where('agency_id',$agency_id)
                ->where('type',$address_type)
                ->get()->result();
            //echo $this->db->last_query();
        }
        
        
        if($tt>0){
            $status  = true;
        }else{
            $status  = false;
        }

        echo json_encode([
            'status' => $status,
            'data' => $data,
        ]);
    }

    public function ajax_delete_agency_addresses(){
        $jdata['status'] = false;
        $post = $this->input->post();
        $agency_id = $post['agency_id'];
        $address_type = $post['address_type'];

        if( $agency_id>0 && $address_type>0 ){

            $fulladdress_q = $this->db->select('*')
                ->from('agency_addresses')
                ->where('agency_id',$agency_id)
                ->where('type',$address_type)
                ->get()->row_array();

            $fulladdress = "{$fulladdress_q['address_1']} {$fulladdress_q['address_2']} {$fulladdress_q['address_3']} {$fulladdress_q['state']}";

            //delete address
            $this->db->where('agency_id',$agency_id);
            $this->db->where('type',$address_type);
            $this->db->delete('agency_addresses');
            $this->db->limit(1);

            //insert delete log
            $log_details = "<strong>{$this->agency_model->vad_address_type_name($address_type)}</strong> at <strong>{$fulladdress}</strong> has been removed";
            $log_params = array(
                'title' => 46,  
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);

            $jdata['status'] = true;

        }else{
            $jdata['status'] = false;
        }
        echo json_encode($jdata);
    }

    public function get_key_address(){
        $id = $this->input->post('id');
        $data = $this->db->select('*')
            ->from('agency_addresses')
            ->where('id',$id)
            ->get()->row_array();
        echo json_encode($data);
    }

    public function add_key_address(){
        $this->load->model('properties_model');
        $jdata['status'] = false;

        $post = $this->input->post();

        $id = $post['edit_id'];
        $agency_id = $post['agency_id'];
        $address_1 = $post['address_1'];
        $address_2 = $post['address_2'];
        $address_3 = $post['address_3'];
        $state = $post['state'];
        $postcode = $post['postcode'];
        $fullAdd = $post['fullAdd'];
        $address_type = $post['address_type'];

        ##Get google coordinates
        $address = "{$address_1} {$address_2} {$address_3} {$state} {$postcode}, {$this->config->item('country')}";
        $coordinates = $this->system_model->getGoogleMapCoordinates($address);

        if(empty($id)){
            $check_addresses = $this->db->select('COUNT(id) AS count')
            ->from('agency_addresses')
            ->where('address_1',$address_1)
            ->where('address_2',$address_2)
            ->where('address_3',$address_3)
            ->where('state',$state)
            ->where('postcode',$postcode)
            ->where('agency_id',$agency_id)
            ->get()->row()->count;

            if($check_addresses > 0){
                $jdata['duplicate'] = true;
                $duplicate = 1;
            }
        }
        else{
            $check_addresses = $this->db->select('COUNT(id) AS count')
            ->from('agency_addresses')
            ->where('id',$id)
            ->where('agency_id',$agency_id)
            ->get()->row()->count;
        }

        if($check_addresses > 0 && $duplicate == 0){ //address exist do update   
            $update_data = array(
                'address_1' => $address_1,
                'address_2' => $address_2,
                'address_3' => $address_3,
                'state' => $state,
                'postcode' => $postcode,
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng']
            );
            $this->db->where('id', $id);
            $this->db->where('agency_id',$agency_id);
            $this->db->update('agency_addresses',$update_data);
            ##LOGS > Fields Edited
            $this->_insertAgencyLogs($post,1);  
            $jdata['status'] =  true;
            $jdata['update'] = true;
        }
        else if($check_addresses == 0 && $duplicate == 0){ //not exist do ad    
            ##add new agency addresses
            $add_data = array(
                'agency_id' => $agency_id,
                'address_1' => $address_1,
                'address_2' => $address_2,
                'address_3' => $address_3,
                'state' => $state,
                'postcode' => $postcode,
                'type' => $address_type,
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng']
            );
            if($address_1 != "" && $address_2 != "" && $address_3 != "" && $state != "" && $postcode != ""){
                $this->db->insert('agency_addresses', $add_data); 
                ##LOGS >Add new address log
                $log_params = array(
                    'title' => 46,
                    'details' => "<strong>{$address}</strong> added as a <strong>{$this->agency_model->vad_address_type_name($address_type)}</strong>",
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params); 
                $jdata['status'] =  true;
                $jdata['add'] = true;
            }
            else{
                $jdata['status'] =  false;
            }
            
        }

        echo json_encode($jdata);
    }

    public function delete_keyAddress(){
        $jdata['status'] = false;
        $post = $this->input->post();
        $id = $post['id'];
        $agency_id = $post['agency_id'];

        if($id>0){

            $fulladdress_q = $this->db->select('*')
                ->from('agency_addresses')
                ->where('id',$id)
                ->where('agency_id',$agency_id)
                ->get()->row_array();

            $fulladdress = "{$fulladdress_q['address_1']} {$fulladdress_q['address_2']} {$fulladdress_q['address_3']} {$fulladdress_q['state']}";

            //delete address
            $this->db->where('id',$id);
            $this->db->where('agency_id',$agency_id);
            $this->db->delete('agency_addresses');
            $this->db->limit(1);

            //insert delete log
            $log_details = "<strong>{$this->agency_model->vad_address_type_name($address_type)}</strong> at <strong>{$fulladdress}</strong> has been removed";
            $log_params = array(
                'title' => 46,  
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);

            $jdata['status'] = true;

        }else{
            $jdata['status'] = false;
        }
        echo json_encode($jdata);
    }


    public function ajax_high_touch_update() {

        $agency_id = $this->input->get_post('agency_id');
        $priority = $this->input->get_post('priority');
        $success = 0;
        
        if( $agency_id > 0 ){
            // update
            $update_data = array(
                'priority' => $priority
            );
            
            $this->db->where('agency_id', $agency_id);
            $ret = $this->db->update('agency', $update_data);
                
            if( $ret == true ){
                $success = 1;

                $marked_str = ( $priority > 0 )?'marked':'unmarked';

                //insert log
                $log_details = "TEST: Agency <b>{$marked_str}</b> as Agency Priority";
                $log_params = array(
                    'title' => 46,  // Agency Update
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);
            }
        }    
        
        $ret_json = array(
            'success' => $success
        );

        echo json_encode($ret_json);
    }

    public function agency_health_check($agency_id=null) {


        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Health Check";
        $data['agency_id'] = $agency_id;

        //total property
        $total_prop = "SELECT COUNT(p.`property_id`) as total, a.state
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        WHERE a.agency_id = '$agency_id' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0
        AND a.deleted = 0";
        $query = $this->db->query($total_prop);
        $data['total_prop'] = $query->row()->total;
        $data['state'] = $query->row()->state;


        //active services
        $prop_custom_where = "ps.service = 1 {$alarm_job_type_id_filter} AND p.is_sales!=1 AND (j.prop_comp_with_state_leg=1 OR j.prop_comp_with_state_leg IS NULL)";
        $params_prop = array(
            'sel_query' => "p.property_id, a.joined_sats",
            'agency_filter' => $agency_id,
            'is_nlm' => 0, ##dont display nlm prop in SATS tab
            'join_table' => array('property_services','jobs'),
            'custom_where_arr' => array($prop_custom_where),
            'group_by' => 'p.property_id'

        );
        $property = $this->properties_model->get_properties($params_prop);
        $data['prop_active_services'] = $property->num_rows();
        //joined sats
        $data['joined_sats'] = $property->row()->joined_sats;

        //show api
        $select_api = "SELECT agen_api.api_name
        FROM `agency_api_integration` AS agen_api_int
        LEFT JOIN `agency_api` AS agen_api ON agen_api_int.`connected_service` = agen_api.`agency_api_id`
        LEFT JOIN `agency` AS a ON agen_api_int.`agency_id` = a.`agency_id`
        WHERE a.agency_id = '$agency_id' AND a.deleted = 0";
        $query = $this->db->query($select_api);
        $data['api'] = $query;

        //New properties for the last 30 days
        $prop_new_30 = "SELECT COUNT(p.`property_id`) as total_new_prop
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        WHERE a.agency_id = '$agency_id' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0 AND a.deleted = 0 AND created > NOW( ) - INTERVAL 30 DAY";
        $query = $this->db->query($prop_new_30);
        $data['prop_new_30'] = $query->row()->total_new_prop;

        //Deactivate properties for the last 30 days
        $prop_dec_30 = "SELECT COUNT(p.`property_id`) as total_dec_prop
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        WHERE a.agency_id = '$agency_id' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0 AND a.deleted = 0 AND nlm_timestamp > NOW( ) - INTERVAL 30 DAY";
        $query = $this->db->query($prop_dec_30);
        $data['prop_dec_30'] = $query->row()->total_dec_prop;

        //Send letters Jobs
        $jobs_send_letters = "SELECT COUNT( j.`id` ) AS total_send_letters
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        INNER JOIN jobs AS j ON p.property_id = j.property_id
        WHERE a.agency_id = '$agency_id'
        AND j.status = 'Send Letters' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0
        AND a.deleted = 0";
        $query = $this->db->query($jobs_send_letters);
        $data['jobs_send_letters'] = $query->row()->total_send_letters;

        //Booked Jobs
        $jobs_booked = "SELECT COUNT( j.`id` ) AS total_booked
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        INNER JOIN jobs AS j ON p.property_id = j.property_id
        WHERE a.agency_id = '$agency_id'
        AND j.status = 'Booked' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0
        AND a.deleted = 0";
        $query = $this->db->query($jobs_booked);
        $data['jobs_booked'] = $query->row()->total_booked;

        //On Hold Jobs
        $jobs_on_hold = "SELECT COUNT( j.`id` ) AS total_on_hold
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        INNER JOIN jobs AS j ON p.property_id = j.property_id
        WHERE `j`.`del_job` = 0
        AND a.agency_id = '$agency_id'
        AND j.status = 'On Hold' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0
        AND a.deleted = 0";
        $query = $this->db->query($jobs_on_hold);
        $data['jobs_on_hold'] = $query->row()->total_on_hold;

        //Escalate Jobs
        $jobs_escalate = "SELECT COUNT( j.`id` ) AS total_escalate
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        INNER JOIN jobs AS j ON p.property_id = j.property_id
        WHERE a.agency_id = '$agency_id'
        AND j.status = 'Escalate' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0
        AND a.deleted = 0";
        $query = $this->db->query($jobs_escalate);
        $data['jobs_escalate'] = $query->row()->total_escalate;

        //Allocate Jobs
        $jobs_allocate = "SELECT COUNT( j.`id` ) AS total_allocate
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        INNER JOIN jobs AS j ON p.property_id = j.property_id
        WHERE a.agency_id = '$agency_id'
        AND j.status = 'Allocate' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0
        AND a.deleted = 0";
        $query = $this->db->query($jobs_allocate);
        $data['jobs_allocate'] = $query->row()->total_allocate;

        //To Be Booked Jobs
        $jobs_to_be_booked = "SELECT COUNT( j.`id` ) AS total_to_be_booked
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        INNER JOIN jobs AS j ON p.property_id = j.property_id
        WHERE a.agency_id = '$agency_id'
        AND j.status = 'To Be Booked' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0
        AND a.deleted = 0
        AND j.`del_job` = 0";
        $query = $this->db->query($jobs_to_be_booked);
        $data['jobs_to_be_booked'] = $query->row()->total_to_be_booked;

        //Merged Certificates Jobs
        $jobs_merged = "SELECT COUNT( j.`id` ) AS total_merged
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        INNER JOIN jobs AS j ON p.property_id = j.property_id
        WHERE a.agency_id = '$agency_id'
        AND j.status = 'Merged Certificates' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0
        AND a.deleted = 0";
        $query = $this->db->query($jobs_merged);
        $data['jobs_merged'] = $query->row()->total_merged;

        //Completed Jobs 30 Days
        $jobs_completed = "SELECT COUNT( j.`id` ) AS total_completed
        FROM `property` AS p
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        INNER JOIN jobs AS j ON p.property_id = j.property_id
        WHERE a.agency_id = '$agency_id'
        AND j.status = 'Completed' AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0 AND a.deleted = 0 AND j.created > NOW( ) - INTERVAL 30 DAY";
        $query = $this->db->query($jobs_completed);
        $data['jobs_completed'] = $query->row()->total_completed;
        
        //get missed jobs
        $custom_where = 'jnc.door_knock != 1 AND jnc.reason_id NOT IN(16, 32, 33)';
        $sel_query = "
            j.`id` AS jid, 
            j.`door_knock`,
            j.`created` AS jcreated,
            j.job_price,
            j.job_type,
            
            jnc.jobs_not_completed_id,
            jnc.reason_id,
            jnc.reason_comment,
            jnc.tech_id as jnc_tech_id,
            jnc.door_knock as jnc_door_knock,
            jnc.date_created as jnc_date_created,

            ass_tech.`StaffID` AS jl_staff_id,
            ass_tech.`FirstName` AS jl_staff_fname,
            ass_tech.`LastName` AS jl_staff_lname,
            
            jr.`name` AS jr_name,  
            jr.log_message,
            
            p.`property_id`,
            p.`address_1`, 
            p.`address_2`, 
            p.`address_3`,

            a.agency_id,
            a.`agency_name`,
            aght.priority
        ";
        $params = array(
            'sel_query' => $sel_query,

            '30_days' => '1',
            'agency_filter' => $agency_id,
            'custom_where' => $custom_where,
            'sort_list' => array(
                array(
                    'order_by' => 'jnc.date_created',
                    'sort' => 'DESC',
                ),
            ),
            'display_query' => 0
            
        );

        $data['lists'] = $this->jobs_model->getJobsNotCompletedV3($params);
        // $data['jobs_missed'] = $query->num_rows();

        //For the Job feedback sms
        $sms_type = 18;
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        // list
        $cust_sel = "
                sas.`sms_api_sent_id`,
                sas.`sent_by`,
                sas.`sms_type`,
                sas.`job_id`,

                sar.`sms_api_replies_id`,
                sar.`message_id`,
                sar.`created_date` AS sar_created_date,
                sar.`mobile` AS sar_mobile,
                sar.`response`,
                sar.`saved`,
                sar.`unread`,

                sa.`FirstName`,
                sa.`LastName`,

                sat.`type_name`,
                sat.`sms_api_type_id`,

                p.`property_id`,
                p.`address_1`, 
                p.`address_2`, 
                p.`address_3`,

                ass_tech.`StaffID` as at_StaffID,
                ass_tech.`FirstName` as at_FirstName,
                ass_tech.`LastName` as at_LastName
                ";
        $list_params = array(
            'sort_list' => array(
                array(
                    'order_by' => 'sar.`datetime_entry`',
                    'sort' => 'DESC'
                )
            ),
            '30_days' => '1',
            'agency_filter' => $agency_id,
            'echo_query' => 0,
            'sms_type' => $sms_type,
            'sms_page' => 'incoming',
            'custom_select' => $cust_sel,

        );

        $sms_sql = $this->sms_model->getSMSrepliesMergedData($list_params);
        $data['list'] = $sms_sql;

        //QLD Failed Jobs
        $jobs_failed_qld = "SELECT COUNT( j.`id` ) AS total_QLD
        FROM jobs AS j
        INNER JOIN `property` AS p ON j.property_id = p.property_id
        INNER JOIN agency AS a ON a.agency_id = p.agency_id
        WHERE a.agency_id = '$agency_id'
        AND p.state = 'QLD'
        AND j.`status` = 'Completed'
        AND (
        j.`start_date` > j.`date`
        OR j.due_date < j.`date`
        ) AND (p.is_nlm = 0 OR p.is_nlm IS NULL)
        AND p.deleted = 0
        AND a.deleted = 0
        AND j.date > NOW( ) - INTERVAL 30 DAY";
        $query = $this->db->query($jobs_failed_qld);
        $data['jobs_failed_qld'] = $query->row()->total_QLD;

        //NSW Failed Jobs
        $jobs_failed_nsw = "SELECT p.property_id, j.`id` , j.date, j.status
        FROM jobs AS j
        LEFT JOIN `property` AS p ON j.property_id = p.property_id
        LEFT JOIN agency AS a ON a.agency_id = p.agency_id
        WHERE a.agency_id = '$agency_id'
        AND p.state = 'NSW'
        AND j.`status` = 'Completed'
        AND j.job_type = 'Yearly Maintenance'
        AND (
        p.is_nlm =0
        OR p.is_nlm IS NULL
        )
        AND p.deleted =0
        AND a.country_id =1
        AND j.date > NOW( ) - INTERVAL 30 DAY
        AND a.deleted = 0
        AND p.property_id
        IN (
        
        SELECT DISTINCT p2.property_id
        FROM jobs AS j2
        LEFT JOIN `property` AS p2 ON j2.property_id = p2.property_id
        LEFT JOIN agency AS a2 ON a2.agency_id = p2.agency_id
        WHERE a2.agency_id = '$agency_id'
        AND p2.state = 'NSW'
        AND j2.`status` = 'Completed'
        AND j2.job_type = 'Yearly Maintenance'
        AND (
        p2.is_nlm =0
        OR p2.is_nlm IS NULL
        )
        AND p2.deleted =0
        AND a2.country_id =1
        AND j2.id != j.id
        AND j.date >= ( j2.date + INTERVAL 365
        DAY )
        )
        GROUP BY p.property_id";
        $query = $this->db->query($jobs_failed_nsw);
        $data['jobs_failed_nsw'] = $query->num_rows();

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/agency_health_check', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function update_agency_price_variation(){

        $agency_id = $this->input->post('agency_id');
        $type = $this->input->post('agency_var_type');
        $agency_var_type_text = $this->input->post('agency_var_type_text');
        $amount = $this->input->post('agency_var_amount');
        $reason = $this->input->post('agency_var_reason');
        $agency_var_reason_text = $this->input->post('agency_var_reason_text');
        $scope = $this->input->post('agency_var_scope');
        $agency_var_scope_text = $this->input->post('agency_var_scope_text');
        $display_on = $this->input->post('apv_display_on');
        $apv_display_on_text = $this->input->post('apv_display_on_text');
        $expiry = ($this->system_model->isDateNotEmpty($this->input->post('apv_expiry')))?$this->system_model->formatDate($this->input->post('apv_expiry')):null;

        $apv_id = $this->input->post('apv_id');  
        $date = date('Y-m-d H:i:s');

        if( $apv_id > 0 ){ // update

            $apv_sql = $this->db->query("
            SELECT 
                apv.`id` AS apv_id,
                apv.`amount`,
                apv.`type` AS apv_type,
                apv.`reason` AS apv_reason,
                apv.`scope`,
                apv.`expiry`,

                apvr.`reason` AS apvr_reason,

                dv.`id` AS dv_id,
                dv.`display_on`,

                disp_on.`location`,

                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `agency_price_variation` AS apv
            LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
            LEFT JOIN `display_variation` AS dv ON ( apv.`id` = dv.`variation_id` AND dv.`type` = 1 )
            LEFT JOIN `display_on` AS disp_on ON dv.`display_on` = disp_on.`id`
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`id` = {$apv_id}                                
            ");    
            $apv_row = $apv_sql->row();
            
            // current
            $scope_str = null;
            if( $apv_row->scope == 0 && is_numeric($apv_row->scope) ){
                $scope_str = 'Agency';
            }else if( $apv_row->scope == 1 ){
                $scope_str = 'Property';
            }else{
                $scope_str = "{$apv_row->short_name} Service";
            }

            $discount_str = ( $apv_row->apv_type == 1 )?'Discount':'Surcharge';
            $expiry_str = ( $this->system_model->isDateNotEmpty($apv_row->expiry) )?' expiring on <b>'.date('d/m/Y', strtotime($apv_row->expiry)).'</b>':null;
            $display_on_str = ( $apv_row->display_on > 0 )?", displaying on <b>{$apv_row->location}</b>":'<b>nowhere</b>';

            // new
            $new_discount_str = ( $type == 1 )?'Discount':'Surcharge';
            $new_expiry_str = ( $this->system_model->isDateNotEmpty($expiry) )?' expiring on <b>'.date('d/m/Y', strtotime($expiry)).'</b>':null;
            $new_display_on_str = ( $display_on > 0 )?", displaying on <b>{$apv_display_on_text}</b>":'<b>nowhere</b>';

            // update agency service price
            $update_data = array(
                'type' => $type,
                'amount' => $amount,
                'reason' => $reason,
                'scope' => $scope,
                'expiry' => $expiry,
                'updated_date' => $date
            );            
            $this->db->where('id', $apv_id);
            $this->db->update('agency_price_variation', $update_data);   
            
            // check display variation already exist
            $dv_type = 1; // agency
            $dv_sql = $this->db->query("
            SELECT COUNT(`id`) AS dv_count
            FROM `display_variation`
            WHERE `variation_id` = {$apv_id}
            AND `type` = {$dv_type}                   
            ");

            if( $dv_sql->row()->dv_count > 0 ){ // exist, update

                // update display on               
                $update_data = array(
                    'display_on' => $display_on
                );            
                $this->db->where('variation_id', $apv_id);
                $this->db->where('type', $dv_type);
                $this->db->update('display_variation', $update_data);
                
            }else{ // new, insert

                // insert display on  
                $insert_data = array(
                    'variation_id' => $apv_id,                    
                    'type' => $dv_type,
                    'display_on' => $display_on                    
                );            
                $this->db->insert('display_variation', $insert_data);                

            }
            
            $current_variation_str = "<b>{$scope_str}</b> <b>{$discount_str}</b> of <b>\$".number_format($apv_row->amount, 2)."</b> due to <b>{$apv_row->apvr_reason}</b>{$expiry_str}{$display_on_str}";
            $new_variation_str = "<b>{$agency_var_scope_text}</b> <b>{$new_discount_str}</b> of <b>\$".number_format($amount, 2)."</b> due to <b>{$agency_var_reason_text}</b>{$new_expiry_str}{$new_display_on_str}";            
            
            //insert log
            $log_details = "{$current_variation_str} updated to {$new_variation_str}";
            $log_params = array(
                'title' => 46,  // Agency Update
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);

        }else{ // insert

            if( $agency_id > 0 && $amount > 0 ){

                $insert_data = array(
                    'agency_id' => $agency_id,
                    'type' => $type,
                    'amount' => $amount,
                    'reason' => $reason,
                    'scope' => $scope,
                    'expiry' => $expiry,
                    'created_date' => $date
                );            
                $this->db->insert('agency_price_variation', $insert_data);
                $last_id = $this->db->insert_id();
    
                ##insert data to display_variation table
                if( $display_on!="" ){
                    
                    $apv_id = $last_id;

                    // check display variation already exist
                    $dv_type = 1; // agency
                    $dv_sql = $this->db->query("
                    SELECT COUNT(`id`) AS dv_count
                    FROM `display_variation`
                    WHERE `variation_id` = {$apv_id}
                    AND `type` = {$dv_type}                 
                    ");

                    if( $dv_sql->row()->dv_count > 0 ){ // exist, update

                        // update display on               
                        $update_data = array(
                            'display_on' => $display_on
                        );            
                        $this->db->where('variation_id', $apv_id);
                        $this->db->where('type', $dv_type);
                        $this->db->update('display_variation', $update_data);
                        
                    }else{ // new, insert

                        // insert display on  
                        $insert_data = array(
                            'variation_id' => $apv_id,                    
                            'type' => $dv_type,
                            'display_on' => $display_on            
                        );            
                        $this->db->insert('display_variation', $insert_data);                        

                    }

                }
                ##insert data to display_variation table end
    
                //insert log
                $log_details = "<b>{$agency_var_type_text}</b> of \${$amount} applied to agency because <b>{$agency_var_reason_text}</b>";
                $log_params = array(
                    'title' => 46,  // Agency Update
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);
    
            }

        }                

    }


    public function delete_agency_price_variation(){

        $apv_id = $this->input->post('apv_id');
        $agency_id = $this->input->post('agency_id');

        $date_full = date('Y-m-d H:i:s');
        
        if( $apv_id > 0 ){ // update

            $apv_sql = $this->db->query("
            SELECT 
                apv.`id` AS apv_id,
                apv.`amount`,
                apv.`type` AS apv_type,
                apv.`reason` AS apv_reason,
                apv.`scope`,
                apv.`expiry`,

                apvr.`reason` AS apvr_reason,

                dv.`id` AS dv_id,
                dv.`display_on`,

                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `agency_price_variation` AS apv
            LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
            LEFT JOIN `display_variation` AS dv ON ( apv.`id` = dv.`variation_id` AND dv.`type` = 1 )
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`id` = {$apv_id}                                
            ");    
            $apv_row = $apv_sql->row();

            // current
            $scope_str = null;
            if( $apv_row->scope == 0 && is_numeric($apv_row->scope) ){
                $scope_str = 'Agency';
            }else if( $apv_row->scope == 1 ){
                $scope_str = 'Property';
            }else{
                $scope_str = "{$apv_row->short_name} Service";
            }

            $discount_str = ( $apv_row->apv_type == 1 )?'Discount':'Surcharge';
            $expiry_str = ( $this->system_model->isDateNotEmpty($apv_row->expiry) )?' expiring on '.date('d/m/Y', strtotime($apv_row->expiry)):null;

            //insert log
            $log_details = "Deleted {$scope_str} {$discount_str} of \$".number_format($apv_row->amount, 2)." due to {$apv_row->apvr_reason}{$expiry_str}";
            $log_params = array(
                'title' => 46,  // Agency Update
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $this->session->staff_id,
                'agency_id' => $agency_id
            );
            $this->system_model->insert_log($log_params);

            /*
            // delete
            $this->db->where('id', $apv_id);
            $this->db->delete('agency_price_variation');
            */

            // updated to SOFT deleted by ben
            $update_data = array(
                'active' => 0,
                'deleted_ts' => $date_full
            );            
            $this->db->where('id', $apv_id);            
            $this->db->update('agency_price_variation', $update_data);

        }               

    }


}

?>