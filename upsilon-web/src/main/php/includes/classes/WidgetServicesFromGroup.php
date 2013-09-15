<?php

require_once 'Widget.php';

use \libAllure\DatabaseFactory;
use \libAllure\ElementSelect;

class WidgetServicesFromGroup extends Widget {
	public function __construct() {
		$this->arguments['title'] = null;
		$this->arguments['group'] = null;
	}

	public function getTitle() {
		$widgetTitle = $this->getArgumentValue('title');
		$group = getGroup($this->getArgumentValue('group'));
		$groupTitle = $group['title'];

		if (empty($widgetTitle)) {
 			if (empty($groupTitle)) {
				return "Services from group";
			} else {
				return 'Group: ' . $groupTitle;
			}
		} else {
			return $widgetTitle;
		}
	}

	public function render() {
		global $tpl;
		$tpl->assign('ref', rand());
		$tpl->assign('url', 'json/getServicesInGroup');
		$tpl->assign('queryParams', json_encode(array('group' => $this->getArgumentValue('group'))));
		$tpl->assign('callback', 'renderServiceList');
		$tpl->assign('repeat', 60000);
		$tpl->display('widgetAjax.tpl');
	}
}

?>
