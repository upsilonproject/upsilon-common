<?php

require_once 'includes/widgets/header.php';

use \libAllure\Form;
use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;
use \libAllure\ElementSelect;

class FormUpdateInstanceCoverage extends Form {
	public function __construct() {
		parent::__construct('update', 'Update instance coverage');

		$this->addElementReadOnly('Instance', Sanitizer::getInstance()->filterUint('instance'), 'instance');
		$this->addElementReadOnly('Requirement', Sanitizer::getInstance()->filterUint('requirement'), 'requirement');
		$this->addElementSelectServiceCheck();
		$this->addDefaultButtons();
	}

	private function addElementSelectServiceCheck() {
		$el = new ElementSelect('service', 'Service');

		$sql = 'SELECT s.id, s.identifier FROM services s';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $itemService) {
			$el->addOption($itemService['identifier'], $itemService['id']);
		}

		$this->addElement($el);
	}

	public function process() {
		$sql = 'INSERT INTO class_service_assignments(instance, requirement, service) values (:instance, :requirement, :service)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':instance', $this->getElementValue('instance'));
		$stmt->bindValue(':requirement', $this->getElementValue('requirement'));
		$stmt->bindValue(':service', $this->getElementValue('service'));
		$stmt->execute();
	}
}

$f = new FormUpdateInstanceCoverage();

if ($f->validate()) {
	$f->process();

	echo 'Updated';
}

$tpl->displayForm($f);

require_once 'includes/widgets/footer.php';

?>
