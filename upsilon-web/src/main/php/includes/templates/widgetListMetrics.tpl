<div>
	{if $serviceDetail}
	<h2>Details</h2>
	<p><strong>Service:</strong> {if $drawNavigation}<a href = "viewService.php?id={$service.id}">{/if}{$service.identifier}{if $drawNavigation}</a>{/if}<p>
	<p><strong>Karma:</strong> <span class = "metricIndicator {$service.karma|strtolower}">{$service.karma}</span></p>
	<p><strong>Last Updated:</strong> {$service.lastUpdatedRelative}</p>

	{/if}
	{if isset($service.listSubresults)} 

	<h2>{$subresultsTitle|default:'Subresults'}</h2>
	<ul class = "subresults">
	{foreach from = $service.listSubresults item = subResult}
		<li><span class = "metricIndicator {$subResult.karma|strtolower}">&nbsp;</span>{$subResult.name}</li>
	{/foreach}
	</ul>
	{else}
	No subresults!
	{/if}
</div>
