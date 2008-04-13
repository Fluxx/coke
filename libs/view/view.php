<?php
/* SVN FILE: $Id: view.php 6311 2008-01-02 06:33:52Z phpnut $ */

/**
 * Methods for displaying presentation data in the view.
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
 * @subpackage		cake.cake.libs.view
 * @since			CakePHP(tm) v 0.10.0.1076
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Included libraries.
 */
uses ('view' . DS . 'helper', 'class_registry');

/**
 * View, the V in the MVC triad.
 *
 * Class holding methods for displaying presentation data.
 *
 * @package			cake
 * @subpackage		cake.cake.libs.view
 */
class View extends Object {
/**
 * Path parts for creating links in views.
 *
 * @var string Base URL
 * @access public
 */
	var $base = null;
/**
 * Stores the current URL (for links etc.)
 *
 * @var string Current URL
 */
	var $here = null;
/**
 * Name of the plugin.
 *
 * @link http://manual.cakephp.org/chapter/plugins
 * @var string
 */
	var $plugin = null;
/**
 * Name of the controller.
 *
 * @var string Name of controller
 * @access public
 */
	var $name = null;
/**
 * Action to be performed.
 *
 * @var string Name of action
 * @access public
 */
	var $action = null;
/**
 * Array of parameter data
 *
 * @var array Parameter data
 */
	var $params = array();
/**
 * Current passed params
 *
 * @var mixed
 */
	var $passedArgs = array();
/**
 * Array of data
 *
 * @var array Parameter data
 */
	var $data = array();
/**
 * An array of names of built-in helpers to include.
 *
 * @var mixed A single name as a string or a list of names as an array.
 * @access public
 */
	var $helpers = array('Html');
/**
 * Path to View.
 *
 * @var string Path to View
 */
	var $viewPath = null;
/**
 * Variables for the view
 *
 * @var array
 * @access public
 */
	var $viewVars = array();
/**
 * Name of layout to use with this View.
 *
 * @var string
 * @access public
 */
	var $layout = 'default';
/**
 * Path to Layout.
 *
 * @var string Path to Layout
 */
	var $layoutPath = null;
/**
 * Title HTML element of this View.
 *
 * @var string
 * @access public
 */
	var $pageTitle = false;
/**
 * Turns on or off Cake's conventional mode of rendering views. On by default.
 *
 * @var boolean
 * @access public
 */
	var $autoRender = true;
/**
 * Turns on or off Cake's conventional mode of finding layout files. On by default.
 *
 * @var boolean
 * @access public
 */
	var $autoLayout = true;
/**
 * File extension. Defaults to Cake's template ".ctp".
 *
 * @var string
 */
	var $ext = '.ctp';
/**
 * Sub-directory for this view file.
 *
 * @var string
 */
	var $subDir = null;
/**
 * Theme name.
 *
 * @var string
 */
	var $themeWeb = null;
/**
 * Used to define methods a controller that will be cached.
 *
 * @see Controller::$cacheAction
 * @var mixed
 * @access public
 */
	var $cacheAction = false;
/**
 * holds current errors for the model validation
 *
 * @var array
 */
	var $validationErrors = array();
/**
 * True when the view has been rendered.
 *
 * @var boolean
 */
	var $hasRendered = false;
/**
 * Array of loaded view helpers.
 *
 * @var array
 */
	var $loaded = array();
/**
 * True if in scope of model-specific region
 *
 * @var boolean
 */
	var $modelScope = false;
/**
 * Name of current model this view context is attached to
 *
 * @var string
 */
	var $model = null;
/**
 * Name of association model this view context is attached to
 *
 * @var string
 */
	var $association = null;
/**
 * Name of current model field this view context is attached to
 *
 * @var string
 */
	var $field = null;
/**
 * Suffix of current field this view context is attached to
 *
 * @var string
 */
	var $fieldSuffix = null;
/**
 * The current model ID this view context is attached to
 *
 * @var mixed
 */
	var $modelId = null;
/**
 * List of generated DOM UUIDs
 *
 * @var array
 */
	var $uuids = array();
/**
 * List of variables to collect from the associated controller
 *
 * @var array
 * @access protected
 */
	var $__passedVars = array('viewVars', 'action', 'autoLayout', 'autoRender', 'ext', 'base', 'webroot', 'helpers', 'here', 'layout', 'name', 'pageTitle', 'layoutPath', 'viewPath', 'params', 'data', 'webservices', 'plugin', 'passedArgs', 'cacheAction');
/**
 * Scripts (and/or other <head /> tags) for the layout
 *
 * @var array
 * @access private
 */
	var $__scripts = array();
/**
 * Holds an array of paths.
 *
 * @var array
 */
	var $__paths = array();
/**
 * Constructor
 *
 * @return View
 */
	function __construct(&$controller, $register = true) {
		if (is_object($controller)) {
			$count = count($this->__passedVars);
			for ($j = 0; $j < $count; $j++) {
				$var = $this->__passedVars[$j];
				$this->{$var} = $controller->{$var};
			}
		}

		parent::__construct();
		if ($register) {
			ClassRegistry::addObject('view', $this);
		}
	}

/**
 * Wrapper for View::renderElement();
 *
 * @param string $name Name of template file in the/app/views/elements/ folder
 * @param array $params Array of data to be made available to the for rendered view (i.e. the Element)
 * @return string View::renderElement()
 * @access public
 */
	function element($name, $params = array()) {
		if (isset($params['cache'])) {
			$expires = '+1 day';
			$key = null;
			if (is_array($params['cache'])) {
				$expires = $params['cache']['time'];
				$key = Inflector::slug($params['cache']['key']);
			} elseif ($params['cache'] !== true) {
				$expires = $params['cache'];
				$key = implode('_', array_keys($params));
			}
			if ($expires) {
				$plugin = null;
				if (isset($params['plugin'])) {
					$plugin = $params['plugin'].'_';
				}
				$cacheFile = 'element_' . $key . '_' . $plugin . Inflector::slug($name);
				$cache = cache('views' . DS . $cacheFile, null, $expires);

				if (is_string($cache)) {
					return $cache;
				} else {
					$element = $this->renderElement($name, $params);
               		cache('views' . DS . $cacheFile, $element, $expires);
               		return $element;
				}
			}
		}
		return $this->renderElement($name, $params);
	}
/**
 * Renders view for given action and layout. If $file is given, that is used
 * for a view filename (e.g. customFunkyView.ctp).
 *
 * @param string $action Name of action to render for
 * @param string $layout Layout to use
 * @param string $file Custom filename for view
 */
	function render($action = null, $layout = null, $file = null) {

		if ($this->hasRendered) {
			return true;
		}

		$out = null;

		if ($file != null) {
			$action = $file;
		}

		if ($action !== false) {
			$viewFileName = $this->_getViewFileName($action);
			if (substr($viewFileName, -3) === 'ctp' || substr($viewFileName, -5) === 'thtml') {
				$out = View::_render($viewFileName, $this->viewVars);
			} else {
				$out = $this->_render($viewFileName, $this->viewVars);
			}
		}

		if ($layout === null) {
			$layout = $this->layout;
		}

		if ($out !== false) {
			if ($layout && $this->autoLayout) {
				$out = $this->renderLayout($out, $layout);
				if (isset($this->loaded['cache']) && (($this->cacheAction != false)) && (Configure::read('Cache.check') === true)) {
					$replace = array('<cake:nocache>', '</cake:nocache>');
					$out = str_replace($replace, '', $out);
				}
			}

			print $out;
			$this->hasRendered = true;
		} else {
			$out = $this->_render($viewFileName, $this->viewVars);
			trigger_error(sprintf(__("Error in view %s, got: <blockquote>%s</blockquote>", true), $viewFileName, $out), E_USER_ERROR);
		}
		return true;
	}
/**
 * Renders a piece of PHP with provided parameters and returns HTML, XML, or any other string.
 *
 * This realizes the concept of Elements, (or "partial layouts")
 * and the $params array is used to send data to be used in the
 * Element.
 *
 * @link
 * @param string $name Name of template file in the/app/views/elements/ folder
 * @param array $params Array of data to be made available to the for rendered view (i.e. the Element)
 * @return string Rendered output
 */
	function renderElement($name, $params = array(), $loadHelpers = false) {
		$file = $plugin = null;

		if (isset($params['plugin'])) {
			$plugin = $params['plugin'];
		}

		if (isset($this->plugin) && !$plugin) {
			$plugin = $this->plugin;
		}

		$paths = $this->_paths($plugin);

		foreach ($paths as $path) {
			if (file_exists($path . 'elements' . DS . $name . $this->ext)) {
				$file = $path . 'elements' . DS . $name . $this->ext;
				break;
			} elseif (file_exists($path . 'elements' . DS . $name . '.thtml')) {
				$file = $path . 'elements' . DS . $name . '.thtml';
				break;
			}
		}

		if (is_file($file)) {
			$params = array_merge_recursive($params, $this->loaded);
			return $this->_render($file, array_merge($this->viewVars, $params), $loadHelpers);
		}

		$file = $paths[0] . 'views' . DS . 'elements' . DS . $name . $this->ext;

		if (Configure::read() > 0) {
			return "Not Found: " . $file;
		}
	}
/**
 * Renders a layout. Returns output from _render(). Returns false on error.
 *
 * @param string $content_for_layout Content to render in a view, wrapped by the surrounding layout.
 * @return mixed Rendered output, or false on error
 */
	function renderLayout($content_for_layout, $layout = null) {
		$layout_fn = $this->_getLayoutFileName($layout);

		$debug = '';

		if (isset($this->viewVars['cakeDebug']) && Configure::read() > 2) {
			$debug = View::renderElement('dump', array('controller' => $this->viewVars['cakeDebug']), false);
			unset($this->viewVars['cakeDebug']);
		}

		if ($this->pageTitle !== false) {
			$pageTitle = $this->pageTitle;
		} else {
			$pageTitle = Inflector::humanize($this->viewPath);
		}

		$data_for_layout = array_merge($this->viewVars,
			array(
				'title_for_layout'   => $pageTitle,
				'content_for_layout' => $content_for_layout,
				'scripts_for_layout' => join("\n\t", $this->__scripts),
				'cakeDebug'          => $debug
			)
		);

		if (empty($this->loaded) && !empty($this->helpers)) {
			$loadHelpers = true;
		} else {
			$loadHelpers = false;
			$data_for_layout = array_merge($data_for_layout, $this->loaded);
		}

		if (substr($layout_fn, -3) === 'ctp' || substr($layout_fn, -5) === 'thtml') {
			$out = View::_render($layout_fn, $data_for_layout, $loadHelpers, true);
		} else {
			$out = $this->_render($layout_fn, $data_for_layout, $loadHelpers);
		}

		if ($out === false) {
			$out = $this->_render($layout_fn, $data_for_layout);
			trigger_error(sprintf(__("Error in layout %s, got: <blockquote>%s</blockquote>", true), $layout_fn, $out), E_USER_ERROR);
			return false;
		}

		return $out;
	}
/**
 * Render cached view
 *
 * @param string $filename the cache file to include
 * @param string $timeStart the page render start time
 */
	function renderCache($filename, $timeStart) {
		ob_start();
		include ($filename);

		if (Configure::read() > 0 && $this->layout != 'xml') {
			echo "<!-- Cached Render Time: " . round(getMicrotime() - $timeStart, 4) . "s -->";
		}

		$out = ob_get_clean();

		if (preg_match('/^<!--cachetime:(\\d+)-->/', $out, $match)) {
			if (time() >= $match['1']) {
				@unlink($filename);
				unset ($out);
				return false;
			} else {
				if ($this->layout === 'xml') {
					header('Content-type: text/xml');
				}
				$out = str_replace('<!--cachetime:'.$match['1'].'-->', '', $out);
				echo $out;
				return true;
			}
		}
	}
/**
 * Returns a list of variables available in the current View context
 *
 * @return array
 * @access public
 */
	function getVars() {
		return array_keys($this->viewVars);
	}
/**
 * Returns the contents of the given View variable(s)
 *
 * @return array
 * @access public
 */
	function getVar($var) {
		if (!isset($this->viewVars[$var])) {
			return null;
		} else {
			return $this->viewVars[$var];
		}
	}
/**
 * Adds a script block or other element to be inserted in $scripts_for_layout in
 * the <head /> of a document layout
 *
 * @param string $name
 * @param string $content
 * @access public
 */
	function addScript($name, $content = null) {
		if (empty($content)) {
			if (!in_array($name, array_values($this->__scripts))) {
				$this->__scripts[] = $name;
			}
		} else {
			$this->__scripts[$name] = $content;
		}
	}
/**
 * Generates a unique, non-random DOM ID for an object, based on the object type and the target URL.
 *
 * @param string $object Type of object, i.e. 'form' or 'link'
 * @param string $url The object's target URL
 * @return string
 * @access public
 */
	function uuid($object, $url) {
		$c = 1;
		$hash = $object . substr(md5($object . Router::url($url)), 0, 10);
		while (in_array($hash, $this->uuids)) {
			$hash = $object . substr(md5($object . Router::url($url) . $c), 0, 10);
			$c++;
		}
		$this->uuids[] = $hash;
		return $hash;
	}
/**
 * Returns the entity reference of the current context as an array of identity parts
 *
 * @return array An array containing the identity elements of an entity
 */
	function entity() {
		return Set::filter(array(
			ife($this->association, $this->association, $this->model),
			$this->modelId, $this->field, $this->fieldSuffix
		));
	}
/**
 * Allows a template or element to set a variable that will be available in
 * a layout or other element.  Analagous to Controller::set.
 *
 * @param mixed $one A string or an array of data.
 * @param mixed $two Value in case $one is a string (which then works as the key).
 * 				Unused if $one is an associative array, otherwise serves as the values to $one's keys.
 * @return unknown
 */
	function set($one, $two = null) {
		$data = null;
		if (is_array($one)) {
			if (is_array($two)) {
				$data = array_combine($one, $two);
			} else {
				$data = $one;
			}
		} else {
			$data = array($one => $two);
		}

		if ($data == null) {
			return false;
		}

		foreach ($data as $name => $value) {
			if ($name == 'title') {
				$this->pageTitle = $value;
			} else {
				$this->viewVars[$name] = $value;
			}
		}
	}

/**
 * Displays an error page to the user. Uses layouts/error.ctp to render the page.
 *
 * @param integer $code HTTP Error code (for instance: 404)
 * @param string $name Name of the error (for instance: Not Found)
 * @param string $message Error message as a web page
 */
	function error($code, $name, $message) {
		header ("HTTP/1.1 {$code} {$name}");
		print ($this->_render(
			$this->_getLayoutFileName('error'),
			array(
				'code' => $code,
				'name' => $name,
				'message' => $message
			)
		));
	}

/**
 * Renders and returns output for given view filename with its
 * array of data.
 *
 * @param string $___viewFn Filename of the view
 * @param array $___dataForView Data to include in rendered view
 * @return string Rendered output
 * @access protected
 */
	function _render($___viewFn, $___dataForView, $loadHelpers = true, $cached = false) {
		$loadedHelpers = array();

		if ($this->helpers != false && $loadHelpers === true) {
			$loadedHelpers = $this->_loadHelpers($loadedHelpers, $this->helpers);

			foreach (array_keys($loadedHelpers) as $helper) {
				$camelBackedHelper = Inflector::variable($helper);
				${$camelBackedHelper} =& $loadedHelpers[$helper];
				$this->loaded[$camelBackedHelper] =& ${$camelBackedHelper};
			}

			foreach ($loadedHelpers as $helper) {
				if (is_object($helper)) {
					if (is_subclass_of($helper, 'Helper') || is_subclass_of($helper, 'helper')) {
						$helper->beforeRender();
					}
				}
			}
		}

		extract($___dataForView, EXTR_SKIP);

		ob_start();

		if (Configure::read() > 0) {
			include ($___viewFn);
		} else {
			@include ($___viewFn);
		}

		if (!empty($loadedHelpers)) {
			foreach ($loadedHelpers as $helper) {
				if (is_object($helper)) {
					if (is_subclass_of($helper, 'Helper') || is_subclass_of($helper, 'helper')) {
						$helper->afterRender();
					}
				}
			}
		}

		$out = ob_get_clean();

		if (isset($this->loaded['cache']) && (($this->cacheAction != false)) && (Configure::read('Cache.check') === true)) {
			if (is_a($this->loaded['cache'], 'CacheHelper')) {
				$cache =& $this->loaded['cache'];

				if ($cached === true) {
					$cache->view = &$this;
				}

				$cache->base = $this->base;
				$cache->here = $this->here;
				$cache->helpers = $this->helpers;
				$cache->action = $this->action;
				$cache->controllerName = $this->name;
				$cache->layout	= $this->layout;
				$cache->cacheAction = $this->cacheAction;
				$cache->cache($___viewFn, $out, $cached);
			}
		}

		return $out;
	}
/**
 * Loads helpers, with their dependencies.
 *
 * @param array $loaded List of helpers that are already loaded.
 * @param array $helpers List of helpers to load.
 * @param string $parent holds name of helper, if loaded helper has helpers
 * @return array
 */
	function &_loadHelpers(&$loaded, $helpers, $parent = null) {
		if (empty($loaded)) {
			$helpers[] = 'Session';
		}

		foreach ($helpers as $helper) {
			$parts = preg_split('/\/|\./', $helper);

			if (count($parts) === 1) {
				$plugin = $this->plugin;
			} else {
				$plugin = Inflector::underscore($parts['0']);
				$helper = $parts[count($parts) - 1];
			}
			$helperCn = $helper . 'Helper';

			if (in_array($helper, array_keys($loaded)) !== true) {
				if (!class_exists($helperCn)) {
				    if (is_null($plugin) || !App::import('Helper', $plugin . '.' . $helper)) {
						if (!App::import('Helper', $helper)) {
							$this->cakeError('missingHelperFile', array(array(
								'helper' => $helper,
								'file' => Inflector::underscore($helper) . '.php',
								'base' => $this->base
							)));
							exit();
						}
				    }
					if (!class_exists($helperCn)) {
						$this->cakeError('missingHelperClass', array(array(
							'helper' => $helper,
							'file' => Inflector::underscore($helper) . '.php',
							'base' => $this->base
						)));
						exit();
					}
				}

				$loaded[$helper] =& new $helperCn();

				$vars = array('base', 'webroot', 'here', 'params', 'action', 'data', 'themeWeb', 'plugin');
				$c = count($vars);
				for ($j = 0; $j < $c; $j++) {
					$loaded[$helper]->{$vars[$j]} = $this->{$vars[$j]};
				}

				if (!empty($this->validationErrors)) {
					$loaded[$helper]->validationErrors = $this->validationErrors;
				}

				if (is_array($loaded[$helper]->helpers) && !empty($loaded[$helper]->helpers)) {
					$loaded =& $this->_loadHelpers($loaded, $loaded[$helper]->helpers, $helper);
				}
			}

			if (isset($loaded[$parent])) {
				$loaded[$parent]->{$helper} =& $loaded[$helper];
			}
		}
		return $loaded;
	}
/**
 * Returns filename of given action's template file (.ctp) as a string.
 * CamelCased action names will be under_scored! This means that you can have
 * LongActionNames that refer to long_action_names.ctp views.
 *
 * @param string $action Controller action to find template filename for
 * @return string Template filename
 * @access protected
 */
	function _getViewFileName($name = null) {
		$subDir = null;

		if (!is_null($this->webservices)) {
			$subDir = strtolower($this->webservices) . DS;
		}
		if (!is_null($this->subDir)) {
			$subDir = $this->subDir . DS;
		}

		if ($name === null) {
			$name = $this->action;
		}

		if (strpos($name, '/') === false && strpos($name, '..') === false) {
			$name = $this->viewPath . DS . $subDir . Inflector::underscore($name);
		} elseif (strpos($name, '/') !== false) {
			if ($name{0} === '/') {
				if (is_file($name)) {
					return $name;
				}
				$name = trim($name, '/');
			} else {
				$name = $this->viewPath . DS . $subDir . $name;
			}
			if (DS !== '/') {
				$name = implode(DS, explode('/', $name));
			}
		} elseif (strpos($name, '..') !== false) {
			$name = explode('/', $name);
			$i = array_search('..', $name);
			unset($name[$i - 1]);
			unset($name[$i]);
			$name = '..' . DS . implode(DS, $name);
		}

		$paths = $this->_paths($this->plugin);
		foreach ($paths as $path) {
			if (file_exists($path . $name . $this->ext)) {
				return $path . $name . $this->ext;
			} elseif (file_exists($path . $name . '.thtml')) {
				return $path . $name . '.thtml';
			}
		}

		return $this->_missingView($paths[0] . $name . $this->ext, 'missingView');
	}

/**
 * Returns layout filename for this template as a string.
 *
 * @return string Filename for layout file (.ctp).
 * @access protected
 */
	function _getLayoutFileName($name = null) {
		if ($name === null) {
			$name = $this->layout;
		}
		$subDir = null;

		if (!is_null($this->layoutPath)) {
			$subDir = $this->layoutPath . DS;
		}

		$paths = $this->_paths($this->plugin);
		$file = 'layouts' . DS . $subDir . $name;

		foreach ($paths as $path) {
			if (file_exists($path . $file . $this->ext)) {
				return $path . $file . $this->ext;
			} elseif (file_exists($path . $file . '.thtml')) {
				return $path . $file . '.thtml';
			}
		}

		return $this->_missingView($paths[0] . $file . $this->ext, 'missingLayout');
	}
/**
 * Return a misssing view error message
 *
 * @param string $viewFileName the filename that should exist
 * @return cakeError
 */
	function _missingView($file, $error = 'missingView') {
		$paths = $this->_paths($this->plugin);
		$name = 'errors' . DS . Inflector::underscore($error);
		foreach ($paths as $path) {
			if (file_exists($path . $name . $this->ext)) {
				$name =  $path . $name . $this->ext;
				break;
			} elseif (file_exists($path . $name . '.thtml')) {
				$name = $path . $name . '.thtml';
				break;
			}
		}

		if ($error === 'missingView') {
			return $this->cakeError('missingView', array(array(
					'className' => $this->name,
					'action' => $this->action,
					'file' => $file,
					'base' => $this->base
				)));
		}
		if ($error === 'missingLayout') {
			return $this->cakeError('missingLayout', array(array(
					'layout' => $this->layout,
					'file' => $file,
					'base' => $this->base
				)));
		}
		return $name;
	}
/**
 * Return all possible paths to find view files in order
 *
 * @param string $plugin
 * @return array paths
 * @access protected
 */
	function _paths($plugin = null, $cached = true) {
		if ($plugin === null && $cached === true && !empty($this->__paths)) {
			return $this->__paths;
		}
		$paths = array();
		$viewPaths = Configure::read('viewPaths');
		if ($plugin !== null) {
			$count = count($viewPaths);
			for ($i = 0; $i < $count; $i++) {
				$paths[] = $viewPaths[$i] . 'plugins' . DS . $plugin . DS;
			}

			$pluginPaths = Configure::read('pluginPaths');
			$count = count($pluginPaths);
			for ($i = 0; $i < $count; $i++) {
				$paths[] = $pluginPaths[$i] . $plugin . DS . 'views' . DS;
			}
		}

		$paths = array_merge($paths, $viewPaths);

		if (empty($this->__paths)) {
			$this->__paths = $paths;
		}

		return $paths;
	}
}
?>