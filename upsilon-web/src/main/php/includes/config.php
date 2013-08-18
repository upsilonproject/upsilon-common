<?php
date_default_timezone_set('Europe/London');
ini_set('display_errors', 'on');

define('CFG_DB_DSN', 'mysql:dbname=upsilon');
define('CFG_DB_USER', 'root');
define('CFG_DB_PASS', '');

// The following is configuration for advanced users only.
define('CFG_PASSWORD_SALT', '521120417419d'); // If you change this value, you will break all existing user passwords.  
?>