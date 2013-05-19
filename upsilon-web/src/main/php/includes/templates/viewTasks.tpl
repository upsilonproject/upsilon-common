<div class = "box">
	<h3>Tasks</h3>

<table class = "taskList">
	<tr>
		<th>High Importance, High Urgency ({$tasks.hihu|@count})</th>
		<th>High Importance, Low Urgency ({$tasks.hilu|@count})</th>
	</tr>
	<tr>
		<td>
			{include file = "taskList.tpl" tasks = $tasks.hihu}
		</td>

		<td>
			{include file = "taskList.tpl" tasks = $tasks.hilu}
		</td>

	</tr>
	<tr>
		<th>Low Importance, High Urgency ({$tasks.hihu|@count})</th>
		<th>Low Importance, Low Urgency ({$tasks.hilu|@count})</th>
	</tr>

	<tr>
		<td>
			{include file = "taskList.tpl" tasks = $tasks.lihu}
		</td>

		<td>
			{include file = "taskList.tpl" tasks = $tasks.lilu}
		</td>
	</tr>
</table>
</div>
