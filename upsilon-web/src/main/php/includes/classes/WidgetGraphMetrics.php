<?php

require_once 'includes/classes/Widget.php';

use \libAllure\ElementNumeric;
use \libAllure\ElementTextbox;

class WidgetGraphMetrics extends Widget {
	public function __construct() {
		parent::__construct();
		$this->arguments['service'] = null;
		$this->arguments['metric'] = null;
		$this->arguments['yAxisMarkings'] = null;
	}

	public function render() {
		$id = $this->getArgumentValue('service');
		$metric = $this->getArgumentValue('metric');

		global $tpl;
		$tpl->assign('serviceId', $id);
		$tpl->assign('metric', $metric);
		$tpl->assign('yAxisMarkings', explode("\n", $this->getArgumentValue('yAxisMarkings')));
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
		$this->links->add('viewService.php?id=' . $this->getArgumentValue('service'), 'Service');
	}
}

?>
