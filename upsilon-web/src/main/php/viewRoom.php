<?php

$title = 'View Room';
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;
use \libAllure\Sanitizer;

$sql = 'SELECT r.id, r.filename, r.title FROM rooms r';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':id', Sanitizer::getInstance()->filterUint('id'));
$stmt->execute();

foreach ($stmt->fetchAll() as $room) {
	$tpl->assign('itemRoom', $room);
	$tpl->assign('svgContent', file_get_contents('resources/images/rooms/' . $room['filename']));
	$tpl->display('viewRoom.tpl');
}

require_once 'includes/widgets/footer.php';

?>


