<?php

class Booking_model extends CI_Model{

    public function __construct(){
        $this->load->database();
    }

    public function get_booking_schedule_num_days(){

        return $this->db->select('booking_schedule_num AS schedule_num_days')
                        ->where('StaffID', $this->session->staff_id)
                        ->limit(1)
                        ->get('staff_accounts')
                        ->row();
    }

    public function get_assigned_tech($date){
        return $this->db->select('assigned_tech')
                        ->from('tech_run AS tr')
                        ->join("staff_accounts AS sa",'tr.assigned_tech = sa.StaffID','left')
                        ->where('tr.date', $date)
                        ->where('tr.country_id', $this->config->item('country'))
                        ->order_by('tr.tech_run_id', 'asc')
                        ->get()
                        ->result();
    }

    public function get_tech_with_runs(){
        return $this->db->select('assigned_tech, sa.FirstName, sa. LastName')
                        ->from('tech_run AS tr')
                        ->join("staff_accounts AS sa",'tr.assigned_tech = sa.StaffID')
                        ->where('tr.country_id', $this->config->item('country'))
                        ->where('tr.date >=', date('Y-m-d'))
                        ->group_by(["tr.assigned_tech"])
                        ->order_by('sa.FirstName', 'asc')
                        ->get()
                        ->result();
    }

    public function get_tech_run_dates(){
        return $this->db->select('tr.date')
                        ->from('tech_run AS tr')
                        ->where('tr.country_id', $this->config->item('country'))
                        ->where('tr.date >=', date('Y-m-d'))
                        ->group_by(["tr.date"])
                        ->order_by('tr.date', 'asc')
                        ->get()
                        ->result();
    }

    public function get_run_status(){
        $status = [
             ["id" => 1, "status" => "Run Set"],
             ["id" => 2, "status" => "Ready to Book"],
             ["id" => 3, "status" => "1st Call Over Done"],
             ["id" => 4, "status" => "Run Reviewed"],
             ["id" => 5, "status" => "2nd Call Over Done"],
             ["id" => 6, "status" => "Extra Call Over"],
             ["id" => 7, "status" => "Exra Call Over Done"],
             ["id" => 8, "status" => "Run Ready to Map"],
             ["id" => 9, "status" => "Run Mapped"],
             ["id" => 11, "status" => "Morning Call Over"],
             ["id" => 10, "status" => "FULL - No More Jobs"]
         ];

        //  $keys = array_column($status, 'id');
        //  array_multisort($keys, SORT_DESC, $status);
 
         return (object)$status;
     }

    public function get_calendar($params){
        $cal_fields = "c.calendar_id,
                    c.region,
                    c.details,
                    c.booking_staff,
                    c.accomodation,
                    sa.FirstName,
                    sa.LastName,
                    sa.StaffID";
        return $this->db->select($cal_fields)
            ->from('calendar AS c')
            ->join('staff_accounts AS sa','c.booking_staff = sa.StaffID','left')
            ->where('c.staff_id', $params->assigned_tech)
            ->where('c.country_id', $this->config->item('country'))
            ->where('c.date_start >=', $params->date)
            ->where('c.date_finish <=', $params->date)
            ->get()
            ->row();
    }

    public function get_completed($params){
        return $this->db->select("COUNT(j.id) AS jcount")
            ->from('jobs AS j')
            ->join('property AS p','j.property_id = p.property_id','left')
            ->join('agency AS a','a.agency_id = p.agency_id','left')
            ->where('j.assigned_tech', $params->assigned_tech)
            ->where('j.del_job', 0)
            ->where('p.deleted', 0)
            ->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )") 
            ->where('a.status', 'active')
            ->where('j.date', $params->date)
            ->where('a.country_id', $this->config->item('country'))
            ->where('j.ts_completed', 1)
            ->get()
            ->row();
    }

    public function get_booked($params){
        return $this->db->select("COUNT(j.id) AS jcount")
            ->from('jobs AS j')
            ->join('property AS p','j.property_id = p.property_id','left')
            ->join('agency AS a','a.agency_id = p.agency_id','left')
            ->where('j.assigned_tech', $params->assigned_tech)
            ->where('j.del_job', 0)
            ->where('j.status', 'Booked')
            ->where('j.date', $params->date)
            ->where('j.door_knock', 0)
            ->where('a.status', 'active')
            ->where('a.country_id', $this->config->item('country'))
            ->where('p.deleted', 0)
            ->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )") 
            ->get()
            ->row();
    }

    public function get_door_knock($params){
        return $this->db->select("COUNT(j.id) AS jcount")
            ->from('jobs AS j')
            ->join('property AS p','j.property_id = p.property_id','left')
            ->join('agency AS a','a.agency_id = p.agency_id','left')
            ->where('j.assigned_tech', $params->assigned_tech)
            ->where('j.del_job', 0)
            ->where('p.deleted', 0)
            ->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )") 
            ->where('a.status', 'active')
            ->where('j.date', $params->date)
            ->where('a.country_id', $this->config->item('country'))
            ->where('j.door_knock', 1)
            ->where_in('j.status', ['Booked', 'To Be Booked'])
            ->get()
            ->row();
    }

    public function get_billable($params){
        return $this->db->select("COUNT(j.id) AS jcount")
            ->from('jobs AS j')
            ->join('property AS p','j.property_id = p.property_id','left')
            ->join('agency AS a','a.agency_id = p.agency_id','left')
            ->where('j.assigned_tech', $params->assigned_tech)
            ->where('j.del_job', 0)
            ->where('p.deleted', 0)
            ->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )") 
            ->where('a.status', 'active')
            ->where('j.status', 'Booked')
            ->where('j.date', $params->date)
            ->where('a.country_id', $this->config->item('country'))
            ->where('j.door_knock', 0)
            ->where('j.job_price >', 0)
            ->get()
            ->row();

    }

    public function get_tech_runs($search_params){
        // used raw query due to SUBSTRING_INDEX(tr.sub_regions, ',' , 1) in joining sub_regions table
        $sql_str = "SELECT tr.*,
                           sa.StaffID,
                           sa.FirstName,
                           sa.LastName,
                           r.region_state AS `state`
                    FROM tech_run AS tr 
                    LEFT JOIN staff_accounts AS sa ON tr.assigned_tech = sa.StaffID 
                   
                    LEFT JOIN sub_regions AS sr ON SUBSTRING_INDEX(tr.sub_regions, ',' , 1) = sr.sub_region_id
                    LEFT JOIN regions AS r ON sr.region_id = r.regions_id 
                    LEFT JOIN postcode AS p ON sr.sub_region_id = p.sub_region_id 
                    WHERE tr.country_id = '{$this->config->item('country')}' ";
                    
                    //  LEFT JOIN jobs AS j ON j.assigned_tech = sa.StaffID
                    // LEFT JOIN (SELECT property_id, address_3, postcode FROM property) AS p ON p.property_id = j.property_id
            
        
        if ($search_params->date){
            $sql_str .= " AND tr.date = '{$search_params->date}'";
        } else {
            $sql_str .= " AND tr.date >= '" . date('Y-m-d') ."'";
        }

        // search by phrase
        if ($search_params->search_phrase){
            $sql_str .= " AND ( 
                            sr.subregion_name LIKE '%{$search_params->search_phrase}%' OR
                            r.region_name LIKE '%{$search_params->search_phrase}' OR
                            p.postcode LIKE '%{$search_params->search_phrase}'
                        )";

            // p.address_3 LIKE '%{$search_params->search_phrase}%' OR
            // p.postcode LIKE '%{$search_params->search_phrase}%' OR
        }

        // search by state
        if ($search_params->state_filter){
            $sql_str .= " AND ( ";

            foreach($search_params->state_filter as $key => $state) {
                if ($key == 0){
                    $sql_str .= " r.region_state = '{$state}' ";
                } else {
                    $sql_str .= " OR r.region_state = '{$state}' ";
                }
            }

            $sql_str .= " )";
        }

        // search by assigned tech
        if ($search_params->assigned_tech){
            $sql_str .= " AND tr.assigned_tech = '{$search_params->assigned_tech}' ";
        }

        if ($search_params->run_status){
            switch ($search_params->run_status) {
                case 1:
                    $sql_str .= " AND  tr.run_set = 1 ";
                    break;
                case 2:
                    $sql_str .= " AND  tr.ready_to_book = 1 ";
                    break;
                case 3:
                    $sql_str .= " AND  tr.first_call_over_done = 1 ";
                    break;
                case 4:
                    $sql_str .= " AND  tr.run_reviewed = 1 ";
                    break;
                case 5:
                    $sql_str .= " AND  tr.finished_booking = 1 ";
                    break;
                case 6:
                    $sql_str .= " AND  tr.additional_call_over = 1 ";
                    break;
                case 7:
                    $sql_str .= " AND  tr.additional_call_over_done = 1 ";
                    break;
                case 8:
                    $sql_str .= " AND  tr.ready_to_map = 1 ";
                    break;
                case 9:
                    $sql_str .= " AND  tr.run_mapped = 1 ";
                    break;
                case 10:
                    $sql_str .= " AND  tr.no_more_jobs = 1 ";
                    break;
                case 11:
                    $sql_str .= " AND  tr.morning_call_over = 1 ";
                    break;
                default:
            }
        }
        
        // get states
        if ($search_params->get_states){
            $sql_str .=  " AND r.region_state  != '' GROUP BY r.region_state";
            return $this->db->query($sql_str)->result(); 
        }
        
        if ($search_params->group_by_date){
            $sql_str .=  " GROUP BY tr.date ";
        } else {
            $sql_str .=  " GROUP BY r.region_state, sa.StaffID";
        }

        return $this->db->query($sql_str)->result();
    }
 
}