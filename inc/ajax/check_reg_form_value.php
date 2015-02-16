<?php

$error = false;
$err_msg = '';

switch($_GET['element']) {
	case 'first_name': case 'last_name':	# check that length > 1
		$error = strlen($_GET['value']) < 2;
		$err_msg = $error ? min_2_chars : '';
	break;
	case 'address':							# check that length > 5
		$error = strlen($_GET['value']) < 6;
		$err_msg = $error ? min_6_chars : '';
	break;
	case 'id_num':
		$error = !valida_dni($_GET['value']) && $_GET['value'] != '';
		$err_msg = $error ? id_instructions : '';
	break;
	case 'phone_1': case 'phone_2':			# lenght > 6 or is empty
		$error = strlen($_GET['value']) < 7 && $_GET['value'] != '';
		$err_msg = $error ? min_6_chars : '';
	break;
	case 'mail':
		$error = !check_email_address($_GET['value']);
		$err_msg = $error ? email_not_valid : '';
	break;
	case 'pass': 	case 'username':
		$error = strlen($_GET['value']) < 6 || strlen($_GET['value']) > 32;
		$err_msg = $error ? between_6_and_32 : '';
	break;
	case 'captcha':
		$error = $_GET['value'] != $_SESSION['misc']['captcha'];
		$err_msg = $error ? captcha_in_number : '';
	break;
}

$_SESSION['registration'][$_GET['element']] = $err_msg;

$img = $error ? 'ko.png' : 'ok.png';

echo '<img src="'. $conf_images_path . $img .'" title="'. ucfirst($err_msg) .'" alt="">';


?>