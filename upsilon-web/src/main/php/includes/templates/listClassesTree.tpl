<table class = "hover">

<thead>
	<tr>
		<th colspan = "2">Title</th>
		<th>Depth</th>
	</tr>
</thead>

<tbody>
{foreach from = $listSubClasses item = itemClass}
	<tr>
		<td>
			{if !empty($itemClass.icon)}
			<img src = "resources/images/serviceIcons/{$itemClass.icon}" alt = "serviceIcon" style = "padding-top: 4px"/>
			{/if}
		</td>
		<td><a href = "listClasses.php?id={$itemClass.id}">{$itemClass.title}</a></td>
		<td>{$itemClass.childrenCount}</td>
	</tr>
{/foreach}
</tbody>
</table>
