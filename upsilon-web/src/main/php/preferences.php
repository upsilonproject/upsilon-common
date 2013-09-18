<?php

$title = 'Preferences';
require_once 'includes/common.php';

require_once 'libAllure/FormHandler.php';

use \libAllure\FormHandler;
use \libAllure\Form;
use \libAllure\ElementInput;
use \libAllure\ElementPassword;
use \libAllure\ElementCheckbox;
use \libAllure\ElementNumeric;
use \libAllure\DatabaseFactory;
use \libAllure\Session;
use \libAllure\AuthBackend;

class UserPreferences extends Form {
	public function __construct() {
		parent::__construct('userPreferences', 'User Preferences');

		$this->addSection('Display');
		$this->addElement(new ElementNumeric('dtBegin', 'Daytime begin', Session::getUser()->getData('daytimeBegin')));
		$this->addElement(new ElementNumeric('dtEnd', 'Daytime end', Session::getUser()->getData('daytimeEnd')));
		$this->addElement(new ElementCheckbox('tutorialMode', 'Tutorial mode', Session::getUser()->getData('tutorialMode')));

		$this->addSection('Behavior');
		$this->addElement(new ElementCheckbox('promptBeforeDeletions', 'Prompet me before deletions?', Session::getUser()->getData('promptBeforeDeletions')));
		$this->addElement(new ElementNumeric('oldServiceThreshold', 'Old Service Threshold', Session::getUser()->getData('oldServiceThreshold')));
		$this->addElement(new ElementCheckbox('enableDebug', 'Enable debug link', Session::getUser()->getData('enableDebug'), 'Simply shows a link to the debug page in the user links menu.'));
		$this->addElement(new ElementCheckbox('enableExperimentalFeatures', 'Enable experimental features', Session::getUser()->getData('experimentalFeatures'), 'Enable various links to experimental stuff'));

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

	private function supportsPasswords() {
		return AuthBackend::getInstance() instanceof \libAllure\AuthPasswordModification;
	}

	public function process() {
		Session::getUser()->setData('daytimeBegin', $this->getElementValue('dtBegin'));
		Session::getUser()->setData('daytimeEnd', $this->getElementValue('dtEnd'));
		Session::getUser()->setData('promptBeforeDeletions', $this->getElementValue('promptBeforeDeletions'));
		Session::getUser()->setData('oldServiceThreshold', $this->getElementValue('oldServiceThreshold'));
		Session::getUser()->setData('tutorialMode', $this->getElementValue('tutorialMode'));
		Session::getUser()->setData('enableDebug', $this->getElementValue('enableDebug'));
		Session::getUser()->setData('experimentalFeatures', $this->getElementValue('enableExperimentalFeatures'));
		Session::getUser()->getAttribute('username', false);

		if ($this->supportsPasswords() && strlen($this->getElementValue('password')) > 0) {
			AuthBackend::getInstance()->setSessionUserPassword($this->getElementValue('password'));
		}
	}
}

$handler = new FormHandler('UserPreferences');
$handler->setRedirect('preferences.php');
$handler->handle();

?>
