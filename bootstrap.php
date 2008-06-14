<?php
/* SVN FILE: $Id: bootstrap.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
if (!defined('PHP5')) {
	define ('PHP5', (phpversion() >= 5));
}
/**
 * Configuration, directory layout and standard libraries
 */
	if (!isset($bootstrap)) {
		require CORE_PATH . 'coke' . DS . 'basics.php';
		$TIME_START = getMicrotime();
		require CORE_PATH . 'coke' . DS . 'config' . DS . 'paths.php';
		require LIBS . 'object.php';
		require LIBS . 'inflector.php';
		require LIBS . 'configure.php';
	}
	require LIBS . 'file.php';
	require LIBS . 'cache.php';

	Configure::getInstance();

	require LIBS . 'session.php';
	require LIBS . 'security.php';
	require LIBS . 'string.php';
	require LIBS.'model'.DS.'record.php';

	$url = null;
	require CAKE . 'dispatcher.php';
?>