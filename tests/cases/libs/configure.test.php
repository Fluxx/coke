<?php
/* SVN FILE: $Id: configure.test.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
 * @subpackage		cake.tests.cases.libs
 * @since			CakePHP(tm) v 1.2.0.5432
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
uses('configure');
/**
 * Short description for class.
 *
 * @package    cake.tests
 * @subpackage cake.tests.cases.libs
 */
class ConfigureTest extends UnitTestCase {

	function setUp() {
		$this->Configure =& Configure::getInstance();
	}

	function testListCoreObjects() {
		$result = $this->Configure->listObjects('class', TEST_CAKE_CORE_INCLUDE_PATH . 'libs');
		$this->assertTrue(in_array('Xml', $result));
		$this->assertTrue(in_array('Cache', $result));
		$this->assertTrue(in_array('HttpSocket', $result));

		$result = $this->Configure->listObjects('model');
		$this->assertTrue(in_array('Model', $result));

		$result = $this->Configure->listObjects('behavior');
		$this->assertTrue(in_array('Tree', $result));

		$result = $this->Configure->listObjects('controller');
		$this->assertTrue(in_array('Pages', $result));

		$result = $this->Configure->listObjects('component');
		$this->assertTrue(in_array('Auth', $result));

		$result = $this->Configure->listObjects('view');
		$this->assertTrue(in_array('Media', $result));

		$result = $this->Configure->listObjects('helper');
		$this->assertTrue(in_array('Html', $result));
	}

	function tearDown() {
		unset($this->Configure);
	}
}

?>