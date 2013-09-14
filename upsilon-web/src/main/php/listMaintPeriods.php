<?php

require_once 'includes/common.php';

$links = linksCollection();
$links->add('createMaintPeriod.php', 'Create new');

$title = 'Maintenance Periods';

require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

$sql = 'SELECT s.id, s.title, s.content FROM acceptable_downtime_sla s';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->execute();
$listMaintPeriods = $stmt->fetchAll();

$tpl->assign('listMaintPeriods', $listMaintPeriods);
$tpl->display('listMaintPeriods.tpl');

require_once 'includes/widgets/footer.php';

?>
