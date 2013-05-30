<?php

$title = 'Delete node';
require_once 'includes/common.php';
require_once 'includes/functions.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$id = Sanitizer::getInstance()->filterUint('id');

$sql = 'DELETE FROM nodes WHERE id = :id ';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':id', $id);
$stmt->execute();

redirect('index.php');

?>
