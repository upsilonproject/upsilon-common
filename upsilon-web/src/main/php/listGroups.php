<?php

require_once 'includes/common.php';

use \libAllure\HtmlLinksCollection;

$links = new HtmlLinksCollection();
$links->add('createGroup.php', 'Create group');

$title = 'Groups';

require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;

$sql = 'SELECT g.id, g.name AS title FROM groups g';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->execute();

$tpl->assign('listGroups', $stmt->fetchAll());
$tpl->display('listGroups.tpl');

require_once 'includes/widgets/footer.php';

?>
