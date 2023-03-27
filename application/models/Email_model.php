<?php

class Email_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function get_email_templates($params) {

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = " 
				*, ett.`name` AS ett_name, et.`active` AS et_active
			";
        }

        $this->db->select($sel_str);
        $this->db->from("`email_templates` AS et");
        $this->db->join("`email_templates_type` AS ett", "et.`temp_type` = ett.`email_templates_type_id`", "LEFT");


        // filters
        $filter_arr = array();


        if ($params['active'] != "") {
            $filter_arr[] = "AND et.`active` = {$params['active']}";
        }

        if ($params['email_templates_id'] != "") {
            $filter_arr[] = "AND et.`email_templates_id` = {$params['email_templates_id']}";
        }

        if ($params['temp_type'] != "") {
            $filter_arr[] = "AND et.`temp_type` = {$params['temp_type']}";
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				bn.`notes` LIKE '%{$params['phrase']}%' OR
				a.`agency_name` LIKE '%{$params['phrase']}%'
			 )";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = substr(implode(" ", $filter_arr), 3);
            $this->db->where($filter_str);
        }



        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
            $this->db->where($custom_filter_str);
        }





        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $this->group_by($params['group_by']);
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
            }
        }

        $query = $this->db->get();

        if ($params['echo_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function get_email_template_type($params) {

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = " 
				*
			";
        }
        $this->db->select($sel_str);
        $this->db->from("`email_templates_type` AS et_type");

        // filters
        $filter_arr = array();


        if ($params['active'] != "") {
            $filter_arr[] = "AND et_type.`active` = {$params['active']}";
        }

        if ($params['email_templates_type_id'] != "") {
            $filter_arr[] = "AND et_type.`email_templates_type_id` = {$params['email_templates_type_id']}";
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				bn.`notes` LIKE '%{$params['phrase']}%' OR
				a.`agency_name` LIKE '%{$params['phrase']}%'
			 )";
        }
        // combine all filters
        if (count($filter_arr) > 0) {
//            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
            $this->db->where(substr(implode(" ", $filter_arr), 3));
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
            $this->db->where($custom_filter_str);
        }






        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $this->db->limit($params['paginate']['offset'], $params['paginate']['limit']);
            }
        }
        $query = $this->db->get();
        if ($params['echo_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function get_email_template_tag($params) {

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = " 
				*
			";
        }
        $this->db->select($sel_str);
        $this->db->from("`email_templates_tag` AS ett");

        // filters
        $filter_arr = array();


        if ($params['active'] != "") {
            $filter_arr[] = "AND ett.`active` = {$params['active']}";
        }

        if ($params['email_templates_id'] != "") {
            $filter_arr[] = "AND ett.`email_templates_tag_id` = {$params['email_templates_id']}";
        }



        /* 	
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }
         */

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				bn.`notes` LIKE '%{$params['phrase']}%' OR
				a.`agency_name` LIKE '%{$params['phrase']}%'
			 )";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $this->db->where(substr(implode(" ", $filter_arr), 3));
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
            $this->db->where($custom_filter_str);
        }






        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {

                $this->db->limit($params['paginate']['offset'], $params['paginate']['limit']);
            }
        }
        $query = $this->db->get();

        if ((int) $params['echo_query'] === 1) {
            echo $this->db->last_query();
        }
        return $query;
    }

    public function add_email_template($template_name, $subject, $temp_type, $show_to_call_centre, $et_body) {
// add template

        $this->db->insert("email_templates", [
            "`template_name`"=>$template_name,
            "`subject`"=>$subject,
            "`temp_type`"=>$temp_type,
            "`body`"=>$et_body,
            "`show_to_call_centre`"=>$show_to_call_centre
        ]);
        return $this->db->affected_rows();
    }

    public function update_email_template($template_name, $subject, $temp_type, $show_to_call_centre, $et_body, $active, $template_id) {
// add template
        if (0 === (int) $template_id) {
            return false;
        }
        $str = "
	UPDATE email_templates
	SET
		`template_name`='{$template_name}',
		`subject`='{$subject}',
		`temp_type`='{$temp_type}',
		`body`='{$et_body}',
		`show_to_call_centre`='{$show_to_call_centre}',
                `active`=$active
	WHERE email_templates_id=$template_id
        ";
        $this->db->query($str);
        return $this->db->affected_rows();
    }

    function parseEmailTemplateTags($params, $body) {

        $this->load->model('agency_model');

        $loggedin_staff_id = $this->session->staff_id;
        $loggedin_staff = $this->gherxlib->getStaffInfo([
                    "sel_query" => "sa.FirstName, sa.LastName",
                    "staff_id" => $loggedin_staff_id
                ])->row();
        $loggedin_staff_name = "{$loggedin_staff->FirstName} {$loggedin_staff->LastName}";

        // get agent, tenant number from countries
        $this->db->select("`tenant_number`,`agent_number`");
        $this->db->from("`countries`");
        $this->db->where("`country_id`", $this->config->item('country'));
        $ctn = $this->db->get()->row_array();
        //$blank = '<span class="colorItRed">BLANK</span>';
        $blank = '<span style="color:#dc3545;">BLANK</span>'; ##updated by Gherx > changed color tweak to inline in order to work on email

        /**
         * Joseph NOTE:::
         * This is a copy of old crm sats_crm_class :: parseEmailTemplateTags
         * agency is not yet converted for CI USE
         */
        if ($params['agency_id'] != '') { // agency
            // get agency data
            $jparams = array(
                'agency_id' => $params['agency_id'],
                'display_echo' => 0
            );

            ##Get Agency Details
            $sel_query = "
            a.agency_name, 
            a.address_1, a.address_2, 
            a.address_3, a.state, 
            a.postcode,
            a.account_emails, 
            a.agency_emails,
            a.phone AS a_phone
            ";
            $agency_params = array(
                'sel_query' => $sel_query,
                'country_id' => COUNTRY,
                'agency_id' => $params['agency_id']
            );
            $row = $this->agency_model->get_agency($agency_params)->row_array();

            
            /*$a_sql = $this->getAgency($jparams);
            $row = mysql_fetch_array($a_sql);*/

            $agency_address = "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}";

            // put agency emails into an array
            $agency_emails_exp = explode("\n", trim($row['agency_emails']));
            $agency_emails_imp = implode(", ", $agency_emails_exp);

            // put account emails into an array
            $account_emails_exp = explode("\n", trim($row['account_emails']));
            $account_emails_imp = implode(", ", $account_emails_exp);

            $find = array(
                "{agency_name}",
                "{tenant_phone_number}",
                "{agency_phone_number}",
                "{agency_email}",
                "{agency_accounts_email}",
                "{agency_address}"
            );
            $search = array(
                ( trim($row['agency_name']) != '') ? $row['agency_name'] : $blank,
                ( trim($ctn['tenant_number']) != '') ? $ctn['tenant_number'] : $blank,
                ( trim($row['a_phone']) != '') ? $row['a_phone'] : $blank,
                ( trim($agency_emails_imp) != '') ? $agency_emails_imp : $blank,
                ( trim($account_emails_imp) != '') ? $account_emails_imp : $blank,
                ( trim($agency_address) != '') ? $agency_address : $blank
            );

            //$subject_fin = str_replace($find, $search, $subject);
            $message_fin = str_replace($find, $search, $body);
        } else if ($params['job_id'] != '') { // jobs				
            // get jobs data
            $jparams = array(
                'job_id' => $params['job_id'],
                'remove_deleted_filter' => 1
            );
            $job = $this->system_model->getJobsData($jparams);

            $row = (array) $job[0];

            $property_id = $row['property_id'];
            $paddress = "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']} {$row['p_state']} {$row['p_postcode']}";
            $jdate = ( $this->system_model->isDateNotEmpty($row['jdate']) ) ? date('d/m/Y', strtotime($row['jdate'])) : '';

            $landlord = "{$row['landlord_firstname']} {$row['landlord_lastname']}";
            $agency_address = "{$row['a_address_1']} {$row['a_address_2']} {$row['a_address_3']} {$row['a_state']} {$row['a_postcode']}";

            $landlord_email = $row['landlord_email'];

            // put agency emails into an array
            $agency_emails_exp = explode("\n", trim($row['agency_emails']));
            $agency_emails_imp = implode(", ", $agency_emails_exp);

            // put account emails into an array
            $account_emails_exp = explode("\n", trim($row['account_emails']));
            $account_emails_imp = implode(", ", $account_emails_exp);

            $sats_google_review = "https://bit.ly/3G8PbXM";

            $find = array(
                "{agency_name}",
                "{property_address}",
                "{service_type}",
                "{job_date}",
                "{job_number}",
                "{landlord}",
                "{landlord_email}",
                "{tenant_phone_number}",
                "{agency_phone_number}",
                "{user}",
                "{tech_comments}",
                "{agency_email}",
                "{agency_accounts_email}",
                "{agency_address}",
                "{tenant_number}",
                "{agent_number}",
                "{sats_google_review}"
            );


            $search = array(
                ( trim($row['agency_name']) != '') ? $row['agency_name'] : $blank,
                ( trim($paddress) != '') ? $paddress : $blank,
                ( trim($row['type']) != '') ? $row['type'] : $blank,
                ( trim($jdate) != '') ? $jdate : $blank,
                ( trim($row['jid']) != '') ? $row['jid'] : $blank,
                ( trim($landlord) != '') ? $landlord : $blank,
                ( trim($landlord_email) != '') ? $landlord_email : $blank,
                ( trim($ctn['tenant_number']) != '') ? $ctn['tenant_number'] : $blank,
                ( trim($row['a_phone']) != '') ? $row['a_phone'] : $blank,
                ( trim($loggedin_staff_name) != '') ? $loggedin_staff_name : $blank,
                ( trim($row['tech_comments']) != '') ? $row['tech_comments'] : $blank,
                ( trim($agency_emails_imp) != '') ? $agency_emails_imp : $blank,
                ( trim($account_emails_imp) != '') ? $account_emails_imp : $blank,
                ( trim($agency_address) != '') ? $agency_address : $blank,
                ( trim($ctn['tenant_number']) != '') ? $ctn['tenant_number'] : $blank,
                ( trim($ctn['agent_number']) != '') ? $ctn['agent_number'] : $blank,
                $sats_google_review
            );


            // tenants
            $pt_params = array(
                'property_id' => $property_id,
                'active' => 1
            );
            $pt = $this->gherxlib->getNewTenantsData($pt_params);

            $pt_i = 1;
            foreach ($pt as $pt_r) {
                $pt_row = (array) $pt_r;
                $find[] = '{tenant_' . $pt_i . '}';
                if ($pt_row['tenant_firstname'] != '') {
                    $search[] = "{$pt_row['tenant_firstname']} {$pt_row['tenant_lastname']}";
                } else {
                    $search[] = $blank;
                }
                $pt_i++;
            }


            //$subject_fin = str_replace($find, $search, $subject);
            $message_fin = str_replace($find, $search, $body);
        }

        return $message_fin;
    }


    //Server Side Datatable | Email Logs START
    function all_logs($title_id,$limit,$start,$col,$dir) {   
        $query = $this->db
            ->select('logs.details, logs.created_date, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
            ->from('logs')
            ->join('staff_accounts', 'staff_accounts.StaffID=logs.created_by_staff')
            ->where('logs.title',$title_id)
            ->order_by($col,$dir)
            ->limit($limit,$start)
            ->get();
        
        if($query->num_rows()>0) {
            return $query->result(); 
        } else {
            return null;
        }
    }

    function all_logs_count($title_id) {   
        $query = $this->db
            ->select('logs.details, logs.created_date, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
            ->from('logs')
            ->join('staff_accounts', 'staff_accounts.StaffID=logs.created_by_staff')
            ->where('logs.title',$title_id)
            ->get();
        
        return $query->num_rows();  
    }
    
    function logs_search($title_id,$limit,$start,$search,$col,$dir) {
        $query = $this->db
            ->select('logs.details, logs.created_date, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
            ->from('logs')
            ->join('staff_accounts', 'staff_accounts.StaffID=logs.created_by_staff')
            ->where('logs.title',$title_id)
            ->group_start()
                ->like('logs.details',$search)
                ->or_like('staff_accounts.FirstName',$search)
                ->or_like('staff_accounts.LastName',$search)
                ->or_like('logs.created_date',$search)
            ->group_end()
            ->limit($limit,$start)
            ->order_by($col,$dir)
            ->get();
        if($query->num_rows()>0) {
            return $query->result();  
        } else {
            return null;
        }
    }
    
    function logs_search_count($title_id,$search) {
        $query = $this->db
            ->select('logs.details, logs.created_date, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
            ->from('logs')
            ->join('staff_accounts', 'staff_accounts.StaffID=logs.created_by_staff')
            ->where('logs.title',$title_id)
            ->group_start()
                ->like('logs.details',$search)
                ->or_like('staff_accounts.FirstName',$search)
                ->or_like('staff_accounts.LastName',$search)
                ->or_like('logs.created_date',$search)
            ->group_end()
            ->limit($limit,$start)
            ->order_by($col,$dir)
            ->get();
        return $query->num_rows();
    } 
    //Server Side Datatable | Email Logs END

    /** get emails template type **/
    public function get_emails_category()
    {
        $this->db->select("ett.*");
        $this->db->from("email_templates_type as ett");
        $this->db->where("ett.active", 1);
        $this->db->order_by("name", "asc");

        return $this->db->get()->result();
    }

    /** get emails template type **/
    public function get_emails_templates_by_temp_type_id($id)
    {
        $this->db->select("et.*");
        $this->db->from("email_templates as et");
        $this->db->join("email_templates_type as ett", "ett.email_templates_type_id = et.temp_type", "left");
        $this->db->where("et.temp_type", $id);
        $this->db->where("et.active", 1);
        $this->db->order_by("name", "asc");

        return $this->db->get()->result();
    }
}
