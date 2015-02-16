<?php

# create_alert is on new_user.phps

if(count($_SESSION['registration'])) {
	foreach($_SESSION['registration'] as $element) {
		if($element != '') {
			echo 'create_alert(\'errors\')';
			exit();
		}
	}
	
	echo 'document.reg_form.submit()';
}
else {
	echo 'create_alert(\'empty\')';
}

?>