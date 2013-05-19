<div id = "graphService{$serviceId}" class = "graph">

</div>
<script type = "text/javascript">
fetchServiceMetricResultGraph('{$metric}', '{$serviceId}');

{literal}
pm = window.plotMarkings[{/literal}{$serviceId}{literal}] = [];
{/literal}

{foreach from = $yAxisMarkings item = marking}
pm.push({$marking})
{/foreach}
</script>
