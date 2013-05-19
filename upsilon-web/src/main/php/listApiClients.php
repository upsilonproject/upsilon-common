<?php

require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;
use \libAllure\Session;

$sql = 'SELECT a.* FROM apiClients a WHERE a.user = :userId';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':userId', Session::getUser()->getId());
$stmt->execute();

$tpl->assign('listApiClients', $stmt->fetchAll());
$tpl->display('listApiClients.tpl');

require_once 'includes/widgets/footer.php';

?>
