<?php

require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

$sql = 'SELECT s.id, s.title, s.content FROM acceptable_downtime_sla s';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->execute();
$listSlas = $stmt->fetchAll();

$tpl->assign('listSlas', $listSlas);
$tpl->display('listSlas.tpl');

require_once 'includes/widgets/footer.php';

?>
