<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class PusherLoader
{
    public function __construct(){
		require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';	
    }
}
?>