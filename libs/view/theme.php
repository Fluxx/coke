<?php
/* SVN FILE: $Id: theme.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * A custom view class that is used for themeing
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
 * Theme view class
 *
 * @package			cake
 * @subpackage		cake.cake.libs.view
 */
class ThemeView extends View {
/**
 * System path to themed element: themed . DS . theme . DS . elements . DS
 *
 * @var string
 */
	var $themeElement = null;
/**
 * System path to themed layout: themed . DS . theme . DS . layouts . DS
 *
 * @var string
 */
	var $themeLayout = null;
/**
 * System path to themed: themed . DS . theme . DS
 *
 * @var string
 */
	var $themePath = null;

/**
 * Enter description here...
 *
 * @param unknown_type $controller
 */
	function __construct (&$controller) {
		parent::__construct($controller);

    	$this->theme =& $controller->theme;
    	if (!empty($this->theme)) {
    		if (is_dir(WWW_ROOT . 'themed' . DS . $this->theme)) {
    			$this->themeWeb = 'themed/'. $this->theme .'/';
    		}
			/* deprecated: as of 6128 the following properties are no longer needed */
    		$this->themeElement = 'themed'. DS . $this->theme . DS .'elements'. DS;
    		$this->themeLayout =  'themed'. DS . $this->theme . DS .'layouts'. DS;
    		$this->themePath = 'themed'. DS . $this->theme . DS;
    	}
	}

/**
 * Return all possible paths to find view files in order
 *
 * @param string $plugin
 * @return array paths
 * @access private
 */
	function _paths($plugin = null, $cached = true) {
		$paths = parent::_paths($plugin, $cached);

		if (!empty($this->theme)) {
			$count = count($paths);
			for ($i = 0; $i < $count; $i++) {
				$themePaths[] = $paths[$i] . 'themed'. DS . $this->theme . DS;
			}
			$paths = array_merge($themePaths, $paths);
		}

		if(empty($this->__paths)) {
			$this->__paths = $paths;
		}

		return $paths;
	}
}
?>