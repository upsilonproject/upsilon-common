<div class = "box">
	<h2>Usergroup: {$itemUsergroup.title}</h2>

	<p>ID: {$itemUsergroup.id}</p>

	<h3>Members</h3>
	{foreach from = $listMembers item = itemMember}
		<p>{$itemMember.username} <a href = "deleteUserGroupMembership.php?user={$itemMember.userId}&group={$itemUsergroup.id}">Delete</a></p>
	{/foreach}
</div>
