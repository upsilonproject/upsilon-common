<div>
<div class = "serviceDetail box">
	<h4>
{if !empty($metadata.icon)}
<img src = "resources/images/serviceIcons/{$metadata.icon}" alt = "serviceIcon" style = "padding-top: 4px"/>
{/if}
	Service Configuration</h4>

	<div style = "float: left; vertical-align: top;">
		<h3>Basics</h3>
		<p><strong>Identifier:</strong> {$itemService.identifier}</p>
		<p><strong>Last Check:</strong> {$itemService.lastUpdated} <span class = "subtle">{$itemService.lastUpdatedRelative}</span></p>
		<p><strong>Estimated Next Check:</strong> {$itemService.estimatedNextCheck} <span class = "subtle">{$itemService.estimatedNextCheckRelative}</span></p>

		<p>
			<strong>Karma:</strong> <span class = "metricIndicator {$itemService.karma|strtolower}">{$itemService.karma} ({$itemService.goodCount} in a row)</span>
		</p>

		{if isset($metadata.criticalCast)}
		<p><strong>Critical Cast:</strong> <span class = "metricIndicator {$metadata.criticalCast|strtolower}">{$metadata.criticalCast|default:'none'}</span></p>
		{/if}
		{if isset($metadata.goodCast)}
		<p><strong>Good Cast:</strong> <span class = "metricIndicator {$metadata.goodCast|strtolower}">{$metadata.goodCast|default:'none'}</span></p>
		{/if}
	</div>

	<div style = "float: right; vertical-align: top;">
		<h3>Debug info</h3>
		<p><strong>ID:</strong> {$itemService.id}</p>
		<p><strong>Command line:</strong> {$itemService.commandLine}</p>

		<p><strong>Group memberships:</strong>
		{if $listGroupMemberships|@count eq 0}
			<em>No memberships.</em>
		{else}
			<ul>
			{foreach from = $listGroupMemberships item = itemGroupMembership}
				<li><a href = "viewGroup.php?id={$itemGroupMembership.groupId}">{$itemGroupMembership.groupName}</a> [<a href = "deleteGroupMembership.php?id={$itemGroupMembership.id}">X</a>]</li>
			{/foreach}
			</ul>
		{/if}
			<br />
			<a href = "addGroupMembership.php?serviceId={$itemService.id}">Add</a>
		</p>

		<p><strong>Node:</strong> <a href = "viewNode.php?identifier={$itemService.node}">{$itemService.node}</a></p>

	</div>

	<div style = "margin-right: 2em; float: right; vertica-align: top">
		<h3>Actions</h3>
		<p><a href = "deleteService.php?identifier={$itemService.identifier}">Delete Service</a></p>

		<h3>Metadata</h3>
		{if not empty($metadata.room)}
			<p>View service in <a href = "viewRoom.php?id={$metadata.room}">room</a></a>
		{/if}
		<p>
		{if empty($metadata)}
			No metadata
		{/if}
		</p>

		<p><a href = "updateServiceMetadata.php?id={$itemService.id}">Update service metadata</a></p>
	</div>

	<div style = "clear: both;"></div>
</div>

<div class = "box" id = "graphContainer">
	<h4 id = "graphTitle">Graph</h4>

	{include file = "widgetGraphMetric.tpl"}

	<p>

	{if !empty($metadata.metrics)}
		<strong>Metric:</strong>
		{foreach from = $metadata.metrics item = metric}
			<a href = "#graphContainer" onclick = "javascript:fetchServiceMetricResultGraph('{$metric|trim}', {$itemService.id})">{$metric|trim}</a>&nbsp;&nbsp;&nbsp;&nbsp;
		{/foreach}
	{/if}
	</p>
</div>

<div class = "recentResults box">
	<h4>Results</h4>

{if $listResults|@count == 0}
<p>No results stored in the results table.</p>
{else}
<table class = "hover dataTable" />
	<thead>
		<tr>
			<th>Timestamp</th>
			<th>Output</th>
			<th>Karma</th>
		</tr>
	</thead>

	<tbody>
		{foreach from = "$listResults" item = "itemResult"}
		<tr>
			<td>{$itemResult.checked} &nbsp;&nbsp;<span class = "metricOutput">{$itemResult.relative}</span></td>
			<td><pre>{$itemResult.output|htmlspecialchars|wordwrap}</pre></td>
			<td class = "{$itemResult.karma|strtolower}">{$itemResult.karma}</td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/if}
</div>
