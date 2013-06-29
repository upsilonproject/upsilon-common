<?php

require_once 'Widget.php';

class WidgetEvents extends Widget {
	public function render() {
		global $tpl;

		$events = getEvents();
		usort($events, array($this, 'sortEvent'));


		$tpl->assign('events', $events);
		$tpl->display('widgetEvents.tpl');
	}

	private function sortEvent($a, $b) {
		$aTime = strtotime($a['start']);
		$bTime = strtotime($b['start']);

		return $aTime > $bTime;
	}
}

?>
