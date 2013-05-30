<?php
/*******************************************************************************

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*******************************************************************************/

namespace libAllure;

require_once 'libAllure/Sanitizer.php';

if (!function_exists('array_flatten')) {
	function array_flatten(array $o) {
		$ret = array();

		foreach ($o as $k => $a) {
			if (is_array($a)) {
				$ret = array_merge($ret, array_flatten($a));
			} else {
				$ret[$k] = $a;
			}
		}

		return $ret;
	}
}

abstract class Form {
	private $rules = array();
	protected $elements = array();
	public $scripts = array();
	private $name;
	private $submitter;
	private $title = '';
	private $generalError;
	private $action;

	protected $enctype = 'multipart/form-data';

	public static $fullyQualifiedElementNames = true;

	const BTN_LOGIN = 1;
	//const BTN_RESET = 2;
	const BTN_SUBMIT = 4;

	public function __construct($name = 'form', $title = NULL, $action = null) {
		$this->name = $name;
		$this->title = $title;
		
		if ($action == null) {
			$action = $_SERVER['PHP_SELF'];
		}

		$this->action = $action;
	}

	public function orderElements() {
		$newOrder = func_get_args();
		$oldOrder = $this->elements;
		$this->elements = array();

		foreach ($newOrder as $element) {
			$element = $this->getElementName($element);

			$this->elements[$element] = $oldOrder[$element];
		}

		$this->elements = array_merge($this->elements, $oldOrder);
	}

	public function setFullyQualifiedElementNames($newVal) {
		if (count($this->elements) > 0) {
			throw new \Exception('Cannot change form FQFN after elements have been added.');
		}

		self::$fullyQualifiedElementNames = $newVal;
	}

	/**
	Encapsulate a string within <script> tags and dump it
	at the bottom of the form.
	*/
	public function addScript($s) {
		$this->scripts[] = $s;
	}

	public function addSection($sectionTitle, $additionalClasses = null) {
		if (is_array($additionalClasses)) {
			$additionalClasses = implode($additionalClasses);
		}

		$this->addElement(new ElementHtml('sectionTitle' . uniqid(), null, '<p class = "formSection ' . $additionalClasses . '">' . $sectionTitle . '</p>'));
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function requireFields() {
		$requiredFields = func_get_args();

		if (count(func_get_args()) == 1 && is_array($requiredFields[0])) {
			$requiredFields = $requiredFields[0];
		}

		foreach ($requiredFields as $field) {
			$this->getElement($field)->setRequired(true);
		}	
	}

	public function process() {
		throw new \Exception('this form (' . get_class($this) . ') has an empty process handler.');
	}

	public function getElementValue($elementName) {
		global $db;

		return Sanitizer::getInstance()->escapeStringForClean($this->getElement($elementName)->getValue());
	}

	public function bindElementToStatement(&$stmt, $elementName, $parameterName = null) {
		$el = $this->getElement($elementName);

		if ($parameterName == null) {
			$parameterName = ':' . $elementName;
		}

		$stmt->bindValue($parameterName, $el->getValue());
	}

	public function addDefaultButtons($title = null) {
		$this->addButtons(Form::BTN_SUBMIT);

		if (!empty($title)) {
			$this->getElement('submit')->setCaption($title);
		}
	}

	/**

	A simple function to add buttons to a form. Buttons are specified as a bitmask,
	the values for which should be defined at the top of this file.

	*/
	function addButtons($buttonMask) {
		$buttons = array();

		if ($buttonMask & Form::BTN_LOGIN) {
			$buttons[] = new ElementButton('submit', 'Login', $this->name);
		}

		if ($buttonMask & Form::BTN_SUBMIT) {
			$buttons[] = new ElementButton('submit', 'Submit', $this->name);
		}

		$this->addElementGroup($buttons);
	}

	public function addElement(Element $el) {
		return $this->addElementImpl($el);
	}

	public function addElementHidden($name, $value) {
		return $this->addElement(new ElementHidden($name, null, $value));
	}

	public function addElementReadOnly($title, $value, $roElementName = null) {
		if (!empty($roElementName)) {
			$this->addElement(new ElementHidden($roElementName, null, $value));
		}

		return $this->addElement(new ElementHtml(uniqid(), null, '<fieldset><label>' . $title . '</label>' . $value . '</fieldset>'));
	}

	public function addElementDetached(Element $el) {
		$oldValue = self::$fullyQualifiedElementNames;
		self::$fullyQualifiedElementNames = false;
		$this->addElementImpl($el);
		self::$fullyQualifiedElementNames = $oldValue;
	}

	private function addElementImpl($el) {
		if ($el->isSubmitter()) {
			$this->submitter = &$el;
		}

		$newName = $this->getElementName($el->getName());
		$el->setName($newName);

		$this->elements[$newName] = &$el;

		return $el;
	}

	private function getElementName($element) {
		if (self::$fullyQualifiedElementNames) {
			return $this->name . '-' . $element;
		} else {
			return $element;
		}
	}

	public function setGeneralError($generalError) {
		$this->generalError = $generalError;
	}

	public static function strToForm($s) {
		if (class_exists($s)) {
			$i = new $s();

			if ($i instanceof Form) {
				return $i;
			} else {
				throw new \Exception('Found form class for (' . $s . ') but, it isnt a form instance!');
			}
		} else {
			throw new \Exception('Str to Form failed, the class does not exist: ' . $s);
		}
	}

	public function addElementGroup(array $elementList) {
		foreach ($elementList as $el) {
			if ($el->isSubmitter()) {
				$this->submitter = &$el;
			}

			if (self::$fullyQualifiedElementNames) {
				$el->setName($this->name . '-' . $el->getName());
			}
		}

		// groups are added without a name
		$this->elements[] = $elementList;
	}

	public function getElement($name) {
		if (self::$fullyQualifiedElementNames) {	
			$internalName = $this->name . '-' . $name;
		} else {
			$internalName = $name;
		}

		foreach (array_flatten($this->elements) as $el) {
			if ($el->getName() == $internalName) {
				return $el;
			}
		}

		throw new \Exception('Could not find element "' . $name . '" from form elements.');
	}

	public function setElementError($el, $err) {
		$this->getElement($el)->setValidationError($err);
	}

	public function isSubmitted() {
		if (!isset($this->submitter)) {
			throw new \Exception('Cannot check if a form is submitted, as no element on the form is a valid submitter.');
		}

		if (isset($_POST[$this->submitter->getName()])) {
			if ($this->submitter->getValue() == $this->name) {
				return true;
			}
		}

		return false;
	}

	/**
	This will not reset element groups or hidden elements.
	*/
	public final function reset() {
		unset($_POST[$this->submitter->getName()]);

		foreach($this->elements as $e) {
			if ($e instanceof Element && !($e instanceof ElementHidden)) {
				$e->setValue(null);
			}
		}
	}

	public function getDisplay() {
		ob_start();
		$this->display();
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	public final function validate() {
		if (!$this->isSubmitted()) {
			return false;
		}

		foreach (array_flatten($this->elements) as $e) {
			$name =  $e->getName();

			if ($e instanceof ElementCheckBox && !isset($_POST[$name])) {
				// If checkboxes are not checked browsers do not include them in
				// $_POST, but we have them in our form, so set to 0.
				$e->setValue(0);
			} else if (isset($e->multiple) && $e->multiple) {
				$browserName = str_replace('[]', null, $name);

				if (isset($_POST[$browserName]) && is_array($_POST[$browserName])) {
					$e->setValue($_POST[$browserName]);
				} else {
					$e->setValue(array());
				}
			} else if ($e instanceof ElementButton || $e instanceof ElementHtml) {
				// These elements cannot have values.
			} else {
				if (isset($_POST[$name])) {
					$e->setValue($_POST[$name]);
				} else {
					Logger::messageWarning('Could not set element value on: ' . $name . ' which is a ' . get_class($e));
				}
			}
			
			$e->validate();
		}

		$this->validateExtended();

		// The errors will not have been detected, so lets see
		// if there are any errors.
		foreach (array_flatten($this->elements) as $el) {
			if ($el->getValidationError() != null) {
				return false;
			} 
		}

		return true;
	}

	protected function validateExtended() {
	}

	public function getEnctype() {
		return $this->enctype;
	}

	public function getScripts() {
		return $this->scripts;
	}

	public function getAction() {
		return $this->action;
	}

	public function getName() {
		return $this->name;
	}

	public function setAllElementValues($values) {
		if (!isset($values['id'])) {
			throw new \Exception('setAllElementValues must have an index with the item id (id, not itemID).');
		}

		try {
			$this->getElement('itemId')->setValue($values['id']);
		} catch (\Exception $e) {
			throw new \Exception('Form (' . get_class($this) . ') must contain a itemId element if using setAllElementValues');
		}

		foreach ($this->elements as $elName => $el) {
			if (isset($values[$elName])) {
				$el->setValue($values[$elName]);
			}
		}
	}

	public function getAllElementValues() {
		$values = array();

		foreach (array_flatten($this->elements) as $el) {
			$values[$el->getName()] = $el->getValue();
		}

		return $values;
	}

	public function getElements() {
		return $this->elements;
	}
}

abstract class Element {
	protected $name;
	protected $caption;
	protected $value;
	protected $enabled;
	protected $required = false;
	protected $isSubmitter = false;

	public $description;
	public $suffix;

	private $validationErrorMessage = null;

	/**
	The Javascript function to call onChange (or similar).

	You cannot set this value from the constructor so it has a default value.
	*/
	protected $onChange = '';

	public function __construct($name, $caption, $value = null, $description = null, $suffix = null, $enabled = true) {
		$this->name = $name;
		$this->caption = $caption;
		$this->value = $value;
		$this->description = $description;
		$this->suffix = $suffix;
		$this->enabled = $enabled;

		$this->afterConstruct();
	}

	protected function validateInternals() {}
	protected function afterConstruct() {}
	abstract public function render();


	public final function validate() {
		$this->validateRequired();
		$this->validateInternals();
	}

	private function validateRequired() {
		if ($this->required) {
			$val = $this->getValue();
			if ($val == null || $val == '') {
				$this->setValidationError('This field is required.');
			}
		}
	}

	public final function getType() {
		$typeWithNamespace = get_class($this);
		$components = explode("\\", $typeWithNamespace);
		$class = $components[count($components) - 1];

		return $class;
	}

	public final function setName($newName) {
		$this->name = $newName;
	}

	public final function setValue($value) {
		$this->value = $value;
	}

	public final function setRequired($v) { 
		$this->required = ($v === true);
	}

	public final function setCaption($c) {
		$this->caption = $c;
	}

	public final function getCaption() {
		return $this->caption;
	}

	public final function setOnChange($onChange) {
		$this->onChange = $onChange;
	}

	public final function getName() {
		return $this->name;
	}

	public final function isSubmitter() {
		return $this->isSubmitter;
	}

	public final function setValidationError($validationErrorMessage) {
		if (empty($this->validationErrorMessage)) {
			$this->validationErrorMessage = $validationErrorMessage;
		}
	}

	public final function getValidationError() {
		return $this->validationErrorMessage;
	}

	public function getValue() {
		return $this->value;
	}
}

class ElementRadio extends Element {
	protected $options = array();

	public function addOption($value, $key = null) {
		if ($key === null) {
			$this->options[$value] = $value;
		} else {
			$this->options[$key] = $value;
		}
	}

	public function render() {
		$strOptions = '';

		foreach ($this->options as $key => $val) {
			if ($key === null) {
				$key = $val;
			}

			$sel = ($key == $this->value) ? 'checked = "checked"' : '';

			$strOptions .= sprintf('<li><label><input type = "radio" name = "%s" value = "%s" %s />%s</label></li>', $this->name, $key, $sel, $val);
		}

		return sprintf('<label>%s</label><ul>%s</ul>', $this->caption, $strOptions);
	}
}

class ElementHtml extends Element {
	public function render() {
		echo $this->value;
	}
}


class ElementTextbox extends Element {
	public function render() {

		$value = htmlentities($this->value, ENT_QUOTES);
		$value = stripslashes($value);
		$value = strip_tags($value);

		return sprintf('<label for = "%s">%s</label><textarea id = "%s" name = "%s" rows = "8" cols = "80">%s</textarea>', $this->name, $this->caption, $this->name, $this->name, $this->value);
	}
}

class ElementInputRegex extends ElementInput {
	const PAT_DEFAULT = '/^[a-z0-9 _]+$/i';
	protected $pat = self::PAT_DEFAULT;

	protected $validationExcuse = ' letters, numbers and english punctuation.';

	public function setPattern($regex, $validationExcuse = null) {
		$this->pat = $regex;

		if (!empty($validationExcuse)) {
			$this->validationExcuse = $validationExcuse; 
		}
	}

	public function setPatternToIdentifier() {
		$this->setPattern('#^[a-z_]+$#i', 'letters and underscores');
	}

	public function setPatternToTime() {
		$this->setPattern('#^\d{2}\:\d{2}$#', 'a time, like 08:15');
	}

	public function validateInternals() {
		parent::validateInternals();

		if (empty($this->value)) {
			return;
		}

		if (!preg_match($this->pat, $this->getValue())) {
			if ($this->pat == self::PAT_DEFAULT) {
				$this->setValidationError('This field may only contain letters, numbers');
			} else {
				$this->setValidationError('This field may only contain ' . $this->validationExcuse . '.');
			}
		}
	}
}

class ElementAlphaNumeric extends ElementInputRegex {
	public function setPunctuationAllowed($isAllowed) {
		if ($isAllowed) {
			$this->pat = '/^[\w _\-\.,!]+$/i';
		} else {
			$this->pat = self::PAT_DEFAULT;
		}
	}
}

class ElementCheckbox extends Element {
	public function getValue() {
		return ($this->value == 0) ? 0 : 1;
	}

	public function render() {
		$value = ($this->value) ? 'checked = "checked"' : '';
		return sprintf('<label for = "%s">%s</label><input value = "1" type = "checkbox" id = "%s" name = "%s" %s />', $this->name, $this->caption, $this->name, $this->name, $value);
	}
}

class ElementMultiCheck extends Element {
	private $values;

	public function addOption($key, $value = null) {
		$value = ($value == null) ? $key : $value;

		$this->values[$key] = $value;
	}

	public function getValue() {
		$v = parent::getValue();

		if ($v === null) {
			return array();
		} else {
			return $v;
		}
	}

	public function render() {
		$ret = '<label>' . $this->caption . '</label><ul>';
		foreach ($this->values as $key => $label) {
			$checked = (in_array($key, $this->getValue())) ? 'checked = "checked" ' : '';
			$ret .= sprintf('<li><input type = "checkbox" name = "%s[]" value = "%s" %s /> <label for = "%s">%s</label></li>', $this->name, $key, $checked, $this->name, $label);
		}
		$ret .= '</ul>';

		return $ret;
	}
}

class ElementFile extends Element {
	public $isImage = true;
	public $destinationDir = '/tmp/';
	public $destinationFilename = 'unnamed'; 
	private $tempName = null;

	public $imageMaxW = 80;
	public $imageMaxH = 80;

	public function getFilename() {
		return $this->destinationFilename;
	}

	public function setMaxImageBounds($imageMaxW, $imageMaxH) {
		$this->imageMaxW = $imageMaxW;
		$this->imageMaxH = $imageMaxH;
	}

	public function wasAnythingUploaded() {
		if (sizeof($_FILES) == 0 || empty($_FILES[$this->name]['tmp_name'])) {
			return false;
		} else {
			return true;
		}
	}

	public function validateInternals() {
		if (!$this->wasAnythingUploaded()) {
			return;
		}

		if (!@is_uploaded_file($_FILES[$this->name]['tmp_name'])) {
			$this->setValidationError('Got an object which is not a file.');
		}

		if ($this->isImage) {
			if (!@getimagesize($_FILES[$this->name]['tmp_name'])) {
				$this->setValidationError('Cannot interpret that as an image.');
			} 
		}

		$this->moveFileToTemp();

		if ($this->isImage) {
			$this->validateImage();
		}
	}

	public function setDestination($dir, $filename) {
		$this->destinationDir = $dir;
		$this->destinationFilename = $filename;
	}

	private function moveFileToTemp() {
		$this->tempName = tempnam('tempUploads', uniqid());
		$mov = @move_uploaded_file($_FILES[$this->name]['tmp_name'], $this->tempName);

		if (!$mov) {
			throw new \Exception('Could not move uploaded file: ' . $this->tempName);
		}
	}

	private function validateImage() {
		$type = exif_imagetype($this->tempName);

		if ($type == IMAGETYPE_JPEG) {
			$this->imageResource = imagecreatefromjpeg($this->tempName);
		} else if ($type == IMAGETYPE_GIF) {
			$this->imageResource = imagecreatefromgif($this->tempName);
		} else if ($type == IMAGETYPE_PNG) {
			$this->imageResource = imagecreatefrompng($this->tempName);
		} else {
			$this->setValidationError("Unsupported file type.");
			return;
		}

		if (imagesx($this->imageResource) > $this->imageMaxW || imagesy($this->imageResource) > $this->imageMaxH) {
			$this->setValidationError('Image too big, images may up to ' . $this->imageMaxW . 'x' . $this->imageMaxH . ' pixels, that was ' . imagesx($this->imageResource) . 'x' . imagesy($this->imageResource) . ' pixels.');
		}
	}

	/*
	private function checkUploadSuccess() {
		$uploadStatus = $_FILES[$this->name]['error'];

		if ($uploadStatus !== UPLOAD_ERR_OK) {
			throw new Exception('Upload of file failed.');
		}
	}
	*/

	public function saveJpg() {
		if (!$this->wasAnythingUploaded()) {
			return;
		}
	echo 'saving to ' . $this->destinationDir . $this->destinationFilename;

		imagejpeg($this->imageResource, $this->destinationDir . DIRECTORY_SEPARATOR . $this->destinationFilename);
	}

	public function savePng() {
		if (!$this->wasAnythingUploaded()) {
			return;
		}

		imagepng($this->imageResource, $this->destinationDir . DIRECTORY_SEPARATOR . $this->destinationFilename);
	}

	public function render() {
		return sprintf('<label for = "%s">%s</label><input name = "%s" type = "file" />', $this->name, $this->caption, $this->name);
	}
}

class ElementSelect extends Element {
	protected $options = array();
	private $size = null;
	public $multiple = false;

	public function addOption($value, $key = null, $optGroup = null) {
		if ($key === null) {
			$key = $value;
		}		

		if (!empty($optGroup)) {
			if (!isset($this->options[$optGroup])) {
				$this->options[$optGroup] = array();
			}

			$this->options[$optGroup][$key] = $value;
		} else {
			$this->options[$key] = $value;
		}
	}

	private function buildOptionHtml($key, $val) {
		$sel = ($key == $this->value || (is_array($this->value) && in_array($key, $this->value)) ) ? 'selected = "selected"' : '';

		return sprintf('<option value = "%s" %s>%s</option>', $key, $sel, $val );
	}

	public function render() {
		$strOptions = '';

		foreach ($this->options as $key => $val) {
			if (is_array($val)) { // optgroup
				$optGroup = $val;
				$strOptGroup = '<optgroup label = "' . $key . '">';

				foreach ($optGroup as $key => $val) {
					if ($key === null) {
						$key = $val;
					}

					$strOptGroup .= $this->buildOptionHtml($key, $val);
				}

				$strOptGroup .= '</optgroup>';
				$strOptions .= $strOptGroup;
			} else {
				if ($key === null) {
					$key = $val;
				}

				$strOptions .= $this->buildOptionHtml($key, $val);
			}
		}


		if (!empty($this->onChange)) {
			$onChange = ' onchange = "' . $this->onChange . '()" onkeyup = "' . $this->onChange . '()" ';
		} else {
			$onChange = '';
		}

		if ($this->size != null) {
			$size = 'size = "' . $this->size . '"';
		} else {
			$size = null;
		}

		$multiple = ($this->multiple) ? ' multiple = "true" ' : null;

		return sprintf('<label>%s</label><select id = "%s" %s %s %s name = "%s">%s</select>', $this->caption, $this->name, $multiple, $onChange, $size, $this->name, $strOptions);
	}

	public function setSize($count) {
		if (is_int($count) && $count > 0) {
			$this->size = $count;
		}
	}
}

class ElementAutoSelect extends ElementSelect {
	public function render() {
		$ret = '';

		$ret .= '<label>' . $this->caption . '</label><input id = "' . $this->name . '" name = "' . $this->name . '" value = "' . $this->value . '">'. '</input>';

		$acId = uniqid();
		$ret .= '<script type = "text/javascript">var ac' . $acId . ' = [';

		foreach ($this->options as $key => $val) {
			$ret .= '{value: "' . $key . '", label: "' . $val . '"},' . "\n";
		}

		$ret .= '];';
		$ret .= 'var sel = function(evt, ui) { $("#' . $this->name . '").val(ui.item.value) };';
		$ret .= '$("#' . $this->name . '").autocomplete({ source: ac' . $acId . ', minLength: 0, select: sel, focus: sel  }); ';
		$ret .=' $("#' . $this->name . '").data("autocomplete")._renderItem = function(ul, item) { return $("<li />").data("item.autocomplete", item).append("<a>" + item.label + "</a>").appendTo(ul); }';
		$ret .= '</script>';

		return $ret;
	}

}

class ElementDate extends Element {
	protected function validateInternals() {
		$val = $this->getValue();

		$mathes = array();
		$res = preg_match_all('#\d{4}-\d{2}-\d{2}#', $val, $matches); 

		$ts = strtotime($val);

		if (!$res || $ts < 0 || !$ts) {
			$this->setValidationError('That is a not a valid date.');
		}
	}

	public function render() {
		$today = new \DateTime();
		$today = $today->format('Y-m-d');

		$buf = null;
		$buf .= sprintf('<label for = "%s">%s</label><input id = "%s" name = "%s" value = "%s" /><span class = "dummyLink" onclick = "javascript:document.getElementById(\'%s\').value=\'%s\'">Today</span>', $this->name, $this->caption, $this->name, $this->name, $this->value, $this->name, $today);
		$buf .= <<<JS
<script type = "text/javascript">
	$("#{$this->name}").datepicker({
		dateFormat: "yy-mm-dd", firstDay: 1
	});

</script>
JS;

		return $buf;
	}
}

class ElementInput extends Element {
	protected $minLength = 4;
	protected $maxLength = 64;

	protected $suggestedValues = array();

	public function addSuggestedValue($value, $caption = null) {
		$caption = (empty($caption)) ? $value : $caption;

		$this->suggestedValues[$value] = $caption;
	}

	public function render() {
		$onChange = (empty($this->onChange)) ? null : 'onkeyup = "' . $this->onChange . '()"';

		$value = htmlentities($this->value, ENT_QUOTES);
		$value = stripslashes($value);
		$value = strip_tags($value);

		$classes = ($this->required) ? ' class = "required" ' : null;

		$suggestedValues = array();

		if (!empty($this->suggestedValues)) {
			foreach ($this->suggestedValues as $suggestedValue => $caption) {
				$suggestedValues[] = '<span class = "dummyLink" onclick = "document.getElementById(\'' . $this->name . '\').value = \'' . $suggestedValue . '\'">' . $caption . '</span>';
			}
		}

		return sprintf('<label ' . $classes . 'for = "%s">%s</label><input %s id = "%s" name = "%s" value = "%s" />%s', $this->name, $this->caption, $onChange, $this->name, $this->name, $value, implode(', ', $suggestedValues));
	}

	public function validateInternals() {
		$val = trim($this->getValue());
		$length = strlen($val);

		if (empty($val) && !$this->required) {
			return;
		}

		if ($length < $this->minLength) {
			$this->setValidationError('You should enter more than ' . $this->minLength . ' characters, this is ' . $length . ' characters long.');
			return;
		}

		if ($length > $this->maxLength) {
			$this->setValidationError('You may not enter more than ' . $this->maxLength . ' characters, this is ' . $length . ' characters long.');
			return;
		}
	}

	public function setMinMaxLengths($minLength, $maxLength) {
		$this->minLength = $minLength;
		$this->maxLength = $maxLength;
	}
}

class ElementEmail extends ElementInput {
	public function validateInternals() {
		parent::validateInternals();

		if (!filter_var($this->getValue(), FILTER_VALIDATE_EMAIL)) {
			$this->setValidationError('This is not a valid email address.');
		}		
	}
}

class ElementNumeric extends ElementInput {
	private $allowNegative = false;
	private $allowFloatingPoint = false;
	private $allowNonDenery = false;
	private $maximum = PHP_INT_MAX;
	private $minimum = 0;

	public function setAllowNegative($allowNegative) {
		$this->allowNegative = $allowNegative;
		$this->minimum = ($this->allowNegative) ? -PHP_INT_MAX : 0;
	}

	public function setAllowFloatingPoint($allowFloatingPoint) {
		$this->allowFloatingPoint = $allowFloatingPoint;
	}

	public function setAllowNonDenery($allowNonDenary) {	
	}

	public function setBounds($minimum, $maximum) {
		if (!is_numeric($minimum) || !is_numeric($maximum)) {
			throw new \Exception('The minimum and maximum values on a ElementNumeric must also be numeric.');
		}

		if ($maximum < $minimum) {
			throw new \Exception('The maximum value on an ElementNumeric is less than the minimum!');
		}

		$this->minimum = $minimum;
		$this->maximum = $maximum;
	}

	public function validateInternals() {
		if (empty($this->value) && (!$this->required || $this->maximum == 0)) {
			return;
		}

		if (!is_numeric($this->value)) {
			$this->setValidationError('A number is required.');
		}

		if (!$this->allowNegative && floatval($this->value) < 0) {
			$this->setValidationError('Only positive values are allowed.');
		}	

		if (!$this->allowFloatingPoint && is_float($this->value)) {
			$this->setValidationError('Floating point values are not allowed, use integers (whole numbers).');
		}

		// denery	

		if (floatval($this->value) > $this->maximum || floatval($this->value) < $this->minimum) {
			if ($this->maximum == PHP_INT_MAX) {
				$this->setValidationError('A number, 0 or larger, is required.');
			} else { 
				$this->setValidationError('A number between ' . $this->minimum . ' and ' . $this->maximum . ' is required');
			}
		}
	}
}

class ElementPassword extends Element {
	private $minLength = 6;
	private $maxLength = 128;

	public function setOptional($isOptional = true) {
		if ($isOptional) {
			$this->minLength = 0;
		} else {
			$this->minLength = 6;
		}
	}

	public function validateInternals() {
		
		$length = strlen($this->getValue());

		if ($length < $this->minLength) {
			$this->setValidationError('You should enter more than ' . $this->minLength . ' characters, this is ' . $length . ' characters long.');
			return;
		}

		if ($length > $this->maxLength) {
			$this->setValidationError('You may not enter more than ' . $this->maxLength . ' characters, this is ' . $length . ' characters long.');
			return;
		}
	}

	public function render() {
		return sprintf('<label for = "%s">%s</label><input %s id = "%s" name = "%s" type = "password" />', $this->name, $this->caption, (($this->required == true) ? 'class = "required"' : null), $this->name, $this->name);
	}
}

class ElementHidden extends Element {
	public function isVisible() {
		return false;
	}

	public function render() {
		return '<input name = "' . $this->name . '" type = "hidden" value = "' . $this->value . '" />';
	}
}

class ElementButton extends Element {
	protected $type = 'submit';

	protected function afterConstruct() {
		if ($this->getName() == 'submit') {
			$this->isSubmitter = true; 
		}
	}

	public function render() {
		return '<button name = "' . $this->name . '" type = "' . $this->type . '" value = "' . $this->value . '">' . $this->caption . '</button>';
		return '<input name = "' . $this->name . '" type = "' . $this->type . '" value = "' . $this->caption . '" />';
	}
}

?>
