<?php
App::uses('AppModel', 'Model');
/**
 * AccountingRegisterType Model
 *
 * @property AccountingRegister $AccountingRegister
 */
class AccountingRegisterType extends AppModel {
	public $displayField='Name';
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'abbreviation' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	
	
/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'AccountingRegister' => array(
			'className' => 'AccountingRegister',
			'foreignKey' => 'accounting_register_type_id',
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
