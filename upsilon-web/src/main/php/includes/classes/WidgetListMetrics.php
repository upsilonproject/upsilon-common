<?php

require_once 'includes/classes/Widget.php';

use \libAllure\ElementNumeric;
use \libAllure\ElementCheckbox;

class WidgetListMetrics extends Widget {
	public function __construct() {
		parent::__construct();
		$this->arguments['serviceDetail'] = null;
		$this->arguments['subresultsTitle'] = null;
	}

	public function render() {
		global $tpl;

		$sr = getServiceById($this->getArgumentValue('service'));

		parseOutputJson($sr);

		$tpl->assign('service', $sr);
		$tpl->assign('serviceDetail', $this->getArgumentValue('serviceDetail'));
		$tpl->assign('subresultsTitle', $this->getArgumentValue('subresultsTitle'));
		$tpl->display('widgetListMetrics.tpl');
	}

	public function getArgumentFormElement($name) {
		switch ($name) {
		case 'serviceDetail':
			return new ElementCheckbox('serviceDetail', 'Service detail');
		default:
			return parent::getArgumentFormElement($name);
		}
	}
}

?>
