<?php
class Logs_model extends CI_Model {

	public function __construct(){
			$this->load->database();
	}

	public function _tab_getLogs($params) {
		$sel_query = "
				l.`log_id`,
				l.`created_date`,
				l.`title`,
				l.`details`,

				ltit.`title_name`,

				sa.`StaffID`,
				sa.`FirstName`,
				sa.`LastName`,
			";

		$this->db->select($sel_query);
		$this->db->from('logs AS l');
		$this->db->join('log_titles AS ltit', 'l.`title` = ltit.`log_title_id`', 'left');
		$this->db->join('staff_accounts AS sa', 'l.`created_by_staff` = sa.`StaffID`', 'left');

		if (isset($params['hook']) && is_callable($params['hook'])) {
			$params['hook']($this->db);
		}

		$this->db->order_by('l.`created_date`', 'DESC');

		$query = $this->db->get();

		$logs = $query->result();

		// print_r($logs);
		// die();

		return $logs;
	}

	public function getLogs($params){

		if( isset($params['sel_query']) ){
			$sel_query = $params['sel_query'];
		}else{
			$sel_query = "
				l.`log_id`,
				l.`created_date`,
				l.`title`,
				l.`details`,
				l.`auto_process`,
				l.`property_id`,

				ltit.`title_name`,

				aua.`agency_user_account_id`,
				aua.`fname`,
				aua.`lname`,

				sa.`StaffID`,
				sa.`FirstName`,
				sa.`LastName`,

				p.`address_1` AS p_address_1,
				p.`address_2` AS p_address_2,
				p.`address_3` AS p_address_3
			";
		}

		$this->db->select($sel_query);
		$this->db->from('logs AS l');
		$this->db->join('log_titles AS ltit', 'l.`title` = ltit.`log_title_id`', 'left');
		$this->db->join('agency_user_accounts AS aua', 'l.`created_by` = aua.`agency_user_account_id`', 'left');
		$this->db->join('staff_accounts AS sa', 'l.`created_by_staff` = sa.`StaffID`', 'left');
		$this->db->join('property AS p', 'p.property_id = l.property_id', 'left');

		if ( isset($params['joins']) ) {
			foreach ($params['joins'] as $join) {
				$j = array_merge([
					'type' => 'left',
				], $join);
				$this->db->join($j['table'], $j['condition'], $j['type']);
			}
		}

		// filters
		if( isset($params['user_filter']) && $params['user_filter']!="" ){
			$this->db->where('aua.`agency_user_account_id`', $params['user_filter']);
		}
		if( isset($params['log_id']) ){
			$this->db->where('l.`log_id`', $params['log_id']);
		}
		if( isset($params['log_title']) ){
			$this->db->where('l.`title`', $params['log_title']);
		}
		if( isset($params['log_type']) ){
			$this->db->where('l.`log_type`', $params['log_type']);
		}
		if( isset($params['created_by']) && $params['created_by'] != '' ){
			$this->db->where('l.`created_by`', $params['created_by']);
		}
		if( isset($params['job_id']) ){
			$this->db->where('l.`job_id`', $params['job_id']);
		}
		if( isset($params[' property_id ']) ){
			$this->db->where('l.`property_id`', $params['property_id']);
		}
		if( isset($params['agency_id']) ){
			$this->db->where('l.`agency_id`', $params['agency_id']);
		}
		if( isset($params['deleted']) ){
			$this->db->where('l.`deleted`', $params['deleted']);
		}
		if( isset($params['deleted_by']) ){
			$this->db->where('l.`deleted_by`', $params['deleted_by']);
		}
		if ( isset($params['staff_id']) ) {
			$this->db->where('l.`created_by_staff`', $params['staff_id']);
		}

		// markers
		if( isset($params['display_in_vjd']) && is_numeric($params['display_in_vad']) ){
			$this->db->where('l.`display_in_vjd`', $params['display_in_vjd']);
		}
		if( isset($params['display_in_vpd']) && is_numeric($params['display_in_vpd']) ){
			$this->db->where('l.`display_in_vpd`', $params['display_in_vpd']);
		}
		if( isset($params['display_in_vad']) && is_numeric($params['display_in_vad']) ){
			$this->db->where('l.`display_in_vad`', $params['display_in_vad']);
		}
		if( isset($params['display_in_portal']) && is_numeric($params['display_in_portal']) ){
			$this->db->where('l.`display_in_portal`', $params['display_in_portal']);
		}
		if( isset($params['display_in_accounts']) && is_numeric($params['display_in_accounts']) ){
			$this->db->where('l.`display_in_accounts`', $params['display_in_accounts']);
		}
		if( isset($params['display_in_accounts_hid']) && is_numeric($params['display_in_accounts_hid']) ){
			$this->db->where('l.`display_in_accounts_hid`', $params['display_in_accounts_hid']);
		}
		if( isset($params['display_in_sales']) && is_numeric($params['display_in_sales']) ){
			$this->db->where('l.`display_in_sales`', $params['display_in_sales']);
		}

		// custom filter
		if( isset($params['custom_where']) && $params['custom_where'] != '' ){
			$this->db->where($params['custom_where']);
		}

		if (isset($params['hook']) && is_callable($params['hook'])) {
			$params['hook']($this->db);
		}


		// sort
		if( isset($params['sort_list']) ){
			foreach( $params['sort_list'] as $sort_arr ){
				if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
					$this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
				}
			}
		}else{
			// default
			$this->db->order_by('l.`created_date`', 'DESC');
		}

		// limit
		if( isset($params['limit']) && $params['limit'] > 0 ){
			$this->db->limit( $params['limit'], $params['offset']);
		}

		$query = $this->db->get();
		if( isset($params['display_query']) && $params['display_query'] == 1 ){
			echo $this->db->last_query();
		}

		$logs = $query->result();

		if (!empty($logs)) {

            $logsById = [];
            $agencyUserPattern = "/agency_user:\d+/";
			$taggedAgencyUserIds = [];

			$staffIds = [];
			$staffPattern = "/staff_user:\d+/";
			$taggedStaffIds = [];

            for ($x = 0; $x < count($logs); $x++) {
                $log =& $logs[$x];

                $matches = [];
                if( preg_match($agencyUserPattern, $log->details, $matches) == 1 ) {
                    $agencyUserId = explode(':', $matches[0])[1];

                    $taggedAgencyUserIds[] = $agencyUserId;

                    $log->taggedAgencyUserId = $agencyUserId;
				}

				$staffMatches = [];
				if ( preg_match($staffPattern, $log->details, $staffMatches) == 1 ) {
					$staffId = explode(':', $staffMatches[0])[1];

					$taggedStaffIds[] = $staffId;
					$log->taggedStaffId = $staffId;
				}

                $logsById[$log->log_id] =& $log;
            }

            if (!empty($taggedAgencyUserIds)) {
                $taggedAgencyUserIds = array_unique($taggedAgencyUserIds);

				$taggedAgencyUsers = $this->db->select("
						aua.`agency_user_account_id`,
						aua.`fname`,
						aua.`lname`
					")
					->from("agency_user_accounts AS aua")
					->where_in("aua.agency_user_account_id", $taggedAgencyUserIds)
					->get()->result();

                foreach ($logs as &$log) {
                    if (isset($log->taggedAgencyUserId)) {
                        foreach ($taggedAgencyUsers as $taggedAgencyUser) {
                            if ($log->taggedAgencyUserId == $taggedAgencyUser->agency_user_account_id) {
                                $log->taggedAgencyUser = $taggedAgencyUser;
                                break;
                            }
                        }
                    }
				}
			}

            if (!empty($taggedStaffIds)) {
                $taggedStaffIds = array_unique($taggedStaffIds);

				$taggedStaffs = $this->db->select("
						sa.`StaffID`,
						sa.`FirstName`,
						sa.`LastName`
					")
					->from("staff_accounts AS sa")
					->where_in("sa.StaffID", $taggedStaffIds)
					->get()->result();

                foreach ($logs as &$log) {
                    if (isset($log->taggedStaffId)) {
                        foreach ($taggedStaffs as $taggedStaff) {
                            if ($log->taggedStaffId == $taggedStaff->StaffID) {
                                $log->taggedStaff = $taggedStaff;
                                break;
                            }
                        }
                    }
				}
			}
		}


		return $logs;
	}

	public function _tab_getLogsCount($params){

		$sel_query = "
			IFNULL(COUNT(l.`log_id`), 0) AS count
		";

		$this->db->select($sel_query);
		$this->db->from('logs AS l');
		$this->db->join('log_titles AS ltit', 'l.`title` = ltit.`log_title_id`', 'left');
		$this->db->join('staff_accounts AS sa', 'l.`created_by_staff` = sa.`StaffID`', 'left');
		if (isset($params['hook']) && is_callable($params['hook'])) {
			$params['hook']($this->db);
		}
		$query = $this->db->get();

		return $query->row()->count;
	}

	public function getLogsCount($params){

		$sel_query = "
			IFNULL(COUNT(l.`log_id`), 0) AS count
		";

		$this->db->select($sel_query);
		$this->db->from('logs AS l');
		$this->db->join('log_titles AS ltit', 'l.`title` = ltit.`log_title_id`', 'left');
		$this->db->join('agency_user_accounts AS aua', 'l.`created_by` = aua.`agency_user_account_id`', 'left');
		$this->db->join('staff_accounts AS sa', 'l.`created_by_staff` = sa.`StaffID`', 'left');
		$this->db->join('property AS p', 'p.property_id = l.property_id', 'left');

		if ( isset($params['joins']) ) {
			foreach ($params['joins'] as $join) {
				$j = array_merge([
					'type' => 'left',
				], $join);
				$this->db->join($j['table'], $j['condition'], $j['type']);
			}
		}

		// filters
		if( isset($params['user_filter']) && $params['user_filter']!="" ){
			$this->db->where('aua.`agency_user_account_id`', $params['user_filter']);
		}
		if( isset($params['log_id']) ){
			$this->db->where('l.`log_id`', $params['log_id']);
		}
		if( isset($params['log_title']) ){
			$this->db->where('l.`title`', $params['log_title']);
		}
		if( isset($params['log_type']) ){
			$this->db->where('l.`log_type`', $params['log_type']);
		}
		if( isset($params['created_by']) && $params['created_by'] != '' ){
			$this->db->where('l.`created_by`', $params['created_by']);
		}
		if( isset($params['job_id']) ){
			$this->db->where('l.`job_id`', $params['job_id']);
		}
		if( isset($params[' property_id ']) ){
			$this->db->where('l.`property_id`', $params['property_id']);
		}
		if( isset($params['agency_id']) ){
			$this->db->where('l.`agency_id`', $params['agency_id']);
		}
		if( isset($params['deleted']) ){
			$this->db->where('l.`deleted`', $params['deleted']);
		}
		if( isset($params['deleted_by']) ){
			$this->db->where('l.`deleted_by`', $params['deleted_by']);
		}
		if ( isset($params['staff_id']) ) {
			$this->db->like('l.`details`', "{staff_user:{$params['staff_id']}}");
		}

		// markers
		if( isset($params['display_in_vjd']) && is_numeric($params['display_in_vad']) ){
			$this->db->where('l.`display_in_vjd`', $params['display_in_vjd']);
		}
		if( isset($params['display_in_vpd']) && is_numeric($params['display_in_vpd']) ){
			$this->db->where('l.`display_in_vpd`', $params['display_in_vpd']);
		}
		if( isset($params['display_in_vad']) && is_numeric($params['display_in_vad']) ){
			$this->db->where('l.`display_in_vad`', $params['display_in_vad']);
		}
		if( isset($params['display_in_portal']) && is_numeric($params['display_in_portal']) ){
			$this->db->where('l.`display_in_portal`', $params['display_in_portal']);
		}
		if( isset($params['display_in_accounts']) && is_numeric($params['display_in_accounts']) ){
			$this->db->where('l.`display_in_accounts`', $params['display_in_accounts']);
		}
		if( isset($params['display_in_accounts_hid']) && is_numeric($params['display_in_accounts_hid']) ){
			$this->db->where('l.`display_in_accounts_hid`', $params['display_in_accounts_hid']);
		}
		if( isset($params['display_in_sales']) && is_numeric($params['display_in_sales']) ){
			$this->db->where('l.`display_in_sales`', $params['display_in_sales']);
		}

		// custom filter
		if( isset($params['custom_where']) && $params['custom_where'] != '' ){
			$this->db->where($params['custom_where']);
		}

		if (isset($params['hook']) && is_callable($params['hook'])) {
			$params['hook']($this->db);
		}

		$query = $this->db->get();
		if( isset($params['display_query']) && $params['display_query'] == 1 ){
			echo $this->db->last_query();
		}

		return $query->row()->count;
	}
}
?>