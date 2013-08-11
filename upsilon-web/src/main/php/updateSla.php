<?php

require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\DatabaseFactory;
use \libAllure\ElementTextbox;
use \libAllure\FormHandler;
	
class FormUpdateSla extends Form {
	public function __construct($id) {
		$this->addElementReadOnly('ID', $id, 'id');

		$sql = 'SELECT s.content FROM acceptable_downtime_sla s WHERE s.id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();
		$sla = $stmt->fetchRow();

		$this->addElement(new ElementTextbox('content', 'Content', $sla['content'], 'Current Week:' . date('W')));

		$this->addDefaultButtons();
	}

	public function validateExtended() {
		validateAcceptableDowntime($this->getElement('content'));
	}

	public function process() {
		$sql = 'UPDATE acceptable_downtime_sla SET content = :content WHERE id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':content', $this->getElementValue('content'));
		$stmt->bindValue(':id', $this->getElementValue('id'));
		$stmt->execute();

		redirect('listSlas.php');
	}
}

$fh = new FormHandler('FormUpdateSla');
$fh->setConstructorArgument(0, san()->filterUint('id'));
$fh->handle();

?>
