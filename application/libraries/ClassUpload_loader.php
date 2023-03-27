<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ClassUpload_loader
{
    public function __construct(){
      require $_SERVER['DOCUMENT_ROOT'].'/inc/class.upload/src/class.upload.php';	      
    }
}
?>