<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class SalesOrdersController extends AppController {

	public $components = ['Paginator','RequestHandler'];
	public $helpers = ['PhpExcel'];

	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow('getSalesOrderCode','getSalesOrderInfo','getSalesOrderProducts','crearOrdenVentaExterna','confirmacionOrdenDeVenta','getSalesOrdersForClient','detallePdf');		
	}
  
  public function getSalesOrderCode(){
    $this->autoRender = false; 
		$this->request->onlyAllow('ajax'); 
		$this->layout = "ajax";
		$warehouseId=trim($_POST['warehouseId']);
    $userId=trim($_POST['userId']);
    
    $this->loadModel('Warehouse');
    $this->loadModel('User');
    $warehouseSeries=$this->Warehouse->getWarehouseSeries($warehouseId);
    
    $userAbbreviation = $this->User->getUserAbbreviation($userId);
    
    return $this->SalesOrder->getSalesOrderCode($warehouseId,$warehouseSeries,$userAbbreviation);
  }
  
  public function getSalesOrderInfo() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax'); 
		$this->layout = "ajax";
		
		$salesOrderId=trim($_POST['salesOrderId']);
		
		$this->SalesOrder->recursive=-1;
		$salesOrder=$this->SalesOrder->find('first',[
			'conditions'=>['SalesOrder.id'=> $salesOrderId],
      'contain'=>['Client'],
		]);
		return json_encode($salesOrder);
	}

  public function getSalesOrderProducts() {
		$this->layout = "ajax";
		
    $salesOrderId=$_POST['salesOrderId'];
    $warehouseId=$_POST['warehouseId'];
		$currencyId=trim($_POST['currencyId']);
		$exchangeRate=trim($_POST['exchangeRate']);
    
    $orderDay=trim($_POST['orderDay']);
    $orderMonth=trim($_POST['orderMonth']);
    $orderYear=trim($_POST['orderYear']);
    
    $this->set(compact('salesOrderId'));
		$this->set(compact('currencyId','exchangeRate'));
		
    if (!empty($salesOrderId)){
      $this->loadModel('SalesOrderProduct');
      $this->loadModel('Product');
      
      $this->loadModel('StockItem');
      
      $orderDateString=$orderYear.'-'.$orderMonth.'-'.$orderDay;
      $orderDate=date( "Y-m-d", strtotime($orderDateString));
      
      $salesOrderProductConditions=[
        'SalesOrderProduct.sales_order_id'=>$salesOrderId,
      ];
      //pr($productConditions);
      $productsForSalesOrder=$this->SalesOrderProduct->find('all',[
        'fields'=>[
          'SalesOrderProduct.id',
          'SalesOrderProduct.product_id','SalesOrderProduct.raw_material_id','SalesOrderProduct.product_unit_price',
          'SalesOrderProduct.product_quantity',
          'SalesOrderProduct.product_total_price',
          'SalesOrderProduct.currency_id',
        ],
        'recursive'=>-1,
        'conditions'=>$salesOrderProductConditions,
      ]);
      //pr($productsForSalesOrder);
      if (!empty($productsForSalesOrder)){
        for ($so=0;$so<count($productsForSalesOrder);$so++){
          $productId=$productsForSalesOrder[$so]['SalesOrderProduct']['product_id'];
          $rawMaterialId=$productsForSalesOrder[$so]['SalesOrderProduct']['raw_material_id'];
          $productInventory=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$orderDate,$warehouseId,$rawMaterialId);
          $productCost=0;
          if ($productInventory['quantity'] > 0){
            $productCost=round($productInventory['value']/$productInventory['quantity'],2);
          }
          $productsForSalesOrder[$so]['SalesOrderProduct']['product_unit_cost']=$productCost;
        } 
        //pr($productsForSalesOrder);
      }
      $this->set(compact('productsForSalesOrder'));
    }  
    
    $this->loadModel('ProductType');
    $this->loadModel('Product');
    $this->loadModel('ProductionResultCode');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $stockItemConditions=[
      'StockItem.bool_active'=>true,
      'StockItem.warehouse_id'=>[$warehouseId,WAREHOUSE_INJECTION],
    ];
    $stockItemConditions[]=[];
    $excludedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>['ProductType.product_category_id'=>[CATEGORY_RAW,CATEGORY_CONSUMIBLE]],
    ]);
    
		$productsAll = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
      'conditions'=>[
        'Product.bool_active'=>true,
        'Product.product_type_id !='=>$excludedProductTypeIds,
      ],
			'contain'=>[
				'ProductType',
				'StockItem'=>[
					'fields'=> ['remaining_quantity','raw_material_id','production_result_code_id'],
          'conditions'=>$stockItemConditions,
				],
			],
			'order'=>'product_type_id DESC, name ASC',
		]);
		$products = [];
		$rawMaterialIds=[];
    $rawMaterialsAvailablePerFinishedProduct=[];

		foreach ($productsAll as $product){
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockItem){
					if ($stockItem['remaining_quantity']>0){
            $productId=$product['Product']['id'];
						$products[$productId]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
            if (!empty($stockItem['raw_material_id'])){
              $rawMaterialId=$stockItem['raw_material_id'];
              $productionResultCodeId=$stockItem['production_result_code_id'];
              if (!in_array($rawMaterialId,$rawMaterialIds)){
                $rawMaterialIds[]=$rawMaterialId;
              }
              if (!array_key_exists($productId,$rawMaterialsAvailablePerFinishedProduct)){
                $rawMaterialsAvailablePerFinishedProduct[$productId]=[];
              }
              if (!array_key_exists($rawMaterialId,$rawMaterialsAvailablePerFinishedProduct[$productId])){
                $rawMaterialsAvailablePerFinishedProduct[$productId][$rawMaterialId]=[
                  '1'=>0,
                  '2'=>0,
                  '3'=>0
                ];
              }
              $rawMaterialsAvailablePerFinishedProduct[$productId][$rawMaterialId][$productionResultCodeId]+=$stockItem['remaining_quantity'];
            }  
          }            
				}
			}
      elseif ($product['ProductType']['id'] == PRODUCT_TYPE_SERVICE){
        $products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
      }
		}
		$this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    
		$productionResultCodes=$this->ProductionResultCode->find('list',[
      'conditions'=>['ProductionResultCode.id'=>PRODUCTION_RESULT_CODE_A]
    ]);
    
    $this->Product->recursive=-1;
		$preformasAll = $this->Product->find('all',[
      'fields'=>['Product.id','Product.name'],
      'conditions' => [
       'Product.id'=>$rawMaterialIds,
       'Product.bool_active'=>true
      ],
      'order'=>'Product.name',
		]);
    
		$rawMaterials=[];
		foreach ($preformasAll as $preforma){
      $startingPosition=0;
      if (strpos ($preforma['Product']['name'],"PREFORMA") !== false){
        $preforma['Product']['name']=str_replace("PREFORMA ","",$preforma['Product']['name']);
      }
			$rawMaterials[$preforma['Product']['id']]=substr($preforma['Product']['name'],0,18).(strlen($preforma['Product']['name'])>18?"...":"");
		}
    //pr($rawMaterials);
    $this->set(compact('products','rawMaterials','productionResultCodes'));
    
    $otherProducts=$this->Product->find('list',[
      'fields'=>'Product.id',
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_SERVICE]
    ]);
    $otherProducts=array_values($otherProducts);
    $this->set(compact('otherProducts'));
    
    $this->loadModel('UserAction');
    $userActions=$this->UserAction->find('all',[
      'conditions'=>[
        'UserAction.controller_name'=>'salesOrders',
        'UserAction.item_id'=>$salesOrderId,
      ],
      'contain'=>['User'],
      'limit'=>1,
      'order'=>'action_datetime DESC',
    ]);
    //pr($userActions);
    $lastModifyingUserRoleId = 0;
    if (!empty($userActions) && ($userActions[0]['UserAction']['action_name'] === "crearOrdenVentaExterna" || $userActions[0]['UserAction']['action_name'] === "editarOrdenVentaExterna")){
        $lastModifyingUserRoleId=$userActions[0]['User']['role_id'];
    }
    $this->set(compact('lastModifyingUserRoleId'));  
          
	}
	
	public function resumen() {
    $this->loadModel('Quotation');
    $this->loadModel('ExchangeRate');
    $this->loadModel('Currency');
    
    $this->loadModel('ClientType');
		$this->loadModel('Zone');
    $this->loadModel('Vehicle');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    $this->Quotation->recursive=-1;
		$this->SalesOrder->recursive = -1;
    $this->User->recursive = -1;
		
		//echo "user id in session is ".$_SESSION['userId']."<br/>";
		$loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $canSeeExecutiveSummary=$this->UserPageRight->hasUserPageRight('VER_RESUMEN_EJECUTIVO',$userRoleId,$loggedUserId,'SalesOrders','resumen');
    $this->set(compact('canSeeExecutiveSummary'));
        
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'SalesOrders','resumen');
    $this->set(compact('canSeeAllUsers'));
    
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'SalesOrders','resumen');
    $this->set(compact('canSeeAllVendors'));
    
    define('SALESORDERS_ALL',0);
    define('SALESORDERS_WITH_INVOICE',1);
    define('SALESORDERS_WITHOUT_INVOICE',2);
    
    $invoiceOptions=[
      SALESORDERS_ALL=>"Mostrar todas ordenes de venta (con y sin factura)",
      SALESORDERS_WITH_INVOICE=>"Mostrar solamente ordenes de venta con factura",
      SALESORDERS_WITHOUT_INVOICE=>"Mostrar solamente ordenes de venta sin factura"
    ];
		$this->set(compact('invoiceOptions'));
    
    define('SALESORDERS_WITH_AUTHORIZATION',1);
    define('SALESORDERS_WITHOUT_AUTHORIZATION',2);
    
		$authorizedOptions=[
      SALESORDERS_ALL=>"Mostrar todas ordenes de venta (autorizadas o no)",
      SALESORDERS_WITH_AUTHORIZATION=>"Mostrar solamente ordenes de venta autorizadas",
      SALESORDERS_WITHOUT_AUTHORIZATION=>"Mostrar solamente ordenes de venta no autorizadas",
    ];
		$this->set(compact('authorizedOptions'));
		
    $invoiceDisplay=0;
		$authorizedDisplay=0;
    
    $userId=$loggedUserId;
		$currencyId=CURRENCY_USD;
    
    $clientTypeId=0;
    $zoneId=0;
    $driverUserId=0;
    $vehicleId=0;
    
    $warehouseId=0;
    
		if ($userRoleId == ROLE_ADMIN  || $canSeeAllUsers || $canSeeAllVendors){
      $userId=0;
    } 
		if ($this->request->is('post')) {
			if (!empty($this->request->data['authorize_all'])){
				//pr($this->request->data);
				foreach ($this->request->data['Report']['selector'] as $salesOrderId=>$checked){
					if ($checked){
						$salesOrder=$this->SalesOrder->find('first',[
							'conditions'=>[
								'SalesOrder.id'=>$salesOrderId,
							],
						]);
						if (!$salesOrder['SalesOrder']['bool_authorized']){
							$datasource=$this->SalesOrder->getDataSource();
							$datasource->begin();
							try {
								//pr($this->request->data);
								$this->SalesOrder->id=$salesOrder['SalesOrder']['id'];
                
								$salesOrderArray=[
                  'SalesOrder'=>[
                    'id'=>$salesOrder['SalesOrder']['id'],
                    'bool_authorized'=>true,
                    'authorization_user_id'=>$loggedUserId,
                  ],
                ];
								if (!$this->SalesOrder->save($salesOrderArray)) {
									echo "Problema al autorizar la orden de venta";
									pr($this->validateErrors($this->SalesOrder));
									throw new Exception();
								}
								
								$this->loadModel('SalesOrderProduct');
								$this->SalesOrderProduct->recursive=-1;
								$salesOrderProducts=$this->SalesOrderProduct->find('all',[
									'fields'=>['SalesOrderProduct.id','SalesOrderProduct.bool_no_production'],
									'conditions'=>[
										'SalesOrderProduct.sales_order_id'=>$salesOrder['SalesOrder']['id'],
									],
								]);
								if (!empty($salesOrderProducts)){
									foreach ($salesOrderProducts as $salesOrderProduct){
										$this->SalesOrderProduct->id=$salesOrderProduct['SalesOrderProduct']['id'];
										$salesOrderProductArray=[];
										if (!$this->SalesOrderProduct->save($salesOrderProductArray)) {
											echo "Problema al cambiar el estado de los productos de la orden de venta a autorizado";
											pr($this->validateErrors($this->SalesOrderProduct));
											throw new Exception();
										}
									}
								}
											
								$datasource->commit();
								$flashMessage="La orden de venta ".$salesOrder['SalesOrder']['sales_order_code']." se ha autorizada.";
								$this->Session->setFlash($flashMessage,'default',['class' => 'success']);
							}
							catch(Exception $e){
								$this->Session->setFlash(__('La orden de venta no se podía autorizar.'), 'default',['class' => 'error-message']);
							}
						}	
					}
				}
			}
		
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
      $warehouseId=$this->request->data['Report']['warehouse_id'];
			$userId=$this->request->data['Report']['user_id'];
			$currencyId=$this->request->data['Report']['currency_id'];
			
			$clientTypeId=$this->request->data['Report']['client_type_id'];
      $zoneId=$this->request->data['Report']['zone_id'];
      $driverUserId=$this->request->data['Report']['driver_user_id'];
      $vehicleId=$this->request->data['Report']['vehicle_id'];
			
      $invoiceDisplay=$this->request->data['Report']['invoice_display'];
			$authorizedDisplay=$this->request->data['Report']['authorized_display'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			//echo "retrieving values from session<br/>";
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			if ($this->Session->check('currencyId')){
				$currencyId=$_SESSION['currencyId'];
			}
			if ($this->Session->check('invoiceDisplay')){
				$invoiceDisplay=$_SESSION['invoiceDisplay'];
			}
			if ($this->Session->check('authorizedDisplay')){
				$authorizedDisplay=$_SESSION['authorizedDisplay'];
			}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$_SESSION['currencyId']=$currencyId;
		$_SESSION['userId']=$userId;
    $_SESSION['invoiceDisplay']=$invoiceDisplay;
		$_SESSION['authorizedDisplay']=$authorizedDisplay;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('userId','currencyId'));
		$this->set(compact('clientTypeId'));
    $this->set(compact('zoneId'));
    $this->set(compact('driverUserId'));
		$this->set(compact('vehicleId'));
		$this->set(compact('invoiceDisplay'));
		$this->set(compact('authorizedDisplay'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    elseif (count($warehouses) > 1 && $warehouseId == 0){
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      elseif (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
        $warehouseId = WAREHOUSE_DEFAULT;
      }
      else {
        $warehouseId=0;
      }
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    
		//echo "user id is ".$userId."<br/>";
		$conditions=[
			'SalesOrder.sales_order_date >='=>$startDate,
			'SalesOrder.sales_order_date <'=>$endDatePlusOne,
      'SalesOrder.warehouse_id'=>$warehouseId,
		];
    if ($clientTypeId>0){
      $conditions['SalesOrder.client_type_id']=$clientTypeId;
    }
    if ($zoneId>0){
      $conditions['SalesOrder.zone_id']=$zoneId;
    }
    if ($vehicleId>0){
      $conditions['SalesOrder.vehicle_id']=$vehicleId;
    }
    if ($driverUserId>0){
      $conditions['SalesOrder.driver_user_id']=$driverUserId;
    }
    $userConditions=['User.bool_active'=>true];
		if ($userRoleId!=ROLE_ADMIN && !$canSeeAllUsers && !$canSeeAllVendors){
      $userConditions['User.id']=$loggedUserId;
		}
		$userPeriod=$this->User->find('list',[
			'conditions'=>$userConditions,
			'order'=>'User.username'
		]);
		
    foreach ($userPeriod as $key=>$value){
        $userPeriod[$key]=0;
    }
    //pr($userPeriod);
    
    $userPendingCS=$userPendingUSD=$userPeriodCS=$userPeriodUSD=$userPeriod;
    
		if ($userId > 0) { 
			$quotationList=$this->Quotation->find('list',[
				'fields'=>'Quotation.id',
				'conditions'=>[
					'Quotation.vendor_user_id'=>$userId,	
				],
			]);
			$conditions['SalesOrder.quotation_id']=$quotationList;
		}
    //pr($conditions);
    // filter conditions are applied in view
    /*
    switch ($invoiceDisplay){
      case SALESORDERS_ALL:
        break;
      case SALESORDERS_WITH_INVOICE:
        $conditions['SalesOrder.bool_invoice']=true;
        break;
      case SALESORDERS_WITHOUT_INVOICE:
        $conditions['SalesOrder.bool_invoice']='0';
    }
    switch ($authorizedDisplay){
      case SALESORDERS_ALL:
        break;
      case SALESORDERS_WITH_AUTHORIZATION:
          $conditions['SalesOrder.bool_authorized']=true;
        break;
      case SALESORDERS_WITHOUT_AUTHORIZATION:
        $conditions['SalesOrder.bool_authorized']='0';
    }
		*/
		$salesOrderCount=$this->SalesOrder->find('count', [
			'fields'=>['SalesOrder.id'],
			'conditions' => $conditions,
		]);
		
		$this->Paginator->settings = [
			'conditions' => $conditions,
			'contain'=>[
				'Client',
        'ClientType'=>[
          'fields'=>['ClientType.id','ClientType.name','ClientType.hex_color',]
        ],
				'Currency',
				'Invoice',
        'Quotation',
        'VendorUser',
				'SalesOrderProduct'=>[
					'fields'=>[
						'SalesOrderProduct.id','SalesOrderProduct.product_total_price',
					],
				],
			],
			'order'=>'SalesOrder.sales_order_date DESC,SalesOrder.sales_order_code DESC',
			'limit'=>($salesOrderCount!=0?$salesOrderCount:1),
		];

		$salesOrders = $this->Paginator->paginate('SalesOrder');
		if (!empty($salesOrders)){
			for ($i=0;$i<count($salesOrders);$i++){
				// get the exchange rate
				$salesOrderDate=$salesOrders[$i]['SalesOrder']['sales_order_date'];
				$salesOrders[$i]['SalesOrder']['exchange_rate']=$this->ExchangeRate->getApplicableExchangeRateValue($salesOrderDate);
				// get the status
				$status=0;
				if (!empty($salesOrders[$i]['SalesOrderProduct'])){
					//if ($salesOrders[$i]['SalesOrder']['id']==52){
					//	pr($salesOrders[$i]['SalesOrderProduct']);
					//}
					//echo "sales order product not empty<br/>";
					$totalPriceProducts=0;
					$totalPriceSoldProducts=0;
					foreach ($salesOrders[$i]['SalesOrderProduct'] as $salesOrderProduct){
						$totalPriceProducts+=$salesOrderProduct['product_total_price'];
					}
          if (!empty($salesOrders[$i]['Quotation']) && !empty($salesOrders[$i]['Quotation']['User'])){
            if ($salesOrders[$i]['Currency']['id'] == CURRENCY_CS){
            
              $userPeriodCS[$salesOrders[$i]['Quotation']['User']['id']]+=$salesOrders[$i]['SalesOrder']['price_subtotal'];
              $userPeriodUSD[$salesOrders[$i]['Quotation']['User']['id']]+=round($salesOrders[$i]['SalesOrder']['price_subtotal']/$salesOrders[$i]['SalesOrder']['exchange_rate'],2);
            }
            elseif ($salesOrders[$i]['Currency']['id'] == CURRENCY_USD){
              $userPeriodUSD[$salesOrders[$i]['Quotation']['User']['id']]+=$salesOrders[$i]['SalesOrder']['price_subtotal'];
              $userPeriodCS[$salesOrders[$i]['Quotation']['User']['id']]+=round($salesOrders[$i]['SalesOrder']['price_subtotal']*$salesOrders[$i]['SalesOrder']['exchange_rate'],2);
            }
          }
				}
				//$salesOrders[$i]['SalesOrder']['status']=$status;
			}
		}
		$this->set(compact('salesOrders'));
    //pr($salesOrders);
    $this->set(compact('userPeriodCS','userPeriodUSD'));
		
		if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      $users=$this->User->getActiveVendorAdminUserList($warehouseId);
    }
    elseif ($canSeeAllVendors) {
      $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    }
    else {
      $users=$this->User->getActiveUserList($loggedUserId);
    }
		$this->set(compact('users'));
		
		$currencies=$this->Currency->find('list');
		$this->set(compact('currencies'));
		
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $clientTypeHexColors=$this->ClientType->getClientTypeHexColors();
    $this->set(compact('clientTypeHexColors'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
    $driverUsers=$this->User->getActiveUsersForRole(ROLE_DRIVER,$warehouseId);
    $this->set(compact('driverUsers'));
    $vehicles=$this->Vehicle->getVehicleList($warehouseId);
    $this->set(compact('vehicles'));
    
		$aco_name="SalesOrders/autorizar";		
		$bool_autorizar_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_autorizar_permission'));
		$aco_name="SalesOrders/cambiarEstado";		
		$bool_cambiarestado_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_cambiarestado_permission'));
		
    $aco_name="Quotations/resumen";		
    $bool_quotation_index_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_index_permission'));
		$aco_name="Quotations/crear";		
		$bool_quotation_add_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_add_permission'));
    
    $aco_name="SalesOrders/crearOrdenVentaExterna";		
		$bool_crear_orden_venta_externa_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_crear_orden_venta_externa_permission'));
	}

	public function guardarResumen() {
		$exportData=$_SESSION['resumen'];
		$this->set(compact('exportData'));
	}

	public function detalle($id = null) {
		if (!$this->SalesOrder->exists($id)) {
			throw new NotFoundException(__('Orden de venta inexistente'));
		}
    $this->SalesOrder->recursive=-1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
		$options = [
			'conditions' => [
				'SalesOrder.id' => $id,
			],
			'contain'=>[
				'Client'=>[
          'ClientType',
          'Zone',
        ],
        'ClientType'=>[
          'fields'=>['ClientType.id','ClientType.name','ClientType.hex_color',]
        ],
        'Currency',
        'Invoice',
				'Quotation'=>[
					'Client',
					'VendorUser',
				],
				'SalesOrderProduct'=>[
					'Product',
          'RawMaterial',
					'Currency',
				],
        'RecordUser',
        'VendorUser',
        'CreditAuthorizationUser',
        //'AuthorizationUser', 
        //'DriverUser', 
        //'Vehicle', 
        'Warehouse',
        'Zone',
        
        'Delivery'=>[
          'DeliveryStatus',
        ],
			],
		];
		
		$salesOrder=$this->SalesOrder->find('first', $options);
    
		$this->set(compact('salesOrder'));
    //pr($salesOrder);
		
		$fileName="Orden_de_Venta_".$salesOrder['SalesOrder']['sales_order_code']."_".((empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic'])?$salesOrder['SalesOrder']['client_name']:$salesOrder['Client']['company_name']);
		$this->set(compact('fileName'));
    
    $aco_name="SalesOrders/editarOrdenVentaExterna";		
		$bool_edit_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_edit_permission'));
    //echo "bool edit permission is ".$bool_edit_permission."<br/>";
    
    $aco_name="Orders/crearVenta";		
    $bool_crearVenta_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_crearVenta_permission'));
		
    
    $aco_name="Quotations/resumen";		
    $bool_quotation_index_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_index_permission'));
		$aco_name="Quotations/crear";		
		$bool_quotation_add_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_add_permission'));
	}
	
  public function detallePdf($id = null) {
		if (!$this->SalesOrder->exists($id)) {
			throw new NotFoundException(__('Orden de Venta no válida'));
		}

		$options = [
			'conditions' => [
				'SalesOrder.id' => $id,
			],
			'contain'=>[
				'Client',
        'Currency',
        'Invoice',
				'Quotation'=>[
					'Client',
					'VendorUser',
				],
				'SalesOrderProduct'=>[
					'Product',
          'RawMaterial',
					'Currency',
				],
        'RecordUser',
        'VendorUser',
        'CreditAuthorizationUser',
        //'AuthorizationUser', 
        //'DriverUser', 
        //'Vehicle', 
        'Warehouse',
			],
		];
		
		$salesOrder=$this->SalesOrder->find('first', $options);
		$this->set(compact('salesOrder'));
		
		$dueDate= new DateTime($salesOrder['Quotation']['due_date']);
		$quotationDate= new DateTime($salesOrder['Quotation']['quotation_date']);
		$daysValid=$quotationDate->diff($dueDate);
		$validityQuotation=(int)$daysValid->format("%r%a");
		$this->set(compact('validityQuotation'));
		
		$filename="Orden_de_Venta_".$salesOrder['SalesOrder']['sales_order_code']."_".((empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic'])?$salesOrder['SalesOrder']['client_name']:$salesOrder['Client']['company_name']);
		$this->set(compact('filename'));
	}

  public function confirmacionOrdenDeVenta($id = null) {
		if (!$this->SalesOrder->exists($id)) {
			throw new NotFoundException(__('Invalid sales order'));
		}
    $this->SalesOrder->recursive=-1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
		$options = [
			'conditions' => [
				'SalesOrder.id' => $id,
			],
			'contain'=>[
				'AuthorizingUser', 
				'Currency',
				'Quotation'=>[
					'Client',
					'User',
				],
				'SalesOrderProduct'=>[
					'Product',
          'RawMaterial',
					'Currency',
				],
			],
		];
		
		$salesOrder=$this->SalesOrder->find('first', $options);
		$this->set(compact('salesOrder'));
		
		//$fileName="Orden_de_Venta_".$salesOrder['SalesOrder']['sales_order_code']."_".((empty($salesOrder['Client']['id']) || $salesOrder['Client']['bool_generic'])?$salesOrder['SalesOrder']['client_name']:$salesOrder['Client']['company_name']);
		//$this->set(compact('fileName'));
    
    
	}
	
	public function crearOrdenVentaExterna($quotationId=0) {
		$this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('StockItem');
    
    $this->loadModel('ClosingDate');
    
    $this->loadModel('Quotation');
    
    $this->loadModel('SalesOrderProduct');
    $this->loadModel('SalesOrderRemark');
		
    $this->loadModel('ThirdParty');
    $this->loadModel('ActionType');
		
    $this->loadModel('ClientType');
		$this->loadModel('Zone');
		$this->loadModel('PriceClientCategory');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $canSeeInventoryCost=$this->UserPageRight->hasUserPageRight('VER_COSTO_INVENTARIO',$userRoleId,$loggedUserId,'All','All');
    $this->set(compact('canSeeInventoryCost'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'SalesOrders','resumen');
    $this->set(compact('canSeeAllUsers'));
    
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'SalesOrders','resumen');
    $this->set(compact('canSeeAllVendors'));
    
    
    $salesOrderDate=date( "Y-m-d");
    $clientId=0;
    $currencyId=CURRENCY_CS;
    $vendorUserId=$loggedUserId;
    $recordUserId=$loggedUserId;
    $creditAuthorizationUserId=$loggedUserId;
    //$driverUserId=0;
    //$vehicleId=0;
    $warehouseId=0;
    $boolDelivery='0';
    
    if ($userRoleId == ROLE_CLIENT){
      $userForExternalClient=$this->User->find('first',[
        'conditions'=>['User.id'=>$loggedUserId],
      ]);  
      $clientId=$userForExternalClient['User']['client_id'];
    }
    
    $genericClientIds=$this->ThirdParty->getGenericClientIds();
    $this->set(compact('genericClientIds'));
    
		$boolInitialLoad=true;
    $requestProducts=[];
    if ($this->request->is('post')) {
      $boolInitialLoad='0';
      //pr($this->request->data);
      foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
        if ($salesOrderProduct['product_id']>0 && $salesOrderProduct['product_quantity']>0 && $salesOrderProduct['product_unit_price']>0){
          $requestProducts[]['SalesOrderProduct']=$salesOrderProduct;
        }
      }
      
      $quotationId=$this->request->data['SalesOrder']['quotation_id'];
      
      $salesOrderDateArray=$this->request->data['SalesOrder']['sales_order_date'];
      //pr($salesOrderDateArray);
      $salesOrderDateString=$salesOrderDateArray['year'].'-'.$salesOrderDateArray['month'].'-'.$salesOrderDateArray['day'];
      $salesOrderDate=date( "Y-m-d", strtotime($salesOrderDateString));
      //$productTypeId=$this->request->data['SalesOrder']['product_type_id'];
      $clientId=$this->request->data['SalesOrder']['client_id'];
      $currencyId=$this->request->data['SalesOrder']['currency_id'];
      $vendorUserId=$this->request->data['SalesOrder']['vendor_user_id'];
      $recordUserId=$this->request->data['SalesOrder']['record_user_id'];
      if (array_key_exists('credit_authorization_user_id',$this->request->data['SalesOrder'])){
        $creditAuthorizationUserId=$this->request->data['SalesOrder']['credit_authorization_user_id'];
      }
      //$driverUserId=$this->request->data['SalesOrder']['driver_user_id'];
      //$vehicleId=$this->request->data['SalesOrder']['vehicle_id'];
      
      $warehouseId=$this->request->data['SalesOrder']['warehouse_id'];
      
      $boolDelivery=$this->request->data['SalesOrder']['bool_delivery'];
    }
    
    $salesOrderDatePlusOne=date( "Y-m-d", strtotime($salesOrderDate."+1 days"));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    elseif (count($warehouses) > 1 && $warehouseId == 0){
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      elseif (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
        $warehouseId = WAREHOUSE_DEFAULT;
      }
      else {
        $warehouseId=0;
      }
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));    
    $plantId=$this->Warehouse->getPlantId($warehouseId);
    $this->set(compact('plantId'));
    
    if ($this->request->is('post') && empty($this->request->data['updateWarehouse']) && empty($this->request->data['changeProductType']) ) {
      //echo "saving";
      $clientId=$this->request->data['SalesOrder']['client_id'];
      $clientName=$this->request->data['SalesOrder']['client_name'];
      $clientPhone=$this->request->data['SalesOrder']['client_phone'];
      $clientMail=$this->request->data['SalesOrder']['client_email'];
      
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDateTime=new DateTime($latestClosingDate);
      
      $boolMultiplicationOK=true;
      $multiplicationErrorMessage='';
      $sumProductTotals=0;
      $boolProductPricesRegistered=true;
      $productPriceWarning='';
      $boolProductPriceLessThanDefaultPrice='0';
      $productPriceLessThanDefaultPriceError='';
      $boolProductPriceRepresentsBenefit=true;
      $productPriceBenefitError='';
      
      if (!empty($this->request->data['SalesOrderProduct'])){
        foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
          if ($salesOrderProduct['product_id'] > 0) {
            $acceptableProductPrice=1000;
            $acceptableProductPrice=$this->Product->getAcceptablePriceForProductClientCostQuantityDate($salesOrderProduct['product_id'],$clientId,$salesOrderProduct['product_unit_cost'],$salesOrderProduct['product_quantity'],$salesOrderDate,$salesOrderProduct['raw_material_id']);
            
            $productName=$this->Product->getProductName($salesOrderProduct['product_id']);
            $rawMaterialName=($salesOrderProduct['raw_material_id'] > 0?($this->Product->getProductName($salesOrderProduct['raw_material_id'])):'');
            if (!empty($rawMaterialName)){
              $productName.=(' '.$rawMaterialName.' A');
            }
            
            $multiplicationDifference=abs($salesOrderProduct['product_total_price']-$salesOrderProduct['product_quantity']*$salesOrderProduct['product_unit_price']);
            if ($multiplicationDifference>=0.01){
              $boolMultiplicationOK='0';
            };
            if ($salesOrderProduct['product_id'] != PRODUCT_SERVICE_OTHER){
              if ($salesOrderProduct['default_product_unit_price'] <=0) {
                //pr($salesOrderProduct);
                $boolProductPricesRegistered='0'; 
                $productPriceWarning='Producto '.$productName.' no tiene registrado un precio de listado entonces no se podía aplicar un control de precios.  Por favor graba un precio para este producto primero.  ';  
              }
              // 20211004 default_product_price could be tricked into accepting volume price by users with bad intentions by increasing and then decreasing prices, that's why the price is calculated afreshe in $acceptableProductPrice
              //if ($salesOrderProduct['product_unit_price'] < $salesOrderProduct['default_product_unit_price']) {
              if ($salesOrderProduct['product_unit_price'] < $acceptableProductPrice) {  
                $boolProductPriceLessThanDefaultPrice=true; 
                //$productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$salesOrderProduct['product_unit_price'].' pero el precio mínimo establecido es '.$salesOrderProduct['default_product_unit_price'].'.  No se permite vender abajo del precio mínimo establecido.  ';  
                $productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$salesOrderProduct['product_unit_price'].' pero el precio mínimo establecido es '.$acceptableProductPrice.'.  No se permite vender abajo del precio mínimo establecido.  ';  
              }
              if ($salesOrderProduct['product_unit_price'] < $salesOrderProduct['product_unit_cost']) {
                $boolProductPriceRepresentsBenefit='0'; 
                if ($userRoleId === ROLE_ADMIN){
                  $productPriceBenefitError='Producto '.$productName.' tiene un precio '.$salesOrderProduct['product_unit_price'].' pero el costo es '.$salesOrderProduct['product_unit_cost'].'.  No se permite vender con pérdidas.  ';  
                }
                else {
                  $productPriceBenefitError='Precio no autorizado para producto '.$productName.'.  No se guardó la orden de venta.  ';  
                } 
              }
            }
          }
          $sumProductTotals+=$salesOrderProduct['product_total_price'];
        }
      }
      
      if (!array_key_exists('bool_credit',$this->request->data['SalesOrder'])){
        $this->request->data['SalesOrder']['bool_credit']=$this->request->data['bool_credit'];
      }
      if (!array_key_exists('save_allowed',$this->request->data['SalesOrder'])){
        $this->request->data['SalesOrder']['save_allowed']=$this->request->data['save_allowed'];
      }
      if (array_key_exists('credit_days',$this->request->data['Client']) && $this->request->data['Client']['credit_days']){
        $this->request->data['SalesOrder']['credit_days']=$this->request->data['Client']['credit_days'];  
      }
      else {
        $this->request->data['SalesOrder']['credit_days']=0;
      }
      if (!array_key_exists('credit_authorization_user_id',$this->request->data['SalesOrder'])){
        $this->request->data['SalesOrder']['credit_authorization_user_id']=$this->request->data['credit_authorization_user_id'];
      }
      $creditAuthorizationUserId=$this->request->data['SalesOrder']['credit_authorization_user_id'];
      if (!array_key_exists('retention_allowed',$this->request->data['SalesOrder'])){
        if (array_key_exists('retention_allowed',$this->request->data)){
          $this->request->data['SalesOrder']['retention_allowed']=$this->request->data['retention_allowed'];
        }
        else{
          $this->request->data['SalesOrder']['retention_allowed']=1;
        }
      }
      
      
      if ($salesOrderDateString>date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de orden de venta no puede estar en el futuro!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['SalesOrder']['save_allowed'] == 0){
        $this->Session->setFlash('No se permite guardar esta orden de venta de crédito!  Si está el gerente, marca la casilla de permitir guardar venta.  No se guardó la orden de venta.', 'default',['class' => 'error-message']);
      }
      elseif ($salesOrderDateString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($clientId) && empty($clientName)){
        $this->Session->setFlash('Se debe registrar el nombre del cliente.  No se guardó la orden.', 'default',['class' => 'error-message']);
      }
      elseif (empty($clientId) && empty($clientPhone) && empty($clientMail)){
        $this->Session->setFlash('Se debe registrar el teléfono o el correo electrónico del cliente.  No se guardó la orden.', 'default',['class' => 'error-message']);
      }
      //elseif (empty($this->request->data['SalesOrder']['driver_user_id'])){
      //  $this->Session->setFlash('Se debe registrar el conductor.  No se guardó la orden de venta.', 'default',['class' => 'error-message']);
      //}
      elseif (!$this->request->data['SalesOrder']['client_generic'] && empty($this->request->data['SalesOrder']['client_type_id'])){
        $this->Session->setFlash('Se debe registrar el tipo de cliente.  No se guardó la orden de venta.', 'default',['class' => 'error-message']);
      }
      elseif (!$this->request->data['SalesOrder']['client_generic'] && empty($this->request->data['SalesOrder']['zone_id'])){
        $this->Session->setFlash('Se debe registrar la zona del cliente.  No se guardó la orden de venta.', 'default',['class' => 'error-message']);
      }
      elseif (!$boolMultiplicationOK){
        $this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
      }
      elseif (abs($sumProductTotals-$this->request->data['SalesOrder']['price_subtotal']) > 0.01){
        $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$sumProductTotals.' pero el total calculado es '.$this->request->data['SalesOrder']['price_subtotal'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      elseif (abs($this->request->data['SalesOrder']['price_total']-$this->request->data['SalesOrder']['price_iva']-$this->request->data['SalesOrder']['price_subtotal'])>0.01){
        $this->Session->setFlash('La suma del subtotal '.$this->request->data['SalesOrder']['price_subtotal'].' y el IVA '.$this->request->data['SalesOrder']['price_iva'].' no igualan el precio total '.$this->request->data['SalesOrder']['price_total'].', la diferencia es de '.(abs($this->request->data['SalesOrder']['price_total']-$this->request->data['SalesOrder']['price_iva']-$this->request->data['SalesOrder']['price_subtotal'])).'.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['SalesOrder']['price_total'])){
        $this->Session->setFlash(__('El total de la orden de venta tiene que ser mayor que cero.  No se guardó la orden.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['SalesOrder']['bool_retention'] && strlen($this->request->data['SalesOrder']['retention_number'])==0){
        $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['SalesOrder']['bool_retention'] && abs(0.02*$this->request->data['SalesOrder']['price_subtotal']-$this->request->data['SalesOrder']['retention_amount']) > 0.01){
        $this->Session->setFlash(__('La retención debería igualar el 2% del subtotal de la orden de venta!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
      }  
      else if ($this->request->data['SalesOrder']['bool_delivery'] && empty($this->request->data['SalesOrder']['delivery_address'])){
        $this->Session->setFlash('Se indicó que.la orden de venta se debe entregar a domicilio pero no se indicó la dirección de entrega.  La dirección de entrega se debe registrar.', 'default',['class' => 'error-message']);
      }  
      elseif (!$boolProductPricesRegistered && $userRoleId != ROLE_ADMIN){
        $this->Session->setFlash($productPriceWarning.'No se guardó la orden de venta.', 'default',['class' => 'error-message']);
      }
      elseif ($boolProductPriceLessThanDefaultPrice && $userRoleId != ROLE_ADMIN){
        $this->Session->setFlash($productPriceLessThanDefaultPriceError.'No se guardó la orden venta.', 'default',['class' => 'error-message']);
      }
      elseif (!$boolProductPriceRepresentsBenefit){
        $this->Session->setFlash($productPriceBenefitError.'No se guardó la orden de venta.', 'default',['class' => 'error-message']);
      }
      else {
        $datasource=$this->SalesOrder->getDataSource();
        $datasource->begin();
        try {
          //pr($this->request->data);
          $this->SalesOrder->create();
          if (!$this->SalesOrder->save($this->request->data)) {
            echo "Problema guardando la orden de venta";
            pr($this->validateErrors($this->SalesOrder));
            throw new Exception();
          } 
          $salesOrderId=$this->SalesOrder->id;
          
          if ($quotationId > 0){
            $this->Quotation->id=$quotationId;
            $quotationArray=[
              'Quotation'=>[
                'id'=>$quotationId,
                'bool_sales_order'=>true,
                'sales_order_id'=>$salesOrderId,
              ]
            ];
            if (!$this->Quotation->save($quotationArray)) {
              echo "Problema actualizando la cotización";
              pr($this->validateErrors($this->Quotation));
              throw new Exception();
            }
          }
          
          foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
            if ($salesOrderProduct['product_id']>0 && $salesOrderProduct['product_quantity']>0){
              //pr($salesOrderProduct);
              $productArray=[];
              $productArray['SalesOrderProduct']['sales_order_id']=$salesOrderId;
              $productArray['SalesOrderProduct']['product_id']=$salesOrderProduct['product_id'];
              $productArray['SalesOrderProduct']['raw_material_id']=$salesOrderProduct['raw_material_id'];
              //$productArray['SalesOrderProduct']['product_description']=$salesOrderProduct['product_description'];
              $productArray['SalesOrderProduct']['product_quantity']=$salesOrderProduct['product_quantity'];
              $productArray['SalesOrderProduct']['product_unit_price']=$salesOrderProduct['product_unit_price'];
              $productArray['SalesOrderProduct']['product_total_price']=$salesOrderProduct['product_total_price'];
              //$productArray['SalesOrderProduct']['bool_iva']=$salesOrderProduct['bool_iva'];
              $productArray['SalesOrderProduct']['bool_iva']=true;
              //$productArray['SalesOrderProduct']['currency_id']=$this->request->data['SalesOrder']['currency_id'];
              $productArray['SalesOrderProduct']['currency_id']=CURRENCY_CS;
              
              $this->SalesOrderProduct->create();
              if (!$this->SalesOrderProduct->save($productArray)) {
                echo "Problema guardando los productos de la orden de venta";
                //pr($this->validateErrors($this->SalesOrderProduct));
                throw new Exception();
              }
              
              $salesOrderProductId=$this->SalesOrderProduct->id;
            }
          }
          
        /*	
          if (!empty($this->request->data['SalesOrderRemark']['remark_text'])){
            $salesOrderRemark=$this->request->data['SalesOrderRemark'];
            //pr($quotationRemark);
            $salesOrderRemarkArray=[];
            $salesOrderRemarkArray['SalesOrderRemark']['user_id']=$salesOrderRemark['user_id'];
            $salesOrderRemarkArray['SalesOrderRemark']['sales_order_id']=$salesOrderId;
            $salesOrderRemarkArray['SalesOrderRemark']['remark_datetime']=date('Y-m-d H:i:s');
            $salesOrderRemarkArray['SalesOrderRemark']['remark_text']=$salesOrderRemark['remark_text'];
            $salesOrderRemarkArray['SalesOrderRemark']['working_days_before_reminder']=$salesOrderRemark['working_days_before_reminder'];
            $salesOrderRemarkArray['SalesOrderRemark']['reminder_date']=$salesOrderRemark['reminder_date'];
            $salesOrderRemarkArray['SalesOrderRemark']['action_type_id']=$salesOrderRemark['action_type_id'];
            $this->SalesOrderRemark->create();
            if (!$this->SalesOrderRemark->save($salesOrderRemarkArray)) {
              echo "Problema guardando las remarcas para la orden de venta";
              pr($this->validateErrors($this->SalesOrderRemark));
              throw new Exception();
            }
          }
        */

          //if (!$this->request->data['SalesOrder']['client_generic']){
          if (!in_array($this->request->data['SalesOrder']['client_id'],$genericClientIds)){  
            $salesOrderClientData=[
              'id'=>$this->request->data['SalesOrder']['client_id'],
              'phone'=>$this->request->data['SalesOrder']['client_phone'],
              'email'=>$this->request->data['SalesOrder']['client_email'],
              'address'=>$this->request->data['SalesOrder']['client_address'],
              'ruc_number'=>'',
              'client_type_id'=>$this->request->data['SalesOrder']['client_type_id'],
              'zone_id'=>$this->request->data['SalesOrder']['zone_id'],
              
            ];
            if (!$this->ThirdParty->updateClientDataConditionally($salesOrderClientData,$userRoleId)['success']){
              echo "Problema actualizando los datos del cliente";
              throw new Exception();
            }
          }
        
          $datasource->commit();
          $this->recordUserAction($this->SalesOrder->id,null,null);
          $this->recordUserActivity($clientName,"Se registró la orden de venta número ".$this->request->data['SalesOrder']['sales_order_code']);
          
          $flashMessage='Se guardó la orden de venta.  ';
          if (!$boolProductPricesRegistered){
            $flashMessage.=$productPriceWarning;
          }  
          $this->Session->setFlash($flashMessage,'default',['class' => 'success']);
          if ($userRoleId > 0){
            return $this->redirect(['action' => 'detalle',$salesOrderId]);
          }
          else {
            return $this->redirect(['action' => 'confirmacionOrdenDeVenta',$salesOrderId]);
          }
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
        }
      }
    }
        
		$this->set(compact('requestProducts'));
    $this->set(compact('boolInitialLoad'));
    
    $this->set(compact('quotationId'));
    //$this->set(compact('productTypeId'));
		$this->set(compact('clientId'));
    $this->set(compact('currencyId'));
    
    $this->set(compact('vendorUserId'));
    $this->set(compact('recordUserId'));
    $this->set(compact('creditAuthorizationUserId'));
    
    //$this->set(compact('driverUserId'));
    //$this->set(compact('vehicleId'));
    $this->set(compact('boolDelivery'));
    
    
    if ($userRoleId == ROLE_CLIENT){
      $clients=$this->ThirdParty->find('list',[
        'conditions'=>['Client.id'=>$clientId],
      ]);
    }
    else {
      $clients = $this->ThirdParty->getActiveClientList(20);
    }
		$this->set(compact('clients'));
    
    $currencies = $this->SalesOrder->Currency->find('list');
		$this->set(compact('currencies'));
    //pr($clients);
    
		$exchangeRateSalesOrder=$this->ExchangeRate->getApplicableExchangeRateValue($salesOrderDate);
		$this->set(compact('exchangeRateSalesOrder'));
		
		$actionTypes=$this->ActionType->find('list',['order'=>'ActionType.list_order ASC']);
		$this->set(compact('actionTypes'));
    
    $availableProductsForSale=$this->Product->getAvailableProductsForSale($salesOrderDate,$warehouseId,false);
    $products=$availableProductsForSale['products'];
    //pr($availableProductsForSale);
    if ($warehouseId != WAREHOUSE_INJECTION){
      $availableInjectionProductsForSale=$this->Product->getAvailableProductsForSale($salesOrderDate,WAREHOUSE_INJECTION,false);
      $injectionProducts=$availableInjectionProductsForSale['products'];
      //pr($injectionProducts);
      $products+=$injectionProducts;
    }
    $rawMaterialsAvailablePerFinishedProduct=$availableProductsForSale['rawMaterialsAvailablePerFinishedProduct'];
    $rawMaterials=$availableProductsForSale['rawMaterials'];
    $this->set(compact('products'));
    $this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    $this->set(compact('rawMaterials'));
    
    if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      $users=$this->User->getActiveVendorAdminUserList($warehouseId);
    }
    elseif ($canSeeAllVendors) {
      $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    }
    else {
      $users=$this->User->getActiveUserList($loggedUserId);
    }
    $this->set(compact('users'));
    
    $quotations=$this->Quotation->getPendingQuotations($warehouseId);
    $this->set(compact('quotations'));
    
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
    $priceClientCategories=$this->PriceClientCategory->getPriceClientCategoryList();
    $this->set(compact('priceClientCategories'));
    
    $productCategoriesPerProduct=$this->Product->getProductCategoriesPerProduct();
    $this->set(compact('productCategoriesPerProduct'));
    
    $productTypesPerProduct=$this->Product->getProductTypesPerProduct();
    $this->set(compact('productTypesPerProduct'));
    
    $clientRucNumbers=$this->ThirdParty->getRucNumbersPerClient();
    $this->set(compact('clientRucNumbers'));
    
    //$driverUsers=$this->User->getActiveUsersForRole(ROLE_DRIVER,$warehouseId);
    //$this->set(compact('driverUsers'));
    //$vehicles=$this->Vehicle->getVehicleList($warehouseId);
    //$this->set(compact('vehicles'));
    
    $productCategoryId=CATEGORY_OTHER;
    $productTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>$productCategoryId]
    ]);
    $otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$salesOrderDate,$warehouseId);
    $this->set(compact('otherMaterialsInventory'));
    if ($warehouseId != WAREHOUSE_INJECTION){
      $injectionMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$salesOrderDate,WAREHOUSE_INJECTION);
      $this->set(compact('injectionMaterialsInventory'));
    }
    
		$aco_name="SalesOrders/autorizar";		
		$bool_autorizar_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_autorizar_permission'));
	}

	public function editarOrdenVentaExterna($id = null) {
		if (!$this->SalesOrder->exists($id)) {
			throw new NotFoundException(__('Invalid sales order'));
		}
		
    $this->loadModel('Product');
		$this->loadModel('ProductType');
    $this->loadModel('StockItem');
    
    $this->loadModel('ClosingDate');
    
    $this->loadModel('Quotation');
    
    $this->loadModel('SalesOrderRemark');
		$this->loadModel('SalesOrderProduct');
    
    $this->loadModel('ThirdParty');
    $this->loadModel('ActionType');
		
    $this->loadModel('ClientType');
    $this->loadModel('Zone');
    
    $this->loadModel('PriceClientCategory');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $canSeeInventoryCost=$this->UserPageRight->hasUserPageRight('VER_COSTO_INVENTARIO',$userRoleId,$loggedUserId,'All','All');
    $this->set(compact('canSeeInventoryCost'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'SalesOrders','resumen');
    $this->set(compact('canSeeAllUsers'));
    
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'SalesOrders','resumen');
    $this->set(compact('canSeeAllVendors'));
    
    $quotationId=0;
    
    $salesOrderDate=date( "Y-m-d");
    $clientId=0;
    $currencyId=CURRENCY_CS;
    $vendorUserId=$loggedUserId;
    $recordUserId=$loggedUserId;
    $creditAuthorizationUserId=$loggedUserId;
    $warehouseId=0;
    $boolDelivery='0';
    
    $genericClientIds=$this->ThirdParty->getGenericClientIds();
    $this->set(compact('genericClientIds'));
    
    $boolInitialLoad=true;
    
		$requestProducts=[];
    if ($this->request->is(['post', 'put'])) {
      $boolInitialLoad='0';
      //pr($this->request->data);
      foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
        if ($salesOrderProduct['product_id']>0 && $salesOrderProduct['product_quantity']>0 && $salesOrderProduct['product_unit_price']>0){
          $requestProducts[]['SalesOrderProduct']=$salesOrderProduct;
        }
      }
      
      $quotationId=$this->request->data['SalesOrder']['quotation_id'];
      
      $salesOrderDateArray=$this->request->data['SalesOrder']['sales_order_date'];
      //pr($salesOrderDateArray);
      $salesOrderDateString=$salesOrderDateArray['year'].'-'.$salesOrderDateArray['month'].'-'.$salesOrderDateArray['day'];
      $salesOrderDate=date( "Y-m-d", strtotime($salesOrderDateString));
      
      //$productTypeId=$this->request->data['SalesOrder']['product_type_id'];
      $clientId=$this->request->data['SalesOrder']['client_id'];
      $currencyId=$this->request->data['SalesOrder']['currency_id'];
      $vendorUserId=$this->request->data['SalesOrder']['vendor_user_id'];
      $recordUserId=$this->request->data['SalesOrder']['record_user_id'];
      if (array_key_exists('credit_authorization_user_id',$this->request->data['SalesOrder'])){
        $creditAuthorizationUserId=$this->request->data['SalesOrder']['credit_authorization_user_id'];
      }
      //$driverUserId=$this->request->data['SalesOrder']['driver_user_id'];
      //$vehicleId=$this->request->data['SalesOrder']['vehicle_id'];
      
      $warehouseId=$this->request->data['SalesOrder']['warehouse_id'];
      $boolDelivery=$this->request->data['SalesOrder']['bool_delivery'];
    }
    if ($this->request->is(['post', 'put'])) {
      if (!array_key_exists('bool_credit',$this->request->data['SalesOrder'])){
        $this->request->data['SalesOrder']['bool_credit']=$this->request->data['bool_credit'];
      }  
      
      if (empty($this->request->data['updateWarehouse']) && empty($this->request->data['changeProductType'])){
        $clientId=$this->request->data['SalesOrder']['client_id'];
        $clientName=$this->request->data['SalesOrder']['client_name'];
        $clientPhone=$this->request->data['SalesOrder']['client_phone'];
        $clientMail=$this->request->data['SalesOrder']['client_email'];
        
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDateTime=new DateTime($latestClosingDate);
        
        $boolMultiplicationOK=true;
        $multiplicationErrorMessage='';
        $sumProductTotals=0;
        $boolProductPricesRegistered=true;
        $productPriceWarning='';
        $boolProductPriceLessThanDefaultPrice='0';
        $productPriceLessThanDefaultPriceError='';
        $boolProductPriceRepresentsBenefit=true;
        $productPriceBenefitError='';
        if (!empty($this->request->data['SalesOrderProduct'])){
          foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
            if ($salesOrderProduct['product_id']>0){
              $acceptableProductPrice=1000;
              $acceptableProductPrice=$this->Product->getAcceptablePriceForProductClientCostQuantityDate($salesOrderProduct['product_id'],$clientId,$salesOrderProduct['product_unit_cost'],$salesOrderProduct['product_quantity'],$salesOrderDate,$salesOrderProduct['raw_material_id']);
              
              $productName=$this->Product->getProductName($salesOrderProduct['product_id']);
              $rawMaterialName=($salesOrderProduct['raw_material_id'] > 0?($this->Product->getProductName($salesOrderProduct['raw_material_id'])):'');
              if (!empty($rawMaterialName)){
                $productName.=(' '.$rawMaterialName.' A');
              }
              
              $multiplicationDifference=abs($salesOrderProduct['product_total_price']-$salesOrderProduct['product_quantity']*$salesOrderProduct['product_unit_price']);
              if ($multiplicationDifference>=0.01){
                $boolMultiplicationOK='0';
              };
              if ($salesOrderProduct['product_id'] != PRODUCT_SERVICE_OTHER){
                if ($salesOrderProduct['default_product_unit_price'] <=0) {
                  $boolProductPricesRegistered='0'; 
                  $productPriceWarning='Producto '.$productName.' no tiene registrado un precio de listado entonces no se podía aplicar un control de precios.  Por favor graba un precio para este producto primero.  ';  
                }
                // 20211004 default_product_price could be tricked into accepting volume price by users with bad intentions by increasing and then decreasing prices, that's why the price is calculated afreshe in $acceptableProductPrice
                //if ($salesOrderProduct['product_unit_price'] < $salesOrderProduct['default_product_unit_price']) {
                if ($salesOrderProduct['product_unit_price'] < $acceptableProductPrice) {  
                  $boolProductPriceLessThanDefaultPrice=true; 
                  //$productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$salesOrderProduct['product_unit_price'].' pero el precio mínimo establecido es '.$salesOrderProduct['default_product_unit_price'].'.  No se permite vender abajo del precio mínimo establecido.  ';  
                  $productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$salesOrderProduct['product_unit_price'].' pero el precio mínimo establecido es '.$acceptableProductPrice.'.  No se permite vender abajo del precio mínimo establecido.  ';  
                }
                if ($salesOrderProduct['product_unit_price'] < $salesOrderProduct['product_unit_cost']) {
                  $boolProductPriceRepresentsBenefit='0'; 
                  if ($userRoleId === ROLE_ADMIN){
                    $productPriceBenefitError='Producto '.$productName.' tiene un precio '.$salesOrderProduct['product_unit_price'].' pero el costo es '.$salesOrderProduct['product_unit_cost'].'.  No se permite vender con pérdidas.  ';  
                  }
                  else {
                    $productPriceBenefitError='Precio no autorizado para producto '.$productName.'.  No se guardó la orden de venta.  ';  
                  } 
                }
              }  
            }
            $sumProductTotals+=$salesOrderProduct['product_total_price'];
          }
        }
        //pr($this->request->data);

        if (!array_key_exists('bool_credit',$this->request->data['SalesOrder'])){
          $this->request->data['SalesOrder']['bool_credit']=$this->request->data['bool_credit'];
        }
        if (!array_key_exists('save_allowed',$this->request->data['SalesOrder'])){
          $this->request->data['SalesOrder']['save_allowed']=$this->request->data['save_allowed'];
        }
        if (array_key_exists('credit_days',$this->request->data['Client']) && $this->request->data['Client']['credit_days']){
          $this->request->data['SalesOrder']['credit_days']=$this->request->data['Client']['credit_days'];  
        }
        else {
          $this->request->data['SalesOrder']['credit_days']=0;
        }
        if (!array_key_exists('credit_authorization_user_id',$this->request->data['SalesOrder'])){
          $this->request->data['SalesOrder']['credit_authorization_user_id']=$this->request->data['credit_authorization_user_id'];
        }
        $creditAuthorizationUserId=$this->request->data['SalesOrder']['credit_authorization_user_id'];
        if (!array_key_exists('retention_allowed',$this->request->data['SalesOrder'])){
          if (array_key_exists('retention_allowed',$this->request->data)){
            $this->request->data['SalesOrder']['retention_allowed']=$this->request->data['retention_allowed'];
          }
          else{
            $this->request->data['SalesOrder']['retention_allowed']=1;
          }
        }
        
        if ($salesOrderDateString>date('Y-m-d 23:59:59')){
          $this->Session->setFlash(__('La fecha de orden de venta no puede estar en el futuro!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
        }
        elseif ($this->request->data['SalesOrder']['save_allowed'] == 0){
          $this->Session->setFlash('No se permite guardar esta orden de venta de crédito!  Si está el gerente, marca la casilla de permitir guardar venta.  No se guardó la orden de venta.', 'default',['class' => 'error-message']);
        }
        elseif ($salesOrderDateString<$latestClosingDatePlusOne){
            $this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
        }
        elseif (empty($clientId) && empty($clientName)){
          $this->Session->setFlash('Se debe registrar el nombre del cliente.  No se guardó la orden.', 'default',['class' => 'error-message']);
        }
        elseif (empty($clientId) && empty($clientPhone) && empty($clientMail)){
          $this->Session->setFlash('Se debe registrar el teléfono o el correo electrónico del cliente.  No se guardó la orden.', 'default',['class' => 'error-message']);
        }
        elseif (!$this->request->data['SalesOrder']['client_generic'] && empty($this->request->data['SalesOrder']['client_type_id'])){
          $this->Session->setFlash('Se debe registrar el tipo de cliente.  No se guardó la orden de venta.', 'default',['class' => 'error-message']);
        }
        elseif (!$this->request->data['SalesOrder']['client_generic'] && empty($this->request->data['SalesOrder']['zone_id'])){
          $this->Session->setFlash('Se debe registrar la zona del cliente.  No se guardó la orden de venta.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolMultiplicationOK){
          $this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
        }
        elseif (abs($sumProductTotals-$this->request->data['SalesOrder']['price_subtotal']) > 0.01){
          $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$sumProductTotals.' pero el total calculado es '.$this->request->data['SalesOrder']['price_subtotal'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (abs($this->request->data['SalesOrder']['price_total']-$this->request->data['SalesOrder']['price_iva']-$this->request->data['SalesOrder']['price_subtotal'])>0.01){
          $this->Session->setFlash('La suma del subtotal y el IVA no igualan el precio total.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['SalesOrder']['price_total'])){
          $this->Session->setFlash(__('El total de la orden de venta tiene que ser mayor que cero.  No se guardó la orden.'), 'default',['class' => 'error-message']);
        }
        else if ($this->request->data['SalesOrder']['bool_retention'] && strlen($this->request->data['SalesOrder']['retention_number'])==0){
          $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
        }
        else if ($this->request->data['SalesOrder']['bool_retention'] && abs(0.02*$this->request->data['SalesOrder']['price_subtotal']-$this->request->data['SalesOrder']['retention_amount']) > 0.01){
          $this->Session->setFlash(__('La retención debería igualar el 2% del subtotal de la orden de venta!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
        }
        else if ($this->request->data['SalesOrder']['bool_delivery'] && empty($this->request->data['SalesOrder']['delivery_address'])){
          $this->Session->setFlash('Se indicó que.la orden de venta se debe entregar a domicilio pero no se indicó la dirección de entrega.  La dirección de entrega se debe registrar.', 'default',['class' => 'error-message']);
        }          
        elseif (!$boolProductPricesRegistered && $userRoleId != ROLE_ADMIN){
          $this->Session->setFlash($productPriceWarning.'No se editó la orden de venta.', 'default',['class' => 'error-message']);
        }
        elseif ($boolProductPriceLessThanDefaultPrice && $userRoleId != ROLE_ADMIN){
          $this->Session->setFlash($productPriceLessThanDefaultPriceError.'No se editó la orden venta.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolProductPriceRepresentsBenefit){
          $this->Session->setFlash($productPriceBenefitError.'No se editó la orden de venta.', 'default',['class' => 'error-message']);
        }
        else {
          $datasource=$this->SalesOrder->getDataSource();
          $datasource->begin();
          try {
            // REMOVE FORMER DATA
            $previousSalesOrder=$this->SalesOrder->find('first',[
              'conditions'=>[
                'SalesOrder.id'=>$id,
              ],
              'contain'=>[
                'SalesOrderProduct',
              ],
            ]);
            //pr($previousSalesOrder);
            // TECHNICALLY IT IS NOT POSSIBLE THAT THERE ARE NO PRODUCTS IN THE SALES ORDER, SO WE SPARE A CHECK ON EMPTY
            foreach ($previousSalesOrder['SalesOrderProduct'] as $previousSalesOrderProduct){
              //pr($previousSalesOrderProduct);
              $this->SalesOrderProduct->id=$previousSalesOrderProduct['id'];
              if (!$this->SalesOrderProduct->delete($previousSalesOrderProduct['id'])){
                echo "Problema removiendo los productos anteriores";
                pr($this->validateErrors($this->SalesOrderProduct));
                throw new Exception();
              }
            }
            if ($previousSalesOrder['SalesOrder']['quotation_id'] > 0 && $previousSalesOrder['SalesOrder']['quotation_id'] != $this->request->data['SalesOrder']['quotation_id']){
              $this->Quotation->id=$previousSalesOrder['SalesOrder']['quotation_id'];
              $quotationArray=[
                'Quotation'=>[
                  'id'=>$previousSalesOrder['SalesOrder']['quotation_id'],
                  'bool_sales_order'=>'0',
                  'sales_order_id'=>0,
                ]
              ];
              if (!$this->Quotation->save($quotationArray)) {
                echo "Problema removiendo la asociación con la cotización previa";
                pr($this->validateErrors($this->Quotation));
                throw new Exception();
              }
            }
            
            //pr($this->request->data);
            $this->SalesOrder->id=$id;
            if (!$this->SalesOrder->save($this->request->data)) {
              echo "Problema guardando la orden de venta";
              //pr($this->validateErrors($this->SalesOrder));
              throw new Exception();
            } 
            $salesOrderId=$this->SalesOrder->id;
            
            if ($quotationId > 0){
              $this->Quotation->id=$quotationId;
              $quotationArray=[
                'Quotation'=>[
                  'id'=>$quotationId,
                  'bool_sales_order'=>true,
                  'sales_order_id'=>$salesOrderId,
                ]
              ];
              if (!$this->Quotation->save($quotationArray)) {
                echo "Problema actualizando la cotización";
                pr($this->validateErrors($this->Quotation));
                throw new Exception();
              }
            }
            
            foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
              if ($salesOrderProduct['product_id']>0&&$salesOrderProduct['product_quantity']>0){
                //pr($salesOrderProduct);
                $productArray=[];
                $productArray['SalesOrderProduct']['sales_order_id']=$salesOrderId;
                $productArray['SalesOrderProduct']['product_id']=$salesOrderProduct['product_id'];
                $productArray['SalesOrderProduct']['raw_material_id']=$salesOrderProduct['raw_material_id'];
                //$productArray['SalesOrderProduct']['product_description']=$salesOrderProduct['product_description'];
                $productArray['SalesOrderProduct']['product_quantity']=$salesOrderProduct['product_quantity'];
                $productArray['SalesOrderProduct']['product_unit_price']=$salesOrderProduct['product_unit_price'];
                $productArray['SalesOrderProduct']['product_total_price']=$salesOrderProduct['product_total_price'];
                //$productArray['SalesOrderProduct']['bool_iva']=$salesOrderProduct['bool_iva'];
                $productArray['SalesOrderProduct']['bool_iva']=true;
                //$productArray['SalesOrderProduct']['currency_id']=$this->request->data['SalesOrder']['currency_id'];
                $productArray['SalesOrderProduct']['currency_id']=CURRENCY_CS;
                
                $this->SalesOrderProduct->create();
                if (!$this->SalesOrderProduct->save($productArray)) {
                  echo "Problema guardando los productos de la orden de venta";
                  //pr($this->validateErrors($this->SalesOrderProduct));
                  throw new Exception();
                }
                
                $salesOrderProductId=$this->SalesOrderProduct->id;
              }
            }
          
            //if (!$this->request->data['SalesOrder']['client_generic']){
            if (!in_array($this->request->data['SalesOrder']['client_id'],$genericClientIds)){    
              $salesOrderClientData=[
                'id'=>$this->request->data['SalesOrder']['client_id'],
                'phone'=>$this->request->data['SalesOrder']['client_phone'],
                'email'=>$this->request->data['SalesOrder']['client_email'],
                'address'=>$this->request->data['SalesOrder']['client_address'],
                'ruc_number'=>'',
                'client_type_id'=>$this->request->data['SalesOrder']['client_type_id'],
                'zone_id'=>$this->request->data['SalesOrder']['zone_id'],
                
              ];
              if (!$this->ThirdParty->updateClientDataConditionally($salesOrderClientData,$userRoleId)['success']){
                echo "Problema actualizando los datos del cliente";
                throw new Exception();
              }
            }
            
            $datasource->commit();
            $this->recordUserAction($this->SalesOrder->id,null,null);
            $this->recordUserActivity($clientName,"Se registró la orden de venta número ".$this->request->data['SalesOrder']['sales_order_code']);
            
            $flashMessage='Se editó la orden de venta.  ';
            if (!$boolProductPricesRegistered){
              $flashMessage.=$productPriceWarning;
            }  
            $this->Session->setFlash($flashMessage,'default',['class' => 'success']);
            if ($userRoleId > 0){
              return $this->redirect(['action' => 'detalle',$salesOrderId]);
            }
            else {
              return $this->redirect(['action' => 'confirmacionOrdenDeVenta',$salesOrderId]);
            }
          }
          catch(Exception $e){
            $datasource->rollback();
            $this->Session->setFlash(__('No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
          }
        }
      
      }
    } 
		else {
      $options = [
				'conditions' => [
					'SalesOrder.id' => $id,
				],
				'contain'=>[
					'SalesOrderProduct'=>[
						'Product'=>[
						],
					],
				],
			];
			$this->request->data = $this->SalesOrder->find('first', $options);
      
      $salesOrderDate=$this->request->data['SalesOrder']['sales_order_date']; 
      
      $quotationId=$this->request->data['SalesOrder']['quotation_id'];
      
      $clientId=$this->request->data['SalesOrder']['client_id'];
      $currencyId=$this->request->data['SalesOrder']['currency_id'];
      $vendorUserId=$this->request->data['SalesOrder']['vendor_user_id'];
      $recordUserId=$this->request->data['SalesOrder']['record_user_id'];
      if (array_key_exists('credit_authorization_user_id',$this->request->data['SalesOrder']) && !empty($this->request->data['SalesOrder']['credit_authorization_user_id'])){
        $creditAuthorizationUserId=$this->request->data['SalesOrder']['credit_authorization_user_id'];
      }
      //$driverUserId=$this->request->data['SalesOrder']['driver_user_id'];
      //$vehicleId=$this->request->data['SalesOrder']['vehicle_id'];
      
      $warehouseId=$this->request->data['SalesOrder']['warehouse_id'];
      $boolDelivery=$this->request->data['SalesOrder']['bool_delivery'];
    
      
      for ($sop=0;$sop<count($this->request->data['SalesOrderProduct']);$sop++){
        $productId=$this->request->data['SalesOrderProduct'][$sop]['product_id'];
        $rawMaterialId=$this->request->data['SalesOrderProduct'][$sop]['raw_material_id'];
        $productInventory=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$salesOrderDate,$warehouseId,$rawMaterialId);
        $productCost=0;
        if ($productInventory['quantity'] > 0){
          $productCost=round($productInventory['value']/$productInventory['quantity'],2);
        }
        $this->request->data['SalesOrderProduct'][$sop]['product_unit_cost']=$productCost;
				$requestProducts[]['SalesOrderProduct']=$this->request->data['SalesOrderProduct'][$sop];
			} 
		}
    //pr($requestProducts);
    $this->set(compact('requestProducts'));
    $this->set(compact('boolInitialLoad'));
    
    $this->set(compact('quotationId'));
    //$this->set(compact('productTypeId'));
		$this->set(compact('clientId'));
    $this->set(compact('currencyId'));
    
    $this->set(compact('vendorUserId'));
    $this->set(compact('recordUserId'));
    $this->set(compact('creditAuthorizationUserId'));
    
    $this->set(compact('warehouseId'));
    $plantId=$this->Warehouse->getPlantId($warehouseId);
    $this->set(compact('plantId'));
    
    //$this->set(compact('driverUserId'));
    //$this->set(compact('vehicleId'));
    $this->set(compact('boolDelivery'));
    
    $salesOrderDatePlusOne=date( "Y-m-d", strtotime($salesOrderDate."+1 days"));
		
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    $this->set(compact('warehouses'));
    // WAREHOUSE ID IS DETERMINED BY SALESORDER ITSELF NOW
    //if (count($warehouses) == 1){
    //  $warehouseId=array_keys($warehouses)[0];
    //}
    //$_SESSION['warehouseId']=$warehouseId;
    //$this->set(compact('warehouseId'));
    
    if ($userRoleId == ROLE_CLIENT){
      $clients=$this->ThirdParty->find('list',[
        'conditions'=>['Client.id'=>$clientId],
      ]);
    }
    else {
      $clients = $this->ThirdParty->getActiveClientList(20,$clientId);
    }
		$this->set(compact('clients'));
    
    $currencies = $this->SalesOrder->Currency->find('list');
		$this->set(compact('currencies'));
        
		$exchangeRateSalesOrder=$this->ExchangeRate->getApplicableExchangeRateValue($salesOrderDate);
		$this->set(compact('exchangeRateSalesOrder'));
		
		$actionTypes=$this->ActionType->find('list',['order'=>'ActionType.list_order ASC']);
		$this->set(compact('actionTypes'));
    
    // include products that were already in the salesOrder
    //pr($requestProducts);
    $finishedProductsForEdit=[];
    $rawMaterialsForEdit=[];
    foreach ($requestProducts as $requestProduct){
      if (!in_array($requestProduct['SalesOrderProduct']['product_id'],$finishedProductsForEdit)){
        $finishedProductsForEdit[]=$requestProduct['SalesOrderProduct']['product_id'];
      }
      if (!in_array($requestProduct['SalesOrderProduct']['raw_material_id'],$rawMaterialsForEdit)){
        $rawMaterialsForEdit[]=$requestProduct['SalesOrderProduct']['raw_material_id'];
      }
    }
    $availableProductsForSale=$this->Product->getAvailableProductsForSale($salesOrderDate,$warehouseId,false,$finishedProductsForEdit,$rawMaterialsForEdit);
    $products=$availableProductsForSale['products'];
    if ($warehouseId != WAREHOUSE_INJECTION){
      $availableInjectionProductsForSale=$this->Product->getAvailableProductsForSale($salesOrderDate,WAREHOUSE_INJECTION,false);
      $injectionProducts=$availableInjectionProductsForSale['products'];
      //pr($injectionProducts);
      $products+=$injectionProducts;
    }
    $rawMaterialsAvailablePerFinishedProduct=$availableProductsForSale['rawMaterialsAvailablePerFinishedProduct'];
    $rawMaterials=$availableProductsForSale['rawMaterials'];
    $this->set(compact('products'));
    $this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    $this->set(compact('rawMaterials'));
    
    if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      $users=$this->User->getActiveVendorAdminUserList($warehouseId);
    }
    elseif ($canSeeAllVendors) {
      $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    }
    else {
      $users=$this->User->getActiveUserList($loggedUserId);
    }
    $this->set(compact('users'));
    
    $quotations=$this->Quotation->getPendingQuotations($warehouseId,$quotationId);
    $this->set(compact('quotations'));
    
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
    $priceClientCategories=$this->PriceClientCategory->getPriceClientCategoryList();
    $this->set(compact('priceClientCategories'));
    
    $productCategoriesPerProduct=$this->Product->getProductCategoriesPerProduct();
    $this->set(compact('productCategoriesPerProduct'));
    
    $productTypesPerProduct=$this->Product->getProductTypesPerProduct();
    $this->set(compact('productTypesPerProduct'));
    
    $clientRucNumbers=$this->ThirdParty->getRucNumbersPerClient();
    $this->set(compact('clientRucNumbers'));
  /*  
    $driverUsers=$this->User->getActiveUsersForRole(ROLE_DRIVER,$warehouseId);
    $this->set(compact('driverUsers'));
    $vehicles=$this->Vehicle->getVehicleList($warehouseId);
    $this->set(compact('vehicles'));
  */  
    $productCategoryId=CATEGORY_OTHER;
    $productTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>$productCategoryId]
    ]);
    $otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$salesOrderDate,$warehouseId);
    $this->set(compact('otherMaterialsInventory'));
    if ($warehouseId != WAREHOUSE_INJECTION){
      $injectionMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$salesOrderDate,WAREHOUSE_INJECTION);
      $this->set(compact('injectionMaterialsInventory'));
    }
    
		$aco_name="SalesOrders/autorizar";		
		$bool_autorizar_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_autorizar_permission'));
	}

	public function cambiarEstado($id = null) {
		if (!$this->SalesOrder->exists($id)) {
			throw new NotFoundException(__('Invalid sales order'));
		}
		
		$this->loadModel('Quotation');
		$this->loadModel('SalesOrderProduct');
		$this->loadModel('SalesOrderProductStatus');
		$this->loadModel('Product');
		
    $this->SalesOrderProduct->recursive=-1;
    
		if ($this->request->is(['post', 'put'])) {
			$salesOrderDateArray=$this->request->data['SalesOrder']['sales_order_date'];
			//pr($entryDateArray);
			$salesOrderDateString=$salesOrderDateArray['year'].'-'.$salesOrderDateArray['month'].'-'.$salesOrderDateArray['day'];
			$salesOrderDate=date( "Y-m-d", strtotime($salesOrderDateString));
			if ($salesOrderDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha de orden de venta no puede estar en el futuro!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
			}
			else {
				$datasource=$this->SalesOrder->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
					
					$previousSalesOrderProducts=$this->SalesOrderProduct->find('all',[
						'fields'=>['SalesOrderProduct.id'],
						'conditions'=>[
							'SalesOrderProduct.sales_order_id'=>$id,
						],
					]);
					if (!empty($previousSalesOrderProducts)){
						foreach ($previousSalesOrderProducts as $previousSalesOrderProduct){
							$this->SalesOrderProduct->id=$previousSalesOrderProduct['SalesOrderProduct']['id'];
							$this->SalesOrderProduct->delete($previousSalesOrderProduct['SalesOrderProduct']['id']);
						}
					}
					
					if ($this->request->data['SalesOrder']['bool_annulled']){
						$this->request->data['SalesOrder']['price_subtotal']=0;
						$this->request->data['SalesOrder']['price_iva']=0;
						$this->request->data['SalesOrder']['price_total']=0;
						$this->SalesOrder->id=$id;
						if (!$this->SalesOrder->save($this->request->data)) {
							echo "Problema guardando la orden de venta";
							//pr($this->validateErrors($this->SalesOrder));
							throw new Exception();
						} 
						$salesOrderId=$this->SalesOrder->id;
					}
					else {				
						if (!$this->SalesOrder->save($this->request->data)) {
							echo "Problema guardando la orden de venta";
							//pr($this->validateErrors($this->SalesOrder));
							throw new Exception();
						} 
						$salesOrderId=$this->SalesOrder->id;
						
						foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
							if ($salesOrderProduct['product_id']>0){
								//pr($salesOrderProduct);
								$productArray=[];
								$productArray['SalesOrderProduct']['sales_order_id']=$salesOrderId;
								$productArray['SalesOrderProduct']['product_id']=$salesOrderProduct['product_id'];
                $productArray['SalesOrderProduct']['raw_material_id']=$salesOrderProduct['raw_material_id'];
								$productArray['SalesOrderProduct']['product_description']=$salesOrderProduct['product_description'];
								$productArray['SalesOrderProduct']['product_quantity']=$salesOrderProduct['product_quantity'];
								//$productArray['SalesOrderProduct']['sales_order_product_status_id']=$salesOrderProduct['sales_order_product_status_id'];
								$productArray['SalesOrderProduct']['product_unit_price']=$salesOrderProduct['product_unit_price'];
								$productArray['SalesOrderProduct']['product_total_price']=$salesOrderProduct['product_total_price'];
								$productArray['SalesOrderProduct']['currency_id']=$this->request->data['SalesOrder']['currency_id'];
								
								$this->SalesOrderProduct->create();
								if (!$this->SalesOrderProduct->save($productArray)) {
									echo "Problema guardando los productos de la orden de venta";
									//pr($this->validateErrors($this->SalesOrderProduct));
									throw new Exception();
								}
							}
						}
					}
					$datasource->commit();
					$this->recordUserAction($this->SalesOrder->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se registró la orden de venta número ".$this->request->data['SalesOrder']['sales_order_code']);
					
					$this->Session->setFlash(__('The sales order has been saved.'),'default',['class' => 'success']);
					return $this->redirect(['action' => 'detalle',$this->SalesOrder->id]);
				}
				catch(Exception $e){
					$datasource->rollback();
					$this->Session->setFlash(__('No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
				}			
				
			}
		} 
		else {
			$options = [
				'conditions' => [
					'SalesOrder.id' => $id,
				],
				'contain'=>[
					'SalesOrderProduct',
				],
			];
			$this->request->data = $this->SalesOrder->find('first', $options);
			
		}
		$relatedQuotation=$this->Quotation->read(null,$this->request->data['SalesOrder']['quotation_id']);
		$this->set(compact('relatedQuotation'));	
		
		$loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
		$userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
		
		$quotationsInSalesOrders=$this->SalesOrder->find('list',[
			'fields'=>'SalesOrder.quotation_id',
			'conditions'=>[
				'SalesOrder.id !='=>$id,
			],
			'order'=>'SalesOrder.quotation_id',
		]);
		//pr($quotationsInSalesOrders);
		
		$quotationConditions=['Quotation.id !='=>$quotationsInSalesOrders,];
		
		$quotations = $this->SalesOrder->Quotation->find('list',[
			'conditions'=>$quotationConditions,
			'order'=>'Quotation.quotation_code',
		]);
		$this->set(compact('quotations'));
		
		$currencies = $this->SalesOrder->Currency->find('list');
		$this->set(compact('quotations','currencies'));
		
		$products=$this->Product->find('list');
		$this->set(compact('products'));
		
		$salesOrderProductStatuses=$this->SalesOrderProductStatus->find('list');
		$this->set(compact('salesOrderProductStatuses'));
		
		$this->loadModel('Client');
		$clients = $this->Client->find('list',['order'=>'Client.name']);
		$this->set(compact('clients'));
		
		$aco_name="Quotations/resumen";		
		$bool_quotation_index_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_index_permission'));
		$aco_name="Quotations/crear";		
		$bool_quotation_add_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_add_permission'));
	}

	public function autorizar($id = null) {
		if (!$this->SalesOrder->exists($id)) {
			throw new NotFoundException(__('Invalid sales order'));
		}
		//$this->request->allowMethod('post', 'put');
		
		$this->SalesOrder->recursive=-1;
		$salesOrder=$this->SalesOrder->find('first',[
			'conditions'=>[
				'SalesOrder.id'=>$id,
			],
		]);
		
		if (!$salesOrder['SalesOrder']['bool_authorized']){
			$datasource=$this->SalesOrder->getDataSource();
			$datasource->begin();
			try {
				//pr($this->request->data);
				$this->SalesOrder->id=$id;
				$salesOrderArray['SalesOrder']['id']=$id;
				$salesOrderArray['SalesOrder']['bool_authorized']=true;
				$salesOrderArray['SalesOrder']['authorizing_user_id']=$this->Auth->User('id');
				if (!$this->SalesOrder->save($salesOrderArray)) {
					echo "Problema al autorizar la orden de venta";
					pr($this->validateErrors($this->SalesOrder));
					throw new Exception();
				}
				
				$this->loadModel('SalesOrderProduct');
				$this->SalesOrderProduct->recursive=-1;
				$salesOrderProducts=$this->SalesOrderProduct->find('all',[
					'fields'=>['SalesOrderProduct.id','SalesOrderProduct.bool_no_production'],
					'conditions'=>[
						'SalesOrderProduct.sales_order_id'=>$id,
					],
				]);
				if (!empty($salesOrderProducts)){
					foreach ($salesOrderProducts as $salesOrderProduct){
						$this->SalesOrderProduct->id=$salesOrderProduct['SalesOrderProduct']['id'];
						$salesOrderProductArray=[];
						if ($salesOrderProduct['SalesOrderProduct']['bool_no_production']){
							$salesOrderProductArray['SalesOrderProduct']['sales_order_product_status_id']=PRODUCT_STATUS_READY_FOR_DELIVERY;
						}
						else {
							$salesOrderProductArray['SalesOrderProduct']['sales_order_product_status_id']=PRODUCT_STATUS_AUTHORIZED;
						}
						if (!$this->SalesOrderProduct->save($salesOrderProductArray)) {
							echo "Problema al cambiar el estado de los productos de la orden de venta a autorizado";
							pr($this->validateErrors($this->SalesOrderProduct));
							throw new Exception();
						}
					}
				}
							
				$datasource->commit();
				$flashMessage="La orden de venta ".$salesOrder['SalesOrder']['sales_order_code']." se ha autorizada.";
				$this->Session->setFlash($flashMessage,'default',['class' => 'success']);
			}
			catch(Exception $e){
				$this->Session->setFlash(__('La orden de venta no se podía autorizar.'), 'default',['class' => 'error-message']);
			}
		}
		return $this->redirect(['action' => 'detalle',$id]);
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->SalesOrder->id = $id;
		if (!$this->SalesOrder->exists()) {
			throw new NotFoundException(__('Invalid sales order'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('Quotation');
		$salesOrder=$this->SalesOrder->find('first',[
			'conditions'=>[
				'SalesOrder.id'=>$id,
			],
			'contain'=>[				
        'Quotation',
        'Invoice'=>[
          'conditions'=>[
            //'Invoice.bool_annulled'=>'0',
          ],
        ],
				'SalesOrderProduct',
			],
		]);
    //pr($salesOrder);
		$flashMessage="";
		$boolDeletionAllowed=true;
		
		if (!empty($salesOrder['Invoice']['id'])){
			$boolDeletionAllowed='0';
			$flashMessage.="Ya se emitió una factura para esta orden de venta.  Para poder eliminar la orden de venta, primero hay que eliminar o anular la factura ";
			$flashMessage.=$salesOrder['Invoice']['invoice_code'].".";
		}
		if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó la orden de venta por ya tener factura asociado.";
			$this->Session->setFlash($flashMessage, 'default',['class' => 'error-message']);
			return $this->redirect(['action' => 'detalle',$id]);
		}
		else {
			$datasource=$this->SalesOrder->getDataSource();
			$datasource->begin();	
			try {
				//delete all stockMovements, stockItems and stockItemLogs
				foreach ($salesOrder['SalesOrderProduct'] as $salesOrderProduct){
					if (!$this->SalesOrder->SalesOrderProduct->delete($salesOrderProduct['id'])) {
						echo "Problema al eliminar el producto de la orden de venta";
						//pr($this->validateErrors($this->SalesOrder->SalesOrderProduct));
						throw new Exception();
					}
				}
        
        if (!empty($salesOrder['Quotation']['id'])){
          //pr($salesOrder['Quotation']);
          $this->Quotation->id=$salesOrder['Quotation']['id'];
          $quotationArray=[
            'Quotation'=>[
              'id'=>$salesOrder['Quotation']['id'],
              'bool_sales_order'=>'0',
              'sales_order_id'=>null,
            ]
          ];
          if (!$this->Quotation->save($quotationArray)) {
            echo "Problema actualizando la cotización";
            pr($this->validateErrors($this->Quotation));
            throw new Exception();
          }
        }
        
				if (!$this->SalesOrder->delete($id)) {
					echo "Problema al eliminar la orden de venta";
					pr($this->validateErrors($this->SalesOrder));
					throw new Exception();
				}
						
				$datasource->commit();
				
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=[];
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$salesOrder['SalesOrder']['id'];
				$deletionArray['Deletion']['reference']=$salesOrder['SalesOrder']['sales_order_code'];
				$deletionArray['Deletion']['type']='SalesOrder';
				$this->Deletion->save($deletionArray);
						
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la orden de venta número ".$salesOrder['SalesOrder']['sales_order_code']);
						
				$this->Session->setFlash(__('Se eliminó la orden de venta.'),'default',['class' => 'success']);				
				return $this->redirect(['action' => 'resumen']);
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar la orden de venta.'), 'default',['class' => 'error-message']);
				return $this->redirect(['action' => 'detalle',$id]);
			}
		}
	}
/*
	public function annul($id = null) {
		$this->SalesOrder->id = $id;
		if (!$this->SalesOrder->exists()) {
			throw new NotFoundException(__('Orden de Venta inválida'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$datasource=$this->SalesOrder->getDataSource();
		$datasource->begin();
		try {
			//pr($this->request->data);
			
			$this->loadModel('SalesOrderProduct');
			$this->SalesOrderProduct->recursive=-1;
			$previousSalesOrderProducts=$this->SalesOrderProduct->find('all',[
				'fields'=>['SalesOrderProduct.id'],
				'conditions'=>[
					'SalesOrderProduct.sales_order_id'=>$id,
				],
			]);
			if (!empty($previousSalesOrderProducts)){
				foreach ($previousSalesOrderProducts as $previousSalesOrderProduct){
					$this->SalesOrderProduct->id=$previousSalesOrderProduct['SalesOrderProduct']['id'];
					if (!$this->SalesOrderProduct->delete($previousSalesOrderProduct['SalesOrderProduct']['id'])){
						echo "Problema al eliminar los productos de la orden de venta";
						pr($this->validateErrors($this->SalesOrderProduct));
						throw new Exception();
					}
				}
			}
		
			$this->SalesOrder->id=$id;
			$salesOrderArray=[];
			$salesOrderArray['SalesOrder']['id']=$id;
			$salesOrderArray['SalesOrder']['bool_annulled']=true;
			$salesOrderArray['SalesOrder']['price_subtotal']=0;
			$salesOrderArray['SalesOrder']['price_iva']=0;
			$salesOrderArray['SalesOrder']['price_total']=0;
			if (!$this->SalesOrder->save($salesOrderArray)) {
				echo "Problema al anular la orden de venta";
				pr($this->validateErrors($this->SalesOrder));
				throw new Exception();
			}
						
			$datasource->commit();
			$this->Session->setFlash(__('La orden de venta se anuló.'),'default',['class' => 'success']);
		}
		catch(Exception $e){
			$this->Session->setFlash(__('La orden de venta no se podía anular.'), 'default',['class' => 'error-message']);
		}
		
		return $this->redirect(['action' => 'resumen']);
	}
*/
	public function getSalesOrdersForClient() {
		$this->layout = "ajax";
		
		$clientId=trim($_POST['clientid']);
		$boolSkipProductChecks=trim($_POST['boolskipproductchecks']);
		$invoiceId=trim($_POST['invoiceid']);
		//$userid=trim($_POST['userid']);
		
		//pr($clientId);
		$this->loadModel('Client');
		$this->loadModel('Quotation');
		$quotationConditions=[];
		if ($clientId>0){
			$quotationConditions=[
				'Quotation.client_id'=>$clientId,
			];
		}
		//if ($userid>0){
		//	$quotationConditions[]=['Quotation.user_id'=>$userid);
		//}
		$quotationsForClient=$this->Quotation->find('list',[
			'fields'=>'Quotation.id',
			'conditions'=>$quotationConditions,
		]);
		//pr($quotationsForClient);
		
		$this->SalesOrder->recursive=-1;
		
		// 20160807 WITH ADDED CHECKS ON WHETHER TO CHECK ON PRODUCT STATUS OR NOT
		if ($boolSkipProductChecks){
			// in case bool_skip_production_checks is true=>just make a direct check on bool_invoice and that's it
			$salesOrders=$this->SalesOrder->find('all',[
				'fields'=>['SalesOrder.id','SalesOrder.sales_order_code',],
				'conditions'=>[				
					'SalesOrder.bool_invoice'=>'0',
					'SalesOrder.quotation_id'=>$quotationsForClient,
				],
				'order'=>'LENGTH(SalesOrder.sales_order_code) ASC, SalesOrder.sales_order_code ASC',
			]);
			//pr($salesOrders);
		}
		else {
			$salesOrderIdsBasedOnProducts=$this->SalesOrder->SalesOrderProduct->find('list',[
				'fields'=>['SalesOrderProduct.sales_order_id'],
				'conditions'=>[
					'SalesOrderProduct.sales_order_product_status_id'=>[PRODUCT_STATUS_READY_FOR_DELIVERY],
				],
			]);
			$salesOrders=$this->SalesOrder->find('all',[
				'fields'=>['SalesOrder.id','SalesOrder.sales_order_code',],
				'conditions'=>[				
					'SalesOrder.bool_invoice'=>'0',
					'SalesOrder.id'=>$salesOrderIdsBasedOnProducts,
					'SalesOrder.quotation_id'=>$quotationsForClient,
				],
				'order'=>'LENGTH(SalesOrder.sales_order_code) ASC, SalesOrder.sales_order_code ASC',
			]);
		}
		$salesOrdersForClient=$salesOrders;
		$this->set(compact('salesOrdersForClient'));
	}

	public function verReporteOrdenesDeVentaPorEstado(){
    $this->loadModel('SalesOrderProductStatus');
    
    $this->SalesOrderProductStatus->recursive=-1;
    
		$salesOrderProductStatusId=0;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			//pr($startDateArray);
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$salesOrderProductStatusId=$this->request->data['Report']['sales_order_product_status_id'];
		}
		if (!isset($startDate)){
			$startDate=date("Y-m-d", strtotime(date("Y-m-01")));
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$this->set(compact('startDate','endDate','sales_order_product_status_id'));
		
		
		$salesOrderProductStatusConditions=[];
		
		
		if ($salesOrderProductStatusId>0){
			$salesOrderProductStatusConditions['SalesOrderProductStatus.id']=$salesOrderProductStatusId;
		}
		
		$selectedStatuses=[];
		if ($salesOrderProductStatusId!=-1){
			$selectedStatuses=$this->SalesOrderProductStatus->find('all',[
				'fields'=>['SalesOrderProductStatus.id','SalesOrderProductStatus.status'],
				'conditions'=>$salesOrderProductStatusConditions,
			]);
		}
		//pr($selectedUsers);
		$this->SalesOrder->recursive=-1;
		$salesOrders=$this->SalesOrder->find('all',[
			'conditions'=>[
				'SalesOrder.sales_order_date >='=>$startDate,
				'SalesOrder.sales_order_date <'=>$endDatePlusOne,
				'SalesOrder.bool_annulled'=>'0',
			],
			'contain'=>[
				'AuthorizingUser', 
				'Currency',
				'Quotation'=>[
					'Client',
					'Contact',
					'User',
				],
				'SalesOrderProduct'=>[
					'fields'=>[
						'SalesOrderProduct.id','SalesOrderProduct.sales_order_product_status_id',
					],
					'order'=>'SalesOrderProduct.sales_order_product_status_id DESC',
					'Product',
					'SalesOrderProductStatus',
					'Currency',
				],
				/*
				'InvoiceSalesOrder'=>[
					'Invoice'=>[
						'InvoiceProduct',
					),
				),
				*/
				'ProductionOrder'=>[
					'ProductionOrderProduct'=>[
						'PurchaseOrderProduct',
					],
				],
				'ProductionProcessProduct',
			],
		]);
		if (!empty($salesOrders)){
			for ($so=0;$so<count($salesOrders);$so++){	
				$orderStatus=PRODUCT_STATUS_DELIVERED;
				for ($sop=0;$sop<count($salesOrders[$so]['SalesOrderProduct']);$sop++){
					$status=$salesOrders[$so]['SalesOrderProduct'][$sop]['sales_order_product_status_id'];
					
					if ($status<$orderStatus){
						$orderStatus=$status;
					}
				}
				$salesOrders[$so]['SalesOrder']['order_status']=$orderStatus;
			}
		}
		//pr($salesOrders);
		for ($s=0;$s<count($selectedStatuses);$s++){
			$salesOrdersOfStatus=[];
			foreach ($salesOrders as $salesOrder){
				if ($selectedStatuses[$s]['SalesOrderProductStatus']['id']==$salesOrder['SalesOrder']['order_status']){
					$salesOrdersOfStatus[]=$salesOrder;
				}
			}
			$selectedStatuses[$s]['SalesOrders']=$salesOrdersOfStatus;			
		}
		//pr($selectedStatuses);
		
		$salesOrderProductStatuses=$this->SalesOrderProductStatus->find('list');
		$salesOrderProductStatuses=['-1'=>'Anulada']+$salesOrderProductStatuses;
		$this->set(compact('salesOrderProductStatuses','selectedStatuses'));	
		
		$annulledSalesOrders=[];
		if ($salesOrderProductStatusId<=0){
			$annulledSalesOrders=$this->SalesOrder->find('all',[
				'conditions'=>[
					'SalesOrder.sales_order_date >='=>$startDate,
					'SalesOrder.sales_order_date <'=>$endDatePlusOne,
					'SalesOrder.bool_annulled'=>true,
				],
				'contain'=>[
					'Quotation'=>[
						'Client',
						'Contact',
						'Currency',
					],
				],
			]);
		}
		$this->set(compact('annulledSalesOrders'));
	}
	
	public function guardarReporteOrdenesDeVentaPorEstado() {
		$exportData=$_SESSION['reporteCotizacionesPorEjecutivo'];
		$this->set(compact('exportData'));
	}
/*  
  public function crear($quotation_id=0) {
		$this->set(compact('quotation_id'));
		
		$this->loadModel('Quotation');
		$this->loadModel('SalesOrderRemark');
		$this->loadModel('SalesOrderProduct');
		$this->loadModel('SalesOrderProductStatus');
    $this->loadModel('Client');
		$this->loadModel('ActionType');
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
		
		if ($this->request->is('post')) {
			$salesOrderDateArray=$this->request->data['SalesOrder']['sales_order_date'];
			//pr($entryDateArray);
			$salesOrderDateString=$salesOrderDateArray['year'].'-'.$salesOrderDateArray['month'].'-'.$salesOrderDateArray['day'];
			$salesOrderDate=date( "Y-m-d", strtotime($salesOrderDateString));
			
			$boolMultiplicationOK=true;
			if (!empty($this->request->data['SalesOrderProduct'])){
				foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
					if ($salesOrderProduct['product_id']>0){
						$multiplicationDifference=abs($salesOrderProduct['product_total_price']-$salesOrderProduct['product_quantity']*$salesOrderProduct['product_unit_price']);
						if ($multiplicationDifference>=0.01){
							$boolMultiplicationOK='0';
						};
					}
				}
			}
			
			$relatedQuotation=$this->Quotation->find('first',[
        'conditions'=>['Quotation.id'=>$this->request->data['SalesOrder']['quotation_id']],
      ]);
			$boolTotalOK=true;
			if (empty($this->request->data['SalesOrder']['price_total'])){
				$boolTotalOK='0';
			}
			elseif ($this->request->data['SalesOrder']['price_total']!=$relatedQuotation['Quotation']['price_total']){
				$boolTotalOK='0';
			}
			
			if ($salesOrderDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha de orden de venta no puede estar en el futuro!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
			}
			elseif (!$boolMultiplicationOK){
				$this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
			}
			elseif (!$boolTotalOK){
				$this->Session->setFlash(__('Occurrió un problema al cargar los productos; los totales de la orden de venta y de la cotización deben estar iguales.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
			}
			else {
				$datasource=$this->SalesOrder->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
					$this->SalesOrder->create();
					if (!$this->SalesOrder->save($this->request->data)) {
						echo "Problema guardando la orden de venta";
						//pr($this->validateErrors($this->SalesOrder));
						throw new Exception();
					} 
					$salesOrderId=$this->SalesOrder->id;
					
					foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
						if ($salesOrderProduct['product_id']>0&&$salesOrderProduct['product_quantity']>0){
							//pr($salesOrderProduct);
							$productArray=[];
							$productArray['SalesOrderProduct']['sales_order_id']=$salesOrderId;
							$productArray['SalesOrderProduct']['product_id']=$salesOrderProduct['product_id'];
							$productArray['SalesOrderProduct']['product_description']=$salesOrderProduct['product_description'];
							$productArray['SalesOrderProduct']['product_quantity']=$salesOrderProduct['product_quantity'];
							$productArray['SalesOrderProduct']['product_unit_price']=$salesOrderProduct['product_unit_price'];
							$productArray['SalesOrderProduct']['product_total_price']=$salesOrderProduct['product_total_price'];
							$productArray['SalesOrderProduct']['bool_iva']=$salesOrderProduct['bool_iva'];
							$productArray['SalesOrderProduct']['currency_id']=$this->request->data['SalesOrder']['currency_id'];
							$productArray['SalesOrderProduct']['bool_no_production']=$salesOrderProduct['bool_no_production'];
							$productArray['SalesOrderProduct']['sales_order_product_status_id']=$salesOrderProduct['sales_order_product_status_id'];
							
							$this->SalesOrderProduct->create();
							if (!$this->SalesOrderProduct->save($productArray)) {
								echo "Problema guardando los productos de la orden de venta";
								//pr($this->validateErrors($this->SalesOrderProduct));
								throw new Exception();
							}
							
							$sales_order_product_id=$this->SalesOrderProduct->id;
						}
					}
					
					if (!empty($this->request->data['SalesOrderRemark']['remark_text'])){
						$salesOrderRemark=$this->request->data['SalesOrderRemark'];
						//pr($quotationRemark);
						$salesOrderRemarkArray=[];
						$salesOrderRemarkArray['SalesOrderRemark']['user_id']=$salesOrderRemark['user_id'];
						$salesOrderRemarkArray['SalesOrderRemark']['sales_order_id']=$salesOrderId;
						$salesOrderRemarkArray['SalesOrderRemark']['remark_datetime']=date('Y-m-d H:i:s');
						$salesOrderRemarkArray['SalesOrderRemark']['remark_text']=$salesOrderRemark['remark_text'];
						$salesOrderRemarkArray['SalesOrderRemark']['working_days_before_reminder']=$salesOrderRemark['working_days_before_reminder'];
						$salesOrderRemarkArray['SalesOrderRemark']['reminder_date']=$salesOrderRemark['reminder_date'];
						$salesOrderRemarkArray['SalesOrderRemark']['action_type_id']=$salesOrderRemark['action_type_id'];
						$this->SalesOrderRemark->create();
						if (!$this->SalesOrderRemark->save($salesOrderRemarkArray)) {
							echo "Problema guardando las remarcas para la orden de venta";
							pr($this->validateErrors($this->SalesOrderRemark));
							throw new Exception();
						}
					}
					
					$datasource->commit();
					$this->recordUserAction($this->SalesOrder->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se registró la orden de venta número ".$this->request->data['SalesOrder']['sales_order_code']);
					
					$this->Session->setFlash(__('The sales order has been saved.'),'default',['class' => 'success']);
					return $this->redirect(['action' => 'detalle',$salesOrderId]);
				}
				catch(Exception $e){
					$datasource->rollback();
					$this->Session->setFlash(__('No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
				}
			}
		}
			
		$quotationsInSalesOrders=$this->SalesOrder->find('list',[
			'fields'=>'SalesOrder.quotation_id',
			'order'=>'SalesOrder.quotation_id',
		]);
		//pr($quotationsInSalesOrders);
		// MODIFID 20160713 TO ALLOW ADMINISTRATORS TO CREATE SALES ORDERS FOR EXTERIOR QUOTATIONS
		$quotationConditions=['Quotation.id !='=>$quotationsInSalesOrders];
		
		if ($userRoleId!=ROLE_ADMIN&&$userRoleId!=ROLE_ASSISTANT) { 
			$quotationConditions[]=['Quotation.user_id'=>$loggedUserId];
		}
		
		$quotations = $this->SalesOrder->Quotation->find('list',[
			'conditions'=>$quotationConditions,
			'order'=>'Quotation.quotation_code',
		]);
		$this->set(compact('quotations'));
		
		$salesOrderProductStatuses = $this->SalesOrderProductStatus->find('list');
		$currencies = $this->SalesOrder->Currency->find('list');
		$this->set(compact('salesOrderProductStatuses','currencies'));
		
    $clients = $this->Client->find('list',['order'=>'Client.name']);
		$this->set(compact('clients'));
		
		$salesOrderDate=date( "Y-m-d");
		$salesOrderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($salesOrderDate);
		$exchangeRateSalesOrder=$salesOrderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateSalesOrder'));
		
		
		$actionTypes=$this->ActionType->find('list',['order'=>'ActionType.list_order ASC']);
		$this->set(compact('actionTypes'));
		
		$aco_name="SalesOrders/autorizar";		
		$bool_autorizar_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_autorizar_permission'));
		
		$aco_name="Quotations/resumen";		
		$bool_quotation_index_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_index_permission'));
		$aco_name="Quotations/crear";		
		$bool_quotation_add_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_add_permission'));
		
		$loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
	}

	public function editar($id = null) {
		if (!$this->SalesOrder->exists($id)) {
			throw new NotFoundException(__('Invalid sales order'));
		}
		
		$this->loadModel('Quotation');
		$this->loadModel('SalesOrderRemark');
		$this->loadModel('SalesOrderProduct');
		$this->loadModel('SalesOrderProductStatus');
		$this->loadModel('Product');
		
		$requestProducts=[];
		if ($this->request->is(['post', 'put'])) {
			$salesOrderDateArray=$this->request->data['SalesOrder']['sales_order_date'];
			//pr($entryDateArray);
			$salesOrderDateString=$salesOrderDateArray['year'].'-'.$salesOrderDateArray['month'].'-'.$salesOrderDateArray['day'];
			$salesOrderDate=date( "Y-m-d", strtotime($salesOrderDateString));
			
			$boolMultiplicationOK=true;
			if (!$this->request->data['SalesOrder']['bool_annulled']){
				foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
					//pr($salesOrderProduct);
					if ($salesOrderProduct['product_id']>0){
						$requestProducts[]['SalesOrderProduct']=$salesOrderProduct;
						$multiplicationDifference=abs($salesOrderProduct['product_total_price']-$salesOrderProduct['product_quantity']*$salesOrderProduct['product_unit_price']);
						if ($multiplicationDifference>=0.01){
							$boolMultiplicationOK='0';
						};
					}
				}
			}
			//pr($requestProducts);
			
			$relatedQuotation=$this->Quotation->find('first',[
				'conditions'=>[
					'Quotation.id'=>$this->request->data['SalesOrder']['quotation_id'],
				],
			]);
			$boolTotalOK=true;
			if (empty($this->request->data['SalesOrder']['price_total'])){
				$boolTotalOK='0';
			}
			elseif ($this->request->data['SalesOrder']['price_total']!=$relatedQuotation['Quotation']['price_total']){
				$boolTotalOK='0';
			}
			
			if ($salesOrderDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha de orden de venta no puede estar en el futuro!  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
			}
			elseif (!$boolMultiplicationOK){
				$this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
			}
			elseif (!$boolTotalOK){
				$this->Session->setFlash(__('Occurrió un problema al cargar los productos; los totales de la orden de venta ('.$this->request->data['SalesOrder']['price_total'].') y de la cotización ( '.$relatedQuotation['Quotation']['price_total'].') deben estar iguales.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
			}
			elseif ($this->request->data['SalesOrder']['bool_annulled']&&empty($this->request->data['SalesOrderRemark']['remark_text'])){
				$this->Session->setFlash(__('Se debe grabar una remarca al anular una orden de venta.  No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
			}
			else {
				$datasource=$this->SalesOrder->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
					$this->SalesOrderProduct->recursive=-1;
					$previousSalesOrderProducts=$this->SalesOrderProduct->find('all',[
						'fields'=>['SalesOrderProduct.id'],
						'conditions'=>[
							'SalesOrderProduct.sales_order_id'=>$id,
						],
					]);
					if (!empty($previousSalesOrderProducts)){
						foreach ($previousSalesOrderProducts as $previousSalesOrderProduct){
							//pr($previousSalesOrderProduct);
							$this->SalesOrderProduct->id=$previousSalesOrderProduct['SalesOrderProduct']['id'];
							$this->SalesOrderProduct->delete($previousSalesOrderProduct['SalesOrderProduct']['id']);
						}
					}
					
					if ($this->request->data['SalesOrder']['bool_annulled']){
						$this->request->data['SalesOrder']['price_subtotal']=0;
						$this->request->data['SalesOrder']['price_iva']=0;
						$this->request->data['SalesOrder']['price_total']=0;
						$this->SalesOrder->id=$id;
						if (!$this->SalesOrder->save($this->request->data)) {
							echo "Problema guardando la orden de venta";
							//pr($this->validateErrors($this->SalesOrder));
							throw new Exception();
						} 
						$salesOrderId=$this->SalesOrder->id;
					}
					else {				
						if (!$this->SalesOrder->save($this->request->data)) {
							echo "Problema guardando la orden de venta";
							//pr($this->validateErrors($this->SalesOrder));
							throw new Exception();
						} 
						$salesOrderId=$this->SalesOrder->id;
						
						foreach ($this->request->data['SalesOrderProduct'] as $salesOrderProduct){
							if ($salesOrderProduct['product_id']>0){
								//pr($salesOrderProduct);
								$productArray=[];
								$productArray['SalesOrderProduct']['sales_order_id']=$salesOrderId;
								$productArray['SalesOrderProduct']['product_id']=$salesOrderProduct['product_id'];
								$productArray['SalesOrderProduct']['product_description']=$salesOrderProduct['product_description'];
								$productArray['SalesOrderProduct']['product_quantity']=$salesOrderProduct['product_quantity'];
								$productArray['SalesOrderProduct']['sales_order_product_status_id']=$salesOrderProduct['sales_order_product_status_id'];
								$productArray['SalesOrderProduct']['product_unit_price']=$salesOrderProduct['product_unit_price'];
								$productArray['SalesOrderProduct']['product_total_price']=$salesOrderProduct['product_total_price'];
								$productArray['SalesOrderProduct']['bool_iva']=$salesOrderProduct['bool_iva'];
								$productArray['SalesOrderProduct']['currency_id']=$this->request->data['SalesOrder']['currency_id'];
								
								$this->SalesOrderProduct->create();
								if (!$this->SalesOrderProduct->save($productArray)) {
									echo "Problema guardando los productos de la orden de venta";
									//pr($this->validateErrors($this->SalesOrderProduct));
									throw new Exception();
								}
								
								$sales_order_product_id=$this->SalesOrderProduct->id;
							}
						}
						
						if (!empty($this->request->data['SalesOrderRemark']['remark_text'])){
							$salesOrderRemark=$this->request->data['SalesOrderRemark'];
							//pr($quotationRemark);
							$salesOrderRemarkArray=[];
							$salesOrderRemarkArray['SalesOrderRemark']['user_id']=$salesOrderRemark['user_id'];
							$salesOrderRemarkArray['SalesOrderRemark']['sales_order_id']=$salesOrderId;
							$salesOrderRemarkArray['SalesOrderRemark']['remark_datetime']=date('Y-m-d H:i:s');
							$salesOrderRemarkArray['SalesOrderRemark']['remark_text']=$salesOrderRemark['remark_text'];
							$salesOrderRemarkArray['SalesOrderRemark']['working_days_before_reminder']=$salesOrderRemark['working_days_before_reminder'];
							$salesOrderRemarkArray['SalesOrderRemark']['reminder_date']=$salesOrderRemark['reminder_date'];
							$salesOrderRemarkArray['SalesOrderRemark']['action_type_id']=$salesOrderRemark['action_type_id'];
							$this->SalesOrderRemark->create();
							if (!$this->SalesOrderRemark->save($salesOrderRemarkArray)) {
								echo "Problema guardando las remarcas para la orden de venta";
								pr($this->validateErrors($this->SalesOrderRemark));
								throw new Exception();
							}
						}
					}
					$datasource->commit();
					$this->recordUserAction($this->SalesOrder->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se registró la orden de venta número ".$this->request->data['SalesOrder']['sales_order_code']);
					
					$this->Session->setFlash(__('The sales order has been saved.'),'default',['class' => 'success']);
					return $this->redirect(['action' => 'detalle',$salesOrderId]);
				}
				catch(Exception $e){
					$datasource->rollback();
					$this->Session->setFlash(__('No se guardó la orden de venta.'), 'default',['class' => 'error-message']);
				}			
			}
		} 
		else {
			$options = [
				'conditions' => [
					'SalesOrder.id' => $id,
				],
				'contain'=>[
					'SalesOrderProduct'=>[
						'Product'=>[
						],
					],
					'ProductionOrder',
				],
			];
			$this->request->data = $this->SalesOrder->find('first', $options);
			for ($sop=0;$sop<count($this->request->data['SalesOrderProduct']);$sop++){
				$this->request->data['SalesOrderProduct'][$sop]['bool_no_iva']=$this->request->data['SalesOrderProduct'][$sop]['Product']['bool_no_iva'];
				$requestProducts[]['SalesOrderProduct']=$this->request->data['SalesOrderProduct'][$sop];
			}
		}
		
		$this->set(compact('requestProducts'));
		
		$this->loadModel('SalesOrderRemark');
		$this->SalesOrderRemark->recursive=-1;
		$salesOrderRemarks=$this->SalesOrderRemark->find('all',[
			'conditions'=>[
				'SalesOrderRemark.sales_order_id'=>$id,
			],
			'contain'=>[
				'User',
			],
		]);
		$this->set(compact('salesOrderRemarks'));
		
		$relatedQuotation=$this->Quotation->read(null,$this->request->data['SalesOrder']['quotation_id']);
		$this->set(compact('relatedQuotation'));	
		
		$loggedUserId=$this->Auth->User('id');
		$this->set(compact('user_id'));
		$userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
		
		$quotationsInSalesOrders=$this->SalesOrder->find('list',[
			'fields'=>'SalesOrder.quotation_id',
			'conditions'=>[
				'SalesOrder.id !='=>$id,
			],
			'order'=>'SalesOrder.quotation_id',
		]);
		//pr($quotationsInSalesOrders);
		
		$quotationConditions=[
			'Quotation.id !='=>$quotationsInSalesOrders,
		];
		if ($userRoleId!=ROLE_ADMIN){
			$quotationConditions[]=['Quotation.user_id'=>$loggedUserId];
		}
		$quotations = $this->SalesOrder->Quotation->find('list',[
			'conditions'=>$quotationConditions,
			'order'=>'Quotation.quotation_code',
		]);
		$this->set(compact('quotations'));
		
		$currencies = $this->SalesOrder->Currency->find('list');
		$this->set(compact('quotations','currencies'));
		
		$products=$this->Product->find('list',['order'=>'Product.name']);		
    $this->set(compact('products'));
		
		$salesOrderProductStatuses=$this->SalesOrderProductStatus->find('list');
		$this->set(compact('salesOrderProductStatuses'));
		
		$this->loadModel('Client');
		$clients = $this->Client->find('list',['order'=>'Client.name']);
		$this->set(compact('clients'));
		
		$salesOrderDateAsString=$this->SalesOrder->deconstruct('sales_order_date',$this->request->data['SalesOrder']['sales_order_date']);
		$salesOrderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($salesOrderDateAsString);
		$exchangeRateSalesOrder=$salesOrderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateSalesOrder'));
		
		$this->loadModel('ActionType');
		$actionTypes=$this->ActionType->find('list',['order'=>'ActionType.list_order ASC']);
		$this->set(compact('actionTypes'));
		
		$loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
		
		$aco_name="SalesOrders/autorizar";		
		$bool_autorizar_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_autorizar_permission'));
		
		$aco_name="Quotations/resumen";		
		$bool_quotation_index_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_index_permission'));
		$aco_name="Quotations/crear";		
		$bool_quotation_add_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_quotation_add_permission'));
	}
*/
}
