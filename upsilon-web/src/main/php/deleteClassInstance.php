<?php

require_once 'includes/common.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$id = Sanitizer::getInstance()->filterUint('instance');

$sql = 'DELETE FROM class_instances WHERE id = :id';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':id', $id);
$stmt->execute();

redirect('listClasses.php', 'Redirected')

?>
