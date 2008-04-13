<?php
/* SVN FILE: $Id: 0500_052f.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Case Folding Properties.
 *
 * Provides case mapping of Unicode characters for code points U+0500 through U+052F
 *
 * @see http://www.unicode.org/Public/UNIDATA/UCD.html
 * @see http://www.unicode.org/Public/UNIDATA/CaseFolding.txt
 * @see http://www.unicode.org/reports/tr21/tr21-5.html
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
 * @subpackage		cake.cake.config.unicode.casefolding
 * @since			CakePHP(tm) v 1.2.0.5691
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * The upper field is the decimal value of the upper case character
 *
 * The lower filed is an array of the decimal values that form the lower case version of a character.
 *
 *	The status field is:
 * C: common case folding, common mappings shared by both simple and full mappings.
 * F: full case folding, mappings that cause strings to grow in length. Multiple characters are separated by spaces.
 * S: simple case folding, mappings to single characters where different from F.
 * T: special case for uppercase I and dotted uppercase I
 *   - For non-Turkic languages, this mapping is normally not used.
 *   - For Turkic languages (tr, az), this mapping can be used instead of the normal mapping for these characters.
 *     Note that the Turkic mappings do not maintain canonical equivalence without additional processing.
 *     See the discussions of case mapping in the Unicode Standard for more information.
 */
$config['0500_052f'][] = array('upper' => 1280, 'status' => 'C', 'lower' => array(1281)); /* CYRILLIC CAPITAL LETTER KOMI DE */
$config['0500_052f'][] = array('upper' => 1282, 'status' => 'C', 'lower' => array(1283)); /* CYRILLIC CAPITAL LETTER KOMI DJE */
$config['0500_052f'][] = array('upper' => 1284, 'status' => 'C', 'lower' => array(1285)); /* CYRILLIC CAPITAL LETTER KOMI ZJE */
$config['0500_052f'][] = array('upper' => 1286, 'status' => 'C', 'lower' => array(1287)); /* CYRILLIC CAPITAL LETTER KOMI DZJE */
$config['0500_052f'][] = array('upper' => 1288, 'status' => 'C', 'lower' => array(1289)); /* CYRILLIC CAPITAL LETTER KOMI LJE */
$config['0500_052f'][] = array('upper' => 1290, 'status' => 'C', 'lower' => array(1291)); /* CYRILLIC CAPITAL LETTER KOMI NJE */
$config['0500_052f'][] = array('upper' => 1292, 'status' => 'C', 'lower' => array(1293)); /* CYRILLIC CAPITAL LETTER KOMI SJE */
$config['0500_052f'][] = array('upper' => 1294, 'status' => 'C', 'lower' => array(1295)); /* CYRILLIC CAPITAL LETTER KOMI TJE */
$config['0500_052f'][] = array('upper' => 1296, 'status' => 'C', 'lower' => array(1297)); /* CYRILLIC CAPITAL LETTER REVERSED ZE */
$config['0500_052f'][] = array('upper' => 1298, 'status' => 'C', 'lower' => array(1299)); /* CYRILLIC CAPITAL LETTER EL WITH HOOK */
?>