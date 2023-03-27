<?php
require APPPATH . './libraries/REST_Controller.php';
class Restapi extends REST_Controller {

	function __construct($config = 'rest') {
		parent::__construct($config);
        $this->load->model('vehicles_model');
	} 

    function contact_get() {
		$contacts = $this->db->get('staff_accounts')->result();
		$this->response($contacts, 200);
	}

    function plate_get() {
        $id = $this->get('id');
        if ($id == '') {
        $result = $this->db->get('vehicles')->result();
        } else {
        $this->db->where('vehicles_id', $id);
        $result = $this->db->get('vehicles')->result();
        }
        $this->response($result, 200);
    }

	public function upload_post()
    {
        $cpt = count($_FILES['files']['name']);

        $vehicle_id = $_POST['vehicle_id'];

        $tmp_tech_id = $this->vehicles_model->getTechByVehicleId($vehicle_id);
        $technician_id = $tmp_tech_id[0]->StaffID;

        //$technician_id = 12;
        //$vehicle_id = 10;
        
        $this->load->helper(array('form', 'url'));
    
        $config = array(
            'upload_path' => "./images/kms_files",
            'allowed_types' => "gif|jpg|png|jpeg|pdf",
            'overwrite' => TRUE
        );
    
        $this->load->library('upload',$config);
        
        for($count = 0; $count < $cpt; $count++)
        {
            $_FILES["file"]["name"] = "img_" . $vehicle_id ."_". $technician_id ."_". date("YmdHis") . $_FILES["files"]["name"][$count];
            $_FILES["file"]["type"] = $_FILES["files"]["type"][$count];
            $_FILES["file"]["tmp_name"] = $_FILES["files"]["tmp_name"][$count];
            $_FILES["file"]["error"] = $_FILES["files"]["error"][$count];
            $_FILES["file"]["size"] = $_FILES["files"]["size"][$count];
            if($this->upload->do_upload('file'))
            {
                $data = array('upload_data' => $this->upload->data());
                $this->set_response($data, REST_Controller::HTTP_CREATED);
            }
            else{
                $error = array('error' => $this->upload->display_errors());
                $this->response($error, REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }
}
