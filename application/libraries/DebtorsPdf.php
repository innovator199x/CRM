<?php

class DebtorsPdf extends FPDF{

	protected $CI;
	public $report_name;
	public $agency_name;

	function Header()
	{
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/documents/inv_cert_pdf_header.png',150,10,50);
		$this->CI =& get_instance();
	}
	
	function Footer()
	{

		$this->SetY(-33);
		$this->SetFont('Arial','I',8);

		$x = $this->GetX();
		$y = $this->GetY();

		$this->SetXY($x,$y);

		$this->Cell(0,10,"{$this->report_name} for {$this->agency_name} as of ".date('d/m/Y'),0,0,'L');

		$this->SetXY($x,$y);

		$this->Cell(0,10,"Page {$this->PageNo()}/{nb}",0,0,'R');
				
		if( $this->CI->config->item('country') == 1 ){ // AU
			$image = '/documents/inv_cert_pdf_footer_au.png';
		}else if( $this->CI->config->item('country') == 2 ){ // NZ
			$image = '/documents/inv_cert_pdf_footer_nz.png';
		}

		$this->Image($_SERVER['DOCUMENT_ROOT'] . $image,0,273,210);
	}
	
}

?>