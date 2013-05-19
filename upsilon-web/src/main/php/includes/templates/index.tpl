{if $tutorialMode}
<div class = "box tutorialMessage">
	<p><strong>Services</strong> are individual items that can be checked. This might be a server ping, database uptime, disk usage or listing of unread emails. </p>
</div>
{/if}	

<div id = "loadingAnimation">
{if not $mobile}
		<h3>LOADING</h3>
		<p>If this message does not go away, your browser does not support javascript.</p>
{/if}
</div>

<div id = "metricGroupContainer" class = "blockContainer" />
