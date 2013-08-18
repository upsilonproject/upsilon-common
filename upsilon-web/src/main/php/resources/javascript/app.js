function onLoad() {
	reqLogin();
	setupToolbar();
	setupWindowMenu();
	createDashboardWidget();
}

function createDashboardWidget() {
require([
	"dojo/dom-construct"
], function(domConstruct) {
	var node = domConstruct.create("div", {style: { backgroundColor: "red" }}, win.body());
	console.log(node);
});}

function main() {
	require([
		"dojo/request",
		"dojo/domReady!"
	], function() {
		onLoad();
	});
}
 
function applyPermissionsToToolbar(permissions) {
	require(["dijit/registry"], function(registry){
		registry.byId("mniDashboard").set("disabled", !permissions.viewDashboard);
		registry.byId("mniServices").set("disabled", !permissions.viewServices);
	});
} 

function loadUpdatePermissions(perms) {
	window.permissions = perms;
	
	applyPermissionsToToolbar(perms);
} 

function reqUpdatePermissions() {
	var req = {
		url: "json/sessionPermissions",
		handleAs: "json",
		load: function (perms) {
			loadUpdatePermissions(perms);
		},
		error: function (error) {
			displayError(error);
		}
	}; 
	
	dojo.xhrGet(req);
}

function displayError(err) {
	window.alert(err);
} 

function initGridNodes() {
	require([
	    "gridx/Grid",
		"dojo/store/Memory",
		"gridx/core/model/cache/Sync",
		"gridx/modules/VirtualVScroller"
	], function (Grid, Store, Cache, scroller) {
		grid = new Grid({
			id: "gridNodes",
			cacheClass: Cache, 
			store: new Store({data: [{identifier: "foo"}]}),
			structure: [
		        {field: "identifier", name: "Identifier"},
		        {field: "karma", name: "Karma"}
		    ],
		    modules: [
		              scroller
            ]
		    	
		});
		grid.startup();
	});
}
 
function loadListNodes(nodes) {
	require([
	     "dijit/Dialog", 
	     "dijit/registry",
		 "dojo/store/Memory",
	     "dojo/domReady!" 
     ], function (Dialog, registry, Store) {
		if (!registry.byId("gridNodes")) {
			initGridNodes(); 
		} 
		  
		grid = registry.byId("gridNodes"); 
		grid.setStore(new Store({data: nodes}));

		new Dialog({
			title: "List of nodes",
			content: grid,  
			style: "width: 640px; height: 480px",
		}).show(); 
		
	});
}

function mniNodesClicked() {
	var req = {
		url: "json/listNodes",
		handleAs: "json",
		load: loadListNodes,
		error: displayError,
	}
	
	dojo.xhrGet(req);
}

function setupToolbar() {
	require([
		"dijit/MenuBar",
		"dijit/MenuBarItem",
	], function(MenuBar, MenuBarItem) {
		window.mainToolbar = new MenuBar({});

		mainToolbar.addChild(new MenuBarItem({id: "mniDashboard", label: "Dashboard", onClick: mniDashboardClicked }));
		mainToolbar.addChild(new MenuBarItem({id: "mniNodes", label: "Nodes", onClick: mniNodesClicked})); 
		mainToolbar.addChild(new MenuBarItem({id: "mniServices", label: "Services", onClick: mniServicesClicked }));

		mainToolbar.placeAt("wrapper");
		mainToolbar.startup();  
	});
}

function loadGetServices(services) {
	console.log(services);
}

function errorGetServices(err) {
	window.alert("err get services" + err);
}

function mniDashboardClicked() {
	reqDashboard(1);
}

function reqLogin() {
	var req = {
		url: "json/authenticate",
		handleAs: "json",
		query: {
			username: "administrator",
			password: window.prompt("Password?"),
		},
		load: function(res) {
			console.log("authenticated");
			reqUpdatePermissions();
		},
		error: function(err) {
			displayError("Cannot auth"); 
		}
	}

	dojo.xhrGet(req);
}

function newJsonReq() {
	return {
		handleAs: "json", 
		error: displayError,
		get: function() {
			dojo.xhrGet(this); 
		} 
	}
}

function renderWidgetEvents() {}

function renderWidgetProblemServices(widget, container) {
	var req = newJsonReq();
	req.url = "json/getServices";
	req.load = function(services) {
		list = "<h2>Problem Services</h2>";
		
		dojo.forEach(services, function(service) {
			list += '<span class = "metricIndictator ' + service.karma.toLowerCase() + '">' + service.karma + '</span> ' + service.identifier + "<br />"; 
		}); 
		
		container.set("content", list); 
	};
	req.get();  
}

function renderWidgetNodes(widget, container) {
	console.log(widget, container);  
	var req = { 
			url: "json/listNodes",
			load: function(nodes) {
				nodeList = "";
				
				dojo.forEach(nodes, function(node) { 
					nodeList += '<span class = "metricIndictator ' + node.karma.toLowerCase() + '">' + node.karma + '</span> ' + node.identifier + "<br />"; 
				});
				
				container.set("content", nodeList);
			},
			error: displayError,
			handleAs: "json"
	}
	
	dojo.xhrGet(req);
}

function loadDashboard(dashboard) {
	require([
	    "dijit/layout/StackContainer",
	    "dijit/layout/ContentPane",
	    "dijit/registry"
    ], function(Container, ContentPane, registry){
		if (!registry.byId("dashboardWidgetContainer")) {
			var container = new Container({id: "dashboardWidgetContainer"});
			container.placeAt("wrapper"); 
		}
		 
		var container = registry.byId("dashboardWidgetContainer");
		
		dojo.forEach(dashboard.widgetInstances, function(widget) {
			if (!registry.byId("widget" + widget.id)) {
				var widgetContent = new ContentPane({
					id: "widget" + widget.id,
					style: "border: 1px solid black; width: 320px;"
				});   
				widgetContent.set("content", "title:" + widget.class);
				container.addChild(widgetContent);
			}   
			
			var cp = registry.byId("widget" + widget.id);
			var renderFunction = "renderWidget" + widget.class;
			window[renderFunction](widget, cp);
		});

	});
}

function reqDashboard() {
	var req = {
		url: "json/getDashboard",
		handleAs: "json",
		query: { id: 1 }, 
		load: loadDashboard,
		error: displayError
	}; 
	
	dojo.xhrGet(req);
}

function reqGetServices() {
	var req = {
		url: "json/getServices",
		handleAs: "json",
		load: "loadGetServices",
		error: "errorGetServices"
	}

	dojo.xhrGet(req);
}

function mniServicesClicked() {}

function setupWindowMenu() {
	require([
		"dijit/Menu",
		"dijit/MenuItem",
		"dijit/MenuSeparator",
		"dijit/PopupMenuItem",
		"dojo/domReady!"
	], function(Menu, MenuItem, CheckedMenuItem, MenuSeparator, PopupMenuItem){
		var menu = new Menu({
			contextMenuForWindow: true
		});

		menu.addChild(new MenuItem({
			label: "Hi",
		}));

		menu.startup();
	});
}
