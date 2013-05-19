<?php

$title = 'List of Services';
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

if (isset($_REQUEST['problems'])) {
	$sql = 'SELECT s.id, s.identifier, s.output, s.description, s.lastUpdated, s.karma, s.secondsRemaining FROM services s WHERE s.karma != "good"';
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
