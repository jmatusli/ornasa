<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ProductsController extends AppController {
	public $components = array('Paginator');
	public $helpers = array('PhpExcel'); 
  
  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('getproductcategoryid','getproductpackagingunit','getbagproductid','getmachines','getdefaultcost','getProductCurrentCostAndDefaultPrice','getProductPriceInfo','getRawMaterialId');		
	}
 
	public function getproductcategoryid($productid){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
		$this->autoRender = false;
		if (!$productid){
			throw new NotFoundException(__('No producto seleccionado'));
		}
		if (!$this->Product->exists($productid)) {
			throw new NotFoundException(__('Producto no existe'));
		}
		echo $this->Product->getProductCategoryId($productid);
	}
	
  // 20210202 looked up directly from array in purchase orders
	public function getproductpackagingunit($productId){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
		$this->autoRender = false;
		if (!$productId){
			throw new NotFoundException(__('No producto seleccionado'));
		}
		if (!$this->Product->exists($productId)) {
			throw new NotFoundException(__('Producto no existe'));
		}
		$product=$this->Product->read(null,$productId);
		return intval($product['Product']['packaging_unit']);
	}
  
  public function getbagproductid($productid){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
		$this->autoRender = false;
		if (!$productid){
			throw new NotFoundException(__('No producto seleccionado'));
		}
		if (!$this->Product->exists($productid)) {
			throw new NotFoundException(__('Producto no existe'));
		}
		echo $this->Product->getBagProductId($productid);
	}
	 
	public function getmachines($productId){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
		if (!$productId){
			throw new NotFoundException(__('Identificador de producto no está presente'));
		}
		if (!$this->Product->exists($productId)) {
			throw new NotFoundException(__('Producto inválido'));
		}
			
		$this->loadModel('MachineProduct');
		$this->loadModel('Machine');
		
		$machineIdList=$this->MachineProduct->find('list',[
			'fields'=>'MachineProduct.machine_id',
			'conditions'=>[
				'MachineProduct.product_id'=>$productId,
			],
		]);
		
		$machineList=$this->Machine->find('list',[
			'conditions'=>[
				'Machine.id'=>$machineIdList,
				'Machine.bool_active'=>true,
      ],
			'order'=>'Machine.name',
		]);
		//pr($machineList);
		
		$this->set(compact('machineList'));
	} 
	
// 20210202 looked up directly from array in purchase orders  
	public function getdefaultcost(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
    $this->autoRender = false;
		
    $productId=trim($_POST['productId']);
		$currencyId=trim($_POST['currencyId']);
		
    $defaultCost=0;
    
		$product=$this->Product->find('first',[
			'fields'=>['Product.default_cost'],
			'conditions'=>[
				'Product.id'=>$productId,
        'Product.default_cost_currency_id'=>$currencyId,
			],
		]);
		
		if (!empty($product)){
      $defaultCost=$product['Product']['default_cost'];
    }
		return $defaultCost;
	} 
	
  public function getProductCurrentCostAndDefaultPrice(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
    $this->autoRender = false;
		
    $selectedDay=trim($_POST['selectedDay']);
    $selectedMonth=trim($_POST['selectedMonth']);
    $selectedYear=trim($_POST['selectedYear']);
    
    $productId=trim($_POST['productId']);
    $rawMaterialId=trim($_POST['rawMaterialId']);
    
    $clientId=trim($_POST['clientId']);
    
    $warehouseId=trim($_POST['warehouseId']);
    
    $selectedDateString=$selectedYear.'-'.$selectedMonth.'-'.$selectedDay;
		$selectedDate=date( "Y-m-d", strtotime($selectedDateString));
    
    $this->loadModel('StockItem');
    $productInventory=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$selectedDate,$warehouseId,$rawMaterialId);
    //pr($productInventory);
    
    $result=[
      'product_name'=>$productInventory['Product']['name'],
    ];
    if ($productInventory['ProductType']['product_category_id'] == CATEGORY_PRODUCED){
      $result['raw_material_name']=$productInventory['raw_material_name'];
      $result['production_result_code']=$productInventory['production_result_code'];
    }
    
    $productCost=0;
    if ($productInventory['quantity'] > 0){
      $productCost=round($productInventory['value']/$productInventory['quantity'],4);
    }
    $result['product_cost']=$productCost;
  
		$productPriceForClient=$this->Product->ProductPriceLog->getApplicableProductPriceForClient($productId,$rawMaterialId,$clientId,$selectedDate);
    $result['product_price']=$productPriceForClient;
    return json_encode($result);
	} 
	
  public function getProductPriceInfo(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
    $this->autoRender = false;
		
    $selectedDay=trim($_POST['selectedDay']);
    $selectedMonth=trim($_POST['selectedMonth']);
    $selectedYear=trim($_POST['selectedYear']);
    
    $productId=trim($_POST['productId']);
    $rawMaterialId=trim($_POST['rawMaterialId']);
    
    $clientId=trim($_POST['clientId']);
    
    $warehouseId=trim($_POST['warehouseId']);
    
    $selectedDateString=$selectedYear.'-'.$selectedMonth.'-'.$selectedDay;
		$selectedDate=date( "Y-m-d", strtotime($selectedDateString));
    
    $result=[];
    
    $this->loadModel('ProductThresholdVolume');
    if ($rawMaterialId==0){
      $productThresholdVolume=$this->ProductThresholdVolume->getThresholdVolume($productId,PRICE_CLIENT_CATEGORY_VOLUME,$selectedDate);
    }
    else {
      $productThresholdVolume=$this->ProductThresholdVolume->getCompositeThresholdVolume($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_VOLUME,$selectedDate);
    }
    $result['threshold_volume']=$productThresholdVolume;
    
    $this->loadModel('StockItem');
    $productInventory=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$selectedDate,$warehouseId,$rawMaterialId);
    //pr($productInventory);
    
    $result['product_name']=$productInventory['Product']['name'];
    
    if ($productInventory['ProductType']['product_category_id'] == CATEGORY_PRODUCED){
      $result['raw_material_name']=$productInventory['raw_material_name'];
      $result['production_result_code']=$productInventory['production_result_code'];
    }
    
    $productCost=0;
    if ($productInventory['quantity'] > 0){
      $productCost=round($productInventory['value']/$productInventory['quantity'],2);
    }
    $result['product_cost']=$productCost;
  
		$productPricesClient=$this->Product->ProductPriceLog->getClientAndCategoryPricesForClient($productId,$rawMaterialId,$clientId,$selectedDate);
    $result['product_prices']=$productPricesClient;
    
    return json_encode($result);
	} 
	
  public function getRawMaterialId($productid){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
		$this->autoRender = false;
		if (!$productid){
			throw new NotFoundException(__('No producto seleccionado'));
		}
		if (!$this->Product->exists($productid)) {
			throw new NotFoundException(__('Producto no existe'));
		}
		echo $this->Product->getPreferredRawMaterialId($productid);
	}
	
	
	 public function getTransferable($productid){
		//$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
		$this->autoRender = false;
		$this->loadModel('ProductTransferable');
        
		if (!$productid){
			throw new NotFoundException(__(' producto no seleccionado'));
		}
		echo json_encode($this->ProductTransferable->getTransferableTo($productid));
 
	}
  
  public function index() {
    $this->loadModel('ProductNature');
    $this->loadModel('ProductType');
    $this->loadModel('ProductCategory');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    $this->loadModel('WarehouseProduct');
    
    $this->loadModel('ProductionType');
    $this->loadModel('ProductionTypeProduct');
    
    $this->Product->recursive = -1;
    
		$loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));

    $productNatureId=0;
    $productCategoryId=0;
    $warehouseId=0;
    $productionTypeId=0;
    
    if ($this->request->is('post')) {
			//$productCategoryId=$this->request->data['Report']['product_category_id'];
      $productNatureId=$this->request->data['Report']['product_nature_id'];
      $warehouseId=$this->request->data['Report']['warehouse_id'];
      $productionTypeId=$this->request->data['Report']['production_type_id'];
		}		
    $this->set(compact('productCategoryId'));
		$this->set(compact('productNatureId'));
    $this->set(compact('productionTypeId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    
    //pr($warehouses);
    $this->set(compact('productspref'));
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
    
    /*
    if ($productCategoryId>0){
      $productTypeIds=$this->ProductType->find('list',[
        'fields'=>['ProductType.id'],
        'conditions'=>['ProductType.product_category_id'=>$productCategoryId],
      ]);
      $productConditions['Product.product_type_id']=$productTypeIds;
    }
		*/
    
    $productNatureConditions=[];
    if ($productNatureId > 0){
      $productNatureConditions['ProductNature.id']=$productNatureId;
    }
   
    if ($warehouseId > 0){
      $warehouseProductIds=$this->WarehouseProduct->getProductIdsForWarehouse($warehouseId);
    }
    if ($productionTypeId > 0){
      $productionTypeProductIds=$this->ProductionTypeProduct->find('list',[
        'fields'=>['ProductionTypeProduct.product_id'],
        'conditions'=>[
          'ProductionTypeProduct.production_type_id'=>$productionTypeId,
        ],
      ]);
    }
    
    $productsByNature=$this->ProductNature->find('all',[
      'conditions'=>$productNatureConditions,
      'recursive'=>-1,
      'order'=>['ProductNature.list_order ASC','ProductNature.name ASC'],
    ]);
    //pr($productsByNature);
    for ($pn=0;$pn<count($productsByNature);$pn++){
      $productConditions=[
        'Product.product_nature_id'=>$productsByNature[$pn]['ProductNature']['id'],
      ];
      if ($warehouseId > 0){
        $productConditions['Product.id']=$warehouseProductIds;
      }
      if ($productionTypeId > 0){
        $productConditions['Product.id']=$productionTypeProductIds;
      }
      $productCount=	$this->Product->find('count', [
        'fields'=>['Product.id'],
        'conditions'=>$productConditions,
      ]);
      $products=$this->Product->find('all',[
        'conditions'=>$productConditions, 
        'order' => ['ProductType.name'=>'ASC','Product.name'=> 'ASC'],
        //'order' => ['Product.product_type_id'=>'ASC','Product.name'=> 'ASC'],
        'limit'=>$productCount, 
        'contain'=>[
          'ProductType'=>['ProductCategory',],
          'AccountingCode',
          'ProductProduction'=>[
            'order'=>'ProductProduction.application_date DESC, ProductProduction.id DESC'
          ],
          'PreferredRawMaterial',
          'BagProduct',
        ],
      ]);
      $productsByNature[$pn]['Products']=$products;
    }    
    //pr($productsByNature);
    $this->set(compact('productsByNature'));
		
    /*
    $productCategories=$this->ProductCategory->find('list');
    $this->set(compact('productCategories'));
    */
    $productionNatures=$this->ProductNature->getProductNatureList();
    $this->set(compact('productionNatures'));
    $productionTypes=$this->ProductionType->getProductionTypes();
    $this->set(compact('productionTypes'));
    
    if ($productNatureId === 0){
      $productConditions=[
        'Product.product_nature_id'=>null,
      ];
      if ($warehouseId > 0){
        $productConditions['Product.id']=$warehouseProductIds;
      }
      if ($productionTypeId > 0){
        $productConditions['Product.id']=$productionTypeProductIds;
      }
      $productCount=	$this->Product->find('count', [
        'fields'=>['Product.id'],
        'conditions'=>$productConditions,
      ]);
      $productsWithoutNature=$this->Product->find('all',[
        'conditions'=>$productConditions, 
        'order' => ['ProductType.name'=>'ASC','Product.name'=> 'ASC'],
        'limit'=>$productCount, 
        'contain'=>[
          'ProductType'=>['ProductCategory',],
          'AccountingCode',
          'ProductProduction'=>[
            'order'=>'ProductProduction.application_date DESC, ProductProduction.id DESC'
          ],
          'PreferredRawMaterial',
          'BagProduct',
        ],
      ]);
      $this->set(compact('productsWithoutNature'));
    }
    
		$aco_name="ProductNatures/resumen";		
		$bool_productnature_resumen_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productnature_resumen_permission'));
		$aco_name="ProductNatures/crear";		
		$bool_productnature_crear_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productnature_crear_permission'));
    
    $aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
	}

	public function view($id = null) {
    if (!$this->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
		
    $this->loadModel('Order');
    $this->loadModel('StockMovement');
    $this->loadModel('ProductionRun');
    
    $this->loadModel('ProductionType');
    $this->loadModel('ProductionTypeProduct');
    
    $this->loadModel('UserWarehouse');
    $this->loadModel('Warehouse');
    $this->loadModel('WarehouseProduct');
    
    $this->loadModel('ProductPriceLog');
    $this->loadModel('PriceClientCategory');

    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
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
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->Set(compact('startDate','endDate'));
    $this->Set(compact('id'));
		
    $product=$this->Product->find('first', [
			'conditions' => ['Product.id'=> $id,],
			'contain'=>[
        'Unit',
				'ProductType'=>['ProductCategory'],
        'ProductNature',
        'ProductionType',
        'AccountingCode',
        
        'ProductThresholdVolume'=>[
          'limit'=>1,
          'order'=>'volume_datetime DESC',
        ],
				
        'ProductProduction'=>[
					'order'=>'ProductProduction.application_date DESC, ProductProduction.id DESC'
				],
        
        'PreferredRawMaterial',
        'BagProduct',
        
        'DefaultCostCurrency',
				'ProductionMovement'=>[
					'conditions'=>[
						'ProductionMovement.movement_date >='=>$startDate,
						'ProductionMovement.movement_date <'=>$endDatePlusOne,
					],
					'order'=>'ProductionMovement.movement_date DESC',
				],
        'StockMovement'=>[
					'conditions'=>[
						'StockMovement.movement_date >='=>$startDate,
						'StockMovement.movement_date <'=>$endDatePlusOne,
					],
					'order'=>'StockMovement.movement_date DESC, StockMovement.reclassification_code DESC',
				],
			],
		]);
    //pr($product);
    
    if ($product['ProductNature']['id'] == PRODUCT_NATURE_PRODUCED){
      $defaultProductPriceLog=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($id,$product['Product']['preferred_raw_material_id'],PRICE_CLIENT_CATEGORY_GENERAL,date('Y-m-d'));
    }  
    else {
      $defaultProductPriceLog=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($id,PRICE_CLIENT_CATEGORY_GENERAL,date('Y-m-d'));
    }
    $defaultPrice=$defaultProductPriceLog['price'];
    $this->set(compact('defaultPrice'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    
    $this->set(compact('warehouses'));
    $productWarehouses=[];
    foreach ($warehouses as $warehouseId=>$warehouseName){
      if ($this->WarehouseProduct->hasWarehouse($id,$warehouseId)){
        $productWarehouses[$warehouseId]=$warehouseName;
      }
    }
    //pr($productWarehouses);
    $this->set(compact('productWarehouses'));
    
    $priceClientCategories=$this->PriceClientCategory->getPriceClientCategoryList();
    $this->set(compact('priceClientCategories'));
    $priceClientCategoryPrices=[];
    foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
      if ($product['ProductNature']['id'] == PRODUCT_NATURE_PRODUCED){
        $priceClientCategoryPrices[$priceClientCategoryId]=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($id,$product['Product']['preferred_raw_material_id'],$priceClientCategoryId,date('Y-m-d H:i:s'))['price'];
      }
      else {
        $priceClientCategoryPrices[$priceClientCategoryId]=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($id,$priceClientCategoryId,date('Y-m-d H:i:s'))['price'];
      }
    }
    $this->set(compact('priceClientCategoryPrices'));
    
    $productionTypes=$this->ProductionType->getProductionTypes();
    /*
    $productProductionTypes=[];
    foreach ($productionTypes as $productionTypeId=>$productionTypeName){
      if ($this->ProductionTypeProduct->hasProductionType($id,$productionTypeId)){
        $productProductionTypes[$productionTypeId]=$productionTypeName;
      }
    }
    //pr($productProductionTypes);
    $this->set(compact('productProductionTypes'));
    */
    if ($product['ProductType']['ProductCategory']['id']==CATEGORY_PRODUCED && !empty($product['ProductPriceLog'])){
      $rawMaterials=$this->Product->find('list',[
        'fields'=>'Product.name',
        'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_PREFORMA],
        'order'=>['Product.name'],
      ]);
      $rawMaterialArray=[];
      for ($p=0;$p<count($product['ProductPriceLog']);$p++){
        $rawMaterialId=$product['ProductPriceLog'][$p]['raw_material_id'];
        if (!isset($rawMaterialArray[$rawMaterialId]['name'])){
          $rawMaterialArray[$rawMaterialId]['name']=$rawMaterials[$rawMaterialId];
          $rawMaterialArray[$rawMaterialId]['latest_price']=$this->Product->ProductPriceLog->getLatestPrice($rawMaterialId);
          $rawMaterialArray[$rawMaterialId]['ProductPriceLog']=[];
        }
        $rawMaterialArray[$rawMaterialId]['ProductPriceLog'][]=$product['ProductPriceLog'][$p];
        uasort($rawMaterialArray,[$this,'sortByRawMaterial']);  
      }
      $product['ProductPriceLog']['RawMaterials']=$rawMaterialArray;
    }  
    //pr($product);
    
    $orderIds=[];
    $productionRunIds=[];
		for ($i=0;$i<count($product['StockMovement']);$i++){
			$linkedOrder=$this->Order->read(null,$product['StockMovement'][$i]['order_id']);
			$product['StockMovement'][$i]['order_code']=$linkedOrder['Order']['order_code'];
      if (!in_array($product['StockMovement'][$i]['order_id'],$orderIds)){
        $orderIds[]=$product['StockMovement'][$i]['order_id'];
      }
      //if ($product['StockMovement'][$i]['bool_reclassification']){
      //    $originStockMovement=$this->StockMovement->find('first',[
      //      'conditions'=>['StockMovement.id'=>$product['StockMovement'][$i]['origin_stock_movement_id']],
      //      'contain'=>['Product'],
      //    ]);
      //    if (!empty($originStockMovement)){
      //      $product['StockMovement'][$i]['Origin']=$originStockMovement;
      //    }
      //}
      
		}
		for ($i=0;$i<count($product['ProductionMovement']);$i++){
			$linkedProductionRun=$this->ProductionRun->read(null,$product['ProductionMovement'][$i]['production_run_id']);
			$product['ProductionMovement'][$i]['production_run_code']=$linkedProductionRun['ProductionRun']['production_run_code'];
      if (!in_array($product['ProductionMovement'][$i]['production_run_id'],$productionRunIds)){
        $productionRunIds[]=$product['ProductionMovement'][$i]['production_run_id'];
      }
		}
		$this->set(compact('product'));
    //pr($product);
    
    $ordersForProductInPeriod=$this->Order->find('all',[
      'conditions'=>['Order.id'=>$orderIds],
      'contain'=>[
        'StockMovement'=>[
          'conditions'=>[
            'StockMovement.product_id'=>$id,
            'StockMovement.bool_reclassification'=>false
          ]
        ],
        'ThirdParty',
      ],
      'order'=>'order_date DESC'
    ]);
    
    $this->set(compact('ordersForProductInPeriod'));
    //pr($productionRunIds);
    $productionRunsForProductInPeriod=$this->ProductionRun->find('all',[
      'conditions'=>['ProductionRun.id'=>$productionRunIds],
      'contain'=>[
        'ProductionMovement'=>[
          'conditions'=>[
            'ProductionMovement.product_id'=>$id,
          ],
          'order'=>'ProductionMovement.production_result_code_id'
        ]
      ],
      'order'=>'production_run_date DESC'
    ]);
    $this->set(compact('productionRunsForProductInPeriod'));
    
    $originReclassificationsCaps=$this->StockMovement->find('all',array(
			'fields'=>array('StockMovement.id','Product.id','Product.name','StockMovement.product_quantity','StockMovement.movement_date','StockMovement.reclassification_code'),
			'conditions'=>array(
				'StockMovement.movement_date >='=>$startDate,
				'StockMovement.movement_date <'=>$endDatePlusOne,
				'StockMovement.bool_reclassification'=>true,
				'Product.product_type_id'=>PRODUCT_TYPE_CAP,
				'StockMovement.bool_input'=>'0',
			),
			'order'=>'DATE(StockMovement.movement_date) DESC, StockMovement.reclassification_code DESC',
		));
		
		$reclassificationsCaps=[];
		
		for ($i=0;$i<count($originReclassificationsCaps);$i++){
			$destinationMovement=$this->StockMovement->find('first',array(
				'fields'=>array('StockMovement.id','Product.id','Product.name','StockMovement.product_quantity','StockMovement.movement_date','StockMovement.comment'),
				'conditions'=>array(
					'StockMovement.movement_date >='=>$startDate,
					'StockMovement.movement_date <'=>$endDatePlusOne,
					'StockMovement.bool_reclassification'=>true,
					'Product.product_type_id'=>PRODUCT_TYPE_CAP,
					'StockMovement.bool_input'=>true,
					'StockMovement.origin_stock_movement_id'=>$originReclassificationsCaps[$i]['StockMovement']['id'],
				),
			));
			if (!empty($destinationMovement)){
				$reclassificationsCaps[$i]['movement_date']=$originReclassificationsCaps[$i]['StockMovement']['movement_date'];
				$reclassificationsCaps[$i]['reclassification_code']=$originReclassificationsCaps[$i]['StockMovement']['reclassification_code'];
				$reclassificationsCaps[$i]['origin_product_id']=$originReclassificationsCaps[$i]['Product']['id'];
				$reclassificationsCaps[$i]['origin_product_name']=$originReclassificationsCaps[$i]['Product']['name'];
				$reclassificationsCaps[$i]['origin_product_quantity']=$originReclassificationsCaps[$i]['StockMovement']['product_quantity'];
				$reclassificationsCaps[$i]['destination_product_id']=$destinationMovement['Product']['id'];
				$reclassificationsCaps[$i]['destination_product_name']=$destinationMovement['Product']['name'];
				$reclassificationsCaps[$i]['destination_product_quantity']=$destinationMovement['StockMovement']['product_quantity'];
        $reclassificationsCaps[$i]['comment']=$destinationMovement['StockMovement']['comment'];
			}
		}
		
		$originReclassificationsBottles=$this->StockMovement->find('all',array(
			'fields'=>array('StockMovement.id','StockMovement.product_quantity','StockMovement.movement_date','StockMovement.reclassification_code','StockMovement.comment'),
			'conditions'=>array(
				'StockMovement.movement_date >='=>$startDate,
				'StockMovement.movement_date <'=>$endDatePlusOne,
				'StockMovement.bool_reclassification'=>true,
				'StockMovement.bool_input'=>'0',
				
			),
			'contain'=>array(
				'StockItem'=>array(
					'fields'=>array('id','raw_material_id'),
					'RawMaterial',
				),
				'Product'=>array(
					'fields'=>array('id','name'),
					'conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE),
				),
				'ProductionResultCode'=>array(
					'fields'=>array('code')
				),
			),
			'order'=>'DATE(StockMovement.movement_date) DESC, StockMovement.reclassification_code DESC, Product.name ASC, ProductionResultCode.code',
		));
		
		$reclassificationsBottles=array();
		
		for ($i=0;$i<count($originReclassificationsBottles);$i++){
			$destinationMovement=$this->StockMovement->find('first',array(
				'fields'=>array('StockMovement.id','StockMovement.product_quantity','StockMovement.comment'),
				'conditions'=>array(
					'StockMovement.movement_date >='=>$startDate,
					'StockMovement.movement_date <'=>$endDatePlusOne,
					'StockMovement.bool_reclassification'=>true,
					'Product.product_type_id'=>PRODUCT_TYPE_BOTTLE,
					'StockMovement.bool_input'=>true,
					'StockMovement.origin_stock_movement_id'=>$originReclassificationsBottles[$i]['StockMovement']['id'],
				),
				'contain'=>array(
					'StockItem'=>array(
						'fields'=>array('id','raw_material_id'),
						'RawMaterial',
					),
					'Product'=>array(
						'fields'=>array('id','name'),
						'conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE),
					),
					'ProductionResultCode'=>array(
						'fields'=>array('code')
					),
				),
			));
			if (!empty($destinationMovement)){
				//pr($originReclassificationsBottles[$i]);
				$reclassificationsBottles[$i]['movement_date']=$originReclassificationsBottles[$i]['StockMovement']['movement_date'];
				$reclassificationsBottles[$i]['reclassification_code']=$originReclassificationsBottles[$i]['StockMovement']['reclassification_code'];
				$reclassificationsBottles[$i]['origin_product_id']=$originReclassificationsBottles[$i]['Product']['id'];
				$reclassificationsBottles[$i]['origin_product_name']=$originReclassificationsBottles[$i]['Product']['name'];
				$reclassificationsBottles[$i]['origin_product_quantity']=$originReclassificationsBottles[$i]['StockMovement']['product_quantity'];
				$reclassificationsBottles[$i]['origin_production_result_code']=$originReclassificationsBottles[$i]['ProductionResultCode']['code'];
				$reclassificationsBottles[$i]['origin_raw_material_id']=$originReclassificationsBottles[$i]['StockItem']['RawMaterial']['id'];
				$reclassificationsBottles[$i]['origin_raw_material_name']=$originReclassificationsBottles[$i]['StockItem']['RawMaterial']['name'];
				$reclassificationsBottles[$i]['destination_product_id']=$destinationMovement['Product']['id'];
				$reclassificationsBottles[$i]['destination_product_name']=$destinationMovement['Product']['name'];
				$reclassificationsBottles[$i]['destination_product_quantity']=$destinationMovement['StockMovement']['product_quantity'];
        $reclassificationsBottles[$i]['comment']=$destinationMovement['StockMovement']['comment'];
				$reclassificationsBottles[$i]['destination_production_result_code']=$destinationMovement['ProductionResultCode']['code'];
				$reclassificationsBottles[$i]['destination_raw_material_id']=$destinationMovement['StockItem']['RawMaterial']['id'];
				$reclassificationsBottles[$i]['destination_raw_material_name']=$destinationMovement['StockItem']['RawMaterial']['name'];
			}
		}
		
		$reclassificationsPreformas=array();
		
		for ($i=0;$i<count($originReclassificationsBottles);$i++){
			$destinationMovement=$this->StockMovement->find('first',array(
				'fields'=>array('StockMovement.id','Product.id','Product.name','StockMovement.product_quantity','StockMovement.comment'),
				'conditions'=>array(
					'StockMovement.movement_date >='=>$startDate,
					'StockMovement.movement_date <'=>$endDatePlusOne,
					'StockMovement.bool_reclassification'=>true,
					'Product.product_type_id'=>PRODUCT_TYPE_PREFORMA,
					'StockMovement.bool_input'=>true,
					'StockMovement.origin_stock_movement_id'=>$originReclassificationsBottles[$i]['StockMovement']['id'],
				),
				'order'=>'DATE(StockMovement.movement_date) DESC,StockMovement.reclassification_code',
			));
			if (!empty($destinationMovement)){
				$reclassificationsPreformas[$i]['movement_date']=$originReclassificationsBottles[$i]['StockMovement']['movement_date'];
				$reclassificationsPreformas[$i]['reclassification_code']=$originReclassificationsBottles[$i]['StockMovement']['reclassification_code'];
				$reclassificationsPreformas[$i]['origin_product_id']=$originReclassificationsBottles[$i]['Product']['id'];
				$reclassificationsPreformas[$i]['origin_product_name']=$originReclassificationsBottles[$i]['Product']['name'];
				$reclassificationsPreformas[$i]['origin_product_quantity']=$originReclassificationsBottles[$i]['StockMovement']['product_quantity'];
				$reclassificationsPreformas[$i]['origin_production_result_code']=$originReclassificationsBottles[$i]['ProductionResultCode']['code'];
				$reclassificationsPreformas[$i]['origin_raw_material_id']=$originReclassificationsBottles[$i]['StockItem']['RawMaterial']['id'];
				$reclassificationsPreformas[$i]['origin_raw_material_name']=$originReclassificationsBottles[$i]['StockItem']['RawMaterial']['name'];$reclassificationsPreformas[$i]['destination_product_id']=$destinationMovement['Product']['id'];
				$reclassificationsPreformas[$i]['destination_product_id']=$destinationMovement['Product']['id'];
				$reclassificationsPreformas[$i]['destination_product_name']=$destinationMovement['Product']['name'];
				$reclassificationsPreformas[$i]['destination_product_quantity']=$destinationMovement['StockMovement']['product_quantity'];
        $reclassificationsPreformas[$i]['comment']=$destinationMovement['StockMovement']['comment'];
			}
		}
		//pr($reclassificationsCaps);
		$this->set(compact('reclassificationsCaps','reclassificationsBottles','reclassificationsPreformas','startDate','endDate'));
		
		$aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
	}

	public function add() {
    $this->loadModel('AccountingCode');  
    $this->loadModel('Currency');
    
    $this->loadModel('Unit');
    $this->loadModel('ProductNature');
    
    $this->loadModel('PriceClientCategory');
    $this->loadModel('ProductPriceLog');
    $this->loadModel('ProductThresholdVolume');
    $this->loadModel('ProductProduction');
		
    $this->loadModel('ProductionType');
    $this->loadModel('ProductionTypeProduct');
    
    $this->loadModel('Machine');
    $this->loadModel('MachineProduct');
    
    $this->loadModel('Recipe');
    
    $this->loadModel('UserWarehouse');
    $this->loadModel('Warehouse');
    $this->loadModel('WarehouseProduct');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    
    $productionTypes=$this->ProductionType->getProductionTypes();
    $this->set(compact('productionTypes'));
    
    $machines=$this->Machine->getMachineList();
    $this->set(compact('machines'));
    
    $priceClientCategories=$this->PriceClientCategory->getPriceClientCategoryList();
    $this->set(compact('priceClientCategories'));
    //pr($priceClientCategories);
    
		if ($this->request->is('post')) {
      //pr($this->request->data);
      
			$previousProductsWithThisName=[];
			$previousProductsWithThisName=$this->Product->find('all',[
				'conditions'=>[
					'TRIM(LOWER(Product.name))'=>trim(strtolower($this->request->data['Product']['name'])),
				],
			]);
			
      $warehouseAssigned='0';
      foreach ($this->request->data['Warehouse'] as $warehouseId=>$warehouseData){
        $warehouseAssigned = $warehouseAssigned || $warehouseData['bool_assigned'];
      }
      //$productionTypeAssigned='0';
      //foreach ($this->request->data['ProductionType'] as $productionTypeId=>$productionTypeData){
      //  $productionTypeAssigned = $productionTypeAssigned || $productionTypeData['bool_assigned']; 
      //}
      $machineAssigned='0';
      foreach ($this->request->data['MachineProduct'] as $machineId=>$machineData){
        $machineAssigned = $machineAssigned || $machineData['bool_assigned']; 
      }
      
      if (count($previousProductsWithThisName)>0){
				$this->Session->setFlash('Ya se introdujo un producto con este nombre!  No se guardó el producto.', 'default',['class' => 'error-message']);
			}
      elseif (empty($this->request->data['Product']['product_type_id'])){
        $this->Session->setFlash('Se debe especificar el tipo de producto.  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Product']['product_nature_id']) && $this->request->data['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
        $this->Session->setFlash('Se debe especificar la naturaleza de producto.  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Product']['production_type_id']) && in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_RAW])){
        $this->Session->setFlash('El producto se tiene que asociar con un tipo de producción.', 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['PriceClientCategory'][PRICE_CLIENT_CATEGORY_GENERAL]['category_price'] <= 0  && in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_BOTTLES_BOUGHT,PRODUCT_NATURE_ACCESORIES])){
        $this->Session->setFlash('El precio de venta tiene que ser mayor que cero!  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Product']['threshold_volume'] <= 0  && in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_BOTTLES_BOUGHT,PRODUCT_NATURE_ACCESORIES])){
        $this->Session->setFlash('El volumen de venta tiene que ser mayor que cero!  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
      elseif (!$warehouseAssigned){
        $this->Session->setFlash('El producto se tiene que asignar a por lo menos una bodega.', 'default',['class' => 'error-message']);
      }
      //elseif (!$productionTypeAssigned && in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_RAW])){
      //  $this->Session->setFlash('El producto se tiene que asignar a por lo menos un tipo de producción.', 'default',['class' => 'error-message']);
      //}
      //elseif (!$machineAssigned && in_array($this->request->data['Product']['product_type_id'],[PRODUCT_TYPE_BOTTLE])){
      //  $this->Session->setFlash('La botella tiene que asignarse a por lo menos una máquina.', 'default',['class' => 'error-message']);
      //}
      elseif (empty($this->request->data['Product']['preferred_raw_material_id']) && $this->request->data['Product']['product_type_id'] == PRODUCT_TYPE_BOTTLE){
        $this->Session->setFlash('Para productos de tipo botella se debe indicar la materia prima preferida!  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
			else {
        $successMessage='Se guardó el producto.  ';
        
				$datasource=$this->Product->getDataSource();
				$datasource->begin();
				try {
					$this->Product->create();
					if (!$this->Product->save($this->request->data)) {
						echo "problema al guardar el producto";
						pr($this->validateErrors($this->Product));
						throw new Exception();
					} 
					$productId=$this->Product->id;
          
          foreach ($this->request->data['Warehouse'] as $warehouseId=>$warehouseData){
            $warehouseProductArray=[
              'WarehouseProduct'=>[
                'assignment_datetime'=>date('Y-m-d H:i:s'),
                'warehouse_id'=>$warehouseId,
                'product_id'=>$productId,
                'bool_assigned'=>$warehouseData['bool_assigned'],
              ]
            ];  
            $this->WarehouseProduct->create();
            if (!$this->WarehouseProduct->save($warehouseProductArray)) {
              echo "Problema guardando las asociaciones del producto ".$productId.' con bodega '.$warehouseId;
              pr($this->validateErrors($this->WarehouseProduct));
              throw new Exception();
            }   
          }
          if (in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_RAW])){
          /*
            if (!empty($this->request->data['ProductionType'])){
              foreach ($this->request->data['ProductionType'] as $productionTypeId=>$productionTypeData){
                $productionTypeProductArray=[
                  'ProductionTypeProduct'=>[
                    'assignment_datetime'=>date('Y-m-d H:i:s'),
                    'production_type_id'=>$productionTypeId,
                    'product_id'=>$productId,
                    'bool_assigned'=>$productionTypeData['bool_assigned'],
                  ]
                ];  
                $this->ProductionTypeProduct->create();
                if (!$this->ProductionTypeProduct->save($productionTypeProductArray)) {
                  echo "Problema guardando las asociaciones del producto ".$productId.' con bodega '.$productionTypeId;
                  pr($this->validateErrors($this->ProductionTypeProduct));
                  throw new Exception();
                }   
              }
            }
          */
          }
					
					if ($this->request->data['Product']['product_nature_id'] == PRODUCT_NATURE_PRODUCED){
            foreach ($this->request->data['PriceClientCategory'] as $priceClientCategoryId=>$priceClientCategoryData){
              $productPriceData=[
                'ProductPriceLog'=>[
                  'price_datetime'=>date('Y-m-d H:i:s'),
                  'product_id'=>$productId,
                  
                  'raw_material_id'=>$this->request->data['Product']['preferred_raw_material_id'],
                  'price_client_category_id'=>$priceClientCategoryId,
                  'user_id'=>$loggedUserId,
                  'price'=>$priceClientCategoryData['category_price'],
                  'currency_id'=>CURRENCY_CS,
                ],
              ];
              $this->ProductPriceLog->create();
              if (!$this->ProductPriceLog->save($productPriceData)) {
                echo "Problema guardando el precio del producto ".$productId.' para categoría '.$priceClientCategoryId;
                pr($this->validateErrors($this->ProductPriceLog));
                throw new Exception();
              }   
            }
            
            $productThresholdVolumeArray=[
              'ProductThresholdVolume'=>[
                'volume_datetime'=>date('Y-m-d H:i:s'),
                'product_id'=>$productId,
                'raw_material_id'=>$this->request->data['Product']['preferred_raw_material_id'],
                'price_client_category_id'=>PRICE_CLIENT_CATEGORY_VOLUME,
                'threshold_volume'=>$this->request->data['Product']['threshold_volume'],
                'user_id'=>$loggedUserId,
              ],
            ];
            $this->ProductThresholdVolume->create();
						if (!$this->ProductThresholdVolume->save($productThresholdVolumeArray)) {
							echo "problema al guardar el volumen de ventas para el producto";
							pr($this->validateErrors($this->ProductThresholdVolume));
							throw new Exception();
						}  
						
            $productProductionArray=[];
						$productProductionArray['ProductProduction']['application_date']=date('Y-m-d');
						$productProductionArray['ProductProduction']['product_id']=$productId;
						$productProductionArray['ProductProduction']['acceptable_production']=$this->request->data['ProductProduction']['acceptable_production'];
						$this->ProductProduction->create();
						if (!$this->ProductProduction->save($productProductionArray)) {
							echo "problema al guardar la producción aceptable para el producto";
							pr($this->validateErrors($this->ProductProduction));
							throw new Exception();
						}
            if (!empty($this->request->data['MachineProduct'])){
              foreach ($this->request->data['MachineProduct'] as $machineId=>$machineData){
                if ($machineData['bool_assigned']){
                  $machineProductArray=[
                    'MachineProduct'=>[
                      'assignment_datetime'=>date('Y-m-d H:i:s'),
                      'bool_assigned'=>$machineData['bool_assigned'],
                      'machine_id'=>$machineId,
                      'product_id'=>$productId,
                    ]
                  ];
                  $this->MachineProduct->create();
                  //pr($machineProductArray);
                  if (!$this->MachineProduct->save($machineProductArray)) {
                    echo "problema al guardar las asociaciones entre maquinas y productos";
                    pr($this->validateErrors($this->MachineProduct));
                    throw new Exception();
                  }                
                }
              }
            }
					
          
            if ($this->request->data['Product']['production_type_id'] == PRODUCTION_TYPE_INJECTION){
              $recipeIdsWithPendingProductId=$this->Recipe->getRecipeIdsWithProductIdPending();
              if (!empty($recipeIdsWithPendingProductId)){
                foreach ($recipeIdsWithPendingProductId as $recipeId=>$recipeCreated){
                  $recipeCreatedDateTime=new Datetime($recipeCreated);
                  $currentDateTime= new Datetime(date('Y-m-d'));
                  if ($recipeCreatedDateTime->format('Y-m-d') == $currentDateTime->format('Y-m-d')){
                    $this->Recipe->id=$recipeId;
                    $recipeArray=[
                      'Recipe'=>[
                        'id'=>$recipeId,
                        'product_id'=>$productId,
                      ],
                    ];
                    if (!$this->Recipe->save($recipeArray)) {
                      echo "problema al guardar la receta para el producto";
                      pr($this->validateErrors($this->Recipe));
                      throw new Exception();
                    }                    
                  }
                  else {
                    $this->Recipe->id=$recipeId;
                    if (!$this->Recipe->delete($recipeId)) {
                      echo "problema eliminando recetas no asociadas con este producto";
                      pr($this->validateErrors($this->Recipe));
                      throw new Exception();
                    }  
                  }
                }  
              }  
            }
          }
          elseif (!in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_RAW,PRODUCT_NATURE_BAGS])){     
            foreach ($this->request->data['PriceClientCategory'] as $priceClientCategoryId=>$priceClientCategoryData){
              $productPriceData=[
                'ProductPriceLog'=>[
                  'price_datetime'=>date('Y-m-d H:i:s'),
                  'product_id'=>$productId,
                  'price_client_category_id'=>$priceClientCategoryId,
                  'user_id'=>$loggedUserId,
                  'price'=>$priceClientCategoryData['category_price'],
                  'currency_id'=>CURRENCY_CS,
                ],
              ];
              $this->ProductPriceLog->create();
              if (!$this->ProductPriceLog->save($productPriceData)) {
                echo "Problema guardando el precio del producto ".$productId.' para categoría '.$priceClientCategoryId;
                pr($this->validateErrors($this->ProductPriceLog));
                throw new Exception();
              }   
            }
            
            $productThresholdVolumeArray=[
              'ProductThresholdVolume'=>[
                'volume_datetime'=>date('Y-m-d H:i:s'),
                'product_id'=>$productId,
                'price_client_category_id'=>PRICE_CLIENT_CATEGORY_VOLUME,
                'threshold_volume'=>$this->request->data['Product']['threshold_volume'],
                'user_id'=>$loggedUserId,
              ],
            ];
            $this->ProductThresholdVolume->create();
						if (!$this->ProductThresholdVolume->save($productThresholdVolumeArray)) {
							echo "problema al guardar el volumen de ventas para el producto";
							pr($this->validateErrors($this->ProductThresholdVolume));
							throw new Exception();
						}  
          }      
          $datasource->commit();
					
					$this->recordUserAction($this->Product->id,null,null);
					$this->Session->setFlash($successMessage,'default',['class' => 'success']);
          
          $aco_name="ProductPriceLogs/registrarPreciosProducto";		
          $boolRegistrarPreciosProductoPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
          
          if (in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_RAW,PRODUCT_NATURE_BAGS]) || !$boolRegistrarPreciosProductoPermission){
            return $this->redirect(['action' => 'index']);  
          }  
          else {
            return $this->redirect(['controller'=>'productPriceLogs','action' => 'registrarPreciosProducto',$productId,2]);  
          }
				} 		
				catch(Exception $e){
					$datasource->rollback();
					pr($e);					
					$this->Session->setFlash(__('The product could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
				}
			}
		}
		
    $productTypes = $this->Product->ProductType->find('list',[
      'order'=>'name',
    ]);
		$preferredRawMaterials=$this->Product->PreferredRawMaterial->find('list',[
			'conditions'=>['product_type_id'=>PRODUCT_TYPE_PREFORMA],
			'order'=>'name ASC',
		]);
		$this->set(compact('productTypes','preferredRawMaterials'));
    
    //$bagConditions=['OR'=>
    //  [
    //    ['product_type_id'=>PRODUCT_TYPE_ROLL],
    //    ['product_type_id'=>PRODUCT_TYPE_CONSUMIBLES],
    //  ],
    //];
    //$bagProducts=$this->Product->find('list',[
		//	'conditions'=>$bagConditions,
		//	'order'=>'name ASC',
		//]);
    $bagProducts=$this->Product->getProductsByProductNature(PRODUCT_NATURE_BAGS);
    $this->set(compact('bagProducts'));
    
		$inventoryAccountingCode=$this->AccountingCode->getAccountingCodeById(ACCOUNTING_CODE_INVENTORY);
    $accountingCodes=$this->AccountingCode->getChildAccountingCodes($inventoryAccountingCode['AccountingCode']['lft'],$inventoryAccountingCode['AccountingCode']['rght']);
		$this->set(compact('accountingCodes'));
    
    $defaultPriceCurrencies=$defaultCostCurrencies=$currencies=$this->Currency->find('list');
    $this->set(compact('defaultPriceCurrencies','defaultCostCurrencies'));
		
    $productNatures=$this->ProductNature->getProductNatureList();
    $this->set(compact('productNatures'));
    
    $units=$this->Unit->getUnitList();
    $this->set(compact('units'));
    
    $injectionProducts = $this->Product->getProductsByProductionType(PRODUCTION_TYPE_INJECTION,PRODUCT_NATURE_PRODUCED);
		$this->set(compact('injectionProducts'));
    
    $injectionRawMaterials = $this->Product->getProductsByProductionType(PRODUCTION_TYPE_INJECTION,PRODUCT_NATURE_RAW);
		$this->set(compact('injectionRawMaterials'));
    //pr($rawMaterials);
    
    $rawMaterialUnits=$this->Product->getProductUnitList(array_keys($injectionRawMaterials));
    $this->set(compact('rawMaterialUnits'));
    
    $injectionConsumables = $this->Product->getProductsByProductNature(PRODUCT_NATURE_BAGS);
		$this->set(compact('injectionConsumables'));
    //pr($injectionConsumables);
    
    $consumableUnits=$this->Product->getProductUnitList(array_keys($injectionConsumables));
    $this->set(compact('consumableUnits'));
    
    $aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
    
    $this->loadModel('AccountingCode');  
    $this->loadModel('Currency');
    
    $this->loadModel('Unit');
    $this->loadModel('ProductNature');
    
    $this->loadModel('PriceClientCategory');
    $this->loadModel('ProductPriceLog');
    $this->loadModel('ProductThresholdVolume');
    $this->loadModel('ProductProduction');
		
    $this->loadModel('ProductionType');
    $this->loadModel('ProductionTypeProduct');
    
    $this->loadModel('Machine');
    $this->loadModel('MachineProduct');
    
    $this->loadModel('Recipe');
    
    $this->loadModel('UserWarehouse');
    $this->loadModel('Warehouse');
    $this->loadModel('WarehouseProduct');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    $productWarehouses=[];
    foreach ($warehouses as $warehouseId=>$warehouseName){
      if ($this->WarehouseProduct->hasWarehouse($id,$warehouseId)){
        $productWarehouses[$warehouseId]=$warehouseName;
      }
    }
    //pr($productWarehouses);
    $this->set(compact('productWarehouses'));
    
    $productionTypes=$this->ProductionType->getProductionTypes();
    $this->set(compact('productionTypes'));
    
    $productProductionTypes=[];
    foreach ($productionTypes as $productionTypeId=>$productionTypeName){
      if ($this->ProductionTypeProduct->hasProductionType($id,$productionTypeId)){
        $productProductionTypes[$productionTypeId]=$productionTypeName;
      }
    }
    //pr($productProductionTypes);
    $this->set(compact('productProductionTypes'));
    
    $machines=$this->Machine->getMachineList();
    $this->set(compact('machines'));
    
    $productMachines=[];
    foreach ($machines as $machineId=>$machineName){
      if ($this->MachineProduct->hasMachine($id,$machineId)){
        $productMachines[$machineId]=$machineName;
      }
    }
    $this->set(compact('productMachines'));
    
    $priceClientCategories=$this->PriceClientCategory->getPriceClientCategoryList();
    $this->set(compact('priceClientCategories'));
    //pr($priceClientCategories);
    
    $productPrices=[];
    foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
      if ($this->Product->getProductTypeId($id) == PRODUCT_TYPE_BOTTLE){
        $productPrices[$priceClientCategoryId]=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($id,$this->Product->getPreferredRawMaterialId($id), $priceClientCategoryId,date('Y-m-d H:i:s'))['price'];
      }
      else {
        $productPrices[$priceClientCategoryId]=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($id,$priceClientCategoryId,date('Y-m-d H:i:s'))['price'];
      }
    }
    $this->set(compact('productPrices'));
    
    $productThresholdVolume=$this->ProductThresholdVolume->getThresholdVolume($id,PRICE_CLIENT_CATEGORY_VOLUME,date('Y-m-d H:i:s'));
    
		if ($this->request->is(['post', 'put'])) {
      /*
      if ($this->request->data['Product']['product_nature_id'] == PRODUCT_NATURE_PRODUCED){
        $defaultProductPriceLog=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($id,$this->request->data['Product']['preferred_raw_material_id'],PRICE_CLIENT_CATEGORY_GENERAL,date('Y-m-d'));
      }  
      else {
        $defaultProductPriceLog=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($id,PRICE_CLIENT_CATEGORY_GENERAL,date('Y-m-d'));
      }
      $defaultPrice=$defaultProductPriceLog['price'];
      // in case something goes wrong, and for price comparison later on
      $this->set(compact('defaultPrice'));
      */
      
			$previousProductsWithThisName=[];
			
			$previousProductsWithThisName=$this->Product->find('all',[
        'conditions'=>[
          'TRIM(LOWER(Product.name))'=>trim(strtolower($this->request->data['Product']['name'])),
          'Product.id !='=>$id
        ],
      ]);
    
			$warehouseAssigned='0';
      foreach ($this->request->data['Warehouse'] as $warehouseId=>$warehouseData){
        $warehouseAssigned = $warehouseAssigned || $warehouseData['bool_assigned'];
      }
    /*  
      $productionTypeAssigned='0';
      foreach ($this->request->data['ProductionType'] as $productionTypeId=>$productionTypeData){
        $productionTypeAssigned = $productionTypeAssigned || $productionTypeData['bool_assigned']; 
      }
    */  
      $machineAssigned='0';
      foreach ($this->request->data['MachineProduct'] as $machineId=>$machineData){
        $machineAssigned = $machineAssigned || $machineData['bool_assigned']; 
      }
      
			if (count($previousProductsWithThisName)>0){
				$this->Session->setFlash('Ya se introdujo un producto con este nombre!  No se guardó el producto.', 'default',['class' => 'error-message']);
			}
      elseif (empty($this->request->data['Product']['product_type_id'])){
        $this->Session->setFlash('Se debe especificar el tipo de producto.  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Product']['product_nature_id']) && $this->request->data['Product']['product_type_id'] != PRODUCT_TYPE_SERVICE){
        $this->Session->setFlash('Se debe especificar la naturaleza de producto.  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Product']['production_type_id']) && in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_RAW])){
        $this->Session->setFlash('El producto se tiene que asociar con un tipo de producción.', 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['PriceClientCategory'][PRICE_CLIENT_CATEGORY_GENERAL]['category_price'] <= 0  && in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_BOTTLES_BOUGHT,PRODUCT_NATURE_ACCESORIES])){
        $this->Session->setFlash('El precio de venta tiene que ser mayor que cero!  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Product']['threshold_volume'] <= 0  && in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_BOTTLES_BOUGHT,PRODUCT_NATURE_ACCESORIES])){
        $this->Session->setFlash('El volumen de venta tiene que ser mayor que cero!  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
      elseif (!$warehouseAssigned){
        $this->Session->setFlash('El producto se tiene que asignar a por lo menos una bodega.', 'default',['class' => 'error-message']);
      }
      /*
      elseif (!$productionTypeAssigned && in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_RAW])){
        $this->Session->setFlash('El producto se tiene que asignar a por lo menos un tipo de producción.', 'default',['class' => 'error-message']);
      }
      elseif (!$machineAssigned && in_array($this->request->data['Product']['product_type_id'],[PRODUCT_TYPE_BOTTLE])){
        $this->Session->setFlash('La botella tiene que asignarse a por lo menos una máquina.', 'default',['class' => 'error-message']);
      }
      */
      elseif (empty($this->request->data['Product']['preferred_raw_material_id']) && $this->request->data['Product']['product_type_id'] == PRODUCT_TYPE_BOTTLE){
        $this->Session->setFlash('Para productos de tipo botella se debe indicar la materia prima preferida!  No se guardó el producto.', 'default',['class' => 'error-message']);
      }
      else {
        $successMessage='Se editó el producto '.$this->request->data['Product']['name'].'.  ';
        
				$datasource=$this->Product->getDataSource();
				$datasource->begin();
				try {
					$this->Product->id=$id;
					if (!$this->Product->save($this->request->data)) {
						echo "problema al editar el producto";
						pr($this->validateErrors($this->Product));
						throw new Exception();
					} 
					$productId=$this->Product->id;
          
          foreach ($this->request->data['Warehouse'] as $warehouseId=>$warehouseData){
            if (
              (in_array($warehouseId,array_keys($productWarehouses)) && !$warehouseData['bool_assigned']) || 
              (!in_array($warehouseId,array_keys($productWarehouses)) && $warehouseData['bool_assigned']) 
            ){
              $warehouseProductArray=[
                'WarehouseProduct'=>[
                  'assignment_datetime'=>date('Y-m-d H:i:s'),
                  'warehouse_id'=>$warehouseId,
                  'product_id'=>$productId,
                  'bool_assigned'=>$warehouseData['bool_assigned'],
                ]
              ];  
              $this->WarehouseProduct->create();
              if (!$this->WarehouseProduct->save($warehouseProductArray)) {
                echo "Problema guardando las asociaciones del producto ".$productId.' con bodega '.$warehouseId;
                pr($this->validateErrors($this->WarehouseProduct));
                throw new Exception();
              } 
            }            
          }
          if (in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_RAW])){
          /*
            if (!empty($this->request->data['ProductionType'])){  
              foreach ($this->request->data['ProductionType'] as $productionTypeId=>$productionTypeData){
                if (
                  (in_array($productionTypeId,array_keys($productProductionTypes)) && !$productionTypeData['bool_assigned']) || 
                  (!in_array($productionTypeId,array_keys($productProductionTypes)) && $productionTypeData['bool_assigned']) 
                ){
                  $productionTypeProductArray=[
                    'ProductionTypeProduct'=>[
                      'assignment_datetime'=>date('Y-m-d H:i:s'),
                      'production_type_id'=>$productionTypeId,
                      'product_id'=>$productId,
                      'bool_assigned'=>$productionTypeData['bool_assigned'],
                    ]
                  ];  
                  $this->ProductionTypeProduct->create();
                  if (!$this->ProductionTypeProduct->save($productionTypeProductArray)) {
                    echo "Problema guardando las asociaciones del producto ".$productId.' con bodega '.$productionTypeId;
                    pr($this->validateErrors($this->ProductionTypeProduct));
                    throw new Exception();
                  }
                }      
              }
            }
          */    
          }
					
          if ($this->request->data['Product']['product_nature_id'] == PRODUCT_NATURE_PRODUCED){
            foreach ($this->request->data['PriceClientCategory'] as $priceClientCategoryId=>$priceClientCategoryData){
              if ($priceClientCategoryData['category_price'] != $productPrices[$priceClientCategoryId]){
                $productPriceData=[
                  'ProductPriceLog'=>[
                    'price_datetime'=>date('Y-m-d H:i:s'),
                    'product_id'=>$id,
                    
                    'raw_material_id'=>$this->request->data['Product']['preferred_raw_material_id'],
                    'price_client_category_id'=>$priceClientCategoryId,
                    'user_id'=>$loggedUserId,
                    'price'=>$priceClientCategoryData['category_price'],
                    'currency_id'=>CURRENCY_CS,
                  ],
                ];
                $this->ProductPriceLog->create();
                if (!$this->ProductPriceLog->save($productPriceData)) {
                  echo "Problema guardando el precio del producto ".$productId.' para categoría '.$priceClientCategoryId;
                  pr($this->validateErrors($this->ProductPriceLog));
                  throw new Exception();
                }
              }    
            }
            
            if ($productThresholdVolume != $this->request->data['Product']['threshold_volume']){             
              $productThresholdVolumeArray=[
                'ProductThresholdVolume'=>[
                  'volume_datetime'=>date('Y-m-d H:i:s'),
                  'product_id'=>$productId,
                  'raw_material_id'=>$this->request->data['Product']['preferred_raw_material_id'],
                  'price_client_category_id'=>PRICE_CLIENT_CATEGORY_VOLUME,
                  'threshold_volume'=>$this->request->data['Product']['threshold_volume'],
                  'user_id'=>$loggedUserId,
                ],
              ];
              $this->ProductThresholdVolume->create();
              if (!$this->ProductThresholdVolume->save($productThresholdVolumeArray)) {
                echo "problema al guardar el volumen de ventas para el producto";
                pr($this->validateErrors($this->ProductThresholdVolume));
                throw new Exception();
              }  
						}
            
            $previousAcceptableProduction=$this->ProductProduction->getAcceptableProduction($productId);
            
            if ($this->request->data['ProductProduction']['acceptable_production'] != $previousAcceptableProduction){
              $productProductionArray=[];
              $productProductionArray['ProductProduction']['application_date']=date('Y-m-d');
              $productProductionArray['ProductProduction']['product_id']=$productId;
              $productProductionArray['ProductProduction']['acceptable_production']=$this->request->data['ProductProduction']['acceptable_production'];
              $this->ProductProduction->create();
              if (!$this->ProductProduction->save($productProductionArray)) {
                echo "problema al guardar la producción aceptable para el producto";
                pr($this->validateErrors($this->ProductProduction));
                throw new Exception();
              }
            }  
            
            foreach ($this->request->data['MachineProduct'] as $machineId=>$machineData){
               if (
                (in_array($machineId,array_keys($productMachines)) && !$machineData['bool_assigned']) || 
                (!in_array($machineId,array_keys($productMachines)) && $machineData['bool_assigned']) 
              ){
                $machineProductArray=[
                  'MachineProduct'=>[
                    'assignment_datetime'=>date('Y-m-d H:i:s'),
                    'bool_assigned'=>$machineData['bool_assigned'],
                    'machine_id'=>$machineId,
                    'product_id'=>$productId,
                  ]
                ];
                $this->MachineProduct->create();
                //pr($machineProductArray);
                if (!$this->MachineProduct->save($machineProductArray)) {
                  echo "problema al guardar las asociaciones entre maquinas y productos";
                  pr($this->validateErrors($this->MachineProduct));
                  throw new Exception();
                }                
              }
            }
					}
          elseif (!in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_RAW,PRODUCT_NATURE_BAGS])){     
            foreach ($this->request->data['PriceClientCategory'] as $priceClientCategoryId=>$priceClientCategoryData){
              if ($priceClientCategoryData['category_price'] != $productPrices[$priceClientCategoryId]){
                $productPriceData=[
                  'ProductPriceLog'=>[
                    'price_datetime'=>date('Y-m-d H:i:s'),
                    'product_id'=>$productId,
                    'price_client_category_id'=>$priceClientCategoryId,
                    'user_id'=>$loggedUserId,
                    'price'=>$priceClientCategoryData['category_price'],
                    'currency_id'=>CURRENCY_CS,
                  ],
                ];
                $this->ProductPriceLog->create();
                if (!$this->ProductPriceLog->save($productPriceData)) {
                  echo "Problema guardando el precio del producto ".$productId.' para categoría '.$priceClientCategoryId;
                  pr($this->validateErrors($this->ProductPriceLog));
                  throw new Exception();
                }   
              }
            }
            
            if ($productThresholdVolume != $this->request->data['Product']['threshold_volume']){
              $productThresholdVolumeArray=[
                'ProductThresholdVolume'=>[
                  'volume_datetime'=>date('Y-m-d H:i:s'),
                  'product_id'=>$productId,
                  'price_client_category_id'=>PRICE_CLIENT_CATEGORY_VOLUME,
                  'threshold_volume'=>$this->request->data['Product']['threshold_volume'],
                  'user_id'=>$loggedUserId,
                ],
              ];
              $this->ProductThresholdVolume->create();
              if (!$this->ProductThresholdVolume->save($productThresholdVolumeArray)) {
                echo "problema al guardar el volumen de ventas para el producto";
                pr($this->validateErrors($this->ProductThresholdVolume));
                throw new Exception();
              }  
            }
          }      
             
					$datasource->commit();
					
					$this->recordUserAction($this->Product->id,null,null);
					$this->Session->setFlash($successMessage,'default',['class' => 'success']);
          
          return $this->redirect(['action' => 'view',$id]);  
          /*
          $aco_name="ProductPriceLogs/registrarPreciosProducto";		
          $boolRegistrarPreciosProductoPermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
          
          if (in_array($this->request->data['Product']['product_nature_id'],[PRODUCT_NATURE_RAW,PRODUCT_NATURE_BAGS]) || !$boolRegistrarPreciosProductoPermission){
            return $this->redirect(['action' => 'index']);  
          }  
          else {
            return $this->redirect(['controller'=>'productPriceLogs','action' => 'registrarPreciosProducto',$productId,2]);  
          }
          */
				} 		
				catch(Exception $e){
					$datasource->rollback();
					pr($e);					
					$this->Session->setFlash(__('The product could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
				}
			}
		} 
		else {
			$product = $this->Product->find('first',[
        'conditions'=>['Product.id'=>$id],
        'contain'=>[
          'ProductType'=>['ProductCategory'],
          'ProductNature',
          'AccountingCode',
          
          'ProductProduction'=>[
            'order'=>'ProductProduction.application_date DESC, ProductProduction.id DESC'
          ],
          
          'PreferredRawMaterial',
          'BagProduct',
          
          'DefaultCostCurrency',
        ],
      ]);
      $this->request->data=$product;
      /*
      if ($product['ProductNature']['id'] == PRODUCT_NATURE_PRODUCED){
        $defaultProductPriceLog=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($id,$product['Product']['preferred_raw_material_id'],PRICE_CLIENT_CATEGORY_GENERAL,date('Y-m-d'));
      }  
      else {
        $defaultProductPriceLog=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($id,PRICE_CLIENT_CATEGORY_GENERAL,date('Y-m-d'));
      }
      $defaultPrice=$defaultProductPriceLog['price'];
      $this->set(compact('defaultPrice'));
      */
		}
		
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
	$productspref=$this->Product->getAllPreformas();
    //pr($warehouses);
    $this->set(compact('warehouses'));
    $this->set(compact('productspref'));
    $productWarehouses=[];
    foreach ($warehouses as $warehouseId=>$warehouseName){
      if ($this->WarehouseProduct->hasWarehouse($id,$warehouseId)){
        $productWarehouses[$warehouseId]=$warehouseName;
      }
    }
    $productPreform=[];
    foreach ($productspref as $productId=>$productName){
         $productPreform[$productId]=$productName;
       
    }
    //pr($productWarehouses);
    $this->set(compact('productWarehouses'));
    $this->set(compact('productPreform'));
    
    $productionTypes=$this->ProductionType->getProductionTypes();
    $this->set(compact('productionTypes'));
    $productProductionTypes=[];
    foreach ($productionTypes as $productionTypeId=>$productionTypeName){
      if ($this->ProductionTypeProduct->hasProductionType($id,$productionTypeId)){
        $productProductionTypes[$productionTypeId]=$productionTypeName;
      }
    }
    //pr($productProductionTypes);
    $this->set(compact('productProductionTypes'));
    
    $productMachines=[];
    foreach ($machines as $machineId=>$machineName){
      if ($this->MachineProduct->hasMachine($id,$machineId)){
        $productMachines[$machineId]=$machineName;
      }
    }
    $this->set(compact('productMachines'));
    
    $productPrices=[];
    foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
      if ($this->Product->getProductTypeId($id) == PRODUCT_TYPE_BOTTLE){
        $productPrices[$priceClientCategoryId]=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($id,$this->Product->getPreferredRawMaterialId($id), $priceClientCategoryId,date('Y-m-d H:i:s'))['price'];
      }
      else {
        $productPrices[$priceClientCategoryId]=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($id,$priceClientCategoryId,date('Y-m-d H:i:s'))['price'];
      }
    }
    $this->set(compact('productPrices'));
    
    $productThresholdVolume=$this->ProductThresholdVolume->getThresholdVolume($id,PRICE_CLIENT_CATEGORY_VOLUME,date('Y-m-d H:i:s'));
    $this->set(compact('productThresholdVolume'));
    
    $productTypes = $this->Product->ProductType->find('list',[
      'order'=>'name',
    ]);
		$preferredRawMaterials=$this->Product->PreferredRawMaterial->find('list',[
			'conditions'=>['product_type_id'=>PRODUCT_TYPE_PREFORMA],
			'order'=>'name ASC',
		]);

	//agregado para validar seleccion de tranferible a en edicion de productos de tipo ingroup	
	  $isingroup=(($this->Product->getProductTypeId($id)==PRODUCT_TYPE_INJECTION_OUTPUT) && $this->Product->find('first',['conditions'=>['Product.id'=>$id,'Product.name LIKE'=>'preforma%']]));
	 
		$this->set(compact('productTypes','preferredRawMaterials','isingroup'));
		
    $bagProducts=$this->Product->getProductsByProductNature(PRODUCT_NATURE_BAGS);
    $this->set(compact('bagProducts'));
    
		$inventoryAccountingCode=$this->AccountingCode->getAccountingCodeById(ACCOUNTING_CODE_INVENTORY);
    $accountingCodes=$this->AccountingCode->getChildAccountingCodes($inventoryAccountingCode['AccountingCode']['lft'],$inventoryAccountingCode['AccountingCode']['rght']);
		$this->set(compact('accountingCodes'));
    
    $defaultPriceCurrencies=$defaultCostCurrencies=$currencies=$this->Currency->find('list');
    $this->set(compact('defaultPriceCurrencies','defaultCostCurrencies'));
		
    $productNatures=$this->ProductNature->getProductNatureList();
    $this->set(compact('productNatures'));
    
    $units=$this->Unit->getUnitList();
    $this->set(compact('units'));
    
    $injectionProducts = $this->Product->getProductsByProductionType(PRODUCTION_TYPE_INJECTION,PRODUCT_NATURE_PRODUCED);
		$this->set(compact('injectionProducts'));
    
    $injectionRawMaterials = $this->Product->getProductsByProductionType(PRODUCTION_TYPE_INJECTION,PRODUCT_NATURE_RAW);
		$this->set(compact('injectionRawMaterials'));
    //pr($rawMaterials);
    
    $rawMaterialUnits=$this->Product->getProductUnitList(array_keys($injectionRawMaterials));
    $this->set(compact('rawMaterialUnits'));
    
    $injectionConsumables = $this->Product->getProductsByProductNature(PRODUCT_NATURE_BAGS);
		$this->set(compact('injectionConsumables'));
    //pr($injectionConsumables);
    
    $consumableUnits=$this->Product->getProductUnitList(array_keys($injectionConsumables));
    $this->set(compact('consumableUnits'));
    
		$aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
	}
  
  public function sortByRawMaterial($a,$b ){ 
    return ($a['name'] < $b['name']) ? -1 : 1;
	} 

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Product->id = $id;
		if (!$this->Product->exists()) {
			throw new NotFoundException(__('Invalid product'));
		}
		$this->request->allowMethod('post', 'delete');
    
    $product=$this->Product->find('first',[
			'conditions'=>['Product.id'=>$id,],
			'contain'=>[
				'ProductProduction',
        'ProductPriceLog',
        'ProductionMovement'=>['conditions'=>['ProductionMovement.product_quantity >'=>0]],
        'StockMovement'=>['conditions'=>['StockMovement.product_quantity >'=>0]],
			],
		]);
		$flashMessage="";
		$boolDeletionAllowed=true;
    
    if (!empty($product['ProductionMovement'])){
      $flashMessage.="Hay movimientos de producción asociados con este producto";
      $boolDeletionAllowed='0';  
    }
    if (!empty($product['StockMovement'])){
      $flashMessage.="Hay movimientos de lote asociados con este producto";
      $boolDeletionAllowed='0';  
    }
    
    if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó el producto.";
			$this->Session->setFlash($flashMessage, 'default',['class' => 'error-message']);
			return $this->redirect(['action' => 'ver',$id]);
		}
		else {
			$datasource=$this->Product->getDataSource();
			$datasource->begin();	
			try {
				//delete all products, remarks and other costs
				foreach ($product['ProductProduction'] as $productProduction){
					if (!$this->Product->ProductProduction->delete($productProduction['id'])) {
						echo "Problema al eliminar la producción acceptable del producto";
						pr($this->validateErrors($this->Product->ProductProduction));
						throw new Exception();
					}
				}
        foreach ($product['ProductPriceLog'] as $productPriceLog){
					if (!$this->Product->ProductPriceLog->delete($productPriceLog['id'])) {
						echo "Problema al eliminar los precios de venta del producto";
						pr($this->validateErrors($this->Product->ProductPriceLog));
						throw new Exception();
					}
				}
				
				if (!$this->Product->delete($id)) {
					echo "Problema al eliminar el producto";
					pr($this->validateErrors($this->Product));
					throw new Exception();
				}
						
				$datasource->commit();
				
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=[];
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$product['Product']['id'];
				$deletionArray['Deletion']['reference']=$product['Product']['name'];
				$deletionArray['Deletion']['type']='Product';
				$this->Deletion->save($deletionArray);
						
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó el producto ".$product['Product']['name']);
						
				$this->Session->setFlash(__('Se eliminó el producto.')." ".$product['Product']['name'],'default',['class' => 'success']);				
				return $this->redirect(['action' => 'index']);
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar la orden de compra.'), 'default',['class' => 'error-message']);
				return $this->redirect(['action' => 'view',$id]);
			}
		}
	}
	
	public function compareArraysByDate($a, $b){
		$ad = strtotime($a['order_date']);
		$bd = strtotime($b['order_date']);
		return ($ad-$bd);
	}
	
	public function viewSaleReport($id=null,$startDate = null,$endDate=null) {
		$this->loadModel('Order');
		$this->loadModel('ProductionMovement');
		$this->loadModel('StockMovement');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('StockItem');
		
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
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->Product->recursive=-1;
    $model=$this;
    /*
    $allFinishedProducts = Cache::remember('productsalereport_productspercategory_'.CATEGORY_PRODUCED, function() use ($model){
        return $model->Product->find('all',[
          'fields'=>['id','name'],
          'conditions' => ['ProductType.product_category_id'=>CATEGORY_PRODUCED],
          'contain'=>[
            'ProductType',
          ],
          'order'=>'name ASC'
        ]);
    }, 'long');
    */
		$allFinishedProducts=$this->Product->find('all',
			array(
				'fields'=>array('id','name'),
				'conditions'=>array(
					'ProductType.product_category_id'=>CATEGORY_PRODUCED
				),
				'contain'=>array(
					'ProductType',
				),
				'order'=>'name ASC'
			)
		);
    
		$this->ProductionResultCode->recursive=-1;
		$allProductionResultCodes=$this->ProductionResultCode->find('all',
			array('fields'=>array('id','code'))
		);
		$model->Product->recursive=-1;
		if ($id==null){
      /*
      $allRawMaterials = Cache::remember('productsalereport_productspercategory_'.CATEGORY_RAW, function() use ($model){
        return $model->Product->find('all',[
          'fields'=>['Product.id','Product.name'],
          'conditions' => ['ProductType.product_category_id'=>CATEGORY_RAW],
          'contain'=>[
            'ProductType',
          ],
          'order'=>'Product.name ASC'
        ]);
      }, 'long');
      */
			$allRawMaterials = $this->Product->find('all',array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array('ProductType.product_category_id'=>CATEGORY_RAW),
				'contain'=>array(
					'ProductType',
				),
				'order'=>'Product.name',
			));
      
		}
		else {
			$allRawMaterials=$this->Product->find('first',array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array('Product.id'=>$id)
			));
		}
    /*
    $allSales = Cache::remember('productsalereport_allsales_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne){
      return $model->Order->find('all',[
        'fields'=>['id','order_date','order_code'],
				'contain'=>[
					'ThirdParty'=>['fields'=>'company_name'],
					'StockMovement'=>[
						'fields'=>['id','movement_date','order_id','stockitem_id','product_quantity','product_unit_price','product_total_price'],
						'Product'=>[
							'fields'=>['id','packaging_unit'],	
							'ProductType'=>['fields'=>'product_category_id'],
						],
						'StockItem'=>[
							'fields'=>['production_result_code_id','product_id','raw_material_id'],
						]
					],
          'Invoice',
          'CashReceipt'
				],
				'conditions'=>[
					'Order.stock_movement_type_id'=>MOVEMENT_SALE,
					'Order.order_date >='=>$startDate,
					'Order.order_date <'=>$endDatePlusOne,
				],
				'order'=>'order_date ASC'
      ]);
    }, 'long');
    */
      $allSales=$this->Order->find('all',
        array(
          'fields'=>array('id','order_date','order_code'),
          'contain'=>array(
            'ThirdParty'=>array('fields'=>'company_name'),
            'StockMovement'=>array(
              'fields'=>array('id','movement_date','order_id','stockitem_id','product_quantity','product_unit_price','product_total_price'),
              'Product'=>array(
                'fields'=>array('id','packaging_unit'),	
                'ProductType'=>array('fields'=>'product_category_id'),
              ),
              'StockItem'=>array(
                'fields'=>array('production_result_code_id','product_id','raw_material_id'),
              )
            ),
            'Invoice',
            'CashReceipt'
          ),
          'conditions'=>array(
            'Order.stock_movement_type_id'=>MOVEMENT_SALE,
            'Order.order_date >='=>$startDate,
            'Order.order_date <'=>$endDatePlusOne,
          ),
          'order'=>'order_date ASC'
        )
      );
		
		
		$salesData=[];
		$i=0;
		//pr($allSales);
		/*
    $allReclassifications = Cache::remember('productsalereport_allreclassifications_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne){
      return $model->StockMovement->find('all',[
        'fields'=>['StockMovement.reclassification_code','StockMovement.movement_date'],
        'conditions'=>[
          'StockMovement.bool_reclassification'=>true,
          'StockMovement.movement_date >='=>$startDate,
          'StockMovement.movement_date <='=>$endDatePlusOne,
        ],
        'group'=>'reclassification_code',
      ]);
    }, 'long');
    */
		$allReclassifications=$this->StockMovement->find('all',array(
			'fields'=>array('StockMovement.reclassification_code','StockMovement.movement_date'),
			'conditions'=>array(
				'StockMovement.bool_reclassification'=>true,
				'StockMovement.movement_date >='=>$startDate,
				'StockMovement.movement_date <='=>$endDatePlusOne,
			),
			'group'=>'reclassification_code',
		));
		
		for ($r=0;$r<count($allReclassifications);$r++){
      $reclassificationCode=$allReclassifications[$r]['StockMovement']['reclassification_code'];
      /*
      $reclassificationMovements = Cache::remember('productsalereport_reclassificationmovements_'.$reclassificationCode.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$reclassificationCode,$startDate,$endDatePlusOne){
        return $model->StockMovement->find('all',[
          'fields'=>['StockMovement.movement_date','StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.product_id','StockMovement.production_result_code_id','StockMovement.bool_input'],
          'conditions'=>[
            'StockMovement.bool_reclassification'=>true,
            'StockMovement.movement_date >='=>$startDate,
            'StockMovement.movement_date <='=>$endDatePlusOne,
            'StockMovement.reclassification_code'=>$reclassificationCode,
          ],
          'contain'=>[
            'Product'=>[
              'fields'=>['id','packaging_unit'],	
              'ProductType'=>['fields'=>'product_category_id'],
            ],
            'StockItem'=>[
              'fields'=>['production_result_code_id','product_id','raw_material_id'],
            ]
          ],
        ]);
      }, 'long');
      */
			$reclassificationMovements=$this->StockMovement->find('all',array(
				'fields'=>array('StockMovement.movement_date','StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.product_id','StockMovement.production_result_code_id','StockMovement.bool_input'),
				'conditions'=>array(
					'StockMovement.bool_reclassification'=>true,
					'StockMovement.movement_date >='=>$startDate,
					'StockMovement.movement_date <='=>$endDatePlusOne,
					'StockMovement.reclassification_code'=>$allReclassifications[$r]['StockMovement']['reclassification_code'],
				),
				'contain'=>array(
					'Product'=>array(
						'fields'=>array('id','packaging_unit'),	
						'ProductType'=>array('fields'=>'product_category_id'),
					),
					'StockItem'=>array(
						'fields'=>array('production_result_code_id','product_id','raw_material_id'),
					)
				),
			));
      
			$allReclassifications[$r]['reclassificationMovements']=$reclassificationMovements;
		}
		
		//pr($allReclassifications);
				
		foreach ($allRawMaterials as $rawMaterial){
			$salesData[$i]['RawMaterial']['id']=$rawMaterial['Product']['id'];
			$salesData[$i]['RawMaterial']['name']=$rawMaterial['Product']['name'];
			
			$saleCount=0;
			// first get the sales data
			$formattedSales=[];
			foreach ($allSales as $sale){
				//pr($sale);
				$sold=[];
				$saleid=$sale['Order']['id'];
				foreach ($sale['StockMovement'] as $saleMovement){
					$soldProducts=[];
          if (!empty($saleMovement['StockItem'])){
            //echo "salemovement loop started";
            if ($saleMovement['StockItem']['raw_material_id']==$rawMaterial['Product']['id']){
              $productidForSale=$saleMovement['StockItem']['product_id'];
              $productionresultcodeForSale=$saleMovement['StockItem']['production_result_code_id'];
              foreach ($allFinishedProducts as $finishedProduct){
                foreach ($allProductionResultCodes as $productionResultCode){	
                  // retrieve products sold
                  if ($productidForSale==$finishedProduct['Product']['id'] && $productionresultcodeForSale==$productionResultCode['ProductionResultCode']['id']){
                    $soldProducts[]=$saleMovement['product_quantity'];
                  }
                  else{
                    $soldProducts[]=0;
                  }
                }
              }
            }
            foreach (array_keys($soldProducts + $sold) as $key) {
              $sold[$key] = (isset($soldProducts[$key]) ? $soldProducts[$key] : 0) + (isset($sold[$key]) ? $sold[$key] : 0);
            }
					}
          else {
             //pr($saleMovement);
          }
				}
				
				//pr($sold);
				/*
				if (array_sum($sold)>0){
					$salesData[$i]['Sale'][$saleCount]['id']=$sale['Order']['id'];
					$salesData[$i]['Sale'][$saleCount]['order_date']=$sale['Order']['order_date'];
					$salesData[$i]['Sale'][$saleCount]['order_code']=$sale['Order']['order_code'];
					$salesData[$i]['Sale'][$saleCount]['client']=$sale['ThirdParty']['company_name'];
					$salesData[$i]['Sale'][$saleCount]['sold_products']=$sold;
				}
				*/
				
				if (array_sum($sold)>0){
          //if ($sale['Order']['id']==2501){
            //pr($sale);
          //}
					$formattedSales[$saleCount]['id']=$sale['Order']['id'];
					$formattedSales[$saleCount]['order_date']=$sale['Order']['order_date'];
					$formattedSales[$saleCount]['order_code']=$sale['Order']['order_code'];
          $formattedSales[$saleCount]['is_sale']=(empty($sale['Invoice'])?0:1);
					$formattedSales[$saleCount]['reclassification_code']=0;
					$formattedSales[$saleCount]['client']=$sale['ThirdParty']['company_name'];
					$formattedSales[$saleCount]['sold_products']=$sold;
				}
				$saleCount++;
			}
			
			$formattedReclassifications=array();
			foreach ($allReclassifications as $reclassification){
				$reclassified=array();
				
				foreach($reclassification['reclassificationMovements'] as $reclassificationMovement){
					$reclassifiedProducts=array();
					
					$productidForReclassification=$reclassificationMovement['StockMovement']['product_id'];
					$productionresultcodeForReclassification=$reclassificationMovement['StockMovement']['production_result_code_id'];
					if ($reclassificationMovement['StockItem']['raw_material_id']==$rawMaterial['Product']['id']){
						//echo "production result code is ".$productionresultcodeForReclassification."<br/>";
						foreach ($allFinishedProducts as $finishedProduct){
							foreach ($allProductionResultCodes as $productionResultCode){	
								if ($productidForReclassification==$finishedProduct['Product']['id'] && $productionresultcodeForReclassification==$productionResultCode['ProductionResultCode']['id']){
									if ($reclassificationMovement['StockMovement']['bool_input']){
										$reclassifiedProducts[]=$reclassificationMovement['StockMovement']['product_quantity'];
									}
									else {
										$reclassifiedProducts[]=0-$reclassificationMovement['StockMovement']['product_quantity'];
									}
								}
								else{
									$reclassifiedProducts[]=0;
								}
							}
						}
					}
					
					foreach (array_keys($reclassifiedProducts + $reclassified) as $key) {
						$reclassified[$key] = (isset($reclassifiedProducts[$key]) ? $reclassifiedProducts[$key] : 0) + (isset($reclassified[$key]) ? $reclassified[$key] : 0);
					}
					
				}
				if (!empty($reclassified)){
					if (max($reclassified)>0){
						$formattedReclassifications[$saleCount]['id']=0;
						$formattedReclassifications[$saleCount]['order_date']=$reclassification['StockMovement']['movement_date'];
						$formattedReclassifications[$saleCount]['order_code']=0;
						$formattedReclassifications[$saleCount]['reclassification_code']=$reclassification['StockMovement']['reclassification_code'];
						$formattedReclassifications[$saleCount]['client']="-";
						$formattedReclassifications[$saleCount]['sold_products']=$reclassified;
						//pr($reclassified);
					}			
				}
				$saleCount++;
			}
			
			// and now for the conclusion: merge the sales and the reclassifications by date
			$mergedSalesAndReclassifications=array_merge($formattedSales,$formattedReclassifications);
			usort($mergedSalesAndReclassifications,array($this,'compareArraysByDate'));
			$salesData[$i]['Sale']=$mergedSalesAndReclassifications;
			//pr($mergedSalesAndReclassifications);
			
			$initialStock=array();
			$producedStock=array();
			$reclassifiedStock=array();
			$finalStock=array();
			foreach ($allFinishedProducts as $finishedProduct){
				foreach ($allProductionResultCodes as $productionResultCode){	
					// get all the stockitems
					//ADDED RECURSIVE 20151201
          $finishedProductId=$finishedProduct['Product']['id'];
          $productionResultCodeId=$productionResultCode['ProductionResultCode']['id'];
          $rawMaterialId=$rawMaterial['Product']['id'];
          
          $model->StockItem->recursive=-1;
          $allStockItemsForProduct = Cache::remember('productsalereport_allstockitemsforproduct_'.$finishedProductId.'_'.$productionResultCodeId.'_'.$rawMaterialId.'_'.$startDate, function() use ($model,$finishedProductId,$productionResultCodeId,$rawMaterialId,$startDate){
            return $model->StockItem->find('all',[
              'fields'=>'StockItem.id',
              'conditions' => [
                'StockItem.product_id'=> $finishedProductId,
                'StockItem.production_result_code_id'=> $productionResultCodeId,
                'StockItem.raw_material_id'=> $rawMaterialId,
                // ADDED CONDITIONS 20180314
                'StockItem.stockitem_creation_date <'=>$startDate,
                'StockItem.stockitem_depletion_date >'=>$startDate,
              ],
            ]);  
          }, 'long');      
          /*
					$allStockItemsForProduct = $this->StockItem->find('all', array(
						//ADDED FIELDS 20151201
						'fields'=>'StockItem.id',
						'conditions' => 
							array(
								'StockItem.product_id ='=> $finishedProduct['Product']['id'],
								'StockItem.production_result_code_id ='=> $productionResultCode['ProductionResultCode']['id'],
								'StockItem.raw_material_id'=> $rawMaterial['Product']['id'],
                // ADDED CONDITIONS 20180314
                'StockItem.stockitem_creation_date <'=>$startDate,
                'StockItem.stockitem_depletion_date >'=>$startDate,
							)
						)
					);
          */
					//pr($allStockItemsForProduct);
					$quantityInitialStock=0;
					$quantityProduced=0;
					$quantityReclassified=0;
					$quantityFinalStock=0;					
					
					// retrieve produced quantity
          $producedProducts = Cache::remember('productsalereport_producedproducts_'.$finishedProductId.'_'.$productionResultCodeId.'_'.$rawMaterialId.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$finishedProductId,$productionResultCodeId,$rawMaterialId,$startDate,$endDatePlusOne){
            return $model->ProductionMovement->find('all',[
              'fields'=>['Product.id, SUM(ProductionMovement.product_quantity) as total_product_quantity'],
              'conditions' => array(
                'ProductionMovement.product_id ='=> $finishedProductId,
                'ProductionMovement.production_result_code_id'=> $productionResultCodeId,
                'ProductionMovement.movement_date >='=>$startDate,
                'ProductionMovement.movement_date <'=>$endDatePlusOne,
                'StockItem.raw_material_id'=> $rawMaterialId,
              ),
              'group'=>array('Product.id')
            ]);  
          }, 'long'); 
          /*
					$producedProducts=$this->ProductionMovement->find('all',array(
						'fields'=>array('Product.id, SUM(ProductionMovement.product_quantity) as total_product_quantity'),
						'conditions' => array(
							'ProductionMovement.product_id ='=> $finishedProduct['Product']['id'],
							'ProductionMovement.production_result_code_id'=> $productionResultCode['ProductionResultCode']['id'],
							'ProductionMovement.movement_date >='=>$startDate,
							'ProductionMovement.movement_date <'=>$endDatePlusOne,
							'StockItem.raw_material_id'=> $rawMaterial['Product']['id'],
						),
						'group'=>array('Product.id')
					));
          */
					//pr($producedProducts);
					if (!empty($producedProducts)){
						$quantityProduced=$producedProducts[0][0]['total_product_quantity'];
					}
					$inputReclassifiedProducts = Cache::remember('productsalereport_inputreclassifiedproducts_'.$finishedProductId.'_'.$productionResultCodeId.'_'.$rawMaterialId.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$finishedProductId,$productionResultCodeId,$rawMaterialId,$startDate,$endDatePlusOne){
            return $model->StockMovement->find('all',[
              'fields'=>['Product.id, SUM(StockMovement.product_quantity) as total_product_quantity'],
              'conditions' => [
                'StockMovement.product_id ='=> $finishedProductId,
                'StockMovement.production_result_code_id'=> $productionResultCodeId,
                'StockMovement.movement_date >='=>$startDate,
                'StockMovement.movement_date <'=>$endDatePlusOne,
                'StockItem.raw_material_id'=> $rawMaterialId,
                'StockMovement.bool_reclassification'=>true,
                'StockMovement.bool_input'=>true,
              ],
              'group'=>['Product.id']
            ]);  
          }, 'long'); 
          /*
					$inputReclassifiedProducts=$this->StockMovement->find('all',array(
						'fields'=>array('Product.id, SUM(StockMovement.product_quantity) as total_product_quantity'),
						'conditions' => array(
							'StockMovement.product_id ='=> $finishedProduct['Product']['id'],
							'StockMovement.production_result_code_id'=> $productionResultCode['ProductionResultCode']['id'],
							'StockMovement.movement_date >='=>$startDate,
							'StockMovement.movement_date <'=>$endDatePlusOne,
							'StockItem.raw_material_id'=> $rawMaterial['Product']['id'],
							'StockMovement.bool_reclassification'=>true,
							'StockMovement.bool_input'=>true,
						),
						'group'=>array('Product.id')
					));
          */
          $outputReclassifiedProducts = Cache::remember('productsalereport_outputreclassifiedproducts_'.$finishedProductId.'_'.$productionResultCodeId.'_'.$rawMaterialId.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$finishedProductId,$productionResultCodeId,$rawMaterialId,$startDate,$endDatePlusOne){
            return $model->StockMovement->find('all',[
              'fields'=>['Product.id, SUM(StockMovement.product_quantity) as total_product_quantity'],
              'conditions' => [
                'StockMovement.product_id ='=> $finishedProductId,
                'StockMovement.production_result_code_id'=> $productionResultCodeId,
                'StockMovement.movement_date >='=>$startDate,
                'StockMovement.movement_date <'=>$endDatePlusOne,
                'StockItem.raw_material_id'=> $rawMaterialId,
                'StockMovement.bool_reclassification'=>true,
                'StockMovement.bool_input'=>true,
              ],
              'group'=>['Product.id']
            ]);  
          }, 'long'); 
					
          /*
          $outputReclassifiedProducts=$this->StockMovement->find('all',array(
						'fields'=>array('Product.id, SUM(StockMovement.product_quantity) as total_product_quantity'),
						'conditions' => array(
							'StockMovement.product_id ='=> $finishedProduct['Product']['id'],
							'StockMovement.production_result_code_id'=> $productionResultCode['ProductionResultCode']['id'],
							'StockMovement.movement_date >='=>$startDate,
							'StockMovement.movement_date <'=>$endDatePlusOne,
							'StockItem.raw_material_id'=> $rawMaterial['Product']['id'],
							'StockMovement.bool_reclassification'=>true,
							'StockMovement.bool_input'=>'0',
						),
						'group'=>array('Product.id')
					));
          */
					//pr($reclassifiedProducts);
					if (!empty($inputReclassifiedProducts)||!empty($outputReclassifiedProducts)){
						if (!empty($inputReclassifiedProducts)){
							// we know there is reclassified input
							if (!empty($outputReclassifiedProducts)){
								// both reclassified input and output
								$quantityReclassified=$inputReclassifiedProducts[0][0]['total_product_quantity']-$outputReclassifiedProducts[0][0]['total_product_quantity'];
							}
							else {
								// only reclassified input
								$quantityReclassified=$inputReclassifiedProducts[0][0]['total_product_quantity'];
							}
						}
						else {
							// only reclassified output
							$quantityReclassified=-$outputReclassifiedProducts[0][0]['total_product_quantity'];
						}
					}
					
					foreach ($allStockItemsForProduct as $stockItemForProduct){
						$stockItemId=$stockItemForProduct['StockItem']['id'];
            //if ($finishedProductId == 52 && $productionResultCodeId == PRODUCTION_RESULT_CODE_C){
            //  pr($stockItemId);
            //}
						// retrieve initial stock value
						//get the last stockitem log before the startdate to determine the initial stock
            $model->StockItem->StockItemLog->recursive=-1;
            $initialStockItemLogForStockItem = Cache::remember('productsalereport_stockitemlog_initial_'.$stockItemId.'_'.$startDate, function() use ($model,$stockItemId,$startDate){
              return $model->StockItem->StockItemLog->find('first',[
                'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
                'conditions' => [
                  'StockItemLog.stockitem_id ='=> $stockItemId,
                  'StockItemLog.stockitem_date <'=>$startDate
                ],
                'order'=>'StockItemLog.id DESC'
              ]);
            }, 'long');  
						/*$initialStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
							//ADDED FIELDS 20151201
							'fields'=>'StockItemLog.product_quantity',
							'conditions' => array(
								'StockItemLog.stockitem_id ='=> $stockItemId,
								'StockItemLog.stockitem_date <'=>$startDate
							),
							'order'=>'StockItemLog.id DESC'
						));
            */
						//pr($initialStockItemLogForStockItem);
						if (!empty($initialStockItemLogForStockItem)){
							$quantityInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
						}
							
						// retrieve final stock value
						//get the last stockitem log before the startdate to determine the initial stock
            $finalStockItemLogForStockItem = Cache::remember('productsalereport_stockitemlog_final_'.$stockItemId.'_'.$endDatePlusOne, function() use ($model,$stockItemId,$endDatePlusOne){
              return $model->StockItem->StockItemLog->find('first',[
                'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
                'conditions' => [
                  'StockItemLog.stockitem_id ='=> $stockItemId,
                  'StockItemLog.stockitem_date <'=>$endDatePlusOne
                ],
                'order'=>'StockItemLog.id DESC'
              ]);
            }, 'long');  
            //if ($finishedProductId == 52 && $productionResultCodeId == PRODUCTION_RESULT_CODE_C){
              //pr($finalStockItemLogForStockItem);
            //}
            /*
						$finalStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
							//ADDED FIELDS 20151201
							'fields'=>'StockItemLog.product_quantity',
							'conditions' => array(
								'StockItemLog.stockitem_id ='=> $stockItemId,
								'StockItemLog.stockitem_date <'=>$endDatePlusOne
							),
							'order'=>'StockItemLog.id DESC'
						));
            */
						if (!empty($finalStockItemLogForStockItem)){
							$quantityFinalStock+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity'];
              //if ($finishedProductId == 52 && $productionResultCodeId == PRODUCTION_RESULT_CODE_C){
              //  echo 'quantity final stock is '.$quantityFinalStock.'<br/>';
              //}
						}
					}	
          
          $allProducedStockItemsForProduct = Cache::remember('productsalereport_allproducedstockitemsforproduct_'.$finishedProductId.'_'.$productionResultCodeId.'_'.$rawMaterialId.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$finishedProductId,$productionResultCodeId,$rawMaterialId,$startDate,$endDatePlusOne){
            return $model->StockItem->find('all',[
              'fields'=>'StockItem.id',
              'conditions' => [
                'StockItem.product_id'=> $finishedProductId,
                'StockItem.production_result_code_id'=> $productionResultCodeId,
                'StockItem.raw_material_id'=> $rawMaterialId,
                'StockItem.stockitem_creation_date >'=>$startDate,
                'StockItem.stockitem_creation_date <'=>$endDatePlusOne,
              ],
            ]);  
          }, 'long');    

          if (!empty($allProducedStockItemsForProduct)){
            foreach ($allProducedStockItemsForProduct as $producedStockItem){
              $producedStockItemId=$producedStockItem['StockItem']['id'];
              $finalStockItemLogForProducedStockItem = Cache::remember('productsalereport_producedstockitemlog_final_'.$producedStockItemId.'_'.$endDatePlusOne, function() use ($model,$producedStockItemId,$endDatePlusOne){
                return $model->StockItem->StockItemLog->find('first',[
                  'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
                  'conditions' => [
                    'StockItemLog.stockitem_id ='=> $producedStockItemId,
                    'StockItemLog.stockitem_date <'=>$endDatePlusOne
                  ],
                  'order'=>'StockItemLog.id DESC'
                ]);
              }, 'long');  
              
              if (!empty($finalStockItemLogForProducedStockItem)){
                $quantityFinalStock+=$finalStockItemLogForProducedStockItem['StockItemLog']['product_quantity'];
                //if ($finishedProductId == 52 && $productionResultCodeId == PRODUCTION_RESULT_CODE_C){
                //  echo 'quantity final stock is '.$quantityFinalStock.'<br/>';
                //}
              }
            }
          }  
          
          
					$initialStock[]=$quantityInitialStock;
					$producedStock[]=$quantityProduced;
					$reclassifiedStock[]=$quantityReclassified;
					$finalStock[]=$quantityFinalStock;					
				}
			}
			$salesData[$i]['initial_stock']=$initialStock;
			$salesData[$i]['produced_stock']=$producedStock;
			$salesData[$i]['reclassified_stock']=$reclassifiedStock;
			$salesData[$i]['final_stock']=$finalStock;
			
			$i++;
		}
		//pr($salesData);
			
		$this->set(compact('salesData','startDate','endDate','allFinishedProducts','allProductionResultCodes'));

	}
	
	/*******************************************************************************************************/
	
	public function verReporteProducto($id=null) {
		if (!$this->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
		$this->loadModel('Order');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('StockItem');
		
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
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$this->ProductionResultCode->recursive=0;
		$allProductionResultCodes=$this->ProductionResultCode->find('all',
			array('fields'=>array('id','code'))
		);
		$this->Product->recursive=0;
		$allRawMaterials = $this->Product->find('all',
			array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array('ProductType.product_category_id'=>CATEGORY_RAW)
			)
		);
		
		$finishedProduct = $this->Product->find('first',
			array(
				'fields'=>array('Product.id','Product.name'),
				'conditions'=>array('Product.id'=>$id)
			)
		);
		
		$productionRuns=$this->ProductionRun->find('all',
			array(
				'fields'=>array('id','production_run_date','raw_material_quantity'),
				'contain'=>array(
					'ProductionMovement'=>array(
						'fields'=>array('id','stockitem_id','product_quantity','product_unit_price'),
						'StockItem'=>array('fields'=>array('production_result_code_id','product_id','raw_material_id','product_unit_price'))
					)
				),
				'conditions'=>array(
					'ProductionRun.finished_product_id'=>$id,
					'ProductionRun.production_run_date >='=>$startDate,
					'ProductionRun.production_run_date <='=>$endDatePlusOne,
				),
				'order'=>'production_run_date ASC'
			)
		);
		$productionData=[];
		$i=0;
		foreach ($allRawMaterials as $rawMaterial){
			$productionData[$i]['RawMaterial']['id']=$rawMaterial['Product']['id'];
			$productionData[$i]['RawMaterial']['name']=$rawMaterial['Product']['name'];
			$productionData[$i]['Product']['id']=$finishedProduct['Product']['id'];
			$productionData[$i]['Product']['productname']=$finishedProduct['Product']['name'];
			
			$productionCount=0;
			foreach ($productionRuns as $productionRun){
				$productionRunId=$productionRun['ProductionRun']['id'];
				$rawMaterialQuantity=$productionRun['ProductionRun']['raw_material_quantity'];
				$quantityA=0;
				$quantityB=0;
				$quantityC=0;
				$valueA=0;
				$valueB=0;
				$valueC=0;
				foreach ($productionRun['ProductionMovement'] as $productionMovement){
					
					if ($productionMovement['StockItem']['raw_material_id']==$rawMaterial['Product']['id']){
						switch ($productionMovement['StockItem']['production_result_code_id']){
							case 1:
								$quantityA+=$productionMovement['product_quantity'];
								$valueA=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
								break;
							case 2:
								$quantityB+=$productionMovement['product_quantity'];
								$valueB=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
								break;
							case 3:
								$quantityC+=$productionMovement['product_quantity'];
								$valueC=$productionMovement['product_quantity']*$productionMovement['product_unit_price'];
								break;
						}
					}
				}
				if (($quantityA+$quantityB+$quantityC)>0){
					$productionData[$i]['ProductionRun'][$productionCount]['id']=$productionRunId;
					$productionData[$i]['ProductionRun'][$productionCount]['productionrundate']=$productionRun['ProductionRun']['production_run_date'];
					$productionData[$i]['ProductionRun'][$productionCount]['quantityA']=$quantityA;
					$productionData[$i]['ProductionRun'][$productionCount]['quantityB']=$quantityB;
					$productionData[$i]['ProductionRun'][$productionCount]['quantityC']=$quantityC;
					$productionData[$i]['ProductionRun'][$productionCount]['rawUsed']=$rawMaterialQuantity;
					$productionData[$i]['ProductionRun'][$productionCount]['valueA']=$valueA;
					$productionData[$i]['ProductionRun'][$productionCount]['valueB']=$valueB;
					$productionData[$i]['ProductionRun'][$productionCount]['valueC']=$valueC;
					$productionData[$i]['ProductionRun'][$productionCount]['valueTotal']=$valueA+$valueB+$valueC;
				}
				$productionCount++;
			}			
			$i++;
		}
		$this->set(compact('finishedProduct','productionData','startDate','endDate','allProductionResultCodes'));
	}
	
	public function guardarReporteProductoFabricado($productname) {
		$exportData=$_SESSION['fabricatedProductReport'];
		$this->set(compact('exportData','productname'));
	}
	
	public function guardarReporteSalidasMateriaPrima() {
		$exportData=$_SESSION['rawMaterialExitReport'];
		$this->set(compact('exportData'));
	}

  public function volumenesVentas(){
    $this->loadModel('ClosingDate');
    $this->loadModel('ProductPriceLog');
    $this->loadModel('ProductType');
    $this->loadModel('ProductCategory');
    $this->loadModel('ProductThresholdVolume');
    $this->loadModel('Currency');
    $this->loadModel('ThirdParty');
    
    $this->loadModel('PriceClientCategory');
    $this->loadModel('StockItem');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
        
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $warehouseId=0;
    $productCategoryId=CATEGORY_PRODUCED;
    
    $existenceOptions=[
      0=>'Mostrar solamente productos con existencia',
      1=>'Mostrar todos productos',
    ];
    $this->set(compact('existenceOptions'));
    define('SHOW_EXISTING','0');
    define('SHOW_ALL','1');
    $existenceOptionId=0;
    
    if ($this->request->is('post')) {
      $volumeDateTimeArray=$this->request->data['Product']['volume_datetime'];
      $volumeDateTimeAsString=$this->ProductThresholdVolume->deconstruct('volume_datetime',$this->request->data['Product']['volume_datetime']);
      $volumeDateTime=date( "Y-m-d H:i:s", strtotime($volumeDateTimeAsString)); 
      
      $warehouseId=$this->request->data['Product']['warehouse_id']; 
      $productCategoryId=$this->request->data['Product']['product_category_id'];
      $existenceOptionId=$this->request->data['Product']['existence_option_id'];
		}
    elseif (!empty($_SESSION['volumeDateTime'])){
      $volumeDateTime=$_SESSION['volumeDateTime'];
    }
		else {
			$volumeDateTime=date("Y-m-d H:i:s");
		}
    //pr($volumeDateTime);
    $volumeDateTimeAsString=$volumeDateTime;
    $volumeDateTime=date( "Y-m-d H:i:s", strtotime($volumeDateTimeAsString));
    $volumeDateTimePlusOne= date( "Y-m-d", strtotime( $volumeDateTime."+1 days" ) );
    $_SESSION['volumeDateTime']=$volumeDateTime;
		//pr($volumeDateTime);
    $this->set(compact('volumeDateTime'));
    //pr($volumeDateTime);
    $volumeDate=date( "Y-m-d", strtotime($volumeDateTimeAsString));
    
    $this->set(compact('productCategoryId'));
    $this->set(compact('existenceOptionId'));
    
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
    
    $priceClientCategories=$this->PriceClientCategory->find('list',[
      'order'=>'PriceClientCategory.category_number ASC'
    ]);
    $this->set(compact('priceClientCategories'));
    
    $productTypeConditions=[];
    if ($productCategoryId == 0){
      $productTypeConditions['ProductType.product_category_id !=']=[CATEGORY_RAW,CATEGORY_CONSUMIBLE];
    }
    else{
      $productTypeConditions['ProductType.product_category_id']=$productCategoryId;
      $productTypeConditions['ProductType.id !=']=[PRODUCT_TYPE_SERVICE];
    }
    $productTypeList=$this->ProductType->find('list',[
      'conditions'=>$productTypeConditions,
      'order'=>['ProductType.product_category_id ASC','ProductType.id ASC']
    ]);
    $productTypeIds=array_keys($productTypeList);
    $productTypes=[];
    foreach ($productTypeList as $productTypeId=>$productTypeName){
      $existences=$this->StockItem->getAllProductCombinations($warehouseId,$productTypeId,!$existenceOptionId);
      $productTypes[$productTypeId]=[
        'ProductType'=>[
          'name'=>$productTypeName,
          'productIds'=>$existences['productIds'],
          'rawMaterialIds'=>$existences['rawMaterialIds'],
        ],
        'existences'=>$existences['existences'],
      ];
    }
    //pr($productTypes);

    $boolSaved='0';
    if ($this->request->is('post') && empty($this->request->data['changeDate'])) {	      
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDate=new DateTime($latestClosingDate);
      
      if ($volumeDateTimeAsString>date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('Los volumenes no se pueden registrar en el futuro!  No se guardaron los volumenes.'), 'default',['class' => 'error-message']);
      }
      elseif ($volumeDateTimeAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardaron los volumenes.'), 'default',['class' => 'error-message']);
      }
      else {
        //pr($this->request->data);
        $datasource=$this->ProductThresholdVolume->getDataSource();
        $datasource->begin();
        
        foreach ($productTypes as $productTypeId => $productTypeData){
          foreach ($productTypeData['existences']['Product'] as $productId=>$productData){
            if ($productTypeId == PRODUCT_TYPE_BOTTLE){
              foreach($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['threshold_volume']=$this->ProductThresholdVolume->getCompositeThresholdVolume($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_VOLUME,$volumeDateTime);
              
                $productPriceLogCategoryOne=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_GENERAL,$volumeDateTime);
                $productPriceLogCategoryTwo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_TWO,$volumeDateTime);
                $productPriceLogCategoryThree=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_VOLUME,$volumeDateTime);
                
                //pr($productPriceLogCategoryOne);
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_category_one']=$productPriceLogCategoryOne=$productPriceLogCategoryOne['price'];
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_category_two']=$productPriceLogCategoryTwo['price'];
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_category_three']=$productPriceLogCategoryThree['price'];
              }
            }  
            else {
              $productTypes[$productTypeId]['existences']['Product'][$productId]['threshold_volume']=$this->ProductThresholdVolume->getThresholdVolume($productId,PRICE_CLIENT_CATEGORY_VOLUME,$volumeDateTime);
              
              $productPriceLogCategoryOne=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,PRICE_CLIENT_CATEGORY_GENERAL,$volumeDateTime);
              $productPriceLogCategoryTwo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,PRICE_CLIENT_CATEGORY_TWO,$volumeDateTime);
              $productPriceLogCategoryThree=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,PRICE_CLIENT_CATEGORY_VOLUME,$volumeDateTime);
              
              //pr($productPriceLogCategoryOne);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['price_category_one']=$productPriceLogCategoryOne=$productPriceLogCategoryOne['price'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['price_category_two']=$productPriceLogCategoryTwo['price'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['price_category_three']=$productPriceLogCategoryThree['price'];
            }
          }
        }
    
        
        try {
          foreach ($this->request->data['ProductType'] as $requestProductTypeId=>$requestProductTypeData){
            //pr($requestProductTypeData);
            if (array_key_exists('Product',$requestProductTypeData)){
              foreach ($requestProductTypeData['Product'] as $requestProductId=>$requestProductData){
                //echo "productTypeId is ".$requestProductId."<br/>";
                //pr($requestProductData);
                if ($requestProductTypeId == PRODUCT_TYPE_BOTTLE){
                  //if ($requestProductId == 40){
                  //  pr ($requestRawMaterialData);
                  //}
                  foreach ($requestProductData['RawMaterial'] as $requestRawMaterialId=>$requestRawMaterialData){
                    if (
                      (
                        !array_key_exists($requestProductId,$productTypes[$requestProductTypeId]['existences']['Product'])
                        || !array_key_exists('RawMaterial',$productTypes[$requestProductTypeId]['existences']['Product'][$requestProductId])
                        || !array_key_exists($requestRawMaterialId,$productTypes[$requestProductTypeId]['existences']['Product'][$requestProductId]['RawMaterial'])
                        || $requestRawMaterialData['threshold_volume'] != $productTypes[$requestProductTypeId]['existences']['Product'][$requestProductId]['RawMaterial'][$requestRawMaterialId]['threshold_volume']
                      ) 
                      && $requestRawMaterialData['threshold_volume']>0
                    ){                  
                      $productThresholdVolumeData=[
                        'ProductThresholdVolume'=>[
                          'volume_datetime'=>$volumeDateTime,
                          'currency_id'=>CURRENCY_CS,
                          'user_id'=>$loggedUserId,
                          'price_client_category_id'=>PRICE_CLIENT_CATEGORY_VOLUME,
                          'product_id'=>$requestProductId,
                          'raw_material_id'=>$requestRawMaterialId,
                          'threshold_volume'=>$requestRawMaterialData['threshold_volume'],
                        ],
                      ];
                      //if ($requestProductId == 40){
                      //  pr ($productThresholdVolumeData);
                      //}
                      $this->ProductThresholdVolume->create();
                      if (!$this->ProductThresholdVolume->save($productThresholdVolumeData)) {
                        echo "Problema guardando el volumen de ventas para producto ".$requestProductId;
                        pr($this->validateErrors($this->ProductThresholdVolume));
                        throw new Exception();
                      }
                    }
                    if (
                      (
                        !array_key_exists($requestProductId,$productTypes[$requestProductTypeId]['existences']['Product'])
                        || !array_key_exists('RawMaterial',$productTypes[$requestProductTypeId]['existences']['Product'][$requestProductId])
                        || !array_key_exists($requestRawMaterialId,$productTypes[$requestProductTypeId]['existences']['Product'][$requestProductId]['RawMaterial'])
                        || $requestRawMaterialData['price_category_volume'] != $productTypes[$requestProductTypeId]['existences']['Product'][$requestProductId]['RawMaterial'][$requestRawMaterialId]['price_category_three']
                      ) 
                      && $requestRawMaterialData['price_category_volume']>0
                    ){                  
                      $productPriceLogData=[
                        'ProductPriceLog'=>[
                          'price_datetime'=>$volumeDateTimeAsString,
                          'user_id'=>$loggedUserId,
                          'price_client_category_id'=>PRICE_CLIENT_CATEGORY_VOLUME,
                          'product_id'=>$requestProductId,
                          'raw_material_id'=>$requestRawMaterialId,
                          'price'=>$requestRawMaterialData['price_category_volume'],
                        ],
                      ];
                      $this->ProductPriceLog->create();
                      if (!$this->ProductPriceLog->save($productPriceLogData)) {
                        echo "Problema guardando el precio del producto ".$requestProductId;
                        pr($this->validateErrors($this->ProductPriceLog));
                        throw new Exception();
                      }
                    }
                }
                }
                else {
                  pr($requestProductData);  
                  if (
                    (
                      !array_key_exists($requestProductId,$productTypes[$requestProductTypeId]['existences']['Product'])
                      || $requestRawMaterialData['threshold_volume'] != $productTypes[$requestProductTypeId]['existences']['Product'][$requestProductId]['threshold_volume']
                    ) 
                    && $requestProductData['threshold_volume']>0
                  ){                  
                    $productThresholdVolumeData=[
                      'ProductThresholdVolume'=>[
                        'volume_datetime'=>$volumeDateTimeAsString,
                        'user_id'=>$loggedUserId,
                        'price_client_category_id'=>PRICE_CLIENT_CATEGORY_VOLUME,
                        'product_id'=>$requestProductId,
                        'raw_material_id'=>null,
                        'threshold_volume'=>$requestProductData['threshold_volume'],
                      ],
                    ];
                    $this->ProductThresholdVolume->create();
                    if (!$this->ProductThresholdVolume->save($productThresholdVolumeData)) {
                      echo "Problema guardando el volumen de ventas para producto ".$requestProductId;
                      pr($this->validateErrors($this->ProductThresholdVolume));
                      throw new Exception();
                    }
                  }
                  if (
                    (
                      !array_key_exists($requestProductId,$productTypes[$requestProductTypeId]['existences']['Product'])
                      || $requestRawMaterialData['price_category_volume'] != $productTypes[$requestProductTypeId]['existences']['Product'][$requestProductId]['price_category_three']
                    ) 
                    && $requestProductData['price_category_volume']>0
                  ){                  
                    $productPriceLogData=[
                      'ProductPriceLog'=>[
                        'price_datetime'=>$volumeDateTimeAsString,
                        'currency_id'=>CURRENCY_CS,
                        'user_id'=>$loggedUserId,
                        'price_client_category_id'=>PRICE_CLIENT_CATEGORY_VOLUME,
                        'product_id'=>$requestProductId,
                        'raw_material_id'=>null,
                        'price'=>$requestProductData['price_category_volume'],
                      ],
                    ];
                    $this->ProductPriceLog->create();
                    if (!$this->ProductPriceLog->save($productPriceLogData)) {
                      echo "Problema guardando el precio del producto ".$requestProductId;
                      pr($this->validateErrors($this->ProductPriceLog));
                      throw new Exception();
                    }
                  }
                } 
              }  
              
            }
          }                
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),"Se registraron los volumenes de venta para fecha ".$volumeDateTimeAsString);
          $this->Session->setFlash("Se registraron los volumenes de venta para fecha ".$volumeDateTimeAsString,'default',['class' => 'success'],'default',['class' => 'success']);
          $boolSaved=true;
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash("No se podían registrar los volumenes para fecha ".$volumeDateTimeAsString, 'default',['class' => 'error-message']);
        }
      }	
    
    }
    $this->set(compact('boolSaved'));
    
    foreach ($productTypes as $productTypeId => $productTypeData){
      foreach ($productTypeData['existences']['Product'] as $productId=>$productData){
        if ($productTypeId == PRODUCT_TYPE_BOTTLE){
          foreach($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
            $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['threshold_volume']=$this->ProductThresholdVolume->getCompositeThresholdVolume($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_VOLUME,$volumeDateTime);
          
            $productPriceLogCategoryOne=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_GENERAL,$volumeDateTime);
            $productPriceLogCategoryTwo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_TWO,$volumeDateTime);
            $productPriceLogCategoryThree=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_VOLUME,$volumeDateTime);
            
            //pr($productPriceLogCategoryOne);
            $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_category_one']=$productPriceLogCategoryOne=$productPriceLogCategoryOne['price'];
            $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_category_two']=$productPriceLogCategoryTwo['price'];
            $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_category_three']=$productPriceLogCategoryThree['price'];
          }
        }  
        else {
          $productTypes[$productTypeId]['existences']['Product'][$productId]['threshold_volume']=$this->ProductThresholdVolume->getThresholdVolume($productId,PRICE_CLIENT_CATEGORY_VOLUME,$volumeDateTime);
          
          $productPriceLogCategoryOne=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,PRICE_CLIENT_CATEGORY_GENERAL,$volumeDateTime);
          $productPriceLogCategoryTwo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,PRICE_CLIENT_CATEGORY_TWO,$volumeDateTime);
          $productPriceLogCategoryThree=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,PRICE_CLIENT_CATEGORY_VOLUME,$volumeDateTime);
          
          //pr($productPriceLogCategoryOne);
          $productTypes[$productTypeId]['existences']['Product'][$productId]['price_category_one']=$productPriceLogCategoryOne=$productPriceLogCategoryOne['price'];
          $productTypes[$productTypeId]['existences']['Product'][$productId]['price_category_two']=$productPriceLogCategoryTwo['price'];
          $productTypes[$productTypeId]['existences']['Product'][$productId]['price_category_three']=$productPriceLogCategoryThree['price'];
        }
      }
    }
    $this->set(compact('productTypes'));
    
    $productCategories=$this->ProductCategory->find('list',[
      'conditions'=>[
        'ProductCategory.id !='=>[CATEGORY_RAW,CATEGORY_CONSUMIBLE],
      ],
    ]);
    $this->set(compact('productCategories'));
    
    $priceClientCategoryColors=$this->PriceClientCategory->find('list',[
      'fields'=>['id','hexcolor'],
    ]);
    $this->set(compact('priceClientCategoryColors'));
    
    $clientPriceClientCategories=$this->ThirdParty->find('list',[
      'fields'=>['id','price_client_category_id'],
    ]);
    $this->set(compact('clientPriceClientCategories'));
    
    $products=$this->Product->getActiveProductsByTypes($productTypeIds);
    $this->set(compact('products'));
    //pr($products);
    $rawMaterials=$this->Product->getActivePreformasAbbreviated();
    $this->set(compact('rawMaterials'));
  }
  
  public function guardarVolumenesVentas($fileName) {
		$exportData=$_SESSION['resumenPrecios'];
		$this->set(compact('exportData','fileName'));
	}
  
}
