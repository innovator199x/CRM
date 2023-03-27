<?php

class Accounts extends CI_Controller {

    public function __construct() {
        parent::__construct();
//$this->load->database();
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->model('/inc/user_class_model');
        $this->load->model('/inc/activity_functions_model');
        $this->load->model('/inc/pdf_template');
        $this->load->model('statements_model');
        $this->load->model('accountslogs_model');
    }

    public function index() {
//        $data['title'] = "Reports";
//
//        $tester_arr = $this->system_model->tester();
//        $tester_appended = $tester_arr;
////$tester_appended[] = 2056; // Robert Bell 
////$tester_appended[] = 2175; // Thalia Paki 
//
//        $data['testers_arr'] = $tester_appended;
//
//        $this->load->view('templates/inner_header', $data);
//        $this->load->view('reports/index', $data);
//        $this->load->view('templates/inner_footer', $data);
    }

    public function view_statements() {

        $this->load->model('remittance_model');
        $this->load->model('agency_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "All Statements";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';

        $search = $this->input->get_post('search');
        $search_submit = $this->input->get_post('search_submit');
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'j.date';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'ASC';
        $from = $this->input->get_post('from');
        $from2 = ( $from != '' ) ? $this->system_model->formatDate($from) : '';
        $to = $this->input->get_post('to');
        $to2 = ( $to != '' ) ? $this->system_model->formatDate($to) : '';
        $data['from'] = $from;
        $data['to'] = $to;
        $financial_year = $this->config->item('accounts_financial_year');


        // get unpaid jobs and exclude 0 job price
        
        /* disabled by gherx > moved to main query model (statements_model->getButtonStatements)
        $custom_filter = "
        AND `j`.`invoice_balance` !=0
        AND `j`.`status` = 'Completed'
        AND a.`status` != 'target'
        AND (
                j.`date` >= '$financial_year' OR
                j.`unpaid` = 1	
        )
        ";
        */
        $params['sel_query'] = '
        j.`id` AS jid,
        j.`status` AS jstatus,
        j.`service` AS jservice,
        j.`created` AS jcreated,
        j.`date` AS jdate,
        j.`comments` AS j_comments,
                        j.property_id,
        
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments,
        p.`compass_index_num`,
        
        a.`agency_id` AS a_id,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`account_emails`,
        a.`agency_emails`,
        a.`franchise_groups_id`
        ';
        
        $params = array(
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'echo_query' => 0,
            'filterDate' => array(
                'from' => $from2,
                'to' => $to2
            ),
            'search' => $search,
        );

        $agency_id = $this->input->get_post('agency_id');
        $phrase = $this->input->get_post('phrase');

        if ($agency_id !== null) {
            $params['agency_id'] = $agency_id;
            $data['agency_id'] = $agency_id;
        }
        if ($phrase !== null) {
            $params['phrase'] = $phrase;
            $data['phrase'] = $phrase;
        }

        $data['search_flag'] = (int) $this->input->get_post('search_flag');

        if (!empty($agency_id) || !empty($phrase)) {
            $statements = $this->statements_model->getButtonStatements($params)->result_array();
        } else {
            $statements = array();
        }
       

        $data['agencies'] = $this->statements_model->getAgencies()->result_array();

        $data['statements'] = $statements;
        
        // total rows
        $tparams = array(
            'sel_query' => 'COUNT(*) as statement_count'
        );
        $total_rows = count($statements);
        $data['sort_list'] = $total_rows;
        // base url
        $base_url = '/accounts/view_statements/';





        // get agency payments
        $sel_query = "
        agen_pay.agency_payments_id,
        agen_pay.date,
        agen_pay.amount,
        agen_pay.reference,
        agen_pay.payment_type,
        agen_pay.allocated,
        agen_pay.remaining,
        agen_pay.bank_deposit,
        agen_pay.remittance,

        pt.payment_type_id,
        pt.pt_name
        ";

        $custom_filter = "agen_pay.bank_deposit = 1 AND agen_pay.remittance = 0 AND agen_pay.remaining > 0";
        $agen_pay_params = array(
            'sel_query' => $sel_query,     
            'custom_filter' => $custom_filter,                           
            'agency_id' => $agency_id,
            'join_table' => array('agency_payments_agencies'),                                
            'display_query' => 0
        );
        $data['agen_pay_sql'] = $this->remittance_model->get_agency_payments($agen_pay_params);





        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $base_url;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);


        $this->load->view('templates/inner_header', $data);
        $this->load->view('accounts/view_statements.php', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function pdfexport_statement() {            

        $agency_id = $this->encryption_model->decrypt(rawurldecode($this->input->get_post('a')));
        $from = $this->encryption_model->decrypt(rawurldecode($this->input->get_post('f')));
        $to = $this->encryption_model->decrypt(rawurldecode($this->input->get_post('t')));;
        $phrase = $this->encryption_model->decrypt(rawurldecode($this->input->get_post('p')));

        $pdf_filename = "agency_statement_{$agency_id}_".date('dmYHis')."_".rand().".pdf";

        $pdf_params = array(
            'agency_id' => $agency_id,
            'from' => $from,
            'to' => $to,
            'phrase' => $phrase,
            'output' => 'I',
            'file_name' => $pdf_filename
        );
        $this->getStatementsPdf($pdf_params);
        
    }

    public function view_account_logs() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Account Logs";

// pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'ael.`date`';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'DESC';
        $from = $this->input->get_post('dateFrom');
        $from2 = ( $from != '' ) ? $this->system_model->formatDate($from) : '';
        $to = $this->input->get_post('dateTo');
        $to2 = ( $to != '' ) ? $this->system_model->formatDate($to) : '';
        $staff = $this->input->get_post('staff');
        $agency = $this->input->get_post('agency');
        $params = array(
            'sel_query' => '
                    ael.date as eventdate, 
                    ael.comment as comments,
                    ael.id as agency_event_log_id,
                    sa.`FirstName`,
                    sa.`LastName`,
                    ael.`next_contact`,
                    a.`agency_name`,
                    a.agency_id,
                    mlt.contact_type
                 ',
            'agency' => $agency,
            'staff' => $staff,
            'search_date' => array(
                'from' => $from2,
                'to' => $to2
            ),
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'echo_query' => 0
        );
        $logs = $this->accountslogs_model->getButtonAccountLogs($params)->result_array();
   
        $params = array(
            'sel_query' => "DISTINCT(ael.`staff_id`), sa.`FirstName`, sa.`LastName`",
        );
        $staff_sql = $this->accountslogs_model->getButtonAccountLogs($params)->result_array();
        $func_params = array(
            'sel_query' => 'DISTINCT (ael.`agency_id`), a.`agency_name`',
        );
        $agen_sql = $this->accountslogs_model->getButtonAccountLogs($func_params)->result_array();
        $data['dateFrom'] = $from;
        $data['dateTo'] = $to;
        $data['logs'] = $logs;
        $data['staff_sql'] = $staff_sql;
        $data['agen_sql'] = $agen_sql;
        $data['staff'] = $staff;
        $data['agency'] = $agency;
        $total_rows = count($logs);
        $data['sort_list'] = $total_rows;
        // base url
        $base_url = '/accounts/view_accounts_logs/';

        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $base_url;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);


        $this->load->view('templates/inner_header', $data);
        $this->load->view('accounts/view_accounts_logs.php', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function receipting() {

        $this->load->model('remittance_model');
        $this->load->model('agency_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Receipting";

        $from = $this->input->get_post('date_from_filter');
        $from2 = ( $from != '' ) ? $this->system_model->formatDate($from) : NULL;
        $to = $this->input->get_post('date_to_filter');
        $to2 = ( $to != '' ) ? $this->system_model->formatDate($to) : NULL;
        
        $phrase = $this->input->get_post('search');
        $btn_search = $this->input->get_post('btn_search');
        $show_inactive_agency = $this->input->get_post('show_inactive_agency');
        $agency_payments_id = $this->input->get_post('agency_payments_id');


        // pagination
        $per_page = $this->config->item('pagi_per_page');                 
        $offset = $this->input->get_post('offset');

        // get agency payments
        $custom_filter = "agen_pay.`bank_deposit` = 1 AND ( agen_pay.`remittance` = 1 OR agen_pay.`remittance` = 2 ) AND agen_pay.`remaining` > 0";
        

        if( $agency_payments_id > 0 ){

            // get agencies
            $sel_query = "
            agen_pay_a.agency_payments_id,
           
            a.`agency_id`,
            a.`agency_name`
            ";
            
            $a_params = array(
                'sel_query' => $sel_query,                                
                'agency_payments_id' => $agency_payments_id,    
                'join_table' => array('agency_payments_agencies'),                                
                'display_query' => 0
            );
            $agency_sql = $this->remittance_model->get_agency_payments($a_params);
            foreach( $agency_sql->result() as $a_row ){ 
                $agency[] = $a_row->agency_id;
            }
            $btn_search = 'Search';

            // paginated list
            $sel_query = "
            agen_pay.agency_payments_id,
            agen_pay.date,
            agen_pay.amount,
            agen_pay.reference,
            agen_pay.payment_type,
            agen_pay.remaining
            ";
            
            $params = array(
                'sel_query' => $sel_query,
                'agency_payments_id' => $agency_payments_id,
                'display_query' => 0
            );

            $agency_pay_sql = $this->remittance_model->get_agency_payments($params);        
            $agency_pay_row = $agency_pay_sql->row();

            $data['agen_pay_date'] = $agency_pay_row->date;
            $data['agen_pay_amount'] = $agency_pay_row->amount;
            $data['agen_pay_remaining'] = $agency_pay_row->remaining;
            $data['agen_pay_payment_type'] = $agency_pay_row->payment_type;
            $data['agen_pay_reference'] = $agency_pay_row->reference;

            $data['agen_pay_hide_tol'] = true;
            $data['agen_pay_hide_pagi'] = true;
           
        }else{

            $agency = $this->input->get_post('agency');
            $btn_search = $this->input->get_post('btn_search');
            
        }

        $data['agency_filter'] = $agency;
        $data['btn_search'] = $btn_search;
       
        // agency payment summary count
        $agency_pay_rollup_filter = '';
        if( count($agency) > 0 ){
            $agency_imp .= implode(",",$agency);
            $agency_pay_rollup_filter = "AND  agen_pay2_a.`agency_id` IN({$agency_imp})";
        }    

        if( $agency_payments_id > 0 ){
            $agency_pay_rollup_filter .= "AND agen_pay2_a.agency_payments_id = {$agency_payments_id}";
        }
        
        // get totals
        $agency_pay_rollup_str = "
        SELECT 
            COUNT(agen_pay.agency_payments_id) AS agency_pay_count, 
            SUM(agen_pay.amount) AS agency_pay_amnt_sum, 
            SUM(agen_pay.allocated) AS agency_pay_alloc_sum,
            SUM(agen_pay.remaining) AS agency_pay_rem_sum                
        FROM agency_payments AS agen_pay
        WHERE agen_pay.agency_payments_id IN(            
                
            SELECT agen_pay2_a.agency_payments_id
            FROM agency_payments AS agen_pay2	
            INNER JOIN agency_payments_agencies AS agen_pay2_a ON agen_pay2.agency_payments_id = agen_pay2_a.agency_payments_id	
            WHERE agen_pay2.`bank_deposit` = 1 AND ( agen_pay2.`remittance` = 1 OR agen_pay2.`remittance` = 2 ) AND agen_pay2.`remaining` > 0
            {$agency_pay_rollup_filter}               
            GROUP BY agen_pay2_a.agency_payments_id

        )
        ";
        $agency_pay_sql_rollup_sql = $this->db->query($agency_pay_rollup_str);
        $agency_pay_row = $agency_pay_sql_rollup_sql->row();
        $data['agency_pay_count'] = $agency_pay_row->agency_pay_count; 
        $data['agency_pay_amnt_sum'] = $agency_pay_row->agency_pay_amnt_sum;       
        $data['agency_pay_alloc_sum'] = $agency_pay_row->agency_pay_alloc_sum;
        $data['agency_pay_rem_sum'] = $agency_pay_row->agency_pay_rem_sum;


      
        // agency payments paginated list
        $sel_query = "
        agen_pay.agency_payments_id,
        agen_pay.date,
        agen_pay.amount,
        agen_pay.reference,
        agen_pay.payment_type,
        agen_pay.allocated,
        agen_pay.remaining,
        agen_pay.bank_deposit,
        agen_pay.remittance,

        pt.payment_type_id,
        pt.pt_name
        ";
        
        
        $params = array(
            'sel_query' => $sel_query,

            'custom_filter' => $custom_filter,
            'agency_payments_id' => $agency_payments_id,

            'multi_agency_filter' => $agency,

            'join_table' => array('agency_payments_agencies'),
            'group_by' => 'agen_pay_a.agency_payments_id',

            'sort_list' => array(
                array(
                    'order_by' => 'agen_pay.`date`',
                    'sort' => 'DESC'
                )
            ),

            'display_query' => 0
        );

        $data['agen_pay_sql'] = $this->remittance_model->get_agency_payments($params);

        // agency payments total row
        $sel_query = "agen_pay.agency_payments_id";
        $params = array(
            'sel_query' => $sel_query,

            'custom_filter' => $custom_filter,
            'agency_payments_id' => $agency_payments_id,

            'multi_agency_filter' => $agency,

            'join_table' => array('agency_payments_agencies'),
            'group_by' => 'agen_pay_a.agency_payments_id',
            'display_query' => 0
        );

        $agency_pay_tot_sql = $this->remittance_model->get_agency_payments($params);
        $agency_pay_count = $agency_pay_tot_sql->num_rows();

        //pagination
        $pagi_links_params_arr = array(
            'agency_payments_id' => $agency_payments_id,
            'btn_search' => $btn_search
        );
        $pagi_link_params = '/accounts/receipting/?' . http_build_query($pagi_links_params_arr);

        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $agency_pay_count;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;
        $this->pagination->initialize($config);

        $data['agency_pay_pagination_link'] = $this->pagination->create_links();

        // pagination count
        $pc_params = array(
            'total_rows' => $agency_pay_count,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['agency_pay_pagination_count'] = $this->jcclass->pagination_count($pc_params);

    


      
        // receipting paginated list
        // purposely put back as j.`assigned_tech` != 1, bec they dont want nulls to be included
        $custom_filter = "            
            j.`invoice_balance` > 0 AND 
            j.`status` = 'Completed' AND 
            a.`status` != 'target' AND
            j.`assigned_tech` != 1
            AND (
                j.`date` >= '{$this->config->item('accounts_financial_year')}'  OR
                j.`unpaid` = 1	
            )  
        ";
        $params = array(
            'sel_query' =>
            '
                j.`id` AS jid,
				j.`status` AS jstatus,
				j.`service` AS jservice,
				j.`created` AS jcreated,
				j.`date` AS jdate,
                j.`comments` AS j_comments,
                j.invoice_amount,
                j.invoice_balance,
                
                p.property_id,
				p.`address_1` AS p_address_1, 
				p.`address_2` AS p_address_2, 
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
				p.`postcode` AS p_postcode,
				p.`comments` AS p_comments,
				p.`compass_index_num`,
				
                a.`agency_id` AS a_id,
                a.agency_name,
				a.`phone` AS a_phone,
				a.`address_1` AS a_address_1, 
				a.`address_2` AS a_address_2, 
				a.`address_3` AS a_address_3,
				a.`state` AS a_state,
				a.`postcode` AS a_postcode,
				a.`account_emails`,
				a.`agency_emails`,
                a.`franchise_groups_id`,
              
            ',
            'custom_filter' => $custom_filter,
            'filterDate' => array(
                'from' => $from2,
                'to' => $to2
            ),
            'multi_agency_filter' => $agency,
            'phrase' => $phrase,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'j.`id`',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );

        if ($btn_search) {
            $data['plist'] = $this->remittance_model->getUnpaidJobs($params);
        }

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'custom_filter' => $custom_filter,
            'filterDate' => array(
                'from' => $from2,
                'to' => $to2
            ),
            'multi_agency_filter' => $agency,
            'phrase' => $phrase
        );
        if ($btn_search) {
            $query = $this->remittance_model->getUnpaidJobs($params);
            $total_rows = $query->row()->jcount;
        }

        // get agency payments
        $sel_query = "
        a.`agency_id`,
        a.`agency_name`
        ";
        $custom_filter = "agen_pay.`bank_deposit` = 1 AND ( agen_pay.`remittance` = 1 OR agen_pay.`remittance` = 2 )";
        
        $params = array(
            'sel_query' => $sel_query,

            'custom_filter' => $custom_filter,

            'join_table' => array('agency_payments_agencies'),
            'group_by' => 'agen_pay_a.agency_id',
            'display_query' => 0
        );

        $data['active_agency_filter'] = $this->remittance_model->get_agency_payments($params);        

        // all Agency filter
        $custom_where = "agency_id > 1";
        $a_params = array(
            'sel_query' => "agency_id,agency_name",   
            'custom_where' => $custom_where,          
            'country_id' => $this->config->item('country'),
            'sort_list' => array(
                array(
                    'order_by' => '`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $data['all_agency_filter'] = $this->agency_model->get_agency($a_params);

        //getpaymetntypes
        $data['pt_arr'] = $this->remittance_model->getPaymentTypes();
        

        //pagination
        $pagi_links_params_arr = array(
            'date_from_filter' => $from,
            'date_to_filter' => $to,
            'agency' => $agency,
            'phrase' => $phrase,
            'btn_search' => $btn_search,
            'agency_payments_id' => $agency_payments_id,
        );
        $pagi_link_params = '/accounts/receipting/?' . http_build_query($pagi_links_params_arr);

        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;
        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);



        $this->load->view('templates/inner_header', $data);
        $this->load->view('accounts/remittance.php', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_save_remittance() {

        $payments_arr = $this->input->post('payments_arr');
        $ip_id = $this->input->post('ip_id');
        $payment_date = ( $this->input->post('payment_date') != '' ) ? $this->system_model->formatDate($this->input->post('payment_date')) : NULL;
        $amount_paid = $this->input->post('amount_paid');
        $orig_amount_paid = $this->input->post('orig_amount_paid');
        $type_of_payment = $this->input->post('type_of_payment');
        $edited = $this->input->post('edited');
        $logged_user = $this->session->staff_id;
        $today = date('Y-m-d H:i:s');
        $job_log_type = 2; // job accounts log
        $payment_reference = $this->input->post('payment_reference');        
        $agency_id = $this->input->post('agency_id');
        $remaining_val = $this->input->post('remaining_val');
        $agency_payments_id = $this->input->post('agency_payments_id');
        $agen_pay_amount = $this->input->post('agen_pay_amount');
        $agen_pay_date = $this->input->post('agen_pay_date');


        // agency logs for bulk pay
        $multi_agency = $this->input->post('multi_agency');
        $bulk_amount_paid = $this->input->post('bulk_amount_paid');
        $bulk_rows_amount_paid = $this->input->post('bulk_rows_amount_paid');
        $bulk_pay_type = $this->input->post('bulk_pay_type');
        $bulk_pay_ref = $this->input->post('bulk_pay_ref');
        



        foreach ($payments_arr as $pay) {

            // decodes json string to actual json object
            $json_enc = json_decode($pay);

            $job_id = $json_enc->job_id;
            $payment_date = ( $json_enc->payment_date != '' ) ? $this->system_model->formatDate($json_enc->payment_date) : NULL;
            $amount_paid = $json_enc->amount_paid;
            $type_of_payment = $json_enc->type_of_payment;
            $ip_id = $json_enc->ip_id;
            $orig_amount_paid = $json_enc->orig_amount_paid;
            $edited = $json_enc->edited;
            $payment_reference = $json_enc->payment_reference;            
            $agency_id = $json_enc->agency_id;


            
            // insert agency payments jobs
            $data = array(
                'agency_payments_id' => $agency_payments_id,
                'job_id' => $job_id,
                'created_date' => date('Y-m-d H:i:s'),
            );
            
            $this->db->insert('agency_payments_jobs', $data);
            $agency_payments_jobs_id = $this->db->insert_id();


            // save payments
            $invoice_payments_insert_data = array(
                'job_id' => $job_id,
                'payment_date' => $payment_date,
                'amount_paid' => $amount_paid,
                'type_of_payment' => $type_of_payment,
                'created_by' => $logged_user,
                'created_date' => $today,
                'payment_reference' => $payment_reference,
                'agen_pay_j_id' => $agency_payments_jobs_id
            );
            $this->db->insert('invoice_payments', $invoice_payments_insert_data);

            if ($type_of_payment > 0) {

                $this->db->select('pt_name');
                $this->db->from('payment_types');
                $this->db->where('payment_type_id', $type_of_payment);
                $pt_sql = $this->db->get();
                $pt_row = $pt_sql->row();
                $pt_name = $pt_row->pt_name;
            }

            //insert job account logs            
            $log_title = 43; // Payment            
            //$j_log_details = "<strong>{$pt_name}</strong> Payment of <strong>\${$amount_paid}/\${$agen_pay_amount}</strong> of <strong>\${$remaining_val}</strong>. Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$payment_reference}</a>";
            $j_log_details = "Allocated <strong>{$pt_name}</strong> Payment of <strong>\${$amount_paid}</strong> of total <strong>\${$bulk_rows_amount_paid}/\${$bulk_amount_paid}</strong> with <strong>\${$remaining_val}</strong> remaining. Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$payment_reference}</a>";

            $log_params = array(
                'title' => $log_title,
                'details' => $j_log_details,
                'display_in_accounts' => 1,
                'created_by_staff' => $logged_user,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);




            // AUTO - UPDATE INVOICE DETAILS
            $this->system_model->updateInvoiceDetails($job_id);
        }

       

        foreach ($multi_agency as $agency_id) {

            if ($bulk_pay_type > 0) {

                $this->db->select('pt_name');
                $this->db->from('payment_types');
                $this->db->where('payment_type_id', $bulk_pay_type);
                $pt_sql = $this->db->get();
                $pt_row = $pt_sql->row();
                $pt_name = $pt_row->pt_name;
            }

            $log_title = 43; // Payment            
            //$log_details = "BULK {$j_log_details}";
            $a_log_details = "Allocated <strong>{$pt_name}</strong> Payment of <strong>\${$bulk_rows_amount_paid}/\${$bulk_amount_paid}</strong> with <strong>\${$remaining_val}</strong> remaining. Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$payment_reference}</a>, on <strong>{$agen_pay_date}</strong>";

            // insert agency logs
            $log_params_agency = array(
                'title' => $log_title,
                'details' => $a_log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $logged_user,
                'agency_id' => $agency_id
            );

            $this->system_model->insert_log($log_params_agency);
        }


        // update agency payments
        if( $agency_payments_id > 0 && $bulk_rows_amount_paid > 0 ){

            $this->db->query("
                UPDATE `agency_payments` 
                SET 
                    `allocated` = (`allocated`+{$bulk_rows_amount_paid}),
                    `remaining` = {$remaining_val}
                WHERE `agency_payments_id` = {$agency_payments_id}
            ");

        }        


    }

    // closed invoice
    public function ajax_get_closed_invoice() {

        $this->load->model('remittance_model');

        $multi_agency = $this->input->get_post('multi_agency');
        $p_address = $this->input->get_post('p_address');
        $from = $this->input->get_post('from');
        $from2 = ( $from != '' ) ? $this->system_model->formatDate($from) : NULL;
        $to = $this->input->get_post('to');
        $to2 = ( $to != '' ) ? $this->system_model->formatDate($to) : NULL;
        $phrase = $this->input->get_post('phrase');
        $include_closed_inv = $this->input->get_post('include_closed_inv');

        

        $custom_filter = "
            j.`status` = 'Completed' AND 
            a.`status` != 'target'  
            AND (
                j.`assigned_tech` != 1
                OR j.`assigned_tech` IS NULL
            )
        ";

        if( $include_closed_inv != 1 ){
            $custom_filter .= "AND j.`invoice_balance` > 0";
        }

        $cl_inv_params = array(
            'sel_query' =>
            '
                j.`id` AS jid,
				j.`status` AS jstatus,
				j.`service` AS jservice,
				j.`created` AS jcreated,
				j.`date` AS jdate,
                j.`comments` AS j_comments,
                j.invoice_amount,
                j.invoice_balance,
                
                p.property_id,
				p.`address_1` AS p_address_1, 
				p.`address_2` AS p_address_2, 
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
				p.`postcode` AS p_postcode,
				p.`comments` AS p_comments,
				p.`compass_index_num`,
				
                a.`agency_id` AS a_id,
                a.agency_name,
				a.`phone` AS a_phone,
				a.`address_1` AS a_address_1, 
				a.`address_2` AS a_address_2, 
				a.`address_3` AS a_address_3,
				a.`state` AS a_state,
				a.`postcode` AS a_postcode,
				a.`account_emails`,
				a.`agency_emails`,
                a.`franchise_groups_id`,
              
            ',
            'custom_filter' => $custom_filter,
            'filterDate' => array(
                'from' => $from2,
                'to' => $to2
            ),
            'p_address' => $p_address,
            'multi_agency_filter' => $multi_agency,
            'phrase' => $phrase,

            'sort_list' => array(
                array(
                    'order_by' => 'j.`id`',
                    'sort' => 'ASC'
                )
            ),

            'display_query' => 0
        );

        $cl_inv_sql = $this->remittance_model->getUnpaidJobs($cl_inv_params);

        //getpaymetntypes
        $pt_arr = $this->remittance_model->getPaymentTypes();

        $chckCounter = 1;
        foreach ($cl_inv_sql->result_array() as $row) {


            $check_digit = $this->system_model->getCheckDigit(trim($row['jid']));
            $bpay_ref_code = "{$row['jid']}{$check_digit}";
            ?>

            <tr class="body_tr closed_inv_row j_new_row_bg" data-toggle="tooltip">
                <td><?php echo ($this->system_model->isDateNotEmpty($row['jdate']) == true) ? $this->system_model->formatDate($row['jdate'], 'd/m/Y') : ''; ?></td>	
                <td> <?php echo $this->gherxlib->crmlink('vjd', $row['jid'], $bpay_ref_code); ?> </td>	
                <td> <?php echo $this->gherxlib->crmlink('vpd', $row['property_id'], "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"); ?> </td>	
                <td> 
                    <?php echo $this->gherxlib->crmlink('vjd', $row['a_id'], $row['agency_name']); ?>
                    <input type="hidden" class="agency_id" value="<?php echo $row['a_id']; ?>">
                </td>	
                <td>
                    <strong>
                        $<?php
                        echo $amount = number_format($row['invoice_amount'], 2)
                        ?>
                    </strong>
                    <input type="hidden" class="amount" value="<?php echo $amount; ?>" />
                </td>
                <td>
                    <em style="color:red;">
                        $<?php
                        echo $balance = number_format($row['invoice_balance'], 2)
                        ?>
                    </em>
                    <input type="hidden" class="balance" value="<?php echo $balance; ?>" />
                </td>
                <td style="border-left: 1px solid #cccccc"><input type="text" class="form-control payment_fields amount_paid" style="width: 75px" disabled /></td>
                <td><input data-allow-input="true" type="text" class="form-control flatpickr payment_fields payment_date" disabled /></td>
                <td>
                    <input type="text" class="payment_fields form-control payment_reference" disabled />
                </td>
                <td>
                    <div class="checkbox" style="margin:0;">
                        <input class="job_chk job_chk_ci" name="chk_all" type="checkbox" id="checkbox_ci_<?php echo $chckCounter ?>" value="<?php echo $row['jid']; ?>">
                        <label for="checkbox_ci_<?php echo $chckCounter ?>">&nbsp;</label>
                    </div>
                </td>
            </tr>

            <?php
            $chckCounter++;
        }
    }

    public function send_statements() {


        $this->load->model('remittance_model');

        $agency_id = $this->input->get_post('agency');
        $phrase = $this->input->get_post('search');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //$custom_filter = "j.`job_price` > 0 AND j.`invoice_balance` > 0 AND j.`status` = 'Completed' AND (a.`status` = 'Active' OR a.`status` = 'Deactivated')";
        $financial_year = $this->config->item('accounts_financial_year');
        $custom_filter = "
            `j`.`invoice_balance` >0
            AND `j`.`status` = 'Completed'
            AND a.`status` != 'target'
            AND (
                j.`date` >= '{$financial_year}' OR
                j.`unpaid` = 1
            )
        ";
        $custom_select = "SUM(j.`invoice_balance`) AS invoice_balance_tot, a.`agency_name`, a.`agency_id`, a.`account_emails`, a.`agency_emails`, a.`send_statement_email_ts`, a.`statements_agency_comments`";
        $params = array(
            'sel_query' => $custom_select,
            'custom_filter' => $custom_filter,
            'agency_id' => $agency_id,
            'phrase' => $phrase,
            'group_by' => 'a.`agency_id`',
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $data['plist'] = $this->remittance_model->getUnpaidJobs($params);


        // all rows
        $sel_query = "COUNT( Distinct(a.`agency_id`) ) AS a_count";
        $total_rows_params = array(
            'sel_query' => $sel_query,
            'custom_filter' => $custom_filter,
            'agency_id' => $agency_id,
            'phrase' => $phrase,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $query = $this->remittance_model->getUnpaidJobs($total_rows_params);
        $total_rows = $query->row()->a_count;

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`),a.`agency_name`";
        $params_agency = array(
            'sel_query' => $sel_query,
            'custom_filter' => $custom_filter,
            'group_by' => 'a.`agency_id`',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $data['agency_filter'] = $this->remittance_model->getUnpaidJobs($params_agency);

        //pagination
        $pagi_links_params_arr = array(
            'agency' => $agency_id,
            'search' => $phrase
        );
        $pagi_link_params = '/accounts/send_statements/?' . http_build_query($pagi_links_params_arr);

        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;
        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);


        $data['title'] = "Send Statements";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('accounts/send_statements', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_email_agency_statements() {

        $this->load->library('email');
        $this->load->model('remittance_model');
        $this->load->model('agency_model');

        $agency_id_arr = $this->input->post('agency_id_arr');
        $country_id = $this->config->item('country');

        $country_query = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));
        $e_from = $country_query->outgoing_email;

        

        foreach ($agency_id_arr as $json) {


            $email_data = [];

            // decodes json string to actual json object
            $json_enc = json_decode($json);

            $agency_id = $json_enc->agency_id;
            $accounts_email = $json_enc->accounts_email;

            //$pdf_filename = 'statements_' . date('dmYHis') . '.pdf';
            $pdf_filename = "agency_statement_{$agency_id}_".date('dmYHis')."_".rand().".pdf";

            /*
            //$qa_email = "danielk@sats.com.au";
            //PDF Template
            $pdf_params = array(
                'agency_id' => $agency_id,
                'country_id' => $country_id,
                'ret' => 1,
                'output' => 'S',
                'file_name' => $pdf_filename
            );
            $getStatementsPdf = $this->getStatementsPdf($pdf_params);
            */

            $pdf_params = array(
                'agency_id' => $agency_id,
                'output' => 'S',
                'file_name' => $pdf_filename
            );
            $getStatementsPdf = $this->getStatementsPdf($pdf_params);

            /**  Abandon statement email pdf link
              $email_data['statement_pdf_link'] = BASEURL."accounts/statement_pdf/{$agency_id}"; // use pdf link for now > disable later when pdf attachment is up
            */

            //Email
            $config = Array(
                'mailtype' => 'html',
                'charset' => 'utf-8'
            );
            $this->email->initialize($config);
            $this->email->set_newline("\r\n");
            $this->email->from($e_from, "SATS - Smoke Alarm Testing Services");            
            $this->email->to($accounts_email);            
            $this->email->subject("Statement as of " . date('d/m/Y'));
            $e_body = $this->load->view('emails/send_statement', $email_data, TRUE);
            $this->email->attach($getStatementsPdf, 'attachment', $pdf_filename, 'application/pdf');
            $this->email->message($e_body);

            if ($this->email->send()) {

                //-----send email to cc accounts (Plain email text)-----
                //get agency info
                $agency_params = array(
                    'sel_query' => 'a.agency_id, a.agency_name, a.address_1, a.address_2, a.address_3, a.state, a.postcode, a.franchise_groups_id',
                    'agency_id' => $agency_id
                );
                $cc_email_data['agency_info'] = $this->agency_model->get_agency($agency_params)->row_array();

                //get statments data for CC email datas
                $cc_email_data['statement_data'] = $this->get_statement_for_plain_email($agency_id);
                $cc_email_data['agency_id'] = $agency_id;

                $this->email->clear(TRUE);
                $this->email->set_newline("\r\n");
                $this->email->from($e_from, "SATS - Smoke Alarm Testing Services");
                $this->email->to($this->config->item('sats_cc_email'));                              
                $this->email->subject("Statement as of " . date('d/m/Y'));
                $e_body = $this->load->view('emails/send_statement_cc_accounts', $cc_email_data, TRUE);
                $this->email->message($e_body);
                $this->email->send();
                //-----send email to cc accounts end-----
                //insert logs
                $log_details = "Statement as of " . date('d/m/Y') . " <a href='{$this->config->item("crmci_link")}/accounts/pdfexport_statement/?a=".rawurlencode($this->encryption_model->encrypt($agency_id))."'>Statement PDF</a>";
                $log_params = array(
                    'title' => 45, //statement
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'agency_id' => $agency_id
                );
                $this->system_model->insert_log($log_params);

                //update agency send_statement_email_ts
                if($agency_id && is_numeric($agency_id)){
                    $agency_update_data = array(
                        'send_statement_email_ts' => date('Y-m-d H:i:s')
                    );
                    $this->db->where('agency_id',$agency_id);
                    $this->db->update('agency', $agency_update_data);
                }
                
            }
        }
    }

    /**
     * statement_pdf
     * @params agency_id (url segment 3)
     */
    public function statement_pdf() {

        $agency_id = $this->uri->segment(3);
        $country_id = $this->config->item('country');

        if ($agency_id && is_numeric($agency_id)) {
            //PDF Template
            $pdf_params = array(
                'agency_id' => $agency_id,
                'country_id' => $country_id,
                'output' => 'I',
                'file_name' => $pdf_filename
            );
            $getStatementsPdf = $this->getStatementsPdf($pdf_params);

            echo $getStatementsPdf;
        } else {
            echo "PDF ERROR: Contact Admin";
        }
    }

    /**
     * by Gherx
     * statement pdf template/content
     */
    function getStatementsPdf($params) {

        $this->load->model('remittance_model'); 

        $agency_id = $params['agency_id'];
        $from = $params['from'];
        $from2 = ( $from != '' ) ? $this->system_model->formatDate($from) : '';
        $to = $params['to'];
        $to2 = ( $to != '' ) ? $this->system_model->formatDate($to) : '';
        $phrase = $params['phrase'];


        $order_by = ( $params['order_by'] != "" ) ? $params['order_by'] : 'j.date';
        $sort = ( $params['sort'] != "" ) ? $params['sort'] : 'ASC';

        $financial_year = $this->config->item('accounts_financial_year');
        
         /* disabled by gherx > moved to main query model (statements_model->getButtonStatements)
        $custom_filter = "
        AND `j`.`invoice_balance` !=0
        AND `j`.`status` = 'Completed'
        AND a.`status` != 'target'
        AND (
                j.`date` >= '$financial_year' OR
                j.`unpaid` = 1	
        )";
        */

        $sel_query = '
			j.`id` AS jid,
				j.`status` AS jstatus,
				j.`service` AS jservice,
				j.`created` AS jcreated,
				j.`date` AS jdate,
				j.`comments` AS j_comments,
				
				p.`address_1` AS p_address_1, 
				p.`address_2` AS p_address_2, 
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
				p.`postcode` AS p_postcode,
				p.`comments` AS p_comments,
				p.`compass_index_num`,
				
				a.`agency_id` AS a_id,
				a.`phone` AS a_phone,
				a.`address_1` AS a_address_1, 
				a.`address_2` AS a_address_2, 
				a.`address_3` AS a_address_3,
				a.`state` AS a_state,
				a.`postcode` AS a_postcode,
				a.`account_emails`,
				a.`agency_emails`,
				a.`franchise_groups_id`
        ';
        
        $statement_params = array(
            'sel_query' => $sel_query,
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'echo_query' => 0,
            'filterDate' => array(
                'from' => $from2,
                'to' => $to2
            )
        );

        if ($agency_id !== null) {
            $statement_params['agency_id'] = $agency_id;            
        }
        if ($phrase !== null) {
            $statement_params['phrase'] = $phrase;            
        }

        $statements = $this->statements_model->getButtonStatements($statement_params)->result_array();

        // load statement pdf class that extends FPDF
        $pdf = new StatementsPdf('P', 'mm', 'A4');
        $pdf->setAgency($this->statements_model->getAgencyInfo($agency_id));
        $pdf->agency_id = $agency_id;
        $pdf->to_date = $to;

        $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
        $pdf->setCountryData($this->config->item('country'));

        $pdf->SetTopMargin(40); // top margin
        $pdf->SetAutoPageBreak(true, 30); // bottom margin
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $cell_height = 5;
        $font_size = 8;

        $col1 = 20;
        $col2 = 40;
        $col3 = 81;
        $col5 = 15;
        $ref_width = 30;

        $pdf->SetFont('Arial', '', $font_size);


        $balance_tot = 0;
        $not_overdue = 0;
        $overdue_31_to_60 = 0;
        $overdue_61_to_90 = 0;
        $overdue_91_more = 0;
        
        $balance_tot_agen_pay_rem = 0;
        $not_overdue_agen_pay_rem = 0;
        $overdue_31_to_60_agen_pay_rem = 0;
        $overdue_61_to_90_agen_pay_rem = 0;
        $overdue_91_more_agen_pay_rem = 0;
        $border = true;

        // job listings
        foreach ($statements as $row) {

            $jdate = ( $this->system_model->isDateNotEmpty($row['jdate']) ) ? date('d/m/Y', strtotime($row['jdate'])) : '';

            // append checkdigit to job id for new invoice number
            $check_digit = $this->system_model->getCheckDigit(trim($row['jid']));
            $bpay_ref_code = "{$row['jid']}{$check_digit}";

            $p_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";

            $invoice_amount = number_format($row['invoice_amount'], 2);
            $invoice_payments = number_format($row['invoice_payments'], 2);
            $invoice_credits = number_format($row['invoice_credits'], 2);

            $balance_tot += $row['invoice_balance'];
            $invoice_balance = number_format($row['invoice_balance'], 2);

            if ($invoice_payments > 0) {
                $invoice_payments_str = '$' . $invoice_payments;
            } else {
                $invoice_payments_str = '';
            }

            if ($invoice_credits > 0) {
                $invoice_credits_str = '($' . $invoice_credits.')';
            } else {
                $invoice_credits_str = '';
            }


            // Age
            $date1 = date_create(date('Y-m-d', strtotime($row['jdate'])));
            $date2 = date_create(date('Y-m-d'));
            $diff = date_diff($date1, $date2);
            $age = $diff->format("%r%a");
            $age_val = (((int) $age) != 0) ? $age : 0;


            if ($age_val <= 30) { // not overdue, within 30 days
                $not_overdue += $row['invoice_balance'];
            } else if ($age_val >= 31 && $age_val <= 60) { // overdue, within 31 - 60 days
                $overdue_31_to_60 += $row['invoice_balance'];
            } else if ($age_val >= 61 && $age_val <= 90) { // overdue, within 61 - 90 days
                $overdue_61_to_90 += $row['invoice_balance'];
            } else if ($age_val >= 91) { // overdue over 91 days or more
                $overdue_91_more += $row['invoice_balance'];
            }

            $url = $_SERVER['SERVER_NAME'];
            if ( $this->config->item('country') == 1) { // AU
                $compass_fg_id = 39;
            }

            $fg_id = $row['franchise_groups_id'];

            $multicell_height = 5;
            $cell_height = 5; 

            $pdf->Cell($col1, $cell_height, $jdate, $border);

            // reference
            $current_y = $pdf->GetY();
            $current_x = $pdf->GetX();
            $pdf->Cell($ref_width, $cell_height, $bpay_ref_code, $border);
            $pdf->SetXY($current_x + $ref_width, $current_y);   

            // description          
            if ($fg_id == $compass_fg_id) { // compass only
                $pdf->Cell($col1, $cell_height, $row['compass_index_num'], $border);
                $current_y = $pdf->GetY();
                $current_x = $pdf->GetX();
                $des_col_width = $col3 - 20;
                //comment out for now// $pdf->MultiCell($des_col_width, $multicell_height, $p_address,'T','L');
                $pdf->Cell($des_col_width, $cell_height, $p_address,'T','L');
                $pdf->SetXY($current_x + $des_col_width, $current_y);
            } else {
                $current_y = $pdf->GetY();
                $current_x = $pdf->GetX();
                //comment out for now// $pdf->MultiCell($col3, $multicell_height, $p_address, 'T',"L");
                $pdf->Cell($des_col_width, $cell_height, $p_address,'T','L');

                $pdf->SetXY($current_x + $col3, $current_y);  
            }
            //$pdf->SetXY($current_x + $col3, $current_y);  

            $pdf->Cell($col5, $cell_height, '$' . $invoice_amount, $border);
            $pdf->Cell($col5, $cell_height, $invoice_payments_str, $border);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell($col5, $cell_height, $invoice_credits_str, $border);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell($col5, $cell_height, '$' . $invoice_balance, $border);
            $pdf->Ln();

        }

        // agency payments listing
        $sel_query = "
        agen_pay.agency_payments_id,
        agen_pay.date,
        agen_pay.amount,
        agen_pay.reference,
        agen_pay.payment_type,
        agen_pay.allocated,
        agen_pay.remaining,
        agen_pay.bank_deposit,
        agen_pay.remittance,

        pt.payment_type_id,
        pt.pt_name
        ";

        $custom_filter = "
        agen_pay.bank_deposit = 1         
        AND agen_pay.remaining > 0";
        $agen_pay_params = array(
            'sel_query' => $sel_query,     
            'custom_filter' => $custom_filter,                           
            'agency_id' => $agency_id,
            'join_table' => array('agency_payments_agencies'),                                
            'display_query' => 0
        );
        $agen_pay_sql = $this->remittance_model->get_agency_payments($agen_pay_params);

        foreach($agen_pay_sql->result() as $index => $agen_pay_row){

            $agency_pay_date = date("d/m/Y",strtotime($agen_pay_row->date));

            // Age
            $date1 = date_create(date('Y-m-d', strtotime($agen_pay_row->date)));
            $date2 = date_create(date('Y-m-d'));
            $diff = date_diff($date1, $date2);
            $age = $diff->format("%r%a");
            $age_val = (((int) $age) != 0) ? $age : 0;

            $balance_tot_agen_pay_rem += $agen_pay_row->remaining;

            if ($age_val <= 30) { // not overdue, within 30 days
                $not_overdue_agen_pay_rem += $agen_pay_row->remaining;
            } else if ($age_val >= 31 && $age_val <= 60) { // overdue, within 31 - 60 days
                $overdue_31_to_60_agen_pay_rem += $agen_pay_row->remaining;
            } else if ($age_val >= 61 && $age_val <= 90) { // overdue, within 61 - 90 days
                $overdue_61_to_90_agen_pay_rem += $agen_pay_row->remaining;
            } else if ($age_val >= 91) { // overdue over 91 days or more
                $overdue_91_more_agen_pay_rem += $agen_pay_row->remaining;
            }

            
            if( $agen_pay_row->remittance == 0 ){

                $agency_pay_description = "Payment received {$agency_pay_date}. Please email a remittance advice to {$this->config->item('sats_accounts_email')}";
                $agency_pay_description_limit = substr($agency_pay_description,0,55);
                $agency_pay_desc_length =  strlen($agency_pay_description);
                $allocated = '';

                //$multicell_height = 5;
                if( $agency_pay_desc_length > 64 ){
                    //$cell_height = 10;               
                }

            }else{
                
                $agency_pay_description = "Please contact our accounts department to discuss this payment.";
                $agency_pay_description_limit = substr($agency_pay_description,0,55);
                //$multicell_height = 5;
                //$cell_height = 5;                   
                $allocated = '$'.$agen_pay_row->allocated;

            }

             
    
            

            $pdf->Cell($col1,$cell_height,$agency_pay_date,$border);

            // reference
            $agencyPayRefText = (strlen($agen_pay_row->reference)>20) ? substr($agen_pay_row->reference,0,20)."..." :  $agen_pay_row->reference;
            $current_y = $pdf->GetY();
            $current_x = $pdf->GetX();
            $pdf->MultiCell($ref_width,$cell_height,$agencyPayRefText,$border); 
            $pdf->SetXY($current_x + $ref_width, $current_y);     
             
            // description
            $current_y = $pdf->GetY();
            $current_x = $pdf->GetX();
           //comment out fo now // $pdf->MultiCell($col3,$multicell_height,$agency_pay_description,'T','L');
            $pdf->Cell($col3,$cell_height,$agency_pay_description_limit,$border,'L');
            $pdf->SetXY($current_x + $col3, $current_y);
           
            $pdf->Cell($col5,$cell_height,'($'.$agen_pay_row->amount.')',$border);
            $pdf->Cell($col5,$cell_height,$allocated,$border);
            $pdf->Cell($col5,$cell_height,'',$border);
            $pdf->Cell($col5,$cell_height,'($'.$agen_pay_row->remaining.')',$border);
            $pdf->Ln();
        }


        $x = $pdf->GetX();
        $y = $pdf->GetY();

        //agency comment start
        $pdf->setX(10);
        $pdf->setY($y + 3);
        $statement_agency_comments = $row['statements_agency_comments'];
        $pdf->MultiCell(100, 5, $statement_agency_comments, 0, 'L');
        //agency comment end

        $y = $pdf->GetY(); //reset

        $pdf->setX(10);
        $pdf->setY($y + 3);


        $cell_width = 38;
        $cell_height = 7;
        $cell_border = 1;
        $cell_new_line = 0;
        $cell_align = 'R';
        $cell_change_txt_color = true;

        $cell_height = 10;

        // grey
        $pdf->SetFillColor(238, 238, 238);
        $pdf->SetFont('Arial', 'B', $font_size);

        $cell_height = 5;
        $pdf->Cell($cell_width, $cell_height, '0-30 days (Not Overdue)', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '31-60 days OVERDUE', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '61-90 days OVERDUE', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '91+ days OVERDUE', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, 'Total Amount Due', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);

        $pdf->Ln();


        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', $font_size);
        $_30_days = ($not_overdue<$not_overdue_agen_pay_rem) ? "($".number_format(($not_overdue-$not_overdue_agen_pay_rem), 2).")" : "$".number_format(($not_overdue-$not_overdue_agen_pay_rem), 2);
        $_31_60_days = ($overdue_31_to_60<$overdue_31_to_60_agen_pay_rem) ? "($".number_format(($overdue_31_to_60-$overdue_31_to_60_agen_pay_rem), 2).")" : "$".number_format(($overdue_31_to_60-$overdue_31_to_60_agen_pay_rem), 2);
        $_61_90_days = ($overdue_61_to_90<$overdue_61_to_90_agen_pay_rem) ? "($".number_format(($overdue_61_to_90-$overdue_61_to_90_agen_pay_rem), 2).")" : "$".number_format(($overdue_61_to_90-$overdue_61_to_90_agen_pay_rem), 2);
        $_91days = ($overdue_91_more<$overdue_91_more_agen_pay_rem) ? "($".number_format(($overdue_91_more-$overdue_91_more_agen_pay_rem), 2).")" : "$".number_format(($overdue_91_more-$overdue_91_more_agen_pay_rem), 2);
        $total_amount_due = ($balance_tot<$balance_tot_agen_pay_rem) ? "($".number_format(($balance_tot-$balance_tot_agen_pay_rem), 2).")" : "$".number_format(($balance_tot-$balance_tot_agen_pay_rem), 2);
       
        /*$pdf->Cell($cell_width, $cell_height, '$' . number_format(($not_overdue-$not_overdue_agen_pay_rem), 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '$' . number_format(($overdue_31_to_60-$overdue_31_to_60_agen_pay_rem), 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '$' . number_format(($overdue_61_to_90-$overdue_61_to_90_agen_pay_rem), 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '$' . number_format(($overdue_91_more-$overdue_91_more_agen_pay_rem), 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        // grey
        $pdf->SetFillColor(238, 238, 238);
        $pdf->SetFont('Arial', 'B', $font_size);
        $pdf->Cell($cell_width, $cell_height, '$' . number_format(($balance_tot-$balance_tot_agen_pay_rem), 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->SetFillColor(255, 255, 255);*/

        
        $pdf->Cell($cell_width, $cell_height,$_30_days, $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, $_31_60_days, $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, $_61_90_days, $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height,$_91days, $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        // grey
        $pdf->SetFillColor(238, 238, 238);
        $pdf->SetFont('Arial', 'B', $font_size);
        $pdf->Cell($cell_width, $cell_height,$total_amount_due, $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->SetFillColor(255, 255, 255);


        //statement not start
        //get statements_generic_note from crm_settings table
        $statements_generic_note_query = $this->db->select('statements_generic_note')->from('crm_settings')->where('country_id', $this->config->item('country'))->get();
        $statements_generic_note_query_row = $statements_generic_note_query->row_array();

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setX(10);
        $pdf->setY($y + 12);
        $pdf->SetFont('Arial', '', $font_size);
        $statement_agency_comments = $statements_generic_note_query_row['statements_generic_note'];
        $pdf->WriteHTML("{$statement_agency_comments}");

        return $pdf->Output($params['file_name'], $params['output']);
        
    }

    /**
     * Update statement agency comments via ajax
     */
    public function ajax_update_statement_agency_comments() {

        $agency_id = $this->input->post('agency_id');
        $statement_comments = $this->input->post('statement_comments');

        if ($agency_id && !empty($agency_id) && is_numeric($agency_id)) {

            $update_data = array(
                'statements_agency_comments' => $statement_comments,
                'statements_agency_comments_ts' => date('Y-m-d H:i:s')
            );
            $this->db->where('agency_id', $agency_id);
            $this->db->update('agency', $update_data);
            $this->db->limit(1);
        } else {
            echo "Error: agency id not valid!!!";
        }
    }

    /**
     * Get statement data for plain email to CC accounts
     * @params agency_id
     */
    public function get_statement_for_plain_email($agency_id) {

        $this->load->model('remittance_model');

        // static financial year 
        $financial_year = $this->config->item('accounts_financial_year');

        $order_by = 'j.date';
        $sort = 'ASC';

       /* $custom_filter = "
            `j`.`invoice_balance` >0
            AND `j`.`status` = 'Completed'
            AND a.`status` != 'target'
            AND (
                j.`date` >= '{$financial_year}' OR
                j.`unpaid` = 1	
            )
		";

        $sel_query = "
            *, 
            j.`id` AS jid,
            j.`status` AS jstatus,
            j.`service` AS jservice,
            j.`created` AS jcreated,
            j.`date` AS jdate,
            j.`comments` AS j_comments,
            
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments,
            p.`compass_index_num`,
            
            a.`agency_id` AS a_id,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`account_emails`,
            a.`agency_emails`,
            a.`franchise_groups_id`
        ";
        $jparams = array(
            'sel_query' => $sel_query,
            'custom_filter' => $custom_filter,
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'agency_id' => $agency_id
        );
        */

        //new query (by: gherx)
        $sel_query = '
			j.`id` AS jid,
				j.`status` AS jstatus,
				j.`service` AS jservice,
				j.`created` AS jcreated,
				j.`date` AS jdate,
				j.`comments` AS j_comments,
				
				p.`address_1` AS p_address_1, 
				p.`address_2` AS p_address_2, 
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
				p.`postcode` AS p_postcode,
				p.`comments` AS p_comments,
				p.`compass_index_num`,
				
				a.`agency_id` AS a_id,
				a.`phone` AS a_phone,
				a.`address_1` AS a_address_1, 
				a.`address_2` AS a_address_2, 
				a.`address_3` AS a_address_3,
				a.`state` AS a_state,
				a.`postcode` AS a_postcode,
				a.`account_emails`,
				a.`agency_emails`,
				a.`franchise_groups_id`
        ';

        $statement_params = array(
            'sel_query' => $sel_query,
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),
            'agency_id' => $agency_id,
            'echo_query' => 0
        );

        //return $this->remittance_model->getUnpaidJobs($jparams);
        return $this->statements_model->getButtonStatements($statement_params);
        
    }


    public function agency_payments() {

        $this->load->model('remittance_model');
        $this->load->model('agency_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Payments";
        $uri = '/accounts/agency_payments';
        $data['uri'] = $uri;

        $agency = $this->input->get_post('agency');

        $from = ( $this->input->post('from') != '' ) ? $this->system_model->formatDate($this->input->post('from')) : NULL;
        $to = ( $this->input->post('to') != '' ) ? $this->system_model->formatDate($this->input->post('to')) : NULL;

        $amount = $this->input->get_post('amount');
        $payment_type = $this->input->get_post('payment_type');
        $reference = $this->input->get_post('reference');
        
        $btn_search = $this->input->get_post('btn_search');
        $open_edit_lb = $this->input->get_post('open_edit_lb');
        $agency_payments_id = $this->input->get_post('agency_payments_id');

        // edit lightbox should show all including closed payments      
        if( $open_edit_lb == 1 ){
            $include_closed_pay = 1;
        }else{
            $include_closed_pay = $this->input->get_post('include_closed_pay');
        }

        if( $include_closed_pay == 1 ){
            $show_only_open_payments = false;
        }else{
            $show_only_open_payments = true;
        }
        
        // Active Agency filter
        $custom_where = "agency_id > 1";
        $a_params = array(
            'sel_query' => "agency_id,agency_name",
            'custom_where' => $custom_where,  
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'sort_list' => array(
                array(
                    'order_by' => '`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $data['active_agency_filter'] = $this->agency_model->get_agency($a_params);

        // all Agency filter
        $a_params = array(
            'sel_query' => "agency_id,agency_name",   
            'custom_where' => $custom_where,          
            'country_id' => $this->config->item('country'),
            'sort_list' => array(
                array(
                    'order_by' => '`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $data['all_agency_filter'] = $this->agency_model->get_agency($a_params);


        // get paymet types
        $data['pt_arr'] = $this->remittance_model->getPaymentTypes();

        // pagination
        $per_page = $this->config->item('pagi_per_page');            
        $offset = $this->input->get_post('offset');

        if( $show_only_open_payments == true ){
            $custom_filter = "agen_pay.`remaining` > 0";
        }
        

        // paginated list
        $sel_query = "
        agen_pay.agency_payments_id,
        agen_pay.date,
        agen_pay.amount,
        agen_pay.reference,
        agen_pay.payment_type,
        agen_pay.allocated,
        agen_pay.remaining,
        agen_pay.bank_deposit,
        agen_pay.remittance,

        pt.payment_type_id,
        pt.pt_name
        ";
        
        $params = array(
            'sel_query' => $sel_query,
            'custom_filter' => $custom_filter,

            'multi_agency_filter' => $agency,

            'search_from_to' => array(
                'from' => $from,
                'to' => $to
            ),
            'amount' => $amount,
            'payment_type' => $payment_type,
            'reference' => $reference,
            'agency_payments_id' => $agency_payments_id,

            'join_table' => array('agency_payments_agencies'),

            'group_by' => 'agen_pay_a.agency_payments_id',

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => 'agen_pay.`date`',
                    'sort' => 'DESC'
                )
            ),

            'display_query' => 0
        );

        $data['agen_pay_sql'] = $this->remittance_model->get_agency_payments($params);

        // all rows
        $sel_query = "agen_pay.agency_payments_id";
        $params = array(
            'sel_query' => $sel_query,
            'custom_filter' => $custom_filter,

            'multi_agency_filter' => $agency,

            'search_from_to' => array(
                'from' => $from,
                'to' => $to
            ),
            'amount' => $amount,
            'payment_type' => $payment_type,
            'reference' => $reference,
            'agency_payments_id' => $agency_payments_id,

            'join_table' => array('agency_payments_agencies'),

            'group_by' => 'agen_pay_a.agency_payments_id',

            'display_query' => 0
        );
        $query = $this->remittance_model->get_agency_payments($params);
        $total_rows = $query->num_rows();
        
        $agency_filter = '';
        $agency_payments_filter_str = '';
        if( count($agency) > 0 ){
            $agency_imp = implode(",",$agency);
            $agency_filter = "AND  agen_pay2_a.`agency_id` IN({$agency_imp})";
        }   
        
        if( $agency_payments_id > 0 ){
          $agency_payments_filter_str  = "AND agen_pay2.`agency_payments_id` = {$agency_payments_id}";          
        }

        $agency_pay_rollup_str = "
        SELECT 
            COUNT(agen_pay.agency_payments_id) AS agency_pay_count, 
            SUM(agen_pay.amount) AS agency_pay_amnt_sum, 
            SUM(agen_pay.allocated) AS agency_pay_alloc_sum,
            SUM(agen_pay.remaining) AS agency_pay_rem_sum                
        FROM agency_payments AS agen_pay
        WHERE agen_pay.agency_payments_id IN(            
                
            SELECT agen_pay2_a.agency_payments_id
            FROM agency_payments AS agen_pay2	
            INNER JOIN agency_payments_agencies AS agen_pay2_a ON agen_pay2.agency_payments_id = agen_pay2_a.agency_payments_id	
            WHERE agen_pay2.`remaining` > 0  
            {$agency_filter}   
            {$agency_payments_filter_str}
            GROUP BY agen_pay2_a.agency_payments_id

        )
        ";
        $agency_pay_sql_rollup_sql = $this->db->query($agency_pay_rollup_str);
        $agency_pay_row = $agency_pay_sql_rollup_sql->row();
        $data['agency_pay_count'] = $agency_pay_row->agency_pay_count; 
        $data['agency_pay_amnt_sum'] = $agency_pay_row->agency_pay_amnt_sum;       
        $data['agency_pay_alloc_sum'] = $agency_pay_row->agency_pay_alloc_sum;
        $data['agency_pay_rem_sum'] = $agency_pay_row->agency_pay_rem_sum;


        //pagination
        $pagi_links_params_arr = array(
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'payment_type' => $payment_type,
            'reference' => $reference,
            'btn_search' => $btn_search
        );
        $pagi_link_params = $uri.'?' . http_build_query($pagi_links_params_arr);

        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;
        $this->pagination->initialize($config);

        $data['agency_pay_pagination_link'] = $this->pagination->create_links();

        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['agency_pay_pagination_count'] = $this->jcclass->pagination_count($pc_params);

        $data['include_closed_pay'] = $include_closed_pay;
        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }



    public function ajax_edit_agency_payments_lb(){

        $this->load->model('remittance_model');
        $this->load->model('agency_model');

        $agency_payments_id = $this->input->get_post('agen_pay_id');

        if( $agency_payments_id > 0 ){
        
        // agency payment
        $sel_query = "
        agen_pay.agency_payments_id,
        agen_pay.date,
        agen_pay.amount,
        agen_pay.reference,
        agen_pay.payment_type,
        agen_pay.allocated,
        agen_pay.remaining,
        agen_pay.bank_deposit,
        agen_pay.remittance,

        pt.payment_type_id,
        pt.pt_name
        ";
        
        $params = array(
            'sel_query' => $sel_query,
            'agency_payments_id' => $agency_payments_id,
            'display_query' => 0
        );

        $agen_pay_sql = $this->remittance_model->get_agency_payments($params);
        $agen_pay_row = $agen_pay_sql->row();

        // all Agency filter
        $custom_where = "agency_id > 1";
        $a_params = array(
            'sel_query' => "agency_id,agency_name",   
            'custom_where' => $custom_where,          
            'country_id' => $this->config->item('country'),
            'sort_list' => array(
                array(
                    'order_by' => '`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        $all_agency_filter = $this->agency_model->get_agency($a_params);

        // get paymet types
        $pt_arr = $this->remittance_model->getPaymentTypes();
        ?>

       
        

            <h4>Agency Payment Details</h4>
            
            <table class="table agen_pay_det_tbl">
                <tbody id="pme_prop_details_tbl_fb">
                    <tr>                                                            
                        <td>
                            Reference<br />
                            <input type="text" class="form-control reference" placeholder="Text" value="<?php echo $agen_pay_row->reference; ?>">
                        </td>
                    </tr>                                                        
                    <tr>                                                            
                        <td>
                            Date<br />
                            <input type="text" data-allow-input="true" class="form-control flatpickr date" value="<?php echo ($this->system_model->isDateNotEmpty($agen_pay_row->date)==true)?$this->system_model->formatDate($agen_pay_row->date,'d/m/Y'):''; ?>" />
                        </td>
                    </tr>    
                    <tr>                                                            
                        <td>
                            Amount<br />
                            <input type="text" class="form-control amount"  value="<?php echo $agen_pay_row->amount; ?>" readonly />
                        </td>
                    </tr>                                             
                    <tr>                                                            
                        <td>
                            Agency<br />
                            <table class="table agen_pay_a_tbl">
                            <?php    
                            // get agencies
                            $sel_query = "
                            agen_pay_a.`agency_payments_agencies_id`,
                            agen_pay_a.`agency_payments_id`,
                        
                            a.`agency_id`,
                            a.`agency_name`
                            ";                                                                
                            $a_params = array(
                                'sel_query' => $sel_query,                                
                                'agency_payments_id' => $agen_pay_row->agency_payments_id,    
                                'join_table' => array('agency_payments_agencies'),     
                                'sort_list' => array(
                                    array(
                                        'order_by' => 'a.`agency_name`',
                                        'sort' => 'ASC'
                                    )
                                ),                          
                                'display_query' => 0
                            );
                            $agency_sql = $this->remittance_model->get_agency_payments($a_params);                           
                            foreach( $agency_sql->result() as $a_row ){ 
                            ?>
                                <tr class="agency_pay_det_agency_row_tr">
                                    <td class="agency_pay_det_agency_row">
                                        <select class="form-control ageny_pay_a_edit_agency">										
                                            <?php 
                                            foreach($all_agency_filter->result_array() as $agency_filter_row){
                                            ?>
                                                <option <?php echo ( $agency_filter_row['agency_id'] == $a_row->agency_id ) ? 'selected' : ''; ?> value="<?php echo $agency_filter_row['agency_id'] ?>"><?php echo $agency_filter_row['agency_name'] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>                                                                              
                                    </td>    
                                    <td class="remove_col">                                                                
                                        <a href="javascript:void(0);" class="remove_link">
                                            <span class="fa fa-remove del_agen_pay_a_btn"></span>
                                        </a>
                                        <input type="hidden" class="ageny_pay_a_orig_agency" value="<?php echo $a_row->agency_id; ?>" />
                                        <input type="hidden" class="ageny_pay_a_agency_is_edited" value="0" />
                                        <input type="hidden" class="ageny_pay_a_id" value="<?php echo $a_row->agency_payments_agencies_id; ?>" />
                                    </td>
                                </tr>
                            <?php               
                            }                                                                   
                            ?>                                                                  
                            <tr class="add_agency_tr">    
                                <td>&nbsp;</td>                                                                
                                <td>                                                                        
                                    <a href="javascript:void(0);">
                                        <span class="fa fa-plus add_agency_icon"></span>
                                    </a>
                                </td>
                            </tr>        
                            </table>
                        </td>
                    </tr>
                    <tr>                                                            
                        <td>
                            Payment Type<br />
                            <select class="form-control payment_type">	
                                <?php			
                                foreach( $pt_arr->result_array() as $pt ){                                                                        
                                ?>
                                    <option value="<?php echo $pt['payment_type_id']; ?>" <?php echo ( $pt['payment_type_id'] == $agen_pay_row->payment_type_id )?'selected="selected"':null ?>><?php echo $pt['pt_name'] ?></option>
                                <?php
                                }										
                                ?>									                                  
                            </select>
                        </td>
                    </tr>                                                       
                    <tr>                                                            
                        <td>
                            Allocated<br />
                            <input type="text" class="form-control allocated"  value="<?php echo $agen_pay_row->allocated; ?>" readonly="readonly" />
                        </td>
                    </tr>
                    <tr>                                                            
                        <td>
                            Invoice #<br />
                            
                            <?php    
                            // get jobs
                            $sel_query = "
                            agen_pay_j.`agency_payments_id`,            
                            agen_pay_j.`agency_payments_jobs_id`,
                            agen_pay_j.`active`,

                            j.`id` AS jid                                            
                            ";                                                             
                            $j_params = array(
                                'sel_query' => $sel_query,                                
                                'agency_payments_id' => $agen_pay_row->agency_payments_id,    
                                'join_table' => array('agency_payments_jobs'),                                
                                'display_query' => 0
                            );
                            $jobs_sql = $this->remittance_model->get_agency_payments($j_params);                           
                            foreach( $jobs_sql->result() as $j_row ){ 

                                $check_digit = $this->system_model->getCheckDigit(trim($j_row->jid));
                                $bpay_ref_code = "{$j_row->jid}{$check_digit}";	

                            ?>
                            <span class="invoice_num_span mr-1">
                                <a href='<?php echo $this->config->item("crm_link"); ?>/view_job_details.php?id=<?php echo $j_row->jid; ?>'>
                                    <span class="<?php echo  ($j_row->active == 0)?'del_strike':null;  ?>"><?php echo $bpay_ref_code; ?></span>
                                </a>
                                <?php
                                if( $j_row->active == 1 ){ ?>
                                    <a href="javascript:void(0);" class="remove_link">
                                        <span class="fa fa-remove del_agen_pay_j_btn"></span>
                                    </a>
                                <?php
                                }
                                ?>                                                                    
                                <input type="hidden" class="ageny_pay_j_id" value="<?php echo $j_row->agency_payments_jobs_id; ?>" />
                                <input type="hidden" class="jid" value="<?php echo $j_row->jid; ?>" />
                            </span>
                            <?php               
                            }                                                                   
                            ?>   
                            
                        </td>
                    </tr>
                    <tr>                                                            
                        <td>
                            Remaining<br />
                            <input type="text" class="form-control remaining"  value="<?php echo $agen_pay_row->remaining; ?>" readonly="readonly" />
                        </td>
                    </tr> 
                    <tr>                                                            
                        <td>
                            Bank Deposit<br />
                            <select class="form-control bank_deposit">
                                <option value="0" <?php echo ( is_numeric($agen_pay_row->bank_deposit) && $agen_pay_row->bank_deposit == 0 )?'selected="selected"':''; ?>>No</option>
                                <option value="1" <?php echo ( $agen_pay_row->bank_deposit == 1 )?'selected="selected"':''; ?>>Yes</option>                    
                            </select>	
                        </td>
                    </tr>
                    <tr>                                                            
                        <td>
                            Remittance<br />
                            <select class="form-control remittance">
                                <option value="0" <?php echo ( is_numeric($agen_pay_row->remittance) && $agen_pay_row->remittance == 0 )?'selected="selected"':''; ?>>No</option>
                                <option value="1" <?php echo ( $agen_pay_row->remittance == 1 )?'selected="selected"':''; ?>>Yes</option>  
                                <option value="2" <?php echo ( $agen_pay_row->remittance == 2 )?'selected="selected"':''; ?>>Not Needed</option>                  
                            </select>
                        </td>
                    </tr> 
                    <tr>
                        <td colspan="2" class="text-center">
                            <button class="btn btn-primary float-left agen_pay_update_btn" type="button">Update</button>
                            <button class="btn btn-danger float-right agen_pay_partial_refund_btn" type="button">Refund</button>
                            <?php
                            if( $jobs_sql->num_rows() == 0 ){ ?>
                                <button class="btn btn-danger float-right reverse_payment_btn" type="button">Reverse Payment</button>
                            <?php
                            }
                            ?>                                                                
                            <input type="hidden" class="agency_payments_id" value="<?php echo $agen_pay_row->agency_payments_id; ?>" />
                            <input type="hidden" class="bank_deposit_is_edited" value="0" />
                            <input type="hidden" class="remittance_is_edited" value="0" />
                        </td>
                    </tr>                                                                                                            
                </tbody>
            </table>

        
 

        <?php

        }

    }


    public function ajax_get_active_or_all_agency(){

        $this->load->model('agency_model');
        
        $show_inactive = $this->input->post('show_inactive');
        $custom_where = "agency_id > 1";

        $a_params = array(
            'sel_query' => "agency_id,agency_name",   
            'custom_where' => $custom_where,         
            'country_id' => $this->config->item('country'),
            'sort_list' => array(
                array(
                    'order_by' => '`agency_name`',
                    'sort' => 'ASC'
                )
            )
        );
        
        if( $show_inactive != 1 ){
            $a_params['a_status'] = 'active';
        }
        $agency_sql = $this->agency_model->get_agency($a_params);
      
        $option_str = '';
        foreach($agency_sql->result() as $agency_row){                
            $option_str .= '<option value="'.$agency_row->agency_id.'">'.$agency_row->agency_name.'</option>';
        }

        echo $option_str;
									

    }


    public function ajax_update_agency_payments() {

        $this->load->model('agency_model');
        $this->load->model('remittance_model');

        $agency_payments_id = $this->input->post('agency_payments_id');
        
        $date = ( $this->input->post('date') != '' ) ? $this->system_model->formatDate($this->input->post('date')) : NULL;
        $amount = $this->input->post('amount');        
        $payment_type = $this->input->post('payment_type');
        $reference = $this->input->post('reference');   
        $allocated = $this->input->post('allocated');           
        $bank_deposit = $this->input->post('bank_deposit');  
        $remittance = $this->input->post('remittance');  
        $bank_deposit_is_edited = $this->input->post('bank_deposit_is_edited');  
        $remittance_is_edited = $this->input->post('remittance_is_edited'); 

        $ageny_pay_a_id_arr = $this->input->post('ageny_pay_a_id_arr');
        $agency_new_arr = $this->input->post('agency_new_arr');
        $edit_agency_arr = $this->input->post('edit_agency_arr');
        $orig_agency_arr = $this->input->post('orig_agency_arr');
        $is_edited_arr = $this->input->post('is_edited_arr');

        $logged_user = $this->session->staff_id;
        $today = date('Y-m-d H:i:s');

        $remaining = ( $amount-$allocated );

        if( $agency_payments_id > 0 ){

            // update agency payments
            $update_sql_str = "
            UPDATE `agency_payments`
            SET 
                `reference` = ".$this->db->escape($reference).",
                `date` = ".$this->db->escape($date).",
                `amount` = ".$this->db->escape($amount).",
                `payment_type` = ".$this->db->escape($payment_type).",
                `remaining` = ".$this->db->escape($remaining).",
                `bank_deposit` = ".$this->db->escape($bank_deposit).",
                `remittance` = ".$this->db->escape($remittance)."                
            WHERE `agency_payments_id` = ".$this->db->escape($agency_payments_id)."
            ";
            $this->db->query($update_sql_str);

            // insert new agency payment agencies
            foreach( $agency_new_arr as $agency_id ){

                // insert
                $data = array(
                        'agency_payments_id' => $agency_payments_id,
                        'agency_id' => $agency_id,
                        'created_date' => $today
                );                
                $this->db->insert('agency_payments_agencies', $data);

                // insert agency logs    
                $log_title = 47; // Agency Payment                        
                $log_details = "Agency added to payment on <strong>{$this->input->post('date')}</strong> of <strong>\$".$this->system_model->currency_format($amount)."</strong> Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";

                // insert agency logs
                $log_params_agency = array(
                    'title' => $log_title,
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $logged_user,
                    'agency_id' => $agency_id
                );

                $this->system_model->insert_log($log_params_agency);                


            }


            // update existing agency payment agencies
            foreach( $ageny_pay_a_id_arr as $index => $ageny_pay_a_id ){

                if( $is_edited_arr[$index] == 1 ){ // is edited

                    if( $ageny_pay_a_id > 0 ){                   

                        // update
                        $this->db->query("
                        UPDATE `agency_payments_agencies`
                        SET `agency_id` = {$edit_agency_arr[$index]}
                        WHERE `agency_payments_id` = {$agency_payments_id}                
                        AND `agency_payments_agencies_id` = {$ageny_pay_a_id}                        
                        ");


                        if( $this->db->affected_rows() > 0 ){ // update success

                            // get old agency name                         
                            $a_params = array(
                                'sel_query' => "agency_id,agency_name",                                                                            
                                'agency_id' => $orig_agency_arr[$index],
                            );
                            $agency_sql = $this->agency_model->get_agency($a_params);
                            $agency_row = $agency_sql->row();
                            $old_agency_name = $agency_row->agency_name;

                            // get edited agency name                         
                            $a_params = array(
                                'sel_query' => "agency_id,agency_name",                                                        
                                'agency_id' => $edit_agency_arr[$index],
                            );
                            $agency_sql = $this->agency_model->get_agency($a_params);
                            $agency_row = $agency_sql->row();
                            $edited_agency_name = $agency_row->agency_name;

                            // insert agency logs    
                            $log_title = 47; // Agency Payment                        
                            /*$log_details = "Agency payment of <strong>\$".$this->system_model->currency_format($amount)."</strong> received on <strong>{$this->input->post('date')}</strong> 
                            updated from <a href='{$this->config->item("crm_link")}/view_agency_details.php?id={$orig_agency_arr[$index]}'>{$old_agency_name}</a> 
                            to <a href='{$this->config->item("crm_link")}/view_agency_details.php?id={$edit_agency_arr[$index]}'>{$edited_agency_name}</a>. 
                            Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";
                            */

                            $log_details = "Agency payment of <strong>\$".$this->system_model->currency_format($amount)."</strong> received on <strong>{$this->input->post('date')}</strong> 
                            updated from <a href='/agency/view_agency_details/{$orig_agency_arr[$index]}'>{$old_agency_name}</a> 
                            to <a href='/agency/view_agency_details/{$edit_agency_arr[$index]}'>{$edited_agency_name}</a>. 
                            Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";

                            // insert agency logs
                            $log_params_agency = array(
                                'title' => $log_title,
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $logged_user,
                                'agency_id' => $orig_agency_arr[$index]
                            );

                            $this->system_model->insert_log($log_params_agency);

                            // insert agency logs
                            $log_params_agency = array(
                                'title' => $log_title,
                                'details' => $log_details,
                                'display_in_vad' => 1,
                                'created_by_staff' => $logged_user,
                                'agency_id' => $edit_agency_arr[$index]
                            );

                            $this->system_model->insert_log($log_params_agency);


                            if( $bank_deposit_is_edited == 1 ){

                                // insert agency logs    
                                $log_title = 47; // Agency Payment                        
                                $log_details = "Agency Payment’s Bank Deposit has been changed to: <strong>{$this->remittance_model->get_agen_pay_bank_deposit($bank_deposit)}</strong>, Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";
            
                                // insert agency logs
                                $log_params_agency = array(
                                    'title' => $log_title,
                                    'details' => $log_details,
                                    'display_in_vad' => 1,
                                    'created_by_staff' => $logged_user,
                                    'agency_id' => $edit_agency_arr[$index]
                                );
                                $this->system_model->insert_log($log_params_agency);
            
                            }
            
                            if( $remittance_is_edited == 1 ){
            
                                // insert agency logs    
                                $log_title = 47; // Agency Payment                        
                                $log_details = "Agency Payment’s Remittance has been changed to: <strong>{$this->remittance_model->get_agen_pay_remittance($remittance)}</strong>, Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";
            
                                // insert agency logs
                                $log_params_agency = array(
                                    'title' => $log_title,
                                    'details' => $log_details,
                                    'display_in_vad' => 1,
                                    'created_by_staff' => $logged_user,
                                    'agency_id' => $edit_agency_arr[$index]
                                );
                                $this->system_model->insert_log($log_params_agency);
            
                            }

                        }

                        

                    }                    

                }else{ // unedited


                    if( $bank_deposit_is_edited == 1 ){

                        // insert agency logs    
                        $log_title = 47; // Agency Payment                        
                        $log_details = "Agency Payment’s Bank Deposit has been changed to: <strong>{$this->remittance_model->get_agen_pay_bank_deposit($bank_deposit)}</strong>, Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";
    
                        // insert agency logs
                        $log_params_agency = array(
                            'title' => $log_title,
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $logged_user,
                            'agency_id' => $orig_agency_arr[$index]
                        );
                        $this->system_model->insert_log($log_params_agency);
    
                    }
    
                    if( $remittance_is_edited == 1 ){
    
                        // insert agency logs    
                        $log_title = 47; // Agency Payment                        
                        $log_details = "Agency Payment’s Remittance has been changed to: <strong>{$this->remittance_model->get_agen_pay_remittance($remittance)}</strong>, Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";
    
                        // insert agency logs
                        $log_params_agency = array(
                            'title' => $log_title,
                            'details' => $log_details,
                            'display_in_vad' => 1,
                            'created_by_staff' => $logged_user,
                            'agency_id' => $orig_agency_arr[$index]
                        );
                        $this->system_model->insert_log($log_params_agency);
    
                    }

                    

                } 
               

               


            }


        }        

    }


    public function ajax_agency_payments_delete_agencies() {

        $ageny_pay_a_id = $this->input->post('ageny_pay_a_id');

        $agency_id = $this->input->post('agency_id');
        $amount = $this->input->post('amount');
        $reference = $this->input->post('reference');
        $date = $this->input->post('date');
        $agency_payments_id = $this->input->post('agency_payments_id');

        $logged_user = $this->session->staff_id;
        $today = date('Y-m-d H:i:s');


        if( $ageny_pay_a_id > 0 ){

            echo $delete_sql_str = "
            DELETE
            FROM `agency_payments_agencies`
            WHERE `agency_payments_agencies_id` = {$ageny_pay_a_id}
            ";

            $this->db->query($delete_sql_str);

            // insert agency logs    
            $log_title = 47; // Agency Payment                        
            $log_details = "Agency removed from payment on <strong>{$date}</strong> of <strong>\$".$this->system_model->currency_format($amount)."</strong> Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";

            // insert agency logs
            $log_params_agency = array(
                'title' => $log_title,
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $logged_user,
                'agency_id' => $agency_id
            );

            $this->system_model->insert_log($log_params_agency);

        }        

    }

    public function ajax_reverse_agency_payments() {

        $this->load->model('remittance_model');

        $agency_payments_id = $this->input->post('agency_payments_id');
        $reference = $this->input->post('reference');
        $amount = $this->input->post('amount');
        $date = $this->input->post('date');
        $rev_pay_reason = $this->input->post('rev_pay_reason');

        $logged_user = $this->session->staff_id;
        $today = date('Y-m-d H:i:s');


        if( $agency_payments_id > 0 ){                    


            // get agency payment agencies
            $sel_query = "
            agen_pay_a.`agency_payments_agencies_id`,
            agen_pay_a.`agency_payments_id`,
        
            a.`agency_id`,
            a.`agency_name`
            ";                                                                
            $a_params = array(
                'sel_query' => $sel_query,                                
                'agency_payments_id' => $agency_payments_id,    
                'join_table' => array('agency_payments_agencies'),     
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC'
                    )
                ),                          
                'display_query' => 0
            );
            $agency_sql = $this->remittance_model->get_agency_payments($a_params); 
            
            foreach( $agency_sql->result() as $a_row ){

                // insert agency logs    
                $log_title = 47; // Agency Payment            
                //$log_details = "Agency Payments Ref: <strong>{$reference}</strong> has been reversed";
                $log_details = "Reversed Agency Payment of <strong>\${$amount}</strong> received on <strong>{$date}</strong>. Ref: <strong>{$reference}</strong>. Reason: <strong>{$rev_pay_reason}</strong>";
    
                // insert agency logs
                $log_params_agency = array(
                    'title' => $log_title,
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $logged_user,
                    'agency_id' => $a_row->agency_id
                );

                $this->system_model->insert_log($log_params_agency);

            }  


            // delete agency payments
            echo $delete_sql_str = "
            DELETE
            FROM `agency_payments`
            WHERE `agency_payments_id` = {$agency_payments_id}
            ";
            $this->db->query($delete_sql_str);    
           

            // delete agency payments agencies
            echo $delete_sql_str = "
            DELETE
            FROM `agency_payments_agencies`
            WHERE `agency_payments_id` = {$agency_payments_id}
            ";

            $this->db->query($delete_sql_str);

        }        

    }


    public function ajax_partial_refund_agency_payments() {

        $this->load->model('remittance_model');

        $agency_payments_id = $this->input->post('agency_payments_id');
        $reference = $this->input->post('reference');
        $amount = $this->input->post('amount');
        $date = $this->input->post('date');
        $partial_refund_amount = $this->input->post('partial_refund_amount');

        $logged_user = $this->session->staff_id;
        $today = date('Y-m-d H:i:s');


        if( $agency_payments_id > 0 ){                    


            // get agency payment agencies
            $sel_query = "
            agen_pay_a.`agency_payments_agencies_id`,
            agen_pay_a.`agency_payments_id`,
        
            a.`agency_id`,
            a.`agency_name`
            ";                                                                
            $a_params = array(
                'sel_query' => $sel_query,                                
                'agency_payments_id' => $agency_payments_id,    
                'join_table' => array('agency_payments_agencies'),     
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC'
                    )
                ),                          
                'display_query' => 0
            );
            $agency_sql = $this->remittance_model->get_agency_payments($a_params); 
            
            foreach( $agency_sql->result() as $a_row ){

                // insert agency logs    
                $log_title = 47; // Agency Payment                            
                $log_details = "Partially refunded <strong>\$".$this->system_model->currency_format($partial_refund_amount)."</strong> to Agency Payment of <strong>\${$amount}</strong> received on <strong>{$date}</strong>. Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";
    
                // insert agency logs
                $log_params_agency = array(
                    'title' => $log_title,
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $logged_user,
                    'agency_id' => $a_row->agency_id
                );

                $this->system_model->insert_log($log_params_agency);

            }  


            // partial refund
            if( $agency_payments_id > 0 && $partial_refund_amount > 0 ){

                $this->db->query("
                    UPDATE `agency_payments` 
                    SET 
                        `amount` = (`amount`-{$partial_refund_amount}),
                        `remaining` = (`remaining`-{$partial_refund_amount})
                    WHERE `agency_payments_id` = {$agency_payments_id}
                ");

            }

        }        

    }


    // ajax bank deposit inline update
    public function ajax_agen_pay_bank_deposit_inline_update(){

        $this->load->model('remittance_model');

        $agency_payments_id = $this->input->post('agency_payments_id');
        $bank_deposit = $this->input->post('bank_deposit');
        $reference = $this->input->post('reference');

        $logged_user = $this->session->staff_id;       
        $today = date('Y-m-d H:i:s'); 

        if( $agency_payments_id > 0  ){

            $this->db->query("
                UPDATE `agency_payments` 
                SET 
                    `bank_deposit` = {$bank_deposit}
                WHERE `agency_payments_id` = {$agency_payments_id}
            ");

            // get agency payment agencies
            $sel_query = "
            agen_pay_a.`agency_payments_agencies_id`,
            agen_pay_a.`agency_payments_id`,
        
            a.`agency_id`,
            a.`agency_name`
            ";                                                                
            $a_params = array(
                'sel_query' => $sel_query,                                
                'agency_payments_id' => $agency_payments_id,    
                'join_table' => array('agency_payments_agencies'),                            
                'display_query' => 0
            );
            $agency_sql = $this->remittance_model->get_agency_payments($a_params); 

            foreach( $agency_sql->result() as $a_row ){

                // insert agency logs    
                $log_title = 47; // Agency Payment                        
                $log_details = "Agency Payment’s Bank Deposit has been changed to: <strong>{$this->remittance_model->get_agen_pay_bank_deposit($bank_deposit)}</strong>, Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";

                // insert agency logs
                $log_params_agency = array(
                    'title' => $log_title,
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $logged_user,
                    'agency_id' => $a_row->agency_id
                );
                $this->system_model->insert_log($log_params_agency);

            }        

        }

    }

    // ajax bank remittance inline update
    public function ajax_agen_pay_remittance_inline_update(){

        $this->load->model('remittance_model');

        $agency_payments_id = $this->input->post('agency_payments_id');
        $remittance = $this->input->post('remittance');
        $reference = $this->input->post('reference');

        $logged_user = $this->session->staff_id;       
        $today = date('Y-m-d H:i:s'); 

        if( $agency_payments_id > 0  ){

            $this->db->query("
                UPDATE `agency_payments` 
                SET 
                    `remittance` = {$remittance}
                WHERE `agency_payments_id` = {$agency_payments_id}
            ");

            // get agency payment agencies
            $sel_query = "
            agen_pay_a.`agency_payments_agencies_id`,
            agen_pay_a.`agency_payments_id`,
        
            a.`agency_id`,
            a.`agency_name`
            ";                                                                
            $a_params = array(
                'sel_query' => $sel_query,                                
                'agency_payments_id' => $agency_payments_id,    
                'join_table' => array('agency_payments_agencies'),                            
                'display_query' => 0
            );
            $agency_sql = $this->remittance_model->get_agency_payments($a_params); 

            foreach( $agency_sql->result() as $a_row ){

                // insert agency logs    
                $log_title = 47; // Agency Payment                        
                $log_details = "Agency Payment’s Remittance has been changed to: <strong>{$this->remittance_model->get_agen_pay_remittance($remittance)}</strong>, Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";

                // insert agency logs
                $log_params_agency = array(
                    'title' => $log_title,
                    'details' => $log_details,
                    'display_in_vad' => 1,
                    'created_by_staff' => $logged_user,
                    'agency_id' => $a_row->agency_id
                );
                $this->system_model->insert_log($log_params_agency);

            } 

        }

    }


    public function ajax_agency_payments_delete_jobs() {

        $agency_payments_id = $this->input->post('agency_payments_id');
        $ageny_pay_j_id = $this->input->post('ageny_pay_j_id');
        $job_id = $this->input->post('jid');
        $remove_invoice_reason = $this->input->post('remove_invoice_reason');
        $transfer_invoice = $this->input->post('transfer_invoice');
        $transfer_job_id = $this->input->post('transfer_job_id');

        $logged_user = $this->session->staff_id;       
        $today = date('Y-m-d H:i:s'); 

        if( $ageny_pay_j_id > 0 ){

            // get invoice payments deleted from agency payments
            $inv_pay_sql_str = "
            SELECT
                inv_pay.`invoice_payment_id`,
                inv_pay.`amount_paid`,
                inv_pay.`type_of_payment`,                    
                inv_pay.`payment_reference`,  
                inv_pay.`agen_pay_j_id`,
                
                pt.`payment_type_id`,
                pt.`pt_name`
            FROM `invoice_payments` AS inv_pay
            LEFT JOIN payment_types AS pt ON inv_pay.`type_of_payment` = pt.`payment_type_id`
            WHERE inv_pay.`job_id` = {$job_id}
            AND inv_pay.`agen_pay_j_id` = {$ageny_pay_j_id}
            ";
            $inv_pay_sql = $this->db->query($inv_pay_sql_str);            
            $inv_pay_row = $inv_pay_sql->row();

            if( isset($inv_pay_row) ){

                // transfer
                if( $transfer_invoice == 1 ){

                    $payment_reference = "TRANSFERRED: {$inv_pay_row->payment_reference}";  


                    // insert agency payments jobs
                    $trans_data = array(
                        'agency_payments_id' => $agency_payments_id,
                        'job_id' => $transfer_job_id,
                        'created_date' => date('Y-m-d H:i:s'),
                    );
                    
                    $this->db->insert('agency_payments_jobs', $trans_data);
                    $agency_payments_jobs_id = $this->db->insert_id();
                    
                    // transfer payment                                      
                    $invoice_payments_insert_data = array(
                        'job_id' => $transfer_job_id,
                        'payment_date' => date('Y-m-d'),
                        'amount_paid' => $inv_pay_row->amount_paid,
                        'type_of_payment' => $inv_pay_row->type_of_payment,
                        'created_by' => $logged_user,
                        'created_date' => $today,
                        'payment_reference' => $payment_reference,
                        'agen_pay_j_id' => $agency_payments_jobs_id                    
                    );
                    $this->db->insert('invoice_payments', $invoice_payments_insert_data);
                    // AUTO - UPDATE INVOICE DETAILS
                    $this->system_model->updateInvoiceDetails($transfer_job_id); 

                    // transfer account logs            
                    $log_title = 43; // Payment    
                    $acc_log = "Transferred <strong>\${$inv_pay_row->amount_paid}</strong> from <a href='{$this->config->item("crm_link")}/view_job_details.php?id={$job_id}'>{$job_id}</a>, because <strong>{$remove_invoice_reason}</strong>. Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$payment_reference}</a>";                        
                    $log_params = array(
                        'title' => $log_title,
                        'details' => $acc_log,
                        'display_in_accounts' => 1,
                        'created_by_staff' => $logged_user,
                        'job_id' => $transfer_job_id
                    );
                    $this->system_model->insert_log($log_params);
                    
                    
                    // reverse account logs            
                    $log_title = 43; // Payment    
                    $acc_log = "Transferred <strong>\${$inv_pay_row->amount_paid}</strong> to <a href='{$this->config->item("crm_link")}/view_job_details.php?id={$transfer_job_id}'>{$transfer_job_id}</a>, because <strong>{$remove_invoice_reason}</strong>. Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$payment_reference}</a>";                        
                    $log_params = array(
                        'title' => $log_title,
                        'details' => $acc_log,
                        'display_in_accounts' => 1,
                        'created_by_staff' => $logged_user,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);                    
                    
                }else{ // reverse

                    $payment_reference = "REVERSED: {$inv_pay_row->payment_reference}";
                    
                    //insert account logs            
                    $log_title = 43; // Payment    
                    $acc_log = "Payment of <strong>\${$inv_pay_row->amount_paid}</strong> reversed due to: <strong>{$remove_invoice_reason}</strong>. Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$payment_reference}</a>";                        
                    $log_params = array(
                        'title' => $log_title,
                        'details' => $acc_log,
                        'display_in_accounts' => 1,
                        'created_by_staff' => $logged_user,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);

                     // reverse - update agency payments allocated and remaining
                    if( $agency_payments_id > 0 && $inv_pay_row->amount_paid > 0 ){

                        $this->db->query("
                            UPDATE `agency_payments` 
                            SET 
                                `allocated` = (`allocated`-{$inv_pay_row->amount_paid}),
                                `remaining` = (`remaining`+{$inv_pay_row->amount_paid})
                            WHERE `agency_payments_id` = {$agency_payments_id}
                        ");

                    }


                }

                      
                // reverse payment
                $invoice_payments_insert_data = array(
                'job_id' => $job_id,
                'payment_date' => date('Y-m-d'),
                'amount_paid' => "-{$inv_pay_row->amount_paid}",
                'type_of_payment' => $inv_pay_row->type_of_payment,
                'created_by' => $logged_user,
                'created_date' => $today,
                'payment_reference' => $payment_reference                    
                );
                $this->db->insert('invoice_payments', $invoice_payments_insert_data);
                // AUTO - UPDATE INVOICE DETAILS
                $this->system_model->updateInvoiceDetails($job_id);  
                               

                // soft delete agency payments jobs
                $delete_sql_str = "
                UPDATE `agency_payments_jobs`
                SET `active` = 0
                WHERE `agency_payments_jobs_id` = {$ageny_pay_j_id}
                ";
                $this->db->query($delete_sql_str); 
                    
            }                                 

        }        

        

    }


    public function ajax_get_tranfered_to_jobs_invoice_balance(){

        $this->load->model('jobs_model');
        
        $job_id = $this->input->post('job_id');
        $agency_arr = $this->input->post('agency_arr');

        if( $agency_arr > 0 ){

            $agency_arr = implode(",",$agency_arr);

            if( $job_id > 0 ){
                            
                $sel_query = "j.`invoice_balance`";
                $custom_where = "a.`agency_id` IN({$agency_arr})";

                $params = array(
                    'sel_query' => $sel_query,
                    'custom_where' => $custom_where,
                    
                    'del_job' => 0,
                    'p_deleted' => 0,
                    'a_status' => 'active',            
                    'job_id' => $job_id,
                    'display_query' => 0
                );

                $sql = $this->jobs_model->get_jobs($params);

                if( $sql->num_rows() > 0 ){

                    
                    $row = $sql->row();
                    echo $row->invoice_balance;  
                     

                }                                   

            } 

        }               

    }


    public function ajax_save_agency_payments() {

        $multi_agency = $this->input->post('multi_agency');
        
        $date = ( $this->input->post('date') != '' ) ? $this->system_model->formatDate($this->input->post('date')) : NULL;
        $amount = $this->input->post('amount');        
        $payment_type = $this->input->post('payment_type');
        $reference = $this->input->post('reference');

        $bank_deposit = $this->input->post('bank_deposit');
        $remittance = $this->input->post('remittance');

        $logged_user = $this->session->staff_id;
        $today = date('Y-m-d H:i:s');


        // insert `agency_payments`
        $agency_payments_paramns = array(
            'date' => $date,
            'amount' => $amount,
            'reference' => $reference,            
            'payment_type' => $payment_type,
            'remaining' => $amount,

            'bank_deposit' => $bank_deposit,
            'remittance' => $remittance,

            'created_date' => $today
        );
        $this->db->insert('agency_payments', $agency_payments_paramns);

        $agency_payments_id = $this->db->insert_id();

        foreach( $multi_agency as $agency_id ){

            // insert `agency_payments_agencies`
            $agency_payments_paramns = array(
                'agency_payments_id' => $agency_payments_id,            
                'agency_id' => $agency_id,
                'created_date' => $today
            );
            $this->db->insert('agency_payments_agencies', $agency_payments_paramns);

            // insert agency logs    
            $log_title = 47; // Agency Payment            
            //$log_details = "Agency Payments Ref: <strong>{$reference}</strong> has been created";
            $log_details = "Added Agency Payment of <strong>\$".$this->system_model->currency_format($amount)."</strong> received on <strong>{$this->input->post('date')}</strong>. Ref: <a href='".BASEURL."accounts/agency_payments/?agency_payments_id={$agency_payments_id}&open_edit_lb=1'>{$reference}</a>";

            // insert agency logs
            $log_params_agency = array(
                'title' => $log_title,
                'details' => $log_details,
                'display_in_vad' => 1,
                'created_by_staff' => $logged_user,
                'agency_id' => $agency_id
            );

            $this->system_model->insert_log($log_params_agency);

        }   
        
        


    }


}
