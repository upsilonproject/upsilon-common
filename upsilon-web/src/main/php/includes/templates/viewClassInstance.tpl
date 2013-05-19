

<div class = "box" style = "width: 700px; margin: auto; margin-top: 1em;">
	<div class = "menu">
	<h4>{$itemClassInstance.title}</h4>
	{include file = "links.tpl" skipTitle = true}
	</div>

	<p>Takes requirements from: 
	{foreach from = $listMemberClasses item = memberClass}
		<strong><a href = "listClasses.php?id={$memberClass.id}">{$memberClass.title}</a></strong>
	{/foreach}
	</p>

	<table class = "hover">
		<thead>
			<tr>
				<th style = "width: 200px;">Requirement<br /><small>Class</small></th>
				<th style = "width: 200px;">Coverage</th>
				<th>Service</th>
			</tr>
		</thead>	

		<tbody>
		{foreach from = $listInstanceRequirements item = itemInstanceRequirement}
			<tr>
				<td>
					<a href = "addInstanceCoverage.php?instance={$itemClassInstance.id}&amp;requirement={$itemInstanceRequirement.requirementId}">{$itemInstanceRequirement.requirementTitle}</a>
					<p class = "subtle"><small>&raquo; <strong>{$itemInstanceRequirement.owningClassTitle}</strong></small></p>
				</td>

				<td class = "{if empty($itemInstanceRequirement.karma)}bad{else}{$itemInstanceRequirement.karma|strtolower}{/if}">
					{if empty($itemInstanceRequirement.service)}Not Covered{else}{$itemInstanceRequirement.serviceLastUpdated}{/if}
				</td>

				<td>
					{if not empty($itemInstanceRequirement.service)}
						<img src = "resources/images/serviceIcons/{$itemInstanceRequirement.icon}" />
						<a href = "viewService.php?id={$itemInstanceRequirement.service}">{$itemInstanceRequirement.serviceIdentifier}</a>
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
