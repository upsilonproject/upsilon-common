<?php

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
		$sql = 'INSERT INTO groups (name) VALUES (:title)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue('title', $this->getElementValue('title'));
		$stmt->execute();
	}
}

$fh = new FormHandler('FormCreateGroup');
$fh->setRedirect('index.php');
$fh->handle();

?>
