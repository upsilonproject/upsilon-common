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

	private function getFormElementService($multi = false) {
		if (!$multi) {
			$el = new ElementSelect('service', 'Service');
		} else if ($multi) {
			$el = new ElementSelect('service[]', 'Services');
			$el->setSize(5);
			$el->multiple = true;
		} else {
			throw new Exception('No service arg in widget');
		}

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

		$val = $this->arguments[$key];

		if (strpos($key, '[]') !== FALSE) {
			$val = explode(';', $val);
		}


		return $val;
	}

	public function getArgumentFormElement($optionName) {
		switch ($optionName) {
		case 'service[]':
			return $this->getFormElementService(true);
		case 'service':
			return $this->getFormElementService(false);
		default:
			$input = new ElementInput($optionName, ucwords($optionName), null);
			$input->setMinMaxLengths(0, 128);

			return $input;
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

	public function init() {}

	public function isShown() {
		return true;
	}
}
?>
