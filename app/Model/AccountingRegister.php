<?php
App::uses('AppModel', 'Model');
/**
 * AccountingRegister Model
 *
 * @property AccountingMovement $AccountingMovement
 */
class AccountingRegister extends AppModel {

	public function getregistercode($accountingregistertypeid){
	
		$accountingRegisterType=$this->AccountingRegisterType->find('first',array(
			'fields'=>array('abbreviation'),
			'conditions'=>array('AccountingRegisterType.id'=>$accountingregistertypeid),
		));
		$typePrefix=$accountingRegisterType['AccountingRegisterType']['abbreviation']."_";
	
		$prefixLength=strlen($typePrefix);
		
		$newAccountingRegisterCode="";
		$this->recursive=-1;
		$lastAccountingRegister = $this->find('first',array(
			'fields'=>array(
				'accounting_register_type_id',
				'register_code',
			),
			'conditions'=>array(
				'AccountingRegister.accounting_register_type_id'=>$accountingregistertypeid,
			),
			'order' => array('AccountingRegister.register_code' => 'desc'),
		));
		//pr($lastAccountingRegister);
		if ($lastAccountingRegister!= null){
			$newAccountingRegisterCode = substr($lastAccountingRegister['AccountingRegister']['register_code'],$prefixLength)+1;
			$newAccountingRegisterCode=$typePrefix.str_pad($newAccountingRegisterCode,5,'0',STR_PAD_LEFT);
		}
		else {
			$newAccountingRegisterCode=$typePrefix."00001";
		}
		return $newAccountingRegisterCode;
	}

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'concept' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				'message' => 'Introduzca el nombre del asiento contable',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'register_date' => array(
			'datetime' => array(
				'rule' => array('date'),
				'message' => 'Seleccione la fecha del asiento contable',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'currency_id' => array(
			'datetime' => array(
				'rule' => array('comparison',">",'0'),
				'message' => 'Seleccione la moneda del asiento contable',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	public $belongsTo = array(
		'AccountingRegisterType' => array(
			'className' => 'AccountingRegisterType',
			'foreignKey' => 'accounting_register_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'AccountingMovement' => array(
			'className' => 'AccountingMovement',
			'foreignKey' => 'accounting_register_id',
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
