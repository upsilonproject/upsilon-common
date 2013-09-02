<?php

require_once 'includes/common.php';

$id = san()->filterUint('id');
deleteDashboardById($id);

redirect('listDashboards.php', 'Dashboard deleted.');

?>
