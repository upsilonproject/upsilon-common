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

abstract class LogEventType {
	const TESTING = 1000;
	const USER_LOGIN = 1001;
	const USER_LOGOUT = 1002;
	CONST USER_REGISTER = 1003;
	const LOGIN_FAILURE = 1060;
	const LOGIN_FAILURE_USERNAME = 1061;
	const LOGIN_FAILURE_PASSWORD = 1062;
}


function syslogListener($priority, $message, $type) {
	syslog(LOG_NOTICE, $message);
}

abstract class Logger {
	private static $handle; 
	private static $filepath = '../logger.log';
	private static $listeners = array();
	private static $fileLoggingEnabled = false;
	private static $logName = 'libAllure-Logger';

	public static function setLogName($logName) {
		self::$logName = $logName;
	}

	public static function open() {
		openlog(self::$logName, LOG_PID | LOG_PERROR, LOG_LOCAL0);
	}

	public static function addListener($funcName) {
		self::$listeners[] = $funcName;
	}

	public static function messageWarning($message, $eventType = null, array $metadata = null) {
		self::message('WARN', $message, $eventType, $metadata);
	}

	public static function messageNormal($message, $eventType = null, array $metadata = null) {
		self::message('NORM', $message, $eventType, $metadata);
	}

	public static function messageException($e, $comment = null) {
		if (!$e instanceof Exception) {
			self::message('EXPT', 'Logging a weird exception: ' . print_r($e, true));

			return; exit;
		}

		$eventType = null;
		self::message('EXPT', get_class($e) . ' Line:' . $e->getLine() . ' File: ' . $e->getFile() . ' :::' . $e->getMessage() . ':::' . $comment, $eventType);

		foreach ($e->getTrace() as $id => $trace) {
			self::message('EXPT', 'ST/' . $id . ' ' . $trace ['file'] . ':' . $trace['line'] . ' ', $eventType);
		}
	}

	public static function messageDebug($message, $eventType = null, array $metadata = null) {
		self::message('DEBG', $message, $eventType, $metadata);
	}

	private static function message($priority, $messageActual, $eventType, array $metadata = null) {
		if (self::$fileLoggingEnabled) {
			self::logToFile($priority, $messageActual, $eventType);
		}

		self::logToListeners($priority, $messageActual, $eventType, $metadata);
	}

	private static function logToFile($priority, $message) {
		if (self::$handle == null) {
			self::$handle = fopen(self::$filepath, 'a');
		}

		$message = ' [' . $priority . '] ' . $message;
		$message = date('c') . $message;

		fputs(self::$handle, $message . "\n");
	}

	public static function setFilePath($filepath) {
		self::$filepath = $filepath;
	}

	public static function setFileLoggingEnabled($toState) {
		self::$fileLoggingEnabled = $toState;
	}

	private static function logToListeners($priority, $message, $eventType, $metadata) {
		foreach (self::$listeners as $funcName) {
			call_user_func($funcName, $priority, $message, $eventType, $metadata);
		}
	}
}

?>
