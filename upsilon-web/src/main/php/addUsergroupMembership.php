<?php

require_once 'includes/common.php';
require_once 'libAllure/FormHandler.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\ElementSelect;

class FormAddUserToGroup extends Form {
	public function __construct() {
		parent::__construct('formAddUserToGroup', 'Add User To Group');

		$this->addElement($this->getElementUsername());
		$this->addElement($this->getElementGroup());

		$this->addDefaultButtons();
	}

	private function getElementUsername() {
		$sql = 'SELECT u.id, u.username FROM users u ';
		$stmt = stmt($sql);
		$stmt->execute();

		$el = new ElementSelect('username', 'Username');

		foreach ($stmt->fetchAll() as $user) {
			$el->addOption($user['username'], $user['id']);
		}

		return $el;
	}

	private function getElementGroup() {
		$el = new ElementSelect('usergroup', 'Usergroup');

		foreach (getUsergroups() as $group) {
			$el->addOption($group['title'], $group['id']);
		}

		return $el;
	}

	public function process() {
		addUserToGroup($this->getElementValue('username'), $this->getElementValue('usergroup'));
	}
}

$fh = new FormHandler('FormAddUserToGroup');
$fh->setConstructorArgument(1, san()->filterUint('id'));
$fh->setRedirect('listUsergroups.php');
$fh->handle();

?>
