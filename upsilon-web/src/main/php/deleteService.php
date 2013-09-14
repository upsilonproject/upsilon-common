<?php

$title = 'Delete service';
require_once 'includes/widgets/header.php';
require_once 'libAllure/Sanitizer.php';

use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;
use \libAllure\Session;

$serviceIdentifier = Sanitizer::getInstance()->filterString('identifier');

if (isset($_REQUEST['confirm']) || intval(Session::getUser()->getData('promptBeforeDeletions')) == 1) {
	deleteServiceByIdentifier($serviceIdentifier);

	$tpl->assign('message', 'Service deleted. <a href = "index.php">Index</a>');
	$tpl->display('message.tpl');
} else { 
	$tpl->assign('message', '<a href = "deleteService.php?identifier=' . $serviceIdentifier . '&amp;confirm">Sure?</a>');
	$tpl->display('message.tpl');
}

require_once 'includes/widgets/footer.php';

?>
