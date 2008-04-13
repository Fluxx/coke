<?php
/* SVN FILE: $Id: theme.test.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
 * @since			CakePHP(tm) v 1.2.0.4206
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
uses('controller' . DS . 'controller', 'view'.DS.'theme');

class ThemePostsController extends Controller {
	var $name = 'ThemePosts';
	function index() {
		$this->set('testData', 'Some test data');
		$test2 = 'more data';
		$test3 = 'even more data';
		$this->set(compact('test2', 'test3'));
	}
}

class TestThemeView extends ThemeView {

	function renderElement($name, $params = array()) {
		return $name;
	}

	function getViewFileName($name = null) {
		return $this->_getViewFileName($name);
	}
	function getLayoutFileName($name = null) {
		return $this->_getLayoutFileName($name);
	}

	function cakeError($name, $params) {
		return $name;
	}
}

/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs
 */
class ThemeViewTest extends UnitTestCase {

	function setUp() {
		Router::reload();
		$this->Controller = new Controller();
		$this->PostsController = new ThemePostsController();
		$this->PostsController->viewPath = 'posts';
		$this->PostsController->index();
		$this->ThemeView = new ThemeView($this->PostsController);
	}

	function testPluginGetTemplate() {
		$this->Controller->plugin = 'test_plugin';
		$this->Controller->name = 'TestPlugin';
		$this->Controller->viewPath = 'test_plugin';
		$this->Controller->action = 'index';
		$this->Controller->theme = 'test_plugin_theme';

		$ThemeView = new TestThemeView($this->Controller);
		Configure::write('pluginPaths', array(TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'plugins' . DS));
		Configure::write('viewPaths', array(TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'views'. DS));

		$expected = TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'plugins' . DS .'test_plugin' . DS . 'views' . DS . 'themed' . DS . 'test_plugin_theme' . DS .'test_plugin' . DS .'index.ctp';
		$result = $ThemeView->getViewFileName('index');
		$this->assertEqual($result, $expected);

		$expected = TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'plugins' . DS .'test_plugin' . DS . 'views' . DS . 'themed' . DS . 'test_plugin_theme' . DS . 'layouts' . DS .'default.ctp';
		$result = $ThemeView->getLayoutFileName();
		$this->assertEqual($result, $expected);
	}

	function testGetTemplate() {
		$this->Controller->plugin = null;
		$this->Controller->name = 'Pages';
		$this->Controller->viewPath = 'pages';
		$this->Controller->action = 'display';
		$this->Controller->params['pass'] = array('home');

		$ThemeView = new TestThemeView($this->Controller);
		$ThemeView->theme = 'test_theme';

		Configure::write('pluginPaths', array(TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'plugins' . DS));
		Configure::write('viewPaths', array(TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'views'. DS, TEST_CAKE_CORE_INCLUDE_PATH . 'libs' . DS . 'view' . DS));

		$expected = TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'views' . DS .'pages' . DS .'home.ctp';
		$result = $ThemeView->getViewFileName('home');
		$this->assertEqual($result, $expected);

		$expected = TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'views' . DS . 'themed' . DS . 'test_theme' . DS . 'posts' . DS .'index.ctp';
		$result = $ThemeView->getViewFileName('/posts/index');
		$this->assertEqual($result, $expected);

		$expected = TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'views' . DS . 'themed' . DS . 'test_theme' . DS . 'layouts' . DS .'default.ctp';
		$result = $ThemeView->getLayoutFileName();
		$this->assertEqual($result, $expected);

		$ThemeView->layoutPath = 'rss';
		$expected = TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'views' . DS . 'layouts' . DS . 'rss' . DS . 'default.ctp';
		$result = $ThemeView->getLayoutFileName();
		$this->assertEqual($result, $expected);

		$ThemeView->layoutPath = 'email' . DS . 'html';
		$expected = TEST_CAKE_CORE_INCLUDE_PATH . 'libs' . DS . 'view' . DS . 'layouts' . DS . 'email' . DS . 'html' . DS . 'default.ctp';
		$result = $ThemeView->getLayoutFileName();
		$this->assertEqual($result, $expected);
	}

	function testMissingView() {
		$this->Controller->plugin = null;
		$this->Controller->name = 'Pages';
		$this->Controller->viewPath = 'pages';
		$this->Controller->action = 'display';
		$this->Controller->params['pass'] = array('home');

		$ThemeView = new TestThemeView($this->Controller);

		$expected = 'missingView';
		$result = $ThemeView->getViewFileName('does_not_exist');
		$this->assertEqual($result, $expected);

	}

	function testMissingLayout() {
		$this->Controller->plugin = null;
		$this->Controller->name = 'Posts';
		$this->Controller->viewPath = 'posts';
		$this->Controller->layout = 'whatever';

		$ThemeView = new TestThemeView($this->Controller);
		$expected = 'missingLayout';
		$result = $ThemeView->getLayoutFileName();
		$this->assertEqual($result, $expected);
	}

	function tearDown() {
		unset($this->ThemeView);
		unset($this->PostsController);
		unset($this->Controller);

	}
}
?>