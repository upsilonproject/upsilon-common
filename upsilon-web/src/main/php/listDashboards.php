<?php

require_once 'includes/common.php';

$title = 'Dashboards';

$links = linksCollection();
$links->add('createDashboard.php', 'Create new dashboard');

require_once 'includes/widgets/header.php';

$sql = 'SELECT d.title, d.id, count(w.id) AS widgetCount FROM dashboard d LEFT JOIN widget_instances w ON w.dashboard = d.id GROUP BY d.id';
$stmt = stmt($sql);
$stmt->execute();

$tpl->assign('listDashboards', $stmt->fetchAll());
$tpl->display('listDashboards.tpl');

require_once 'includes/widgets/footer.php';

?>
