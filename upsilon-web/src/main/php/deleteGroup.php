<?php

require_once 'includes/common.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$id = Sanitizer::getInstance()->filterUint('id');

$group = getGroup($id);

$sql = 'DELETE FROM group_memberships WHERE `group` = :groupTitle';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':groupTitle', $group['name']);
$stmt->execute();

$sql = 'DELETE FROM groups WHERE name = :groupTitle';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':groupTitle', $group['name']);
$stmt->execute();

redirect('listGroups.php', 'Deleted group: '. $group['name']); 
?>
