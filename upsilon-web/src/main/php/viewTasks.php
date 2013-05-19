<?php

require_once 'includes/common.php';

$title = 'Tasks List';
require_once 'includes/widgets/header.php';

use \libAllure\DatabaseFactory;


$tpl->assign('tasks', getTasks());
$tpl->display('viewTasks.tpl');

require_once 'includes/widgets/footer.php';

?>
