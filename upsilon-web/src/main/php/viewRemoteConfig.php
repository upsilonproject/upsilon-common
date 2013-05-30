<?php

$title = 'View remote config';
require_once 'includes/common.php';

$sql = 'SELECT rc.* FROM remote_configs rc WHERE rc.id = :id';
$stmt = stmt($sql);
$stmt->bindValue(':id', san()->filterUint('id'));
$stmt->execute();
$remoteConfig = $stmt->fetchRowNOtNull();

use \libAllure\HtmlLinksCollection;

$links = new HtmlLinksCollection();
$links->add('createRemoteConfigService.php?id=' . $remoteConfig['id'], 'Create Service');

require_once 'includes/widgets/header.php';

$sql = 'SELECT s.* FROM remote_config_services s WHERE config = :id';
$stmt = stmt($sql);
$stmt->bindValue(':id', $remoteConfig['id']);
$stmt->execute();
$tpl->assign('services', $stmt->fetchAll());

$tpl->assign('remoteConfig', $remoteConfig);
$tpl->display('viewRemoteConfig.tpl');

require_once 'includes/widgets/footer.php';

?>
