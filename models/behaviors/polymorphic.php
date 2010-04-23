<?php
/**
 * Polymorphic Behavior 2.0
 *
 * Allow the model to be associated with any other model object
 *
 * Copyright (c), Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @author 		Andy Dawson (AD7six)
 * @version		2.0
 * @modifiedby	Gothfunc & Theaxiom
 * @license		http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class PolymorphicBehavior extends ModelBehavior {
	
	private $__polyConditions = null;

	public function setup(&$model, $config = array()) {
		$this->settings[$model->name] = am (array('classField' => 'class', 'foreignKey' => 'foreign_id'),$config);
	}

	public function beforeFind(&$model, $queryData) {
        // You can set conditions for each model associated with the polymorphic model.
		if (isset($queryData['polyConditions'])) {
			$this->__polyConditions = $queryData['polyConditions'];
			unset($queryData['polyConditions']);
		}
		return $queryData;
	}

	public function afterFind (&$model, $results, $primary = false) {
		extract($this->settings[$model->name]);
		if ($primary && isset($results[0][$model->alias][$classField])) {
			foreach ($results as $key => $result) {
				$associated = array();
				$class = $result[$model->alias][$classField];
				$foreignId = $result[$model->alias][$foreignKey];
				if ($class && $foreignId) {
					$associatedConditions = array(
						'conditions' => array(
							$class . '.id' => $foreignId
						)
					);
					if (isset($this->__polyConditions[$class])) {
						$associatedConditions = Set::merge($associatedConditions, $this->__polyConditions[$class]);
					}
					$result = $result[$model->alias];
					if (!isset($model->$class)) {
						$model->bindModel(array('belongsTo' => array(
							$class => array(
								'conditions' => array($model->alias . '.' . $classField => $class),
								'foreignKey' => $foreignKey
							)
						)));
					}
					$associated = $model->$class->find('first', $associatedConditions);
					$associated[$class]['display_field'] = $associated[$class][$model->$class->displayField];
                    $results[$key][$class] = $associated[$class];
                    unset($associated[$class]);
                    $results[$key][$class] = Set::merge($results[$key][$class], $associated);
				}
			}
		} elseif(isset($results[$model->alias][$classField])) {
			$associated = array();
			$class = $results[$model->alias][$classField];
			$foreignId = $results[$model->alias][$foreignKey];
			if ($class && $foreignId) {
				$result = $results[$model->alias];
				if (!isset($model->$class)) {
					$model->bindModel(array('belongsTo' => array(
						$class => array(
							'conditions' => array($model->alias . '.' . $classField => $class),
							'foreignKey' => $foreignKey
						)
					)));
				}
				$associated = $model->$class->find(array($class.'.id' => $foreignId), array('id', $model->$class->displayField), null, -1);
				$associated[$class]['display_field'] = $associated[$class][$model->$class->displayField];
				$results[$class] = $associated[$class];
			}
		}
		return $results;
	}
}
?>