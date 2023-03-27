<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

// $hook['post_controller_constructor'][] = [
//     'class' => 'ApiHooks',
//     'function' => 'checkToken',
//     'filename' => 'ApiHooks.php',
//     'filepath' => 'hooks',
//     'params' => [],
// ];

$hook['post_controller_constructor'][] = [
    'class' => 'TemplateHooks',
    'function' => 'preloadData',
    'filename' => 'TemplateHooks.php',
    'filepath' => 'hooks',
    'params' => [],
];

$hook['pre_system'][] = [
    'class' => 'PageLoadHooks',
    'function' => 'registerStart',
    'filename' => 'PageLoadHooks.php',
    'filepath' => 'hooks',
    'params' => [],
];

$hook['post_controller'][] = [
    'class' => 'PageLoadHooks',
    'function' => 'recordDuration',
    'filename' => 'PageLoadHooks.php',
    'filepath' => 'hooks',
    'params' => [],
];