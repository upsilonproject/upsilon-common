<?php

require_once 'includes/common.php';

use \libAllure\FormHandler;

class FormUpdateMultiple extends \libAllure\Form {
	public function __construct($source, $id, $services) {
		parent::__construct('formUpdateMultiple', 'Update Multiple');

		$this->addElement(new \libAllure\ElementHidden($source, '', $id));
		$this->addElement($this->getElementServices($services));

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

	public function process() {
		$sql = 'UPDATE service_metadata SET acceptableDowntimeSla = :acceptableDowntimeSla';


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
