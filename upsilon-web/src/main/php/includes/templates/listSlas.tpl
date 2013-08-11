<div class = "box">
	<h4>SLAs/Maintenence windows</h4>

	<table class = "sortable">
		<thead>
			<tr>
				<th>ID</th>
				<th>Title</th>
				<th>Content</th>
			</tr>
		</thead>

		<tbody>
		{foreach from = $listSlas item = itemSla}
			<tr>
				<td><a href = "updateSla.php?id={$itemSla.id}">{$itemSla.id}</a></td>
				<td>{$itemSla.title}</td>
				<td><pre>{$itemSla.content}</pre></td>
			</td>
		{/foreach}
		</tbody>
	</table>
</div>
