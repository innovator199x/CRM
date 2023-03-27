<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tech extends CI_Controller {

	public function __construct(){

        parent::__construct();
        $this->load->model('tech_model');
	}
	
	
	public function view_overall_schedule()
	{


		$data['title'] = "View Schedule";
		 $this->load->view('templates/inner_header', $data);
        $this->load->view('tech/view_overall_schedule', $data);
        $this->load->view('templates/inner_footer', $data);
    }
    
    public function json_view_overall_schedule_calendar(){

        $usemonth = $this->input->get_post('month');
        $useyear = $this->input->get_post('year');

        if ($usemonth == "") { $usemonth = date("m",time()); }
        if ($useyear == "") { $useyear = date("Y", time()); }
        $actday = 1;
     
        #$jobdate = $useyear."-".$usemonth."-".$actday;

        $getJobSched_query = $this->tech_model->getJobSched();
      



        if(!empty($getJobSched_query)){
			foreach($getJobSched_query->result_array() as $row){


                //get region by postcode
                $pr_sql_params = array(
                    'sel_query' => 'r.region_name, sr.subregion_name as postcode_region_name',
                    'postcode' => $row['postcode'],
                    'delete' => 0
                );
                //$pr_sql = $this->system_model->getSubRegion($pr_sql_params);
                $pr_sql = $this->system_model->get_postcodes($pr_sql_params);
                $pr = $pr_sql->row_array();

				
                $color =  ($row->marked_as_leave==1)?'event-red':'event-blue';
                
                if($row['date']!=NULL){ //check if date is no null 
                    $title = $this->system_model->formatStaffName($row['FirstName'], $row['LastName'])." ({$pr['postcode_region_name']})";
                    $data[] = array(
                        'id' => $row['id'],
                        'staffName' => $this->system_model->formatStaffName($row['FirstName'], $row['LastName']),
                        'start' => $row['date'],
                        'end' => $row['date']."T23:59:00",
                        'title' => $title,
                        'className' => $color,
                        'url' => $this->config->item("crm_link")."/view_job_details.php?id={$row['id']}"
                    );

                }
			}
		}
		
        echo json_encode($data);
        

    }

    public function view_overall_schedule_day(){

        $this->load->model('jobs_model');

        $date = $this->input->get_post('date');
        $useyear = date('Y', strtotime($date));
        $usemonth = date('m', strtotime($date));
        $useday = date('d', strtotime($date));
        $jobdate = $useyear."-".$usemonth."-".$useday;
        $datebooked = $useday."-".$usemonth."-".$useyear;
        $today = date("d-m-Y");	
        $dc = date_create($datebooked);
        $dateformat = date_format($dc, 'd-m-Y');

        $data['datebooked'] = ($dateformat == $today) ? "today" : date_format($dc, 'D d-M-Y');

        //main list query by date and booked
        $main_list_query = array(
            'sel_query' => 'j.status, p.address_1, p.address_2, p.address_3, p.postcode, p.key_number, j.time_of_day, j.tech_notes, sa.FirstName, sa.LastName, p.property_id, j.id, j.service, ajt.type',
            'join_table' => array('staff_accounts','alarm_job_type'),
            'del_job' => 0,
            'p_deleted' => 0,
            'country_id' => $this->config->item('country'),
            'job_status' => 'Booked',
            'date' => $jobdate,
            'sort_list' => array(
                array('order_by'=> 'sa.FirstName','sort' => 'ASC')
            )
        );
        $main_list = $this->jobs_model->get_jobs($main_list_query);



        if($this->input->get_post('export') && $this->input->get_post('export')==1){ //EXPORT

            $filename = "Overall_Schedule_Day_".$useday."-".$usemonth ."-".$useyear.".csv";

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");

            //header
            echo "Service,Status,Address,Postcode,Time,Name\n";

            foreach ($main_list->result_array() as $row)
            {
                 echo "\"{$row['type']}\",{$row['status']},\"$row[address_1] $row[address_2] $row[address_3]\",$row[postcode],$row[time_of_day],\"$row[FirstName] $row[LastName]\"\n"; 		
            }
            

        }else{ //NORMAL LIST 

            //get job sched count that is booked
            $jobs_params = array(
                'sel_query' => "COUNT(j.id) as j_count",
                'job_status' => 'Booked',
                'date' => $jobdate,
                'del_job' => 0,
                'country_id' => $this->config->item('country')
            );
            $job_count_query = $this->jobs_model->get_jobs($jobs_params);
            $data['job_count'] = $job_count_query->row()->j_count;


            //get main job list by date
            $data['job_query'] = $main_list;

            $data['title'] = "Overall Schedule";
            $this->load->view('templates/inner_header', $data);
            $this->load->view('tech/view_overall_schedule_day', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }

        
           
    }

    


}
