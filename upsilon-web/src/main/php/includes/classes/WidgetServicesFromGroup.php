<?php

require_once 'Widget.php';

use \libAllure\DatabaseFactory;

class WidgetServicesFromGroup extends Widget {
	public function __construct() {
		$this->arguments['title'] = null;
		$this->arguments['group'] = null;
	}

	public function getTitle() {
		$widgetTitle = $this->getArgumentValue('title');
		$groupTitle = $this->getArgumentValue('group');

		if (empty($widgetTitle)) {
 			if (empty($groupTitle)) {
				return parent::getTitle();
			} else {
				return 'Group: ' . $groupTitle;
			}
		} else {
			return $wigetTitle;
		}
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
