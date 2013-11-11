<?php

require_once 'jsonCommon.php';

$id = san()->filterUint('id');

$group = getGroup($id);

outputJson($group);

?>
