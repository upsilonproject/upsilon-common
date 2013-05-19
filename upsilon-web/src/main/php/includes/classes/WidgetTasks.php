<?php

require_once 'Widget.php';

class WidgetTasks extends Widget {
	public function getTitle() {
		return 'Tasks';
	}

	public function render() {
		global $tpl;

		$tpl->assign('tasks', getTasks());
		$tpl->display('widgetTasks.tpl');
	}
}

?>
