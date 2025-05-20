<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class OrdersController extends AppController {

	public $components = array('Paginator','RequestHandler');
  public $helpers = ['PhpExcel'];
	
  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('setDueDate','imprimirVenta','guardarResumenVentasRemisiones','guardarResumenDescuadresSubtotalesSumaProductosVentasRemisiones','guardarResumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones','guardarResumenComprasRealizadas','verPdfEntrada','verPdfVenta','verPdfRemision','sortByTotalForClient','guardarReporteCierre','guardarReporteVentasCliente','verPagoEntradas','verPagoEntradasPdf','facturasPorVendedor');		
	}
	
	public function setDueDate(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
		$clientId=trim($_POST['clientId']);
		$emissionDay=trim($_POST['emissionDay']);
		$emissionMonth=trim($_POST['emissionMonth']);
		$emissionYear=trim($_POST['emissionYear']);
	
		$this->loadModel('ThirdParty');
		if (!$clientId){
			throw new NotFoundException(__('Cliente no está presente'));
		}
		if (!$this->ThirdParty->exists($clientId)) {
			throw new NotFoundException(__('Cliente inválido'));
		}
		
		$client=$this->ThirdParty->getClientById($clientId);
		
		$creditPeriod=0;
		if (!empty($client)){
			$creditPeriod=$client['ThirdParty']['credit_days'];
		}
		$emissionDateString=$emissionYear.'-'.$emissionMonth.'-'.$emissionDay;
		$emissionDate=date( "Y-m-d", strtotime($emissionDateString));
		
		$dueDate=$emissionDate;
		if($creditPeriod>0){
			$dueDate=date("Y-m-d",strtotime($emissionDate."+".$creditPeriod." days"));
		}
		
		$this->set(compact('dueDate'));
	}
	
	public function resumenEntradas($lastMonth=0) {
    $this->loadModel('ProductType');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
		$loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $this->Order->recursive = -1;
    
    $startDate = null;
		$endDate = null;
    
    $providerId=0;
		$warehouseId=0;
    
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $warehouseId=$this->request->data['Report']['warehouse_id'];
      
      $providerId=$this->request->data['Report']['provider_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
	
    $this->set(compact('providerId'));
		
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
		
		$purchaseConditions=[
			'Order.stock_movement_type_id'=> [MOVEMENT_PURCHASE,MOVEMENT_PURCHASE_CONSUMIBLES],
			'Order.order_date >='=> $startDate,
			'Order.order_date <'=> $endDatePlusOne,
      'Order.warehouse_id'=> $warehouseId,
		];
    if ($providerId > 0){
        $purchaseConditions['Order.third_party_id']=$providerId;
    }
		
		$purchaseCount=$this->Order->find('count', [
			'conditions' => $purchaseConditions,
		]);
		
		$purchases=[];
		$this->Paginator->settings = [
			'conditions' => $purchaseConditions,
			'contain'=>[
        'PurchaseOrder'=>[
          'fields'=>['PurchaseOrder.id','PurchaseOrder.purchase_order_code'],
        ],
				'StockMovement'=>[
					'fields'=>['product_id','product_quantity'],
          'conditions'=>['product_quantity >'=> 0],
					'Product'=>[
						'fields'=>['product_type_id','name'],
						'ProductType'=>[
							'fields'=>['product_category_id'],
						],
					],
				],
				'ThirdParty'=>[
					'fields'=>['id','company_name'],
				],
			],
			'order'=>'order_date DESC,order_code DESC',
			'limit'=>($purchaseCount!=0?$purchaseCount:1)
		];
		$purchases = $this->Paginator->paginate('Order');
    //pr($purchases);
		$this->set(compact('purchases', 'startDate','endDate'));
		
		$orderIdsForPeriod=$this->Order->find('list',[
			'fields'=>['Order.id'],
			'conditions' => $purchaseConditions,
		]);
		
		$consideredProductTypeIds=$this->ProductType->find('list',[
			'conditions'=>[
        'ProductType.product_category_id'=>[CATEGORY_RAW,CATEGORY_OTHER,CATEGORY_CONSUMIBLE],
      ],
			'order'=>'ProductType.product_category_id ASC, ProductType.name ASC',
		]);
    //pr($consideredProductTypeIds);
    $consideredProductTypeIds = [PRODUCT_TYPE_PREFORMA => $consideredProductTypeIds[PRODUCT_TYPE_PREFORMA]] + $consideredProductTypeIds;
    //pr($consideredProductTypeIds);
    $consideredProductTypeIds = [PRODUCT_TYPE_INJECTION_GRAIN => $consideredProductTypeIds[PRODUCT_TYPE_INJECTION_GRAIN]] + $consideredProductTypeIds;
    
    $consideredProductTypes=[];
    foreach ($consideredProductTypeIds as $typeId=>$typeName){
      $productType=$this->ProductType->find('first',[
        'conditions'=>['ProductType.id'=>$typeId],
        'contain'=>[
          'Product'=>[
            'StockMovement'=>[
              'conditions'=>['StockMovement.order_id'=>$orderIdsForPeriod],
            ],
          ],
        ],
      ]);
      $consideredProductTypes[$typeId]['ProductType']=$productType['ProductType'];
      $consideredProductTypes[$typeId]['Product']=$productType['Product'];
    }
    
		//pr($consideredProductTypes);
		foreach ($consideredProductTypes as $typeId=>$typeData){
			for ($p=0;$p<count($typeData['Product']);$p++){
				$packagingUnitProduct=$typeData['Product'][$p]['packaging_unit'];
				$totalQuantityProduct=0;
				$totalCostProduct=0;
				foreach ($typeData['Product'][$p]['StockMovement'] as $stockMovement){
					$totalQuantityProduct+=$stockMovement['product_quantity'];
					$totalCostProduct+=$stockMovement['product_total_price'];
				}
				if ($packagingUnitProduct>0){
					$consideredProductTypes[$typeId]['Product'][$p]['total_packages']=round($totalQuantityProduct/$packagingUnitProduct);
				}
				else {
					$consideredProductTypes[$typeId]['Product'][$p]['total_packages']=-1;
				}
				
				$consideredProductTypes[$typeId]['Product'][$p]['total_quantity_product']=$totalQuantityProduct;
				$consideredProductTypes[$typeId]['Product'][$p]['total_cost_product']=$totalCostProduct;
			}
		}
		//pr($consideredProductTypes);
		$this->set(compact('consideredProductTypes'));
    
    $providers=$this->Order->ThirdParty->getActiveProviderList();
    $this->set(compact('providers'));
		
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}

  public function resumenVentasRemisiones() {
    $this->loadModel('ProductType');
    
    $this->loadModel('ClientType');
    $this->loadModel('Zone');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $canSeeUtilityTables=$this->UserPageRight->hasUserPageRight('VER_RESUMEN_EJECUTIVO',$userRoleId,$loggedUserId,'Orders','resumenVentasRemisiones');
    $this->set(compact('canSeeUtilityTables'));
    //echo 'can see tables is '.$canSeeUtilityTables.'<br/>';
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'Orders','resumenVentasRemisiones');
    $this->set(compact('canSeeAllUsers'));
    
		$startDate = null;
		$endDate = null;
    
    define('INVOICES_ALL','0');
    define('INVOICES_CASH','1');
    define('INVOICES_CREDIT','2');
    
    $paymentOptions=[
      INVOICES_ALL=>'Todas Facturas',
      INVOICES_CASH=>'Solo Facturas de Contado',
      INVOICES_CREDIT=>'Solo Facturas de Crédito',
    ];
    $this->set(compact('paymentOptions'));
    
    define('SALE_TYPE_ALL','0');
    define('SALE_TYPE_BOTTLE','1');
    define('SALE_TYPE_CAP','2');
    define('SALE_TYPE_SERVICE','3');
    //define('SALE_TYPE_CONSUMIBLE','4');
    define('SALE_TYPE_IMPORT','5');
    //define('SALE_TYPE_LOCAL','6');
    define('SALE_TYPE_INJECTION','7');
    
    $saleTypeOptions=[
      SALE_TYPE_ALL=>'-- Todos Productos --',
      SALE_TYPE_BOTTLE=>'Botellas',
      SALE_TYPE_CAP=>'Tapones',
      SALE_TYPE_SERVICE=>'Servicios',
      //SALE_TYPE_CONSUMIBLE=>'Consumibles',
      SALE_TYPE_IMPORT=>'Importados',
      //SALE_TYPE_LOCAL=>'Locales',
      SALE_TYPE_INJECTION=>'ProductosIngroup',
    ];
    $this->set(compact('saleTypeOptions'));
    $paymentOptionId=0;
    $saleTypeOptionId=0;
    $warehouseId=0;
    $userId=$loggedUserId;
    $clientTypeId=0;
    $zoneId=0;
    
    if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      $userId=0;
    }
    else {
      $userId=$loggedUserId;
    }
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
      //pr($startDateArray);
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $warehouseId=$this->request->data['Report']['warehouse_id'];
      $paymentOptionId=$this->request->data['Report']['payment_option_id'];
      
      $clientTypeId=$this->request->data['Report']['client_type_id'];
      $zoneId=$this->request->data['Report']['zone_id'];
      
      $saleTypeOptionId=$this->request->data['Report']['sale_type_option_id'];
      $userId=$this->request->data['Report']['user_id'];
      //pr($this->request->data);
      
      if (!empty($this->request->data['onlyBottle'])){
        $saleTypeOptionId=SALE_TYPE_BOTTLE;
      }
      if (!empty($this->request->data['onlyCap'])){
        //echo "only caps are shown<br/>";
        $saleTypeOptionId=SALE_TYPE_CAP;
      }
      if (!empty($this->request->data['onlyService'])){
        $saleTypeOptionId=SALE_TYPE_SERVICE;
      }
      //if (!empty($this->request->data['onlyConsumible'])){
      //  $saleTypeOptionId=SALE_TYPE_CONSUMIBLE;
      //}
      if (!empty($this->request->data['onlyImport'])){
        $saleTypeOptionId=SALE_TYPE_IMPORT;
      }
      if (!empty($this->request->data['onlyInjection'])){
        $saleTypeOptionId=SALE_TYPE_INJECTION;
      }
      if (!empty($this->request->data['onlyLocal'])){
        $saleTypeOptionId=SALE_TYPE_LOCAL;
      }
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('paymentOptionId'));
    $this->set(compact('clientTypeId'));
    $this->set(compact('zoneId'));
    $this->set(compact('saleTypeOptionId'));
    $this->set(compact('userId'));
	
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
    
    $users=$this->User->getActiveVendorUserList($warehouseId);
    //if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
    //  $users=$this->User->getActiveVendorAdminUserList($warehouseId);
    //}
    //elseif ($canSeeAllVendors) {
    //  $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    //}
    //else {
    //  $users=$this->User->getActiveUserList($loggedUserId);
    //}
    $this->set(compact('users'));
    //pr($users);
    
    $allProductTypes=$this->ProductType->find('list');
    $this->set(compact('allProductTypes'));
  
		$conditions=[
      'Order.stock_movement_type_id'=> MOVEMENT_SALE,
      'Order.order_date >='=> $startDate,
      'Order.order_date <'=> $endDatePlusOne,
      //'Order.warehouse_id'=> $warehouseId,
    ];
    if ($clientTypeId > 0){
      $conditions['Order.client_type_id']=$clientTypeId;
    }
    if ($zoneId > 0){
      $conditions['Order.zone_id']=$zoneId;
    }
    if ($userId > 0){
      $conditions['Order.vendor_user_id']=$userId;
    }
    //else {
    //  $conditions['Order.vendor_user_id']=array_keys($users);
    //}
    //pr($conditions);
    
    $salesForPeriod=$this->Order->find('all',[
			'fields'=>[],
			'conditions' => $conditions,
			'contain'=>[
				'ThirdParty'=>['fields'=>[
          'id','company_name','bool_generic']
        ],
				'StockMovement'=>[
					'fields'=>['StockMovement.product_quantity','StockMovement.production_result_code_id',
            'StockMovement.product_total_price',
            'StockMovement.service_unit_cost','StockMovement.service_total_cost'
          ],
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
				'CashReceipt'=>[
					'fields'=>[
						'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
						'CashReceipt.currency_id','CashReceipt.amount',
					],
					'Currency'
				],
			],
			'order'=>'order_date DESC,order_code DESC',
		]);
		//pr($salesForPeriod);
		
		$quantitySales=0;
		// loop to determine quantity
		foreach ($salesForPeriod as $sale){
			if (!empty($sale['Invoice'])){
				if ($sale['Invoice'][0]['bool_annulled']){
					$quantitySales+=1;
				}
			}
			else {
				foreach ($sale['StockMovement'] as $stockMovement){
					if ($stockMovement['Product']['ProductType']['product_category_id'] == CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						$quantitySales+=1;
					}
					elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
						$quantitySales+=1;
					}
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantitySales!=0?$quantitySales:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProduced=0;
		$totalQuantityCap=0;
    $totalQuantityService=0;
    //$totalQuantityConsumible=0;
    $totalQuantityImport=0;
    $totalQuantityInjection=0;
    $totalQuantityLocal=0;
    $totalQuantityOthers=[];
		//pr($salesForPeriod);
		$sales=[];
		// loop to get extended information
		foreach ($salesForPeriod as $sale){
      //if ($sale['Order']['id']==4391){
        //pr($sale);  
      //}
			$quantityProduced=0;
			$quantityCap=0;
      $quantityService=0;
      //$quantityConsumible=0;
      $quantityImport=0;
      $quantityInjection=0;
      $quantityLocal=0;
      $quantityOthers=[];
      
      $priceProduced=0;
      $priceCap=0;
      $priceService=0;
      //$priceConsumible=0;
      $priceImport=0;
      $priceInjection=0;
      $priceLocal=0;
      $priceOthers=[];
      
      $costProduced=0;
      $costCap=0;
      $costService=0;
      //$costConsumible=0;
      $costImport=0;
      $costInjection=0;
      $costLocal=0;
      $costOthers=[];
      
			$totalCost=0;
      
      $salesOtherProductTypes=[];
      
			foreach ($sale['StockMovement'] as $stockMovement){
        //pr ($stockMovement);
        $qualifiedStockMovement='0';
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED){
          if ($stockMovement['Product']['product_type_id'] == PRODUCT_TYPE_INJECTION_OUTPUT){
            $quantityInjection+=$stockMovement['product_quantity'];
            $totalQuantityInjection+=$stockMovement['product_quantity'];
            
            $priceInjection+=$stockMovement['product_total_price'];
            $costInjection+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            $qualifiedStockMovement=true;
          }
          elseif ($stockMovement['production_result_code_id'] == PRODUCTION_RESULT_CODE_A) {       
            $quantityProduced+=$stockMovement['product_quantity'];
            $totalQuantityProduced+=$stockMovement['product_quantity'];
            
            $priceProduced+=$stockMovement['product_total_price'];
            $costProduced+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            $qualifiedStockMovement=true;
          }
					
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
          if ($stockMovement['Product']['product_type_id'] == PRODUCT_TYPE_CAP){
            $quantityCap+=$stockMovement['product_quantity'];
            $totalQuantityCap+=$stockMovement['product_quantity'];
            
            $priceCap+=$stockMovement['product_total_price'];
            $costCap+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            $qualifiedStockMovement=true;
          }
          else if ($stockMovement['Product']['product_type_id'] == PRODUCT_TYPE_INJECTION_OUTPUT){
            $quantityInjection+=$stockMovement['product_quantity'];
            $totalQuantityInjection+=$stockMovement['product_quantity'];
            
            $priceInjection+=$stockMovement['product_total_price'];
            $costInjection+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            $qualifiedStockMovement=true;
          }
          elseif ($stockMovement['Product']['product_type_id'] == PRODUCT_TYPE_SERVICE){
            $quantityService+=$stockMovement['product_quantity'];
            $totalQuantityService+=$stockMovement['product_quantity'];
            
            $priceService+=$stockMovement['product_total_price'];
            $costService+=$stockMovement['product_quantity']*$stockMovement['service_unit_cost'];
            
            $qualifiedStockMovement=true;
          }
          elseif ($stockMovement['Product']['product_type_id'] == PRODUCT_TYPE_IMPORT){
            $quantityImport+=$stockMovement['product_quantity'];
            $totalQuantityImport+=$stockMovement['product_quantity'];
            
            $priceImport+=$stockMovement['product_total_price'];
            $costImport+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            $qualifiedStockMovement=true;
          }
          
          // elseif ($stockMovement['Product']['product_type_id'] == PRODUCT_TYPE_LOCAL){
            // $quantityLocal+=$stockMovement['product_quantity'];
            // $totalQuantityLocal+=$stockMovement['product_quantity'];
            
            // $priceLocal+=$stockMovement['product_total_price'];
            // $costLocal+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            // $qualifiedStockMovement=true;
          // }
          else {
            $productTypeId=$stockMovement['Product']['product_type_id'];
            if (!array_key_exists($productTypeId,$totalQuantityOthers)){
              $totalQuantityOthers[$productTypeId]=0;
            }
            if (!array_key_exists($productTypeId,$quantityOthers)){
              $quantityOthers[$productTypeId]=0;
              $priceOthers[$productTypeId]=0;
              $costOthers[$productTypeId]=0;
            }
            //if ($stockMovement['Product']['product_nature_id'] == PRODUCT_NATURE_BOTTLES_BOUGHT){
              $quantityOthers[$productTypeId]+=$stockMovement['product_quantity'];
              $totalQuantityOthers[$productTypeId]+=$stockMovement['product_quantity'];
            //}
            
            $priceOthers[$productTypeId]+=$stockMovement['product_total_price'];
            $costOthers[$productTypeId]+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            $qualifiedStockMovement=true;
          }
				}
        elseif ($stockMovement['Product']['ProductType']['product_category_id'] == CATEGORY_CONSUMIBLE) {
          $quantityConsumible+=$stockMovement['product_quantity'];
          $totalQuantityConsumible+=$stockMovement['product_quantity'];
          
          $priceConsumible+=$stockMovement['product_total_price'];
          $costConsumible+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
          
          $qualifiedStockMovement=true;
				}
        if ($qualifiedStockMovement){
          if ($stockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
            $totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
          }
          else {
            $totalCost+=$stockMovement['product_quantity']*$stockMovement['service_unit_cost'];
          }
        }				
			}
      $summedPriceOthers=0;
      if (!empty($priceOthers)){
        foreach ($priceOthers as $priceOtherProductTypeId => $priceOther){
          $summedPriceOthers+=$priceOther;
        }
      }
      $summedQuantityOthers=0;
      //pr($quantityOthers);
      if (!empty($quantityOthers)){
        foreach ($quantityOthers as $productTypeId=>$productTypeQuantity){
          $summedQuantityOthers+=$productTypeQuantity;
        }
      }
			//echo 'quantity Injection is '.$quantityInjection.'<br/>';
      if (
        (
          ($quantityProduced+$quantityCap+$quantityService+$quantityImport+$quantityInjection+$summedQuantityOthers) > 0
        ) || 
        (
          !empty($sale['Invoice'])&&
          (
            $sale['Invoice'][0]['bool_annulled'] ||
            empty($sale['StockMovement'])
          )
        ) ||
        (
          empty($sale['Invoice']) &&
          empty($sale['CashReceipt']) &&
          empty($sale['StockMovement'])
        )
      ){
				$sales[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$sales[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$sales[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
        $sales[$rowCounter]['Order']['warehouse_id']=$sale['Order']['warehouse_id'];
        if (!$sale['ThirdParty']['bool_generic']){
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
        }
        else {
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['Order']['client_name'];
        }
				$sales[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
        $sales[$rowCounter]['ThirdParty']['bool_generic']=$sale['ThirdParty']['bool_generic'];
				$sales[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
        $sales[$rowCounter]['Order']['price_produced']=$priceProduced;
        $sales[$rowCounter]['Order']['price_cap']=$priceCap;
        $sales[$rowCounter]['Order']['price_service']=$priceService;
        //$sales[$rowCounter]['Order']['price_consumible']=$priceConsumible;
        $sales[$rowCounter]['Order']['price_import']=$priceImport;
        $sales[$rowCounter]['Order']['price_injection']=$priceInjection;
        //$sales[$rowCounter]['Order']['price_local']=$priceLocal;
        if (!empty($priceOthers)){
          foreach ($priceOthers as $priceOtherProductTypeId => $priceOther){
            $sales[$rowCounter]['Order']['price_others'][$priceOtherProductTypeId]=$priceOther;  
          }
        }
        else {
          $sales[$rowCounter]['Order']['price_others']=[];
        }
        
        $sales[$rowCounter]['Order']['total_cost']=$totalCost;
        $sales[$rowCounter]['Order']['cost_produced']=$costProduced;
        $sales[$rowCounter]['Order']['cost_cap']=$costCap;
        $sales[$rowCounter]['Order']['cost_service']=$costService;
        //$sales[$rowCounter]['Order']['cost_consumible']=$costConsumible;
        $sales[$rowCounter]['Order']['cost_import']=$costImport;
        $sales[$rowCounter]['Order']['cost_injection']=$costInjection;
        //$sales[$rowCounter]['Order']['cost_local']=$costLocal;
        if (!empty($costOthers)){
          foreach ($costOthers as $costOtherProductTypeId => $costOther){
            $sales[$rowCounter]['Order']['cost_others'][$costOtherProductTypeId]=$costOther;  
          }
        }
        else {
          $sales[$rowCounter]['Order']['cost_others']=[];
        }
				$sales[$rowCounter]['Order']['quantity_cap']=$quantityCap;
				$sales[$rowCounter]['Order']['quantity_produced']=$quantityProduced;
        $sales[$rowCounter]['Order']['quantity_service']=$quantityService;
        //$sales[$rowCounter]['Order']['quantity_consumible']=$quantityConsumible;
        $sales[$rowCounter]['Order']['quantity_import']=$quantityImport;
        $sales[$rowCounter]['Order']['quantity_injection']=$quantityInjection;
        //$sales[$rowCounter]['Order']['quantity_local']=$quantityLocal;
        if (!empty($quantityOthers)){
          foreach ($quantityOthers as $quantityOtherProductTypeId => $quantityOther){
            $sales[$rowCounter]['Order']['quantity_others'][$quantityOtherProductTypeId]=$quantityOther;  
          }
        }
        else {
          $sales[$rowCounter]['Order']['quantity_others']=[];
        }
				$sales[$rowCounter]['Order']['total_quantity_cap']=$totalQuantityCap;
				$sales[$rowCounter]['Order']['total_quantity_produced']=$totalQuantityProduced;
        $sales[$rowCounter]['Order']['total_quantity_service']=$totalQuantityService;
        //$sales[$rowCounter]['Order']['total_quantity_consumible']=$totalQuantityConsumible;
        $sales[$rowCounter]['Order']['total_quantity_import']=$totalQuantityImport;
        $sales[$rowCounter]['Order']['total_quantity_injection']=$totalQuantityInjection;
        //$sales[$rowCounter]['Order']['total_quantity_local']=$totalQuantityLocal;
        if (!empty($totalQuantityOthers)){
          foreach ($totalQuantityOthers as $productTypeId => $totalQuantityOther){
            $sales[$rowCounter]['Order']['total_quantity_others'][$productTypeId]=$totalQuantityOther;  
            if (!array_key_exists($productTypeId,$salesOtherProductTypes)){
              $salesOtherProductTypes[$productTypeId]=$allProductTypes[$productTypeId];
            }
          }
        }
        else {
          $sales[$rowCounter]['Order']['total_quantity_others']=[];
        }
				if (!empty($sale['Invoice'])){
					//pr($sale);
					$sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
					if($sale['Invoice'][0]['bool_annulled']){
						$sales[$rowCounter]['Invoice']['bool_annulled']=true;
					}
					else {
						$sales[$rowCounter]['Invoice']['bool_annulled']='0';
					}
				}
				else {
					$sales[$rowCounter]['Invoice']['bool_annulled']='0';
          $sales[$rowCounter]['Invoice']['bool_credit']=true;
				}
				$rowCounter++;
			}
		}
    //pr($salesOtherProductTypes);
    if (!empty($salesOtherProductTypes)){
      asort($salesOtherProductTypes);
    }
    //pr($salesOtherProductTypes);
    $this->set(compact('salesOtherProductTypes'));
    //pr($sales);
    
		$quantityRemissions=0;
		// loop to determine quantity remissions
		foreach ($salesForPeriod as $sale){
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityRemissions+=1;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityRemissions+=1;
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantityRemissions!=0?$quantityRemissions:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProducedB=0;
		$totalQuantityProducedC=0;
		
		$remissions=[];
		foreach ($salesForPeriod as $sale){
			$quantityProducedB=0;
			$quantityProducedC=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
        $qualifiedStockMovement='0';
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityProducedB+=$stockMovement['product_quantity'];
					$totalQuantityProducedB+=$stockMovement['product_quantity'];
          $qualifiedStockMovement=true;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityProducedC+=$stockMovement['product_quantity'];
					$totalQuantityProducedC+=$stockMovement['product_quantity'];
          $qualifiedStockMovement=true;
				}
        if ($qualifiedStockMovement){
          if ($stockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
            $totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
          }          
        }
			}
			if ((($quantityProducedB+$quantityProducedC)>0)||(!empty($sale['CashReceipt'])&&$sale['CashReceipt'][0]['bool_annulled'])){
				$remissions[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$remissions[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$remissions[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
        $remissions[$rowCounter]['Order']['warehouse_id']=$sale['Order']['warehouse_id'];
				$remissions[$rowCounter]['ThirdParty']['bool_generic']=$sale['ThirdParty']['bool_generic'];
        $remissions[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$remissions[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$remissions[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$remissions[$rowCounter]['Order']['total_cost']=$totalCost;
				$remissions[$rowCounter]['Order']['quantity_produced_B']=$quantityProducedB;
				$remissions[$rowCounter]['Order']['quantity_produced_C']=$quantityProducedC;
				$remissions[$rowCounter]['Order']['total_quantity_produced_B']=$totalQuantityProducedB;
				$remissions[$rowCounter]['Order']['total_quantity_produced_C']=$totalQuantityProducedC;
				if (!empty($sale['CashReceipt'])){
					$remissions[$rowCounter]['CashReceipt']=$sale['CashReceipt'][0];
					if ($sale['CashReceipt'][0]['bool_annulled']){
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=true;
						//pr($remissions[$rowCounter]);
					}
					else {
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']='0';
					}
				}
				else {
					$remissions[$rowCounter]['CashReceipt']['bool_annulled']='0';
				}
				$rowCounter++;
			}
		}
		
		$this->set(compact('sales','remissions','startDate','endDate'));
    
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
    //pr($sales);
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
    $aco_name="Orders/verVenta";		
		$bool_sale_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_view_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
    $aco_name="Orders/verRemision";		
		$bool_remission_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_view_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}
  
  public function resumenDescuadresSubtotalesSumaProductosVentasRemisiones() {
		$startDate = null;
		$endDate = null;
    
    define('ORDERS_ERROR','0');
    define('ORDERS_ALL','1');
    
    $displayOptions=[
      ORDERS_ERROR=>'Solo Ventas y Remisiones donde hay descuadre de subtotal vs suma de precios productos',
      ORDERS_ALL=>'Todas Ventas y Remisiones',
    ];
    $this->set(compact('displayOptions'));
    
    $displayOptionId=0;
   
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $displayOptionId=$this->request->data['Report']['display_option_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('displayOptionId'));
    
    $this->Order->recursive = -1;
		
		$salesForPeriod=$this->Order->find('all',[
			'fields'=>[],
			'contain'=>[
				'ThirdParty'=>['fields'=>['id','company_name']],
				'StockMovement'=>[
					'fields'=>['StockMovement.product_unit_price','StockMovement.product_quantity','StockMovement.product_total_price','StockMovement.production_result_code_id'],
					'Product'=>[
						'ProductType'=>[
							'fields'=>['product_category_id']
						]
					]
				],
				'Invoice'=>[
					'fields'=>[
						'Invoice.id','Invoice.invoice_code','Invoice.bool_annulled',
            //'Invoice.bool_credit',
						//'Invoice.currency_id','Invoice.total_price',
					],
					//'Currency'
				],
				'CashReceipt'=>[
					'fields'=>[
						'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
						//'CashReceipt.currency_id','CashReceipt.amount',
					],
					//'Currency'
				],
			],
			'conditions' => [
				'Order.stock_movement_type_id'=> MOVEMENT_SALE,
				'Order.order_date >='=> $startDate,
				'Order.order_date <'=> $endDatePlusOne,
			],
			'order'=>'order_date DESC,order_code DESC',
		]);
		//pr($salesForPeriod);
		
		$quantitySales=0;
		// loop to determine quantity
		foreach ($salesForPeriod as $sale){
			if (!empty($sale['Invoice'])){
				if ($sale['Invoice'][0]['bool_annulled']){
					$quantitySales+=1;
				}
			}
			else {
				foreach ($sale['StockMovement'] as $stockMovement){
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						$quantitySales+=1;
					}
					elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
						$quantitySales+=1;
					}
				}
			}
		}
		
		$this->Paginator->settings = ['limit'=>($quantitySales!=0?$quantitySales:1)];
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		//$totalQuantityProduced=0;
		//$totalQuantityOther=0;
		//pr($salesForPeriod);
		$sales=[];
		// loop to get extended information
		foreach ($salesForPeriod as $sale){
			$quantityProduced=0;
			$quantityOther=0;
			//$totalCost=0;
      
      $priceProductsCSBasedOnUnitPriceQuantity=0;
      $priceProductsCSBasedOnTotals=0;
    
			foreach ($sale['StockMovement'] as $stockMovement){
        //pr ($stockMovement);
				
        if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
					$quantityProduced+=$stockMovement['product_quantity'];
					//$totalQuantityProduced+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
					$quantityOther+=$stockMovement['product_quantity'];
					//$totalQuantityOther+=$stockMovement['product_quantity'];
				}
				
				//$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
        
        $priceProductsCSBasedOnUnitPriceQuantity+=($stockMovement['product_unit_price']*$stockMovement['product_quantity']);
        $priceProductsCSBasedOnTotals+=$stockMovement['product_total_price'];
			}
			if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement'])))||(empty($sale['Invoice'])&&empty($sale['CashReceipt'])&&empty($sale['StockMovement']))){
				$sales[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$sales[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$sales[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
        if ($sale['ThirdParty']['id'] != CLIENTS_VARIOUS){
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
        }
        else {
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['Order']['client_name'];
        }
				$sales[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
        $sales[$rowCounter]['Order']['price_products_unit_quantity']=$priceProductsCSBasedOnUnitPriceQuantity;
        $sales[$rowCounter]['Order']['price_products_total']=$priceProductsCSBasedOnTotals;
        // yes that is right, the total_price field in the order corresponds with the sub_total_price  of the invoice
				$sales[$rowCounter]['Order']['sub_total_price']=$sale['Order']['total_price'];
				//$sales[$rowCounter]['Order']['total_cost']=$totalCost;
				//$sales[$rowCounter]['Order']['quantity_other']=$quantityOther;
				//$sales[$rowCounter]['Order']['quantity_produced']=$quantityProduced;
				//$sales[$rowCounter]['Order']['total_quantity_other']=$totalQuantityOther;
				//$sales[$rowCounter]['Order']['total_quantity_produced']=$totalQuantityProduced;
				if (!empty($sale['Invoice'])){
					//pr($sale);
					$sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
					if($sale['Invoice'][0]['bool_annulled']){
						$sales[$rowCounter]['Invoice']['bool_annulled']=true;
					}
					else {
						$sales[$rowCounter]['Invoice']['bool_annulled']='0';
					}
				}
				else {
					$sales[$rowCounter]['Invoice']['bool_annulled']='0';
          //$sales[$rowCounter]['Invoice']['bool_credit']=true;
				}
				$rowCounter++;
			}
		}
	
		$quantityRemissions=0;
		// loop to determine quantity remissions
		foreach ($salesForPeriod as $sale){
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityRemissions+=1;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityRemissions+=1;
				}
			}
		}
		
		$this->Paginator->settings = [
			'limit'=>($quantityRemissions!=0?$quantityRemissions:1)
		];
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProducedB=0;
		$totalQuantityProducedC=0;
		
		$remissions=[];
		foreach ($salesForPeriod as $sale){
			$quantityProducedB=0;
			$quantityProducedC=0;
			//$totalCost=0;
			
      $priceProductsCSBasedOnUnitPriceQuantity=0;
      $priceProductsCSBasedOnTotals=0;

      foreach ($sale['StockMovement'] as $stockMovement){
        
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityProducedB+=$stockMovement['product_quantity'];
					//$totalQuantityProducedB+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityProducedC+=$stockMovement['product_quantity'];
					//$totalQuantityProducedC+=$stockMovement['product_quantity'];
				}
        
        $priceProductsCSBasedOnUnitPriceQuantity+=($stockMovement['product_unit_price']*$stockMovement['product_quantity']);
        $priceProductsCSBasedOnTotals+=$stockMovement['product_total_price'];
			}
			if ((($quantityProducedB+$quantityProducedC)>0)||(!empty($sale['CashReceipt'])&&$sale['CashReceipt'][0]['bool_annulled'])){
				$remissions[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$remissions[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$remissions[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$remissions[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$remissions[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
        $remissions[$rowCounter]['Order']['price_products_unit_quantity']=$priceProductsCSBasedOnUnitPriceQuantity;
        $remissions[$rowCounter]['Order']['price_products_total']=$priceProductsCSBasedOnTotals;
        $remissions[$rowCounter]['Order']['sub_total_price']=$sale['Order']['total_price'];
				//$remissions[$rowCounter]['Order']['total_cost']=$totalCost;
				//$remissions[$rowCounter]['Order']['quantity_produced_B']=$quantityProducedB;
				//$remissions[$rowCounter]['Order']['quantity_produced_C']=$quantityProducedC;
				//$remissions[$rowCounter]['Order']['total_quantity_produced_B']=$totalQuantityProducedB;
				//$remissions[$rowCounter]['Order']['total_quantity_produced_C']=$totalQuantityProducedC;
				if (!empty($sale['CashReceipt'])){
					$remissions[$rowCounter]['CashReceipt']=$sale['CashReceipt'][0];
					if ($sale['CashReceipt'][0]['bool_annulled']){
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=true;
						//pr($remissions[$rowCounter]);
					}
					else {
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']='0';
					}
				}
				else {
					$remissions[$rowCounter]['CashReceipt']['bool_annulled']='0';
				}
				$rowCounter++;
			}
		}
		
		$this->set(compact('sales','remissions','startDate','endDate'));
		//pr($sales);
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
    $aco_name="Orders/verVenta";		
		$bool_sale_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_view_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
    $aco_name="Orders/verRemision";		
		$bool_remission_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_view_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

  public function resumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones() {
		$startDate = null;
		$endDate = null;
    
    define('ORDERS_ERROR','0');
    define('ORDERS_ALL','1');
    
    $displayOptions=[
      ORDERS_ERROR=>'Solo Ventas donde hay descuadre de total vs suma subtotal+iva',
      ORDERS_ALL=>'Todas Ventas',
    ];
    $this->set(compact('displayOptions'));
    
    $displayOptionId=0;
    
    $paymentOptionId=0;
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $displayOptionId=$this->request->data['Report']['display_option_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('displayOptionId'));
	
		$this->Order->recursive = -1;
		
		$salesForPeriod=$this->Order->find('all',[
			'fields'=>[],
			'contain'=>[
				'ThirdParty'=>['fields'=>['id','company_name','bool_generic']],
				'StockMovement'=>[
					'fields'=>['StockMovement.product_unit_price','StockMovement.product_quantity','StockMovement.product_total_price','StockMovement.production_result_code_id'],
					'Product'=>[
						'ProductType'=>[
							'fields'=>['product_category_id']
						]
					]
				],
				'Invoice'=>[
					'fields'=>[
						'Invoice.id','Invoice.invoice_code','Invoice.bool_annulled',
            'Invoice.sub_total_price','Invoice.iva_price','Invoice.total_price','Invoice.currency_id',
					],
				],
				'CashReceipt'=>[
					'fields'=>[
						'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
					],
				],
			],
			'conditions' => [
				'Order.stock_movement_type_id'=> MOVEMENT_SALE,
				'Order.order_date >='=> $startDate,
				'Order.order_date <'=> $endDatePlusOne,
			],
			'order'=>'order_date DESC,order_code DESC',
		]);
		//pr($salesForPeriod);
		
		$quantitySales=0;
		// loop to determine quantity
		foreach ($salesForPeriod as $sale){
			if (!empty($sale['Invoice'])){
				if ($sale['Invoice'][0]['bool_annulled']){
					$quantitySales+=1;
				}
			}
			else {
				foreach ($sale['StockMovement'] as $stockMovement){
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						$quantitySales+=1;
					}
					elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
						$quantitySales+=1;
					}
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantitySales!=0?$quantitySales:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		//$totalQuantityProduced=0;
		//$totalQuantityOther=0;
		//pr($salesForPeriod);
		$sales=[];
		// loop to get extended information
		foreach ($salesForPeriod as $sale){
      $quantityProduced=0;
			$quantityOther=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
        //pr ($stockMovement);
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
					$quantityProduced+=$stockMovement['product_quantity'];
					//$totalQuantityProduced+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
					$quantityOther+=$stockMovement['product_quantity'];
					//$totalQuantityOther+=$stockMovement['product_quantity'];
				}
				
				//$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement'])))||(empty($sale['Invoice'])&&empty($sale['CashReceipt'])&&empty($sale['StockMovement']))){
      //if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement'])))||(empty($sale['Invoice'])&&empty($sale['StockMovement']))){  
				$sales[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$sales[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$sales[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
        if ($sale['ThirdParty']['id'] != CLIENTS_VARIOUS){
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
        }
        else {
          $sales[$rowCounter]['ThirdParty']['company_name']=$sale['Order']['client_name'];
        }
				$sales[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
        $sales[$rowCounter]['Invoice']=$sale['Invoice'];
				if (!empty($sale['Invoice'])){
					//pr($sale);
					$sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
					if($sale['Invoice'][0]['bool_annulled']){
						$sales[$rowCounter]['Invoice']['bool_annulled']=true;
					}
					else {
						$sales[$rowCounter]['Invoice']['bool_annulled']='0';
					}
				}
				else {
					$sales[$rowCounter]['Invoice']['bool_annulled']='0';
				}
				$rowCounter++;
			}
		}
    /*
		$quantityRemissions=0;
		// loop to determine quantity remissions
		foreach ($salesForPeriod as $sale){
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityRemissions+=1;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityRemissions+=1;
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantityRemissions!=0?$quantityRemissions:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProducedB=0;
		$totalQuantityProducedC=0;
		
		$remissions=array();
		foreach ($salesForPeriod as $sale){
			$quantityProducedB=0;
			$quantityProducedC=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityProducedB+=$stockMovement['product_quantity'];
					$totalQuantityProducedB+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityProducedC+=$stockMovement['product_quantity'];
					$totalQuantityProducedC+=$stockMovement['product_quantity'];
				}
				
				$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProducedB+$quantityProducedC)>0)||(!empty($sale['CashReceipt'])&&$sale['CashReceipt'][0]['bool_annulled'])){
				$remissions[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$remissions[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$remissions[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$remissions[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$remissions[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$remissions[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$remissions[$rowCounter]['Order']['total_cost']=$totalCost;
				$remissions[$rowCounter]['Order']['quantity_produced_B']=$quantityProducedB;
				$remissions[$rowCounter]['Order']['quantity_produced_C']=$quantityProducedC;
				$remissions[$rowCounter]['Order']['total_quantity_produced_B']=$totalQuantityProducedB;
				$remissions[$rowCounter]['Order']['total_quantity_produced_C']=$totalQuantityProducedC;
				if (!empty($sale['CashReceipt'])){
					$remissions[$rowCounter]['CashReceipt']=$sale['CashReceipt'][0];
					if ($sale['CashReceipt'][0]['bool_annulled']){
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=true;
						//pr($remissions[$rowCounter]);
					}
					else {
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']='0';
					}
				}
				else {
					$remissions[$rowCounter]['CashReceipt']['bool_annulled']='0';
				}
				$rowCounter++;
			}
		}
		*/
		$this->set(compact('sales','remissions','startDate','endDate'));
		//pr($sales);
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
    $aco_name="Orders/verVenta";		
		$bool_sale_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_view_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
    $aco_name="Orders/verRemision";		
		$bool_remission_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_view_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function guardarResumenVentasRemisiones() {
		$exportData=$_SESSION['resumenVentasRemisiones'];
		$this->set(compact('exportData'));
	}	
	
  public function guardarResumenDescuadresSubtotalesSumaProductosVentasRemisiones() {
		$exportData=$_SESSION['resumenDescuadresSubtotalesSumaProductosVentasRemisiones'];
		$this->set(compact('exportData'));
	}	
	
  public function guardarResumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones() {
		$exportData=$_SESSION['resumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones'];
		$this->set(compact('exportData'));
	}	
	
  public function resumenComprasRealizadas($clientId=0) {
		$startDate = null;
		$endDate = null;
    
    define('INVOICES_ALL','0');
    define('INVOICES_CASH','1');
    define('INVOICES_CREDIT','2');
    
    $paymentOptions=[
      INVOICES_ALL=>'Todas Facturas',
      INVOICES_CASH=>'Solo Facturas de Contado',
      INVOICES_CREDIT=>'Solo Facturas de Crédito',
    ];
    $this->set(compact('paymentOptions'));
    
    $paymentOptionId=0;
    
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    if ($clientId==0){
      //pr($this->Auth->User());
      $clientId=$this->Auth->User('client_id');
    }
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $clientId=$this->request->data['Report']['client_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date( "Y-m-d", strtotime( date("Y-m-d")." - 1 months"));
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('startDate','endDate'));
    $this->set(compact('paymentOptionId'));
    $this->set(compact('clientId'));
    
		$this->Order->recursive = 0;
		
		$salesForPeriod=$this->Order->find('all',array(
			'fields'=>array(),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('id','company_name')),
				'StockMovement'=>array(
					'fields'=>array('StockMovement.product_quantity','StockMovement.production_result_code_id'),
					'StockItem'=>array(
						'fields'=>array('product_unit_price'),
					),
					'Product'=>array(
						'ProductType'=>array(
							'fields'=>array('product_category_id')
						)
					)
				),
				'Invoice'=>array(
					'fields'=>array(
						'Invoice.id','Invoice.invoice_code','Invoice.bool_annulled',
            'Invoice.bool_credit',
						'Invoice.currency_id','Invoice.total_price',
					),
					'Currency'
				),
				'CashReceipt'=>array(
					'fields'=>array(
						'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
						'CashReceipt.currency_id','CashReceipt.amount',
					),
					'Currency'
				),
			),
			'conditions' => [
				'Order.stock_movement_type_id'=> MOVEMENT_SALE,
				'Order.order_date >='=> $startDate,
				'Order.order_date <'=> $endDatePlusOne,
        'Order.third_party_id'=>$clientId,
        'Order.bool_annulled'=>false
			],
			'order'=>'order_date DESC,order_code DESC',
		));
		//pr($salesForPeriod);
		
		$quantitySales=0;
		// loop to determine quantity
		foreach ($salesForPeriod as $sale){
			//pr($sale);
			if (!empty($sale['Invoice'])){
				if ($sale['Invoice'][0]['bool_annulled']){
					$quantitySales+=1;
				}
			}
			else {
				foreach ($sale['StockMovement'] as $stockMovement){
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						$quantitySales+=1;
					}
					elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
						$quantitySales+=1;
					}
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantitySales!=0?$quantitySales:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProduced=0;
		$totalQuantityOther=0;
		
		$sales=array();
		// loop to get extended information
		foreach ($salesForPeriod as $sale){
			$quantityProduced=0;
			$quantityOther=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
				//pr ($stockMovement);
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
					$quantityProduced+=$stockMovement['product_quantity'];
					$totalQuantityProduced+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
					$quantityOther+=$stockMovement['product_quantity'];
					$totalQuantityOther+=$stockMovement['product_quantity'];
				}
				
				$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&$sale['Invoice'][0]['bool_annulled'])){
				$sales[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$sales[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$sales[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$sales[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$sales[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$sales[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$sales[$rowCounter]['Order']['total_cost']=$totalCost;
				$sales[$rowCounter]['Order']['quantity_other']=$quantityOther;
				$sales[$rowCounter]['Order']['quantity_produced']=$quantityProduced;
				$sales[$rowCounter]['Order']['total_quantity_other']=$totalQuantityOther;
				$sales[$rowCounter]['Order']['total_quantity_produced']=$totalQuantityProduced;
				if (!empty($sale['Invoice'])){
					//pr($sale);
					$sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
					if($sale['Invoice'][0]['bool_annulled']){
						$sales[$rowCounter]['Invoice']['bool_annulled']=true;
					}
					else {
						$sales[$rowCounter]['Invoice']['bool_annulled']='0';
					}
				}
				else {
					$sales[$rowCounter]['Invoice']['bool_annulled']='0';
				}
				$rowCounter++;
			}
		}
	
		$quantityRemissions=0;
		// loop to determine quantity remissions
		foreach ($salesForPeriod as $sale){
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityRemissions+=1;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityRemissions+=1;
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantityRemissions!=0?$quantityRemissions:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProducedB=0;
		$totalQuantityProducedC=0;
		
		$remissions=array();
		foreach ($salesForPeriod as $sale){
			$quantityProducedB=0;
			$quantityProducedC=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityProducedB+=$stockMovement['product_quantity'];
					$totalQuantityProducedB+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityProducedC+=$stockMovement['product_quantity'];
					$totalQuantityProducedC+=$stockMovement['product_quantity'];
				}
				
				$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProducedB+$quantityProducedC)>0)||(!empty($sale['CashReceipt'])&&$sale['CashReceipt'][0]['bool_annulled'])){
				$remissions[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$remissions[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$remissions[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$remissions[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$remissions[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$remissions[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$remissions[$rowCounter]['Order']['total_cost']=$totalCost;
				$remissions[$rowCounter]['Order']['quantity_produced_B']=$quantityProducedB;
				$remissions[$rowCounter]['Order']['quantity_produced_C']=$quantityProducedC;
				$remissions[$rowCounter]['Order']['total_quantity_produced_B']=$totalQuantityProducedB;
				$remissions[$rowCounter]['Order']['total_quantity_produced_C']=$totalQuantityProducedC;
				if (!empty($sale['CashReceipt'])){
					$remissions[$rowCounter]['CashReceipt']=$sale['CashReceipt'][0];
					if ($sale['CashReceipt'][0]['bool_annulled']){
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=true;
						//pr($remissions[$rowCounter]);
					}
					else {
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']='0';
					}
				}
				else {
					$remissions[$rowCounter]['CashReceipt']['bool_annulled']='0';
				}
				$rowCounter++;
			}
		}
		
		$this->set(compact('sales','remissions'));
    
    $this->loadModel('ThirdParty');
    $this->ThirdParty->recursive=-1;
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>[
        'ThirdParty.bool_provider' => false,
        'ThirdParty.bool_active' => true,
      ],
      'order'=>'company_name ASC',
    ]);
    $this->set(compact('clients'));
    
    $this->loadModel('PurchaseEstimation');
    $purchaseEstimation=$this->PurchaseEstimation->getPurchaseEstimation($clientId);
    //pr($purchaseEstimation);
    $this->set(compact('purchaseEstimation'));
	}

	public function guardarResumenComprasRealizadas() {
		$exportData=$_SESSION['resumenComprasRealizadas'];
		$this->set(compact('exportData'));
	}	
  
	public function verEntrada($id = null) {
		$this->Order->recursive=-1;
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid purchase'));
		}
		$order=$this->Order->getEntry($id);
		$this->set(compact('order'));
		
    $deletabilityData=$this->Order->getDeletabilityEntryData($id);
    $this->set(compact('deletabilityData'));  
    
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
	
	public function verPdfEntrada($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid purchase'));
		}
    
		$order=$this->Order->getEntry($id);
    $this->set(compact('order'));
	}
	
  public function verVenta($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid sale'));
		}
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('Invoice');
		$this->loadModel('ExchangeRate');
		
		$order=$this->Order->getFullSale($id);
		//pr($order);
		$invoice=$this->Invoice->find('first',[
			'conditions'=>[
				'Invoice.order_id'=>$id,
			],
			'contain'=>[
				'AccountingRegisterInvoice'=>[
					'AccountingRegister'=>[
						'AccountingMovement'=>[
							'AccountingCode',
						],
					],
				],
        'SalesOrder',
				'CashboxAccountingCode',
				'Currency'=>['fields'=>['Currency.id, Currency.abbreviation']],
        'CreditAuthorizationUser',
			]
		]);
    //pr($invoice);
		if (!empty($invoice)){
			$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
			$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
			
			$invoice_total_price_CS=$invoice['Invoice']['total_price'];
			if ($invoice['Invoice']['currency_id']==CURRENCY_USD){
				$invoice_total_price_CS*=$exchangeRateCurrent;
			}
			$invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($invoice['Invoice']['id']);
			$invoice['Invoice']['total_price_CS']=$invoice_total_price_CS;
			$invoice['Invoice']['pendingCS']=$invoice_total_price_CS-$invoice_paid_already_CS;
		}
    $this->StockMovement->virtualFields['total_product_quantity']=0;
    $this->StockMovement->virtualFields['total_product_cost']=0;
    $this->StockMovement->virtualFields['total_product_price']=0;
		$summedMovements=$this->StockMovement->find('all',[
			'fields'=>[
        'SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity',
        'SUM(StockMovement.product_quantity*StockMovement.product_unit_price) AS StockMovement__total_product_price',
        'StockMovement.product_unit_price', 
        'Product.name', 'Product.product_type_id', 
        'StockMovement.production_result_code_id', 
        'StockMovement.service_total_cost', 
        'ProductionResultCode.code', 
        'StockItem.raw_material_id',
        'SUM(StockMovement.product_quantity*StockItem.product_unit_price) AS StockMovement__total_product_cost',
      ],
			'conditions'=>['StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'],
			'group'=>['Product.id, StockItem.raw_material_id, ProductionResultCode.code','StockMovement.product_unit_price'],
		]);
		//pr($summedMovements);
		
		$cashReceiptsForInvoice=[];
		if (!empty($invoice)){
			if ($invoice['Invoice']['bool_credit']){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('all',array(
					'fields'=>array(
						'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.currency_id',
						'Currency.abbreviation','Currency.id',
						'CashReceipt.id','CashReceipt.receipt_date','CashReceipt.receipt_code',
					),
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$invoice['Invoice']['id'],
					),
				));
			}
		}
		//pr($cashReceiptsForInvoice);
		
		$this->set(compact('order','summedMovements','invoice','cashReceiptsForInvoice','exchangeRateCurrent'));
		
    $rawMaterials=$this->Product->getAllPreformas();
    //pr($rawMaterials);
    $this->set(compact('rawMaterials'));
    
		//if (!empty($invoice)){
		//	$creditDays=$this->Invoice->getCreditDays($invoice['Invoice']['id']);
		//}
		//$this->set(compact('creditDays'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}
	
  public function imprimirVenta($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid sale'));
		}
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('Invoice');
		$this->loadModel('ExchangeRate');
				
		$this->Product->recursive=0;
		
		$order=$this->Order->getSaleClientUsers($id);
		
		$invoice=$this->Invoice->find('first',[
			'conditions'=>[
				'Invoice.order_id'=>$id,
			],
			'contain'=>[
				'CashboxAccountingCode',
				'Currency'=>['fields'=>['Currency.id, Currency.abbreviation, Currency.full_name']],
        'CreditAuthorizationUser'=>[
          'fields'=>['CreditAuthorizationUser.username','CreditAuthorizationUser.first_name','CreditAuthorizationUser.last_name']
        ],
			]
		]);
		if (!empty($invoice)){
			$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
			$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
			
			$invoice_total_price_CS=$invoice['Invoice']['total_price'];
			if ($invoice['Invoice']['currency_id']==CURRENCY_USD){
				$invoice_total_price_CS*=$exchangeRateCurrent;
			}
			$invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($invoice['Invoice']['id']);
			$invoice['Invoice']['total_price_CS']=$invoice_total_price_CS;
			$invoice['Invoice']['pendingCS']=$invoice_total_price_CS-$invoice_paid_already_CS;
		}
		$summedMovements=$this->StockMovement->find('all',[
      'fields'=>['SUM(StockMovement.product_quantity) AS total_product_quantity,  
        StockMovement.product_unit_price, 
        Product.name, Product.packaging_unit,
        StockMovement.production_result_code_id, 
        ProductionResultCode.code, 
        StockItem.raw_material_id'],
			'conditions'=>['StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'],
			'group'=>['Product.id, StockItem.raw_material_id, ProductionResultCode.code','StockMovement.product_unit_price'],
		]);
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName=empty($summedMovements[$i]['StockItem']['raw_material_id'])?"":$this->Product->getProductName($summedMovements[$i]['StockItem']['raw_material_id']);
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		
		$this->set(compact('order','summedMovements','invoice','exchangeRateCurrent'));
	}
	 
	public function verPdfVenta($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid sale'));
		}
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('Invoice');
		$this->loadModel('ExchangeRate');
		
		$this->Product->recursive=0;
		
		$order=$this->Order->getSaleClientUsers($id);
		
		$invoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.order_id'=>$id,
			),
			'contain'=>array(
				'CashboxAccountingCode',
				'Currency'=>array('fields'=>array('Currency.id, Currency.abbreviation')),
        'CreditAuthorizationUser'=>[
          'fields'=>['CreditAuthorizationUser.username','CreditAuthorizationUser.first_name','CreditAuthorizationUser.last_name']
        ],
			)
		));
		if (!empty($invoice)){
			$exchangeRateCurrent=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
			
			$invoice_total_price_CS=$invoice['Invoice']['total_price'];
			if ($invoice['Invoice']['currency_id']==CURRENCY_USD){
				$invoice_total_price_CS*=$exchangeRateCurrent;
			}
			$invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($invoice['Invoice']['id']);
			$invoice['Invoice']['total_price_CS']=$invoice_total_price_CS;
			$invoice['Invoice']['pendingCS']=$invoice_total_price_CS-$invoice_paid_already_CS;
		}
		$summedMovements=$this->StockMovement->find('all',[
			'fields'=>['SUM(StockMovement.product_quantity) AS total_product_quantity, StockMovement.product_unit_price, Product.name, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'],
			'conditions'=>['StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'],
			'group'=>['Product.id, StockItem.raw_material_id, ProductionResultCode.code','StockMovement.product_unit_price'],
		]);
		
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName=empty($summedMovements[$i]['StockItem']['raw_material_id'])?"":$this->Product->getProductName($summedMovements[$i]['StockItem']['raw_material_id']);
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		//pr($summedMovements);
		
		$cashReceiptsForInvoice=array();
		if (!empty($invoice)){
			if ($invoice['Invoice']['bool_credit']){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('all',array(
					'fields'=>array(
						'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.amount','CashReceiptInvoice.currency_id',
						'Currency.abbreviation','Currency.id',
						'CashReceipt.id','CashReceipt.receipt_date','CashReceipt.receipt_code',
					),
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$invoice['Invoice']['id'],
					),
				));
			}
		}
		//pr($cashReceiptsForInvoice);
		
		$this->set(compact('order','summedMovements','invoice','cashReceiptsForInvoice','exchangeRateCurrent'));
	}
	
	public function verRemision($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Remisión inválido'));
		}
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('CashReceipt');
		
		$this->Product->recursive=-1;
		$this->CashReceipt->recursive=-1;
		
		$options = array(
			'conditions' => array('Order.' . $this->Order->primaryKey => $id),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('ThirdParty.id, ThirdParty.company_name')),
			),
		);
		$order=$this->Order->find('first', $options);
		
		
		$cashReceipt=$this->CashReceipt->find('first',array(
			'conditions'=>array(
				'CashReceipt.order_id'=>$id,
			),
			'contain'=>array(
				'AccountingRegisterCashReceipt'=>array(
					'AccountingRegister'=>array(
						'AccountingMovement'=>array(
							'AccountingCode',
						),
					),
				),
				'CashboxAccountingCode',
			)
		));
		
		$summedMovements=$this->StockMovement->find('all',array(
			'fields'=>array('SUM(StockMovement.product_quantity) AS total_product_quantity, StockMovement.product_unit_price, Product.name, Product.packaging_unit, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'),
			'conditions'=>array(
				'StockMovement.order_id'=>$id,
				'StockMovement.product_quantity >'=>0,
			),
			'group'=>array('Product.id, StockItem.raw_material_id, ProductionResultCode.code','StockMovement.product_unit_price'),
		));
		
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName="";
			if (!empty($summedMovements[$i]['StockItem']['raw_material_id'])){
				//$linkedRawMaterial=$this->Product->read(null,$summedMovements[$i]['StockItem']['raw_material_id']);
				$this->Product->recursive=-1;
				$linkedRawMaterial=$this->Product->find('first',array(
					'conditions'=>array(
						'Product.id'=>$summedMovements[$i]['StockItem']['raw_material_id'],
					),
				));
				//pr ($linkedRawMaterial);
				$rawMaterialName=$linkedRawMaterial['Product']['name'];
			}
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		//pr($summedMovements);
		$this->set(compact('order','summedMovements','cashReceipt'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		$aco_name="Orders/eliminarRemision";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function verPdfRemision($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Remisión inválido'));
		}
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('CashReceipt');
		
		$this->Product->recursive=-1;
		$this->CashReceipt->recursive=-1;
		
		$options = [
			'conditions' => ['Order.id' => $id],
			'contain'=>[
				'ThirdParty'=>['fields'=>['ThirdParty.id, ThirdParty.company_name']],
			],
		];
		$order=$this->Order->find('first', $options);
		
		
		$cashReceipt=$this->CashReceipt->find('first',[
			'conditions'=>[
				'CashReceipt.order_id'=>$id,
			],
			'contain'=>[
				'CashboxAccountingCode'
			],
		]);
		
		$summedMovements=$this->StockMovement->find('all',[
			'fields'=>['SUM(StockMovement.product_quantity) AS total_product_quantity, StockMovement.product_unit_price, Product.name, Product.packaging_unit, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'],
			'conditions'=>[
				'StockMovement.order_id'=>$id,
				'StockMovement.product_quantity >'=>0,
			],
			'group'=>['Product.id, StockItem.raw_material_id, ProductionResultCode.code','StockMovement.product_unit_price'],
		]);
		
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName="";
			if (!empty($summedMovements[$i]['StockItem']['raw_material_id'])){
				//$linkedRawMaterial=$this->Product->read(null,$summedMovements[$i]['StockItem']['raw_material_id']);
				$this->Product->recursive=-1;
				$linkedRawMaterial=$this->Product->find('first',[
					'conditions'=>[
						'Product.id'=>$summedMovements[$i]['StockItem']['raw_material_id'],
					],
				]);
				//pr ($linkedRawMaterial);
				$rawMaterialName=$linkedRawMaterial['Product']['name'];
			}
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		//pr($summedMovements);
		$this->set(compact('order','summedMovements','cashReceipt'));
	}
	
  public function crearEntrada() {
		$this->loadModel('Product');
		$this->loadModel('StockMovement');
		$this->loadModel('ThirdParty');
		$this->loadModel('ClosingDate');
    $this->loadModel('PurchaseOrder');
    $this->loadModel('PurchaseOrderInvoice');
    
    $this->loadModel('Unit');
    
    $this->loadModel('PlantProductType');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $warehouseId=0;
    $purchaseOrderDeliveryOptions=[
      //0=>'Entrega parcial',
      1=>'Entrega completa',
    ];
    $this->set(compact('purchaseOrderDeliveryOptions'));
    
    $requestInvoices=[];
    $requestProducts=[];
		   
    if ($this->request->is('post')) {
      $warehouseId=$this->request->data['Order']['warehouse_id'];
      
      $boolInvoiceNamesOk=true;
      $invoiceNameError="";
      $boolInvoiceIvaOk=true;
      $invoiceIvaError="";
      $boolInvoiceLineTotalsOk=true;
      $invoiceLineTotalsError="";
      $invoiceTotalBasedOnInvoices=0;
      if (!empty($this->request->data['PurchaseOrderInvoice'])){
        foreach ($this->request->data['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
          //pr($purchaseOrderInvoice);  
          if (!empty(trim($purchaseOrderInvoice['invoice_code']))){
            $requestInvoices[]['PurchaseOrderInvoice']=$purchaseOrderInvoice;
            
            if ($purchaseOrderInvoice['invoice_subtotal'] > 0 && empty($purchaseOrderInvoice['invoice_code'])){
              $boolInvoiceNamesOk='0';
              $invoiceNameError.="Hay una factura con un subtotal de ".$purchaseOrderInvoice['invoice_subtotal']." pero falta el número de la factura.";
            }
            if ($purchaseOrderInvoice['bool_iva'] == 0 && $purchaseOrderInvoice['invoice_iva'] > 0){
              $boolInvoiceIvaOk='0';
              $invoiceIvaError.="La factura ".$purchaseOrderInvoice['invoice_code']." no aplica IVA y el IVA es ".$purchaseOrderInvoice['invoice_iva'];
            }
            if ($purchaseOrderInvoice['bool_iva'] && abs($purchaseOrderInvoice['invoice_iva'] - 0.15*$purchaseOrderInvoice['invoice_subtotal'])> 0.01){
              $boolInvoiceIvaOk='0';
              $invoiceIvaError.="La factura ".$purchaseOrderInvoice['invoice_code']." aplica IVA, el IVA es ".$purchaseOrderInvoice['invoice_iva'].' y el 15% del subtotal es '.(0.15*$purchaseOrderInvoice['invoice_subtotal']);
            }
            if (abs($purchaseOrderInvoice['invoice_total'] - $purchaseOrderInvoice['invoice_iva']-$purchaseOrderInvoice['invoice_subtotal'])> 0.01){
              $boolInvoiceLineTotalsOk='0';
              $invoiceLineTotalsError.="La factura ".$purchaseOrderInvoice['invoice_code']." tiene un total de ".$purchaseOrderInvoice['invoice_total'].' pero la suma del subtotal '.$purchaseOrderInvoice['invoice_subtotal'].' y del IVA '.$purchaseOrderInvoice['invoice_iva'].' es '.($purchaseOrderInvoice['invoice_subtotal'] + $purchaseOrderInvoice['invoice_iva']);
            }
            
            $invoiceTotalBasedOnInvoices+=$purchaseOrderInvoice['invoice_subtotal'];
          }
        }
      }
      
      $productTotalSumBasedOnProductTotals=0;  
      foreach ($this->request->data['Product'] as $product){
        //pr($product);
        if ($product['product_quantity'] > 0 && $product['product_id'] > 0){
          $requestProducts[]['Product']=$product;
          $productTotalSumBasedOnProductTotals+=$product['product_price'];
        }
      }
      
			$purchaseDate=$this->request->data['Order']['order_date'];
			$purchaseDateAsString=$this->Order->deconstruct('order_date',$this->request->data['Order']['order_date']);
			
      if(empty($this->request->data['refresh'] )){
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDate=new DateTime($latestClosingDate);
        
        $previousPurchasesWithThisCode=[];
        $previousPurchasesWithThisCode=$this->Order->find('all',[
          'conditions'=>[
            'Order.order_code'=>$this->request->data['Order']['order_code'],
            'Order.stock_movement_type_id'=>[MOVEMENT_PURCHASE,MOVEMENT_PURCHASE_CONSUMIBLES],
            'Order.third_party_id'=>$this->request->data['Order']['third_party_id'],
          ],
        ]);
        
        $purchaseOrderWarehouseId=$this->PurchaseOrder->getPurchaseOrderWarehouseId($this->request->data['Order']['purchase_order_id']);
			
        if ($purchaseDateAsString>date('Y-m-d H:i')){
          $this->Session->setFlash(__('La fecha de entrada no puede estar en el futuro!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        elseif ($purchaseDateAsString<$latestClosingDatePlusOne){
          $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',['class' => 'error-message']);
        }
        elseif (count($previousPurchasesWithThisCode)>0){
          $this->Session->setFlash(__('Ya se introdujo una entrada con este código!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        elseif ($warehouseId != $purchaseOrderWarehouseId){
          $this->Session->setFlash(__('La bodega de la entrada y de la orden de compra deben ser iguales!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        elseif (!$boolInvoiceNamesOk){
          $this->Session->setFlash($invoiceNameError.'  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolInvoiceIvaOk){
          $this->Session->setFlash($invoiceIvaError.'  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolInvoiceLineTotalsOk){
          $this->Session->setFlash($invoiceLineTotalsError.'  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (abs($invoiceTotalBasedOnInvoices-$this->request->data['Order']['total_price']) > 1){
          $this->Session->setFlash('Si se suman los subtotales de cada factura se llega a '.$invoiceTotalBasedOnInvoices.' pero el total calculado es '.$this->request->data['Order']['total_price'].'.  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (abs($this->request->data['Order']['entry_cost_total'] - $this->request->data['Order']['entry_cost_iva'] - $this->request->data['Order']['total_price']) > 0.01){
          $this->Session->setFlash('El total para la entrada incluyendo IVA es '.$this->request->data['Order']['entry_cost_total'].' pero la suma del subtotal '.$this->request->data['Order']['total_price'].' y el IVA '.$this->request->data['Order']['entry_cost_iva'].' es '.($this->request->data['Order']['total_price']+$this->request->data['Order']['entry_cost_iva']).'.  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (abs($productTotalSumBasedOnProductTotals-$this->request->data['Order']['subtotal_based_on_products']) > 1){
          $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['Order']['total_price'].'.  Verifique que ha indicado cada producto para que se registró un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (abs($this->request->data['Order']['subtotal_based_on_products'] - $this->request->data['Order']['total_price']) > 1){
          $this->Session->setFlash('El subtotal basado en productos es '.$this->request->data['Order']['subtotal_based_on_products'].' pero el subtotal de las facturas es '.$this->request->data['Order']['total_price'].'.  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['Order']['third_party_id'] )){
          $this->Session->setFlash(__('Se debe especificar un proveedor para la entrada!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['Order']['purchase_order_id']) && $this->request->data['Order']['third_party_id'] != 102){
          $this->Session->setFlash(__('Se debe especificar una orden de compra para la entrada!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        //elseif (abs($this->request->data['Order']['total_price']-$this->request->data['PurchaseOrder']['total_price']) > 10){
        //  $this->Session->setFlash('El subtotal de las facturas es '.$this->request->data['Order']['total_price'].' pero el monto autorizado para la orden de compra es '.$this->request->data['PurchaseOrder']['total_price'].'.  No se guardó la entrada.', 'default',['class' => 'error-message']);
        //}
        else {
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          try {
            $this->Order->create();
            $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_PURCHASE;
            if (!$this->Order->save($this->request->data)) {
              echo "problema guardando la entrada";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            }
            $purchaseId=$this->Order->id;
            $orderCode=$this->request->data['Order']['order_code'];
            $providerId=$this->request->data['Order']['third_party_id'];
            $this->ThirdParty->recursive=-1;
            $linkedProvider=$this->ThirdParty->getProviderById($providerId);
            $providerName=$linkedProvider['ThirdParty']['company_name'];
            
            $this->PurchaseOrder->id=$this->request->data['Order']['purchase_order_id'];
            $purchaseOrderArray=[
              'PurchaseOrder'=>[
                'id'=>$this->request->data['Order']['purchase_order_id'],
                //'purchase_order_state_id'=>($this->request->data['Order']['bool_purchase_order_delivery_complete']?PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY:PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY),
                'purchase_order_state_id'=>PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY,
              ],
            ];
            //pr($purchaseOrderArray);
            if (!$this->PurchaseOrder->save($purchaseOrderArray)) {
              echo "problema cambiando el estado de la orden de compra";
              pr($this->validateErrors($this->PurchaseOrder));
              throw new Exception();
            }
                        
            foreach ($this->request->data['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
              if (!empty($purchaseOrderInvoice['invoice_code']) && $purchaseOrderInvoice['invoice_subtotal']  > 0){
                $this->PurchaseOrderInvoice->create();
                
                $purchaseOrderInvoice['purchase_order_id']=$this->request->data['Order']['purchase_order_id'];
                $purchaseOrderInvoice['entry_id']=$purchaseId;
                
                if (!$this->PurchaseOrderInvoice->save($purchaseOrderInvoice)) {
                  echo "problema guardando la factura".$purchaseOrderInvoice['invoice_code'];
                  pr($this->validateErrors($this->PurchaseOrderInvoice));
                  throw new Exception();
                }
              }  
            }      
                        
            foreach ($this->request->data['Product'] as $product){
              // four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
              
              // load the product request data into variables
              $productId = $product['product_id'];
              $unitId = $product['unit_id'];
              $productQuantity = $product['product_quantity'];
              $productPrice = $product['product_price'];
              
              if ($productQuantity>0 && $productId>0){
                // calculate the unit price
                $productUnitPrice=$productPrice/$productQuantity;
                
                // get the related product data
                //$linkedProduct=$this->Product->read(null,$productId);
                $this->Product->recursive=-1;
                $linkedProduct=$this->Product->find('first',[
                  'conditions'=>[
                    'Product.id'=>$productId,
                  ],
                ]);
                $productName=$linkedProduct['Product']['name'];
                $itemmovementname=$purchaseDate['day']."_".$purchaseDate['month']."_".$purchaseDate['year']."_".$providerName."_".$orderCode."_".$productName;
                $description="New stockitem ".$productName." (Quantity:".$productQuantity.",Unit Price:".$productUnitPrice.") from Purchase ".$providerName."_".$orderCode;
                
                // STEP 1: SAVE THE STOCK ITEM
                $this->loadModel('StockItem');
                $stockItemData=[
                  'name'=>$itemmovementname,
                  'description'=>$description,
                  'stockitem_creation_date'=>$purchaseDate,
                  'product_id'=>$productId,
                  'unit_id'=>$unitId,
                  'product_unit_price'=>$productUnitPrice,
                  'original_quantity'=>$productQuantity,
                  'remaining_quantity'=>$productQuantity,
                  'warehouse_id'=>$warehouseId,
                ];  
                
                $this->StockItem->create();
                if (!$this->StockItem->save($stockItemData)) {
                  echo "problema guardando el lote";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
                
                // STEP 2: SAVE THE STOCK MOVEMENT
                $stockItemId=$this->StockItem->id;
                
                $stockMovementData=[
                  'movement_date'=>$purchaseDate,
                  'bool_input'=>true,
                  'name'=>$itemmovementname,
                  'description'=>$description,
                  'order_id'=>$purchaseId,
                  'stockitem_id'=>$stockItemId,
                  'product_id'=>$productId,
                  'product_quantity'=>$productQuantity,
                  'unit_id'=>$unitId,
                  'product_unit_price'=>$productUnitPrice,
                  'product_total_price'=>$productPrice,
                ];
                $this->StockMovement->create();
                if (!$this->StockMovement->save($stockMovementData)) {
                  echo "problema guardando el movimiento de inventario";
                  pr($this->validateErrors($this->StockMovement));
                  throw new Exception();
                }
                
                // STEP 3: SAVE THE STOCK ITEM LOG
                $this->loadModel('StockItemLog');
                $stockMovementId=$this->Order->StockMovement->id;
                
                $stockItemLogData=[
                  'stockitem_id'=>$stockItemId,
                  'stock_movement_id'=>$stockMovementId,
                  'stockitem_date'=>$purchaseDate,
                  'product_id'=>$productId,
                  'unit_id'=>$unitId,
                  'product_unit_price'=>$productUnitPrice,
                  'product_quantity'=>$productQuantity,
                  'warehouse_id'=>$warehouseId,
                ];
                $this->StockItemLog->create();
                if (!$this->StockItemLog->save($stockItemLogData)) {
                  echo "problema guardando el estado de lote";
                  pr($this->validateErrors($this->StockItemLog));
                  throw new Exception();
                }
                
                // STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                $this->recordUserActivity($this->Session->read('User.username'),$description);
              }
            }
            
            $datasource->commit();
            $this->recordUserAction($this->Order->id,"crearEntrada",null);
            // SAVE THE USERLOG FOR THE PURCHASE
            $this->recordUserActivity($this->Session->read('User.username'),"Se registró la entrada número ".$this->request->data['Order']['order_code']);
            $this->Session->setFlash(__('The purchase has been saved.'),'default',['class' => 'success']);
            return $this->redirect(['action' => 'resumenEntradas']);
          } 
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('The purchase could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
          }
        }
      }  
    }
    $this->set(compact('requestInvoices'));
    $this->set(compact('requestProducts'));
    
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
		$thirdParties = $this->ThirdParty->getActiveProviderList($plantId);
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
    
    $productTypes=$this->PlantProductType->getProductTypesForPlant($plantId);
		$productsAll = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => [
				//'ProductType.product_category_id !='=> CATEGORY_PRODUCED,
        'Product.product_type_id !='=> PRODUCT_TYPE_SERVICE,
        'Product.product_type_id'=> array_keys($productTypes),
        'Product.bool_active'=> true
			],
      'recursive'=>0,
      'order'=>'Product.name'
		]);
		$products = null;
		foreach ($productsAll as $product){
			$products[$product['Product']['id']]=$product['Product']['name'];
		}
		$this->set(compact('thirdParties', 'stockMovementTypes','products'));
    
    $purchaseOrders=$this->PurchaseOrder->getConfirmedPurchaseOrders($warehouseId);
		$this->set(compact('purchaseOrders'));
    
    $units=$this->Unit->getUnitList();
    $this->set(compact('units'));
    
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		
    $aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
	
	public function manipularVenta(){
		$requestData=$_SESSION['saleRequestData'];
		
		$this->LoadModel('Product');
		$this->LoadModel('ProductionResultCode');
		$this->LoadModel('StockItem');
		$this->LoadModel('StockItemLog');
    
    $this->loadModel('Currency');
    $this->loadModel('PlantProductType');
    $this->loadModel('AccountingCode');
    
    $this->loadModel('ThirdParty');
		
		$this->Product->recursive=-1;
		$this->ProductionResultCode->recursive=-1;
		$this->StockItem->recursive=-1;
		$this->StockItemLog->recursive=-1;
		
		$this->loadModel('ProductType');
		$this->loadModel('StockMovement');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ClosingDate');
		$this->loadModel('Invoice');
		
		$this->loadModel('Currency');
		
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterInvoice');
    
    $this->loadModel('Warehouse');
    
    $this->loadModel('Constant');
    $manipulationMaxValueConstant=$this->Constant->find('first',[
      'conditions'=>[
        'Constant.constant'=>'MAXIMO_RECLASIFICACION_VENTA'
      ]
    ]);
    if (!defined('MANIPULATION_MAX_VENTA')){
      define('MANIPULATION_MAX_VENTA',$manipulationMaxValueConstant['Constant']['value']);
    }
	
    //pr($requestData);
    
    $warehouseId=$requestData['Order']['warehouse_id'];
		$orderDateAsString=$this->Order->deconstruct('order_date',$requestData['Order']['order_date']);
		
		$productionResultCodes=$this->ProductionResultCode->find('list');
		$this->set(compact('productionResultCodes'));
		
		$requestedProducts=array();
		
		$productSummary="";
		$boolReclassificationPossible=true;
		$reasonForNoReclassificationPossible="";
    $reclassificationComment="";
		if (!empty($requestData['Product'])){
			foreach ($requestData['Product'] as $product){
				if (!empty($product['product_quantity'])){
					//pr($product);
					$relatedProduct=$this->Product->find('first',[
						'conditions'=>['Product.id'=>$product['product_id'],],
						'contain'=>[
							'ProductType',
						],
					]);
					//pr($relatedProduct);
					$requestedProductInfo=array();
					$requestedProductInfo['requested_quantity']=$product['product_quantity'];
					$requestedProductInfo['Product']=$relatedProduct['Product'];
					$requestedProductInfo['ProductType']=$relatedProduct['ProductType'];
					$requestedProductInfo['ProductRequest']=$product;
					
					switch ($relatedProduct['ProductType']['product_category_id']){
						case CATEGORY_PRODUCED:
							$relatedRawMaterial=$this->Product->find('first',array(
								'conditions'=>array(
									'Product.id'=>$product['raw_material_id'],
								),							
							));	
							$requestedProductInfo['RawMaterial']=$relatedRawMaterial['Product'];
							// ONLY BOTTLES OF TYPE A CAN BE RECLASSIFIED TO
							if ($relatedProduct['ProductType']['id']==PRODUCT_TYPE_BOTTLE && $product['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
								$quantityBottleQualityAInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($product['product_id'],$product['raw_material_id'],PRODUCTION_RESULT_CODE_A,$orderDateAsString,$warehouseId,true);
								$quantityBottleQualityBInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($product['product_id'],$product['raw_material_id'],PRODUCTION_RESULT_CODE_B,$orderDateAsString,$warehouseId,true);
								$quantityBottleQualityCInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($product['product_id'],$product['raw_material_id'],PRODUCTION_RESULT_CODE_C,$orderDateAsString,$warehouseId,true);
								//echo "quantity bottle A is ".$quantityBottleQualityAInStock."<br/>";
								if ($product['product_quantity']>$quantityBottleQualityAInStock){
									$productSummary.="Para producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad A la cantidad requerida ".$product['product_quantity']." NO está disponible en bodega en esta fecha.<br/>";	
									// INVESTIGATE IF IT CAN BE DONE 
									//echo $productSummary;
									
									if ($product['product_quantity']>($quantityBottleQualityAInStock+MANIPULATION_MAX_VENTA)){
										$boolReclassificationPossible='0';
										$reasonForNoReclassificationPossible="Se requieren más que ".MANIPULATION_MAX_VENTA." unidades adicionales de  ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad A para realizar la venta, no se permite reclasificación automática.<br/>";
									}
									elseif ($product['product_quantity']>$quantityBottleQualityAInStock+$quantityBottleQualityBInStock+$quantityBottleQualityCInStock){
										$boolReclassificationPossible='0';
										$reasonForNoReclassificationPossible="No hay suficiente productos en bodega para realizar la venta de ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad A, incluso reclasificando los productos de calidad B y C.<br/>";	
									}
									else {
										if ($product['product_quantity']<=$quantityBottleQualityAInStock+$quantityBottleQualityCInStock){
											$requestedProductInfo['reclassification_B']=0;
											$requestedProductInfo['reclassification_C']=$product['product_quantity']-$quantityBottleQualityAInStock;								
											$productSummary.="Se puede vender la cantidad requerida del producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad A si se convierte una cantidad ".$requestedProductInfo['reclassification_C']." de calidad C<br/>";
                      $comment="Venta ".$requestData['Order']['order_code']." registrada de producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad A con reclasificación de cantidad ".$requestedProductInfo['reclassification_C']." de calidad C";
                      $requestedProductInfo['reclassification_comment']=$comment;
                      if (!empty($reclassificationComment)){
                        $reclassificationComment.="\r\n";
                      }
                      $reclassificationComment.=$comment;
											
										}
										elseif ($product['product_quantity']<=$quantityBottleQualityAInStock+$quantityBottleQualityBInStock+$quantityBottleQualityCInStock){
											$requestedProductInfo['reclassification_B']=$product['product_quantity']-$quantityBottleQualityAInStock-$quantityBottleQualityCInStock;
											$requestedProductInfo['reclassification_C']=$quantityBottleQualityCInStock;
											$productSummary.="Se puede vender la cantidad requerida del producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad A si se convierte una cantidad ".$requestedProductInfo['reclassification_B']." de calidad B y una cantidad ".$requestedProductInfo['reclassification_C']." de calidad C<br/>";
                      $comment="Venta ".$requestData['Order']['order_code']." registrada de producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad A con reclasificación de cantidad ".$requestedProductInfo['reclassification_B']." de calidad B y una cantidad ".$requestedProductInfo['reclassification_C']." de calidad C";
                      $requestedProductInfo['reclassification_comment']=$comment;
                      if (!empty($reclassificationComment)){
                        $reclassificationComment.="\r\n";
                      }
                      $reclassificationComment.=$comment;
										}
					
									}
								}
								else {
									$requestedProductInfo['reclassification_B']=0;
									$requestedProductInfo['reclassification_C']=0;								
								
									$productSummary.="Para producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad A la cantidad requerida ".$product['product_quantity']." está disponible en bodega en esta fecha.<br/>";
								}
								$requestedProductInfo['quality_A']=$quantityBottleQualityAInStock;
								$requestedProductInfo['quality_B']=$quantityBottleQualityBInStock;
								$requestedProductInfo['quality_C']=$quantityBottleQualityCInStock;
							}
							break;
						case CATEGORY_RAW:
						case CATEGORY_OTHER:
              if($relatedProduct['Product']['product_type_id']!= PRODUCT_TYPE_SERVICE){
                // 20170425 ALTHOUGH TAPONES ARE NOT RECLASSIFIED AT THIS TIME, WE DO SHOW HOW MANY THERE ARE IN STOCK
                $quantity=$this->StockItemLog->getStockQuantityAtDateForProduct($product['product_id'],$orderDateAsString,0,true);
                if ($quantity<$product['product_quantity']){
                  $productSummary.="Para producto ".$relatedProduct['Product']['name']." la cantidad requerida ".$product['product_quantity']." NO está disponible en bodega en esta fecha.<br/>";	
                  $boolReclassificationPossible='0';
                  $reasonForNoReclassificationPossible="Como para el producto ".$relatedProduct['Product']['name']." la cantidad requerida no es en bodega, no se puede realizar la venta; no se pueden reclasificar tapones.<br/>";
                }
                else {
                  $productSummary.="Para producto ".$relatedProduct['Product']['name']." la cantidad requerida ".$product['product_quantity']." está disponible en bodega en esta fecha.<br/>";	
                }
                $requestedProductInfo['quantity']=$quantity;
              }
              else {
                $requestedProductInfo['quantity']=$product['product_quantity'];
              }
              break;
						default:
							break;
					}
					$requestedProducts[]=$requestedProductInfo;
				}
			}
		}
		//pr($requestedProducts);
		$this->set(compact('requestedProducts'));
    $this->set(compact('reclassificationComment'));
		$this->set(compact('productSummary','boolReclassificationPossible','reasonForNoReclassificationPossible'));
		
    if (!empty($requestData['Order']['comment'])){
      $requestData['Order']['comment']=$requestData['Order']['comment']."\r\n".$reclassificationComment;
    }
    else {
      $requestData['Order']['comment']=$reclassificationComment;
    }
    
		if ($this->request->is('post')) {	
			//pr($this->request->data);
			$reclassificationDateString=$orderDateAsString;
			$reclassificationDatePlusOne=date("Y-m-d",strtotime($reclassificationDateString."+1 days"));
					
			$allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
			$allBottles=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE)));
			
      $boolReclassificationSuccess=true;
      
			foreach ($this->request->data['ReclassificationProduct'] as $reclassificationProduct){
				// PRIMERA LA RECLASIFICACIÓN DE C a A
				$lastReclassification=$this->StockMovement->find('first',array(
					'fields'=>array('StockMovement.reclassification_code'),
					'conditions'=>array(
						'bool_reclassification'=>true,
					),
					'order'=>array('StockMovement.reclassification_code' => 'desc'),
				));
				$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1;
				$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
				
				if ($reclassificationProduct['reclassification_C']>0){
					$bottle_id=$reclassificationProduct['product_id'];
					$preforma_id=$reclassificationProduct['raw_material_id'];
					$original_production_result_code_id=PRODUCTION_RESULT_CODE_C;
					$target_production_result_code_id=PRODUCTION_RESULT_CODE_A;
					$quantity_bottles=$reclassificationProduct['reclassification_C'];
          $movement_comment=$reclassificationProduct['reclassification_comment'];
					if ($quantity_bottles>0 && $bottle_id>0 && $preforma_id>0){
						$quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($bottle_id,$preforma_id,PRODUCTION_RESULT_CODE_C,$orderDateAsString,0,true);
						if ($quantityInStock<$quantity_bottles){
							$this->Session->setFlash('bottle_id is '.$bottle_id.' y preforma_id is '.$preforma_id.' y orderdateasstring is '.$orderDateAsString.'. En bodega hay '.$quantityInStock.' y la cantidad necesitada es '.$quantity_bottles.'. Intento de reclasificar '.$quantity_bottles." ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." (".$productionResultCodes[$original_production_result_code_id].") pero en bodega únicamente hay ".$quantityInStock, 'default',['class' => 'error-message']);
						}
						else {
							// reclassify!
							$currentdate= new DateTime();
							$usedBottleStockItems=$this->StockItem->getFinishedMaterialsForSale($bottle_id,$original_production_result_code_id,$quantity_bottles,$preforma_id,$reclassificationDatePlusOne,0,"DESC");
							$quantityAvailableForReclassification=0;
							if (count($usedBottleStockItems)){
								foreach ($usedBottleStockItems as $usedBottleStockItem){
									$quantityAvailableForReclassification+=$usedBottleStockItem['quantity_present'];
								}
							}
							if ($quantity_bottles>$quantityAvailableForReclassification){
								$this->Session->setFlash('Los lotes presentes en el momento de reclasificación ya salieron de bodega', 'default',['class' => 'error-message']);
							}
							else {
								$newlyCreatedStockItems=array();
								
								//pr($usedBottleStockItems);
								$datasource=$this->StockItem->getDataSource();
								$datasource->begin();
								try{
									foreach ($usedBottleStockItems as $usedBottleStockItem){
										$stockItemId=$usedBottleStockItem['id'];
										$quantity_present=$usedBottleStockItem['quantity_present'];
										$quantity_used=$usedBottleStockItem['quantity_used'];
										$quantity_remaining=$usedBottleStockItem['quantity_remaining'];
										$unit_price=$usedBottleStockItem['unit_price'];
										if (!$this->StockItem->exists($stockItemId)) {
											throw new NotFoundException(__('Invalid StockItem'));
										}
										//$linkedStockItem=$this->StockItem->read(null,$stockItemId);
										$this->StockItem->recursive=-1;
										$linkedStockItem=$this->StockItem->find('first',array(
											'conditios'=>array(
												'StockItem.id'=>$stockItemId,
											),
										));
										$message="Reclassified ".$quantity_used." of ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." from ".$productionResultCodes[$original_production_result_code_id]." to ".$productionResultCodes[$target_production_result_code_id]." on ".date("d")."-".date("m")."-".date("Y");
										
										// STEP 1: EDIT THE STOCKITEM OF ORIGIN
										$stockItemData=array();
										$stockItemData['id']=$stockItemId;
										$stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
										$stockItemData['remaining_quantity']=$quantity_remaining;
										
										if (!$this->StockItem->save($stockItemData)) {
											echo "problema al editor el lote de origen";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
										$StockMovementData=array();
										$StockMovementData['movement_date']=$orderDateAsString;
										$StockMovementData['bool_input']='0';
										$StockMovementData['name']=$message;
										$StockMovementData['description']=$message;
										$StockMovementData['order_id']=0;
										$StockMovementData['stockitem_id']=$stockItemId;
										$StockMovementData['product_id']=$bottle_id;
										$StockMovementData['product_quantity']=$quantity_used;
										$StockMovementData['product_unit_price']=$unit_price;
										$StockMovementData['product_total_price']=$unit_price*$quantity_used;
										$StockMovementData['production_result_code_id']=$original_production_result_code_id;
										$StockMovementData['bool_reclassification']=true;
										$StockMovementData['reclassification_code']=$reclassificationCode;
                    $StockMovementData['comment']=$movement_comment;
										
										$this->StockMovement->create();
										if (!$this->StockMovement->save($StockMovementData)) {
											echo "problema al guardar el movimiento de lote";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}
										
										// STEP 3: SAVE THE TARGET STOCKITEM
										$stockItemData=array();
										$stockItemData['name']=$message;
										$stockItemData['description']=$message;
										$stockItemData['stockitem_creation_date']=$orderDateAsString;
										$stockItemData['product_id']=$bottle_id;
										$stockItemData['product_unit_price']=$unit_price;
										$stockItemData['original_quantity']=$quantity_used;
										$stockItemData['remaining_quantity']=$quantity_used;
										$stockItemData['production_result_code_id']=$target_production_result_code_id;
										$stockItemData['raw_material_id']=$preforma_id;
										
										$this->StockItem->create();
										// notice that no new stockitem is created because we are taking from an already existing one
										
										if (!$this->StockItem->save($stockItemData)) {
											echo "problema al guardar el lote de destino";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
										$new_stockitem_id=$this->StockItem->id;
										$newlyCreatedStockItems[]=$new_stockitem_id;
										
										$origin_stock_movement_id=$this->StockMovement->id;
										
										$StockMovementData=array();
										$StockMovementData['movement_date']=$orderDateAsString;
										$StockMovementData['bool_input']=true;
										$StockMovementData['name']=$message;
										$StockMovementData['description']=$message;
										$StockMovementData['order_id']=0;
										$StockMovementData['stockitem_id']=$new_stockitem_id;
										$StockMovementData['product_id']=$bottle_id;
										$StockMovementData['product_quantity']=$quantity_used;
										$StockMovementData['product_unit_price']=$unit_price;
										$StockMovementData['product_total_price']=$unit_price*$quantity_used;
										$StockMovementData['production_result_code_id']=$target_production_result_code_id;
										$StockMovementData['bool_reclassification']=true;
										$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
										$StockMovementData['reclassification_code']=$reclassificationCode;
                    $StockMovementData['comment']=$movement_comment;
										
										$this->StockMovement->create();
										if (!$this->StockMovement->save($StockMovementData)) {
											echo "problema al guardar el movimiento de lote";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}
												
										// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
										$this->recordUserActivity($this->Session->read('User.username'),$message);
									}
									
									$datasource->commit();
									
									foreach ($usedBottleStockItems as $usedBottleStockItem){
										$this->recreateStockItemLogs($usedBottleStockItem['id']);
									}
									for ($i=0;$i<count($newlyCreatedStockItems);$i++){
										$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
									}
									
									$reclassificationNumber=substr($reclassificationCode,6,6)+1;
									$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
									
									$this->Session->setFlash(__('Reclasificación exitosa'),'default',['class' => 'success']);
								}
								catch(Exception $e){
									$datasource->rollback();
									pr($e);
									$this->Session->setFlash(__('Reclasificación falló'), 'default',['class' => 'error-message'], 'default',['class' => 'error-message']);
                  $boolReclassificationSuccess='0';
								}
							}
						}
					}
					else {
						$warning="";
						if($quantity_bottles==0){
							$warning.="Cantidad de botellas debe estar positiva!<br/>";
						}
						if($bottle_id==0){
							$warning.="No botella seleccionada!<br/>";
						}
						if($preforma_id==0){
							$warning.="No preforma seleccionado!<br/>";
						}
						$this->Session->setFlash($warning, 'default',['class' => 'error-message']);
					}			
				}
				
				if ($reclassificationProduct['reclassification_B']>0){
					$bottle_id=$reclassificationProduct['product_id'];
					$preforma_id=$reclassificationProduct['raw_material_id'];
					$original_production_result_code_id=PRODUCTION_RESULT_CODE_B;
					$target_production_result_code_id=PRODUCTION_RESULT_CODE_A;
					$quantity_bottles=$reclassificationProduct['reclassification_B'];
          $movement_comment=$reclassificationProduct['reclassification_comment'];
					if ($quantity_bottles>0 && $bottle_id>0 && $preforma_id>0 && $original_production_result_code_id>0 && $target_production_result_code_id>0){
						$quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($bottle_id,$preforma_id,PRODUCTION_RESULT_CODE_B,$reclassificationDateString,0,true);
						if ($quantityInStock<$quantity_bottles){
							$this->Session->setFlash('Intento de reclasificar '.$quantity_bottles." ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." (".$productionResultCodes[$original_production_result_code_id].") pero en bodega únicamente hay ".$quantityInStock, 'default',['class' => 'error-message']);
						}
						else {
							// reclassify!
							$currentdate= new DateTime();
							$usedBottleStockItems=$this->StockItem->getFinishedMaterialsForSale($bottle_id,$original_production_result_code_id,$quantity_bottles,$preforma_id,$reclassificationDatePlusOne,0,"DESC");
							$quantityAvailableForReclassification=0;
							if (count($usedBottleStockItems)){
								foreach ($usedBottleStockItems as $usedBottleStockItem){
									$quantityAvailableForReclassification+=$usedBottleStockItem['quantity_present'];
								}
							}
							if ($quantity_bottles>$quantityAvailableForReclassification){
								$this->Session->setFlash('Los lotes presentes en el momento de reclasificación ya salieron de bodega', 'default',['class' => 'error-message']);
							}
							else {
								$newlyCreatedStockItems=array();
								
								//pr($usedBottleStockItems);
								$datasource=$this->StockItem->getDataSource();
								$datasource->begin();
								try{
									foreach ($usedBottleStockItems as $usedBottleStockItem){
										$stockItemId=$usedBottleStockItem['id'];
										$quantity_present=$usedBottleStockItem['quantity_present'];
										$quantity_used=$usedBottleStockItem['quantity_used'];
										$quantity_remaining=$usedBottleStockItem['quantity_remaining'];
										$unit_price=$usedBottleStockItem['unit_price'];
										if (!$this->StockItem->exists($stockItemId)) {
											throw new NotFoundException(__('Invalid StockItem'));
										}
										//$linkedStockItem=$this->StockItem->read(null,$stockItemId);
										$this->StockItem->recursive=-1;
										$linkedStockItem=$this->StockItem->find('first',array(
											'conditions'=>array(
												'StockItem.id'=>$stockItemId,
											),
										));
										$message="Reclassified ".$quantity_used." of ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." from ".$productionResultCodes[$original_production_result_code_id]." to ".$productionResultCodes[$target_production_result_code_id]." on ".date("d")."-".date("m")."-".date("Y");
										
										// STEP 1: EDIT THE STOCKITEM OF ORIGIN
										$stockItemData=array();
										$stockItemData['id']=$stockItemId;
										$stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
										$stockItemData['remaining_quantity']=$quantity_remaining;
										
										if (!$this->StockItem->save($stockItemData)) {
											echo "problema al editor el lote de origen";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
										$StockMovementData=array();
										$StockMovementData['movement_date']=$orderDateAsString;
										$StockMovementData['bool_input']='0';
										$StockMovementData['name']=$message;
										$StockMovementData['description']=$message;
										$StockMovementData['order_id']=0;
										$StockMovementData['stockitem_id']=$stockItemId;
										$StockMovementData['product_id']=$bottle_id;
										$StockMovementData['product_quantity']=$quantity_used;
										$StockMovementData['product_unit_price']=$unit_price;
										$StockMovementData['product_total_price']=$unit_price*$quantity_used;
										$StockMovementData['production_result_code_id']=$original_production_result_code_id;
										$StockMovementData['bool_reclassification']=true;
										$StockMovementData['reclassification_code']=$reclassificationCode;
                    $StockMovementData['comment']=$movement_comment;
										
										$this->StockMovement->create();
										if (!$this->StockMovement->save($StockMovementData)) {
											echo "problema al guardar el movimiento de lote";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}
										
										// STEP 3: SAVE THE TARGET STOCKITEM
										$stockItemData=array();
										$stockItemData['name']=$message;
										$stockItemData['description']=$message;
										$stockItemData['stockitem_creation_date']=$orderDateAsString;
										$stockItemData['product_id']=$bottle_id;
										$stockItemData['product_unit_price']=$unit_price;
										$stockItemData['original_quantity']=$quantity_used;
										$stockItemData['remaining_quantity']=$quantity_used;
										$stockItemData['production_result_code_id']=$target_production_result_code_id;
										$stockItemData['raw_material_id']=$preforma_id;
										
										$this->StockItem->create();
										// notice that no new stockitem is created because we are taking from an already existing one
										
										if (!$this->StockItem->save($stockItemData)) {
											echo "problema al guardar el lote de destino";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
										$new_stockitem_id=$this->StockItem->id;
										$newlyCreatedStockItems[]=$new_stockitem_id;
										
										$origin_stock_movement_id=$this->StockMovement->id;
										
										$StockMovementData=array();
										$StockMovementData['movement_date']=$orderDateAsString;
										$StockMovementData['bool_input']=true;
										$StockMovementData['name']=$message;
										$StockMovementData['description']=$message;
										$StockMovementData['order_id']=0;
										$StockMovementData['stockitem_id']=$new_stockitem_id;
										$StockMovementData['product_id']=$bottle_id;
										$StockMovementData['product_quantity']=$quantity_used;
										$StockMovementData['product_unit_price']=$unit_price;
										$StockMovementData['product_total_price']=$unit_price*$quantity_used;
										$StockMovementData['production_result_code_id']=$target_production_result_code_id;
										$StockMovementData['bool_reclassification']=true;
										$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
										$StockMovementData['reclassification_code']=$reclassificationCode;
                    $StockMovementData['comment']=$movement_comment;
										
										$this->StockMovement->create();
										if (!$this->StockMovement->save($StockMovementData)) {
											echo "problema al guardar el movimiento de lote";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}
												
										// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
										$this->recordUserActivity($this->Session->read('User.username'),$message);
									}
									
									$datasource->commit();
									
									foreach ($usedBottleStockItems as $usedBottleStockItem){
										$this->recreateStockItemLogs($usedBottleStockItem['id']);
									}
									for ($i=0;$i<count($newlyCreatedStockItems);$i++){
										$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
									}
									
									$reclassificationNumber=substr($reclassificationCode,6,6)+1;
									$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
									
									$this->Session->setFlash(__('Reclasificación exitosa'),'default',['class' => 'success']);
								}
								catch(Exception $e){
									$datasource->rollback();
									pr($e);
									$this->Session->setFlash(__('Reclasificación falló'), 'default',['class' => 'error-message'], 'default',['class' => 'error-message']);
                  $boolReclassificationSuccess='0';
								}
							}
						}
					}
					else {
						$warning="";
						if($quantity_bottles==0){
							$warning.="Cantidad de botellas debe estar positiva!<br/>";
						}
						if($bottle_id==0){
							$warning.="No botella seleccionada!<br/>";
						}
						if($preforma_id==0){
							$warning.="No preforma seleccionado!<br/>";
						}
						$this->Session->setFlash($warning, 'default',['class' => 'error-message']);
					}			
				}
			}
				
      if ($boolReclassificationSuccess){     
        $warehouseId=$this->request->data['Order']['warehouse_id'];
        
        $sale_date=$this->request->data['Order']['order_date'];
        $saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDate=new DateTime($latestClosingDate);
              
        $saleDateArray=[];
        $saleDateArray['year']=$sale_date['year'];
        $saleDateArray['month']=$sale_date['month'];
        $saleDateArray['day']=$sale_date['day'];
            
        $orderCode=$this->request->data['Order']['order_code'];
        $namedSales=$this->Order->find('all',array(
          'conditions'=>array(
            'order_code'=>$orderCode,
            'stock_movement_type_id'=>MOVEMENT_SALE,
          )
        ));
        if (count($namedSales)>0){
          $this->Session->setFlash(__('Ya existe una venta con el mismo código!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        else if ($saleDateAsString>date('Y-m-d 23:59:59')){
          $this->Session->setFlash(__('La fecha de salida no puede estar en el futuro!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        elseif ($saleDateAsString<$latestClosingDatePlusOne){
          $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        elseif ($this->request->data['Order']['third_party_id']==0){
          $this->Session->setFlash(__('Se debe seleccionar el cliente para la venta!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        else if (!$this->request->data['bool_credit']&&$this->request->data['Invoice']['cashbox_accounting_code_id']==0){
          $this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una factura de contado!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        else if ($this->request->data['Invoice']['bool_retention']&&strlen($this->request->data['Invoice']['retention_number'])==0){
          $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }	
        else {
          //echo "now we check the items still<br/>";
          // before moving into the selling part, perform the check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
          $saleItemsOK=true;
          $exceedingItems="";
          $productCount=0;
          $products=array();
          foreach ($this->request->data['Product'] as $product){
            //pr($product);
            // keep track of number of rows so that in case of an error jquery displays correct number of rows again
            if ($product['product_id']>0){
              $productCount++;
            }
            // only process lines where product_quantity and product id have been filled out
            if ($product['product_quantity']>0 && $product['product_id']>0){
              $products[]=$product;
              $quantityEntered=$product['product_quantity'];
              $productid = $product['product_id'];
              $productionresultcodeid = $product['production_result_code_id'];
              $rawmaterialid = $product['raw_material_id'];
              
              $relatedProduct=$this->Product->find('first',array(
                'conditions'=>array(
                  'Product.id'=>$productid,
                ),
              ));
              if ($relatedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){  
                if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
                  $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($productid,$rawmaterialid,$productionresultcodeid,$saleDateAsString,$warehouseId,true);
                }
                else {
                  $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productid,$saleDateAsString,$warehouseId,true);
                }
                
                //compare the quantity requested and the quantity in stock
                if ($quantityEntered>$quantityInStock){
                  $saleItemsOK='0';
                  $exceedingItems.=__("Para producto ".$relatedProduct['Product']['name']." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
                }
              }
            }
          }
          if ($exceedingItems!=""){
            $exceedingItems.=__("Please correct and try again!");
          }					
          if (!$saleItemsOK){
            $this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
          }
          else{
            //echo "now we enter into the selling part after checking the items<br/>";	
            //pr($this->request->data);
            $datasource=$this->Order->getDataSource();
            $datasource->begin();
            try {
              $currency_id=$this->request->data['Invoice']['currency_id'];
            
              $retention_invoice=$this->request->data['Invoice']['retention_amount'];
              $sub_total_invoice=$this->request->data['Invoice']['sub_total_price'];
              $ivaInvoice=$this->request->data['Invoice']['iva_price'];
              $totalInvoice=$this->request->data['Invoice']['total_price'];
          
              // if all products are in stock, proceed with the sale 
              $this->Order->create();
              $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
              // ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
              if ($currency_id==CURRENCY_USD){
                $this->request->data['Order']['total_price']=$sub_total_invoice*$this->request->data['Order']['exchange_rate'];
              }
              else {
                $this->request->data['Order']['total_price']=$sub_total_invoice;
              }
            
              if (!$this->Order->save($this->request->data)) {
                echo "Problema guardando la salida";
                pr($this->validateErrors($this->Order));
                throw new Exception();
              }
            
              $order_id=$this->Order->id;
              $orderCode=$this->request->data['Order']['order_code'];
            
              $this->Invoice->create();
              $this->request->data['Invoice']['order_id']=$order_id;
              $this->request->data['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
              $this->request->data['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
              $this->request->data['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
              if ($this->request->data['Invoice']['bool_credit']){
                $this->request->data['Invoice']['bool_retention']='0';
                $this->request->data['Invoice']['retention_amount']=0;
                $this->request->data['Invoice']['retention_number']="";
              }
              else {
                $this->request->data['Invoice']['bool_paid']=true;
              }
          
              if (!$this->Invoice->save($this->request->data)) {
                echo "Problema guardando la factura";
                pr($this->validateErrors($this->Invoice));
                throw new Exception();
              }
              
              $invoice_id=$this->Invoice->id;
              
              // now prepare the accounting registers
              
              // if the invoice is with credit, save one accounting register; 
              // debit=cuentas por cobrar clientes 101-004-001, credit = ingresos por venta 401, amount = subtotal
              
              // if the invoice is paid with cash, save two or three accounting register; 
              // debit=caja selected by client, credit = ingresos por venta 401, amount = total
              // debit=?, credit = ?, amount = iva
              // if bool_retention is true
              // debit=?, credit = ?, amount = retention
              
              if ($currency_id==CURRENCY_USD){
                $this->loadModel('ExchangeRate');
                $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateAsString);
                //pr($applicableExchangeRate);
                $retention_CS=round($retention_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                $sub_total_CS=round($sub_total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                $ivaCS=round($ivaInvoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                $totalCS=round($totalInvoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
              }
              else {
                $retention_CS=$retention_invoice;
                $sub_total_CS=$sub_total_invoice;
                $ivaCS=$ivaInvoice;
                $totalCS=$totalInvoice;
              }
              $this->AccountingCode->recursive=-1;
              if ($this->request->data['Invoice']['bool_credit']){
                $client_id=$this->request->data['Order']['third_party_id'];
                $this->loadModel('ThirdParty');
                
                $this->ThirdParty->recursive=-1;
                $thisClient=$this->ThirdParty->getClientById($clientId);
              
                $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
                $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
                $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
                $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
                $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
                $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$ivaCS;
                $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
                
                if (empty($thisClient['ThirdParty']['accounting_code_id'])){
                  $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES);
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
                    ),
                  ));
                }
                else {								
                  $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
                  //$accountingCode=$this->AccountingCode->read(null,$thisClient['ThirdParty']['accounting_code_id']);
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>$thisClient['ThirdParty']['accounting_code_id'],
                    ),
                  ));
                }
                $accountingRegisterData['AccountingMovement'][0]['concept']="A cobrar Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS;
                
                $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
                //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][1]['concept']="Ingresos Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
                
                if ($this->request->data['Invoice']['bool_iva']){
                  $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                    )
                  ));
                  $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$ivaCS;
                }
                
                //pr($accountingRegisterData);
                $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
                $this->recordUserAction($this->AccountingRegister->id,"add",null);
            
                $AccountingRegisterInvoiceData=array();
                $AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
                $AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
                $this->AccountingRegisterInvoice->create();
                if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                  pr($this->validateErrors($this->AccountingRegisterInvoice));
                  echo "problema al guardar el lazo entre asiento contable y factura";
                  throw new Exception();
                }
                //echo "link accounting register sale saved<br/>";					
              }
              else {
                $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
                $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
                $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
                $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
                $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
                $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$ivaCS;
                $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
                
                if (!$this->request->data['Invoice']['bool_retention']){
                  $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                  //$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                    )
                  ));
                  $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS;
                }
                else {
                  // with retention
                  $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                  //$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                    )
                  ));
                  $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS-$retention_CS;
                }
                
                $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
                //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
                $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                    )
                  ));
                $accountingRegisterData['AccountingMovement'][1]['concept']="Subtotal Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
                
                if ($this->request->data['Invoice']['bool_iva']){
                  $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                    )
                  ));
                  $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$ivaCS;
                }
                if ($this->request->data['Invoice']['bool_retention']){
                  $accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_RETENCIONES_POR_COBRAR);
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_RETENCIONES_POR_COBRAR,
                    )
                  ));
                  $accountingRegisterData['AccountingMovement'][3]['concept']="Retención Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][3]['debit_amount']=$retention_CS;
                }
                
                //pr($accountingRegisterData);
                $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
                $this->recordUserAction($this->AccountingRegister->id,"add",null);
                //echo "accounting register saved for cuentas cobrar clientes<br/>";
            
                $AccountingRegisterInvoiceData=array();
                $AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
                $AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
                $this->AccountingRegisterInvoice->create();
                if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                  pr($this->validateErrors($this->AccountingRegisterInvoice));
                  echo "problema al guardar el lazo entre asiento contable y factura";
                  throw new Exception();
                }
                //echo "link accounting register sale saved<br/>";	
              }
            
              foreach ($products as $product){
                // four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
                //echo "keeping the products<br/>";
                //pr($product);
                
                // load the product request data into variables
                $product_id = $product['product_id'];
                $product_category_id = $this->Product->getProductCategoryId($product_id);
                $production_result_code_id =0;
                $raw_material_id=0;
                
                if ($product_category_id==CATEGORY_PRODUCED){
                  $production_result_code_id = $product['production_result_code_id'];
                  $raw_material_id = $product['raw_material_id'];
                }
                $service_unit_cost=$product['service_unit_cost'];
                $productUnitPrice=$product['product_unit_price'];
                $product_quantity = $product['product_quantity'];
                
                if ($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
                  $productUnitPrice*=$this->request->data['Order']['exchange_rate'];
                }
                
                // get the related product data
                //$linkedProduct=$this->Product->read(null,$product_id);
                $this->Product->recursive=-1;
                $linkedProduct=$this->Product->find('first',array(
                  'conditions'=>array(
                    'Product.id'=>$product_id,
                  ),
                ));
                $productName=$linkedProduct['Product']['name'];
                if ($linkedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                  // STEP 1: SAVE THE STOCK ITEM(S)
                  // first prepare the materials that will be taken out of stock
                  
                  if ($product_category_id==CATEGORY_PRODUCED){
                    $usedMaterials= $this->StockItem->getFinishedMaterialsForSale($product_id,$production_result_code_id,$product_quantity,$raw_material_id,$saleDateAsString,$warehouseId);		
                  }
                  else {
                    $usedMaterials= $this->StockItem->getOtherMaterialsForSale($product_id,$product_quantity,$saleDateAsString,$warehouseId);		
                  }
                  //echo "used materials found<br/>";
                  //pr($usedMaterials);

                  for ($k=0;$k<count($usedMaterials);$k++){
                    $materialUsed=$usedMaterials[$k];
                    $stockItemId=$materialUsed['id'];
                    $quantity_present=$materialUsed['quantity_present'];
                    $quantity_used=$materialUsed['quantity_used'];
                    $quantity_remaining=$materialUsed['quantity_remaining'];
                    if (!$this->StockItem->exists($stockItemId)) {
                      throw new NotFoundException(__('Invalid StockItem'));
                    }
                    //$linkedStockItem=$this->StockItem->read(null,$stockItemId);
                    $linkedStockItem=$this->StockItem->recursive=-1;
                    $linkedStockItem=$this->StockItem->find('first',array(
                      'conditions'=>array(
                        'StockItem.id'=>$stockItemId,
                      ),
                    ));
                    $message="Se vendió lote ".$productName." (Cantidad:".$quantity_used.") para Venta ".$orderCode;
                    
                    $stockItemData=array();
                    $stockItemData['id']=$stockItemId;
                    //$stockItemData['name']=$sale_date['day'].$sale_date['month'].$sale_date['year']."_".$orderCode."_".$productName;
                    $stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
                    $stockItemData['remaining_quantity']=$quantity_remaining;
                    // notice that no new stockitem is created because we are taking from an already existing one
                    $this->StockItem->id=$stockItemId;
                    //pr($stockItemData);
                    if (!$this->StockItem->save($stockItemData)) {
                      echo "problema al guardar el lote";
                      pr($this->validateErrors($this->StockItem));
                      throw new Exception();
                    }
                    
                    // STEP 2: SAVE THE STOCK MOVEMENT
                    $message="Se vendió ".$productName." (Cantidad:".$quantity_used.", total para venta:".$product_quantity.") para Venta ".$orderCode;
                    $stockMovementData=array();
                    $stockMovementData['movement_date']=$sale_date;
                    $stockMovementData['bool_input']='0';
                    $stockMovementData['name']=$sale_date['day'].$sale_date['month'].$sale_date['year']."_".$orderCode."_".$productName;
                    $stockMovementData['description']=$message;
                    $stockMovementData['order_id']=$order_id;
                    $stockMovementData['stockitem_id']=$stockItemId;
                    $stockMovementData['product_id']=$product_id;
                    $stockMovementData['product_quantity']=$quantity_used;
                    $stockMovementData['product_unit_price']=$productUnitPrice;
                    $stockMovementData['product_total_price']=$productUnitPrice*$quantity_used;
                    $stockMovementData['service_unit_cost']=0;
                    $stockMovementData['service_total_cost']=0;
                    $stockMovementData['production_result_code_id']=$production_result_code_id;
                    
                    $this->StockMovement->create();
                    if (!$this->StockMovement->save($stockMovementData)) {
                      echo "problema al guardar el movimiento de lote";
                      pr($this->validateErrors($this->StockMovement));
                      throw new Exception();
                    }
                  
                    // STEP 3: SAVE THE STOCK ITEM LOG
                    $this->recreateStockItemLogs($stockItemId);
                        
                    // STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                    $this->recordUserActivity($this->Session->read('User.username'),$message);
                    
                    //echo "product saved<br/>";
                  }
                }  
                else {
                  $message="Se vendió ".$productName." (Cantidad:".$product_quantity.", total para venta:".$product_quantity.") para Venta ".$orderCode;
                  $stockMovementData=[];
                  $stockMovementData['movement_date']=$sale_date;
                  $stockMovementData['bool_input']='0';
                  $stockMovementData['name']=$sale_date['day'].$sale_date['month'].$sale_date['year']."_".$orderCode."_".$productName;
                  $stockMovementData['description']=$message;
                  $stockMovementData['order_id']=$order_id;
                  $stockMovementData['stockitem_id']=0;
                  $stockMovementData['product_id']=$product_id;
                  $stockMovementData['product_quantity']=$product_quantity;
                  $stockMovementData['product_unit_price']=$productUnitPrice;
                  $stockMovementData['product_total_price']=$productUnitPrice*$product_quantity;
                  $stockMovementData['service_unit_cost']=$service_unit_cost;
                  $stockMovementData['service_total_cost']=$service_unit_cost*$product_quantity;
                  $stockMovementData['production_result_code_id']=$production_result_code_id;
                  
                  $this->StockMovement->create();
                  if (!$this->StockMovement->save($stockMovementData)) {
                    echo "problema al guardar el movimiento de lote";
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                
                  $this->recordUserActivity($this->Session->read('User.username'),$message);
                }
              }
                      
              $datasource->commit();
              $this->recordUserAction($this->Order->id,"add",null);
              // SAVE THE USERLOG FOR THE PURCHASE
              $this->recordUserActivity($this->Session->read('User.username'),"Sale registered with invoice code ".$this->request->data['Order']['order_code']);
              $this->Session->setFlash(__('Se guardó la venta.'),'default',['class' => 'success'],'default',['class' => 'success']);
              //return $this->redirect(array('action' => 'resumenVentasRemisiones'));
              return $this->redirect(array('action' => 'imprimirVenta',$order_id));
              // on the view page the print button will be present; it should display the invoice just as it has been made out, this is then sent to javascript
              //return $this->redirect(array('action' => 'verVenta',$order_id));
            }
            catch(Exception $e){
              $datasource->rollback();
              pr($e);
              $this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
            }
          }
        }
      }
		}
		
		$this->request->data=$requestData;
		//pr($requestData);
		$this->set(compact('requestData'));
		
		$warehouses=$this->Warehouse->find('list');
		$this->set(compact('warehouses'));
    
		$currencies = $this->Currency->find('list');
		$this->set(compact('currencies'));
		
		$this->AccountingCode->recursive=-1;
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
			'order'=>'AccountingCode.code',
		));
		$this->set(compact('cashboxAccountingCode'));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			),
			'order'=>'AccountingCode.code',
		));
		$this->set(compact('accountingCodes'));
		
		$rawProductTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_RAW,
			),
		));
		//$rawMaterialsAll=$this->Product->find('all', array(
		$rawMaterials=$this->Product->find('list', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=>$rawProductTypeIds,
			),
			'order'=>'Product.name',
		));
		//$rawMaterials=null;
		//foreach ($rawMaterialsAll as $rawMaterial){
		//	$rawMaterials[$rawMaterial['Product']['id']]=$rawMaterial['Product']['name'];
		//}
		$this->set(compact('rawMaterials'));
		
    $plantId=$this->Warehouse->getPlantId($warehouseId);
		$thirdParties = $this->ThirdParty->getActiveProviderList($plantId);
		$this->set(compact('thirdParties'));
    $productTypes=$this->PlantProductType->getProductTypesForPlant($plantId);
		
		$productsAll = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'contain'=>[
				'ProductType',
				'StockItem'=>[
					'fields'=> ['remaining_quantity','raw_material_id','warehouse_id'],
				]
			],
			'order'=>'product_type_id DESC, name ASC',
		]);
		$products =[];
		foreach ($productsAll as $product){
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					if ($stockitem['remaining_quantity']>0){
						if (!empty($warehouseId)){
							if ($stockitem['warehouse_id']==$warehouseId){
								$products[$product['Product']['id']]=$product['Product']['name'];
							}
						}
						else {
							$products[$product['Product']['id']]=$product['Product']['name'];
						}		
					}
				}
			}
      elseif ($product['ProductType']['id'] == PRODUCT_TYPE_SERVICE){
        $products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
      }
    }
		$this->set(compact('products'));

		$producedProductTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_PRODUCED,
			),
		));
		//$finishedProductsAll = $this->Product->find('all', array(
		$finishedProducts = $this->Product->find('list', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=> $producedProductTypeIds,
			),
			'order'=>'Product.name',
		));
		//$finishedProducts = null;
		//foreach ($finishedProductsAll as $finishedProduct){
		//	$finishedProducts[$finishedProduct['Product']['id']]=$finishedProduct['Product']['name'];
		//}
		
		$otherProductTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_OTHER,
			),
		));
		//$otherMaterialsAll=$this->Product->find('all', array(
		$otherMaterials=$this->Product->find('all', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=>$otherProductTypeIds,
			),
			'order'=>'Product.name',
		));
		
		$this->set(compact('finishedProducts','otherMaterials'));
    
    $otherProducts=$this->Product->find('list',[
      'fields'=>'Product.id',
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_SERVICE]
    ]);
    $otherProducts=array_values($otherProducts);
    $this->set(compact('otherProducts'));
	}

  public function crearVentaOld($salesOrderId=0) {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
    $this->loadModel('ProductionResultCode');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		$this->loadModel('Invoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterInvoice');
		$this->loadModel('ThirdParty');
    $this->loadModel('ExchangeRate');
    
    $this->loadModel('SalesOrder');
    
    $this->loadModel('ClientType');
		$this->loadModel('Zone');
		
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
     
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $roleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId','roleId'));
    
		//$this->loadModel('Invoice');
    
		$this->Order->recursive=-1;
		$this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		$this->Order->ThirdParty->recursive=-1;
		$this->Order->StockMovementType->recursive=-1;
		$this->AccountingCode->recursive=-1;
		$this->Invoice->recursive=-1;
    
		$warehouseId=0;
		
		$inventoryDisplayOptions=[
			'0'=>'No mostrar inventario',
			'1'=>'Mostrar inventario',
		];
		$this->set(compact('inventoryDisplayOptions'));
		$inventoryDisplayOptionId=0;
    
    $adminUsers=$this->User->getActiveUsersForRole(ROLE_ADMIN);
    $adminUserIds=array_keys($adminUsers);
    $this->set(compact('adminUserIds'));
    //pr($adminUserIds);
		
    $boolInitialLoad=true;
		$requestProducts=[];
    
    if (!empty($this->request->data)){
      $orderDateArray=$this->request->data['Order']['order_date'];
      $orderDateString=$orderDateArray['year'].'-'.$orderDateArray['month'].'-'.$orderDateArray['day'];
      $orderDate=date("Y-m-d",strtotime($orderDateString));
      $orderDatePlusOne=date("Y-m-d",strtotime($orderDateString."+1 days"));			
		}
		else {
			$orderDate=date("Y-m-d",strtotime(date('Y-m-d')));
			$orderDatePlusOne=date("Y-m-d",strtotime(date('Y-m-d')."+1 days"));
		}
		$this->set(compact('orderDate'));
    
    if ($this->request->is('post') && empty($this->request->data['refresh'])){	
      $boolInitialLoad='0';
      //pr($this->request->data);
      //pr($this->request->data['Order']);
      $salesOrderId=$this->request->data['Order']['sales_order_id'];
      $warehouseId=$this->request->data['Order']['warehouse_id'];
    
      $clientId=$this->request->data['Order']['third_party_id'];
      $clientName=$this->request->data['Order']['client_name'];
      $clientPhone=$this->request->data['Order']['client_phone'];
      $clientMail=$this->request->data['Order']['client_email'];
  
      $productRawMaterialPresent=true;
      $errorMessage="";
      
      $boolMultiplicationOK=true;
      $multiplicationErrorMessage='';
      $sumProductTotals=0;
      $boolProductPricesRegistered=true;
      $productPriceWarning='';
      $boolProductPriceLessThanDefaultPrice='0';
      $productPriceLessThanDefaultPriceError='';
      $boolProductPriceRepresentsBenefit=true;
      $productPriceBenefitError='';
      
      if (!empty($this->request->data['Product'])){
        foreach ($this->request->data['Product'] as $product){
          if (!empty($product['product_id']) && $product['product_quantity'] > 0){
            $requestProducts[]['Product']=$product;
          
            $productName=$this->Product->getProductName($product['product_id']);
            $rawMaterialName=($product['raw_material_id'] > 0?($this->Product->getProductName($product['raw_material_id'])):'');
            if (!empty($rawMaterialName)){
              $productName.=(' '.$rawMaterialName.' A');
            }
            $multiplicationDifference=abs($product['product_total_price']-$product['product_quantity']*$product['product_unit_price']);
            if ($multiplicationDifference>=0.01){
              $boolMultiplicationOK='0';
              $multiplicationErrorMessage.="Para producto ".$productName." la cantidad indicada es ".$product['product_quantity']." y el precio unitario ".$product['product_unit_price']." lo que da un producto de multiplicación de ".round($product['product_quantity']*$product['product_unit_price'],2)." pero el total calculado por la fila es de ".$product['product_total_price'].".  ";
            };
                   
            if ($this->Product->getProductTypeId($product['product_id']) == PRODUCT_TYPE_BOTTLE){
              if (empty($product['raw_material_id'])){
                $productRawMaterialPresent='0';  
                $errorMessage.="Para producto ".$productName." no se indicó la preforma, es obligatorio indicarlo.  ";
              }          
            }
            if ($product['product_id'] != PRODUCT_SERVICE_OTHER){
              if ($product['default_product_unit_price'] <=0) {
                $boolProductPricesRegistered='0'; 
                $productPriceWarning='Producto '.$productName.' no tiene registrado un precio de listado entonces no se podía aplicar un control de precios.  Por favor graba un precio para este producto primero.  ';  
              }
              if ($product['product_unit_price'] < $product['default_product_unit_price']) {
                $boolProductPriceLessThanDefaultPrice=true; 
                $productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$product['product_unit_price'].' pero el precio mínimo establecido es '.$product['default_product_unit_price'].'.  No se permite vender abajo del precio mínimo establecido.  ';  
              }
              if ($product['product_unit_price'] < $product['product_unit_cost']) {
                $boolProductPriceRepresentsBenefit='0'; 
                if ($userRoleId === ROLE_ADMIN){                
                  $productPriceBenefitError='Producto '.$productName.' tiene un precio '.$product['product_unit_price'].' pero el costo es '.$product['product_unit_cost'].'.  No se permite vender con pérdidas.  ';  
                }
                else {
                  $productPriceBenefitError='Precio no autorizado para producto '.$productName.'.  No se guardó la venta.  ';  
                } 
              }
            }
          }
          $sumProductTotals+=$product['product_total_price'];
        }
      }
      
      if (!array_key_exists('bool_credit',$this->request->data['Invoice'])){
        if (array_key_exists('bool_credit',$this->request->data)){
          $this->request->data['Invoice']['bool_credit']=$this->request->data['bool_credit'];
        }
        else {
          $this->request->data['Invoice']['bool_credit']=0;
        }
      }
      if (!array_key_exists('save_allowed',$this->request->data['Order'])){
        if (array_key_exists('save_allowed',$this->request->data)){  
          $this->request->data['Order']['save_allowed']=$this->request->data['save_allowed'];
        }
        else {
          $this->request->data['Order']['save_allowed']=1;
        }
      }
      if (!array_key_exists('retention_allowed',$this->request->data['Order'])){
        if (array_key_exists('retention_allowed',$this->request->data)){
          $this->request->data['Order']['retention_allowed']=$this->request->data['retention_allowed'];
        }
        else{
          $this->request->data['Order']['retention_allowed']=1;
        }
      }
      
      $saleDate=$this->request->data['Order']['order_date'];
      $saleDateString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
      $dueDateString = $this->Invoice->deconstruct('due_date', $this->request->data['Invoice']['due_date']);
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDateTime=new DateTime($latestClosingDate);
            
      $saleDateArray=[];
      $saleDateArray['year']=$saleDate['year'];
      $saleDateArray['month']=$saleDate['month'];
      $saleDateArray['day']=$saleDate['day'];
      
      $orderCode=$this->request->data['Order']['order_code'];
      $namedSales=$this->Order->find('all',[
        'conditions'=>[
          'order_code'=>$orderCode,
          'stock_movement_type_id'=>MOVEMENT_SALE,
        ],
      ]);
      if (count($namedSales)>0){
        $this->Session->setFlash(__('Ya existe una venta con el mismo código!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif ($saleDateString > date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de venta no puede estar en el futuro!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif ($saleDateString > date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de venta no puede estar en el futuro!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Order']['save_allowed'] == 0){
        $this->Session->setFlash('No se permite guardar esta venta de crédito!  Si está el gerente, marca la casilla de permitir guardar venta.  No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif ($saleDateString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($clientId) && empty($clientName)){
        $this->Session->setFlash('Se debe registrar el nombre del cliente.  No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif (empty($clientId) && empty($clientPhone) && empty($clientMail)){
        $this->Session->setFlash('Se debe registrar el teléfono o el correo electrónico del cliente.  No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Order']['third_party_id']==0){
        $this->Session->setFlash(__('Se debe seleccionar el cliente para la venta!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif (!$boolMultiplicationOK){
        $this->Session->setFlash($multiplicationErrorMessage.'No se guardó la venta.', 'default',['class' => 'error-message']);
      } 
      elseif (!$productRawMaterialPresent){
        $this->Session->setFlash($errorMessage.'No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif (abs($sumProductTotals-$this->request->data['Order']['price_subtotal']) > 0.01){
        $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$sumProductTotals.' pero el total calculado es '.$this->request->data['Order']['price_subtotal'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      elseif (abs($this->request->data['Order']['price_total']-$this->request->data['Order']['price_iva']-$this->request->data['Order']['price_subtotal'])>0.01){
        $this->Session->setFlash('La suma del subtotal '.$this->request->data['Order']['price_subtotal'].' y el IVA '.$this->request->data['Order']['price_iva'].' no igualan el precio total '.$this->request->data['Order']['price_total'].', la diferencia es de '.(abs($this->request->data['Order']['price_total']-$this->request->data['Order']['price_iva']-$this->request->data['Order']['price_subtotal'])).'.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Order']['price_total'])){
        $this->Session->setFlash(__('El total de la orden de venta tiene que ser mayor que cero.  No se guardó la orden.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['Invoice']['bool_retention'] && strlen($this->request->data['Invoice']['retention_number'])==0){
        $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['Invoice']['bool_retention'] && abs(0.02*$this->request->data['Order']['price_subtotal']-$this->request->data['Order']['retention_amount']) > 0.01){
        $this->Session->setFlash(__('La retención debería igualar el 2% del subtotal de la venta!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }  
      elseif (!$boolProductPricesRegistered && $userRoleId != ROLE_ADMIN){
        $this->Session->setFlash($productPriceWarning.'No se guardó la  venta.', 'default',['class' => 'error-message']);
      }
      elseif ($boolProductPriceLessThanDefaultPrice && $userRoleId != ROLE_ADMIN){
        $this->Session->setFlash($productPriceLessThanDefaultPriceError.'No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif (!$boolProductPriceRepresentsBenefit){
        $this->Session->setFlash($productPriceBenefitError.'No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Invoice']['bool_annulled']){
        $datasource=$this->Order->getDataSource();
        $datasource->begin();
        try {
          //pr($this->request->data);					
          $orderData=[];
          $orderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
          $orderData['Order']['order_date']=$this->request->data['Order']['order_date'];
          $orderData['Order']['order_code']=$this->request->data['Order']['order_code'];
          $orderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
          $orderData['Order']['bool_annulled']=true;
          $orderData['Order']['warehouse_id']=$warehouseId;
          $orderData['Order']['total_price']=0;
      
          $this->Order->create();
          if (!$this->Order->save($orderData)) {
            echo "Problema guardando el orden de salida";
            pr($this->validateErrors($this->Order));
            throw new Exception();
          }
          $order_id=$this->Order->id;
          
          $invoiceData=[];
          $invoiceData['Invoice']['order_id']=$order_id;
          $invoiceData['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
          $invoiceData['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
          $invoiceData['Invoice']['bool_annulled']=true;
          $invoiceData['Invoice']['warehouse_id']=$warehouseId;
          $invoiceData['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
          $invoiceData['Invoice']['sub_total_price']=0;
          $invoiceData['Invoice']['iva_price']=0;
          $invoiceData['Invoice']['total_price']=0;
          $invoiceData['Invoice']['currency_id']=CURRENCY_CS;
      
          $this->Invoice->create();
          if (!$this->Invoice->save($invoiceData)) {
            echo "Problema guardando la factura";
            pr($this->validateErrors($this->Invoice));
            throw new Exception();
          }
          
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),"Se registró una venta anulada con número ".$this->request->data['Order']['order_code']);
          $this->Session->setFlash(__('Se guardó la venta '.$this->request->data['Order']['order_code'].' anulada.'),'default',['class' => 'success'],'default',['class' => 'success']);
          //return $this->redirect(array('action' => 'resumenVentasRemisiones'));
          return $this->redirect(['action' => 'imprimirVenta',$order_id]);
          
          //return $this->redirect(array('action' => 'verVenta',$order_id));
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }
      }					
      elseif ($this->request->data['Order']['price_total']==0){
        $this->Session->setFlash(__('El precio total no puede ser cero para una venta que no está anulada!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      else if (!$this->request->data['Invoice']['bool_credit']&&$this->request->data['Invoice']['cashbox_accounting_code_id']==0){
        $this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una factura de contado!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['Invoice']['bool_retention'] && strlen($this->request->data['Invoice']['retention_number'])==0){
        $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }	
      else {
        // before moving into the selling part, perform the check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
        $saleItemsOK=true;
        $exceedingItems="";
        
        $productMultiplicationOk=true;
        $productMultiplicationWarning="";
        
        $productTotalSumBasedOnProductTotals=0;
        
        $productCount=0;
        $products=[];
        foreach ($this->request->data['Product'] as $product){
          //pr($product);
          // keep track of number of rows so that in case of an error jquery displays correct number of rows again
          if ($product['product_id']>0){
            $productCount++;
          }
          if ($product['product_quantity']>0 && $product['product_id']>0){
            $products[]=$product;
            $quantityEntered=$product['product_quantity'];
            $productid = $product['product_id'];
            $productionresultcodeid = $product['production_result_code_id'];
            $rawmaterialid = $product['raw_material_id'];
            
            $productName=$this->Product->getProductName($product['product_id']);
            $productTypeId=$this->Product->getProductTypeId($product['product_id']);
            if ($productTypeId != PRODUCT_TYPE_SERVICE){  
              if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
                $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($productid,$rawmaterialid,$productionresultcodeid,$saleDateString,$warehouseId,true);
              }
              else {
                $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productid,$saleDateString,$warehouseId,true);
              }
              //echo "quantity in stock is ".$quantityInStock."<br>";
              
              //compare the quantity requested and the quantity in stock
              if ($quantityEntered>$quantityInStock){
                $saleItemsOK='0';
                $exceedingItems.=__("Para producto ".$productName." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
              }
            }
            
            
          }
        }
        //echo "saleItemsOK is ".$saleItemsOK."<br>";
        //echo "exceedingItems is ".$exceedingItems."<br>";
        
        $creditBlocked=true;
        $creditBlockMessage="";
        $unpaidBlocked=true;
        $unpaidBlockMessage="";
        
        $boolCreditAuthorized='0';
        if ($salesOrderId > 0){
          $salesOrder=$this->SalesOrder->find('first',[
            'conditions'=>[
              'SalesOrder.id'=>$salesOrderId,
            ],
            'recursive'=>-1,
          ]);
          if (!empty($salesOrder)){
            //pr($salesOrder);
            if (in_array($salesOrder['SalesOrder']['credit_authorization_user_id'],$adminUserIds) && $salesOrder['SalesOrder']['bool_credit']){
              $boolCreditAuthorized=true;
            }
          }
        } 
        
        if(!$this->request->data['Invoice']['bool_credit']){
          $creditBlocked='0';
          $unpaidBlocked='0';
        }
        else {
          $clientCreditStatus=$this->ThirdParty->getClientCreditStatus($this->request->data['Order']['third_party_id']);
          //pr($clientCreditStatus);
          $creditUsedBeforeInvoice=$clientCreditStatus['ThirdParty']['pending_payment'];
          $creditUsedWithThisInvoice=$this->request->data['Order']['price_total'];
          if($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
            $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($saleDateString);
            $creditUsedWithThisInvoice=round($creditUsedWithThisInvoice*$applicableExchangeRate,2);
          }
          $creditUsedAfterInvoice= $creditUsedBeforeInvoice + $creditUsedWithThisInvoice;
          $creditLimit=$clientCreditStatus['ThirdParty']['credit_amount'];
          
          if ($creditLimit < $creditUsedAfterInvoice){
            $creditBlockMessage.="El cliente ".$clientCreditStatus['ThirdParty']['company_name']." tiene un límite de crédito de ".$creditLimit." y ya tiene pagos pendientes para un total de C$ ".$creditUsedBeforeInvoice.".  Con el total de esta factura (C$ ".$creditUsedWithThisInvoice.") el monto total que se debe (C$ ".$creditUsedAfterInvoice.") excede el límite de crédito.";
          }
          else {
            $creditBlocked='0';
          }
          
          $pendingInvoices=$this->Invoice->find('all',[
            'fields'=>[
              'Invoice.id','Invoice.invoice_code',
              'Invoice.total_price','Invoice.currency_id',
              'Invoice.invoice_date','Invoice.due_date',
              'Invoice.client_id',
              'Currency.abbreviation','Currency.id',
              'Invoice.order_id',
            ],
            'conditions'=>[
              'Invoice.due_date <'=>date('Y-m-d'),
              'Invoice.bool_annulled'=>'0',
              'Invoice.bool_paid'=>'0',
              'Invoice.client_id'=>$this->request->data['Order']['third_party_id'],
            ],
            'order'=>'Invoice.invoice_date ASC',
          ]);
          
          if (!empty($pendingInvoices)){
            $unpaidBlockMessage="El cliente ".$clientCreditStatus['ThirdParty']['company_name']." tiene facturas de crédito que vencieron: ";
            $counter=0;
            foreach ($pendingInvoices as $pendingInvoice){
              $unpaidBlockMessage.=$pendingInvoice['Invoice']['invoice_code']." (".($pendingInvoice['Invoice']['currency_id'] == CURRENCY_USD?"US$":"C$")." ".$pendingInvoice['Invoice']['total_price'].")";
              if ($counter<count($pendingInvoices)-1){
                $unpaidBlockMessage.=",";
              }
              $counter++;
            } 
            $unpaidBlockMessage.=". ";
            //$unpaidBlockMessage="Revise el ".$this->Html->link('estado de crédito del cliente',['controller'=>'invoices','action'=>'verFacturasPorCobrar',$this->request->data['Order']['third_party_id']])."!";
          }
          else {
            $unpaidBlocked='0';  
          }
          
          if ($roleId == ROLE_ADMIN){
            $creditBlocked='0';
            $unpaidBlocked='0';  
          }
        }
        
        //echo "saleItemsOK is ".$saleItemsOK."<br>";
        //echo "exceedingItems is ".$exceedingItems."<br>";
        //echo 'creditBlocked is '.$creditBlocked.'<br/>';  
        //echo 'unpaidBlocked is '.$unpaidBlocked.'<br/>';  
        if (!empty($exceedingItems)){
          $exceedingItems.='Por favor corriga e intente de nuevo';
          //echo "exceedingItems is ".$exceedingItems."<br>";
        }					
        if (empty($saleItemsOK)){
          //echo "sales items not ok<br/>";  
          $_SESSION['saleRequestData']=$this->request->data;
          
          $aco_name="Orders/manipularVenta";		
          $bool_order_manipularventa_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
          
          if ($bool_order_manipularventa_permission){
            echo 'redirigiendo a reclasificación automática<br/>';  
            return $this->redirect(['action' => 'manipularVenta']);
          }
          //echo __('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems.'<br/>';
          $this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
        }
        elseif (($creditBlocked || $unpaidBlocked) && $userRoleId != ROLE_ADMIN && !$boolCreditAuthorized){
          //echo 'credit not ok<br/>';  
          //echo 'creditBlocked is '.$creditBlocked.'<br/>';  
          //echo 'unpaidBlocked is '.$unpaidBlocked.'<br/>';  
          
          $this->Session->setFlash($creditBlockMessage.$unpaidBlockMessage.'  No se guardó la factura de crédito.', 'default',['class' => 'error-message']);
        }
        else{
          //echo 'everything chipper??!!<br/>';
          $totalPriceProducts=0;
          
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          try {
            $currency_id=$this->request->data['Invoice']['currency_id'];
          
            $retention_invoice=$this->request->data['Order']['retention_amount'];
            $sub_total_invoice=$this->request->data['Order']['price_subtotal'];
            $ivaInvoice=$this->request->data['Order']['price_iva'];
            $totalInvoice=$this->request->data['Order']['price_total'];
        
            // if all products are in stock, proceed with the sale 
            $this->Order->create();
            $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
            // ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
            if ($currency_id==CURRENCY_USD){
              $this->request->data['Order']['total_price']=$sub_total_invoice*$this->request->data['Order']['exchange_rate'];
            }
            else {
              $this->request->data['Order']['total_price']=$sub_total_invoice;
            }
            //pr($this->request->data);
            if (!$this->Order->save($this->request->data)) {
              echo "Problema guardando la salida";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            }
          
            $order_id=$this->Order->id;
            $orderCode=$this->request->data['Order']['order_code'];
          
            $this->Invoice->create();
            $this->request->data['Invoice']['order_id']=$order_id;
            $this->request->data['Invoice']['sales_order_id']=$salesOrderId;
            $this->request->data['Invoice']['warehouse_id']=$warehouseId;
            $this->request->data['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
            $this->request->data['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
            $this->request->data['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
            
            $this->request->data['Invoice']['sub_total_price']=$this->request->data['Order']['price_subtotal'];
            $this->request->data['Invoice']['iva_price']=$this->request->data['Order']['price_iva'];
            $this->request->data['Invoice']['total_price']=$this->request->data['Order']['price_total'];
            $this->request->data['Invoice']['retention_amount']=$this->request->data['Order']['retention_amount'];
            
            if ($this->request->data['Invoice']['bool_credit']){
              $this->request->data['Invoice']['bool_retention']='0';
              $this->request->data['Invoice']['retention_amount']=0;
              $this->request->data['Invoice']['retention_number']="";
            }
            else {
              $this->request->data['Invoice']['bool_paid']=true;
            }
        
            if (!$this->Invoice->save($this->request->data)) {
              echo "Problema guardando la factura";
              pr($this->validateErrors($this->Invoice));
              throw new Exception();
            }
            
            $invoice_id=$this->Invoice->id;
            
            if ($salesOrderId > 0){
              $this->SalesOrder->id=$salesOrderId;
              $salesOrderArray=[
                'SalesOrder'=>[
                  'id'=>$salesOrderId,
                  'bool_invoice'=>true,
                  'invoice_id'=>$invoice_id,
                ]
              ];
              if (!$this->SalesOrder->save($salesOrderArray)) {
                echo "Problema actualizando la orden de venta";
                pr($this->validateErrors($this->SalesOrder));
                throw new Exception();
              }
            }
            
            if ($currency_id==CURRENCY_USD){
              $this->loadModel('ExchangeRate');
              $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateString);
              //pr($applicableExchangeRate);
              $retention_CS=round($retention_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
              $sub_total_CS=round($sub_total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
              $ivaCS=round($ivaInvoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
              $totalCS=round($totalInvoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
            }
            else {
              $retention_CS=$retention_invoice;
              $sub_total_CS=$sub_total_invoice;
              $ivaCS=$ivaInvoice;
              $totalCS=$totalInvoice;
            }
            $this->AccountingCode->recursive=-1;
            if ($this->request->data['Invoice']['bool_credit']){
              $clientId=$this->request->data['Order']['third_party_id'];
              $this->loadModel('ThirdParty');
              $this->ThirdParty->recursive=-1;
              $thisClient=$this->ThirdParty->getClientById($clientId);
            
              $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
              $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
              $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
              $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
              $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
              $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$ivaCS;
              $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
              
              if (empty($thisClient['ThirdParty']['accounting_code_id'])){
                $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
                  ),
                ));
              }
              else {								
                $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>$thisClient['ThirdParty']['accounting_code_id'],
                  ),
                ));
              }
              $accountingRegisterData['AccountingMovement'][0]['concept']="A cobrar Venta ".$orderCode;
              $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS;
              
              $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
              $accountingCode=$this->AccountingCode->find('first',array(
                'conditions'=>array(
                  'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                ),
              ));
              $accountingRegisterData['AccountingMovement'][1]['concept']="Ingresos Venta ".$orderCode;
              $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
              
              if ($this->request->data['Invoice']['bool_iva']){
                $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$ivaCS;
              }
              
              //pr($accountingRegisterData);
              $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
              $this->recordUserAction($this->AccountingRegister->id,"add",null);
          
              $AccountingRegisterInvoiceData=[];
              $AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
              $AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
              $this->AccountingRegisterInvoice->create();
              if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                pr($this->validateErrors($this->AccountingRegisterInvoice));
                echo "problema al guardar el lazo entre asiento contable y factura";
                throw new Exception();
              }
              //echo "link accounting register sale saved<br/>";					
            }
            else {
              $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
              $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
              $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
              $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
              $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
              $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$ivaCS;
              $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
              
              if (!$this->request->data['Invoice']['bool_retention']){
                $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS;
              }
              else {
                // with retention
                $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS-$retention_CS;
              }
              
              $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
              $accountingCode=$this->AccountingCode->find('first',array(
                'conditions'=>array(
                  'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                ),
              ));
              $accountingRegisterData['AccountingMovement'][1]['concept']="Subtotal Venta ".$orderCode;
              $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
              
              if ($this->request->data['Invoice']['bool_iva']){
                $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$ivaCS;
              }
              if ($this->request->data['Invoice']['bool_retention']){
                $accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_RETENCIONES_POR_COBRAR,
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][3]['concept']="Retención Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][3]['debit_amount']=$retention_CS;
              }
              
              //pr($accountingRegisterData);
              $accountingRegisterId=$this->saveAccountingRegisterData($accountingRegisterData,true);
              $this->recordUserAction($this->AccountingRegister->id,"add",null);
              //echo "accounting register saved for cuentas cobrar clientes<br/>";
          
              $AccountingRegisterInvoiceData=[];
              $AccountingRegisterInvoiceData['accounting_register_id']=$accountingRegisterId;
              $AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
              $this->AccountingRegisterInvoice->create();
              if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                pr($this->validateErrors($this->AccountingRegisterInvoice));
                echo "problema al guardar el lazo entre asiento contable y factura";
                throw new Exception();
              }
              //echo "link accounting register sale saved<br/>";	
            }
          
            foreach ($products as $product){
              // four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
              //pr($product);
              
              // load the product request data into variables
              $productId = $product['product_id'];
              $product_category_id = $this->Product->getProductCategoryId($productId);
              $production_result_code_id =0;
              $raw_material_id=0;
              
              if ($product_category_id==CATEGORY_PRODUCED){
                $production_result_code_id = $product['production_result_code_id'];
                $raw_material_id = $product['raw_material_id'];
              }
              if (array_key_exists('service_unit_cost',$product)){
                $service_unit_cost=$product['service_unit_cost'];
              }
              else {
                $service_unit_cost=0;
              }
              $productUnitPrice=$product['product_unit_price'];
              $product_quantity = $product['product_quantity'];
              
              if ($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
                $productUnitPrice*=$this->request->data['Order']['exchange_rate'];
              }
              
              // get the related product data
              $this->Product->recursive=-1;
              $linkedProduct=$this->Product->find('first',[
                'conditions'=>['Product.id'=>$productId,],
              ]);
              $productName=$linkedProduct['Product']['name'];
              
              if ($linkedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                // STEP 1: SAVE THE STOCK ITEM(S)
                // first prepare the materials that will be taken out of stock
                if ($product_category_id==CATEGORY_PRODUCED){
                  $usedMaterials= $this->StockItem->getFinishedMaterialsForSale($productId,$production_result_code_id,$product_quantity,$raw_material_id,$saleDateString,$warehouseId);		
                }
                else {
                  $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$product_quantity,$saleDateString,$warehouseId);		
                }
                //pr($usedMaterials);
              
                for ($k=0;$k<count($usedMaterials);$k++){
                  $materialUsed=$usedMaterials[$k];
                  $stockItemId=$materialUsed['id'];
                  $quantity_present=$materialUsed['quantity_present'];
                  $quantity_used=$materialUsed['quantity_used'];
                  $quantity_remaining=$materialUsed['quantity_remaining'];
                  if (!$this->StockItem->exists($stockItemId)) {
                    throw new NotFoundException(__('Invalid StockItem'));
                  }
                  //$linkedStockItem=$this->StockItem->read(null,$stockItemId);
                  $this->StockItem->recursive=-1;
                  $linkedStockItem=$this->StockItem->find('first',[
                    'conditions'=>['StockItem.id'=>$stockItemId,],
                  ]);
                  $message="Se vendió lote ".$productName." (Cantidad:".$quantity_used.") para Venta ".$orderCode;
                  
                  $stockItemData=[];
                  $stockItemData['id']=$stockItemId;
                  //$stockItemData['name']=$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName;
                  $stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
                  $stockItemData['remaining_quantity']=$quantity_remaining;
                  // notice that no new stockitem is created because we are taking from an already existing one
                  if (!$this->StockItem->save($stockItemData)) {
                    echo "problema al guardar el lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  // STEP 2: SAVE THE STOCK MOVEMENT
                  $message="Se vendió ".$productName." (Cantidad:".$quantity_used.", total para venta:".$product_quantity.") para Venta ".$orderCode;
                  $stockMovementData=[];
                  $stockMovementData['movement_date']=$saleDate;
                  $stockMovementData['bool_input']='0';
                  $stockMovementData['name']=$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName;
                  $stockMovementData['description']=$message;
                  $stockMovementData['order_id']=$order_id;
                  $stockMovementData['stockitem_id']=$stockItemId;
                  $stockMovementData['product_id']=$productId;
                  $stockMovementData['product_quantity']=$quantity_used;
                  $stockMovementData['product_unit_price']=$productUnitPrice;
                  $stockMovementData['product_total_price']=$productUnitPrice*$quantity_used;
                  $stockMovementData['service_unit_cost']=0;
                  $stockMovementData['service_total_cost']=0;
                  $stockMovementData['production_result_code_id']=$production_result_code_id;
                  
                  $totalPriceProducts+=$stockMovementData['product_total_price'];
                  
                  $this->StockMovement->create();
                  if (!$this->StockMovement->save($stockMovementData)) {
                    echo "problema al guardar el movimiento de lote";
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                
                  // STEP 3: SAVE THE STOCK ITEM LOG
                  $this->recreateStockItemLogs($stockItemId);
                      
                  // STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                  $this->recordUserActivity($this->Session->read('User.username'),$message);
                }
              
              }
              else {
                $message="Se vendió ".$productName." (Cantidad:".$product_quantity.", total para venta:".$product_quantity.") para Venta ".$orderCode;
                $stockMovementData=[];
                $stockMovementData['movement_date']=$saleDate;
                $stockMovementData['bool_input']='0';
                $stockMovementData['name']=$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName;
                $stockMovementData['description']=$message;
                $stockMovementData['order_id']=$order_id;
                $stockMovementData['stockitem_id']=0;
                $stockMovementData['product_id']=$productId;
                $stockMovementData['product_quantity']=$product_quantity;
                $stockMovementData['product_unit_price']=$productUnitPrice;
                $stockMovementData['product_total_price']=$productUnitPrice*$product_quantity;
                $stockMovementData['service_unit_cost']=$service_unit_cost;
                $stockMovementData['service_total_cost']=$service_unit_cost*$product_quantity;
                $stockMovementData['production_result_code_id']=$production_result_code_id;
                
                $totalPriceProducts+=$stockMovementData['product_total_price'];
                
                $this->StockMovement->create();
                if (!$this->StockMovement->save($stockMovementData)) {
                  echo "problema al guardar el movimiento de lote";
                  pr($this->validateErrors($this->StockMovement));
                  throw new Exception();
                }
              
                $this->recordUserActivity($this->Session->read('User.username'),$message);
              }
            }
            
            if ($this->request->data['Invoice']['currency_id'] == CURRENCY_USD){
              if (abs($this->request->data['Invoice']['sub_total_price']-$totalPriceProducts/$this->request->data['Order']['exchange_rate']) > 0.01){
                echo "el subtotal no iguala el precio sumado de los productos";
                throw new Exception();
              }
            }
            else {
              if (abs($this->request->data['Invoice']['sub_total_price']-$totalPriceProducts) > 0.01){
                echo "el subtotal no iguala el precio sumado de los productos";
                throw new Exception();
              }
            }
            
            $datasource->commit();
            $this->recordUserAction($this->Order->id,"add",null);
            // SAVE THE USERLOG FOR THE PURCHASE
            $this->recordUserActivity($this->Session->read('User.username'),"Sale registered with invoice code ".$this->request->data['Order']['order_code']);
            $this->Session->setFlash(__('Se guardó la venta. ').$creditBlockMessage.$unpaidBlockMessage,'default',['class' => 'success']);
            //return $this->redirect(array('action' => 'resumenVentasRemisiones'));
            return $this->redirect(['action' => 'imprimirVenta',$order_id]);
            // on the view page the print button will be present; it should display the invoice just as it has been made out, this is then sent to javascript
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
          }
        }
      }
    }
		
    $this->set(compact('boolInitialLoad'));
		$this->set(compact('inventoryDisplayOptionId'));
		$this->set(compact('requestProducts'));
		$this->set(compact('salesOrderId'));
    
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
    
		$thirdParties = $this->Order->ThirdParty->getActiveClientList();
		
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		
  /*  
    $stockItemConditions=[
      'StockItem.bool_active'=>true,
      'StockItem.stockitem_creation_date <'=>$orderDatePlusOne,
    ];
    if (!empty($warehouseId)){
      $stockItemConditions[]=['StockItem.warehouse_id'=>$warehouseId,];
    }
    
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
		//pr($productsAll);
		$products = [];
		$rawMaterialIds=[];
    $rawMaterialsAvailablePerFinishedProduct=[];

		foreach ($productsAll as $product){
			// only show products that are in inventory AT CURRENT DATE
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockItem){
					// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
					// in this case the associative array just contains the product_id because otherwise the list would become very long
					if ($stockItem['remaining_quantity']>0){
            $productId=$product['Product']['id'];
						$products[$productId]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
            
            if (!empty($stockItem['raw_material_id'])){
             
              
              $rawMaterialId=$stockItem['raw_material_id'];
              $productionResultCodeId=$stockItem['production_result_code_id'];
              // first build list of general raw material ids
              if (!in_array($rawMaterialId,$rawMaterialIds)){
                $rawMaterialIds[]=$rawMaterialId;
              }
              // verify and if necessary add the product id to inventory table
              if (!array_key_exists($productId,$rawMaterialsAvailablePerFinishedProduct)){
                $rawMaterialsAvailablePerFinishedProduct[$productId]=[];
              }
              //if ($productId==31){
              //  pr($stockItem);
              //}
              // initialize rawmaterial if needed
              if (!array_key_exists($rawMaterialId,$rawMaterialsAvailablePerFinishedProduct[$productId])){
                $rawMaterialsAvailablePerFinishedProduct[$productId][$rawMaterialId]=[
                  '1'=>0,
                  '2'=>0,
                  '3'=>0
                ];
              }
              // add relevant figure to raw material production result codes
              $rawMaterialsAvailablePerFinishedProduct[$productId][$rawMaterialId][$productionResultCodeId]+=$stockItem['remaining_quantity'];
            }  
          }            
				}
			}
      elseif ($product['ProductType']['id'] == PRODUCT_TYPE_SERVICE){
        $products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
      }
		}
		//pr($rawMaterialIds);
    //pr($rawMaterialsAvailablePerFinishedProduct);
    
		$this->Product->recursive=-1;
		$preformasAll = $this->Product->find('all',array(
      'fields'=>array('Product.id','Product.name'),
      'conditions' => [
        //'Product.product_type_id'=> $rawProductTypeIds,
        'Product.id'=>$rawMaterialIds,
        'Product.bool_active'=>true
      ],
      //'contain'=>array(
      //	'ProductType',
      //	//'StockItem'=>array(
      //	//	'fields'=> array('remaining_quantity'),
      //  //  'conditions'=>array(
      //  //    'StockItem.bool_active'=>true,
      //  //  ),
      //	//),
      //),
      'order'=>'Product.name',
		));
    //pr($preformasAll);
    
		$rawMaterials=[];
		foreach ($preformasAll as $preforma){
      $startingPosition=0;
      if (strpos ($preforma['Product']['name'],"PREFORMA") !== false){
        $preforma['Product']['name']=str_replace("PREFORMA ","",$preforma['Product']['name']);
      }
			$rawMaterials[$preforma['Product']['id']]=substr($preforma['Product']['name'],0,18).(strlen($preforma['Product']['name'])>18?"...":"");
		}
  */  
    $availableProductsForSale=$this->Product->getAvailableProductsForSale($orderDate,$warehouseId,true);
    
    $products=$availableProductsForSale['products'];
    $rawMaterialsAvailablePerFinishedProduct=$availableProductsForSale['rawMaterialsAvailablePerFinishedProduct'];
    $rawMaterials=$availableProductsForSale['rawMaterials'];
    $this->set(compact('products'));
    $this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    $this->set(compact('rawMaterials'));
    
    $productionResultCodes=$this->ProductionResultCode->find('list',[
      'conditions'=>['ProductionResultCode.id'=>PRODUCTION_RESULT_CODE_A]
    ]);
    $this->set(compact('productionResultCodes'));
    
		//if (!empty($inventoryDisplayOptionId)){
      //echo "inventory display option id is ".$inventoryDisplayOptionId."<br/>";
			$productCategoryId=CATEGORY_PRODUCED;
			$productTypeIds=$this->ProductType->find('list',[
				'fields'=>['ProductType.id'],
				'conditions'=>['ProductType.product_category_id'=>$productCategoryId],
			]);
      $finishedMaterialsInventory =[];
			$finishedMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
			if ($warehouseId != WAREHOUSE_INJECTION){
        $injectionMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,WAREHOUSE_INJECTION);
        $this->set(compact('injectionMaterialsInventory'));
      }
			//pr($finishedMaterialsInventory);
			$productCategoryId=CATEGORY_OTHER;
			$productTypeIds=$this->ProductType->find('list',[
				'fields'=>['ProductType.id'],
				'conditions'=>['ProductType.product_category_id'=>$productCategoryId]
			]);
			$otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
      //pr($otherMaterialsInventory);
      
			$productCategoryId=CATEGORY_RAW;
			$productTypeIds=$this->ProductType->find('list',[
				'fields'=>['ProductType.id'],
				'conditions'=>['ProductType.product_category_id'=>$productCategoryId],
			]);
			$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
      
		//}
    //echo "inventarios hallados<br/>";
		$currencies = $this->Currency->find('list');
    //echo "currencies found<br/>";
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			),
			'order'=>'AccountingCode.code',
		));
		// calculate the code for the new service sheet
		$newInvoiceCode="";
		
		$this->set(compact('thirdParties', 'stockMovementTypes','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','currencies','accountingCodes','newInvoiceCode'));
		
		$orderDate=date( "Y-m-d");
		$orderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($orderDate);
		$exchangeRateOrder=$orderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
		//$newInvoiceCode=$this->Invoice->getInvoiceCode($warehouseId);
    //$this->set(compact('newInvoiceCode'));
		
    $otherProducts=$this->Product->find('list',[
      'fields'=>'Product.id',
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_SERVICE]
    ]);
    $otherProducts=array_values($otherProducts);
    $this->set(compact('otherProducts'));
    
    $users=$this->User->getActiveVendorUserList($warehouseId);
    $this->set(compact('users'));
    
    //echo "warehouse id is ".$warehouseId."<br/>";
    $salesOrders=$this->SalesOrder->getPendingSalesOrders($warehouseId);
    $this->set(compact('salesOrders'));
    //pr($salesOrders);
    
		$clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
    
	}
		
	public function crearVenta($salesOrderId=0) {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
    $this->loadModel('ProductionResultCode');
		
		$this->loadModel('ClosingDate');
    
    $this->loadModel('SalesOrder');
    
    $this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
    $this->loadModel('ThirdParty');
    $this->loadModel('ClientType');
		$this->loadModel('Zone');
		//$this->loadModel('Vehicle');
    
    $this->loadModel('PriceClientCategory');
    
		$this->loadModel('Currency');
		$this->loadModel('Invoice');
    $this->loadModel('AccountingCode');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterInvoice');
		
    $this->loadModel('ExchangeRate');
		
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    $this->loadModel('WarehouseProduct');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
     
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $canSeeInventoryCost=$this->UserPageRight->hasUserPageRight('VER_COSTO_INVENTARIO',$userRoleId,$loggedUserId,'All','All');
    $this->set(compact('canSeeInventoryCost'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'Orders','resumenVentasRemisiones');
    $this->set(compact('canSeeAllUsers'));
    
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'Orders','resumenVentasRemisiones');
    $this->set(compact('canSeeAllVendors'));
	
	$canApplyCredit=$this->UserPageRight->hasUserPageRight('AUTORIZACION_CREDITO',$userRoleId,$loggedUserId,'orders','crearVenta');
 
  
		$this->Order->recursive=-1;
		$this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		$this->ThirdParty->recursive=-1;
		$this->Order->StockMovementType->recursive=-1;
		$this->AccountingCode->recursive=-1;
		$this->Invoice->recursive=-1;
    
    $orderDate=date( "Y-m-d");
    $clientId=0;
    $currencyId=CURRENCY_CS;
    $vendorUserId=$loggedUserId;
    $recordUserId=$loggedUserId;
    $creditAuthorizationUserId=$loggedUserId;
    //$driverUserId=0;
    //$vehicleId=0;
		// TEMPORARY FIX COLINAS
    //$warehouseId=0;
    $warehouseId= WAREHOUSE_DEFAULT;
    $boolDelivery='0';
		
		$inventoryDisplayOptions=[
			'0'=>'No mostrar inventario',
			'1'=>'Mostrar inventario',
		];
		$this->set(compact('inventoryDisplayOptions'));
		$inventoryDisplayOptionId=0;
    
    $adminUsers=$this->User->getActiveUsersForRole(ROLE_ADMIN);
    $adminUserIds=array_keys($adminUsers);
    $this->set(compact('adminUserIds'));
    //pr($adminUserIds);
    
    $genericClientIds=$this->ThirdParty->getGenericClientIds();
    $this->set(compact('genericClientIds'));
    
    $boolInitialLoad=true;
		$requestProducts=[];
    if ($this->request->is('post')) {
      $boolInitialLoad='0';
      if (!empty($this->request->data['Product'])){
        foreach ($this->request->data['Product'] as $product){
          if (!empty($product['product_id']) && $product['product_quantity'] > 0){
            $requestProducts[]['Product']=$product;
          }
        }
      }
      
      $salesOrderId=$this->request->data['Order']['sales_order_id'];
      
      $orderDateArray=$this->request->data['Order']['order_date'];
      $orderDateString=$orderDateArray['year'].'-'.$orderDateArray['month'].'-'.$orderDateArray['day'];
      $orderDate=date("Y-m-d",strtotime($orderDateString));
      
      $clientId=$this->request->data['Order']['third_party_id'];
      $currencyId=$this->request->data['Invoice']['currency_id'];
      
      $vendorUserId=$this->request->data['Order']['vendor_user_id'];
      $recordUserId=$this->request->data['Order']['record_user_id'];
      if (!array_key_exists('credit_authorization_user_id',$this->request->data['Order'])){
        $this->request->data['Order']['credit_authorization_user_id']=$this->request->data['credit_authorization_user_id'];
      }
      $creditAuthorizationUserId=$this->request->data['Order']['credit_authorization_user_id'];
      //$driverUserId=$this->request->data['Order']['driver_user_id'];
      //$vehicleId=$this->request->data['Order']['vehicle_id'];
      
      $warehouseId=$this->request->data['Order']['warehouse_id'];
      
      $boolDelivery=$this->request->data['Order']['bool_delivery'];
		}
		$orderDatePlusOne=date("Y-m-d",strtotime(date('Y-m-d')."+1 days"));
		$this->set(compact('orderDate'));
    
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
    
    if ($this->request->is('post') && empty($this->request->data['refresh'])){	
      $clientId=$this->request->data['Order']['third_party_id'];
      $clientName=$this->request->data['Order']['client_name'];
      $clientPhone=$this->request->data['Order']['client_phone'];
      $clientMail=$this->request->data['Order']['client_email'];
      
      $productRawMaterialPresent=true;
      $errorMessage="";
      
      $boolMultiplicationOK=true;
      $multiplicationErrorMessage='';
      $sumProductTotals=0;
      $boolProductPricesRegistered=true;
      $productPriceWarning='';
      $boolProductPriceLessThanDefaultPrice='0';
      $productPriceLessThanDefaultPriceError='';
      $boolProductPriceRepresentsBenefit=true;
      $productPriceBenefitError='';

   
      if (!empty($this->request->data['Product'])){
        foreach ($this->request->data['Product'] as $product){
          if (!empty($product['product_id']) && $product['product_quantity'] > 0){
            $acceptableProductPrice=1000;
            $acceptableProductPrice=$this->Product->getAcceptablePriceForProductClientCostQuantityDate($product['product_id'],$clientId,$product['product_unit_cost'],$product['product_quantity'],$orderDate,$product['raw_material_id']);
            
            $productName=$this->Product->getProductName($product['product_id']);
            $rawMaterialName=($product['raw_material_id'] > 0?($this->Product->getProductName($product['raw_material_id'])):'');
            if (!empty($rawMaterialName)){
              $productName.=(' '.$rawMaterialName.' A');
            }
            $multiplicationDifference=abs($product['product_total_price']-$product['product_quantity']*$product['product_unit_price']);
            if ($multiplicationDifference>=0.01){
              $boolMultiplicationOK='0';
              $multiplicationErrorMessage.="Para producto ".$productName." la cantidad indicada es ".$product['product_quantity']." y el precio unitario ".$product['product_unit_price']." lo que da un producto de multiplicación de ".round($product['product_quantity']*$product['product_unit_price'],2)." pero el total calculado por la fila es de ".$product['product_total_price'].".  ";
            };
                   
            if ($this->Product->getProductTypeId($product['product_id']) == PRODUCT_TYPE_BOTTLE){
              if (empty($product['raw_material_id'])){
                $productRawMaterialPresent='0';  
                $errorMessage.="Para producto ".$productName." no se indicó la preforma, es obligatorio indicarlo.  ";
              }          
            }
            if ($product['product_id'] != PRODUCT_SERVICE_OTHER){
              if ($product['default_product_unit_price'] <=0) {
                $boolProductPricesRegistered='0'; 
                $productPriceWarning='Producto '.$productName.' no tiene registrado un precio de listado entonces no se podía aplicar un control de precios.  Por favor graba un precio para este producto primero.  ';  
              }
              // 20211004 default_product_price could be tricked into accepting volume price by users with bad intentions by increasing and then decreasing prices, that's why the price is calculated afreshe in $acceptableProductPrice
              // if ($product['product_unit_price'] < $product['default_product_unit_price']) {
              if ($product['product_unit_price'] < $acceptableProductPrice) {
                $boolProductPriceLessThanDefaultPrice=true; 
                //$productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$product['product_unit_price'].' pero el precio mínimo establecido es '.$product['default_product_unit_price'].'.  No se permite vender abajo del precio mínimo establecido.  ';  
                $productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$product['product_unit_price'].' pero el precio mínimo establecido es '.$acceptableProductPrice.'.  No se permite vender abajo del precio mínimo establecido.  ';  
              }
              if ($product['product_unit_price'] < $product['product_unit_cost']) {
                $boolProductPriceRepresentsBenefit='0'; 
                if ($userRoleId === ROLE_ADMIN){                
                  $productPriceBenefitError='Producto '.$productName.' tiene un precio '.$product['product_unit_price'].' pero el costo es '.$product['product_unit_cost'].'.  No se permite vender con pérdidas.  ';  
                }
                else {
                  $productPriceBenefitError='Precio no autorizado para producto '.$productName.'.  No se guardó la venta.  ';  
                } 
              }
            }
          }
          $sumProductTotals+=$product['product_total_price'];
        }
      }
      
      if (!array_key_exists('bool_credit',$this->request->data['Invoice'])){
        if (array_key_exists('bool_credit',$this->request->data)){
          $this->request->data['Invoice']['bool_credit']=$this->request->data['bool_credit'];
        }
        else {
          $this->request->data['Invoice']['bool_credit']=0;
        }
      }
      if (!array_key_exists('save_allowed',$this->request->data['Order'])){
        if (array_key_exists('save_allowed',$this->request->data)){  
          $this->request->data['Order']['save_allowed']=$this->request->data['save_allowed'];
        }
        else {
          $this->request->data['Order']['save_allowed']=1;
        }
      }
      if (!array_key_exists('credit_authorization_user_id',$this->request->data['Order'])){
        $this->request->data['Order']['credit_authorization_user_id']=$this->request->data['credit_authorization_user_id'];
      }
      $creditAuthorizationUserId=$this->request->data['Order']['credit_authorization_user_id'];
      $this->request->data['Invoice']['credit_authorization_user_id']=$creditAuthorizationUserId;
      if (!array_key_exists('retention_allowed',$this->request->data['Order'])){
        if (array_key_exists('retention_allowed',$this->request->data)){
          $this->request->data['Order']['retention_allowed']=$this->request->data['retention_allowed'];
        }
        else{
          $this->request->data['Order']['retention_allowed']=1;
        }
      }
      
      $saleDate=$this->request->data['Order']['order_date'];
      $saleDateString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
      $dueDateString = $this->Invoice->deconstruct('due_date', $this->request->data['Invoice']['due_date']);
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDateTime=new DateTime($latestClosingDate);
            
      $saleDateArray=[];
      $saleDateArray['year']=$saleDate['year'];
      $saleDateArray['month']=$saleDate['month'];
      $saleDateArray['day']=$saleDate['day'];
      
      $orderCode=$this->request->data['Order']['order_code'];
      $namedSales=$this->Order->find('all',[
        'conditions'=>[
          'order_code'=>$orderCode,
          'stock_movement_type_id'=>MOVEMENT_SALE,
        ],
      ]);
      if (count($namedSales)>0){
        $this->Session->setFlash(__('Ya existe una venta con el mismo código!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif ($saleDateString > date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de venta no puede estar en el futuro!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Order']['save_allowed'] == 0){
        $this->Session->setFlash('No se permite guardar esta venta de crédito!  Si está el gerente, marca la casilla de permitir guardar venta.  No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif ($saleDateString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($clientId) && empty($clientName)){
        $this->Session->setFlash('Se debe registrar el nombre del cliente.  No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Order']['vendor_user_id'])){
        //pr($this->request->data['Order']);
        $this->Session->setFlash('Se debe registrar el vendedor.  No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif (empty($clientId) && empty($clientPhone) && empty($clientMail)){
        $this->Session->setFlash('Se debe registrar el teléfono o el correo electrónico del cliente.  No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Order']['third_party_id']==0){
        $this->Session->setFlash(__('Se debe seleccionar el cliente para la venta!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      elseif (!$this->request->data['Order']['client_generic'] && empty($this->request->data['Order']['client_type_id'])){
        $this->Session->setFlash('Se debe registrar el tipo de cliente.  No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif (!$this->request->data['Order']['client_generic'] && empty($this->request->data['Order']['zone_id'])){
        $this->Session->setFlash('Se debe registrar la zona del cliente.  No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif (!$boolMultiplicationOK){
        $this->Session->setFlash($multiplicationErrorMessage.'No se guardó la venta.', 'default',['class' => 'error-message']);
      } 
      elseif (!$productRawMaterialPresent){
        $this->Session->setFlash($errorMessage.'No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif (abs($sumProductTotals-$this->request->data['Order']['price_subtotal']) > 0.01){
        $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$sumProductTotals.' pero el total calculado es '.$this->request->data['Order']['price_subtotal'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      //elseif ($this->request->data['Invoice']['currency_id'] == CURRENCY_USD && (abs($this->request->data['Invoice']['sub_total_price']-$sumProductTotals/$this->request->data['Order']['exchange_rate']) > 1)){
      //  echo "el subtotal de la factura ".$this->request->data['Invoice']['sub_total_price']." no iguala el precio sumado de los productos ".($sumProductTotals/$this->request->data['Order']['exchange_rate'])." en base a una tasa de cambio ".$this->request->data['Order']['exchange_rate'];
      //  throw new Exception();
      //}
      //elseif ($this->request->data['Invoice']['currency_id'] != CURRENCY_USD && (abs($this->request->data['Invoice']['sub_total_price']-$sumProductTotals) > 1)){
      //  echo "el subtotal de la factura ".$this->request->data['Invoice']['sub_total_price']." no iguala el precio sumado de los productos ".$sumProductTotals;
      //  throw new Exception();
      //}
      elseif (abs($this->request->data['Order']['price_total']-$this->request->data['Order']['price_iva']-$this->request->data['Order']['price_subtotal'])>0.01){
        $this->Session->setFlash('La suma del subtotal '.$this->request->data['Order']['price_subtotal'].' y el IVA '.$this->request->data['Order']['price_iva'].' no igualan el precio total '.$this->request->data['Order']['price_total'].', la diferencia es de '.(abs($this->request->data['Order']['price_total']-$this->request->data['Order']['price_iva']-$this->request->data['Order']['price_subtotal'])).'.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Order']['price_total'])){
        $this->Session->setFlash(__('El total de la orden de venta tiene que ser mayor que cero.  No se guardó la orden.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['Invoice']['bool_retention'] && strlen($this->request->data['Invoice']['retention_number'])==0){
        $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['Invoice']['bool_retention'] && abs(0.02*$this->request->data['Order']['price_subtotal']-$this->request->data['Order']['retention_amount']) > 0.01){
        $this->Session->setFlash(__('La retención debería igualar el 2% del subtotal de la venta!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['Order']['bool_delivery'] && empty($this->request->data['Order']['delivery_address'])){
        $this->Session->setFlash('Se indicó que.la factura se debe entregar a domicilio pero no se indicó la dirección de entrega.  La dirección de entrega se debe registrar.', 'default',['class' => 'error-message']);
      }        
      elseif (!$boolProductPricesRegistered && $userRoleId != ROLE_ADMIN){
        $this->Session->setFlash($productPriceWarning.'No se guardó la  venta.', 'default',['class' => 'error-message']);
      }
      elseif ($boolProductPriceLessThanDefaultPrice && $userRoleId != ROLE_ADMIN){
        $this->Session->setFlash($productPriceLessThanDefaultPriceError.'No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif (!$boolProductPriceRepresentsBenefit){
        $this->Session->setFlash($productPriceBenefitError.'No se guardó la venta.', 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Invoice']['bool_annulled']){
        $datasource=$this->Order->getDataSource();
        $datasource->begin();
        try {
          //pr($this->request->data);					
          $orderData=[
            'Order'=>[
              'stock_movement_type_id'=>MOVEMENT_SALE,
              'order_date'=>$this->request->data['Order']['order_date'],
              'order_code'=>$this->request->data['Order']['order_code'],
              'third_party_id'=>$this->request->data['Order']['third_party_id'],
              'bool_annulled'=>true,
              'warehouse_id'=>$warehouseId,
              'total_price'=>0,
            ],
          ];
          $this->Order->create();
          if (!$this->Order->save($orderData)) {
            echo "Problema guardando el orden de salida";
            pr($this->validateErrors($this->Order));
            throw new Exception();
          }
          $orderId=$this->Order->id;
          
          $invoiceData=[
            'Invoice'=>[
              'order_id'=>$orderId,
              'invoice_code'=>$this->request->data['Order']['order_code'],
              'invoice_date'=>$this->request->data['Order']['order_date'],
              'bool_annulled'=>true,
              'warehouse_id'=>$warehouseId,
              'client_id'=>$this->request->data['Order']['third_party_id'],
              'sub_total_price'=>0,
              'iva_price'=>0,
              'total_price'=>0,
              'currency_id'=>CURRENCY_CS,
            ],
          ];            
          $this->Invoice->create();
          if (!$this->Invoice->save($invoiceData)) {
            echo "Problema guardando la factura";
            pr($this->validateErrors($this->Invoice));
            throw new Exception();
          }
          
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),"Se registró una venta anulada con número ".$this->request->data['Order']['order_code']);
          $this->Session->setFlash(__('Se guardó la venta '.$this->request->data['Order']['order_code'].' anulada.'),'default',['class' => 'success'],'default',['class' => 'success']);
          if (!empty($this->request->data['saveAndNew'])){
            return $this->redirect(['action' => 'crearVenta']);
          }
          else {
            return $this->redirect(['action' => 'imprimirVenta',$orderId]);  
          }          
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }
      }					
      elseif ($this->request->data['Order']['price_total']==0){
        $this->Session->setFlash(__('El precio total no puede ser cero para una venta que no está anulada!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      else if (!$this->request->data['Invoice']['bool_credit']&&$this->request->data['Invoice']['cashbox_accounting_code_id']==0){
        $this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una factura de contado!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['Invoice']['bool_retention'] && strlen($this->request->data['Invoice']['retention_number'])==0){
        $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',['class' => 'error-message']);
      }	
      else {
        // before moving into the selling part, perform the check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
        $saleItemsOK=true;
        $exceedingItems="";
        
        $productMultiplicationOk=true;
        $productMultiplicationWarning="";
        
        $productTotalSumBasedOnProductTotals=0;
        
        $productCount=0;
        $products=[];
        foreach ($this->request->data['Product'] as $product){
          //pr($product);
          // keep track of number of rows so that in case of an error jquery displays correct number of rows again
          if ($product['product_id']>0){
            $productCount++;
          }
          if ($product['product_quantity']>0 && $product['product_id']>0){
             $products[]=$product;
            $quantityEntered=$product['product_quantity'];
            $productId = $product['product_id'];
            $productionResultCodeId = $product['production_result_code_id'];
            $rawMaterialId = $product['raw_material_id'];
            
            $productName=$this->Product->getProductName($product['product_id']);
            $productTypeId=$this->Product->getProductTypeId($product['product_id']);
            if ($productTypeId != PRODUCT_TYPE_SERVICE){  
              if ($this->Product->getProductCategoryId($productId) == CATEGORY_PRODUCED){
                // 20210429 handle sale from warehouse las colinas temporarily
                // as selling from warehouse colinas (WAREHOUSE_INJECTION) is now allowed, we check if the product is present in the selected warehouse
                // if the product is not asssocited with warehouseId, verify if it is associated with WAREHOUSE_INJECTION and if so, get the quantity from there
                if ($this->WarehouseProduct->hasWarehouse($productId,$warehouseId) && $warehouseId != WAREHOUSE_INJECTION){
                  $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($productId,$rawMaterialId,$productionResultCodeId,$saleDateString,$warehouseId,true);
                }
                elseif ($this->WarehouseProduct->hasWarehouse($productId,WAREHOUSE_INJECTION)){
                  $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productId,$saleDateString,WAREHOUSE_INJECTION,true);
                }
              }
              else {
                if ($this->WarehouseProduct->hasWarehouse($productId,$warehouseId)){
                  $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productId,$saleDateString,$warehouseId,true);
                }
                elseif ($this->WarehouseProduct->hasWarehouse($productId,WAREHOUSE_INJECTION)){
                  $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productId,$saleDateString,WAREHOUSE_INJECTION,true);  
                }
              }
              if ($quantityEntered>$quantityInStock){
                $saleItemsOK='0';
                $exceedingItems.=__("Para producto ".$productName." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
              }
            }
          }
        }
        $creditBlocked=true;
        $creditBlockMessage="";
        $unpaidBlocked=true;
        $unpaidBlockMessage="";
        
        $boolCreditAuthorized='0';
        if ($salesOrderId > 0){
          $salesOrder=$this->SalesOrder->find('first',[
            'conditions'=>[
              'SalesOrder.id'=>$salesOrderId,
            ],
            'recursive'=>-1,
          ]);
          if (!empty($salesOrder)){
            if (in_array($salesOrder['SalesOrder']['credit_authorization_user_id'],$adminUserIds) && $salesOrder['SalesOrder']['bool_credit']){
              $boolCreditAuthorized=true;
            }
          }
        } 
        
        if(!$this->request->data['Invoice']['bool_credit']){
          $creditBlocked='0';
          $unpaidBlocked='0';
        }
        else {
          $clientCreditStatus=$this->ThirdParty->getClientCreditStatus($this->request->data['Order']['third_party_id']);
          //pr($clientCreditStatus);
          $creditUsedBeforeInvoice=$clientCreditStatus['ThirdParty']['pending_payment'];
          $creditUsedWithThisInvoice=$this->request->data['Order']['price_total'];
          if($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
            $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($saleDateString);
            $creditUsedWithThisInvoice=round($creditUsedWithThisInvoice*$applicableExchangeRate,2);
          }
          $creditUsedAfterInvoice= $creditUsedBeforeInvoice + $creditUsedWithThisInvoice;
          $creditLimit=$clientCreditStatus['ThirdParty']['credit_amount'];
          
          if (($creditLimit < $creditUsedAfterInvoice) && $canApplyCredit!=1){
            $creditBlockMessage.="El cliente ".$clientCreditStatus['ThirdParty']['company_name']." tiene un límite de crédito de ".$creditLimit." y ya tiene pagos pendientes para un total de C$ ".$creditUsedBeforeInvoice.".  Con el total de esta factura (C$ ".$creditUsedWithThisInvoice.") el monto total que se debe (C$ ".$creditUsedAfterInvoice.") excede el límite de crédito.";
          }
          else {
            $creditBlocked='0';
          }
          
          $pendingInvoices=$this->Invoice->find('all',[
            'fields'=>[
              'Invoice.id','Invoice.invoice_code',
              'Invoice.total_price','Invoice.currency_id',
              'Invoice.invoice_date','Invoice.due_date',
              'Invoice.client_id',
              'Currency.abbreviation','Currency.id',
              'Invoice.order_id',
            ],
            'conditions'=>[
              'Invoice.due_date <'=>date('Y-m-d'),
              'Invoice.bool_annulled'=>'0',
              'Invoice.bool_paid'=>'0',
              'Invoice.client_id'=>$this->request->data['Order']['third_party_id'],
            ],
            'order'=>'Invoice.invoice_date ASC',
          ]);
          
          if (!empty($pendingInvoices)){
            $unpaidBlockMessage="El cliente ".$clientCreditStatus['ThirdParty']['company_name']." tiene facturas de crédito que vencieron: ";
            $counter=0;
            foreach ($pendingInvoices as $pendingInvoice){
              $unpaidBlockMessage.=$pendingInvoice['Invoice']['invoice_code']." (".($pendingInvoice['Invoice']['currency_id'] == CURRENCY_USD?"US$":"C$")." ".$pendingInvoice['Invoice']['total_price'].")";
              if ($counter<count($pendingInvoices)-1){
                $unpaidBlockMessage.=",";
              }
              $counter++;
            } 
            $unpaidBlockMessage.=". ";
            //$unpaidBlockMessage="Revise el ".$this->Html->link('estado de crédito del cliente',['controller'=>'invoices','action'=>'verFacturasPorCobrar',$this->request->data['Order']['third_party_id']])."!";
          }
          else {
            $unpaidBlocked='0';  
          }
          
          if ($userRoleId == ROLE_ADMIN || $canApplyCredit==1){
            $creditBlocked='0';
            $unpaidBlocked='0';  
          }
        }
        if (!empty($exceedingItems)){
          $exceedingItems.='Por favor corriga e intente de nuevo';
        }					
        if (!$saleItemsOK){
          $_SESSION['saleRequestData']=$this->request->data;
          
          $aco_name="Orders/manipularVenta";		
          $bool_order_manipularventa_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
          $salesOrderFromColinas='0';
          if ($salesOrderId > 0){
            $salesOrderFromColinas=$this->SalesOrder->getSalesOrderWarehouse($salesOrderId)== WAREHOUSE_INJECTION;
          }
          
          if ($bool_order_manipularventa_permission && !$salesOrderFromColinas){
            echo 'redirigiendo a reclasificación automática<br/>';  
            return $this->redirect(['action' => 'manipularVenta']);
          }
          //echo __('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems.'<br/>';
          $this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
        }
        elseif (($creditBlocked || $unpaidBlocked) && $userRoleId != ROLE_ADMIN && !$boolCreditAuthorized){
          $this->Session->setFlash($creditBlockMessage.$unpaidBlockMessage.'  No se guardó la factura de crédito.', 'default',['class' => 'error-message']);
        }
        else{
          $totalPriceProducts=0;
          
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          try {
            $currency_id=$this->request->data['Invoice']['currency_id'];
          
            $retention_invoice=$this->request->data['Order']['retention_amount'];
            $sub_total_invoice=$this->request->data['Order']['price_subtotal'];
            $ivaInvoice=$this->request->data['Order']['price_iva'];
            $totalInvoice=$this->request->data['Order']['price_total'];
        
            // if all products are in stock, proceed with the sale 
            $this->Order->create();
            $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
            // ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
            if ($currency_id==CURRENCY_USD){
              $this->request->data['Order']['total_price']=$sub_total_invoice*$this->request->data['Order']['exchange_rate'];
            }
            else {
              $this->request->data['Order']['total_price']=$sub_total_invoice;
            }
            //pr($this->request->data);
            if (!$this->Order->save($this->request->data)) {
              echo "Problema guardando la salida";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            }
          
            $orderId=$this->Order->id;
            $orderCode=$this->request->data['Order']['order_code'];
          
            $this->Invoice->create();
            $this->request->data['Invoice']['order_id']=$orderId;
            $this->request->data['Invoice']['sales_order_id']=$salesOrderId;
            $this->request->data['Invoice']['warehouse_id']=$warehouseId;
            $this->request->data['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
            $this->request->data['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
            $this->request->data['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
            
            $this->request->data['Invoice']['sub_total_price']=$this->request->data['Order']['price_subtotal'];
            $this->request->data['Invoice']['iva_price']=$this->request->data['Order']['price_iva'];
            $this->request->data['Invoice']['total_price']=$this->request->data['Order']['price_total'];
            $this->request->data['Invoice']['retention_amount']=$this->request->data['Order']['retention_amount'];
            
            if ($this->request->data['Invoice']['bool_credit']){
              $this->request->data['Invoice']['bool_retention']='0';
              $this->request->data['Invoice']['retention_amount']=0;
              $this->request->data['Invoice']['retention_number']="";
            }
            else {
              $this->request->data['Invoice']['bool_paid']=true;
            }
        
            if (!$this->Invoice->save($this->request->data)) {
              echo "Problema guardando la factura";
              pr($this->validateErrors($this->Invoice));
              throw new Exception();
            }
            
            $invoiceId=$this->Invoice->id;
            
            if ($salesOrderId > 0){
              $this->SalesOrder->id=$salesOrderId;
              // keep the salesorder delivery state consistent with the order delivery state
              $salesOrderArray=[
                'SalesOrder'=>[
                  'id'=>$salesOrderId,
                  'bool_invoice'=>true,
                  'invoice_id'=>$invoiceId,
                  'bool_delivery'=>$this->request->data['Order']['bool_delivery'],
                ]
              ];
              if (!$this->SalesOrder->save($salesOrderArray)) {
                echo "Problema actualizando la orden de venta";
                pr($this->validateErrors($this->SalesOrder));
                throw new Exception();
              }
            }
            
            if ($this->request->data['Order']['delivery_id'] > 0){
              $this->loadModel('Delivery');
              $deliveryArray=[
                'Delivery'=>[
                  'id'=>$this->request->data['Order']['delivery_id'],
                  'order_id'=>$orderId,
                ],
              ];
              $this->Delivery->id=$this->request->data['Order']['delivery_id'];  
              if (!$this->Delivery->save($deliveryArray)) {
                echo "Problema actualizando la entrega a domicilio";
                pr($this->validateErrors($this->Delivery));
                throw new Exception();
              }              
            }
            
            if ($currency_id==CURRENCY_USD){
              $this->loadModel('ExchangeRate');
              $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateString);
              //pr($applicableExchangeRate);
              $retention_CS=round($retention_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
              $sub_total_CS=round($sub_total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
              $ivaCS=round($ivaInvoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
              $totalCS=round($totalInvoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
            }
            else {
              $retention_CS=$retention_invoice;
              $sub_total_CS=$sub_total_invoice;
              $ivaCS=$ivaInvoice;
              $totalCS=$totalInvoice;
            }
            $this->AccountingCode->recursive=-1;
            if ($this->request->data['Invoice']['bool_credit']){
              $clientId=$this->request->data['Order']['third_party_id'];
              $this->loadModel('ThirdParty');
              $this->ThirdParty->recursive=-1;
              $thisClient=$this->ThirdParty->getClientById($clientId);
            
              $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
              $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
              $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
              $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
              $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
              $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$ivaCS;
              $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
              
              if (empty($thisClient['ThirdParty']['accounting_code_id'])){
                $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
                  ),
                ));
              }
              else {								
                $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>$thisClient['ThirdParty']['accounting_code_id'],
                  ),
                ));
              }
              $accountingRegisterData['AccountingMovement'][0]['concept']="A cobrar Venta ".$orderCode;
              $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS;
              
              $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
              $accountingCode=$this->AccountingCode->find('first',array(
                'conditions'=>array(
                  'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                ),
              ));
              $accountingRegisterData['AccountingMovement'][1]['concept']="Ingresos Venta ".$orderCode;
              $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
              
              if ($this->request->data['Invoice']['bool_iva']){
                $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$ivaCS;
              }
              
              //pr($accountingRegisterData);
              $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
              $this->recordUserAction($this->AccountingRegister->id,"add",null);
          
              $AccountingRegisterInvoiceData=[];
              $AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
              $AccountingRegisterInvoiceData['invoice_id']=$invoiceId;
              $this->AccountingRegisterInvoice->create();
              if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                pr($this->validateErrors($this->AccountingRegisterInvoice));
                echo "problema al guardar el lazo entre asiento contable y factura";
                throw new Exception();
              }
              //echo "link accounting register sale saved<br/>";					
            }
            else {
              $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
              $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
              $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
              $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
              $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
              $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$ivaCS;
              $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
              
              if (!$this->request->data['Invoice']['bool_retention']){
                $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS;
              }
              else {
                // with retention
                $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS-$retention_CS;
              }
              
              $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
              $accountingCode=$this->AccountingCode->find('first',array(
                'conditions'=>array(
                  'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
                ),
              ));
              $accountingRegisterData['AccountingMovement'][1]['concept']="Subtotal Venta ".$orderCode;
              $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
              
              if ($this->request->data['Invoice']['bool_iva']){
                $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR,
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$ivaCS;
              }
              if ($this->request->data['Invoice']['bool_retention']){
                $accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
                $accountingCode=$this->AccountingCode->find('first',array(
                  'conditions'=>array(
                    'AccountingCode.id'=>ACCOUNTING_CODE_RETENCIONES_POR_COBRAR,
                  ),
                ));
                $accountingRegisterData['AccountingMovement'][3]['concept']="Retención Venta ".$orderCode;
                $accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][3]['debit_amount']=$retention_CS;
              }
              
              //pr($accountingRegisterData);
              $accountingRegisterId=$this->saveAccountingRegisterData($accountingRegisterData,true);
              $this->recordUserAction($this->AccountingRegister->id,"add",null);
              //echo "accounting register saved for cuentas cobrar clientes<br/>";
          
              $AccountingRegisterInvoiceData=[];
              $AccountingRegisterInvoiceData['accounting_register_id']=$accountingRegisterId;
              $AccountingRegisterInvoiceData['invoice_id']=$invoiceId;
              $this->AccountingRegisterInvoice->create();
              if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                pr($this->validateErrors($this->AccountingRegisterInvoice));
                echo "problema al guardar el lazo entre asiento contable y factura";
                throw new Exception();
              }
              //echo "link accounting register sale saved<br/>";	
            }
          
            foreach ($products as $product){
              // four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
              //pr($product);
              
              // load the product request data into variables
              $productId = $product['product_id'];
              $productCategoryId = $this->Product->getProductCategoryId($productId);
              $productionResultCodeId =0;
              $rawMaterialId=0;
              
              if ($productCategoryId==CATEGORY_PRODUCED && $warehouseId != WAREHOUSE_INJECTION){
                $productionResultCodeId = $product['production_result_code_id'];
                $rawMaterialId = $product['raw_material_id'];
              }
              if (array_key_exists('service_unit_cost',$product)){
                $service_unit_cost=$product['service_unit_cost'];
              }
              else {
                $service_unit_cost=0;
              }
              $productUnitPrice=$product['product_unit_price'];
              $productQuantity = $product['product_quantity'];
              
              if ($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
                $productUnitPrice*=$this->request->data['Order']['exchange_rate'];
              }
              
              // get the related product data
              $this->Product->recursive=-1;
              $linkedProduct=$this->Product->getProductById($productId);
              $productName=$linkedProduct['Product']['name'];
              
              if ($linkedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                // STEP 1: SAVE THE STOCK ITEM(S)
                // first prepare the materials that will be taken out of stock
                if ($productCategoryId==CATEGORY_PRODUCED){
                  if ($this->WarehouseProduct->hasWarehouse($productId,$warehouseId) && $warehouseId != WAREHOUSE_INJECTION){
                    $usedMaterials= $this->StockItem->getFinishedMaterialsForSale($productId,$productionResultCodeId,$productQuantity,$rawMaterialId,$saleDateString,$warehouseId);		
                  }
                  elseif ($this->WarehouseProduct->hasWarehouse($productId,WAREHOUSE_INJECTION)){
                    $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$saleDateString,WAREHOUSE_INJECTION);		  
                  }  
                }
                else {
                  if ($this->WarehouseProduct->hasWarehouse($productId,$warehouseId)){
                    $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$saleDateString,$warehouseId);		
                  }
                  elseif ($this->WarehouseProduct->hasWarehouse($productId,WAREHOUSE_INJECTION)){
                    $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$saleDateString,WAREHOUSE_INJECTION);		
                  }
                }
                //pr($usedMaterials);
              
                for ($k=0;$k<count($usedMaterials);$k++){
                  $materialUsed=$usedMaterials[$k];
                  $stockItemId=$materialUsed['id'];
                  $quantity_present=$materialUsed['quantity_present'];
                  $quantity_used=$materialUsed['quantity_used'];
                  $quantity_remaining=$materialUsed['quantity_remaining'];
                  if (!$this->StockItem->exists($stockItemId)) {
                    throw new NotFoundException(__('Invalid StockItem'));
                  }
                  //$linkedStockItem=$this->StockItem->read(null,$stockItemId);
                  $this->StockItem->recursive=-1;
                  $linkedStockItem=$this->StockItem->getStockItemById($stockItemId);
                  $message="Se vendió lote ".$productName." (Cantidad:".$quantity_used.") para Venta ".$orderCode;
                  
                  $stockItemData=[  
                    'id'=>$stockItemId,
                    'description'=>$linkedStockItem['StockItem']['description']."|".$message,
                    'remaining_quantity'=>$quantity_remaining,
                  ];
                    
                  // notice that no new stockitem is created because we are taking from an already existing one
                  if (!$this->StockItem->save($stockItemData)) {
                    echo "problema al guardar el lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  // STEP 2: SAVE THE STOCK MOVEMENT
                  $message="Se vendió ".$productName." (Cantidad:".$quantity_used.", total para venta:".$productQuantity.") para Venta ".$orderCode;
                  $stockMovementData=[
                    'movement_date'=>$saleDate,
                    'bool_input'=>'0',
                    'name'=>$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName,
                    'description'=>$message,
                    'order_id'=>$orderId,
                    'stockitem_id'=>$stockItemId,
                    'product_id'=>$productId,
                    'product_quantity'=>$quantity_used,
                    'product_unit_price'=>$productUnitPrice,
                    'product_total_price'=>$productUnitPrice*$quantity_used,
                    'service_unit_cost'=>0,
                    'service_total_cost'=>0,
                    'production_result_code_id'=>$productionResultCodeId,
                  ];
                  $totalPriceProducts+=$stockMovementData['product_total_price'];
                  
                  $this->StockMovement->create();
                  if (!$this->StockMovement->save($stockMovementData)) {
                    echo "problema al guardar el movimiento de lote";
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                
                  // STEP 3: SAVE THE STOCK ITEM LOG
                  $this->recreateStockItemLogs($stockItemId);
                      
                  // STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                  $this->recordUserActivity($this->Session->read('User.username'),$message);
                }
              
              }
              else {
                $message="Se vendió ".$productName." (Cantidad:".$productQuantity.", total para venta:".$productQuantity.") para Venta ".$orderCode;
                $stockMovementData=[
                  'movement_date'=>$saleDate,
                  'bool_input'=>'0',
                  'name'=>$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName,
                  'description'=>$message,
                  'order_id'=>$orderId,
                  'stockitem_id'=>0,
                  'product_id'=>$productId,
                  'product_quantity'=>$productQuantity,
                  'product_unit_price'=>$productUnitPrice,
                  'product_total_price'=>$productUnitPrice*$productQuantity,
                  'service_unit_cost'=>$service_unit_cost,
                  'service_total_cost'=>$service_unit_cost*$productQuantity,
                  'production_result_code_id'=>$productionResultCodeId,
                ];
                
                $totalPriceProducts+=$stockMovementData['product_total_price'];
                
                $this->StockMovement->create();
                if (!$this->StockMovement->save($stockMovementData)) {
                  echo "problema al guardar el movimiento de lote";
                  pr($this->validateErrors($this->StockMovement));
                  throw new Exception();
                }
              
                $this->recordUserActivity($this->Session->read('User.username'),$message);
              }
            }
            
            //if (!$this->request->data['Order']['client_generic']){
            if (!in_array($this->request->data['Order']['third_party_id'],$genericClientIds)){  
              $orderClientData=[
                'id'=>$this->request->data['Order']['third_party_id'],
                'phone'=>$this->request->data['Order']['client_phone'],
                'email'=>$this->request->data['Order']['client_email'],
                'address'=>$this->request->data['Order']['client_address'],
                'ruc_number'=>'',
                'client_type_id'=>$this->request->data['Order']['client_type_id'],
                'zone_id'=>$this->request->data['Order']['zone_id'],
                
              ];
              if (!$this->ThirdParty->updateClientDataConditionally($orderClientData,$userRoleId)['success']){
                echo "Problema actualizando los datos del cliente";
                throw new Exception();
              }
            }
          
            if ($this->request->data['Invoice']['currency_id'] == CURRENCY_USD){
              if (abs($this->request->data['Invoice']['sub_total_price']-$totalPriceProducts/$this->request->data['Order']['exchange_rate'])>1){
                echo "el subtotal ".$this->request->data['Invoice']['sub_total_price']." no iguala el precio sumado de los productos ".($totalPriceProducts/$this->request->data['Order']['exchange_rate']);
                throw new Exception();
              }
            }
            else {
              if (abs($this->request->data['Invoice']['sub_total_price']-$totalPriceProducts)>1){
                echo "el subtotal ".$this->request->data['Invoice']['sub_total_price']." no iguala el precio sumado de los productos ".$totalPriceProducts;
                throw new Exception();
              }
            }
           
            $datasource->commit();
            $this->recordUserAction($this->Order->id,"add",null);
            // SAVE THE USERLOG FOR THE PURCHASE
            $this->recordUserActivity($this->Session->read('User.username'),"Sale registered with invoice code ".$this->request->data['Order']['order_code']);
            
            $flashMessage='Se guardó la venta.  ';
            if (!$boolProductPricesRegistered){
              $flashMessage.=$productPriceWarning;
            }
            $flashMessage.= $creditBlockMessage.$unpaidBlockMessage;
            $this->Session->setFlash($flashMessage,'default',['class' => 'success']);
            
            //return $this->redirect(array('action' => 'resumenVentasRemisiones'));
            if (!empty($this->request->data['saveAndNew'])){
              return $this->redirect(['action' => 'crearVenta']);
            }
            else {
              return $this->redirect(['action' => 'imprimirVenta',$orderId]);  
            } 
            // on the view page the print button will be present; it should display the invoice just as it has been made out, this is then sent to javascript
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
          }
        }
      }
    }
		
    $this->set(compact('requestProducts'));
    $this->set(compact('boolInitialLoad'));
		
    $this->set(compact('inventoryDisplayOptionId'));
		
		$this->set(compact('salesOrderId'));
    $this->set(compact('clientId'));
    $this->set(compact('currencyId'));
    
    $this->set(compact('vendorUserId'));
    $this->set(compact('recordUserId'));
    $this->set(compact('creditAuthorizationUserId'));
    
    //$this->set(compact('driverUserId'));
    //$this->set(compact('vehicleId'));
    $this->set(compact('boolDelivery'));
    
    $thirdParties = $this->ThirdParty->getActiveClientList();
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		
    $currencies = $this->SalesOrder->Currency->find('list');
		$this->set(compact('currencies'));  
    
    $exchangeRateOrder=$this->ExchangeRate->getApplicableExchangeRateValue($orderDate);
		$this->set(compact('exchangeRateOrder'));
    
    $availableProductsForSale=$this->Product->getAvailableProductsForSale($orderDate,$warehouseId,true);
    $products=$availableProductsForSale['products'];
    //pr($products);
    if ($warehouseId != WAREHOUSE_INJECTION){
      $availableInjectionProductsForSale=$this->Product->getAvailableProductsForSale($orderDate,WAREHOUSE_INJECTION,false);
      $injectionProducts=$availableInjectionProductsForSale['products'];
      //pr($injectionProducts);
      $products+=$injectionProducts;
    }
    //pr($products);
    $rawMaterialsAvailablePerFinishedProduct=$availableProductsForSale['rawMaterialsAvailablePerFinishedProduct'];
    $rawMaterials=$availableProductsForSale['rawMaterials'];
    $this->set(compact('products'));
    $this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    $this->set(compact('rawMaterials'));
    
    //if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      // TEMPORARY FIX COLINAS
      $users=$this->User->getActiveVendorAdminUserList($warehouseId);
      
      $users=$this->User->getActiveVendorAdminUserList();
    //}
    //elseif ($canSeeAllVendors) {
    //  $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    //}
    //else {
    //  $users=$this->User->getActiveUserList($loggedUserId);
    //}
    $this->set(compact('users'));
    //pr($users);
    
	$productionResultCodes=$this->ProductionResultCode->find('list',[
      'conditions'=>['ProductionResultCode.id'=>PRODUCTION_RESULT_CODE_A]
    ]);
	
    $this->set(compact('productionResultCodes'));
    
		//if (!empty($inventoryDisplayOptionId)){
      //echo "inventory display option id is ".$inventoryDisplayOptionId."<br/>";
			$productCategoryId=CATEGORY_PRODUCED;
			$productTypeIds=$this->ProductType->find('list',[
				'fields'=>['ProductType.id'],
				'conditions'=>['ProductType.product_category_id'=>$productCategoryId],
			]);
      $finishedMaterialsInventory =[];

			$finishedMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
	  
		if ($warehouseId != WAREHOUSE_INJECTION){
        $injectionMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,WAREHOUSE_INJECTION);
        $this->set(compact('injectionMaterialsInventory'));
      }
          
      //pr($finishedMaterialsInventory);
			$productCategoryId=CATEGORY_OTHER;
			$productTypeIds=$this->ProductType->find('list',[
				'fields'=>['ProductType.id'],
				'conditions'=>['ProductType.product_category_id'=>$productCategoryId]
			]);
			$otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
      //pr($otherMaterialsInventory);
      
			$productCategoryId=CATEGORY_RAW;
			$productTypeIds=$this->ProductType->find('list',[
				'fields'=>['ProductType.id'],
				'conditions'=>['ProductType.product_category_id'=>$productCategoryId],
			]);
			$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
		//}
    
		$cashboxAccountingCode=$this->AccountingCode->find('first',[
			'fields'=>['AccountingCode.lft','AccountingCode.rght'],
			'conditions'=>[
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			],
		]);
		
		$accountingCodes=$this->AccountingCode->find('list',[
			'fields'=>'AccountingCode.fullname',
			'conditions'=>[
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			],
			'order'=>'AccountingCode.code',
		]);
		// calculate the code for the new service sheet
		$newInvoiceCode="";
		
		$this->set(compact('thirdParties', 'stockMovementTypes','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','currencies','accountingCodes','newInvoiceCode'));
		
    $otherProducts=$this->Product->find('list',[
      'fields'=>'Product.id',
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_SERVICE]
    ]);
    $otherProducts=array_values($otherProducts);
    $this->set(compact('otherProducts'));
    
    //echo "warehouse id is ".$warehouseId."<br/>";
    //$salesOrders=$this->SalesOrder->getPendingSalesOrders($warehouseId);
    $salesOrders=$this->SalesOrder->getPendingSalesOrders();
    $this->set(compact('salesOrders'));
    //pr($salesOrders);
    
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
    
    //$clientRucNumbers=$this->ThirdParty->getRucNumbersPerClient();
    //$this->set(compact('clientRucNumbers'));
    
    //$driverUsers=$this->User->getActiveUsersForRole(ROLE_DRIVER,$warehouseId);
    //$this->set(compact('driverUsers'));
    //$vehicles=$this->Vehicle->getVehicleList($warehouseId);
    //$this->set(compact('vehicles'));
    
    $ownerUsersPerClient=$this->ThirdParty->getClientOwnerList();
    $this->set(compact('ownerUsersPerClient'));
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
    
	}
	
  public function manipularRemision(){
		$requestData=$_SESSION['remissionRequestData'];
		
		$this->LoadModel('Product');
		$this->LoadModel('ProductionResultCode');
		$this->LoadModel('StockItem');
		$this->LoadModel('StockItemLog');
		
		$this->Product->recursive=-1;
		$this->ProductionResultCode->recursive=-1;
		$this->StockItem->recursive=-1;
		$this->StockItemLog->recursive=-1;
		
		$this->loadModel('ProductType');
		$this->loadModel('StockMovement');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ClosingDate');
		$this->loadModel('CashReceipt');
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterCashReceipt');
    
    $this->loadModel('Constant');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $roleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId','roleId'));
    
    $warehouseId=$requestData['Order']['warehouse_id'];
    
    $manipulationMaxValueConstant=$this->Constant->find('first',[
      'conditions'=>[
        'Constant.constant'=>'MAXIMO_RECLASIFICACION_REMISION'
      ]
    ]);
    if (!defined('MANIPULATION_MAX_REMISION')){
      define('MANIPULATION_MAX_REMISION',$manipulationMaxValueConstant['Constant']['value']);
    }
	
		$orderDateAsString=$this->Order->deconstruct('order_date',$requestData['Order']['order_date']);
		$warehouseId=$requestData['Order']['warehouse_id'];
		
		$productionResultCodes=$this->ProductionResultCode->find('list');
		$this->set(compact('productionResultCodes'));
		
		$requestedProducts=[];
		
		$productSummary="";
		$boolReclassificationPossible=true;
		$reasonForNoReclassificationPossible="";
    $reclassificationComment="";
		if (!empty($requestData['Product'])){
			foreach ($requestData['Product'] as $product){
				if (!empty($product['product_quantity'])){
					//pr($product);
					$relatedProduct=$this->Product->find('first',[
						'conditions'=>[
							'Product.id'=>$product['product_id'],
						],
						'contain'=>['ProductType',],
					]);
					//pr($relatedProduct);
					$requestedProductInfo=[];
					$requestedProductInfo['requested_quantity']=$product['product_quantity'];
					$requestedProductInfo['Product']=$relatedProduct['Product'];
					$requestedProductInfo['ProductType']=$relatedProduct['ProductType'];
					$requestedProductInfo['ProductRequest']=$product;
					
					switch ($relatedProduct['ProductType']['product_category_id']){
						case CATEGORY_PRODUCED:
							$relatedRawMaterial=$this->Product->find('first',[
								'conditions'=>[
									'Product.id'=>$product['raw_material_id'],
								],							
							]);	
							$requestedProductInfo['RawMaterial']=$relatedRawMaterial['Product'];
							// ONLY BOTTLES OF TYPE B CAN BE RECLASSIFIED TO
							if ($relatedProduct['ProductType']['id']==PRODUCT_TYPE_BOTTLE && $product['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
								$quantityBottleQualityBInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($product['product_id'],$product['raw_material_id'],PRODUCTION_RESULT_CODE_B,$orderDateAsString,$warehouseId,true);
								$quantityBottleQualityCInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($product['product_id'],$product['raw_material_id'],PRODUCTION_RESULT_CODE_C,$orderDateAsString,$warehouseId,true);
								if ($product['product_quantity']>$quantityBottleQualityBInStock){
									$productSummary.="Para producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad A la cantidad requerida ".$product['product_quantity']." NO está disponible en bodega en esta fecha.<br/>";	
									// INVESTIGATE IF IT CAN BE DONE 
									//echo $productSummary;
									
									if ($product['product_quantity']>($quantityBottleQualityBInStock+MANIPULATION_MAX_REMISION)){
										$boolReclassificationPossible='0';
										$reasonForNoReclassificationPossible="Se requieren más que ".MANIPULATION_MAX_REMISION." unidades adicionales de  ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad B para realizar la remisión, no se permite reclasificación automática.<br/>";
									}
									elseif ($product['product_quantity']>$quantityBottleQualityBInStock+$quantityBottleQualityCInStock){
										$boolReclassificationPossible='0';
										$reasonForNoReclassificationPossible="No hay suficiente productos en bodega para realizar la remisión de ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad C, incluso reclasificando los productos de calidad C.<br/>";	
									}
									else {
										if ($product['product_quantity']<=$quantityBottleQualityBInStock+$quantityBottleQualityCInStock){
											$requestedProductInfo['reclassification_C']=$product['product_quantity']-$quantityBottleQualityBInStock;
                      
											$productSummary.="Se puede remitir la cantidad requerida del producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad B si se convierte una cantidad ".$requestedProductInfo['reclassification_C']." de calidad C<br/>";
                      $comment="Remisión ".$requestData['Order']['order_code']." registrada de producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad B con reclasificación de cantidad ".$requestedProductInfo['reclassification_C']." de calidad C";
                      $requestedProductInfo['reclassification_comment']=$comment;
                      if (!empty($reclassificationComment)){
                        $reclassificationComment.="\r\n";
                      }
                      $reclassificationComment.=$comment;
										}
									}
								}
								else {
									$requestedProductInfo['reclassification_C']=0;								
								
									$productSummary.="Para producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad B la cantidad requerida ".$product['product_quantity']." está disponible en bodega en esta fecha.<br/>";
								}
								$requestedProductInfo['quality_B']=$quantityBottleQualityBInStock;
								$requestedProductInfo['quality_C']=$quantityBottleQualityCInStock;
                
							}
							elseif ($relatedProduct['ProductType']['id']==PRODUCT_TYPE_BOTTLE && $product['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
                $quantityBottleQualityBInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($product['product_id'],$product['raw_material_id'],PRODUCTION_RESULT_CODE_B,$orderDateAsString,0,true);
								$quantityBottleQualityCInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($product['product_id'],$product['raw_material_id'],PRODUCTION_RESULT_CODE_C,$orderDateAsString,0,true);
								$requestedProductInfo['quality_B']=$quantityBottleQualityBInStock;
								$requestedProductInfo['quality_C']=$quantityBottleQualityCInStock;
                if ($quantityBottleQualityCInStock < $product['product_quantity']){
                  $boolReclassificationPossible='0';
                  $reasonForNoReclassificationPossible.="No se pueden hacer reclasificaciones a calidad C, se intentó reclasificar al producto ".$relatedProduct['Product']['name']." ".$relatedRawMaterial['Product']['name']." calidad C.<br/>";	
                }
              }
              break;
						case CATEGORY_RAW:
						case CATEGORY_OTHER:
							// 20170425 ALTHOUGH TAPONES ARE NOT RECLASSIFIED AT THIS TIME, WE DO SHOW HOW MANY THERE ARE IN STOCK
							$quantity=$this->StockItemLog->getStockQuantityAtDateForProduct($product['product_id'],$orderDateAsString,0,true);
							if ($quantity<$product['product_quantity']){
								$productSummary.="Para producto ".$relatedProduct['Product']['name']." la cantidad requerida ".$product['product_quantity']." NO está disponible en bodega en esta fecha.<br/>";	
								$boolReclassificationPossible='0';
								$reasonForNoReclassificationPossible="Como para el producto ".$relatedProduct['Product']['name']." la cantidad requerida no es en bodega, no se puede realizar la venta; no se pueden reclasificar tapones.<br/>";
							}
							else {
								$productSummary.="Para producto ".$relatedProduct['Product']['name']." la cantidad requerida ".$product['product_quantity']." está disponible en bodega en esta fecha.<br/>";	
							}
							$requestedProductInfo['quantity']=$quantity;
							break;
						default:
							break;
					}
					$requestedProducts[]=$requestedProductInfo;
				}
			}
		}
		//pr($requestedProducts);
    
		$this->set(compact('requestedProducts'));
    $this->set(compact('reclassificationComment'));
		$this->set(compact('productSummary','boolReclassificationPossible','reasonForNoReclassificationPossible'));
		
    if (!empty($requestData['Order']['comment'])){
      $requestData['Order']['comment']=$requestData['Order']['comment']."\r\n".$reclassificationComment;
    }
    else {
      $requestData['Order']['comment']=$reclassificationComment;
    }
    
		if ($this->request->is('post')) {	
			//pr($this->request->data);
			$reclassificationDateString=$orderDateAsString;
			$reclassificationDatePlusOne=date("Y-m-d",strtotime($reclassificationDateString."+1 days"));
					
			$allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
			$allBottles=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE)));
			
      $boolReclassificationSuccess=true;
			      
			foreach ($this->request->data['ReclassificationProduct'] as $reclassificationProduct){
				// PRIMERA LA RECLASIFICACIÓN DE C a B
				$lastReclassification=$this->StockMovement->find('first',array(
					'fields'=>array('StockMovement.reclassification_code'),
					'conditions'=>array(
						'bool_reclassification'=>true,
					),
					'order'=>array('StockMovement.reclassification_code' => 'desc'),
				));
				$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1;
				$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
				
				if ($reclassificationProduct['reclassification_C']>0){
					$bottle_id=$reclassificationProduct['product_id'];
					$preforma_id=$reclassificationProduct['raw_material_id'];
					$original_production_result_code_id=PRODUCTION_RESULT_CODE_C;
					$target_production_result_code_id=PRODUCTION_RESULT_CODE_B;
					$quantity_bottles=$reclassificationProduct['reclassification_C'];
          $movement_comment=$reclassificationProduct['reclassification_comment'];
					if ($quantity_bottles>0 && $bottle_id>0 && $preforma_id>0){
						$quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($bottle_id,$preforma_id,PRODUCTION_RESULT_CODE_C,$orderDateAsString,0,true);
						if ($quantityInStock<$quantity_bottles){
							$this->Session->setFlash('bottle_id is '.$bottle_id.' y preforma_id is '.$preforma_id.' y orderdateasstring is '.$orderDateAsString.'. En bodega hay '.$quantityInStock.' y la cantidad necesitada es '.$quantity_bottles.'. Intento de reclasificar '.$quantity_bottles." ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." (".$productionResultCodes[$original_production_result_code_id].") pero en bodega únicamente hay ".$quantityInStock, 'default',['class' => 'error-message']);
						}
						else {
							// reclassify!
							$currentdate= new DateTime();
							$usedBottleStockItems=$this->StockItem->getFinishedMaterialsForSale($bottle_id,$original_production_result_code_id,$quantity_bottles,$preforma_id,$reclassificationDatePlusOne,0,"DESC");
							$quantityAvailableForReclassification=0;
							if (count($usedBottleStockItems)){
								foreach ($usedBottleStockItems as $usedBottleStockItem){
									$quantityAvailableForReclassification+=$usedBottleStockItem['quantity_present'];
								}
							}
							if ($quantity_bottles>$quantityAvailableForReclassification){
								$this->Session->setFlash('Los lotes presentes en el momento de reclasificación ya salieron de bodega', 'default',['class' => 'error-message']);
							}
							else {
								$newlyCreatedStockItems=[];
								
								//pr($usedBottleStockItems);
								$datasource=$this->StockItem->getDataSource();
								$datasource->begin();
								try{
									foreach ($usedBottleStockItems as $usedBottleStockItem){
										$stockItemId=$usedBottleStockItem['id'];
										$quantity_present=$usedBottleStockItem['quantity_present'];
										$quantity_used=$usedBottleStockItem['quantity_used'];
										$quantity_remaining=$usedBottleStockItem['quantity_remaining'];
										$unit_price=$usedBottleStockItem['unit_price'];
										if (!$this->StockItem->exists($stockItemId)) {
											throw new NotFoundException(__('Invalid StockItem'));
										}
										//$linkedStockItem=$this->StockItem->read(null,$stockItemId);
										$this->StockItem->recursive=-1;
										$linkedStockItem=$this->StockItem->find('first',array(
											'conditios'=>array(
												'StockItem.id'=>$stockItemId,
											),
										));
										$message="Reclassified ".$quantity_used." of ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." from ".$productionResultCodes[$original_production_result_code_id]." to ".$productionResultCodes[$target_production_result_code_id]." on ".date("d")."-".date("m")."-".date("Y");
										
										// STEP 1: EDIT THE STOCKITEM OF ORIGIN
										$stockItemData=[];
										$stockItemData['id']=$stockItemId;
										$stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
										$stockItemData['remaining_quantity']=$quantity_remaining;
										
										if (!$this->StockItem->save($stockItemData)) {
											echo "problema al editor el lote de origen";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
										$StockMovementData=[];
										$StockMovementData['movement_date']=$orderDateAsString;
										$StockMovementData['bool_input']='0';
										$StockMovementData['name']=$message;
										$StockMovementData['description']=$message;
										$StockMovementData['order_id']=0;
										$StockMovementData['stockitem_id']=$stockItemId;
										$StockMovementData['product_id']=$bottle_id;
										$StockMovementData['product_quantity']=$quantity_used;
										$StockMovementData['product_unit_price']=$unit_price;
										$StockMovementData['product_total_price']=$unit_price*$quantity_used;
										$StockMovementData['production_result_code_id']=$original_production_result_code_id;
										$StockMovementData['bool_reclassification']=true;
										$StockMovementData['reclassification_code']=$reclassificationCode;
                    $StockMovementData['comment']=$movement_comment;
										
										$this->StockMovement->create();
										if (!$this->StockMovement->save($StockMovementData)) {
											echo "problema al guardar el movimiento de lote";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}
										
										// STEP 3: SAVE THE TARGET STOCKITEM
										$stockItemData=[];
										$stockItemData['name']=$message;
										$stockItemData['description']=$message;
										$stockItemData['stockitem_creation_date']=$orderDateAsString;
										$stockItemData['product_id']=$bottle_id;
										$stockItemData['product_unit_price']=$unit_price;
										$stockItemData['original_quantity']=$quantity_used;
										$stockItemData['remaining_quantity']=$quantity_used;
										$stockItemData['production_result_code_id']=$target_production_result_code_id;
										$stockItemData['raw_material_id']=$preforma_id;
										
										$this->StockItem->create();
										// notice that no new stockitem is created because we are taking from an already existing one
										
										if (!$this->StockItem->save($stockItemData)) {
											echo "problema al guardar el lote de destino";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
										$new_stockitem_id=$this->StockItem->id;
										$newlyCreatedStockItems[]=$new_stockitem_id;
										
										$origin_stock_movement_id=$this->StockMovement->id;
										
										$StockMovementData=[];
										$StockMovementData['movement_date']=$orderDateAsString;
										$StockMovementData['bool_input']=true;
										$StockMovementData['name']=$message;
										$StockMovementData['description']=$message;
										$StockMovementData['order_id']=0;
										$StockMovementData['stockitem_id']=$new_stockitem_id;
										$StockMovementData['product_id']=$bottle_id;
										$StockMovementData['product_quantity']=$quantity_used;
										$StockMovementData['product_unit_price']=$unit_price;
										$StockMovementData['product_total_price']=$unit_price*$quantity_used;
										$StockMovementData['production_result_code_id']=$target_production_result_code_id;
										$StockMovementData['bool_reclassification']=true;
										$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
										$StockMovementData['reclassification_code']=$reclassificationCode;
                    $StockMovementData['comment']=$movement_comment;
										
										$this->StockMovement->create();
										if (!$this->StockMovement->save($StockMovementData)) {
											echo "problema al guardar el movimiento de lote";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}
												
										// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
										$this->recordUserActivity($this->Session->read('User.username'),$message);
									}
									
									$datasource->commit();
									
									foreach ($usedBottleStockItems as $usedBottleStockItem){
										$this->recreateStockItemLogs($usedBottleStockItem['id']);
									}
									for ($i=0;$i<count($newlyCreatedStockItems);$i++){
										$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
									}
									
									$reclassificationNumber=substr($reclassificationCode,6,6)+1;
									$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
									
									$this->Session->setFlash(__('Reclasificación exitosa'),'default',['class' => 'success']);
								}
								catch(Exception $e){
									$datasource->rollback();
									pr($e);
									$this->Session->setFlash(__('Reclasificación falló'), 'default',['class' => 'error-message'], 'default',['class' => 'error-message']);
                  $boolReclassificationSuccess='0';
								}
							}
						}
					}
					else {
						$warning="";
						if($quantity_bottles==0){
							$warning.="Cantidad de botellas debe estar positiva!<br/>";
						}
						if($bottle_id==0){
							$warning.="No botella seleccionada!<br/>";
						}
						if($preforma_id==0){
							$warning.="No preforma seleccionado!<br/>";
						}
						$this->Session->setFlash($warning, 'default',['class' => 'error-message']);
					}			
				}
      }
				
      if ($boolReclassificationSuccess){    
        $warehouseId=$this->request->data['Order']['warehouse_id'];
			
        $remission_date=$this->request->data['Order']['order_date'];
				$remissionDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
				$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
				$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
				$closingDate=new DateTime($latestClosingDate);
							
				$remissionDateArray=[];
				$remissionDateArray['year']=$remission_date['year'];
				$remissionDateArray['month']=$remission_date['month'];
				$remissionDateArray['day']=$remission_date['day'];
						
				$orderCode=$this->request->data['Order']['order_code'];
				$namedRemissions=$this->Order->find('all',array(
					'conditions'=>array(
						'order_code'=>$orderCode,
						'stock_movement_type_id'=>MOVEMENT_SALE,
					)
				));
				if (count($namedRemissions)>0){
					$this->Session->setFlash(__('Ya existe una remisión con el mismo código!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				else if ($remissionDateAsString>date('Y-m-d 23:59:59')){
					$this->Session->setFlash(__('La fecha de remisión no puede estar en el futuro!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				elseif ($remissionDateAsString<$latestClosingDatePlusOne){
					$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',['class' => 'error-message']);
				}
				elseif ($this->request->data['Order']['third_party_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar el cliente para la remisión!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				else if ($this->request->data['CashReceipt']['bool_annulled']){
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					try {
						//pr($this->request->data);
						$this->Order->create();
						$orderData=[];
						$orderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
						$orderData['Order']['order_date']=$this->request->data['Order']['order_date'];
						$orderData['Order']['order_code']=$this->request->data['Order']['order_code'];
						$orderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
						$orderData['Order']['total_price']=0;
				
						if (!$this->Order->save($orderData)) {
							echo "Problema guardando el orden de salida";
							pr($this->validateErrors($this->Order));
							throw new Exception();
						}
						
						$orderId=$this->Order->id;
						
						$this->CashReceipt->create();
						$CashReceiptData=[];
						$CashReceiptData['CashReceipt']['order_id']=$orderId;
						$CashReceiptData['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
						$CashReceiptData['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
						$CashReceiptData['CashReceipt']['bool_annulled']=true;
						$CashReceiptData['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
						$CashReceiptData['CashReceipt']['concept']=$this->request->data['CashReceipt']['concept'];
            $CashReceiptData['CashReceipt']['concept']="Anulada";
						$CashReceiptData['CashReceipt']['observation']=$this->request->data['CashReceipt']['observation'];
						$CashReceiptData['CashReceipt']['cash_receipt_type_id']=CASH_RECEIPT_TYPE_REMISSION;
						$CashReceiptData['CashReceipt']['amount']=0;
						$CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
				
						if (!$this->CashReceipt->save($CashReceiptData)) {
							echo "Problema guardando el recibo de caja";
							pr($this->validateErrors($this->CashReceipt));
							throw new Exception();
						}
						
						$datasource->commit();
						$this->recordUserAction();	
						// SAVE THE USERLOG 
						$this->recordUserActivity($this->Session->read('User.username'),"Se registró una remisión anulada con número ".$this->request->data['Order']['order_code']);
						$this->Session->setFlash(__('Se guardó la remisión.'),'default',['class' => 'success'],'default',['class' => 'success']);
						return $this->redirect(array('action' => 'resumenVentasRemisiones'));
						// on the view page the print button will be present; tt should display the invoice just as it has been made out, this is then sent to javascript
						//return $this->redirect(array('action' => 'verVenta',$orderId));
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('No se podía guardar la remisión.  Por favor vuelva a intentar.'), 'default',['class' => 'error-message']);
					}
				}					
				else if ($this->request->data['CashReceipt']['cashbox_accounting_code_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una remisión!  No se guardó la venta.'), 'default',['class' => 'error-message']);
				}
				else {
					// before moving into the selling part, perform the check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
					$remissionItemsOK=true;
					$exceedingItems="";
					$productCount=0;
					$products=[];
					foreach ($this->request->data['Product'] as $product){
						//pr($product);
						// keep track of number of rows so that in case of an error jquery displays correct number of rows again
						if ($product['product_id']>0){
							$productCount++;
						}
						// only process lines where product_quantity and product id have been filled out
						if ($product['product_quantity']>0 && $product['product_id']>0){
							$products[]=$product;
							$quantityEntered=$product['product_quantity'];
							$productId = $product['product_id'];
							$productionResultCodeId = $product['production_result_code_id'];
							$rawMaterialId = $product['raw_material_id'];
							$this->Product->recursive=-1;
							$relatedProduct=$this->Product->find('first',array(
								'conditions'=>array(
									'Product.id'=>$productId,
								),
							));
							
							if ($this->Product->getProductCategoryId($productId) == CATEGORY_PRODUCED){
								$quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($productId,$rawMaterialId,$productionResultCodeId,$remissionDateAsString,$warehouseId,true);
							}
							else {
								$quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productId,$remissionDateAsString,$warehouseId,true);
							}
							
							//compare the quantity requested and the quantity in stock
							if ($quantityEntered>$quantityInStock){
								$remissionItemsOK='0';
								$exceedingItems.=__("Para producto ".$relatedProduct['Product']['name']." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
							}
						}
					}
					if ($exceedingItems!=""){
						$exceedingItems.=__("Please correct and try again!");
					}					
					if (!$remissionItemsOK){
            $_SESSION['remissionRequestData']=$this->request->data;
							
						$aco_name="Orders/manipularRemision";		
						$bool_order_manipularremision_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
						
						if ($bool_order_manipularremision_permission){
							return $this->redirect(array('action' => 'manipularRemision'));
						}
					
						
						$this->Session->setFlash(__('La cantidad de bodega para los siguientes productos no es suficientes.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
					}
					else{
						$datasource=$this->Order->getDataSource();
						$datasource->begin();
						try {
							$currency_id=$this->request->data['CashReceipt']['currency_id'];
							$total_cash_receipt=$this->request->data['CashReceipt']['amount'];
					
							// if all products are in stock, proceed with the remission 
							$this->Order->create();
							$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
							
							// ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
							if ($currency_id==CURRENCY_USD){
								$this->request->data['Order']['total_price']=$total_cash_receipt*$this->request->data['Order']['exchange_rate'];
							}
							else {
								$this->request->data['Order']['total_price']=$total_cash_receipt;
							}
						
							if (!$this->Order->save($this->request->data)) {
								echo "Problema guardando la salida";
								pr($this->validateErrors($this->Order));
								throw new Exception();
							}
						
							$orderId=$this->Order->id;
							$orderCode=$this->request->data['Order']['order_code'];
						
							$this->CashReceipt->create();
							$this->request->data['CashReceipt']['order_id']=$orderId;
							$this->request->data['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
							$this->request->data['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
							$this->request->data['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
					
							if (!$this->CashReceipt->save($this->request->data)) {
								echo "Problema guardando la factura";
								pr($this->validateErrors($this->CashReceipt));
								throw new Exception();
							}
							$cash_receipt_id=$this->CashReceipt->id;
							// now prepare the accounting registers
							// if the invoice is paid with cash, save two or three accounting register; 
							// debit=caja selected by client, credit = ingresos por venta 401, amount = total
							if ($currency_id==CURRENCY_USD){
								$this->loadModel('ExchangeRate');
								$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($remissionDateAsString);
								//pr($applicableExchangeRate);
								$totalCS=round($total_cash_receipt*$applicableExchangeRate['ExchangeRate']['rate'],2);
							}
							else {
								$totalCS=$total_cash_receipt;
							}
							
							$accountingRegisterData['AccountingRegister']['concept']="Remisión Orden".$orderCode;
							$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
							$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
							$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;		
							$accountingRegisterData['AccountingRegister']['register_date']=$remissionDateArray;
							$accountingRegisterData['AccountingRegister']['amount']=$totalCS;
							$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
							
							$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['CashReceipt']['cashbox_accounting_code_id'];
							//$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['cashbox_accounting_code_id']);
							$accountingCode=$this->AccountingCode->find('first',array(
								'conditions'=>array(
									'AccountingCode.id'=>$this->request->data['CashReceipt']['cashbox_accounting_code_id'],
								),
							));
							$accountingRegisterData['AccountingMovement'][0]['concept']="Remisión ".$orderCode;
							$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
							$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$totalCS;
							
							$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
							//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
							$accountingCode=$this->AccountingCode->find('first',array(
								'conditions'=>array(
									'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
								),
							));
							$accountingRegisterData['AccountingMovement'][1]['concept']="Remisión Orden".$orderCode;
							$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
							$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$totalCS;
							
							//pr($accountingRegisterData);
							$accountingRegisterId=$this->saveAccountingRegisterData($accountingRegisterData,true);
							$this->recordUserAction($this->AccountingRegister->id,"add",null);
							//echo "accounting register saved for cuentas cobrar clientes<br/>";
					
							$AccountingRegisterCashReceiptData=[];
							$AccountingRegisterCashReceiptData['accounting_register_id']=$accountingRegisterId;
							$AccountingRegisterCashReceiptData['cash_receipt_id']=$cash_receipt_id;
							$this->AccountingRegisterCashReceipt->create();
							if (!$this->AccountingRegisterCashReceipt->save($AccountingRegisterCashReceiptData)) {
								pr($this->validateErrors($this->AccountingRegisterCashReceipt));
								echo "problema al guardar el lazo entre asiento contable y recibo de caja";
								throw new Exception();
							}
							//echo "link accounting register cash receipt saved<br/>";					
							
							foreach ($products as $product){
								// four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
								//pr($product);
								
								// load the product request data into variables
								$productId = $product['product_id'];
								$productCategoryId = $this->Product->getProductCategoryId($productId);
								$productionResultCodeId =0;
								$rawMaterialId=0;
								//echo "product_category_id is ".$productCategoryId."<br/>";
								if ($productCategoryId==CATEGORY_PRODUCED){
									$productionResultCodeId = $product['production_result_code_id'];
									$rawMaterialId = $product['raw_material_id'];
								}
								
								$productUnitPrice=$product['product_unit_price'];
								$productQuantity = $product['product_quantity'];
								
								if ($currency_id==CURRENCY_USD){
									$productUnitPrice*=$this->request->data['Order']['exchange_rate'];
								}
								
								// get the related product data
								//$linkedProduct=$this->Product->read(null,$productId);
								$this->Product->recursive=-1;
								$linkedProduct=$this->Product->find('first',array(
									'conditions'=>array(
										'Product.id'=>$productId,
									),
								));
								//pr($linkedProduct);
								$productName=$linkedProduct['Product']['name'];
								
								// STEP 1: SAVE THE STOCK ITEM(S)
								// first prepare the materials that will be taken out of stock
								
								if ($productCategoryId==CATEGORY_PRODUCED){
									$usedMaterials= $this->StockItem->getFinishedMaterialsForSale($productId,$productionResultCodeId,$productQuantity,$rawMaterialId,$remissionDateAsString,$warehouseId);		
								}
								else {
									$usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$remissionDateAsString,$warehouseId);		
								}
								//pr($usedMaterials);

								for ($k=0;$k<count($usedMaterials);$k++){
									$materialUsed=$usedMaterials[$k];
									$stockItemId=$materialUsed['id'];
									$quantity_present=$materialUsed['quantity_present'];
									$quantity_used=$materialUsed['quantity_used'];
									$quantity_remaining=$materialUsed['quantity_remaining'];
									if (!$this->StockItem->exists($stockItemId)) {
										throw new NotFoundException(__('Invalid StockItem'));
									}
									//$linkedStockItem=$this->StockItem->read(null,$stockItemId);
									$this->StockItem->recursive=-1;
									$linkedStockItem=$this->StockItem->find('first',array(
										'conditions'=>array(
											'StockItem.id'=>$stockItemId,
										),
									));
									$message="Se vendió lote ".$productName." (Cantidad:".$quantity_used.") para Venta ".$orderCode;
									
									$stockItemData=[];
									$stockItemData['id']=$stockItemId;
									//$stockItemData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$orderCode."_".$productName;
									$stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
									$stockItemData['remaining_quantity']=$quantity_remaining;
									// notice that no new stockitem is created because we are taking from an already existing one
									if (!$this->StockItem->save($stockItemData)) {
										echo "problema al guardar el lote";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									
									// STEP 2: SAVE THE STOCK MOVEMENT
									$message="Se remitió ".$productName." (Cantidad:".$quantity_used.", total para remisión:".$productQuantity.") para Remisión ".$orderCode;
									$StockMovementData=[];
									$StockMovementData['movement_date']=$remission_date;
									$StockMovementData['bool_input']='0';
									$StockMovementData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$orderCode."_".$productName;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=$orderId;
									$StockMovementData['stockitem_id']=$stockItemId;
									$StockMovementData['product_id']=$productId;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$productUnitPrice;
									$StockMovementData['product_total_price']=$productUnitPrice*$quantity_used;
									$StockMovementData['production_result_code_id']=$productionResultCodeId;
									
									$this->StockMovement->create();
									if (!$this->StockMovement->save($StockMovementData)) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
								
									// STEP 3: SAVE THE STOCK ITEM LOG
									$this->recreateStockItemLogs($stockItemId);
									
									// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
									$this->recordUserActivity($this->Session->read('User.username'),$message);
								}
							}
							$datasource->commit();
							//echo "data committed";
							$this->recordUserAction($this->Order->id,"add",null);
							// SAVE THE USERLOG FOR THE REMISSION
							$this->recordUserActivity($this->Session->read('User.username'),"Remisión registrada con número ".$this->request->data['Order']['order_code']);
							$this->Session->setFlash(__('Se guardó la remisión.'),'default',['class' => 'success'],'default',['class' => 'success']);
							return $this->redirect(array('action' => 'resumenVentasRemisiones'));
							// on the view page the print button will be present; it should display the invoice just as it has been made out, this is then sent to javascript
							//return $this->redirect(array('action' => 'verVenta',$orderId));
						}
						catch(Exception $e){
							$datasource->rollback();
							//pr($e);
							$this->Session->setFlash(__('La remisión no se podía guardar.'), 'default',['class' => 'error-message']);
						}
					}
				}
			}
		}
		
		$this->request->data=$requestData;
		//pr($requestData);
		$this->set(compact('requestData'));
		
		$warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
		
		$this->loadModel('Currency');
		$currencies = $this->Currency->find('list');
		$this->set(compact('currencies'));
		
		$this->loadModel('AccountingCode');
		$this->AccountingCode->recursive=-1;
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
			'order'=>'AccountingCode.code',
		));
		$this->set(compact('cashboxAccountingCode'));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			),
			'order'=>'AccountingCode.code',
		));
		$this->set(compact('accountingCodes'));
		
    $this->loadModel('CashReceiptType');
    $cashReceiptTypes = $this->CashReceiptType->find('list');
    $this->set(compact('cashReceiptTypes'));
    
		$rawProductTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_RAW,
			),
		));
		//$rawMaterialsAll=$this->Product->find('all', array(
		$rawMaterials=$this->Product->find('list', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=>$rawProductTypeIds,
			),
			'order'=>'Product.name',
		));
		//$rawMaterials=null;
		//foreach ($rawMaterialsAll as $rawMaterial){
		//	$rawMaterials[$rawMaterial['Product']['id']]=$rawMaterial['Product']['name'];
		//}
		$this->set(compact('rawMaterials'));
		
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions' => array(
				'ThirdParty.bool_provider'=> false,
				'ThirdParty.bool_active'=>true,
			),
			'order'=>'ThirdParty.company_name',			
		));
		$this->set(compact('thirdParties'));
		
		$productsAll = $this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'contain'=>array(
				'ProductType',
				'StockItem'=>array(
					'fields'=> array('remaining_quantity','raw_material_id','warehouse_id'),
				)
			),
			'order'=>'product_type_id DESC, name ASC',
		));
		$products = [];
		foreach ($productsAll as $product){
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					if ($stockitem['remaining_quantity']>0){
						if (!empty($warehouseId)){
							if ($stockitem['warehouse_id']==$warehouseId){
								$products[$product['Product']['id']]=$product['Product']['name'];
							}
						}
						else {
							$products[$product['Product']['id']]=$product['Product']['name'];
						}		
					}
				}
			}
		}
		$this->set(compact('products'));

		$producedProductTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_PRODUCED,
			),
		));
		//$finishedProductsAll = $this->Product->find('all', array(
		$finishedProducts = $this->Product->find('list', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=> $producedProductTypeIds,
			),
			'order'=>'Product.name',
		));
		//$finishedProducts = null;
		//foreach ($finishedProductsAll as $finishedProduct){
		//	$finishedProducts[$finishedProduct['Product']['id']]=$finishedProduct['Product']['name'];
		//}
		
		$otherProductTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_OTHER,
			),
		));
		//$otherMaterialsAll=$this->Product->find('all', array(
		$otherMaterials=$this->Product->find('all', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=>$otherProductTypeIds,
			),
			'order'=>'Product.name',
		));
		
		$this->set(compact('finishedProducts','otherMaterials'));
	}
	 
	public function crearRemision() {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('ProductionResultCode');
    
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('CashReceipt');
		$this->loadModel('CashReceiptType');
		
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterCashReceipt');
		
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $roleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId','roleId'));
    
    $this->Order->recursive=-1;
		$this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
    $this->Order->ThirdParty->recursive=-1;
    $this->Order->StockMovementType->recursive=-1;
    $this->AccountingCode->recursive=-1;
		$this->CashReceipt->recursive=-1;
    
    $warehouseId=0;
    
		$inventoryDisplayOptions=[
			'0'=>'No mostrar inventario',
			'1'=>'Mostrar inventario',
		];
		$this->set(compact('inventoryDisplayOptions'));
		$inventoryDisplayOptionId=1;
			
		$requestProducts=[];
    $productCount=0;
    
    
		if ($this->request->is('post')) {	
			foreach ($this->request->data['Product'] as $product){
				if (!empty($product['product_id'])){
					$requestProducts[]['Product']=$product;
				}
			}
			$warehouseId=$this->request->data['Order']['warehouse_id'];
			//$inventoryDisplayOptionId=$this->request->data['Order']['inventory_display_option_id'];
			if (empty($this->request->data['refresh'])&&empty($this->request->data['showinventory'])){
				$remission_date=$this->request->data['Order']['order_date'];
				$remissionDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
				$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
				$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
				$closingDate=new DateTime($latestClosingDate);
							
				$remissionDateArray=[];
				$remissionDateArray['year']=$remission_date['year'];
				$remissionDateArray['month']=$remission_date['month'];
				$remissionDateArray['day']=$remission_date['day'];
						
				$orderCode=$this->request->data['Order']['order_code'];
				$namedRemissions=$this->Order->find('all',array(
					'conditions'=>array(
						'order_code'=>$orderCode,
						'stock_movement_type_id'=>MOVEMENT_SALE,
					)
				));
				if (count($namedRemissions)>0){
					$this->Session->setFlash(__('Ya existe una remisión con el mismo código!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				else if ($remissionDateAsString>date('Y-m-d 23:59:59')){
					$this->Session->setFlash(__('La fecha de remisión no puede estar en el futuro!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				elseif ($remissionDateAsString<$latestClosingDatePlusOne){
					$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',['class' => 'error-message']);
				}
				elseif ($this->request->data['Order']['third_party_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar el cliente para la remisión!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				else if ($this->request->data['CashReceipt']['bool_annulled']){
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					try {
						//pr($this->request->data);
						$this->Order->create();
						$orderData=[];
						$orderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
						$orderData['Order']['order_date']=$this->request->data['Order']['order_date'];
						$orderData['Order']['order_code']=$this->request->data['Order']['order_code'];
						$orderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
						$orderData['Order']['total_price']=0;
				
						if (!$this->Order->save($orderData)) {
							echo "Problema guardando el orden de salida";
							pr($this->validateErrors($this->Order));
							throw new Exception();
						}
						
						$orderId=$this->Order->id;
						
						$this->CashReceipt->create();
						$CashReceiptData=[];
						$CashReceiptData['CashReceipt']['order_id']=$orderId;
						$CashReceiptData['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
						$CashReceiptData['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
						$CashReceiptData['CashReceipt']['bool_annulled']=true;
						$CashReceiptData['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
						$CashReceiptData['CashReceipt']['concept']=$this->request->data['CashReceipt']['concept'];
            $CashReceiptData['CashReceipt']['concept']="Anulada";
						$CashReceiptData['CashReceipt']['observation']=$this->request->data['CashReceipt']['observation'];
						$CashReceiptData['CashReceipt']['cash_receipt_type_id']=CASH_RECEIPT_TYPE_REMISSION;
						$CashReceiptData['CashReceipt']['amount']=0;
						$CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
				
						if (!$this->CashReceipt->save($CashReceiptData)) {
							echo "Problema guardando el recibo de caja";
							pr($this->validateErrors($this->CashReceipt));
							throw new Exception();
						}
						
						$datasource->commit();
						$this->recordUserAction($this->Order->id,"crearRemision",null);	
						// SAVE THE USERLOG 
						$this->recordUserActivity($this->Session->read('User.username'),"Se registró una remisión anulada con número ".$this->request->data['Order']['order_code']);
						$this->Session->setFlash(__('Se guardó la remisión.'),'default',['class' => 'success'],'default',['class' => 'success']);
						return $this->redirect(array('action' => 'resumenVentasRemisiones'));
						// on the view page the print button will be present; tt should display the invoice just as it has been made out, this is then sent to javascript
						//return $this->redirect(array('action' => 'verVenta',$orderId));
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('No se podía guardar la remisión.  Por favor vuelva a intentar.'), 'default',['class' => 'error-message']);
					}
				}					
				else if ($this->request->data['CashReceipt']['cashbox_accounting_code_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una remisión!  No se guardó la venta.'), 'default',['class' => 'error-message']);
				}
				else {
					// before moving into the selling part, perform the check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
					$remissionItemsOK=true;
					$exceedingItems="";
          
          $productMultiplicationOk=true;
          $productMultiplicationWarning="";
          
          $productTotalSumBasedOnProductTotals=0;
          
					$products=[];
					foreach ($this->request->data['Product'] as $product){
						//pr($product);
						// keep track of number of rows so that in case of an error jquery displays correct number of rows again
						if ($product['product_id']>0){
							$productCount++;
						}
						// only process lines where product_quantity and product id have been filled out
						if ($product['product_quantity']>0 && $product['product_id']>0 && $product['raw_material_id']>0){
							$products[]=$product;
							$quantityEntered=$product['product_quantity'];
							$productId = $product['product_id'];
							$productionResultCodeId = $product['production_result_code_id'];
							$rawMaterialId = $product['raw_material_id'];
							$this->Product->recursive=-1;
							$relatedProduct=$this->Product->find('first',array(
								'conditions'=>array(
									'Product.id'=>$productId,
								),
							));
							
							if ($this->Product->getProductCategoryId($productId)==CATEGORY_PRODUCED){
								$quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($productId,$rawMaterialId,$productionResultCodeId,$remissionDateAsString,$warehouseId,true);
							}
							else {
								$quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productId,$remissionDateAsString,$warehouseId,true);
							}
							
							//compare the quantity requested and the quantity in stock
							if ($quantityEntered>$quantityInStock){
								$remissionItemsOK='0';
								$exceedingItems.=__("Para producto ".$relatedProduct['Product']['name']." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
							}
              
              $productMultiplicationBasedOnUnitPriceAndQuantity=$product['product_quantity']*$product['product_unit_price'];
              $productMultiplicationBasedOnTotalPrice=$product['product_total_price'];
              if (abs($productMultiplicationBasedOnUnitPriceAndQuantity-$productMultiplicationBasedOnTotalPrice) > 0.01){
                $productMultiplicationOk='0';
                $productMultiplicationWarning.="Producto ".$relatedProduct['Product']['name']." tiene una cantidad ".$product['product_quantity']." y un precio unitario ".$product['product_unit_price'].", pero el total calculado ".$product['product_total_price']." no es correcto;";
              }
              //echo "product total price is ".$product['product_total_price']."<br/>";
              $productTotalSumBasedOnProductTotals+=$product['product_total_price'];
						}
					}
          
					if ($exceedingItems!=""){
						$exceedingItems.=__("Please correct and try again!");
					}
          if (!$productMultiplicationOk){
            $this->Session->setFlash($productMultiplicationWarning.'  vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
          }
          elseif (abs($productTotalSumBasedOnProductTotals-$this->request->data['CashReceipt']['amount']) > 0.01){
            $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['CashReceipt']['amount'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
          }
          elseif (!$remissionItemsOK){
            $_SESSION['remissionRequestData']=$this->request->data;
							
						$aco_name="Orders/manipularRemision";		
						$bool_order_manipularremision_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
						
						if ($bool_order_manipularremision_permission){
              //echo $exceedingItems."<br/>";
							return $this->redirect(array('action' => 'manipularRemision'));
						}
						$this->Session->setFlash(__('La cantidad de bodega para los siguientes productos no es suficientes.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
					}
					else{
						$datasource=$this->Order->getDataSource();
						$datasource->begin();
						try {
							$currency_id=$this->request->data['CashReceipt']['currency_id'];
							$total_cash_receipt=$this->request->data['CashReceipt']['amount'];
					
							// if all products are in stock, proceed with the remission 
							$this->Order->create();
							$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
							
							// ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
							if ($currency_id==CURRENCY_USD){
								$this->request->data['Order']['total_price']=$total_cash_receipt*$this->request->data['Order']['exchange_rate'];
							}
							else {
								$this->request->data['Order']['total_price']=$total_cash_receipt;
							}
						
							if (!$this->Order->save($this->request->data)) {
								echo "Problema guardando la salida";
								pr($this->validateErrors($this->Order));
								throw new Exception();
							}
						
							$orderId=$this->Order->id;
							$orderCode=$this->request->data['Order']['order_code'];
						
							$this->CashReceipt->create();
							$this->request->data['CashReceipt']['order_id']=$orderId;
							$this->request->data['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
							$this->request->data['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
							$this->request->data['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
					
							if (!$this->CashReceipt->save($this->request->data)) {
								echo "Problema guardando la factura";
								pr($this->validateErrors($this->CashReceipt));
								throw new Exception();
							}
							$cash_receipt_id=$this->CashReceipt->id;
							// now prepare the accounting registers
							// if the invoice is paid with cash, save two or three accounting register; 
							// debit=caja selected by client, credit = ingresos por venta 401, amount = total
							if ($currency_id==CURRENCY_USD){
								$this->loadModel('ExchangeRate');
								$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($remissionDateAsString);
								//pr($applicableExchangeRate);
								$totalCS=round($total_cash_receipt*$applicableExchangeRate['ExchangeRate']['rate'],2);
							}
							else {
								$totalCS=$total_cash_receipt;
							}
							
							$accountingRegisterData['AccountingRegister']['concept']="Remisión Orden".$orderCode;
							$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
							$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
							$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;		
							$accountingRegisterData['AccountingRegister']['register_date']=$remissionDateArray;
							$accountingRegisterData['AccountingRegister']['amount']=$totalCS;
							$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
							
							$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['CashReceipt']['cashbox_accounting_code_id'];
							//$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['cashbox_accounting_code_id']);
							$accountingCode=$this->AccountingCode->find('first',array(
								'conditions'=>array(
									'AccountingCode.id'=>$this->request->data['CashReceipt']['cashbox_accounting_code_id'],
								),
							));
							$accountingRegisterData['AccountingMovement'][0]['concept']="Remisión ".$orderCode;
							$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
							$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$totalCS;
							
							$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
							//$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
							$accountingCode=$this->AccountingCode->find('first',array(
								'conditions'=>array(
									'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA,
								),
							));
							$accountingRegisterData['AccountingMovement'][1]['concept']="Remisión Orden".$orderCode;
							$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
							$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$totalCS;
							
							//pr($accountingRegisterData);
							$accountingRegisterId=$this->saveAccountingRegisterData($accountingRegisterData,true);
							$this->recordUserAction($this->AccountingRegister->id,"add",null);
							//echo "accounting register saved for cuentas cobrar clientes<br/>";
					
							$AccountingRegisterCashReceiptData=[];
							$AccountingRegisterCashReceiptData['accounting_register_id']=$accountingRegisterId;
							$AccountingRegisterCashReceiptData['cash_receipt_id']=$cash_receipt_id;
							$this->AccountingRegisterCashReceipt->create();
							if (!$this->AccountingRegisterCashReceipt->save($AccountingRegisterCashReceiptData)) {
								pr($this->validateErrors($this->AccountingRegisterCashReceipt));
								echo "problema al guardar el lazo entre asiento contable y recibo de caja";
								throw new Exception();
							}
							//echo "link accounting register cash receipt saved<br/>";					
							
							foreach ($products as $product){
								// four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
								//pr($product);
								
								// load the product request data into variables
								$productId = $product['product_id'];
								$productCategoryId = $this->Product->getProductCategoryId($productId);
								$productionResultCodeId =0;
								$rawMaterialId=0;
								//echo "product_category_id is ".$productCategoryId."<br/>";
								if ($productCategoryId==CATEGORY_PRODUCED){
									$productionResultCodeId = $product['production_result_code_id'];
									$rawMaterialId = $product['raw_material_id'];
								}
								
								$productUnitPrice=$product['product_unit_price'];
								$productQuantity = $product['product_quantity'];
								
								if ($currency_id==CURRENCY_USD){
									$productUnitPrice*=$this->request->data['Order']['exchange_rate'];
								}
								
								// get the related product data
								//$linkedProduct=$this->Product->read(null,$productId);
								$this->Product->recursive=-1;
								$linkedProduct=$this->Product->find('first',array(
									'conditions'=>array(
										'Product.id'=>$productId,
									),
								));
								//pr($linkedProduct);
								$productName=$linkedProduct['Product']['name'];
								
								// STEP 1: SAVE THE STOCK ITEM(S)
								// first prepare the materials that will be taken out of stock
								
								if ($productCategoryId==CATEGORY_PRODUCED){
									$usedMaterials= $this->StockItem->getFinishedMaterialsForSale($productId,$productionResultCodeId,$productQuantity,$rawMaterialId,$remissionDateAsString,$warehouseId);		
								}
								else {
									$usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$remissionDateAsString,$warehouseId);		
								}
								//pr($usedMaterials);

								for ($k=0;$k<count($usedMaterials);$k++){
									$materialUsed=$usedMaterials[$k];
									$stockItemId=$materialUsed['id'];
									$quantity_present=$materialUsed['quantity_present'];
									$quantity_used=$materialUsed['quantity_used'];
									$quantity_remaining=$materialUsed['quantity_remaining'];
									if (!$this->StockItem->exists($stockItemId)) {
										throw new NotFoundException(__('Invalid StockItem'));
									}
									//$linkedStockItem=$this->StockItem->read(null,$stockItemId);
									$this->StockItem->recursive=-1;
									$linkedStockItem=$this->StockItem->find('first',array(
										'conditions'=>array(
											'StockItem.id'=>$stockItemId,
										),
									));
									$message="Se vendió lote ".$productName." (Cantidad:".$quantity_used.") para Venta ".$orderCode;
									
									$stockItemData=[];
									$stockItemData['id']=$stockItemId;
									//$stockItemData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$orderCode."_".$productName;
									$stockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
									$stockItemData['remaining_quantity']=$quantity_remaining;
									// notice that no new stockitem is created because we are taking from an already existing one
									if (!$this->StockItem->save($stockItemData)) {
										echo "problema al guardar el lote";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									
									// STEP 2: SAVE THE STOCK MOVEMENT
									$message="Se remitió ".$productName." (Cantidad:".$quantity_used.", total para remisión:".$productQuantity.") para Remisión ".$orderCode;
									$StockMovementData=[];
									$StockMovementData['movement_date']=$remission_date;
									$StockMovementData['bool_input']='0';
									$StockMovementData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$orderCode."_".$productName;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=$orderId;
									$StockMovementData['stockitem_id']=$stockItemId;
									$StockMovementData['product_id']=$productId;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$productUnitPrice;
									$StockMovementData['product_total_price']=$productUnitPrice*$quantity_used;
									$StockMovementData['production_result_code_id']=$productionResultCodeId;
									
									$this->StockMovement->create();
									if (!$this->StockMovement->save($StockMovementData)) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
								
									// STEP 3: SAVE THE STOCK ITEM LOG
									$this->recreateStockItemLogs($stockItemId);
									
									// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
									$this->recordUserActivity($this->Session->read('User.username'),$message);
								}
							}
							$datasource->commit();
							//echo "data committed";
							$this->recordUserAction($this->Order->id,"add",null);
							// SAVE THE USERLOG FOR THE REMISSION
							$this->recordUserActivity($this->Session->read('User.username'),"Remisión registrada con número ".$this->request->data['Order']['order_code']);
							$this->Session->setFlash(__('Se guardó la remisión.'),'default',['class' => 'success'],'default',['class' => 'success']);
							return $this->redirect(array('action' => 'resumenVentasRemisiones'));
							// on the view page the print button will be present; it should display the invoice just as it has been made out, this is then sent to javascript
							//return $this->redirect(array('action' => 'verVenta',$orderId));
						}
						catch(Exception $e){
							$datasource->rollback();
							//pr($e);
							$this->Session->setFlash(__('La remisión no se podía guardar.'), 'default',['class' => 'error-message']);
						}
					}
				}
			}
		}
		
		$this->set(compact('inventoryDisplayOptionId'));
		$this->set(compact('requestProducts'));
		
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
    
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions' => array(
				'ThirdParty.bool_provider'=> false,
				'ThirdParty.bool_active'=>true,
			),
			'order'=>'ThirdParty.company_name',			
		));
		
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		
		if (!empty($this->request->data)){
			// 	20170422 THE ONLY WAY THE REQUEST DATA WOULD BE SET IF THERE WAS A SUBMISSION OF A KIND ALREADY
			// 20170422 THIS WOULD IMPLY THAT THE ORDER DATE COMES IN FORM OF AN ARRAY
			//if (is_array($this->request->data['Order']['order_date'])){
				$orderDateArray=$this->request->data['Order']['order_date'];
				$orderDateString=$orderDateArray['year'].'-'.$orderDateArray['month'].'-'.$orderDateArray['day'];
				$orderDate=date("Y-m-d",strtotime($orderDateString));
				$orderDatePlusOne=date("Y-m-d",strtotime($orderDateString."+1 days"));
			//}
		}
		else {
			// 20170422 CREATION BY DEFAULT HAS CURRENT DATE
			$orderDate=date("Y-m-d",strtotime(date('Y-m-d')));
			$orderDatePlusOne=date("Y-m-d",strtotime(date('Y-m-d')."+1 days"));
		}
		$this->set(compact('orderDate'));
		
    $availableProductsForSale=$this->Product->getAvailableProductsForSale($orderDate,$warehouseId,true);
    
    $products=$availableProductsForSale['products'];
    $rawMaterialsAvailablePerFinishedProduct=$availableProductsForSale['rawMaterialsAvailablePerFinishedProduct'];
    $rawMaterials=$availableProductsForSale['rawMaterials'];
    $this->set(compact('products'));
    $this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    $this->set(compact('rawMaterials'));
    //pr($rawMaterialsAvailablePerFinishedProduct);
   
		$productsAll = $this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'ProductType.product_category_id'=> CATEGORY_PRODUCED,
        'Product.bool_active'=> true,
			),
			'contain'=>array(
				'ProductType',
				'StockItem'=>array(
					'fields'=> array('remaining_quantity','raw_material_id','warehouse_id'),
					'conditions'=>array(
						'StockItem.stockitem_creation_date <'=>$orderDatePlusOne,
            'StockItem.bool_active'=>true,
					),
				)
			),
			'order'=>'product_type_id DESC, name ASC',
		));
		//pr($productsAll);
		$products = [];
		$rawMaterialIds=[];
		foreach ($productsAll as $product){
			// only show products that are in inventory AT CURRENT DATE
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					if ($stockitem['remaining_quantity']>0){
						// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
						// in this case the associative array just contains the product_id because otherwise the list would become very long
						if (!empty($warehouseId)){
							if ($stockitem['warehouse_id']==$warehouseId){
								$products[$product['Product']['id']]=$product['Product']['name'];
                if (!in_array($stockitem['raw_material_id'],$rawMaterialIds) && !empty($stockitem['raw_material_id'])){
                  $rawMaterialIds[]=$stockitem['raw_material_id'];
                }
							}
						}
						else {
							$products[$product['Product']['id']]=$product['Product']['name'];
              if (!in_array($stockitem['raw_material_id'],$rawMaterialIds) && !empty($stockitem['raw_material_id'])){
                $rawMaterialIds[]=$stockitem['raw_material_id'];
              }
						}	
					}
				}
			}
		}
		//pr($rawMaterialIds);
		
		$productionResultCodes=$this->ProductionResultCode->find('list',array('conditions'=>array('id !='=>PRODUCTION_RESULT_CODE_A)));
    
    //$rawProductTypeIds=$this->ProductType->find('list',array(
    //  'fields'=>'ProductType.id',
    //  'conditions'=>array(
    //    'ProductType.product_category_id'=> CATEGORY_RAW
    //  ),
    //));
    
		$preformasAll = $this->Product->find('all',[
      'fields'=>'Product.id,Product.name',
      'conditions' => [
        //'Product.product_type_id ='=> $rawProductTypeIds,
        'Product.id'=>$rawMaterialIds,
        'Product.bool_active'=>true
      ],
      'order'=>'Product.name',
		]);
		//pr($preformasAll);
		
    $rawMaterials=[];
		foreach ($preformasAll as $preforma){
			$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
		}
		
		if (!empty($inventoryDisplayOptionId)){
			$productCategoryId=CATEGORY_PRODUCED;
			$productTypeIds=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productCategoryId)
			));
			$finishedMaterialsInventory =[];
			$finishedMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
			if ($warehouseId != WAREHOUSE_INJECTION){
        $injectionMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,WAREHOUSE_INJECTION);
        $this->set(compact('injectionMaterialsInventory'));
      }
      $productCategoryId=CATEGORY_OTHER;
			$productTypeIds=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productCategoryId)
			));
			$otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
			$productCategoryId=CATEGORY_RAW;
			$productTypeIds=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productCategoryId)
			));
			$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
			//$rawMaterialsInventory = $this->StockItem->getInventoryTotals($categoryids,productTypeIds);
			//$finishedMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_PRODUCED);
			//$otherMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_OTHER);
		}
		$currencies = $this->Currency->find('list');
		
		//$accountingCodes = $this->AccountingCode->find('list',array('fields'=>array('AccountingCode.id','AccountingCode.shortfullname')));
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
			'order'=>'AccountingCode.code',
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			),
			'order'=>'AccountingCode.code',
		));
		
		$cashReceiptTypes = $this->CashReceiptType->find('list');
		
		// calculate the code for the new service sheet
		$newCashReceiptCode="";
		/*$allCashReceipts = $this->CashReceipt->find('all',array(
			'fields'=>array('receipt_code'),
			'order' => array('CashReceipt.receipt_code' => 'desc'),
		));
		pr($allCashReceipts);
		*/
		$this->CashReceipt->recursive=-1;
		$lastCashReceipt = $this->CashReceipt->find('first',array(
			'fields'=>array('receipt_code'),
			'order' => array('CAST(SUBSTR(CashReceipt.receipt_code,4,5) AS DEC)' => 'desc'),
		));
		//CAST(SUBSTR(receipt_code,4,5) AS DEC) DESC
		//SELECT * FROM `cash_receipts` WHERE 1 ORDER BY CAST(SUBSTR(receipt_code,4,5) AS DEC) DESC 
		//pr($lastCashReceipt);
		if ($lastCashReceipt!= null){
			$newCashReceiptCode = intval(substr($lastCashReceipt['CashReceipt']['receipt_code'],4))+1;
			$newCashReceiptCode="R/C ".$newCashReceiptCode;
		}
		else {
			$newCashReceiptCode="R/C 00001";
		}
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','rawMaterials','currencies','accountingCodes','newCashReceiptCode','cashReceiptTypes'));
		
		$this->loadModel('ExchangeRate');
		$orderDate=date( "Y-m-d");
		$orderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($orderDate);
		$exchangeRateOrder=$orderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
    /*
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
    */
	}
	
  public function editarEntrada($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Entrada no válida'));
		}
		
		$this->loadModel('Product');
    $this->loadModel('StockMovement');
		$this->loadModel('ThirdParty');
		$this->loadModel('ClosingDate');
		$this->loadModel('PurchaseOrder');
    $this->loadModel('PurchaseOrderInvoice');
    $this->loadModel('ExchangeRate');
                  
    $this->loadModel('Unit');
    
    $this->loadModel('PlantProductType');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('StockItem');
    $this->loadModel('StockItemLog');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionRun');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
		
    $purchaseOrderDeliveryOptions=[
      //0=>'Entrega parcial',
      1=>'Entrega completa',
    ];
    $this->set(compact('purchaseOrderDeliveryOptions'));
		
		$productsPurchasedEarlier=[];
		$productsPurchasedEarlier = $this->Order->StockMovement->find('all',[
			'fields'=>'StockMovement.product_id,Product.name,StockMovement.product_quantity,StockMovement.stockitem_id,StockMovement.product_total_price,StockMovement.id',
			'conditions' => [
				'StockMovement.order_id'=> $id,
				'StockMovement.product_quantity >'=> 0,
				'StockMovement.product_id >'=> 0,
			],
		]);
		
    $deletabilityData=$this->Order->getDeletabilityEntryData($id);
    $this->set(compact('deletabilityData'));  
    
		$productsInEntry=[];
    $requestProducts=[];
    $requestInvoices=[];
		if ($this->request->is(['post', 'put'])) {
      $warehouseId=$this->request->data['Order']['warehouse_id'];
      
      $boolInvoiceNamesOk=true;
      $invoiceNameError="";
      $boolInvoiceIvaOk=true;
      $invoiceIvaError="";
      $boolInvoiceLineTotalsOk=true;
      $invoiceLineTotalsError="";
      $invoiceTotalBasedOnInvoices=0;
      if (!empty($this->request->data['PurchaseOrderInvoice'])){
        foreach ($this->request->data['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
          //pr($purchaseOrderInvoice);  
          if (!empty(trim($purchaseOrderInvoice['invoice_code']))){
            $requestInvoices[]['PurchaseOrderInvoice']=$purchaseOrderInvoice;
            
            if ($purchaseOrderInvoice['invoice_subtotal'] > 0 && empty($purchaseOrderInvoice['invoice_code'])){
              $boolInvoiceNamesOk='0';
              $invoiceNameError.="Hay una factura con un subtotal de ".$purchaseOrderInvoice['invoice_subtotal']." pero falta el número de la factura.";
            }
            if ($purchaseOrderInvoice['bool_iva'] == 0 && $purchaseOrderInvoice['invoice_iva'] > 0){
              $boolInvoiceIvaOk='0';
              $invoiceIvaError.="La factura ".$purchaseOrderInvoice['invoice_code']." no aplica IVA y el IVA es ".$purchaseOrderInvoice['invoice_iva'];
            }
            if ($purchaseOrderInvoice['bool_iva'] && abs($purchaseOrderInvoice['invoice_iva'] - 0.15*$purchaseOrderInvoice['invoice_subtotal'])> 0.01){
              $boolInvoiceIvaOk='0';
              $invoiceIvaError.="La factura ".$purchaseOrderInvoice['invoice_code']." aplica IVA, el IVA es ".$purchaseOrderInvoice['invoice_iva'].' y el 15% del subtotal es '.(0.15*$purchaseOrderInvoice['invoice_subtotal']);
            }
            if (abs($purchaseOrderInvoice['invoice_total'] - $purchaseOrderInvoice['invoice_iva']-$purchaseOrderInvoice['invoice_subtotal'])> 0.01){
              $boolInvoiceLineTotalsOk='0';
              $invoiceLineTotalsError.="La factura ".$purchaseOrderInvoice['invoice_code']." tiene un total de ".$purchaseOrderInvoice['invoice_total'].' pero la suma del subtotal '.$purchaseOrderInvoice['invoice_subtotal'].' y del IVA '.$purchaseOrderInvoice['invoice_iva'].' es '.($purchaseOrderInvoice['invoice_subtotal'] + $purchaseOrderInvoice['invoice_iva']);
            }
            
            $invoiceTotalBasedOnInvoices+=$purchaseOrderInvoice['invoice_subtotal'];
          }
        }
      }
      
      $productTotalSumBasedOnProductTotals=0;  
      foreach ($this->request->data['Product'] as $product){
        //pr($product);
        if ($product['product_quantity'] > 0 && $product['product_id'] > 0){
          $requestProducts[]['Product']=$product;
          $productTotalSumBasedOnProductTotals+=$product['product_price'];
        }
      }
      
			$purchaseDate=$this->request->data['Order']['order_date'];
			$purchaseDateAsString=$this->Order->deconstruct('order_date',$this->request->data['Order']['order_date']);
			
      if(empty($this->request->data['refresh'] )){
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDate=new DateTime($latestClosingDate);
        
        $previousPurchasesWithThisCode=[];
        $previousPurchasesWithThisCode=$this->Order->find('all',[
          'conditions'=>[
            'Order.order_code'=>$this->request->data['Order']['order_code'],
            'Order.stock_movement_type_id'=>[MOVEMENT_PURCHASE,MOVEMENT_PURCHASE_CONSUMIBLES],
            'Order.third_party_id'=>$this->request->data['Order']['third_party_id'],
            'Order.id !='=>$id,
          ],
        ]);
			
        $purchaseOrderWarehouseId=$this->PurchaseOrder->getPurchaseOrderWarehouseId($this->request->data['Order']['purchase_order_id']);
      
        $previousOrder=$this->Order->getOrder($id);
        //echo 'previous purchase date was '.$previousOrder['Order']['order_date'].'<br/>';
        //echo 'current purchase date is '.$purchaseDateAsString.'<br/>';
        
        $newPurchaseDateOlderThanPreviousPurchaseDate=true;
        $previousPurchaseDateTime=new DateTime($previousOrder['Order']['order_date']);
        $currentPurchaseDateTime=new DateTime($purchaseDateAsString);
          
        $dateCanBeEdited=true;
        $dateComparisonOutcome='';
        if ($previousOrder['Order']['order_date'] != $purchaseDate){
          if ($previousPurchaseDateTime->format('Y')<$currentPurchaseDateTime->format('Y')){
            $newPurchaseDateOlderThanPreviousPurchaseDate='0';
          }
          elseif ($previousPurchaseDateTime->format('m')<$currentPurchaseDateTime->format('m')){
            $newPurchaseDateOlderThanPreviousPurchaseDate='0';
          }
          elseif ($previousPurchaseDateTime->format('d')<$currentPurchaseDateTime->format('d')){
            $newPurchaseDateOlderThanPreviousPurchaseDate='0';
          }
          if (!$newPurchaseDateOlderThanPreviousPurchaseDate){
            $dateComparisonOutcome='La fecha nueva de la entrada '.($currentPurchaseDateTime->format('d-m-Y')).' viene después de la fecha anterior '.($previousPurchaseDateTime->format('d-m-Y')).'!';  
            if (!empty($deletabilityData['productionRuns'])){
              $productionRunCounter=0;
              $prohibitiveProductionRuns='';
              foreach ($deletabilityData['productionRuns'] as $productionRunId=>$productionRunData){
                $productionRunCounter++;
                $productionRunDateTime=new DateTime($productionRunData['production_run_date']);
                if ($productionRunDateTime < $currentPurchaseDateTime){
                   $prohibitiveProductionRuns.=$productionRunData['production_run_code'].' ('.$productionRunDateTime->format('d-m-Y').'), ';
                }
              }
              if (!empty($prohibitiveProductionRuns)){
                $dateComparisonOutcome.='  Los procesos de producción '.substr($prohibitiveProductionRuns,0,strlen($prohibitiveProductionRuns)-2).' vienen antes de la nueva fecha de entrada.';
                $dateCanBeEdited='0';
              }
            }
            if (!empty($deletabilityData['orders'])){
              $orderCounter=0;
              $prohibitiveOrders='';
              foreach ($deletabilityData['orders'] as $orderId=>$orderData){
                $orderCounter++;
                $orderDateTime=new DateTime($orderData['order_date']);
                if ($orderDateTime < $currentPurchaseDateTime){
                   $prohibitiveOrders.=$orderData['order_code'].' ('.$orderDateTime->format('d-m-Y').'), ';
                }
              }
              if (!empty($prohibitiveOrders)){
                $dateComparisonOutcome.='  Los ventas '.substr($prohibitiveOrders,0,strlen($prohibitiveOrders)-2).' vienen antes de la nueva fecha de entrada.';
                $dateCanBeEdited='0';
              }
            }
            if (!empty($deletabilityData['transfers'])){
              $transferCounter=0;
              $prohibitiveTransfers='';
              foreach ($deletabilityData['transfers'] as $transferData){
                $transferCounter++;
                $transferDateTime=new DateTime($transferData['transfer_date']);
                if ($transferDateTime < $currentPurchaseDateTime){
                   $prohibitiveTransfers.=$transferData['transfer_code'].' ('.$transferDateTime->format('d-m-Y').'), ';
                }
              }
              if (!empty($prohibitiveTransfers)){
                $dateComparisonOutcome.='  Las transferencias '.substr($prohibitiveTransfers,0,strlen($prohibitiveTransfers)-2).' vienen antes de la nueva fecha de entrada.';
                $dateCanBeEdited='0';
              }
            }
          }
        }
        
        if ($purchaseDateAsString>date('Y-m-d H:i')){
          $this->Session->setFlash(__('La fecha de entrada no puede estar en el futuro!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        elseif ($purchaseDateAsString<$latestClosingDatePlusOne){
          $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',['class' => 'error-message']);
        }
        elseif (count($previousPurchasesWithThisCode)>0){
          $this->Session->setFlash(__('Ya se introdujo una entrada con este código!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        elseif ($warehouseId != $purchaseOrderWarehouseId){
          echo 'warehouseid is '.$warehouseId.' and purchaseorderwarehouseid is '.$purchaseOrderWarehouseId.'<br/>';
          $this->Session->setFlash(__('La bodega de la entrada y de la orden de compra deben ser iguales!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        elseif (!$boolInvoiceNamesOk){
          $this->Session->setFlash($invoiceNameError.'  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolInvoiceIvaOk){
          $this->Session->setFlash($invoiceIvaError.'  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolInvoiceLineTotalsOk){
          $this->Session->setFlash($invoiceLineTotalsError.'  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (abs($invoiceTotalBasedOnInvoices-$this->request->data['Order']['total_price']) > 1){
          $this->Session->setFlash('Si se suman los subtotales de cada factura se llega a '.$invoiceTotalBasedOnInvoices.' pero el total calculado es '.$this->request->data['Order']['total_price'].'.  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (abs($this->request->data['Order']['entry_cost_total'] - $this->request->data['Order']['entry_cost_iva'] - $this->request->data['Order']['total_price']) > 0.01){
          $this->Session->setFlash('El total para la entrada incluyendo IVA es '.$this->request->data['Order']['entry_cost_total'].' pero la suma del subtotal '.$this->request->data['Order']['total_price'].' y el IVA '.$this->request->data['Order']['entry_cost_iva'].' es '.($this->request->data['Order']['total_price']+$this->request->data['Order']['entry_cost_iva']).'.  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        
        
        elseif (abs($productTotalSumBasedOnProductTotals-$this->request->data['Order']['subtotal_based_on_products']) > 1){
          $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['Order']['total_price'].'.  Verifique que ha indicado cada producto para que se registró un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (abs($this->request->data['Order']['subtotal_based_on_products'] - $this->request->data['Order']['total_price']) > 1){
          $this->Session->setFlash('El subtotal basado en productos es '.$this->request->data['Order']['subtotal_based_on_products'].' pero el subtotal de las facturas es '.$this->request->data['Order']['total_price'].'.  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['Order']['third_party_id'] )){
          $this->Session->setFlash(__('Se debe especificar un proveedor para la entrada!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['Order']['purchase_order_id']) && $this->request->data['Order']['third_party_id'] != 102){
          $this->Session->setFlash(__('Se debe especificar una orden de compra para la entrada!  No se guardó la entrada.'), 'default',['class' => 'error-message']);
        }
        elseif (!$dateCanBeEdited){
          $this->Session->setFlash($dateComparisonOutcome.'  No se guardó la entrada.', 'default',['class' => 'error-message']);
        }
        else{
          //pr($this->request->data);
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          try {
            $previousPurchaseOrderInvoices=$this->PurchaseOrderInvoice->find('list',[
              'fields'=>'PurchaseOrderInvoice.id',
              'conditions'=>[
                'PurchaseOrderInvoice.entry_id'=>$id,
              ]
            ]);
            
            if (!empty($previousPurchaseOrderInvoices)){
              foreach ($previousPurchaseOrderInvoices as $previousPurchaseOrderInvoiceId){
                $this->PurchaseOrderInvoice->id=$previousPurchaseOrderInvoiceId;
                if (!$this->PurchaseOrderInvoice->delete($previousPurchaseOrderInvoiceId)) {
                  echo "problema eliminando la información obsoleta de facturas";
                  pr($this->validateErrors($this->PurchaseOrderInvoice));
                  throw new Exception();
                }
              }
            }
            
            $this->Order->id=$id;
            $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_PURCHASE;
            if (!$this->Order->save($this->request->data)) {
              echo "problema guardando la entrada";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            }
            $purchaseId=$this->Order->id;
            $orderCode=$this->request->data['Order']['order_code'];
            $providerId=$this->request->data['Order']['third_party_id'];
            $this->ThirdParty->recursive=-1;
            $linkedProvider=$this->ThirdParty->getProviderById($providerId);
            $providerName=$linkedProvider['ThirdParty']['company_name'];
            
            $this->PurchaseOrder->id=$this->request->data['Order']['purchase_order_id'];
            $purchaseOrderArray=[
              'PurchaseOrder'=>[
                'id'=>$this->request->data['Order']['purchase_order_id'],
                //'purchase_order_state_id'=>($this->request->data['Order']['bool_purchase_order_delivery_complete']?PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY:PURCHASE_ORDER_STATE_RECEIVED_PARTIALLY),
                'purchase_order_state_id'=>PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY,
              ],
            ];
            //pr($purchaseOrderArray);
            if (!$this->PurchaseOrder->save($purchaseOrderArray)) {
              echo "problema cambiando el estado de la orden de compra";
              pr($this->validateErrors($this->PurchaseOrder));
              throw new Exception();
            }
                        
            foreach ($this->request->data['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
              if (!empty($purchaseOrderInvoice['invoice_code']) && $purchaseOrderInvoice['invoice_subtotal']  > 0){
                $this->PurchaseOrderInvoice->create();
                
                $purchaseOrderInvoice['purchase_order_id']=$this->request->data['Order']['purchase_order_id'];
                $purchaseOrderInvoice['entry_id']=$purchaseId;
                
                if (!$this->PurchaseOrderInvoice->save($purchaseOrderInvoice)) {
                  echo "problema guardando la factura".$purchaseOrderInvoice['invoice_code'];
                  pr($this->validateErrors($this->PurchaseOrderInvoice));
                  throw new Exception();
                }
              }  
            }     

            if ($previousPurchaseDateTime != $currentPurchaseDateTime){            
              $previousMovements=$this->Order->StockMovement->find('all',[
                'conditions' => [
                  'StockMovement.order_id'=> $id,
                  'StockMovement.product_quantity >'=> 0,
                  'StockMovement.product_id >'=> 0,
                ],
                'contain'=>[
                  'StockItem'=>[
                    'StockItemLog'=>[
                      'order'=>'id ASC',
                      'limit'=>1,
                    ]
                  ],
                ],
              ]);
              if (!empty($previousMovements)){
                
                foreach ($previousMovements as $previousStockMovement){
                  $stockMovementData=[
                    'id'=> $previousStockMovement['StockMovement']['id'],
                    'movement_date'=>$purchaseDate,
                    'name'=>$previousStockMovement['StockMovement']['name'].'|'.$purchaseDateAsString,
                  ];
                  $this->StockMovement->id=$previousStockMovement['StockMovement']['id'];
                  if (!$this->StockMovement->save($stockMovementData)) {
                    echo "problema guardando el movimiento de inventario";
                    pr($this->validateErrors($this->StockMovement));
                    throw new Exception();
                  }
                  $stockItemData=[
                    'id'=> $previousStockMovement['StockItem']['id'],
                    'name'=>$previousStockMovement['StockItem']['name'].'|'.$purchaseDateAsString,
                    'stockitem_creation_date'=>$purchaseDate,
                  ];  
                  
                  $this->StockItem->id=$previousStockMovement['StockItem']['id'];
                  if (!$this->StockItem->save($stockItemData)) {
                    echo "problema actualizando fecha de lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  $stockItemLogData=[
                    'id'=> $previousStockMovement['StockItem']['StockItemLog'][0]['id'],
                    'stockitem_date'=>$purchaseDate,
                    
                  ];
                  $this->StockItemLog->id=$previousStockMovement['StockItem']['StockItemLog'][0]['id'];
                  if (!$this->StockItemLog->save($stockItemLogData)) {
                    echo "problema guardando el estado de lote";
                    pr($this->validateErrors($this->StockItemLog));
                    throw new Exception();
                  }
                }
                
                
                
                
               }
            
            }
            
            /*            
            foreach ($this->request->data['Product'] as $product){
              // four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
              
              // load the product request data into variables
              $productId = $product['product_id'];
              $unitId = $product['unit_id'];
              $productQuantity = $product['product_quantity'];
              $productPrice = $product['product_price'];
              
              if ($productQuantity>0 && $productId>0){
                // calculate the unit price
                $productUnitPrice=$productPrice/$productQuantity;
                
                // get the related product data
                //$linkedProduct=$this->Product->read(null,$productId);
                $this->Product->recursive=-1;
                $linkedProduct=$this->Product->find('first',[
                  'conditions'=>[
                    'Product.id'=>$productId,
                  ],
                ]);
                $productName=$linkedProduct['Product']['name'];
                $itemmovementname=$purchaseDate['day']."_".$purchaseDate['month']."_".$purchaseDate['year']."_".$providerName."_".$orderCode."_".$productName;
                $description="New stockitem ".$productName." (Quantity:".$productQuantity.",Unit Price:".$productUnitPrice.") from Purchase ".$providerName."_".$orderCode;
                
                // STEP 1: SAVE THE STOCK ITEM
                $this->loadModel('StockItem');
                $stockItemData=[
                  'name'=>$itemmovementname,
                  'description'=>$description,
                  'stockitem_creation_date'=>$purchaseDate,
                  'product_id'=>$productId,
                  'unit_id'=>$unitId,
                  'product_unit_price'=>$productUnitPrice,
                  'original_quantity'=>$productQuantity,
                  'remaining_quantity'=>$productQuantity,
                  'warehouse_id'=>$warehouseId,
                ];  
                
                $this->StockItem->create();
                if (!$this->StockItem->save($stockItemData)) {
                  echo "problema guardando el lote";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
                
                // STEP 2: SAVE THE STOCK MOVEMENT
                $stockItemId=$this->StockItem->id;
                
                $stockMovementData=[
                  'movement_date'=>$purchaseDate,
                  'bool_input'=>true,
                  'name'=>$itemmovementname,
                  'description'=>$description,
                  'order_id'=>$purchaseId,
                  'stockitem_id'=>$stockItemId,
                  'product_id'=>$productId,
                  'product_quantity'=>$productQuantity,
                  'unit_id'=>$unitId,
                  'product_unit_price'=>$productUnitPrice,
                  'product_total_price'=>$productPrice,
                ];
                $this->StockMovement->create();
                if (!$this->StockMovement->save($stockMovementData)) {
                  echo "problema guardando el movimiento de inventario";
                  pr($this->validateErrors($this->StockMovement));
                  throw new Exception();
                }
                
                // STEP 3: SAVE THE STOCK ITEM LOG
                $this->loadModel('StockItemLog');
                $stockMovementId=$this->Order->StockMovement->id;
                
                $stockItemLogData=[
                  'stockitem_id'=>$stockItemId,
                  'stock_movement_id'=>$stockMovementId,
                  'stockitem_date'=>$purchaseDate,
                  'product_id'=>$productId,
                  'unit_id'=>$unitId,
                  'product_unit_price'=>$productUnitPrice,
                  'product_quantity'=>$productQuantity,
                  'warehouse_id'=>$warehouseId,
                ];
                $this->StockItemLog->create();
                if (!$this->StockItemLog->save($stockItemLogData)) {
                  echo "problema guardando el estado de lote";
                  pr($this->validateErrors($this->StockItemLog));
                  throw new Exception();
                }
                
                // STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                $this->recordUserActivity($this->Session->read('User.username'),$description);
              }
            }
            */
            $datasource->commit();
            $this->recordUserAction($this->Order->id,"editarEntrada",null);	
            $this->recordUserActivity($this->Session->read('User.username'),"Se editó entrada número ".$this->request->data['Order']['order_code']);
            
            $this->Session->setFlash(__('Se editó la entrada.'),'default',['class' => 'success']);
            //return $this->redirect(['action' => 'resumenEntradas']);
          }
          catch(Exception $e){
            $this->Session->setFlash(__('La entrada no se podía editar.'),'default',['class' => 'error-message']);
            pr($e);
            $datasource->rollback();
          }
        }
      }  
    }
    else {
			$this->request->data = $this->Order->find('first', [
				'conditions' => [
					'Order.id' => $id,
				],
				'contain'=>[
          'PurchaseOrderInvoice',
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
									'StockMovement.order_id !='=>$id,
								],
								'Order',
							],
						],
					],
				],
			]);
      
      $warehouseId=$this->request->data['Order']['warehouse_id'];
      
      foreach ($this->request->data['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
        $requestInvoices[]=['PurchaseOrderInvoice'=>$purchaseOrderInvoice];
      }
      
			$entryProducts=[];
      foreach ($this->request->data['StockMovement'] as $stockMovement){
        if (!array_key_exists($stockMovement['product_id'],$entryProducts)){
          $entryProducts[$stockMovement['product_id']]=[
            'product_id'=>$stockMovement['product_id'],
            'unit_id'=>$stockMovement['unit_id'],
            'product_quantity'=>0,
            'product_price'=>0,
          ];  
        }
        $entryProducts[$stockMovement['product_id']]['product_quantity']+=$stockMovement['product_quantity'];
        $entryProducts[$stockMovement['product_id']]['product_price']+=$stockMovement['product_total_price'];
        
				if (!in_array($stockMovement['id'],$productsInEntry)){
          $productsInEntry[]=$stockMovement['product_id'];
        }
			}
      foreach ($entryProducts as $productId=>$productData){
        $requestProducts[]['Product']=$productData;
      }
    }	
		$this->set(compact('requestProducts'));
    $this->set(compact('requestInvoices'));
    $this->set(compact('warehouseId'));
    
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId,$warehouseId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
		
    //pr($this->request->data['Order']);  
		$plantId=$this->Warehouse->getPlantId($warehouseId);
		$thirdParties = $this->ThirdParty->getActiveProviderList($plantId);
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
	
		$productTypes=$this->PlantProductType->getProductTypesForPlant($plantId);
		
		$productsAll = $this->Product->find('all',[
			'fields'=>['Product.id,Product.name'],
			'conditions' => [
        'OR'=>[
          [
            'ProductType.product_category_id !='=> CATEGORY_PRODUCED,
            'Product.product_type_id !='=> PRODUCT_TYPE_SERVICE,
            'Product.product_type_id'=> array_keys($productTypes),
            'Product.bool_active'=> true,
          ],
          [
            'ProductType.product_category_id !='=> CATEGORY_PRODUCED,
            'Product.product_type_id !='=> PRODUCT_TYPE_SERVICE,
            'Product.product_type_id'=> array_keys($productTypes),
            'Product.id'=> $productsInEntry,
          ]
        ],
      ],
      'recursive'=>0,
      'order'=>'Product.name'
		]);
		$products = null;
		foreach ($productsAll as $product){
			$products[$product['Product']['id']]=$product['Product']['name'];
		}
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','productsPurchasedEarlier'));		
		//pr($this->request->data['Order']);
    $purchaseOrders=$this->PurchaseOrder->getConfirmedPurchaseOrders($warehouseId,$this->request->data['Order']['purchase_order_id']);
		$this->set(compact('purchaseOrders'));
    
    $units=$this->Unit->getUnitList();
    $this->set(compact('units'));
    //pr($units);
    
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
	
  public function editarVenta($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}
    
    $this->loadModel('Product');
		$this->loadModel('ProductType');
    $this->loadModel('ProductionResultCode');
		
    $this->loadModel('ClosingDate');
    
    $this->loadModel('SalesOrder');
    
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
    $this->loadModel('StockMovement');
		
    $this->loadModel('ThirdParty');
    $this->loadModel('ClientType');
		$this->loadModel('Zone');    
    //$this->loadModel('Vehicle');
		
    $this->loadModel('PriceClientCategory');
    
		$this->loadModel('Currency');
		$this->loadModel('Invoice');
    $this->loadModel('AccountingCode');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterInvoice');
    // loaded for editarVenta, not crearVenta
		$this->loadModel('AccountingMovement');
    // loaded for editarVenta, not crearVenta
    $this->loadModel('CashReceiptInvoice');
    
    $this->loadModel('ExchangeRate');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    $this->loadModel('WarehouseProduct');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
		
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $canSeeInventoryCost=$this->UserPageRight->hasUserPageRight('VER_COSTO_INVENTARIO',$userRoleId,$loggedUserId,'All','All');
    $this->set(compact('canSeeInventoryCost'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'Orders','resumenVentasRemisiones');
    $this->set(compact('canSeeAllUsers'));
    
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'Orders','resumenVentasRemisiones');
    $this->set(compact('canSeeAllVendors'));
    
	$canApplyCredit=$this->UserPageRight->hasUserPageRight('AUTORIZACION_CREDITO',$userRoleId,$loggedUserId,'orders','crearVenta');
 
	
    $this->Order->recursive=-1;
    $this->StockItem->recursive=-1;
    $this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		$this->Order->ThirdParty->recursive=-1;
    $this->Order->StockMovementType->recursive=-1;
    $this->AccountingCode->recursive=-1;
		$this->Invoice->recursive=-1;
    
    $vendorUserId=$loggedUserId;
    $recordUserId=$loggedUserId;
    $creditAuthorizationUserId=$loggedUserId;
    //$driverUserId=0;
    //$vehicleId=0;
    $warehouseId=0;
		$boolDelivery='0';
    
		$inventoryDisplayOptions=[
			'0'=>'No mostrar inventario',
			'1'=>'Mostrar inventario',
		];
		$this->set(compact('inventoryDisplayOptions'));
		$inventoryDisplayOptionId=0;
		
    $orderDate=date("Y-m-d",strtotime(date('Y-m-d')));
    
    $adminUsers=$this->User->getActiveUsersForRole(ROLE_ADMIN);
    $adminUserIds=array_keys($adminUsers);
    $this->set(compact('adminUserIds'));
    //pr($adminUserIds);
		
    $genericClientIds=$this->ThirdParty->getGenericClientIds();
    $this->set(compact('genericClientIds'));
    
    $boolInitialLoad=true;
    $salesOrderId=0;
    $requestProducts=[];
    
    //if (!empty($this->request->data)){
    if ($this->request->is(['post', 'put'])){
      $boolInitialLoad='0';
      if (!empty($this->request->data['Product'])){
        foreach ($this->request->data['Product'] as $product){
          if (!empty($product['product_id']) && $product['product_quantity'] > 0){
            $requestProducts[]['Product']=$product;
          }
        }
      }
      
      $salesOrderId=$this->request->data['Order']['sales_order_id'];
      
      $orderDateArray=$this->request->data['Order']['order_date'];
      $orderDateString=$orderDateArray['year'].'-'.$orderDateArray['month'].'-'.$orderDateArray['day'];
      $orderDate=date("Y-m-d",strtotime($orderDateString));
      
      $clientId=$this->request->data['Order']['third_party_id'];
      $currencyId=$this->request->data['Invoice']['currency_id'];
      
      $vendorUserId=$this->request->data['Order']['vendor_user_id'];
      $recordUserId=$this->request->data['Order']['record_user_id'];
      if (!array_key_exists('credit_authorization_user_id',$this->request->data['Order'])){
        $this->request->data['Order']['credit_authorization_user_id']=$this->request->data['credit_authorization_user_id'];
      }
      $creditAuthorizationUserId=$this->request->data['Order']['credit_authorization_user_id'];
      //$driverUserId=$this->request->data['Order']['driver_user_id'];
      //$vehicleId=$this->request->data['Order']['vehicle_id'];
      
      $warehouseId=$this->request->data['Order']['warehouse_id'];  
      $boolDelivery=$this->request->data['Order']['bool_delivery'];
      
      if (empty($this->request->data['refresh'])) {
        //$inventoryDisplayOptionId=$this->request->data['Order']['inventory_display_option_id'];
        
        $clientName=$this->request->data['Order']['client_name'];
        $clientPhone=$this->request->data['Order']['client_phone'];
        $clientMail=$this->request->data['Order']['client_email'];
    
        $productRawMaterialPresent=true;
        $errorMessage="";
        
        $boolMultiplicationOK=true;
        $multiplicationErrorMessage='';
        $sumProductTotals=0;
        $boolProductPricesRegistered=true;
        $productPriceWarning='';
        $boolProductPriceLessThanDefaultPrice='0';
        $productPriceLessThanDefaultPriceError='';
        $boolProductPriceRepresentsBenefit=true;
        $productPriceBenefitError='';
        
        if (!empty($this->request->data['Product'])){
          foreach ($this->request->data['Product'] as $product){
            if (!empty($product['product_id'])  && $product['product_quantity'] > 0){
              $acceptableProductPrice=1000;
              $acceptableProductPrice=$this->Product->getAcceptablePriceForProductClientCostQuantityDate($product['product_id'],$clientId,$product['product_unit_cost'],$product['product_quantity'],$orderDate,$product['raw_material_id']);
              
              $productName=$this->Product->getProductName($product['product_id']);
              $rawMaterialName=($product['raw_material_id'] > 0?($this->Product->getProductName($product['raw_material_id'])):'');
              if (!empty($rawMaterialName)){
                $productName.=(' '.$rawMaterialName.' A');
              }
              $multiplicationDifference=abs($product['product_total_price']-$product['product_quantity']*$product['product_unit_price']);
              if ($multiplicationDifference>=0.01){
                $boolMultiplicationOK='0';
                $multiplicationErrorMessage.="Para producto ".$productName." la cantidad indicada es ".$product['product_quantity']." y el precio unitario ".$product['product_unit_price']." lo que da un producto de multiplicación de ".round($product['product_quantity']*$product['product_unit_price'],2)." pero el total calculado por la fila es de ".$product['product_total_price'].".  ";
              };
                     
              if ($this->Product->getProductTypeId($product['product_id']) == PRODUCT_TYPE_BOTTLE){
                if (empty($product['raw_material_id'])){
                  $productRawMaterialPresent='0';  
                  $errorMessage.="Para producto ".$productName." no se indicó la preforma, es obligatorio indicarlo.  ";
                }          
              }
              if ($product['product_id'] != PRODUCT_SERVICE_OTHER){
                if ($product['default_product_unit_price'] <=0) {
                  $boolProductPricesRegistered='0'; 
                  $productPriceWarning='Producto '.$productName.' no tiene registrado un precio de listado entonces no se podía aplicar un control de precios.  Por favor graba un precio para este producto primero.  ';  
                }
                // 20211004 default_product_price could be tricked into accepting volume price by users with bad intentions by increasing and then decreasing prices, that's why the price is calculated afreshe in $acceptableProductPrice
                // if ($product['product_unit_price'] < $product['default_product_unit_price']) {
                if ($product['product_unit_price'] < $acceptableProductPrice) {
                  $boolProductPriceLessThanDefaultPrice=true; 
                  //$productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$product['product_unit_price'].' pero el precio mínimo establecido es '.$product['default_product_unit_price'].'.  No se permite vender abajo del precio mínimo establecido.  ';  
                  $productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$product['product_unit_price'].' pero el precio mínimo establecido es '.$acceptableProductPrice.'.  No se permite vender abajo del precio mínimo establecido.  '; 
                }
                if ($product['product_unit_price'] < $product['product_unit_cost']) {
                  $boolProductPriceRepresentsBenefit='0'; 
                  if ($userRoleId === ROLE_ADMIN){                
                    $productPriceBenefitError='Producto '.$productName.' tiene un precio '.$product['product_unit_price'].' pero el costo es '.$product['product_unit_cost'].'.  No se permite vender con pérdidas.  ';  
                  }
                  else {
                    $productPriceBenefitError='Precio no autorizado para producto '.$productName.'.  No se editó la venta.  ';  
                  } 
                }
              }
            }
            $sumProductTotals+=$product['product_total_price'];
          }
        }
        
        if (!array_key_exists('bool_credit',$this->request->data['Invoice'])){
          if (array_key_exists('bool_credit',$this->request->data)){
            $this->request->data['Invoice']['bool_credit']=$this->request->data['bool_credit'];
          }
          else {
            $this->request->data['Invoice']['bool_credit']=0;
          }
        }
        if (!array_key_exists('save_allowed',$this->request->data['Order'])){
          if (array_key_exists('save_allowed',$this->request->data)){  
            $this->request->data['Order']['save_allowed']=$this->request->data['save_allowed'];
          }
          else {
            $this->request->data['Order']['save_allowed']=1;
          }
        }
        if (!array_key_exists('credit_authorization_user_id',$this->request->data['Order'])){
          $this->request->data['Order']['credit_authorization_user_id']=$this->request->data['credit_authorization_user_id'];
        }
        $creditAuthorizationUserId=$this->request->data['Order']['credit_authorization_user_id'];
        $this->request->data['Invoice']['credit_authorization_user_id']=$creditAuthorizationUserId;
        if (!array_key_exists('retention_allowed',$this->request->data['Order'])){
          if (array_key_exists('retention_allowed',$this->request->data)){
            $this->request->data['Order']['retention_allowed']=$this->request->data['retention_allowed'];
          }
          else{
            $this->request->data['Order']['retention_allowed']=1;
          }
        }
        
        $saleDate=$this->request->data['Order']['order_date'];
        $saleDateString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
        $dueDateString = $this->Invoice->deconstruct('due_date', $this->request->data['Invoice']['due_date']);
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDateTime=new DateTime($latestClosingDate);
        
        $saleDateArray=[];
        $saleDateArray['year']=$saleDate['year'];
        $saleDateArray['month']=$saleDate['month'];
        $saleDateArray['day']=$saleDate['day'];
        
        $orderCode=$this->request->data['Order']['order_code'];
        
        $namedSales=$this->Order->find('all',[
          'conditions'=>[
            'Order.order_code'=>$orderCode,
            'Order.stock_movement_type_id'=>MOVEMENT_SALE,
            'Order.id !='=>$id,
          ],
        ]);
        
        if (count($namedSales) > 0){
          $this->Session->setFlash(__('Ya existe una venta con el mismo código!  No se guardó la salida.'), 'default',['class' => 'error-message']);
        }
        elseif ($saleDateString > date('Y-m-d 23:59:59')){
          $this->Session->setFlash(__('La fecha de venta no puede estar en el futuro!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        elseif ($saleDateString > date("Y-m-d 23:59:59",strtotime($dueDateString))){
          $this->Session->setFlash(__('La fecha de vencimiento no puede venir antes de la fecha de venta!  No se editó la venta.'), 'default',['class' => 'error-message']);
        }
        elseif ($this->request->data['Order']['save_allowed'] == 0){
          $this->Session->setFlash('No se permite guardar esta venta de crédito!  Si está el gerente, marca la casilla de permitir guardar venta.  No se guardó la venta.', 'default',['class' => 'error-message']);
        }
        elseif ($saleDateString<$latestClosingDatePlusOne){
          $this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se pueden realizar cambios y no se editó la venta.'), 'default',['class' => 'error-message']);
        }
        elseif (empty($clientId) && empty($clientName)){
          $this->Session->setFlash('Se debe registrar el nombre del cliente.  No se guardó la venta.', 'default',['class' => 'error-message']);
        }
        elseif (empty($clientId) && empty($clientPhone) && empty($clientMail)){
          $this->Session->setFlash('Se debe registrar el teléfono o el correo electrónico del cliente.  No se guardó la venta.', 'default',['class' => 'error-message']);
        }
        elseif ($this->request->data['Order']['third_party_id']==0){
          $this->Session->setFlash(__('Se debe seleccionar el cliente para la venta!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        elseif (!$this->request->data['Order']['client_generic'] && empty($this->request->data['Order']['client_type_id'])){
          //pr($this->request->data['Order']);
          $this->Session->setFlash('Se debe registrar el tipo de cliente.  No se guardó la orden de venta.', 'default',['class' => 'error-message']);
        }
        elseif (!$this->request->data['Order']['client_generic'] && empty($this->request->data['Order']['zone_id'])){
          $this->Session->setFlash('Se debe registrar la zona del cliente.  No se guardó la orden de venta.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolMultiplicationOK){
          $this->Session->setFlash($multiplicationErrorMessage.'No se guardó la venta.', 'default',['class' => 'error-message']);
        } 
        elseif (!$productRawMaterialPresent){
          $this->Session->setFlash($errorMessage.'No se guardó la venta.', 'default',['class' => 'error-message']);
        }
        elseif (abs($sumProductTotals-$this->request->data['Order']['price_subtotal']) > 0.01){
          pr ($this->Request->data['Order']);
          $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$sumProductTotals.' pero el total calculado es '.$this->request->data['Order']['price_subtotal'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        //elseif ($this->request->data['Invoice']['currency_id'] == CURRENCY_USD && (abs($this->request->data['Invoice']['sub_total_price']-$sumProductTotals/$this->request->data['Order']['exchange_rate']) > 1)){
        //  echo "el subtotal de la factura ".$this->request->data['Invoice']['sub_total_price']." no iguala el precio sumado de los productos ".($sumProductTotals/$this->request->data['Order']['exchange_rate'])." en base a una tasa de cambio ".$this->request->data['Order']['exchange_rate'];
        //  throw new Exception();
        //}
        //elseif ($this->request->data['Invoice']['currency_id'] != CURRENCY_USD && (abs($this->request->data['Invoice']['sub_total_price']-$sumProductTotals) > 1)){
        //  echo "el subtotal de la factura ".$this->request->data['Invoice']['sub_total_price']." no iguala el precio sumado de los productos ".$sumProductTotals;
        //  throw new Exception();
        //}
        elseif (abs($this->request->data['Order']['price_total']-$this->request->data['Order']['price_iva']-$this->request->data['Order']['price_subtotal'])>0.01){
          $this->Session->setFlash('La suma del subtotal '.$this->request->data['Order']['price_subtotal'].' y el IVA '.$this->request->data['Order']['price_iva'].' no igualan el precio total '.$this->request->data['Order']['price_total'].', la diferencia es de '.(abs($this->request->data['Order']['price_total']-$this->request->data['Order']['price_iva']-$this->request->data['Order']['price_subtotal'])).'.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['Order']['price_total'])){
          $this->Session->setFlash(__('El total de la orden de venta tiene que ser mayor que cero.  No se guardó la orden.'), 'default',['class' => 'error-message']);
        }
        else if ($this->request->data['Invoice']['bool_retention'] && strlen($this->request->data['Invoice']['retention_number'])==0){
          $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        else if ($this->request->data['Invoice']['bool_retention'] && abs(0.02*$this->request->data['Order']['price_subtotal']-$this->request->data['Order']['retention_amount']) > 0.01){
          $this->Session->setFlash(__('La retención debería igualar el 2% del subtotal de la venta!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }  
        else if ($this->request->data['Order']['bool_delivery'] && empty($this->request->data['Order']['delivery_address'])){
          $this->Session->setFlash('Se indicó que.la factura se debe entregar a domicilio pero no se indicó la dirección de entrega.  La dirección de entrega se debe registrar.', 'default',['class' => 'error-message']);
        }  
        elseif (!$boolProductPricesRegistered && $userRoleId != ROLE_ADMIN){
          $this->Session->setFlash($productPriceWarning.'No se editó la  venta.', 'default',['class' => 'error-message']);
        }
        elseif ($boolProductPriceLessThanDefaultPrice && $userRoleId != ROLE_ADMIN){
          $this->Session->setFlash($productPriceLessThanDefaultPriceError.'No se editó la venta.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolProductPriceRepresentsBenefit){
          $this->Session->setFlash($productPriceBenefitError.'No se editó la venta.', 'default',['class' => 'error-message']);
        }
        elseif ($this->request->data['Invoice']['bool_annulled']){
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          // first remove existing data
          $stockMovementsOriginalSale=$this->Order->StockMovement->find('all',[
            'fields'=>[
              'StockMovement.product_id,StockMovement.product_quantity,StockMovement.stockitem_id,StockMovement.service_unit_cost,StockMovement.service_total_cost,StockMovement.product_total_price,StockMovement.id, StockMovement.description, StockMovement.movement_date',
            ],
            'conditions' => ['StockMovement.order_id'=> $id],
            'contain'=>[
              'StockItem'=>[
                'fields'=> ['remaining_quantity','raw_material_id','production_result_code_id','remaining_quantity','description'],
                'StockItemLog'=>['fields'=>['StockItemLog.id,StockItemLog.stockitem_date'],]
              ],
              'Product'=>['fields'=> ['id','name','product_type_id'],],
            ],						
          ]);						
          
          $originalInvoice=$this->Invoice->find('first',[
            'conditions'=>[
              'Invoice.order_id'=>$id,
            ],
            'contain'=>[
              'AccountingRegisterInvoice'=>[
                'AccountingRegister'=>[
                  'AccountingMovement'
                ],
                'Invoice',
              ],
            ],
          ]);						
          try {
            if (!empty($stockMovementsOriginalSale)){
              foreach ($stockMovementsOriginalSale as $originalStockMovement){						
                // set all stockmovements to 0
                $annulledStockMovementData=[];
                $annulledStockMovementData['id']=$originalStockMovement['StockMovement']['id'];
                $annulledStockMovementData['description']=$originalStockMovement['StockMovement']['description']." cancelled through editing on ".date('Y-m-d');
                $annulledStockMovementData['product_quantity']=0;
                $annulledStockMovementData['service_unit_cost']=0;
                $annulledStockMovementData['service_total_cost']=0;
                $annulledStockMovementData['product_total_price']=0;								
                if (!$this->StockMovement->save($annulledStockMovementData)) {
                  echo "problema al guardar el movimiento de salida";
                  pr($this->validateErrors($this->StockMovement));
                  throw new Exception();
                }
                if ($originalStockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                  // restore the stockitems to their previous level
                  $annulledStockItemData=[];
                  $annulledStockItemData['id']=$originalStockMovement['StockItem']['id'];
                  $annulledStockItemData['description']=$originalStockMovement['StockItem']['description']." added back quantity ".$originalStockMovement['StockMovement']['product_quantity']." through editing on ".date('Y-m-d')." for order ".$id;
                  $annulledStockItemData['remaining_quantity']=$originalStockMovement['StockItem']['remaining_quantity']+$originalStockMovement['StockMovement']['product_quantity'];
                  if (!$this->StockItem->save($annulledStockItemData)) {
                    echo "problema al guardar el lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  $this->recreateStockItemLogs($originalStockMovement['StockItem']['id']);
                }
                
              }
            }					
            if (!empty($originalInvoice)){				
              if (!empty($originalInvoice['AccountingRegisterInvoice'])){
                foreach ($originalInvoice['AccountingRegisterInvoice'] as $originalAccountingRegisterInvoice){
                  if (!empty($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'])){
                    foreach ($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'] as $originalAccountingMovement){
                      $this->AccountingMovement->delete($originalAccountingMovement['id']);
                    }
                  }
                  $this->AccountingRegister->delete($originalAccountingRegisterInvoice['AccountingRegister']['id']);
                  $this->AccountingRegisterInvoice->delete($originalAccountingRegisterInvoice['id']);
                }
              }
              $this->Invoice->delete($originalInvoice['Invoice']['id']);
            }						
            $datasource->commit();
            $this->recordUserActivity($this->Session->read('User.username'),"Se removieron los datos viejos para la anulación de venta ".$this->request->data['Order']['order_code']);
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('Problema al eliminar los datos viejos en la anulación.'), 'default',['class' => 'error-message']);
          }
          // then save the minimum data for the annulled invoice/order				
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          try {
            //pr($this->request->data);
            $orderData=[
              'Order'=>[
                'id'=>$id,
                'stock_movement_type_id'=>MOVEMENT_SALE,
                'order_date'=>$this->request->data['Order']['order_date'],
                'order_code'=>$this->request->data['Order']['order_code'],
                'third_party_id'=>$this->request->data['Order']['third_party_id'],
                'bool_annulled'=>true,
                'warehouse_id'=>$warehouseId,
                'total_price'=>0,
              ],
            ];
            
            $this->Order->id=$id;
            if (!$this->Order->save($orderData)) {
              echo "Problema guardando la venta";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            }
            $orderId=$this->Order->id;
            
            $invoiceData=[
              'Invoice'=>[
                'id'=>$this->request->data['Invoice']['id'],
                'order_id'=>$orderId,
                'invoice_code'=>$this->request->data['Order']['order_code'],
                'invoice_date'=>$this->request->data['Order']['order_date'],
                'bool_annulled'=>true,
                'warehouse_id'=>$warehouseId,
                'client_id'=>$this->request->data['Order']['third_party_id'],
                'sub_total_price'=>0,
                'iva_price'=>0,
                'total_price'=>0,
                'currency_id'=>CURRENCY_CS,
              ],
            ];   
            
            $this->Invoice->id=$this->request->data['Invoice']['id'];
            if (!$this->Invoice->save($invoiceData)) {
              echo "Problema guardando la factura";
              pr($this->validateErrors($this->Invoice));
              throw new Exception();
            }
            
            $datasource->commit();
            $this->recordUserAction();  
            // SAVE THE USERLOG 
            $this->recordUserActivity($this->Session->read('User.username'),"Se anuló la venta con número ".$this->request->data['Order']['order_code']);
            $this->Session->setFlash(__('Se anuló la venta '.$this->request->data['Order']['order_code'].'.'),'default',['class' => 'success'],'default',['class' => 'success']);
            //return $this->redirect(array('action' => 'resumenVentasRemisiones'));
            if (!empty($this->request->data['saveAndNew'])){
              return $this->redirect(['action' => 'crearVenta']);
            }
            else {
              return $this->redirect(['action' => 'imprimirVenta',$orderId]);  
            }
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
          }
        }
        elseif ($this->request->data['Order']['price_total']==0){
          $this->Session->setFlash(__('El precio total no puede ser cero para una venta que no está anulada!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        else if (!$this->request->data['Invoice']['bool_credit'] && $this->request->data['Invoice']['cashbox_accounting_code_id']==0){
          $this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una factura de contado!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }
        else if ($this->request->data['Invoice']['bool_retention'] && strlen($this->request->data['Invoice']['retention_number'])==0){
          $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',['class' => 'error-message']);
        }			
        else {
          // 20170420 REMOVE EXISTING DATA
          $stockMovementsOriginalSale=$this->Order->StockMovement->find('all',[
            'fields'=>[
              'StockMovement.product_id,StockMovement.product_id,StockMovement.product_quantity,StockMovement.stockitem_id,StockMovement.service_unit_cost,StockMovement.service_total_cost,StockMovement.product_total_price,StockMovement.id, StockMovement.description, StockMovement.movement_date',
            ],
            'conditions' => ['StockMovement.order_id'=> $id],
            'contain'=>[
              'StockItem'=>[
                'fields'=> ['remaining_quantity','raw_material_id','production_result_code_id','remaining_quantity','description'],
                'StockItemLog'=>['fields'=>['StockItemLog.id,StockItemLog.stockitem_date'],],
              ],
              'Product'=>['fields'=> ['id','name','product_type_id'],],
            ],
          ]);						
          $originalInvoice=$this->Invoice->find('first',[
            'conditions'=>[
              'Invoice.order_id'=>$id,
            ],
            'contain'=>[
              'AccountingRegisterInvoice'=>[
                'AccountingRegister'=>[
                  'AccountingMovement'
                ],
                'Invoice',
              ],
            ],
          ]);	
          //pr($stockMovementsOriginalSale);
          //pr($originalInvoice);
          $oldDataRemoved='0';
          $datasource=$this->Order->getDataSource();
          $datasource->begin();
          try {
            if (!empty($stockMovementsOriginalSale)){
              foreach ($stockMovementsOriginalSale as $originalStockMovement){						
                // set all stockmovements to 0
                $annulledStockMovementData=[];
                $annulledStockMovementData['id']=$originalStockMovement['StockMovement']['id'];
                $annulledStockMovementData['description']=$originalStockMovement['StockMovement']['description']." cancelled through editing on ".date('Y-m-d');
                $annulledStockMovementData['product_quantity']=0;
                $annulledStockMovementData['service_unit_cost']=0;		
                $annulledStockMovementData['service_total_cost']=0;		
                $annulledStockMovementData['product_total_price']=0;								
                if (!$this->StockMovement->save($annulledStockMovementData)) {
                  echo "problema al guardar el movimiento de salida";
                  pr($this->validateErrors($this->StockMovement));
                  throw new Exception();
                }
                if ($originalStockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                  // restore the stockitems to their previous level
                  $annulledStockItemData=[];
                  $annulledStockItemData['id']=$originalStockMovement['StockItem']['id'];
                  $annulledStockItemData['description']=$originalStockMovement['StockItem']['description']." added back quantity ".$originalStockMovement['StockMovement']['product_quantity']." through editing on ".date('Y-m-d')." for order ".$id;
                  $annulledStockItemData['remaining_quantity']=$originalStockMovement['StockItem']['remaining_quantity']+$originalStockMovement['StockMovement']['product_quantity'];
                  //if ($originalStockMovement['StockItem']['id']==8907){
                  //  pr($annulledStockItemData);
                  //}
                  if (!$this->StockItem->save($annulledStockItemData)) {
                    echo "problema al guardar el lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  $this->recreateStockItemLogs($originalStockMovement['StockItem']['id']);
                }
                
              }
            }
            
            if (!empty($originalInvoice)){				
              if (!empty($originalInvoice['AccountingRegisterInvoice'])){
                foreach ($originalInvoice['AccountingRegisterInvoice'] as $originalAccountingRegisterInvoice){
                  //pr($originalAccountingRegisterInvoice);
                  if (!empty($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'])){
                    foreach ($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'] as $originalAccountingMovement){
                      $this->AccountingMovement->delete($originalAccountingMovement['id']);
                    }
                  }
                  
                  $this->AccountingRegister->delete($originalAccountingRegisterInvoice['AccountingRegister']['id']);
                  $this->AccountingRegisterInvoice->delete($originalAccountingRegisterInvoice['id']);
                }
              }
              $this->Invoice->delete($originalInvoice['Invoice']['id']);
              
              if ($originalInvoice['Invoice']['sales_order_id'] > 0 && $originalInvoice['Invoice']['sales_order_id'] != $this->request->data['Order']['sales_order_id']){
                $this->SalesOrder->id=$originalInvoice['Invoice']['sales_order_id'];
                $salesOrderArray=[
                  'SalesOrder'=>[
                    'id'=>$originalInvoice['Invoice']['sales_order_id'],
                    'bool_invoice'=>'0',
                    'invoice_id'=>0,
                  ]
                ];
                if (!$this->SalesOrder->save($salesOrderArray)) {
                  echo "Problema removiendo la asociación con la orden de venta previa";
                  pr($this->validateErrors($this->SalesOrder));
                  throw new Exception();
                }
              }
            }
            
            $datasource->commit();
            $this->recordUserActivity($this->Session->read('User.username'),"Se removieron los datos viejos para venta ".$this->request->data['Order']['order_code']);
            $oldDataRemoved=true;
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',['class' => 'error-message']);
          }
          //$restoredStockItemForTaponVerde=$this->StockItem->find('first',array(
          //  'conditions'=>array('id'=>8907),
          //  'contain'=>'StockItemLog',
          //));
          //pr($restoredStockItemForTaponVerde);
          
          if ($oldDataRemoved){
            $this->request->data['Order']['id']=$id;
            $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
            // get the relevant information of the purchase that was just saved
            $orderId=$id;
            $saleDate=$this->request->data['Order']['order_date'];
            $orderCode=$this->request->data['Order']['order_code'];		
            
            $newDataSaved='0';
            
            $saleItemsOK=true;
            $exceedingItems="";
            
            $productMultiplicationOk=true;
            $productMultiplicationWarning="";
            
            $productTotalSumBasedOnProductTotals=0;
            
            $productCount=0;
            $products=[];
            foreach ($this->request->data['Product'] as $product){
              //pr($product);
              // keep track of number of rows so that in case of an error jquery displays correct number of rows again
              if ($product['product_id']>0){
                $productCount++;
              }
              // only process lines where product_quantity and product id have been filled out
              if ($product['product_quantity']>0 && $product['product_id']>0){
                $products[]=$product;
                $quantityEntered=$product['product_quantity'];
                $productId = $product['product_id'];
                $productionResultCodeId = $product['production_result_code_id'];
                $rawMaterialId = $product['raw_material_id'];
                
                $productName=$this->Product->getProductName($product['product_id']);
                $productTypeId=$this->Product->getProductTypeId($product['product_id']);
                if ($productTypeId != PRODUCT_TYPE_SERVICE){  
                  if ($this->Product->getProductCategoryId($productId) == CATEGORY_PRODUCED){
                    if ($this->WarehouseProduct->hasWarehouse($productId,$warehouseId) && $warehouseId != WAREHOUSE_INJECTION){
                      $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($productId,$rawMaterialId,$productionResultCodeId,$saleDateString,$warehouseId,true);
                    }
                    elseif ($this->WarehouseProduct->hasWarehouse($productId,WAREHOUSE_INJECTION)){
                      $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productId,$saleDateString,WAREHOUSE_INJECTION,true);
                    }
                  }
                  else {
                    if ($this->WarehouseProduct->hasWarehouse($productId,$warehouseId)){
                      $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productId,$saleDateString,$warehouseId,true);
                    }
                    elseif ($this->WarehouseProduct->hasWarehouse($productId,WAREHOUSE_INJECTION)){
                      $quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productId,$saleDateString,WAREHOUSE_INJECTION,true);  
                    }  
                  }
                  //compare the quantity requested and the quantity in stock
                  if ($quantityEntered>$quantityInStock){
                    $saleItemsOK='0';
                    $exceedingItems.=__("Para producto ".$productName." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
                  }
                }
              }
            }
            $creditBlocked=true;
            $creditBlockMessage="";
            $unpaidBlocked=true;
            $unpaidBlockMessage="";
            
            $boolCreditAuthorized='0';
            if ($salesOrderId > 0){
              $salesOrder=$this->SalesOrder->find('first',[
                'conditions'=>[
                  'SalesOrder.id'=>$salesOrderId,
                ],
                'recursive'=>-1,
              ]);
              if (!empty($salesOrder)){
                //pr($salesOrder);
                if (in_array($salesOrder['SalesOrder']['credit_authorization_user_id'],$adminUserIds) && $salesOrder['SalesOrder']['bool_credit']){
                  $boolCreditAuthorized=true;
                }
              }
            } 
          
            if(!$this->request->data['Invoice']['bool_credit']){
              $creditBlocked='0';
              $unpaidBlocked='0';
            }
            else {
              $clientCreditStatus=$this->ThirdParty->getClientCreditStatus($this->request->data['Order']['third_party_id']);
              //pr($clientCreditStatus);
              $creditUsedBeforeInvoice=$clientCreditStatus['ThirdParty']['pending_payment'];
              $creditUsedWithThisInvoice=$this->request->data['Order']['price_total'];
              if($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
                $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($saleDateString);
                $creditUsedWithThisInvoice=round($creditUsedWithThisInvoice*$applicableExchangeRate,2);
              }
              $creditUsedAfterInvoice= $creditUsedBeforeInvoice + $creditUsedWithThisInvoice;
              $creditLimit=$clientCreditStatus['ThirdParty']['credit_amount'];
              
              if (($creditLimit < $creditUsedAfterInvoice) && $canApplyCredit!=1){
                $creditBlockMessage.="El cliente ".$clientCreditStatus['ThirdParty']['company_name']." tiene un límite de crédito de ".$creditLimit." y ya tiene pagos pendientes para un total de C$ ".$creditUsedBeforeInvoice.".  Con el total de esta factura (C$ ".$creditUsedWithThisInvoice.") el monto total que se debe (C$ ".$creditUsedAfterInvoice.") excede el límite de crédito.";
              }
              else {
                $creditBlocked='0';
              }
              
              $pendingInvoices=$this->Invoice->find('all',[
                'fields'=>[
                  'Invoice.id','Invoice.invoice_code',
                  'Invoice.total_price','Invoice.currency_id',
                  'Invoice.invoice_date','Invoice.due_date',
                  'Invoice.client_id',
                  'Currency.abbreviation','Currency.id',
                  'Invoice.order_id',
                ],
                'conditions'=>[
                  'Invoice.due_date <'=>date('Y-m-d'),
                  'Invoice.bool_annulled'=>'0',
                  'Invoice.bool_paid'=>'0',
                  'Invoice.client_id'=>$this->request->data['Order']['third_party_id'],
                ],
                'order'=>'Invoice.invoice_date ASC',
              ]);
              
              if (!empty($pendingInvoices)){
                $unpaidBlockMessage="El cliente ".$clientCreditStatus['ThirdParty']['company_name']." tiene facturas de crédito que vencieron: ";
                $counter=0;
                foreach ($pendingInvoices as $pendingInvoice){
                  $unpaidBlockMessage.=$pendingInvoice['Invoice']['invoice_code']." (".($pendingInvoice['Invoice']['currency_id'] == CURRENCY_USD?"US$":"C$")." ".$pendingInvoice['Invoice']['total_price'].")";
                  if ($counter<count($pendingInvoices)-1){
                    $unpaidBlockMessage.=",";
                  }
                  $counter++;
                } 
                $unpaidBlockMessage.=". ";
                //$unpaidBlockMessage="Revise el ".$this->Html->link('estado de crédito del cliente',['controller'=>'invoices','action'=>'verFacturasPorCobrar',$this->request->data['Order']['third_party_id']])."!";
              }
              else {
                $unpaidBlocked='0';  
              }
              
              if ($userRoleId == ROLE_ADMIN || $canApplyCredit==1){
                $creditBlocked='0';
                $unpaidBlocked='0';  
              }
            }
            
            //echo "saleItemsOK is ".$saleItemsOK."<br>";
            //echo "exceedingItems is ".$exceedingItems."<br>";
            //echo 'creditBlocked is '.$creditBlocked.'<br/>';  
            //echo 'unpaidBlocked is '.$unpaidBlocked.'<br/>';  
            if (!empty($exceedingItems)){
              $exceedingItems.='Por favor corriga e intente de nuevo';
              //echo "exceedingItems is ".$exceedingItems."<br>";
            }			
            
            if (!$saleItemsOK){
              $this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
            }
            elseif (($creditBlocked || $unpaidBlocked) && $userRoleId != ROLE_ADMIN && !$boolCreditAuthorized){
              //echo 'credit not ok<br/>';  
              //echo 'creditBlocked is '.$creditBlocked.'<br/>';  
              //echo 'unpaidBlocked is '.$unpaidBlocked.'<br/>';  
              
              $this->Session->setFlash($creditBlockMessage.$unpaidBlockMessage.'  No se guardó la factura de crédito.', 'default',['class' => 'error-message']);
            }
            else{
              $totalPriceProducts=0;
              
              $datasource=$this->Order->getDataSource();
              $datasource->begin();
              try {
                $currency_id=$this->request->data['Invoice']['currency_id'];
              
                $retention_invoice=$this->request->data['Order']['retention_amount'];
                $sub_total_invoice=$this->request->data['Order']['price_subtotal'];
                $ivaInvoice=$this->request->data['Order']['price_iva'];
                $totalInvoice=$this->request->data['Order']['price_total'];
            
                // if all products are in stock, proceed with the sale 
                $this->Order->create();
                $this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
                $this->request->data['Order']['bool_annulled']='0';
                // ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
                if ($currency_id==CURRENCY_USD){
                  $this->request->data['Order']['total_price']=$sub_total_invoice*$this->request->data['Order']['exchange_rate'];
                }
                else {
                  $this->request->data['Order']['total_price']=$sub_total_invoice;
                }
              
                if (!$this->Order->save($this->request->data)) {
                  echo "Problema guardando la salida";
                  pr($this->validateErrors($this->Order));
                  throw new Exception();
                }
              
                $orderId=$this->Order->id;
                $orderCode=$this->request->data['Order']['order_code'];
              
                $this->Invoice->create();
                $this->request->data['Invoice']['order_id']=$orderId;
                $this->request->data['Invoice']['sales_order_id']=$salesOrderId;
                $this->request->data['Invoice']['warehouse_id']=$warehouseId;
                $this->request->data['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
                $this->request->data['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
                $this->request->data['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
                
                $this->request->data['Invoice']['sub_total_price']=$this->request->data['Order']['price_subtotal'];
                $this->request->data['Invoice']['iva_price']=$this->request->data['Order']['price_iva'];
                $this->request->data['Invoice']['total_price']=$this->request->data['Order']['price_total'];
                $this->request->data['Invoice']['retention_amount']=$this->request->data['Order']['retention_amount'];
                
                if ($this->request->data['Invoice']['bool_credit']){
                  $this->request->data['Invoice']['bool_retention']='0';
                  $this->request->data['Invoice']['retention_amount']=0;
                  $this->request->data['Invoice']['retention_number']="";
                }
                else {
                  $this->request->data['Invoice']['bool_paid']=true;
                }
            
                if (!$this->Invoice->save($this->request->data)) {
                  echo "Problema guardando la factura";
                  pr($this->validateErrors($this->Invoice));
                  throw new Exception();
                }
                
                $invoiceId=$this->Invoice->id;
                
                if ($salesOrderId > 0){
                  $this->SalesOrder->id=$salesOrderId;
                  // keep the salesorder delivery state consistent with the order delivery state
                  $salesOrderArray=[
                    'SalesOrder'=>[
                      'id'=>$salesOrderId,
                      'bool_invoice'=>true,
                      'invoice_id'=>$invoiceId,
                      'bool_delivery'=>$this->request->data['Order']['bool_delivery'],
                    ]
                  ];
                  if (!$this->SalesOrder->save($salesOrderArray)) {
                    echo "Problema actualizando la orden de venta";
                    pr($this->validateErrors($this->SalesOrder));
                    throw new Exception();
                  }
                  
                }
                
                if ($this->request->data['Order']['delivery_id'] > 0){
                  $this->loadModel('Delivery');
                  $deliveryArray=[
                    'Delivery'=>[
                      'id'=>$this->request->data['Order']['delivery_id'],
                      'order_id'=>$orderId,
                    ],
                  ];
                  $this->Delivery->id=$this->request->data['Order']['delivery_id'];  
                  if (!$this->Delivery->save($deliveryArray)) {
                    echo "Problema actualizando la entrega a domicilio";
                    pr($this->validateErrors($this->Delivery));
                    throw new Exception();
                  }         
                }
                
                // now prepare the accounting registers
                
                // if the invoice is with credit, save one accounting register; 
                // debit=cuentas por cobrar clientes 101-004-001, credit = ingresos por venta 401, amount = subtotal
                
                // if the invoice is paid with cash, save two or three accounting register; 
                // debit=caja selected by client, credit = ingresos por venta 401, amount = total
                // debit=?, credit = ?, amount = iva
                // if bool_retention is true
                // debit=?, credit = ?, amount = retention
                
                if ($currency_id == CURRENCY_USD){
                  $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateString);
                  //pr($applicableExchangeRate);
                  $retention_CS=round($retention_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                  $sub_total_CS=round($sub_total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                  $ivaCS=round($ivaInvoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                  $totalCS=round($totalInvoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
                }
                else {
                  $retention_CS=$retention_invoice;
                  $sub_total_CS=$sub_total_invoice;
                  $ivaCS=$ivaInvoice;
                  $totalCS=$totalInvoice;
                }
                $this->AccountingCode->recursive=-1;
                if ($this->request->data['Invoice']['bool_credit']){
                  $clientId=$this->request->data['Order']['third_party_id'];
                  $thisClient=$this->ThirdParty->find('first',array(
                    'conditions'=>array(
                      'ThirdParty.id'=>$clientId,
                    ),
                    'recursive'=>-1,
                  ));
                
                  $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
                  $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
                  $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
                  $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
                  $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
                  $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$ivaCS;
                  $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
                  
                  if (empty($thisClient['ThirdParty']['accounting_code_id'])){
                    $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
                    //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES);
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES
                      ),
                    ));
                  }
                  else {								
                    $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
                    //$accountingCode=$this->AccountingCode->read(null,$thisClient['ThirdParty']['accounting_code_id']);
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>$thisClient['ThirdParty']['accounting_code_id']
                      ),
                    ));
                  }
                  $accountingRegisterData['AccountingMovement'][0]['concept']="A cobrar Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS;
                  
                  $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA
                    ),
                  ));
                  $accountingRegisterData['AccountingMovement'][1]['concept']="Ingresos Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
                  
                  if ($this->request->data['Invoice']['bool_iva']){
                    $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                    //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR
                      ),
                    ));
                    
                    $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$ivaCS;
                  }
                  
                  //pr($accountingRegisterData);
                  $accountingRegisterId=$this->saveAccountingRegisterData($accountingRegisterData,true);
                  $this->recordUserAction($this->AccountingRegister->id,"add",null);
              
                  $AccountingRegisterInvoiceData=[];
                  $AccountingRegisterInvoiceData['accounting_register_id']=$accountingRegisterId;
                  $AccountingRegisterInvoiceData['invoice_id']=$invoiceId;
                  $this->AccountingRegisterInvoice->create();
                  if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                    pr($this->validateErrors($this->AccountingRegisterInvoice));
                    echo "problema al guardar el lazo entre asiento contable y factura";
                    throw new Exception();
                  }
                  //echo "link accounting register sale saved<br/>";					
                }
                else {
                  $accountingRegisterData['AccountingRegister']['concept']="Venta ".$orderCode;
                  $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
                  $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
                  $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
                  $accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
                  $accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$ivaCS;
                  $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
                  
                  if (!$this->request->data['Invoice']['bool_retention']){
                    $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                    //$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                      ),
                    ));
                    $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS;
                  }
                  else {
                    // with retention
                    $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
                    //$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>$this->request->data['Invoice']['cashbox_accounting_code_id'],
                      ),
                    ));
                    $accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$ivaCS-$retention_CS;
                  }
                  
                  $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
                  $accountingCode=$this->AccountingCode->find('first',array(
                    'conditions'=>array(
                      'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA
                    ),
                  ));
                  $accountingRegisterData['AccountingMovement'][1]['concept']="Subtotal Venta ".$orderCode;
                  $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
                  
                  if ($this->request->data['Invoice']['bool_iva']){
                    $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
                    //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>ACCOUNTING_CODE_IVA_POR_PAGAR
                      ),
                    ));
                    $accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$ivaCS;
                  }
                  if ($this->request->data['Invoice']['bool_retention']){
                    $accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
                    //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_RETENCIONES_POR_COBRAR);
                    $accountingCode=$this->AccountingCode->find('first',array(
                      'conditions'=>array(
                        'AccountingCode.id'=>ACCOUNTING_CODE_RETENCIONES_POR_COBRAR
                      ),
                    ));
                    $accountingRegisterData['AccountingMovement'][3]['concept']="Retención Venta ".$orderCode;
                    $accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][3]['debit_amount']=$retention_CS;
                  }
                  
                  //pr($accountingRegisterData);
                  $accountingRegisterId=$this->saveAccountingRegisterData($accountingRegisterData,true);
                  $this->recordUserAction($this->AccountingRegister->id,"add",null);
                  //echo "accounting register saved for cuentas cobrar clientes<br/>";
              
                  $AccountingRegisterInvoiceData=[];
                  $AccountingRegisterInvoiceData['accounting_register_id']=$accountingRegisterId;
                  $AccountingRegisterInvoiceData['invoice_id']=$invoiceId;
                  $this->AccountingRegisterInvoice->create();
                  if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
                    pr($this->validateErrors($this->AccountingRegisterInvoice));
                    echo "problema al guardar el lazo entre asiento contable y factura";
                    throw new Exception();
                  }
                  //echo "link accounting register sale saved<br/>";	
                }
              
                foreach ($products as $product){
                  // four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
                  //pr($product);
                  
                  // load the product request data into variables
                  $productId = $product['product_id'];
                  $productCategoryId = $this->Product->getProductCategoryId($productId);
                  $productionResultCodeId =0;
                  $rawMaterialId=0;
                  
                  if ($productCategoryId==CATEGORY_PRODUCED){
                    $productionResultCodeId = $product['production_result_code_id'];
                    $rawMaterialId = $product['raw_material_id'];
                  }
                  if (array_key_exists('service_unit_cost',$product)){
                    $service_unit_cost=$product['service_unit_cost'];
                  }
                  else {
                    $service_unit_cost=0;
                  }
                  $productUnitPrice=$product['product_unit_price'];
                  $productQuantity = $product['product_quantity'];
                  
                  if ($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
                    $productUnitPrice*=$this->request->data['Order']['exchange_rate'];
                  }
                  
                  // get the related product data
                  //$linkedProduct=$this->Product->read(null,$productId);
                  $this->Product->recursive=-1;
                  $linkedProduct=$this->Product->find('first',[
                  'conditions'=>['Product.id'=>$productId,],
                ]);
                  $productName=$linkedProduct['Product']['name'];
                  if ($linkedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                    // STEP 1: SAVE THE STOCK ITEM(S)
                    // first prepare the materials that will be taken out of stock
                    
                    if ($productCategoryId==CATEGORY_PRODUCED){
                      if ($this->WarehouseProduct->hasWarehouse($productId,$warehouseId) && $warehouseId != WAREHOUSE_INJECTION){
                        $usedMaterials= $this->StockItem->getFinishedMaterialsForSale($productId,$productionResultCodeId,$productQuantity,$rawMaterialId,$saleDateString,$warehouseId);		
                      }
                      elseif ($this->WarehouseProduct->hasWarehouse($productId,WAREHOUSE_INJECTION)){
                        $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$saleDateString,WAREHOUSE_INJECTION);		  
                      }
                    }
                    else {
                      if ($this->WarehouseProduct->hasWarehouse($productId,$warehouseId)){
                        $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$saleDateString,$warehouseId);		
                      }
                      elseif ($this->WarehouseProduct->hasWarehouse($productId,WAREHOUSE_INJECTION)){
                        $usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$saleDateString,WAREHOUSE_INJECTION);		
                      }
                    }
                    //pr($usedMaterials);

                    for ($k=0;$k<count($usedMaterials);$k++){
                      $materialUsed=$usedMaterials[$k];
                      $stockItemId=$materialUsed['id'];
                      $quantity_present=$materialUsed['quantity_present'];
                      $quantity_used=$materialUsed['quantity_used'];
                      $quantity_remaining=$materialUsed['quantity_remaining'];
                      if (!$this->StockItem->exists($stockItemId)) {
                        throw new NotFoundException(__('Invalid StockItem'));
                      }
                      $this->StockItem->recursive=-1;
                      $linkedStockItem=$this->StockItem->getStockItemById($stockItemId);
                      $message="Se vendió lote ".$productName." (Cantidad:".$quantity_used.") para Venta ".$orderCode;
                      
                      $stockItemData=[  
                        'id'=>$stockItemId,
                        'description'=>$linkedStockItem['StockItem']['description']."|".$message,
                        'remaining_quantity'=>$quantity_remaining,
                      ];
                    // notice that no new stockitem is created because we are taking from an already existing one
                      if (!$this->StockItem->save($stockItemData)) {
                        echo "problema al guardar el lote";
                        pr($this->validateErrors($this->StockItem));
                        throw new Exception();
                      }
                      
                      // STEP 2: SAVE THE STOCK MOVEMENT
                      $message="Se vendió ".$productName." (Cantidad:".$quantity_used.", total para venta:".$productQuantity.") para Venta ".$orderCode;
                      $stockMovementData=[
                        'movement_date'=>$saleDate,
                        'bool_input'=>'0',
                        'name'=>$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName,
                        'description'=>$message,
                        'order_id'=>$orderId,
                        'stockitem_id'=>$stockItemId,
                        'product_id'=>$productId,
                        'product_quantity'=>$quantity_used,
                        'product_unit_price'=>$productUnitPrice,
                        'product_total_price'=>$productUnitPrice*$quantity_used,
                        'service_unit_cost'=>0,
                        'service_total_cost'=>0,
                        'production_result_code_id'=>$productionResultCodeId,
                      ];
                      $totalPriceProducts+=$stockMovementData['product_total_price'];
                      
                      $this->StockMovement->create();
                      if (!$this->StockMovement->save($stockMovementData)) {
                        echo "problema al guardar el movimiento de lote";
                        pr($this->validateErrors($this->StockMovement));
                        throw new Exception();
                      }
                    
                      // STEP 3: SAVE THE STOCK ITEM LOG
                      $this->recreateStockItemLogs($stockItemId);
                          
                      // STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
                      $this->recordUserActivity($this->Session->read('User.username'),$message);
                    }
                  
                  }
                  else {
                    $message="Se vendió ".$productName." (Cantidad:".$productQuantity.", total para venta:".$productQuantity.") para Venta ".$orderCode;
                    $stockMovementData=[
                      'movement_date'=>$saleDate,
                      'bool_input'=>'0',
                      'name'=>$saleDate['day'].$saleDate['month'].$saleDate['year']."_".$orderCode."_".$productName,
                      'description'=>$message,
                      'order_id'=>$orderId,
                      'stockitem_id'=>0,
                      'product_id'=>$productId,
                      'product_quantity'=>$productQuantity,
                      'product_unit_price'=>$productUnitPrice,
                      'product_total_price'=>$productUnitPrice*$productQuantity,
                      'service_unit_cost'=>$service_unit_cost,
                      'service_total_cost'=>$service_unit_cost*$productQuantity,
                      'production_result_code_id'=>$productionResultCodeId,
                    ];
                    $totalPriceProducts+=$stockMovementData['product_total_price'];
                    
                    $this->StockMovement->create();
                    if (!$this->StockMovement->save($stockMovementData)) {
                      echo "problema al guardar el movimiento de lote";
                      pr($this->validateErrors($this->StockMovement));
                      throw new Exception();
                    }
                  
                    $this->recordUserActivity($this->Session->read('User.username'),$message);
                  }
                  
                }
                
                if ($this->request->data['Invoice']['currency_id'] == CURRENCY_USD){
                  if (abs($this->request->data['Invoice']['sub_total_price']-$totalPriceProducts/$this->request->data['Order']['exchange_rate'])>1){
                    echo "el subtotal ".$this->request->data['Invoice']['sub_total_price']." no iguala el precio sumado de los productos ".($totalPriceProducts/$this->request->data['Order']['exchange_rate'])." en base a tasa de cambio ".$this->request->data['Order']['exchange_rate'];
                    throw new Exception();
                  }
                }
                else {
                  if (abs($this->request->data['Invoice']['sub_total_price']-$totalPriceProducts) > 1){
                    echo "el subtotal ".$this->request->data['Invoice']['sub_total_price']." no iguala el precio sumado de los productos ".$totalPriceProducts;
                    throw new Exception();
                  }
                }
              
                //if (!$this->request->data['Order']['client_generic']){
                if (!in_array($this->request->data['Order']['third_party_id'],$genericClientIds)){  
                  $orderClientData=[
                    'id'=>$this->request->data['Order']['third_party_id'],
                    'phone'=>$this->request->data['Order']['client_phone'],
                    'email'=>$this->request->data['Order']['client_email'],
                    'address'=>$this->request->data['Order']['client_address'],
                    'ruc_number'=>'',
                    'client_type_id'=>$this->request->data['Order']['client_type_id'],
                    'zone_id'=>$this->request->data['Order']['zone_id'],
                    
                  ];
                  if (!$this->ThirdParty->updateClientDataConditionally($orderClientData,$userRoleId)['success']){
                    echo "Problema actualizando los datos del cliente";
                    throw new Exception();
                  }
                }
                        
                $datasource->commit();
                $this->recordUserAction($this->Order->id,"editarVenta",null);
                $newDataSaved=true;
                // SAVE THE USERLOG FOR THE PURCHASE
                $this->recordUserActivity($this->Session->read('User.username'),"Se editó la venta con factura número ".$this->request->data['Order']['order_code']);
                $this->Session->setFlash(__('Se editó la venta.'),'default',['class' => 'success'],'default',['class' => 'success']);
                if (!empty($this->request->data['saveAndNew'])){
                  return $this->redirect(['action' => 'crearVenta']);
                }
                else {
                  return $this->redirect(['action' => 'imprimirVenta',$orderId]);  
                }
              }
              catch(Exception $e){
                $datasource->rollback();
                pr($e);
                $this->Session->setFlash(__('The sale could not be edited. Please, try again.'), 'default',['class' => 'error-message']);
              }
            }
            if (!$newDataSaved){
              $datasource=$this->Order->getDataSource();
              $datasource->begin();	
              try {
                if (!empty($stockMovementsOriginalSale)){
                  foreach ($stockMovementsOriginalSale as $originalStockMovement){						
                    // set all stockmovements to 0
                    $restoredStockMovementData=[];
                    $restoredStockMovementData['id']=$originalStockMovement['StockMovement']['id'];
                    $restoredStockMovementData['description']=$originalStockMovement['StockMovement']['description'];
                    $restoredStockMovementData['product_quantity']=$originalStockMovement['StockMovement']['product_quantity'];
                    $restoredStockMovementData['service_unit_cost']=$originalStockMovement['StockMovement']['service_unit_cost'];								
                    $restoredStockMovementData['service_total_cost']=$originalStockMovement['StockMovement']['service_total_cost'];								
                    $restoredStockMovementData['product_total_price']=$originalStockMovement['StockMovement']['product_total_price'];								
                    if (!$this->StockMovement->save($restoredStockMovementData)) {
                      echo "problema al guardar el movimiento de salida";
                      pr($this->validateErrors($this->StockMovement));
                      throw new Exception();
                    }
                    
                    
                    $this->Product->recursive=-1;
                    $linkedProduct=$this->Product->find('first',[
                      'conditions'=>[
                        'Product.id'=>$originalStockMovement['StockMovement']['product_id'],
                      ],
                    ]);
                    if ($linkedProduct['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
                    // restore the stockitems to their previous level
                      $restoredStockItemData=[];
                      $restoredStockItemData['id']=$originalStockMovement['StockItem']['id'];
                      $restoredStockItemData['description']=$originalStockMovement['StockItem']['description'];
                      $restoredStockItemData['remaining_quantity']=$originalStockMovement['StockItem']['remaining_quantity'];
                      if (!$this->StockItem->save($restoredStockItemData)) {
                        echo "problema al guardar el lote";
                        pr($this->validateErrors($this->StockItem));
                        throw new Exception();
                      }
                      
                      $this->recreateStockItemLogs($originalStockMovement['StockItem']['id']);
                    }
                  }
                }					
                if (!empty($originalInvoice)){				
                  if (!empty($originalInvoice['AccountingRegisterInvoice'])){
                    foreach ($originalInvoice['AccountingRegisterInvoice'] as $originalAccountingRegisterInvoice){
                      if (!empty($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'])){
                        foreach ($originalAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'] as $originalAccountingMovement){
                          $accountingMovementArray=$originalAccountingMovement;
                          $this->AccountingMovement->create();
                          if (!$this->AccountingMovement->save($accountingMovementArray)) {
                            echo "problema al guardar el movimeinto contable";
                            pr($this->validateErrors($this->AccountingMovement));
                            throw new Exception();
                          }
                        }
                      }
                      $accountingRegisterArray=$originalAccountingRegisterInvoice['AccountingRegister'];
                      $this->AccountingRegister->create();
                      if (!$this->AccountingRegister->save($restoredStockItemData)) {
                        echo "problema al guardar el asiento contable";
                        pr($this->validateErrors($this->AccountingRegister));
                        throw new Exception();
                      }
                      $accountingRegisterInvoiceArray=$originalAccountingRegisterInvoice;
                      $this->AccountingRegisterInvoice->create();
                      if (!$this->AccountingRegisterInvoice->save($accountingRegisterInvoiceArray)) {
                        echo "problema al guardar el vínculo entre asiento contable y factura";
                        pr($this->validateErrors($this->AccountingRegisterInvoice));
                        throw new Exception();
                      }
                    }
                  }
                  $invoiceArray=$originalInvoice['Invoice'];
                  $this->Invoice->create();
                  if (!$this->Invoice->save($invoiceArray)) {
                    echo "problema al guardar la factura";
                    pr($this->validateErrors($this->Invoice));
                    throw new Exception();
                  }
                  $this->Invoice->delete($originalInvoice['Invoice']['id']);
                }						
                $datasource->commit();
                $this->recordUserActivity($this->Session->read('User.username'),"Se removieron los datos viejos para venta ".$this->request->data['Order']['order_code']);
                $oldDateRemoved=true;
              }
              catch(Exception $e){
                $datasource->rollback();
                pr($e);
                $this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',['class' => 'error-message']);
              }
            }
          }
        }
      } 
    }  
    else {
			$options = [
				'conditions' => [
					'Order.id' => $id,
				],
				'contain'=>[
					'Invoice'=>[
						'AccountingRegisterInvoice'=>[
							'AccountingRegister'=>[
								'AccountingMovement'
							],
							'Invoice',
						],
					],
				],
			];
      
			$this->request->data = $this->Order->find('first', $options);
      
      //pr($this->request->data);
      $orderDate=$this->request->data['Order']['order_date'];
      
      $salesOrderId=0;
      $this->request->data['Order']['sales_order_id']=0;
      //pr($this->request->data);
      if (!empty($this->request->data['Invoice'])){
        $salesOrderId=$this->request->data['Invoice'][0]['sales_order_id'];
        $this->request->data['Order']['sales_order_id']=$this->request->data['Invoice'][0]['sales_order_id'];
        $creditAuthorizationUserId=$this->request->data['Invoice'][0]['credit_authorization_user_id'];
      }
      
      $clientId=$this->request->data['Order']['third_party_id'];
      $currencyId=$this->request->data['Invoice'][0]['currency_id'];
      
      $vendorUserId=empty($this->request->data['Order']['vendor_user_id'])?$loggedUserId:$this->request->data['Order']['vendor_user_id'];
      $recordUserId=empty($this->request->data['Order']['record_user_id'])?$loggedUserId:$this->request->data['Order']['record_user_id'];
      //$driverUserId=empty($this->request->data['Order']['driver_user_id'])?0:$this->request->data['Order']['driver_user_id'];
      //$vehicleId=empty($this->request->data['Order']['vehicle_id'])?0:$this->request->data['Order']['vehicle_id'];
      
      $warehouseId=$this->request->data['Order']['warehouse_id'];
      $boolDelivery=$this->request->data['Order']['bool_delivery'];
			
			$this->StockMovement->recursive=0;
			$this->StockMovement->virtualFields['total_product_quantity']=0;
      $this->StockMovement->virtualFields['service_total_cost']=0;
			$this->StockMovement->virtualFields['total_product_price']=0;
	
			$stockMovements=$this->StockMovement->find('all',[
				'fields'=>[
					'StockItem.warehouse_id',
					'StockMovement.product_id',
					'StockItem.raw_material_id',
					'StockMovement.production_result_code_id',
					'SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity', 
					'SUM(service_total_cost) AS StockMovement__service_total_cost', 
          'SUM(product_total_price) AS StockMovement__total_product_price', 
					
				],
				'conditions'=>[
					'StockMovement.product_quantity >'=>0,
					'StockMovement.order_id'=>$id,
				],
				'group'=>'StockMovement.product_id,StockItem.raw_material_id,StockMovement.production_result_code_id',
			]);
			if (!empty($stockMovements)){
				$stockItemWarehouseId=$stockMovements[0]['StockItem']['warehouse_id'];
				foreach ($stockMovements as $stockMovement){
					//pr($stockMovement);
					$productArray=[];
					$productArray['product_id']=$stockMovement['StockMovement']['product_id'];
					$productArray['raw_material_id']=$stockMovement['StockItem']['raw_material_id'];
					$productArray['production_result_code_id']=$stockMovement['StockMovement']['production_result_code_id'];
					$productArray['product_quantity']=$stockMovement['StockMovement']['total_product_quantity'];
          $productArray['service_unit_cost']=$stockMovement['StockMovement']['service_total_cost']/$stockMovement['StockMovement']['total_product_quantity'];
					$productArray['service_total_cost']=$stockMovement['StockMovement']['service_total_cost'];
					//$productArray['product_unit_price']=round($stockMovement['StockMovement']['total_product_price']/$stockMovement['StockMovement']['total_product_quantity'],4);
          $productArray['product_unit_price']=$stockMovement['StockMovement']['total_product_price']/$stockMovement['StockMovement']['total_product_quantity'];
          $productArray['product_total_price']=$stockMovement['StockMovement']['total_product_price'];
          
          $productInventory=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($stockMovement['StockMovement']['product_id'],$orderDate,$stockItemWarehouseId,$stockMovement['StockItem']['raw_material_id']);
          $productCost=0;
          if ($productInventory['quantity'] > 0){
            $productCost=round($productInventory['value']/$productInventory['quantity'],2);
          }
          $productArray['product_unit_cost']=$productCost;
        
					$requestProducts[]['Product']=$productArray;
				}
			} 
		}
    //pr($requestProducts);
    //echo 'sales order id is '.$salesOrderId.'<br/>';
    //echo 'credit authorization user id is '.$creditAuthorizationUserId.'<br/>';
    $orderDatePlusOne=date("Y-m-d",strtotime(date('Y-m-d')."+1 days"));
		$this->set(compact('orderDate'));
    $this->set(compact('creditAuthorizationUserId'));
    
    $creditAuthorizedInOriginalInvoice=$this->Invoice->getCreditStatus($id);
    $salesOrderIdOriginalInvoice=$this->Invoice->getSalesOrderId($id);
    $this->set(compact('creditAuthorizedInOriginalInvoice','salesOrderIdOriginalInvoice'));
    
    if (array_key_exists(0,$this->request->data['Invoice'])){
      $this->request->data['Invoice']=$this->request->data['Invoice'][0];
    }
		
    $this->set(compact('requestProducts'));
    $this->set(compact('boolInitialLoad'));
    
    $this->set(compact('inventoryDisplayOptionId'));
    
    $this->set(compact('salesOrderId'));
    $this->set(compact('clientId'));
    $this->set(compact('currencyId'));
    
    $this->set(compact('vendorUserId'));
    $this->set(compact('recordUserId'));
    $this->set(compact('creditAuthorizationUserId'));
    
    //$this->set(compact('driverUserId'));
    //$this->set(compact('vehicleId'));
    $this->set(compact('boolDelivery'));
		
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    // warehouseId is set by order itself
  /*  
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
  */
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    $plantId=$this->Warehouse->getPlantId($warehouseId);
    $this->set(compact('plantId'));
    
		$subtotalNoInvoice=0;
		//pr($this->request->data);
		$bool_invoicetype_editable=true;
		if (!empty($this->request->data['Invoice']['id'])){
			if ($this->request->data['Invoice']['bool_credit']){
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('list',[
					'conditions'=>[
						'CashReceiptInvoice.invoice_id'=>$this->request->data['Invoice']['id'],
						'CashReceiptInvoice.amount >'=>0,
					],
				]);
				if (count($cashReceiptsForInvoice)>0){
					$bool_invoicetype_editable='0';
				}
			}
		}
		elseif (!empty($this->request->data['StockMovement'])){			
			foreach($this->request->data['StockMovement'] as $productSold){
				$subtotalNoInvoice+=$productSold['StockMovement']['total_product_price'];
			}
		}
		$this->set(compact('subtotalNoInvoice'));
		
		$thirdParties = $this->Order->ThirdParty->getActiveClientList();
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		
		if (is_array($this->request->data['Order']['order_date'])){
			$orderDateArray=$this->request->data['Order']['order_date'];
			$orderDateString=$orderDateArray['year'].'-'.$orderDateArray['month'].'-'.$orderDateArray['day'];
			$orderDate=date("Y-m-d",strtotime($orderDateString));
			$orderDatePlusOne=date("Y-m-d",strtotime($orderDateString."+1 days"));
		}
		else {
			$orderDate=date("Y-m-d",strtotime($this->request->data['Order']['order_date']));
			$orderDatePlusOne=date("Y-m-d",strtotime($this->request->data['Order']['order_date']."+1 days"));
		}
		$this->set(compact('orderDate'));
		
    $currencies = $this->SalesOrder->Currency->find('list');
		$this->set(compact('currencies'));  
    
    $exchangeRateOrder=$this->ExchangeRate->getApplicableExchangeRateValue($orderDate);
		$this->set(compact('exchangeRateOrder'));
    
    $finishedProductsForEdit=[];
    $rawMaterialsForEdit=[];
    foreach ($requestProducts as $requestProduct){
      if (!in_array($requestProduct['Product']['product_id'],$finishedProductsForEdit)){
        $finishedProductsForEdit[]=$requestProduct['Product']['product_id'];
      }
      if (!in_array($requestProduct['Product']['raw_material_id'],$rawMaterialsForEdit)){
        $rawMaterialsForEdit[]=$requestProduct['Product']['raw_material_id'];
      }
    }
    //pr($finishedProductsForEdit);
    $availableProductsForSale=$this->Product->getAvailableProductsForSale($orderDate,$warehouseId,true,$finishedProductsForEdit,$rawMaterialsForEdit);
    $products=$availableProductsForSale['products'];
    if ($warehouseId != WAREHOUSE_INJECTION){
      $availableInjectionProductsForSale=$this->Product->getAvailableProductsForSale($orderDate,WAREHOUSE_INJECTION,false,$finishedProductsForEdit);
      $injectionProducts=$availableInjectionProductsForSale['products'];
      //pr($injectionProducts);
      $products+=$injectionProducts;
    }
    $rawMaterialsAvailablePerFinishedProduct=$availableProductsForSale['rawMaterialsAvailablePerFinishedProduct'];
    $rawMaterials=$availableProductsForSale['rawMaterials'];
    //pr($products);
    $this->set(compact('products'));
    $this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    $this->set(compact('rawMaterials'));
    
		//if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      // TEMPORARY  FIX COLINAS
      //$users=$this->User->getActiveVendorAdminUserList($warehouseId);
      $users=$this->User->getActiveVendorAdminUserList();
    //}
    //elseif ($canSeeAllVendors) {
    //  $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    //}
    //else {
    //  $users=$this->User->getActiveUserList($loggedUserId);
    //}
    $this->set(compact('users'));
    
    $productionResultCodes=$this->ProductionResultCode->find('list',[
      'conditions'=>['ProductionResultCode.id'=>PRODUCTION_RESULT_CODE_A]
    ]);
    $this->set(compact('productionResultCodes'));
   
		//if (!empty($inventoryDisplayOptionId)){
			$productCategoryId=CATEGORY_PRODUCED;
			$productTypeIds=$this->ProductType->find('list',[
				'fields'=>['ProductType.id'],
				'conditions'=>['ProductType.product_category_id'=>$productCategoryId],
			]);
      $finishedMaterialsInventory =[];
			$finishedMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
			
      if ($warehouseId != WAREHOUSE_INJECTION){
        $injectionMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,WAREHOUSE_INJECTION);
        $this->set(compact('injectionMaterialsInventory'));
      }
      
			//pr($finishedMaterialsInventory);
			$productCategoryId=CATEGORY_OTHER;
			$productTypeIds=$this->ProductType->find('list',[
				'fields'=>['ProductType.id'],
				'conditions'=>['ProductType.product_category_id'=>$productCategoryId]
			]);
			$otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
      //pr($otherMaterialsInventory);
      
			$productCategoryId=CATEGORY_RAW;
			$productTypeIds=$this->ProductType->find('list',[
				'fields'=>['ProductType.id'],
				'conditions'=>['ProductType.product_category_id'=>$productCategoryId],
			]);
			$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
		//}
    
		$cashboxAccountingCode=$this->AccountingCode->find('first',[
			'fields'=>['AccountingCode.lft','AccountingCode.rght'],
			'conditions'=>[
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			],
		]);
		
		$accountingCodes=$this->AccountingCode->find('list',[
			'fields'=>'AccountingCode.fullname',
			'conditions'=>[
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			],
		]);
		
		$this->set(compact('thirdParties', 'stockMovementTypes','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','rawMaterials','currencies','accountingCodes','bool_invoicetype_editable'));
		
		$otherProducts=$this->Product->find('list',[
      'fields'=>'Product.id',
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_SERVICE]
    ]);
    $otherProducts=array_values($otherProducts);
    $this->set(compact('otherProducts'));
    
    //echo "warehouse id is ".$warehouseId."<br/>";
    // TEMPORARY FIX COLINAS
    //$salesOrders=$this->SalesOrder->getPendingSalesOrders($warehouseId,$salesOrderId);
    $salesOrders=$this->SalesOrder->getPendingSalesOrders(0,$salesOrderId);
    $this->set(compact('salesOrders'));
    //pr($salesOrders);
    
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
		
    //$driverUsers=$this->User->getActiveUsersForRole(ROLE_DRIVER,$warehouseId);
    //$this->set(compact('driverUsers'));
    //$vehicles=$this->Vehicle->getVehicleList($warehouseId);
    //$this->set(compact('vehicles'));
    
    $genericClientIds=$this->ThirdParty->find('list',[
      'fields'=>['ThirdParty.id'],
      'conditions'=>['ThirdParty.bool_generic'=>true]
    ]);
    $this->set(compact('genericClientIds'));
    
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function editarRemision($id = null) {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
    $this->loadModel('ProductionResultCode');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
				
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('CashReceipt');
		$this->loadModel('CashReceiptType');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterCashReceipt');
		$this->loadModel('AccountingMovement');
		
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
     
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $roleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId','roleId'));
    
    $this->Order->recursive=-1;
    $this->StockItem->recursive=-1;
    $this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		$this->Order->ThirdParty->recursive=-1;
    $this->Order->StockMovementType->recursive=-1;
    $this->AccountingCode->recursive=-1;
		$this->CashReceipt->recursive=-1;
    
		$warehouseId=0;
		
		$inventoryDisplayOptions=[
			'0'=>'No mostrar inventario',
			'1'=>'Mostrar inventario',
		];
		$this->set(compact('inventoryDisplayOptions'));
		$inventoryDisplayOptionId=1;
		
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}
		
		$cashReceipt=[];
		//pr($this->request->data);
    $requestProducts=[];
    $productCount=0;
    
    if ($this->request->is(array('post', 'put'))) {
			foreach ($this->request->data['Product'] as $product){
				if (!empty($product['product_id'])){
					$requestProducts[]['Product']=$product;
				}
			}
			$warehouseId=$this->request->data['Order']['warehouse_id'];
			//$inventoryDisplayOptionId=$this->request->data['Order']['inventory_display_option_id'];
			
			$cashReceipt['CashReceipt']=$this->request->data['CashReceipt'];
			
			if (empty($this->request->data['refresh'])&&empty($this->request->data['showinventory'])){			
				$remission_date=$this->request->data['Order']['order_date'];
				$remissionDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
				$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
				$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
				$closingDate=new DateTime($latestClosingDate);
				
				$remissionDateArray=[];
				$remissionDateArray['year']=$remission_date['year'];
				$remissionDateArray['month']=$remission_date['month'];
				$remissionDateArray['day']=$remission_date['day'];
				
				$orderCode=$this->request->data['Order']['order_code'];
				$namedRemissions=$this->Order->find('all',array(
					'conditions'=>array(
						'order_code'=>$orderCode,
						'stock_movement_type_id'=>MOVEMENT_SALE,
						'Order.id !='=>$id,
					)
				));
				
				if (count($namedRemissions)>0){
					$this->Session->setFlash(__('Ya existe una remisión con el mismo código!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				elseif ($remissionDateAsString>date('Y-m-d 23:59:59')){
					$this->Session->setFlash(__('La fecha de remisión no puede estar en el futuro!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				elseif ($remissionDateAsString<$latestClosingDatePlusOne){
					$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se puede realizar cambios.'), 'default',['class' => 'error-message']);
				}
				elseif ($this->request->data['Order']['third_party_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar el cliente para la remisión!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				elseif ($this->request->data['CashReceipt']['bool_annulled']){
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					$stockMovementsOriginalRemission=$this->Order->StockMovement->find('all',array(
						'fields'=>array(
							'StockMovement.product_id,StockMovement.product_quantity,StockMovement.stockitem_id,StockMovement.product_total_price,StockMovement.id, StockMovement.description, StockMovement.movement_date',
						),
						'conditions' => array(
							'StockMovement.order_id'=> $id
						),
						'contain'=>array(
							'StockItem'=>array(
								'fields'=> array('remaining_quantity','raw_material_id','production_result_code_id','remaining_quantity','description'),
								'StockItemLog'=>array(
									'fields'=>array('StockItemLog.id,StockItemLog.stockitem_date'),
								)
							),
							'Product'=>array(
								'fields'=> array('id','name'),
							)
						),						
					));		
					$originalCashReceipt=$this->CashReceipt->find('first',array(
						'conditions'=>array(
							'CashReceipt.order_id'=>$id,
						),
						'contain'=>array(
							'AccountingRegisterCashReceipt'=>array(
								'AccountingRegister'=>array(
									'AccountingMovement'
								),
								'Invoice',
							),
						),
					));
					try {
						if (!empty($stockMovementsOriginalRemission)){
							foreach ($stockMovementsOriginalRemission as $originalStockMovement){						
								// set all stockmovements to 0
								$annulledStockMovementData=[];
								$annulledStockMovementData['id']=$originalStockMovement['StockMovement']['id'];
								$annulledStockMovementData['description']=$originalStockMovement['StockMovement']['description']." cancelled through editing on ".date('Y-m-d');
								$annulledStockMovementData['product_quantity']=0;
								$annulledStockMovementData['product_total_price']=0;								
								if (!$this->StockMovement->save($annulledStockMovementData)) {
									echo "problema al guardar el movimiento de salida";
									pr($this->validateErrors($this->StockMovement));
									throw new Exception();
								}
								
								// restore the stockitems to their previous level
								$annulledStockItemData=[];
								$annulledStockItemData['id']=$originalStockMovement['StockItem']['id'];
								$annulledStockItemData['description']=$originalStockMovement['StockItem']['description']." added back quantity ".$originalStockMovement['StockMovement']['product_quantity']." through editing on ".date('Y-m-d')." for order ".$id;
								$annulledStockItemData['remaining_quantity']=$originalStockMovement['StockItem']['remaining_quantity']+$originalStockMovement['StockMovement']['product_quantity'];
								if (!$this->StockItem->save($annulledStockItemData)) {
									echo "problema al guardar el lote";
									pr($this->validateErrors($this->StockItem));
									throw new Exception();
								}
								
								$this->recreateStockItemLogs($originalStockMovement['StockItem']['id']);
							}
						}
						if (!empty($originalCashReceipt)){				
							if (!empty($originalCashReceipt['AccountingRegisterCashReceipt'])){
								foreach ($originalCashReceipt['AccountingRegisterCashReceipt'] as $originalAccountingRegisterCashReceipt){
									if (!empty($originalAccountingRegisterCashReceipt['AccountingRegister']['AccountingMovement'])){
										foreach ($originalAccountingRegisterCashReceipt['AccountingRegister']['AccountingMovement'] as $originalAccountingMovement){
											$this->AccountingMovement->delete($originalAccountingMovement['id']);
										}
									}
									$this->AccountingRegister->delete($originalAccountingRegisterCashReceipt['AccountingRegister']['id']);
									$this->AccountingRegisterCashReceipt->delete($originalAccountingRegisterCashReceipt['id']);
								}
							}
							$this->CashReceipt->delete($originalCashReceipt['CashReceipt']['id']);
						}						
						$datasource->commit();
						$this->recordUserActivity($this->Session->read('User.username'),"Se removieron los viejos datos de remisión para ".$this->request->data['Order']['order_code']);
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',['class' => 'error-message']);
					}
					// then save the minimum data for the annulled invoice/order				
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					try {
						//pr($this->request->data);
						$orderData=[];
						$orderData['Order']['bool_annulled']=true;
						$orderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
						$orderData['Order']['order_date']=$this->request->data['Order']['order_date'];
						$orderData['Order']['order_code']=$this->request->data['Order']['order_code'];
						$orderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
						$orderData['Order']['total_price']=0;
						$this->Order->id=$id;
						if (!$this->Order->save($orderData)) {
							echo "Problema guardando la remisión";
							pr($this->validateErrors($this->Order));
							throw new Exception();
						}
						$orderId=$this->Order->id;
						
						$this->CashReceipt->create();
						$CashReceiptData=[];
						$CashReceiptData['CashReceipt']['order_id']=$orderId;
						$CashReceiptData['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
						$CashReceiptData['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
						$CashReceiptData['CashReceipt']['bool_annulled']=true;
						$CashReceiptData['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
						$CashReceiptData['CashReceipt']['cash_receipt_type_id']=CASH_RECEIPT_TYPE_REMISSION;
						$CashReceiptData['CashReceipt']['amount']=0;
						$CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
				
						if (!$this->CashReceipt->save($CashReceiptData)) {
							echo "Problema guardando el recibo de caja";
							pr($this->validateErrors($this->CashReceipt));
							throw new Exception();
						}
						
						$datasource->commit();
							
						// SAVE THE USERLOG 
						$this->recordUserActivity($this->Session->read('User.username'),"Se anuló la remisión con número ".$this->request->data['Order']['order_code']);
						$this->Session->setFlash(__('Se anuló la remisión.'),'default',['class' => 'success'],'default',['class' => 'success']);
						return $this->redirect(array('action' => 'resumenVentasRemisiones'));

					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('No se podía guardar la remisión.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
					}
				}
				else if ($this->request->data['CashReceipt']['cashbox_accounting_code_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una remisión!  No se guardó la remisión.'), 'default',['class' => 'error-message']);
				}
				else {
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					$stockMovementsOriginalRemission=$this->Order->StockMovement->find('all',array(
						'fields'=>array(
							'StockMovement.product_id,StockMovement.product_quantity,StockMovement.stockitem_id,StockMovement.product_total_price,StockMovement.id, StockMovement.description, StockMovement.movement_date',
						),
						'conditions' => array(
							'StockMovement.order_id'=> $id
						),
						'contain'=>array(
							'StockItem'=>array(
								'fields'=> array('remaining_quantity','raw_material_id','production_result_code_id','remaining_quantity','description'),
								'StockItemLog'=>array(
									'fields'=>array('StockItemLog.id,StockItemLog.stockitem_date'),
								)
							),
							'Product'=>array(
								'fields'=> array('id','name'),
							)
						),						
					));		
					$originalCashReceipt=$this->CashReceipt->find('first',[
						'conditions'=>[
							'CashReceipt.order_id'=>$id,
						],
						'contain'=>[
							'AccountingRegisterCashReceipt'=>[
								'AccountingRegister'=>[
									'AccountingMovement'
								],
								'CashReceipt',
							],
						],
					]);
					//pr($originalCashReceipt);
					$oldDataRemoved='0';
					try {
						if (!empty($stockMovementsOriginalRemission)){
							foreach ($stockMovementsOriginalRemission as $originalStockMovement){						
								// set all stockmovements to 0
								$annulledStockMovementData=[];
								$annulledStockMovementData['id']=$originalStockMovement['StockMovement']['id'];
								$annulledStockMovementData['description']=$originalStockMovement['StockMovement']['description']." cancelled through editing on ".date('Y-m-d');
								$annulledStockMovementData['product_quantity']=0;
								$annulledStockMovementData['product_total_price']=0;								
								if (!$this->StockMovement->save($annulledStockMovementData)) {
									echo "problema al guardar el movimiento de salida";
									pr($this->validateErrors($this->StockMovement));
									throw new Exception();
								}
								
								// restore the stockitems to their previous level
								$annulledStockItemData=[];
								$annulledStockItemData['id']=$originalStockMovement['StockItem']['id'];
								$annulledStockItemData['description']=$originalStockMovement['StockItem']['description']." added back quantity ".$originalStockMovement['StockMovement']['product_quantity']." through editing on ".date('Y-m-d')." for order ".$id;
								$annulledStockItemData['remaining_quantity']=$originalStockMovement['StockItem']['remaining_quantity']+$originalStockMovement['StockMovement']['product_quantity'];
								if (!$this->StockItem->save($annulledStockItemData)) {
									echo "problema al guardar el lote";
									pr($this->validateErrors($this->StockItem));
									throw new Exception();
								}
								
								$this->recreateStockItemLogs($originalStockMovement['StockItem']['id']);
							}
						}					
						if (!empty($originalCashReceipt)){				
							if (!empty($originalCashReceipt['AccountingRegisterCashReceipt'])){
                //echo "starting to remove the accountingregistercashreceipts<br/>";
								foreach ($originalCashReceipt['AccountingRegisterCashReceipt'] as $originalAccountingRegisterCashReceipt){
									//pr($originalAccountingRegisterCashReceipt);
									if (!empty($originalAccountingRegisterCashReceipt['AccountingRegister']['AccountingMovement'])){
                    //echo "starting to remove the accountingmovements<br/>";
										foreach ($originalAccountingRegisterCashReceipt['AccountingRegister']['AccountingMovement'] as $originalAccountingMovement){
											if (!$this->AccountingMovement->delete($originalAccountingMovement['id'])){
                          echo "could not remove accounting movement<br/>";
                      }
										}
									}
									
									if (!$this->AccountingRegister->delete($originalAccountingRegisterCashReceipt['AccountingRegister']['id'])){
                    echo "could not remove accounting register<br/>";
                  }
									if (!$this->AccountingRegisterCashReceipt->delete($originalAccountingRegisterCashReceipt['id'])){
                    echo "could not remove accounting register cash receipt<br/>";
                  }
								}
							}
							if (!$this->CashReceipt->delete($originalCashReceipt['CashReceipt']['id'])){
                echo "could not remove cash receipt<br/>";
              }
						}						
						$datasource->commit();
						$this->recordUserActivity($this->Session->read('User.username'),"Se removieron los datos viejos para remisión ".$this->request->data['Order']['order_code']);
						$oldDataRemoved=true;
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',['class' => 'error-message']);
					}
					if ($oldDataRemoved){
						$this->request->data['Order']['id']=$id;
						$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
						// get the relevant information of the purchase that was just saved
						$orderId=$id;
						$remission_date=$this->request->data['Order']['order_date'];
						$orderCode=$this->request->data['Order']['order_code'];		
						
						$newDataSaved='0';
						
						$saleItemsOK=true;
						$exceedingItems="";
            
            $productMultiplicationOk=true;
            $productMultiplicationWarning="";
            
            $productTotalSumBasedOnProductTotals=0;
            
						$products=[];	
						foreach ($this->request->data['Product'] as $product){
							//pr($product);
							// keep track of number of rows so that in case of an error jquery displays correct number of rows again
							if ($product['product_id']>0){
								$productCount++;
							}
							// only process lines where product_quantity has been filled out
							if ($product['product_quantity']>0 && $product['product_id']>0){
								$products[]=$product;
								
								$productId = $product['product_id'];
								
								$relatedProduct=$this->Product->find('first',array(
									'conditions'=>array(
										'Product.id'=>$productId,
									),
								));
								
								$quantityNeeded=$productquantity=$product['product_quantity'];
								$productunitprice = $product['product_unit_price'];
								$producttotalprice = $product['product_total_price'];
								$productionResultCodeId = $product['production_result_code_id'];
								$rawMaterialId = $product['raw_material_id'];
								
								if ($this->Product->getProductCategoryId($productId)==CATEGORY_PRODUCED){
									$quantityInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($productId,$rawMaterialId,$productionResultCodeId,$remissionDateAsString,$warehouseId,true);
								}
								else {
									$quantityInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($productId,$remissionDateAsString,$warehouseId,true);
								}
								//compare the quantity requested and the quantity in stock
								if ($quantityNeeded>$quantityInStock){
									$saleItemsOK='0';
									$exceedingItems.=__("Para producto ".$relatedProduct['Product']['name']." la cantidad requerida (".$quantityNeeded.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
								}
                
                $productMultiplicationBasedOnUnitPriceAndQuantity=$product['product_quantity']*$product['product_unit_price'];
                $productMultiplicationBasedOnTotalPrice=$product['product_total_price'];
                if (abs($productMultiplicationBasedOnUnitPriceAndQuantity-$productMultiplicationBasedOnTotalPrice) > 0.01){
                  $productMultiplicationOk='0';
                  $productMultiplicationWarning.="Producto ".$relatedProduct['Product']['name']." tiene una cantidad ".$product['product_quantity']." y un precio unitario ".$product['product_unit_price'].", pero el total calculado ".$product['product_total_price']." no es correcto;";
                }
                //echo "product total price is ".$product['product_total_price']."<br/>";
                $productTotalSumBasedOnProductTotals+=$product['product_total_price'];
							}
						}
						if ($exceedingItems!=""){
							$exceedingItems.=__("Please correct and try again!");
						}
						if (!$productMultiplicationOk){
              $this->Session->setFlash($productMultiplicationWarning.'  vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
            }
            elseif (abs($productTotalSumBasedOnProductTotals-$this->request->data['CashReceipt']['amount']) > 0.01){
              $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['CashReceipt']['amount'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
            }
            elseif (!$saleItemsOK) {
							$this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
						}				
						else{
							$datasource=$this->Order->getDataSource();
							$datasource->begin();
							// retrieve the original stockMovements that were involved in the sale
							
							try	{
								$currency_id=$this->request->data['CashReceipt']['currency_id'];
								
								$total_cash_receipt=$this->request->data['CashReceipt']['amount'];
								
								if ($currency_id==CURRENCY_USD){
									$this->request->data['Order']['total_price']=$total_cash_receipt*$this->request->data['Order']['exchange_rate'];
								}
								else {
									$this->request->data['Order']['total_price']=$total_cash_receipt;
								}
								if (!$this->Order->save($this->request->data)) {
									echo "problema al guardar la remisión";
									pr($this->validateErrors($this->Order));
									throw new Exception();
								}
								
								$orderId=$this->Order->id;
								$orderCode=$this->request->data['Order']['order_code'];
							
								$this->CashReceipt->create();
								$this->request->data['CashReceipt']['order_id']=$orderId;
								$this->request->data['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
								$this->request->data['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
								$this->request->data['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
						
								if (!$this->CashReceipt->save($this->request->data)) {
									echo "Problema guardando el recibo de caja";
									pr($this->validateErrors($this->CashReceipt));
									throw new Exception();
								}
								
								$cash_receipt_id=$this->CashReceipt->id;						
								// now prepare the accounting registers
								
								if ($currency_id==CURRENCY_USD){
									$this->loadModel('ExchangeRate');
									$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($remissionDateAsString);
									$totalCS=round($total_cash_receipt*$applicableExchangeRate['ExchangeRate']['rate'],2);
								}
								else {
									$totalCS=$total_cash_receipt;
								}
								
								$accountingRegisterData['AccountingRegister']['concept']="Remisión Orden".$orderCode;
								$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
								if (!empty($oldAccountingRegisterCode)){
									$registerCode=$oldAccountingRegisterCode;
								}
								else {
									$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
								}
								$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;		
								$accountingRegisterData['AccountingRegister']['register_date']=$remissionDateArray;
								$accountingRegisterData['AccountingRegister']['amount']=$totalCS;
								$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
								
								$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['CashReceipt']['cashbox_accounting_code_id'];
								$this->AccountingCode->recursive=-1;
								$accountingCode=$this->AccountingCode->find('first',array(
									'conditions'=>array(
										'AccountingCode.id'=>$this->request->data['CashReceipt']['cashbox_accounting_code_id']
									),
								));
								$accountingRegisterData['AccountingMovement'][0]['concept']="Remisión ".$orderCode;
								$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
								$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$totalCS;
								
								$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
								$accountingCode=$this->AccountingCode->find('first',array(
									'conditions'=>array(
										'AccountingCode.id'=>ACCOUNTING_CODE_INGRESOS_VENTA
									),
								));
								$accountingRegisterData['AccountingMovement'][1]['concept']="Remisión Orden".$orderCode;
								$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
								$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$totalCS;
								
								//pr($accountingRegisterData);
								$accountingRegisterId=$this->saveAccountingRegisterData($accountingRegisterData,true);
								$this->recordUserAction($this->AccountingRegister->id,"add",null);
								//echo "accounting register saved for cuentas cobrar clientes<br/>";
						
								$AccountingRegisterCashReceiptData=[];
								$AccountingRegisterCashReceiptData['accounting_register_id']=$accountingRegisterId;
								$AccountingRegisterCashReceiptData['cash_receipt_id']=$cash_receipt_id;
								$this->AccountingRegisterCashReceipt->create();
								if (!$this->AccountingRegisterCashReceipt->save($AccountingRegisterCashReceiptData)) {
									pr($this->validateErrors($this->AccountingRegisterCashReceipt));
									echo "problema al guardar el lazo entre asiento contable y factura";
									throw new Exception();
								}
								//echo "link accounting register sale saved<br/>";					
								
								foreach ($products as $product){
									//pr($product);

									$productId = $product['product_id'];
									
									$productionResultCodeId = $product['production_result_code_id'];
									$rawMaterialId = $product['raw_material_id'];
									
									$productUnitPrice=$product['product_unit_price'];
									$productQuantity = $product['product_quantity'];
									
									if ($currency_id==CURRENCY_USD){
										$productUnitPrice*=$this->request->data['Order']['exchange_rate'];
									}
									
									// get the related product data
									$this->Product->recursive=-1;
									$linkedProduct=$this->Product->find('first',array(
										'Product.id'=>$productId,
									));
									$productName=$linkedProduct['Product']['name'];
									
									// STEP 1: SAVE THE STOCK ITEM(S)
									// first prepare the materials that will be taken out of stock
									if ($this->Product->getProductCategoryId($productId)==CATEGORY_PRODUCED){
										$usedMaterials= $this->StockItem->getFinishedMaterialsForSale($productId,$productionResultCodeId,$productQuantity,$rawMaterialId,$remissionDateAsString,$warehouseId);
									}
									else {
										$usedMaterials= $this->StockItem->getOtherMaterialsForSale($productId,$productQuantity,$remissionDateAsString,$warehouseId);		
									}
											
									//echo "got sales materials";
									
									//pr($usedMaterials);
									for ($k=0;$k<count($usedMaterials);$k++){
										$materialUsed=$usedMaterials[$k];
										$stockItemId=$materialUsed['id'];
										$quantity_present=$materialUsed['quantity_present'];
										$quantity_used=$materialUsed['quantity_used'];
										$quantity_remaining=$materialUsed['quantity_remaining'];
										
										if (!$this->StockItem->exists($stockItemId)) {
											throw new NotFoundException(__('Invalid Purchase'));
										}
										
										$stockItem=$this->StockItem->find('first',array ('conditions'=>array('StockItem.id'=>$stockItemId)));
										//pr($stockItem);
										$message="Sold stockitem ".$productName." (Quantity:".$quantity_used.") for Sale ".$orderCode;
										$newStockItemData=[];
										$newStockItemData['id']=$stockItemId;
										$newStockItemData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$orderCode."_".$productName;
										$newStockItemData['description']=$stockItem['StockItem']['description']."|".$message;
										$newStockItemData['remaining_quantity']=$quantity_remaining;
										
										if (!$this->StockItem->save($newStockItemData)) {
											echo "problema al guardar el lote";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}

										// STEP 2: SAVE THE STOCK MOVEMENT
										$newStockMovementData=[];
										$message="Vendió lote ".$productName." (Usado:".$quantity_used.", total para venta:".$productQuantity.") para Venta ".$orderCode;
										
										$newStockMovementData=[];
										$newStockMovementData['movement_date']=$remission_date;
										$newStockMovementData['bool_input']='0';
										$newStockMovementData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$orderCode."_".$productName;
										$newStockMovementData['description']=$message;
										$newStockMovementData['order_id']=$id;
										$newStockMovementData['stockitem_id']=$stockItemId;
										$newStockMovementData['product_id']=$productId;
										$newStockMovementData['product_quantity']=$quantity_used;
										$newStockMovementData['product_unit_price']=$productUnitPrice;
										$newStockMovementData['product_total_price']=$productUnitPrice*$quantity_used;
										$newStockMovementData['production_result_code_id']=$productionResultCodeId;
										//pr($newStockMovementData);
										$this->StockMovement->create();
										if (!$this->StockMovement->save($newStockMovementData)) {
											echo "problema al guardar el movimiento de remisión";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}
										
										// STEP 3: SAVE THE STOCK ITEM LOG
										$this->recreateStockItemLogs($stockItemId);
										
										// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
										$this->recordUserActivity($this->Session->read('User.username'),$message);
									}
								}
							
									
								$datasource->commit();
								$this->recordUserAction();
								// SAVE THE USERLOG FOR THE PURCHASE
								//echo "committed";
								$this->recordUserActivity($this->Session->read('User.username'),"Remisión número ".$this->request->data['Order']['order_code']." editada");
								//echo "userlog written away";
								$this->Session->setFlash(__('Se editó la remisión.'),'default',['class' => 'success']);
								//echo "starting to redirect to action viewSale for order id ".$id;
								return $this->redirect(array('action' => 'verRemision',$id));
							} 
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('No se podía guardar la remisión.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
							}
							if (!$newDataSaved){
                //echo "new data not saved<br/>";
								$datasource=$this->Order->getDataSource();
								$datasource->begin();	
								try {
									if (!empty($stockMovementsOriginalRemission)){
										foreach ($stockMovementsOriginalRemission as $originalStockMovement){						
											// set all stockmovements to 0
											$restoredStockMovementData=[];
											$restoredStockMovementData['id']=$originalStockMovement['StockMovement']['id'];
											$restoredStockMovementData['description']=$originalStockMovement['StockMovement']['description'];
											$restoredStockMovementData['product_quantity']=$originalStockMovement['StockMovement']['product_quantity'];
											$restoredStockMovementData['product_total_price']=$originalStockMovement['StockMovement']['product_total_price'];								
											if (!$this->StockMovement->save($restoredStockMovementData)) {
												echo "problema al guardar el movimiento de salida";
												pr($this->validateErrors($this->StockMovement));
												throw new Exception();
											}
											
											// restore the stockitems to their previous level
											$restoredStockItemData=[];
											$restoredStockItemData['id']=$originalStockMovement['StockItem']['id'];
											$restoredStockItemData['description']=$originalStockMovement['StockItem']['description'];
											$restoredStockItemData['remaining_quantity']=$originalStockMovement['StockItem']['remaining_quantity'];
											if (!$this->StockItem->save($restoredStockItemData)) {
												echo "problema al guardar el lote";
												pr($this->validateErrors($this->StockItem));
												throw new Exception();
											}
											
											$this->recreateStockItemLogs($originalStockMovement['StockItem']['id']);
										}
									}					
									if (!empty($originalCashReceipt)){				
										if (!empty($originalCashReceipt['AccountingRegisterCashReceipt'])){
											foreach ($originalCashReceipt['AccountingRegisterCashReceipt'] as $originalAccountingRegisterCashReceipt){
												if (!empty($originalAccountingRegisterCashReceipt['AccountingRegister']['AccountingMovement'])){
													foreach ($originalAccountingRegisterCashReceipt['AccountingRegister']['AccountingMovement'] as $originalAccountingMovement){
														$accountingMovementArray=$originalAccountingMovement;
														$this->AccountingMovement->create();
														if (!$this->AccountingMovement->save($accountingMovementArray)) {
															echo "problema al guardar el movimeinto contable";
															pr($this->validateErrors($this->AccountingMovement));
															throw new Exception();
														}
													}
												}
												$accountingRegisterArray=$originalAccountingRegisterCashReceipt['AccountingRegister'];
												$this->AccountingRegister->create();
												if (!$this->AccountingRegister->save($restoredStockItemData)) {
													echo "problema al guardar el asiento contable";
													pr($this->validateErrors($this->AccountingRegister));
													throw new Exception();
												}
												$accountingRegisterCashReceiptArray=[];
                        
                        $accountingRegisterCashReceiptArray['accounting_register_id']=$originalAccountingRegisterCashReceipt['accounting_register_id'];
                        $accountingRegisterCashReceiptArray['cash_receipt_id']=$originalAccountingRegisterCashReceipt['cash_receipt_id'];
												$this->AccountingRegisterCashReceipt->create();
												if (!$this->AccountingRegisterCashReceipt->save($accountingRegisterCashReceiptArray)) {
													echo "problema al guardar el vínculo entre asiento contable y factura";
													pr($this->validateErrors($this->AccountingRegisterCashReceipt));
													throw new Exception();
												}
											}
										}
										$cashReceiptArray=$originalCashReceipt['CashReceipt'];
										$this->CashReceipt->create();
										if (!$this->CashReceipt->save($cashReceiptArray)) {
											echo "problema al guardar el recibo de caja";
											pr($this->validateErrors($this->CashReceipt));
											throw new Exception();
										}
										$this->CashReceipt->delete($originalCashReceipt['CashReceipt']['id']);
									}						
									$datasource->commit();
									$this->recordUserActivity($this->Session->read('User.username'),"Se recrearon los datos viejos para remisión ".$this->request->data['Order']['order_code']);
									//$oldDataRemoved=true;
								}
								catch(Exception $e){
									$datasource->rollback();
									pr($e);
									$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',['class' => 'error-message']);
								}
							}	
						}
					}
				}
			}
		} 
		else {
			$options = array(
				'conditions' => array(
					'Order.id' => $id,
				),
				'contain'=>array(
					'CashReceipt'=>array(
						'AccountingRegisterCashReceipt'=>array(
							'AccountingRegister'=>array(
								'AccountingMovement'
							),
							'CashReceipt',
						),
					),
				),
			);
			$this->request->data = $this->Order->find('first', $options);
			
      $warehouseId=$this->request->data['Order']['warehouse_id'];
      
			$this->StockMovement->recursive=0;
			$this->StockMovement->virtualFields['total_product_quantity']=0;
			$this->StockMovement->virtualFields['total_product_price']=0;
			$stockMovements=$this->StockMovement->find('all',array(
				'fields'=>array(
					'StockItem.warehouse_id',
					'StockMovement.product_id',
					'StockItem.raw_material_id',
					'StockMovement.production_result_code_id',
					'SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity', 
					'SUM(product_total_price) AS StockMovement__total_product_price', 
					
				),
				'conditions'=>array(
					'StockMovement.product_quantity >'=>0,
					'StockMovement.order_id'=>$id,
				),
				'group'=>'StockMovement.product_id,StockItem.raw_material_id,StockMovement.production_result_code_id',
			));
			if (!empty($stockMovements)){
				$warehouseId=$stockMovements[0]['StockItem']['warehouse_id'];
				foreach ($stockMovements as $stockMovement){
					//pr($stockMovement);
					$productArray=[];
					$productArray['product_id']=$stockMovement['StockMovement']['product_id'];
					$productArray['raw_material_id']=$stockMovement['StockItem']['raw_material_id'];
					$productArray['production_result_code_id']=$stockMovement['StockMovement']['production_result_code_id'];
					$productArray['product_quantity']=$stockMovement['StockMovement']['total_product_quantity'];
					//$productArray['product_unit_price']=round($stockMovement['StockMovement']['total_product_price']/$stockMovement['StockMovement']['total_product_quantity'],4);
          $productArray['product_unit_price']=$stockMovement['StockMovement']['total_product_price']/$stockMovement['StockMovement']['total_product_quantity'];
					$productArray['product_total_price']=$stockMovement['StockMovement']['total_product_price'];
					$requestProducts[]['Product']=$productArray;
				}
			}
			$this->StockMovement->recursive=-1;
			
			$this->CashReceipt->recursive=-1;
			$cashReceipt=$this->CashReceipt->find('first',array(
				'conditions'=>array(
					'CashReceipt.order_id'=>$id,
				)
			));
		}
		$this->set(compact('warehouseId'));
		$this->set(compact('inventoryDisplayOptionId'));
		$this->set(compact('requestProducts'));
		
		$this->set(compact('cashReceipt'));
		
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if ($warehouseId == 0 && count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    $plantId=$this->Warehouse->getPlantId($warehouseId);
		$this->set(compact('plantId'));    
    
    
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions'=>array(
				'OR'=>array(
					array(
						'ThirdParty.bool_provider'=>'0',
						'ThirdParty.bool_active'=>true,
					),
					array(
						'ThirdParty.id'=>$this->request->data['Order']['third_party_id'],
					),
				),
			),
			'order'=>'ThirdParty.company_name',		
		));
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		$this->Product->recursive=-1;
		// Remove the raw products from the dropdown list
		if (is_array($this->request->data['Order']['order_date'])){
			$orderDateArray=$this->request->data['Order']['order_date'];
			$orderDateString=$orderDateArray['year'].'-'.$orderDateArray['month'].'-'.$orderDateArray['day'];
			$orderDate=date("Y-m-d",strtotime($orderDateString));
			$orderDatePlusOne=date("Y-m-d",strtotime($orderDateString."+1 days"));
		}
		else {
			$orderDate=date("Y-m-d",strtotime($this->request->data['Order']['order_date']));
			$orderDatePlusOne=date("Y-m-d",strtotime($this->request->data['Order']['order_date']."+1 days"));
		}
		$this->set(compact('orderDate'));
    
    //pr($requestProducts);
    $finishedProductsInSale=[];
    $rawMaterialsInSale=[];
    foreach ($requestProducts as $requestProduct){
      if (!in_array($requestProduct['Product']['product_id'],$finishedProductsInSale)){
        $finishedProductsInSale[]=$requestProduct['Product']['product_id'];
      }
      if (!in_array($requestProduct['Product']['raw_material_id'],$rawMaterialsInSale)){
        $rawMaterialsInSale[]=$requestProduct['Product']['raw_material_id'];
      }
    }
    
		$productsAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
        'conditions'=>[
          'OR'=>[
            [
              'Product.bool_active'=>true
            ],
            [
              'Product.id'=>$finishedProductsInSale
            ]
          ],
        ],
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('remaining_quantity','raw_material_id'),
						'conditions'=>array(
							//'StockItem.remaining_quantity >'=>0,
							'StockItem.stockitem_creation_date <='=>$orderDatePlusOne,
              'StockItem.bool_active'=>true,
						),
					),
				),
				'order'=>'product_type_id DESC, name ASC',
			)
		);
		$products = [];
		$rawMaterialIds=[];
		$stockItemsOfSoldProducts=$this->StockMovement->find('all',array(
			'fields'=>array(
				'StockMovement.stockitem_id',
			),
			'conditions'=>array(
				'StockMovement.order_id'=>$id,
				'StockMovement.product_quantity>0',
			),
			'contain'=>array(
				'StockItem'=>array(
					'fields'=> array('id','product_id','remaining_quantity','raw_material_id'),
				),
			),
		));
		//pr($stockItemsOfSoldProducts);
		$stockItemRawMaterialIdsForProductId=[];
		foreach ($stockItemsOfSoldProducts as $soldProduct){
			$stockItemRawMaterialIdsForProductId[$soldProduct['StockItem']['product_id']]=$soldProduct['StockItem']['raw_material_id'];
		}
		//pr($stockItemRawMaterialIdsForProductId);
		foreach ($productsAll as $product){
			// only show products that are in inventory
			if (!empty($product['StockItem'])){
				$products[$product['Product']['id']]=$product['Product']['name'];
				foreach ($product['StockItem'] as $stockitem){
					if ($stockitem['remaining_quantity']>0){
						// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
						// in this case the associative array just contains the product_id because otherwise the list would become very long
            if (!in_array($stockitem['raw_material_id'],$rawMaterialIds) && !empty($stockitem['raw_material_id'])){
              $rawMaterialIds[]=$stockitem['raw_material_id'];
            }
					}
				}
			}
			if (in_array($product['Product']['id'],array_keys($stockItemRawMaterialIdsForProductId))){
				//pr($product['Product']['id']);
				//pr(array_keys($stockItemRawMaterialIdsForProductId));
				$products[$product['Product']['id']]=$product['Product']['name'];
        if (!in_array($stockitem['raw_material_id'],$rawMaterialIds) && !empty($stockitem['raw_material_id'])){
          $rawMaterialIds[]=$stockItemRawMaterialIdsForProductId[$product['Product']['id']];
        }
			}
		}
		
		$this->loadModel('ProductionResultCode');
		$productionResultCodes=$this->ProductionResultCode->find('list');
    
    //$rawProductTypeIds=$this->ProductType->find('list',array(
    //  'fields'=>'ProductType.id',
    //  'conditions'=>array(
    //    'ProductType.product_category_id'=> CATEGORY_RAW
    //  ),
    //));
    
		$preformasAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
        'conditions'=>[
          'OR'=>[
            [
              'Product.id'=>$rawMaterialIds,
              'Product.bool_active'=>true
            ],
            [
              'Product.id'=>$rawMaterialIds,
              'Product.id'=>$rawMaterialsInSale
            ]
          ],
        ],
				//'contain'=>array(
				//	'ProductType',
				//	'StockItem'=>array(
				//		'fields'=> array('remaining_quantity')
				//	)
				//),
				
			)
		);
		$rawMaterials=[];
		foreach ($preformasAll as $preforma){
			$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
		}
		if (!empty($inventoryDisplayOptionId)){
			$this->loadModel('ProductType');
			$productCategoryId=CATEGORY_PRODUCED;
			$productTypeIds=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productCategoryId)
			));
			$finishedMaterialsInventory =[];
			$finishedMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
			$productCategoryId=CATEGORY_OTHER;
			$productTypeIds=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productCategoryId)
			));
			$otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
			$productCategoryId=CATEGORY_RAW;
			$productTypeIds=$this->ProductType->find('list',array(
				'fields'=>array('ProductType.id'),
				'conditions'=>array('ProductType.product_category_id'=>$productCategoryId)
			));
			$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,$orderDate,$warehouseId);
		}	
		$currencies = $this->Currency->find('list');
		$cashReceiptTypes = $this->CashReceiptType->find('list');
		
		//$accountingCodes = $this->AccountingCode->find('list',array('fields'=>array('AccountingCode.id','AccountingCode.shortfullname')));
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			)
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			)
		));
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','rawMaterials','currencies','accountingCodes','cashReceiptTypes'));
		
		$this->loadModel('ExchangeRate');
		//$orderDate=date( "Y-m-d");
		$saleDateString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
		$orderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateString);
		$exchangeRateOrder=$orderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
		$this->loadModel('Warehouse');
		$warehouses=$this->Warehouse->find('list',array(
			'order'=>'Warehouse.name',
		));
		$this->set(compact('warehouses'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function eliminarEntrada($id = null) {
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Entrada inválida'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('StockMovement');
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
    
    $this->loadModel('PurchaseOrder');
    $this->loadModel('PurchaseOrderInvoice');
    
		
		$entry=$this->Order->find('first', [
			'conditions' => [
				'Order.id' => $id,
			],
			'contain'=>[
        'PurchaseOrderInvoice',
				'StockMovement'=>[
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
								'StockMovement.order_id !='=>$id,
							],
							'Order',
						],	
					],
				],
			],
		]);
		$flashMessage="";
		$boolDeletionAllowed=true;
		if (!empty($entry['StockMovement'])){
			foreach ($entry['StockMovement'] as $stockMovement){
				if (!empty($stockMovement['StockItem']['ProductionMovement'])){
					$boolDeletionAllowed='0';
				}
				if (!empty($stockMovement['StockItem']['StockMovement'])){
					$boolDeletionAllowed='0';
				}
			}
			if (!$boolDeletionAllowed){
				$flashMessage.="Los productos de la entrada no se pueden editar porque ya se han utilizado en ";
				foreach ($entry['StockMovement'] as $stockMovement){
					foreach ($stockMovement['StockItem']['ProductionMovement'] as $productionMovement){						
						if (!empty($productionMovement['ProductionRun'])){
							//pr($productionMovement['ProductionRun']);
							$flashMessage.="orden de producción ".$productionMovement['ProductionRun']['production_run_code']." ";
						}
						else {
							//pr($productionMovement);
						}
						
					}
					foreach ($stockMovement['StockItem']['StockMovement'] as $stockMovement){
						//pr($productionMovement['StockItem']['StockMovement']);
						if (!$stockMovement['bool_transfer']){
							if (!empty($stockMovement['Order'])){
								//pr($productionMovement['StockItem']['StockMovement']['Order']);
								$flashMessage.=$stockMovement['Order']['order_code']." ";
							}
							else {
								//pr($productionMovement['StockItem']['StockMovement']);
							}
						}
						else {
							$flashMessage.=$stockMovement['transfer_code']." ";
						}
					}
				}
			}
		}
		if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó la entrada.";
			$this->Session->setFlash($flashMessage, 'default',['class' => 'error-message']);
			return $this->redirect(array('action' => 'verEntrada',$id));
		}
		else {
			$datasource=$this->Order->getDataSource();
			$datasource->begin();
			try {
        if (!empty($entry['PurchaseOrderInvoice'])){
          foreach ($entry['PurchaseOrderInvoice'] as $previousPurchaseOrderInvoiceId){
            $this->PurchaseOrderInvoice->id=$previousPurchaseOrderInvoiceId;
            if (!$this->PurchaseOrderInvoice->delete($previousPurchaseOrderInvoiceId)) {
              echo "problema eliminando la información obsoleta de facturas";
              pr($this->validateErrors($this->PurchaseOrderInvoice));
              throw new Exception();
            }
          }
        }
        
				//delete all stockMovements, stockItems and stockItemLogs
				foreach ($entry['StockMovement'] as $stockMovement){
					//pr($stockMovement['StockItem']);
				
					if (!$this->StockMovement->delete($stockMovement['id'])) {
						echo "Problema al eliminar el movimiento de entrada en bodega";
						pr($this->validateErrors($this->StockMovement));
						throw new Exception();
					}
					
					if (!empty($stockMovement['StockItem']['StockItemLog'])){
						foreach ($stockMovement['StockItem']['StockItemLog'] as $stockItemLog){
							if (!$this->StockItemLog->delete($stockItemLog['id'])) {
								echo "Problema al eliminar el estado de lote";
								pr($this->validateErrors($this->StockItemLog));
								throw new Exception();
							}
						}
					}
					
					if (!empty($stockMovement['StockItem']['id'])){
						if (!$this->StockItem->delete($stockMovement['StockItem']['id'])) {
							echo "Problema al eliminar el lote de bodega";
							pr($this->validateErrors($this->StockItem));
							throw new Exception();
						}
					}
				}			
				
        $this->PurchaseOrder->id=$entry['Order']['purchase_order_id'];
        $purchaseOrderArray=[
          'PurchaseOrder'=>[
            'id'=>$entry['Order']['purchase_order_id'],
            'purchase_order_state_id'=>PURCHASE_ORDER_STATE_CONFIRMED_WITH_CLIENT,
          ],
        ];
        if (!$this->PurchaseOrder->save($purchaseOrderArray)) {
					echo "Problema al restablecer el estado de la orden de compra";
					pr($this->validateErrors($this->PurchaseOrder));
					throw new Exception();
				}
        
				if (!$this->Order->delete($id)) {
					echo "Problema al eliminar la entrada";
					pr($this->validateErrors($this->Order));
					throw new Exception();
				}
						
				$datasource->commit();
			/*
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=[];
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$entry['Order']['id'];
				$deletionArray['Deletion']['reference']=$entry['Order']['order_code'];
				$deletionArray['Deletion']['type']='Order';
				$this->Deletion->save($deletionArray);
			*/			
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la entrada número ".$entry['Order']['order_code']);
						
				$this->Session->setFlash(__('Se eliminó la entrada.'),'default',['class' => 'success']);				
				return $this->redirect(array('action' => 'resumenEntradas'));
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar la entrada.'), 'default',['class' => 'error-message']);
				return $this->redirect(array('action' => 'verEntrada',$id));
			}
		}
	}
	
/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
    
    $this->loadModel('StockItem');
		
    $this->loadModel('SalesOrder');
		$this->loadModel('Invoice');
		$this->loadModel('CashReceipt');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterInvoice');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$linkedSale=$this->Order->find('first',[
			'conditions'=>[
				'Order.id'=>$id,
			],
      'contain'=>[
        'Invoice'=>[
          'AccountingRegisterInvoice'=>[
            'AccountingRegister'=>[
              'AccountingMovement',
            ]
          ]
        ],
        'StockMovement'=>['StockItem'],
      ],
		]);
    //pr($linkedSale);
		$orderCode=$linkedSale['Order']['order_code'];
		
		$this->request->allowMethod('post', 'delete');
    
		$datasource=$this->Order->getDataSource();
		$datasource->begin();
		try {
			// reestablish stockitem quantity
			foreach ($linkedSale['StockMovement'] as $stockMovement){
        // 20190429 stockitem is empty for service product type
				if (!empty($stockMovement['StockItem']['id'])){
          $stockItem=[
            'StockItem'=>$stockMovement['StockItem']
          ];
          $stockItem['StockItem']['remaining_quantity']+=$stockMovement['product_quantity'];
          $stockItem['StockItem']['description'].="|eliminated sale ".$orderCode;
          $this->StockItem->id=$stockMovement['StockItem']['id'];
          if (!$this->StockItem->save($stockItem)) {
            echo "problema eliminando el estado de lote";
            pr($this->validateErrors($this->StockItem));
            throw new Exception();
          }
        }
				
				// delete stockmovements
				$this->Order->StockMovement->id=$stockMovement['id'];
				if (!$this->Order->StockMovement->delete()) {
					echo "problema eliminando el movimiento de lote";
					pr($this->validateErrors($this->Order->StockMovement));
					throw new Exception();
				}
			}
			
			if (!empty($linkedSale['Invoice'][0]['id'])){
				// first remove existing data: invoice, accounting registers, accounting register invoice				
				if (!empty($linkedSale['Invoice'][0]['AccountingRegisterInvoice'])){
					foreach ($linkedSale['Invoice'][0]['AccountingRegisterInvoice'] as $oldAccountingRegisterInvoice){
						// first remove the movement
						if (!empty($oldAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'])){
							foreach ($oldAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'] as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterInvoice['AccountingRegister']['id']);
						// then remove the register invoice link
						$this->AccountingRegisterInvoice->delete($oldAccountingRegisterInvoice['id']);
					}
				}
				// then remove the invoice
				if (!$this->Invoice->delete($linkedSale['Invoice'][0]['id'])) {
					echo "problema al eliminar la factura";
					pr($this->validateErrors($this->Invoice));
					throw new Exception();
				}			
        
        if (!empty($linkedSale['Invoice'][0]['sales_order_id'])){
          //pr($linkedSale['Invoice']);
          $this->SalesOrder->id=$linkedSale['Invoice'][0]['sales_order_id'];
          $salesOrderArray=[
            'SalesOrder'=>[
              'id'=>$linkedSale['Invoice'][0]['sales_order_id'],
              'bool_invoice'=>'0',
              'invoice_id'=>null,
            ]
          ];
          if (!$this->SalesOrder->save($salesOrderArray)) {
            echo "Problema actualizando la orden de venta";
            pr($this->validateErrors($this->SalesOrder));
            throw new Exception();
          }
        }
			}
			
			if (!empty($linkedSale['CashReceipt'][0]['id'])){
				// first remove existing data: cash receipt, accounting registers, accounting register cash receipt				
				
				
				if (!empty($linkedSale['CashReceipt'][0]['AccountingRegisterCashReceipt'])){
					foreach ($linkedSale['CashReceipt'][0]['AccountingRegisterCashReceipt'] as $oldAccountingRegisterCashReceipt){
						// first remove the movement
						if (!empty($oldAccountingRegisterCashReceipt['AccountingRegister']['AccountingMovement'])){
							foreach ($oldAccountingRegisterCashReceipt['AccountingRegister']['AccountingMovement'] as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingRegisterCashReceipt['AccountingRegister']['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegister']['id']);
						// then remove the register cash receipt link
						$this->AccountingRegisterCashReceipt->delete($oldAccountingRegisterCashReceipt['id']);
					}
				}
				// then remove the cash receipt
				if (!$this->CashReceipt->delete($linkedSale['CashReceipt'][0]['id'])) {
					echo "problema al eliminar el recibo de caja";
					pr($this->validateErrors($this->CashReceipt));
					throw new Exception();
				}			
			}
			
			
			// delete order
			if (!$this->Order->delete($id)) {
				echo "problema al eliminar la venta";
				pr($this->validateErrors($this->Order));
				throw new Exception();
			}
			
			//recreate stockitemlogs
      foreach ($linkedSale['StockMovement'] as $stockMovement){
        if (!empty($stockMovement['stockitem_id'])){
           $this->recreateStockItemLogs($stockMovement['stockitem_id']);
        }				
			}

			$datasource->commit();
			$this->recordUserActivity($this->Session->read('User.username'),"Order removed with code ".$orderCode);			
			$this->Session->setFlash(__('The sale has been deleted.'), 'default',['class' => 'success']);
		} 		
		catch(Exception $e){
			$datasource->rollback();
			pr($e);					
			$this->Session->setFlash(__('The sale could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
    
		return $this->redirect(['action' => 'resumenVentasRemisiones']);
    
	}
	
	public function eliminarRemision($id = null) {
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
		$this->Order->recursive=-1;
		$linkedRemission=$this->Order->find('first',array(
			'conditions'=>array(
				'Order.id'=>$id,
			),
		));
		$orderCode=$linkedRemission['Order']['order_code'];
		$this->loadModel('StockItem');
		
		$this->loadModel('CashReceipt');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$this->request->allowMethod('post', 'delete');
		$datasource=$this->Order->getDataSource();
		$datasource->begin();
		try {
			// find stock movements for order
			$stockMovements=$this->Order->StockMovement->find('all',array(
				'fields'=>array('stockitem_id','product_quantity','StockMovement.id'),
				'conditions'=>array('order_id'=>$id),
			));
			
			// reestablish stockitem quantity
			foreach ($stockMovements as $stockMovement){
				$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$stockMovement['StockMovement']['stockitem_id'])));
				
				$stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
				$stockItem['StockItem']['description'].="|eliminated sale ".$orderCode;
				if (!$this->StockItem->save($stockItem)) {
					echo "problema eliminando el estado de lote";
					pr($this->validateErrors($this->StockItem));
					throw new Exception();
				}
				
				// delete stockmovements
				$this->Order->StockMovement->id=$stockMovement['StockMovement']['id'];
				if (!$this->Order->StockMovement->delete()) {
					echo "problema eliminando el movimiento de lote";
					pr($this->validateErrors($this->Order->StockMovement));
					throw new Exception();
				}
			}
			
			$oldCashReceipt=$this->CashReceipt->find('first',array(
				'conditions'=>array(
					'CashReceipt.order_id'=>$id,
				)
			));
			if (!empty($oldCashReceipt)){
				// first remove existing data: cash receipt, accounting registers, accounting register cash receipt				
				$oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
					'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
					'conditions'=>array(
						'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
					)
				));
				
				if (!empty($oldAccountingRegisterCashReceipts)){
					foreach ($oldAccountingRegisterCashReceipts as $oldAccountingRegisterCashReceipt){
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id']);
						// then remove the register cash receipt link
						$this->AccountingRegisterCashReceipt->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']);
					}
				}
				// then remove the cash receipt
				if (!$this->CashReceipt->delete($oldCashReceipt['CashReceipt']['id'])) {
					echo "problema al eliminar el recibo de caja";
					pr($this->validateErrors($this->CashReceipt));
					throw new Exception();
				}			
			}
			
			// delete order
			if (!$this->Order->delete()) {
				echo "problema al eliminar la remisión";
				pr($this->validateErrors($this->Order));
				throw new Exception();
			}
			
			//recreate stockitemlogs
			foreach ($stockMovements as $stockMovement){
				$this->recreateStockItemLogs($stockMovement['StockMovement']['stockitem_id']);
			}

			$datasource->commit();
			$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la remisión con código ".$orderCode);			
			$this->Session->setFlash(__('Se eliminó la remisión.'), 'default',['class' => 'success']);
		} 		
		catch(Exception $e){
			$datasource->rollback();
			pr($e);					
			$this->Session->setFlash(__('No se eliminó la remisión.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(array('action' => 'resumenVentasRemisiones'));
	}
	
	public function anularVenta ($id=null){
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
    
    $this->loadModel('StockItem');
		
		$this->loadModel('Invoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterInvoice');
		
		$this->request->allowMethod('post', 'put');
    
		$linkedSale=$this->Order->find('first',[
			'conditions'=>[
				'Order.id'=>$id,
			],
      'contain'=>[
        'CashReceipt'=>[
          'AccountingRegisterCashReceipt'=>[
          
          ],
        ],
        'Invoice'=>[
          'AccountingRegisterInvoice'=>[
            'AccountingRegister'=>[
              'AccountingMovement',
            ]
          ]
        ],
        'StockMovement'=>['StockItem'],
      ],
		]);
		$orderCode=$linkedSale['Order']['order_code'];
		
		$datasource=$this->Order->getDataSource();
		$datasource->begin();
		try {
			// reestablish stockitem quantity
			foreach ($linkedSale['StockMovement'] as $stockMovement){
				// stock item id is 0 for services
        if (!empty($stockMovement['StockItem']['id'])){
          $stockItem=[
            'StockItem'=>$stockMovement['StockItem']
          ];
				
          $stockItem['StockItem']['remaining_quantity']+=$stockMovement['product_quantity'];
          $stockItem['StockItem']['description'].="|eliminated sale ".$orderCode;
          if (!$this->StockItem->save($stockItem)) {
            echo "problema eliminando el estado de lote";
            pr($this->validateErrors($this->StockItem));
            throw new Exception();
          }
        }
        // delete stockmovements
        $this->Order->StockMovement->id=$stockMovement['id'];
        if (!$this->Order->StockMovement->delete()) {
          echo "problema eliminando el movimiento de lote";
          pr($this->validateErrors($this->Order->StockMovement));
          throw new Exception();
        }
			}
		
			// first remove existing data: cash receipt, accounting registers, accounting register cash receipt
			if (!empty($linkedSale['Invoice'][0]['id'])){
				if (!empty($linkedSale['Invoice'][0]['AccountingRegisterInvoice'])){
					foreach ($linkedSale['Invoice'][0]['AccountingRegisterInvoice'] as $oldAccountingRegisterInvoice){
						// first remove the movement
						if (!empty($oldAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'])){
							foreach ($oldAccountingRegisterInvoice['AccountingRegister']['AccountingMovement'] as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterInvoice['AccountingRegister']['id']);
						// then remove the register invoice link
						$this->AccountingRegisterInvoice->delete($oldAccountingRegisterInvoice['id']);
					}
				}
				// then remove the invoice
				if (!$this->Invoice->delete($linkedSale['Invoice'][0]['id'])) {
					echo "problema al eliminar la factura";
					pr($this->validateErrors($this->Invoice));
					throw new Exception();
				}		
			}
			
			//recreate stockitemlogs
			foreach ($linkedSale['StockMovement'] as $stockMovement){
        if (!empty($stockMovement['stockitem_id'])){
          $this->recreateStockItemLogs($stockMovement['stockitem_id']);
        }
			}
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',['class' => 'error-message']);
		}
		// then save the minimum data for the annulled invoice/order				

		try {
			$orderData=[];
			$orderData['Order']['id']=$id;
			$orderData['Order']['bool_annulled']=true;
			$orderData['Order']['total_price']=0;
			$this->Order->id=$id;
      //pr($orderData);
			if (!$this->Order->save($orderData)) {
				echo "Problema anulando la venta";
				pr($this->validateErrors($this->Order));
				throw new Exception();
			}
      //else {
      //  echo "Se anuló la venta";
      //}
			
			$orderId=$this->Order->id;
			$this->Order->recursive=-1;
			$linkedOrder=$this->Order->find('first',[
        'conditions'=>[
          'Order.id'=>$orderId,],
      ]);
			//pr($linkedOrder);		
			$this->Invoice->create();
			$invoiceData=[];
			$invoiceData['Invoice']['order_id']=$orderId;
			$invoiceData['Invoice']['invoice_code']=$linkedOrder['Order']['order_code'];
			$invoiceData['Invoice']['invoice_date']=date( 'Y-m-d', strtotime($linkedOrder['Order']['order_date']));
			$invoiceData['Invoice']['bool_annulled']=true;
			$invoiceData['Invoice']['client_id']=$linkedOrder['Order']['third_party_id'];
			$invoiceData['Invoice']['total_price']=0;
			$invoiceData['Invoice']['currency_id']=CURRENCY_CS;
	
			if (!$this->Invoice->save($invoiceData)) {
				echo "Problema guardando la factura";
				pr($this->validateErrors($this->Invoice));
				throw new Exception();
			}
			$datasource->commit();
      
      //echo 'jippie saved<br/>';
      //$linkedOrder=$this->Order->find('first',[
      //  'conditions'=>[
      //    'Order.id'=>$orderId,],
      //]);
			//pr($linkedOrder);	
				
			// SAVE THE USERLOG 
			$this->recordUserActivity($this->Session->read('User.username'),"Se anuló la venta con número ".$linkedOrder['Order']['order_code']);
			$this->Session->setFlash('Se anuló la venta con número '.$linkedOrder['Order']['order_code'],'default',['class' => 'success'],'default',['class' => 'success']);
			return $this->redirect(['action' => 'resumenVentasRemisiones']);
		}
		catch(Exception $e){
      //echo 'rolling back';
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('No se podía anular la venta.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
		}
    
    return $this->redirect(['action' => 'verVenta',$id]);
	}
	
	public function anularRemision ($id=null){
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
		$this->Order->recursive=-1;
		$linkedRemission=$this->Order->find('first',array(
			'conditions'=>array(
				'Order.id'=>$id,
			),
		));
		$orderCode=$linkedRemission['Order']['order_code'];
		$this->loadModel('StockItem');
		
		$this->loadModel('CashReceipt');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$this->request->allowMethod('post', 'delete');
		
		$datasource=$this->Order->getDataSource();
		$oldCashReceipt=$this->CashReceipt->find('first',array(
			'conditions'=>array(
				'CashReceipt.order_id'=>$id,
			)
		));
		try {
			$stockMovements=$this->Order->StockMovement->find('all',array(
				'fields'=>array('stockitem_id','product_quantity','StockMovement.id'),
				'conditions'=>array('order_id'=>$id),
			));
			
			// reestablish stockitem quantity
			foreach ($stockMovements as $stockMovement){
				$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$stockMovement['StockMovement']['stockitem_id'])));
				
				$stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
				$stockItem['StockItem']['description'].="|eliminated sale ".$orderCode;
				$success=$this->StockItem->save($stockItem);
				if (!$success) {
					echo "problema eliminando el estado de lote";
					pr($this->validateErrors($this->StockItem));
					throw new Exception();
				}
				
				// delete stockmovements
				$this->Order->StockMovement->id=$stockMovement['StockMovement']['id'];
				$success=$this->Order->StockMovement->delete();
				if (!$success) {
					echo "problema eliminando el movimiento de lote";
					pr($this->validateErrors($this->Order->StockMovement));
					throw new Exception();
				}
			}
		
			// first remove existing data: cash receipt, accounting registers, accounting register cash receipt
			$oldAccountingRegisterCashReceipts=[];
			if (!empty($oldCashReceipt)){
				$oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
					'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
					'conditions'=>array(
						'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
					)
				));
			
				if (!empty($oldAccountingRegisterCashReceipts)){
					foreach ($oldAccountingRegisterCashReceipts as $oldAccountingRegisterCashReceipt){
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id']);
						// then remove the register cash receipt link
						$this->AccountingRegisterCashReceipt->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']);
					}
				}
				// then remove the cash receipt
			
				$this->CashReceipt->delete($oldCashReceipt['CashReceipt']['id']);
			}
			
			//recreate stockitemlogs
			foreach ($stockMovements as $stockMovement){
				$this->recreateStockItemLogs($stockMovement['StockMovement']['stockitem_id']);
			}
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',['class' => 'error-message']);
		}
		// then save the minimum data for the annulled invoice/order				
		$datasource=$this->Order->getDataSource();
		$datasource->begin();
		try {
			//pr($this->request->data);
			
			
			$orderData=[];
			$orderData['Order']['id']=$id;
			$orderData['Order']['bool_annulled']=true;
			$orderData['Order']['total_price']=0;
			$this->Order->id=$id;
	
			if (!$this->Order->save($orderData)) {
				echo "Problema anulando la remisión";
				pr($this->validateErrors($this->Order));
				throw new Exception();
			}
			
			$orderId=$this->Order->id;
			$this->Order->recursive=-1;
			$linkedOrder=$this->Order->find('first',array('conditions'=>array('Order.id'=>$orderId)));
			//pr($linkedOrder);		
			$this->CashReceipt->create();
			
			$CashReceiptData=[];
			$CashReceiptData['CashReceipt']['order_id']=$orderId;
			$CashReceiptData['CashReceipt']['receipt_code']=$linkedOrder['Order']['order_code'];
			$CashReceiptData['CashReceipt']['receipt_date']=date( 'Y-m-d', strtotime($linkedOrder['Order']['order_date']));
			$CashReceiptData['CashReceipt']['bool_annulled']=true;
			$CashReceiptData['CashReceipt']['client_id']=$linkedOrder['Order']['third_party_id'];
			$CashReceiptData['CashReceipt']['cash_receipt_type_id']=CASH_RECEIPT_TYPE_REMISSION;
			$CashReceiptData['CashReceipt']['amount']=0;
			$CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
	
			if (!$this->CashReceipt->save($CashReceiptData)) {
				echo "Problema anulando el recibo de caja para la remisión anulada";
				pr($this->validateErrors($this->CashReceipt));
				throw new Exception();
			}
			
			$datasource->commit();
				
			// SAVE THE USERLOG 
			$this->recordUserActivity($this->Session->read('User.username'),"Se anuló la remisión con número ".$linkedOrder['Order']['order_code']);
			$this->Session->setFlash(__('Se anuló la remisión.'),'default',['class' => 'success'],'default',['class' => 'success']);
			return $this->redirect(array('action' => 'resumenVentasRemisiones'));

		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('No se podía anular la remisión.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
		}
		
	}
	
  public function verProveedoresPorPagar() {
		$this->loadModel('ExchangeRate');
		$this->loadModel('ThirdParty');
    
    $this->loadModel('PurchaseOrderInvoice');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
		$loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $this->Order->recursive = -1;
    $this->ThirdParty->recursive=-1;
    
		$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
		$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
		
    $warehouseId=0;
    
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
		
    
    $providers=$this->ThirdParty->find('all',[
			'fields'=>[
				'ThirdParty.company_name','ThirdParty.id','ThirdParty.credit_days',
			],
			'conditions'=>[
				'bool_provider'=>true,
				'bool_active'=>true,
			],
			'order'=>'ThirdParty.company_name',
		]);
		//pr($providers);
		
		for ($p=0;$p<count($providers);$p++){
			$pendingEntries=$this->Order->find('all',[
				'fields'=>[
					'Order.id','Order.order_code',
					//'Order.total_price',
          'Order.entry_cost_total',
					'Order.order_date',
					'Order.third_party_id',
				],
				'conditions'=>[
					'Order.bool_annulled'=>'0',
					'Order.bool_entry_paid'=>'0',
					'Order.third_party_id'=>$providers[$p]['ThirdParty']['id'],
          'Order.order_date >'=>date('2019-06-01'),
				],
        'contain'=>[
          // all orders are in C$
          //'Currency'=>['fields'=>['abbreviation','id',]],
          'PurchaseOrderInvoice'=>[
            'conditions'=>[
              'PurchaseOrderInvoice.bool_paid'=>'0',
            ],
          ],
        ],
				'order'=>'Order.order_date ASC',
			]);
			
			$totalPending=0;
			$pendingUnder30=0;
			$pendingUnder45=0;
			$pendingUnder60=0;
			$pendingOver60=0;
      
      $nowDateTime= new DateTime();
			for ($po=0;$po<count($pendingEntries);$po++){
        if (empty($pendingEntries[$po]['PurchaseOrderInvoice'])){
          $totalForEntryCS=$pendingEntries[$po]['Order']['entry_cost_total'];
          
          $pendingForEntry=$totalForEntryCS;
          $totalPending+=$pendingForEntry;
          
          $entryDateTime=new DateTime($pendingEntries[$po]['Order']['order_date']);
          //$entryDatePlusCreditDays=date("Y-m-d",strtotime($pendingEntries[$po]['Order']['order_date']."+".$providers[$p]['ThirdParty']['credit_days']." days"));
          //$dueDateTime= new DateTime($entryDatePlusCreditDays);
          
          
          $daysLate=$nowDateTime->diff($entryDateTime);
          //if ($pendingEntries[$po]['Order']['third_party_id'] == 80){
          //  pr($providers[$p]['ThirdParty']);
          //  pr($dueDateTime);
          //  pr($nowDateTime);
          //  pr($daysLate);
          //}
          
          if ($daysLate->days<31){
            $pendingUnder30+=$pendingForEntry;
          }
          else if ($daysLate->days<46){
            $pendingUnder45+=$pendingForEntry;
          }
          else if ($daysLate->days<61){
            $pendingUnder60+=$pendingForEntry;
          }
          else{
            $pendingOver60+=$pendingForEntry;
          }
        }
        else {
          foreach ($pendingEntries[$po]['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
            $totalForInvoiceCS=$purchaseOrderInvoice['invoice_total'];
            
            $pendingForInvoice=$totalForInvoiceCS;
            $totalPending+=$pendingForInvoice;
            
            $invoiceDateTime=new DateTime($purchaseOrderInvoice['invoice_date']);
            //$invoiceDatePlusCreditDays=date("Y-m-d",strtotime($purchaseOrderInvoice['invoice_date']."+".$providers[$p]['ThirdParty']['credit_days']." days"));
            //$dueDateTime= new DateTime($invoiceDatePlusCreditDays);
            $daysLate=$nowDateTime->diff($invoiceDateTime);
            if ($daysLate->days<31){
              $pendingUnder30+=$pendingForInvoice;
            }
            else if ($daysLate->days<46){
              $pendingUnder45+=$pendingForInvoice;
            }
            else if ($daysLate->days<61){
              $pendingUnder60+=$pendingForInvoice;
            }
            else{
              $pendingOver60+=$pendingForInvoice;
            }
          }
        }
			}
			$providers[$p]['saldo']=$totalPending;
			$providers[$p]['pendingUnder30']=$pendingUnder30;
			$providers[$p]['pendingUnder45']=$pendingUnder45;
			$providers[$p]['pendingUnder60']=$pendingUnder60;
			$providers[$p]['pendingOver60']=$pendingOver60;
		}
		
		$this->set(compact('providers'));
	}
	
	public function guardarProveedoresPorPagar() {
		$exportData=$_SESSION['proveedoresPorPagar'];
		$this->set(compact('exportData'));
	}
	
  public function verFacturasPorPagar($providerId=0) {
		$this->loadModel('ExchangeRate');
		$this->loadModel('ThirdParty');
    
    $this->loadModel('PurchaseOrderInvoice');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
		$loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $this->Order->recursive = -1;
		$this->ThirdParty->recursive=-1;
    
		$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
		$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
		
		$provider=$this->ThirdParty->getProviderById($providerId);
		
    if ($userRoleId == ROLE_ADMIN){  
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      else {
        $warehouseId = WAREHOUSE_DEFAULT;
      }
		}
    if ($this->request->is('post')) {
			//pr($this->request->data);
      $chequeNumbersOK=true;
      $paymentCount=0;
      $chequeError="";
      if (!empty($this->request->data['Entry'])){
        foreach ($this->request->data['Entry'] as $entryId => $entryData){
          if ($entryData['payment']){
            $paymentCount++;
            if (empty($entryData['entry_cheque_number'])){
              $chequeNumbersOK='0';
              $thisEntry=$this->Order->find('first',[
                'fields'=>'order_code',
                'conditions'=>['Order.id'=>$entryId],
              ]);
              $chequeError.="Se marcó la entrada ".(empty($thisEntry)?"":($thisEntry['Order']['order_code']." "))."como cancelada pero no se indicó el número de cheque. ";
            }
          }
        }
      }
      if (!empty($this->request->data['PurchaseOrderInvoice'])){
        foreach ($this->request->data['PurchaseOrderInvoice'] as $purchaseOrderInvoiceId => $purchaseOrderInvoiceData){
          if ($purchaseOrderInvoiceData['payment']){
            $paymentCount++;
            if (empty($purchaseOrderInvoiceData['invoice_cheque_number'])){
              $chequeNumbersOK='0';
              $thisPurchaseOrderInvoice=$this->PurchaseOrderInvoice->find('first',[
                'fields'=>'invoice_code',
                'conditions'=>['PurchaseOrderInvoice.id'=>$purchaseOrderInvoiceId],
              ]);
              $chequeError.="Se marcó la factura ".(empty($thisPurchaseOrderInvoice)?"":($thisPurchaseOrderInvoice['PurchaseOrderInvoice']['invoice_code']." "))."como cancelada pero no se indicó el número de cheque. ";
            }
          }
        }
      }
      if (!$chequeNumbersOK){
        $chequeError.="Cada entrada y/o factura cancelada tiene que marcar el número de cheque. ";
        $this->Session->setFlash($chequeError, 'default',['class' => 'error-message']);
      }
      elseif ($paymentCount == 0){
        $this->Session->setFlash('No se marcaron facturas para pagar', 'default',['class' => 'error-message']);
      }
      else {
        $datasource=$this->Order->getDataSource();
				$datasource->begin();
        $chequeNumber=$this->request->data['Order']['cheque_number'];
				try {  
          if (!empty($this->request->data['Entry'])){
            foreach ($this->request->data['Entry'] as $entryId => $entryData){
              if ($entryData['payment']){
                $this->Order->id=$entryId;
                $entryArray=[];
                $entryArray['Order']['id']=$entryId;
                $entryArray['Order']['bool_entry_paid']=true;
                $entryArray['Order']['entry_cheque_number']=$entryData['entry_cheque_number'];
                $entryArray['Order']['payment_date']=date('Y-m-d');
                $entryArray['Order']['payment_user_id']=$loggedUserId;
                if (!$this->Order->save($entryArray)) {
                  echo "Problema guardando el pago de la entrada";
                  pr($this->validateErrors($this->Order));
                  throw new Exception();
                }
                $this->recordUserAction($this->Order->id,'payment',null);
                $this->recordUserActivity($this->Session->read('User.username'),"Se canceló la entrada id ".$entryId);  
                $chequeNumber=$entryData['entry_cheque_number'];
              }
            }
          }
          $purchaseOrderInvoiceIds=[];
          if (!empty($this->request->data['PurchaseOrderInvoice'])){
            foreach ($this->request->data['PurchaseOrderInvoice'] as $purchaseOrderInvoiceId => $purchaseOrderInvoiceData){
              if ($purchaseOrderInvoiceData['payment']){
                $this->PurchaseOrderInvoice->id=$purchaseOrderInvoiceId;
                $purchaseOrderInvoiceArray=[
                  'PurchaseOrderInvoice'=>[
                    'id'=>$purchaseOrderInvoiceId,
                    'bool_paid'=>true,
                    'invoice_cheque_number'=>$purchaseOrderInvoiceData['invoice_cheque_number'],
                    'payment_date'=>date('Y-m-d'),
                    'payment_user_id'=>$loggedUserId,
                  ],
                ];
                
                if (!$this->PurchaseOrderInvoice->save($purchaseOrderInvoiceArray)) {
                  echo "Problema guardando el pago de la factura";
                  pr($this->validateErrors($this->PurchaseOrderInvoice));
                  throw new Exception();
                }
                $purchaseOrderInvoiceIds[]=$purchaseOrderInvoiceId;
                $this->recordUserAction($this->PurchaseOrderInvoice->id,'payment',null);
                $this->recordUserActivity($this->Session->read('User.username'),"Se canceló la factura purchase order invoice id ".$purchaseOrderInvoiceId);  
                $chequeNumber=$purchaseOrderInvoiceData['invoice_cheque_number'];
              }
            }
          }    
          if (!empty($purchaseOrderInvoiceIds)){
            $entryIdsForPaidPurchaseOrders=$this->PurchaseOrderInvoice->find('list',[
              'fields'=>'PurchaseOrderInvoice.entry_id',
              'conditions'=>['PurchaseOrderInvoice.id'=>$purchaseOrderInvoiceIds],
            ]);
            $entriesForPaidPurchaseOrders=$this->Order->find('all',[
              'conditions'=>['Order.id'=>$entryIdsForPaidPurchaseOrders],
              'contain'=>[
                'PurchaseOrderInvoice'=>[
                  'conditions'=>[
                    'PurchaseOrderInvoice.id !='=>$purchaseOrderInvoiceIds,
                  ],
                ],
              ],
            ]);  
            
            foreach ($entriesForPaidPurchaseOrders as $entry){
              $boolEntryPaid='0';
              if (empty($entry['PurchaseOrderInvoice'])){
                $boolEntryPaid=true;  
              }
              else {
                $allInvoicesPaid=true;
                foreach ($entry['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
                  $allInvoicesPaid=$allInvoicesPaid && $purchaseOrderInvoice['bool_paid'];
                }
                $boolEntryPaid=$allInvoicesPaid;  
              }
              if ($boolEntryPaid){
                $this->Order->id=$entry['Order']['id'];
                $entryArray=[
                  'Order'=>[
                    'id'=>$entry['Order']['id'],
                    'bool_entry_paid'=>true,
                    'entry_cheque_number'=>$purchaseOrderInvoiceData['invoice_cheque_number'],
                    'payment_date'=>date('Y-m-d'),
                    'payment_user_id'=>$loggedUserId,
                  ],
                ];
                if (!$this->Order->save($entryArray)) {
                  echo "Problema guardando el pago de la entrada a través de factura";
                  pr($this->validateErrors($this->Order));
                  throw new Exception();
                }
                $this->recordUserAction($this->Order->id,'payment',null);
                $this->recordUserActivity($this->Session->read('User.username'),"Se canceló a través de sus facturas la entrada id ".$entry['Order']['id']);  
              }
            }
          }
          
          
					$datasource->commit();
					$this->Session->setFlash(__('Se pagaron las entradas y sus facturas.'),'default',['class' => 'success']);				
					return $this->redirect(['action' => 'verPagoEntradas',$chequeNumber]);
				}
				catch(Exception $e){
					$datasource->rollback();
					// pr($e);
					$this->Session->setFlash(__('No se podían pagar las entradas.'), 'default',['class' => 'error-message']);
				}
      }  
		}
		
    $pendingEntries=$this->Order->find('all',[
			'fields'=>[
				'Order.id','Order.order_code',
				'Order.total_price',
        'Order.entry_cost_total',
				'Order.order_date',
			],
			'conditions'=>[
				'Order.bool_annulled'=>'0',
				'Order.bool_entry_paid'=>'0',
				'Order.third_party_id'=>$providerId,
        'Order.order_date >'=>date('2019-06-01'),
			],
      'contain'=>[
        'PurchaseOrderInvoice'=>[
            'conditions'=>[
              'PurchaseOrderInvoice.bool_paid'=>'0',
            ],
            'PurchaseOrder',
          ],
        'StockMovement'=>[
          'fields'=>['StockMovement.product_quantity'],
          'Product'=>[
            'fields'=>['Product.product_type_id','Product.packaging_unit'],
          ],
        ],
      ],
      'order'=>'Order.order_date ASC',
		]);
    //pr($pendingEntries);
		
		for ($po=0;$po<count($pendingEntries);$po++){
      if (empty($pendingEntries[$po]['PurchaseOrderInvoice'])){
        //$pendingForEntryCS=$totalForEntryCS=$totalForEntry=$pendingEntries[$po]['Order']['total_price'];
        $pendingForEntryCS=$totalForEntryCS=$totalForEntry=$pendingEntries[$po]['Order']['entry_cost_total'];
        //$paidForPurchaseOrderCS=0;
        //$pendingForPurchaseOrderCS=$totalForPurchaseOrderCS-$paidForPurchaseOrderCS;
        
        $pendingEntries[$po]['Order']['pendingCS']=$pendingForEntryCS;
      }
      else {
        $pendingForEntryCS=0;
        foreach ($pendingEntries[$po]['PurchaseOrderInvoice'] as $purchaseOrderInvoice){
          $pendingForEntryCS+=$purchaseOrderInvoice['invoice_total'];          
        }
        $pendingEntries[$po]['Order']['pendingCS']=$pendingForEntryCS;        
      }
		}
		
		$this->set(compact('pendingEntries','provider','exchangeRateCurrent'));
	}
	
	public function guardarFacturasPorPagar($providerName) {
		$exportData=$_SESSION['facturasPorPagar'];
		$this->set(compact('exportData','providerName'));
	}

  public function resumenEntradasPagadas($providerId=0) {
    $this->loadModel('ThirdParty');
    
    $this->loadModel('PurchaseOrderInvoice');
    
    $this->Order->recursive = -1;
    
		$startDate = null;
		$endDate = null;
		
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $providerId=$this->request->data['Report']['provider_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-01-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
	
    $this->set(compact('startDate','endDate'));
    $this->set(compact('providerId'));
  	
		$purchaseConditions=[
			'Order.stock_movement_type_id'=> [MOVEMENT_PURCHASE,MOVEMENT_PURCHASE_CONSUMIBLES],
      'Order.payment_date >='=> $startDate,
			'Order.payment_date <'=> $endDatePlusOne,
      'Order.bool_entry_paid'=> true,
		];
    if ($providerId>0){
      $purchaseConditions['Order.third_party_id']=$providerId;
    }
		
		$purchaseCount=$this->Order->find('count', [
			'conditions' => $purchaseConditions,
		]);
		
		$paidPurchases=[];
		$this->Paginator->settings = [
			'conditions' => $purchaseConditions,
			'contain'=>[
				'ThirdParty'=>[
					'fields'=>['id','company_name'],
				],
        'PaymentUser'=>[
					'fields'=>['id','username'],
				],
			],
      'order'=>'payment_date DESC, order_date DESC,order_code DESC',
			'limit'=>($purchaseCount!=0?$purchaseCount:1)
		];
		$paidPurchases = $this->Paginator->paginate('Order');
    //pr($paidPurchases);
		$this->set(compact('paidPurchases'));
    
    $invoiceConditions=[
			'PurchaseOrderInvoice.payment_date >='=> $startDate,
			'PurchaseOrderInvoice.payment_date <'=> $endDatePlusOne,
      'PurchaseOrderInvoice.bool_paid'=> true,
		];
    if ($providerId > 0){
      $this->loadModel('PurchaseOrder');
      
      $startDateMinusSixMonths= date( "Y-m-d", strtotime( date("Y-m-d")."-6 months" ) );
      //pr($startDateMinusSixMonths);
      $purchaseOrderIdsOfLastHalfYear=$this->PurchaseOrder->find('list',[
        'fields'=>'PurchaseOrder.id',
        'conditions'=>[
          'PurchaseOrder.provider_id'=>$providerId,
          'PurchaseOrder.purchase_order_date >='=>$startDateMinusSixMonths,
          'PurchaseOrder.purchase_order_date <'=>$endDatePlusOne,
        ],
      ]);
      //pr($purchaseOrderIdsOfLastHalfYear);
      $invoiceConditions['PurchaseOrderInvoice.purchase_order_id']=$purchaseOrderIdsOfLastHalfYear;
    }
		
		$invoiceCount=$this->PurchaseOrderInvoice->find('count', [
			'conditions' => $invoiceConditions,
		]);
		
		$paidPurchaseOrderInvoices=$this->PurchaseOrderInvoice->find('all',[
      'conditions' => $invoiceConditions,
			'contain'=>[
        'Entry'=>[
          'fields'=>['id','stock_movement_type_id'],
          'ThirdParty'=>[
            'fields'=>['id','company_name'],
          ],
        ],
        'PaymentUser'=>[
					'fields'=>['id','username'],
				],
        'PurchaseOrder'=>[
					'fields'=>['id','purchase_order_code'],
				],
  
			],
      'order'=>'PurchaseOrderInvoice.payment_date DESC, invoice_date DESC,invoice_code DESC',
			'limit'=>($invoiceCount!=0?$invoiceCount:1)
		]);
		//pr($paidPurchaseOrderInvoices);
		$this->set(compact('paidPurchaseOrderInvoices'));
		
    $providers=$this->ThirdParty->find('list',[
			'fields'=>[
				'ThirdParty.company_name',
			],
			'conditions'=>[
				'bool_provider'=>true,
				'bool_active'=>true,
			],
			'order'=>'ThirdParty.company_name',
		]);
    $this->set(compact('providers'));
    
		/*
		$aco_name="Orders/crearEntradaSuministros";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Orders/editarEntradaSuministros";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Orders/anularEntradaSuministros";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
    */
    
  }

  public function guardarEntradasPagadas($providerName) {
		$exportData=$_SESSION['entradasPagadas'];
		$this->set(compact('exportData','providerName'));
	}	
  
  public function verPagoEntradas($chequeNumber){
    $this->loadModel('PurchaseOrderInvoice');
    
    $entries=$this->Order->find('all',[
      'fields'=>[
        'Order.entry_cheque_number','Order.payment_date',
        'Order.id','Order.order_code','Order.order_date','Order.total_price',
      ],
      'conditions'=>['Order.entry_cheque_number'=>$chequeNumber],
      'contain'=>[
        'PurchaseOrderInvoice',
        'ThirdParty'=>[
          'fields'=>['ThirdParty.id','ThirdParty.company_name',],
        ],  
      ],
      'order'=>['order_date DESC',],
    ]);
    
    $this->set(compact('entries'));
    //pr($entries);
    
    $purchaseOrderInvoices=$this->PurchaseOrderInvoice->find('all',[
      'fields'=>[
        'PurchaseOrderInvoice.invoice_cheque_number','PurchaseOrderInvoice.payment_date',
        'PurchaseOrderInvoice.id','PurchaseOrderInvoice.invoice_code','PurchaseOrderInvoice.invoice_date','PurchaseOrderInvoice.invoice_total',
      ],
      'conditions'=>['PurchaseOrderInvoice.invoice_cheque_number'=>$chequeNumber],
      'contain'=>[
        'Entry'=>[
          'fields'=>['Entry.id','Entry.order_code',],
          'ThirdParty'=>[
            'fields'=>['ThirdParty.id','ThirdParty.company_name',],
          ],  
        ],
        'PurchaseOrder'=>[
          'fields'=>['PurchaseOrder.id','PurchaseOrder.purchase_order_code',],
        ],
      ],
      'order'=>['order_date DESC',],
    ]);
    
    $this->set(compact('purchaseOrderInvoices'));
    //pr($purchaseOrderInvoices);
  }

  public function verPagoEntradasPdf($chequeNumber) {
    $this->loadModel('PurchaseOrderInvoice');
    
		$entries=$this->Order->find('all',[
      'fields'=>[
        'Order.entry_cheque_number','Order.payment_date',
        'Order.id','Order.order_code','Order.order_date','Order.total_price',
      ],
      'conditions'=>['Order.entry_cheque_number'=>$chequeNumber],
      'contain'=>[
        'ThirdParty'=>[
          'fields'=>['ThirdParty.id','ThirdParty.company_name',],
        ],  
      ],
      'order'=>['order_date DESC',],
    ]);
    
    $this->set(compact('entries'));
    
    $purchaseOrderInvoices=$this->PurchaseOrderInvoice->find('all',[
      'fields'=>[
        'PurchaseOrderInvoice.invoice_cheque_number','PurchaseOrderInvoice.payment_date',
        'PurchaseOrderInvoice.id','PurchaseOrderInvoice.invoice_code','PurchaseOrderInvoice.invoice_date','PurchaseOrderInvoice.invoice_total',
      ],
      'conditions'=>['PurchaseOrderInvoice.invoice_cheque_number'=>$chequeNumber],
      'contain'=>[
        'Entry'=>[
          'fields'=>['Entry.id','Entry.order_code',],
          'ThirdParty'=>[
            'fields'=>['ThirdParty.id','ThirdParty.company_name',],
          ],  
        ],
        'PurchaseOrder'=>[
          'fields'=>['PurchaseOrder.id','PurchaseOrder.purchase_order_code',],
        ],
      ],
      'order'=>['order_date DESC',],
    ]);
    
    $this->set(compact('purchaseOrderInvoices'));
    //pr($purchaseOrderInvoices);
	}
	
	public function verReporteCierre($startDate = null,$endDate=null) {
		$bool_bottles='0';
    //echo "role id is ".$this->Auth->User('role_id')."<br/>";
    if ($this->Auth->User('role_id')!=ROLE_SALES){
        $bool_bottles='0';
    }
    else {
      $bool_bottles=true;
    }
		if ($this->request->is('post')) {
			//pr($this->request->data);
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$bool_bottles=$this->request->data['Report']['report_type'];
		}
		else{
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->loadModel('StockMovement');
		$this->loadModel('ThirdParty');
		$clients=$this->ThirdParty->find('all',[
      'fields'=>['id','company_name'],
      'conditions'=>[
        'ThirdParty.bool_provider'=>'0',
        'ThirdParty.bool_active'=>true,
      ],
    ]);
		
		// get the relevant time period
		$startDateDay=date("d",strtotime($startDate));
		$startDateMonth=date("m",strtotime($startDate));
		$startDateYear=date("Y",strtotime($startDate));
		$endDateDay=date("d",strtotime($endDate));
		$endDateMonth=date("m",strtotime($endDate));
		$endDateYear=date("Y",strtotime($endDate));
		
		//echo $startDateDay."<br/>";
		//echo $startDateMonth."<br/>";
		//echo $startDateYear."<br/>";
		//echo $endDateDay."<br/>";
		//echo $endDateMonth."<br/>";
		//echo $endDateYear."<br/>";
		$monthArray=[];
		$counter=0;
		for ($yearCounter=$startDateYear;$yearCounter<=$endDateYear;$yearCounter++){
			if ($yearCounter==$startDateYear && $yearCounter==$endDateYear){
				// only 1 year in consideration
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=$endDateMonth;
			}
			else if($yearCounter==$startDateYear){
				// starting year (not the same as ending year)
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=12;
			}
			else if ($yearCounter==$endDateYear){
				// ending year (not the same as starting year)
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=$endDateMonth;
			}
			else {
				// in between year
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=12;
			}
			for ($monthCounter=$startingMonth;$monthCounter<=$endingMonth;$monthCounter++){
				$monthArray[$counter]['period']=$monthCounter.'_'.$yearCounter;
				if ($monthCounter==$startDateMonth && $yearCounter == $startDateYear){
					$monthArray[$counter]['start']=$startingDay;
				}
				else {
					$monthArray[$counter]['start']=1;
				}
				$monthArray[$counter]['month']=$monthCounter;
				$monthArray[$counter]['year']=$yearCounter;
				$counter++;
			}
		}
		
		$salesArray=[];
		$clientCounter=0;
		$totalSale=0;
		if (!$bool_bottles){
			for ($clientCounter=0;$clientCounter<count($clients);$clientCounter++){
				$salesCounter=0;
				$totalForClient=0;
				$salesArray[$clientCounter]['clientid']=$clients[$clientCounter]['ThirdParty']['id'];
				$salesArray[$clientCounter]['clientname']=$clients[$clientCounter]['ThirdParty']['company_name'];
				for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
					$period=$monthArray[$salePeriod]['period'];
					$start=$monthArray[$salePeriod]['start'];
					$month=$monthArray[$salePeriod]['month'];
					$nextmonth=($month==12)?1:($month+1);
					$year=$monthArray[$salePeriod]['year'];
					$nextyear=($month==12)?($year+1):$year;
					$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
					$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));
					$saleForMonthForClient=$this->Order->find('first',
						array(
							'fields'=>array('SUM(total_price) as totalSale'),
							'conditions'=>array(
								'stock_movement_type_id'=>MOVEMENT_SALE,
								'order_date >='=> $saleStartDate,
								'order_date <'=> $saleEndDate,
								'third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							)
						)
					);
					
					$salesArray[$clientCounter]['sales'][$salesCounter]['period']=$period;
					
					$salesArray[$clientCounter]['sales'][$salesCounter]['total']=$saleForMonthForClient[0]['totalSale'];
					if (!empty($saleForMonthForClient)){
						$totalForClient+=$saleForMonthForClient[0]['totalSale'];
						$totalSale+=$saleForMonthForClient[0]['totalSale'];
					}
					$salesCounter++;
				}
				$salesArray[$clientCounter]['totalForClient']=$totalForClient;
			}
		}		
		//echo "totalSale is ".$totalSale."<br/>";
		usort($salesArray,array($this,'sortByTotalForClient'));
		//pr($salesArray);
		
		$bottlesAArray=[];
		$totalABottles=0;
		$bottlesBCArray=[];
		$totalBCBottles=0;
		if ($bool_bottles){
			$clientCounter=0;
			for ($clientCounter=0;$clientCounter<count($clients);$clientCounter++){
				$bottlesCounter=0;
				$totalForClient=0;
				$bottlesAArray[$clientCounter]['clientid']=$clients[$clientCounter]['ThirdParty']['id'];
				$bottlesAArray[$clientCounter]['clientname']=$clients[$clientCounter]['ThirdParty']['company_name'];
				for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
					$period=$monthArray[$salePeriod]['period'];
					$start=$monthArray[$salePeriod]['start'];
					$month=$monthArray[$salePeriod]['month'];
					$nextmonth=($month==12)?1:($month+1);
					$year=$monthArray[$salePeriod]['year'];
					$nextyear=($month==12)?($year+1):$year;
					$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
					$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));

					$bottlesForMonthForClient=$this->StockMovement->find('first',
						array(
							'fields'=>array('SUM(product_quantity) as totalBottles'),
							'conditions'=>array(
								'StockMovement.production_result_code_id'=>1,
								'StockMovement.bool_reclassification'=>'0',
								'StockMovement.product_quantity >'=>0,
								'Order.stock_movement_type_id'=>MOVEMENT_SALE,
								'Order.order_date >='=> $saleStartDate,
								'Order.order_date <'=> $saleEndDate,
								'Order.third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							)
						)
					);
					
					// if ($clients[$clientCounter]['ThirdParty']['id']==19){
						// $bottlesForProcasa=$this->StockMovement->find('all',array(
							// 'fields'=>array(
								// 'StockMovement.product_quantity',
							// ),
							// 'conditions'=>array(
								// 'StockMovement.production_result_code_id'=>1,
								// 'StockMovement.bool_reclassification'=>'0',
								// 'StockMovement.product_quantity >'=>0,
								// 'Order.stock_movement_type_id'=>MOVEMENT_SALE,
								// 'Order.order_date >='=> $saleStartDate,
								// 'Order.order_date <'=> $saleEndDate,
								// 'Order.third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							// ),
							// 'contain'=>array(
								// 'Order'=>array(
									// 'fields'=>array(
										// 'Order.order_code','Order.order_date',
									// ),
								// ),
							// ),
						// ));
						// //pr($bottlesForProcasa);
					// }
					$bottlesAArray[$clientCounter]['bottles'][$bottlesCounter]['period']=$period;					
					$bottlesAArray[$clientCounter]['bottles'][$bottlesCounter]['totalBottles']=$bottlesForMonthForClient[0]['totalBottles'];
					if (!empty($bottlesForMonthForClient)){
						$totalForClient+=$bottlesForMonthForClient[0]['totalBottles'];
						$totalABottles+=$bottlesForMonthForClient[0]['totalBottles'];
					}
					$bottlesCounter++;
				}
				$bottlesAArray[$clientCounter]['totalForClient']=$totalForClient;
			}
			$clientCounter=0;
			for ($clientCounter=0;$clientCounter<count($clients);$clientCounter++){
				$bottlesCounter=0;
				$totalForClient=0;
				$bottlesBCArray[$clientCounter]['clientid']=$clients[$clientCounter]['ThirdParty']['id'];
				$bottlesBCArray[$clientCounter]['clientname']=$clients[$clientCounter]['ThirdParty']['company_name'];
				for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
					$period=$monthArray[$salePeriod]['period'];
					$start=$monthArray[$salePeriod]['start'];
					$month=$monthArray[$salePeriod]['month'];
					$nextmonth=($month==12)?1:($month+1);
					$year=$monthArray[$salePeriod]['year'];
					$nextyear=($month==12)?($year+1):$year;
					$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
					$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));

					$bottlesForMonthForClient=$this->StockMovement->find('first',
						array(
							'fields'=>array('SUM(product_quantity) as totalBottles'),
							'conditions'=>array(
								'StockMovement.production_result_code_id >'=>1,
								'StockMovement.bool_reclassification'=>'0',
								'Order.stock_movement_type_id'=>MOVEMENT_SALE,
								'Order.order_date >='=> $saleStartDate,
								'Order.order_date <'=> $saleEndDate,
								'Order.third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							)
						)
					);
					
					$bottlesBCArray[$clientCounter]['bottles'][$bottlesCounter]['period']=$period;					
					$bottlesBCArray[$clientCounter]['bottles'][$bottlesCounter]['totalBottles']=$bottlesForMonthForClient[0]['totalBottles'];
					if (!empty($bottlesForMonthForClient)){
						$totalForClient+=$bottlesForMonthForClient[0]['totalBottles'];
						$totalBCBottles+=$bottlesForMonthForClient[0]['totalBottles'];
					}
					$bottlesCounter++;
				}
				$bottlesBCArray[$clientCounter]['totalForClient']=$totalForClient;
			}
		}
		
		usort($bottlesAArray,array($this,'sortByTotalForClient'));
		usort($bottlesBCArray,array($this,'sortByTotalForClient'));
		
		$this->set(compact('clients','monthArray','salesArray','bottlesAArray','bottlesBCArray','startDate','endDate','totalSale','totalABottles','totalBCBottles','bool_bottles'));
	}
	
	public function sortByTotalForClient($a,$b ){ 
	  if( $a['totalForClient'] == $b['totalForClient'] ){ return 0 ; } 
	  return ($a['totalForClient'] < $b['totalForClient']) ? 1 : -1;
	} 
	
	public function guardarReporteCierre() {
		$exportData=$_SESSION['reporteCierre'];
		$this->set(compact('exportData'));
	}
	
	public function verVentasPorCliente($id=0){
		$this->loadModel('StockItem');
		$startDate = null;
		$endDate = null;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
		}
		else {
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		// get the relevant time period
		$startDateDay=date("d",strtotime($startDate));
		$startDateMonth=date("m",strtotime($startDate));
		$startDateYear=date("Y",strtotime($startDate));
		$endDateDay=date("d",strtotime($endDate));
		$endDateMonth=date("m",strtotime($endDate));
		$endDateYear=date("Y",strtotime($endDate));
		
		$monthArray=[];
		$counter=0;
		for ($yearCounter=$startDateYear;$yearCounter<=$endDateYear;$yearCounter++){
			if ($yearCounter==$startDateYear && $yearCounter==$endDateYear){
				// only 1 year in consideration
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=$endDateMonth;
			}
			else if($yearCounter==$startDateYear){
				// starting year (not the same as ending year)
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=12;
			}
			else if ($yearCounter==$endDateYear){
				// ending year (not the same as starting year)
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=$endDateMonth;
			}
			else {
				// in between year
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=12;
			}
			for ($monthCounter=$startingMonth;$monthCounter<=$endingMonth;$monthCounter++){
				$monthArray[$counter]['period']=$monthCounter.'_'.$yearCounter;
				if ($monthCounter==$startDateMonth && $yearCounter == $startDateYear){
					$monthArray[$counter]['start']=$startingDay;
				}
				else {
					$monthArray[$counter]['start']=1;
				}
				$monthArray[$counter]['month']=$monthCounter;
				$monthArray[$counter]['year']=$yearCounter;
				$counter++;
			}
		}
		//pr($monthArray);
		$this->loadModel('ThirdParty');
		$client=$this->ThirdParty->find('first',array('conditions'=>array('ThirdParty.id'=>$id)));
		
		$salesArray=[];
		$salesCounter=0;
		$totalQuantityProduced=0;
		$totalQuantityOther=0;
		$totalSale=0;
		$totalCost=0;
		$totalProfit=0;
		
		for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
			$period=$monthArray[$salePeriod]['period'];
			$start=$monthArray[$salePeriod]['start'];
			$month=$monthArray[$salePeriod]['month'];
			//echo "saleperiod is ".$salePeriod."<br/>";
			//echo "month is ".$month."<br/>";
			$nextmonth=($month==12)?1:($month+1);
			$year=$monthArray[$salePeriod]['year'];
			$nextyear=($month==12)?($year+1):$year;
			$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
			$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));
			$salesForMonthForClient=$this->Order->find('all',
				array(
					'fields'=>array('total_price','order_date','order_code'),
					'conditions'=>array(
						'stock_movement_type_id'=>MOVEMENT_SALE,
						'order_date >='=> $saleStartDate,
						'order_date <'=> $saleEndDate,
						'third_party_id'=>$client['ThirdParty']['id']
					),
					'contain'=>array(
						'ThirdParty'=>array('fields'=>'company_name'),
						'StockMovement'=>array(
							'fields'=>array('id','movement_date','order_id','stockitem_id','product_quantity','product_unit_price','product_total_price'),
							'conditions'=>array('StockMovement.product_quantity >'=>0),
							'Product'=>array(
								'fields'=>array('id','packaging_unit'),	
								'ProductType'=>array('fields'=>'product_category_id'),
								
							)
						)
					),
					'order'=>'Order.order_date ASC, Order.id ASC'
				)
			);
			//pr($salesForMonthForClient);
			
			$totalQuantityProducedMonth=0;
			$totalQuantityOtherMonth=0;
			$totalSaleMonth=0;
			$totalCostMonth=0;
			$totalProfitMonth=0;
			
			$processedSales=[];
			for ($s=0;$s<count($salesForMonthForClient);$s++){
				//pr($salesForMonthForClient[$s]);
				$processedSales[$s]['order_date']=$salesForMonthForClient[$s]['Order']['order_date'];
				$processedSales[$s]['order_id']=$salesForMonthForClient[$s]['Order']['id'];
				$processedSales[$s]['order_code']=$salesForMonthForClient[$s]['Order']['order_code'];
				$amountBottles=0;
				$amountCaps=0;
				$productTotalPrice=0;
				$productTotalCost=0;
				$productTotalUtility=0;
				
				foreach ($salesForMonthForClient[$s]['StockMovement'] as $stockMovement){
					if ($stockMovement['product_quantity']>0){
						if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED){
							$amountBottles+=$stockMovement['product_quantity'];
						}
						else {
							$amountCaps+=$stockMovement['product_quantity'];
						}
						$productTotalPrice+=$stockMovement['product_total_price'];
						$this->StockItem->recursive=-1;
						$stockItem=$this->StockItem->find('first',array(
							'conditions'=>array(
								'StockItem.id'=>$stockMovement['stockitem_id']
							),
						));
						$productTotalCost+=$stockMovement['product_quantity']*$stockItem['StockItem']['product_unit_price'];
						$productTotalUtility+=($stockMovement['product_total_price']-$stockMovement['product_quantity']*$stockItem['StockItem']['product_unit_price']);
					}
				}
				
				$processedSales[$s]['amount_bottles']=$amountBottles;
				$processedSales[$s]['amount_caps']=$amountCaps;
				$processedSales[$s]['product_total_price']=$productTotalPrice;
				$processedSales[$s]['product_total_cost']=$productTotalCost;
				$processedSales[$s]['product_total_utility']=$productTotalUtility;
			}
			
			foreach ($salesForMonthForClient as $saleForMonth){
				$totalSaleMonth+=$saleForMonth['Order']['total_price'];
				foreach ($saleForMonth['StockMovement'] as $stockMovement){
					//pr($stockMovement);
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED){
						$totalQuantityProducedMonth+=$stockMovement['product_quantity'];
					}
					else {
						$totalQuantityOtherMonth+=$stockMovement['product_quantity'];
					}
					$relatedStockitem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$stockMovement['stockitem_id'])));
					$unitcost=$relatedStockitem['StockItem']['product_unit_price'];
					$totalCostMonth+=$stockMovement['product_quantity']*$unitcost;
				}
			}
			$totalProfitMonth=$totalSaleMonth-$totalCostMonth;
			
			$totalQuantityProduced+=$totalQuantityProducedMonth;
			$totalQuantityOther+=$totalQuantityOtherMonth;
			$totalSale+=$totalSaleMonth;
			$totalCost+=$totalCostMonth;
			$totalProfit+=$totalProfitMonth;
			
			//$salesArray[$salesCounter]['clientid']=$client['ThirdParty']['id'];
			$salesArray[$salesCounter]['period']=$period;
			
			$salesArray[$salesCounter]['sales']=$processedSales;
			
			$salesArray[$salesCounter]['totalSaleMonth']=$totalSaleMonth;
			$salesArray[$salesCounter]['totalQuantityProducedMonth']=$totalQuantityProducedMonth;
			$salesArray[$salesCounter]['totalQuantityOtherMonth']=$totalQuantityOtherMonth;
			$salesArray[$salesCounter]['totalCostMonth']=$totalCostMonth;
			$salesArray[$salesCounter]['totalProfitMonth']=$totalProfitMonth;
			
			$salesCounter++;
		}
		//echo "totalSale is ".$totalSale."<br/>";
		
		$totals=[];
		$totals['totalQuantityProduced']=$totalQuantityProduced;
		$totals['totalQuantityOther']=$totalQuantityOther;
		$totals['totalSale']=$totalSale;
		$totals['totalCost']=$totalCost;
		$totals['totalProfit']=$totalProfitMonth;
		
		$this->set(compact('client','monthArray','salesArray','startDate','endDate','totals'));
	}
	
	public function guardarReporteVentasCliente($clientname){
		$exportData=$_SESSION['reporteVentasPorCliente'];
		$this->set(compact('exportData','clientname'));
	}
	
	public function create_pdf($name=null){ 
		$outputcompra = $_SESSION['output_compra'];
		if (empty($name)){
			$name="compra.pdf";
		}
		$this->set(compact('outputcompra','name'));
		$this->layout = '/pdf/default';
		$this->render('/pdf/pdf_compra');
		//$this->redirect(array('action' => 'downloadPdf'),$name);
		//exit();
	}
	
	public function downloadPdf($name=null) { 
		$outputcompra = $_SESSION['output_compra'];
		
		App::import('Vendor','xtcpdf');
		$pdf = new XTCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
		//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->AddPage();
		$html = $outputcompra;
		$pdf->writeHTML($html, true, false, true, false, '');
		$pdf->lastPage();
		//echo $pdf->Output(APP.'files/pdf'. DS . $name, 'F');
		echo $pdf->Output('E:/' . DS . $name, 'F');
		//echo "pdf 'E:/".DS.$name." generated";
	
		$this->viewClass = 'Media';
		$filename=substr($name,0,strpos($name,"."));
		$params = array(
			'id' => $name,
			'name' => $filename ,
			'download' => true,
			'extension' => 'pdf',
			'path' => 'E:/'
		);
		$this->set($params);
	}

  public function facturasPorVendedor() {
    $this->loadModel('ProductType');
    
    $this->loadModel('ClientType');
    $this->loadModel('Zone');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $canSeeUtilityTables=$this->UserPageRight->hasUserPageRight('VER_RESUMEN_EJECUTIVO',$userRoleId,$loggedUserId,'Orders','facturasPorVendedor');
    $this->set(compact('canSeeUtilityTables'));
    
    $canSeeInventoryCost=$this->UserPageRight->hasUserPageRight('VER_COSTO_INVENTARIO',$userRoleId,$loggedUserId,'All','All');
    $this->set(compact('canSeeInventoryCost'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'Orders','facturasPorVendedor');
    $this->set(compact('canSeeAllUsers'));
    
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'Orders','facturasPorVendedor');
    $this->set(compact('canSeeAllVendors'));
    
    $aco_name="Orders/verVenta";		
		$bool_sale_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		
		 
		$this->set(compact('bool_sale_view_permission'));
    
    $startDate = null;
		$endDate = null;
    
    define('INVOICES_ALL','0');
    define('INVOICES_CASH','1');
    define('INVOICES_CREDIT','2');
    
    $paymentOptions=[
      INVOICES_ALL=>'Todas Facturas',
      INVOICES_CASH=>'Solo Facturas de Contado',
      INVOICES_CREDIT=>'Solo Facturas de Crédito',
    ];
    $this->set(compact('paymentOptions'));
    
    define('SALE_TYPE_ALL','0');
    define('SALE_TYPE_BOTTLE','1');
    define('SALE_TYPE_CAP','2');
    define('SALE_TYPE_SERVICE','3');
    //define('SALE_TYPE_CONSUMIBLE','4');
    define('SALE_TYPE_IMPORT','5');
    //define('SALE_TYPE_LOCAL','6');
    define('SALE_TYPE_INJECTION','7');
    
    $saleTypeOptions=[
      SALE_TYPE_ALL=>'-- Todos Productos --',
      SALE_TYPE_BOTTLE=>'Botellas',
      SALE_TYPE_CAP=>'Tapones',
      SALE_TYPE_SERVICE=>'Servicios',
      //SALE_TYPE_CONSUMIBLE=>'Consumibles',
      SALE_TYPE_IMPORT=>'Importados',
      //SALE_TYPE_LOCAL=>'Locales',
      SALE_TYPE_INJECTION=>'ProductosIngroup',
    ];
    $this->set(compact('saleTypeOptions'));
    $paymentOptionId=0;
    $saleTypeOptionId=0;
    
    $warehouseId=0;
    $vendorUserId=$loggedUserId;
    $clientTypeId=0;
    $zoneId=0;
    
    if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers || $canSeeAllVendors){
      $vendorUserId=0;
    }
    else {
      $vendorUserId=$loggedUserId;
    }
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
      //pr($startDateArray);
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $warehouseId=$this->request->data['Report']['warehouse_id'];
      $paymentOptionId=$this->request->data['Report']['payment_option_id'];
      
      $clientTypeId=$this->request->data['Report']['client_type_id'];
      $zoneId=$this->request->data['Report']['zone_id'];
      
      $saleTypeOptionId=$this->request->data['Report']['sale_type_option_id'];
      $vendorUserId=$this->request->data['Report']['vendor_user_id'];
      //pr($this->request->data);
      
      if (!empty($this->request->data['onlyBottle'])){
        $saleTypeOptionId=SALE_TYPE_BOTTLE;
      }
      if (!empty($this->request->data['onlyCap'])){
        //echo "only caps are shown<br/>";
        $saleTypeOptionId=SALE_TYPE_CAP;
      }
      if (!empty($this->request->data['onlyService'])){
        $saleTypeOptionId=SALE_TYPE_SERVICE;
      }
      //if (!empty($this->request->data['onlyConsumible'])){
      //  $saleTypeOptionId=SALE_TYPE_CONSUMIBLE;
      //}
      if (!empty($this->request->data['onlyImport'])){
        $saleTypeOptionId=SALE_TYPE_IMPORT;
      }
      if (!empty($this->request->data['onlyInjection'])){
        $saleTypeOptionId=SALE_TYPE_INJECTION;
      }
      if (!empty($this->request->data['onlyLocal'])){
        $saleTypeOptionId=SALE_TYPE_LOCAL;
      }
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('startDate','endDate'));
		
    $this->set(compact('paymentOptionId'));
    $this->set(compact('clientTypeId'));
    $this->set(compact('zoneId'));
    $this->set(compact('saleTypeOptionId'));
    $this->set(compact('vendorUserId'));
	
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
    
    //$users=$this->User->getActiveVendorUserList($warehouseId);
    //$this->set(compact('users'));
    
    if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      //echo 'can see all users<br/>';
      $users=$this->User->getActiveVendorAdminUserList($warehouseId);
      $selectedUsers=($vendorUserId == 0)?$users:[$vendorUserId=>$users[$vendorUserId]];
    }
    elseif ($canSeeAllVendors) {
      //echo 'can see all vendors<br/>';
      $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
      $selectedUsers=($vendorUserId == 0)?$users:[$vendorUserId=>$users[$vendorUserId]];
    }
    else {
      //echo 'can only see oneself<br/>';
      $users=$this->User->getActiveUserList($loggedUserId);
      $selectedUsers=[$vendorUserId=>$users[$vendorUserId]];
    }
    $this->set(compact('users'));
    //pr($users);
    $this->set(compact('selectedUsers'));
    //pr($selectedUsers);
    
    $allUserSales=[];
    
    $allProductTypes=$this->ProductType->find('list');
    $this->set(compact('allProductTypes'));
  
		
    if ($clientTypeId > 0){
      $orderConditions['Order.client_type_id']=$clientTypeId;
    }
    if ($zoneId > 0){
      $orderConditions['Order.zone_id']=$zoneId;
    }
    
    $emptyTypeCounter=[
      'produced'=>0,
      'cap'=>0,
      'service'=>0,
      'import'=>0,
      'local'=>0,
      'other'=>[],
      'consumible'=>0,
    ];
    
    $totalQuantities=$emptyTypeCounter;
    
    foreach ($selectedUsers as $selectedUserId=>$selectedUserName){
      $salesForPeriod=$this->Order->getFullSales($warehouseId,$startDate,$endDate,$selectedUserId,[
        'client_type_id'=>$clientTypeId,
        'zone_id'=>$zoneId,
        'bool_include_salesorder'=>true,
      ]);
      //pr($salesForPeriod);
      
      $rowCounter=0;
      $userQuantities=$emptyTypeCounter;
      
      $sales=[];
      foreach ($salesForPeriod as $sale){
        $quantities=$prices=$costs=$emptyTypeCounter;
        $totalCost=0;
        
        $salesOtherProductTypes=[];
        foreach ($sale['StockMovement'] as $stockMovement){
          //pr ($stockMovement);
          $qualifiedStockMovement='0';
          if ($stockMovement['Product']['ProductType']['product_category_id'] == CATEGORY_PRODUCED  && !in_array($stockMovement['production_result_code_id'],[PRODUCTION_RESULT_CODE_B,PRODUCTION_RESULT_CODE_C])){
            $quantities['produced']+=$stockMovement['product_quantity'];
            $prices['produced']+=$stockMovement['product_total_price'];
            $costs['produced']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            $qualifiedStockMovement=true;
          }
          elseif ($stockMovement['Product']['ProductType']['product_category_id'] == CATEGORY_OTHER) {
            $qualifiedStockMovement=true;
            switch ($stockMovement['Product']['product_type_id']){
              case PRODUCT_TYPE_CAP:
                $quantities['cap']+=$stockMovement['product_quantity'];
                $prices['cap']+=$stockMovement['product_total_price'];
                $costs['cap']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                break;
              case PRODUCT_TYPE_IMPORT:
                $quantities['import']+=$stockMovement['product_quantity'];
                $prices['import']+=$stockMovement['product_total_price'];
                $costs['import']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                break;
              case PRODUCT_TYPE_INJECTION_OUTPUT:
                $quantities['injection']+=$stockMovement['product_quantity'];
                $prices['injection']+=$stockMovement['product_total_price'];
                $costs['injection']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                break;  
              // case PRODUCT_TYPE_LOCAL:
                // $quantities['local']+=$stockMovement['product_quantity'];
                // $prices['local']+=$stockMovement['product_total_price'];
                // $costs['local']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                // break;
              case PRODUCT_TYPE_SERVICE:
                $quantities['service']+=$stockMovement['product_quantity'];
                $prices['service']+=$stockMovement['product_total_price'];
                // notice that service cost is determined by service_unit_cost
                $costs['service']+=$stockMovement['product_quantity']*$stockMovement['service_unit_cost'];
                break;
              default:
                $productTypeId=$stockMovement['Product']['product_type_id'];
                if (!array_key_exists($productTypeId,$totalQuantities['other'])){
                  $totalQuantities['other'][$productTypeId]=0;
                }
                if (!array_key_exists($productTypeId,$userQuantities['other'])){
                  $userQuantities['other'][$productTypeId]=0;
                }
                if (!array_key_exists($productTypeId,$quantityOthers)){
                  $quantities['other'][$productTypeId]=0;
                  $prices['other'][$productTypeId]=0;
                  $costs['other'][$productTypeId]=0;
                }
                $quantities['other'][$productTypeId]+=$stockMovement['product_quantity'];
                $prices['other'][$productTypeId]+=$stockMovement['product_total_price'];
                $costs['other'][$productTypeId]+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];                  
            }
          }
          elseif ($stockMovement['Product']['ProductType']['product_category_id'] == CATEGORY_CONSUMIBLE) {
            $quantities['consumible'][$productTypeId]+=$stockMovement['product_quantity'];
            $prices['consumible'][$productTypeId]+=$stockMovement['product_total_price'];
            $costs['consumible'][$productTypeId]+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];   
        
            $qualifiedStockMovement=true;
          }
          if ($qualifiedStockMovement){
            if ($stockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
              $totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            }
            else {
              $totalCost+=$stockMovement['product_quantity']*$stockMovement['service_unit_cost'];
            }
          }				
        }
        
        $summedPriceOthers=0;
        if (!empty($prices['other'])){
          foreach ($prices['other'] as $priceOtherProductTypeId => $priceOther){
            $summedPriceOthers+=$priceOther;
          }
        }
        $summedQuantityOthers=0;
        //pr($quantityOthers);
        if (!empty($quantities['other'])){
          foreach ($quantities['other'] as $productTypeId=>$productTypeQuantity){
            $summedQuantityOthers+=$productTypeQuantity;
          }
        }
        $saleTotalCost=0;
        //pr($costs);
        foreach ($costs as $costType=>$costValue){
          if ($costType!= 'other' && $costType!= 'consumible'){
            $saleTotalCost +=  $costValue;
          }
        }
        if (
          ($quantities['produced']+$quantities['cap']+$quantities['service']+$quantities['import']+$quantities['local']+$summedQuantityOthers)>0
          ||
          (
            !empty($sale['Invoice'])
            &&
            ($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement']))
          )
          ||
          (
            empty($sale['Invoice'])
            &&
            empty($sale['CashReceipt'])
            &&
            empty($sale['StockMovement'])
          )
        ){
          $sales[$rowCounter]=[
            'Order'=>[
              'id'=>$sale['Order']['id'],
              'order_date'=>$sale['Order']['order_date'],
              'order_code'=>$sale['Order']['order_code'],
              'total_price'=>$sale['Order']['total_price'],
              'total_cost'=>$saleTotalCost,
              'prices'=>$prices,
              'costs'=>$costs,
              'quantities'=>$quantities,
            ],
            'ThirdParty'=>[
              'id'=>$sale['ThirdParty']['id'],
              'company_name'=>($sale['ThirdParty']['bool_generic']?$sale['Order']['client_name']:$sale['ThirdParty']['company_name']),
              'bool_generic'=>$sale['ThirdParty']['bool_generic'],
            ],
          ];
          if (!empty($sale['Invoice'])){
            //pr($sale);
            $sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
            if($sale['Invoice'][0]['bool_annulled']){
              $sales[$rowCounter]['Invoice']['bool_annulled']=true;
            }
            else {
              $sales[$rowCounter]['Invoice']['bool_annulled']='0';
            }
          }
          else {
            // this implies a remission I think
            $sales[$rowCounter]['Invoice']['bool_annulled']='0';
            $sales[$rowCounter]['Invoice']['bool_credit']=true;
           }
          /*  
            if (!empty($priceOthers)){
              foreach ($priceOthers as $priceOtherProductTypeId => $priceOther){
                $sales[$rowCounter]['Order']['price_others'][$priceOtherProductTypeId]=$priceOther;  
              }
            }
            else {
              $sales[$rowCounter]['Order']['price_others']=[];
            }
            $sales[$rowCounter]['Order']['total_cost']=$totalCost;
            if (!empty($costOthers)){
              foreach ($costOthers as $costOtherProductTypeId => $costOther){
                $sales[$rowCounter]['Order']['cost_others'][$costOtherProductTypeId]=$costOther;  
              }
            }
            else {
              $sales[$rowCounter]['Order']['cost_others']=[];
            }
            if (!empty($quantityOthers)){
              foreach ($quantityOthers as $quantityOtherProductTypeId => $quantityOther){
                $sales[$rowCounter]['Order']['quantity_others'][$quantityOtherProductTypeId]=$quantityOther;  
              }
            }
            else {
              $sales[$rowCounter]['Order']['quantity_others']=[];
            }
            $sales[$rowCounter]['Order']['total_quantity_cap']=$totalQuantityCap;
            $sales[$rowCounter]['Order']['total_quantity_produced']=$totalQuantityProduced;
            $sales[$rowCounter]['Order']['total_quantity_service']=$totalQuantityService;
            $sales[$rowCounter]['Order']['total_quantity_import']=$totalQuantityImport;
            $sales[$rowCounter]['Order']['total_quantity_local']=$totalQuantityLocal;
            if (!empty($totalQuantityOthers)){
              foreach ($totalQuantityOthers as $productTypeId => $totalQuantityOther){
                $sales[$rowCounter]['Order']['total_quantity_others'][$productTypeId]=$totalQuantityOther;  
                if (!array_key_exists($productTypeId,$salesOtherProductTypes)){
                  $salesOtherProductTypes[$productTypeId]=$allProductTypes[$productTypeId];
                }
              }
            }
            else {
              $sales[$rowCounter]['Order']['total_quantity_others']=[];
            }
          */  
                    
          $rowCounter++;
        }
      }
      if (!empty($salesOtherProductTypes)){
        asort($salesOtherProductTypes);
      }
      $this->set(compact('salesOtherProductTypes'));
      $allUserSales[$selectedUserId]['Sales']=$sales;
    }
    $this->set(compact('allUserSales'));
    //pr($allUserSales);    
    
    $unconditionalUserSales=[];
    foreach ($users as $currentUserId=>$currentUserName){
      $salesForUserForPeriod=$this->Order->getFullSales($warehouseId,$startDate,$endDate,$currentUserId,[
        'bool_include_salesorder'=>true,
      ]);
      //pr($salesForUserForPeriod);
      
      $rowCounter=0;
      $userQuantities=$emptyTypeCounter;
      
      $unconditionalSales=[];

      foreach ($salesForUserForPeriod as $sale){
        $quantities=$prices=$costs=$emptyTypeCounter;
        $totalCost=0;
        
        $salesOtherProductTypes=[];
        foreach ($sale['StockMovement'] as $stockMovement){
          //pr ($stockMovement);
          $qualifiedStockMovement='0';
          if ($stockMovement['Product']['ProductType']['product_category_id'] == CATEGORY_PRODUCED  && !in_array($stockMovement['production_result_code_id'],[PRODUCTION_RESULT_CODE_B,PRODUCTION_RESULT_CODE_C])){
            $quantities['produced']+=$stockMovement['product_quantity'];
            $prices['produced']+=$stockMovement['product_total_price'];
            $costs['produced']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            $qualifiedStockMovement=true;
          }
          elseif ($stockMovement['Product']['ProductType']['product_category_id'] == CATEGORY_OTHER) {
            $qualifiedStockMovement=true;
            switch ($stockMovement['Product']['product_type_id']){
              case PRODUCT_TYPE_CAP:
                $quantities['cap']+=$stockMovement['product_quantity'];
                $prices['cap']+=$stockMovement['product_total_price'];
                $costs['cap']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                break;
              case PRODUCT_TYPE_IMPORT:
                $quantities['import']+=$stockMovement['product_quantity'];
                $prices['import']+=$stockMovement['product_total_price'];
                $costs['import']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                break;
              case PRODUCT_TYPE_INJECTION_OUTPUT:
                $quantities['injection']+=$stockMovement['product_quantity'];
                $prices['injection']+=$stockMovement['product_total_price'];
                $costs['injection']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                break;
              case PRODUCT_TYPE_LOCAL:
                $quantities['local']+=$stockMovement['product_quantity'];
                $prices['local']+=$stockMovement['product_total_price'];
                $costs['local']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                break;
              case PRODUCT_TYPE_SERVICE:
                $quantities['service']+=$stockMovement['product_quantity'];
                $prices['service']+=$stockMovement['product_total_price'];
                // notice that service cost is determined by service_unit_cost
                $costs['service']+=$stockMovement['product_quantity']*$stockMovement['service_unit_cost'];
                break;
              default:
                $productTypeId=$stockMovement['Product']['product_type_id'];
                if (!array_key_exists($productTypeId,$totalQuantities['other'])){
                  $totalQuantities['other'][$productTypeId]=0;
                }
                if (!array_key_exists($productTypeId,$userQuantities['other'])){
                  $userQuantities['other'][$productTypeId]=0;
                }
                if (!array_key_exists($productTypeId,$quantityOthers)){
                  $quantities['other'][$productTypeId]=0;
                  $prices['other'][$productTypeId]=0;
                  $costs['other'][$productTypeId]=0;
                }
                $quantities['other'][$productTypeId]+=$stockMovement['product_quantity'];
                $prices['other'][$productTypeId]+=$stockMovement['product_total_price'];
                $costs['other'][$productTypeId]+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];                  
            }
          }
          elseif ($stockMovement['Product']['ProductType']['product_category_id'] == CATEGORY_CONSUMIBLE) {
            $quantities['consumible'][$productTypeId]+=$stockMovement['product_quantity'];
            $prices['consumible'][$productTypeId]+=$stockMovement['product_total_price'];
            $costs['consumible'][$productTypeId]+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];   
        
            $qualifiedStockMovement=true;
          }
          if ($qualifiedStockMovement){
            if ($stockMovement['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
              $totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            }
            else {
              $totalCost+=$stockMovement['product_quantity']*$stockMovement['service_unit_cost'];
            }
          }				
        }
        
        $summedPriceOthers=0;
        if (!empty($prices['other'])){
          foreach ($prices['other'] as $priceOtherProductTypeId => $priceOther){
            $summedPriceOthers+=$priceOther;
          }
        }
        $summedQuantityOthers=0;
        //pr($quantityOthers);
        if (!empty($quantities['other'])){
          foreach ($quantities['other'] as $productTypeId=>$productTypeQuantity){
            $summedQuantityOthers+=$productTypeQuantity;
          }
        }
        $saleTotalCost=0;
        //pr($costs);
        foreach ($costs as $costType=>$costValue){
          if ($costType!= 'other' && $costType!= 'consumible'){
            $saleTotalCost +=  $costValue;
          }
        }
        if (
          ($quantities['produced']+$quantities['cap']+$quantities['service']+$quantities['import']+$quantities['local']+$summedQuantityOthers)>0
          ||
          (
            !empty($sale['Invoice'])
            &&
            ($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement']))
          )
          ||
          (
            empty($sale['Invoice'])
            &&
            empty($sale['CashReceipt'])
            &&
            empty($sale['StockMovement'])
          )
        ){
          $unconditionalSales[$rowCounter]=[
            'Order'=>[
              'id'=>$sale['Order']['id'],
              'order_date'=>$sale['Order']['order_date'],
              'order_code'=>$sale['Order']['order_code'],
              'client_type_id'=>$sale['Order']['client_type_id'],
              'total_price'=>$sale['Order']['total_price'],
              'total_cost'=>$saleTotalCost,
              'prices'=>$prices,
              'costs'=>$costs,
              'quantities'=>$quantities,
            ],
            'ThirdParty'=>[
              'id'=>$sale['ThirdParty']['id'],
              'company_name'=>($sale['ThirdParty']['bool_generic']?$sale['Order']['client_name']:$sale['ThirdParty']['company_name']),
              'bool_generic'=>$sale['ThirdParty']['bool_generic'],
            ],
          ];
          if (!empty($sale['Invoice'])){
            //pr($sale);
            $unconditionalSales[$rowCounter]['Invoice']=$sale['Invoice'][0];
            if($sale['Invoice'][0]['bool_annulled']){
              $unconditionalSales[$rowCounter]['Invoice']['bool_annulled']=true;
            }
            else {
              $unconditionalSales[$rowCounter]['Invoice']['bool_annulled']='0';
            }
          }
          else {
            // this implies a remission I think
            $unconditionalSales[$rowCounter]['Invoice']['bool_annulled']='0';
            $unconditionalSales[$rowCounter]['Invoice']['bool_credit']=true;
          }
          $rowCounter++;
        }  
   
        
      }
      $unconditionalUserSales[$currentUserId]['Sales']=$unconditionalSales;
    }
    //pr($unconditionalUserSales);    
    
    $emptyCostPriceArray=[
      'cost'=>0,
      'price'=>0,
    ]; 
    $statsByVendor=$statsByClientType=[
      '0'=>$emptyCostPriceArray,
    ];
    $statsByVendorByCreditStatus=[
      '0'=>$emptyCostPriceArray,
      'cash'=>[
        '0'=>$emptyCostPriceArray
      ],
      'credit'=>[
        '0'=>$emptyCostPriceArray
      ],
    ];
    foreach ($unconditionalUserSales as $currentUserId=>$currentUserSalesData){
      $statUserId=$currentUserId>0?$currentUserId:-1;
      $statsByVendor[$statUserId]=$emptyCostPriceArray;
      $statsByVendorByCreditStatus['cash'][$statUserId]=$emptyCostPriceArray;
      $statsByVendorByCreditStatus['credit'][$statUserId]=$emptyCostPriceArray;
      //pr($currentUserSalesData);
      if (!empty($currentUserSalesData['Sales'])){
        foreach ($currentUserSalesData['Sales'] as $currentSale){
          //pr($currentSale);
          $statsByVendor[$statUserId]['cost']+=$currentSale['Order']['total_cost'];
          $statsByVendor[$statUserId]['price']+=$currentSale['Order']['total_price'];
          $statsByVendor[0]['cost']+=$currentSale['Order']['total_cost'];
          $statsByVendor[0]['price']+=$currentSale['Order']['total_price'];
          
          if ($currentSale['Invoice']['bool_credit']){
            $statsByVendorByCreditStatus['credit'][$statUserId]['cost']+=$currentSale['Order']['total_cost'];
            $statsByVendorByCreditStatus['credit'][$statUserId]['price']+=$currentSale['Order']['total_price'];
            $statsByVendorByCreditStatus['credit'][0]['cost']+=$currentSale['Order']['total_cost'];
            $statsByVendorByCreditStatus['credit'][0]['price']+=$currentSale['Order']['total_price'];
          }
          else {
            $statsByVendorByCreditStatus['cash'][$statUserId]['cost']+=$currentSale['Order']['total_cost'];
            $statsByVendorByCreditStatus['cash'][$statUserId]['price']+=$currentSale['Order']['total_price'];
            $statsByVendorByCreditStatus['cash'][0]['cost']+=$currentSale['Order']['total_cost'];
            $statsByVendorByCreditStatus['cash'][0]['price']+=$currentSale['Order']['total_price'];
          }
          $statsByVendorByCreditStatus[0]['cost']+=$currentSale['Order']['total_cost'];
          $statsByVendorByCreditStatus[0]['price']+=$currentSale['Order']['total_price'];
          
          $statClientTypeId=$currentSale['Order']['client_type_id']>0?$currentSale['Order']['client_type_id']:-1;
          if (!array_key_exists(
          $statClientTypeId,$statsByClientType)){
            $statsByClientType[$statClientTypeId]=$emptyCostPriceArray;
          }
          $statsByClientType[$statClientTypeId]['cost']+=$currentSale['Order']['total_cost'];
          $statsByClientType[$statClientTypeId]['price']+=$currentSale['Order']['total_price'];
          $statsByClientType[0]['cost']+=$currentSale['Order']['total_cost'];
          $statsByClientType[0]['price']+=$currentSale['Order']['total_price'];
        }
      }
    }
    $this->set(compact('statsByVendor','statsByVendorByCreditStatus','statsByClientType'));
    //pr($statsByVendorByCreditStatus);
    
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
	}
  public function guardarFacturasPorVendedor() {
		$exportData=$_SESSION['facturasPorVendedor'];
		$this->set(compact('exportData'));
	}	
	
}