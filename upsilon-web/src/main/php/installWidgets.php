<?php

require_once 'includes/common.php';

function widgetExists($class) {
	$sql = 'SELECT w.id FROM widgets w WHERE w.class = :class';
	$stmt = stmt($sql);
	$stmt->bindValue(':class', $class);
	$stmt->execute();

	return $stmt->numRows() != 0;
}

$sql = 'INSERT INTO widgets (class) VALUES (:class) ';
$stmt = stmt($sql);

function installWidgetsFromDirectory($directory) {
	if (!is_dir($directory)) {
		return;
	}

	foreach (scandir($directory) as $widget) {
		if (strpos($widget, 'Widget') === FALSE) {
			continue;
		}

		$widget = str_replace('.php', '', $widget);
		$widget = str_replace('Widget', '', $widget);
		
		if (strlen($widget) > 0 && !widgetExists($widget)) {
			$stmt->bindValue(':class', $widget);
			$stmt->execute();
		}
	}
}

installWidgetsFromDirectory('includes/classes/');
installWidgetsFromDirectory('plugins/widgets/');
redirect('listDashboards.php');

?>
