<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Emails_ajax extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();

        $this->load->model('email_model');
    }

    public function auto_populate_emails_template()
    {
        $success = false;
        $job_id = $this->input->post("job_id");
        $templates_type_id = $this->input->post("templates_type_id");
        $data = array();

        try {
      
            if ( !empty($job_id) ) {

                if (!empty($templates_type_id)) {
                    $data = $this->email_model->get_emails_templates_by_temp_type_id($templates_type_id);
                    $success = true;
                } else {
                    
                    //default template
                    $params = array(
                        'echo_query' => 0,
                        'active' => 1,
                        'sort_list' => array(
                            [
                                'order_by' => 'et.`template_name`',
                                'sort' => 'ASC'
                            ]
                        )
                    );
                    $data = $this->email_model->get_email_templates($params)->result();
                    $success = true;
                }
    
            }
            
        } catch(Exception $e) { //catch exception
            echo 'Message: ' .$e->getMessage();
        }

        echo json_encode(['data' => $data, 'success' => $success]);
          
    }


}