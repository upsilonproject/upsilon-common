<?php

require_once 'includes/classes/Widget.php';

class WidgetNodes extends Widget {
	public function getTitle() {
		return 'Nodes';
	}

	public function render() {
		global $tpl;

		$tpl->assign('listNodes', getNodes());
		$tpl->display('widgetNodes.tpl');
	}

	public function addLinks() {
		$this->links->add('listNodes.php', 'List of nodes');
	}
}

?>
