<?php

class Sales_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function get_sales_document() {
        $this->db->select("*");
        $this->db->from("sales_documents");
        $this->db->where("country_id={$this->config->item('country')}");
        $this->db->order_by('date', 'DESC');
        return $this->db->get();
    }

    public function add_sales_document($params) {

        $this->db->insert('sales_documents', $params);
        return $this->db->insert_id();
    }

    public function remove_sales_document($id) {
        if ((int) $id === 0) {
            return 0;
        }
        $this->db->where('sales_documents_id', $id);
        $this->db->delete('sales_documents');
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }
    

}
