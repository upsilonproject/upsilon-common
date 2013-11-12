<br /><div style = "text-align: center">
	<div id = "clock" style = "font-size: x-large; text-align: center;">CLOCK</div>
	<p class = "subtle">Client side time</p>
</div>

<script type = "text/javascript">
	{literal}
	function tick() {
		var txt = window.locale.format(new Date(), {selector:"date", datePattern: "HH:mm:ss" });

		require([
			"dojo/query",
		], function(query) {
			query('#clock')[0].innerHTML = txt;
		});

		setTimeout(function() { tick()}, 1000);
	}

	require([
		"dojo/date/locale",
	], function(locale) {
		window.locale = locale;
		tick();
	});
	{/literal}
</script>
