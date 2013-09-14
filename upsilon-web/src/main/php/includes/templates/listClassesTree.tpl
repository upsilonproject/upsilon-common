<table class = "hover">

<thead>
	<tr>
		<th>Title</th>
		<th>Depth</th>
	</tr>
</thead>

<tbody>
{foreach from = $listSubClasses item = itemClass}
	<tr>
		<td><a href = "listClasses.php?id={$itemClass.id}">{$itemClass.title}</a></td>
		<td>{$itemClass.childrenCount}</td>
	</tr>
{/foreach}
</tbody>
</table>
