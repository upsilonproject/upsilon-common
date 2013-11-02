<?php

$title = 'Create class';
require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\ElementInput;
use \libAllure\DatabaseFactory;
use \libAllure\ElementSelect;

class FormCreateClass extends Form {
	public function __construct($parent) {
		$this->addElement(new ElementInput('title', 'Title'));

		$this->addElement($this->getElementParent($parent));
		$this->addDefaultButtons();
	}

	public function getElementParent($parent) {
		$el = new ElementSelect('class', 'Parent class');

		$sql = 'SELECT c.id, c.title, c.l, c.r FROM classes c ORDER BY c.title ASC';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		$this->classes = $stmt->fetchAll();

		foreach ($this->classes as $class) {
			$el->addOption($class['title'], $class['id']);
		}

		$el->setValue($parent);

		return $el;
	}

	public function process() {
		$parentr = 0;

		foreach ($this->classes as $class) {
			if ($class['id'] == $this->getElementValue('class')) {
				$parentr = $class['r']; break;
			}
		}

		$sql = 'UPDATE classes SET r = r + 2 WHERE r >= :parentr';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':parentr', $parentr);
		$stmt->execute();

		$sql = 'INSERT INTO classes (title, l, r) VALUES (:title, :l, :r)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':title', $this->getElementValue('title'));
		$stmt->bindValue(':l', $parentr);
		$stmt->bindValue(':r', $parentr + 1);
		$stmt->execute();
	}
}

$fh = new FormHandler('FormCreateClass');
$fh->setRedirect('listClasses.php');

if (isset($_REQUEST['parent'])) {
	$fh->setConstructorArgument(0, $_REQUEST['parent']);
	$fh->setRedirect('listClasses.php?id=' . $_REQUEST['parent']);
}

$fh->handle();
?>
