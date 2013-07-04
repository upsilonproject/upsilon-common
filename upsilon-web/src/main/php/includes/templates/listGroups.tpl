<div class = "box">
	<h2>Groups</h2>
	
	<table class = "dataTable">
		<thead>
			<tr>
				<th>ID</th>
				<th>Title</th>
				<th>Parent</th>
			</tr>
		</thead>

	<tbody>
	{foreach from = $listGroups item = itemGroup}
		<tr>
			<td><a href = "viewGroup.php?id={$itemGroup.id}">{$itemGroup.id}</a></td>
			<td><a href = "viewGroup.php?id={$itemGroup.id}">{$itemGroup.title}</a></td>
			<td>{if empty($itemGroup.parentId)}-{else}<a href = "viewGroup.php?id={$itemGroup.parentId}">{$itemGroup.parentName}</a>{/if}</td>
		</tr>
	{/foreach}
	</tbody>
	</table>
</div>
