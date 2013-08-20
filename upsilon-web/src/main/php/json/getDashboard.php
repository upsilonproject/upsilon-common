<?php

require_once 'jsonCommon.php';
require_once 'includes/classes/Dashboard.php'; 

$dashboard = new Dashboard(san()->filterUint('id')); 

outputJson($dashboard); 
?>
