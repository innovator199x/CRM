<?php

class TechRun extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->load->model('tech_model');
        $this->load->model('tech_run_model');
    }

    public function add_kms() {
        $this->api->assertMethod('put');

        $this->form_validation->set_data($this->api->getPostData());

        $this->form_validation->set_rules([
            [
                'field' => 'kms',
                'rules' => 'required|integer',
            ],
            [
                'field' => 'vehicle_id',
                'rules' => 'required|callback__exists[vehicles.vehicles_id]',
                'errors' => [
                    'required' => 'The {field} field is required.',
                    '_exists' => '{field} must exist in {param}.',
                ],
            ],
        ]);

        $this->api->validateForm();

        $kms = $this->api->getPostData('kms');
        $vehicleId = $this->api->getPostData('vehicle_id');

        $addData = [
            'vehicles_id' => $vehicleId,
            'kms' => $kms,
            'kms_updated' => date("Y-m-d H:i:s"),
        ];
        $this->db->insert('kms', $addData);
        $this->db->limit(1);

        if($this->db->affected_rows()>0){
            $this->api->setStatusCode(201);
            $this->api->setSuccess(true);
            $this->api->setMessage('Kms updated.');
            $this->api->putData('kms', [
                'kms' => $addData['kms'],
                'vehicle_id' => $addData['vehicles_id'],
                'last_updated' => (new DateTime($addData['kms_updated']))->format('Y-m-d'),
            ]);
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Kms could not be saved.');
        }

    }

    public function run_sheet($techRunId = null) {
        if (is_null($techRunId)) {
            $trResult = $this->tech_model->getTechRunIdForStaff($this->api->getJWTItem('staff_id'));
            $techRunId = $trResult['tech_run_id'];
        }

        $countryId = $this->config->item('country');

        //get techrun by techrun id
        $techRunResult = $this->db->select('*')->from('tech_run')->where('tech_run_id', $techRunId)->get();

        if ($techRunResult->num_rows() > 0) {

            $techRun = $techRunResult->row_array();

            // $techId = $techRun['assigned_tech'];
            // $date = new DateTimeImmutable($techRun['date'], new DateTimeZone(date_default_timezone_get()));

            $subRegions = $techRun['sub_regions'];

            $techId = $techRun['assigned_tech'];

            $accommodationStart = $this->db->select('*')->from('accomodation')->where([
                'accomodation_id'=> $techRun['start'],
                'country_id'=> $countryId,
            ])->get()->row_array();

            $accommodationEnd = $this->db->select('*')->from('accomodation')->where([
                'accomodation_id'=> $techRun['end'],
                'country_id'=> $countryId,
            ])->get()->row_array();

            $vehicle = $this->tech_model->getVehicleByTechId($techId)->row_array();

            $kms = $this->tech_model->getKmsByVehicleId($vehicle['vehicles_id'])->row_array();

            $staff_params = array(
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
                'staff_id' => $techId
            );
            $staff = $this->gherxlib->getStaffInfo($staff_params)->row_array();

            $techStock = $this->db->select('*')->from('tech_stock')->where('staff_id', $staff['StaffID'])->order_by('date','DESC')->limit(1)->get()->row_array();

            $tr_sel = "
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,

                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,

                j.`id` AS jid,
                j.`precomp_jobs_moved_to_booked`,
                j.`completed_timestamp`,

                p.`property_id`,

                a.`agency_id`,
                a.`allow_upfront_billing`
            ";
            $tr_params = [
                'sel_query' => $tr_sel,
                'sort_list' => [
                    [
                        'order_by' => 'trr.sort_order_num',
                        'sort' => 'ASC',
                    ],
                ],
                'display_only_booked' => 1,
                'display_query' => 0
            ];
            $techRunRowsResult = $this->tech_model->getTechRunRows($techRunId, $countryId, $tr_params);
            $techRunRows = [];
            if ($techRunRowsResult->num_rows() > 0) {
                $techRunRows = $techRunRowsResult->result_array();
            }

            $this->addExtraTechRunRowsData($techRunRows, $techId, $techRun['date']);

            $serviceTypes = $this->db->query("
                SELECT `id`, `type`
                FROM `alarm_job_type`
                WHERE `active` = 1
            ")->result_array();

            $today = date("Y-m-d");
            $tbSql = $this->db->query("
            SELECT COUNT(`tech_break_id`) AS tb_count
            FROM `tech_breaks`
            WHERE CAST( `tech_break_start` AS Date ) = '{$today}'
            AND `tech_break_taken` = 1
            AND `tech_id` = {$techId}
            ");
            $tbCount = $tbSql->row()->tb_count;

            $this->api->setSuccess(true);
            $this->api->putData('tech_run', $techRun);
            $this->api->putData('tech_run_rows', $techRunRows);
            $this->api->putData('accommodation', [
                'start' => $accommodationStart,
                'end' => $accommodationEnd,
            ]);
            $this->api->putData('kms', $kms);
            $this->api->putData('staff', $staff);
            $this->api->putData('service_types', $serviceTypes);
            $this->api->putData('fn_agencies', $this->system_model->get_fn_agencies());
            $this->api->putData('vision_agencies', $this->system_model->get_vision_agencies());
            $this->api->putData('tb_count', $tbCount);

            return;
        }

        $this->api->setSuccess(false);
        $this->api->setMessage("Tech run does not exist.");
    }

    private function addExtraTechRunRowsData(&$techRunRows, $techId, $date) {

        $countryId = $this->config->item('country');

        $techRunRowsAssoc = [];
        $techRunRowsAssocByJob = [];
        $techRunRowsAssocByKey = [];
        $techRunRowsAssocBySupplier = [];
        for ($x = 0; $x < count($techRunRows); $x++) {
            $techRunRow = &$techRunRows[$x];
            $techRunRow['job'] = null;
            $techRunRow['key'] = null;
            $techRunRow['supplier'] = null;
            $techRunRowsAssoc[$techRunRow['tech_run_rows_id']] = &$techRunRow;

            if ($techRunRows[$x]['row_id_type'] == 'job_id') {
                $techRunRowsAssocByJob[$techRunRow['row_id']] = &$techRunRow;
            }
            else if ($techRunRows[$x]['row_id_type'] == 'keys_id') {
                $techRunRowsAssocByKey[$techRunRow['row_id']] = &$techRunRow;
            }
            else if ($techRunRows[$x]['row_id_type'] == 'supplier_id') {
                $techRunRowsAssocBySupplier[$techRunRow['row_id']] = &$techRunRow;
            }
        }
        $jobIds = array_keys($techRunRowsAssocByJob);

        if (!empty($jobIds)) {
            $jobs = $this->tech_model->getJobRowDataWithJobIds($jobIds, $countryId);

            $propertyIds = [];
            $agencyIds = [];
            $jobsAssoc = [];
            for ($x = 0; $x < count($jobs); $x++) {
                $jobs[$x]['first_visit'] = true;
                $jobs[$x]['log'] = null;
                $jobs[$x]['alarm_make'] = null;
                $jobs[$x]['jnc_count'] = 0;
                $jobs[$x]['new_tenant'] = null;
                $propertyIds[] = $jobs[$x]['property_id'];
                $agencyIds[] = $jobs[$x]['agency_id'];
                $jobsAssoc[$jobs[$x]['jid']] = &$jobs[$x];
            }

            $newTenants = $this->db->select('property_id,tenant_firstname,tenant_mobile')->from('property_tenants')->where([
                'active' => 1,
                'property_tenant_id >' => 0
            ])
            ->where_in('property_id', $propertyIds)
            ->get()->result_array();

            $propertiesVisits = $this->tech_model->checkPropertyFirstVisitsByIds($propertyIds);
            if ($countryId == 2) {
                $agencyAlarms = $this->system_model->displayOrcaOrCaviAlarmsByAgencyIds($agencyIds);
            }
            $jobExpiredAlarms = $this->system_model->findExpiredAlarmByJobIds($jobIds);

            for ($x = 0; $x < count($jobs); $x++) {
                $jobs[$x]['has_expired_alarms'] = $jobExpiredAlarms[$jobs[$x]['jid']] ?? false;
                foreach ($propertiesVisits as $property) {
                    if ($jobs[$x]['property_id'] == $property['property_id']) {
                        $jobs[$x]['first_visit'] = $property['j_count'] == 0;
                        break;
                    }
                }

                foreach ($newTenants as $newTenant) {
                    if (
                        $jobs[$x]['property_id'] == $newTenant['property_id'] &&
                        $jobs[$x]['booked_with'] == $newTenant['tenant_firstname']
                    ) {
                        $jobs[$x]['new_tenant'] = $newTenant;
                    }
                }

                if ($countryId == 2) {
                    foreach ($agencyAlarms as $alarmKey => $alarmMake) {
                        if ($jobs[$x]['agency_id'] == $alarmKey) {
                            $jobs[$x]['alarm_make'] = $alarmmake;
                            break;
                        }
                    }
                }
            }

            $jobsNotCompleted = $this->tech_run_model->getJobsNotCompleted($jobIds);
            foreach($jobsNotCompleted as $notCompletedJob) {
                $jobsAssoc[$notCompletedJob->job_id]['jnc_count'] = $notCompletedJob->jnc_count;
            }

            $job_log_params = array(
                'sel_query' => "job_id, eventdate, eventtime",
                'job_ids' => $jobIds,
                'eventdate' => date('Y-m-d'),
                'contact_type' => 'Phone Call'
            );

            $logsResult = $this->tech_model->getJobLogByJobIds($job_log_params);

            foreach ($logsResult as $log) {
                $jobsAssoc[$log['job_id']]['log'] = $log;
            }

            foreach ($jobs as $job) {
                $techRunRowsAssocByJob[$job['jid']]['job'] = $job;
            }
        }

        $keyIds = array_keys($techRunRowsAssocByKey);

        if (!empty($keyIds)) {
            $keys = $this->tech_model->getTechRunKeysByIds($keyIds);

            $agencyIds = [];
            $keysAssoc = [];

            for ($x = 0; $x < count($keys); $x++) {
                $keys[$x]['first_visit'] = true;
                $keys[$x]['log'] = null;
                $keys[$x]['booked_keys'] = 0;
                $agencyIds[] = $keys[$x]['agency_id'];
                $keysAssoc[$keys[$x]['jid']] = &$keys[$x];
            }

            $numOfBookedKeys = $this->tech_model->getNumberOfBookedKeysByAgencyIds($techId, $date, $countryId, $agencyIds);

            for ($x = 0; $x < count($keys); $x++) {
                foreach ($numOfBookedKeys as $bookedKey) {
                    if ($keys[$x]['agency_id'] == $bookedKey['agency_id']) {
                        $keys[$x]['booked_keys'] = $bookedKey['j_count'];
                        break;
                    }
                }
            }

            foreach ($keys as $key) {
                $techRunRowsAssocByKey[$key['tech_run_keys_id']]['key'] = $key;
            }
        }

        $supplierIds = array_keys($techRunRowsAssocBySupplier);

        if (!empty($supplierIds)) {
            $suppliers = $this->tech_model->getTechRunSuppliersByIds($supplierIds);

            foreach ($suppliers as $supplier) {
                $techRunRowsAssocBySupplier[$supplier['tech_run_suppliers_id']]['supplier'] = $supplier;
            }
        }

    }

    public function job_key_list() {
        $tech_id = $this->api->getPostData('tech_id');
        $date = $this->api->getPostData('date');
        $agency_id = $this->api->getPostData('agency_id');

        $key_action = $this->api->getPostData('key_action');

        $params = [
            'tech_id' => $tech_id,
            'date' => $date,
            'agency_id' => $agency_id,
            'key_action' => $key_action
        ];

        $jobReasons = $this->db->query("
            SELECT `job_reason_id`, `name`
            FROM `job_reason`
            ORDER BY `name` ASC
        ")->result_array();

        $jobsForTechRunKeyList = $this->tech_run_model->getTechRunKeyList($params);

        $this->api->setSuccess(true);

        $this->api->putData('jobs', $jobsForTechRunKeyList['jobs']);
        $this->api->putData('other_jobs', $jobsForTechRunKeyList['other_jobs']);
        $this->api->putData('job_reasons', $jobReasons);
    }

    public function save_agency_key_pickup() {
        $this->api->assertMethod('post');

        $this->load->model('properties_model');

        $trkId = $this->api->getPostData('trk_id');
        $techId = $this->api->getPostData('tech_id');
        $agencyId = $this->api->getPostData('agency_id');
        $date = $this->api->getPostData('date');
        $agencyStaff = $this->api->getPostData('agency_staff');
        $numberOfKeys = $this->api->getPostData('number_of_keys');

        $jobResponses = $this->api->getPostData('job_responses');

        $now = date('Y-m-d H:i:s');
        $countryId = $this->config->item('country');

        if ($trkId > 0) {
            $this->db->trans_start();

            $this->db->query("
                UPDATE `tech_run_keys`
                SET
                    `completed` = 1,
                    `completed_date` = '{$now}',
                    `agency_staff`	= '{$agencyStaff}',
                    `number_of_keys` = {$numberOfKeys}
                WHERE `tech_run_keys_id` = {$trkId}
            ");

            if ($techId > 0 && $date != null && $agencyId > 0) {
                $this->db->query("
                DELETE
                FROM `agency_keys`
                WHERE `tech_id` = {$techId}
                AND `date` = '{$date}'
                AND `agency_id` = {$agencyId}
                ");
            }

            $jobIds = array_keys($jobResponses);
            $jobIdsString = implode(',', $jobIds);

            $jobsAndProperties = $this->db->query("
                SELECT
                    j.`id` as jid,
                    j.`assigned_tech`,
                    p.`property_id`
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                WHERE `id` IN ({$jobIdsString})
            ")->result_array();

            $jobsPropertiesAndAssignedTech = [];
            foreach ($jobsAndProperties as $jobAndProperty) {
                $jobsPropertiesAndAssignedTech[$jobAndProperty['jid']] = $jobAndProperty;
            }

            foreach ($jobResponses as $jobId => $response) {
                $isPickedUp = $response['isPickedUpIndex'] == 1 ? 0 : 1;
                $attendProperty = $response['attendProperty'];
                $jobReason = $response['jobReasonId'];
                $reasonComment = $response['reasonComment'];
                $keyNumber = $response['keyNumber'];

                if ($jobId > 0) {
                    $jobPropertyAndAssignedTech = $jobsPropertiesAndAssignedTech[$jobId];

                    $assignedTech = $jobPropertyAndAssignedTech['assigned_tech'];
                    $propertyId = $jobPropertyAndAssignedTech['property_id'];

                    if ($propertyId > 0) {
                        $this->db->query("
                            UPDATE `property`
                            SET
                                `key_number` = '{$keyNumber}'
                            WHERE `property_id` = {$propertyId}
                        ");
                    }

                    if ($isPickedUp == 0 && is_numeric($isPickedUp)) {
                        if ($attendProperty == 0 && is_numeric($attendProperty) && $jobReason > 0) {
                            $params = [
                                'job_id' => $jobId,
                                'tech_id' => $assignedTech,
                                'job_reason' => $jobReason,
                                'reason_comment' => $reasonComment,
                            ];

                            $this->tech_run_model->mark_job_not_completed($params);
                        }
                        else {
                            $logTitle = 64;
                            $logDetails = "Tech <b>will</b> attend the property";

                            $params = [
                                'title' => $logTitle,
                                'details' => $logDetails,
                                'display_in_vjd' => 1,
                                'created_by_staff' => $this->api->getJWTItem('staff_id'),
                                'job_id' => $jobId,
                            ];

                            $this->system_model->insert_log($params);
                        }
                    }

                    $this->db->query("
                        INSERT INTO
                        `agency_keys`(
                            `tech_id`,
                            `date`,
                            `agency_id`,
                            `job_id`,
                            `is_keys_picked_up`,
                            `attend_property`,
                            `job_reason`,
                            `reason_comment`,
                            `created_date`
                        )
                        VALUES(
                            {$techId},
                            '{$date}',
                            {$agencyId},
                            {$jobId},
                            ". ( ( is_numeric($isPickedUp) ) ? $isPickedUp : 'NULL' ) .",
                            ". ( ( is_numeric($attendProperty) ) ? $attendProperty : 'NULL' ) .",
                            ". ( ( $jobReason > 0 ) ? $jobReason : 'NULL' ) .",
                            '{$reasonComment}',
                            '{$now}'
                        )
                    ");
                }
            }

            $this->db->trans_complete();

            $this->api->setSuccess($this->db->trans_status());
            return;
        }

        $this->api->setSuccess(false);
        $this->api->setMessage('No tech run keys id is set');
    }

    public function save_agency_key_drop_off() {
        $this->api->assertMethod('post');

        $this->load->model('properties_model');

        $trkId = $this->api->getPostData('trk_id');

        $agencyStaff = $this->api->getPostData('agency_staff');
        $numberOfKeys = $this->api->getPostData('number_of_keys');

        $signature = $this->api->getPostData('signature', false);
        $signatureUpdateStr = '';
        if ($signature != '') {
            $signatureUpdateStr = ", `signature_svg` = '{$signature}'";
        }

        $jobResponses = $this->api->getPostData('job_responses');

        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();

        if ($trkId > 0) {
            $this->db->query("
                UPDATE `tech_run_keys`
                SET
                    `completed` = 1,
                    `completed_date` = '{$now}',
                    `agency_staff` = '{$agencyStaff}',
                    `number_of_keys` = {$numberOfKeys}{$signatureUpdateStr}
                WHERE `tech_run_keys_id` = {$trkId}
            ");
        }

        foreach ($jobResponses as $jobId => $response) {
            $agencyKeysId = $response['agencyKeysId'];
            $keyNumber = $response['keyNumber'];
            $isKeysReturned = $response['isKeysReturnedIndex'] == 1 ? 0 : 1;
            $notReturnedNotes = $response['notReturnedNotes'];

            if ($agencyKeysId > 0) {

                $this->db->update("agency_keys", [
                    "is_keys_returned" => $isKeysReturned,
                    "not_returned_notes" => $notReturnedNotes != '' ? $notReturnedNotes : null,
                    "drop_off_ts" => $now,
                ], "agency_keys_id = {$agencyKeysId}", "1");
            }

        }

        $this->db->trans_complete();

        $this->api->setSuccess($this->db->trans_status());
    }

    public function available_dk($techRunId = null) {
        if (is_null($techRunId)) {
            $trResult = $this->tech_model->getTechRunIdForStaff($this->api->getJWTItem('staff_id'));
            $techRunId = $trResult['tech_run_id'];
        }

        $countryId = $this->config->item('country');

        if ($techRunId > 0) {
            $techRun = $this->db->select('`tech_run_id`,`assigned_tech`,`date`,`start`,`end`')->from('tech_run')->where('tech_run_id', $techRunId)->get()->row_array();
            $techId = $techRun['assigned_tech'];

            $accommodationStart = $this->db->select('*')->from('accomodation')->where([
                'accomodation_id'=> $techRun['start'],
                'country_id'=> $countryId,
            ])->get()->row_array();

            $accommodationEnd = $this->db->select('*')->from('accomodation')->where([
                'accomodation_id'=> $techRun['end'],
                'country_id'=> $countryId,
            ])->get()->row_array();

            $staff = $this->gherxlib->getStaffInfo([
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
                'staff_id' => $techId
            ])->row_array();

            $countryParams = [
                'sel_query' => 'c.`country`',
                'country_id' => $countryId
            ];
            $countryName = $this->system_model->get_countries($countryParams)->row()->country;

            $accommodations = $this->db->query("
                SELECT `accomodation_id`, `name`
                FROM `accomodation`
                WHERE `country_id` = {$countryId}
                ORDER BY `name`
            ")->row_array();

            $techRunRowColors = $this->db->query("
                SELECT `tech_run_row_color_id`,`color`
                FROM  `tech_run_row_color`
                WHERE `active` = 1
            ")->result_array();

            $trParams = [
                'sel_query' => "DISTINCT (a.`agency_id`), a.`agency_name`",
                'job_rows_only' => 1
            ];
            $agencyKeysResult = $this->tech_model->getTechRunRows($techRunId, $countryId, $trParams);
            $agencyKeys = [];
            if ($agencyKeysResult->num_rows() > 0) {
                $agencyKeys = $agencyKeysResult->result_array();
            }

            $jobReasons = $this->db->query("
                SELECT `job_reason_id`, `name`
                FROM `job_reason`
                ORDER BY `name` ASC
            ")->result_array();

            $serviceTypes = $this->db->query("
                SELECT `id`, `type`
                FROM `alarm_job_type`
                WHERE `active` = 1
            ")->result_array();

            $techRunRowsResult = $this->tech_model->getTechRunRows($techRunId, $countryId, [
                'sel_query' => "
                    trr.`tech_run_rows_id`,
                    trr.`row_id_type`,
                    trr.`row_id`,
                    trr.`hidden`,
                    trr.`dnd_sorted`,
                    trr.`highlight_color`,

                    trr_hc.`tech_run_row_color_id`,
                    trr_hc.`hex`,

                    j.`id` AS jid,
                    j.`precomp_jobs_moved_to_booked`,
                    j.`completed_timestamp`,

                    p.`property_id`,

                    a.`agency_id`,
                    a.`allow_upfront_billing`
                ",
                'sort_list' => [
                    [
                        'order_by' => 'p.`address_3`',
                        'sort' => 'ASC'
                    ]
                ],
                'display_query' => 0,
                'dk_query_listing' => 1
            ]);
            $techRunRows = [];
            if ($techRunRowsResult->num_rows() > 0) {
                $techRunRows = $techRunRowsResult->result_array();
            }
            $this->addExtraTechRunRowsData($techRunRows, $techId, $techRun['date']);

            $this->api->setSuccess(true);
            $this->api->putData('tech_run', $techRun);
            $this->api->putData('tech_run_rows', $techRunRows);
            $this->api->putData('accommodation', [
                'start' => $accommodationStart,
                'end' => $accommodationEnd,
            ]);
            $this->api->putData('agency_keys', $agencyKeys);
            $this->api->putData('fn_agencies', $this->system_model->get_fn_agencies());
            $this->api->putData('vision_agencies', $this->system_model->get_vision_agencies());
            $this->api->putData('country_name', $countryName);
            $this->api->putData('accommodations', $accommodations);
            $this->api->putData('tech_run_row_colors', $techRunRowColors);
            $this->api->putData('job_reasons', $jobReasons);
            $this->api->putData('service_types', $serviceTypes);

            return;
        }

        $this->api->setSuccess(false);
        $this->api->setMessage("Tech run does not exist.");
    }

    public function dk_complete() {
        $this->api->assertMethod('post');

        $jobId = $this->api->getPostData('job_id');
        $techId = $this->api->getPostData('tech_id');
        $date = $this->api->getPostData('date');

        if ($jobId && $techId) {
            $job = $this->db->query("
                SELECT
                    j.`assigned_tech`,
                    j.`status`,

                    p.`property_id`,
                    p.`address_1` AS p_address_1,
                    p.`address_2` AS p_address_2,
                    p.`address_3` AS p_address_3,
                    p.`state` AS p_state,
                    p.`postcode` AS p_postcode
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                WHERE `id` = {$jobId}
            ")->row_array();

            $jobStatus = $job['status'];

            $this->db->trans_start();

            $this->db->query("
                UPDATE `jobs`
                SET
                    `status` = 'Booked',
                    `door_knock` = 1,
                    `booked_with`	= 'Agent',
                    `assigned_tech` = {$techId},
                    `booked_by` = {$techId},
                    `date` = '{$date}'
                WHERE `id` = {$jobId}
            ");

            $logTitle = 32;
            $logDetails = "This job was updated from <b>{$jobStatus}</b> to <b>Booked</b> during tech door knocking";
            $logParams = [
                'title' => $logTitle,
                'details' => $logDetails,
                'display_in_vjd' => 1,
                'created_by_staff' => $this->api->getJWTItem('staff_id'),
                'job_id' => $jobId
            ];
            $this->system_model->insert_log($logParams);

            $this->db->trans_complete();

            $this->api->setSuccess($this->db->trans_status());

            return;
        }

        $this->api->setStatusCode(422);
        $this->api->setSuccess(false);
        $this->api->setMessage("Job ID or tech ID is not set.");
    }

    public function dk_utc(){
        $this->api->assertMethod('post');

        $jobId = $this->api->getPostData('job_id');
        $techId = $this->api->getPostData('tech_id');
        $jobReason = $this->api->getPostData('job_reason');
        $reasonComment = $this->api->getPostData('reason_comment');

        // update property key
        if( $jobId > 0 && $techId > 0 && $jobReason > 0 ){

            $this->db->trans_start();

            $mjnc_params = array(
                'job_id' => $jobId,
                'tech_id' => $techId,
                'job_reason' => $jobReason,
                'reason_comment' => $reasonComment
            );
            $this->tech_run_model->mark_job_not_completed($mjncParams);

            $this->db->trans_complete();

            $this->api->setSuccess($this->db->trans_status());

            return;
        }

        $this->api->setStatusCode(422);
        $this->api->setSuccess(false);
        $this->api->setMessage("Job ID or tech ID is not set.");
    }

    public function run_sheet_map($techRunId = null) {
        if (is_null($techRunId)) {
            $trResult = $this->tech_model->getTechRunIdForStaff($this->api->getJWTItem('staff_id'));
            $techRunId = $trResult['tech_run_id'];
        }

        $countryId = $this->config->item('country');

        if ($techRunId != null) {
            $techRun = $this->db->select('`tech_run_id`,`assigned_tech`,`date`,`start`,`end`')->from('tech_run')->where('tech_run_id', $techRunId)->get()->row_array();
            $techId = $techRun['assigned_tech'];

            $accommodationStart = $this->db->select('name`,`address`,`lat`,`lng`')->from('accomodation')->where([
                'accomodation_id'=> $techRun['start'],
                'country_id'=> $countryId,
            ])->get()->row_array();

            $accommodationEnd = $this->db->select('name`,`address`,`lat`,`lng`')->from('accomodation')->where([
                'accomodation_id'=> $techRun['end'],
                'country_id'=> $countryId,
            ])->get()->row_array();


            $staff_params = [
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
                'staff_id' => $techId
            ];
            $staff = $this->gherxlib->getStaffInfo($staff_params)->row_array();

            // country_name and accommodiations ignored

            $techRunRowColors = $this->db->query("
                SELECT `tech_run_row_color_id`,`color`
                FROM  `tech_run_row_color`
                WHERE `active` = 1
            ")->result_array();

            $techRunRowsResult = $this->tech_model->getTechRunRows($techRunId, $countryId, [
                'sel_query' => "
                    trr.`tech_run_rows_id`,
                    trr.`row_id_type`,
                    trr.`row_id`,
                    trr.`hidden`,
                    trr.`dnd_sorted`,
                    trr.`highlight_color`,

                    trr_hc.`tech_run_row_color_id`,
                    trr_hc.`hex`,

                    j.`id` AS jid,
                    j.`precomp_jobs_moved_to_booked`,
                    j.`completed_timestamp`,

                    p.`property_id`,

                    a.`agency_id`,
                    a.`allow_upfront_billing`
                ",
                'sort_list' => [
                    [
                        'order_by' => 'trr.`sort_order_num`',
                        'sort' => 'ASC'
                    ]
                ],
                'display_only_booked' => 1,
                'display_query' => 0,
            ]);
            $techRunRows = [];
            if ($techRunRowsResult->num_rows() > 0) {
                $techRunRows = $techRunRowsResult->result_array();
            }
            $this->addExtraTechRunRowsData($techRunRows, $techId, $techRun['date']);

            $this->api->setSuccess(true);
            $this->api->putData('tech_run', $techRun);
            $this->api->putData('tech_run_rows', $techRunRows);
            $this->api->putData('accommodation', [
                'start' => $accommodationStart,
                'end' => $accommodationEnd,
            ]);
            $this->api->putData('agency_keys', $agencyKeys);
            $this->api->putData('staff', $staff);
            $this->api->putData('fn_agencies', $this->system_model->get_fn_agencies());
            $this->api->putData('vision_agencies', $this->system_model->get_vision_agencies());
            $this->api->putData('tech_run_row_colors', $techRunRowColors);
        }

    }

    public function take_lunch_break(){
        $this->api->assertMethod('post');
        $techId = $this->api->getJWTItem('staff_id');
        $now = date('Y-m-d H:i:s');

        $this->db->insert('tech_breaks', [
            'tech_id' => $techId,
            'tech_break_start' => $now,
            'tech_break_taken' => 1,
        ]);

        $this->api->setSuccess(true);
    }

    public function can_take_break() {
        $techId = $this->api->getJWTItem('staff_id');
        $today = date("Y-m-d");
        $lunchBreakTime = strtotime("11:30:00");

        if (time() >= $lunchBreakTime) {
            $techBreaks = $this->db->query("
                SELECT COUNT(`tech_break_id`) AS tb_count
                FROM `tech_breaks`
                WHERE CAST( `tech_break_start` AS Date ) = '{$today}'
                AND `tech_break_taken` = 1
                AND `tech_id` = {$techId}
            ")->row()->tb_count;

            $this->api->setSuccess($techBreaks == 0);
        }
        else {
            $this->api->setSuccess(false);
        }
    }

    public function tech_sheet($jobId, $techRunId = 0) {
        $this->load->model('jobs_model');
        $this->load->model('tech_model');
        $this->load->model('figure_model');

        // get fields needed for tech sheet
        $job = $this->jobs_model->get_jobs([
            'sel_query' => "
                j.`id` AS jid,
                j.`status` AS j_status,
                j.`service` AS j_service,
                j.`created` AS j_created,
                j.`date` AS j_date,
                j.`comments` AS j_comments,
                j.`job_price`,
                j.`job_type`,
                j.`assigned_tech`,
                j.`invoice_amount`,
                j.`work_order`,
                j.`completed_timestamp`,
                j.`ts_signoffdate`,
                j.`swms_heights`,
                j.`swms_uv_protection`,
                j.`swms_asbestos`,
                j.`swms_powertools`,
                j.`swms_animals`,
                j.`swms_live_circuit`,
                j.`swms_covid_19`,
                j.`tech_comments`,
                j.`repair_notes,
                j.`job_reason_id`,
                j.`job_reason_comment`,
                j.`survey_numlevels`,
                j.`survey_ladder`,
                j.`survey_ceiling`,
                j.`ps_number_of_bedrooms`,
                j.`ss_location`,
                j.`ss_quantity`,
                j.`ts_safety_switch`,
                j.`ts_safety_switch_reason`,
                j.`survey_numalarms`,
                j.`ts_batteriesinstalled`,
                j.`ts_items_tested`,
                j.`ss_items_tested`,
                j.`cw_items_tested`,
                j.`we_items_tested`,
                j.`ts_alarmsinstalled`,
                j.`survey_alarmspositioned`,
                j.`survey_minstandard`,
                j.`entry_gained_via`,
                j.`property_leaks`,
                j.`leak_notes`,
                j.`ss_image`,
                j.`ts_techconfirm`,
                j.`prop_comp_with_state_leg`,
                j.`booked_with`,
                j.`job_entry_notice`,
                j.`key_access_required`,
                j.`en_date_issued`,
                j.`key_access_details`,
                j.`entry_gained_other_text`,
                j.`door_knock`,
                j.`time_of_day`,

                p.`property_id`,
                p.`address_1` AS p_street_num,
                p.`address_2` AS p_street_name,
                p.`address_3` AS p_suburb,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`comments` AS p_comments,
                p.`created` AS p_created,
                p.`key_number`,
                p.`alarm_code`,
                p.`prop_upgraded_to_ic_sa`,
                p.`qld_new_leg_alarm_num`,
                p.`preferred_alarm_id`,
                p.`holiday_rental`,
                p.`service_garage`,

                nsw_pc.`short_term_rental_compliant`,
                nsw_pc.`req_num_alarms`,
                nsw_pc.`req_heat_alarm`,

                al_p.`alarm_make` AS pref_alarm_make,

                a.`agency_id`,
                a.`agency_name` AS agency_name,
                a.`phone` AS a_phone,
                a.`address_1` AS a_street_num,
                a.`address_2` AS a_street_name,
                a.`address_3` AS a_suburb,
                a.`state` AS a_state,
                a.`postcode` AS a_postcode,
                a.`trust_account_software`,
                a.`tas_connected`,
                a.`agency_specific_notes`,

                ajt.`id` AS service_type_id,
                ajt.`type` AS service_type,
                ajt.`bundle` AS is_bundle_serv,

                t.`StaffID` AS tech_id,
                t.`FirstName` AS tech_fname,
                t.`LastName` AS tech_lname
            ",
            'job_id' => $jobId,
            'country_id' => $this->config->item("country"),
            'join_table' => array('job_type','alarm_job_type','tech','preferred_alarm'),
            'custom_joins' => array(
                'join_table' => 'nsw_property_compliance as nsw_pc',
                'join_on' => 'p.property_id = nsw_pc.property_id',
                'join_type' => 'left'
            ),
            'display_query' => 0
        ])->row_array();

        $icServices = $this->figure_model->getICService(); // ic service ids
        $isICService = in_array($job["j_service"], $icServices);

        $notCompletedReasons = $this->db->select("
                job_reason_id,
                name
            ")
            ->from("job_reason")
            ->order_by("name", "ASC")
            ->get()->result_array();

        if ($job["is_bundle_serv"] == 1) { // job can have multiple services
            $services = $this->db->select("
                    bundle_services_id, alarm_job_type_id
                ")
                ->from("bundle_services AS bs")
                ->join("alarm_job_type AS ajt", "ajt.id = bs.alarm_job_type_id", "left")
                ->where("job_id", $jobId)
                ->get()->result_array();

            $alarmJobTypeIds = array_map(function($ajt) {
                return $ajt["alarm_job_type_id"];
            }, $services);

            $hasSmokeAlarm = in_array(2, $alarmJobTypeIds);
            $hasSafetySwitch = in_array(5, $alarmJobTypeIds);
            $hasCordedWindow = in_array(6, $alarmJobTypeIds);
            $hasWaterEffeciency = in_array(15, $alarmJobTypeIds);

            array_walk($services, function($ajt) use ($jobId) {
                $this->jobs_model->runSync([
                    "job_id" => $jobId,
                    "jserv" => $ajt["alarm_job_type_id"],
                    "bundle_serv_id" => $ajt["bundle_services_id"],
                ]);
            });
        }
        else { // single service job
            $hasSmokeAlarm = $job["j_service"] == 2;
            $hasSafetySwitch = $job["j_service"] == 5;
            $hasCordedWindow = $job["j_service"] == 6;
            $hasWaterEffeciency = $job["j_service"] == 15;

            $alarmJobTypeIds = [$job["j_service"]];

            $this->jobs_model->runSync([
                "job_id" => $jobId,
                "jserv" => $job["j_service"],
            ]);
        }

        // active tenants
        $propertyTenants = $this->properties_model->get_property_tenants([
            'sel_query' => "
                pt.`property_tenant_id`,
                pt.`tenant_firstname`,
                pt.`tenant_lastname`,
                pt.`tenant_email`,
                pt.`tenant_mobile`,
                pt.`tenant_landline`
            ",
            'property_id' => $job["property_id"],
            'pt_active' => 1,
            'display_query' => 0,
        ])->result_array();

        // check if first visit and
        $firstVisit = $this->tech_model->check_prop_first_visit($job["property_id"]);

        $existingAlarms = $this->db->select("
                al.`alarm_id`,
                al.`alarm_power_id`,
                al.`alarm_type_id`,
                al.`alarm_reason_id`,
                al.`expiry`,
                al.`make`,
                al.`model`,
                al.`new`,
                al.`ts_added`,
                al.`ts_alarm_sounds_other`,
                al.`ts_cleaned`,
                al.`ts_db_rating`,
                al.`ts_discarded`,
                al.`ts_discarded_reason`,
                al.`ts_expiry`,
                al.`ts_fixing`,
                al.`ts_meetsas1851`,
                al.`ts_newbattery`,
                al.`ts_position`,
                al.`rec_batt_exp`,
                al.`ts_required_compliance`,
                al.`ts_testbutton`,
                al.`ts_visualind`,

                al_pwr.`alarm_pwr_id`,
                al_pwr.`alarm_pwr`,

                al_type.`alarm_type_id`,
                al_type.`alarm_type`
            ")
            ->from("alarm AS al")
            ->join("alarm_pwr AS al_pwr", "al.alarm_power_id = al_pwr.alarm_pwr_id", "left")
            ->join("alarm_type AS al_type", "al.alarm_type_id = al_type.alarm_type_id", "left")
            ->where("al.job_id", $jobId)
            ->where("al.new !=", 1)
            ->order_by("al.alarm_id", "ASC")
            ->get()->result_array();

        $newAlarms = $this->db->select("
                al.`alarm_id`,
                al.`alarm_power_id`,
                al.`alarm_type_id`,
                al.`alarm_reason_id`,
                al.`expiry`,
                al.`make`,
                al.`model`,
                al.`new`,
                al.`ts_added`,
                al.`ts_alarm_sounds_other`,
                al.`ts_cleaned`,
                al.`ts_db_rating`,
                al.`ts_discarded`,
                al.`ts_discarded_reason`,
                al.`ts_expiry`,
                al.`ts_fixing`,
                al.`ts_meetsas1851`,
                al.`ts_newbattery`,
                al.`ts_position`,
                al.`rec_batt_exp`,
                al.`ts_required_compliance`,
                al.`ts_testbutton`,
                al.`ts_visualind`,

                al_pwr.`alarm_pwr_id`,
                al_pwr.`alarm_pwr`,

                al_type.`alarm_type_id`,
                al_type.`alarm_type`
            ")
            ->from("alarm AS al")
            ->join("alarm_pwr AS al_pwr", "al.alarm_power_id = al_pwr.alarm_pwr_id", "left")
            ->join("alarm_type AS al_type", "al.alarm_type_id = al_type.alarm_type_id", "left")
            ->where("al.job_id", $jobId)
            ->where("al.new", 1)
            ->order_by("al.alarm_id", "ASC")
            ->get()->result_array();

        $expiredAlarms = $this->db->select("
                al.`alarm_id`,
                al.`alarm_power_id`,
                al.`alarm_type_id`,
                al.`alarm_reason_id`,
                al.`expiry`,
                al.`make`,
                al.`model`,
                al.`new`,
                al.`ts_added`,
                al.`ts_alarm_sounds_other`,
                al.`ts_cleaned`,
                al.`ts_db_rating`,
                al.`ts_discarded`,
                al.`ts_discarded_reason`,
                al.`ts_expiry`,
                al.`ts_fixing`,
                al.`ts_meetsas1851`,
                al.`ts_newbattery`,
                al.`ts_position`,
                al.`rec_batt_exp`,
                al.`ts_required_compliance`,
                al.`ts_testbutton`,
                al.`ts_visualind`,

                al_pwr.`alarm_pwr_id`,
                al_pwr.`alarm_pwr`,

                al_type.`alarm_type_id`,
                al_type.`alarm_type`
            ")
            ->from("alarm AS al")
            ->join("alarm_pwr AS al_pwr", "al.alarm_power_id = al_pwr.alarm_pwr_id", "left")
            ->join("alarm_type AS al_type", "al.alarm_type_id = al_type.alarm_type_id", "left")
            ->where("al.alarm_power_id !=", 6)
            ->where("al.job_id", $jobId)
            ->where("al.expiry <=", date("Y"))
            ->order_by("al.alarm_id", "ASC")
            ->get()->result_array();

        $alarmPowerForExistingAlarms = $this->db->select("
                ap.alarm_pwr_id,
                ap.alarm_pwr,
                ap.is_li,
                ap.is_240v
            ")
            ->from("alarm_pwr AS ap")
            ->where("ap.alarm_pwr_id !=", 6)
            ->order_by("ap.alarm_pwr", "ASC")
            ->get()->result_array();

        $alarmPowerForNewAlarms = $this->db->select("
                ap.alarm_pwr_id,
                ap.alarm_pwr,
                ap.is_li,
                ap.is_240v
            ")
            ->from("agency_alarms AS aa")
            ->join("alarm_pwr AS ap", "aa.alarm_pwr_id = ap.alarm_pwr_id", "left")
            ->where("aa.agency_id", $job["agency_id"])
            ->order_by("ap.alarm_pwr", "ASC")
            ->get()->result_array();

        $alarmTypes = $this->db->select("alarm_type_id, alarm_type")
            ->from("alarm_type")
            ->where("alarm_job_type_id", 2)
            ->order_by("alarm_type", "DESC")
            ->get()->result_array();

        $alarmReasons = $this->db->select("alarm_reason_id, alarm_reason")
            ->from("alarm_reason")
            ->where("alarm_job_type_id", 2)
            ->order_by("alarm_reason", "ASC")
            ->get()->result_array();

        $alarmDiscardedReasons = $this->db->select("id, reason")
            ->from("alarm_discarded_reason")
            ->where("active", 1)
            ->order_by("reason", "ASC")
            ->get()->result_array();

        $safetySwitches = $this->db->select("
                safety_switch_id,
                make,
                model,
                test
            ")
            ->from("safety_switch")
            ->where("job_id", $jobId)
            ->order_by("make", "ASC")
            ->get()->result_array();

        $cordedWindows = $this->db->select("
                corded_window_id,
                location,
                num_of_windows
            ")
            ->from("corded_window")
            ->where("job_id", $jobId)
            ->order_by("location", "ASC")
            ->get()->result_array();

        $waterEfficiencyDetails = $this->db->select("
                water_efficiency_id,
                device,
                pass,
                location,
                note
            ")
            ->from("water_efficiency")
            ->where("job_id", $jobId)
            ->where("active", 1)
            ->get()->result_array();

        $waterEfficiencyDevices = $this->db->select("
                water_efficiency_device_id,
                name
            ")
            ->from("water_efficiency_device")
            ->where("active", 1)
            ->get()->result_array();

        if ($job["ss_image"] != null && $job["ss_image"] != '') {
            // dynamic switch of ss image
            if ( file_exists("{$_SERVER['DOCUMENT_ROOT']}/uploads/switchboard_image/{$job["ss_image"]}") ) {
                // tecsheet CI
                $ss_image_upload_folder = "{$this->config->item("crmci_link")}/uploads/switchboard_image/";
            }else{ // old techsheet
                $ss_image_upload_folder = "{$this->config->item("crm_link")}/images/ss_image/";
            }
            $job["ss_image"] = "{$ss_image_upload_folder}{$job["ss_image"]}";
        }

        $this->api->setSuccess(true);
        $data = [
            "job" => $job,
            "is_ic_service" => $isICService,
            "not_completed_reasons" => $notCompletedReasons,
            "has_smoke_alarm" => $hasSmokeAlarm,
            "has_safety_switch" => $hasSafetySwitch,
            "has_corded_window" => $hasCordedWindow,
            "has_water_efficiency" => $hasWaterEffeciency,
            "property_tenants" => $propertyTenants,
            "first_visit" => $firstVisit,
            "existing_alarms" => $existingAlarms,
            "new_alarms" => $newAlarms,
            "expired_alarms" => $expiredAlarms,
            "alarm_power_for_existing_alarms" => $alarmPowerForExistingAlarms,
            "alarm_power_for_new_alarms" => $alarmPowerForNewAlarms,
            "alarm_types" => $alarmTypes,
            "alarm_reasons" => $alarmReasons,
            "alarm_discarded_reasons" => $alarmDiscardedReasons,
            "safety_switches" => $safetySwitches,
            "corded_windows" => $cordedWindows,
            "water_efficiency_details" => $waterEfficiencyDetails,
            "water_efficiency_devies" => $waterEfficiencyDevices,
            "alarm_job_type_ids" => $alarmJobTypeIds,
        ];

        foreach ($data as $key => $value) {
            $this->api->putData($key, $value);
        }
    }

    // updates data of a specified row and specified table
    public function tech_sheet_field_update() {
        $this->api->assertMethod('post');
        $postData = $this->api->getPostData();

        $table = $postData["_table"]; // table of the row to update
        $rowId = $postData["_id"]; // row id of row to update
        $idField = $postData["_id_field"]; // id field of the table

        //remove these keys since they don't need to be updated and will cause error
        unset($postData["_table"]);
        unset($postData["_id"]);
        unset($postData["_id_field"]);

        $updateResult = $this->db
            ->set($postData)
            ->where($idField, $rowId)
            ->update($table);
        $this->api->setSuccess(boolval($updateResult));
    }

    public function mark_job_not_completed() {

        //load model
        $this->load->model('/inc/email_functions_model');

        $jobId = $this->api->getPostData('job_id');
        $reasonId = $this->api->getPostData('reason_id');
        $comment = $this->api->getPostData('comment');
        $staffId = $this->api->getJWTItem("staff_id");
        $today = date("Y-m-d H:i:s");

        // get job data
        $job = $this->db->select("door_knock, assigned_tech")
            ->from("jobs")
            ->where("id", $jobId)
            ->limit(1)->get()->row();

        // get job reason
        $jobReason = $this->db->select("name")
        ->from("job_reason")
        ->where("job_reason_id", $jobId)
        ->limit(1)->get()->row();



        // Insert log
        $commentLog = "";
        if( $comment != '' ){
            $commentLog = ", Comment: {$comment}";
        }
        $logDetails = "Due to <b>{$jobReason->name}{$commentLog}</b>";

        $this->db->trans_start();

        // update job
        $this->db->set([
            "job_reason_id" => $reasonId,
            "job_reason_comment" => $comment,
            "completed_timestamp" => $today,
        ])
        ->where("id", $jobId)
        ->update("jobs");

        $log_params = array(
            'title' => 74, // Job Not Completed
            'details' => $logDetails,
            'display_in_vjd' => 1,
            'created_by_staff' => $staffId,
            'job_id' => $jobId
        );
        $this->system_model->insert_log($log_params);

        //insert to jobs_not_completed table
        $this->db->set([
            "job_id" => $jobId,
            "reason_id" => $reasonId,
            "reason_comment" => $comment,
            "tech_id" => $job->assigned_tech,
            "date_created" => $today,
            "door_knock" => $job->door_knock,
        ])
        ->insert("jobs_not_completed");

        $this->db->trans_complete();

        $this->api->setSuccess($this->db->trans_status());
    }

    public function submit_tech_sheet(){
        $postData = $this->api->getPostData();
        $job_id = $postData["job_id"];
        $ts_techconfirm = $postData["ts_techconfirm"];
        $prop_comp_with_state_leg = $postData["prop_comp_with_state_leg"];
        $prop_upgraded_to_ic_sa = $postData["prop_upgraded_to_ic_sa"];

        $this->form_validation->set_data($postData);

        // validation rules
        $this->form_validation->set_rules([
            [
                'field' => 'job_id',
                'rules' => 'required|integer',
            ],
            [
                'field' => 'ts_techconfirm',
                'rules' => 'required',
            ],
            [
                'field' => 'prop_comp_with_state_leg',
                'rules' => 'required',
            ],
            [
                'field' => 'prop_upgraded_to_ic_sa',
                'rules' => 'required',
            ],
        ]);

        $staff_id = $this->api->getJWTItem("staff_id");
        $today = date("Y-m-d H:i:s");

        $success = false;

        if( $this->form_validation->run() ){

            $jobUpdateData = [];
            $bundleUpdateData = [];

            // get job data to handle saving properly
            $job = $this->db->select("
                    j.id AS jid,
                    j.`status` AS jstatus,
                    j.`service` AS jservice,
                    j.`property_id`,

                    p.`state` AS p_state,

                    ajt.`bundle`
                ")
                ->from("jobs AS j")
                ->join("property AS p", "j.property_id = p.property_id", "left")
                ->join("alarm_job_type AS ajt", "j.service = ajt.id", "left")
                ->where("j.id", $job_id)
                ->get()->row();

            if( $job->bundle == 1 ){

                $bundles = $this->db->select("
                        bundle_services_id,
                        alarm_job_type_id
                    ")
                    ->from("bundle_services AS bs")
                    ->join("alarm_job_type AS ajt", "ajt.id = bs.alarm_job_type_id", "LEFT")
                    ->where("job_id", $job_id)
                    ->get()->result();

                // mark which services are confirmed
                foreach( $bundles as $bundle ){

                    $bundleUpdateData[] = [
                        "completed" => 1,
                        "job_id" => $job_id,
                        "bundle_services_id" => $bundle->bundle_services_id,
                    ];

                    $ts_confirm_marker = null;
                    if( $bundle->alarm_job_type_id == 2 ){ // smoke alarm
                        $jobUpdateData['ts_techconfirm'] = 1;
                    }else if( $bundle->alarm_job_type_id == 5 ){ // safety switch
                        $jobUpdateData['ss_techconfirm'] = 1;
                    }else if( $bundle->alarm_job_type_id == 6 ){ // corded window
                        $jobUpdateData['cw_techconfirm'] = 1;
                    }else if( $bundle->alarm_job_type_id == 15 ){ // water efficiency
                        $jobUpdateData['we_techconfirm'] = 1;
                    }

                }

            }
            else {

                // mark the service confirmed
                $ts_confirm_marker = null;
                if( $job->jservice == 2 ){ // smoke alarm
                    $jobUpdateData['ts_techconfirm'] = 1;
                }else if( $job->jservice == 5 ){ // safety switch
                    $jobUpdateData['ss_techconfirm'] = 1;
                }else if( $job->jservice == 6 ){ // corded window
                    $jobUpdateData['cw_techconfirm'] = 1;
                }else if( $job->jservice == 15 ){ // water efficiency
                    $jobUpdateData['we_techconfirm'] = 1;
                }

            }

            $jobUpdateData["status"] = "Pre Completion";
            $jobUpdateData["ts_completed"] = "1";
            $jobUpdateData["completed_timestamp"] = $today;
            $jobUpdateData["precomp_jobs_moved_to_booked"] = NULL;
            $jobUpdateData["prop_comp_with_state_leg"] = $prop_comp_with_state_leg;
            $jobUpdateData["job_reason_id"] = NULL;
            $jobUpdateData["job_reason_comment"] = NULL;

            $this->db->trans_start();

            $this->db->set($jobUpdateData)
                ->where("id", $job_id)
                ->update("jobs");

            if( $job->property_id > 0 ){

                if( $job->p_state == 'QLD' ){

                    $this->db->set([
                            "prop_upgraded_to_ic_sa" => $prop_upgraded_to_ic_sa,
                        ])
                        ->where("property_id", $job->property_id)
                        ->update("property");

                }

            }

            if (!empty($bundleUpdateData)) {
                foreach ($bundleUpdateData as $bud) {
                    $this->db->set([
                            "completed" => $bud["completed"],
                        ])
                        ->where("job_id", $bud["job_id"])
                        ->where("bundle_services_id", $bud["bundle_services_id"])
                        ->update("bundle_services");
                }
            }

            // insert log
            $log_details = "<b>Techsheet Completed</b>, job changed from <b>{$job->jstatus}</b> to <b>Pre Completion</b>";
            $log_params = array(
                'title' => 75, // Techsheet Completed
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);

            $this->db->trans_complete();

            $success = $this->db->trans_status();

        }
        else {
            $errors = $this->form_validation->error_array();
            $errors = implode(", ", $errors);
            $this->api->setMessage($errors);
        }

        $this->api->setSuccess($success);

    }

}
?>