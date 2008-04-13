<?php
/* SVN FILE: $Id: plugin.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * The Plugin Task handles creating an empty plugin, ready to be used
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
 * @subpackage		cake.cake.console.libs.tasks
 * @since			CakePHP(tm) v 1.2
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
if (!class_exists('File')) {
	uses('file');
}
/**
 * Task class for creating a plugin
 *
 * @package		cake
 * @subpackage	cake.cake.console.libs.tasks
 */
class PluginTask extends Shell {
/**
 * Tasks
 *
 */
	var $tasks = array('Model', 'Controller', 'View');
/**
 * path to CONTROLLERS directory
 *
 * @var array
 * @access public
 */
	var $path = null;
/**
 * initialize
 *
 * @return void
 */
	function initialize() {
		$this->path = APP . 'plugins' . DS;
	}
/**
 * Execution method always used for tasks
 *
 * @return void
 */
    function execute() {
		if (empty($this->params['skel'])) {
			$this->params['skel'] = '';
			if (is_dir(CAKE_CORE_INCLUDE_PATH.DS.'cake'.DS.'console'.DS.'libs'.DS.'templates'.DS.'skel') === true) {
				$this->params['skel'] = CAKE_CORE_INCLUDE_PATH.DS.'cake'.DS.'console'.DS.'libs'.DS.'templates'.DS.'skel';
			}
		}

		$plugin = null;

		if(isset($this->args[0])) {
			$plugin = Inflector::camelize($this->args[0]);
			$this->Dispatch->shiftArgs();
			$this->out(sprintf('Plugin: %s', $plugin));
			$pluginPath = Inflector::underscore($plugin) . DS;
			$this->out(sprintf('Plugin: %s', $this->path . $pluginPath));

		}

		if (isset($this->args[0]) && isset($plugin)) {
			$task = Inflector::classify($this->args[0]);
			$this->Dispatch->shiftArgs();

			if (in_array($task, $this->tasks)) {
				$this->{$task}->path = $this->path . $pluginPath . Inflector::underscore(Inflector::pluralize($task)) . DS;

				if (!is_dir($this->{$task}->path)) {
					$this->err(sprintf(__("%s directory could not be found.\nBe sure you have created %s", true), $task, $this->{$task}->path));
				}
				$this->{$task}->loadTasks();
				$this->{$task}->execute();
			}
			exit();
		}

		$this->__interactive($plugin);

	}

/**
 * Interactive interface
 *
 * @access private
 * @return void
 */
	function __interactive($plugin = null) {
        while ($plugin === null) {
            $plugin = $this->in(__('Enter the name of the plugin in CamelCase format', true));
        }

		if (!$this->bake($plugin)) {
			$this->err(sprintf(__("An error occured trying to bake: %s in %s", true), $plugin, $this->path . $pluginPath));
		}
	}

/**
 * Bake the plugin, create directories and files
 *
 * @params $plugin name of the plugin in CamelCased format
 * @access public
 * @return bool
 */
	function bake($plugin) {

		$pluginPath = Inflector::underscore($plugin);

		$this->hr();
		$this->out("Plugin Name: $plugin");
		$this->out("Plugin Directory: {$this->path}{$pluginPath}");
		$this->hr();


		$looksGood = $this->in('Look okay?', array('y', 'n', 'q'), 'y');

		if (low($looksGood) == 'y' || low($looksGood) == 'yes') {
			$verbose = $this->in(__('Do you want verbose output?', true), array('y', 'n'), 'n');

			$Folder = new Folder($this->path . $pluginPath);
			$directories = array('models' . DS . 'behaviors', 'controllers' . DS . 'components', 'views' . DS . 'helpers');

			foreach ($directories as $directory) {
				$Folder->create($this->path . $pluginPath . DS . $directory);
			}

			if (low($verbose) == 'y' || low($verbose) == 'yes') {
				foreach ($Folder->messages() as $message) {
					$this->out($message);
				}
			}

			$errors = $Folder->errors();
			if (!empty($errors)) {
				return false;
			}
		}

        $controllerFileName = $pluginPath . '_app_controller.php';

        $out = "<?php\n\n";
        $out .= "class {$plugin}AppController extends AppController {\n\n";
        $out .= "}\n\n";
        $out .= "?>\n";
        $this->createFile($this->path . $pluginPath. DS . $controllerFileName, $out);

        $modelFileName = $pluginPath . '_app_model.php';

        $out = "<?php\n\n";
        $out .= "class {$plugin}AppModel extends AppModel {\n\n";
        $out .= "}\n\n";
        $out .= "?>\n";
        $this->createFile($this->path . $pluginPath . DS . $modelFileName, $out);

		$this->hr();
		$this->out(sprintf(__("Created: %s in %s", true), $plugin, $this->path . $pluginPath));
		$this->hr();

		return true;
	}
/**
 * Help
 *
 * @return void
 * @access public
 */
	function help() {
		$this->hr();
		$this->out("Usage: cake bake plugin <arg1> <arg2>...");
		$this->hr();
		$this->out('Commands:');
		$this->out("\n\tplugin <name>\n\t\tbakes plugin directory structure");
		$this->out("\n\tplugin <name> model\n\t\tbakes model. Run 'cake bake model help' for more info.");
		$this->out("\n\tplugin <name> controller\n\t\tbakes controller. Run 'cake bake controller help' for more info.");
		$this->out("\n\tplugin <name> view\n\t\tbakes view. Run 'cake bake view help' for more info.");
		$this->out("");
		exit();
	}
}
?>