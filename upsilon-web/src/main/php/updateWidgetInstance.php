<?php

$title = 'Update widget instance';
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

		$this->getWidgetInstance($id);
		$this->addElementReadOnly('Widget Class', get_class($this->widgetInstance));
		$this->addElement(new ElementHidden('id', 'ID', $id));

		$this->addElementsWidgetOptions($this->widgetInstance);

		$this->addDefaultButtons();
	}

	private function addElementsWidgetOptions() {
		foreach ($this->widgetInstance->getArguments() as $name => $value) {
			$el = $this->widgetInstance->getArgumentFormElement($name);

			$val = $this->widgetInstance->getArgumentValue($name);
	
			$el->setValue($val);

			$this->addElement($el);
		}
	}

	private function getWidgetInstance($id) {
		$sql = 'SELECT wi.*, w.class, wi.dashboard FROM widget_instances wi LEFT JOIN widgets w ON wi.widget = w.id WHERE wi.id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		$this->rowWidgetInstance = $stmt->fetchRowNotNull();

		include_once 'includes/classes/Widget' . $this->rowWidgetInstance['class'] . '.php';
		$this->widgetInstance = 'Widget' . $this->rowWidgetInstance['class'];
		$this->widgetInstance = new $this->widgetInstance();
		$this->widgetInstance->loadArguments($this->rowWidgetInstance['id']);

	}

	public function process() {
		$sql = 'INSERT INTO widget_instance_arguments (instance, name, value) VALUES (:instance, :name, :value) ON DUPLICATE KEY UPDATE value = :value2';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':instance', $this->getElementValue('id'));

		foreach ($this->widgetInstance->getArguments() as $name => $oldValue) {
			$val = $this->getElementValue($name);

			if (is_array($val)) {
				$val = implode(";", $val);
			}

			$stmt->bindValue(':name', $name);
			$stmt->bindValue(':value', $val);
			$stmt->bindValue(':value2', $val);
			$stmt->execute();
		}
	}
}


$fh = new FormHandler('FormUpdateWidgetInstance');
$fh->constructForm();
$fh->setRedirect('viewDashboard.php?id=' . $fh->getForm()->rowWidgetInstance['dashboard']);
$fh->handle();

?>
