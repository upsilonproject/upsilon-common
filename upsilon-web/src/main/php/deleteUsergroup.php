<?php

require_once 'includes/common.php';

$id = san()->filterUint('id');

deleteUsergroupById($id);

redirect('listUsergroups.php');

?>
