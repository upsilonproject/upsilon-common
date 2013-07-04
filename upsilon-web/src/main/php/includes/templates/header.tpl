<!DOCTYPE html>

<html>
<head>
	<title>Upsilon</title>


	{if not $mobile}
	<meta http-equiv = "refresh" content = "60" />
	{/if}

	{if $mobile}
	<link rel = "alternative stylesheet" type = "text/css" href = "resources/stylesheets/mobile.css" title = "mobile" />
	{else}
	<link rel = "stylesheet" type = "text/css" href = "http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/black-tie/jquery-ui.css" />
	<link rel = "stylesheet" type = "text/css" href = "resources/stylesheets/select2.css" />
	<link rel = "stylesheet" type = "text/css" href = "resources/stylesheets/main.css" />
	{/if}
	<link rel = "{if $isNighttime}stylesheet{/if}" type = "text/css" href = "resources/stylesheets/main-nighttime.css" title = "nighttime" />

	<link rel = "shortcut icon" href = "resources/images/icons/logo96pxblackbg.png" title = "Shortcut icon" type = "image/png" />

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js" type="text/javascript"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script src="resources/javascript/jquery.cookie.js"></script>
	<script src="resources/javascript/hud.js"></script>

	<link rel="stylesheet" href="resources/dojo/dijit/themes/claro/claro.css" />

	<script src="resources/dojo/dojo/dojo.js"></script>
	</head>

<body class = "{if $mobile}mobile{else}full{/if} claro">
<div id = "header">
	{if $drawHeader}
	<div class = "title">
		<h1>
			<a href = "index.php">Upsilon</a>
			&raquo;
			<span class = "pageTitle">{$title|default:'Untitled page'}</span>
		</h1>
	</div>
	{/if}
</div>
	{if $drawNavigation}
	<div class = "navigationMenuItems">
		<div>
		{if $generalLinks->hasLinks()}
			{include file = "links.tpl" links = $generalLinks skipTitle = true}
		{/if}
		</div>
	</div>
	{/if}

{if $isNighttime && $drawBigClock}<p style = "margin: 0; font-size:9em; font-weight: bold; background-color: white; color: black;">{$datetime}</p>{/if}
<div id = "content">
	
