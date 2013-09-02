<?php

$title = 'Create group';
require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\DatabaseFactory;
use \libAllure\ElementInput;
use \libAllure\FormHandler;

class FormCreateGroup extends Form {
	public function __construct() {
		parent::__construct('createGroup', 'Create Group');

		$this->addElement(new ElementInput('title', 'Title'));

		$this->addDefaultButtons();
	}

	public function process() {
		createGroup($this->getElementValue('title'));
	}
}

$fh = new FormHandler('FormCreateGroup');
$fh->setRedirect('listGroups.php');
$fh->handle();

?>
