<?php
/* SVN FILE: $Id: ajax.test.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
 * @subpackage		cake.tests.cases.libs.view.helpers
 * @since			CakePHP(tm) v 1.2.0.4206
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}
uses('view'.DS.'helpers'.DS.'app_helper', 'controller'.DS.'controller', 'model'.DS.'model', 'view'.DS.'helper', 'view'.DS.'helpers'.DS.'ajax',
	'view'.DS.'helpers'.DS.'html', 'view'.DS.'helpers'.DS.'form', 'view'.DS.'helpers'.DS.'javascript');

class AjaxTestController extends Controller {
	var $name = 'AjaxTest';
	var $uses = null;
}

class PostAjaxTest extends Model {
	var $primaryKey = 'id';
	var $useTable = false;

	function schema() {
		return array(
			'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
			'name' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
			'created' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
			'updated' => array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null)
		);
	}
}

/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.view.helpers
 */
class AjaxTest extends UnitTestCase {
	function setUp() {
		Router::reload();
		$this->Ajax =& new AjaxHelper();
		$this->Ajax->Html =& new HtmlHelper();
		$this->Ajax->Form =& new FormHelper();
		$this->Ajax->Javascript =& new JavascriptHelper();
		$this->Ajax->Form->Html =& $this->Ajax->Html;
		$view =& new View(new AjaxTestController());
		ClassRegistry::addObject('view', $view);
		ClassRegistry::addObject('PostAjaxTest', new PostAjaxTest());
	}

	function testEvalScripts() {
		$result = $this->Ajax->link('Test Link', 'http://www.cakephp.org', array('id' => 'link1', 'update' => 'content', 'evalScripts' => false));
		$this->assertPattern('/^<a\s+[^<>]+>Test Link<\/a><script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote('Event.observe(\'link1\', \'click\', function(event) { new Ajax.Updater(\'content\',\'http://www.cakephp.org\', {asynchronous:true, evalScripts:false, requestHeaders:[\'X-Update\', \'content\']}) }, false);')) . '\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a\s+[^<>]*href="http:\/\/www.cakephp.org"[^<>]*>/', $result);
		$this->assertPattern('/^<a\s+[^<>]*id="link1"[^<>]*>/', $result);
		$this->assertPattern('/^<a\s+[^<>]*onclick="\s*' . str_replace('/', '\\/', preg_quote('event.returnValue = false; return false;')) . '\s*"[^<>]*>/', $result);

		$result = $this->Ajax->link('Test Link', 'http://www.cakephp.org', array('id' => 'link1', 'update' => 'content'));
		$this->assertPattern('/^<a\s+[^<>]+>Test Link<\/a><script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote('Event.observe(\'link1\', \'click\', function(event) { new Ajax.Updater(\'content\',\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, requestHeaders:[\'X-Update\', \'content\']}) }, false);')) . '\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a\s+[^<>]*href="http:\/\/www.cakephp.org"[^<>]*>/', $result);
		$this->assertPattern('/^<a\s+[^<>]*id="link1"[^<>]*>/', $result);
		$this->assertPattern('/^<a\s+[^<>]*onclick="\s*' . str_replace('/', '\\/', preg_quote('event.returnValue = false; return false;')) . '\s*"[^<>]*>/', $result);
	}

	function testAutoComplete() {
		$result = $this->Ajax->autoComplete('PostAjaxTest.title' , '/posts', array('minChars' => 2));
		$this->assertPattern('/^<input[^<>]+name="data\[PostAjaxTest\]\[title\]"[^<>]+autocomplete="off"[^<>]+\/>/', $result);
		$this->assertPattern('/<div[^<>]+id="PostAjaxTestTitle_autoComplete"[^<>]*><\/div>/', $result);
		$this->assertPattern('/<div[^<>]+class="auto_complete"[^<>]*><\/div>/', $result);
		$this->assertPattern('/<\/div>\s+<script type="text\/javascript">\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote('new Ajax.Autocompleter(\'PostAjaxTestTitle\', \'PostAjaxTestTitle_autoComplete\', \'/posts\',')) . '/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Autocompleter(\'PostAjaxTestTitle\', \'PostAjaxTestTitle_autoComplete\', \'/posts\', {minChars:2});')) . '/', $result);
		$this->assertPattern('/<\/script>$/', $result);

		$result = $this->Ajax->autoComplete('PostAjaxTest.title' , '/posts', array('paramName' => 'parameter'));
		$this->assertPattern('/^<input[^<>]+name="data\[PostAjaxTest\]\[title\]"[^<>]+autocomplete="off"[^<>]+\/>/', $result);
		$this->assertPattern('/<div[^<>]+id="PostAjaxTestTitle_autoComplete"[^<>]*><\/div>/', $result);
		$this->assertPattern('/<div[^<>]+class="auto_complete"[^<>]*><\/div>/', $result);
		$this->assertPattern('/<\/div>\s+<script type="text\/javascript">\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote('new Ajax.Autocompleter(\'PostAjaxTestTitle\', \'PostAjaxTestTitle_autoComplete\', \'/posts\',')) . '/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Autocompleter(\'PostAjaxTestTitle\', \'PostAjaxTestTitle_autoComplete\', \'/posts\', {paramName:\'parameter\'});')) . '/', $result);
		$this->assertPattern('/<\/script>$/', $result);

		$result = $this->Ajax->autoComplete('PostAjaxTest.title' , '/posts', array('paramName' => 'parameter', 'updateElement' => 'elementUpdated', 'afterUpdateElement' => 'function (input, element) { alert("updated"); }'));
		$this->assertPattern('/^<input[^<>]+name="data\[PostAjaxTest\]\[title\]"[^<>]+autocomplete="off"[^<>]+\/>/', $result);
		$this->assertPattern('/<div[^<>]+id="PostAjaxTestTitle_autoComplete"[^<>]*><\/div>/', $result);
		$this->assertPattern('/<div[^<>]+class="auto_complete"[^<>]*><\/div>/', $result);
		$this->assertPattern('/<\/div>\s+<script type="text\/javascript">\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote('new Ajax.Autocompleter(\'PostAjaxTestTitle\', \'PostAjaxTestTitle_autoComplete\', \'/posts\',')) . '/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Autocompleter(\'PostAjaxTestTitle\', \'PostAjaxTestTitle_autoComplete\', \'/posts\', {paramName:\'parameter\', updateElement:elementUpdated, afterUpdateElement:function (input, element) { alert("updated"); }});')) . '/', $result);
		$this->assertPattern('/<\/script>$/', $result);

		$result = $this->Ajax->autoComplete('PostAjaxTest.title' , '/posts', array('callback' => 'function (input, queryString) { alert("requesting"); }'));
		$this->assertPattern('/^<input[^<>]+name="data\[PostAjaxTest\]\[title\]"[^<>]+autocomplete="off"[^<>]+\/>/', $result);
		$this->assertPattern('/<div[^<>]+id="PostAjaxTestTitle_autoComplete"[^<>]*><\/div>/', $result);
		$this->assertPattern('/<div[^<>]+class="auto_complete"[^<>]*><\/div>/', $result);
		$this->assertPattern('/<\/div>\s+<script type="text\/javascript">\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote('new Ajax.Autocompleter(\'PostAjaxTestTitle\', \'PostAjaxTestTitle_autoComplete\', \'/posts\',')) . '/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Autocompleter(\'PostAjaxTestTitle\', \'PostAjaxTestTitle_autoComplete\', \'/posts\', {callback:function (input, queryString) { alert("requesting"); }});')) . '/', $result);
		$this->assertPattern('/<\/script>$/', $result);
	}

	function testAsynchronous() {
		$result = $this->Ajax->link('Test Link', '/', array('id' => 'link1', 'update' => 'content', 'type' => 'synchronous'));
		$this->assertPattern('/^<a\s+[^<>]+>Test Link<\/a><script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote('Event.observe(\'link1\', \'click\', function(event) { new Ajax.Updater(\'content\',\'/\', {asynchronous:false, evalScripts:true, requestHeaders:[\'X-Update\', \'content\']}) }, false);')) . '\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a\s+[^<>]*href="\/"[^<>]*>/', $result);
		$this->assertPattern('/^<a\s+[^<>]*id="link1"[^<>]*>/', $result);
		$this->assertPattern('/^<a\s+[^<>]*onclick="\s*' . str_replace('/', '\\/', preg_quote('event.returnValue = false; return false;')) . '\s*"[^<>]*>/', $result);
	}

	function testDraggable() {
		$result = $this->Ajax->drag('id', array('handle' => 'other_id'));
		$expected = 'new Draggable(\'id\', {handle:\'other_id\'});';
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote($expected)) . '\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
	}

	function testDroppable() {
		$result = $this->Ajax->drop('droppable', array('accept' => 'crap'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*Droppables.add\(\'droppable\', {accept:\'crap\'}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->dropRemote('droppable', array('accept' => 'crap'), array('url' => '/posts'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*Droppables.add\(\'droppable\', {accept:\'crap\', onDrop:function\(element, droppable, event\) {.+}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'/posts\', {asynchronous:true, evalScripts:true})')) . '/', $result);

		$result = $this->Ajax->dropRemote('droppable', array('accept' => array('crap1', 'crap2')), array('url' => '/posts'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*Droppables.add\(\'droppable\', {accept:\["crap1", "crap2"\], onDrop:function\(element, droppable, event\) {.+}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'/posts\', {asynchronous:true, evalScripts:true})')) . '/', $result);

		$result = $this->Ajax->dropRemote('droppable', array('accept' => 'crap'), array('url' => '/posts', 'with' => '{drag_id:element.id,drop_id:dropon.id,event:event.whatever_you_want}'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*Droppables.add\(\'droppable\', {accept:\'crap\', onDrop:function\(element, droppable, event\) {.+}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'/posts\', {asynchronous:true, evalScripts:true, parameters:{drag_id:element.id,drop_id:dropon.id,event:event.whatever_you_want}})')) . '/', $result);
	}

	function testForm() {
		$result = $this->Ajax->form('showForm', 'post', array('model' => 'Form', 'url' => array('action' => 'showForm', 'controller' => 'forms'), 'update' => 'form_box'));
		$this->assertNoPattern('/model=/', $result);
	}

	function testSortable() {
		$result = $this->Ajax->sortable('ull', array('constraint'=>false, 'ghosting'=>true));
		$expected = 'Sortable.create(\'ull\', {constraint:false, ghosting:true});';
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote($expected)) . '\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->sortable('ull', array('constraint'=>'false', 'ghosting'=>'true'));
		$expected = 'Sortable.create(\'ull\', {constraint:false, ghosting:true});';
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote($expected)) . '\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->sortable('ull', array('constraint'=>'false', 'ghosting'=>'true', 'update' => 'myId'));
		$expected = 'Sortable.create(\'ull\', {constraint:false, ghosting:true, update:\'myId\'});';
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote($expected)) . '\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->sortable('faqs', array('url'=>'http://www.cakephp.org',
			'update' => 'faqs',
			'tag' => 'tbody',
			'handle' => 'grip',
			'before' => 'Element.hide(\'message\')',
			'complete' => 'Element.show(\'message\');'));
		$expected = 'Sortable.create(\'faqs\', {update:\'faqs\', tag:\'tbody\', handle:\'grip\', onUpdate:function(sortable) {Element.hide(\'message\'); new Ajax.Updater(\'faqs\',\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, onComplete:function(request, json) {Element.show(\'message\');}, parameters:Sortable.serialize(\'faqs\'), requestHeaders:[\'X-Update\', \'faqs\']})}});';
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*' . str_replace('/', '\\/', preg_quote($expected)) . '\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
	}

	function testSubmitWithIndicator() {
		$result = $this->Ajax->submit('Add', array('div' => false, 'url' => "http://www.cakephp.org", 'indicator' => 'loading', 'loading' => "doSomething()", 'complete' => 'doSomethingElse() '));
		$this->assertPattern('/onLoading:function\(request\) {doSomething\(\);\s+Element.show\(\'loading\'\);}/', $result);
		$this->assertPattern('/onComplete:function\(request, json\) {doSomethingElse\(\) ;\s+Element.hide\(\'loading\'\);}/', $result);
	}

	function testLink() {
		$result = $this->Ajax->link('Ajax Link', 'http://www.cakephp.org/downloads');
		$this->assertPattern('/^<a[^<>]+>Ajax Link<\/a><script [^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*[^<>]+\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a[^<>]+href="http:\/\/www.cakephp.org\/downloads"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+id="link\d+"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+onclick="\s*event.returnValue = false;\s*return false;"[^<>]*>/', $result);
		$this->assertPattern('/<script[^<>]+type="text\/javascript"[^<>]*>/', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/Event.observe\(\'link\d+\',\s*\'click\',\s*function\(event\)\s*{.+},\s*false\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/function\(event\)\s*{\s*new Ajax\.Request\(\'http:\/\/www.cakephp.org\/downloads\',\s*{asynchronous:true, evalScripts:true}\)\s*},\s*false\);/', $result);

		$result = $this->Ajax->link('Ajax Link', 'http://www.cakephp.org/downloads', array('confirm' => 'Are you sure & positive?'));
		$this->assertPattern('/^<a[^<>]+>Ajax Link<\/a><script [^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*[^<>]+\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a[^<>]+href="http:\/\/www.cakephp.org\/downloads"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+id="link\d+"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+onclick="\s*event.returnValue = false;\s*return false;"[^<>]*>/', $result);
		$this->assertPattern('/<script[^<>]+type="text\/javascript"[^<>]*>/', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/Event.observe\(\'link\d+\',\s*\'click\',\s*function\(event\)\s*{.+},\s*false\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/function\(event\)\s*{\s*if \(confirm\(\'Are you sure & positive\?\'\)\) {\s*new Ajax\.Request\(\'http:\/\/www.cakephp.org\/downloads\',\s*{asynchronous:true, evalScripts:true}\);\s*}\s*else\s*{\s*event.returnValue = false;\s*return false;\s*}\s*},\s*false\);/', $result);

		$result = $this->Ajax->link('Ajax Link', 'http://www.cakephp.org/downloads', array('update' => 'myDiv'));
		$this->assertPattern('/^<a[^<>]+>Ajax Link<\/a><script [^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*[^<>]+\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a[^<>]+href="http:\/\/www.cakephp.org\/downloads"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+id="link\d+"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+onclick="\s*event.returnValue = false;\s*return false;"[^<>]*>/', $result);
		$this->assertPattern('/<script[^<>]+type="text\/javascript"[^<>]*>/', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/Event.observe\(\'link\d+\',\s*\'click\',\s*function\(event\)\s*{.+},\s*false\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/function\(event\)\s*{\s*new Ajax\.Updater\(\'myDiv\',\s*\'http:\/\/www.cakephp.org\/downloads\',\s*{asynchronous:true, evalScripts:true, requestHeaders:\[\'X-Update\', \'myDiv\'\]}\)\s*},\s*false\);/', $result);

		$result = $this->Ajax->link('Ajax Link', 'http://www.cakephp.org/downloads', array('update' => 'myDiv', 'id' => 'myLink'));
		$this->assertPattern('/^<a[^<>]+>Ajax Link<\/a><script [^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*[^<>]+\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a[^<>]+href="http:\/\/www.cakephp.org\/downloads"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+id="myLink"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+onclick="\s*event.returnValue = false;\s*return false;"[^<>]*>/', $result);
		$this->assertPattern('/<script[^<>]+type="text\/javascript"[^<>]*>/', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/Event.observe\(\'myLink\',\s*\'click\',\s*function\(event\)\s*{.+},\s*false\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/function\(event\)\s*{\s*new Ajax\.Updater\(\'myDiv\',\s*\'http:\/\/www.cakephp.org\/downloads\',\s*{asynchronous:true, evalScripts:true, requestHeaders:\[\'X-Update\', \'myDiv\'\]}\)\s*},\s*false\);/', $result);

		$result = $this->Ajax->link('Ajax Link', 'http://www.cakephp.org/downloads', array('update' => 'myDiv', 'id' => 'myLink', 'complete' => 'myComplete();'));
		$this->assertPattern('/^<a[^<>]+>Ajax Link<\/a><script [^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*[^<>]+\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a[^<>]+href="http:\/\/www.cakephp.org\/downloads"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+id="myLink"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+onclick="\s*event.returnValue = false;\s*return false;"[^<>]*>/', $result);
		$this->assertPattern('/<script[^<>]+type="text\/javascript"[^<>]*>/', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/Event.observe\(\'myLink\',\s*\'click\',\s*function\(event\)\s*{.+},\s*false\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/function\(event\)\s*{\s*new Ajax\.Updater\(\'myDiv\',\s*\'http:\/\/www.cakephp.org\/downloads\',\s*{asynchronous:true, evalScripts:true, onComplete:function\(request, json\) {myComplete\(\);}, requestHeaders:\[\'X-Update\', \'myDiv\'\]}\)\s*},\s*false\);/', $result);

		$result = $this->Ajax->link('Ajax Link', 'http://www.cakephp.org/downloads', array('update' => 'myDiv', 'id' => 'myLink', 'loading' => 'myLoading();', 'complete' => 'myComplete();'));
		$this->assertPattern('/^<a[^<>]+>Ajax Link<\/a><script [^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*[^<>]+\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a[^<>]+href="http:\/\/www.cakephp.org\/downloads"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+id="myLink"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+onclick="\s*event.returnValue = false;\s*return false;"[^<>]*>/', $result);
		$this->assertPattern('/<script[^<>]+type="text\/javascript"[^<>]*>/', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/Event.observe\(\'myLink\',\s*\'click\',\s*function\(event\)\s*{.+},\s*false\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/function\(event\)\s*{\s*new Ajax\.Updater\(\'myDiv\',\s*\'http:\/\/www.cakephp.org\/downloads\',\s*{asynchronous:true, evalScripts:true, onComplete:function\(request, json\) {myComplete\(\);}, onLoading:function\(request\) {myLoading\(\);}, requestHeaders:\[\'X-Update\', \'myDiv\'\]}\)\s*},\s*false\);/', $result);

		$result = $this->Ajax->link('Ajax Link', 'http://www.cakephp.org/downloads', array('update' => 'myDiv', 'encoding' => 'utf-8'));
		$this->assertPattern('/^<a[^<>]+>Ajax Link<\/a><script [^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*[^<>]+\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a[^<>]+href="http:\/\/www.cakephp.org\/downloads"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+onclick="\s*event.returnValue = false;\s*return false;"[^<>]*>/', $result);
		$this->assertPattern('/<script[^<>]+type="text\/javascript"[^<>]*>/', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/Event.observe\(\'\w+\',\s*\'click\',\s*function\(event\)\s*{.+},\s*false\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/function\(event\)\s*{\s*new Ajax\.Updater\(\'myDiv\',\s*\'http:\/\/www.cakephp.org\/downloads\',\s*{asynchronous:true, evalScripts:true, encoding:\'utf-8\', requestHeaders:\[\'X-Update\', \'myDiv\'\]}\)\s*},\s*false\);/', $result);

		$result = $this->Ajax->link('Ajax Link', 'http://www.cakephp.org/downloads', array('update' => 'myDiv', 'success' => 'success();'));
		$this->assertPattern('/^<a[^<>]+>Ajax Link<\/a><script [^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*[^<>]+\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a[^<>]+href="http:\/\/www.cakephp.org\/downloads"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+onclick="\s*event.returnValue = false;\s*return false;"[^<>]*>/', $result);
		$this->assertPattern('/<script[^<>]+type="text\/javascript"[^<>]*>/', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/Event.observe\(\'\w+\',\s*\'click\',\s*function\(event\)\s*{.+},\s*false\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/function\(event\)\s*{\s*new Ajax\.Updater\(\'myDiv\',\s*\'http:\/\/www.cakephp.org\/downloads\',\s*{asynchronous:true, evalScripts:true, onSuccess:function\(request\) {success\(\);}, requestHeaders:\[\'X-Update\', \'myDiv\'\]}\)\s*},\s*false\);/', $result);

		$result = $this->Ajax->link('Ajax Link', 'http://www.cakephp.org/downloads', array('update' => 'myDiv', 'failure' => 'failure();'));
		$this->assertPattern('/^<a[^<>]+>Ajax Link<\/a><script [^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*[^<>]+\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/^<a[^<>]+href="http:\/\/www.cakephp.org\/downloads"[^<>]*>/', $result);
		$this->assertPattern('/^<a[^<>]+onclick="\s*event.returnValue = false;\s*return false;"[^<>]*>/', $result);
		$this->assertPattern('/<script[^<>]+type="text\/javascript"[^<>]*>/', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/Event.observe\(\'\w+\',\s*\'click\',\s*function\(event\)\s*{.+},\s*false\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/function\(event\)\s*{\s*new Ajax\.Updater\(\'myDiv\',\s*\'http:\/\/www.cakephp.org\/downloads\',\s*{asynchronous:true, evalScripts:true, onFailure:function\(request\) {failure\(\);}, requestHeaders:\[\'X-Update\', \'myDiv\'\]}\)\s*},\s*false\);/', $result);
	}

	function testRemoteTimer() {
		$result = $this->Ajax->remoteTimer(array('url' => 'http://www.cakephp.org'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new PeriodicalExecuter\(function\(\) {.+}, 10\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true})')) . '/', $result);

		$result = $this->Ajax->remoteTimer(array('url' => 'http://www.cakephp.org', 'frequency' => 25));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new PeriodicalExecuter\(function\(\) {.+}, 25\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true})')) . '/', $result);

		$result = $this->Ajax->remoteTimer(array('url' => 'http://www.cakephp.org', 'complete' => 'complete();'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new PeriodicalExecuter\(function\(\) {.+}, 10\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, onComplete:function(request, json) {complete();}})')) . '/', $result);

		$result = $this->Ajax->remoteTimer(array('url' => 'http://www.cakephp.org', 'complete' => 'complete();', 'create' => 'create();'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new PeriodicalExecuter\(function\(\) {.+}, 10\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, onComplete:function(request, json) {complete();}, onCreate:function(request, xhr) {create();}})')) . '/', $result);

		$result = $this->Ajax->remoteTimer(array('url' => 'http://www.cakephp.org', 'exception' => 'alert(exception);'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new PeriodicalExecuter\(function\(\) {.+}, 10\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, onException:function(request, exception) {alert(exception);}})')) . '/', $result);

		$result = $this->Ajax->remoteTimer(array('url' => 'http://www.cakephp.org', 'contentType' => 'application/x-www-form-urlencoded'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new PeriodicalExecuter\(function\(\) {.+}, 10\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, contentType:\'application/x-www-form-urlencoded\'})')) . '/', $result);

		$result = $this->Ajax->remoteTimer(array('url' => 'http://www.cakephp.org', 'method' => 'get', 'encoding' => 'utf-8'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new PeriodicalExecuter\(function\(\) {.+}, 10\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, method:\'get\', encoding:\'utf-8\'})')) . '/', $result);

		$result = $this->Ajax->remoteTimer(array('url' => 'http://www.cakephp.org', 'postBody' => 'var1=value1'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new PeriodicalExecuter\(function\(\) {.+}, 10\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, postBody:\'var1=value1\'})')) . '/', $result);
	}

	function testObserveField() {
		$result = $this->Ajax->observeField('field', array('url' => 'http://www.cakephp.org'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new Form.Element.EventObserver\(\'field\', function\(element, value\) {.+}\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, parameters:Form.Element.serialize(\'field\')})')) . '/', $result);

		$result = $this->Ajax->observeField('field', array('url' => 'http://www.cakephp.org', 'frequency' => 15));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new Form.Element.Observer\(\'field\', 15, function\(element, value\) {.+}\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, parameters:Form.Element.serialize(\'field\')})')) . '/', $result);

		$result = $this->Ajax->observeField('field', array('url' => 'http://www.cakephp.org', 'update' => 'divId'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new Form.Element.EventObserver\(\'field\', function\(element, value\) {.+}\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Updater(\'divId\',\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, parameters:Form.Element.serialize(\'field\'), requestHeaders:[\'X-Update\', \'divId\']})')) . '/', $result);

		$result = $this->Ajax->observeField('field', array('url' => 'http://www.cakephp.org', 'update' => 'divId', 'with' => 'Form.Element.serialize(\'otherField\')'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new Form.Element.EventObserver\(\'field\', function\(element, value\) {.+}\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Updater(\'divId\',\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, parameters:Form.Element.serialize(\'otherField\'), requestHeaders:[\'X-Update\', \'divId\']})')) . '/', $result);
	}

	function testObserveForm() {
		$result = $this->Ajax->observeForm('form', array('url' => 'http://www.cakephp.org'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new Form.EventObserver\(\'form\', function\(element, value\) {.+}\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, parameters:Form.serialize(\'form\')})')) . '/', $result);

		$result = $this->Ajax->observeForm('form', array('url' => 'http://www.cakephp.org', 'frequency' => 15));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new Form.Observer\(\'form\', 15, function\(element, value\) {.+}\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Request(\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, parameters:Form.serialize(\'form\')})')) . '/', $result);

		$result = $this->Ajax->observeForm('form', array('url' => 'http://www.cakephp.org', 'update' => 'divId'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new Form.EventObserver\(\'form\', function\(element, value\) {.+}\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Updater(\'divId\',\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, parameters:Form.serialize(\'form\'), requestHeaders:[\'X-Update\', \'divId\']})')) . '/', $result);

		$result = $this->Ajax->observeForm('form', array('url' => 'http://www.cakephp.org', 'update' => 'divId', 'with' => 'Form.serialize(\'otherForm\')'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*new Form.EventObserver\(\'form\', function\(element, value\) {.+}\)\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
		$this->assertPattern('/' . str_replace('/', '\\/', preg_quote('new Ajax.Updater(\'divId\',\'http://www.cakephp.org\', {asynchronous:true, evalScripts:true, parameters:Form.serialize(\'otherForm\'), requestHeaders:[\'X-Update\', \'divId\']})')) . '/', $result);
	}

	function testSlider() {
		$result = $this->Ajax->slider('sliderId', 'trackId');
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*var sliderId = new Control.Slider\(\'sliderId\', \'trackId\', {}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->slider('sliderId', 'trackId', array('axis' => 'vertical'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*var sliderId = new Control.Slider\(\'sliderId\', \'trackId\', {axis:\'vertical\'}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->slider('sliderId', 'trackId', array('axis' => 'vertical', 'minimum' => 60, 'maximum' => 288, 'alignX' => -28, 'alignY' => -5, 'disabled' => true));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*var sliderId = new Control.Slider\(\'sliderId\', \'trackId\', {axis:\'vertical\', minimum:60, maximum:288, alignX:-28, alignY:-5, disabled:true}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->slider('sliderId', 'trackId', array('change' => 'alert(\'changed\');'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*var sliderId = new Control.Slider\(\'sliderId\', \'trackId\', {onChange:function\(value\) {alert\(\'changed\'\);}}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->slider('sliderId', 'trackId', array('change' => 'alert(\'changed\');', 'slide' => 'alert(\'sliding\');'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*var sliderId = new Control.Slider\(\'sliderId\', \'trackId\', {onChange:function\(value\) {alert\(\'changed\'\);}, onSlide:function\(value\) {alert\(\'sliding\'\);}}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->slider('sliderId', 'trackId', array('values' => array(10, 20, 30)));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*var sliderId = new Control.Slider\(\'sliderId\', \'trackId\', {values:\[10, 20, 30\]}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);

		$result = $this->Ajax->slider('sliderId', 'trackId', array('range' => '$R(10, 30)'));
		$this->assertPattern('/^<script[^<>]+type="text\/javascript"[^<>]*>.+<\/script>$/s', $result);
		$this->assertNoPattern('/<script[^<>]+[^type]=[^<>]*>/', $result);
		$this->assertPattern('/^<script[^<>]+>\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*var sliderId = new Control.Slider\(\'sliderId\', \'trackId\', {range:\$R\(10, 30\)}\);\s*' . str_replace('/', '\\/', preg_quote('//]]>')) . '\s*<\/script>$/', $result);
	}

	function tearDown() {
		unset($this->Ajax);
	}
}
?>