<?php

namespace App\Exceptions;

use Exception;

class HttpException extends Exception {

    public function __construct($code, $message = '', Exception $previous = null) {

        if (!isset($code)) {
            throw new \InvalidArgumentExcetion('$code is not defined.');
        }

        parent::__construct($message, $code, $previous);
    }

}