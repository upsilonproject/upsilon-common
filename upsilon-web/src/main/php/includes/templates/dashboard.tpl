
<div class = "blockContainer">
{if empty($listInstances)}
	<p>This dashboard is empty. Select "Create Widget Instance" from the Dashboard menu.</p>
{else}
		{foreach from = $listInstances item = widget}
			<div class = "block">
				<div class = "menu">	
				<h3>{$widget.instance->getTitle()|default:'Untitled Widget'}</h3>
				{if $drawNavigation}
				{include file = "links.tpl" links = $widget.instance->getLinks() skipTitle = true}
				{/if}

				</div>

				{$widget.instance->render()}
			</div>
		{/foreach}
{/if}
</div>

<script type = "text/javascript">
{literal}
$(document).ready(function() {
	layoutBoxes();
	toggleGroups();
});
{/literal}
</script>

