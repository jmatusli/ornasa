<?php
App::uses('AppModel', 'Model');

class SalesOrder extends AppModel {

	public $displayField='sales_order_code';

  public function getSalesOrderCode($warehouseId,$warehouseSeries,$userAbbreviation=""){
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
      $lastSalesOrderCodeNumber=(int)substr($lastSalesOrder['SalesOrder']['sales_order_code'],6,12);
      $newSalesOrderCodeNumber=$lastSalesOrderCodeNumber+1;
      $newSalesOrderCode=$warehouseSeries.'_OV_'.str_pad($newSalesOrderCodeNumber,6,'0',STR_PAD_LEFT);
		}
		else {
			$newSalesOrderCode=$warehouseSeries."_OV_000001";
		}
    if (!empty($userAbbreviation)){
      $newSalesOrderCode.='_'.$userAbbreviation;
    }
    return $newSalesOrderCode;
  }

  public function getPendingSalesOrders($warehouseId=0,$currentSalesOrderId=0){
    $conditions=[
      'SalesOrder.bool_invoice'=>false,
      'SalesOrder.bool_annulled'=>false,
     
    ];
    if ($currentSalesOrderId > 0){
     $conditions['SalesOrder.warehouse_id']=$warehouseId;
    }
    if ($currentSalesOrderId > 0){
     $conditions=[
      'OR'=>[
        [
          'SalesOrder.bool_invoice'=>false,
          'SalesOrder.bool_annulled'=>false,
          'SalesOrder.warehouse_id'=>$warehouseId,
        ],
        [
          'SalesOrder.id'=>$currentSalesOrderId,  
        ],
      ],
     ];
    }
    //pr($conditions);
    $allPendingSalesOrders=$this->find('all',[
      'conditions'=>$conditions,
      'recursive'=>-1,
      'order'=>'SalesOrder.id DESC',
    ]);
    //pr($allPendingSalesOrders);
    $salesOrders=[];
    if (!empty($allPendingSalesOrders)){
      foreach ($allPendingSalesOrders as $salesOrder){
        $salesOrders[$salesOrder['SalesOrder']['id']]=($salesOrder['SalesOrder']['client_name']." (".$salesOrder['SalesOrder']['sales_order_code'].")");
      }
    }
    return $salesOrders;
  }
	
  public function getPendingSalesOrdersWithDelivery($warehouseId,$currentSalesOrderId=0){
    $salesOrderIdsWithRegisteredDeliveries=$this->Delivery->find('list',[
      'fields'=>['Delivery.id','Delivery.sales_order_id'],
    ]);
    
    
    $conditions=[
      'SalesOrder.id !='=>$salesOrderIdsWithRegisteredDeliveries,
      'SalesOrder.bool_invoice'=>false,
      'SalesOrder.bool_annulled'=>false,
      'SalesOrder.bool_delivery'=>true,
      'SalesOrder.warehouse_id'=>$warehouseId
    ];
    if ($currentSalesOrderId>0){
     $conditions=[
      'OR'=>[
        [
          'SalesOrder.id !='=>$salesOrderIdsWithRegisteredDeliveries,
          'SalesOrder.bool_invoice'=>false,
          'SalesOrder.bool_annulled'=>false,
          'SalesOrder.bool_delivery'=>true,
          'SalesOrder.warehouse_id'=>$warehouseId,
        ],
        [
          'SalesOrder.id'=>$currentSalesOrderId,  
        ],
      ],
     ];
    }
    //pr($conditions);
    $allPendingSalesOrdersWithDelivery=$this->find('all',[
      'conditions'=>$conditions,
      'recursive'=>-1,
      'order'=>'SalesOrder.id DESC',
    ]);
    //pr($allPendingSalesOrders);
    $salesOrders=[];
    if (!empty($allPendingSalesOrdersWithDelivery)){
      foreach ($allPendingSalesOrdersWithDelivery as $salesOrder){
        $salesOrders[$salesOrder['SalesOrder']['id']]=($salesOrder['SalesOrder']['client_name']." (".$salesOrder['SalesOrder']['sales_order_code'].")");
      }
    }
    return $salesOrders;
  }
	
  
  public function getSalesOrder($id){
    return $this->find('first',[
      'conditions'=>[
        'SalesOrder.id'=>$id,
      ],
      'recursive'=>-1,
    ]);
  }
  
  public function getSalesOrderByOrderId($invoiceId){
    return $this->find('first',[
      'conditions'=>[
        'SalesOrder.invoice_id'=>$invoiceId,
      ],
      'recursive'=>-1,
    ]);
  }
  
  public function getSalesOrderIdByOrderId($invoiceId){
    $salesOrder=$this->getSalesOrderByOrderId($invoiceId);
    if (empty($salesOrder)){
      return -1;
    }
    else {
      return $salesOrder['SalesOrder']['id'];
    }
  }
  
  public function getSalesOrdersForClientName($clientName,$clientIds=[],$excludedSalesOrderIds=[]){
    return $this->find('all',[
      'conditions'=>[
        'client_name'=>$clientName,
        'client_id'=>$clientIds,
        'SalesOrder.id !='=>$excludedSalesOrderIds,
      ],
      'contain'=>[
        'ClientType',
        'Zone',
      ],
      'order'=>'sales_order_date',
    ]);
  }
  
  public function getSalesOrderWarehouse($salesOrderId){
    $salesOrder=$this->getSalesOrder($salesOrderId);
    return empty($salesOrder)?0:$salesOrder['SalesOrder']['warehouse_id'];
  }
  public $validate = [
		'sales_order_date' => [
			'date' => [
				'rule' => ['date'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'sales_order_code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
			'unique' => [
				'rule' => 'isUnique',
				'message' => 'Ya existe una orden de venta con este código',
			],
		],
	];


	public $belongsTo = [
		'Quotation' => [
			'className' => 'Quotation',
			'foreignKey' => 'quotation_id',
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
    'Currency' => [
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Invoice' => [
			'className' => 'Invoice',
			'foreignKey' => 'invoice_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
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
    'AuthorizationUser' => [
			'className' => 'User',
			'foreignKey' => 'authorization_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
  /*  
    'DriverUser' => [
			'className' => 'User',
			'foreignKey' => 'driver_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Vehicle' => [
			'className' => 'Vehicle',
			'foreignKey' => 'vehicle_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
  */
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
		'SalesOrderProduct' => [
			'className' => 'SalesOrderProduct',
			'foreignKey' => 'sales_order_id',
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
    'SalesOrderRemark' => [
			'className' => 'SalesOrderRemark',
			'foreignKey' => 'sales_order_id',
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
    'Delivery' => [
			'className' => 'Delivery',
			'foreignKey' => 'sales_order_id',
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
