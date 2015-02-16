<?php

header("Content-Type: text/html; charset=iso-8859-1");

session_start();

# Includes
	
include 'config.php';
include 'comm.php';
include 'connect.php';
/*include 'oops_comm.php';
include 'oops_sc.php';
if($_GET['mod'] == 'admin') include 'comm_admin.php';*/

if(!$_GET['lang'] && !$_SESSION['misc']['lang']) $_GET['lang'] = $conf_default_lang;
if($_GET['lang']) $_SESSION['misc']['lang'] = $_GET['lang'];

//$in['file'] = '../tra/'. basename($_SERVER['SCRIPT_NAME'], '.php') .'_'. $_SESSION['misc']['lang'] . '.php';
$in['file'] = '../tra/index_'. $_SESSION['misc']['lang'] . '.php';
include 'translation.php'; 

date_default_timezone_set($conf_timezone);

# Sanitize get and post  ----------------------------------
sanitize_input();

# This file is always called when we need some ajax.
# The $_GET parameter content indicates which file will actually return the contents.

switch($_GET['content']) {
	case 'construct_date':
		$odate = new my_date($_GET['value']);
		echo $odate->odate;
	break;
	
	case 'captcha':					include 'ajax/captcha_generator.php';	break;

	case 'check_reg_form':			include 'ajax/check_reg_form_value.php'; break;
	
	case 'submit_reg_form':			include 'ajax/submit_reg_form.php'; break;
}

?>
