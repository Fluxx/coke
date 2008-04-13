<?php
/* SVN FILE: $Id: set.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Library of array functions for Cake.
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
 * @since			CakePHP(tm) v 1.2.0
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Class used for manipulation of arrays.
 *
 * Long description for class
 *
 * @package		cake
 * @subpackage	cake.cake.libs
 */
class Set extends Object {
/**
 * Value of the Set object.
 *
 * @var array
 * @access public
 */
	var $value = array();
/**
 * Constructor. Defaults to an empty array.
 *
 * @access public
 */
	function __construct() {
		if (func_num_args() == 1 && is_array(func_get_arg(0))) {
			$this->value = func_get_arg(0);
		} else {
			$this->value = func_get_args();
		}
	}
/**
 * Returns the contents of the Set object
 *
 * @return array
 * @access public
 */
	function &get() {
		return $this->value;
	}
/**
 * This function can be thought of as a hybrid between PHP's array_merge and array_merge_recursive. The difference
 * to the two is that if an array key contains another array then the function behaves recursive (unlike array_merge)
 * but does not do if for keys containing strings (unlike array_merge_recursive). See the unit test for more information.
 *
 * Note: This function will work with an unlimited amount of arguments and typecasts non-array parameters into arrays.
 *
 * @param array $arr1 Array to be merged
 * @param array $arr2 Array to merge with
 * @return array Merged array
 * @access public
 */
	function merge($arr1, $arr2 = null) {
		$args = func_get_args();

		if (is_a($this, 'set')) {
			$backtrace = debug_backtrace();
			$previousCall = strtolower($backtrace[1]['class'].'::'.$backtrace[1]['function']);
			if ($previousCall != 'set::merge') {
				$r =& $this->value;
				array_unshift($args, null);
			}
		}
		if (!isset($r)) {
			$r = (array)current($args);
		}

		while (($arg = next($args)) !== false) {
			if (is_a($arg, 'set')) {
				$arg = $arg->get();
			}

			foreach ((array)$arg as $key => $val)	 {
				if (is_array($val) && isset($r[$key]) && is_array($r[$key])) {
					$r[$key] = Set::merge($r[$key], $val);
				} elseif (is_int($key)) {
					$r[] = $val;
				} else {
					$r[$key] = $val;
				}
			}
		}
		return $r;
	}
/**
 * Filters empty elements out of a route array, excluding '0'.
 *
 * @param mixed $var Either an array to filter, or value when in callback
 * @param boolean $isArray Force to tell $var is an array when $var is empty
 * @return mixed Either filtered array, or true/false when in callback
 * @access public
 */
	function filter($var, $isArray = false) {
		if (is_array($var) && (!empty($var) || $isArray)) {
			return array_filter($var, array('Set', 'filter'));
		} else {
			if ($var === 0 || $var === '0' || !empty($var)) {
				return true;
			} else {
				return false;
			}
		}
	}
/**
 * Pushes the differences in $array2 onto the end of $array
 *
 * @param mixed $array Original array
 * @param mixed $array2 Differences to push
 * @return array Combined array
 * @access public
 */
	function pushDiff($array = null, $array2 = null) {
		if ($array2 !== null && is_array($array2)) {
			foreach ($array2 as $key => $value) {
				if (!array_key_exists($key, $array)) {
					$array[$key] = $value;
				} else {
					if (is_array($value)) {
						$array[$key] = Set::pushDiff($array[$key], $array2[$key]);
					}
				}
			}
			return $array;
		}

		if (!isset($this->value)) {
			$this->value = array();
		}
		$this->value = Set::pushDiff($this->value, Set::__array($array));
		return $this->value;
	}
/**
 * Maps the contents of the Set object to an object hierarchy.
 * Maintains numeric keys as arrays of objects
 *
 * @param string $class A class name of the type of object to map to
 * @param string $tmp A temporary class name used as $class if $class is an array
 * @return object Hierarchical object
 * @access public
 */
	function map($class = 'stdClass', $tmp = 'stdClass') {
		if (is_array($class)) {
			$val = $class;
			$class = $tmp;
		} elseif (is_a($this, 'set')) {
			$val = $this->get();
		}

		if (empty($val) || $val == null) {
			return null;
		}
		return Set::__map($val, $class);
	}

/**
 * Get the array value of $array. If $array is null, it will return
 * the current array Set holds. If it is an object of type Set, it
 * will return its value. If it is another object, its object variables.
 * If it is anything else but an array, it will return an array whose first
 * element is $array.
 *
 * @param mixed $array Data from where to get the array.
 * @return array Array from $array.
 * @access private
 */
	function __array($array) {
		if ($array == null) {
			$array = $this->value;
		} elseif (is_object($array) && (is_a($array, 'set'))) {
			$array = $array->get();
		} elseif (is_object($array)) {
			$array = get_object_vars($array);
		} elseif (!is_array($array)) {
			$array = array($array);
		}
		return $array;
	}

/**
 * Maps the given value as an object. If $value is an object,
 * it returns $value. Otherwise it maps $value as an object of
 * type $class, and if primary assign _name_ $key on first array.
 * If $value is not empty, it will be used to set properties of
 * returned object (recursively). If $key is numeric will maintain array
 * structure
 *
 * @param mixed $value Value to map
 * @param string $class Class name
 * @param boolean $primary whether to assign first array key as the _name_
 * @return mixed Mapped object
 * @access private
 */
	function __map(&$array, $class, $primary = false) {
		if ($class === true) {
			$out = new stdClass;
		} else {
			$out = new $class;
		}
		if (is_array($array)) {
			$keys = array_keys($array);
			foreach ($array as $key => $value) {
				if($keys[0] === $key && $class !== true) {
					$primary = true;
				}
				if (is_numeric($key)) {
					if (is_object($out) && is_array($value)) {
						$out = get_object_vars($out);
					}
					$out[$key] = Set::__map($value, $class, true);
				} elseif ($primary === true && is_array($value)) {
					$out->_name_ = $key;
					$primary = false;
					foreach($value as $key2 => $value2) {
						$out->{$key2} = Set::__map($value2, $class);
					}
				} else {
					$out->{$key} = Set::__map($value, $class);
				}
			}
		} else {
			$out = $array;
		}
		return $out;
	}
/**
 * Checks to see if all the values in the array are numeric
 *
 * @param array $array The array to check.  If null, the value of the current Set object
 * @return boolean true if values are numeric, false otherwise
 * @access public
 */
	function numeric($array = null) {
		if ($array == null && (is_a($this, 'set') || is_a($this, 'Set'))) {
			$array = $this->get();
		}

		$numeric = true;
		$keys = array_keys($array);
		$count = count($keys);
		for ($i = 0; $i < $count; $i++) {
			if (!is_numeric($array[$keys[$i]])) {
				$numeric = false;
				break;
			}
		}
		return $numeric;
	}
/**
 * Return a value from an array list if the key exists.
 *
 * If a comma separated $list is passed arrays are numeric with the key of the first being 0
 * $list = 'no, yes' would translate to  $list = array(0 => 'no', 1 => 'yes');
 *
 * If an array is used, keys can be strings example: array('no' => 0, 'yes' => 1);
 *
 * $list defaults to 0 = no 1 = yes if param is not passed
 *
 * @param mixed $select Key in $list to return
 * @param mixed $list can be an array or a comma-separated list.
 * @return string the value of the array key or null if no match
 * @access public
 */
	function enum($select, $list = null) {
		if (empty($list) && is_a($this, 'Set')) {
			$list = $this->get();
		} elseif (empty($list)) {
			$list = array('no', 'yes');
		}

		$return = null;
		$list = Set::normalize($list, false);

		if (array_key_exists($select, $list)) {
			$return = $list[$select];
		}
		return $return;
	}
/**
 * Returns a series of values extracted from an array, formatted in a format string.
 *
 * @param array		$data Source array from which to extract the data
 * @param string	$format Format string into which values will be inserted, see sprintf()
 * @param array		$keys An array containing one or more Set::extract()-style key paths
 * @return array	An array of strings extracted from $keys and formatted with $format
 * @access public
 */
	function format($data, $format, $keys) {

		$extracted = array();
		$count = count($keys);

		if (!$count) {
			return;
		}

		for ($i = 0; $i < $count; $i++) {
			$extracted[] = Set::extract($data, $keys[$i]);
		}
		$out = array();
		$data = $extracted;
		$count = count($data[0]);

		if (preg_match_all('/\{([0-9]+)\}/msi', $format, $keys2) && isset($keys2[1])) {
			$keys = $keys2[1];
			$format = preg_split('/\{([0-9]+)\}/msi', $format);
			$count2 = count($format);

			for ($j = 0; $j < $count; $j++) {
				$formatted = '';
				for ($i = 0; $i <= $count2; $i++) {
					if (isset($format[$i])) {
						$formatted .= $format[$i];
					}
					if (isset($keys[$i]) && isset($data[$keys[$i]][$j])) {
						$formatted .= $data[$keys[$i]][$j];
					}
				}
				$out[] = $formatted;
			}
		} else {
			$count2 = count($data);
			for ($j = 0; $j < $count; $j++) {
				$args = array();
				for ($i = 0; $i < $count2; $i++) {
					if (isset($data[$i][$j])) {
						$args[] = $data[$i][$j];
					}
				}
				$out[] = vsprintf($format, $args);
			}
		}
		return $out;
	}
/**
 * Gets a value from an array or object that maps a given path.
 * The special {n}, as seen in the Model::generateList method, is taken care of here.
 *
 * @param array $data Array from where to extract
 * @param mixed $path As an array, or as a dot-separated string.
 * @return array Extracted data
 * @access public
 */
	function extract($data, $path = null) {
		if ($path === null && is_a($this, 'set')) {
			$path = $data;
			$data = $this->get();
		}
		if (is_object($data)) {
			$data = get_object_vars($data);
		}
		if (!is_array($path)) {
			$path = String::tokenize($path, '.', '{', '}');
		}
		$tmp = array();

		if (!is_array($path) || empty($path)) {
			return null;
		}

		foreach ($path as $i => $key) {
			if (is_numeric($key) && intval($key) > 0 || $key == '0') {
				if (isset($data[intval($key)])) {
					$data = $data[intval($key)];
				} else {
					return null;
				}
			} elseif ($key == '{n}') {
				foreach ($data as $j => $val) {
					if (is_int($j)) {
						$tmpPath = array_slice($path, $i + 1);
						if (empty($tmpPath)) {
							$tmp[] = $val;
						} else {
							$tmp[] = Set::extract($val, $tmpPath);
						}
					}
				}
				return $tmp;
			} elseif ($key == '{s}') {
				foreach ($data as $j => $val) {
					if (is_string($j)) {
						$tmpPath = array_slice($path, $i + 1);
						if (empty($tmpPath)) {
							$tmp[] = $val;
						} else {
							$tmp[] = Set::extract($val, $tmpPath);
						}
					}
				}
				return $tmp;
			} elseif (false !== strpos($key,'{') && false !== strpos($key,'}')) {
				$pattern = substr($key, 1, -1);

				foreach ($data as $j => $val) {
					if (preg_match('/^'.$pattern.'/s', $j) !== 0) {
						$tmpPath = array_slice($path, $i + 1);
						if (empty($tmpPath)) {
							$tmp[$j] = $val;
						} else {
							$tmp[$j] = Set::extract($val, $tmpPath);
						}
					}
				}
				return $tmp;
			} else {
				if (isset($data[$key])) {
					$data = $data[$key];
				} else {
					return null;
				}
			}
		}
		return $data;
	}
/**
 * Inserts $data into an array as defined by $path.
 *
 * @param mixed $list Where to insert into
 * @param mixed $path A dot-separated string.
 * @param array $data Data to insert
 * @return array
 * @access public
 */
	function insert($list, $path, $data = null) {
		if (empty($data) && is_a($this, 'Set')) {
			$data = $path;
			$path = $list;
			$list =& $this->get();
		}
		if (!is_array($path)) {
			$path = explode('.', $path);
		}
		$_list =& $list;

		foreach ($path as $i => $key) {
			if (is_numeric($key) && intval($key) > 0 || $key == '0') {
				$key = intval($key);
			}
			if ($i == count($path) - 1) {
				$_list[$key] = $data;
			} else {
				if (!isset($_list[$key])) {
					$_list[$key] = array();
				}
				$_list =& $_list[$key];
			}
		}
		return $list;
	}
/**
 * Removes an element from a Set or array as defined by $path.
 *
 * @param mixed $list From where to remove
 * @param mixed $path A dot-separated string.
 * @return array Array with $path removed from its value
 * @access public
 */
	function remove($list, $path = null) {
		if (empty($path) && is_a($this, 'Set')) {
			$path = $list;
			$list =& $this->get();
		}
		if (!is_array($path)) {
			$path = explode('.', $path);
		}
		$_list =& $list;

		foreach ($path as $i => $key) {
			if (is_numeric($key) && intval($key) > 0 || $key == '0') {
				$key = intval($key);
			}
			if ($i == count($path) - 1) {
				unset($_list[$key]);
			} else {
				if (!isset($_list[$key])) {
					return $list;
				}
				$_list =& $_list[$key];
			}
		}

		if (is_a($this, 'Set')) {
			$this->value = $list;
			return $this;
		} else {
			return $list;
		}
	}
/**
 * Checks if a particular path is set in an array
 *
 * @param mixed $data Data to check on
 * @param mixed $path A dot-separated string.
 * @return boolean true if path is found, false otherwise
 * @access public
 */
	function check($data, $path = null) {
		if (empty($path) && is_a($this, 'Set')) {
			$path = $data;
			$data = $this->get();
		}
		if (!is_array($path)) {
			$path = explode('.', $path);
		}

		foreach ($path as $i => $key) {
			if (is_numeric($key) && intval($key) > 0 || $key == '0') {
				$key = intval($key);
			}
			if ($i == count($path) - 1) {
				return isset($data[$key]);
			} else {
				if (!isset($data[$key])) {
					return false;
				}
				$data =& $data[$key];
			}
		}
		return true;
	}
/**
 * Computes the difference between a Set and an array, two Sets, or two arrays
 *
 * @param mixed $val1 First value
 * @param mixed $val2 Second value
 * @return array Computed difference
 * @access public
 */
	function diff($val1, $val2 = null) {
		if ($val2 == null && (is_a($this, 'set') || is_a($this, 'Set'))) {
			$val2 = $val1;
			$val1 = $this->get();
		}

		if (is_object($val2) && (is_a($val2, 'set') || is_a($val2, 'Set'))) {
			$val2 = $val2->get();
		}
		$out = array();

		if (empty($val1)) {
			return (array)$val2;
		} elseif (empty($val2)) {
			return (array)$val1;
		}

		foreach ($val1 as $key => $val) {
			if (array_key_exists($key, $val2) && $val2[$key] != $val) {
				$out[$key] = $val;
			} elseif (!array_key_exists($key, $val2)) {
				$out[$key] = $val;
			}
			unset($val2[$key]);
		}

		foreach ($val2 as $key => $val) {
			if (!array_key_exists($key, $out)) {
				$out[$key] = $val;
			}
		}
		return $out;
	}
/**
 * Determines if two Sets or arrays are equal
 *
 * @param array $val1 First value
 * @param array $val2 Second value
 * @return boolean true if they are equal, false otherwise
 * @access public
 */
	function isEqual($val1, $val2 = null) {
		if ($val2 == null && (is_a($this, 'set') || is_a($this, 'Set'))) {
			$val2 = $val1;
			$val1 = $this->get();
		}

		return ($val1 == $val2);
	}
/**
 * Determines if one Set or array contains the exact keys and values of another.
 *
 * @param array $val1 First value
 * @param array $val2 Second value
 * @return boolean true if $val1 contains $val2, false otherwise
 * @access public
 */
	function contains($val1, $val2 = null) {
		if ($val2 == null && is_a($this, 'set')) {
			$val2 = $val1;
			$val1 = $this->get();
		} elseif ($val2 != null && is_object($val2) && is_a($val2, 'set')) {
			$val2 = $val2->get();
		}

		foreach ($val2 as $key => $val) {
			if (is_numeric($key)) {
				if (!in_array($val, $val1)) {
					return false;
				}
			} else {
				if (!isset($val1[$key]) || $val1[$key] != $val) {
					return false;
				}
			}
		}
		return true;
	}
/**
 * Counts the dimensions of an array. If $all is set to false (which is the default) it will
 * only consider the dimension of the first element in the array.
 *
 * @param array $array Array to count dimensions on
 * @param boolean $all Set to true to count the dimension considering all elements in array
 * @param integer $count Start the dimension count at this number
 * @return integer The number of dimensions in $array
 * @access public
 */
	function countDim($array = null, $all = false, $count = 0) {
		if ($array === null) {
			$array = $this->get();
		} elseif (is_object($array) && is_a($array, 'set')) {
			$array = $array->get();
		}
		if ($all) {
			$depth = array($count);
			if (is_array($array) && reset($array) !== false) {
				foreach ($array as $value) {
					$depth[] = Set::countDim($value, true, $count + 1);
				}
			}
			$return = max($depth);
		} else {
			if (is_array(reset($array))) {
				$return = Set::countDim(reset($array)) + 1;
			} else {
				$return = 1;
			}
		}
		return $return;
	}
/**
 * Normalizes a string or array list.
 *
 * @param mixed $list List to normalize
 * @param boolean $assoc If true, $list will be converted to an associative array
 * @param string $sep If $list is a string, it will be split into an array with $sep
 * @param boolean $trim If true, separated strings will be trimmed
 * @return array
 * @access public
 */
	function normalize($list, $assoc = true, $sep = ',', $trim = true) {
		if (is_string($list)) {
			$list = explode($sep, $list);
			if ($trim) {
				$list = array_map('trim', $list);
			}
			if ($assoc) {
				return Set::normalize($list);
			}
		} elseif (is_array($list)) {
			$keys = array_keys($list);
			$count = count($keys);
			$numeric = true;

			if (!$assoc) {
				for ($i = 0; $i < $count; $i++) {
					if (!is_int($keys[$i])) {
						$numeric = false;
						break;
					}
				}
			}
			if (!$numeric || $assoc) {
				$newList = array();
				for ($i = 0; $i < $count; $i++) {
					if (is_int($keys[$i])) {
						$newList[$list[$keys[$i]]] = null;
					} else {
						$newList[$keys[$i]] = $list[$keys[$i]];
					}
				}
				$list = $newList;
			}
		}
		return $list;
	}
/**
 * Creates an associative array using a $path1 as the path to build its keys, and optionally
 * $path2 as path to get the values. If $path2 is not specified, all values will be initialized
 * to null (useful for Set::merge). You can optionally group the values by what is obtained when
 * following the path specified in $groupPath.
 *
 * @param array $data Array from where to extract keys and values
 * @param mixed $path1 As an array, or as a dot-separated string.
 * @param mixed $path2 As an array, or as a dot-separated string.
 * @param string $groupPath As an array, or as a dot-separated string.
 * @return array Combined array
 * @access public
 */
	function combine($data, $path1 = null, $path2 = null, $groupPath = null) {
		if (is_a($this, 'set') && is_string($data) && is_string($path1) && is_string($path2)) {
			$groupPath = $path2;
			$path2 = $path1;
			$path1 = $data;
			$data = $this->get();

		} elseif (is_a($this, 'set') && is_string($data) && empty($path2)) {
			$path2 = $path1;
			$path1 = $data;
			$data = $this->get();
		}

		if (is_object($data)) {
			$data = get_object_vars($data);
		}

		if (is_array($path1)) {
			$format = array_shift($path1);
			$keys = Set::format($data, $format, $path1);
		} else {
			$keys = Set::extract($data, $path1);
		}

		if (!empty($path2) && is_array($path2)) {
			$format = array_shift($path2);
			$vals = Set::format($data, $format, $path2);

		} elseif (!empty($path2)) {
			$vals = Set::extract($data, $path2);

		} else {
			$count = count($keys);
			for ($i = 0; $i < $count; $i++) {
				$vals[$i] = null;
			}
		}

		if ($groupPath != null) {
			$group = Set::extract($data, $groupPath);
			if (!empty($group)) {
				$c = count($keys);
				for ($i = 0; $i < $c; $i++) {
					if (!isset($group[$i])) {
						$group[$i] = 0;
					}
					if (!isset($out[$group[$i]])) {
						$out[$group[$i]] = array();
					}
					$out[$group[$i]][$keys[$i]] = $vals[$i];
				}
				return $out;
			}
		}

		return array_combine($keys, $vals);
	}
/**
 * Converts an object into an array
 *
 * @param object $object
 * @return array
 */
	function reverse($object) {
		$out = array();
		if (is_a($object, 'xmlnode') || is_a($object, 'XMLNode')) {
			if (isset($object->name) && isset($object->children)) {
				if ($object->name === 'root' && !empty($object->children)) {
					$out = Set::reverse($object->children[0]);
				} else {
					$children = array();
					if (!empty($object->children)) {
						foreach ($object->children as $child) {
							$childName = Inflector::camelize($child->name);
							if (count($child->children) > 1 && isset($child->name)) {
								$children[$childName][] = Set::reverse($child);
							} else {
								$children = array_merge($children, Set::reverse($child));
							}
						}
					}

					$camelName = Inflector::camelize($object->name);
					if (!empty($object->attributes) && !empty($children)) {
						$out[$camelName] = array_merge($object->attributes, $children);
					} elseif (!empty($object->attributes) && !empty($object->value)) {
						$out[$object->name] = array_merge($object->attributes, array('value' => $object->value));
					} elseif (!empty($object->attributes)) {
						$out[$camelName] = $object->attributes;
					} elseif (!empty($children) && (isset($children[$childName][0]) || isset($children[$child->name][0]))) {
						$out = $children;
					} elseif (!empty($children)) {
						$out[$camelName] = $children;
					} elseif (!empty($object->value)) {
						$out[$object->name] = $object->value;
					}
				}
			}
		} else {
			if (is_object($object)) {
				$keys = get_object_vars($object);
				if (isset($keys['_name_'])) {
					$identity = $keys['_name_'];
					unset($keys['_name_']);
				}
				$new = array();
				foreach ($keys as $key => $value) {
					if (is_array($value)) {
						$new[$key] = (array)Set::reverse($value);
					} else {
						$new[$key] = Set::reverse($value);
					}
				}
				if (isset($identity)) {
					$out[$identity] = $new;
				} else {
					$out = $new;
				}
			} elseif (is_array($object)) {
				foreach ($object as $key => $value) {
					$out[$key] = Set::reverse($value);
				}
			} else {
				$out = $object;
			}

		}
		return $out;
	}
}
?>