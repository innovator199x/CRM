<?php

class Bookings extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('booking_model');
    }

    public function view_schedule($num_days = ""){

        $data['title'] = 'Booking Schedule';

        $str_sql = $this->db->query("
        SELECT `booking_schedule_num` 
        FROM `staff_accounts`
        WHERE `StaffID`={$this->session->staff_id}");
        $str_row = $str_sql->row();

        if( $str_row->booking_schedule_num > 0){
            $num_days = $str_row->booking_schedule_num;
        } else {
            $num_days = (!empty($num_days) && $num_days >= 0) ? $num_days : 14;
        }

        $data['num_days'] = $num_days;
        $data['tech_runs'] = $this->booking_model->get_tech_with_runs();
        $data['run_dates'] = $this->booking_model->get_tech_run_dates();
        $data['run_status'] = $this->booking_model->get_run_status();

        //get states
		$params = (object)["get_states" => true];
		$data['states'] = $this->booking_model->get_tech_runs($params);
		$params = (object)["get_states" => false];

        $booking = $this->booking_model->get_booking_schedule_num_days();

        // get schedule
        $num_days = (!empty($num_days) && $num_days >= 0) ?
                        $num_days :
                        ($booking->schedule_num_days > 0 ? $booking->schedule_num_days : 14);

        $data['num_days'] = $num_days;

        $this->load->view('templates/inner_header', $data);
        $this->load->view('bookings/schedule',$data);
        $this->load->view('templates/inner_footer');
    }

    public function update_preferred_day(){
        $day = $this->input->get_post('bs_num');
        $this->db->query("UPDATE `staff_accounts` SET `booking_schedule_num`= {$day} WHERE `StaffID`='{$this->session->staff_id}'");
    }
}

?>