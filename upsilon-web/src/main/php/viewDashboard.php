<?php

require_once 'includes/common.php';
require_once 'includes/classes/Dashboard.php';

use \libAllure\HtmlLinksCollection;
use \libAllure\DatabaseFactory;

$id = san()->filterUint('id');

$links = new HtmlLinksCollection('Dashboard &nabla;');
$links->add('createWidgetInstance.php?dashboard=' . $id, 'Create Widget Instance');
$links->add('updateDashboard.php?id=' . $id, 'Update this dashboard');
$links->add('deleteDashboard.php?id=' . $id, 'Delete this dashboard');

$itemDashboard = new Dashboard($id); 

$title = 'Dashboard: ' . $itemDashboard->getTitle();
require_once 'includes/widgets/header.php';

$tpl->assign('itemDashboard', $itemDashboard);
$tpl->assign('listInstances', $itemDashboard->getWidgetInstances());
$tpl->assign('hiddenWidgets', $itemDashboard->getHiddenWidgetInstances());
$tpl->display('dashboard.tpl');

require_once 'includes/widgets/footer.php';

?>
