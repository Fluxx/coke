<?php
/* SVN FILE: $Id: aros_aco_fixture.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package			cake.tests
 * @subpackage		cake.tests.fixtures
 * @since			CakePHP(tm) v 1.2.0.4667
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.fixtures
 */
class ArosAcoFixture extends CakeTestFixture {
	var $name = 'ArosAco';
	var $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary', 'extra'=> 'auto_increment'),
		'aro_id' => array('type' => 'integer', 'length' => 10, 'null' => false),
		'aco_id' => array('type' => 'integer', 'length' => 10, 'null' => false),
		'_create' => array('type' => 'string', 'length' => 2, 'null' => false, 'default' => '0'),
		'_read' => array('type' => 'string', 'length' => 2, 'null' => false, 'default' => '0'),
		'_update' => array('type' => 'string', 'length' => 2, 'null' => false, 'default' => '0'),
		'_delete' => array('type' => 'string', 'length' => 2, 'null' => false, 'default' => '0')
	);
	var $records = array(
	);
}

?>