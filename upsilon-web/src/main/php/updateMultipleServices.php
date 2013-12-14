<?php

require_once 'includes/common.php';

use \libAllure\FormHandler;

class FormUpdateMultiple extends \libAllure\Form {
	public function __construct($source, $id, $services) {
		parent::__construct('formUpdateMultiple', 'Update Multiple');

		$this->addElement(new \libAllure\ElementHidden($source, '', $id));
		$this->addElement($this->getElementServices($services));
		$this->addElement($this->getElementSelectMaintPeriod());

		$this->addDefaultButtons();
	}

	public function getElementServices($services) {
		$el = new \libAllure\ElementSelect('serviceIds[]', 'Service IDs');
		$el->setSize(10);
		$el->multiple = true;

		foreach ($services as $s) {
			$el->addOption($s, $s);
		}

		return $el;		
	}

	private function getElementSelectMaintPeriod() {
		$el = new \libAllure\ElementSelect('acceptableDowntimeSla', 'Maint Period');
		$el->addOption('(none)', null);

		$sql = 'SELECT s.id, s.title FROM acceptable_downtime_sla s';
		$stmt = db()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $sla) {
			$el->addOption($sla['title'], $sla['id']);
		}

		return $el;
	}


	public function process() {
		$sql = 'INSERT IGNORE INTO service_metadata (service) VALUES (:service)';
		$stmt = db()->prepare($sql);

		foreach ($this->getElementValue('serviceIds[]') as $serviceId) {
			$stmt->bindValue(':service', $serviceId);
			$stmt->execute();
		}

		$sql = 'UPDATE service_metadata SET acceptableDowntimeSla = :acceptableDowntimeSla WHERE service = :service';
		$stmt = db()->prepare($sql);
		$stmt->bindValue(':acceptableDowntimeSla', $this->getElementValue('acceptableDowntimeSla'));

		foreach ($this->getElementValue('serviceIds[]') as $serviceId) {
			$stmt->bindValue(':service', $serviceId);
			$stmt->execute();
		}
	}
}

$serviceIds = array();
$sourceId = san()->filterString('group');

if (!empty($sourceId)) {
	$services = getServices(san()->filterUint('group'));
	$serviceIds = array();

	foreach ($services as $s) {
		$serviceIds[] = $s['description'];
	}
}

$fh = new FormHandler('FormUpdateMultiple');
$fh->setConstructorArgument(0, 'group');
$fh->setConstructorArgument(1, $sourceId);
$fh->setConstructorArgument(2, $serviceIds);
$fh->setRedirect('index.php');
$fh->handle();
?>
