<?php 

require_once 'includes/common.php';

use \libAllure\ElementInput;
use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\Session;
use \libAllure\User;
use \libAllure\DatabaseFactory;
use \libAllure\AuthBackend;
use \libAllure\ElementPassword;

class FormUpdateUser extends Form {
	public function __construct() {
		parent::__construct('updateUser', 'Update user');

		$user = User::getUserById(san()->filterUint('id'));

		$this->addElementReadOnly('ID', $user->getId(), 'id');
		$this->addElement(new ElementInput('username', 'Username', $user->getUsername()));

		$this->addSection('Password');

		if ($this->supportsPasswords()) {
			$this->addElement(new ElementPassword('password', 'Password'));
			$this->getElement('password')->setOptional(true);
	//		$this->getElement('password')->setMinMaxLengths(0, 64);
			$this->addElement(new ElementPassword('passwordConfirm', 'Password (confirm)'));
			$this->getElement('passwordConfirm')->setOptional(true);
	//		$this->getElement('passwordConfirm')->setMinMaxLengths(0, 64);
		} else {
			$this->addElementReadOnly('Password', 'Password modification is not supported by backend (' . get_class(AuthBackend::getInstance()) . ')');	
		}


		$this->addDefaultButtons();
	}

	public function process() {
		$sql = 'UPDATE users SET username = :username WHERE id = :id';

		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $this->getElementValue('id'));
		$stmt->bindValue(':username', $this->getElementValue('username'));
		$stmt->execute();

		if ($this->supportsPasswords() && strlen($this->getElementValue('password')) > 0) {
			AuthBackend::getInstance()->setSessionUserPassword($this->getElementValue('password'));
		}
	}

	private function supportsPasswords() {
		return AuthBackend::getInstance() instanceof \libAllure\AuthPasswordModification;
	}
}

$fh = new FormHandler('FormUpdateUser');
$fh->setRedirect('listUsers.php');
$fh->handle();
?>
