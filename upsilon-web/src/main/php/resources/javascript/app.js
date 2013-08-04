function onLoad() {
	updatePermissions();
	setupToolbar();
	setupWindowMenu();
}

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

		var btnOne = new MenuBarItem({label: "foo", onClick: mniFooClicked });

		menubar.addChild(btnOne);

		menubar.placeAt("wrapper");
		menubar.startup();
	});
}

function mniFooClicked() {
	window.alert("foo");
}

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
