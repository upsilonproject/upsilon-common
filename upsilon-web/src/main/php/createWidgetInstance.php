<?php

$title = 'Create widget instance';
require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;
use \libAllure\ElementHidden;
use \libAllure\ElementSelect;

class FormCreateWidgetInstance extends Form {
	public function __construct() {
		parent::__construct('createWidgetInstance', 'Create Widget Instance');

		$this->id = Sanitizer::getInstance()->filterUint('dashboard');

		$this->addElement(new ElementHidden('dashboard', 'Dashboard', $this->id));
		$this->addelement($this->getElementClass());

		$this->addDefaultButtons();
	}

	private function getElementClass() {
		$el = new ElementSelect('class', 'Class');

		$sql = 'SELECT w.* FROM widgets w ';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $widget) {
			$el->addOption($widget['class'], $widget['id']);
		}

		return $el;
	}

	public function process() {
		$sql = 'INSERT INTO widget_instances (widget, dashboard) VALUES (:class, :dashboard)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':class', $this->getElementValue('class'));
		$stmt->bindValue(':dashboard', $this->getElementValue('dashboard'));
		$stmt->execute();
	}
}

$fh = new FormHandler('FormCreateWidgetInstance');
$fh->constructForm();
$fh->setRedirect('viewDashboard.php?id=' . $fh->getForm()->id);
$fh->handle();

?>
