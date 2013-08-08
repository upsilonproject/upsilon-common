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

class Sanitizer {
	private $filterAllowUndefined = true;

	const INPUT_GET = 1;
	const INPUT_POST = 2;
	const INPUT_REQUEST = 3;
	const INPUT_SERVER = 4;

	const FORMAT_FOR_DB = 1;
	const FORMAT_FOR_HTML = 2;
	const FORMAT_FOR_ALL = 64;

	private $inputSource = self::INPUT_REQUEST;
	private $variableNamePrefixes = array ('form');
	private static $instance;

	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new Sanitizer();
		}

		return self::$instance;
	}

	public function triggerFailFilter($message) {
		throw new \Exception($message);
	}

	public function setInputSource($inputSource) {
		$this->inputSource = $inputSource;
	}

	private function getInput($name) {
		switch ($this->inputSource) {
			case self::INPUT_GET: $source = $_GET; break;
			case self::INPUT_POST: $source = $_POST; break;
			case self::INPUT_REQUEST: $source = $_REQUEST; break;
			case self::INPUT_SERVER: $source = $_SERVER; break;
			default:
				throw new \Exception('Invalid input source');
		}

		if (isset($source[$name])) {
			return $source[$name];
		} else {
			return $this->variableHunt($source, $name);
		}
	}

	private function variableHunt(array $source, $name) {
		foreach ($source as $key => $value) {
			if (strstr($key, $name) !== FALSE) {
				return $source[$key];
			}	
		}

		if ($this->filterAllowUndefined) {
			return false;
		} else {
			throw new \Exception('Input variable not found: ' . $name);
		}
	}

	public function filterUint($name, $min = 0, $max = PHP_INT_MAX) {
		$min = max($min, 0); // rectify sint

		return $this->filterInt($name, $min, $max);
	}

	public function filterInt($name, $min = null, $max = PHP_INT_MAX) {
		if ($min == null) {
			$min = -PHP_INT_MAX;
		}

		$value = intval($this->getInput($name));

		if ($value < $min) {
			$this->triggerFailFilter('The integer variable '  . $name . ' is below the minimum legal value of ' . $min);
		} else if ($value > $max) {
			$this->triggerFailFilter('The integer variable '  . $name . ' is above the maximum legal value of ' . $max);
		}

		return $value;
	}

	public function filterSint($name, $min = PHP_INT_MIN, $max = PHP_INT_MAX) {
		return $this->filterInt($name, $min, $max);
	}

	public function filterIdentifier($name) {
		$c = $this->getInput($name);
		$c = (string) $c;

		if (preg_match('#^\w[\w\d]+$#', $c) === 0) {
			$this->triggerFailFilter('Content is not an identifier: ' . $name);
		}

		return $c;
	}

	public function filterAlphanumeric($name) {
		$c = $this->getInput($name);
		$c = (string) $c;

		if (preg_match('#^[\w\d ]+$#', $c) === 0) {
			$this->triggerFailFilter('Content is not alphanumeric: ' . $name);
		}

		return $c; 
	}

	public function filterNumeric($content) {
		if (!is_numeric($content)) {
			$this->triggerFailFilter('Content is not numeric');	
		}
	}

	public function filterString($name) {
		return (string) $this->getInput($name);
	}

	public function filterStringEnum($name, array $options, $default = null) {
		try {
			$value = $this->filterString($name);

			if (in_array($value, $options)) {
				return $value; 
			} else {
				return $default;
			}
		} catch (Exception $e) {
			return $default;
		}
	}

	public function filterFilepath() {

	}

	public function escapeStringForClean($content) {
		if ($content == null) {
			return null;
		}

		if (is_string($content)) {
			$content = stripslashes($content);
		}

		return $content;
	}

	public function escapeStringForDatabase($content) {
		return $this->escapeStringForClean($content);
	}

	public function escapeStringForHtml($content) {
		$content = strip_tags($content);
		$content = htmlentities($content);

		return $content;
	}

	public function escapeStringForConsole($content) {
		return $content;
	}


	public function formatStringForDatabase($content) {
		return $this->escapeStringForDatabase($content);
	}

	public function formatStringForHtml($content) {
		return $this->escapeStringForHtml($content);
	}

	public function formatString($content, $destination = 3) {
		if ($destination & self::FORMAT_FOR_DB) {
			$content = $this->formatStringForDatabase($content);
		}

		if ($destination & self::FORMAT_FOR_HTML) {
			$content = $this->formatStringForHtml($content);
		}

		return $content;
	}

	public function formatNumericAsHex($num) {
		return dechex($num);
	}

	public function formatBool($content) {
		if ($content) {
			return true;
		} else {
			return false;
		}
	}
}

?>
