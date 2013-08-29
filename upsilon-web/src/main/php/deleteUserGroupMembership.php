<?php

require_once 'includes/common.php';

$user = san()->filterUint('user');
$group = san()->filterUint('group');

deleteUserGroupMembership($user, $group);

redirect('viewUsergroup.php?id=' . $group, 'Deleted');

?>
