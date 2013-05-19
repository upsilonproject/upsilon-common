<h2>nodes</h2>{foreach from = $listNodes item = node}<p><span class = "{$node.karma|strtolower} metricIdicator">{$node.identifier}</span></p>{/foreach}

