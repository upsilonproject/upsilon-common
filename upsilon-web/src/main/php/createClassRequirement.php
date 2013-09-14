<?php

$title = 'Create class requirement';
require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\ElementInput;
use \libAllure\DatabaseFactory;
use \libAllure\Sanitizer;
use \libAllure\FormHandler;

class FormCreateClassRequirement extends Form {
	public function __construct($id) {
		parent::__construct('formCreateClassRequirement', 'Create Class Requirement');

		$this->addElementReadOnly('Class', $id, 'id');

		$this->addElement(new ElementInput('title', 'Title', null));

		$this->addDefaultButtons();
	}

	public function process() {
		$sql = 'INSERT INTO class_service_requirements (class, title) VALUES (:class, :title)';			
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':class', $this->getElementValue('id'));
		$stmt->bindValue(':title', $this->getElementValue('title'));
		$stmt->execute();
	}
}

$id = Sanitizer::getInstance()->filterUint('id');
$fh = new FormHandler('FormCreateClassRequirement');
$fh->setConstructorArgument(0, $id);
$fh->setRedirect('listClasses.php?id=' . $id);
$fh->handle();

?>
