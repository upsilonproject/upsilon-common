<?php

require_once 'includes/common.php';
require_once 'includes/classes/Dashboard.php';

use \libAllure\FormHandler;
use \libAllure\Form;
use \libAllure\ElementInput;
use \libAllure\ElementCheckbox;

class FormUpdateDashboard extends Form {
	public function __construct($id) {
		$this->dashboard = new Dashboard($id);

		$this->addElementHidden('id', $id);
		$this->addElement(new ElementInput('title', 'Title', $this->dashboard->getTitle()));
		$this->addelement(new ElementCheckbox('serviceGrouping', 'Service Grouping', $this->dashboard->isServicesGrouped()));

		$this->addDefaultButtons();
	}

	public function process() {
		$sql = 'UPDATE dashboard SET title = :title, servicesGrouped = :servicesGrouped WHERE id = :id';
		$stmt = stmt($sql);
		$stmt->bindValue(':id', $this->dashboard->getId());
		$stmt->bindValue(':title', $this->getElementValue('title'));
		$stmt->bindValue(':servicesGrouped', $this->getElementValue('serviceGrouping'));
		$stmt->execute();
		var_dump('execd');
	}
}

$id = san()->filterUint('id');

$f = new FormHandler('FormUpdateDashboard');
$f->setConstructorArgument(0, $id);
$f->setRedirect('viewDashboard.php?id=' . $id);
$f->handle();

?>
