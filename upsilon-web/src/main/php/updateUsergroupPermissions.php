<?php

require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\ElementTextbox;

class FormUpdateUsergroupPermissions extends Form {
	public function __construct($id) {
		parent::__construct('updatePermissions', 'Update permissions');

		$this->id = $id;

		$this->addElementReadOnly('ID', $id, 'id');
		$this->addElement(new ElementTextbox('permissions', 'Permissions', implode("\n", $this->getAssignedPermissions())));

		$this->addDefaultButtons();
	}

	protected function validateExtended() {
		$perms = $this->getElementValue('permissions');
		$perms = trim($perms);
		$perms = explode("\n", $perms);

		$possiblePerms = $this->getPossiblePermissions();

		foreach ($perms as $p) {
			$p = trim($p);

			if (!in_array($p, $possiblePerms)) {
				$this->getElement('permissions')->setValidationError($p . ' is not a valid permission');
				return;
			}
		}
	}

	private function getAssignedPermissions() {
		$sql = 'SELECT i.permission, p.`key` FROM privileges_g i LEFT JOIN permissions p ON i.permission = p.id AND i.`group` = :id';
		$stmt = stmt($sql);
		$stmt->bindValue(':id', $this->id);
		$stmt->execute();

		$ret = array();

		foreach ($stmt->fetchAll() as $perm) {
			$ret[] = $perm['key'];
		}

		return $ret;
	}

	private function getPossiblePermissions() {
		$sql = 'SELECT p.`key` FROM permissions p '; 
		$stmt = stmt($sql);
		$stmt->execute();

		$ret = array();

		foreach ($stmt->fetchAll() as $perm) {
			$ret[] = $perm['key'];
		}

		return $ret;
	}

	public function process() {
		$newPerms = $this->getElementValue('permissions');
		$newPerms = trim($newPerms);
		$newPerms = explode("\n", $newPerms);

		setGroupPermissions($this->id, $newPerms);
	}
}

$id = san()->filterUint('id');

$fh = new FormHandler('FormUpdateUsergroupPermissions');
$fh->setConstructorArgument(0, $id);
$fh->setRedirect('listUsergroups.php');
$fh->handle();

?>
