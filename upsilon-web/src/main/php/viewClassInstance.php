<?php

$title = 'View Class Instance';
require_once 'includes/common.php';

function findMemberClasses($listInstanceRequirements) {
	$classes = array();

	foreach ($listInstanceRequirements as $requirement) {
		if (!in_array($requirement['owningClassTitle'], $classes)) {
			$classes[$requirement['owningClassTitle']] = array( 
				'title' => $requirement['owningClassTitle'],
				'id' => $requirement['owningClassId']
			);
		}
	}

	return $classes;
}

$itemClassInstance = getClassInstance(san()->filterUint('id'));

$links = linksCollection();
$links->add('updateClassInstance.php?instance=' . $itemClassInstance['id'], 'Update instance');
$links->add('deleteClassInstance.php?instance=' . $itemClassInstance['id'], 'Delete instance');

$tpl->assign('title', 'View Service Instance');
require_once 'includes/widgets/header.php';

$tpl->assign('itemClassInstance', $itemClassInstance);

$listInstanceRequirements = getInstanceRequirements($itemClassInstance['id']);

$tpl->assign('listInstanceRequirements', $listInstanceRequirements);

$listMemberClasses = findMemberClasses($listInstanceRequirements);
$tpl->assign('listMemberClasses', $listMemberClasses);

$tpl->display('viewClassInstance.tpl');
require_once 'includes/widgets/footer.php';

?>
