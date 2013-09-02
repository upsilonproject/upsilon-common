<?php

require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\DatabaseFactory;
use \libAllure\ElementTextbox;
use \libAllure\FormHandler;
	
class FormUpdateSla extends Form {
	public function __construct($id) {
		$this->addElementReadOnly('ID', $id, 'id');

		$sla = getSlaById($id);
		$this->addElement(new ElementTextbox('content', 'Content', $sla['content'], 'Current Week:' . date('W')));

		$this->addDefaultButtons();
	}

	public function validateExtended() {
		validateAcceptableDowntime($this->getElement('content'));
	}

	public function process() {
		setSlaContent($this->getElementValue('id'), $this->getElementValue('content'));
		redirect('listSlas.php');
	}
}

$fh = new FormHandler('FormUpdateSla');
$fh->setConstructorArgument(0, san()->filterUint('id'));
$fh->handle();

?>
