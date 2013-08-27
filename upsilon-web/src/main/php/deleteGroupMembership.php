<?php

$title = 'Delete group membership';
require_once 'includes/common.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;

$id = Sanitizer::getInstance()->filterUint('id');

$membership = getServiceGroupMembershipById($id);
deleteServiceGroupMembershipById($id);

redirect('viewService.php?id=' . $membership['service'], 'Membership deleted.');
?>
