{if not isset($skipTitle)}
<div style = "float: left; vertical-align: top;">
	<div class = "menu">
		{$links->getTitle()}
{/if}

		<ul class = "submenu">
		{foreach from = $links item = link}
			<li><a href = "{$link.url}">{$link.title}</a></li>
		{/foreach}
		</ul>
{if not isset($skipTitle)}
	</div>
</div>
{/if}

