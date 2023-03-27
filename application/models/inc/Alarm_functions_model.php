<?php

class Alarm_functions_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->model('/inc/functions_model');
    }


    public function getPropertyAlarms($job_id, $incnew = 1, $discarded = 1, $alarm_job_type_id = 1)
    {
        $query = "  SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason  
                    FROM alarm a 
                        LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
                        LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
                        LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
                    WHERE a.job_id = '" . $job_id . "'";

        if($alarm_job_type_id == 4 || $alarm_job_type_id == 5) // Safety Switch view and mech should have same alarms
        {
            $query .= " AND a.alarm_job_type_id IN (4,5)";
        }
        else
        {
            $query .= " AND a.alarm_job_type_id = {$alarm_job_type_id}";
        }

        
        
        if($incnew == 0) $query .= " AND a.New = 0";
        if($incnew == 2) $query .= " AND a.New = 1";
        
        if($discarded == 0) $query .= " AND a.ts_discarded = 0";
        if($discarded == 2) $query .= " AND a.ts_discarded = 1";
        
        $query .= " ORDER BY a.alarm_id ASC ";

        $alarms = $this->functions_model->mysqlMultiRows($query);
        
        return $alarms;
    }




    
   



}
