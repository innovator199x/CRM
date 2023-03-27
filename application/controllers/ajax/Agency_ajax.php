<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agency_ajax extends CI_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->database();
      $this->load->model('agency_model');

      $this->load->library('email');
  }

  public function ajax_high_touch_update_v2()
  {
      $agency_id = $this->input->get_post('agency_id');
      $priority = $this->input->get_post('priority');
      $priority_reason = $this->input->get_post('priority_reason');
      $ht_date_added = $this->input->get_post('ht_date_added');
      $unmark = $this->input->get_post('unmark');
      $staff_id = $this->session->staff_id;
      $success = 0;

      $this->load->model('email_model');

      $data['staff'] =  $this->staff_accounts_model->get_staff_accounts_details($staff_id);
      $data['agency_name']    = $this->agency_model->get_agency_details($agency_id);

      if (empty($priority_reason) && $priority > 0) {
        echo json_encode(array('error' => $success));
        return false;
      } 
      else {
        if (empty($priority_reason) && $priority == 0) {
            $reason = "";
        } 
        else{
            $reason = "because";
        }
        $update_ht = $this->agency_model->save_agency_high_touch($agency_id, $priority, $priority_reason, $staff_id);
      }

      if ($update_ht) {
          $success = 1;
          $marked_str = ( $priority >= 0 && $unmark == 0 )? 'marked' : 'unmarked';
          if($priority == 1){
            $ap = "High Touch (HT)";
          }
          else if($priority == 2){
            $ap = "Very Important Person (VIP)";
          }
          else if($priority == 3){
            $ap = "Handle With Care (HWC)";
          }
          else{
            $ap = "Regular Agency";
          }

          //insert log
          $log_details = "Agency <b>{$marked_str}</b> as {$ap} {$reason} {$priority_reason}";
          $log_params = array(
              'title' => 46,  // Agency Update
              'details' => $log_details,
              'display_in_vad' => 1,
              'created_by_staff' => $this->session->staff_id,
              'agency_id' => $agency_id
          );
          $this->system_model->insert_log($log_params);

          // email settings
          $email_config = Array(
              'mailtype' => 'html',
              'charset' => 'utf-8'
          );

          $from_name = "Smoke Alarm Testing Services";

          $from_email = $country_row->outgoing_email;
          $subject = 'Agency Priority Updated';

          $data['priority'] = ( $priority >= 0 && $unmark == 0 )? 'marked' : 'unmarked';
          $data['priority_reason'] =  $priority_reason;
          $data['agency_id'] = $agency_id;
          $data['abb'] = $ap;

          // content
          $email_body = $this->load->view('emails/agency_high_touch_email', $data, true);

          $to_email = $this->config->item('sats_info_email');
          $cc_email = $this->config->item('sats_sales_email');

          $this->email->initialize($email_config);
          $this->email->clear(TRUE);
          $this->email->from($this->config->item('sats_it_email'), $from_name);
          $this->email->to($to_email);
          $this->email->cc($cc_email); 
          //$this->email->to("lpagiwayan@gmail.com");  

          $this->email->subject($subject);
          $this->email->message($email_body);

          // send email
          if ($this->email->send()) {
              $ret_json = array(
                  'success' => $success
              );
          } else {
              echo 'error!'; die();
          }
      }

      echo json_encode($ret_json);
  }


}