<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use App\Exceptions\HttpException;
use Exception;

class ApiHooks {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    //API HOOKS
    public function checkToken() {
        $inApi = is_a($this->CI, 'MY_ApiController');
        if ($inApi) {

            $allowedActions = $this->CI->allowedActions;
            $action = $this->CI->router->fetch_method();

            $URI =& $this->CI->uri;
            $params = array_slice($URI->rsegments, 2);

            if (!in_array($action, $allowedActions) && !in_array('*', $allowedActions)) {
                $headers = $this->CI->input->request_headers();

                $validatedToken = false;

                $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? $headers['AUTHORIZATION'] ?? $this->CI->input->get('tkn', null);

                if ($authorization) {
                    $validatedToken = Authorization::validateTimestamp($authorization);
                    $this->CI->api->setJWTData($validatedToken);
                }

                if (!$validatedToken) {

                    if ($this->CI->config->item('jwt_debug')) {
                        $this->CI->api->putData('apache_headers', apache_request_headers());
                        $this->CI->api->putData('server', $_SERVER);
                        $this->CI->api->putData('headers', $headers);
                        $this->CI->api->putData('token', $authorization);
                        $this->CI->api->putData('jwt', Authorization::validateToken($authorization));
                        $this->CI->api->putData('server_time', time());
                    }

                    $this->CI->api->setStatusCode(401);
                    $this->CI->api->setSuccess(false);
                    $this->CI->api->setMessage('Invalid Token.');
                }
                else {
                    $this->run($action, $params);
                }
            }
            else {
                $this->run($action, $params);
            }


            $this->renderAPI();

            exit;
        }
    }

    private function run($action, $params) {
        try {
            $this->CI->api->buildPostData();
            call_user_func_array(array(&$this->CI, $action), $params);
        }
        catch(HttpException $exception) {
            $this->CI->api->setStatusCode($exception->getCode());
            $this->CI->api->setSuccess(false);
            $this->CI->api->setMessage($exception->getMessage());
            $this->putException($exception);
        }
        catch(Exception $exception) {
            $this->CI->api->setStatusCode(500);
            $this->CI->api->setSuccess(false);
            $this->CI->api->setMessage($exception->getMessage());
            $this->putException($exception);
        }
    }

    private function putException($exception) {
        if (getenv('APP_DEBUG', 0)) {
            $this->CI->api->putData('exception', [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);
        }
    }

    private function renderAPI() {
        $this->CI->output->set_status_header($this->CI->api->getStatusCode());
        $outputData = [
            'success' => $this->CI->api->isSuccess(),
            'message' => $this->CI->api->getMessage(),
        ];
        if (getenv('APP_DEBUG', 0)) {
            $outputData['post_data'] = $this->CI->api->getPostData();
        }
        $outputData['data'] = $this->CI->api->getData();
        $this->CI->output->set_output(json_encode($outputData, JSON_PRETTY_PRINT))->_display();
    }
}
