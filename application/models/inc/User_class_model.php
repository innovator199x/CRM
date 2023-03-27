<?php

class User_class_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->model('/inc/functions_model');
    }


    
    function getUserDetails($user_id)
	{
        $query = "SELECT a.*, b.ClassName FROM (staff_accounts a, staff_classes b)  WHERE a.StaffID = '$user_id' AND b.ClassID = a.ClassID AND Deleted = 0 LIMIT 1";
        
		$result =  $this->functions_model->mysqlSingleRow($query);
		
		if(is_numeric($result['StaffID'])) $result['States'] = $this->getUserStatePermissions($user_id);
		
		return $result;	
    }
    
    function getUserStatePermissions($user_id)
	{
		$query = "SELECT sd.StateID, sd.state FROM states_def sd, staff_states ss WHERE ss.StateID = sd.StateID AND ss.StaffID = '$user_id' ORDER BY sd.StateID ASC";
		
		$result = $this->functions_model->mysqlMultiRows($query);
		
		return $result;
    }
    

    function getTechDetails($tech_id)
	{
		$query = "SELECT * FROM staff_accounts WHERE StaffID = '$tech_id' LIMIT 1";
		$result = $this->functions_model->mysqlSingleRow($query);
		return $result;	
	}
	



}
