<?php

require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

$sql = 'SELECT s.id, s.identifier, s.description, s.karma, s.goodCount, s.output FROM services s WHERE s.karma = "good"';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->execute();
$stmt->fetchAll();

echo '<p>' . $stmt->numRows() . ' are <span class = "good">GOOD</a></p><hr />';

$services = getServicesBad();

if (count($services) == 0) {
	echo 'Everything is good!';
} else {
	$tpl->assign('listServices', $services);
	$tpl->display('metricList.tpl');
}

echo '<hr />';
$tpl->assign('listNodes', getNodes());
$tpl->display('mobileStats.tpl');

require_once 'includes/widgets/footer.php';

?>
