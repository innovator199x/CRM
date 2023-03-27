<?php

use App\Exceptions\HttpException;

class Calendar extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->load->model('tech_model');
        $this->load->model('tech_run_model');
        $this->load->model('calendar_model');
    }

    public function monthly_schedule($year, $month) {
        $staffId = $this->api->getJWTItem('staff_id');
        $staffParams = array(
            'sel_query' => "sa.FirstName, sa.LastName",
            'staff_id' => $staffId
        );
        $staff = $this->gherxlib->getStaffInfo($staffParams)->row_array();

        $serviceTypes = $this->db->query("
            SELECT `id`, `type`
            FROM `alarm_job_type`
            WHERE `active` = 1
        ")->result_array();

        $this->api->setSuccess(true);
        $this->api->putData('staff', $staff);
        $this->api->putData('service_types', $serviceTypes);
        $this->api->putData('fn_agencies', $this->system_model->get_fn_agencies());
        $this->api->putData('vision_agencies', $this->system_model->get_vision_agencies());
    }

    public function tech_run_list($date) {
        $techId = $this->api->getJWTItem('staff_id');

        $techRunResult = $this->db->select('*')->from('tech_run')->where(array('assigned_tech' => $techId, 'date' => $date))->get();
        $techRun = null;
        $techRunRows = [];

        if ($techRunResult->num_rows() > 0) {
            $techRun = $techRunResult->row_array();
        }

        $trSel = "
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
        $trParams = [
            'sel_query' => $trSel,
            'sort_list' => [
                [
                    'order_by' => 'trr.sort_order_num',
                    'sort' => 'ASC'
                ]
            ],
            'display_only_booked' => 1,
            'display_query' => 0
        ];

        if ($techRun != null) {
            $techRunRowsResult = $this->tech_model->getTechRunRows($techRun['tech_run_id'], $this->config->item('country'), $trParams);
            if ($techRunRowsResult->num_rows() > 0) {
                $techRunRows = $techRunRowsResult->result_array();
            }

            $this->addExtraTechRunRowsData($techRunRows, $techId, $techRun['date']);
        }

        $this->api->setSuccess(true);
        $this->api->putData('tech_run', $techRun);
        $this->api->putData('tech_run_rows', $techRunRows);
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

    public function my_calendar() {
        $params = [
            'sel_query' => "c.calendar_id, c.staff_id, c.region, c.date_start, c.date_finish, c.date_start_time, c.date_finish_time, c.booking_target, c.details, c.accomodation,c.marked_as_leave, s.FirstName, s.LastName, s.ClassID, acco.accomodation_id, acco.name as acco_name, acco.area as acco_area, acco.address as acco_address, acco.phone as acco_phone",
            'StaffID' => $this->api->getJWTItem("staff_id"),
            'sort_list' => [
                [
                    'order_by' => 'c.date_start',
                    'sort' => 'DESC'
                ],
            ],
        ];

        $cal_query = $this->calendar_model->get_tech_calendar($params);

        $data = [];
        if(!empty($cal_query)){
			foreach($cal_query->result() as $row){

                $colorClass =  ($row->marked_as_leave==1)?'eventRed':'eventBlue';
                $isHome = false;
                $status = null;

                if ($row->accomodation=='0') { // Required
                    $isHome = true;
                    $status = 'required';
                }
                else if ($row->accomodation == 2) { //Pending
                    $isHome = true;
                    $status = 'pending';
                }
                else if ($row->accomodation == 1) { // Booked
                    $isHome = true;
                    $status = 'booked';
                }

				$data[] = [
                    'id' => $row->calendar_id,
                    'staff_id' => $row->staff_id,
                    'start' => $row->date_start,
                    'end' => $row->date_finish,
                    'details' => $row->details,
					'title' => $row->region,
                    'colorClass' => $colorClass,
                    'address' => $row->acco_address,
                    'ClassID' => $row->ClassID,
                    'accomodation' => $row->accomodation,
                    'accomodation_name' => $row->acco_name,
                    'acco_phone' => $row->acco_phone,
                    'is_home' => $isHome,
                    'status' => $status,
                    'start_time' =>  $row->date_start_time,
                    'end_time' => $row->date_finish_time
                ];
			}
        }

        $this->api->setSuccess(true);

        $this->api->putData('calendar_events', $data);
    }

}