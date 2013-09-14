<?php

require_once 'includes/common.php';

$sql = 'INSERT INTO acceptable_downtime_sla (title) VALUES ("Untitled Maint Period")';
$stmt = stmt($sql);
$stmt->execute();

redirect('updateMaintPeriod.php?id=' . $db->lastInsertId());
?>
