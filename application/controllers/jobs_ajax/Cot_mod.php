<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cot_mod extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
        $this->load->database();
        $this->load->model('jobs_model');
        $this->load->helper('url');
        $this->load->library('customlib');
    }   

	public function ajax_move_to_maps(){	
		$job_id = $this->input->post('job_id');
		$tech_id = $this->input->post('tech_id');
		$date = $this->input->post('date');
		$date2 = date("Y-m-d",strtotime(str_replace("/","-",$date)));
		
		$updateData = array(
		    'status' => 'To Be Booked',
		    'assigned_tech' => $tech_id,
		    'date' => $date2
		);
		$updateStatus = array();
		foreach($job_id as $val){
			$this->db->where('id', $val);
			$this->db->update('jobs', $updateData);
			if ($this->db->affected_rows() > 0){
				array_push($updateStatus, true);
			}else {
				array_push($updateStatus, false);
			}
		}

        echo in_array(true, $updateStatus);
	}

	public function get_main_region_by_state() {

		$state = $this->input->post('state');
		$job_type = $this->input->post('job_type');
		$job_status = $this->input->post('job_status');
		$custom_query_flag = $this->input->post('custom_query_flag');
		$ulStr = "";

		if($custom_query_flag==1){
			$custom_query = " AND j.property_vacant = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') ";
		}else {
			$custom_query = " 1=1";
		}


		$this->db->select('DISTINCT(r.`regions_id`), r.`region_name`');
		$this->db->from('postcode_regions pr');
		$this->db->join('regions r','pr.region = r.regions_id', 'left');
		$this->db->where('r.region_state', $state);
		$this->db->where('pr.country_id', $this->config->item('country'));
		$this->db->where('pr.deleted', 0);
      	$this->db->where($custom_query);
        $this->db->order_by('r.region_name');

		$reg_sql = $this->db->get();

		if($reg_sql->num_rows() >0 ){ 

			foreach ($reg_sql->result_array() as $reg) {
			$region_id = $reg['regions_id'];
			$region_postcodes = str_replace(',,',',',$this->customlib->getTobeBookedPostcodeViaRegion($region_id));
			$region_count = $this->customlib->getTobeBookedSubRegionCount($_SESSION['country_default'],$region_postcodes,$job_type,$job_status,$custom_query);
				if($region_count>0){
					$jcount_txt = ($region_count>0)?"({$region_count})":"";
					$regName = $reg['region_name'] . $jcount_txt;
					$regId = $reg['regions_id'];
					$ulStr .= `<ul class="<?php echo $state; ?>_regions">
								<li class="main_region_li">
									<div class="region_wrapper">
										<span>
											<input type="checkbox" class="region_check_all" style="display:none;" value="" />
										</span>
										<span class="reg_db_main_reg">
											{$regName}
											<input type="hidden" class="sel_region_id" value="{$regId}" />
										</span>
										<span style="position: relative; top: 2px;">
											<input type="checkbox" class="check_all_sub_region" />
										</span>
									</div>
									<input type="hidden" value="<?php echo $region_postcodes; ?>" />
									<div style="clear:both;"></div>
									<ul class="reg_db_sub_reg"></ul>
									<input type="hidden" class="regions_id" value="<?php echo $region_id; ?>" />
								</li>
							</ul>`;
				}
			}
			
		}

		echo $ulStr;


	}

	public function get_sub_region() {

		$return_type = $this->input->post('return_type');
		$sel_sub_regions = $this->input->post('sel_sub_regions');

		$sel_sub_reg = explode(",",$sel_sub_regions);

		$region = $this->input->post('region');
		$job_type = $this->input->post('job_type');
		$job_status = $this->input->post('job_status');
		$urgent_job = $this->input->post('urgent_job');
		$ulStr = "";

		$this->db->select('*');
		$this->db->from('postcode_regions');
		$this->db->where('region', $region);
		$this->db->where('country_id', $this->config->item('country'));
		$this->db->where('deleted', 0);
        $this->db->order_by('postcode_region_name');

		$reg_sql = $this->db->get();

		if($reg_sql->num_rows() >0 ){ 

			foreach ($reg_sql->result_array() as $reg) {
				$region_postcodes = str_replace(',,',',',$reg['postcode_region_postcodes']);
				$ret = ($return_type=='region_id')?$reg['postcode_region_id']:$region_postcodes;
				$jparams = array('urgent_job'=>$urgent_job);
				$sub_region_count = $jc->getMainRegionCount($_SESSION['country_default'],$region_postcodes,$job_type,$job_status,$jparams);
				if($sub_region_count>0){ 
					$jcount_txt = ($sub_region_count>0)?"({$sub_region_count})":""; 
					$regName = ( $return_type=='region_id' && in_array($reg['postcode_region_id'], $sel_sub_reg) )?'checked="checked"':'';
					$regId = $reg['postcode_region_name'];
					$ulStr .= `
							<li>
								<input type="checkbox" name="postcode_region_id[]" class="postcode_region_id" value="<?php echo $ret; ?>" 
								<?php echo $regName  ?> /> 
								<?php echo $regId ?> <?php echo $jcount_txt; ?>
							</li>`;
				}
			}
			
		}

		echo $ulStr;


	}

    public function cot_filter_ajax(){

        $filterType = $this->input->get_post('filterType');
        $country_id = $this->config->item('country');
        $job_status = 'To Be Booked';
        $custom_where = " ( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' )";
        $custom_sort = " (CASE WHEN j.due_date IS NULL THEN 1 ELSE 0 END), j.due_date ASC";

        if($filterType){
        
            if($filterType ==  "Agency"){  // AGENCY FILTER

                //Agency name filter
		        $sel_query = "DISTINCT(a.`agency_id`),
		        a.`agency_name`";
		        $params = array(
		            'sel_query' => $sel_query,
		            'p_deleted' => 0,
		            'a_status' => 'active',
		            'del_job' => 0,
		            'country_id' => $country_id,
		            'join_table' => array('job_type','alarm_job_type'),
		            'custom_where' => $custom_where,
		            'custom_sort' => $custom_sort,
		        );
                $agency_filter = $this->jobs_model->get_jobs($params);

                foreach($agency_filter->result_array() as $row){        
                    echo "<option value='{$row["agency_id"]}'>{$row["agency_name"]}</option>";
                }

            }elseif($filterType == "JobType"){  //JOB TYPE FILTER

                
		        //Job type Filter
		        $sel_query = "DISTINCT(j.`job_type`),
		                `j.job_type`";
		        $params = array(
		            'sel_query' => $sel_query,
		            'p_deleted' => 0,
		            'a_status' => 'active',
		            'del_job' => 0,
		            'country_id' => $country_id,
		            'job_status' => $job_status,
		            'join_table' => array('job_type','alarm_job_type'),
		            'custom_sort' => $custom_sort,
		        );
		        $job_filter = $this->jobs_model->get_jobs($params);


                foreach($job_filter->result_array() as $row){               
                    // echo "<li>{$row["job_type"]}<li>";
                    echo "<option value='{$row["job_type"]}'>{$row["job_type"]}</option>";
                }
            }elseif ($filterType == "Service") {

		        $sel_query = "DISTINCT(j.`service`),
		                `ajt.type`";
		        $params = array(
		            'sel_query' => $sel_query,
		            'p_deleted' => 0,
		            'a_status' => 'active',
		            'del_job' => 0,
		            'country_id' => $country_id,
		            'job_status' => $job_status,
		            'join_table' => array('job_type','alarm_job_type'),
		            'custom_sort' => $custom_sort,
		        );
		        $service_filter = $this->jobs_model->get_jobs($params);

                foreach($service_filter->result_array() as $row){       
                    echo "<option value='{$row["service"]}'>{$row["type"]}</option>";
                }
            }elseif ($filterType == "State") {

		        $sel_query = "DISTINCT(p.`state`)";
		        $params = array(
		            'sel_query' => $sel_query,
		            'p_deleted' => 0,
		            'a_status' => 'active',
		            'del_job' => 0,
		            'country_id' => $country_id,
		            'job_status' => $job_status,
		            'join_table' => array('job_type','alarm_job_type'),
		            'custom_sort' => $custom_sort,
		        );
		        $state_filter = $this->jobs_model->get_jobs($params);

                foreach($state_filter->result_array() as $row){       
                    echo "<option value='{$row["state"]}'>{$row["state"]}</option>";
                }
            }
       
        }else{
            echo "Error: Filter type not set";
        }
    }
	

}

