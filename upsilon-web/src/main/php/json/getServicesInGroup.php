<?php

require_once 'jsonCommon.php';

outputJson(getServices(san()->filterUint('id')));

?>
