<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Templatedatahandler
{
	private $data = [];

    public function __construct()
    {
        $this->CI =& get_instance();
	}

	public function preloadData() {
        $this->data['loggedInUser'] = $this->CI->staff_accounts_model->get_staff_accounts([
            'sel_query' => '
                sa.`StaffID`,
                sa.ClassID,
                sa.`FirstName`,
                sa.`LastName`
            ',
            'staff_id' => $this->CI->session->staff_id,
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0,
        ])->row();

        $this->data["loggedInCountryAccess"] = $this->CI->db->select("*")
            ->from("country_access")
            ->where("staff_accounts_id", $this->CI->session->staff_id)
            ->get()->result_array();
	}

	public function getData() {
        return $this->data;
	}
}
?>