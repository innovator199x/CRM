<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tbi_mod extends CI_Controller {

  	private $datafields;
  	private $lastIdInserted;

	public function __construct(){
		parent::__construct(); 
        $this->load->database();
        $this->load->model('jobs_model');
        $this->load->model('system_model');
        $this->load->model('functions_model');
        $this->load->helper('url');
        $this->load->library('customlib');
		$this->load->model('inc/email_functions_model');
    }   

	public function ajax_create_job(){	
		$property_id = $this->input->post('property_id');
		$alarm_job_type_id = $this->input->post('alarm_job_type_id');
		$job_type = $this->input->post('job_type');
		$price = $this->input->post('price');
		$vacant_from = $this->input->post('vacant_from');
		$vacant_from2 = ($vacant_from!="")?$vacant_from:'';

		$new_ten_start = $this->input->post('new_ten_start');
		$new_ten_start2 = ($new_ten_start!="")?$new_ten_start:'';
		$problem = $this->input->post('problem');
		$agency_id = $this->input->post('agency_id');
		$comments = "";

		$end_date_str = 'NULL';
		$start_date_str = 'NULL';

		switch($job_type){
			case 'Once-off':
				$status = "Send Letters";
				$comments = "{$job_type}";
			break;
			case 'Change of Tenancy':
				$status = "To Be Booked";
				
				if( $vacant_from!="" ){
					$start_date = date('Y-m-d',strtotime(str_replace('/','-',$vacant_from)));
					$start_date_str = "'{$start_date}'";
				}else{
					$start_date_str = 'NULL';
				}

				if( $new_ten_start !="" ){
					$end_date = date('Y-m-d',strtotime(str_replace('/','-',$new_ten_start )));
					$end_date_str = "'{$end_date}'";
				}else{
					$end_date_str = 'NULL';
				}

				$no_dates_provided = 0;

				if( $vacant_from=="" && $new_ten_start =="" ){
					$no_dates_provided = 1;
					$comments_temp = 'No Dates Provided';
				}else if( $vacant_from!="" && $new_ten_start =="" ){
					$no_dates_provided = 1;
					$comments_temp = "Vacant from {$vacant_from} - {$problem}";
				}else if( $vacant_from=="" && $new_ten_start !="" ){
					$no_dates_provided = 1;
					$comments_temp = "Book before {$new_ten_start} - {$problem}";			
				}else{
					$no_dates_provided = 0;
					$comments_temp = "Vacant from {$vacant_from} - {$new_ten_start } {$problem}";
				}
				
				$comments = "COT {$comments_temp}"; 
				
				
				/*
				$temp = "Vacant From ".$vacant_from2." and New Tenancy Starts ".$new_ten_start2;
				$comments = "{$job_type} {$temp}";
				*/
				
			break;
			case 'Yearly Maintenance':
				$this->db->select('id');
				$this->db->from('jobs');
				$this->db->where('property_id', $property_id);
				$this->db->where('del_job', 0);
				$j_sql = $this->db->get();
				$status = ($j_sql->num_rows() > 0)?"To Be Booked":"Send Letters";
			break;
			case 'Fix or Replace':
				$status = "To Be Booked";
				if( $new_ten_start2 != '' ){
					$temp = " New Tenancy Starts ".$new_ten_start2.",";
				}else{
					$temp = ',';
				}		
				$comments = "{$job_type}{$temp} Comments: <strong>{$problem}</strong>";
			break;
			case '240v Rebook':
				$status = "To Be Booked";
				$comments = "{$job_type}";
			break;
			case 'Lease Renewal':
				$status = "To Be Booked";
				
				if( $new_ten_start!="" ){
					$end_date = date('Y-m-d',strtotime(str_replace('/','-',$new_ten_start)));
					$end_date_str = "'{$end_date}'";
					$start_date = date('Y-m-d',strtotime("{$end_date} -30 days"));
					$start_date_str = "'{$start_date}'";
					$start_date_txt = date('d/m/Y',strtotime("{$end_date} -30 days"));
				}else{
					$end_date_str = 'NULL';
					$start_date_str = 'NULL';
				}
				
				$no_dates_provided = 0;
				
				if( $new_ten_start=="" ){
					$no_dates_provided = 1;
					$comments_temp = 'No Dates Provided';
				}else{
					$no_dates_provided = 0;
					$comments_temp = "{$start_date_txt} - {$new_ten_start} {$problem}";
				}
				
				$comments = "LR {$comments_temp}"; 
				
				/*
				$temp = "New Tenancy Starts ".$new_ten_start2;
				$comments = "{$job_type} {$temp}";
				*/
			break;
			case 'Annual Visit':
				$status = "To Be Booked";
				$comments = "{$job_type}";
			break;
		}

		//echo "Job Type: ".$job_type."<br />";

		echo $price2 = ($job_type=="Yearly Maintenance"||$job_type=="Once-off")?$price:0;

		// if job type is 'Fix or Replace' set it as urgent
		if( $job_type == 'Fix or Replace' ){
			$urg_field = " `urgent_job`, `urgent_job_reason`, ";
			$urg_val = " 1, 'URGENT REPAIR', ";

		}

		$vacant_prop = $this->input->post('vacant_prop'); 

		// get Franchise Group
		// $this->db->select('franchise_groups_id');
		// $this->db->from('agency');
		// $this->db->where('agency_idagency_id', $agency_id);
		// $agen_sql = $this->db->get();

		$agen_sql = "
			SELECT `franchise_groups_id`
			FROM `agency`
			WHERE `agency_id` = {$agency_id}
		";
	    $agen_exec = $this->db->query($agen_sql);
		$agen = $agen_exec->result_array();

		// if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program
		if( $this->functions_model->isDHAagenciesV2($agen[0]['franchise_groups_id'])==true || $this->functions_model->agencyHasMaintenanceProgram($agency_id)==true ){
			$dha_need_processing = 1;
		}

		$sql = "INSERT INTO 
			jobs (
				`job_type`, 
				`property_id`, 
				`status`,
				`service`,
				{$urg_field}
				`job_price`,
				`comments`,
				`start_date`, 
				`due_date`, 
				`no_dates_provided`,
				`property_vacant`,
				`dha_need_processing`
			) 
			VALUES (
				'{$job_type}', 
				'{$property_id}', 
				'{$status}',
				'{$alarm_job_type_id}',
				 {$urg_val}
				'{$price2}',
				'{$comments}',
				{$start_date_str}, 
				{$end_date_str}, 
				'{$no_dates_provided}',
				'{$vacant_prop}',
				'{$dha_need_processing}'
			)";

		$this->db->query($sql);

		// job id
		$job_id = $this->db->insert_id();
		$staffID = $this->session->staff_id;

		// AUTO - UPDATE INVOICE DETAILS
		$this->system_model->updateInvoiceDetails($job_id);

		//$service_name = $_POST['service_name'];	
				
		// insert job logs
		
		$sql2 = "INSERT INTO 
			`job_log` (
				`contact_type`,
				`eventdate`,
				`eventtime`,
				`comments`,
				`job_id`,
				`staff_id`
			) 
			VALUES (
				'<strong>{$job_type}</strong> Job Created',
				'" . date('Y-m-d') . "',
				'" . date('H:i') . "',
				'{$comments}', 
				'{$job_id}',
				'{$staffID}'
			)
		";

		$this->db->query($sql2);

			
		//$sql;

		// get alarm job type
		$this->db->select('*');
		$this->db->from('alarm_job_type');
		$this->db->where('id', $alarm_job_type_id);
		$ajt_sql = $this->db->get();

		$ajt = $ajt_sql->result_array();

		// if bundle
		if($ajt[0]['bundle']==1){
			$b_ids = explode(",",trim($ajt[0]['bundle_ids']));
			// insert bundles
			foreach($b_ids as $val){
				$sql3 = "INSERT INTO
					`bundle_services`(
						`job_id`,
						`alarm_job_type_id`
					)
					VALUES(
						{$job_id},
						{$val}
					)
				";

				$this->db->query($sql3);	
				
				$bundle_id = $this->db->insert_id();
				$bs_id = $bundle_id;
				$params = array("job_id" => $job_id, "bs_id"  => $bs_id);
				$bs2 = $this->jobs_model->getbundle_services($params);
				$ajt_id = $bs2[0]['alarm_job_type_id'];
				
				//echo "Job ID: {$job_id} - ajt ID: {$alarm_job_type_id} Bundle ID: {$bundle_id} <br />";
				
				// sync alarm
				$syncParams = array("job_id" => $job_id, "ajt_id" => $ajt_id, "bundle_id" => $bundle_id);
				$this->jobs_model->runSync($syncParams);

			}	
		}else{
			$syncParams = array("job_id" => $job_id, "ajt_id" => $alarm_job_type_id);
			$this->jobs_model->runSync($syncParams);
		}

		// expired 240v check
		if( $job_type == 'Fix or Replace' && $this->system_model->findExpired240vAlarm($job_id) == true ){
            $updateData = array(
                'job_type' => '240v Rebook'
            );

            $this->db->where('id', $job_id);
            $this->db->update('jobs', $updateData);
		}

		if( ( $job_type == 'Change of Tenancy' ||  $job_type == 'Lease Renewal' ) && $this->system_model->findExpired240vAlarm($job_id) == true ){
			$updateData = array(
                'comments' => '240v REBOOK - {$comments}'
            );

            $this->db->where('id', $job_id);
            $this->db->update('jobs', $updateData);
		}

		$data = array(
			'property_id' => $property_id,
			'alarm_job_type_id' => $alarm_job_type_id
		);
		$this->db->insert('property_propertytype', $data);

		// add logs
		//$service_name = $_POST['service_name'];
		$staff_id = $this->input->post('staff_id');
		$data = array(
			'property_id' => $property_id,
			'staff_id' => $staff_id,
			'event_type' => $ajt[0]['type'] . "Job Created",
			'event_details' => $job_type,
			'log_date' => date('Y-m-d H:i:s')
		);
		$this->db->insert('property_event_log', $data);

		// clear tenant details
		$delete_tenant = $this->input->post('delete_tenant');
		if($delete_tenant==1){
	        $updateData = array(
	            'active' => 0
	        );
	        $this->db->where('property_id', $property_id);
	        $this->db->update('property_tenants', $updateData);
		}

	}

	public function ajax_do_invoice(){
		$job_id_arr = $this->input->post('job_id');
		$country_id = $this->config->item('country');

		foreach( $job_id_arr as $job_id ){
			$i = 0;

			$today = date('Y-m-d');
			$todaydt = date('Y-m-d H:i:s');
			$logged_user = $this->session->staff_id;
			$pme_billable = false;
			$palace_billable = false;

			// clear email array
			unset($jemail);
			$jemail = array();
			$indv_job_log_arr = [];

			// get job details
			$jobs_sql = $this->db->query("
			SELECT 
				`date`,
				`job_type`,
				`status`,
				`booked_with`,
				`booked_by`,
				`assigned_tech`,
				`property_id`
			FROM `jobs`
			WHERE `id` = {$job_id}
			");
			$jobs_row = $jobs_sql->row();

			// Job Type
			$job_type_to = 'Yearly Maintenance';
			if( $jobs_row->job_type != '' ){

				if ( $jobs_row->job_type != $job_type_to ) {
					$indv_job_log_arr[] = "Job Type updated from <strong>{$jobs_row->job_type}</strong> to <strong>{$job_type_to}</strong>";                            
				}

			}else{ 

				$indv_job_log_arr[] = "Job Type updated to <strong>{$job_type_to}</strong>";     

			}
            

			// Job Status
			$job_status_to = 'Merged Certificates';
			if( $jobs_row->status != '' ){

				if ( $jobs_row->status != $job_status_to ) {
					$indv_job_log_arr[] = "Job Status updated from <strong>{$jobs_row->status}</strong> to <strong>{$job_status_to}</strong>";                            
				}

			}else{

				$indv_job_log_arr[] = "Job Status updated to <strong>{$job_status_to}</strong>";      

			}
            

			// Booked With
			$booked_with_to = 'Agent';
			if( $jobs_row->booked_with != '' ){

				if ( $jobs_row->booked_with != $booked_with_to ) {
					$indv_job_log_arr[] = "Booked With updated from <strong>{$jobs_row->booked_with}</strong> to <strong>{$booked_with_to}</strong>";                            
				}

			}else{

				$indv_job_log_arr[] = "Booked With updated to <strong>{$booked_with_to}</strong>";    

			}            

			// Booked By
			$booked_by_to = $logged_user;

			// staff_accounts TO
			$staff_acc_sql = $this->db->query("
			SELECT 
				`FirstName`,
				`LastName`
			FROM `staff_accounts`
			WHERE `StaffID` = {$booked_by_to}
			");
			$staff_acc_row = $staff_acc_sql->row();
			$booked_by_to_user_full = $this->system_model->formatStaffName($staff_acc_row->FirstName, $staff_acc_row->LastName);

			if( $jobs_row->booked_by != '' ){

				if ( $jobs_row->booked_by != $booked_by_to ) {

					// staff_accounts FROM
					$staff_acc_sql2 = $this->db->query("
					SELECT 
						`FirstName`,
						`LastName`
					FROM `staff_accounts`
					WHERE `StaffID` = {$jobs_row->booked_by}
					");
					$staff_acc_row2 = $staff_acc_sql2->row();
					$booked_by_from_user_full = $this->system_model->formatStaffName($staff_acc_row2->FirstName, $staff_acc_row2->LastName);					
								
					$indv_job_log_arr[] = "Booked By updated from <strong>{$booked_by_from_user_full}</strong> to <strong>{$booked_by_to_user_full}</strong>";                            
				}

			}else{

				$indv_job_log_arr[] = "Booked By updated to <strong>{$booked_by_to_user_full}</strong>"; 

			}
            

			// Assigned Tech			
			$assigned_tech_to = 2; // Upfront Bill
			$tech_to = 'Upfront Bill';

			if( $jobs_row->assigned_tech != '' ){

				if ( $jobs_row->assigned_tech != $assigned_tech_to ) {

					// tech FROM
					$tech_acc_sql = $this->db->query("
					SELECT 
						`FirstName`,
						`LastName`
					FROM `staff_accounts`
					WHERE `StaffID` = {$jobs_row->assigned_tech}
					");
					$tech_acc_row = $tech_acc_sql->row();
					$tech_from = $this->system_model->formatStaffName($tech_acc_row->FirstName, $tech_acc_row->LastName);
								
					$indv_job_log_arr[] = "Technician updated from <strong>{$tech_from}</strong> to <strong>{$tech_to}</strong>";   
											 
				}

			}else{

				$indv_job_log_arr[] = "Technician updated to <strong>{$tech_to}</strong>";   

			}
            
			

			//update jobs fields
			$update_data = array(
				'job_type'      => $job_type_to,
				'status'	    => $job_status_to,				
				'booked_with'   => $booked_with_to,
				'booked_by'	    => $booked_by_to,
				'assigned_tech' => $assigned_tech_to
			);			

			if( $this->system_model->isDateNotEmpty($jobs_row->date) == false ){ // empty/null job date

				$update_data['date'] = $today;
				$indv_job_log_arr[] = "Date updated to <strong>".date('d/m/Y',strtotime($today))."</strong>";   

			}

			// insert job log
            if( count($indv_job_log_arr) > 0  ){

                $combined_job_log = implode(" | ",$indv_job_log_arr);

				//insert logs
				$log_params = array(
					'title' => 63,  // Job Update
					'details' => $combined_job_log,
					'display_in_vjd' => 1,
					'created_by_staff' => $this->session->staff_id,
					'property_id' => $jobs_row->property_id,
					'job_id' => $job_id
				);
				$this->system_model->insert_log($log_params);

            }
			
			$this->db->where('id', $job_id);
    		$this->db->update('jobs' ,$update_data);

			// get updated job
			// copied from email_functions.php, batchSendInvoicesCertificates function 
			$sql_str2 = "SELECT j.id, j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y') AS job_date,
				DATE_FORMAT(j.date, '%d/%m/%Y') AS date,
				j.job_price, j.price_used, 
				j.status, p.address_1, p.address_2, p.address_3, 
				p.state, p.postcode, j.id, p.property_id,
				a.agency_id, a.send_emails, a.account_emails, a.send_combined_invoice,
				DATE_FORMAT(DATE_ADD(j.date, INTERVAL 1 YEAR), '%d/%m/%Y') AS retest_date,
				j.ss_location,
				j.ss_quantity,
				sa.FirstName, 
				sa.LastName,
				j.work_order,
				p.`landlord_email`,
				p.`property_managers_id`,
				a.`allow_indiv_pm_email_cc`,
				p.`pm_id_new`,
				a.`franchise_groups_id`,
				a.`agency_name`,
				p.`landlord_firstname`,
				p.`landlord_lastname`,
				p.`propertyme_prop_id`,
				a.`pme_supplier_id`,
				p.`palace_prop_id`,
				a.`palace_diary_id`,
				apd.`api`,
				apd.`api_prop_id`,
				j.`id` AS `jservice`
				FROM (jobs j, property p, agency a)
				LEFT JOIN staff_accounts AS sa ON j.assigned_tech = sa.StaffID 
				LEFT JOIN api_property_data AS apd ON p.property_id = apd.crm_prop_id   
				WHERE j.property_id = p.property_id 
				AND p.agency_id = a.agency_id
				AND j.`id` = {$job_id}
				";
			$query = $this->db->query($sql_str2);

			// get the result as a array
			$job = $query->result_array();		
			//print_r($job);
			//exit();

			// Pme property ID exist and agency supplier ID exist
			if( $job[$i]['api_prop_id'] != '' && $job[$i]['pme_supplier_id'] != '' && $job[$i]['api'] == 1){
				$pme_billable = true;
			}
			
			// Palace property ID exist and palace diary ID exist
			if( $job[$i]['api_prop_id'] != '' && $job[$i]['palace_diary_id'] != '' && $job[$i]['api'] == 4){
				$palace_billable = true;
			}

			// Palace property ID exist and palace diary ID exist
			if( $job[$i]['api_prop_id'] != '' && $job[$i]['api'] == 6){
				$ourtradie_billable = true;
			}
			
			if( $pme_billable == true ){ // skip email

				//insert job log
				$data = array(
					'contact_type' =>'Upfront Job moved',
					'eventdate'	   =>date('Y-m-d'),
					'comments'	   =>'PMe connected job moved from <b>To Be Invoiced</b> to <b>Merged Jobs</b> for invoicing',
					'job_id'	   =>$job_id,
					'staff_id'	   =>$this->session->staff_id,
					'eventtime'	   =>date('H:i')
				);
			
				$this->db->insert('job_log',$data);
				
			}else if( $palace_billable == true ){ // skip email

				//insert job log
				$data = array(
					'contact_type' =>'Upfront Job moved',
					'eventdate'	   =>date('Y-m-d'),
					'comments'	   =>'Palace connected job moved from <b>To Be Invoiced</b> to <b>Merged Jobs</b> for invoicing',
					'job_id'	   =>$job_id,
					'staff_id'	   =>$this->session->staff_id,
					'eventtime'	   =>date('H:i')
				);
			
				$this->db->insert('job_log',$data);
				
			}else if( $ourtradie_billable == true ){ // skip email

				//insert job log
				$data = array(
					'contact_type' =>'Upfront Job moved',
					'eventdate'	   =>date('Y-m-d'),
					'comments'	   =>'Ourtradie connected job moved from <b>To Be Invoiced</b> to <b>Merged Jobs</b> for invoicing',
					'job_id'	   =>$job_id,
					'staff_id'	   =>$this->session->staff_id,
					'eventtime'	   =>date('H:i')
				);
			
				$this->db->insert('job_log',$data);
				
			}else{ // send email
				
				// check if agency has maintenance program
				$jemail = $this->email_functions_model->processMergedSendToEmails($job[$i]['agency_id'],$job[$i]['account_emails'],$job);
				
				// email invoice
				$invoice_only = 1;
				$this->email_functions_model->sendInvoiceCertEmail($job[$i], $jemail,$country_id,$invoice_only);

				//update jobs fields
				$data = array(
					'client_emailed'      =>$todaydt,
					'sms_sent_merge'	  =>$todaydt
				);

				$this->db->where('id', $job_id);
    			$this->db->update('jobs' ,$data);

			}
			$i++;
		}
	}
}

