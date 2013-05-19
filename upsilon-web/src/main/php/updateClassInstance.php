<?php

require_once 'includes/widgets/header.php';

require_once 'libAllure/FormHandler.php';

use \libAllure\Form;
use \libAllure\DatabaseFactory;
use \libAllure\FormHandler;
use \libAllure\Sanitizer;
use \libAllure\ElementInput;
use \libAllure\ElementSelect;

class FormUpdateClassInstance extends \libAllure\Form {
	public function __construct() {
		parent::__construct('updateClassInstance', 'Update class instance');

		$id = Sanitizer::getInstance()->filterUint('instance');
		$this->addElementHidden('instance', $id);

		$sql = 'SELECT c.title FROM class_instances c WHERE c.id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		$this->addElement($this->getElementParents($id));

		$current = $stmt->fetchRowNotNull();

		$this->addElement(new ElementInput('title', 'Title', $current['title']));

		$this->addDefaultButtons();
	}

	public function getElementParents($classId) {
		$el = new ElementSelect('parents[]', 'Parents');
		$el->setSize(5);
		$el->multiple = true;

		$sql = 'SELECT c.id, c.title FROM classes c';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $class) {
			$el->addOption($class['title'], $class['id']);
		}

		$sql = 'SELECT p.parent AS parentClassId FROM class_instance_parents p WHERE instance = :instanceId ';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':instanceId', $classId);
		$stmt->execute();

		$values = array();
		foreach ($stmt->fetchAll() as $value) {
			$values[] = $value['parentClassId'];
		}
		$el->setValue($values);

		return $el;
	}

	public function process() {
		$sql = 'UPDATE class_instances SET title = :title WHERE id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $this->getElementValue('instance'));
		$stmt->bindValue(':title', $this->getElementValue('title'));
		$stmt->execute();

		$sql = 'DELETE FROM class_instance_parents WHERE instance = :instance ';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':instance', $this->getElementValue('instance'));
		$stmt->execute();

		$sql = 'INSERT INTO class_instance_parents (instance, parent) VALUES (:instance, :parent) ';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		
		foreach ($this->getElementValue('parents[]') as $parentId) {
			$stmt->bindValue(':parent', $parentId);
			$stmt->bindValue(':instance', $this->getElementValue('instance'));
			$stmt->execute();
		}
	}
}

$h = new FormHandler('FormUpdateClassInstance');
$h->setRedirect('viewClassInstance.php?id=' . Sanitizer::getInstance()->filterUint('instance'));
$h->handle();

require_once 'includes/widgets/footer.php';

?>
