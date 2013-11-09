</div>
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
	<p>
		<a href = "http://upsilon-project.co.uk">Upsilon Project</a><br />
		<a href = "https://github.com/upsilonproject/upsilon/issues?">Raise Issue (bug, suggestion or feature request)</a> | <a href = "mailto:upsilonproject@googlegroups.com
		">Email the developers</a>
	</p>
</div>

<script type = "text/javascript">
setupSortableTables();
setupEnhancedSelectBoxes();
setupCollapseableForms();

{literal}
require(["dojo/parser"], function(parser) {
	parser.parse();	
});
{/literal}
</script>

</body>
</html>
