<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

function commented_var_dump($var) {
    echo "<!--";
    var_dump($var);
    echo "-->";
}

function pre_var_dump($var, $heading = "") {
    if (!empty($heading)) {
        echo "<h3>{$heading}</h3>";
    }
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
}

function dump_header($heading) {
    echo "<h2>{$heading}</h2>";
}

?>