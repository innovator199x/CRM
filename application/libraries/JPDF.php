<?php

class JPDF extends FPDF {

    protected $CI;

    function Header() {
        $this->Image($_SERVER['DOCUMENT_ROOT'] . '/documents/inv_cert_pdf_header.png', 150, 10, 50);
        $this->CI = & get_instance();
    }

    function Footer() {

        // get logged user
        $params = array(
            'aua_id' => $this->CI->session->staff_id,
            'sel_query' => '
				aua.`agency_user_account_id`,
				aua.`fname`,
				aua.`lname`,
				aua.`photo`,
				aua.`email`,
				aua.`user_type`,
				aua.`phone`,
				aua.`job_title`,
				aua.`active`
			'
        );

        // get logged user
        $user_sql = $this->CI->system_model->get_user_accounts($params);
        $user = $user_sql->row();

        //$this->SetFont('Arial','I',8);
        //$this->Cell(0,10,"{$user->fname} {$user->lname} ".date('d/m/Y H:i'),0,0,'R');

        if ($this->CI->config->item('country') == 1) { // AU
            $image = '/documents/inv_cert_pdf_footer_au.png';
        } else if ($this->CI->config->item('country') == 2) { // NZ
            $image = '/documents/inv_cert_pdf_footer_nz.png';
        }

        $this->Image($_SERVER['DOCUMENT_ROOT'] . $image, 0, 273, 210);
    }

    function setCountryData($country_id) {
        $this->country_id = $country_id;
    }

}

?>