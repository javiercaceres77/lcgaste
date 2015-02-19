<?php

$translation_file = 'tra/check_code_'. $_SESSION['misc']['lang'] .'.php';
include $translation_file;

?>

<h5>
  <?= ucfirst(new_user_registration) .': '. decode($_GET['m']); ?>
</h5>
<?php

if($_POST) {
//	$today = new my_date('today');
//	$minus_30_days = new my_date(date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 30, date('Y'))));
	$user = new user(decode($_GET['m']));
	
	if($user->is_account_active()) {
		jump_to($conf_main_page);
	}
	else {
		$user_details = $user->get_all_details();
		if($user_details['control_code'] == $_POST['check_code']) {
			if($user->activate_user()) {
			?>
<div style="padding-left:25px; padding-top:15px;">
  <table cellpadding="10" cellspacing="4" border="0" class="default_text" align="center" width="75%">
    <tr>
      <td><h3>
          <?= congratulations; ?>
          !</h3>
        <p>
          <?= you_have_registered_ok; ?>
        </p></td>
    </tr>
  </table>
</div>
<?php	
				exit();	
			}
		}
		else {
			$text = reg_insert_ko_1 .' ('. $_POST['check_code'] .') '. reg_insert_ko_2 .'.<br />'. reg_insert_ko_3 .'<br />';
			$text.= '<a href="'. $conf_main_page .'?mod='. $_GET['mod'] .'&view='. $_GET['view'] .'&m='. $_GET['m'] .'&code='. $_POST['check_code'] .'&se=1">'. reg_insert_ko_4 .'</a>: '. decode($_GET['m']);
			?>
<script language="javascript">

document.getElementById('alerts').className = 'notice_alert';
document.getElementById('alerts').innerHTML = '<?= $text; ?>';

show_alerts_box();

</script>
			<?php
			write_log_db('REGISTRATION', 'NEW USER KO', 'User activation code wrong: '. $_POST['check_code'] .' mail: '. decode($_GET['m']), 'check_code.php');
		}
	}
}

# Send e-mail from here
if($_GET['se']) {
	$mail = decode($_GET['m']);
	$user = new user($mail);
	if($user->user_id) {
		$arr_code = simple_select('users', 'user_id', $user->user_id, 'control_code');
		
		$enc_mail = $_GET['m'];
		$arr_vars = array('check_code' => $arr_code['control_code'],
						  'url' => $conf_main_url . $conf_main_page .'?mod=home&view=check_code&m='. $enc_mail,
						  'url_link' => $conf_main_url . $conf_main_page .'?mod=home&view=check_code&m='. $enc_mail .'&code='. $arr_code['control_code']);
						  
		if($_SERVER['SERVER_NAME'] != 'localhost')
			mail_templates::send_mail($mail, 'check_code', $arr_vars);
	}
	

}

?>
<div style="padding-left:25px; padding-top:15px;">
  <form name="check_form" id="check_form" method="post" action="">
    <table cellpadding="10" cellspacing="4" border="0" class="default_text" align="center" width="75%">
      <tr>
        <td valign="top"><img src="<?= $conf_images_path; ?>email_big.gif" /></td>
        <td colspan="2"><p>
            <?= check_code_txt_1; ?>
          </p></td>
      </tr>
      <tr>
        <td></td>
        <td align="right"><p>
            <?= ucfirst(code); ?>
            :&nbsp;</p></td>
        <td><input type="text" class="input_normal" name="check_code" value="<?= $_GET['code']; ?>" id="check_code" maxlength="50" style="width:300px;"/></td>
      </tr>
      <tr>
        <td></td>
        <td>&nbsp;</td>
        <td><input type="submit" name="send_code" class="button_dark" value="  <?= strtoupper(activate_account); ?>  " /></td>
      </tr>
    </table>
  </form>
</div>
