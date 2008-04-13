<?php
/* SVN FILE: $Id: bake.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Command-line code generation utility to automate programmer chores.
 *
 * Bake is CakePHP's code generation script, which can help you kickstart
 * application development by writing fully functional skeleton controllers,
 * models, and views. Going further, Bake can also write Unit Tests for you.
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
/**
 * Bake is a command-line code generation utility for automating programmer chores.
 *
 * @package		cake
 * @subpackage	cake.cake.console.libs
 */
class BakeShell extends Shell {
/**
 * Contains tasks to load and instantiate
 *
 * @var array
 * @access public
 */
	var $tasks = array('Project', 'DbConfig', 'Model', 'Controller', 'View', 'Plugin');
/**
 * Override loadTasks() to handle paths
 *
 * @access public
 */
	function loadTasks() {
		parent::loadTasks();
		$task = Inflector::classify($this->command);
		if (isset($this->{$task}) && !in_array($task, array('Project', 'DbConfig'))) {
			$path = Inflector::underscore(Inflector::pluralize($this->command));
			$this->{$task}->path = $this->params['working'] . DS . $path . DS;
			if (!is_dir($this->{$task}->path)) {
				$this->err(sprintf(__("%s directory could not be found.\nBe sure you have created %s", true), $task, $this->{$task}->path));
				exit();
			}
		}
	}
/**
 * Override main() to handle action
 *
 * @access public
 */
	function main() {

		if (!is_dir(CONFIGS)) {
			$this->Project->execute();
		}

		if (!config('database')) {
			$this->out(__("Your database configuration was not found. Take a moment to create one.", true));
			$this->args = null;
			return $this->DbConfig->execute();
		}
		$this->out('Interactive Bake Shell');
		$this->hr();
		$this->out('[D]atabase Configuration');
		$this->out('[M]odel');
		$this->out('[V]iew');
		$this->out('[C]ontroller');
		$this->out('[P]roject');
		$this->out('[Q]uit');

		$classToBake = strtoupper($this->in(__('What would you like to Bake?', true), array('D', 'M', 'V', 'C', 'P', 'Q')));
		switch($classToBake) {
			case 'D':
				$this->DbConfig->execute();
				break;
			case 'M':
				$this->Model->execute();
				break;
			case 'V':
				$this->View->execute();
				break;
			case 'C':
				$this->Controller->execute();
				break;
			case 'P':
				$this->Project->execute();
				break;
			case 'Q':
				exit(0);
				break;
			default:
				$this->out('You have made an invalid selection. Please choose a type of class to Bake by entering D, M, V, or C.');
		}
		$this->hr();
		$this->main();
	}
/**
 * Quickly bake the MVC
 *
 * @access public
 */
	function all() {
		$ds = 'default';
		$this->hr();
		$this->out('Bake All');
		$this->hr();

		if (isset($this->params['connection'])) {
			$ds = $this->params['connection'];
		}

		if (empty($this->args)) {
			$name = $this->Model->getName($ds);
		}

		if (!empty($this->args[0])) {
			$name = $this->args[0];
			$this->Model->listAll($ds, false);
		}

		$modelExists = false;
		$model = $this->_modelName($name);
		if (App::import('Model', $model)) {
			$object = new $model();
			$modelExists = true;
		} else {
			App::import('Model');
			$object = new Model(array('name' => $name, 'ds' => $ds));
		}

		$modelBaked = $this->Model->bake($object, false);

		if ($modelBaked && $modelExists === false) {
			$this->out(sprintf(__('%s Model was baked.', true), $model));
			if ($this->_checkUnitTest()) {
				$this->Model->bakeTest($model);
			}
			$modelExists = true;
		}

		if ($modelExists === true) {
			$controller = $this->_controllerName($name);
			if ($this->Controller->bake($controller, $this->Controller->bakeActions($controller))) {
				$this->out(sprintf(__('%s Controller was baked.', true), $name));
				if ($this->_checkUnitTest()) {
					$this->Controller->bakeTest($controller);
				}
			}
			if (App::import('Controller', $controller)) {
				$this->View->args = array($controller);
				$this->View->execute();
			}
			$this->out(__('Bake All complete'));
		} else {
			$this->err(__('Bake All could not continue without a valid model', true));
		}

		if (empty($this->args)) {
			$this->all();
		}
		exit();
	}

/**
 * Displays help contents
 *
 * @access public
 */
	function help() {
		$this->out('CakePHP Bake:');
		$this->hr();
		$this->out('The Bake script generates controllers, views and models for your application.');
		$this->out('If run with no command line arguments, Bake guides the user through the class');
		$this->out('creation process. You can customize the generation process by telling Bake');
		$this->out('where different parts of your application are using command line arguments.');
		$this->hr();
		$this->out("Usage: cake bake <command> <arg1> <arg2>...");
		$this->hr();
		$this->out('Params:');
		$this->out("\t-app <path> Absolute/Relative path to your app folder.\n");
		$this->out('Commands:');
		$this->out("\n\tbake help\n\t\tshows this help message.");
		$this->out("\n\tbake all <name>\n\t\tbakes complete MVC. optional <name> of a Model");
		$this->out("\n\tbake project <path>\n\t\tbakes a new app folder in the path supplied\n\t\tor in current directory if no path is specified");
		$this->out("\n\tbake db_config\n\t\tbakes a database.php file in config directory.");
		$this->out("\n\tbake model\n\t\tbakes a model. run 'bake model help' for more info");
		$this->out("\n\tbake view\n\t\tbakes views. run 'bake view help' for more info");
		$this->out("\n\tbake controller\n\t\tbakes a controller. run 'bake controller help' for more info");
		$this->out("");

	}
}
?>