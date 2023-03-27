<?php

class Expensesummary_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function getButtonExpenseSummary($params) {
        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } elseif ($params['return_count'] == 1) {
            $sel_query = " COUNT(*) AS jcount ";
        } else {
            $sel_query = '*';
        }
        $this->db->select($sel_query);
        $this->db->from("`expense_summary` AS exp_sum");
        $this->db->join("`staff_accounts` AS sa", "exp_sum.`employee` = sa.`StaffID`", "LEFT");
        $this->db->join("`staff_accounts` AS sa_who", "exp_sum.`who` = sa_who.`StaffID`", "LEFT");
        $this->db->join("`staff_accounts` AS lm", "exp_sum.`line_manager` = lm.`StaffID`", "LEFT");
        $this->db->join("`expenses` AS exp", "exp.`expense_summary_id` = exp_sum.`expense_summary_id`", "LEFT"); //added by gherx
        if ($params['join_table'] != '') {
            if ($params['join_table'] == 'expenses') {
                $this->db->join("`expenses` AS exp", "exp.`expense_summary_id` = exp_sum.`expense_summary_id`", "RIGHT");
            }
        }
        $this->db->where("exp_sum.`active`=1");
        $this->db->where("exp_sum.`deleted`=0");
        if ($params['country_id'] != "") {
            $this->db->where("exp_sum.`country_id` = '{$params['country_id']}' ");
        }
        if ($params['exp_sum_id'] != "") {
            $this->db->where("exp_sum.`expense_summary_id` = '{$params['exp_sum_id']}' ");
        }

        if ($params['employee'] != "") {
            $this->db->where(" exp_sum.`employee` = '{$params['employee']}' ");
        }

        if ($params['date_reimbursed_is_null'] == 1) {
            $this->db->where(" exp_sum.`date_reimbursed` IS NULL ");
        }

        if ($params['line_manager'] != '') {
            $this->db->where(" exp_sum.`line_manager` = {$params['line_manager']} ");
        }

        if ($params['card'] != '') {
            $this->db->where(" exp.`card` = {$params['card']} ");
        }

        if ($params['exp_sum_status'] != '') {

            if ($params['exp_sum_status'] == -2) {
                // all
            } else if ($params['exp_sum_status'] == -1) {
                $this->db->where(" ( exp_sum.`exp_sum_status` IS NULL ) ");
            } else {
                $this->db->where(" exp_sum.`exp_sum_status` = {$params['exp_sum_status']} ");
            }
        }
        if (isset($params['filterDate']) && isset($params['filterDate']['from']) && isset($params['filterDate']['to']) && !empty($params['filterDate']['from']) && !empty($params['filterDate']['to'])) {
            $this->db->where("( exp_sum.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ) ");
        }

        // group by
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
        }

        if (isset($params['sort_list']) && $params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
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
       // return $query->result_array();
        return $query;
    }

    public function getStaffAccountsByCountryId($countryId) {
        $this->db->select("DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`");
        $this->db->from("staff_accounts AS sa");
        $this->db->join("`country_access` AS ca", "(sa.`StaffID` = ca.`staff_accounts_id` AND ca.`country_id` ={$countryId})", "INNER");
        $this->db->where("sa.deleted=0");
        $this->db->where("sa.active=1");
        $this->db->order_by("sa.`FirstName`", "ASC");
        return $this->db->get();
    }

    public function updateLineManager($line_manager, $staff_id, $exp_sum_id) {
        $this->db->set(['line_manager' => $line_manager, 'who' => $staff_id]);
        $this->db->where('expense_summary_id', $exp_sum_id);
        $this->db->update('expense_summary');
    }

    public function updateStatus($status, $staff_id, $exp_sum_id) {
        $this->db->set(['exp_sum_status' => $status, 'who' => $staff_id]);
        $this->db->where('expense_summary_id', $exp_sum_id);
        $this->db->update('expense_summary');
    }

    public function updateDateReimbursed($date_reimbursed, $staff_id, $exp_sum_id) {
        $this->db->set(['date_reimbursed' => $date_reimbursed, 'who' => $staff_id]);
        $this->db->where('expense_summary_id', $exp_sum_id);
        $this->db->update('expense_summary');
    }

    public function getExpenses($params) {
        $sel_str = "  
			*,emp.`StaffID` AS emp_staff_id,
			emp.`FirstName` AS emp_fname, 
			emp.`LastName` AS emp_lname,
			eb.`StaffID` AS eb_staff_id,
			eb.`FirstName` AS eb_fname, 
			eb.`LastName` AS eb_lname 			
		";

        if (isset($params['sel_query'])) {
            $sel_str = $params['sel_query'];
        }
        $this->db->select($sel_str);
        $this->db->from("`expenses` AS exp");
        $this->db->join("`expense_account` AS exp_acc", "exp.`account` = exp_acc.`expense_account_id`", "LEFT");
        $this->db->join("staff_accounts` AS emp", "exp.`employee` = emp.`StaffID`", "LEFT");
        $this->db->join("`staff_accounts` AS eb", "exp.`entered_by` = eb.`StaffID`", "LEFT");
        $this->db->where(" exp.`active` = 1 ");
        $this->db->where(" exp.`deleted` = 0 ");


        if ($params['employee'] != "") {
            $this->db->where(" exp.`employee` = '{$params['employee']}' ");
        }

        if ($params['expense_id'] != "") {
            $this->db->where(" exp.`expense_id` = '{$params['expense_id']}' ");
        }

        if ($params['entered_by'] != "") {
            $this->db->where(" exp.`entered_by` = '{$params['entered_by']}' ");
        }

        if ($params['exp_sum_id'] != "") {
            $this->db->where(" exp.`expense_summary_id` = '{$params['exp_sum_id']}' ");
        }

        // exclude submitted expenses
        if ($params['exc_sub_exp'] == 1) {
            $this->db->where(" exp.`expense_summary_id` IS NULL ");
        }

        if ($params['date'] != "") {
            $this->db->where(" exp.`date` = '{$params['date']}' ");
        }

        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $this->db->where(" exp.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ");
            }
        }

        if ($params['sort_list'] != '') {

            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    //$sort_str_arr[] = $sort_arr['order_by'] . ' ' . $sort_arr['sort'];
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        if ($params['paginate'] != "") {
            $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
        }

        $query = $this->db->get();
        if (isset($params['echo_query']) && (int) $params['echo_query'] === 1) {
            echo $this->db->last_query();
        }
        return $query;
    }

    function getExpenseCards($card_id) {

        switch ($card_id) {
            case 1:
                $card = 'Company Card';
                break;
            case 2:
                $card = 'Personal Card';
                break;
            case 3:
                $card = 'AU Main Card';
                break;
            case 4:
                $card = 'NZ Main Card';
                break;
            case 5:
                $card = 'Cash';
                break;
        }

        return $card;
    }

    function getDynamicGST($val, $country_id) {

        switch ($country_id) {
            case 1:
                $gst = $val / 11;
                break;
            case 2:
                $gst = ($val * 3) / 23;
                break;
        }

        return $gst;
    }

    public function getExpenseAccount($params) {

        $sel_str = " SELECT * ";
        if (isset($params['sel_query'])) {
            $sel_str = $params['sel_query'];
        } else {
            $sel_str = "*";
        }
        $this->db->select($sel_str);
        $this->db->from("`expense_account` AS exp_acc");
        $this->db->where(" exp_acc.`active` = 1 ");
        $this->db->where(" exp_acc.`deleted` = 0 ");
        if ($params['sort_list'] != '') {

            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    //$sort_str_arr[] = $sort_arr['order_by'] . ' ' . $sort_arr['sort'];
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        if ($params['paginate'] != "") {
            $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
        }

        $query = $this->db->get();
        if (isset($params['echo_query']) && (int) $params['echo_query'] === 1) {
            echo $this->db->last_query();
        }
        return $query;
    }

    public function add_expense($params) {
        $this->db->insert('expenses', $params);
        return $this->db->insert_id();
    }

    public function update_expense($exp_sum_id, $exp_id) {
        $this->db->set(['expense_summary_id' => $exp_sum_id]);
        $this->db->where('expense_id', $exp_id);
        $this->db->update('expenses');
    }

    public function update_expense_record($update_params, $exp_id) {
        $this->db->set($update_params);
        $this->db->where('expense_id', $exp_id);
        $this->db->update('expenses');
    }

    public function update_expense_summary_record($update_params, $expense_summary_id, $escape = true) {
        foreach ($update_params as $key => $value) {
            $this->db->set($key, $value, $escape);
        }

        $this->db->where('expense_summary_id', $expense_summary_id);
        $this->db->update('expense_summary');
    }

    public function delete_expense_record($exp_id) {
        $this->db->where('expense_id', $exp_id);
        $this->db->delete('expenses');
    }

    public function add_expense_summary($params) {
        $this->db->insert('expense_summary', $params);
        return $this->db->insert_id();
    }

    public function get_expense_summary_pdf($params, $output = 'I') {
        $exp_sql = $this->getExpenses($params);
        $cntry_query = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));
        // start fpdf
        $pdf = new ExpenseSummaryPdf('L', 'mm', 'A4');
        $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
        $pdf->setCountryData($cntry_query);



        $jparams = array(
            'sort_list' => array(
                [
                    'order_by' => 'exp_sum.`date`',
                    'sort' => 'DESC'
                ]
            ),
            'exp_sum_id' => $params['exp_sum_id'],
            'country_id' => $params['country_id']
        );
        $jparams['sel_query'] = '
                exp_sum.expense_summary_id,
                exp_sum.date,
                exp_sum.date_reimbursed,
                exp_sum.exp_sum_status AS exp_sum_status,
                sa.`FirstName` AS sa_fname, 
                sa.`LastName` AS sa_lname, 
                sa_who.`FirstName` AS sa_who_fname, 
                sa_who.`LastName` AS sa_who_lname,
                lm.`FirstName` AS lm_fname, 
                lm.`LastName` AS lm_lname,
                lm.StaffId as line_manager
		';
        $exp_sum_sql = $this->getButtonExpenseSummary($jparams)->result_array();
        $exp_sum = $exp_sum_sql[0];
        $pdf->exp_sum = $exp_sum;
        $pdf->AliasNbPages();
        $pdf->SetTopMargin(18);
        $pdf->SetAutoPageBreak(true, 30);
        $pdf->AddPage();


        $cell_width = 27.5;
        $cell_height = 5;          
        $font_size = 8;
        $heading2_col1 = 33;
        $col1 = 17; // Date
        $col1_ins = 22; // Card
        $col2 = 38; // Supplier
        $col3 = 75; // Description
        $col4 = 33; // Account
        $col5 = 20; // Amount
        $col6 = 20; // Net Amt
        $col7 = 20; // GST
        $col8 = 20; // Gross Am


        $pdf->SetFont('Arial', 'B', $font_size);
        $pdf->Cell($col1, $cell_height, 'Date', 1);
        $pdf->Cell($col1_ins, $cell_height, 'Card', 1);
        $pdf->Cell($col2, $cell_height, 'Supplier', 1);
        $pdf->Cell($col3, $cell_height, 'Description', 1);
        $pdf->Cell($col4, $cell_height, 'Account', 1);
        $pdf->Cell($col5, $cell_height, 'Amount', 1);
        // grey
        $pdf->SetFillColor(192, 192, 192);
        $pdf->Cell($col6, $cell_height, 'Net Amt', 1, null, null, true);
        $pdf->Cell($col7, $cell_height, 'GST', 1, null, null, true);
        $pdf->Cell($col8, $cell_height, 'Gross Amt', 1, null, null, true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', $font_size);

        $amount_tot = 0;
        $net_amount_tot = 0;
        $gst_tot = 0;
        $amount_reimbursed = 0;
        $recordCounter = 0;
//        var_dump($exp_sql->result_array());
        foreach ($exp_sql->result_array() as $exp) {
//            if ($recordCounter >= 20) {
//                $pdf->SetTopMargin(48);
////                $pdf->SetAutoPageBreak(true, 10);
//                $pdf->AddPage();
//                $recordCounter = 0;
//            }
//            $recordCounter++;

            $cell_height = 5;
            $char_count = strlen($exp['description']);
            $desc_lines = ceil($char_count / 61);

            $char_count_supp = strlen($exp['supplier']);
            $suppier_lines = ceil($char_count_supp / 26);

            if (strlen($exp['description']) > 60) {
                $cell_height = $cell_height * $desc_lines;
            }elseif(strlen($exp['supplier']) > 25){
                $cell_height = $cell_height * $suppier_lines;
            }

            $pdf->Cell($col1, $cell_height, date('d/m/Y', strtotime($exp['date'])), 1);
            $pdf->Cell($col1_ins, $cell_height, $this->getExpenseCards($exp['card']), 1);

            //supplier start
            $x = $pdf->GetX() + $col2;
            $y = $pdf->GetY();
           
            if ( strlen($exp['supplier']) > 25 ) {
               $exp_desc_heighta = $cell_height / $suppier_lines;
            } else {
                $exp_desc_heighta = $cell_height;
            }
            $pdf->MultiCell($col2, floor($exp_desc_heighta), $exp['supplier'], 1, 'L');
            $pdf->SetXY($x, $y);
            //supplier end

            //description
            $x = $pdf->GetX() + $col3;
            $y = $pdf->GetY();

            if ( strlen($exp['description']) > 49 ) {
                $exp_desc_height = $cell_height / $desc_lines;
            } else {
                $exp_desc_height = $cell_height;
            }
            $pdf->MultiCell($col3, floor($exp_desc_height), $exp['description'], 1,'L'); //, $align, $fill);

            $pdf->SetXY($x, $y);
            //description end
            
            $pdf->Cell($col4, $cell_height, $exp['account_name'], 1);
            $pdf->Cell($col5, $cell_height, '$' . $exp['amount'], 1);
            // get dynamic GST based on country
            $gst = $this->getDynamicGST($exp['amount'], $params['country_id']);
            $net_amount = $exp['amount'] - $gst;
            $pdf->Cell($col6, $cell_height, '$' . number_format($net_amount, 2), 1, null, null, true);
            $pdf->Cell($col7, $cell_height, '$' . number_format($gst, 2), 1, null, null, true);
            $pdf->Cell($col8, $cell_height, '$' . $exp['amount'], 1, null, null, true);
            $pdf->Ln();

            $amount_tot += $exp['amount'];
            $net_amount_tot += $net_amount;
            $gst_tot += $gst;
            // reimbursed if Personal Card or Cash
            if ($exp['card'] == 2 || $exp['card'] == 5) {
                $amount_reimbursed += $exp['amount'];
            }
        }


        // total
        $pdf->SetFont('Arial', 'B', $font_size);
        $pdf->Cell($col1, $cell_height, '', 1);
        $pdf->Cell($col1_ins, $cell_height, '', 1);
        $pdf->Cell($col2, $cell_height, '', 1);
        $pdf->Cell($col3, $cell_height, '', 1);
        $pdf->Cell($col4, $cell_height, '', 1);
        $pdf->Cell($col5, $cell_height, '$' . number_format($amount_tot, 2), 1);
        $pdf->Cell($col6, $cell_height, '$' . number_format($net_amount_tot, 2), 1, null, null, true);
        $pdf->Cell($col7, $cell_height, '$' . number_format($gst_tot, 2), 1, null, null, true);
        $pdf->Cell($col8, $cell_height, '$' . number_format($amount_tot, 2), 1, null, null, true);
        $pdf->Ln();

        $pdf->Ln(5);

        // due to employee
        $pdf->SetX(218);
        $pdf->SetFont('Arial', 'B', $font_size);
        $pdf->Cell($heading2_col1, $cell_height, 'Due To Employee: ');
        $pdf->Cell($col8, $cell_height, '$' . number_format($amount_reimbursed, 2), 1);
        $pdf->Ln();


        $pdf_filename = 'expense_summary_' . date('dmYHis') . '.pdf';
        if (isset($params['pdf_filename'])) {
            $pdf_filename = $params['pdf_filename'];
        }
        return $pdf->Output($pdf_filename, $output);
    }

    /*
        get expsen claim total by expense_summary_id
        include only personal card and cash
        return total
    */
    public function get_expense_claim_total($expense_summary_id){

        $q = "SELECT sum(exp.amount) AS total_amount
        FROM `expense_summary` AS `exp_sum`
        LEFT JOIN `expenses` AS `exp` ON exp.`expense_summary_id` = exp_sum.`expense_summary_id`
        WHERE `exp_sum`.`active` = 1
        AND `exp_sum`.`deleted` =0
        AND `exp_sum`.`country_id` = {$this->config->item('country')}
        AND `exp_sum`.`expense_summary_id` = $expense_summary_id 
        AND (exp.card= 2 OR exp.card = 5)
        ORDER BY `exp_sum`.`date` DESC";

        $a = $this->db->query($q);
        $row =  $a->row_array();
        return $row['total_amount'];

    }

}
