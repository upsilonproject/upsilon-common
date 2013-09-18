<ul>
{foreach from = $services item = service}
	<li><strong><span class = "metricIndicator {$service.karma|strtolower}">{$service.consecutiveCount}</span></strong> {$service.identifier}<p class = "metricOutput">{$service.output}</li>
{/foreach}
</ul>

