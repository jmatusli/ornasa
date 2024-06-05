<?php
App::uses('AppModel', 'Model');
/**
 * CashReceiptInvoice Model
 *
 * @property CashReceipt $CashReceipt
 * @property Invoice $Invoice
 */
class CashReceiptInvoice extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
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
		'invoice_id' => array(
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
		'CashReceipt' => array(
			'className' => 'CashReceipt',
			'foreignKey' => 'cash_receipt_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Invoice' => array(
			'className' => 'Invoice',
			'foreignKey' => 'invoice_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Currency' => array(
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
