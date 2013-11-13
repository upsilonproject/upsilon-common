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
		$this->addElement(new ElementInput('title', 'Title', $this->itemGroup['title']));
		$this->addElement($this->getGroupSelectionElement($this->itemGroup['parent'], $this->itemGroup['id']));
		$this->getElement('parent')->setValue($this->itemGroup['parent']);

		$this->addDefaultButtons();
	}

	private function getGroupSelectionElement($current, $self) {
		$sql = 'SELECT g.title FROM service_groups g WHERE g.id != :gid ORDER BY g.title ASC';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':gid', $self);
		$stmt->execute();

		$el = new ElementSelect('parent', 'Parent');
		$el->addOption('None', '');

		foreach ($stmt->fetchAll() as $itemGroup) {
			$el->addOption($itemGroup['title'], $itemGroup['title']);
		}

		$el->setValue($current);

		return $el;
	}
	
	public function process() {
		$sql = 'UPDATE service_group_memberships SET `group` = :new_title WHERE `group` = :title ';
		$stmt = stmt($sql);
		$stmt->bindValue(':title', $this->itemGroup['title']);
		$stmt->bindValue(':new_title', $this->getElementValue('title'));
		$stmt->execute();
		
		$sql = 'UPDATE service_groups SET title = :title, parent = :parent WHERE id = :id';
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
