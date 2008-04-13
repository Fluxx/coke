<?php
/* SVN FILE: $Id: dbo_odbc.php 6311 2008-01-02 06:33:52Z phpnut $ */

/**
 * ODBC for DBO
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake.libs.model.dbo
 * @since			CakePHP(tm) v 0.10.5.1790
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Short description for class.
 *
 * Long description for class
 *
 * @package		cake
 * @subpackage	cake.cake.libs.model.datasources.dbo
 */
class DboOdbc extends DboSource{

/**
 * Driver description
 *
 * @var string
 */
	var $description = "ODBC DBO Driver";

/**
 * Table/column starting quote
 *
 * @var string
 */
	var $startQuote = "`";

/**
 * Table/column end quote
 *
 * @var string
 */
	var $endQuote = "`";

/**
 * Driver base configuration
 *
 * @var array
 */
	var $_baseConfig = array('persistent' => true,
				'login' => 'root',
				'password' => '',
				'database' => 'cake',
				'connect'  => 'odbc_pconnect'
	);

/**
 * Enter description here...
 *
 * @var unknown_type
 */

	var $columns = array();

	//	var $columns = array('primary_key' => array('name' => 'int(11) DEFAULT NULL auto_increment'),
	//						'string' => array('name' => 'varchar', 'limit' => '255'),
	//						'text' => array('name' => 'text'),
	//						'integer' => array('name' => 'int', 'limit' => '11'),
	//						'float' => array('name' => 'float'),
	//						'datetime' => array('name' => 'datetime', 'format' => 'Y-m-d h:i:s', 'formatter' => 'date'),
	//						'timestamp' => array('name' => 'datetime', 'format' => 'Y-m-d h:i:s', 'formatter' => 'date'),
	//						'time' => array('name' => 'time', 'format' => 'h:i:s', 'formatter' => 'date'),
	//						'date' => array('name' => 'date', 'format' => 'Y-m-d', 'formatter' => 'date'),
	//						'binary' => array('name' => 'blob'),
	//						'boolean' => array('name' => 'tinyint', 'limit' => '1'));

/**
 * Connects to the database using options in the given configuration array.
 *
 * @return boolean True if the database could be connected, else false
 */
	function connect() {
		$config = $this->config;
		$connect = $config['connect'];

		$this->connected = false;
		$this->connection = $connect($config['database'], $config['login'], $config['password']);

		if ($this->connection) {
				$this->connected = true;
		}

		return $this->connected;
	}

/**
 * Disconnects from database.
 *
 * @return boolean True if the database could be disconnected, else false
 */
	function disconnect() {
		return @odbc_close($this->connection);
	}
/**
 * Executes given SQL statement.
 *
 * @param string $sql SQL statement
 * @return resource Result resource identifier
 * @access protected
 */
	function _execute($sql) {
		return odbc_exec($this->connection, $sql);
	}
/**
 * Returns an array of sources (tables) in the database.
 *
 * @return array Array of tablenames in the database
 */
	function listSources() {

		$cache = parent::listSources();
		if ($cache != null) {
			return $cache;
		}

		/*$result = odbc_tables($this->connection);
		if (function_exists('odbc_fetch_row')) {
			echo 'GOOD';
		} else {
			echo 'BAD';
		}*/

		$result = odbc_tables($this->connection);

		$tables = array();
		while (odbc_fetch_row($result)) {
			array_push($tables, odbc_result($result, "TABLE_NAME"));
		}

		parent::listSources($tables);
		return $tables;
	}
/**
 * Returns an array of the fields in given table name.
 *
 * @param Model $model Model object to describe
 * @return array Fields in table. Keys are name and type
 */
	function &describe(&$model) {
		$cache=parent::describe($model);

		if ($cache != null) {
				return $cache;
		}

		$fields = array();
		$sql = 'SELECT * FROM ' . $this->fullTableName($model);
		$result = odbc_exec($this->connection, $sql);

		$count = odbc_num_fields($result);

		for ($i = 1; $i <= $count; $i++) {
				$cols[$i - 1] = odbc_field_name($result, $i);
		}

		foreach ($cols as $column) {
			$type = odbc_field_type(odbc_exec($this->connection, "SELECT " . $column . " FROM " . $this->fullTableName($model)), 1);
			$fields[$column] = array('type' => $type);
		}

		$this->__cacheDescription($model->tablePrefix . $model->table, $fields);
		return $fields;
	}

	function name($data) {
		if ($data == '*') {
				return '*';
		}

		$pos = strpos($data, '`');

		if ($pos === false) {
				$data = '' . str_replace('.', '.', $data) . '';
		//$data = '`'. str_replace('.', '`.`', $data) .'`';
		}

		return $data;
	}

/**
 * Returns a quoted and escaped string of $data for use in an SQL statement.
 *
 * @param string $data String to be prepared for use in an SQL statement
 * @param string $column The column into which this data will be inserted
 * @return string Quoted and escaped
 * @todo Add logic that formats/escapes data based on column type
 */
	function value($data, $column = null) {
		$parent=parent::value($data, $column);

		if ($parent != null) {
				return $parent;
		}

		if ($data === null) {
				return 'NULL';
		}

		// $data = mysql_real_escape_string($data, $this->connection);

		if (!is_numeric($data)) {
				$return = "'" . $data . "'";
		} else {
				$return = $data;
		}

		return $return;
	}

/**
 * Not sure about this one, MySQL needs it but does ODBC?  Safer just to leave it
 * Translates between PHP boolean values and MySQL (faked) boolean values
 *
 * @param mixed $data Value to be translated
 * @return mixed Converted boolean value
 */
	function boolean($data) {
		if ($data === true || $data === false) {
				if ($data === true) {
					return 1;
				}

				return 0;
		} else {
				if (intval($data !== 0)) {
					return true;
				}

				return false;
		}
	}

/**
 * Begin a transaction
 *
 * @param unknown_type $model
 * @return boolean True on success, false on fail
 * (i.e. if the database/model does not support transactions).
 */
	function begin(&$model) {
		if (parent::begin($model)) {
				if (odbc_autocommit($this->connection, false)) {
					$this->_transactionStarted = true;
					return true;
				}
		}

		return false;
	}

/**
 * Commit a transaction
 *
 * @param unknown_type $model
 * @return boolean True on success, false on fail
 * (i.e. if the database/model does not support transactions,
 * or a transaction has not started).
 */
	function commit(&$model) {
		if (parent::commit($model)) {
				if (odbc_commit($this->connection)) {
					$this->_transactionStarted = false;
					return true;
				}
		}

		return false;
	}

/**
 * Rollback a transaction
 *
 * @param unknown_type $model
 * @return boolean True on success, false on fail
 * (i.e. if the database/model does not support transactions,
 * or a transaction has not started).
 */
	function rollback(&$model) {
		if (parent::rollback($model)) {
				$this->_transactionStarted=false;
				return odbc_rollback($this->connection);
		}

		return false;
	}

/**
 * Returns a formatted error message from previous database operation.
 *
 * @return string Error message with error number
 */
	function lastError() {
		if (odbc_error($this->connection)) {
				return odbc_error($this->connection) . ': ' . odbc_errormsg($this->connection);
		}

		return null;
	}

/**
 * Returns number of affected rows in previous database operation. If no previous operation exists,
 * this returns false.
 *
 * @return integer Number of affected rows
 */
	function lastAffected() {
		if ($this->_result) {
				return null;
		}

		return null;
	}

/**
 * Returns number of rows in previous resultset. If no previous resultset exists,
 * this returns false.
 *
 * @return integer Number of rows in resultset
 */
	function lastNumRows() {
		if ($this->_result) {
				return@odbc_num_rows($this->_result);
		}

		return null;
	}

/**
 * Returns the ID generated from the previous INSERT operation.
 *
 * @param unknown_type $source
 * @return int
 */
	function lastInsertId($source = null) {
		$result=$this->fetchRow('SELECT @@IDENTITY');
		return $result[0];
	}

/**
 * Enter description here...
 *
 * @param string $real Real database-layer column type (i.e. "varchar(255)")
 */
	function column($real) {
		if (is_array($real)) {
				$col=$real['name'];

				if (isset($real['limit'])) {
					$col .= '(' . $real['limit'] . ')';
				}

				return $col;
		}

		return $real;
	}

/**
 * Enter description here...
 *
 * @param unknown_type $results
 */
	function resultSet(&$results) {
		$this->results=&$results;
		$this->map=array();
		$num_fields   =odbc_num_fields($results);
		$index        =0;
		$j            =0;

		while ($j < $num_fields) {
				$column = odbc_fetch_array($results, $j);

				if (!empty($column->table)) {
					$this->map[$index++] = array($column->table,
								$column->name);
				} else {
					echo array(0,
								$column->name);

					$this->map[$index++]=array(0,
								$column->name);
				}

				$j++;
		}
	}

/**
 * Fetches the next row from the current result set
 *
 * @return unknown
 */
	function fetchResult() {
		if ($row = odbc_fetch_row($this->results)) {
				$resultRow=array();
				$i=0;

				foreach ($row as $index => $field) {
					list($table, $column)      = $this->map[$index];
					$resultRow[$table][$column]=$row[$index];
					$i++;
				}

				return $resultRow;
		} else {
				return false;
		}
	}
}
?>