<?php

namespace libAllure\util;

require_once 'libAllure/Session.php';
require_once 'libAllure/Form.php';

use \libAllure\AuthBackend;
use \libAllure\ElementAlphaNumeric;
use \libAllure\ElementPassword;
use \libAllure\Session;

class FormLogin extends \libAllure\Form {
	public function __construct() {
		parent::__construct('formLogin', 'Login');

		if (session_id() === '') {
			throw new \Exception('Cannot construct login form, no session has been started.');
		}

		$this->addElement(new ElementAlphaNumeric('username', 'Username'));
		$this->addElement(new ElementPassword('password', 'Password'));

		$this->requireFields(array('username', 'password'));
		$this->addDefaultButtons('Login');
	}

	protected function validateExtended() {
		$username = $this->getElementValue('username');
		$password = $this->getElementValue('password');

		try {
			$res = Session::checkCredentials($username, $password);

			if (!$res) {
				$this->getElement('username')->setValidationError('Non true return code from backend::checkCredentials().');
			}
		} catch (\libAllure\UserNotFoundException $e) {
			$this->getElement('username')->setValidationError('Username not found.');
		} catch (\libAllure\IncorrectPasswordException $e) {
			$this->getElement('password')->setValidationError('Incorrect password.');
		}
	}

	public function process() { }
}

?>
