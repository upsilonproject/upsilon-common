<div class = "box">
	<h2>Remote configs</h2>
	
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Identifier</th>
				<th colspan = "2">Actions</th>
			</tr>
		</thead>

		<tbody>
		{foreach from = $listRemoteConfigs item = itemRemoteConfig}
			<tr>
				<td>{$itemRemoteConfig.id}</td>
				<td><a href = "viewRemoteConfig.php?id={$itemRemoteConfig.id}">{$itemRemoteConfig.identifier}</a></td>
				<td><a href = "/remoteConfig/?node={$itemRemoteConfig.identifier}">View</a> <a href = "updateRemoteConfig.php">Touch</a></td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
