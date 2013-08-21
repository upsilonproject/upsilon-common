<?php

require_once 'Widget.php';

class WidgetEvents extends Widget {
	public function __construct() {
		$this->arguments['dateFormat'] = '';
		$this->arguments['eventLimit'] = '';
		$this->arguments['eventCutoff'] = '';
	}

	public function render() {
		global $tpl;

		$events = getEvents();
		usort($events, array($this, 'sortEvent'));
		$events = $this->filterList($events, $this->getArgumentValue('eventLimit'), $this->getArgumentValue('eventCutoff'));


		$tpl->assign('dateFormat', $this->getArgumentValue('dateFormat'));
		$tpl->assign('events', $events);
		$tpl->display('widgetEvents.tpl');
	}

	private function filterList(array $events, $limit = 10, $cutoff) {
		if (empty($cutoff)) {
			$interval = new DateInterval('P365D');
		} else {
			$interval = new DateInterval($cutoff);
		}

		if (empty($limit)) {
			$limit = 20;
		} else {
			$limit = intval($limit);
		}

		$now = new DateTime();
		$cutoff = ($now)->add($interval);

		$ret = array();

		for ($i = 0; $i < min(sizeof($events), $limit); $i++) {
			$eventDate = new DateTime($events[$i]['start']);	

			if ($eventDate > $cutoff) {
				continue;
			} else {
				$ret[] = $events[$i];
			}
		}

		return $ret;
	}

	private function sortEvent($a, $b) {
		$aTime = strtotime($a['start']);
		$bTime = strtotime($b['start']);

		return $aTime > $bTime;
	}
}

?>
