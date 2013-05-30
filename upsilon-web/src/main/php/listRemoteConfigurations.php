<?php

require_once 'includes/common.php';

use \libAllure\DatabaseFactory;
use \libAllure\HtmlLinksCollection;

$sql = 'SELECT rc.id, rc.identifier FROM remote_configs rc';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->execute();

$configs = $stmt->fetchAll();

$links = new HtmlLinksCollection();
$links->add('createRemoteConfiguration.php', 'Create remote configurations');

$title = 'Remote configurations';
require_once 'includes/widgets/header.php';

$tpl->assign('listRemoteConfigs', $configs);
$tpl->display('listRemoteConfigs.tpl');

require_once 'includes/widgets/footer.php';
?>
