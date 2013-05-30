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

require_once 'libAllure/Exceptions.php';

class ErrorHandler {
	protected $css = "margin: auto; width: 75%; background-color: #FFBCBA; border: 2px solid black; line-height: 1.5; padding: 6px; font-family: Verdana, Sans-Serif; font-size: 9pt; text-align: left;";
	protected $cssErrorTitle = 'background-color: red; color: white; text-align: left; margin: 0; padding: .5em; font-size: 12pt;';
	public static $instance;

	/**
	 * Constructs the new class.
	 *
 	 * @param greedy Whether or not this class can be greedy: is allowed to
	 * capture all types of errors that it can bind to.
	 */
	public function __construct($greedy = true) {
		if (!class_exists('\libAllure\SimpleFatalError')) {
			throw new \Exception('SimpleFatalError does not exist, cannot construct a ErrorHandler!');
		}


		if ($greedy) {
			$this->beGreedy();
		}
	}

	/**
	 * @returns ErrorHandler
	 * @deprecated
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return if the error can be simplified.
	 */
	protected function renderSfe(SimpleFatalError $e) {
		if (isset($_GET['showError'])) {
			return;
		}

		$this->clearOutputBuffers();

		echo '<!-- ERROR --!>' . "\n";
		echo '<div style = "' . $this->css . '">' . "\n";
		echo '<h1 style = "' . $this->cssErrorTitle . '">' . 'Error: ' . get_class($e) . '</h1>';
		echo '<strong>' . $e->getMessage() . '</strong>';
		echo '<br /><br />';
		echo _("You cannot do anything about this unless you own this site, please check back a little later. ");
		echo _("If you know the website administrator it might be helpful to get in contact and tell them. Copy the URL for this page and paste it to them.");
		echo _('<br /><br /><a href = "index.php">Click here</a> to return to the home page.');

		echo '<hr style = "height: 0; border: 1px solid black;" />';

		$showErrorUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ((strpos($_SERVER['REQUEST_URI'], '?')) ? '&showError' : '?showError' );

		if (ini_get('display_errors') == '1') {
			echo _("To view technical information about this error, append '<a href = " . $showErrorUrl . ">showError</a>' as a URL argument.");
		}

		$this->errorFooter();

		echo '</div>';

		exit;
	}

	protected function errorFooter() {
		if (function_exists('getProgramName')) {
			echo '<br /><br /><div style = "text-align: right; font-style: italic;">';

			echo getProgramName();
			echo '</div>';
		} else {
			echo '</div>';
		}
	}

	protected function clearOutputBuffers() {
		// Clear the output buffers so we can actually show a message;
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
	}

	/**
	 * Print the error out.
	 *
	 * @param trigger What triggored this error.
	 * @param message The message for the error.
	 * @param code The code for the error.
	 * @param file The file that this error came from.
	 * @param line The line that this error came from.
	 * @param stacktrace A stacktrace leading up to this error ( should be a
 	 * array).
	 */
	protected function render($trigger, $message, $code = null, $file = null, $line = null, $stacktrace = null) {
		if (class_exists('Logger', false)) {
			$metadata = '';

			if (isset($_SERVER['REQUEST_URI'])) {
				$metadata .= ' Request URI: ' . $_SERVER['REQUEST_URI'];
			}

			if (isset($_SERVER['REMOTE_ADDR'])) {
				$metadata .= ' Remote addr: ' . $_SERVER['REMOTE_ADDR'];
			}

			if (class_exists('Session', false) && class_exists('User', false) && Session::isLoggedIn()) {
				$metadata .= ' User:' . Session::getUser()->getUsername();
			}

			Logger::messageWarning('Fatal error;' . $message . ' at ' . $file . ':' . $line . ' ' . $metadata);
		}

		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$this->renderHtml($trigger, $message, $code, $file, $line, $stacktrace);
		} else {
			echo 'Fatal error' . "\n";
			echo '-------------------------------' . "\n";
			echo 'Message: ' . $message . "\n";
			echo 'Source: ' . $file . ':' . $line . "\n";
			exit(1);
		}

		exit;
	}

	protected function renderHtml($trigger, $message, $code = null, $file = null, $line = null, $stacktrace = null) {
		$this->clearOutputBuffers();

		if (!ini_get('display_errors') == '1') {
			$fe = new SimpleFatalError('A serious error has occoured, which cannot be sent via the web browser due to the webserver security configuration.');
			$this->renderSfe($fe);
			return;
		}

		// Show the error.
		echo "<!--\n##\n## ERROR: {$message} \n##\n-->\n";
		echo '<div style = "' . $this->css . '">', "\n";
		echo '<h1 style = "' . $this->cssErrorTitle . '">' . "Error!" . '</h1>', "\n";

		echo '<p>PHP display_errors is turned on, this is the full error message;</p>', "\n";

		echo '<strong>Message: </strong>', $message, '<br />', "\n";

		if (isset($code))
			echo '<strong>Code: </strong>', $code, '<br />', "\n";

		if (isset($line))
			echo '<strong>Line: </strong>', $line, '<br />', "\n";

		if (isset($file))
			echo '<strong>File: </strong>', $file, '<br />', "\n";

		// If the message concerns the database, skip the stack trace for risk
		// of printing db details.
		if (strpos($message, 'database'))
			$stacktrace = null;

		if (isset($stacktrace))
			if (is_array($stacktrace) && !empty($stacktrace)) {
				echo '<strong>Stacktrace: </strong><br />';
				echo '<blockquote><table style = "font-size: 9pt; width: 100%; text-align: left;">';
				echo '<tr><th>ID</th><th>File</th><th>Line</th><th>Class</th><th>function call</th></tr>';
				foreach ($stacktrace as $id => $point) {
					$point['class'] = (isset($point['class']) ? $point['class'] : '(none)');
					echo '<tr><td>' . (sizeof($stacktrace) - $id) . '</td><td>' . $point['file'] . '</td><td>' . $point['line'] . '</td><td>' . $point['class'] . '</td><td>' . $point ['function'] . '(';

					if (isset($point['args'])) {
						foreach ($point['args'] as $id =>  $arg) {
							if (is_object($arg)) {
								echo get_class($arg);
							} else {
								echo $arg;
							}

							if (isset($point['args'][$id + 1])) {
								echo ', ';
							}
						}
					}

					echo ')</td>' . '</tr>';
				}

				echo '</table></blockquote>';
			} else {
				if (is_array($stacktrace)) {
					$stacktrace = 'Empty';
				}

				echo '<strong>Stacktrace: </strong>' . $stacktrace, '<br />';
			}

		echo '<strong>Trigger: </strong>', $trigger, '<br />';

		$this->errorFooter();

		echo '</div>';

		exit;
	}

	public function handleHttpError($code) {
		switch ($code) {
			case '404': $message = 'Object not found.'; break;
			case '403': $message = 'Forbidden.'; break;
			case '401': $message = 'Unauthorized.'; break;
		}

		$this->render('HTTP Error', $message, $code);
	}

	public function handlePhpError($code, $message, $file, $line) {
		// Supressed with @?
		if (error_reporting() == 0) {
			return;
		}

		// mysqli_connect() errors.
		// Dont include "my" in the search because it would return 0, which would evaluate to false and thats a pain in the ass. So, instead, we use "sql_...". This is a long comment.
		if (strpos($message, 'sqli_connect')) {
			if (strpos($message, '1045')) {
				throw new SimpleFatalError("Access denied by the database server");
			}

			if (strpos($message, '10061')) {
				throw new SimpleFatalError("Could not connect to the database server. The server may not be responding.");
			}

			if (strpos($message, '1049')) {
				throw new SimpleFatalError("Connected to the database server, but the database does not exist!");
			}

			throw new SimpleFatalError("There was a general problem while communicating with the database.");
		}

		switch ($code) {
			case 1:
				$code = 'PHP Error';
				break;
			case 2:
				$code = 'PHP Warning';
				break;
			case 4:
				$code = 'PHP Parse Error';
				break;
			case 8:
				$code = 'PHP Notice';
				break;
			case E_CORE_ERROR:
				$code = 'PHP Core Error';
				break;
			case E_CORE_WARNING:
				$code = 'PHP Core Warning';
				break;
			case E_COMPILE_ERROR:
				$code = 'PHP Compile Error';
				break;
			case E_COMPILE_WARNING:
				$code = 'PHP Compile Warning';
				break;
			case E_USER_ERROR:
				$code = 'PHP User Error';
				break;
			case 512:
				$code = 'PHP User Warning';
				break;
			case 1024:
				$code = 'PHP User Notice';
				break;
			case 2048:
				$code = 'PHP E_STRICT ';
				break;
			case 4096:
				$code = 'PHP Recoverable Error';
				break;
			default:
				$code = 'Unknown (' . $code . ')';
		}

		$trigger = 'PHP Triggered Error';

		$this->render($trigger, $message, $code, $file, $line);
	}

	public function handleException($obj) {
		if ($obj instanceof SimpleFatalError) {
			$this->renderSfe($obj);
		}

		$message = $obj->getMessage();
		$code = $obj->getCode();
		$file = $obj->getFile();
		$line = $obj->getLine();
		$stacktrace = $obj->getTrace();

		$this->render('Exception -> ' . get_class($obj), $message, $code, $file, $line, $stacktrace);

	}

	public function setCss($css) {
		$this->css = $css;
	}

	public function null() {}

	public function beLazy() {
		error_reporting(E_ERROR);
		set_error_handler(array(&$this, 'null'));
//		restore_error_handler();
		restore_exception_handler();
	}

	public function beGreedy() {
		error_reporting(E_ALL | E_STRICT);

		// check for http errors
		if (isset($_REQUEST['httpError'])) {
			$this->handleHttpError(intval($_REQUEST['httpError']));
		}
		
		set_error_handler(array(&$this, 'handlePhpError'), error_reporting());
		set_exception_handler(array(&$this, 'handleException'));
	}
}

?>
