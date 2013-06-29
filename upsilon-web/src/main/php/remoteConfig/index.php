<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../');

require_once 'includes/common.php';

$tpl->assign('comment', 'Generated config for node: ' . htmlentities($_REQUEST['node']));

$sql = 'SELECT * FROM remote_configs WHERE identifier = :node';
$stmt = stmt($sql);
$stmt->bindValue(':node', san()->filterString('node'));
$stmt->execute();
$remoteNode = $stmt->fetchRow();

$sql = 'SELECT s.* FROM remote_config_services s LEFT JOIN remote_configs rc ON s.config = rc.id WHERE rc.identifier = :node';
$stmt = stmt($sql);
$stmt->bindValue(':node', san()->filterString('node'));
$stmt->execute();
$services = $stmt->fetchAll();

$tpl->assign('listServices', $services);

$mtime = date(DATE_RFC2822, strtotime($remoteNode['mtime']));

header('Last-Modified: ' . $mtime);
header('Content-Type: application/xml');

$tpl->assign('mtime', $mtime);
$tpl->display('config.xml');

?>
