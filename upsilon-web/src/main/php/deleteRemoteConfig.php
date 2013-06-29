<?php

$title = 'Delete class instance';
require_once 'includes/common.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$id = Sanitizer::getInstance()->filterUint('id');
$sql = 'DELETE FROM remote_configs WHERE id = :id';

$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':id', $id);
$stmt->execute();

redirect('listNodes.php', 'Remote config deleted.')


?>
