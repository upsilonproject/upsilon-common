<?php

require_once 'jsonCommon.php';

$id = san()->filterUint('serviceId');
$service = getServiceById($id, true);

outputJson($service['listSubresults']);

?>
