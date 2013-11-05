<div class = "metricGroup block" {if $hidden}style = "display: none"{/if}>
	<h3>{if $drawNavigation}<a href = "viewGroup.php?id={$itemGroup.id}">{/if}{$itemGroup.title}{if $drawNavigation}</a>{/if}</h3>
{if !empty($itemGroup.listServices) || !empty($itemGroup.listSubgroups)}

	{if !empty($itemGroup.listServices)}
	{include file = "metricList.tpl" listServices = $itemGroup.listServices}
	{/if}

	{foreach from = $itemGroup.listSubgroups item = itemSubgroup}
		<h4>{if $drawNavigation}<a href = "viewGroup.php?id={$itemSubgroup.id}">{/if}{$itemSubgroup.title}{if $drawNavigation}</a>{/if}</h4>
		{include file = "metricList.tpl" listServices = $itemSubgroup.listServices}
	{/foreach}
{else}
	<p>This group is has 0 services and 0 subgroups.</p>
{/if}
</div>
