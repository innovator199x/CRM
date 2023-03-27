<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dpc_mod extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
        $this->load->database();
        $this->load->model('/inc/pdf_template');
        $this->load->model('/inc/job_functions_model');
        $this->load->model('/inc/functions_model');
        $this->load->model('/inc/email_functions_model');
    }   

    public function view_combined() {
    	$pdf = new FPDF();
		// $pdf->AddPage();
		// $pdf->SetFont('Arial','B',16);
		// $pdf->Cell(40,10,'Hello World!');
		// $pdf->Output();
		# Job Details
		$job_id = $this->input->get('job_id');
		$job_details = $this->system_model->getJobDetails2($job_id);

		# Appliance Details
		$appliance_details = $this->system_model->getPropertyAlarms($job_id, 1, 0);
		$num_appliances = sizeof($appliance_details);

		# Alarm Details
		$alarm_details = $this->system_model->getPropertyAlarms($job_id, 1, 0, 2);
		$num_alarms = sizeof($alarm_details);

		# Safety Switch Details
		$safety_switches = $this->system_model->getPropertyAlarms($job_id, 0, 1, 4);
		$num_safety_switches = sizeof($safety_switches);

		$this->db->select('*');
		$this->db->from('corded_window');
		$this->db->where('job_id', $job_id);
		$cw_sql_ex = $this->db->get();
		$cw_sql = $cw_sql_ex->result_array();

		$job_tech_sheet_job_types = $this->job_functions_model->getTechSheetAlarmTypesJob($job_id, true);

		$property_details = $this->functions_model->getPropertyAgentDetails($job_details[0]['property_id']);


		// AUTO - UPDATE INVOICE DETAILS
		// $crm_pdf = new Sats_Crm_Class;
		// $crm_pdf->updateInvoiceDetails($job_id);

		$this->system_model->updateInvoiceDetails($job_id);

		$property_job_types = $this->job_functions_model->getTechSheetAlarmTypesJob($job_details[0]['property_id'], true);


		switch($job_details[0]['jservice']){
			case 2:
				$service = 'Smoke Alarms';
				$service2 = 'Alarm';
			break;
			case 5:
				$service = 'Safety Switch';
				$service2 = 'Switch';
			break;
			case 6:
				$service = 'Corded Windows';
				$service2 = 'Window';
			break;
			case 7:
				$service = 'Pool Barriers';
				$service2 = 'Pool';
			break;
			case 12:
				$service = 'Smoke Alarms (IC)';
				$service2 = 'Alarm';
			break;
		}

		#instantiate only if required
		if(!isset($pdf)) {
			
			//$pdf=new FPDF('P','mm','A4');
			//include('fpdf_override.php');
			$pdf=new jPDF('P','mm','A4');
			$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
			$pdf->setCountryData($job_details[0]['country_id']);
		}

		$pdf->SetTopMargin(40);
		$pdf->SetAutoPageBreak(true,35);
		$pdf->AddPage();

		/*
		# If external PDF (linked from email) - add header and footer images
		if(defined('EXTERNAL_PDF'))
		{
			if(COMPANY_ABBREV_NAME == "SATS")
			{
				

				$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_corner_img.png',110,0,100);
				$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,263,210);  
			}
			else
			{
				$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_corner_img.png',0,0,210);
				$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,271.5,210);  
			}
		}else{
			if($print!=true){
				$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_corner_img.png',110,0,100);
				$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,263,210);
			}	
		}
		*/



		if( $job_details[0]['show_as_paid']==1 || ( is_numeric($job_details[0]['invoice_balance']) && $job_details[0]['invoice_balance'] == 0 ) ){
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'images/paid.png',90,110);
		}


		// append checkdigit to job id for new invoice number
		$check_digit = $this->customlib->getCheckDigit(trim($job_id));
		$bpay_ref_code = "{$job_id}{$check_digit}";	




		$pdf->SetFont('Arial','',10); 

		/*
		$pdf->Cell(0,14,'',0,1,'C');
		// $pdf->Cell(0,5,'A.B.N.    28 132 807 491',0,1,'');
		$pdf->Cell(0,5,'',0,1,'');

		$pdf->Cell(0,10,'',0,1,'C');
		*/

		//$pdf->Ln(18);

		// space needed to fit envelope
		$pdf->Cell(20,10,'');

		$pdf->Cell(70,5,'Invoice Date:   ' . $job_details[0]['date']); 

		$pdf->SetFont('Arial','B',14);

		if(isset($job_details[0]['tmh_id']))
		{
			$pdf->Cell(100,5,'Tax Invoice    #' . str_pad($job_details[0]['tmh_id'] . " TMH", 6, "0", STR_PAD_LEFT),0,1,'C');
		}
		else
		{
			$pdf->Cell(100,5,'Tax Invoice    #' . $bpay_ref_code,0,1,'C');
		}


		$pdf->SetFont('Arial','',10);
		#$pdf->Cell(40,5,'Invoice #' . str_pad($job_id, 6, "0", STR_PAD_LEFT)); 

		$pdf->Ln(5);

		# Agent Details
		$curry = $pdf->GetY();
		$currx = $pdf->GetX();

		// space needed to fit envelope
		$pdf->Cell(20,10,'');

		if( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' ){
			$landlord_txt = "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}";
			$landlord_txt2 = "\n\nLANDLORD: {$landlord_txt}";
		}else{
			$landlord_txt = "LANDLORD";
			$landlord_txt2 = "";
		}


		// compass index number
		if( $property_details['compass_index_num'] != '' ){
			$compass_index_num = "\nINDEX NO.: {$property_details['compass_index_num']}";	
		}else{
			$compass_index_num = "\n";
		}



		$agency_address_txt = htmlspecialchars_decode("{$property_details['a_address_1']} {$property_details['a_address_2']}\n{$property_details['a_address_3']} {$property_details['a_state']} {$property_details['a_postcode']}");
		$property_address_txt = htmlspecialchars_decode("{$property_details['address_1']} {$property_details['address_2']}\n{$property_details['address_3']} {$property_details['state']} {$property_details['postcode']}");
		$workorder_txt = ($job_details[0]['work_order'])?"\nWORK ORDER: {$job_details[0]['work_order']}":"";

		# Hack for LJ Hooker Tamworth - display Landlord in different spot for them
		if($property_details['agency_id'] == 1348){
		$pdf->MultiCell(100, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\nDATE OF VISIT: " . $job_details[0]['date']);
			$box1_h = $pdf->GetY();
			$pdf->SetY($curry);
			$pdf->SetX(124);
			$pdf->MultiCell(100, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" );
			$box2_h = $pdf->GetY();	
			$pdf->Ln(6);
		}else if ($property_details['agency_id'] == 3079){	
			$pdf->MultiCell(100, 5, "ATTN: {$landlord_txt}\n" . "\n\nDATE OF VISIT: " . $job_details[0]['date']);
			$box1_h = $pdf->GetY();
			$pdf->SetY($curry);
			$pdf->SetX(124);
			$pdf->MultiCell(100, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" );
			$box2_h = $pdf->GetY();	
		}else{	
			$pdf->MultiCell(100, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\nDATE OF VISIT: " . $job_details[0]['date']);
			$box1_h = $pdf->GetY();
			$pdf->SetY($curry);
			$pdf->SetX(124);
			$pdf->MultiCell(100, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" );
			$box2_h = $pdf->GetY();	
		}

		#$pdf->SetX(0);

		if($box1_h>$box2_h){
			$pdf->SetY($box1_h);
		}else{
			$pdf->SetY($box2_h);
		}

		$pdf->Ln(5);

		$curry = $pdf->GetY();
		$currx = $pdf->GetX();
		$pdf->SetLineWidth(0.4);
		$pdf->Line($currx, $curry, $currx + 190, $curry);
		$pdf->Ln(1.5);

		$pdf->Cell(15,5,"Qty");
		$pdf->Cell(45,5,"Item");
		$pdf->Cell(80,5,"Description");
		$pdf->Cell(25,5,"Unit Price");
		$pdf->Cell(25,5,"Total Amount");
		$pdf->Ln(6);


		$pdf->Cell(140.5,5,"");
		$pdf->Cell(25.5,5,"Inc. GST");
		$pdf->Cell(25,5,"Inc. GST");
		$pdf->Ln(6);


		$curry = $pdf->GetY();
		$currx = $pdf->GetX();
		$pdf->SetLineWidth(0.4);
		$pdf->Line($currx, $curry, $currx + 190, $curry);
		$pdf->Ln(5);

		// get service
		// $os_sql = getService($job_details[0]['jservice']);
		$os_sql = $this->job_functions_model->getService($job_details[0]['jservice']);	
		$os = $os_sql->row();

		# Add Job Type
		$pdf->Cell(15,5,"1", 0, 0, '');
		$pdf->Cell(45,5,$job_details[0]['job_type']);
		$pdf->Cell(80,5,$os->type);
		$pdf->Cell(19,5,"$".number_format($job_details[0]['job_price'], 2), 0, 0, 'R');
		$pdf->Cell(31,5,"$".number_format($job_details[0]['job_price'], 2), 0, 0, 'R');
		$pdf->Ln();

		$grand_total = $job_details[0]['job_price'];

		for($x = 0; $x < $num_alarms; $x++)
		{
			if($alarm_details[$x]['new'] == 1)
			{
				#$pdf->Cell(25,5,$alarm_details[$x]['alarm_pwr']);
				#$pdf->Cell(35,5,$alarm_details[$x]['alarm_type']);
				#$pdf->Cell(25,5,"Expiry");
				#$pdf->Cell(35,5,$alarm_details[$x]['expiry']);
				#$pdf->Ln();
				
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(15,5,"1", 0, 0, '');
				$pdf->Cell(45,5,$alarm_details[$x]['alarm_pwr']);
				$pdf->Cell(80,5,"Supply & Install " . $alarm_details[$x]['alarm_type'] . " Smoke Alarm");
				$pdf->Cell(19,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
				$pdf->Cell(31,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
				$pdf->Ln();
					
					$pdf->SetFont('Arial','I',10);
					$pdf->Cell(15,5,"", 0, 0, 'C');
					$pdf->Cell(45,5,"");
					$pdf->SetTextColor(255, 0, 0); // red
					$pdf->Cell(80,5,"Reason: " . $alarm_details[$x]['alarm_reason']);
					$pdf->SetTextColor(0, 0, 0);
					$pdf->Cell(19,5,"", 0, 0, 'R');
					$pdf->Cell(31,5,"", 0, 0, 'R');
					$pdf->Ln();
				
				$grand_total += $alarm_details[$x]['alarm_price'];
			}
		}


		// surcharge
		$sc_sql = $this->db->query("SELECT *, m.`name` AS m_name 
						FROM `agency_maintenance` AS am
						LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
						WHERE am.`agency_id` = {$property_details['agency_id']}
						AND am.`maintenance_id` > 0
					");

		$sc = $sc_sql->row();

		if( $grand_total!=0 && $sc->surcharge==1 ){
			
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(15,5,"1", 0, 0, '');
			$pdf->Cell(45,5,$sc->m_name);
			$surcharge_txt = ($sc->display_surcharge==1)?$sc->surcharge_msg:'';
			$pdf->Cell(80,5,$surcharge_txt);
			$pdf->Cell(19,5,"$".number_format($sc->price, 2), 0, 0, 'R');
			$pdf->Cell(31,5,"$".number_format($sc->price, 2), 0, 0, 'R');
			$pdf->Ln();
			
			$grand_total += $sc->price;
			
		}

		// CREDITS
		$credit_sql = $this->db->query("SELECT *
						FROM `invoice_credits` AS ic 
						WHERE ic.`job_id` = {$job_id}
					");

		$credit_exec = $credit_sql->result_array();

		foreach ($credit_exec as $credit) {
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(15,5,"1", 0, 0, '');
			$pdf->Cell(45,5,'Credit');
			$pdf->SetFont('Arial','I',10);
			$pdf->SetTextColor(255, 0, 0); // red
			$pdf->Cell(80,5,'Reason: '.$this->system_model->getInvoiceCreditReason($credit['credit_reason']));
			$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('Arial','',10);
			$pdf->Cell(19,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
			$pdf->Cell(31,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
			$pdf->Ln();
			
			$grand_total -= $credit['credit_paid'];
		}


		/*
		$pdf->Ln(5);

		$curry = $pdf->GetY();
		$currx = $pdf->GetX();
		$pdf->SetLineWidth(0.4);
		$pdf->Line($currx, $curry, $currx + 190, $curry);
		$pdf->Ln(5);
		*/
		    
		$pdf->Ln(8);
		$pdf->SetFont('Arial','',10);





		// get country
		$c_sql = $this->db->query("SELECT *
							FROM `agency` AS a
							LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
							WHERE a.`agency_id` = {$property_details['agency_id']}
						"); 
		$c = $c_sql->row();

		// gst
		if($c->country_id==1){
			$gst = $grand_total / 11;
		}else if($c->country_id==2){
			$gst = ($grand_total*3) / 23;
		}	

		// get cursor position
		$cursor_y = $pdf->GetY();

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(140,5,"", 0, 0, 'C');
		//$text = 'Sale Amount';
		$pdf->Cell(19,5,$text, 0, 0, 'R');
		//$pdf->Cell(31,5,"$" . number_format($grand_total - ($grand_total / 11), 2), 0, 0, 'R');
		//$gst = $grand_total * .10;
		$pdf->Cell(31,5,"$" . number_format($grand_total-($gst), 2), 0, 0, 'R');
		$pdf->Ln();

		$pdf->Cell(140,5,"", 0, 0, 'C');
		//$text = 'GST';
		$pdf->Cell(19,5,$text, 0, 0, 'R');
		//$pdf->Cell(31,5,"$" . number_format($grand_total / 11, 2), 0, 0, 'R');
		$pdf->Cell(31,5,"$" . number_format($gst, 2), 0, 0, 'R');
		$pdf->Ln();

		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(140,5,"", 0, 0, 'C');
		//$text = 'Invoice Total';
		$pdf->Cell(25,5,$text, 0, 0, 'R');
		//$pdf->Cell(31,5,"$" . number_format($grand_total, 2), 0, 0, 'R');
		$pdf->Cell(25,5,"$" . number_format($grand_total, 2), 'B', 0, 'R');
		//$pdf->Ln(12);
		$pdf->Ln();



		// balance
		$pdf->SetFont('Arial','I',10);
		$pdf->Cell(140,10,"", 0, 0, 'C');
		$text = 'Amount Owing';
		$pdf->Cell(25,10,$text, 0, 0, 'R');
		$pdf->SetFont('Arial','B',12);
		$inv_balance = ( is_numeric($job_details[0]['invoice_balance']) )?$job_details[0]['invoice_balance']:$grand_total;
		$pdf->Cell(25,10,'$'.number_format($inv_balance, 2), 0, 0, 'R');
		$pdf->Ln();



		$x_pos = 10;
		$pdf->SetXY($x_pos,(($cursor_y)-1.3));	


		// BPAY AU only
		if( $c->country_id==1 && $job_details[0]['display_bpay']==1 ){
			
			// BPAY logo	
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'images/bpay/bpay_does_not_accept_credit_card.jpg',null,null,60);
			
			// set font 
			$pdf->SetFont('Helvetica','',11);
			$pdf->SetTextColor(24, 49, 104); // blue
			
			// Biller Code
			$bpay_x = $x_pos+38;
			$bpay_y = $cursor_y+4;
			$pdf->SetXY($bpay_x,$bpay_y);	
			$biller_code = '264291';
			$pdf->Cell(15,5,$biller_code, 0, 0, 'R');
			
			// Ref Code
			$pdf->SetXY($bpay_x-15,$bpay_y+4);
			//$ref_code = str_pad($job_id, 12, "0", STR_PAD_LEFT);
			//$check_digit = getCheckDigit($job_id);
			//$bpay_ref_code = "{$job_id}{$check_digit}";	
			$pdf->Cell(30,5,$bpay_ref_code, 0, 0, 'R');
			
			$pdf->SetTextColor(0, 0, 0);
			
			$pdf->SetXY($x_pos+62,$cursor_y);
			
			//$x_pos += 62;
			
		}



		// QR code
		$qr_code_path = $_SERVER['DOCUMENT_ROOT'] . 'phpqrcode/temp/invoice_'.$bpay_ref_code.'_qr_code.png';

		// generate QR code
		# include library
		//include($_SERVER['DOCUMENT_ROOT'].'phpqrcode/qrlib.php');

		// $qr_code = $this->functions_model->generate_qr_code($bpay_ref_code,$job_details[0]['property_id'],number_format($grand_total, 2),number_format($gst, 2),$job_details[0]['date'],$country_id);
		// QRcode::png($qr_code['data'], $qr_code['path'],'L',2);
		$qr_code = "";
			

		// $pdf->Image($qr_code_path);


		// unlink($qr_code_path);
		$pdf->SetFont('Arial','',6.5);
		$pdf->Cell(24,2,'getpaidfaster.com.au',0,0); 
		$pdf->SetFont('Arial','',11);
		$x_pos = $pdf->getX();

		// Direct Deposit Details
		$pdf->SetXY($x_pos ,$cursor_y);
		$pdf->SetFont('Arial','',10);
			
		$c_bank = $c->bank;	
		$c_ac_name = $c->ac_name;
		$c_ac_number = $c->ac_number;
			
		if($c->country_id!=2){
			
		$c_bsb = $c->bsb;	
		$pdf->MultiCell(185,5,"Direct Deposit Details:
		Name: {$c_ac_name}
		Bank: {$c_bank} 
		BSB: {$c_bsb}
		Account #: {$c_ac_number}
		");

		}else{
			
		$pdf->MultiCell(185,5,"Direct Deposit Details:
		Name: {$c_ac_name}
		Bank: {$c_bank} 
		Account #: {$c_ac_number}
		");

		}

		// Reference No.
		$pdf->SetX($x_pos);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(22,5,'Reference #: ');
		$pdf->SetTextColor(255, 0, 0); // red
		$pdf->Cell(11,5,$bpay_ref_code,0,1);
		$pdf->SetTextColor(0, 0, 0); // clear red
		$pdf->SetFont('Arial','',10);

		$pdf->SetX($x_pos);
		$pdf->Cell(11,5,'Term:');
		$pdf->SetFont('Arial','U',10);
		$pdf->Cell(30,5,'NET 30 Days');	


		$pdf->SetFont('Arial','',10);


		#$pdf->SetY($pdf->GetY() - 28);

		/*
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(26,5,'Banking Details:');


		$c_bank = $c->bank;
		$c_bsb = $c->bsb;	
		$c_ac_name = $c->ac_name;
		$c_ac_number = $c->ac_number;

		if($c->country_id!=2){
			$pdf->SetFont('Arial','',9);
			$pdf->Cell(20,5,$c_bank);
			$pdf->SetFont('Arial','B',9);
			$pdf->Cell(9,5,'BSB:');
			$pdf->SetFont('Arial','',9);
			$pdf->Cell(17,5,$c_bsb);
			$pdf->SetFont('Arial','B',9);
			$pdf->Cell(21,5,'A/C Number:');
			$pdf->SetFont('Arial','',9);
			$pdf->Cell(18,5,$c_ac_number);
			$pdf->SetFont('Arial','B',9);
			$pdf->Cell(15,5,'A/C Name:');
			$pdf->SetFont('Arial','',9);
			$pdf->Cell(40,5,': ' . $c_ac_name);	
		}else{
			$pdf->SetFont('Arial','',9);
			$pdf->Cell(20,5,$c_bank);	
			$pdf->SetFont('Arial','B',9);
			$pdf->Cell(21,5,'A/C Number:');
			$pdf->SetFont('Arial','',9);
			$pdf->Cell(40,5,$c_ac_number);
			$pdf->SetFont('Arial','B',9);
			$pdf->Cell(15,5,'A/C Name:');
			$pdf->SetFont('Arial','',9);
			$pdf->Cell(40,5,': ' . $c_ac_name);
		}
		*/



		$pdf->SetFont('Arial','',10);

		$pdf->Ln(10);

		$curry = $pdf->GetY();
		$currx = $pdf->GetX();
		$pdf->SetLineWidth(0.4);
		$pdf->Line($currx, $curry, $currx + 190, $curry);
		$pdf->Ln(3);


		# State of Compliance
		$pdf->SetFont('Arial','B',14);

		 $pdf->Cell(0,5,'Statement of Compliance',0,1,'C');
		 $pdf->Ln(4);
		 
		//$pdf->Cell(0,15,'',0,1,'C');




		if($num_appliances > 0)
		{
		    $pdf->SetFont('Arial','B',11);

		    $pdf->Cell(45,5,"Appliance Summary:");
		    $pdf->Ln(10);


		    $pdf->Cell(8, 5, "#");
		    $pdf->Cell(20, 5, "Type");
		    $pdf->Cell(36, 5, "Appliance");
		    $pdf->Cell(36, 5, "Location");
		    $pdf->Cell(22, 5, "Pass/Fail");
		    $pdf->Cell(40, 5, "Reason");
		    $pdf->Cell(65, 5, "Comments");
		    $pdf->Ln(9);

		    $pdf->SetFont('Arial','',10);

		    for($x = 0; $x < $num_appliances; $x++)
		    {

		        $pdf->Cell(8, 2, $x + 1);
		        $pdf->Cell(20, 2, $appliance_details[$x]['alarm_type']);
		        $pdf->Cell(36, 2, $appliance_details[$x]['make']);
		        $pdf->Cell(36, 2, $appliance_details[$x]['ts_location']);
		        $pdf->Cell(22, 2, ($appliance_details[$x]['pass'] ? "Pass" : "Fail"));
		        $pdf->Cell(40, 2, $appliance_details[$x]['alarm_reason']);
		        $pdf->Cell(65, 2, $appliance_details[$x]['ts_comments']);
		        $pdf->Ln(6);

		    }

		    $pdf->Ln(5);

		    $pdf->SetFont('Arial','B',11);
		    $pdf->Cell(25, 5, "Retest Date:");
		    $pdf->Cell(15, 5, $job_details[0]['retest_date']);

		    $pdf->Ln(15);

		    $pdf->SetFont('Arial','',9);
		    $pdf->MultiCell(185,5,'All Appliances located within the property as detailed above are compliant with current legislation and Australian Standards. Appliances and leads are tested as per Manufacturers recommendations & the NSW Test and Tag requirements.');
		    $pdf->Ln(10);
		}

		// if bundle, get bundle services id
		$ajt_serv_sql = $this->job_functions_model->getService($job_details[0]['jservice']);	
		$ajt_serv = $ajt_serv_sql->row();


		// bundle
		if($ajt_serv->bundle==1){	
			 $bs_sql = $this->db->query("SELECT *
				FROM `alarm_job_type`
				WHERE `id` IN({$ajt_serv->bundle_ids})
			");
		// not bundle
		}else{
			$bs_sql = $this->db->query("SELECT *
				FROM `alarm_job_type`
				WHERE `id` = {$job_details[0]['jservice']}
			");
		}

		/*
		$bs_sql = mysql_query("
				SELECT *
				FROM `alarm_job_type`
				WHERE `id` = {$job_details[0]['jservice']}
				AND `active` = 1
			");
			*/

		// loop

		$bs_exec = $bs_sql->result_array();
		foreach ($bs_exec as $bs) {

			// smoke alarms
			if( $bs['id'] == 2 || $bs['id'] == 12 ){
				/*
				$pdf->Ln(2);
					$pdf->SetDrawColor(190,190,190);
					$pdf->SetLineWidth(0.05);
					$pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

					$pdf->Ln(6);
				*/

				$pdf->SetFont('Arial','B',11);

				$pdf->Cell(45,5,"{$bs['type']} Summary:");
				$pdf->Ln(10);

				$hw_Position = 35;
				$hw_Power = 18;
				$hw_Type = 30;
				$hw_Make = 27;
				$hw_Model = 25;
				$hw_Expiry = 14;
				$hw_dB = 25;
				
				$pdf->Cell($hw_Position,5,"Position");
				$pdf->Cell($hw_Power,5,"Power");
				$pdf->Cell($hw_Type,5,"Type");
				$pdf->Cell($hw_Make,5,"Make");
				$pdf->Cell($hw_Model,5,"Model");
				$pdf->Cell($hw_Expiry,5,"Expiry");
				$pdf->Cell($hw_dB,5,"dB");		

				$pdf->Ln(9);

				$sa_font_size = 9;
				$pdf->SetFont('Arial','',$sa_font_size);

				
				$jalarms_sql = $this->db->query("SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason  
					FROM alarm a 
						LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
						LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
						LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
					WHERE a.job_id = '" . $job_id . "'
					ORDER BY a.`ts_discarded` ASC, a.alarm_id ASC
				");	

				$temp_alarm_flag = 0;	
				$jalarms_exec = $jalarms_sql->result_array();
				foreach ($jalarms_exec as $jalarms) {	
					// if reason: temporary alarm
					if( $jalarms['alarm_reason_id']==31 ){
						$temp_alarm_flag = 1;
					}
					// red italic - start
					if($jalarms['ts_discarded']==1){
						$pdf->SetTextColor(255, 0, 0);
						$pdf->SetFont('Arial','I',$sa_font_size);
					}
					$pdf->Cell($hw_Position,5,$jalarms['ts_position']);
					$pdf->Cell($hw_Power,5,$jalarms['alarm_pwr']);
					$pdf->Cell($hw_Type,5,$jalarms['alarm_type']);
					$pdf->Cell($hw_Make,5,$jalarms['make']);
					$pdf->Cell($hw_Model,5,$jalarms['model']);
					$pdf->Cell($hw_Expiry,5,$jalarms['expiry']);
					
					// if techsheet "Required for Compliance" = 0/No
					$append_asterisk = '';
					if( $jalarms['ts_required_compliance'] == 0 ){
						$append_asterisk = '  *';
					}
					
					if($jalarms['ts_discarded']==1){
						$adr_sql =  $this->db->query("SELECT * 
							FROM `alarm_discarded_reason`
							WHERE `active` = 1
							AND `id` = {$jalarms['ts_discarded_reason']}
						");

						$adr = $adr_sql->row();
						$pdf->Cell($hw_dB,5,'Removed - '.$adr->reason.$append_asterisk);
					}else{
						$pdf->Cell($hw_dB,5,$jalarms['ts_db_rating'].$append_asterisk);
					}

					// red italic - end
					if($jalarms['ts_discarded']==1){
						$pdf->SetFont('Arial','',$sa_font_size);
						$pdf->SetTextColor(0, 0, 0);
					}					
					$pdf->Ln();
				}

				$pdf->Ln(4);
					
				$pdf->SetFont('Arial','',10);
				//$pdf->MultiCell(185,5,'All Smoke Alarms Located within the Property as detailed above are Compliant with Current Legislation and Australian Standards. Smoke Alarms are installed as per Manufacturers Recommendations & the Building Code of Australia.'); 
				//$pdf->MultiCell(185,5,$_SERVER['DOCUMENT_ROOT'].'documents/cert_corner_img.png'); 
				

				switch($c->country_id){
					case 1:
						$country_text = 'Australian';
					break;
					case 2:
						$country_text = "New Zealand";
					break;
					case 3:
						$country_text = "Canadian";
					break;
					case 4:
						$country_text = "British";
					break;
					case 5:
						$country_text = "American";
					break;
					default:
						$country_text = 'Australian';
				}
				
				
				if( $job_details[0]['state'] == 'QLD' && $temp_alarm_flag==1 ){ // if QLD and temporary alarm
					$pdf->SetTextColor(255, 0, 0);
					$pdf->SetFont('Arial','I',10);
					$pdf->MultiCell(185,5,'Smoke alarms at the above property are NOT compliant with AS3786 (2014) and will need to be replaced when compliant smoke alarms become available. The property has working smoke alarms and the property is safe however they are not compliant, and SATS will revisit the property to make it compliant as soon as compliant alarms become available.'); 
					$pdf->SetFont('Arial','',10);
					$pdf->SetTextColor(0, 0, 0);
				}else{
					
					if( $job_details[0]['country_id']==1 ){ // AU
						$pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and been installed in accordance with '.$country_text.' Standard AS 3786 (2014) Smoke Alarms, Building Code of '.$c->country.', Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations.'); 
					}else if( $job_details[0]['country_id']==2 ){ // NZ
						$pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations.'); 
					}  						
					
				}
				
				
				$pdf->Ln(3);
				//$pdf->SetFont('Arial','',8);
				
				$pdf->MultiCell(185,5,'* Not required for compliance'); 
				
				
			// safety switch
			}else if($bs['id'] == 5){
			
				$ssp_sql = $this->db->query("SELECT `ts_safety_switch`, `ts_safety_switch_reason`, `ss_quantity`
					FROM `jobs`
					WHERE `id` = {$job_details[0]['id']}
				");
			
				$ssp = $ssp_sql->row();
				
				//if($ssp->ts_safety_switch==1){
				
					$pdf->Ln(2);
					$pdf->SetDrawColor(190,190,190);
					$pdf->SetLineWidth(0.05);
					$pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

					$pdf->Ln(6);

					$pdf->SetFont('Arial','B',11);

					$pdf->Cell(45,5,"{$bs['type']} Summary:");
					$pdf->Ln(10);
					
					// check if at least 1 SS failed
					$chk_ss_sql = $this->db->query("SELECT *
						FROM `safety_switch`
						WHERE `job_id` ={$job_details[0]['id']}
						AND `test` = 0
					");
					
					$num_ss_fail = $chk_ss_sql->num_rows();
					
					if( $num_ss_fail > 0 ){
						
						
					
						//$pdf->Cell(30,5,"{$service} Headings");
						
						$pdf->Cell(30,5,"Make");
						$pdf->Cell(30,5,"Model");
						
						$pdf->Cell(30,5,"Test Date");
						$pdf->Cell(30,5,"Test Result");
						
						$pdf->Ln(9);
						
						
						
						
						
						$pdf->SetFont('Arial','',10);

						 //$pdf->Cell(30,5,"{$service} Data");
						 $ss_sql = $this->db->query("
							SELECT *
							FROM `safety_switch`
							WHERE `job_id` ={$job_details[0]['id']}
							ORDER BY `make`
						");

						$ss_sql_exec = $ss_sql->result_array();
						foreach ($variable as $ss) {
							
							
							
							$pdf->Cell(30,5,$ss['make']);
							$pdf->Cell(30,5,$ss['model']);
							$pdf->Cell(30,5,$job_details[0]['date']);	
							if($ss['test']==1||$ss['test']==""){
								$pdf->Cell(30,5,'Pass');
							}else if($ss['test']==0){
								$pdf->SetTextColor(255, 0, 0); // red
								$pdf->Cell(30,5,'Fail');
								$pdf->SetTextColor(0, 0, 0); 
							}else if($ss['test']==2){
								$pdf->Cell(30,5,'No Power to Property at time of testing');
							}
							
							$pdf->Ln();
						}
						
						
						

					
						
						
						// Fusebox Viewed
						$pdf->Ln(4);
						$pdf->SetFont('Arial','B',11);			
						$pdf->Cell(40,5,"Fusebox Viewed:");
						$pdf->SetFont('Arial','',10);
						$pdf->Cell(15,5,($ssp->ts_safety_switch==2)?'Yes':'No');
						
						// Fusebox Viewed - Yes
						if($ssp->ts_safety_switch==2){
						
							// Safety Switch Quantity						
							$pdf->SetFont('Arial','B',11);			
							$pdf->Cell(60,5,"Number of Safety Switches:");
							$pdf->SetFont('Arial','',10);
							$pdf->Cell(30,5,$ssp->ss_quantity);
						
						// Fusebox Viewed - No
						}else if($ssp->ts_safety_switch==1){
						
						
							// reason				
							$pdf->SetFont('Arial','B',11);			
							$pdf->Cell(18,5,"Reason:");
							$pdf->SetFont('Arial','',10);
							switch($ssp->ts_safety_switch_reason){
								case 0:
									$ssp_reason = 'No Switch';
								break;
								case 1:
									$ssp_reason = 'Unable to Locate';
								break;
								case 2:
									$ssp_reason = 'Unable to Access';
								break;
							}
							$pdf->Cell(30,5,$ssp_reason);
						
						}
						
						
						

					}
					
					
					

					//$pdf->Ln(11);
					$pdf->Ln(4);
						
					$pdf->SetFont('Arial','',10);
					//$pdf->MultiCell(185,5,"{$service} Compliance Statement");
					//$pdf->MultiCell(185,5,'All Smoke Alarms Located within the Property as detailed above are Compliant with Current Legislation and Australian Standards. Smoke Alarms are installed as per Manufacturers Recommendations & the Building Code of Australia.'); 
					
					$ss_sql = $this->db->query("
						SELECT *
						FROM `safety_switch`
						WHERE `job_id` ={$job_details[0]['id']}
						ORDER BY `make`
					");
							
					if($ssp->ss_quantity==0){
						$pdf->SetTextColor(255,0,0);
						$pdf->MultiCell(185,5,'No Safety Switches Present. We strongly recommend a Safety Switch be installed to protect the occupants.'); 
						$pdf->SetTextColor(0,0,0);
					}else{				

						// check if at least 1 has no power
						$chk_ss_no_pwr_sql = $this->db->query("
							SELECT *
							FROM `safety_switch`
							WHERE `job_id` ={$job_details[0]['id']}
							AND `test` = 2
						");
						
						$num_no_power = $chk_ss_no_pwr_sql->num_rows();
					
						if( $num_no_power > 0 ){
							
							$pdf->SetFont('Arial','',10);
							$pdf->MultiCell(185,5,$ss_sql->num_rows().' Safety Switches Present'); 					
							$pdf->Ln(4);					
							$pdf->MultiCell(185,5,"The Safety Switches at this property were unable to be tested due to their being no power at the property at the time of inspection, and power is required to perform a mechanical test on the Safety Switches.");
						
						}else if( $num_ss_fail > 0 ){ // if at least 1 SS test failed	
						
							$pdf->Ln(4);					
							$pdf->MultiCell(185,5,"All Safety Switches have been Mechanically Tested to assess they are in working order. No test has been performed to determine the speed at which the device activated.");
							$pdf->Ln(4);
							$pdf->SetTextColor(255, 0, 0); // red
							$pdf->MultiCell(185,5,"One of the Safety Switches at this property have failed and will require replacing by a Licensed Electrician. SATS do not install Safety Switches, however we do test them when they are present.");
							$pdf->SetTextColor(0, 0, 0);
							
						}else{					

							$pdf->SetFont('Arial','',10);
							$pdf->MultiCell(185,5,$ss_sql->num_rows().' Safety Switches tested'); 
							$pdf->Ln(4);
							$pdf->MultiCell(185,5,"All Safety Switches have been Mechanically Tested and pass a basic mechanical test, to assess they are in working order. No test has been performed to determine the speed at which the device activated.");
						
						}
						
					}
					
					
					
					
					
					$pdf->Ln(3);
					$pdf->SetFont('Arial','',8);
				
				//}
					
				
			// corded windows
			}else if($bs['id'] == 6){

				$pdf->SetFont('Arial','B',11);

				$pdf->Cell(45,5,"{$bs['type']} Summary:");
				$pdf->Ln(10);

				$num_windows_total = 0;

					
				foreach ($cw_sql as $cw) {
					$num_windows_total += $cw['num_of_windows'];
				}
				
				$pdf->SetFont('Arial','',10);
				$pdf->MultiCell(185,5,$num_windows_total.' Windows tested and Compliant'); 

				$pdf->Ln(4);
					
				$pdf->SetFont('Arial','',10);
				//$pdf->MultiCell(185,5,'All Corded Windows within the Property as detailed above are Compliant with Current Legislation and '.$country_text.' Standards. The Required Clips and Tags have been installed to ensure proper compliance with Current Legislation.');
				$pdf->MultiCell(185,5,'All Corded Windows within the Property are Compliant with Current Legislation and '.$country_text.' Standards. The Required Clips and Tags have been installed to ensure proper compliance with Current Legislation. Further data is available on the agency portal'); 		
				$pdf->Ln(3);
				$pdf->SetFont('Arial','',8);
				
			// poop barriers	
			}else if($bs['id'] == 7){
				$pdf->Ln(2);
					$pdf->SetDrawColor(190,190,190);
					$pdf->SetLineWidth(0.05);
					$pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

					$pdf->Ln(6);

				$pdf->SetFont('Arial','B',11);

				$pdf->Cell(45,5,"{$bs['type']} Summary:");
				$pdf->Ln(10);

				
				$pdf->Cell(30,5,"Reading");
				$pdf->Cell(30,5,"Location");

				$pdf->Ln(9);
				
			
				$pdf->SetFont('Arial','',10);
				$wm_sql = $this->functions_model->getWaterMeter($job_details[0]['id']);

				$wm_sql_exec = $wm_sql->result_array();
				foreach ($wm_sql_exec as $wm) {
					
					$pdf->Cell(30,5,$wm['reading']);
					$pdf->Cell(30,5,$wm['location']);
					$pdf->Ln();
				}

				$pdf->Ln(4);
					
				$pdf->SetFont('Arial','',10);
				//$pdf->MultiCell(185,5,"{$service} Compliance Statement");
				//$pdf->MultiCell(185,5,'All Smoke Alarms Located within the Property as detailed above are Compliant with Current Legislation and Australian Standards. Smoke Alarms are installed as per Manufacturers Recommendations & the Building Code of Australia.'); 
				//$pdf->Ln(3);
				//$pdf->SetFont('Arial','',8);
				
			}

		}


		$pdf->Ln(2);
		$pdf->SetFont('Arial','',10);	


		// if service type is IC dont show, only show for non-IC services
		$ic_service = $this->system_model->getICService();

		if(in_array($job_details[0]['jservice'], $ic_service)){
			$ic_check = 1;
		}else{
			$ic_check = 0;
		}

		if( $ic_check==0 ){

			// do not show on insite realty agency ID: 5222
			if( $job_details[0]['state'] == 'QLD' && $job_details[0]['qld_new_leg_alarm_num']>0 && $property_details['agency_id'] != 5222 ){
				$pdf->SetTextColor(0, 0, 204);
				//$pdf->MultiCell(185,5,'We have provided a quote, on the agency portal, to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details[0]['date']).'+60 days')).'. To go ahead with this quote please contact SATS on '.$c->agent_number.' or '.$c->outgoing_email.'.'); 
				
				// QUOTE
				$quote_qty = $job_details[0]['qld_new_leg_alarm_num'];
				$price_240vrf = $this->system_model->get240vRfAgencyAlarm($property_details['agency_id']);
				$quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
				$quote_total = $quote_price*$quote_qty;
				$pdf->MultiCell(185,5,'We have provided a quote for $'.$quote_total.' to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details[0]['date']).'+90 days')).' and available on the agency portal. To go ahead with this quote please contact SATS on '.$c->agent_number.' or '.$c->outgoing_email); 
				$pdf->SetTextColor(0, 0, 0);
			}

		}	


		$bpay_ref_code = "{$job_id}{$check_digit}";
		$output_type = $this->input->get('output_type');
		$output_type2 = ($output_type!='')?$output_type:'I'; 

		$pdf->Output('invoice' . $bpay_ref_code . '.pdf', $output_type2);
    }

    public function view_invoice() {
    	// AUTO - UPDATE INVOICE DETAILS
    	$pdf = new FPDF();
		// $crm_pdf = new Sats_Crm_Class;
		// $crm_pdf->updateInvoiceDetails($job_id);
		$this->system_model->updateInvoiceDetails($job_id);
		$job_id = $this->input->get('job_id');
		$job_details = $this->system_model->getJobDetails2($job_id);
		$property_details = $this->functions_model->getPropertyAgentDetails($job_details[0]['property_id']);
		
		#instantiate only if required
		if(!isset($pdf)) {
			
			//$pdf=new FPDF('P','mm','A4');
			//include('fpdf_override.php');
			$pdf=new jPDF('P','mm','A4');
			$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
			$pdf->setCountryData($job_details[0]['country_id']);
		}

		$pdf->SetTopMargin(40);
		$pdf->SetAutoPageBreak(true,50);
		$pdf->AddPage();
		
		if( $job_details[0]['show_as_paid']==1 || ( is_numeric($job_details[0]['invoice_balance']) && $job_details[0]['invoice_balance'] == 0 ) ){
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'images/paid.png',55,180);
		}

		$check_digit = $this->customlib->getCheckDigit(trim($job_id));
		$bpay_ref_code = "{$job_id}{$check_digit}";

	    $pdf->SetFont('Arial','',11); 

		
		$pdf->SetX(30);
	    
		$pdf->Cell(70,5,'Invoice Date:   ' . $job_details[0]['date']); 
	    
	    $pdf->SetFont('Arial','B',14);
	    
	    if(isset($job_details[0]['tmh_id']))
	    {
	    	$pdf->Cell(100,5,'Tax Invoice    #' . str_pad($job_details[0]['tmh_id'] . ' TMH', 6, "0", STR_PAD_LEFT),0,1,'C');
	    }
	    else
	    {
	    	$pdf->Cell(100,5,'Tax Invoice    #' . $bpay_ref_code,0,1,'C');
	    }

	    $pdf->SetFont('Arial','',11);
	    
	    $pdf->Ln(5);
	   
	    # Agent Details
		$curry = $pdf->GetY();
		$currx = $pdf->GetX();
		
		// space needed to fit envelope
		$pdf->Cell(20,10,'');
		
		# Hack for LJ Hooker Tamworth - display Landlord in different spot for them
		if( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' ){
			$landlord_txt = "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}";
			$landlord_txt2 = "\n\nLANDLORD: {$landlord_txt}";
		}else{
			$landlord_txt = "LANDLORD";
			$landlord_txt2 = "";
		}
		
		// compass index number
		if( $property_details['compass_index_num'] != '' ){
			$compass_index_num = "\nINDEX NO.: {$property_details['compass_index_num']}";	
		}else{
			$compass_index_num = "\n";
		}
		
		$agency_address_txt = htmlspecialchars_decode("{$property_details['a_address_1']} {$property_details['a_address_2']}\n{$property_details['a_address_3']} {$property_details['a_state']} {$property_details['a_postcode']}");
		$property_address_txt = htmlspecialchars_decode("{$property_details['address_1']} {$property_details['address_2']}\n{$property_details['address_3']} {$property_details['state']} {$property_details['postcode']}");
		$workorder_txt = ($job_details[0]['work_order'])?"\nWORK ORDER: {$job_details[0]['work_order']}":"";
		
		// exclude tech 'unassigned(id:1)'
		$date_of_visit = ( $job_details[0]['tech_id'] > 1 )?$job_details[0]['date']:'N/A';

		# Hack for LJ Hooker Tamworth - display Landlord in different spot for them
		if($property_details['agency_id'] == 1348){
		$pdf->MultiCell(100, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\nDATE OF VISIT: " . $date_of_visit);
			$box1_h = $pdf->GetY();
			$pdf->SetY($curry);
			$pdf->SetX(124);
			$pdf->MultiCell(100, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" );
			$box2_h = $pdf->GetY();	
			$pdf->Ln(6);
		}else if ($property_details['agency_id'] == 3079){	
			$pdf->MultiCell(100, 5, "ATTN: {$landlord_txt}\n" . "\n\nDATE OF VISIT: " . $date_of_visit);
			$box1_h = $pdf->GetY();
			$pdf->SetY($curry);
			$pdf->SetX(124);
			$pdf->MultiCell(100, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" );
			$box2_h = $pdf->GetY();	
		}else{	
			$pdf->MultiCell(100, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\nDATE OF VISIT: " . $date_of_visit);
			$box1_h = $pdf->GetY();
			$pdf->SetY($curry);
			$pdf->SetX(124);
			$pdf->MultiCell(100, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" );
			$box2_h = $pdf->GetY();	
		}
		
		if($box1_h>$box2_h){
			$pdf->SetY($box1_h);
		}else{
			$pdf->SetY($box2_h);
		}
		
	    $pdf->Ln();
		
		$curry = $pdf->GetY();
		$currx = $pdf->GetX();
		$pdf->SetLineWidth(0.4);
		$pdf->Line($currx, $curry, $currx + 190, $curry);
		$pdf->Ln(1.5);
		
	    $pdf->Cell(15,5,"Qty");
		$pdf->Cell(45,5,"Item");
		$pdf->Cell(80,5,"Description");
		$pdf->Cell(25,5,"Unit Price");
		$pdf->Cell(25,5,"Total Amount");
		$pdf->Ln();
		
		$pdf->Cell(141,5,"");
		$pdf->Cell(27.5,5,"Inc. GST");
		$pdf->Cell(25,5,"Inc. GST");
		$pdf->Ln(6);
		
		$curry = $pdf->GetY();
		$currx = $pdf->GetX();
		$pdf->SetLineWidth(0.4);
		$pdf->Line($currx, $curry, $currx + 190, $curry);
		$pdf->Ln(5);
		
		// get service
		$os_sql = $this->job_functions_model->getService($job_details[0]['jservice']);	
		$os = $os_sql->row();
		
		# Add Job Type
		$pdf->Cell(15,5,"1", 0, 0, 'C');
		$pdf->Cell(45,5,$job_details[0]['job_type']);
		$pdf->Cell(80,5,$os->type);
		$pdf->Cell(19,5,"$".number_format($job_details[0]['job_price'], 2), 0, 0, 'R');
		$pdf->Cell(31,5,"$".number_format($job_details[0]['job_price'], 2), 0, 0, 'R');
		$pdf->Ln();
		
		$grand_total = $job_details[0]['job_price'];
		
		// installed alarm
		for($x = 0; $x < $num_alarms; $x++)
		{
			if($alarm_details[$x]['new'] == 1)
			{
				$pdf->SetFont('Arial','',11);
				$pdf->Cell(15,5,"1", 0, 0, 'C');
				$pdf->Cell(45,5,$alarm_details[$x]['alarm_pwr']);
				$pdf->Cell(80,5,"Supply & Install " . $alarm_details[$x]['alarm_type'] . " Smoke Alarm");
				$pdf->Cell(19,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
				$pdf->Cell(31,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
				$pdf->Ln();
				
				$pdf->SetFont('Arial','I',11);
				$pdf->Cell(15,5,"", 0, 0, 'C');
				$pdf->Cell(45,5,"");
				$pdf->SetTextColor(255, 0, 0); // red
				$pdf->Cell(80,5,"Reason: " . $alarm_details[$x]['alarm_reason']);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->Cell(19,5,"", 0, 0, 'R');
				$pdf->Cell(31,5,"", 0, 0, 'R');
				$pdf->Ln();
				
				$grand_total += $alarm_details[$x]['alarm_price'];
			}
		}
		
		// removed alarm
		for($x = 0; $x < $numDelAlarm; $x++)
		{
			$pdf->SetFont('Arial','',11);
			$pdf->Cell(15,5,"1", 0, 0, 'C');
			$pdf->Cell(45,5,$delAlarm[$x]['alarm_pwr']);
			$pdf->Cell(80,5,"Remove ".$delAlarm[$x]['alarm_type'] . " Smoke Alarm");
			$pdf->Cell(19,5,"$" . $delAlarm[$x]['alarm_price'], 0, 0, 'R');
			$pdf->Cell(31,5,"$" . $delAlarm[$x]['alarm_price'], 0, 0, 'R');
			$pdf->Ln();
			
			$pdf->SetFont('Arial','I',11);
			$pdf->Cell(15,5,"", 0, 0, 'C');
			$pdf->Cell(45,5,"");
			$pdf->Cell(80,5,"Reason: " . $delAlarm[$x]['reason']);
			$pdf->Cell(19,5,"", 0, 0, 'R');
			$pdf->Cell(31,5,"", 0, 0, 'R');
			$pdf->Ln();
			
			$grand_total += $delAlarm[$x]['alarm_price'];
		}
		
		// surcharge
		$sc_sql = $this->db->query("SELECT *, m.`name` AS m_name 
						FROM `agency_maintenance` AS am
						LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
						WHERE am.`agency_id` = {$property_details['agency_id']}
						AND am.`maintenance_id` > 0
					");

		$sc = $sc_sql->row();

		if( $grand_total!=0 && $sc->surcharge==1 ){
			
			$pdf->SetFont('Arial','',11);
			$pdf->Cell(15,5,"1", 0, 0, 'C');
			$pdf->Cell(45,5,$sc->m_name);
			$surcharge_txt = ($sc->display_surcharge==1)?$sc->surcharge_msg:'';
			$pdf->Cell(80,5,$surcharge_txt);
			$pdf->Cell(19,5,"$".number_format($sc->price, 2), 0, 0, 'R');
			$pdf->Cell(31,5,"$".number_format($sc->price, 2), 0, 0, 'R');
			$pdf->Ln();
			
			$grand_total += $sc->price;
			
		}
		
		// CREDITS

		$credit_sql = $this->db->query("SELECT *
						FROM `invoice_credits` AS ic 
						WHERE ic.`job_id` = {$job_id}
					");

		$credit_exec = $credit_sql->result_array();

		foreach ($credit_exec as $credit) {
			
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(15,5,"1", 0, 0, '');
			$pdf->Cell(45,5,'Credit');
			$pdf->SetFont('Arial','I',10);
			$pdf->SetTextColor(255, 0, 0); // red
			$pdf->Cell(80,5,'Reason: '.$this->system_model->getInvoiceCreditReason($credit['credit_reason']));
			$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('Arial','',10);
			$pdf->Cell(19,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
			$pdf->Cell(31,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
			$pdf->Ln();
			
			$grand_total -= $credit['credit_paid'];
			
		}
		 
		$pdf->Ln(5);
		
		// get country
		$c_sql = $this->db->query("SELECT *
							FROM `agency` AS a
							LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
							WHERE a.`agency_id` = {$property_details['agency_id']}
						"); 
		$c = $c_sql->row();
		
		// gst
		if($c->country_id==1){
			$gst = $grand_total / 11;
		}else if($c->country_id==2){
			$gst = ($grand_total*3) / 23;
		}	
		
		$curry = $pdf->GetY();
		$currx = $pdf->GetX();
		$pdf->SetLineWidth(0.4);
		$pdf->Line($currx, $curry, $currx + 190, $curry);
		$pdf->Ln(5);
		
		// get cursor position
		$cursor_y = $pdf->GetY();
		
		$pdf->SetFont('Arial','',11);
		$pdf->Cell(140,5,"", 0, 0, 'C');
		//$text = 'Sale Amount';
		$pdf->Cell(19,5,$text, 0, 0, 'R');
		$pdf->Cell(31,5,"$" . number_format($grand_total - ($gst), 2), 0, 0, 'R');
		$pdf->Ln();

		$pdf->Cell(140,5,"", 0, 0, 'C');
		//$text = 'GST';
		$pdf->Cell(19,5,$text, 0, 0, 'R');
		$pdf->Cell(31,5,"$" . number_format($gst, 2), 0, 0, 'R');	
		$pdf->Ln();

		
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(140,5,"", 0, 0, 'C');
		//$text = 'Invoice Total';
		$pdf->Cell(25,5,$text, 0, 0, 'R');
		$pdf->Cell(25,5,"$" . number_format($grand_total, 2), 'B', 0, 'R');
		$pdf->Ln();
		
		
		// balance
		$pdf->SetFont('Arial','I',10);
		$pdf->Cell(140,10,"", 0, 0, 'C');
		$text = 'Amount Owing';
		$pdf->Cell(25,10,$text, 0, 0, 'R');
		$pdf->SetFont('Arial','B',12);
		$inv_balance = ( is_numeric($job_details[0]['invoice_balance']) )?$job_details[0]['invoice_balance']:$grand_total;
		$pdf->Cell(25,10,'$'.number_format($inv_balance, 2), 0, 0, 'R');
		$pdf->Ln();
		
			
		
		$x_pos = 10;
		$pdf->SetXY($x_pos,(($cursor_y)-1.3));	
		
		
		// BPAY AU only
		if( $c->country_id==1 && $job_details[0]['display_bpay']==1 ){
			
			// BPAY logo	
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'images/bpay/bpay_does_not_accept_credit_card.jpg',null,null,60);
			
			// set font 
			$pdf->SetFont('Helvetica','',11);
			$pdf->SetTextColor(24, 49, 104); // blue
			
			// Biller Code
			$bpay_x = $x_pos+38;
			$bpay_y = $cursor_y+4;
			$pdf->SetXY($bpay_x,$bpay_y);	
			$biller_code = '264291';
			$pdf->Cell(15,5,$biller_code, 0, 0, 'R');
			
			// Ref Code
			$pdf->SetXY($bpay_x-15,$bpay_y+4);
			//$ref_code = str_pad($job_id, 12, "0", STR_PAD_LEFT);	
			//$check_digit = getCheckDigit($job_id);
			//$bpay_ref_code = "{$job_id}{$check_digit}";
			$pdf->Cell(30,5,$bpay_ref_code, 0, 0, 'R');
			
			$pdf->SetTextColor(0, 0, 0);
			
			$pdf->SetXY($x_pos+62,$cursor_y);
			
			//$x_pos += 62;
			
		}

		// generate QR code
		$qr_code_path = $_SERVER['DOCUMENT_ROOT'] . 'phpqrcode/temp/invoice_'.$bpay_ref_code.'_qr_code.png';
		// $qr_code = generate_qr_code($bpay_ref_code,$job_details[0]['property_id'],number_format($grand_total, 2),number_format($gst, 2),$job_details[0]['date'],$country_id);
		// QRcode::png($qr_code['data'], $qr_code['path'],'L',2);
		$qr_code = "";

		// display QR code	
		// $pdf->Image($qr_code_path);
		// delete qr image
		// unlink($qr_code_path);
		$pdf->SetFont('Arial','',6.5);
		$pdf->Cell(24,2,'getpaidfaster.com.au',0,0); 
		
		$x_pos = $pdf->getX();

		// Direct Deposit Details
		$pdf->SetXY($x_pos,($cursor_y+1));
		$pdf->SetFont('Arial','',10);
		
		
		$c_bank = $c->bank;	
		$c_ac_name = $c->ac_name;
		$c_ac_number = $c->ac_number;
			
		if($c->country_id!=2){
			
		$c_bsb = $c->bsb;	
		$pdf->MultiCell(185,5,"Direct Deposit Details:
		Name: {$c_ac_name}
		Bank: {$c_bank} 
		BSB: {$c_bsb}
		Account #: {$c_ac_number}
		");

		}else{
			
		$pdf->MultiCell(185,5,"Direct Deposit Details:
		Name: {$c_ac_name}
		Bank: {$c_bank} 
		Account #: {$c_ac_number}
		");

		}	

		// Reference No.
		$pdf->SetX($x_pos);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(22,5,'Reference #: ');
		$pdf->SetTextColor(255, 0, 0); // red
		$pdf->Cell(11,5,$bpay_ref_code,0,1);
		$pdf->SetTextColor(0, 0, 0); // clear red
		$pdf->SetFont('Arial','',10);

		$pdf->SetX($x_pos);
		$pdf->Cell(11,5,'Term:');
		$pdf->SetFont('Arial','U',10);
		$pdf->Cell(30,5,'NET 30 Days');	
			    
		$pdf->SetFont('Arial','',11);
		
		
		$pdf->Ln(10);
		
		$pdf->Cell(160, 5, $job_details[0]['job_type'].' Includes:');
		
		
		// if bundle, get bundle services id
		$ajt_serv_sql = $this->job_functions_model->getService($job_details[0]['jservice']);
		$ajt_serv = $ajt_serv_sql->row();

		// if bundle
		if($ajt_serv->bundle==1){
		
			$bs_sql = $this->db->query("SELECT *
				FROM `alarm_job_type`
				WHERE `id` IN({$ajt_serv->bundle_ids})
				AND `active` = 1
			");
			$bs_sql_exec = $bs_sql->result_array();
			foreach ($bs_sql_exec as $bs) {
				$pdf->Ln(10);
				$pdf->Cell(160, 5, $bs['type'].': ');
				$this->getServiceIncludesDesc($pdf,$job_details[0]['job_type'],$bs['id'],$country_id);
			}

		// not bundle
		}else{
			$this->getServiceIncludesDesc($pdf,$job_details[0]['job_type'],$job_details[0]['jservice'],$country_id);
		}
		

		$curr_y = $pdf->GetY();	
		$pdf->SetY($curr_y+10);
		$pdf->SetFont('Arial','',10);

		switch($c->country_id){
			case 1:
				$country_text = 'Australian';
			break;
			case 2:
				$country_text = "New Zealand";
			break;
			case 3:
				$country_text = "Canadian";
			break;
			case 4:
				$country_text = "British";
			break;
			case 5:
				$country_text = "American";
			break;
			default:
				$country_text = 'Australian';
		}	

		// if service type is IC dont show, only show for non-IC services
		$ic_service = $this->system_model->getICService();

		if(in_array($job_details[0]['jservice'], $ic_service)){
			$ic_check = 1;
		}else{
			$ic_check = 0;
		}

		if( $ic_check==0 ){

			// do not show on insite realty agency ID: 5222
			if( $job_details[0]['state'] == 'QLD' && $job_details[0]['qld_new_leg_alarm_num']>0 && $property_details['agency_id'] != 5222 ){
				$pdf->SetTextColor(0, 0, 204);
				//$pdf->MultiCell(185,5,'We have provided a quote, on the agency portal, to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details[0]['date']).'+60 days')).'. To go ahead with this quote please contact SATS on '.$c->agent_number.' or '.$c->outgoing_email.'.'); 
				
				// QUOTE
				$quote_qty = $job_details[0]['qld_new_leg_alarm_num'];
				$price_240vrf = $crm_pdf->get240vRfAgencyAlarm($property_details['agency_id']);
				$quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
				$quote_total = $quote_price*$quote_qty;
				$pdf->MultiCell(185,5,'We have provided a quote for $'.$quote_total.' to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details[0]['date']).'+90 days')).' and available on the agency portal. To go ahead with this quote please contact SATS on '.$c->agent_number.' or '.$c->outgoing_email); 
				$pdf->SetTextColor(0, 0, 0);
			}

		}
    }

    public function getServiceIncludesDesc($pdf,$jt,$serv,$country_id){
        
        $country_id2 = ($country_id=="")? $this->config->item('country'):$country_id;
        
        # description
        if( ($jt=="Yearly Maintenance" || $jt=="Change of Tenancy" || $jt=="Once-off") && ( $serv==2 || $serv==12 ) ){
            
            $pdf->SetFont('Arial','',10);
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Surveying the Quantity and Location of Smoke Alarms');
            $pdf->Cell(160, 5, '* Inspecting Alarms for Secure Fitting');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Replacing Batteries in all Alarms where Replaceable');
            $pdf->Cell(160, 5, '* Cleaning Alarms with a Smoke Alarm Wipe');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Testing Alarms with the Manual Test Button');
            $pdf->Cell(160, 5, '* Verifying Expiry Dates on All Alarms');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Measure and Record Alarm Decibel Readings');
            $pdf->Cell(160, 5, '* Checking Alarms for Visual Indicators');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Checking Alarms meet '.$this->job_functions_model->getCountryText($country_id2).' Standards');
            $pdf->Cell(160, 5, '* The Recording of all Details in SATS Database');
            $pdf->SetFont('Arial','',11);

        
        }else if( $jt=="Fix or Replace" && ( $serv==2 || $serv==12 ) ){
        
            $pdf->SetFont('Arial','',10);
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Fix or Replace Problem Alarm');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Conduct Full Test on Alarm');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Record all Details in SATS Database');
            $pdf->SetFont('Arial','',11);
        
        }else if( $jt=="Lease Renewal" && ( $serv==2 || $serv==12 ) ){
        
            $pdf->SetFont('Arial','',10);
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Surveying the Quantity and Location of Smoke Alarms');
            $pdf->Cell(160, 5, '* Inspecting Alarms for Secure Fitting');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Replacing Batteries in all Alarms where Replaceable');
            $pdf->Cell(160, 5, '* Cleaning Alarms with a Smoke Alarm Wipe');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Testing Alarms with the Manual Test Button');
            $pdf->Cell(160, 5, '* Verifying Expiry Dates on All Alarms');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Measure and Record Alarm Decibel Readings');
            $pdf->Cell(160, 5, '* Checking Alarms for Visual Indicators');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Checking Alarms meet '.$this->job_functions_model->getCountryText($country_id2).' Standards');
            $pdf->Cell(160, 5, '* The Recording of all Details in SATS Database');
            $pdf->SetFont('Arial','',11);
        
        }else if($jt=="Yearly Maintenance"&&$serv==6){
        
            $pdf->SetFont('Arial','',10);
            $pdf->Ln();
            
            $pdf->Cell(108, 5, '* Verifying and Installing Clips on all Windows where Required');
            $pdf->Cell(160, 5, '* Inspecting all Window Coverings');
            $pdf->Ln();
            
            $pdf->Cell(108, 5, '* Verifying and Installing Tags on all Windows where Required');
            $pdf->Cell(160, 5, '* Surveying all Windows in the Property');
            $pdf->Ln();

            $pdf->Cell(108, 5, '* Verifying that all cord loops Meet Legislation');
            $pdf->Cell(160, 5, '* The Recording of all Details in SATS Database');
            $pdf->SetFont('Arial','',11);
        
        }else if($jt=="Fix or Replace"&&$serv==6){
        
            $pdf->SetFont('Arial','',10);
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Repair Problem Window Covering');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Conduct Full Survey of all Windows');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Record all Details in SATS Database');
            $pdf->SetFont('Arial','',11);

        
        }else if($serv==5){
        
            $pdf->SetFont('Arial','',10);
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Determine and Record Safety Switch Quantity');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Determine and Record Safety Switch Make');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Determine and Record Safety Switch Model');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Perform Mechanical test on all Safety Switches and Record results');
            $pdf->Ln();
        
        }
		$bpay_ref_code = "{$job_id}{$check_digit}";
		$output_type = $this->input->get('output_type');
		$output_type2 = ($output_type!='')?$output_type:'I'; 

		$pdf->Output('invoice' . $bpay_ref_code . '.pdf', $output_type2);

    }

    public function ajax_dha_precomp_email_agency_accounts($ajob_id = "", $apdf_type = "", $aqt_type = "") {

		$job_id = $ajob_id == "" ? $this->input->post('job_id') : $ajob_id;
		$pdf_type = $apdf_type == "" ? $this->input->post('pdf_type') : $apdf_type;
		$qt_type = $aqt_type == "" ? $this->input->post('qt_type') : $aqt_type;

		$country_id = $this->config->item('country');
		$invoice_only = 0;

		if($job_id!=''){
			
			unset($jemail);
			$jemail = array();

			$Query = $this->job_functions_model->getJobDetails($job_id, true);
			// $email_job_details = mysqlSingleRow($Query);
			$email_job_details = $this->functions_model->mysqlSingleRow($Query);

			$temp = explode("\n",trim($email_job_details['account_emails']));
			
			foreach($temp as $val){
				$val2 = preg_replace('/\s+/', '', $val);
				if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
					$jemail[] = $val2;
				}				
			}

			if( $pdf_type == 'quote' ){
				$email_job_details['send_quote'] = 1;
				$email_job_details['qt'] = $qt_type;
				$jog_log_txt = 'Quote';
			}else if( $pdf_type == 'invoice_cert' ){
				$email_job_details['mm_need_proc_inv'] = 1;
				$jog_log_txt = 'Invoice/Cert';
			}else if( $pdf_type == 'invoice' ){
				$email_job_details['mm_need_proc_inv'] = 1;
				$jog_log_txt = 'Invoice';
				$invoice_only = 1;
			}

			//print_r($email_job_details);
			//exit();

			$email_job_details['platform_invoicing'] = 1;
			$response = $this->email_functions_model->sendInvoiceCertEmail($email_job_details, $jemail, $country_id, $invoice_only);
			echo $response;
    	}
    }

    public function ajax_update_dha_need_processing($ajob = "", $adha = "", $amain = "") {

		$job_id = $ajob == "" ? $this->input->post('job_id') : $ajob;
		$dha_need_processing = $adha == "" ? $this->input->post('dha_need_processing') : $adha;
		$main_prog = $amain == "" ? $this->input->post('main_prog') : $amain;

		$updateData = array(
		    'dha_need_processing' => $dha_need_processing
		);

		$this->db->where('id', $job_id);
		$this->db->update('jobs', $updateData);
		
		//insert log
		$log_details = "Processed";
		$log_params = array(
			'title' => 79,
			'details' => $log_details,
			'display_in_vjd' => 1,
			'created_by_staff' => $this->session->staff_id,
			'job_id' => $job_id
		);
		$this->system_model->insert_log($log_params);

    }

    public function send_and_clear() {
    	$job_id = $this->input->post('job_id');
    	$attra = $this->input->post('attra');
    	$attrb = $this->input->post('attrb');
    	$invCert = $this->input->post('data_invoice_cert');
    	$quote = $this->input->post('data_quote');
    	$pdfType = array("quote", "invoice_cert", "invoice");

    	foreach ($job_id as $key => $val) {
    		// $needProcess = $attra[$key] === "1" ? 0 : 1;
    		$this->ajax_update_dha_need_processing($val, 0, $attrb[$key]);
    // 		foreach ($pdfType as $type) {
    // 			if ($invCert[$key] == 1) {
    // 				$this->ajax_dha_precomp_email_agency_accounts($val, "invoice_cert");
    // 			}
    // 			if ($quote[$key] > 0 ) {
    // 				$this->ajax_dha_precomp_email_agency_accounts($val, "quote");
    // 			}
				// $this->ajax_dha_precomp_email_agency_accounts($val, "invoice");
    // 		}
    	}
    }





}

