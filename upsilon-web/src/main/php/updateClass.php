<?php

require_once 'includes/common.php';

use \libAllure\FormHandler;
use \libAllure\Form;
use \libAllure\ElementInput;

class FormUpdateClass extends Form {
	public function __construct($id) {
		parent::__construct('updateClass', 'Update class');

		$this->class = $this->getClass($id);

		$this->addElementHidden('id', $id);
		$this->addElement(new ElementInput('title', 'Title', $this->class['title']));
		$this->addElement(getElementServiceIcon($this->class['icon']));

		$this->addDefaultButtons();
	}

	private function getClass($id) {
		$sql = 'SELECT c.id, c.title, c.icon FROM classes c WHERE c.id = :id';
		$stmt = stmt($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		return $stmt->fetchRowNotNull();
	}

	public function process() {
		$sql = 'UPDATE classes SET title = :title, icon = :icon WHERE id = :id';
		$stmt = stmt($sql);
		$stmt->bindValue(':title', $this->getElementValue('title'));
		$stmt->bindValue(':icon', $this->getElementValue('icon'));
		$stmt->bindValue(':id', $this->getElementValue('id'));
		$stmt->execute();
	}
}

$id = san()->filterUint('id');
$fh = new FormHandler('FormUpdateClass');
$fh->setConstructorArgument(0, $id);
$fh->setRedirect('listClasses.php?id=' . $id);
$fh->handle();

?>
