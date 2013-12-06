<div class = "box">
	<h2>Widgets</h2>
	
	<table>
		<thead>
		<tr>
			<th>Classes</th>
			<th>Instances</th>
		</tr>
		</thead>

		<tbody>
			{foreach from = $listWidgets item = itemWidget}
			<tr>
				<td>{$itemWidget.class}</td>
				<td>{$itemWidget.instances}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
