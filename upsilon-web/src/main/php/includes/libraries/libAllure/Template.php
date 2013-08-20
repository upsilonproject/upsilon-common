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

if (!@include_once 'smarty/libs/Smarty.class.php') {
	include_once 'Smarty/Smarty.class.php';
}

require_once 'libAllure/Inflector.php';
require_once 'libAllure/Form.php';

use \libAllure\Form;

class Template extends \Smarty {
	private $autoClearVars = array();

	public function __construct($cacheDir, $templateDir = 'includes/templates/') {
		parent::__construct();


		if (strpos($cacheDir, '/') === FALSE) {
			$this->compile_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $cacheDir;

			if (!is_dir($this->compile_dir)) {
				mkdir($this->compile_dir);
			}
		} else {
			$this->compile_dir = $cacheDir;
		}

		$this->template_dir = $templateDir;

		$this->registerModifier('htmlify', array($this, 'htmlify'));
		$this->registerModifier('externUrl', array($this, 'externUrl'));
		$this->registerModifier('externUrlOr', array($this, 'externUrlOr'));
		$this->registerModifier('gt', array($this, 'getText'));
	}

	public function registerModifier($content, $callback) {
		if (method_exists($this, 'registerPlugin')) {
			$this->registerPlugin('modifier', $content, $callback);
		} else {
			parent::register_modifier($content, $callback);
		}
	}

	public function addAutoClearVar($var) {
		$this->autoClearVars[] = $var;
	}

	public function getText($input) {
		return _($input);
	}

	public function htmlify($content, $paragraphs = true) {
		$content = stripslashes($content);
		$content = strip_tags($content);
		$content = htmlentities($content);
		$content = str_replace(chr(96), '&quot;', $content);

		if ($paragraphs) {
			$content = $this->parify($content);
		}

		return $content;
	}

	function parify($s) {
		$s = trim($s);
	//  return str_replace("<br />\r\n<br />", "foo", $s);
	    $s = explode("\n", $s);
	    $paragraphs = array();

	    foreach ($s as $paragraph) {
			$paragraph = trim($paragraph);

			// Dont wrap this as a paragraph if;
			// 1) It is short (under 1 char), or
			// 2) If it does not start with a letter, and
			// 3) If it does not start with a number/digit.
		if (strlen($paragraph) <= 1 || (!ctype_alpha($paragraph[0]) && !ctype_digit($paragraph[1])) ) {
				$paragraphs[] = $paragraph;
			} else {
			$paragraphs[] = '<p>' . $paragraph .  '</p>' . "\n";
			}
	    }

	    return implode($paragraphs);
	}

	public function boolToString($test, $onTrue = "Yes", $onFalse = "No", $onNull = "Unknown") {
		if ($test == null) {
			return $onNull;
		} else if ($test) {
			return $onTrue;
		} else {
			return $onFalse;
		}
	}

	public function externUrl($input) {
		if (strpos($input, 'http') !== 0)  {
			$input = 'http://' . $input;
		}

		$input = htmlentities($input);

		return '<a target = "_new" href = "' . $input . '">' . $input . '</a>';
	}

	public function externUrlOr($input, $default = "None") {
		if (empty($input)) {
			return $default;
		} else {
			return $this->externUrl($input);
		}
	}

	public function registerFunction($alias, $function) {
		if (method_exists($this, 'registerPlugin')) {
			$this->registerPlugin('function', $alias, $function);
		} else {
			$this->register_function($alias, $function);
		}
	}

	public function assignForm(Form $f, $prefix = null) {
		$this->assign($prefix . 'form', $f);
		$this->assign($prefix . 'elements', $f->getElements());
		$this->assign($prefix . 'scripts', $f->getScripts());
	}

	public function assignOr($key, &$val, $default) {
		if (!isset($val) || empty($val)) {
			$this->assign($key, $default);
		} else {
			$this->assign($key, $val);
		}
	}

	public function displayForm(Form $f, $tplName = 'form.tpl') {
		$this->assignForm($f);
		$this->display($tplName);
	}

	public function displayWithHeaderAndFooter($tplName) {
		require_once 'includes/widgets/header.php';
		$this->display($tplName);
		require_once 'includes/widgets/footer.php';
	}

	public function error($message = null) {		
		$tpl = $this;
		require_once 'includes/widgets/header.php';

		$this->assign('errorMessage', $message);
		$this->display('error.tpl');

		require_once 'includes/widgets/footer.php';
	}

	public function display($template = null, $cacheId = null, $compileId = null, $parent = null) {
		parent::display($template, $cacheId, $compileId);

		foreach ($this->autoClearVars as $varName) {
			if ($this->get_template_vars($varName)) {
				$this->clear_assign($varName);
			}
		}
	}
}


?>
