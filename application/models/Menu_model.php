<?php

class Menu_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function getMenu($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`menu');

        // filter
        if (is_numeric($params['active'])) {
            $this->db->where('active', $params['active']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // custom filter
        if (isset($params['custom_sort'])) {
            $this->db->order_by($params['custom_sort']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function getPages($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`crm_pages AS cp');
        $this->db->join('menu AS m', 'cp.menu = m.menu_id', 'left');

        // filter
        if (is_numeric($params['active'])) {
            $this->db->where('cp.active', $params['active']);
        }

        if (is_numeric($params['menu'])) {
            $this->db->where('cp.menu', $params['menu']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // custom filter
        if (isset($params['custom_sort'])) {
            $this->db->order_by($params['custom_sort']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    // menu permission class
    public function get_menu_permission_class($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`menu_permission_class AS mpc');
        if (is_numeric($params['menu_id'])) {
            $this->db->where('mpc.menu', $params['menu_id']);
        }
        if (is_numeric($params['staff_class'])) {
            $this->db->where('mpc.staff_class', $params['staff_class']);
        }
        if (is_numeric($params['active'])) {
            $this->db->where('mpc.active', $params['active']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    // menu permission user
    public function get_menu_permission_user($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('menu_permission_user AS mpu');
        $this->db->join('staff_accounts AS sa', 'mpu.user = sa.StaffID', 'left');

        if (is_numeric($params['menu'])) {
            $this->db->where('mpu.menu', $params['menu']);
        }
        if (is_numeric($params['denied'])) {
            $this->db->where('mpu.denied', $params['denied']);
        }
        if (is_numeric($params['active'])) {
            $this->db->where('mpu.active', $params['active']);
        }
        if (is_numeric($params['user'])) {
            $this->db->where('mpu.user', $params['user']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    // page permission class
    public function get_page_permission_class($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`crm_page_permission_class AS cppc');
        if (is_numeric($params['page'])) {
            $this->db->where('cppc.page', $params['page']);
        }
        if (is_numeric($params['staff_class'])) {
            $this->db->where('cppc.staff_class', $params['staff_class']);
        }
        if (is_numeric($params['active'])) {
            $this->db->where('cppc.active', $params['active']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    // page permission user
    public function get_page_permission_user($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('crm_page_permission_user AS cppu');
        $this->db->join('staff_accounts AS sa', 'cppu.user = sa.StaffID', 'left');

        if (is_numeric($params['page'])) {
            $this->db->where('cppu.page', $params['page']);
        }
        if (is_numeric($params['denied'])) {
            $this->db->where('cppu.denied', $params['denied']);
        }
        if (is_numeric($params['active'])) {
            $this->db->where('cppu.active', $params['active']);
        }
        if (is_numeric($params['user'])) {
            $this->db->where('cppu.user', $params['user']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function canViewMenuByStaffClass($params) {

        $sel_query = 'COUNT(mpc.mpc_id) AS jcount';
        $perm_params = array(
            'sel_query' => $sel_query,
            'menu_id' => $params['menu_id'],
            'staff_class' => $params['staff_class'],
            'active' => 1,
            'display_query' => 0
        );
        $perm_sql = $this->get_menu_permission_class($perm_params);
        $perm_count = $perm_sql->row()->jcount;
        return ( $perm_count > 0 ) ? true : false;
    }

    public function canViewMenuByStaffAccounts($params) {

        $sel_query = "COUNT(mpu.mpu_id) AS jcount";
        $perm_params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'menu' => $params['menu_id'],
            'user' => $params['user'],
            'denied' => $params['denied'],
            'display_query' => 0
        );
        $perm_sql = $this->menu_model->get_menu_permission_user($perm_params);
        $perm_count = $perm_sql->row()->jcount;
        return ( $perm_count > 0 ) ? true : false;
    }

    public function canViewPageByStaffClass($params) {

        $sel_query = 'COUNT(cppc.cppc_id) AS jcount';
        $perm_params = array(
            'sel_query' => $sel_query,
            'page' => $params['page_id'],
            'staff_class' => $params['staff_class'],
            'active' => 1,
            'display_query' => 0
        );
        $perm_sql = $this->get_page_permission_class($perm_params);
        $perm_count = $perm_sql->row()->jcount;
        return ( $perm_count > 0 ) ? true : false;
    }

    public function canViewPageByStaffAccounts($params) {
        $sel_query = "COUNT(cppu.cppu_id) AS jcount";
        $perm_params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'page' => $params['page_id'],
            'user' => $params['user'],
            'denied' => $params['denied'],
            'display_query' => 0
        );
        $perm_sql = $this->menu_model->get_page_permission_user($perm_params);
        $perm_count = $perm_sql->row()->jcount;
        return ( $perm_count > 0 ) ? true : false;
    }

    public function canViewPage($params) {

        // allowed
        $allowed_params = $params;
        $allowed_params['denied'] = 0;

        // denied
        $denied_params = $params;
        $denied_params['denied'] = 1;

        if (
                (
                $this->canViewPageByStaffClass($params) == true ||
                $this->canViewPageByStaffAccounts($allowed_params) == true
                ) &&
                $this->canViewPageByStaffAccounts($denied_params) == false
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function canViewMenu($params) {

        // allowed
        $allowed_params = $params;
        $allowed_params['denied'] = 0;

        // denied
        $denied_params = $params;
        $denied_params['denied'] = 1;

        if (
                (
                $this->canViewMenuByStaffClass($params) == true ||
                $this->canViewMenuByStaffAccounts($allowed_params) == true
                ) &&
                $this->canViewMenuByStaffAccounts($denied_params) == false
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function getDynamicLinks($params) {

        // old crm links
        $page_url = $params['page_url'];
        $page = "{$this->config->item('crm_link')}/link_login.php?staff_id={$this->session->staff_id}&page={$page_url}";

        // PROPERTY
        if ($params['page_url'] == 'add_property_static.php') {

            $page = '/properties/add';
        }

        // SCHEDULING
        else if ($params['page_url'] == 'booking_schedule.php') {
            $page = '/bookings/view_schedule';
        }

        else if ($params['page_url'] == 'platform_invoicing.php') {
            $page = '/jobs/platform_invoicing';
        }

        // JOBS
        else if ($params['page_url'] == 'outside_tech_hours.php') {

            $page = '/jobs/after_hours';
        } else if ($params['page_url'] == 'allocate.php') {

            $page = '/jobs/allocate';
        } else if ($params['page_url'] == 'cancelled_jobs.php') {

            $page = '/jobs/cancelled';
        } else if ($params['page_url'] == 'completed_jobs.php') {

            $page = '/jobs/completed';
        } else if ($params['page_url'] == 'cot_jobs.php') {

            $page = '/jobs/cot';
        } else if ($params['page_url'] == 'dha_jobs.php') {

            $page = '/jobs/dha';
        } else if ($params['page_url'] == 'escalate.php') {

            $page = '/jobs/escalate';
        } else if ($params['page_url'] == 'holiday_rentals.php') {

            $page = '/jobs/holiday_rentals';
        } else if ($params['page_url'] == 'service_due_jobs.php') {

            $page = '/jobs/service_due';
        } else if ($params['page_url'] == 'to_be_booked_jobs.php') {

            $page = '/jobs/to_be_booked';
        } else if ($params['page_url'] == 'to_be_invoiced_jobs.php') {

            $page = '/jobs/to_be_invoiced';
        } else if ($params['page_url'] == 'vacant_jobs.php') {

            $page = '/jobs/vacant';
        } else if ($params['page_url'] == 'on_hold_jobs.php') {

            $page = '/jobs/on_hold';
        } else if ($params['page_url'] == 'merged_jobs.php') {

            $page = '/jobs/merged_jobs';
        }else if ($params['page_url'] == 'precompleted_jobs.php') {

            $page = '/jobs/pre_completion';
        }else if ($params['page_url'] == 'send_letter_jobs.php') {
            $page = '/jobs/new_jobs';
        }

        // CS DAILY ITEMS
        else if ($params['page_url'] == 'daily/active_unsold_services') {

            $page = '/daily/active_unsold_services';
        } else if ($params['page_url'] == 'daily/no_job_status') {

            $page = '/daily/no_job_status';
        } else if ($params['page_url'] == 'daily/no_job_types') {

            $page = '/daily/no_job_types';
        } else if ($params['page_url'] == 'reports/dirty_address/') {

            $page = '/reports/dirty_address/';
        } else if ($params['page_url'] == 'duplicate_postcode.php') {

            $page = '/properties/duplicate_postcode';
        } else if ($params['page_url'] == 'property_me/properties_needs_verification') {

            $page = '/property_me/properties_needs_verification';
        } else if ($params['page_url'] == 'no_active_job_properties.php') {

            $page = '/daily/view_no_active_job_properties';
        }else if ($params['page_url'] == 'multiple_jobs.php') {

            $page = '/daily/multiple_jobs';
        }else if ($params['page_url'] == 'unserviced.php') {

            $page = '/daily/unserviced';
        }else if ($params['page_url'] == 'action_required_jobs.php') {

            $page = '/daily/action_required_jobs';
        }else if ($params['page_url'] == 'bne_to_call.php') {
            $page = '/jobs/bne_to_call';
        }else if ($params['page_url'] == 'send_letter_jobs.php') {
            $page = '/jobs/new_jobs';
        }else if ($params['page_url'] == 'properties/inactive_properties_on_api') {
            $page = '/properties/inactive_properties_on_api';
        }else if ($params['page_url'] == 'daily/active_properties_without_jobs') {
            $page = '/daily/active_properties_without_jobs';
        }else if ($params['page_url'] == 'daily/overdue_jobs') {
            $page = '/daily/overdue_jobs';
        }else if ($params['page_url'] == 'reports/properties_with_coordinates_errors') {
            $page = '/reports/properties_with_coordinates_errors';
        }else if ($params['page_url'] == 'properties/properties_with_multiple_services') {
            $page = '/properties/properties_with_multiple_services';
        }else if ($params['page_url'] == 'daily/missed_jobs') {
            $page = '/daily/missed_jobs';
        }

        // VIEW TOOLS
        else if ($params['page_url'] == 'add_tools.php') {

            $page = '/vehicles/add_tools';
        } else if ($params['page_url'] == 'add_vehicle.php') {

            $page = '/vehicles/add_vehicle';
        }

        // SALES
        else if ($params['page_url'] == 'sales_snapshot.php') {

            $page = '/reports/sales_snapshot';
        } else if ($params['page_url'] == 'view_target_agencies.php') {
            $page = '/agency/view_target_agencies';
        } else if ($params['page_url'] == 'add_prospects.php') {
            $page = '/agency2/view_add_prospects';
        } else if ($params['page_url'] == 'sales_documents.php') {
            $page = '/sales/view_sales_documents';
        }else if ($params['page_url'] == 'agency_audits.php') {
            $page = '/agency/agency_audits';
        }else if ($params['page_url'] == 'view_all_agencies.php' || $params['page_url'] == 'agency/view_all_agencies') {
            $page = '/agency/view_all_agencies';
        }

        // ACCOUNTS
        else if ($params['page_url'] == 'nlm_properties.php') {
            $page = '/properties/nlm_properties';
        } else if ($params['page_url'] == 'statements.php') {
            $page = '/accounts/view_statements';
        } else if ($params['page_url'] == 'jobs/invoiced_jobs') {
            $page = '/jobs/invoiced_jobs';
        } else if ($params['page_url'] == 'accounts_logs.php') {
            $page = '/accounts/view_account_logs';
        } else if ($params['page_url'] == 'remittance.php') {
            $page = '/accounts/receipting';
        } else if ($params['page_url'] == 'send_statements.php') {
            $page = '/accounts/send_statements';
        }else if ($params['page_url'] == 'accounts/agency_payments') {
            $page = '/accounts/agency_payments';
        }else if ($params['page_url'] == 'credit/credit_request_summary') {
            $page = '/credit/credit_request_summary';
        }else if ($params['page_url'] == 'credit/refund_request_summary') {
            $page = '/credit/refund_request_summary';
        }else if ($params['page_url'] == 'credit/credit_request') {
            $page = '/credit/credit_request';
        }else if ($params['page_url'] == 'credit/refund_request') {
            $page = '/credit/refund_request';
        }

        // ADMIN
        else if ($params['page_url'] == 'menu_manager.php') {
            $page = '/menu/manager';
        } else if ($params['page_url'] == 'accomodation.php') {
            $page = '/admin/accommodation';
        } else if ($params['page_url'] == 'crm_tasks.php') {
            $page = '/reports/view_crm_tasks/';
        } else if ($params['page_url'] == 'alarm_pricing_page.php') {
            $page = '/admin/alarm_pricing_page/';
        } else if ($params['page_url'] == 'agency_site_maintenance_mode.php') {
            $page = '/admin/agency_site_maintenance_mode/';
        } else if ($params['page_url'] == 'admin_doc.php') {
            $page = '/admin/view_admin_docs';
        }else if ($params['page_url'] == 'email_templates.php') {
            $page = '/email/view_email_templates';
        }else if ($params['page_url'] == 'cronjobs/index') {
            $page = '/cronjobs/index';
        }else if ($params['page_url'] == 'create_renewals.php') {
            $page = '/admin/renewals';
        }else if ($params['page_url'] == 'admin/emergency_action') {
            $page = '/admin/emergency_action';
        }else if ($params['page_url'] == 'admin/page_totals') {
            $page = '/admin/page_totals';
        }else if ($params['page_url'] == 'noticeboard.php') {
            $page = '/admin/noticeboard';
        }else if ($params['page_url'] == 'resources.php') {
            $page = '/admin/resources';
        }else if ($params['page_url'] == 'alarm_guide.php') {
            $page = '/admin/alarm_guide';
        }else if ($params['page_url'] == 'add_alarm.php') {
            $page = '/admin/add_alarm';
        }else if ($params['page_url'] == 'countries.php') {
          $page = '/admin/countries';
        }else if ($params['page_url'] == 'passwords.php') {
            $page = '/admin/passwords';
        }
        else if ($params['page_url'] == 'view_regions.php') {
            $page = '/admin/view_regions';
        }else if ($params['page_url'] == 'gmapproperties') {
            $page = '/gmapproperties';
        }

        // SMS
        else if ($params['page_url'] == 'sms/templates') {

            $page = '/sms/templates';
        }
        else if ($params['page_url'] == 'outgoing_sms.php') {

            $page = '/sms/view_outgoing_sms';
        }
        else if ($params['page_url'] == 'incoming_sms.php') {

            $page = '/sms/view_incoming_sms';
        }
        else if ($params['page_url'] == 'job_feedback.php') {

            $page = '/sms/view_job_feedback_sms';
        }

        // USERS
        else if ($params['page_url'] == 'add_sats_user.php') {

            $page = '/users/add';
        }
        else if ($params['page_url'] == 'view_sats_users.php') {
            $page = '/users';
        }

        // AGENCIES
        else if ($params['page_url'] == 'agency/services') {
            $page = '/agency/services';
        } else if ($params['page_url'] == 'agency/multi_agency_users') {
            $page = '/agency/multi_agency_users';
        } else if ($params['page_url'] == 'agency_booking_notes.php') {
            $page = '/agency2/view_agency_booking_notes';
        } else if ($params['page_url'] == 'view_target_agencies.php') {
            $page = '/agency/view_target_agencies';
        }else if ($params['page_url'] == 'add_agency_static.php') {
            $page = '/agency/add_agency';
        }else if ($params['page_url'] == 'view_agencies.php') {
            $page = '/agency/view_agencies';
        }

        // CALENDAR
        else if ($params['page_url'] == 'view_individual_staff_calendar.php') {

            $page = '/calendar/my_calendar_admin';
        } else if ($params['page_url'] == 'add_calendar_entry_static.php') {

            $page = '/calendar/add_new_entry';
        } else if ($params['page_url'] == 'view_tech_calendar.php') {

            $page = '/calendar/view_tech_calendar';
        }

        // TECHNICIAN
        else if ($params['page_url'] == 'add_purchase_order.php') {

            $page = '/reports/add_purchase_order';
        } else if ($params['page_url'] == 'view_overall_schedule.php') {

            $page = '/tech/view_overall_schedule';
        } else if ($params['page_url'] == 'stock/update_tech_stock') {

            $page = '/stock/update_tech_stock';
        }else if ($params['page_url'] == 'resources/index') {
            $page = '/resources/index';
        }else if ($params['page_url'] == 'resources/tech_doc_admin') {
            $page = '/resources/tech_doc_admin';
        }

        // FORMS
        else if ($params['page_url'] == 'expense.php') {
            $page = '/reports/view_add_expense/';
        } else if ($params['page_url'] == 'incident_and_injury_report.php') {
            $page = '/users/incident_and_injury_report/';
        }else if ($params['page_url'] == 'leave_form.php') {
            $page = '/users/leave_form';
        }

        // MESSAGES
        else if ($params['page_url'] == 'messages.php') {
            $page = '/messages';
        } else if ($params['page_url'] == 'create_message.php') {
            $page = '/messages/create';
        }

        // OPS DAILY ITEMS
        else if ($params['page_url'] == 'ageing_jobs_30_to_60.php') {
            $page = '/jobs/ageing_jobs_30_to_60';
        }else if ($params['page_url'] == 'ageing_jobs_60_to_90.php') {
            $page = '/jobs/ageing_jobs_60_to_90';
        }else if ($params['page_url'] == 'ageing_jobs_90.php') {
            $page = '/jobs/ageing_jobs_90';
        }else if ($params['page_url'] == 'duplicate_properties.php') {
            $page = '/properties/duplicate_properties';
        }else if ($params['page_url'] == 'last_contact.php') {
            $page = '/daily/view_last_contact';
        }else if ($params['page_url'] == 'missing_region.php') {
            $page = '/daily/missing_region';
        }else if ($params['page_url'] == 'properties/next_service') {
            $page = '/properties/next_service';
        }else if ($params['page_url'] == 'no_id_properties.php') {
            $page = '/daily/view_no_id_properties';
        }else if ($params['page_url'] == 'str_less_jobs.php') {
            $page = '/daily/str_less_jobs';
        }else if ($params['page_url'] == 'daily/incorrectly_upgraded_properties') {
            $page = '/daily/incorrectly_upgraded_properties';
        }else if ($params['page_url'] == 'daily/view_nsw_act_job_with_tbb') {
            $page = '/daily/view_nsw_act_job_with_tbb';
        }else if ($params['page_url'] == 'daily/duplicate_visit') {
            $page = '/daily/duplicate_visit';
        }else if ($params['page_url'] == 'daily/overdue_nsw_jobs') {
            $page = '/daily/overdue_nsw_jobs';
        }else if ($params['page_url'] == 'reports/no_retest_date_property') {
            $page = '/reports/no_retest_date_property';
        }else if ($params['page_url'] == '/daily/overdue_other_jobs') {
            $page = '/daily/overdue_other_jobs';
        }else if ($params['page_url'] == 'jobs/preferred_time') {
            $page = '/jobs/preferred_time';
        }

        else if ($params['page_url'] == 'benchmark/index') {
            $page = '/benchmark/index';
        }

        // API
        else if ($params['page_url'] == 'property_me/bulk_connect') { // PMe Bulk Match
            $page = '/property_me/bulk_connect';
        } else if ($params['page_url'] == 'property_me/linked_properties') { // Linked Properties
            $page = '/property_me/linked_properties';
        } else if ($params['page_url'] == 'property_me/supplier_pme') { // PMe Supplier
            $page = '/property_me/supplier_pme';
        }else if ($params['page_url'] == 'property_me/agency_connections') { // Agency PropertyMe Connections
            $page = '/property_me/agency_connections';
        }else if ($params['page_url'] == 'palace/index') { // Palace Bulk Match
            $page = '/palace/index';
        }else if ($params['page_url'] == 'palace/supplier_palace') { // Palace Supplier
            $page = '/palace/supplier_palace';
        }else if ($params['page_url'] == 'palace/agent_palace') { // Palace Agent
            $page = '/palace/agent_palace';
        }else if ($params['page_url'] == 'palace/diary_palace') { // Palace Diary Code
            $page = '/palace/diary_palace';
        }else if ($params['page_url'] == 'ourtradie/bulk_connect') { // Ourtradie Bulk Match
            $page = '/ourtradie/bulk_connect';
        }else if ($params['page_url'] == 'console/bulk_connect') { // Console Bulk Connect
            $page = '/console/bulk_connect';
        }else if ($params['page_url'] == 'console/property_info') { // Console Property Info
            $page = '/console/property_info';
        }else if ($params['page_url'] == 'property_tree/bulk_connect') { // Property Tree Bulk Connect
            $page = '/property_tree/bulk_connect';
        }else if ($params['page_url'] == 'property_tree/connect_agency') { // Property Tree Connect Agency
            $page = '/property_tree/connect_agency';
        }else if ($params['page_url'] == 'console/tenants_info') { // Console Tenants Info
            $page = '/console/tenants_info';
        }else if ($params['page_url'] == 'console/compliance_info') { // Console Compliance Info
            $page = '/console/compliance_info';
        }else if ($params['page_url'] == 'property_me/updated_tenants') { // Pme updated tenants
            $page = '/property_me/updated_tenants';
        }


        else if (strpos($params["page_url"], "/") === 0) {
            $page = $params["page_url"];
        }

        return $page;
    }


    public function sort_menu($menu_id_arr){

        $i = 1;
        foreach($menu_id_arr as $menu_id){

            if( $menu_id > 0 ){

                $this->db->query("
				UPDATE `menu`
				SET `sort_index` = {$i}
                WHERE `menu_id` = {$menu_id}
                ");
                $i++;

            }

        }

    }

}
