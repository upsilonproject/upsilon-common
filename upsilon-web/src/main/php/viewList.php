<?php

$title = 'List of Services';
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

if (isset($_REQUEST['problems'])) {
	$sql = 'SELECT s.id, s.identifier, s.output, s.description, s.lastUpdated, s.karma, s.secondsRemaining FROM services s WHERE s.karma != "good"';
} else if (isset($_REQUEST['ungrouped']))  {
	$sql = 'SELECT s.id, s.identifier, s.output, s.description, s.lastUpdated, s.karma, s.secondsRemaining FROM services s WHERE s.identifier NOT IN (SELECT g.service FROM service_group_memberships g)';
} else if (isset($_REQUEST['maintPeriod'])) {
	$id = san()->filterUint('maintPeriod');
	$sql = 'SELECT s.id, s.identifier, s.output, s.description, s.lastUpdated, s.karma, s.secondsRemaining FROM services s LEFT JOIN service_metadata m ON s.identifier = m.service WHERE m.acceptableDowntimeSla = ' . $id;
} else {
	$sql = 'SELECT s.id, s.identifier, s.output, s.description, s.lastUpdated, s.karma, s.secondsRemaining FROM services s';
}

$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->execute();
$listServices = $stmt->fetchAll();

$tpl->assign('listServices', $listServices);
$tpl->display('listServices.tpl');

require_once 'includes/widgets/footer.php';

?>
