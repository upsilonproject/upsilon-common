<?php

require_once 'Widget.php';

class WidgetEvents extends Widget {
	public function render() {
		global $tpl;

		$tpl->assign('events', getEvents());
		$tpl->display('widgetEvents.tpl');
	}
}

?>
