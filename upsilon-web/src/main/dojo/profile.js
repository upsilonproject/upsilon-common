  var profile = (function(){
	return {
		basePath:	"../../../target/dojo-release/",
		releaseDir:	"../../target/",
		releaseName: "dojo-upsilon",

		action: "release",

		layerOptimize: "shrinksafe", // shrinksafe
		optimize: "shrinksafe", // shrinksafe
		cssOptimize: "comments",
		mini: true,
		stripConsole: "warn",
		selectorEngine: "lite",
		insertAbsMids: false,

		packages:[
			{
				name:"dojo",
				location:"./dojo"
			},
			{
				name:"dijit",
				location:"./dijit"
			},
			{
				name:"dojox",
				location:"./dojox"
			},
			{
				name:"gridx",
				location:"./gridx"
			}
		],


		layers: {
			"dojo/dojo": {
				include: [
					"dojo/dojo",
					"dojo/main",
					"dojo/dom", 
					"dojo/domReady",
					"dojo/request/xhr",
					"dojo/parser",
					"dijit/_base",
					"dijit/_base/focus",
					"dijit/_base/place",
					"dijit/_base/popup",
					"dijit/_base/scroll",
					"dijit/_base/sniff",
					"dijit/_base/typematic",
					"dijit/_base/wai",
					"dijit/_base/window",
					"dijit/form/Button",
					"dijit/form/FilteringSelect",
					"dijit/Menu",
					"dijit/MenuBar",
					"dijit/PopupMenuBarItem",
					"dijit/MenuBarItem",
					"dijit/MenuItem", 
					"dijit/PopupMenuItem", 
					"dijit/MenuSeparator",
					"dijit/DropDownMenu",
					"dijit/selection",
					"dijit/WidgetSet",
					"dijit/typematic",
					"dojox/grid/DataGrid",
					"dojo/cookie",
					"dojo/regexp",
					"dijit/form/Select",
					"dijit/form/_FormSelectWidget",
					"dojox/charting/plot2d/Lines",
					"dojox/charting/plot2d/Default",
					"dojox/charting/plot2d/Base", 
					"dojox/charting/Series",
					"gridx/Grid",
					"dojo/date",
					"dojo/date/locale",
					"dojo/cldr/supplemental",
					"dojox/charting/Chart",
					"dojox/gfx/svg",
					"dojox/gfx/path",
					"dojo/colors",
					"dijit/form/DropDownButton",
					"dijit/form/ComboButton",
					"dijit/form/ToggleButton",
					"dijit/form/_ToggleButtonMixin",
					"dojo/cldr/nls/gregorian",
					"dojo/cldr/nls/en/gregorian",
					"dojox/charting/themes/Claro",
					"dojox/charting/Theme",
					"dojox/color/_base",
					"dojox/color/Palette",
					"dojox/charting/themes/common",
					"dojox/charting/plot2d/StackedAreas",
					"dojox/charting/plot2d/Stacked",
					"dojox/charting/plot2d/commonStacked",
					"dojox/charting/plot2d/Default",
					"dojox/charting/axis2d/Invisible",
					"dojox/charting/plot2d/Base",
				],
				customBase: true,
				boot: true
			}
		}
	};
})();
