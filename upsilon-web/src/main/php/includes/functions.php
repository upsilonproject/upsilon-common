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

function parseOutputJson(&$service) {
	$pat = '#<json>(.+)</json>#ims';

	$matches = array();
	$res = preg_match($pat, $service['output'], $matches);


	if ($res) {
		$ret = preg_replace($pat, null, $service['output']);

		//$service['output'] = $service['output']; 
		$json = json_decode($matches[1], true);

		if (!empty($json['subresults'])) {
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
	$sql = 'SELECT s.id, s.identifier, IF(m.criticalCast IS NULL OR s.karma != "GOOD", s.karma, m.criticalCast) AS karma, s.goodCount, s.output, s.description, s.executable, s.estimatedNextCheck, s.lastUpdated, m.alias, IF(m.acceptableDowntimeSla IS NULL, m.acceptableDowntime, sla.content) AS acceptableDowntime FROM services s LEFT JOIN service_metadata m ON s.identifier = m.service LEFT JOIN acceptable_downtime_sla sla ON m.acceptableDowntimeSla = sla.id WHERE s.karma != "GOOD"   ';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$problemServices = $stmt->fetchAll();
	$problemServices = enrichServices($problemServices);

	return $problemServices;
}

function getServices($groupName) {
	$sqlSubservices = 'SELECT DISTINCT m.id membershipId, md.actions AS metaActions, md.icon, md.alias, IF(md.acceptableDowntimeSla IS NULL, md.acceptableDowntime, sla.content) AS acceptableDowntime, s.id, s.lastUpdated, s.description, s.commandLine, s.output, s.karma, s.secondsRemaining, s.executable, s.goodCount, s.node, s.estimatedNextCheck FROM group_memberships m RIGHT JOIN services s ON m.service = s.identifier LEFT JOIN groups g ON m.`group` = g.name LEFT JOIN service_metadata md ON md.service = s.identifier LEFT JOIN acceptable_downtime_sla sla ON md.acceptableDowntimeSla = sla.id WHERE g.name = :groupName ORDER BY s.identifier';
	$stmt = DatabaseFactory::getInstance()->prepare($sqlSubservices);
	$stmt->bindValue(':groupName', $groupName);
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
		$itemGroup['listServices'] = getServices($itemGroup['name']);

		if ($subGroupDepth > 0) {
				$sql = 'SELECT g.* FROM groups g WHERE g.parent = :name';
				$stmt = DatabaseFactory::getInstance()->prepare($sql);
				$stmt->bindValue(':name', $itemGroup['name']);
				$stmt->execute();

				$itemGroup['listSubgroups'] = array();

				foreach ($stmt->fetchAll() as $itemSubgroup) {
					$itemSubgroup['listServices'] = getServices($itemSubgroup['name']);

					$itemGroup['listSubgroups'][] = $itemSubgroup;
				}
		}
	}

	return $listGroups;
}

function getGroups() {
	$sql = 'SELECT g.* FROM groups g WHERE g.parent IS NULL or g.parent = "" ORDER BY g.name';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->execute();

	$listGroups = $stmt->fetchAll();
	$listGroups = enrichGroups($listGroups);

	return $listGroups;
}

function getGroup($id) {
	$sql = 'SELECT g.* FROM groups g WHERE g.id = :id LIMIT 1';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();

	$itemGroup = enrichGroups($stmt->fetchAll());

	return $itemGroup[0];
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
			switch ($_SESSION['apiClientRedirect']) {
				case 'mobile':
					redirect('viewMobileStats.php', 'View Mobile Stats');
				case 'dashboard':
					redirect('viewDashboard.php', 'API Login complete. Redirecting to Dashboard.');
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

function denyApiAccess() {
	header('HTTP/1.0 403 Forbidden');
	outputJson("API Access Forbidden. Did you authenticate?");
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

?>
