<?php

class Home extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model('resources_model');
        //$this->load->library('pagination');
        $this->load->model('stock_model');
        $this->load->model('users_model');
    }
	
    public function index(){

        $this->load->model('crmtasks_model');
        $this->load->model('expensesummary_model');
        $this->load->model('booking_model');
        $this->load->model('staff_accounts_model');

        //$data['start_load_time'] = microtime(true);
        $data['title'] = "Home";

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $staff_class = $this->system_model->getStaffClassID();
        $today = date('Y-m-d');

        ##get staff_accounts
        $staff_params = array('staff_id'=>$staff_id);
        $data['staff'] = $this->staff_accounts_model->get_staff_accounts($staff_params)->row_array();

        if( $staff_class == 6 ){ // tech
            
            $this->index_tech();

        }else{

            $tt_num_rows = $this->users_model->check_home_content_block_users_block($staff_id);

            $tt_rr = array();
            $tt_rr2 = array();

            if( $tt_num_rows<=0 ){ //display default blocks per user_class

                //default query to home_content_block_class_access filtered user class

                ##category 1 query -----------------------
                $params = array(
                    'staff_class' => $staff_class,
                    'category' => 1
                );
                $home_content_block_class_access_q = $this->_home_content_block_class_access($params);
               
                foreach( $home_content_block_class_access_q as $row ){
                    $tt_rr[] = $row['content_block_id'];
                }
                $data['tt_rr'] = $tt_rr;

                ##echo $this->db->last_query();exit();

                ##category 2 query -----------------------
                $params2 = array(
                    'staff_class' => $staff_class,
                    'category' => 2
                );
                $home_content_block_class_access_q2 = $this->_home_content_block_class_access($params2);
               
                foreach( $home_content_block_class_access_q2 as $row ){
                    $tt_rr2[] = $row['content_block_id'];
                }
                $data['tt_rr2'] = $tt_rr2;

           // echo $this->db->last_query();exit();
             

            }else{

                //query to home_content_block_users_block
                ##category 1 query-----------------
                $params = array(
                    'staff_id' => $staff_id,
                    'staff_class' => $staff_class,
                    'category' => 1
                );
                $home_content_block_users_block_q = $this->_get_home_content_block_users_block($params);
                foreach( $home_content_block_users_block_q as $row ){
                    $tt_rr[] = $row['content_block_id'];
                }
                $data['tt_rr'] = $tt_rr;

                ##category 2 query----------------
                $params2 = array(
                    'staff_id' => $staff_id,
                    'staff_class' => $staff_class,
                    'category' => 2
                );
                $home_content_block_users_block_q_2 = $this->_get_home_content_block_users_block($params2);
                foreach( $home_content_block_users_block_q_2 as $row ){
                    $tt_rr2[] = $row['content_block_id'];
                }
                $data['tt_rr2'] = $tt_rr2;
               
            }

            //get recent tickets------------------------------
            $custom_select_recent_tickets = '
            ct.`crm_task_id`,        
            ct.`date_created`,
            ct.`page_link`,
            ct.`describe_issue`,
            ct.`response`,
            ct.`status` AS ct_status,
            ct.`issue_summary`,
            ct.`help_topic`,
            ct.`ticket_priority`,
            ct.`completed_ts`,
            ct.`last_updated_ts`,
    
            ctht.`help_topic` AS ctht_help_topic,
            
            cts.`status` AS cts_status,
            cts.`hex`,
    
            rb.`StaffID`,
            rb.`FirstName`,
            rb.`LastName`,
            ';

            $custom_where_arr[] = "ct.`status` NOT IN(4,2,7)"; // exclude Completed, Declined and Unable to Replicate
            $params_recent_tickets = array(
                'custom_select' => $custom_select_recent_tickets,
                'active' => 1,                
                'custom_where_arr' => $custom_where_arr,     
                'sort_list' => array(
                    array(
                        'order_by' => 'ct.`date_created`',
                        'sort' => 'DESC'
                    )
                ),
                'paginate' => array(
                    'offset' => 0,
                    'limit' => 5
                ),
                'echo_query' => 0
            );
            $data['recent_tickets'] =  $this->crmtasks_model->getButtonCrmTasks($params_recent_tickets);   
            //get recent tickets end

            //get cars-----------------------------
            $vehicles = $this->db->query("
					SELECT *
					FROM `vehicles` AS v
					LEFT JOIN `staff_accounts` AS s ON v.`StaffID` = s.`StaffID`
					WHERE `country_id` = {$this->config->item('country')}
					AND v.`active` = 1
				")->result_array();
            
            $vehiclesById = [];

            for ($x = 0; $x < count($vehicles); $x++) {
                $vehicle =& $vehicles[$x];
                $vehicle['kms'] = 0;

                $vehiclesById[$vehicle['vehicles_id']] =& $vehicle;
            }
            if (!empty($vehicles)) {

                $vehicleIds = array_keys($vehiclesById);
				$vehicleIdsString = implode(',', $vehicleIds);

                $kms = $this->db->query("
                    SELECT
                        kms.vehicles_id,
                        kms.kms,
                        kms.kms_updated
                    FROM `kms`
                    INNER JOIN (
                        SELECT vehicles_id, MAX(kms_updated) AS kms_updated
                        FROM kms
                        WHERE kms.`vehicles_id` IN ({$vehicleIdsString})
                        GROUP BY vehicles_id
                    ) AS k2 ON k2.vehicles_id = kms.vehicles_id AND k2.kms_updated = kms.kms_updated
                ")->result_array();

            }

            foreach ($kms as $km) {
                $vehiclesById[$km['vehicles_id']]['kms'] = $km;
            }
            $data['vehicles'] = $vehicles;
            //get cars end

            //leave request ------------------------------
            $sel_query_leave_req = "
                l.leave_id,
                l.`date`,
                l.type_of_leave,

                sa_emp.`StaffID` AS emp_staff_id,
                sa_emp.`FirstName` AS emp_fname,
                sa_emp.`LastName` AS emp_lname
            ";
            $params_leave_req = array(
                'sel_query' => $sel_query_leave_req,
                'l_status' => 'Pending',
                'country_id' => COUNTRY,
                'limit' => 20,
                'offset' => 0,
                'sort_list' => array(
                    array(
                        'order_by' => 'l.date',
                        'sort' => 'DESC',
                    ),
                ),
            );
            $data['lists_leave_req'] = $this->users_model->getLeave($params_leave_req);
            //leave request end

            //Expense summary ----------------------
            $sel_query_expense = "
                exp_sum.expense_summary_id,
                exp_sum.date,
                exp_sum.total_amount,
                sa.`FirstName` AS sa_fname,
                sa.`LastName` AS sa_lname,";
            $params_expense = array(
                'sel_query' => $sel_query_expense,
                'paginate' => array(
                    'offset' => 0,
                    'limit' => 20
                ),
                'sort_list' => array(
                    array(
                        'order_by' => 'exp_sum.date_created',
                        'sort' => 'DESC'
                    )
                ),
                'country_id' => COUNTRY,
                ##'exp_sum_status' => '-1',
                'date_reimbursed_is_null' => 1,
                'group_by' => 'exp_sum.expense_summary_id',
                'echo_query' => 0
            );
            $data['expense_summary'] = $this->expensesummary_model->getButtonExpenseSummary($params_expense);
            //Expense summary end ----------------------

            //Get Staff Date -------------------------
            ##BDAY
            $dsql = $this->db->query("
				SELECT sa.StaffID, sa.FirstName, sa.LastName, sa.dob
				FROM `staff_accounts` AS sa
                INNER JOIN `country_access` AS `ca` ON sa.`StaffID` = ca.`staff_accounts_id`
				WHERE sa.`dob` IS NOT NULL
				AND sa.`dob` != '0000-00-00'
				AND DATE_FORMAT(sa.`dob`, '%m%d') >= DATE_FORMAT( NOW(), '%m%d' )
				AND DATE_FORMAT(sa.`dob`, '%m%d') <=  DATE_FORMAT( DATE_ADD(NOW(), INTERVAL 15 DAY), '%m%d' )
				AND sa.`active` = 1
                AND ca.country_id = {$this->config->item('country')}
				ORDER BY DATE_FORMAT(sa.`dob`, '%m%d') ASC
				");
            $data['staff_dates'] = $dsql;

            ##Anniv
            $anivsql = $this->db->query("
                SELECT sa.StaffID, sa.FirstName, sa.LastName, sa.start_date
                FROM `staff_accounts` AS sa
                INNER JOIN `country_access` AS `ca` ON sa.`StaffID` = ca.`staff_accounts_id`
                WHERE sa.`start_date` IS NOT NULL
                AND sa.`start_date` != '0000-00-00'
                AND DATE_FORMAT(sa.`start_date`, '%m%d') >= DATE_FORMAT( NOW(), '%m%d' )
                AND DATE_FORMAT(sa.`start_date`, '%m%d') <=  DATE_FORMAT( DATE_ADD(NOW(), INTERVAL 15 DAY), '%m%d' )
                AND sa.`active` = 1
                AND ca.country_id = {$this->config->item('country')}
                ORDER BY DATE_FORMAT(sa.`start_date`, '%m%d') ASC
                ");
            $data['staff_anniv'] = $anivsql;
            
            ## Blue Card expiry
            $blue_card_sql = $this->db->query("
					SELECT * , DATEDIFF( sa.`blue_card_expiry` , '{$today}' ) AS  'rem_days'
					FROM `staff_accounts` as sa
                    INNER JOIN `country_access` AS `ca` ON sa.`StaffID` = ca.`staff_accounts_id`
					WHERE sa.`blue_card_expiry` IS NOT NULL
					AND sa.`blue_card_expiry` != '0000-00-00'
					AND  '{$today}' >= DATE_SUB( sa.`blue_card_expiry` , INTERVAL 15 DAY )
                    AND ca.country_id = {$this->config->item('country')}
				");
            $data['blue_card'] = $blue_card_sql;

            ##Licence Expiry
            $license_exp_str = "
					SELECT * , DATEDIFF( sa.`licence_expiry` , '{$today}' ) AS  'rem_days'
					FROM `staff_accounts` AS sa
                    INNER JOIN `country_access` AS `ca` ON sa.`StaffID` = ca.`staff_accounts_id`
					WHERE sa.`licence_expiry` IS NOT NULL
					AND sa.`licence_expiry` != '0000-00-00'
					AND  '{$today}' >= DATE_SUB( sa.`licence_expiry` , INTERVAL 15 DAY )
					AND sa.`active` = 1
                    AND ca.country_id = {$this->config->item('country')}
				";
            $data['license_exp'] = $this->db->query($license_exp_str);

            ##Electrical Licence Expiry
            $electrical_licence_str = "
                SELECT * , DATEDIFF( sa.`elec_licence_expiry` , '{$today}' ) AS  'rem_days'
                FROM `staff_accounts` AS sa
                INNER JOIN `country_access` AS `ca` ON sa.`StaffID` = ca.`staff_accounts_id`
                WHERE sa.`elec_licence_expiry` IS NOT NULL
                AND sa.`elec_licence_expiry` != '0000-00-00'
                AND  '{$today}' >= DATE_SUB( sa.`elec_licence_expiry` , INTERVAL 30 DAY )
                AND sa.`active` = 1
                AND ca.country_id = {$this->config->item('country')}
            ";
            $data['electrical_licence'] = $this->db->query($electrical_licence_str);

            ##Notice Board
            $notice_board_sql = $this->db->query("
				SELECT *
				FROM `noticeboard`
				WHERE `country_id` = {$this->config->item('country')}
			");
            $data['notice_board'] = $notice_board_sql->row_array();

            //Get Staff Date end -------------------------

            //Booking Sched ----------------------
            $str_sql = $this->db->query("
                SELECT `booking_schedule_num` 
                FROM `staff_accounts`
                WHERE `StaffID`={$this->session->staff_id}");
            $str_row = $str_sql->row();

            if( $str_row->booking_schedule_num > 0){
                $num_days = $str_row->booking_schedule_num;
            } else {
                $num_days = (!empty($num_days) && $num_days >= 0) ? $num_days : 14;
            }

            $data['num_days'] = $num_days;
            $data['tech_runs'] = $this->booking_model->get_tech_with_runs();
            $data['run_dates'] = $this->booking_model->get_tech_run_dates();
            $data['run_status'] = $this->booking_model->get_run_status();

            //get states
            $params = (object)["get_states" => true];
            $data['states'] = $this->booking_model->get_tech_runs($params);
            $params = (object)["get_states" => false];

            $booking = $this->booking_model->get_booking_schedule_num_days();

            // get schedule
            $num_days = (!empty($num_days) && $num_days >= 0) ?
                            $num_days :
                            ($booking->schedule_num_days > 0 ? $booking->schedule_num_days : 14);

            $data['num_days'] = $num_days;
            //Booking Sched end----------------------

            $this->load->view('templates/inner_header', $data);
            $this->load->view('/home/index', $data);
            $this->load->view('templates/inner_footer', $data);

        }

    }


    public function index_tech(){

        $this->load->model('tech_model');
        
        //$data['start_load_time'] = microtime(true);
        $data['title'] = "Tech Home";
        
        $tr_id_query = $this->tech_model->get_tech_run_id_by_date()->row_array();
        $data['tr_id'] = $tr_id_query['tech_run_id'];        

        $this->load->view('templates/inner_header_tech', $data);
        $this->load->view('/home/index_tech', $data);
        $this->load->view('templates/inner_footer_tech', $data);        

    }


    public function ajax_save_tech_stocktake(){

        $this->load->model('tech_model');

        $tech_stock_id = $this->input->post('tech_stock_id');
        $stocks_id_arr = $this->input->post('stocks_id_arr');
        $stock_qty_arr = $this->input->post('stock_qty_arr');
        
        $stocks_id_imp = null;
        if( count($stocks_id_arr) > 0 ){
            $stocks_id_imp = implode(",",$stocks_id_arr);
        }        

        $staff_id = $this->session->staff_id;        
        $country_id = $this->config->item('country');
        $today = date("Y-m-d H:i:s");

        if( $tech_stock_id > 0 ){ // existing tech stocktake

            if( $stocks_id_imp != '' ){

                
                // clear stocktake data
                $clear_stocktake_sql_str = "
                DELETE 
                FROM `tech_stock_items`
                WHERE `tech_stock_id` = {$tech_stock_id}
                AND `stocks_id` IN($stocks_id_imp)
                ";
                $this->db->query($clear_stocktake_sql_str);
                

                foreach($stocks_id_arr as $index => $stock_id){

                    // re-insert tech stock items
                    $tech_stock_params = array(
                        'tech_stock_id' => $tech_stock_id,
                        'stocks_id' => $stock_id,
                        'quantity' => $stock_qty_arr[$index],
                        'status' => 1
                    );
                    $this->db->insert('tech_stock_items', $tech_stock_params);
                
                }

                // update tech stock date too
                $update_data = array('date' => $today);                
                $this->db->where('tech_stock_id', $tech_stock_id);
                $this->db->update('tech_stock', $update_data);

            }            

        }else{ // new


            // inser new tech stock
            $insert_data = array(
                'staff_id' => $staff_id,
                'date' => $today,
                'status' => 1,
                'country_id' => $country_id
            );
            $this->db->insert('tech_stock', $insert_data);
            $tech_stock_id = $this->db->insert_id();

            foreach($stocks_id_arr as $index => $stock_id){

                // insert tech stock items
                $tech_stock_params = array(
                    'tech_stock_id' => $tech_stock_id,
                    'stocks_id' => $stock_id,
                    'quantity' => $stock_qty_arr[$index],
                    'status' => 1
                );
                $this->db->insert('tech_stock_items', $tech_stock_params);
            
            }

        }    
                              

    }

    public function homepage_settings(){

        $this->load->model('staff_accounts_model');
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Home Page Settings";

        $staff_id = $this->session->staff_id;

        $params = array( 
            'sel_query' => '
                sa.`StaffID`,
				sa.`ClassID`
            ',
            'staff_id' => $staff_id,
			'display_query' => 0
		);

        $staff_accounts_sql = $this->staff_accounts_model->get_staff_accounts($params);
        $staff_row = $staff_accounts_sql->row();
        $staff_class = $staff_row->ClassID;

        //get data from home_content_block_class_access
        /*$this->db->select('hcb.content_block_id, hcb.content_name, hcbca.user_class');
        $this->db->from('home_content_block_class_access AS hcbca');
        $this->db->join('`home_content_block` AS hcb', 'hcb.`content_block_id` = hcbca.`content_block_id`', 'left');
        $this->db->join('`home_content_block_users_block` AS hcbub', 'hcbub.`content_block_id` = hcb.`content_block_id`', 'left');
        $this->db->where('hcbca.user_class', $staff_class);
        $this->db->where('hcbub.user_id', $staff_id);
        $this->db->order_by('hcbub.sort','DESC');
        $data['res'] = $this->db->get();*/

        ## Query fo small box
        $tt_query_a = $this->db->query("
            SELECT `hcb`.`content_block_id`, `hcb`.`content_name`, `hcbca`.`user_class`, tt.sort
            FROM `home_content_block_class_access` AS `hcbca` 
            LEFT JOIN `home_content_block` AS `hcb` ON hcb.`content_block_id` = hcbca.`content_block_id` 
            LEFT JOIN (
                SELECT content_block_id, user_id, sort FROM home_content_block_users_block
                WHERE user_id = {$staff_id}
            ) AS tt ON hcb.content_block_id = tt.content_block_id
            WHERE `hcbca`.`user_class` = {$staff_class} 
            AND `hcb`.`category` = 1
            ORDER BY tt.`sort`,hcb.content_name ASC
        ");
        $data['res'] = $tt_query_a;
        
        ## Query fo large box
        $tt_query_a2 = $this->db->query("
            SELECT `hcb`.`content_block_id`, `hcb`.`content_name`, `hcbca`.`user_class`, tt.sort
            FROM `home_content_block_class_access` AS `hcbca` 
            LEFT JOIN `home_content_block` AS `hcb` ON hcb.`content_block_id` = hcbca.`content_block_id` 
            LEFT JOIN (
                SELECT content_block_id, user_id, sort FROM home_content_block_users_block
                WHERE user_id = {$staff_id}
            ) AS tt ON hcb.content_block_id = tt.content_block_id
            WHERE `hcbca`.`user_class` = {$staff_class} 
            AND `hcb`.`category` = 2
            ORDER BY tt.`sort`,hcb.content_name ASC
        ");
        $data['res2'] = $tt_query_a2;

        //get data from home_content_block_users_block table 
        $this->db->select('*');
        $this->db->from('home_content_block_users_block');
        $this->db->where('user_id', $staff_id);
        $home_content_block_users_block_q = $this->db->get()->result_array();
        foreach( $home_content_block_users_block_q as $row ){
            $tt_rr[] = $row['content_block_id'];
        }
        $data['tt_rr'] = $tt_rr;

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('/home/homepage_settings', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_add_users_block(){

        $jdata['status'] = false;
        $block_id = $this->input->post('block_id');
        $sort_val = $this->input->post('sort_val');

        ## Get staff class
        $params_staff_class = array( 
            'sel_query' => '
                sa.`StaffID`,
				sa.`ClassID`
            ',
            'staff_id' => $this->session->staff_id,
			'display_query' => 0
		);
        $staff_accounts_sql = $this->staff_accounts_model->get_staff_accounts($params_staff_class);
        $staff_row = $staff_accounts_sql->row();
        $staff_class = $staff_row->ClassID;
        ## Get staff class end

        if( !empty($block_id) ){

            ##clear home_content_block_users_block by user_id first 
            $this->db->where('user_id', $this->session->staff_id);
            $this->db->delete('home_content_block_users_block');

            $ttcnt = 1;
            foreach( $block_id as $index=>$item ){
                if( is_numeric($item) && $item!="" ){

                    if( $this->_check_if_class_has_block_access($staff_class, $item)>0 ){ ##Check if the block is assign to correct class

                        ##add new block per user id
                        $insert_data = array(
                            'user_id' => $this->session->staff_id,
                            'content_block_id' => $item,
                           // 'sort' => $sort_val[$index]
                            'sort' => $ttcnt
                        );
                        $this->db->insert('home_content_block_users_block', $insert_data);

                    }

                }
                $ttcnt++;
            }

            $jdata['status'] = true;

        }

        echo json_encode($jdata);

    }

    private function _check_if_class_has_block_access($class_id, $block_id){
        $this->db->select('COUNT(id) as q_count');
        $this->db->from('home_content_block_class_access');
        $this->db->where('user_class', $class_id);
        $this->db->where('content_block_id', $block_id);
        $count = $this->db->get()->row()->q_count;
        return $count;
    }

    public function assign_user_class_block(){

        if( !in_array( $this->session->staff_id, $this->config->item('allow_to_edit_user_class_block') ) ){
            show_404();
        }

        $this->load->model('staff_accounts_model');
        $data['title'] = "Update/Assign User Class Block";

        $staff_id = $this->session->staff_id;

        $this->db->select('*');
        $this->db->from('home_content_block');
        $this->db->order_by('content_name', 'ASC');
        $data['home_content_block_list'] = $this->db->get();


        $this->db->select('*');
        $this->db->from('staff_classes');
        $this->db->where('active',1);
        $data['staff_class_list'] = $this->db->get();
      
        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('/home/assign_user_class_block', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_assign_user_class_block(){
        $jdata['status'] = false;
        $class_id = $this->input->post('class_id');
        $block_id = $this->input->post('block_id');

        if( $class_id!="" && $block_id!="" ){

            //clear user class block first
            $this->db->where('user_class', $class_id);
            $this->db->delete('home_content_block_class_access');

            foreach( $block_id as $index=>$item ){
                if( is_numeric($item) && $item!="" ){
                    ##assign block to per use class
                    $insert_data = array(
                        'user_class' => $class_id,
                        'content_block_id' => $item
                    );
                    $this->db->insert('home_content_block_class_access', $insert_data);
                }
            }

            $jdata['status'] = true;

        }

        echo json_encode($jdata);

    }

    public function ajax_get_goal_count_data(){

        $data = array();
        $exclude = "'upgrades-brooks', 'upgrades-cavius', 'upgrades-emerald'";
        $data['result'] = $this->db->query("SELECT `name`,`total_goal` FROM `main_page_total` WHERE `name` NOT IN($exclude)");

        $this->load->view('/home/ajax_get_goal_count_data', $data);

    }

    public function ajax_save_goal_count_data(){

        $post = $this->input->post();

        if( !empty($post) ){

            foreach( $post as $key => $value ){ ##Loop

                if( $value>=0 ){ ## Prevent negative value

                    $main_page_total_row = $this->db->select('*')->from('main_page_total')->where('name', $key)->get()->row_array();

                    if( $main_page_total_row['total_goal']!=$value ){ ## Update only if has value changes

                        $update_data = array(
                            'total_goal' => $value
                        );
                        $this->db->where('name',$key);
                        $this->db->update('main_page_total', $update_data);

                    }

                }

            }

        }

    }

    private function _get_home_content_block_users_block($params){

        $this->db->select('*');
        $this->db->from('home_content_block_users_block AS hcbub');
        $this->db->join('home_content_block_class_access AS hcbca','hcbca.content_block_id = hcbub.content_block_id' ,'INNER');
        $this->db->join('home_content_block AS hcb','hcbca.content_block_id = hcb.content_block_id' ,'left');
        $this->db->where('hcbub.user_id', $params['staff_id']);
        $this->db->where('hcbca.user_class', $params['staff_class']);

        if( $params['category'] && $params['category']!="" ){
            $this->db->where('hcb.category', $params['category']);
        }

        $this->db->group_by('hcbub.content_block_id');
        $this->db->order_by('hcbub.sort','ASC');
        $this->db->order_by('hcb.content_name','ASC');
        $q = $this->db->get()->result_array();
        return $q;

    }

    private function _home_content_block_class_access($params){

        $this->db->select('*');
        $this->db->from('home_content_block_class_access AS hcbca');
        $this->db->join('home_content_block AS hcb','hcbca.content_block_id = hcb.content_block_id' ,'left');
        $this->db->where('hcbca.user_class', $params['staff_class']);
        $this->db->order_by('hcb.content_name', 'ASC');

        if( $params['category'] && $params['category']!="" ){
            $this->db->where('hcb.category', $params['category']);
        }

        $q = $this->db->get()->result_array();
        return $q;
        
    }

    public function sort_homepage_settings_block(){

        $post = $this->input->post('serialsDict');
       
        foreach( $post as $row ){
            ##$oldData = $row['oldData'];
            $new_data = $row['newData'];
            $content_block_id = $row['content_block_id'];

            $this->db->where('content_block_id', $content_block_id);
            $this->db->where('user_id', $this->session->staff_id);
            $update_data = array('sort'=> $new_data);
            $this->db->update('home_content_block_users_block', $update_data);
        }

    }

    public function ajax_check_home_content_block_users_block_count(){
        $row_count = $this->users_model->check_home_content_block_users_block($this->session->staff_id);
        echo $row_count;
    }


}

?>