<?php

require_once 'includes/common.php';

$id = san()->filterUint('id');

$links = linksCollection();
$links->add('deleteMaintPeriod.php?id=' . $id, 'Delete');

use \libAllure\Form;
use \libAllure\DatabaseFactory;
use \libAllure\ElementTextbox;
use \libAllure\FormHandler;
use \libAllure\ElementInput;
	
class FormUpdateMaintPeriod extends Form {
	public function __construct($id) {
		parent::__construct('updateMaintPeriod', 'Update Maint Period');
		$this->addElementReadOnly('ID', $id, 'id');

		$sla = getMaintPeriodById($id);
		$this->addElement(new ElementInput('title', 'Title', $sla['title']));
		$this->addElement(new ElementTextbox('content', 'Content', $sla['content'], 'Current Week:' . date('W') . '. the <a href = "http://upsilon-project.co.uk/site/index.php/Maintenance_Periods">syntax used for maintenence window content</a> is described in the manual. '));

		$this->addDefaultButtons();
	}

	public function validateExtended() {
		validateAcceptableDowntime($this->getElement('content'));
	}

	public function process() {
		setMaintPeriodContent($this->getElementValue('id'), $this->getElementValue('content'), $this->getElementValue('title'));
		redirect('listMaintPeriods.php');
	}
}

$fh = new FormHandler('FormUpdateMaintPeriod');
$fh->setConstructorArgument(0, $id);
$fh->handle();

?>
