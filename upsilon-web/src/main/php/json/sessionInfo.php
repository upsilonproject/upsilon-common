<?php

require_once 'jsonCommon.php';

use \libAllure\Session;

$user = [
	'username' => Session::getUser()->getUsername()
];

outputJson([
	'user' => [
		'username' => Session::getUser()->getUsername(),
		'id' => Session::getUser()->getId(),
	],
]);

?>
