<?php
/* SVN FILE: $Id: memcache.test.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
 * @subpackage		cake.tests.cases.libs.cache
 * @since			CakePHP(tm) v 1.2.0.5434
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
uses('cache', 'cache' . DS . 'memcache');
/**
 * Short description for class.
 *
 * @package    cake.tests
 * @subpackage cake.tests.cases.libs.cache
 */
class MemcacheEngineTest extends UnitTestCase {

	function skip() {
		$skip = true;
		if($result = Cache::engine('Memcache')) {
			$skip = false;
		}
		$this->skipif ($skip, 'Memcache not available');
	}

	function setUp() {
		Cache::config('memcache', array('engine'=>'Memcache'));
	}

	function testSettings() {
		$settings = Cache::settings();
		$expecting = array('duration'=> 3600,
						'probability' => 100,
						'servers' => array('127.0.0.1'),
						'compress' => false,
						'engine' => 'Memcache'
						);
		$this->assertEqual($settings, $expecting);
	}

	function testConnect() {
		$Cache =& Cache::getInstance();
		$result = $Cache->_Engine['Memcache']->connect('127.0.0.1');
		$this->assertTrue($result);
	}


	function testReadAndWriteCache() {
		$result = Cache::read('test');
		$expecting = '';
		$this->assertEqual($result, $expecting);

		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('test', $data, 1);
		$this->assertTrue($result);

		$result = Cache::read('test');
		$expecting = $data;
		$this->assertEqual($result, $expecting);
	}

	function testExpiry() {
		sleep(2);
		$result = Cache::read('test');
		$this->assertFalse($result);

		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('other_test', $data, 1);
		$this->assertTrue($result);

		sleep(2);
		$result = Cache::read('other_test');
		$this->assertFalse($result);

		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('other_test', $data, "+1 second");
		$this->assertTrue($result);

		sleep(2);
		$result = Cache::read('other_test');
		$this->assertFalse($result);
	}

	function testDeleteCache() {
		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('delete_test', $data);
		$this->assertTrue($result);

		$result = Cache::delete('delete_test');
		$this->assertTrue($result);
	}

	function tearDown() {
		Cache::config('default');
	}
}
?>