{if not empty($sub)}
<div data-dojo-type = "dijit/DropDownMenu">
{else}
<div data-dojo-type = "dijit/MenuBar">
{/if}
	{if not isset($skipTitle)}
		<div data-dojo-type = "dijit/MenuItem">
			{$links->getTitle()}
		</div>
	{/if}

	{foreach from = $links item = link}

		{if count($link.children) > 0}
			<div data-dojo-type = "dijit/PopupMenuBarItem">
				<span>{$link.title}</span>

				{include file = "links.tpl" links = $link.children skipTitle = true sub = true}
			</div>
		{else}
			{if not empty($sub)}
			<div data-dojo-type = "dijit/MenuItem" data-dojo-props = "onClick: function() {literal}{{/literal} window.location = '{$link.url}' {literal}}{/literal}" >
			{else}
			<div data-dojo-type = "dijit/MenuBarItem" data-dojo-props = "onClick: function() {literal}{{/literal} window.location = '{$link.url}' {literal}}{/literal}" >
			{/if}
				<span>{$link.title}</span>
			</div>
		{/if}

	{/foreach}
</div>
