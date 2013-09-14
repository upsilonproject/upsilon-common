<div class = "box">
	<h4>Maintenence Periods</h4>

	<table class = "sortable">
		<thead>
			<tr>
				<th>ID</th>
				<th>Title</th>
				<th>Content</th>
			</tr>
		</thead>

		<tbody>
		{foreach from = $listMaintPeriods item = itemMaintPeriod}
			<tr>
				<td><a href = "updateMaintPeriod.php?id={$itemMaintPeriod.id}">{$itemMaintPeriod.id}</a></td>
				<td>{$itemMaintPeriod.title}</td>
				<td><pre>{$itemMaintPeriod.content}</pre></td>
			</td>
		{/foreach}
		</tbody>
	</table>
</div>
