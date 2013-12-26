<?php

require_once 'includes/common.php';

$title = 'Dashboards';

$links = linksCollection();
$links->add('createDashboard.php', 'Create new dashboard');
$links->add('installWidgets.php', 'Install Widgets');

require_once 'includes/widgets/header.php';

$sql = 'SELECT d.title, d.id, count(w.id) AS widgetCount FROM dashboard d LEFT JOIN widget_instances w ON w.dashboard = d.id GROUP BY d.id';
$stmt = stmt($sql);
$stmt->execute();

$tpl->assign('listDashboards', $stmt->fetchAll());
$tpl->display('listDashboards.tpl');

$sql = 'SELECT w.id, w.class, count(wi.id) AS instances FROM widgets w LEFT JOIN widget_instances wi ON w.id = wi.widget GROUP BY w.id';
$stmt = stmt($sql);
$stmt->execute();

$tpl->assign('listWidgets', $stmt->fetchAll());
$tpl->display('listWidgets.tpl');

require_once 'includes/widgets/footer.php';

?>
