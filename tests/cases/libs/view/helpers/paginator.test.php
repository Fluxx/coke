<?php
/* SVN FILE: $Id: paginator.test.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
uses('view'.DS.'helpers'.DS.'app_helper', 'view'.DS.'helper', 'view'.DS.'helpers'.DS.'html', 'view'.DS.'helpers'.DS.'form',
	'view'.DS.'helpers'.DS.'ajax', 'view'.DS.'helpers'.DS.'javascript', 'view'.DS.'helpers'.DS.'paginator');
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.view.helpers
 */
class PaginatorTest extends UnitTestCase {

	function setUp() {
		$this->Paginator = new PaginatorHelper();
		$this->Paginator->params['paging'] = array(
			'Article' => array(
				'current' => 9,
				'count' => 62,
				'prevPage' => false,
				'nextPage' => true,
				'pageCount' => 7,
				'defaults' => array(
					'order' => 'Article.date ASC',
					'limit' => 9,
					'conditions' => array()
                ),
				'options' => array(
					'order' => 'Article.date ASC',
					'limit' => 9,
					'page' => 1,
					'conditions' => array()
				)
			)
		);
		$this->Paginator->Html =& new HtmlHelper();
		$this->Paginator->Ajax =& new AjaxHelper();
		$this->Paginator->Ajax->Html =& new HtmlHelper();
		$this->Paginator->Ajax->Javascript =& new JavascriptHelper();
		$this->Paginator->Ajax->Form =& new FormHelper();

		Configure::write('Routing.admin', '');
		Router::reload();
	}

	function testHasPrevious() {
		$this->assertIdentical($this->Paginator->hasPrev(), false);
		$this->Paginator->params['paging']['Article']['prevPage'] = true;
		$this->assertIdentical($this->Paginator->hasPrev(), true);
		$this->Paginator->params['paging']['Article']['prevPage'] = false;
	}

	function testHasNext() {
		$this->assertIdentical($this->Paginator->hasNext(), true);
		$this->Paginator->params['paging']['Article']['nextPage'] = false;
		$this->assertIdentical($this->Paginator->hasNext(), false);
		$this->Paginator->params['paging']['Article']['nextPage'] = true;
	}

	function testDisabledLink() {
		$this->Paginator->params['paging']['Article']['nextPage'] = false;
		$this->Paginator->params['paging']['Article']['page'] = 1;
		$result = $this->Paginator->next('Next', array(), true);
		$expected = '<div>Next</div>';
		$this->assertEqual($result, $expected);
	}

	function testSortLinks() {
		Router::reload();
		Router::parse('/');
		Router::setRequestInfo(array(
			array('plugin' => null, 'controller' => 'accounts', 'action' => 'index', 'pass' => array(), 'form' => array(), 'url' => array('url' => 'accounts/', 'mod_rewrite' => 'true'), 'bare' => 0),
			array('plugin' => null, 'controller' => null, 'action' => null, 'base' => '/officespace', 'here' => '/officespace/accounts/', 'webroot' => '/officespace/', 'passedArgs' => array())
		));
		$this->Paginator->options(array('url' => array('param')));
		$result = $this->Paginator->sort('title');
		$this->assertPattern('/\/accounts\/index\/param\/page:1\/sort:title\/direction:asc"\s*>Title<\/a>$/', $result);

		$result = $this->Paginator->sort('date');
		$this->assertPattern('/\/accounts\/index\/param\/page:1\/sort:date\/direction:desc"\s*>Date<\/a>$/', $result);

		$result = $this->Paginator->numbers(array('modulus'=> '2', 'url'=> array('controller'=>'projects', 'action'=>'sort'),'update'=>'list'));
		$this->assertPattern('/\/projects\/sort\/page:2/', $result);
		$this->assertPattern('/<script type="text\/javascript">\s*' . str_replace('/', '\\/', preg_quote('//<![CDATA[')) . '\s*Event.observe/', $result);
	}

	function testSortAdminLinks() {
		Router::reload();
		Configure::write('Routing.admin', 'admin');
		Router::setRequestInfo(array(
			array('plugin' => null, 'controller' => 'test', 'action' => 'admin_index', 'pass' => array(), 'prefix' => 'admin', 'admin' => true, 'form' => array(), 'url' => array('url' => 'admin/test'), 'bare' => 0, 'webservices' => null),
			array ( 'plugin' => null, 'controller' => null, 'action' => null, 'base' => '', 'here' => '/admin/test', 'webroot' => '/')
		));
		Router::parse('/');
		$this->Paginator->options(array('url' => array('param')));
		$result = $this->Paginator->sort('title');
		$this->assertPattern('/\/admin\/test\/index\/param\/page:1\/sort:title\/direction:asc"\s*>Title<\/a>$/', $result);
	}

	function testUrlGeneration() {
		$result = $this->Paginator->sort('controller');
		$this->assertPattern('/\/page:1\//', $result);
		$this->assertPattern('/\/sort:controller\//', $result);

		$result = $this->Paginator->url();
		$this->assertEqual($result, '/index/page:1');

		$this->Paginator->params['paging']['Article']['options']['page'] = 2;
		$result = $this->Paginator->url();
		$this->assertEqual($result, '/index/page:2');
	}

	function testPagingLinks() {
		$this->Paginator->params['paging'] = array('Client' => array(
			'page' => 1, 'current' => 3, 'count' => 13, 'prevPage' => false, 'nextPage' => true, 'pageCount' => 5,
			'defaults' => array('limit' => 3, 'step' => 1, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()),
			'options' => array('page' => 1, 'limit' => 3, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()))
		);
		$result = $this->Paginator->prev('<< Previous', null, null, array('class' => 'disabled'));
		$expected = '<div class="disabled">&lt;&lt; Previous</div>';
		$this->assertEqual($result, $expected);

		$this->Paginator->params['paging']['Client']['page'] = 2;
		$this->Paginator->params['paging']['Client']['prevPage'] = true;
		$result = $this->Paginator->prev('<< Previous', null, null, array('class' => 'disabled'));
		$this->assertPattern('/^<a[^<>]+>&lt;&lt; Previous<\/a>$/', $result);
		$this->assertPattern('/href="\/index\/page:1"/', $result);

		$result = $this->Paginator->next('Next');
		$this->assertPattern('/^<a[^<>]+>Next<\/a>$/', $result);
		$this->assertPattern('/href="\/index\/page:3"/', $result);
	}

	function testGenericLinks() {
		$result = $this->Paginator->link('Sort by title on page 5', array('sort' => 'title', 'page' => 5, 'direction' => 'desc'));
		$this->assertPattern('/^<a href=".+"[^<>]*>Sort by title on page 5<\/a>$/', $result);
		$this->assertPattern('/\/page:5/', $result);
		$this->assertPattern('/\/sort:title/', $result);
		$this->assertPattern('/\/direction:desc/', $result);

		$this->Paginator->params['paging']['Article']['options']['page'] = 2;
		$result = $this->Paginator->link('Sort by title', array('sort' => 'title', 'direction' => 'desc'));
		$this->assertPattern('/^<a href=".+"[^<>]*>Sort by title<\/a>$/', $result);
		$this->assertPattern('/\/page:2/', $result);
		$this->assertPattern('/\/sort:title/', $result);
		$this->assertPattern('/\/direction:desc/', $result);
	}

	function testNumbers() {
		$this->Paginator->params['paging'] = array('Client' => array(
			'page' => 8, 'current' => 3, 'count' => 30, 'prevPage' => false, 'nextPage' => 2, 'pageCount' => 15,
			'defaults' => array('limit' => 3, 'step' => 1, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()),
			'options' => array('page' => 1, 'limit' => 3, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()))
		);
		$result = $this->Paginator->numbers();
		$expected = '<span><a href="/index/page:4">4</a></span> | <span><a href="/index/page:5">5</a></span> | <span><a href="/index/page:6">6</a></span> | <span><a href="/index/page:7">7</a></span> | <span class="current">8</span> | <span><a href="/index/page:9">9</a></span> | <span><a href="/index/page:10">10</a></span> | <span><a href="/index/page:11">11</a></span> | <span><a href="/index/page:12">12</a></span>';
		$this->assertEqual($result, $expected);


		$result = $this->Paginator->numbers(true);
		$expected = '<span><a href="/index/page:1">first</a></span> | <span><a href="/index/page:4">4</a></span> | <span><a href="/index/page:5">5</a></span> | <span><a href="/index/page:6">6</a></span> | <span><a href="/index/page:7">7</a></span> | <span class="current">8</span> | <span><a href="/index/page:9">9</a></span> | <span><a href="/index/page:10">10</a></span> | <span><a href="/index/page:11">11</a></span> | <span><a href="/index/page:12">12</a></span> | <span><a href="/index/page:15">last</a></span>';
		$this->assertEqual($result, $expected);

		$this->Paginator->params['paging'] = array('Client' => array(
			'page' => 1, 'current' => 3, 'count' => 30, 'prevPage' => false, 'nextPage' => 2, 'pageCount' => 15,
			'defaults' => array('limit' => 3, 'step' => 1, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()),
			'options' => array('page' => 1, 'limit' => 3, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()))
		);
		$result = $this->Paginator->numbers();
		$expected = '<span class="current">1</span> | <span><a href="/index/page:2">2</a></span> | <span><a href="/index/page:3">3</a></span> | <span><a href="/index/page:4">4</a></span> | <span><a href="/index/page:5">5</a></span> | <span><a href="/index/page:6">6</a></span> | <span><a href="/index/page:7">7</a></span> | <span><a href="/index/page:8">8</a></span> | <span><a href="/index/page:9">9</a></span>';
		$this->assertEqual($result, $expected);

		$this->Paginator->params['paging'] = array('Client' => array(
			'page' => 14, 'current' => 3, 'count' => 30, 'prevPage' => false, 'nextPage' => 2, 'pageCount' => 15,
			'defaults' => array('limit' => 3, 'step' => 1, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()),
			'options' => array('page' => 1, 'limit' => 3, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()))
		);
		$result = $this->Paginator->numbers();
		$expected = '<span><a href="/index/page:7">7</a></span> | <span><a href="/index/page:8">8</a></span> | <span><a href="/index/page:9">9</a></span> | <span><a href="/index/page:10">10</a></span> | <span><a href="/index/page:11">11</a></span> | <span><a href="/index/page:12">12</a></span> | <span><a href="/index/page:13">13</a></span> | <span class="current">14</span> | <span><a href="/index/page:15">15</a></span>';
		$this->assertEqual($result, $expected);

		$this->Paginator->params['paging'] = array('Client' => array(
			'page' => 1, 'current' => 3, 'count' => 30, 'prevPage' => false, 'nextPage' => 2, 'pageCount' => 15,
			'defaults' => array('limit' => 3, 'step' => 1, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()),
			'options' => array('page' => 1, 'limit' => 3, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()))
		);

		$result = $this->Paginator->first();
		$expected = '';
		$this->assertEqual($result, $expected);


		$this->Paginator->params['paging'] = array('Client' => array(
			'page' => 4, 'current' => 3, 'count' => 30, 'prevPage' => false, 'nextPage' => 2, 'pageCount' => 15,
			'defaults' => array('limit' => 3, 'step' => 1, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()),
			'options' => array('page' => 1, 'limit' => 3, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()))
		);

		$result = $this->Paginator->first();
		$expected = '<span><a href="/index/page:1">&lt;&lt; first</a></span>';
		$this->assertEqual($result, $expected);


		$result = $this->Paginator->last();
		$expected = '<span><a href="/index/page:15">last &gt;&gt;</a></span>';
		$this->assertEqual($result, $expected);

		$result = $this->Paginator->last(1);
		$expected = '...<span><a href="/index/page:15">15</a></span>';
		$this->assertEqual($result, $expected);

		$result = $this->Paginator->last(2);
		$expected = '...<span><a href="/index/page:14">14</a></span> | <span><a href="/index/page:15">15</a></span>';
		$this->assertEqual($result, $expected);

		$this->Paginator->params['paging'] = array('Client' => array(
			'page' => 15, 'current' => 3, 'count' => 30, 'prevPage' => false, 'nextPage' => 2, 'pageCount' => 15,
			'defaults' => array('limit' => 3, 'step' => 1, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()),
			'options' => array('page' => 1, 'limit' => 3, 'order' => array('Client.name' => 'DESC'), 'conditions' => array()))
		);
		$result = $this->Paginator->last();
		$expected = '';
		$this->assertEqual($result, $expected);
	}

	function tearDown() {
		unset($this->Paginator);
	}
}
?>