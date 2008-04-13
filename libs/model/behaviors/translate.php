<?php
/* SVN FILE: $Id: translate.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Short description for file.
 *
 * Long description for file
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
 * @subpackage		cake.cake.libs.model.behaviors
 * @since			CakePHP(tm) v 1.2.0.4525
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * @package	 	cake
 * @subpackage	cake.cake.libs.model.behaviors
 */
class TranslateBehavior extends ModelBehavior {
/**
 * Used for runtime configuration of model
 */
	var $runtime = array();
/**
 * Callback
 *
 * $config for TranslateBehavior should be
 * array( 'fields' => array('field_one',
 * 'field_two' => 'FieldAssoc', 'field_three'))
 *
 * With above example only one permanent hasMany will be joined (for field_two
 * as FieldAssoc)
 *
 * $config could be empty - and translations configured dynamically by
 * bindTranslation() method
 */
	function setup(&$model, $config = array()) {
		$db =& ConnectionManager::getDataSource($model->useDbConfig);
		if (!$db->connected) {
			trigger_error('Datasource '.$model->useDbConfig.' for TranslateBehavior of model '.$model->alias.' is not connected', E_USER_ERROR);
			return false;
		}

		$this->settings[$model->alias] = array();
		$this->runtime[$model->alias] = array('fields' => array());
		$this->translateModel($model);
		return $this->bindTranslation($model, null, false);
	}
/**
 * Callback
 */
	function beforeFind(&$model, $query) {
		$locale = $this->_getLocale($model);
		$db =& ConnectionManager::getDataSource($model->useDbConfig);
		$tablePrefix = $db->config['prefix'];
		$RuntimeModel =& $this->translateModel($model);

		if (is_string($query['fields']) && 'COUNT(*) AS '.$db->name('count') == $query['fields']) {
			if (empty($locale)) {
				return $query;
			}
			$query['fields'] = 'COUNT(DISTINCT('.$db->name($model->alias).'.'.$db->name($model->primaryKey).')) ' . $db->alias . 'count';
			$query['joins'][] = array(
						'type' => 'INNER',
						'alias' => $RuntimeModel->alias,
						'table' => $db->name($tablePrefix . $RuntimeModel->useTable),
						'conditions' => array(
								$model->alias.'.id' => '{$__cakeIdentifier['.$RuntimeModel->alias.'.foreign_key]__$}',
								$RuntimeModel->alias.'.model' => $model->alias,
								$RuntimeModel->alias.'.locale' => $locale));
			return $query;
		}

		if (empty($locale)) {
			return $query;
		}
		$autoFields = false;

		if (empty($query['fields'])) {
			$query['fields'] = array($model->alias.'.*');

			foreach (array('hasOne', 'belongsTo') as $type) {
				foreach ($model->{$type} as $key => $value) {

					if (empty($value['fields'])) {
						$query['fields'][] = $key.'.*';
					} else {
						foreach ($value['fields'] as $field) {
							$query['fields'][] = $key.'.'.$field;
						}
					}
				}
			}
			$autoFields = true;
		}
		$fields = array_merge($this->settings[$model->alias], $this->runtime[$model->alias]['fields']);
		$addFields = array();
		if (is_array($query['fields'])) {
			foreach ($fields as $key => $value) {
				$field = ife(is_numeric($key), $value, $key);

				if (in_array($model->alias.'.*', $query['fields']) || $autoFields || in_array($model->alias.'.'.$field, $query['fields']) || in_array($field, $query['fields'])) {
					$addFields[] = $field;
				}
			}
		}

		if ($addFields) {
			foreach ($addFields as $field) {
				foreach (array($field, $model->alias.'.'.$field) as $_field) {
					$key = array_search($_field, $query['fields']);

					if ($key !== false) {
						unset($query['fields'][$key]);
					}
				}

				if (is_array($locale)) {
					foreach ($locale as $_locale) {
						$query['fields'][] = 'I18n__'.$field.'__'.$_locale.'.content';
						$query['joins'][] = array(
								'type' => 'LEFT',
								'alias' => 'I18n__'.$field.'__'.$_locale,
								'table' => $db->name($tablePrefix . $RuntimeModel->useTable),
								'conditions' => array(
										$model->alias.'.id' => '{$__cakeIdentifier[I18n__'.$field.'__'.$_locale.'.foreign_key]__$}',
										'I18n__'.$field.'__'.$_locale.'.model' => $model->alias,
										'I18n__'.$field.'__'.$_locale.'.'.$RuntimeModel->displayField => $field,
										'I18n__'.$field.'__'.$_locale.'.locale' => $_locale));
					}
				} else {
					$query['fields'][] = 'I18n__'.$field.'.content';
					$query['joins'][] = array(
							'type' => 'LEFT',
							'alias' => 'I18n__'.$field,
							'table' => $db->name($tablePrefix . $RuntimeModel->useTable),
							'conditions' => array(
									$model->alias.'.id' => '{$__cakeIdentifier[I18n__'.$field.'.foreign_key]__$}',
									'I18n__'.$field.'.model' => $model->alias,
									'I18n__'.$field.'.'.$RuntimeModel->displayField => $field));
					$query['conditions'][$db->name('I18n__'.$field.'.locale')] = $locale;
				}
			}
		}
		if (is_array($query['fields'])) {
			$query['fields'] = array_merge($query['fields']);
		}
		$this->runtime[$model->alias]['beforeFind'] = $addFields;
		return $query;
	}
/**
 * Callback
 */
	function afterFind(&$model, $results, $primary) {
		$this->runtime[$model->alias]['fields'] = array();
		$locale = $this->_getLocale($model);

		if (empty($locale) || empty($results) || empty($this->runtime[$model->alias]['beforeFind'])) {
			return $results;
		}
		$beforeFind = $this->runtime[$model->alias]['beforeFind'];

		foreach ($results as $key => $row) {
			$results[$key][$model->alias]['locale'] = ife(is_array($locale), @$locale[0], $locale);

			foreach ($beforeFind as $field) {
				if (is_array($locale)) {
					foreach ($locale as $_locale) {
						if (!isset($results[$key][$model->alias][$field]) && !empty($results[$key]['I18n__'.$field.'__'.$_locale]['content'])) {
							$results[$key][$model->alias][$field] = $results[$key]['I18n__'.$field.'__'.$_locale]['content'];
						}
						unset($results[$key]['I18n__'.$field.'__'.$_locale]);
					}

					if (!isset($results[$key][$model->alias][$field])) {
						$results[$key][$model->alias][$field] = '';
					}
				} else {
					$value = ife(empty($results[$key]['I18n__'.$field]['content']), '', $results[$key]['I18n__'.$field]['content']);
					$results[$key][$model->alias][$field] = $value;
					unset($results[$key]['I18n__'.$field]);
				}
			}
		}
		return $results;
    }
/**
 * Callback
 */
	function beforeSave(&$model) {
		$locale = $this->_getLocale($model);

		if (empty($locale) || is_array($locale)) {
			return true;
		}
		$fields = array_merge($this->settings[$model->alias], $this->runtime[$model->alias]['fields']);
		$tempData = array();

		foreach ($fields as $key => $value) {
			$field = ife(is_numeric($key), $value, $key);

			if (isset($model->data[$model->alias][$field])) {
				$tempData[$field] = $model->data[$model->alias][$field];
				unset($model->data[$model->alias][$field]);
			}
		}
		$this->runtime[$model->alias]['beforeSave'] = $tempData;
		return true;
	}
/**
 * Callback
 */
	function afterSave(&$model, $created) {
		if (!isset($this->runtime[$model->alias]['beforeSave'])) {
			return true;
		}
		$locale = $this->_getLocale($model);
		$tempData = $this->runtime[$model->alias]['beforeSave'];
		unset($this->runtime[$model->alias]['beforeSave']);
		$conditions = array('locale' => $locale, 'model' => $model->alias, 'foreign_key' => $model->id);
		$RuntimeModel =& $this->translateModel($model);

		if (empty($created)) {
			$translations = $RuntimeModel->find('list', array('conditions' => array_merge($conditions, array($RuntimeModel->displayField => array_keys($tempData)))));

			if ($translations) {
				foreach ($translations as $id => $field) {
					$RuntimeModel->create();
					$RuntimeModel->save(array($RuntimeModel->alias => array('id' => $id, 'content' => $tempData[$field])));
					unset($tempData[$field]);
				}
			}
		}

		if (!empty($tempData)) {
			foreach ($tempData as $field => $value) {
				$RuntimeModel->create(array_merge($conditions, array($RuntimeModel->displayField => $field, 'content' => $value)));
				$RuntimeModel->save();
			}
		}
	}
/**
 * Callback
 */
	function afterDelete(&$model) {
		$RuntimeModel =& $this->translateModel($model);
		$conditions = array('model' => $model->alias, 'foreign_key' => $model->id);
		$RuntimeModel->deleteAll($conditions);
	}
/**
 * Get selected locale for model
 *
 * @return mixed string or false
 */
	function _getLocale(&$model) {
		if (!isset($model->locale) || is_null($model->locale)) {
			if (!class_exists('I18n')) {
				uses('i18n');
			}
			$I18n =& I18n::getInstance();
			$model->locale = $I18n->l10n->locale;
		}
		return $model->locale;
	}
/**
 * Get instance of model for translations
 *
 * @return object
 */
	function &translateModel(&$model) {
		if (!isset($this->runtime[$model->alias]['model'])) {
			if (!isset($model->translateModel) || empty($model->translateModel)) {
				$className = 'I18nModel';
			} else {
				$className = $model->translateModel;
			}

			if (PHP5) {
				$this->runtime[$model->alias]['model'] = ClassRegistry::init($className, 'Model');
			} else {
				$this->runtime[$model->alias]['model'] =& ClassRegistry::init($className, 'Model');
			}
		}

		$useTable = 'i18n';
		if (!empty($model->translateTable)) {
			$useTable = $model->translateTable;
		}
		if ($useTable !== $this->runtime[$model->alias]['model']->useTable) {
			$this->runtime[$model->alias]['model']->setSource($model->translateTable);
		}

		return $this->runtime[$model->alias]['model'];
	}
/**
 * Bind translation for fields, optionally with hasMany association for
 * fake field
 *
 * @param object instance of model
 * @param mixed string with field, or array(field1, field2=>AssocName, field3), or null for bind all original translations
 * @param boolean $reset
 * @return bool
 */
	function bindTranslation(&$model, $fields = null, $reset = true) {
		if (empty($fields)) {
			return $this->bindTranslation($model, $model->actsAs['Translate'], $reset);
		}

		if (is_string($fields)) {
			$fields = array($fields);
		}
		$associations = array();
		$RuntimeModel =& $this->translateModel($model);
		$default = array('className' => $RuntimeModel->alias, 'foreignKey' => 'foreign_key');

		foreach ($fields as $key => $value) {
			if (is_numeric($key)) {
				$field = $value;
				$association = null;
			} else {
				$field = $key;
				$association = $value;
			}

			if (array_key_exists($field, $this->settings[$model->alias])) {
				unset($this->settings[$model->alias][$field]);

			} elseif (in_array($field, $this->settings[$model->alias])) {
				$this->settings[$model->alias] = array_merge(array_diff_assoc($this->settings[$model->alias], array($field)));
			}

			if (array_key_exists($field, $this->runtime[$model->alias]['fields'])) {
				unset($this->runtime[$model->alias]['fields'][$field]);

			} elseif (in_array($field, $this->runtime[$model->alias]['fields'])) {
				$this->runtime[$model->alias]['fields'] = array_merge(array_diff_assoc($this->runtime[$model->alias]['fields'], array($field)));
			}

			if (is_null($association)) {
				if ($reset) {
					$this->runtime[$model->alias]['fields'][] = $field;
				} else {
					$this->settings[$model->alias][] = $field;
				}
			} else {

				if ($reset) {
					$this->runtime[$model->alias]['fields'][$field] = $association;
				} else {
					$this->settings[$model->alias][$field] = $association;
				}

				foreach (array('hasOne', 'hasMany', 'belongsTo', 'hasAndBelongsToMany') as $type) {
					if (isset($model->{$type}[$association]) || isset($model->__backAssociation[$type][$association])) {
						trigger_error('Association '.$association.' is already binded to model '.$model->alias, E_USER_ERROR);
						return false;
					}
				}
				$associations[$association] = array_merge($default, array('conditions' => array(
						'model' => $model->alias,
						$RuntimeModel->displayField => $field)));
			}
		}

		if (!empty($associations)) {
			$model->bindModel(array('hasMany' => $associations), $reset);
		}
		return true;
	}
/**
 * Unbind translation for fields, optionally unbinds hasMany association for
 * fake field
 *
 * @param object instance of model
 * @param mixed string with field, or array(field1, field2=>AssocName, field3), or null for unbind all original translations
 * @return bool
 */
	function unbindTranslation(&$model, $fields = null) {
		if (empty($fields)) {
			return $this->unbindTranslation($model, $model->actsAs['Translate']);
		}

		if (is_string($fields)) {
			$fields = array($fields);
		}
		$RuntimeModel =& $this->translateModel($model);
		$default = array('className' => $RuntimeModel->alias, 'foreignKey' => 'foreign_key');
		$associations = array();

		foreach ($fields as $key => $value) {
			if (is_numeric($key)) {
				$field = $value;
				$association = null;
			} else {
				$field = $key;
				$association = $value;
			}

			if (array_key_exists($field, $this->settings[$model->alias])) {
				unset($this->settings[$model->alias][$field]);

			} elseif (in_array($field, $this->settings[$model->alias])) {
				$this->settings[$model->alias] = array_merge(array_diff_assoc($this->settings[$model->alias], array($field)));
			}

			if (array_key_exists($field, $this->runtime[$model->alias]['fields'])) {
				unset($this->runtime[$model->alias]['fields'][$field]);

			} elseif (in_array($field, $this->runtime[$model->alias]['fields'])) {
				$this->runtime[$model->alias]['fields'] = array_merge(array_diff_assoc($this->runtime[$model->alias]['fields'], array($field)));
			}

			if (!is_null($association) && (isset($model->hasMany[$association]) || isset($model->__backAssociation['hasMany'][$association]))) {
				$associations[] = $association;
			}
		}

		if (!empty($associations)) {
			$model->unbindModel(array('hasMany' => $associations), false);
		}
		return true;
	}
}
if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
/**
 * @package	 	cake
 * @subpackage	cake.cake.libs.model.behaviors
 */
	class I18nModel extends AppModel {
		var $name = 'I18nModel';
		var $useTable = 'i18n';
		var $displayField = 'field';
	}
}
?>