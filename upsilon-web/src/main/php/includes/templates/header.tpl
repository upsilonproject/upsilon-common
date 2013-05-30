<!DOCTYPE html>

<html>
<head>
	<title>Upsilon</title>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js" type="text/javascript"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
	<script src="resources/javascript/jquery.masonry.min.js"></script>
	<script src="resources/javascript/jquery.flot.js"></script>
	<script src="resources/javascript/jquery.flot.time.min.js"></script>
	<script src="resources/javascript/jquery.cookie.js"></script>
	<script src="resources/javascript/jquery.svg.js"></script>
	<script src="resources/javascript/jquery.dataTables.min.js"></script>
	<script src="resources/javascript/jquery.jsplmb.js"></script>
	<script src="resources/javascript/jquery.select2.min.js"></script>

	{if not $mobile}
	<meta http-equiv = "refresh" content = "60" />
	{/if}

	{if $mobile}
	<link rel = "alternative stylesheet" type = "text/css" href = "resources/stylesheets/mobile.css" title = "mobile" />
	{else}
	<link rel = "stylesheet" type = "text/css" href = "resources/stylesheets/main.css" />
	<link rel = "stylesheet" type = "text/css" href = "//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js" />
	<link rel = "stylesheet" type = "text/css" href = "resources/stylesheets/select2.css" />
	{/if}
	<script src="resources/javascript/hud.js"></script>
	<link rel = "{if $isNighttime}stylesheet{/if}" type = "text/css" href = "resources/stylesheets/main.nighttime.css" title = "nighttime" />
</head>

<body class = "{if $mobile}mobile{else}full{/if}">
<div id = "header">
	{if $drawHeader}
	<div class = "title">
		<h1><a href = "index.php">Upsilon</a></h1>
	</div>
	{/if}

	{if $drawNavigation}
	<div class = "userMenuItems">
		{if $loggedIn}
		<div class = "menu">
		Logged in as <strong>{$username}</strong>
		{include file = "links.tpl" links = $userLinks skipTitle = true}
		</div>

		{if $enableDebug}
		<a href = "viewDebugInfo.php">Debug</a> | 
		{/if}
		{/if}
	</div>
	{/if}
</div>
	{if $drawNavigation}
	<div class = "navigationMenuItems">
		{if isset($links)}
			<div class = "menu"><h2 class = "menu">
				{$title|default:'Untitled page'}</h2>
				{if isset($links)}
					{include file = "links.tpl" links = $links skipTitle = true}
				{/if}
			</div>
		{else}
			<h2>{$title|default:'Untitled page'}</h2>
		{/if}

		<div style = "display: inline-block; float: right;">
		{if $loggedIn}
		<a href = "index.php">Service HUD</a> | 
		<a href = "viewDashboard.php?id=1">Dashboard</a> | 
		<a href = "listClasses.php?">Classes</a> | 
		<a href = "listNodes.php">Nodes</a> | 
		<a href = "viewTasks.php">Tasks</a> |
		<a href = "viewList.php">List</a>
		<a href = "viewList.php?problems">(Problems)</a> |
		<a href = "viewRoom.php?id=1">Rooms</a> |
		<a href = "listGroups.php">Groups</a> 
		{/if}
		</div>
	</div>
	{/if}

{if $isNighttime && $drawBigClock}<p style = "margin: 0; font-size:9em; font-weight: bold; background-color: white; color: black;">{$datetime}</p>{/if}
<div id = "content">
	
