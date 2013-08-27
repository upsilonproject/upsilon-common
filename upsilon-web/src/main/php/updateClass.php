<?php

require_once 'includes/common.php';

use \libAllure\FormHandler;
use \libAllure\Form;
use \libAllure\ElementInput;

class FormUpdateClass extends Form {
	public function __construct($id) {
		$this->class = $this->getClass($id);

		$this->addElementHidden('id', $id);
		$this->addElement(new ElementInput('title', 'Title', $this->class['title']));

		$this->addDefaultButtons();
	}

	private function getClass($id) {
		$sql = 'SELECT c.id, c.title FROM classes c WHERE c.id = :id';
		$stmt = stmt($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		return $stmt->fetchRowNotNull();
	}

	public function process() {
		$sql = 'UPDATE classes SET title = :title WHERE id = :id';
		$stmt = stmt($sql);
		$stmt->bindValue(':title', $this->getElementValue('title'));
		$stmt->bindValue(':id', $this->getElementValue('id'));
		$stmt->execute();
	}
}

$fh = new FormHandler('FormUpdateClass');
$fh->setConstructorArgument(0, san()->filterUint('id'));
$fh->setRedirect('listClasses.php');
$fh->handle();

?>
