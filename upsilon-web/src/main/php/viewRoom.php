<?php

$title = 'View Room';
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;
use \libAllure\Sanitizer;

$tpl->display('listRooms.tpl');

$rooms = getRooms();

if (empty($rooms)) {
	$tpl->error('No rooms defined. This feature is very much in testing and requires manual database editing.');
} else {
	foreach ($rooms as $room) {
		$tpl->assign('itemRoom', $room);
		$tpl->assign('svgContent', file_get_contents('resources/images/rooms/' . $room['filename']));
		$tpl->display('viewRoom.tpl');
	}
}

require_once 'includes/widgets/footer.php';

?>


