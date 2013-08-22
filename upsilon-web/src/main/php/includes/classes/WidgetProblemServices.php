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

                if (empty($this->problemServices)) {
                        echo '<p>No services with problems!</p>';
                } else {
                        $tpl->display('metricList.tpl');
                }
	}
}

?>
