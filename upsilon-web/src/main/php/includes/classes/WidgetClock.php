<?php

require_once 'Widget.php';

class WidgetClock extends Widget {
	public function __construct() {
		$this->arguments['title'] = '';
	}

	public function render() {
		global $tpl;

		$tpl->display('widgetClock.tpl');
	}

}

?>
