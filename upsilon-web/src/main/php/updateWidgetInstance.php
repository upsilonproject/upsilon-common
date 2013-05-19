<?php

require_once 'includes/common.php';
require_once 'includes/classes/Widget.php';

use \libAllure\Form;
use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;
use \libAllure\FormHandler;
use \libAllure\ElementHidden;

class FormUpdateWidgetInstance extends Form {
	public function __construct() {
		parent::__construct('updateWidgetInstance', 'Update Widget Instance');

		$id = Sanitizer::getInstance()->filterUint('id');

		$this->addElement(new ElementHidden('id', 'ID', $id));
		$this->getWidgetInstance($id);

		$this->addElementsWidgetOptions($this->widgetInstance);

		$this->addDefaultButtons();
	}

	private function addElementsWidgetOptions() {
		foreach ($this->widgetInstance->getArguments() as $name => $value) {
			$el = $this->widgetInstance->getArgumentFormElement($name);
			$el->setValue($this->widgetInstance->getArgumentValue($name));

			$this->addElement($el);
		}
	}

	private function getWidgetInstance($id) {
		$sql = 'SELECT wi.*, w.class FROM widget_instances wi LEFT JOIN widgets w ON wi.widget = w.id WHERE wi.id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		$rowWidgetInstance = $stmt->fetchRowNotNull();

		include_once 'includes/classes/Widget' . $rowWidgetInstance['class'] . '.php';
		$this->widgetInstance = 'Widget' . $rowWidgetInstance['class'];
		$this->widgetInstance = new $this->widgetInstance();
		$this->widgetInstance->loadArguments($rowWidgetInstance['id']);

	}

	public function process() {
		$sql = 'INSERT INTO widget_instance_arguments (instance, name, value) VALUES (:instance, :name, :value) ON DUPLICATE KEY UPDATE value = :value2';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':instance', $this->getElementValue('id'));

		foreach ($this->widgetInstance->getArguments() as $name => $oldValue) {
			$stmt->bindValue(':name', $name);
			$stmt->bindValue(':value', $this->getElementValue($name));
			$stmt->bindValue(':value2', $this->getElementValue($name));
			$stmt->execute();
		}
	}
}


$fh = new FormHandler('FormUpdateWidgetInstance');
$fh->setRedirect('viewDashboard.php');
$fh->handle();

?>
