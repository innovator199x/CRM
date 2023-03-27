<?php

class Customlib {


	// We'll use a constructor, as you can't directly call a function
	// from a property definition.
	protected $CI;

	public function __construct(){
		$this->CI =& get_instance();
		$this->CI->load->database();
    	$this->CI->load->model('system_model');
	}

	/**
	 * GET post code region name
	 * @param postCode, countryId
	 * return region name
	 */
	function getPostCodeRegName($postCode, $countryId){

		/* OLD TABLE >Gherx
		$this->CI->db->select('postcode_region_name');
		$this->CI->db->from('postcode_regions');
		$this->CI->db->like('postcode_region_postcodes',$postCode);
		$this->CI->db->where('country_id',$countryId);
		$this->CI->db->where('deleted',0);
		*/

		$this->CI->db->select('sr.subregion_name as postcode_region_name');
		$this->CI->db->from('postcode as pc');
		$this->CI->db->join('sub_regions as sr','sr.sub_region_id = pc.sub_region_id','left');
		$this->CI->db->where('pc.postcode',$postCode);
		$this->CI->db->where('pc.deleted',0);

		$query = $this->CI->db->get();
		return $query->result();
		// return $this->CI->db->last_query();
	}

	/**
	 * GET last comleted job date
	 * @param propertyId
	 * return completed date
	 */
	function getLastCompletedJob($propertyId){
		$this->CI->db->select('j.`date` AS jdate, j.`job_type` , j.`assigned_tech`');
		$this->CI->db->from('jobs as j');
		$this->CI->db->join('property p','j.property_id = p.property_id', 'left');
		$this->CI->db->join('agency a','p.agency_id = a.agency_id', 'left');
		$this->CI->db->where('p.property_id',$propertyId);
		$this->CI->db->where('j.status','Completed');
		$this->CI->db->where('p.deleted',0);
		$this->CI->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
		$this->CI->db->where('j.del_job',0);
		$this->CI->db->where('a.status','active');
        $this->CI->db->order_by('a.status','active');
		$this->CI->db->limit(1);
		$query = $this->CI->db->get();
		// return $this->CI->db->last_query();
		return $query->result();
	}

	/**
	 * GET determine if date is empty
	 * @param date
	 * return true or false
	 */
	function isDateNotEmpty($date){
		if(
			$date!='' &&
			$date!='0000-00-00' &&
			$date!='0000-00-00 00:00:00' &&
			$date!='1970-01-01'
		){
			return true;
		} else{
			return false;
		}
	}

	/**
	 * GET alarms 240v or 240vli are expired
	 * @param job_id
	 * return true or false
	 */
	function getAlarm($jobId){
		$where_au = "(alarm_power_id = 2 OR alarm_power_id = 4)";
		$this->CI->db->select('*');
		$this->CI->db->from('alarm');
		$this->CI->db->where('job_id',$jobId);
		$this->CI->db->where('expiry',date("Y"));
		$this->CI->db->where($where_au);
		$query = $this->CI->db->get();
		return $query->result();
	}

	function isDHAagenciesV2($fg_id){
		// Defence Housing
		if( $fg_id == 14 ){
			return true;
		}else{
			return false;
		}
	}

	function jFormatDateToBeDbReady($date){
		return date('Y-m-d',strtotime(str_replace("/","-",mysql_real_escape_string($date))));
	}

	function getInvoiceTotal($job_id){
		# Job Details
		$job_details = $this->CI->system_model->getJobDetails2($job_id);
		# Alarm Details
		$alarm_details = $this->CI->system_model->getPropertyAlarms($job_id, 1, 0, 2);
		$num_alarms = sizeof($alarm_details);
		$grand_total = $job_details[0]['job_price'];

		// installed alarm
		for($x = 0; $x < $num_alarms; $x++)
		{
			if($alarm_details[$x]['new'] == 1)
			{
				$grand_total += $alarm_details[$x]['alarm_price'];
			}
		}

		$this->CI->db->select('*, m.`name` AS m_name');
		$this->CI->db->from('`agency_maintenance` AS am');
		$this->CI->db->join('maintenance m','am.maintenance_id = m.maintenance_id', 'left');
		$this->CI->db->where('am.`agency_id`', $job_details['agency_id']);
		$query = $this->CI->db->get();
		$sc_sql = $query->result();

		if( $grand_total!=0 && $sc_sql['surcharge']==1 ){
			$grand_total += $sc_sql['price'];
		}

		return $grand_total;
	}

	function getInvoiceNumber($job_id){
		// append checkdigit to job id for new invoice number
		$check_digit = $this->getCheckDigit(trim($job_id));
		return $bpay_ref_code = "{$job_id}{$check_digit}";
	}

	// compute check digit
	function getCheckDigit($number){

		$sumTable = array(array(0,1,2,3,4,5,6,7,8,9),array(0,2,4,6,8,1,3,5,7,9));
		$length = strlen($number);
				$sum = 0;
				$flip = 1;
				// Sum digits (last one is check digit, which is not in parameter)
				for($i=$length-1;$i>=0;--$i) $sum += $sumTable[$flip++ & 0x1][$number[$i]];
				// Multiply by 9
				$sum *= 9;

		return (int)substr($sum,-1,1);
	}

	function get240vRfAgencyAlarm($agency_id){

		$this->CI->db->select('price');
		$this->CI->db->from('agency_alarms');
		$this->CI->db->where('agency_id', $agency_id);
		$this->CI->db->where('alarm_pwr_id', 10);
		$this->CI->db->limit(1);
		$query = $this->CI->db->get();
		$row = $query->result();

		return $row[0]->price;
	}

	// format date
	function formatDate($date,$format='Y-m-d'){
		return date($format,strtotime(str_replace("/","-",$date)));
	}

	// format date
	function getStaffData($defaultCountry){

		$this->CI->db->select('sa.`StaffID`, sa.`FirstName`, sa.`LastName`,
			sa.`is_electrician`,
			 sa.`active` AS sa_active');
		$this->CI->db->from('staff_accounts sa');
		$this->CI->db->join('country_access ca','sa.StaffID = ca.staff_accounts_id', 'left');
		$this->CI->db->where('ca.country_id', $defaultCountry);
		$this->CI->db->where('sa.Deleted', 0);
		$this->CI->db->where('sa.ClassID', 6);
		$this->CI->db->where('sa.active', 1);
        $this->CI->db->order_by('sa.FirstName','sa.LastName');
		$query = $this->CI->db->get();
		$row = $query->result_array();

		return $row;
	}

	//getregion
	function getQueryBySql($queryStr){
		$query = $this->CI->db->query($queryStr);
		if($query->num_rows()>0){
			return $query->result_array();
			// return true;
		}else{
			return false;
		}
	}

	function getDynamicRegionViaCountry($country_id){
		// NZ
		if($country_id==2){
			$region_str = 'District';
		}else{
			$region_str = 'Region';
		}
		return $region_str;
	}

	function getTobeBookedPostcodeViaRegion($region){
		$postcodes_arr = [];
		$sql = "SELECT `postcode_region_postcodes`
			FROM  `postcode_regions`
			WHERE `region` ={$region}
			AND  `deleted` =0
		";
		$query = $this->CI->db->query($sql);
		$query = $query->result_array();
		foreach ($query as $row) {
			if($row['postcode_region_postcodes']!=''){
				$postcodes_arr[] = explode(",",trim($row['postcode_region_postcodes']));
			}
		}

		$rejoin_arr = [];
		foreach( $postcodes_arr as $pc ){
			// remove empty
			$pc2 = array_filter($pc);
			$rejoin_arr[] = implode(",",$pc2);
		}

		return implode(",",$rejoin_arr);
	}

	function getTobeBookedSubRegionCount($country_id,$postcode,$job_type="",$job_status="",$custom_query){

		// disable this filter, it will conflict for custom query
		$str = "";
		if($custom_query==""){
			$job_status = ($job_status!="")?$job_status:'To Be Booked';
		}else{
			$custom_query_str = $custom_query;
		}

		if($job_type!=""){
			if($job_type=='cot & lr'){
				$str .= " ( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' ) ";
			}else{
				$str .= " j.job_type = '{$job_type}' ";
			}
		}

		if($job_status!=""){
			$str .= " j.`status` = '{$job_status}' ";
		}
		$str = " 1=1";
		$this->CI->db->select('count(j.`id`) AS jcount');
		$this->CI->db->from('jobs j');
		$this->CI->db->join('property p','j.property_id = p.property_id', 'left');
		$this->CI->db->join('agency a','p.agency_id = a.agency_id', 'left');
		$this->CI->db->where('p.deleted', 0);
		$this->CI->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
		$this->CI->db->where('a.status', "active");
		$this->CI->db->where('j.del_job', 0);
		$this->CI->db->where('a.country_id', $country_id);
      	$this->CI->db->where($str);
		$this->CI->db->where('p.postcode', $postcode);
		$query = $this->CI->db->get();
		$row = $query->result_array();
		return $row[0]['jcount'];
	}

	# Check dd/mm/yyyy and yyyy-dd-mm format
	function isValidDate($date)
	{
		if(stristr($date, "/"))
		{
			$tmp = explode("/", $date);

			if(checkdate($tmp[1], $tmp[0], $tmp[2]))
			{
				return true;
			}
		}

		if(stristr($date, "-"))
		{
			$tmp = explode("-", $date);

			if(checkdate($tmp[1], $tmp[2], $tmp[0]))
			{
				return true;
			}
		}

		return false;

	}

	function generateLink($params, $staff_filter = array())
	{
		$link = "<a href='?";

		$link .= "date_from_filter=" . $params['from'] . "&date_to_filter=" . $params['to']."&get_sats=1";

		if(is_int($staff_filter['staff_id'])) $link .= "&sid=" . $staff_filter['staff_id'];
		if(is_int($staff_filter['tech_id'])) $link .= "&tid=" . $staff_filter['tech_id'];

		# Close off url
		$link .= "'";

		# Add style
		if($params['css']) $link .=" style='" . $params['css']  ."' ";
		$link .= " />" . $params['title'] . "</a>";

		return $link;
	}

	public function formatDmyToYmd($date, $checkEmpty = false) {
		if (!$checkEmpty) {
			return \DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d');
		}
		else {
			return $this->isDateNotEmpty($date) ? \DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d') : '';
		}
	}

	public function formatDmyToYmdhis($date, $checkEmpty = false) {
		if (!$checkEmpty) {
			$obj = \DateTime::createFromFormat('d/m/Y', $date);
			$ci = get_instance();
			if ($ci->config->item('country') == 2) {
				$obj->add(\DateInterval::createFromDateString("P2H"));
			}
			return $obj->format('Y-m-d H:i:s');
		}
		else {
			return $this->isDateNotEmpty($date) ? \DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d H:i:s') : '';
		}
	}

	public function formatDmyhisToYmdhis($date, $checkEmpty = false) {
		if (!$checkEmpty) {
			return \DateTime::createFromFormat('d/m/Y H:i:s', $date)->format('Y-m-d H:i:s');
		}
		else {
			return $this->isDateNotEmpty($date) ? \DateTime::createFromFormat('d/m/Y H:i:s', $date)->format('Y-m-d H:i:s') : '';
		}
	}

	public function formatYmdToDmy($date, $checkEmpty = false) {
		if (!$checkEmpty) {
			return \DateTime::createFromFormat('Y-m-d', $date)->format('d/m/Y');
		}
		else {
			return $this->isDateNotEmpty($date) ? \DateTime::createFromFormat('Y-m-d', $date)->format('d/m/Y') : '';
		}
	}

	public function formatYmdhisToDmy($date, $checkEmpty = false) {
		if (!$checkEmpty) {
			return \DateTime::createFromFormat('Y-m-d H:i:s', $date)->format('d/m/Y');
		}
		else {
			return $this->isDateNotEmpty($date) ? \DateTime::createFromFormat('Y-m-d H:i:s', $date)->format('d/m/Y') : '';
		}
	}

}



?>