function onLoad() {
	setupHeader();
	setupToolbar();

	reqUpdatePermissions();
}

function main() {
	require([
		"dojo/request",
		"dojo/domReady!"
	], function() {
		onLoad();
	});
}
 
function applyPermissionsToToolbar() {
	require([
		"dijit/registry"
	], function(_registry){
		permissions = window.permissions;

		_registry.byId("mniDashboard").set("disabled", !permissions.viewDashboard);
		_registry.byId("mniServices").set("disabled", !permissions.viewServices);
		_registry.byId("mniNodes").set("disabled", !permissions.viewNodes);
		_registry.byId("mniLogout").set("disabled", !permissions.loggedIn);
		_registry.byId("mniLogin").set("disabled", permissions.loggedIn);
	});
} 

function loadUpdatePermissions(perms) {
	window.permissions = perms;
	
	applyPermissionsToToolbar(perms);
} 

function reqUpdatePermissions() {
	window.permissions = { loggedIn: false };

	var req = newJsonReq();
	req.url = "json/sessionPermissions";
	req.load = loadUpdatePermissions;
	req.error = applyPermissionsToToolbar;
	req.get();
}

function displayError(err) {
	window.alert("General Error: " + err);
} 

function initGridNodes() {
	require([
		"gridx/Grid",
		"dojo/store/Memory",
		"gridx/core/model/cache/Sync",
		"gridx/modules/VirtualVScroller",
		"gridx/modules/ColumnResizer",
		"gridx/modules/Filter",
		"gridx/modules/filter/FilterBar",
	], function (Grid, Store, Cache, scroller, resizer, filter, filterBar) {
		grid = new Grid({
			id: "gridNodes",
			cacheClass: Cache, 
			store: new Store({data: [{identifier: "foo"}]}),
			structure: [
				{field: "identifier", name: "Identifier"},
				{field: "karma", name: "Karma"},
				{field: "instanceApplicationVersion", name: "Version", hidden: true},
				{field: "nodeType", name: "Type"},
				{field: "serviceCount", name: "Service count"},
				{field: "lastUpdated", name: "Last updated Relative"}
			],
			modules: [
		              scroller, resizer, filter, filterBar
            		]
		    	
		});

		grid.filterBar.closeButton = false;
		grid.filterBar.refresh();
		console.log(grid.filterBar.closeButton);
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
			resizable: true,
			style: "width: 640px; height: 480px",
		}).show(); 
		
	});
}

function mniNodesClicked() {
	var req = newJsonReq();
	req.url = "json/listNodes",
	req.load = loadListNodes,
	req.get();
}

function mniLogoutClicked() {
	req = newJsonReq();
	req.url = "json/logout";
	req.load = loadLogout;
	req.get();
}

function setupToolbar() {
	require([
		"dijit/MenuBar",
		"dijit/MenuBarItem",
	], function(MenuBar, MenuBarItem) {
		window.mainToolbar = new MenuBar({});
		mainToolbar.placeAt("wrapper");
		mainToolbar.startup();  

		mainToolbar.addChild(new MenuBarItem({id: "mniDashboard", label: "Dashboard", onClick: mniDashboardClicked, disabled: true }));
		mainToolbar.addChild(new MenuBarItem({id: "mniNodes", label: "Nodes", onClick: mniNodesClicked, disabled: true})); 
		mainToolbar.addChild(new MenuBarItem({id: "mniServices", label: "Services", onClick: mniServicesClicked, disabled: true }));
		mainToolbar.addChild(new MenuBarItem({id: "mniLogout", label: "Logout", onClick: mniLogoutClicked, disabled: true }));
		mainToolbar.addChild(new MenuBarItem({id: "mniLogin", label: "Login", onClick: showFormLogin, disabled: true }));

	});
}

function loadGetServices(services) {
	console.log(services);
}

function errorGetServices(err) {
	window.alert("err get services" + err);
}

function mniDashboardClicked() {
	reqDashboard(5);
}

function loadLogout() {
	require([
		"dijit/registry"
	], function (registry) {
		reqUpdatePermissions();
	});
}

function loadLogin(res, a, b, c) {
	reqUpdatePermissions();
}

function showFormLogin() {
	require([
		"dijit/layout/ContentPane",
	], function(container) {
		var username = window.prompt("username?");
		var password = window.prompt("password");
		reqLogin(username, password);
	});
}

function errorLogin() {
	window.alert("Login failed.");
}

function reqLogin(username, password) {
	var req = newJsonReq();
	req.url = "json/authenticate";
	req.content = {
		username: username,
		password: password,
	};
	req.load = loadLogin;
	req.error = errorLogin;
	req.get();
}

function newJsonReq(url) {
	return {
		url: url,
		handleAs: "json", 
		error: displayError,
		get: function() {
			if (!this.url.endsWith(".php")) {
				this.url += ".php";
			}

			dojo.xhrGet(this); 
		} 
	}
}

function renderWidgetEvents() {}
function renderWidgetTasks() {}
function renderWidgetGraphMetrics(widget) {
	console.log(widget);
}
function renderWidgetListMetrics() {}
function renderWidgetListSubresults() {}
function renderWidgetServicesFromGroup() {}

function renderWidgetProblemServices(widget, container) {
	var req = newJsonReq();
	req.url = "json/getServices";
	req.load = function(services) {
		list = "<h2>Problem Services</h2>";

		dojo.forEach(services, function(service) {
			list += '<span class = "metricIndictator ' + service.karma.toLowerCase() + '">' + service.lastChangedRelative + '</span> ' + service.identifier + "<br />"; 
		}); 
		
		container.set("content", list); 
	};
	req.get();  
}

function renderWidgetNodes(widget, container) {
	var req = newJsonReq();
	req.url = "json/listNodes",
	req.load = function(nodes) {
		define([ 
			"dojo/_base/declare",
			"dijit/_WidgetBase",
			"dijit/_TemplatedMixin",
			"dojo/text!/upsilon/upsilon-web/src/main/php/resources/templatesclient/example.tpl"
		], function(declare, _WidgetBase, _TemplatedMixin, tpl) {
			return declare([_WidgetBase, _TemplatedMixin], {
				message: "Hello World"
			});
		});

		nodeList = "<h2>Nodes</h2><ul class = 'metricList'>";
		
		dojo.forEach(nodes, function(node) { 
			nodeList += '<li><div class = "metricIndicatorContainer"><span class = "metricIndicator ' + node.karma.toLowerCase() + '">' + node.karma + '</span><span class = "metricText">' + node.identifier + "</span></div></li>"; 
		});

		nodeList += "</ul>";
		
		container.set("content", nodeList);
	};
	
	
	req.get();
}

function loadDashboard(dashboard) {
	require([
	    "dijit/layout/StackContainer",
	    "dijit/layout/ContentPane",
	    "dijit/registry"
    ], function(Container, ContentPane, registry){
	    	setTitle("Dashboard: " + dashboard.dashboard.title);

		if (!registry.byId("dashboardWidgetContainer")) {
			var container = new Container({id: "dashboardWidgetContainer", class: "blockContainer"});
			container.placeAt("wrapper"); 
		}
		 
		var container = registry.byId("dashboardWidgetContainer");
		
		dojo.forEach(dashboard.widgetInstances, function(widget) {
			if (!registry.byId("widget" + widget.id)) {
				var widgetContent = new ContentPane({
					id: "widget" + widget.id,
					class: "block",
				});   
				widgetContent.set("content", "<h2>" + widget.class + "</h2><div>Undefined Widget Content</div>");
				container.addChild(widgetContent);
			}   
			
			var cp = registry.byId("widget" + widget.id);
			var renderFunction = "renderWidget" + widget.class;
			console.log(renderFunction);
			window[renderFunction](widget, cp);
		});

		layoutBoxes();
	});

}

function reqDashboard(dashboard) {
	var req = newJsonReq();
	req.url = "json/getDashboard",
	req.content = { id: dashboard }, 
	req.load = loadDashboard,
	req.get();
}

function reqGetServices() {
	var req = newJsonReq();
	req.url = "json/getServices";
	req.load = loadGetServices;
	req.get();
}

function serviceGroupsModel() {
	getItem = function () {
		console.log("yoo");
	}
}

function mniServicesClicked() {
	require([
		"dojo/_base/window",
		"dijit/Tree",
		"dojo/store/JsonRest",
		"dijit/tree/ObjectStoreModel",
		"dojo/store/Memory"
	], function(win, Tree, JsonRestStore, ObjectStoreModel, Memory) {
		store = new JsonRestStore({
			target: "/json/getServiceGroup",
			labelAttribute: "description"
		});

		model = new dijit.tree.ObjectStoreModel({
			store: store,
			derferItemLoadingUntilExpanded: true,
			query: { id: 11281081 },

		});

		window.st = store;
		window.mo = model;

		tree = new Tree({
			model: model,
		});

		tree.placeAt("wrapper");
		tree.startup();
	});
}

function setTitle(newTitle) {
	require(["dijit/registry"], function(registry){
		registry.byId("title").setContent("Upsilon &raquo; " + newTitle);
	});
}

function setupHeader() {
	require([
		"dijit/layout/ContentPane",
	], function (ContentPane) {
		header = new ContentPane({
			id: "header",
			content: new ContentPane({
				class: 'pageTitle title',
				content: 'Upsilon',
				id: 'title',
			})
		});

		header.placeAt("wrapper");
	});
}

