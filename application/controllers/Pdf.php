<?php

class Pdf extends CI_Controller {

    public function __construct() {

        parent::__construct();
        $this->load->model('/inc/job_functions_model');
        $this->load->model('/inc/alarm_functions_model');
        $this->load->model('/inc/functions_model');
        $this->load->model('/inc/pdf_template');
        $this->load->model('encryption_model');
        $this->load->model('vehicles_model');
    }

    public function view_combined() {

        $staff_id =  $this->session->staff_id;
        $country_id = $this->config->item('country');
        $job_id = $_GET['job_id'];
        $page = $_GET['page'];
        $output_type = $_GET['output_type'];
        
        if($page == "invoicing"){
            $job_id = $this->encryption_model->encrypt($_GET['job_id']);
        }
       
        //check job id
        if( $job_id && !empty($job_id) ){

            $decrypt_job_id = $this->encryption_model->decrypt(rawurldecode($job_id));

            $this->system_model->updateInvoiceDetails($decrypt_job_id); ## Run updateInvoice first
            
            //get state by job_id
            $state_query = $this->pdf_template->get_state_by_job_id($decrypt_job_id)->row();
            $p_state = $state_query->p_state;

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($decrypt_job_id));
            $bpay_ref_code = "{$decrypt_job_id}{$check_digit}";

            $job_details =  $this->job_functions_model->getJobDetails2($decrypt_job_id,$query_only = false);

            if($job_details == null)  exit('Error: Please contact admin.');
            # Alarm Details
            $alarm_details = $this->alarm_functions_model->getPropertyAlarms($decrypt_job_id, 1, 0, 2);
            $num_alarms = sizeof($alarm_details);

            # Property + Agent Details
            $property_details = $this->functions_model->getPropertyAgentDetails($job_details['property_id']);


            //pdf template switch base on state 
            $pdf = $this->pdf_template->pdf_combined_template_v2($decrypt_job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, "I");
            //pdf template switch base on state end

            $output_type2 = ($output_type!='')?$output_type:'I'; 

            $pdf->Output('invoice' . $bpay_ref_code . '.pdf', $output_type2);

        }else{
            exit('Error: Please contact admin.');
        }



    }

    public function view_invoice() {

        $staff_id =  $this->session->staff_id;
        $country_id = $this->config->item('country');
        $job_id = $_GET['job_id'];
        $page = $_GET['page'];
        $output_type = $_GET['output_type'];

        if($page == "invoicing"){
            $job_id = $this->encryption_model->encrypt($_GET['job_id']);
        }

        if( $job_id && !empty($job_id) ){

            $decrypt_job_id = $this->encryption_model->decrypt(rawurldecode($job_id));

            $this->system_model->updateInvoiceDetails($decrypt_job_id); ## Run updateInvoice first

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($decrypt_job_id));
            $bpay_ref_code = "{$decrypt_job_id}{$check_digit}";
            
            $job_details =  $this->job_functions_model->getJobDetails2($decrypt_job_id,$query_only = false);

            if($job_details == null) exit(EXIT_MESSAGE);
            # Alarm Details
            $alarm_details = $this->alarm_functions_model->getPropertyAlarms($decrypt_job_id, 1, 0, 2);
            $num_alarms = sizeof($alarm_details);

            # Property + Agent Details
            $property_details = $this->functions_model->getPropertyAgentDetails($job_details['property_id']);

            $pdf = $this->pdf_template->pdf_invoice_template($decrypt_job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, "I");

            $output_type2 = ($output_type!='')?$output_type:'I'; 

            $pdf->Output('invoice' . $bpay_ref_code . '.pdf', $output_type2);

        }else{
            exit('Error: Please contact admin.');
        }

    }

    public function view_quote_old() {

        $staff_id =  $this->session->staff_id;
        $country_id = $this->config->item('country');
        $job_id = $_GET['job_id'];
        $output_type = $_GET['output_type'];
        $qt = $this->input->get_post('qt'); // quotes type

        //check job id
        if( $job_id && !empty($job_id) ){

            $decrypt_job_id = $this->encryption_model->decrypt(rawurldecode($job_id));

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($decrypt_job_id));
            $bpay_ref_code = "{$decrypt_job_id}{$check_digit}";
            
            $job_details =  $this->job_functions_model->getJobDetails2($decrypt_job_id,$query_only = false);

            if($job_details == null) exit(EXIT_MESSAGE);
            # Alarm Details
            $alarm_details = $this->alarm_functions_model->getPropertyAlarms($decrypt_job_id, 1, 0, 2);
            $num_alarms = sizeof($alarm_details);

            # Property + Agent Details
            $property_details = $this->functions_model->getPropertyAgentDetails($job_details['property_id']);

            if( $qt == 'combined'){                

                  // combined quotes pdf *new
                  $pdf_name = 'combined_quotes_pdf_' . $bpay_ref_code.rand().date('YmdHis') . '.pdf';
                  $pdf_output = 'I'; //  send the file inline to the browser. The PDF viewer is used if available.
  
                  $combined_quotes_pdf_params = array(
                      'job_id' => $decrypt_job_id,
                      'job_details' => $job_details,
                      'property_details' => $property_details,                      
                      'pdf_name' => $pdf_name,
                      'pdf_output' => $pdf_output
                  );
                  $pdf = $this->pdf_template->pdf_combined_quote_template($combined_quotes_pdf_params);

            }else{

                $pdf = $this->pdf_template->pdf_quote_template($decrypt_job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, "I", null, $qt);
                $output_type2 = ($output_type!='')?$output_type:'I'; 
                $pdf->Output('invoice' . $bpay_ref_code . '.pdf', $output_type2);

            }
            

           

        }else{
            exit('Error: Please contact admin.');
        }

    }


    public function view_quote() {
        $page = $_GET['page'];

        $staff_id =  $this->session->staff_id;
        $country_id = $this->config->item('country');
        $job_id = $_GET['job_id'];
        $output_type = $_GET['output_type'];

        if($page == "quote"){
            $qt = $_GET['qt'];
            $job_id = $this->encryption_model->encrypt($_GET['job_id']);
        }
        else{
            $qt = $this->input->get_post('qt'); // quotes type
        }
        //$qt = "emerald";

        $this->load->model('jobs_model');
        $this->load->model('properties_model');

        //check job id
        if( $job_id && !empty($job_id) ){

            $decrypt_job_id = $this->encryption_model->decrypt(rawurldecode($job_id));

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($decrypt_job_id));
            $bpay_ref_code = "{$decrypt_job_id}{$check_digit}";

            $job_details =  $this->job_functions_model->getJobDetails2($decrypt_job_id,$query_only = false);

            if($job_details == null) exit(EXIT_MESSAGE);
            # Alarm Details
            $alarm_details = $this->alarm_functions_model->getPropertyAlarms($decrypt_job_id, 1, 0, 2);
            $num_alarms = sizeof($alarm_details);

            # Property + Agent Details
            $property_details = $this->functions_model->getPropertyAgentDetails($job_details['property_id']);

            if( $qt == 'combined'){                

                /*
                  // combined quotes pdf *new
                  $pdf_name = 'combined_quotes_pdf_' . $bpay_ref_code.rand().date('YmdHis') . '.pdf';
                  $pdf_output = 'I'; //  send the file inline to the browser. The PDF viewer is used if available.
  
                  $combined_quotes_pdf_params = array(
                      'job_id' => $decrypt_job_id,
                      'job_details' => $job_details,
                      'property_details' => $property_details,                      
                      'pdf_name' => $pdf_name,
                      'pdf_output' => $pdf_output
                  );
                  $pdf = $this->pdf_template->view_quote($combined_quotes_pdf_params);
                  */
                    
                    $pdf_name = 'combined_quotes_pdf_' . $bpay_ref_code.rand().date('YmdHis') . '.pdf';                    
                    
                    $en_pdf_params = array(
                        'job_id' => $decrypt_job_id,
                        'output' => 'I',

                        'job_id' => $decrypt_job_id,
                        'job_details' => $job_details,
                        'property_details' => $property_details,                      
                        'pdf_name' => $pdf_name,           
                    );
                    $this->pdf_template->combined_qoutes($en_pdf_params);

            }else{

                $pdf = $this->pdf_template->pdf_quote_template_v2($decrypt_job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, "I", null, $qt);
                $output_type2 = ($output_type!='')?$output_type:'I'; 
                $quote_name = ($qt=='emerald') ? 'economical_quotes_pdf' : 'brooks_quotes_pdf';
                $pdf->Output($quote_name.'_'. $bpay_ref_code . '.pdf', $output_type2);

            }                       

        }else{
            exit('Error: Please contact admin.');
        }

    }

    public function view_certificate() {

        $staff_id =  $this->session->staff_id;
        $country_id = $this->config->item('country');
        $job_id = $_GET['job_id'];
        $output_type = $_GET['output_type'];

        //check job id
        if( $job_id && !empty($job_id) ){

            $decrypt_job_id = $this->encryption_model->decrypt(rawurldecode($job_id));

            //get state by job_id
            $state_query = $this->pdf_template->get_state_by_job_id($decrypt_job_id)->row();
            $p_state = $state_query->p_state;

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($decrypt_job_id));
            $bpay_ref_code = "{$decrypt_job_id}{$check_digit}";
            
            $job_details =  $this->job_functions_model->getJobDetails2($decrypt_job_id,$query_only = false);

            if($job_details == null) exit(EXIT_MESSAGE);
            # Alarm Details
            $alarm_details = $this->alarm_functions_model->getPropertyAlarms($decrypt_job_id, 1, 0, 2);
            $num_alarms = sizeof($alarm_details);

            # Property + Agent Details
            $property_details = $this->functions_model->getPropertyAgentDetails($job_details['property_id']);

           

            //pdf template switch base on state 
            $pdf = $this->pdf_template->pdf_certificate_template_v2($decrypt_job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, "I");
            //pdf template switch base on state end

            $output_type2 = ($output_type!='')?$output_type:'I'; 

            $pdf->Output('invoice' . $bpay_ref_code . '.pdf', $output_type2);

        }else{
            exit('Error: Please contact admin.');
        }

    }



    public function entry_notice() {

        $this->load->model('jobs_model');
        $this->load->model('properties_model');

        $job_id_enc = $this->input->get_post('job_id');     

        $staff_id =  $this->session->staff_id;
        $country_id = $this->config->item('country');         

        //check job id
        if( $job_id_enc != '' ){

            // descrypt
            $job_id = $this->encryption_model->decrypt(rawurldecode($job_id_enc));           
            
            $en_pdf_params = array(
                'job_id' => $job_id,
                'output' => 'I'
            );
            $this->pdf_template->entry_notice_switch($en_pdf_params);            

        }else{
            exit('Error: Please contact admin.');
        }

    }

    public function swms() {

        $this->load->model('jobs_model');

        $job_id = $this->input->get_post('job_id');     
        $swms_type = $this->input->get_post('swms_type');

        $staff_id =  $this->session->staff_id;
        $country_id = $this->config->item('country');         

        //check job id
        if( $job_id > 0 ){        
            
            $en_pdf_params = array(
                'job_id' => $job_id,
                'swms_type' => $swms_type,
                'output' => 'I'
            );
            $this->pdf_template->swms($en_pdf_params);            

        }else{
            exit('Error: Please contact admin.');
        }

    }

    public function vehicle_details(){
        $vehicle_id = $_GET['id'];
        $this->pdf_template->vehicle_details($vehicle_id);  
    }
    

}

