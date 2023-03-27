<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Property_mod extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
        $this->load->database();
        $this->load->model('properties_model');
        $this->load->model('agency_model');
        $this->load->helper('url');
    }   

	/**
	 * Get Property Manger by agency id
	 */
	public function get_property_manager_by_agency_id(){

		$agency_id = $this->input->post('agency_id');

		$sel_query = "aua.agency_user_account_id, aua.email, aua.fname, aua.lname";
		$params = array(
			'sel_query' => $sel_query,
			'active' => 1,
			'agency_id' => $agency_id,
			'sort_list' => array(
				array('order_by' => 'aua.fname','sort' => 'ASC')
			)
		);
		$data['pm'] = $this->properties_model->get_agency_pm($params);
		
		//load html
		$this->load->view('properties/ajax_property/ajax_get_property_manager', $data);

	}


	/**
	 * Get Agency Services by agency id
	 */
	public function get_agency_services(){

		$agency_id = $this->input->post('agency_id');

		$params = array(
			'sel_query' => "*",
			'agency_id' => $agency_id,
			'join_table' => array('alarm_job_type')
		);
		$data['agency_services_list'] = $this->agency_model->get_agency_services($params);

		//load html
		$this->load->view('properties/ajax_property/ajax_get_services', $data);

	}

	public function ajax_check_private_fg(){

		$fg_id = $this->input->post('fg_id');

		if( $this->system_model->getAgencyPrivateFranchiseGroups($fg_id)== true ){
			echo 1;
		}else{
			echo 0;
		}

	}

	public function ajax_check_dha_agencies(){

		$fg_id = $this->input->post('fg_id');
		$is_dha_agency = 0;

		if( $this->system_model->isDHAagenciesV2($fg_id)==true ){
			$is_dha_agency = 1;
		}

		echo $is_dha_agency;

	}

	public function get_agency_services_for_sales_property(){

		$agency_id = $this->input->post('agency_id');
		$custom_where = "ajt.id = 12"; //get IC only
		$params = array(
			'sel_query' => "*",
			'agency_id' => $agency_id,
			'join_table' => array('alarm_job_type'),
			'custom_where' => $custom_where
		);
		$data['agency_services_list'] = $this->agency_model->get_agency_services($params);

		//load html
		$this->load->view('properties/ajax_property/ajax_get_service_sales_property', $data);

	}




}

