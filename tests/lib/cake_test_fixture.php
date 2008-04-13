<?php
/* SVN FILE: $Id: cake_test_fixture.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package			cake
 * @subpackage		cake.cake.tests.libs
 * @since			CakePHP(tm) v 1.2.0.4667
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
/**
 * Short description for class.
 *
 * @package    cake
 * @subpackage cake.cake.tests.lib
 */
class CakeTestFixture extends Object {
	var $db = null;
/**
 * Instantiate the fixture.
 *
 * @param object	Cake's DBO driver (e.g: DboMysql).
 *
 * @access public
 */
	function __construct(&$db) {
		$this->db =& $db;
		$this->init();
		if(!class_exists('cakeschema')) {
			uses('model' . DS .'schema');
		}
		$this->Schema = new CakeSchema(array('name'=>'TestSuite', 'connection'=>'test_suite'));
	}
/**
 * Initialize the fixture.
 *
 */
	function init() {
		if (isset($this->import) && (is_string($this->import) || is_array($this->import))) {
			$import = array();

			if (is_string($this->import) || is_array($this->import) && isset($this->import['model'])) {
				$import = am(array('records' => false), ife(is_array($this->import), $this->import, array()));

				$import['model'] = ife(is_array($this->import), $this->import['model'], $this->import);
			} elseif (isset($this->import['table'])) {
				$import = am(array('connection' => 'default', 'records' => false), $this->import);
			}

			if (isset($import['model']) && (class_exists($import['model']) || loadModel($import['model']))) {
				$model =& new $import['model'];
				$db =& ConnectionManager::getDataSource($model->useDbConfig);
				$db->cacheSources = false;
				$this->fields = $model->schema(true);
				$this->fields[$model->primaryKey]['key'] = 'primary';
			} elseif (isset($import['table'])) {
				$model =& new Model(null, $import['table'], $import['connection']);
				$db =& ConnectionManager::getDataSource($import['connection']);
				$db->cacheSources = false;
				$model->name = Inflector::camelize(Inflector::singularize($import['table']));
				$model->table = $import['table'];
				$model->tablePrefix = $db->config['prefix'];
				$this->fields = $model->schema(true);
			}

			if ($import['records'] !== false && isset($model) && isset($db)) {
				$this->records = array();

				$query = array(
					'fields' => array_keys($this->fields),
					'table' => $db->name($model->table),
					'alias' => $model->alias,
					'conditions' => array(),
					'order' => null,
					'limit' => null
				);

				foreach ($query['fields'] as $index => $field) {
					$query['fields'][$index] = $db->name($query['alias']) . '.' . $db->name($field);
				}

				$records = $db->fetchAll($db->buildStatement($query, $model), false, $model->alias);

				if ($records !== false && !empty($records)) {
					$this->records = Set::extract($records, '{n}.' . $model->alias);
				}
			}
		}

		if (!isset($this->table)) {
			$this->table = Inflector::underscore(Inflector::pluralize($this->name));
		}

		if (!isset($this->primaryKey) && isset($this->fields['id'])) {
			$this->primaryKey = 'id';
		}

		if (isset($this->fields)) {
			foreach ($this->fields as $index => $field) {
				if (empty($field['default'])) {
					unset($this->fields[$index]['default']);
				}
			}
		}
	}
/**
 * Run before all tests execute, should return SQL statement to create table for this fixture.
 *
 * @return string	SQL CREATE TABLE statement, false if not applicable.
 *
 * @access public
 */
	function create() {
		if (!isset($this->_create)) {
			if (!isset($this->fields) || empty($this->fields)) {
				return null;
			}
			$this->Schema->_build(array($this->table => $this->fields));
			$this->_create = $this->db->createSchema($this->Schema);
		}
		return $this->_create;
	}
/**
 * Run after all tests executed, should return SQL statement to drop table for this fixture.
 *
 * @return string	SQL DROP TABLE statement, false if not applicable.
 *
 * @access public
 */
	function drop() {
		if (!isset($this->_drop)) {
			$this->Schema->_build(array($this->table => $this->fields));
			$this->_drop = $this->db->dropSchema($this->Schema);
		}
		return $this->_drop;
	}
/**
 * Run before each tests is executed, should return a set of SQL statements to insert records for the table of this fixture.
 *
 * @return array	SQL INSERT statements, empty array if not applicable.
 *
 * @access public
 */
	function insert() {
		if (!isset($this->_insert)) {
			$inserts = array();

			if (isset($this->records) && !empty($this->records)) {
				foreach ($this->records as $record) {
					$fields = array_keys($record);
					$values = array_values($record);

					$insert = 'INSERT INTO ' . $this->db->name($this->db->config['prefix'] . $this->table) . '(';

					foreach ($fields as $field) {
						$insert .= $this->db->name($field) . ',';
					}
					$insert = substr($insert, 0, -1);

					$insert .= ') VALUES (';

					foreach ($values as $values) {
						$insert .= $this->db->value($values) . ',';
					}
					$insert = substr($insert, 0, -1);

					$insert .= ')';

					$inserts[] = $insert;
				}
			}

			$this->_insert = $inserts;
		}

		return $this->_insert;
	}
}
?>