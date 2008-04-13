<?php
/* SVN FILE: $Id: helper.php 6311 2008-01-02 06:33:52Z phpnut $ */

/**
 * Backend for helpers.
 *
 * Internal methods for the Helpers.
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
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Included libs
 */
uses('overloadable');

/**
 * Backend for helpers.
 *
 * Long description for class
 *
 * @package		cake
 * @subpackage	cake.cake.libs.view
 */
class Helper extends Overloadable {

/**
 * List of helpers used by this helper
 *
 * @var array
 */
	var $helpers = null;
/**
 * Base URL
 *
 * @var string
 */
	var $base = null;
/**
 * Webroot path
 *
 * @var string
 */
	var $webroot = null;
/**
 * Theme name
 *
 * @var string
 */
	var $themeWeb = null;
/**
 * URL to current action.
 *
 * @var string
 */
	var $here = null;
/**
 * Parameter array.
 *
 * @var array
 */
	var $params = array();
/**
 * Current action.
 *
 * @var string
 */
	var $action = null;
/**
 * Plugin path
 *
 * @var string
 */
	var $plugin = null;
/**
 * POST data for models
 *
 * @var array
 */
	var $data = null;
/**
 * List of named arguments
 *
 * @var array
 */
	var $namedArgs = null;
/**
 * URL argument separator character
 *
 * @var string
 */
	var $argSeparator = null;
/**
 * Contains model validation errors of form post-backs
 *
 * @access public
 * @var array
 */
	var $validationErrors = null;
/**
 * Holds tag templates.
 *
 * @access public
 * @var array
 */
	var $tags = array();
/**
 * Holds the content to be cleaned.
 *
 * @access private
 * @var mixed
 */
	var $__tainted = null;
/**
 * Holds the cleaned content.
 *
 * @access private
 * @var mixed
 */
	var $__cleaned = null;
/**
 * Default overload methods
 *
 * @access protected
 */
	function get__($name) {}
	function set__($name, $value) {}
	function call__($method, $params) {
		trigger_error(sprintf(__('Method %1$s::%2$s does not exist', true), get_class($this), $method), E_USER_WARNING);
	}

/**
 * Parses tag templates into $this->tags.
 *
 * @param $name file name
 * @return array merged tags from config/$name.php
 */
	function loadConfig($name = 'tags') {
		if (file_exists(APP . 'config' . DS . $name .'.php')) {
			require(APP . 'config' . DS . $name .'.php');
			if (isset($tags)) {
				$this->tags = array_merge($this->tags, $tags);
			}
		}
		return $this->tags;
	}
/**
 * Finds URL for specified action.
 *
 * Returns an URL pointing to a combination of controller and action. Param
 * $url can be:
 *	+ Empty - the method will find adress to actuall controller/action.
 *	+ '/' - the method will find base URL of application.
 *	+ A combination of controller/action - the method will find url for it.
 *
 * @param  mixed  $url    Cake-relative URL, like "/products/edit/92" or "/presidents/elect/4"
 *                        or an array specifying any of the following: 'controller', 'action',
 *                        and/or 'plugin', in addition to named arguments (keyed array elements),
 *                        and standard URL arguments (indexed array elements)
 * @param boolean $full   If true, the full base URL will be prepended to the result
 * @return string  Full translated URL with base path.
 */
	function url($url = null, $full = false) {
		return Router::url($url, $full);
	}
/**
 * Checks if a file exists when theme is used, if no file is found default location is returned
 *
 * @param  string  $file
 * @return string  $webPath web path to file.
 */
	function webroot($file) {
		$webPath = "{$this->webroot}" . $file;
		if (!empty($this->themeWeb)) {
			$os = env('OS');
			if (!empty($os) && strpos($os, 'Windows') !== false) {
				$path = str_replace('/', '\\', WWW_ROOT . $this->themeWeb  . $file);
			} else {
				$path = WWW_ROOT . $this->themeWeb  . $file;
			}
			if (file_exists($path)) {
				$webPath = "{$this->webroot}" . $this->themeWeb . $file;
			}
		}
		return str_replace('//', '/', $webPath);
	}

/**
 * Used to remove harmful tags from content
 *
 * @param mixed $output
 * @return cleaned content for output
 * @access public
 */
	function clean($output) {
		$this->__reset();
		if (is_array($output)) {
			foreach ($output as $key => $value) {
				$return[$key] = $this->clean($value);
			}
			return $return;
		}
		$this->__tainted = $output;
		$this->__clean();
		return $this->__cleaned;
	}
/**
 * Returns a space-delimited string with items of the $options array. If a
 * key of $options array happens to be one of:
 *	+ 'compact'
 *	+ 'checked'
 *	+ 'declare'
 *	+ 'readonly'
 *	+ 'disabled'
 *	+ 'selected'
 *	+ 'defer'
 *	+ 'ismap'
 *	+ 'nohref'
 *	+ 'noshade'
 *	+ 'nowrap'
 *	+ 'multiple'
 *	+ 'noresize'
 *
 * And its value is one of:
 *	+ 1
 *	+ true
 *	+ 'true'
 *
 * Then the value will be reset to be identical with key's name.
 * If the value is not one of these 3, the parameter is not output.
 *
 * @param  array  $options Array of options.
 * @param  array  $exclude Array of options to be excluded.
 * @param  string $insertBefore String to be inserted before options.
 * @param  string $insertAfter  String to be inserted ater options.
 * @return string
 */
	function _parseAttributes($options, $exclude = null, $insertBefore = ' ', $insertAfter = null) {
		if (is_array($options)) {
			$options = array_merge(array('escape' => true), $options);

			if (!is_array($exclude)) {
				$exclude = array();
			}
			$keys = array_diff(array_keys($options), array_merge((array)$exclude, array('escape')));
			$values = array_intersect_key(array_values($options), $keys);
			$escape = $options['escape'];
			$attributes = array();

			foreach ($keys as $index => $key) {
				$attributes[] = $this->__formatAttribute($key, $values[$index], $escape);
			}
			$out = implode(' ', $attributes);
		} else {
			$out = $options;
		}
		return $out ? $insertBefore . $out . $insertAfter : '';
	}
/**
 * @param  string $key
 * @param  string $value
 * @return string
 * @access private
 */
	function __formatAttribute($key, $value, $escape = true) {
		$attribute = '';
		$attributeFormat = '%s="%s"';
		$minimizedAttributes = array('compact', 'checked', 'declare', 'readonly', 'disabled', 'selected', 'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize');
		if (is_array($value)) {
			$value = '';
		}

		if (in_array($key, $minimizedAttributes)) {
			if ($value === 1 || $value === true || $value === 'true' || $value == $key) {
				$attribute = sprintf($attributeFormat, $key, $key);
			}
		} else {
			$attribute = sprintf($attributeFormat, $key, ife($escape, h($value), $value));
		}
		return $attribute;
	}
/**
 * @deprecated
 */
	function setFormTag($tagValue, $setScope = false) {
		return $this->setEntity($tagValue, $setScope);
	}
/**
 * Sets this helper's model and field properties to the dot-separated value-pair in $entity.
 *
 * @param mixed $entity A field name, like "ModelName.fieldName" or "ModelName.ID.fieldName"
 * @param boolean $setScope Sets the view scope to the model specified in $tagValue
 * @return void
 */
	function setEntity($entity, $setScope = false) {
		$view =& ClassRegistry::getObject('view');

		if ($setScope) {
			$view->modelScope = false;
		}

		if ($entity === null) {
			$view->model = null;
			$view->association = null;
			$view->modelId = null;
			$view->modelScope = false;
			return;
		}

		$sameScope = $hasField = false;
		$parts = preg_split('/\/|\./', $entity);

		$model = $view->model;
		if(count($parts) === 1 || is_numeric($parts[0])) {
			$sameScope = true;
		} else {
			if (ClassRegistry::isKeySet($parts[0])) {
				$model = $parts[0];
			}
		}

		if (ClassRegistry::isKeySet($model)) {
			$ModelObj =& ClassRegistry::getObject($model);
			for ($i = 0; $i < count($parts); $i++) {
				if ($ModelObj->hasField($parts[$i]) || array_key_exists($parts[$i], $ModelObj->validate)) {
					$hasField = $i;
					if ($hasField === 0) {
						$sameScope = true;
					}
					break;
				}
			}

			if($sameScope === true && in_array($parts[0], array_keys($ModelObj->hasAndBelongsToMany))) {
				$sameScope = false;
			}
		}

		$view->field = $view->modelId = $view->fieldSuffix = $view->association = null;

		switch (count($parts)) {
			case 1:
				if($view->modelScope === false) {
					$view->model = $parts[0];
				} else {
					$view->field = $parts[0];
					if($sameScope === false) {
						$view->association = $parts[0];
					}
				}
			break;
		 	case 2:
				if ($view->modelScope === false) {
					list($view->model, $view->field) = $parts;
				} elseif ($sameScope === true && $hasField === 0) {
					list($view->field, $view->fieldSuffix) = $parts;
				} elseif ($sameScope === true && $hasField === 1) {
					list($view->modelId, $view->field) = $parts;
				} else {
					list($view->association, $view->field) = $parts;
				}
			break;
			case 3:
				if ($sameScope === true && $hasField === 1) {
					list($view->modelId, $view->field, $view->fieldSuffix) = $parts;
				} elseif ($hasField === 2) {
					list($view->association, $view->modelId, $view->field) = $parts;
				} else {
					list($view->association, $view->field, $view->fieldSuffix) = $parts;
				}
			break;
		}

		if (!isset($view->model) || empty($view->model)) {
			$view->model = $view->association;
			$view->association = null;
		} elseif ($view->model === $view->association) {
			$view->association = null;
		}

		if ($setScope) {
			$view->modelScope = true;
		}
	}
/**
 * Gets the currently-used model of the rendering context.
 *
 * @return string
 */
	function model() {
		$view =& ClassRegistry::getObject('view');
		if (!empty($view->association)) {
			return $view->association;
		} else {
			return $view->model;
		}
	}
/**
 * Gets the ID of the currently-used model of the rendering context.
 *
 * @return mixed
 */
	function modelID() {
		$view =& ClassRegistry::getObject('view');
		return $view->modelId;
	}
/**
 * Gets the currently-used model field of the rendering context.
 *
 * @return string
 */
	function field() {
		$view =& ClassRegistry::getObject('view');
		return $view->field;
	}
/**
 * Returns false if given FORM field has no errors. Otherwise it returns the constant set in the array Model->validationErrors.
 *
 * @param string $model Model name as string
 * @param string $field		Fieldname as string
 * @return boolean True on errors.
 */
	function tagIsInvalid($model = null, $field = null) {
		if ($model == null) {
			$model = $this->model();
		}
		if ($field == null) {
			$field = $this->field();
		}
		return empty($this->validationErrors[$model][$field]) ? 0 : $this->validationErrors[$model][$field];
	}
/**
 * Generates a DOM ID for the selected element, if one is not set.
 *
 * @param mixed $options
 * @param string $id
 * @return mixed
 */
	function domId($options = null, $id = 'id') {
		$view =& ClassRegistry::getObject('view');

		if (is_array($options) && array_key_exists($id, $options) && $options[$id] === null) {
			unset($options[$id]);
			return $options;
		} elseif (!is_array($options) && $options !== null) {
			$this->setEntity($options);
			return $this->domId();
		}

		$dom = $this->model() . Inflector::camelize($view->field) . Inflector::camelize($view->fieldSuffix);

		if (is_array($options) && !array_key_exists($id, $options)) {
			$options[$id] = $dom;
		} elseif ($options === null) {
			return $dom;
		}
		return $options;
	}
/**
 * Gets the input field name for the current tag
 *
 * @param array $options
 * @param string $key
 * @return array
 */
	function __name($options = array(), $field = null, $key = 'name') {
		$view =& ClassRegistry::getObject('view');

		if ($options === null) {
			$options = array();
		} elseif (is_string($options)) {
			$field = $options;
			$options = 0;
		}

		if (!empty($field)) {
			$this->setEntity($field);
		}

		if (is_array($options) && array_key_exists($key, $options)) {
			return $options;
		}

		switch ($field) {
			case '_method':
				$name = $field;
			break;
			default:
				$name = 'data[' . join('][', $view->entity()) . ']';
			break;
		}

		if (is_array($options)) {
			$options[$key] = $name;
			return $options;
		} else {
			return $name;
		}
	}
/**
 * Gets the data for the current tag
 *
 * @param array $options
 * @param string $key
 * @return array
 * @access public
 */
	function value($options = array(), $field = null, $key = 'value') {
		if ($options === null) {
			$options = array();
		} elseif (is_string($options)) {
			$field = $options;
			$options = 0;
		}

		if (!empty($field)) {
			$this->setEntity($field);
		}

		if (is_array($options) && isset($options[$key])) {
			return $options;
		}

		$result = null;

		if (isset($this->data[$this->model()][$this->field()])) {
			$result = $this->data[$this->model()][$this->field()];
		} elseif (isset($this->data[$this->field()]) && is_array($this->data[$this->field()])) {
			if (ClassRegistry::isKeySet($this->field())) {
				$model =& ClassRegistry::getObject($this->field());
				$result = $this->__selectedArray($this->data[$this->field()], $model->primaryKey);
			}
		} elseif (isset($this->data[$this->model()][$this->modelID()][$this->field()])) {
			$result = $this->data[$this->model()][$this->modelID()][$this->field()];
		}

		if (is_array($result)) {
			$view =& ClassRegistry::getObject('view');
			if(isset($result[$view->fieldSuffix])) {
				$result = $result[$view->fieldSuffix];
			}
		}

		if (is_array($options)) {
			if (empty($result) && isset($options['default'])) {
				$result = $options['default'];
			}
			unset($options['default']);
		}

		if (is_array($options)) {
			$options[$key] = $result;
			return $options;
		} else {
			return $result;
		}
	}
/**
 * Sets the defaults for an input tag
 *
 * @param array $options
 * @param string $key
 * @return array
 */
	function __initInputField($field, $options = array()) {
		if ($field !== null) {
			$this->setEntity($field);
		}
		$options = (array)$options;
		$options = $this->__name($options);
		$options = $this->value($options);
		$options = $this->domId($options);
		if ($this->tagIsInvalid()) {
			$options = $this->addClass($options, 'form-error');
		}
		return $options;
	}
/**
 * Adds the given class to the element options
 *
 * @param array $options
 * @param string $class
 * @param string $key
 * @return array
 */
	function addClass($options = array(), $class = null, $key = 'class') {
		if (isset($options[$key]) && trim($options[$key]) != '') {
			$options[$key] .= ' ' . $class;
		} else {
			$options[$key] = $class;
		}
		return $options;
	}
/**
 * Returns a string generated by a helper method
 *
 * This method can be overridden in subclasses to do generalized output post-processing
 *
 * @param  string  $str	String to be output.
 * @return string
 */
	function output($str) {
		return $str;
	}
/**
 * Assigns values to tag templates.
 *
 * Finds a tag template by $keyName, and replaces $values's keys with
 * $values's keys.
 *
 * @param  string $keyName Name of the key in the tag array.
 * @param  array  $values  Values to be inserted into tag.
 * @return string Tag with inserted values.
 */
	function assign($keyName, $values) {
		$out = $keyName;
		if (isset($this->tags) && isset($this->tags[$keyName])) {
			$out = $this->tags[$keyName];
		}

		//$out =
	}
/**
 * Before render callback.  Overridden in subclasses.
 *
 */
	function beforeRender() {
	}
/**
 * After render callback.  Overridden in subclasses.
 *
 */
	function afterRender() {
	}
/**
 * Before layout callback.  Overridden in subclasses.
 *
 */
	function beforeLayout() {
	}
/**
 * After layout callback.  Overridden in subclasses.
 *
 */
	function afterLayout() {
	}
/**
 * Enter description here...
 *
 * @param mixed $data
 * @param string $key
 * @return array
 * @access private
 */
	function __selectedArray($data, $key = 'id') {
		if (!is_array($data)) {
			$model = $data;
			if (!empty($this->data[$model][$model])) {
				return $this->data[$model][$model];
			}
			if (!empty($this->data[$model])) {
				$data = $this->data[$model];
			}
		}
		$array = array();
		if (!empty($data)) {
			foreach ($data as $var) {
				$array[$var[$key]] = $var[$key];
			}
		}
		return $array;
	}
/**
 * Resets the vars used by Helper::clean() to null
 *
 * @access private
 */
	function __reset() {
		$this->__tainted = null;
		$this->__cleaned = null;
	}
/**
 * Removes harmful content from output
 *
 * @access private
 */
	function __clean() {
		if (get_magic_quotes_gpc()) {
			$this->__cleaned = stripslashes($this->__tainted);
		} else {
			$this->__cleaned = $this->__tainted;
		}

		$this->__cleaned = str_replace(array("&amp;","&lt;","&gt;"),array("&amp;amp;","&amp;lt;","&amp;gt;"), $this->__cleaned);
		$this->__cleaned = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u',"$1;", $this->__cleaned);
		$this->__cleaned = preg_replace('#(&\#x*)([0-9A-F]+);*#iu',"$1$2;", $this->__cleaned);
		$this->__cleaned = html_entity_decode($this->__cleaned, ENT_COMPAT, "UTF-8");
		$this->__cleaned = preg_replace('#(<*[^>]*[\x00-\x20\"\'])(on|xmlns)[^>]*>#iUu',"$1>", $this->__cleaned);
		$this->__cleaned = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*)[\\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu','$1=$2nojavascript...', $this->__cleaned);
		$this->__cleaned = preg_replace('#([a-z]*)[\x00-\x20]*=([\'\"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu','$1=$2novbscript...', $this->__cleaned);
		$this->__cleaned = preg_replace('#([a-z]*)[\x00-\x20]*=*([\'\"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#iUu','$1=$2nomozbinding...', $this->__cleaned);
		$this->__cleaned = preg_replace('#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*expression[\x00-\x20]*\([^>]*>#iU',"$1>", $this->__cleaned);
		$this->__cleaned = preg_replace('#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*behaviour[\x00-\x20]*\([^>]*>#iU',"$1>", $this->__cleaned);
		$this->__cleaned = preg_replace('#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*>#iUu',"$1>",$this->__cleaned);
		$this->__cleaned = preg_replace('#</*\w+:\w[^>]*>#i',"", $this->__cleaned);
		do {
			$oldstring = $this->__cleaned;
			$this->__cleaned = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i',"",$this->__cleaned);
		} while ($oldstring != $this->__cleaned);
	}
}
?>