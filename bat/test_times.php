<?php

# Includes  ----------------------------------

include '/var/www/lcgaste/inc/config_dom.php';
include $conf_include_path . 'comm.php';
#include $conf_include_path . 'connect.php';
include $conf_include_path . 'oops_comm.php';

date_default_timezone_set($conf_timezone);

# Sanitize get and post  ----------------------------------
sanitize_input();
unset($_POST, $_GET);

$mins = 200;

$obj_date_time = new date_time('now');

pa($obj_date_time, 'obj_date_time');

$obj_plus_mins = $obj_date_time->plus_mins($mins);

pa($obj_plus_mins, 'obj_plus_mins');

$obj_my_time = new my_time('now');

pa($obj_my_time, 'obj_my_time');

$obj_plus_mins = $obj_my_time->plus_mins($mins);

pa($obj_plus_mins, 'obj_plus_mins');

?>
