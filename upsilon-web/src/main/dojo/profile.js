  var profile = (function(){
	return {
		basePath:	"../../../var/dojo-release/",
		releaseDir:	"../../target/",
		releaseName: "dojo-upsilon",

		action: "release",

		layerOptimize: "closure", 
		cssOptimize: "comments",
		mini: true,
		stripConsole: "warn",
		selectorEngine:"lite",

		packages:[
			{
				name:"dojo",
				location:"./dojo"
			},
			{
				name:"dijit",
				location:"./dijit"
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
					"dijit/form/Button",
					"dijit/Menu",
					"dijit/MenuBar",
					"dijit/PopupMenuBarItem",
					"dijit/MenuBarItem",
					"dijit/MenuItem", 
					"dijit/PopupMenuItem", 
					"dijit/MenuSeparator",
					"dijit/DropDownMenu",
				],
				customBase: true,
				boot: true
			}
		}
	};
})();
