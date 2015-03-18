<style type="text/css">
.okko {
	position:absolute;
	top: 4px;
	visibility:hidden;
}
.ui-autocomplete {
	padding: 0;
	list-style: none;
	background-color: #fff;
	width: 218px;
	border: 1px solid #B0BECA;
	max-height: 300px;
	overflow-y: scroll;
}
.ui-autocomplete .ui-menu-item a {
	border-top: 1px solid #B0BECA;
	display: block;
	padding: 4px 6px;
	color: #353D44;
	cursor: pointer;
	font-family: "Lucida Console", Monaco, monospace;
}
.ui-autocomplete .ui-menu-item:first-child a {
	border-top: none;
}
.ui-autocomplete .ui-menu-item a.ui-state-hover {
	background-color: #D5E5F4;
	color: #161A1C;
}
</style>
<!-- all this is for the countries auto complete -->
<script src="<?= $conf_include_path; ?>jquery/jquery.js"></script>
<script src="<?= $conf_include_path; ?>jquery/jquery-ui-autocomplete.js"></script>
<script src="<?= $conf_include_path; ?>jquery/jquery.select-to-autocomplete.js"></script>
<script type="text/javascript" charset="utf-8">
  (function($){
	$(function(){
	  $('select').selectToAutocomplete();
	  $('form').submit(function(){
		alert( $(this).serialize() );
		return false;
	  });
	});
  })(jQuery);
  
function create_alert(alert_content) {
	// alert_content has the values: errors, empty
	if(alert_content == 'errors')
		alert_text = '<?= reg_error_msg_1; ?> <img src="<?= $conf_images_path; ?>ko.png" align="absmiddle" /> <?= reg_error_msg_2; ?>.';
	else if(alert_content == 'empty')
		alert_text = '<?= reg_fill_mandatory; ?>';
	else if(alert_content == 'tncs')
		alert_text = '<?= reg_must_accept_tncs; ?>';
	else
		alert_text = alert_content;
		
	document.getElementById('alerts').className = 'notice_alert';
	document.getElementById('alerts').innerHTML = alert_text;
	
	show_alerts_box();
}
</script>
<?php

$translation_file = 'tra/new_user_'. $_SESSION['misc']['lang'] .'.php';
include $translation_file;

if($ob_user->user_id == $conf_generic_user_id) {
	# initialize the errors array on $_SESSION
	$_SESSION['registration'] = array();
	
	if($_POST) {
		# Check again all the POST elements.
		$error = '';
		if(strlen($_POST['first_name']) < 2)
			$error = reg_first_name_error;
		elseif(strlen($_POST['last_name']) < 2)
			$error = reg_last_name_error;
		elseif(strlen($_POST['address']) < 6)
			$error = reg_address_error;
		elseif(!$_POST['country'])
			$error = reg_country_error;
		elseif(!valida_dni($_POST['id_num']) && $_POST['id_num'] != '')
			$error = reg_id_num_error;
		elseif(strlen($_POST['phone_1']) < 7 && $_POST['phone_1'] != '')
			$error = reg_phone_1_error;
		elseif(strlen($_POST['phone_2']) < 7 && $_POST['phone_2'] != '')
			$error = reg_phone_2_error;
		elseif(!check_email_address($_POST['mail']))
			$error = reg_mail_error;
		elseif(strlen($_POST['username']) < 6 || strlen($_POST['username']) > 32)
			$error = reg_username_error;
		elseif(strlen($_POST['pass']) < 6 || strlen($_POST['pass']) > 32)
			$error = reg_pass_error;
		elseif($_POST['captcha'] != $_SESSION['misc']['captcha'])
			$error = reg_captcha_error;
		elseif(!$_POST['tncs'])
			$error = reg_tncs_error;
			
		if($error != '') {
		?>
        <script language="javascript">
		create_alert('<?= $error; ?>');
		</script>
        <?php
		}
		else {
		# insert the user in the database and send the code e-mail. ----------------
			# check if the user already exist in the DB.
			$ob_user = new user($_POST['mail']);
			if($ob_user->user_id) {		
				pa($ob_user);
				# if it exist but hasn't been activated yet, take it to the activation page that will allow to send a new message.
				
				# else, show alert and option to recover password.
				
			}
			else {
				# the user doesn't exist, insert it on to the database.
				$arr_data = array('first_name' => $_POST['first_name'], 'last_name' => $_POST['last_name'], 'pasapalabra' => $_POST['pass'],
								  'phone1' => $_POST['phone_1'], 'phone2' => $_POST['phone_2'], 'email' => $_POST['mail'], 'user_name' => $_POST['username'],
								  'address' => $_POST['address'], 'country' => $_POST['country'], 'id_num' => $_POST['id_num']);
				$user_id = user::create_user($arr_data);
								
				$ob_user = new user($user_id);
				$ob_user->add_default_modules();
				
				# send control_code e-mail
				$user_details = $ob_user->get_all_details();
				$enc_mail = encode($_POST['mail']);
				$arr_vars = array('check_code' => $user_details['control_code'],
								  'url' => $conf_main_url . $conf_main_page .'?mod=home&view=check_code&m='. $enc_mail,
								  'url_link' => $conf_main_url . $conf_main_page .'?mod=home&view=check_code&m='. $enc_mail .'&code='. $user_details['control_code']);
								  
				if($_SERVER['SERVER_NAME'] != 'localhost')
					mail_templates::send_mail($_POST['mail'], 'check_code', $arr_vars);
				
				jump_to($conf_main_page .'?mod=home&view=check_code&m='. $enc_mail);
				exit();
			}
		}
	}
	?>

<h5>
  <?= ucfirst(new_user_form); ?>
</h5>
<p><a href="<?= $conf_main_page; ?>">&lt;
  <?= return_to_main; ?>
  </a></p>
<form action="" method="post" name="reg_form">
  <table border="0" style="margin-top:10px; margin-left:170px;" width="620">
    <tr>
      <td width="50%"><div class="small_text">
          <?= first_name; ?>
        </div>
        <div style="position:relative;">
          <input name="first_name" type="text" class="input_normal" id="first_name" maxlength="125" style="width: 250px;" onblur="JavaScript:check_value('first_name','');" value="<?= $_POST['first_name']; ?>" />
          <div id="first_name_okko" class="okko" style="left:266px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
      <td width="50%"><div class="small_text">
          <?= last_name; ?>
        </div>
        <div style="position:relative;">
          <input name="last_name" type="text" class="input_normal" id="last_name" maxlength="125" style="width: 250px;" onblur="JavaScript:check_value('last_name','2');" value="<?= $_POST['last_name']; ?>" />
          <div id="last_name_okko" class="okko" style="left:266px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top:10px;"><div class="small_text">
          <?= address; ?> (<?= address_parts; ?>)
        </div>
        <div style="position:relative;">
          <input name="address" type="text" class="input_normal" id="address" maxlength="250" style="width: 560px;" onblur="JavaScript:check_value('address','3');" value="<?= $_POST['address']; ?>" />
          <div id="address_okko" class="okko" style="left:576px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
    </tr>
    <tr>
      <td style="padding-top:10px;"><div class="small_text">
          <?= country; ?>
        </div>
        <div style="position:relative;">
        <?php
		$ob_table = new table('countries');
		
		$arr_fields = array('tra_name', 'alt_name', 'relevancy');
		$arr_countries = dump_table2($ob_table->get_translated_table_name(), 'country_id', $arr_fields, ' WHERE active_ind = \'1\'');
//		pa($arr_countries);
		?>
          <select name="country" class="input_normal"  id="country-selector" style="width: 250px; height: 300px;" onblur="JavaScript:check_value('country','');" />
          <?php
		  if(!$_POST['country']) $_POST['country'] = 'ES';
		  foreach($arr_countries as $country) {
			  echo '<option value="'. $country['country_id'] .'" data-alternative-spellings="'. $country['alt_name'] .'"';
			  if($country['relevancy'] != '1')
			  	echo ' data-relevancy-booster="'. $country['relevancy'] .'"';
			  if(strtoupper($country['country_id']) == $_POST['country'])
			    echo ' selected="selected"';
			  echo '>'. $country['tra_name'] .'</option>';
		  }
		  ?>
          </select>
          <div id="country_okko" class="okko" style="left:266px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
      <td style="padding-top:10px;"><div class="small_text">
          <?= id_num .' ('. optional; ?>
          )</div>
        <div style="position:relative;">
          <input name="id_num" type="text" class="input_normal" id="id_num" maxlength="45" style="width: 250px;" onblur="JavaScript:check_value('id_num','2');" value="<?= $_POST['id_num']; ?>" />
          <div id="id_num_okko" class="okko" style="left:266px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
    </tr>
    <tr>
      <td style="padding-top:10px;"><div class="small_text">
          <?= phone; ?>
          1 (
          <?= optional; ?>
          )</div>
        <div style="position:relative;">
          <input name="phone_1" type="text" class="input_normal" id="phone_1" maxlength="45" style="width: 250px;" onblur="JavaScript:check_value('phone_1','3');" value="<?= $_POST['phone_1']; ?>" />
          <div id="phone_1_okko" class="okko" style="left:266px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
      <td style="padding-top:10px;"><div class="small_text">
          <?= phone; ?>
          2 (
          <?= optional; ?>
          )</div>
        <div style="position:relative;">
          <input name="phone_2" type="text" class="input_normal" id="phone_2" maxlength="45" style="width: 250px;" onblur="JavaScript:check_value('phone_2','');" value="<?= $_POST['phone_2']; ?>" />
          <div id="phone_2_okko" class="okko" style="left:266px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
    </tr>
    <tr>
      <td style="padding-top:10px;"><div class="small_text"> e-mail </div>
        <div style="position:relative;">
          <input name="mail" type="text" class="input_normal" id="mail" maxlength="250" style="width: 250px;" onblur="JavaScript:check_value('mail','2');" value="<?= $_POST['mail']; ?>" />
          <div id="mail_okko" class="okko" style="left:266px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
      <td style="padding-top:10px;"><div class="small_text">
          <?= alias; ?>
        </div>
        <div style="position:relative;">
          <input name="username" type="text" class="input_normal" id="username" maxlength="75" style="width: 250px;" onblur="JavaScript:check_value('username','3');" value="<?= $_POST['username']; ?>" />
          <div id="username_okko" class="okko" style="left:266px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
    </tr>
    <tr>
      <td style="padding-top:10px;" colspan="2"><div class="small_text">
          <?= password; ?>(<?= pass_size; ?>)
        </div>
        <div style="position:relative;">
          <input name="pass" type="text" class="input_normal" id="pass" maxlength="30" style="width: 250px;" onblur="JavaScript:check_value('pass','');" />
          <label>
            <input type="checkbox" name="hide_pwd" id="hide_pwd"  onchange="JavaScript:show_hide()"/>
            <?= hide_password; ?>
          </label>
          <div id="pass_okko" class="okko" style="left:576px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
        </div></td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top: 15px;"><?= captcha_message; ?>
        :</td>
    </tr>
    <tr>
      <td colspan="2"><table border="0">
          <tr>
            <td width="170" align="right"><div id="captcha_container"></div></td>
            <td><div style="position:relative;">
                <input name="captcha" type="text" class="input_normal" id="captcha" maxlength="3" style="width: 250px;" onblur="JavaScript:check_value('captcha');" />
                <a href="JavaScript:reload_captcha();"><img src="<?= $conf_images_path; ?>reload.png" alt="<?= ucfirst(reload); ?>" title="<?= ucfirst(reload); ?>" width="24" height="24" border="0" align="absmiddle" /></a>
                <div id="captcha_okko" class="okko" style="left:296px;"><img src="<?= $conf_images_path; ?>waiting.gif" title="" alt="" /></div>
              </div></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top:10px;"><label>
          <input type="checkbox" name="tncs" id="tncs" />
          <?= tncs_message; ?>
        </label></td>
    </tr>
    <tr>
      <td align="center" colspan="2" style="padding-top:15px;"><input name="submit_form" type="button"  onClick="JavaScript:register_form()" class="button_dark" value="   <?= ucfirst(register); ?>   " style="width:140px;" /></td>
    </tr>
  </table>
</form>
</div>
<?php
}
else {
	jump_to($conf_main_page);	
}
?>
<script language="javascript">

document.onload = reload_captcha();

function reload_captcha() {
	url = 'inc/ajax.php?content=captcha';
	getData(url, 'captcha_container');
}

function show_hide() {
	if(document.reg_form.hide_pwd.checked)
		document.reg_form.pass.type = 'password';
	else
		document.reg_form.pass.type = 'text';
}

function register_form() {
	if(document.reg_form.tncs.checked) {
		url = '<?= $conf_include_path; ?>ajax.php?content=submit_reg_form';//&element='+ field_name +'&value='+ field_value;
		getData_JS(url);//, 'alerts', 'show_alerts_box()');
	}
	else {
		create_alert('tncs');
	}
}

function check_value(field_name, instance) {
	field_value = eval('document.reg_form.' + field_name + '.value');
	url = '<?= $conf_include_path; ?>ajax.php?content=check_reg_form&element='+ field_name +'&value='+ field_value;
	switch(instance) {
		case '2': getData2(url, field_name + '_okko');	break;
		case '3': getData3(url, field_name + '_okko');	break;
		default:  getData(url, field_name + '_okko');	break;
	}
	
	document.getElementById(field_name + '_okko').style.visibility = 'visible';
}

</script>
