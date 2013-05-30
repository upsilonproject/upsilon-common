<?php

$title = 'Create class instance';
require_once 'includes/widgets/header.php';

use \libAllure\Form;
use \libAllure\ElementInput;
use \libAllure\ElementSelect;
use \libAllure\DatabaseFactory;

class FormCreateClassInstance extends Form {
	public function __construct() {
		parent::__construct('createClassInstance', 'Create class instance');

		$this->addElement($this->getElementClasses());
		$this->addElement(new ElementInput('title', 'Title'));
		$this->addDefaultButtons();
	}

	private function getElementClasses() {
		$el = new ElementSelect('class', 'Classes');
		
		$sql = 'SELECT c.id, c.title FROM classes c';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $itemClass) {
			$el->addOption($itemClass['title'], $itemClass['id']);
		}

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

$f = new FormCreateClassInstance();

if ($f->validate()) {
	$f->process();

	redirect('listClasses.php', 'Class created');
}

$tpl->displayForm($f);

require_once 'includes/widgets/footer.php';

?>
