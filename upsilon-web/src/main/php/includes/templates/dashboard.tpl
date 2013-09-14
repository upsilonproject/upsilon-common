{if $tutorialMode}
<div class = "box tutorialMessage">
	<p><strong>Dashboards</strong> are useful for grouping up lots of information in to one easy to view place. <strong>Widgets</strong> on dashboards are responsible for showing information.</p>
	<p style = "font-size: x-small " class = "subtle">This message is being shown because <a href = "preferences.php">tutorial mode</a> is enabled.</p>
</div>
{/if}

<div class = "blockContainer">
{if empty($listInstances)}
	<p>This dashboard is empty. Select "Create Widget Instance" from the Dashboard menu.</p>
{else}
		{foreach from = $listInstances item = widget}
			{if $widget.instance->isShown()}
			<div class = "block">
				<div style = "float: right" data-dojo-type = "dijit/form/DropDownButton">
					<span>Widget</span>

					{if $drawNavigation}
						{include file = "links.tpl" links = $widget.instance->getLinks() skipTitle = true sub = true}
					{/if}
				</div>
				<h3>{$widget.instance->getTitle()}</h3>

				{$widget.instance->render()}
			</div>
			{/if}
		{/foreach}
{/if}
</div>

{if !empty($hiddenWidgets)}
<h3>Hidden Widgets</h3>
{foreach from = $hiddenWidgets item = itemWidget}
	<a href = "updateWidgetInstance.php?id={$itemWidget.id}">{$itemWidget.instance->getTitle()}</a> (<a href = "deleteWidgetInstance.php?id={$itemWidget.id}">X</a>) . 
{/foreach}
{/if}

<script type = "text/javascript">
{literal}
$(document).ready(function() {
	layoutBoxes();
	{/literal}
	{if $itemDashboard->isServicesGrouped()}
	toggleGroups();
	{/if}
	{literal}
});
{/literal}
</script>

