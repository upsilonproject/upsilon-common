<h2>Events ({$events|@count})</h2>
{if empty($events)}
	<p>0 events.</p>
{else}
<ul>
{foreach from = $events item = itemEvent}
	<li><strong>{$itemEvent.start|date_format:$dateFormat}</strong> - {$itemEvent.title} {if not empty($itemEvent.source)}<span class = "subtle">({$itemEvent.source})</span>{/if}</li>
{/foreach}
</ul>
{/if}
