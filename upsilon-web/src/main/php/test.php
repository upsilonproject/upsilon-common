<?php

require_once 'includes/common.php';

$a = linksCollection('a');
$a->add('#', 'a1');
$a->add('#', 'a2');
$a->add('#', 'a3');
$aa = linksCollection('aa');
$aa->add('#', 'aa1');
$aa->add('#', 'aa2');
$aa->add('#', 'aa3');
$bb = linksCollection();
$bb->add('#', 'b1');
$aa->addChildCollection('aa3', $bb);

$a->addChildCollection('a2', $aa);

$tpl->assign('links', $a);
$tpl->display('test.tpl');

?>
