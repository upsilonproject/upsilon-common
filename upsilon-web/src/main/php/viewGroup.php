<?php

$title = 'View Group';
require_once 'includes/widgets/header.php';

use \libAllure\Sanitizer;

$itemGroup = getGroup(Sanitizer::getInstance()->filterUint('id'));

$tpl->assign('hidden', false);
$tpl->assign('itemGroup', $itemGroup);
$tpl->assign('singleGroup', true);
$tpl->display('group.tpl');

require_once 'includes/widgets/footer.php';

?>
