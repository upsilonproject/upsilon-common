<?php

use \libAllure\DatabaseFactory;

global $tpl;
$tpl->assign('date', date(DATE_ATOM));

try {
	$tpl->assign('queryCount', DatabaseFactory::getInstance()->queryCount);
} catch (Exception $e) {
	$tpl->assign('queryCount', '?');
}

$tpl->display('footer.tpl');

exit;

?>
