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
</div>

	<script src="resources/javascript/jquery.masonry.min.js"></script>
	<script src="resources/javascript/jquery.flot.js"></script>
	<script src="resources/javascript/jquery.flot.time.min.js"></script>
	<script src="resources/javascript/jquery.select2.min.js"></script>

<script type = "text/javascript">
require([
	"dojo/dom", 
	"dijit/form/Button",
	"dijit/Menu",
	"dijit/MenuBar",
	"dijit/PopupMenuBarItem",
	"dijit/MenuItem", 
	"dijit/PopupMenuItem", 
	"dijit/MenuSeparator",
	"dijit/DropDownMenu",
	"dojo/parser",
	"dojo/_base/window",
	"dijit/ProgressBar",
	"dojo/domReady!", 
]);
	</script>
	<script src="resources/javascript/jquery.dataTables.min.js"></script>

<script type = "text/javascript">
setupSortableTables();
setupEnhancedSelectBoxes();
setupCollapseableForms();

{literal}
$(document).ready(function() {
	dojo.parser.parse();	
});
{/literal}
</script>





</body>
</html>
