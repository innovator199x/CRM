<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use App\Exceptions\HttpException;
use Exception;

class PageLoadHooks {

    protected $CI;
    protected $startTime;

    public function registerStart() {
        $this->startTime = microtime(true);
    }

    public function recordDuration() {
        $this->CI =& get_instance();

        $controller = $this->CI->router->fetch_class();
        $action = $this->CI->router->fetch_method();

        $page = "{$controller}/{$action}";

        $trackedPages = [
            "agency/view_agencies",
            "jobs/completed",
            "jobs/merged_jobs",
            "jobs/to_be_booked",
            "menu/manager",
            "properties/active_properties",
            "reports/ajax_get_sales_result",
            "reports/expiring",
            "reports/new_jobs_report",
            "reports/new_properties_report",
            "reports/sales_report",
            "tech_run/keys",
        ];

        if (in_array($page, $trackedPages)) {

            $endTime = microtime(true);

            $duration = $endTime - $this->startTime;

            $db =& $this->CI->db;

            $db->insert('logged_page_durations', [
                'page' => $page,
                'duration' => round($duration * 1000),
                'created' => date('Y-m-d H:i:s'),
            ]);
        }
    }

}