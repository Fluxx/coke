<?php
/* SVN FILE: $Id: error.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * ErrorHandler for Console Shells
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
 * @subpackage		cake.cake.console
 * @since			CakePHP(tm) v 1.2.0.5074
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Error Handler for Cake console.
 *
 * @package		cake
 * @subpackage	cake.cake.console
 */
class ErrorHandler extends Object {
/**
 * Standard output stream.
 *
 * @var filehandle
 * @access public
 */
	var $stdout;
/**
 * Standard error stream.
 *
 * @var filehandle
 * @access public
 */
	var $stderr;
/**
 * Class constructor.
 *
 * @param string $method Method dispatching an error
 * @param array $messages Error messages
 */
	function __construct($method, $messages) {
		$this->stdout = fopen('php://stdout', 'w');
		$this->stderr = fopen('php://stderr', 'w');
		if (Configure::read() > 0 || $method == 'error') {
			call_user_func_array(array(&$this, $method), $messages);
		} else {
			call_user_func_array(array(&$this, 'error404'), $messages);
		}
	}
/**
 * Displays an error page (e.g. 404 Not found).
 *
 * @param array $params Parameters (code, name, and message)
 * @access public
 */
	function error($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr($code . $name . $message."\n");
		exit();
	}
/**
 * Convenience method to display a 404 page.
 *
 * @param array $params Parameters (url, message)
 * @access public
 */
	function error404($params) {
		extract($params, EXTR_OVERWRITE);
		$this->error(array('code' => '404',
							'name' => 'Not found',
							'message' => sprintf(__("The requested address %s was not found on this server.", true), $url, $message)));
		exit();
	}
/**
 * Renders the Missing Controller web page.
 *
 * @param array $params Parameters (className)
 * @access public
 */
	function missingController($params) {
		extract($params, EXTR_OVERWRITE);
		$controllerName = str_replace('Controller', '', $className);
		$this->stderr(sprintf(__("Missing Controller '%s'", true), $controllerName));
		exit();
	}
/**
 * Renders the Missing Action web page.
 *
 * @param array $params Parameters (action, className)
 * @access public
 */
	function missingAction($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Missing Method '%s' in '%s'", true), $action, $className));
		exit();
	}
/**
 * Renders the Private Action web page.
 *
 * @param array $params Parameters (action, className)
 * @access public
 */
	function privateAction($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Trying to access private method '%s' in '%s'", true), $action, $className));
		exit();
	}
/**
 * Renders the Missing Table web page.
 *
 * @param array $params Parameters (table, className)
 * @access public
 */
	function missingTable($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Missing database table '%s' for model '%s'", true), $table, $className));
		exit();
	}
/**
 * Renders the Missing Database web page.
 *
 * @param array $params Parameters
 * @access public
 */
	function missingDatabase($params = array()) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(__("Missing Database", true));
		exit();
	}
/**
 * Renders the Missing View web page.
 *
 * @param array $params Parameters (file, action, className)
 * @access public
 */
	function missingView($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Missing View '%s' for '%s' in '%s'", true), $file, $action, $className));
		exit();
	}
/**
 * Renders the Missing Layout web page.
 *
 * @param array $params Parameters (file)
 * @access public
 */
	function missingLayout($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Missing Layout '%s'", true), $file));
		exit();
	}
/**
 * Renders the Database Connection web page.
 *
 * @param array $params Parameters
 * @access public
 */
	function missingConnection($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(__("Missing Database Connection. Try 'cake bake'", true));
		exit();
	}
/**
 * Renders the Missing Helper file web page.
 *
 * @param array $params Parameters (file, helper)
 * @access public
 */
	function missingHelperFile($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Missing Helper file '%s' for '%s'", true), $file, Inflector::camelize($helper)));
		exit();
	}
/**
 * Renders the Missing Helper class web page.
 *
 * @param array $params Parameters (file, helper)
 * @access public
 */
	function missingHelperClass($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Missing Helper class '%s' in '%s'", true), Inflector::camelize($helper), $file));
		exit();
	}
/**
 * Renders the Missing Component file web page.
 *
 * @param array $params Parameters (file, component)
 * @access public
 */
	function missingComponentFile($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Missing Component file '%s' for '%s'", true), $file, Inflector::camelize($component)));
		exit();
	}
/**
 * Renders the Missing Component class web page.
 *
 * @param array $params Parameters (file, component)
 * @access public
 */
	function missingComponentClass($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Missing Component class '%s' in '%s'", true), Inflector::camelize($component), $file));
		exit();
	}
/**
 * Renders the Missing Model class web page.
 *
 * @param array $params Parameters (className)
 * @access public
 */
	function missingModel($params) {
		extract($params, EXTR_OVERWRITE);
		$this->stderr(sprintf(__("Missing model '%s'", true), $className));
		exit();
	}
/**
 * Outputs to the stdout filehandle.
 *
 * @param string $string String to output.
 * @param boolean $newline If true, the outputs gets an added newline.
 * @access public
 */
	function stdout($string, $newline = true) {
		if ($newline) {
			fwrite($this->stdout, $string . "\n");
		} else {
			fwrite($this->stdout, $string);
		}
	}
/**
 * Outputs to the stderr filehandle.
 *
 * @param string $string Error text to output.
 * @access public
 */
	function stderr($string) {
		fwrite($this->stderr, "Error: ". $string . "\n");
	}
}
?>