<?php

require_once 'libAllure/HtmlLinksCollection.php';

use \libAllure\ElementSelect;
use \libAllure\ElementInput;
use \libAllure\DatabaseFactory;
use \libAllure\HtmlLinksCollection;

class Widget {
	protected $arguments = array();

	public function __construct() {
		$this->arguments['title'] = null;
		$this->arguments['service'] = null;

	}

	public function getTitle() {
		return $this->getArgumentValue('title');
	}

	public function loadArguments($id) {
		$this->id = $id;

		$sql = 'SELECT name, value FROM widget_instance_arguments WHERE instance = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $arg) {
			$this->setArgument($arg['name'], $arg['value']);
		}
	}

	public function render() {
		echo 'Empty Widget!';
	}

	private function getFormElementService() {
		$el = new ElementSelect('service', 'Service');

		$sql = 'SELECT s.id, s.identifier FROM services s';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $service) {
			$el->addOption($service['identifier'], $service['id']);
		}

		return $el;
	}

	public function getArguments() {
		return $this->arguments;
	}

	public function setArgument($key, $value) {
		$this->arguments[$key] = $value;
	}

	public function getArgumentValue($key) {
		if (!isset($this->arguments[$key])) {
			return null;
		}

		return $this->arguments[$key];
	}

	public function getArgumentFormElement($optionName) {
		switch ($optionName) {
		case 'service':
			return $this->getFormElementService();
		default:
			return new ElementInput($optionName, ucwords($optionName), null);
		}
		
	}

	protected function addLinks() {}

	public function getLinks() {
		if (!isset($this->links)) {
			$this->links = new HtmlLinksCollection();
			$this->addLinks();
			$this->links->add('updateWidgetInstance.php?id=' . $this->id, 'Update');
			$this->links->add('deleteWidgetInstance.php?id=' . $this->id, 'Delete');
		}

		return $this->links;
	}

}
?>
