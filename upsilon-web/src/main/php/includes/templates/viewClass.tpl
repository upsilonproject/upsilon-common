<a href = "createClassInstance.php">Create class instance</a>

{foreach from = $listClassInstances item = itemClassInstance} 
	<div class = "box">
	<h4>title ({$itemClassInstance.classTitle})</h4>

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
