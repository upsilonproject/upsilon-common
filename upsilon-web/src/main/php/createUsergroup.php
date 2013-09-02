<?php

require_once 'includes/common.php';
require_once 'libAllure/FormHandler.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\ElementInput;

class FormCreateUsergroup extends Form {
	public function __construct() {
		parent::__construct('formCreateUsergroup', 'Create Usergroup');

		$this->addElement(new ElementInput('title', 'Title'));

		$this->addDefaultButtons();
	}

	public function process() {
		createUsergroup($this->getElementValue('title'));
	}
}

$fh = new FormHandler('FormCreateUsergroup');
$fh->setRedirect('listUsergroups.php');
$fh->handle();

?>
