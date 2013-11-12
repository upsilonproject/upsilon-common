<?php

require_once 'includes/common.php';

use \libAllure\FormHandler;
use \libAllure\ElementAlphaNumeric;

class FormCreateCommand extends \libAllure\Form {
	public function __construct() {
		$this->addElement(new ElementAlphaNumeric('identifier', 'Command identifier', null, 'The &lt;command id = &quot;<strong>...</strong>&quot; /&gt; in a upsilon-node configuration file. If you have a service in the web interface with this command, you can hover over its "command line" to show the command identifier. eg: checkPing, checkHttpd'));
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
