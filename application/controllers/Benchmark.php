<?php

class Benchmark extends CI_Controller {

    public function index() {
        $data['start_load_time'] = microtime(true);

        $data['title'] = "Page Benchmarks";

        $date = $this->input->get_post('date') ?? "";
        if ($date != "") {
            $date = DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        }
        $data['date'] = $date;
        $pageDurationsQuery = $this->db
            ->select("
                page,
                COUNT(duration) AS count,
                AVG(duration) AS average,
                MIN(duration) AS minimum,
                MAX(duration) AS maximum
            ")
            ->from('logged_page_durations')
            ->like('created', $date, 'after')
            ->group_by('page')
            ->order_by('page ASC');

        $pageDurations= $pageDurationsQuery->get()->result_array();
        $data['page_load_durations'] = $pageDurations;

        $this->load->view('templates/inner_header', $data);
        $this->load->view('benchmark/index', $data);
        $this->load->view('templates/inner_footer', $data);
    }

}

?>