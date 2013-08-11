<script type = "text/javascript">
{literal}
function renderServiceList(data) {
	alert("yay, services!");
}

function failedServiceList(data) {
	alert("aww, no services today");
}

$.ajax({
	url: '/json/getServices',
	success: renderServiceList,
	failure: failedServiceList,
	dataType: 'json',
});
{/literal}
</script>

