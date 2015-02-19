<style type="text/css"></style>
<?php

if($ob_user->user_id == $conf_generic_user_id) {
	?>

<form action="<?= $conf_main_page; ?>?action=login" method="post" name="login_form">
  <table border="0" style="margin-top:40px; margin-left:355px;" width="200">
    <tr>
      <td><div class="small_text">e-mail</div>
        <input name="user" type="text" class="input_normal" id="user" maxlength="60" autofocus="autofocus" tabindex="1" style="width: 250px;" /></td>
    </tr>
    <tr>
      <td style="padding-top:15px;"><div class="small_text">
          <?= password; ?>
        </div>
        <input name="pass" type="password" class="input_normal" id="pass" maxlength="30" tabindex="2" style="width: 250px;" /></td>
    </tr>
    <tr>
      <td align="right" class="small_text"><a href="#">
        <?= forgot_password_q; ?>
        </a></td>
    </tr>
    <tr>
      <td align="right" style="padding-top:15px;"><input name="Submit" type="submit"  onClick="JavaScript:submit_login_form()" class="button" value="   <?= strtoupper(login); ?>   " tabindex="3" style="width:140px;" /></td>
    </tr>
    <tr>
      <td align="right"><p> <a href="<?= $conf_main_page; ?>?mod=home&view=new_user">
          <?= ucfirst(new_user); ?>
          </a></p></td>
    </tr>
  </table>
</form>
<table border="0" style="margin-top:20px; margin-left:280px;" width="400">
  <tr>
    <td><p><?= pb_main_intro_1; ?>.</p>
    <p><?= pb_main_free_1; ?>.</p></td>
  </tr>
</table>
</div>
<?php
}
else {
	jump_to($conf_main_page);
}
?>