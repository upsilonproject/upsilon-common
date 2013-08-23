{if count($tasks.hihu) == 0 && count($tasks.hilu) == 0 && count($tasks.lihu) == 0 && count($tasks.lilu) == 0}
	<p>There are 0 tasks to display.</p>
{else}
	{if count($tasks.hihu) > 0}
		<h2>High Importance, High Urgency</h2>
		{include file = "taskList.tpl" tasks = $tasks.hihu}
	{/if}

	{if count($tasks.hilu) > 0}
		<h2>High Importance, Low Urgency</h2>
		{include file = "taskList.tpl" tasks = $tasks.hilu}
	{/if}

	{if not empty($tasks.lihu)}
		<h2>Low Importance, High Urgency</h2>
		{include file = "taskList.tpl" tasks = $tasks.lihu}
	{/if}

	{if not empty($tasks.lilu)}
		<h2>Low Importance, Low Urgency</h2>
		{include file = "taskList.tpl" tasks = $tasks.lilu}
	{/if}
{/if}
