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

require_once 'libAllure/AuthBackend.php';
require_once 'libAllure/Database.php';

class AuthBackendDatabase extends \libAllure\AuthBackend implements AuthPasswordModification {
	// sha1 as default isn't the most secure, but it's a good trade off of security vs speed.
	private $hashAlgo = 'sha1'; 
	private $database;
	private $prefixSalt = null;
	private $suffixSalt = null;

	public function __construct(Database $databaseInstance = null) {
		if ($databaseInstance == null) {
			$this->database = DatabaseFactory::getInstance();
		} else {
			$this->database = $databaseInstance; 
		}

		if (!($this->database instanceof Database)) {
			throw new Exception('No valid database found in AuthBackendDatabase - neither passed to constructor or registered in DatabaseFactory.');
		}
	}	

	public function checkCredentials($username, $password) {
		$sql = 'SELECT u.username, u.password FROM users u WHERE u.username = :username LIMIT 1';
		$stmt = $this->database->prepare($sql);
		$stmt->bindValue(':username', $username);
		$stmt->execute();

		if ($stmt->numRows() == 0) {
			throw new UserNotFoundException();
		} else	{
			$result = $stmt->fetchRow();

			if ($this->hashPassword($password) !== $result['password']) {
				throw new IncorrectPasswordException();
			} else {
				return true;
			}
		}
	}

	public function setHashAlgo($hashAlgo) {
		$this->hashAlgo = $hashAlgo;
	}

	public function setSalt($prefixSalt = null, $suffixSalt = null) {
		$this->prefixSalt = $prefixSalt;
		$this->suffixSalt = $suffixSalt;
	}

	public function hashPassword($password) {
		$password = $this->prefixSalt . $password . $this->suffixSalt;

		return hash($this->hashAlgo, $password);
	}

	public function getUserAttributes($username) {
		$sql = 'SELECT * FROM `users` WHERE `username` = :username LIMIT 1';
		$stmt = $this->database->prepare($sql);
		$stmt->bindValue(':username', $username);
		$stmt->execute();

		if ($stmt->numRows() == 0) {
			throw new UserNotFoundException('Could not getData for user, because local user record was not found in the DB. ');

		}

		$attributes = $stmt->fetchRow(Database::FM_ASSOC);

		return $attributes;
	}

	public function setUserAttribute($username, $field, $value) {
		$sql = 'UPDATE `users` SET `' . DatabaseFactory::getInstance()->escape($field) . '` = :value WHERE `username` = :username LIMIT 1';
		$stmt = $this->database->prepare($sql);
		$stmt->bindValue(':value', $value);
		$stmt->bindValue(':username', $username);
		$stmt->execute();
	}

	public function createTables() {
		$sql = array();
		$sql[] = 'CREATE TABLE IF NOT EXISTS users (id int not null primary key auto_increment, username varchar(32), password varchar(64), `group` int, lastLogin datetime)';
		$sql[] = 'CREATE TABLE IF NOT EXISTS groups (id int not null primary key auto_increment, title varchar(32))';
		$sql[] = 'CREATE TABLE IF NOT EXISTS group_memberships (id int not null primary key auto_increment, `group` int, user int)';
		$sql[] = 'CREATE TABLE IF NOT EXISTS permissions (id int not null primary key auto_increment, `key` varchar(32), description longtext)';
		$sql[] = 'CREATE TABLE IF NOT EXISTS privileges_g (permission int, `group` int)';
		$sql[] = 'CREATE TABLE IF NOT EXISTS privileges_u (permission int, `user` int)';

		foreach($sql as $sqlQuery) {
			$this->database->query($sqlQuery);
		}
	}

	public function setSessionUserPassword($newPlaintextPassword) {
		$user = Session::getUser();
		$username = $user->getUsername();
		$password = $this->hashPassword($newPlaintextPassword);
	
		$sql = 'UPDATE `users` SET password = :password WHERE username = :username';
		$stmt = $this->database->prepare($sql);
		$stmt->bindValue(':password', $password);
		$stmt->bindValue(':username', $username);
		$stmt->execute();
	}
}

?>
