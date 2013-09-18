<?php

require_once 'includes/common.php';
require_once 'includes/functions.php';

use \libAllure\Session;
use \libAllure\HtmlLinksCollection;

global $tpl, $title;

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

$tpl->assign('crypto', isUsingSsl());
$tpl->assign('drawHeader', isset($_SESSION['drawHeader']) ? $_SESSION['drawHeader'] : true);
$tpl->assign('drawNavigation', isset($_SESSION['drawNavigation']) ? $_SESSION['drawNavigation'] : true);
$tpl->assign('drawBigClock', isset($_SESSION['drawBigClock']) ? $_SESSION['drawBigClock'] : false);
$tpl->assign('datetime', date('D H:i'));
$tpl->assign('apiClient', isset($_SESSION['apiClient']) ? $_SESSION['apiClient'] : false);

$generalLinks = linksCollection();

if (Session::isLoggedIn()) {
	$generalLinks = linksCollection();

	global $links, $title;
	$generalLinks->add('#', 'Actions');

	if (isset($links)) {
		$generalLinks->addChildCollection('Actions', $links);
	} else {
		$generalLinks->setEnabled(0, false);
	}

	$generalLinks->add('listDashboards.php', 'Dashboards');
	
	$generalLinks->add('#', 'Services');

	$generalLinksServices = linksCollection();
	$generalLinksServices->add('viewServiceHud.php', 'Service HUD');
	$generalLinksServices->add('listGroups.php', 'Groups');
	$generalLinksServices->add('viewList.php', 'List');
	$generalLinksServices->add('viewList.php?problems', 'List with problems');
	$generalLinksServices->add('listMaintPeriods.php', 'Maintenance Periods');
	$generalLinks->addChildCollection('Services', $generalLinksServices);

	$generalLinks->add('listClasses.php', 'Classes');
	$generalLinks->add('listNodes.php', 'Nodes');

	if (Session::getUser()->getData('experimentalFeatures')) {
		$experimentalLinks = linksCollection();
		$experimentalLinks->add('viewTasks.php', 'Tasks');
		$experimentalLinks->add('viewRoom.php?id=1', 'Rooms');

		$generalLinks->add('#', 'Experimental');
		$generalLinks->addChildCollection('Experimental', $experimentalLinks);
	}

	$systemLinks = linksCollection();
	$systemLinks->addIf(Session::getUser()->getData('enableDebug'), 'viewDebugInfo.php', 'Debug');
	$systemLinks->add('listUsergroups.php', 'Usergroups');
	$systemLinks->add('listUsers.php', 'Users');
	$systemLinks->add('listApiClients.php', 'API Clients');
	$systemLinks->add('preferences.php', 'Preferences');
	$systemLinks->add('logout.php', 'Logout');

	$generalLinks->add('#', 'System');
	$generalLinks->addChildCollection('System', $systemLinks);
}

$tpl->assign('generalLinks', $generalLinks);

$tpl->display('header.tpl');


?>
