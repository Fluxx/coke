<?php
/* SVN FILE: $Id: shell.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Base class for Shells
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
 * @subpackage		cake.cake.console.libs
 * @since			CakePHP(tm) v 1.2.0.5012
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
require_once CAKE . 'console' . DS . 'error.php';
/**
 * Base class for command-line utilities for automating programmer chores.
 *
 * @package		cake
 * @subpackage	cake.cake.console.libs
 */
class Shell extends Object {
/**
 * An instance of the ShellDispatcher object that loaded this script
 *
 * @var object
 * @access public
 */
	var $Dispatch = null;
/**
 * If true, the script will ask for permission to perform actions.
 *
 * @var boolean
 * @access public
 */
	var $interactive = true;
/**
 * Holds the DATABASE_CONFIG object for the app. Null if database.php could not be found,
 * or the app does not exist.
 *
 * @var object
 * @access public
 */
	var $DbConfig = null;
/**
 * Contains command switches parsed from the command line.
 *
 * @var array
 * @access public
 */
	var $params = array();
/**
 * Contains arguments parsed from the command line.
 *
 * @var array
 * @access public
 */
	var $args = array();
/**
 * The file name of the shell that was invoked.
 *
 * @var string
 * @access public
 */
	var $shell = null;
/**
 * The class name of the shell that was invoked.
 *
 * @var string
 * @access public
 */
	var $className = null;
/**
 * The command called if public methods are available.
 *
 * @var string
 * @access public
 */
	var $command = null;
/**
 * The name of the shell in camelized.
 *
 * @var string
 * @access public
 */
	var $name = null;
/**
 * Contains tasks to load and instantiate
 *
 * @var array
 * @access public
 */
	var $tasks = array();
/**
 * Contains the loaded tasks
 *
 * @var array
 * @access public
 */
	var $taskNames = array();
/**
 * Contains models to load and instantiate
 *
 * @var array
 * @access public
 */
	var $uses = array();
/**
 *  Constructs this Shell instance.
 *
 */
	function __construct(&$dispatch) {
		$vars = array('params', 'args', 'shell', 'shellName'=> 'name', 'shellClass'=> 'className', 'shellCommand'=> 'command');
		foreach ($vars as $key => $var) {
			if (is_string($key)) {
				$this->{$var} =& $dispatch->{$key};
			} else {
				$this->{$var} =& $dispatch->{$var};
			}
		}

		$shellKey = Inflector::underscore($this->name);
		ClassRegistry::addObject($shellKey, $this);
		ClassRegistry::map($shellKey, $shellKey);
		if (!PHP5 && isset($this->args[0]) && strpos(low(get_class($this)), low(Inflector::camelize($this->args[0]))) !== false) {
			$dispatch->shiftArgs();
		}
		if (!PHP5 && isset($this->args[0]) && low($this->command) == low(Inflector::variable($this->args[0])) && method_exists($this, $this->command)) {
			$dispatch->shiftArgs();
		}
		$this->Dispatch =& $dispatch;
	}
/**
 * Initializes the Shell
 * acts as constructor for subclasses
 * allows configuration of tasks prior to shell execution
 *
 * @access public
 */
	function initialize() {
		$this->_loadModels();
	}
/**
 * Starts up the the Shell
 * allows for checking and configuring prior to command or main execution
 * can be overriden in subclasses
 *
 * @access public
 */
	function startup() {
		$this->_welcome();
	}
/**
 * Displays a header for the shell
 *
 * @access protected
 */
	function _welcome() {
		$this->out('App : '. $this->params['app']);
		$this->out('Path: '. $this->params['working']);
		$this->hr();
	}
/**
 * Loads database file and constructs DATABASE_CONFIG class
 * makes $this->DbConfig available to subclasses
 *
 * @return bool
 * @access protected
 */
	function _loadDbConfig() {
		if (config('database') && class_exists('DATABASE_CONFIG')) {
			$this->DbConfig =& new DATABASE_CONFIG();
			return true;
		}
		$this->err('Database config could not be loaded');
		$this->out('Run \'bake\' to create the database configuration');
		return false;
	}
/**
 * if var $uses = true
 * Loads AppModel file and constructs AppModel class
 * makes $this->AppModel available to subclasses
 * if var $uses is an array of models will load those models
 *
 * @return bool
 * @access protected
 */
	function _loadModels() {
		if ($this->uses === null || $this->uses === false) {
			return;
		}

		uses ('model'.DS.'connection_manager',
			'model'.DS.'datasources'.DS.'dbo_source', 'model'.DS.'model'
		);

		if ($this->uses === true && App::import('Model', 'AppModel')) {
			$this->AppModel = & new AppModel(false, false, false);
			return true;
		}

		if ($this->uses !== true && !empty($this->uses)) {
			$uses = is_array($this->uses) ? $this->uses : array($this->uses);
			$this->modelClass = $uses[0];

			foreach ($uses as $modelClass) {
				$modelKey = Inflector::underscore($modelClass);

				if (!class_exists($modelClass)) {
					App::import('Model', $modelClass);
				}
				if (class_exists($modelClass)) {
					$model =& new $modelClass();
					$this->modelNames[] = $modelClass;
					$this->{$modelClass} =& $model;
					ClassRegistry::addObject($modelKey, $model);
				} else {
					return $this->cakeError('missingModel', array(array('className' => $modelClass)));
				}
			}
			return true;
		}
		return false;
	}
/**
 * Loads tasks defined in var $tasks
 *
 * @return bool
 * @access public
 */
	function loadTasks() {
		if ($this->tasks === null || $this->tasks === false) {
			return;
		}

		if ($this->tasks !== true && !empty($this->tasks)) {

			$tasks = $this->tasks;
			if (!is_array($tasks)) {
				$tasks = array($tasks);
			}

			foreach ($tasks as $taskName) {
				$taskKey = Inflector::underscore($taskName);
				$taskClass = Inflector::camelize($taskName.'Task');
				if (!class_exists($taskClass)) {
					foreach ($this->Dispatch->shellPaths as $path) {
						$taskPath = $path . 'tasks' . DS . Inflector::underscore($taskName).'.php';
						if (file_exists($taskPath)) {
							require_once $taskPath;
							break;
						}
					}
				}
				if (ClassRegistry::isKeySet($taskKey)) {
					$this->taskNames[] = $taskName;
					if (!PHP5) {
						$this->{$taskName} =& ClassRegistry::getObject($taskKey);
						ClassRegistry::map($taskName, $taskKey);
					} else {
						$this->{$taskName} = ClassRegistry::getObject($taskKey);
						ClassRegistry::map($taskName, $taskKey);
					}
				} else {
					$this->taskNames[] = $taskName;
					if (!PHP5) {
						$this->{$taskName} =& new $taskClass($this->Dispatch);
					} else {
						$this->{$taskName} = new $taskClass($this->Dispatch);
					}
				}

				if (!isset($this->{$taskName})) {
					$this->err("Task '".$taskName."' could not be loaded");
					exit();
				}
			}
		}

		return false;
	}
/**
 * Prompts the user for input, and returns it.
 *
 * @param string $prompt Prompt text.
 * @param mixed $options Array or string of options.
 * @param string $default Default input value.
 * @return Either the default value, or the user-provided input.
 * @access public
 */
	function in($prompt, $options = null, $default = null) {
		$in = $this->Dispatch->getInput($prompt, $options, $default);
		if ($options && is_string($options)) {
			if (strpos($options, ',')) {
				$options = explode(',', $options);
			} elseif (strpos($options, '/')) {
				$options = explode('/', $options);
			} else {
				$options = array($options);
			}
		}
		if (is_array($options)) {
			while ($in == '' || ($in && (!in_array(low($in), $options) && !in_array(up($in), $options)) && !in_array($in, $options))) {
				 $in = $this->Dispatch->getInput($prompt, $options, $default);
			}
		}
		if ($in) {
			return $in;
		}
	}
/**
 * Outputs to the stdout filehandle.
 *
 * @param string $string String to output.
 * @param boolean $newline If true, the outputs gets an added newline.
 * @access public
 */
	function out($string, $newline = true) {
		if (is_array($string)) {
			$str = '';
			foreach($string as $message) {
				$str .= $message ."\n";
			}
			$string = $str;
		}
		return $this->Dispatch->stdout($string, $newline);
	}
/**
 * Outputs to the stderr filehandle.
 *
 * @param string $string Error text to output.
 * @access public
 */
	function err($string) {
		if (is_array($string)) {
			$str = '';
			foreach($string as $message) {
				$str .= $message ."\n";
			}
			$string = $str;
		}
		return $this->Dispatch->stderr($string."\n");
	}
/**
 * Outputs a series of minus characters to the standard output, acts as a visual separator.
 *
 * @param boolean $newline If true, the outputs gets an added newline.
 * @access public
 */
	function hr($newline = false) {
		if ($newline) {
			$this->out("\n");
		}
		$this->out('---------------------------------------------------------------');
		if ($newline) {
			$this->out("\n");
		}
	}
/**
 * Displays a formatted error message and exits the application
 *
 * @param string $title Title of the error message
 * @param string $msg Error message
 * @access public
 */
	function error($title, $msg) {
		$out  = "$title\n";
		$out .= "$msg\n";
		$out .= "\n";
		$this->err($out);
		exit();
	}
/**
 * Will check the number args matches otherwise throw an error
 *
 * @param integer $expectedNum Expected number of paramters
 * @param string $command Command
 * @access protected
 */
	function _checkArgs($expectedNum, $command = null) {
		if (!$command) {
			$command = $this->command;
		}
		if (count($this->args) < $expectedNum) {
			$this->error("Wrong number of parameters: ".count($this->args), "Expected: {$expectedNum}\nPlease type 'cake {$this->shell} help' for help on usage of the {$this->name} {$command}");
		}
	}
/**
 * Creates a file at given path
 *
 * @param string $path Where to put the file.
 * @param string $contents Content to put in the file.
 * @return boolean Success
 * @access public
 */
	function createFile ($path, $contents) {
		$path = str_replace(DS . DS, DS, $path);
		$this->out("\n" . sprintf(__("Creating file %s", true), $path));
		if (is_file($path) && $this->interactive === true) {
			$key = $this->in(__("File exists, overwrite?", true). " {$path}",  array('y', 'n', 'q'), 'n');
			if (low($key) == 'q') {
				$this->out(__("Quitting.", true) ."\n");
				exit;
			} elseif (low($key) == 'a') {
				$this->dont_ask = true;
			} elseif (low($key) != 'y') {
				$this->out(__("Skip", true) ." {$path}\n");
				return false;
			}
		}
		if (!class_exists('File')) {
			uses('file');
		}

		if ($File = new File($path, true)) {
			$data = $File->prepare($contents);
			$File->write($data);
			$this->out(__("Wrote", true) ." {$path}");
			return true;
		} else {
			$this->err(__("Error! Could not write to", true)." {$path}.\n");
			return false;
		}
	}
/**
 * Outputs usage text on the standard output. Implement it in subclasses.
 *
 * @access public
 */
	function help() {
		if ($this->command != null) {
			$this->err("Unknown {$this->name} command '$this->command'.\nFor usage, try 'cake {$this->shell} help'.\n\n");
		} else {
			$this->Dispatch->help();
		}
	}
/**
 * Action to create a Unit Test
 *
 * @return boolean Success
 * @access protected
 */
	function _checkUnitTest() {
		if (is_dir(VENDORS.'simpletest') || is_dir(ROOT.DS.APP_DIR.DS.'vendors'.DS.'simpletest')) {
			return true;
		}
		$unitTest = $this->in('Cake test suite not installed.  Do you want to bake unit test files anyway?', array('y','n'), 'y');
		$result = low($unitTest) == 'y' || low($unitTest) == 'yes';

		if ($result) {
			$this->out("\nYou can download the Cake test suite from http://cakeforge.org/projects/testsuite/", true);
		}
		return $result;
	}
/**
 * Makes absolute file path easier to read
 *
 * @param string $file Absolute file path
 * @return sting short path
 * @access public
 */
	function shortPath($file) {
		$shortPath = str_replace(ROOT, null, $file);
		$shortPath = str_replace('..'.DS, '', $shortPath);
		return r(DS.DS, DS, $shortPath);
	}
/**
 * Checks for Configure::read('Routing.admin') and forces user to input it if not enabled
 *
 * @return string Admin route to use
 * @access public
 */
	function getAdmin() {
		$admin = '';
		$cakeAdmin = null;
		$adminRoute = Configure::read('Routing.admin');
		if (!empty($adminRoute)) {
			$cakeAdmin = $adminRoute . '_';
		} else {
			$this->out('You need to enable Configure::write(\'Routing.admin\',\'admin\') in /app/config/core.php to use admin routing.');
			$this->out('What would you like the admin route to be?');
			$this->out('Example: www.example.com/admin/controller');
			while ($admin == '') {
				$admin = $this->in("What would you like the admin route to be?", null, 'admin');
			}
			if ($this->Project->cakeAdmin($admin) !== true) {
				$this->out('Unable to write to /app/config/core.php.');
				$this->out('You need to enable Configure::write(\'Routing.admin\',\'admin\') in /app/config/core.php to use admin routing.');
				exit();
			} else {
				$cakeAdmin = $admin . '_';
			}
		}
		return $cakeAdmin;
	}
/**
 * Creates the proper controller path for the specified controller class name
 *
 * @param string $name Controller class name
 * @return string Path to controller
 * @access protected
 */
	function _controllerPath($name) {
		return low(Inflector::underscore($name));
	}
/**
 * Creates the proper controller plural name for the specified controller class name
 *
 * @param string $name Controller class name
 * @return string Controller plural name
 * @access protected
 */
	function _controllerName($name) {
		return Inflector::pluralize(Inflector::camelize($name));
	}
/**
 * Creates the proper controller camelized name (singularized) for the specified name
 *
 * @param string $name Name
 * @return string Camelized and singularized controller name
 * @access protected
 */
	function _modelName($name) {
		return Inflector::camelize(Inflector::singularize($name));
	}
/**
 * Creates the proper singular model key for associations
 *
 * @param string $name Controller class name
 * @return string Singular model key
 * @access protected
 */
	function _modelKey($name) {
		return Inflector::underscore(Inflector::singularize($name)).'_id';
	}
/**
 * Creates the proper model name from a foreign key
 *
 * @param string $key Foreign key
 * @return string Model name
 * @access protected
 */
	function _modelNameFromKey($key) {
		$name = str_replace('_id', '',$key);
		return $this->_modelName($name);
	}
/**
 * creates the singular name for use in views.
 *
 * @param string $name
 * @return string $name
 * @access protected
 */
	function _singularName($name) {
		return Inflector::variable(Inflector::singularize($name));
	}
/**
 * Creates the plural name for views
 *
 * @param string $name Name to use
 * @return string Plural name for views
 * @access protected
 */
	function _pluralName($name) {
		return Inflector::variable(Inflector::pluralize($name));
	}
/**
 * Creates the singular human name used in views
 *
 * @param string $name Controller name
 * @return string Singular human name
 * @access protected
 */
	function _singularHumanName($name) {
		return Inflector::humanize(Inflector::underscore(Inflector::singularize($name)));
	}
/**
 * Creates the plural human name used in views
 *
 * @param string $name Controller name
 * @return string Plural human name
 * @access protected
 */
	function _pluralHumanName($name) {
		return Inflector::humanize(Inflector::underscore(Inflector::pluralize($name)));
	}
}
?>