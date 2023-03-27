<?php

class Pdf_template extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->model('/inc/functions_model');
        $this->load->model('/inc/job_functions_model');
        //$this->load->library('JPDF');
        $this->load->library('JPDI');

        $this->load->model('pme_model');
        include APPPATH . 'third_party/phpqrcode/qrlib.php';
    }


    /**
     * set pdf_combined_template pdf template
     * param   job_id
     * param   job_details
     * return pdf output 
     */
    public function pdf_combined_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, $output = "", $is_copy = false){

        //$pdf = new JPDF();


        $this->updateInvoiceDetails($job_id);

        $property_job_types = $this->job_functions_model->getTechSheetAlarmTypesJob($job_details['property_id'], true);

        switch($job_details['jservice']){
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
            
            /*
            //$pdf=new FPDF('P','mm','A4');
            //include('fpdf_override.php');
            $pdf=new jPDF('P','mm','A4');
            $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
            $pdf->setCountryData($job_details['country_id']);
            */

            $pdf=new jPDI();
            //$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
            //$pdf->setCountryData($job_details['country_default']);

            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer
            $pdf->is_new_invoice_template(1); //use new template

        }

        $pdf->SetTopMargin(40);
        //$pdf->SetAutoPageBreak(true,35);
        $pdf->SetAutoPageBreak(true,55);
        $pdf->AddPage();



        //if( $job_details['show_as_paid']==1 || ( is_numeric($job_details['invoice_balance']) && $job_details['invoice_balance'] == 0 ) ){
        if( $job_details['show_as_paid']==1 || ( is_numeric($job_details['invoice_balance']) && $job_details['invoice_balance'] <= 0 && $job_details['invoice_payments'] > 0 ) ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/paid.png',90,110);
        }

        if( $is_copy == true ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/copy.png',10,10,30);
        }   

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}";	

        //invoice num
        $pdf->SetFont('Arial','B',18);
        $pdf->SetTextColor(255, 255, 255);
        if(isset($job_details['tmh_id']))
        {
           $pdf->Cell(275,3,'Tax Invoice    #' . str_pad($job_details['tmh_id'] . ' TMH', 6, "0", STR_PAD_LEFT),0,1,'C');
        }
        else
        {
            $pdf->Cell(275,-40, $bpay_ref_code,0,1,'C');
        }
        //invoice num end

        $pdf->SetFont('Arial','',10); 
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetY(40);
        //$pdf->Ln(18);

        // space needed to fit envelope
        $pdf->Cell(20,10,'');

        $pdf->Cell(70,5,'Invoice Date:   ' . $job_details['date']); 

        $pdf->SetFont('Arial','B',14);

        if(isset($job_details['tmh_id']))
        {
            //$pdf->Cell(100,5,'Tax Invoice    #' . str_pad($job_details['tmh_id'] . " TMH", 6, "0", STR_PAD_LEFT),0,1,'C');
            $pdf->Cell(100,5,'',0,1,'C');
        }
        else
        {
            //$pdf->Cell(100,5,'Tax Invoice    #' . $bpay_ref_code,0,1,'C');
            $pdf->Cell(100,5,'',0,1,'C');
        }


        $pdf->SetFont('Arial','',10);
        #$pdf->Cell(40,5,'Invoice #' . str_pad($job_id, 6, "0", STR_PAD_LEFT)); 

        $pdf->Ln(5);

        # Agent Details
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();

        // space needed to fit envelope
        $pdf->Cell(20,10,'');
        
        
        if( $property_details['add_inv_to_agen'] == 1 ){ 
            $landlord_txt = $property_details['agency_name'];
            $landlord_txt2 = "\n\nLANDLORD: {$landlord_txt}";
        }else if( 
            ( is_numeric($property_details['add_inv_to_agen']) && $property_details['add_inv_to_agen'] == 0 ) && 
            ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' ) 
        ){
            $landlord_txt = "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}";
            $landlord_txt2 = "\n\nLANDLORD: {$landlord_txt}";
        }else{
            $landlord_txt = "CARE OF THE OWNER";
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
        $workorder_txt = ($job_details['work_order'])?"\nWORK ORDER: {$job_details['work_order']}":"";

        $date_of_visit = ( $job_details['assigned_tech'] > 0 && $job_details['assigned_tech'] != 1 && $job_details['assigned_tech'] != 2 )?$job_details['date']:'N/A';

        $append_str = null;
        // if agency "Agency Allows up front billing" to yes and job type is YM
        $is_upfront_billing = ( $job_details['allow_upfront_billing'] == 1 && $job_details['job_type'] == "Yearly Maintenance" )?true:false;

        if( $is_upfront_billing == true ){

            //4644 - Ray White Whitsunday
            //4637 - Vision Real Estate Mackay
            //6782 - Vision Real Estate Dysart 
            //4318 - first national mackay
            $spec_agen_arr = array(4644,4637,6782,4318);

            if( in_array($property_details['agency_id'], $spec_agen_arr) ){

                // month format
                $sub_start_period = date("F Y",strtotime($job_details['jdate']));;
                $sub_end_period = date("F Y",strtotime($job_details['jdate']."+ 11 months"));

            }else{

                // d/m/y format
                $sub_start_period = date("1/m/Y",strtotime($job_details['jdate']));;
                $sub_end_period = date("t/m/Y",strtotime($job_details['jdate']."+ 11 months"));     

            }

            $append_str = "Subscription Period {$sub_start_period} - {$sub_end_period}";
            
        }else{
            $append_str = "DATE OF VISIT: {$date_of_visit}";
        }
        
        
        # Hack for LJ Hooker Tamworth - display Landlord in different spot for them
        if($property_details['agency_id'] == 1348){
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\n{$append_str}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);
            $pdf->MultiCell(70, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}",0,'L' );
            $box2_h = $pdf->GetY(); 
            $pdf->Ln(6);
        }else if ($property_details['agency_id'] == 3079){  
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n" . "\n\n{$append_str}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);
            $pdf->MultiCell(70, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" ,0,'L');
            $box2_h = $pdf->GetY(); 
        }else{  
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\n{$append_str}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);
            $pdf->MultiCell(70, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" ,0,'L');
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
        $os_sql = $this->job_functions_model->getService($job_details['jservice']);	
        $os = $os_sql->row_array();

        # Add Job Type
        $pdf->Cell(15,5,"1", 0, 0, '');
        $pdf->Cell(45,5,$job_details['job_type']);
        $pdf->Cell(80,5,$os['type']);
        $pdf->Cell(19,5,"$".number_format($job_details['job_price'], 2), 0, 0, 'R');
        $pdf->Cell(31,5,"$".number_format($job_details['job_price'], 2), 0, 0, 'R');
        $pdf->Ln();

        $grand_total = $job_details['job_price'];

        for($x = 0; $x < $num_alarms; $x++)
        {
            if($alarm_details[$x]['new'] == 1)
            {
                
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
        $sc_sql = $this->db->query("
            SELECT *, m.`name` AS m_name 
            FROM `agency_maintenance` AS am
            LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
            WHERE am.`agency_id` = {$property_details['agency_id']}
            AND am.`maintenance_id` > 0
        ");
        $sc = $sc_sql->row_array();
        if( $grand_total!=0 && $sc['surcharge']==1 ){
            
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(15,5,"1", 0, 0, '');
            $pdf->Cell(45,5,$sc['m_name']);
            $surcharge_txt = ($sc['display_surcharge']==1)?$sc['surcharge_msg']:'';
            $pdf->Cell(80,5,$surcharge_txt);
            $pdf->Cell(19,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
            $pdf->Cell(31,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
            $pdf->Ln();
            
            $grand_total += $sc['price'];
            
        }


        // CREDITS
        $credit_sql = $this->db->query("
            SELECT *
            FROM `invoice_credits` AS ic 
            WHERE ic.`job_id` = {$job_id}
        ");

        foreach($credit_sql->result_array() as $credit){

            $item_credit_text = ($credit['credit_paid']<0) ? 'Credit - Reversal' : 'Credit' ;
            $credit_paid = ( $credit['credit_paid']<0 ) ? '$'.number_format(abs($credit['credit_paid']),2) : "$".number_format($credit['credit_paid'], 2) ;
            
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(15,5,"1", 0, 0, '');
            $pdf->Cell(45,5,'Credit');
            $pdf->SetFont('Arial','I',10);
            $pdf->SetTextColor(255, 0, 0); // red
            $pdf->Cell(80,5,'Reason: '.$this->getInvoiceCreditReason($credit['credit_reason']));
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial','',10);
            // $pdf->Cell(19,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
            // $pdf->Cell(31,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
            $pdf->Cell(19,5,'('.$credit_paid.')', 0, 0, 'R');
		    $pdf->Cell(31,5,'('.$credit_paid.')', 0, 0, 'R');

            $pdf->Ln();
            
            $grand_total -= $credit['credit_paid'];
            
        }

            
        $pdf->Ln(8);
        $pdf->SetFont('Arial','',10);

        // get country
        $c_sql = $this->db->query("
            SELECT *
            FROM `agency` AS a
            LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
            WHERE a.`agency_id` = {$property_details['agency_id']}
        ");
        $c = $c_sql->row_array();

        // gst
        if($c['country_id']==1){
            $gst = $grand_total / 11;
        }else if($c['country_id']==2){
            $gst = ($grand_total*3) / 23;
        }	

        // get cursor position
        $cursor_y = $pdf->GetY();

        //SUB TOTAL
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = 'Sub Total';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        //$pdf->Cell(31,5,"$" . number_format($grand_total - ($grand_total / 11), 2), 0, 0, 'R');
        //$gst = $grand_total * .10;
        $pdf->Cell(31,5,"$" . number_format($grand_total-($gst), 2), 0, 0, 'R');
        $pdf->Ln();

        //GST
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = 'GST';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        //$pdf->Cell(31,5,"$" . number_format($grand_total / 11, 2), 0, 0, 'R');
        $pdf->Cell(31,5,"$" . number_format($gst, 2), 0, 0, 'R');
        $pdf->Ln();

        //Total
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = 'Total';
        //$text = 'Invoice Total';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        //$pdf->Cell(31,5,"$" . number_format($grand_total, 2), 0, 0, 'R');
        $pdf->Cell(31,5,"$" . number_format($grand_total, 2), 'B', 0, 'R');
        //$pdf->Ln(12);
        $pdf->Ln();

        // Payments/Credits
        $pdf->Cell(140,10,"", 0, 0, 'C');
        $text = 'Payments';
        //$pdf->SetTextColor(255, 0, 0); // red
        $pdf->Cell(25,10,$text, 0, 0, 'R');
        $pdf->SetFont('Arial','B',12);
        $inv_payments = $grand_total - $job_details['invoice_balance'];
        $pdf->Cell(25,10,'($'.number_format($inv_payments, 2).')', 0, 0, 'R');
        //$pdf->SetTextColor(0, 0, 0); // clear red
        $pdf->Ln();

        // balance
        $pdf->SetFont('Arial','I',10);
        $pdf->Cell(140,10,"", 0, 0, 'C');
        $text = 'Amount Owing';
        $pdf->Cell(25,5,$text, 0, 0, 'R');
        $pdf->SetFont('Arial','B',12);
        $inv_balance = ( is_numeric($job_details['invoice_balance']) )?$job_details['invoice_balance']:$grand_total;
        $pdf->Cell(25,5,'$'.number_format($inv_balance, 2), 0, 0, 'R');
        $pdf->Ln();



        $x_pos = 10;
        $pdf->SetXY($x_pos,(($cursor_y)-1.3));	


        // BPAY AU only
        if( $c['country_id']==1 && $job_details['display_bpay']==1 ){
            
            // BPAY logo	
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/bpay/bpay_does_not_accept_credit_card.jpg',null,null,60);
            
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
        $qr_code_path = APPPATH . 'third_party/phpqrcode/temp/invoice_'.$bpay_ref_code.'_qr_code.png';

        // generate QR code
        # include library
        //include($_SERVER['DOCUMENT_ROOT'].'phpqrcode/qrlib.php');
        $qr_code = $this->functions_model->generate_qr_code($bpay_ref_code,$job_details['property_id'],number_format($grand_total, 2),number_format($gst, 2),$job_details['date'],$country_id);
        QRcode::png($qr_code['data'], $qr_code['path'],'L',2);

        // display QR code
        $pdf->Image($qr_code_path,null,null);
        // delete qr image
        unlink($qr_code_path);
        $pdf->SetFont('Arial','',6.5);
        $pdf->Cell(24,2,'getpaidfaster.com.au',0,0); 
        $pdf->SetFont('Arial','',11);

        $x_pos = $pdf->getX();

        // Direct Deposit Details
        $pdf->SetXY($x_pos ,$cursor_y);
        $pdf->SetFont('Arial','',10);

        $c_bank = $c['bank'];	
        $c_ac_name = $c['ac_name'];
        $c_ac_number = $c['ac_number'];
            
if($c['country_id']!=2){
    
$c_bsb = $c['bsb'];	
$pdf->MultiCell(55,5,"Direct Deposit Details:
Name: {$c_ac_name}
Bank: {$c_bank} 
BSB: {$c_bsb}
Account #: {$c_ac_number}
",0,'L');

}else{
    
$pdf->MultiCell(55,5,"Direct Deposit Details:
Name: {$c_ac_name}
Bank: {$c_bank} 
Account #: {$c_ac_number}
",0,'L');

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


        $c_bank = $c['bank'];
        $c_bsb = $c['bsb']; 
        $c_ac_name = $c['ac_name'];
        $c_ac_number = $c['ac_number'];

        if($c['country_id']!=2){
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



        $appliance_details = $this->getPropertyAlarms($job_id, 1, 0, 1);
        $num_appliances = sizeof($appliance_details);
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
            $pdf->Cell(15, 5, $job_details['retest_date']);

            $pdf->Ln(15);

            $pdf->SetFont('Arial','',9);
            $pdf->MultiCell(185,5,'All Appliances located within the property as detailed above are compliant with current legislation and Australian Standards. Appliances and leads are tested as per Manufacturers recommendations & the NSW Test and Tag requirements.');
            $pdf->Ln(10);
        }


        // if bundle, get bundle services id
        $ajt_serv_sql = $this->job_functions_model->getService($job_details['jservice']);
        $ajt_serv = $ajt_serv_sql->row_array();

        // bundle
        if($ajt_serv['bundle']==1){ 
            $bs_sql =  $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `id` IN({$ajt_serv['bundle_ids']})
            ");
        // not bundle
        }else{
            $bs_sql =  $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `id` = {$job_details['jservice']}
            ");
        }

        

        // loop
        // while($bs = mysql_fetch_array($bs_sql)){
        foreach($bs_sql->result_array() as $bs){

            // smoke alarms
            if( $bs['id'] == 2 || $bs['id'] == 12 ){
               

                $pdf->SetFont('Arial','B',11);

                $pdf->Cell(45,5,"{$bs['type']} Summary:");
                $pdf->Ln(10);

                $ast_pos = 3;
                $hw_Position = 35;
                $hw_Power = 18;
                $hw_Type = 30;
                $hw_Make = 27;
                $hw_Model = 25;
                $hw_Expiry = 14;
                $hw_dB = 25;
                
                $pdf->Cell($ast_pos,5,"");
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

                
                $jalarms_sql = $this->db->query("
                    SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason  
                    FROM alarm a 
                        LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
                        LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
                        LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
                    WHERE a.job_id = '" . $job_id . "'
                    ORDER BY a.`ts_discarded` ASC, a.alarm_id ASC
                ");	
                $temp_alarm_flag = 0;		
                foreach($jalarms_sql->result_array() as $jalarms)
                {
                    // if reason: temporary alarm
                    if( $jalarms['alarm_reason_id']==31 ){
                        $temp_alarm_flag = 1;
                    }

                    // red italic - start
                    if($jalarms['ts_discarded']==1){
                        $pdf->SetTextColor(255, 0, 0);
                        $pdf->SetFont('Arial','I',$sa_font_size);
                    }

                    // if techsheet "Required for Compliance" = 0/No
                    $append_asterisk = '';
                    if( $jalarms['ts_required_compliance'] == 0 ){
                        $append_asterisk = '*';
                    }

                    $pdf->SetTextColor(255, 0, 0); // red
                    $pdf->Cell($ast_pos,5,$append_asterisk);
                    $pdf->SetTextColor(0, 0, 0); // clear red

                    $pdf->Cell($hw_Position,5,mb_strimwidth($jalarms['ts_position'], 0, 20, '...'));
                    $pdf->Cell($hw_Power,5,$jalarms['alarm_pwr']);
                    $pdf->Cell($hw_Type,5,$jalarms['alarm_type']);
                    $pdf->Cell($hw_Make,5,$jalarms['make']);
                    $pdf->Cell($hw_Model,5,$jalarms['model']);
                    $pdf->Cell($hw_Expiry,5,$jalarms['expiry']);
                    
                    if($jalarms['ts_discarded']==1){
                        $adr_sql = $this->db->query("
                            SELECT * 
                            FROM `alarm_discarded_reason`
                            WHERE `active` = 1
                            AND `id` = {$jalarms['ts_discarded_reason']}
                        ");
                        $adr = $adr_sql->row_array();
                        $pdf->Cell($hw_dB,5,'Removed - '.$adr['reason']);
                    }else{
                        $pdf->Cell($hw_dB,5,$jalarms['ts_db_rating']);
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

                switch($c['country_id']){
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
                
                
                if( $job_details['state'] == 'QLD' && $temp_alarm_flag==1 ){ // if QLD and temporary alarm
                    $pdf->SetTextColor(255, 0, 0);
                    $pdf->SetFont('Arial','I',10);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(185,5,'Smoke alarms at the above property are NOT compliant with AS3786 (2014) and will need to be replaced when compliant smoke alarms become available. The property has working smoke alarms and the property is safe however they are not compliant, and SATS will revisit the property to make it compliant as soon as compliant alarms become available.'); 
                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(0, 0, 0);
                }else if( $job_details['state'] == 'NSW' ){

                    if( $job_details['country_id']==1 ){ // AU
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms within the property as detailed above where a removable battery is present have had batteries replaced at this service dated '.$date_of_visit.' in accordance with Residential Tenancies Regulation 2019 [NSW]. '); 
                        $pdf->Ln(3);
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,"All smoke alarms within the property as detailed above have been cleaned and tested as per manufacturer's instructions and where new alarms have been installed they have been installed in accordance with Residential Tenancies Regulation 2019 [NSW], Australian Standard AS 3786: 2014 Smoke Alarms, Building Code of Australia, Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations"); 
                    }else if( $job_details['country_id']==2 ){ // NZ
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations (where smoke alarms are hard-wired) and Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.'); 
                    } 

                }else{
                    
                    if( $job_details['country_id']==1 ){ // AU
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and been installed in accordance with '.$country_text.' Standard AS 3786 (2014) Smoke Alarms, Building Code of '.$c['country'].', Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations.'); 
                    }else if( $job_details['country_id']==2 ){ // NZ
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations (where smoke alarms are hard-wired) and Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.'); 
                    }  						
                    
                }
                
                
                $pdf->Ln(3);
                //$pdf->SetFont('Arial','',8);
                
                $pdf->SetTextColor(255, 0, 0); // red
                $pdf->Cell($ast_pos,5,'*');
                $pdf->SetTextColor(0, 0, 0); // clear red
                $pdf->MultiCell(185,5,'Not required for compliance');  

                $pdf->Ln(3);
                $pdf->MultiCell(185,5,'Where alarm Power is 240v or 240vLi the alarm is mains powered. (Hard Wired). All other alarms are battery powered.');  
                
                
            // safety switch
            }else if($bs['id'] == 5){
            
                $ssp_sql = $this->db->query("
                    SELECT `ts_safety_switch`, `ts_safety_switch_reason`, `ss_quantity`
                    FROM `jobs`
                    WHERE `id` = {$job_details['id']}
                ");
                $ssp = $ssp_sql->row_array(); 

                $pdf->Ln(2);
                $pdf->SetDrawColor(190,190,190);
                $pdf->SetLineWidth(0.05);
                $pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

                $pdf->Ln(6);

                $pdf->SetFont('Arial','B',11);

                $pdf->Cell(45,5,"{$bs['type']} Summary:");
                $pdf->Ln(10);
                
                // check if at least 1 SS failed
                $chk_ss_sql = $this->db->query("
                    SELECT *
                    FROM `safety_switch`
                    WHERE `job_id` ={$job_details['id']}
                    AND `test` = 0
                ");
                
                $num_ss_fail = $chk_ss_sql->row_array();
                
                //if( $num_ss_fail > 0 ){
                    
                    // Fusebox Viewed
                    /* comment out (gherx)
                    $pdf->Ln(4);
                    $pdf->SetFont('Arial','B',11);			
                    $pdf->Cell(40,5,"Fusebox Viewed:");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(15,5,($ssp['ts_safety_switch']==2)?'Yes':'No');
                    */
                
                // Fusebox Viewed - Yes
                        if($ssp['ts_safety_switch']==2){

                            //SS TABLE START
                             //$pdf->Cell(30,5,"{$service} Headings");
                            $pdf->Cell(30,5,"Make");
                            $pdf->Cell(30,5,"Model");
                            //$pdf->Cell(30,5,"Test Date");
                            $pdf->Cell(30,5,"Test Result");
                            $pdf->Ln(9);
                            $pdf->SetFont('Arial','',10);

                            //$pdf->Cell(30,5,"{$service} Data");
                            $ss_sql = $this->db->query("
                                SELECT *
                                FROM `safety_switch`
                                WHERE `job_id` ={$job_details['id']}
                                ORDER BY `make`
                            ");
                        
                            // while($ss = mysql_fetch_array($ss_sql))
                            foreach($ss_sql->result_array() as $ss)
                            {
                                
                                $pdf->Cell(30,5,$ss['make']);
                                $pdf->Cell(30,5,$ss['model']);
                                //$pdf->Cell(30,5,$job_details['date']);  
                                if($ss['test']==1){ // pass
                                    $pdf->Cell(30,5,'Pass');
                                }else if( is_numeric($ss['test']) && $ss['test']==0 ){ // fail
                                    $pdf->SetTextColor(255, 0, 0); // red
                                    $pdf->Cell(30,5,'Fail');
                                    $pdf->SetTextColor(0, 0, 0); 
                                }else if($ss['test']==2){ // no power
                                    $pdf->Cell(30,5,'No Power to Property at time of testing');
                                }else if($ss['test']==3){ // not tested
                                    $pdf->Cell(30,5,'Not Tested');
                                }else if($ss['test']==''){
                                    $pdf->Cell(30,5,'Not Tested');
                                }
                                
                                $pdf->Ln();
                            }
                            //SS TABLE START END
                        
                            //new gherx added  
                            if($ssp['ss_quantity']==0){ // 0 safety switch
                                $pdf->SetTextColor(255,0,0);
                                $pdf->Cell($ast_pos,5,'');
                                $pdf->MultiCell(185,5,'No Safety Switches Present. We strongly recommend a Safety Switch be installed to protect the occupants.'); 
                                $pdf->Ln(4);
                                $pdf->Cell($ast_pos,5,'');
                                $pdf->MultiCell(185,5,'Our Technician has noted there are no safety switches installed at the premises. Therefore none were tested upon attendance.'); 
                                $pdf->SetTextColor(0,0,0);
                            }else{ // 1 or more safety switch

                                // query if at least 1 has not tested
                                $chk_ss_not_tested_sql = $this->db->query("
                                    SELECT *
                                    FROM `safety_switch`
                                    WHERE `job_id` ={$job_details['id']}
                                    AND `test` = 3
                                ");

                                // query if at least 1 has no power
                                $chk_ss_no_pwr_sql = $this->db->query("
                                    SELECT *
                                    FROM `safety_switch`
                                    WHERE `job_id` ={$job_details['id']}
                                    AND `test` = 2
                                ");
                                $num_no_power = $chk_ss_no_pwr_sql->num_rows();

                                $pdf->Ln(4);
                                $pdf->MultiCell(185,5,$ss_sql->num_rows().' Safety Switches Present'); //display number of switch	

                                if( $num_no_power > 0 ){ //NO POWER		
                                    $pdf->Ln(4);						
                                    $pdf->MultiCell(185,5,"One or more of the safety switches at the property were unable to be tested due to no power supply to the property at the time of inspection, and power is required to perform a mechanical test on the Safety Switches.");
                                }else if( $num_ss_fail > 0 ){ // ATLEAT 1 SS TEST FAILD	
                            
                                    switch ($chk_ss_sql->num_rows()) {
                                        case 1:
                                            $num_string = "One";
                                            break;
                                        case 2:
                                            $num_string = "Two";
                                            break;
                                        case 3:
                                            $num_string = "Three";
                                            break;
                                        case 4:
                                            $num_string = "Four";
                                            break;	
                                        case 5:
                                            $num_string = "Five";
                                            break;	
                                        case 6:
                                            $num_string = "Six";
                                            break;
                                        case 7:
                                            $num_string = "Seven";
                                            break;
                                        case 8:
                                            $num_string = "Eight";
                                            break;
                                        case 9:
                                            $num_string = "Nine";
                                            break;
                                        case 10:
                                            $num_string = "Ten";
                                            break;
                                        default:
                                            $num_string = $num_ss_fail;
                                    } 
        
                                    /*$pdf->Ln(4);					
                                    $pdf->MultiCell(185,5,"One or more of the Safety Switches at this property has failed. This information is for your use, and we strongly suggest you advise your client. SATS do not install Safety Switches; however we do test them when they are present.");
                                    $pdf->Ln(4);*/
                                    $pdf->SetTextColor(255, 0, 0); // red
                                    $have_has = ($chk_ss_sql->num_rows()>1) ? 'have' : 'has';
                                    $pdf->MultiCell(185,5,"{$num_string} of the Safety Switches at this property {$have_has} failed. This information is for your use, and we strongly suggest you advise your client. SATS do not install Safety Switches; however we do test them when they are present.");
                                    $pdf->SetTextColor(0, 0, 0);
                                    
                                }else if($chk_ss_not_tested_sql->num_rows()>0){ //IF ANY SS NOT TESTED
                                    $pdf->Ln(4);					
                                    $pdf->MultiCell(185,5,"One or more of the safety switches at the property were unable to be tested at the time of attendance. Please contact SATS for further information.");
                                }else{
                                    $pdf->Ln(4);					
                                    $pdf->MultiCell(185,5,"All Safety Switches have been Mechanically Tested and pass a basic mechanical test, to assess they are in working order. No test has been performed to determine the speed at which the device activated.");
                                }

                            }
                            //new gherx added end

                        // Fusebox Viewed - No
                        }else if($ssp['ts_safety_switch']==1){
                        
                            // reason				
                            $pdf->SetFont('Arial','B',11);			
                            //$pdf->Cell(18,5,"Reason:");
                            $pdf->SetFont('Arial','',10);
                            switch($ssp['ts_safety_switch_reason']){
                                case 0:
                                    $ssp_reason = 'No Switch';
                                    $ssp_reason2 = "Our Technician has noted there are no safety switches installed at the premises. Therefore none were tested upon attendance.";
                                break;
                                case 1:
                                    $ssp_reason = 'Unable to Locate';
                                    $ssp_reason2 = "One or more of the safety switches were not tested due to the inability to locate them at the time of attendance.";
                                break;
                                case 2:
                                    $ssp_reason = 'Unable to Access';
                                    $ssp_reason2 = "One or more of the safety switches were not tested due to the inability to access at the time of attendance.";
                                break;
                            }
                           // $pdf->Cell(30,5,$ssp_reason);

                            $pdf->Ln(8);
                            $pdf->MultiCell(185,5,$ssp_reason2);
                        
                        }
                        
                   // }
                    
                    $pdf->Ln(3);
                    $pdf->SetFont('Arial','',8);
                
                //}
                    
                
            // corded windows
            }else if($bs['id'] == 6){

                $pdf->SetFont('Arial','B',11);

                $pdf->Cell(45,5,"{$bs['type']} Summary:");
                $pdf->Ln(10);

                $num_windows_total = 0;
                $cw_sql = $this->db->query("
                    SELECT *
                    FROM `corded_window`
                    WHERE `job_id` ={$job_id}
                ");
                // while( $cw = mysql_fetch_array($cw_sql) ){
                foreach($cw_sql->result_array() as $cw){
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
                $wm_sql = $this->functions_model->getWaterMeter($job_details['id']);
                // while($wm = mysql_fetch_array($wm_sql))
                foreach($wm_sql->result_array() as $wm)
                {
                    
                    $pdf->Cell(30,5,$wm['reading']);
                    $pdf->Cell(30,5,$wm['location']);
                    $pdf->Ln();
                }

                $pdf->Ln(4);
                    
                $pdf->SetFont('Arial','',10);
            
                
            }

        }


        $pdf->Ln(2);
        $pdf->SetFont('Arial','',10);	


        // if service type is IC dont show, only show for non-IC services
        $ic_service = $this->system_model->getICService();

        if(in_array($job_details['jservice'], $ic_service)){
            $ic_check = 1;
        }else{
            $ic_check = 0;
        }

        if( $ic_check == 0 && $job_details['state'] == 'QLD' && $job_details['qld_new_leg_alarm_num']>0 && $job_details['prop_upgraded_to_ic_sa'] != 1 ){

            $pdf->SetTextColor(0, 0, 204);              
            // QUOTE
            $quote_qty = $job_details['qld_new_leg_alarm_num'];
            $price_240vrf = $this->get240vRfAgencyAlarm($property_details['agency_id']);
            $quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
            $quote_total = $quote_price*$quote_qty;
            //$pdf->MultiCell(185,5,'We have provided a quote for $'.$quote_total.' to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details['date']).'+90 days')).' and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
            $pdf->MultiCell(185,5,'We have provided a quote to upgrade this property to meet the NEW QLD 2022 legislation. This quote is valid until 21/04/2022 and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
            $pdf->SetTextColor(0, 0, 0);
            

        }	


        // WE PDF
        // get WE services
        $we_services = $this->system_model->we_services_id();    

        if ( in_array($job_details['jservice'], $we_services) ){ // only display if it has WE service
            
            // display WE PDF using FPDI
            $pdf->SetFont('Arial','',10);
            $pdf->SetAutoPageBreak(true,7);
            $pdf->addPage(); 
            $pdf->set_dont_display_footer(1); // hide the footer
            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/we_cert.pdf');
            $tplidx = $pdf->importPage(1);        
            $pdf->useTemplate($tplidx, 0, 20);

            // ADDRESS
            // Stret name and num
            $pdf->setXY(27,75);
            $pdf->Cell(8,0, "{$property_details['address_1']} {$property_details['address_2']}");
            
            // suburb and state
            $pdf->setXY(27,82.5);
            $pdf->Cell(8,0, "{$property_details['address_3']} {$property_details['state']}");

            // postcode
            $pdf->setXY(157,82.5);
            $pdf->Cell(8,0, $property_details['postcode']);

            // water efficiency measures         
            $we_sql = $this->db->query("
            SELECT 
                we.`water_efficiency_id`,
                we.`device`,
                we.`pass`,
                we.`location`,
                we.`note`,

                wed.`water_efficiency_device_id`,
                wed.`name` AS wed_name
            FROM `water_efficiency` AS we
            LEFT JOIN `water_efficiency_device` AS wed ON we.`device` = wed.`water_efficiency_device_id`
            WHERE we.`job_id` = {$job_id}
            AND we.`active` = 1
            ORDER BY we.`location` ASC
            ");        

            // total count
            $shower_count = 0;
            $tap_count = 0;
            $toilet_count = 0;

            // total pass count
            $shower_pass_count = 0;
            $tap_pass_count = 0;
            $toilet_pass_count = 0;

            foreach( $we_sql->result() as $we_row ){

                // shower count
                if($we_row->device == 3){ 
                    $shower_count++;
                }

                // tap count
                if($we_row->device == 1){ 
                    $tap_count++;
                }

                // toilet
                if($we_row->device == 2){ 
                    $toilet_count++;
                }
            
                // passed shower count
                if( $we_row->device == 3 && $we_row->pass == 1 ){
                    $shower_pass_count++;
                }

                // passwed tap count
                if( $we_row->device == 1 && $we_row->pass == 1 ){
                    $tap_pass_count++;
                }

                // passed toilet count
                if( $we_row->device == 2 && $we_row->pass == 1 ){
                    $toilet_pass_count++;
                }

            }         

            // leak    
            $pass_img = null;  
            if ( $job_details['property_leaks'] == 0 && is_numeric($job_details['property_leaks']) ){
                $pass_img = 'green_check.png';
            }else if( $job_details['property_leaks'] == 1 ){
                $pass_img = 'red_cross.png';
            }  
            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,108,10);
            }             
            

            // shower           
            $pass_img = null;      
            if ( $shower_pass_count == $shower_count ){
                $pass_img = 'green_check.png';
            }else{
                $pass_img = 'red_cross.png';
            } 
            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,130,10);
            }
            

            // tap        
            $pass_img = null;      
            if ( $tap_pass_count == $tap_count ){
                $pass_img = 'green_check.png';
            }else{
                $pass_img = 'red_cross.png';
            } 
            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,150,10);
            }
            

            // toilet
            $dual_flush_due_date =  '2025/03/23';
            $pass_img = null;   
            
            if ( $toilet_pass_count == $toilet_count ){ // pass
                $pass_img = 'green_check.png';
            }else{ // fail

                if( $job_details['jdate'] >= date('Y-m-d',strtotime($dual_flush_due_date)) ){
                    $pass_img = 'red_cross.png';
                }else{
                    $pass_img = 'green_check.png';
                }
                
            }         
            

            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,175,10);
            }


            // WE summary        
            $pdf->setXY(12,220);
            $pdf->SetFont('Arial','B',11);       
            
            $left_spacing = 21;

            // set headers
            $th_border = 0;
            $we_col3 = 60;
            $we_col1 = 60;
            $we_col2 = 60;       
            //$we_col4 = 100;

            $pdf->setX($left_spacing);
            $pdf->Cell($we_col3,5,"Location",$th_border);
            $pdf->Cell($we_col1,5,"Device",$th_border);
            $pdf->Cell($we_col2,5,"Result",$th_border);        
            //$pdf->Cell($we_col4,5,"Note",$th_border);
            $pdf->Ln();
        

            $pdf->SetFont('Arial','',10);
            
            foreach( $we_sql->result() as $we_row ){

                $pdf->setX($left_spacing);
                $pdf->Cell($we_col3,5,$we_row->location,$th_border);
                $pdf->Cell($we_col1,5,$we_row->wed_name,$th_border);

                if( $we_row->device == 2 ){ // toilet

                    if( $we_row->pass == 1 ){
                        $pdf->Cell($we_col2,5,'Dual Flush',$th_border);
                    }else if( $we_row->pass == 0 && is_numeric($we_row->pass) ){
                        $pdf->SetTextColor(255, 0, 0); // red
                        $pdf->Cell($we_col2,5,'*Single Flush',$th_border);
                        $pdf->SetTextColor(0, 0, 0); // clear red
                    }

                }else{ // tap or shower

                    if( $we_row->pass == 1 ){
                        $pdf->Cell($we_col2,5,'Pass',$th_border);
                    }else if( $we_row->pass == 0 && is_numeric($we_row->pass) ){
                        $pdf->SetTextColor(255, 0, 0); // red
                        $pdf->Cell($we_col2,5,'Fail',$th_border);
                        $pdf->SetTextColor(0, 0, 0); // clear red
                    }
                }
                                
                //$pdf->Cell($we_col4,5,$we_row->note,$th_border);
                $pdf->Ln();            
            }

            // leak notes
            $pdf->setX($left_spacing);
            $pdf->SetFont('Arial','I',10);
            $pdf->SetTextColor(255, 0, 0); // red
            $pdf->Cell(130,5,$job_details['leak_notes']); 
            $pdf->SetTextColor(0, 0, 0); // clear red  

            $pdf->ln(10);  
            $pdf->setX($left_spacing);

            // note
            $note_border = 0;
            $pdf->SetFont('Arial','I',10);      
            $pdf->Cell(12,5,'*Note:',$note_border);  

            // pass
            $pdf->SetFont('Arial','BI',10); 
            $pdf->Cell(12,5,'PASS',$note_border);   
            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(52,5,'= Less than 9L/minute flow rate;',$note_border);      
            
            // fail
            $pdf->SetFont('Arial','BI',10); 
            $pdf->Cell(10,5,'FAIL',$note_border);   
            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(55,5,'= greater than 9L/minute flow rate.',$note_border);  
            
            $pdf->ln();  
            $pdf->setX($left_spacing+11);

            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(130,5,'Single Flush toilets must be replaced to dual flush toilets on/after 23rd March 2025',$note_border);              

        }
        
        if ($output == "") {
            return $pdf->Output('','S');
        }else {
            return $pdf;
        }


   }

   public function pdf_invoice_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, $output = "", $is_copy = false){

        //$pdf = new JPDF();

        //$this->updateInvoiceDetails($job_id); ##disabled use same function from system_model
        $this->system_model->updateInvoiceDetails($job_id);

        #instantiate only if required
        if(!isset($pdf)) {
            
            /*
            //$pdf=new FPDF('P','mm','A4');
            //include('fpdf_override.php');
            $pdf=new jPDF('P','mm','A4');
            $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
            $pdf->setCountryData($job_details['country_id']);
            */


            $pdf=new jPDI();
            //$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
            //$pdf->setCountryData($job_details['country_default']);

            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer
            $pdf->is_new_invoice_template(1); //use new template

        }

        $pdf->SetTopMargin(40);
        $pdf->SetAutoPageBreak(true,63);
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
        

        
        if( $job_details['show_as_paid']==1 || ( is_numeric($job_details['invoice_balance']) && $job_details['invoice_balance'] <= 0 && $job_details['invoice_payments'] > 0 ) ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/paid.png',55,180);
        }

        if( $is_copy == true ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/copy.png',160,70,30);
        }       
        
        // space needed to fit envelope
        //$pdf->Cell(20,10,'');
        
        // append checkdigit to job id for new invoice number

        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}";
        
        //invoice num
        $pdf->SetFont('Arial','B',18);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetX(139);
        if(isset($job_details['tmh_id']))
        {
           $pdf->Cell(275,3,'Tax Invoice    #' . str_pad($job_details['tmh_id'] . ' TMH', 6, "0", STR_PAD_LEFT),0,1,'L');
        }
        else
        {
            $pdf->Cell(275,-40, $bpay_ref_code,0,1,'L');
        }
        //invoice num end

        $pdf->SetFont('Arial','',11); 
        $pdf->SetTextColor(0, 0, 0);
       
        $pdf->SetY(40);
        $pdf->SetX(30);
        

        /* ---Disabled > update to new layout
        $pdf->Cell(70,5,'Invoice Date:   ' . $job_details['date']); 
        
        $pdf->SetFont('Arial','B',18);
        
        if(isset($job_details['tmh_id']))
        {
           //$pdf->Cell(100,5,'Tax Invoice    #' . str_pad($job_details['tmh_id'] . ' TMH', 6, "0", STR_PAD_LEFT),0,1,'C');
           $pdf->Cell(100,5,'',0,1,'C');
        }
        else
        {
            //$pdf->Cell(100,5,'Tax Invoice    #' . $bpay_ref_code,0,1,'C');
            $pdf->Cell(100,5,'',0,1,'C');
        }

        $pdf->SetFont('Arial','',11);
        // $pdf->Cell(40,5,'Invoice #' . str_pad($job_id, 6, "0", STR_PAD_LEFT)); 
        
        $pdf->Ln(5);
       
        # Agent Details
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        
        // space needed to fit envelope
        $pdf->Cell(20,10,'');
        
        
        if( $country_id == 1 ){ // AU

            if( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' ){
                $landlord_txt = "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}";
                $landlord_txt2 = "\n\nLANDLORD: {$landlord_txt}";
            }else{
                $landlord_txt = "CARE OF THE OWNER";
                $landlord_txt2 = "";
            }

        }else if( $country_id == 2 ){ // NZ
        
        
            if( $property_details['add_inv_to_agen'] == 1 ){ 
                $landlord_txt = $property_details['agency_name'];
                $landlord_txt2 = "\n\nLANDLORD: {$landlord_txt}";
            }else if( 
                ( is_numeric($property_details['add_inv_to_agen']) && $property_details['add_inv_to_agen'] == 0 ) && 
                ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' ) 
            ){
                $landlord_txt = "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}";
                $landlord_txt2 = "\n\nLANDLORD: {$landlord_txt}";
            }else{
                $landlord_txt = "CARE OF THE OWNER";
                $landlord_txt2 = "";
            }

        }    
        
        
        
        // compass index number
        if( $property_details['compass_index_num'] != '' ){
            $compass_index_num = "\nINDEX NO.: {$property_details['compass_index_num']}";   
        }else{
            $compass_index_num = "\n";
        }
        

        $agency_address_txt = htmlspecialchars_decode("{$property_details['a_address_1']} {$property_details['a_address_2']}\n{$property_details['a_address_3']} {$property_details['a_state']} {$property_details['a_postcode']}");
        $property_address_txt = htmlspecialchars_decode("{$property_details['address_1']} {$property_details['address_2']}\n{$property_details['address_3']} {$property_details['state']} {$property_details['postcode']}");
        $workorder_txt = ($job_details['work_order'])?"\nWORK ORDER: {$job_details['work_order']}":"";
        
        $date_of_visit = ( $job_details['assigned_tech'] > 0 && $job_details['assigned_tech'] != 1 && $job_details['assigned_tech'] != 2 )?$job_details['date']:'N/A';
    
        $append_str = null;
        // if agency "Agency Allows up front billing" to yes and job type is YM
        $is_upfront_billing = ( $job_details['allow_upfront_billing'] == 1 && $job_details['job_type'] == "Yearly Maintenance" )?true:false;

        if( $is_upfront_billing == true ){

            //4644 - Ray White Whitsunday
            //4637 - Vision Real Estate Mackay
            //6782 - Vision Real Estate Dysart 
            //4318 - first national mackay
            $spec_agen_arr = array(4644,4637,6782,4318);

            if( in_array($property_details['agency_id'], $spec_agen_arr) ){

                // month format
                $sub_start_period = date("F Y",strtotime($job_details['jdate']));;
                $sub_end_period = date("F Y",strtotime($job_details['jdate']."+ 11 months"));

            }else{

                // d/m/y format
                $sub_start_period = date("1/m/Y",strtotime($job_details['jdate']));;
                $sub_end_period = date("t/m/Y",strtotime($job_details['jdate']."+ 11 months"));     

            }

            $append_str = "Subscription Period {$sub_start_period} - {$sub_end_period}";
            
        }else{
            $append_str = "DATE OF VISIT: {$date_of_visit}";
        }
        
        
        # Hack for LJ Hooker Tamworth - display Landlord in different spot for them
        if($property_details['agency_id'] == 1348){
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\n{$append_str}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);
            $pdf->MultiCell(70, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}",0,'L' );
            $box2_h = $pdf->GetY(); 
            $pdf->Ln(6);
        }else if ($property_details['agency_id'] == 3079){  
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n" . "\n\n{$append_str}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);
            $pdf->MultiCell(70, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" ,0,'L');
            $box2_h = $pdf->GetY(); 
        }else{  
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\n{$append_str}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);
            $pdf->MultiCell(70, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" ,0,'L');
            $box2_h = $pdf->GetY(); 
        }
        */ // ---Disabled > update to new layout end




        ## --------------------NEW HEADING----------------------
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        #first row
        if( $property_details['add_inv_to_agen'] == 1 ){ 
            $landlord_txt = $property_details['agency_name'];
            $landlord_txt2 = "{$landlord_txt}";
            $landlord_title = "LANDLORD: ";
        }else if( 
            ( is_numeric($property_details['add_inv_to_agen']) && $property_details['add_inv_to_agen'] == 0 ) && 
            ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' ) 
        ){
            $landlord_txt = "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}";
            $landlord_txt2 = "{$landlord_txt}";
            $landlord_title = "LANDLORD: ";
        }else{
            $landlord_txt = "CARE OF THE OWNER";
            $landlord_txt2 = "";
            $landlord_title = "LANDLORD: ";
        }   
        
        if( $property_details['add_inv_to_agen'] == 1 ){ 
            $agency_address_txt = htmlspecialchars_decode("{$property_details['a_address_1']} {$property_details['a_address_2']}\n{$property_details['a_address_3']} {$property_details['a_state']} {$property_details['a_postcode']}");
        }else{
            $agency_address_txt = "";
        }
       

        # Hack for LJ Hooker Tamworth - display Landlord in different spot for them
        if($property_details['agency_id'] == 1348){
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(12.5,5,'ATTN: ');
            $pdf->SetFont('Arial','',11);
            $pdf->ln();
            $pdf->cell(20,5,'');
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);

            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Invoice Date: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(70,5,$job_details['date']); 
            $pdf->ln();
            $pdf->SetX(124);
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Terms: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(30,5,'NET 30 Days'); 
            $box2_h = $pdf->GetY(); 
            $pdf->Ln(6);
        }else if ($property_details['agency_id'] == 3079){  
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(12.5,5,'ATTN: ');
            $pdf->SetFont('Arial','',11);
            $pdf->ln();
            $pdf->cell(20,5,'');
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);

            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Invoice Date: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(70,5,$job_details['date']); 
            $pdf->ln();
            $pdf->SetX(124);
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Terms: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(30,5,'NET 30 Days'); 
            $box2_h = $pdf->GetY(); 
        }else{  
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(12.5,5,'ATTN: ');
            $pdf->SetFont('Arial','',11);
            $pdf->ln();
            $pdf->cell(20,5,'');
            $pdf->MultiCell(90, 5, "{$landlord_txt}\n{$agency_address_txt}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);
            
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Invoice Date: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(70,5,$job_details['date']); 
            $pdf->ln();
            $pdf->SetX(124);
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Terms: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(30,5,'NET 30 Days'); 
            $box2_h = $pdf->GetY(); 
        }
        #first row end


        $pdf->Ln(5);
       
        # second row
        $pdf->SetY($box2_h+30);
        $pdf->SetX(16);
        $property_address_txt = htmlspecialchars_decode("{$property_details['address_1']} {$property_details['address_2']} {$property_details['address_3']} {$property_details['state']} {$property_details['postcode']}");
        //$workorder_txt = ($job_details['work_order'])?"\nWORK ORDER: {$job_details['work_order']}":"";
        $workorder_txt = ($job_details['work_order']!= 'NULL')?"{$job_details['work_order']}":"";
        
        // compass index number
        /*
        if( $property_details['compass_index_num'] != '' ){
            $compass_index_num = "\nINDEX NO.: {$property_details['compass_index_num']}";   
        }else{
            $compass_index_num = "";
        }
        */

        //Date of Visit/Subscription tweak
        $date_of_visit = ( $job_details['assigned_tech'] > 0 && $job_details['assigned_tech'] != 1 && $job_details['assigned_tech'] != 2 )?$job_details['date']:'N/A';
       
        // if agency "Agency Allows up front billing" to yes and job type is YM
        $is_upfront_billing = ( $job_details['allow_upfront_billing'] == 1 && $job_details['job_type'] == "Yearly Maintenance" )?true:false;

        $append_str = null;
        if( $is_upfront_billing == true ){

            //4644 - Ray White Whitsunday
            //4637 - Vision Real Estate Mackay
            //6782 - Vision Real Estate Dysart 
            //4318 - first national mackay
            $spec_agen_arr = array(4644,4637,6782,4318);

            // get subscription valid date range
            $sub_valid_date = $this->system_model->get_subscription_valid_date_range($property_details['property_id']);

            if( $sub_valid_date->success == true ){ // subscription date exist

                // d/m/y format
                $sub_start_period = date("d/m/Y",strtotime($sub_valid_date->sub_valid_from));;
                $sub_end_period = date("d/m/Y",strtotime($sub_valid_date->sub_valid_to)); 

            }else if( in_array($property_details['agency_id'], $spec_agen_arr) ){

                // month format
                $sub_start_period = date("F Y",strtotime($job_details['jdate']));;
                $sub_end_period = date("F Y",strtotime($job_details['jdate']."+ 11 months"));

            }else{

                // d/m/y format
                $sub_start_period = date("1/m/Y",strtotime($job_details['jdate']));;
                $sub_end_period = date("t/m/Y",strtotime($job_details['jdate']."+ 11 months"));     

            }

            //$append_str = "\nSUBSCRIPTION PERIOD: {$sub_start_period} - {$sub_end_period}";
            $append_str = "{$sub_start_period} - {$sub_end_period}";
            $subscription_or_datevisit_title = "SUBSCRIPTION PERIOD: ";
            
        }else{
            //$append_str = "\nDATE OF VISIT: {$date_of_visit}";
            $append_str = "{$date_of_visit}";
            $subscription_or_datevisit_title = "DATE OF VISIT: ";
        }
        
        #cell start
       //property
       $pdf->SetFont('Arial','B',11);
       $pdf->Cell(48,2.5,'PROPERTY SERVICED: ');
       $pdf->SetFont('Arial','',11);
       //$pdf->MultiCell(200, 5,"{$property_address_txt}{$append_str}{$landlord_txt2}{$compass_index_num}{$workorder_txt}",0,'L' );
       //$pdf->MultiCell(200, 2.5,"{$property_address_txt}",0,'L' ); ##disabled and replace below for macron NZ fix
       
       // fix for NZ macron char issue 
       setlocale(LC_CTYPE, 'en_US');
       $incov_val = iconv('UTF-8', 'windows-1252//TRANSLIT', $property_address_txt);
       $pdf->MultiCell(200, 2.5,"{$incov_val}",0,'L' );
       

       $pdf->ln();

       //SUbscription or Date of Visit
       $pdf->SetFont('Arial','B',11);
       $pdf->Cell('6',2.5,'');
       $pdf->Cell(48,2.5,$subscription_or_datevisit_title);
       $pdf->SetFont('Arial','',11);
       $pdf->MultiCell(200, 2.5,"{$append_str}",0,'L' );

       $pdf->ln();

       //landlord
       $pdf->SetFont('Arial','B',11);
       $pdf->Cell('6',2.5,'');
       $pdf->Cell(48,2.5,$landlord_title);
       $pdf->SetFont('Arial','',11);
       $pdf->MultiCell(200, 2.5,"{$landlord_txt2}",0,'L' );

       //compass index
       if( $property_details['compass_index_num'] != '' ){
           $pdf->ln();
           $pdf->SetFont('Arial','B',11);
           $pdf->Cell('6',2.5,'');
           $pdf->Cell(48,2.5,'INDEX NO.: ');
           $pdf->SetFont('Arial','',11);
           $pdf->MultiCell(200, 2.5,"{$property_details['compass_index_num']}",0,'L' );
       
       }

       $pdf->ln();
     
       //work order
       $pdf->SetFont('Arial','B',11);
       $pdf->Cell('6',2.5,'');
       $pdf->Cell(48,2.5,'WORK ORDER: ');
       $pdf->SetFont('Arial','',11);
       $pdf->MultiCell(200, 2.5,"{$workorder_txt}",0,'L' );
        #cell end
        # second row end
        $pdf->ln(10);
               
         ## --------------------NEW HEADING END----------------------



        $currYTT = $pdf->GetY();
        
       /* if($box1_h>$box2_h){
            $pdf->SetY($box1_h);
        }else{
            $pdf->SetY($box2_h);
        }*/
        
        $pdf->Ln();
        
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        $pdf->SetY($currYTT);
       /* $pdf->SetLineWidth(0.4);
        $pdf->Line($currx, $curry, $currx + 190, $curry);
        $pdf->Ln(1.5); */
        
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(15,5,"Qty");
        $pdf->Cell(40,5,"Item");
        $pdf->Cell(85,5,"Description");
        $pdf->Cell(25,5,"Unit Price");
        $pdf->Cell(25,5,"Total Amount");
        $pdf->SetFont('Arial','',11); //reset bold to regular font
        $pdf->Ln();
       
       // $pdf->Cell(141,5,"");
       // $pdf->Cell(27.5,5,"Inc. GST");
       // $pdf->Cell(25,5,"Inc. GST");
        $pdf->Ln(1);
      
        /*
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        $pdf->SetLineWidth(0.4);
        $pdf->Line($currx, $curry, $currx + 190, $curry);
        $pdf->Ln(5);
        */

        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        //$pdf->SetDrawColor(255,0,0);
        $pdf->SetDrawColor(200,38,47); //sats red
        $pdf->SetLineWidth(0.4);
        $pdf->Line($currx, $curry, $currx + 190, $curry);
        $pdf->Ln(5);
        $pdf->SetDrawColor(0,0,0); //reset line color to black
        
        
        // get service
        $os_sql = $this->job_functions_model->getService($job_details['jservice']); 
        $os = $os_sql->row_array();
        
        ## price variation tweak
        if(  $this->system_model->check_price_increase_excluded_agency($property_details['agency_id']) ){ ## Normal Price
            $dynamicPrice = $job_details['job_price']; ##orig price use for orig calculation
            $dynamic_price_total =  $job_details['job_price']; ## user for +|- variation total
        }else{
            $tt_params = array(
                'service_type' => $job_details['jservice'],
                'property_id' => $property_details['property_id'],
                'job_id' => $job_details['id']
            );
            //$tt_price = $this->system_model->get_job_variation($tt_params);
            $tt_price = $this->system_model->get_job_variations_v2($tt_params);
            $dynamicPrice = $tt_price['total_price_including_variations']; ##orig price use for orig calculation
            $dynamic_price_total = $tt_price['dynamic_price_total_display_on']; ## user for +|- variation total
        }
        ## price variation tweak end

        # Add Job Type
        $pdf->Cell(15,5,"1", 0, 0, 'C');
        $pdf->Cell(40,5,$job_details['job_type']);
        $pdf->Cell(85,5,$os['type']);
        //$pdf->Cell(19,5,"$".number_format($job_details['job_price'], 2), 0, 0, 'R');
        //$pdf->Cell(31,5,"$".number_format($tt_price['dynamic_price'], 2), 0, 0, 'R');
        $pdf->Cell(19,5,"$".number_format($dynamic_price_total, 2), 0, 0, 'R');
        $pdf->Cell(31,5,"$".number_format($dynamic_price_total, 2), 0, 0, 'R');
        $pdf->Ln(5);

        //$grand_total = $job_details['job_price'];
        $grand_total = $dynamicPrice;

        ## new row for price variations
        if( $tt_price && !empty( $tt_price['display_var_arr'] ) ){
            
            foreach( $tt_price['display_var_arr'] as $tt_awa )
            {

                if( $tt_awa['type'] == 1 ){
                    $price_var_format = "(-$".$tt_awa['amount'].")";
                }else{
                    $price_var_format = "+$".$tt_awa['amount'];
                }

                $pdf->Cell(15,5,"1", 0, 0, 'C');
                $pdf->Cell(40,5,$tt_awa['item']);
                $pdf->Cell(85,5,$tt_awa['description']);
                $pdf->Cell(19,5,$price_var_format, 0, 0, 'R');
                $pdf->Cell(31,5,$price_var_format, 0, 0, 'R');
                $pdf->Ln(5);

                /*if( $tt_awa['type'] == 1 ){
                    $grand_total -= $tt_awa['amount'];
                }else{
                    $grand_total += $tt_awa['amount'];
                }*/
                
            }    
            
        }
        ## new row for price variations end
       
        

        $pdf->Ln();
        
        
        // installed alarm
        for($x = 0; $x < $num_alarms; $x++)
        {
            if($alarm_details[$x]['new'] == 1)
            {
                #$pdf->Cell(25,5,$alarm_details[$x]['alarm_pwr']);
                #$pdf->Cell(35,5,$alarm_details[$x]['alarm_type']);
                #$pdf->Cell(25,5,"Expiry");
                #$pdf->Cell(35,5,$alarm_details[$x]['expiry']);
                #$pdf->Ln();
                
                $pdf->SetFont('Arial','',11);
                $pdf->Cell(15,5,"1", 0, 0, 'C');
                $pdf->Cell(40,5,$alarm_details[$x]['alarm_pwr']);
                $pdf->Cell(85,5,"Supply & Install " . $alarm_details[$x]['alarm_type'] . " Smoke Alarm");
                $pdf->Cell(19,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
                $pdf->Cell(31,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
                $pdf->Ln();
                
                $pdf->SetFont('Arial','I',11);
                $pdf->Cell(15,5,"", 0, 0, 'C');
                $pdf->Cell(40,5,"");
                $pdf->SetTextColor(255, 0, 0); // red
                $pdf->Cell(85,5,"Reason: " . $alarm_details[$x]['alarm_reason']);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(19,5,"", 0, 0, 'R');
                $pdf->Cell(31,5,"", 0, 0, 'R');
                $pdf->Ln();
                
                $grand_total += $alarm_details[$x]['alarm_price'];
            }
        }
        
        // surcharge
        $sc_sql = $this->db->query("
            SELECT *, m.`name` AS m_name 
            FROM `agency_maintenance` AS am
            LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
            WHERE am.`agency_id` = {$property_details['agency_id']}
            AND am.`maintenance_id` > 0
        ");
        $sc = $sc_sql->row_array();
        if( $grand_total!=0 && $sc['surcharge']==1 ){
            
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(15,5,"1", 0, 0, 'C');
            $pdf->Cell(45,5,$sc['m_name']);
            $surcharge_txt = ($sc['display_surcharge']==1)?$sc['surcharge_msg']:'';
            $pdf->Cell(80,5,$surcharge_txt);
            $pdf->Cell(19,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
            $pdf->Cell(31,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
            $pdf->Ln();
            
            $grand_total += $sc['price'];
            
        }
        
        
        // CREDITS
        $credit_sql = $this->db->query("
            SELECT *
            FROM `invoice_credits` AS ic 
            WHERE ic.`job_id` = {$job_id}
        ");
        // while( $credit = mysql_fetch_array($credit_sql) ){
        foreach($credit_sql->result_array() as $credit){

            $item_credit_text = ($credit['credit_paid']<0) ? 'Credit - Reversal' : 'Credit' ;
            $credit_paid = ( $credit['credit_paid']<0 ) ? '$'.number_format(abs($credit['credit_paid']),2) : "$".number_format($credit['credit_paid'], 2) ;
            
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(15,5,"1", 0, 0, 'C');
            $pdf->Cell(45,5,$item_credit_text);
            $pdf->SetFont('Arial','I',11);
            $pdf->SetTextColor(255, 0, 0); // red
            $pdf->Cell(80,5,'Reason: '.$this->getInvoiceCreditReason($credit['credit_reason']));
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial','',11);
            /*
            $pdf->Cell(19,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
            $pdf->Cell(31,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
            */
            $pdf->Cell(19,5,'('.$credit_paid.')', 0, 0, 'R');
		    $pdf->Cell(31,5,'('.$credit_paid.')', 0, 0, 'R');


            $pdf->Ln();
            
            $grand_total -= $credit['credit_paid'];
            
        }
        
        
        
        //getServiceIncludesDesc($pdf,$job_details['job_type'],$job_details['jservice']);

        
        
        
        /*
        if($num_alarms > 0)
        {   
            $pdf->Cell(160, 5, $job_details['job_type'].' Includes:');
            $pdf->SetFont('Arial','',10);
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Surveying the quantity and location of smoke alarms');
            $pdf->Cell(160, 5, '* Inspecting alarms for secure fitting');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Replacing batteries in all alarms with replaceable batteries');
            $pdf->Cell(160, 5, '* Cleaning alarms with an anti-static wipe');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Testing alarms with the manual test button');
            $pdf->Cell(160, 5, '* Verifying expiry dates on all alarms');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Checking alarms for audible notification');
            $pdf->Cell(160, 5, '* Checking alarms for visual indicators');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Checking alarms meet Australian Standards');
            $pdf->Cell(160, 5, '* The recording of all details in SATS database');
            $pdf->SetFont('Arial','',11);
        }
        */


        # Old Format
        /*
        $pdf->MultiCell(185,5,'Annual Maintenance Includes:
    * Surveying the quantity and location of smoke alarms
    * Inspecting alarms for secure fitting
    * Cleaning alarms with an anti-static wipe
    * Replacing batteries in all alarms with replaceable batteries
    * Testing alarms with the manual test button
    * Verifying expiry dates on all alarms
    * Checking alarms for audible notification
    * Checking alarms for visual indicators
    * Checking alarms meet Australian Standards
    * The recording of all details in SATS database');
        */
         
        $pdf->Ln(5);
        
        // get country
        $c_sql = $this->db->query("
            SELECT *
            FROM `agency` AS a
            LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
            WHERE a.`agency_id` = {$property_details['agency_id']}
        ");
        $c = $c_sql->row_array();
        
        // gst
        if($c['country_id']==1){
            $gst = $grand_total / 11;
        }else if($c['country_id']==2){
            $gst = ($grand_total*3) / 23;
        }   
        
        /*
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        $pdf->SetLineWidth(0.4);
        $pdf->Line($currx, $curry, $currx + 190, $curry);
        $pdf->Ln(5);
        */
        
        // get cursor position
        $cursor_y = $pdf->GetY();
        
        //SUB TOTAL
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = 'Sub Total';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        $pdf->Cell(31,5,"$" . number_format($grand_total - ($gst), 2), 0, 0, 'R');
        $pdf->Ln();

        //GST
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = 'GST';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        $pdf->Cell(31,5,"$" . number_format($gst, 2), 0, 0, 'R');   
        $pdf->Ln();

        //Total
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = 'Total';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        $pdf->Cell(31,5,"$" . number_format($grand_total, 2), 'B', 0, 'R');
        $pdf->Ln();

        // Payments/Credits
        $pdf->Cell(140,10,"", 0, 0, 'C');
        $text = 'Payments';
        //$pdf->SetTextColor(255, 0, 0); // red
        $pdf->Cell(25,10,$text, 0, 0, 'R');
        $pdf->SetFont('Arial','B',12);
        $inv_payments = $grand_total - $job_details['invoice_balance'];
        $pdf->Cell(25,10,'($'.number_format($inv_payments, 2).')', 0, 0, 'R');
        //$pdf->SetTextColor(0, 0, 0); // clear red
        $pdf->Ln();
        
        
        // balance
        $pdf->SetFont('Arial','I',10);
        $pdf->Cell(140,10,"", 0, 0, 'C');
        $text = 'Amount Owing';
        $pdf->Cell(25,10,$text, 0, 0, 'R');
        $pdf->SetFont('Arial','B',12);
        $inv_balance = ( is_numeric($job_details['invoice_balance']) )?$job_details['invoice_balance']:$grand_total;
        $pdf->Cell(25,10,'$'.number_format($inv_balance, 2), 0, 0, 'R');
        $pdf->Ln(15);
        

        // BPAY AU only
        $tt_x_for_no_bpay = $pdf->GetX();
        $tt_y_for_no_bpay = $pdf->GetY();
        if( $c['country_id']==1 && $job_details['display_bpay']==1 ){
            $pdf->Ln(1);

            // BPAY logo    
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/bpay/bpay_does_not_accept_credit_card.jpg',17,null,60);
            
            $tt_y = $pdf->GetY(); //current vertical position after QR image
            $tt_x = $pdf->GetX();

            // set font 
            $pdf->SetFont('Helvetica','',11);
            $pdf->SetTextColor(24, 49, 104); // blue
            
            // Biller Code
            /*$bpay_x = $x_pos+86;
            $bpay_y = $cursor_y+45.5;
            */
            $bpay_x = $pdf->GetX()+43;
            $bpay_y =  $pdf->GetY()-27.5;
            $pdf->SetXY($bpay_x,$bpay_y);   
            $biller_code = '264291';
            $pdf->Cell(15,5,$biller_code, 0, 0, 'R');
            
            // Ref Code
            $pdf->SetXY($bpay_x,$bpay_y+4.5);
            //$ref_code = str_pad($job_id, 12, "0", STR_PAD_LEFT);  
            //$check_digit = getCheckDigit($job_id);
            //$bpay_ref_code = "{$job_id}{$check_digit}";
            $pdf->Cell(15,5,$bpay_ref_code, 0, 0, 'R');
            
            $pdf->SetTextColor(0, 0, 0);
            
            // $pdf->SetXY($x_pos+62,$cursor_y);
            
            //$x_pos += 62; 
            
        }
    

        ## Bank Details
        // $x_pos = 16;
        // $pdf->SetXY($x_pos,(($cursor_y)-1.3)); 
        /*$add_x = 80; 
        $pdf->SetXY($tt_x+$add_x,$tt_y-33);  
        $pdf->SetFont('Arial','',10);
        */
        if( $c['country_id']!=1 || ($job_details['display_bpay']!=1 && COUNTRY==1) ){
            $add_x = 15; 
            $pdf->SetXY($tt_x_for_no_bpay+5,$tt_y_for_no_bpay);  
        }else{
            $add_x = 80; 
            $pdf->SetXY($tt_x+$add_x,$tt_y-33);  
        }


        $c_bank = $c['bank'];   
        $c_ac_name = $c['ac_name'];
        $c_ac_number = $c['ac_number'];
        
        if($c['country_id']!=2){
            $c_bsb = $c['bsb']; 
            //$pdf->MultiCell(55,5,"Direct Deposit Details:
            //$pdf->MultiCell(55,5,"Direct Deposit Details:
            $pdf->SetFont('Arial','B',10);
            $pdf->cell(55,5,"Direct Deposit Details:",0,1,'L','','');
            $pdf->SetFont('Arial','',10); //reset
            $pdf->SetX($tt_x+$add_x);
            $pdf->MultiCell(100,5,"Name: {$c_ac_name}",0,'L');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"Bank: {$c_bank}",0,1,'L','','');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"BSB: {$c_bsb}",0,1,'L','','');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"Account #: {$c_ac_number}",0,1,'L','','');
        /*  $pdf->MultiCell(55,5,"Name: {$c_ac_name}
            Bank: {$c_bank} 
            BSB: {$c_bsb}
            Account #: {$c_ac_number}
            ",0,'L');*/

        }else{
            
        //$pdf->MultiCell(55,5,"Direct Deposit Details:
        /*$pdf->MultiCell(55,5,"Direct Deposit Details:
        Name: {$c_ac_name}
        Bank: {$c_bank} 
        Account #: {$c_ac_number}
        ",0,'L');
            */
            $pdf->SetFont('Arial','B',10);
            $pdf->cell(55,5,"Direct Deposit Details:",0,1,'L','','');
            $pdf->SetFont('Arial','',10); //reset
            $pdf->SetX($tt_x+$add_x);
            $pdf->MultiCell(100,5,"Name: {$c_ac_name}",0,'L');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"Bank: {$c_bank}",0,1,'L','','');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"Account #: {$c_ac_number}",0,1,'L','','');
        } 
          


        // Reference No.
        $pdf->SetX($tt_x+$add_x);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(22,5,'Reference #: ');
        $pdf->SetTextColor(255, 0, 0); // red
        $pdf->Cell(11,5,$bpay_ref_code,0,1);
        $pdf->SetTextColor(0, 0, 0); // clear red
        $pdf->SetFont('Arial','',10);

        $pdf->Cell(41,5,'');  //dummy
       /* 
        $pdf->SetX($x_pos);
        $pdf->Cell(11,5,'Term:');
        $pdf->SetFont('Arial','U',10);
        $pdf->Cell(30,5,'NET 30 Days'); 
        */
        ## Bank Details End
       
        
        // generate QR code
        /* disabled as per Ness request Feb1,2022
        $qr_code_path = APPPATH . 'third_party/phpqrcode/temp/invoice_'.$bpay_ref_code.'_qr_code.png';
        $qr_code = $this->functions_model->generate_qr_code($bpay_ref_code,$job_details['property_id'],number_format($grand_total, 2),number_format($gst, 2),$job_details['date'],$country_id);
        QRcode::png($qr_code['data'], $qr_code['path'],'L',2);

        // display QR code  
        $pdf->Image($qr_code_path,null,null);
        // delete qr image
        unlink($qr_code_path);
        $pdf->SetFont('Arial','',6.5);
        $pdf->Cell(24,2,'getpaidfaster.com.au',0,0); 
        */
        
        
       # $x_pos = $pdf->getX();
        // Direct Deposit Details
        //$pdf->SetXY($x_pos,($cursor_y+1));
       # $pdf->SetXY($x_pos+50,$cursor_y+40.5);
       # $pdf->SetFont('Arial','',10);
        
       
        
        
        #$pdf->Ln(10);
        
        /*
        // if agency "Agency Allows up front billing" to yes and job type is YM
        if( $is_upfront_billing == true ){

            $pdf->SetFont('Arial','',10);
            $pdf->cell(190, 5, '* SATS will attend the property for Change of Tenancies and Lease Renewals as instructed by your agency to ensure');
            $pdf->Ln();
            $pdf->cell(190, 5, '  compliance is maintained throughout the subscription period.');
            $pdf->Ln();

        }   
        */

        /*--
        $pdf->SetFont('Arial','',11);
        $pdf->Ln(5);
        $pdf->Cell(160, 5, 'All SATS visits include:'); 
        
        // if bundle, get bundle services id
        $ajt_serv_sql = $this->job_functions_model->getService($job_details['jservice']);    
        $ajt_serv = $ajt_serv_sql->row_array();

        $we_services = $this->system_model->we_services_id();
        
        // if bundle
        if($ajt_serv['bundle']==1){
        
            $bs_sql = $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `id` IN({$ajt_serv['bundle_ids']})
                AND `active` = 1
            ");
            // while($bs = mysql_fetch_array($bs_sql)){
            foreach($bs_sql->result_array() as $bs){
                $pdf->Ln(10);
                $pdf->Cell(160, 5, $bs['type'].': ');

                $country_id2 = $country_id;
                
                # description
                if( ($job_details['job_type']=="Yearly Maintenance" || $job_details['job_type']=="Change of Tenancy" || $job_details['job_type']=="Once-off") && ( $bs['id']==2 || $bs['id']==12 ) ){
                    
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

                
                }else if( $job_details['job_type']=="Fix or Replace" && ( $bs['id']==2 || $bs['id']==12 ) ){
                
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Fix or Replace Problem Alarm');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Conduct Full Test on Alarm');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Record all Details in SATS Database');
                    $pdf->SetFont('Arial','',11);
                
                }else if( $job_details['job_type']=="Lease Renewal" && ( $bs['id']==2 || $bs['id']==12 ) ){
                
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
                
                }else if($job_details['job_type']=="Yearly Maintenance"&&$bs['id']==6){
                
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
                
                }else if($job_details['job_type']=="Fix or Replace"&&$bs['id']==6){
                
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Repair Problem Window Covering');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Conduct Full Survey of all Windows');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Record all Details in SATS Database');
                    $pdf->SetFont('Arial','',11);

                
                }else if($bs['id']==5){
                
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
                
                }else if ( in_array($bs['id'], $we_services) ){ // WE

                                                        
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Check all taps for any leaks');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Check all toilets for Single/Dual Flush');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Check all required taps to verify they meet Water Efficeincy of Less than 9L per minute');
                    $pdf->Ln();             
                
                }

            }

        // not bundle
        }else{
                $country_id2 = $country_id;
                
                # description
                if( ($job_details['job_type']=="Yearly Maintenance" || $job_details['job_type']=="Change of Tenancy" || $job_details['job_type']=="Once-off") && ( $job_details['jservice']==2 || $job_details['jservice']==12 ) ){
                    
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

                
                }else if( $job_details['job_type']=="Fix or Replace" && ( $job_details['jservice']==2 || $job_details['jservice']==12 ) ){
                
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Fix or Replace Problem Alarm');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Conduct Full Test on Alarm');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Record all Details in SATS Database');
                    $pdf->SetFont('Arial','',11);
                
                }else if( $job_details['job_type']=="Lease Renewal" && ( $job_details['jservice']==2 || $job_details['jservice']==12 ) ){
                
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
                
                }else if($job_details['job_type']=="Yearly Maintenance"&&$job_details['jservice']==6){
                
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
                
                }else if($job_details['job_type']=="Fix or Replace"&&$job_details['jservice']==6){
                
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Repair Problem Window Covering');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Conduct Full Survey of all Windows');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Record all Details in SATS Database');
                    $pdf->SetFont('Arial','',11);

                
                }else if($job_details['jservice']==5){
                
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
                
                }else if ( in_array($job_details['jservice'], $we_services) ){ // WE

                                                        
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Check all taps for any leaks');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Check all toilets for Single/Dual Flush');
                    $pdf->Ln();
                    $pdf->Cell(108, 5, '* Check all required taps to verify they meet Water Efficeincy of Less than 9L per minute');
                    $pdf->Ln();             
                
                }

        }
        --*/
       /* 
        $curr_y = $pdf->GetY(); 
        $pdf->SetY($curr_y+10);
        $pdf->SetFont('Arial','',10);

        switch($c['country_id']){
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
*/
        /*
        // smoke alarms
        if( $job_details['jservice'] == 2 || $job_details['jservice'] == 12 ){
            
            if( $job_details['country_id']==1 ){ // AU
                $pdf->MultiCell(185,5,'All Smoke Alarms Located within the Property as detailed above have been cleaned and tested as per manufacturers instructions and been installed in accordance with '.$country_text.' Standard AS 3786 (2014) Smoke Alarms, Building Code of '.$c['country'].', Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations.'); 
            }else if( $job_details['country_id']==2 ){ // NZ
                $pdf->MultiCell(185,5,'All Smoke Alarms Located within the Property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations.'); 
            }  
            $pdf->Ln(2);
            
        }
        */

        // if service type is IC dont show, only show for non-IC services
        $pdf->SetY($pdf->GetY()+20);
        $pdf->SetX($pdf->GetX()+5);

        $ic_service = $this->system_model->getICService();

        if(in_array($job_details['jservice'], $ic_service)){
            $ic_check = 1;
        }else{
            $ic_check = 0;
        }

        if( $ic_check == 0 && $job_details['state'] == 'QLD' && $job_details['qld_new_leg_alarm_num']>0 && $job_details['prop_upgraded_to_ic_sa'] != 1 ){

          # $pdf->Ln(10);

            if( $job_details['assigned_tech']!=NULL &&  $job_details['assigned_tech']!=1 && $job_details['assigned_tech']!=2){

                $pdf->SetTextColor(0, 0, 204);              
                // QUOTE
                $quote_qty = $job_details['qld_new_leg_alarm_num'];
                $price_240vrf = $this->get240vRfAgencyAlarm($property_details['agency_id']);
                $quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
                $quote_total = $quote_price*$quote_qty;
                //$pdf->MultiCell(185,5,'We have provided a quote for $'.$quote_total.' to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details['date']).'+90 days')).' and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
                $pdf->MultiCell(185,5,'We have provided a quote to upgrade this property to meet the NEW QLD 2022 legislation. This quote is valid until 21/04/2022 and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
                $pdf->SetTextColor(0, 0, 0);
            
            }

        }

        if ($output == "") {
            return $pdf->Output('','S');
        }else {
            return $pdf;
        }

   }
   

   public function pdf_quote_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, $output = "", $is_copy = false,$qt){

        //$pdf = new JPDF();

        // quote type
        $qt = ( $qt != '' )?$qt:'brooks';
        

        #instantiate only if required
        if(!isset($pdf)) {
            
            /*
            //$pdf=new FPDF('P','mm','A4');
            //include('fpdf_override.php');
            $pdf=new jPDF('P','mm','A4');
            $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
            $pdf->setCountryData($job_details['country_id']);
            */


            $pdf=new jPDI();
            //$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
            //$pdf->setCountryData($job_details['country_default']);

        }

        $pdf->SetTopMargin(40);
        $pdf->SetAutoPageBreak(true,50);
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
        
        
        
        
        // space needed to fit envelope
        //$pdf->Cell(20,10,'');
        
        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}";
        
        

        $pdf->SetFont('Arial','',11); 
       
        // $pdf->Cell(0,15,'',0,1,'C');
        // $pdf->Cell(0,5,'A.B.N.    48 160 538 741',0,1,'');
       
        // $pdf->Cell(0,10,'',0,1,'C');
        //$pdf->Ln(18);

        
        $pdf->SetX(30);
        
        $pdf->Cell(65.5,5,'Quote Date:   ' . $job_details['date']); 
        
        $pdf->SetFont('Arial','B',14);
        
        if(isset($job_details['tmh_id']))
        {
            $pdf->Cell(100,5,'Quote    #' . str_pad($job_details['tmh_id'] . ' TMH-Q', 6, "0", STR_PAD_LEFT),0,1,'C');
        }
        else
        {
            $pdf->Cell(100,5,'Quote    #' . $bpay_ref_code.'Q',0,1,'C');
        }

        $pdf->SetFont('Arial','',11);
        // $pdf->Cell(40,5,'Quote #' . str_pad($job_id, 6, "0", STR_PAD_LEFT)); 
        
        $pdf->Ln(5);
       
        # Agent Details
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        
        // space needed to fit envelope
        $pdf->Cell(20,10,'');

        ##fix for NZ macron char in address issue 
        setlocale(LC_CTYPE, 'en_US');
        $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $property_details['address_1']." ".$property_details['address_2']);
        $incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $property_details['address_3']." ".$property_details['state']." ".$property_details['postcode']);
        
        
        # Hack for LJ Hooker Tamworth - display Landlord in different spot for them
        if($property_details['agency_id'] == 1348) {
            $pdf->MultiCell(90, 5, "ATTN: " . ( ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' )?"{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}":'CARE OF THE OWNER' ) . "\n" . "\nC/- {$property_details['agency_name']}" . "\n" . trim($property_details['a_address_1']). " " . trim($property_details['a_address_2']) . "\n" . trim($property_details['a_address_3']) . " " . $property_details['a_state'] . " " . $property_details['a_postcode'] . "\n\n\n");
            $pdf->SetY($curry);
            $pdf->SetX(124);
            //$pdf->MultiCell(85, 5, "PROPERTY ADDRESS:" . "\n"  . $property_details['address_1']. " " . $property_details['address_2'] . "\n" . $property_details['address_3'] . " " . $property_details['state'] . " " . $property_details['postcode'] . "\n\n" . ( ($property_details['landlord_firstname']!='')?"LANDLORD: {$property_details['landlord_firstname']} {$property_details['landlord_lastname']}\n\n":"" )  );
            $pdf->MultiCell(85, 5, "PROPERTY ADDRESS:" . "\n"  . $incov_val1 . "\n" . $incov_val2 . "\n\n" . ( ($property_details['landlord_firstname']!='')?"LANDLORD: {$property_details['landlord_firstname']} {$property_details['landlord_lastname']}\n\n":"" )  );
            $pdf->Ln(6);
        }
        else
        {
            $pdf->MultiCell(90, 5, "ATTN: ". ( ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' )?"{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}":'CARE OF THE OWNER' ) . "\nC/- {$property_details['agency_name']}" . "\n" . $property_details['a_address_1']. " " . htmlspecialchars_decode($property_details['a_address_2']) . "\n" . $property_details['a_address_3'] . " " . $property_details['a_state'] . " " . $property_details['a_postcode']);
            $pdf->SetY($curry);
            $pdf->SetX(124);
            //$pdf->MultiCell(85, 5, "PROPERTY ADDRESS:" . "\n"  . $property_details['address_1']. " " . $property_details['address_2'] . "\n" . $property_details['address_3'] . " " . $property_details['state'] . " " . $property_details['postcode'] . "\n\n" . ( ($property_details['landlord_firstname']!='')?"LANDLORD: {$property_details['landlord_firstname']} {$property_details['landlord_lastname']}\n\n":"" )  );
            $pdf->MultiCell(85, 5, "PROPERTY ADDRESS:" . "\n"  . $incov_val1 . "\n" . $incov_val2 . "\n\n" . ( ($property_details['landlord_firstname']!='')?"LANDLORD: {$property_details['landlord_firstname']} {$property_details['landlord_lastname']}\n\n":"" )  );
                
        }
        
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial','',10);

        $pdf->Cell(190, 5, 'This quote is to upgrade the above property to meet the new QLD Legislation (effective 1/1/2022).',0,1);
        
        
        $pdf->SetFont('Arial','',11);
        #$pdf->SetX(0);
        
        $pdf->Ln(6);
        
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
        $os_sql = $this->job_functions_model->getService($job_details['jservice']); 
        $os = $os_sql->row_array();
        
        # Add Job Type
        $quote_qty = $job_details['qld_new_leg_alarm_num'];
        if($qt == 'brooks'){
            $price_240vrf = $this->get240vRfAgencyAlarm($property_details['agency_id']);
        }else if($qt == 'cavius'){
            $price_240vrf = $this->get240vRf_cavius_AgencyAlarm($property_details['agency_id']);
        }else if($qt == 'emerald'){
            $price_240vrf = $this->get_emerald_AgencyAlarm($property_details['agency_id']);
        }
        
        $ic_alarm_price = $this->get_equivalent_IC_price($job_details['jservice'],$property_details['agency_id']);
        
        $quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
        //$service_price = ( $ic_alarm_price > 0 )?$ic_alarm_price:119;
        
        $quote_total = $quote_price*$quote_qty;
        $pdf->Cell(15,5,$quote_qty, 0, 0, 'C');
        $pdf->Cell(45,5,'Photo-Electric');
        $pdf->Cell(80,5,ucwords($qt).' Interconnected Smoke Alarms');
        $pdf->Cell(19,5,"$".number_format($quote_total, 2), 0, 0, 'R');
        $pdf->Cell(31,5,"$".number_format($quote_total, 2), 0, 0, 'R');
        $pdf->Ln();
        
        $grand_total = $quote_total;
        
        /*
        // installed alarm
        for($x = 0; $x < $num_alarms; $x++)
        {
            if($alarm_details[$x]['new'] == 1)
            {
                #$pdf->Cell(25,5,$alarm_details[$x]['alarm_pwr']);
                #$pdf->Cell(35,5,$alarm_details[$x]['alarm_type']);
                #$pdf->Cell(25,5,"Expiry");
                #$pdf->Cell(35,5,$alarm_details[$x]['expiry']);
                #$pdf->Ln();
                
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
                #$pdf->Cell(25,5,$alarm_details[$x]['alarm_pwr']);
                #$pdf->Cell(35,5,$alarm_details[$x]['alarm_type']);
                #$pdf->Cell(25,5,"Expiry");
                #$pdf->Cell(35,5,$alarm_details[$x]['expiry']);
                #$pdf->Ln();
                
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
        $sc_sql = mysql_query("
            SELECT *, m.`name` AS m_name 
            FROM `agency_maintenance` AS am
            LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
            WHERE am.`agency_id` = {$property_details['agency_id']}
        ");
        $sc = mysql_fetch_array($sc_sql);
        if( $grand_total!=0 && $sc['surcharge']==1 ){
            
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(15,5,"1", 0, 0, 'C');
            $pdf->Cell(45,5,$sc['m_name']);
            $surcharge_txt = ($sc['display_surcharge']==1)?$sc['surcharge_msg']:'';
            $pdf->Cell(80,5,$surcharge_txt);
            $pdf->Cell(19,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
            $pdf->Cell(31,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
            $pdf->Ln();
            
            $grand_total += $sc['price'];
            
        }
        */
        
        
        //getServiceIncludesDesc($pdf,$job_details['job_type'],$job_details['jservice']);

        
        
        
        /*
        if($num_alarms > 0)
        {   
            $pdf->Cell(160, 5, $job_details['job_type'].' Includes:');
            $pdf->SetFont('Arial','',10);
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Surveying the quantity and location of smoke alarms');
            $pdf->Cell(160, 5, '* Inspecting alarms for secure fitting');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Replacing batteries in all alarms with replaceable batteries');
            $pdf->Cell(160, 5, '* Cleaning alarms with an anti-static wipe');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Testing alarms with the manual test button');
            $pdf->Cell(160, 5, '* Verifying expiry dates on all alarms');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Checking alarms for audible notification');
            $pdf->Cell(160, 5, '* Checking alarms for visual indicators');
            $pdf->Ln();
            $pdf->Cell(108, 5, '* Checking alarms meet Australian Standards');
            $pdf->Cell(160, 5, '* The recording of all details in SATS database');
            $pdf->SetFont('Arial','',11);
        }
        */


        # Old Format
        /*
        $pdf->MultiCell(185,5,'Annual Maintenance Includes:
    * Surveying the quantity and location of smoke alarms
    * Inspecting alarms for secure fitting
    * Cleaning alarms with an anti-static wipe
    * Replacing batteries in all alarms with replaceable batteries
    * Testing alarms with the manual test button
    * Verifying expiry dates on all alarms
    * Checking alarms for audible notification
    * Checking alarms for visual indicators
    * Checking alarms meet Australian Standards
    * The recording of all details in SATS database');
        */
         
        $pdf->Ln(15);
        
        // get country
        $c_sql = $this->db->query("
            SELECT *
            FROM `agency` AS a
            LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
            WHERE a.`agency_id` = {$property_details['agency_id']}
        ");
        $c = $c_sql->row_array();
        
        // gst
        if($c['country_id']==1){
            $gst = $grand_total / 11;
        }else if($c['country_id']==2){
            $gst = ($grand_total*3) / 23;
        }   
        
        $pdf->MultiCell(190, 5, 'All current smoke alarms at the above address will be removed and replaced as part of this quote. This is to ensure all alarms will interconnect and no warranties will be void due to manufacturers recommendations and/or specifications.',0,1);
        $pdf->Ln(2);

        if($qt == 'emerald'){
            $pdf->MultiCell(190, 5, 'Emerald Planet alarms installed by SATS carry a 7 year manufacturers warranty.',0,1);
        }else{
            //$pdf->MultiCell(190, 5, 'All alarms detailed in this quote that are installed by SATS carry a manufacturers warranty of 5 Years and SATS offer an Additional 5 years warranty whilst part of a SATS service agreement.',0,1);
            $pdf->MultiCell(190, 5, 'All alarms detailed in this quote that are installed by SATS carry a manufacturers warranty of 10 Years whilst part of a SATS service agreement.',0,1);
        }
        
        $pdf->Ln(2);
        
        $pdf->Ln(2);
        $pdf->SetFont('Arial','I',10);
        if( $ic_alarm_price > 0 ){
            $pdf->Cell(190, 5, '*After the upgrade has been carried out the Annual Maintenance fee will be $'.$ic_alarm_price.' incl GST.',0,1,'C');
        }else{
            $pdf->Cell(190, 5, '*Please refer to your Property Manager to confirm pricing',0,1,'C');
        }
        
        $pdf->Ln(2);
        
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        $pdf->SetLineWidth(0.4);
        $pdf->Line($currx, $curry, $currx + 190, $curry);
        $pdf->Ln(5);
        
        // get cursor position
        $cursor_y = $pdf->GetY();
        
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = ""; //ADDED SO THAT IT WILL NOT RETURN ERROR ON CRMCI UNLINE OLD CRM
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
        //$text = 'Quote Total';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        $pdf->Cell(31,5,"$" . number_format($grand_total, 2), 0, 0, 'R');
        $pdf->Ln();
        
        
        
        $x_pos = 10;
        $pdf->SetXY($x_pos,$cursor_y);  
            
        $pdf->SetFont('Arial','',10);

        /*
        if( $property_details['agency_id'] == 5229 ){
            $quote_valid_until_ts = strtotime(str_replace('/','-',$job_details['date']).'+400 days');
        }else{
            $quote_valid_until_ts = strtotime(str_replace('/','-',$job_details['date']).'+90 days');
        }

        $pdf->Cell(190, 5, 'Quote valid until '.date("d/m/Y",$quote_valid_until_ts),0,1);
        */

        // as per ben's order to put "quote valid until" to static 31/10/21
        // updated to 30/11/21
        $pdf->Cell(190, 5, 'Quote valid until 30/11/21',0,1);
        
        $pdf->Ln(30);
        $pdf->SetFont('Arial','',14);


        if( $job_details['prop_upgraded_to_ic_sa'] == 1 ){ // upgraded = yes

            $pdf->Cell(190, 5, 'No Upgrade required. Property meets NEW QLD Legislation',0,1,'C');

        }else{ //  upgraded = no

            $pdf->Cell(190, 5, 'To go ahead with the above quote please issue a work order to SATS',0,1,'C');

        }  


        if ($output == "") {
            return $pdf->Output('','S');
        }else {
            return $pdf;
        }

        
   }


   public function pdf_combined_quote_template($params){

        $job_id = $params['job_id'];
        $job_details = $params['job_details'];
        $property_details = $params['property_details'];        
        $pdf_name = $params['pdf_name'];
        $pdf_output = $params['pdf_output'];
        
        #instantiate only if required
        if(!isset($pdf)) {
            $pdf=new jPDI();            
        }

        $pdf->SetTopMargin(40);
        $pdf->SetAutoPageBreak(true,30);
        $pdf->AddPage();        
        
        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}";                

        $pdf->SetFont('Arial','',11);             
        $pdf->SetX(30);        
        $pdf->Cell(65.5,5,'Quote Date:   ' . $job_details['date']);         
        $pdf->SetFont('Arial','B',14);
        
        if(isset($job_details['tmh_id']))
        {
            $pdf->Cell(100,5,'Quote    #' . str_pad($job_details['tmh_id'] . ' TMH-Q', 6, "0", STR_PAD_LEFT),0,1,'C');
        }
        else
        {
            $pdf->Cell(100,5,'Quote    #' . $bpay_ref_code.'Q',0,1,'C');
        }

        $pdf->SetFont('Arial','',11);        
        
        $pdf->Ln(5);
    
        # Agent Details
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        
        // space needed to fit envelope
        $pdf->Cell(20,10,'');
        
        # Hack for LJ Hooker Tamworth - display Landlord in different spot for them
        if($property_details['agency_id'] == 1348) {
            $pdf->MultiCell(90, 5, "ATTN: " . ( ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' )?"{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}":'CARE OF THE OWNER' ) . "\n" . $property_details['landlord_firstname'] . " " . $property_details['landlord_lastname'] . ( ( $property_details['landlord_firstname']=='' && $property_details['landlord_lastname']=='' )?"\nC/- {$property_details['agency_name']}":"" ) . "\n" . trim($property_details['a_address_1']). " " . trim($property_details['a_address_2']) . "\n" . trim($property_details['a_address_3']) . " " . $property_details['a_state'] . " " . $property_details['a_postcode'] . "\n\n\n");
            $pdf->SetY($curry);
            $pdf->SetX(124);
            $pdf->MultiCell(85, 5, "PROPERTY ADDRESS:" . "\n"  . $property_details['address_1']. " " . $property_details['address_2'] . "\n" . $property_details['address_3'] . " " . $property_details['state'] . " " . $property_details['postcode'] . "\n\n" . ( ($property_details['landlord_firstname']!='')?"LANDLORD: {$property_details['landlord_firstname']} {$property_details['landlord_lastname']}\n\n":"" )  );
            $pdf->Ln(6);
        }
        else
        {
            $pdf->MultiCell(90, 5, "ATTN: ". ( ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' )?"{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}":'CARE OF THE OWNER' ) . ( ( $property_details['landlord_firstname']=='' && $property_details['landlord_lastname']=='' )?"\nC/- {$property_details['agency_name']}":"" ) . "\n" . $property_details['a_address_1']. " " . htmlspecialchars_decode($property_details['a_address_2']) . "\n" . $property_details['a_address_3'] . " " . $property_details['a_state'] . " " . $property_details['a_postcode']);
            $pdf->SetY($curry);
            $pdf->SetX(124);
            $pdf->MultiCell(85, 5, "PROPERTY ADDRESS:" . "\n"  . $property_details['address_1']. " " . $property_details['address_2'] . "\n" . $property_details['address_3'] . " " . $property_details['state'] . " " . $property_details['postcode'] . "\n\n" . ( ($property_details['landlord_firstname']!='')?"LANDLORD: {$property_details['landlord_firstname']} {$property_details['landlord_lastname']}\n\n":"" )  );
                
        }
        
        $pdf->Ln(10);        
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(190, 5, 'This quote is to upgrade the above property to meet the new QLD Legislation (effective 1/1/2022).',0,1);                                     
        $pdf->Ln(6);
        
        // brooks
        $quote_qty = $job_details['qld_new_leg_alarm_num'];
        $price_240vrf_brooks_price = $this->get240vRfAgencyAlarm($property_details['agency_id']);
        $price_240vrf_brooks_price_final = ( $price_240vrf_brooks_price > 0 )?$price_240vrf_brooks_price:$this->config->item('default_qld_upgrade_quote_price');
        $quote_total_brooks = $price_240vrf_brooks_price_final*$quote_qty;

        $pdf->SetFont('Arial','',10); 
        $pdf->SetTextColor(255, 0, 0); // red
        $pdf->Cell(190, 5, 'OPTION 1',0,1); 
        $pdf->SetTextColor(0, 0, 0); // clear red
        $pdf->Ln(2);
        $pdf->Cell(190, 5, 'To supply and install',0,1); 
        $pdf->Ln(2);
        $pdf->Cell(190, 5, "{$quote_qty} x Brooks Interconnected Photo Electric Smoke Alarms @ \$".number_format($price_240vrf_brooks_price_final,2)." EA = \$".number_format($quote_total_brooks,2)." Inc. GST",0,1); 
        $pdf->Ln(2);
        $pdf->SetFont('Arial','I',9);
        $pdf->Cell(190, 5, 'Made in Europe - For further information please visit www.brooks.com.au',0,1,'C');
        
        $pdf->Ln(10);

        // cavius
        $quote_qty = $job_details['qld_new_leg_alarm_num'];
        $price_240vrf_cavius_price = $this->get240vRf_cavius_AgencyAlarm($property_details['agency_id']);
        $price_240vrf_cavius_price_final = ( $price_240vrf_cavius_price > 0 )?$price_240vrf_cavius_price:$this->config->item('default_qld_upgrade_quote_price');
        $quote_total_cavius = $price_240vrf_cavius_price_final*$quote_qty;

        $pdf->SetFont('Arial','',10); 
        $pdf->SetTextColor(255, 0, 0); // red
        $pdf->Cell(190, 5, 'OPTION 2',0,1); 
        $pdf->SetTextColor(0, 0, 0); // clear red
        $pdf->Ln(2);
        $pdf->Cell(190, 5, 'To supply and install',0,1); 
        $pdf->Ln(2);
        $pdf->Cell(190, 5, "{$quote_qty} x Cavius Interconnected Photo Electric Smoke Alarms @ \$".number_format($price_240vrf_cavius_price_final,2)." EA = \$".number_format($quote_total_cavius,2)." Inc. GST",0,1); 
        $pdf->Ln(2);
        $pdf->SetFont('Arial','I',9);
        $pdf->Cell(190, 5, 'For further information please visit www.cavius.com.au',0,1,'C'); 

        $pdf->Ln(10);

        // emerald
        $quote_qty = $job_details['qld_new_leg_alarm_num'];
        $price_240vrf_emerald_price = $this->get_emerald_AgencyAlarm($property_details['agency_id']);
        $price_240vrf_emerald_price_final = ( $price_240vrf_emerald_price > 0 )?$price_240vrf_emerald_price:$this->config->item('default_qld_upgrade_quote_price');
        $quote_total_cavius = $price_240vrf_emerald_price_final*$quote_qty;

        $pdf->SetFont('Arial','',10); 
        $pdf->SetTextColor(255, 0, 0); // red
        $pdf->Cell(190, 5, 'OPTION 3',0,1); 
        $pdf->SetTextColor(0, 0, 0); // clear red
        $pdf->Ln(2);
        $pdf->Cell(190, 5, 'To supply and install',0,1); 
        $pdf->Ln(2);
        $pdf->Cell(190, 5, "{$quote_qty} x Emerald Planet Interconnected Photo Electric Smoke Alarms @ \$".number_format($price_240vrf_emerald_price_final,2)." EA = \$".number_format($quote_total_cavius,2)." Inc. GST",0,1); 
        $pdf->Ln(2);
        $pdf->SetFont('Arial','I',9);
        $pdf->Cell(190, 5, 'For further information please visit www.emeraldplanet.com.au',0,1,'C'); 

        $pdf->Ln(10); 

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(190, 5, 'To proceed with this quote, please issue a work order to SATS, stating which Smoke Alarm brand is to be installed.',0,1);
                
        //$pdf->Ln(35);            
        $ic_alarm_price = $this->get_equivalent_IC_price($job_details['jservice'],$property_details['agency_id']);        
        //$service_price = ( $ic_alarm_price > 0 )?$ic_alarm_price:119;
        
        $pdf->SetFont('Arial','',10);  
        /*      
        if( $property_details['agency_id'] == 5229 ){
            $quote_valid_until_ts = strtotime(str_replace('/','-',$job_details['date']).'+400 days');
        }else{
            $quote_valid_until_ts = strtotime(str_replace('/','-',$job_details['date']).'+90 days');
        }
        $pdf->Cell(190, 5, 'Quote valid until '.date("d/m/Y",$quote_valid_until_ts),0,1);
        */

        // as per ben's order to put "quote valid until" to static 31/10/21

        
        $pdf->Ln(5);        
        $pdf->Cell(190, 5, 'Quote valid until 30/11/21.',0,1);
        $pdf->Ln(5);

        
        $pdf->SetFont('Arial','I',10);
        $pdf->MultiCell(190, 5, '*All current smoke alarms at the above address will be removed and replaced as part of this quote. This is to ensure all alarms will interconnect and no warranties will be void due to manufacturers recommendations and/or specifications.',0,1);
        $pdf->Ln(2);       
        $pdf->MultiCell(190, 5, '**Brooks and Cavius alarms installed by SATS carry a manufacturers warranty of 5 years and SATS offer an additional 5 years warranty whilst part of a SATS service agreement.',0,1);
        $pdf->Ln(2);
        $pdf->MultiCell(190, 5, '***Emerald Planet alarms installed by SATS carry a 7 year manufacturers warranty.',0,1);
        $pdf->Ln(2);

        if( $ic_alarm_price > 0 ){
            $pdf->Cell(190, 5, '****After the upgrade has been carried out the Annual Maintenance fee will be $'.$ic_alarm_price.' incl GST.',0,1);
        }else{
            $pdf->Cell(190, 5, '****Please refer to your Property Manager to confirm pricing.',0,1);
        }        

        if( $pdf_output == 'S' ){
            return $pdf->Output('', $pdf_output);
        }else{
            return $pdf->Output($pdf_name, $pdf_output);
        }
        
        
    }


   public function pdf_certificate_template($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, $output = "", $is_copy = false){

    //$pdf = new JPDF();

    $this->updateInvoiceDetails($job_id);

    // get service
    $os_sql = $this->job_functions_model->getService($job_details['jservice']); 
    $os = $os_sql->row_array();
    $property_job_types = $this->job_functions_model->getTechSheetAlarmTypesJob($job_details['property_id'], true);

    #instantiate only if required
    if(!isset($pdf)) {
        
        /*
        //$pdf=new FPDF('P','mm','A4');
        //include('fpdf_override.php');
        $pdf=new jPDF('P','mm','A4');
        $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
        $pdf->setCountryData($job_details['country_id']);
        */

        $pdf=new jPDI();
        //$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
        //$pdf->setCountryData($job_details['country_default']);

    }

    $pdf->SetTopMargin(50);
    $pdf->SetAutoPageBreak(true,30);
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
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,263,210);  
            }
        }else{
            if($print!=true){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_corner_img.png',110,0,100);
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,263,210);
            }       
        }
    */

    $pdf->SetFont('Arial','B',18); 
       
       // $pdf->Cell(0,36,'',0,1,'C');
       
        $pdf->Cell(0,5,'STATEMENT OF COMPLIANCE',0,1,'C');
    $pdf->Cell(0,15,'',0,1,'C');
       
        $pdf->SetFont('Arial','B',11);

    $pdf->Cell(45,5,"Real Estate Agent:");

    $pdf->SetFont('Arial','',11);
    $pdf->Cell(30,5,$property_details['agency_name']);
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(45,5,"Property:");

    $pdf->SetFont('Arial','',11);
    $pdf->Cell(30,5,$property_details['address_1'] . " " . $property_details['address_2']);
    $pdf->Ln();
    $pdf->Cell(45,5,"");
    $pdf->Cell(30,5,$property_details['address_3'] . " " . $property_details['state'] . ", " .$property_details['postcode'] );
    $pdf->Ln(10);

    // compass index number
    if( $property_details['compass_index_num'] != '' ){
        
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(45,5,"Index No.");
        
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(45,5,$property_details['compass_index_num']);
        
        $pdf->Ln(10);
        
    }

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(45,5,"Type of Visit:");

    $pdf->SetFont('Arial','',11);
    $pdf->Cell(30,5,$job_details['job_type'].' '.$os['type']);
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(45,5,"Date of Visit:");

    $pdf->SetFont('Arial','',11);
    $pdf->Cell(30,5,$job_details['date']);
    $pdf->Ln(10);

    //$pdf->SetFont('Arial','B',11);
    //$pdf->Cell(45,5,"Tested by:");

    //$pdf->SetFont('Arial','',11);
    //$pdf->Cell(55,5,$job_details['FirstName']);

    // $pdf->SetFont('Arial','B',11);
    // $pdf->Cell(38,5,"License Number:");

    // $pdf->SetFont('Arial','',11);
    // $pdf->Cell(30,5,$job_details['license_number']);

    $pdf->Ln(15);

    /*
    $appliance_details = $this->getPropertyAlarms($job_id, 1, 0, 1);
    $num_appliances = sizeof($appliance_details);
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
        $pdf->Cell(15, 5, $job_details['retest_date']);

        $pdf->Ln(15);

        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(185,5,'All Appliances located within the property as detailed above are compliant with current legislation and Australian Standards. Appliances and leads are tested as per Manufacturers recommendations & the NSW Test and Tag requirements.');
        $pdf->Ln(10);
    }
    */


    // if bundle, get bundle services id
    $ajt_serv_sql = $this->job_functions_model->getService($job_details['jservice']); 
    $ajt_serv = $ajt_serv_sql->row_array();

    // bundle
    if($ajt_serv['bundle']==1){ 
        $bs_sql = $this->db->query("
            SELECT *
            FROM `alarm_job_type`
            WHERE `id` IN({$ajt_serv['bundle_ids']})
        ");
    // not bundle
    }else{
        $bs_sql = $this->db->query("
            SELECT *
            FROM `alarm_job_type`
            WHERE `id` = {$job_details['jservice']}
        ");
    }

    /*
    $bs_sql = mysql_query("
            SELECT *
            FROM `alarm_job_type`
            WHERE `id` = {$job_details['jservice']}
            AND `active` = 1
        ");
    */

    // while($bs = mysql_fetch_array($bs_sql)){
    foreach($bs_sql->result_array() as $bs){

        // smoke alarms
        if( $bs['id'] == 2 || $bs['id'] == 12 ){
            $pdf->Ln(2);
                $pdf->SetDrawColor(190,190,190);
                $pdf->SetLineWidth(0.05);
                $pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

                $pdf->Ln(6);

            $pdf->SetFont('Arial','B',11);

            $pdf->Cell(45,5,"{$bs['type']} Summary:");
            $pdf->Ln(10);

            $ast_pos = 3;
            $hw_Position = 35;
            $hw_Power = 18;
            $hw_Type = 30;
            $hw_Make = 27;
            $hw_Model = 25;
            $hw_Expiry = 14;
            $hw_dB = 25;
            
            $pdf->Cell($ast_pos,5,"");
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

            $jalarms_sql = $this->db->query("
                SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason  
                FROM alarm a 
                    LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
                    LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
                    LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
                WHERE a.job_id = '" . $job_id . "'
                ORDER BY a.`ts_discarded` ASC, a.alarm_id ASC
            ");
            $temp_alarm_flag = 0;
            // while($jalarms = mysql_fetch_array($jalarms_sql)){
            foreach($jalarms_sql->result_array() as $jalarms)
            {

                // if reason: temporary alarm
                if( $jalarms['alarm_reason_id']==31 ){
                    $temp_alarm_flag = 1;
                }

                // if discarded
                if($jalarms['ts_discarded']==1){
                    $pdf->SetTextColor(255, 0, 0);
                    $pdf->SetFont('Arial','I',$sa_font_size);
                }

                // if techsheet "Required for Compliance" = 0/No
                $append_asterisk = '';
                if( $jalarms['ts_required_compliance'] == 0 ){
                    $append_asterisk = '*';
                }   

                $pdf->SetTextColor(255, 0, 0); // red
                $pdf->Cell($ast_pos,5,$append_asterisk);
                $pdf->SetTextColor(0, 0, 0); // clear red

                $pdf->Cell($hw_Position,5,mb_strimwidth($jalarms['ts_position'], 0, 20, '...'));
                $pdf->Cell($hw_Power,5,$jalarms['alarm_pwr']);
                $pdf->Cell($hw_Type,5,$jalarms['alarm_type']);
                $pdf->Cell($hw_Make,5,$jalarms['make']);
                $pdf->Cell($hw_Model,5,$jalarms['model']);
                $pdf->Cell($hw_Expiry,5,$jalarms['expiry']);        
                
                if($jalarms['ts_discarded']==1){
                    $adr_sql = $this->db->query("
                        SELECT * 
                        FROM `alarm_discarded_reason`
                        WHERE `active` = 1
                        AND `id` = {$jalarms['ts_discarded_reason']}
                    ");
                    $adr = $adr_sql->row_array();
                    $pdf->Cell($hw_dB,5,'Removed - '.$adr['reason']);
                }else{
                    $pdf->Cell($hw_dB,5,$jalarms['ts_db_rating']);
                }
                if($jalarms['ts_discarded']==1){
                    $pdf->SetFont('Arial','',$sa_font_size);
                    $pdf->SetTextColor(0, 0, 0);
                }
                $pdf->Ln();
            }

            $pdf->Ln(4);
                
            $c_sql = $this->db->query("
                SELECT *
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
                WHERE j.`id` = {$job_details['id']}
            "); 
            $c = $c_sql->row_array();
            switch($c['country_id']){
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
                
            $pdf->SetFont('Arial','',10);
            if( $job_details['state'] == 'QLD' && $temp_alarm_flag==1 ){ // if QLD and temporary alarm
                $pdf->SetTextColor(255, 0, 0);
                $pdf->SetFont('Arial','I',10);
                $pdf->MultiCell(185,5,'Smoke alarms at the above property are NOT compliant with AS3786 (2014) and will need to be replaced when compliant smoke alarms become available. The property has working smoke alarms and the property is safe however they are not compliant, and SATS will revisit the property to make it compliant as soon as compliant alarms become available.'); 
                $pdf->SetFont('Arial','',10);
                $pdf->SetTextColor(0, 0, 0);
            }else if( $job_details['state'] == 'NSW' ){

                if( $job_details['country_id']==1 ){ // AU
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(185,5,'All smoke alarms within the property as detailed above where a removable battery is present have had batteries replaced at this service dated '.$date_of_visit.' in accordance with Residential Tenancies Regulation 2019 [NSW]. '); 
                    $pdf->Ln(3);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(185,5,"All smoke alarms within the property as detailed above have been cleaned and tested as per manufacturer's instructions and where new alarms have been installed they have been installed in accordance with Residential Tenancies Regulation 2019 [NSW], Australian Standard AS 3786: 2014 Smoke Alarms, Building Code of Australia, Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations"); 
                }else if( $job_details['country_id']==2 ){ // NZ
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations (where smoke alarms are hard-wired) and Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.'); 
                } 

            }else{          
                
                if( $job_details['country_id']==1 ){ // AU
                    $pdf->Cell($ast_pos,5,'');
                    //$pdf->MultiCell(185,5,'All smoke alarms within the property as detailed above where a removable battery is present have had batteries replaced at this service dated '.$date_of_visit.' in accordance with Residential Tenancies Regulation 2019 [NSW]. '); 
                   // $pdf->Ln(3);
                    //$pdf->MultiCell(185,5,"All smoke alarms within the property as detailed above have been cleaned and tested as per manufacturer's instructions and where new alarms have been installed they have been installed in accordance with Residential Tenancies Regulation 2019 [NSW], Australian Standard AS 3786: 2014 Smoke Alarms, Building Code of Australia, Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations");     
                    $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and been installed in accordance with '.$country_text.' Standard AS 3786 (2014) Smoke Alarms, Building Code of '.$c['country'].', Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations.'); 
                   
                }else if( $job_details['country_id']==2 ){ // NZ
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations (where smoke alarms are hard-wired) and Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.'); 
                }  
                
            }
            

            $pdf->Ln(3);
            //$pdf->SetFont('Arial','',8);
            
            $pdf->SetTextColor(255, 0, 0); // red
            $pdf->Cell($ast_pos,5,'*');
            $pdf->SetTextColor(0, 0, 0); // clear red
            $pdf->MultiCell(185,5,'Not required for compliance'); 

            $pdf->Ln(3);
            $pdf->MultiCell(185,5,'Where alarm Power is 240v or 240vLi the alarm is mains powered. (Hard Wired). All other alarms are battery powered.');  
           
            
        // safety switch
        }else if($bs['id'] == 5){
        
            $ssp_sql = $this->db->query("
                SELECT `ts_safety_switch`, `ts_safety_switch_reason`, `ss_quantity`
                FROM `jobs`
                WHERE `id` = {$job_details['id']}
            ");
            $ssp = $ssp_sql->row_array();

            $pdf->Ln(2);
                $pdf->SetDrawColor(190,190,190);
                $pdf->SetLineWidth(0.05);
                $pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

                $pdf->Ln(6);

                $pdf->SetFont('Arial','B',11);

                $pdf->Cell(45,5,"{$bs['type']} Summary:");
                $pdf->Ln(10);
                
                // check if at least 1 SS failed
                $chk_ss_sql = $this->db->query("
                    SELECT *
                    FROM `safety_switch`
                    WHERE `job_id` ={$job_details['id']}
                    AND `test` = 0
                ");
                
                $num_ss_fail = $chk_ss_sql->row_array();
                
                //if( $num_ss_fail > 0 ){
                    
                    // Fusebox Viewed
                    /* comment out (gherx)
                    $pdf->Ln(4);
                    $pdf->SetFont('Arial','B',11);			
                    $pdf->Cell(40,5,"Fusebox Viewed:");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(15,5,($ssp['ts_safety_switch']==2)?'Yes':'No');
                    */
        
             // Fusebox Viewed - Yes
             if($ssp['ts_safety_switch']==2){

                //SS TABLE START
                 //$pdf->Cell(30,5,"{$service} Headings");
                $pdf->Cell(30,5,"Make");
                $pdf->Cell(30,5,"Model");
                //$pdf->Cell(30,5,"Test Date");
                $pdf->Cell(30,5,"Test Result");
                $pdf->Ln(9);
                $pdf->SetFont('Arial','',10);

                //$pdf->Cell(30,5,"{$service} Data");
                $ss_sql = $this->db->query("
                    SELECT *
                    FROM `safety_switch`
                    WHERE `job_id` ={$job_details['id']}
                    ORDER BY `make`
                ");
            
                // while($ss = mysql_fetch_array($ss_sql))
                foreach($ss_sql->result_array() as $ss)
                {
                    
                    $pdf->Cell(30,5,$ss['make']);
                    $pdf->Cell(30,5,$ss['model']);
                    //$pdf->Cell(30,5,$job_details['date']);  
                    if($ss['test']==1){ // pass
                        $pdf->Cell(30,5,'Pass');
                    }else if( is_numeric($ss['test']) && $ss['test']==0 ){ // fail
                        $pdf->SetTextColor(255, 0, 0); // red
                        $pdf->Cell(30,5,'Fail');
                        $pdf->SetTextColor(0, 0, 0); 
                    }else if($ss['test']==2){ // no power
                        $pdf->Cell(30,5,'No Power to Property at time of testing');
                    }else if($ss['test']==3){ // not tested
                        $pdf->Cell(30,5,'Not Tested');
                    }else if($ss['test']==''){
                        $pdf->Cell(30,5,'Not Tested');
                    }
                    
                    $pdf->Ln();
                }
                //SS TABLE START END
            
                //new gherx added  
                if($ssp['ss_quantity']==0){ // 0 safety switch
                    $pdf->SetTextColor(255,0,0);
                    $pdf->MultiCell(185,5,'No Safety Switches Present. We strongly recommend a Safety Switch be installed to protect the occupants.'); 
                    $pdf->Ln(4);
                    $pdf->MultiCell(185,5,'Our Technician has noted there are no safety switches installed at the premises. Therefore none were tested upon attendance.'); 
                    $pdf->SetTextColor(0,0,0);
                }else{ // 1 or more safety switch

                    // query if at least 1 has not tested
                    $chk_ss_not_tested_sql = $this->db->query("
                        SELECT *
                        FROM `safety_switch`
                        WHERE `job_id` ={$job_details['id']}
                        AND `test` = 3
                    ");

                    // query if at least 1 has no power
                    $chk_ss_no_pwr_sql = $this->db->query("
                        SELECT *
                        FROM `safety_switch`
                        WHERE `job_id` ={$job_details['id']}
                        AND `test` = 2
                    ");
                    $num_no_power = $chk_ss_no_pwr_sql->num_rows();

                    $pdf->Ln(4);
                    $pdf->MultiCell(185,5,$ss_sql->num_rows().' Safety Switches Present'); //display number of switch	

                    if( $num_no_power > 0 ){ //NO POWER		
                        $pdf->Ln(4);						
                        $pdf->MultiCell(185,5,"One or more of the safety switches at the property were unable to be tested due to no power supply to the property at the time of inspection, and power is required to perform a mechanical test on the Safety Switches.");
                    }else if( $num_ss_fail > 0 ){ // ATLEAT 1 SS TEST FAILD	
                
                        switch ($chk_ss_sql->num_rows()) {
                            case 1:
                                $num_string = "One";
                                break;
                            case 2:
                                $num_string = "Two";
                                break;
                            case 3:
                                $num_string = "Three";
                                break;
                            case 4:
                                $num_string = "Four";
                                break;	
                            case 5:
                                $num_string = "Five";
                                break;	
                            case 6:
                                $num_string = "Six";
                                break;
                            case 7:
                                $num_string = "Seven";
                                break;
                            case 8:
                                $num_string = "Eight";
                                break;
                            case 9:
                                $num_string = "Nine";
                                break;
                            case 10:
                                $num_string = "Ten";
                                break;
                            default:
                                $num_string = $num_ss_fail;
                        } 

                        /*$pdf->Ln(4);					
                        $pdf->MultiCell(185,5,"One or more of the Safety Switches at this property has failed. This information is for your use, and we strongly suggest you advise your client. SATS do not install Safety Switches; however we do test them when they are present.");
                        $pdf->Ln(4);*/
                        $pdf->SetTextColor(255, 0, 0); // red
                        $have_has = ($chk_ss_sql->num_rows()>1) ? 'have' : 'has';
                        $pdf->MultiCell(185,5,"{$num_string} of the Safety Switches at this property {$have_has} failed. This information is for your use, and we strongly suggest you advise your client. SATS do not install Safety Switches; however we do test them when they are present.");
                        $pdf->SetTextColor(0, 0, 0);
                        
                    }else if($chk_ss_not_tested_sql->num_rows()>0){ //IF ANY SS NOT TESTED
                        $pdf->Ln(4);					
                        $pdf->MultiCell(185,5,"One or more of the safety switches at the property were unable to be tested at the time of attendance. Please contact SATS for further information.");
                    }else{
                        $pdf->Ln(4);					
                        $pdf->MultiCell(185,5,"All Safety Switches have been Mechanically Tested and pass a basic mechanical test, to assess they are in working order. No test has been performed to determine the speed at which the device activated.");
                    }

                }
                //new gherx added end

            // Fusebox Viewed - No
            }else if($ssp['ts_safety_switch']==1){
            
                // reason				
                $pdf->SetFont('Arial','B',11);			
                //$pdf->Cell(18,5,"Reason:");
                $pdf->SetFont('Arial','',10);
                switch($ssp['ts_safety_switch_reason']){
                    case 0:
                        $ssp_reason = 'No Switch';
                        $ssp_reason2 = "Our Technician has noted there are no safety switches installed at the premises. Therefore none were tested upon attendance.";
                    break;
                    case 1:
                        $ssp_reason = 'Unable to Locate';
                        $ssp_reason2 = "One or more of the safety switches were not tested due to the inability to locate them at the time of attendance.";
                    break;
                    case 2:
                        $ssp_reason = 'Unable to Access';
                        $ssp_reason2 = "One or more of the safety switches were not tested due to the inability to access at the time of attendance.";
                    break;
                }
               // $pdf->Cell(30,5,$ssp_reason);

                $pdf->Ln(8);
                $pdf->MultiCell(185,5,$ssp_reason2);
            
            }
            
       // }
        
            $pdf->Ln(3);
            $pdf->SetFont('Arial','',8);
                
            //}
        
            
        // corded windows
        }else if($bs['id'] == 6){
            $pdf->Ln(2);
                $pdf->SetDrawColor(190,190,190);
                $pdf->SetLineWidth(0.05);
                $pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

                $pdf->Ln(6);

            $pdf->SetFont('Arial','B',11);

            $pdf->Cell(45,5,"{$bs['type']} Summary:");
            $pdf->Ln(10);

            $pdf->SetFont('Arial','',10);
            $cw_sql = $this->db->query("
                SELECT *
                FROM `corded_window`
                WHERE `job_id` ={$job_id}
            ");
            // while( $cw = mysql_fetch_array($cw_sql) ){
            foreach($cw_sql->result_array() as $cw){
                $num_windows_total += $cw['num_of_windows'];
                $pdf->Cell(30,5,$cw['location']);
                $pdf->Cell(30,5,$cw['num_of_windows'],0,1);
            }

            $pdf->Ln(5);
            $pdf->MultiCell(185,5,'All Corded Windows within the Property as detailed above are Compliant with Current Legislation and '.$country_text.' Standards. The Required Clips and Tags have been installed to ensure proper compliance with Current Legislation. Further data is available on the agency portal'); 
            $pdf->Ln(3);
            $pdf->SetFont('Arial','',8);
            
            
        // water meter
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
            $wm_sql = $this->functions_model->getWaterMeter($job_details['id']);
            // while($wm = mysql_fetch_array($wm_sql))
            // {
            foreach($wm_sql->result_array() as $wm)
            {
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

    if(in_array($job_details['jservice'], $ic_service)){
        $ic_check = 1;
    }else{
        $ic_check = 0;
    }

    if( $ic_check == 0 && $job_details['state'] == 'QLD' && $job_details['qld_new_leg_alarm_num']>0 && $job_details['prop_upgraded_to_ic_sa'] != 1 ){

        $pdf->SetTextColor(0, 0, 204);              
        // QUOTE
        $quote_qty = $job_details['qld_new_leg_alarm_num'];
        $price_240vrf = $this->get240vRfAgencyAlarm($property_details['agency_id']);
        $quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
        $quote_total = $quote_price*$quote_qty;
        $pdf->MultiCell(185,5,'We have provided a quote for $'.$quote_total.' to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details['date']).'+90 days')).' and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
        $pdf->SetTextColor(0, 0, 0);
        

    }

    // WE PDF
    // get WE services
    $we_services = $this->system_model->we_services_id();    

    if ( in_array($job_details['jservice'], $we_services) ){ // only display if it has WE service
        
        // display WE PDF using FPDI
        $pdf->SetFont('Arial','',10);
        $pdf->SetAutoPageBreak(true,7);
        $pdf->addPage(); 
        $pdf->set_dont_display_footer(1); // hide the footer
        $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/we_cert.pdf');
        $tplidx = $pdf->importPage(1);        
        $pdf->useTemplate($tplidx, 0, 20);

        // ADDRESS
        // Stret name and num
        $pdf->setXY(27,75);
        $pdf->Cell(8,0, "{$property_details['address_1']} {$property_details['address_2']}");
        
        // suburb and state
        $pdf->setXY(27,82.5);
        $pdf->Cell(8,0, "{$property_details['address_3']} {$property_details['state']}");

        // postcode
        $pdf->setXY(157,82.5);
        $pdf->Cell(8,0, $property_details['postcode']);

        // water efficiency measures         
        $we_sql = $this->db->query("
        SELECT 
            we.`water_efficiency_id`,
            we.`device`,
            we.`pass`,
            we.`location`,
            we.`note`,

            wed.`water_efficiency_device_id`,
            wed.`name` AS wed_name
        FROM `water_efficiency` AS we
        LEFT JOIN `water_efficiency_device` AS wed ON we.`device` = wed.`water_efficiency_device_id`
        WHERE we.`job_id` = {$job_id}
        AND we.`active` = 1
        ORDER BY we.`location` ASC
        ");        

        // total count
        $shower_count = 0;
        $tap_count = 0;
        $toilet_count = 0;

        // total pass count
        $shower_pass_count = 0;
        $tap_pass_count = 0;
        $toilet_pass_count = 0;

        foreach( $we_sql->result() as $we_row ){

            // shower count
            if($we_row->device == 3){ 
                $shower_count++;
            }

            // tap count
            if($we_row->device == 1){ 
                $tap_count++;
            }

            // toilet
            if($we_row->device == 2){ 
                $toilet_count++;
            }
           
            // passed shower count
            if( $we_row->device == 3 && $we_row->pass == 1 ){
                $shower_pass_count++;
            }

            // passwed tap count
            if( $we_row->device == 1 && $we_row->pass == 1 ){
                $tap_pass_count++;
            }

            // passed toilet count
            if( $we_row->device == 2 && $we_row->pass == 1 ){
                $toilet_pass_count++;
            }

        }         

        // leak    
        $pass_img = null;  
        if ( $job_details['property_leaks'] == 0 && is_numeric($job_details['property_leaks']) ){
            $pass_img = 'green_check.png';
        }else if( $job_details['property_leaks'] == 1 ){
            $pass_img = 'red_cross.png';
        }  
        if( $pass_img != '' ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,108,10);
        }             
        

        // shower           
        $pass_img = null;      
        if ( $shower_pass_count == $shower_count ){
            $pass_img = 'green_check.png';
        }else{
            $pass_img = 'red_cross.png';
        } 
        if( $pass_img != '' ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,130,10);
        }
        

        // tap        
        $pass_img = null;      
        if ( $tap_pass_count == $tap_count ){
            $pass_img = 'green_check.png';
        }else{
            $pass_img = 'red_cross.png';
        } 
        if( $pass_img != '' ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,150,10);
        }
        

        // toilet
        $dual_flush_due_date =  '2025/03/23';
        $pass_img = null;   
        
        if ( $toilet_pass_count == $toilet_count ){ // pass
            $pass_img = 'green_check.png';
        }else{ // fail

            if( $job_details['jdate'] >= date('Y-m-d',strtotime($dual_flush_due_date)) ){
                $pass_img = 'red_cross.png';
            }else{
                $pass_img = 'green_check.png';
            }
            
        }         
        

        if( $pass_img != '' ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,175,10);
        }


        // WE summary        
        $pdf->setXY(12,220);
        $pdf->SetFont('Arial','B',11);       
        
        $left_spacing = 21;

        // set headers
        $th_border = 0;
        $we_col3 = 60;
        $we_col1 = 60;
        $we_col2 = 60;       
        //$we_col4 = 100;

        $pdf->setX($left_spacing);
        $pdf->Cell($we_col3,5,"Location",$th_border);
        $pdf->Cell($we_col1,5,"Device",$th_border);
        $pdf->Cell($we_col2,5,"Result",$th_border);        
        //$pdf->Cell($we_col4,5,"Note",$th_border);
        $pdf->Ln();
       

        $pdf->SetFont('Arial','',10);
        
        foreach( $we_sql->result() as $we_row ){

            $pdf->setX($left_spacing);
            $pdf->Cell($we_col3,5,$we_row->location,$th_border);
            $pdf->Cell($we_col1,5,$we_row->wed_name,$th_border);

            if( $we_row->device == 2 ){ // toilet

                if( $we_row->pass == 1 ){
                    $pdf->Cell($we_col2,5,'Dual Flush',$th_border);
                }else if( $we_row->pass == 0 && is_numeric($we_row->pass) ){
                    $pdf->SetTextColor(255, 0, 0); // red
                    $pdf->Cell($we_col2,5,'*Single Flush',$th_border);
                    $pdf->SetTextColor(0, 0, 0); // clear red
                }

            }else{ // tap or shower

                if( $we_row->pass == 1 ){
                    $pdf->Cell($we_col2,5,'Pass',$th_border);
                }else if( $we_row->pass == 0 && is_numeric($we_row->pass) ){
                    $pdf->SetTextColor(255, 0, 0); // red
                    $pdf->Cell($we_col2,5,'Fail',$th_border);
                    $pdf->SetTextColor(0, 0, 0); // clear red
                }
            }
                               
            //$pdf->Cell($we_col4,5,$we_row->note,$th_border);
            $pdf->Ln();            
        }

        // leak notes
        $pdf->setX($left_spacing);
        $pdf->SetFont('Arial','I',10);
        $pdf->SetTextColor(255, 0, 0); // red
        $pdf->Cell(130,5,$job_details['leak_notes']); 
        $pdf->SetTextColor(0, 0, 0); // clear red  

        $pdf->ln(10);  
        $pdf->setX($left_spacing);

        // note
        $note_border = 0;
        $pdf->SetFont('Arial','I',10);      
        $pdf->Cell(12,5,'*Note:',$note_border);  

        // pass
        $pdf->SetFont('Arial','BI',10); 
        $pdf->Cell(12,5,'PASS',$note_border);   
        $pdf->SetFont('Arial','I',10);
        $pdf->Cell(52,5,'= Less than 9L/minute flow rate;',$note_border);      
        
        // fail
        $pdf->SetFont('Arial','BI',10); 
        $pdf->Cell(10,5,'FAIL',$note_border);   
        $pdf->SetFont('Arial','I',10);
        $pdf->Cell(55,5,'= greater than 9L/minute flow rate.',$note_border);  
        
        $pdf->ln();  
        $pdf->setX($left_spacing+11);

        $pdf->SetFont('Arial','I',10);
        $pdf->Cell(130,5,'Single Flush toilets must be replaced to dual flush toilets on/after 23rd March 2025',$note_border);              

    }
    



    if ($output == "") {
        return $pdf->Output('','S');
    }else {
        return $pdf;
    }


}


    public function updateInvoiceDetails($job_id) {

        if ($job_id != '') {

            // get job details
            $this->db->select('`invoice_amount`, `invoice_payments`, `invoice_refunds`, `invoice_credits`, `invoice_balance`');
            $this->db->from('jobs');
            $this->db->where('id', $job_id);
            $query = $this->db->get();
            $job = $query->result_array();

            $invoice_amount_orig = $job[0]['invoice_amount'];
            $invoice_payments_orig = $job[0]['invoice_payments'];
            $invoice_refunds_orig = $job[0]['invoice_refunds'];
            $invoice_credits_orig = $job[0]['invoice_credits'];
            $invoice_balance_orig = $job[0]['invoice_balance'];

            // get the calculated values
            // invoice amount
            $inv_a = $this->getJobTotalAmount($job_id);
            $invoice_amount = ( $inv_a > 0 ) ? $inv_a : 0;

            // invoice payments
            $inv_p = $this->getJobInvoicePayments($job_id);
            $invoice_payments = ( $inv_p > 0 ) ? $inv_p : 0;

            // invoice refunds
            $inv_r = $this->getJobInvoiceRefunds($job_id);
            $invoice_refunds = ( $inv_r > 0 ) ? $inv_r : 0;

            // invoice credits
            $inv_c = $this->getJobInvoiceCredits($job_id);
            $invoice_credits = ( $inv_c > 0 ) ? $inv_c : 0;

            // invoice balance
            $invoice_balance = ($invoice_amount + $invoice_refunds) - ( $invoice_payments + $invoice_credits);

            $test_val = "
            invoice_amount_orig: {$invoice_amount_orig} - invoice_amount: {$invoice_amount}<br />
            invoice_payments_orig: {$invoice_payments_orig} - invoice_payments: {$invoice_payments}<br />
            invoice_refunds_orig: {$invoice_refunds_orig} - invoice_refunds: {$invoice_refunds}<br />
            invoice_credits_orig: {$invoice_credits_orig} - invoice_credits: {$invoice_credits}<br />
            invoice_balance_orig: {$invoice_balance_orig} - invoice_balance: {$invoice_balance}<br />
            ";
            //echo $test_val;
            // only update if invoice details changed
            if (
                    $invoice_amount_orig == '' ||
                    $invoice_amount_orig != $invoice_amount ||
                    $invoice_payments_orig != $invoice_payments ||
                    $invoice_refunds_orig != $invoice_refunds ||
                    $invoice_credits_orig != $invoice_credits ||
                    $invoice_balance_orig != $invoice_balance
            ) {
                $updateData = array(
                    'invoice_amount' => $invoice_amount,
                    'invoice_payments' => $invoice_payments,
                    'invoice_refunds' => $invoice_refunds,
                    'invoice_credits' => $invoice_credits,
                    'invoice_balance' => $invoice_balance
                );

                $this->db->where('id', $job_id);
                $this->db->update('jobs', $updateData);
            }
        }
    }

    public function getJobTotalAmount($job_id) {

        $grand_total = 0;

        $sql = "SELECT *
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE j.`id` = {$job_id}
        ";

        $jobData = $this->db->query($sql);
        $row1 = $jobData->row_array();

        if ($jobData->num_rows() > 0) {
            // get amount
            $grand_total = $row1['job_price'];
        }


        // get new alarm
        $a_sql = "SELECT *
            FROM `alarm`
            WHERE `job_id`  = {$job_id} 
            AND `new` = 1
            AND `ts_discarded` = 0
        ";

        $ajobData = $this->db->query($a_sql);
        $row2 = $ajobData->result_array();
        if ($ajobData->num_rows() > 0) {
            foreach ($row2 as $a) {
                $grand_total += $a['alarm_price'];
            }
        }


        if ($jobData->num_rows() > 0) {

            // surcharge
            $sc_sql = "SELECT *, m.`name` AS m_name 
                FROM `agency_maintenance` AS am
                LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
                WHERE am.`agency_id` = {$row1['agency_id']}
                AND am.`maintenance_id` > 0
            ";

            $scjobData = $this->db->query($sc_sql);
            $sc = $scjobData->result_array();
            if ($scjobData->num_rows() > 0) {
                if ($grand_total != 0 && $sc[0]['surcharge'] == 1) {
                    $grand_total += $sc[0]['price'];
                }
            }
        }


        return $grand_total;
    }

    public function getJobInvoicePayments($job_id) {
        $sql = "SELECT SUM(`amount_paid`) AS amount_paid_tot
            FROM `invoice_payments`
            WHERE `job_id` = {$job_id}
            AND `active` = 1
        ";

        $invoicePayment = $this->db->query($sql);
        $row = $invoicePayment->row_array();

        return $row['amount_paid_tot'];
    }

    public function getJobInvoiceRefunds($job_id) {
        $sql = "SELECT SUM(`amount_paid`) AS refund_paid_tot
            FROM `invoice_refunds`
            WHERE `job_id` = {$job_id}
            AND `active` = 1
        ";

        $invoiceRefund = $this->db->query($sql);
        $row = $invoiceRefund->row_array();

        return $row['refund_paid_tot'];
    }

    public function getJobInvoiceCredits($job_id) {
        $sql = "SELECT SUM(`credit_paid`) AS credit_paid_tot
            FROM `invoice_credits`
            WHERE `job_id` = {$job_id}
            AND `active` = 1
        ";

        $invoiceCredits = $this->db->query($sql);
        $row = $invoiceCredits->row_array();

        return $row['credit_paid_tot'];
    }

    public function getInvoiceCreditReason($credit_reason_id) {

        if ($credit_reason_id == -1) { // other
            $credit_reason = 'Other';
        } else {
            $credit_reason_sql = $this->getCreditReason($credit_reason_id);
            $credit_reason = $credit_reason_sql['reason'];
        }

        return $credit_reason;
    }

    public function getCreditReason($credit_reason_id = null) {

        $append_str = null;
        if ($credit_reason_id > 0) {
            $append_str = " AND `credit_reason_id` = {$credit_reason_id} ";
        }

        $sql_str = "
            SELECT *
            FROM `credit_reason` 
            WHERE `active` = 1
            {$append_str}
        ";

        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();

        return $row;
    }

    public function getPropertyAlarms($job_id, $incnew = 1, $discarded = 1, $alarm_job_type_id = 1) {

        $query = "  SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason  
                    FROM alarm a 
                        LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
                        LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
                        LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
                    WHERE a.job_id = '" . $job_id . "'";

        if ($alarm_job_type_id == 4 || $alarm_job_type_id == 5) { // Safety Switch view and mech should have same alarms
            $query .= " AND a.alarm_job_type_id IN (4,5)";
        } else {
            $query .= " AND a.alarm_job_type_id = {$alarm_job_type_id}";
        }

        if ($incnew == 0)
            $query .= " AND a.New = 0";
        if ($incnew == 2)
            $query .= " AND a.New = 1";

        if ($discarded == 0)
            $query .= " AND a.ts_discarded = 0";
        if ($discarded == 2)
            $query .= " AND a.ts_discarded = 1";

        $query .= " ORDER BY a.alarm_id ASC ";

        $alarms = $this->db->query($query);

        return $alarms->result_array();
    }

    // 240v RF Brooks
    public function get240vRfAgencyAlarm($agency_id) {
        $sql_str = "
            SELECT `price` 
            FROM `agency_alarms`
            WHERE `agency_id` = {$agency_id}
            AND `alarm_pwr_id` = 10
            LIMIT 1
        ";
        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();
        return $row['price'];
    }

    // 240v RF Cavius
    public function get240vRf_cavius_AgencyAlarm($agency_id) {
        $sql_str = "
            SELECT `price` 
            FROM `agency_alarms`
            WHERE `agency_id` = {$agency_id}
            AND `alarm_pwr_id` = 14
            LIMIT 1
        ";
        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();
        return $row['price'];
    }

    // 240v RF Emerald
    public function get_emerald_AgencyAlarm($agency_id) {
        $sql_str = "
            SELECT `price` 
            FROM `agency_alarms`
            WHERE `agency_id` = {$agency_id}
            AND `alarm_pwr_id` = 22
            LIMIT 1
        ";
        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();
        return $row['price'];
    }

    public function getIcAlarmAgencyService($agency_id) {
        $sql_str = "
            SELECT `price` 
            FROM `agency_services`
            WHERE `agency_id` = {$agency_id}
            AND `service_id` = 12
        ";
        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();
        return $row['price'];
    }


    public function get_equivalent_IC_price($service,$agency_id) {

        if( $agency_id > 0 ){

            switch( $service ){
            
                case 2: // SA
                    $ic_service = 12;
                break;
    
                case 8: // SA.SS
                    $ic_service = 13;
                break;
    
                case 9: // SA.CW.SS
                    $ic_service = 14;
                break;
    
            }

            if( $ic_service > 0 ){

                // get agency price
                    $sql_str = "
                    SELECT `price` 
                    FROM `agency_services`
                    WHERE `agency_id` = {$agency_id}
                    AND `service_id` = {$ic_service}
                ";
                $sql = $this->db->query($sql_str);
                $row = $sql->row_array();

                return $row['price'];

            }            

        }        

    }

    public function check_api_logs_by_JobId($jobId) {
        $this->db->select('id');
        $this->db->from('agency_api_logs');
        $this->db->where('job_id', $jobId);
        $this->db->where('status', 1);
        $query = $this->db->get();
        $res = $query->num_rows();
        if ($res > 0 ) {
            return true;
        }else {
            return false;
        }
    }

    public function get_state_by_job_id($job_id){
        $this->db->select('a.state as a_state, p.state as p_state, p.property_id as p_propid');
        $this->db->from('jobs as j');
        $this->db->join("`property` AS p", "p.`property_id` = j.`property_id`", "LEFT");
        $this->db->join("`agency` AS a", "a.`agency_id` = p.`agency_id`", "LEFT");
        $this->db->where('j.id', $job_id);
        $this->db->where('a.country_id', $this->config->item('country'));
        $query = $this->db->get();
        return $query;
    }

    
    
    public function entry_notice_switch($params){

        $job_id = $params['job_id'];
        $output = $params['output'];
        
        $staff_id =  $this->session->staff_id;
        $country_id = $this->config->item('country');

        if( $job_id > 0 ){

            $sel_query = "p.`state` AS p_state";    
               
            $job_params = array(
                'sel_query' => $sel_query,
                
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id, 
                'job_id' => $job_id,
                           
                'join_table' => array('job_type','alarm_job_type'),                      
            );
            $job_sql = $this->jobs_model->get_jobs($job_params);
            $job_row = $job_sql->row();

            //$output = 'I'; // display to the browser

            if(COUNTRY == 2){ //NZ EN PDF

                $en_pdf_params = array(
                    'job_id' => $job_id,
                    'output' => $output
                );
                $this->en_nz_pdf($en_pdf_params);

            }else{  //AU EN PDFs

                switch( $job_row->p_state ){                

                    case 'SA':
                        $en_pdf_params = array(
                            'job_id' => $job_id,
                            'output' => $output
                        );
                        return $this->entry_notice_sa($en_pdf_params);       
                    break;  
                    
                    case 'QLD':
                        $en_pdf_params = array(
                            'job_id' => $job_id,
                            'output' => $output
                        );
                        return $this->entry_notice_qld($en_pdf_params);       
                    break;
    
                    case 'NSW':
                        $en_pdf_params = array(
                            'job_id' => $job_id,
                            'output' => $output
                        );
                        return $this->entry_notice_nsw($en_pdf_params);       
                    break;
    
                    case 'ACT':
                        $en_pdf_params = array(
                            'job_id' => $job_id,
                            'output' => $output
                        );
                        return $this->entry_notice_act($en_pdf_params);       
                    break;
    
                    default:
                        $en_pdf_params = array(
                            'job_id' => $job_id,
                            'output' => $output
                        );
                        return $this->entry_notice_generic($en_pdf_params);   
    
                }

            }

        }       

    }
    
    
    public function entry_notice_sa($params){

        $job_id = $params['job_id'];
        $output = ( $params['output'] != '' )?$params['output']:'I';  
        $country_id = $this->config->item('country');     

        // get country data
        $country_params = array(
        'sel_query' => '
            c.`country_id`,
            c.`agent_number`, 
            c.`outgoing_email`, 
            c.`tenant_number`
        ',
        'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();

        
        if( $job_id ){

            $pdf = new jPDI();

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
            $invoice_number = "{$job_id}{$check_digit}";

            // pdf settings
            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer

            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/en_sa.pdf');

            // 1st page
            $tplidx = $pdf->importPage(1, '/MediaBox');   
            $size = $pdf->getTemplateSize($tplidx);
            $pdf->AddPage('P', array(210, $size['h']+30));        
            $pdf->useTemplate($tplidx, 0, 0, 210);  
            $pdf->SetFont('Arial','',11);

            // get job data
            $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`at_myob`,
            j.`sms_sent_merge`,
            j.`client_emailed`,
            j.`time_of_day`,
            j.`en_date_issued`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            
            a.`agency_id`,
            a.`agency_name`,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`send_emails`,
            a.`account_emails`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type
            ";    
                
            $job_params = array(
                'sel_query' => $sel_query,
                
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id, 
                'job_id' => $job_id,
                            
                'join_table' => array('job_type','alarm_job_type'),                      
            );
            $job_sql = $this->jobs_model->get_jobs($job_params);
            $job_row = $job_sql->row();

            $property_id = $job_row->property_id;

            $x_pos = 28;	
            // Tenant 
            $pdf->SetXY($x_pos, 49);

            if( $property_id > 0 ){              

                // get tenants 
                $sel_query = "
                    pt.`property_tenant_id`,
                    pt.`tenant_firstname`,
                    pt.`tenant_lastname`,
                    pt.`tenant_mobile`
                ";
                $params = array(
                    'sel_query' => $sel_query,
                    'property_id' => $property_id,
                    'pt_active' => 1,                   
                    'offset' => 0,
                    'limit' => 2,
                    'display_query' => 0
                );
                $pt_sql = $this->properties_model->get_property_tenants($params);

                foreach($pt_sql->result() as $pt_row){
                    $tenants_names_arr[] = ucwords(strtolower($pt_row->tenant_firstname)).' '.ucwords(strtolower($pt_row->tenant_lastname));
                }

                if( count( $tenants_names_arr ) > 1 ){
                    
                    $tenant_str_imp = implode(", ",$tenants_names_arr); // separate tenant names with a comma
                    $last_comma_pos = strrpos($tenant_str_imp,","); // find the last comma(,) position
                    $tenant_str = substr_replace($tenant_str_imp,' &',$last_comma_pos,1); // replace comma with ampersand(&)		
                    $pdf->Cell(0,0, $tenant_str);
                    
                }else{
                            
                    $pdf->Cell(0,0, $tenants_names_arr[0]);
                    
                }

            }   
            
            // Address
            ##fix for NZ macron char in address issue 
            setlocale(LC_CTYPE, 'en_US');
            $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_1." ".$job_row->p_address_2." ".$job_row->p_address_3." ".$job_row->p_state);

            $pdf->SetXY($x_pos, 71);
            //$pdf->Cell(0,0, "{$job_row->p_address_1} {$job_row->p_address_2} {$job_row->p_address_3} {$job_row->p_state}");
            $pdf->Cell(0,0, "{$incov_val1}");

            $y_pos = 98;
            // date
            $pdf->SetXY(32, $y_pos); 
            $pdf->Cell(0,0, date("d      m      Y",strtotime($job_row->j_date)) );

            // time
            $pdf->SetXY(116, $y_pos); 
            $pdf->Cell(0,0, $job_row->time_of_day );

            // mark "For some other genuine purpose"
            $pdf->SetXY(19.5, 161); 
            $pdf->Cell(0,0, 'X' );

            // purpose 
            $pdf->SetXY(32, 174); 
            $pdf->Cell(0,0, 'To test and service the Smoke Alarms located at this address' );

            $x_pos = 75;
	
            // Signature of landlord/agent:
            $pdf->SetXY($x_pos, 183); 
            //$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/signature.png',null, null, 50); // Manually position image on PDF
            $pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/DK_signature.png',66, 175, 70); // Manually position image on PDF

            // signature date
            $pdf->SetXY(162, 196.5); 
            $pdf->Cell(0,0, date("d    m   Y",strtotime($job_row->en_date_issued)) );

            // Address of landlord/agent:
            $pdf->SetXY($x_pos, 208); 
            $pdf->Cell(0,0, "Smoke Alarm Testing Services ({$country_row->tenant_number}) on behalf of" );
            
            // Agency
            $pdf->SetXY($x_pos, 220); 
            $pdf->Cell(0,0, "{$job_row->agency_name} - {$job_row->a_address_1} {$job_row->a_address_2} {$job_row->a_address_3} {$job_row->a_state} {$job_row->a_postcode}" );
            

            // 2ND page
            $tplidx2 = $pdf->importPage(2, '/MediaBox');
            $pdf->addPage(); 
            $pdf->useTemplate($tplidx2, 0, 0, 210);

        
            $pdf_name = "entry_notice_{$invoice_number}".date('Ymdhis').rand().".pdf";

            return $pdf->Output($pdf_name, $output);

        }


    }



    public function entry_notice_qld($params){

        $job_id = $params['job_id'];
        $output = ( $params['output'] != '' )?$params['output']:'I';  
        $country_id = $this->config->item('country');     

        // get country data
        $country_params = array(
        'sel_query' => '
            c.`country_id`,
            c.`agent_number`, 
            c.`outgoing_email`, 
            c.`tenant_number`
        ',
        'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();

        
        if( $job_id ){

            $pdf = new jPDI();

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
            $invoice_number = "{$job_id}{$check_digit}";

            // pdf settings
            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer

            //$pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/en_qld.pdf');
            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/en_qld_v2.pdf');

            $tplidx = $pdf->importPage(1, '/MediaBox');               

            $pdf->addPage();       
            $pdf->useTemplate($tplidx, 0, 0, 210);  

            $pdf->SetFont('Arial','',11);

            // get job data
            $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`at_myob`,
            j.`sms_sent_merge`,
            j.`client_emailed`,
            j.`time_of_day`,
            j.`en_date_issued`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            
            a.`agency_id`,
            a.`agency_name`,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`send_emails`,
            a.`account_emails`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type,

            sa.`StaffID`,
            sa.`FirstName` AS tech_fname,
            sa.`LastName` AS tech_lname
            ";    
                
            $job_params = array(
                'sel_query' => $sel_query,
                
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id, 
                'job_id' => $job_id,
                            
                'join_table' => array('job_type','alarm_job_type','staff_accounts')                     
            );
            $job_sql = $this->jobs_model->get_jobs($job_params);
            $job_row = $job_sql->row();

            $property_id = $job_row->property_id;

            // Tenant details
            $pdf->setY(59); 

            if( $property_id > 0 ){              

                // get tenants 
                $sel_query = "
                    pt.`property_tenant_id`,
                    pt.`tenant_firstname`,
                    pt.`tenant_lastname`,
                    pt.`tenant_mobile`
                ";
                $params = array(
                    'sel_query' => $sel_query,
                    'property_id' => $property_id,
                    'pt_active' => 1,                   
                    'offset' => 0,
                    'limit' => 2,
                    'display_query' => 0
                );
                $pt_sql = $this->properties_model->get_property_tenants($params);
                $pt_num_row = $pt_sql->num_rows();

                foreach($pt_sql->result() as $pt_row){

                     // Tenant 
                    $pdf->Cell(21,0,"");     
                    $pdf->Cell(0,0, ucwords(strtolower($pt_row->tenant_firstname)).' '.ucwords(strtolower($pt_row->tenant_lastname)));
                    $pdf->Ln(6.4);

                }               

            }   
            
            // if only 1 tenant, but a blank space to preserve the spacing
            if( $pt_num_row == 1 ){

                // Tenant 
                $pdf->Cell(21,0,"");     
                $pdf->Cell(0,0, "");
                $pdf->Ln(6.4);

            }elseif($pt_num_row <= 0){ //add another cell if no tenants to fixed alignement:GHERX

                $pdf->Cell(21,0,"");     
                $pdf->Cell(0,0, "");
                $pdf->Ln(6.4);
                $pdf->Cell(21,0,"");     
                $pdf->Cell(0,0, "");
                $pdf->Ln(6.4);

            }

            ##fix for NZ macron char in address issue 
            setlocale(LC_CTYPE, 'en_US');
            $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_1." ".$job_row->p_address_2);
            $incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_3." ".$job_row->p_state);
            
            // street name and number 
            $pdf->Cell(21,0,"");     
            //$pdf->Cell(0,0, "{$job_row->p_address_1} {$job_row->p_address_2}");
            $pdf->Cell(0,0, "{$incov_val1}");

            // Suburb and Postcode
            $pdf->Ln(6.6);
            $pdf->Cell(21,0,"");     
            //$pdf->Cell(70,0, "{$job_row->p_address_3} {$job_row->p_state}");
            $pdf->Cell(90,0, "{$incov_val2}");
            $pdf->Cell(60,0, $job_row->p_postcode);

            // 1 Address of the rental property
            $pdf->Ln(13.8);

            // 2 Notice issued by
            $pdf->Ln(13.8);
        
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+102.5,$y_pos-0.5);
            $pdf->Cell(10,0, "X");

            $pdf->Ln(9.8);
            
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+6,$y_pos+0.8);
            $pdf->Cell(140,0, $this->config->item('COMPANY_FULL_NAME'));
            $pdf->Cell(30,0, $country_row->tenant_number);

            // 3 Details of all people entering
            $pdf->Ln(17);
             
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+9,$y_pos+0.5);

            $initial_tech = substr($job_row->tech_lname, 0, 1).'.';
            $pdf->Cell(137,0, "{$job_row->tech_fname} {$initial_tech}");
            $pdf->Cell(30,0, $country_row->tenant_number);


            // 4 Notice issued on
            $pdf->Ln(30.5);
              
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+7,$y_pos);
            $pdf->Cell(45,0, ( $job_row->en_date_issued != '' )?date('l',strtotime($job_row->en_date_issued)):null ); //eg Sunday
            $pdf->Cell(9,0, ( $job_row->en_date_issued != '' )?date('d',strtotime($job_row->en_date_issued)):null ); // dd
            $pdf->Cell(9.2,0, ( $job_row->en_date_issued != '' )?date('m',strtotime($job_row->en_date_issued)):null ); // mm
            $pdf->Cell(23,0, ( $job_row->en_date_issued != '' )?date('Y',strtotime($job_row->en_date_issued)):null ); // yy

            $pdf->Cell(15,0, "Email/SMS"); // Method of Issue

            // 5 Entry is sought under the following grounds 
            $pdf->Ln(30.5);
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+4.5,$y_pos+2);
            $pdf->Cell(10,0, "X"); // Fire & Rescue Service Act

            $pdf->Ln();

            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+4.5,$y_pos+8.5);
            $pdf->Cell(10,0, "X"); // Smoke alarm act
            
            $pdf->Ln(30);

            // 6 Entry to the property by the property owner/manager or other authorised person
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+6,$y_pos+11.5);
            $pdf->Cell(46,0, ( $job_row->j_date != '' )?date('l',strtotime($job_row->j_date)):null ); // Smoke alarm act
            $pdf->Cell(9,0, ( $job_row->j_date != '' )?date('d',strtotime($job_row->j_date)):null ); // dd
            $pdf->Cell(9.2,0, ( $job_row->j_date != '' )?date('m',strtotime($job_row->j_date)):null ); // mm
            $pdf->Cell(21,0, ( $job_row->j_date != '' )?date('Y',strtotime($job_row->j_date)):null ); // yy

            # Prepare Time of Day
            $tod_fixed = trim(preg_replace("/[^:.\-0-9\s]/", "", $job_row->time_of_day));
            $tod_fixed = str_replace("-", " ", $tod_fixed);
            $tod_fixed = preg_replace("/\s{2,}/", " ", $tod_fixed);
            $tod_fixed = str_replace(":", ".", $tod_fixed);


            $tmp = explode(" ", $tod_fixed);
            $tod_start = number_format($tmp[0], 2);
            $tod_end = number_format($tmp[1], 2);
            
            // replace . with :
            $from_str = str_replace(".", ":", $tod_start);
            $to_str = str_replace(".", ":", $tod_end);
            
            // From AM or PM
            if($tod_start < 12 && $tod_start >= 6){
                $from_ampm = 'AM';
            }else if($tod_start < 8){
                $from_ampm = 'PM';        
            }
            // To AM or PM
            if($tod_end < 12 && $tod_end >= 8){
                $to_ampm = 'AM';
            }else if($tod_end < 8 || $tod_end >= 12){
                $to_ampm = 'PM'; 
            }

            $from_24_hour  = date("H:i", strtotime( "{$from_str} {$from_ampm}"));
            $to_24_hour  = date("H:i", strtotime( "{$to_str} {$to_ampm}"));        
            
            // Two hour period
            $pdf->Cell(11,0, $from_24_hour);
            $pdf->Cell(3,0, "-");
            $pdf->Cell(11,0, $to_24_hour);

        
            $pdf_name = "entry_notice_{$invoice_number}".date('Ymdhis').rand().".pdf";

            // 6 Signature of the lessor, agent or secondary agent
            $pdf->Ln(25);
            $pdf->Cell(6,0,"");     
            $pdf->Cell(90,0, "Daniel Kramarzewski");

            $pdf->Cell(65,0, ""); // Padding behind signature image
            
            $pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/DK_signature.png', 102, 253, 63); // Manually position image on PDF

            $pdf->Cell(9,0, date('d',strtotime($job_row->en_date_issued))); // dd
            $pdf->Cell(8.0,0, date('m',strtotime($job_row->en_date_issued))); // mm
            $pdf->Cell(17,0, date('Y',strtotime($job_row->en_date_issued))); // yy


            $pdf->addPage(); 
            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/en_qld_v2_p2.pdf');
            $tplidx = $pdf->importPage(1);        
            $pdf->useTemplate($tplidx, 0, 20);

            return $pdf->Output($pdf_name, $output);

        }


    }



    public function entry_notice_nsw($params){

        $job_id = $params['job_id'];
        $output = ( $params['output'] != '' )?$params['output']:'I';  
        $country_id = $this->config->item('country');     

        // get country data
        $country_params = array(
        'sel_query' => '
            c.`country_id`,
            c.`agent_number`, 
            c.`outgoing_email`, 
            c.`tenant_number`
        ',
        'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();

        
        if( $job_id ){

            $pdf = new jPDI();

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
            $invoice_number = "{$job_id}{$check_digit}";

            // pdf settings
            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer

            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/en_nsw.pdf');

            $tplidx = $pdf->importPage(1, '/MediaBox');               

            $pdf->addPage();       
            $pdf->useTemplate($tplidx, 0, 0, 210);  

            $pdf->SetFont('Arial','',11);

            // get job data
            $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`at_myob`,
            j.`sms_sent_merge`,
            j.`client_emailed`,
            j.`time_of_day`,
            j.`en_date_issued`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            
            a.`agency_id`,
            a.`agency_name`,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`send_emails`,
            a.`account_emails`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type,

            sa.`StaffID`,
            sa.`FirstName` AS tech_fname,
            sa.`LastName` AS tech_lname
            ";    
                
            $job_params = array(
                'sel_query' => $sel_query,
                
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id, 
                'job_id' => $job_id,
                            
                'join_table' => array('job_type','alarm_job_type','staff_accounts')                     
            );
            $job_sql = $this->jobs_model->get_jobs($job_params);
            $job_row = $job_sql->row();

            $property_id = $job_row->property_id;

            // Tenant details
            $pdf->setY(44); 

            if( $property_id > 0 ){              

                // get tenants 
                $sel_query = "
                    pt.`property_tenant_id`,
                    pt.`tenant_firstname`,
                    pt.`tenant_lastname`,
                    pt.`tenant_mobile`
                ";
                $params = array(
                    'sel_query' => $sel_query,
                    'property_id' => $property_id,
                    'pt_active' => 1,                   
                    'offset' => 0,
                    'limit' => 2,
                    'display_query' => 0
                );
                $pt_sql = $this->properties_model->get_property_tenants($params);
                $pt_num_row = $pt_sql->num_rows();

                foreach($pt_sql->result() as $pt_row){

                     // Tenant 
                    $pdf->Cell(23,0,"");     
                    $pdf->Cell(0,0, ucwords(strtolower($pt_row->tenant_firstname)).' '.ucwords(strtolower($pt_row->tenant_lastname)));
                    $pdf->Ln(5.3);

                }               

            }   
            
            // if only 1 tenant, but a blank space to preserve the spacing
            if( $pt_num_row == 1 ){
                // Tenant 
                $pdf->Cell(23,0,"");     
                $pdf->Cell(0,0, "");
                $pdf->Ln(5.3);
            }elseif($pt_num_row <= 0){ //add another cell if no tenants to fixed alignement:GHERX
                $pdf->Cell(23,0,"");     
                $pdf->Cell(0,0, "");
                $pdf->Ln(5.3);
                $pdf->Cell(23,0,"");     
                $pdf->Cell(0,0, "");
                $pdf->Ln(5.3);
            }

            ##fix for NZ macron char in address issue 
            setlocale(LC_CTYPE, 'en_US');
            $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_1." ".$job_row->p_address_2);
            $incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_3." ".$job_row->p_state);

            // street name and number 
            $pdf->Cell(23,0,"");     
           // $pdf->Cell(0,0, "{$job_row->p_address_1} {$job_row->p_address_2}");
            $pdf->Cell(0,0, "{$incov_val1}");

            // Suburb and Postcode
            $pdf->Ln(5.3);
            $pdf->Cell(23,0,"");     
            //$pdf->Cell(90,0, "{$job_row->p_address_3} {$job_row->p_state}");
            $pdf->Cell(90,0, "{$incov_val2}");
            $pdf->Cell(60,0, $job_row->p_postcode);

            // Other authorised secondary agent
            $pdf->Ln(13.5);     
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+112.8,$y_pos-0.4);
            $pdf->Cell(8,0, "X"); 

            $pdf->Ln(9.1);
            
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+16,$y_pos+0.8);
            $pdf->Cell(119,0, $this->config->item('COMPANY_FULL_NAME'));
            $pdf->Cell(30,0, $country_row->tenant_number);

            // Details of all people entering
            $pdf->Ln(19.3);
              
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+16,$y_pos+0.8);

            $initial_tech = substr($job_row->tech_lname, 0, 1).'.';
            $pdf->Cell(119,0, "{$job_row->tech_fname} {$initial_tech}");
            $pdf->Cell(30,0, $country_row->tenant_number);

            // Notice issued on
            $pdf->Ln(25.3);
               
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+16,$y_pos);
            $pdf->Cell(43,0, ( $job_row->en_date_issued != '' )?date('l',strtotime($job_row->en_date_issued)):null ); //eg Sunday
            $pdf->Cell(9,0, ( $job_row->en_date_issued != '' )?date('d',strtotime($job_row->en_date_issued)):null ); // dd
            $pdf->Cell(9.2,0, ( $job_row->en_date_issued != '' )?date('m',strtotime($job_row->en_date_issued)):null ); // mm
            $pdf->Cell(28,0, ( $job_row->en_date_issued != '' )?date('Y',strtotime($job_row->en_date_issued)):null ); // yy

            $pdf->Cell(15,0, "Email/SMS"); // Method of Issue

            $pdf->Ln(60.6);           

            // Entry to the property by the property owner/manager or other authorised secondary agent
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+16,$y_pos+11.5);
            $pdf->Cell(43,0, ( $job_row->j_date != '' )?date('l',strtotime($job_row->j_date)):null ); // Smoke alarm act
            $pdf->Cell(9,0, ( $job_row->j_date != '' )?date('d',strtotime($job_row->j_date)):null ); // dd
            $pdf->Cell(9.2,0, ( $job_row->j_date != '' )?date('m',strtotime($job_row->j_date)):null ); // mm
            $pdf->Cell(28,0, ( $job_row->j_date != '' )?date('Y',strtotime($job_row->j_date)):null ); // yy

            # Prepare Time of Day
            $tod_fixed = trim(preg_replace("/[^:.\-0-9\s]/", "", $job_row->time_of_day));
            $tod_fixed = str_replace("-", " ", $tod_fixed);
            $tod_fixed = preg_replace("/\s{2,}/", " ", $tod_fixed);
            $tod_fixed = str_replace(":", ".", $tod_fixed);


            $tmp = explode(" ", $tod_fixed);
            $tod_start = number_format($tmp[0], 2);
            $tod_end = number_format($tmp[1], 2);
            
            // replace . with :
            $from_str = str_replace(".", ":", $tod_start);
            $to_str = str_replace(".", ":", $tod_end);
            
            // From AM or PM
            if($tod_start < 12 && $tod_start >= 6){
                $from_ampm = 'AM';
            }else if($tod_start < 8){
                $from_ampm = 'PM';        
            }
            // To AM or PM
            if($tod_end < 12 && $tod_end >= 8){
                $to_ampm = 'AM';
            }else if($tod_end < 8 || $tod_end >= 12){
                $to_ampm = 'PM'; 
            }

            $from_24_hour  = date("H:i", strtotime( "{$from_str} {$from_ampm}"));
            $to_24_hour  = date("H:i", strtotime( "{$to_str} {$to_ampm}"));        
            
            // Two hour period
            $pdf->Cell(11,0, $from_24_hour);
            $pdf->Cell(3,0, "-");
            $pdf->Cell(11,0, $to_24_hour);

        
            $pdf_name = "entry_notice_{$invoice_number}".date('Ymdhis').rand().".pdf";

            // Signature of the property owner/manager or other authorised secondary agent
            $pdf->setXY($x_pos+10,$y_pos+31);            
            $pdf->Cell(6,0,"");     
            $pdf->Cell(89,0, "Daniel Kramarzewski");
            
            $pdf->Cell(9,0, date('d',strtotime($job_row->en_date_issued))); // dd
            $pdf->Cell(8.0,0, date('m',strtotime($job_row->en_date_issued))); // mm
            $pdf->Cell(17,0, date('Y',strtotime($job_row->en_date_issued))); // yy

            $pdf->Cell(30,0, ""); // Padding behind signature image

            //$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/signature.png', 38, 228, 50); // Manually position image on PDF
            $pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/DK_signature.png', 33, 221, 70);

            return $pdf->Output($pdf_name, $output);

        }


    }


    public function entry_notice_act($params){

        $job_id = $params['job_id'];
        $output = ( $params['output'] != '' )?$params['output']:'I';  
        $country_id = $this->config->item('country');     

        // get country data
        $country_params = array(
        'sel_query' => '
            c.`country_id`,
            c.`agent_number`, 
            c.`outgoing_email`, 
            c.`tenant_number`
        ',
        'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();

        
        if( $job_id ){

            $pdf = new jPDI();

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
            $invoice_number = "{$job_id}{$check_digit}";

            // pdf settings
            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer

            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/en_act.pdf');

            $tplidx = $pdf->importPage(1, '/MediaBox');               

            $pdf->addPage();       
            $pdf->useTemplate($tplidx, 0, 0, 210);  

            $pdf->SetFont('Arial','',11);

            // get job data
            $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`at_myob`,
            j.`sms_sent_merge`,
            j.`client_emailed`,
            j.`time_of_day`,
            j.`en_date_issued`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            
            a.`agency_id`,
            a.`agency_name`,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`send_emails`,
            a.`account_emails`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type,

            sa.`StaffID`,
            sa.`FirstName` AS tech_fname,
            sa.`LastName` AS tech_lname
            ";    
                
            $job_params = array(
                'sel_query' => $sel_query,
                
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id, 
                'job_id' => $job_id,
                            
                'join_table' => array('job_type','alarm_job_type','staff_accounts')                     
            );
            $job_sql = $this->jobs_model->get_jobs($job_params);
            $job_row = $job_sql->row();

            $property_id = $job_row->property_id;

            // Tenant details
            $pdf->setY(44); 

            if( $property_id > 0 ){              

                // get tenants 
                $sel_query = "
                    pt.`property_tenant_id`,
                    pt.`tenant_firstname`,
                    pt.`tenant_lastname`,
                    pt.`tenant_mobile`
                ";
                $params = array(
                    'sel_query' => $sel_query,
                    'property_id' => $property_id,
                    'pt_active' => 1,                   
                    'offset' => 0,
                    'limit' => 2,
                    'display_query' => 0
                );
                $pt_sql = $this->properties_model->get_property_tenants($params);
                $pt_num_row = $pt_sql->num_rows();

                foreach($pt_sql->result() as $pt_row){

                     // Tenant 
                    $pdf->Cell(23,0,"");     
                    $pdf->Cell(0,0, ucwords(strtolower($pt_row->tenant_firstname)).' '.ucwords(strtolower($pt_row->tenant_lastname)));
                    $pdf->Ln(5.3);

                }               

            }   
            
            // if only 1 tenant, but a blank space to preserve the spacing
            if( $pt_num_row == 1 ){
                // Tenant 
                $pdf->Cell(23,0,"");     
                $pdf->Cell(0,0, "");
                $pdf->Ln(5.3);
            }elseif($pt_num_row <= 0){ //add another cell if no tenants to fixed alignement:GHERX
                $pdf->Cell(23,0,"");     
                $pdf->Cell(0,0, "");
                $pdf->Ln(5.3);
                $pdf->Cell(23,0,"");     
                $pdf->Cell(0,0, "");
                $pdf->Ln(5.3);
            }

            ##fix for NZ macron char in address issue 
            setlocale(LC_CTYPE, 'en_US');
            $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_1." ".$job_row->p_address_2);
            $incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_3." ".$job_row->p_state);

            
            // street name and number 
            $pdf->Cell(23,0,"");     
           // $pdf->Cell(0,0, "{$job_row->p_address_1} {$job_row->p_address_2}");
            $pdf->Cell(0,0, "{$incov_val1}");

            // Suburb and Postcode
            $pdf->Ln(5.3);
            $pdf->Cell(23,0,"");     
            //$pdf->Cell(90,0, "{$job_row->p_address_3} {$job_row->p_state}");
            $pdf->Cell(90,0, "{$incov_val2}");
            $pdf->Cell(60,0, $job_row->p_postcode);

            // Other authorised secondary agent
            $pdf->Ln(13.5);     
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+112.8,$y_pos-0.4);
            $pdf->Cell(8,0, "X"); 

            $pdf->Ln(9.1);
            
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+16,$y_pos+0.8);
            $pdf->Cell(119,0, $this->config->item('COMPANY_FULL_NAME'));
            $pdf->Cell(30,0, $country_row->tenant_number);

            // Details of all people entering
            $pdf->Ln(19.3);
              
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+16,$y_pos+0.8);

            $initial_tech = substr($job_row->tech_lname, 0, 1).'.';
            $pdf->Cell(119,0, "{$job_row->tech_fname} {$initial_tech}");
            $pdf->Cell(30,0, $country_row->tenant_number);

            // Notice issued on
            $pdf->Ln(25.3);
               
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+16,$y_pos);
            $pdf->Cell(43,0, ( $job_row->en_date_issued != '' )?date('l',strtotime($job_row->en_date_issued)):null ); //eg Sunday
            $pdf->Cell(9,0, ( $job_row->en_date_issued != '' )?date('d',strtotime($job_row->en_date_issued)):null ); // dd
            $pdf->Cell(9.2,0, ( $job_row->en_date_issued != '' )?date('m',strtotime($job_row->en_date_issued)):null ); // mm
            $pdf->Cell(28,0, ( $job_row->en_date_issued != '' )?date('Y',strtotime($job_row->en_date_issued)):null ); // yy

            $pdf->Cell(15,0, "Email/SMS"); // Method of Issue

            $pdf->Ln(60.6);           

            // Entry to the property by the property owner/manager or other authorised secondary agent
            $x_pos = $pdf->GetX();
            $y_pos = $pdf->GetY();
            $pdf->setXY($x_pos+16,$y_pos+11.5);
            $pdf->Cell(43,0, ( $job_row->j_date != '' )?date('l',strtotime($job_row->j_date)):null ); // Smoke alarm act
            $pdf->Cell(9,0, ( $job_row->j_date != '' )?date('d',strtotime($job_row->j_date)):null ); // dd
            $pdf->Cell(9.2,0, ( $job_row->j_date != '' )?date('m',strtotime($job_row->j_date)):null ); // mm
            $pdf->Cell(28,0, ( $job_row->j_date != '' )?date('Y',strtotime($job_row->j_date)):null ); // yy

            # Prepare Time of Day
            $tod_fixed = trim(preg_replace("/[^:.\-0-9\s]/", "", $job_row->time_of_day));
            $tod_fixed = str_replace("-", " ", $tod_fixed);
            $tod_fixed = preg_replace("/\s{2,}/", " ", $tod_fixed);
            $tod_fixed = str_replace(":", ".", $tod_fixed);


            $tmp = explode(" ", $tod_fixed);
            $tod_start = number_format($tmp[0], 2);
            $tod_end = number_format($tmp[1], 2);
            
            // replace . with :
            $from_str = str_replace(".", ":", $tod_start);
            $to_str = str_replace(".", ":", $tod_end);
            
            // From AM or PM
            if($tod_start < 12 && $tod_start >= 6){
                $from_ampm = 'AM';
            }else if($tod_start < 8){
                $from_ampm = 'PM';        
            }
            // To AM or PM
            if($tod_end < 12 && $tod_end >= 8){
                $to_ampm = 'AM';
            }else if($tod_end < 8 || $tod_end >= 12){
                $to_ampm = 'PM'; 
            }

            $from_24_hour  = date("H:i", strtotime( "{$from_str} {$from_ampm}"));
            $to_24_hour  = date("H:i", strtotime( "{$to_str} {$to_ampm}"));        
            
            // Two hour period
            $pdf->Cell(11,0, $from_24_hour);
            $pdf->Cell(3,0, "-");
            $pdf->Cell(11,0, $to_24_hour);

        
            $pdf_name = "entry_notice_{$invoice_number}".date('Ymdhis').rand().".pdf";

            // Signature of the property owner/manager or other authorised secondary agent
            $pdf->setXY($x_pos+10,$y_pos+31);            
            $pdf->Cell(6,0,"");     
            $pdf->Cell(89,0, "Daniel Kramarzewski");
            
            $pdf->Cell(9,0, date('d',strtotime($job_row->en_date_issued))); // dd
            $pdf->Cell(8.0,0, date('m',strtotime($job_row->en_date_issued))); // mm
            $pdf->Cell(17,0, date('Y',strtotime($job_row->en_date_issued))); // yy

            $pdf->Cell(30,0, ""); // Padding behind signature image

            //$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/signature.png', 38, 228, 50); // Manually position image on PDF
            $pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/DK_signature.png', 33, 221, 70);

            return $pdf->Output($pdf_name, $output);

        }


    }


    public function entry_notice_generic($params){

        $job_id = $params['job_id'];
        $output = ( $params['output'] != '' )?$params['output']:'I';  
        $country_id = $this->config->item('country');  

        // get country data
        $country_params = array(
        'sel_query' => '
            c.`country_id`,
            c.`agent_number`, 
            c.`outgoing_email`, 
            c.`tenant_number`
        ',
        'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $invoice_number = "{$job_id}{$check_digit}";

        if( $job_id ){

            $pdf = new jPDI();

            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer

            $pdf->setMargins(35, 35, 35); // Left margin 3.5mm
            $pdf->addPage(); 
        
            $pdf->SetFont('Arial','',11); 
        

            // get job data
            $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`at_myob`,
            j.`sms_sent_merge`,
            j.`client_emailed`,
            j.`time_of_day`,
            j.`en_date_issued`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            
            a.`agency_id`,
            a.`agency_name`,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`send_emails`,
            a.`account_emails`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type,

            sa.`StaffID`,
            sa.`FirstName` AS tech_fname,
            sa.`LastName` AS tech_lname
            ";    
                
            $job_params = array(
                'sel_query' => $sel_query,
                
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id, 
                'job_id' => $job_id,
                            
                'join_table' => array('job_type','alarm_job_type','staff_accounts')                     
            );
            $job_sql = $this->jobs_model->get_jobs($job_params);
            $job_row = $job_sql->row();

            $property_id = $job_row->property_id;

            // Tenant details
            $pdf->setY(50); 

            if( $property_id > 0 ){              

                // get tenants 
                $sel_query = "
                    pt.`property_tenant_id`,
                    pt.`tenant_firstname`,
                    pt.`tenant_lastname`,
                    pt.`tenant_mobile`
                ";
                $params = array(
                    'sel_query' => $sel_query,
                    'property_id' => $property_id,
                    'pt_active' => 1,  
                    'offset' => 0,
                    'limit' => 2,                                   
                    'display_query' => 0
                );
                $pt_sql = $this->properties_model->get_property_tenants($params);
                $pt_num_row = $pt_sql->num_rows();

                foreach($pt_sql->result() as $pt_row){

                    // Tenant        
                    $tenants_names_arr[] = ucwords(strtolower($pt_row->tenant_firstname));                                     
                    $pdf->Cell(0,5, ucwords(strtolower($pt_row->tenant_firstname)).' '.ucwords(strtolower($pt_row->tenant_lastname)),0,1);                 

                }               

            }

            ##fix for NZ macron char in address issue 
            setlocale(LC_CTYPE, 'en_US');
            $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_1." ".$job_row->p_address_2);
            $incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_3." ".$job_row->p_state." ".$job_row->p_postcode);

            
            $pdf->Cell(0,5, "{$incov_val1}",0,1);
            $pdf->Cell(180,5, "{$incov_val2}",0,1);

            // Greeting Line
            $pdf->Ln(15.4);

            if( count( $tenants_names_arr ) > 1 ){
		
                // Tenant 
                $tenant_str_imp = implode(", ",$tenants_names_arr); // separate tenant names with a comma
                $last_comma_pos = strrpos($tenant_str_imp,","); // find the last comma(,) position
                $tenant_str = substr_replace($tenant_str_imp,' &',$last_comma_pos,1); // replace comma with ampersand(&)
                $pdf->Cell(0,0, "Dear ".$tenant_str);
        
                
            }else{
                
                $pdf->Cell(0,0, "Dear ".$tenants_names_arr[0]);
                
            }

            $tech_initial = substr($job_row->tech_lname, 0, 1).'.';
            $tech_name = "{$job_row->tech_fname} {$tech_initial}";

            // Email Body
            if( $country_id == 1 ){ // AU

                // Immediate Access Required
                $pdf->Ln(13.4);
                $pdf->SetFont('', 'BU', 13);
                $pdf->Cell(0,0, "IMMEDIATE ACCESS REQUIRED", 0, 0, 'C');
                $pdf->SetFont('Arial','',11); 
                
                $pdf->Ln(15.4);
                $pdf->MultiCell(0,5, "Recently your Landlord and Property Manager have engaged the services of {$this->config->item('COMPANY_FULL_NAME')} to undertake Smoke Alarm Maintenance and Testing Services on the property you occupy.");
                $pdf->Ln(5.4);
                $pdf->MultiCell(0,5, $this->config->item('COMPANY_FULL_NAME') . " needs to attend your property to undertake works on the installed smoke alarms to ensure the smoke alarms within the property are correctly working and compliant with legislation.");
                $pdf->Ln(5.4);
            
            
                $pdf->MultiCell(0,5, "Adhering to the Residential Tenancy Agreement 23.7 to carry out, or assess the need for, work relating to statutory health and safety obligations relating to the residential premises, if the tenant is given at least 2 days notice each time. ");
                $pdf->Ln(5.4);
                $pdf->MultiCell(0,5, "SATS will be attending your property on ".( ( $job_row->j_date != '' )?date('d/m/Y',strtotime($job_row->j_date)):null )." between {$job_row->time_of_day}. We will be obtaining the keys from {$job_row->agency_name} to carry out the service. Please call SATS on {$country_row->tenant_number} if there are any issues.");
                
            }else if( $country_id == 2 ){ // NZ

                // Immediate Access Required
                $pdf->Ln(13.4);
                $pdf->SetFont('', 'BU', 13);
                $pdf->Cell(0,0, "NOTICE TO ENTER PREMISES - SMOKE ALARM INSPECTION", 0, 0, 'C');
            
                $pdf->SetFont('Arial','',10); 
                $pdf->Ln(10);
                $pdf->MultiCell(0,5, "I hereby give you notice that SATS will enter the above premises on ".( ( $job_row->j_date != '' )?date('d/m/Y',strtotime($job_row->j_date)):null )." between {$job_row->time_of_day}.");
                $pdf->Ln(5.4);
                $pdf->MultiCell(0,5, "Purpose of visit: To inspect/service/install smoke alarms as per The Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.");
                $pdf->Ln(5.4);
                $pdf->MultiCell(0,5, "As part of the Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016, landlords are required to ensure that there are correctly installed, maintained and fully operational smoke alarms in all residential rental properties.");
                $pdf->Ln(5.4);
                $pdf->MultiCell(0,5, "As per the request of {$job_row->agency_name}, this notice is issued by Smoke Alarm Testing Services who, as the industry leader in smoke alarm servicing and maintenance, have been authorised to act as a Secondary Agent on behalf of the Landlord.");
                $pdf->Ln(5.4);
                $pdf->MultiCell(0,5, "The date and time frame of our attendance is detailed above. Our technician, {$tech_name} will collect the keys from your agency the morning of the inspection therefore there is no need to for you to be home when we attend the property. Our technicians are company employees who wear photo identification, drive sign written vehicles and have been extensively trained in customer service.");
                $pdf->Ln(5.4);
                $pdf->MultiCell(0,5, "Please call SATS on {$country_row->tenant_number} if there are any issues.");
                $pdf->Ln(5.4);	
                
            }

            // Yours Faithfully
            $pdf->Ln(10);
            $pdf->Cell(0,0, "Yours Faithfully,");
            
            // Signature (manually placed with padding ln())
            // $pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/DK_signature.png', 34, 185, 70); // Manually position image on PDF
            $pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/DK_signature.png', 24, $pdf->GetY()-5, 70); // Manually position image on PDF
            

            // SATS, number and agent name
            $pdf->Ln(30);
            $pdf->Cell(0,0, "{$this->config->item('COMPANY_FULL_NAME')} ({$country_row->tenant_number})");

            $pdf->Ln(7);
            $pdf->Cell(157,0, "Technician Attending: {$tech_name}");

    
            $pdf_name = "entry_notice_{$invoice_number}".date('Ymdhis').rand().".pdf";
    
            return $pdf->Output($pdf_name, $output);

        }       


    }


    public function swms($params){

        $job_id = $params['job_id'];
        $swms_type = $params['swms_type'];
        $output = ( $params['output'] != '' )?$params['output']:'I';  
        $country_id = $this->config->item('country');     

        switch($swms_type){
            case 'heights':
                $swms_pdf = 'SWMS heights.pdf';
            break;
            case 'uv_protection':
                $swms_pdf = 'SWMS UV protection.pdf';
            break;
            case 'asbestos':
                $swms_pdf = 'SWMS asbestos.pdf';
            break;
            case 'powertools':
                $swms_pdf = 'SWMS powertools.pdf';
            break;
            case 'animals':
                $swms_pdf = 'SWMS Animals.pdf';
            break;
            case 'live_circuits':
                $swms_pdf = 'SWMS Isolating circuits.pdf';
            break;
            case 'covid_19':
                $swms_pdf = 'SWMS COVID-19.pdf';
            break;            
        }

        
        if( $job_id ){

            $pdf = new jPDI();

            $path = getcwd() .'/documents/tech/'.$swms_pdf;

            // pdf settings
            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer
            
            $pagecount = $pdf->setSourceFile($path);
            $tplidx = $pdf->importPage(1, '/MediaBox');  
        
            $pdf->addPage(); 
            $pdf->useTemplate($tplidx, 0, 0, 210);

            $x_pos = 69;
            $y_pos = 48;
        
        
            $pdf->SetFont('Arial','',11); 
            
            $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`date` AS j_date,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`urgent_job`,
            j.`job_reason_id`,
            
            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            p.`deleted` AS p_deleted,
            
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
           
            t.`StaffID` AS tech_id,
            t.`FirstName` AS tech_fname,
            t.`LastName` AS tech_lname
            ";

            
            $params = array(
                'sel_query' => $sel_query,

                'job_id' => $job_id, 
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,  

                'join_table' => array('tech'),

                'display_query' => 0
            );

            $job_sql = $this->jobs_model->get_jobs($params);
            $job_row = $job_sql->row();
            $tech_name = "{$job_row->tech_fname} {$job_row->tech_lname}";
                
            // Work Location
            $pdf->setXY($x_pos,$y_pos); 
            $pdf->Cell(21,0,"");     
            $pdf->Cell(0,0, $job_row->p_address_1." ".$job_row->p_address_2." ".$job_row->p_address_3); 
            
            // Person responsible for ensuring compliance with SWMS
            $pdf->setXY($x_pos,$y_pos+30);  
            $pdf->Cell(21,0,"");     
            $pdf->Cell(0,0,$tech_name); 	
                
            // Workers Name
            $pdf->setXY($x_pos,$y_pos+53); 
            $pdf->Cell(21,0,"");     
            $pdf->Cell(0,0,$tech_name); 
            
            // Date Received
            $pdf->setXY($x_pos,$y_pos+58); 
            $pdf->Cell(21,0,"");     
            $pdf->Cell(0,0, date('d/m/Y',strtotime($job_row->j_date))); 
            
        
            $pdf->Output('swms.pdf', 'I');
            

        }


    }


    public function combined_qoutes($params){

        $today = date('Y-m-d');
        $nov_date = date('2022-11-18');
        $job_id = $params['job_id'];
        $job_details = $params['job_details'];
        $property_details = $params['property_details'];        
        $pdf_name = $params['pdf_name'];        

        $output = ( $params['output'] != '' )?$params['output']:'I';  
        $country_id = $this->config->item('country');  
        
        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}";       

        // get country data
        $country_params = array(
        'sel_query' => '
            c.`country_id`,
            c.`agent_number`, 
            c.`outgoing_email`, 
            c.`tenant_number`
        ',
        'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();

        
        if( $job_id ){

            $pdf = new jPDI();

            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
            $invoice_number = "{$job_id}{$check_digit}";

            // pdf settings
            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer

            //echo $_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/combined_quotes.pdf';

            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/combined_quotes_2.pdf');

            // 1st page
            $tplidx = $pdf->importPage(1, '/MediaBox');   
            $size = $pdf->getTemplateSize($tplidx);    
            
            //$pdf->SetAutoPageBreak(true,35);
            $pdf->SetAutoPageBreak(false);
            
            $pdf->AddPage();    
            $pdf->useTemplate($tplidx, 0, 0, 212);  
                
            
            $qoute_number = ( $job_details['tmh_id'] != '' )?str_pad($job_details['tmh_id'] . ' TMH-Q', 6, "0", STR_PAD_LEFT):$bpay_ref_code.'Q';

            $pos_x = $pdf->GetX();
            $pos_y = $pdf->GetY();

            $pdf->SetXY($pos_x+123,$pos_y+16.5); 

            // QUOTE number
            $pdf->SetFont('Arial','B',20);
            $pdf->SetTextColor(255, 255, 255); // white 
            $pdf->Cell(23,5,$qoute_number,0,1); 
            $pdf->SetTextColor(0, 0, 0); // put back to black 

            $pos_x = $pdf->GetX();
            $pos_y = $pdf->GetY();
            
            $pdf->SetFont('Arial',null,11);

            // Quote Date              
            //$pdf->SetXY($pos_x+33,$pos_y+23);    
            $pdf->SetXY($pos_x+39,$pos_y+23.5);    
            $pdf->Cell(23,5,$job_details['date']);   
            
            // PROPERTY ADDRESS
            ##fix for NZ macron char in address issue 
            setlocale(LC_CTYPE, 'en_US');

            $pos_x = $pdf->GetX();
            $pos_y = $pdf->GetY();
            //$pdf->SetXY($pos_x+50.3,$pos_y+6);
            $pdf->SetXY($pos_x+45.5,$pos_y+6);
            //$prop_address = "{$property_details['address_1']} {$property_details['address_2']}, {$property_details['address_3']}\n{$property_details['state']} {$property_details['postcode']}";
            //$pdf->MultiCell(80,5,$prop_address,0);

            $prop_address = "{$property_details['address_1']} {$property_details['address_2']}, {$property_details['address_3']}\n{$property_details['state']} {$property_details['postcode']}";
            $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $prop_address);
            $pdf->MultiCell(80,5,$incov_val1,0);

            // ATTN
            $pos_x = $pdf->GetX();
            $pos_y = $pdf->GetY();
            //$pdf->SetXY($pos_x+21.5,$pos_y-4.5);            
            $pdf->SetXY($pos_x+30.5,$pos_y-4.8);            
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(50,5,'CARE OF THE OWNER'); 
            $pdf->SetFont('Arial',null,11);
            $pdf->Ln(5);  

            // agency name and address
            $pos_x = $pdf->GetX();
            $pos_y = $pdf->GetY();
            //$pdf->SetXY($pos_x+8.8,$pos_y+2);  
            $pdf->SetXY($pos_x+16.5,$pos_y+2);  
            $agency_name = "C/- {$property_details['agency_name']}";          
            $agency_address = "{$property_details['a_address_1']} {$property_details['a_address_2']}, {$property_details['a_address_3']}\n{$property_details['a_state']} {$property_details['a_postcode']}";
            $attn_text = "{$agency_name}\n{$agency_address}";
            $pdf->MultiCell(95,5,$attn_text,0);
         
            // LANDLORD
            $pos_x = $pdf->GetX();
            $pos_y = $pdf->GetY();

            $pdf->SetXY($pos_x+107,$pos_y-10.8);  
            $pdf->SetFont('Arial','B',11);                
            $pdf->Cell(23,5,( ( $property_details['landlord_firstname'] != '' || $property_details['landlord_lastname'] != '' )?'LANDLORD:':null )); 
            $pdf->SetFont('Arial',null,11);

            $pdf->SetXY($pos_x+131,$pos_y-10.8);      
            $landlord =  "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}"; 
            $pdf->Cell(23,5,( ( $property_details['landlord_firstname'] != '' || $property_details['landlord_lastname'] != '' )?$landlord:null )); 
            $pdf->SetFont('Arial',null,10);  

            $pos_x = $pdf->GetX();
            $pos_y = $pdf->GetY();
            $pdf->SetXY($pos_x,$pos_y); 
            $pdf->Ln(90);            

            // OPTIONS
            $pdf->SetFont('Arial',null,10); 
            $cell_width = 47;
            $cell_height = 4;            
            $cell_border = 0;
            $new_line = 1;
            $align = 'C';

            $pos_x = $pdf->GetX();            
            $pos_y = $pdf->GetY();                    

            // brooks
            $quote_qty = $job_details['qld_new_leg_alarm_num'];
            $price_240vrf_brooks_price = $this->get240vRfAgencyAlarm($property_details['agency_id']);
            $price_240vrf_brooks_price_final = ( $price_240vrf_brooks_price > 0 )?$price_240vrf_brooks_price:$this->config->item('default_qld_upgrade_quote_price');
            $quote_total_brooks = $price_240vrf_brooks_price_final*$quote_qty;              

            // brooks column position
           // $col_position_x = $pos_x+13;
           // $col_position_y = $pos_y+2;

            $col_position_x = $pos_x+31;
            $col_position_y = $pos_y+4;
            $pdf->SetXY($col_position_x,$col_position_y);  

            $pdf->Cell($cell_width,$cell_height,"{$quote_qty} x Brooks Interconnected",$cell_border,$new_line,$align);

            $pos_x = $pdf->GetX();

            $pdf->SetX($col_position_x); 
            $pdf->Cell($cell_width,$cell_height,"Photo Electric Smoke Alarms",$cell_border,$new_line,$align); 

            $pdf->SetX($col_position_x);         
            $pdf->Cell(10,$cell_height,'@',$cell_border,0,'R');

            $pdf->SetFont('Arial','B',10);
            $qoute_amount_txt = "\$".number_format($price_240vrf_brooks_price_final,2)." EA = \$".number_format($quote_total_brooks,2);
            $pdf->Cell(37,$cell_height,$qoute_amount_txt,$cell_border,$new_line,'L');
            $pdf->SetFont('Arial',null,10);

            $pdf->SetX($col_position_x);         
            $pdf->Cell($cell_width,$cell_height,'Inc. GST',$cell_border,$new_line,$align);   
            
            
            // cavius
            /*$quote_qty = $job_details['qld_new_leg_alarm_num'];
            $price_240vrf_cavius_price = $this->get240vRf_cavius_AgencyAlarm($property_details['agency_id']);
            $price_240vrf_cavius_price_final = ( $price_240vrf_cavius_price > 0 )?$price_240vrf_cavius_price:$this->config->item('default_qld_upgrade_quote_price');
            $quote_total_cavius = $price_240vrf_cavius_price_final*$quote_qty;

            // cavius column position
            $col_position_x = $col_position_x+59;
            $pdf->SetXY($col_position_x,$col_position_y);                      
            
            $pdf->SetX($col_position_x); 
            $pdf->Cell($cell_width,$cell_height,"{$quote_qty} x Cavius Interconnected",$cell_border,$new_line,$align);          

            $pdf->SetX($col_position_x);      
            $pdf->Cell($cell_width,$cell_height,"Photo Electric Smoke Alarms",$cell_border,$new_line,$align);

            $pdf->SetX($col_position_x);        
            $pdf->Cell(10,$cell_height,'@',$cell_border,0,'R');

            $pdf->SetFont('Arial','B',10);
            $qoute_amount_txt = "\$".number_format($price_240vrf_cavius_price_final,2)." EA = \$".number_format($quote_total_cavius,2);
            $pdf->Cell(37,$cell_height,$qoute_amount_txt,$cell_border,$new_line,'L');
            $pdf->SetFont('Arial',null,10);

            $pdf->SetX($col_position_x);        
            $pdf->Cell($cell_width,$cell_height,'Inc. GST',$cell_border,$new_line,$align);   */

           
            // emerald
            $quote_qty = $job_details['qld_new_leg_alarm_num'];
            $price_240vrf_emerald_price = $this->get_emerald_AgencyAlarm($property_details['agency_id']);
            $price_240vrf_emerald_price_final = ( $price_240vrf_emerald_price > 0 )?$price_240vrf_emerald_price:$this->config->item('default_qld_upgrade_quote_price');
            $quote_total_cavius = $price_240vrf_emerald_price_final*$quote_qty;

            $cell_width = 50;

            // emerald planet column position            
            $pdf->SetFont('Arial',null,9);
            //$col_position_x = $col_position_x+56;
            $col_position_x = $col_position_x+79;
            $pdf->SetXY($col_position_x,$col_position_y);                      
            
            $pdf->SetX($col_position_x); 
            $pdf->Cell($cell_width,$cell_height,"{$quote_qty} x Quality Interconnected",$cell_border,$new_line,$align);          

            $pdf->SetX($col_position_x);      
            $pdf->Cell($cell_width,$cell_height,"Photo Electric Smoke Alarms",$cell_border,$new_line,$align);

            $pdf->SetX($col_position_x);        
            $pdf->Cell(10,$cell_height,'@',$cell_border,0,'R');
                        
            $pdf->SetFont('Arial','B',10);
            $qoute_amount_txt = "\$".number_format($price_240vrf_emerald_price_final,2)." EA = \$".number_format($quote_total_cavius,2);
            $pdf->Cell(40,$cell_height,$qoute_amount_txt,$cell_border,$new_line,'L');
            $pdf->SetFont('Arial',null,10);
            
            $pdf->SetX($col_position_x); 
            $pdf->Cell($cell_width,$cell_height,'Inc. GST',$cell_border,$new_line,$align);  

            $pos_x = $pdf->GetX();
            $pos_y = $pdf->GetY();
            //$pdf->SetXY($pos_x+56,$pos_y+97.4); 
            $pdf->SetXY($pos_x+60,$pos_y+91.4); 
            
            $pdf->SetFont('Arial','B',13);
            $pdf->SetTextColor(255, 255, 255); // white 
            $pdf->Cell($cell_width,$cell_height,date( 'd/m/Y', strtotime( "+6 months", strtotime($nov_date) ) ),$cell_border,$new_line,$align);    
            $pdf->SetTextColor(0, 0, 0); // put back to black                        

            return $pdf->Output($pdf_name, $output);

        }

    }


    //New certificate template > Ness request
    public function pdf_certificate_template_v2($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, $output = "", $is_copy = false){

        $this->updateInvoiceDetails($job_id);

        // get service
        $os_sql = $this->job_functions_model->getService($job_details['jservice']); 
        $os = $os_sql->row_array();
        $property_job_types = $this->job_functions_model->getTechSheetAlarmTypesJob($job_details['property_id'], true);

        #instantiate only if required
        $pdf=new jPDI();

        $pdf->set_dont_display_header(1); // hide the header
        $pdf->set_dont_display_footer(1); // hide the footer
        $pdf->is_compliance_second_page_bg(1);
        $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/sats_statement_of_compliance_21202021_v2_template.pdf');

        $tplidx = $pdf->importPage(1, '/MediaBox');   
        
        $pdf->SetTopMargin(48);
        $pdf->SetAutoPageBreak(true,65);
        
        $pdf->AddPage();    
        //$pdf->useTemplate($tplidx, 0, 0, 210);  //AL: disabled already initiated in libraray at header function (Only for compliance pdf)

        if( $is_copy == true ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/copy.png',160,60,30);
        }    

        //property

        ##fix for NZ macron char issue 
        setlocale(LC_CTYPE, 'en_US');
        $full_address1 = $property_details['address_1']." ".$property_details['address_2'];
        $full_address2 = $property_details['address_3']." ".$property_details['state'].", ".$property_details['postcode'];
        $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $full_address1);
        $incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $full_address2);
        ##fix for NZ macron char issue end

        $pos_x = $pdf->GetX();
        $pos_y = $pdf->GetY();
        $pdf->SetFont('Arial','B',11);
        $pdf->SetXY($pos_x+14,$pos_y);   
        $pdf->SetFont('Arial','',11);
        //$pdf->Cell(30,5,$property_details['address_1'] . " " . $property_details['address_2']);
        $pdf->Cell(30,5, $incov_val1);
        $pdf->Ln();
        $pdf->Cell(14,5,"");
        //$pdf->Cell(30,5,$property_details['address_3'] . " " . $property_details['state'] . ", " .$property_details['postcode'] );
        $pdf->Cell(30,5,$incov_val2 );
        
        //Compliance icon here
        //property status
        $is_not_compliant = false;
        $is_holiday_rent = false;
        if( $property_details['state']=="QLD" ){

            
            ##prop_comp_with_state_leg = QLD compliant
            ##prop_upgraded_to_ic_sa = Not 2022 Compliant
           /*
            if( ( $job_details['prop_upgraded_to_ic_sa']==0 &&  $job_details['prop_upgraded_to_ic_sa']!="") && ($job_details['prop_comp_with_state_leg']==0 && $job_details['prop_comp_with_state_leg']!="") ){
                $prop_status_icon = "/images/combine_notComp_and_not2022.png";
                $is_not_compliant = true;
            }elseif( ($job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="") && ($job_details['prop_upgraded_to_ic_sa']==0 && $job_details['prop_upgraded_to_ic_sa']!="") ){
                $prop_status_icon = "/images/combine_currentlyComp_and_not2022.png";
                $is_not_compliant = true;
            }else{
                if( $job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="" ){
                    $prop_status_icon = "/images/currently_compliant.png";
                }else{
                    $prop_status_icon = "/images/not_compliant.png";
                    $is_not_compliant = true;
                }
            }
            */

            if( ( $job_details['prop_upgraded_to_ic_sa']==0 &&  $job_details['prop_upgraded_to_ic_sa']!="") && ($job_details['prop_comp_with_state_leg']==0 && $job_details['prop_comp_with_state_leg']!="") ){
                
                //if( ($job_details['marker_id']!="" && $job_details['marker_id']==1) && $job_details['holiday_rental']==1 ){ //hide NOT 2022 Compliant
                if( $job_details['marker_id']=="" && $job_details['holiday_rental']==1 ){ //hide NOT 2022 Compliant
                    $prop_status_icon = "/images/not_compliant.png";
                }else{
                    $prop_status_icon = "/images/combine_notComp_and_not2022.png";
                }

                $is_not_compliant = true;

            }elseif( ($job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="") && ($job_details['prop_upgraded_to_ic_sa']==0 && $job_details['prop_upgraded_to_ic_sa']!="") ){
                
                //if( ($job_details['marker_id']!="" && $job_details['marker_id']==1) && $job_details['holiday_rental']==1 ){ //hide NOT 2022 Compliant
                if( $job_details['marker_id']=="" && $job_details['holiday_rental']==1 ){ //hide NOT 2022 Compliant
                    $prop_status_icon = "/images/currently_compliant.png";
                }else{
                    if( $job_details['holiday_rental']==1 && $job_details['state'] == 'QLD' ){
                        $prop_status_icon = "/images/not_compliant_shortTemRental.png";
                        $is_holiday_rent = true;
                    }else{
                        $prop_status_icon = "/images/combine_currentlyComp_and_not2022.png";
                    }
                }
                $is_not_compliant = true;

            }else{
                if( $job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="" ){
                    $prop_status_icon = "/images/currently_compliant.png";
                }else{
                    $prop_status_icon = "/images/not_compliant.png";
                    $is_not_compliant = true;
                }
            }
            
        
        }else{
            if( $job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="" ){
                $prop_status_icon = "/images/currently_compliant.png";
            }else{
                $prop_status_icon = "/images/not_compliant.png";
                $is_not_compliant = true;
            }
        }
        
        if( $is_holiday_rent ){ //use different image (short term remtal)
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . $prop_status_icon,116.5,48,80);  ## same as below but different image size
        }else{
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . $prop_status_icon,116.5,48,50); 
        }
       
        $pdf->Ln();
        //Compliance icon here end
        
        $pdf->Ln(10);

        /* this is messing up the new certificate design, ben said hide it for now
        // compass index number
        if( $property_details['compass_index_num'] != '' ){
            
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(45,5,"Index No.");
            
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(45,5,$property_details['compass_index_num']);
            
            $pdf->Ln(10);
            
        }
        */

        //Type of Visit
        $pos_x = $pdf->GetX();
        $pos_y = $pdf->GetY();
        $pdf->SetXY($pos_x+14,$pos_y+3);  
        $pdf->Cell(30,14,$job_details['job_type']);
    
        //Date Visit
        $pos_x = $pdf->GetX();
        $pos_y = $pdf->GetY();
        $pdf->SetXY($pos_x+61.5,$pos_y+3);  
        $pdf->Cell(115,8,$job_details['date']);
        $pdf->Ln(10);


        $pdf->Ln(15);

        // if bundle, get bundle services id
        $ajt_serv_sql = $this->job_functions_model->getService($job_details['jservice']); 
        $ajt_serv = $ajt_serv_sql->row_array();

        // bundle
        if($ajt_serv['bundle']==1){ 
            $bs_sql = $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `id` IN({$ajt_serv['bundle_ids']})
            ");
        // not bundle
        }else{
            $bs_sql = $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `id` = {$job_details['jservice']}
            ");
        }

        // while($bs = mysql_fetch_array($bs_sql)){
        $has_something = 0; //flag where to display compliant comments/notes
        foreach($bs_sql->result_array() as $bs){

            // smoke alarms
            if( $bs['id'] == 2 || $bs['id'] == 12 ){
                $pdf->Ln(2);
                    //$pdf->SetDrawColor(190,190,190);
                    //$pdf->SetLineWidth(0.05);
                    //$pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

                    $pdf->Ln(6);

                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();
                //$pdf->SetXY($pos_x+14,$pos_y+1);  
                $pdf->SetFont('Arial','B',11);
                $pdf->Cell(45,5,"{$bs['type']} Summary:");
                $pdf->Ln(10);

                $ast_pos = 1;
                $hw_Position = 35;
                $hw_Power = 21;
                $hw_Type = 30;
                $hw_Make = 27;
                $hw_Model = 28;
                $hw_Expiry = 14;
                $hw_dB = 25;
                
                
                $pdf->Cell($ast_pos,5,"");
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

                $jalarms_sql = $this->db->query("
                    SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason  
                    FROM alarm a 
                        LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
                        LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
                        LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
                    WHERE a.job_id = '" . $job_id . "'
                    ORDER BY a.`ts_discarded` ASC, a.alarm_id ASC
                ");
                $temp_alarm_flag = 0;
                // while($jalarms = mysql_fetch_array($jalarms_sql)){
                foreach($jalarms_sql->result_array() as $jalarms)
                {

                    // if reason: temporary alarm
                    if( $jalarms['alarm_reason_id']==31 ){
                        $temp_alarm_flag = 1;
                    }

                    // if discarded
                    if($jalarms['ts_discarded']==1){
                        $pdf->SetTextColor(255, 0, 0);
                        $pdf->SetFont('Arial','I',$sa_font_size);
                    }

                    // if techsheet "Required for Compliance" = 0/No
                    $append_asterisk = '';
                    if( $jalarms['ts_required_compliance'] == 0 ){
                        $append_asterisk = '*';
                    }   

                    $pdf->SetTextColor(255, 0, 0); // red
                    $pdf->Cell($ast_pos,5,$append_asterisk);
                    $pdf->SetTextColor(0, 0, 0); // clear red

                    $pdf->Cell($hw_Position,5,mb_strimwidth($jalarms['ts_position'], 0, 20, '...'));
                    $pdf->Cell($hw_Power,5,$jalarms['alarm_pwr']);
                    $pdf->Cell($hw_Type,5,$jalarms['alarm_type']);
                    $pdf->Cell($hw_Make,5,$jalarms['make']);
                    $pdf->Cell($hw_Model,5,$jalarms['model']);
                    $pdf->Cell($hw_Expiry,5,$jalarms['expiry']);        
                    
                    if($jalarms['ts_discarded']==1){
                        $adr_sql = $this->db->query("
                            SELECT * 
                            FROM `alarm_discarded_reason`
                            WHERE `active` = 1
                            AND `id` = {$jalarms['ts_discarded_reason']}
                        ");
                        $adr = $adr_sql->row_array();
                        // $pdf->Cell($hw_dB,5,'Removed -');
                        // $pdf->Ln();
                        // $pdf->Cell($hw_dB,5,''.$adr['reason']);
                        $pdf->MultiCell($hw_dB, 5, "Removed -\n{$adr['reason']}");
                    }else{
                        $pdf->Cell($hw_dB,5,$jalarms['ts_db_rating']);
                    }
                    if($jalarms['ts_discarded']==1){
                        $pdf->SetFont('Arial','',$sa_font_size);
                        $pdf->SetTextColor(0, 0, 0);
                    }
                    $pdf->Ln();
                }

                $pdf->Ln(4);
                    
                $c_sql = $this->db->query("
                    SELECT *
                    FROM `jobs` AS j
                    LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
                    WHERE j.`id` = {$job_details['id']}
                "); 
                $c = $c_sql->row_array();
                switch($c['country_id']){
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
                
                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();
                //$pdf->SetXY($pos_x+14,$pos_y+3);  
                $pdf->SetFont('Arial','',10);
                if( $job_details['state'] == 'QLD' && $temp_alarm_flag==1 ){ // if QLD and temporary alarm
                    $pdf->SetTextColor(255, 0, 0);
                    $pdf->SetFont('Arial','I',10);
                    $pdf->MultiCell(185,5,'Smoke alarms at the above property are NOT compliant with AS3786 (2014) and will need to be replaced when compliant smoke alarms become available. The property has working smoke alarms and the property is safe however they are not compliant, and SATS will revisit the property to make it compliant as soon as compliant alarms become available.'); 
                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(0, 0, 0);
                }else if( $job_details['state'] == 'NSW' ){

                    if( $job_details['country_id']==1 ){ // AU
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms within the property as detailed above where a removable battery is present have had batteries replaced at this service dated '.$date_of_visit.' in accordance with Residential Tenancies Regulation 2019 [NSW]. '); 
                        $pdf->Ln(3);
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,"All smoke alarms within the property as detailed above have been cleaned and tested as per manufacturer's instructions and where new alarms have been installed they have been installed in accordance with Residential Tenancies Regulation 2019 [NSW], Australian Standard AS 3786: 2014 Smoke Alarms, Building Code of Australia, Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations"); 
                    }else if( $job_details['country_id']==2 ){ // NZ
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations (where smoke alarms are hard-wired) and Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.'); 
                    } 

                }else{          
                    
                    if( $job_details['country_id']==1 ){ // AU
                        //$pdf->MultiCell(185,5,'All smoke alarms within the property as detailed above where a removable battery is present have had batteries replaced at this service dated '.$date_of_visit.' in accordance with Residential Tenancies Regulation 2019 [NSW]. '); 
                    // $pdf->Ln(3);
                        //$pdf->MultiCell(185,5,"All smoke alarms within the property as detailed above have been cleaned and tested as per manufacturer's instructions and where new alarms have been installed they have been installed in accordance with Residential Tenancies Regulation 2019 [NSW], Australian Standard AS 3786: 2014 Smoke Alarms, Building Code of Australia, Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations");     
                        // $pdf->Cell($ast_pos,5,'');
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and been installed in accordance with '.$country_text.' Standard AS 3786 (2014) Smoke Alarms, Building Code of '.$c['country'].', Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations.'); 
                    
                    }else if( $job_details['country_id']==2 ){ // NZ
                        // $pdf->Cell($ast_pos,5,'');
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations (where smoke alarms are hard-wired) and Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.'); 
                    }  
                    
                }
                

                $pdf->Ln(3);
                //$pdf->SetFont('Arial','',8);
                
                $pdf->SetTextColor(255, 0, 0); // red
                $pdf->Cell($ast_pos,5,'');
                $pdf->Cell(2,5,'*');
                $pdf->SetTextColor(0, 0, 0); // clear red
                $pdf->MultiCell(185,5,'Not required for compliance'); 

                $pdf->Ln(3);
                //$pdf->Cell($ast_pos,5,'');
                $pdf->MultiCell(185,5,'Where alarm Power is 240v or 240vLi the alarm is mains powered. (Hard Wired). All other alarms are battery powered.');  

                if( $job_details['state'] == 'QLD' && ( is_numeric($job_details['prop_upgraded_to_ic_sa']) && $job_details['prop_upgraded_to_ic_sa'] == 0 ) ){

                    $pdf->Ln(3);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(185,5,'Disclosure: This property could be compliant if a new lease has not been entered into after 1st January 2022.');  

                    ##added by gherx >new 
                    if( $job_details['holiday_rental']==1 && $job_details['marker_id']=="" ){
                        $pdf->Ln(3);
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'As advised this property is being used as a holiday or short-term rental property Division 2 part 31 excludes these properties from the Residential Tenancies and Rooming Accommodation Act 2008.  Should the right to occupy this premises be given for 6 weeks or longer it is taken to not be given for holiday purposes and compliance would need to be reassessed prior to commencement of the agreement.');  
                    }

                }                
            
                
            // safety switch
            }else if($bs['id'] == 5){
                
                $has_something = 1; //flag where to display compliant comments/notes

                $ssp_sql = $this->db->query("
                    SELECT `ts_safety_switch`, `ts_safety_switch_reason`, `ss_quantity`
                    FROM `jobs`
                    WHERE `id` = {$job_details['id']}
                ");
                $ssp = $ssp_sql->row_array();

                $pdf->Ln(2);
                    $pdf->SetDrawColor(190,190,190);
                    $pdf->SetLineWidth(0.05);
                    $pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

                    $pdf->Ln(6);

                    $pdf->SetFont('Arial','B',11);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->Cell(45,5,"{$bs['type']} Summary:");
                    $pdf->Ln(10);
                    
                    // check if at least 1 SS failed
                    $chk_ss_sql = $this->db->query("
                        SELECT *
                        FROM `safety_switch`
                        WHERE `job_id` ={$job_details['id']}
                        AND `test` = 0
                    ");
                    
                    $num_ss_fail = $chk_ss_sql->row_array();
                    
                    //if( $num_ss_fail > 0 ){
                        
                        // Fusebox Viewed
                        /* comment out (gherx)
                        $pdf->Ln(4);
                        $pdf->SetFont('Arial','B',11);			
                        $pdf->Cell(40,5,"Fusebox Viewed:");
                        $pdf->SetFont('Arial','',10);
                        $pdf->Cell(15,5,($ssp['ts_safety_switch']==2)?'Yes':'No');
                        */
            
                // Fusebox Viewed - Yes
                if($ssp['ts_safety_switch']==2){

                    //SS TABLE START
                    //$pdf->Cell(30,5,"{$service} Headings");
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->Cell(30,5,"Make");
                    $pdf->Cell(30,5,"Model");
                    //$pdf->Cell(30,5,"Test Date");
                    $pdf->Cell(30,5,"Test Result");
                    $pdf->Ln(9);
                    $pdf->SetFont('Arial','',10);

                    //$pdf->Cell(30,5,"{$service} Data");
                    $ss_sql = $this->db->query("
                        SELECT *
                        FROM `safety_switch`
                        WHERE `job_id` ={$job_details['id']}
                        ORDER BY `make`
                    ");
                
                    // while($ss = mysql_fetch_array($ss_sql))
                    foreach($ss_sql->result_array() as $ss)
                    {
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->Cell(30,5,$ss['make']);
                        $pdf->Cell(30,5,$ss['model']);
                        //$pdf->Cell(30,5,$job_details['date']);  
                        if($ss['test']==1){ // pass
                            $pdf->Cell(30,5,'Pass');
                        }else if( is_numeric($ss['test']) && $ss['test']==0 ){ // fail
                            $pdf->SetTextColor(255, 0, 0); // red
                            $pdf->Cell(30,5,'Fail');
                            $pdf->SetTextColor(0, 0, 0); 
                        }else if($ss['test']==2){ // no power
                            $pdf->Cell(30,5,'No Power to Property at time of testing');
                        }else if($ss['test']==3){ // not tested
                            $pdf->Cell(30,5,'Not Tested');
                        }else if($ss['test']==''){
                            $pdf->Cell(30,5,'Not Tested');
                        }
                        
                        $pdf->Ln();
                    }
                    //SS TABLE START END
                
                    //new gherx added  
                    if($ssp['ss_quantity']==0){ // 0 safety switch
                        $pos_x = $pdf->GetX();
                        $pos_y = $pdf->GetY();
                        $pdf->SetXY($pos_x+14,$pos_y+3);

                        $pdf->SetTextColor(255,0,0);
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'No Safety Switches Present. We strongly recommend a Safety Switch be installed to protect the occupants.'); 
                        $pdf->Ln(4);
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'Our Technician has noted there are no safety switches installed at the premises. Therefore none were tested upon attendance.'); 
                        $pdf->SetTextColor(0,0,0);
                    }else{ // 1 or more safety switch

                        // query if at least 1 has not tested
                        $chk_ss_not_tested_sql = $this->db->query("
                            SELECT *
                            FROM `safety_switch`
                            WHERE `job_id` ={$job_details['id']}
                            AND `test` = 3
                        ");

                        // query if at least 1 has no power
                        $chk_ss_no_pwr_sql = $this->db->query("
                            SELECT *
                            FROM `safety_switch`
                            WHERE `job_id` ={$job_details['id']}
                            AND `test` = 2
                        ");
                        $num_no_power = $chk_ss_no_pwr_sql->num_rows();

                        $pdf->Ln(4);
                        $pos_x = $pdf->GetX();
                        $pos_y = $pdf->GetY();
                        $pdf->SetXY($pos_x+14,$pos_y+3);
                    
                        $pdf->MultiCell(185,5,$ss_sql->num_rows().' Safety Switches Present'); //display number of switch	

                        if( $num_no_power > 0 ){ //NO POWER		
                            $pdf->Ln(4);						
                            $pdf->MultiCell(185,5,"One or more of the safety switches at the property were unable to be tested due to no power supply to the property at the time of inspection, and power is required to perform a mechanical test on the Safety Switches.");
                        }else if( $num_ss_fail > 0 ){ // ATLEAT 1 SS TEST FAILD	
                    
                            switch ($chk_ss_sql->num_rows()) {
                                case 1:
                                    $num_string = "One";
                                    break;
                                case 2:
                                    $num_string = "Two";
                                    break;
                                case 3:
                                    $num_string = "Three";
                                    break;
                                case 4:
                                    $num_string = "Four";
                                    break;	
                                case 5:
                                    $num_string = "Five";
                                    break;	
                                case 6:
                                    $num_string = "Six";
                                    break;
                                case 7:
                                    $num_string = "Seven";
                                    break;
                                case 8:
                                    $num_string = "Eight";
                                    break;
                                case 9:
                                    $num_string = "Nine";
                                    break;
                                case 10:
                                    $num_string = "Ten";
                                    break;
                                default:
                                    $num_string = $num_ss_fail;
                            } 

                            /*$pdf->Ln(4);					
                            $pdf->MultiCell(185,5,"One or more of the Safety Switches at this property has failed. This information is for your use, and we strongly suggest you advise your client. SATS do not install Safety Switches; however we do test them when they are present.");
                            $pdf->Ln(4);*/
                            $pdf->SetTextColor(255, 0, 0); // red
                            $pdf->Cell($ast_pos,5,'');
                            $have_has = ($chk_ss_sql->num_rows()>1) ? 'have' : 'has';
                            $pdf->MultiCell(185,5,"{$num_string} of the Safety Switches at this property {$have_has} failed. This information is for your use, and we strongly suggest you advise your client. SATS do not install Safety Switches; however we do test them when they are present.");
                            $pdf->SetTextColor(0, 0, 0);
                            
                        }else if($chk_ss_not_tested_sql->num_rows()>0){ //IF ANY SS NOT TESTED
                            $pdf->Ln(4);	
                            $pdf->Cell($ast_pos,5,'');				
                            $pdf->MultiCell(185,5,"One or more of the safety switches at the property were unable to be tested at the time of attendance. Please contact SATS for further information.");
                        }else{
                            $pdf->Ln(4);	
                            $pdf->Cell($ast_pos,5,'');				
                            $pdf->MultiCell(185,5,"All Safety Switches have been Mechanically Tested and pass a basic mechanical test, to assess they are in working order. No test has been performed to determine the speed at which the device activated.");
                        }

                    }
                    //new gherx added end

                // Fusebox Viewed - No
                }else if($ssp['ts_safety_switch']==1){
                
                    // reason				
                    $pdf->SetFont('Arial','B',11);			
                    //$pdf->Cell(18,5,"Reason:");
                    $pdf->SetFont('Arial','',10);
                    switch($ssp['ts_safety_switch_reason']){
                        case 0:
                            $ssp_reason = 'No Switch';
                            $ssp_reason2 = "Our Technician has noted there are no safety switches installed at the premises. Therefore none were tested upon attendance.";
                        break;
                        case 1:
                            $ssp_reason = 'Unable to Locate';
                            $ssp_reason2 = "One or more of the safety switches were not tested due to the inability to locate them at the time of attendance.";
                        break;
                        case 2:
                            $ssp_reason = 'Unable to Access';
                            $ssp_reason2 = "One or more of the safety switches were not tested due to the inability to access at the time of attendance.";
                        break;
                    }
                // $pdf->Cell(30,5,$ssp_reason);

                    $pdf->Ln(8);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(185,5,$ssp_reason2);
                
                }
                
        // }
            
                $pdf->Ln(3);
                $pdf->SetFont('Arial','',8);
                    
                //}
                
                
            // corded windows
            }else if($bs['id'] == 6){

                $has_something = 1; //flag where to display compliant comments/notes

                $pdf->Ln(2);
                $pdf->SetDrawColor(190,190,190);
                $pdf->SetLineWidth(0.05);
                $pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

                $pdf->Ln(6);

                $pdf->SetFont('Arial','B',11);
                $pdf->Cell($ast_pos,5,'');
                $pdf->Cell(45,5,"{$bs['type']} Summary:");
                $pdf->Ln(10);

                $pdf->SetFont('Arial','',10);
                $cw_sql = $this->db->query("
                    SELECT *
                    FROM `corded_window`
                    WHERE `job_id` ={$job_id}
                ");
                // while( $cw = mysql_fetch_array($cw_sql) ){
                foreach($cw_sql->result_array() as $cw){
                    $num_windows_total += $cw['num_of_windows'];
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->Cell(30,5,$cw['location']);
                    $pdf->Cell(30,5,$cw['num_of_windows'],0,1);
                }

                $pdf->Ln(5);
                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();
                //$pdf->SetXY($pos_x+14,$pos_y+3);
                $pdf->MultiCell(185,5,'All Corded Windows within the Property as detailed above are Compliant with Current Legislation and '.$country_text.' Standards. The Required Clips and Tags have been installed to ensure proper compliance with Current Legislation. Further data is available on the agency portal'); 
                $pdf->Ln(3);
                $pdf->SetFont('Arial','',8);
                
                
            // water meter
            }else if($bs['id'] == 7){

                $has_something = 1; //flag where to display compliant comments/notes

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
                $wm_sql = $this->functions_model->getWaterMeter($job_details['id']);
                // while($wm = mysql_fetch_array($wm_sql))
                // {
                foreach($wm_sql->result_array() as $wm)
                {
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

        if(in_array($job_details['jservice'], $ic_service)){
            $ic_check = 1;
        }else{
            $ic_check = 0;
        }

        if( $ic_check == 0 && $job_details['state'] == 'QLD' && $job_details['qld_new_leg_alarm_num']>0 && $job_details['prop_upgraded_to_ic_sa'] != 1 ){

            $pdf->SetTextColor(0, 0, 204);              
            // QUOTE
            $quote_qty = $job_details['qld_new_leg_alarm_num'];
            $price_240vrf = $this->get240vRfAgencyAlarm($property_details['agency_id']);
            $quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
            $quote_total = $quote_price*$quote_qty;
            
            $pos_x = $pdf->GetX();
            $pos_y = $pdf->GetY();
            $pdf->SetXY($pos_x+14,$pos_y+3);  
            //$pdf->MultiCell(157,5,'We have provided a quote for $'.$quote_total.' to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details['date']).'+90 days')).' and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
            $pdf->MultiCell(185,5,'We have provided a quote to upgrade this property to meet the NEW QLD 2022 legislation. This quote is valid until 21/04/2022 and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
            $pdf->SetTextColor(0, 0, 0);

        }

        //Property NOT Compliant Notes > Gherx
        //query for extra_job_notes table > query as separate rather than joining in main query to git rid of possible issue becaues lots of pages used that main query
        $extra_job_notes_sql = $this->db->query("
            SELECT *
            FROM `extra_job_notes`
            WHERE `job_id` ={$job_details['id']}
        ");
        $extra_job_notes_row = $extra_job_notes_sql->row_array();
        $not_compliant_heading = "Property NOT COMPLIANT comments:";
        if( $has_something >0 ){ //Show at the top of WE if has something and WE
            if($is_not_compliant){
                if( $extra_job_notes_sql->num_rows()> 0 && $extra_job_notes_row['not_compliant_notes']!="" ){
                    $pdf->ln(5);  
                    $pdf->SetTextColor(255, 0, 0); //red
                    $pdf->SetFont('Arial','BI',10); 
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->Cell(130,5,$not_compliant_heading);
                    $pdf->ln();  
                    $pdf->SetTextColor(255, 0, 0); //red
                    $pdf->SetFont('Arial','I',10);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(130,5,$extra_job_notes_row['not_compliant_notes']);
                }
            }
        }
        //Property NOT Compliant Notes End > Gherx

        // WE PDF
        // get WE services
        $we_services = $this->system_model->we_services_id();    

        if ( in_array($job_details['jservice'], $we_services) ){ // only display if it has WE service
            
            // display WE PDF using FPDI
            $pdf->SetFont('Arial','',10);
            $pdf->SetAutoPageBreak(true,7);
            $pdf->addPage(); 
            //$pdf->set_dont_display_header(1); // hide the header
           // $pdf->set_dont_display_footer(1); // hide the footer main template
            //$pdf->is_compliance_second_page_bg(0);
            //$pdf->is_compliance_second_page_bg_for_WE(1); //show new compliant template with no footer
            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/we_cert.pdf');
            $tplidx = $pdf->importPage(1);        
            $pdf->useTemplate($tplidx, 0, 20);

            // ADDRESS
            // Stret name and num
            $pdf->setXY(27,75);
            $pdf->Cell(8,0, "{$property_details['address_1']} {$property_details['address_2']}");
            
            // suburb and state
            $pdf->setXY(27,82.5);
            $pdf->Cell(8,0, "{$property_details['address_3']} {$property_details['state']}");

            // postcode
            $pdf->setXY(157,82.5);
            $pdf->Cell(8,0, $property_details['postcode']);

            // water efficiency measures         
            $we_sql = $this->db->query("
            SELECT 
                we.`water_efficiency_id`,
                we.`device`,
                we.`pass`,
                we.`location`,
                we.`note`,

                wed.`water_efficiency_device_id`,
                wed.`name` AS wed_name
            FROM `water_efficiency` AS we
            LEFT JOIN `water_efficiency_device` AS wed ON we.`device` = wed.`water_efficiency_device_id`
            WHERE we.`job_id` = {$job_id}
            AND we.`active` = 1
            ORDER BY we.`location` ASC
            ");        

            // total count
            $shower_count = 0;
            $tap_count = 0;
            $toilet_count = 0;

            // total pass count
            $shower_pass_count = 0;
            $tap_pass_count = 0;
            $toilet_pass_count = 0;

            foreach( $we_sql->result() as $we_row ){

                // shower count
                if($we_row->device == 3){ 
                    $shower_count++;
                }

                // tap count
                if($we_row->device == 1){ 
                    $tap_count++;
                }

                // toilet
                if($we_row->device == 2){ 
                    $toilet_count++;
                }
            
                // passed shower count
                if( $we_row->device == 3 && $we_row->pass == 1 ){
                    $shower_pass_count++;
                }

                // passwed tap count
                if( $we_row->device == 1 && $we_row->pass == 1 ){
                    $tap_pass_count++;
                }

                // passed toilet count
                if( $we_row->device == 2 && $we_row->pass == 1 ){
                    $toilet_pass_count++;
                }

            }         

            // leak    
            $pass_img = null;  
            if ( $job_details['property_leaks'] == 0 && is_numeric($job_details['property_leaks']) ){
                $pass_img = 'green_check.png';
            }else if( $job_details['property_leaks'] == 1 ){
                $pass_img = 'red_cross.png';
            }  
            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,108,10);
            }             
            

            // shower           
            $pass_img = null;      
            if ( $shower_pass_count == $shower_count ){
                $pass_img = 'green_check.png';
            }else{
                $pass_img = 'red_cross.png';
            } 
            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,130,10);
            }
            

            // tap        
            $pass_img = null;      
            if ( $tap_pass_count == $tap_count ){
                $pass_img = 'green_check.png';
            }else{
                $pass_img = 'red_cross.png';
            } 
            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,150,10);
            }
            

            // toilet
            $dual_flush_due_date =  '2025/03/23';
            $pass_img = null;   
            
            if ( $toilet_pass_count == $toilet_count ){ // pass
                $pass_img = 'green_check.png';
            }else{ // fail

                if( $job_details['jdate'] >= date('Y-m-d',strtotime($dual_flush_due_date)) ){
                    $pass_img = 'red_cross.png';
                }else{
                    $pass_img = 'green_check.png';
                }
                
            }         
            

            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,175,10);
            }


            // WE summary        
            $pdf->setXY(12,220);
            $pdf->SetFont('Arial','B',11);       
            
            $left_spacing = 21;

            // set headers
            $th_border = 0;
            $we_col3 = 60;
            $we_col1 = 60;
            $we_col2 = 60;       
            //$we_col4 = 100;
            
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/for_WE_white_bg_image.png", 0, 220,300,100);
            

            $pdf->setX($left_spacing);
            $pdf->Cell($we_col3,5,"Location",$th_border);
            $pdf->Cell($we_col1,5,"Device",$th_border);
            $pdf->Cell($we_col2,5,"Result",$th_border);        
            //$pdf->Cell($we_col4,5,"Note",$th_border);
            $pdf->Ln();
        

            $pdf->SetFont('Arial','',10);
            
            foreach( $we_sql->result() as $we_row ){

                $pdf->setX($left_spacing);
                $pdf->Cell($we_col3,5,$we_row->location,$th_border);
                $pdf->Cell($we_col1,5,$we_row->wed_name,$th_border);

                if( $we_row->device == 2 ){ // toilet

                    if( $we_row->pass == 1 ){
                        $pdf->Cell($we_col2,5,'Dual Flush',$th_border);
                    }else if( $we_row->pass == 0 && is_numeric($we_row->pass) ){
                        $pdf->SetTextColor(255, 0, 0); // red
                        $pdf->Cell($we_col2,5,'*Single Flush',$th_border);
                        $pdf->SetTextColor(0, 0, 0); // clear red
                    }

                }else{ // tap or shower

                    if( $we_row->pass == 1 ){
                        $pdf->Cell($we_col2,5,'Pass',$th_border);
                    }else if( $we_row->pass == 0 && is_numeric($we_row->pass) ){
                        $pdf->SetTextColor(255, 0, 0); // red
                        $pdf->Cell($we_col2,5,'Fail',$th_border);
                        $pdf->SetTextColor(0, 0, 0); // clear red
                    }
                }
                                
                //$pdf->Cell($we_col4,5,$we_row->note,$th_border);
                $pdf->Ln();            
            }

            // leak notes
            $pdf->setX($left_spacing);
            $pdf->SetFont('Arial','I',10);
            $pdf->SetTextColor(255, 0, 0); // red
            $pdf->Cell(130,5,$job_details['leak_notes']); 
            $pdf->SetTextColor(0, 0, 0); // clear red  

            $pdf->ln(10);  
            $pdf->setX($left_spacing);

            // note
            $note_border = 0;
            $pdf->SetFont('Arial','I',10);      
            $pdf->Cell(12,5,'*Note:',$note_border);  

            // pass
            $pdf->SetFont('Arial','BI',10); 
            $pdf->Cell(12,5,'PASS',$note_border);   
            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(52,5,'= Less than 9L/minute flow rate;',$note_border);      
            
            // fail
            $pdf->SetFont('Arial','BI',10); 
            $pdf->Cell(10,5,'FAIL',$note_border);   
            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(55,5,'= greater than 9L/minute flow rate.',$note_border);  
            
            $pdf->ln();  
            $pdf->setX($left_spacing+11);

            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(130,5,'Single Flush toilets must be replaced to dual flush toilets on/after 23rd March 2025',$note_border);              

        }

        //DISPLAY NOT COMPLIANT NOTES HERE IF ONLY WE
        if( $has_something == 0 ){ 
            if($is_not_compliant){
                if( $extra_job_notes_sql->num_rows()> 0 && $extra_job_notes_row['not_compliant_notes']!="" ){
                    $pdf->ln(5);  
                    $pdf->SetTextColor(255, 0, 0); //red
                    $pdf->SetFont('Arial','BI',10); 
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->Cell(130,5,$not_compliant_heading);
                    $pdf->ln();  
                    $pdf->SetTextColor(255, 0, 0); //red
                    $pdf->SetFont('Arial','I',10);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(130,5,$extra_job_notes_row['not_compliant_notes']);
                }
            }
        }

        if ($output == "") {
            return $pdf->Output('','S');
        }else {
            return $pdf;
        }


    }

    public function vehicle_details($vehicle_id){
        $v = $this->vehicles_model->get_vehicle_details($vehicle_id);
        $output = ( $params['output'] != '' )?$params['output']:'I'; 

        // start fpdf
        $pdf = new FPDF('P', 'mm', 'A4');

        $pdf->SetTopMargin(10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        // set default values
        $header_space = 2.5;
        $header_width = 100;
        $header_height = 5;
        $header_border = 0;
        $header_new_line = 1;
        $header_align = null;
        $header_font_family = 'Arial';
        $header_font_style = 'U';
        $header_font_size = 12;

        $cell_width = 50;
        $cell_width2 = 30;
        $cell_height = 6;
        $cell_border = 0;
        $col1_cell_new_line = 0;
        $col2_cell_new_line = 1;
        $col1_cell_align = 'L';
        $col2_cell_align = 'L';
        $cell_font_family = 'Arial';
        $cell_font_style = '';
        $cell_font_size = 9;


        // sats logo
        $pdf->image($_SERVER['DOCUMENT_ROOT'] . '/images/pdf_logo.png');

        // image
        if ($v->image != '') {
            $pdf->image($_SERVER['DOCUMENT_ROOT'] . '/images/vehicle/' . $v->image, 110, 30);
        } else { // if car image not yet present
            $pdf->image($_SERVER['DOCUMENT_ROOT'] . '/images/no_car_image.jpg', 110, 30);
        }

        $pdf->Ln($header_space);

        // Vehicle
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Vehicle', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Plant ID: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->plant_id, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Make: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->make, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Model: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->model, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Year: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->year, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'VIN Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->vin_num, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Engine Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->engine_number, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);


        $current_x_pos = $pdf->GetX();
        $current_y_pos = $pdf->GetY();

        $col_1_pos_x = $current_x_pos;
        $col_1_pos_y = $current_y_pos;

        $pd_x_pos = $current_x_pos + 100;
        $pd_y_pos = $current_y_pos + 20;

        $pdf->SetXY($pd_x_pos, $pd_y_pos);

        // Purchase Details
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Purchase Details', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Purchase Date: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $purchase_date = ($v->purchase_date != "0000-00-00" && $v->purchase_date != "") ? date("d/m/Y", strtotime($v->purchase_date)) : '';
        $pdf->Cell($cell_width, $cell_height, $purchase_date, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Purchase Price: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->purchase_price, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Warranty Expires: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $warrant_expires = ($v->warranty_expires != "0000-00-00" && $v->warranty_expires != "") ? date("d/m/Y", strtotime($v->warranty_expires)) : '';
        $pdf->Cell($cell_width, $cell_height, $warrant_expires, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();

        // Driver
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Driver', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Driver Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        
        $driver = $this->vehicles_model->get_driver($data['vehicle']->StaffID);
        $driver_name = $driver->name;
        $pdf->Cell($cell_width, $cell_height, $driver_name, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();


        // Finance
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Finance', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Bank:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->finance_bank, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Loan Number:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->finance_loan_num, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Term (Months):', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->finance_loan_terms, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Monthly $:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->finance_monthly_repayments, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Start Date:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $finance_start_date = ( $v->finance_start_date != "0000-00-00" && $v->finance_start_date != "" ) ? date("d/m/Y", strtotime($v->finance_start_date)) : '';
        $pdf->Cell($cell_width, $cell_height, $finance_start_date, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'End Date:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $finance_end_date = ( $v->finance_end_date != "0000-00-00" && $v->finance_end_date != "" ) ? date("d/m/Y", strtotime($v->finance_end_date)) : '';
        $pdf->Cell($cell_width, $cell_height, $finance_end_date, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);


        $pdf->SetXY($col_1_pos_x, $col_1_pos_y);
        // Fuel
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Fuel', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Fuel Type: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->fuel_type, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Fuel Card Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->fuel_card_num, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Fuel Card Pin: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->fuel_card_pin, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        // eTag
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'eTag', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'eTag Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->etag_num, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        // Insurance
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Insurance', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Policy Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->ins_pol_num, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Insurer: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->insurer, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Policy Expires: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $policy_expiry = ( $v->policy_expires != "0000-00-00 00:00:00" && $v->policy_expires != "" ) ? date("d/m/Y", strtotime($v->policy_expires)) : '';
        $pdf->Cell($cell_width, $cell_height, $policy_expiry, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        // Registration
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Registration', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Number Plate: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->number_plate, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $rego_expiry = ($v->rego_expires != "0000-00-00 00:00:00") ? date("d/m/Y", strtotime($v->rego_expires)) : '';
        $pdf->Cell($cell_width, $cell_height, 'Rego Expires: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $rego_expiry, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Cust. Rego #:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->cust_reg_num, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Key Number:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v->key_number, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        $kms = $this->vehicles_model->get_vehicle_details_kms($vehicle_id);
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'KMS', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Kms:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $kms->kms, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Kms Updated:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $kms_updated_ts = ( $kms->kms_updated != "0000-00-00 00:00:00" && $kms->kms_updated != "" ) ? date('d/m/Y', strtotime($kms->kms_updated)) : '';
        $pdf->Cell($cell_width, $cell_height, $kms_updated_ts, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        // Tools
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Tools', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell(60, $header_height, 'Item ID:', 1, 0, $header_align);
        $pdf->Cell(60, $header_height, 'Brand:', 1, 0, $header_align);
        $pdf->Cell(60, $header_height, 'Description:', 1, 0, $header_align);

        $pdf->Ln();

        $tools_sql = $this->db->query("SELECT item_id, brand, description FROM tools WHERE assign_to_vehicle={$vehicle_id}");

        $pdf->SetFont('Arial', '', 11);
        foreach( $tools_sql->result() as $tool ){
            $pdf->Cell(60, $header_height, $tool->item_id, 1, 0, $header_align);
            $pdf->Cell(60, $header_height, $tool->brand, 1, 0, $header_align);
            $pdf->Cell(60, $header_height, $tool->description, 1, 0, $header_align);
            $pdf->Ln();
        }

        $pdf_filename = 'vehicle_details_' . date('dmYHis') . '.pdf';
        return $pdf->Output($pdf_filename, $output);
    }


    /**
     * New combined invoice pdf template
     */
    public function pdf_combined_template_v2($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, $output = "", $is_copy = false){

       
        $this->system_model->updateInvoiceDetails($job_id);

        #instantiate only if required
        if(!isset($pdf)) {
            
            $pdf=new jPDI();

            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer
            $pdf->is_new_combined_template(1); //use new template

        }

        $pdf->SetTopMargin(40);
        //$pdf->SetAutoPageBreak(true,35);
        $pdf->SetAutoPageBreak(true,63);
        $pdf->AddPage();

        //if( $job_details['show_as_paid']==1 || ( is_numeric($job_details['invoice_balance']) && $job_details['invoice_balance'] == 0 ) ){
        if( $job_details['show_as_paid']==1 || ( is_numeric($job_details['invoice_balance']) && $job_details['invoice_balance'] <= 0 && $job_details['invoice_payments'] > 0 ) ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/paid.png',90,110);
        }

        if( $is_copy == true ){
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/copy.png',160,70,30);
        }   

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $bpay_ref_code = "{$job_id}{$check_digit}";	

        //invoice num
        $pdf->SetFont('Arial','B',18);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetX(139);
        if(isset($job_details['tmh_id']))
        {
           $pdf->Cell(100,3,str_pad($job_details['tmh_id'] . ' TMH', 6, "0", STR_PAD_LEFT),0,1,'L');
        }
        else
        {
            $pdf->Cell(100,-40, $bpay_ref_code,0,1,'L');
        }
        //invoice num end

        $pdf->SetFont('Arial','',10); 
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetY(40);
        $pdf->SetX(30);

        ## --------------------NEW HEADING----------------------
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        #first row
        if( $property_details['add_inv_to_agen'] == 1 ){ 
            $landlord_txt = $property_details['agency_name'];
            $landlord_txt2 = "{$landlord_txt}";
            $landlord_title = "LANDLORD: ";
        }else if( 
            ( is_numeric($property_details['add_inv_to_agen']) && $property_details['add_inv_to_agen'] == 0 ) && 
            ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' ) 
        ){
            $landlord_txt = "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}";
            $landlord_txt2 = "{$landlord_txt}";
            $landlord_title = "LANDLORD: ";
        }else{
            $landlord_txt = "CARE OF THE OWNER";
            $landlord_txt2 = "";
            $landlord_title = "LANDLORD: ";
        }   
       
        if( $property_details['add_inv_to_agen'] == 1 ){ 
            $agency_address_txt = htmlspecialchars_decode("{$property_details['a_address_1']} {$property_details['a_address_2']}\n{$property_details['a_address_3']} {$property_details['a_state']} {$property_details['a_postcode']}");
        }else{
            $agency_address_txt = "";
        }
       
        # Hack for LJ Hooker Tamworth - display Landlord in different spot for them
        if($property_details['agency_id'] == 1348){
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(12.5,5,'ATTN: ');
            $pdf->SetFont('Arial','',11);
            $pdf->ln();
            $pdf->cell(20,5,'');
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);

            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Invoice Date: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(70,5,$job_details['date']); 
            $pdf->ln();
            $pdf->SetX(124);
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Terms: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(30,5,'NET 30 Days'); 
            $box2_h = $pdf->GetY(); 
            $pdf->Ln(6);
        }else if ($property_details['agency_id'] == 3079){  
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(12.5,5,'ATTN: ');
            $pdf->SetFont('Arial','',11);
            $pdf->ln();
            $pdf->cell(20,5,'');
            $pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);

            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Invoice Date: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(70,5,$job_details['date']); 
            $pdf->ln();
            $pdf->SetX(124);
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Terms: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(30,5,'NET 30 Days'); 
            $box2_h = $pdf->GetY(); 
        }else{  
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(12.5,5,'ATTN: ');
            $pdf->SetFont('Arial','',11);
            $pdf->ln();
            $pdf->cell(20,5,'');
            $pdf->MultiCell(90, 5, "{$landlord_txt}\n{$agency_address_txt}");
            $box1_h = $pdf->GetY();
            $pdf->SetY($curry);
            $pdf->SetX(124);
            
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Invoice Date: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(70,5,$job_details['date']); 
            $pdf->ln();
            $pdf->SetX(124);
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(26,5,'Terms: ');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(30,5,'NET 30 Days'); 
            $box2_h = $pdf->GetY(); 
        }
        #first row end

        $pdf->Ln(5);
        
        # second row
        $pdf->SetY($box2_h+30);
        $pdf->SetX(16);
        $property_address_txt = htmlspecialchars_decode("{$property_details['address_1']} {$property_details['address_2']} {$property_details['address_3']} {$property_details['state']} {$property_details['postcode']}");
        $workorder_txt = ($job_details['work_order']!='NULL')?"{$job_details['work_order']}":"";

        //Date of Visit/Subscription tweak
        $date_of_visit = ( $job_details['assigned_tech'] > 0 && $job_details['assigned_tech'] != 1 && $job_details['assigned_tech'] != 2 )?$job_details['date']:'N/A';
        
        // if agency "Agency Allows up front billing" to yes and job type is YM
        $is_upfront_billing = ( $job_details['allow_upfront_billing'] == 1 && $job_details['job_type'] == "Yearly Maintenance" )?true:false;

        $append_str = null;
        if( $is_upfront_billing == true ){

           //4644 - Ray White Whitsunday
           //4637 - Vision Real Estate Mackay
           //6782 - Vision Real Estate Dysart 
           //4318 - first national mackay
           $spec_agen_arr = array(4644,4637,6782,4318);

           if( in_array($property_details['agency_id'], $spec_agen_arr) ){

               // month format
               $sub_start_period = date("F Y",strtotime($job_details['jdate']));;
               $sub_end_period = date("F Y",strtotime($job_details['jdate']."+ 11 months"));

           }else{

               // d/m/y format
               $sub_start_period = date("1/m/Y",strtotime($job_details['jdate']));;
               $sub_end_period = date("t/m/Y",strtotime($job_details['jdate']."+ 11 months"));     

           }

           $append_str = "{$sub_start_period} - {$sub_end_period}";
           $subscription_or_datevisit_title = "SUBSCRIPTION PERIOD: ";
           
        }else{
            $append_str = "{$date_of_visit}";
            $subscription_or_datevisit_title = "DATE OF VISIT: ";
        }
       
       #cell start
        //property
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(48,2.5,'PROPERTY SERVICED: ');
        $pdf->SetFont('Arial','',11);
        //$pdf->MultiCell(200, 5,"{$property_address_txt}{$append_str}{$landlord_txt2}{$compass_index_num}{$workorder_txt}",0,'L' );
        //$pdf->MultiCell(200, 2.5,"{$property_address_txt}",0,'L' ); ##disabled and replace below for macron NZ fix

        // fix for NZ macron char issue 
       setlocale(LC_CTYPE, 'en_US');
       $incov_val = iconv('UTF-8', 'windows-1252//TRANSLIT', $property_address_txt);
       $pdf->MultiCell(200, 2.5,"{$incov_val}",0,'L' );

        $pdf->ln();

        //SUbscription or Date of Visit
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell('6',2.5,'');
        $pdf->Cell(48,2.5,$subscription_or_datevisit_title);
        $pdf->SetFont('Arial','',11);
        $pdf->MultiCell(200, 2.5,"{$append_str}",0,'L' );

        $pdf->ln();

        //landlord
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell('6',2.5,'');
        $pdf->Cell(48,2.5,$landlord_title);
        $pdf->SetFont('Arial','',11);
        $pdf->MultiCell(200, 2.5,"{$landlord_txt2}",0,'L' );

      //compass index
      if( $property_details['compass_index_num'] != '' ){
          $pdf->ln();
          $pdf->SetFont('Arial','B',11);
          $pdf->Cell('6',2.5,'');
          $pdf->Cell(48,2.5,'INDEX NO.: ');
          $pdf->SetFont('Arial','',11);
          $pdf->MultiCell(200, 2.5,"{$property_details['compass_index_num']}",0,'L' );
      
      }

      $pdf->ln();
    
      //work order
      $pdf->SetFont('Arial','B',11);
      $pdf->Cell('6',2.5,'');
      $pdf->Cell(48,2.5,'WORK ORDER: ');
      $pdf->SetFont('Arial','',11);
      $pdf->MultiCell(200, 2.5,"{$workorder_txt}",0,'L' );
       #cell end
       # second row end
       $pdf->ln(10);
              
    ## --------------------NEW HEADING END----------------------

        $currYTT = $pdf->GetY();

        $pdf->Ln();
        
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        $pdf->SetY($currYTT);

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(15,5,"Qty");
        $pdf->Cell(40,5,"Item");
        $pdf->Cell(85,5,"Description");
        $pdf->Cell(25,5,"Unit Price");
        $pdf->Cell(25,5,"Total Amount");
        $pdf->SetFont('Arial','',11); //reset bold to regular font
        $pdf->Ln();
        $pdf->Ln(1);
        $curry = $pdf->GetY();
        $currx = $pdf->GetX();
        //$pdf->SetDrawColor(255,0,0);
        $pdf->SetDrawColor(200,38,47); //sats red
        $pdf->SetLineWidth(0.4);
        $pdf->Line($currx, $curry, $currx + 190, $curry);
        $pdf->Ln(5);
        $pdf->SetDrawColor(0,0,0); //reset line color to black

        // get service
        $os_sql = $this->job_functions_model->getService($job_details['jservice']);	
        $os = $os_sql->row_array();

        ## price variation tweak
        if(  $this->system_model->check_price_increase_excluded_agency($property_details['agency_id']) ){ ## Normal Price
            $dynamicPrice = $job_details['job_price']; ##orig price use for orig calculation
            $dynamic_price_total =  $job_details['job_price']; ## user for +|- variation total
        }else{
            $tt_params = array(
                'service_type' => $job_details['jservice'],
                'property_id' => $property_details['property_id'],
                'job_id' => $job_details['id']
            );
            //$tt_price = $this->system_model->get_job_variation($tt_params);
            $tt_price = $this->system_model->get_job_variations_v2($tt_params);
            $dynamicPrice = $tt_price['total_price_including_variations'];
            $dynamic_price_total = $tt_price['dynamic_price_total_display_on']; ## user for +|- variation total
        }
        ## price variation tweak end

        # Add Job Type
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(15,5,"1", 0, 0, 'C');
        $pdf->Cell(45,5,$job_details['job_type']);
        $pdf->Cell(80,5,$os['type']);
        //$pdf->Cell(19,5,"$".number_format($job_details['job_price'], 2), 0, 0, 'R');
        //$pdf->Cell(31,5,"$".number_format($job_details['job_price'], 2), 0, 0, 'R');
        $pdf->Cell(19,5,"$".number_format($dynamic_price_total, 2), 0, 0, 'R');
        $pdf->Cell(31,5,"$".number_format($dynamic_price_total, 2), 0, 0, 'R');
        $pdf->Ln();

        //$grand_total = $job_details['job_price'];
        $grand_total = $dynamicPrice;

        ## new row for price variations
        if( $tt_price && !empty( $tt_price['display_var_arr'] ) ){
            
            foreach( $tt_price['display_var_arr'] as $tt_awa )
            {

                if( $tt_awa['type'] == 1 ){
                    $price_var_format = "(-$".$tt_awa['amount'].")";
                }else{
                    $price_var_format = "+$".$tt_awa['amount'];
                }

                $pdf->Cell(15,5,"1", 0, 0, 'C');
                $pdf->Cell(45,5,$tt_awa['item']);
                $pdf->Cell(80,5,$tt_awa['description']);
                $pdf->Cell(19,5,$price_var_format, 0, 0, 'R');
                $pdf->Cell(31,5,$price_var_format, 0, 0, 'R');
                $pdf->Ln(5);

                /*if( $tt_awa['type'] == 1 ){
                    $grand_total -= $tt_awa['amount'];
                }else{
                    $grand_total += $tt_awa['amount'];
                }*/
                
            }    
            
        }
        ## new row for price variations end

        for($x = 0; $x < $num_alarms; $x++)
        {
            if($alarm_details[$x]['new'] == 1)
            {
                
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(15,5,"1", 0, 0, 'C');
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
        $sc_sql = $this->db->query("
            SELECT *, m.`name` AS m_name 
            FROM `agency_maintenance` AS am
            LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
            WHERE am.`agency_id` = {$property_details['agency_id']}
            AND am.`maintenance_id` > 0
        ");
        $sc = $sc_sql->row_array();
        if( $grand_total!=0 && $sc['surcharge']==1 ){
            
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(15,5,"1", 0, 0, '');
            $pdf->Cell(45,5,$sc['m_name']);
            $surcharge_txt = ($sc['display_surcharge']==1)?$sc['surcharge_msg']:'';
            $pdf->Cell(80,5,$surcharge_txt);
            $pdf->Cell(19,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
            $pdf->Cell(31,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
            $pdf->Ln();
            
            $grand_total += $sc['price'];
            
        }

        // CREDITS
        $credit_sql = $this->db->query("
            SELECT *
            FROM `invoice_credits` AS ic 
            WHERE ic.`job_id` = {$job_id}
        ");

        foreach($credit_sql->result_array() as $credit){

            $item_credit_text = ($credit['credit_paid']<0) ? 'Credit - Reversal' : 'Credit' ;
            $credit_paid = ( $credit['credit_paid']<0 ) ? '$'.number_format(abs($credit['credit_paid']),2) : "$".number_format($credit['credit_paid'], 2) ;
            
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(15,5,"1", 0, 0, '');
            $pdf->Cell(45,5,'Credit');
            $pdf->SetFont('Arial','I',10);
            $pdf->SetTextColor(255, 0, 0); // red
            $pdf->Cell(80,5,'Reason: '.$this->getInvoiceCreditReason($credit['credit_reason']));
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial','',10);
            // $pdf->Cell(19,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
            // $pdf->Cell(31,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
            $pdf->Cell(19,5,'('.$credit_paid.')', 0, 0, 'R');
		    $pdf->Cell(31,5,'('.$credit_paid.')', 0, 0, 'R');

            $pdf->Ln();
            
            $grand_total -= $credit['credit_paid'];
            
        }

        $pdf->Ln(8);
        $pdf->SetFont('Arial','',10);

        // get country
        $c_sql = $this->db->query("
            SELECT *
            FROM `agency` AS a
            LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
            WHERE a.`agency_id` = {$property_details['agency_id']}
        ");
        $c = $c_sql->row_array();

        // gst
        if($c['country_id']==1){
            $gst = $grand_total / 11;
        }else if($c['country_id']==2){
            $gst = ($grand_total*3) / 23;
        }	

        // get cursor position
        $cursor_y = $pdf->GetY();

        //SUB TOTAL
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = 'Sub Total';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        $pdf->Cell(31,5,"$" . number_format($grand_total-($gst), 2), 0, 0, 'R');
        $pdf->Ln();

        //GST
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = 'GST';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        $pdf->Cell(31,5,"$" . number_format($gst, 2), 0, 0, 'R');
        $pdf->Ln();

        //Total
        $pdf->Cell(140,5,"", 0, 0, 'C');
        $text = 'Total';
        $pdf->Cell(19,5,$text, 0, 0, 'R');
        $pdf->Cell(31,5,"$" . number_format($grand_total, 2), 'B', 0, 'R');
        $pdf->Ln();

        // Payments/Credits
        $pdf->Cell(140,10,"", 0, 0, 'C');
        $text = 'Payments';
        $pdf->Cell(25,10,$text, 0, 0, 'R');
        $pdf->SetFont('Arial','B',12);
        $inv_payments = $grand_total - $job_details['invoice_balance'];
        $pdf->Cell(25,10,'($'.number_format($inv_payments, 2).')', 0, 0, 'R');
        $pdf->Ln();

        // balance
        $pdf->SetFont('Arial','I',10);
        $pdf->Cell(140,10,"", 0, 0, 'C');
        $text = 'Amount Owing';
        $pdf->Cell(25,5,$text, 0, 0, 'R');
        $pdf->SetFont('Arial','B',12);
        $inv_balance = ( is_numeric($job_details['invoice_balance']) )?$job_details['invoice_balance']:$grand_total;
        $pdf->Cell(25,5,'$'.number_format($inv_balance, 2), 0, 0, 'R');
        $pdf->Ln(15);

        // BPAY AU only
        $tt_x_for_no_bpay = $pdf->GetX();
        $tt_y_for_no_bpay = $pdf->GetY();
        if( $c['country_id']==1 && $job_details['display_bpay']==1 ){
            $pdf->Ln(1);

            // BPAY logo    
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/bpay/bpay_does_not_accept_credit_card.jpg',17,null,60);
            
            $tt_y = $pdf->GetY(); //current vertical position after QR image
            $tt_x = $pdf->GetX();

            // set font 
            $pdf->SetFont('Helvetica','',11);
            $pdf->SetTextColor(24, 49, 104); // blue
            
            $bpay_x = $pdf->GetX()+43;
            $bpay_y =  $pdf->GetY()-27.5;
            $pdf->SetXY($bpay_x,$bpay_y);   
            $biller_code = '264291';
            $pdf->Cell(15,5,$biller_code, 0, 0, 'R');
            
            // Ref Code
            $pdf->SetXY($bpay_x,$bpay_y+4.5);
            $pdf->Cell(15,5,$bpay_ref_code, 0, 0, 'R');
            
            $pdf->SetTextColor(0, 0, 0);
            
        }

        ## Bank Details
       

        if($c['country_id']!=1 || ($job_details['display_bpay']!=1 && COUNTRY==1)){
            $add_x = 15; 
            $pdf->SetXY($tt_x_for_no_bpay+5,$tt_y_for_no_bpay);  
        }else{
            $add_x = 80; 
            $pdf->SetXY($tt_x+$add_x,$tt_y-33);  
        }

        $pdf->SetFont('Arial','',10);

        $c_bank = $c['bank'];   
        $c_ac_name = $c['ac_name'];
        $c_ac_number = $c['ac_number'];
        
        if($c['country_id']!=2){
            $c_bsb = $c['bsb']; 
            $pdf->SetFont('Arial','B',10);
            $pdf->cell(55,5,"Direct Deposit Details:",0,1,'L','','');
            $pdf->SetFont('Arial','',10); //reset
            $pdf->SetX($tt_x+$add_x);
            $pdf->MultiCell(100,5,"Name: {$c_ac_name}",0,'L');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"Bank: {$c_bank}",0,1,'L','','');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"BSB: {$c_bsb}",0,1,'L','','');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"Account #: {$c_ac_number}",0,1,'L','','');

        }else{
            
        //$pdf->MultiCell(55,5,"Direct Deposit Details:
            $pdf->SetFont('Arial','B',10);
            $pdf->cell(55,5,"Direct Deposit Details:",0,1,'L','','');
            $pdf->SetFont('Arial','',10); //reset
            $pdf->SetX($tt_x+$add_x);
            $pdf->MultiCell(100,5,"Name: {$c_ac_name}",0,'L');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"Bank: {$c_bank}",0,1,'L','','');
            $pdf->SetX($tt_x+$add_x);
            $pdf->cell(55,5,"Account #: {$c_ac_number}",0,1,'L','','');
        } 
          


        // Reference No.
        $pdf->SetX($tt_x+$add_x);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(22,5,'Reference #: ');
        $pdf->SetTextColor(255, 0, 0); // red
        $pdf->Cell(11,5,$bpay_ref_code,0,1);
        $pdf->SetTextColor(0, 0, 0); // clear red
        $pdf->SetFont('Arial','',10);

        $pdf->Cell(41,5,'');  //dummy
        ## Bank Details End

        /*
        ## Bank Details-------
        $x_pos = 16;
        $pdf->SetXY($x_pos,(($cursor_y)+40));  
        $pdf->SetFont('Arial','',10);

        $c_bank = $c['bank'];   
        $c_ac_name = $c['ac_name'];
        $c_ac_number = $c['ac_number'];
        
        if($c['country_id']!=2){;

            $c_bsb = $c['bsb']; 
            $pdf->SetFont('Arial','B',10);
            $pdf->cell(55,5,"Direct Deposit Details:",0,1,'L','','');
            $pdf->SetFont('Arial','',10); //reset
            $pdf->cMargin = 7;
            $pdf->MultiCell(100,5,"Name: {$c_ac_name}",0,'L');
            $pdf->cell(55,5,"Bank: {$c_bank}",0,1,'L','','');
            $pdf->cell(55,5,"BSB: {$c_bsb}",0,1,'L','','');
            $pdf->cell(55,5,"Account #: {$c_ac_number}",0,1,'L','','');

        }else{
            
            $pdf->SetFont('Arial','B',10);
            $pdf->cell(55,5,"Direct Deposit Details:",0,1,'L','','');
            $pdf->SetFont('Arial','',10); //reset
            $pdf->cMargin = 7;
            $pdf->MultiCell(100,5,"Name: {$c_ac_name}",0,'L');
            $pdf->cell(55,5,"Bank: {$c_bank}",0,1,'L','','');
            $pdf->cell(55,5,"Account #: {$c_ac_number}",0,1,'L','','');

        } 

        // Reference No.
        $pdf->cMargin = 1;
        $pdf->SetX($x_pos);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(22,5,'Reference #: ');
        $pdf->SetTextColor(255, 0, 0); // red
        $pdf->Cell(11,5,$bpay_ref_code,0,1);
        $pdf->SetTextColor(0, 0, 0); // clear red
        $pdf->SetFont('Arial','',10);

        $pdf->Cell(41,5,'');  //dummy
        ## Bank Details End-------

        $x_pos = $pdf->getX();
        $pdf->SetXY($x_pos+50,$cursor_y+40.5);
        $pdf->SetFont('Arial','',10);

        ## BPAY AU only
        if( $c['country_id']==1 && $job_details['display_bpay']==1 ){
            
            // BPAY logo    
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/bpay/bpay_does_not_accept_credit_card.jpg',null,null,60);
            
            // set font 
            $pdf->SetFont('Helvetica','',11);
            $pdf->SetTextColor(24, 49, 104); // blue
            
            // Biller Code
            $bpay_x = $x_pos+86;
            $bpay_y = $cursor_y+45.5;
            $pdf->SetXY($bpay_x,$bpay_y);   
            $biller_code = '264291';
            $pdf->Cell(15,5,$biller_code, 0, 0, 'R');
            
            // Ref Code
            $pdf->SetXY($bpay_x,$bpay_y+5);
            $pdf->Cell(15,5,$bpay_ref_code, 0, 0, 'R');
            
            $pdf->SetTextColor(0, 0, 0);
            
        }
        ## BPAY AU only end

        */


        ## STATEMENT OF COMPLIANCE SECTION ##
        $pdf->show_compliance_template(1);
        $pdf->AddPage();
        
        $pdf->Ln(7);

        ##statement heading (Property address, Porperty Status, Type Visit, Inspection Date)
        #property address
        $pos_x = $pdf->GetX();
        $pos_y = $pdf->GetY();
        //$pdf->SetXY($pos_x+14,$pos_y);   
        $pdf->SetXY($pos_x+14,$pos_y);   
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(30,-9,'PROPERTY');
        
        $pdf->SetFont('Arial','',11);
        $pdf->Ln(1);
        $pdf->Cell(14,4,"");
        $prop_add_1_2 = iconv('UTF-8', 'windows-1252//TRANSLIT', "{$property_details['address_1']} {$property_details['address_2']}" );
        //$pdf->Cell(30,4,$property_details['address_1'] . " " . $property_details['address_2']);
        $pdf->Cell(30,4,$prop_add_1_2);
        $pdf->Ln();
        $pdf->Cell(14,5,"");
        $prop_add_3_4 = iconv('UTF-8', 'windows-1252//TRANSLIT', "{$property_details['address_3']} {$property_details['state']}, {$property_details['postcode']}" );
        //$pdf->Cell(30,6,$property_details['address_3'] . " " . $property_details['state'] . ", " .$property_details['postcode'] );
        $pdf->Cell(30,6,$prop_add_3_4 );

        #compliance status icons
        $is_not_compliant = false;
        if( $property_details['state']=="QLD" ){

           /* if( ( $job_details['prop_upgraded_to_ic_sa']==0 &&  $job_details['prop_upgraded_to_ic_sa']!="") && ($job_details['prop_comp_with_state_leg']==0 && $job_details['prop_comp_with_state_leg']!="") ){
                $prop_status_icon = "/images/combine_notComp_and_not2022.png";
                $is_not_compliant = true;
            }elseif( ($job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="") && ($job_details['prop_upgraded_to_ic_sa']==0 && $job_details['prop_upgraded_to_ic_sa']!="") ){
                $prop_status_icon = "/images/combine_currentlyComp_and_not2022.png";
                $is_not_compliant = true;
            }else{
                if( $job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="" ){
                    $prop_status_icon = "/images/currently_compliant.png";
                }else{
                    $prop_status_icon = "/images/not_compliant.png";
                    $is_not_compliant = true;
                }
            }*/

            if( ( $job_details['prop_upgraded_to_ic_sa']==0 &&  $job_details['prop_upgraded_to_ic_sa']!="") && ($job_details['prop_comp_with_state_leg']==0 && $job_details['prop_comp_with_state_leg']!="") ){
               
                //if(  ($job_details['marker_id']!="" && $job_details['marker_id']==1) && $job_details['holiday_rental']==1 ){ //hide NOT 2022 Compliant
                if( $job_details['marker_id']=="" && $job_details['holiday_rental']==1 ){ 
                    $prop_status_icon = "/images/not_compliant.png";
                }else{
                    $prop_status_icon = "/images/combine_notComp_and_not2022.png";
                }

                $is_not_compliant = true;

            }elseif( ($job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="") && ($job_details['prop_upgraded_to_ic_sa']==0 && $job_details['prop_upgraded_to_ic_sa']!="") ){
               
                //if( ($job_details['marker_id']!="" && $job_details['marker_id']==1) && $job_details['holiday_rental']==1 ){ //hide NOT 2022 Compliant
                if( $job_details['marker_id']=="" && $job_details['holiday_rental']==1 ){ 
                    $prop_status_icon = "/images/currently_compliant.png";
                }else{
                    if( $job_details['holiday_rental']==1 && $job_details['state'] == 'QLD' ){
                        $prop_status_icon = "/images/not_compliant_shortTemRental.png";
                        $is_holiday_rent = true;
                    }else{
                        $prop_status_icon = "/images/combine_currentlyComp_and_not2022.png";
                    }
                }
                $is_not_compliant = true;

            }else{

                if( $job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="" ){
                    $prop_status_icon = "/images/currently_compliant.png";
                }else{
                    $prop_status_icon = "/images/not_compliant.png";
                    $is_not_compliant = true;
                }
                
            }
        
        }else{
            if( $job_details['prop_comp_with_state_leg']==1 || $job_details['prop_comp_with_state_leg']=="" ){
                $prop_status_icon = "/images/currently_compliant.png";
            }else{
                $prop_status_icon = "/images/not_compliant.png";
                $is_not_compliant = true;
            }
        }
        $pdf->SetFont('Arial','B',11);
        $pos_x = $pdf->GetX();
        $pos_y = $pdf->GetY();
        $pdf->SetXY($pos_x+61.5,$pos_y-9.5);  
        $pdf->cell(115,0,'PROPERTY STATUS');
        $pdf->Ln(1);

        $pdf->SetFont('Arial','',11);

        if( $is_holiday_rent ){ //use different image (short term remtal)
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . $prop_status_icon,116.5,48,80);  ## same as below but different image size
        }else{
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . $prop_status_icon,116.5,48,50); 
        }

        $pdf->Ln(10);

        #Type of Visit
        $pos_x = $pdf->GetX();
        $pos_y = $pdf->GetY();
        $pdf->SetXY($pos_x+14,$pos_y+4);  
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(30,25,'TYPE OF VISIT');
        $pdf->Ln(7);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(14,5,"");
        $pdf->Cell(30,25,$job_details['job_type']);

        #Date Visit
        $pos_x = $pdf->GetX();
        $pos_y = $pdf->GetY();
        $pdf->SetXY($pos_x+61.5,$pos_y);  
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(30,11,'INSPECTION DATE');
        $pdf->Ln(7);
        $pdf->SetFont('Arial','',11);
        $pdf->SetXY($pos_x+61.5,$pos_y);  
        $pdf->Cell(115,25,$job_details['date']);
        $pdf->Ln(10);
        ##statement heading (Property address, Porperty Status, Type Visit, Inspection Date) end

        #HR
        $pos_x = $pdf->GetX();
        $pos_y = $pdf->GetY();
        $pdf->SetXY($pos_x,$pos_y+17); 
        $pdf->SetDrawColor(190,190,190);
        $pdf->SetLineWidth(0.05);
        $pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

        # State of Compliance
        $pdf->SetFont('Arial','B',14);

        $pdf->Ln(15);
              
        $appliance_details = $this->getPropertyAlarms($job_id, 1, 0, 1);
        $num_appliances = sizeof($appliance_details);
        if($num_appliances > 0)
        {
            $pdf->SetFont('Arial','B',11);

            $pdf->Cell(45,5,"Appliance Summary:");
            $pdf->Ln(10);


            $pdf->Cell(8, 5, "#");
            $pdf->Cell(23, 5, "Type");
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
                $pdf->Cell(23, 2, $appliance_details[$x]['alarm_type']);
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
            $pdf->Cell(15, 5, $job_details['retest_date']);

            $pdf->Ln(15);

            $pdf->SetFont('Arial','',9);
            $pdf->MultiCell(185,5,'All Appliances located within the property as detailed above are compliant with current legislation and Australian Standards. Appliances and leads are tested as per Manufacturers recommendations & the NSW Test and Tag requirements.');
            $pdf->Ln(10);
        }

        // if bundle, get bundle services id
        $ajt_serv_sql = $this->job_functions_model->getService($job_details['jservice']);
        $ajt_serv = $ajt_serv_sql->row_array();

        // bundle
        if($ajt_serv['bundle']==1){ 
            $bs_sql =  $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `id` IN({$ajt_serv['bundle_ids']})
            ");
        // not bundle
        }else{
            $bs_sql =  $this->db->query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `id` = {$job_details['jservice']}
            ");
        }

        // loop
        // while($bs = mysql_fetch_array($bs_sql)){
        $has_something = 0; //flag where to display compliant comments/notes
        foreach($bs_sql->result_array() as $bs){

            // smoke alarms
            if( $bs['id'] == 2 || $bs['id'] == 12 ){
               

                $pdf->SetFont('Arial','B',11);

                $pdf->Cell(45,5,"{$bs['type']} Summary:");
                $pdf->Ln(10);

                $ast_pos = 1;
                $hw_Position = 35;
                $hw_Power = 21;
                $hw_Type = 30;
                $hw_Make = 27;
                $hw_Model = 28;
                $hw_Expiry = 14;
                $hw_dB = 25;
                
                $pdf->Cell($ast_pos,5,"");
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

                
                $jalarms_sql = $this->db->query("
                    SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason  
                    FROM alarm a 
                        LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
                        LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
                        LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
                    WHERE a.job_id = '" . $job_id . "'
                    ORDER BY a.`ts_discarded` ASC, a.alarm_id ASC
                ");	
                $temp_alarm_flag = 0;		
                foreach($jalarms_sql->result_array() as $jalarms)
                {
                    // if reason: temporary alarm
                    if( $jalarms['alarm_reason_id']==31 ){
                        $temp_alarm_flag = 1;
                    }

                    // red italic - start
                    if($jalarms['ts_discarded']==1){
                        $pdf->SetTextColor(255, 0, 0);
                        $pdf->SetFont('Arial','I',$sa_font_size);
                    }

                    // if techsheet "Required for Compliance" = 0/No
                    $append_asterisk = '';
                    if( $jalarms['ts_required_compliance'] == 0 ){
                        $append_asterisk = '*';
                    }

                    $pdf->SetTextColor(255, 0, 0); // red
                    $pdf->Cell($ast_pos,5,$append_asterisk);
                    $pdf->SetTextColor(0, 0, 0); // clear red

                    $pdf->Cell($hw_Position,5,mb_strimwidth($jalarms['ts_position'], 0, 20, '...'));
                    $pdf->Cell($hw_Power,5,$jalarms['alarm_pwr']);
                    $pdf->Cell($hw_Type,5,$jalarms['alarm_type']);
                    $pdf->Cell($hw_Make,5,$jalarms['make']);
                    $pdf->Cell($hw_Model,5,$jalarms['model']);
                    $pdf->Cell($hw_Expiry,5,$jalarms['expiry']);
                    
                    if($jalarms['ts_discarded']==1){
                        $adr_sql = $this->db->query("
                            SELECT * 
                            FROM `alarm_discarded_reason`
                            WHERE `active` = 1
                            AND `id` = {$jalarms['ts_discarded_reason']}
                        ");
                        $adr = $adr_sql->row_array();
                        $pdf->Cell($hw_dB,5,'Removed - '.$adr['reason']);
                    }else{
                        $pdf->Cell($hw_dB,5,$jalarms['ts_db_rating']);
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

                switch($c['country_id']){
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
                
                
                if( $job_details['state'] == 'QLD' && $temp_alarm_flag==1 ){ // if QLD and temporary alarm
                    $pdf->SetTextColor(255, 0, 0);
                    $pdf->SetFont('Arial','I',10);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(185,5,'Smoke alarms at the above property are NOT compliant with AS3786 (2014) and will need to be replaced when compliant smoke alarms become available. The property has working smoke alarms and the property is safe however they are not compliant, and SATS will revisit the property to make it compliant as soon as compliant alarms become available.'); 
                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(0, 0, 0);
                }else if( $job_details['state'] == 'NSW' ){

                    if( $job_details['country_id']==1 ){ // AU
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms within the property as detailed above where a removable battery is present have had batteries replaced at this service dated '.$date_of_visit.' in accordance with Residential Tenancies Regulation 2019 [NSW]. '); 
                        $pdf->Ln(3);
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,"All smoke alarms within the property as detailed above have been cleaned and tested as per manufacturer's instructions and where new alarms have been installed they have been installed in accordance with Residential Tenancies Regulation 2019 [NSW], Australian Standard AS 3786: 2014 Smoke Alarms, Building Code of Australia, Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations"); 
                    }else if( $job_details['country_id']==2 ){ // NZ
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations (where smoke alarms are hard-wired) and Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.'); 
                    } 

                }else{
                    
                    if( $job_details['country_id']==1 ){ // AU
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and been installed in accordance with '.$country_text.' Standard AS 3786 (2014) Smoke Alarms, Building Code of '.$c['country'].', Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations.'); 
                    }else if( $job_details['country_id']==2 ){ // NZ
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations (where smoke alarms are hard-wired) and Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.'); 
                    }  						
                    
                }
                
                $pdf->Ln(3);
                
                $pdf->SetTextColor(255, 0, 0); // red
                $pdf->Cell(3,5,'*');
                $pdf->SetTextColor(0, 0, 0); // clear red
                $pdf->MultiCell(185,5,'Not required for compliance');  

                $pdf->Ln(3);
                $pdf->MultiCell(185,5,'Where alarm Power is 240v or 240vLi the alarm is mains powered. (Hard Wired). All other alarms are battery powered.');  
                
                if( $job_details['state'] == 'QLD' && ( is_numeric($job_details['prop_upgraded_to_ic_sa']) && $job_details['prop_upgraded_to_ic_sa'] == 0 ) ){

                    $pdf->Ln(3);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(185,5,'Disclosure: This property could be compliant if a new lease has not been entered into after 1st January 2022.');  

                    ##added by gherx >new 
                    if( $job_details['holiday_rental']==1 && $job_details['marker_id']=="" ){
                        $pdf->Ln(3);
                        $pdf->Cell($ast_pos,5,'');
                        $pdf->MultiCell(185,5,'As advised this property is being used as a holiday or short-term rental property Division 2 part 31 excludes these properties from the Residential Tenancies and Rooming Accommodation Act 2008.  Should the right to occupy this premises be given for 6 weeks or longer it is taken to not be given for holiday purposes and compliance would need to be reassessed prior to commencement of the agreement.');  
                    }

                }   

            // safety switch
            }else if($bs['id'] == 5){
                
                $has_something = 1; //flag where to display compliant comments/notes

                $ssp_sql = $this->db->query("
                    SELECT `ts_safety_switch`, `ts_safety_switch_reason`, `ss_quantity`
                    FROM `jobs`
                    WHERE `id` = {$job_details['id']}
                ");
                $ssp = $ssp_sql->row_array(); 

                $pdf->Ln(2);
                $pdf->SetDrawColor(190,190,190);
                $pdf->SetLineWidth(0.05);
                $pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

                $pdf->Ln(6);

                $pdf->SetFont('Arial','B',11);

                $pdf->Cell(45,5,"{$bs['type']} Summary:");
                $pdf->Ln(10);
                
                // check if at least 1 SS failed
                $chk_ss_sql = $this->db->query("
                    SELECT *
                    FROM `safety_switch`
                    WHERE `job_id` ={$job_details['id']}
                    AND `test` = 0
                ");
                
                $num_ss_fail = $chk_ss_sql->row_array();
                
                //if( $num_ss_fail > 0 ){
                    
                    // Fusebox Viewed
                    /* comment out (gherx)
                    $pdf->Ln(4);
                    $pdf->SetFont('Arial','B',11);			
                    $pdf->Cell(40,5,"Fusebox Viewed:");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(15,5,($ssp['ts_safety_switch']==2)?'Yes':'No');
                    */
                
                // Fusebox Viewed - Yes
                        if($ssp['ts_safety_switch']==2){

                            //SS TABLE START
                             //$pdf->Cell(30,5,"{$service} Headings");
                            $pdf->Cell(30,5,"Make");
                            $pdf->Cell(30,5,"Model");
                            //$pdf->Cell(30,5,"Test Date");
                            $pdf->Cell(30,5,"Test Result");
                            $pdf->Ln(9);
                            $pdf->SetFont('Arial','',10);

                            //$pdf->Cell(30,5,"{$service} Data");
                            $ss_sql = $this->db->query("
                                SELECT *
                                FROM `safety_switch`
                                WHERE `job_id` ={$job_details['id']}
                                ORDER BY `make`
                            ");
                        
                            // while($ss = mysql_fetch_array($ss_sql))
                            foreach($ss_sql->result_array() as $ss)
                            {
                                
                                $pdf->Cell(30,5,$ss['make']);
                                $pdf->Cell(30,5,$ss['model']);
                                //$pdf->Cell(30,5,$job_details['date']);  
                                if($ss['test']==1){ // pass
                                    $pdf->Cell(30,5,'Pass');
                                }else if( is_numeric($ss['test']) && $ss['test']==0 ){ // fail
                                    $pdf->SetTextColor(255, 0, 0); // red
                                    $pdf->Cell(30,5,'Fail');
                                    $pdf->SetTextColor(0, 0, 0); 
                                }else if($ss['test']==2){ // no power
                                    $pdf->Cell(30,5,'No Power to Property at time of testing');
                                }else if($ss['test']==3){ // not tested
                                    $pdf->Cell(30,5,'Not Tested');
                                }else if($ss['test']==''){
                                    $pdf->Cell(30,5,'Not Tested');
                                }
                                
                                $pdf->Ln();
                            }
                            //SS TABLE START END
                        
                            //new gherx added  
                            if($ssp['ss_quantity']==0){ // 0 safety switch
                                $pdf->SetTextColor(255,0,0);
                                $pdf->Cell($ast_pos,5,'');
                                $pdf->MultiCell(185,5,'No Safety Switches Present. We strongly recommend a Safety Switch be installed to protect the occupants.'); 
                                $pdf->Ln(4);
                                $pdf->Cell($ast_pos,5,'');
                                $pdf->MultiCell(185,5,'Our Technician has noted there are no safety switches installed at the premises. Therefore none were tested upon attendance.'); 
                                $pdf->SetTextColor(0,0,0);
                            }else{ // 1 or more safety switch

                                // query if at least 1 has not tested
                                $chk_ss_not_tested_sql = $this->db->query("
                                    SELECT *
                                    FROM `safety_switch`
                                    WHERE `job_id` ={$job_details['id']}
                                    AND `test` = 3
                                ");

                                // query if at least 1 has no power
                                $chk_ss_no_pwr_sql = $this->db->query("
                                    SELECT *
                                    FROM `safety_switch`
                                    WHERE `job_id` ={$job_details['id']}
                                    AND `test` = 2
                                ");
                                $num_no_power = $chk_ss_no_pwr_sql->num_rows();

                                $pdf->Ln(4);
                                $pdf->MultiCell(185,5,$ss_sql->num_rows().' Safety Switches Present'); //display number of switch	

                                if( $num_no_power > 0 ){ //NO POWER		
                                    $pdf->Ln(4);						
                                    $pdf->MultiCell(185,5,"One or more of the safety switches at the property were unable to be tested due to no power supply to the property at the time of inspection, and power is required to perform a mechanical test on the Safety Switches.");
                                }else if( $num_ss_fail > 0 ){ // ATLEAT 1 SS TEST FAILD	
                            
                                    switch ($chk_ss_sql->num_rows()) {
                                        case 1:
                                            $num_string = "One";
                                            break;
                                        case 2:
                                            $num_string = "Two";
                                            break;
                                        case 3:
                                            $num_string = "Three";
                                            break;
                                        case 4:
                                            $num_string = "Four";
                                            break;	
                                        case 5:
                                            $num_string = "Five";
                                            break;	
                                        case 6:
                                            $num_string = "Six";
                                            break;
                                        case 7:
                                            $num_string = "Seven";
                                            break;
                                        case 8:
                                            $num_string = "Eight";
                                            break;
                                        case 9:
                                            $num_string = "Nine";
                                            break;
                                        case 10:
                                            $num_string = "Ten";
                                            break;
                                        default:
                                            $num_string = $num_ss_fail;
                                    } 
        
                                    /*$pdf->Ln(4);					
                                    $pdf->MultiCell(185,5,"One or more of the Safety Switches at this property has failed. This information is for your use, and we strongly suggest you advise your client. SATS do not install Safety Switches; however we do test them when they are present.");
                                    $pdf->Ln(4);*/
                                    $pdf->SetTextColor(255, 0, 0); // red
                                    $have_has = ($chk_ss_sql->num_rows()>1) ? 'have' : 'has';
                                    $pdf->MultiCell(185,5,"{$num_string} of the Safety Switches at this property {$have_has} failed. This information is for your use, and we strongly suggest you advise your client. SATS do not install Safety Switches; however we do test them when they are present.");
                                    $pdf->SetTextColor(0, 0, 0);
                                    
                                }else if($chk_ss_not_tested_sql->num_rows()>0){ //IF ANY SS NOT TESTED
                                    $pdf->Ln(4);					
                                    $pdf->MultiCell(185,5,"One or more of the safety switches at the property were unable to be tested at the time of attendance. Please contact SATS for further information.");
                                }else{
                                    $pdf->Ln(4);					
                                    $pdf->MultiCell(185,5,"All Safety Switches have been Mechanically Tested and pass a basic mechanical test, to assess they are in working order. No test has been performed to determine the speed at which the device activated.");
                                }

                            }
                            //new gherx added end

                        // Fusebox Viewed - No
                        }else if($ssp['ts_safety_switch']==1){
                        
                            // reason				
                            $pdf->SetFont('Arial','B',11);			
                            //$pdf->Cell(18,5,"Reason:");
                            $pdf->SetFont('Arial','',10);
                            switch($ssp['ts_safety_switch_reason']){
                                case 0:
                                    $ssp_reason = 'No Switch';
                                    $ssp_reason2 = "Our Technician has noted there are no safety switches installed at the premises. Therefore none were tested upon attendance.";
                                break;
                                case 1:
                                    $ssp_reason = 'Unable to Locate';
                                    $ssp_reason2 = "One or more of the safety switches were not tested due to the inability to locate them at the time of attendance.";
                                break;
                                case 2:
                                    $ssp_reason = 'Unable to Access';
                                    $ssp_reason2 = "One or more of the safety switches were not tested due to the inability to access at the time of attendance.";
                                break;
                            }
                           // $pdf->Cell(30,5,$ssp_reason);

                            $pdf->Ln(8);
                            $pdf->MultiCell(185,5,$ssp_reason2);
                        
                        }
                        
                   // }
                    
                    $pdf->Ln(3);
                    $pdf->SetFont('Arial','',8);
                
                //}
                    
                
            // corded windows
            }else if($bs['id'] == 6){

                $has_something = 1; //flag where to display compliant comments/notes
                
                $pdf->SetFont('Arial','B',11);

                $pdf->Cell(45,5,"{$bs['type']} Summary:");
                $pdf->Ln(10);

                $num_windows_total = 0;
                $cw_sql = $this->db->query("
                    SELECT *
                    FROM `corded_window`
                    WHERE `job_id` ={$job_id}
                ");
                // while( $cw = mysql_fetch_array($cw_sql) ){
                foreach($cw_sql->result_array() as $cw){
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
                
                $has_something = 1; //flag where to display compliant comments/notes

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
                $wm_sql = $this->functions_model->getWaterMeter($job_details['id']);
                // while($wm = mysql_fetch_array($wm_sql))
                foreach($wm_sql->result_array() as $wm)
                {
                    
                    $pdf->Cell(30,5,$wm['reading']);
                    $pdf->Cell(30,5,$wm['location']);
                    $pdf->Ln();
                }

                $pdf->Ln(4);
                    
                $pdf->SetFont('Arial','',10);
            
                
            }

        }


        $pdf->Ln(2);
        $pdf->SetFont('Arial','',10);	


        // if service type is IC dont show, only show for non-IC services
        $ic_service = $this->system_model->getICService();

        if(in_array($job_details['jservice'], $ic_service)){
            $ic_check = 1;
        }else{
            $ic_check = 0;
        }

        if( $ic_check == 0 && $job_details['state'] == 'QLD' && $job_details['qld_new_leg_alarm_num']>0 && $job_details['prop_upgraded_to_ic_sa'] != 1 ){

            $pdf->SetTextColor(0, 0, 204);              
            // QUOTE
            $quote_qty = $job_details['qld_new_leg_alarm_num'];
            $price_240vrf = $this->get240vRfAgencyAlarm($property_details['agency_id']);
            $quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
            $quote_total = $quote_price*$quote_qty;
            //$pdf->MultiCell(185,5,'We have provided a quote for $'.$quote_total.' to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details['date']).'+90 days')).' and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
            $pdf->MultiCell(185,5,'We have provided a quote to upgrade this property to meet the NEW QLD 2022 legislation. This quote is valid until 21/04/2022 and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
            $pdf->SetTextColor(0, 0, 0);
            

        }	

        //Property NOT Compliant Notes > Gherx
        //query for extra_job_notes table > query as separate rather than joining in main query to git rid of possible issue becaues lots of pages used that main query
        $extra_job_notes_sql = $this->db->query("
            SELECT *
            FROM `extra_job_notes`
            WHERE `job_id` ={$job_details['id']}
        ");
        $extra_job_notes_row = $extra_job_notes_sql->row_array();
        $not_compliant_heading = "Property NOT COMPLIANT comments:";
        if( $has_something >0 ){ //Show at the top of WE if has something and WE
            if($is_not_compliant){
                if( $extra_job_notes_sql->num_rows()> 0 && $extra_job_notes_row['not_compliant_notes']!="" ){
                    $pdf->ln(5);  
                    $pdf->SetTextColor(255, 0, 0); //red
                    $pdf->SetFont('Arial','BI',10); 
                    //$pdf->Cell($ast_pos,5,'');
                    $pdf->Cell(125,5,$not_compliant_heading);
                    $pdf->ln();  
                    $pdf->SetTextColor(255, 0, 0); //red
                    $pdf->SetFont('Arial','I',10);
                    //$pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(125,5,$extra_job_notes_row['not_compliant_notes']);
                }
            }
        }
        //Property NOT Compliant Notes End > Gherx

        // WE PDF
        // get WE services
        $we_services = $this->system_model->we_services_id();    

        if ( in_array($job_details['jservice'], $we_services) ){ // only display if it has WE service
            
            // display WE PDF using FPDI
            $pdf->SetFont('Arial','',10);
            $pdf->SetAutoPageBreak(true,7);
            $pdf->addPage(); 
            $pdf->set_dont_display_footer(1); // hide the footer
            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/we_cert.pdf');
            $tplidx = $pdf->importPage(1);        
            $pdf->useTemplate($tplidx, 0, 20);

            // ADDRESS
            // Stret name and num
            $pdf->setXY(27,75);
            $pdf->Cell(8,0, "{$property_details['address_1']} {$property_details['address_2']}");
            
            // suburb and state
            $pdf->setXY(27,82.5);
            $pdf->Cell(8,0, "{$property_details['address_3']} {$property_details['state']}");

            // postcode
            $pdf->setXY(157,82.5);
            $pdf->Cell(8,0, $property_details['postcode']);

            // water efficiency measures         
            $we_sql = $this->db->query("
            SELECT 
                we.`water_efficiency_id`,
                we.`device`,
                we.`pass`,
                we.`location`,
                we.`note`,

                wed.`water_efficiency_device_id`,
                wed.`name` AS wed_name
            FROM `water_efficiency` AS we
            LEFT JOIN `water_efficiency_device` AS wed ON we.`device` = wed.`water_efficiency_device_id`
            WHERE we.`job_id` = {$job_id}
            AND we.`active` = 1
            ORDER BY we.`location` ASC
            ");        

            // total count
            $shower_count = 0;
            $tap_count = 0;
            $toilet_count = 0;

            // total pass count
            $shower_pass_count = 0;
            $tap_pass_count = 0;
            $toilet_pass_count = 0;

            foreach( $we_sql->result() as $we_row ){

                // shower count
                if($we_row->device == 3){ 
                    $shower_count++;
                }

                // tap count
                if($we_row->device == 1){ 
                    $tap_count++;
                }

                // toilet
                if($we_row->device == 2){ 
                    $toilet_count++;
                }
            
                // passed shower count
                if( $we_row->device == 3 && $we_row->pass == 1 ){
                    $shower_pass_count++;
                }

                // passwed tap count
                if( $we_row->device == 1 && $we_row->pass == 1 ){
                    $tap_pass_count++;
                }

                // passed toilet count
                if( $we_row->device == 2 && $we_row->pass == 1 ){
                    $toilet_pass_count++;
                }

            }         

            // leak    
            $pass_img = null;  
            if ( $job_details['property_leaks'] == 0 && is_numeric($job_details['property_leaks']) ){
                $pass_img = 'green_check.png';
            }else if( $job_details['property_leaks'] == 1 ){
                $pass_img = 'red_cross.png';
            }  
            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,108,10);
            }             
            

            // shower           
            $pass_img = null;      
            if ( $shower_pass_count == $shower_count ){
                $pass_img = 'green_check.png';
            }else{
                $pass_img = 'red_cross.png';
            } 
            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,130,10);
            }
            

            // tap        
            $pass_img = null;      
            if ( $tap_pass_count == $tap_count ){
                $pass_img = 'green_check.png';
            }else{
                $pass_img = 'red_cross.png';
            } 
            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,150,10);
            }
            

            // toilet
            $dual_flush_due_date =  '2025/03/23';
            $pass_img = null;   
            
            if ( $toilet_pass_count == $toilet_count ){ // pass
                $pass_img = 'green_check.png';
            }else{ // fail

                if( $job_details['jdate'] >= date('Y-m-d',strtotime($dual_flush_due_date)) ){
                    $pass_img = 'red_cross.png';
                }else{
                    $pass_img = 'green_check.png';
                }
                
            }         
            

            if( $pass_img != '' ){
                $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/{$pass_img}",175.5,175,10);
            }


            // WE summary        
            $pdf->setXY(12,220);
            $pdf->SetFont('Arial','B',11);       
            
            $left_spacing = 21;

            // set headers
            $th_border = 0;
            $we_col3 = 60;
            $we_col1 = 60;
            $we_col2 = 60;       
            //$we_col4 = 100;

            //white image hack to cover footer if WE
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . "/images/for_WE_white_bg_image.png", 0, 220,300,100);
            
            $pdf->setX($left_spacing);
            $pdf->Cell($we_col3,5,"Location",$th_border);
            $pdf->Cell($we_col1,5,"Device",$th_border);
            $pdf->Cell($we_col2,5,"Result",$th_border);        
            //$pdf->Cell($we_col4,5,"Note",$th_border);
            $pdf->Ln();
        

            $pdf->SetFont('Arial','',10);
            
            foreach( $we_sql->result() as $we_row ){

                $pdf->setX($left_spacing);
                $pdf->Cell($we_col3,5,$we_row->location,$th_border);
                $pdf->Cell($we_col1,5,$we_row->wed_name,$th_border);

                if( $we_row->device == 2 ){ // toilet

                    if( $we_row->pass == 1 ){
                        $pdf->Cell($we_col2,5,'Dual Flush',$th_border);
                    }else if( $we_row->pass == 0 && is_numeric($we_row->pass) ){
                        $pdf->SetTextColor(255, 0, 0); // red
                        $pdf->Cell($we_col2,5,'*Single Flush',$th_border);
                        $pdf->SetTextColor(0, 0, 0); // clear red
                    }

                }else{ // tap or shower

                    if( $we_row->pass == 1 ){
                        $pdf->Cell($we_col2,5,'Pass',$th_border);
                    }else if( $we_row->pass == 0 && is_numeric($we_row->pass) ){
                        $pdf->SetTextColor(255, 0, 0); // red
                        $pdf->Cell($we_col2,5,'Fail',$th_border);
                        $pdf->SetTextColor(0, 0, 0); // clear red
                    }
                }
                                
                //$pdf->Cell($we_col4,5,$we_row->note,$th_border);
                $pdf->Ln();            
            }

            // leak notes
            $pdf->setX($left_spacing);
            $pdf->SetFont('Arial','I',10);
            $pdf->SetTextColor(255, 0, 0); // red
            $pdf->Cell(130,5,$job_details['leak_notes']); 
            $pdf->SetTextColor(0, 0, 0); // clear red  

            $pdf->ln(10);  
            $pdf->setX($left_spacing);

            // note
            $note_border = 0;
            $pdf->SetFont('Arial','I',10);      
            $pdf->Cell(12,5,'*Note:',$note_border);  

            // pass
            $pdf->SetFont('Arial','BI',10); 
            $pdf->Cell(12,5,'PASS',$note_border);   
            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(52,5,'= Less than 9L/minute flow rate;',$note_border);      
            
            // fail
            $pdf->SetFont('Arial','BI',10); 
            $pdf->Cell(10,5,'FAIL',$note_border);   
            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(55,5,'= greater than 9L/minute flow rate.',$note_border);  
            
            $pdf->ln();  
            $pdf->setX($left_spacing+11);

            $pdf->SetFont('Arial','I',10);
            $pdf->Cell(130,5,'Single Flush toilets must be replaced to dual flush toilets on/after 23rd March 2025',$note_border);              

        }

        //DISPLAY NOT COMPLIANT NOTES HERE IF ONLY WE
        if( $has_something == 0 ){ 
            if($is_not_compliant){
                if( $extra_job_notes_sql->num_rows()> 0 && $extra_job_notes_row['not_compliant_notes']!="" ){
                    $pdf->ln(5);  
                    $pdf->SetTextColor(255, 0, 0); //red
                    $pdf->SetFont('Arial','BI',10); 
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->Cell(130,5,$not_compliant_heading);
                    $pdf->ln();  
                    $pdf->SetTextColor(255, 0, 0); //red
                    $pdf->SetFont('Arial','I',10);
                    $pdf->Cell($ast_pos,5,'');
                    $pdf->MultiCell(130,5,$extra_job_notes_row['not_compliant_notes']);
                }
            }
        }

        $pdf->Cell(10,5,'');   

        ## STATEMENT OF COMPLIANCE SECTION END ##
        
        if ($output == "") {
            return $pdf->Output('','S');
        }else {
            return $pdf;
        }


   }

    public function pdf_quote_template_v2($job_id, $job_details, $property_details, $alarm_details, $num_alarms, $country_id, $output = "", $is_copy = false,$qt){

            $today = date('Y-m-d');
            $nov_date = date('2022-11-18');

            if( $job_id ){

                $pdf = new jPDI();

                $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
                $invoice_number = "{$job_id}{$check_digit}";

                // pdf settings
                $pdf->set_dont_display_header(1); // hide the header
                $pdf->set_dont_display_footer(1); // hide the footer

                if( $qt=="emerald" ){
                    $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/Emerald-quote-blank_31082022.pdf');
                }else if( $qt=="brooks" ){
                    $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/brooks-quote-blank_31082022.pdf');
                }

                $tplidx = $pdf->importPage(1, '/MediaBox');   
                $size = $pdf->getTemplateSize($tplidx);   
                $pdf->SetAutoPageBreak(false);

                $pdf->AddPage();    
                $pdf->useTemplate($tplidx, 0, 0, 210);  

                $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
                $bpay_ref_code = "{$job_id}{$check_digit}"; 
                $qoute_number = ( $job_details['tmh_id'] != '' )?str_pad($job_details['tmh_id'] . ' TMH-Q', 6, "0", STR_PAD_LEFT):$bpay_ref_code.'Q';

                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();
    
                $pdf->SetXY($pos_x+123,$pos_y+15); 

                 // QUOTE number
                $pdf->SetFont('Arial','B',20);
                $pdf->SetTextColor(255, 255, 255); // white 
                $pdf->Cell(23,5,$qoute_number,0,1); 
                $pdf->SetTextColor(0, 0, 0); // put back to black 

                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();

                // Quote Date       
                $pdf->SetFont('Arial',null,11);       
                $pdf->SetXY($pos_x+32,$pos_y+23);    
                $pdf->Cell(23,5,$job_details['date']);  

                ##fix for NZ macron char in address issue 
                setlocale(LC_CTYPE, 'en_US');

                //Property Address
                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();
                $pdf->SetXY($pos_x+52,$pos_y+6);
                $prop_address = "{$property_details['address_1']} {$property_details['address_2']}, {$property_details['address_3']}\n{$property_details['state']} {$property_details['postcode']}";
                $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $prop_address);
                $pdf->MultiCell(80,5,$incov_val1,0);

                // ATTN
                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();          
                $pdf->SetXY($pos_x+22,$pos_y-4.5);    
                $pdf->SetFont('Arial','B',11);
                $pdf->Cell(50,5,( ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' )?"{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}":'CARE OF THE OWNER' )); 
                $pdf->SetFont('Arial',null,11);
                $pdf->Ln(1);  
                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();
                $pdf->SetXY($pos_x+9,$pos_y);  
                $pdf->MultiCell(80, 5,"\nC/- {$property_details['agency_name']}" . "\n" . trim($property_details['a_address_1']). " " . trim($property_details['a_address_2']) . "\n" . trim($property_details['a_address_3']) . " " . $property_details['a_state'] . " " . $property_details['a_postcode'] . "\n\n\n");
               


                // LANDLORD
                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();

                $pdf->SetXY($pos_x+107,$pos_y-20);  
                $pdf->SetFont('Arial','B',11);                
                $pdf->Cell(23,5,( ( $property_details['landlord_firstname'] != '' || $property_details['landlord_lastname'] != '' )?'LANDLORD:':null )); 
                $pdf->SetFont('Arial',null,11);

                $pdf->SetXY($pos_x+131,$pos_y-20);      
                $landlord =  "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}"; 
                $pdf->Cell(23,5,( ( $property_details['landlord_firstname'] != '' || $property_details['landlord_lastname'] != '' )?$landlord:null )); 
                // LANDLORD END

                $pdf->Ln(50);   
                $cell_width = 50;
                $cell_height = 5;            
                $cell_border = 0;
                $new_line = 1;
                $align = 'C';

                $pos_x = $pdf->GetX();            
                $pos_y = $pdf->GetY();    
                $col_position_x = $pos_x+75;
                $col_position_y = $pos_y+18;
                $pdf->SetXY($col_position_x,$col_position_y);  
                
                if( $qt=="emerald" ){

                    $quote_qty = $job_details['qld_new_leg_alarm_num'];
                    $price_240vrf_emerald_price = $this->get_emerald_AgencyAlarm($property_details['agency_id']);
                    $price_240vrf_emerald_price_final = ( $price_240vrf_emerald_price > 0 )?$price_240vrf_emerald_price:$this->config->item('default_qld_upgrade_quote_price');
                    $quote_total_cavius = $price_240vrf_emerald_price_final*$quote_qty;

                    $pdf->Cell($cell_width,$cell_height,"{$quote_qty} x Quality Interconnected",$cell_border,$new_line,$align);          

                    $pos_x = $pdf->GetX();
                    $pdf->SetX($col_position_x);

                    $pdf->Cell($cell_width,$cell_height,"Photo Electric Smoke Alarms",$cell_border,$new_line,$align);

                    $pdf->SetX($col_position_x); 

                    $pdf->SetFont('Arial','B',10);
                    $qoute_amount_txt = "\$".number_format($price_240vrf_emerald_price_final,2)." EA = \$".number_format($quote_total_cavius,2);
                    $pdf->Cell(40,$cell_height,"@ ".$qoute_amount_txt,$cell_border,$new_line,'L');
                    $pdf->SetFont('Arial',null,10);

                    $pdf->SetX($col_position_x); 
                    $pdf->Cell($cell_width,$cell_height,'Inc. GST',$cell_border,$new_line,$align);  

                }else if( $qt=="brooks" ){

                    $quote_qty = $job_details['qld_new_leg_alarm_num'];
                    $price_240vrf_brooks_price = $this->get240vRfAgencyAlarm($property_details['agency_id']);
                    $price_240vrf_brooks_price_final = ( $price_240vrf_brooks_price > 0 )?$price_240vrf_brooks_price:$this->config->item('default_qld_upgrade_quote_price');
                    $quote_total_brooks = $price_240vrf_brooks_price_final*$quote_qty;  
                    
                    $pdf->Cell($cell_width,$cell_height,"{$quote_qty} x Brooks Interconnected",$cell_border,$new_line,$align);
                    
                    $pos_x = $pdf->GetX();
                    $pdf->SetX($col_position_x);

                    $pdf->Cell($cell_width,$cell_height,"Photo Electric Smoke Alarms",$cell_border,$new_line,$align); 

                    $pdf->SetX($col_position_x);         

                    $pdf->SetFont('Arial','B',10);
                    $qoute_amount_txt = "\$".number_format($price_240vrf_brooks_price_final,2)." EA = \$".number_format($quote_total_brooks,2);
                    $pdf->Cell($cell_width,$cell_height,"@ ".$qoute_amount_txt,$cell_border,$new_line,$align);
                    $pdf->SetFont('Arial',null,10);

                    $pdf->SetX($col_position_x);         
                    $pdf->Cell($cell_width,$cell_height,'Inc. GST',$cell_border,$new_line,$align);

                }

                $pos_x = $pdf->GetX();
                $pos_y = $pdf->GetY();
                $pdf->SetXY($pos_x+30,$pos_y+121); 
                
                $pdf->SetFont('Arial','B',13);
                $pdf->SetTextColor(255, 255, 255); // white 
                $pdf->Cell($cell_width,$cell_height,date( 'd/m/Y', strtotime( "+6 months", strtotime($nov_date) ) ),$cell_border,$new_line,$align);   
                $pdf->SetTextColor(0, 0, 0); // put back to black   

                if ($output == "") {
                    return $pdf->Output('','S');
                }else {
                    return $pdf;
                }
               
            }

    }

    public function en_nz_pdf($params){

        $job_id = $params['job_id'];
        $output = ( $params['output'] != '' )?$params['output']:'I';  
        $country_id = $this->config->item('country');  

        // get country data
        $country_params = array(
        'sel_query' => '
            c.`country_id`,
            c.`agent_number`, 
            c.`outgoing_email`, 
            c.`tenant_number`
        ',
        'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();

        // append checkdigit to job id for new invoice number
        $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
        $invoice_number = "{$job_id}{$check_digit}";

        if( $job_id ){

            $pdf = new jPDI();

            $pdf->set_dont_display_header(1); // hide the header
            $pdf->set_dont_display_footer(1); // hide the footer

            //$pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/NZENTemplate.pdf');
            $pdf->setSourceFile($_SERVER['DOCUMENT_ROOT'].'/inc/pdf_templates/NZENTRYNOTICEOLDTEMPLATEBLANK.pdf');

            $tplidx = $pdf->importPage(1, '/MediaBox');   
            $size = $pdf->getTemplateSize($tplidx);
            $pdf->AddPage('P', array(210, $size['h']+0));        
            $pdf->useTemplate($tplidx, 0, 0, 210);  

            $pdf->SetFont('Arial','',11); 
        
            // get job data
            $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`at_myob`,
            j.`sms_sent_merge`,
            j.`client_emailed`,
            j.`time_of_day`,
            j.`en_date_issued`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            
            a.`agency_id`,
            a.`agency_name`,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`send_emails`,
            a.`account_emails`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type,

            sa.`StaffID`,
            sa.`FirstName` AS tech_fname,
            sa.`LastName` AS tech_lname
            ";    
                
            $job_params = array(
                'sel_query' => $sel_query,
                
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id, 
                'job_id' => $job_id,
                            
                'join_table' => array('job_type','alarm_job_type','staff_accounts')                     
            );
            $job_sql = $this->jobs_model->get_jobs($job_params);
            $job_row = $job_sql->row();

            $property_id = $job_row->property_id;

            // Tenant details
            $pdf->setY(50); 

            if( $property_id > 0 ){              

                // get tenants 
                $sel_query = "
                    pt.`property_tenant_id`,
                    pt.`tenant_firstname`,
                    pt.`tenant_lastname`,
                    pt.`tenant_mobile`
                ";
                $params = array(
                    'sel_query' => $sel_query,
                    'property_id' => $property_id,
                    'pt_active' => 1,  
                    'offset' => 0,
                    'limit' => 2,                                   
                    'display_query' => 0
                );
                $pt_sql = $this->properties_model->get_property_tenants($params);
                $pt_num_row = $pt_sql->num_rows();

                foreach($pt_sql->result() as $pt_row){

                    // Tenant        
                    $tenants_names_arr[] = ucwords(strtolower($pt_row->tenant_firstname));                                     
                    //$pdf->Cell(0,5, ucwords(strtolower($pt_row->tenant_firstname)).' '.ucwords(strtolower($pt_row->tenant_lastname)),0,1);                 

                }               

            }

            ##fix for NZ macron char in address issue 
            setlocale(LC_CTYPE, 'en_US');
            $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_1." ".$job_row->p_address_2);
            $incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_row->p_address_3." ".$job_row->p_state." ".$job_row->p_postcode);
            
            $pdf->Cell(0,5, "{$tenants_names_arr[0]}",0,1);
            $pdf->Cell(0,5, "{$incov_val1}",0,1);
            $pdf->Cell(180,5, "{$incov_val2}",0,1);

            // Greeting Line
            $pdf->Ln(16);

            if( count( $tenants_names_arr ) > 1 ){
		
                // Tenant 
                $tenant_str_imp = implode(", ",$tenants_names_arr); // separate tenant names with a comma
                $last_comma_pos = strrpos($tenant_str_imp,","); // find the last comma(,) position
                $tenant_str = substr_replace($tenant_str_imp,' &',$last_comma_pos,1); // replace comma with ampersand(&)
                $pdf->Cell(0,0, "Dear ".$tenant_str.",");
        
                
            }else{
                
                $pdf->Cell(0,0, "Dear ".$tenants_names_arr[0].",");
                
            }

            $tech_initial = substr($job_row->tech_lname, 0, 1).'.';
            $tech_name = "{$job_row->tech_fname} {$tech_initial}";

            // Email Body

            // Immediate Access Required
            $pdf->Ln(13);
            //$pdf->SetFont('', 'BU', 13);
            $pdf->SetFont('', 'B', 11);
            $pdf->Cell(0,0, "RE: NOTICE OF ENTRY AT {$incov_val1} {$incov_val2}");
            $pdf->SetFont('Arial','',11); 
            
            $pdf->Ln(10);
            $pdf->MultiCell(0,5, "This notice is to advise you that SATS will enter the premises at the date and time listed below to inspect/service/install smoke alarms as per The Residential Tenancies (Smoke Alarms and Insulation) Regulation 2016.");
            $pdf->Ln(10);

            $pdf->SetFont('Arial', 'IU', 11);
            $pdf->Cell(20,0, ( ( $job_row->j_date != '' )?date('d/m/Y',strtotime($job_row->j_date)):null ) );
            $pdf->SetFont('Arial','I',11); 
            $pdf->Cell(27,0, " at/or between: ");
            $pdf->SetFont('Arial', 'IU', 11);
            $pdf->Cell(20,0, $job_row->time_of_day );
            
            //$pdf->Ln(5);
            //$pdf->Line(10,10,2,2);
            //$pdf->Cell(20,0, "(date)");
            $pdf->Ln(10);

            $pdf->SetFont('Arial','',11); 
            $pdf->MultiCell(0,5,"As per the request of {$job_row->agency_name}, this notice is issued by Smoke Alarm Testing Services, who have been authorised to act as a Secondary Agent on behalf of the Landlord and your agency.");
            $pdf->Ln(5.4);
        
        
            $pdf->MultiCell(0,5, "Our technician will collect keys from {$job_row->agency_name}, the morning of the inspection, therefore there is no need for you to be home when we attend the property. Our technicians are company employees who wear photo identification, drive company branded vehicles and have been extensively trained in customer service. ");
            $pdf->Ln(5.4);
            $pdf->MultiCell(0,5, "Unless we hear from you to the contrary, we will presume we have your full permission to enter the dwelling at the time indicated above.");

            $pdf->Ln(5.4);
            $pdf->MultiCell(0,5, "Thank you for your cooperation in this matter, if you wish to discuss further, please call SATS on {$country_row->tenant_number}. ");
            
            // Yours Faithfully
            $pdf->Ln(10);
            $pdf->Cell(0,0, "Yours Faithfully,");

            // Signature (manually placed with padding ln())
            $pdf->Image($_SERVER['DOCUMENT_ROOT'].'/images/entry_notice/DK_signature.png', 10, $pdf->GetY()-5, 70); // Manually position image on PDF
            

            // SATS, number and agent name
            $pdf->Ln(33);
            $pdf->Cell(0,0, "{$this->config->item('COMPANY_FULL_NAME')} ({$country_row->tenant_number})");

            $pdf->Ln(7);
            $pdf->Cell(157,0, "Technician Attending: {$tech_name}"); 

    
            $pdf_name = "entry_notice_{$invoice_number}".date('Ymdhis').rand().".pdf";
    
            return $pdf->Output($pdf_name, $output);

        }       

    }



}
