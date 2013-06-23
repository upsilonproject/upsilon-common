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

abstract class AuthBackend {
	public abstract function checkCredentials($username, $password);

	public function getUserAttributes($username) {
		if (get_class($this) != '\libAllure\AuthBackendDatabase') {
			$db = new AuthBackendDatabase();
			return $db->getUserAttributes($username);
		}
	}

	private static $registry;

	public static function getInstance() {
		return self::getBackend();
	}

	public final function register($identifier = 'default') {
		self::$registry[$identifier] = $this;
	}

	public static function getBackend($identifier = 'default') {
		if (!isset(self::$registry[$identifier])) {
			throw new \Exception('An AuthBackend instance with identifier ' . $identifier . ' has not been registered via AuthBackend::setBackend($instance).');
		}

		if (!self::$registry[$identifier] instanceof AuthBackend) {
			throw new \Exception('Failed to get auth backend, it is not a valid instance of AuthBackend.');
		}

		return self::$registry[$identifier];
	}

	public static function setBackend(AuthBackend $backend) {
		self::$registry['default'] = $backend;
	}

	public function registerAsDefault() {
		self::register();
	}

	public static function getAllBackendIdentifiers() {
		return array_keys(self::$registry);
	}

	public static function getAllBackends() {
		return self::$registry;
	}
}

interface AuthPasswordModification {
	public function setSessionUserPassword($newPlaintextPassword);	
	public function setUserPassword($username, $password);
}

?>
