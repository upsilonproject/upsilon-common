{if $tutorialMode}
<div class = "box tutorialMessage">
	<p><strong>Nodes</strong> are responsible for executing <a href = "index.php">service checks</a> and optionally sending those results their node peers.</p>
	<p style = "font-size: x-small " class = "subtle">This message is being shown because <a href = "preferences.php">tutorial mode</a> is enabled.</p>
</div>
{/if}

<div class = "box">
	<h2>Nodes</h2>

{if empty($listNodes)}
	<p>0 nodes in database.</p> 
	<p>Visit the wiki to understand how to <a href = "http://upsilon-project.co.uk/site/index.php/SetupNodeDatabase">configure your node to write to a database</a>.</p>
{else}
<table class = "hover dataTable">
	<thead>
		<tr>
			<th>id</th>
			<th>Title</th>
			<th>Type</th>
			<th>Service count</th>
			<th>Last updated</th>
			<th>Status</th>
		</tr>
	</thead>

	<tbody>
		{foreach from = $listNodes item = itemNode}
		<tr>
			<td>{$itemNode.id}</td>
			<td><a class = "node" href = "viewNode.php?id={$itemNode.id}">{$itemNode.identifier}</a></td>
			<td>{$itemNode.nodeType} (version {$itemNode.instanceApplicationVersion})</td>
			<td>{$itemNode.serviceCount}</td>
			<td>{$itemNode.lastUpdated} {$itemNode.lastUpdateRelative}</td>
			<td class = "{$itemNode.karma|strtolower}">{$itemNode.karma}</td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/if}
</div>
