<?php

require_once 'Widget.php';

class WidgetNews extends Widget {
	public function __construct() {
		$this->arguments['group'] = null;
		$this->arguments['maximum'] = 5;
		$this->arguments['title'] = 'News';
	}

	public function render() {
		global $tpl; 

		$group = $this->getArgumentValue('group');

		$tpl->assign('ref', 'news');
		$tpl->assign('url', 'json/getNews?group=' . $group);
		$tpl->assign('callback', 'renderNewsList');
		$tpl->assign('queryParams', json_encode(array()));
		$tpl->assign('repeat', 60000);
		$tpl->display('widgetAjax.tpl');
	}
}

?>
