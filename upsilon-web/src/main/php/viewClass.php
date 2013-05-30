<?php

require_once 'includes/common.php';

$title = 'View class';
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;
use \libAllure\Sanitizer;

function applyServiceRequirements() {
	$sql = 'SELECT p.id, c.id AS class, c.title AS classTitle FROM class_instance_parents p LEFT JOIN classes c ON p.parent = c.id WHERE p.instance = :instance';

	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':instance', Sanitizer::getInstance()->filterUint('id'));
	$stmt->execute();

	$listClassInstances = $stmt->fetchAll();

$sql = <<<SQL
SELECT 
	r.id AS requirementId,
	r.title,
	a.service,
	s.identifier AS serviceIdentifier,
	s.lastUpdated AS serviceLastUpdated
FROM class_service_requirements r
LEFT JOIN class_instances i ON
	i.id = :instance
LEFT JOIN class_service_assignments a ON 
	a.instance = i.id
	AND a.requirement = r.id
LEFT JOIN services s ON 
	a.service = s.id
WHERE 
	r.class = :class 
SQL;
	$stmt = DatabaseFactory::getInstance()->prepare($sql);

	foreach ($listClassInstances as $index => $classInstance) {
		$stmt->bindValue(':instance', $classInstance['id']);
		$stmt->bindValue(':class', $classInstance['class']);
		$stmt->execute();

		$listRequiredServices = $stmt->fetchAll();

		$listClassInstances[$index]['listServices'] = $listRequiredServices;
	}

	return $listClassInstances;
}

$listClassInstances = applyServiceRequirements();

$tpl->assign('listClassInstances', $listClassInstances);
$tpl->display('viewClass.tpl');
require_once 'includes/widgets/footer.php';

?>
