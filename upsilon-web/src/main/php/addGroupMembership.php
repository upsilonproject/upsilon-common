<?php

$title = 'Add group membership';
require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;
use \libAllure\ElementSelect;
use \libAllure\ElementHidden;

class FormAddMembership extends Form {
	public function __construct() {
		parent::__construct('formAddMembership', 'Form Add Membership');

		$this->addElement($this->getElementService());
		$this->addElementGroupSelect();
		
		$this->addDefaultButtons();
	}

	private function getElementService() {
		$sql = 'SELECT s.id, s.identifier, count(m.id) AS groups, s.node FROM services s LEFT JOIN service_group_memberships m ON m.`service` = s.identifier GROUP BY s.id ORDER BY node ASC, groups DESC, s.identifier ASC';
		$stmt = db()->prepare($sql);
		$stmt->execute();

		$el = new ElementSelect('serviceId[]', 'Service');
		$el->setSize(10);
		$el->multiple = true;

		foreach ($stmt->fetchall() as $service) {
			$el->addOption($service['node'] . '::' . $service['identifier'] . ' (' . $service['groups'] . ' groups)', $service['identifier']);
		}

		return $el;
		
	}

	private function addElementGroupSelect() {
		$el = new ElementSelect('group', 'Group');
		
		$sql = 'SELECT g.id, g.title FROM service_groups g';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $group) {
			$el->addOption($group['title'], $group['title']);
		}

		$group = san()->filterString('group');
		if (!empty($group)) {
			$el->setValue($group);
		}

		$this->addElement($el);
	}

	private function getService($id) {
		$sql = 'SELECT s.id, s.identifier FROM services s WHERE s.id = :sid';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':sid', $id);
		$stmt->execute();

		return $stmt->fetchRowNotNull();
	}

	public function process() {
		$sql = 'INSERT INTO service_group_memberships (`group`, service) VALUES (:group, :service)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':group', $this->getElementValue('group'));

		$services = $this->getElementValue('serviceId[]');

		foreach ($services as $service) {
			$stmt->bindValue(':service', $service);
			$stmt->execute();
		}

		$sql = 'SELECT g.id FROM service_groups g WHERE g.title = :title';
		$stmt = stmt($sql);
		$stmt->bindValue(':title', $this->getElementValue('group'));
		$stmt->execute();
		$group = $stmt->fetchRowNotNull();

		// hack
		redirect('viewGroup.php?id=' . $group['id'], 'Membership Added');
	}

	public function getServiceId() {
		if (!empty($this->service['id'])) {
			return $this->service['id'];
		} else {
			return null;
		}
	}
}

$groups = getGroup();
if (empty($groups)) {
	redirect('listGroups.php', 'You need to create some groups.');
}

$f = new FormAddMembership();
$fh = new FormHandler($f);
$fh->handle();

?>
