<?php

require_once 'includes/common.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$requirementId = Sanitizer::getInstance()->filterUint('requirement');

$sql = 'DELETE FROM class_service_requirements WHERE id = :requirement';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':requirement', $requirementId);
$stmt->execute();

redirect('listClasses.php');
