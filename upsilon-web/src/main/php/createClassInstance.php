<?php

$title = 'Create class instance';
require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\ElementInput;
use \libAllure\ElementSelect;
use \libAllure\DatabaseFactory;
use \libAllure\FormHandler;

class FormCreateClassInstance extends Form {
	public function __construct($parent) {
		parent::__construct('createClassInstance', 'Create class instance');

		$this->addElement($this->getElementClasses($parent));
		$this->addElement(new ElementInput('title', 'Title'));
		$this->addDefaultButtons();
	}

	private function getElementClasses($parent) {
		$el = new ElementSelect('class', 'First class');
		
		$sql = 'SELECT c.id, c.title FROM classes c';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $itemClass) {
			$el->addOption($itemClass['title'], $itemClass['id']);
		}

		$el->setValue($parent);

		return $el;
	}

	public function process() {
		$sql = 'INSERT INTO class_instances (title) VALUES (:title)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':title', $this->getElementValue('title'));
		$stmt->execute();

		$instanceId = DatabaseFactory::getInstance()->lastInsertId();

		$sql = 'INSERT INTO class_instance_parents (instance, parent) VALUES (:instance, :parent)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':instance', $instanceId);
		$stmt->bindValue(':parent', $this->getElementValue('class'));
		$stmt->execute();
	}
}

$fh = new FormHandler('FormCreateClassInstance');

if (isset($_REQUEST['parent'])) {
	$fh->setConstructorArgument(0, $_REQUEST['parent']);
}

$fh->setRedirect('listClasses.php');
$fh->handle();


?>
