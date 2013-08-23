<?php

$title = 'Update service metadata';
require_once 'includes/common.php';
require_once 'libAllure/FormHandler.php';

use \libAllure\DatabaseFactory;
use \libAllure\Sanitizer;
use \libAllure\Form;
use \libAllure\FormHandler;
use \libAllure\ElementTextbox;
use \libAllure\ElementSelect;
use \libAllure\ElementCheckbox;
use \libAllure\ElementReadyOnly;
use \libAllure\ElementNumeric;
use \libAllure\ElementInput;
use \libAllure\ElementHtml;

$sql = 'SELECT s.id, s.identifier FROM services s WHERE s.id = :id';
$stmt = DatabaseFactory::getInstance()->prepare($sql);
$stmt->bindValue(':id', Sanitizer::getInstance()->filterUint('id'));
$stmt->execute();

$service = $stmt->fetchrow();

if (empty($service) || $stmt->numRows() == 0) {
	throw new Exception("Cannot get service with id: " . Sanitizer::getInstance()->filterUint('id'));
}

function getMetadata($service) {
	$sql = 'SELECT m.* FROM service_metadata m WHERE m.service = :serviceIdentifier LIMIT 1';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':serviceIdentifier', $service['identifier']);
	$stmt->execute();

	return $stmt->fetchRow();
}

$metadata = getMetadata($service);

if (empty($metadata)) {
	$sql = 'INSERT INTO service_metadata (service) VALUES (:identifier)';
	$stmt = DatabaseFactory::getInstance()->prepare($sql);
	$stmt->bindValue(':identifier', $service['identifier']);
	$stmt->execute();

	$metadata = getMetadata($service);
}

class FormUpdateMetadata extends Form {
	public function __construct($metadata, $serviceIdentifier) {
		$this->metadata = $metadata;
		$this->serviceIdentifier = $serviceIdentifier;

		parent::__construct('updateMetadata', 'Update Metadata');

		$this->addSection('Service');
		$this->addElementReadOnly('Service identifier', $this->serviceIdentifier);
		$this->addElement(new ElementInput('alias', 'Alias', $this->metadata['alias']));
		$this->addElement(new ElementTextbox('actions', 'Actions', $this->metadata['actions']));
		$this->addElement(new ElementTextbox('metrics', 'Metrics', $this->metadata['metrics']));
		$this->addElementDefaultMetric($metadata['defaultMetric']);
		$this->addElement($this->getElementServiceIcon($this->metadata['icon']));
		$this->addElement(new ElementCheckbox('hasTasks', 'Has Tasks', $this->metadata['hasTasks']));
		$this->addElement(new ElementCheckbox('hasEvents', 'Has Events', $this->metadata['hasEvents']));

		$this->addSection('Result Casting');
		$this->addElement(new ElementHtml('desc', 'Desc', '<p>Sometimes, you need to change the the result of a service check to display differently to the actual check result.</p>'));
		$this->addElement($this->getCastElement('critical', $this->metadata['criticalCast']));
		$this->addElement($this->getCastElement('good', $this->metadata['goodCast']));
		$this->addElement($this->getElementSelectSla($this->metadata['acceptableDowntimeSla']));
		$this->addElement(new ElementTextbox('acceptableDowntime', 'Acceptable downtime', $this->metadata['acceptableDowntime']));

		$this->addSection('Physical');
		$this->addElement($this->getElementRoom($this->metadata['room']));
		$this->addElement(new ElementNumeric('roomPositionX', 'Room Position X', $this->metadata['roomPositionX']));
		$this->addElement(new ElementNumeric('roomPositionY', 'Room Position Y', $this->metadata['roomPositionY']));

		$id = &$_REQUEST['id'];
		$this->addElementHidden('id', $id);

		$this->addScript('serviceIconChanged()');
		$this->addDefaultButtons();
	}

	public function validateExtended() {
		validateAcceptableDowntime($this->getElement('acceptableDowntime'));
	}

	private function getElementSelectSla($existing) {
		$el = new ElementSelect('acceptableDowntimeSla', 'Acceptable Downtime SLA');
		$el->addOption('(none)', null);

		$sql = 'SELECT s.id, s.title FROM acceptable_downtime_sla s';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		$listSla = $stmt->fetchAll();

		foreach ($listSla as $sla) {
			$el->addOption($sla['title'], $sla['id']);
		}

		$el->setValue($existing);

		return $el;
	}

	private function getElementRoom($room) {
		$el = new ElementSelect('room', 'Room');
		$el->addOption('(none)', null);

		$sql = 'SELECT r.id, r.title FROM rooms r';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		foreach ($stmt->fetchAll() as $row) {
			$el->addOption($row['title'], $row['id']);
		}

		$el->setValue($room);

		return $el;
	}

	private function getCastElement($title, $value) {
		$el = new ElementSelect($title . 'Cast', 'Cast when ' . $title);

		$el->addOption('(no casting)', '');
		$el->addOption('Warning', 'WARNING');
		$el->addOption('Critical', 'CRITICAL');
		$el->addOption('Good', 'GOOD');

		$el->setValue($value);

		return $el;
	}

	private function addElementDefaultMetric($defaultMetric) {
		$availableMetrics = explode("\n", trim($this->getElementValue('metrics')));

		if ($this->getElementValue('metrics') !== null && !empty($availableMetrics)) {
			$el = new ElementSelect('defaultMetric', 'Default Metric');

			foreach ($availableMetrics as $metric) {
				$el->addOption($metric);
			}

			$el->setValue($defaultMetric);

			$this->addElement($el);
		} else {
			$this->addElementReadOnly('DefaultMetric', 'karma', 'defaultMetric');
		}
	}

	private function getElementServiceIcon($default) {
		$el = new ElementSelect('icon', 'Icon', null, '<span id = "serviceIconPreview"><em>No icon selected.</em></span>');
		$el->addOption('', '');

		$listIcons = scandir('resources/images/serviceIcons/');

		foreach ($listIcons as $k => $itemIcon) {
			if ($itemIcon[0] == '.') {
				continue;
			}

			if (stripos($itemIcon, '.png') == false) {
				continue;
			}

			$el->addOption($itemIcon, $itemIcon);
		}

		$el->setValue($default);
		$el->setOnChange('serviceIconChanged');
		
		return $el;
	}

	public function process() {
		$sql = 'UPDATE service_metadata SET actions = :actions, alias = :alias, metrics = :metrics, defaultMetric = :defaultMetric, icon = :icon, hasTasks = :hasTasks, hasEvents = :hasEvents, room = :room, roomPositionX = :roomPositionX, roomPositionY = :roomPositionY, criticalCast = :criticalCast, goodCast = :goodCast, acceptableDowntime = :acceptableDowntime, acceptableDowntimeSla = :acceptableDowntimeSla  WHERE service = :identifier';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':actions', $this->getElementValue('actions'));
		$stmt->bindValue(':alias', $this->getElementValue('alias'));
		$stmt->bindValue(':metrics', $this->getElementValue('metrics'));
		$stmt->bindValue(':icon', $this->getElementValue('icon'));
		$stmt->bindValue(':identifier', $this->serviceIdentifier);
		$stmt->bindValue(':defaultMetric', $this->getElementValue('defaultMetric'));
		$stmt->bindValue(':hasTasks', $this->getElementValue('hasTasks'));
		$stmt->bindValue(':hasEvents', $this->getElementValue('hasEvents'));
		$stmt->bindValue(':room', $this->getElementValue('room'));
		$stmt->bindValue(':roomPositionX', $this->getElementValue('roomPositionX'));
		$stmt->bindValue(':roomPositionY', $this->getElementValue('roomPositionY'));
		$stmt->bindValue(':criticalCast', $this->getElementValue('criticalCast'));
		$stmt->bindValue(':goodCast', $this->getElementValue('goodCast'));
		$stmt->bindValue(':acceptableDowntimeSla', $this->getElementValue('acceptableDowntimeSla'));
		$stmt->bindValue(':acceptableDowntime', $this->getElementValue('acceptableDowntime'));
		$stmt->execute();

		echo 'Updated. View <a href = "viewService.php?id=' . $this->getElementValue('id') . '">' . $this->serviceIdentifier . '</a> again?';;
	}
}

$formHandler = new FormHandler('FormUpdateMetadata');
$formHandler->setConstructorArgument(0, $metadata);
$formHandler->setConstructorArgument(1, $service['identifier']);
$formHandler->setRedirect('viewService.php?id=' . $service['id']);
$formHandler->handle();

require_once 'includes/widgets/footer.php';

?>
