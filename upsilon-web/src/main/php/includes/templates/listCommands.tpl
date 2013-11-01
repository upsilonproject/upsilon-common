<div class = "box">
	<h2>Commands</h2>

	<table>
		<thead>
			<tr>
				<th>Command Identifier</th>
				<th>Icon</th>
			</tr>
		</thead>

		<tbody>
		{foreach from = $listCommands item = itemCommand}	
			<tr>
				<td><a href = "updateCommand.php?id={$itemCommand.id}">{$itemCommand.commandIdentifier}</a></td>
				<td>{$itemCommand.icon}</td>
			</tr>	
		{/foreach}
		</tbody>
	</table>
</div>
