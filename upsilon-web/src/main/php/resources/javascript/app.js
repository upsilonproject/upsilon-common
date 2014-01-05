if (typeof String.prototype.endsWith !== 'function') {
	String.prototype.endsWith = function(suffix) {
		return this.indexOf(suffix, this.length - suffix.length) !== -1;
	};
}

function onLoad() {
	try {
		setupHeader();
		setupToolbar();
		setupRootContainer();

		reqUpdatePermissions();
	} catch (err) {
		displayError(err);
	}
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
	], function(registry){
		permissions = window.permissions;

		registry.byId("mniDashboard").set("disabled", !permissions.viewDashboard);
		registry.byId("mniServices").set("disabled", !permissions.viewServices);
		registry.byId("mniNodes").set("disabled", !permissions.viewNodes);
		registry.byId("mniLogout").set("disabled", !permissions.loggedIn);
		registry.byId("mniLogin").set("disabled", permissions.loggedIn);

		window.registry = registry;
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
	    "dijit/registry",
	    "dojo/dom-construct",
    ], function(Container, ContentPane, registry, domcon){
	    	setTitle("Dashboard: " + dashboard.dashboard.title);

		if (!registry.byId("dashboardWidgetContainer")) {
			var container = new Container({id: "dashboardWidgetContainer", class: "blockContainer"});
			container.placeAt("content");
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

function clickedTreeNode(item) {
	switchContentToGroup(item);
}

function switchContentToGroup(group) {
	require([
		"dijit/registry",
	], function(registry) {
		content = registry.byId("content");
		content.set("content", "Group:" + group.title + "<br />ID:" + group.id);
	});
}

function setupRootContainer() {
	require([
		"dijit/registry",
		"dijit/layout/BorderContainer",
		"dijit/layout/ContentPane",
		"dijit/Tree",
		"dojo/store/JsonRest",
		"dijit/tree/ObjectStoreModel",
		"dojo/store/Memory"
	], function(registry, BorderContainer, ContentPane, Tree, JsonRestStore, ObjectStoreModel, Memory) {
		if (registry.byId("navTree") == null) {
			contentBody = new ContentPane({
				id: "content",
				content: "main content",
				region: "center"
			});

			store = new JsonRestStore({
				target: "/json/getServiceGroup/",
				getRoot: function (onItem, onError) {
					this.get(7888395).then(onItem, onError);
				},
				getChildren: function(group, onComplete, onError) {
					onComplete(group.listSubgroups);
				},
				getLabel: function(group) {
					return group.title;
				},
				mayHaveChildren: function(o) {
					return true;
				}
			});

			tree = new Tree({
				id: "navTree",
				region: "center",
				model: store,
				onClick: clickedTreeNode
			});
			tree.startup();

			rootContainer = new BorderContainer({
				id: "rootContainer",
				liveSplitters: true,
			});

			contentTree = new ContentPane({ content: tree, region: "left", splitter: true});
			contentTree.startup();

			rootContainer.addChild(contentTree);
			rootContainer.addChild(contentBody);
			rootContainer.placeAt("wrapper");
			rootContainer.startup();
		}
	});
}

