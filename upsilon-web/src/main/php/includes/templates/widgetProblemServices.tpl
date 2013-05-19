<ul class = "metricList">
{foreach from = $problemServices item = itemService}
	<li><span Class = "metricIndicator {$itemService.karma|strtolower}">{$itemService.goodCount}</span>
	<div class = ""<span class = "metricTitle">{$itemService.identifier}</span></li>
{/foreach}
</ul>
