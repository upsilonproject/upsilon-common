<?php

require_once 'includes/classes/Widget.php';

use \libAllure\ElementNumeric;
use \libAllure\ElementCheckbox;

class WidgetListSubresults extends Widget {
	public function __construct() {
		parent::__construct();
		$this->arguments['service'] = null;
		$this->arguments['serviceDetail'] = null;
		$this->arguments['subresultsTitle'] = null;
	}

	public function init() {
		try {
			$this->service = getServiceById($this->getArgumentValue('service'));
		} catch (Exception $e) {
			$this->service = null;
		}

		parseOutputJson($this->service);
	}

	public function render() {
		global $tpl;

		if ($this->service == null) {
			$tpl->assign('message', 'Service is not set.');
			$tpl->display('message.tpl');
		} else {
			$tpl->assign('service', $this->service);
			$tpl->assign('serviceDetail', $this->getArgumentValue('serviceDetail'));
			$tpl->assign('subresultsTitle', $this->getArgumentValue('subresultsTitle'));
			$tpl->display('widgetListSubresults.tpl');
		}
	}

	public function getArgumentFormElement($name) {
		switch ($name) {
		case 'serviceDetail':
			return new ElementCheckbox('serviceDetail', 'Service detail');
		default:
			return parent::getArgumentFormElement($name);
		}
	}

	public function isShown() {
		if ($this->service == null) {
			return true;
		} else {
			return !empty($this->service['listSubresults']);
		}
	}
}

?>
