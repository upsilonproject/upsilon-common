<ul class = "subresults">
{foreach from = $listNodes item = node} 
	<li><span class = "metricIndicator {$node.karma|strtolower}">&nbsp;</span> {$node.identifier}</li>
{/foreach}
</ul>
