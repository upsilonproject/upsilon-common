<div class = "box">
	<h2>Remote service configs for node: {$remoteConfig.identifier}</h2>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Identifier</th>
				<th>Actions</th>
			</tr>
		</thead>

		<tbody>
			{foreach from = $services item = service}
			<tr>
				<td>{$service.id}</td>
				<td><a href = "updateRemoteConfigurationService.php?id={$service.id}">{$service.identifier}</a></td>
				<td><a href = "deleteRemoteConfigurationService.php?id={$service.id}">Delete</a></td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
