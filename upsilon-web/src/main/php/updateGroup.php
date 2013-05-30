<?php

$title = 'Update group';
require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\ElementInput;
use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;
use \libAllure\ElementSelect;

class FormUpdateGroup extends \libAllure\Form {
	public function __construct($id) {
		parent::__construct('formUpdateGroup', 'Update Group');

		$this->itemGroup = getGroup($id);

		$this->addElementReadOnly('ID', $id, 'id');
		$this->addElement(new ElementInput('title', 'Title', $this->itemGroup['name']));
		$this->addElement($this->getGroupSelectionElement());
		$this->getElement('parent')->setValue($this->itemGroup['parent']);

		$this->addDefaultButtons();
	}

	private function getGroupSelectionElement() {
		$sql = 'SELECT g.* FROM groups g';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		$el = new ElementSelect('parent', 'Parent');
		$el->addOption('None', '');

		foreach ($stmt->fetchAll() as $itemGroup) {
			$el->addOption($itemGroup['name'], $itemGroup['name']);
		}

		return $el;
	}

	public function process() {
		$sql = 'UPDATE groups SET name = :title, parent = :parent WHERE id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);

		$stmt->bindValue(':id', $this->itemGroup['id']);
		$stmt->bindValue(':title', $this->getElementValue('title'));
		$stmt->bindValue(':parent', $this->getElementValue('parent'));
		$stmt->execute();
	}
}

$fh = new FormHandler('FormUpdateGroup');
$fh->setConstructorArgument(0, Sanitizer::getInstance()->filterUint('id'));
$fh->setRedirect('viewGroup.php?id=' . Sanitizer::getInstance()->filterUint('id'));
$fh->handle();

?>
