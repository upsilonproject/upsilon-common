<?php

define('ANONYMOUS_PAGE', true);
set_include_path('../' . PATH_SEPARATOR . get_include_path());
require_once 'includes/common.php';

use \libAllure\Session;

if (Session::isLoggedIn()) {
	outputJson('Already logged in.');
}

$username = san()->filterString('username');
$password = san()->filterString('password');

try {
	Session::checkCredentials($username, $password);

	outputJson("logged in");
} catch (Exception $e) {
	denyApiAccess(outputJson('Exception. ' . get_class($e) . ' = ' . $e->getMessage()));
}

?>
