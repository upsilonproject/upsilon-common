<?php

require_once 'includes/widgets/header.php';

use \libAllure\Form;
use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;
use \libAllure\ElementSelect;
use \libAllure\FormHandler;

class FormUpdateInstanceCoverage extends Form {
	public function __construct() {
		parent::__construct('update', 'Update instance coverage');

		$instId = Sanitizer::getInstance()->filterUint('instance');
		$inst = $this->getClassInstance($instId);

		$reqId = Sanitizer::getInstance()->filterUint('requirement');
		$req = $this->getRequirement($reqId);

		$this->addElementHidden('instance', Sanitizer::getInstance()->filterUint('instance'));
		$this->addElementReadOnly('Instance title', $inst['title']);

		$this->addElementHidden('requirement', Sanitizer::getInstance()->filterUint('requirement'));
		$this->addElementReadOnly('Requirement', $req['title']);
		$this->addElementSelectServiceCheck();
		$this->addDefaultButtons();
	}

	private function getClassInstance($id) { 
		$sql = 'SELECT i.* FROM class_instances i WHERE i.id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();
	
		return $stmt->fetchRow();
	}

	private function getRequirement($id) { 
		$sql = 'SELECT r.* FROM class_service_requirements r WHERE r.id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();
	
		return $stmt->fetchRow();
	}

	private function addElementSelectServiceCheck() {
		$el = new ElementSelect('service', 'Service');

		$sql = 'SELECT s.id, s.identifier, s.node FROM services s ORDER BY s.node ASC, s.identifier ASC';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $itemService) {
			$el->addOption($itemService['identifier'], $itemService['id'], $itemService['node']);
		}

		$sql = 'SELECT a.service FROM class_service_assignments a WHERE instance = :instance AND requirement = :requirement';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':instance', Sanitizer::getInstance()->filterUint('instance'));
		$stmt->bindValue(':requirement', Sanitizer::getInstance()->filterUint('requirement'));
		$stmt->execute();

		if ($stmt->numRows() > 0) {
			$el->setValue($stmt->fetchRowNotNull()['service']);
		}

		$this->addElement($el);
	}

	public function process() {
		$sql = 'DELETE FROM class_service_assignments WHERE instance = :instance AND service = :service AND requirement = :requirement ';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':instance', $this->getElementValue('instance'));
		$stmt->bindValue(':requirement', $this->getElementValue('requirement'));
		$stmt->bindValue(':service', $this->getElementValue('service'));
		$stmt->execute();

		$sql = 'INSERT INTO class_service_assignments(instance, requirement, service) values (:instance, :requirement, :service)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':instance', $this->getElementValue('instance'));
		$stmt->bindValue(':requirement', $this->getElementValue('requirement'));
		$stmt->bindValue(':service', $this->getElementValue('service'));
		$stmt->execute();
	}
}

$instId = Sanitizer::getInstance()->filterUint('instance');
$fh = new FormHandler('FormUpdateInstanceCoverage');
$fh->setRedirect('viewClassInstance.php?id=' . $instId);
$fh->handle();

require_once 'includes/widgets/footer.php';

?>
