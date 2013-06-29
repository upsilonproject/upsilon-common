<?php

$title = 'View Node';
require_once 'includes/common.php';

use \libAllure\HtmlLinksCollection;
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

$links = new HtmlLinksCollection();
$links->add('deleteNode.php?id=' . $node['id'], 'Delete');

require_once 'includes/widgets/header.php';
require_once 'libAllure/Sanitizer.php';

$tpl->assign('itemNode', $node);
$tpl->display('viewNode.tpl');

$sql = 'SELECT s.id, s.identifier, s.lastUpdated, s.output, s.karma FROM services s WHERE s.node = :node';
$stmt = stmt($sql);
$stmt->bindValue(':node', $node['identifier']);
$stmt->execute();

$tpl->assign('listServices', $stmt->fetchAll());
$tpl->display('listServices.tpl');

require_once 'includes/widgets/footer.php';

?>
