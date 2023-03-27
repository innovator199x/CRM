<?php

use App\Exceptions\HttpException;

class Auth extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->allowedActions = ['login', 'header_check'];
    }

    public function login() {
        $this->api->assertMethod('post');

        $this->form_validation->set_data($this->api->getPostData());

        $this->form_validation->set_rules([
            [
                'field' => 'email',
                'rules' => 'required|valid_email',
            ],
            [
                'field' => 'password',
                'rules' => 'required',
            ],
        ]);

        $this->api->validateForm();

        $email = trim($this->api->getPostData('email'));
        $password = trim($this->api->getPostData('password'));

        //$email = trim($_POST['email']);
        //$password = trim($_POST['password']);

        $country_id = $this->config->item('country');

        $params = array(
            'sel_query' => '
                sa.`StaffID`,
                sa.`ClassID`,
                sa.`Email`,
                sa.`FirstName`,
                sa.`LastName`,
                sa.`TechID`,
                sa.`password_new`,
                sa.`profile_pic`
            ',
            'joins' => array('country_access'),
            'email' => $email,
            'country_id' => $country_id,
            'staff_id' => null,
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0
        );

        // get user details
        $userAccountResult = $this->staff_accounts_model->get_staff_accounts($params);
        //echo $this->db->last_query();

        if ($userAccountResult->num_rows() > 0) {
            $userAccount = $userAccountResult->row();

            $hashedPassword = $userAccount->password_new;

            if ($userAccount->ClassID == 6 && password_verify($password, $hashedPassword)) {
                //echo "IF PASSED!!";
                
                $this->api->setStatusCode(200);
                $this->api->setSuccess(true);
                $this->api->setMessage('Login successful.');

                $this->api->putData('logged_user', [
                    'staff_id' => $userAccount->StaffID,
                    'class_id' => $userAccount->ClassID,
                    'email' => $userAccount->Email,
                    'first_name' => $userAccount->FirstName,
                    'last_name' => $userAccount->LastName,
                    'tech_id' => $userAccount->TechID,
                    'profile_pic' => $userAccount->profile_pic,
                ]);

                $this->load->model('tech_model');
                $kmsResult = $this->tech_model->getKmsByStaffId($userAccount->StaffID);

                if (($kmsArr = $kmsResult->row_array())) {
                    $kms = [
                        'kms' => $kmsArr['kms'],
                        'last_updated' => $this->system_model->formatDate($kmsArr['kms_updated']),
                        'vehicle_id' => $kmsArr['v_vehicle_id'],
                    ];
                }
                else {
                    $kms = [
                        'kms' => null,
                        'last_updated' => null,
                        'vehicle_id' => null,
                    ];
                }

                $this->api->putData('kms', $kms);


                if ($kms['vehicle_id']) {
                    $vehicle_id = $kms['vehicle_id'];
                    $techStockData  = $this->db->select('tech_stock_id, date')->from('tech_stock')->where('vehicle', $vehicle_id)->order_by('date','DESC')->limit(1)->get()->row_array();
                    $techStockDate = new DateTimeImmutable($techStockData['date'], new DateTimeZone(date_default_timezone_get()));
                    $next7Days = $techStockDate->modify('+7 days')->format('Y-m-d');

                    $techStock = [
                        'original_date' => $techStockDate->format('Y-m-d'),
                        'next_schedule' => $next7Days,
                    ];
                }
                else {
                    $techStock = [
                        'original_date' => null,
                        'next_schedule' => null,
                    ];
                }

                $this->api->putData('tech_stock', $techStock);

                $this->api->putData('country_id', $this->config->item('country'));

                $this->api->putData('token', Authorization::generateToken([
                    'staff_id' => $userAccount->StaffID,
                    'class_id' => $userAccount->ClassID,
                    'timestamp' =>  time() + ($this->config->item('token_timeout') * 60),
                    'type' => 'tech',
                ]));

                return;
            }
        }

        $this->api->setStatusCode(200);
        $this->api->setSuccess(false);
        $this->api->setMessage('Invalid e-mail or password.');
    }

    public function refresh_token() {
        $staffId = $this->api->getJWTItem('staff_id');
        $classId = $this->api->getJWTItem('class_id');

        $this->api->putData('token', Authorization::generateToken([
            'staff_id' => $staffId,
            'class_id' => $classId,
            'timestamp' =>  time() + ($this->config->item('token_timeout') * 60),
            'type' => 'tech',
        ]));

        $this->api->setStatusCode(200);
        $this->api->setSuccess(true);
    }

    public function logout() {
        // invalidate token, maybe?
    }

    public function header_check() {
        $headers = $this->input->request_headers();
        if ($this->config->item('is_dev_server')) {
            $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? $headers['AUTHORIZATION'] ?? $this->input->get('tkn', null);

            $this->api->putData('headers', $headers);
            $this->api->putData("authorization", $authorization);
            $this->api->setStatusCode(!is_null($authorization) ? 200 : 401);
            $this->api->setSuccess(!is_null($authorization));
        }
        else {
            $this->api->setStatusCode(200);
            $this->api->setSuccess(true);
        }
    }
}
?>