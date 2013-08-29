<!DOCTYPE html>

<html>
<head>
	<title>Upsilon &raquo; {$title|default:'Untitled page'}</title>


	{if not $mobile}
	<meta http-equiv = "refresh" content = "60" />
	{/if}

	{if $mobile}
	<link rel = "alternative stylesheet" type = "text/css" href = "resources/stylesheets/mobile.css" title = "mobile" />
	{else}
	<link rel = "stylesheet" type = "text/css" href = "resources/stylesheets/main.css" />
	{/if}
	<link rel = "{if $isNighttime}stylesheet{/if}" type = "text/css" href = "resources/stylesheets/main-nighttime.css" title = "nighttime" />

	<link rel = "shortcut icon" href = "resources/images/icons/logo96pxdarkbg.png" title = "Shortcut icon" type = "image/png" />

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

	<script src="resources/javascript/masonry.js"></script>
	<script src="resources/dojo/dojo/dojo.js"></script>
	<script src="resources/javascript/hud.js"></script>

	<link rel="stylesheet" href="resources/dojo/dijit/themes/claro/claro.css" />

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
	
