<?php

namespace libAllure;

require_once 'libAllure/Form.php';

class FormRegister extends \libAllure\Form {
	public function __construct() {
		parent::__construct('formRegister', 'Register a new account');

		$this->addElement(Element::factory('text', 'username', 'Username'));
		$this->addElement(Element::factory('password', 'password1', 'Password'));
		$this->addElement(Element::factory('password', 'password2', 'Password (confirm)'));
		$this->addElement(Element::factory('text', 'email', 'E-Mail address'));
	
		$this->addButtons(Form::BTN_SUBMIT);
	}

	public function validateExtended() {
		$this->validateUsername();
		$this->validatePassword();
		$this->validateEmail();
	}

	private function validateEmail() {} // FIXME

	private function validateUsername() {
		global $db;

		if (strlen($this->getElementValue('username')) < 4) {
			$this->setElementError('username', 'Username should be more than 4 chars.');
		}

//		if (preg_match('/[a-z|0-9]+/i', $this->getElementValue('username')) != 1) {
//			$this->setElementError('username', 'Invalid characters used, only alphanumeric characters are permitted (a-z, 0-9). ');
//		}

		$sql = 'SELECT username FROM users WHERE username = :username';
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':username', $this->getElementValue('username'));
		$stmt->execute();

		if ($stmt->numRows() > 0) {
			$this->setElementError('username', 'That username has already been taken.');
		}	
	}

	private function validatePassword() {
		if (strlen($this->getElementValue('password1')) < 6) {
			$this->setElementError('password1', 'Please provide a password which is at least 6 characters long before you get h4xed.');
		}

		if ($this->getElementValue('password1') != $this->getElementValue('password2')) {
			$this->setElementError('password2', 'Passwords do not match.');
		}

	}

	public function process() {
		global $db;

		$sql = 'INSERT INTO users (username, password, registered, email) VALUES (:username, :password, now(), :email) ';	
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':username', $this->getElementValue('username'));
		$stmt->bindValue(':password', sha1($this->getElementValue('password1')));
		$stmt->bindValue(':email', $this->getElementValue('email'));
		$stmt->execute();

		Logger::messageNormal('New user registration: ' . $this->getElementValue('username'), LocalEventType::CREATE_USER);
		sendEmailToAdmins('Username: ' . $this->getElementValue('username'), 'New user registration: ' . $this->getElementValue('username'));

		redirect('loginregister.php?formLogin-username=' . $this->getElementValue('username'), 'Registeration complete!');
	}
}

?>
