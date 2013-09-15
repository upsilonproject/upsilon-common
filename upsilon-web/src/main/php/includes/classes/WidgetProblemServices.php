<?php

require_once 'Widget.php';

use \libAllure\DatabaseFactory;

class WidgetProblemServices extends Widget {
	public function __construct() {
		$this->arguments['title'] = 'Problem Services';
		$this->problemServices = getServicesBad();
	}

	public function render() {
		global $tpl;
		$tpl->assign('ref', rand());
		$tpl->assign('listServices', $this->problemServices);

                if (empty($this->problemServices)) {
                        echo '<p>No services with problems!</p>';
                } else {
			$tpl->assign('url', 'json/getServices');
			$tpl->assign('callback', 'renderServiceList');
			$tpl->assign('repeat', 60000);
			$tpl->display('widgetAjax.tpl');
                }
	}
}

?>
