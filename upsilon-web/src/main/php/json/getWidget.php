<?php

require_once 'jsonCommon.php';

$id = san()->filterUint('id');

function getWidgetArgumentsFromWidgetId($id) {
	$sql = 'SELECT * FROM widget_instance_arguments wi WHERE wi.id = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	return $stmt->fetchAll();
}

outputJson(getWidgetArgumentsFromWidgetId($id));


?>
