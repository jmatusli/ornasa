<?php
App::uses('AppModel', 'Model');

class CashReceipt extends AppModel {


  function getCashReceiptCode($plantId =0,$plantShortName){
    if ($plantId == 0){
      return null;
    }
    $newCashReceiptCode="";
		$lastCashReceipt = $this->find('first',[
			'fields'=>['receipt_code'],
      'conditions'=>['CashReceipt.plant_id'=>$plantId],
			'order' => ['CAST(SUBSTR(CashReceipt.receipt_code,4,5) AS DEC)' => 'desc'],
		]);
		//pr($lastCashReceipt);
    $cashReceiptPrefix="R/C".($plantId == PLANT_SANDINO?" ":("_".$plantShortName."_"));
		if ($lastCashReceipt!= null){
			$newCashReceiptCodeNumber = intval(substr($lastCashReceipt['CashReceipt']['receipt_code'],strlen($cashReceiptPrefix)))+1;
			$newCashReceiptCode=$cashReceiptPrefix.$newCashReceiptCodeNumber;
		}
		else {
			$newCashReceiptCode=$cashReceiptPrefix."00001";
		} 
    return $newCashReceiptCode;    
  }

  function setDepositedStatus($id,$depositAmount,$currencyId){
    $depositedStatus=false;
    $thisCashReceipt=$this->find('first',[
			'fields'=>[
        'CashReceipt.receipt_date',
        'CashReceipt.amount','CashReceipt.currency_id',
      ],
			'conditions'=>['CashReceipt.id'=>$id],
		]);
    if (!empty($thisCashReceipt)){
      $cashReceiptDate=$thisCashReceipt['CashReceipt']['receipt_date'];
      $exchangeRateModel=ClassRegistry::init('ExchangeRate');
      $exchangeRateModel->recursive=-1;
      $cashReceiptExchangeRate=$exchangeRateModel->getApplicableExchangeRate($cashReceiptDate);
      $exchangeRateCashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
      
      //pr($exchangeRate);
      $thisCashReceipt['CashReceipt']['exchange_rate']=$exchangeRateCashReceipt;
      if ($thisCashReceipt['CashReceipt']['currency_id']==CURRENCY_CS){
        $thisCashReceipt['CashReceipt']['paid_amount_CS']=($thisCashReceipt['CashReceipt']['amount']);
        $thisCashReceipt['CashReceipt']['paid_amount_USD']=round(($thisCashReceipt['CashReceipt']['amount'])/$thisCashReceipt['CashReceipt']['exchange_rate'],2);
      }
      elseif ($thisCashReceipt['currency_id']==CURRENCY_USD){
        $thisCashReceipt['CashReceipt']['paid_amount_CS']=round(($thisCashReceipt['CashReceipt']['amount'])*$thisCashReceipt['CashReceipt']['exchange_rate'],2);
        $thisCashReceipt['CashReceipt']['paid_amount_USD']=($thisCashReceipt['CashReceipt']['amount']);
      }
      if ($currencyId == CURRENCY_CS){
        if (($depositAmount-$thisCashReceipt['CashReceipt']['paid_amount_CS'])>-0.001){
          $depositedStatus=true;  
        }
      }
      elseif ($currencyId == CURRENCY_USD){
        if (($depositAmount-$thisCashReceipt['CashReceipt']['paid_amount_USD'])>-0.001){
          $depositedStatus=true;
        }
      }
    }
    return $depositedStatus;
  }

	public $validate = [
		'receipt_date' => [
			'date' => [
				'rule' => ['date'],
			],
		],
		'receipt_code' => [
			//'notEmpty' => [
			//	'rule' => ['notEmpty'],
			//],
		],
		'currency_id' => [
			//'numeric' => [
			//	'rule' => ['numeric'],
			//],
		],
		'concept' => [
			//'notEmpty' => [
			//	'rule' => ['notEmpty'],
			//	'message' => 'Por favor registra el concepto del recibo de caja',
			//],
		],
	];

	public $belongsTo = [
		'CashReceiptType' => [
			'className' => 'CashReceiptType',
			'foreignKey' => 'cash_receipt_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Currency' => [
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Client' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'CashboxAccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'cashbox_accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'CreditAccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'credit_accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Order' => [
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Plant' => [
			'className' => 'Plant',
			'foreignKey' => 'plant_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'AccountingRegisterCashReceipt' => [
			'className' => 'AccountingRegisterCashReceipt',
			'foreignKey' => 'cash_receipt_id',
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
    'CashReceiptInvoice' => [
			'className' => 'CashReceiptInvoice',
			'foreignKey' => 'cash_receipt_id',
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
