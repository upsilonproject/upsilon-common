
<?php

require_once 'includes/common.php';

use \libAllure\FormHandler;
use \libAllure\ElementAlphaNumeric;

class FormUpdateCommand extends \libAllure\Form {
	public function __construct($id) {
		parent::__construct('updateMetadata');

		$command = $this->getCommand($id);

		$this->addElementReadOnly('ID', $command['id'], 'id');
		$this->addElement(new ElementAlphaNumeric('identifier', 'Identifier', $command['commandIdentifier']));
		$this->addElement(getElementServiceIcon($command['icon']));
		$this->addDefaultButtons();
	}

	public function getCommand($id) {
		$sql = 'SELECT c.commandIdentifier, c.id, c.icon FROM command_metadata c WHERE c.id = :id';
		$stmt = db()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		return $stmt->fetchRowNotNull();
	}

	public function process() {
		$sql = 'UPDATE command_metadata SET commandIdentifier = :commandIdentifier, icon = :icon WHERE id = :id';
		$stmt = db()->prepare($sql);
		$stmt->bindValue(':commandIdentifier', $this->getElementValue('identifier'));
		$stmt->bindValue(':icon', $this->getElementValue('icon'));
		$stmt->bindValue(':id', $this->getElementValue('id'));
		$stmt->execute();
	}
}

$fh = new FormHandler('FormUpdateCommand');
$fh->setConstructorArgument(0, san()->filterUint('id'));
$fh->setRedirect('listCommands.php');
$fh->handle();

?>
