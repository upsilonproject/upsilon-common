<div class = "box">
<h4>List services ({$listServices|@count})</h4>
<table class = "dataTable hover">
	<thead>
		<tr>
			<th>Description</th>
			<th><nobr>Last updated</nobr></th>
			<th>Output</th>
			<th>Karma</th>
		</tr>
	</thead>

	<tbody>
{foreach from = "$listServices" item = "itemService"}
	<tr>
		<td>
			<a href = "viewService.php?id={$itemService.id}">{$itemService.identifier}</a>
		</td>
		<td>{$itemService.lastUpdated}</td>
		<td><pre>{$itemService.output}</pre></td>
		<td class = "{$itemService.karma|strtolower}">{$itemService.karma}</td>
	</tr>
{/foreach}
	</tbody>
</table>
</div>
