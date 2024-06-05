<?php
App::uses('AppModel', 'Model');

class AccountingCode extends AppModel {
	public $displayField="fullname";
	public $actsAs = [
		'Tree',
	];
	
  public function getAccountingCodeById($id){
    $accountingCode=$this->find('first',[
      'conditions'=>['AccountingCode.id'=>$id],
      'recursive'=>-1,
    ]);
    return $accountingCode;
  }
  
  public function getChildAccountingCodes($accountingCodeLeft,$accountingCodeRight,$boolMain=false){
    $accountingCodes=$this->find('list',[
			'conditions'=>[
				'AccountingCode.lft >'=>$accountingCodeLeft,
				'AccountingCode.rght <'=>$accountingCodeRight,
				'AccountingCode.bool_main'=>$boolMain,
			],
			'order'=>'AccountingCode.code ASC',
		]);
    return $accountingCodes;
  }
  
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['fullname'] = sprintf(
			'CONCAT(%s.code, " (", %s.description,")")', $this->alias, $this->alias
		);
		$this->virtualFields['shortfullname'] = sprintf(
			'SUBSTR(CONCAT(%s.code, " (", %s.description,")"),1,50)', $this->alias, $this->alias
		);
	}
	//public $virtualFields = array('fullname' => 'CONCAT(ChildAccountingCode.code, " (", ChildAccountingCode.description,")")');

	function getSaldo($accountingcodeid,$registerdate){	
		$accountingCode= $this->find('first', [
			'conditions'=>['id'=>$accountingcodeid],
			'contain' => [
				'AccountingMovement'=>[
					'fields' => [
						'AccountingMovement.amount',
						'AccountingMovement.currency_id',
						'AccountingMovement.bool_debit',
					],
					'conditions' => [
						'AccountingMovement.accounting_code_id'=>$accountingcodeid,
						'AccountingMovement.amount >'=>'0',
					],
					'AccountingRegister'=>[
						'fields'=>['AccountingRegister.id','AccountingRegister.register_date'],
						'conditions'=>[
							'AccountingRegister.register_date <'=>$registerdate,
						],
					],
				],
			],			
		]);
		$saldo=0;
		foreach ($accountingCode['AccountingMovement'] as $accountingMovement){
			if (!empty($accountingMovement['AccountingRegister'])){
				if ($accountingMovement['bool_debit']){
					$saldo+=$accountingMovement['amount'];
				}
				else {
					$saldo-=$accountingMovement['amount'];
				}
			}
			//echo "saldo is ".$saldo."<br/>";
		}
		
		if ($accountingCode['AccountingCode']['bool_creditor']){
			$saldo=-$saldo;
		}
		//echo "saldo after conversion is ".$saldo."<br/>";
		return $saldo;
	}
	
	function getTotalSaldo($accountingcodeid,$registerdate){
		$accountingCode=$this->read(null,$accountingcodeid);
		$bool_parent_credit=$accountingCode['AccountingCode']['bool_creditor'];
		$descendentcodeids=$this->find('list',[
			'fields' => ['AccountingCode.id'],
			'conditions' => [
				'AccountingCode.lft BETWEEN ? AND ?' => [$accountingCode['AccountingCode']['lft']+1, $accountingCode['AccountingCode']['rght']-1],
			],
		]);
		//pr($descendentcodeids);
		$totalSaldo=$this->getSaldo($accountingcodeid,$registerdate);
		//echo "total saldo is ".$totalSaldo."<br/>";
		if (!empty($descendentcodeids)){
			foreach ($descendentcodeids as $descendentcodeid){
				//pr($registerdate);
				//$totalchildren=$this->getTotalSaldo($descendentcodeid,$registerdate);
				$saldochildren=$this->getSaldo($descendentcodeid,$registerdate);
				//pr($saldochildren);
				$childAccountingCode=$this->read(null,$descendentcodeid);
				$bool_child_credit=$childAccountingCode['AccountingCode']['bool_creditor'];
				if ($bool_parent_credit==$bool_child_credit){
					$totalSaldo+=$saldochildren;
				}
				else {
					$totalSaldo-=$saldochildren;
				}
			}
		}
		return $totalSaldo;
	}

  function getNewClientAccountingCode(){
    $lastClientAccountingCode=$this->find('first',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
			],
			'order'=>'AccountingCode.code DESC',
		]);
		$lastClientCode=$lastClientAccountingCode['AccountingCode']['code'];
		//pr($lastClientCode);
		$positionLastHyphen=strrpos($lastClientCode,"-");
		//echo $positionLastHyphen."<br/>";
		$clientCodeStart=substr($lastClientCode,0,($positionLastHyphen+1));
		$clientCodeEnding=substr($lastClientCode,($positionLastHyphen+1));
		//echo $clientCodeStart."<br/>";
		//echo $clientCodeEnding."<br/>";
		$newClientCodeEnding=str_pad($clientCodeEnding+1,3,'0',STR_PAD_LEFT);
		//echo $newClientCodeEnding."<br/>";
    
    return $clientCodeStart.$newClientCodeEnding;
  }

	public $validate = [
		'code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'description' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		/*'bool_creditor' => [
			'boolean' => [
				'rule' => ['boolean'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],*/
		/*
		'lft' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'rght' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		*/
	];


	public $belongsTo = [
		'ParentAccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'ChildAccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'parent_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
		'AccountingMovement' => [
			'className' => 'AccountingMovement',
			'foreignKey' => 'accounting_code_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
	];

}
