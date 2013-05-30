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
require_once 'libAllure/Logger.php';
require_once 'libAllure/AuthBackend.php';
require_once 'libAllure/Database.php';
require_once 'libAllure/User.php';

class Session {
	private static $sessionName = '';

	// static copy of $_SESSION['user']
	private static $user;

	protected function __construct() {}

	public static function hasPriv($p) {
		if (!self::isLoggedIn()) {
			return false;
		}

		return self::getUser()->hasPriv($p);
	}

	public static function requirePriv($ident) {
		if (!self::hasPriv($ident)) {
			throw new PermissionsException();
		}
	}

	/**
	 * @returns User
	 */
	public static function getUser() {
		if (!self::isLoggedIn()) {
			throw new \Exception('User is not yet logged in.');
		}

		if ($_SESSION['user'] instanceof \libAllure\User) {
			return $_SESSION['user'];
		} else {
			throw new \Exception('Your session is probably corrupted, could not unpack Session::user');
		}
	}

	public static function checkCredentials($username, $password) {
		$credCheck = AuthBackend::getBackend()->checkCredentials($username, $password);

		if ($credCheck) {
			// Create account if it does not exist.
			self::checkLocalAccount($username);

			// Construct the user object and store it in the session
			$user = \libAllure\User::getUser($username);
			$_SESSION['user'] = $user;
			$_SESSION['username'] = $username;

			$now = new \DateTime();
			$now = $now->format('Y-m-d H:s');

			$sql = 'UPDATE users SET lastLogin = :now WHERE id = :id LIMIT 1';
			$stmt = DatabaseFactory::getInstance()->prepare($sql);
			$stmt->bindValue(':now', $now);
			$stmt->bindValue(':id', $user->getId());
			$stmt->execute();

			Logger::messageDebug('Sucessful login for: ' . $username, LogEventType::USER_LOGIN);
			return true;
		} else {
			Logger::messageDebug('Login failed for: ' . $username, LogEventType::LOGIN_FAILURE);
			return false;
		}
	}

	private static function checkLocalAccount($username) {
		$sql = 'SELECT username FROM `users` WHERE `username` = :username LIMIT 1';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':username', $username);
		$stmt->execute();

		if ($stmt->numRows() >= 1) {
			// This user has a local account
		} else {
			// Create a local account for this user.
			$sql = 'INSERT INTO users (username, `group`) VALUES (:username, 1) ';
			$stmt = DatabaseFactory::getInstance()->prepare($sql);
			$stmt->bindValue(':username', $username);

			infobox('This is probably the first time you have used TEFSys, so the system has just setup your user account.');
		}
	}

	public static function setSessionExpiry() {}
	public static function setCookieExpiry() {}

	public static function start() {
		if (!(DatabaseFactory::getInstance() instanceof Database)) {
			throw new \Exception('Session cannot be started without a valid database instance registered in the DatabaseFactory.');
		}

		if (!empty(self::$sessionName)) {
			session_name(self::$sessionName);
		}

		session_start();
	}

	public static function logout() {
		Logger::messageNormal('Logout: ' . self::getUser()->getUsername(), LogEventType::USER_LOGOUT);

		session_unset();
		session_destroy();
		session_regenerate_id();
	}

	public static function isLoggedIn() {
		return (isset($_SESSION['username']));
	}

	public static function setSessionName($s) {
		self::$sessionName = $s;
	}

}

?>
