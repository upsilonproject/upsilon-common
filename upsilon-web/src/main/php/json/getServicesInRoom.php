<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../');
require_once 'includes/common.php';

use \libAllure\DatabaseFactory;
use \libAllure\Sanitizer;

$sql = 'SELECT s.id, s.identifier, m.roomPositionX, m.roomPositionY, s.karma FROM services s JOIN service_metadata m ON s.identifier = m.service AND m.room = :roomId';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':roomId', Sanitizer::getInstance()->filterUint('id'));
$stmt->execute();

outputJson($stmt->fetchAll());

?>
