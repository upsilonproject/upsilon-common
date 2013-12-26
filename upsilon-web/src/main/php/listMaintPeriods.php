<?php

require_once 'includes/common.php';

$links = linksCollection();
$links->add('createMaintPeriod.php', 'Create new');

$title = 'Maintenance Periods';

require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

$sql = 'SELECT s.id, s.title, s.content, COUNT(m.id) AS countServices FROM acceptable_downtime_sla s LEFT JOIN service_metadata m ON m.acceptableDowntimeSla = s.id GROUP BY s.id';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->execute();
$listMaintPeriods = $stmt->fetchAll();

$tpl->assign('listMaintPeriods', $listMaintPeriods);
$tpl->display('listMaintPeriods.tpl');

require_once 'includes/widgets/footer.php';

?>
