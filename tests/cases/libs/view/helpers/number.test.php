<?php
/* SVN FILE: $Id: number.test.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
 * @link			https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package			cake.tests
 * @subpackage		cake.tests.cases.libs.view.helpers
 * @since			CakePHP(tm) v 1.2.0.4206
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
uses('view'.DS.'helpers'.DS.'app_helper', 'view'.DS.'helper', 'view'.DS.'helpers'.DS.'number');
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.view.helpers
 */
class NumberTest extends UnitTestCase {
	var $helper = null;


	function setUp() {
		$this->Number =& new NumberHelper();
	}

	function testFormatAndCurrency() {
		$value = '100100100';

		$result = $this->Number->format($value, '#');
		$expected = '#100,100,100';
		$this->assertEqual($expected, $result);

		$result = $this->Number->format($value);
		$expected = '100,100,100';
		$this->assertEqual($expected, $result);

		$result = $this->Number->format($value, '-');
		$expected = '100-100-100';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value);
		$expected = '$100,100,100.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, '#');
		$expected = '#100,100,100.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, false);
		$expected = '100,100,100.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'USD');
		$expected = '$100,100,100.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'EUR');
		$expected = '&#8364;100.100.100,00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP');
		$expected = '&#163;100,100,100.00';
		$this->assertEqual($expected, $result);
	}

	function testCurrencyPositive() {
		$value = '100100100';

		$result = $this->Number->currency($value);
		$expected = '$100,100,100.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'USD', array('before'=> '#'));
		$expected = '#100,100,100.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, false);
		$expected = '100,100,100.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'USD');
		$expected = '$100,100,100.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'EUR');
		$expected = '&#8364;100.100.100,00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP');
		$expected = '&#163;100,100,100.00';
		$this->assertEqual($expected, $result);
	}


	function testCurrencyNegative() {
		$value = '-100100100';

		$result = $this->Number->currency($value);
		$expected = '($100,100,100.00)';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'EUR');
		$expected = '(&#8364;100.100.100,00)';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP');
		$expected = '(&#163;100,100,100.00)';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'USD', array('negative'=>'-'));
		$expected = '-$100,100,100.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'EUR', array('negative'=>'-'));
		$expected = '-&#8364;100.100.100,00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP', array('negative'=>'-'));
		$expected = '-&#163;100,100,100.00';
		$this->assertEqual($expected, $result);

	}

	function testCurrencyCentsPositive() {
		$value = '0.99';

		$result = $this->Number->currency($value, 'USD');
		$expected = '99c';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'EUR');
		$expected = '99c';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP');
		$expected = '99p';
		$this->assertEqual($expected, $result);

	}

	function testCurrencyCentsNegative() {
		$value = '-0.99';

		$result = $this->Number->currency($value, 'USD');
		$expected = '(99c)';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'EUR');
		$expected = '(99c)';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP');
		$expected = '(99p)';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'USD', array('negative'=>'-'));
		$expected = '-99c';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'EUR', array('negative'=>'-'));
		$expected = '-99c';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP', array('negative'=>'-'));
		$expected = '-99p';
		$this->assertEqual($expected, $result);

	}

	function testCurrencyZero() {
		$value = '0';

		$result = $this->Number->currency($value, 'USD');
		$expected = '$0.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'EUR');
		$expected = '&#8364;0,00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP');
		$expected = '&#163;0.00';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP', array('zero'=> 'FREE!'));
		$expected = 'FREE!';
		$this->assertEqual($expected, $result);

	}

	function testCurrencyOptions() {
		$value = '1234567.89';

		$result = $this->Number->currency($value, null, array('before'=>'GBP'));
		$expected = 'GBP1,234,567.89';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP', array('places'=>0));
		$expected = '&#163;1,234,568';
		$this->assertEqual($expected, $result);

		$result = $this->Number->currency($value, 'GBP', array('escape'=>true));
		$expected = '&amp;#163;1,234,567.89';
		$this->assertEqual($expected, $result);

	}
	function testToReadableSize() {
		$result = $this->Number->toReadableSize(0);
		$expected = '0 Bytes';
		$this->assertEqual($expected, $result);
	}

	function tearDown() {
		unset($this->Number);
	}
}

?>