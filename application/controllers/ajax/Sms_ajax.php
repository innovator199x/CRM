<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_ajax extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();

        $this->load->model('sms_model');
    }

    public function auto_populate_sms_template()
    {
        $success = false;
        $category = $this->input->post('category');
        $job_id = $this->input->post('job_id');
        $data = array();

        if ( !empty($job_id) ) {

            if (!empty($category)) {
                $data = $this->sms_model->get_sms_template_by_category_name($category);
                $success = true;
            } else {
                //default template
                $data = $this->sms_model->get_sms_template_by_default();
            }

        }

        echo json_encode(['data' => $data, 'success' => $success]);
    }


}