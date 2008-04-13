<?php
/* SVN FILE: $Id: connection_manager.php 6311 2008-01-02 06:33:52Z phpnut $ */

/**
 * Short description for file.
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
 * @subpackage		cake.cake.libs.model
 * @since			CakePHP(tm) v 0.10.x.1402
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Manages loaded instances of DataSource objects
 *
 * Long description for file
 *
 * @package		cake
 * @subpackage	cake.cake.libs.model
 */

uses ('model' . DS . 'datasources' . DS . 'datasource');
config('database');

class ConnectionManager extends Object {
/**
 * Holds a loaded instance of the Connections object
 *
 * @var object
 * @access public
 */
	var $config = null;
/**
 * Holds instances DataSource objects
 *
 * @var array
 * @access protected
 */
	var $_dataSources = array();
/**
 * Contains a list of all file and class names used in Connection settings
 *
 * @var array
 * @access protected
 */
	var $_connectionsEnum = array();
/**
 * Constructor.
 *
 */
	function __construct() {
		if (class_exists('DATABASE_CONFIG')) {
			$this->config =& new DATABASE_CONFIG();
		}
	}
/**
 * Gets a reference to the ConnectionManger object instance
 *
 * @return object Instance
 * @access public
 * @static
 */
	function &getInstance() {
		static $instance = array();

		if (!isset($instance[0]) || !$instance[0]) {
			$instance[0] =& new ConnectionManager();
		}

		return $instance[0];
	}
/**
 * Gets a reference to a DataSource object
 *
 * @param string $name The name of the DataSource, as defined in app/config/connections
 * @return object Instance
 * @access public
 * @static
 */
	function &getDataSource($name) {
		$_this =& ConnectionManager::getInstance();

		if (in_array($name, array_keys($_this->_dataSources))) {
			return $_this->_dataSources[$name];
		}

		$connections = $_this->enumConnectionObjects();
		if (in_array($name, array_keys($connections))) {
			$conn = $connections[$name];
			$class = $conn['classname'];
			$_this->loadDataSource($name);
			$_this->_dataSources[$name] =& new $class($_this->config->{$name});
			$_this->_dataSources[$name]->configKeyName = $name;
		} else {
			trigger_error(sprintf(__("ConnectionManager::getDataSource - Non-existent data source %s", true), $name), E_USER_ERROR);
			return null;
		}

		return $_this->_dataSources[$name];
	}
/**
 * Gets the list of available DataSource connections
 *
 * @return array List of available connections
 * @access public
 * @static
 */
	function sourceList() {
		$_this =& ConnectionManager::getInstance();
		return array_keys($_this->_dataSources);
	}
/**
 * Gets a DataSource name from an object reference
 *
 * @param object $source DataSource object
 * @return string Datasource name
 * @access public
 * @static
 */
	function getSourceName(&$source) {
		$_this =& ConnectionManager::getInstance();
		$names = array_keys($_this->_dataSources);
		for ($i = 0; $i < count($names); $i++) {
			if ($_this->_dataSources[$names[$i]] === $source) {
				return $names[$i];
			}
		}
		return null;
	}
/**
 * Loads the DataSource class for the given connection name
 *
 * @param mixed $connName A string name of the connection, as defined in Connections config,
 *                        or an array containing the file and class name of the object.
 * @return boolean True on success, null on failure or false if the class is already loaded
 * @access public
 * @static
 */
	function loadDataSource($connName) {
		$_this =& ConnectionManager::getInstance();

		if (is_array($connName)) {
			$conn = $connName;
		} else {
			$connections = $_this->enumConnectionObjects();
			$conn = $connections[$connName];
		}

		if (isset($conn['parent']) && !empty($conn['parent'])) {
			$_this->loadDataSource($conn['parent']);
		}

		if (class_exists($conn['classname'])) {
			return false;
		}

		if (file_exists(MODELS . 'datasources' . DS . $conn['filename'] . '.php')) {
			require (MODELS . 'datasources' . DS . $conn['filename'] . '.php');
		} elseif (fileExistsInPath(LIBS . 'model' . DS . 'datasources' . DS . $conn['filename'] . '.php')) {
			require (LIBS . 'model' . DS . 'datasources' . DS . $conn['filename'] . '.php');
		} else {
			trigger_error(sprintf(__('Unable to load DataSource file %s.php', true), $conn['filename']), E_USER_ERROR);
			return null;
		}
	}
/**
 * Gets a list of class and file names associated with the user-defined DataSource connections
 *
 * @return array An associative array of elements where the key is the connection name
 *               (as defined in Connections), and the value is an array with keys 'filename' and 'classname'.
 * @access public
 * @static
 */
	function enumConnectionObjects() {
		$_this =& ConnectionManager::getInstance();

		if (!empty($_this->_connectionsEnum)) {
			return $_this->_connectionsEnum;
		}
		$connections = get_object_vars($_this->config);

		if ($connections != null) {
			foreach ($connections as $name => $config) {
				$_this->_connectionsEnum[$name] = $_this->__getDriver($config);
			}
			return $_this->_connectionsEnum;
		} else {
			$_this->cakeError('missingConnection', array(array('className' => 'ConnectionManager')));
		}
	}
/**
 * Dynamically creates a DataSource object at runtime, with the given name and settings
 *
 * @param string $name The DataSource name
 * @param array $config The DataSource configuration settings
 * @return object A reference to the DataSource object, or null if creation failed
 * @access public
 * @static
 */
	function &create($name = '', $config = array()) {
		$_this =& ConnectionManager::getInstance();

		if (empty($name) || empty($config) || array_key_exists($name, $_this->_connectionsEnum)) {
			$null = null;
			return $null;
		}

		$_this->config->{$name} = $config;
		$_this->_connectionsEnum[$name] = $_this->__getDriver($config);
		return $_this->getDataSource($name);
	}
/**
 * Returns the file, class name, and parent for the given driver.
 *
 * @return array An indexed array with: filename, classname, and parent
 * @access private
 */
	function __getDriver($config) {
		$_this =& ConnectionManager::getInstance();

		if (!isset($config['datasource'])) {
			$config['datasource'] = 'dbo';
		}

		if (isset($config['driver']) && $config['driver'] != null && !empty($config['driver'])) {
			$filename = $config['datasource'] . DS . $config['datasource'] . '_' . $config['driver'];
			$classname = Inflector::camelize(strtolower($config['datasource'] . '_' . $config['driver']));
			$parent = $_this->__getDriver(array('datasource' => $config['datasource']));
		} else {
			$filename = $config['datasource'] . '_source';
			$classname = Inflector::camelize(strtolower($config['datasource'] . '_source'));
			$parent = null;
		}
		return array('filename'  => $filename, 'classname' => $classname, 'parent' => $parent);
	}
/**
 * Destructor.
 *
 * @access private
 */
	function __destruct() {
		if (Configure::read('Session.save') == 'database' && function_exists('session_write_close')) {
			session_write_close();
		}
	}
}

?>