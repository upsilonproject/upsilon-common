<h3>Room: {$itemRoom.title}</h3>

<div id = "roomContainer{$itemRoom.id}">
{$svgContent}
</div>

<script type = "text/javascript">
{literal}

var walls = $('svg g[inkscape\\:label="walls"] *');
var voids = $('svg g[inkscape\\:label="voids"] *');
var floor = $('svg g[inkscape\\:label="floors"] *');
var opens = $('svg g[inkscape\\:label="openings"] *');

walls.attr('style', '');
voids.attr('style', '');
floor.attr('style', '');
opens.attr('style', '');

{/literal}
</script>

<script type = "text/javascript">
{literal}

window.maxRoomServiceIdentifierLength = 14;

function addStringEllipsis(longString) {
	if (longString.length > window.maxRoomServiceIdentifierLength) {
		return longString.substring(0, window.maxRoomServiceIdentifierLength) + "\u2026";
	} else {
		return longString;
	}
}

function getServiceColor(karma) {
	switch(karma) {
		case 'GOOD': return 'lightgreen';
		case 'BAD': return 'salmon';
		default: return 'gray';
	}
}

function addServiceMarker(roomSvg, x, y, karma, identifier, id) {
	var marker = roomSvg.group();

	var markerIcon = roomSvg.rect(marker, x, y, 100, 25, {fill: getServiceColor(karma)});
	var markerLink = roomSvg.link(marker, "viewService.php?id=" + id);
	var markerText = roomSvg.text(markerLink, x + 5, y + 15, addStringEllipsis(identifier), {color: 'black', fill: 'black', fontSize: '10', fontFamily: 'monospace'});
}

function getRoomServices(roomSvg, roomId) {
	$.getJSON("json/getServicesInRoom.php?id=" + roomId, function(ret) {
		$(ret).each(function(index, service) {
			addServiceMarker(roomSvg, parseInt(service.roomPositionX), parseInt(service.roomPositionY), service.karma, service.identifier, service.id);

console.log(roomId, roomSvg);
		});
	});
}

$(document).ready(function() {
	var svgSelector = 'div#roomContainer{/literal}{$itemRoom.id}{literal} svg';

	$(svgSelector).svg({onLoad: function(svg) {
		getRoomServices(svg, {/literal}{$itemRoom.id}{literal});
	}});

});

{/literal}
</script>

