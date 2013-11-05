{if count($listUngroupedServices) > 0}
<p>
	<strong>Ungrouped: </strong>
{foreach from = $listUngroupedServices item = itemUngroupedService}
	<strong><a href = "viewService.php?id={$itemUngroupedService.id}">{$itemUngroupedService.description}</a></strong> ({$itemUngroupedService.id}) 
{/foreach}

	<a href = "addGroupMembership.php">Add many...</a>
</p>
{/if}
