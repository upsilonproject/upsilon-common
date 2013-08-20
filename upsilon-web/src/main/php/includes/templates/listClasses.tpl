{if $tutorialMode}
<div class = "box tutorialMessage">
	<p><strong>Classes</strong> define a standard set of service requirements. By using classes, it is easy to identify what is <em>not</em> being currently checked.<p>
	<p>For example, <tt>database servers</tt> are a class, which should have the requirements of <tt>free disk space</tt>, a running <tt>database service</tt> and <tt>recent software updates</tt>.</p>
</div>
{/if}

<div class = "box" style = "vertical-align: top;">
		<div style = "display:inline-block; width: 40%; vertical-align: top;">
			<h3>Detail</h3>
			<p><strong>Title:</strong> {$itemClass.title}</p>
		</div>

		{if $listRequirements|@count gt 0}
		<div style = "display: inline-block; width: 40%; vertical-align: top;">
			<h3>Requirements ({$listRequirements|@count})</h3>

			<ul>
			{foreach from = $listRequirements item = itemRequirement}
				<li>{$itemRequirement.title} [<a href = "deleteClassRequirement.php?requirement={$itemRequirement.id}">X</a>]</li>
			{/foreach}
			</ul>
		</div>
		{/if}
</div>

<div class = "box">
<h3>Sub Classes</h3>

{if $listClasses|@count eq 0}
	<p>No child classes.</p>
{else}
	<p style = "text-align: left">
	{include file = "listClassesTree.tpl" listClasses = $listClasses}
	</p>
{/if}
</div>

<div class = "box">
<h3>All Instances</h3>

{if $listInstances|@count == 0}
<p>No class instances.</p>
{else}
<table class = "dataTable hover">
	<thead>
		<tr>
			<th>ID</th>
			<th>Instance title</th>
			<th>Good / Assigned</th>
			<th>Assigned / Requirements</th>
		</tr>
	</thead>

	<tbody>
		{foreach from = $listInstances item = itemInstance}
		<tr>
			<td>{$itemInstance.id}</td>
			<td><a href = "viewClassInstance.php?id={$itemInstance.id}">{$itemInstance.title}</a></td>
			<td class = "{$itemInstance.assignedKarma}">{$itemInstance.goodCount} out of {$itemInstance.assignedCount}</td>
			<td class = "{$itemInstance.overallKarma}">{$itemInstance.assignedCount} / {$itemInstance.totalCount}</td>
		</tr>
		{/foreach}
	<tbody>
</table>
{/if}
</div>
