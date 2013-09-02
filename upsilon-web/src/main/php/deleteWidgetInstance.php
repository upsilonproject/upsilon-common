<?php

$title = 'Delete widget instance';
require_once 'includes/common.php';

$widgetInstance = deleteWidgetInstance(san()->filterUint('id'));

redirect('viewDashboard.php?id=' . $widgetInstance['dashboard'], 'Redirecting to dashboard');

?>
