<?php

require_once 'includes/widgets/header.php';

echo _('test');
echo '<hr />';
echo sprintf(_('greetings %s'), 'James');

$tpl->display('gttest.tpl');
