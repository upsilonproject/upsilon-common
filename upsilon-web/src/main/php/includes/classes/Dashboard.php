<?php

class Dashboard {
	public function __construct($id) { 
		$sql = 'SELECT wi.id, w.class FROM widget_instances wi LEFT JOIN widgets w ON wi.widget = w.id ';
		$stmt = stmt($sql); 
		$stmt->execute();
		
		$listInstances = $stmt->fetchAll();
		$hiddenWidgets = array();  
		  
		foreach ($listInstances as &$itemInstance) {
			$wi = 'Widget' . $itemInstance['class'];
			include_once 'includes/classes/Widget' . $itemInstance['class'] . '.php';
		
			$itemInstance['instance'] = new $wi();
			$itemInstance['instance']->loadArguments($itemInstance['id']);
			$itemInstance['instance']->init();
		
			if (!$itemInstance['instance']->isShown()) {
				$hiddenWidgets[] = $itemInstance;
			}
		}
		
		$this->widgetInstances = $listInstances;
		$this->hiddenWidgetInstances = $hiddenWidgets;
	}
	
	public function getWidgetInstances() {
		return $this->widgetInstances;
	}
	
	public function getHiddenWidgetInstances() {
		return $this->hiddenWidgetInstances;
	}
} 
?>
