<?php

$title = 'Update remote configuration service';
require_once 'includes/common.php';

$sql = 'UPDATE remote_configs SET mtime = now()';
$stmt = stmt($sql);
$stmt->execute();

redirect('index.php');

?>
