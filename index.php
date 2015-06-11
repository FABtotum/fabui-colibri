<?php

$_auto_install_file = 'AUTOINSTALL';

$_redirect = file_exists($_auto_install_file) ? '/recovery/install' : '/fabui';

header("Location: http://".$_SERVER['SERVER_NAME'].$_redirect);

?>
