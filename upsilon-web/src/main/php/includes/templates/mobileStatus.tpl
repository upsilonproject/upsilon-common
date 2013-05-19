<ul>
{foreach from = $services item = service}
	<li><strong><span class = "metricIndicator {$service.karma|strtolower}">{$service.goodCount}</span></strong> {$service.identifier}<p class = "metricOutput">{$service.output}</li>
{/foreach}
</ul>

