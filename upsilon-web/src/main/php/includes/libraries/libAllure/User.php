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

class User {
	private $privs = array();
	private $usergroups = array();
	private $data = array();
	private $username;

	private function __construct($username) {
		$this->username = $username;
		$this->getData('username', false);
		$this->updateUsergroups();
		$this->updatePrivileges();
	}

	public function requirePriv($priv) {
		if (!$this->hasPriv($priv)) {
			throw new UserPrivilegeException($priv);
		}
	}

	public static function getUserById($id) {
		$id = intval($id);

		$sql = 'SELECT u.* FROM users u WHERE id = :id LIMIT 1';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		if ($stmt->numRows() == 0) {
			throw new UserNotFoundException();
		} else {
			$result = $stmt->fetchRow();

			return new User($result['username']);
		}
	}

	public function getManager() {
		if (isset($this->data['manager'])) {
			return $this->data['manager'];
		} else {
			throw new Exception('Manager not set.');
		}
	}

	public static function getUser($username) {
		return new User($username);
	}

	public function updatePrivileges() {
		$this->privs = array();

		$uPrivs = array(); $gPrivs = array();

		//
		// Group privs
		//
//		$sql = 'SELECT distinct p.key, p.description, g.title AS groupTitle, g.id AS groupId FROM permissions p, privileges_g gp, groups g, users u, group_memberships gm WHERE gm.user = u.id AND gm.group = g.id AND gp.permission = p.id AND gp.group = gm.id AND u.id = "' . $this->getId() . '" ';
		$username = $this->getUsername();
		$sql = <<<SQL
SELECT
   u.id,
   u.username,
   gm.user,
   g.id AS groupId,
   g.title AS groupTitle,
   p.key,
   p.description
FROM
   permissions p,
   privileges_g gp,
   groups g,
   group_memberships gm,
   users u

WHERE
   gm.`user` = u.id AND
   gm.`group` = gp.`group` AND
   gp.`group` = g.id AND
   gp.permission = p.id AND
   u.username = "$username"
SQL;

		$result = DatabaseFactory::getInstance()->query($sql);

		if ($result->numRows()) {
			foreach ($result->fetchAll() as $priv) {
				if ($priv['description'] == '') {
					$priv['description'] = '???';
				}

				$priv['source'] = 'Group';
				$priv['sourceTitle'] = $priv['groupTitle'];
				$priv['sourceId'] = $priv['groupId'];

				$gPrivs[$priv['key']] = $priv;
			}
		}

		//
		// Principle group privs
		//
		$sql = 'SELECT distinct p.key, p.description, u.username as userUsername, u.id as userId, g.id groupId, g.title groupTitle FROM permissions p, users u, groups g, privileges_g gp WHERE u.group = g.id AND gp.`group` = g.id AND gp.permission = p.id AND u.id = "' . $this->getId() .  '" ';
		$result = DatabaseFactory::getInstance()->query($sql);

		if ($result->numRows()) {
			foreach ($result->fetchAll() as $priv) {
				if ($priv['description'] == '') {
					$priv['description'] = '???';
				}

				$priv['source'] = 'Group';
				$priv['sourceTitle'] = $priv['groupTitle'];
				$priv['sourceId'] = $priv['groupId'];

				$gPrivs[$priv['key']] = $priv;
			}
		}

		//
		// User privs
		//
		$sql = 'SELECT distinct p.key, p.description, u.username as userUsername, u.id as userId FROM permissions p, privileges_u up, users u WHERE up.user = u.id AND up.permission = p.id AND u.id = "' . $this->getId() . '" ';
		$result = DatabaseFactory::getInstance()->query($sql);

		if ($result->numRows()) {
			foreach ($result->fetchAll() as $priv) {
				if ($priv['description'] == '') {
					$priv['description'] = '???';
				}

				$priv['source'] = 'User';
				$priv['sourceTitle'] = $priv['userUsername'];
				$priv['sourceId'] = $priv['userId'];

				$uPrivs[$priv['key']] = $priv;
			}
		}

		$this->privs = array_merge($uPrivs, $gPrivs);

		// FIXME Sort this for nicer display

		return true;
	}

	public function getPrivs() {
		return $this->privs;
	}

	public function hasPriv($ident) {
		if (!is_string($ident)) {
			throw new Exception('Priv ident must be a string, passed to User::hasPriv');
		}

		return (array_key_exists($ident, $this->privs) || array_key_exists('SUPERUSER', $this->privs)) !== false;
	}

	public function getPriv($ident) {
		if ($this->hasPriv($ident)) {
			return $ident;
		} else {
			throw new Exception('Trying to get a priv that the user does not have (' . $ident . ')');
		}
	}

	public function getUsergroups() {
		if (sizeof($this->usergroups) == 0) {
			$this->updateUsergroups();
		}

		return $this->usergroups;
	}

	function updateUsergroups() {
		$this->usergroups = array();

		$sql = 'SELECT g.*, "primary" AS type FROM groups g, users u WHERE u.group = g.id AND u.id = :id LIMIT 1';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $this->getId());
		$stmt->execute();

		$this->usergroups['primary'] = $stmt->fetchRow();

		$sql = 'SELECT g.*, "supplimentary" AS type FROM group_memberships gm, groups g WHERE gm.group = g.id AND gm.user = :id ';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->bindValue(':id', $this->getId());
		$stmt->execute();

		foreach ($stmt->fetchAll() as $group) {
			if ($group['title'] != $this->usergroups['primary']['title']) {
				$this->usergroups[$group['title']] = $group;
			}
		}

		return true;
	}

	function getId() {
		return $this->getData('id');
	}

	function getUsername() {
		return $this->username;
	}

	function getDataAll() {
		return $this->data;
	}

	/**
	 * @Deprecated
	 */
	public function getData($field, $useCache = true) {
		return $this->getAttribute($field, $useCache);
	}

	public function getAttribute($field, $useCache = true) {
		if (!$useCache) {
			$this->updateAttributeCache();
		}

		return $this->data[$field];
	}

	public function setAttribute($key, $value) {
		return AuthBackend::getBackend()->setUserAttribute($this->username, $key, $value); 
	}

	/** @Deprecated */
	function setData($key, $value) {
		$this->setAttribute($key, $value);	
	}

	private function updateAttributeCache() {
		$this->data = AuthBackend::getBackend()->getUserAttributes($this->username);
	}

	public function __toString() {
		return 'User class for (' . $this->getUsername() . ')';
	}

	public static function getAllLocalUsers() {
		$sql = 'SELECT u.*, g.id as groupId, g.title as groupTitle, g.css FROM users u, groups g WHERE u.group = g.id ORDER BY u.id ';
		$stmt = DatabaseFactory::getInstance()->prepare($sql);
		$stmt->execute();

		return $stmt->fetchAll();
	}
}

?>
