<?php

$title = 'Delete group';
require_once 'includes/common.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$id = Sanitizer::getInstance()->filterUint('id');

$group = getGroup($id);

deleteGroupByName($group['title']);

redirect('listGroups.php', 'Deleted group: '. $group['title']); 
?>
