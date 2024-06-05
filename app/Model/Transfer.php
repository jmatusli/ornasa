<?php
App::uses('AppModel', 'Model');
/**
 * Transfer Model
 *
 * @property Currency $Currency
 * @property BankAccountingCode $BankAccountingCode
 * @property CashboxAccountingCode $CashboxAccountingCode
 * @property AccountingRegister $AccountingRegister
 */
class Transfer extends AppModel {

  public function getNewTransferCode(){
    $lastTransfer = $this->find('first',[
			'fields'=>['transfer_code'],
			'order' => ['Transfer.transfer_code' => 'desc'],
		]);
		//pr($lastTransfer);
		if ($lastTransfer!= null){
			$newTransferCode = intval(substr($lastTransfer['Transfer']['transfer_code'],2))+1;
			$newTransferCode="T_".str_pad($newTransferCode,5,'0',STR_PAD_LEFT);
		}
		else {
			$newTransferCode="T_00001";
		}
    return $newTransferCode;
  }
  
	public $validate = array(
		'transfer_date' => array(
			'date' => array(
				'rule' => array('date'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'currency_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'bank_accounting_code_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'cashbox_accounting_code_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Currency' => array(
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		
		'BankAccountingCode' => array(
			'className' => 'AccountingCode',
			'foreignKey' => 'bank_accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		
		'CashboxAccountingCode' => array(
			'className' => 'AccountingCode',
			'foreignKey' => 'cashbox_accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'AccountingRegister' => array(
			'className' => 'AccountingRegister',
			'foreignKey' => 'accounting_register_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
  
  public $hasMany = array(
		'PaymentDeposit' => array(
			'className' => 'PaymentDeposit',
			'foreignKey' => 'transfer_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);	
}
