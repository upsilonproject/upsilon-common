<?php

require_once 'includes/widgets/header.php';

use \libAllure\FormHandler;
use \libAllure\Form;
use \libAllure\Sanitizer;
use \libAllure\DatabaseFactory;
use \libAllure\ElementCheckbox;
use \libAllure\ElementInput;
use \libAllure\ElementSelect;

class FormUpdateApiClient extends Form {
	public function __construct($id) {
		parent::__construct('formUpdateApiClient', 'Update API Client');

		$apiClient = $this->getApiClient($id);

		$this->addElementReadOnly('ID', $id, 'id');
		$this->addElementReadOnly('User ID', $apiClient['user']);

		$this->addElement(new ElementInput('identifier', 'Identifier', $apiClient['identifier'], 'The identifier string used to connect.'));

		$this->addSection('Security');
		$this->addElement(new ElementCheckbox('anonymousLogin', 'Anonymous Login', $apiClient['anonymousLogin']));
		$elRedirect = $this->addElement(new ElementSelect('redirect', 'Redirect'));
		$elRedirect->addOption('Nowhere', '');
		$elRedirect->addOption('HUD', 'hud');
		$elRedirect->addOption('Dashboard', 'dashboard');
		$elRedirect->addOption('Mobile (stats only)', 'mobile');
		$elRedirect->setValue($apiClient['redirect']);
	
		$this->addSection('Display');
		$this->addElement(new ElementCheckbox('drawHeader', 'Draw Header', $apiClient['drawHeader']));
		$this->addElement(new ElementCheckbox('drawNavigation', 'Draw Navigiation', $apiClient['drawNavigation']));
		$this->addElement(new ElementCheckbox('drawBigClock', 'Draw Big Clock', $apiClient['drawBigClock'], 'Show a big clock during night time.'));

		$this->addDefaultButtons();
	}

	private function getApiClient($id) {
		$sql = 'SELECT * FROM apiClients WHERE id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		return $stmt->fetchRowNotNull();
	}

	public function process() {
		$sql = 'UPDATE apiClients SET identifier = :identifier, drawBigClock = :drawBigClock, drawHeader = :drawHeader, drawNavigation = :drawNavigation, anonymousLogin = :anonymousLogin, redirect = :redirect WHERE id = :id';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':identifier', $this->getElementValue('identifier'));
		$stmt->bindValue(':drawHeader', $this->getElementValue('drawHeader'));
		$stmt->bindValue(':drawNavigation', $this->getElementValue('drawNavigation'));
		$stmt->bindValue(':drawBigClock', $this->getElementValue('drawBigClock'));
		$stmt->bindValue(':anonymousLogin', $this->getElementValue('anonymousLogin'));
		$stmt->bindValue(':redirect', $this->getElementValue('redirect'));
		$stmt->bindValue(':id', $this->getElementValue('id'));
		$stmt->execute();

	}
}

$fh = new FormHandler('FormUpdateApiClient');
$fh->setConstructorArgument(0, Sanitizer::getInstance()->filterUint('id'));
$fh->setRedirect('listApiClients.php');
$fh->handle();

require_once 'includes/widgets/footer.php';

?>
