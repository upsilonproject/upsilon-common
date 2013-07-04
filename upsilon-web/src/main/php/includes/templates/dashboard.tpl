
<div class = "blockContainer">
{if empty($listInstances)}
	<p>This dashboard is empty. Select "Create Widget Instance" from the Dashboard menu.</p>
{else}
		{foreach from = $listInstances item = widget}
			{if $widget.instance->isShown()}
			<div class = "block">
				<h3>{$widget.instance->getTitle()|default:'Untitled Widget'}</h3>

				{if $drawNavigation}
					{include file = "links.tpl" links = $widget.instance->getLinks() skipTitle = true}
				{/if}

				{$widget.instance->render()}
			</div>
			{/if}
		{/foreach}
{/if}
</div>

{if !empty($hiddenWidgets)}
<h3>Hidden Widgets</h3>
{foreach from = $hiddenWidgets item = itemWidget}
	{$itemWidget.instance->getTitle()}. 
{/foreach}
{/if}

<script type = "text/javascript">
{literal}
$(document).ready(function() {
	layoutBoxes();
	toggleGroups();
});
{/literal}
</script>

