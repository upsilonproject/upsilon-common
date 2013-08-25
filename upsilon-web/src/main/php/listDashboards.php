<?php

$title = 'Dashboards';
require_once 'includes/widgets/header.php';

$sql = 'SELECT d.title, d.id FROM dashboard d ';
$stmt = stmt($sql);
$stmt->execute();

$tpl->assign('listDashboards', $stmt->fetchAll());
$tpl->display('listDashboards.tpl');

require_once 'includes/widgets/footer.php';

?>
