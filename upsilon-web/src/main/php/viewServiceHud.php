<?php

require_once 'includes/common.php';

$title = 'Service HUD';
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

$tpl->assign('listUngroupedServices', getServicesUngrouped());
$tpl->display('ungroupedServices.tpl');

$tpl->display('index.tpl');

foreach (getGroups() as $itemGroup) {
	$tpl->assign('itemGroup', $itemGroup);
	$tpl->assign('hidden', false);
	$tpl->display('group.tpl');
}

$tpl->display('hudJavascript.tpl');

require_once 'includes/widgets/footer.php';

?>
