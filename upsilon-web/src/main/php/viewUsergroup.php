<?php

require_once 'includes/common.php';

$id = san()->filterUint('id');

$links = linksCollection();
$links->add('addUsergroupMembership.php?id=' . $id, 'Add User to Group');
$links->add('updateUsergroupPermissions.php?id=' . $id, 'Update usergroup permissions');
$links->add('deleteUsergroup.php?id=' . $id, 'Delete usergroup');
require_once 'includes/widgets/header.php';

$group = getUserGroupById($id);

$tpl->assign('itemUsergroup', $group);
$tpl->assign('listMembers', getUsersInGroupById($id));
$tpl->display('viewUsergroup.tpl');

require_once 'includes/widgets/footer.php';
?>
