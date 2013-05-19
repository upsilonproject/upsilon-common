<?php

require_once 'includes/widgets/header.php';
require_once 'libAllure/Sanitizer.php';

use \libAllure\Sanitizer;

if (isset($_REQUEST['identifier'])) {
	$sql = 'SELECT n.* FROM nodes n WHERE n.identifier = :nodeId LIMIT 1';
	$id = Sanitizer::getInstance()->filterString('identifier');
} else {
	$sql = 'SELECT n.* FROM nodes n WHERE n.id = :nodeId LIMIT 1';
	$id = Sanitizer::getInstance()->filterUint('id');
}

$stmt = $db->prepare($sql);
$stmt->bindValue(':nodeId', $id);
$stmt->execute();

$node = $stmt->fetchRow(); 

$tpl->assign('itemNode', $node);
$tpl->display('viewNode.tpl');

require_once 'includes/widgets/footer.php';

?>
