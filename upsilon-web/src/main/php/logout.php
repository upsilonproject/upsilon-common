<?php

require_once 'includes/common.php';

use \libAllure\Session;

if (Session::isLoggedIn()) {
	Session::logout();
}

?>
<h1><a href = "/">Logged out</a></h1>
