<?php

require_once 'includes/common.php';
require_once 'includes/functions.php';

use \libAllure\Session;
use \libAllure\HtmlLinksCollection;

global $tpl;

$tpl->assign('mobile', isMobile());

if (Session::isLoggedIn()) {
	$dtBegin = Session::getUser()->getData('daytimeBegin');
	$dtEnd = Session::getUser()->getData('daytimeEnd');

	$nowHour = intval(date('G'));

	$tpl->assign('isNighttime', !($nowHour > $dtBegin && $nowHour < $dtEnd));
	$tpl->assign('tutorialMode', Session::getUser()->getData('tutorialMode'));
	$tpl->assign('enableDebug', Session::getUser()->getData('enableDebug'));
	$tpl->assign('username', Session::getUser()->getUsername());
} else {
	$tpl->assign('isNighttime', false);
}

if (isset($title)) {
	$tpl->assign('title', $title);
} else {
	$tpl->assign('title', 'Untitled page');
}

if (isset($links)) {
	$tpl->assign('links', $links);
}

$tpl->assign('loggedIn', Session::isLoggedIn());

if (!isset($_SERVER['HTTPS'])) {
	$_SERVER['HTTPS'] = 'off';
}
$tpl->assign('crypto', $_SERVER['HTTPS']);
$tpl->assign('drawHeader', isset($_SESSION['drawHeader']) ? $_SESSION['drawHeader'] : true);
$tpl->assign('drawNavigation', isset($_SESSION['drawNavigation']) ? $_SESSION['drawNavigation'] : true);
$tpl->assign('drawBigClock', isset($_SESSION['drawBigClock']) ? $_SESSION['drawBigClock'] : false);
$tpl->assign('datetime', date('D H:i'));
$tpl->assign('apiClient', isset($_SESSION['apiClient']) ? $_SESSION['apiClient'] : false);

$userLinks = new HtmlLinksCollection();
$userLinks->add('preferences.php', 'Preferences');
$userLinks->add('listApiClients.php', 'API Clients');
$userLinks->add('logout.php', 'Logout');
$tpl->assign('userLinks', $userLinks);

$tpl->display('header.tpl');

if (!Session::isLoggedIn()) {
	require_once 'login.php';
}

?>
