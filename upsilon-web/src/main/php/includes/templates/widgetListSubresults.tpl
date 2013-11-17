<div>
	{if $serviceDetail}
	<h2>Details</h2>
	<p><strong>Service:</strong> {if $drawNavigation}<a href = "viewService.php?id={$service.id}">{/if}{$service.identifier}{if $drawNavigation}</a>{/if}<p>
	<p><strong>Karma:</strong> <span class = "metricIndicator {$service.karma|strtolower}">{$service.karma}</span></p>
	<p><strong>Last Updated:</strong> {$service.lastUpdatedRelative}</p>

	{/if}

	<h2>{$subresultsTitle|default:'Subresults'}</h2>
	<ul class = "subresults" id = "subresultsService{$service.id}">&nbsp;</ul>
		<li><p style = "text-align: center"><img src = "resources/images/loading.gif" alt = "loading icon" /></p></li>
	</ul>

	<script type = "text/javascript">
	{literal}
	request("json/getSubresults.php", {serviceId: {/literal}{$service.id}{literal}}, renderSubresults, "#subresultsService{/literal}{$service.id}{literal}")
	{/literal}
	</script>
</div>
