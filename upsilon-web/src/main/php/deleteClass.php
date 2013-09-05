<?php

require_once 'includes/common.php';

$sql = 'DELETE FROM classes WHERE id = :id';
$stmt = stmt($sql);
$stmt->bindValue(':id', san()->filterUint('id'));
$stmt->execute();

redirect('listClasses.php');

?>
