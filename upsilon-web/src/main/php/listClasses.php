<?php

require_once 'includes/common.php';

use \libAllure\HtmlLinksCollection;
use \libAllure\Sanitizer;

$id = (Sanitizer::getInstance()->filterUint('id'));

if (empty($id)) {
	$id = 1;
}

$links = new HtmlLinksCollection();
$links->add('createClass.php', 'Create Class');
$links->add('createClassRequirement.php?id=' . $id, 'Create Requirement');
$links->add('updateClass.php?id=' . $id, 'Update class');
$links->add('deleteClass.php?id=' . $id, 'Delete class');
$links->add('createClassInstance.php?', 'Create class instance');

$title = 'Classes';
require_once 'includes/widgets/header.php';

$tpl->assign('listSubClasses', getImmediateChildrenClasses($id));
$tpl->assign('listInstances', getImmediateClassInstances($id));

try {
	$tpl->assign('itemClass', getClass($id));
} catch (Exception $e) {
	$tpl->error('Could not find class: ' . $id);
}

$tpl->assign('listRequirements', getClassRequirements($id));

$tpl->display('listClasses.tpl');
require_once 'includes/widgets/footer.php';

?>
