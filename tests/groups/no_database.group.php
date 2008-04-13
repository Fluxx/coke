<?php
/* SVN FILE: $Id: no_database.group.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
 * @subpackage		cake.tests.groups
 * @since			CakePHP(tm) v 1.2.0.4206
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
/** AllCoreLibGroupTest
 *
 * This test group will run all test in the cases/libs directory.
 *
 * @package    cake.tests
 * @subpackage cake.tests.groups
 */
class AllCoreWithOutDatabaseGroupTest extends GroupTest {
	var $label = 'All tests without a database connection';

	function AllCoreWithOutDatabaseGroupTest() {
		TestManager::addTestFile($this, CORE_TEST_CASES . DS . 'dispatcher');
		TestManager::addTestFile($this, CORE_TEST_CASES . DS . 'libs' . DS . 'router');
		TestManager::addTestFile($this, CORE_TEST_CASES . DS . 'libs' . DS . 'inflector');
		TestManager::addTestFile($this, CORE_TEST_CASES . DS . 'libs' . DS . 'validation');
		TestManager::addTestFile($this, CORE_TEST_CASES . DS . 'libs' . DS . 'session');
		TestManager::addTestFile($this, CORE_TEST_CASES . DS . 'libs' . DS . 'socket');
		TestManager::addTestCasesFromDirectory($this, CORE_TEST_CASES . DS . 'libs' . DS . 'view');
	}
}
?>