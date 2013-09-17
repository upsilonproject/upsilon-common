window.karmaColors = {}
window.karmaColors["GOOD"] = '#90ee90';
window.karmaColors["BAD"] =  '#fa8072';
window.karmaColors["STALLED"] = '#000099';
window.karmaColors["WARNING"] = '#ffa500';
window.karmaColors["UNKNOWN"] = '#666';

function rawPlot(plot, ctx) {
    var data = plot.getData();
    var axes = plot.getAxes();
    var offset = plot.getPlotOffset();

    for (var i = 0; i < data.length; i++) {
        var series = data[i];
        for (var j = 0; j < series.data.length; j++) {
            var d = (series.data[j]);
            var x = offset.left + axes.xaxis.p2c(d[0]);
            var y = offset.top + axes.yaxis.p2c(d[1]);
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.arc(x,y,4,0,Math.PI*2,true);
            ctx.closePath();           
            ctx.fillStyle = window.karmaColors[d[2]]
            ctx.fill();
        }    
    }
}

function labelDateAxis(date) {
	var d = new Date(date * 1000);
	
	return window.stamp.format(d, {selector:"date", datePattern: "HH:mm" });
}

function updateGraph(results) {
	require([
		"dojox/charting/Chart",
		"dojox/charting/themes/Claro",
		"dojo/date/locale",
		"dojox/charting/plot2d/StackedAreas",
		"dojox/charting/axis2d/Default"
	], function(Chart, theme, stamp) {
		window.stamp = stamp;

		$('#graphService' + results.graphIndex).empty();

		/*
		xaxis: {mode: "time", timeformat: "%a\n %H:%M"},
		{colors: ["#cecece", '#cecece'] }
		*/

		var c = new Chart("graphService" + results.graphIndex, {
			title: "Metric: " + results.metric,
			titleFont: "sans-serif",
			axisFont: "sans-serif",
		});
		c.setTheme(theme);
		c.addPlot("default", {
			type: "StackedAreas",
			markers: true,
		});

		c.addAxis("x", {vertical: false, titleOrientation: "away", font: "sans-serif", labelFunc: labelDateAxis });
		c.addAxis("y", {vertical: true, titleOrientation: "axis", font: "sans-serif" });

		$(results.services).each(function(index, service) {
			axisData = []

			$(service.metrics).each(function(index, result) {
				axisData.push({y: result.value, x: result.date})
			});

			c.addSeries("service " + service.serviceId, axisData);
		});

		c.render();


		window.plots[results.graphIndex] = c;
	});
}

function getAxisColor(index) {
	switch(index) {
		default: return "black";
	}
}

window.plots = {};
window.plotMarkings = {};

function fetchServiceMetricResultGraph(metric, id, graphIndex) {
	data = {
		"services": id,
		"metric": metric,
		"graphIndex": graphIndex
	}

	window.serviceResultGraphUrl = 'viewServiceResultGraph.php';

	$.getJSON(window.serviceResultGraphUrl, data, updateGraph);
}


function layoutBoxes() {
	new Masonry('div.blockContainer', {itemSelector: 'div.block', columnWidth: 200, isFitWidth: true });
}

function cookieOrDefault(cookieName, defaultValue) {
	require(["dojo/cookie"], function(cookie) {
		cookieValue = cookie(cookieName)

		if (cookieValue == null) {
			return defaultValue;
		} else {
			return cookieValue;
		}
	});	
}


window.nighttime = cookieOrDefault("nighttime", false);
window.showGoodGroups = cookieOrDefault("groups", false);
window.showEmptyGroups = cookieOrDefault("showEmptyGroups", false);

function toggleEmptyGroups() {
	$('.metricGroup').each(function(index,container) {
		container = $(container);
		var services = container.find('.metricList li');

		if (!window.showEmptyGroups && services.size() == 0) {
			container.hide();
		}
	});
}

function toggleNightVision() {
	window.nighttime = !window.nighttime;
	var stylesheet = $('link[title=nighttime]');

	if (window.nighttime) {
		$(stylesheet).attr('rel', 'stylesheet');
	} else {
		$(stylesheet).attr('rel', 'disabled');
	}
}

function toggleSingleGroup(group) {
//	console.log(group);
}

function toggleGroups() {
	var desc = $('.metricListContainer').each(function(index,container) {
		container = $(container);
		var desc  = container.find('.metricListDescription');
		var services = container.find('.metricList li');

		desc.empty();
		services.show();
		services.children().show();

		var servicesGood = services.find('div span.metricIndicator.good').parent().parent('li');
		var servicesBad = services.find('div span.metricIndicator.bad').parent().parent('li');
		var servicesSkipped = services.find('div span.metricIndicator.skipped').parent().parent('li');
		var servicesWarning = services.find('div span.metricIndicator.warning').parent().parent('li');

		if (!window.showGoodGroups) {
			if ((servicesGood.size() + servicesWarning.size()) == services.size()) {
				servicesGood.hide();
				servicesWarning.hide();
				desc.append($('<div style = "display:inline-block"><span class = "metricIndicator good grouped">~</span></div> <div class = "metricText">All <strong>' + servicesGood.size() + '</strong> services are good.</div>'));

				if (servicesWarning.size() > 0) {
					desc.append(' <br /><span class = "warning"><strong>' + servicesWarning.size() + '</strong> have a warning</span>.')
				}

				desc.click(toggleSingleGroup)
			}

			if (servicesSkipped.size() > 0) {
				servicesSkipped.hide();
				desc.append($('<div style = "display:inline-block"><span class = "metricIndicator skipped grouped">~</span></div> <div class = "metricText">Skipped <strong>' + servicesSkipped.size() + '</strong> services</div>'));
			}			
		}
	});
}

window.shortcutToggleNighttime = 78;
window.shortcutToggleEmptyGroups = 77;
window.shortcutToggleGroups = 71;

require(["dojo/dom-construct", "dojo/on", "dojo/query", "dojo/keys", "dojo/domReady!"],
function(domConstruct, on, query, keys) {
        query("body").on("keydown", function(event) {
               if (event.target.localName != "body") {
                        return;
                }

		if (event.ctrlKey) {
			return;
		}

                switch (event.keyCode) {
                case window.shortcutToggleNighttime:
                        event.preventDefault();
                        toggleNightVision();
                        break;
                case window.shortcutToggleGroups:
                        event.preventDefault();

                        window.showGoodGroups = !window.showGoodGroups;

                        toggleGroups();
                        layoutBoxes(false);

                        break;
                case window.shortcutToggleEmptyGroups:
                        event.preventDefault();

                        window.shortcutToggleEmptyGroups = !window.shortcutToggleEmptyGroups;

                        toggleEmptyGroups();

                        break;
                }
        });
});


function setupCollapseableForms() {
	$('p.collapseable').each(function(index,sectionTitle) {
		sectionTitle = $(sectionTitle);

		var list = $('<div />');

		list.append(sectionTitle.nextUntil('p.collapseable', 'fieldset'));

		sectionTitle.after(list);

	});
}

function setupEnhancedSelectBoxes() {
	require(["dojo/query", "dijit/form/Select", "dojo/_base/array"], function(query, Select, array) {
		var selects = query("select");

		array.forEach(selects, function(entry, index) {
	//		new Select({}, entry);
		});
	});
}


function setupSortableTables() {
	return;

	$('table.dataTable').dataTable({
		'sDom': 'flpitpil',
		'aaSorting': [[ 1, 'desc ']],
		'oLanguage': {
		'oPaginate': {
			'sNext': '&nbsp;',
			'sPrevious': '&nbsp;'
		}
		}
	});

	$('a.paginate_enabled_next').html('&nbsp;');
	$('a.paginate_enabled_previous').html('&nbsp;');
	$('a.paginate_disabled_next').html('&nbsp;');
	$('a.paginate_disabled_previous').html('&nbsp;');
}

function serviceIconChanged() {
	var icon = $('select#updateMetadata-icon').val();

	if (icon != '') {
		icon = 'resources/images/serviceIcons/' + icon;
		
		$('span#serviceIconPreview').html('<img src = "' + icon + '" alt = "serviceIcon" />');
	}
}

function menuButtonClick(address) {
	// Hide your eyes. This will be temporary.
	if (address.indexOf(".php") != -1) {
		window.location = address;
	} else {
		eval(address);
	}
}

function requestRescanWidgets() {
	var proBar = new dijit.ProgressBar();
	proBar.placeAt("body");

	proBar.set("value", 50);
}

function renderServiceList(data, stuff, req) {
	container = $('.widgetRef' + req.htmlRef);
	container.addClass('metricListContainer');
	container.empty();

	list = $('<ul class = "metricList" />');
	container.append(list);

	$(data).each(function(index, service) {
		metric = $('<li />');
		indicator = $('<span class = "metricIndicator" />');
		indicator.addClass(service.karma.toLowerCase());

		if (service.icon != null) {
			indicator.append($('<img src = "resources/images/serviceIcons/' + service.icon + '" /><br />'));
		}

		indicator.append('<span>' + service.goodCount + '</span>');
		indicator = $('<div class = "metricIndicatorContainer" />').append(indicator);

		metric.append(indicator);

		text = $('<div class = "metricText" />');
		text.append('<span class = "metricDetail">' + service.estimatedNextCheckRelative + '</span>');
		text.append('<a href = "viewService.php?id=' + service.id + '"><span class = "metricTitle">' + service.alias + '</span></a>');
		metric.append(text);

		metric.append

		list.append(metric);
	});

	layoutBoxes();
	toggleGroups();
}

function updateMetricList(url, ref, callback, qp, repeat) {
	var fn = function() {
		var req = $.ajax({
			url: url,
			success: callback,
			failure: window.alert,
			dataType: 'json',
			data: qp
		});

		req.htmlRef = ref;
	}
	
	fn();

	if (repeat > 0) {
		setInterval(fn, repeat);
	}
}
