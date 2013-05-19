<?php

require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;
use \liballure\ElementSelect;

class FormAddMembership extends Form {
	public function __construct() {
		parent::__construct('formAddMembership', 'Form Add Membership');

		$id = Sanitizer::getInstance()->filterUint('serviceId');

		$this->service = $this->getService($id);

		$this->addElementReadOnly('Service Id', $id, 'serviceId');
		$this->addElementReadOnly('Service Identifier', $this->service['identifier']);
		$this->addElementGroupSelect();
		
		$this->addDefaultButtons();
	}

	private function addElementGroupSelect() {
		$el = new ElementSelect('group', 'Group');
		
		$sql = 'SELECT g.id, g.name FROM groups g';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $group) {
			$el->addOption($group['name'], $group['name']);
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
		$sql = 'INSERT INTO group_memberships (`group`, service) VALUES (:group, :service)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':group', $this->getElementValue('group'));
		$stmt->bindValue(':service', $this->service['identifier']);
		$stmt->execute();
	}

	public function getServiceId() {
		if (!empty($this->service['id'])) {
			return $this->service['id'];
		} else {
			return null;
		}
	}
}

$f = new FormAddMembership();
$fh = new FormHandler($f);
$fh->setRedirect('viewService.php?id=' . $f->getServiceId(), 'Mumbership Added');
$fh->handle();

?>
