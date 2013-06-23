<?php

$title = 'DANGER: DEBUG PAGE!';
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

if (isset($_REQUEST['purgeDeadGroups'])) {
	$sql = 'DELETE FROM group_memberships WHERE service NOT IN (SELECT identifier FROM services) OR service = "" ';
	DatabaseFactory::getInstance()->query($sql);
}

if (isset($_REQUEST['purge'])) {
	$sql = 'DELETE FROM services;';
	DatabaseFactory::getInstance()->query($sql);

	$sql = 'DELETE FROM group_memberships';
	DatabaseFactory::getInstance()->query($sql);

	$sql = 'DELETE FROM groups';
	DatabaseFactory::getInstance()->query($sql);

	$sql = 'DELETE FROM service_check_results';
	DatabaseFactory::getInstance()->query($sql);
}

function dbquery($sql) {
	$res = DatabaseFactory::getInstance()->query($sql);

	return $res->fetchAll();
}

function metric($title, $value) {
	if (is_bool($value)) {
		$value = 'True';
	} else {
		$value = 'False';
	}

	echo '<p><strong>' . $title . '</strong>: ' . $value . '</p>';
}

metric('User agent', $_SERVER['HTTP_USER_AGENT']);
metric('Service check results', dbQuery('SELECT count(id) AS count FROM service_check_results')[0]['count']);
metric('Services', dbQuery('SELECT count(id) AS count FROM services')[0]['count']);
metric('Group memberships', dbQuery('SELECT count(id) AS count FROM group_memberships')[0]['count']);
metric('Groups', dbQuery('SELECT count(id) AS count FROM groups')[0]['count']);

function getServiceResults() {
	$sql = 'SELECT s.identifier, r.service, r.checked, r.karma FROM service_check_results r JOIN services s ON r.service = s.id WHERE (r.checked + 60) < now() ORDER BY r.service, r.checked';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$lastService = null;

	$hourAgo = strtotime('-1 hour');

	$listServiceResults = array();

	foreach ($stmt->fetchAll() as $serviceResult) {
		$relative = strtotime($serviceResult['checked']) - $hourAgo;
		$relative /= 2;

		if ($relative < 50) {
			continue;
		}

		$listServiceResults[] = $serviceResult;
	}
}

echo '<a href = "?purge" class = "badButton">Purge everything</a><br />';
echo '<a href = "?purgeDeadGroups" class = "badButton">Purge dead groups</a>';

//$tpl->assign('listServiceResults', getServiceResults());
//$tpl->display('schedule.tpl');

$modules = apache_get_modules();

echo '<h2> Apache modules</h2>';
metric('mod_expires?', in_array('mod_expires', $modules));

require_once 'includes/widgets/footer.php'

?>
