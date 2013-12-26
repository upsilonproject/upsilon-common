<div class = "box">
	<h2>Maintenence Periods</h2>

	<table class = "sortable">
		<thead>
			<tr>
				<th>ID</th>
				<th>Title</th>
				<th>Content</th>
				<th>Services</td>
			</tr>
		</thead>

		<tbody>
		{foreach from = $listMaintPeriods item = itemMaintPeriod}
			<tr>
				<td><a href = "updateMaintPeriod.php?id={$itemMaintPeriod.id}">{$itemMaintPeriod.id}</a></td>
				<td><a href = "updateMaintPeriod.php?id={$itemMaintPeriod.id}">{$itemMaintPeriod.title}</a></td>
				<td><pre>{$itemMaintPeriod.content}</pre></td>
				<td><a href = "viewList.php?maintPeriod={$itemMaintPeriod.id}">{$itemMaintPeriod.countServices}</a></td>
			</td>
		{/foreach}
		</tbody>
	</table>
</div>
