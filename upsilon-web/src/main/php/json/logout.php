<?php

set_include_path('../' . PATH_SEPARATOR . get_include_path());
require_once 'includes/common.php';

use \libAllure\Session;

Session::logout();

outputJson('Logged out');

?>
