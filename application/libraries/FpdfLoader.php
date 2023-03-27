<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class FpdfLoader
{
    public function __construct(){
      require $_SERVER['DOCUMENT_ROOT'].'/inc/fpdf/fpdf.php';	
      require $_SERVER['DOCUMENT_ROOT'].'/inc/fpdi-1.4.4/fpdi.php';
    }
}
?>