<?php

require_once 'Widget.php';

use \libAllure\DatabaseFactory;
use \libAllure\ElementSelect;

class WidgetServicesFromGroup extends Widget {
	public function __construct() {
		$this->arguments['title'] = null;
		$this->arguments['group'] = null;

	}

	public function init() {
		$this->group = getGroup($this->getArgumentValue('group'));
	}

	public function getTitle() {
		$widgetTitle = $this->getArgumentValue('title');

		if (empty($widgetTitle)) {
 			if (empty($this->group['title'])) {
				return "Services from group";
			} else {
				return 'Group: ' . $this->group['title'];
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

	public function addLinks() {
		$this->links->add('viewGroup.php?id=' . $this->group['id'], 'Group: ' . $this->group['title']);
	}
}

?>
