<?php

require_once 'includes/common.php';

$id = san()->filterUint('id');
deleteMaintPeriodById($id);

redirect('listMaintPeriods.php');

?>
