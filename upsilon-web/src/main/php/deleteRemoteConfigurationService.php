<?php

require_once 'includes/common.php';

$sql = 'DELETE FROM remote_config_services WHERE id = :id';
$stmt = stmt($sql);
$stmt->bindValue(':id', san()->filterUint('id'));
$stmt->execute();

redirect('listRemoteConfigurations.php', 'Deleted');;

?>
