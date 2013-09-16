<?php

use \libAllure\DatabaseFactory;
use \libAllure\Session;
use \libAllure\Sanitizer;
use \libAllure\HtmlLinksCollection;

function isUsingSsl() {
	if (!isset($_SERVER['HTTPS'])) {
		$_SERVER['HTTPS'] = 'off';
	}

	return $_SERVER['HTTPS'] == 'on';
}

function explodeOrEmpty($delimiter = null, $serialString = "") {
	$serialString = trim($serialString);

	if (strlen($serialString) == 0) {
		return array();
	} else {
		return explode($delimiter, $serialString);
	}
}

function getSiteSetting($key, $default = '') {
        global $settings;
        global $db;

        if (empty($settings)) {
                $sql = 'SELECT s.`key`, s.value FROM settings s';
                $stmt = DatabaseFactory::getInstance()->prepare($sql);
                $stmt->execute();

                foreach ($stmt->fetchAll() as $row) {
                        $settings[$row['key']] = $row['value'];
                }
        }


        if (!isset($settings[$key])) {
                return $default;
        } else {
                return $settings[$key];
        }
}

function connectDatabase() {
        try {
                $db = new \libAllure\Database(CFG_DB_DSN, CFG_DB_USER, CFG_DB_PASS);
                \libAllure\DatabaseFactory::registerInstance($db);
        } catch (Exception $e) {
                throw new Exception('Could not connect to database. Check the username, password, host, port and database name.<br />' . $e->getMessage(), null, $e);
        }

        try {
                $maint = getSiteSetting('maintenanceMode', 'NONE');
        } catch (Exception $e) {
                if ($e->getCode() == '42S02') {
                        throw new Exception('Settings table not found. Did you import the table schema?', null, $e);
                } else {
                        throw new Exception('Unhandled SQL error while getting settings table: ' . $e->getMessage(), null, $e);
                }
        }

        if ($maint === 'NONE') {
                throw new Exception('Essential setting "maintenanceMode" does not exist in the DB. Did you import the initial data?');
        }

        return $db;
}

function insertId() {
	return DatabaseFactory::getInstance()->lastInsertId();
}

function stmt($sql) {
	return DatabaseFactory::getInstance()->prepare($sql);
}
 
function san() {
	return Sanitizer::getInstance();
}

function db() { 
	return DatabaseFactory::getInstance();
}

function linksCollection() {
	return new HtmlLinksCollection();
}

function redirect($url) {
	header('Location: ' . $url);

	exit;
}

function plural($num, $short = false, $longForm = null) {
	$shortForm = substr($longForm, 0, 1);

	if ($short) {
		return $shortForm;
	} else {
		if ($num != 1) {
			$longForm .= 's';
		}

		return ' ' . $longForm . ' ago';
	}
}
	 
function getRelativeTime($date, $short = false, $fromDate = null) {
	if ($fromDate == null) {
		$fromDate = time();
	}

	return getRelativeTimeSecondsRectified($fromDate - strtotime($date), $short);
}

function getRelativeTimeSecondsRectified($diff, $short = false) {
	$rectified = false;

	if ($diff < 0) {
		$diff = abs($diff);
		$rectified = true;
	}

	$res = getRelativeTimeSeconds($diff, $short);

	if ($rectified) {
		return '+'.$res;
	} else {
		return '-'.$res;
	}
}

function getRelativeTimeSeconds($diff, $short = false) {
	if ($diff<60) {
		return $diff . plural($diff, $short, 'second');
	}

	$diff = round($diff/60);

	if ($diff<60) {
		return $diff . plural($diff, $short, 'minute');
	}

	$diff = round($diff/60);
	
	if ($diff<24) {
		return $diff . plural($diff, $short, 'hour');
	}

	$diff = round($diff/24);

	if ($diff<7) {
		return $diff . plural($diff, $short, 'day');
	}
	
	$diff = round($diff/7);

	if ($diff<4) {
		return $diff . plural($diff, $short, 'week');
	}

	return '???';
}

function getNodes() {
	$sql = 'SELECT n.id, n.identifier, n.serviceType, n.lastUpdated, n.serviceCount, n.serviceType AS nodeType, n.instanceApplicationVersion FROM nodes n';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$nodes = $stmt->fetchAll();

	foreach ($nodes as &$itemNode) {
		$itemNode['lastUpdateRelative'] = getRelativeTime($itemNode['lastUpdated'], true);
		$itemNode['karma'] = 'UNKNOWN';

		$diff = time() - strtotime($itemNode['lastUpdated']);

		if ($diff > 1200) {
			$itemNode['karma'] = 'BAD';
		} else {
			$itemNode['karma'] = 'GOOD';
		}
	}

	return $nodes;
}


function isMobile() {
	if (isset($_REQUEST['mobile'])) {
		return true;
	}

	$ua = $_SERVER['HTTP_USER_AGENT'];

	if (strpos($ua, 'HTC') || strpos($ua, 'Android')) {
		return true;
	}

	return false;
}

function isJsonSubResultsValid($results) {
	foreach ($results as $result) {
		if (!is_array($result)) {
			return false;
		}
	}

	return true;
}

function parseOutputJson(&$service) {
	$pat = '#<json>(.+)</json>#ims';

	$matches = array();
	$res = preg_match($pat, $service['output'], $matches);


	if ($res) {
		$ret = preg_replace($pat, null, $service['output']);

		//$service['output'] = $service['output']; 
		$json = json_decode($matches[1], true);

		if (!empty($json['subresults']) && isJsonSubResultsValid($json['subresults'])) {
			$service['listSubresults'] = $json['subresults'];
				
			foreach ($service['listSubresults'] as $key => $result) {
				if (!isset($result['karma'])) {
					$service['listSubresults'][$key]['karma'] = $service['karma'];
				}

				// name
				if (isset($result['name'])) {
					$service['listSubresults'][$key]['name'] = san()->escapeStringForHtml($result['name']);
					continue;
				}	

				if (isset($result['subject'])) {
					$service['listSubresults'][$key]['name'] = san()->escapeStringForHtml($result['subject']);
					continue;
				}

				if (isset($result['title'])) {
					$service['listSubresults'][$key]['name'] = san()->escapeStringForHtml($result['title']);
				}

			}
		}

		if (isset($json['tasks'])) {
			$service['tasks'] = $json['tasks'];
		}

		if (isset($json['events'])) {
			$service['events'] = $json['events'];
		}

		$service['stabilityProbibility'] = rand(1, 100);
	}

}

function parseMetadata(&$service) {
	if (empty($service['metaActions'])) {
		return;
	}

	foreach (explode("\n", $service['metaActions']) as $line) {
		$comps = explode("=", $line, 2);
		
		if (count($comps) > 0) {
			$link = new stdClass;
			$link->url = $comps[1];
			$link->title = $comps[0];

			$service['listActions'][] = $link;
		}
	
	}
}

$now = time();

function invalidateOldServices(&$service) {
	global $now;
	
	$diff = $now - strtotime($service['lastUpdated']);

	if ($diff > intval(Session::getUser()->getData('oldServiceThreshold'))) {
		$service['karma'] = 'OLD';
		$service['output'] = "WARNING: This result of this service check was older than the user's preference threshold." . $service['output'];
	}
}

function parseAcceptableDowntime(&$service) {
	if (!empty($service['acceptableDowntime'])) {
		$downtime = explode("\n", trim($service['acceptableDowntime']));

		$dt = getFailedDowntimeRule($downtime);

		if ($dt != false && $service['karma'] != 'GOOD') {
			$service['karma'] = 'skipped';
			$service['output'] = '[DT:' . $dt . '] ' . $service['output'];
		}
	}
}

function getFailedDowntimeRule(array $downtime) {
	foreach ($downtime as $rule) {
		$literals = explode(' ', trim($rule));

		if (sizeof($literals) != 3) {
			continue;
		} else {
			$field = $literals[0];
			$operator = $literals[1];
			$value = $literals[2];

			if (is_numeric($value)) {
				$value = intval($value);
			}

			switch ($field) {
				case 'day':
					$lval = strtolower(date('D'));
					break;
				case 'hour':
					$lval = intval(date('G'));
					break;
				case 'week':
					$lval = intval(date('W'));
					break;
				default:
					continue;
			}

			switch ($operator) {
				case '>':
				case '>=':
				case '<':
				case '<=':
				case '==':
				case '!':
					$res = null;

					$expr = "\$res = '$lval' $operator '$value';";
					eval($expr);

					if ($res) {
						return $rule . '(' . $lval . ')';
					}

					break;
			}
		}
	}

	return false;
}

function getServicesBad() {
	$sql = 'SELECT s.id, s.identifier, m.icon, IF(m.criticalCast IS NULL OR s.karma != "GOOD", s.karma, m.criticalCast) AS karma, s.goodCount, s.output, s.description, s.executable, s.estimatedNextCheck, s.lastUpdated, IF(m.alias IS null, s.identifier, m.alias) AS alias, IF(m.acceptableDowntimeSla IS NULL, m.acceptableDowntime, sla.content) AS acceptableDowntime FROM services s LEFT JOIN service_metadata m ON s.identifier = m.service LEFT JOIN acceptable_downtime_sla sla ON m.acceptableDowntimeSla = sla.id WHERE s.karma != "GOOD"   ';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$problemServices = $stmt->fetchAll();
	$problemServices = enrichServices($problemServices);

	return $problemServices;
}

function getServices($groupId) {
	$sqlSubservices = 'SELECT DISTINCT m.id membershipId, md.actions AS metaActions, md.icon, IF(md.alias IS null, s.identifier, md.alias) AS alias, IF(md.acceptableDowntimeSla IS NULL, md.acceptableDowntime, sla.content) AS acceptableDowntime, s.id, s.lastUpdated, s.description, s.commandLine, s.output, s.karma, s.secondsRemaining, s.executable, s.goodCount, s.node, s.estimatedNextCheck FROM service_group_memberships m RIGHT JOIN services s ON m.service = s.identifier LEFT JOIN service_groups g ON m.`group` = g.title LEFT JOIN service_metadata md ON md.service = s.identifier LEFT JOIN acceptable_downtime_sla sla ON md.acceptableDowntimeSla = sla.id WHERE g.id = :groupId ORDER BY s.identifier';
	$stmt = DatabaseFactory::getInstance()->prepare($sqlSubservices);
	$stmt->bindValue(':groupId', $groupId);
	$stmt->execute();

	$listServices = $stmt->fetchAll();
	$listServices = enrichServices($listServices);

	return $listServices;
}

function castService(&$service) {
	echo 'yo';
	var_dump($service['castServiceCritical']);
}

function enrichServices($listServices, $parseOutput = true, $parseMetadata = true, $invalidateOldServices = true, $parseAcceptableDowntime = true, $castServices = false) {
	foreach ($listServices as $k => $itemService) {
		$listServices[$k]['stabilityProbibility'] = 0;
		$listServices[$k]['executableShort'] = str_replace(array('.pl', '.py', 'check_'), null, basename($listServices[$k]['executable']));
		$listServices[$k]['isOverdue'] = (time() - strtotime($itemService['estimatedNextCheck'])) > 0;
		$listServices[$k]['estimatedNextCheckRelative'] = getRelativeTime($itemService['estimatedNextCheck'], true);
		$listServices[$k]['listSubresults'] = array();
		$listServices[$k]['listActions'] = array();

		$parseAcceptableDowntime && parseAcceptableDowntime($listServices[$k]);
		$parseOutput && parseOutputJson($listServices[$k]);
		$parseMetadata && parseMetadata($listServices[$k]);
		$invalidateOldServices && invalidateOldServices($listServices[$k]);
		$castServices && castService($listServices[$k]);

		$listServices[$k]['output'] = htmlspecialchars($listServices[$k]['output']);
	}


	return $listServices;
}

function array2dFetchKey($array, $key) {
	$ret = array();

	foreach ($array as $item) {
		if (is_array($item) && isset($item[$key])) {
			$ret[] = $item[$key];
		}
	}

	return $ret;
}

function enrichGroups($listGroups, $subGroupDepth = 1) {
	foreach ($listGroups as &$itemGroup) {
		$itemGroup['listServices'] = getServices($itemGroup['id']);

		if ($subGroupDepth > 0) {
				$sql = 'SELECT g.* FROM service_groups g WHERE g.parent = :name';
				$stmt = DatabaseFactory::getInstance()->prepare($sql);
				$stmt->bindValue(':name', $itemGroup['title']);
				$stmt->execute();

				$itemGroup['listSubgroups'] = array();

				foreach ($stmt->fetchAll() as $itemSubgroup) {
					$itemSubgroup['listServices'] = getServices($itemSubgroup['title']);

					$itemGroup['listSubgroups'][] = $itemSubgroup;
				}
		}
	}

	return $listGroups;
}

function getGroups() {
	$sql = 'SELECT g.title AS name, g.* FROM service_groups g WHERE g.parent IS NULL or g.parent = "" ORDER BY g.title';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$listGroups = $stmt->fetchAll();
	$listGroups = enrichGroups($listGroups);

	return $listGroups;
}

function getGroup($id) {
	$sql = 'SELECT g.* FROM service_groups g WHERE g.id = :id LIMIT 1';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	$itemGroup = enrichGroups($stmt->fetchAll());

	return current($itemGroup);
}

function handleApiLogin() {
	if (isset($_REQUEST['login'])) {
		$sql = 'SELECT u.id, u.username, a.* FROM apiClients a LEFT JOIN users u ON a.user = u.id WHERE a.identifier = :identifier LIMIT 1';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':identifier', $_REQUEST['login']);
		$stmt->execute();


		if ($stmt->numRows() > 0) {
			$apiClient = $stmt->fetchRow();
			$username = $apiClient['username'];

			$user = \libAllure\User::getUser($username);
			$_SESSION['user'] = $user;
			$_SESSION['username'] = $username;
			$_SESSION['drawHeader'] = $apiClient['drawHeader'];
			$_SESSION['drawNavigation'] = $apiClient['drawNavigation'];
			$_SESSION['drawBigClock'] = $apiClient['drawBigClock'];
			$_SESSION['apiClient'] = $apiClient['identifier'];
			$_SESSION['apiClientRedirect'] = $apiClient['redirect'];


			redirectApiClients();
		}
	}  
}

function redirectApiClients() {
	if (isset($_SESSION['apiClientRedirect'])) {
			if (stripos($_SESSION['apiClientRedirect'], 'dashboard') !== false) {
				$dashboard = explode(':', $_SESSION['apiClientRedirect']);
	
				$url = 'viewDashboard.php?id=' . $dashboard[1];
				redirect($url, 'API Login complete. Redirecting to Dashboard.');
			}

			switch ($_SESSION['apiClientRedirect']) {
				case 'mobile':
					redirect('viewMobileStats.php', 'View Mobile Stats');
				case 'hud':
					redirect('viewServiceHud.php', 'API Login complete. Redirecting to Service HUD.');
				default:
					redirect($_SERVER['REQUEST_URI'], 'API login complete.');
			}
	}
}

function getServiceById($id) {
		$sql = 'SELECT s.id, s.description, s.identifier, s.commandLine, s.karma, s.node, s.output, s.lastUpdated, s.estimatedNextCheck, s.goodCount FROM services s WHERE s.id = :serviceId';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':serviceId', $id);
		$stmt->execute();

		if ($stmt->numRows() == 0) {
			throw new Exception("Service not found");
		}

		$service = $stmt->fetchRowNotNull();
		$service['estimatedNextCheckRelative'] = getRelativeTime($service['estimatedNextCheck'], true);
		$service['lastUpdatedRelative'] = getRelativeTime($service['lastUpdated'], true);

		return $service;
}

function getEvents() {
	$sql = 'SELECT s.id, s.identifier, s.output FROM services s JOIN service_metadata m ON m.service = s.identifier AND m.hasEvents = 1';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$events = array();
	$listEvents = $stmt->fetchAll();

	foreach ($listEvents as $itemServiceWithEvents) {
		parseOutputJson($itemServiceWithEvents);

		if (!empty($itemServiceWithEvents['events'])) {
			$events = array_merge_recursive($events, $itemServiceWithEvents['events']);
		}
	}

	return $events;
}

function getTasks() {
	$sql = 'SELECT s.id, s.identifier, s.output FROM services s JOIN service_metadata m ON m.service = s.identifier AND m.hasTasks = 1 ';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$listServices = $stmt->fetchAll();

	$tasks = array(
		'hihu' => array(),
		'hilu' => array(),
		'lihu' => array(),
		'lilu' => array()
	);

	foreach ($listServices as $itemService) {
		parseOutputJson($itemService);

		if (isset($itemService['tasks'])) {
			$tasks = array_merge_recursive($tasks, $itemService['tasks']);
		}
	}

	return $tasks;
}

function outputJson($content) {
	header('Content-Type: application/json');
	echo json_encode($content);
	exit;
}

function isApiPage() {
	return strpos($_SERVER['PHP_SELF'], 'json');
}

function denyApiAccess($message = 'API Access Forbidden. Did you authenticate?') {
	header('HTTP/1.0 403 Forbidden');
	header('Content-Type: application/json');

	outputJson($message);
}

function validateAcceptableDowntime($el) {
	$content = $el->getValue();
	$content = trim($content);

	if (empty($content)) {
		return;
	}

	$line = 0;
	foreach (explode("\n", $content) as $rule) {
		$line++;

		$literals = explode(' ', trim($rule));

		if (count($literals) != 3) {
			$el->setValidationError('Line ' . $line . ': 3 literals expected (field, operator, value). Found: ' . count($literals));
			return;
		}

		$field = $literals[0];
		$operator = $literals[1];
		$value = $literals[2];

		switch ($operator) {
			case '==':
			case '!':
			case '>':
			case '<':
			case '>=':
			case '<=':
				break;
			default:
				$el->setValidationError('Line ' . $line . ': Unknown operator: ' . $operator);
				return;
		}


		switch ($field) {
			case 'hour':
			case 'day':
			case 'week':
				break;
			default:
				$el->setValidationError('Line ' . $line . ': Unknown operator: ' . $field);
				return;
		}
	}
}

function deleteServiceByIdentifier($identifier) {
	$sql = 'DELETE FROM services WHERE identifier = :identifier';
	$stmt = stmt($sql);
	$stmt->bindValue(':identifier', $identifier);
	$stmt->execute();

	$sql = 'DELETE FROM service_group_memberships WHERE service = :serviceIdentifier';
	$stmt = stmt($sql);
	$stmt->bindValue(':serviceIdentifier', $identifier);
	$stmt->execute();

	$sql = 'DELETE FROM service_check_results WHERE service = :serviceIdentifier';
	$stmt = stmt($sql);
	$stmt->bindValue(':serviceIdentifier', $identifier);
	$stmt->execute();
}


function getWidgetInstance($id) {
	$sql = 'SELECT wi.dashboard FROM widget_instances wi WHERE wi.id = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	return $stmt->fetchRowNotNull();
}

function deleteWidgetInstance($id) {
	$widgetInstance = getWidgetInstance($id);

	$sql = 'DELETE FROM widget_instances WHERE id = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	return $widgetInstance;
}

function deleteGroupByName($name) {
	$sql = 'DELETE FROM service_group_memberships WHERE `group` = :groupTitle';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':groupTitle', $name);
	$stmt->execute();

	$sql = 'DELETE FROM groups WHERE name = :groupTitle';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':groupTitle', $name);
	$stmt->execute();
}

function deleteDashboardById($id) {
	$sql = 'DELETE FROM widget_instances WHERE dashboard = :id ';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	$sql = 'DELETE FROM dashboard WHERE id = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();
}

function getUsergroups() {
	$sql = 'SELECT g.id, g.title FROM groups g ORDER BY g.title ASC';
	$stmt = stmt($sql);
	$stmt->execute();

	return $stmt->fetchAll();
}

function getUserGroupById($id) {
	$sql = 'SELECT g.id, g.title FROM groups g WHERE g.id = :id LIMIT 1';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	return $stmt->fetchRowNotNull();
}

function addUserToGroup($userId, $groupId) {
	$sql = 'INSERT INTO group_memberships (`user`, `group`) VALUES (:user, :group)';
	$stmt = stmt($sql);
	$stmt->bindValue(':user', $userId);
	$stmt->bindValue(':group', $groupId);
	$stmt->execute();
}

function getUsersInGroupById($groupId) {
	$sql = 'SELECT u.id AS userId, u.username FROM users u LEFT JOIN group_memberships m ON m.user = u.id WHERE m.`group` = :id ';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $groupId);
	$stmt->execute();

	return $stmt->fetchAll();
}

function deleteUserGroupMembership($user, $group) {
	$sql = 'DELETE FROM group_memberships WHERE user = :user AND `group` = :group LIMIT 1';
	$stmt = stmt($sql);
	$stmt->bindValue(':user', $user);
	$stmt->bindValue(':group', $group);
	$stmt->execute();
}

function createUsergroup($title) {
	$sql = 'INSERT INTO groups (`title`) VALUES (:title)';
	$stmt = stmt($sql);
	$stmt->bindValue(':title', $title);
	$stmt->execute();

	return insertId();
}

function deleteUsergroupById($id) {
	$sql = 'DELETE FROM groups WHERE id = :id LIMIT 1';	
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();
}

function getServiceGroups() {
	$sql = 'SELECT g.id, g.title, p.id AS parentId, p.title AS parentName FROM service_groups g LEFT JOIN service_groups p ON g.parent = p.title';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	return $stmt->fetchAll();
}

function createGroup($title) {
	$sql = 'INSERT INTO service_groups (title) VALUES (:title)';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue('title', $title);
	$stmt->execute();

	return insertId();
}

function getRooms() {
	$sql = 'SELECT r.id, r.filename, r.title FROM rooms r';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':id', Sanitizer::getInstance()->filterUint('id'));
	$stmt->execute();
}

function getMaintPeriodById($id) {
	$sql = 'SELECT s.content, s.title FROM acceptable_downtime_sla s WHERE s.id = :id';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();
	$sla = $stmt->fetchRowNotNull();

	return $sla;
}

function deleteMaintPeriodById($id) {
	$sql = 'DELETE FROM acceptable_downtime_sla WHERE id = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	$sql = 'UPDATE service_metadata SET acceptableDowntimeSla = NULL WHERE acceptableDowntimeSla = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();
}

function setMaintPeriodContent($id, $content, $title) {
	$sql = 'UPDATE acceptable_downtime_sla SET content = :content, title = :title WHERE id = :id';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':content', $content);
	$stmt->bindValue(':title', $title);
	$stmt->bindValue(':id', $id);
	$stmt->execute();
}

function getServicesUngrouped() {
	$sql = 'SELECT s.estimatedNextCheck, s.secondsRemaining, s.description, s.id FROM services s WHERE s.description NOT IN (SELECT s2.description FROM service_group_memberships m INNER JOIN services s2 ON m.service = s2.identifier)';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$listServices = $stmt->fetchAll();

	return $listServices;
}

function getMembershipsFromServiceIdentifier($identifier) {
	$sql = 'SELECT m.id, m.`group`, g.id AS groupId, g.title AS groupName FROM service_group_memberships m INNER JOIN service_groups g ON m.group = g.title WHERE m.service = :service';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':service', $identifier);
	$stmt->execute();

	return $stmt->fetchAll();
}

function deleteServiceGroupMembershipById($id) {
	$sql = 'DELETE FROM service_group_memberships WHERE id = :id';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();
}

function getServiceGroupMembershipById($id) {
	$sql = 'SELECT m.*, s.id AS service FROM service_group_memberships m INNER JOIN services s ON m.service = s.identifier WHERE m.id = :id';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	return $stmt->fetchRowNotNull();
}

function setGroupPermissions($id, array $perms) {
	$sql = 'DELETE FROM privileges_g WHERE `group` = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	foreach ($perms as $perm) {
		$sql = 'SELECT p.id FROM permissions p WHERE p.`key` = :key LIMIT 1';
		$stmt = stmt($sql);
		$stmt->bindValue(':key', trim($perm));
		$stmt->execute();

		$permDb = $stmt->fetchRowNotNull();
	
		$sql = 'INSERT INTO privileges_g (`permission`, `group`) VALUES (:key, :group)';
		$stmt = stmt($sql);
		$stmt->bindValue(':key', $permDb['id']);
		$stmt->bindValue(':group', $id);
		$stmt->execute();

	}
}

function getSingleServiceMetric($service, $field) {
	$pat = '#<json>(.+)</json>#ims';

	if ($field == 'karma') {
			$metric = new stdClass;
			$metric->date = $service['date'];
			$metric->karma = $service['karma'];
			$metric->value = karmaToInt($service['karma']);

			return $metric;
		}

		$res = preg_match($pat, $service['output'], $matches);

		if ($res) {
			$ret = preg_replace($pat, null, $service['output']);

			$json = json_decode($matches[1]);

			if ($field == 'count') {
				$metric = new stdClass;
				$metric->date = $service['date'];
				$metric->karma = $service['karma'];
				$metric->value = count($json);

				return $metric;
			}

			if (!empty($json->metrics)) {
				foreach ($json->metrics as $metric) {
					if ($metric->name == $field) {
						$metric->value = $metric->value;
					} else {
						return;
					}
			
					$metric->date = $service['date'];
					$metric->karma = $service['karma'];

					return $metric;
				}
			}
		} else {
			$metric = extractNagiosMetric($service, $field);
/*
			$metric = new stdClass;
			$metric->date = $service['date'];
			$metric->karma = $service['karma'];
			$metric->value = '[NO OUTPUT]';
*/
			return $metric;
		}

}

function getServiceMetrics($results, $field) {
	$matches = array();
	$metrics = array();

	foreach ($results as $service) {
		$metric = getSingleServiceMetric($service, $field);

		if (!empty($metric)) {
			$metrics[] = $metric;
		}
	}
	

	foreach ($metrics as &$metric) {
		$metric->date = strtotime($metric->date);
	}

	return $metrics;
}

function getClassInstance($id) {
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
	$stmt->bindValue(':instanceId', $id);
	$stmt->execute();

	return $stmt->fetchRowNotNull();
}

function getInstanceRequirements($id) {
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

	$stmt = stmt($sql);
	$stmt->bindValue(':instanceId', $id);
	$stmt->execute();

	return $stmt->fetchAll();
}

function getImmediateClassInstances($id) {
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
WHERE ip.parent = :id
GROUP BY
	ci.id
SQL;

	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':id', $id);
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

function getImmediateChildrenClasses($id) {
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

	$sql = $sqlImmediateChildren;
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':nodeId', $id);
	$stmt->bindValue(':nodeIdOrig', $id);
	$stmt->execute();

	return $stmt->fetchall();
}

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

?>
