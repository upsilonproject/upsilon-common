{if $tutorialMode}
<div class = "box tutorialMessage">
	<p><strong>Dashboards</strong> are useful for grouping up lots of information in to one easy to view place. </p>
</div>
{/if}

<div class = "box">
	<h2>Dashboards</h2>

	{if empty($listDashboards)}
		<p>You do not have any dashboards created at the moment. Select "Create Dashboard" from the actions menu to get started.</p>
	{else}	
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Title</th>
			</tr>
		</thead>
		<tbody>
		{foreach from = $listDashboards item = itemDashboard}
			<tr>
				<td>{$itemDashboard.id}</td>
				<td><a href = "viewDashboard.php?id={$itemDashboard.id}">{$itemDashboard.title}</a></td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	{/if}
</div>
