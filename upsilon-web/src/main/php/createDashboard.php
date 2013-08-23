<?php

require_once 'includes/common.php';

$sql = 'INSERT INTO dashboard (title) values ("Dasboard")';
stmt($sql)->execute();;

redirect('index.php');

?>
