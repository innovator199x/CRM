<?php
/**
 * @name Date Helper
 */
defined('BASEPATH') OR exit('No direct script access allowed');

function dateFormatter($date) {
    if ($date === NULL) {
        return null;
    }
    return date_format(date_create($date), 'd/m/Y');
}

function date_after($a, $b){
    //$format = array('D jS \ M', 'Y-m-d', 'd-m-Y');		//date('Y-m-d');
    //$format = 'D jS \ M';
    $format = array('l jS F Y', 'Y-m-d', 'd-m-Y','l<\b\r>d/m/Y');
    $hours =  $a * 24;

    return date($format[$b], ($hours * 3600)+time());
}