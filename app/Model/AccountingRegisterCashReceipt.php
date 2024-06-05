<?php
App::uses('AppModel', 'Model');
/**
 * AccountingRegisterCashReceipt Model
 *
 * @property AccountingRegister $AccountingRegister
 * @property CashReceipt $CashReceipt
 */
class AccountingRegisterCashReceipt extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'accounting_register_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'cash_receipt_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
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
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'AccountingRegister' => array(
			'className' => 'AccountingRegister',
			'foreignKey' => 'accounting_register_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'CashReceipt' => array(
			'className' => 'CashReceipt',
			'foreignKey' => 'cash_receipt_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
