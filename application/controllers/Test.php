<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {
	
	public function __construct(){
		parent::__construct(); 
		$this->load->model('staff_accounts_model');
		//$this->load->library('pagination');
  	}  

	public function index(){


		$image  = '/uploads/complaints_files/Untitled_20221027_00379.png';
		echo $image_path = "{$_SERVER['DOCUMENT_ROOT']}{$image}";

		echo "<br /></br />";

		echo "<img src='{$image}' />";

		echo "exif_imagetype: <br />";
		$ret = exif_imagetype($image_path);
		print_r($ret);

		echo "<br /></br />";

		echo "getimagesize: <br />";
		$info  = getimagesize($image_path);
		print_r($info);
		

	}
		
	public function php_info(){
		phpinfo();
	}
	
	public function session(){
		print_r($_SESSION);
	}
	
	public function get_document_root(){
		echo $_SERVER["DOCUMENT_ROOT"].'/session';
	}

	public function update_agency_price_variation_updated_date(){

		$logs_sql = $this->db->query("
		SELECT 
			`agency_id`,
			`details`, 
			`created_date` 
		FROM `logs`
		WHERE `title` = 46
		AND `details` LIKE '% applied to agency because %'
		");
		echo $this->db->last_query();
		echo "<br /><br />";

		foreach( $logs_sql->result() as $logs_row ){

			// breakdown logs
			$log_det_arr = explode(" ",$logs_row->details);

			// type 
			$type = ( strip_tags($log_det_arr[0]) == 'Discount' )?1:2; // remove html bold(<b>) tags

			// amount			
			$amount = substr($log_det_arr[2], 1); // remove dollar($) sign

			// reason
			$append_reason = ( $log_det_arr[8] != '' )?" {$log_det_arr[8]}":null;
			$reason = strip_tags($log_det_arr[7].$append_reason ); // remove html bold(<b>) tags

			echo $update_sql = "
			UPDATE `agency_price_variation` AS apv
			LEFT JOIN `agency_price_variation_reason` AS apv_r ON apv.`reason` = apv_r.`id`
			SET apv.`updated_date` = '$logs_row->created_date'
			WHERE apv.`agency_id` = {$logs_row->agency_id}
			AND apv.`type` = {$type}
			AND apv.`amount` = {$amount}
			AND apv_r.`reason` = '{$reason}'
			";
			echo "<br /><br />";
			$this->db->query($update_sql);

		}

	}


	public function update_agency_price_variation_created_date(){

		$logs_sql = $this->db->query("
		SELECT 
			`agency_id`,
			`details`, 
			`created_date` 
		FROM `logs`
		WHERE `title` = 46
		AND `details` LIKE '% applied to agency because %'
		");
		echo $this->db->last_query();
		echo "<br /><br />";

		foreach( $logs_sql->result() as $logs_row ){

			// breakdown logs
			$log_det_arr = explode(" ",$logs_row->details);

			// type 
			$type = ( strip_tags($log_det_arr[0]) == 'Discount' )?1:2; // remove html bold(<b>) tags

			// amount			
			$amount = substr($log_det_arr[2], 1); // remove dollar($) sign

			// reason
			$append_reason = ( $log_det_arr[8] != '' )?" {$log_det_arr[8]}":null;
			$reason = strip_tags($log_det_arr[7].$append_reason ); // remove html bold(<b>) tags

			echo $update_sql = "
			UPDATE `agency_price_variation` AS apv
			LEFT JOIN `agency_price_variation_reason` AS apv_r ON apv.`reason` = apv_r.`id`
			SET apv.`created_date` = '$logs_row->created_date'
			WHERE apv.`agency_id` = {$logs_row->agency_id}
			AND apv.`type` = {$type}
			AND apv.`amount` = {$amount}
			AND apv_r.`reason` = '{$reason}'
			";
			echo "<br /><br />";
			$this->db->query($update_sql);

		}

	}

	public function var_dump_variations(){

		$tt_params = array(
			'service_type' => 2,
			'property_id' =>1259,
			'job_id' => 2473
		);
		$tt_price = $this->system_model->get_job_variation($tt_params);

		echo "<pre>";
		var_dump($tt_price);

	}

	public function export_tbi(){

		
		$this->load->model('agency_model');

			$agency_id = $this->input->get_post('agency_id');

			if($agency_id && $agency_id!="" && is_numeric($agency_id)){

				$lists = $this->db->query(
					"SELECT `j`.`id` AS `jid`, `j`.`job_type`, `j`.`status` AS `jstatus`, `j`.`service` AS `jservice`, `j`.`created` AS `jcreated`, `j`.`date` AS `jdate`,p.property_id, p.address_1, p.address_2, p.address_3, p.state, p.postcode, a.agency_name
					FROM `jobs` AS `j`
					LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
					LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
					WHERE `j`.`del_job` = 0
					AND `p`.`deleted` = 0
					AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
					AND `a`.`status` = 'active'
					AND `a`.`country_id` = {$this->config->item('country')}
					AND `a`.`deleted` = 0
					AND `j`.`status` = 'To Be Invoiced'
					AND a.agency_id = {$agency_id}"
				);

				


				// file name
				$date_export = date('d/m/Y');
				$filename = "TBI.csv";
	
				header("Content-type: application/csv");
				header("Content-Disposition: attachment; filename={$filename}");
				header("Pragma: no-cache");
				header("Expires: 0");
	
				// file creation 
				$csv_file = fopen('php://output', 'w');            
				
				$header = [];
				$header[] = "Job Date";
				$header[] = "Job ID";
				$header[] = "Property ID";
				$header[] = 'Property Address';
				$header[] = 'Agency Name';
				$header[] = 'Last YM';
	
				
				fputcsv($csv_file, $header);
				
				foreach ($lists->result() as $row) { 

					//last ym
					$last_ym = $this->agency_model->get_last_ym_by_prop_and_service($row->property_id,$row->jservice);
	
					$csv_row = [];                
					
					$csv_row[] = date("d/m/Y",strtotime($row->jdate));
					$csv_row[] = $row->jid;
					$csv_row[] = $row->property_id;
					$csv_row[] = "{$row->address_1} {$row->address_2} {$row->address_3}, {$row->state}, {$row->postcode}";
					$csv_row[] = $row->agency_name;
					$csv_row[] = $last_ym;
	
					fputcsv($csv_file,$csv_row);  
				}

			
				fclose($csv_file); 
				exit; 

			}

		

	}

}
