<?php

require_once 'includes/widgets/header.php';

use \libAllure\Session;
var_dump(Session::getUser()->getPrivs());
var_dump(Session::getUser()->getUsergroups());

require_once 'includes/widgets/footer.php';

?>
