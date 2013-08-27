<?php

require_once 'includes/common.php';

$sql = 'INSERT INTO dashboard (title) values ("Dasboard")';
$stmt = stmt($sql)->execute();

redirect('viewDashboard.php?id=' . db()->lastInsertId());

?>
