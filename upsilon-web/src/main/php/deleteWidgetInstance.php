<?php

$title = 'Delete widget instance';
require_once 'includes/common.php';

function getWidgetInstance() {
	$sql = 'SELECT FROM widget_instances wi WHERE wi.id = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	return $stmt->fetchRowNotNull();
}

function deleteWidgetInstance($id) {
	$widgetInstance = getWidgetInstance($id);

	$sql = 'DELETE FROM widget_instances WHERE id = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	return $widgetInstance;
}

$widgetInstance = getWidget(san()->filterUint('id'));

redirect('viewDashboard.php/?id=' . $widgetInstance['dashboard'], 'Redirecting to dashboard');

?>
