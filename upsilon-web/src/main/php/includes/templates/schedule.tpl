<div style = "text-align: left">

<table>
	<thead>
		<tr>
			<th>Service</th>
			<th>Results</th>
		</tr>
	</thead>

	<tbody>
	{foreach from = $listServiceResults item = itemServiceResult}
		<tr>
			<td>{$itemServiceResult.identifier}</td>
			<td>results</td>
		</tr>
	{/foreach}
	</tbody>
</table>

{literal}
position: absolute; vertical-align: top; left:' . $relative. 'px
{/literal}

