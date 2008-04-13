<?php
/* SVN FILE: $Id: syfile_fixture.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
class SyfileFixture extends CakeTestFixture {
	var $name = 'Syfile';
	var $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary', 'extra'=> 'auto_increment'),
		'image_id' => array('type' => 'integer', 'null' => true),
		'name' => array('type' => 'string', 'null' => false),
		'item_count' => array('type' => 'integer', 'null' => true)
	);
	var $records = array(
		array('id' => 1, 'image_id' => 1, 'name' => 'Syfile 1'),
		array('id' => 2, 'image_id' => 2, 'name' => 'Syfile 2'),
		array('id' => 3, 'image_id' => 5, 'name' => 'Syfile 3'),
		array('id' => 4, 'image_id' => 3, 'name' => 'Syfile 4'),
		array('id' => 5, 'image_id' => 4, 'name' => 'Syfile 5'),
		array('id' => 6, 'image_id' => null, 'name' => 'Syfile 6')
	);
}
?>