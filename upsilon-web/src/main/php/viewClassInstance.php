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



$tpl->assign('title', 'View Service Instance');
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;
use \libAllure\Sanitizer;
use \libAllure\HtmlLinksCollection;

$sql = <<<SQL
SELECT
	i.id,
	i.title
FROM 
	class_instances i 
WHERE 
	i.id = :instanceId
LIMIT 1
SQL;

$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':instanceId', Sanitizer::getInstance()->filterUint('id'));
$stmt->execute();

$itemClassInstance = $stmt->fetchRowNotNull();

$tpl->assign('itemClassInstance', $itemClassInstance);

// TODO: It might be possible to have multiple checks assigned to a requirement
// if DISTINCT is removed and the query is slightly adjusted. 
$sql = <<<SQL
SELECT DISTINCT
	p.title AS owningClassTitle, 
	p.id AS owningClassId,
	r.title AS requirementTitle,
	r.id AS requirementId,
	a.service,
	s.identifier,
	s.karma,
	s.identifier AS serviceIdentifier,
	s.lastUpdated AS serviceLastUpdated,
	m.icon
FROM 
	class_instances i
LEFT JOIN class_instance_parents ip ON
	ip.instance = i.id
LEFT JOIN classes p ON 
	ip.parent = p.id
RIGHT JOIN class_service_requirements r ON
	r.class = p.id
LEFT JOIN class_service_assignments a ON
	a.instance = ip.instance
	AND a.requirement = r.id
LEFT JOIN services s ON
	a.service = s.id
LEFT JOIN service_metadata m ON
	m.service = s.identifier
WHERE 
	ip.instance = :instanceId
SQL;

$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':instanceId', Sanitizer::getInstance()->filterUint('id'));
$stmt->execute();

$listInstanceRequirements = $stmt->fetchAll();
$tpl->assign('listInstanceRequirements', $listInstanceRequirements);

$listMemberClasses = findMemberClasses($listInstanceRequirements);
$tpl->assign('listMemberClasses', $listMemberClasses);

$links = new HtmlLinksCollection();
$links->add('updateClassInstance.php?instance=' . $itemClassInstance['id'], 'Update instance');
$links->add('deleteClassInstance.php?instance=' . $itemClassInstance['id'], 'Delete instance');
$tpl->assign('links', $links);

$tpl->display('viewClassInstance.tpl');
require_once 'includes/widgets/footer.php';

?>
