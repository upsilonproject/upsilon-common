{if empty($listClassInstances)}
	<p>There are 0 instances of this class</p>
{else}
{foreach from = $listClassInstances item = itemClassInstance} 
	<div class = "box">
	<h2>title ({$itemClassInstance.classTitle})</h2>

	<table>
		<thead>
			<tr>
				<th style = "width: 200px;">Requirement</th>
				<th style = "width: 200px;">Coverage</th>
				<th>Service</th>
			</tr>
		</thead>	

		<tbody>
		{foreach from = $itemClassInstance.listServices item = itemService}
			<tr>
				<td><a href = "updateInstanceCoverage.php?instance={$itemClassInstance.id}&amp;requirement={$itemService.requirementId}">{$itemService.classTitle}</a></td>

				<td class = "{if empty($itemService.service)}bad{else}good{/if}">
					{if empty($itemService.service)}Not Covered{else}{$itemService.serviceLastUpdated}{/if}
				</td>

				<td>
					{if not empty($itemService.service)}
						<a href = "viewService.php?id={$itemService.service}">{$itemService.serviceIdentifier}</a>
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	</div>
{/foreach}
{/if}
