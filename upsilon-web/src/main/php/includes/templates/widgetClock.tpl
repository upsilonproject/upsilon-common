<div id = "clock" style = "font-size: x-large; text-align: center;">CLOCK</div>

<script type = "text/javascript">
{literal}
function tick() {
	var txt = window.locale.format(new Date(), {selector:"date", datePattern: "HH:mm" });
	$('#clock').text(txt);
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
