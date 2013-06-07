</div>
<script type = "text/javascript">
setupSortableTables();
setupEnhancedSelectBoxes();
setupCollapseableForms();
</script>
<div id = "footer">
	<p>
		<strong>Crypto:</strong> <span class = "{if $crypto}good{else}bad{/if}">{if $crypto}on{else}off{/if}</span>
		&nbsp;&nbsp;&nbsp;&nbsp;
	{if !empty($apiClient)}
		<strong>API Client:</strong> {$apiClient}
	{else}
		<strong>Time now:</strong> {$date}
		&nbsp;&nbsp;&nbsp;&nbsp;
		<strong>DB Queries:</strong> {$queryCount}
	{/if}
	</p>
</div>
</body>
</html>
