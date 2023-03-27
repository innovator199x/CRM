<?php

class StatementsPdf extends FPDF {

    public $path;
    public $country_id;
    public $agency_id;
    public $agency;
    var $B = 0;
    var $I = 0;
    var $U = 0;
    var $HREF = '';
    var $ALIGN = '';

    function Header() {

// get agency details
        $jparams = array(
            'agency_id' => $this->agency_id,
            'join_table' => 'country'
        );
        $agency = $this->agency;
        $a_name = $agency['agency_name'];
        $a_address1 = "{$agency['address_1']} {$agency['address_2']}";
        $a_address2 = "{$agency['address_3']} {$agency['state']} {$agency['postcode']}";
        $a_country_name = $agency['country'];
        $country_id = $agency['country_id'];
        $fg_id = $agency['franchise_groups_id'];

        if ($this->PageNo() == 1) {


            /* if ($this->country_id == 1) { // AU
              $image = '/documents/statements_header_au.png';
              } else if ($this->country_id == 2) { // NZ
              $image = '/documents/statements_header_nz.png';
              } */

            if ($this->country_id == 1) { // AU
                $image = '/documents/inv_cert_pdf_header.png';
            } else if ($this->country_id == 2) { // NZ
                $image = '/documents/inv_cert_pdf_header.png';
            }


            $this->Image($this->path . $image, 150, 10, 50);



            // set default values
            $header_width = 0;
            $header_height = 6;
            $header_border = 0;
            $header_new_line = 1;
            $header_align = 'T';

            //header info
            $this->SetXY(10, 35);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell($header_width, 5, "1300 41 66 67", $header_border, 1, 'R');
            $this->SetFont('Arial', null, 10);
            $country_email = ($this->country_id == 1) ? 'accounts@sats.com.au' : 'accounts@sats.co.nz';
            $this->MultiCell($header_width, 5, "PO Box 6393 \n Yatala QLD 4207 \n {$country_email}", $header_border, 'R');
            $y = $this->GetY();

            $x_add = 10;
            $y_add = 54;
            $this->SetXY($x_add, $y_add);
            $font_size = 10;
            $this->SetFont('Arial', 'B', $font_size);
            $this->Cell($header_width, 4, $a_name, $header_border, 1, 'L');
            $y_add = $this->GetY();

            // agency address
            $this->SetXY($x_add, $y_add);
            $this->SetFont('Arial', null, $font_size);
            $agency_text = "{$a_address1}\n{$a_address2}";
            $this->MultiCell($header_width, 4, $agency_text, $header_border, 'L');
            $y = $this->GetY();

            $x = 10;
            $y = 60;
            // statement
            $x = $header_width + 10;
            $this->SetXY($x, $y);
            $this->SetFont('Arial', 'B', 14);
            $this->SetTextColor(180, 32, 37);
            $this->Cell(155, 7, 'STATEMENT', 0, null, 'R');
            $this->SetTextColor(0, 0, 0);
            $y = $this->GetY();


            // Current as of 
            $to_date = ( $this->to_date != '' ) ? $this->to_date : date('d/m/Y');
            $this->SetFont('Arial', 'B', 12);
            $this->Cell($header_width, 7, ' As at ' . $to_date, 0, null, 'R');
            $y = $this->GetY();

            $this->Ln();
        }

        $url = $_SERVER['SERVER_NAME'];
        if ($this->country_id == 1) { // AU
            $compass_fg_id = 39;
        }


        // table header
        $cell_height = 5;
        $font_size = 8;

        $col1 = 20;
        $col2 = 40;
        $col3 = 81;
        $col5 = 15;
        $ref_width = 30;

        // grey
        $this->SetFillColor(238, 238, 238);
        $this->SetFont('Arial', 'B', $font_size);
        $this->Cell($col1, $cell_height, 'Date', 1, null, null, true);
        $this->Cell($ref_width, $cell_height, 'Reference', 1, null, null, true);
        if ($fg_id == $compass_fg_id) { // compass only
            $this->Cell($col1, $cell_height, 'Index No.', 1, null, null, true);
            $this->Cell($col3 - 20, $cell_height, 'Description', 1, null, null, true);
        } else {
            $this->Cell($col3, $cell_height, 'Description', 1, null, null, true);
        }
        $this->Cell($col5, $cell_height, 'Charges', 1, null, null, true);
        $this->Cell($col5, $cell_height, 'Payments', 1, null, null, true);
        $this->Cell($col5, $cell_height, 'Credits', 1, null, null, true);
        $this->Cell($col5, $cell_height, 'Balance', 1, null, null, true);
        $this->Ln();
    }

    function Footer() {

        // Go to 1.5 cm from bottom
        $this->SetY(-31);
        // Select Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Print current and total page numbers
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');

        if ($this->country_id == 1) { // AU
            $image = '/documents/statements_footer_au.png';
        } else if ($this->country_id == 2) { // NZ
            $image = '/documents/statements_footer_nz.png';
        }

        $this->Image($this->path . $image, 2, 273, 208);
    }

    function setPath($path) {
        $this->path = $path;
    }

    function getCountryData($country_id) {
        return mysql_query("
			SELECT *
			FROM `countries`
			WHERE `country_id` = {$country_id}
		");
    }

    function setCountryData($country_id) {
        $this->country_id = $country_id;
    }

    function setAgency($agency) {
        $this->agency = $agency;
        return $this;
    }

    //added by gherx for pdf html content
    function WriteHTML($html) {
        //HTML parser
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                //Text
                if ($this->HREF)
                    $this->PutLink($this->HREF, $e);
                elseif ($this->ALIGN == 'center')
                    $this->Cell(0, 5, $e, 0, 1, 'C');
                else
                    $this->Write(5, $e);
            } else {
                //Tag
                if ($e[0] == '/')
                    $this->CloseTag(strtoupper(substr($e, 1)));
                else {
                    //Extract properties
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $prop = array();
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
                            $prop[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $prop);
                }
            }
        }
    }

    function OpenTag($tag, $prop) {
        //Opening tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, true);
        if ($tag == 'A')
            $this->HREF = $prop['HREF'];
        if ($tag == 'BR')
            $this->Ln(5);
        if ($tag == 'P')
            $this->ALIGN = $prop['ALIGN'];
        if ($tag == 'HR') {
            if (!empty($prop['WIDTH']))
                $Width = $prop['WIDTH'];
            else
                $Width = $this->w - $this->lMargin - $this->rMargin;
            $this->Ln(2);
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetLineWidth(0.4);
            $this->Line($x, $y, $x + $Width, $y);
            $this->SetLineWidth(0.2);
            $this->Ln(2);
        }
    }

    function CloseTag($tag) {
        //Closing tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';
        if ($tag == 'P')
            $this->ALIGN = '';
    }

    function SetStyle($tag, $enable) {
        //Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s)
            if ($this->$s > 0)
                $style .= $s;
        $this->SetFont('', $style);
    }

    function PutLink($URL, $txt) {
        //Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }

    //added by gherx for pdf html content end
}

?>