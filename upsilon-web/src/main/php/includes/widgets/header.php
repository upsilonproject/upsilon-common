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

$tpl->assign('loggedIn', Session::isLoggedIn());

if (!isset($_SERVER['HTTPS'])) {
	$_SERVER['HTTPS'] = 'off';
}

$crypto = $_SERVER['HTTPS'] == 'on';
$tpl->assign('crypto', $crypto);
$tpl->assign('drawHeader', isset($_SESSION['drawHeader']) ? $_SESSION['drawHeader'] : true);
$tpl->assign('drawNavigation', isset($_SESSION['drawNavigation']) ? $_SESSION['drawNavigation'] : true);
$tpl->assign('drawBigClock', isset($_SESSION['drawBigClock']) ? $_SESSION['drawBigClock'] : false);
$tpl->assign('datetime', date('D H:i'));
$tpl->assign('apiClient', isset($_SESSION['apiClient']) ? $_SESSION['apiClient'] : false);

$generalLinks = linksCollection();

if (Session::isLoggedIn()) {
	$generalLinks = linksCollection();

	if (isset($links)) {
		$generalLinks->add('#', $title . ' Actions');
		$generalLinks->addChildCollection($title . ' Actions', $links);
	}

	$generalLinks->add('viewDashboard.php?id=1', 'Dashboard');
	
	$generalLinks->add('#', 'Services');

	$generalLinksServices = linksCollection();
	$generalLinksServices->add('viewServiceHud.php', 'Service HUD');
	$generalLinksServices->add('listGroups.php', 'Groups');
	$generalLinksServices->add('viewList.php', 'List');
	$generalLinksServices->add('viewList.php?problems', 'List with problems');
	$generalLinksServices->add('listSlas.php', 'SLAs');
	$generalLinks->addChildCollection('Services', $generalLinksServices);

	$generalLinks->add('listClasses.php', 'Classes');
	$generalLinks->add('listNodes.php', 'Nodes');

	$generalLinksPlus = linksCollection();
	$generalLinksPlus->add('viewTasks.php', 'Tasks');
	$generalLinksPlus->add('viewRoom.php?id=1', 'Rooms');
	$generalLinksPlus->add('listUsers.php', 'Users');

	$generalLinks->add('#', 'Other');
	$generalLinks->addChildCollection('Other', $generalLinksPlus);

	$userLinks = linksCollection();
	$userLinks->add('preferences.php', 'Preferences');
	$userLinks->add('listApiClients.php', 'API Clients');
	$userLinks->addIf(Session::getUser()->getData('enableDebug'), 'viewDebugInfo.php', 'Debug');
	$userLinks->add('logout.php', 'Logout');

	$generalLinks->add('#', 'User');
	$generalLinks->addChildCollection('User', $userLinks);
}

$tpl->assign('generalLinks', $generalLinks);

$tpl->display('header.tpl');


?>
