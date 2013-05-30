<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../');

require_once 'includes/common.php';

$tpl->assign('comment', 'Generated config for node: ' . htmlentities($_REQUEST['node']));

$sql = 'SELECT s.* FROM remote_config_services s LEFT JOIN remote_configs rc ON s.config = rc.id WHERE rc.identifier = :node';
$stmt = stmt($sql);
$stmt->bindValue(':node', san()->filterString('node'));
$stmt->execute();
$services = $stmt->fetchAll();

$tpl->assign('listServices', $services);

header('Last-Modified: ' . date(DATE_RFC2822));
header('Content-Type: application/xml');
$tpl->display('config.xml');

?>
