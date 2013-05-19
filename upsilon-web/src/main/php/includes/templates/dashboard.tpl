
<div class = "blockContainer">
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
</div>

<script type = "text/javascript">
layoutBoxes();
toggleGroups();
</script>
