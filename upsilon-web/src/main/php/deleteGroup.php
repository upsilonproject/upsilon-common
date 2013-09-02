<?php

$title = 'Delete group';
require_once 'includes/common.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$id = Sanitizer::getInstance()->filterUint('id');

$group = getGroup($id);

deleteGroupByName($group['name']);

redirect('listGroups.php', 'Deleted group: '. $group['name']); 
?>
