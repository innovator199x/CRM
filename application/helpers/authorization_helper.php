<?php

//AUTHORIZATION helper file
class AUTHORIZATION
{
    public static function validateTimestamp($token)
    {
        $CI =& get_instance();
        $token = self::validateToken($token);
        // if ($token != false && (now() - $token->timestamp < ($CI->config->item('token_timeout') * 60))) {
        if ($token != false && time() < $token->timestamp) {
            return $token;
        }
        return false;
    }

    public static function validateToken($token)
    {
        $CI =& get_instance();
        try {
            return JWT::decode($token, $CI->config->item('jwt_key'));
        }
        catch(\Exception $ex) {
            return false;
        }
    }

    public static function generateToken($data)
    {
        $CI =& get_instance();
        return JWT::encode($data, $CI->config->item('jwt_key'));
    }

}