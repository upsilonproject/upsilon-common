<?php

require_once 'includes/common.php';

function deleteUserById($id) {
	$sql = 'DELETE FROM users WHERE id = :id';
	$stmt = stmt($sql);
	$stmt->bindValue(':id', $id);
	$stmt->execute();
}

$id = san()->filterUint('id');
deleteUserById($id);
redirect('listUsers.php');

?>
