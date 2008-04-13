<?php
/* SVN FILE: $Id: db_acl.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * This is core configuration file.
 *
 * Use it to configure core behaviour ofCake.
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
 * @subpackage		cake.cake.libs.model
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Set database config if not defined.
 */
/**
 * Load Model and AppModel
 */
App::import('Model', 'App');
/**
 * Short description for file.
 *
 * Long description for file
 *
 *
 * @package		cake
 * @subpackage	cake.cake.libs.model
 */
class AclNode extends AppModel {
/**
 * Explicitly disable in-memory query caching for ACL models
 *
 * @var boolean
 * @access public
 */
	var $cacheQueries = false;
/**
 * ACL models use the Tree behavior
 *
 * @var array
 * @access public
 */
	var $actsAs = array('Tree' => 'nested');
/**
 * Constructor
 *
 */
	function __construct() {
		$config = Configure::read('Acl.database');
		if(isset($config)) {
			$this->useDbConfig = $config;
		}
		parent::__construct();
	}
/**
 * Retrieves the Aro/Aco node for this model
 *
 * @param mixed $ref Array with 'model' and 'foreign_key', model object, or string value
 * @return array Node found in database
 * @access public
 */
	function node($ref = null) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$type = $this->alias;
		$prefix = $this->tablePrefix;
		$result = null;

		if (!empty($this->useTable)) {
			$table = $this->useTable;
		} else {
			$table = Inflector::pluralize(Inflector::underscore($type));
		}

		if (empty($ref)) {
			return null;
		} elseif (is_string($ref)) {
			$path = explode('/', $ref);
			$start = $path[0];
			unset($path[0]);

			$queryData = array('conditions' => array(
											$db->name("{$type}.lft") . ' <= ' . $db->name("{$type}0.lft"),
											$db->name("{$type}.rght") . ' >= ' . $db->name("{$type}0.rght")),
									'fields' => array('id', 'parent_id', 'model', 'foreign_key', 'alias'),
									'joins' => array(array('table' => $db->name($prefix . $table),
											'alias' => "{$type}0",
											'type' => 'LEFT',
											'conditions' => array("{$type}0.alias" => $start))),
									'order' => $db->name("{$type}.lft") . ' DESC');
			foreach ($path as $i => $alias) {
				$j = $i - 1;

				array_push($queryData['joins'], array(
								'table' => $db->name($prefix . $table),
								'alias' => "{$type}{$i}",
								'type'  => 'LEFT',
								'conditions' => array(
										$db->name("{$type}{$i}.lft") . ' > ' . $db->name("{$type}{$j}.lft"),
										$db->name("{$type}{$i}.rght") . ' < ' . $db->name("{$type}{$j}.rght"),
										$db->name("{$type}{$i}.alias") . ' = ' . $db->value($alias))));

				$queryData['conditions'] = array('or' => array(
				$db->name("{$type}.lft") . ' <= ' . $db->name("{$type}0.lft") . ' AND ' . $db->name("{$type}.rght") . ' >= ' . $db->name("{$type}0.rght"),
				$db->name("{$type}.lft") . ' <= ' . $db->name("{$type}{$i}.lft") . ' AND ' . $db->name("{$type}.rght") . ' >= ' . $db->name("{$type}{$i}.rght")));
			}
			$result = $db->read($this, $queryData, -1);

		} elseif (is_object($ref) && is_a($ref, 'Model')) {
			$ref = array('model' => $ref->alias, 'foreign_key' => $ref->id);
		} elseif (is_array($ref) && !(isset($ref['model']) && isset($ref['foreign_key']))) {
			$name = key($ref);

			if(PHP5) {
				$model = ClassRegistry::init(array('class' => $name, 'alias' => $name));
			} else {
				$model =& ClassRegistry::init(array('class' => $name, 'alias' => $name));
			}

			if (empty($model)) {
				trigger_error("Model class '$name' not found in AclNode::node() when trying to bind {$this->alias} object", E_USER_WARNING);
				return null;
			}

			$tmpRef = null;
			if (method_exists($model, 'bindNode')) {
				$tmpRef = $model->bindNode($ref);
			}
			if (empty($tmpRef)) {
				$ref = array('model' => $name, 'foreign_key' => $ref[$name][$model->primaryKey]);
			} else {
				if (is_string($tmpRef)) {
					return $this->node($tmpRef);
				}
				$ref = $tmpRef;
			}
		}
		if (is_array($ref)) {
			foreach ($ref as $key => $val) {
				if (strpos($key, $type) !== 0) {
					unset($ref[$key]);
					$ref["{$type}0.{$key}"] = $val;
				}
			}
			$queryData = array('conditions'	=> $ref,
									'fields' => array('id', 'parent_id', 'model', 'foreign_key', 'alias'),
									'joins' => array(array('table' => $db->name($prefix . $table),
									'alias' => "{$type}0",
									'type' => 'LEFT',
									'conditions' => array(
									$db->name("{$type}.lft") . ' <= ' . $db->name("{$type}0.lft"),
									$db->name("{$type}.rght") . ' >= ' . $db->name("{$type}0.rght")))),
									'order' => $db->name("{$type}.lft") . ' DESC');
			$result = $db->read($this, $queryData, -1);

			if (!$result) {
				trigger_error("AclNode::node() - Couldn't find {$type} node identified by \"" . print_r($ref, true) . "\"", E_USER_WARNING);
			}
		}
		return $result;
	}
}
/**
 * Access Control Object
 *
 * @package cake
 * @subpackage cake.cake.libs.model
 */
class Aco extends AclNode {
/**
 * Model name
 *
 * @var string
 * @access public
 */
	var $name = 'Aco';
/**
 * Binds to ARO nodes through permissions settings
 *
 * @var array
 * @access public
 */
	var $hasAndBelongsToMany = array('Aro' => array('with' => 'Permission'));
}
/**
 * Action for Access Control Object
 *
 * @package		cake
 * @subpackage	cake.cake.libs.model
 */
class AcoAction extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
	var $name = 'AcoAction';
/**
 * ACO Actions belong to ACOs
 *
 * @var array
 * @access public
 */
	 var $belongsTo = array('Aco');
}
/**
 * Access Request Object
 *
 * @package	cake
 * @subpackage cake.cake.libs.model
 */
class Aro extends AclNode {
/**
 * Model name
 *
 * @var string
 * @access public
 */
	var $name = 'Aro';
/**
 * AROs are linked to ACOs by means of Permission
 *
 * @var array
 * @access public
 */
	var $hasAndBelongsToMany = array('Aco' => array('with' => 'Permission'));
}
/**
 * Permissions linking AROs with ACOs
 *
 * @package cake
 * @subpackage cake.cake.libs.model
 */
class Permission extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
	var $name = 'Permission';
/**
 * Explicitly disable in-memory query caching
 *
 * @var boolean
 * @access public
 */
	var $cacheQueries = false;
/**
 * Override default table name
 *
 * @var string
 * @access public
 */
	 var $useTable = 'aros_acos';
/**
 * Permissions link AROs with ACOs
 *
 * @var array
 * @access public
 */
	 var $belongsTo = array('Aro', 'Aco');
/**
 * No behaviors for this model
 *
 * @var array
 * @access public
 */
	 var $actsAs = null;
/**
 * Constructor, used to tell this model to use the
 * database configured for ACL
 */
	function __construct() {
		$config = Configure::read('Acl.database');
		if (!empty($config)) {
			$this->useDbConfig = $config;
		}
		parent::__construct();
	}
}
?>