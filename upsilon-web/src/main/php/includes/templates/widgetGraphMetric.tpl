	<div id = "graphService{$instanceGraphIndex}" class = "graph">

	</div>
	<script type = "text/javascript">
datasets = []

{foreach from = $listServiceId item = serviceId}
datasets.push({$serviceId});
{/foreach}

	fetchServiceMetricResultGraph('{$metric}', datasets, '{$instanceGraphIndex}');

	{literal}
pm = window.plotMarkings[{/literal}{$instanceGraphIndex}{literal}] = [];
	{/literal}

	{foreach from = $yAxisMarkings item = marking}
pm.push({$marking})

	{/foreach}
	</script>
