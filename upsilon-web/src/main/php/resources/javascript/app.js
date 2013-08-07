function onLoad() {
	updatePermissions();
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

function updatePermissions() {
	require(["dojo/request"], function(request){
		request("/json/sessionPermissions").then(
			function (text) {
				console.log(text);
			},

			function (error) {
				displayError(error);
			}
		);
	});
}

function displayError(err) {
	window.alert(error);
}

function setupToolbar() {
	require([
		"dijit/MenuBar",
		"dijit/MenuBarItem",
	], function(MenuBar, MenuBarItem) {
		var menubar = new MenuBar({});

		//var mniDashboard = new MenuBarItem({label: "Dashboard", onClick: mniDashboardClicked });
		//menubar.addChild(mniDashboard);
		menubar.addChild(new MenuBarItem({label: "Dashboard", onClick: mniDashboardClicked }));
		menubar.addChild(new MenuBarItem({label: "Services", onClick: mniServicesClicked }));

		menubar.placeAt("wrapper");
		menubar.startup();
	});
}

function mniDashboardClicked() {
	window.alert("db");
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
