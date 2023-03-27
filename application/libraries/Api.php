<?php

class Api {

    private $statusCode = 200;
    private $success = false;
    private $message = '';
    private $data = [];

    private $postData = null;

    private $jwtData = [];

    public function __construct() {

        $this->CI =& get_instance();
    }

    public function buildPostData() {

        if (!in_array($this->CI->input->method(), ['post', 'put', 'patch'])) {
            return;
        }

        $rawInputStream = $this->CI->input->raw_input_stream;
        
        if (!empty($rawInputStream)) {
            $decoded = json_decode($rawInputStream, true);
            $jsonError = json_last_error();

            if ($jsonError == JSON_ERROR_NONE) {
                $this->postData = $decoded;
            }
            else {
                $errorMessage = json_last_error_msg();
                throw new \App\Exceptions\HttpException(400, "Invalid POST/PUT/PATCH data: {$errorMessage}");
            }
        }
    }

    public function setStatusCode($code) {
        $this->statusCode = $code;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function setSuccess($success) {
        $this->success = $success;
    }

    public function isSuccess() {
        return $this->success;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function putData($key, $data) {
        $this->data[$key] = $data;
    }

    public function mergeData($data) {
        $this->data = array_merge($this->data, $data);
    }

    public function getData() {
        return $this->data;
    }

    public function getPostData($field = null, $clean = TRUE) {
        if ($field) {
            if (!is_bool($field)) {
                if ($clean === TRUE) {
                    return $this->CI->security->xss_clean($this->postData[$field]);
                }
                else {
                    return $this->postData[$field];
                }
            }
            else if ($field === TRUE) {
                return $this->CI->security->xss_clean($this->postData);
            }
        }
        return $this->postData;
    }


    public function setJWTData($data) {
        $this->jwtData = (array)$data;
    }

    public function mergeJWTData($data) {
        $this->jwtData = array_merge($this->jwtData, $data);
    }

    public function getJWTData() {
        return $this->jwtData;
    }

    public function setJWTItem($key, $item) {
        $this->jwtData[$key] = $item;
    }

    public function getJWTItem($key) {
        return $this->jwtData[$key];
    }

    public function assertMethod($allowedMethods, $message = 'Method not allowed.') {
        if (!is_array($allowedMethods)) {
            $allowedMethods = [$allowedMethods];
        }

        $method = $this->CI->input->method();

        if (!in_array($method, $allowedMethods)) {
            throw new \App\Exceptions\HttpException(405, $message);
        }
    }

    public function validateForm() {
        if (!$this->CI->form_validation->run()) {
            $message = strip_tags($this->CI->form_validation->error_string());
            $message = !empty($message) ? $message : 'Unprocessable Entity';
            throw new \App\Exceptions\HttpException(422, $message);
        }
    }
}