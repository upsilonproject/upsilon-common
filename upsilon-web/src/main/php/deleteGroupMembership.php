<?php

$title = 'Delete group membership';
require_once 'includes/common.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$id = Sanitizer::getInstance()->filterUint('id');

$sql = 'SELECT m.*, s.id AS service FROM group_memberships m INNER JOIN services s ON m.service = s.identifier WHERE m.id = :id';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':id', $id);
$stmt->execute();

$membership = $stmt->fetchRowNotNull();

$sql = 'DELETE FROM group_memberships WHERE id = :id';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':id', $id);
$stmt->execute();

redirect('viewService.php?id=' . $membership['service'], 'Membership deleted.');
?>
