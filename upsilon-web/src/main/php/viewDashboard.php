<?php

require_once 'includes/common.php';
require_once 'includes/classes/Dashboard.php';

use \libAllure\HtmlLinksCollection;
use \libAllure\DatabaseFactory;

$itemDashboard = array(
	'id' => 1
);

$links = new HtmlLinksCollection('Dashboard &nabla;');
$links->add('createWidgetInstance.php?dashboard=' . $itemDashboard['id'], 'Create Widget Instance');
$links->add('requestRescanWidgets()', 'RefreshWidgets');

$itemDashboard = new Dashboard(1); 

$title = 'Dashboard: ' . $itemDashboard->getTitle();
require_once 'includes/widgets/header.php';

$tpl->assign('itemDashboard', $itemDashboard);
$tpl->assign('listInstances', $itemDashboard->getWidgetInstances());
$tpl->assign('hiddenWidgets', $itemDashboard->getHiddenWidgetInstances());
$tpl->display('dashboard.tpl');

require_once 'includes/widgets/footer.php';

?>
