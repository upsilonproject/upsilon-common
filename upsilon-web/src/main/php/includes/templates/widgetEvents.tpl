<h2>Events ({$events|@count})</h2>
<ul>
{foreach from = $events item = itemEvent}
	<li><strong>{$itemEvent.start}</strong> - {$itemEvent.title} {if not empty($itemEvent.source)}<span class = "subtle">({$itemEvent.source})</span>{/if}</li>
{/foreach}
</ul>
