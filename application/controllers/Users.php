<?php

class Users extends CI_Controller {

    public function __construct() {

        parent::__construct();
        $this->load->helper('url');
        $this->load->model('users_model');
        $this->load->model('staff_accounts_model');
        $this->load->model('agency_model');
        $this->load->library('pagination');
        $this->load->database();
        $this->load->helper('form_helper');
    }

    public function add() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Add User";
        $data['page_redirect'] = "/users/add";

        $country_id = $this->config->item('country');

        $this->form_validation->set_rules('fname', 'First Name', 'required');
        $this->form_validation->set_rules('lname', 'Last Name', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[staff_accounts.Email]', [
            'is_unique' => "Email already exists, please use a new one.",
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
        $this->form_validation->set_rules('user_class', 'User Class', 'required');


        if ($this->form_validation->run() == true) {

            $fname = $this->input->get_post('fname');
            $lname = $this->input->get_post('lname');
            $address = $this->input->get_post('address');
            $birthday = $this->customlib->formatDmyToYmd($this->input->get_post('birthday'), true);
            $debit_card = $this->input->get_post('debit_card');
            $email = $this->input->get_post('email');
            $phone = $this->input->get_post('phone');
            $job_title = $this->input->get_post('job_title');
            $user_class = $this->input->get_post('user_class');
            $states_arr = $this->input->get_post('states');
            $working_days_arr = $this->input->get_post('working_days');
            $password = $this->input->get_post('password');
            $startDate = $this->input->get_post('start_date');

            if ($this->customlib->isDateNotEmpty($startDate)) {
                $startDate = $this->customlib->formatDmyToYmd($startDate);
            }

            $working_days_imp = implode(",", $working_days_arr);

            // hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $data = [
                'FirstName' => $fname,
                'LastName' => $lname,
                'address' => $address,
                'dob' => $birthday,
                'Email' => $email,
                'password_new' => $password_hash,
                'ContactNumber' => $phone,
                'debit_card_num' => $debit_card,
                'sa_position' => $job_title,
                'ClassID' => $user_class,
                'working_days' => $working_days_imp,
                'start_date' => $startDate,
            ];

            $passwordResult = $this->users_model->encryptWithOldCRM($password);

            if ($passwordResult['success']) {
                $data['Password'] = $passwordResult['encrypted'];
            }

            $this->db->trans_start();

            $this->db->insert('staff_accounts', $data);
            $staff_id = $this->db->insert_id();

            $countryAccess = $this->input->post("country_access");

            foreach ($countryAccess as &$ca) {
                $ca['staff_accounts_id'] = $staff_id;
            }

            $this->db->insert_batch("country_access", $countryAccess);


            // insert states
            if ($country_id == 1) { // AU
                foreach ($states_arr as $state) {

                    $data = array(
                        'StaffID' => $staff_id,
                        'country_id' => $country_id,
                        'StateID' => $state
                    );

                    $this->db->insert('staff_states', $data);
                }
            }

            if( $user_class == 6 ){ // if technician, create accomodation
				$tech_full_name = "{$fname} {$lname}";

                $accommodationData = $this->input->post("accommodation");

                $this->db->insert("accomodation", [
                    "name" => $tech_full_name,
                    "area" => "1 Staff",
                    "address" => $accommodationData['address'],
                    "phone" => $phone,
                    "email" => $email,
                    "rate" => "",
                    "comment" => "STAFF",
                    "country_id" => $country_id,
                    "lat" => $accommodationData["lat"],
                    "lng" => $accommodationData["lng"],
                    "postcode" => $accommodationData["postcode"],
                ]);
				$accommodationId = $this->db->insert_id();

				// update accomodation id on staff account
                $this->db->update('staff_accounts', [
                    'accomodation_id' => $accommodationId
                ], "StaffID = {$staff_id}");

			}

            


            $this->system_model->insert_log([
                'title' => 17,
                'details' => "User added: {$fname} {$lname}",
                'created_by_staff' => $this->session->staff_id,
            ]);

            $getUser = function($staff_id) {
                return $this->db->select("FirstName,LastName")
                    ->from('staff_accounts')
                    ->where('StaffID', $staff_id)
                    ->limit(1)
                    ->get()->row();
            };
            $admin = $getUser($this->session->staff_id);

            $this->system_model->insert_log([
                'title' => 17,
                //'details' => "New User {$email} was created by " . $admin->Email,
                'details' => "{staff_user:$staff_id} was created by $admin->FirstName $admin->LastName", 
                'created_by_staff' => $this->session->staff_id,
            ]);
            

            $this->db->trans_complete();

            $this->session->set_flashdata(['add_user_success' => 1, "message" => "User has been added.<br/><a href=\"/users/view/{$staff_id}\">{$fname} {$lname}</a>"]);
            redirect('/users/add');
            return;
        }
        else if ($this->input->method() == "post") {
            $this->session->set_flashdata(array('error_msg' => "User not added.", 'status' => 'error'));
        }


        // staff classes
        $sel_query = "ClassID, ClassName";
        $params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'ClassName',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );
        $data['staff_classes_sql'] = $this->system_model->getStaffClasses($params);


        $this->load->view('templates/inner_header', $data);
        $this->load->view('users/add', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view($id = null, $tab = "personal") {

        $this->load->model('logs_model');

        $data = [];

        $getUser = function($id) {
            return $this->db->select("
                sa.FirstName,
                sa.LastName,
                sa.Email,
                sa.ContactNumber,
                sa.phone_model_num,
                sa.phone_serial_num,
                sa.phone_imei,
                sa.phone_pin,
                sa.other_key_num,
                sa.other_plant_id,
                sa.other_shirt_size,
                sa.laptop_make,
                sa.laptop_serial_num,
                sa.ipad_model_num,
                sa.ipad_serial_num,
                sa.ipad_imei,
                sa.tablet_pin,
                sa.ipad_prepaid_serv_num,
                sa.ipad_expiry_date,
                sa.debit_card_num,
                sa.debit_expiry_date,
                sa.active,
                sa.ClassID,
                sa.sa_position,
                sa.other_call_centre,
                sa.working_days,
                sa.StaffID,
                sa.Password,
                sa.license_num,
                sa.licence_expiry,
                sa.is_electrician,
                sa.elec_license_num,
                sa.driver_license_num,
                sa.elec_licence_expiry,
                sa.blue_card_num,
                sa.blue_card_expiry,
                sa.start_date,
                sa.dob,
                sa.ice_name,
                sa.ice_phone,
                sa.address,
                sa.profile_pic,
                sa.electrical_license,
                sa.driver_license,
                sa.display_on_wsr,
                sa.recieve_wsr,
                sa.accomodation_id,
                sa.personal_contact_number,

                cc.FirstName AS cc_firstname,
                cc.LastName AS cc_lastname,

                sc.ClassName AS sc_classname
            ")
                ->from('staff_accounts AS sa')
                ->join("staff_accounts AS cc", "sa.other_call_centre = cc.StaffID", "left")
                ->join("staff_classes AS sc", "sc.ClassID = sa.ClassID", "left")
                ->where('sa.StaffID', $id)
                ->limit(1)
                ->get()->row_array();
        };

        $user = $getUser($id);

        $data['title'] = "{$user["FirstName"]} {$user["LastName"]}'s Details";

        if (!is_null($user)) {

            switch($tab) {
                case "logs":
                    $logData = $this->_tab_contents_logs($user);
                    $data = array_merge($data, $logData);
                    break;
                default:
                    $devicesData = $this->_tab_contents_devices($user);
                    $permissionsData = $this->_tab_contents_permissions($user);
                    $licencingData = $this->_tab_contents_licencing($user);
                    $personalData = $this->_tab_contents_personal($user);
                    $data = array_merge($data, $devicesData, $permissionsData, $licencingData, $personalData);
                    break;
            }

            $vaccinations = $this->db->select('*')
                ->from("vaccinations")
                ->where("StaffID", $user["StaffID"])
                ->order_by("completed_on", "desc")
                ->get()->result_array();

            $data["vaccinations"] = $vaccinations;

            // decrypt password
            $password = $this->users_model->decryptWithOldCRM($user['Password']);

            $data['password'] = $password['decrypted'];

            $data['user'] = $user;
            //print_r($data['user']);
            //exit();

        }
        else {
            return show_404();
        }

        $data["tab"] = $tab;

        $this->load->view('templates/inner_header', $data);
        $this->load->view('users/view', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    private function _tab_contents_personal($user) {
        $id = $user['StaffID'];

        if ($this->input->method() == 'post') {
            $postData = $this->input->post();

            $this->form_validation->set_rules('user[FirstName]', 'First Name', 'required');
            $this->form_validation->set_rules('user[LastName]', 'Last Name', 'required');
            $this->form_validation->set_rules('user[Email]', 'email', 'required|valid_email');
            $this->form_validation->set_rules(
                'user[Email]', 'email',
                [
                    'required',
                    'valid_email',
                    [
                        'email_callable',
                        function($email) use ($id)
                        {
                            $isUnique = $this->db->select("IFNULL(COUNT(StaffID), 0) AS count")
                                ->from("staff_accounts")
                                ->where("Email", $email)
                                ->where("StaffID !=", $id)
                                ->limit(1)
                                ->get()->row()->count == 0;

                            if (!$isUnique) {
                                $this->form_validation->set_message("email_callable", "Email already exists, please use a new one.");
                            }

                            return $isUnique;
                        }
                    ],
                ]
            );

            if ($this->form_validation->run()) {

                $pdWorkingDays = $postData['working_days'];

                $activeWorkingDays = [];
                foreach ($pdWorkingDays as $abbr => $active) {
                    if ($active) {
                        $activeWorkingDays[] = $abbr;
                    }
                }

                $vaccinationData = $postData["vaccination"];

                $userData = $postData['user'];

                $dateFields = [
                    'debit_expiry_date',
                    'start_date',
                ];

                foreach ($dateFields as $dateField) {
                    if ($this->system_model->isDateNotEmpty($userData[$dateField])) {
                        $userData[$dateField] = $this->customlib->formatDmyToYmd($userData[$dateField]);
                    }
                    else {
                        $userData[$dateField] = null;
                    }
                }

                $userData['dob'] = $this->customlib->formatDmyToYmd($userData["dob"], true);

                $userFieldsToUpdate = [
                    'FirstName',
                    'LastName',
                    'Email',
                    'other_key_num',
                    'other_plant_id',
                    'other_shirt_size',
                    'start_date',
                    'dob',
                    'debit_card_num',
                    'debit_expiry_date',
                    'address',
                    'ice_name',
                    'ice_phone',
                    'display_on_wsr',
                    'recieve_wsr',
                ];

                $userDataToSave = [];
                foreach ($userFieldsToUpdate as $field) {
                    $userDataToSave[$field] = $userData[$field];
                }

                $userDataToSave['working_days'] = implode(',', $activeWorkingDays);

                if (!empty($userData['NewPassword'])) {
                    $userDataToSave['password_new'] = password_hash($userData['NewPassword'], PASSWORD_DEFAULT);

                    $result = $this->users_model->encryptWithOldCRM($userData['NewPassword']);

                    if ($result['success']) {
                        $userDataToSave['Password'] = $result['encrypted'];
                    }

                }

                $uploadFile = function($fileField, $uploadPath) use ($id) {
                    //Upload front image
                    if (isset($_FILES[$fileField]) && !empty($_FILES[$fileField]['name'])) {
                        $imagePath = $_FILES[$fileField]['name'];
                        $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
                        $imageName = "img_{$id}_" . rand() . "_" . date("YmdHis");
                        $uploadParams = array(
                            'file_name' => $imageName,
                            'upload_path' => $uploadPath,
                            'max_size' => '3000',
                            'allowed_types' => 'gif|jpg|jpeg|png'
                        );
                        if ($this->gherxlib->do_upload($fileField, $uploadParams)) {
                            return "{$imageName}.{$ext}";
                        }
                    }
                    return false;
                };

                $fileFields = [
                    [
                        'fileField' => 'user_profile_pic',
                        'columnName' => 'profile_pic',
                        'uploadPath' => 'images/staff_profile/',
                    ],
                ];

                foreach ($fileFields as $fileData) {
                    $uploadResult = $uploadFile($fileData['fileField'], $fileData['uploadPath']);
                    if ($uploadResult != false) {
                        $userDataToSave[$fileData['columnName']] = $uploadResult;
                    }
                }

                $accommodationData =& $postData['accommodation'];

                $oldUserData = $user;

                $this->db->trans_start();

                $affectedRows = 0;
                $returnValue = $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);
                $affectedRows += $this->db->affected_rows();

                if ($user['ClassID'] == 6) {
                    if (!isset($user['accomodation_id'])) {
                        $this->db->insert("accomodation", $accommodationData);
                        $affectedRows += $this->db->affected_rows();
                        $accommodationId = $this->db->insert_id();
                        $this->db->update('staff_accounts', [
                            'accomodation_id' => $accommodationId
                        ], "StaffID = {$user['StaffID']}");
                    }
                    else {
                        $this->db->update("accomodation", $accommodationData, "accomodation_id = {$user["accomodation_id"]}");
                        $affectedRows += $this->db->affected_rows();
                    }
                }

                // !!!TODO: do not delete when valid_till is implemented
                // !!!TODO: also add certificate image
                $this->db->where("StaffID", $user["StaffID"]);
                $this->db->delete("vaccinations");

                if (!is_null($vaccinationData["vaccine_brand"]) && $vaccinationData["vaccine_brand"] != "") {
                    $vaccinationData["completed_on"] = $this->customlib->formatDmyToYmd($vaccinationData["completed_on"], true);
                    $this->db->set($vaccinationData)
                        ->insert("vaccinations");

                    $affectedRows++;
                }


                if ($affectedRows > 0) {
                    $logDetails = "{staff_user:{$id}}'s account updated. ";

                    $this->system_model->insert_log([
                        'title' => 4,
                        'details' => $logDetails,
                        'created_by_staff' => $this->session->staff_id,
                    ]);
                }

                $this->db->trans_complete();

                if ($this->db->trans_status()) {
                    $this->session->set_flashdata(array('success_msg' => "User details saved", 'status' => 'success'));

                    if ($postData["redirect"]) {
                        redirect($postData["redirect"]);
                    }
                    else {
                        redirect("/users/view/{$id}/personal");
                    }
                }
                else {
                    $this->session->set_flashdata(array('error_msg' => "User not saved", 'status' => 'error'));
                }
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "User not saved", 'status' => 'error'));
            }
        }

        $workingDaysRaw = explode(',', $user['working_days']);
        $workingDays = [
            'Mon' => 0,
            'Tue' => 0,
            'Wed' => 0,
            'Thu' => 0,
            'Fri' => 0,
            'Sat' => 0,
            'Sun' => 0,
        ];
        foreach ($workingDaysRaw as $day)  {
            $workingDays[$day] = 1;
        }

        $accommodation = $this->db->select("
                a.accomodation_id,
                a.name,
                a.area,
                a.address,
                a.street_number,
                a.street_name,
                a.suburb,
                a.state,
                a.postcode,
                a.rate,
                a.comment,
                a.lat,
                a.lng,
                a.postcode,
                a.assigned_region,
                tr.name AS assigned_region_name
            ")
            ->from("accomodation AS a")
            ->join("tech_regions AS tr", "tr.id = a.assigned_region", "left")
            ->where("a.accomodation_id", $user['accomodation_id'])
            ->limit(1)
            ->get()->row_array();

        // !!!TODO: improve later when all fields will be used
        $latestVaccination = $this->db->select("
                vaccine_brand,
                completed_on
            ")
            ->from("vaccinations")
            ->where("StaffID", $id)
            ->limit(1)
            ->get()->row_array();


        return compact("workingDays", "accommodation", "latestVaccination");
    }

    private function _tab_contents_devices($user) {
        $id = $user["StaffID"];

        if ($this->input->method() == 'post') {
            $postData = $this->input->post();

            $userData = $postData['user'];

            $deviceAccountsPostData = $postData['device_accounts'];
            $deviceAccountsToUpdate = [];
            $deviceAccountsToUpdateWithOG = [];
            $deviceAccountsToCreate = [];
            foreach ($deviceAccountsPostData as $deviceAccount) {
                if ($deviceAccount['will_delete']) {
                    $deviceAccount['deleted'] = date('Y-m-d');
                    $deviceAccountsToUpdateWithOG[] = $deviceAccount;
                    unset($deviceAccount['og_account_identifier']);
                    unset($deviceAccount['og_account_password']);
                    unset($deviceAccount['will_delete']);
                    $deviceAccountsToUpdate[] = $deviceAccount;
                }
                else if (
                    isset($deviceAccount['id']) &&
                    (
                        $deviceAccount['og_account_identifier'] != $deviceAccount['account_identifier'] ||
                        $deviceAccount['og_account_password'] != $deviceAccount['account_password']
                    )
                ) {
                    $deviceAccountsToUpdateWithOG[] = $deviceAccount;
                    unset($deviceAccount['og_account_identifier']);
                    unset($deviceAccount['og_account_password']);
                    $deviceAccountsToUpdate[] = $deviceAccount;
                }
                else if (!isset($deviceAccount['id'])) {
                    unset($deviceAccount['og_account_identifier']);
                    unset($deviceAccount['og_account_password']);
                    $deviceAccountsToCreate[] = $deviceAccount;
                }
            }

            $dateFields = [
                'ipad_expiry_date',
            ];

            foreach ($dateFields as $dateField) {
                if ($this->system_model->isDateNotEmpty($userData[$dateField])) {
                    $userData[$dateField] = $this->customlib->formatDmyToYmd($userData[$dateField]);
                }
                else {
                    $userData[$dateField] = null;
                }
            }

            $userFieldsToUpdate = [
                'ContactNumber',
                'phone_model_num',
                'phone_serial_num',
                'phone_imei',
                'phone_pin',
                'ipad_model_num',
                'ipad_serial_num',
                'ipad_imei',
                'tablet_pin',
                'ipad_prepaid_serv_num',
                'ipad_expiry_date',
                'laptop_make',
                'laptop_serial_num',
            ];

            $userDataToSave = [];
            foreach ($userFieldsToUpdate as $field) {
                $userDataToSave[$field] = $userData[$field];
            }

            $oldUserData = $user;

            $this->db->trans_start();

            $affectedRows = 0;
            $returnValue = $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);
            $affectedRows += $this->db->affected_rows();

            if (!empty($deviceAccountsToUpdate)) {
                $this->db->update_batch('device_accounts', $deviceAccountsToUpdate, 'id');
                $affectedRows += $this->db->affected_rows();
            }
            if (!empty($deviceAccountsToCreate)) {
                $this->db->insert_batch('device_accounts', $deviceAccountsToCreate);
                $affectedRows += $this->db->affected_rows();
            }


            if ($affectedRows > 0) {
                $logDetails = "{staff_user:{$id}}'s account updated. ";

                foreach ($deviceAccountsToCreate as $deviceAccount) {
                    $logDetails .= "Device account for <b>{$deviceAccount['account_type']}</b> added. ";
                }
                foreach ($deviceAccountsToUpdateWithOG as $deviceAccount) {
                    if (isset($deviceAccount['deleted'])) {
                        $logDetails .= "Device account for <b>{$deviceAccount['account_type']}</b> removed. " .
                        "Details were: <b>{$deviceAccount['og_account_identifier']}</b> and  <b>{$deviceAccount['og_account_password']}</b>. ";
                    }
                    else if (
                        $deviceAccount['og_account_identifier'] != $deviceAccount['account_identifier'] ||
                        $deviceAccount['og_account_password'] != $deviceAccount['account_password']
                    ) {
                        $logDetails .= "Device account for <b>{$deviceAccount['account_type']}</b> updated from " .
                        "<b>{$deviceAccount['og_account_identifier']}</b> and <b>{$deviceAccount['og_account_password']}</b> " .
                        "to <b>{$deviceAccount['account_identifier']}</b> and <b>{$deviceAccount['account_password']}</b>. ";
                    }
                }

                $this->system_model->insert_log([
                    'title' => 4,
                    'details' => $logDetails,
                    'created_by_staff' => $this->session->staff_id,
                ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "User details saved", 'status' => 'success'));

                if ($postData["redirect"]) {
                    redirect($postData["redirect"]);
                }
                else {
                    redirect("/users/view/{$user["StaffID"]}/devices");
                }
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "User not saved", 'status' => 'error'));
            }

        }

        $deviceAccounts = $this->db->select("id, staff_id, account_type, account_identifier, account_password")
            ->from("device_accounts")
            ->where("staff_id", $id)
            ->where("deleted IS NULL")
            ->get()->result_array();


        return compact("deviceAccounts");
    }

    private function _tab_contents_permissions($user) {
        $id = $user["StaffID"];

        $countryAccess = $this->db->select("ca.*, c.country, c.iso")
            ->from("country_access AS ca")
            ->join("countries AS c", "c.country_id = ca.country_id", "inner")
            ->where("staff_accounts_id", $id)
            ->get()->result_array();

        if ($this->input->method() == 'post') {
            $postData = $this->input->post();

            $this->form_validation->set_rules('user[NewPassword]', 'New Password', 'min_length[4]');
            if ($postData['user']['NewPassword'] != "") {
                $this->form_validation->set_rules('user[ConfirmPassword]', 'Confirm Password', 'matches[user[NewPassword]]');
            }

            if ($this->form_validation->run()) {

                $userData = $postData['user'];

                $staffStatesDataToSave = [];
                $states = $postData['states'];

                $originalStates = implode("-", $postData['og_states']);
                $statesSelectedArray = [];
                foreach ($states as $state) {
                    if ($state['selected'] == 1) {
                        $staffStatesDataToSave[] = [
                            'country_id' => $this->config->item('country'),
                            'StaffID' => $id,
                            'StateID' => $state['StateID'],
                        ];

                        $statesSelectedArray[] = $state['StateID'];
                    }
                }
                $statesSelected = implode("-", $statesSelectedArray);

                $dateFields = [
                    'debit_expiry_date',
                    'start_date',
                    'licence_expiry',
                    'elec_licence_expiry',
                    'blue_card_expiry',
                ];

                foreach ($dateFields as $dateField) {
                    if ($this->system_model->isDateNotEmpty($userData[$dateField])) {
                        $userData[$dateField] = $this->customlib->formatDmyToYmd($userData[$dateField]);
                    }
                    else {
                        $userData[$dateField] = null;
                    }
                }

                $userFieldsToUpdate = [
                    'active',
                    'ClassID',
                    'sa_position',
                    'other_call_centre',
                ];

                $userDataToSave = [];
                foreach ($userFieldsToUpdate as $field) {
                    $userDataToSave[$field] = $userData[$field];
                }

                if (!empty($userData['NewPassword'])) {
                    $userDataToSave['password_new'] = password_hash($userData['NewPassword'], PASSWORD_DEFAULT);

                    $result = $this->users_model->encryptWithOldCRM($userData['NewPassword']);

                    if ($result['success']) {
                        $userDataToSave['Password'] = $result['encrypted'];
                    }

                }

                $staffStatesDataToSave = [];
                $states = $postData['states'];

                $originalStates = implode("-", $postData['og_states']);
                $statesSelectedArray = [];
                foreach ($states as $state) {
                    if ($state['selected'] == 1) {
                        $staffStatesDataToSave[] = [
                            'country_id' => $this->config->item('country'),
                            'StaffID' => $id,
                            'StateID' => $state['StateID'],
                        ];

                        $statesSelectedArray[] = $state['StateID'];
                    }
                }
                $statesSelected = implode("-", $statesSelectedArray);

                $oldUserData = $user;

                $this->db->trans_start();

                $affectedRows = 0;
                $returnValue = $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);
                $affectedRows += $this->db->affected_rows();

                if ($originalStates != $statesSelected) {
                    $this->db->delete('staff_states', "StaffID = {$id}");
                    if (!empty($staffStatesDataToSave)) {
                        $this->db->insert_batch('staff_states', $staffStatesDataToSave);
                    }
                    $affectedRows += $this->db->affected_rows();
                }

                $oldCountryAccessIds = [];
                foreach ($countryAccess as $ca) {
                    if ($ca["status"] == 1) {
                        $oldCountryAccessIds[] = $ca['country_id'];
                    }
                }
                $oldCountryAccess = implode("-", $oldCountryAccessIds);

                $countryAccessData =& $postData["country_access"];

                $newCountryAccessIds = [];
                foreach ($countryAccessData as $ca) {
                    if ($ca["status"] == 1) {
                        $newCountryAccessIds[] = $ca['country_id'];
                    }
                }
                $newCountryAccess = implode("-", $newCountryAccessIds);

                if ($oldCountryAccess != $newCountryAccess) {
                    $this->db->delete("country_access", "staff_accounts_id = {$id}");
                    $this->db->insert_batch("country_access", $countryAccessData);
                    $affectedRows += $this->db->affected_rows();
                }

                if ($userData["ClassID"] == 6) {
                    $accommodationData = $postData["accommodation"];

                    if (!empty($accommodationData["address"])) {
                        if (isset($accommodationData['accomodation_id'])) {
                            $this->db->update_batch("accomodation", [$accommodationData], "accomodation_id");
                            $affectedRows += $this->db->affected_rows();
                        }
                        else {
                            $this->db->insert("accomodation", $accommodationData);
                            $accommodationId = $this->db->insert_id();
                            $this->db->update("staff_accounts", [
                                "accomodation_id" => $accommodationId,
                            ], "StaffID = {$user["StaffID"]}");
                            $affectedRows += $this->db->affected_rows();
                        }
                    }
                }


                if ($affectedRows > 0) {
                    $logDetails = "{staff_user:{$id}}'s account updated. ";

                    if (isset($userDataToSave['password_new'])) {
                        $logDetails .= "Password changed. ";
                    }

                    if ($oldUserData['active'] != $userDataToSave['active']) {
                        $beforeStatus = $oldUserData['active'] == 1 ? 'active' : 'inactive';
                        $newStatus = $userDataToSave['active'] == 1 ? 'active' : 'inactive';
                        $logDetails .= "Status changed from {$beforeStatus} to {$newStatus}. ";
                    }

                    if ($oldUserData['ClassID'] != $userDataToSave['ClassID']) {
                        $staffClasses = $this->db->select('ClassID, ClassName')
                            ->from('staff_classes')
                            ->where_in('ClassID', [$oldUserData['ClassID'], $userDataToSave['ClassID']])
                            ->get()->result_array();

                        foreach ($staffClasses as $staffClass) {
                            if ($staffClass['ClassID'] == $oldUserData['ClassID']) {
                                $oldClass = $staffClass['ClassName'];
                            }
                            else if ($staffClass['ClassID'] == $userDataToSave['ClassID']) {
                                $newClass = $staffClass['ClassName'];
                            }
                        }

                        $logDetails .= "Class changed from {$oldClass} to {$newClass}. ";
                    }

                    $this->system_model->insert_log([
                        'title' => 4,
                        'details' => $logDetails,
                        'created_by_staff' => $this->session->staff_id,
                    ]);
                }

                $this->db->trans_complete();
                $this->session->set_flashdata(array('success_msg' => "User details saved", 'status' => 'success'));

                if ($postData["redirect"]) {
                    redirect($postData["redirect"]);
                }
                else {
                    redirect("/users/view/{$user["StaffID"]}/permissions");
                }
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "User not saved", 'status' => 'error'));
            }
        }

        $states = $this->db->select("sd.*, (ss.PermissionID IS NOT NULL) AS selected")
            ->from("states_def AS sd")
            ->join("staff_states AS ss", "ss.StateID = sd.StateID AND ss.StaffID = {$id}", "left")
            ->where("sd.country_id", $this->config->item("country"))
            ->order_by('sd.StateID', 'ASC')
            ->get()->result_array();

        $staffClasses = $this->db->select('*')
            ->from('staff_classes')
            ->get()->result_array();

        $callCentres = $this->db->select("StaffID, FirstName, LastName")
            ->from('staff_accounts')
            ->where('active', 1)
            ->where('Deleted', 0)
            ->where_in('ClassID', [7, 8])
            ->order_by('FirstName', 'ASC')
            ->get()->result_array();

        if ($user['accomodation_id']) {
            $accommodation = $this->db->select("*")
                ->from("accomodation")
                ->where("accomodation_id", $user['accomodation_id'])
                ->get()->row_array();
        }
        else {
            $accommodation = null;
        }

        return compact("countryAccess", "states", "staffClasses", "callCentres", "accommodation");
    }

    private function _tab_contents_licencing($user) {
        $id = $user["StaffID"];

        if ($this->input->method() == 'post') {
            $postData = $this->input->post();

            $userData = $postData['user'];

            $dateFields = [
                'licence_expiry',
                'blue_card_expiry',
                'elec_licence_expiry',
            ];

            foreach ($dateFields as $dateField) {
                if ($this->system_model->isDateNotEmpty($userData[$dateField])) {
                    $userData[$dateField] = $this->customlib->formatDmyToYmd($userData[$dateField]);
                }
                else {
                    $userData[$dateField] = null;
                }
            }

            $userFieldsToUpdate = [
                'license_num',
                'licence_expiry',
                'is_electrician',
                'elec_license_num',
                'elec_licence_expiry',
                'blue_card_num',
                'blue_card_expiry',
            ];

            $userDataToSave = [];
            foreach ($userFieldsToUpdate as $field) {
                $userDataToSave[$field] = $userData[$field];
            }

            $uploadFile = function($fileField, $uploadPath) use ($id) {
                //Upload front image
                if (isset($_FILES[$fileField]) && !empty($_FILES[$fileField]['name'])) {
                    $imagePath = $_FILES[$fileField]['name'];
                    $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
                    $imageName = "img_{$id}_" . rand() . "_" . date("YmdHis");
                    $uploadParams = array(
                        'file_name' => $imageName,
                        'upload_path' => $uploadPath,
                        'max_size' => '3000',
                        'allowed_types' => 'gif|jpg|jpeg|png'
                    );
                    if ($this->gherxlib->do_upload($fileField, $uploadParams)) {
                        return "{$imageName}.{$ext}";
                    }
                }
                return false;
            };

            $fileFields = [
                [
                    'fileField' => 'user_electrical_license',
                    'columnName' => 'electrical_license',
                    'uploadPath' => 'images/electrical_license/',
                ],
            ];

            foreach ($fileFields as $fileData) {
                $uploadResult = $uploadFile($fileData['fileField'], $fileData['uploadPath']);
                if ($uploadResult != false) {
                    $userDataToSave[$fileData['columnName']] = $uploadResult;
                }
            }

            $staffStatesDataToSave = [];
            $states = $postData['states'];

            $originalStates = implode("-", $postData['og_states']);
            $statesSelectedArray = [];
            foreach ($states as $state) {
                if ($state['selected'] == 1) {
                    $staffStatesDataToSave[] = [
                        'country_id' => $this->config->item('country'),
                        'StaffID' => $id,
                        'StateID' => $state['StateID'],
                    ];

                    $statesSelectedArray[] = $state['StateID'];
                }
            }
            $statesSelected = implode("-", $statesSelectedArray);

            $oldUserData = $user;

            $this->db->trans_start();

            $affectedRows = 0;
            $returnValue = $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);
            $affectedRows += $this->db->affected_rows();

            if ($originalStates != $statesSelected) {
                $this->db->delete('staff_states', "StaffID = {$id}");
                if (!empty($staffStatesDataToSave)) {
                    $this->db->insert_batch('staff_states', $staffStatesDataToSave);
                }
                $affectedRows += $this->db->affected_rows();
            }


            if ($affectedRows > 0) {
                $logDetails = "{staff_user:{$id}}'s account updated. ";

                if (isset($userDataToSave['password_new'])) {
                    $logDetails .= "Password changed. ";
                }

                if ($oldUserData['active'] != $userDataToSave['active']) {
                    $beforeStatus = $oldUserData['active'] == 1 ? 'active' : 'inactive';
                    $newStatus = $userDataToSave['active'] == 1 ? 'active' : 'inactive';
                    $logDetails .= "Status changed from {$beforeStatus} to {$newStatus}. ";
                }

                if ($oldUserData['ClassID'] != $userDataToSave['ClassID']) {
                    $staffClasses = $this->db->select('ClassID, ClassName')
                        ->from('staff_classes')
                        ->where_in('ClassID', [$oldUserData['ClassID'], $userDataToSave['ClassID']])
                        ->get()->result_array();

                    foreach ($staffClasses as $staffClass) {
                        if ($staffClass['ClassID'] == $oldUserData['ClassID']) {
                            $oldClass = $staffClass['ClassName'];
                        }
                        else if ($staffClass['ClassID'] == $userDataToSave['ClassID']) {
                            $newClass = $staffClass['ClassName'];
                        }
                    }
                }

                $this->system_model->insert_log([
                    'title' => 4,
                    'details' => $logDetails,
                    'created_by_staff' => $this->session->staff_id,
                ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "User details saved", 'status' => 'success'));

                if ($postData["redirect"]) {
                    redirect($postData["redirect"]);
                }
                else {
                    redirect("/users/view/{$user["StaffID"]}/licencing");
                }
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "User not saved", 'status' => 'error'));
            }
        }

        $vehicles = $this->db->select("number_plate")
            ->from('vehicles AS v')
            ->where('v.StaffID', $id)
            ->get()->result_array();

        return compact("vehicles");
    }

    public function _tab_contents_logs($user) {
        $id = $user["StaffID"];

        if ($this->input->method() == "post") {
            var_dump($this->input->post()); exit;
        }

        $data = [];

        $data['userLogs'] = $this->db->select('ul.*, who.FirstName, who.LastName')
            ->from('user_log AS ul')
            ->join('staff_accounts AS who', 'ul.added_by = who.StaffID', 'inner')
            ->where('ul.staff_id', $id)
            ->order_by('ul.date DESC')
            ->limit(10)
            ->get()->result_array();

        $data['userLogsCount'] = $this->db->select('IFNULL(COUNT(ul.user_log_id), 0) AS count')
            ->from('user_log AS ul')
            ->where('ul.staff_id', $id)
            ->get()->row()->count;

        $data['systemLogs'] = $this->logs_model->_tab_getLogs([
            'limit' => 10,
            'hook' => function ($db) use ($id) {
                
                // load logs involving this user
                if( $this->input->get_post('logs_inv_this_user') == 1 ){

                    $db->group_start();
                    $db->like('l.`details`', "{staff_user:{$id}}");
                    $db->or_group_start();
                    $db->where('l.`title`', 77);
                    $db->where('l.`created_by_staff', $id);
                    $db->group_end();
                    $db->group_end();

                }else{

                    $db->where('l.`title`', 77);
                    $db->where('l.`created_by_staff', $id);

                }          

            },
        ]);

        $data['systemLogsCount'] = $this->logs_model->_tab_getLogsCount([
            'hook' => function ($db) use ($id) {
                $db->group_start();
                $db->like('l.`details`', "{staff_user:{$id}}");
                $db->or_group_start();
                $db->where('l.`title`', 77);
                $db->where('l.`created_by_staff', $id);
                $db->group_end();
                $db->group_end();
            },
        ]);

        // print_r($data['userLogsCount']);
        // die();

        return $data;
    }

    public function ajax_update_fields() {
        $postData = $this->input->post();

        $this->db->trans_start();

        $affectedRows = 0;
        foreach($postData as $table => $tableData) {
            $idField = $tableData["_idField"];
            $idValue = $tableData["_idValue"];

            $this->db->set($tableData["fields"])
                ->where($idField, $idValue)
                ->update($table);

            $affectedRows += $this->db->affected_rows();
        }

        if ($affectedRows > 0) {
            $logDetails = "{staff_user:{$id}}'s account updated. ";

            $this->system_model->insert_log([
                'title' => 4,
                'details' => $logDetails,
                'created_by_staff' => $this->session->staff_id,
            ]);
        }

        $this->db->trans_complete();

        $success = $this->db->trans_status();

        $jsonData = [];
        $jsonData["success"] = $success;

        if ($success) {
            $jsonData["message"] = "Update successful.";
        }
        else {
            $jsonData["message"] = "Update failed.";
        }

        echo json_encode($jsonData);
        return;
    }

    public function update_location_access() {

        if ($this->input->method() == 'post') {
            $postData = $this->input->post();

            $id = $postData["StaffID"];

            $countryAccess = $this->db->select("ca.*, c.country, c.iso")
                ->from("country_access AS ca")
                ->join("countries AS c", "c.country_id = ca.country_id", "inner")
                ->where("staff_accounts_id", $id)
                ->get()->result_array();

            $oldCountryAccessIds = [];
            foreach ($countryAccess as $ca) {
                if ($ca["status"] == 1) {
                    $oldCountryAccessIds[] = $ca['country_id'];
                }
            }
            $oldCountryAccess = implode("-", $oldCountryAccessIds);

            $countryAccessData =& $postData["country_access"];

            $newCountryAccessIds = [];
            foreach ($countryAccessData as $ca) {
                if ($ca["status"] == 1) {
                    $newCountryAccessIds[] = $ca['country_id'];
                }
            }
            $newCountryAccess = implode("-", $newCountryAccessIds);

            if ($oldCountryAccess != $newCountryAccess) {
                $this->db->delete("country_access", "staff_accounts_id = {$id}");
                $this->db->insert_batch("country_access", $countryAccessData);
                $affectedRows += $this->db->affected_rows();
            }

            $staffStatesDataToSave = [];
            $states = $postData['states'] ?? [];

            $originalStates = implode("-", $postData['og_states'] ?? []);
            $statesSelectedArray = [];
            foreach ($states as $state) {
                if ($state['selected'] == 1) {
                    $staffStatesDataToSave[] = [
                        'country_id' => $this->config->item('country'),
                        'StaffID' => $id,
                        'StateID' => $state['StateID'],
                    ];

                    $statesSelectedArray[] = $state['StateID'];
                }
            }

            $statesSelected = implode("-", $statesSelectedArray);

            $this->db->trans_start();

            $affectedRows = 0;

            if ($originalStates != $statesSelected) {
                $this->db->delete('staff_states', "StaffID = {$id}");
                if (!empty($staffStatesDataToSave)) {
                    $this->db->insert_batch('staff_states', $staffStatesDataToSave);
                }
                $affectedRows += $this->db->affected_rows();
            }

            if ($affectedRows > 0) {
                $logDetails = "{staff_user:{$id}}'s account updated. ";

                $this->system_model->insert_log([
                    'title' => 4,
                    'details' => $logDetails,
                    'created_by_staff' => $this->session->staff_id,
                ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "User details saved", 'status' => 'success'));
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "User details not saved", 'status' => 'error'));
            }
        }
        $this->load->library('user_agent');

        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/users/view/{$staffId}");
        redirect($referrer);
    }

    public function update_address() {
        if ($this->input->method() == 'post') {
            $postData = $this->input->post();

            $id = $postData["StaffID"];
            $classID = $postData["ClassID"];

            $userDataToSave = $postData["user"];

            $this->db->trans_start();

            $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);

            if ($classID == 6) {
                $accommodationId = $postData["accommodation_id"];

                $accommodationData =& $postData['accommodation'];

                if (!is_null($accommodationId) && $accommodationId != "") {
                    $this->db->update("accomodation", $accommodationData, "accomodation_id = {$accommodationId}");
                }
                else {
                    $this->db->insert("accomodation", $accommodationData);

                    $accommodationId = $this->db->insert_id();
                    $this->db->update('staff_accounts', [
                        'accomodation_id' => $accommodationId,
                    ], "StaffID = {$id}");
                }
            }

            $logDetails = "{staff_user:{$id}}'s account updated. ";

            $this->system_model->insert_log([
                'title' => 4,
                'details' => $logDetails,
                'created_by_staff' => $this->session->staff_id,
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "User address saved", 'status' => 'success'));
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "User address not saved", 'status' => 'error'));
            }
        }

        $this->load->library('user_agent');
        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/users/view/{$staffId}");
        redirect($referrer);
    }

    //Function to update data in users / drivers license
    public function update_driver_license() {
        if ($this->input->method() == 'post') {
            $postData = $this->input->post();

            $id = $postData["StaffID"];

            $userDataToSave = $postData["user"];
            $userDataToSave["licence_expiry"] = $this->customlib->formatDmyToYmd($userDataToSave["licence_expiry"], true);
            //echo $userDataToSave["licence_expiry"];

            $uploadFile = function($fileField, $uploadPath) use ($id) {
                //Upload front image
                if (isset($_FILES[$fileField]) && !empty($_FILES[$fileField]['name'])) {
                    $imagePath = $_FILES[$fileField]['name'];
                    $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
                    $imageName = "img_{$id}_" . rand() . "_" . date("YmdHis");
                    $uploadParams = array(
                        'file_name' => $imageName,
                        'upload_path' => $uploadPath,
                        'max_size' => '3000',
                        'allowed_types' => 'gif|jpg|jpeg|png'
                    );
                    if ($this->gherxlib->do_upload($fileField, $uploadParams)) {
                        return "{$imageName}.{$ext}";
                    }
                }
                return false;
            };

            $fileFields = [
                [
                    'fileField' => 'user_driver_license',
                    'columnName' => 'driver_license',
                    'uploadPath' => 'images/driver_license/',
                ],
            ];

            foreach ($fileFields as $fileData) {
                $uploadResult = $uploadFile($fileData['fileField'], $fileData['uploadPath']);
                if ($uploadResult != false) {
                    $userDataToSave[$fileData['columnName']] = $uploadResult;
                }
            }

            if ($userDataToSave["driver_license"] == "") {
                $userDataToSave["driver_license"] = null;
            }

            $this->db->trans_start();

            $affectedRows = 0;
            $returnValue = $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);
            $affectedRows += $this->db->affected_rows();

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "Driver licence details saved", 'status' => 'success'));
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "Driver licence details not saved", 'status' => 'error'));
            }
        }

        $this->load->library('user_agent');
        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/users/view/{$staffId}");
        redirect($referrer);
    }

    public function update_electrical_license() {
        if ($this->input->method() == 'post') {

            //print_r($_POST);

            $postData = $this->input->post();

            $id = $postData["StaffID"];

            $userDataToSave = $postData["user"];

            $userDataToSave["elec_licence_expiry"] = $this->customlib->formatDmyToYmd($userDataToSave["elec_licence_expiry"], true);

            $uploadFile = function($fileField, $uploadPath) use ($id) {
                //Upload front image
                if (isset($_FILES[$fileField]) && !empty($_FILES[$fileField]['name'])) {
                    $imagePath = $_FILES[$fileField]['name'];
                    $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
                    $imageName = "img_{$id}_" . rand() . "_" . date("YmdHis");
                    $uploadParams = array(
                        'file_name' => $imageName,
                        'upload_path' => $uploadPath,
                        'max_size' => '3000',
                        'allowed_types' => 'gif|jpg|jpeg|png'
                    );
                    if ($this->gherxlib->do_upload($fileField, $uploadParams)) {
                        return "{$imageName}.{$ext}";
                    }
                }
                return false;
            };

            $fileFields = [
                [
                    'fileField' => 'user_electrical_license',
                    'columnName' => 'electrical_license',
                    'uploadPath' => 'images/electrical_license/',
                ],
            ];

            foreach ($fileFields as $fileData) {
                $uploadResult = $uploadFile($fileData['fileField'], $fileData['uploadPath']);
                if ($uploadResult != false) {
                    $userDataToSave[$fileData['columnName']] = $uploadResult;
                }
            }

            if ($userDataToSave["electrical_license"] == "") {
                $userDataToSave["electrical_license"] = null;
            }

            $this->db->trans_start();

            $affectedRows = 0;
            $returnValue = $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);
            $affectedRows += $this->db->affected_rows();

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "Electrical licence details saved", 'status' => 'success'));
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "Electrical licence details not saved", 'status' => 'error'));
            }
        }

        $this->load->library('user_agent');
        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/users/view/{$staffId}");
        redirect($referrer);
    }

    public function update_password() {

        $this->load->library('user_agent');
        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/users/view/{$staffId}");

        if ($this->input->method() == "post") {
            $postData = $this->input->post();

            $id = $postData["StaffID"];

            $userData = $postData['user'];

            $password = $this->users_model->encryptWithOldCRM($userData['CurrentPassword']);

            // check if current password exists
            $sttaffID = $this->db->select('StaffID')->where('password', $password['encrypted'])->limit(1)->get('staff_accounts')->row();
            if (!$sttaffID){
                $this->session->set_flashdata(array('error_msg' => "Current password does not exist", 'status' => 'error'));
                redirect($referrer);
            }


            $userDataToSave['password_new'] = password_hash($userData['NewPassword'], PASSWORD_DEFAULT);

            $result = $this->users_model->encryptWithOldCRM($userData['NewPassword']);

            if ($result['success']) {
                $userDataToSave['Password'] = $result['encrypted'];
            }

            $this->db->trans_start();

            $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);

            if ($affectedRows > 0) {
                $logDetails = "{staff_user:{$id}}'s account updated. ";

                $this->system_model->insert_log([
                    'title' => 4,
                    'details' => $logDetails,
                    'created_by_staff' => $this->session->staff_id,
                ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "Password saved", 'status' => 'success'));
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "Password not saved", 'status' => 'error'));
            }
        }


        redirect($referrer);
    }

    public function update_workdays() {
        if ($this->input->method() == "post") {
            $postData = $this->input->post();

            $id = $postData["StaffID"];

            $pdWorkingDays = $postData['working_days'];

            $activeWorkingDays = [];
            foreach ($pdWorkingDays as $abbr => $active) {
                if ($active) {
                    $activeWorkingDays[] = $abbr;
                }
            }
            $userDataToSave = [];

            $userDataToSave['working_days'] = implode(',', $activeWorkingDays);

            $this->db->trans_start();
            $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);

            //tech account only
            if ($postData["ClassID"] == 6) {
                if ($postData["has_already_working_hours"] == 1) {
                    $data = array(
                        'working_hours' => $postData["working_hours"],
                    );
                    
                    $this->db->where('staff_id', $postData["StaffID"]);
                    $this->db->update('tech_working_hours', $data);
                } else {
                    $data = array(
                        'staff_id' => $postData["StaffID"],
                        'working_hours' => $postData["working_hours"],
                    );
                    
                    $this->db->insert('tech_working_hours', $data);
                }
            }
            

            $logDetails = "{staff_user:{$id}}'s account updated. ";

            $this->system_model->insert_log([
                'title' => 4,
                'details' => $logDetails,
                'created_by_staff' => $this->session->staff_id,
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "Working days updated", 'status' => 'success'));
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "Working days not updated", 'status' => 'error'));
            }
        }

        $this->load->library('user_agent');
        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/users/view/{$staffId}");
        redirect($referrer);
    }

    public function update_device_accounts() {

        if ($this->input->method() == 'post') {
            $postData = $this->input->post();

            $staffId = $postData['StaffID'];

            $deviceAccountsPostData = $postData['device_accounts'];
            $deviceAccountsToUpdate = [];
            $deviceAccountsToUpdateWithOG = [];
            $deviceAccountsToCreate = [];
            foreach ($deviceAccountsPostData as $deviceAccount) {
                if ($deviceAccount['will_delete']) {
                    $deviceAccount['deleted'] = date('Y-m-d');
                    $deviceAccountsToUpdateWithOG[] = $deviceAccount;
                    unset($deviceAccount['og_account_identifier']);
                    unset($deviceAccount['og_account_password']);
                    unset($deviceAccount['will_delete']);
                    $deviceAccountsToUpdate[] = $deviceAccount;
                }
                else if (
                    isset($deviceAccount['id']) &&
                    (
                        $deviceAccount['og_account_identifier'] != $deviceAccount['account_identifier'] ||
                        $deviceAccount['og_account_password'] != $deviceAccount['account_password']
                    )
                ) {
                    $deviceAccountsToUpdateWithOG[] = $deviceAccount;
                    unset($deviceAccount['og_account_identifier']);
                    unset($deviceAccount['og_account_password']);
                    $deviceAccountsToUpdate[] = $deviceAccount;
                }
                else if (!isset($deviceAccount['id'])) {
                    unset($deviceAccount['og_account_identifier']);
                    unset($deviceAccount['og_account_password']);
                    $deviceAccountsToCreate[] = $deviceAccount;
                }
            }

            $this->db->trans_start();

            if (!empty($deviceAccountsToUpdate)) {
                $this->db->update_batch('device_accounts', $deviceAccountsToUpdate, 'id');
                $affectedRows += $this->db->affected_rows();
            }
            if (!empty($deviceAccountsToCreate)) {
                $this->db->insert_batch('device_accounts', $deviceAccountsToCreate);
                $affectedRows += $this->db->affected_rows();
            }

            if ($affectedRows > 0) {
                $logDetails = "{staff_user:{$staffId}}'s account updated. ";

                foreach ($deviceAccountsToCreate as $deviceAccount) {
                    $logDetails .= "Device account for <b>{$deviceAccount['account_type']}</b> added. ";
                }
                foreach ($deviceAccountsToUpdateWithOG as $deviceAccount) {
                    if (isset($deviceAccount['deleted'])) {
                        $logDetails .= "Device account for <b>{$deviceAccount['account_type']}</b> removed. " .
                        "Details were: <b>{$deviceAccount['og_account_identifier']}</b> and  <b>{$deviceAccount['og_account_password']}</b>. ";
                    }
                    else if (
                        $deviceAccount['og_account_identifier'] != $deviceAccount['account_identifier'] ||
                        $deviceAccount['og_account_password'] != $deviceAccount['account_password']
                    ) {
                        $logDetails .= "Device account for <b>{$deviceAccount['account_type']}</b> updated from " .
                        "<b>{$deviceAccount['og_account_identifier']}</b> and <b>{$deviceAccount['og_account_password']}</b> " .
                        "to <b>{$deviceAccount['account_identifier']}</b> and <b>{$deviceAccount['account_password']}</b>. ";
                    }
                }

                $this->system_model->insert_log([
                    'title' => 4,
                    'details' => $logDetails,
                    'created_by_staff' => $this->session->staff_id,
                ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "Device accounts updated", 'status' => 'success'));
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "Device accounts not updated", 'status' => 'error'));
            }
        }

        $this->load->library('user_agent');
        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/users/view/{$staffId}");
        redirect($referrer);
    }

    public function upload_profile_pic() {

        if ($this->input->method() == "post") {
            $postData = $this->input->post();

            $id = $postData["StaffID"];

            $userDataToSave = [];

            $uploadFile = function($fileField, $uploadPath) use ($id) {
                //Upload front image
                if (isset($_FILES[$fileField]) && !empty($_FILES[$fileField]['name'])) {
                    $imagePath = $_FILES[$fileField]['name'];
                    $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
                    $imageName = "img_{$id}_" . rand() . "_" . date("YmdHis");
                    $uploadParams = array(
                        'file_name' => $imageName,
                        'upload_path' => $uploadPath,
                        'max_size' => '3000',
                        'allowed_types' => 'gif|jpg|jpeg|png'
                    );
                    if ($this->gherxlib->do_upload($fileField, $uploadParams)) {
                        return "{$imageName}.{$ext}";
                    }
                }
                return false;
            };

            $fileFields = [
                [
                    'fileField' => 'user_profile_pic',
                    'columnName' => 'profile_pic',
                    'uploadPath' => 'images/staff_profile/',
                ],
            ];

            foreach ($fileFields as $fileData) {
                $uploadResult = $uploadFile($fileData['fileField'], $fileData['uploadPath']);
                if ($uploadResult != false) {
                    $userDataToSave[$fileData['columnName']] = $uploadResult;
                }
            }

            $this->db->trans_start();

            $this->db->update('staff_accounts', $userDataToSave, "StaffID = {$id}", 1);

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata(array('success_msg' => "Profile photo uploaded.", 'status' => 'success'));
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "Profile Photo not uploaded", 'status' => 'error'));
            }
        }

        $this->load->library('user_agent');
        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/users/view/{$staffId}");
        redirect($referrer);
    }

    public function remove_profile_pic($staffId) {
        $success = false;
        if ($this->input->method() == "delete") {
            $result = $this->db->set(
                [
                    "profile_pic" => null,
                ])
                ->where("StaffID", $staffId)
                ->update("staff_accounts");

            if ($result) {
                $success = true;
            }
        }

        echo json_encode([
            "success" => $success,
        ]);
    }

    public function add_vaccination() {
        $success = false;

        if ($this->input->method() == "post") {
            $postData = $this->input->post();

            $vaccinationData = $postData["vaccination"];

            $vaccinationData["completed_on"] = $this->customlib->formatDmyToYmd($vaccinationData["completed_on"], true);
            $vaccinationData["valid_till"] = $this->customlib->formatDmyToYmd($vaccinationData["valid_till"], true);

            if (empty($vaccinationData["valid_till"])) {
                $vaccinationData["valid_till"] = null;
            }

            $fileField = "vaccination_certificate_image";
            $uploadPath = "uploads/vaccination_certificates/";

            if (isset($_FILES[$fileField]) && !empty($_FILES[$fileField]['name'])) {
                $imagePath = $_FILES[$fileField]['name'];
                $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
                $imageName = "img_{$vaccinationData["StaffID"]}_" . rand() . "_" . date("YmdHis");
                $uploadParams = array(
                    'file_name' => $imageName,
                    'upload_path' => $uploadPath,
                    'allowed_types' => 'gif|jpg|jpeg|png'
                );

                if ($this->gherxlib->do_upload($fileField, $uploadParams)) {
                    $vaccinationData["certificate_image"] = "{$imageName}.{$ext}";
                }
            }

            $this->db->trans_start();

            $success = $this->db->set($vaccinationData)
                ->insert("vaccinations");

            $vaccinationData["vaccination_id"] = $this->db->insert_id();

            $this->db->trans_complete();

            $success = $this->db->trans_status();
        }
        else {
            $vaccinationData = null;
        }

        echo json_encode([
            "success" => $success,
            "vaccination" => $vaccinationData,
        ]);
    }

    public function edit_vaccination() {
        $success = false;

        if ($this->input->method() == "post") {
            $postData = $this->input->post();

            $vaccinationId = $postData["vaccination_id"];

            $vaccinationData = $postData["vaccination"];

            $vaccinationData["completed_on"] = $this->customlib->formatDmyToYmd($vaccinationData["completed_on"], true);
            $vaccinationData["valid_till"] = $this->customlib->formatDmyToYmd($vaccinationData["valid_till"], true);

            if (empty($vaccinationData["valid_till"])) {
                $vaccinationData["valid_till"] = null;
            }

            $fileField = "vaccination_certificate_image";
            $uploadPath = "uploads/vaccination_certificates/";

            if (isset($_FILES[$fileField]) && !empty($_FILES[$fileField]['name'])) {
                $imagePath = $_FILES[$fileField]['name'];
                $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
                $imageName = "img_{$vaccinationData["StaffID"]}_" . rand() . "_" . date("YmdHis");
                $uploadParams = array(
                    'file_name' => $imageName,
                    'upload_path' => $uploadPath,
                    'allowed_types' => 'gif|jpg|jpeg|png'
                );

                if ($this->gherxlib->do_upload($fileField, $uploadParams)) {
                    $vaccinationData["certificate_image"] = "{$imageName}.{$ext}";
                }
            }

            if (empty($vaccinationData["certificate_image"])) {
                $vaccinationData["certificate_image"] = null;
            }

            $this->db->trans_start();

            $success = $this->db
                ->set($vaccinationData)
                ->where("vaccination_id", $vaccinationId)
                ->update("vaccinations");

            $this->db->trans_complete();

            $success = $this->db->trans_status();
        }
        else {
            $vaccinationData = null;
        }

        echo json_encode([
            "success" => $success,
            "vaccination" => $vaccinationData,
        ]);
    }

    public function delete_vaccination($vaccinationId) {
        $success = false;

        if ($this->input->method() == "delete") {
            $this->db->trans_start();

            $this->db->where("vaccination_id", $vaccinationId)
                ->delete("vaccinations");

            $this->db->trans_complete();

            $success = $this->db->trans_status();
        }

        echo json_encode([
            "success" => $success,
        ]);
    }

    public function user_logs($id) {
        $this->load->library('pagination');

        $data = [
            'id' => $id,
            'title' => "User Logs",
        ];

        $limit = 25;

        $data['logs'] = $this->db->select('ul.*, who.FirstName, who.LastName')
            ->from('user_log AS ul')
            ->join('staff_accounts AS who', 'ul.added_by = who.StaffID', 'inner')
            ->where('ul.staff_id', $id)
            ->order_by('ul.date DESC')
            ->limit($limit)
            ->offset($this->input->get('offset'))
            ->get()->result_array();

        $data['logsCount'] = $totalRows = $this->db->select('IFNULL(COUNT(ul.user_log_id), 0) AS count')
            ->from('user_log AS ul')
            ->where('ul.staff_id', $id)
            ->get()->row()->count;

        $config = [];
        $config['base_url'] = "/users/user_logs/{$id}";
        $config['enable_query_strings'] = true;
        $config['page_query_string'] = true;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $totalRows;
        $config['per_page'] = $limit;

        $this->pagination->initialize($config);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('users/user_logs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function system_logs($id) {
        $this->load->model('logs_model');

        $this->load->library('pagination');

        $data = [
            'id' => $id,
            'title' => "System Logs",
        ];

        $limit = 25;

        $data['logs'] = $this->logs_model->getLogs([
            'limit' => $limit,
            'offset' => $this->input->get('offset'),
            'hook' => function ($db) use ($id) {
                $db->group_start();
                $db->like('l.`details`', "{staff_user:{$id}}");
                $db->or_group_start();
                $db->where('l.`title`', 77);
                $db->where('l.`created_by_staff', $id);
                $db->group_end();
                $db->group_end();
            },
        ]);

        $data['logsCount'] = $totalRows = $this->logs_model->getLogsCount([
            'hook' => function ($db) use ($id) {
                $db->group_start();
                $db->like('l.`details`', "{staff_user:{$id}}");
                $db->or_group_start();
                $db->where('l.`title`', 77);
                $db->where('l.`created_by_staff', $id);
                $db->group_end();
                $db->group_end();
            },
        ]);

        $config = [];
        $config['base_url'] = "/users/system_logs/{$id}";
        $config['enable_query_strings'] = true;
        $config['page_query_string'] = true;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $totalRows;
        $config['per_page'] = $limit;

        $this->pagination->initialize($config);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('users/system_logs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function add_log($staffId) {
        $this->load->library('user_agent');

        if ($this->input->method() == "post") {
            $this->form_validation->set_rules('date', 'Date', 'required');
            $this->form_validation->set_rules('details', 'Details', 'required');

            if ($this->form_validation->run()) {
                $postData = $this->input->post();

                $saveData = [
                    'date' => $this->customlib->formatDmyToYmdhis($postData['date']),
                    'details' => $postData['details'],
                    'staff_id' => $staffId,
                    'added_by' => $this->session->staff_id,
                ];

                $result = $this->db->insert("user_log", $saveData);

                if ($result) {
                    $this->session->set_flashdata(array('success_msg' => "Log added successfully", 'status' => 'success'));
                }
                else {
                    $this->session->set_flashdata(array('error_msg' => "Log not saved", 'status' => 'error'));
                }
            }
            else {
                $this->session->set_flashdata(array('error_msg' => "Log not saved", 'status' => 'error'));
            }
        }

        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/users/view/{$staffId}");
        redirect($referrer);
    }

    public function ajax_delete_user_log() {
        if ($this->input->method() == "post") {

            $userLogId = $this->input->post('user_log_id');

            if ($userLogId != null) {
                $result = $this->db->where("user_log_id", $userLogId)->delete('user_log');
                if ($result) {
                    echo json_encode([
                        'success' => true,
                    ]);
                    return;
                }
            }
        }

        echo json_encode([
            'success' => false,
        ]);
    }

    public function index()
    {
        $this->load->model('vehicles_model');

        $data['title'] = "SATS Users";

        $country_id = $this->config->item('country');
        $class_filter = $this->input->get_post('class_fil');
        $show_all = $this->input->get_post('show_all');
        $search = $this->input->get_post('search_filter');
        $search_tech = $this->input->get_post('search_filter_tech');
        $state_filter_tech = $this->input->get_post('state_filter_tech');
        $state_filter_admin = $this->input->get_post('state_filter_admin');
        $export = $this->input->get_post('export');
        $tab = $this->input->get_post('tab');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $data['logged_user_id'] = $this->session->staff_id;
        $data['logged_user_class'] = $this->system_model->getStaffClassID();
        $data['allowed_user_class_to_edit_arr'] = array(2,9);

        if( $country_id == 1 ){ // AU
            // Sarah Guthrie
            $data['allowed_user_to_edit_arr'] = array(2226);
        }else if( $country_id == 2 ){ // NZ
            $data['allowed_user_to_edit_arr'] = [];
        }

        if( $show_all && $show_all==1 ){
            $sa_opt_deleted = NULL;
            $sa_opt_active = NULL;
        }else{
            $sa_opt_deleted = 0;
            $sa_opt_active= 1;
        }

        $sel_query = "
        DISTINCT(sa.staffID),
        sa.`staffID` AS sa_staffid,
		sa.`FirstName` AS sa_firstname,
		sa.`LastName` AS sa_lastname,
        sa.`sa_position` AS sa_position,
        sa.`contactNumber` AS contactNum,
        sa.`Email` AS sa_email,
        sa.`ipad_prepaid_serv_num AS sa_ipad_prepaid_serv_num`,
        sa.`TechID` AS sa_techID,
        sa.`active` AS sa_active,
        sa.address AS sa_address,
        sa.password_new,
        sa.Password,

        sc.`className` AS sc_classname,
        sc.`ClassID` AS sc_classID
        ";

        //TECHS MAIN QUERY
        $params = array(
            'sel_query' => $sel_query,
            'join_table' => array('cc','accomodation'),
            'sa_deleted' => $sa_opt_deleted,
            'sa_active' => $sa_opt_active,
            'class_filter'=>6,
            'state' => $state_filter_tech,
            'search' => $search_tech,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'sa.LastName',
                    'sort' => 'ASC',
                )
            ),
        );

        if( $export == 1 && $tab == 1){
            $tech_sql = $this->users_model->get_users($params);

            // file name
			$filename = 'tech_user'.date('YmdHis').rand().'.csv';

			header("Content-type: application/csv");
			header("Content-Disposition: attachment; filename={$filename}");
			header("Pragma: no-cache");
			header("Expires: 0");

			// file creation
			$file = fopen('php://output', 'w');

			// csv header
			$csv_header = []; // clear
			$csv_header = array( 'Name', 'Position', 'Phone', 'Email', 'Ipad Service', 'Class', 'ID', 'Status');
			fputcsv($file, $csv_header);

			// csv row
			foreach ( $tech_sql->result() as $tech_row ) {

				$csv_row = [];
				$csv_row = array(

					"{$tech_row->sa_firstname} {$tech_row->sa_lastname}",
					"{$tech_row->sa_position}",
					"{$tech_row->contactNum}",
					"{$tech_row->sa_email}",
					"{$tech_row->sa_ipad_prepaid_serv_num}",
					"{$tech_row->sc_classname}",
					"{$tech_row->sa_staffid}",
					( $tech_row->sa_active == 1 )? 'Active': 'Inactive'
				);

				fputcsv($file, $csv_row);

			}

			fclose($file);

        } else {
            $data['lists_tech'] = $this->users_model->get_users($params);

            //ADMIN MAIN QUERY
            $custom_where_admin = "sa.ClassID!=6";
            $params = array(
                'sel_query' => $sel_query,
                'join_table' => array('cc'),
                'sa_deleted' => $sa_opt_deleted,
                'sa_active' => $sa_opt_active,
                'class_filter' => $class_filter,
                'custom_where' => $custom_where_admin,
                'state' => $state_filter_admin,
                'search' => $search,
                'sort_list' => array(
                    array(
                        'order_by' => 'sa.FirstName',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'sa.LastName',
                        'sort' => 'ASC',
                    )
                ),
            );

            if( $export == 1 && $tab == 2){
                $admin_sql = $this->users_model->get_users($params);
    
                // file name
                $filename = 'admin_user'.date('YmdHis').rand().'.csv';
    
                header("Content-type: application/csv");
                header("Content-Disposition: attachment; filename={$filename}");
                header("Pragma: no-cache");
                header("Expires: 0");
    
                // file creation
                $file = fopen('php://output', 'w');
    
                // csv header
                $csv_header = []; // clear
                $csv_header = array( 'Name', 'Position', 'Phone', 'Email', 'Class', 'ID', 'Status');
                fputcsv($file, $csv_header);
    
                // csv row
                foreach ( $admin_sql->result() as $admin_row ) {
    
                    $csv_row = [];
                    $csv_row = array(
    
                        "{$admin_row->sa_firstname} {$admin_row->sa_lastname}",
                        "{$admin_row->sa_position}",
                        "{$admin_row->contactNum}",
                        "{$admin_row->sa_email}",
                        "{$admin_row->sc_classname}",
                        "{$admin_row->sa_staffid}",
                        ( $admin_row->sa_active == 1 )? 'Active': 'Inactive'
                    );
    
                    fputcsv($file, $csv_row);
    
                }
    
                fclose($file);
    
            } else {
            $data['lists_admin'] = $this->users_model->get_users($params);

            //Class Filter
            $sel_query = "DISTINCT(sc.`ClassID`) AS sc_classID,
            sc.`ClassName` AS sc_classname";
            $params = array(
                'sel_query' => $sel_query,
                'sa_deleted' => $sa_opt_deleted,
                'sa_active' => $sa_opt_active,
                'custom_where' => $custom_where_admin,
                'sort_list' => array(
                    array(
                        'order_by' => 'sc.`ClassName`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['class_filter'] = $this->users_model->get_users($params);

            //states dropdown filter
            $state_query = $this->db->select('state')->from('states_def')->where('country_id', $country_id)->order_by('state','ASC')->get();
            $data['state_filter'] = $state_query->result_array();

            //views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('users/view_users', $data);
            $this->load->view('templates/inner_footer', $data);
            }
        }
    }

    public function incident_and_injury_report_list() {

        $data['title'] = "Incident Summary";

        $date_f = $this->input->get_post('date_from_filter');
        $date_t = $this->input->get_post('date_to_filter');
        $date_from = ($date_f != "") ? $this->system_model->formatDate($date_f) : NULL;
        $date_to = ($date_t != "") ? $this->system_model->formatDate($date_t) : NULL;
        $staff = $this->input->get_post('staff');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');


        $sel_query = "
            iai.`incident_and_injury_id`,
            iai.`datetime_of_incident`,
            iai.`nature_of_incident`,
            iai.`location_of_incident`,
            iai.`describe_incident`,
            iai.`ip_name`,
            iai.`created_date`,
            sa.active
        ";
        $params = array(
            'sel_query' => $sel_query,
            'from' => $date_from,
            'to' => $date_to,
            'staff_active' => $staff,
            'sort_list' => array(
                array(
                    'order_by' => 'iai.`created_date`',
                    'sort' => 'DESC'
                ),
            ),
            'limit' => $per_page,
            'offset' => $offset,
        );
        $data['lists'] = $this->users_model->getIncidentAndReport($params);

        //Total rows
        $params_total = array(
            'sel_query' => "COUNT(iai.`incident_and_injury_id`) as iai_count",
            'from' => $date_from,
            'to' => $date_to,
            'staff_active' => $staff
        );
        $total_row_query = $this->users_model->getIncidentAndReport($params_total);
        $total_rows = $total_row_query->row()->iai_count;

        $pagi_links_params_arr = array(
            'date_from_filter' => $date_from,
            'date_to_filter' => $date_to,
            'staff' => $staff
        );

        $pagi_link_params = '/users/incident_and_injury_report_list/?' . http_build_query($pagi_links_params_arr);

        // pagination settings
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


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/incident_and_injury_report_list', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function incident_and_injury_report_pdf() {
        $this->load->library('JPDF');
        $iai_id = $this->uri->segment(3);


        if ($iai_id && is_numeric($iai_id)) {

            // Incident PDF TEMPLATE
            //$this->getIncidentAndReportPdf("I", $iai_id);
            $this->getIncidentAndReportPdf_v2("I", $iai_id);
        } else {
            redirect(base_url('/users/incident_and_injury_report_list'));
        }
    }

    /**
     * Add Incident and Injury Report FORM
     */
    public function incident_and_injury_report() {

        $data['title'] = "Incident Form";

        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/incident_and_injury_report', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Add New Incident Report Script
     * Action Insert insert_incident_and_injury
     * Action Insert Photo
     * Action Email Data and attached PDF
     */
    public function incident_and_injury_report_script() {

        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->library('email');

        // The incident Post
        $date_of_incident = $this->input->post('date_of_incident');
        $date_of_incident2 = $this->system_model->formatDate($date_of_incident);
        $time_of_incident = $this->input->post('time_of_incident');
        $datetime_of_incident = "{$date_of_incident2} {$time_of_incident}:00";
        $nature_of_incident = $this->input->post('nature_of_incident');
        $loc_of_inci = $this->input->post('loc_of_inci');
        $desc_inci = $this->input->post('desc_inci');

        // Injured Person Details Post
        $ip_name = $this->input->post('ip_name');
        $ip_address = $this->input->post('ip_address');
        $ip_occu = $this->input->post('ip_occu');
        $ip_dob = $this->input->post('ip_dob');
        $ip_dob2 = $this->system_model->formatDate($ip_dob);
        $ip_tel_num = $this->input->post('ip_tel_num');
        $ip_employer = $this->input->post('ip_employer');
        $ip_noi = $this->input->post('ip_noi');
        $ip_loi = $this->input->post('ip_loi');
        $ip_onsite_treatment = $this->input->post('ip_onsite_treatment');
        $ip_fur_treat = $this->input->post('ip_fur_treat');

        // Witness Details Post
        $witness_name = $this->input->post('witness_name');
        $witness_contact = $this->input->post('witness_contact');

        // Outcome Post
        $loss_time_injury = $this->input->post('loss_time_injury');
        $reported_to = $this->input->post('reported_to');
        $confirm_chk = $this->input->post('confirm_chk');

        $created_by = $this->session->staff_id;
        //photo
        $photo_of_incident = $this->input->post('photo_of_incident'); //array
        //Insert to incident_and_injury
        $insert_data = array(
            'datetime_of_incident' => $datetime_of_incident,
            'nature_of_incident' => $nature_of_incident,
            'location_of_incident' => $loc_of_inci,
            'describe_incident' => $desc_inci,
            'ip_name' => $ip_name,
            'ip_address' => $ip_address,
            'ip_occupation' => $ip_occu,
            'ip_dob' => $ip_dob2,
            'ip_tel_num' => $ip_tel_num,
            'ip_employer' => $ip_employer,
            'ip_noi' => $ip_noi,
            'ip_loi' => $ip_loi,
            'ip_onsite_treatment' => $ip_onsite_treatment,
            'ip_further_treatment' => $ip_fur_treat,
            'witness_name' => $witness_name,
            'witness_contact' => $witness_contact,
            'loss_time_injury' => $loss_time_injury,
            'reported_to' => $reported_to,
            'confirm_chk' => $confirm_chk,
            'created_by' => $created_by,
            'country_id' => $this->config->item('country')
        );
        $insert_incident_and_injury = $this->users_model->insert_incident_and_injury($insert_data);
        $iai_id = $insert_incident_and_injury; // last id
        //UPLOAD PHOTO
        $country_iso = strtolower($this->gherxlib->get_country_iso());
        $new_file_name = "incident" . rand() . '_' . date('YmdHis');
        $upload_path = "./images/incident/{$country_iso}/";
        $upload_folter = "/images/incident/{$country_iso}/"; //note without dot

        $countfiles = count($_FILES['photo_of_incident']['name']);

        //make directory if not exist and set permission to 777
        if (!is_dir($upload_folter)) {
            mkdir($upload_path, 0777, true);
        }

        // Looping all files
        for ($i = 0; $i < $countfiles; $i++) {

            if (!empty($_FILES['photo_of_incident']['name'][$i])) {
                // New Files value
                $_FILES['file']['name'] = $_FILES['photo_of_incident']['name'][$i];
                $_FILES['file']['type'] = $_FILES['photo_of_incident']['type'][$i];
                $_FILES['file']['tmp_name'] = $_FILES['photo_of_incident']['tmp_name'][$i];
                $_FILES['file']['error'] = $_FILES['photo_of_incident']['error'][$i];
                $_FILES['file']['size'] = $_FILES['photo_of_incident']['size'][$i];

                //set upload config
                $upload_params = array(
                    'file_name' => $new_file_name,
                    'upload_path' => $upload_path,
                    'max_size' => '3000', //1mb
                    'allowed_types' => 'gif|jpg|png'
                );
                $upload = $this->gherxlib->do_upload('file', $upload_params);

                if ($upload) {
                    //upload photo data
                    $uploadData = $this->upload->data();
                    $photo_data = array(
                        'incident_and_injury_id' => $iai_id,
                        'image_name' => $upload_folter . "" . $uploadData['file_name']
                    );
                    $this->users_model->upload_photo_data($photo_data);
                } else {
                    if ($countfiles > 0) {
                        $error_msg = "There was a problem saving your attachment, please check and try again";
                        $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                        redirect(base_url('/users/incident_and_injury_report_details/' . $iai_id), 'refresh');
                    }
                }
            }
        }
        //UPLOAD PHOTO END
        // EMAIL AND ATTACHED INCIDENT INFO/DATA (PDF)
        $getCountryInfo = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));
        $pdf_attahcment_name = 'incident_and_injury_report_' . date('dmYHis') . '.pdf';

        // Incident PDF TEMPLATE
        $getIncidentAndReportPdf_template = $this->getIncidentAndReportPdf("S", $iai_id);


        $email_from = $getCountryInfo->outgoing_email;
        $email_to = $this->config->item('sats_hr_email');
        $email_subject = "Incident Report";

        //email config
        $config = Array(
            'mailtype' => 'html',
            'charset' => 'iso-8859-1'
        );

        $this->email->initialize($config);
        $this->email->set_newline("\r\n");

        $this->email->from($email_from, $this->config->item('COMPANY_FULL_NAME'));
        $this->email->to($email_to);
        $this->email->subject($email_subject);
        $body = $this->load->view('emails/incident_report.php', $email_data, TRUE);
        $this->email->message($body);
        $this->email->attach($getIncidentAndReportPdf_template, 'attachment', $pdf_attahcment_name, 'application/pdf');
        $this->email->send();
        // END EMAIL
        //SET SUCCESS FLASH SESSION
        $success_message = "Submission Success";
        $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
        redirect(base_url('/users/incident_and_injury_report_list'), 'refresh');
    }

    /**
     * Incident And Report PDF Template
     * params iai iD
     * params output (pdf output)
     */
    public function getIncidentAndReportPdf($output, $iai_id) {
        $this->load->library('JPDF');
        // get incident query
        $sel_query = "
           iai.`incident_and_injury_id`,
           iai.`datetime_of_incident`,
           iai.`nature_of_incident`,
           iai.`location_of_incident`,
           iai.`describe_incident`,
           iai.`ip_name`,
           iai.`created_date`,

           iai.`ip_address`,
           iai.`ip_occupation`,
           iai.`ip_dob`,
           iai.`ip_tel_num`,
           iai.`ip_employer`,
           iai.`ip_noi`,
           iai.`ip_loi`,
           iai.`ip_onsite_treatment`,
           iai.`ip_further_treatment`,
           iai.`witness_name`,
           iai.`witness_contact`,
           iai.`loss_time_injury`,
           sa.`FirstName`,
           sa.`LastName`
       ";
        $params = array(
            'sel_query' => $sel_query,
            'iai_id' => $iai_id
        );
        $iai_sql = $this->users_model->getIncidentAndReport($params);
        $iai = $iai_sql->row_array();

        // pdf initiation
        $pdf = new JPDF();
        $pdf->SetTitle('Incident Report');
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
        $cell_width2 = 128;
        $cell_height = 6;
        $cell_border = 0;
        $col1_cell_new_line = 0;
        $col2_cell_new_line = 1;
        $col1_cell_align = 'L';
        $col2_cell_align = 'L';


        // THE INCIDENT
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'The Incident', $header_border, $header_new_line, $header_align);
        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Date of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('d/m/Y', strtotime($iai['datetime_of_incident'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Time of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('H:i', strtotime($iai['datetime_of_incident'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Nature of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $this->users_model->getNatureOfIncident($iai['nature_of_incident']), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Location of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['location_of_incident'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Describe the incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->MultiCell($cell_width2, $cell_height, $iai['describe_incident']);


        $pdf->Ln($header_space);


        // INJURED PERSON DETAILS
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Injured Person Details', $header_border, $header_new_line, $header_align);
        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_name'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Address: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_address'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Occupation: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_occupation'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Date of birth: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('d/m/Y', strtotime($iai['ip_dob'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Telephone number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_tel_num'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Employer: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_employer'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Nature of Injury: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_noi'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Location of Injury: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_loi'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Onsite treatment: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, ( ( is_numeric($iai['ip_onsite_treatment']) && $iai['ip_onsite_treatment'] == 1 ) ? 'Yes' : 'No'), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Further treatment required?: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, ( ( is_numeric($iai['ip_further_treatment']) && $iai['ip_further_treatment'] == 1 ) ? 'Yes' : 'No'), $cell_border, $col2_cell_new_line, $col2_cell_align);


        $pdf->Ln($header_space);


        // WITNESS DETAILS
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Witness Details', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['witness_name'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Contact Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['witness_contact'], $cell_border, $col2_cell_new_line, $col2_cell_align);


        $pdf->Ln($header_space);


        // OUTCOME
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Outcome', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Time lost due to injury: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, ( ( is_numeric($iai['loss_time_injury']) && $iai['loss_time_injury'] == 1 ) ? 'Yes' : 'No'), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Who was the incident reported to?: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['FirstName'] . ' ' . $iai['LastName'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Report Submitted: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('d/m/Y H:i', strtotime($iai['created_date'])), $cell_border, $col2_cell_new_line, $col2_cell_align);


        $file_name = 'incident_and_injury_report_' . date('dmYHis') . '.pdf';

        return $pdf->Output($file_name, $output);
    }

    /**
     * Incident and Injury Detail Page
     */
    public function incident_and_injury_report_details() {

        $data['title'] = "Incident and Injury Report Details";

        $data['iai_id'] = $this->uri->segment(3);

        if ($data['iai_id'] && is_numeric($data['iai_id'])) {

            $sel_query = "
                iai.`incident_and_injury_id`,
                iai.`datetime_of_incident`,
                iai.`nature_of_incident`,
                iai.`location_of_incident`,
                iai.`describe_incident`,
                iai.`ip_name`,
                iai.`created_date`,
                iai.`ip_name`,
                iai.`ip_address`,
                iai.`ip_occupation`,
                iai.`ip_dob`,
                iai.`ip_tel_num`,
                iai.`ip_employer`,
                iai.`ip_noi`,
                iai.`ip_loi`,
                iai.`ip_onsite_treatment`,
                iai.`ip_further_treatment`,
                iai.`witness_name`,
                iai.`witness_contact`,
                iai.`loss_time_injury`,
                iai.`reported_to`,
                iai.`confirm_chk`,
                iai.created_by,
                iai.`department`,
                iai.`were_the_police_notified`,
                iai.`reported_to_phone_number`,
                iai.`further_treatment_details`,
                iai.`injury_type_other_details`,

                sa2.FirstName,
                sa2.LastName
            ";
            $params = array(
                'sel_query' => $sel_query,
                'iai_id' => $data['iai_id']
            );
            $data['incident_details_info'] = $this->users_model->getIncidentAndReport($params)->row();


            //GET EXISTING PHOTO
            $data['photo'] = $this->db->select('incident_photos_id,incident_and_injury_id,image_name')->where(array('active=>1', 'incident_and_injury_id' => $data['iai_id']))->get('incident_photos');
        } else {
            redirect(base_url('/users/incident_and_injury_report_list'));
        }

        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/incident_and_injury_report_details', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Incident and Injury Update Script
     */
    public function incident_and_injury_report_update() {

        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->library('email');

        $iai_id = $this->input->post('iai_id');

        // The incident Post
        $date_of_incident = $this->input->post('date_of_incident');
        $date_of_incident2 = $this->system_model->formatDate($date_of_incident);
        $time_of_incident = $this->input->post('time_of_incident');
        $datetime_of_incident = "{$date_of_incident2} {$time_of_incident}:00";
        $nature_of_incident = $this->input->post('nature_of_incident');
        $loc_of_inci = $this->input->post('loc_of_inci');
        $desc_inci = $this->input->post('desc_inci');

        // Injured Person Details Post
        $ip_name = $this->input->post('ip_name');
        $ip_address = $this->input->post('ip_address');
        $ip_occu = $this->input->post('ip_occu');
        $ip_dob = $this->input->post('ip_dob');
        $ip_dob2 = $this->system_model->formatDate($ip_dob);
        $ip_tel_num = $this->input->post('ip_tel_num');
        $ip_employer = $this->input->post('ip_employer');
        $ip_noi = $this->input->post('ip_noi');
        $ip_loi = $this->input->post('ip_loi');
        $ip_onsite_treatment = $this->input->post('ip_onsite_treatment');
        $ip_fur_treat = $this->input->post('ip_fur_treat');

        // Witness Details Post
        $witness_name = $this->input->post('witness_name');
        $witness_contact = $this->input->post('witness_contact');

        // Outcome Post
        $loss_time_injury = $this->input->post('loss_time_injury');
        $reported_to = $this->input->post('reported_to');
        $confirm_chk = $this->input->post('confirm_chk');


        if ($iai_id && is_numeric($iai_id)) {


            //UPDATE INCIDENT
            $update_data = array(
                'datetime_of_incident' => $datetime_of_incident,
                'nature_of_incident' => $nature_of_incident,
                'location_of_incident' => $loc_of_inci,
                'describe_incident' => $desc_inci,
                'ip_name' => $ip_name,
                'ip_address' => $ip_address,
                'ip_occupation' => $ip_occu,
                'ip_dob' => $ip_dob2,
                'ip_tel_num' => $ip_tel_num,
                'ip_employer' => $ip_employer,
                'ip_noi' => $ip_noi,
                'ip_loi' => $ip_loi,
                'ip_onsite_treatment' => $ip_onsite_treatment,
                'ip_further_treatment' => $ip_fur_treat,
                'witness_name' => $witness_name,
                'witness_contact' => $witness_contact,
                'loss_time_injury' => $loss_time_injury,
                'reported_to' => $reported_to,
                'confirm_chk' => $confirm_chk
            );
            $update_incident = $this->users_model->update_incident_and_injury($iai_id, $update_data);
            //UPDATE INCIDENT END
            //UPLOAD PHOTO
            $country_iso = strtolower($this->gherxlib->get_country_iso());
            $new_file_name = "incident" . rand() . '_' . date('YmdHis');
            $upload_path = "./images/incident/{$country_iso}/";
            $upload_folter = "/images/incident/{$country_iso}/"; //note without dot

            $countfiles = count($_FILES['photo_of_incident']['name']);

            //make directory if not exist and set permission to 777
            if (!is_dir($upload_folter)) {
                mkdir($upload_path, 0777, true);
            }

            // Looping all files
            for ($i = 0; $i < $countfiles; $i++) {

                if (!empty($_FILES['photo_of_incident']['name'][$i])) {
                    // New Files value
                    $_FILES['file']['name'] = $_FILES['photo_of_incident']['name'][$i];
                    $_FILES['file']['type'] = $_FILES['photo_of_incident']['type'][$i];
                    $_FILES['file']['tmp_name'] = $_FILES['photo_of_incident']['tmp_name'][$i];
                    $_FILES['file']['error'] = $_FILES['photo_of_incident']['error'][$i];
                    $_FILES['file']['size'] = $_FILES['photo_of_incident']['size'][$i];

                    //set upload config
                    $upload_params = array(
                        'file_name' => $new_file_name,
                        'upload_path' => $upload_path,
                        'max_size' => '3000', //1mb
                        'allowed_types' => 'gif|jpg|png'
                    );
                    $upload = $this->gherxlib->do_upload('file', $upload_params);

                    if ($upload) {
                        //upload photo data
                        $uploadData = $this->upload->data();
                        $photo_data = array(
                            'incident_and_injury_id' => $iai_id,
                            'image_name' => $upload_folter . "" . $uploadData['file_name']
                        );
                        $this->users_model->upload_photo_data($photo_data);
                    } else {
                        if ($countfiles > 0) {
                            $error_msg = "There was a problem saving your attachment, please check and try again";
                            $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                            redirect(base_url('/users/incident_and_injury_report_details/' . $iai_id), 'refresh');
                        }
                    }
                }
            }
            //UPLOAD PHOTO END
            //SET SUCCESS FLASH SESSION
            $success_message = "Details updated successfully";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url('/users/incident_and_injury_report_list/'), 'refresh');
        }
    }

    public function ajax_delete_incident_photo() {

        $json_data['status'] = false;

        $iai_id = $this->input->post('iai_id');
        $incident_photos_id = $this->input->post('incident_photos_id');

        if (($iai_id && $incident_photos_id) && (is_numeric($iai_id) && is_numeric($incident_photos_id))) {

            //delete photos
            $this->users_model->delete_incident_photo($iai_id, $incident_photos_id);
            $json_data['status'] = true;
            $json_data['json_msg'] = "Photo deleted successfully";
        } else {
            $json_data['status'] = false;
            $json_data['json_msg'] = "Server Error: Please Contact Admin!";
        }

        echo json_encode($json_data);
    }

    public function leave_requests() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Leave Summary";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $country_id = $this->config->item('country');
        $employee = $this->input->get_post('employee_filter');
        $line_manager = $this->input->get_post('line_manager_filter');
        $status = ( $this->input->get_post('status_filter') != '' ) ? $this->input->get_post('status_filter') : 'Pending';

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
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'l.date',
                    'sort' => 'DESC',
                ),
            ),
        );
        $data['lists'] = $this->users_model->getLeave($params);

        // all rows
        $sel_query = "COUNT(l.`leave_id`) AS leave_count";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'emp_id' => $employee,
            'lm_id' => $line_manager,
            'l_status' => $status
        );
        $query = $this->users_model->getLeave($params);
        $total_rows = $query->row()->leave_count;

        //Employee Name Filter
        $emp_sel_query = "DISTINCT(sa.`StaffID`), sa.FirstName, sa.LastName";
        $emp_params = array(
            'sel_query' => $emp_sel_query,
            'country_id' => $country_id,
            'sa_active' => 1,
            'sa_deleted' => 0,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['employee'] = $this->users_model->get_users($emp_params);

        //Line Manager Filter
        $lm_sel_query = "DISTINCT(sa_lm.`StaffID`), sa_lm.FirstName, sa_lm.LastName";
        $lm_params = array(
            'sel_query' => $lm_sel_query,
            'country_id' => $country_id,
            'l_status' => $status,
            'sort_list' => array(
                array(
                    'order_by' => 'sa_lm.FirstName',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['line_manager'] = $this->users_model->getLeave($lm_params);


        $pagi_links_params_arr = array(
            'employee_filter' => $agency_filter,
            'line_manager_filter' => $jobType,
            'status_filter' => $search
        );
        $pagi_link_params = '/users/leave_requests/?' . http_build_query($pagi_links_params_arr);

        // pagination settings
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
        $this->load->view('reports/leave_requests', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Leave Details PDF
     */
    public function leave_details_pdf() {

        $leave_id = $this->uri->segment(3);

        if ($leave_id && is_numeric($leave_id)) {
            $this->getLeavePdf('I', $leave_id);
        } else {
            redirect(base_url('/users/leave_requests'));
        }
    }

    /**
     * Leave Details PDF LAYOUT
     */
    public function getLeavePdf($output, $leave_id) {
        $this->load->library('JPDF');

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

    public function getTypesofLeave($tol) {
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

    public function ajax_delete_leave() {
        $json_data['status'] = false;
        $leave_id = $this->input->post('leave_id');

        if ($leave_id && is_numeric($leave_id)) {

            $update_data = array(
                'deleted' => 1
            );
            $deleteLeave = $this->users_model->delete_leave($leave_id, $update_data);

            if ($deleteLeave) {
                $json_data['msg'] = "Leave deleted successfully";
                $json_data['status'] = true;
            }
        }

        echo json_encode($json_data);
    }

    public function user_manager() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Manage Agency Logins";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $country_id = $this->config->item('country');

        $search = $this->input->get_post('agency_filter');

        //list
        $custom_where = "a.agency_name LIKE '%{$search}%' ";
        $sel_query = "
        a.`phone` AS a_phone,
        a.agency_name,
        a.agency_id,
        a.phone as a_phone,
        a.initial_setup_done
        ";
        $params = array(
            'sel_query' => $sel_query,
            'a_status' => 'active',
            'custom_where' => $custom_where,
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC'
                )
            ),
            'limit' => $per_page,
            'offset' => $offset,
            'display_query' => 0
        );
        $data['lists'] = $this->agency_model->get_agency($params);


        //list count
        $params = array(
            'sel_query' => "COUNT(a.`agency_id`) AS a_count",
            'a_status' => 'active',
            'custom_where' => $custom_where,
        );
        $query = $this->agency_model->get_agency($params);
        $total_rows = $query->row()->a_count;

        $pagi_links_params_arr = array(
            'agency_filter' => $search
        );
        $pagi_link_params = '/users/user_manager/?' . http_build_query($pagi_links_params_arr);

        // pagination settings
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



        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('users/user_manager', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function leave_form(){

        $this->load->library('email');

        $country_id = $this->config->item('country');
        $today = date('Y-m-d');

        // FORM SUBMIT
        if($this->input->post('btn_add_leave')){
             // Form post
            $employee = $this->input->post('employee');
            $type_of_leave = $this->input->post('type_of_leave');
            $lday_of_work = $this->input->post('lday_of_work');
            $lday_of_work2 = $this->system_model->formatDate($lday_of_work);
            $fday_back = $this->input->post('fday_back');
            $fday_back2 = $this->system_model->formatDate($fday_back);
            $num_of_days = $this->input->post('num_of_days');
            $reason_for_leave = $this->input->post('reason_for_leave');
            $line_manager = $this->input->post('line_manager');
            $backup_leave = $this->input->post('backup_leave');

            $this->form_validation->set_rules('employee', 'Name', 'required');
            $this->form_validation->set_rules('type_of_leave', 'Type of leave', 'required');
            $this->form_validation->set_rules('lday_of_work', 'Last day of leave', 'required');
            $this->form_validation->set_rules('fday_back', 'First day of leave', 'required');
            $this->form_validation->set_rules('num_of_days', 'Number of days', 'required');
            $this->form_validation->set_rules('reason_for_leave', 'Reason for leave', 'required');
            $this->form_validation->set_rules('line_manager', 'Line manager', 'required');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'date' => $today,
                    'employee' => $employee,
                    'type_of_leave' => $type_of_leave,
                    'lday_of_work' => $lday_of_work2,
                    'fday_back' => $fday_back2,
                    'num_of_days'  => $num_of_days,
                    'reason_for_leave' => $reason_for_leave,
                    'line_manager' =>  $line_manager,
                    'status' => 'Pending',
                    'country_id' => $country_id,
                    'backup_leave' => $backup_leave
                );
                $last_id = $this->users_model->insert_leave($data);
            }else{
                echo "Error: Contact Admin";exit();
            }

            //PDF
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

            $success_message = "Leave request has been succesfully added";
            $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
            redirect(base_url('/users/leave_form/'), 'refresh');

        }
        // FORM SUBMIT END

        //staff dropdown
        $staffparams = array(
            'sel_query' => 'sa.StaffID, sa.FirstName, sa.LastName',
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                )
            )
        );
        $data['staff']  = $this->gherxlib->getStaffInfo($staffparams);

        $data['title'] = "Leave Request Form";
        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/leave_form', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function leave_details(){

        $id = $this->uri->segment(3);
        $country_id = $this->config->item('country');
        if($id && $id!=""){

            //staff dropdown
            $staffparams = array(
                'sel_query' => 'sa.StaffID, sa.FirstName, sa.LastName',
                'sort_list' => array(
                    array(
                        'order_by' => 'sa.FirstName',
                        'sort' => 'ASC'
                    )
                )
            );
            $data['staff']  = $this->gherxlib->getStaffInfo($staffparams);

            //get leave request by id
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
            l.type_of_leave,
            l.num_of_days,
            l.status,
            l.backup_leave,
            l.comments,

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
                'leave_id' => $id
            );
            $data['row'] = $this->users_model->getLeave($params)->row_array();

            $data['title'] = "Leave Details";
            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('reports/leave_details', $data);
            $this->load->view('templates/inner_footer', $data);

        }else{
            redirect('/users/leave_requests');
        }

    }

    public function update_leave_details(){

        $this->load->library('email');

        $staff_id = $this->session->staff_id;
        $leave_id = $this->uri->segment(3);
        $country_id = $this->config->item('country');
        $now = date("Y-m-d H:i:s");

        // POST
        $date = $this->input->post('date');
        $employee = $this->input->post('employee');
        $type_of_leave = $this->input->post('type_of_leave');
        $lday_of_work = $this->input->post('lday_of_work');
        $lday_of_work2 = $this->system_model->formatDate($lday_of_work);
        $fday_back = $this->input->post('fday_back');
        $fday_back2 = $this->system_model->formatDate($fday_back);
        $num_of_days = $this->input->post('num_of_days');
        $reason_for_leave = $this->input->post('reason_for_leave');
        $line_manager = $this->input->post('line_manager');
        $backup_leave = $this->input->post('backup_leave');

        $line_manager_app = (is_numeric($this->input->post('line_manager_app'))) ? $this->input->post('line_manager_app') : NULL;
        $hr_app = (is_numeric($this->input->post('hr_app'))) ? $this->input->post('hr_app') : NULL;
        $added_to_cal = (is_numeric($this->input->post('added_to_cal'))) ? $this->input->post('added_to_cal') : 'NULL';
        $added_to_cal_changed = $this->input->post('added_to_cal_changed');
        $staff_notified = (is_numeric($this->input->post('staff_notified')))?$this->input->post('staff_notified'):'NULL';
        $staff_notified_changed = $this->input->post('staff_notified_changed');
        $comments = $this->input->post('comments');

        $this->form_validation->set_rules('employee', 'Name', 'required');
        $this->form_validation->set_rules('type_of_leave', 'Type of leave', 'required');
        $this->form_validation->set_rules('lday_of_work', 'Last day of leave', 'required');
        $this->form_validation->set_rules('fday_back', 'First day of leave', 'required');
        $this->form_validation->set_rules('num_of_days', 'Number of days', 'required');
        $this->form_validation->set_rules('reason_for_leave', 'Reason for leave', 'required');
        $this->form_validation->set_rules('line_manager', 'Line manager', 'required');

        if ($this->form_validation->run() == true) {

            if( $line_manager_app=='1' && $hr_app=='1' ){
                $status = 'Approved';
            }else if( $line_manager_app==='0' || $hr_app==='0' ){
                $status = 'Denied';
            }else{
                $status = 'Pending';
            }

            //Fetch old data
            $jparams = array(
                'leave_id' => $leave_id,
                'country_id' => $country_id,
            );
            $leave_sql = $this->users_model->getLeave($jparams);
            $leave = $leave_sql->row_array();
            $old_line_manager_app = $leave['line_manager_app'];
            $old_hr_app = $leave['hr_app'];

            // Update Leave
            $post_data = array(
                'date' => $this->system_model->formatDate($date),
                'employee' => $employee,
                'type_of_leave' => $type_of_leave,
                'lday_of_work' => $lday_of_work2,
                'fday_back' => $fday_back2,
                'num_of_days' => $num_of_days,
                'reason_for_leave' => $reason_for_leave,
                'line_manager' => $line_manager,
                'line_manager_app' => $line_manager_app,
                'hr_app' => $hr_app,
                'comments' => $comments,
                'status' => $status,
                'backup_leave' => $backup_leave
            );

            if( is_numeric($line_manager_app) && $line_manager_app != $old_line_manager_app ){
                $post_data['line_manager_app_by'] = $staff_id;
                $post_data['line_manager_app_timestamp'] = $now;
            }

            if( is_numeric($hr_app) && $hr_app != $old_hr_app ){
                $post_data['hr_app_by'] = $staff_id;
                $post_data['hr_app_timestamp'] = $now;
            }

            if( $added_to_cal_changed!=$added_to_cal){
                $post_data['added_to_cal'] = $added_to_cal;
                $post_data['added_to_cal_by'] = $staff_id;
                $post_data['added_to_cal_timestamp'] = $now;
            }

            if( $staff_notified_changed!=$staff_notified ){
                $post_data['staff_notified'] = $staff_notified;
                $post_data['staff_notified_by'] = $staff_id;
                $post_data['staff_notified_timestamp'] = $now;
            }

            $this->users_model->edit_leave_details($post_data, $leave_id);
            // Update Leave End

            $success_message = "Leave request has been succesfully updated";
            $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
            redirect(base_url('/users/leave_details/'.$leave_id), 'refresh');

        }else{
            echo "Error: Contact Admin";exit();
        }

    }


    public function incident_and_injury_report_script_v2() {

        $this->load->library('form_validation');
        $this->load->library('email');

        //EMPLOYEE DETAILS
        $ip_name = $this->input->post('ip_name');
        $department = $this->input->post('department');
        $ip_tel_num = $this->input->post('ip_tel_num');

        //DESCRIPTION OF INCIDENT
        $loc_of_inci = $this->input->post('loc_of_inci');
        $date_of_incident = $this->input->post('date_of_incident');
        $date_of_incident2 = $this->system_model->formatDate($date_of_incident);
        $time_of_incident = $this->input->post('time_of_incident');
        $datetime_of_incident = "{$date_of_incident2} {$time_of_incident}:00";
        $desc_inci = $this->input->post('desc_inci');
        $police_notified = $this->input->post('police_notified');
        $witness_name = $this->input->post('witness_name');
        $witness_contact = $this->input->post('witness_contact');
        $reported_to = $this->input->post('reported_to');
        $reported_to_phone = $this->input->post('reported_to_phone');

        //INJURY TYPE
        $injuury_type = $this->input->post('injuury_type');
        if($injuury_type==8){
            $injury_type_other_details = $this->input->post('injury_other_details');
        }else{
            $injury_type_other_details = "";
        }

        $further_treatment = $this->input->post('ip_fur_treat');
        $further_treatment_details = $this->input->post('further_treatment_details');

        $confirm_chk = $this->input->post('confirm_chk');

        $created_by = $this->session->staff_id;

        //Insert to incident_and_injury
        $insert_data = array(
            'datetime_of_incident' => $datetime_of_incident,
            'nature_of_incident' => $injuury_type,
            'injury_type_other_details' => $injury_type_other_details,
            'location_of_incident' => $loc_of_inci,
            'describe_incident' => $desc_inci,
            'were_the_police_notified' => $police_notified,
            'ip_name' => $ip_name,
            'department' => $department,
            'ip_tel_num' => $ip_tel_num,
            'ip_further_treatment' => $further_treatment,
            'further_treatment_details' => $further_treatment_details,
            'witness_name' => $witness_name,
            'witness_contact' => $witness_contact,
            'reported_to' => $reported_to,
            'reported_to_phone_number' => $reported_to_phone,
            'confirm_chk' => $confirm_chk,
            'created_by' => $created_by,
            'country_id' => $this->config->item('country')
        );
        $insert_incident_and_injury = $this->users_model->insert_incident_and_injury($insert_data);
        $iai_id = $insert_incident_and_injury; // last id

        // EMAIL AND ATTACHED INCIDENT INFO/DATA (PDF)
        $getCountryInfo = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));
        $pdf_attahcment_name = 'incident_and_injury_report_' . date('dmYHis') . '.pdf';

        // Incident PDF TEMPLATE
        $getIncidentAndReportPdf_template = $this->getIncidentAndReportPdf("S", $iai_id);


        $email_from = $getCountryInfo->outgoing_email;
        $email_to = $this->config->item('sats_hr_email');
        //$email_to = "itsmegherx@gmail.com";
        $email_subject = "Incident Report";

        //email config
        $config = Array(
            'mailtype' => 'html',
            'charset' => 'iso-8859-1'
        );

        $this->email->initialize($config);
        $this->email->set_newline("\r\n");

        $this->email->from($email_from, $this->config->item('COMPANY_FULL_NAME'));
        $this->email->to($email_to);
        $this->email->subject($email_subject);
        $body = $this->load->view('emails/incident_report.php', $email_data, TRUE);
        $this->email->message($body);
        $this->email->attach($getIncidentAndReportPdf_template, 'attachment', $pdf_attahcment_name, 'application/pdf');
        $this->email->send();
        // END EMAIL
        //SET SUCCESS FLASH SESSION
        $success_message = "Submission Success";
        $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success', 'incident_id'=>$iai_id));
        redirect(base_url('/users/incident_and_injury_report'), 'refresh');
    }

    public function getIncidentAndReportPdf_v2($output, $iai_id) {
        $this->load->library('JPDF');
        // get incident query
        $sel_query = "
           iai.`incident_and_injury_id`,
           iai.`datetime_of_incident`,
           iai.`nature_of_incident`,
           iai.`location_of_incident`,
           iai.`describe_incident`,
           iai.`ip_name`,
           iai.`created_date`,

           iai.`ip_address`,
           iai.`ip_occupation`,
           iai.`ip_dob`,
           iai.`ip_tel_num`,
           iai.`ip_employer`,
           iai.`ip_noi`,
           iai.`ip_loi`,
           iai.`ip_onsite_treatment`,
           iai.`ip_further_treatment`,
           iai.`witness_name`,
           iai.`witness_contact`,
           iai.`loss_time_injury`,
           iai.`department`,
           iai.`were_the_police_notified`,
           iai.`reported_to_phone_number`,
           iai.`further_treatment_details`,
           iai.`injury_type_other_details`,
           sa.`FirstName`,
           sa.`LastName`
       ";
        $params = array(
            'sel_query' => $sel_query,
            'iai_id' => $iai_id
        );
        $iai_sql = $this->users_model->getIncidentAndReport($params);
        $iai = $iai_sql->row_array();

        // pdf initiation
        $pdf = new JPDF();
        $pdf->SetTitle('Incident Report');
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

        $cell_width = 70;
        $cell_width2 = 128;
        $cell_height = 6;
        $cell_border = 0;
        $col1_cell_new_line = 0;
        $col2_cell_new_line = 1;
        $col1_cell_align = 'L';
        $col2_cell_align = 'L';


        // THE INCIDENT
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'EMPLOYEE DETAILS', $header_border, $header_new_line, $header_align);
        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_name'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Department: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['department'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Phone number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_tel_num'], $cell_border, $col2_cell_new_line, $col2_cell_align);







        $pdf->Ln($header_space);


        // INJURED PERSON DETAILS
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'DESCRIPTION OF INCIDENT', $header_border, $header_new_line, $header_align);
        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Location of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['location_of_incident'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Date of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('d/m/Y', strtotime($iai['datetime_of_incident'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Time of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('H:i', strtotime($iai['datetime_of_incident'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Were the police notified?: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, ( ( is_numeric($iai['were_the_police_notified']) && $iai['were_the_police_notified'] == 1 ) ? 'Yes' : 'No'), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Incident Details in Full: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->MultiCell($cell_width2, $cell_height, $iai['describe_incident']);

        $pdf->Ln($header_space);

        $pdf->Cell($cell_width, $cell_height, 'Witness Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['witness_name'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Witness Phone Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['witness_contact'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        $pdf->Cell($cell_width, $cell_height, 'To Whom was this Incident Reported?: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['FirstName'].' '.$iai['LastName'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Incident Reported Phone Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['reported_to_phone_number'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        // WITNESS DETAILS
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'INJURY TYPE', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Injury type: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $this->users_model->getNatureOfIncident($iai['nature_of_incident']), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Injury type details: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['injury_type_other_details'], $cell_border, $col2_cell_new_line, $col2_cell_align);


        $pdf->Cell($cell_width, $cell_height, 'Do you require further treatment?: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, ( ( is_numeric($iai['ip_further_treatment']) && $iai['ip_further_treatment'] == 1 ) ? 'Yes' : 'No'), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Further treatment details: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['further_treatment_details'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        $pdf->Cell($cell_width, $cell_height, 'Report Submitted: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('d/m/Y H:i', strtotime($iai['created_date'])), $cell_border, $col2_cell_new_line, $col2_cell_align);


        $file_name = 'incident_and_injury_report_' . date('dmYHis') . '.pdf';

        return $pdf->Output($file_name, $output);
    }

    public function incident_and_injury_report_update_v2() {

        $this->load->library('form_validation');
        $this->load->library('email');

        $iai_id = $this->input->post('iai_id');

        $ip_name = $this->input->post('ip_name');
        $department = $this->input->post('department');
        $ip_tel_num = $this->input->post('ip_tel_num');

        $loc_of_inci = $this->input->post('loc_of_inci');
        $date_of_incident = $this->input->post('date_of_incident');
        $date_of_incident2 = $this->system_model->formatDate($date_of_incident);
        $time_of_incident = $this->input->post('time_of_incident');
        $datetime_of_incident = "{$date_of_incident2} {$time_of_incident}:00";
        $police_notified = $this->input->post('police_notified');
        $desc_inci = $this->input->post('desc_inci');
        $witness_name = $this->input->post('witness_name');
        $witness_contact = $this->input->post('witness_contact');

        $reported_to = $this->input->post('reported_to');
        $reported_to_phone = $this->input->post('reported_to_phone');

        $injuury_type = $this->input->post('injuury_type');
        if($injuury_type==8){
            $injury_other_details = $this->input->post('injury_other_details');
        }else{
            $injury_other_details = NULL;
        }

        $ip_fur_treat = $this->input->post('ip_fur_treat');
        $further_treatment_details = $this->input->post('further_treatment_details');

        $confirm_chk = $this->input->post('confirm_chk');

        if ($iai_id && is_numeric($iai_id)) {

            //UPDATE INCIDENT
            $update_data = array(
                'datetime_of_incident' => $datetime_of_incident,
                'nature_of_incident' => $injuury_type,
                'injury_type_other_details' => $injury_other_details,
                'location_of_incident' => $loc_of_inci,
                'describe_incident' => $desc_inci,
                'were_the_police_notified' => $police_notified,
                'ip_name' => $ip_name,
                'department' => $department,
                'ip_tel_num' => $ip_tel_num,
                'ip_further_treatment' => $ip_fur_treat,
                'further_treatment_details' => $further_treatment_details,
                'witness_name' => $witness_name,
                'witness_contact' => $witness_contact,
                'reported_to' => $reported_to,
                'reported_to_phone_number' => $reported_to_phone,
                'confirm_chk' => $confirm_chk,
                'country_id' => $this->config->item('country')
            );
            $update_incident = $this->users_model->update_incident_and_injury($iai_id, $update_data);
            //UPDATE INCIDENT END

            //SET SUCCESS FLASH SESSION
            $success_message = "Details updated successfully";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url('/users/incident_and_injury_report_list/'), 'refresh');
        }
    }


}

?>
