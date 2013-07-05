<?php

set_include_path('../' . PATH_SEPARATOR . get_include_path());
require_once 'includes/common.php';

var_dump('yo'); exit;
$status = san()->filterStringEnum('status', array('!GOOD', 'ALL'), '!GOOD');

switch ($status) {
	case '!GOOD':
		outputJson(getServicesBad());
	default:
		outputJson(getServices());
}


?>
