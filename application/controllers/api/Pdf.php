<?php

class Pdf extends MY_ApiController {

    public function swms() {

        $this->load->model('jobs_model');
        $this->load->model('/inc/pdf_template');

        $job_id = $this->input->get('job_id');
        $swms_type = $this->input->get('swms_type');

        $staff_id =  $this->api->getJWTItem("staff_id");
        $country_id = $this->config->item('country');

        //check job id
        if( $job_id > 0 ){

            $en_pdf_params = [
                'job_id' => $job_id,
                'swms_type' => $swms_type,
                'output' => 'I'
            ];
            $this->pdf_template->swms($en_pdf_params);
            exit;

        }
        else{
            exit('Error: Please contact admin.');
        }

    }

}

?>