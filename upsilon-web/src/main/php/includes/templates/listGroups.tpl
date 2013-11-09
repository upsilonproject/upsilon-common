<div class = "box">
	<h2>Groups</h2>
	
	{if $listGroups|@count == 0}
	<p>There are no groups defined at the moment. Create a group by going to <strong>Group Actions</strong> &raquo; <strong>Create Group</strong> on the menu.</p>
	{else}
	<table class = "dataTable">
		<thead>
			<tr>
				<th>ID</th>
				<th>Title</th>
				<th>Parent</th>
				<th>Number of Services</th>
			</tr>
		</thead>

	<tbody>
	{foreach from = $listGroups item = itemGroup}
		<tr>
			<td><a href = "viewGroup.php?id={$itemGroup.id}">{$itemGroup.id}</a></td>
			<td><a href = "viewGroup.php?id={$itemGroup.id}">{$itemGroup.title}</a></td>
			<td>{if empty($itemGroup.parentId)}-{else}<a href = "viewGroup.php?id={$itemGroup.parentId}">{$itemGroup.parentName}</a>{/if}</td>
			<td>{$itemGroup.serviceCount}</td>
		</tr>
	{/foreach}
	</tbody>
	</table>
	{/if}
</div>
