<?php

use \libAllure\DatabaseFactory;

global $tpl;
$tpl->assign('date', date(DATE_ATOM));
$tpl->assign('queryCount', DatabaseFactory::getInstance()->queryCount);
$tpl->display('footer.tpl');

exit;

?>
