<?php

require_once 'jsonCommon.php';

$group = san()->filterUint('group');
$services = getServices($group);

$news = array();
$times = array();

foreach ($services as $service) {
	if (!isset($service['news'])) {
		continue;
	}

	foreach ($service['news'] as $key => $story) {
		$story['time'] = strtotime($story['time']);
		$news[] = $story;
		$times[] = $story['time'];
	}
}

array_multisort($times, SORT_DESC, $news);

$news = array_slice($news, 0, 10, true);

outputJson($news);

?>
