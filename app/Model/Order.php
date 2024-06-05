<?php
App::uses('AppModel', 'Model');

class Order extends AppModel {
	public $displayField='order_code';
  
  public function getDeletabilityEntryData($entryId){
    $boolDeletable=true;
    $message='ok';
    $productionRuns=[];
    $orders=[];
    $transfers=[];
    
    $entry=$this->find('first',[
      'conditions'=>['Order.id'=>$entryId],
      'contain'=>[
        'StockMovement'=>[
          'conditions'=>[
            'StockMovement.product_quantity >'=>0,
          ],
          'StockItem'=>[
            'ProductionMovement'=>[
              'conditions'=>[  
                'ProductionMovement.product_quantity >'=>0,
              ],  
              'ProductionRun',
            ],
            'StockMovement'=>[
              'conditions'=>[
                'StockMovement.product_quantity >'=>0,
                'StockMovement.order_id !='=>$entryId,
              ],
              'Order',
            ],
          ]
        ]
      ]
    ]);
    if (empty($entry)){
      $boolDeletable=false;
      $message='No existe la entrada';
    }
    else {
      foreach ($entry['StockMovement'] as $stockMovement){
        if (!empty($stockMovement['StockItem']['ProductionMovement'])){
          $boolDeletable=false;
          $message='Los productos de la entrada no se pueden cambiar. ';
          foreach ($stockMovement['StockItem']['ProductionMovement'] as $productionMovement){
            $productionRuns[$productionMovement['ProductionRun']['id']]=[
              'production_run_date'=>$productionMovement['ProductionRun']['production_run_date'],
              'production_run_code'=>$productionMovement['ProductionRun']['production_run_code'],
            ];
            
          }
        }
        elseif (!empty($stockMovement['StockItem']['StockMovement'])){
          $boolDeletable=false;
          $message='Los productos de la entrada no se pueden cambiar.';
          foreach ($stockMovement['StockItem']['StockMovement'] as $stockMovement){
            if (!$stockMovement['bool_transfer']){
              $orders[$stockMovement['Order']['id']]=[
                'order_date'=>$stockMovement['Order']['order_date'],
                'order_code'=>$stockMovement['Order']['order_code'],
              ];  
            }
            else {
              $transfers[$stockMovement['transfer_code']]=[
                'transfer_date'=>$stockMovement['movement_date'],
                'transfer_code'=>$stockMovement['transfer_code'],
              ];
            }
          }
        }
      }  
    }  
    return [
      'boolDeletable'=>$boolDeletable,
      'message'=>$message,
      'productionRuns'=>$productionRuns,
      'orders'=>$orders,
      'transfers'=>$transfers,
    ];
  }

  public function getOrder($orderId){
    return $this->find('first',[
      'conditions'=>['Order.id'=>$orderId],
    ]);
  }
  
  public function getEntry($entryId){
    return $this->find('first',[
      'conditions'=>['Order.id' => $entryId],
      'contain'=>[
        'PurchaseOrder',
        'PurchaseOrderInvoice',
        'StockMovement'=>[
          'Product',
        ],
        'ThirdParty',
        'Warehouse',
      ],
    ]);
  }
  
  public function getSaleClientUsers($saleId){
    return $this->find('first', [
			'conditions' => ['Order.id' => $saleId],
			'contain'=>[
				'ThirdParty'=>[
          'fields'=>[
            'ThirdParty.id, ThirdParty.company_name, ThirdParty.phone','ThirdParty.address','ThirdParty.ruc_number','ThirdParty.expiration_rate','ThirdParty.first_name','ThirdParty.last_name',
            'ThirdParty.bool_generic',
          ],
        ],
        'RecordUser'=>[
          'fields'=>['RecordUser.username','RecordUser.first_name','RecordUser.last_name']
        ],
        'VendorUser'=>[
          'fields'=>['VendorUser.username','VendorUser.first_name','VendorUser.last_name']
        ],
			],
		]);
  }
  
  public function getFullSale($saleId){
    return $this->find('first', [
			'conditions' => ['Order.id' => $saleId],
			'contain'=>[
				'ThirdParty'=>[
          'fields'=>['ThirdParty.id','ThirdParty.company_name','ThirdParty.bool_generic','ThirdParty.client_type_id'],
          'ClientType',
          'Zone',
        ],
        'RecordUser',
        'VendorUser',
        'Warehouse',
        'ClientType',
        'Zone',
        'Delivery'=>[
          'DeliveryStatus',
        ],
			],
		]);
  }
  
  public function getFullSales($warehouseId,$startDate,$endDate,$vendorUserId=0,$params=[]){
    $endDatePlusOne= date( "Y-m-d", strtotime($endDate."+1 days" ) );
    
    $orderConditions=[
      'Order.stock_movement_type_id'=> MOVEMENT_SALE,
      'Order.order_date >='=> $startDate,
      'Order.order_date <'=> $endDatePlusOne,
      'Order.warehouse_id'=> $warehouseId,
    ];
    if ($vendorUserId>0){
      $orderConditions['Order.vendor_user_id']=$vendorUserId;
    }
    if (!empty($params['client_type_id'])){
      $orderConditions['Order.client_type_id']=$params['client_type_id'];
    }
    if (!empty($params['zone_id'])){
      $orderConditions['Order.zone_id']=$params['zone_id'];
    }
    
    $containedModels=[
      'ThirdParty'=>['fields'=>[
        'id','company_name','bool_generic','client_type_id']
      ],
      'StockMovement'=>[
        'fields'=>['StockMovement.product_quantity','StockMovement.production_result_code_id',
          'StockMovement.product_total_price',
          'StockMovement.service_unit_cost','StockMovement.service_total_cost'
        ],
        'conditions'=>['StockMovement.product_quantity' > 0],
        'StockItem'=>[
          'fields'=>['product_unit_price'],
        ],
        'Product'=>[
          'fields'=>['Product.id','Product.product_nature_id'],
          'ProductType'=>[
            'fields'=>['product_category_id'],
          ],
        ],
      ],
      'Invoice'=>[
        'fields'=>[
          'Invoice.id','Invoice.invoice_code','Invoice.bool_annulled',
          'Invoice.bool_credit',
          'Invoice.currency_id','Invoice.total_price',
        ],
        'Currency'
      ],
    ];
    if ($params['bool_include_salesorder']){
      $containedModels['Invoice']['SalesOrder']=[
        'Quotation',
      ];
    }
    
    return $this->find('all',[
      'fields'=>[],
      'conditions' => $orderConditions,
      'contain'=>$containedModels,
      'order'=>'order_date DESC,order_code DESC',
    ]);
  }

  public function getOrdersForClientName($clientName,$clientIds){
    return $this->find('all',[
      'conditions'=>[
        'client_name'=>$clientName,
        'third_party_id'=>$clientIds,
      ],
      'contain'=>[
        'ClientType',
        'Zone',
        'Invoice',
      ],
      'order'=>'order_date',
    ]);
  }
  
  public function getGenericClientNames($genericClientIds){
    $duplicateGenericClientNames=$this->find('list',[
      'fields'=>['client_name'],
      'conditions'=>['third_party_id'=>$genericClientIds,],
      'order'=>'client_name'
    ]);
    return array_unique($duplicateGenericClientNames);
  }
  
  public function getSimilarClientNames($comparisonList,$clientName,$percent){
    $similarClients=[];
    foreach ($comparisonList as $comparisonClientName){
      similar_text($comparisonClientName,$clientName,$similarity);
      //echo $comparisonClientName.' has similarity '.$similarity.'<br/>';
      if ($similarity > $percent && $similarity != 100){
        $similarClients[]=$comparisonClientName;
      }
    }
    return $similarClients;
  }

  public function getPendingOrdersWithDelivery($warehouseId,$currentOrderId=0){
    $orderIdsWithRegisteredDeliveries=$this->Delivery->find('list',[
      'fields'=>['Delivery.id','Delivery.order_id'],
    ]);
      
    $conditions=[
      'Order.id !='=>$orderIdsWithRegisteredDeliveries,
      'Order.bool_annulled'=>false,
      'Order.bool_delivery'=>true,
      'Order.warehouse_id'=>$warehouseId
    ];
    if ($currentOrderId>0){
     $conditions=[
      'OR'=>[
        [
          'Order.id !='=>$orderIdsWithRegisteredDeliveries,
          'Order.bool_annulled'=>false,
          'Order.bool_delivery'=>true,
          'Order.warehouse_id'=>$warehouseId,
        ],
        [
          'Order.id'=>$currentOrderId,  
        ],
      ],
     ];
    }
    //pr($conditions);
    $allPendingOrdersWithDelivery=$this->find('all',[
      'conditions'=>$conditions,
      'recursive'=>-1,
      'order'=>'Order.id DESC',
    ]);
    //pr($allPendingSalesOrders);
    $orders=[];
    if (!empty($allPendingOrdersWithDelivery)){
      foreach ($allPendingOrdersWithDelivery as $order){
        $orders[$order['Order']['id']]=($order['Order']['client_name']." (".$order['Order']['order_code'].")");
      }
    }
    return $orders;
  }
	

	public $validate = [
		'order_code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
			],
		],
		'third_party_id' => [
			'numeric' => [
				'rule' => ['comparison','>',0],
			],
		],
	];

	public $belongsTo = [
    'ClientType' => [
			'className' => 'ClientType',
			'foreignKey' => 'client_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'PaymentUser' => [
			'className' => 'User',
			'foreignKey' => 'payment_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'PurchaseOrder' => [
			'className' => 'PurchaseOrder',
			'foreignKey' => 'purchase_order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'StockMovementType' => [
			'className' => 'StockMovementType',
			'foreignKey' => 'stock_movement_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'ThirdParty' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'third_party_id',
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
		'Invoice' => [
			'className' => 'Invoice',
			'foreignKey' => 'order_id',
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
    'StockMovement' => [
			'className' => 'StockMovement',
			'foreignKey' => 'order_id',
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
		'PurchaseOrderInvoice' => [
			'className' => 'PurchaseOrderInvoice',
			'foreignKey' => 'entry_id',
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
		'CashReceipt' => [
			'className' => 'CashReceipt',
			'foreignKey' => 'order_id',
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
			'foreignKey' => 'order_id',
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
