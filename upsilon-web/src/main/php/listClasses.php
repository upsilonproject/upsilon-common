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

use \libAllure\DatabaseFactory;

# leaf/edge nodes
$ssql = 'SELECT n.id, n.title AS parentId FROM classes AS n WHERE n.r = n.l + 1';

# children at depth 1
$sqlImmediateChildren = <<<SQL
SELECT 
	n.id AS id,
	n.title, 
	children.count AS childrenCount,
	(count(parent.title) - (children.depth + 1)) AS depth
FROM 
	classes AS n,
	classes AS parent,
	classes AS sub_parent, 
	(
		SELECT 
			n.title,
			(count(parent.title) - 1) AS depth,
			count(parent.title) AS count
		FROM
			classes AS n,
			classes AS parent
		WHERE
			n.l BETWEEN parent.l AND parent.r 
			AND n.id = :nodeId
		GROUP BY 
			n.title, 
			n.l
	) AS children
WHERE 
	n.l BETWEEN parent.l AND parent.r
	AND n.l BETWEEN sub_parent.l AND sub_parent.r
	AND parent.title = children.title
	AND n.id != :nodeIdOrig
GROUP BY n.title
HAVING depth <= 1
ORDER BY n.l
	
SQL;

# full graph overview
$sql = <<<SQL
SELECT
	c.title AS classTitle
FROM 
	classes AS c
SQL;

$sql = $sqlImmediateChildren;
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':nodeId', $id);
$stmt->bindValue(':nodeIdOrig', $id);
$stmt->execute();

$listClasses = $stmt->fetchAll();
//print_r($listClasses); exit;

function getClassInstances($id) {
	$sql = <<<SQL
SELECT DISTINCT 
	ci.id AS id,
	ci.title, 
	r.title AS requirementTitle,
	r.id AS requirementId,
	count(s.id) AS goodCount,
	count(a.id) AS assignedCount,
	count(r.id) AS totalCount
FROM 
	class_instances ci
LEFT JOIN class_instance_parents ip ON 
	ip.instance = ci.id
LEFT JOIN classes c ON
	ip.parent = c.id
LEFT JOIN class_service_requirements r ON
	r.class = c.id
LEFT JOIN class_service_assignments a ON
	a.instance = ci.id
	AND a.requirement = r.id
LEFT JOIN services s ON
	a.service = s.id
	AND s.karma = "GOOD"
GROUP BY
	ci.id
SQL;
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$listInstances = $stmt->fetchAll();

	foreach ($listInstances as $index => $instance) {
			$row = &$listInstances[$index];
			$row['assignedKarma'] = 'unknown';

			if ($row['assignedCount'] == $row['totalCount']) {
				$row['overallKarma'] = 'good';
			} else {
				$row['overallKarma'] = 'bad';
			}

			if ($row['goodCount'] == $row['assignedCount']) {
				$row['assignedKarma'] = 'good';
			} else {
				$row['assignedKarma'] = 'bad';
			}
	}

	return $listInstances;
}

$tpl->assign('listInstances', getClassInstances($id));

$tpl->assign('listClasses', $listClasses);

function getClass($id) {
	$sql = 'SELECT c.* FROM classes c WHERE c.id = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	$class = $stmt->fetchRowNotNull();

	return $class;
}

function getClassRequirements($id) {
	$sql = 'SELECT r.id, r.title FROM class_service_requirements r WHERE r.class = :id ';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	return $stmt->fetchAll();
}

try {
	$tpl->assign('itemClass', getClass($id));
} catch (Exception $e) {
	$tpl->error('Could not find class: ' . $id);
}

$tpl->assign('listRequirements', getClassRequirements($id));

$tpl->display('listClasses.tpl');
require_once 'includes/widgets/footer.php';

?>
