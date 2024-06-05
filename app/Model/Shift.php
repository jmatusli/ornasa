<?php
App::uses('AppModel', 'Model');
/**
 * Shift Model
 *
 * @property ProductionRun $ProductionRun
 */
class Shift extends AppModel {
	var $displayField="name";

	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);


	public $hasMany = array(
		'ProductionRun' => array(
			'className' => 'ProductionRun',
			'foreignKey' => 'shift_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
