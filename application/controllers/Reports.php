<?php

class Reports extends CI_Controller {

    public function __construct() {
        parent::__construct();
        //$this->load->database();
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->model('/inc/user_class_model');
        $this->load->model('/inc/activity_functions_model');
        $this->load->model('reports_model');
        $this->load->model('alarms_model');
        $this->load->model('jobs_model');
        $this->load->model('cron_model');
        $this->load->model('staff_accounts_model');
        $this->load->model('agency_model');
        $this->load->model('icons_model');
        $this->load->model('franchisegroups_model');
        $this->load->model('expensesummary_model');
        $this->load->model('crmtasks_model');
        $this->load->model('properties_model');
        $this->load->model('admin_model');
    }

    public function index() {
        $data['title'] = "Reports";

        $tester_arr = $this->system_model->tester();
        $tester_appended = $tester_arr;
        //$tester_appended[] = 2056; // Robert Bell
        //$tester_appended[] = 2175; // Thalia Paki

        $data['testers_arr'] = $tester_appended;

        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/index', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function api_tenancy_info(){
        $clientId;
        $clientSecret;
        $clientScope;
        $urlCallBack;
        $accessTokenUrl;
        $authorizeUrl; 
        $suppArr = array();
        $myArray = [];

        $this->clientId = $this->config->item('PME_CLIENT_ID');
        $this->clientSecret = $this->config->item('PME_CLIENT_SECRET');
        $this->clientScope = $this->config->item('PME_CLIENT_Scope');
        $this->urlCallBack = urlencode($this->config->item('PME_URL_CALLBACK'));
        $this->accessTokenUrl = $this->config->item('PME_ACCESS_TOKEN_URL');
        $this->authorizeUrl = $this->config->item('PME_AUTHORIZE_URL');

        $country_id = $this->config->item('country');

        $data['title'] = "API Tenancy Info";
        //$agency_id = 1570;
        //$agency_id = 4228;

        $pmeList = $this->get_pme_lot_details($agency_id);

        $agencies = $this->agency_model->get_agencies_api_connected();
        //echo $this->db->last_query();
        //exit();

        if(!empty($_POST['agency_filter'])){
            $agency_id = $_POST['agency_filter'];

            $props = $this->properties_model->get_connected_pme_properties($agency_id);
            $pmeList = $this->get_pme_lot_details($agency_id);
            //echo $this->db->last_query();
            //exit();
        
            foreach ($props as $prop){
                $apiID = $prop->api_prop_id;
                
                if(empty($pmeList->ResponseStatus)){
                    foreach ($pmeList as $row) {
                        if($apiID == $row->LotId){
                            array_push($myArray, (object)[
                                'PropID'         => $prop->property_id,
                                'AgencyID'       => $prop->agency_id,
                                'ApiID'          => $prop->api,
                                'LotAddress'     => $row->LotAddress,
                                'TenancyStart'   => $row->TenancyStart,
                                'AgreementStart' => $row->AgreementStart,
                                'AgreementEnd'   => $row->AgreementEnd,
                                'TenancyEnd'     => $row->TenancyEnd,
                                'Termination'    => $row->Termination,
                                'BreakLease'     => $row->BreakLease
                            ]);
                        }
                    }
                }
            }
        }
        else{
            $agency_id = "";
        }

        $data['tenancy'] = $myArray;
        $data['agencies'] = $agencies;
    
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/api_tenancy_info', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function get_pme_lot_details($agency_id){

        //$end_points = "https://app.propertyme.com/api/v1/lots";
        $end_points = "https://app.propertyme.com/api/v1/tenancies";

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

    public function report_admin() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Admin Report";

        $country = array(1 => 'au', 2 => 'nz'); //$this->config->item('country');
        $from = $this->input->get_post('date_from_filter');
        $to = $this->input->get_post('date_to_filter');
        $getSats = $this->input->get_post('get_sats') ? $this->input->get_post('get_sats') : 0;

        $data['from'] = isset($from) ? $from : "01" . date('/m/Y');
        $data['to'] = isset($to) ? $to : date('d/m/Y');
        $data['prev_day'] = array(
            'from' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Day '
        );

        $data['next_day'] = array(
            'from' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => 'Next Day <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );

        $staff_id = ($this->input->get_post('sid') ? (int) $this->input->get_post('sid') : "z");
        $tech_id = ($this->input->get_post('tid') ? (int) $this->input->get_post('tid') : "z");

        # Get Staff details for display if needed
        if ($staff_id === 0) {
            $staff_details['FirstName'] = "SATS System";
        } elseif (is_int($staff_id)) {
            $staff_details = $this->user_class_model->getUserDetails($staff_id);
        }

        # Get Tech details for display if needed
        if ($tech_id === 0) {
            $tech_details['first_name'] = "Unassigned";
        } elseif (is_int($tech_id)) {
            $tech_details = $this->user_class_model->getTechDetails($tech_id);
        }


        # Staff and tech id's to filter
        $data['staff_filter'] = array(
            'staff_id' => $staff_id,
            'tech_id' => $tech_id
        );

        if ($this->isValidDate($from) && $this->isValidDate($to)) {
            $to = $this->convertDate($from);
            $from = $this->convertDate($to);
        } else {
            $to = date('Y-m-d');
            $from = date('Y-m-') . "01";
        }

        $data['getSats'] = $getSats;
        // var_dump($data); die();
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/report_admin', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function sales_activity() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Sales Activity Report";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $all = $this->input->get_post('all');

        //pass data variable
        $data['from'] = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
        $data['to'] = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");

        $salesrep = $this->input->get_post('sales_rep_filter');
        $state = $this->input->get_post('state_filter');



        $data['prev_day'] = array(
            'from' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Day '
        );

        $data['next_day'] = array(
            'from' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => 'Next Day <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );


        $staff_id = ($this->input->get_post('sid') ? (int) $this->input->get_post('sid') : "z");
        $tech_id = ($this->input->get_post('tid') ? (int) $this->input->get_post('tid') : "z");

        # Get Staff details for display if needed
        if ($staff_id === 0) {
            $staff_details['FirstName'] = "SATS System";
        } elseif (is_int($staff_id)) {
            $staff_details = $this->user_class_model->getUserDetails($staff_id);
        }

        # Get Tech details for display if needed
        if ($tech_id === 0) {
            $tech_details['first_name'] = "Unassigned";
        } elseif (is_int($tech_id)) {
            $tech_details = $this->user_class_model->getTechDetails($tech_id);
        }


        # Staff and tech id's to filter
        $data['staff_filter'] = array(
            'staff_id' => $staff_id,
            'tech_id' => $tech_id
        );

        $report_params = array('date_from_filter' => $data['from'], 'date_to_filter' => $data['to'], 'staff_id' => $staff_id, 'tech_id' => $tech_id);



        //GET REPORTS LIST
        $sel_query = "ael.`comment` AS ael_comments, a.`status` AS a_status, ael.date as eventdate, mlt.contact_type, ael.next_contact, sa.LastName, sa.FirstName, a.agency_name, aght.priority, apmd.abbreviation";
        $params = array(
            'sel_query' => $sel_query,
            'state_filter' => $state,
            'salesrep_filter' => $salesrep,
            'date_from_filter' => $data['from'],
            'date_to_filter' => $data['to'],
            'limit' => $per_page,
            'offset' => $offset,
        );

        $aa_query =  $this->reports_model->getSalesRepAgencyLogs($params);

        if( $this->input->get_post('export')==1 ){

            if ($this->input->get_post('get_sats') == 1) {

                // file name
                $filename = 'sales_activity_' . date('Y-m-d') . '.csv';
    
                header("Content-type: application/csv");
                header("Content-Disposition: attachment; filename={$filename}");
                header("Pragma: no-cache");
                header("Expires: 0");
    
                $country_id = $this->config->item('country');
    
                $all = $this->input->get_post('all');
                $date_from_filter = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
                $date_to_filter = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");
                $salesrep_filter = $this->input->get_post('sales_rep_filter');
                $state_filter = $this->input->get_post('state_filter');
    
                //GET REPORTS LIST
               /* $sel_query = "ael.`comment` AS ael_comments, a.`status` AS a_status, ael.date as eventdate, ael.contact_type, ael.next_contact, sa.LastName, sa.FirstName, a.agency_name";
                $params = array(
                    'sel_query' => $sel_query,
                    'state_filter' => $state_filter,
                    'salesrep_filter' => $salesrep_filter,
                    'date_from_filter' => $date_from_filter,
                    'date_to_filter' => $date_to_filter
                );*/
                
                $lists = $aa_query;
    
                // file creation
                $file = fopen('php://output', 'w');
    
                //header
                $header = array("Date", "Sales Rep", "Type", "Agency", "Status", "Comment", "Next Contact");
                fputcsv($file, $header);
    
                foreach ($lists->result_array() as $row) {
    
                    $csvdata['Date'] = date('d/m/Y', strtotime($row['eventdate']));
                    $csvdata['Sales_Rep'] = "{$row['FirstName']} {$row['LastName']}";
                    $csvdata['Type'] = $row['contact_type'];
                    $csvdata['Agency'] = $row['agency_name'];
                    $csvdata['Status'] = $row['a_status'];
                    $csvdata['Comment'] = $row['ael_comments'];
                    $csvdata['Next_Contact'] = ( $this->system_model->isDateNotEmpty($row['next_contact']) ) ? date('d/m/Y', strtotime($row['next_contact'])) : '';
    
                    fputcsv($file, $csvdata);
                }
    
                fclose($file);
                exit;
            } else {
                redirect(base_url('/reports/sales_activity'));
            }

        }else{

            if ($this->input->get_post('btnGetStats') || $this->input->get_post('get_sats') == 1) {
                $data['lists'] = $aa_query;
            }
    
    
            //GET total rows
           // $sel_query = "COUNT(ael.`agency_event_log_id`) AS totalCount";
            $sel_query = "COUNT(ael.`id`) AS totalCount";
            $params = array(
                'sel_query' => $sel_query,
                'state_filter' => $state,
                'salesrep_filter' => $salesrep,
                'date_from_filter' => $data['from'],
                'date_to_filter' => $data['to']
            );
            if ($this->input->get_post('btnGetStats') || $this->input->get_post('get_sats') == 1) {
                $query = $this->reports_model->getSalesRepAgencyLogs($params);
                $total_rows = $query->row()->totalCount;
            }
    
    
            $pagi_links_params_arr = array(
                'btnGetStats' => $this->input->get_post('btnGetStats'),
                'sales_rep_filter' => $salesrep,
                'state_filter' => $state,
                'date_from_filter' => $data['from'],
                'date_to_filter' => $data['to'],
                'get_sats' => 1
            );
            $pagi_link_params = '/reports/sales_activity/?' . http_build_query($pagi_links_params_arr);
    
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
            $this->load->view('reports/sales_activity', $data);
            $this->load->view('templates/inner_footer', $data);

        }
        
    }


     //LEAVE REPORTS

     public function leave_report() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Employee Leave Report";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $all = $this->input->get_post('all');

        //pass data variable
        $data['from'] = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
        $data['to'] = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");

        $salesrep = $this->input->get_post('sales_rep_filter');
        $staff_filter = $this->input->get_post('staff_filter');
        $state = $this->input->get_post('state_filter');



        $data['prev_day'] = array(
            'from' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Day '
        );

        $data['next_day'] = array(
            'from' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => 'Next Day <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );
        //GET STAFF fo dropdown
        $staff_params = array(
            'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName",
            'sort_list' => array(
                array(
                    'order_by'=> 'sa.FirstName',
                    'sort' => 'ASC'
                )
            )
        );
        $data['staff_list'] = $this->gherxlib->getStaffInfo($staff_params);

        $staff_id = ($this->input->get_post('sid') ? (int) $this->input->get_post('sid') : "z");
        $tech_id = ($this->input->get_post('tid') ? (int) $this->input->get_post('tid') : "z");

        # Get Staff details for display if needed
        if ($staff_id === 0) {
            $staff_details['FirstName'] = "SATS System";
        } elseif (is_int($staff_id)) {
            $staff_details = $this->user_class_model->getUserDetails($staff_id);
        }

        # Get Tech details for display if needed
        if ($tech_id === 0) {
            $tech_details['first_name'] = "Unassigned";
        } elseif (is_int($tech_id)) {
            $tech_details = $this->user_class_model->getTechDetails($tech_id);
        }


        # Staff and tech id's to filter
        $data['staff_filter'] = array(
            'staff_id' => $staff_id,
            'tech_id' => $tech_id
        );

        $report_params = array('date_from_filter' => $data['from'], 'date_to_filter' => $data['to'], 'staff_id' => $staff_id, 'tech_id' => $tech_id);



        //GET REPORTS LIST
        $sel_query = "*";
        $params = array(
            'sel_query' => $sel_query,
            'state_filter' => $state,
            'salesrep_filter' => $salesrep,
            'staff_filter' => $staff_filter,
            'date_from_filter' => $data['from'],
            'date_to_filter' => $data['to'],
            'limit' => $per_page,
            'offset' => $offset,
        );

        $aa_query =  $this->reports_model->getLeaveReport($params);

        if( $this->input->get_post('export')==1 ){

            if ($this->input->get_post('get_sats') == 1) {

                // file name
                $filename = 'leave_report_' . date('Y-m-d') . '.csv';
    
                header("Content-type: application/csv");
                header("Content-Disposition: attachment; filename={$filename}");
                header("Pragma: no-cache");
                header("Expires: 0");
    
                $country_id = $this->config->item('country');
    
                $all = $this->input->get_post('all');
                $date_from_filter = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
                $date_to_filter = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");
                $salesrep_filter = $this->input->get_post('sales_rep_filter');
                $state_filter = $this->input->get_post('state_filter');
    
          
                
                $lists = $aa_query;
    
                // file creation
                $file = fopen('php://output', 'w');
    
                //header
                $header = array("Date", "Name", "Type of Leave","No of Leave");
                fputcsv($file, $header);
    
                foreach ($lists->result_array() as $row) {

                    //get number of days leave without weekend

                    $start = new DateTime($row['date_start']);
                    $end = new DateTime($row['date_finish']);
                    // otherwise the  end date is excluded (bug?)
                    $end->modify('+1 day');

                    $interval = $end->diff($start);

                    // total days
                    $days = $interval->days;

                    // create an iterateable period of date (P1D equates to 1 day)
                    $period = new DatePeriod($start, new DateInterval('P1D'), $end);

                    // best stored as array, so you can add more than one
                    $holidays = array('2012-09-07');

                    foreach($period as $dt) {
                        $curr = $dt->format('D');

                        // substract if Saturday or Sunday
                        if ($curr == 'Sat' || $curr == 'Sun') {
                            $days--;
                        }

                        // (optional) for the updated question
                        // elseif (in_array($dt->format('Y-m-d'), $holidays)) {
                        //     $days--;
                        // }
                    }


                    //end of getting number of days leave without weekend
    
                    $csvdata['Date'] = date('d/m/Y', strtotime($row['date_start']));
                    $csvdata['Name'] = "{$row['FirstName']} {$row['LastName']}";
                    // if($row['type_of_leave'] == '1'){
                    //     $csvdata['Type of Leave'] = 'Annual';
                    // }elseif($row['type_of_leave'] == '2'){
                    //     $csvdata['Type of Leave'] = 'Personal(sick)';
                    // }elseif($row['type_of_leave'] == '3'){
                    //     $csvdata['Type of Leave'] = 'Personal(carers)';
                    // }elseif($row['type_of_leave'] == '4'){
                    //     $csvdata['Type of Leave'] = 'Compassionate';
                    // }elseif($row['type_of_leave'] == '5'){
                    //     $csvdata['Type of Leave'] = 'Cancel Previous Leave';
                    // }elseif($row['type_of_leave'] == '-1'){
                    //     $csvdata['Type of Leave'] = 'Others';
                    // }
                    $csvdata['Type of Leave'] = $row['region'];
                    $csvdata['No. of Days'] = $days;
                    // $csvdata['Agency'] = $row['agency_name'];
                    // $csvdata['Status'] = $row['a_status'];
                    // $csvdata['Comment'] = $row['ael_comments'];
                    // $csvdata['Next_Contact'] = ( $this->system_model->isDateNotEmpty($row['next_contact']) ) ? date('d/m/Y', strtotime($row['next_contact'])) : '';
    
                    fputcsv($file, $csvdata);
                }
    
                fclose($file);
                exit;
            } else {
                redirect(base_url('/reports/leave_report'));
            }

        }else{

            if ($this->input->get_post('btnGetStats') || $this->input->get_post('get_sats') == 1) {
                $data['lists'] = $aa_query;
            }
    
    
            //GET total rows
           // $sel_query = "COUNT(ael.`agency_event_log_id`) AS totalCount";
            // $sel_query = "COUNT(e.`leave_id`) AS totalCount";
            // $params = array(
            //     'sel_query' => $sel_query,
            //     'state_filter' => $state,
            //     'salesrep_filter' => $salesrep,
            //     'date_from_filter' => $data['from'],
            //     'date_to_filter' => $data['to']
            // );
            // if ($this->input->get_post('btnGetStats') || $this->input->get_post('get_sats') == 1) {
            //     $query = $this->reports_model->getSalesRepAgencyLogs($params);
            //     $total_rows = $query->row()->totalCount;
            // }
    
    
            $pagi_links_params_arr = array(
                'btnGetStats' => $this->input->get_post('btnGetStats'),
                'sales_rep_filter' => $salesrep,
                'state_filter' => $state,
                'date_from_filter' => $data['from'],
                'date_to_filter' => $data['to'],
                'get_sats' => 1
            );
            $pagi_link_params = '/reports/leave_report/?' . http_build_query($pagi_links_params_arr);
    
            // pagination settings
            // $config['page_query_string'] = TRUE;
            // $config['query_string_segment'] = 'offset';
            // $config['total_rows'] = $total_rows;
            // $config['per_page'] = $per_page;
            // $config['base_url'] = $pagi_link_params;
    
            // $this->pagination->initialize($config);
    
            // $data['pagination'] = $this->pagination->create_links();
    
            // // pagination count
            // $pc_params = array(
            //     'total_rows' => $total_rows,
            //     'offset' => $offset,
            //     'per_page' => $per_page
            // );
            // $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
    
    
    
            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('reports/leave_report', $data);
            $this->load->view('templates/inner_footer', $data);

        }
        
    }

    //end of leave reports



    public function convertDateAus($date) {
        if (stristr($date, "-")) {
            $tmp = explode("-", $date);
            $date = $tmp[2] . "/" . $tmp[1] . "/" . $tmp[0];
        }
        return $date;
    }

    public function convertDate($date) {
        if (stristr($date, "/")) {
            $tmp = explode("/", $date);
            $date = $tmp[2] . "-" . $tmp[1] . "-" . $tmp[0];
        }
        return $date;
    }

    public function isValidDate($date) {
        if (stristr($date, "/")) {
            $tmp = explode("/", $date);
            if (checkdate($tmp[1], $tmp[0], $tmp[2])) {
                return true;
            }
        }

        if (stristr($date, "-")) {
            $tmp = explode("-", $date);
            if (checkdate($tmp[1], $tmp[2], $tmp[0])) {
                return true;
            }
        }
        return false;
    }

    public function export_sales_activity() { //Gherx: TO be disabled > moved to sales_activity() function

        if ($this->input->get_post('get_sats') == 1) {


            // file name
            $filename = 'sales_activity_' . date('Y-m-d') . '.csv';

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            $country_id = $this->config->item('country');

            $all = $this->input->get_post('all');
            $date_from_filter = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
            $date_to_filter = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");
            $salesrep_filter = $this->input->get_post('sales_rep_filter');
            $state_filter = $this->input->get_post('state_filter');

            //GET REPORTS LIST
            $sel_query = "ael.`comment` AS ael_comments, a.`status` AS a_status, ael.date as eventdate, ael.contact_type, ael.next_contact, sa.LastName, sa.FirstName, a.agency_name";
            $params = array(
                'sel_query' => $sel_query,
                'state_filter' => $state_filter,
                'salesrep_filter' => $salesrep_filter,
                'date_from_filter' => $date_from_filter,
                'date_to_filter' => $date_to_filter
            );
            $lists = $this->reports_model->getSalesRepAgencyLogs($params);

            // file creation
            $file = fopen('php://output', 'w');

            //header
            $header = array("Date", "Sales Rep", "Type", "Agency", "Status", "Comment", "Next Contact");
            fputcsv($file, $header);

            foreach ($lists->result_array() as $row) {

                $data['Date'] = date('d/m/Y', strtotime($row['eventdate']));
                $data['Sales_Rep'] = "{$row['FirstName']} {$row['LastName']}";
                $data['Type'] = $row['contact_type'];
                $data['Agency'] = $row['agency_name'];
                $data['Status'] = $row['a_status'];
                $data['Comment'] = $row['ael_comments'];
                $data['Next_Contact'] = ( $this->system_model->isDateNotEmpty($row['next_contact']) ) ? date('d/m/Y', strtotime($row['next_contact'])) : '';

                fputcsv($file, $data);
            }

            fclose($file);
            exit;
        } else {
            redirect(base_url('/reports/sales_activity'));
        }
    }

    public function sales_report() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Sales Report";
        $country_id = $this->config->item('country');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $all = $this->input->get_post('all');

        //pass data variable
        $from = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
        $to = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");

        $from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
        $to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));

        $data['from'] = $from;
        $data['to'] = $to;

        // sales commission version on page parameter overrides global settings
		$data['sales_commission_ver'] = ( $this->input->get_post('ver') != '' )?$this->input->get_post('ver'):$this->config->item('sales_commission_ver');


        $data['prev_day'] = array(
            'from' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Day '
        );

        $data['next_day'] = array(
            'from' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => 'Next Day <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );

        $data['staff_id'] = ($this->input->get_post('sid') ? (int) $this->input->get_post('sid') : "z");
        $data['tech_id'] = ($this->input->get_post('tid') ? (int) $this->input->get_post('tid') : "z");

        # Get Staff details for display if needed
        if ($data['staff_id'] === 0) {
            $dat['staff_details']['FirstName'] = "SATS System";
        } elseif (is_int($data['staff_id'])) {
            $data['staff_details'] = $this->user_class_model->getUserDetails($data['staff_id']);
        }

        # Get Tech details for display if needed
        if ($data['tech_id'] === 0) {
            $data['tech_details']['first_name'] = "Unassigned";
        } elseif (is_int($data['tech_id'])) {
            $data['tech_details'] = $this->user_class_model->getTechDetails($data['tech_id']);
        }


        # Staff and tech id's to filter
        $data['staff_filter'] = array(
            'staff_id' => $data['staff_id'],
            'tech_id' => $data['tech_id']
        );

        // get distinct salesrep from agency logs
        // allowed agency log titles to display
        $allowed_log_titles_arr =
        array(
            "'Cold Call'",
            "'Cold Call In'",
            "'Conference'",
            "'E-mail'",
            "'Follow up'",
            "'Happy Call'",
            "'Mailout'",
            "'Meeting'",
            "'Other'",
            "'Pack Sent'",
            "'Phone Call'",
            "'Pop In'"
        );
        $allowed_log_titles_imp = implode(',',$allowed_log_titles_arr);

        //$date_filter_str = null;
        $data['date_filter_str'] = null;
        if(
            ( $this->input->get_post('date_from_filter') != '' && $this->input->get_post('date_to_filter') != '' ) &&
            ( $this->input->get_post('date_from_filter') != 'all' && $this->input->get_post('date_to_filter') != 'all' )
         ){
            //$date_filter_str = "AND ael.`eventdate` BETWEEN '{$from2}' AND '{$to2}'";
            $data['date_filter_str'] = "sr.`date` BETWEEN '{$from2}' AND '{$to2}'";
        }

       /* 
        $agency_log_str = "
        SELECT DISTINCT sa.`StaffID`, sa.`FirstName` , sa.`LastName`
        FROM `agency_event_log` AS ael
        LEFT JOIN `agency` AS a ON ael.`agency_id` = a.`agency_id`
        LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
        WHERE a.`country_id` = {$country_id}
        AND `contact_type` IN($allowed_log_titles_imp)
        AND sa.`StaffID` IS NOT NULL
        AND sa.`active` = 1
        {$date_filter_str}
        ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
        ";
        $data['agency_log_dist_sr'] = $this->db->query($agency_log_str);
        */

        ## NEW BY AL>>>>>
        ## Get sales report
        $sales_class = 5;
        $params_get_sales_report = array(
            'sel_query' => "DISTINCT(sr.contact_type) as contact_type_id, mlt.contact_type as contact_type_name",
            'group_by' => 'sr.contact_type'
        );
        $data['get_sales_report_sql'] = $this->reports_model->get_sales_report($params_get_sales_report)->result_array();

        $params_get_sales_report_staff = array(
            'sel_query' => "DISTINCT(sr.staff_id) as staff_id, sa.FirstName, sa.LastName",
            'class_id' => $sales_class,
            'join_table' => array('salesrep'),
            'custom_where' => $data['date_filter_str']
        );
        $data['sales_report_staff'] = $this->reports_model->get_sales_report($params_get_sales_report_staff)->result_array();
        ## NEW BY AL END>>>>>

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/sales_report', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function process_mark_all_property_as_paid_commission(){

        $from = $this->input->get_post('from');
        $from_ymd = $this->system_model->formatDate($from);  
        $to = $this->input->get_post('to');
        $to_ymd = $this->system_model->formatDate($to);  
		$sales_commission_ver = $this->input->get_post('sales_commission_ver');

        $to_month = date("F",strtotime($to_ymd));        

        // sales commission version switch
        $sales_commission_str =  null;
		if( $sales_commission_ver == 'new' ){
			$sales_commission_str = 'AND ps.`is_payable` = 1';
		}else{
			$sales_commission_str = 'AND ps.`service` = 1';
		}

        $ps_sql_str = "
        SELECT 
            DISTINCT(ps.`property_id`), 
            
            a.`agency_id`,
            a.`agency_name`,

            agen_sr.`FirstName`,
            agen_sr.`LastName`
        FROM `property_services` AS ps
        LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `staff_accounts` AS agen_sr ON a.`salesrep` = agen_sr.`StaffID`
        WHERE Date(ps.`status_changed`) BETWEEN '{$from_ymd}' AND '{$to_ymd}'
        AND (
            p.`is_nlm` IS NULL 
            OR p.`is_nlm` = 0
        )        
        {$sales_commission_str}
        ";
        
        $ps_sql = $this->db->query($ps_sql_str); 

        foreach( $ps_sql->result() as $ps_row ){

            $sales_rep = "{$ps_row->FirstName} {$ps_row->LastName}";

            $log_title = 86; // Sales Report
            $log_details = "Paid commission on this property for {$to_month} to {$sales_rep} for {$ps_row->agency_name}";                        
            
            $params = array(
                'title' => $log_title, 
                'details' => $log_details,
                'display_in_vpd' => 1,
                'agency_id' => $ps_row->agency_id,
                'created_by_staff' => $this->session->staff_id,
                'property_id' => $ps_row->property_id
            );
            $this->system_model->insert_log($params);    
                  
        }
        
    }

    public function sales_snapshot() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Sales Snapshot";

        //get snapshot sales rep
        $data['snapshot_sales_rep_list'] = $this->reports_model->getSnapshotSalesRep();


        //get all agency
        $data['agency_list'] = $this->db->select('agency_id,agency_name as a_name, status')->where('country_id', $this->config->item('country'))->order_by('agency_name', 'ASC')->get('agency');

        //get sales_snapshot_status
        $data['sales_snapshot_status_list'] = $this->db->select('sales_snapshot_status_id,name')->order_by('name', 'ASC')->get('sales_snapshot_status');

        // get staff class ID = 5/Sales
        if($this->config->item('country')==1){ //AU
            $brad = 2165;
            $gavin = 2189;
            $shaquille = 2296; //Shaquille Smith
            $custom_where = "(sa.`ClassID` = 5 OR sa.`StaffID` = {$brad} OR sa.`StaffID` = {$gavin} OR sa.`StaffID` = {$shaquille})";
        }else{ //NZ
            $gavin = 2202;
            $shaquille = 2259; //Shaquille Smith
            $custom_where = "(sa.`ClassID` = 5 OR sa.`StaffID` = {$gavin} OR sa.`StaffID` = {$shaquille})";
        }

        $params = array(
            'sel_query' => "sa.StaffID AS sales_snapshot_sales_rep_id, sa.FirstName AS first_name, sa.LastName AS last_name",
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0,
            'custom_where' => $custom_where
        );
        $data['all_sales_rep_staff'] = $this->staff_accounts_model->get_staff_accounts($params);
        $data['last_query'] = $this->reports_model->sales_snapsho_about_qry();

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/sales_snapshot', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_get_sales_snapshot() {

        $data['sales_snapshot_sales_rep_id'] = $this->input->post('sales_snapshot_sales_rep_id');

        if (!$data['sales_snapshot_sales_rep_id']) {
            redirect(base_url('/reports/sales_snapshot'));
        } else {

            //get all agency
            $data['agency_list'] = $this->db->select('agency_id,agency_name as a_name, status')->where('country_id', $this->config->item('country'))->order_by('agency_name', 'ASC')->get('agency');

            //get sales_snapshot_status
            $data['sales_snapshot_status_list'] = $this->db->select('sales_snapshot_status_id,name')->order_by('name', 'ASC')->get('sales_snapshot_status');


            // get staff class ID = 5/Sales
            if($this->config->item('country')==1){ //AU
                $brad = 2165;
                $gavin = 2189;
                $shaquille = 2296;
                $custom_where = "(sa.`ClassID` = 5 OR sa.`StaffID` = {$brad} OR sa.`StaffID` = {$gavin} OR sa.`StaffID` = {$shaquille})";
            }else{ //NZ
                $gavin = 2202;
                $shaquille = 2259;
                $custom_where = "(sa.`ClassID` = 5 OR sa.`StaffID` = {$gavin} OR sa.`StaffID` = {$shaquille})";
            }

            $params = array(
                'sel_query' => "sa.StaffID AS sales_snapshot_sales_rep_id, sa.FirstName AS first_name, sa.LastName AS last_name",
                'active' => 1,
                'deleted' => 0,
                'display_query' => 0,
                'custom_where' => $custom_where
            );
            $data['snapshot_reps'] = $this->staff_accounts_model->get_staff_accounts($params);


            $this->load->view('reports/ajax_get_sales_snapshot', $data);
        }
    }

    public function export_sales_snapshot() {


        // file name
        $filename = 'sales_snapshot_' . date('Y-m-d') . '.csv';

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $country_id = $this->config->item('country');


        //GET REPORTS LIST
        $sel_query = " DISTINCT(ss.`sales_snapshot_sales_rep_id`), ss_s.name as status_name, ss.sales_snapshot_status_id as ss_status_id, ss.details, ss.date, ss.properties, a.agency_id, a.agency_name, sr.sub_region_id as postcode_region_id, sr.subregion_name as postcode_region_name, sa.FirstName AS first_name, sa.LastName AS last_name ";
        $params = array(
            'sel_query' => $sel_query
        );
        $lists = $this->reports_model->getSnapshot($params);


        // file creation
        $file = fopen('php://output', 'w');

        //header
        $region = $this->customlib->getDynamicRegionViaCountry($this->config->item('country'));
        $header = array("Date", "Sales Rep", "Agency", "Properties", "{$region}", "Status", "Details");
        fputcsv($file, $header);

        foreach ($lists->result() as $row) {

            $date = ( $this->system_model->isDateNotEmpty($row->date) ) ? date('d/m/Y', strtotime($row->date)) : "";

            $data['Date'] = $date;
            $data['Sales_Rep'] = "{$row->first_name} {$row->last_name}";
            $data['Agency'] = $row->agency_name;
            $data['Properties'] = $row->properties;
            $data['region'] = $row->postcode_region_name;
            $data['status'] = $row->status_name;
            $data['details'] = $row->details;


            fputcsv($file, $data);
        }

        fclose($file);
        exit;
    }

    /**
     * Update Sales Snapshot via ajax
     */
    public function ajax_update_sales_snapshot() {

        $data2['status'] = false;

        $sales_snapshot_id = $this->input->post('sales_snapshot_id');
        $sales_rep = $this->input->post('sales_rep');
        $agency_id = $this->input->post('agency_id');
        $properties = $this->input->post('properties');
        $status = $this->input->post('status');
        $details = $this->input->post('details');

        $sales_snapshot_sales_rep_id = $this->input->post('sales_snapshot_sales_rep_id'); // set to used to retrieve exact sales rep box/dropdown (not used for now)

        if ($sales_snapshot_id && $sales_snapshot_id != "" && is_numeric($sales_snapshot_id)) {

            //query update snapshot
            $data = array(
                'sales_snapshot_sales_rep_id' => $sales_rep,
                'agency_id' => $agency_id,
                'properties' => $properties,
                'sales_snapshot_status_id' => $status,
                'details' => $details,
                'date' => date('Y-m-d H:i:s')
            );
            $update = $this->reports_model->updateSnapShot($sales_snapshot_id, $data);
            if ($update) {
                $data2['status'] = true;

                //get and set json data for event populate
                #get snapshot by snapshot id
                $paramss = array(
                    'sel_query' => 'ss.sales_snapshot_sales_rep_id, ss_s.name as status_name, ss.sales_snapshot_id, ss.sales_snapshot_status_id as ss_status_id, ss.details, ss.date, ss.properties, a.agency_id, a.agency_name, sr.sub_region_id as postcode_region_id, sr.subregion_name as postcode_region_name, sa.FirstName AS first_name, sa.LastName AS last_name ',
                    'snapshot_id' => $sales_snapshot_id
                );
                $row = $this->reports_model->getSnapshot($paramss)->row();
                $data2['date'] = ( $this->system_model->isDateNotEmpty($row->date) ) ? date('d/m/Y', strtotime($row->date)) : null;
                $data2['agency'] = $this->gherxlib->crmLink('vad', $row->agency_id, $row->agency_name);
                $data2['properties'] = $row->properties;
                $data2['region'] = ($row->postcode_region_id != "") ? $row->postcode_region_name : NULL;
                $data2['snap_status'] = $row->status_name;
                $data2['details'] = $row->details;
            }
        }

        echo json_encode($data2);
    }

    /**
     * Delete Snapsho via ajax
     */
    public function ajax_delete_snapshot() {

        $json_data['status'] = false;

        $snapid = $this->input->post('sales_snapshot_id');

        if ($snapid && !empty($snapid) && is_numeric($snapid)) {

            //delete query
            $this->db->where('sales_snapshot_id', $snapid);
            $this->db->delete('sales_snapshot');

            if ($this->db->affected_rows() > 0) {
                $json_data['status'] = true;
                $json_data['msg'] = "Snapshot successfully deleted";
            }
        }

        echo json_encode($json_data);
    }

    public function kpi() {


        $data['start_load_time'] = microtime(true);
        $data['title'] = "KPIs";


        //total property
        $data['total_prop'] = $this->reports_model->kpi_getTotalPropertyCount()->row();


        //agency count
        $data['agency_count'] = $this->reports_model->getTotalAgencyCount();


        //get outstanding jobs
        $sel_query = "COUNT(DISTINCT(j.`id`)) as j_count";
        $oj_custom_where = "
        (
            j.`status` != 'On Hold'
            AND j.`status` != 'Pending'
            AND j.`status` != 'Completed'
            AND j.`status` != 'Cancelled'
        )
        AND CAST(j.`created` AS Date) <= '".date('Y-m-d')."'
        ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $this->config->item('country'),
            'custom_where' => $oj_custom_where
        );
        $data['outstanding_jobs'] = $this->jobs_model->get_jobs($params);


        //get outstanding value
        $sel_query = "SUM(j.`job_price`) as j_sum_price";
        $params_outstanding_value = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $this->config->item('country'),
            'custom_where' => $oj_custom_where
        );
        $data['outstanding_value'] = $this->jobs_model->get_jobs($params_outstanding_value);


        //get Average Age  NOT COMPLETED
        $sel_query = "
				SUM( DATEDIFF( '" . date('Y-m-d') . "', CAST( j.`created` AS DATE ) ) ) AS sum_age,
				COUNT(j.`id`) AS j_count
			";
        $custom_where = "( j.`status` != 'On Hold' AND j.`status` != 'Pending' AND j.`status` != 'Completed' AND j.`status` != 'Cancelled' )";
        $params_aveAge = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $this->config->item('country'),
            'custom_where' => $custom_where
        );
        $data['average_age'] = $this->jobs_model->get_jobs($params_aveAge);


        //get avarage age COMPLETED
        $from = date('Y-m-01');
        $to = date('Y-m-t');
        $sel_query = "
				SUM( DATEDIFF( j.`date`, CAST( j.`created` AS DATE ) ) ) AS sum_completed_age,
				COUNT(j.`id`) AS j_count
			";
        $custom_where = "( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )";
        $custom_where2 = "j.`date` BETWEEN '{$from}' AND '{$to}'";

        $params_aveAgeCompleted = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $this->config->item('country'),
            'custom_where_arr' => array($custom_where, $custom_where2)
        );
        $data['average_age_completed'] = $this->jobs_model->get_jobs($params_aveAgeCompleted);


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/kpi', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * GET SALES RESULT VIA AJAX (KPI)
     */
    public function ajax_get_sales_result() {
        //load views
        $this->load->view('reports/ajax_get_sales_result', $data);
    }

    /**
     * GET BOOKINGS VIA AJAX (KPI)
     */
    public function ajax_get_bookings() {
        //load views
        $this->load->view('reports/ajax_get_bookings', $data);
    }

    public function add_opportunity() {

        $this->load->library('form_validation');

        $agency_id = $this->input->post('snap_add_agency');
        $properties = $this->input->post('snap_add_properties');
        $status = $this->input->post('snap_add_status');
        $sales_rep = $this->input->post('snap_add_sales_rep');
        $details = $this->input->post('snap_add_details');
        $insert_agency_log = $this->input->post('snap_insert_agency_log');

        //validate
        $this->form_validation->set_rules('snap_add_agency', 'Agency', 'required');
        $this->form_validation->set_rules('snap_add_sales_rep', 'Sales Rep', 'required');

        if ($this->form_validation->run() != FALSE) {

            //insert snapshot opportunity
            $data = array(
                'agency_id' => $agency_id,
                'properties' => $properties,
                'sales_snapshot_status_id' => $status,
                'details' => $details,
                'date' => date('Y-m-d H:i:s'),
                'sales_snapshot_sales_rep_id' => $sales_rep,
                'country_id' => $this->config->item('country')
            );
            $this->db->insert('sales_snapshot', $data);
            $this->db->limit(1);

            //insert log tick
            if ($insert_agency_log == 1) {

                //insert logs
                $log_details = "Added New Sales Snapshot";
                $log_params = array(
                    'title' => 37, //sales snapshot
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);
            }

            $success_message = "Opportunity Successfully Added";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url('/reports/sales_snapshot'), 'refresh');
        } else {
            $error_msg = "Error: Required field must not be empty";
            $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
            redirect(base_url('/reports/sales_snapshot'), 'refresh');
        }
    }

    /**
     *
     * NOTE: DISABLED FOR SNAPSHOT ADD SALES REP AS MOVED FROM SNAPSHOT_SALES_REP TO STAFF_ACCOUNT TABLE
     */
    public function add_sales_snapshot_sales_rep() {

        //-----------
        redirect(base_url('/reports/sales_snapshot')); // REDIRECT / DISABLED ATM
        //------------


        $this->load->library('form_validation');

        $fname = $this->input->post('sales_rep_fname');
        $lname = $this->input->post('sales_rep_lname');

        //validate
        $this->form_validation->set_rules('sales_rep_fname', 'First Name', 'required');
        $this->form_validation->set_rules('sales_rep_lname', 'Last Name', 'required');

        if ($this->form_validation->run() != FALSE) {

            $data = array(
                'first_name' => $fname,
                'last_name' => $lname,
                'country_id' => $this->config->item('country')
            );
            $this->db->insert('sales_snapshot_sales_rep', $data);
            $this->db->limit(1);

            $success_message = "Sales Rep Successfully Added";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url('/reports/sales_snapshot'), 'refresh');
        } else {
            $error_msg = "Error: Required field must not be empty";
            $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
            redirect(base_url('/reports/sales_snapshot'), 'refresh');
        }
    }

    /**
     * delete via ajax
     */
    public function ajax_delete_snapshot_sales_rep() {
        $json_data['status'] = false;

        $ss_sr_id = $this->input->post('ss_sr_id');
        $current_salesrep = $this->input->post('current_salesrep');

        if ($ss_sr_id && $ss_sr_id != "" && is_numeric($ss_sr_id)) {

            if (in_array($ss_sr_id, $current_salesrep)) { //if in array return false
                $json_data['status'] = false;
                $json_data['msg'] = "Cannot Delete Salesrep with Active Opportunities";
            } else { // not in array proceed to submission
                $this->db->where('sales_snapshot_sales_rep_id', $ss_sr_id);
                $this->db->delete('sales_snapshot_sales_rep');

                if ($this->db->affected_rows() > 0) {
                    $json_data['status'] = true;
                    $json_data['msg'] = "Sales Rep Successfully Deleted";
                }
            }
        } else {
            $json_data['status'] = false;
            $json_data['msg'] = "Server Error: Please Contact Sats Admin";
        }

        echo json_encode($json_data);
    }

    /**
     * NOTE DISABLE ATM
     *
     */
    public function update_sales_snapshot_sales_rep() {

        //-----------
        redirect(base_url('/reports/sales_snapshot')); // REDIRECT / DISABLED ATM
        //------------



        $ss_sr_id = $this->input->post('edit_snap_sales_rep_id');
        $edit_fname = $this->input->post('edit_snap_sales_rep_fname');
        $edit_lname = $this->input->post('edit_snap_sales_rep_lname');


        if (!empty($ss_sr_id)) {
            $err = false;

            foreach ($ss_sr_id as $index => $val) {

                if (is_numeric($val) && $val != "") {
                    if ($edit_fname[$index] != "" && $edit_lname[$index] != "") {

                        //all good > proceed udpate
                        $update_data = array(
                            'first_name' => $edit_fname[$index],
                            'last_name' => $edit_lname[$index]
                        );
                        $this->db->where('sales_snapshot_sales_rep_id', $val);
                        $this->db->update('sales_snapshot_sales_rep', $update_data);

                        if ($this->db->affected_rows() > 0) {
                            $err = false;
                        }
                    } else {
                        $err = true;
                    }
                }
            }

            //set error and success flashdata
            if ($err) {
                $error_msg = "First Name / Last Name must not be empty";
                $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                redirect(base_url('/reports/sales_snapshot'), 'refresh');
            } elseif ($err == false) {
                $success_message = "Sales Rep Successfully Updated";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url('/reports/sales_snapshot'), 'refresh');
            }
        } else {
            $error_msg = "Error: ID must not be empty";
            $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
            redirect(base_url('/reports/sales_snapshot'), 'refresh');
        }
    }

    /**
     * NEW PROPERTIES REPORT PAGE
     */
    public function new_properties_report() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "New Properties Report";


        $all = $_REQUEST['all'];
        $data['from'] = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
        $data['to'] = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");
        $data['state'] = $this->input->get_post('state_filter');
        $agency_filter_id = $this->input->get_post('agency_filter');

        //Prev and Next Day Cal
        $data['prev_day'] = array(
            'from' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Day '
        );

        $data['next_day'] = array(
            'from' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'to' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/", "-", $data['from'])))),
            'title' => 'Next Day <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );


        $staff_id = ($this->input->get_post('sid') ? (int) $this->input->get_post('sid') : "z");
        $tech_id = ($this->input->get_post('tid') ? (int) $this->input->get_post('tid') : "z");

        # Get Staff details for display if needed
        if ($staff_id === 0) {
            $staff_details['FirstName'] = "SATS System";
        } elseif (is_int($staff_id)) {
            $staff_details = $this->user_class_model->getUserDetails($staff_id);
        }

        # Get Tech details for display if needed
        if ($tech_id === 0) {
            $tech_details['first_name'] = "Unassigned";
        } elseif (is_int($tech_id)) {
            $tech_details = $this->user_class_model->getTechDetails($tech_id);
        }

        ##agency filter
        $agency_filter_params =  array(
            'sel_query' => " DISTINCT(a.`agency_id`),a.`agency_name` ",
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASc'
                )
            )
        );
        $data['agency_filter'] = $this->activity_functions_model->getActivity($agency_filter_params);


        //GET ACTIVITY IF POST BUTTON IS SET
        if ($this->input->get_post('btnGetStats') || $this->input->get_post('get_sats') == 1) {

            //get Activity
            $activity_params = array(
                'sel_query' => " DISTINCT(a.`agency_id`), a.`agency_name`, a.`state`, a.joined_sats ",
                'date_from_filter' => $data['from'],
                'date_to_filter' => $data['to'],
                'state_filter' => $data['state'],
                'agency_filter' => $agency_filter_id
            );
            $data['get_activity'] = $this->activity_functions_model->getActivity($activity_params);

            ##export
            if($this->input->get_post('export')==1){
                $filename = 'new_properties_report'.date('Y-m-d').'.csv';

                header("Content-type: application/csv");
                header("Content-Disposition: attachment; filename={$filename}");
                header("Pragma: no-cache");
                header("Expires: 0");

                $ajt_sql2 = $this->reports_model->getDynamicServices();
                foreach($ajt_sql2->result_array() as $ajt_sql2_row){
                    if(COUNTRY == 2){ # NZ
                        if($ajt_sql2_row['id']==2){
                            $alarm_text[] = $ajt_sql2_row['type'];
                        }
                    }else{ # AU display all
                        $alarm_text[] = $ajt_sql2_row['type'];
                    }
                }

                $file = fopen('php://output', 'w');

                $head_text = array("Agency","State", "Joined Date");
                $head_text_2_merge = array_merge($head_text, $alarm_text);
                $head_text_last_part = array("Total New","Deleted","Net","By SATS","By Agency");
                $header_final_text = array_merge($head_text_2_merge, $head_text_last_part);
                $header = $header_final_text;
                fputcsv($file, $header);

                ## col/rows start
                foreach ($data['get_activity']->result_array() as $sr){

                    $x_data['Agency'] = $sr['agency_name'];
                    $x_data['State'] = $sr['state'];
                    $x_data['join_date'] = ($this->system_model->isDateNotEmpty($sr['joined_sats'])) ? $this->system_model->formatDate($sr['joined_sats'],'d/m/Y') : NULL;
                    
                    ## alarm job type 
                    $i = 0;
                    $gross_tot = 0;
                    foreach($ajt_sql2->result_array() as $ajt2){

                        $sa_params = array(
                            'sel_query' => " COUNT(ps.`property_services_id`) as serv_count ",
                            'agency_id' => $sr['agency_id'],
                            'alarm_job_type_id' => $ajt2['id'],
                            'country_id' => COUNTY,
                            'date_from_filter' =>  $data['from'],
                            'date_to_filter' => $data['to'],
                            'display_query' => 0
                        );
                        $sa = $this->reports_model->get_property_services($sa_params)->row()->serv_count;

                        if( COUNTRY==2){  //IF NZ DISPLAY ONLY SMOKE ALARM SERVICES
                            if($ajt2['id']==2){
                               if($sa>0){
                                    $x_data['alarm_job_'.$i] = $sa;
                               }else{
                                    $x_data['alarm_job_'.$i] = NULL;
                               }
                            }
                        }else{ //AU display all
                            if($sa>0){
                                $x_data['alarm_job_'.$i] = $sa;
                            }else{
                                $x_data['alarm_job_'.$i] = NULL;
                            }
                        }

                        $i++;
                        $gross_tot += $sa;
                        $serv_tot[$i] += $sa;
                        
                    }
                    ## alarm job type end

                    $x_data['total_new'] = ($gross_tot > 0) ? $gross_tot : NULL;
                    
                    ##deleted
                    $del_params = array(
                        'sel_query' => " COUNT(ps.`property_services_id`) as del_serv_count ",
                        'agency_id' => $sr['agency_id'],
                        'date_from_filter' =>  $data['from'],
                        'date_to_filter' => $data['to']
                    );
                    $deleted = $this->reports_model->get_deleted_services($del_params)->row()->del_serv_count;
                    $x_data['deleted'] = ($deleted>0) ? $deleted : NULL;
                    ##deleted end

                    ##net
                    $net = ($gross_tot-$deleted); 
                    $x_data['Net'] = $net;
                    ##net end
                    
                    ##by sats
                    $add_by_sats_params = array(
                        'sel_query' => " COUNT(ps.`property_services_id`) as added_sats_count ",
                        'agency_id' => $sr['agency_id'],
                        'date_from_filter' => $data['from'],
                        'date_to_filter' => $data['to']
                    );
                    $add_by_sats = $this->reports_model->getAddedBySats($add_by_sats_params)->row()->added_sats_count;
                    $x_data['by_sats'] = ($add_by_sats>0) ? $add_by_sats : NULL;
                    ##by sats end
                   
                    ##by agency
                    $add_by_agency_params = array(
                        'sel_query' => " COUNT(ps.`property_services_id`) as added_agency_count ",
                        'agency_id' => $sr['agency_id'],
                        'date_from_filter' => $data['from'],
                        'date_to_filter' => $data['to']
                    );
                    $add_by_agency = $this->reports_model->getAddedByAgency($add_by_agency_params)->row()->added_agency_count;
                    $x_data['by_agency'] = ($add_by_agency>0) ? $add_by_agency : NULL;
                    ##by agency end

                    fputcsv($file,$x_data); 


                    //SET VARIABLES FOR TOTALS
                    $add_by_sats_tot += $add_by_sats;
                    $add_by_agency_tot += $add_by_agency;
                    $gross_tot_tot += $gross_tot;
                    $deleted_tot += $deleted;
                    $sats_del_tot += $sats_del;
                    $net_total_tot += $net;		
                    //SET VARIABLES FOR TOTALS END
                }
                ## col/rows end

                ##Total row
                $x_data2['total'] = "Total";
                $x_data2['total_state'] = "";
                
                $awts = 0;
                foreach($serv_tot as $val){
                    if(COUNTRY==2){
                        if($awts == 0){
                            if($val>0){
                                $x_data2['total_ajt_'.$awts] = $val;
                            }else{
                                $x_data2['total_ajt_'.$awts] = NULL;
                            }
                            
                        }
                    }else{
                        if($val>0){
                            $x_data2['total_ajt_'.$awts] = $val;
                        }else{
                            $x_data2['total_ajt_'.$awts] = NULL;
                        }
                    }

                    $awts++;
                }
                $x_data2['ajt_total_new'] = ($gross_tot_tot>0)?$gross_tot_tot:'';
                $x_data2['ajt_deleted'] = ($deleted_tot>0)?$deleted_tot:'';
                $x_data2['ajt_net'] = ($net_total_tot>0)?$net_total_tot:'';

                $ajt_by_sats_tt = ($add_by_sats_tot>0)?(is_numeric(number_format((($add_by_sats_tot/$gross_tot_tot)*100))))?number_format((($add_by_sats_tot/$gross_tot_tot)*100)).'%':'':'';
                $x_data2['ajt_by_sats'] = ($add_by_sats_tot>0) ? $add_by_sats_tot. " (".$ajt_by_sats_tt.")" :'';

                $ajt_by_agency_tt = ($add_by_sats_tot>0)?(is_numeric(number_format((($add_by_agency_tot/$gross_tot_tot)*100))))?number_format((($add_by_agency_tot/$gross_tot_tot)*100)).'%':'':'';
                $x_data2['ajt_by_agency'] = ($net_total_tot>0)?$net_total_tot ." (".$ajt_by_agency_tt.")":'';
                ##Total row end

                fputcsv($file,$x_data2); 
                

                fclose($file); 
                exit; 
            }
            ##export end

        }

       

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/new_properties_report', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * GET CONTRACTORS LIST
     */
    public function contractors() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Contractors";

        //get contractors list
        $params = array(
            'sel_query' => "c.contractors_id,c.name,c.area,c.address,c.phone,c.email,c.rate,c.comment"
        );
        $data['lists'] = $this->reports_model->get_contractors($params);

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/contractors', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * UPDATE CONTRACTOR VIA AJAX
     */
    public function ajax_update_contractors() {

        $json_data['status'] = false;

        $contractors_id = $this->input->post('contractors_id');
        $name = $this->input->post('name');
        $area = $this->input->post('area');
        $address = $this->input->post('address');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');
        $rate = $this->input->post('rate');
        $comment = $this->input->post('comment');

        if ($contractors_id && $contractors_id != "" && is_numeric($contractors_id)) {

            $udpate_data = array(
                'name' => $name,
                'area' => $area,
                'address' => $address,
                'phone' => $phone,
                'email' => $email,
                'rate' => $rate,
                'comment' => $comment
            );
            $this->db->where('contractors_id', $contractors_id);
            $this->db->update('contractors', $udpate_data);

            if ($this->db->affected_rows() > 0) {
                $json_data['status'] = true;
                $json_data['msg'] = "Contractor details successfully updated";
            } else {
                $json_data['msg'] = "Server Error: Please contact Admin";
            }
        }

        echo json_encode($json_data);
    }

    /**
     * Delete Contractor
     */
    public function ajax_delete_contractors() {
        $json_data['status'] = false;

        $contractors_id = $this->input->post('contractors_id');

        if ($contractors_id && $contractors_id != "" && is_numeric($contractors_id)) {

            $this->db->where('contractors_id', $contractors_id);
            $this->db->delete('contractors');


            if ($this->db->affected_rows() > 0) {
                $json_data['status'] = true;
                $json_data['msg'] = "Contractor successfully deleted";
            } else {
                $json_data['msg'] = "Server Error: Please contact Admin";
            }
        }

        echo json_encode($json_data);
    }

    /**
     * ADD CONTRACTORS
     */
    public function add_contractor() {

        $this->load->library('form_validation');

        $name = $this->input->post('name');
        $area = $this->input->post('area');
        $address = $this->input->post('address');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');
        $rate = $this->input->post('rate');
        $comment = $this->input->post('comment');

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('rate', 'Name', 'required');

        if ($this->form_validation->run() != FALSE) {

            $insert_data = array(
                'name' => $name,
                'area' => $area,
                'address' => $address,
                'phone' => $phone,
                'email' => $email,
                'rate' => $rate,
                'comment' => $comment,
                'country_id' => $this->config->item('country')
            );
            $this->db->insert('contractors', $insert_data);

            if ($this->db->affected_rows() > 0) {

                $success_message = "Contractors Successfully Added";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url('/reports/contractors'), 'refresh');
            } else {

                $error_msg = "Server Error: Please contact admin";
                $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                redirect(base_url('/reports/contractors'), 'refresh');
            }
        } else {
            $error_msg = "Error: Required field must not be empty";
            $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
            redirect(base_url('/reports/contractors'), 'refresh');
        }
    }

    public function installed_alarms() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Installed Alarms";

        $uri = '/reports/installed_alarms';
        $data['uri'] = $uri;


        //Post Request
        $date_from_filter = $this->input->get_post('date_from_filter');
        $date_to_filter = $this->input->get_post('date_to_filter');
        $data['date_from_filter'] = ($date_from_filter != "") ? $this->system_model->formatDate($date_from_filter, 'd/m/Y') : date('1/m/Y');
        $data['date_to_filter'] = ($date_to_filter != "") ? $this->system_model->formatDate($date_to_filter, 'd/m/Y') : date('t/m/Y');

        $alarm_pwr = $this->input->get_post('alarm_type_filter');
        $alarm_reason = $this->input->get_post('reason_filter');
        $state = $this->input->get_post('state_filter');
        $tech_filter = $this->input->get_post('tech_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $job_type_filter = $this->input->get_post('job_type_filter');
        $export = $this->input->get_post('export');


        // pagination settings
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
			j.id as j_id,
			j.date as j_date,
			alrm.alarm_price as alrm_alarm_price,
			alrm.alarm_price a_price,
			alrm_p.alarm_pwr_id,
			alrm_p.alarm_pwr,
			alrm_p.alarm_price_inc,
			alrm_p.alarm_price_ex,
			p.state as p_state,
			alrm_r.alarm_reason,
            a.agency_id,
            sa.FirstName,
            sa.LastName

		";
        $params = array(
            'sel_query' => $sel_query,
            'new' => 1,
            'state' => $state,
            'alarm_pwr' => $alarm_pwr,
            'alarm_reason' => $alarm_reason,
            'tech' => $tech_filter,
            'agency_filter'=> $agency_filter,
            'job_type' => $job_type_filter,
            'filterDate' => array(
                'from' => $this->system_model->formatDate($data['date_from_filter'], 'Y-m-d'),
                'to' => $this->system_model->formatDate($data['date_to_filter'], 'Y-m-d'),
            ),
            'sort_list' => array(
                array(
                    'order_by' => 'j.date',
                    'sort' => 'DESC'
                )
            )
        );

        // export should show all
        if ( $export != 1 ){ 
            $params['limit'] = $per_page;
            $params['offset'] = $offset;
        }

        $alarm_sql = $this->alarms_model->getNewAlarms($params);

        if ( $export == 1 ) { //EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "installed_alarms_{$date_export}.csv";
    
            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");
    
            // file creation 
            $csv_file = fopen('php://output', 'w');            
    
            //$csv_header = array("Date","Alarm Type","Sell Price","Buy Price","State","Reason","Job");
            $csv_header = array("Date","Alarm Type","Sell (INC GST) Price","Sell (EX GST) Price","Buy (INC GST) Price","Buy (EX GST) Price","State","Technicians","Reason","Job");
            fputcsv($csv_file, $csv_header);
                        
            foreach( $alarm_sql->result() as $alarm_row ){ 
                $csv_row = [];                
                $csv_row[] = ( $this->system_model->isDateNotEmpty($alarm_row->j_date) )?date('d/m/Y', strtotime($alarm_row->j_date)):''; 
                $csv_row[] = $alarm_row->alarm_pwr;                                                               
                $csv_row[] = ( $alarm_row->a_price > 0 )?'$'.number_format($alarm_row->a_price,2):null;
                $csv_row[] = ( $alarm_row->a_price > 0 )?'$'.number_format($alarm_row->a_price/1.1,2):null;
                $csv_row[] = ( $alarm_row->alarm_price_inc > 0 )?'$'.number_format($alarm_row->alarm_price_inc,2):null;
                $csv_row[] = ( $alarm_row->alarm_price_inc > 0 )?'$'.number_format($alarm_row->alarm_price_inc/1.1,2):null;
                $csv_row[] = $alarm_row->p_state;
                $csv_row[] = $alarm_row->FirstName.' '.$alarm_row->LastName;
                $csv_row[] = $alarm_row->alarm_reason;
                $csv_row[] = $alarm_row->j_id;         
                fputcsv($csv_file,$csv_row); 
            }            
                    
            fclose($csv_file); 
            exit; 
    
        }else{

            $data['lists'] = $alarm_sql;
            $data['query_string'] = $this->db->last_query();

            // Total rows
            $total_sel_query = "COUNT('j.id') as j_count";
            $total_params = array(
                'sel_query' => $total_sel_query,
                'new' => 1,
                'state' => $state,
                'alarm_pwr' => $alarm_pwr,
                'alarm_reason' => $alarm_reason,
                'tech' => $tech_filter,
                'agency_filter'=> $agency_filter,
                'job_type' => $job_type_filter,
                'filterDate' => array(
                    'from' => $this->system_model->formatDate($data['date_from_filter'], 'Y-m-d'),
                    'to' => $this->system_model->formatDate($data['date_to_filter'], 'Y-m-d'),
                ),
            );
            $query = $this->alarms_model->getNewAlarms($total_params);
            $total_rows = $query->row()->j_count;

            //Tech filter
            $alarm_power_params = array(
                'sel_query' => "DISTINCT('sa.StaffID'), sa.StaffID, sa.FirstName, sa.LastName",
                'new' => 1,
                'active_tech' => 1,
                'sort_list' => array(
                    array(
                        'order_by' => 'sa.FirstName',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['tech_filter'] = $this->alarms_model->getNewAlarms($alarm_power_params);

            //Agency filter
            $agency_filter_params = array(
                'sel_query' => "DISTINCT('a.agency_id'),a.agency_id, a.agency_name",
                'new' => 1,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.agency_name',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['agency_filter'] = $this->alarms_model->getNewAlarms($agency_filter_params);

            //Alarm Power filter
            $alarm_power_sel = "DISTINCT('alrm_p.alarm_pwr_id'),alrm_p.alarm_pwr_id, alrm_p.alarm_pwr";
            $alarm_power_params = array(
                'sel_query' => $alarm_power_sel,
                'new' => 1,
                'sort_list' => array(
                    array(
                        'order_by' => 'alrm_p.alarm_pwr',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['alarm_power'] = $this->alarms_model->getNewAlarms($alarm_power_params);

            //Alarm Reason filter
            $alarm_res_sel = "DISTINCT('alrm_r.alarm_reason_id'),alrm_r.alarm_reason_id, alrm_r.alarm_reason";
            $alarm_res_params = array(
                'sel_query' => $alarm_res_sel,
                'new' => 1,
                'sort_list' => array(
                    array(
                        'order_by' => 'alrm_r.alarm_reason',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['alarm_reason'] = $this->alarms_model->getNewAlarms($alarm_res_params);

            //job type filter
            $job_type_params = array(
                'sel_query' => "DISTINCT('j.job_type'), j.job_type",
                'new' => 1,
                'sort_list' => array(
                    array(
                        'order_by' => 'j.job_type',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['job_type_filter'] = $this->alarms_model->getNewAlarms($job_type_params);



            // get buy and sell price total
            $list_params = array(
                'sel_query' => " SUM(alrm.`alarm_price`) AS alrm_price_tot, SUM(alrm_p.`alarm_price_inc`) as alarm_price_inc, SUM(alrm_p.alarm_price_ex) as alarm_price_ex  ",
                'new' => 1,
                'state' => $state,
                'alarm_pwr' => $alarm_pwr,
                'alarm_reason' => $alarm_reason,
                'tech' => $tech_filter,
                'agency_filter'=> $agency_filter,
                'job_type' => $job_type_filter,
                'filterDate' => array(
                    'from' => $this->system_model->formatDate($data['date_from_filter'], 'Y-m-d'),
                    'to' => $this->system_model->formatDate($data['date_to_filter'], 'Y-m-d'),
                ),
                'get_buy_and_sell_price_tot' => 1
            );
            $all_alarm_sql = $this->alarms_model->getNewAlarms($list_params);
            $all_alarm = $all_alarm_sql->row_array();
            $data['alarm_price_tot'] = $all_alarm['alrm_price_tot'];
            $data['alarm_price_inc'] = $all_alarm['alarm_price_inc'];
            $data['alarm_price_ex'] = $all_alarm['alarm_price_ex'];



            $pagi_links_params_arr = array(
                'date_from_filter' => $this->system_model->formatDate($data['date_from_filter'], 'Y-m-d'),
                'date_to_filter' => $this->system_model->formatDate($data['date_to_filter'], 'Y-m-d'),
                'alarm_type_filter' => $alarm_pwr,
                'reason_filter' => $alarm_reason,
                'state_filter' => $state,
                'tech_filter' => $tech_filter,
                'agency_filter' => $agency_filter,
                'job_type_filter' => $job_type_filter
            );            

            // pagination link
            $pagi_link_params = "{$uri}/?".http_build_query($pagi_links_params_arr);

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


            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('reports/installed_alarms', $data);
            $this->load->view('templates/inner_footer', $data);


        }
        
    }

    public function key_tracking() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Key Tracking";

        $country_id = $this->config->item('country');

        $date_filter = ($this->input->get_post('date_filter') != "") ? $this->system_model->formatDate($this->input->get_post('date_filter'), 'Y-m-d') : date('Y-m-d');
        $agency_filter = $this->input->get_post('agency_filter');
        $tech_filter = $this->input->get_post('tech_filter');

        // pagination settings
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
			kr.tech_run_keys_id as techRun_id,
			kr.date as tech_date,
			kr.action,
			kr.completed_date,
			kr.number_of_keys,
			kr.agency_staff as kr_agency_staff,
			kr.signature_svg,
            kr.`refused_sig`,
			a.agency_id as a_id,
			a.agency_name as a_name,
			sa.FirstName as staff_fName,
			sa.LastName as staff_lName,
            aght.priority,
            kr.assigned_tech as tech_id
		";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'completed' => 1,
            'date' => $date_filter,
            'agency_id' => $agency_filter,
            'tech_id' => $tech_filter,
            'sort_list' => array(
                array(
                    'order_by' => 'kr.`date`',
                    'sort' => 'DESC'
                )
            ),
            'limit' => $per_page,
            'offset' => $offset,
        );
        $data['lists'] = $this->reports_model->getKeyMapRoutes_v2($params);

        //Total Rows
        $total_sel_query = "COUNT('kr.tech_run_keys_id') as kr_count";
        $total_params = array(
            'sel_query' => $total_sel_query,
            'country_id' => $country_id,
            'completed' => 1,
            'date' => $date_filter,
            'agency_id' => $agency_filter,
            'tech_id' => $tech_filter
        );
        $query = $this->reports_model->getKeyMapRoutes_v2($total_params);
        $total_rows = $query->row()->kr_count;

        //Agency Filter
        $sel_query = "DISTINCT('a.agency_id'), a.agency_id as a_id, a.agency_name as a_name";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'completed' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC'
                )
            )
        );
        $data['agency_list'] = $this->reports_model->getKeyMapRoutes_v2($params);

        //Tech Filter
        $sel_query = "DISTINCT('sa.StaffID'), sa.StaffID, sa.FirstName as staff_fName, sa.LastName as staff_lName";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'completed' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                )
            )
        );
        $data['tech_list'] = $this->reports_model->getKeyMapRoutes_v2($params);


        $pagi_links_params_arr = array(
            'date_filter' => $date_filter,
            'agency_filter' => $agency_filter,
            'tech_filter' => $tech_filter
        );

        $pagi_link_params = '/reports/key_tracking/?' . http_build_query($pagi_links_params_arr);

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
        $this->load->view('reports/key_tracking', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function expiring() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Expiring";

        $country_id = $this->config->item('country');


        //get alarm power
        $data['alarm_pwr'] = $this->db->select('alarm_pwr_id,alarm_pwr,alarm_price_ex')->get('alarm_pwr');

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/expiring', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function discarded_alarms() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Discarded Alarms";


        $date_from_filter = ($this->input->get_post('date_from_filter') != "") ? $this->system_model->formatDate($this->input->get_post('date_from_filter')) : date("Y-m-01");
        $data['date_from_filter'] = ($this->input->get_post('date_from_filter') != "") ? $this->system_model->formatDate($this->input->get_post('date_from_filter')) : date("Y-m-01");
        $date_to_filter = ($this->input->get_post('date_to_filter') != "") ? $this->system_model->formatDate($this->input->get_post('date_to_filter')) : date("Y-m-t");
        $data['date_to_filter'] = ($this->input->get_post('date_to_filter') != "") ? $this->system_model->formatDate($this->input->get_post('date_to_filter')) : date("Y-m-t");
        $reason = $this->input->get_post('reason_filter');
        $state = $this->input->get_post('state_filter');


        //date quicklinks
        // shortcut links tweak date
        $currDate = date('Y-m-d');
        $currMonth = ($this->input->get_post('date_from_filter') && $this->input->get_post('date_from_filter') != "") ? $this->input->get_post('date_from_filter') : date('Y-m-01');
        $nextMonth = date('Y-m-d', strtotime('+1 month', strtotime($currMonth)));
        $nextMonthTo = date('Y-m-t', strtotime('+1 month', strtotime($currMonth)));
        $prevMonth = date('Y-m-d', strtotime('-1 month', strtotime($currMonth)));
        $prevMontTo = date('Y-m-t', strtotime('-1 month', strtotime($currMonth)));

        $data['paramsToday'] = "?date_from_filter={$currDate}&date_to_filter=" . urlencode($currDate) . "&reason_filter=" . urlencode($reason) . "&state_filter=" . $state;
        $data['paramsThisMonth'] = "?date_from_filter=" . date('Y-m-01') . "&date_to_filter=" . urlencode(date('Y-m-t')) . "&reason_filter=" . urlencode($reason) . "&state_filter=" . $state;
        $data['paramsNextMonth'] = "?date_from_filter={$prevMonth}&date_to_filter=" . urlencode($prevMontTo) . "&reason_filter=" . urlencode($reason) . "&state_filter=" . $state;


        // pagination settings
        if ($this->input->get_post('export') != 1) { // set only if not export
            $per_page = $this->config->item('pagi_per_page');
            $offset = $this->input->get_post('offset');
        }

        //GET LIST

        $sel_query = "
			a.`alarm_id`,
			a.`job_id`,
			a.`make`,
			a.`model`,
			a.`expiry`,
			a.`ts_discarded_reason`,
			a.`ts_required_compliance`,
			adr.`reason`,
			j.`date`,
			j.`ts_rfc`,
			j.`job_type`,
			j.`id` As job_id,
			p.`rfc`,
			p.`state`,
			ap.`alarm_pwr`,
			at.`alarm_type`,
            sa.FirstName,
            sa.LastName
		";
        $main_params = array(
            'sel_query' => $sel_query,
            'filterDate' => array(
                'from' => $this->system_model->formatDate($date_from_filter, 'Y-m-d'),
                'to' => $this->system_model->formatDate($date_to_filter, 'Y-m-d'),
            ),
            'reason' => $reason,
            'state' => $state,
            'sort_list' => array(
                array(
                    'order_by' => 'a.alarm_id',
                    'sort' => 'DESC'
                )
            ),
            'limit' => $per_page,
            'offset' => $offset,
        );


        //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>CSV EXPORT---------------------------
        if ($this->input->get_post('export') == 1) {

            // file name
            $filename = 'discarded_alarms' . date('Y-m-d') . '.csv';

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            $country_id = $this->config->item('country');


            $lists = $this->alarms_model->getDiscardedAlarms($main_params);

            // file creation
            $file = fopen('php://output', 'w');

            //header
            $header = array("Job Type", "Job ID", "Job Date", "Technicians", "Make", "Model", "Type", "Power", "Expiry", "Reason", "RFC", "State");
            fputcsv($file, $header);

            foreach ($lists->result_array() as $exportrow) {

                $exportdata['job_type'] = $exportrow['job_type'];
                $exportdata['job_id'] = $exportrow['job_id'];
                $exportdata['Date'] = ($this->system_model->isDateNotEmpty($exportrow['date'])) ? $this->system_model->formatDate($exportrow['date'], 'd/m/Y') : NULL;
                $exportdata['technicians'] = $exportrow['FirstName'].' '.$exportrow['LastName'];
                $exportdata['make'] = $exportrow['make'];
                $exportdata['model'] = $exportrow['model'];
                $exportdata['alarm_type'] = $exportrow['alarm_type'];
                $exportdata['alarm_pwr'] = $exportrow['alarm_pwr'];
                $exportdata['expiry'] = $exportrow['expiry'];
                $exportdata['reason'] = $exportrow['reason'];
                $exportdata['RFC'] = ($exportrow['ts_required_compliance'] == 1) ? 'Yes' : 'No';
                $exportdata['state'] = $exportrow['state'];
                fputcsv($file, $exportdata);
            }

            fclose($file);
            exit;
        } else { //------------------------------------NORMAL VIEW---------------
            $data['lists'] = $this->alarms_model->getDiscardedAlarms($main_params);
            $data['query_string'] = $this->db->last_query();

            // all rows
            $sel_query = "COUNT(a.`alarm_id`) AS alrm_count";
            $params = array(
                'sel_query' => $sel_query,
                'filterDate' => array(
                    'from' => $this->system_model->formatDate($date_from_filter, 'Y-m-d'),
                    'to' => $this->system_model->formatDate($date_to_filter, 'Y-m-d'),
                ),
                'reason' => $reason,
                'state' => $state,
            );
            $query = $this->alarms_model->getDiscardedAlarms($params);
            $total_rows = $query->row()->alrm_count;


            //reason filter
            $sel_query = "DISTINCT(adr.`id`), adr.reason";
            $params = array(
                'sel_query' => $sel_query,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.alarm_id',
                        'sort' => 'DESC'
                    )
                )
            );
            $data['reason_filter'] = $this->alarms_model->getDiscardedAlarms($params);

            // state filter
            $sel_query = "p.`state`";
            $params = array(
                'sel_query' => $sel_query,
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`'
            );
            $data['state_filter_json'] = json_encode($params);


            $pagi_links_params_arr = array(
                'date_from_filter' => $date_from_filter,
                'date_to_filter' => $date_to_filter,
                'reason_filter' => $reason,
                'state_filter' => $state
            );
            $pagi_link_params = '/reports/discarded_alarms/?' . http_build_query($pagi_links_params_arr);


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
            $this->load->view('reports/discarded_alarms', $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }

    public function region_numbers() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Region Numbers";

        // default is exclude DHA
        $exclude_dha = ( $this->input->get_post('exclude_dha') != '' )?$this->input->get_post('exclude_dha'):1;
        $data['exclude_dha'] = $this->input->get_post('exclude_dha');

        $country_id = $this->config->item('country');
        $btn_search = $this->input->get_post('btn_search');
        $state_filter = $this->input->get_post('state_filter');

        //region filter
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);


        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');


        if ($btn_search || $this->input->get_post('btn_search')) {

            //GET LIST
            $sel_query = "
            r.`regions_id`, 
            r.`region_name`, 
            r.`region_state`,                        

            sr.`sub_region_id`, 
            sr.`subregion_name`, 
            
            pc.`postcode`";

            $params = array(
                'sel_query' => $sel_query,
                'postcode_region_id' => $sub_region_ms,
                'state' => $state_filter,
                'sort_list' => array(
                    array(
                        'order_by' => 'r.region_state',
                        'sort' => 'ASC'
                    ),
                    array(
                        'order_by' => 'r.region_name',
                        'sort' => 'ASC',
                    )
                ),

                'limit' => $per_page,
                'offset' => $offset,

                'display_query' => 0

            );            
            $postcode_sql = $this->reports_model->getRegion($params);
            $postcode_sql_res = $postcode_sql->result();

      
            // get all fields that is need in the join
            $postcode_arr = [];
            foreach( $postcode_sql_res as $postcode_row ){
                $postcode_arr[] = $postcode_row->postcode;
            }
            
            // make sure its unique
            $postcode_arr = array_unique($postcode_arr);
                              
            $ps_sql = $this->reports_model->get_property_service_data($postcode_arr,$exclude_dha);
            $ps_sql_res = $ps_sql->result();        

            // manually join table            
            $prop_count_total = 0;
            $tot_ps_price_total = 0; 
            foreach ($postcode_sql_res as $postcode_row) {
                foreach ($ps_sql_res as $ps_sql_row) {
                    
                    if ($postcode_row->postcode == $ps_sql_row->postcode) { // match   

                        $postcode_row->property_service_count = $ps_sql_row->ps_count;
                        $postcode_row->property_service_price = $ps_sql_row->ps_price;

                        // computation; copied from the body loop
                        $p_count = $ps_sql_row->ps_count;
                        $tot_ps_price = $ps_sql_row->ps_price;

                        $prop_count_total += $p_count;
                        $tot_ps_price_total += $tot_ps_price;      

                        break;

                    }
                }
            }

            $average_price_fin = ($tot_ps_price_total/$prop_count_total);
               
            /*
            // EX GST
            if( $this->config->item('country') == 1 ){ // AU
                $tot_ps_price_total_devided = $tot_ps_price_total / 1.1;
                $average_price_fin_devided = $average_price_fin / 1.1;
            }else if( $this->config->item('country') == 2 ){ // NZ
                $tot_ps_price_total_devided = $tot_ps_price_total * (20/23);
                $average_price_fin_devided = $average_price_fin * (20/23);
            }
        
            $data['tot_ps_price_total_devided'] = $tot_ps_price_total_devided;
            $data['average_price_fin_devided'] = $average_price_fin_devided;
            */
            $data['prop_count_total'] = $prop_count_total;
            $data['tot_ps_price_total'] = $tot_ps_price_total;
            $data['average_price_fin'] = $average_price_fin;
        
            $data['postcode_sql_res'] = $postcode_sql_res;

            //GET total
            $sel_query = "
            r.`regions_id`, 
            r.`region_name`, 
            r.`region_state`,                        

            sr.`sub_region_id`, 
            sr.`subregion_name`, 
            
            pc.`postcode`";

            $params = array(
                'sel_query' => $sel_query,
                'postcode_region_id' => $sub_region_ms,
                'state' => $state_filter,                
                'display_query' => 0

            );            
            $postcode_sql = $this->reports_model->getRegion($params);
            $postcode_sql_res = $postcode_sql->result();

      
            // get all fields that is need in the join
            $postcode_arr = [];
            foreach( $postcode_sql_res as $postcode_row ){
                $postcode_arr[] = $postcode_row->postcode;
            }
            
            // make sure its unique
            $postcode_arr = array_unique($postcode_arr);
                              
            $ps_sql = $this->reports_model->get_property_service_data($postcode_arr,$exclude_dha);
            $ps_sql_res = $ps_sql->result();        

            // manually join table
            $prop_count_total = 0;
            $tot_ps_price_total = 0;            

            foreach ($postcode_sql_res as $postcode_row) {
                foreach ($ps_sql_res as $ps_sql_row) {
                    
                    if ($postcode_row->postcode == $ps_sql_row->postcode) { // match   

                        // computation; copied from the body loop
                        $p_count = $ps_sql_row->ps_count;
                        $tot_ps_price = $ps_sql_row->ps_price;

                        $prop_count_total += $p_count;
                        $tot_ps_price_total += $tot_ps_price;                        

                        break;

                    }
                }
            }

            $average_price_fin = ($tot_ps_price_total/$prop_count_total);
                     
            /*
            // EX GST
            if( $this->config->item('country') == 1 ){ // AU
                $tot_ps_price_total_devided = $tot_ps_price_total / 1.1;
                $average_price_fin_devided = $average_price_fin / 1.1;
            }else if( $this->config->item('country') == 2 ){ // NZ
                $tot_ps_price_total_devided = $tot_ps_price_total * (20/23);
                $average_price_fin_devided = $average_price_fin * (20/23);
            }
        
            $data['tot_ps_price_total_devided_full_totals'] = $tot_ps_price_total_devided;
            $data['average_price_fin_devided_full_totals'] = $average_price_fin_devided;
            */
            $data['prop_count_total_full_totals'] = $prop_count_total;
            $data['tot_ps_price_total_full_totals'] = $tot_ps_price_total;
            $data['average_price_fin_full_totals'] = $average_price_fin;

            //GET LIST
            $sel_query = "COUNT(pc.`id`) AS pc_count";
            $params = array(
                'sel_query' => $sel_query,
                'postcode_region_id' => $sub_region_ms,
                'state' => $state_filter,
                'sort_list' => array(
                    array(
                        'order_by' => 'r.region_state',
                        'sort' => 'ASC'
                    ),
                    array(
                        'order_by' => 'r.region_name',
                        'sort' => 'ASC',
                    )
                ),
                'display_query' => 0
            );
            $total_sql = $this->reports_model->getRegion($params);
            $total_rows = $total_sql->row()->pc_count;

             //base url params
            $pagi_links_params_arr = array(
                'state_filter' => $state_filter,
                'sub_region_ms' => $sub_region_ms,
                'btn_search' => $btn_search
            );
            $pagi_link_params = '/reports/region_numbers/?' . http_build_query($pagi_links_params_arr);

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


        // region filter
        $sel_query = "r.region_state AS state";
        $region_filter_arr = array(
            'sel_query' => $sel_query,            
            'state' => $state_filter,
            'group_by' => 'r.region_state',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);

        //get state
        $sel_query_state = "DISTINCT(r.`region_state`)";
        $params_state = array(
            'sel_query' => $sel_query_state,
        );
        $data['state_filter'] = $this->reports_model->getRegion($params_state);
        

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/region_numbers', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function edit_main_region() {

        $data['title'] = "Edit Region";

        $this->load->library('form_validation');

        $data['region_id'] = $this->uri->segment(3);
        $submit = $this->input->post('btn_edit_region');
        $region_name = $this->input->post('region_name');
        $state = $this->input->post('state');

        //redirect if id not set
        if (!$data['region_id'] || !is_numeric($data['region_id'])) {
            redirect(base_url('/reports/region_numbers'));
        }


        //get region by id
        $data['region'] = $this->db->get_where('regions', array('regions_id' => $data['region_id']))->row_array();


        //Edit/Update process -  if press update/submit button
        if ($submit) {

            if ($data['region_id'] && $data['region_id'] != "" && is_numeric($data['region_id'])) {

                //validate
                $this->form_validation->set_rules('region_name', 'Region Name', 'required');
                $this->form_validation->set_rules('state', 'State', 'required');

                if ($this->form_validation->run() != FALSE) {

                    $update_data = array(
                        'region_name' => $region_name,
                        'region_state' => $state,
                        'country_id' => $this->config->item('country'),
                        'status' => 1
                    );
                    $this->db->where('regions_id', $data['region_id']);
                    $this->db->update('regions', $update_data);
                    $this->db->limit(1);

                    if ($this->db->affected_rows() > 0) {

                        $success_message = "Region Successfully Updated";
                        $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                        redirect(base_url('/reports/region_numbers'), 'refresh');
                    } else {
                        $error_msg = "No changes made";
                        $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                        redirect(base_url('/reports/region_numbers'), 'refresh');
                    }
                } else {
                    $error_msg = "Error: Required field must not be empty";
                    $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                    redirect(base_url('/reports/region_numbers'), 'refresh');
                }
            }
        }


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/edit_main_region', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function edit_region() {

        ##redirect to /reports/region_numbers 
        ##used chops edit sub region page > admin/edit_subregion
        redirect(base_url('/reports/region_numbers'), 'refresh');
        ##end


        $this->load->library('form_validation');

        $data['title'] = "Edit Booking Region";

        $country_id = $this->config->item('country');
        $submit = $this->input->post('btn_save_region');
        $data['region_id'] = $this->uri->segment(3);
        $region_name = $this->input->post('region_name');
        $sub_region = $this->input->post('sub_region');
        $postcode = $this->input->post('postcode');

        //GET LIST fo Dropdown
        $sel_query = "DISTINCT(r.`regions_id`), r.regions_id, r.region_name, sr.region_id as region";
        $params = array(
            'sel_query' => $sel_query,
            'display_query' => 0
        );
        $data['lists_region'] = $this->reports_model->getRegion($params);


        if ($data['region_id'] && !empty($data['region_id'])) { //fetch only region details if id is set/present
            //GET region by id
            $sel_query = "r.regions_id, sr.region_id as region, sr.subregion_name as postcode_region_name, pc.postcode as postcode_region_postcodes";
            $params = array(
                'sel_query' => $sel_query,
                'postcode_region_id' => $data['region_id'],
                'display_query' => 0
            );
           // $data['lists'] = $this->reports_model->getRegion($params)->row_array();
            $data['lists'] = $this->reports_model->getRegion($params);
        }



        if ($submit) {

            //validate
            $this->form_validation->set_rules('region_name', 'Region Name', 'required');
            $this->form_validation->set_rules('sub_region', 'Sub Region', 'required');
            $this->form_validation->set_rules('postcode', 'Postcode', 'required');

            if ($data['region_id'] && !empty($data['region_id']) && is_numeric($data['region_id'])) { //UPDATE
                if ($this->form_validation->run() != FALSE) {

                    //UPDATE POSTCODE REGION
                    $update_params = array(
                        'region' => $region_name,
                        'postcode_region_name' => $sub_region,
                        'postcode_region_postcodes' => $postcode
                    );
                    $this->db->where('postcode_region_id', $data['region_id']);
                    $this->db->update('postcode_regions', $update_params);
                    $this->db->limit(1);

                    if ($this->db->affected_rows() > 0) {

                        $success_message = "Region Successfully Updated";
                        $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                        redirect(base_url('/reports/region_numbers'), 'refresh');
                    } else {
                        $error_msg = "No changes made";
                        $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                        redirect(base_url('/reports/region_numbers'), 'refresh');
                    }
                } else {
                    $error_msg = "Error: Required field must not be empty";
                    $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                    redirect(base_url("/reports/edit_region/{$this->uri->segment(3)}"), 'refresh');
                }
            } else { //ADD NEW
                if ($this->form_validation->run() != FALSE) {
                    $insert_data = array(
                        'region' => $region_name,
                        'postcode_region_name' => $sub_region,
                        'postcode_region_postcodes' => $postcode,
                        'country_id' => $this->config->item('country')
                    );
                    $this->db->insert('postcode_regions', $insert_data);
                    $this->db->limit(1);

                    //set success session and redirect
                    $success_message = "Region Successfully Saved";
                    $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                    redirect(base_url('/reports/edit_region/'), 'refresh');
                } else {
                    //set error session and redirect
                    $error_msg = "Error: Required field must not be empty";
                    $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                    redirect(base_url('/reports/edit_region/'), 'refresh');
                }
            }
        }

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/edit_region', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function cron_report() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Cron Report";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get/post
        $cron_type = $this->input->get_post('cron_type_filter');
        $data['from'] = ($this->system_model->isDateNotEmpty($this->input->get_post('date_from_filter'))) ? $this->input->get_post('date_from_filter') : date('d/m/Y');
        $data['to'] = ($this->system_model->isDateNotEmpty($this->input->get_post('date_to_filter'))) ? $this->input->get_post('date_to_filter') : date('d/m/Y');

        $db_ready_date_from = date('Y-m-d', strtotime(str_replace('/', '-', $data['from'])));
        $db_ready_date_to = date('Y-m-d', strtotime(str_replace('/', '-', $data['to'])));

        $sel_query = "
            cl.`log_id`,
            cl.`started`,
            cl.`triggered_by`,

            ct.`cron_type_id`,
            ct.`type_name`,
            ct.`description`,

            sa.`StaffID`,
            sa.`FirstName`,
            sa.`LastName`
        ";

        //get list
        $params = array(
            'sel_query' => $sel_query,
            'cron_type' => $cron_type,
            'from' => $db_ready_date_from,
            'to' => $db_ready_date_to,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'cl.started',
                    'sort' => 'DESC'
                )
            ),
            'display_query' => 0
        );
        $data['lists'] = $this->cron_model->getCronLogs($params);

        //list count
        $params_rows = array(
            'sel_query' => "COUNT(cl.log_id) as cl_count",
            'cron_type' => $cron_type,
            'from' => $db_ready_date_from,
            'to' => $db_ready_date_to,
            'display_query' => 0
        );
        $query = $this->cron_model->getCronLogs($params_rows);
        $total_rows = $query->row()->cl_count;


        //CRON TYPE Filter (dropdown)
        $data['cron_type'] = $this->cron_model->getCronTypes();


        //base url params
        $pagi_links_params_arr = array(
            'cron_type_filter' => $cron_type,
            'date_from_filter' => $data['from'],
            'date_to_filter' => $data['to']
        );
        $pagi_link_params = '/reports/cron_report/?' . http_build_query($pagi_links_params_arr);

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
        $this->load->view('reports/cron_report', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function debtors() {

        $data['start_load_time'] = microtime(true);
        $title = "Debtors Report";
        $data['title'] = $title;
        $uri = '/reports/debtors';
        $search_submit = $this->input->get_post('search_submit');

        $is_search = $this->input->get_post('is_search');

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');
        $search_agency = $this->input->get_post('search_agency');
        $pdf_post = $this->input->get_post('pdf');

        // sort
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'a.`agency_name`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'ASC';

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');


        // main query
        $sel_query = "
			SUM(j.`invoice_balance`) AS invoice_balance_tot,
			a.`agency_name`,
			a.`agency_id`,
            aght.priority
		";

        // static financial year
        $financial_year = $this->config->item('accounts_financial_year');
        // get unpaid jobs and exclude 0 job price
        $custom_where = "`j`.`invoice_balance` >0
                    AND `j`.`status` = 'Completed'
                    AND a.`status` != 'target'
                    AND (
                            j.`date` >= '$financial_year' OR
                            j.`unpaid` = 1
                    )
		";

        // main list
        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'agency_filter' => $agency_filter,
            'search_agency' => $search_agency,
            'country_id' => $country_id,
            'join_table' => array('agency_priority'),
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'group_by' => 'a.`agency_id`',
            'display_query' => 0
        );

        // PDF
        if ($pdf_post == 1) {

            $this->load->library('DebtorsPdf');
            $output_type = $this->input->get_post('output_type');

            // pdf initiation
            $pdf = new DebtorsPdf();

            // settings
            $pdf->SetTopMargin(40);
            $pdf->SetAutoPageBreak(true, 40);
            $pdf->AliasNbPages();
            $pdf->AddPage();

            $pdf->report_name = $title;

            // get agency
            if ($agency_filter > 0) {

                $sel_query = "
					a.`agency_id`,
					a.`agency_name`
				";

                $agency_params = array(
                    'sel_query' => $sel_query,
                    'agency_id' => $agency_filter,
                    'country_id' => $country_id,
                    'display_query' => 0
                );
                $agency_sql = $this->agency_model->get_agency($agency_params);
                $agency_row = $agency_sql->row();

                $pdf->agency_name = $agency_row->agency_name;
            } else {
                $pdf->agency_name = 'All Agency';
            }



            // set default values
            $header_width = 0;
            $header_height = 6;
            $header_border = 0;

            $font_size = 10;
            $pdf->SetFont('Arial', 'BI', 14);
            $pdf->SetTextColor(180, 32, 37);
            $pdf->Cell($header_width, 4, 'DEBTORS REPORT', $header_border, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);


            // Current as of
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell($header_width, $header_height, 'Current as of ' . date('d/m/Y'), $header_border, 1, 'L');
            $y = $pdf->GetY();

            $pdf->Ln();

            // row
            $font_size = 8;
            $cell_height = 5;
            $col_width1 = 80;
            $col_width2 = 22;

            // body
            $pdf->SetFillColor(211, 211, 211);
            $pdf->SetFont('Arial', 'B', $font_size);
            $pdf->Cell($col_width1, $cell_height, 'Agency Name', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '0-30 days', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '31-60 days', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '61-90 days', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '91+ days', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, 'Total Due', 1, null, null, true);
            $pdf->Ln();

            // total
            $not_overdue_tot = 0;
            $overdue_31_to_60_tot = 0;
            $overdue_61_to_90_tot = 0;
            $overdue_91_more_tot = 0;
            $invoice_balance_tot = 0;

            // percentage
            $not_overdue_perc = 0;
            $overdue_31_to_60_perc = 0;
            $overdue_61_to_90_perc = 0;
            $overdue_91_more_perc = 0;

            $pdf->SetFont('Arial', '', $font_size);

            $jobs_sql = $this->jobs_model->get_jobs($main_params);

            foreach ($jobs_sql->result() as $row) {

                $having = "DateDiff <= 30";
                $job_params = array(
                    'agency_id' => $row->agency_id,
                    'having' => $having,
                    'display_query' => 0
                );
                $not_overdue = $this->jobs_model->getTotalUnpaidAmount($job_params);

                $having = "DateDiff BETWEEN 31 AND 60";
                $job_params = array(
                    'agency_id' => $row->agency_id,
                    'having' => $having,
                    'display_query' => 0
                );
                $overdue_31_to_60 = $this->jobs_model->getTotalUnpaidAmount($job_params);

                $having = "DateDiff BETWEEN 61 AND 90";
                $job_params = array(
                    'agency_id' => $row->agency_id,
                    'having' => $having,
                    'display_query' => 0
                );
                $overdue_61_to_90 = $this->jobs_model->getTotalUnpaidAmount($job_params);

                $having = "DateDiff >= 91";
                $job_params = array(
                    'agency_id' => $row->agency_id,
                    'having' => $having,
                    'display_query' => 0
                );
                $overdue_91_more = $this->jobs_model->getTotalUnpaidAmount($job_params);

                $invoice_balance = $row->invoice_balance_tot;

                $pdf->Cell($col_width1, $cell_height, $row->agency_name, 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($not_overdue, 2), 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_31_to_60, 2), 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_61_to_90, 2), 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_91_more, 2), 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($invoice_balance, 2), 1);
                $pdf->Ln();

                // total
                $not_overdue_tot += $not_overdue;
                $overdue_31_to_60_tot += $overdue_31_to_60;
                $overdue_61_to_90_tot += $overdue_61_to_90;
                $overdue_91_more_tot += $overdue_91_more;
                $invoice_balance_tot += $invoice_balance;
            }


            // get percentage
            $not_overdue_perc = ($not_overdue_tot / $invoice_balance_tot) * 100;
            $overdue_31_to_60_perc = ($overdue_31_to_60_tot / $invoice_balance_tot) * 100;
            $overdue_61_to_90_perc = ($overdue_61_to_90_tot / $invoice_balance_tot) * 100;
            $overdue_91_more_perc = ($overdue_91_more_tot / $invoice_balance_tot) * 100;

            // total
            $pdf->SetFillColor(238, 238, 238);
            $pdf->SetFont('Arial', 'B', $font_size);
            $pdf->Cell($col_width1, $cell_height, 'Total', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($not_overdue_tot, 2), 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_31_to_60_tot, 2), 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_61_to_90_tot, 2), 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_91_more_tot, 2), 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($invoice_balance_tot, 2), 1, null, null, true);
            $pdf->Ln();

            // percentage
            $pdf->SetFont('Arial', 'B', $font_size);
            $pdf->Cell($col_width1, $cell_height, 'Ageing Percentage', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, number_format($not_overdue_perc) . '%', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, number_format($overdue_31_to_60_perc) . '%', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, number_format($overdue_61_to_90_perc) . '%', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, number_format($overdue_91_more_perc) . '%', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '100%', 1, null, null, true);
            $pdf->Ln();

            $file_name = 'debtors_' . date('YmdHis') . '.pdf';
            $pdf->Output($file_name, $output_type);
        } else {

            if ($search_submit == 'Search' || $is_search == 1) {

                // paginate
                $main_params['limit'] = $per_page;
                $main_params['offset'] = $offset;

                $data['list'] = $this->jobs_model->get_jobs($main_params);
            }

            // total row
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'agency_filter' => $agency_filter,
                'search_agency' => $search_agency,
                'country_id' => $country_id,
                'group_by' => 'a.`agency_id`',
                'display_query' => 0
            );

            if ($search_submit == 'Search' || $is_search == 1) {
                $query = $this->jobs_model->get_jobs($params);
                $total_rows = $query->num_rows();
            }


            // agency filter
            // main query
            $sel_query = "
				j.`id`,
				j.`invoice_balance`,
				a.`agency_name`,
				a.`agency_id`
			";

            // main list
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'country_id' => $country_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC'
                    )
                ),
                'group_by' => 'a.`agency_id`',
                'display_query' => 0
            );

            $data['agency_filter'] = $this->jobs_model->get_jobs($params);



            if ($search_submit == 'Search' || $is_search == 1) {

                //base url params
                $pagi_links_params_arr = array(
                    'agency_filter' => $agency_filter,
                    'search_agency' => $search_agency,
                    'is_search' => $is_search
                );
                $pagi_link_params = "{$uri}/?" . http_build_query($pagi_links_params_arr);

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

            $data['uri'] = $uri;

            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }

    public function payment_credits() {

        $data['start_load_time'] = microtime(true);
        $title = "Payments & Credits";
        $data['title'] = $title;
        $uri = '/reports/payment_credits';

        $search_submit = $this->input->get_post('search_submit');

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');
        $search = $this->input->get_post('search');
        $credit_reason_filter = $this->input->get_post('credit_reason_filter');

        $from = ( $this->input->get_post('from') != '' ) ? $this->system_model->formatDate($this->input->get_post('from')) : date('Y-m-01');
        $to = ( $this->input->get_post('to') != '' ) ? $this->system_model->formatDate($this->input->get_post('to')) : date('Y-m-t');

        // sort
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'j.`date`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'ASC';

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $custom_where_arr = [];


        // main query
        $sel_query = '
			j.`id` AS jid,
			j.`date` AS jdate,
			j.`invoice_amount`,
			j.`invoice_payments`,
			j.`invoice_credits`,
			j.`invoice_balance`,

			p.`property_id`,
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,

			a.`agency_id`,
			a.`agency_name`,

			inv_cred.`credit_paid`,
			inv_cred.`credit_reason`,
			inv_cred.`credit_date`,

			cred_reas.`credit_reason_id`,
			cred_reas.`reason` AS cr_reason
		';

        // get unpaid jobs and exclude 0 job price
        $custom_where_arr[] = "
		j.`job_price` > 0
		AND j.`status` = 'Completed'
		AND (
			a.`status` = 'Active' OR
			a.`status` = 'Deactivated'
		)
		AND (
			j.`invoice_payments` > 0 OR
			j.`invoice_credits` > 0
		)
		";

        $custom_where_arr[] = "
		( inv_cred.`credit_date` BETWEEN '{$from}' AND '{$to}' )
		";

        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => $custom_where_arr,
            'agency_filter' => $agency_filter,
            'search' => $search,
            'credit_reason_id' => $credit_reason_filter,
            'country_id' => $country_id,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'join_table' => array('invoice_credits', 'credit_reason'),
            'display_query' => 0
        );



        if ($search_submit == 'Search') {

            // paginate
            $main_params['limit'] = $per_page;
            $main_params['offset'] = $offset;

            $data['list'] = $this->jobs_model->get_jobs($main_params);
        }

        // total row
        $sel_query = "COUNT(j.`id`) AS jcount";
        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => $custom_where_arr,
            'agency_filter' => $agency_filter,
            'search' => $search,
            'credit_reason_id' => $credit_reason_filter,
            'country_id' => $country_id,
            'join_table' => array('invoice_credits', 'credit_reason'),
            'display_query' => 0
        );

        if ($search_submit == 'Search') {
            $query = $this->jobs_model->get_jobs($main_params);
            $total_rows = $query->row()->jcount;
        }



        // total count
        $sel_query = "
			SUM(j.`invoice_amount`) AS tot_inv_amt,
			SUM(j.`invoice_payments`) AS tot_inv_pay,
			SUM(j.`invoice_credits`) AS tot_inv_cred,
			SUM(j.`invoice_balance`) AS tot_inv_bal
		";
        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => $custom_where_arr,
            'agency_filter' => $agency_filter,
            'search' => $search,
            'country_id' => $country_id,
            'join_table' => array('invoice_credits', 'credit_reason'),
            'display_query' => 0
        );

        if ($search_submit == 'Search') {

            $query = $this->jobs_model->get_jobs($main_params);
            $total_count_row = $query->row();

            $data['tot_inv_amt'] = $total_count_row->tot_inv_amt;
            $data['tot_inv_pay'] = $total_count_row->tot_inv_pay;
            $data['tot_inv_cred'] = $total_count_row->tot_inv_cred;
            $data['tot_inv_bal'] = $total_count_row->tot_inv_bal;
        }

        // agency filter
        $sel_query = "
			a.`agency_id`,
			a.`agency_name`,
		";

        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => $custom_where_arr,
            'country_id' => $country_id,
            'join_table' => array('invoice_credits', 'credit_reason'),
            'group_by' => 'a.`agency_id`',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );

        $data['agency_filter'] = $this->jobs_model->get_jobs($main_params);


        // credit reason filter
        $sel_query = "
			cred_reas.`credit_reason_id`,
			cred_reas.`reason` AS cr_reason
		";

        $custom_where_arr[] = "cred_reas.`credit_reason_id` != ''";

        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => $custom_where_arr,
            'country_id' => $country_id,
            'join_table' => array('invoice_credits', 'credit_reason'),
            'group_by' => 'inv_cred.`credit_reason`',
            'sort_list' => array(
                array(
                    'order_by' => 'cred_reas.`reason`',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );

        $data['cred_reason_filter'] = $this->jobs_model->get_jobs($main_params);


        if ($search_submit == 'Search') {

            //base url params
            $pagi_links_params_arr = array(
                'from' => $this->input->get_post('from'),
                'to' => $this->input->get_post('to'),
                'agency_filter' => $agency_filter,
                'search' => $search,
                'credit_reason_filter' => $this->input->get_post('credit_reason_filter'),
                'search_submit' => $search_submit
            );
            $pagi_link_params = "{$uri}/?" . http_build_query($pagi_links_params_arr);

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


        $data['uri'] = $uri;





        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function payments() {

        $data['start_load_time'] = microtime(true);
        $title = "Payment Summary Report";
        $data['title'] = $title;
        $uri = '/reports/payments';

        $export = $this->input->get_post('export');

        $search_submit = $this->input->get_post('search_submit');

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');
        $search = $this->input->get_post('search');
        $payment_type_filter = $this->input->get_post('payment_type_filter');

        $from = ( $this->input->get_post('from') != '' ) ? $this->system_model->formatDate($this->input->get_post('from')) : date('Y-m-01');
        $to = ( $this->input->get_post('to') != '' ) ? $this->system_model->formatDate($this->input->get_post('to')) : date('Y-m-t');

        // sort
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'j.`date`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'ASC';

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $custom_where_arr = [];


        // main query
        $sel_query = '
			j.`id` AS jid,
			j.`date` AS jdate,
			j.`invoice_amount`,
			j.`invoice_payments`,
			j.`invoice_credits`,
			j.`invoice_balance`,

			p.`property_id`,
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,

			a.`agency_id`,
			a.`agency_name`,
            aght.priority,
            apmd.abbreviation,

			inv_pay.`invoice_payment_id`,
			inv_pay.`payment_date`,
			inv_pay.`amount_paid`,
			inv_pay.`type_of_payment`,

			pay_type.`payment_type_id`,
			pay_type.`pt_name`
		';

        // get unpaid jobs and exclude 0 job price
        $custom_where_arr[] = "
		j.`invoice_amount` > 0
		AND j.`status` = 'Completed'
		AND j.`invoice_payments` > 0
		";

        $custom_where_arr[] = "
		( inv_pay.`payment_date` BETWEEN '{$from}' AND '{$to}' )
		";

        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => $custom_where_arr,
            'multi_agency_filter' => $agency_filter,
            'search' => $search,
            'type_of_payment' => $payment_type_filter,
            'country_id' => $country_id,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'join_table' => array('invoice_payments', 'payment_types', 'agency_priority', 'agency_priority_marker_definition'),
            'display_query' => 0
        );



        if ($export == 1) { //EXPORT
            unset($main_params['limit'],$main_params['offset']); //unset limit and offset element to be able to export all queries rather than per page
            $sql = $this->jobs_model->get_jobs($main_params);

            // file name
            $date_from = $this->input->get_post('from');
            $date_to = $this->input->get_post('to');
            $filename = "Payment_summary_report_{$date_from}_to_{$date_to}.csv";

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename={$filename}");
            header("Pragma: no-cache");

            // headers
            $str = "Payment Date,Invoice #,Agency,Property,Charges,Payments,Payment Type\n";
            foreach ($sql->result() as $row) {

                //payment date
                $payment_date = ( $this->system_model->isDateNotEmpty($row->payment_date) == true ) ? $this->system_model->formatDate($row->payment_date, 'd/m/Y') : '';

                //invoice num
                $check_digit = $this->system_model->getCheckDigit(trim($row->jid));
                $bpay_ref_code = "{$row->jid}{$check_digit}";

                //agency
                $agency_name = "{$row->agency_name}";

                //address
                $full_address = "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3}";

                //charges
                $Charges = ( $row->invoice_amount > 0 ) ? '$' . number_format($row->invoice_amount, 2) : '';

                //payment
                $payment = ( $row->amount_paid > 0 ) ? '$' . number_format($row->amount_paid, 2) : '';

                //balance
               // $bal = ( $row->invoice_balance > 0 ) ? '$' . number_format($row->invoice_balance, 2) : '';

                $str .= "{$payment_date},{$bpay_ref_code},\"{$agency_name}\",\"{$full_address}\",{$Charges},\"{$payment}\",\"{$bal}\",{$row->pt_name}\n";
            }

            echo $str;
        } else { // MAIN LISTING
            if ($search_submit == 'Search') {

                // paginate
                $main_params['limit'] = $per_page;
                $main_params['offset'] = $offset;

                $data['list'] = $this->jobs_model->get_jobs($main_params);
            }

            // total row
            $sel_query = "COUNT(j.`id`) AS jcount";
            $main_params = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => $custom_where_arr,
                'multi_agency_filter' => $agency_filter,
                'search' => $search,
                'type_of_payment' => $payment_type_filter,
                'country_id' => $country_id,
                'join_table' => array('invoice_payments', 'payment_types'),
                'display_query' => 0
            );

            if ($search_submit == 'Search') {
                $query = $this->jobs_model->get_jobs($main_params);
                $total_rows = $query->row()->jcount;
            }


            // total count
            $sel_query = "SUM(inv_pay.`amount_paid`) AS tot_inv_pay";
            $main_params = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => $custom_where_arr,
                'multi_agency_filter' => $agency_filter,
                'search' => $search,
                'country_id' => $country_id,
                'join_table' => array('invoice_payments', 'payment_types'),
                'display_query' => 0
            );

            if ($search_submit == 'Search') {

                $query = $this->jobs_model->get_jobs($main_params);
                $total_count_row = $query->row();

                $data['tot_inv_pay'] = $total_count_row->tot_inv_pay;
            }




            // total count
            $where_append_imp = '';
            $total_count_where_append_arr = [];

            // custom where query append for total count
            if (count($custom_where_arr) > 0) {
                $total_count_where_append_arr = $custom_where_arr;
            }

            // header filter query search append for total count
            if ($country_id > 0) {
                $total_count_where_append_arr[] = "a.`country_id` = {$country_id}";
            }

            // if ($agency_filter > 0) {
            if (count($agency_filter) > 0) {
                // $total_count_where_append_arr[] = "a.`agency_id` = {$agency_filter}";
                $agency_array = implode(", ", $agency_filter);
                if (!empty($agency_array)) {
                    $total_count_where_append_arr[] = "a.`agency_id` IN ($agency_array)";
                }
            }


            if ($payment_type_filter > 0) {
                $total_count_where_append_arr[] = "`inv_pay`.`type_of_payment` = {$payment_type_filter}";
            }

            if ($search != '') {
                $total_count_where_append_arr[] = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$search}%'";
            }



            // combine
            if (count($total_count_where_append_arr) > 0) {
                $where_append_imp = 'AND ' . implode(' AND ', $total_count_where_append_arr);
            }

            // total count query
            $tot_count_sql_str = "
			SELECT SUM(temp_list.`invoice_amount`) AS tot_inv_amt
			FROM (
				SELECT COUNT(inv_pay.`invoice_payment_id`), j.`invoice_amount`, j.`invoice_balance`
				FROM `jobs` AS `j`
				LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
				LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
				INNER JOIN `invoice_payments` AS `inv_pay` ON j.`id` = inv_pay.`job_id`
				LEFT JOIN `payment_types` AS `pay_type` ON inv_pay.`type_of_payment` = pay_type.`payment_type_id`
				WHERE `j`.`id` > 0
				{$where_append_imp}
				GROUP BY `j`.`id`
			) AS temp_list
			";

            if ($search_submit == 'Search') {

                $tot_count_sql = $this->db->query($tot_count_sql_str);
                //echo $this->db->last_query();
                $tot_row = $tot_count_sql->row();

                $data['tot_inv_amt'] = $tot_row->tot_inv_amt;
            }


            // agency filter
            $sel_query = "
				a.`agency_id`,
				a.`agency_name`
			";

            $main_params = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => $custom_where_arr,
                'country_id' => $country_id,
                'join_table' => array('invoice_payments', 'payment_types'),
                'group_by' => 'a.`agency_id`',
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0
            );

            $data['agency_filter'] = $this->jobs_model->get_jobs($main_params);


            // payment types filter
            $sel_query = "
				pay_type.`payment_type_id`,
				pay_type.`pt_name`
			";

            $custom_where_arr[] = "pay_type.`payment_type_id` > 0";

            $main_params = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => $custom_where_arr,
                'country_id' => $country_id,
                'join_table' => array('invoice_payments', 'payment_types'),
                'group_by' => 'inv_pay.`type_of_payment`',
                'sort_list' => array(
                    array(
                        'order_by' => 'pay_type.`pt_name`',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0
            );

            $data['payment_type_filter'] = $this->jobs_model->get_jobs($main_params);

            //by gherx (show all payment types)
            $data['payment_type_filter_v2'] = $this->db->select('*')->from('payment_types')->where('active', 1)->order_by('payment_type_id', 'ASC')->get();
            //by gherx end



            if ($search_submit == 'Search') {

                //base url params
                $pagi_links_params_arr = array(
                    'from' => $this->input->get_post('from'),
                    'to' => $this->input->get_post('to'),
                    'agency_filter' => $agency_filter,
                    'search' => $search,
                    'credit_reason_filter' => $this->input->get_post('credit_reason_filter'),
                    'search_submit' => $search_submit
                );
                $pagi_link_params = "{$uri}/?" . http_build_query($pagi_links_params_arr);

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

            $data['export_link'] = $uri . '/?export=1&' . http_build_query($pagi_links_params_arr);
            $data['uri'] = $uri;

            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }

    public function credits() {

        $data['start_load_time'] = microtime(true);
        $title = "Credits Summary Report";
        $data['title'] = $title;
        $uri = '/reports/credits';

        $export = $this->input->get_post('export');

        $search_submit = $this->input->get_post('search_submit');

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $agency_filter = $this->input->get_post('agency_filter');
        $search = $this->input->get_post('search');
        $credit_reason_filter = $this->input->get_post('credit_reason_filter');

        $from = ( $this->input->get_post('from') != '' ) ? $this->system_model->formatDate($this->input->get_post('from')) : date('Y-m-01');
        $to = ( $this->input->get_post('to') != '' ) ? $this->system_model->formatDate($this->input->get_post('to')) : date('Y-m-t');

        // sort
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'j.`date`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'ASC';

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $custom_where_arr = [];


        // main query
        $sel_query = '
			j.`id` AS jid,
			j.`date` AS jdate,
			j.`invoice_amount`,
			j.`invoice_payments`,
			j.`invoice_credits`,
			j.`invoice_balance`,

			p.`property_id`,
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,

			a.`agency_id`,
			a.`agency_name`,

			inv_cred.`credit_paid`,
			inv_cred.`credit_reason`,
			inv_cred.`credit_date`,

			cred_reas.`credit_reason_id`,
			cred_reas.`reason` AS cr_reason
		';

        // get unpaid jobs and exclude 0 job price
        $custom_where_arr[] = "
		j.`invoice_amount` > 0
		AND j.`status` = 'Completed'
		AND j.`invoice_credits` > 0
		";

        $custom_where_arr[] = "
		( inv_cred.`credit_date` BETWEEN '{$from}' AND '{$to}' )
		";

        //important when updating main params > update main_params_for_export also but removed limit/offset
        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => $custom_where_arr,
            'agency_filter' => $agency_filter,
            'search' => $search,
            'credit_reason_id' => $credit_reason_filter,
            'country_id' => $country_id,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'join_table' => array('invoice_credits', 'credit_reason'),
            'display_query' => 0
        );
        //main params removed limit/offset
        $main_params_for_export = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => $custom_where_arr,
            'agency_filter' => $agency_filter,
            'search' => $search,
            'credit_reason_id' => $credit_reason_filter,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'join_table' => array('invoice_credits', 'credit_reason'),
            'display_query' => 0
        );



        if ($export == 1) { //EXPORT
            $sql = $this->jobs_model->get_jobs($main_params_for_export);

            // file name
            $date_from = $this->input->get_post('from');
            $date_to = $this->input->get_post('to');
            $filename = "Credit_summary_report_{$date_from}_to_{$date_to}.csv";

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename={$filename}");
            header("Pragma: no-cache");

            // headers
            $str = "Credit Date,Invoice #,Property,Charges,Credits,Balance,Reason\n";
            foreach ($sql->result() as $row) {

                //date
                $credit_date = ( $this->system_model->isDateNotEmpty($row->credit_date) == true ) ? $this->system_model->formatDate($row->credit_date, 'd/m/Y') : '';

                //invoice num
                $check_digit = $this->system_model->getCheckDigit(trim($row->jid));
                $bpay_ref_code = "{$row->jid}{$check_digit}";

                //address
                $full_address = "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3}";

                //charges
                $Charges = ( $row->invoice_amount > 0 ) ? '$' . number_format($row->invoice_amount, 2) : '';

                //credit
                $credit = ( $row->credit_paid > 0 ) ? '$' . number_format($row->credit_paid, 2) : '';

                //balance
                $bal = ( $row->invoice_balance > 0 ) ? '$' . number_format($row->invoice_balance, 2) : '';

                //reason
                $reason = ( $row->credit_reason == -1 ) ? 'Other' : $row->cr_reason;

                $str .= "{$credit_date},{$bpay_ref_code},\"{$full_address}\",{$Charges},\"{$credit}\",\"{$bal}\",{$reason}\n";
            }

            echo $str;
        } else { //MAIN LISTING
            if ($search_submit == 'Search') {

                // paginate
                $main_params['limit'] = $per_page;
                $main_params['offset'] = $offset;

                $data['list'] = $this->jobs_model->get_jobs($main_params);
            }

            // total row
            $sel_query = "COUNT(j.`id`) AS jcount";
            $main_params = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => $custom_where_arr,
                'agency_filter' => $agency_filter,
                'search' => $search,
                'credit_reason_id' => $credit_reason_filter,
                'country_id' => $country_id,
                'join_table' => array('invoice_credits', 'credit_reason'),
                'display_query' => 0
            );

            if ($search_submit == 'Search') {
                $query = $this->jobs_model->get_jobs($main_params);
                $total_rows = $query->row()->jcount;
            }


            // total count
            $sel_query = "SUM(inv_cred.`credit_paid`) AS tot_inv_cred";
            $main_params = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => $custom_where_arr,
                'agency_filter' => $agency_filter,
                'search' => $search,
                'country_id' => $country_id,
                'join_table' => array('invoice_credits', 'credit_reason'),
                'display_query' => 0
            );

            if ($search_submit == 'Search') {

                $query = $this->jobs_model->get_jobs($main_params);
                $total_count_row = $query->row();

                $data['tot_inv_cred'] = $total_count_row->tot_inv_cred;
            }



            // total count
            $where_append_imp = '';
            $total_count_where_append_arr = [];

            // custom where query append for total count
            if (count($custom_where_arr) > 0) {
                $total_count_where_append_arr = $custom_where_arr;
            }

            // header filter query search append for total count
            if ($country_id > 0) {
                $total_count_where_append_arr[] = "a.`country_id` = {$country_id}";
            }

            if ($agency_filter > 0) {
                $total_count_where_append_arr[] = "a.`agency_id` = {$agency_filter}";
            }

            if ($credit_reason_filter > 0) {
                $total_count_where_append_arr[] = "inv_cred.`credit_reason` = {$credit_reason_filter}";
            }

            if ($search != '') {
                $total_count_where_append_arr[] = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$search}%'";
            }

            // combine
            if (count($total_count_where_append_arr) > 0) {
                $where_append_imp = 'AND ' . implode(' AND ', $total_count_where_append_arr);
            }

            // total count query
            $tot_count_sql_str = "
			SELECT SUM(temp_list.`invoice_amount`) AS tot_inv_amt
			FROM (
				SELECT COUNT(inv_cred.`invoice_credit_id`), j.`invoice_amount`, j.`invoice_balance`
				FROM `jobs` AS `j`
				LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
				LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
				INNER JOIN `invoice_credits` AS `inv_cred` ON j.`id` = inv_cred.`job_id`
				LEFT JOIN `credit_reason` AS `cred_reas` ON inv_cred.`credit_reason` = cred_reas.`credit_reason_id`
				WHERE `j`.`id` > 0
				{$where_append_imp}
				GROUP BY `j`.`id`
			) AS temp_list
			";

            if ($search_submit == 'Search') {

                $tot_count_sql = $this->db->query($tot_count_sql_str);
                //echo $this->db->last_query();
                $tot_row = $tot_count_sql->row();

                $data['tot_inv_amt'] = $tot_row->tot_inv_amt;
            }


            // agency filter
            $sel_query = "
				a.`agency_id`,
				a.`agency_name`
			";

            $main_params = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => $custom_where_arr,
                'country_id' => $country_id,
                'join_table' => array('invoice_credits', 'credit_reason'),
                'group_by' => 'a.`agency_id`',
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0
            );

            $data['agency_filter'] = $this->jobs_model->get_jobs($main_params);


            // credit reason filter
            $sel_query = "
				cred_reas.`credit_reason_id`,
				cred_reas.`reason` AS cr_reason
			";

            $custom_where_arr[] = "cred_reas.`credit_reason_id` != ''";

            $main_params = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => $custom_where_arr,
                'country_id' => $country_id,
                'join_table' => array('invoice_credits', 'credit_reason'),
                'group_by' => 'inv_cred.`credit_reason`',
                'sort_list' => array(
                    array(
                        'order_by' => 'cred_reas.`reason`',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0
            );

            $data['cred_reason_filter'] = $this->jobs_model->get_jobs($main_params);



            if ($search_submit == 'Search') {

                //base url params
                $pagi_links_params_arr = array(
                    'from' => $this->input->get_post('from'),
                    'to' => $this->input->get_post('to'),
                    'agency_filter' => $agency_filter,
                    'search' => $search,
                    'credit_reason_filter' => $this->input->get_post('credit_reason_filter'),
                    'search_submit' => $search_submit
                );
                $pagi_link_params = "{$uri}/?" . http_build_query($pagi_links_params_arr);

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

            $data['export_link'] = $uri . '/?export=1&' . http_build_query($pagi_links_params_arr);
            $data['uri'] = $uri;

            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }

    public function view_icons() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "All Icons";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';

        $search = $this->input->get_post('search');
        $search_submit = $this->input->get_post('search_submit');
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'ico.page';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'ASC';
        $params['sel_query'] = '
			COUNT(*) as icon_count,
			`ico`.`icon_id`,
			`ico`.`icon`,
			`ico`.`page`,
			`ico`.`description`
		';
        $params = array(
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'search' => $search,
        );

        $data['icons'] = $this->icons_model->getButtonIcons($params);

        // total rows
        $tparams = array(
            'sel_query' => 'COUNT(*) as icon_count'
        );
        $total_rows = $this->icons_model->getButtonIcons($tparams)->row()->icon_count;
        $data['sort_list'] = $total_rows;

        // base url
        $base_url = '/reports/view_icons/';

        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $base_url;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/icons', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function export_franchise_group() {




        $data['start_load_time'] = microtime(true);
        $data['title'] = "Franchise Groups";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';

        $search = $this->input->get_post('search');
        $search_submit = $this->input->get_post('search_submit');
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'ASC';

        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'fg.name';

        $params['sel_query'] = "
			`fg`.`name`,
			(SELECT COUNT(*) FROM agency a WHERE a.franchise_groups_id=fg.franchise_groups_id AND `status` = 'active') as `agency_count`,
		";
        $params['country_id'] = $this->config->item('country');
//        $params['echo_query'] = true;
        $params['paginate'] = array(
            'offset' => $offset,
            'limit' => $per_page
        );
        $params['sort_list'] = [
            array(
                'order_by' => $order_by,
                'sort' => $sort
            )
        ];
        $params['search'] = $search;
        $params['getCsv'] = true;
//        $data['franchise_groups'] = $this->franchisegroups_model->getButtonFranchiseGroups($params);
        // total rows
        $tparams = array(
            'sel_query' => 'COUNT(*) as fg_count'
        );
        $total_rows = $this->franchisegroups_model->getButtonFranchiseGroups($tparams)->row()->fg_count;
        $data['sort_list'] = $total_rows;

        // base url
        $base_url = '/reports/view_franchise_groups/';

        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $base_url;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $filename = "Franchise_groups_agencies_" . date("d/m/Y") . ".csv";
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        // get data
        $usersData = $this->franchisegroups_model->getButtonFranchiseGroups($params);
//        var_dump($usersData);
        // file creation
        $file = fopen('php://output', 'w');

        $header = array("Franchise Group", "Number of Offices");
        fputcsv($file, $header);
        foreach ($usersData as $key => $line) {
            fputcsv($file, $line);
        }
        fclose($file);
        exit;
    }

    public function view_franchise_groups() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Franchise Groups";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';

        $search = $this->input->get_post('search');
        $search_submit = $this->input->get_post('search_submit');
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'ASC';
        if ($this->uri->segment(3, 0) === 0) {
            $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'fg.name';

            $params['sel_query'] = "
			`fg`.`franchise_groups_id`,
			`fg`.`name`,
			(SELECT COUNT(*) FROM agency a WHERE a.franchise_groups_id=fg.franchise_groups_id AND `status` = 'active') as `agency_count`,
		";
            $params['country_id'] = $this->config->item('country');
//        $params['echo_query'] = true;
            $params['paginate'] = array(
                'offset' => $offset,
                'limit' => $per_page
            );
            $params['sort_list'] = [
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ];
            $params['search'] = $search;
//            $params['echo_query'] = 1;
            $data['franchise_groups'] = $this->franchisegroups_model->getButtonFranchiseGroups($params);

            // total rows
            $tparams = array(
                'sel_query' => 'COUNT(*) as fg_count'
            );
            $total_rows = $this->franchisegroups_model->getButtonFranchiseGroups($tparams)->row()->fg_count;
            $data['sort_list'] = $total_rows;

            // base url
            $base_url = '/reports/view_franchise_groups/';

            // pagination
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = $total_rows;
            $config['per_page'] = $per_page;
            $config['base_url'] = $base_url;

            $this->pagination->initialize($config);

            $data['pagination'] = $this->pagination->create_links();

            $pc_params = array(
                'total_rows' => $total_rows,
                'offset' => $offset,
                'per_page' => $per_page
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

            $this->load->view('templates/inner_header', $data);
            $this->load->view('reports/franchise_groups/view_franchise_groups', $data);
            $this->load->view('templates/inner_footer', $data);
        } else {
            $params['sel_query'] = 'a.agency_id,a.agency_name,a.state, a.tot_properties';
            $state = $this->input->get_post('state');
            $sales_rep = $this->input->get_post('salesrep');
            $region = $this->input->get_post('region');
            $phrase = $this->input->get_post('phrase');

            if ($state !== null) {
                $params['state'] = $state;
            }
            if ($sales_rep !== null) {
                $params['salesrep'] = $sales_rep;
            }
            if ($region !== null) {
                $params['region'] = $region;
            }
            if ($phrase !== null) {
                $params['phrase'] = $phrase;
            }
            $params['franchise_groups_id'] = $this->uri->segment(3, 0);
            $params['paginate'] = array(
                'offset' => $offset,
                'limit' => $per_page
            );
            $params['sort_list'] = [
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ];
            $params['search'] = $search;
            $serv_tot = array();
            $prop_tot = 0;
            $alarm_job_types = $this->franchisegroups_model->getAlarmJobTypes()->result_array();

            $isExport = $this->input->get_post('isExport');
            if ($isExport !== null) {
                $params['getCsv'] = true;
                $filename = "Franchise_groups_" . date("d/m/Y") . ".csv";
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                // get data
                $usersData = $this->franchisegroups_model->getButtonFranchiseGroupsAgency($params);
//        var_dump($usersData);
                // file creation
                $file = fopen('php://output', 'w');

                $header = array("Office", "State", 'Properties');
                foreach ($alarm_job_types as $ajt) {
                    $header[] = $ajt['type'];
                }
                fputcsv($file, $header);
                foreach ($usersData as $key => $line) {

                    foreach ($alarm_job_types as $alarm) {
                        $serv_count = $this->franchisegroups_model->getAlarmsServicesNumbers([
                            'agency_id' => $line['agency_id'],
                            'alarm_job_type_id' => $alarm['id'],
                            'service' => 1
                        ]);
                        $serv_tot[$alarm['id']] += $serv_count;
                        $line[] = $serv_count;
                    }
                    unset($line['agency_id']);
                    fputcsv($file, $line);
                }
                fclose($file);
//                exit;
            } else {
                $franchise_group_agency = $this->franchisegroups_model->getButtonFranchiseGroupsAgency($params)->result_array();

                for ($i = 0; $i < count($franchise_group_agency); $i++) {
                    $ctr = 0;
                    foreach ($alarm_job_types as $alarm) {
//                    $serv_count = get_serv_num($row['agency_id'], $ajt['id'], 1);
                        $serv_count = $this->franchisegroups_model->getAlarmsServicesNumbers([
                            'agency_id' => $franchise_group_agency[$i]['agency_id'],
                            'alarm_job_type_id' => $alarm['id'],
                            'service' => 1
                        ]);
                        $serv_tot[$alarm['id']] += $serv_count;
                        $franchise_group_agency[$i]['AJT_' . $ctr] = $serv_count;
                        $ctr++;
                    }
                    $prop_tot += $franchise_group_agency[$i]['tot_properties'];
                }

                $data['franchise_groups_agency'] = $franchise_group_agency;
                $data['alarm_job_types'] = $alarm_job_types;
                $data['prop_tot'] = $prop_tot;
                $data['serv_tot'] = $serv_tot;
                $data['sales_rep'] = $this->franchisegroups_model->getSalesRep(['franchise_groups_id' => $this->uri->segment(3)]);
                $data['result_region'] = $this->franchisegroups_model->getAgencyRegion();
                $data['fg_id'] = $this->uri->segment(3);
                // total rows
                $tparams = array(
                    'sel_query' => 'COUNT(*) as fg_count'
                );
                $total_rows = $this->franchisegroups_model->getButtonFranchiseGroupsAgency($tparams)->row()->fg_count;
                $data['sort_list'] = $total_rows;
                $data['state'] = $state;
                $data['salesrep'] = $sales_rep;
                $data['region'] = $region;
                $data['phrase'] = $phrase;
                // base url
                $base_url = '/reports/view_franchise_groups/' . $this->uri->segment(3, 0);

                // pagination
                $config['page_query_string'] = TRUE;
                $config['query_string_segment'] = 'offset';
                $config['total_rows'] = $total_rows;
                $config['per_page'] = $per_page;
                $config['base_url'] = $base_url;

                $this->pagination->initialize($config);

                $data['pagination'] = $this->pagination->create_links();

                $pc_params = array(
                    'total_rows' => $total_rows,
                    'offset' => $offset,
                    'per_page' => $per_page
                );
                $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
                $this->load->view('templates/inner_header', $data);
                $this->load->view('reports/franchise_groups/view_franchise_groups_agency', $data);
                $this->load->view('templates/inner_footer', $data);
            }
        }
    }

    public function view_add_franchise_groups() {


        $data['start_load_time'] = microtime(true);
        $data['title'] = "Add Franchise Groups";

        $valid_config = [
            [
                'field' => 'franchise_groups',
                'label' => 'franchise_groups',
                'rules' => 'required',
                'errors' => [
                    'required' => 'You must provide a %s.'
                ]
            ]
        ];
        $this->form_validation->set_rules($valid_config);
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/franchise_groups/view_add_franchise_groups', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function add_franchise_groups_action_form_submit() {
        $name = $this->input->get_post('franchise_groups');
        // INSERT TO DATABASE
        $insert_param = [
            'name' => $name,
            'country_id' => $this->config->item('country')
        ];
        $hasDup = $this->franchisegroups_model->check_dup_franchise_groups([
            'name' => $name, 'country_id' => $this->config->item('country')]);
        if ($hasDup) {
            $this->session->set_flashdata([
                'error_msg' => 'Franchise Group already exists.',
                'status' => 'error'
            ]);

            redirect(base_url('/reports/view_franchise_groups'));
        }
        $last_insert_icon_id = $this->franchisegroups_model->add_franchise_groups($insert_param);
//        var_dump($last_insert_icon_id);
//        die();
        // SET FLASH NOTICES
        $this->session->set_flashdata([
            'success_msg' => 'Franchise Group Added.',
            'status' => 'success'
        ]);
        redirect(base_url('/reports/view_franchise_groups'));

        $this->session->set_flashdata([
            'error_msg' => 'Add unsuccessful.',
            'status' => 'error'
        ]);
        redirect(base_url('/reports/view_franchise_groups'));
    }

    public function update_franchise_groups_action_form_submit() {
        $params['franchise_groups_id'] = $this->input->get_post('franchise_groups_id');
        $params['franchise_groups'] = [
            'name' => $this->input->get_post('name')
        ];
        $hasDup = $this->franchisegroups_model->check_dup_franchise_groups([
            'name' => $this->input->get_post('name'), 'country_id' => $this->config->item('country')]);
        if ($hasDup) {
            $this->session->set_flashdata([
                'error_msg' => 'Franchise Group already exists.',
                'status' => 'error'
            ]);

            redirect(base_url('/reports/view_franchise_groups'));
        }

        $updated = $this->franchisegroups_model->update_franchise_groups($params);

        if ($updated) {
            $this->session->set_flashdata([
                'success_msg' => 'Franchise Group updated.',
                'status' => 'success'
            ]);
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Update not successful.',
                'status' => 'error'
            ]);
        }
        redirect(base_url('/reports/view_franchise_groups'));

//		echo json_encode( $json_data );
    }

    public function delete_franchise_groups_action_ajax() {
        $params['franchise_groups_id'] = $this->input->get_post('franchise_groups_id');

        $deleted = $this->franchisegroups_model->remove_franchise_groups($params);
        $json_data = array(
            'title' => 'Error',
            'type' => 'error',
            'status' => false,
            'message' => 'Delete not successful.'
        );
        if ($deleted) {
            $json_data = array(
                'title' => 'Success',
                'type' => 'success',
                'status' => true,
                'message' => 'Delete successful.',
            );
        }

        echo json_encode($json_data);
    }

    // Display the add icon form
    public function add_icon() {
        // load the form upload library
        // $config['upload_path']          = './uploads/icons/';
        // $config['allowed_types']        = 'gif|jpg|png';
        // $config['max_size']             = 100;
        // $config['max_width']            = 1024;
        // $config['max_height']           = 768;
        // $this->load->library('upload', $config);


        $data['start_load_time'] = microtime(true);
        $data['title'] = "Add Icon";

        $valid_config = [
            [
                'field' => 'page',
                'label' => 'Page',
                'rules' => 'required',
                'errors' => [
                    'required' => 'You must provide a %s.'
                ]
            ],
            [
                'field' => 'description',
                'label' => 'Description',
                'rules' => 'required',
                'errors' => [
                    'required' => 'You must provide a %s.'
                ]
            ]
        ];
        $this->form_validation->set_rules($valid_config);

        // RUN THE VALIDATION
        // if( $this->form_validation->run() != FALSE ){
        // 	// POST DATA
        // 	$page = $this->input->get_post( 'page' );
        // 	$description = $this->input->get_post( 'description' );
        // 	//RUN THE UPLOAD
        // 	if( $this->upload->do_upload( 'iconfile' ) ){ // successful upload
        // 		// INSERT TO DATABASE
        // 		$insert_param = [
        // 			'icon_path' => 'uploads/icons/' . $this->upload->data( 'file_name' ),
        // 			'page' => $page,
        // 			'desc' => $description
        // 		];
        // 		$last_insert_icon_id = $this->icons_model->add_icon( $insert_param );
        // 		// SET FLASH NOTICES
        // 		$this->session->set_flashdata( 'message','Successfully added the icon.' );
        // 		$this->session->set_flashdata( 'status','success' );
        // 	}else{ // failed upload
        // 		$this->session->set_flashdata( 'message', 'Failed adding the icon:' . $this->upload->display_errors() );
        // 		$this->session->set_flashdata( 'status','danger' );
        // 	}
        // }
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/new_icon', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    // AJAX add icon
    public function ajax_add_icon() {
        $config['upload_path'] = './uploads/icons/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = 100;
        $config['max_width'] = 1024;
        $config['max_height'] = 768;
        $this->load->library('upload', $config);

        $page = $this->input->get_post('page');
        $description = $this->input->get_post('description');

        $json_data = array(
            'title' => 'Error',
            'type' => 'error',
            'status' => false,
            'message' => 'Add unsuccessful.',
        );
        //RUN THE UPLOAD
        if ($this->upload->do_upload('iconfile')) { // successful upload
            // INSERT TO DATABASE
            $insert_param = [
                'icon_path' => 'uploads/icons/' . $this->upload->data('file_name'),
                'page' => $page,
                'desc' => $description
            ];
            $last_insert_icon_id = $this->icons_model->add_icon($insert_param);

            // SET FLASH NOTICES
            $json_data = array(
                'title' => 'Success',
                'type' => 'success',
                'status' => true,
                'message' => 'Icon added.'
            );
        }

        echo json_encode($json_data);
    }

    public function add_icon_action_form_submit() {
        $config['upload_path'] = './uploads/icons/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = 100;
        $config['max_width'] = 1024;
        $config['max_height'] = 768;
        $this->load->library('upload', $config);

        $page = $this->input->get_post('page');
        $description = $this->input->get_post('description');
        $uploadFile = $this->upload->do_upload('iconfile');
        //RUN THE UPLOAD
        if ($uploadFile) { // successful upload
            // INSERT TO DATABASE
            $insert_param = [
                'icon_path' => 'uploads/icons/' . $this->upload->data('file_name'),
                'page' => $page,
                'desc' => $description
            ];
            $last_insert_icon_id = $this->icons_model->add_icon($insert_param);

            // SET FLASH NOTICES
            $this->session->set_flashdata([
                'success_msg' => 'Icon Added.',
                'status' => 'success'
            ]);
            redirect(base_url('/reports/view_icons'));
        }
        $upload_err_msg = strip_tags($this->upload->display_errors());
        $this->session->set_flashdata([
            'error_msg' => 'Add unsuccessful.\n' . $upload_err_msg,
            'status' => 'error'
        ]);
        redirect(base_url('/reports/view_icons'));
    }

    public function update_icon() {
        $params['icon_id'] = $icon = $this->input->get_post('icon_id');
        $desc = $this->input->get_post('description');
        $page = $this->input->get_post('page');
        $params['icon'] = array(
            'description' => $desc,
            'page' => $page
        );
        $updated = $this->icons_model->update_icon($params);

        if ($updated) {
            $this->session->set_flashdata([
                'success_msg' => 'Icon updated.',
                'status' => 'success'
            ]);
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Icon not updated.',
                'status' => 'error'
            ]);
        }
        redirect(base_url('/reports/view_icons'));

//		echo json_encode( $json_data );
    }

    // Ajax update icon
    public function update_icon_bak() {
        $params['icon_id'] = $icon = $this->input->get_post('icon_id');
        $desc = $this->input->get_post('description');
        $page = $this->input->get_post('page');
        $params['icon'] = array(
            'description' => $desc,
            'page' => $page
        );
        $updated = $this->icons_model->update_icon($params);
        $json_data = array(
            'title' => 'Error',
            'type' => 'error',
            'status' => false,
            'message' => 'Update unsuccessful.',
        );
        if ($updated) {
            $json_data = array(
                'title' => 'Success',
                'type' => 'success',
                'status' => true,
                'message' => 'Icon updated.'
            );
        }

        echo json_encode($json_data);
    }

    // Ajax delete icon
    public function delete_icon() {
        $uploadsPath = './uploads/icons/';
        $params['icon_id'] = $this->input->get_post('icon_id');

        $sel_query = '
			`ico`.icon as pathToFile_name
		';
        $params['sel_query'] = $sel_query;
        $params['bi_id'] = $this->input->get_post('icon_id');
        $icon = $this->icons_model->getButtonIcons($params);
        $file_name = $icon->row()->pathToFile_name;

        $deleted = $this->icons_model->remove_icon($params);
        $json_data = array(
            'title' => 'Error',
            'type' => 'error',
            'status' => false,
            'message' => 'Delete unsuccessful.'
        );
        if ($deleted) {
            unlink($file_name);
            $json_data = array(
                'title' => 'Success',
                'type' => 'success',
                'status' => true,
                'message' => 'Delete successful.',
                'file' => $file_name
            );
        }

        echo json_encode($json_data);
    }

    /**
     * PURCHASE ORDERS
     * return result query
     */
    public function purchase_order() {

        $this->load->model('supplier_model');
        $this->load->model('purchase_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Purchase Order";

        $export = $this->input->get_post('export');

        //var pass data
        $data['date_from'] = ($this->input->get_post('date_from') != "") ? $this->system_model->formatDate($this->input->get_post('date_from'), 'd/m/Y') : date('01/m/Y');
        $data['date_to'] = ($this->input->get_post('date_to') != "") ? $this->system_model->formatDate($this->input->get_post('date_to'), 'd/m/Y') : date('d/m/Y');
        $data['supplier'] = $this->input->get_post('supplier_filter');

        //db ready
        $date_from = ($this->input->get_post('date_from') != "") ? $this->system_model->formatDate($this->input->get_post('date_from')) : $this->system_model->formatDate(date('01/m/Y'));
        $data['date_from_2'] = ($this->input->get_post('date_from') != "") ? $this->system_model->formatDate($this->input->get_post('date_from')) : $this->system_model->formatDate(date('01/m/Y'));
        $date_to = ($this->input->get_post('date_to') != "") ? $this->system_model->formatDate($this->input->get_post('date_to')) : $this->system_model->formatDate(date('d/m/Y'));
        $data['date_to_2'] = ($this->input->get_post('date_to') != "") ? $this->system_model->formatDate($this->input->get_post('date_to')) : $this->system_model->formatDate(date('d/m/Y'));
        $supplier = $this->input->get_post('supplier_filter');

        // pagination settings
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get supplier list - dropdown filter
        $supplier_params = array(
            'sel_query' => "suppliers_id, company_name",
            'sort_list' => array(
                array(
                    'order_by' => 'company_name',
                    'sort' => 'ASC'
                )
            )
        );
        $data['supplier_list'] = $this->supplier_model->getSupplier($supplier_params)->result_array();


        //Purchase order list
        $sel_query = "
			po.purchase_order_id as po_id,
			po.date as po_date,
			po.purchase_order_num,
			po.suppliers_id as po_suppliers_id,
			po.item_note,
			po.invoice_total as po_invoice_total,

			sup.`address` AS sup_address,
			sup.`email` AS sup_email,
			sup.company_name as sup_company_name,

			sa.`FirstName` AS dt_fname,
			sa.`LastName` AS dt_lname,
			sa.`address` AS dt_address,
			sa.`Email` AS dt_email,

			sa2.`FirstName` AS ob_fname,
			sa2.`LastName` AS ob_lname,
			sa2.`Email` AS ob_email,

			a.agency_name as a_name,
			a.agency_id,
            aght.priority,
            apmd.abbreviation
		";
        $params = array(
            'sel_query' => $sel_query,
            'filterDate' => array(
                'from' => $date_from,
                'to' => $date_to
            ),
            'supplier_id' => $supplier,
            'sort_list' => array(
                array(
                    'order_by' => 'po.date',
                    'sort' => 'DESC'
                )
            )
        );

        if($export == 1){

            // file name
            $filename = 'purchase_order_' . date('Y-m-d') . '.csv';

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            $lists = $this->purchase_model->getPurchaseOrder($params);

            // file creation
            $file = fopen('php://output', 'w');

            //header
            $header = array("Date", "PO No.", "Supplier", "Deliver to", "Order Total", "Agency", "Problem");
            fputcsv($file, $header);

            foreach ($lists->result_array() as $exportrow) {

                //supplier total
                if( $exportrow['po_suppliers_id'] == $this->purchase_model->getDynamicHandyManID() ){ // if supplier is handyman

                    $poi_tot = $exportrow['po_invoice_total'];

                }else{

                    $jparams = array(
                        'sel_query' => "SUM(poi.`total`) AS poi_total",
                        'purchase_order_id' => $exportrow['po_id']
                    );
                    $poi_sql = $this->purchase_model->getPurchaseOrderItem($jparams);
                    $poi = $poi_sql->row_array();
                    $poi_tot = $poi['poi_total'];

                }
                //supplier total end

                $exportdata['date'] = ($this->system_model->isDateNotEmpty($exportrow['po_date'])) ? $this->system_model->formatDate($exportrow['po_date'], 'd/m/Y') : NULL;
                $exportdata['po_num'] = $exportrow['purchase_order_num'];
                $exportdata['supplier'] = $exportrow['sup_company_name'];
                $exportdata['deliver_to'] = $this->system_model->formatStaffName($exportrow['dt_fname'], $exportrow['dt_lname']);
                $exportdata['order_total'] = ($poi_tot>0)?'$'.number_format($poi_tot,2):'';
                $exportdata['agency'] = $exportrow['a_name'];
                $exportdata['problem'] =  ( $exportrow['po_suppliers_id'] == $this->purchase_model->getDynamicHandyManID() )?$exportrow['item_note']:NULL;

                fputcsv($file, $exportdata);
            }

            fclose($file);
            exit;

        }else{
            //add per page and limit for normal query listing
            $params['limit'] = $per_page;
            $params['offset'] = $offset;

            $data['lists'] = $this->purchase_model->getPurchaseOrder($params);

            //total row
            $params_total_rows = array(
                'sel_query' => "COUNT(po.purchase_order_id) as po_count",
                'filterDate' => array(
                    'from' => $date_from,
                    'to' => $date_to
                ),
                'supplier_id' => $supplier,
            );
            $query = $this->purchase_model->getPurchaseOrder($params_total_rows);
            $total_rows = $query->row()->po_count;


            //PAGINATION
            $pagi_links_params_arr = array(
                'date_from' => $date_from,
                'date_to' => $date_to,
                'supplier_filter' => $supplier
            );

            $pagi_link_params = '/reports/purchase_order/?' . http_build_query($pagi_links_params_arr);

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


            //VIEWS
            $this->load->view('templates/inner_header', $data);
            $this->load->view('reports/purchase_order', $data);
            $this->load->view('templates/inner_footer', $data);

        }


    }

    /**
     * PURCHASE ORDER DETAIL/UPDATE PAGE
     * param purchase detail id
     */
    public function purchase_order_details($id) {

        $this->load->model('supplier_model');
        $this->load->model('purchase_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Purchase Order Details";


        if ($id && is_numeric($id)) {

            $data['purchase_id'] = $id; //padd data

            $po_params = array(
                'sel_query' => "
					po.purchase_order_num,
					po.date as po_date,
					po.suppliers_id,
					po.invoice_total,
					po.agency_id,
					po.item_note,
					po.deliver_to,
					po.ordered_by,
					po.comments,
					sup.company_name,
					sup.address,
                    sup.email,
                    sup.sales_agreement_number,
					sa.FirstName,
					sa.LastName,
					sa.address as delivery_address,
					sa.Email as delivery_email,
					sa2.FirstName as order_by_fname,
					sa2.LastName as order_by_lname,
					sa2.Email as order_by_email
				",
                'purchase_order_id' => $id
            );
            $data['po'] = $this->purchase_model->getPurchaseOrder($po_params)->row_array();

            //staff list
            $staff_params = array(
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName",
                'sort_list' => array(
                    array(
                        'order_by' => 'sa.FirstName',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['staff_list'] = $this->gherxlib->getStaffInfo($staff_params);
        } else {
            redirect('/reports/purchase_order');
        }


        //VIEWS
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/purchase_order_details', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Get/fetch Pruchase item via ajax
     */
    public function ajax_purchase_order_item_list() {

        $this->load->model('supplier_model');
        $this->load->model('purchase_model');
        $this->load->model('stock_model');

        $purchase_id = $this->input->post('purchase_id');
        $supplier_id = $this->input->post('supplier_id');

        //Switch parameters if fetch by supplier_id or purcase_id
        if ($purchase_id && !empty($purchase_id)) {
            $jparams = array(
                'sel_query' => "poi.purchase_order_item_id,poi.stocks_id, poi.quantity, poi.total, s.item, s.code, s.price, po.purchase_order_id",
                'purchase_order_id' => $purchase_id
            );
            $data['poi_sql'] = $this->purchase_model->getPurchaseOrderItem($jparams);
        } else if ($supplier_id && !empty($supplier_id)) {

            $jparams = array(
                'sel_query' => "s.item, s.code, s.price, s.stocks_id, s.carton",
                'suppliers_id' => $supplier_id,
                'status' => 1,
                'sort_list' => array(
                    array(
                        'order_by' => 's.`item`',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['poi_sql'] = $this->stock_model->getStock($jparams);
        }


        //load views
        $this->load->view('reports/ajax_purchase_order_item_list_by_purchase_id', $data);
    }

    /**
     * Get supplier details via ajax
     */
    public function ajax_get_supplier_details() {

        $this->load->model('supplier_model');

        $supplier_id = $this->input->post('supplier_id');
        $params = array(
            'sel_query' => "suppliers_id,company_name,address,email,sales_agreement_number,phone",
            'suppliers_id' => $supplier_id
        );
        $json_data['query'] = $this->supplier_model->getSupplier($params)->row_array();


        echo json_encode($json_data);
    }

    /**
     * Get Staff via ajax (json)
     */
    public function ajax_get_staff_accounts() {

        $staff_id = $this->input->post('staff_id');
        $params = array(
            'sel_query' => "sa.StaffID,sa.Email,sa.FirstName,sa.LastName,sa.address,sa.`ContactNumber`",
            'staff_id' => $staff_id
        );
        $query = $this->gherxlib->getStaffInfo($params)->row();

        $json_data['address'] = $query->address;
        $json_data['email'] = $query->Email;
        $json_data['contact_num'] = $query->ContactNumber;
        $json_data['fullname'] = $this->system_model->formatStaffName($query->FirstName, $query->LastName);
        $json_data['fullname2'] = "{$query->FirstName} {$query->LastName}";

        echo json_encode($json_data);
    }

    public function purchase_order_details_update() {

        $this->load->model('purchase_model');

        $purchase_order_id = $this->input->post('purchase_order_id');
        $date = $this->input->post('date');
        $date2 = $this->system_model->formatDate($date);
        $suppliers_id = $this->input->post('supplier');
        $item_note = $this->input->post('item_note');
        $deliver_to = $this->input->post('deliver_to');
        $comments = $this->input->post('comments');
        $ordered_by = $this->input->post('ordered_by');

        $agency_id = $this->input->post('agency');
        $invoice_total = $this->input->post('invoice_total');
        $supplier_email = $this->input->post('supplier_email');
        $reciever_email = $this->input->post('reciever_email');
        $order_by_email = $this->input->post('order_by_email');
        $sales_agreement_number = $this->input->post('sales_agreement_number');


        if ($suppliers_id == $this->purchase_model->getDynamicHandyManID()) { //SUPPLIER IS HANDYMAN
            $handyman_tweak_array = array(
                'agency_id' => $agency_id,
                'invoice_total' => $invoice_total
            );
        } else {

            $handyman_tweak_array = array(
                'item_note' => $item_note,
                'deliver_to' => $deliver_to,
                'comments' => $comments,
                'ordered_by' => $ordered_by
            );
        }


        // update purchase order
        $update_data = array(
            'date' => $date2,
            'suppliers_id' => $suppliers_id
                ) + $handyman_tweak_array;

        $this->purchase_model->update_purchase_order($purchase_order_id, $update_data);


        if ($suppliers_id != $this->purchase_model->getDynamicHandyManID()) {

            // clear purchase order item
            $this->purchase_model->delete_purchase_order_item($purchase_order_id);

            // purchase order items
            $stocks_id_arr = $this->input->post('stocks_id');
            $qty_arr = $this->input->post('qty');
            $total_arr = $this->input->post('total');

            //loop and add purchase_order_item
            foreach ($stocks_id_arr as $index => $stocks_id) {

                $add_data = array(
                    'purchase_order_id' => $purchase_order_id,
                    'stocks_id' => $stocks_id,
                    'quantity' => $qty_arr[$index],
                    'total' => $total_arr[$index],
                    'active' => 1,
                    'deleted' => 0,
                    'date_created' => date('Y-m-d H:i:s')
                );
                $this->purchase_model->add_purchase_order_item($add_data);
            }
        }


        //Email purchase order if tick
        $epo = $this->input->post('email_purchase_order');
        if ($epo == 1) {


            //load email library
            $this->load->library('email');


            //email post datas
            $country_query = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));
            $e_from = $country_query->outgoing_email;
            $email_data['trading_name'] = $country_query->trading_name;
            $email_data['company_address'] = $country_query->company_address;
            $email_data['abn'] = $country_query->abn;

            $email_data['purchase_order_num'] = $this->input->post('purchase_order_num');
            $email_data['purchase_order_id'] = $this->input->post('purchase_order_id');
            $email_data['date'] = $this->input->post('date');
            $email_data['date2'] = $this->system_model->formatDate($date);
            $email_data['suppliers_id'] = $this->input->post('supplier');
            $email_data['supplier_name'] = $this->input->post('supplier_name');
            $email_data['supplier_address'] = $this->input->post('supplier_address');
            $email_data['supplier_email'] = $this->input->post('supplier_email');
            $email_data['sales_agreement_number'] = $this->input->post('sales_agreement_number');
            $email_data['item_note'] = $this->input->post('item_note');
            $email_data['deliver_to'] = $this->input->post('deliver_to');
            $email_data['deliver_to_name'] = $this->input->post('deliver_to_name');
            $email_data['delivery_address'] = $this->input->post('delivery_address');
            $email_data['comments'] = $this->input->post('comments');
            $email_data['ordered_by'] = $this->input->post('ordered_by');

            $email_data['agency_id'] = $this->input->post('agency');
            $email_data['invoice_total'] = $this->input->post('invoice_total');
            $email_data['supplier_email'] = $this->input->post('supplier_email');
            $email_data['reciever_email'] = $this->input->post('reciever_email');
            $email_data['order_by_email'] = $this->input->post('order_by_email');
            $email_data['ordered_by_full_name'] = $this->input->post('ordered_by_full_name');

            $email_data['code_arr'] = $this->input->post('code');
            $email_data['item_arr'] = $this->input->post('item');
            $email_data['price_arr'] = $this->input->post('price');
            $email_data['qty_arr'] = $this->input->post('qty');
            $email_data['total_arr'] = $this->input->post('total');


            $to_email_arr = [];
            if (filter_var($supplier_email, FILTER_VALIDATE_EMAIL)) {
                $to_email_arr[] = $supplier_email;
            }
            if (filter_var($reciever_email, FILTER_VALIDATE_EMAIL)) {
                $to_email_arr[] = $reciever_email;
            }
            if (filter_var($order_by_email, FILTER_VALIDATE_EMAIL)) {
                $to_email_arr[] = $order_by_email;
            }
            $e_to = implode(",", $to_email_arr);
            //$e_to = "vanessah@sats.com.au";  //QA

            /* $config = Array(
              'protocol' => 'smtp',
              'smtp_host' => 'ssl://smtp.googlemail.com',
              'smtp_port' => 465,
              'smtp_user' => 'itsmegherx@gmail.com',
              'smtp_pass' => 'sdfsafd',
              'wordwrap' => TRUE,
              'mailtype' => 'html',
              'charset' => 'utf-8'
              );
             */

            $config = Array(
                'mailtype' => 'html',
                'charset' => 'utf-8'
            );
            $this->email->initialize($config);
            $this->email->set_newline("\r\n");
            $this->email->from($e_from, "SATS - Smoke Alarm Testing Services");
            $this->email->to($e_to);
            $this->email->subject('Purchase Order Updated');
            $e_body = $this->load->view('emails/purchase_order_updated', $email_data, TRUE);
            $this->email->message($e_body);
            $this->email->send();
        }

        $success_message = "Update Successful";
        $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
        redirect("/reports/purchase_order_details/" . $purchase_order_id, 'refresh');
    }

    public function add_purchase_order() {

        $this->load->model('purchase_model');
        $this->load->model('supplier_model');

        $data['pon'] = $this->purchase_model->getPurchaseOrderLastIDNumber()->row_array();

        //staff list
        $staff_params = array(
            'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName",
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                )
            )
        );
        $data['staff_list'] = $this->gherxlib->getStaffInfo($staff_params);

        //get staff by staff staff id
        $staff_paramse2 = array(
            'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.Email, sa.`ContactNumber`",
            'staff_id' => $this->session->staff_id
        );
        $data['staff_a'] = $this->gherxlib->getStaffInfo($staff_paramse2)->row_array();


        //if submit button is clicked/set
        if ($this->input->post('btn_add_po')) {

            // Post data
            $country_id = $this->config->item('country');
            $purchase_order_num = $this->input->post('purchase_order_num');
            $date = $this->input->post('date');
            $date2 = $this->system_model->formatDate($date);

            $suppliers_id = $this->input->post('supplier');
            $supplier_name = $this->input->post('supplier_name');
            $supplier_address = $this->input->post('supplier_address');
            $supplier_email = $this->input->post('supplier_email');
            $supplier_phone = $this->input->post('supplier_phone');

            $code_arr = $this->input->post('code');
            $item_arr = $this->input->post('item');
            $price_arr = $this->input->post('price');
            $qty_arr = $this->input->post('qty');
            $total_arr = $this->input->post('total');
            $item_note = $this->input->post('item_note');

            $deliver_to = $this->input->post('deliver_to');
            $deliver_to_name = $this->input->post('deliver_to_name');
            $delivery_address = $this->input->post('delivery_address');
            $reciever_email = $this->input->post('reciever_email');

            $comments = $this->input->post('comments');

            $ordered_by = $this->input->post('ordered_by');
            $ordered_by_name = $this->input->post('ordered_by_name');
            $ordered_by_full_name = $this->input->post('ordered_by_full_name');
            $order_by_email = $this->input->post('order_by_email');

            $agency_id = $this->input->post('agency');
            $invoice_total = $this->input->post('invoice_total');
            $sales_agreement_number = $this->input->post('sales_agreement_number');
            $receiver_phone = $this->input->post('receiver_phone');


            //INSERT NEW PURCHASE ORDER DATA
            $data = array(
                'purchase_order_num' => $purchase_order_num,
                'date' => $date2,
                'suppliers_id' => $suppliers_id,
                'item_note' => $item_note,
                'deliver_to' => $deliver_to,
                'comments' => $comments,
                'ordered_by' => $ordered_by,
                'agency_id' => $agency_id,
                'invoice_total' => $invoice_total,
                'active' => 1,
                'deleted' => 0,
                'date_created' => date('Y-m-d H:i:s'),
                'country_id' => $this->config->item('country'),
                'receiver_phone' => $receiver_phone
            );
            $this->db->insert('purchase_order', $data);

            $purchase_order_id = $this->db->insert_id();

            //INSERT PURCHASE ORDER ITEM
            $stocks_id_arr = $this->input->post('stocks_id');
            $qty_arr = $this->input->post('qty');
            $total_arr = $this->input->post('total');

            foreach ($stocks_id_arr as $index => $stocks_id) {

                $add_data = array(
                    'purchase_order_id' => $purchase_order_id,
                    'stocks_id' => $stocks_id,
                    'quantity' => $qty_arr[$index],
                    'total' => $total_arr[$index],
                    'active' => 1,
                    'deleted' => 0,
                    'date_created' => date('Y-m-d H:i:s')
                );
                $this->purchase_model->add_purchase_order_item($add_data);
            }

            $epo = $this->input->post('email_purchase_order');

            if ($epo == 1) {


                //load email library
                $this->load->library('email');


                //email post datas
                $country_query = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));
                $e_from = $country_query->outgoing_email;
                $email_data['trading_name'] = $country_query->trading_name;
                $email_data['company_address'] = $country_query->company_address;
                $email_data['abn'] = $country_query->abn;

                $email_data['purchase_order_num'] = $purchase_order_num;
                $email_data['purchase_order_id'] = $purchase_order_id;
                $email_data['date'] = $date;
                $email_data['date2'] = $date2;
                $email_data['suppliers_id'] = $suppliers_id;
                $email_data['supplier_name'] = $supplier_name;
                $email_data['supplier_address'] = $supplier_address;
                $email_data['supplier_email'] = $supplier_email;
                $email_data['sales_agreement_number'] = $sales_agreement_number;
                $email_data['item_note'] = $item_note;
                $email_data['deliver_to'] = $deliver_to;
                $email_data['deliver_to_name'] = $deliver_to_name;
                $email_data['delivery_address'] = $delivery_address;
                $email_data['comments'] = $comments;
                $email_data['ordered_by'] = $ordered_by;

                $email_data['agency_id'] = $agency_id;
                $email_data['invoice_total'] = $invoice_total;
                $email_data['supplier_email'] = $this->input->post('supplier_email');
                $email_data['reciever_email'] = $this->input->post('reciever_email');
                $email_data['order_by_email'] = $this->input->post('order_by_email');
                $email_data['order_by_email'] = $this->input->post('order_by_email');
                $email_data['receiver_phone'] = $this->input->post('receiver_phone');

                $email_data['code_arr'] = $this->input->post('code');
                $email_data['item_arr'] = $this->input->post('item');
                $email_data['price_arr'] = $this->input->post('price');
                $email_data['qty_arr'] = $this->input->post('qty');
                $email_data['total_arr'] = $this->input->post('total');

                
                $to_email_arr = [];
                if (filter_var($supplier_email, FILTER_VALIDATE_EMAIL)) {
                    $to_email_arr[] = $supplier_email;
                }
                if (filter_var($reciever_email, FILTER_VALIDATE_EMAIL)) {
                    $to_email_arr[] = $reciever_email;
                }
                if (filter_var($order_by_email, FILTER_VALIDATE_EMAIL)) {
                    $to_email_arr[] = $order_by_email;
                }
                $e_to = implode(",", $to_email_arr);
                
                //$e_to = "vaultdweller123@gmail.com"; 

                $email_subject = "Purchase Order: {$purchase_order_num}";

                /* $config = Array(
                  'protocol' => 'smtp',
                  'smtp_host' => 'ssl://smtp.googlemail.com',
                  'smtp_port' => 465,
                  'smtp_user' => 'itsmegherx@gmail.com',
                  'smtp_pass' => 'sdfdf..sdfafsd',
                  'wordwrap' => TRUE,
                  'mailtype' => 'html',
                  'charset' => 'utf-8'
                  );
                 */

                $config = Array(
                    'mailtype' => 'html',
                    'charset' => 'utf-8'
                );
                $this->email->initialize($config);
                $this->email->set_newline("\r\n");
                $this->email->from($e_from, "SATS - Smoke Alarm Testing Services");
                $this->email->to($e_to);
                $this->email->subject($email_subject);
                $e_body = $this->load->view('emails/purchase_order_updated', $email_data, TRUE);
                $this->email->message($e_body);
                $this->email->send();
            }

            $success_message = "Items successfully Ordered";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect("/reports/add_purchase_order/", 'refresh');
        }


        $data['title'] = "Add Purchase Order";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/add_purchase_order', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function daily_figures() {

        $this->load->model('figure_model'); // Load model

        $data['from'] = date('Y-m-01');
        $data['to'] = date('Y-m-t');

        // variables
        $data['start_date'] = ( $this->input->get_post('from_date') != "" ) ? $this->input->get_post('from_date') : date('Y-m-01'); // start
        $data['end_date'] = ( $this->input->get_post('end_date') != "" ) ? $this->input->get_post('end_date') : date('Y-m-t'); // end of month
        $country_id = $this->config->item('country');
        $data['date'] = date('Y-m-d');

        //NEXT AND PREV DATE
        $data['prev_day'] = array(
            'from' => date("Y-m-01", strtotime("{$data['start_date']} -1 month")),
            'to' => date("Y-m-t", strtotime("{$data['end_date']} -1 month")),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Month '
        );

        $data['next_day'] = array(
            'from' => date("Y-m-01", strtotime("{$data['start_date']} +1 month")),
            'to' => date("Y-m-t", strtotime("{$data['start_date']} +1 month")),
            'title' => 'Next Month <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );

        // get daily figures data
        $df_sql = $this->figure_model->getDailyFigures($data['start_date']);
        $data['df'] = $df_sql->row_array();

        // get todays daily figure
        $dfpd_sql = $this->figure_model->getDailyFiguresPerDate($data['date']);
        $dfpd = $dfpd_sql->row_array();
        $data['todays_working_day'] = $dfpd['working_day'];




        $data['start_load_time'] = microtime(true);
        $data['title'] = "Daily Figures";

        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/daily_figures', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_daily_figures_fetch_data() {

        $this->load->model('figure_model'); // Load model

        $date = $this->input->post('date');

        // get Number of Techs Today
        $sales = $this->figure_model->jGetSales();
        $techs = $this->figure_model->jGetNumOfTechToday($date);
        $jobs = $this->figure_model->jGetNumJobsCompleted();
        
        // exclude Upfront Bill and Other Supplier
        $params = array(
            'exc_ub_os' => true,
            'exclude_dha' => true
        );
        $sales_ub_os_only = $this->figure_model->jGetSales($params);
        $jobs_exc_ub_os = $this->figure_model->jGetNumJobsCompleted($params);

        // Upfront Bill and Other Supplier Only
        $params = array(
            'ub_os_only' => true,
            'exclude_dha' => true
        );
        $sales_ub_os_only = $this->figure_model->jGetSales($params);    
        
        // excluding job type = 'IC Upgrade'
        $params = array(
            'exc_ic_up' => true,
            'exclude_dha' => true
        );
        $sales_exc_ic_up = $this->figure_model->jGetSales($params);

        // job type = 'IC Upgrade' only
        $params = array(
            'ic_up_only' => true,
            'exclude_dha' => true
        );
        $sales_ic_up_only = $this->figure_model->jGetSales($params);

        // needs to send via json
        $arr = array(
            "sales" => $sales,
            "sales_ub_os_only" => $sales_ub_os_only,
            "sales_exc_ic_up" => $sales_exc_ic_up,
            "sales_ic_up_only" => $sales_ic_up_only,
            "techs" => $techs,
            "jobs" => $jobs,
            "jobs_exc_ub_os" => $jobs_exc_ub_os
        );
        echo json_encode($arr);
    }

    public function ajax_update_daily_figures_per_date() {

        $this->load->model('figure_model'); // Load model

        $dfpd_id = $this->input->get_post('dfpd_id');

        $working_day = $this->input->get_post('working_day');
        $date = $this->input->get_post('date');
        $techs = $this->input->get_post('techs');
        $jobs = $this->input->get_post('jobs');
        $jobs_exc_ub_os = $this->input->get_post('jobs_exc_ub_os');
        $sales = $this->input->get_post('sales');
        $sales_ub_os_only = $this->input->get_post('sales_ub_os_only');
        $sales_exc_ic_up = $this->input->get_post('sales_exc_ic_up');
        $sales_ic_up_only = $this->input->get_post('sales_ic_up_only');
        $country_id = $this->config->item('country');

        if ($dfpd_id != '') { // UPDATE
            $update_params = array(
                'date' => $date,
                'working_day' => $working_day,
                'techs' => $techs,
                'jobs' => $jobs,
                'jobs_exc_ub_os' => $jobs_exc_ub_os,
                'sales' => $sales,
                'sales_ub_os_only' => $sales_ub_os_only,
                'sales_exc_ic_up' => $sales_exc_ic_up,
                'sales_ic_up_only' => $sales_ic_up_only
            );
            $this->figure_model->update_daily_figures_per_date($dfpd_id, $update_params);
        } else { // ADD/INSERT
            $params = array(
                'date' => $date,
                'working_day' => $working_day,
                'techs' => $techs,
                'jobs' => $jobs,
                'jobs_exc_ub_os' => $jobs_exc_ub_os,
                'sales' => $sales,
                'sales_ub_os_only' => $sales_ub_os_only,
                'sales_exc_ic_up' => $sales_exc_ic_up,
                'sales_ic_up_only' => $sales_ic_up_only,
                'country_id' => $country_id
            );
            $this->figure_model->insert_daily_figures_per_date($params);
        }
    }

    //Update ajax_update_daily_figures
    public function ajax_update_daily_figures() {

        $this->load->model('figure_model'); // Load model

        $df_id = $this->input->get_post('df_id');
        $date = $this->input->get_post('date');
        $month = $this->input->get_post('month');
        $budget = $this->input->get_post('budget');
        $working_days = $this->input->get_post('working_days');
        $country_id = $this->config->item('country');

        if ($df_id != '') { // UPDATE
            $data = array(
                'budget' => $budget,
                'working_days' => $working_days
            );
            $this->figure_model->update_daily_figures($df_id, $data);
        } else { // daily_figure_per_date_id
            $data = array(
                'month' => $month,
                'budget' => $budget,
                'working_days' => $working_days,
                'country_id' => $country_id
            );
            $this->figure_model->insert_daily_figures($data);
        }
    }

    public function ajax_daily_figures_statistics() {

        $this->load->model('figure_model'); // Load figure model
        $this->load->model('jobs_model'); // Load jobs model

        $country_id = $this->config->item('country');
        $date = date('Y-m-d');
        $ic_service = $this->figure_model->getICService();
        $ic_service_imp = implode(',', $ic_service);

        $from = $this->input->post('from');
        $to = $this->input->post('to');
        $mtd_sales = $this->input->post('mtd_sales');
        $data['mtd_sales'] = $this->input->post('mtd_sales');
        $df_budget = $this->input->post('df_budget');
        $data['df_budget'] = $this->input->post('df_budget');
        $df_working_days = $this->input->post('df_working_days');
        $data['df_working_days'] = $this->input->post('df_working_days');


        //get country
        $country_query = $this->gherxlib->get_country_data()->row_array();
        $data['country'] = $country_query['country'];


        //TODAY
        $dfpd_sql = $this->figure_model->getDailyFiguresPerDate($date);
        $dfpd = $dfpd_sql->row_array();
        $data['sales'] = $dfpd['sales'];
        $data['jobs_exc_ub_os'] = $dfpd['jobs_exc_ub_os'];
        $data['techs'] = $dfpd['techs'];
        $data['jobs'] = $dfpd['jobs'];
        $data['sales_ub_os_only'] = $dfpd['sales_ub_os_only'];
        $data['sales_exc_ic_up'] = $dfpd['sales_exc_ic_up'];
        $data['sales_ic_up_only'] = $dfpd['sales_ic_up_only'];
        $data['working_day'] = $dfpd['working_day'];

        // Upfront Bill and Other Supplier Only job COUNT
        $params = array(
            'ub_os_only' => true,
            'return_count' => true,
            'exclude_dha' => true
        );
        $data['sales_ub_os_only_count'] = $this->figure_model->jGetSales($params); 

        // Upfront Bill and Other Supplier Only job COUNT
        $params = array(
            'ic_up_only' => true,
            'return_count' => true,
            'exclude_dha' => true
        );
        $data['sales_ic_up_only_count'] = $this->figure_model->jGetSales($params); 

        // Sales COUNT
        $params = array(            
            'return_count' => true,
            'exclude_dha' => true
        );
        $data['sales_count'] = $this->figure_model->jGetSales($params);


        // IC Upgrade Jobs
        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_filter = "
					j.`job_type` = 'IC Upgrade'
					AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )
					AND j.`date` = '" . date('Y-m-d') . "'
                    AND a.franchise_groups_id != 14
                    AND p.`is_sales` != 1
				";
        $job_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'custom_where' => $custom_filter
        );
        $job_query = $this->jobs_model->get_jobs($job_params);
        $data['total_upgrade_completed'] = $job_query->row()->jcount;

        // Sales Upgrade Jobs
        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_filter = "
					j.`job_type` = 'IC Upgrade'
					AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )
					AND j.`date` = '" . date('Y-m-d') . "'
                    AND a.franchise_groups_id != 14
                    AND p.`is_sales` = 1
				";
        $job_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'custom_where' => $custom_filter
        );
        $job_query = $this->jobs_model->get_jobs($job_params);
        $data['total_upgrade_completed_sales'] = $job_query->row()->jcount;

        //Total Jobs Completed
        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_filter = "
					 ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )
					AND j.`date` = '" . date('Y-m-d') . "'
                    AND a.franchise_groups_id != 14
                    AND p.`is_sales` != 1
                    AND j.assigned_tech != 1
				";
        $job_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'custom_where' => $custom_filter
        );
        $job_query = $this->jobs_model->get_jobs($job_params);
        $data['total_jobs_completed'] = $job_query->row()->jcount;
        //Total Jobs Completed End

        //Total Upgrade Income
        $params = array(
            'ic_service' => $ic_service
        );
        $up_sql = $this->figure_model->getIcUpgradeTotal($params);
        $up = $up_sql->row_array();
        $data['up_tot'] = ($up['job_price'] + $up['alarm_tot'] + $up['am_price']);


        //Total Upgrades Completed MONTH TO DATE
        $sel_query = "COUNT(j.`id`) AS jcount";

        $custom_filter = "
			j.`job_type` = 'IC Upgrade'
			AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )
		";
        $date_now = date('Y-m-01');
        $date_range_filter = "j.`date` BETWEEN '{$date_now}' AND '{$to}'";
        $exclude_dha = "a.franchise_groups_id!=14";

        $job_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'custom_where_arr' => array($custom_filter, $date_range_filter, $exclude_dha)
        );
        $job_query2 = $this->jobs_model->get_jobs($job_params);
        $data['total_upgrade_completed2'] = $job_query2->row()->jcount;

        //Total Upgrade Income MONTH TO DATE
        $params_a = array(
            'ic_service' => $ic_service,
            'date_range' => array(
                'from' => date('Y-m-01'),
                'to' => $to
            )
        );
        $up_sql2 = $this->figure_model->getIcUpgradeTotal($params_a);
        $up2 = $up_sql2->row_array();
        $data['up_tot2'] = ($up2['job_price'] + $up2['alarm_tot'] + $up2['am_price']);


        //Booked Jobs until EOM
        $date_eom = date('Y-m-d');
        $date_range_filter2 = "j.`date` BETWEEN '{$date_eom}' AND '{$to}'";
        $eom_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'job_status' => 'Booked',
            'custom_where_arr' => array($date_range_filter2, $exclude_dha)
        );
        $eom_query = $this->jobs_model->get_jobs($eom_params);
        $data['eom_booked'] = $eom_query->row()->jcount;



        // sum age
        $custom_select_1 = "
					SUM( DATEDIFF( j.`date`, CAST( j.`created` AS DATE ) ) ) AS sum_completed_age,
					COUNT(j.`id`) AS jcount
				";
        // completed status
        $custom_filter_2 = "
			( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' ) AND `assigned_tech` NOT IN(1,2)
		";
        $date_range_filter_tt = "j.`date` BETWEEN '{$from}' AND '{$to}'";
        $sa_params = array(
            'sel_query' => $custom_select_1,
            'custom_where_arr' => array($custom_filter_2, $date_range_filter_tt, $exclude_dha),
            'country_id' => $this->config->item('country'),
            'p_deleted' => 0,
            'del_job' => 0,
            'a_status' => 'active',
            'a_status' => 'active'
        );
        $sa_sql = $this->jobs_model->get_jobs($sa_params);
        $sa = $sa_sql->row_array();
        $sum_completed_age = $sa['sum_completed_age'];
        $jcount = $sa['jcount'];
        $data['average_completed'] = (is_numeric(number_format(($sum_completed_age / $jcount)))) ? number_format(($sum_completed_age / $jcount), 2, '.', '') . ' days' : '0 days';

        //Total Jobs Completed Month to Date
        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_filter_total_jobs_completed_mont_to_date = "
                    ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )
                    AND j.`date` BETWEEN '{$from}' AND '{$to}'
                    AND a.franchise_groups_id != 14
                    AND p.`is_sales` != 1
                ";
        $job_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'custom_where' => $custom_filter_total_jobs_completed_mont_to_date
        );
        $job_query = $this->jobs_model->get_jobs($job_params);
        $data['total_jobs_completed_mont_to_date'] = $job_query->row()->jcount;
        //Total Jobs Completed Month to Date End

        //STATISTICS
        //outstanding jobs
        $os_jobs_filter = "
		(
			j.`status` != 'On Hold' AND
			j.`status` != 'Pending' AND
			j.`status` != 'Completed' AND
			j.`status` != 'Cancelled' AND
			j.`status` != 'Booked'
        )
        AND CAST(j.`created` AS Date) <= '".date('Y-m-d')."'
		";
        $os_jobs_params = array(
            'sel_query' => "COUNT(j.`id`) AS jcount",
            'custom_where_arr' => array($os_jobs_filter, $exclude_dha),
            'p_deleted' => 0,
            'del_job' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
        );
        $data['os_jobs'] = $this->jobs_model->get_jobs($os_jobs_params)->row_array();

        //outstanding value
        $ov_jobs_params = array(
            'sel_query' => "SUM( j.`job_price` ) AS j_price ",
            'custom_where_arr' => array($os_jobs_filter, $exclude_dha),
            'p_deleted' => 0,
            'del_job' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
        );
        $data['ov_jobs'] = $this->jobs_model->get_jobs($ov_jobs_params)->row_array();

        //average age (not completed)
        $aanc_jobs_sel = "
					SUM( DATEDIFF( '" . date('Y-m-d') . "', CAST( j.`created` AS DATE ) ) ) AS sum_age,
					COUNT(j.`id`) AS jcount
				";
        $aanc_jobs_params = array(
            'sel_query' => $aanc_jobs_sel,
            'custom_where_arr' => array($os_jobs_filter, $exclude_dha),
            'p_deleted' => 0,
            'del_job' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
        );
        $aanc_jobs = $this->jobs_model->get_jobs($aanc_jobs_params)->row_array();
        $data['aanc_sum_age'] = $aanc_jobs['sum_age'];
        $data['aanc_jcount'] = $aanc_jobs['jcount'];

        //ACCOUNTS
        //total payments
//        $acc_sql = $this->figure_model->getTotalPaymentsAndCredits();
//        $acc = $acc_sql->row_array();
        $acc = $this->figure_model->getTotalPaymentsAndCredits();
        $data['tot_payments'] = $acc['inv_pay_tot'];
        $data['tot_credits'] = $acc['inv_cred_tot'];

        $this->load->view('/reports/ajax_daily_figures_statistics_table_info', $data);
        $this->load->view('/reports/ajax_daily_figures_statistics_table_info_v2', $data);


    }

    public function figures() {

        $this->load->model('figure_model'); // Load model

        $data['months_arr'] = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July ',
            'August',
            'September',
            'October',
            'November',
            'December'
        );

        //fetch figures
        $per_page = 25;
        $offset = $this->input->get_post('offset');
        $fig_params = array(
            'sel_query' => "*",
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'year',
                    'sort' => 'DESC'
                ),
                array(
                    'order_by' => 'month',
                    'sort' => 'DESC'
                )
            )
        );
        $data['figure_sql'] = $this->figure_model->get_figures($fig_params);
        $data['country'] = $this->config->item('country');

        // total rows
        $total_sel_query = "COUNT('figures_id') as f_count";
        $total_params = array(
            'sel_query' => $total_sel_query
        );
        $query = $this->figure_model->get_figures($total_params);
        $total_rows = $query->row()->f_count;

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = "/reports/figures";

        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['title'] = "Figures";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/figures', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * DELETE FIGURES
     */
    public function ajax_delete_figures() {

        $figures_id = $this->input->post('figures_id');

        if ($figures_id && !empty($figures_id) && is_numeric($figures_id)) {

            $this->db->where('figures_id', $figures_id);
            $this->db->delete('figures');
        }
    }

    /**
     * EDIT/UPDATE FIGURES
     */
    public function ajax_update_figures() {

        $figures_id = $this->input->post('figures_id');

        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $working_days = ($this->input->post('working_days') != '') ? $this->input->post('working_days') : NULL;

        $p_actual = ($this->input->post('p_actual') != '') ? $this->input->post('p_actual') : NULL;
        $p_last_month = ($this->input->post('p_last_month')) ? $this->input->post('p_last_month') : NULL;
        $ym = ($this->input->post('ym') != '') ? $this->input->post('ym') : NULL;
        $of = ($this->input->post('of') != '') ? $this->input->post('of') : NULL;
        $cot = ($this->input->post('cot') != '') ? $this->input->post('cot') : NULL;
        $lr = ($this->input->post('lr') != '') ? $this->input->post('lr') : NULL;
        $fr = ($this->input->post('fr') != '') ? $this->input->post('fr') : NULL;
        $upgrades = ($this->input->post('upgrades') != '') ? $this->input->post('upgrades') : NULL;
        $upgrades_income = ($this->input->post('upgrades_income') != '') ? $this->input->post('upgrades_income') : NULL;
        $jobs_not_comp = ($this->input->post('jobs_not_comp') != '') ? $this->input->post('jobs_not_comp') : NULL;

        $new_sales = ($this->input->post('new_sales') != '') ? $this->input->post('new_sales') : NULL;
        $renewals = ($this->input->post('renewals') != '') ? $this->input->post('renewals') : NULL;
        $lost = ($this->input->post('lost') != '') ? $this->input->post('lost') : NULL;
        $budget = ($this->input->post('budget') != '') ? $this->input->post('budget') : NULL;
        $actual = ($this->input->post('actual') != '') ? $this->input->post('actual') : NULL;
        $prev_year = ($this->input->post('prev_year') != '') ? $this->input->post('prev_year') : NULL;
        $techs = ($this->input->post('techs') != '') ? $this->input->post('techs') : NULL;
        $annual = ($this->input->post('annual') != '') ? $this->input->post('annual') : NULL;
        $upfronts = ($this->input->post('upfronts') != '') ? $this->input->post('upfronts') : NULL;
        $rebook_240v = ($this->input->post('rebook_240v') != '') ? $this->input->post('rebook_240v') : NULL;

        if ($figures_id && !empty($figures_id) && is_numeric($figures_id)) {

            $data = array(
                'month' => $month,
                'year' => $year,
                'working_days' => $working_days,
                'p_actual' => $p_actual,
                'p_last_month' => $p_last_month,
                'ym' => $ym,
                'of' => $of,
                'cot' => $cot,
                'lr' => $lr,
                'fr' => $fr,
                'upgrades' => $upgrades,
                'upgrades_income' => $upgrades_income,
                'annual' => $annual,
                'jobs_not_comp' => $jobs_not_comp,
                'new_sales' => $new_sales,
                'renewals' => $renewals,
                'lost' => $lost,
                'budget' => $budget,
                'actual' => $actual,
                'prev_year' => $prev_year,
                'techs' => $techs,
                'upfronts' => $upfronts,
                '240v_rebook' => $rebook_240v
            );
            $this->db->where('figures_id', $figures_id);
            $this->db->update('figures', $data);
            $this->db->limit(1);
        }
    }

    /**
     * ADD FIGURES
     */
    public function add_figures() {

        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $working_days = ($this->input->post('working_days') != '') ? $this->input->post('working_days') : NULL;

        $p_actual = ($this->input->post('p_actual') != '') ? $this->input->post('p_actual') : NULL;
        $p_last_month = ($this->input->post('p_last_month') != '') ? $this->input->post('p_last_month') : NULL;

        $ym = ($this->input->post('ym') != '') ? $this->input->post('ym') : NULL;
        $of = ($this->input->post('of') != '') ? $this->input->post('of') : NULL;
        $cot = ($this->input->post('cot') != '') ? $this->input->post('cot') : NULL;
        $lr = ($this->input->post('lr') != '') ? $this->input->post('lr') : NULL;
        $fr = ($this->input->post('fr') != '') ? $this->input->post('fr') : NULL;
        $upgrades = ($this->input->post('upgrades') != '') ? $this->input->post('upgrades') : NULL;
        $upgrades_income = ($this->input->post('upgrades_income') != '') ? $this->input->post('upgrades_income') : NULL;
        $jobs_not_comp = ($this->input->post('jobs_not_comp') != '') ? $this->input->post('jobs_not_comp') : NULL;


        $new_sales = ($this->input->post('new_sales') != '') ? $this->input->post('new_sales') : NULL;
        $renewals = ($this->input->post('renewals') != '') ? $this->input->post('renewals') : NULL;
        $lost = ($this->input->post('lost') != '') ? $this->input->post('lost') : NULL;
        $budget = ($this->input->post('budget') != '') ? $this->input->post('budget') : NULL;
        $actual = ($this->input->post('actual') != '') ? $this->input->post('actual') : NULL;
        $prev_year = ($this->input->post('prev_year') != '') ? $this->input->post('prev_year') : NULL;
        $techs = ($this->input->post('techs') != '') ? $this->input->post('techs') : NULL;
        $annual = ($this->input->post('annual') != '') ? $this->input->post('annual') : NULL;
        $upfronts = ($this->input->post('upfronts') != '') ? $this->input->post('upfronts') : NULL;
        $rebook = ($this->input->post('240v_rebook') != '') ? $this->input->post('240v_rebook') : NULL;

        if (!empty($month) && !empty($year)) {

            $data = array(
                'month' => $month,
                'year' => $year,
                'working_days' => $working_days,
                'p_actual' => $p_actual,
                'p_last_month' => $p_last_month,
                'ym' => $ym,
                'of' => $of,
                'cot' => $cot,
                'lr' => $lr,
                'fr' => $fr,
                'upgrades' => $upgrades,
                'upgrades_income' => $upgrades_income,
                'jobs_not_comp' => $jobs_not_comp,
                'annual' => $annual,
                'upfronts' => $upfronts,
                '240v_rebook' => $rebook,
                'new_sales' => $new_sales,
                'renewals' => $renewals,
                'lost' => $lost,
                'budget' => $budget,
                'actual' => $actual,
                'prev_year' => $prev_year,
                'techs' => $techs,
                'date_created' => date('Y-m-d H:i:s'),
                'active' => 1,
                'deleted' => 0,
                'country_id' => $this->config->item('country')
            );
            $this->db->insert('figures', $data);
            $this->db->limit(1);

            if ($this->db->affected_rows() > 0) {
                //set success session data
                $success_message = "New Data has been Added!";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url('/reports/figures'));
            } else {
                //set error session data
                $error_message = "Server Error Contact Admin!";
                $this->session->set_flashdata(array('error_msg' => $error_message, 'status' => 'error'));
                redirect(base_url('/reports/figures'));
            }
        }
    }

    public function dirty_address() {

        $page_url = '/reports/dirty_address/';

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $params = array(
            'sel_query' => "p.property_id, p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.deleted, p.agency_id as a_id, a.agency_name, aght.priority",
            'limit' => $per_page,
            'offset' => $offset,
            'search' => $this->input->post('search')
        );
        $dirty_address_query = $this->reports_model->get_dirty_address($params);
        $data['lists'] = $dirty_address_query;

        // Total rows
        $total_sel_query = "COUNT('property_id') as p_count";
        $total_params = array(
            'sel_query' => $total_sel_query,
            'search' => $this->input->post('search')
        );
        $query = $this->reports_model->get_dirty_address($total_params);
        $total_rows = $query->row()->p_count;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        $pagi_links_params_arr = array(
            'search' => $this->input->get_post('search')
        );
        $pagi_link_params = '/reports/dirty_address/?' . http_build_query($pagi_links_params_arr);

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



        $data['title'] = "Dirty Address";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/dirty_address', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view_crm_tasks() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "CRM Support Ticket";

// pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'ael.`eventdate`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
        // use array incase they add more people
        $vip = array(2025); // sir dan
        $vip2 = array(2025, 11);
//$vip = array(11); // ness

        $date = $this->input->get_post('date');
        $date2 = ($date != "") ? $this->system_model->formatDate($date) : '';
        $created_by = $this->input->get_post('created_by');
        //$status = ($this->input->get_post('status')) ? $this->input->get_post('status') : 1;

        $status = $this->input->get_post('status');
        $help_topic = $this->input->get_post('help_topic');
        $ticket_priority = $this->input->get_post('ticket_priority');

        $assigned = $this->input->get_post('assigned');

        $date_filter_type = $this->input->get_post('date_filter_type');
        $date_from = ( $this->input->get_post('date_from') !='' )?$this->system_model->formatDate($this->input->get_post('date_from')):null;
        $date_to = ( $this->input->get_post('date_to') !='' )?$this->system_model->formatDate($this->input->get_post('date_to')):null;

        $phrase_filter = $this->input->get_post('phrase_filter');

        $custom_where_arr = [];
        if( $status != 'all' ){

            if( $status > 0 ){
                $custom_where_arr[] = "ct.`status` = {$status}";
            }else{ // default      
                if ($date_filter_type > 0) {
                    # code...
                } else {
                    if (!empty($phrase_filter)) {
                        // $custom_where_arr[] = "(cts.status LIKE '%{$phrase_filter}%' OR rb.FirstName LIKE '%{$phrase_filter}%' OR rb.LastName LIKE '%{$phrase_filter}%') AND ct.`status` NOT IN(4,2,7)";
                        $custom_where_arr[] = "(ct.issue_summary LIKE '%{$phrase_filter}%' OR ct.describe_issue LIKE '%{$phrase_filter}%')";
                    } else {
                        $custom_where_arr[] = "ct.`status` NOT IN(4,2,7)"; // exclude Completed, Declined and Unable to Replicate
                    }
                    
                }     
            }

        }   

        //if the status is completed order it by completed_ts asc
        if ($status == 4) {
            $date_order = 'ct.completed_ts';
            $sort_order = 'DESC';
        } else {
            $date_order = 'ct.date_created';
            $sort_order = 'ASC';
        }

        if ($help_topic != '') {
            $custom_where_arr[] = "ct.`help_topic` = {$help_topic}";
        }
        
        $date_to_str = ( $this->input->get_post('date_to') != '' )?$date_to:$date_from;

        if( $date_filter_type == 1 && $this->input->get_post('date_from') != '' ){ // Created date            

            $custom_where_arr[] = "Date(ct.`date_created`) BETWEEN '{$date_from}' AND '{$date_to_str}'";
            
        }else if( $date_filter_type == 2 && $this->input->get_post('date_from') != '' ){ // Last updated

            $custom_where_arr[] = "Date(ct.`last_updated_ts`) BETWEEN '{$date_from}' AND '{$date_to_str}'";
            
        }else if( $date_filter_type == 3 && $this->input->get_post('date_from') != '' ){ // Completed

            $custom_where_arr[] = "Date(ct.`completed_ts`) BETWEEN '{$date_from}' AND '{$date_to_str}'";
            
        }
        
        // select
        $custom_select = '
        ct.`crm_task_id`,
        ct.`date_created`,
        ct.`page_link`,
        ct.`describe_issue`,
        ct.`response`,
        ct.`status` AS ct_status,
        ct.`issue_summary`,
        ct.`help_topic`,
        ct.`ticket_priority`,
        ct.`completed_ts`,
        ct.`last_updated_ts`,

        ctht.`help_topic` AS ctht_help_topic,
        
        cts.`status` AS cts_status,
        cts.`hex`,

        rb.`StaffID`,
        rb.`FirstName`,
        rb.`LastName`,
        ';

        $params = array(
            'custom_select' => $custom_select,
            'assigned' => $assigned, 
            'active' => 1,            
            'user' => $created_by,            
            'ticket_priority' => $ticket_priority,

            'custom_where_arr' => $custom_where_arr,

            'sort_list' => array(
                array(
                    'order_by' => $date_order,
                    'sort' => $sort_order
                )
            ),
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'group_by' => 'ct.`crm_task_id`',
            'echo_query' => 0
        );
        $plist = $this->crmtasks_model->getButtonCrmTasks($params);               
        $data['tasks'] = $plist->result_array();
        $data['sql_query'] = $this->db->last_query(); //Show query on About
        
        // echo "<pre>";
        // var_dump($data['task']);
        // die();
        
        $data['logged_user'] = $this->session->staff_id;
        $data['vip'] = $vip;
        $data['vip2'] = $vip2;
        $count_params = array(
            'active' => 1,            
            'user' => $created_by,
            'ticket_priority' => $ticket_priority,
            
            'custom_where_arr' => $custom_where_arr,

            'return_count' => 1
        );
        $ptotal = $this->crmtasks_model->getButtonCrmTasks($count_params);

        $total_rows = $ptotal;
        $data['sort_list'] = $total_rows;
        // base url
        $base_url = '/reports/view_crm_tasks/';


        // get staff accounts        
        $sel_query = '
            sa.`StaffID`,
            sa.`FirstName`,
            sa.`LastName`
        ';
        $params = array( 
            'sel_query' => $sel_query,                        
            'active' => 1,
            'deleted' => 0,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
				),
				array(
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC',
                ),
            ),            
            'display_query' => 0
        );
        
        // get user details
        $data['staff_sql'] = $this->staff_accounts_model->get_staff_accounts($params);

        // get subscribed managers
        $data['crm_task_managers_sql'] = $this->db->query("
        SELECT *
        FROM `crm_task_managers` AS ctm
        INNER JOIN `staff_accounts` AS sa ON ctm.`staff_id` = sa.`StaffID`
        WHERE ctm.`active` = 1
        AND sa.`active` = 1
        AND sa.`Deleted` = 0
        ");

        // get ticket status
        $data['crm_task_status_sql'] = $this->db->query("
        SELECT *
        FROM `crm_task_status` 
        WHERE `active` = 1
        ORDER BY `status` ASC
        ");

        // get help topic
        $data['crm_task_help_topic_sql'] = $this->db->query("
        SELECT *
        FROM `crm_task_help_topic` 
        WHERE `active` = 1
        ORDER BY `help_topic` ASC
        ");

        $data['assigned_dev'] = $this->db->query("
        SELECT DISTINCT
            sa.`StaffID`,
            sa.`FirstName`,
            sa.`LastName`
        FROM `crm_task_details_devs` AS ctd_dev
        INNER JOIN `staff_accounts` AS sa ON ctd_dev.`dev_id` = sa.`StaffID`
        INNER JOIN `crm_tasks` AS ct ON ctd_dev.`ticket_id` = ct.`crm_task_id`
        WHERE ctd_dev.`active` = 1
        AND sa.`active` = 1
        AND sa.`Deleted` = 0
        ");

        // echo "<pre>";
        // var_dump($data['assigned_dev']->result());
        // die();
        // DISTINCT create by
        $custom_select = 'DISTINCT(rb.`StaffID`), rb.`FirstName`, rb.`LastName`';
        $params = array(
            'custom_select' => $custom_select,
            'active' => 1,                              
            'ticket_priority' => $ticket_priority,

            'custom_where_arr' => $custom_where_arr,

            'sort_list' => array(
                array(
                    'order_by' => 'rb.`FirstName`',
                    'sort' => 'ASC'
                ),
                array(
                    'order_by' => 'rb.`LastName`',
                    'sort' => 'ASC'
                )
            ),
            'echo_query' => 0
        );
        $data['distinct_created_by_sql'] = $this->crmtasks_model->getButtonCrmTasks($params);  

        $pagi_links_params_arr = array(
            'ticket_priority' => $ticket_priority,
            'status' => $status,
            'help_topic' => $help_topic,
            'created_by' => $created_by,
            'date_filter_type' => $date_filter_type,
            'date_from' => $date_from,
            'date_to' => $date_to
        );
        $pagi_link_params = '/reports/view_crm_tasks/?'.http_build_query($pagi_links_params_arr);
        $data['pagi_links_params_arr'] = $pagi_links_params_arr;

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
        $data = array_merge($data, $_POST, $_GET);
        $data['status'] = $status;
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/crm_tasks/view_crm_tasks', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view_complaints() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "CRM Complaints";

// pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'ael.`eventdate`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
        // use array incase they add more people
        $vip = array(2025); // sir dan
        $vip2 = array(2025, 11);
//$vip = array(11); // ness

        $date = $this->input->get_post('date');
        $date2 = ($date != "") ? $this->system_model->formatDate($date) : '';
        $created_by = $this->input->get_post('created_by');
        //$status = ($this->input->get_post('status')) ? $this->input->get_post('status') : 1;

        $status = $this->input->get_post('status');
        $help_topic = $this->input->get_post('help_topic');
        $ticket_priority = $this->input->get_post('ticket_priority');

        $assigned = $this->input->get_post('assigned');

        $date_filter_type = $this->input->get_post('date_filter_type');
        $date_from = ( $this->input->get_post('date_from') !='' )?$this->system_model->formatDate($this->input->get_post('date_from')):null;
        $date_to = ( $this->input->get_post('date_to') !='' )?$this->system_model->formatDate($this->input->get_post('date_to')):null;

        $custom_where_arr = [];
        if( $status != 'all' ){

            if( $status > 0 ){
                $custom_where_arr[] = "comp.`status` = {$status}";
            }else{ // default                
                $custom_where_arr[] = "comp.`status` NOT IN(4,2,7)"; // exclude Completed, Declined and Unable to Replicate
            }

        }   

        //if the status is completed order it by completed_ts asc
        if ($status == 4) {
            $date_order = 'comp.completed_ts';
            $sort_order = 'DESC';
        } else {
            $date_order = 'comp.date_created';
            $sort_order = 'ASC';
        }

        if ($help_topic != '') {
            $custom_where_arr[] = "comp.`comp_topic` = {$help_topic}";
        }
        
        $date_to_str = ( $this->input->get_post('date_to') != '' )?$date_to:$date_from;

        if( $date_filter_type == 1 && $this->input->get_post('date_from') != '' ){ // Created date            

            $custom_where_arr[] = "Date(comp.`date_created`) BETWEEN '{$date_from}' AND '{$date_to_str}'";
            
        }else if( $date_filter_type == 2 && $this->input->get_post('date_from') != '' ){ // Last updated

            $custom_where_arr[] = "Date(comp.`last_updated_ts`) BETWEEN '{$date_from}' AND '{$date_to_str}'";
            
        }else if( $date_filter_type == 3 && $this->input->get_post('date_from') != '' ){ // Completed

            $custom_where_arr[] = "Date(comp.`completed_ts`) BETWEEN '{$date_from}' AND '{$date_to_str}'";
            
        }

        // select
        $custom_select = '
        comp.`comp_id`,        
        comp.`date_created`,
        comp.`page_link`,
        comp.`describe_issue`,
        comp.`response`,
        comp.`status` AS ct_status,
        comp.`issue_summary`,
        comp.`comp_topic`,
        comp.`ticket_priority`,
        comp.`completed_ts`,
        comp.`last_updated_ts`,

        ct.`comp_topic` as comp_topic_details,
        
        cs.`status` AS cts_status,
        cs.`hex`,

        rb.`StaffID`,
        rb.`FirstName`,
        rb.`LastName`,
        ';

        $params = array(
            'custom_select' => $custom_select,
            'assigned' => $assigned, 
            'active' => 1,            
            'user' => $created_by,            
            'ticket_priority' => $ticket_priority,

            'custom_where_arr' => $custom_where_arr,

            'sort_list' => array(
                array(
                    'order_by' => $date_order,
                    'sort' => $sort_order
                )
            ),
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'group_by' => 'comp.`comp_id`',
            'echo_query' => 0
        );
        $plist = $this->crmtasks_model->getComplaints($params);               
        $data['tasks'] = $plist->result_array();
        $data['sql_query'] = $this->db->last_query(); //Show query on About
        
        // echo "<pre>";
        // var_dump($data['task']);
        // die();
        
        $data['logged_user'] = $this->session->staff_id;
        $data['vip'] = $vip;
        $data['vip2'] = $vip2;
        $count_params = array(
            'active' => 1,            
            'user' => $created_by,
            'ticket_priority' => $ticket_priority,
            
            'custom_where_arr' => $custom_where_arr,

            'return_count' => 1
        );
        $ptotal = $this->crmtasks_model->getComplaints($count_params);

        $total_rows = $ptotal;
        $data['sort_list'] = $total_rows;
        // base url
        $base_url = '/reports/view_complaints/';


        // get staff accounts        
        $sel_query = '
            sa.`StaffID`,
            sa.`FirstName`,
            sa.`LastName`
        ';
        $params = array( 
            'sel_query' => $sel_query,                        
            'active' => 1,
            'deleted' => 0,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
				),
				array(
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC',
                ),
            ),            
            'display_query' => 0
        );
        
        // get user details
        $data['staff_sql'] = $this->staff_accounts_model->get_staff_accounts($params);

        // get help topic
        $data['complaints_topic_sql'] = $this->db->query("
        SELECT *
        FROM `complaints_topic` 
        WHERE `active` = 1
        ORDER BY `comp_topic` ASC
        ");

        // get ticket status
        $data['complaints_status_sql'] = $this->db->query("
        SELECT *
        FROM `complaints_status` 
        WHERE `active` = 1
        ORDER BY `status` ASC
        ");

        $custom_select = 'DISTINCT(rb.`StaffID`), rb.`FirstName`, rb.`LastName`';
        $params = array(
            'custom_select' => $custom_select,
            'active' => 1,                              
            'ticket_priority' => $ticket_priority,

            'custom_where_arr' => $custom_where_arr,

            'sort_list' => array(
                array(
                    'order_by' => 'rb.`FirstName`',
                    'sort' => 'ASC'
                ),
                array(
                    'order_by' => 'rb.`LastName`',
                    'sort' => 'ASC'
                )
            ),
            'echo_query' => 0
        );
        $data['distinct_created_by_sql'] = $this->crmtasks_model->getComplaints($params);  

        $pagi_links_params_arr = array(
            'ticket_priority' => $ticket_priority,
            'status' => $status,
            'help_topic' => $help_topic,
            'created_by' => $created_by,
            'date_filter_type' => $date_filter_type,
            'date_from' => $date_from,
            'date_to' => $date_to
        );
        $pagi_link_params = '/reports/view_complaints/?'.http_build_query($pagi_links_params_arr);
        $data['pagi_links_params_arr'] = $pagi_links_params_arr;

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
        $data = array_merge($data, $_POST, $_GET);
        $data['status'] = $status;
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/view_complaints', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    function get_managers_complaints() {
        
        // get managers temp
        $user_id = $this->session->staff_id;
        $complaints_assign_temp_sql = $this->db->query("
        SELECT *
        FROM `complaints_assign_to_temp` as cat
        INNER JOIN `staff_accounts` AS sa ON cat.`staff_id` = sa.`StaffID`
        WHERE sa.`active` = 1
        AND sa.`Deleted` = 0
        AND cat.`user_id` = $user_id
        ");
        foreach( $complaints_assign_temp_sql->result() as $assign_temp_row ){ 

            $managers_full_name = $this->system_model->formatStaffName($assign_temp_row->FirstName, $assign_temp_row->LastName);
            echo '
            <label type="button" class="label label-success subscribe-btn btn-inline subscribed_manager remove_manager_btn"  data-subcribed_staff_id="'.$assign_temp_row->StaffID.'">'.$managers_full_name.' </label>  
            ';
        }
    }

    function get_agency_complaints() {
        
        // get managers temp
        $user_id = $this->session->staff_id;
        $complaints_assign_temp_sql = $this->db->query("
        SELECT a.agency_name
        FROM `complaints_agency_temp` as cat
        INNER JOIN `agency` AS a ON cat.`agency_id` = a.`agency_id`
        WHERE cat.`user_id` = $user_id
        ");
        foreach( $complaints_assign_temp_sql->result() as $assign_temp_row ){ 

            // $managers_full_name = $this->system_model->formatStaffName($assign_temp_row->FirstName, $assign_temp_row->LastName);
            echo '
            <label type="button" class="label label-success subscribe-btn btn-inline subscribed_manager remove_manager_btn" >'.$assign_temp_row->agency_name.' </label>  
            ';
        }
    }

    function remove_managers_complaints() {
        
        // remove managers temp
        $this->db->where("`user_id` = {$this->session->staff_id}");
        $this->db->delete("`complaints_assign_to_temp`");

        // remove agency temp
        $this->db->where("`user_id` = {$this->session->staff_id}");
        $this->db->delete("`complaints_agency_temp`");
    }

    public function ticket_details() {

        $id = $this->input->get_post('id');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "CRM Support Ticket Details";
        $uri = "/reports/ticket_details/?id={$id}";
        $data['uri'] = $uri;        

        if( $id > 0 ){

            $sql_str = "
            SELECT *
            FROM `crm_tasks`
            WHERE `crm_task_id` = {$id}
            ORDER BY `date_created` DESC
            ";
            $crm_task_sql = $this->db->query($sql_str);
            $data['crm_task_row'] = $crm_task_sql->row();

            // get crm task data
            $data['crm_tasks_log_sql'] = $this->db->query("
            SELECT *
            FROM `crm_tasks_log`
            WHERE `ticket_id` = {$id}
            ORDER BY `created` DESC
            ");     
            
            // get subscribed managers
            $data['crm_task_details_sub_users_sql'] = $this->db->query("
            SELECT *
            FROM `crm_task_details_sub_users` AS ctdsu
            INNER JOIN `staff_accounts` AS sa ON ctdsu.`staff_id` = sa.`StaffID`
            WHERE ctdsu.`active` = 1
            AND sa.`active` = 1
            AND sa.`Deleted` = 0
            AND ctdsu.`ticket_id` = {$id}            
            ");

            // get developers assigned on this task
            $data['crm_task_dev_sql'] = $this->db->query("
            SELECT *
            FROM `crm_task_details_devs` AS ctd_dev
            INNER JOIN `staff_accounts` AS sa ON ctd_dev.`dev_id` = sa.`StaffID`
            WHERE ctd_dev.`active` = 1
            AND sa.`active` = 1
            AND sa.`Deleted` = 0
            AND ctd_dev.`ticket_id` = {$id}            
            ");

            // get ticket status
            $data['crm_task_status_sql'] = $this->db->query("
            SELECT *
            FROM `crm_task_status` 
            WHERE `active` = 1
            ORDER BY `index_sort` ASC
            ");

            // get help topic
            $data['crm_task_help_topic_sql'] = $this->db->query("
            SELECT *
            FROM `crm_task_help_topic` 
            WHERE `active` = 1
            ORDER BY `help_topic` ASC
            ");

            $this->load->view('templates/inner_header', $data);
            $this->load->view('reports/crm_tasks/ticket_details', $data);
            $this->load->view('templates/inner_footer', $data);

        }        

    }

    public function complaints_details() {

        $id = $this->input->get_post('id');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Complaints Ticket Details";
        $uri = "/reports/complaints_details/?id={$id}";
        $data['uri'] = $uri;        

        if( $id > 0 ){

            $sql_str = "
            SELECT *
            FROM `complaints`
            WHERE `comp_id` = {$id}
            ORDER BY `date_created` DESC
            ";
            $crm_task_sql = $this->db->query($sql_str);
            $data['complaints_row'] = $crm_task_sql->row();

            // get crm task data
            $data['complaints_log_sql'] = $this->db->query("
            SELECT *
            FROM `complaints_log`
            WHERE `comp_id` = {$id}
            ORDER BY `created` DESC
            ");     

            // // get manager assigned on this task
            $data['managers_sql'] = $this->db->query("
            SELECT *
            FROM `complaints_assign_to` AS cat
            INNER JOIN `staff_accounts` AS sa ON cat.`staff_id` = sa.`StaffID`
            WHERE sa.`active` = 1
            AND sa.`Deleted` = 0
            AND cat.`comp_id` = {$id}            
            ");

            $data['agency_sql'] = $this->db->query("
            SELECT ca.id, a.agency_name, a.agency_id
            FROM `complaints_agency` AS ca
            INNER JOIN `agency` AS a ON ca.`agency_id` = a.`agency_id`
            WHERE ca.`comp_id` = {$id}            
            ");

            // get ticket status
            $data['complaints_status_sql'] = $this->db->query("
            SELECT *
            FROM `complaints_status` 
            WHERE `active` = 1
            ORDER BY `index_sort` ASC
            ");

            // get help topic
            $data['complaints_topic_sql'] = $this->db->query("
            SELECT *
            FROM `complaints_topic` 
            WHERE `active` = 1
            ORDER BY `comp_topic` ASC
            ");

            $this->load->view('templates/inner_header', $data);
            $this->load->view('reports/complaints_details', $data);
            $this->load->view('templates/inner_footer', $data);

        }        

    }

    public function insert_crm_task_logs($crm_task_id,$crm_task_log_text){

        $logged_user_id = $this->session->staff_id;

        if( $crm_task_id > 0 && $crm_task_log_text != '' ){

            // insert logs
            $data = array(
                'ticket_id' => $crm_task_id,
                'log_text' => $crm_task_log_text,
                'created_by' => $logged_user_id
            );
            
            $this->db->insert('crm_tasks_log', $data);

        } 

    }
    
    public function insert_complaints_logs($comp_id,$complaints_log_text){

        $logged_user_id = $this->session->staff_id;

        if( $comp_id > 0 && $complaints_log_text != '' ){

            // insert logs
            $data = array(
                'comp_id' => $comp_id,
                'log_text' => $complaints_log_text,
                'created_by' => $logged_user_id
            );
            
            $this->db->insert('complaints_log', $data);

        } 

    }

    public function ticket_response(){

        $issue_summary = $this->input->get_post('issue_summary');
        $help_topic = $this->input->get_post('help_topic');
        $ticket_priority = $this->input->get_post('ticket_priority');
        $page_link = $this->input->get_post('page_link');
        $describe_issue = $this->input->get_post('describe_issue');
        $crm_task_id = $this->input->get_post('crm_task_id');        
        $response = $this->input->get_post('response');
        $status = $this->input->get_post('status');
        $logged_user_id = $this->session->staff_id;
        $today_full = date('Y-m-d H:i:s');
        $crm_task_log_text = null;

        $logged_user_row = $this->gherxlib->getStaffInfo(['staff_id' => $logged_user_id])->row_array();
        $logged_user_full_name = $this->system_model->formatStaffName($logged_user_row['FirstName'], $logged_user_row['LastName']);
        $crm_ticket_link = base_url(). "reports/ticket_details/?id=".$crm_task_id;
    
        if( $crm_task_id > 0 ){

            // get crm task data
            $crm_task_sql = $this->db->query("
            SELECT 
                ct.`issue_summary`,
                ct.`help_topic`,
                ct.`describe_issue`,
                ct.`ticket_priority`,
                ct.`page_link`,
                ct.`status` AS ct_status,
                ct.`response`,
                ct.`screenshot`,
                ct.`requested_by`,
                ct.`last_updated_by`,

                ctht.`help_topic` AS ctht_help_topic,  

                cts.`status` AS cts_status,
                cts.`hex`
            FROM `crm_tasks` AS ct
            LEFT JOIN `crm_task_status` AS cts ON ct.`status` = cts.`id`
            LEFT JOIN `crm_task_help_topic` AS ctht ON ct.`help_topic` = ctht.`id`
            WHERE `crm_task_id` = {$crm_task_id}
            ");
            $crm_task_row = $crm_task_sql->row();

            //print_r($crm_task_row);
            //exit();

            // get first 30 characters
            $describe_issue_short = substr($crm_task_row->describe_issue,0,30);

            // Issue Summary
            if( $crm_task_row->issue_summary != $issue_summary ){
      
                $crm_task_log_text = "Issue Summary updated from <b>{$crm_task_row->issue_summary}</b> to <b>{$issue_summary}</b>";
                $this->insert_crm_task_logs($crm_task_id,$crm_task_log_text);

            }

            // Help Topic
            if( $crm_task_row->help_topic != $help_topic ){
      
                // get help topic
                $new_help_topic = null;
                $crm_task_help_topic_sql = $this->db->query("
                SELECT `help_topic`
                FROM `crm_task_help_topic` 
                WHERE `active` = 1
                AND `id` = {$help_topic}        
                ");
                $crm_task_help_topic_row = $crm_task_help_topic_sql->row();
                $new_help_topic = $crm_task_help_topic_row->help_topic;             

                $crm_task_log_text = "Help Topic updated from <b>{$crm_task_row->ctht_help_topic}</b> to <b>{$new_help_topic}</b>";
                $this->insert_crm_task_logs($crm_task_id,$crm_task_log_text);

            }

            // Priority
            if( $crm_task_row->ticket_priority != $ticket_priority ){
      
                $current_ticket_priority = $this->reports_model->get_support_ticket_priority($crm_task_row->ticket_priority);
                $new_ticket_priority = $this->reports_model->get_support_ticket_priority($ticket_priority);                

                $crm_task_log_text = "Priority updated from <b>{$current_ticket_priority}</b> to <b>{$new_ticket_priority}</b>";
                $this->insert_crm_task_logs($crm_task_id,$crm_task_log_text);
                $priority_txt = $crm_task_row->ticket_priority.' --> <strong>'.$priority_txt.'</strong>';

            }

            // Page Link
            if( $crm_task_row->page_link != $page_link ){
      
                $crm_task_log_text = "Page Link updated from <a href='{$crm_task_row->page_link}' target='_blank'>{$crm_task_row->page_link}</a> to <a href='{$crm_task_row->page_link}' target='_blank'>{$page_link}</a>";
                $this->insert_crm_task_logs($crm_task_id,$crm_task_log_text);

            }

            // Ticket Status
            if( $crm_task_row->ct_status != $status ){

                // get help topic
                $new_status = null;
                $crm_task_status_sql = $this->db->query("
                SELECT `status`
                FROM `crm_task_status` 
                WHERE `active` = 1
                AND `id` = {$status}        
                ");
                $crm_task_status_row = $crm_task_status_sql->row();
                $new_status = $crm_task_status_row->status;          
                      
                $crm_task_log_text = "Status updated from <b>{$crm_task_row->cts_status}</b> to <b>{$new_status}</b>";
                $this->insert_crm_task_logs($crm_task_id,$crm_task_log_text);

            }

            // Response
            if( $crm_task_row->response != $response ){

                $crm_task_log_text = $response;
                $this->insert_crm_task_logs($crm_task_id,$crm_task_log_text);

            }

            
            
            // if screenshot uploaded
            $screenshotCount = count($_FILES["screenshot"]['name']);

            if($screenshotCount > 0){
                $names='';
                $name_array = [];

                foreach($_FILES as $key=>$value) {
                    for($s=0; $s<=$screenshotCount-1; $s++) {

                        if( $_FILES["screenshot"]['name'] != '' ) {
                        
                            $_FILES['screenshot']['name']=$value['name'][$s];
                            $_FILES['screenshot']['type']    = $value['type'][$s];
                            $_FILES['screenshot']['tmp_name'] = $value['tmp_name'][$s];
                            $_FILES['screenshot']['error']       = $value['error'][$s];
                            $_FILES['screenshot']['size']    = $value['size'][$s];   
                            $file = pathinfo($_FILES['screenshot']['name']);
                            $filename = str_replace(' ', '_', $file['filename']) . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
                            $config['file_name'] = $filename;
                            $config['upload_path'] = 'uploads/crm_task_screenshots/';
                            $config['allowed_types'] = '*';
                            $config['max_size']	= '5000';
                            $this->load->library('upload', $config);
                            // $this->upload->do_upload();
                            $this->upload->do_upload('screenshot');
                            $data = $this->upload->data();
                            $name_array[] ='uploads/crm_task_screenshots/'.$data['file_name'];
                        }
                        $names = implode(',', $name_array);
                    }
                }
            }
            
            // check if new images
            $tmp_name = $_FILES['screenshot']['name'];
            
            $new_uploads = "No new documents";
            $new_comment = "No new comments";

            if(!empty($response)){
                if(strlen($response) > 200){
                    $new_comment = "<strong>...<a href={$crm_ticket_link}>See more on ticket</a></strong>";
                }
                else{
                    $new_comment = "";
                }
            }

            if(!empty($tmp_name)){
                $new_uploads = "<strong>New document added <a href={$crm_ticket_link}>please view on the ticket</a></strong>";
            }

            // update
            $update_data = array(
                'issue_summary' => $issue_summary,
                'help_topic' => $help_topic,
                'ticket_priority' => $ticket_priority,
                'page_link' => $page_link,                                
                'response' => $response,
                'last_updated_by' => $logged_user_id,
                'last_updated_ts' => $today_full,
                'status' => $status
            );   

            if( $_FILES["screenshot"]['name'] != '' ){

                if (!empty($crm_task_row->screenshot)) {
                    $update_data['screenshot'] = $crm_task_row->screenshot.','.$names;
                } else {
                    $update_data['screenshot'] = $names;
                }
                
                $screenshotArray = explode(',', $names);
                $fileLink = '';
                foreach($screenshotArray as $screenshot){
                    $fileLink .='[<a href="/'.$screenshot.'" class="fancybox-uploaded-screenshot">File</>] ';
                }
                $crm_task_log_text = "Added new <b>{$screenshotCount}</b> screenshot(s) ".$fileLink;
                $this->insert_crm_task_logs($crm_task_id,$crm_task_log_text);
            }


            if( $status == 4 ){ // update to completed
                $update_data['completed_ts'] = $today_full;
            }

            $this->db->where('crm_task_id', $crm_task_id);
            $this->db->update('crm_tasks', $update_data);            

            // notification
            $send_notif_to_arr = [];
            $send_notif_to_arr_emails = [];

            // get subscribed managers
            $subscribed_managers_sql = $this->db->query("
            SELECT ctm.`staff_id`, sa.`Email`
            FROM `crm_task_managers` AS ctm
            INNER JOIN `staff_accounts` AS sa ON ctm.`staff_id` = sa.`StaffID`
            WHERE ctm.`active` = 1
            AND sa.`active` = 1
            AND sa.`Deleted` = 0
            ");
            foreach( $subscribed_managers_sql->result() as $subscribed_manager_row ){

                if( $subscribed_manager_row->staff_id > 0 ){
                    $send_notif_to_arr[] = $subscribed_manager_row->staff_id;
                    $send_notif_to_arr_emails[] = $subscribed_manager_row->Email;
                }

            }

            // get subscribed users of this ticket
            $subscribed_users_sql = $this->db->query("
            SELECT ctdsu.`staff_id`, sa.`Email`
            FROM `crm_task_details_sub_users` AS ctdsu
            INNER JOIN `staff_accounts` AS sa ON ctdsu.`staff_id` = sa.`StaffID`
            WHERE ctdsu.`active` = 1
            AND sa.`active` = 1
            AND sa.`Deleted` = 0
            AND ctdsu.`ticket_id` = {$crm_task_id} 
            ");
            foreach( $subscribed_users_sql->result() as $subscribed_users_row ){

                if( $subscribed_users_row->staff_id > 0 ){
                    $send_notif_to_arr[] = $subscribed_users_row->staff_id;
                    $send_notif_to_arr_emails[] = $subscribed_users_row->Email;
                }

            }

            // get assigned developers of this ticket
            $subscribed_devs_sql = $this->db->query("
            SELECT ctd_dev.`dev_id`, sa.`Email`
            FROM `crm_task_details_devs` AS ctd_dev
            INNER JOIN `staff_accounts` AS sa ON ctd_dev.`dev_id` = sa.`StaffID`
            WHERE ctd_dev.`active` = 1
            AND sa.`active` = 1
            AND sa.`Deleted` = 0
            AND ctd_dev.`ticket_id` = {$crm_task_id} 
            ");
            foreach( $subscribed_devs_sql->result() as $subscribed_devs_row ){

                if( $subscribed_devs_row->dev_id > 0 ){
                    $send_notif_to_arr[] = $subscribed_devs_row->dev_id;
                    $send_notif_to_arr_emails[] = $subscribed_devs_row->Email;
                }

            }

            // get created user of this ticket
            $created_ticket_sql = $this->db->query("
            SELECT ct.`requested_by`, sa.`Email`
            FROM `crm_tasks` AS ct
            INNER JOIN `staff_accounts` AS sa ON ct.`requested_by` = sa.`StaffID`
            WHERE ct.`active` = 1
            AND sa.`active` = 1
            AND sa.`Deleted` = 0
            AND ct.`crm_task_id` = {$crm_task_id} 
            ");
            foreach( $created_ticket_sql->result() as $created_ticket_row ){

                if( $created_ticket_row->requested_by > 0 ){
                    $send_notif_to_arr_emails[] = $created_ticket_row->Email;
                }

            }

            // get requested by user
            if( $crm_task_row->requested_by > 0 ){
                $send_notif_to_arr[] = $crm_task_row->requested_by;
            }
            
            // get last updated by user
            if( $crm_task_row->last_updated_by > 0 ){
                $send_notif_to_arr[] = $crm_task_row->last_updated_by;
            }

            // remove duplicates 
            $send_notif_to_unique_arr = array_unique($send_notif_to_arr); 

            // remove duplicates 
            $send_notif_to_unique_arr_emails = array_unique($send_notif_to_arr_emails); 
            //print_r($send_notif_to_unique_arr_emails);
            //exit();   
            
            //print_r($send_notif_to_unique_arr_emails);
            //exit();

            // ticket priority
            $ticket_priority_txt = null;
            switch($ticket_priority){
                case 1:
                    $ticket_priority_txt = 'Low';
                break;
                case 2:
                    $ticket_priority_txt = 'Medium';
                break;
                case 3:
                    $ticket_priority_txt = 'High';
                break;
            }

            // ticket help topic
            $help_topic_txt = null;
            switch($help_topic){
                case 1:
                    $help_topic_txt = 'Bug';
                break;
                case 2:
                    $help_topic_txt = 'Suggestion';
                break;
                case 3:
                    $help_topic_txt = 'Feature Needed';
                break;
                case 4:
                    $help_topic_txt = 'Feature Wanted';
                break;
                case -1:
                    $help_topic_txt = 'Other';
                break;
            }

            // ticket status
            $status_txt = null;
            switch($status){
                case 1:
                    $status_txt = 'Pending';
                break;
                case 2:
                    $status_txt = 'Declined';
                break;
                case 3:
                    $status_txt = 'In Progress';
                break;
                case 4:
                    $status_txt = 'Completed';
                break;
                case 5:
                    $status_txt = 'QA';
                break;
                case 6:
                    $status_txt = 'More info required';
                break;
                case 7:
                    $status_txt = 'Unable to Replicate';
                break;
                case 8:
                    $status_txt = 'Approval Required';
                break;
            }

            $status = $status_txt;

            if($status != $crm_task_row->cts_status){
                $status_txt = $crm_task_row->cts_status.' --> <strong>'.$status_txt.'</strong>';
            }

            if( $crm_task_row->ticket_priority != $ticket_priority ){
                $tmp_priority = $crm_task_row->ticket_priority;
                if($tmp_priority == 1){
                    $tpriorty = "Low";
                }
                if($tmp_priority == 2){
                    $tpriorty = "Medium";
                }
                if($tmp_priority == 3){
                    $tpriorty = "High";
                }
                $priority_txt = $tpriorty.' --> <strong>'.$ticket_priority_txt.'</strong>';

            }

            else{
                $tmp_priority = $crm_task_row->ticket_priority;
                if($tmp_priority == 1){
                    $tpriorty = "Low";
                }
                if($tmp_priority == 2){
                    $tpriorty = "Medium";
                }
                if($tmp_priority == 3){
                    $tpriorty = "High";
                }
                $priority_txt = $tpriorty;
            }

            // loop through each subscribed user
            foreach( $send_notif_to_unique_arr as $send_notif_to ){

                if ( $send_notif_to > 0  && $send_notif_to != $logged_user_id ){ // do not notify the user who has submitted

                    // insert notifications            
                    $notf_msg = "{$logged_user_full_name} has responded to <a href='{$this->config->item('crmci_link')}/reports/ticket_details/?id={$crm_task_id}'>{$describe_issue_short}</a>";                                   
                    
                    $notf_type = 1; // General Notifications
                    $params = array(
                        'notf_type'=> $notf_type,
                        'staff_id'=> $send_notif_to, // notify staff
                        'country_id'=> $this->config->item('country'),
                        'notf_msg'=> $notf_msg
                    );
                    $this->gherxlib->insertNewNotification($params);

                    // pusher API notification
                    // notification config
                    $options = array(
                        'cluster' => $this->config->item('PUSHER_CLUSTER'),
                        'useTLS' => true
                    );
                    $pusher = new Pusher\Pusher(
                        $this->config->item('PUSHER_KEY'),
                        $this->config->item('PUSHER_SECRET'),
                        $this->config->item('PUSHER_APP_ID'),
                        $options
                    );
                
                    // trigger notification
                    $pusher_data['notif_type'] = $notf_type;
                    $ch = "ch".$send_notif_to; // notify staff
                    $ev = "ev01";
                    $pusher->trigger($ch, $ev, $pusher_data);                    
                }                
            }    
            
            //Start Chops
            // Response
            if( $crm_task_row->response != $response ){
                $readmore_response = "<strong>".substr($response, 0, 200)."</strong>";
            }
            

            $subject = "Update Status for CRM Ticket #".$crm_task_id;

            $email_content = "
            <html>
                <head>
                    <title>CRM Task #{$crm_task_id}</title>
                </head>
                <body>
                    <h2>Ticket Updates</h2>
                    <table>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>Status:</th>
                            <td style='text-align: left; padding: 5px;'> {$status_txt}</td>
                        </tr>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>Help Topic:</th>
                            <td style='text-align: left; padding: 5px;'> {$help_topic_txt}</td>
                        </tr>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>Ticket Priority:</th>
                            <td style='text-align: left; padding: 5px;'> {$priority_txt}</td>
                        </tr>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>Issue Summary:</th>
                            <td style='text-align: left; padding: 5px;'>{$issue_summary}</td>
                        </tr>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>Page Link: </th>
                            <td style='text-align: left; padding: 5px;'>" . $page_link . "</td>
                        </tr>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>CRM Ticket Link: </th>
                            <td style='text-align: left; padding: 5px;'>" . $crm_ticket_link. "</td>
                        </tr>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>Issue Details: </th>
                            <td style='text-align: left; padding: 5px;'>" . nl2br($describe_issue_short) . "</td>
                        </tr>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>Requested By: </th>
                            <td style='text-align: left; padding: 5px;'>{$logged_user_full_name}</td>
                        </tr>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>Comments: </th>
                            <td style='text-align: left; padding: 5px;'>{$readmore_response}{$new_comment}</td>
                        </tr>
                        <tr>
                            <th style='text-align: right; padding: 5px;'>Documents: </th>
                            <td style='text-align: left; padding: 5px;'>{$new_uploads}</td>
                        </tr>
                    </table>
                </body>
            </html>
            ";


            // email settings
            $email_config = Array(
                'mailtype' => 'html',
                'charset' => 'utf-8'
            );
            $this->email->initialize($email_config);  
            $this->email->clear(TRUE);          
            $this->email->from($this->config->item('sats_it_email'));   
            $this->email->to($this->config->item('sats_it_email'));     
            //$this->email->to('lpagiwayan@gmail.com'); 
            $this->email->cc($send_notif_to_unique_arr_emails);                                          

            $this->email->subject($subject);
            $this->email->message($email_content);
            $this->email->send();

            //END
        }        

        $this->session->set_flashdata([
            'success_msg' => 'CRM Support Ticket Response Sent!',
            'status' => 'success'
        ]);
        
        //redirect('/reports/view_crm_tasks');
        redirect("/reports/ticket_details/?id={$crm_task_id}");

    }

    public function complaints_response(){

        $issue_summary = $this->input->get_post('issue_summary');
        $comp_topic = $this->input->get_post('comp_topic');
        $ticket_priority = $this->input->get_post('ticket_priority');
        // $page_link = $this->input->get_post('page_link');
        $describe_issue = $this->input->get_post('describe_issue');
        $comp_id = $this->input->get_post('comp_id');        
        $response = $this->input->get_post('response');
        $status = $this->input->get_post('status');
        $logged_user_id = $this->session->staff_id;
        $today_full = date('Y-m-d H:i:s');
        $crm_task_log_text = null;

        $logged_user_row = $this->gherxlib->getStaffInfo(['staff_id' => $logged_user_id])->row_array();
        $logged_user_full_name = $this->system_model->formatStaffName($logged_user_row['FirstName'], $logged_user_row['LastName']);

        if( $comp_id > 0 ){

            // get crm task data
            $complaints_sql = $this->db->query("
            SELECT 
                ct.`issue_summary`,
                ct.`comp_topic`,
                ct.`describe_issue`,
                ct.`ticket_priority`,
                ct.`page_link`,
                ct.`status` AS ct_status,
                ct.`response`,
                ct.`screenshot`,
                ct.`requested_by`,
                ct.`last_updated_by`,

                ctht.`comp_topic` AS ctht_help_topic,  

                cts.`status` AS cts_status,
                cts.`hex`
            FROM `complaints` AS ct
            LEFT JOIN `complaints_status` AS cts ON ct.`status` = cts.`id`
            LEFT JOIN `complaints_topic` AS ctht ON ct.`comp_topic` = ctht.`comp_topic_id`
            WHERE ct.`comp_id` = {$comp_id}
            ");
            $complaints_row = $complaints_sql->row();

            // get first 30 characters
            $describe_issue_short = substr($complaints_row->describe_issue,0,30);

            // Issue Summary
            if( $complaints_row->issue_summary != $issue_summary ){
      
                $comp_log_text = "Issue Summary updated from <b>{$complaints_row->issue_summary}</b> to <b>{$issue_summary}</b>";
                $this->insert_complaints_logs($comp_id,$comp_log_text);

            }

            // Help Topic
            if( $complaints_row->comp_topic != $comp_topic ){
      
                // get help topic
                $new_comp_topic = null;
                $comp_topic_sql = $this->db->query("
                SELECT `comp_topic`
                FROM `complaints_topic` 
                WHERE `active` = 1
                AND `comp_topic_id` = {$comp_topic}        
                ");
                $comp_topic_row = $comp_topic_sql->row();
                $new_comp_topic = $comp_topic_row->comp_topic;             

                $crm_task_log_text = "Help Topic updated from <b>{$complaints_row->ctht_help_topic}</b> to <b>{$new_comp_topic}</b>";
                $this->insert_complaints_logs($comp_id,$crm_task_log_text);

            }

            // Priority
            if( $complaints_row->ticket_priority != $ticket_priority ){
      
                $current_ticket_priority = $this->reports_model->get_support_ticket_priority($complaints_row->ticket_priority);
                $new_ticket_priority = $this->reports_model->get_support_ticket_priority($ticket_priority);                

                $comp_log_text = "Priority updated from <b>{$current_ticket_priority}</b> to <b>{$new_ticket_priority}</b>";
                $this->insert_complaints_logs($comp_id,$comp_log_text);

            }

            // Page Link
            // if( $complaints_row->page_link != $page_link ){
      
            //     $crm_task_log_text = "Page Link updated from <a href='{$crm_task_row->page_link}' target='_blank'>{$crm_task_row->page_link}</a> to <a href='{$crm_task_row->page_link}' target='_blank'>{$page_link}</a>";
            //     $this->insert_complaints_logs($crm_task_id,$crm_task_log_text);

            // }

            // Ticket Status
            if( $complaints_row->ct_status != $status ){

                // get help topic
                $new_status = null;
                $complaints_status_sql = $this->db->query("
                SELECT `status`
                FROM `complaints_status` 
                WHERE `active` = 1
                AND `id` = {$status}        
                ");
                $complaints_status_row = $complaints_status_sql->row();
                $new_status = $complaints_status_row->status;          
                      
                $comp_log_text = "Status updated from <b>{$complaints_row->cts_status}</b> to <b>{$new_status}</b>";
                $this->insert_complaints_logs($comp_id,$comp_log_text);

            }

            // Response
            if( $complaints_row->response != $response ){

                $comp_log_text = $response;
                $this->insert_complaints_logs($comp_id,$comp_log_text);

            }

            
            
              // if screenshot uploaded
              $screenshotCount = count($_FILES["screenshot"]['name']);

              if($screenshotCount > 0){
              $names='';
              $name_array = [];

              foreach($_FILES as $key=>$value)
              for($s=0; $s<=$screenshotCount-1; $s++) {
  
              if( $_FILES["screenshot"]['name'] != '' ){
              
              $_FILES['screenshot']['name']=$value['name'][$s];
              $_FILES['screenshot']['type']    = $value['type'][$s];
              $_FILES['screenshot']['tmp_name'] = $value['tmp_name'][$s];
              $_FILES['screenshot']['error']       = $value['error'][$s];
              $_FILES['screenshot']['size']    = $value['size'][$s];   
              $file = pathinfo($_FILES['screenshot']['name']);
              $filename = str_replace(' ', '_', $file['filename']) . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
              $config['file_name'] = $filename;
              $config['upload_path'] = 'uploads/complaints_files/';
              $config['allowed_types'] = '*';
              $config['max_size']	= '5000';
              $this->load->library('upload', $config);
              // $this->upload->do_upload();
               $this->upload->do_upload('screenshot');
              $data = $this->upload->data();
              $name_array[] ='uploads/complaints_files/'.$data['file_name'];
                  }
              $names = implode(',', $name_array);
  
               }

            }
            
            // update
            $update_data = array(
                'issue_summary' => $issue_summary,
                'comp_topic' => $comp_topic,
                'ticket_priority' => $ticket_priority,
                // 'page_link' => $page_link,                                
                'response' => $response,
                'last_updated_by' => $logged_user_id,
                'last_updated_ts' => $today_full,
                'status' => $status
            );   

            if( $_FILES["screenshot"]['name'] != '' ){

                $update_data['screenshot'] = $complaints_row->screenshot.','.$names;
                $screenshotArray = explode(',', $names);
                $fileLink = '';
                foreach($screenshotArray as $screenshot){
                    $fileLink .='[<a href="/'.$screenshot.'">File</>] ';
                }
                $comp_log_text = "Added new <b>{$screenshotCount}</b> screenshot(s) ".$fileLink;
                $this->insert_complaints_logs($comp_id,$comp_log_text);
            }


            if( $status == 4 ){ // update to completed
                $update_data['completed_ts'] = $today_full;
            }

            $this->db->where('comp_id', $comp_id);
            $this->db->update('complaints', $update_data);                                        

        }        

        $this->session->set_flashdata([
            'success_msg' => 'CRM Complaints Response Sent!',
            'status' => 'success'
        ]);
        
        //redirect('/reports/view_crm_tasks');
        redirect("/reports/complaints_details/?id={$comp_id}");

    }

    public function ignore_dirty_address() {

        $prop_id = $this->input->post('prop_id');

        if (!empty($prop_id)) {

            foreach ($prop_id as $val) {
                $update_data = array(
                    'ignore_dirty_address' => 1
                );
                $this->db->where('property_id', $val);
                $this->db->update('property', $update_data);
            }
        }
    }

    public function update_crm_task_action_form_submit() {
        $ct_id = $this->input->get_post('ct_id');
        $help_topic = $this->input->get_post('help_topic');
        $ticket_priority = $this->input->get_post('ticket_priority');
        $issue_summary = $this->input->get_post('issue_summary');
        $page_link = $this->input->get_post('page_link');
        $describe_issue = $this->input->get_post('describe_issue');
        $response = $this->input->get_post('response');
        $status = $this->input->get_post('status');
        $crm_ticket_link = trim(base_url());

        // get help topic
        $help_topic_txt = null;
        $crm_task_help_topic_sql = $this->db->query("
        SELECT `help_topic`
        FROM `crm_task_help_topic` 
        WHERE `active` = 1
        AND `id` = {$help_topic}        
        ");
        $crm_task_help_topic_row = $crm_task_help_topic_sql->row();
        $help_topic_txt = $crm_task_help_topic_row->help_topic;

        // ticket priority
        $ticket_priority_txt = null;
        switch($ticket_priority){
            case 1:
                $ticket_priority_txt = 'Low';
            break;
            case 2:
                $ticket_priority_txt = 'Medium';
            break;
            case 3:
                $ticket_priority_txt = 'High';
            break;
        }


        $params = array();
        if ($page_link != '') {
            $params['response'] = $page_link;
        }
        if ($describe_issue != '') {
            $params['describe_issue'] = $describe_issue;
        }
        if ($status != '') {
            $params['status'] = $status;
        }
        if ($response != '') {
            $params['response'] = $response;
        }
        if ($this->input->get_post('action') === 'add') {
            $logged_user = $this->session->staff_id;
            $staff = $this->gherxlib->getStaffInfo(['staff_id' => $logged_user])->row_array();
            $logged_user_fullname = $this->system_model->formatStaffName($staff['FirstName'], $staff['LastName']);

            // add crm task
            $add_crm_task_params = array(
                'help_topic' => $help_topic,
                'ticket_priority' => $ticket_priority,
                'issue_summary' => $issue_summary,
                'page_link' => $page_link,
                'describe_issue' => $describe_issue,
                'requested_by' => $logged_user,                
                'date_created' => date("Y-m-d H:i:s")
            );

            // if screenshot uploaded
            // if( $_FILES["screenshot"]['name'] != '' ){

            //     $file = pathinfo($_FILES["screenshot"]['name']);
            //     $filename = $file['filename'] . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
            //     $config['upload_path'] = 'uploads/crm_task_screenshots/';
            //     $config['allowed_types'] = 'gif|jpg|png|pdf';
            //     $config['max_size'] = 5000;
            //     $config['file_name'] = $filename;
            //     $this->load->library('upload', $config);
            //     $uploadFile = $this->upload->do_upload('screenshot');

            //     $add_crm_task_params['screenshot'] = 'uploads/crm_task_screenshots/' . $filename;

            // }

              // if screenshot uploaded
                $name_array = [];
                $screenshotCount = count($_FILES["screenshot"]['name']);

                foreach($_FILES as $key=>$value)
                for($s=0; $s<=$screenshotCount-1; $s++) {
    
                    if( $_FILES["screenshot"]['name'] != '' ){
                
                        $_FILES['screenshot']['name']=$value['name'][$s];
                        $_FILES['screenshot']['type']    = $value['type'][$s];
                        $_FILES['screenshot']['tmp_name'] = $value['tmp_name'][$s];
                        $_FILES['screenshot']['error']       = $value['error'][$s];
                        $_FILES['screenshot']['size']    = $value['size'][$s];   
                        $file = pathinfo($_FILES['screenshot']['name']);
                        $filename = str_replace(' ', '_', $file['filename']) . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
                        $config['file_name'] = $filename;
                        $config['upload_path'] = 'uploads/crm_task_screenshots/';
                        $config['allowed_types'] = '*';
                        $config['max_size']	= '5000';
                        $this->load->library('upload', $config);
                        // $this->upload->do_upload();
                        $this->upload->do_upload('screenshot');
                        $data = $this->upload->data();
                        $name_array[] ='uploads/crm_task_screenshots/'.$data['file_name'];

                    }
                    $names = implode(',', $name_array);
  
                }

                if( $_FILES["screenshot"]['name'] != '' ) {
                    $add_crm_task_params['screenshot'] = $names;
                }
            
            //if ($uploadFile) {

                $this->crmtasks_model->add_crmtask($add_crm_task_params);
                
                $crm_task_id = $this->db->insert_id();

                $subject = "New Crm Task";

                $email_content = "
                <html>
                    <head>
                        <title>CRM Task</title>
                    </head>
                    <body>
                        <h2>New Crm Task has been submitted</h2>
                        <table>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Help Topic:</th>
                                <td style='text-align: left; padding: 5px;'> {$help_topic_txt}</td>
                            </tr>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Ticket Priority:</th>
                                <td style='text-align: left; padding: 5px;'> {$ticket_priority_txt}</td>
                            </tr>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Issue Summary:</th>
                                <td style='text-align: left; padding: 5px;'>{$issue_summary}</td>
                            </tr>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Page Link: </th>
                                <td style='text-align: left; padding: 5px;'>" . $_POST['page_link'] . "</td>
                            </tr>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>CRM Ticket Link: </th>
                                <td style='text-align: left; padding: 5px;'>" . $crm_ticket_link. "reports/ticket_details/?id=$crm_task_id</td>
                            </tr>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Issue Details: </th>
                                <td style='text-align: left; padding: 5px;'>" . nl2br($_POST['describe_issue']) . "</td>
                            </tr>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Requested By: </th>
                                <td style='text-align: left; padding: 5px;'>{$logged_user_fullname}</td>
                            </tr>
                        </table>
                    </body>
                </html>
                ";


                // email settings
                $email_config = Array(
                    'mailtype' => 'html',
                    'charset' => 'utf-8'
                );
                $this->email->initialize($email_config);  
                $this->email->clear(TRUE);          
                $this->email->from($this->config->item('sats_it_email'));   
                $this->email->to($this->config->item('sats_it_email'));     
                //$this->email->to('lpagiwayan@gmail.com');                                           

                $this->email->subject($subject);
                $this->email->message($email_content);

                // send email
                if( $this->email->send() ){

                    $email_content = "
                    <html>
                        <head>
                            <title>CRM Support Ticket</title>
                        </head>
                        <body>
                            <h2>
                                Hi {$logged_user_fullname}<br />
                                Your Ticket # {$crm_task_id}
                            </h2>
                            <table>
                                <tr>
                                    <th style='text-align: right; padding: 5px;'>Help Topic:</th>
                                    <td style='text-align: left; padding: 5px;'> {$help_topic_txt}</td>
                                </tr>
                                <tr>
                                    <th style='text-align: right; padding: 5px;'>Ticket Priority:</th>
                                    <td style='text-align: left; padding: 5px;'> {$ticket_priority_txt}</td>
                                </tr>
                                <tr>
                                    <th style='text-align: right; padding: 5px;'>Issue Summary:</th>
                                    <td style='text-align: left; padding: 5px;'>{$issue_summary}</td>
                                </tr> 
                                <tr>
                                    <th style='text-align: right; padding: 5px;'>Page Link: </th>
                                    <td style='text-align: left; padding: 5px;'>" . $_POST['page_link'] . "</td>
                                </tr>
                                <tr>
                                    <th style='text-align: right; padding: 5px;'>Issue Details: </th>
                                    <td style='text-align: left; padding: 5px;'>" . nl2br($_POST['describe_issue']) . "</td>
                                </tr>
                                <tr>
                                    <th style='text-align: right; padding: 5px;'>Created: </th>
                                    <td style='text-align: left; padding: 5px;'>".date('d/m/Y H:i:s')."</td>
                                </tr>
                            </table>
                        </body>
                    </html>
                    ";

                    $subject = "CRM Support Ticket";

                    // email settings
                    $email_config = Array(
                        'mailtype' => 'html',
                        'charset' => 'utf-8'
                    );
                    $this->email->initialize($email_config);  
                    $this->email->clear(TRUE);          
                    $this->email->from($this->config->item('sats_it_email'));                
                    $this->email->to($staff['Email']);   
                    //$this->email->to('lpagiwayan@gmail.com');                                              

                    $this->email->subject($subject);
                    $this->email->message($email_content);
                    $this->email->send();

                }

                // notification
                $send_notif_to_arr = [];

                // get subscribed managers
                $subscribed_managers_sql = $this->db->query("
                SELECT 
                    ctm.`staff_id`,
                    
                    sa.`FirstName`,
                    sa.`LastName`
                FROM `crm_task_managers` AS ctm
                INNER JOIN `staff_accounts` AS sa ON ctm.`staff_id` = sa.`StaffID`
                WHERE ctm.`active` = 1
                AND sa.`active` = 1
                AND sa.`Deleted` = 0
                ");
                foreach( $subscribed_managers_sql->result() as $subscribed_manager_row ){

                    if( $subscribed_manager_row->staff_id > 0 ){

                        // insert notifications            
                        $notf_msg = "{$logged_user_fullname} has submitted new <a href='{$this->config->item('crmci_link')}/reports/ticket_details/?id={$crm_task_id}'>support ticket</a>";                                   
                            
                        $notf_type = 1; // General Notifications
                        $params = array(
                            'notf_type'=> $notf_type,
                            'staff_id'=> $subscribed_manager_row->staff_id, // notify staff
                            'country_id'=> $this->config->item('country'),
                            'notf_msg'=> $notf_msg
                        );
                        $this->gherxlib->insertNewNotification($params);

                        // pusher API notification
                        // notification config
                        $options = array(
                            'cluster' => $this->config->item('PUSHER_CLUSTER'),
                            'useTLS' => true
                        );
                        $pusher = new Pusher\Pusher(
                            $this->config->item('PUSHER_KEY'),
                            $this->config->item('PUSHER_SECRET'),
                            $this->config->item('PUSHER_APP_ID'),
                            $options
                        );
                    
                        // trigger notification
                        $pusher_data['notif_type'] = $notf_type;
                        $ch = "ch".$subscribed_manager_row->staff_id; // notify staff
                        $ev = "ev01";
                        $pusher->trigger($ch, $ev, $pusher_data); 

                    }

                }

                // SET FLASH NOTICES
                $this->session->set_flashdata([
                    'success_msg' => 'Task has been added',
                    'status' => 'success'
                ]);
                redirect(base_url('/reports/view_crm_tasks'));
               
        } 

    }

    public function update_complaints_action_form_submit() {
        // $ct_id = $this->input->get_post('ct_id');
        $comp_topic = $this->input->get_post('comp_topic');
        // $ticket_priority = $this->input->get_post('ticket_priority');
        $issue_summary = $this->input->get_post('issue_summary');
        // $page_link = $this->input->get_post('page_link');
        $describe_issue = $this->input->get_post('describe_issue');
        // $response = $this->input->get_post('response');
        // $status = $this->input->get_post('status');

        // get help topic
        $comp_topic_txt = null;
        $comp_topic_sql = $this->db->query("
        SELECT `comp_topic`
        FROM `complaints_topic` 
        WHERE `active` = 1
        AND `comp_topic_id` = {$comp_topic}          
        ");
        $comp_topic_row = $comp_topic_sql->row();
        $comp_topic_txt = $comp_topic_row->comp_topic;

        // ticket priority
        // $ticket_priority_txt = null;
        // switch($ticket_priority){
        //     case 1:
        //         $ticket_priority_txt = 'Low';
        //     break;
        //     case 2:
        //         $ticket_priority_txt = 'Medium';
        //     break;
        //     case 3:
        //         $ticket_priority_txt = 'High';
        //     break;
        // }


        $params = array();
        if ($page_link != '') {
            $params['response'] = $page_link;
        }
        if ($describe_issue != '') {
            $params['describe_issue'] = $describe_issue;
        }
        if ($status != '') {
            $params['status'] = $status;
        }
        if ($response != '') {
            $params['response'] = $response;
        }
        if ($this->input->get_post('action') === 'add') {
            $logged_user = $this->session->staff_id;
            $staff = $this->gherxlib->getStaffInfo(['staff_id' => $logged_user])->row_array();
            $logged_user_fullname = $this->system_model->formatStaffName($staff['FirstName'], $staff['LastName']);

            // add crm task
            $add_crm_task_params = array(
                'comp_topic' => $comp_topic,
                // 'ticket_priority' => $ticket_priority,
                'issue_summary' => $issue_summary,
                // 'page_link' => $page_link,
                'describe_issue' => $describe_issue,
                'requested_by' => $logged_user,                
                'date_created' => date("Y-m-d H:i:s")
            );

              // if screenshot uploaded
              $name_array = [];
              $screenshotCount = count($_FILES["screenshot"]['name']);
  
              foreach($_FILES as $key=>$value)
              for($s=0; $s<=$screenshotCount-1; $s++) {
  
              if( $_FILES["screenshot"]['name'] != '' ){
              
              $_FILES['screenshot']['name']=$value['name'][$s];
              $_FILES['screenshot']['type']    = $value['type'][$s];
              $_FILES['screenshot']['tmp_name'] = $value['tmp_name'][$s];
              $_FILES['screenshot']['error']       = $value['error'][$s];
              $_FILES['screenshot']['size']    = $value['size'][$s];   
              $file = pathinfo($_FILES['screenshot']['name']);
              $filename = str_replace(' ', '_', $file['filename']) . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
              $config['file_name'] = $filename;
              $config['upload_path'] = 'uploads/complaints_files/';
              $config['allowed_types'] = '*';
              $config['max_size']	= '5000';
              $this->load->library('upload', $config);
              // $this->upload->do_upload();
               $this->upload->do_upload('screenshot');
              $data = $this->upload->data();
              $name_array[] ='uploads/complaints_files/'.$data['file_name'];
                  }
                  $names= implode(',', $name_array);
  
               }
              if( $_FILES["screenshot"]['name'] != '' ){

               $add_crm_task_params['screenshot'] = $names;
              }
            
            //if ($uploadFile) {

                // $this->crmtasks_model->add_crmtask($add_crm_task_params);
                $this->db->insert('complaints', $add_crm_task_params);
                
                $complaints_id = $this->db->insert_id();

                $agency_temp_sql = $this->db->query("
                    SELECT *
                    FROM `complaints_agency_temp` 
                    WHERE `user_id` = {$this->session->staff_id}
                    ");
                    foreach( $agency_temp_sql->result() as $temps_row ){ 
                        $move_assign = array(
                            'comp_id' => $complaints_id,
                            'agency_id' => $temps_row->agency_id
                        );
                        $this->db->insert('complaints_agency', $move_assign);
                    }

                $this->db->where("`user_id` = {$this->session->staff_id}");
                $this->db->delete("`complaints_agency_temp`");

                $subject = "New Complaints";

                $email_content = "
                <html>
                    <head>
                        <title>Complaints</title>
                    </head>
                    <body>
                        <h2>New Complaints has been submitted</h2>
                        <table>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Complaints Topic:</th>
                                <td style='text-align: left; padding: 5px;'> {$comp_topic_txt}</td>
                            </tr>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Issue Summary:</th>
                                <td style='text-align: left; padding: 5px;'>{$issue_summary}</td>
                            </tr>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Issue Details: </th>
                                <td style='text-align: left; padding: 5px;'>" . nl2br($_POST['describe_issue']) . "</td>
                            </tr>
                            <tr>
                                <th style='text-align: right; padding: 5px;'>Requested By: </th>
                                <td style='text-align: left; padding: 5px;'>{$logged_user_fullname}</td>
                            </tr>
                        </table>
                    </body>
                </html>
                ";


                // email settings
                $email_config = Array(
                    'mailtype' => 'html',
                    'charset' => 'utf-8'
                );
                $this->email->initialize($email_config);  
                $this->email->clear(TRUE);          
                $this->email->from($this->config->item('sats_info_email'));   
                $this->email->to($this->config->item('sats_info_email'));       
                // $this->email->to('dave.flores199x@gmail.com');                                           

                $this->email->subject($subject);
                $this->email->message($email_content);

                // send email
                if( $this->email->send() ){

                $assign_temp_sql = $this->db->query("
                    SELECT *
                    FROM `complaints_assign_to_temp` 
                    WHERE `user_id` = {$this->session->staff_id}
                    ");
                    foreach( $assign_temp_sql->result() as $temp_row ){ 
                        $move_assign = array(
                            'comp_id' => $complaints_id,
                            'staff_id' => $temp_row->staff_id
                        );
                        $this->db->insert('complaints_assign_to', $move_assign);

                        $staff = $this->gherxlib->getStaffInfo(['staff_id' => $temp_row->staff_id])->row_array();
                        $email_content = "
                        <html>
                            <head>
                                <title>Complaints Ticket</title>
                            </head>
                            <body>
                                <h2>
                                    Hi {$logged_user_fullname}<br />
                                    Your Ticket # {$complaints_id}
                                </h2>
                                <table>
                                    <tr>
                                        <th style='text-align: right; padding: 5px;'>Complaints Topic:</th>
                                        <td style='text-align: left; padding: 5px;'> {$comp_topic_txt}</td>
                                    </tr>
                                    <tr>
                                        <th style='text-align: right; padding: 5px;'>Issue Summary:</th>
                                        <td style='text-align: left; padding: 5px;'>{$issue_summary}</td>
                                    </tr> 
                                    <tr>
                                        <th style='text-align: right; padding: 5px;'>Issue Details: </th>
                                        <td style='text-align: left; padding: 5px;'>" . nl2br($_POST['describe_issue']) . "</td>
                                    </tr>
                                    <tr>
                                        <th style='text-align: right; padding: 5px;'>Created: </th>
                                        <td style='text-align: left; padding: 5px;'>".date('d/m/Y H:i:s')."</td>
                                    </tr>
                                </table>
                            </body>
                        </html>
                        ";

                        $subject = "Complaints Ticket";

                        // email settings
                        $email_config = Array(
                            'mailtype' => 'html',
                            'charset' => 'utf-8'
                        );
                        $this->email->initialize($email_config);  
                        $this->email->clear(TRUE);          
                        $this->email->from($this->config->item('sats_info_email'));                
                        $this->email->to($staff['Email']);   
                        // $this->email->to('dave.flores199x@gmail.com');   
                        //test
                        //$this->email->to('vaultdweller123@gmail.com');                                              

                        $this->email->subject($subject);
                        $this->email->message($email_content);
                        $this->email->send();
                    }
                    $this->db->where("`user_id` = {$this->session->staff_id}");
                    $this->db->delete("`complaints_assign_to_temp`");
                }


                // SET FLASH NOTICES
                $this->session->set_flashdata([
                    'success_msg' => 'Complaints has been added',
                    'status' => 'success'
                ]);
                redirect(base_url('/reports/view_complaints'));
               
        } 

    }

    /**
     * Aged Debtors list
     * NOte: when changing main query update also export query named 'aged_debtors_export()'
     */
    public function aged_debtors() {
        $search = $this->input->get_post('search_submit');
        $agency_id = $this->input->get_post('agency_filter');
        $uri = '/reports/debtors';
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;


        // main query
        $sel_query = "
            a.`agency_name`,
            a.`agency_id`
        ";

        // static financial year
        $financial_year = $this->config->item('accounts_financial_year');
        // get unpaid jobs and exclude 0 job price
        $custom_where = "`j`.`invoice_balance` >0
                    AND `j`.`status` = 'Completed'
                    AND a.`status` != 'target'
                    AND (
                            j.`date` >= '$financial_year' OR
                            j.`unpaid` = 1
                    )
        ";
        $custom_where2_test = "`j`.`invoice_balance` >0
                    AND (
                            j.`date` >= '$financial_year' OR
                            j.`unpaid` = 1
                    )
        ";

        //main list for agency group
        // main list
       /* $main_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'agency_filter' => $agency_id,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC'
                )
            ),
            'group_by' => 'a.`agency_id`',
            'display_query' => 0
        );*/

        if ($search) {
           // $data['list'] = $this->jobs_model->get_jobs($main_params);
        }


        // agency filter
        $sel_query = "
            DISTINCT(a.`agency_id`),
            a.`agency_name`,
        ";

        $main_params = array(
            'sel_query' => $sel_query,
           // 'custom_where' => $custom_where2_test,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC'
                )
            ),
            //'group_by' => 'a.`agency_id`',
            'display_query' => 0
        );

        $data['agency_filter'] = $this->jobs_model->get_jobs($main_params);


        // main list
        /* $main_params = array(
          'sel_query' => $sel_query,
          'custom_where' => $custom_where,
          'agency_filter' => $agency_id,
          'country_id' => $country_id,
          'sort_list' => array(
          array(
          'order_by' => 'j.date',
          'sort' => 'DESC'
          )
          ),
          'display_query' => 0
          );

          $data['list'] = $this->jobs_model->get_jobs($main_params); */


        $data['title'] = 'Aged Debtors';
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/aged_debtors', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Aged Export
     */
    public function aged_debtors_export() {

        $this->load->library('DebtorsPdf');
        $title = "Aged Debtors Report";
        $agency_filter = $this->input->get_post('agency_filter');
        $country_id = $this->config->item('country');
        $output_type = $this->input->get_post('output_type');


        //>>>>>>>>>>>>>>>>>>>>excel export start

        // get agency
        $sel_query = "
            a.`agency_id`,
            a.`agency_name`
        ";

        $agency_params = array(
            'sel_query' => $sel_query,
            'agency_id' => $agency_filter,
            'country_id' => $country_id,
            'display_query' => 0
        );
        $agency_sql = $this->agency_model->get_agency($agency_params);
        $agency_row = $agency_sql->row();

        $agency_name = $agency_row->agency_name;
        // get agency end

        //MAIN QUERY
        $sel_query = "
            a.`agency_name`,
            a.`agency_id`,

            j.`id` as j_id,
            j.`invoice_balance`,
            j.date as j_date,

            p.`address_1` as p_address_1,
            p.`address_2` as p_address_2,
            p.`address_3` as p_address_3,
            p.`state` as p_state,
            p.`postcode` as p_postcode,
        ";

        // static financial year
        $financial_year = $this->config->item('accounts_financial_year');
        // get unpaid jobs and exclude 0 job price
        $custom_where = "`j`.`invoice_balance` >0
                    AND `j`.`status` = 'Completed'
                    AND a.`status` != 'target'
                    AND (
                            j.`date` >= '$financial_year' OR
                            j.`unpaid` = 1
                    )
        ";
        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'agency_filter' => $agency_filter,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'j.date',
                    'sort' => 'DESC'
                )
            ),
            'display_query' => 0
        );
        $list = $this->jobs_model->get_jobs($main_params);

        //excel initial start
        $filename = "aged_debtors_" . date('Y-m-d') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: Attachment; filename=$filename");
        header("Pragma: no-cache");

        //header
        echo "Property Address,Date,Invoice #,Current,1-30 days OVERDUE,31-60 days OVERDUE,61+ days OVERDUE,Total Amount Due\n";


        // total var
        $current_tot = 0;
        $overdue_1_to_30_tot = 0;
        $overdue_31_to_60_tot = 0;
        $overdue_61_tot = 0;
        $total_amount_due_tot = 0;

        foreach ($list->result_array() as $row)
        {

            $check_digit = $this->gherxlib->getCheckDigit(trim($row['j_id']));
            $bpay_ref_code = "{$row['j_id']}{$check_digit}";

            $full_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";

            $job_date = $this->system_model->formatDate($row['j_date'], 'd/m/Y');

            //current
            $having = "DateDiff <= 1";
            $job_params = array(
                'agency_id' => $row['agency_id'],
                'having' => $having,
                'job_id' => $row['j_id'],
                'display_query' => 0
            );
            $current = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params);
            $current_format = number_format($current,2);

            //1-30 days
            $having_1_30 = "DateDiff BETWEEN 1 AND 30";
            $job_params_1_30 = array(
                'agency_id' => $row['agency_id'],
                'job_id' => $row['j_id'],
                'having' => $having_1_30,
                'display_query' => 0
            );
            $overdue_1_to_30 = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params_1_30);
            $overdue_1_to_30_format = number_format($overdue_1_to_30,2);

            //31-60 days
            $having_31_60 = "DateDiff BETWEEN 31 AND 60";
            $job_params_31_60 = array(
                'agency_id' => $row['agency_id'],
                'job_id' => $row['j_id'],
                'having' => $having_31_60,
                'display_query' => 0
            );
            $overdue_31_to_60 = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params_31_60);
            $overdue_31_to_60_format = number_format($overdue_31_to_60,2);

            //61+ days
            $having_61 = "DateDiff >= 61";
            $job_params_61 = array(
                'agency_id' => $row['agency_id'],
                'job_id' => $row['j_id'],
                'having' => $having_61,
                'display_query' => 0
            );
            $overdue_61 = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params_61);
            $overdue_61_format = number_format($overdue_61,2);

            //total
            $total_amount_due = $row['invoice_balance'];
            $total_amount_due_format = number_format($total_amount_due,2);

            echo "\"{$full_address}\",{$job_date},\"$bpay_ref_code\",\"$.$current_format\",\"$.$overdue_1_to_30_format\",\"$.$overdue_31_to_60_format\",\"$.$overdue_61_format\",\"$.$total_amount_due_format\"\n";

            //calculate totals
            $current_tot += $current;
            $overdue_1_to_30_tot += $overdue_1_to_30;
            $overdue_31_to_60_tot += $overdue_31_to_60;
            $overdue_61_tot += $overdue_61;
            $total_amount_due_tot += $total_amount_due;

            $current_tot_format = number_format($current_tot,2);
            $overdue_1_to_30_tot_format = number_format($overdue_1_to_30_tot,2);
            $overdue_31_to_60_tot_format = number_format($overdue_31_to_60_tot,2);
            $overdue_61_tot_format = number_format($overdue_61_tot,2);
            $total_amount_due_tot_format = number_format($total_amount_due_tot,2);
        }
        echo "\"Total\",\"\",\"\",\"$.$current_tot_format\",\"$.$overdue_1_to_30_tot_format\",\"$.$overdue_31_to_60_tot_format\",\"$.$overdue_61_tot_format\",\"$.$total_amount_due_tot_format\"\n";

        //>>>>>>>>>>>>>>>>>>>>excel export end

        /**** disable pdf and replace with excel above
        // pdf initiation
        $pdf = new DebtorsPdf();

        // settings
        $pdf->SetTopMargin(40);
        $pdf->SetAutoPageBreak(true, 40);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $pdf->report_name = $title;

        // get agency
        if ($agency_filter > 0) {

            $sel_query = "
                a.`agency_id`,
                a.`agency_name`
            ";

            $agency_params = array(
                'sel_query' => $sel_query,
                'agency_id' => $agency_filter,
                'country_id' => $country_id,
                'display_query' => 0
            );
            $agency_sql = $this->agency_model->get_agency($agency_params);
            $agency_row = $agency_sql->row();

            $pdf->agency_name = $agency_row->agency_name;
        } else {
            $pdf->agency_name = 'All Agency';
        }


        //MAIN QUERY
        // main query
        $sel_query = "
            a.`agency_name`,
            a.`agency_id`
        ";

        // static financial year
        $financial_year = $this->config->item('accounts_financial_year');
        // get unpaid jobs and exclude 0 job price
        $custom_where = "`j`.`invoice_balance` >0
                    AND `j`.`status` = 'Completed'
                    AND a.`status` != 'target'
                    AND (
                            j.`date` >= '$financial_year' OR
                            j.`unpaid` = 1
                    )
        ";
        $main_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'agency_filter' => $agency_filter,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC'
                )
            ),
            'group_by' => 'a.`agency_id`',
            'display_query' => 0
        );
        $list = $this->jobs_model->get_jobs($main_params);
        //AMIN QUERY END
        // set default values
        $header_width = 0;
        $header_height = 6;
        $header_border = 0;

        $font_size = 10;
        $pdf->SetFont('Arial', 'BI', 14);
        $pdf->SetTextColor(180, 32, 37);
        $pdf->Cell($header_width, 4, 'DEBTORS REPORT', $header_border, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);


        // Current as of
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($header_width, $header_height, 'Current as of ' . date('d/m/Y'), $header_border, 1, 'L');
        $y = $pdf->GetY();

        $pdf->Ln();

        // row
        $font_size = 8;
        $cell_height = 5;
        $col_width1 = 55;
        $col_width2 = 20;

        $pdf->SetFont('Arial', '', $font_size);

        $jobs_sql = $this->jobs_model->get_jobs($main_params);

        foreach ($list->result_array() as $agency_row) {

            $pdf->SetFont('Arial', 'B', $font_size);
            $pdf->Cell($col_width2, $cell_height, " ", 0);
            $pdf->Ln();
            $pdf->Cell($col_width2, $cell_height, $agency_row['agency_name'], 0);
            $pdf->Ln();

            $pdf->SetFillColor(211, 211, 211);
            $pdf->SetFont('Arial', 'B', $font_size);
            $pdf->Cell($col_width1, $cell_height, 'Property Address', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, 'Date', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, 'Invoice #', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, 'Current', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '1-30 days', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '31-60 days', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, '61+ days', 1, null, null, true);
            $pdf->Cell($col_width2, $cell_height, 'Amount Due', 1, null, null, true);
            $pdf->Ln();


            //list of unpaid ivoices by agency
            // main query
            $sel_query_2 = "
              a.`agency_name`,
              a.`agency_id`,

              j.`id` as j_id,
              j.`invoice_balance`,
              j.date as j_date,

              p.`address_1` as p_address_1,
              p.`address_2` as p_address_2,
              p.`address_3` as p_address_3,
              p.`state` as p_state,
              p.`postcode` as p_postcode,
          ";

            // static financial year
            $financial_year = $this->config->item('accounts_financial_year');
            // get unpaid jobs and exclude 0 job price
            $custom_where = "`j`.`invoice_balance` >0
                      AND `j`.`status` = 'Completed'
                      AND a.`status` != 'target'
                      AND (
                              j.`date` >= '$financial_year' OR
                              j.`unpaid` = 1
                      )
          ";
            $main_params_2 = array(
                'sel_query' => $sel_query_2,
                'custom_where' => $custom_where,
                'agency_filter' => $agency_row['agency_id'],
                'country_id' => $country_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'j.date',
                        'sort' => 'DESC'
                    )
                ),
                'display_query' => 0
            );

            $list2 = $this->jobs_model->get_jobs($main_params_2);

            // total
            $current_tot = 0;
            $overdue_1_to_30_tot = 0;
            $overdue_31_to_60_tot = 0;
            $overdue_61_tot = 0;
            $total_amount_due_tot = 0;

            foreach ($list2->result_array() as $row) {

                $check_digit = $this->gherxlib->getCheckDigit(trim($row['j_id']));
                $bpay_ref_code = "{$row['j_id']}{$check_digit}";

                $full_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";

                $job_date = $this->system_model->formatDate($row['j_date'], 'd/m/Y');

                //current
                $having = "DateDiff <= 1";
                $job_params = array(
                    'agency_id' => $row['agency_id'],
                    'having' => $having,
                    'job_id' => $row['j_id'],
                    'display_query' => 0
                );
                $current = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params);

                //1-30 days
                $having_1_30 = "DateDiff BETWEEN 1 AND 30";
                $job_params_1_30 = array(
                    'agency_id' => $row['agency_id'],
                    'job_id' => $row['j_id'],
                    'having' => $having_1_30,
                    'display_query' => 0
                );
                $overdue_1_to_30 = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params_1_30);

                //31-60 days
                $having_31_60 = "DateDiff BETWEEN 31 AND 60";
                $job_params_31_60 = array(
                    'agency_id' => $row['agency_id'],
                    'job_id' => $row['j_id'],
                    'having' => $having_31_60,
                    'display_query' => 0
                );
                $overdue_31_to_60 = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params_31_60);

                //61+ days
                $having_61 = "DateDiff >= 61";
                $job_params_61 = array(
                    'agency_id' => $row['agency_id'],
                    'job_id' => $row['j_id'],
                    'having' => $having_61,
                    'display_query' => 0
                );
                $overdue_61 = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params_61);

                //total
                $total_amount_due = $row['invoice_balance'];


                //PDF LIST CELL
                $pdf->SetFont('Arial', '', $font_size);
                $pdf->Cell($col_width1, $cell_height, $full_address, 1);
                $pdf->Cell($col_width2, $cell_height, $job_date, 1);
                $pdf->Cell($col_width2, $cell_height, $bpay_ref_code, 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($current, 2), 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_1_to_30, 2), 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_31_to_60, 2), 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_61, 2), 1);
                $pdf->Cell($col_width2, $cell_height, '$' . number_format($total_amount_due, 2), 1);
                $pdf->Ln();

                //calculate totals
                $current_tot += $current;
                $overdue_1_to_30_tot += $overdue_1_to_30;
                $overdue_31_to_60_tot += $overdue_31_to_60;
                $overdue_61_tot += $overdue_61;
                $total_amount_due_tot += $total_amount_due;
            }

            //calculated total cells
            $pdf->SetFont('Arial', 'B', $font_size);
            $pdf->Cell($col_width1, $cell_height, "TOTAL", 1);
            $pdf->Cell($col_width2, $cell_height, " ", 1);
            $pdf->Cell($col_width2, $cell_height, " ", 1);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($current_tot, 2), 1);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_1_to_30_tot, 2), 1);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_31_to_60_tot, 2), 1);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($overdue_61_tot, 2), 1);
            $pdf->Cell($col_width2, $cell_height, '$' . number_format($total_amount_due_tot, 2), 1);
            $pdf->Ln();
        }
        $file_name = 'debtors_' . date('YmdHis') . '.pdf';
        $pdf->Output($file_name, $output_type);
        */
    }

    public function view_expense_summary() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Expense Summary";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';
//
//        $search = $this->input->get_post('search');
//        $search_submit = $this->input->get_post('search_submit');
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'exp_sum.`date`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
        $employee = $this->input->get_post('employee');
        $country_id = $this->config->item('country');
        $line_manager_search = $this->input->get_post('line_manager_search');
        $filt_sum_status = $this->input->get_post('filt_sum_status');
        $card = $this->input->get_post('with_fund_source');
        if ($filt_sum_status === null) {
            $filt_sum_status = -1;
        }
        $from_date = ($this->input->get_post('from_date') != '') ? $this->input->get_post('from_date') : '';
        if ($from_date != '') {
            $from_date2 = $this->system_model->formatDate($from_date);
        }
        $to_date = ($this->input->get_post('to_date') != '') ? $this->input->get_post('to_date') : '';
        if ($to_date != '') {
            $to_date2 = $this->system_model->formatDate($to_date);
        }
        $params = array(
            'filterDate' => array(
                'from' => $from_date2,
                'to' => $to_date2
            ),
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'employee' => $employee,
            'country_id' => $country_id,
            'line_manager' => $line_manager_search,
            'exp_sum_status' => $filt_sum_status,
            'card' => $card,
            'group_by' => 'exp_sum.expense_summary_id',
            'echo_query' => 0
        );
        $params['sel_query'] = '
                exp_sum.expense_summary_id,
                exp_sum.date,
                exp_sum.date_reimbursed,
                exp_sum.exp_sum_status AS exp_sum_status,
                sa.`FirstName` AS sa_fname,
                sa.`LastName` AS sa_lname,
                sa_who.`FirstName` AS sa_who_fname,
                sa_who.`LastName` AS sa_who_lname,
                lm.`FirstName` AS lm_fname,
                lm.`LastName` AS lm_lname,
                lm.StaffId as line_manager
		';
//        var_dump($params);
        $data['expense_summary'] = $this->expensesummary_model->getButtonExpenseSummary($params);
        $data['staffAccounts'] = $this->expensesummary_model->getStaffAccountsByCountryId($this->config->item('country'))->result_array();
        $emp_params = array(
            'filterDate' => array(
                'from' => $from_date2,
                'to' => $to_date2
            ),
            'sel_query' => 'distinct(exp_sum.`employee`),
                sa.`FirstName` AS sa_fname,
                sa.`LastName` AS sa_lname, ',
            'country_id' => $country_id,
            'sort_list' => array([
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC'
                ]),
        );
        $data['employees'] = $this->expensesummary_model->getButtonExpenseSummary($emp_params);
        $data['employee'] = $employee;
        $data['line_manager_search'] = $line_manager_search;
        $data['filt_sum_status'] = $filt_sum_status;
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $t_params = array(
            'filterDate' => array(
                'from' => $from_date2,
                'to' => $to_date2
            ),
            'employee' => $employee,
            'country_id' => $country_id,
            'line_manager' => $line_manager_search,
            'exp_sum_status' => $filt_sum_status,
            'card' => $card,
            'group_by' => 'exp_sum.expense_summary_id',
            'echo_query' => 0
        );
        $t_params['sel_query'] = '
             exp_sum.expense_summary_id
		';
        $total = $this->expensesummary_model->getButtonExpenseSummary($t_params);
        $total_rows = $total->num_rows();
        $data['sort_list'] = $total_rows;

        // base url
        $query_args = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'employee' => $employee,
            'line_manager_search' => $line_manager_search,
            'filt_sum_status' => $filt_sum_status,
            'with_fund_source' => $card
        ];
        $base_url = '/reports/view_expense_summary/?' . http_build_query($query_args);

        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $base_url;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/view_expense_summary', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function update_exp_summary_action_ajax() {
        $action = $this->input->get_post('action');
        $exp_sum_id = $this->input->get_post('exp_sum_id');
        $staff_id = $this->session->staff_id;
        if ($action === "update_exp_sum_line_manager") {
            $line_manager = $this->input->get_post('line_manager');

            if ($line_manager === null || $exp_sum_id === null || (int) $exp_sum_id === 0) {
                echo "error2.1";
                return;
            }
            $this->expensesummary_model->updateLineManager($line_manager, $staff_id, $exp_sum_id);
            echo 'success1';
            return;
        } elseif ($action === "update_exp_sum_status") {
            $status = $this->input->get_post('exp_sum_status');

            if ($status === "" && $status !== 0) {
                $status = null;
            }
            if ((int) $exp_sum_id === 0) {
                echo "error2.2";
                return;
            }
            $this->expensesummary_model->updateStatus($status, $staff_id, $exp_sum_id);
            echo 'success2';
            return;
        } elseif ($action === "update_date_reimbursed") {
            $date_reimbursed = $this->input->get_post('date_reimbursed');
            $date_reimbursed = $this->system_model->formatDate($date_reimbursed);
            if ((int) $exp_sum_id === 0) {
                echo "error2.4";
                return;
            }
            $this->expensesummary_model->updateDateReimbursed($date_reimbursed, $staff_id, $exp_sum_id);
            echo 'success3';
            return;
        } else {
            echo "error1";
            return;
        }
    }

    public function view_expense_summary_pdf() {
        $exp_sum_id = $this->input->get_post('exp_sum_id');
        $country_id = $this->config->item('country');
        $output = 'I';
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'exp.date';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
        $jparams = array(
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'country_id' => $country_id,
            'exp_sum_id' => $exp_sum_id
        );
        $this->expensesummary_model->get_expense_summary_pdf($jparams, $output);
    }

    public function view_expense_summary_details() {
        if ($this->input->get_post('id') === null) {
            echo "ERROR";
            return;
        }
        $exp_sum_id = $this->input->get_post('id');
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Expense Summary Details";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';
//
//        $search = $this->input->get_post('search');
//        $search_submit = $this->input->get_post('search_submit');
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'exp_sum.`date`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
        $employee = $this->input->get_post('employee');
        $country_id = $this->config->item('country');
        $line_manager_search = $this->input->get_post('line_manager_search');
        $filt_sum_status = $this->input->get_post('filt_sum_status');
        $from_date = ($this->input->get_post('from_date') != '') ? $this->input->get_post('from_date') : '';
        if ($from_date != '') {
            $from_date2 = $this->system_model->formatDate($from_date);
        }
        $to_date = ($this->input->get_post('to_date') != '') ? $this->input->get_post('to_date') : '';
        if ($to_date != '') {
            $to_date2 = $this->system_model->formatDate($to_date);
        }
        if ($filt_sum_status === null) {
            $filt_sum_status = -1;
        }

        $bulk_download = $this->input->get_post('bulk_download');

        $params = array(
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'employee' => $employee,
            'country_id' => $country_id,
            'line_manager' => $line_manager_search,
            'exp_sum_status' => $filt_sum_status,
            'exp_sum_id' => $exp_sum_id,
            'echo_query' => 0
        );
        $params['sel_query'] = '
                exp_sum.expense_summary_id,
                exp_sum.date,
                exp_sum.date_reimbursed,
                exp_sum.exp_sum_status AS exp_sum_status,
                sa.`FirstName` AS sa_fname,
                sa.`LastName` AS sa_lname,
                sa_who.`FirstName` AS sa_who_fname,
                sa_who.`LastName` AS sa_who_lname,
                lm.`FirstName` AS lm_fname,
                lm.`LastName` AS lm_lname,
                lm.StaffId as line_manager,
                exp.amount
		';
//        var_dump($params);
        $data['expense_summary'] = $this->expensesummary_model->getButtonExpenseSummary($params);
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'exp.`date`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
        $jparams = array(
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'country_id' => $country_id,
            'exp_sum_id' => $exp_sum_id
        );

        $expenses_sql = $this->expensesummary_model->getExpenses($jparams);

        if( $bulk_download == 1 ){ // zip files for bulk download

            $zipname = "expense_summary_{$exp_sum_id}".date("YmdHis").rand().".zip";

            $zip = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);

            foreach( $expenses_sql->result() as $expenses_row ){
                
                // copied from expense summary details page
                $file = pathinfo($expenses_row->receipt_image);
                $filename = str_replace([" ", "."], "_", $file['filename']);                
                $file_path = "{$_SERVER["DOCUMENT_ROOT"]}/{$file['dirname']}/{$filename}.{$file['extension']}";   

                $zip->addFile($file_path);                    
                
            } 

            $zip->close();

            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename='.$zipname);
            readfile($zipname);

        }else{ // default page view

            $data['expenses'] = $expenses_sql;

            $jparams = array(
                'sort_list' => array([
                        'order_by' => '`account_name`',
                        'sort' => 'ASC']
                )
            );
            $accounts = $this->expensesummary_model->getExpenseAccount($jparams)->result_array();
            $data['accounts'] = $accounts;
            $total_rows = count($data['expenses']);
            $data['sort_list'] = $total_rows;
    
            // base url
            $base_url = '/reports/view_expense_summary     ';
    
            // pagination
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = $total_rows;
            $config['per_page'] = $per_page;
            $config['base_url'] = $base_url;
    
            $this->pagination->initialize($config);
    
            $data['pagination'] = $this->pagination->create_links();
    
            $pc_params = array(
                'total_rows' => $total_rows,
                'offset' => $offset,
                'per_page' => $per_page
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
    
            $this->load->view('templates/inner_header', $data);
            $this->load->view('reports/view_expense_summary_detail', $data);
            $this->load->view('templates/inner_footer', $data);

        }

        
    }

    public function export_expense_summary() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Expense Summary";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';
//
//        $search = $this->input->get_post('search');
//        $search_submit = $this->input->get_post('search_submit');
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'exp_sum.`date`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
        $employee = $this->input->get_post('employee');
        $country_id = $this->config->item('country');
        $line_manager_search = $this->input->get_post('line_manager_search');
        $filt_sum_status = $this->input->get_post('filt_sum_status');

        $from_date = ($this->input->get_post('from_date') != '') ? $this->input->get_post('from_date') : '';
        if ($from_date != '') {
            $from_date2 = $this->system_model->formatDate($from_date);
        }
        $to_date = ($this->input->get_post('to_date') != '') ? $this->input->get_post('to_date') : '';
        if ($to_date != '') {
            $to_date2 = $this->system_model->formatDate($to_date);
        }
        $params = array(
            'filterDate' => array(
                'from' => $from_date2,
                'to' => $to_date2
            ),
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'employee' => $employee,
            'country_id' => $country_id,
            'line_manager' => $line_manager_search,
            'exp_sum_status' => $filt_sum_status,
            'echo_query' => 0
        );
        if ($filt_sum_status !== null) {
            $params['exp_sum_status'] = $filt_sum_status;
        }
        $params['sel_query'] = '
                exp_sum.expense_summary_id,
                exp_sum.date,
                exp_sum.date_reimbursed,
                exp_sum.exp_sum_status AS exp_sum_status,
                sa.`FirstName` AS sa_fname,
                sa.`LastName` AS sa_lname,
                sa_who.`FirstName` AS sa_who_fname,
                sa_who.`LastName` AS sa_who_lname,
                lm.`FirstName` AS lm_fname,
                lm.`LastName` AS lm_lname,
                lm.StaffId as line_manager
		';
        if ($this->input->get('debug') === 'debug') {
            $params['echo_query'] = 1;
        }
//        var_dump($params);
        $data['expense_summary'] = $this->expensesummary_model->getButtonExpenseSummary($params);
        $data['staffAccounts'] = $this->expensesummary_model->getStaffAccountsByCountryId($this->config->item('country'))->result_array();
        $filename = "expense_summary_" . date("d/m/Y") . ".csv";

        if ($this->input->get('debug') !== 'debug') {
            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename={$filename}");
            header("Pragma: no-cache");
        }
// headers
//        echo "<pre>";
        echo "Date of Purchase,Name,Card Used,Supplier,Description,Account,Entered By,Amount,Net Amt,GST,Gross Amt\n";
        foreach ($data['expense_summary']->result_array() as $exp_sum) {
            $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'exp.`date`';
            $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
            $jparams = array(
                'sort_list' => array(
                    array(
                        'order_by' => $order_by,
                        'sort' => $sort
                    )
                ),
                'country_id' => $country_id,
                'exp_sum_id' => $exp_sum['expense_summary_id']
            );
            $expenses = $this->expensesummary_model->getExpenses($jparams)->result_array();

            foreach ($expenses as $exp) {
//                echo $exp_sum['expense_summary_id'];
//                echo "\t";
                $dop = date('d/m/Y', strtotime($exp['date']));
                $emp_full = "{$exp_sum['sa_fname']} {$exp_sum['sa_lname']}";
                $card_used = $this->expensesummary_model->getExpenseCards($exp['card']);
                $supplier = $exp['supplier'];
                $desc = $exp['description'];
                $acc_name = $exp['account_name'];
                $eb_full = "{$exp['eb_fname']} {$exp['eb_lname']}";
                $gst = $this->expensesummary_model->getDynamicGST($exp['amount'], $country_id);
                $net_amount = $exp['amount'] - $gst;

                $amount = "\$" . $exp['amount'];
                $net_amount2 = "\$" . number_format($net_amount, 2);
                $gst2 = "\$" . number_format($gst, 2);
                $gross_amt = "\$" . $exp['amount'];
                echo "\"{$dop}\",\"{$emp_full}\",\"{$card_used}\",\"{$supplier}\",\"{$desc}\",\"{$acc_name}\",\"{$eb_full}\",\"{$amount}\",\"{$net_amount2}\",\"{$gst2}\",\"{$gross_amt}\"\n";
            }
        }
//        echo "</pre>";
    }

    function view_add_expense() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Expense";
        $loggedin_staff_id = $this->session->staff_id;
        $country_id = $this->config->item('country');
        $jparams = array(
            'sort_list' => array([
                    'order_by' => 'exp.`expense_id`',
                    'sort' => 'ASC'
                ]),
            'paginate' => array(
                'offset' => 0,
                'limit' => 1
            ),
            'entered_by' => $loggedin_staff_id,
            'country_id' => $country_id,
            'exc_sub_exp' => 1
        );
        $exp_user = $this->expensesummary_model->getExpenses($jparams)->result_array();
        $jparams = array(
            'sort_list' => array([
                    'order_by' => 'exp.`date`',
                    'sort' => 'DESC'
                ]),
            'entered_by' => $loggedin_staff_id,
            'country_id' => $country_id,
            'exc_sub_exp' => 1
        );
        $exp_sql = $this->expensesummary_model->getExpenses($jparams)->result_array();
        $data['last_query'] = $this->db->last_query();
        $staff_accounts = $this->expensesummary_model->getStaffAccountsByCountryId($this->config->item('country'))->result_array();
        $loggedin_staff_id = $this->session->staff_id;
        $jparams = array(
            'sort_list' => array([
                    'order_by' => '`account_name`',
                    'sort' => 'ASC']
            )
        );
        $accounts = $this->expensesummary_model->getExpenseAccount($jparams)->result_array();

        $data['exp_user'] = $exp_user;
        $data['exp_sql'] = $exp_sql;
        $data['staff_accounts'] = $staff_accounts;
        $data['accounts'] = $accounts;
        $data['loggedin_staff_id'] = $loggedin_staff_id;
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/view_add_expense', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function add_expense_action_form_submit() {
        $employee = $this->input->post('employee');
        $date = $this->input->post('date');
        $date2 = $this->system_model->formatDate($date);
        $card = $this->input->post('card');
        $supplier = $this->input->post('supplier');
        $description = $this->input->post('description');
        $account = $this->input->post('account');
        $amount = $this->input->post('amount');
        $country_id = $this->config->item('country');
        $file = pathinfo($_FILES["receipt_image"]['name']);
        $filename = $file['filename'] . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
        $loggedin_staff_id = $this->session->staff_id;
        $config['upload_path'] = './uploads/expenses/';
        $config['allowed_types'] = 'gif|jpg|png|pdf|jpeg';
        $config['max_size'] = 5000;
        $config['file_name'] = $filename;
        $this->load->library('upload', $config);
        $receipt_image = $_FILES['receipt_image'];
        $file_type = ($receipt_image['type'] == 'application/pdf') ? 'pdf' : 'image';


        $uploadFile = $this->upload->do_upload('receipt_image');


        //RUN THE UPLOAD
        if ($uploadFile) { // successful upload
            // INSERT TO DATABASE
            $insert_param = [
                'employee' => $employee,
                'date' => $date2,
                'card' => $card,
                'supplier' => $supplier,
                'description' => $description,
                'account' => $account,
                'amount' => $amount,
                'receipt_image' => 'uploads/expenses/' . $filename,
                'file_type' => $file_type,
                'country_id' => $country_id,
                'entered_by' => $loggedin_staff_id
            ];
            $last_insert_expense_id = $this->expensesummary_model->add_expense($insert_param);

            // SET FLASH NOTICES
            $this->session->set_flashdata([
                'success_msg' => 'Expense Added.',
                'status' => 'success'
            ]);
            redirect(base_url('/reports/view_add_expense'));
        }
        $upload_err_msg = strip_tags($this->upload->display_errors());
        $this->session->set_flashdata([
            'error_msg' => 'Unsuccessful.\n' . $upload_err_msg,
            'status' => 'error'
        ]);
        redirect(base_url('/reports/view_add_expense'));
    }

    public function update_expense_action_form_submit() {
        $delete = $this->input->post('delete');
        $employee = $this->input->post('employee');
        $date = $this->input->post('date');
        $date2 = $this->system_model->formatDate($date);
        $card = $this->input->post('card');
        $supplier = $this->input->post('supplier');
        $description = $this->input->post('description');
        $account = $this->input->post('account');
        $amount = $this->input->post('amount');
        $amount_readonly = $this->input->post('amount_readonly');
        $country_id = $this->config->item('country');
        $expense_id = $this->input->post('expense_id');
        $expense_summary_id = $this->input->post('expense_summary_id');
        $redirect_url = $this->input->post('redirect_url');
        $file = pathinfo($_FILES["receipt_image"]['name']);
        $filename = $file['filename'] . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
        $loggedin_staff_id = $this->session->staff_id;
        $config['upload_path'] = './uploads/expenses/';
        $config['allowed_types'] = 'gif|jpg|png|pdf|jpeg';
        $config['max_size'] = 5000;
        $config['file_name'] = $filename;
        $this->load->library('upload', $config);
        $receipt_image = $_FILES['receipt_image'];
        $file_type = ($receipt_image['type'] == 'application/pdf') ? 'pdf' : 'image';
        if ($delete === 'delete') {
            $this->expensesummary_model->delete_expense_record($expense_id);

            if ((int) $expense_summary_id != 0) {
                $this->expensesummary_model->update_expense_summary_record([
                    'total_amount' => '`total_amount`-' . $amount_readonly
                        ], $expense_summary_id, false);
            }
            $this->session->set_flashdata([
                'success_msg' => 'Expense Updated.',
                'status' => 'success'
            ]);
            redirect($redirect_url);
            die();
        }
        $insert_param = [
            'expense_id' => (int) $expense_id,
            'employee' => $employee,
            'date' => $date2,
            'card' => $card,
            'supplier' => $supplier,
            'description' => $description,
            'account' => $account,
            'amount' => $amount,
            'receipt_image' => 'uploads/expenses/' . $filename,
            'file_type' => $file_type,
            'country_id' => $country_id,
            'entered_by' => $loggedin_staff_id
        ];
        if ((int) $_FILES["receipt_image"]['size'] === 0) {
            $uploadFile = true;
            unset($insert_param['receipt_image']);
        } else {
            $uploadFile = $this->upload->do_upload('receipt_image');
        }

        //RUN THE UPLOAD
        if ($uploadFile) { // successful upload
            // INSERT TO DATABASE
            $last_insert_expense_id = $this->expensesummary_model->update_expense_record($insert_param, $expense_id);
            if ((int) $expense_summary_id != 0) {
                $this->expensesummary_model->update_expense_summary_record([
                    'total_amount' => '`total_amount`-' . $amount_readonly . '+' . $amount
                        ], $expense_summary_id, false);
            }
            // SET FLASH NOTICES
            $this->session->set_flashdata([
                'success_msg' => 'Expense Updated.',
                'status' => 'success'
            ]);
            redirect($redirect_url);
            die();
        }
        $upload_err_msg = strip_tags($this->upload->display_errors());
        $this->session->set_flashdata([
            'error_msg' => 'Unsuccessful.\n' . $upload_err_msg,
            'status' => 'error'
        ]);
        redirect($redirect_url);
        die();
    }

    public function add_expense_summary_action_form_submit() {

        $employee = $this->input->post('employee');
        $total_amount = $this->input->post('total_amount');
        $expense_arr = $this->input->post('expense_id');
        $country_id = $this->config->item('country');
        $line_manager = $this->input->post('line_manager');
        $insert_param = [
            'date' => date('Y-m-d'),
            'employee' => $employee,
            'total_amount' => $total_amount,
            'line_manager' => $line_manager,
            'country_id' => $country_id
        ];
        if (!count($expense_arr)) {
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful. Please contact web administrator for assistance.',
                'status' => 'error'
            ]);
            redirect(base_url('/reports/view_expense_summary'));
            die();
        }
        try {
            $expense_summary_id = $this->expensesummary_model->add_expense_summary($insert_param);
//            $expense_summary_id = 42;
            if ($expense_summary_id === 0) {
                $this->session->set_flashdata([
                    'error_msg' => 'Unsuccessful',
                    'status' => 'error'
                ]);
                redirect(base_url('/reports/view_expense_summary'));
                die();
            }

            foreach ($expense_arr as $expense_id) {
                $this->expensesummary_model->update_expense($expense_summary_id, $expense_id);
            }
            $country_id = $this->config->item('country');
            $output = 'S';
            $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'exp.date';
            $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
            $pdf_filename = 'expense_summary_' . date('dmYHis') . '.pdf';
            $jparams = array(
                'sort_list' => array(
                    array(
                        'order_by' => $order_by,
                        'sort' => $sort
                    )
                ),
                'country_id' => $country_id,
                'exp_sum_id' => $expense_summary_id,
                'pdf_filename' => $pdf_filename
            );

            // get country data
            $cntry = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));

// employee
            $params2 = array('staff_id' => $employee);
            $emp = $this->gherxlib->getStaffInfo($params2)->row_array();
            $emp_name = "{$emp['FirstName']} {$emp['LastName']}";

            $lm_params = array('staff_id' => $line_manager);
            $lm = $this->gherxlib->getStaffInfo($lm_params)->row_array();
            $lm_name = "{$lm['FirstName']} {$lm['LastName']}";
            $lm_email = $lm['Email'];
            $country_query = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));
            $e_from = $country_query->outgoing_email;

            $subject = "Expense Summary for {$emp_name}";
            $to = array($this->config->item('sats_accounts_email'), $lm_email);
            $pdf_data = $this->expensesummary_model->get_expense_summary_pdf($jparams, $output);
            $email_data['content'] = "
                    <p>
                            <table style='border:none; margin: 0;'>
                                    <tr><td>Date: </td><td>" . date('d/m/Y') . "</td></tr>
                                    <tr><td>Staff: </td><td>{$emp_name}</td></tr>
                                    <tr><td>Amount: </td><td>$" . number_format($total_amount, 2) . "</td></tr>
                                    <tr><td>Line Manager: </td><td>{$lm_name} <strong style='color:red;'>APPROVAL REQUIRED</strong></td></tr>
                            </table>
                    </p>
                    <p>Please find attached Expense Claim Form</p>
                    ";
            $email_data['email_signature'] = $cntry->email_signature;
            $email_data['trading_name'] = $cntry->trading_name;
            $mail_config = Array(
                'mailtype' => 'html',
                'charset' => 'utf-8'
            );
            $this->email->initialize($mail_config);
            $this->email->set_newline("\r\n");
            $this->email->from($e_from, "SATS - Smoke Alarm Testing Services");
            $this->email->to($to);
            $this->email->subject($subject);
            $e_body = $this->load->view('emails/expense_summary_email', $email_data, TRUE);
            $this->email->attach($pdf_data, 'attachment', $pdf_filename, 'application/pdf');
            $this->email->message($e_body);

            $emailSent = $this->email->send();
            if (!$emailSent) {
                throw new Exception("Statement successfully saved but necessary emails were not broadcasted");
            }

        } catch (Exception $ex) {
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful: ' . $ex->getMessage(),
                'status' => 'error'
            ]);
            redirect(base_url('/reports/view_expense_summary'));
            die();
        }
        $this->session->set_flashdata([
            'success_msg' => 'Expense Summary Added.',
            'status' => 'success'
        ]);
        redirect(base_url('/reports/view_expense_summary'));
    }


    public function weekly_sales_report() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Weekly Sales Report";
        $uri = '/reports/weekly_sales_report';
        $data['uri'] = $uri;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //get/post
        $cron_type = $this->input->get_post('cron_type_filter');
        $data['from'] = ($this->system_model->isDateNotEmpty($this->input->get_post('date_from_filter'))) ? $this->input->get_post('date_from_filter') : date('d/m/Y');
        $data['to'] = ($this->system_model->isDateNotEmpty($this->input->get_post('date_to_filter'))) ? $this->input->get_post('date_to_filter') : date('d/m/Y');

        $db_ready_date_from = date('Y-m-d', strtotime(str_replace('/', '-', $data['from'])));
        $db_ready_date_to = date('Y-m-d', strtotime(str_replace('/', '-', $data['to'])));

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function get_staff_weekly_sales_report_ajax(){

        $staff_id = $this->input->get_post('sa_id');
        echo $this->reports_model->week_sales_report_table_row($staff_id);

    }

    public function no_retest_date() {

        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;

        $join_table_url_params = $this->input->get_post('joinAgencyTable');

        $custom_where = "p.retest_date IS NULL";

        $params = array(
            'sel_query' => "DISTINCT(p.property_id) as property_id, p.address_1 as p_address1, p.address_2 as p_address2, p.address_3 as p_address3, p.state as p_state, p.postcode as p_postcode",
            'custom_where' => $custom_where,
            'limit' => $per_page,
            'offset' => $offset,
            'display_query' => 0
        );

        //add agency join table if joinAgencyTable = yes
        if($join_table_url_params!="" && $join_table_url_params=='yes'){
            $params['join_table'] = array('agency_table');
        }

        $data['lists'] =  $this->reports_model->get_null_retest_date($params);
        $data['sql_query'] = $this->db->last_query(); //Show query on About

        //total rows
        $paramstot = array(
            'sel_query' => "DISTINCT(p.property_id)",
            'custom_where' => $custom_where,
            'display_query' => 0
        );

        //add agency join table if joinAgencyTable = yes
        if($join_table_url_params!="" && $join_table_url_params=='yes'){
            $paramstot['join_table'] = array('agency_table');
        }

        $querytot =  $this->reports_model->get_null_retest_date($paramstot);
        $total_rows = $querytot->num_rows();

        //job type dropdown
        $this->db->select('*');
        $this->db->from('job_type');
        $job_type_query = $this->db->get();
        $data['job_type'] = $job_type_query;

       /* $query = $this->reports_model->get_null_retest_date($offset,$per_page);

        $data['lists'] = $query;

        $querytot = $this->reports_model->get_null_retest_date('','');
        $total_rows = $querytot->num_rows();
        */

        /*
        //main list
        $data['lists'] = $this->reports_model->get_nsw_property_report($offset, $per_page);

        $query = $this->reports_model->get_nsw_property_report('','');
        $total_rows = $query->num_rows();
        */

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = "/reports/no_retest_date";

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
        $data['title'] = "No Retest Date";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/nsw_property_report', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_get_last_completed_job(){

        $property_id = $this->input->post('prop_id');

        $this->db->select('j.id,j.created,j.date,j.status,j.job_type,p.property_id as p_prop_id');
        $this->db->from('jobs as j');
        $this->db->join('property as p','p.property_id = j.property_id','left');
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');
        $this->db->where('a.`country_id`', $this->config->item('country'));
        $this->db->where('j.`del_job`', 0);
        $this->db->where('j.status','Completed');
        $this->db->where('j.`property_id`', $property_id);
        $this->db->order_by('j.created','DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        $row = $query->row_array();
        $created =  $row['created'];

        $date1 = date_create(date('Y-m-d', strtotime($created)));
        $date2 = date_create(date('Y-m-d'));
        $diff = date_diff($date1, $date2);
        $age = $diff->format("%r%a");

        if($query->num_rows()>0){
            $age_val = (((int) $age) != 0) ? $age : 0;
        }else{
            $age_val = NULL;
        }

        $job_type = $row['job_type'];
        $job_url = $this->gherxlib->crmLink('vjd',$row['id'], $job_type);

        //table start
        echo "<table data-jobtype='{$job_type}' class='awo' style='margin:0;padding;0;width:100%;border:0;'><tr><td style='width:200px;'>";
        echo $job_url;
        echo "</td>";

        echo "<td style='width:150px;'>";
        echo $age_val;
        echo "</td></tr></table>";
        //table end

    }

    public function no_retest_date_property(){

        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;
        $page_url = '/reports/no_retest_date_property';

        $last_90_days = date('Y-m-d', strtotime(date('Y-m-d').' -90 days'));
        $custom_where = "p.retest_date IS NULL AND CAST(p.`created` AS DATE ) < '{$last_90_days}' AND `is_nlm` != 1";

        $params = array(
            'sel_query' => "DISTINCT(p.property_id) as property_id, p.address_1 as p_address1, p.address_2 as p_address2, p.address_3 as p_address3, p.state as p_state, p.postcode as p_postcode",
            'custom_where' => $custom_where,
            'join_table' => array('agency_table'),
            'limit' => $per_page,
            'offset' => $offset,
            'display_query' => 0
        );

        $data['lists'] =  $this->reports_model->get_null_retest_date($params);

        //total rows
        $paramstot = array(
            'sel_query' => "DISTINCT(p.property_id)",
            'custom_where' => $custom_where,
            'join_table' => array('agency_table'),
            'display_query' => 0
        );
        $querytot =  $this->reports_model->get_null_retest_date($paramstot);
        $total_rows = $querytot->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = "/reports/no_retest_date_property";

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
        $data['title'] = "No Retest Date Property";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/no_retest_date_property', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function tech_break_report(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Tech Break Report";
        $page_url = '/reports/tech_break_report';

        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;

        $tech = $this->input->get_post('tech');
        $tb_start = ( $this->input->get_post('tb_start') != '' )?$this->system_model->formatDate($this->input->get_post('tb_start')):date('Y-m-d');
        $tb_taken = $this->input->get_post('tb_taken');

        $staff_class = 6; // Techs

        if( isset($tb_start) ){
            $custom_where_arr[] = "CAST(tb.tech_break_start AS Date) = '{$tb_start}'";
        }

        // paginatated row
        $sel_query = "
            sa.`StaffID`,
            sa.`FirstName`,
            sa.`LastName`,

            tb.`tech_break_id`,
            tb.`tech_id`,
            tb.`tech_break_start`,
            tb.`tech_break_taken`,
        ";

        $tb_params = array(
            'sel_query' => $sel_query,
            'staff_class' => $staff_class,
            'custom_where_arr' => $custom_where_arr,
            'tech' => $tech,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                ),
                array(
                    'order_by' => 'sa.LastName',
                    'sort' => 'ASC'
                )
            ),
            'limit' => $per_page,
            'offset' => $offset,
            'display_query' => 0
        );

        $data['tb_sql'] =  $this->reports_model->get_tech_break($tb_params);

        // total rows
        $tb_params = array(
            'sel_query' => "COUNT(tb.tech_break_id) AS tb_count",
            'staff_class' => $staff_class,
            'custom_where_arr' => $custom_where_arr,
            'tech' => $tech,
            'display_query' => 0
        );
        $querytot =  $this->reports_model->get_tech_break($tb_params);
        $total_rows = $querytot->row()->tb_count;

        // DISTINCT tech
        $tb_params = array(
            'sel_query' => "DISTINCT(sa.`StaffID`), sa.`FirstName`, sa.`LastName`",
            'staff_class' => $staff_class,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                ),
                array(
                    'order_by' => 'sa.LastName',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );
        $data['tech_filter_sql'] = $this->reports_model->get_tech_break($tb_params);


        $pagi_links_params_arr = array(
            'tech' => $tech,
            'tb_start' => $tb_start,
            'tb_taken' => $tb_taken
        );
        $pagi_link_params = $page_url.'/?'.http_build_query($pagi_links_params_arr);

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
        $this->load->view('reports/tech_break_report', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function agency_expiring_alarms() {

		$this->load->model('jobs_model');
		$this->load->model('tech_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Expiring Alarms";
        $uri = '/reports/agency_expiring_alarms';
        $data['uri'] = $uri;

        $agency_filter = $this->db->escape_str($this->input->get_post('agency_filter'));
        $alarm_expiry = $this->db->escape_str($this->input->get_post('alarm_expiry'));
        $btn_search = $this->db->escape_str($this->input->get_post('btn_search'));
        $country_id = $this->config->item('country');

        $query_filter = null;

        // pagination
		$per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        if( $agency_filter > 0 ){
            $query_filter .= " AND a.`agency_id` = {$agency_filter} ";
        }

        if( $alarm_expiry != '' ){
            $query_filter .= " AND al.`expiry` = '{$alarm_expiry}' ";
        }

        if( $agency_filter > 0 && $alarm_expiry != '' ){

            // main listing
            $list_sql_str = "
                SELECT al_pwr.`alarm_pwr`, al.`alarm_power_id`, COUNT(al.`alarm_id`) AS al_qty
                FROM `alarm` AS al
                LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                LEFT JOIN `jobs` AS j ON al.`job_id` = j.`id`
                INNER JOIN (

                    SELECT j_inner.property_id, MAX(j_inner.date) AS latest_date
                    FROM `jobs` AS j_inner
                    LEFT JOIN `property` AS p_inner ON j_inner.`property_id` = p_inner.`property_id`
                    LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id`
                    WHERE j_inner.`del_job` = 0
                    AND p_inner.`deleted` = 0
                    AND a_inner.`status` = 'active'
                    AND j_inner.`status` = 'Completed'
                    AND j_inner.`assigned_tech` != 1
                    AND j_inner.`assigned_tech` != 2
                    GROUP BY j_inner.`property_id` DESC

                ) AS j3 ON ( j.property_id = j3.property_id AND j.date = j3.latest_date )
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE j.`del_job` = 0
                AND p.`deleted` = 0
                AND a.`status` = 'active'
                AND al.`ts_discarded` = 0
                AND j.`assigned_tech` != 1
                AND j.`assigned_tech` != 2
                AND a.deleted = 0
                {$query_filter}
                GROUP BY al.`alarm_power_id`
            ";
            $data['list_sql'] = $this->db->query($list_sql_str);


            // get agency alarms
            $agen_al_sql_str = "
            SELECT agen_al.`price`, al_pwr.`alarm_pwr`
            FROM `agency_alarms` AS agen_al
            LEFT JOIN `alarm_pwr` AS al_pwr ON agen_al.`alarm_pwr_id` = al_pwr.`alarm_pwr_id`
            WHERE agen_al.`agency_id` = {$agency_filter}
            ";
            $data['agen_al_sql'] = $this->db->query($agen_al_sql_str);


        }

        // agency filter
        $data['agency_filter_sql'] = $this->db->query("
            SELECT `agency_id`, `agency_name`
            FROM `agency`
            WHERE `status` = 'active'
            AND `deleted` = 0
            ORDER BY `agency_name`
        ");

        /*
        // get total row
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where'=> $custom_where,

            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'date' => $date_filter,
            'country_id' => $country_id,

            'join_table' => array('job_type','alarm_job_type'),
            'display_query' => 0
        );
        $job_sql = $this->jobs_model->get_jobs($params);
        $total_rows = $job_sql->row()->jcount;
        $data['total_job_count'] = $job_sql->row()->jcount;


        $pagi_links_params_arr = array(
            'date_filter' => $date_filter
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
		*/


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function agency_expiring_alarms_hume() {

        $this->load->model('jobs_model');
        $this->load->model('tech_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Expiring Alarms - Hume";
        $uri = '/reports/agency_expiring_alarms_hume';
        $data['uri'] = $uri;

        $pm_filter = $this->db->escape_str($this->input->get_post('pm_filter'));
        $alarm_expiry = $this->db->escape_str($this->input->get_post('alarm_expiry'));

        $country_id = $this->config->item('country');

        $query_filter = null;
        $query_filter_inner = null;
        $limit_str = null;

        $agency_id = 1598; // Hume Housing
        //$agency_id = 1448; // Adams

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        if( $country_id == 1 ){ // only on AU


            if( $pm_filter != '' ){
                $query_filter .= " AND p.`pm_id_new` = '{$pm_filter}' ";
            }

            if( $alarm_expiry != '' ){

                $query_filter .= " AND al.`expiry` = '{$alarm_expiry}' ";


                // main listing
                $list_sql_str = "
                SELECT
                    COUNT(al.`alarm_id`) AS al_qty,
                    COUNT(
                        CASE
                            WHEN al.`alarm_power_id` = 1
                            OR al.`alarm_power_id` = 3
                            OR al.`alarm_power_id` = 5
                            OR al.`alarm_power_id` = 6
                            OR al.`alarm_power_id` = 7
                            OR al.`alarm_power_id` = 8
                            OR al.`alarm_power_id` = 12
                            OR al.`alarm_power_id` = 13
                            THEN al.`alarm_id`
                        END
                    )  AS al_9v_count,
                    COUNT(
                        CASE
                            WHEN al.`alarm_power_id` = 2
                            OR al.`alarm_power_id` = 4
                            OR al.`alarm_power_id` = 9
                            OR al.`alarm_power_id` = 10
                            OR al.`alarm_power_id` = 11
                            OR al.`alarm_power_id` = 14
                            THEN al.`alarm_id`
                        END
                    ) AS al_240v_count,

                    p.`property_id`,
                    p.`address_1` AS p_street_num,
                    p.`address_2` AS p_street_name,
                    p.`address_3` AS p_suburb,
                    p.`state` AS p_state,
                    p.`postcode` AS p_postcode,
                    p.`pm_id_new`,

                    pm.`agency_user_account_id` AS aua_id,
                    pm.`fname` AS pm_fname,
                    pm.`lname` AS pm_lname
                FROM `alarm` AS al
                LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                LEFT JOIN `jobs` AS j ON al.`job_id` = j.`id`
                INNER JOIN (

                    SELECT j_inner.property_id, MAX(j_inner.date) AS latest_date
                    FROM `jobs` AS j_inner
                    LEFT JOIN `property` AS p_inner ON j_inner.`property_id` = p_inner.`property_id`
                    LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id`
                    WHERE j_inner.`del_job` = 0
                    AND p_inner.`deleted` = 0
                    AND a_inner.`status` = 'active'
                    AND j_inner.`status` = 'Completed'
                    AND j_inner.`assigned_tech` NOT IN(1,2)
                    AND a_inner.`agency_id` = {$agency_id}
                    GROUP BY j_inner.`property_id` DESC

                ) AS j3 ON ( j.property_id = j3.property_id AND j.date = j3.latest_date )
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `agency_user_accounts` AS pm ON p.`pm_id_new` = pm.`agency_user_account_id`
                WHERE j.`del_job` = 0
                AND p.`deleted` = 0
                AND a.`status` = 'active'
                AND a.deleted = 0
                AND al.`ts_discarded` = 0
                AND j.`assigned_tech` NOT IN(1,2)
                AND a.`agency_id` = {$agency_id}
                {$query_filter}
                GROUP BY p.`property_id`
                LIMIT {$offset}, {$per_page}
                ";
                $data['list_sql'] = $this->db->query($list_sql_str);


                // get total
                $list_sql_str = "
                SELECT COUNT(al.`alarm_id`) AS al_qty
                FROM `alarm` AS al
                LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                LEFT JOIN `jobs` AS j ON al.`job_id` = j.`id`
                INNER JOIN (

                    SELECT j_inner.property_id, MAX(j_inner.date) AS latest_date
                    FROM `jobs` AS j_inner
                    LEFT JOIN `property` AS p_inner ON j_inner.`property_id` = p_inner.`property_id`
                    LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id`
                    WHERE j_inner.`del_job` = 0
                    AND p_inner.`deleted` = 0
                    AND a_inner.`status` = 'active'
                    AND j_inner.`status` = 'Completed'
                    AND j_inner.`assigned_tech` NOT IN(1,2)
                    AND a_inner.`agency_id` = {$agency_id}
                    GROUP BY j_inner.`property_id` DESC

                ) AS j3 ON ( j.property_id = j3.property_id AND j.date = j3.latest_date )
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `agency_user_accounts` AS pm ON p.`pm_id_new` = pm.`agency_user_account_id`
                WHERE j.`del_job` = 0
                AND p.`deleted` = 0
                AND a.`status` = 'active'
                AND al.`ts_discarded` = 0
                AND j.`assigned_tech` NOT IN(1,2)
                AND a.`agency_id` = {$agency_id}
                {$query_filter}
                GROUP BY p.`property_id`
                ";
                $list_sql = $this->db->query($list_sql_str);
                $total_rows = $list_sql->num_rows();


                // get expiring alarm total
                $list_sql_str = "
                SELECT
                    COUNT(al.`alarm_id`) AS al_qty,
                    COUNT(
                        CASE
                            WHEN al.`alarm_power_id` = 1
                            OR al.`alarm_power_id` = 3
                            OR al.`alarm_power_id` = 5
                            OR al.`alarm_power_id` = 6
                            OR al.`alarm_power_id` = 7
                            OR al.`alarm_power_id` = 8
                            OR al.`alarm_power_id` = 12
                            OR al.`alarm_power_id` = 13
                            THEN al.`alarm_id`
                        END
                    )  AS al_9v_count,
                    COUNT(
                        CASE
                            WHEN al.`alarm_power_id` = 2
                            OR al.`alarm_power_id` = 4
                            OR al.`alarm_power_id` = 9
                            OR al.`alarm_power_id` = 10
                            OR al.`alarm_power_id` = 11
                            OR al.`alarm_power_id` = 14
                            THEN al.`alarm_id`
                        END
                    ) AS al_240v_count
                FROM `alarm` AS al
                LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                LEFT JOIN `jobs` AS j ON al.`job_id` = j.`id`
                INNER JOIN (

                    SELECT j_inner.property_id, MAX(j_inner.date) AS latest_date
                    FROM `jobs` AS j_inner
                    LEFT JOIN `property` AS p_inner ON j_inner.`property_id` = p_inner.`property_id`
                    LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id`
                    WHERE j_inner.`del_job` = 0
                    AND p_inner.`deleted` = 0
                    AND a_inner.`status` = 'active'
                    AND j_inner.`status` = 'Completed'
                    AND j_inner.`assigned_tech` NOT IN(1,2)
                    AND a_inner.`agency_id` = {$agency_id}
                    GROUP BY j_inner.`property_id` DESC

                ) AS j3 ON ( j.property_id = j3.property_id AND j.date = j3.latest_date )
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `agency_user_accounts` AS pm ON p.`pm_id_new` = pm.`agency_user_account_id`
                WHERE j.`del_job` = 0
                AND p.`deleted` = 0
                AND a.`status` = 'active'
                AND al.`ts_discarded` = 0
                AND j.`assigned_tech` NOT IN(1,2)
                AND a.`agency_id` = {$agency_id}
                {$query_filter}
                ";
                $list_sql = $this->db->query($list_sql_str);
                $data['tot_exp_al'] = $list_sql->row()->al_qty;
                $data['tot_exp_9v'] = $list_sql->row()->al_9v_count;
                $data['tot_exp_240v'] = $list_sql->row()->al_240v_count;

            }


            $pagi_links_params_arr = array(
                'pm_filter' => $pm_filter,
                'alarm_expiry' => $this->input->get_post('alarm_expiry')
            );
            $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);

            // get property managers
            $data['pm_sql'] = $this->db->query("
            SELECT `agency_user_account_id` AS aua_id, `fname`, `lname`
            FROM `agency_user_accounts`
            WHERE `agency_id` = {$agency_id}
            ORDER BY `fname`, `lname`
            ");


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
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);

        }

    }

    public function tech_tracking(){

        $tech_id = $this->input->get_post('tech_id');
        $tr_id = $this->input->get_post('tr_id');

        $data['start_load_time'] = microtime(true);
        $uri = "/reports/tech_tracking/?tech_id={$tech_id}&tr_id={$tr_id}";
        $data['uri'] = $uri;
        $today = date('Y-m-d');

        if( $tech_id > 0 ){

            // get logged staff name
            $params = array(
                'sel_query' => '
                    sa.`StaffID`,
                    sa.`FirstName`,
                    sa.`LastName`,
                    sa.`profile_pic`
                ',
                'staff_id' => $tech_id,
                'active' => 1,
                'deleted' => 0,
                'display_query' => 0
            );

            // get user details
            $user_account_sql = $this->staff_accounts_model->get_staff_accounts($params);
            $user_account = $user_account_sql->row();
            $tech_name = $this->system_model->formatStaffName($user_account->FirstName,$user_account->LastName);

            $data['title'] = "Tech Tracking - {$tech_name}";

            // tech locations
            $tl_sql_str = "
            SELECT *
            FROM `tech_locations` AS tl
            WHERE tl.`tech_id` = {$tech_id}
            AND Date(tl.`created`) = '{$today}'
            ORDER BY tl.`created` ASC
            LIMIT 10
            ";

            $tl_sql = $this->db->query($tl_sql_str);
            $tech_loc = [];
            foreach( $tl_sql->result() as $tl_row ){

                // convert to string
                $tech_loc[] = array(
                    'lat' => (string)$tl_row->latitude,
                    'lng' => (string)$tl_row->longitude
                );

            }

            $data['tech_loc'] = $tech_loc;

        }

        $data['is_tech_run_map'] = true; // used to load different type of google map script

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/tech_tracking', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function qld_not_upgraded() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "QLD Properties Requiring Upgrade";
        $country_id = $this->config->item('country');
        $uri = '/reports/qld_not_upgraded';
        $data['uri'] = $uri;

        $agency_id = $this->input->get_post('agency_filter');
        $date_filter_str = null;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        $agency_filter = null;


        if( $agency_id > 0 ){
            $agency_filter = "AND a.`agency_id` = {$agency_id}";
        }

        // get paginated list
        $property_sql_str = "
            SELECT
                p.`property_id`,
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,
                p.`qld_new_leg_alarm_num`,

                a.`agency_id`,
                a.`agency_name`,
                aght.priority,
                apmd.abbreviation
            FROM `property` AS p
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
            WHERE p.`qld_new_leg_alarm_num` > 0
            AND p.`prop_upgraded_to_ic_sa` != 1
            AND p.`state` = 'QLD'
            AND p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            {$agency_filter}
            LIMIT {$offset}, {$per_page}
        ";
        $data['list'] = $this->db->query($property_sql_str);

        // get all
        $renewals_sql_str = "
            SELECT COUNT(p.`property_id`) AS p_count
            FROM `property` AS p
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE p.`qld_new_leg_alarm_num` > 0
            AND p.`prop_upgraded_to_ic_sa` != 1
            AND p.`state` = 'QLD'
            AND p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            {$agency_filter}
        ";
        $renewals_sql = $this->db->query($renewals_sql_str);
        $renewals_row = $renewals_sql->row();
        $total_rows = $renewals_row->p_count;
        $data['job_created_count'] = $renewals_row->job_created_count;


        // dinstinct agency
        $distinct_agency_sql_str = "
            SELECT DISTINCT(p.`agency_id`), a.`agency_name`
            FROM `property` AS p
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE p.`qld_new_leg_alarm_num` > 0
            AND p.`prop_upgraded_to_ic_sa` != 1
            AND p.`state` = 'QLD'
            AND p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            {$agency_filter}
        ";
        $data['distinct_agency_sql'] = $this->db->query($distinct_agency_sql_str);

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_id
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
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function most_recent_job_property_report() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Most Recent Job Property Report";
        $country_id = $this->config->item('country');
        $uri = '/reports/most_recent_job_property_report';
        $data['uri'] = $uri;

        $agency_filter_arr = $this->input->get_post('agency_filter_arr');
        $date_filter_str = null;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'j.date';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        $export = $this->input->get_post('export');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        $agency_filter = null;

        // agency filter
        if( count($agency_filter_arr) > 0 ){

            $agency_filter_imp = implode(",",$agency_filter_arr);

            if( $agency_filter_imp != '' ){
                $agency_filter_inner = "AND a_inner.`agency_id` IN({$agency_filter_imp})";
                $agency_filter = "AND a.`agency_id` IN({$agency_filter_imp})";
            }      

        }

        // sort
        $order_by_str = null;
        if( $order_by == 'p.address_2' ){
            $order_by_str = "ORDER BY {$order_by} {$sort}, p.address_3 ASC";
        }else{
            $order_by_str = "ORDER BY {$order_by} {$sort}";
        }

        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 ){ 
            $limit_sql_str = "LIMIT {$offset}, {$per_page}";
        }

        // get paginated list
        $property_sql_str = "
            SELECT 
                j.`id` AS jid,
                j.`job_type`,
                j.`status`,
                j.`date` AS jdate,

                ajt.`id` AS ajt_id,
                ajt.`type` AS ajt_type,

                p.`property_id`,
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,

                ps.`property_services_id`,
                ps.`alarm_job_type_id`,
                ps.`service`,

                a.`agency_id`,
                a.`agency_name`,
                aght.priority,
                apmd.abbreviation
            FROM jobs AS j
            INNER JOIN (

                SELECT j_inner.property_id, MAX(j_inner.date) AS latest_date
                FROM jobs AS j_inner	
                INNER JOIN property AS p_inner ON j_inner.property_id = p_inner.property_id		
                INNER JOIN agency AS a_inner ON p_inner.agency_id = a_inner.agency_id
                INNER JOIN `property_services` AS ps_inner ON ( j_inner.`property_id` = ps_inner.`property_id` AND j_inner.`service` = ps_inner.`alarm_job_type_id` )
                WHERE j_inner.del_job = 0
                AND j_inner.status = 'Completed'
                AND p_inner.deleted = 0
                AND a_inner.status = 'active'	
                AND ps_inner.service = 1
                {$agency_filter_inner}
                GROUP BY j_inner.property_id 

            ) AS j_inner_query ON ( j.property_id = j_inner_query.property_id AND j.date = j_inner_query.latest_date )
            INNER JOIN property AS p ON j.property_id = p.property_id
            INNER JOIN agency AS a ON p.agency_id = a.agency_id
            INNER JOIN agency_priority AS aght ON a.agency_id = aght.agency_id
            INNER JOIN agency_priority_marker_definition AS apmd ON aght.priority = apmd.priority
            INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` )
            INNER JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            WHERE j.del_job = 0
            AND j.status = 'Completed'
            AND p.deleted = 0
            AND a.status = 'active'
            AND ps.service = 1
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            {$agency_filter}
            {$order_by_str}          
            {$limit_sql_str}
        ";

        $job_sql = $this->db->query($property_sql_str);
        $data['sql_query'] = $this->db->last_query(); //Show query on About
        
        if ($export == 1) { //EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "most_recent_job_property_report_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = array("Address","State","Service","Recent Job","Job Date","Job Type","Agency");
            fputcsv($csv_file, $header);
            
            foreach ($job_sql->result() as $row){ 

                $csv_row = [];                              

                $csv_row[] = "{$row->address_1} {$row->address_2}, {$row->address_3}";
                $csv_row[] = $row->state;
                $csv_row[] = $row->ajt_type;
                $csv_row[] = "#{$row->jid}";
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row->jdate) )?date('d/m/Y', strtotime($row->jdate)):null;
                $csv_row[] = $row->job_type;
                $csv_row[] = $row->agency_name;               
                
                fputcsv($csv_file,$csv_row); 

            }
        
            fclose($csv_file); 
            exit; 

        }else{

            $data['list'] = $job_sql;

            // get all
            $property_sql_str = "
                SELECT COUNT(j.`id`) AS jcount
                FROM jobs AS j
                INNER JOIN (

                    SELECT j_inner.property_id, MAX(j_inner.date) AS latest_date
                    FROM jobs AS j_inner	
                    INNER JOIN property AS p_inner ON j_inner.property_id = p_inner.property_id		
                    INNER JOIN agency AS a_inner ON p_inner.agency_id = a_inner.agency_id
                    INNER JOIN `property_services` AS ps_inner ON ( j_inner.`property_id` = ps_inner.`property_id` AND j_inner.`service` = ps_inner.`alarm_job_type_id` )
                    WHERE j_inner.del_job = 0
                    AND j_inner.status = 'Completed'
                    AND p_inner.deleted = 0
                    AND a_inner.status = 'active'	
                    AND ps_inner.service = 1
                    {$agency_filter_inner}
                    GROUP BY j_inner.property_id 

                ) AS j_inner_query ON ( j.property_id = j_inner_query.property_id AND j.date = j_inner_query.latest_date )
                INNER JOIN property AS p ON j.property_id = p.property_id
                INNER JOIN agency AS a ON p.agency_id = a.agency_id
                INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` )
                INNER JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
                WHERE j.del_job = 0
                AND j.status = 'Completed'
                AND p.deleted = 0
                AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
                AND a.status = 'active'
                AND ps.service = 1  
                {$agency_filter}                
            ";
            $property_sql = $this->db->query($property_sql_str);
            $total_rows = $property_sql->row()->jcount;                

            // dinstinct agency
            $distinct_agency_sql_str = "            
                SELECT DISTINCT(p.`agency_id`), a.`agency_name`
                FROM jobs AS j
                INNER JOIN (

                    SELECT j_inner.property_id, MAX(j_inner.date) AS latest_date
                    FROM jobs AS j_inner	
                    INNER JOIN property AS p_inner ON j_inner.property_id = p_inner.property_id		
                    INNER JOIN agency AS a_inner ON p_inner.agency_id = a_inner.agency_id
                    INNER JOIN `property_services` AS ps_inner ON ( j_inner.`property_id` = ps_inner.`property_id` AND j_inner.`service` = ps_inner.`alarm_job_type_id` )
                    WHERE j_inner.del_job = 0
                    AND j_inner.status = 'Completed'
                    AND p_inner.deleted = 0
                    AND a_inner.status = 'active'	
                    AND ps_inner.service = 1                    
                    GROUP BY j_inner.property_id 

                ) AS j_inner_query ON ( j.property_id = j_inner_query.property_id AND j.date = j_inner_query.latest_date )
                INNER JOIN property AS p ON j.property_id = p.property_id
                INNER JOIN agency AS a ON p.agency_id = a.agency_id
                INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` )
                INNER JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
                WHERE j.del_job = 0
                AND j.status = 'Completed'
                AND p.deleted = 0
                AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
                AND a.status = 'active'
                AND ps.service = 1  
            ";
            $data['distinct_agency_sql'] = $this->db->query($distinct_agency_sql_str);

            $pagi_links_params_arr = array(
                'agency_filter_arr' => $agency_filter_arr
            );
            
            $data['header_link_params'] = $pagi_links_params_arr;
            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  

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
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);

        }
                

    }


    public function active_property_yet_to_visit() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Active Property, yet to Visit";
        $country_id = $this->config->item('country');
        $uri = '/reports/active_property_yet_to_visit';
        $data['uri'] = $uri;

        $agency_filter_arr = $this->input->get_post('agency_filter_arr');
        $date_filter_str = null;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'j.date';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        $export = $this->input->get_post('export');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        $agency_filter = null;

        // agency filter
        if( count($agency_filter_arr) > 0 ){

            $agency_filter_imp = implode(",",$agency_filter_arr);

            if( $agency_filter_imp != '' ){
                $agency_filter_inner = "AND a_inner.`agency_id` IN({$agency_filter_imp})";
                $agency_filter = "AND a.`agency_id` IN({$agency_filter_imp})";
            }      

        }

        // sort
        $order_by_str = null;
        if( $order_by == 'p.address_2' ){
            $order_by_str = "ORDER BY {$order_by} {$sort}, p.address_3 ASC";
        }else{
            $order_by_str = "ORDER BY {$order_by} {$sort}";
        }

        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 ){ 
            $limit_sql_str = "LIMIT {$offset}, {$per_page}";
        }

        // get paginated list
        $property_sql_str = "
            SELECT 
                j.`id` AS jid,
                j.`job_type`,
                j.`status`,
                j.`date` AS jdate,

                ajt.`id` AS ajt_id,
                ajt.`type` AS ajt_type,

                p.`property_id`,
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,

                ps.`property_services_id`,
                ps.`alarm_job_type_id`,
                ps.`service`,

                a.`agency_id`,
                a.`agency_name`,
                aght.priority,
                apmd.abbreviation
            FROM jobs AS j
            INNER JOIN property AS p ON j.property_id = p.property_id
            INNER JOIN agency AS a ON p.agency_id = a.agency_id
            INNER JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
            INNER JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
            INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` )
            INNER JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            WHERE j.del_job = 0            
            AND p.deleted = 0
            AND a.status = 'active'
            AND ps.service = 1
            AND j.`property_id` NOT IN(

                SELECT j_inner.property_id
                FROM jobs AS j_inner	
                INNER JOIN property AS p_inner ON j_inner.property_id = p_inner.property_id		
                INNER JOIN agency AS a_inner ON p_inner.agency_id = a_inner.agency_id
                INNER JOIN `property_services` AS ps_inner ON ( j_inner.`property_id` = ps_inner.`property_id` AND j_inner.`service` = ps_inner.`alarm_job_type_id` )
                WHERE j_inner.del_job = 0
                AND j_inner.status = 'Completed'
                AND p_inner.deleted = 0
                AND a_inner.status = 'active'	
                AND ps_inner.service = 1
                {$agency_filter_inner}
                GROUP BY j_inner.property_id 

            )
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            {$agency_filter}
            {$order_by_str}          
            {$limit_sql_str}
        ";

        $job_sql = $this->db->query($property_sql_str);
        $data['sql_query'] = $this->db->last_query(); //Show query on About

        if ($export == 1) { //EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "most_recent_job_property_report_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = array("Address","State","Service","Recent Job","Job Date","Job Type","Agency");
            fputcsv($csv_file, $header);
            
            foreach ($job_sql->result() as $row){ 

                $csv_row = [];                              

                $csv_row[] = "{$row->address_1} {$row->address_2}, {$row->address_3}";
                $csv_row[] = $row->state;
                $csv_row[] = $row->ajt_type;
                $csv_row[] = "#{$row->jid}";
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row->jdate) )?date('d/m/Y', strtotime($row->jdate)):null;
                $csv_row[] = $row->job_type;
                $csv_row[] = $row->agency_name;               
                
                fputcsv($csv_file,$csv_row); 

            }
        
            fclose($csv_file); 
            exit; 

        }else{

            $data['list'] = $job_sql;

            // get all
            $property_sql_str = "
                SELECT COUNT(j.`id`) AS jcount
                FROM jobs AS j
                INNER JOIN property AS p ON j.property_id = p.property_id
                INNER JOIN agency AS a ON p.agency_id = a.agency_id
                INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` )
                INNER JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
                WHERE j.del_job = 0            
                AND p.deleted = 0
                AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
                AND a.status = 'active'
                AND ps.service = 1
                AND j.`property_id` NOT IN(
    
                    SELECT j_inner.property_id
                    FROM jobs AS j_inner	
                    INNER JOIN property AS p_inner ON j_inner.property_id = p_inner.property_id		
                    INNER JOIN agency AS a_inner ON p_inner.agency_id = a_inner.agency_id
                    INNER JOIN `property_services` AS ps_inner ON ( j_inner.`property_id` = ps_inner.`property_id` AND j_inner.`service` = ps_inner.`alarm_job_type_id` )
                    WHERE j_inner.del_job = 0
                    AND j_inner.status = 'Completed'
                    AND p_inner.deleted = 0
                    AND a_inner.status = 'active'	
                    AND ps_inner.service = 1
                    {$agency_filter_inner}
                    GROUP BY j_inner.property_id 
    
                )
                {$agency_filter}                
            ";
            $property_sql = $this->db->query($property_sql_str);
            $total_rows = $property_sql->row()->jcount;                

            // dinstinct agency
            $distinct_agency_sql_str = "            
                SELECT DISTINCT(p.`agency_id`), a.`agency_name`
                FROM jobs AS j
                INNER JOIN property AS p ON j.property_id = p.property_id
                INNER JOIN agency AS a ON p.agency_id = a.agency_id
                INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` )
                INNER JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
                WHERE j.del_job = 0            
                AND p.deleted = 0
                AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
                AND a.status = 'active'
                AND ps.service = 1
                AND j.`property_id` NOT IN(
    
                    SELECT j_inner.property_id
                    FROM jobs AS j_inner	
                    INNER JOIN property AS p_inner ON j_inner.property_id = p_inner.property_id		
                    INNER JOIN agency AS a_inner ON p_inner.agency_id = a_inner.agency_id
                    INNER JOIN `property_services` AS ps_inner ON ( j_inner.`property_id` = ps_inner.`property_id` AND j_inner.`service` = ps_inner.`alarm_job_type_id` )
                    WHERE j_inner.del_job = 0
                    AND j_inner.status = 'Completed'
                    AND p_inner.deleted = 0
                    AND a_inner.status = 'active'	
                    AND ps_inner.service = 1                    
                    GROUP BY j_inner.property_id 
    
                )
            ";
            $data['distinct_agency_sql'] = $this->db->query($distinct_agency_sql_str);

            $pagi_links_params_arr = array(
                'agency_filter_arr' => $agency_filter_arr
            );
            
            $data['header_link_params'] = $pagi_links_params_arr;
            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  

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
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);

        }
                

    }


    public function property_commissions() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Property Commissions";
        $country_id = $this->config->item('country');
        $uri = '/reports/property_commissions';
        $data['uri'] = $uri;

        $salesrep_filter = $this->input->get_post('salesrep_filter');
        $service_type_filter = $this->input->get_post('service_type_filter');   
        $status_changed_from = ( $this->input->get_post('status_changed_from') !='' )?$this->system_model->formatDate($this->input->get_post('status_changed_from')):date('Y-m-01');
        $status_changed_to = ( $this->input->get_post('status_changed_to') !='' )?$this->system_model->formatDate($this->input->get_post('status_changed_to')):date('Y-m-t');     
        $is_payable_filter = $this->input->get_post('is_payable_filter');
        //$ver = $this->input->get_post('ver');

        $data['status_changed_from'] = $status_changed_from;
        $data['status_changed_to'] = $status_changed_to;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'ps.status_changed';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        $export = $this->input->get_post('export');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        // sort
        $order_by_str = null;
        if( $order_by == 'p.address_2' ){
            $order_by_str = "ORDER BY {$order_by} {$sort}, p.address_3 ASC";
        }else{
            $order_by_str = "ORDER BY {$order_by} {$sort}";
        }
        

        // salerep filter
        $salesrep_filter_str = null;
        if( $salesrep_filter > 0 ){
            $salesrep_filter_str = "AND a.`salesrep` = {$salesrep_filter}";   
        }

        // salerep filter
        $service_type_str = null;
        if( $service_type_filter > 0 ){
            $service_type_str = "AND ps.`alarm_job_type_id` = {$service_type_filter}";   
        }

        // salerep filter
        $is_payable_filter_str = null;
        if( is_numeric($is_payable_filter) ){
            $is_payable_filter_str = "AND ps.`is_payable` = {$is_payable_filter}";   
        }

        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 ){ 
            $limit_sql_str = "LIMIT {$offset}, {$per_page}";
        }

        /*
        // switch to old and new version/style
        $ver_switch_str = null;
        if( $ver == 'new' ){
            $ver_switch_str = "AND ps.`is_payable` = 1"; 
        }else{
            $ver_switch_str = "AND ps.`service` = 1"; 
        }
        */

        // set to new
        $ver_switch_str = "AND ps.`is_payable` = 1"; 

        // get paginated list
        $property_sql_str = "
            SELECT               
                p.`property_id`,
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,

                ps.`property_services_id`,
                ps.`alarm_job_type_id`,
                ps.`service`,
                ps.`status_changed`,
                ps.`is_payable`,

                a.`agency_id`,
                a.`agency_name`,
                a.`salesrep`,
                aght.priority,
                apmd.abbreviation,

                sr_sa.`StaffID` AS sr_staff_id,
                sr_sa.`FirstName` AS sr_fname,
                sr_sa.`LastName` AS sr_lname,

                ajt.`id` AS ajt_id,
                ajt.`type` AS ajt_type
              
            FROM `property_services` AS ps 
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
            INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
            LEFT JOIN `staff_accounts` AS sr_sa ON a.`salesrep` = sr_sa.`StaffID`           
            WHERE DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'  
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
            AND p.deleted = 0
            AND p.`property_id` > 0
            {$ver_switch_str}
            {$salesrep_filter_str} 
            {$service_type_str}   
            {$is_payable_filter_str}

            {$order_by_str}           
            {$limit_sql_str}           
        ";        
        
        $data['list'] = $this->db->query($property_sql_str);
        $data['page_query'] = $property_sql_str;

        // get all
        $property_sql_str = "
            SELECT COUNT(ps.`property_services_id`) AS ps_id                
            FROM `property_services` AS ps 
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
            INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `staff_accounts` AS sr_sa ON a.`salesrep` = sr_sa.`StaffID`           
            WHERE DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'   
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
            AND p.deleted = 0
            AND p.`property_id` > 0
            {$ver_switch_str}
            {$salesrep_filter_str}  
            {$service_type_str}   
            {$is_payable_filter_str}                    
        ";
        $property_sql = $this->db->query($property_sql_str);
        $total_rows = $property_sql->row()->ps_id;                

        // dinstinct salesrep
        $salesrep_filter_sql_str = "            
            SELECT 
                DISTINCT(sr_sa.`StaffID`), 
                sr_sa.`FirstName` AS sr_fname, 
                sr_sa.`LastName` AS sr_lname
            FROM `property_services` AS ps 
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
            INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `staff_accounts` AS sr_sa ON a.`salesrep` = sr_sa.`StaffID`           
            WHERE DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'  
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )  
            AND p.deleted = 0
            AND p.`property_id` > 0
            {$ver_switch_str}
            {$service_type_str}    
            {$is_payable_filter_str}   
        ";
        $data['salesrep_filter_sql'] = $this->db->query($salesrep_filter_sql_str);

        // dinstinct service type
        $service_type_filter_sql_str = "            
            SELECT DISTINCT(ajt.`id`),     
            ajt.`id` AS ajt_id,             
            ajt.`type` AS ajt_type
            FROM `property_services` AS ps 
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
            INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `staff_accounts` AS sr_sa ON a.`salesrep` = sr_sa.`StaffID`           
            WHERE DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}' 
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
            AND p.deleted = 0
            AND p.`property_id` > 0
            {$ver_switch_str}
            {$salesrep_filter_str}   
            {$is_payable_filter_str}       
        ";
        $data['service_type_filter_sql'] = $this->db->query($service_type_filter_sql_str);

        $pagi_links_params_arr = array(
            'salesrep_filter' => $salesrep_filter,
            'service_type_filter' => $service_type_filter,
            'status_changed_from' => $status_changed_from,
            'status_changed_to' => $status_changed_to,
            'is_payable_filter' => $is_payable_filter,
            'ver' => $ver
        );
        
        $data['header_link_params'] = $pagi_links_params_arr;
        // explort link
        $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  

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
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
                

    }


    public function property_gained_and_lost() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Property Gained and Lost";
        $country_id = $this->config->item('country');
        $uri = '/reports/property_gained_and_lost';
        $data['uri'] = $uri;

        $salesrep_filter = $this->input->get_post('salesrep_filter');
        $service_type_filter = $this->input->get_post('service_type_filter'); 
        
        $today = date('Y-m-d');
        $cron_save = $this->input->get_post('cron_save');

        if( $cron_save == 1 ){ // filter to daily

            $status_changed_from = $today;
            $status_changed_to = $today;

        }else{ // default

            $status_changed_from = ( $this->input->get_post('status_changed_from') !='' )?$this->system_model->formatDate($this->input->get_post('status_changed_from')):date('Y-m-01');
            $status_changed_to = ( $this->input->get_post('status_changed_to') !='' )?$this->system_model->formatDate($this->input->get_post('status_changed_to')):date('Y-m-t');     

        }
                
        $is_payable_filter = $this->input->get_post('is_payable_filter');        

        // default to gained
        $view_type = ( $this->input->get_post('view_type') != '' )?$this->input->get_post('view_type'):1;

        $data['status_changed_from'] = $status_changed_from;
        $data['status_changed_to'] = $status_changed_to;
        $data['view_type'] = $view_type;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        //$per_page = 10;
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'address_2';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        $export = $this->input->get_post('export');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        // sort
        $order_by_str = null;
        if( $order_by == 'address_2' ){
            $order_by_str = "ORDER BY {$order_by} {$sort}, address_3 ASC";
        }else{
            $order_by_str = "ORDER BY {$order_by} {$sort}";
        }            

        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 ){ 
            $limit_sql_str = "LIMIT {$offset}, {$per_page}";
        }     
        
        // excluded agency copied from mark
        $excude_agency_str = null;
        if( ENVIRONMENT == 'production' ){ // live

            if( $this->config->item('country') == 1 ){ // AU

                $excude_agency_str = 'AND a.`agency_id` != 1448';
    
            }else if( $this->config->item('country') == 2 ){ // NZ
    
                $excude_agency_str = 'AND a.`agency_id` NOT IN(5536,6603)';
    
            }

        }
          
        if( $view_type == 1 ){ // gained
            
            $gained_sql_str = "
            (

                SELECT 
                    DISTINCT(p.`property_id`),
                    p.`address_1`,
                    p.`address_2`,
                    p.`address_3`,
                    p.`state`,
                    p.`postcode`,
                    p.`reason`,
            
                    a.`agency_id`,
                    a.`agency_name`,
                    aght.priority,
                    apmd.abbreviation
                FROM `logs` AS l 
                LEFT JOIN `property` AS p ON l.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
                LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
                WHERE l.`details` LIKE '% marked <b>payable</b>' 
                AND p.is_sales != 1
                AND DATE(l.`created_date`)  BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'
                {$excude_agency_str}
                
            )UNION(
            
                SELECT 
                    DISTINCT(p.`property_id`),
                    p.`address_1`,
                    p.`address_2`,
                    p.`address_3`,
                    p.`state`,
                    p.`postcode`,
                    p.`reason`,
            
                    a.`agency_id`,
                    a.`agency_name`,
                    aght.priority,
                    apmd.abbreviation
                FROM `property_event_log` AS pel 
                LEFT JOIN `property` AS p ON pel.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
                LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
                WHERE pel.`event_details` like '% marked <b>payable</b>'
                AND p.is_sales != 1
                AND DATE(pel.`log_date`)  BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'
                {$excude_agency_str}
                
            )UNION(
            
                SELECT               
                    DISTINCT(p.`property_id`),
                    p.`address_1`,
                    p.`address_2`,
                    p.`address_3`,
                    p.`state`,
                    p.`postcode`,
                    p.`reason`,
            
                    a.`agency_id`,
                    a.`agency_name`,
                    aght.priority,
                    apmd.abbreviation
                FROM property_services AS ps
                LEFT JOIN property AS p ON ps.property_id = p.property_id	
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
                LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
                WHERE ps.service = 1    
                AND ps.is_payable = 1 
                AND p.is_sales != 1
                AND DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'
                {$excude_agency_str}
            
            )
            ";

            // paginated
            $paginated_sql_str = "
            {$gained_sql_str}
            {$order_by_str}             
            {$limit_sql_str}         
            "; 
           
            // all list
            $all_list_sql_str = $gained_sql_str;

        }else if( $view_type == 2 ){ // lost
            
            
            $lost_sql_str = "
            SELECT  
                DISTINCT(p.`property_id`),                 
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,
                p.`reason`,

                a.`agency_id`,
                a.`agency_name`,
                aght.priority,
                apmd.abbreviation
            FROM property_services AS ps
            INNER JOIN property AS p ON ps.property_id = p.property_id	
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
            WHERE (
                p.`property_id` NOT IN(

                    SELECT DISTINCT(p_inner.`property_id`)
                    FROM property_services AS ps_inner
                    INNER JOIN property AS p_inner ON ps_inner.property_id = p_inner.property_id	
                    WHERE ps_inner.service = 1
    
                )
                AND DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'
            ) OR (
                
                (
                    p.`deleted` = 1 AND
                    DATE(p.`deleted_date`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'
                ) OR (
                    p.`is_nlm` = 1 AND
                    DATE(p.`nlm_timestamp`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'
                )                
            )
            AND p.is_sales != 1
            {$excude_agency_str} 
            ";                

            // paginated
            $paginated_sql_str = "
            {$lost_sql_str}  
            {$order_by_str}                                            
            {$limit_sql_str}     
            ";         
            
            // all list
            $all_list_sql_str = $lost_sql_str;

        }

        if( $cron_save == 1 ){ // save for cron
                                            
            $sql = $this->db->query($all_list_sql_str);

            foreach( $sql->result() as $row ){

                $insert_data = array(
                    'property_id' => $row->property_id,
                    'agency_id' => $row->agency_id,
                    'gained_or_lost' => $view_type,
                    'date' => $today
                );
                
                $this->db->insert('properties_tracked', $insert_data);

            }            

        }else{ // page view

            //echo $paginated_sql_str;

            // paginated list
            $data['list'] = $this->db->query($paginated_sql_str);
            $data['page_query'] = $paginated_sql_str;

            // all list
            $all_list_sql = $this->db->query($all_list_sql_str);
            $total_rows = $all_list_sql->num_rows();            

            $pagi_links_params_arr = array(
                'status_changed_from' => $status_changed_from,
                'status_changed_to' => $status_changed_to,                
                'view_type' => $view_type,
                'order_by' => $order_by,
                'sort' => $sort
            );
            
            $data['header_link_params'] = $pagi_links_params_arr;
            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  

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
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);

        }                        

    }

    public function gained_lost_serverside() {
        $columns = array( 
            0 => 'address_1', 
            1 => 'agency_name'
        );

		$limit = $this->input->post('length');
        $start = $this->input->post('start');
        $view_type = ( $this->input->get_post('view_type') != '' )?$this->input->get_post('view_type'):1;

        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];

        $filter['status_changed_from'] = $this->input->get_post('status_changed_from');
        $filter['status_changed_to'] = $this->input->get_post('status_changed_to');
        $filter['view_type'] = $this->input->get_post('view_type');

        $properties =  $this->reports_model->property_gained_lost($limit,$start,$filter,$order,$dir);
        $totalData = $this->reports_model->property_gained_lost_count($filter);
        $totalFiltered = $totalData;
        $data = array();
        if($properties > 0) {
            foreach ($properties as $property) {
                $link = $this->config->item('crm_link') . '/view_property_details.php?id=' . $property->property_id ;
                $nestedData['address_1'] = "<a href='$link' class='text-link' target='_blank'>$property->address_1, $property->address_2, $property->address_3</a>";
                $nestedData['agency_name'] = "<a href='/agency/view_agency_details/$property->agency_id' class='text-link' target='_blank'>$property->agency_name</a>";
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($this->input->post('draw')),  
            "recordsTotal" => intval($totalData),  
            "recordsFiltered" => intval($totalFiltered), 
            "data" => $data
        );
        echo json_encode($json_data); 
    }


    public function ajax_update_is_payable(){

        $ps_id = $this->input->get_post('ps_id');
        $is_payable = $this->input->get_post('is_payable');

        if( $ps_id > 0  && is_numeric($is_payable) ){

            $update_sql_str = "
            UPDATE `property_services`
            SET `is_payable` = {$is_payable}
            WHERE `property_services_id` = {$ps_id}
            ";
            $this->db->query($update_sql_str);
            
        }

    }


    public function ajax_clear_status_change(){

        $ps_id_arr = $this->input->get_post('ps_id_arr');        

        if( count($ps_id_arr) > 0 ){

            $ps_id_imp = implode(",",$ps_id_arr);

            // static date, picked by ben
            $static_date = '1901-01-01 11:11:11';

            $update_sql_str = "
            UPDATE `property_services`
            SET `status_changed` = '{$static_date}'
            WHERE `property_services_id` IN({$ps_id_imp}) 
            ";
            $this->db->query($update_sql_str);
            
        }

    }


    public function ajax_mark_as_not_payable_bulk(){

        $prop_id_arr = $this->input->get_post('prop_id_arr');        

        if( count($prop_id_arr) > 0 ){

            $prop_id_imp = implode(",",$prop_id_arr);
            
            if( $prop_id_imp != '' ){

                $alarm_job_type_id = 2; // Smoke Alarms
                $service_status = 0; // DIY

                $update_sql_str = "
                UPDATE `property_services`
                SET `is_payable` = 0
                WHERE `alarm_job_type_id` = {$alarm_job_type_id} 
                AND `property_id` IN({$prop_id_imp})
                AND `is_payable` = 1                
                AND `service` = {$service_status}
                ";
                $this->db->query($update_sql_str);

            }           
            
        }

    }


    public function properties_with_coordinates_errors() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Properties with Coordinate Errors";
        $country_id = $this->config->item('country');
        $uri = '/reports/properties_with_coordinates_errors';
        $data['uri'] = $uri;
        
        $agency_filter = $this->input->get_post('agency_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // salerep filter
        $agency_filter_str = null;
        if( $agency_filter > 0 ){
            $agency_filter_str = "AND p.`agency_id` = {$agency_filter}";   
        }

        // Ben Taylor made this :)
        $coordinates_filter = null;
        if( $country_id == 1 ){ // AU
            $coordinates_filter = "
            ( p.`lat` NOT BETWEEN -43.644444 AND -10.689167 ) OR
            ( p.`lng` NOT BETWEEN 113.155 AND 153.637222 )
            ";
        }else if( $country_id == 2 ){ // NZ
            $coordinates_filter = "
            ( p.`lat` NOT BETWEEN -52.619444 AND -29.231667 ) OR
                (
                    ( p.`lng` NOT BETWEEN 165.870128 AND 180 ) AND
                    ( p.`lng` NOT BETWEEN -180 AND -175.831410 )
                )
            ";
        }

        // get paginated list
        $property_sql_str = "
            SELECT 
                p.`property_id`,
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,
                p.`lat`,
                p.`lng`,

                a.`agency_id`,
                a.`agency_name`,
                aght.priority,
                apmd.abbreviation
            FROM `property` AS p
            INNER JOIN `property_services` AS ps ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
            WHERE p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'
            AND (
                ( p.`lat` = '' OR p.`lat` IS NULL ) OR
                ( p.`lng` = '' OR p.`lng` IS NULL ) OR
                {$coordinates_filter}                
            )  	        
            {$agency_filter_str}
            LIMIT {$offset}, {$per_page}
        ";        
        
        $data['query_string'] = $property_sql_str;
        $data['list'] = $this->db->query($property_sql_str);

        // get all
        $property_sql_str = "
            SELECT COUNT(p.`property_id`) AS p_count
            FROM `property` AS p
            INNER JOIN `property_services` AS ps ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'
            AND (
                ( p.`lat` = '' OR p.`lat` IS NULL ) OR
                ( p.`lng` = '' OR p.`lng` IS NULL ) OR
                {$coordinates_filter}                
            )   
            {$agency_filter_str}                   
        ";
        $property_sql = $this->db->query($property_sql_str);
        $total_rows = $property_sql->row()->p_count;  
        
        
        // distinct user
        $agency_filter_sql_str = "            
            SELECT 
                DISTINCT(a.`agency_id`),
                a.`agency_name`
            FROM `property` AS p
            INNER JOIN `property_services` AS ps ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'
            AND (
                ( p.`lat` = '' OR p.`lat` IS NULL ) OR
                ( p.`lng` = '' OR p.`lng` IS NULL ) OR
                {$coordinates_filter}                
            )         
        ";
        $data['agency_filter_sql'] = $this->db->query($agency_filter_sql_str);
        

        $pagi_links_params_arr = array();
        
        $data['header_link_params'] = $pagi_links_params_arr;
        // explort link
        $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  

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
        
        // update page total
        $page_tot_params = array(
            'page' => $uri,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
                

    }



    public function user_logins() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "User Logins";
        $country_id = $this->config->item('country');
        $uri = '/reports/user_logins';
        $data['uri'] = $uri;
        
        $crm_user_filter = $this->input->get_post('crm_user_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):null;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // salerep filter
        $crm_user_filter_str = null;
        if( $crm_user_filter > 0 ){
            $crm_user_filter_str = "AND cul.`user` = {$crm_user_filter}";   
        }

        // salerep filter
        $date_filter_str = null;
        if( $date_filter != '' ){
            $date_filter_str = "AND CAST( cul.`date_created` AS Date ) = '{$date_filter}'";   
        }

        // get paginated list
        $property_sql_str = "
            SELECT 
               cul.`crm_user_login_id`,
               cul.`ip`,
               cul.`date_created`,

               sa.`StaffID`,
               sa.`FirstName`,
               sa.`LastName`
            FROM `crm_user_logins` AS cul
            LEFT JOIN `staff_accounts` AS sa ON cul.`user` = sa.`StaffID`
            WHERE cul.`active` = 1  
            {$crm_user_filter_str}    
            {$date_filter_str} 
            ORDER BY cul.`date_created` DESC            
            LIMIT {$offset}, {$per_page}            
        ";        
        
        $data['crm_user_sql'] = $this->db->query($property_sql_str);

        // get all
        $property_sql_str = "
            SELECT COUNT(cul.`crm_user_login_id`) AS cul_count
            FROM `crm_user_logins` AS cul
            LEFT JOIN `staff_accounts` AS sa ON cul.`user` = sa.`StaffID`
            WHERE cul.`active` = 1    
            {$crm_user_filter_str}  
            {$date_filter_str}                       
        ";
        $property_sql = $this->db->query($property_sql_str);
        $total_rows = $property_sql->row()->cul_count;                
        
        // distinct user
        $crm_user_filter_sql_str = "            
            SELECT 
                DISTINCT(sa.`StaffID`),
                sa.`FirstName`,
                sa.`LastName`
            FROM `crm_user_logins` AS cul
            LEFT JOIN `staff_accounts` AS sa ON cul.`user` = sa.`StaffID`
            WHERE cul.`active` = 1    
            {$date_filter_str}
            ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
        ";
        $data['crm_user_filter_sql'] = $this->db->query($crm_user_filter_sql_str);

        $pagi_links_params_arr = array(
            'crm_user_filter' => $crm_user_filter,
            'date_filter' => $this->input->get_post('date_filter')
        );
        
        $data['header_link_params'] = $pagi_links_params_arr;

        // explort link
        $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  

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
        
        // update page total
        $page_tot_params = array(
            'page' => $uri,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
                

    }


    public function property_commissions_comparison() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Property Commissions Comparison";
        $country_id = $this->config->item('country');
        $uri = '/reports/property_commissions_comparison';
        $data['uri'] = $uri;

        $salesrep_filter = $this->input->get_post('salesrep_filter');
        $service_type_filter = $this->input->get_post('service_type_filter');   
        $status_changed_from = ( $this->input->get_post('status_changed_from') !='' )?$this->system_model->formatDate($this->input->get_post('status_changed_from')):date('Y-m-01');
        $status_changed_to = ( $this->input->get_post('status_changed_to') !='' )?$this->system_model->formatDate($this->input->get_post('status_changed_to')):date('Y-m-t');     
        $is_payable_filter = $this->input->get_post('is_payable_filter');
        $ver = $this->input->get_post('ver');

        $data['status_changed_from'] = $status_changed_from;
        $data['status_changed_to'] = $status_changed_to;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'ps.status_changed';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        $export = $this->input->get_post('export');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        // sort
        $order_by_str = null;
        if( $order_by == 'p.address_2' ){
            $order_by_str = "ORDER BY {$order_by} {$sort}, p.address_3 ASC";
        }else{
            $order_by_str = "ORDER BY {$order_by} {$sort}";
        }
        

        // salerep filter
        $salesrep_filter_str = null;
        if( $salesrep_filter > 0 ){
            $salesrep_filter_str = "AND a.`salesrep` = {$salesrep_filter}";   
        }

        // salerep filter
        $service_type_str = null;
        if( $service_type_filter > 0 ){
            $service_type_str = "AND ps.`alarm_job_type_id` = {$service_type_filter}";   
        }

        // salerep filter
        $is_payable_filter_str = null;
        if( is_numeric($is_payable_filter) ){
            $is_payable_filter_str = "AND ps.`is_payable` = {$is_payable_filter}";   
        }

        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 ){ 
            $limit_sql_str = "LIMIT {$offset}, {$per_page}";
        }

        // get paginated list
        $property_sql_str = "
            SELECT               
                p.`property_id`,
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,

                ps.`property_services_id`,
                ps.`alarm_job_type_id`,
                ps.`service`,
                ps.`status_changed`,
                ps.`is_payable`,

                a.`agency_id`,
                a.`agency_name`,
                a.`salesrep`,

                sr_sa.`StaffID` AS sr_staff_id,
                sr_sa.`FirstName` AS sr_fname,
                sr_sa.`LastName` AS sr_lname,

                ajt.`id` AS ajt_id,
                ajt.`type` AS ajt_type
              
            FROM `property_services` AS ps 
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
            INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `staff_accounts` AS sr_sa ON a.`salesrep` = sr_sa.`StaffID`           
            WHERE DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'  
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
            AND p.deleted = 0
            AND p.`property_id` > 0
            AND ps.`service` = 1
            AND p.`property_id` NOT IN(
	
                SELECT DISTINCT(p_inner.`property_id`) 
                FROM `property_services` AS ps_inner 
                LEFT JOIN `alarm_job_type` AS ajt_inner ON ps_inner.`alarm_job_type_id` = ajt_inner.`id`
                INNER JOIN `property` AS p_inner ON ps_inner.`property_id` = p_inner.`property_id` 
                LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id` 
                LEFT JOIN `staff_accounts` AS sr_sa_inner ON a_inner.`salesrep` = sr_sa_inner.`StaffID`           
                WHERE DATE(ps_inner.`status_changed`) BETWEEN '2021-09-01' AND '2021-09-30'  
                AND (
                    p_inner.`is_nlm` IS NULL 
                    OR p_inner.`is_nlm` = 0
                )
                AND p_inner.deleted = 0
                AND p_inner.`property_id` > 0
                AND ps_inner.`is_payable` = 1
                
            )        
            {$salesrep_filter_str} 
            {$service_type_str}   
            {$is_payable_filter_str}

            {$order_by_str}           
            {$limit_sql_str}           
        ";        
        
        $data['list'] = $this->db->query($property_sql_str);
        $data['page_query'] = $property_sql_str;

        // get all
        $property_sql_str = "
            SELECT COUNT(ps.`property_services_id`) AS ps_id                
            FROM `property_services` AS ps 
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
            INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `staff_accounts` AS sr_sa ON a.`salesrep` = sr_sa.`StaffID`           
            WHERE DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'   
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
            AND p.deleted = 0
            AND p.`property_id` > 0  
            AND ps.`service` = 1
            AND p.`property_id` NOT IN(
                SELECT DISTINCT(p_inner.`property_id`) 
                FROM `property_services` AS ps_inner 
                LEFT JOIN `alarm_job_type` AS ajt_inner ON ps_inner.`alarm_job_type_id` = ajt_inner.`id`
                INNER JOIN `property` AS p_inner ON ps_inner.`property_id` = p_inner.`property_id` 
                LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id` 
                LEFT JOIN `staff_accounts` AS sr_sa_inner ON a_inner.`salesrep` = sr_sa_inner.`StaffID`           
                WHERE DATE(ps_inner.`status_changed`) BETWEEN '2021-09-01' AND '2021-09-30'  
                AND (
                    p_inner.`is_nlm` IS NULL 
                    OR p_inner.`is_nlm` = 0
                )
                AND p_inner.deleted = 0
                AND p_inner.`property_id` > 0
                AND ps_inner.`is_payable` = 1
                
            )          
            {$salesrep_filter_str}  
            {$service_type_str}   
            {$is_payable_filter_str}                    
        ";
        $property_sql = $this->db->query($property_sql_str);
        $total_rows = $property_sql->row()->ps_id;                

        // dinstinct salesrep
        $salesrep_filter_sql_str = "            
            SELECT 
                DISTINCT(sr_sa.`StaffID`), 
                sr_sa.`FirstName` AS sr_fname, 
                sr_sa.`LastName` AS sr_lname
            FROM `property_services` AS ps 
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
            INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `staff_accounts` AS sr_sa ON a.`salesrep` = sr_sa.`StaffID`           
            WHERE DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}'  
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )  
            AND p.deleted = 0
            AND p.`property_id` > 0
            AND ps.`service` = 1
            AND p.`property_id` NOT IN(
	
                SELECT DISTINCT(p_inner.`property_id`) 
                FROM `property_services` AS ps_inner 
                LEFT JOIN `alarm_job_type` AS ajt_inner ON ps_inner.`alarm_job_type_id` = ajt_inner.`id`
                INNER JOIN `property` AS p_inner ON ps_inner.`property_id` = p_inner.`property_id` 
                LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id` 
                LEFT JOIN `staff_accounts` AS sr_sa_inner ON a_inner.`salesrep` = sr_sa_inner.`StaffID`           
                WHERE DATE(ps_inner.`status_changed`) BETWEEN '2021-09-01' AND '2021-09-30'  
                AND (
                    p_inner.`is_nlm` IS NULL 
                    OR p_inner.`is_nlm` = 0
                )
                AND p_inner.deleted = 0
                AND p_inner.`property_id` > 0
                AND ps_inner.`is_payable` = 1
                
            )
            {$service_type_str}    
            {$is_payable_filter_str}   
        ";
        $data['salesrep_filter_sql'] = $this->db->query($salesrep_filter_sql_str);

        // dinstinct service type
        $service_type_filter_sql_str = "            
            SELECT DISTINCT(ajt.`id`),     
            ajt.`id` AS ajt_id,             
            ajt.`type` AS ajt_type
            FROM `property_services` AS ps 
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
            INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `staff_accounts` AS sr_sa ON a.`salesrep` = sr_sa.`StaffID`           
            WHERE DATE(ps.`status_changed`) BETWEEN '{$status_changed_from}' AND '{$status_changed_to}' 
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
            AND p.deleted = 0
            AND p.`property_id` > 0
            AND ps.`service` = 1
            AND p.`property_id` NOT IN(
	
                SELECT DISTINCT(p_inner.`property_id`) 
                FROM `property_services` AS ps_inner 
                LEFT JOIN `alarm_job_type` AS ajt_inner ON ps_inner.`alarm_job_type_id` = ajt_inner.`id`
                INNER JOIN `property` AS p_inner ON ps_inner.`property_id` = p_inner.`property_id` 
                LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id` 
                LEFT JOIN `staff_accounts` AS sr_sa_inner ON a_inner.`salesrep` = sr_sa_inner.`StaffID`           
                WHERE DATE(ps_inner.`status_changed`) BETWEEN '2021-09-01' AND '2021-09-30'  
                AND (
                    p_inner.`is_nlm` IS NULL 
                    OR p_inner.`is_nlm` = 0
                )
                AND p_inner.deleted = 0
                AND p_inner.`property_id` > 0
                AND ps_inner.`is_payable` = 1
                
            )
            {$salesrep_filter_str}   
            {$is_payable_filter_str}       
        ";
        $data['service_type_filter_sql'] = $this->db->query($service_type_filter_sql_str);

        $pagi_links_params_arr = array(
            'salesrep_filter' => $salesrep_filter,
            'service_type_filter' => $service_type_filter,
            'status_changed_from' => $status_changed_from,
            'status_changed_to' => $status_changed_to,
            'is_payable_filter' => $is_payable_filter
        );
        
        $data['header_link_params'] = $pagi_links_params_arr;
        // explort link
        $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  

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
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
                

    }

    public function overdue_invoices(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Overdue Invoices";
        $uri = '/reports/myob_import';
        $file = $this->input->post('file');

        if( $this->input->post('btn_import_csv') ){ //Import clicked

            
            $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

            if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes)){

                if(is_uploaded_file($_FILES['file']['tmp_name'])){

                    // Open uploaded CSV file with read-only mode
                    $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

                    // Skip the first line
                    fgetcsv($csvFile);

                    $csv_array = array();
                    while(! feof( $csvFile )){
                        $csv_array[] = fgetcsv($csvFile);	
                    }
                    
                }

                $data['ttcsv'] = $csv_array;

            }

        }

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function jobs_by_agency() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Jobs By Agency";        
        $uri = '/reports/jobs_by_agency';
        $data['uri'] = $uri;
      
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        //$per_page = 10;
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'p.address_2';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        // header filters
        $agency_filter = $this->input->get_post('agency_filter');
        $job_status_filter = $this->input->get_post('job_status_filter');

        $export = $this->input->get_post('export');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        // sort
        $order_by_str = null;
        if( $order_by == 'p.address_2' ){
            $order_by_str = "ORDER BY {$order_by} {$sort}, p.address_3 ASC";
        }else{
            $order_by_str = "ORDER BY {$order_by} {$sort}";
        }   
        
        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 ){ 
            $limit_sql_str = "LIMIT {$offset}, {$per_page}";
        }     

        $agency_filter_str = null;
        if( $agency_filter > 0 ){
            $agency_filter_str = "AND a.`agency_id` = {$agency_filter}";
        }   
        
        $job_status_filter_str = null;
        if( $job_status_filter != '' ){
            $job_status_filter_str = "AND j.`status` = '{$job_status_filter}'";
        }else{
            $job_status_filter_str = "AND j.`status` NOT IN ('Completed','Cancelled')";
        }

        $fg_id = 40; // "Image" Franchise Groups
          
        $gained_sql_str = "
        SELECT      
            j.`id` AS jid,
            j.`job_type`,
            j.`status` AS jstatus,
            
            p.`property_id`,
            p.`address_1`,
            p.`address_2`,
            p.`address_3`,
            p.`state`,
            p.`postcode`,

            a.`agency_id`,
            a.`agency_name`,
            aght.priority,
            apmd.abbreviation
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
        LEFT JOIN `agency_priority` AS aght ON a.`agency_id` = aght.`agency_id`
        LEFT JOIN `agency_priority_marker_definition` AS apmd ON aght.`priority` = apmd.`priority`
        WHERE a.`franchise_groups_id` =  {$fg_id}
        AND j.`del_job` = 0
        AND p.`deleted` = 0
        AND a.`status` = 'active'          
        {$agency_filter_str}    
        {$job_status_filter_str}             
        ";
        $data['main_query'] = $gained_sql_str;

        // paginated
        $paginated_sql_str = "   
        {$gained_sql_str}     
        {$order_by_str}             
        {$limit_sql_str}         
        "; 

        // all list
		$all_list_sql_str = $gained_sql_str;
        
        // paginated list
        $data['list'] = $this->db->query($paginated_sql_str);
        $data['page_query'] = $paginated_sql_str;

        // all list
        $all_list_sql = $this->db->query($all_list_sql_str);
        $total_rows = $all_list_sql->num_rows();   
        
        // agency filter
        $distinct_agency_sql_str = "
        SELECT 
            DISTINCT(a.`agency_id`),
            a.`agency_name`
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
        WHERE a.`franchise_groups_id` =  {$fg_id}
        AND j.`del_job` = 0
        AND p.`deleted` = 0
        AND a.`status` = 'active'
        {$job_status_filter_str}          
        ";
        $data['distinct_agency_sql'] = $this->db->query($distinct_agency_sql_str);

        $pagi_links_params_arr = array( 
            'agency_filter' => $agency_filter,
            'job_status_filter' => $job_status_filter
        );
        
        $data['header_link_params'] = $pagi_links_params_arr;
        // explort link
        $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  

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
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);                     

    }

    public function mark_as_not_payable() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Mark as not payable";        
        $uri = '/reports/mark_as_not_payable';
        $data['uri'] = $uri;

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'p.address_2';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        $export = $this->input->get_post('export');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        // sort
        $order_by_str = null;
        if( $order_by == 'p.address_2' ){
            $order_by_str = "ORDER BY {$order_by} {$sort}, p.address_3 ASC";
        }else{
            $order_by_str = "ORDER BY {$order_by} {$sort}";
        }   
        
        $alarm_job_type_id =  2; // Smoke Alarms
        $service_status = 0; // DIY
        $main_sql_str = "
        SELECT
            p.`property_id`,
            p.`address_1`,
            p.`address_2`,
            p.`address_3`,
            p.`state`,
            p.`postcode`,

            a.`agency_id`,
            a.`agency_name`
        FROM `property` AS p 
        INNER JOIN `property_services` AS ps 
        ON ( 
            p.`property_id` = ps.`property_id` AND 
            ps.`alarm_job_type_id` = {$alarm_job_type_id} AND 
            ps.`is_payable` = 1 AND
            ps.`service` = {$service_status}
        )
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
        WHERE p.`deleted` = 0
        AND a.`status` = 'active'   
        {$order_by_str}                 
        ";        
        $main_sql = $this->db->query($main_sql_str);        

        if ($export == 1) { //EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "mark_as_not_payable_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = array("Property ID","Property Address","Agency Name");
            fputcsv($csv_file, $header);
            
            foreach ( $main_sql->result() as $row ){ 

                // inner query 1
                $inner_query_sql_str = "
                SELECT COUNT(pel.`id`) AS pel_count
                FROM `property_event_log` AS pel
                WHERE pel.`property_id` = {$row->property_id}
                AND pel.`event_type` = 'Property Sales Commission'
                AND pel.`event_details` = 'Property Service <b>Smoke Alarms</b> marked <b>payable</b>'                        
                AND DATE(pel.`log_date`) BETWEEN '2022-03-01' AND '2022-03-31' 	
                ";
                $inner_query_sql = $this->db->query($inner_query_sql_str);
                $inner_query_row = $inner_query_sql->row();

                // inner query 2
                $inner_query_sql_str2 = "
                SELECT COUNT(pel.`id`) AS pel_count
                FROM `property_event_log` AS pel
                WHERE pel.`property_id` = {$row->property_id}
                AND pel.`event_type` = 'Property Service updated'
                AND pel.`event_details` = 'Smoke Alarms Changed from SATS to DIY'                      
                AND DATE(pel.`log_date`) BETWEEN '2022-03-01' AND '2022-03-31' 	
                ";
                $inner_query_sql2 = $this->db->query($inner_query_sql_str2);
                $inner_query_row2 = $inner_query_sql2->row();

                // is true to both logs check
                if( $inner_query_row->pel_count > 0 && $inner_query_row2->pel_count > 0 ){

                    $csv_row = [];                              

                    $csv_row[] = $row->property_id;  
                    $csv_row[] = "{$row->address_1} {$row->address_2}, {$row->address_3}"; 
                    $csv_row[] = $row->agency_name;               
                    
                    fputcsv($csv_file,$csv_row); 

                }                

            }
        
            fclose($csv_file); 
            exit; 

        }else{

            // paginated list
            $data['list'] = $this->db->query($main_sql_str);
            $data['page_query'] = $main_sql_str;
                            
            // explort link
            $data['export_link'] = "{$uri}/?export=1"; 
            
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data); 

        }                                    

    }

    public function suggest_crm_task_managers(){

        $search_managers = $this->db->escape_str($this->input->post('search_managers'));
        $country_id = $this->config->item('country');
        $html_str = null;

        // get staff accounts
        if( $search_managers != '' ){

            $sql_str = "
            SELECT
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`
            FROM `staff_accounts` AS sa INNER JOIN `country_access` AS `ca` ON sa.`StaffID` = ca.`staff_accounts_id`
            WHERE sa.`active` = 1
            AND sa.`Deleted` = 0
            AND `ca`.`country_id` = {$country_id}           
            AND CONCAT_WS(' ', sa.`FirstName`, sa.`FirstName`) LIKE '{$search_managers}%'
            AND sa.`StaffID` NOT IN(

                SELECT DISTINCT(ctm.`staff_id`)
                FROM `crm_task_managers` AS ctm
                INNER JOIN `staff_accounts` AS sa ON ctm.`staff_id` = sa.`StaffID`
                WHERE ctm.`active` = 1
                AND sa.`active` = 1
                AND sa.`Deleted` = 0

            )
            ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
            ";
            $staff_sql = $this->db->query($sql_str);
                
            $html_str .= "<ul id='managers_ul' class='list-group managers_ul'>";
            foreach( $staff_sql->result() as $staff_row ){

                $staff_name = $this->system_model->formatStaffName($staff_row->FirstName, $staff_row->LastName);
                $html_str .= "<li class='list-group-item managers_user_id' data-staff_id='{$staff_row->StaffID}'>{$staff_name}</li>";
            }
            $html_str .= "</ul>";

            echo $html_str;

        }                

    }

    public function suggest_agencies(){

        $search_agency = $this->db->escape_str($this->input->post('search_agency'));
        $country_id = $this->config->item('country');
        $html_str = null;

        // get staff accounts
        if( $search_agency != '' ){

            $sql_str = "
            SELECT
                a.`agency_id`,
                a.`agency_name`
            FROM `agency` AS a
            WHERE `a`.`status` = 'active'
            AND `a`.`deleted` = 0
            AND `a`.`country_id` = {$country_id}           
            AND CONCAT_WS(' ', a.`agency_name`) LIKE '{$search_agency}%'
            ORDER BY a.`agency_name` ASC
            ";
            $agency_sql = $this->db->query($sql_str);
                
            $html_str .= "<ul id='managers_ul' class='list-group managers_ul'>";
            foreach( $agency_sql->result() as $agency_row ){

                // $staff_name = $this->system_model->formatStaffName($staff_row->FirstName, $staff_row->LastName);
                $html_str .= "<li class='list-group-item managers_user_id' data-staff_id='{$agency_row->agency_id}'>{$agency_row->agency_name}</li>";
            }
            $html_str .= "</ul>";

            echo $html_str;

        }                

    }


    public function suggest_crm_task_details_sub_users(){

        $search_managers = $this->db->escape_str($this->input->post('search_managers'));
        $ticket_id = $this->db->escape_str($this->input->post('ticket_id'));

        $country_id = $this->config->item('country');
        $html_str = null;

        // get staff accounts
        if( $search_managers != '' && $ticket_id > 0 ){

            $sql_str = "
            SELECT
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`
            FROM `staff_accounts` AS sa INNER JOIN `country_access` AS `ca` ON sa.`StaffID` = ca.`staff_accounts_id`
            WHERE sa.`active` = 1
            AND sa.`Deleted` = 0
            AND `ca`.`country_id` = {$country_id}           
            AND CONCAT_WS(' ', sa.`FirstName`, sa.`FirstName`) LIKE '{$search_managers}%'
            AND sa.`StaffID` NOT IN(
                
                SELECT DISTINCT(ctdsu.`staff_id`)
                FROM `crm_task_details_sub_users` AS ctdsu
                INNER JOIN `staff_accounts` AS sa ON ctdsu.`staff_id` = sa.`StaffID`
                WHERE ctdsu.`active` = 1
                AND sa.`active` = 1
                AND sa.`Deleted` = 0
                AND ctdsu.`ticket_id` = {$ticket_id}  

            )
            ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
            ";
            $staff_sql = $this->db->query($sql_str);
                
            $html_str .= "<ul id='managers_ul' class='list-group managers_ul'>";
            foreach( $staff_sql->result() as $staff_row ){

                $staff_name = $this->system_model->formatStaffName($staff_row->FirstName, $staff_row->LastName);
                $html_str .= "<li class='list-group-item managers_user_id' data-staff_id='{$staff_row->StaffID}'>{$staff_name}</li>";
            }
            $html_str .= "</ul>";

            echo $html_str;

        }                

    }

    public function suggest_crm_task_details_devs(){

        $search_devs = $this->db->escape_str($this->input->post('search_devs'));
        $ticket_id = $this->db->escape_str($this->input->post('ticket_id'));

        $country_id = $this->config->item('country');
        $html_str = null;

        // get staff accounts
        if( $search_devs != '' && $ticket_id > 0 ){

            $sql_str = "
            SELECT
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`
            FROM `staff_accounts` AS sa INNER JOIN `country_access` AS `ca` ON sa.`StaffID` = ca.`staff_accounts_id`
            WHERE sa.`active` = 1
            AND sa.`Deleted` = 0
            AND `ca`.`country_id` = {$country_id}           
            AND CONCAT_WS(' ', sa.`FirstName`, sa.`FirstName`) LIKE '{$search_devs}%'
            AND sa.`ClassID` = 11
            AND sa.`StaffID` NOT IN(
                
                SELECT DISTINCT(ctd_dev.`dev_id`)
                FROM `crm_task_details_devs` AS ctd_dev
                INNER JOIN `staff_accounts` AS sa ON ctd_dev.`dev_id` = sa.`StaffID`
                WHERE ctd_dev.`active` = 1
                AND sa.`active` = 1
                AND sa.`Deleted` = 0
                AND ctd_dev.`ticket_id` = {$ticket_id}  

            )
            ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
            ";
            $staff_sql = $this->db->query($sql_str);
                
            $html_str .= "<ul id='devs_ul' class='list-group devs_ul'>";
            foreach( $staff_sql->result() as $staff_row ){

                $staff_name = $this->system_model->formatStaffName($staff_row->FirstName, $staff_row->LastName);
                $html_str .= "<li class='list-group-item dev_user_id' data-dev_id='{$staff_row->StaffID}'>{$staff_name}</li>";
            }
            $html_str .= "</ul>";

            echo $html_str;

        }                

    }

    public function subscribe_crm_task_managers(){

        $staff_id = $this->input->post('staff_id');        

        if( $staff_id > 0 ){

            // clear first, this also clears duplicates
            $this->db->where('staff_id', $staff_id);
            $this->db->delete('crm_task_managers');

            // insert managers
            $insert_data = array(
                'staff_id' => $staff_id
            ); 
            
            $this->db->insert('crm_task_managers', $insert_data);

        }        

    }

    public function complaints_temp_managers(){

        $staff_id = $this->input->post('staff_id');        

        if( $staff_id > 0 ){

            // clear first, this also clears duplicates
            $this->db->where('staff_id', $staff_id);
            $this->db->delete('complaints_assign_to_temp');

            // insert managers
            $insert_data = array(
                'user_id' => $this->session->staff_id,
                'staff_id' => $staff_id
            ); 
            
            $this->db->insert('complaints_assign_to_temp', $insert_data);

        }        

    }

    public function complaints_temp_agency(){

        $agency_id = $this->input->post('agency_id');        

        if( $agency_id > 0 ){

            // clear first, this also clears duplicates
            $this->db->where('agency_id', $agency_id);
            $this->db->delete('complaints_agency_temp');

            // insert managers
            $insert_data = array(
                'user_id' => $this->session->staff_id,
                'agency_id' => $agency_id
            ); 
            
            $this->db->insert('complaints_agency_temp', $insert_data);

        }        

    }

    public function unsubscribe_crm_task_managers(){

        $subcribed_staff_id = $this->input->post('subcribed_staff_id');

        if( $subcribed_staff_id > 0 ){
            
            // remove manager from subscribed list
            $this->db->where('staff_id', $subcribed_staff_id);
            $this->db->delete('crm_task_managers');

        }        

    }

    public function complaints_remove_temp_managers(){

        $subcribed_staff_id = $this->input->post('subcribed_staff_id');

        if( $subcribed_staff_id > 0 ){
            
            // remove manager from subscribed list
            $this->db->where('staff_id', $subcribed_staff_id);
            $this->db->delete('complaints_assign_to_temp');

        }        

    }


    public function subscribe_crm_task_details_sub_users(){

        $staff_id = $this->input->post('staff_id');
        $ticket_id = $this->input->post('ticket_id');

        $logged_user_id = $this->session->staff_id;
        // get staff name
        $staff_row = $this->gherxlib->getStaffInfo(['staff_id' => $logged_user_id])->row_array();
        $logged_user_full_name = $this->system_model->formatStaffName($staff_row['FirstName'], $staff_row['LastName']);

        if( $staff_id > 0 && $ticket_id > 0 ){

            // clear first, this also clears duplicates
            $this->db->where('staff_id', $staff_id);
            $this->db->where('ticket_id', $ticket_id);
            $this->db->delete('crm_task_details_sub_users');

            // insert managers
            $insert_data = array(
                'staff_id' => $staff_id,
                'ticket_id' => $ticket_id
            ); 
            
            $this->db->insert('crm_task_details_sub_users', $insert_data);           

            if ( $staff_id > 0  ){ 

                // get staff name
                $staff_row = $this->gherxlib->getStaffInfo(['staff_id' => $staff_id])->row_array();
                $subscribe_staff = $this->system_model->formatStaffName($staff_row['FirstName'], $staff_row['LastName']);

                // get crm task data
                $crm_task_sql = $this->db->query("
                SELECT `describe_issue`
                FROM `crm_tasks`
                WHERE `crm_task_id` = {$ticket_id}
                ");
                $crm_task_row = $crm_task_sql->row();

                // get first 30 characters
                $describe_issue_short = substr($crm_task_row->describe_issue,0,30);

                // insert notifications            
                $notf_msg = "{$logged_user_full_name} has subscribed {$subscribe_staff} to <a href='{$this->config->item('crmci_link')}/reports/ticket_details/?id={$ticket_id}'>{$describe_issue_short}</a>";                                                               

                // add activity logs
                $crm_task_log_text = "{$logged_user_full_name} has subscribed {$subscribe_staff} to this ticket";
                $data = array(
                    'ticket_id' => $ticket_id,
                    'log_text' => $crm_task_log_text,
                    'created_by' => $logged_user_id
                );
                
                $this->db->insert('crm_tasks_log', $data);

                // notification
                $send_notif_to_arr = [];

                // get subscribed managers
                $subscribed_managers_sql = $this->db->query("
                SELECT ctm.`staff_id`
                FROM `crm_task_managers` AS ctm
                INNER JOIN `staff_accounts` AS sa ON ctm.`staff_id` = sa.`StaffID`
                WHERE ctm.`active` = 1
                AND sa.`active` = 1
                AND sa.`Deleted` = 0
                ");
                foreach( $subscribed_managers_sql->result() as $subscribed_manager_row ){

                    if( $subscribed_manager_row->staff_id > 0 ){
                        $send_notif_to_arr[] = $subscribed_manager_row->staff_id;
                    }

                }
                                
                // remove duplicates 
                $send_notif_to_unique_arr = array_unique($send_notif_to_arr);        
                
                // loop through each subscribed user
                foreach( $send_notif_to_unique_arr as $send_notif_to ){

                    if ( $send_notif_to > 0  && $send_notif_to != $logged_user_id ){ // do not notify the user who added users to subscription

                        // insert notifications                                                                                            
                        $notf_type = 1; // General Notifications
                        $params = array(
                            'notf_type'=> $notf_type,
                            'staff_id'=> $send_notif_to, // notify staff
                            'country_id'=> $this->config->item('country'),
                            'notf_msg'=> $notf_msg
                        );
                        $this->gherxlib->insertNewNotification($params);

                        // pusher API notification
                        // notification config
                        $options = array(
                            'cluster' => $this->config->item('PUSHER_CLUSTER'),
                            'useTLS' => true
                        );
                        $pusher = new Pusher\Pusher(
                            $this->config->item('PUSHER_KEY'),
                            $this->config->item('PUSHER_SECRET'),
                            $this->config->item('PUSHER_APP_ID'),
                            $options
                        );
                    
                        // trigger notification
                        $pusher_data['notif_type'] = $notf_type;
                        $ch = "ch".$send_notif_to; // notify staff
                        $ev = "ev01";
                        $pusher->trigger($ch, $ev, $pusher_data);                    

                    }                

                }                     

            }  

        }        

    }


    public function unsubscribe_crm_task_details_sub_users(){

        $subcribed_staff_id = $this->input->post('subcribed_staff_id');
        $ticket_id = $this->input->post('ticket_id');

        if( $subcribed_staff_id > 0 && $ticket_id > 0 ){
            
            // remove manager from subscribed list
            $this->db->where('staff_id', $subcribed_staff_id);
            $this->db->where('ticket_id', $ticket_id);
            $this->db->delete('crm_task_details_sub_users');

        }        

    }


    public function subscribe_crm_task_details_dev(){

        $dev_id = $this->input->post('dev_id');
        $ticket_id = $this->input->post('ticket_id');

        if( $dev_id > 0 && $ticket_id > 0 ){

            // clear first, this also clears duplicates
            $this->db->where('dev_id', $dev_id);
            $this->db->where('ticket_id', $ticket_id);
            $this->db->delete('crm_task_details_devs');

            // insert managers
            $insert_data = array(
                'dev_id' => $dev_id,
                'ticket_id' => $ticket_id
            ); 
            
            $this->db->insert('crm_task_details_devs', $insert_data);

        }        

    }

    public function subscribe_complaints_details_manager(){

        $staff_id = $this->input->post('staff_id');
        $comp_id = $this->input->post('comp_id');

        if( $staff_id > 0 && $comp_id > 0 ){

            // clear first, this also clears duplicates
            $this->db->where('staff_id', $staff_id);
            $this->db->where('comp_id', $comp_id);
            $this->db->delete('complaints_assign_to');

            // insert managers
            $insert_data = array(
                'comp_id' => $comp_id,
                'staff_id' => $staff_id
                
            ); 
            
            $this->db->insert('complaints_assign_to', $insert_data);

        }        

    }


    public function unsubscribe_crm_task_details_dev(){

        $subcribed_dev_id = $this->input->post('subcribed_dev_id');
        $ticket_id = $this->input->post('ticket_id');

        if( $subcribed_dev_id > 0 && $ticket_id > 0 ){
            
            // remove manager from subscribed list
            $this->db->where('dev_id', $subcribed_dev_id);
            $this->db->where('ticket_id', $ticket_id);
            $this->db->delete('crm_task_details_devs');

        }        

    }

    public function unsubscribe_complaints_details_manager(){

        $staff_id = $this->input->post('staff_id');
        $comp_id = $this->input->post('comp_id');

        if( $staff_id > 0 && $comp_id > 0 ){
            
            // remove manager from subscribed list
            $this->db->where('staff_id', $staff_id);
            $this->db->where('comp_id', $comp_id);
            $this->db->delete('complaints_assign_to');

        }        

    }

    /*
    public function service_price_report() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Service Price Report";        
        $uri = '/reports/service_price_report';
        $data['uri'] = $uri;

        $service_source = $this->input->get_post('service_source');
        $service_type = $this->input->get_post('service_type');
        $comparison = $this->input->get_post('comparison');
        $price = $this->input->get_post('price');
        $agency_filter = $this->input->get_post('agency_filter');

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'p.address_2';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        // pagination
        $per_page = $this->config->item('pagi_per_page');        
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 ){ 
            $limit_sql_str = " LIMIT {$offset}, {$per_page}";
        }

        $comparison_operator = ( $comparison == 1 )?'=':'!=';

      
        if( $service_source == 1 ){ // agency

            $main_sql_str = "
            SELECT
                a.`agency_id`,
                a.`agency_name`,

                a_serv.`price`,
                a_serv.`service_id` AS service_type,

                ajt.`type` AS ajt_type,
                ajt.`full_name` AS service_full_name
            FROM `agency` AS a 
            INNER JOIN `agency_services` AS a_serv             
            ON ( 
                a.`agency_id` = a_serv.`agency_id` AND 
                a_serv.`service_id` = {$service_type}
            )
            LEFT JOIN `alarm_job_type` AS ajt ON a_serv.`service_id` = ajt.`id`
            WHERE a.`status` = 'active'   
            AND a_serv.`price` {$comparison_operator} {$price}                           
            ";     
            
            

        }else if( $service_source == 2 ){ // property

            $main_sql_str = "
            SELECT
                p.`property_id`,
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,

                ps.`price`,
                ps.`alarm_job_type_id` AS service_type,
           
                ajt.`type` AS ajt_type,
                ajt.`full_name` AS service_full_name
            FROM `property` AS p 
            INNER JOIN `property_services` AS ps             
            ON ( 
                p.`property_id` = ps.`property_id` AND 
                ps.`alarm_job_type_id` = {$service_type} AND                 
                ps.`service` = 1 
            )
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
            WHERE p.`deleted` = 0
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
            AND ps.`price` {$comparison_operator} {$price}        
            AND p.`agency_id` = {$agency_filter}                  
            ";                            

        }  
        
        if( $this->input->get_post('service_source') > 0 ){

            $total_rows = $this->db->query($main_sql_str)->num_rows();   
            $main_sql_str .= $limit_sql_str;
            $main_sql = $this->db->query($main_sql_str);             

        }        
     
        if ($export == 1) { //EXPORT         
            
            // file name
            $date_export = date('YmdHis');
            $filename = "service_price_report_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = [];
            if( $service_source == 1 ){ // agency
                $header[] = "Agency ID";
            }else if( $service_source == 2 ){ // property
                $header[] = "Property ID";
            }

            if( $service_source == 1 ){ // agency
                $header[] = "Agency Name";
            }else if( $service_source == 2 ){ // property
                $header[] = "Property Address";
            }

            $header[] = 'Service Type';
            $header[] = 'Service Price';

            fputcsv($csv_file, $header);
            
            foreach ( $main_sql->result() as $row ){ 

                $csv_row = [];                              

                if( $service_source == 1 ){ // agency
                    $csv_row[] = $row->agency_id;
                }else if( $service_source == 2 ){ // property
                    $csv_row[] = $row->property_id;
                } 
                
                if( $service_source == 1 ){ // agency
                    $csv_row[] = $row->agency_name;
                }else if( $service_source == 2 ){ // property
                    $csv_row[] = "{$row->address_1} {$row->address_2}, {$row->address_3} {$row->state} {$row->postcode}";
                }

                $csv_row[] =$row->ajt_type;  
                $csv_row[] = '$'.number_format($row->price,2);                       
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit; 
            

        }else{

            if( $this->input->get_post('service_source') > 0 ){

                // paginated list
                $data['list'] = $main_sql;                
                $data['page_query'] = $main_sql_str;                

            }  
            
            // filter by agency
            $data['distinct_sql'] = $this->db->query("                   
            SELECT DISTINCT(a.`agency_id`), a.`agency_name`
            FROM `property` AS p 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`               
            ORDER BY a.`agency_name` ASC
            "); 
            
            $data['ajt_sql'] = $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `active` = 1
                ORDER BY `id` ASC
            ");

            $pagi_links_params_arr = array(
                'service_source' => $service_source,
                'service_type' => $service_type,
                'comparison' => $comparison,
                'price' =>  $price
            );
            $pagi_link_params = "{$uri}?".http_build_query($pagi_links_params_arr);
            $data['pagi_links_params_arr'] = $pagi_links_params_arr;

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
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data); 

        }                                    

    }
    */

    public function agency_service_price_report() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Service Price Report";        
        $uri = '/reports/agency_service_price_report';
        $data['uri'] = $uri;

        $service_type = $this->input->get_post('service_type');
        $comparison = $this->input->get_post('comparison');
        $price = $this->input->get_post('price');
        $state = $this->input->get_post('state');       
        $search = $this->input->get_post('search');

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'p.address_2';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        // pagination
        $per_page = $this->config->item('pagi_per_page');        
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 ){ 
            $limit_sql_str = " LIMIT {$offset}, {$per_page}";
        }

        $comparison_operator = ( $comparison == 1 )?'=':'!=';

        // filter by state
        $state_sql_str = null;
        if ( $state != '' ){ 
            $state_sql_str = " AND a.`state` = '{$state}' ";
        }
      
        $main_sql_str = "
        SELECT
            a.`agency_id`,
            a.`agency_name`,            
            a.`address_1` AS a_address_1,
            a.`address_2` AS a_address_2,
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,

            a_serv.`price`,
            a_serv.`service_id` AS service_type,

            ajt.`type` AS ajt_type,
            ajt.`full_name` AS service_full_name
        FROM `agency` AS a 
        LEFT JOIN `price_increase_excluded_agency` AS piea ON a.`agency_id` = piea.`agency_id`
        LEFT JOIN `agency_completed_increase` AS aci ON a.`agency_id` = aci.`agency_id`
        INNER JOIN `agency_services` AS a_serv             
        ON ( 
            a.`agency_id` = a_serv.`agency_id` AND 
            a_serv.`service_id` = {$service_type}
        )
        LEFT JOIN `alarm_job_type` AS ajt ON a_serv.`service_id` = ajt.`id`
        WHERE a.`status` = 'active'   
        AND a_serv.`price` {$comparison_operator} {$price}           
        AND aci.`agency_id` IS NULL 
        AND (
            piea.`exclude_until` < '".date('Y-m-d')."' OR
            piea.`id` IS NULL
        )
        {$state_sql_str}                         
        ";  
        
        if( $this->input->get_post('search') ){

            $total_rows = $this->db->query($main_sql_str)->num_rows();   
            $main_sql_str .= $limit_sql_str;
            $main_sql = $this->db->query($main_sql_str);             

        }        
     
        if ($export == 1) { //EXPORT         
            
            // file name
            $date_export = date('YmdHis');
            $filename = "agency_service_price_report_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = [];
            $header[] = "Agency ID";
            $header[] = "Agency Name";
            $header[] = 'Service Type';
            $header[] = 'Service Price';

            fputcsv($csv_file, $header);
            
            foreach ( $main_sql->result() as $row ){ 

                $csv_row = [];                              

                $csv_row[] = $row->agency_id;
                $csv_row[] = $row->agency_name;                
                $csv_row[] =$row->ajt_type;  
                $csv_row[] = '$'.number_format($row->price,2);                       
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit; 
            

        }else{

            if( $this->input->get_post('search') ){

                // paginated list
                $data['list'] = $main_sql;                
                $data['page_query'] = $main_sql_str;                

            }  
            
            // filter by agency
            $data['distinct_sql'] = $this->db->query("                   
            SELECT DISTINCT(a.`agency_id`), a.`agency_name`
            FROM `property` AS p 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`               
            ORDER BY a.`agency_name` ASC
            "); 
            
            $data['ajt_sql'] = $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `active` = 1
                ORDER BY `id` ASC
            ");

            $pagi_links_params_arr = array(
                'service_type' => $service_type,
                'comparison' => $comparison,
                'price' =>  $price,
                'search' => $search
            );
            $pagi_link_params = "{$uri}?".http_build_query($pagi_links_params_arr);
            $data['pagi_links_params_arr'] = $pagi_links_params_arr;

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
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data); 

        }                                    

    }


    public function property_service_price_report() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Property Service Price Report";        
        $uri = '/reports/property_service_price_report';
        $data['uri'] = $uri;

        $logged_user_id = $this->session->staff_id;
        $today = date('Y-m-d');

        $service_source = $this->input->get_post('service_source');
        $service_type = $this->input->get_post('service_type');
        $comparison = $this->input->get_post('comparison');
        $price = $this->input->get_post('price');
        $agency_filter = $this->input->get_post('agency_filter');
        $search = $this->input->get_post('search');

        // sort
        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'p.address_2';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        // pagination
        $per_page = $this->config->item('pagi_per_page');             
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');
        $process_remaining = $this->input->get_post('process_remaining');
        
        $data['order_by'] = $order_by;
        $data['sort'] = $sort; 

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

        // sort
        $order_by_str = null;
        if( $order_by != '' ){
            $order_by_str = "ORDER BY {$order_by} {$sort}, p.address_3 ASC";
        }

        // export should show all
        $limit_sql_str = null;
        if ( $export != 1 && $process_remaining != 1 ){ 
            $limit_sql_str = " LIMIT {$offset}, {$per_page}";
        }

        $comparison_operator = ( $comparison == 1 )?'=':'!=';

        $service_type_filter = null;
        if( $service_type != '' ){
            $service_type_filter = " AND ps.`alarm_job_type_id` = {$service_type} ";
        }

        $comparison_operator_filter = null;
        if( $comparison != '' ){
            $comparison_operator_filter = " AND ps.`price` {$comparison_operator} {$price}   ";
        }        

      
        $main_sql_str = "
        SELECT
            p.`property_id`,
            p.`address_1` AS p_address_1,
            p.`address_2` AS p_address_2,
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,

            a.`agency_id`,
            a.`agency_name`,            
            a.`address_1` AS a_address_1,
            a.`address_2` AS a_address_2,
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,

            agen_serv.`price` AS as_price,

            aci.`id` AS aci_id,

            ps.`property_services_id` AS ps_id,
            ps.`price` AS ps_price,
            ps.`alarm_job_type_id` AS service_type,
        
            ajt.`type` AS ajt_type,
            ajt.`full_name` AS service_full_name
        FROM `property` AS p 
        LEFT JOIN `property_completed_increase` AS pci ON p.`property_id` = pci.`property_id`
        INNER JOIN `property_services` AS ps             
        ON ( 
            p.`property_id` = ps.`property_id`  
            AND ps.`service` = 1 
            {$service_type_filter}
        )
        LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_services` AS agen_serv ON ( a.`agency_id` = agen_serv.`agency_id` AND ps.`alarm_job_type_id` = agen_serv.service_id  )
        LEFT JOIN `price_increase_excluded_agency` AS piea ON a.`agency_id` = piea.`agency_id`
        LEFT JOIN `agency_completed_increase` as aci ON a.`agency_id` = aci.`agency_id`
        WHERE p.`deleted` = 0
        AND (
            p.`is_nlm` IS NULL 
            OR p.`is_nlm` = 0
        )
        {$comparison_operator_filter}      
        AND p.`agency_id` = {$agency_filter}  
        AND pci.`property_id` IS NULL                      
        AND (
            piea.`exclude_until` < '".date('Y-m-d')."' OR
            piea.`id` IS NULL
        )
        AND (
            aci.`id` > 0 AND
            aci.`agency_completed` = 0 
        )
        {$order_by_str}
        ";   
        
        if( $this->input->get_post('search') ){

            $total_rows = $this->db->query($main_sql_str)->num_rows();   
            $main_sql_str .= $limit_sql_str;
            $main_sql = $this->db->query($main_sql_str);             

        }        
     
        if ($export == 1) { //EXPORT         
            
            // file name
            $date_export = date('YmdHis');
            $filename = "property_service_price_report_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = [];
            $header[] = "Property ID";
            $header[] = "Property Address";
            $header[] = 'Agency';
            $header[] = 'Service Type';
            $header[] = 'Service Price';
            $header[] = 'Agency Price';

            fputcsv($csv_file, $header);
            
            foreach ( $main_sql->result() as $row ){ 

                $csv_row = [];                              

                $csv_row[] = $row->property_id;
                $csv_row[] = "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$row->p_state} {$row->p_postcode}";
                $csv_row[] = $row->agency_name;
                $csv_row[] = $row->ajt_type;  
                $csv_row[] = '$'.number_format($row->ps_price,2);
                $csv_row[] = '$'.number_format($row->as_price,2);                       
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit; 
            

        } else if ( $process_remaining == 1) { //EXPORT    
            
            if( $agency_filter > 0 ){

                foreach ( $main_sql->result() as $row ){ 

                    $ps_id = $row->ps_id;                     
                    
                    if( $ps_id > 0 ){
    
                        // get property services data
                        $ps_sql = $this->db->query("
                        SELECT 
                            `property_id`,
                            `price`,
                            `alarm_job_type_id` AS service_type
                        FROM `property_services`
                        WHERE `property_services_id` = {$ps_id}
                        ");
                        $ps_row = $ps_sql->row();                
                        $service_type = $ps_row->service_type;
                        $property_id = $ps_row->property_id;
    
                        // get service type name
                        $ajt_sql = $this->db->query("
                        SELECT `type`
                        FROM `alarm_job_type`
                        WHERE `id` = {$service_type}
                        ");
                        $ajt_row = $ajt_sql->row();
                        
                        // get agency service data
                        $agen_serv_sql = $this->db->query("
                        SELECT `price`
                        FROM `agency_services`
                        WHERE `agency_id` = {$agency_filter}
                        AND `service_id` = {$service_type}
                        ");
                        $agen_serv_row = $agen_serv_sql->row();                                                         
    
                        // update property service price from agency service price                
                        $update_data = array(
                            'price' => $agen_serv_row->price
                        );                                          
                        $this->db->where('property_services_id', $ps_id);
                        $this->db->update('property_services', $update_data);                                         
    
                    }                                    
    
                    // insert logs
                    $log_details = "{$ajt_row->type} price changed from \$".number_format($ps_row->price,2)." to \$".number_format($agen_serv_row->price,2);
                    $params = array(
                        'title' => 65, // Property Update
                        'details' => $log_details,
                        'display_in_vpd' => 1,									
                        'created_by_staff' => $this->session->staff_id,
                        'property_id' => $property_id
                    );
                    $this->system_model->insert_log($params);                    
    
                }

                redirect('/reports/property_service_price_report');

            }                    

        }else{

            if( $this->input->get_post('search') ){

                // paginated list
                $data['list'] = $main_sql;                
                $data['page_query'] = $main_sql_str;                

            }  
            
            // filter by agency
            $data['distinct_sql'] = $this->db->query("                   
            SELECT DISTINCT(a.`agency_id`), a.`agency_name`
            FROM `agency` AS a 
            LEFT JOIN `agency_completed_increase` as aci ON a.`agency_id` = aci.`agency_id`
            WHERE (
                aci.`id` > 0 AND
                aci.`agency_completed` = 0 
            )
            "); 
            
            $data['ajt_sql'] = $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `active` = 1
                ORDER BY `id` ASC
            ");

            $pagi_links_params_arr = array(
                'service_source' => $service_source,
                'service_type' => $service_type,
                'comparison' => $comparison,
                'price' =>  $price,
                'agency_filter' =>  $agency_filter,
                'search' => $search
            );
            $pagi_link_params = "{$uri}?".http_build_query($pagi_links_params_arr);
            $data['pagi_links_params_arr'] = $pagi_links_params_arr;

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
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data); 

        }                                    

    }


    public function get_agency_services_lightbox(){

        $agency_id = $this->input->get_post('agency_id');        

        if( $agency_id > 0 ){

            // get agency service data
            $agen_serv_sql = $this->db->query("
                SELECT 
                    agen_serv.`service_id`,
                    agen_serv.`price`,

                    ajt.`id`,
                    ajt.`type`
                FROM `agency_services` AS agen_serv  
                LEFT JOIN `alarm_job_type` AS ajt ON agen_serv.`service_id` = ajt.`id`  
                WHERE agen_serv.`agency_id` = {$agency_id}
                AND ajt.`active` = 1
                ORDER BY ajt.`id` ASC
            ");         
            
            // get display on data
            $disp_on_sql = $this->db->query("
            SELECT *
            FROM `display_on`
            WHERE `id` IN(3,6,7)
            AND `active` = 1
            ORDER BY `location` ASC
            "); 

            $lightbox_str = '
            <section class="card card-blue-fill">
                <header class="card-header">Services</header>
                <div class="card-block">

                    <form id="variation_form" action="/reports/save_agency_price_variation_disable" method="post">
                    <table class="table table-hover main-table vad_pricing_table text-left table-no-border">
                        <thead>
                            <tr>
                                <th>Service Types</th>
                                <th>Current Service Price</th>
                                <th>New Service Price</th>
                                <th>Default Service Price</th>                                
                                <th>Variation</th>
                                <th>Reason</th>
                                <th>Display On</th>    
                                <th>Expiry</th>                        
                            </tr>
                        </thead>
                        <tbody>'; 

                        foreach( $agen_serv_sql->result() as $agen_serv_row ){

                            $service_type = $agen_serv_row->service_id;  
                            
                            // get agency specific service price
                            $assp_sql = $this->db->query("
                            SELECT *
                            FROM `agency_specific_service_price`
                            WHERE `service_type` = {$service_type}
                            AND `agency_id` = {$agency_id}
                            ");                            

                            if( $assp_sql->num_rows() > 0 ){

                                $assp_row = $assp_sql->row();                
                                $dynamic_price = $assp_row->price;              

                            }else{

                                // get agency default service price
                                $adsp_sql = $this->db->query("
                                SELECT *
                                FROM `agency_default_service_price`
                                WHERE `service_type` = {$service_type}
                                ");                                 

                                if( $adsp_sql->num_rows() > 0 ){

                                    $adsp_row = $adsp_sql->row();
                                    $dynamic_price = $adsp_row->price;   

                                }

                            }
                                                                                    
                            $lightbox_str .= '
                            <tr class="service_tr">
                                <td>
                                    '.$agen_serv_row->type.'
                                    <img data-toggle="tooltip" title="'.$agen_serv_row->type.'" src="/images/serv_img/'.$this->system_model->getServiceIcons($service_type).'" />
                                    <input type="hidden" name="service_type[]" class="service_type" value="'.$service_type.'" />
                                </td>
                                <td>
                                    $'.number_format($agen_serv_row->price,2).'
                                    <input type="hidden" name="current_price[]" class="current_price" value="'.$agen_serv_row->price.'" />
                                </td>
                                <td>
                                    <input type="text" name="new_service_price[]" class="form-control new_service_price" required />                                                                      
                                </td>
                                <td>
                                    <input type="text" name="dynamic_price[]" class="form-control dynamic_price" value="'.$dynamic_price.'" readonly />
                                </td>                                                                
                                <td>
                                    <input type="text" name="variation_amount[]" class="form-control variation_amount" readonly />
                                </td>
                                <td>
                                    <select name="apv_reason[]" class="form-control apv_reason" required>
                                        <option value="">---</option>
                                    </select>
                                    <input type="hidden" name="apv_type[]" class="apv_type" />
                                </td>
                                <td>
                                    <select name="display_on[]" class="form-control display_on">
                                        <option value="">---</option>';

                                        foreach( $disp_on_sql->result() as $disp_on_row ){
                                            $lightbox_str .= '<option value="'.$disp_on_row->id.'">'.$disp_on_row->location.'</option>';
                                        } 

                                    $lightbox_str .= '
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="apv_expiry" id="apv_expiry" class="flatpickr form-control flatpickr-input apv_expiry" />
                                </td>
                            </tr>';
                        }

            $lightbox_str .= '
                        </tbody>
                    </table>

                    <div class="float-right">
                        <input type="hidden" name="agency_id" id="agency_id" value="'.$agency_id.'" />
                        <input type="hidden" name="row_to_delete" id="row_to_delete" />
                        <button type="submit" id="save_agency_price_variation" class="btn btn-inline">Save</button>
                    </div>
                    </form>

                </div>
            </section>
            ';

            echo $lightbox_str;

        }       

    }


    public function save_agency_price_variation(){

        $service_type_arr = $this->input->post('service_type_arr');
        $current_price_arr = $this->input->post('current_price_arr');
        $new_service_price_arr = $this->input->post('new_service_price_arr');
        $variation_amount_arr = $this->input->post('variation_amount_arr');
        $apv_type_arr = $this->input->post('apv_type_arr');
        $apv_reason_arr = $this->input->post('apv_reason_arr');
        $display_on_arr = $this->input->post('display_on_arr');
        $apv_expiry_arr = $this->input->post('apv_expiry_arr');        

        $agency_id = $this->input->post('agency_id');        
        $logged_user_id = $this->session->staff_id;
        $today = date('Y-m-d');

        $date = date('Y-m-d H:i:s');

        if( $agency_id > 0 ){

            foreach( $service_type_arr as $index => $service_type ){

                $apv_expiry = ($this->system_model->isDateNotEmpty($apv_expiry_arr[$index]))?$this->system_model->formatDate($apv_expiry_arr[$index]):null;

                if( $service_type > 0 ){
                   
                    // update agency service price
                    $update_data = array(
                        'price' => $new_service_price_arr[$index]
                    );            
                    $this->db->where('service_id', $service_type);
                    $this->db->where('agency_id', $agency_id);
                    $this->db->update('agency_services', $update_data);

                    if( is_numeric($variation_amount_arr[$index]) && $variation_amount_arr[$index] != 0 && is_numeric($apv_type_arr[$index]) ){

                        // check if service and type exist per service on agency
                        $apv_sql = $this->db->query("
                        SELECT *
                        FROM `agency_price_variation`
                        WHERE `type` = {$apv_type_arr[$index]}
                        AND `agency_id` = {$agency_id}
                        AND (
                            `scope` >= 2 AND
                            `scope` = {$service_type}
                        )  
                        AND `active` = 1                    
                        ");

                        if( $apv_sql->num_rows() > 0 ){ // update

                            $apv_row = $apv_sql->row();

                            // update agency service price
                            $update_data = array(
                                'amount' => $variation_amount_arr[$index],
                                'reason' => $apv_reason_arr[$index],
                                'expiry' => $apv_expiry,
                                'updated_date' => $date
                            );            
                            $this->db->where('scope', $service_type);
                            $this->db->where('agency_id', $agency_id);
                            $this->db->where('type', $apv_type_arr[$index]);
                            $this->db->update('agency_price_variation', $update_data);
                            $apv_id = $apv_row->id;
                            
                        }else{

                            // save agency price variation
                            $insert_data = array(
                                'scope' => $service_type,
                                'agency_id' => $agency_id,
                                'type' => $apv_type_arr[$index],
                                'amount' => $variation_amount_arr[$index],
                                'reason' => $apv_reason_arr[$index],
                                'expiry' => $apv_expiry,
                                'created_date' => $date
                            );            
                            $this->db->insert('agency_price_variation', $insert_data);
                            $apv_id = $this->db->insert_id();

                        } 

                        if( $display_on_arr[$index] > 0 ){

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
                                    'display_on' => $display_on_arr[$index]
                                );            
                                $this->db->where('variation_id', $apv_id);
                                $this->db->where('type', $dv_type);
                                $this->db->update('display_variation', $update_data);
                                
                            }else{ // new, insert

                                // insert display on  
                                $insert_data = array(
                                    'variation_id' => $apv_id,                    
                                    'type' => $dv_type,
                                    'display_on' => $display_on_arr[$index]                
                                );            
                                $this->db->insert('display_variation', $insert_data);                                

                            }

                        }                                                                                                                  

                    } 
                    
                    // discount or surcharge
                    $dis_surch_str = " with no variation applied.";    
                    if( $apv_type_arr[$index] == 1 ){ // discount
                        $dis_surch_str = " with a discount of \$".number_format($variation_amount_arr[$index],2);
                    }else if( $apv_type_arr[$index] == 2 ){ // surcharge
                        $dis_surch_str = " with a surcharge of \$".number_format($variation_amount_arr[$index],2);
                    } 

                    // reason                               
                    if( $apv_reason_arr[$index] > 0 ){

                        // get price variation reason                        
                        $apvr_sql = $this->db->query("
                        SELECT `reason`
                        FROM `agency_price_variation_reason`
                        WHERE `id` = {$apv_reason_arr[$index]}
                        ");
                        $apvr_row = $apvr_sql->row();   
                        $reason_str = null;

                        if( $apv_type_arr[$index] == 1 ){ // discount
                            $reason_str = ", due to {$apvr_row->reason}";
                        }                      
                        
                    } 
                    
                    // dynamic insert log
                    $disp_on_str = ', displaying on nowhere';
                    if( $display_on_arr[$index] > 0 ){ 

                        // get display on
                        $disp_on_sql = $this->db->query("
                        SELECT `location`
                        FROM `display_on`
                        WHERE `id` = {$display_on_arr[$index]}
                        ");
                        $disp_on_row = $disp_on_sql->row();   
                        
                        $disp_on_str = ", displaying on {$disp_on_row->location}";
                        
                    }

                    // expiring on
                    $expiry_str = null;
                    if( $apv_expiry != '' ){                             
                        $expiry_str = ", expiring on {$apv_expiry_arr[$index]}";
                    }                           
                    
                    // get service type name
                    $ajt_sql = $this->db->query("
                    SELECT `type`
                    FROM `alarm_job_type`
                    WHERE `id` = {$service_type}
                    ");
                    $ajt_row = $ajt_sql->row();

                    $log_details = "{$ajt_row->type} changed from <b>\$".number_format($current_price_arr[$index],2)."</b> to <b>\$".number_format($new_service_price_arr[$index],2)."</b>{$dis_surch_str}{$reason_str}{$disp_on_str}{$expiry_str}";
                                            
                    $log_params = array(
                        'title' => 46,  // Agency Update
                        'details' => $log_details,
                        'display_in_vad' => 1,
                        'created_by_staff' => $logged_user_id,
                        'agency_id' => $agency_id
                    );
                    $this->system_model->insert_log($log_params);

                }                        
    
            }

            // mark agency as submitted
            $insert_data = array(
                'agency_id' => $agency_id,
                'submitted_by' => $logged_user_id,
                'date' => $today
            );            
            $this->db->insert('agency_completed_increase', $insert_data);            

            $ret_arr = array(
                'success' => 1
            );
    
            echo json_encode($ret_arr);

        } 

    }


    public function get_variation_amount(){

        $agency_id = $this->input->get_post('agency_id');
        $service_type = $this->input->get_post('service_type'); 
        $new_service_price = $this->input->get_post('new_service_price');        
        $variation_amount = 0;

        if( $agency_id > 0 && $service_type > 0 ){

            // get agency specific service price
            $assp_sql = $this->db->query("
            SELECT *
            FROM `agency_specific_service_price`
            WHERE `service_type` = {$service_type}
            AND `agency_id` = {$agency_id}
            ");                            

            if( $assp_sql->num_rows() > 0 ){

                $assp_row = $assp_sql->row();
                $variation_amount = $assp_row->price;  
                $variation_amount_abs = abs($new_service_price-$assp_row->price);
                $dynamic_price = $assp_row->price;              

            }else{

                // get agency default service price
                $adsp_sql = $this->db->query("
                SELECT *
                FROM `agency_default_service_price`
                WHERE `service_type` = {$service_type}
                ");                                 

                if( $adsp_sql->num_rows() > 0 ){

                    $adsp_row = $adsp_sql->row();
                    $variation_amount = ($new_service_price-$adsp_row->price);
                    $variation_amount_abs = abs($new_service_price-$adsp_row->price);
                    $dynamic_price = $adsp_row->price;   

                }

            }

            $variation_amount_abs;

        } 
        
        //$is_discount = ( $variation_amount < 0 )?1:0;
        //$apv_type = ( $is_discount == 1 )?1:2;

        $is_discount = null;
        $apv_type = null;
        if( $variation_amount < 0 ){
            $is_discount = 1;
            $apv_type = 1;
        }else if( $variation_amount > 0 ){
            $is_discount = 0;
            $apv_type = 2;
        }
        
        $apvr_option_str = null;
        if( is_numeric($is_discount) ){

            // get agency price variation reason
            $apvr_sql = $this->db->query("
            SELECT *
            FROM `agency_price_variation_reason`
            WHERE `active` = 1    
            AND `is_discount` = {$is_discount}                 
            ORDER BY `reason` ASC
            ");

            $apvr_option_str .= '<option value="">---</option>';        
            foreach( $apvr_sql->result() as $apvr_row ){
                $apvr_option_str .= '<option value="'.$apvr_row->id.'">'.$apvr_row->reason.'</option>';
            } 

        }
        
        
        $ret_arr = array(
            'variation_amount' => $variation_amount_abs,   
            'dynamic_price' => $dynamic_price,         
            'apv_type' => $apv_type,
            'apvr_option_str' => $apvr_option_str
        );

        echo json_encode($ret_arr);

    }


    public function save_agency_variation(){
        
        $agency_id = $this->input->post('agency_id');
        $agency_price_variation_amount = $this->input->post('agency_price_variation_amount');
        $apv_type = $this->input->post('apv_type');
        $apv_reason = $this->input->post('apv_reason');
        $apv_display_on = $this->input->post('apv_display_on');        
        $scope = 1; // property

        $date = date('Y-m-d H:i:s');

        if( $agency_id > 0 && $agency_price_variation_amount > 0 ){

            $insert_data = array(
                'agency_id' => $agency_id,
                'type' => $apv_type,
                'amount' => $agency_price_variation_amount,
                'reason' => $apv_reason,
                'scope' => $scope,
                'created_date' => $date
            );            
            $this->db->insert('agency_price_variation', $insert_data);
            $agency_price_variation_id = $this->db->insert_id();

            ##insert data to display_variation table
            if( $apv_display_on!="" ){

                // check display variation already exist
                $dv_type = 1; // agency
                $dv_sql = $this->db->query("
                SELECT COUNT(`id`) AS dv_count
                FROM `display_variation`
                WHERE `variation_id` = {$agency_price_variation_id}
                AND `type` = {$dv_type}                 
                ");

                if( $dv_sql->row()->dv_count > 0 ){ // exist, update

                    // update display on               
                    $update_data = array(
                        'display_on' => $apv_display_on
                    );            
                    $this->db->where('variation_id', $agency_price_variation_id);
                    $this->db->where('type', $dv_type);
                    $this->db->update('display_variation', $update_data);
                    
                }else{ // new, insert

                    // insert display on  
                    $insert_data = array(
                        'variation_id' => $agency_price_variation_id,                    
                        'type' => $dv_type,
                        'display_on' => $apv_display_on            
                    );            
                    $this->db->insert('display_variation', $insert_data);                                

                }

            }
            ##insert data to display_variation table end            

            $ret_arr = array(
                'success' => 1
            );
    
            echo json_encode($ret_arr);

        }        

    }


    public function save_property_variation(){
        
        $agency_id = $this->input->post('agency_id');
        $agency_price_variation = $this->input->post('agency_price_variation');             

        $ps_arr = $this->input->post('ps_arr');

        $logged_user_id = $this->session->staff_id;
        $today = date('Y-m-d H:i:s');

        if( $agency_id > 0 && $agency_price_variation > 0 ){
            
            foreach( $ps_arr as $ps_id ){

                // get property services data
                $ps_sql = $this->db->query("
                SELECT 
                    `property_id`,
                    `price`,
                    `alarm_job_type_id` AS service_type
                FROM `property_services`
                WHERE `property_services_id` = {$ps_id}
                ");
                $ps_row = $ps_sql->row();
                $property_id = $ps_row->property_id;
                $service_type = $ps_row->service_type;
                
                // get agency service data
                $agen_serv_sql = $this->db->query("
                SELECT `price`
                FROM `agency_services`
                WHERE `agency_id` = {$agency_id}
                AND `service_id` = {$service_type}
                ");
                $agen_serv_row = $agen_serv_sql->row(); 
                $final_price = $agen_serv_row->price;                                           

                // get agency price variation
                $apv_sql = $this->db->query("
                SELECT 
                    apv.`id`,
                    apv.`amount`,
                    apv.`type`,
                    apv.`reason` AS apv_reason,
                    apv.`scope`,
    
                    apvr.`reason` AS apvr_reason
                FROM `agency_price_variation` AS apv
                LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
                WHERE apv.`id` = {$agency_price_variation}                    
                "); 
                $apv_row = $apv_sql->row();

                if( $apv_row->type == 1 ){ // discount
                    $final_price = ( $agen_serv_row->price - $apv_row->amount );
                }else{ // surcharge
                    $final_price = ( $agen_serv_row->price + $apv_row->amount );
                }

                if( $ps_id > 0 ){

                    // update property service price                
                    $update_data = array(
                        'price' => $final_price
                    );                                          
                    $this->db->where('property_services_id', $ps_id);
                    $this->db->update('property_services', $update_data);  
                    
                }                                                              

                // get property variation
                $pv_sql = $this->db->query("
                SELECT COUNT(`id`) AS pv_count
                FROM `property_variation`
                WHERE `property_id` = {$property_id}                    
                AND `active` = 1
                ");
                $pv_row = $pv_sql->row();                
    
                if( $pv_row->pv_count > 0 ){ // it exist, update
    
                    $this->db->query("
                    UPDATE `property_variation`
                    SET `agency_price_variation` = {$agency_price_variation}
                    WHERE `property_id` = {$property_id}  
                    AND `active` = 1                  
                    ");
    
                    $log_details = "Property price variation updated to <b>\$".number_format($apv_row->amount, 2)."</b> ".( ( $apv_row->type == 1 )?'Discount':'Surcharge' );
                    $params = array(
                        'title' => 65, // Property Update
                        'details' => $log_details,
                        'display_in_vpd' => 1,
                        'agency_id' => $agency_id,
                        'created_by_staff' => $this->session->staff_id,
                        'property_id' => $property_id
                    );
                    $this->system_model->insert_log($params);
    
                }else{ // insert
    
                    if( $agency_price_variation > 0 ){
                        
                        // insert new 
                        $this->db->query("
                        INSERT INTO 
                        `property_variation`(
                            `property_id`,
                            `agency_price_variation`,
                            `date_applied`
                        )
                        VALUES(
                            {$property_id},
                            {$agency_price_variation},
                            '{$today}'
                        )                 
                        ");
                
                        $log_details = "Property price variation set to <b>\$".number_format($apv_row->amount, 2)."</b> ".( ( $apv_row->type == 1 )?'Discount':'Surcharge' );
                        $params = array(
                            'title' => 65, // Property Update
                            'details' => $log_details,
                            'display_in_vpd' => 1,
                            'agency_id' => $agency_id,
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $property_id
                        );
                        $this->system_model->insert_log($params);

                        // get service type name
                        $ajt_sql = $this->db->query("
                        SELECT `type`
                        FROM `alarm_job_type`
                        WHERE `id` = {$service_type}
                        ");
                        $ajt_row = $ajt_sql->row();

                        // insert logs
                        $log_details = "{$ajt_row->type} price changed from \$".number_format($ps_row->price,2)." to \$".number_format($final_price,2);
                        $params = array(
                            'title' => 65, // Property Update
                            'details' => $log_details,
                            'display_in_vpd' => 1,									
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $property_id
                        );
                        $this->system_model->insert_log($params);  
    
                    }                        
    
                }

                // get property variation
                $pci_sql = $this->db->query("
                SELECT COUNT(`id`) AS pci_count
                FROM `property_completed_increase`
                WHERE `property_id` = {$property_id}
                ");
                $pci_row = $pci_sql->row();                
    
                if( $pci_row->pci_count > 0 ){ // existing, update

                    // update agency service price
                    $update_data = array(                        
                        'submitted_by' => $logged_user_id,
                        'date' => $today
                    );            
                    $this->db->where('property_id', $property_id);
                    $this->db->update('property_completed_increase', $update_data);


                }else{ // new, insert

                    // mark property as submitted
                    $insert_data = array(
                        'property_id' => $property_id,
                        'submitted_by' => $logged_user_id,
                        'date' => $today
                    );            
                    $this->db->insert('property_completed_increase', $insert_data);

                }
                
    
            }                           

            $ret_arr = array(
                'success' => 1
            );
    
            echo json_encode($ret_arr);

        }        

    }


    public function mark_agency_completed_for_price_increase(){
        
        $agency_id = $this->input->post('agency_id');            

        if( $agency_id > 0 ){

           // update agency service price
           $update_data = array(                        
                'agency_completed' => 1
            );            
            $this->db->where('agency_id', $agency_id);
            $this->db->update('agency_completed_increase', $update_data);           

        }        

    }


    public function user_logs() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "User Logs";
        $country_id = $this->config->item('country');
        $uri = '/reports/user_logs';
        $data['uri'] = $uri;
        
        $crm_user_filter = $this->input->get_post('crm_user_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):null;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        // user filter
        if( $crm_user_filter > 0 ){
            $crm_user_filter_l_str = "AND l.`created_by_staff` = {$crm_user_filter}";    
            $crm_user_filter_jl_str = "AND jl.`staff_id` = {$crm_user_filter}"; 
            $crm_user_filter_pel_str = "AND pel.`staff_id` = {$crm_user_filter}"; 
            $crm_user_filter_ael_str = "AND ael.`staff_id` = {$crm_user_filter}";
        }

        // date filter
        if( $date_filter != '' ){
            $date_filter_l_str = "AND CAST( l.`created_date` AS Date ) = '{$date_filter}'"; 
            $date_filter_jl_str = "AND CAST( jl.`eventdate` AS Date ) = '{$date_filter}'"; 
            $date_filter_pel_str = "AND CAST( pel.`log_date` AS Date ) = '{$date_filter}'";
            $date_filter_ael_str = "AND CAST( ael.`eventdate` AS Date ) = '{$date_filter}'";
        }
        
        if( $this->input->get_post('search_submit') ){
        
            $logs_sql_str = "
                (
                    SELECT
                        'New Logs' AS log_type,
                        ltit.`title_name` AS log_title,
                        l.`details` AS log_details,
                        l.`created_date` AS log_date
                    FROM `logs` AS l
                    LEFT JOIN `log_titles` AS ltit ON l.`title` = ltit.`log_title_id`
                    WHERE l.`log_id` > 0
                    {$crm_user_filter_l_str}
                    {$date_filter_l_str}
                ) 
                    UNION
                (
                    SELECT
                        'Old Job Logs' AS log_type,
                        jl.`contact_type` AS log_title,
                        jl.`comments` AS log_details,
                        concat(jl.`eventdate`, ' ', jl.`eventtime`) AS log_date
                    FROM `job_log` AS jl  
                    WHERE jl.`log_id` > 0   
                    {$crm_user_filter_jl_str}
                    {$date_filter_jl_str}          
                ) 
                    UNION
                (
                    SELECT
                        'Old Property Logs' AS log_type,
                        pel.`event_type` AS log_title,
                        pel.`event_details` AS log_details,
                        pel.`log_date` AS log_date
                    FROM `property_event_log` AS pel  
                    WHERE pel.`id` > 0   
                    {$crm_user_filter_pel_str}
                    {$date_filter_pel_str}          
                )
                    UNION
                (
                    SELECT
                        'Old Agency Logs' AS log_type,
                        ael.`contact_type` AS log_title,
                        ael.`comments` AS log_details,
                        ael.`eventdate` AS log_date
                    FROM `agency_event_log` AS ael  
                    WHERE ael.`agency_event_log_id` > 0   
                    {$crm_user_filter_ael_str}
                    {$date_filter_ael_str}          
                )
                ORDER BY log_date DESC
                LIMIT {$offset}, {$per_page} 
            ";
            
            $data['logs_sql'] = $this->db->query($logs_sql_str);
            $data['show_query'] = $this->db->last_query();

            // get all
            $property_sql_str = "
            (
                SELECT
                    'logs' AS log_type,
                    ltit.`title_name` AS log_title,
                    l.`details` AS log_details,
                    l.`created_date` AS log_date
                FROM `logs` AS l
                LEFT JOIN `log_titles` AS ltit ON l.`title` = ltit.`log_title_id`
                WHERE l.`log_id` > 0
                {$crm_user_filter_l_str}
                {$date_filter_l_str}
            ) UNION
            (
                SELECT
                    'job_logs' AS log_type,
                    jl.`contact_type` AS log_title,
                    jl.`comments` AS log_details,
                    jl.`created_date` AS log_date
                FROM `job_log` AS jl  
                WHERE jl.`log_id` > 0   
                {$crm_user_filter_jl_str}
                {$date_filter_jl_str}          
            )        
            ";
            $property_sql = $this->db->query($property_sql_str);
            $total_rows = $property_sql->num_rows();   
            
            $pagi_links_params_arr = array(
                'crm_user_filter' => $crm_user_filter,
                'date_filter' => $this->input->get_post('date_filter')
            );
            
            $data['header_link_params'] = $pagi_links_params_arr;
    
            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  
    
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
            
            // update page total
            $page_tot_params = array(
                'page' => $uri,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);
        
        }
        
        // distinct user
        $crm_user_filter_sql_str = "            
        SELECT sa.`StaffID`, sa.`FirstName`, sa.`LastName`
        FROM `staff_accounts` AS sa 
        WHERE sa.`active` = 1
        AND sa.`Deleted` = 0
        ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
        ";
        $data['crm_user_filter_sql'] = $this->db->query($crm_user_filter_sql_str);        

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
                

    }

    public function api_unlinked_properties(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "API Un-Linked Properties";
        $uri = '/reports/api_unlinked_properties';
        $data['uri'] = $uri;

        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        ##post 
        $search = $this->input->get_post('search');
        $agency_filter = $this->input->get_post('agency_filter');

        $country_id = $this->config->item('country');

        $remove_adams_str = null;
        if( $country_id == 1 ){ // on AU remove adams test, requested by Charlotte B.
            $remove_adams_str = ' AND a.`agency_id` != 1448 ';
        }

        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        $tt_q = $this->db->query(
            "SELECT  DISTINCT `pnv`.`property_id`
            FROM `properties_needs_verification` AS `pnv` 
            LEFT JOIN `property` AS `p` ON pnv.`property_id` = p.`property_id` AND pnv.`property_source`=1 
            INNER JOIN `agency` AS `a` ON CASE WHEN pnv.`property_source`= 1 THEN p.`agency_id` = a.`agency_id` WHEN ( pnv.`property_source`= 2 OR pnv.`property_source`= 3 OR pnv.`property_source`= 7) THEN pnv.`agency_id` = a.`agency_id` END 
            LEFT JOIN `api_property_data` AS `apd` ON p.`property_id` = apd.`crm_prop_id` 
            LEFT JOIN `property_services` AS `ps` ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 ) 
            WHERE `pnv`.`active` = 1 
            AND `pnv`.`ignore_issue` = 0 
            AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL) 
            AND (`apd`.`api_prop_id` = '' OR `apd`.`api_prop_id` IS NULL)"
        );
        $ttmo = array();
        foreach( $tt_q->result_array() as $tt_row ){
            $ttmo[] = intval($tt_row['property_id']);
        }

        if( !empty($ttmo) ){
            $ttmo_implode = implode(", ", $ttmo);
            $property_id_not_in = "AND p.property_id NOT IN(".$ttmo_implode.")";
        }
        
        $custom_where = "
        {$property_id_not_in}
        {$remove_adams_str}
        {$agency_filter_sql_str}
        ";

        $sel_query = "`p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`holiday_rental`, `a`.`agency_id`, `a`.`agency_name`, `aght`.`priority`, apt.agency_api_token_id, apd.api_prop_id, apd.api, aapi.api_name, apt.api_id";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'limit' => $per_page,
            'offset' => $offset
        );
        $data['list'] = $this->properties_model->get_unlinked_api_properties($params);
        $data['last_query'] = $this->db->last_query();

        $params_total = array(
            'sel_query' =>"COUNT(p.property_id) as p_count",
            'custom_where' => $custom_where
        );
        $total = $this->properties_model->get_unlinked_api_properties($params_total);
        if( $total!==false ){
            $total_rows = $total->row()->p_count;
        }else{
            $total_rows = 0;
        }
       

        // filter by agency
        $data['agency_filter_sql'] = $this->db->query("                   
        SELECT DISTINCT(a.`agency_id`), a.`agency_name`
        FROM `agency` AS a 
        WHERE a.`status` = 'active'
        {$remove_adams_str}
        ORDER BY a.`agency_name` ASC        
        "); 

        // pagination 
        $pagi_links_params_arr = array(
            'search' => $search
        );
        $pagi_link_params = $uri.'?'.http_build_query($pagi_links_params_arr);

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
        
        // update page total
        $page_tot_params = array(
            'page' => $uri,
            'total' => $total_rows
        );

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function compliance_report_qld() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Compliance Report (QLD)";
        $uri = '/reports/compliance_report_qld';
        $data['uri'] = $uri;

        $from_date_filter = ( $this->input->get_post('from_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('from_date_filter')):null;
        $to_date_filter = ( $this->input->get_post('to_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('to_date_filter')):null;
        $agency_filter = $this->input->get_post('agency_filter');        

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        $date_filter_sql_str = null;
        if( $from_date_filter != '' && $to_date_filter ){
            $date_filter_sql_str = " AND j.`date` BETWEEN '{$from_date_filter}' AND '{$to_date_filter}' ";
        }

        // select query
        $sel_query = "
        SELECT 
        j.`id` AS jid, 
        j.`door_knock`,
        j.`created` AS jcreated,
        j.job_price,
        j.job_type,
        j.`date` AS jdate,
        j.`due_date`,
        
        p.`property_id`,
        p.`address_1`, 
        p.`address_2`, 
        p.`address_3`,

        a.agency_id,
        a.`agency_name`,
        aght.priority
        ";
        
        // main query body
        $main_query = "
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        WHERE j.`status` = 'Completed'
        AND p.`state` = 'QLD'
        AND j.`job_type` IN('Change of Tenancy','Lease Renewal')
        AND p.`deleted` = 0
        AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
        AND j.`del_job` = 0
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND j.`due_date` != ''
        ";        

        if ($export == 1) { //EXPORT         
            
            // file name
            $date_export = date('YmdHis');
            $filename = "compliance_report_qld_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = [];
            $header[] = "Date";
            $header[] = "End Date";
            $header[] = 'Created Date';
            $header[] = 'Age';
            $header[] = 'Property';
            $header[] = 'Agency';
            $header[] = 'Job Type';
            $header[] = 'Compliant';

            fputcsv($csv_file, $header);

            // main listing
            $main_list = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            ");
            
            foreach ( $main_list->result_array() as $row ){ 

                $csv_row = [];                              

                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jdate']) )?date("d/m/Y",strtotime($row['jdate'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['due_date']) )?date("d/m/Y",strtotime($row['due_date'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jcreated']) )?date("d/m/Y",strtotime($row['jcreated'])):null;
                $csv_row[] = $this->gherxlib->getAge($row['jcreated']);
                $csv_row[] = "{$row['address_1']} {$row['address_2']}, {$row['address_3']}";
                $csv_row[] = $row['agency_name'];
                $csv_row[] = $row['job_type'];
                $csv_row[] = ( $row['jdate'] <= $row['due_date'] )?'Yes':'No';
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit; 
            

        }else{

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            LIMIT {$offset}, {$per_page}
            ");
            $data['sql_query'] = $this->db->last_query();

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(j.`id`) AS jcount
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            ");
            $total_rows = $total_rows_sql->row()->jcount;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.agency_id), a.`agency_name`
            {$main_query} 
            ORDER BY a.`agency_name` ASC           
            ");

            // pagination
            $pagi_links_params_arr = array(            
                'from_date_filter' => $this->input->get_post('from_date_filter'),
                'to_date_filter' => $this->input->get_post('to_date_filter'),
                'agency_filter' => $agency_filter
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);

        }                        

    }


    public function compliance_report_nsw_act() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Compliance Report (NSW, ACT & SA)";
        $uri = '/reports/compliance_report_nsw_act';
        $data['uri'] = $uri;

        $from_date_filter = ( $this->input->get_post('from_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('from_date_filter')):null;
        $to_date_filter = ( $this->input->get_post('to_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('to_date_filter')):null;
        $agency_filter = $this->input->get_post('agency_filter');   
        $state_filter = $this->input->get_post('state_filter');       

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        $date_filter_sql_str = null;
        if( $from_date_filter != '' && $to_date_filter ){
            $date_filter_sql_str = " AND j.`date` BETWEEN '{$from_date_filter}' AND '{$to_date_filter}' ";
        }

        if( $state_filter != ''  ){
            $state_filter_sql_str = " AND p.`state` = '{$state_filter}' ";
        }

        // select query
        $sel_query = "
        SELECT 
        jc.`retest_date` AS jc_retest_date,

        j.`id` AS jid, 
        j.`door_knock`,
        j.`created` AS jcreated,
        j.job_price,
        j.job_type,
        j.`date` AS jdate,
        j.`due_date`,
        
        p.`property_id`,
        p.`address_1`, 
        p.`address_2`, 
        p.`address_3`,
        p.`state`,

        a.agency_id,
        a.`agency_name`,
        aght.priority
        ";
        
        // main query body
        $main_query = "
        FROM `job_compliance` AS jc
        LEFT JOIN `jobs` AS j ON jc.`job_id` = j.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        WHERE j.`status` = 'Completed'
        AND p.`state` IN ('NSW','ACT', 'SA')
        AND j.`job_type` = 'Yearly Maintenance'
        AND p.`deleted` = 0
        AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
        AND j.`del_job` = 0
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND jc.`retest_date` != ''
        ";        

        if ($export == 1) { //EXPORT         
            
            // file name
            $date_export = date('YmdHis');
            $filename = "compliance_report_nsw_act_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = [];
            $header[] = "Date";
            $header[] = "End Date";
            $header[] = 'Created Date';
            $header[] = 'Age';
            $header[] = 'Property';
            $header[] = 'Agency';
            $header[] = 'Job Type';
            $header[] = 'Compliant';

            fputcsv($csv_file, $header);

            // main listing
            $main_list = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$state_filter_sql_str}
            {$date_filter_sql_str}
            ");
            
            foreach ( $main_list->result_array() as $row ){ 

                $csv_row = [];                              

                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jdate']) )?date("d/m/Y",strtotime($row['jdate'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['due_date']) )?date("d/m/Y",strtotime($row['due_date'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jcreated']) )?date("d/m/Y",strtotime($row['jcreated'])):null;
                $csv_row[] = $this->gherxlib->getAge($row['jcreated']);
                $csv_row[] = "{$row['address_1']} {$row['address_2']}, {$row['address_3']}";
                $csv_row[] = $row['agency_name'];
                $csv_row[] = $row['job_type'];
                $csv_row[] = ( $row['jdate'] <= $row['jc_retest_date'] )?'Yes':'No';
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit; 
            
        }else{

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$state_filter_sql_str}
            {$date_filter_sql_str}
            LIMIT {$offset}, {$per_page}
            ");
            $data['sql_query'] = $this->db->last_query();
            

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(j.`id`) AS jcount
            {$main_query}
            {$agency_filter_sql_str}
            {$state_filter_sql_str}
            {$date_filter_sql_str}
            ");
            $total_rows = $total_rows_sql->row()->jcount;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.agency_id), a.`agency_name`
            {$main_query} 
            ORDER BY a.`agency_name` ASC           
            ");

            // pagination
            $pagi_links_params_arr = array(            
                'from_date_filter' => $this->input->get_post('from_date_filter'),
                'to_date_filter' => $this->input->get_post('to_date_filter'),
                'agency_filter' => $agency_filter,
                'state_filter' => $this->input->get_post('state_filter')
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }

    public function compliance_report_nsw() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Compliance Report (NSW)";
        $uri = '/reports/compliance_report_nsw';
        $data['uri'] = $uri;

        $from_date_filter = ( $this->input->get_post('from_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('from_date_filter')):null;
        $to_date_filter = ( $this->input->get_post('to_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('to_date_filter')):null;
        $agency_filter = $this->input->get_post('agency_filter');        

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        $date_filter_sql_str = null;
        if( $from_date_filter != '' && $to_date_filter ){
            $date_filter_sql_str = " AND j.`date` BETWEEN '{$from_date_filter}' AND '{$to_date_filter}' ";
        }

        // select query
        $sel_query = "
        SELECT 
        jc.`retest_date` AS jc_retest_date,

        j.`id` AS jid, 
        j.`door_knock`,
        j.`created` AS jcreated,
        j.job_price,
        j.job_type,
        j.`date` AS jdate,
        j.`due_date`,
        
        p.`property_id`,
        p.`address_1`, 
        p.`address_2`, 
        p.`address_3`,

        a.agency_id,
        a.`agency_name`,
        aght.priority
        ";
        
        // main query body
        $main_query = "
        FROM `job_compliance` AS jc
        LEFT JOIN `jobs` AS j ON jc.`job_id` = j.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        WHERE j.`status` = 'Completed'
        AND p.`state` IN ('NSW')
        AND j.`job_type` = 'Yearly Maintenance'
        AND p.`deleted` = 0
        AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
        AND j.`del_job` = 0
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND jc.`retest_date` != ''
        ";        

        if ($export == 1) { //EXPORT         
            
            // file name
            $date_export = date('YmdHis');
            $filename = "compliance_report_nsw_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = [];
            $header[] = "Date";
            $header[] = "End Date";
            $header[] = 'Created Date';
            $header[] = 'Age';
            $header[] = 'Property';
            $header[] = 'Agency';
            $header[] = 'Job Type';
            $header[] = 'Compliant';

            fputcsv($csv_file, $header);

            // main listing
            $main_list = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            ");
            
            foreach ( $main_list->result_array() as $row ){ 

                $csv_row = [];                              

                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jdate']) )?date("d/m/Y",strtotime($row['jdate'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['due_date']) )?date("d/m/Y",strtotime($row['due_date'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jcreated']) )?date("d/m/Y",strtotime($row['jcreated'])):null;
                $csv_row[] = $this->gherxlib->getAge($row['jcreated']);
                $csv_row[] = "{$row['address_1']} {$row['address_2']}, {$row['address_3']}";
                $csv_row[] = $row['agency_name'];
                $csv_row[] = $row['job_type'];
                $csv_row[] = ( $row['jdate'] <= $row['jc_retest_date'] )?'Yes':'No';
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit; 
            
        }else{

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            LIMIT {$offset}, {$per_page}
            ");
            $data['sql_query'] = $this->db->last_query();

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(j.`id`) AS jcount
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            ");
            $total_rows = $total_rows_sql->row()->jcount;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.agency_id), a.`agency_name`
            {$main_query} 
            ORDER BY a.`agency_name` ASC           
            ");

            // pagination
            $pagi_links_params_arr = array(            
                'from_date_filter' => $this->input->get_post('from_date_filter'),
                'to_date_filter' => $this->input->get_post('to_date_filter'),
                'agency_filter' => $agency_filter
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('/reports/compliance_report_nsw_act', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }

    public function compliance_report_act() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Compliance Report (ACT)";
        $uri = '/reports/compliance_report_act';
        $data['uri'] = $uri;

        $from_date_filter = ( $this->input->get_post('from_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('from_date_filter')):null;
        $to_date_filter = ( $this->input->get_post('to_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('to_date_filter')):null;
        $agency_filter = $this->input->get_post('agency_filter');        

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        $date_filter_sql_str = null;
        if( $from_date_filter != '' && $to_date_filter ){
            $date_filter_sql_str = " AND j.`date` BETWEEN '{$from_date_filter}' AND '{$to_date_filter}' ";
        }

        // select query
        $sel_query = "
        SELECT 
        jc.`retest_date` AS jc_retest_date,

        j.`id` AS jid, 
        j.`door_knock`,
        j.`created` AS jcreated,
        j.job_price,
        j.job_type,
        j.`date` AS jdate,
        j.`due_date`,
        
        p.`property_id`,
        p.`address_1`, 
        p.`address_2`, 
        p.`address_3`,

        a.agency_id,
        a.`agency_name`,
        aght.priority
        ";
        
        // main query body
        $main_query = "
        FROM `job_compliance` AS jc
        LEFT JOIN `jobs` AS j ON jc.`job_id` = j.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        WHERE j.`status` = 'Completed'
        AND p.`state` IN ('ACT')
        AND j.`job_type` = 'Yearly Maintenance'
        AND p.`deleted` = 0
        AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
        AND j.`del_job` = 0
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND jc.`retest_date` != ''
        ";        

        if ($export == 1) { //EXPORT         
            
            // file name
            $date_export = date('YmdHis');
            $filename = "compliance_report_act_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = [];
            $header[] = "Date";
            $header[] = "End Date";
            $header[] = 'Created Date';
            $header[] = 'Age';
            $header[] = 'Property';
            $header[] = 'Agency';
            $header[] = 'Job Type';
            $header[] = 'Compliant';

            fputcsv($csv_file, $header);

            // main listing
            $main_list = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            ");
            
            foreach ( $main_list->result_array() as $row ){ 

                $csv_row = [];                              

                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jdate']) )?date("d/m/Y",strtotime($row['jdate'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['due_date']) )?date("d/m/Y",strtotime($row['due_date'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jcreated']) )?date("d/m/Y",strtotime($row['jcreated'])):null;
                $csv_row[] = $this->gherxlib->getAge($row['jcreated']);
                $csv_row[] = "{$row['address_1']} {$row['address_2']}, {$row['address_3']}";
                $csv_row[] = $row['agency_name'];
                $csv_row[] = $row['job_type'];
                $csv_row[] = ( $row['jdate'] <= $row['jc_retest_date'] )?'Yes':'No';
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit; 
            
        }else{

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            LIMIT {$offset}, {$per_page}
            ");
            $data['sql_query'] = $this->db->last_query();

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(j.`id`) AS jcount
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            ");
            $total_rows = $total_rows_sql->row()->jcount;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.agency_id), a.`agency_name`
            {$main_query} 
            ORDER BY a.`agency_name` ASC           
            ");

            // pagination
            $pagi_links_params_arr = array(            
                'from_date_filter' => $this->input->get_post('from_date_filter'),
                'to_date_filter' => $this->input->get_post('to_date_filter'),
                'agency_filter' => $agency_filter
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('/reports/compliance_report_nsw_act', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }

    public function compliance_report_sa() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Compliance Report (SA)";
        $uri = '/reports/compliance_report_sa';
        $data['uri'] = $uri;

        $from_date_filter = ( $this->input->get_post('from_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('from_date_filter')):null;
        $to_date_filter = ( $this->input->get_post('to_date_filter') != "" )?$this->system_model->formatDate($this->input->get_post('to_date_filter')):null;
        $agency_filter = $this->input->get_post('agency_filter');        

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        $date_filter_sql_str = null;
        if( $from_date_filter != '' && $to_date_filter ){
            $date_filter_sql_str = " AND j.`date` BETWEEN '{$from_date_filter}' AND '{$to_date_filter}' ";
        }

        // select query
        $sel_query = "
        SELECT 
        jc.`retest_date` AS jc_retest_date,

        j.`id` AS jid, 
        j.`door_knock`,
        j.`created` AS jcreated,
        j.job_price,
        j.job_type,
        j.`date` AS jdate,
        j.`due_date`,
        
        p.`property_id`,
        p.`address_1`, 
        p.`address_2`, 
        p.`address_3`,

        a.agency_id,
        a.`agency_name`,
        aght.priority
        ";
        
        // main query body
        $main_query = "
        FROM `job_compliance` AS jc
        LEFT JOIN `jobs` AS j ON jc.`job_id` = j.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        WHERE j.`status` = 'Completed'
        AND p.`state` IN ('SA')
        AND j.`job_type` = 'Yearly Maintenance'
        AND p.`deleted` = 0
        AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
        AND j.`del_job` = 0
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND jc.`retest_date` != ''
        ";        

        if ($export == 1) { //EXPORT         
            
            // file name
            $date_export = date('YmdHis');
            $filename = "compliance_report_sa_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = [];
            $header[] = "Date";
            $header[] = "End Date";
            $header[] = 'Created Date';
            $header[] = 'Age';
            $header[] = 'Property';
            $header[] = 'Agency';
            $header[] = 'Job Type';
            $header[] = 'Compliant';

            fputcsv($csv_file, $header);

            // main listing
            $main_list = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            ");
            
            foreach ( $main_list->result_array() as $row ){ 

                $csv_row = [];                              

                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jdate']) )?date("d/m/Y",strtotime($row['jdate'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['due_date']) )?date("d/m/Y",strtotime($row['due_date'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['jcreated']) )?date("d/m/Y",strtotime($row['jcreated'])):null;
                $csv_row[] = $this->gherxlib->getAge($row['jcreated']);
                $csv_row[] = "{$row['address_1']} {$row['address_2']}, {$row['address_3']}";
                $csv_row[] = $row['agency_name'];
                $csv_row[] = $row['job_type'];
                $csv_row[] = ( $row['jdate'] <= $row['jc_retest_date'] )?'Yes':'No';
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit; 
            
        }else{

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            LIMIT {$offset}, {$per_page}
            ");
            $data['sql_query'] = $this->db->last_query();

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(j.`id`) AS jcount
            {$main_query}
            {$agency_filter_sql_str}
            {$date_filter_sql_str}
            ");
            $total_rows = $total_rows_sql->row()->jcount;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.agency_id), a.`agency_name`
            {$main_query} 
            ORDER BY a.`agency_name` ASC           
            ");

            // pagination
            $pagi_links_params_arr = array(            
                'from_date_filter' => $this->input->get_post('from_date_filter'),
                'to_date_filter' => $this->input->get_post('to_date_filter'),
                'agency_filter' => $agency_filter
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('/reports/compliance_report_nsw_act', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }


    public function property_variation() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Property Variation";
        $uri = '/reports/property_variation';
        $data['uri'] = $uri;
        
        $type_filter = ( $this->input->get_post('type_filter') != '' )?$this->input->get_post('type_filter'):1;  
        $agency_filter = $this->input->get_post('agency_filter'); 
        $apvr_filter = $this->input->get_post('apvr_filter');     

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        $type_filter_sql_str = null;
        if( $type_filter > 0  ){
            $type_filter_sql_str = " AND apv.`type` = {$type_filter} ";
        }

        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        $apvr_filter_sql_str = null;
        if( $apvr_filter > 0  ){
            $apvr_filter_sql_str = " AND apvr.`id` = {$apvr_filter} ";
        }

        // select query
        $sel_query = "
        SELECT       
        apv.`type`,
        apv.`amount`,

        apvr.`reason` AS apvr_reason,
        
        p.`property_id`,
        p.`address_1`, 
        p.`address_2`, 
        p.`address_3`,
        p.landlord_firstname, 
        p.landlord_lastname, 

        a.agency_id,
        a.`agency_name`,
        aght.priority,

        sr.`StaffID` AS sr_staff_id,
        sr.`FirstName` AS sr_fname,
        sr.`LastName` AS sr_lname        
        ";
        
        // main query body
        $main_query = "
        FROM `property_variation` AS pv
        LEFT JOIN `agency_price_variation` AS apv ON pv.`agency_price_variation` = apv.`id`
        LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
        LEFT JOIN `property` AS p ON pv.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        LEFT JOIN `staff_accounts` AS sr ON a.`salesrep` = sr.`StaffID`
        WHERE pv.`active` = 1
        AND p.`deleted` = 0
        AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND apv.`active` = 1
        ";        

        if ($export == 1) { //EXPORT         
            
            
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
            
            
        }else{

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$type_filter_sql_str}
            {$apvr_filter_sql_str}
            LIMIT {$offset}, {$per_page}
            ");
            $data['sql_query'] = $this->db->last_query();

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(pv.`id`) AS pv_count
            {$main_query}
            {$agency_filter_sql_str}
            {$type_filter_sql_str}
            {$apvr_filter_sql_str}
            ");
            $total_rows = $total_rows_sql->row()->pv_count;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.agency_id), a.`agency_name`
            {$main_query} 
            {$type_filter_sql_str}
            {$apvr_filter_sql_str}
            ORDER BY a.`agency_name` ASC           
            ");

            // reason filter
            $data['apvr_filter_sql'] = $this->db->query("
            SELECT *
            FROM `agency_price_variation_reason`
            WHERE `active` = 1										
            ORDER BY `reason` ASC        
            ");

            // pagination
            $pagi_links_params_arr = array(            
                'type_filter' => $type_filter,
                'agency_filter' => $agency_filter,
                'apvr_filter' => $apvr_filter
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }


    public function high_touch_agencies() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "High Touch Agencies";
        $uri = '/reports/high_touch_agencies';
        $data['uri'] = $uri;
        
        $agency_filter = $this->input->get_post('agency_filter'); 
        $staff_filter = $this->input->get_post('staff_filter');     
        $high_touch_filter = $this->input->get_post('high_touch_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        // header filters
        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        $staff_filter_sql_str = null;
        if( $staff_filter > 0  ){
            $staff_filter_sql_str = " AND staff.`StaffID` = {$staff_filter} ";
        }  
        
        $high_touch_filter_sql_str = null;
        if( is_numeric($high_touch_filter)  ){
            $high_touch_filter_sql_str = " AND aght.`priority` = {$high_touch_filter} ";
        } 

        // select query
        $sel_query = "
        SELECT      
        l.`details`,
        l.`created_date`,

        a.`agency_id`,
        a.`agency_name`,
        aght.`priority`,

        staff.`StaffID` AS staff_staff_id,
        staff.`FirstName` AS staff_fname,
        staff.`LastName` AS staff_lname        
        ";
        
        // main query body
        $main_query = "
        FROM `logs` AS l        
        LEFT JOIN `agency` AS a ON l.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        LEFT JOIN `staff_accounts` AS staff ON l.`created_by_staff` = staff.`StaffID`
        WHERE l.`deleted` = 0
        AND l.`title` = 46
        AND l.`details` LIKE '% as Agency Priority'
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

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$staff_filter_sql_str}
            {$high_touch_filter_sql_str}
            ORDER BY l.`created_date` DESC
            LIMIT {$offset}, {$per_page}                
            ");
            $data['sql_query'] = $this->db->last_query();

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(l.`log_id`) AS l_count
            {$main_query}
            {$agency_filter_sql_str}
            {$staff_filter_sql_str}
            {$high_touch_filter_sql_str}
            ");
            $total_rows = $total_rows_sql->row()->l_count;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.agency_id), a.`agency_name`
            {$main_query} 
            {$staff_filter_sql_str}
            {$high_touch_filter_sql_str}
            ORDER BY a.`agency_name` ASC           
            ");

            // staff filter
            $data['staff_filter'] = $this->db->query("
            SELECT DISTINCT(staff.`StaffID`), staff.`FirstName`, staff.`LastName`
            {$main_query} 
            {$agency_filter_sql_str}
            {$high_touch_filter_sql_str}
            ORDER BY staff.`FirstName` ASC, staff.`LastName` ASC           
            ");

            // pagination
            $pagi_links_params_arr = array(            
                'agency_filter' => $agency_filter,
                'staff_filter' => $staff_filter
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }


    public function agency_api_logs() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency API Logs";
        $uri = '/reports/agency_api_logs';
        $data['uri'] = $uri;
        
        $agency_filter = $this->input->get_post('agency_filter');
        $api_response_filter = $this->input->get_post('api_response_filter');      

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        // header filters
        $agency_filter_sql_str = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
        }

        $api_response_filter_sql_str = null;
        if( $api_response_filter != '' ){
            $api_response_filter_sql_str = " AND aal.`api_response` = '{$api_response_filter}' ";
        }

        // select query
        $sel_query = "
        SELECT      
            aal.`api_url`,
            aal.`api_response`,
            aal.`date_created`,
            
            j.`id` AS jid,
            j.`job_type`,
            j.`status` AS jstatus,
            
            p.`property_id`,
            p.`address_1` AS p_address_1,
            p.`address_2` AS p_address_2,
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            
            a.`agency_id`,
            a.`agency_name`     
        ";
        
        // main query body
        $main_query = "
        FROM `agency_api_logs` AS aal 
        LEFT JOIN `jobs` AS j ON aal.`job_id` = j.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE aal.`api_url` IN('v1/lots/documents','v1/bills')
        AND aal.`api_response` NOT IN(200,204)
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

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$api_response_filter_sql_str}
            ORDER BY aal.`date_created` DESC
            LIMIT {$offset}, {$per_page}                
            ");
            $data['sql_query'] = $this->db->last_query();

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(aal.`id`) AS aal_count
            {$main_query}
            {$agency_filter_sql_str}
            {$api_response_filter_sql_str}
            ");
            $total_rows = $total_rows_sql->row()->aal_count;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.agency_id), a.`agency_name`
            {$main_query} 
            {$api_response_filter_sql_str}
            ORDER BY a.`agency_name` ASC           
            ");

            // API status response filter
            $data['api_response_filter'] = $this->db->query("
            SELECT DISTINCT(aal.`api_response`)
            {$main_query}         
            ORDER BY a.`agency_name` ASC           
            ");


            // pagination
            $pagi_links_params_arr = array(            
                'agency_filter' => $agency_filter,
                'api_response_filter' => $api_response_filter
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }


    public function subscription_date_report() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Subscription Date Report";
        $uri = '/reports/subscription_date_report';
        $data['uri'] = $uri;
        
        $agency_filter = $this->input->get_post('agency_filter');
        $api_response_filter = $this->input->get_post('api_response_filter');      

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

            prop_subs.`subscription_date`,
            prop_subs.`source`,
            
            a.`agency_id`,
            a.`agency_name`     
        ";
        
        // main query body
        $main_query = "
        FROM `property` AS p 
        LEFT JOIN `property_subscription` AS prop_subs ON p.`property_id` = prop_subs.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND a.`deleted` = 0
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

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}         
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
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.agency_id), a.`agency_name`
            {$main_query} 
            ORDER BY a.`agency_name` ASC           
            ");

            // get subscription source
            $data['subs_source_sql'] = $this->db->query("
            SELECT *
            FROM `subscription_source`		
            ");

            // pagination
            $pagi_links_params_arr = array(            
                'agency_filter' => $agency_filter,
                'api_response_filter' => $api_response_filter
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }

    public function property_subscription_save(){

        $property_id = $this->input->post('property_id');
        $subscription_date = ( $this->input->post('subscription_date') != '' )?$this->system_model->formatDate($this->input->post('subscription_date')):null;
        $subscription_source = $this->input->post('subscription_source');
        $today = date('Y-m-d');

        // subscription date insert/update
        $prop_subs_sql = $this->db->query("
        SELECT COUNT(`id`) AS prop_subs_count
        FROM `property_subscription`
        WHERE `property_id` = {$property_id}
        ");
        $prop_subs_row = $prop_subs_sql->row();

        if( $prop_subs_row->prop_subs_count > 0 ){ // exist, update

            $update_data = array(
                'subscription_date' => $subscription_date,
                'source' => $subscription_source,
                'date_updated' => $today
            );
            
            $this->db->where('property_id', $property_id);
            $this->db->update('property_subscription', $update_data);

        }else{ // new, insert

            $insert_data = array(
                'property_id' => $property_id,
                'subscription_date' => $subscription_date,
                'source' => $subscription_source,
                'date_updated' => $today
            );
            
            $this->db->insert('property_subscription', $insert_data);

        }

    }


    public function properties_missing_variation() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Properties Missing Variation";
        $uri = '/reports/properties_missing_variation';
        $data['uri'] = $uri;
        
        $agency_filter = $this->input->get_post('agency_filter');
        $service_type_filter = $this->input->get_post('service_type_filter');   
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

        $service_type_filter_str = null;
        if( $service_type_filter > 0  ){
            $service_type_filter_str = " AND ajt.`id` = {$service_type_filter} ";
        }

        // select query
        $sel_query = "
        SELECT      
            ajt.`type` AS ajt_type,
        
            ps.`price` AS ps_price,
        
            p.`property_id`,
            p.`address_1` AS p_address_1,
            p.`address_2` AS p_address_2,
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
        
            agen_serv.`price` AS as_price,
            
            a.`agency_id`,
            a.`agency_name`,
            
            aht.`priority`,
            apmd.`abbreviation`
        ";
        
        // main query body
        $main_query = "
        FROM `property_services` AS ps
        LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
        LEFT JOIN `property_variation` AS pv ON p.`property_id` = pv.`property_id`
        LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
        LEFT JOIN `agency_services` AS agen_serv ON ( ps.`alarm_job_type_id` = agen_serv.`service_id` AND p.`agency_id` = agen_serv.`agency_id` )
        LEFT JOIN `agency` AS a ON agen_serv.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` AS aht ON a.`agency_id` = aht.`agency_id`
        LEFT JOIN `agency_priority_marker_definition` AS apmd ON aht.`priority` = apmd.`priority`
        WHERE p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND ps.`price` != agen_serv.`price`
        AND ps.`service` = 1
        AND pv.`id` IS NULL
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

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}    
            {$service_type_filter_str}  
            ORDER BY a.`agency_name`   
            LIMIT {$offset}, {$per_page}                
            ");
            $data['sql_query'] = $this->db->last_query();

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(p.`property_id`) AS p_count
            {$main_query}
            {$agency_filter_sql_str}
            {$service_type_filter_str}
            ");
            $total_rows = $total_rows_sql->row()->p_count;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT 
                a.`agency_id`, 
                a.`agency_name`, 
                aht.`priority`,
                apmd.`abbreviation`
            FROM agency AS a
            LEFT JOIN `agency_priority` AS aht ON a.`agency_id` = aht.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` AS apmd ON aht.`priority` = apmd.`priority`
            WHERE a.`status` = 'active'
            AND a.`deleted` = 0
            ORDER BY a.`agency_name` ASC           
            ");

            // service type filter
            $data['service_type_filter_sql'] = $this->db->query("
            SELECT 
                `id`, 
                `type`
            FROM `alarm_job_type`
            WHERE `active` = 1
            ORDER BY `type` ASC           
            ");


            // pagination
            $pagi_links_params_arr = array(            
                'agency_filter' => $agency_filter,
                'service_type_filter' => $service_type_filter,
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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }


    public function properties_from_other_company() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Properties from other companies";
        $uri = '/reports/properties_from_other_company';
        $data['uri'] = $uri;
        
        $agency_filter = $this->input->get_post('agency_filter');
        $ps_filter = $this->input->get_post('ps_filter');   
        $btn_search = $this->input->get_post('btn_search');
        $sort_multiple = $this->input->get_post('sort_multiple');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;

        $export = $this->input->get_post('export');

        // header filters
        $agency_filter_sql_str = null;
        $agency_filter_sql_str_inner = null;
        if( $agency_filter > 0  ){
            $agency_filter_sql_str = " AND a.`agency_id` = {$agency_filter} ";
            $agency_filter_sql_str_inner = " AND a_inner.`agency_id` = {$agency_filter} ";
        }

        $ps_filter_str = null;
        $ps_filter_str_inner = null;
        if( $ps_filter > 0  ){

            if( $ps_filter == 1 ){ // serviced to SATS
                $ps_filter_str = " AND ps.`service` = 1 ";
                $ps_filter_str_inner = " AND ps_inner.`service` = 1 ";
            }else{ // not serviced to SATS
                $ps_filter_str = " AND ps.`service` != 1 ";
                $ps_filter_str_inner = " AND ps_inner.`service` != 1 ";
            }
            
        }

        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'a.agency_name';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        // sort
        $sort_filter_str = null;
        if( $sort_multiple == 1 ){
         
            if( $order_by != '' && $sort != '' ){

                $order_by_arr = explode(",",$order_by);
                
                // loop through comma separated, order by
                $order_by_sort_arr = [];
                foreach( $order_by_arr as $order_by ){
                    $order_by_sort_arr[] = " {$order_by} ".strtoupper($sort);
                }

                if( count($order_by_sort_arr) > 0 ){

                    $order_by_sort_imp = implode(", ",$order_by_sort_arr);
                    $sort_filter_str = "ORDER BY {$order_by_sort_imp}";

                }
                

            }

        }else{ // default

            // sort
            $sort_filter_str = null;
            if( $order_by != '' && $sort != '' ){
                $sort_filter_str = "ORDER BY {$order_by} {$sort}";
            }

        }
        

        // select query
        $sel_query = "
        SELECT    
            pfoc.`added_date` pfoc_added_date,
             
            p.`property_id`,
            p.`address_1` AS p_address_1,
            p.`address_2` AS p_address_2,
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`is_nlm`,
            p.`nlm_timestamp`,

            ps.`service` AS ps_service,
            ps.`alarm_job_type_id` AS service_type,
        
            a.`agency_id`,
            a.`agency_name`,

            MAX(ps.`status_changed`)
        ";
        
        // main query body
        $main_query = "
        FROM `property_services` AS ps
        INNER JOIN(

            SELECT p_inner.property_id, MAX(ps_inner.status_changed) AS latest_status_changed
            FROM `property_services` AS ps_inner
            INNER JOIN `property` AS p_inner ON ps_inner.`property_id` = p_inner.`property_id`
            INNER JOIN `properties_from_other_company` AS pfoc_inner ON ( p_inner.`property_id` = pfoc_inner.`property_id` ) 
            LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id`
            INNER JOIN `agencies_from_other_company` AS afoc_inner ON ( a_inner.`agency_id` = afoc_inner.`agency_id` ) 
            WHERE pfoc_inner.`active` = 1
            AND afoc_inner.`active` = 1
            {$agency_filter_sql_str_inner}
            {$ps_filter_str_inner}
            GROUP BY p_inner.property_id DESC

        ) AS ps_inner_query ON ( ps.`property_id` = ps_inner_query.`property_id` AND ps.`status_changed` = ps_inner_query.latest_status_changed )
        INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id`        
        INNER JOIN `properties_from_other_company` AS pfoc ON ( p.`property_id` = pfoc.`property_id` ) 
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        INNER JOIN `agencies_from_other_company` AS afoc ON ( a.`agency_id` = afoc.`agency_id` ) 
        WHERE pfoc.`active` = 1
        AND afoc.`active` = 1
        ";        

        if( $export == 1 ) { // export         
            
            // file name
            $date_export = date('YmdHis');
            $filename = "properties_from_other_company_{$date_export}.csv";

            // csv headers
            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = array('Property address','Agency','Property status','Date of creation','Date of deactivation (If Applicable)');

            // insert csv header
            fputcsv($csv_file, $header);

      
            // main listing
            $main_list = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}
            {$ps_filter_str}
            GROUP BY p.`property_id`
            {$sort_filter_str}
            ");
            
            foreach ( $main_list->result() as $row ){ 

                $csv_row = [];    
                
                $csv_row[] = "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3}"; // property address
                $csv_row[] = $row->agency_name; // agency 

                // Property status
                // get property service
                $ps_sql = $this->db->query("
                SELECT ps.`service`
                FROM `property_services` AS ps
                INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id`
                WHERE p.`property_id` = {$row->property_id}
                AND p.`deleted` = 0
                AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                ");
                $ps_row = $ps_sql->row();
                $csv_row[] = ( $ps_row->service == 1 )?'Active':'Not Active';

                // Date of creation
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row->pfoc_added_date) )?date('d/m/Y',strtotime($row->pfoc_added_date)):null;                            
                
                // Date of deactivation
                $deact_date = null;
                if( $row->is_nlm == 1 && $this->system_model->isDateNotEmpty($row->nlm_timestamp) ){
                    $deact_date = date('d/m/Y',strtotime($row->nlm_timestamp));
                }else{

                     // get property service
                    $ps_sql = $this->db->query("
                    SELECT 
                        ps.`service`,
                        ps.`status_changed`
                    FROM `property_services` AS ps
                    INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id`
                    WHERE p.`property_id` = {$row->property_id}                
                    ");

                    $ps_service_count = $ps_sql->num_rows(); // all property service count

                    $not_sats_count = 0;
                    $last_status_changed_date = null;
                    foreach( $ps_sql->result() as $ps_row ){

                        if( $ps_row->service != 1 ){ // not service to SATS
                            $not_sats_count++;
                            $last_status_changed_date = $ps_row->status_changed;
                        }

                    }

                    if( $ps_service_count == $not_sats_count ){ // all service is not serviced to SATS
                        $deact_date = ( $this->system_model->isDateNotEmpty($last_status_changed_date) )?date('d/m/Y',strtotime($last_status_changed_date)):null;
                    }

                }

                $csv_row[] = $deact_date;

                // insert csv row
                fputcsv($csv_file,$csv_row);  

            }
        
        
            fclose($csv_file); 
            exit; 
            
        }else{ // page

            // main listing
            $data['lists'] = $this->db->query("
            {$sel_query}
            {$main_query}
            {$agency_filter_sql_str}    
            {$ps_filter_str}
            GROUP BY p.`property_id`
            {$sort_filter_str} 
            LIMIT {$offset}, {$per_page}                
            ");
            $data['sql_query'] = $this->db->last_query();

            // total rows            
            $total_rows_sql = $this->db->query("
            SELECT COUNT(p.`property_id`) AS p_count
            {$main_query}
            {$agency_filter_sql_str}
            {$ps_filter_str}
            ");
            $total_rows = $total_rows_sql->row()->p_count;   
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
            SELECT DISTINCT(a.`agency_id`), a.`agency_name`
            {$main_query} 
            ORDER BY a.`agency_name` ASC           
            ");

            // pagination
            $pagi_links_params_arr = array(            
                'agency_filter' => $agency_filter,
                'ps_filter' => $ps_filter,
                'order_by' => $order_by,
                'sort' => $sort,
                'sort_multiple' => $sort_multiple
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

            // sort
            $data['order_by'] = $order_by;
            $data['sort'] = $sort;

            $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';	

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
            
        }                        

    }


}