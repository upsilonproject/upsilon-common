<?php

require_once 'includes/classes/Widget.php';

use \libAllure\ElementNumeric;
use \libAllure\ElementTextbox;

class WidgetGraphMetrics extends Widget {
	private static $graphIndex = 0;

	public function __construct() {
		parent::__construct();
	
		$this->instanceGraphIndex = self::$graphIndex++;

		$this->arguments['service[]'] = null;
		$this->arguments['metric'] = null;
		$this->arguments['yAxisMarkings'] = null;
	}

	public function render() {
		$id = $this->getArgumentValue('service[]');

		global $tpl;
		$tpl->assign('listServiceId', $id);
		$tpl->assign('metric', $this->getArgumentValue('metric'));

		$v = trim($this->getArgumentValue('yAxisMarkings'));
		if (empty($v)) {
			$v = array(); 
		} else {
			$v = explode("\n", $v);
		}

		$tpl->assign('yAxisMarkings', $v);
		$tpl->assign('instanceGraphIndex', $this->instanceGraphIndex);
		$tpl->display('widgetGraphMetric.tpl');

	}

	public function getArgumentFormElement($optionName) {
		switch ($optionName) {
		case 'height':
			return new ElementNumeric($optionName, 'Height');
		case 'yAxisMarkings':
			return new ElementTextbox($optionName, 'Y Axis Markings');
		default:
			return parent::getArgumentFormElement($optionName);
		}
	}

	public function addLinks() {
		$servicesMenu = linksCollection();

		foreach ($this->getArgumentValue('service[]') as $service) {
			$servicesMenu->add('viewService.php?id=' . $service, 'Service ' . $service);
		}

		$this->links->add(null, 'Services');
		$this->links->addChildCollection('Services', $servicesMenu);
	}
}

?>
