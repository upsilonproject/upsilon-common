<?php

$title = 'Update remote configuration service';
require_once 'includes/common.php';

use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\ElementInput;

class UpdateRemoteConfigService extends Form {
	public function __construct() {
		parent::__construct('UpdateRemoteConfig', 'Update remote config');

		$id = san()->filterUint('id');

		$sql = 'SELECT s.* FROM remote_config_services s WHERE id = :id';
		$stmt = stmt($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		$config = $stmt->fetchRowNotNull();
		$this->remoteConfig = $config['config'];

		$this->addElementReadOnly('ID', $id, 'id');
		$this->addElement(new ElementInput('identifier', 'Identifier', $config['identifier']));
		$this->addElement(new ElementInput('commandRef', 'Command Ref', $config['commandRef']));
		$this->addElement(new ElementInput('parent', 'Parent', $config['parent']));

		$this->addDefaultButtons();
	}

	public function process() {
		$sql = 'UPDATE remote_config_services SET identifier = :identifier, commandRef = :commandRef, parent = :parent WHERE id = :id';
		$stmt = stmt($sql);
		$stmt->bindValue(':id', $this->getElementValue('id'));
		$stmt->bindValue(':identifier', $this->getElementValue('identifier'));
		$stmt->bindValue(':commandRef', $this->getElementValue('commandRef'));
		$stmt->bindValue(':parent', $this->getElementValue('parent'));
		$stmt->execute();
	}
}

$fh = new FormHandler('UpdateRemoteConfigService');
$fh->setRedirect('listRemoteConfigurations.php');
$fh->handle();

?>
