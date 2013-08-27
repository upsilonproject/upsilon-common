<div class = "box">
	<h2>Usergroups</h2>

	{if empty($listUsergroups)}
		<p>There are 0 usergroups defined. That is a little weird.</p>
	{else}
		<table>
			<thead>
				<tr>
					<th>ID</th>
					<th>Title</th>
				</tr>
			</thead>
			<tbody>
			{foreach from = $listUsergroups item = itemUsergroup}
			<tr>
				<td><a href = "viewUsergroup.php?id={$itemUsergroup.id}">{$itemUsergroup.id}</a></td>
				<td><a href = "viewUsergroup.php?id={$itemUsergroup.id}">{$itemUsergroup.title}</a></td>
			</tr>
			{/foreach}
			</tbody>
		</table>
	{/if}
</div>
