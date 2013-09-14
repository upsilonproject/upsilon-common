function onLoad() {
	showFormLogin();
}

function main() {
	require([
		"dojo/request",
		"dojo/domReady!"
	], function() {
		onLoad();
	});
}
 
function applyPermissionsToToolbar(permissions) {
	require([
		"dijit/registry"
	], function(_registry){
		_registry.byId("mniDashboard").set("disabled", !permissions.viewDashboard);
		_registry.byId("mniServices").set("disabled", !permissions.viewServices);
	});
} 

function loadUpdatePermissions(perms) {
	window.permissions = perms;
	
	applyPermissionsToToolbar(perms);
} 

function reqUpdatePermissions() {
	var req = newJsonReq();
	req.url = "json/sessionPermissions";
	req.load = loadUpdatePermissions;
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
		"gridx/modules/VirtualVScroller"
	], function (Grid, Store, Cache, scroller) {
		grid = new Grid({
			id: "gridNodes",
			cacheClass: Cache, 
			store: new Store({data: [{identifier: "foo"}]}),
			structure: [
		        {field: "identifier", name: "Identifier"},
		        {field: "karma", name: "Karma"},
			{field: "instanceApplicationVersion", name: "Version"}
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

		mainToolbar.addChild(new MenuBarItem({id: "mniDashboard", label: "Dashboard", onClick: mniDashboardClicked }));
		mainToolbar.addChild(new MenuBarItem({id: "mniNodes", label: "Nodes", onClick: mniNodesClicked})); 
		mainToolbar.addChild(new MenuBarItem({id: "mniServices", label: "Services", onClick: mniServicesClicked }));
		mainToolbar.addChild(new MenuBarItem({id: "mniLogout", label: "Logout", onClick: mniLogoutClicked }));

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

function loadLogout() {
	setupHeader();
	setupToolbar();

	reqUpdatePermissions();
}

function loadLogin(res, a, b, c) {
	console.log(res, a, b, c);
	setupHeader();
	setupToolbar();

	reqUpdatePermissions();
}

function showFormLogin() {
	require([
		"dijit/layout/ContentPane",
	], function(container) {
		reqLogin("administrator", "password");
	});
}

function errorLogin() {
	window.alert("hiihi");
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

function newJsonReq() {
	return {
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
			list += '<span class = "metricIndictator ' + service.karma.toLowerCase() + '">' + service.karma + '</span> ' + service.identifier + "<br />"; 
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

function reqDashboard() {
	var req = newJsonReq();
	req.url = "json/getDashboard",
	req.content = { id: 1 }, 
	req.load = loadDashboard,
	req.get();
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

function serviceGroupsModel() {
	getItem = function () {
		console.log("yoo");
	}
}

function mniServicesClicked() {
	require([
		"dijit/Tree",
		"dojo/store/JsonRest",
		"dijit/tree/ObjectStoreModel",
		"dojo/store/Memory"
	], function(Tree, JsonRestStore, ObjectStoreModel, Memory) {
		st = new JsonRestStore({
			target: "/foo"
		});

		st = new Memory({
			data: []
		});

		tree = new Tree({
			store: new ObjectStoreModel({ store: st}),
			query: { id: 0 },
			labelAttr: "foo", 
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

