<?php
/* SVN FILE: $Id: class_registry.test.php 6311 2008-01-02 06:33:52Z phpnut $ */
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
uses('class_registry');
class ClassRegisterModel extends CakeTestModel {
	var $useTable = false;
}

class RegisterArticle extends ClassRegisterModel {
	var $name = 'RegisterArticle';
}
class RegisterArticleFeatured extends ClassRegisterModel {
	var $name = 'RegisterArticlFeatured';
}

class RegisterArticleTag extends ClassRegisterModel {
	var $name = 'RegisterArticlTag';
}

class ClassRegistryTest extends UnitTestCase {
	function testAddModel() {
		if (PHP5) {
			$Tag = ClassRegistry::init('RegisterArticleTag');
		} else {
			$Tag =& ClassRegistry::init('RegisterArticleTag');
		}
		$this->assertTrue(is_a($Tag, 'RegisterArticleTag'));

		$TagCopy = ClassRegistry::isKeySet('RegisterArticleTag');
		$this->assertTrue($TagCopy);

		$Tag->name = 'SomeNewName';

		if (PHP5) {
			$TagCopy = ClassRegistry::getObject('RegisterArticleTag');
		} else {
			$TagCopy =& ClassRegistry::getObject('RegisterArticleTag');
		}

		$this->assertTrue(is_a($TagCopy, 'RegisterArticleTag'));
		$this->assertIdentical($Tag, $TagCopy);

		$NewTag = ClassRegistry::init(array('class' => 'RegisterArticleTag', 'alias' => 'NewTag'));
		$this->assertTrue(is_a($Tag, 'RegisterArticleTag'));

		$this->assertNotIdentical($Tag, $NewTag);

		$NewTag->name = 'SomeOtherName';
		$this->assertNotIdentical($Tag, $NewTag);

		$Tag->name = 'SomeOtherName';
		$this->assertNotIdentical($Tag, $NewTag);

		$this->assertTrue($TagCopy->name === 'SomeOtherName');
	}

	function testClassRegistryFlush () {
		$ArticleTag = ClassRegistry::getObject('RegisterArticleTag');
		$this->assertTrue(is_a($ArticleTag, 'RegisterArticleTag'));
		ClassRegistry::flush();

		$NoArticleTag = ClassRegistry::isKeySet('RegisterArticleTag');
		$this->assertFalse($NoArticleTag);
		$this->assertTrue(is_a($ArticleTag, 'RegisterArticleTag'));
	}

	function testAddMultiplModels () {
		$Article = ClassRegistry::isKeySet('Article');
		$this->assertFalse($Article);

		$Featured = ClassRegistry::isKeySet('Featured');
		$this->assertFalse($Featured);

		$Tag = ClassRegistry::isKeySet('Tag');
		$this->assertFalse($Tag);

		$models = array(array('class' => 'RegisterArticle', 'alias' => 'Article'),
				array('class' => 'RegisterArticleFeatured', 'alias' => 'Featured'),
				array('class' => 'RegisterArticleTag', 'alias' => 'Tag'));

		$added = ClassRegistry::init($models);
		$this->assertTrue($added);

		$Article = ClassRegistry::isKeySet('Article');
		$this->assertTrue($Article);

		$Featured = ClassRegistry::isKeySet('Featured');
		$this->assertTrue($Featured);

		$Tag = ClassRegistry::isKeySet('Tag');
		$this->assertTrue($Tag);

		$Article = ClassRegistry::getObject('Article');
		$this->assertTrue(is_a($Article, 'RegisterArticle'));

		$Featured = ClassRegistry::getObject('Featured');
		$this->assertTrue(is_a($Featured, 'RegisterArticleFeatured'));

		$Tag = ClassRegistry::getObject('Tag');
		$this->assertTrue(is_a($Tag, 'RegisterArticleTag'));
	}
}
?>