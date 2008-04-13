<?php
/* SVN FILE: $Id: schema.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Schema database management for CakePHP.
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
 * @subpackage		cake.cake.libs
 * @since			CakePHP(tm) v 1.2.0.5550
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
if (!class_exists('connectionmanager')) {
	uses('model' . DS . 'connection_manager');
}
/**
 * Base Class for Schema management
 *
 * @package		cake.libs
 * @subpackage	cake.libs
 */
class CakeSchema extends Object {
/**
 * Name of the App Schema
 *
 * @var string
 * @access public
 */
	var $name = null;
/**
 * Path to write location
 *
 * @var string
 * @access public
 */
	var $path = null;
/**
 * File to write
 *
 * @var string
 * @access public
 */
	var $file = 'schema.php';
/**
 * Connection used for read
 *
 * @var string
 * @access public
 */
	var $connection = 'default';
/**
 * Set of tables
 *
 * @var array
 * @access public
 */
	var $tables = array();
/**
 * Constructor
 *
 * @param array $options optional load object properties
 */
	function __construct($options = array()) {
		parent::__construct();

		if (empty($options['name'])) {
			$this->name = preg_replace('/schema$/i', '', get_class($this));
		}

		if ($this->name === 'Cake') {
			$this->name = Inflector::camelize(Configure::read('App.dir'));
		}

		if (empty($options['path'])) {
			$this->path = CONFIGS . 'sql';
		}
		$options = array_merge(get_object_vars($this), $options);
		$this->_build($options);
	}
/**
 * Builds schema object properties
 *
 * @param array $data loaded object properties
 * @access protected
 */
	function _build($data) {
		$file = null;
		foreach ($data as $key => $val) {
			if (!empty($val)) {
				if (!in_array($key, array('name', 'path', 'file', 'connection', 'tables', '_log'))) {
					$this->tables[$key] = $val;
					unset($this->{$key});
				} elseif ($key !== 'tables') {
					if ($key === 'name' && $val !== $this->name) {
						$file = Inflector::underscore($val) . '.php';
					}
					$this->{$key} = $val;
				}
			}
		}

		if (file_exists($this->path . DS . $file) && is_file($this->path . DS . $file)) {
			$this->file = $file;
		}
	}
/**
 * Before callback to be implemented in subclasses
 *
 * @param array $events schema object properties
 * @return boolean Should process continue
 * @access public
 */
	function before($event = array()) {
		return true;
	}
/**
 * After callback to be implemented in subclasses
 *
 * @param array $events schema object properties
 * @access public
 */
	function after($event = array()) {
	}
/**
 * Reads database and creates schema tables
 *
 * @param array $options schema object properties
 * @return array Set of name and tables
 * @access public
 */
	function load($options = array()) {
		if (is_string($options)) {
			$options = array('path'=> $options);
		}

		$this->_build($options);
		extract(get_object_vars($this));

		$class =  $name .'Schema';
		if (!class_exists($class)) {
			if (file_exists($path . DS . $file) && is_file($path . DS . $file)) {
				require_once($path . DS . $file);
			} elseif (file_exists($path . DS . 'schema.php') && is_file($path . DS . 'schema.php')) {
				require_once($path . DS . 'schema.php');
			}
		}

		if (class_exists($class)) {
			$Schema =& new $class($options);
			return $Schema;
		}

		return false;
	}
/**
 * Reads database and creates schema tables
 *
 * @param array $options schema object properties
 * @return array Array indexed by name and tables
 * @access public
 */
	function read($options = array()) {
		extract(array_merge(
			array(
				'connection' => $this->connection,
				'name' => $this->name,
				'models' => true,
			),
			$options
		));
		$db =& ConnectionManager::getDataSource($connection);

		$prefix = null;
		App::import('Model', 'AppModel');

		$tables = array();
		$currentTables = $db->sources();
		if (isset($db->config['prefix'])) {
			$prefix = $db->config['prefix'];
		}

		if (!is_array($models) && $models !== false) {
			$appPaths = array_diff(Configure::read('modelPaths'), Configure::corePaths('model'));
			$models = Configure::listObjects('model', $appPaths, false);
		}

		if (is_array($models)) {
			foreach ($models as $model) {
				if (!class_exists($model)) {
					App::import('Model', $model);
				}
				if (class_exists($model)) {
					$Object =& new $model();
					$Object->setDataSource($connection);
					$table = $db->fullTableName($Object, false);
					if (is_object($Object)) {
						$table = $db->fullTableName($Object, false);
						if (in_array($table, $currentTables)) {
							$key = array_search($table, $currentTables);
							if (empty($tables[$Object->table])) {
								$tables[$Object->table] = $this->__columns($Object);
								$tables[$Object->table]['indexes'] = $db->index($Object);
								unset($currentTables[$key]);
							}
							if (!empty($Object->hasAndBelongsToMany)) {
								foreach($Object->hasAndBelongsToMany as $Assoc => $assocData) {
									if (isset($assocData['with'])) {
										$class = $assocData['with'];
									} elseif ($assocData['_with']) {
										$class = $assocData['_with'];
									}
									if (is_object($Object->$class)) {
										$table = $db->fullTableName($Object->$class, false);
										if (in_array($table, $currentTables)) {
											$key = array_search($table, $currentTables);
											$tables[$Object->$class->table] = $this->__columns($Object->$class);
											$tables[$Object->$class->table]['indexes'] = $db->index($Object->$class);
											unset($currentTables[$key]);
										}
									}
								}
							}
						}
					}
				}
			}
		}
		if (!empty($currentTables)) {
			foreach($currentTables as $table) {
				if ($prefix) {
					$table = str_replace($prefix, '', $table);
				}
				$Object = new AppModel(array('name'=> Inflector::classify($table), 'table'=> $table, 'ds'=> $connection));
				if (in_array($table, array('aros', 'acos', 'aros_acos', Configure::read('Session.table'), 'i18n'))) {
					$tables[$Object->table] = $this->__columns($Object);
					$tables[$Object->table]['indexes'] = $db->index($Object);
				} else {
					$tables['missing'][$table] = $this->__columns($Object);
					$tables['missing'][$table]['indexes'] = $db->index($Object);
				}
			}
		}

		ksort($tables);
		return compact('name', 'tables');
	}
/**
 * Writes schema file from object or options
 *
 * @param mixed $object schema object or options array
 * @param array $options schema object properties to override object
 * @return mixed false or string written to file
 * @access public
 */
	function write($object, $options = array()) {
		if (is_object($object)) {
			$object = get_object_vars($object);
			$this->_build($object);
		}

		if (is_array($object)) {
			$options = $object;
			unset($object);
		}

		extract(array_merge(
			get_object_vars($this), $options
		));

		$out = "\n\nclass {$name}Schema extends CakeSchema {\n\n";

		$out .= "\tvar \$name = '{$name}';\n\n";

		if ($path !== $this->path) {
			$out .= "\tvar \$path = '{$path}';\n\n";
		}

		if ($file !== $this->file) {
			$out .= "\tvar \$file = '{$file}';\n\n";
		}

		if ($connection !== 'default') {
			$out .= "\tvar \$connection = '{$connection}';\n\n";
		}

		$out .= "\tfunction before(\$event = array()) {\n\t\treturn true;\n\t}\n\n\tfunction after(\$event = array()) {\n\t}\n\n";

		if (empty($tables)) {
			$this->read();
		}

		foreach ($tables as $table => $fields) {
			if (!is_numeric($table) && $table !== 'missing') {
				$out .= "\tvar \${$table} = array(\n";
				if (is_array($fields)) {
					$cols = array();
					foreach ($fields as $field => $value) {
						if ($field != 'indexes') {
							if (is_string($value)) {
								$type = $value;
								$value = array('type'=> $type);
							}
							$col = "\t\t\t'{$field}' => array('type'=>'" . $value['type'] . "', ";
							unset($value['type']);
							$col .= join(', ',  $this->__values($value));
						} else {
							$col = "\t\t\t'indexes' => array(";
							$props = array();
							foreach ($value as $key => $index) {
								$props[] = "'{$key}' => array(".join(', ',  $this->__values($index)).")";
							}
							$col .= join(', ', $props);
						}
						$col .= ")";
						$cols[] = $col;
					}
					$out .= join(",\n", $cols);
				}
				$out .= "\n\t\t);\n";
				$out .="\n";
			}
		}
		$out .="}\n";


		$File =& new File($path . DS . $file, true);
		$content = "<?php \n/* SVN FILE: \$Id: schema.php 6311 2008-01-02 06:33:52Z phpnut $ */\n/*". $name ." schema generated on: " . date('Y-m-d H:m:s') . " : ". time() . "*/\n{$out}?>";
		$content = $File->prepare($content);
		if ($File->write($content)) {
			return $content;
		}
		return false;
	}
/**
 * Compares two sets of schemas
 *
 * @param mixed $old Schema object or array
 * @param mixed $new Schema object or array
 * @return array Tables (that are added, dropped, or changed)
 * @access public
 */
	function compare($old, $new = null) {
		if (empty($new)) {
			$new = $this;
		}
		if (is_array($new)) {
			if (isset($new['tables'])) {
				$new = $new['tables'];
			}
		} else {
			$new = $new->tables;
		}

		if (is_array($old)) {
			if (isset($old['tables'])) {
				$old = $old['tables'];
			}
		} else {
			$old = $old->tables;
		}
		$tables = array();
		foreach ($new as $table => $fields) {
			if ($table == 'missing') {
				break;
			}
			if (!array_key_exists($table, $old)) {
				$tables[$table]['add'] = $fields;
			} else {
				$diff = array_diff_assoc($fields, $old[$table]);
				if (!empty($diff)) {
					$tables[$table]['add'] = $diff;
				}
				$diff = array_diff_assoc($old[$table], $fields);
				if (!empty($diff)) {
					$tables[$table]['drop']  = $diff;
				}
			}
			foreach ($fields as $field => $value) {
				if (isset($old[$table][$field])) {
					$diff = array_diff_assoc($value, $old[$table][$field]);
					if (!empty($diff)) {
						$tables[$table]['change'][$field] = array_merge($old[$table][$field], $diff);
					}
				}

				if (isset($add[$table][$field])) {
					$wrapper = array_keys($fields);
					if ($column = array_search($field, $wrapper)) {
						if (isset($wrapper[$column - 1])) {
							$tables[$table]['add'][$field]['after'] = $wrapper[$column - 1];
						}
					}
				}
			}
		}
		return $tables;
	}
/**
 * Formats Schema columns from Model Object
 *
 * @param array $values options keys(type, null, default, key, length, extra)
 * @return array Formatted values
 * @access public
 */
	function __values($values) {
		$vals = array();
		if (is_array($values)) {
			foreach ($values as $key => $val) {
				if (is_array($val)) {
					$vals[] = "'{$key}' => array('".join("', '",  $val)."')";
				} else if (!is_numeric($key)) {
					$val = var_export($val, true);
					$vals[] = "'{$key}' => {$val}";
				}
			}
		}
		return $vals;
	}
/**
 * Formats Schema columns from Model Object
 *
 * @param array $Obj model object
 * @return array Formatted columns
 * @access public
 */
	function __columns(&$Obj) {
		$db =& ConnectionManager::getDataSource($Obj->useDbConfig);
		$fields = $Obj->schema(true);
		$columns = $props = array();
		foreach ($fields as $name => $value) {

			if ($Obj->primaryKey == $name) {
				$value['key'] = 'primary';
			}
			if (!isset($db->columns[$value['type']])) {
				trigger_error('Schema generation error: invalid column type ' . $value['type'] . ' does not exist in DBO', E_USER_NOTICE);
				continue;
			} else {
				$defaultCol = $db->columns[$value['type']];
				if (isset($defaultCol['limit']) && $defaultCol['limit'] == $value['length']) {
					unset($value['length']);
				} elseif (isset($defaultCol['length']) && $defaultCol['length'] == $value['length']) {
					unset($value['length']);
				}
				unset($value['limit']);
			}

			if (isset($value['default']) && ($value['default'] === '' || $value['default'] === false)) {
				unset($value['default']);
			}
			if (empty($value['length'])) {
				unset($value['length']);
			}
			if (empty($value['key'])) {
				unset($value['key']);
			}
			if (empty($value['extra'])) {
				unset($value['extra']);
			}
			$columns[$name] = $value;
		}

		return $columns;
	}
}
?>