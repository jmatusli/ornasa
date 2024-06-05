<?php
App::uses('AppModel', 'Model');

class Quotation extends AppModel {

	public $displayField="quotation_code";
  
  public function getQuotationCode($userName, $warehouseId,$warehouseSeries,$quotationDateDay,$quotationDateMonth,$quotationDateYear){
    $quotationDateString=$quotationDateYear.'-'.$quotationDateMonth.'-'.$quotationDateDay;
		$quotationDate=date( "Y-m-d", strtotime($quotationDateString));
		$firstDayString=$quotationDateYear.'-'.$quotationDateMonth.'-01';
		$dateFirstDayMonth=date('Y-m-d', strtotime($firstDayString));
    $dateFirstDayNextMonth=date('Y-m-d',strtotime($firstDayString."+1 months"));
		
    $quotationCount=$this->find('count',[
			'conditions'=>[
				//'Quotation.quotation_code LIKE'=>$userAbbreviation."%",
        'Quotation.quotation_code LIKE'=>$userName."%",
				'Quotation.quotation_date >='=>$dateFirstDayMonth,
				'Quotation.quotation_date <'=>$dateFirstDayNextMonth,
			],
		]);
		$fullQuotationCount=$this->find('count',[
			'conditions'=>[
				'Quotation.quotation_code LIKE'=>$userName."%",
			],
		]);
    
    $newQuotationCode=$warehouseSeries."_".$userName."_".($fullQuotationCount+1)."_".($quotationCount+1)."_".$quotationDateDay.$quotationDateMonth.substr($quotationDateYear,2,2);
		
		$lastQuotationForMonth=$this->find('first',[
			'fields'=>['Quotation.quotation_code'],
			'conditions'=>[
				//'Quotation.quotation_code LIKE'=>$userAbbreviation."%",
        'Quotation.quotation_code LIKE'=>"%".$userName."%",
				'Quotation.warehouse_id'=>$warehouseId,
        'Quotation.quotation_date >='=>$dateFirstDayMonth,
				'Quotation.quotation_date <'=>$dateFirstDayNextMonth,
        
			],
      'recursive'=>-1,
			'order'=>'Quotation.id DESC',
		]);
		
		if (!empty($lastQuotationForMonth)){
      //pr($lastQuotationForMonth);
      $lastUnderscore=strrpos($lastQuotationForMonth['Quotation']['quotation_code'],'_');
			//pr ($lastUnderscore);
			$penultimateUnderscore=strrpos($lastQuotationForMonth['Quotation']['quotation_code'],'_',-8);
      //pr ($penultimateUnderscore);
			$lastQuotationNumberForMonth=substr($lastQuotationForMonth['Quotation']['quotation_code'],$penultimateUnderscore+1,$lastUnderscore-$penultimateUnderscore-1);
      //echo $lastQuotationNumberForMonth.'<br/>';
			$newQuotationCode=$warehouseSeries."_".$userName."_".($fullQuotationCount+1)."_".($lastQuotationNumberForMonth+1)."_".$quotationDateDay.$quotationDateMonth.substr($quotationDateYear,2,2);
		}
		
		return $newQuotationCode;
    
    
    $lastSalesOrder = $this->find('first',[
			'fields'=>['sales_order_code'],
      'conditions'=>[
        'SalesOrder.warehouse_id'=>$warehouseId,
      ],
      'recursive'=>-1,
			//'order' => ['CAST(SalesOrder.sales_order_code as unsigned)' => 'desc'],
      'order' => ['SalesOrder.sales_order_code' => 'desc'],
		]);
		if ($lastSalesOrder!= null){
      $lastSalesOrderCodeNumber=(int)substr($lastSalesOrder['SalesOrder']['sales_order_code'],6);
      $newSalesOrderCodeNumber=$lastSalesOrderCodeNumber+1;
      $newSalesOrderCode=$warehouseSeries.'_OV_'.str_pad($newSalesOrderCodeNumber,6,'0',STR_PAD_LEFT);
		}
		else {
			$newSalesOrderCode=$warehouseSeries."_OV_000001";
		}
    return $newSalesOrderCode;
  }
  
  public function getPendingQuotations($warehouseId,$quotationId=0){
    $conditions=[
      'Quotation.bool_sales_order'=>false,
      'Quotation.bool_annulled'=>false,
    ];
    if ($warehouseId > 0){
      $conditions['Quotation.warehouse_id']=$warehouseId;
    }
    if ($quotationId > 0){
      $conditions=[
        'OR'=>[
          [
            'Quotation.id'=>$quotationId
          ],
          $conditions,
        ],
      ];
    }
    $allPendingQuotations=$this->find('all',[
      'conditions'=>$conditions,
      'recursive'=>-1,
      'order'=>'Quotation.quotation_date DESC',
    ]);
    $quotations=[];
    if (!empty($allPendingQuotations)){
      foreach ($allPendingQuotations as $quotation){
        $quotations[$quotation['Quotation']['id']]=($quotation['Quotation']['client_name']." (".$quotation['Quotation']['quotation_code'].")");
      }
    }
    
    return $quotations;
  }
	/*
	public function getDroppedPercentageForQuotation($quotation_id){
		$quotation=$this->read(null,$quotation_id);
		if ($quotation['Quotation']['bool_rejected']){
			return 100;
		}
		else {
		
			$this->Invoice->recursive=-1;
			$invoices=$this->Invoice->find('all',array(
				'conditions'=>array(
					'Invoice.quotation_id'=>$quotation_id,
				),
			));
			if (!empty($invoices)){
				//pr($invoices);
				$invoiceSubTotal=0;
				foreach ($invoices as $invoice){
					$invoiceSubTotal+=$invoice['Invoice']['price_subtotal'];
				}
				return round(100-(100*$invoiceSubTotal/$quotation['Quotation']['price_subtotal']),2);
			}
			else {
				$this->SalesOrder->recursive=-1;
				$salesOrders=$this->SalesOrder->find('all',array(
					'conditions'=>array(
						'SalesOrder.quotation_id'=>$quotation_id,
					),
				));
				if (!empty($salesOrders)){
					$salesOrderSubTotal=0;
					foreach ($salesOrders as $salesOrder){
						$salesOrderSubTotal+=$salesOrder['SalesOrder']['price_subtotal'];
					}
					return round(100-(100*$salesOrderSubTotal/$quotation['Quotation']['price_subtotal']),2);
				}
				else {
					$dueDate= new DateTime($quotation['Quotation']['due_date']);
					$nowDate= new DateTime();
					$daysLate=$nowDate->diff($dueDate);
					if ((int)$daysLate->format("%r%a")<0){
						return 100;
					}
					else {
						return 0;
					}
				}
			} 
			//$cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');
		}
	}
	public function getSoldPercentageForQuotation($quotation_id){
		$quotation=$this->read(null,$quotation_id);
		//no logic inserted for bool_rejected
		$this->Invoice->recursive=-1;
		$invoices=$this->Invoice->find('all',array(
			'conditions'=>array(
				'Invoice.quotation_id'=>$quotation_id,
			),
		));
		if (!empty($invoices)){
			//pr($invoices);
			$invoiceSubTotal=0;
			foreach ($invoices as $invoice){
				$invoiceSubTotal+=$invoice['Invoice']['price_subtotal'];
			}
			return round(100*$invoiceSubTotal/$quotation['Quotation']['price_subtotal'],2);
		}
		else {
			$this->SalesOrder->recursive=-1;
			$salesOrders=$this->SalesOrder->find('all',array(
				'conditions'=>array(
					'SalesOrder.quotation_id'=>$quotation_id,
				),
			));
			if (!empty($salesOrders)){
				$salesOrderSubTotal=0;
				foreach ($salesOrders as $salesOrder){
					$salesOrderSubTotal+=$salesOrder['SalesOrder']['price_subtotal'];
				}
				return round(100*$salesOrderSubTotal/$quotation['Quotation']['price_subtotal'],2);
			}
			else {
				return 0;
			}
		}
		//$cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');
	}
	*/
	
  public function getQuotationBySalesOrderId($salesOrderId){
    return $this->find('first',[
      'conditions'=>[
        'Quotation.sales_order_id'=>$salesOrderId,
      ],
      'recursive'=>-1,
    ]);
  }
  
  public function getQuotationIdBySalesOrderId($salesOrderId){
    $quotation=$this->getQuotationBySalesOrderId($salesOrderId);
    if (empty($quotation)){
      return -1;
    }
    else {
      return $quotation['Quotation']['id'];
    }
  } 
  
  public function getQuotationsForClientName($clientName,$clientIds=[],$excludedQuotationIds=[]){
    return $this->find('all',[
      'conditions'=>[
        'client_name'=>$clientName,
        'client_id'=>$clientIds,
        'Quotation.id !='=>$excludedQuotationIds,
      ],
      'contain'=>[
        'ClientType',
        'Zone',
      ],
      'order'=>'quotation_date',
    ]);
  }
  
  public $validate = [
		'record_user_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
			'positive'=>[
				'rule' => ['comparison',">",'0'],
				'message' => 'Se debe seleccionar el ejecutivo',
			],
		],
		
		'quotation_date' => [
			'date' => [
				'rule' => ['date'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'quotation_code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
			//'unique' => [
			//	'rule' => 'isUnique',
			//	'message' => 'Ya existe una cotización con este número',
			//],
			'unique' => [
				'rule' => ['checkUnique',['quotation_code','record_user_id'],false],
				'message' => 'Ya existe una cotización con este número para este ejecutivo de venta',
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
			'positive'=>[
				'rule' => ['comparison',">",'0'],
				'message' => 'Se debe seleccionar la moneda',
			],
		],
	];

	public $belongsTo = [
		'RecordUser' => [
			'className' => 'User',
			'foreignKey' => 'record_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'VendorUser' => [
			'className' => 'User',
			'foreignKey' => 'vendor_user_id',
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
		'Client' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'ClientType' => [
			'className' => 'ClientType',
			'foreignKey' => 'client_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    /*
		'Contact' => [
			'className' => 'Contact',
			'foreignKey' => 'contact_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    */
		'Currency' => [
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'RejectedReason' => [
			'className' => 'RejectedReason',
			'foreignKey' => 'rejected_reason_id',
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
    'Warehouse' => [
			'className' => 'Warehouse',
			'foreignKey' => 'warehouse_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Zone' => [
			'className' => 'Zone',
			'foreignKey' => 'zone_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		/*
    'QuotationImage' => [
			'className' => 'QuotationImage',
			'foreignKey' => 'quotation_id',
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
    */
		'QuotationProduct' => [
			'className' => 'QuotationProduct',
			'foreignKey' => 'quotation_id',
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
		'QuotationRemark' => [
			'className' => 'QuotationRemark',
			'foreignKey' => 'quotation_id',
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
		'SalesOrder' => [
			'className' => 'SalesOrder',
			'foreignKey' => 'quotation_id',
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
