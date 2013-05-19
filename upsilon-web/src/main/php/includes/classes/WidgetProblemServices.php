<?php

require_once 'Widget.php';

use \libAllure\DatabaseFactory;

class WidgetProblemServices extends Widget {
	public function __construct() {
		$this->arguments['title'] = null;
		$this->problemServices = getServicesBad();
	}

	public function render() {
		global $tpl;
		$tpl->assign('listServices', $this->problemServices);
		$tpl->display('metricList.tpl');
//		$tpl->display('widgetProblemServices.tpl');
	}
}

?>
