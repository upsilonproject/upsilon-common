<?php

require_once 'includes/common.php';

use \libAllure\HtmlLinksCollection;
use \libAllure\DatabaseFactory;

$itemDashboard = array(
	'id' => 1
);

$links = new HtmlLinksCollection('Dashboard &nabla;');
$links->add('createWidgetInstance.php?dashboard=' . $itemDashboard['id'], 'Create Widget Instance');

$tpl->assign('links', $links);

$title = 'Dashboard';
require_once 'includes/widgets/header.php';

$sql = 'SELECT wi.id, w.class FROM widget_instances wi LEFT JOIN widgets w ON wi.widget = w.id ';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->execute();

$listInstances = $stmt->fetchAll();
$hiddenWidgets = array();

foreach ($listInstances as &$itemInstance) {
	$wi = 'Widget' . $itemInstance['class'];
	include_once 'includes/classes/Widget' . $itemInstance['class'] . '.php';

	$itemInstance['instance'] = new $wi();
	$itemInstance['instance']->loadArguments($itemInstance['id']);
	$itemInstance['instance']->init();

	if (!$itemInstance['instance']->isShown()) {
		$hiddenWidgets[] = $itemInstance;
	}
}

$tpl->assign('itemDashboard', $itemDashboard);
$tpl->assign('listInstances', $listInstances);
$tpl->assign('hiddenWidgets', $hiddenWidgets);
$tpl->display('dashboard.tpl');

require_once 'includes/widgets/footer.php';

?>
