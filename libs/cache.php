<?php
/* SVN FILE: $Id: cache.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Caching for CakePHP.
 *
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
 * @since			CakePHP(tm) v 1.2.0.4933
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Included libraries.
 */
if (!class_exists('object')) {
	uses('object');
}
/**
 * Caching for CakePHP.
 *
 * @package		cake
 * @subpackage	cake.cake.libs
 */
class Cache extends Object {
/**
 * Cache engine to use
 *
 * @var object
 * @access protected
 */
	var $_Engine = null;
/**
 * Cache configuration stack
 *
 * @var array
 * @access private
 */
	var $__config = array();
/**
 * Holds name of the current configuration being used
 *
 * @var array
 * @access private
 */
	var $__name = null;
/**
 * Returns a singleton instance
 *
 * @return object
 * @access public
 */
	function &getInstance() {
		static $instance = array();
		if (!isset($instance[0]) || !$instance[0]) {
			$instance[0] =& new Cache();
		}
		return $instance[0];
	}
/**
 * Tries to find and include a file for a cache engine and returns object instance
 *
 * @param $name	Name of the engine (without 'Engine')
 * @return mixed $engine object or null
 * @access private
 */
	function __loadEngine($name) {
		if (!class_exists($name . 'Engine')) {
			$fileName = LIBS . DS . 'cache' . DS . strtolower($name) . '.php';
			if (!require($fileName)) {
				return false;
			}
		}
		return true;
	}
/**
 * Set the cache configuration to use
 *
 * @see app/config/core.php for configuration settings
 * @param string $name Name of the configuration
 * @param array $settings Optional associative array of settings passed to the engine
 * @return array(engine, settings) on success, false on failure
 * @access public
 */
	function config($name = 'default', $settings = array()) {
		$_this =& Cache::getInstance();
		if (is_array($name)) {
			extract($name);
		}

		if (isset($_this->__config[$name])) {
			$settings = array_merge($_this->__config[$name], $settings);
		} elseif (!empty($settings)) {
			$_this->__config[$name] = $settings;
		} elseif ($_this->__name !== null && isset($_this->__config[$_this->__name])) {
			$name = $_this->__name;
			$settings = $_this->__config[$_this->__name];
		} else {
			$name = 'default';
			if(!empty($_this->__config['default'])) {
				$settings = $_this->__config['default'];
			} else {
				$settings = array('engine'=>'File');
			}
		}

		$engine = 'File';
		if (!empty($settings['engine'])) {
			$engine = $settings['engine'];
		}

		if ($name !== $_this->__name) {
			if ($_this->engine($engine, $settings) === false) {
				return false;
			}
			$_this->__name = $name;
			$_this->__config[$name] = $_this->settings($engine);
		}

		$settings = $_this->__config[$name];
		return compact('engine', 'settings');
	}
/**
 * Set the cache engine to use or modify settings for one instance
 *
 * @param string $name Name of the engine (without 'Engine')
 * @param array $settings Optional associative array of settings passed to the engine
 * @return boolean True on success, false on failure
 * @access public
 */
	function engine($name = 'File', $settings = array()) {
		if (!$name || Configure::read('Cache.disable')) {
			return false;
		}

		$cacheClass = $name . 'Engine';
		$_this =& Cache::getInstance();
		if (!isset($_this->_Engine[$name])) {
			if ($_this->__loadEngine($name) === false) {
				return false;
			}
			$_this->_Engine[$name] =& new $cacheClass();
		}

		if ($_this->_Engine[$name]->init($settings)) {
			if (time() % $_this->_Engine[$name]->settings['probability'] == 0) {
				$_this->_Engine[$name]->gc();
			}
			return true;
		}
		$_this->_Engine[$name] = null;
		return false;
	}
/**
 * Garbage collection
 *
 * Permanently remove all expired and deleted data
 *
 * @access public
 */
	function gc() {
		$_this =& Cache::getInstance();
		$config = $_this->config();
		extract($config);
		$_this->_Engine[$engine]->gc();
	}
/**
 * Write data for key into cache
 *
 * @param string $key Identifier for the data
 * @param mixed $value Data to be cached - anything except a resource
 * @param mixed $duration Optional - string configuration name OR how long to cache the data, either in seconds or a
 *			string that can be parsed by the strtotime() function OR array('config' => 'default', 'duration' => '3600')
 * @return boolean True if the data was successfully cached, false on failure
 * @access public
 */
	function write($key, $value, $duration = null) {
		$_this =& Cache::getInstance();
		$config = null;
		if (is_array($duration)) {
			extract($duration);
		} elseif (isset($_this->__config[$duration])) {
			$config = $duration;
			$duration = null;
		}
		$config = $_this->config($config);

		if (!is_array($config)) {
			return null;
		}
		extract($config);

		if (!$_this->isInitialized($engine)) {
			return false;
		}

		if (!$key = $_this->__key($key)) {
			return false;
		}

		if (is_resource($value)) {
			return false;
		}

		if (!$duration) {
			$duration = $settings['duration'];
		}
		$duration = ife(is_numeric($duration), intval($duration), strtotime($duration) - time());

		if ($duration < 1) {
			return false;
		}
		$success = $_this->_Engine[$engine]->write($key, $value, $duration);
		$_this->_Engine[$engine]->init($settings);
		return $success;
	}
/**
 * Read a key from the cache
 *
 * @param string $key Identifier for the data
 * @param string $config name of the configuration to use
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 * @access public
 */
	function read($key, $config = null) {
		$_this =& Cache::getInstance();
		$config = $_this->config($config);

		if (!is_array($config)) {
			return null;
		}

		extract($config);

		if (!$_this->isInitialized($engine)) {
			return false;
		}
		if (!$key = $_this->__key($key)) {
			return false;
		}
		$success = $_this->_Engine[$engine]->read($key);
		$_this->_Engine[$engine]->init($settings);
		return $success;
	}
/**
 * Delete a key from the cache
 *
 * @param string $key Identifier for the data
 * @param string $config name of the configuration to use
 * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
 * @access public
 */
	function delete($key, $config = null) {
		$_this =& Cache::getInstance();

		$config = $_this->config($config);
		extract($config);

		if (!$_this->isInitialized($engine)) {
			return false;
		}

		if (!$key = $_this->__key($key)) {
			return false;
		}

		$success = $_this->_Engine[$engine]->delete($key);
		$_this->_Engine[$engine]->init($settings);
		return $success;
	}
/**
 * Delete all keys from the cache
 *
 * @param boolean $check if true will check expiration, otherwise delete all
 * @param string $config name of the configuration to use
 * @return boolean True if the cache was succesfully cleared, false otherwise
 * @access public
 */
	function clear($check = false, $config = null) {
		$_this =& Cache::getInstance();
		$config = $_this->config($config);
		extract($config);

		if (!$_this->isInitialized($engine)) {
			return false;
		}
		$success = $_this->_Engine[$engine]->clear($check);
		$_this->_Engine[$engine]->init($settings);
		return $success;
	}
/**
 * Check if Cache has initialized a working storage engine
 *
 * @param string $engine Name of the engine
 * @param string $config Name of the configuration setting
 * @return bool
 * @access public
 */
	function isInitialized($engine = null) {
		if (Configure::read('Cache.disable')) {
			return false;
		}
		$_this =& Cache::getInstance();
		if (!$engine && isset($_this->__config[$_this->__name]['engine'])) {
			$engine = $_this->__config[$_this->__name]['engine'];
		}
		return isset($_this->_Engine[$engine]);
	}

/**
 * Return the settings for current cache engine
 *
 * @param string $engine Name of the engine
 * @return array list of settings for this engine
 * @access public
 */
	function settings($engine = null) {
		$_this =& Cache::getInstance();
		if (!$engine && isset($_this->__config[$_this->__name]['engine'])) {
			$engine = $_this->__config[$_this->__name]['engine'];
		}
		if (isset($_this->_Engine[$engine]) && !is_null($_this->_Engine[$engine])) {
			return $_this->_Engine[$engine]->settings();
		}
		return array();
	}
/**
 * generates a safe key
 *
 * @param string $key the key passed over
 * @return mixed string $key or false
 * @access private
 */
	function __key($key) {
		if (empty($key)) {
			return false;
		}
		$key = str_replace(array(DS, '/', '.'), '_', strval($key));
		return $key;
	}
}
/**
 * Storage engine for CakePHP caching
 *
 * @package		cake
 * @subpackage	cake.cake.libs
 */
class CacheEngine extends Object {

/**
 * settings of current engine instance
 *
 * @var int
 * @access public
 */
	var $settings;
/**
 * Iitialize the cache engine
 *
 * Called automatically by the cache frontend
 *
 * @param array $params Associative array of parameters for the engine
 * @return boolean True if the engine has been succesfully initialized, false if not
 * @access public
 */
	function init($settings = array()) {
		$this->settings = array_merge(array('duration'=> 3600, 'probability'=> 100), $settings);
		return true;
	}
/**
 * Garbage collection
 *
 * Permanently remove all expired and deleted data
 *
 * @access public
 */
	function gc() {
	}
/**
 * Write value for a key into cache
 *
 * @param string $key Identifier for the data
 * @param mixed $value Data to be cached
 * @param mixed $duration How long to cache the data, in seconds
 * @return boolean True if the data was succesfully cached, false on failure
 * @access public
 */
	function write($key, &$value, $duration) {
		trigger_error(sprintf(__('Method write() not implemented in %s', true), get_class($this)), E_USER_ERROR);
	}
/**
 * Read a key from the cache
 *
 * @param string $key Identifier for the data
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 * @access public
 */
	function read($key) {
		trigger_error(sprintf(__('Method read() not implemented in %s', true), get_class($this)), E_USER_ERROR);
	}
/**
 * Delete a key from the cache
 *
 * @param string $key Identifier for the data
 * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
 * @access public
 */
	function delete($key) {
	}
/**
 * Delete all keys from the cache
 *
 * @param boolean $check if true will check expiration, otherwise delete all
 * @return boolean True if the cache was succesfully cleared, false otherwise
 * @access public
 */
	function clear($check) {
	}
/**
 * Cache Engine settings
 *
 * @return array settings
 * @access public
 */
	function settings() {
		return $this->settings;
	}
}
?>