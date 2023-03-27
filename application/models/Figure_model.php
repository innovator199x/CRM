<?php

class Figure_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    // get Daily Figures data by Date
    public function getDailyFiguresPerDate($date) {

        $this->db->select('*');
        $this->db->from('daily_figures_per_date');
        $this->db->where('date', $date);
        $this->db->where('country_id', $this->config->item('country'));
        return $this->db->get();
    }

    public function getDailyFigures($date) {

        $this->db->select('*');
        $this->db->from('daily_figures');
        $this->db->where('month', $date);
        $this->db->where('country_id', $this->config->item('country'));
        return $this->db->get();
    }

    // get Sales
    public function jGetSales($params = []) {

        $date = $params['date'];
        $exc_ub_os = $params['exc_ub_os'];
        $ub_os_only = $params['ub_os_only'];
        $exc_ic_up = $params['exc_ic_up'];
        $ic_up_only = $params['ic_up_only'];
        $return_count = $params['return_count'];

        $exc_ub_os_filter = null;
        if( $exc_ub_os == true ){
            $exc_ub_os_filter = "AND j.`assigned_tech` NOT IN(1,2)";
        }

        $ub_os_only_filter = null;
        if( $ub_os_only == true ){
            $ub_os_only_filter = "AND j.`assigned_tech` IN(1,2)";
        }

        $exc_ic_up_filter = null;
        if( $exc_ic_up == true ){
            $exc_ic_up_filter = "AND j.`job_type` != 'IC Upgrade'";
        }

        $ic_up_only_filter = null;
        if( $ic_up_only == true ){
            $ic_up_only_filter = "AND j.`job_type` = 'IC Upgrade'";
        }

        $sel_sql_str = null;
        if( $return_count == true ){
            $sel_sql_str = "SELECT COUNT(j.`id`) AS jcount";
        }else{
            $sel_sql_str = "SELECT SUM(j.`job_price`) AS jprice"; 
        }

        //Gherx: exclude dha filter
        $exclude_dha_filter = null;
        if( $exclude_dha == true ){
            $exclude_dha_filter = "AND a.`franchise_groups_id` != '14";
        }

        // date removed
        // AND j.`date` = '{$date}' 
        // job price
        $sql = $this->db->query("
			{$sel_sql_str}
			FROM `jobs` AS j 
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
			WHERE p.`deleted` =0 
			AND a.`status` = 'active' 
			AND j.`del_job` = 0 
			AND a.`country_id` = {$this->config->item('country')}	
			AND j.`status` = 'Merged Certificates' 
            {$exc_ub_os_filter}
            {$ub_os_only_filter}
            {$exc_ic_up_filter}
            {$ic_up_only_filter}
            {$exclude_dha_filter}
		");

        $row = $sql->row_array();
        $tot_job_price = $row['jprice'];

        // alarm price
        $sql2 = $this->db->query("
				SELECT SUM(alrm.`alarm_price`) AS aprice
				FROM `alarm` AS alrm 
				LEFT JOIN `jobs` AS j ON  alrm.`job_id` = j.`id` 
				LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
				LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
				WHERE p.`deleted` =0 
				AND a.`status` = 'active' 
				AND j.`del_job` = 0 
				AND a.`country_id` = {$this->config->item('country')}
				AND j.`status` = 'Merged Certificates' 
				AND alrm.`new`	= 1
				AND alrm.`ts_discarded` = 0
                {$exc_ub_os_filter}
                {$ub_os_only_filter}
                {$exclude_dha_filter}
			");

        $row2 = $sql2->row_array();
        $tot_alarm_price = $row2['aprice'];

        if( $return_count == true ){
            return $sql->row()->jcount;
        }else{
            $final_total = $tot_job_price + $tot_alarm_price;
            return number_format($this->system_model->price_ex_gst($final_total),2,'.','');
        }
        
    }

    // get Number of Techs Today
    public function jGetNumOfTechToday($date) {

        $sql = $this->db->query("
			SELECT *
			FROM  `tech_run` 
			WHERE `date` = '{$date}'
			AND `country_id` = {$this->config->item('country')}
			GROUP BY `assigned_tech`
		");
        return $sql->num_rows();
    }

    // get today's number of jobs completed or merged 
    public function jGetNumJobsCompleted($params = []) {

        $date = $params['date'];
        $exc_ub_os = $params['exc_ub_os'];        

        $exc_ub_os_filter = null;
        if( $exc_ub_os == true ){
            $exc_ub_os_filter = "AND j.`assigned_tech` NOT IN(1,2)";
        }

        // date removed
        // AND j.`date` = '{$date}' 

        $sql = $this->db->query("
			SELECT COUNT(j.`id`) AS jcount 
			FROM `jobs` AS j 
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
			WHERE j.`status` = 'Merged Certificates' 			
			AND a.`country_id` = {$this->config->item('country')} 
			AND p.`deleted` = 0 
			AND a.`status` = 'active' 
			AND j.`del_job` =0 
            {$exc_ub_os_filter}
		");

        $row = $sql->row_array();
        return $row['jcount'];
    }

    //Update daily_figures_per_date
    public function update_daily_figures_per_date($dfpd_id, $data) {

        $this->db->where('daily_figure_per_date_id', $dfpd_id);
        $this->db->update('daily_figures_per_date', $data);
        $this->db->limit(1);
    }

    //add daily_figures_per_date
    public function insert_daily_figures_per_date($data) {

        $this->db->insert('daily_figures_per_date', $data);
    }

    //Update daily_figures
    public function update_daily_figures($dfpd_id, $data) {

        $this->db->where('daily_figure_id', $dfpd_id);
        $this->db->update('daily_figures', $data);
        $this->db->limit(1);
    }

    //add/insert daily_figures
    public function insert_daily_figures($data) {

        $this->db->insert('daily_figures', $data);
    }

    // get Service with interconnected smoke alarms
    public function getICService() {
        return $this->system_model->getICService();
    }

    /**
     * Gherx: updated > exclude DHA 
     */
    public function getIcUpgradeTotal($params) {

        $ic_service = $params['ic_service'];
        $ic_service_imp = implode(',', $ic_service);

        $filter = '';
        if ($params['date_range'] != '') {
            $filter .= " AND j.`date` BETWEEN '{$params['date_range']['from']}' AND '{$params['date_range']['to']}' ";
        } else {
            $filter .= " AND j.`date` = '" . date('Y-m-d') . "' ";
        }

        $sql_str = "
			SELECT j.`job_price`, SUM(al.`alarm_price`) AS alarm_tot, am.`price` AS am_price
			FROM `jobs` AS j 
			INNER JOIN `alarm` AS al ON  j.`id` = al.`job_id`
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `agency_maintenance` AS am ON ( a.`agency_id` = am.`agency_id` AND am.`surcharge` = 1 )
			WHERE al.`new` = 1
			{$filter}
			AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )		
			AND a.`country_id` = {$this->config->item('country')}
			AND j.`job_type` = 'IC Upgrade'
            AND a.franchise_groups_id!=14
		";

        return $this->db->query($sql_str);
    }

    public function getTotalPaymentsAndCredits() {

        /*$ip_total = $this->db->query("SELECT SUM(ip.amount_paid) AS inv_pay_tot 
                FROM invoice_payments ip WHERE ip.job_id>0 AND ip.payment_date=CURRENT_DATE()")->row()->inv_pay_tot;*/

        //Gherx > exclude DHA
        $ip_total = $this->db->query("
        SELECT SUM(ip.amount_paid) AS inv_pay_tot 
        FROM invoice_payments ip
        LEFT JOIN jobs AS j ON ip.job_id =  j.id 
        LEFT JOIN property AS p ON j.property_id =  p.property_id 
        LEFT JOIN agency AS a ON p.agency_id =  a.agency_id 
        WHERE ip.job_id>0 
        AND ip.payment_date=CURRENT_DATE()
        AND a.franchise_groups_id != 14
        ")->row()->inv_pay_tot;

        
        /*$ic_total = $this->db->query("SELECT SUM(ic.credit_paid) AS inv_cred_tot 
                FROM invoice_credits ic WHERE ic.job_id>0 AND ic.credit_date=CURRENT_DATE()")->row()->inv_cred_tot;*/

        $ic_total = $this->db->query("
        SELECT SUM(ic.credit_paid) AS inv_cred_tot
        FROM invoice_credits ic 
        LEFT JOIN jobs AS j ON ic.job_id =  j.id 
        LEFT JOIN property AS p ON j.property_id =  p.property_id 
        LEFT JOIN agency AS a ON p.agency_id =  a.agency_id 
        WHERE ic.job_id>0 
        AND ic.credit_date=CURRENT_DATE()
        AND a.franchise_groups_id != 14
        ")->row()->inv_cred_tot;

        return ['inv_pay_tot' => $ip_total, 'inv_cred_tot' => $ic_total];
    }

    /* old one and duplicate function from reports_model.php
      public function kpi_getTotalPropertyCount($country_id){

      $fg = 14; // Defence Housing
      //$fg_filter = "AND a.`franchise_groups_id` != {$fg}";

      return $this->db->query("
      SELECT DISTINCT p.`property_id`
      FROM `property_services` AS ps
      LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
      LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
      WHERE ps.`service` =1
      AND p.`deleted` =0
      AND a.`status` = 'active'
      AND a.`country_id` = {$country_id}
      {$fg_filter}
      ");
      }

      public function kpi_getTotalPropertyCount_v2(){

      $fg = 14; // Defence Housing
      //$fg_filter = "AND a.`franchise_groups_id` != {$fg}";

      return $this->db->query("
      SELECT COUNT(DISTINCT p.`property_id`) as prop_count
      FROM `property_services` AS ps
      LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
      LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
      WHERE ps.`service` =1
      AND p.`deleted` =0
      AND a.`status` = 'active'
      AND a.`country_id` = {$this->config->item('country')}
      {$fg_filter}
      ");
      }
     */

    public function get_figures($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('figures');
        $this->db->where('country_id', $this->config->item('country'));

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }

        $query = $this->db->get();

        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

}
