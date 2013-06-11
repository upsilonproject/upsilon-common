<?php

require_once 'includes/widgets/header.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\ElementInput;
use \libAllure\ElementPassword;
use \libAllure\DatabaseFactory;
use \libAllure\AuthBackend;
use \libAllure\Session;

class FormCreateUser extends Form {
	public function __construct() {
		parent::__construct('formCreateUser', 'Create user');

		$this->addElement(new ElementInput('username', 'Username'));
		$this->addElement(new ElementPassword('password1', 'Password'));
		$this->addElement(new ElementPassword('password2', 'Password (confirm)'));
		$this->addDefaultButtons();
	}

	public function validateExtended() {
		if ($this->getElementValue('password1') != $this->getElementValue('password2')) {
			$this->getElement('password2')->setValidationError('Passwords do not match.');
		}

		$sql = 'SELECT u.username FROM users u WHERE u.username = :username ';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue('username', $this->getElementValue('username'));
		$stmt->execute();

		if ($stmt->numRows() != 0) {
			$this->getElement('username')->setValidationError('A user with that username already exists.');
		}
	}

	public function process() {
		$sql = 'INSERT INTO users (username, password) VALUES (:username, :password) ';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);

		$stmt->bindValue(':username', $this->getElementValue('username'));
		$stmt->bindValue(':password', AuthBackend::getInstance()->hashPassword($this->getElementValue('password1')));
		$stmt->execute();
	}
}

$fh = new FormHandler('FormCreateUser');
$fh->handle();

require_once 'includes/widgets/footer.php';

?>
