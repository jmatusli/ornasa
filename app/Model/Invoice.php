<?php
App::uses('AppModel', 'Model');

class Invoice extends AppModel {

  function getInvoiceCode($warehouseId,$warehouseSeries){
    $lastInvoice = $this->find('first',[
			'fields'=>['Invoice.invoice_code'],
      'conditions'=>['Invoice.warehouse_id'=>$warehouseId],
      'recursive'=>-1,
			//'order' => ['CAST(Invoice.invoice_code) as unsigned)' => 'desc'],
      'order' => ['SUBSTRING(Invoice.invoice_code,POSITION("_" IN Invoice.invoice_code))' => 'desc'],
		]);
		//pr($lastInvoice);
		if ($lastInvoice!= null){
      $lastInvoiceCodeNumber=(int)substr($lastInvoice['Invoice']['invoice_code'],2);
      $newInvoiceCodeNumber=$lastInvoiceCodeNumber+1;
      $newInvoiceCode=$warehouseSeries.'_'.str_pad($newInvoiceCodeNumber,5,'0',STR_PAD_LEFT);
		}
		else {
      $newInvoiceCode=$warehouseSeries.'_00001';
		}
    //echo "new invoice code is ".$newInvoiceCode."<br/>";
    return $newInvoiceCode;
}  

	function getCreditDays($id){
		$thisInvoice=$this->find('first',array(
			'fields'=>array('Invoice.total_price','Invoice.currency_id','Invoice.bool_credit','Invoice.bool_paid','Invoice.invoice_date','Invoice.due_date'),
			'conditions'=>array('Invoice.id'=>$id),
		));
		$creditDays=0;
		if ($thisInvoice['Invoice']['bool_credit']){
			if (!$thisInvoice['Invoice']['bool_paid']){
				$invoiceDate=new DateTime($thisInvoice['Invoice']['invoice_date']);
        //$dueDate= new DateTime($thisInvoice['Invoice']['due_date']);
				$currentDate= new DateTime(date('Y-m-d'));
				$daysLate=$currentDate->diff($invoiceDate);
        //$daysLate=$dueDate->diff($invoiceDate);
				$creditDays=$daysLate->days;
			}
			else {
				$lastCashReceiptForInvoice=$this->CashReceiptInvoice->find('first',array(
					'fields'=>array('CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.amount','CashReceiptInvoice.currency_id','CashReceipt.receipt_date'),
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$id,
					),
					'order'=>'CashReceipt.receipt_date DESC',
				));
				if (!empty($lastCashReceiptForInvoice)){
					$receiptDate=new DateTime($lastCashReceiptForInvoice['CashReceipt']['receipt_date']);
          $invoiceDate=new DateTime($thisInvoice['Invoice']['invoice_date']);
          //$dueDate= new DateTime($thisInvoice['Invoice']['due_date']);
					$currentDate= new DateTime(date('Y-m-d'));
					$daysLate=$invoiceDate->diff($receiptDate);
          //$daysLate=$dueDate->diff($invoiceDate);
					$creditDays=$daysLate->days;
				}
			}
		}
		return $creditDays;
	}
	
	function getHistoricalCreditForClient($client_id){
		$invoices=$this->find('all',array(
			'fields'=>array('Invoice.id'),
			'conditions'=>array('Invoice.client_id'=>$client_id),
		));
		$historicalCredit=0;
		if (count($invoices)>0){
			foreach ($invoices as $invoice){
				$historicalCredit+=$this->getCreditDays($invoice['Invoice']['id']);
			}
			$historicalCredit=$historicalCredit/count($invoices);
		}
		return $historicalCredit;
	}

	function getAmountPaidAlreadyCS($id){
		$cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');
		
		$cashReceiptsForInvoice=$cashReceiptInvoiceModel->find('all',array(
			'fields'=>array(
				'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.currency_id',
				'CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.payment_retention',
				'CashReceiptInvoice.payment_erdiff_CS','CashReceiptInvoice.payment_increment_CS','CashReceiptInvoice.payment_discount_CS',
			),
			'conditions'=>array(
				'CashReceiptInvoice.invoice_id'=>$id,
			),
		));
		$paidAlreadyCS=0;
		//pr($cashReceiptsForInvoice);
		if (!empty($cashReceiptsForInvoice)){
			foreach ($cashReceiptsForInvoice as $cashReceiptForInvoice){
				//pr($cashReceiptForInvoice);
				$cashReceiptAmount=$cashReceiptForInvoice['CashReceiptInvoice']['payment']+$cashReceiptForInvoice['CashReceiptInvoice']['payment_retention'];
				$cashReceiptCurrencyId=$cashReceiptForInvoice['CashReceiptInvoice']['currency_id'];
				if ($cashReceiptCurrencyId==CURRENCY_CS){
					$paidAlreadyCS+=$cashReceiptAmount;
				}
				if ($cashReceiptCurrencyId==CURRENCY_USD){
					$cashReceiptModel=ClassRegistry::init('CashReceipt');
					$cashReceiptModel->recursive=-1;
					$cashReceipt=$cashReceiptModel->read(null,$cashReceiptForInvoice['CashReceiptInvoice']['cash_receipt_id']);
					
					$exchangeRateModel=ClassRegistry::init('ExchangeRate');
					$exchangeRateModel->recursive=-1;
					$cashReceiptExchangeRate=$exchangeRateModel->getApplicableExchangeRate($cashReceipt['CashReceipt']['receipt_date']);
					$exchangeRateCashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
					
					$paidAlreadyCS+=$cashReceiptAmount*$exchangeRateCashReceipt;
				}
				//$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_erdiff_CS'];
				// MODIFICATION 20160122 
				$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_increment_CS'];
				$paidAlreadyCS+=$cashReceiptForInvoice['CashReceiptInvoice']['payment_discount_CS'];
			}
		}
		return $paidAlreadyCS;
	}
	
	function getAmountPaidAlreadyWithoutErDiffCS($id){
		$cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');
		
		$cashReceiptsForInvoice=$cashReceiptInvoiceModel->find('all',array(
			'fields'=>array(
				'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.currency_id',
				'CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.payment_retention',
				'CashReceiptInvoice.payment_erdiff_CS','CashReceiptInvoice.payment_increment_CS','CashReceiptInvoice.payment_discount_CS',
			),
			'conditions'=>array(
				'CashReceiptInvoice.invoice_id'=>$id,
			),
		));
		$paidAlreadyCS=0;
		//pr($cashReceiptsForInvoice);
		if (!empty($cashReceiptsForInvoice)){
			foreach ($cashReceiptsForInvoice as $cashReceiptForInvoice){
				//pr($cashReceiptForInvoice);
				$cashReceiptAmount=$cashReceiptForInvoice['CashReceiptInvoice']['payment']+$cashReceiptForInvoice['CashReceiptInvoice']['payment_retention'];
				$cashReceiptCurrencyId=$cashReceiptForInvoice['CashReceiptInvoice']['currency_id'];
				if ($cashReceiptCurrencyId==CURRENCY_CS){
					$paidAlreadyCS+=$cashReceiptAmount;
				}
				if ($cashReceiptCurrencyId==CURRENCY_USD){
					$cashReceiptModel=ClassRegistry::init('CashReceipt');
					$cashReceiptModel->recursive=-1;
					$cashReceipt=$cashReceiptModel->read(null,$cashReceiptForInvoice['CashReceiptInvoice']['cash_receipt_id']);
					
					$exchangeRateModel=ClassRegistry::init('ExchangeRate');
					$exchangeRateModel->recursive=-1;
					$cashReceiptExchangeRate=$exchangeRateModel->getApplicableExchangeRate($cashReceipt['CashReceipt']['receipt_date']);
					$exchangeRateICashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
					
					$paidAlreadyCS+=$cashReceiptAmount*$exchangeRateICashReceipt;
				}
				$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_erdiff_CS'];
				// MODIFICATION 20160122 
				// DO NOT HANDLE INCREMENT AND DISCOUNT HERE, IT IS USED FOR CALCULUS PAYMENT IN EDIT CASHRECEIPT
				//$paidAlreadyCS-=$cashReceiptForInvoice['CashReceiptInvoice']['payment_increment_CS'];
				//$paidAlreadyCS+=$cashReceiptForInvoice['CashReceiptInvoice']['payment_discount_CS'];
			}
		}
		return $paidAlreadyCS;
	}
	
	function OBSOLETEgetPendingAmountCSOBSOLETE($id,$appliedExchangeRate){
    // NOTE 20160122 I BELIEVE THIS FUNCTION HAS FALLEN IN DISUSE BECAUSE IT DOES NOT TAKE INTO ACCOUNT DISCOUNT AND INCREMENT
		// TO BE SURE, ONE WOULD HAVE TO OPEN ALL FILES AND SEE IF THERE ARE ANY FUNCTION CALLS PRESENT
		$thisInvoice=$this->find('first',array(
			'fields'=>array('Invoice.total_price','Invoice.currency_id'),
			'conditions'=>array('Invoice.id'=>$id),
		));
		$invoiceTotalPrice=$thisInvoice['Invoice']['total_price'];
		$invoiceCurrencyId=$thisInvoice['Invoice']['currency_id'];
	
		$pendingForInvoice=$invoiceTotalPrice;
		
		$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('all',array(
			'fields'=>array(
				'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.currency_id',
				'CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.payment_retention',
			),
			'conditions'=>array(
				'CashReceiptInvoice.invoice_id'=>$id,
			),
		));
		//pr($cashReceiptsForInvoice);
		if (!empty($cashReceiptsForInvoice)){
			foreach ($cashReceiptsForInvoice as $cashReceiptForInvoice){
				$cashReceiptAmount=$cashReceiptForInvoice['CashReceiptInvoice']['payment']+$cashReceiptForInvoice['CashReceiptInvoice']['payment_retention'];
				$cashReceiptCurrencyId=$cashReceiptForInvoice['CashReceiptInvoice']['currency_id'];
				if ($invoiceCurrencyId==$cashReceiptCurrencyId){
					$pendingForInvoice-=$cashReceiptAmount;
				}
				else {
					// when cash receipt has different currency from invoice, conversion is needed 
					if ($invoiceCurrencyId==CURRENCY_USD && $cashReceiptCurrencyId==CURRENCY_CS){ 
						
						$pendingForInvoice-=$cashReceiptAmount/$appliedExchangeRate;
					}
					else { 
						$pendingForInvoice-=$cashReceiptAmount*$appliedExchangeRate;
					}
				}
			}
		}
		
		return $pendingForInvoice;
	}
  
  function getPendingAmountCS($id){
    // 20180510 NEW VERSION
    // taken from invoices ver historial pagos
    
		$thisInvoice=$this->find('first',[
			'fields'=>['Invoice.total_price','Invoice.currency_id','Invoice.invoice_date'],
			'conditions'=>['Invoice.id'=>$id],
		]);
		$totalForInvoice=$thisInvoice['Invoice']['total_price'];
    $totalForInvoiceCS=$totalForInvoice;
		$invoiceCurrencyId=$thisInvoice['Invoice']['currency_id'];
    
    $exchangeRateModel=ClassRegistry::init('ExchangeRate');
		$exchangeRateModel->recursive=-1;
    
    if ($invoiceCurrencyId==CURRENCY_USD){
      $invoiceExchangeRate=$exchangeRateModel->getApplicableExchangeRate($thisInvoice['Invoice']['invoice_date']);
      $exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
      $totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
    }
		
    $paidForInvoiceCS=round($this->getAmountPaidAlreadyCS($thisInvoice['Invoice']['id']),2);		
    $pendingForInvoiceCS=$totalForInvoiceCS-$paidForInvoiceCS;
		if ($invoiceCurrencyId==CURRENCY_USD){
      $cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');  
			$this->loadModel('CashReceiptInvoice');
      $cashReceiptInvoices=$cashReceiptInvoiceModel->find('all',[
        'conditions'=>['CashReceiptInvoice.invoice_id'=>$pendingInvoices[$c]['Invoice']['id']],
        'contain'=>[
          'CashReceipt'=>[
            'fields'=>[
              'CashReceipt.id','CashReceipt.receipt_code',
              'CashReceipt.receipt_date',
              'CashReceipt.bool_annulled',
            ],
          ],
          'Currency'=>[
            'fields'=>[
              'Currency.abbreviation','Currency.id',
            ],
          ],
        ],
      ]);
      $currentExchangeRate=$exchangeRateModel->getApplicableExchangeRate(date('Y-m-d'));
      $exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
      $differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
      $differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
      $pendingForInvoiceCS+=$differenciaCambiariaTotal;
      // add the diferencia cambiaria on the cashreceipts
      if (!empty($cashReceiptInvoices)){
        for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
          $cashReceiptExchangeRate=$exchangeRateModel->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
          $exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
          $differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
          $differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
          $pendingForInvoiceCS-=$differenciaCambiariaPaid;
        }
      }
    }
    return $pendingForInvoiceCS;
	}

  function setDepositedStatus($id,$depositAmount,$currencyId){
    $depositedStatus=false;
    $thisInvoice=$this->find('first',[
			'fields'=>[
        'Invoice.invoice_date',
        'Invoice.total_price','Invoice.retention_amount','Invoice.currency_id',
      ],
			'conditions'=>['Invoice.id'=>$id],
		]);
    if (!empty($thisInvoice)){
      $invoiceDate=$thisInvoice['Invoice']['invoice_date'];
      $exchangeRateModel=ClassRegistry::init('ExchangeRate');
      $exchangeRateModel->recursive=-1;
      $invoiceExchangeRate=$exchangeRateModel->getApplicableExchangeRate($invoiceDate);
      $exchangeRateInvoice=$invoiceExchangeRate['ExchangeRate']['rate'];
      
      //pr($exchangeRate);
      $thisInvoice['Invoice']['exchange_rate']=$exchangeRateInvoice;
      if ($thisInvoice['Invoice']['currency_id']==CURRENCY_CS){
        $thisInvoice['Invoice']['paid_amount_CS']=($thisInvoice['Invoice']['total_price']-$thisInvoice['Invoice']['retention_amount']);
        $thisInvoice['Invoice']['paid_amount_USD']=round(($thisInvoice['Invoice']['total_price']-$thisInvoice['Invoice']['retention_amount'])/$thisInvoice['Invoice']['exchange_rate'],2);
      }
      elseif ($thisInvoice['currency_id']==CURRENCY_USD){
        $thisInvoice['Invoice']['paid_amount_CS']=round(($thisInvoice['Invoice']['total_price']-$thisInvoice['Invoice']['retention_amount'])*$thisInvoice['Invoice']['exchange_rate'],2);
        $thisInvoice['Invoice']['paid_amount_USD']=($thisInvoice['Invoice']['total_price']-$thisInvoice['Invoice']['retention_amount']);
      }
      if ($currencyId == CURRENCY_CS){
        //echo "deposit  amount is ".$depositAmount." and paid amount is ".$thisInvoice['Invoice']['paid_amount_CS']."<br/>";
        if (($depositAmount-$thisInvoice['Invoice']['paid_amount_CS'])>-0.001){
          $depositedStatus=true;  
        }
      }
      elseif ($currencyId == CURRENCY_USD){
        if (($depositAmount-$thisInvoice['Invoice']['paid_amount_USD'])>-0.001){
          $depositedStatus=true;
        }
      }
    }
    return $depositedStatus;
  }

  function getCreditStatus($orderId){
    $invoice=$this->find('first',[
      'conditions'=>[
        'Invoice.order_id'=>$orderId,
      ],
      'recursive'=>-1,
    ]);
    if (empty($invoice)){
      return 0;
    }
    return $invoice['Invoice']['bool_credit'];
  }
  function getSalesOrderId($orderId){
    $invoice=$this->find('first',[
      'conditions'=>[
        'Invoice.order_id'=>$orderId,
      ],
      'recursive'=>-1,
    ]);
    if (empty($invoice)){
      return 0;
    }
    return $invoice['Invoice']['sales_order_id'];
  }
	public $validate = [
		'invoice_code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'invoice_date' => [
			'date' => [
				'rule' => ['date'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'bool_annulled' => [
			'boolean' => [
				'rule' => ['boolean'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'client_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'currency_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'bool_IVA' => [
			'boolean' => [
				'rule' => ['boolean'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];

	public $belongsTo = [
		'Order' => [
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'SalesOrder' => [
			'className' => 'SalesOrder',
			'foreignKey' => 'sales_order_id',
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
		'Currency' => [
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
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
    'Warehouse' => [
			'className' => 'Warehouse',
			'foreignKey' => 'warehouse_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'CreditAuthorizationUser' => [
			'className' => 'User',
			'foreignKey' => 'credit_authorization_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'AccountingRegisterInvoice' => [
			'className' => 'AccountingRegisterInvoice',
			'foreignKey' => 'invoice_id',
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
			'foreignKey' => 'invoice_id',
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
