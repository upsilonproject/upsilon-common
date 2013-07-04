<?php

require_once 'includes/common.php';

$title = 'Service HUD';
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

$sql = 'SELECT s.estimatedNextCheck, s.secondsRemaining, s.description, s.id FROM services s WHERE s.description NOT IN (SELECT s2.description FROM group_memberships m INNER JOIN services s2 ON m.service = s2.identifier)';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->execute();

$listServices = $stmt->fetchAll();

$tpl->assign('listUngroupedServices', $listServices);
$tpl->display('ungroupedServices.tpl');

$tpl->display('index.tpl');

foreach (getGroups() as $itemGroup) {
	$tpl->assign('itemGroup', $itemGroup);
	$tpl->assign('hidden', false);
	$tpl->display('group.tpl');
}

$tpl->display('hudJavascript.tpl');

require_once 'includes/widgets/footer.php';

?>
