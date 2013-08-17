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

class_exists('PDO') or trigger_error('PDO is not installed!');

class Database extends \PDO {
	const FM_ORDER = \PDO::FETCH_NUM;
	const FM_ASSOC = \PDO::FETCH_ASSOC;
	const FM_OBJECT = \PDO::FETCH_OBJ;

	const DB_MYSQL_ERR_CONSTRAINT = 23000;

	public $queryCount = 0;

	/**
	@throws PDOException if it cannot connect
	*/
	public function __construct($dsn, $username, $password) {
		parent::__construct($dsn, $username, $password, array(\PDO::ATTR_PERSISTENT => false));

		$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('\libAllure\DatabaseStatement', array($this)));
		$this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		//$this->setAttribute(PDO::MYSQL_ATTR_DIRECT_QUERY, true);
	}

	public function prepareSelectById($table, $id) {
		$args = func_get_args();
		$table = array_shift($args);
		$id = intval(array_shift($args));

		$fields = implode(array_merge(array('id'), $args), ', ');
		$sql = "SELECT {$fields} FROM {$table} WHERE id = :id";
		$stmt = $this->prepare($sql);
		$stmt->bindValue(':id', $id);
		
		return $stmt;
	}

	public function fetchById($table, $id) {
		$stmt = call_user_func_array(array($this, 'prepareSelectById'), func_get_args());
		$stmt->execute();

		return $stmt->fetchRowNotNull();
	}

	public function escape($s) {
		return $s;
	}
}

class DatabaseFactory {
	const DEFAULT_INSTANCE_NAME = 'default';

	private static $instances = array();

	public static function registerInstance(Database $instance, $name = DatabaseFactory::DEFAULT_INSTANCE_NAME) {
		self::$instances[$name] = $instance;
	}

	public static function getInstance($name = DatabaseFactory::DEFAULT_INSTANCE_NAME) {
		if (!isset(self::$instances[$name]) || !(self::$instances[$name] instanceof Database)) {
			throw new \Exception('Database instance not registered under name:' . $name);
		}

		return self::$instances[$name];
	}
}

class DatabaseStatement extends \PdoStatement {
	public $dbh;
	private $numRows = null;

	protected function __construct($dbh) {
		$this->dbh = $dbh;
		$this->dbh->queryCount++;
		$this->setFetchMode(Database::FM_ASSOC);
	}

	public function fetchRow($fm = Database::FM_ASSOC) {
		return $this->fetch($fm);
	}

	public function fetchRowNotNull($fm = Database::FM_ASSOC) {
		$result = $this->fetchRow();

		if (empty($result)) {
			throw new \Exception('Row not found. Used query: ' . $this->queryString);
		} else {
			return $result;
		}
	}

	public function numRows() {
		if ($this->numRows == null) {
			$sql = 'SELECT found_rows()';
			$result = $this->dbh->query($sql);
			$row = $result->fetchRow();
			$row = current($row);

			$this->numRows = $row;
		}

		return $this->numRows;
	}
}

?>
