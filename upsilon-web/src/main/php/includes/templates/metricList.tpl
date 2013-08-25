{if empty($listServices)}
	<p>No metrics</p>
{else}
	<div class = "metricListContainer">
	<p class = "metricListDescription"></p>

	<ul class = "metricList">
		{assign var = "countSkipped" value = "0"}

		{foreach from = $listServices item = "itemService"}
				<li>
					<div class = "metricIndicatorContainer">
					<span class = "metricIndicator {$itemService.karma|strtolower}">
						{if !$mobile && !empty($itemService.icon)} 
							<img src = "resources/images/serviceIcons/{$itemService.icon}" alt = "serviceIcon" style = "padding-top: 4px" /><br />
						{/if}
						{$itemService.goodCount|default:'?'}
					</span>

					{if false && $itemService.karma != "good" && $itemService.stabilityProbibility > 0}
					<br />
					<span style = "font-size: x-small; text-align: center; border: 0;" class = "metricIndicator">{$itemService.stabilityProbibility}%</span>
					{/if}
					</div>

					<div class = "metricText">
					{if isset($itemService.isOverdue) && !$mobile}
						{if $itemService.isOverdue}
							<span class = "metricDetail"><em>overdue by {$itemService.estimatedNextCheckRelative}</em></span>
						{else}
							<span class = "metricDetail">{$itemService.estimatedNextCheckRelative}</span>
						{/if}
					{/if}

					{if $drawNavigation}<a href = "viewService.php?id={$itemService.id}">{/if}
						<span class = "metricTitle" title = "{$itemService.description}">{if empty($itemService.alias)}{$itemService.description|default:'nodesc'|truncate:18}{else}{$itemService.alias}{/if}{if empty($itemService.alias) && isset($itemService.executableShort)}cmd:{$itemService.executableShort|default:'nocmd'|truncate:16}{/if}</span>
					{if $drawNavigation}</a>{/if}
					{if not empty($itemService.output) && !$mobile}
					<p class = "metricOutput"><small>{$itemService.output|truncate:32}</small></p>
					{/if}

					{if !$mobile && isset($itemService.listActions) && $itemService.listActions|@count > 0}
						<small>
						{foreach from = $itemService.listActions item = itemAction}
							<a href = "{$itemAction->url}">{$itemAction->title}</a> 
						{/foreach}
						</small>
					{/if}

					{if !$mobile && isset($itemService.listSubresults) && $itemService.listSubresults|@count > 0}
					<ul class = "subresults">
						{foreach from = $itemService.listSubresults item = itemSubresult}
							<li><span class = "metricIndicator {$itemSubresult.karma|strtolower}">&nbsp;</span><span class = "metricTitle">{$itemSubresult.name|default:'ERR: Name not provided in subresult.'}</span></li>
						{/foreach}
						</ul>
					{/if}
	
					</div>
				</li>
		{/foreach}
	</ul>
	</div>
{/if}
