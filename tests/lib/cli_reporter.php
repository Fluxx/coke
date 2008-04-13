<?php
/* SVN FILE: $Id: cli_reporter.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
 * @package			cake
 * @subpackage		cake.cake.tests.libs
 * @since			CakePHP(tm) v 1.2.0.4433
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
	if (! defined('ST_FAILDETAIL_SEPARATOR')) {
	    define('ST_FAILDETAIL_SEPARATOR', "->");
	}

	if (! defined('ST_FAILS_RETURN_CODE')) {
	    define('ST_FAILS_RETURN_CODE', 1);
	}

	if (version_compare(phpversion(), '4.3.0', '<') ||
	    php_sapi_name() == 'cgi') {
	    define('STDOUT', fopen('php://stdout', 'w'));
	    define('STDERR', fopen('php://stderr', 'w'));
	    register_shutdown_function(
	        create_function('', 'fclose(STDOUT); fclose(STDERR); return true;'));
	}
/**
 * Minimal command line test displayer. Writes fail details to STDERR. Returns 0
 * to the shell if all tests pass, ST_FAILS_RETURN_CODE if any test fails.
 *
 * @package    cake
 * @subpackage cake.cake.tests.libs
 */
class CLIReporter extends SimpleReporter {
	var $faildetail_separator = ST_FAILDETAIL_SEPARATOR;

	function CLIReporter($faildetail_separator = NULL) {
		$this->SimpleReporter();
		if (! is_null($faildetail_separator)) {
			$this->setFailDetailSeparator($faildetail_separator);
		}
	}

	function setFailDetailSeparator($separator) {
		$this->faildetail_separator = $separator;
	}
/**
 * Return a formatted faildetail for printing.
 */
	function &_paintTestFailDetail(&$message) {
		$buffer = '';
		$faildetail = $this->getTestList();
		array_shift($faildetail);
		$buffer .= implode($this->faildetail_separator, $faildetail);
		$buffer .= $this->faildetail_separator . "$message\n";
		return $buffer;
	}
/**
 * Paint fail faildetail to STDERR.
 */
	function paintFail($message) {
		parent::paintFail($message);
		fwrite(STDERR, 'FAIL' . $this->faildetail_separator . $this->_paintTestFailDetail($message));
	}
/**
 * Paint exception faildetail to STDERR.
 */
	function paintException($message) {
		parent::paintException($message);
		fwrite(STDERR, 'EXCEPTION' . $this->faildetail_separator . $this->_paintTestFailDetail($message));
	}
/**
 * Paint a footer with test case name, timestamp, counts of fails and exceptions.
 */
	function paintFooter($test_name) {
		$buffer = $this->getTestCaseProgress() . '/' . $this->getTestCaseCount() . ' test cases complete: ';

		if (0 < ($this->getFailCount() + $this->getExceptionCount())) {
			$buffer .= $this->getPassCount() . " passes";
			if (0 < $this->getFailCount()) {
				$buffer .= ", " . $this->getFailCount() . " fails";
			}
			if (0 < $this->getExceptionCount()) {
				$buffer .= ", " . $this->getExceptionCount() . " exceptions";
			}
			$buffer .= ".\n";
			fwrite(STDOUT, $buffer);
			exit(ST_FAILS_RETURN_CODE);
		} else {
			fwrite(STDOUT, $buffer . $this->getPassCount() . " passes.\n");
		}
	}
}
?>