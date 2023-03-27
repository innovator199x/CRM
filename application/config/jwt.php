<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//JWT config file
$config['jwt_key'] = 'hellohellohello'; // 'ingDLMRuGe9UKHRNjs7cYckS2yul4lc3';

/*Generated token will expire in 1 minute for sample code
* Increase this value as per requirement for production
*/
$config['token_timeout'] = 30;

$config['jwt_debug'] = true;

/* End of file jwt.php */
/* Location: ./application/config/jwt.php */