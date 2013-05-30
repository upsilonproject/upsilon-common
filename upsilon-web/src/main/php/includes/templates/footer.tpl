</div>
<script type = "text/javascript">
setupSortableTables();
setupEnhancedSelectBoxes();
setupCollapseableForms();
</script>
<div id = "footer">
	<p>
		<strong>Crypto:</strong> <span class = "{if $crypto == "on"}good{else}bad{/if}">{$crypto}</span>
		&nbsp;&nbsp;&nbsp;&nbsp;
	{if isset($apiClient)}
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
