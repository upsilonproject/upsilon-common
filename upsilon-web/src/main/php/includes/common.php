<?php

date_default_timezone_set('Europe/London');

require_once 'includes/functions.php';

require_once 'libAllure/ErrorHandler.php';
require_once 'libAllure/Session.php';
require_once 'libAllure/AuthBackendDatabase.php';
require_once 'libAllure/FormHandler.php';
require_once 'libAllure/HtmlLinksCollection.php';

use \libAllure\ErrorHandler;
use \libAllure\Session;
use \libAllure\AuthBackend;
use \libAllure\AuthBackendDatabase;

ErrorHandler::getInstance()->beGreedy();

require_once 'libAllure/Database.php';

use \libAllure\Database;
use \libAllure\DatabaseFactory;

$db = new Database('mysql:host=localhost;dbname=upsilon', 'root', '');
DatabaseFactory::registerInstance($db);

Session::start();
$backend = new AuthBackendDatabase();
AuthBackend::setBackend($backend);

require_once 'libAllure/Template.php';

use \libAllure\Template;

$tpl = new Template('upsilonGui');

?>
