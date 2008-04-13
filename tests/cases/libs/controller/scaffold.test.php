<?php
/* SVN FILE: $Id: scaffold.test.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
 * @subpackage		cake.tests.cases.libs.controller
 * @since			CakePHP(tm) v 1.2.0.5436
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
uses('controller' . DS . 'scaffold');
class ScaffoldMockController extends Controller {

	var $name = 'ScaffoldMock';

	var $scaffold;
}
class ScaffoldMock extends CakeTestModel {

	var $useTable = 'posts';

}
class TestScaffoldView extends ScaffoldView {

	function testGetFilename($action) {
		return $this->_getViewFileName($action);
	}
}
/**
 * Short description for class.
 *
 * @package    cake.tests
 * @subpackage cake.tests.cases.libs.controller
 */
class ScaffoldViewTest extends CakeTestCase {

	var $fixtures = array('core.post');

	function setUp() {
		$this->Controller = new ScaffoldMockController();
	}

	function tearDown() {
		unset($this->Controller);
	}
	function testGetViewFilename() {
		$this->Controller->action = 'index';
		$ScaffoldView =& new TestScaffoldView($this->Controller);
		$result = $ScaffoldView->testGetFilename('index');
		$expected = TEST_CAKE_CORE_INCLUDE_PATH . 'libs' . DS . 'view' . DS . 'scaffolds' . DS . 'index.ctp';
		$this->assertEqual($result, $expected);


		$result = $ScaffoldView->testGetFilename('error');
		$expected = 'cake' . DS . 'libs' . DS . 'view' . DS . 'errors' . DS . 'scaffold_error.ctp';
		$this->assertEqual($result, $expected);
	}
}
?>