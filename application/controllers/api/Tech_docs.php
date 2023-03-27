<?php

class Tech_docs extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->load->model('resources_model');
    }

    public function index() {

        $techDocHeaders = $this->resources_model->get_tech_doc_header([
            'sel_query' => "
                tdh.`tech_doc_header_id`,
                tdh.`name`
            ",
            'country_id' => $this->config->item('country'),
			'sort_list' => array(
				array(
					'order_by' => 'tdh.`name`',
					'sort' => 'ASC',
				)
			),
        ])->result_array();

        $techDocsParams = [
            'sel_query' => "
                td.`tech_doc_header_id`,
                td.`technician_documents_id`,
                td.`type`,
                td.`path`,
                td.`filename`,
                td.`title`,
                td.`url`,
                td.`date`
            ",
            'country_id' => $this->config->item('country'),
            'sort_list' => [
                [
                    'order_by' => 'td.title',
                    'sort' => 'ASC',
                ],
            ],
        ];

        $techDocHeadersAssoc = [];
        for ($x = 0; $x < count($techDocHeaders); $x++) {
            $techDocHeader = &$techDocHeaders[$x];
            $techDocHeader['tech_docs'] = [];

            $techDocHeadersAssoc[$techDocHeader['tech_doc_header_id']] = &$techDocHeader;
        }

        $techDocs = $this->resources_model->get_tech_doc($techDocsParams)->result_array();

        for ($x = 0; $x < count($techDocs); $x++) {
            $techDoc = &$techDocs[$x];

            $techDocHeadersAssoc[$techDoc['tech_doc_header_id']]['tech_docs'][] = $techDoc;
        }

        $this->api->setSuccess(true);
        $this->api->putData('tech_doc_headers', $techDocHeaders);
    }

    public function section_by_header_name() {
        $headerName = $this->input->get("header_name");

        if ($headerName) {
            $headerId = $this->resources_model->get_resources_header_id($headerName);

            $techDocHeader = $this->resources_model->get_tech_doc_header([
                'sel_query' => "
                    tdh.`tech_doc_header_id`,
                    tdh.`name`
                ",
                'country_id' => $this->config->item('country'),
                'header_id' => $headerId,
            ])->row_array();

            if (!is_null($techDocHeader)) {
                if($headerId == 9){
                    $techDocs = $this->resources_model->getTechsStaffs();
                }
                else{
                    $techDocsParams = [
                        'sel_query' => "
                            td.`technician_documents_id`,
                            td.`type`,
                            td.`path`,
                            td.`filename`,
                            td.`title`,
                            td.`url`,
                            td.`date`
                        ",
                        'country_id' => $this->config->item('country'),
                        'header_id' => $headerId,
                        'sort_list' => [
                            [
                                'order_by' => 'td.title',
                                'sort' => 'ASC',
                            ],
                        ],
                    ];
    
                    $techDocs = $this->resources_model->get_tech_doc($techDocsParams)->result_array();
                }
                /*
                $techDocsParams = [
                    'sel_query' => "
                        td.`technician_documents_id`,
                        td.`type`,
                        td.`path`,
                        td.`filename`,
                        td.`title`,
                        td.`url`,
                        td.`date`
                    ",
                    'country_id' => $this->config->item('country'),
                    'header_id' => $headerId,
                    'sort_list' => [
                        [
                            'order_by' => 'td.title',
                            'sort' => 'ASC',
                        ],
                    ],
                ];

                $techDocs = $this->resources_model->get_tech_doc($techDocsParams)->result_array();
                */

                $this->api->setSuccess(true);
                $this->api->putData('tech_doc_header', $techDocHeader);
                $this->api->putData('tech_docs', $techDocs);
                return;
            }
            else {
                $this->api->setMessage("{$headerName} does not exist.");
            }
        }
        else {
            $this->api->setMessage("header_name is required");
        }

        $this->api->setSuccess(false);
    }

    public function section($headerId) {

        $techDocsParams = [
            'sel_query' => "
                td.`technician_documents_id`,
                td.`type`,
                td.`path`,
                td.`filename`,
                td.`title`,
                td.`url`,
                td.`date`
            ",
            'country_id' => $this->config->item('country'),
            'header_id' => $headerId,
            'sort_list' => [
                [
                    'order_by' => 'td.title',
                    'sort' => 'ASC',
                ],
            ],
        ];

        $techDocHeader = $this->resources_model->get_tech_doc_header([
            'sel_query' => "
                tdh.`tech_doc_header_id`,
                tdh.`name`
            ",
            'country_id' => $this->config->item('country'),
            'header_id' => $headerId,
        ])->row_array();

        $techDocs = $this->resources_model->get_tech_doc($techDocsParams)->result_array();

        $this->api->setSuccess($techDocHeader != null);
        $this->api->putData('tech_doc_header', $techDocHeader);
        $this->api->putData('tech_docs', $techDocs);
    }

    function add_warranty() {

         $this->api->assertMethod("put");

         $postData = $this->api->getPostData();

         $postData["tech_staff_id"] = $this->api->getJWTItem("staff_id");
         $postData["date_created"] = date("Y-m-d");

         $result = $this->db->set($postData)
            ->insert("warranties");

        $postData["tech_staff_id"] = $this->db->insert_id();

        $this->api->setSuccess($result);
        $this->api->putData("warranty", $postData);
    }

    function expenses() {
        $this->load->model("expensesummary_model");

        $loggedin_staff_id = $this->api->getJWTItem("staff_id");
        $country_id = $this->config->item('country');

        $jparams = array(
            'sort_list' => array([
                    'order_by' => 'exp.`expense_id`',
                    'sort' => 'ASC'
                ]),
            'paginate' => array(
                'offset' => 0,
                'limit' => 1
            ),
            'entered_by' => $loggedin_staff_id,
            'country_id' => $country_id,
            'exc_sub_exp' => 1
        );
        $expensesUser = $this->expensesummary_model->getExpenses($jparams)->row_array();

        $jparams = array(
            'sort_list' => array([
                    'order_by' => 'exp.`date`',
                    'sort' => 'DESC'
                ]),
            'entered_by' => $loggedin_staff_id,
            'country_id' => $country_id,
            'exc_sub_exp' => 1
        );
        $expenses = $this->expensesummary_model->getExpenses($jparams)->result_array();

        foreach($expenses as &$expense) {
            $file_path = str_replace(" ", "_", $expense['receipt_image']);
            if (file_exists(FCPATH . $file_path)) {
                $expense["receipt_image"] = $this->config->item("crmci_link") . "/" . $expense["receipt_image"];
            }
            else {
                $expense["receipt_image"] = $this->config->item("crm_link") . "/" . $expense["receipt_image"];
            }
        }

        $staffAccounts = $this->expensesummary_model->getStaffAccountsByCountryId($this->config->item('country'))->result_array();

        $jparams = array(
            'sort_list' => array([
                    'order_by' => '`account_name`',
                    'sort' => 'ASC']
            )
        );
        $accounts = $this->expensesummary_model->getExpenseAccount($jparams)->result_array();

        $this->api->setSuccess(true);
        $this->api->putData("expenses_user", $expensesUser);
        $this->api->putData("expenses", $expenses);
        $this->api->putData("accounts", $accounts);
        $this->api->putData("staff_accounts", $staffAccounts);
    }

    function add_expense() {
        $this->load->model("expensesummary_model");

        $this->api->assertMethod("put");

        $postData = $this->api->getPostData();

        $receiptImageData = $postData["receipt_image"];

        $fileName = $receiptImageData["filename"];
        $image = base64_decode($receiptImageData["data"]);

        $file = pathinfo($fileName);
        $uploadFileName = "expenses_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension']; // generate unique file name

        $country_iso = strtolower($this->gherxlib->get_country_iso());
        $success = file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/uploads/expenses/{$uploadFileName}", $image); // move file to its proper directory

        if ($success) {
            $staffId = $this->api->getJWTItem("staff_id");

            $insertData = [
                "employee" => $staffId,
                "date" => $postData["date"],
                "card" => $postData["card"],
                "supplier" => $postData["supplier"],
                "description" => $postData["description"],
                "account" => $postData["account"],
                "amount" => $postData["amount"],
                "receipt_image" => 'uploads/expenses/' . $uploadFileName,
                'file_type' => "image",
                'country_id' => $this->config->item('country'),
                'entered_by' => $staffId,
            ];

            $result = $this->db->set($insertData)
                ->insert("expenses");

            $expenseId = $this->db->insert_id();

            $jparams = array(
                'paginate' => array(
                    'offset' => 0,
                    'limit' => 1
                ),
                'expense_id' => $expenseId,
                'exc_sub_exp' => 1
            );
            $expense = $this->expensesummary_model->getExpenses($jparams)->row_array();

            $file_path = str_replace(" ", "_", $expense['receipt_image']);
            if (file_exists(FCPATH . $file_path)) {
                $expense["receipt_image"] = $this->config->item("crmci_link") . "/" . $expense["receipt_image"];
            }
            else {
                $expense["receipt_image"] = $this->config->item("crm_link") . "/" . $expense["receipt_image"];
            }

            $this->api->setSuccess($result);
            $this->api->putData("expense", $expense);
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage("Receipt image upload failed.");
        }
    }

    function update_expense() {
        $this->load->model("expensesummary_model");

        $this->api->assertMethod("patch");

        $postData = $this->api->getPostData();

        $updateData = [
            "date" => $postData["date"],
            "card" => $postData["card"],
            "supplier" => $postData["supplier"],
            "description" => $postData["description"],
            "account" => $postData["account"],
            "amount" => $postData["amount"],
        ];

        if (!is_null($postData["receipt_image"])) {
            $receiptImageData = $postData["receipt_image"];

            $fileName = $receiptImageData["filename"];
            $image = base64_decode($receiptImageData["data"]);

            $file = pathinfo($fileName);
            $uploadFileName = "expenses_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension']; // generate unique file name

            $success = file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/uploads/expenses/{$uploadFileName}", $image); // move file to its proper directory

            $updateData["receipt_image"] = 'uploads/expenses/' . $uploadFileName;
            $updateData['file_type'] = "image";
        }
        else {
            $success = true;
        }

        if ($success) {
            $expenseId = $postData["expense_id"];

            $result = $this->db->set($updateData)
                ->where("expense_id", $expenseId)
                ->update("expenses");

            $jparams = array(
                'paginate' => array(
                    'offset' => 0,
                    'limit' => 1
                ),
                'expense_id' => $expenseId,
                'exc_sub_exp' => 1
            );
            $expense = $this->expensesummary_model->getExpenses($jparams)->row_array();

            $file_path = str_replace(" ", "_", $expense['receipt_image']);
            if (file_exists(FCPATH . $file_path)) {
                $expense["receipt_image"] = $this->config->item("crmci_link") . "/" . $expense["receipt_image"];
            }
            else {
                $expense["receipt_image"] = $this->config->item("crm_link") . "/" . $expense["receipt_image"];
            }

            $this->api->setSuccess($result);
            $this->api->putData("expense", $expense);
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage("Receipt image upload failed.");
        }
    }

    function delete_expense($id) {
        $this->load->model("expensesummary_model");

        $this->api->assertMethod("delete");

        $result = $this->expensesummary_model->delete_expense_record($id);

        $this->api->setSuccess($result);

        if (!$result) {
            $this->api->setMessage("Deleting expense failed.");
        }
    }

    function process_expenses() {
        $this->load->model("expensesummary_model");

        $this->api->assertMethod("post");

        $staffId = $this->api->getJWTItem("staff_id");
        $countryId = $this->config->item('country');

        $postData = $this->api->getPostData();

        $totalAmount = $postData["total_amount"];
        $expenseIds = $postData["expense_ids"];
        $lineManager = $postData["line_manager"];

        if (!empty($expenseIds)) {

            $this->db->trans_begin();

            $insertParams = [
                'date' => date('Y-m-d'),
                'employee' => $staffId,
                'total_amount' => $totalAmount,
                'line_manager' => $lineManager,
                'country_id' => $countryId
            ];

            $expenseSummaryId = $this->expensesummary_model->add_expense_summary($insertParams);

            if ($expenseSummaryId !== 0) {
                foreach ($expenseIds as $expenseId) {
                    $this->expensesummary_model->update_expense($expenseSummaryId, $expenseId);
                }

                $this->db->trans_commit();

                try {
                    $output = 'S';
                    $order_by = 'exp.date';
                    $sort = 'DESC';
                    $pdf_filename = 'expense_summary_' . date('dmYHis') . '.pdf';
                    $jparams = [
                        'sort_list' => [
                            [
                                'order_by' => $order_by,
                                'sort' => $sort,
                            ],
                        ],
                        'country_id' => $countryId,
                        'exp_sum_id' => $expenseSummaryId,
                        'pdf_filename' => $pdf_filename,
                    ];

                    // get country data
                    $country = $this->gherxlib->getCountryViaCountryId($countryId);

                    // employee
                    $emp = $this->gherxlib->getStaffInfo(['staff_id' => $staffId])->row_array();
                    $emp_name = "{$emp['FirstName']} {$emp['LastName']}";

                    $lm = $this->gherxlib->getStaffInfo(['staff_id' => $lineManager])->row_array();
                    $lm_name = "{$lm['FirstName']} {$lm['LastName']}";
                    $lm_email = $lm['Email'];

                    $e_from = $country->outgoing_email;

                    $subject = "Expense Summary for {$emp_name}";
                    $to = array($this->config->item('sats_accounts_email'), $lm_email);
                    $pdf_data = $this->expensesummary_model->get_expense_summary_pdf($jparams, $output);
                    $email_data['content'] = "
                            <p>
                                    <table style='border:none; margin: 0;'>
                                            <tr><td>Date: </td><td>" . date('d/m/Y') . "</td></tr>
                                            <tr><td>Staff: </td><td>{$emp_name}</td></tr>
                                            <tr><td>Amount: </td><td>$" . number_format($totalAmount, 2) . "</td></tr>
                                            <tr><td>Line Manager: </td><td>{$lm_name} <strong style='color:red;'>APPROVAL REQUIRED</strong></td></tr>
                                    </table>
                            </p>
                            <p>Please find attached Expense Claim Form</p>
                            ";
                    $email_data['email_signature'] = $country->email_signature;
                    $email_data['trading_name'] = $country->trading_name;
                    $mail_config = [
                        'mailtype' => 'html',
                        'charset' => 'utf-8'
                    ];
                    $this->load->library('email');
                    $this->email->initialize($mail_config);
                    $this->email->set_newline("\r\n");
                    $this->email->from($e_from, "SATS - Smoke Alarm Testing Services");
                    $this->email->to($to);
                    $this->email->subject($subject);
                    $e_body = $this->load->view('emails/expense_summary_email', $email_data, TRUE);
                    $this->email->attach($pdf_data, 'attachment', $pdf_filename, 'application/pdf');
                    $this->email->message($e_body);

                    $emailSent = $this->email->send();
                    if (!$emailSent) {
                        throw new Exception("Statement successfully saved but necessary emails were not broadcasted");
                    }

                    $this->api->setSuccess(true);
                    $this->api->setMessage("Expense Summary Added.");
                }
                catch (\Exception $ex) {
                    $this->api->setSuccess(false);
                    $this->api->setMessage("Expense Summary email sending failed.");
                    $this->api->putData("exception", $ex->getMessage());
                }
            }
            else {
                $this->db->trans_rollback();

                $this->api->setSuccess(false);
                $this->api->setMessage("Adding expensing summary failed.");
            }


        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage("No Expense IDs");
        }

    }

    function clear_expenses() {
        $this->api->assertMethod("post");
        $postData = $this->api->getPostData();

        $expenseIds = $postData["expense_ids"];

        $result = $this->db->where_in("expense_id", $expenseIds)
            ->delete("expenses");

        if ($result) {
            $this->api->setSuccess(true);
            $this->api->setMessage("Expenses deleted.");
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage("Expenses not deleted.");
        }
    }

    function incident_and_injury_report() {
        $this->db->select("
            DISTINCT(ca.staff_accounts_id), sa.FirstName, sa.LastName
        ");
        $this->db->from("staff_accounts AS sa");
        $this->db->join("country_access AS ca", "sa.StaffID = ca.staff_accounts_id AND ca.country_id = {$this->config->item("country")}", "inner");
        $this->db->where("sa.deleted", "0");
        $this->db->where("sa.active", '1');
        $this->db->group_start();
        $this->db->where_in("sa.ClassID", [2, 9]);
        $this->db->or_where("sa.StaffID", 2226);
        $this->db->group_end();
        $this->db->order_by("sa.FirstName", "asc");
        $staffs = $this->db->get()->result_array();

        $this->api->setSuccess(true);
        $this->api->putData("staffs", $staffs);
    }

    function save_incident_and_injury_report() {
        $this->load->model("users_model");

        $this->api->assertMethod("put");

        $postData = $this->api->getPostData();

        $datetime_of_incident = $postData["datetime_of_incident"];


        $insertData = [];

        $saveKeys = [
            'datetime_of_incident',
            'nature_of_incident',
            'location_of_incident',
            'describe_incident',
            'ip_name',
            'ip_address',
            'ip_occupation',
            'ip_dob',
            'ip_tel_num',
            'ip_employer',
            'ip_noi',
            'ip_loi',
            'ip_onsite_treatment',
            'ip_further_treatment',
            'witness_name',
            'witness_contact',
            'loss_time_injury',
            'reported_to',
            'confirm_chk',
        ];

        foreach ($saveKeys as $key) {
            $insertData[$key] = $postData[$key];
        }

        $insertData["created_by"] = $this->api->getJWTItem("staff_id");
        $insertData["country_id"] = $this->config->item("country");
        $country_iso = strtolower($this->gherxlib->get_country_iso());
        $uploadPath = "/images/incident/{$country_iso}/";

        if (!is_dir("{$_SERVER['DOCUMENT_ROOT']}".$uploadPath)) {
            mkdir("{$_SERVER['DOCUMENT_ROOT']}".$uploadPath, 0777, true);
        }

        $this->db->trans_begin();

        $iai_id = $this->users_model->insert_incident_and_injury($insertData);

        $success = true;

        foreach ($postData["photo_of_incident"] as $photoData) {
            if (!is_null($photoData)) {
                $filename = $photoData["filename"];
                $image = base64_decode($photoData["data"]);
                $file = pathinfo($filename);
                $uploadFileName = "incident" . rand() . '_' . date('YmdHis'). "." . $file['extension'];

                $uploaded = file_put_contents("{$_SERVER['DOCUMENT_ROOT']}{$uploadPath}{$uploadFileName}", $image);

                if ($uploaded) {
                    $this->users_model->upload_photo_data([
                        'incident_and_injury_id' => $iai_id,
                        'image_name' => "{$uploadPath}{$uploadFileName}",
                    ]);
                }
                else {
                    $success = false;
                    break;
                }
            }
        }

        if ($success) {
            $this->db->trans_commit();
        }
        else {
            $this->db->trans_rollback();
            $this->api->setMessage("Photo failed uploading.");
        }

        $this->api->setSuccess($success);
    }

    function leave_request() {
        $this->load->model("expensesummary_model");

        $loggedin_staff_id = $this->api->getJWTItem("staff_id");
        $country_id = $this->config->item('country');

        $staffAccounts = $this->expensesummary_model->getStaffAccountsByCountryId($this->config->item('country'))->result_array();

        $this->api->setSuccess(true);
        $this->api->putData("staff_accounts", $staffAccounts);
    }

    function save_leave_request() {
        $this->api->assertMethod("put");

        $this->load->model("users_model");
        $this->load->library('email');

        $today = date("Y-m-d");
        $postData = $this->api->getPostData();

        $employee = $this->api->getJWTItem("staff_id");
        $postData["date"] = $today;
        $postData["employee"] = $employee;
        $postData["status"] = "Pending";

        $line_manager = $postData["line_manager"];
        $lday_of_work = $postData["lday_of_work"];
        $fday_back = $postData["fday_back"];
        $num_of_days = $postData["num_of_days"];
        $reason_for_leave = $postData["reason_for_leave"];

        $this->form_validation->set_data($postData);

        $this->form_validation->set_rules('type_of_leave', 'Type of leave', 'required');
        $this->form_validation->set_rules('lday_of_work', 'Last day of leave', 'required');
        $this->form_validation->set_rules('fday_back', 'First day of leave', 'required');
        $this->form_validation->set_rules('num_of_days', 'Number of days', 'required');
        $this->form_validation->set_rules('reason_for_leave', 'Reason for leave', 'required');
        $this->form_validation->set_rules('line_manager', 'Line manager', 'required');

        if ($this->form_validation->run() == true) {
            $last_id = $this->users_model->insert_leave($postData);

            $pdf_data = $this->getLeavePdf('S', $last_id);

            //Employee name
            $employee_name_params = array('sel_query' => 'sa.FirstName, sa.LastName','staff_id'=>$employee);
            $employee_name_row = $this->gherxlib->getStaffInfo($employee_name_params)->row_array();

            //Line manager name
            $line_manager_params = array('sel_query' => 'sa.FirstName, sa.LastName, sa.Email','staff_id'=>$line_manager);
            $line_manager_row = $this->gherxlib->getStaffInfo($line_manager_params)->row_array();


            // Send Email
            $pdf_filename = 'leave_request' . date('dmYHis') . '.pdf';

            if($backup_leave!=""){
                switch ($backup_leave) {
                    case 1:
                        $tol_str = "Annual leave";
                        break;
                    case 2:
                        $tol_str = "Leave without pay";
                        break;
                }
            }else{
                $tol_str = "";
            }

            $email_data['today'] = $today;
            $email_data['employee_name']  = "{$employee_name_row['FirstName']} {$employee_name_row['LastName']}";
            $email_data['type_of_leave'] = $this->getTypesofLeave($type_of_leave);
            $email_data['tol_str'] = $tol_str;
            $email_data['lday_of_work'] = $lday_of_work;
            $email_data['fday_back'] = $fday_back;
            $email_data['num_of_days'] = $num_of_days;
            $email_data['reason_for_leave'] = $reason_for_leave;
            $email_data['lm_name'] = "{$line_manager_row['FirstName']} {$line_manager_row['LastName']}";

            $country_query = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));
            $e_from = $country_query->outgoing_email;
            $to = $line_manager_row['Email'];
            $subject = "Leave request for {$email_data['employee_name']}";

            $mail_config = Array(
                'mailtype' => 'html',
                'charset' => 'utf-8'
            );
            $this->email->initialize($mail_config);
            $this->email->set_newline("\r\n");
            $this->email->from($e_from, "SATS - Smoke Alarm Testing Services");
            $this->email->to($to);
            $this->email->subject($subject);
            $e_body = $this->load->view('emails/leave_form_email', $email_data, TRUE);
            $this->email->attach($pdf_data, 'attachment', $pdf_filename, 'application/pdf');
            $this->email->message($e_body);
            $this->email->send();
            // Send Email End

            $this->api->setSuccess(true);
            $this->api->setMessage("Leave request has been succesfully added.");
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage($this->form_validation->error_string());
        }
    }

    private function getTypesofLeave($tol) {
        switch ($tol) {
            case 1:
                $tol_str = "Annual";
                break;
            case 2:
                $tol_str = "Personal(sick)";
                break;
            case 3:
                $tol_str = "Personal(carer's)";
                break;
            case 4:
                $tol_str = "Compassionate";
                break;
            case 5:
                $tol_str = "Cancel Previous Leave";
                break;
            case -1:
                $tol_str = "Other";
                break;
        }

        return $tol_str;
    }

    private function getLeavePdf($output, $leave_id) {
        $this->load->library('JPDF');

        $this->load->model("users_model");

        $country_id = $this->config->item('country');

        //GET LEAVE DETAILS
        $sel_query = "
            l.leave_id,
            l.`date`,
            l.lday_of_work,
            l.fday_back,
            l.reason_for_leave,
            l.hr_app,
            l.hr_app_timestamp,
            l.line_manager_app,
            l.line_manager_app_timestamp,
            l.added_to_cal,
            l.added_to_cal_timestamp,
            l.staff_notified,
            l.staff_notified_timestamp,
            l.status,
            l.type_of_leave,
            l.num_of_days,

            sa_emp.`StaffID` AS emp_staff_id,
            sa_emp.`FirstName` AS emp_fname,
            sa_emp.`LastName` AS emp_lname,
            sa_emp.`Email` AS emp_email,

            sa_lm.`StaffID` AS sa_lm_staff_id,
            sa_lm.`FirstName` AS lm_fname,
            sa_lm.`LastName` AS lm_lname,
            sa_lm.`Email` AS lm_email,

            lma.`FirstName` AS lma_fname,
            lma.`LastName` AS lma_lname,
            hra.`FirstName` AS hra_fname,
            hra.`LastName` AS hra_lname,
            atc.`FirstName` AS atc_fname,
            atc.`LastName` AS atc_lname,
            sn.`FirstName` AS sn_fname,
            sn.`LastName` AS sn_lname
        ";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'emp_id' => $employee,
            'lm_id' => $line_manager,
            'l_status' => $status,
            'leave_id' => $leave_id,
            'sort_list' => array(
                array(
                    'order_by' => 'l.date',
                    'sort' => 'DESC',
                ),
            ),
        );
        $leave = $this->users_model->getLeave($params)->row_array();



        // pdf initiation
        $pdf = new JPDF();

        // settings
        $pdf->SetTopMargin(40);
        $pdf->SetAutoPageBreak(true, 50);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // set default values
        $header_space = 6.5;
        $header_width = 100;
        $header_height = 10;
        $header_border = 0;
        $header_new_line = 1;
        $header_align = null;

        $cell_width = 64;
        $cell_height = 6;
        $cell_border = 0;
        $col1_cell_new_line = 0;
        $col2_cell_new_line = 1;
        $col1_cell_align = 'L';
        $col2_cell_align = 'L';


        // LEAVE REQUEST
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Leave Request', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Date: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, date('d/m/Y', strtotime($leave['date'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $leave['emp_fname'] . ' ' . $leave['emp_lname'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Type of Leave: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $this->getTypesofLeave($leave['type_of_leave']), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'First Day of Leave: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, date('d/m/Y', strtotime($leave['lday_of_work'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Last Day of Leave: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, date('d/m/Y', strtotime($leave['fday_back'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Number of days : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $leave['num_of_days'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Reason for Leave : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->MultiCell($cell_width + 50, $cell_height, $leave['reason_for_leave'], $cell_border, $col2_cell_align);

        $pdf->Ln($header_space);

        // OFFICIAL USE ONLY
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Office Use Only', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Line Manager : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $leave['lm_fname'] . ' ' . $leave['lm_lname'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        // HR Approved
        if (is_numeric($leave['hr_app']) && $leave['hr_app'] == 1) {
            $sel_str = 'Yes';
        } else if (is_numeric($leave['hr_app']) && $leave['hr_app'] == 0) {
            $sel_str = 'No';
        } else {
            $sel_str = '';
        }
        $pdf->Cell($cell_width, $cell_height, 'HR Approved : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $sel_str, $cell_border, $col2_cell_new_line, $col2_cell_align);

        // Line Manager Approved
        if (is_numeric($leave['line_manager_app']) && $leave['line_manager_app'] == 1) {
            $sel_str = 'Yes';
        } else if (is_numeric($leave['line_manager_app']) && $leave['line_manager_app'] == 0) {
            $sel_str = 'No';
        } else {
            $sel_str = '';
        }
        $pdf->Cell($cell_width, $cell_height, 'Line Manager Approved : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $sel_str, $cell_border, $col2_cell_new_line, $col2_cell_align);

        // Added to Calendar
        if (is_numeric($leave['added_to_cal']) && $leave['added_to_cal'] == 1) {
            $sel_str = 'Yes';
        } else if (is_numeric($leave['added_to_cal']) && $leave['added_to_cal'] == 0) {
            $sel_str = 'No';
        } else {
            $sel_str = '';
        }
        $pdf->Cell($cell_width, $cell_height, 'Added to Calendar : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $sel_str, $cell_border, $col2_cell_new_line, $col2_cell_align);

        // Added to MYOB
        if (is_numeric($leave['staff_notified']) && $leave['staff_notified'] == 1) {
            $sel_str = 'Yes';
        } else if (is_numeric($leave['staff_notified']) && $leave['staff_notified'] == 0) {
            $sel_str = 'No';
        } else {
            $sel_str = '';
        }
        $pdf->Cell($cell_width, $cell_height, 'Staff notified in writing : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $sel_str, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf_filename = 'leave_' . date('dmYHis') . '.pdf';
        return $pdf->Output($pdf_filename, $output);
    }

}

?>