<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daily_ajax extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->database();
    $this->load->model('daily_model');
  }


  public function ajax_is_acknowledge_update()
  {
    $success = false;
    $property_id = $this->input->post('property_id');
    $acknowledge = $this->input->post('acknowledge');
    $staff_id = $this->session->staff_id;

    try {
      $params = array(
        'property_id'         => $property_id,
        'is_acknowledge'      => $acknowledge,
        'staff_id'            => $staff_id
      );

      $result = $this->daily_model->update_intentionally_hidden_active_properties($params);

      if ($result) {
        $success = true;
      }
    }
    catch(Exception $e) { //catch exception
      echo 'Message: ' .$e->getMessage();
    }

    echo json_encode($success); 
  }

}