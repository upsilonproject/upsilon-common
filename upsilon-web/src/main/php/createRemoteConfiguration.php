<?php

require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\ElementInput;
use \libAllure\DatabaseFactory;

class FormCreateRemoteConfig extends Form {
	public function __construct() {
		parent::__construct('formCreateRemoteConfig', 'Create remote config');

		$this->addElement(new ElementInput('identifier', 'Identifier'));
		$this->addDefaultButtons();
	}

	public function process() {
		$sql = 'INSERT INTO remote_configs (identifier) VALUES (:identifier)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':identifier', $this->getElementValue('identifier'));
		$stmt->execute();
	}
}

$fh = new FormHandler('FormCreateRemoteConfig');
$fh->setRedirect('listRemoteConfigurations.php');
$fh->handle();

?>
