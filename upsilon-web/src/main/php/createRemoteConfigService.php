<?php

require_once 'includes/common.php';

$sql = 'INSERT INTO remote_config_services (config) VALUES (:config) ';
$stmt = stmt($sql);
$stmt->bindValue(':config', san()->filterUint('id'));
$stmt->execute();

redirect('updateRemoteConfigurationService.php?id=' . insertId(), 'Editing...');

?>
