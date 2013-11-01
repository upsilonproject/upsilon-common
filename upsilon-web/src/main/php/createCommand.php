<?php

require_once 'includes/common.php';

use \libAllure\FormHandler;
use \libAllure\ElementAlphaNumeric;

class FormCreateCommand extends \libAllure\Form {
	public function __construct() {
		$this->addElement(new ElementAlphaNumeric('identifier', 'Identifier'));
		$this->addDefaultButtons();
	}

	public function process() {
		$sql = 'INSERT INTO command_metadata (commandIdentifier) VALUES (:identifier)';
		$stmt = db()->prepare($sql);
		$stmt->bindValue(':identifier', $this->getElementValue('identifier'));
		$stmt->execute();
	}
}

$fh = new FormHandler('FormCreateCommand');
$fh->setRedirect('listCommands.php');
$fh->handle();

?>
