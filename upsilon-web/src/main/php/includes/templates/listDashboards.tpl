<div class = "box">
	<h2>Dashboards</h2>
	
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
</div>
