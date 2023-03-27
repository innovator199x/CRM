<?php

class JPDI extends FPDI {

    protected $CI;
    protected $dont_display_footer = 0;
    protected $dont_display_header = 0;
    protected $is_compliance_second_page_bg = 0;
    protected $is_compliance_second_page_bg_for_WE = 0;
    protected $_tplIdx;
    protected $is_new_invoice_template = 0;
    protected $is_new_combined_template = 0;
    protected $show_compliance_template = 0;

    function Header() {

        if ( $this->dont_display_header != 1 ){
            $this->Image($_SERVER['DOCUMENT_ROOT'] . '/documents/inv_cert_pdf_header.png', 150, 10, 50);
            $this->CI = & get_instance();
        }

        //Added by Gherx > display template hack for compliance pdf 2nd page and so on-----
        if( $this->is_compliance_second_page_bg >0 ){
           
            if (null === $this->_tplIdx) {
                if( COUNTRY == 1 ){ //AU Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/sats_statement_of_compliance_21202021_v2_template.pdf');
                }else{ //NZ Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/sats_statement_of_compliance_21202021_v2_template_nz.pdf');
                }
              
                $this->_tplIdx = $this->importPage(1);
            } else {
                if( COUNTRY == 1 ){ //AU Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/sats_statement_of_compliance_21202021_v2_template_2nd_page.pdf');
                }else{ //NZ Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/sats_statement_of_compliance_21202021_v2_template_2nd_page_nz.pdf');
                }
               
                $this->_tplIdx = $this->importPage(1);
            }
    
            $this->useTemplate($this->_tplIdx);
        }
        //Added by Gherx > display template hack for compliance pdf 2nd page and so on END-----

        //Added by Gherx invoice new template
        if( $this->is_new_invoice_template > 0 ){
            if (null === $this->_tplIdx) {
                if( COUNTRY == 1 ){ //AU Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/new_invoice_template_au.pdf');
                }else{ //NZ Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/new_invoice_template_nz.pdf');
                }
              
                $this->_tplIdx = $this->importPage(1);
            } else {
                if( COUNTRY == 1 ){ //AU Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/new_invoice_2page_au.pdf');
                }else{ //NZ Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/new_invoice_2page_nz.pdf');
                }
               
                $this->_tplIdx = $this->importPage(1);
            }
    
            $this->useTemplate($this->_tplIdx);
        }
        //Added by Gherx invoice new template end

        // Added by Gherx > Combine invoice new template
        if( $this->is_new_combined_template > 0 ){
            if (null === $this->_tplIdx) {
                if( COUNTRY == 1 ){ //AU Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/new_invoice_template_au.pdf');
                }else{ //NZ Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/new_invoice_template_nz.pdf');
                }
              
                $this->_tplIdx = $this->importPage(1);
            } /*elseif( $this->_tplIdx==1 ) {
                if( COUNTRY == 1 ){ //AU Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/sats_statement_of_compliance_21202021_v2_template.pdf');
                }else{ //NZ Template
                    $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/new_invoice_2page_nz.pdf');
                }
               
                $this->_tplIdx = $this->importPage(1);
            }*/else{
                if( $this->show_compliance_template ==0 ){
                    if( COUNTRY == 1 ){ //AU Template
                        //$this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/sats_statement_of_compliance_21202021_v2_template_2nd_page.pdf');
                        $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/new_invoice_2page_au.pdf');
                    }else{ //NZ Template
                        $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/new_invoice_2page_nz.pdf');
                    }
                   
                    $this->_tplIdx = $this->importPage(1);
                }else{
                    if( COUNTRY == 1 ){ //AU Template
                        $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/sats_statement_of_compliance_21202021_v2_template_2nd_page.pdf');
                    }else{ //NZ Template
                        $this->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/sats_statement_of_compliance_21202021_v2_template_2nd_page_nz.pdf');
                    }
                   
                    $this->_tplIdx = $this->importPage(1);
                }
                
            }
    
            $this->useTemplate($this->_tplIdx);
        }
        // Added by Gherx > Combine invoice new template end
        
    }

    function Footer() {

        if ( $this->dont_display_footer != 1 ){

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
        
    }

    function setCountryData($country_id) {
        $this->country_id = $country_id;
    }

    function set_dont_display_footer($dont_display_footer){
		$this->dont_display_footer = $dont_display_footer;
    }
    
    function set_dont_display_header($dont_display_header){
		$this->dont_display_header = $dont_display_header;
	}

    function is_compliance_second_page_bg($is_compliance_second_page_bg){
		$this->is_compliance_second_page_bg = $is_compliance_second_page_bg;
	}

    function is_compliance_second_page_bg_for_WE($is_compliance_second_page_bg_for_WE){
		$this->is_compliance_second_page_bg_for_WE = $is_compliance_second_page_bg_for_WE;
	}

    function is_new_invoice_template($is_new_invoice_template){
		$this->is_new_invoice_template = $is_new_invoice_template;
	}

    function is_new_combined_template($is_new_combined_template){
		$this->is_new_combined_template = $is_new_combined_template;
	}

    function show_compliance_template($show_compliance_template){
		$this->show_compliance_template = $show_compliance_template;
	}
    
}

?>