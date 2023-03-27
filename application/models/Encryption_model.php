<?php
// https://www.geeksforgeeks.org/how-to-encrypt-and-decrypt-a-php-string/
class Encryption_model extends CI_Model {

    public $ciphering = 'BF-CBC'; // Store cipher method
    public $encryption_key = "eD9ktCRPwSf9JqHY";

    // Encrypt the string 
    public function encrypt($simple_string){
		return openssl_encrypt($simple_string, $this->ciphering, $this->encryption_key); 
    }

    // Descrypt the string 
    public function decrypt($simple_string){
		return openssl_decrypt($simple_string, $this->ciphering, $this->encryption_key); 
    }

}
?>