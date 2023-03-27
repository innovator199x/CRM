<?php

class Calendar extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model('jobs_model');
        $this->load->model('staff_accounts_model');
        $this->load->model('calendar_model');
        $this->load->model('tech_model');
        $this->load->library('pagination');
    }
    
    public function index(){

    }


    /**
     * Calendar page
     * Any changes to this stuff must also applied to tech version (view_individual_staff_calendar_tech)
     */
    public function my_calendar_admin(){
       
        $data['start_load_time'] = microtime(true);

        $taff_params = array(
            'sel_query' => "sa.FirstName, sa.LastName",
            'staff_id' => $this->session->staff_id
        );
        $staff_name_query = $this->gherxlib->getStaffInfo($taff_params)->row_array();
        $staff_name = "{$staff_name_query['FirstName']}";

        $data['title'] = "{$staff_name}'s Calendar";


        $this->load->view('templates/inner_header', $data);
        $this->load->view('calendar/view_individual_staff_calendar', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    /**
     * Tech Calendar (ajax request)
     * Json
     */
    public function json_tech_calendar(){
     
        $params = array(
            'sel_query' => "c.calendar_id, c.staff_id, c.region, c.date_start, c.date_finish, c.date_start_time, c.date_finish_time, c.booking_target, c.details, c.accomodation,c.marked_as_leave, s.FirstName, s.LastName, s.ClassID, acco.accomodation_id, acco.name as acco_name, acco.area as acco_area, acco.address as acco_address, acco.phone as acco_phone",
            'StaffID' => $this->session->staff_id,
            'sort_list' => array(
                array(
                    'order_by' => 'c.date_start',
                    'sort' => 'DESC'
                )
            )
            
        );
        $cal_query = $this->calendar_model->get_tech_calendar($params);



        if(!empty($cal_query)){
			foreach($cal_query->result() as $row){
				
                $color =  ($row->marked_as_leave==1)?'event-red':'event-blue';
                
                if($row->accomodation=='0'){ // Required
                    $icon = "home icon_required";
                }else if($row->accomodation == 2){ //Pending
                    $icon = "home icon_pending";
                }else if($row->accomodation == 1){ // Booked
                    $icon = "home icon_booked";
                }else if($row->accomodation===NULL){
                    $icon = "";
                }

				
				$data[] = array(
                    'id' => $row->calendar_id,
                    'staff_id' => $row->staff_id,
                    'start' => $row->date_start,
                    'end' => $row->date_finish."T23:59:00",
                    'details' => $row->details,
					'title' => $row->region,
                    'className' => $color,
                    'address' => $row->acco_address,
                    'ClassID' => $row->ClassID,
                    'accomodation' => $row->accomodation,
                    'accomodation_name' => $row->acco_name,
                    'acco_phone' => $row->acco_phone,
                    'cal_url' => "/calendar/add_calendar_entry_static?id={$row->calendar_id}",
                    'icon' => $icon,
                    'start_time' =>  $row->date_start_time,
                    'end_time' => $row->date_finish_time
				);
			}
		}
		
        echo json_encode($data);


    }


    /**
     * View tech schedule page
     * Any changes to this stuff must also applied to tech version (view_tech_schedule_tech)
     */
    public function monthly_schedule_admin(){

        if(!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))){
            redirect('/home/index_tech','refresh');
        }

        $data['start_load_time'] = microtime(true);

        $tech_id = $this->uri->segment(3);

        $taff_params = array(
            'sel_query' => "sa.FirstName, sa.LastName",
            'staff_id' => $tech_id
        );
        $staff_name_query = $this->gherxlib->getStaffInfo($taff_params)->row_array();
        $staff_name = "{$staff_name_query['FirstName']}";
        $data['title'] = trim($staff_name)."'s Schedule";


        $data['day'] = date("d");
        $data['month'] = date("m");
        $data['year'] = date("y");


        $data['tech_id'] = $this->uri->segment(3);
       
        $usemonth = ($this->input->get_post('month')!="")?$this->input->get_post('month'):date('m');
        $data['usemonth'] = ($this->input->get_post('month')!="")?$this->input->get_post('month'):date('m');
        $useyear = ($this->input->get_post('year')!="")?$this->input->get_post('year'):date('Y');
        $data['useyear'] = ($this->input->get_post('year')!="")?$this->input->get_post('year'):date('Y');

        
        if($usemonth && $useyear){
            $data['current_month'] = date('F Y', strtotime("{$useyear}-{$usemonth}-01"));
        }else{
            $data['current_month'] = date('F Y');
        }

        // do the stuff for nextyear.
        if ($usemonth == 12)
        {
            $data['nextmonth'] = 1;
            $data['nextyear'] = $useyear+1;
        }else
        {
            $data['nextmonth'] = $usemonth+1;
            $data['nextyear'] = $useyear;
        }

        // do the stuff for prevyear.
        if ($usemonth == 1)
            {
            $data['prevmonth'] = 12;
            $data['prevyear'] = $useyear-1;
            }
        else
            {
            $data['prevmonth'] = $usemonth-1;
            $data['prevyear'] = $useyear;

        }

        //get days in month
        //$data['days_in_month'] = cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
        $data['days_in_month'] = cal_days_in_month(CAL_GREGORIAN,$usemonth,$useyear);

        //get staff classID
        $params = array(
            'sel_query' => 'sa.ClassID',
            'staff_id' => $this->session->staff_id
        );
        $data['staff'] = $this->gherxlib->getStaffInfo($params)->row()->ClassID;

        // get all services for legend
        $data['serv_type_sql'] = $this->db->query("
            SELECT `id`, `type`
            FROM `alarm_job_type`
            WHERE `active` = 1
        ");

       

        $this->load->view('templates/inner_header', $data);
        $this->load->view('calendar/view_tech_schedule', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function add_calendar_entry_static(){

        $data['cal_id'] = $this->input->get_post('id'); //calendar id
        $cal_staff_id = $this->input->get_post('staff_id');
        $cal_startdate = $this->input->get_post('startdate');
        $data['add'] = $this->input->get_post('add');

        if( (isset( $data['cal_id']) && !empty($data['cal_id'])) && !$cal_staff_id ){ //has/set call_id but no staff_id > edit individual calendar entry

            //GET CALENDAR ITEM BY ID
            $params = array(
                'sel_query' => "c.calendar_id, c.staff_id, c.region, c.date_start, c.date_start_time, c.date_finish_time, c.booking_staff, c.date_finish, c.booking_target, c.details, c.accomodation,c.marked_as_leave, s.FirstName, s.LastName, s.ClassID, acco.accomodation_id, acco.name as acco_name, acco.area as acco_area, acco.address as acco_address, acco.phone as acco_phone",
                'StaffID'=> $this->session->staff_id,
                'calendar_id' => $data['cal_id']
            );
            $data['row_cal'] = $this->calendar_model->get_tech_calendar($params)->row_array();

            $start_d = $data['row_cal']['date_start'];
            $finish_d = $data['row_cal']['date_finish'];
            $start_time = $data['row_cal']['date_start_time'];
            $finish_time = $data['row_cal']['date_finish_time'];

        }else if( $data['cal_id'] && $cal_staff_id ){ // cal id and staff id is set > edit popup from view_tech_calendar page

             //GET CALENDAR ITEM BY ID
             $params = array(
                'sel_query' => "c.calendar_id, c.staff_id, c.region, c.date_start, c.date_start_time, c.date_finish_time, c.booking_staff, c.date_finish, c.booking_target, c.details, c.accomodation,c.marked_as_leave, s.FirstName, s.LastName, s.ClassID, acco.accomodation_id, acco.name as acco_name, acco.area as acco_area, acco.address as acco_address, acco.phone as acco_phone",
                'StaffID'=> $cal_staff_id,
                'calendar_id' => $data['cal_id']
            );
            $data['row_cal'] = $this->calendar_model->get_tech_calendar($params)->row_array();

            $start_d = $data['row_cal']['date_start'];
            $finish_d = $data['row_cal']['date_finish'];
            $start_time = $data['row_cal']['date_start_time'];
            $finish_time = $data['row_cal']['date_finish_time'];


        }else if($cal_staff_id && !empty($cal_staff_id)){ //has staff_id > add new calendar entry with staff_id is set

            $data['row_cal']['staff_id'] = $cal_staff_id;

            $start_d = $cal_startdate;
            $finish_d = $cal_startdate;
            $start_time = "09:00";
            $finish_time = "17:00";

        }else if(!$data['cal_id'] && !$cal_staff_id ){ // callendar id and staff id url param is not set so use staff id instead
            
            $data['row_cal']['staff_id'] = $this->session->staff_id;

        }

        
            $data['start_date_data'] = ($start_d!="")?date('d/m/Y H:i', strtotime(str_replace("/","-",$start_d." ".$start_time))):date('d/m/Y 09:00');
            $data['finish_date_data'] = ($finish_d!="")?date('d/m/Y H:i', strtotime(str_replace("/","-",$finish_d." ".$finish_time))):date('d/m/Y 17:00');
            


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

        

        $this->load->view('calendar/add_calendar_entry_static', $data);
    
    }


    
    public function add_new_entry(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Add New Calendar Entry";


        //get staff by staff_id
        $staff_params = array(
            'sel_query' => 'sa.ClassID, sa.StaffID, sa.FirstName, sa.LastName',
            'staff_id' => $this->session->staff_id
        );
        $staff = $this->gherxlib->getStaffInfo($staff_params)->row_array();
        $data['staff'] = $this->gherxlib->getStaffInfo($staff_params)->row_array(); //pass


        //redirect if tech
        if($staff['ClassID']==6){
            redirect('/home/index_tech','refresh');
        }


        $this->load->view('templates/inner_header', $data);
        $this->load->view('calendar/add_new_entry', $data);
        $this->load->view('templates/inner_footer', $data);
        

    }


    public function add_calendar_entry_static_process_ajax(){

        $json_data['status'] = false;
      
        $type = $this->input->post('type');
        $cal_id = $this->input->post('cal_id');
        $staff_id = $this->input->post('staff_id');
        $start_date = date('Y-m-d', strtotime(str_replace('/','-', $this->input->post('start_date'))));
        $finish_date = date('Y-m-d', strtotime(str_replace('/','-', $this->input->post('finish_date'))));
        $leave_type = $this->input->post('leave_type');
        $marked_as_leave = ($this->input->post('marked_as_leave')=='true')?1:0;
        $booking_staff = ($this->input->post('booking_staff')!="")?$this->input->post('booking_staff'):NULL;
        $details = $this->input->post('details');
        $accomodation = ($this->input->post('accomodation')!="")?$this->input->post('accomodation'):NULL;
        $accomodation_id = ($accomodation==1 || $accomodation ==2)?$this->input->post('accomodation_id'):'';
        $send_ical = ($this->input->post('send_ical')=='true')?1:0;
        

        $time_start = date('H:i', strtotime(str_replace('/','-', $this->input->post('start_date'))));
        $time_finish = date('H:i', strtotime(str_replace('/','-', $this->input->post('finish_date'))));

        if($cal_id && !empty($cal_id) && $type=='update'){ //DO UPDATE PROCESS HERE....

            $update_data = array(
                'staff_id' => $staff_id,
                'date_start' => $start_date,
                'date_finish' => $finish_date,
                'date_start_time' => $time_start,
                'date_finish_time' => $time_finish,
                'region' => $leave_type,
                'booking_staff' => $booking_staff,
                'marked_as_leave' => $marked_as_leave,
                'details' => $details,
                'accomodation' => $accomodation,
                'accomodation_id' => $accomodation_id
            );

            $this->db->where('calendar_id', $cal_id );
            $this->db->update('calendar', $update_data);
            $this->db->limit(1);

            if($this->db->affected_rows() >= 0){
                $json_data['status'] = true;
            }
            

        }
        


        if(!$cal_id && $type=='add'){ //ADD EVENT HERE... AND SEND ICAL IF CHECKED

            if(is_array($staff_id)){

                foreach($staff_id as $staff){

                    $add_data = array(
                        'staff_id' => $staff,
                        'date_start' =>  $start_date,
                        'date_finish' => $finish_date,
                        'region' => $leave_type,
                        'marked_as_leave' => $marked_as_leave,
                        'booking_staff' => $booking_staff,
                        'details' => $details,
                        'accomodation' => $accomodation,
                        'accomodation_id' => $accomodation_id,
                        'country_id' => $this->config->item('country'),
                        'date_start_time' => $time_start,
                        'date_finish_time' => $time_finish
                    );
                    $this->db->insert('calendar', $add_data);
                    $this->db->limit(1);
        
                    $insert_id = $this->db->insert_id(); //last insert id
        
                    if($this->db->affected_rows()>0){
        
                        if($send_ical==1){ //if send ical is checed > send ical
        
                            $cal_start_date = date('Y-m-d H:i:s', strtotime(str_replace('/','-', $this->input->post('start_date'))));
                            $cal_finish_date = date('Y-m-d H:i:s', strtotime(str_replace('/','-', $this->input->post('finish_date'))));
        
                            $sa_sql_params = array(
                                'sel_query' => "sa.FirstName, sa.LastName, sa.Email",
                                'staff_id' => $staff
                            );
                            $sa_sql = $this->gherxlib->getStaffInfo($sa_sql_params);
                            $sa = $sa_sql->row_array();
                            $subject = 'iCalendar';
                            $from_email = $this->config->item('sats_info_email');
                            $to_name = "{$sa['FirstName']} {$sa['LastName']}";
                            $to_email = $sa['Email'];
                            //$to_email = 'itsmegherx@gmail.com';
        
                            //get tech calendar info
                            $cal_query_params = array(
                                'sel_query' => "c.calendar_id, c.staff_id, c.region, c.date_start, c.date_finish, c.booking_target, c.details, c.accomodation,c.marked_as_leave, s.FirstName, s.LastName, s.ClassID, acco.accomodation_id, acco.name as acco_name, acco.area as acco_area, acco.address as acco_address, acco.phone as acco_phone",
                                'calendar_id' => $insert_id,
                                'StaffID' => $this->session->staff_id
                            );
                            $cal_query = $this->calendar_model->get_tech_calendar($cal_query_params)->row_array();
                            if($cal_query['accomodation']===NULL || $cal_query['accomodation']=='0'){
                                $email_details = "{$cal_query['details']}";
                            }else{
                                $email_details = "{$cal_query['acco_name']} | {$cal_query['acco_address']} | {$cal_query['acco_phone']} | {$cal_query['details']}";
                            }
                            //get tech calendar info end
                            
                            $this->calendar_model->send_ical_to_mail($subject,$from_email, $to_name, $to_email, $leave_type, $email_details, $cal_start_date, $cal_finish_date);
                        
                        }
        
                       
                    }

                }

                

            }

            $json_data['status'] = true;

        }


        echo json_encode($json_data);


    }


    /**
     * For Tech
     */
    public function monthly_schedule(){

        if(!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))){
            redirect('/home/index_tech','refresh');
        }

        //$data['start_load_time'] = microtime(true);

        $uri = '/calendar/monthly_schedule';

        $tech_id = $this->uri->segment(3);
        $staff_class_id = $this->system_model->getStaffClassID();

        $taff_params = array(
            'sel_query' => "sa.FirstName, sa.LastName",
            'staff_id' => $tech_id
        );
        $staff_name_query = $this->gherxlib->getStaffInfo($taff_params)->row_array();
        $staff_name = "{$staff_name_query['FirstName']}";
        $data['title'] = trim($staff_name)."'s Schedule";


        $data['day'] = date("d");
        $data['month'] = date("m");
        $data['year'] = date("y");


        $data['tech_id'] = $this->uri->segment(3);
       
        $usemonth = ($this->input->get_post('month')!="")?$this->input->get_post('month'):date('m');
        $data['usemonth'] = ($this->input->get_post('month')!="")?$this->input->get_post('month'):date('m');
        $useyear = ($this->input->get_post('year')!="")?$this->input->get_post('year'):date('Y');
        $data['useyear'] = ($this->input->get_post('year')!="")?$this->input->get_post('year'):date('Y');

        
        if($usemonth && $useyear){
            $data['current_month'] = date('F Y', strtotime("{$useyear}-{$usemonth}-01"));
        }else{
            $data['current_month'] = date('F Y');
        }

        // do the stuff for nextyear.
        if ($usemonth == 12)
        {
            $data['nextmonth'] = 1;
            $data['nextyear'] = $useyear+1;
        }else
        {
            $data['nextmonth'] = $usemonth+1;
            $data['nextyear'] = $useyear;
        }

        // do the stuff for prevyear.
        if ($usemonth == 1)
            {
            $data['prevmonth'] = 12;
            $data['prevyear'] = $useyear-1;
            }
        else
            {
            $data['prevmonth'] = $usemonth-1;
            $data['prevyear'] = $useyear;

        }

        //get days in month
        $data['days_in_month'] = cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));

        //get staff classID
        $params = array(
            'sel_query' => 'sa.ClassID',
            'staff_id' => $this->session->staff_id
        );
        $data['staff'] = $this->gherxlib->getStaffInfo($params)->row()->ClassID;

        // get all services for legend
        $data['serv_type_sql'] = $this->db->query("
            SELECT `id`, `type`
            FROM `alarm_job_type`
            WHERE `active` = 1
        ");

        $data['vts_quick_links'] = true;
        $data['uri'] = $uri;

        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }  
        $this->load->view('calendar/view_tech_schedule_tech', $data);
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_footer_tech', $data);
        }else{
            $this->load->view('templates/inner_footer', $data);
        }         

    }


    /**
     * For Tech
     * 
     */
    public function my_calendar(){
       
        //$data['start_load_time'] = microtime(true);

        $taff_params = array(
            'sel_query' => "sa.FirstName, sa.LastName",
            'staff_id' => $this->session->staff_id
        );
        $staff_name_query = $this->gherxlib->getStaffInfo($taff_params)->row_array();
        $staff_name = "{$staff_name_query['FirstName']}";
        $staff_class_id = $this->system_model->getStaffClassID();

        $data['title'] = "{$staff_name}'s Calendar";


        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }   
        $this->load->view('calendar/view_individual_staff_calendar_tech', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    
    public function ajax_get_tech_call_centre(){

        $tech_id = $this->input->post('tech');

        $params = array(
            'staff_id' => $tech_id
        );
        $cc_sql = $this->gherxlib->getStaffInfo($params);
        $cc = $cc_sql->row_array();

        $json_arrr = array(
            'other_call_centre' => $cc['other_call_centre'],
            'accomodation_id' => $cc['accomodation_id']
        );

        echo json_encode($json_arrr);

    }


    public function ajax_delete_calendar(){

        $json_data['status'] = false;
        $calendar_id = $this->input->post('calendar_id');

        if($calendar_id && !empty($calendar_id)){

            $this->db->where('calendar_id', $calendar_id);
            $this->db->delete('calendar');
            $this->db->limit(1);
            if($this->db->affected_rows()>0){
                $json_data['status'] = true;
            }

        }
       
        echo json_encode($json_data);

    }

    public function view_tech_calendar(){

        $month = $this->input->get_post('month');
        $data['month'] = $this->input->get_post('month');
        $year = $this->input->get_post('year');
        $data['year'] = $this->input->get_post('year');

        // IF THE DATES DONT EXISIT IN URL THEN USE CURRENT
        if(!isset($month)) {
            $month = date(m);
            $data['month'] = date(m);
        }
        if(!isset($year)) {
            $year = date(Y);
            $data['year'] = date(Y);
        }

        //get the number of days in the month
        $calendardays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $data['calendardays'] = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $data['countday'] = 0;
        $data['themonth'] = array();

        $month_start = $year . "-" . $month . "-01";
        $month_end = $year . "-" . $month . "-" . $calendardays;


        $monthname = mktime(0, 0, 0, $month, 1, $year);
        $data['monthname'] = date("F", $monthname);
        
        //pre month
        if($month == 1){
			$data['backyear'] = $year - 1;
			$data['backmonth'] = 12;			
		} else {
			$data['backmonth'] = $month - 1;
			$data['backyear'] = $year;
        }

        //GO FORWARDS ONE MONTH
		if($month == 12){
			$data['forwardyear'] = $year + 1;
			$data['forwardmonth'] = 1;			
		} else {
			$data['forwardmonth'] = $month + 1;
			$data['forwardyear'] = $year;
        }
        

        
        // Fetch current staff filter for user
        $cf_sql = $this->calendar_model->cal_filters();
		$staff_filter = array();
		$cf = $cf_sql->row_array();
		$data['staff_filter'] = explode(",", $cf['StaffFilter']);
        $data['staff_class_filter'] = explode(",", $cf['staff_class_filter']);
        


        
        // fetch all the calendar entries - differing start / end dates
        $cal_query = $this->calendar_model->fetch_calendar_by_diff_start_end_dates($month_start,$month_end);
        $cal_query_res1 =  $cal_query->result_array();
       
        // fetch all the calendar entries - single dates
        $cal_query2 = $this->calendar_model->fetch_calendar_by_single_dates();
        $cal_query_res2 =  $cal_query2->result_array();
      
       $data['rows'] = array_merge($cal_query_res1,$cal_query_res2);



        // USER CHECKBOX query
        //fetch all the staff who are active
        $staff_params = array(
            'sel_query' => "DISTINCT(sa.`StaffID`), sa.StaffID, sa.FirstName, sa.LastName, sa.working_days, sc.ClassName, sc.ClassID",
            'custom_joins' => array('join_table'=> 'staff_classes as sc' ,'join_on'=> 'sc.ClassID = sa.ClassID' , 'join_type'=>'left'),
            'sort_list' => array(
                array(
                    'order_by' => 'sc.`ClassName`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'sa.StaffID',
                    'sort' => 'ASC'
                )
            )
            
        );
        $data['query_tech'] = $this->gherxlib->getStaffInfo($staff_params);



        $data['title'] = "View Calendar";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('calendar/view_tech_calendar', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function cal_ajax(){

       
        // Update cal filter
        $UpdateCalFilterPost = $this->input->post('UpdateCalFilter');
        if($UpdateCalFilterPost && $UpdateCalFilterPost==1)
        {
            $json_data['success'] = 0;

            if(!isset($this->session->staff_id))
            {
                exit();
            }
            else
            {
                $serialized = trim($this->input->post('serialized'), ",");

                #Update or insert
                $query = $this->calendar_model->cal_filters(); //get staff-id
                $result = $query->row_array();

                

                if(isset($result['StaffId'])) //if has staff id > UPDATE
                {   
                    $cal_filters_update_data = array('StaffFilter'=>$serialized);
                    $this->db->where('StaffId', $this->session->staff_id);
                    $this->db->update('cal_filters', $cal_filters_update_data);
                    $this->db->limit(1);
                }
                else // No staff id > Insert
                {
                    $cal_filters_insert_data = array('StaffId'=>$this->session->staff_id, 'StaffFilter'=>$serialized );
                    $this->db->insert('cal_filters',$cal_filters_insert_data);
                }

                
                $json_data['success'] = 1;

                echo json_encode($json_data);
                exit();
            }
        }


        //UpdateCalStaffClassFilter
        $UpdateCalStaffClassFilter = $this->input->post('UpdateCalStaffClassFilter');
        if($UpdateCalStaffClassFilter && $UpdateCalStaffClassFilter==1)
        {
            $json_data['success'] = 0;

             if(!isset($this->session->staff_id))
            {
                exit();
            }
            else
            {
                $sc_serialized = trim($this->input->post('sc_serialized'), ",");


                #Update or insert
                $query = $this->calendar_model->cal_filters(); //get staffid
                $result = $query->row_array();

                if(isset($result['StaffId'])) //if has staff id > UPDATE
                {
                    $staff_class_filter_update_data = array('staff_class_filter'=>$sc_serialized);
                    $this->db->where('StaffId', $this->session->staff_id);
                    $this->db->update('cal_filters', $staff_class_filter_update_data);
                    $this->db->limit(1);
                }
                else // No staff id > Insert
                {
                    $staff_class_filter_insert_data = array('StaffId'=>$this->session->staff_id, 'staff_class_filter'=>$sc_serialized );
                    $this->db->insert('cal_filters',$staff_class_filter_insert_data);
                }

                $json_data['success'] = 1;

                echo json_encode($json_data);
                exit();
            }
        }

    }

    public function staff_calendar_csv(){
        
        // GRAB THE DATES FROM THE URL
        $month_post = $this->input->get_post('month');
        $year_post = $this->input->get_post('year');
        $payroll_export = $this->input->get_post('payroll_export');
        $country_id = $this->config->item('country');

        if($month_post){ $month = $month_post; }
        if($year_post){ $year = $year_post; }

        // IF THE DATES DONT EXISIT IN URL THEN USE CURRENT
        if(!$month_post) {
            $month = date('m');
        }
        if(!$year_post) {
            $year = date('Y');
        }



        //START CSV
        // file name 
        $random_str = rand().'-'.date('YmdHis');
        $filename = 'StaffCalendar'.$random_str.'-'.$month.'-'.$year.'.csv';

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        
        //the tables rely on this to form.
        $monthname = mktime(0, 0, 0, $month, 1, $year);
        $monthname = date("F", $monthname);
        
        //get the number of days in the month
        $calendardays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        

        $countday = 0;
        $themonth = array();

        if( $payroll_export == 1 ){
          
		
            $payroll_from = $this->system_model->formatDate($this->input->get_post('payroll_from'));
            $payroll_to = $this->system_model->formatDate($this->input->get_post('payroll_to'));
            $start_date_loop = $payroll_from;

            // new
            while( $start_date_loop <= $payroll_to  ){
              
                $current_date = date('Y-m-d',strtotime($start_date_loop));
                $start_date_loop = date('Y-m-d',strtotime("{$start_date_loop} + 1 day"));
                $themonth[$countday]['date'] = $current_date; // this is from old code, this is how they store it so follow
                $countday++;
            }
           
        }else{
            
            
            while($countday < $calendardays) {
                        
                $thedate = $countday + 1;
                $whiledate = $year.'-'.$month.'-'.$thedate;
                
                $themonth[$countday]['date'] = $whiledate;
                            
                $countday = $countday + 1;
            }			
            
            
        }


		$date_str = '"Last Name","First Name",Position';
        foreach($themonth as $theday){
          $thedate = date("d/m/Y", strtotime($theday['date']));
          $date_str .= ',"'.$thedate.'"';
        }

        echo $date_str;
        echo "\n";
        
        $staff_params = array(
            'sel_query' => "DISTINCT(sa.`StaffID`), sa.StaffID, sa.FirstName, sa.LastName, sa.working_days, sa.sa_position, sc.ClassName, sc.ClassID",
            'custom_joins' => array('join_table'=> 'staff_classes as sc' ,'join_on'=> 'sc.ClassID = sa.ClassID' , 'join_type'=>'left'),
            'sort_list' => array(
                array(
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                )
            )
            
        );
        $tech_sql = $this->gherxlib->getStaffInfo($staff_params);

        foreach($tech_sql->result_array() as $tech){
		  
            $cal_fil_sql = $this->calendar_model->cal_filters();
            $cal_fil = $cal_fil_sql->row_array();
            $staff_filter = explode(",", $cal_fil['StaffFilter']);
            
            if(!in_array($tech['StaffID'], $staff_filter)){
    
                $staff_name = "{$tech['LastName']}, {$tech['FirstName']}";
                $position = ucfirst($tech['sa_position']);
            
                echo "\"{$tech['LastName']}\",\"{$tech['FirstName']}\",\"{$position}\"";
                
                
            
                foreach($themonth as $theday){
                        //echo ",Day: {$theday['date']}  Tech: {$tech['StaffID']}";
                        
                        
                        
                        // if weekend
                        $weekDay = date('w', strtotime($theday['date']));
                        $isWeekend = ($weekDay == 0 || $weekDay == 6)?1:0;
                        $jday = date("D",strtotime($theday['date']));
                        
                        // get staff working days
                        $sa_sql = $this->db->select('working_days')->from('staff_accounts')->where('StaffID',$tech['StaffID'])->get();
                        $sa = $sa_sql->row_array();
                        $wd = $sa['working_days'];
                        
                        // if not working day
                        if( strchr($wd,$jday)==false && $isWeekend==0 ){
                            echo ",OFF";
                        }else{
                            
                            $sql = $this->db->query("
                                SELECT c.`calendar_id`, c.`staff_id`, c.`region`, c.`date_start`, c.`date_finish`, s.`FirstName`, s.`LastName`
                                FROM `calendar` AS c 
                                INNER JOIN `staff_accounts` AS s ON (s.`StaffID` = c.`staff_id`)
                                WHERE s.`Deleted` = 0 
                                AND s.`active` = 1 
                                AND c.`staff_id` ={$tech['StaffID']}
                                AND '{$theday['date']}' BETWEEN c.`date_start` AND c.`date_finish`
                            ");
                            
                            if($sql->num_rows()>0){
                            
                                $region_arr = [];
                                $region_imp = null;

                                foreach( $sql->result() as $row ){

                                    $region_arr[] = $row->region;

                                } 

                                // if multiple items, add new line
                                if( count($region_arr) > 0 ){

                                    $region_imp = implode("\n",$region_arr);

                                }
                                
                                echo ',"'.$region_imp.'"';
                                
                            }else{
                                echo ",";
                            }
                            
                        }
                        
                    
                        
                    
                }	
        
                echo "\n";
            
            }
          
          }

    }

   
    public function ajax_get_tech_run_list(){

        $tech_id = $this->input->get_post('tech_id');
        $date = $this->input->get_post('date');


        //get tech run
        $tr_sql = $this->db->select('*')->from('tech_run')->where(array('assigned_tech' => $tech_id, 'date' => $date))->get();

        if($tr_sql->num_rows()>0){            
            $tr = $tr_sql->row_array();
            $tr_id = $tr['tech_run_id'];
        }
  
        
        if( $tr_id > 0 ){ ?>
            <div class="map_link float-left mt-2">
                <a href="/tech_run/run_sheet_map/?tr_id=<?php echo $tr_id; ?>">
                    <span class="fa fa-map-marker" style="font-size:16px;"></span>
                </a>
            </div>
        <?php
        }
        ?> 
        
        <div style="clear:both;"></div>
        
        <div class="row tds_tbl_div">
            <div class="col-sm-12">
            <?php            
            if( $tr_id > 0 ){

                //get techrunrows
                $tr_sel = "
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,

                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,

                j.`id` AS jid,
                j.`precomp_jobs_moved_to_booked`,
                j.`completed_timestamp`,		

                p.`property_id`,
                p.`qld_new_leg_alarm_num`,
                p.`preferred_alarm_id`,

                a.`agency_id`,
                a.`allow_upfront_billing`
                ";
                $tr_params = array(
                'sel_query' => $tr_sel,
                'sort_list' => array(
                array(
                'order_by' => 'trr.sort_order_num',
                'sort' => 'ASC'
                )
                ),
                'display_only_booked' => 1,
                'display_query' => 0
                );
                $view_data['jr_list2'] = $this->tech_model->getTechRunRows($tr_id, $this->config->item('country'), $tr_params);    
                $view_data['tech_id'] = $tech_id;
                $view_data['date'] = $date;  
                $view_data['show_completed_col'] = true;                    

                $this->load->view('tech_run/tech_day_schedule_tech_table_list',$view_data);	

            }                                   
            ?>
            </div>
        </div>  
        <?php
                   

    }


}



?>
