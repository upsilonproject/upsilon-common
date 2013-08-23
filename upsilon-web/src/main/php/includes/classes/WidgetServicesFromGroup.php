<?php

require_once 'Widget.php';

use \libAllure\DatabaseFactory;

class WidgetServicesFromGroup extends Widget {
	public function __construct() {
		$this->arguments['title'] = null;
		$this->arguments['group'] = null;
	}

	public function render() {
		global $tpl;

		$this->services = getServices($this->getArgumentValue('group'));

		$tpl->assign('listServices', $this->services);

                if (empty($this->services)) {
                        echo '<p>No services.</p>';
                } else {
                        $tpl->display('metricList.tpl');
                }
	}
}

?>
