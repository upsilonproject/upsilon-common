<?php

require_once 'includes/common.php';

$title = 'Usergroups';
$links = linksCollection();
$links->add('createUsergroup.php', 'Create usergroup');
require_once 'includes/widgets/header.php';

$tpl->assign('listUsergroups', getUsergroups());
$tpl->display('listUsergroups.tpl');

require_once 'includes/widgets/footer.php';

?>
