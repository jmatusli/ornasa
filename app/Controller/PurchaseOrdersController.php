<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class PurchaseOrdersController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel');
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('getPurchaseOrderCode','setduedate','getPurchaseOrderInfo','verPdf','guardarProveedoresPorPagar','guardarFacturasPorPagar','autorizar');		
	}
  
  public function getPurchaseOrderCode(){
    $this->autoRender = false; 
		$this->request->onlyAllow('ajax'); 
		$this->layout = "ajax";
		$warehouseId=trim($_POST['warehouseId']);
    
    $this->loadModel('Warehouse');
    $warehouseSeries=$this->Warehouse->getWarehouseSeries($warehouseId);
    
    return $this->PurchaseOrder->getPurchaseOrderCode($warehouseId,$warehouseSeries);
  }
  
  public function setduedate(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
    
		$providerId=trim($_POST['providerid']);
		$emissionday=trim($_POST['emissionday']);
		$emissionmonth=trim($_POST['emissionmonth']);
		$emissionyear=trim($_POST['emissionyear']);
	
		
		if (!$providerId){
			throw new NotFoundException(__('Proveedor no está presente'));
		}
    $this->loadModel('ThirdParty');
    if (!$this->ThirdParty->exists($providerId)) {
			throw new NotFoundException(__('Proveedor inválido'));
		}
		$provider=$this->ThirdParty->getProviderById($providerId);
		//pr($provider);
		$creditperiod=0;
		if (!empty($provider)){
			$creditperiod=$provider['ThirdParty']['credit_days'];
		}
		$emissionDateString=$emissionyear.'-'.$emissionmonth.'-'.$emissionday;
		$emissionDate=date( "Y-m-d", strtotime($emissionDateString));
		
		$dueDate=$emissionDate;
		if($creditperiod>0){
			$dueDate=date("Y-m-d",strtotime($emissionDate."+".$creditperiod." days"));
		}
		
		$this->set(compact('dueDate'));
	}
  
  public function getPurchaseOrderInfo() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax'); 
		$this->layout = "ajax";
    
		$purchaseOrderId=trim($_POST['purchaseOrderId']);
    $boolIncludeProducts=trim($_POST['boolIncludeProducts']);
		if (!$purchaseOrderId){
			throw new NotFoundException(__('Orden de compra no presente'));
		}
		
    $contain=$boolIncludeProducts?['PurchaseOrderProduct'=>['Product']]:[];
		// $this->InvoiceProduct->virtualFields['total_product_quantity']=0;
		$purchaseOrder=$this->PurchaseOrder->find('first',[
			'fields'=>[
				'PurchaseOrder.purchase_order_code',
        'PurchaseOrder.purchase_order_date',
        'PurchaseOrder.provider_id',
        'PurchaseOrder.cost_subtotal',
        'PurchaseOrder.currency_id',
        'PurchaseOrder.bool_iva',
			],
			'conditions'=>[
				'PurchaseOrder.id'=>$purchaseOrderId,
			],
      'contain'=>$contain,
		]);
    
    $this->loadModel('ExchangeRate');
    $exchangeRatePurchaseOrder=$this->ExchangeRate->getApplicableExchangeRateValue($purchaseOrder['PurchaseOrder']['purchase_order_date']);
    $purchaseOrder['PurchaseOrder']['exchange_rate']=$exchangeRatePurchaseOrder;
    
		return json_encode($purchaseOrder);
	}
  
  public function resumen() {
    $this->loadModel('Currency');
    $this->loadModel('ExchangeRate');
    $this->loadModel('PurchaseOrderState');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
     $this->loadModel('UserPageRight');
		$loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $this->PurchaseOrder->recursive = -1;
    
    $purchaseOrderStates=$this->PurchaseOrderState->getPurchaseOrderStateList();
    $this->set(compact('purchaseOrderStates'));
    //pr($purchaseOrderStates);
    
    $purchaseOrderStateColors=$this->PurchaseOrderState->getPurchaseOrderStateColors();
    $this->set(compact('purchaseOrderStateColors'));
    //pr($purchaseOrderStateColors);
	
	$canAutorizePurcharse=$this->UserPageRight->hasUserPageRight('AUTORIZAR_COMPRA',$userRoleId,$loggedUserId,'PurchaseOrders','Autorizar');
	
    $this->set(compact('canAutorizePurcharse'));
	
    $purchaseOrderStateId=0;
    $currencyId=CURRENCY_USD;
    
    $warehouseId=0;
    /*
    if ($userRoleId == ROLE_ADMIN){  
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      else {
        $warehouseId = WAREHOUSE_DEFAULT;
      }
		}
    */
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			//$userId=$this->request->data['Report']['user_id'];
			$currencyId=$this->request->data['Report']['currency_id'];
      $purchaseOrderStateId=$this->request->data['Report']['purchase_order_state_id'];
      
      $warehouseId=$this->request->data['Report']['warehouse_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			if ($this->Session->check('currencyId')){
				$currencyId=$_SESSION['currencyId'];
			}
			//if ($this->Session->check('userId')){
			//	$userId=$_SESSION['userId'];
			//}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$_SESSION['currencyId']=$currencyId;
		//$_SESSION['userId']=$userId;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('currencyId'));
    $this->set(compact('purchaseOrderStateId'));
    
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
		
    $purchaseOrderStateArray=[];
    foreach ($purchaseOrderStates as $stateId=>$stateName){
      if ($purchaseOrderStateId == 0 || $purchaseOrderStateId == $stateId){
        $conditions=[
          'PurchaseOrder.purchase_order_state_id'=>$stateId,
          'PurchaseOrder.warehouse_id'=>$warehouseId,
        ];
        if ($stateId == PURCHASE_ORDER_STATE_RECEIVED_COMPLETELY){
          $conditions['PurchaseOrder.purchase_order_date >=']=$startDate;
          $conditions['PurchaseOrder.purchase_order_date <']=$endDatePlusOne;
        }
        
        $purchaseOrderCount=	$this->PurchaseOrder->find('count', [
          'fields'=>['PurchaseOrder.id'],
          'conditions' => $conditions,
        ]);
        $this->Paginator->settings = [
          'conditions' => $conditions,
          'contain'=>[				
            'Currency',
            'Provider',
            'User',
            'PurchaseOrderProduct'=>[
              'conditions'=>['product_quantity >'=>0],
              'Product',
              'Unit',
            ],
            'Entry',
          ],
          'order'=>'purchase_order_date DESC,purchase_order_code DESC',
          'limit'=>($purchaseOrderCount!=0?$purchaseOrderCount:1),
        ];

        $purchaseOrders = $this->Paginator->paginate('PurchaseOrder');
        if (!empty($purchaseOrders)){
          for ($i=0;$i<count($purchaseOrders);$i++){
            // set the exchange rate
            $purchaseOrderDate=$purchaseOrders[$i]['PurchaseOrder']['purchase_order_date'];
            $exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($purchaseOrderDate);
            $purchaseOrders[$i]['PurchaseOrder']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
          }
        }
        $purchaseOrderStateArray[$stateId]=[
          'PurchaseOrderState'=>[
            'name'=>$stateName,
            'color'=>$purchaseOrderStateColors[$stateId],
          ],
          'PurchaseOrders'=>$purchaseOrders,
        ];
      }
    }
    $this->set(compact('purchaseOrderStateArray'));
		
		$currencies=$this->Currency->find('list');
		$this->set(compact('currencies'));
		
    $aco_name="PurchaseOrders/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="PurchaseOrders/confirmar";		
		$bool_confirmar_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_confirmar_permission'));
    $aco_name="PurchaseOrders/anular";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
    
    $aco_name="Orders/verEntrada";		
		$bool_orders_verEntrada_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_orders_verEntrada_permission'));
    
		$aco_name="Providers/index";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="Providers/add";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
	
	public function guardarResumen() {
		$exportData=$_SESSION['resumenOrdenCompras'];
		$this->set(compact('exportData'));
	}

	public function ver($id = null) {
		if (!$this->PurchaseOrder->exists($id)) {
			throw new NotFoundException(__('Invalid purchase order'));
		}
		$options = [
			'conditions' => ['PurchaseOrder.id' => $id],
			'contain'=>[
				'Currency',
				'Provider',
				'PurchaseOrderProduct'=>[
          'Product',
          'Unit',
        ],
        'PurchaseOrderState',
				'User',
        'Warehouse',
			],
		];
		$purchaseOrder=$this->PurchaseOrder->find('first', $options);
		//pr($purchaseOrder);
    
		//$purchaseOrderDate=$purchaseOrder['PurchaseOrder']['purchase_order_date'];
		//$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($purchaseOrderDate);
		//$purchaseorder['PurchaseOrder']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
		
		$this->set(compact('purchaseOrder'));
		//pr($purchaseOrder);
		
		$filename="Orden_de_compras_".$purchaseOrder['PurchaseOrder']['purchase_order_code'];
		$this->set(compact('filename'));
		
		$aco_name="PurchaseOrders/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    $aco_name="PurchaseOrders/anular";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
    
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}

	public function verPdf($id = null) {
		if (!$this->PurchaseOrder->exists($id)) {
			throw new NotFoundException(__('Invalid purchase order'));
		}
		
		$options = [
			'conditions' => ['PurchaseOrder.id' => $id],
			'contain'=>[
				'Currency',
				'Provider',
				'PurchaseOrderProduct'=>[
          'Product',
          'Unit',
        ],
        'PurchaseOrderState',
				'User',
			],
		];
		$purchaseOrder=$this->PurchaseOrder->find('first', $options);
		
		//$purchaseOrderDate=$purchaseOrder['PurchaseOrder']['purchase_order_date'];
		//$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($purchaseOrderDate);
		//$purchaseorder['PurchaseOrder']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
		
		$this->set(compact('purchaseOrder'));
		
		$filename="Orden_de_compras_".$purchaseOrder['PurchaseOrder']['purchase_order_code'];
		$this->set(compact('filename'));
	}

	public function crear() {
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('PlantProductType');
    $this->loadModel('Unit');
    
    $this->loadModel('ProductionType');
    
    $this->loadModel('ThirdParty');
		
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
		$currencyId=CURRENCY_USD;
		$warehouseId=0;
		
    $requestProducts=[];
    //$boolRefreshReload=false;
    
    if ($this->request->is('post')) {
			$currencyId=$this->request->data['PurchaseOrder']['currency_id'];
      $warehouseId=$this->request->data['PurchaseOrder']['warehouse_id'];
      
			$flashMessage="";
			foreach ($this->request->data['PurchaseOrderProduct'] as $purchaseOrderProduct){
				if ($purchaseOrderProduct['product_id']>0 && $purchaseOrderProduct['product_quantity']>0 && $purchaseOrderProduct['product_unit_cost']>0){
					$requestProducts[]['PurchaseOrderProduct']=$purchaseOrderProduct;
				}
			}
			$purchaseOrderDateArray=$this->request->data['PurchaseOrder']['purchase_order_date'];
			//pr($quotationDateArray);
			$purchaseOrderDateString=$purchaseOrderDateArray['year'].'-'.$purchaseOrderDateArray['month'].'-'.$purchaseOrderDateArray['day'];
			$purchaseOrderDate=date( "Y-m-d", strtotime($purchaseOrderDateString));
      $nowDatePlusTwoWeeks=date( "Y-m-d", strtotime(date('Y-m-d')."+2 weeks"));
      
      //if (!empty($this->request->data['refresh'])){
      //  //$boolRefreshReload = true;
      //}
      //else {
      //  //$boolRefreshReload = false;
      if (empty($this->request->data['refresh'])){  
        
        $boolMultiplicationOK=true;
        $productMultiplicationWarning="";
              
        $productTotalSumBasedOnProductTotals=0;
        foreach ($this->request->data['PurchaseOrderProduct'] as $purchaseOrderProduct){
          if ($purchaseOrderProduct['product_id']>0 && $purchaseOrderProduct['product_quantity']>0){
            $multiplicationDifference=abs($purchaseOrderProduct['product_total_cost']-$purchaseOrderProduct['product_quantity']*$purchaseOrderProduct['product_unit_cost']);
            //pr($purchaseOrderProduct);
            if ($multiplicationDifference>=0.01){
              $boolMultiplicationOK=false;
              $this->Product->recursive=-1;
              $relatedProduct=$this->Product->find('first',[
                'conditions'=>['Product.id'=>$purchaseOrderProduct['product_id'],],
              ]);
               $productMultiplicationWarning.="Producto ".$relatedProduct['Product']['name']." tiene una cantidad ".$purchaseOrderProduct['product_quantity']." y un precio unitario ".$purchaseOrderProduct['product_unit_cost'].", pero el total calculado ".$purchaseOrderProduct['product_total_cost']." no es correcto;";
            };
            $productTotalSumBasedOnProductTotals+=$purchaseOrderProduct['product_total_cost'];
          }
        }
        
        if (array_key_exists('bool_authorized',$this->request->data['PurchaseOrder']) && $this->request->data['PurchaseOrder']['bool_authorized']){
          $this->request->data['PurchaseOrder']['purchase_order_state_id'] = PURCHASE_ORDER_STATE_AUTHORIZED;
        }
        else {
          $this->request->data['PurchaseOrder']['purchase_order_state_id'] = PURCHASE_ORDER_STATE_AWAITING_AUTHORIZATION;
        }
        
        if ($purchaseOrderDateString>$nowDatePlusTwoWeeks){
          $this->Session->setFlash(__('La fecha de orden de compra no puede estar más que dos semanas en el futuro!  No se guardó la orden de compra.'), 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['PurchaseOrder']['provider_id'])){
          $this->Session->setFlash(__('Se debe seleccionar el proveedor.  No se guardó la orden de compra.'), 'default',['class' => 'error-message']);
        }
        elseif (!$boolMultiplicationOK){
          $this->Session->setFlash($productMultiplicationWarning.'  vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (abs($productTotalSumBasedOnProductTotals-$this->request->data['PurchaseOrder']['cost_subtotal']) > 0.01){
          $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['PurchaseOrder']['cost_subtotal'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (abs($this->request->data['PurchaseOrder']['cost_total']-$this->request->data['PurchaseOrder']['cost_iva']-$this->request->data['PurchaseOrder']['cost_subtotal'])>0.01){
          $this->Session->setFlash('La suma del subtotal y el IVA no igualan el precio total.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        else {				
          $datasource=$this->PurchaseOrder->getDataSource();
          $datasource->begin();
          try {
            $this->loadModel('PurchaseOrderProduct');
            $this->PurchaseOrder->create();
            if (!$this->PurchaseOrder->save($this->request->data)) {
              echo "Problema guardando la orden de compra";
              pr($this->validateErrors($this->PurchaseOrder));
              throw new Exception();
            }
            $purchaseOrderId=$this->PurchaseOrder->id;
            
            foreach ($this->request->data['PurchaseOrderProduct'] as $purchaseOrderProduct){
              if ($purchaseOrderProduct['product_id']>0 && $purchaseOrderProduct['product_quantity']>0){
                $productArray=[
                  'PurchaseOrderProduct'=>[
                    'purchase_order_id'=>$purchaseOrderId,
                    'product_id'=>$purchaseOrderProduct['product_id'],
                    'product_quantity'=>$purchaseOrderProduct['product_quantity'],
                    'unit_id'=>$purchaseOrderProduct['unit_id'],
                    'product_unit_cost'=>$purchaseOrderProduct['product_unit_cost'],
                    'product_total_cost'=>$purchaseOrderProduct['product_total_cost'],
                    'currency_id'=>$this->request->data['PurchaseOrder']['currency_id'],
                  ],
                ];
                $this->PurchaseOrderProduct->create();
                if (!$this->PurchaseOrderProduct->save($productArray)) {
                  echo "Problema guardando los productos de la orden de compra";
                  pr($this->validateErrors($this->PurchaseOrderProduct));
                  throw new Exception();
                }
              }
            }
          
            $datasource->commit();
            $this->recordUserAction($this->PurchaseOrder->id,null,null);
            $this->recordUserActivity($this->Session->read('User.username'),"Se registró la orden de compra número ".$this->request->data['PurchaseOrder']['purchase_order_code']);
            
            $this->Session->setFlash(__('Se guardó la orden de compra.'),'default',['class' => 'success']);				
            return $this->redirect(['action' => 'ver',$purchaseOrderId]);
          }
          catch(Exception $e){
            $datasource->rollback();
            // pr($e);
            $this->Session->setFlash(__('No se podía guardar la orden de compra.'), 'default',['class' => 'error-message']);
          }
        }
      }
    }
		
    $this->set(compact('requestProducts'));
    //$this->set(compact('boolRefreshReload'));
		$this->set(compact('currencyId'));
		
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
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    $plantId=0;
    if ($warehouseId > 0){
      $plantId=$this->Warehouse->getPlantId($warehouseId);
    }
    $this->set(compact('plantId'));
    //echo 'plant id is '.$plantId.'<br/>';
    
    $warehousePlants=$this->Warehouse->getWarehousePlantIds(array_keys($warehouses));
    $this->set(compact('warehousePlants'));
    //pr($warehousePlants);
    
    $providers = $this->ThirdParty->getActiveProviderList($plantId);
    
		$users = $this->PurchaseOrder->User->find('list',array('order'=>'User.username'));
		$currencies = $this->PurchaseOrder->Currency->find('list');
		$this->set(compact('providers', 'users', 'currencies'));
		
    $productTypeIdsNotProduced=$this->ProductType->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>['ProductType.product_category_id !='=>CATEGORY_PRODUCED]
    ]);
    $productTypesForPlant=$this->PlantProductType->getProductTypesForPlant($plantId);
    
    $productionTypes=$this->ProductionType->getProductionTypesForPlant($plantId);
    //pr($productionTypes);
    
    $acceptableProductTypeIds=array_intersect($productTypeIdsNotProduced,array_keys($productTypesForPlant));
    
		$products=$this->Product->find('list',[
      'conditions'=>[
        'bool_active'=>true,
        'product_type_id'=>$acceptableProductTypeIds,
        //'product_type_id'=>$productTypeIdsNotProduced,
        //'production_type_id'=>array_keys($productionTypes),
      ],
      'order'=>'name',
    ]);
    foreach ($products as $key=>$value){
      if (strlen($value)>30){
        $products[$key]=substr($value,0,30);
      }
    }
		$this->set(compact('products'));
    
    $warehouseSeries=$this->Warehouse->getWarehouseSeries($warehouseId);
    
    $purchaseOrderCode=$this->PurchaseOrder->getPurchaseOrderCode($warehouseId,$warehouseSeries);
    $this->set(compact('purchaseOrderCode'));
    
    //$providerNatures=$this->ThirdParty->getProviderNatureIdsByProviderId();
    //$this->set(compact('providerNatures'));
    
    $units=$this->Unit->getUnitList();
    $this->set(compact('units'));
    
    $productUnits=$this->Product->getUnitIdsForProducts();
    $this->set(compact('productUnits'));
    //pr($productUnits); 
    
    $productPackagingUnits=$this->Product->getPackagingUnitsForProducts();
    $this->set(compact('productPackagingUnits'));
    
    $productDefaultCosts=$this->Product->getDefaultCostsForProducts();
    $this->set(compact('productDefaultCosts'));
    
		$aco_name="PurchaseOrders/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}

	public function editar($id = null) {
		if (!$this->PurchaseOrder->exists($id)) {
			throw new NotFoundException(__('Invalid purchase order'));
		}
    $this->loadModel('PurchaseOrderState');
    
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('PlantProductType');
    $this->loadModel('Unit');
    
    $this->loadModel('ProductionType');
    
    $this->loadModel('ThirdParty');
        
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
       
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
		$loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
        
    $requestProducts=[];
		$currencyId=CURRENCY_USD;
		
		if ($this->request->is(['post', 'put'])) {
      //pr($this->request->data['PurchaseOrder']);
			$currencyId=$this->request->data['PurchaseOrder']['currency_id'];
      $warehouseId=$this->request->data['PurchaseOrder']['warehouse_id'];
      
			$flashMessage="";
			foreach ($this->request->data['PurchaseOrderProduct'] as $purchaseOrderProduct){
				if ($purchaseOrderProduct['product_id']>0 && $purchaseOrderProduct['product_quantity']>0 && $purchaseOrderProduct['product_unit_cost']>0){
					$requestProducts[]['PurchaseOrderProduct']=$purchaseOrderProduct;
				}
			}
			//pr($requestProducts);
			$purchaseOrderDateArray=$this->request->data['PurchaseOrder']['purchase_order_date'];
			//pr($quotationDateArray);
			$purchaseOrderDateString=$purchaseOrderDateArray['year'].'-'.$purchaseOrderDateArray['month'].'-'.$purchaseOrderDateArray['day'];
			$purchaseOrderDate=date( "Y-m-d", strtotime($purchaseOrderDateString));
      $nowDatePlusTwoWeeks=date( "Y-m-d", strtotime(date('Y-m-d')."+2 weeks"));
      
			$boolMultiplicationOK=true;
      $productMultiplicationWarning="";
            
      $productTotalSumBasedOnProductTotals=0;
			foreach ($this->request->data['PurchaseOrderProduct'] as $purchaseOrderProduct){
				if ($purchaseOrderProduct['product_id']>0 && $purchaseOrderProduct['product_quantity']>0){
					$multiplicationDifference=abs($purchaseOrderProduct['product_total_cost']-$purchaseOrderProduct['product_quantity']*$purchaseOrderProduct['product_unit_cost']);
					//pr($purchaseOrderProduct);
					if ($multiplicationDifference>=0.01){
						$boolMultiplicationOK=false;
            $this->Product->recursive=-1;
            $relatedProduct=$this->Product->find('first',[
              'conditions'=>['Product.id'=>$purchaseOrderProduct['product_id'],],
            ]);
             $productMultiplicationWarning.="Producto ".$relatedProduct['Product']['name']." tiene una cantidad ".$purchaseOrderProduct['product_quantity']." y un precio unitario ".$purchaseOrderProduct['product_unit_cost']." para un total calculado de ".($purchaseOrderProduct['product_quantity']*$purchaseOrderProduct['product_unit_cost']).", pero el total en pantalla es ".$purchaseOrderProduct['product_total_cost'].", lo no es correcto;";
					};
          $productTotalSumBasedOnProductTotals+=$purchaseOrderProduct['product_total_cost'];
				}
			}
      
      if ($this->request->data['PurchaseOrder']['purchase_order_state_id'] < PURCHASE_ORDER_STATE_AUTHORIZED){
        $this->request->data['PurchaseOrder']['bool_authorized'] = false;
      }
      else {
        $this->request->data['PurchaseOrder']['bool_authorized'] = true;
      }
			//if ($purchaseOrderDateString>date('Y-m-d')){
      if ($purchaseOrderDateString>$nowDatePlusTwoWeeks){
				$this->Session->setFlash(__('La fecha de orden de compra no puede estar más que dos semanas en el futuro!  No se guardó la orden de compra.'), 'default',['class' => 'error-message']);
			}
			elseif (empty($this->request->data['PurchaseOrder']['provider_id'])){
				$this->Session->setFlash(__('Se debe seleccionar el proveedor.  No se guardó la orden de compra.'), 'default',['class' => 'error-message']);
			}
			elseif (!$boolMultiplicationOK){
				$this->Session->setFlash($productMultiplicationWarning.'  vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
			}
      elseif (abs($productTotalSumBasedOnProductTotals-$this->request->data['PurchaseOrder']['cost_subtotal']) > 0.01){
        $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$productTotalSumBasedOnProductTotals.' pero el total calculado es '.$this->request->data['PurchaseOrder']['cost_subtotal'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      elseif (abs($this->request->data['PurchaseOrder']['cost_total']-$this->request->data['PurchaseOrder']['cost_iva']-$this->request->data['PurchaseOrder']['cost_subtotal'])>0.01){
        $this->Session->setFlash('La suma del subtotal y el IVA no igualan el precio total.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      elseif (!$this->request->data['PurchaseOrder']['bool_authorized'] && $this->request->data['PurchaseOrder']['purchase_order_state_id'] > PURCHASE_ORDER_STATE_AWAITING_AUTHORIZATION){
        $this->Session->setFlash('Se marcó que la orden de compra no está autorizada, pero el estado de la orden de compra no está Esperando Autorización.  Por favor corriga el estado de la orden de compra. No se guardó la orden de  compra.', 'default',['class' => 'error-message']);
      } 
			else {				
				$datasource=$this->PurchaseOrder->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
					$this->loadModel('PurchaseOrderProduct');
					
					$previousPurchaseOrderProducts=$this->PurchaseOrderProduct->find('all',array(
						'fields'=>array(
							'PurchaseOrderProduct.id',
						),
						'conditions'=>array(
							'PurchaseOrderProduct.purchase_order_id'=>$id,
						),
					));
					if (!empty($previousPurchaseOrderProducts)){
						foreach ($previousPurchaseOrderProducts as $previousPurchaseOrderProduct){
							$this->PurchaseOrderProduct->id=$previousPurchaseOrderProduct['PurchaseOrderProduct']['id'];
							$this->PurchaseOrderProduct->delete($previousPurchaseOrderProduct['PurchaseOrderProduct']['id']);
						}
					}
					if ($this->request->data['PurchaseOrder']['bool_annulled']){
						$this->request->data['PurchaseOrder']['cost_subtotal']=0;
						$this->request->data['PurchaseOrder']['cost_iva']=0;
						$this->request->data['PurchaseOrder']['cost_total']=0;
						$this->PurchaseOrder->id=$id;
						if (!$this->PurchaseOrder->save($this->request->data)) {
							echo "Problema guardando la orden de compra";
							//pr($this->validateErrors($this->PurchaseOrder));
							throw new Exception();
						} 
						$purchaseOrderId=$this->PurchaseOrder->id;
						
					}
					else {			
						//pr($this->request->data);
            $this->PurchaseOrder->id=$id;
						if (!$this->PurchaseOrder->save($this->request->data)) {
							echo "Problema guardando la orden de compra";
							pr($this->validateErrors($this->PurchaseOrder));
							throw new Exception();
						}
						$purchaseOrderId=$this->PurchaseOrder->id;
						
						foreach ($this->request->data['PurchaseOrderProduct'] as $purchaseOrderProduct){
							if ($purchaseOrderProduct['product_id']>0 && $purchaseOrderProduct['product_quantity']>0){
								// UPDATE THE SALES ORDER PRODUCT 
								//pr($purchaseOrderProduct);
								$productArray=[
                  'PurchaseOrderProduct'=>[
                    'purchase_order_id'=>$purchaseOrderId,
                    'product_id'=>$purchaseOrderProduct['product_id'],
                    'product_quantity'=>$purchaseOrderProduct['product_quantity'],
                    'unit_id'=>$purchaseOrderProduct['unit_id'],
                    'product_unit_cost'=>$purchaseOrderProduct['product_unit_cost'],
                    'product_total_cost'=>$purchaseOrderProduct['product_total_cost'],
                    'currency_id'=>$this->request->data['PurchaseOrder']['currency_id'],
                  ],
                ];
								$this->PurchaseOrderProduct->create();
								if (!$this->PurchaseOrderProduct->save($productArray)) {
									echo "Problema guardando los productos de la orden de compra";
									pr($this->validateErrors($this->PurchaseOrderProduct));
									throw new Exception();
								}
							}
						}
					}	
						
					$datasource->commit();
					$this->recordUserAction($this->PurchaseOrder->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se editó la orden de compra número ".$this->request->data['PurchaseOrder']['purchase_order_code']);
					
					$this->Session->setFlash(__('Se guardó la orden de compra.'),'default',['class' => 'success']);				
					return $this->redirect(['action' => 'ver',$purchaseOrderId]);					
				}
				catch(Exception $e){
					$datasource->rollback();
					 pr($e);
					$this->Session->setFlash(__('No se podía guardar la orden de compra.'), 'default',['class' => 'error-message']);
				}
			}
		}
		else {
			$options = [
				'conditions' => ['PurchaseOrder.id' => $id],
				'contain'=>[
					'PurchaseOrderProduct'=>['Product'],
				],
			];
			$this->request->data = $this->PurchaseOrder->find('first', $options);
      //pr($this->request->data['PurchaseOrder']);
      
      $currencyId=$this->request->data['PurchaseOrder']['currency_id'];
      $warehouseId=$this->request->data['PurchaseOrder']['warehouse_id'];
			for ($i=0;$i<count($this->request->data['PurchaseOrderProduct']);$i++){
				if ($this->request->data['PurchaseOrderProduct'][$i]['product_id']>0 && $this->request->data['PurchaseOrderProduct'][$i]['product_quantity']>0 && $this->request->data['PurchaseOrderProduct'][$i]['product_unit_cost']>0){
          $thisPurchaseOrderProduct=$this->request->data['PurchaseOrderProduct'][$i];
          $thisPurchaseOrderProduct['product_packaging_unit']=$thisPurchaseOrderProduct['Product']['packaging_unit'];
					$requestProducts[]['PurchaseOrderProduct']=$thisPurchaseOrderProduct;
				}
			}
		}
			
		$this->set(compact('requestProducts'));
		$this->set(compact('currencyId'));
    $this->set(compact('warehouseId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId,$warehouseId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
		
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    $plantId=0;
    if ($warehouseId > 0){
      $plantId=$this->Warehouse->getPlantId($warehouseId);
    }
    $this->set(compact('plantId'));
    //echo 'plant id is '.$plantId.'<br/>';
    
    $warehousePlants=$this->Warehouse->getWarehousePlantIds(array_keys($warehouses));
    $this->set(compact('warehousePlants'));
    //pr($warehousePlants);
    
    $providers = $this->ThirdParty->getActiveProviderList($plantId);
    
    $users = $this->PurchaseOrder->User->find('list',['order'=>'User.username']);
		$currencies = $this->PurchaseOrder->Currency->find('list');
		$this->set(compact('providers', 'users', 'currencies', 'paymentModes'));
		
		$productTypeIdsNotProduced=$this->ProductType->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>['ProductType.product_category_id !='=>CATEGORY_PRODUCED]
    ]);
    $productTypesForPlant=$this->PlantProductType->getProductTypesForPlant($plantId);
    
    $productionTypes=$this->ProductionType->getProductionTypesForPlant($plantId);
    //pr($productionTypes);
    
    $acceptableProductTypeIds=array_intersect($productTypeIdsNotProduced,array_keys($productTypesForPlant));
    
    $productIdsInPurchaseOrder=[];
    foreach ($requestProducts as $requestProduct){
      //pr($requestProduct);
      $productIdsInPurchaseOrder[]=$requestProduct['PurchaseOrderProduct']['product_id'];
    }
    $products=$this->Product->find('list',[
      'conditions'=>[
        'OR'=>[
          [
            'bool_active'=>true,
            'product_type_id'=>$acceptableProductTypeIds,
            //'product_type_id'=>$productTypeIdsNotProduced,
            //'production_type_id'=>array_keys($productionTypes),
          ],
          [
            'Product.id'=>$productIdsInPurchaseOrder,
          ],
        ],
        
      ],
      'order'=>'name',
    ]);
    foreach ($products as $key=>$value){
      if (strlen($value)>30){
        $products[$key]=substr($value,0,30);
      }
    }
		$this->set(compact('products'));
    
    $purchaseOrderStates=$this->PurchaseOrderState->getPurchaseOrderStateList();
    $this->set(compact('purchaseOrderStates'));
    
    //$providerNatures=$this->ThirdParty->getProviderNatureIdsByProviderId();
    //$this->set(compact('providerNatures'));
    
    $units=$this->Unit->getUnitList();
    $this->set(compact('units'));
    
    $productUnits=$this->Product->getUnitIdsForProducts();
    $this->set(compact('productUnits'));
    //pr($productUnits); 
    
    $productPackagingUnits=$this->Product->getPackagingUnitsForProducts();
    $this->set(compact('productPackagingUnits'));
    
    $productDefaultCosts=$this->Product->getDefaultCostsForProducts();
    $this->set(compact('productDefaultCosts'));
		
		$aco_name="PurchaseOrders/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    $aco_name="PurchaseOrders/anular";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
    
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->PurchaseOrder->id = $id;
		if (!$this->PurchaseOrder->exists()) {
			throw new NotFoundException(__('Invalid purchase order'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$purchaseOrder=$this->PurchaseOrder->find('first',array(
			'conditions'=>array(
				'PurchaseOrder.id'=>$id,
			),
			'contain'=>array(
				'PurchaseOrderProduct'=>array(
					'fields'=>array(
						'PurchaseOrderProduct.id',
					),
					'Product'=>array(
						'fields'=>array('Product.id','Product.name'),
					),
				),
			),
		));
		$flashMessage="";
		$boolDeletionAllowed=true;
		
		if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó la orden de compra.";
			$this->Session->setFlash($flashMessage, 'default',['class' => 'error-message']);
			return $this->redirect(['action' => 'ver',$id]);
		}
		else {
			$datasource=$this->PurchaseOrder->getDataSource();
			$datasource->begin();	
			try {
				//delete all products, remarks and other costs
				foreach ($purchaseOrder['PurchaseOrderProduct'] as $purchaseOrderProduct){
					if (!$this->PurchaseOrder->PurchaseOrderProduct->delete($purchaseOrderProduct['id'])) {
						echo "Problema al eliminar el producto de la orden de compra";
						pr($this->validateErrors($this->PurchaseOrder->PurchaseOrderProduct));
						throw new Exception();
					}
				}
				
				if (!$this->PurchaseOrder->delete($id)) {
					echo "Problema al eliminar la orden de compra";
					pr($this->validateErrors($this->PurchaseOrder));
					throw new Exception();
				}
						
				$datasource->commit();
				
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=array();
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$purchaseOrder['PurchaseOrder']['id'];
				$deletionArray['Deletion']['reference']=$purchaseOrder['PurchaseOrder']['purchase_order_code'];
				$deletionArray['Deletion']['type']='PurchaseOrder';
				$this->Deletion->save($deletionArray);
						
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la orden de compra número ".$purchaseOrder['PurchaseOrder']['purchase_order_code']);
						
				$this->Session->setFlash(__('Se eliminó la orden de compra.'),'default',['class' => 'success']);				
				return $this->redirect(['action' => 'resumen']);
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar la orden de compra.'), 'default',['class' => 'error-message']);
				return $this->redirect(['action' => 'ver',$id]);
			}
		}
	}

	public function autorizar($id = null) {
		$this->PurchaseOrder->id = $id;
		if (!$this->PurchaseOrder->exists()) {
			throw new NotFoundException(__('Orden de Compra inválida'));
		}
		$this->request->allowMethod('get');
     $this->loadModel('UserPageRight');
	$userRoleId = $this->Auth->User('role_id');

	$loggedUserId=$this->Auth->User('id');
	$canAutorizePurcharse=$this->UserPageRight->hasUserPageRight('AUTORIZAR_COMPRA',$userRoleId,$loggedUserId,'PurchaseOrders','Autorizar');

    $purchaseOrder=$this->PurchaseOrder->find('first',[
      'conditions'=>['PurchaseOrder.id'=>$id],
      'recursive'=>-1,
    ]);
		
    if ($this->Auth->User('role_id') === ROLE_ADMIN || $canAutorizePurcharse){
      $datasource=$this->PurchaseOrder->getDataSource();
      $datasource->begin();
      try {
        //pr($this->request->data);
        $this->PurchaseOrder->id=$id;
        $purchaseOrderArray=[];
        $purchaseOrderArray['PurchaseOrder']['id']=$id;
        $purchaseOrderArray['PurchaseOrder']['bool_authorized']=true;
        $purchaseOrderArray['PurchaseOrder']['purchase_order_state_id']=PURCHASE_ORDER_STATE_AUTHORIZED;
        if (!$this->PurchaseOrder->save($purchaseOrderArray)) {
          echo "Problema al autorizar la orden de compra";
          pr($this->validateErrors($this->PurchaseOrder));
          throw new Exception();
        }
              
        $datasource->commit();
        
        $this->recordUserActivity($this->Session->read('User.username'),"Se autorizó la orden de compra número ".$purchaseOrder['PurchaseOrder']['purchase_order_code']);
        $this->Session->setFlash(__('La orden de compra se autorizó.'),'default',['class' => 'success']);
      }
      catch(Exception $e){
        $this->Session->setFlash(__('La orden de compra no se podía autorizar.'), 'default',['class' => 'error-message']);
      }  
    }
		
		return $this->redirect(['action' => 'resumen']);
	}
  
  public function confirmar($id = null) {
		$this->PurchaseOrder->id = $id;
		if (!$this->PurchaseOrder->exists()) {
			throw new NotFoundException(__('Orden de Compra inválida'));
		}
		$this->request->allowMethod('get');
    
    $purchaseOrder=$this->PurchaseOrder->find('first',[
      'conditions'=>['PurchaseOrder.id'=>$id],
      'recursive'=>-1,
    ]);
		
    $datasource=$this->PurchaseOrder->getDataSource();
    $datasource->begin();
    try {
      //pr($this->request->data);
      $this->PurchaseOrder->id=$id;
      $purchaseOrderArray=[];
      $purchaseOrderArray['PurchaseOrder']['id']=$id;
      $purchaseOrderArray['PurchaseOrder']['purchase_order_state_id']=PURCHASE_ORDER_STATE_CONFIRMED_WITH_CLIENT;
      if (!$this->PurchaseOrder->save($purchaseOrderArray)) {
        echo "Problema al confirmar la orden de compra";
        pr($this->validateErrors($this->PurchaseOrder));
        throw new Exception();
      }
            
      $datasource->commit();
      
      $this->recordUserActivity($this->Session->read('User.username'),"Se confirmó la orden de compra número ".$purchaseOrder['PurchaseOrder']['purchase_order_code']);
      
      $this->Session->setFlash(__('La orden de compra se confirmó.'),'default',['class' => 'success']);
    }
    catch(Exception $e){
      $this->Session->setFlash(__('La orden de compra no se podía confirmar.'), 'default',['class' => 'error-message']);
    }  
		
		return $this->redirect(['action' => 'resumen']);
	}
  
  
  public function anular($id = null) {
		$this->PurchaseOrder->id = $id;
		if (!$this->PurchaseOrder->exists()) {
			throw new NotFoundException(__('Orden de Compra inválida'));
		}
		$this->request->allowMethod('post', 'delete');
		$this->loadModel('SalesOrderProduct');
		
    $purchaseOrder=$this->PurchaseOrder->find('first',[
      'conditions'=>['PurchaseOrder.id'=>$id],
      'recursive'=>-1,
    ]);
    
		$datasource=$this->PurchaseOrder->getDataSource();
		$datasource->begin();
		try {
			//pr($this->request->data);
			
			$this->loadModel('PurchaseOrderProduct');
			$this->PurchaseOrderProduct->recursive=-1;
			$previousPurchaseOrderProducts=$this->PurchaseOrderProduct->find('all',[
				'fields'=>['PurchaseOrderProduct.id'],
				'conditions'=>['PurchaseOrderProduct.purchase_order_id'=>$id,],
			]);
			if (!empty($previousPurchaseOrderProducts)){
				foreach ($previousPurchaseOrderProducts as $previousPurchaseOrderProduct){
					$this->PurchaseOrderProduct->id=$previousPurchaseOrderProduct['PurchaseOrderProduct']['id'];
					if (!$this->PurchaseOrderProduct->delete($previousPurchaseOrderProduct['PurchaseOrderProduct']['id'])){
						echo "Problema al eliminar los productos de la orden de compra";
						pr($this->validateErrors($this->PurchaseOrderProduct));
						throw new Exception();
					}
				}
			}
			
			$this->PurchaseOrder->id=$id;
			$purchaseOrderArray=[];
			$purchaseOrderArray['PurchaseOrder']['id']=$id;
			$purchaseOrderArray['PurchaseOrder']['bool_annulled']=true;
			$purchaseOrderArray['PurchaseOrder']['cost_subtotal']=0;
			$purchaseOrderArray['PurchaseOrder']['cost_iva']=0;
			$purchaseOrderArray['PurchaseOrder']['cost_total']=0;
			if (!$this->PurchaseOrder->save($purchaseOrderArray)) {
				echo "Problema al anular la orden de compra";
				pr($this->validateErrors($this->PurchaseOrder));
				throw new Exception();
			}
						
			$datasource->commit();
      
      $this->recordUserActivity($this->Session->read('User.username'),"Se anuló la orden de compra número ".$purchaseOrder['PurchaseOrder']['purchase_order_code']);
      
			$this->Session->setFlash(__('La orden de compra se anuló.'),'default',['class' => 'success']);
		}
		catch(Exception $e){
			$this->Session->setFlash(__('La orden de compra no se podía anular.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}

  public function verProveedoresPorPagar() {
		$this->loadModel('ExchangeRate');
		$this->loadModel('ThirdParty');
    
    $this->PurchaseOrder->recursive = -1;
    $this->ThirdParty->recursive=-1;
    
		$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
		$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
		
    $providers=$this->ThirdParty->find('all',[
			'fields'=>[
				'ThirdParty.company_name','ThirdParty.id',
			],
			'conditions'=>[
				'bool_provider'=>true,
				'bool_active'=>true,
			],
			'order'=>'ThirdParty.company_name',
		]);
		//pr($providers);
		
		for ($p=0;$p<count($providers);$p++){
			$pendingPurchaseOrders=$this->PurchaseOrder->find('all',[
				'fields'=>[
					'PurchaseOrder.id','PurchaseOrder.purchase_order_code',
					'PurchaseOrder.cost_total',
					'PurchaseOrder.purchase_order_date','PurchaseOrder.due_date',
					'PurchaseOrder.provider_id',
				],
				'conditions'=>[
					'PurchaseOrder.bool_annulled'=>false,
					'PurchaseOrder.bool_paid'=>false,
					'PurchaseOrder.provider_id'=>$providers[$p]['ThirdParty']['id'],
				],
        'contain'=>[
          'Currency'=>['fields'=>['abbreviation','id',]],
        ],
				'order'=>'PurchaseOrder.purchase_order_date ASC',
			]);
			
			$totalPending=0;
			$pendingUnder30=0;
			$pendingUnder45=0;
			$pendingUnder60=0;
			$pendingOver60=0;
			for ($po=0;$po<count($pendingPurchaseOrders);$po++){
				$totalForPurchaseOrder=$pendingPurchaseOrders[$po]['PurchaseOrder']['cost_total'];
				$totalForPurchaseOrderCS=$totalForPurchaseOrder;
				if ($pendingPurchaseOrders[$po]['Currency']['id']==CURRENCY_USD){
					$purchaseOrderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingPurchaseOrders[$po]['PurchaseOrder']['purchase_order_date']);
					$exchangeRatePurchaseOrderDate=$purchaseOrderExchangeRate['ExchangeRate']['rate'];
					$totalForPurchaseOrderCS=$totalForPurchaseOrder*$exchangeRatePurchaseOrderDate;
				}
				// get the amount already paid for this purchaseOrder
				//$purchaseOrderPaidAlreadyCS=round($this->PurchaseOrder->getAmountPaidAlreadyCS($pendingPurchaseOrders[$po]['PurchaseOrder']['id']),2);
        $purchaseOrderPaidAlreadyCS=0;
				$pendingForPurchaseOrder=$totalForPurchaseOrderCS-$purchaseOrderPaidAlreadyCS;
				if ($pendingPurchaseOrders[$po]['Currency']['id']==CURRENCY_USD){
					/*
          $this->loadModel('CashReceiptInvoice');
					$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
						'conditions'=>array(
							'CashReceiptInvoice.invoice_id'=>$pendingPurchaseOrders[$po]['PurchaseOrder']['id'],
						),
						'contain'=>array(
							'CashReceipt'=>array(
								'fields'=>array(
									'CashReceipt.id','CashReceipt.receipt_code',
									'CashReceipt.receipt_date',
									'CashReceipt.bool_annulled',
								),
							),
							'Currency'=>array(
								'fields'=>array(
									'Currency.abbreviation','Currency.id',
								),
							),
						),
					));
          $purchaseOrderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingPurchaseOrders[$po]['PurchaseOrder']['purchase_order_date']);
					$exchangeRatePurchaseOrderDate=$purchaseOrderExchangeRate['ExchangeRate']['rate'];
					// add the diferencia cambiaria on the total
					$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
					$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
					$diferenceExchangeRateNowPurchaseOrder=$exchangeRateNow-$exchangeRatePurchaseOrderDate;
					$diferenciaCambiariaTotal=$differenceExchangeRateNowPurchaseOrder*$totalForPurchaseOrder;
					$pendingForPurchaseOrder+=$diferenciaCambiariaTotal;
					// add the diferencia cambiaria on the cashreceipts
					if (!empty($cashReceiptInvoices)){
						for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
							$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
							$exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
							$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
							$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
							$pendingForInvoice-=$differenciaCambiariaPaid;
						}
					}
          */
				}
				
				
				$totalPending+=$pendingForPurchaseOrder;
				$purchaseOrderDate=new DateTime($pendingPurchaseOrders[$po]['PurchaseOrder']['purchase_order_date']);
				$dueDate= new DateTime($pendingPurchaseOrders[$po]['PurchaseOrder']['due_date']);
				$nowDate= new DateTime();
				$daysLate=$nowDate->diff($purchaseOrderDate);
				if ($daysLate->days<31){
					$pendingUnder30+=$pendingForPurchaseOrder;
				}
				else if ($daysLate->days<46){
					$pendingUnder45+=$pendingForPurchaseOrder;
				}
				else if ($daysLate->days<61){
					$pendingUnder60+=$pendingForPurchaseOrder;
				}
				else{
					$pendingOver60+=$pendingForPurchaseOrder;
				}
			}
			$providers[$p]['saldo']=$totalPending;
			$providers[$p]['pendingUnder30']=$pendingUnder30;
			$providers[$p]['pendingUnder45']=$pendingUnder45;
			$providers[$p]['pendingUnder60']=$pendingUnder60;
			$providers[$p]['pendingOver60']=$pendingOver60;
      //$providers[$p]['historicalCredit']=$this->PurchaseOrder->getHistoricalCreditForProvider($providers[$p]['ThirdParty']['id']);
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
    
    $this->PurchaseOrder->recursive = -1;
		$this->ThirdParty->recursive=-1;
    
		$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
		$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
		
		$provider=$this->ThirdParty->find('first',['conditions'=>['ThirdParty.id'=>$providerId]]);
		
		$pendingPurchaseOrders=$this->PurchaseOrder->find('all',[
			'fields'=>[
				'PurchaseOrder.id','PurchaseOrder.purchase_order_code',
				'PurchaseOrder.cost_total',
				'PurchaseOrder.purchase_order_date','PurchaseOrder.due_date',
			],
			'conditions'=>[
				'PurchaseOrder.bool_annulled'=>false,
				'PurchaseOrder.bool_paid'=>false,
				'PurchaseOrder.provider_id'=>$providerId,
			],
      'contain'=>[
        'Currency'=>['fields'=>['abbreviation','id',]],
        'Provider'=>['fields'=>['company_name','id']],
      ],
			'order'=>'PurchaseOrder.purchase_order_date ASC',
		]);
		
		for ($po=0;$po<count($pendingPurchaseOrders);$po++){
			$totalForPurchaseOrder=$pendingPurchaseOrders[$po]['PurchaseOrder']['cost_total'];
			$totalForPurchaseOrderCS=$totalForPurchaseOrder;
			if ($pendingPurchaseOrders[$po]['Currency']['id']==CURRENCY_USD){
				$purchaseOrderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingPurchaseOrders[$po]['PurchaseOrder']['purchase_order_date']);
				$exchangeRatePurchaseOrderDate=$purchaseOrderExchangeRate['ExchangeRate']['rate'];
				$totalForPurchaseOrderCS=$totalForPurchaseOrder*$exchangeRatePurchaseOrderDate;
			}
			/*
			// get the amount already paid for this purchase order
			$paidForPurchaseOrderCS=round($this->PurchaseOrder->getAmountPaidAlreadyCS($pendingPurchaseOrders[$po]['PurchaseOrder']['id']),2);		
			$pendingPurchaseOrders[$po]['PurchaseOrder']['paidCS']=$paidForPurchaseOrderCS;
      */
      $paidForPurchaseOrderCS=0;
			$pendingForPurchaseOrderCS=$totalForPurchaseOrderCS-$paidForPurchaseOrderCS;
      /*
			if ($pendingPurchaseOrders[$po]['PurchaseOrder']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptPurchaseOrder');
				$cashReceiptPurchaseOrders=$this->CashReceiptPurchaseOrder->find('all',array(
					'conditions'=>array(
						'CashReceiptPurchaseOrder.invoice_id'=>$pendingPurchaseOrders[$po]['PurchaseOrder']['id'],
					),
					'contain'=>array(
						'CashReceipt'=>array(
							'fields'=>array(
								'CashReceipt.id','CashReceipt.receipt_code',
								'CashReceipt.receipt_date',
								'CashReceipt.bool_annulled',
							),
						),
						'Currency'=>array(
							'fields'=>array(
								'Currency.abbreviation','Currency.id',
							),
						),
					),
				));
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingPurchaseOrders[$po]['PurchaseOrder']['invoice_date']);
				$exchangeRatePurchaseOrderDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				// add the diferencia cambiaria on the total
				$pourrentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
				$exchangeRateNow=$pourrentExchangeRate['ExchangeRate']['rate'];
				$differenceExchangeRateNowPurchaseOrder=$exchangeRateNow-$exchangeRatePurchaseOrderDate;
				$differenciaCambiariaTotal=$differenceExchangeRateNowPurchaseOrder*$totalForPurchaseOrder;
				$pendingForPurchaseOrderCS+=$differenciaCambiariaTotal;
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($poashReceiptPurchaseOrders)){
					for ($pori=0;$pori<count($poashReceiptPurchaseOrders);$pori++){
						$poashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($poashReceiptPurchaseOrders[$pori]['CashReceipt']['receipt_date']);
						$exchangeRateCashReceiptDate=$poashReceiptExchangeRate['ExchangeRate']['rate'];
						$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
						//if ($invoices[$i]['PurchaseOrder']['id']==159){
						//	echo "payment cash receipt dividing the amount paid by the exchange rate of the day".($poashReceiptPurchaseOrders[$pori]['CashReceiptPurchaseOrder']['payment_credit_CS']/$exchangeRateCashReceiptDate)."!<br/>";
						//	//pr($poashReceiptPurchaseOrders);
						//}
						$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$poashReceiptPurchaseOrders[$pori]['CashReceiptPurchaseOrder']['payment_credit_CS']/$exchangeRateCashReceiptDate;
						$pendingForPurchaseOrderCS-=$differenciaCambiariaPaid;
					}
				}
			}
			*/
      $pendingPurchaseOrders[$po]['PurchaseOrder']['pendingCS']=$pendingForPurchaseOrderCS;
		}
		
		$this->set(compact('pendingPurchaseOrders','provider','exchangeRateCurrent'));
	}
	
	public function guardarFacturasPorPagar($providerName) {
		$exportData=$_SESSION['facturasPorPagar'];
		$this->set(compact('exportData','providerName'));
	}

}