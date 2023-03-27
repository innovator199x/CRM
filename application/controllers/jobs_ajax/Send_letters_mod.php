<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Send_letters_mod extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
        $this->load->database();
        $this->load->model('inc/functions_model');
    }   

	public function ajax_send_letters_no_tenant_email(){	
		$job_id_arr = $this->input->post('job_id_arr');
		$staff_id = $this->session->staff_id;
		$country_id = $this->config->item('country');
		$checkSucc = array();
		foreach( $job_id_arr as $job_id ){
			$params = array(
				"job_id" => $job_id,
				"staff_id" => $staff_id,
				"country_id" => $country_id
			);
			$isSucc = $this->functions_model->send_letters_no_tenant_email($params);
			array_push($checkSucc, $isSucc);
		}
		if (in_array(false, $checkSucc, true) === true) {
			echo json_encode(false);
		}else {
			echo json_encode(true);
		}
	}

	public function ajax_send_letters_sms_it() {
		$job_id_arr = $this->input->post('job_id_arr');
		$staff_id = $this->session->staff_id;
		$country_id = $this->config->item('country');
		$checkSucc = array();
		foreach( $job_id_arr as $job_id ){
			$params = array(
				"job_id" => $job_id,
				"staff_id" => $staff_id,
				"country_id" => $country_id
			);
			$isSucc = $this->functions_model->send_letters_send_tenant_sms($params);
			array_push($checkSucc, $isSucc);
		}
		if (in_array(false, $checkSucc, true) === true) {
			echo json_encode(false);
		}else {
			echo json_encode(true);
		}
	}

	public function ajax_send_letters_email_it() {
		$job_id_arr = $this->input->post('job_id_arr');
		$staff_id = $this->session->staff_id;
		$country_id = $this->config->item('country');
		$checkSucc = array();
		foreach( $job_id_arr as $job_id ){
			$params = array(
				"job_id" => $job_id,
				"staff_id" => $staff_id,
				"country_id" => $country_id
			);
			$isSucc = $this->functions_model->send_letters_send_tenant_email($params);
			array_push($checkSucc, $isSucc);
		}
		if (in_array(false, $checkSucc, true) === true) {
			echo json_encode(false);
		}else {
			echo json_encode(true);
		}
	}
	

}

