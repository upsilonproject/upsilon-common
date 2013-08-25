<ul class = "subresults">
{foreach from = $listNodes item = node} 
	<li>
	<span class = "metricIndicator {$node.karma|strtolower}">&nbsp;</span> 
	
		{if $drawNavigation}<a href = "viewNode.php?id={$node.id}">{/if}
		{$node.identifier}
		{if $drawNavigation}</a>{/if}

		<span class = "subtle">({$node.serviceType})</span>
	</li>
{/foreach}
</ul>
