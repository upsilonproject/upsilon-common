<?php

require_once 'includes/common.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$id = Sanitizer::getInstance()->filterUint('id');

$sql = 'DELETE FROM widget_instances WHERE id = :id';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':id', $id);
$stmt->execute();

redirect('viewDashboard.php', 'Redirected');

?>
