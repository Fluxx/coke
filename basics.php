<?php
/* SVN FILE: $Id: basics.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Basic Cake functionality.
 *
 * Core functions for including other source files, loading models and so forth.
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
 * @subpackage		cake.cake
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Basic defines for timing functions.
 */
	define('SECOND', 1);
	define('MINUTE', 60 * SECOND);
	define('HOUR', 60 * MINUTE);
	define('DAY', 24 * HOUR);
	define('WEEK', 7 * DAY);
	define('MONTH', 30 * DAY);
	define('YEAR', 365 * DAY);
/**
 * Patch for PHP < 5.0
 */
if (!function_exists('clone')) {
	if (version_compare(phpversion(), '5.0') < 0) {
		eval ('
		function clone($object)
		{
			return $object;
		}');
	}
}
/**
 * Get CakePHP basic paths as an indexed array.
 * Resulting array will contain array of paths
 * indexed by: Models, Behaviors, Controllers,
 * Components, and Helpers.
 *
 * @return array Array of paths indexed by type
 */
	function paths() {
		$directories = Configure::getInstance();
		$paths = array();

		foreach ($directories->modelPaths as $path) {
			$paths['Models'][] = $path;
		}
		foreach ($directories->behaviorPaths as $path) {
			$paths['Behaviors'][] = $path;
		}
		foreach ($directories->controllerPaths as $path) {
			$paths['Controllers'][] = $path;
		}
		foreach ($directories->componentPaths as $path) {
			$paths['Components'][] = $path;
		}
		foreach ($directories->helperPaths as $path) {
			$paths['Helpers'][] = $path;
		}

		if (!class_exists('Folder')) {
			App::import('Core', 'Folder');
		}

		$folder =& new Folder(APP.'plugins'.DS);
		$plugins = $folder->ls();
		$classPaths = array('models', 'models'.DS.'behaviors',  'controllers', 'controllers'.DS.'components', 'views'.DS.'helpers');

		foreach ($plugins[0] as $plugin) {
			foreach ($classPaths as $path) {
				if (strpos($path, DS) !== false) {
					$key = explode(DS, $path);
					$key = $key[1];
				} else {
					$key = $path;
				}
				$folder->path = APP.'plugins'.DS.$plugin.DS.$path;
				$paths[Inflector::camelize($plugin)][Inflector::camelize($key)][] = $folder->path;
			}
		}
		return $paths;
	}
/**
 * Loads configuration files. Receives a set of configuration files
 * to load.
 * Example:
 * <code>
 * config('config1', 'config2');
 * </code>
 *
 * @return boolean Success
 */
	function config() {
		$args = func_get_args();
		foreach ($args as $arg) {
			if (('database' == $arg) && file_exists(CONFIGS . $arg . '.php')) {
				include_once(CONFIGS . $arg . '.php');
			} elseif (file_exists(CONFIGS . $arg . '.php')) {
				include_once(CONFIGS . $arg . '.php');

				if (count($args) == 1) {
					return true;
				}
			} else {
				if (count($args) == 1) {
					return false;
				}
			}
		}
		return true;
	}
/**
 * Loads component/components from LIBS. Takes optional number of parameters.
 *
 * Example:
 * <code>
 * uses('flay', 'time');
 * </code>
 *
 * @param string $name Filename without the .php part
 */
	function uses() {
		$args = func_get_args();
		foreach ($args as $file) {
			require_once(LIBS . strtolower($file) . '.php');
		}
	}
/**
 * Require given files in the VENDORS directory. Takes optional number of parameters.
 *
 * @param string $name Filename without the .php part.
 */
	function vendor() {
		$args = func_get_args();
		$c = func_num_args();

		for ($i = 0; $i < $c; $i++) {
			$arg = $args[$i];

			if (strpos($arg, '.') !== false) {
				$file = explode('.', $arg);
				$plugin = Inflector::underscore($file[0]);
				unset($file[0]);
				$file = implode('.', $file);
				if (file_exists(APP . 'plugins' . DS . $plugin . DS . 'vendors' . DS . $file . '.php')) {
					require_once(APP . 'plugins' . DS . $plugin . DS . 'vendors' . DS . $file . '.php');
					continue;
				}
			}

			if (file_exists(APP . 'vendors' . DS . $arg . '.php')) {
				require_once(APP . 'vendors' . DS . $arg . '.php');
			} elseif (file_exists(VENDORS . $arg . '.php')) {
				require_once(VENDORS . $arg . '.php');
			} else {
				return false;
			}
		}
		return true;
	}
/**
 * Prints out debug information about given variable.
 *
 * Only runs if debug level is non-zero.
 *
 * @param boolean $var Variable to show debug information for.
 * @param boolean $showHtml If set to true, the method prints the debug data in a screen-friendly way.
 * @param boolean $showFrom If set to true, the method prints from where the function was called.
 */
	function debug($var = false, $showHtml = false, $showFrom = true) {
		if (Configure::read() > 0) {
			if ($showFrom) {
				$calledFrom = debug_backtrace();
				print "<strong>".substr(r(ROOT, "", $calledFrom[0]['file']), 1)."</strong> (line <strong>".$calledFrom[0]['line']."</strong>)";
			}
			print "\n<pre class=\"cake-debug\">\n";
			$var = print_r($var, true);

			if ($showHtml) {
				$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
			}
			print "{$var}\n</pre>\n";
		}
	}
	if (!function_exists('getMicrotime')) {
/**
 * Returns microtime for execution time checking
 *
 * @return float Microtime
 */
		function getMicrotime() {
			list($usec, $sec) = explode(" ", microtime());
			return ((float)$usec + (float)$sec);
		}
	}
	if (!function_exists('sortByKey')) {
/**
 * Sorts given $array by key $sortby.
 *
 * @param array $array Array to sort
 * @param string $sortby Sort by this key
 * @param string $order  Sort order asc/desc (ascending or descending).
 * @param integer $type Type of sorting to perform
 * @return mixed Sorted array
 */
		function sortByKey(&$array, $sortby, $order = 'asc', $type = SORT_NUMERIC) {
			if (!is_array($array)) {
				return null;
			}

			foreach ($array as $key => $val) {
				$sa[$key] = $val[$sortby];
			}

			if ($order == 'asc') {
				asort($sa, $type);
			} else {
				arsort($sa, $type);
			}

			foreach ($sa as $key => $val) {
				$out[] = $array[$key];
			}
			return $out;
		}
	}
	if (!function_exists('array_combine')) {
/**
 * Combines given identical arrays by using the first array's values as keys,
 * and the second one's values as values. (Implemented for back-compatibility with PHP4)
 *
 * @param array $a1 Array to use for keys
 * @param array $a2 Array to use for values
 * @return mixed Outputs either combined array or false.
 */
		function array_combine($a1, $a2) {
			$a1 = array_values($a1);
			$a2 = array_values($a2);
			$c1 = count($a1);
			$c2 = count($a2);

			if ($c1 != $c2) {
				return false;
			}
			if ($c1 <= 0) {
				return false;
			}

			$output=array();
			for ($i = 0; $i < $c1; $i++) {
				$output[$a1[$i]] = $a2[$i];
			}
			return $output;
		}
	}
/**
 * Convenience method for htmlspecialchars.
 *
 * @param string $text Text to wrap through htmlspecialchars
 * @return string Wrapped text
 */
	function h($text) {
		if (is_array($text)) {
			return array_map('h', $text);
		}
		return htmlspecialchars($text);
	}
/**
 * Returns an array of all the given parameters.
 *
 * Example:
 * <code>
 * a('a', 'b')
 * </code>
 *
 * Would return:
 * <code>
 * array('a', 'b')
 * </code>
 *
 * @return array Array of given parameters
 */
	function a() {
		$args = func_get_args();
		return $args;
	}
/**
 * Constructs associative array from pairs of arguments.
 *
 * Example:
 * <code>
 * aa('a','b')
 * </code>
 *
 * Would return:
 * <code>
 * array('a'=>'b')
 * </code>
 *
 * @return array Associative array
 */
	function aa() {
		$args = func_get_args();
		for ($l = 0, $c = count($args); $l < $c; $l++) {
			if ($l + 1 < count($args)) {
				$a[$args[$l]] = $args[$l + 1];
			} else {
				$a[$args[$l]] = null;
			}
			$l++;
		}
		return $a;
	}
/**
 * Convenience method for echo().
 *
 * @param string $text String to echo
 */
	function e($text) {
		echo $text;
	}
/**
 * Convenience method for strtolower().
 *
 * @param string $str String to lowercase
 * @return string Lowercased string
 */
	function low($str) {
		return strtolower($str);
	}
/**
 * Convenience method for strtoupper().
 *
 * @param string $str String to uppercase
 * @return string Uppercased string
 */
	function up($str) {
		return strtoupper($str);
	}
/**
 * Convenience method for str_replace().
 *
 * @param string $search String to be replaced
 * @param string $replace String to insert
 * @param string $subject String to search
 * @return string Replaced string
 */
	function r($search, $replace, $subject) {
		return str_replace($search, $replace, $subject);
	}
/**
 * Print_r convenience function, which prints out <PRE> tags around
 * the output of given array. Similar to debug().
 *
 * @see	debug()
 * @param array $var Variable to print out
 * @param boolean $showFrom If set to true, the method prints from where the function was called
 */
	function pr($var) {
		if (Configure::read() > 0) {
			echo "<pre>";
			print_r($var);
			echo "</pre>";
		}
	}
/**
 * Display parameter
 *
 * @param mixed $p Parameter as string or array
 * @return string
 */
	function params($p) {
		if (!is_array($p) || count($p) == 0) {
			return null;
		} else {
			if (is_array($p[0]) && count($p) == 1) {
				return $p[0];
			} else {
				return $p;
			}
		}
	}
/**
 * Merge a group of arrays
 *
 * @param array First array
 * @param array Second array
 * @param array Third array
 * @param array Etc...
 * @return array All array parameters merged into one
 */
	function am() {
		$r = array();
		foreach (func_get_args()as $a) {
			if (!is_array($a)) {
				$a = array($a);
			}
			$r = array_merge($r, $a);
		}
		return $r;
	}
/**
 * see Dispatcher::uri();
 *
 * @deprecated
 */
	function setUri() {
		return null;
	}
/**
 * see Dispatcher::getUrl();
 *
 * @deprecated
 */
	function setUrl() {
		return null;
	}
/**
 * Gets an environment variable from available sources, and provides emulation
 * for unsupported or inconsisten environment variables (i.e. DOCUMENT_ROOT on
 * IIS, or SCRIPT_NAME in CGI mode).  Also exposes some additional custom
 * environment information.
 *
 * @param  string $key Environment variable name.
 * @return string Environment variable setting.
 */
	function env($key) {
		if ($key == 'HTTPS') {
			if (isset($_SERVER) && !empty($_SERVER)) {
				return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
			} else {
				return (strpos(env('SCRIPT_URI'), 'https://') === 0);
			}
		}

		if ($key == 'SCRIPT_NAME') {
			if (env('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
				$key = 'SCRIPT_URL';
			}
		}

		$val = null;
		if (isset($_SERVER[$key])) {
			$val = $_SERVER[$key];
		} elseif (isset($_ENV[$key])) {
			$val = $_ENV[$key];
		} elseif (getenv($key) !== false) {
			$val = getenv($key);
		}

		if ($key == 'REMOTE_ADDR' && $val == env('SERVER_ADDR')) {
			$addr = env('HTTP_PC_REMOTE_ADDR');
			if ($addr != null) {
				$val = $addr;
			}
		}

		if ($val !== null) {
			return $val;
		}

		switch ($key) {
			case 'SCRIPT_FILENAME':
				if (defined('SERVER_IIS') && SERVER_IIS === true){
					return str_replace('\\\\', '\\', env('PATH_TRANSLATED') );
				}
			break;
			case 'DOCUMENT_ROOT':
				$offset = 0;
				if (!strpos(env('SCRIPT_NAME'), '.php')) {
					$offset = 4;
				}
				return substr(env('SCRIPT_FILENAME'), 0, strlen(env('SCRIPT_FILENAME')) - (strlen(env('SCRIPT_NAME')) + $offset));
			break;
			case 'PHP_SELF':
				return r(env('DOCUMENT_ROOT'), '', env('SCRIPT_FILENAME'));
			break;
			case 'CGI_MODE':
				return (substr(php_sapi_name(), 0, 3) == 'cgi');
			break;
			case 'HTTP_BASE':
				return preg_replace ('/^([^.])*/i', null, env('HTTP_HOST'));
			break;
		}
		return null;
	}
	if (!function_exists('file_put_contents')) {
/**
 * Writes data into file.
 *
 * If file exists, it will be overwritten. If data is an array, it will be join()ed with an empty string.
 *
 * @param string $fileName File name.
 * @param mixed  $data String or array.
 * @return boolean Success
 */
		function file_put_contents($fileName, $data) {
			if (is_array($data)) {
				$data = join('', $data);
			}
			$res = @fopen($fileName, 'w+b');
			if ($res) {
				$write = @fwrite($res, $data);
				if ($write === false) {
					return false;
				} else {
					@fclose($res);
					return $write;
				}
			}
			return false;
		}
	}
/**
 * Reads/writes temporary data to cache files or session.
 *
 * @param  string $path	File path within /tmp to save the file.
 * @param  mixed  $data	The data to save to the temporary file.
 * @param  mixed  $expires A valid strtotime string when the data expires.
 * @param  string $target  The target of the cached data; either 'cache' or 'public'.
 * @return mixed  The contents of the temporary file.
 */
	function cache($path, $data = null, $expires = '+1 day', $target = 'cache') {
		if (Configure::read('Cache.disable')) {
			return null;
		}
		$now = time();

		if (!is_numeric($expires)) {
			$expires = strtotime($expires, $now);
		}

		switch(low($target)) {
			case 'cache':
				$filename = CACHE . $path;
			break;
			case 'public':
				$filename = WWW_ROOT . $path;
			break;
			case 'tmp':
				$filename = TMP . $path;
			break;
		}
		$timediff = $expires - $now;
		$filetime = false;

		if (file_exists($filename)) {
			$filetime = @filemtime($filename);
		}

		if ($data === null) {
			if (file_exists($filename) && $filetime !== false) {
				if ($filetime + $timediff < $now) {
					@unlink($filename);
				} else {
					$data = @file_get_contents($filename);
				}
			}
		} elseif (is_writable(dirname($filename))) {
			@file_put_contents($filename, $data);
		}
		return $data;
	}
/**
 * Used to delete files in the cache directories, or clear contents of cache directories
 *
 * @param mixed $params As String name to be searched for deletion, if name is a directory all files in directory will be deleted.
 *              If array, names to be searched for deletion.
 *              If clearCache() without params, all files in app/tmp/cache/views will be deleted
 *
 * @param string $type Directory in tmp/cache defaults to view directory
 * @param string $ext The file extension you are deleting
 * @return true if files found and deleted false otherwise
 */
	function clearCache($params = null, $type = 'views', $ext = '.php') {
		if (is_string($params) || $params === null) {
			$params = preg_replace('/\/\//', '/', $params);
			$cache = CACHE . $type . DS . $params;

			if (is_file($cache . $ext)) {
				@unlink($cache . $ext);
				return true;
			} elseif (is_dir($cache)) {
				$files = glob("$cache*");

				if ($files === false) {
					return false;
				}

				foreach ($files as $file) {
					if (is_file($file)) {
						@unlink($file);
					}
				}
				return true;
			} else {
				$cache = CACHE . $type . DS . '*' . $params . '*' . $ext;
				$files = glob($cache);

				if ($files === false) {
					return false;
				}
				foreach ($files as $file) {
					if (is_file($file)) {
						@unlink($file);
					}
				}
				return true;
			}
		} elseif (is_array($params)) {
			foreach ($params as $key => $file) {
				$file = preg_replace('/\/\//', '/', $file);
				$cache = CACHE . $type . DS . '*' . $file . '*' . $ext;
				$files[] = glob($cache);
			}

			if (!empty($files)) {
				foreach ($files as $key => $delete) {
					if (is_array($delete)) {
						foreach ($delete as $file) {
							if (is_file($file)) {
								@unlink($file);
							}
						}
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
/**
 * Recursively strips slashes from all values in an array
 *
 * @param array $value Array of values to strip slashes
 * @return mixed What is returned from calling stripslashes
 */
	function stripslashes_deep($value) {
		if (is_array($value)) {
			$return = array_map('stripslashes_deep', $value);
			return $return;
		} else {
			$return = stripslashes($value);
			return $return ;
		}
	}
/**
 * Returns a translated string if one is found, or the submitted message if not found.
 *
 * @param string $singular Text to translate
 * @param boolean $return Set to true to return translated string, or false to echo
 * @return mixed translated string if $return is false string will be echoed
 */
	function __($singular, $return = false) {
		if (!$singular) {
			return;
		}
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}

		if ($return === false) {
			echo I18n::translate($singular);
		} else {
			return I18n::translate($singular);
		}
	}
/**
 * Returns correct plural form of message identified by $singular and $plural for count $count.
 * Some languages have more than one form for plural messages dependent on the count.
 *
 * @param string $singular Singular text to translate
 * @param string $plural Plural text
 * @param integer $count Count
 * @param boolean $return true to return, false to echo
 * @return mixed plural form of translated string if $return is false string will be echoed
 */
	function __n($singular, $plural, $count, $return = false) {
		if (!$singular) {
			return;
		}
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}

		if ($return === false) {
			echo I18n::translate($singular, $plural, null, 5, $count);
		} else {
			return I18n::translate($singular, $plural, null, 5, $count);
		}
	}
/**
 * Allows you to override the current domain for a single message lookup.
 *
 * @param string $domain Domain
 * @param string $msg String to translate
 * @param string $return true to return, false to echo
 * @return translated string if $return is false string will be echoed
 */
	function __d($domain, $msg, $return = false) {
		if (!$msg) {
			return;
		}
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}

		if ($return === false) {
			echo I18n::translate($msg, null, $domain);
		} else {
			return I18n::translate($msg, null, $domain);
		}
    }
/**
 * Allows you to override the current domain for a single plural message lookup
 * Returns correct plural form of message identified by $singular and $plural for count $count
 * from domain $domain
 *
 * @param string $domain Domain
 * @param string $singular Singular string to translate
 * @param string $plural Plural
 * @param integer $count Count
 * @param boolean $return true to return, false to echo
 * @return plural form of translated string if $return is false string will be echoed
 */
	function __dn($domain, $singular, $plural, $count, $return = false) {
		if (!$singular) {
			return;
		}
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}

		if ($return === false) {
			echo I18n::translate($singular, $plural, $domain, 5, $count);
		} else {
			return I18n::translate($singular, $plural, $domain, 5, $count);
		}
	}
/**
 * Allows you to override the current domain for a single message lookup.
 * It also allows you to specify a category.
 *
 * The category argument allows a specific category of the locale settings to be used for fetching a message.
 * Valid categories are: LC_CTYPE, LC_NUMERIC, LC_TIME, LC_COLLATE, LC_MONETARY, LC_MESSAGES and LC_ALL.
 *
 * Note that the category must be specified with a numeric value, instead of the constant name.  The values are:
 * LC_CTYPE     0
 * LC_NUMERIC   1
 * LC_TIME      2
 * LC_COLLATE   3
 * LC_MONETARY  4
 * LC_MESSAGES  5
 * LC_ALL       6
 *
 * @param string $domain Domain
 * @param string $msg Message to translate
 * @param integer $category Category
 * @param boolean $return true to return, false to echo
 * @return translated string if $return is false string will be echoed
 */
	function __dc($domain, $msg, $category, $return = false) {
		if (!$msg) {
			return;
		}
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}

		if ($return === false) {
			echo I18n::translate($msg, null, $domain, $category);
		} else {
			return I18n::translate($msg, null, $domain, $category);
		}
	}
/**
 * Allows you to override the current domain for a single plural message lookup.
 * It also allows you to specify a category.
 * Returns correct plural form of message identified by $singular and $plural for count $count
 * from domain $domain
 *
 * The category argument allows a specific category of the locale settings to be used for fetching a message.
 * Valid categories are: LC_CTYPE, LC_NUMERIC, LC_TIME, LC_COLLATE, LC_MONETARY, LC_MESSAGES and LC_ALL.
 *
 * Note that the category must be specified with a numeric value, instead of the constant name.  The values are:
 * LC_CTYPE     0
 * LC_NUMERIC   1
 * LC_TIME      2
 * LC_COLLATE   3
 * LC_MONETARY  4
 * LC_MESSAGES  5
 * LC_ALL       6
 *
 * @param string $domain Domain
 * @param string $singular Singular string to translate
 * @param string $plural Plural
 * @param integer $count Count
 * @param integer $category Category
 * @param boolean $return true to return, false to echo
 * @return plural form of translated string if $return is false string will be echoed
 */
	function __dcn($domain, $singular, $plural, $count, $category, $return = false) {
		if (!$singular) {
			return;
		}
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}

		if ($return === false) {
			echo I18n::translate($singular, $plural, $domain, $category, $count);
		} else {
			return I18n::translate($singular, $plural, $domain, $category, $count);
		}
	}
/**
 * The category argument allows a specific category of the locale settings to be used for fetching a message.
 * Valid categories are: LC_CTYPE, LC_NUMERIC, LC_TIME, LC_COLLATE, LC_MONETARY, LC_MESSAGES and LC_ALL.
 *
 * Note that the category must be specified with a numeric value, instead of the constant name.  The values are:
 * LC_CTYPE     0
 * LC_NUMERIC   1
 * LC_TIME      2
 * LC_COLLATE   3
 * LC_MONETARY  4
 * LC_MESSAGES  5
 * LC_ALL       6
 *
 * @param string $msg String to translate
 * @param integer $category Category
 * @param string $return true to return, false to echo
 * @return translated string if $return is false string will be echoed
 */
	function __c($msg, $category, $return = false) {
		if (!$msg) {
			return;
		}
		if (!class_exists('I18n')) {
			App::import('Core', 'i18n');
		}

		if ($return === false) {
			echo I18n::translate($msg, null, null, $category);
		} else {
			return I18n::translate($msg, null, null, $category);
		}
    }
/**
 * Computes the difference of arrays using keys for comparison
 *
 * @param array First array
 * @param array Second array
 * @return array Array with different keys
 */
	if (!function_exists('array_diff_key')) {
		function array_diff_key() {
			$valuesDiff = array();

			if (func_num_args() < 2) {
				return false;
			}

			foreach (func_get_args() as $param) {
				if (!is_array($param)) {
					return false;
				}
			}

			$args = func_get_args();
			foreach ($args[0] as $valueKey => $valueData) {
				for ($i = 1; $i < func_num_args(); $i++) {
					if (isset($args[$i][$valueKey])) {
						continue 2;
					}
				}
				$valuesDiff[$valueKey] = $valueData;
			}
			return $valuesDiff;
		}
	}
/**
 * Computes the intersection of arrays using keys for comparison
 *
 * @param array First array
 * @param array Second array
 * @return array Array with interesected keys
 */
	if (!function_exists('array_intersect_key')) {
		function array_intersect_key($arr1, $arr2) {
			$res = array();
			foreach ($arr1 as $key=>$value) {
				if (array_key_exists($key, $arr2)) {
					$res[$key] = $arr1[$key];
				}
			}
			return $res;
		}
	}
/**
 * Shortcut to Log::write.
 *
 * @param string $message Message to write to log
 */
	function LogError($message) {
		if (!class_exists('CakeLog')) {
			App::import('Core', 'CakeLog');
		}
		$bad = array("\n", "\r", "\t");
		$good = ' ';
		CakeLog::write('error', str_replace($bad, $good, $message));
	}
/**
 * Searches include path for files
 *
 * @param string $file File to look for
 * @return Full path to file if exists, otherwise false
 */
	function fileExistsInPath($file) {
		$paths = explode(PATH_SEPARATOR, ini_get('include_path'));
		foreach ($paths as $path) {
			$fullPath = $path . DIRECTORY_SEPARATOR . $file;

			if (file_exists($fullPath)) {
				return $fullPath;
			} elseif (file_exists($file)) {
				return $file;
			}
		}
		return false;
	}
/**
 * Convert forward slashes to underscores and removes first and last underscores in a string
 *
 * @param string String to convert
 * @return string with underscore remove from start and end of string
 */
	function convertSlash($string) {
		$string = trim($string,"/");
		$string = preg_replace('/\/\//', '/', $string);
		$string = str_replace('/', '_', $string);
		return $string;
	}
/**
 * Implements http_build_query for PHP4.
 *
 * @param string $data Data to set in query string
 * @param string $prefix If numeric indices, prepend this to index for elements in base array.
 * @param string $argSep String used to separate arguments
 * @param string $baseKey Base key
 * @return string URL encoded query string
 * @see http://php.net/http_build_query
 */
	if (!function_exists('http_build_query')) {
		function http_build_query($data, $prefix = null, $argSep = null, $baseKey = null) {
			if (empty($argSep)) {
				$argSep = ini_get('arg_separator.output');
			}
			if (is_object($data)) {
				$data = get_object_vars($data);
			}
			$out = array();

			foreach ((array)$data as $key => $v) {
				if (is_numeric($key) && !empty($prefix)) {
					$key = $prefix . $key;
				}
				$key = urlencode($key);

				if (!empty($baseKey)) {
					$key = $baseKey . '[' . $key . ']';
				}

				if (is_array($v) || is_object($v)) {
					$out[] = http_build_query($v, $prefix, $argSep, $key);
				} else {
					$out[] = $key . '=' . urlencode($v);
				}
			}
			return implode($argSep, $out);
		}
	}
/**
 * Wraps ternary operations. If $condition is a non-empty value, $val1 is returned, otherwise $val2.
 * Don't use for isset() conditions, or wrap your variable with @ operator:
 * Example:
 * <code>
 * ife(isset($variable), @$variable, 'default');
 * </code>
 *
 * @param mixed $condition Conditional expression
 * @param mixed $val1 Value to return in case condition matches
 * @param mixed $val2 Value to return if condition doesn't match
 * @return mixed $val1 or $val2, depending on whether $condition evaluates to a non-empty expression.
 */
	function ife($condition, $val1 = null, $val2 = null) {
		if (!empty($condition)) {
			return $val1;
		}
		return $val2;
	}
/**
 * @deprecated
 * @see App::import('View', 'ViewName');
 */
	function loadView($name) {
		trigger_error('loadView is deprecated see App::import(\'View\', \'ViewName\');', E_USER_WARNING);
		return App::import('View', $name);
	}
/**
 * @deprecated
 * @see App::import('Model', 'ModelName');
 */
	function loadModel($name = null) {
		trigger_error('loadModel is deprecated see App::import(\'Model\', \'ModelName\');', E_USER_WARNING);
		return App::import('Model', $name);
	}
/**
 * @deprecated
 * @see App::import('Controller', 'ControllerName');
 */
	function loadController($name) {
		trigger_error('loadController is deprecated see App::import(\'Controller\', \'ControllerName\');', E_USER_WARNING);
		return App::import('Controller', $name);
	}
/**
 * @deprecated
 * @see App::import('Helper', 'HelperName');
 */
	function loadHelper($name) {
		trigger_error('loadHelper is deprecated see App::import(\'Helper\', \'PluginName.HelperName\');', E_USER_WARNING);
		return App::import('Helper', $name);
	}
/**
 * @deprecated
 * @see App::import('Helper', 'PluginName.HelperName');
 */
	function loadPluginHelper($plugin, $helper) {
		trigger_error('loadPluginHelper is deprecated see App::import(\'Helper\', \'PluginName.HelperName\');', E_USER_WARNING);
		return App::import('Helper', $plugin . '.' . $helper);
	}
/**
 * @deprecated
 * @see App::import('Component', 'ComponentName');
 */
	function loadComponent($name) {
		trigger_error('loadComponent is deprecated see App::import(\'Component\', \'ComponentName\');', E_USER_WARNING);
		return App::import('Component', $name);
	}
/**
 * @deprecated
 * @see App::import('Component', 'PluginName.ComponentName');
 */
	function loadPluginComponent($plugin, $component) {
		trigger_error('loadPluginComponent is deprecated see App::import(\'Component\', \'PluginName.ComponentName\');', E_USER_WARNING);
		return App::import('Component', $plugin . '.' . $component);
	}
/**
 * @deprecated
 * @see App::import('Behavior', 'BehaviorrName');
 */
	function loadBehavior($name) {
		trigger_error('loadBehavior is deprecated see App::import(\'Behavior\', $name);', E_USER_WARNING);
		return App::import('Behavior', $name);
	}
/**
 * @deprecated
 * @see $model = Configure::listObjects('model'); and App::import('Model', $models);
 *      or App::import('Model', array(List of Models));
 */
	function loadModels() {
		$loadModels = array();
		if (func_num_args() > 0) {
			$args = func_get_args();
			foreach($args as $arg) {
				if (is_array($arg)) {
					$loadModels = am($loadModels, $arg);
				} else {
					$loadModels[] = $arg;
				}
			}
		}

		if (empty($loadModels)) {
			$loadModels = Configure::listObjects('model');
		}
		App::import('Model', $loadModels);
		trigger_error('loadModels is deprecated see $model = Configure::listObjects(\'model\'); and App::import(\'Model\', $models);', E_USER_WARNING);
		return $loadModels;
	}
/**
 * @deprecated
 * @see App::import('Model', 'PluginName.PluginModel');
 */
	function loadPluginModels($plugin) {
		if (!class_exists('AppModel')) {
			loadModel();
		}
		$plugin = Inflector::underscore($plugin);
		$pluginAppModel = Inflector::camelize($plugin . '_app_model');
		$pluginAppModelFile = APP . 'plugins' . DS . $plugin . DS . $plugin . '_app_model.php';

		if (!class_exists($pluginAppModel)) {
			if (file_exists($pluginAppModelFile)) {
				require($pluginAppModelFile);
				Overloadable::overload($pluginAppModel);
			}
		}

		$pluginModelDir = APP . 'plugins' . DS . $plugin . DS . 'models' . DS;
		if (is_dir($pluginModelDir)) {
			foreach (listClasses($pluginModelDir)as $modelFileName) {
				list($name) = explode('.', $modelFileName);
				$className = Inflector::camelize($name);

				if (!class_exists($className)) {
					require($pluginModelDir . $modelFileName);
					Overloadable::overload($className);
				}
			}
		}
		trigger_error('loadPluginModels is deprecated see App::import(\'Model\', \'PluginName.PluginModel\');', E_USER_WARNING);
	}
/**
 * @deprecated
 * @see $controllers = Configure::listObjects('controller'); and App::import('Controller', $controllers);
 *      or App::import('Controller', array(List of Controllers);
 */
	function loadControllers() {
		$loadControllers = array();
		if (func_num_args() > 0) {
			$args = func_get_args();
			foreach($args as $arg) {
				if (is_array($arg)) {
					$loadControllers = am($loadControllers, $arg);
				} else {
					$loadControllers[] = $arg;
				}
			}
		}

		if (empty($loadControllers)) {
			$loadControllers = Configure::listObjects('controller');
		}
		App::import('Controller', $loadControllers);
		trigger_error('loadControllers is deprecated see $controllers = Configure::listObjects(\'controller\'); and App::import(\'Controller\', $controllers);', E_USER_WARNING);
		return $loadControllers;
	}
/**
 * @deprecated
 * @see Configure::listObjects('file', $path);
 */
	function listClasses($path ) {
		trigger_error('listClasses is deprecated see Configure::listObjects(\'file\', $path);', E_USER_WARNING);
		return Configure::listObjects('file', $path);
	}
?>