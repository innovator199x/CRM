<?php

class ExpenseSummaryPdf extends FPDF {

    public $path;
    public $country_id;
    public $country;
    public $exp_sum;

    function Header() {
        //$this->Image($this->path . 'documents/cert_corner_img.png',198,0,100);
        $this->Image($this->path . '/documents/inv_cert_pdf_header.png', 226, 10, 50);
        // set default values
        $header_space = 6.5;
        $header_width = 100;
        $header_height = 7;
        $header_border = 0;
        $header_new_line = 1;
        $header_align = 'T';


        // Expense Summary
        $this->SetFont('Arial', 'B', 18);
        $this->Cell($header_width, $header_height, 'Expense Claim Form', $header_border, $header_new_line, $header_align);
        $this->Ln(5);

        // heading
        $heading2_font_size = 10;
        $heading2_col1 = 33;
        //        var_dump($exp_sum);
        $this->SetFont('Arial', '', $heading2_font_size);
        $this->Cell($heading2_col1, 6, 'Staff Name: ');
        $this->Cell(40, 6, $this->exp_sum['sa_fname'] . ' ' . $this->exp_sum['sa_lname']);
        $this->Ln();
        $this->Cell($heading2_col1, 6, 'Date Submitted: ');
        $this->Cell(40, 6, date('d/m/Y', strtotime($this->exp_sum['date'])));
        $this->Ln();
        $this->Cell($heading2_col1, 6, 'Line Manager: ');
        $this->Cell(40, 6, $this->exp_sum['lm_fname'] . ' ' . $this->exp_sum['lm_lname']);
        $this->Ln();


        $this->Ln(5);
    }

    function Footer() {
        // Go to 1.5 cm from bottom
        $this->SetY(-31);
        // Select Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Print current and total page numbers
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');

        if ($this->country_id == 1) { // AU
            $image = '/documents/inv_cert_pdf_footer_au.png';
        } else if ($this->country_id == 2) { // NZ
            $image = '/documents/inv_cert_pdf_footer_nz.png';
        }
    }

    function setPath($path) {
        $this->path = $path;
    }

    function getCountryData() {
        return $this->country;
    }

    function setCountryData($country_data) {
        $this->country = $country_data;
        $this->country_id = $country_data->country_id;
    }

}

?>