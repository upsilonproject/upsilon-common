<div class = "box">
	<h2>Commands</h2>

	<table>
		<thead>
			<tr>
				<th colspan = "2">Command Identifier</th>
				<th>Services</th>
			</tr>
		</thead>

		<tbody>
		{foreach from = $listCommands item = itemCommand}	
			<tr>
				<td>
					{if !empty($itemCommand.icon)}
					<img src = "resources/images/serviceIcons/{$itemCommand.icon}" alt = "serviceIcon" class = "inlineIcon"/>
					{/if}
				</td>
				<td><a href = "updateCommand.php?id={$itemCommand.id}">{$itemCommand.commandIdentifier}</a></td>
				<td>{$itemCommand.serviceCount}</td>
			</tr>	
		{/foreach}
		</tbody>
	</table>
</div>
