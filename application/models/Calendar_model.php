<?php

class Calendar_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function get_tech_calendar($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('calendar as c');
        $this->db->join('staff_accounts as s','s.StaffID = c.staff_id','inner');
        $this->db->join('country_access as ca','ca.staff_accounts_id = s.StaffID','left');
        $this->db->join('accomodation as acco','acco.accomodation_id = c.accomodation_id','left');
       #$this->db->where('s.StaffID',$this->session->staff_id);
        $this->db->where('ca.country_id', $this->config->item('country'));


        if($params['StaffID'] && !empty($params['StaffID'])){
            $this->db->where('s.StaffID', $params['StaffID']);
        }

        if($params['calendar_id'] && !empty($params['calendar_id'])){
            $this->db->where('c.calendar_id', $params['calendar_id']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }
        
        // limit
        if( isset($params['limit']) && $params['limit'] > 0 ){
            $this->db->limit( $params['limit'], $params['offset']);
        }	

        $query = $this->db->get();
        if( isset($params['display_query']) && $params['display_query'] == 1 ){
            echo $this->db->last_query();
        }
        
        return $query;	

   }

   public function getAccomodation($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('accomodation');
        $this->db->where('country_id', $this->config->item('country'));

        //accomodation_id filter
        if( $params['accomodation_id'] && !empty($params['accomodation_id']) ){
            $this->db->where('accomodation_id', $params['accomodation_id']);
        }

        //area filter
        if( $params['area'] && !empty($params['area']) ){
            $this->db->where('area', $params['area']);
        }

        //search
        if  ( $params['search'] && !empty($params['search']) ) {
            $this->db->like('address', $params['search']);
        }


         // sort
         if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }
        
        // limit
        if( isset($params['limit']) && $params['limit'] > 0 ){
            $this->db->limit( $params['limit'], $params['offset']);
        }	


        $query = $this->db->get();
        if( isset($params['display_query']) && $params['display_query'] == 1 ){
            echo $this->db->last_query();
        }
        
        return $query;	



   }

    public function jsanitize($input){
        return filter_var(trim($input), FILTER_SANITIZE_STRING);
    }

    public function send_ical_to_mail($subject='', $from_email="", $to_name='', $to_email='', $event_name='', $description='', $date_start='', $date_end='' ){

        $this->load->library('email');

        // data
        // santize input
        $summary     = $this->jsanitize($event_name);
        $date = date("Ymd\THis");
        $datestart   = date("Ymd\THis",strtotime(str_replace("/","-",$this->jsanitize($date_start))));
        $dateend     = date("Ymd\THis",strtotime(str_replace("/","-",$this->jsanitize($date_end))));
        $filename    = 'iCalendar'.date('YmdHis');
        
        $eol = PHP_EOL;
        $unique_id = md5(time());
        

        // attachment
        $message = "BEGIN:VCALENDAR" .$eol;
        $message .= "VERSION:2.0" .$eol;
        $message .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN" .$eol;
        $message .= "BEGIN:VEVENT" . $eol;
        $message .= "UID:{$unique_id}" .$eol;
        $message .= "DTSTAMP:{$date}" .$eol;
        $message .= "SUMMARY:{$summary}" . $eol;
        $message .= "DESCRIPTION:{$description}" . $eol;
        $message .= "DTSTART:{$datestart}". $eol;
        $message .= "DTEND:{$dateend}" .$eol;
        $message .= "END:VEVENT" .$eol;
        $message .= "END:VCALENDAR" . $eol;
       
        
        // mail it
        
        /*
        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'itsmegherx@gmail.com',
            'smtp_pass' => 'asdfsfs',
            'wordwrap' => TRUE,
            'mailtype' => 'html',
            'charset' => 'utf-8'
        );
        */
         
        
        $config = Array(
            'mailtype'  => 'html', 
            'charset'   => 'utf-8'
        ); 
        $this->email->initialize($config);
		$this->email->set_newline("\r\n");
        $this->email->from($from_email, 'SATS');
        $this->email->to($to_email);
        $this->email->subject($subject);
        $this->email->attach($message,'attachment',"{$filename}.ics", 'text/calendar');
        $this->email->send();
            
    }

    /**
     * Fetch current staff filter for user
     */
    public function cal_filters(){
        $this->db->select('StaffId,StaffFilter,staff_class_filter');
        $this->db->from('cal_filters');
        $this->db->where('StaffId', $this->session->staff_id);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query;
    }


    /**
     * // fetch all the calendar entries - single dates
     */
    public function fetch_calendar_by_diff_start_end_dates($month_start, $month_end){   

        $sql = "SELECT c.calendar_id, c.staff_id, c.region, c.date_start, c.date_finish, s.FirstName, s.LastName, DATEDIFF(date_finish, date_start), `booking_target`, c.`accomodation`, c.`marked_as_leave`, s.`working_days`  FROM calendar c INNER JOIN staff_accounts s ON (s.StaffID = c.staff_id) WHERE s.Deleted = 0 AND c.date_start = c.date_finish AND c.date_start >= '{$month_start}' AND c.date_finish <= '{$month_end}' AND s.active = 1 AND c.`country_id` ={$this->config->item('country')} ORDER BY staff_id, date_start;";
		$query = $this->db->query($sql);
        return $query;
        
    }

    /**
     * // fetch all the calendar entries - single dates
     */
    public function fetch_calendar_by_single_dates(){
		$sql = "SELECT c.calendar_id, c.staff_id, c.region, c.date_start, c.date_finish, s.FirstName, s.LastName, DATEDIFF(date_finish, date_start) AS num_days, `booking_target`, c.`accomodation`, c.`marked_as_leave`, s.`working_days`  FROM calendar c INNER JOIN staff_accounts s ON (s.StaffID = c.staff_id) WHERE s.Deleted = 0 AND c.date_start != c.date_finish AND s.active = 1 AND c.`country_id` ={$this->config->item('country')} ORDER BY staff_id, date_start;";
		$query = $this->db->query($sql);
        return $query;
    }

    

}
