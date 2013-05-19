<div class = "taskList">
	{if $tasks|@count > 0}
		<ul>
		{foreach from = $tasks item = task}
			<li>{$task.title}</li>
		{/foreach}
		</ul>
	{else}
		<p><i>empty</i></p>
	{/if}
</div>
