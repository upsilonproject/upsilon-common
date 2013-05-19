<?php

require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\ElementInput;
use \libAllure\DatabaseFactory;
use \libAllure\FormHandler;
use \libAllure\Session;

class FormCreateApiClient extends Form {
	public function __construct() {
		parent::__construct('createApiClient', 'Create API Client');

		$this->addElement(new ElementInput('identifier', 'Identifier'));

		$this->addDefaultButtons();
	}

	public function process() {
		$sql = 'INSERT INTO apiClients (identifier, user) VALUES (:identifier, :user)';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':identifier', $this->getElementValue('identifier'));
		$stmt->bindValue(':user', Session::getUser()->getId());
		$stmt->execute();
	}
}

$fh = new FormHandler('FormCreateApiClient');
$fh->setRedirect('listApiClients.php', 'Client created');
$fh->handle();

?>
