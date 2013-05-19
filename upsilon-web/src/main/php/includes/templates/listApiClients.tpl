<a href = "createApiClient.php">Create</a>

<div class = "box">
	<h2>API Clients</h2>


	<table>
	<thead>
		<tr>
			<th>ID</th>
			<th>Identifier</th>
			<th>Redirect</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	{foreach from = $listApiClients item = itemApiClient}
		<tr>
			<td>{$itemApiClient.id}</td>
			<td>{$itemApiClient.identifier}</td>
			<td>{$itemApiClient.redirect}</td>
			<td><a href = "updateApiClient.php?id={$itemApiClient.id}">Update</a></td>
		</tr>
	{/foreach}
	</tbody>
	</table>
</div>
