<?php
/**
 * @name Staff Helper
 */
defined('BASEPATH') OR exit('No direct script access allowed');


function format_staff_name($fname, $lname) {
    return "{$fname}" . ( ($lname != "") ? ' ' . strtoupper(substr($lname, 0, 1)) . '.' : '' );
}
