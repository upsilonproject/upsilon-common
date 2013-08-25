<ul class = "subresults">
{if empty($listNodes)}
	<p>0 nodes in database.</p> 
	<p>Visit the wiki to understand how to <a href = "http://upsilon-project.co.uk/site/index.php/SetupNodeDatabase">configure your node to write to a database</a>.</p>
{else}
{foreach from = $listNodes item = node} 
	<li>
	<span class = "metricIndicator {$node.karma|strtolower}">&nbsp;</span> 
	
		{if $drawNavigation}<a href = "viewNode.php?id={$node.id}">{/if}
		{$node.identifier}
		{if $drawNavigation}</a>{/if}

		<span class = "subtle">({$node.serviceType})</span>
	</li>
{/foreach}
{/if}
</ul>
