<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');


class StockItemsController extends AppController {

	public $components = ['Paginator','RequestHandler'];
	public $helpers = ['PhpExcel']; 
   
  public function beforeFilter(){
		parent::beforeFilter();
		// Allow users to register and logout.
		$this->Auth->allow('getStockItemInfo','saveStockItemInfo','sortByFinishedProduct','sortByRawMaterial');
	} 
   
  public function getStockItemInfo(){
    $stockItemCount=$this->StockItem->find('count');
    $this->Paginator->settings = [
      'fields'=>[ 'StockItem.id','StockItem.name',
                  'StockItem.stockitem_creation_date','StockItem.remaining_quantity'],
      'contain'=>[
        'StockItemLog'=>[
          'fields'=>['StockItemLog.stockitem_date,StockItemLog.product_quantity'],
          'order'=>'StockItemLog.id DESC',
          'limit'=>1,
        ]
      ],
      'limit'=>$stockItemCount,
    ];
    $stockItems = $this->Paginator->paginate('StockItem');;
    $this->set(compact('stockItems'));
  } 
  public function saveStockItemInfo() {
		$exportData=$_SESSION['stockItemInfo'];
		$this->set(compact('exportData'));
	}	
   	
	public function inventario() {
		$this->loadModel('ProductCategory');
		$this->loadModel('ProductType');
		$this->loadModel('StockItemLog');
		
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    $this->loadModel('PlantProductType');

    $this->loadModel('UserPageRight');

    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $canSeeFinanceData=$this->UserPageRight->hasUserPageRight('VER_DATOS_FINANCIEROS',$userRoleId,$loggedUserId,'StockItems','inventario');
    $this->set(compact('canSeeFinanceData'));
    
    $canSeeExecutiveSummary=$this->UserPageRight->hasUserPageRight('VER_RESUMEN_EJECUTIVO',$userRoleId,$loggedUserId,'StockItems','inventario');
    $this->set(compact('canSeeExecutiveSummary'));
    
    $canSeeLink=$this->UserPageRight->hasUserPageRight('VER_LINK',$userRoleId,$loggedUserId,'StockItems','inventario');
    $this->set(compact('canSeeLink'));
    
		$inventoryDate = null;
		$warehouseId=0;
    
    define('DISPLAY_STOCK','0');
    define('DISPLAY_ALL','1');
    $displayOptions=[
      DISPLAY_STOCK=>"Mostrar solo stock",
      DISPLAY_ALL=>"Mostrar todos",
    ];
    $this->set(compact('displayOptions'));
    $displayOptionId=DISPLAY_STOCK;
		
		if ($this->request->is('post')) {
			$inventoryDateArray=$this->request->data['Report']['inventorydate'];
			$inventoryDateString=$inventoryDateArray['year'].'-'.$inventoryDateArray['month'].'-'.$inventoryDateArray['day'];
			$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
			
			$warehouseId=$this->request->data['Report']['warehouse_id'];
      $displayOptionId=$this->request->data['Report']['display_option_id'];
		}
		else if (!empty($_SESSION['inventoryDate'])){
			$inventoryDate=$_SESSION['inventoryDate'];
		}
		else {
			$inventoryDate = date("Y-m-d",strtotime(date("Y-m-d")));
		}
		
    $inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		$_SESSION['inventoryDate']=$inventoryDate;
		
		$this->set(compact('inventoryDate'));
		$this->set(compact('displayOptionId'));
    
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
		
    $productTypesForPlant=$this->PlantProductType->getProductTypesForPlant($plantId);
    
		$productCategories=$this->ProductCategory->find('all',[
			'contain'=>[
				'ProductType'=>[
          'conditions'=>[
            'ProductType.id'=>array_keys($productTypesForPlant),
          ],
        ],
			],
		]);
		/*
		for ($pc=0;$pc<count($productCategories);$pc++){
			for ($pt=0;$pt<count($productCategories[$pc]['ProductType']);$pt++){
				//pr($productCategories[$pc]['ProductType'][$pt]);
				$conditions=array('Product.product_type_id'=> $productCategories[$pc]['ProductType'][$pt]['id']);
				if ($warehouseId>0){
					$conditions[]=array('StockItem.warehouse_id'=>$warehouseId,);
				}
				switch ($productCategories[$pc]['ProductType'][$pt]['id']){
					case PRODUCT_TYPE_PREFORMA:
						$this->StockItem->recursive=0;
						
						$preformaCount=	$this->StockItem->find('count', array(
							'fields'=>array(
								'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
							),
							'conditions' => $conditions,
							'group'=>'Product.name',
						));
						$this->StockItem->recursive=-1;
						$this->Paginator->settings = array(
							'fields'=>array(
								'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
							),
							'conditions' => $conditions,
							'contain'=>array(
								'Product'=>array(
									'fields'=>array('Product.name','Product.id','Product.packaging_unit','Product.product_type_id'),
								),
								'ProductionResultCode'=>array(
									'fields'=>array('ProductionResultCode.code'),
								),
								'RawMaterial'=>array(
									'fields'=>array('RawMaterial.name'),
								),
							),
							'order'=>'Product.name',
							'group'=>'Product.name',
							'limit'=>$preformaCount,
						);
						$preformas = $this->Paginator->paginate('StockItem');
						
						// now overwrite based on StockItemLogs
						for ($i=0;$i<count($preformas);$i++){
							$this->StockItem->recursive=-1;
							$allStockItems=$this->StockItem->find('all',array(
								'fields'=>array('StockItem.id'),
								'conditions'=>array('StockItem.product_id'=>$preformas[$i]['Product']['id']),
							));
							
							$totalStockInventoryDate=0;
							$totalValueInventoryDate=0;
							if (count($allStockItems)>0){
								$lastStockItemLog=array();
								foreach ($allStockItems as $stockitem){				
									$this->StockItemLog->recursive=-1;
									$lastStockItemLog=$this->StockItemLog->find('first',array(
										'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
										'conditions'=>array(
											'StockItemLog.stockitem_id'=>$stockitem['StockItem']['id'],
											'StockItemLog.stockitem_date <='=>$inventoryDatePlusOne,
										),
										'order'=>'StockItemLog.id DESC',
									));
									if (count($lastStockItemLog)>0){
										$totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
										$totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
									}
								}
							}
							$preformas[$i][0]['Remaining']=$totalStockInventoryDate;
							$preformas[$i][0]['Saldo']=$totalValueInventoryDate;
						}
						$this->set(compact('preformas'));
						break;
					
					case PRODUCT_TYPE_BOTTLE:
						$this->StockItem->recursive=0;
						$bottleCount =$this->StockItem->find('count', array(
							'fields'=>array(
								'(SELECT SUM(`stock_items`.`remaining_quantity`) 
									FROM stock_items 
									right outer JOIN `products` ON (`stock_items`.`product_id` = `products`.`id`) 
									WHERE stock_items.product_id=Product.id
										AND stock_items.raw_material_id = RawMaterial.id
								) AS Remaining',
								'(SELECT SUM(`stock_items`.`remaining_quantity`*`stock_items`.`product_unit_price`) 
									FROM stock_items 
									right outer JOIN `products` ON (`stock_items`.`product_id` = `products`.`id`) 
									WHERE stock_items.product_id=Product.id
										AND stock_items.raw_material_id = RawMaterial.id
								) AS Saldo'
							),
							'conditions' => $conditions,
							'group'=>'Product.name,RawMaterial.name',
						));
						$this->StockItem->recursive=-1;
						$this->Paginator->settings = array(
							'fields'=>array(
								'(SELECT SUM(`stock_items`.`remaining_quantity`) 
									FROM stock_items 
									right outer JOIN `products` ON (`stock_items`.`product_id` = `products`.`id`) 
									WHERE stock_items.product_id=Product.id
										AND stock_items.raw_material_id = RawMaterial.id
								) AS Remaining',
								'(SELECT SUM(`stock_items`.`remaining_quantity`*`stock_items`.`product_unit_price`) 
									FROM stock_items 
									right outer JOIN `products` ON (`stock_items`.`product_id` = `products`.`id`) 
									WHERE stock_items.product_id=Product.id
										AND stock_items.raw_material_id = RawMaterial.id
								) AS Saldo'
							),
							'conditions' => $conditions,
							'contain'=>array(
								'Product'=>array(
									'fields'=>array('Product.name','Product.id','Product.packaging_unit','Product.product_type_id'),
								),
								'ProductionResultCode'=>array(
									'fields'=>array('ProductionResultCode.id','ProductionResultCode.code'),
								),
								'RawMaterial'=>array(
									'fields'=>array('RawMaterial.name'),
								),
							),
							'group'=>'Product.name,RawMaterial.name',
							'order'=>'ISNULL(RawMaterial.name),RawMaterial.name ASC, Product.name ASC',
							'limit'=>$bottleCount
						);
						$productos = $this->Paginator->paginate('StockItem');
						// now overwrite based on StockItemLogs
						for ($i=0;$i<count($productos);$i++){
							$this->StockItem->recursive=-1;
							for ($productionresultcode=1;$productionresultcode<4;$productionresultcode++){
								$allStockItems=$this->StockItem->find('all',array(
									'fields'=>array('StockItem.id'),
									'conditions'=>array(
										'StockItem.product_id'=>$productos[$i]['Product']['id'],
										'StockItem.production_result_code_id'=>$productionresultcode,
										'StockItem.raw_material_id'=>$productos[$i]['RawMaterial']['id'],
									),
								));
								
								$totalStockInventoryDate=0;
								$totalValueInventoryDate=0;
								//echo "inventory date plus one is ".$inventoryDatePlusOne."<br/>";
								if (count($allStockItems)>0){
									$lastStockItemLog=array();
									foreach ($allStockItems as $stockitem){	
										$this->StockItemLog->recursive=-1;
										$lastStockItemLog=$this->StockItemLog->find('first',array(
											'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
											'conditions'=>array(
												'StockItemLog.stockitem_id'=>$stockitem['StockItem']['id'],
												'StockItemLog.stockitem_date <='=>$inventoryDatePlusOne,
											),
											'order'=>'StockItemLog.id DESC',
										));
									
										if (count($lastStockItemLog)>0){
											$totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
											$totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
										}
									}
								}
								switch ($productionresultcode){
									case PRODUCTION_RESULT_CODE_A:
										$productos[$i][0]['Remaining_A']=$totalStockInventoryDate;
										$productos[$i][0]['Saldo_A']=$totalValueInventoryDate;
										break;
									case PRODUCTION_RESULT_CODE_B:
										$productos[$i][0]['Remaining_B']=$totalStockInventoryDate;
										$productos[$i][0]['Saldo_B']=$totalValueInventoryDate;
										break;
									case PRODUCTION_RESULT_CODE_C:
										$productos[$i][0]['Remaining_C']=$totalStockInventoryDate;
										$productos[$i][0]['Saldo_C']=$totalValueInventoryDate;
										break;
								}
							}
						}
						$this->set(compact('productos'));
						break;
					case PRODUCT_TYPE_CAP:
						$this->StockItem->recursive=0;
						$taponesCount= $this->StockItem->find('count', array(
							'fields'=>array(
								'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
							),
							'conditions' => $conditions,
							'group'=>'Product.name',
						));
						$this->StockItem->recursive=-1;
						$this->Paginator->settings = array(
							'fields'=>array(
								'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
							),
							'conditions' => $conditions,
							'contain'=>array(
								'Product'=>array(
									'fields'=>array('Product.name','Product.id','Product.packaging_unit','Product.product_type_id'),
								),
								'ProductionResultCode'=>array(
									'fields'=>array('ProductionResultCode.id','ProductionResultCode.code'),
								),
								'RawMaterial'=>array(
									'fields'=>array('RawMaterial.name'),
								),
							),
							'group'=>'Product.name',
							'order'=>'Product.name',
							'limit'=>$taponesCount,
						);
						$tapones = $this->Paginator->paginate('StockItem');
						// now overwrite based on StockItemLogs
						$this->StockItem->recursive=-1;
						for ($i=0;$i<count($tapones);$i++){
							$allStockItems=$this->StockItem->find('all',array(
								'fields'=>array('StockItem.id'),
								'conditions'=>array('StockItem.product_id'=>$tapones[$i]['Product']['id']),
							));
							
							$totalStockInventoryDate=0;
							$totalValueInventoryDate=0;
							if (count($allStockItems)>0){
								$lastStockItemLog=array();
								foreach ($allStockItems as $stockitem){		
									$this->StockItemLog->recursive=-1;
									$lastStockItemLog=$this->StockItemLog->find('first',array(
										'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
										'conditions'=>array(
											'StockItemLog.stockitem_id'=>$stockitem['StockItem']['id'],
											'StockItemLog.stockitem_date <='=>$inventoryDatePlusOne,
										),
										'order'=>'StockItemLog.id DESC',
									));
									if (count($lastStockItemLog)>0){
										$totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
										$totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
									}
								}
							}
							$tapones[$i][0]['Remaining']=$totalStockInventoryDate;
							$tapones[$i][0]['Saldo']=$totalValueInventoryDate;
						}
						$this->set(compact('tapones'));
						break;
				}
			}
		}
		
		*/
		
    $otherProducts=[];
    if ($warehouseId >0){
      for ($pc=0;$pc<count($productCategories);$pc++){
        $categoryOtherTypesArray=[];
        $categoryOtherTypesArray['ProductCategory']=$productCategories[$pc]['ProductCategory'];
        for ($pt=0;$pt<count($productCategories[$pc]['ProductType']);$pt++){
          //pr($productCategories[$pc]['ProductType'][$pt]);
          /*
          if ($productCategories[$pc]['ProductType'][$pt]['id'] == PRODUCT_TYPE_BOTTLE){
              $productos=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
              $this->set(compact('productos'));
          }
          */
          switch ($productCategories[$pc]['ProductType'][$pt]['id']){
            case PRODUCT_TYPE_PREFORMA:
              $preformas=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
              $this->set(compact('preformas'));
              break;
            case PRODUCT_TYPE_BOTTLE:
              $productos=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
              $this->set(compact('productos'));
              break;
            case PRODUCT_TYPE_CAP:
              $tapones=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
              $this->set(compact('tapones'));
              break;
            default:
			
              if (!in_array($productCategories[$pc]['ProductType'][$pt]['id'],[PRODUCT_TYPE_SERVICE,PRODUCT_TYPE_ROLL])){
                $productTypeArray=[];
				//echo $productCategories[$pc]['ProductType'][$pt]['id'];exit;
				/*if($productCategories[$pc]['ProductType'][$pt]['id']==18)
				{*/
                $productTypeArray['ProductType']=$productCategories[$pc]['ProductType'][$pt];
                $productTypeArray['Products']=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
                $categoryOtherTypesArray['ProductLines'][]=$productTypeArray;
               // }
			 
			  }
          }
        }
        $otherProducts[]=$categoryOtherTypesArray;
      }
    }
    $this->set(compact('otherProducts'));
		//pr($otherProducts);
		
		$filename="Hoja_Inventario_".date('d_m_Y'); 
		$this->set(compact('filename'));
	}

	public function guardarReporteInventario() {
		$exportData=$_SESSION['inventoryReport'];
		$this->set(compact('exportData'));
	}	

  public function ajustesInventario($warehouseId=0,$productTypeId=0) {
		$this->loadModel('ProductCategory');
		$this->loadModel('ProductType');
		$this->loadModel('StockItemLog');
		
		$inventoryDate = null;
    if ($warehouseId == 0){
      $warehouseId=WAREHOUSE_DEFAULT;
    }
    define('DISPLAY_STOCK','0');
    define('DISPLAY_ALL','1');
    //$displayOptions=[
    //  DISPLAY_STOCK=>"Mostrar solo stock",
    //  DISPLAY_ALL=>"Mostrar todos",
    //];
    //$this->set(compact('displayOptions'));
    $displayOptionId=DISPLAY_STOCK;
		
		if ($this->request->is('post')) {
			$inventoryDateArray=$this->request->data['Report']['inventorydate'];
			$inventoryDateString=$inventoryDateArray['year'].'-'.$inventoryDateArray['month'].'-'.$inventoryDateArray['day'];
			$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
			
			$warehouseId=$this->request->data['Report']['warehouse_id'];
      $productTypeId=$this->request->data['Report']['product_type_id'];
      //$displayOptionId=$this->request->data['Report']['display_option_id'];
		}
		else if (!empty($_SESSION['inventoryDate'])){
			$inventoryDate=$_SESSION['inventoryDate'];
		}
		else {
			$inventoryDate = date("Y-m-d",strtotime(date("Y-m-d")));
		}
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		$_SESSION['inventoryDate']=$inventoryDate;
		
		$this->set(compact('inventoryDate'));
		$this->set(compact('warehouseId'));
    $this->set(compact('productTypeId'));
    $this->set(compact('displayOptionId'));
		
		$this->ProductType->recursive=-1;
		
    $productTypeConditions=[
      'ProductType.id !='=>PRODUCT_TYPE_SERVICE,
    ];
    if ($productTypeId>0){
      $productTypeConditions[]=['ProductType.id'=>$productTypeId];
    }
    
		$allInventoryProductTypes=$this->ProductType->find('all',[
      'conditions'=>$productTypeConditions,
      'contain'=>[
        'ProductCategory',
      ],
    ]);
		for ($pt=0;$pt<count($allInventoryProductTypes);$pt++){
      //pr($productCategories[$pc]['ProductType'][$pt]);
      $productTypeId=$allInventoryProductTypes[$pt]['ProductType']['id'];
      switch ($productTypeId){
        case PRODUCT_TYPE_BOTTLE:
          $bottles=$this->StockItem->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
          break;
        case PRODUCT_TYPE_PREFORMA:
        case PRODUCT_TYPE_CAP:
        default:
          $products=$this->StockItem->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
          
          $allInventoryProductTypes[$pt]['products']=$products;
      }
    }
    $this->set(compact('bottles'));
    $this->set(compact('allInventoryProductTypes'));
    //pr($allInventoryProductTypes);
				
		$this->loadModel('Warehouse');
		$warehouses=$this->Warehouse->find('list',['order'=>'Warehouse.name',]);
		$this->set(compact('warehouses'));
    
    $productTypes=$this->ProductType->find('list',[
      'fields'=>['ProductType.id','ProductType.name'],
			'conditions'=>['ProductType.id !='=>PRODUCT_TYPE_SERVICE],
      'order'=>'ProductType.name',
		]);
    $this->set(compact('productTypes'));
	}
  
  public function comprobanteAjustesInventario($warehouseId=0,$productTypeId=0) {
		$this->loadModel('Product');
    $this->loadModel('ProductType');
    
		$this->loadModel('StockMovement');
    
    $this->loadModel('Warehouse');
		
		$inventoryDate = null;
		$warehouseId=WAREHOUSE_DEFAULT;
    
    define('DISPLAY_STOCK','0');
    $displayOptionId=DISPLAY_STOCK;
		
		if ($this->request->is('post')) {
      $startDateArray=$this->request->data['Report']['start_date'];
      $startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date("Y-m-d", strtotime($startDateString));
			
			$inventoryDateArray=$this->request->data['Report']['inventorydate'];
			$inventoryDateString=$inventoryDateArray['year'].'-'.$inventoryDateArray['month'].'-'.$inventoryDateArray['day'];
			$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
			
			$warehouseId=$this->request->data['Report']['warehouse_id'];
      //$productTypeId=$this->request->data['Report']['product_type_id'];
		}
		else if (!empty($_SESSION['inventoryDate'])){
			$startDate=$_SESSION['startDate'];
      $inventoryDate=$_SESSION['inventoryDate'];
		}
		else {
			$startDate = date("Y-m-d",strtotime(date("Y-m-01")));
      $inventoryDate = date("Y-m-d",strtotime(date("Y-m-d")));
		}
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		$_SESSION['startDate']=$startDate;
    $_SESSION['inventoryDate']=$inventoryDate;
		
    $this->set(compact('startDate'));
		$this->set(compact('inventoryDate'));
		$this->set(compact('warehouseId'));
    //$this->set(compact('productTypeId'));
    $this->set(compact('displayOptionId'));
    //echo 'startDate is '.$startDate.'<br/>';
    $productTypeConditions=[
      'ProductType.id !='=>PRODUCT_TYPE_SERVICE,
      'ProductType.id !='=>PRODUCT_TYPE_INJECTION_OUTPUT,
    ];
    /*
    if ($productTypeId>0){
      $productTypeConditions[]=['ProductType.id'=>$productTypeId];
    }
    */
    $allInventoryProductTypes=$this->ProductType->find('list',[
      'fields'=>['ProductType.id','ProductType.name'],
      'conditions'=>$productTypeConditions,
    ]);
    $productTypeProducts=[];
		foreach ($allInventoryProductTypes as $productTypeId => $productTypeName){
      switch ($productTypeId){
        case PRODUCT_TYPE_BOTTLE:
          $products=$this->StockItem->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
          //pr($products);
          break;
        case PRODUCT_TYPE_INJECTION_OUTPUT:
          // PENDING
          break;
        case PRODUCT_TYPE_PREFORMA:
        case PRODUCT_TYPE_CAP:
        default:
          $products=$this->StockItem->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
      }
      $productTypeProducts[$productTypeId]=[
        'name'=>$productTypeName,
        'Products'=>$products,
      ];
    }
    $this->set(compact('productTypeProducts'));
    
    $displayedAdjustments=[
      'BOTTLES_A',
      'BOTTLES_IMPORT',
      'BOTTLES_B',
      'CAPS',
      'CAPS_IMPORT',
      'RAWMATERIAL',
      'BAGS',
    ];
    $this->set(compact('displayedAdjustments'));
    
    $adjustedProducts=[];
    $generalAdjustmentMovementConditions=[
      'StockMovement.movement_date >='=>$startDate,
      'StockMovement.movement_date <'=>$inventoryDatePlusOne,
      'bool_adjustment'=>true,
    ];
    //pr($generalAdjustmentMovementConditions);
    foreach ($displayedAdjustments as $adjustmentName){
      switch ($adjustmentName){
        case 'BOTTLES_A':
          $relevantProducts=[];
          foreach ($productTypeProducts[PRODUCT_TYPE_BOTTLE]['Products'] as $currentProduct){
            //pr($currentProduct);
            if ($currentProduct[0]['Remaining_A'] >0){
              $adjustmentMovementConditions=$generalAdjustmentMovementConditions;
              $adjustmentMovementConditions['StockMovement.product_id']=$currentProduct['Product']['id'];
              $adjustmentMovementConditions['StockMovement.production_result_code_id']=PRODUCTION_RESULT_CODE_A;
              //pr($adjustmentMovementConditions);
              $adjustmentMovements=$this->StockMovement->find('all',[
                'conditions'=>$adjustmentMovementConditions,
                'contain'=>[
                  'StockItem'=>[
                    'conditions'=>['StockItem.raw_material_id' => $currentProduct['RawMaterial']['id']],
                  ],
                ],
              ]);
              //pr($adjustmentMovements);
              $adjusted=[
                'quantity_up'=>0,
                'value_up'=>0,
                'quantity_down'=>0,
                'value_down'=>0,
              ];
              $adjustedDown=0;
              foreach ($adjustmentMovements as $adjustmentMovement){
                //pr($currentProduct);
                //pr($adjustmentMovement);
                //echo 'product rawmaterialid is '.$currentProduct['RawMaterial']['id'].'<br/>';
                //echo 'stockitem rawmaterialid is '.$adjustmentMovement['StockItem']['raw_material_id'].'<br/>';
                if (!empty($adjustmentMovement['StockItem']['id'])){
                  if ($adjustmentMovement['StockMovement']['bool_input']){
                    $adjusted['quantity_up']+=$adjustmentMovement['StockMovement']['product_quantity'];
                    $adjusted['value_up']+=$adjustmentMovement['StockMovement']['product_total_price'];
                  }
                  else {
                    $adjusted['quantity_down']+=$adjustmentMovement['StockMovement']['product_quantity'];
                    $adjusted['value_down']+=$adjustmentMovement['StockMovement']['product_total_price'];
                  }
                }
              }
              
              $relevantProducts[]=[
                'product_name'=>$currentProduct['Product']['name'],
                'product_id'=>$currentProduct['Product']['id'],
                'raw_material_name'=>$currentProduct['RawMaterial']['name'],
                'raw_material_abbreviation'=>$currentProduct['RawMaterial']['abbreviation'],
                'raw_material_id'=>$currentProduct['RawMaterial']['id'],
                'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
                'quantity'=>$currentProduct['0']['Remaining_A'],
                'value'=>$currentProduct['0']['Saldo_A'],
                'adjusted'=>$adjusted,
              ];
            }
          }
          $adjustedProducts[$adjustmentName]=[
            'name'=>'Botellas clase A',
            'Product'=>$relevantProducts,
          ];
          break;
        case 'BOTTLES_B':
          $relevantProducts=[];
          foreach ($productTypeProducts[PRODUCT_TYPE_BOTTLE]['Products'] as $currentProduct){
            //pr($currentProduct);
            if ($currentProduct[0]['Remaining_B'] >0){
              $adjustmentMovementConditions=$generalAdjustmentMovementConditions;
              $adjustmentMovementConditions['StockMovement.product_id']=$currentProduct['Product']['id'];
              $adjustmentMovementConditions['StockMovement.production_result_code_id']=PRODUCTION_RESULT_CODE_B;
              //pr($adjustmentMovementConditions);
              $adjustmentMovements=$this->StockMovement->find('all',[
                'conditions'=>$adjustmentMovementConditions,
                'contain'=>[
                  'StockItem'=>[
                    'conditions'=>['StockItem.raw_material_id' => $currentProduct['RawMaterial']['id']],
                  ],
                ],
              ]);
              //pr($adjustmentMovements);
              $adjusted=[
                'quantity_up'=>0,
                'value_up'=>0,
                'quantity_down'=>0,
                'value_down'=>0,
              ];
              $adjustedDown=0;
              foreach ($adjustmentMovements as $adjustmentMovement){
                //pr($currentProduct);
                //pr($adjustmentMovement);
                //echo 'product rawmaterialid is '.$currentProduct['RawMaterial']['id'].'<br/>';
                //echo 'stockitem rawmaterialid is '.$adjustmentMovement['StockItem']['raw_material_id'].'<br/>';
                if (!empty($adjustmentMovement['StockItem']['id'])){
                  if ($adjustmentMovement['StockMovement']['bool_input']){
                    $adjusted['quantity_up']+=$adjustmentMovement['StockMovement']['product_quantity'];
                    $adjusted['value_up']+=$adjustmentMovement['StockMovement']['product_total_price'];
                  }
                  else {
                    $adjusted['quantity_down']+=$adjustmentMovement['StockMovement']['product_quantity'];
                    $adjusted['value_down']+=$adjustmentMovement['StockMovement']['product_total_price'];
                  }
                }
              }
              
              $relevantProducts[]=[
                'product_name'=>$currentProduct['Product']['name'],
                'product_id'=>$currentProduct['Product']['id'],
                'raw_material_name'=>$currentProduct['RawMaterial']['name'],
                'raw_material_abbreviation'=>$currentProduct['RawMaterial']['abbreviation'],
                'raw_material_id'=>$currentProduct['RawMaterial']['id'],
                'production_result_code_id'=>PRODUCTION_RESULT_CODE_B,
                'quantity'=>$currentProduct['0']['Remaining_B'],
                'value'=>$currentProduct['0']['Saldo_B'],
                'adjusted'=>$adjusted,
              ];
            }
          }
          $adjustedProducts[$adjustmentName]=[
            'name'=>'Botellas clase B',
            'Product'=>$relevantProducts,
          ];
          break;
        case 'BOTTLES_IMPORT':
          $relevantProducts=[];
          foreach ($productTypeProducts[PRODUCT_TYPE_IMPORT]['Products'] as $currentProduct){
            //pr($currentProduct);
            $productNatureId=$this->Product->getProductNatureId($currentProduct['Product']['id']);
            if ($productNatureId == PRODUCT_NATURE_BOTTLES_BOUGHT && $currentProduct[0]['Remaining'] >0){
              $adjustmentMovementConditions=$generalAdjustmentMovementConditions;
              $adjustmentMovementConditions['StockMovement.product_id']=$currentProduct['Product']['id'];
              //pr($adjustmentMovementConditions);
              $adjustmentMovements=$this->StockMovement->find('all',[
                'conditions'=>$adjustmentMovementConditions,
                'recursive'=>-1,
              ]);
              //pr($adjustmentMovements);
              $adjusted=[
                'quantity_up'=>0,
                'value_up'=>0,
                'quantity_down'=>0,
                'value_down'=>0,
              ];
              foreach ($adjustmentMovements as $adjustmentMovement){
                if ($adjustmentMovement['StockMovement']['bool_input']){
                  $adjusted['quantity_up']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_up']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
                else {
                  $adjusted['quantity_down']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_down']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
              }
              
              $relevantProducts[]=[
                'product_name'=>$currentProduct['Product']['name'],
                'product_id'=>$currentProduct['Product']['id'],
                'quantity'=>$currentProduct['0']['Remaining'],
                'value'=>$currentProduct['0']['Saldo'],
                'adjusted'=>$adjusted,
              ];
            }
          }
          $adjustedProducts[$adjustmentName]=[
            'name'=>'Botellas Importados',
            'Product'=>$relevantProducts,
          ];
          break;
        case 'CAPS':
          $relevantProducts=[];
          foreach ($productTypeProducts[PRODUCT_TYPE_CAP]['Products'] as $currentProduct){
            //pr($currentProduct);
            $productNatureId=$this->Product->getProductNatureId($currentProduct['Product']['id']);
            if ($currentProduct[0]['Remaining'] >0){
              $adjustmentMovementConditions=$generalAdjustmentMovementConditions;
              $adjustmentMovementConditions['StockMovement.product_id']=$currentProduct['Product']['id'];
              //pr($adjustmentMovementConditions);
              $adjustmentMovements=$this->StockMovement->find('all',[
                'conditions'=>$adjustmentMovementConditions,
                'recursive'=>-1,
              ]);
              //pr($adjustmentMovements);
              $adjusted=[
                'quantity_up'=>0,
                'value_up'=>0,
                'quantity_down'=>0,
                'value_down'=>0,
              ];
              foreach ($adjustmentMovements as $adjustmentMovement){
                if ($adjustmentMovement['StockMovement']['bool_input']){
                  $adjusted['quantity_up']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_up']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
                else {
                  $adjusted['quantity_down']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_down']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
              }
              
              $relevantProducts[]=[
                'product_name'=>$currentProduct['Product']['name'],
                'product_id'=>$currentProduct['Product']['id'],
                'quantity'=>$currentProduct['0']['Remaining'],
                'value'=>$currentProduct['0']['Saldo'],
                'adjusted'=>$adjusted,
              ];
            }
          }
          $adjustedProducts[$adjustmentName]=[
            'name'=>'Tapones',
            'Product'=>$relevantProducts,
          ];
          break;
        case 'CAPS_IMPORT':
          $relevantProducts=[];
          foreach ($productTypeProducts[PRODUCT_TYPE_IMPORT]['Products'] as $currentProduct){
            //pr($currentProduct);
            $productNatureId=$this->Product->getProductNatureId($currentProduct['Product']['id']);
            if ($productNatureId != PRODUCT_NATURE_BOTTLES_BOUGHT && $currentProduct[0]['Remaining'] >0){
              $adjustmentMovementConditions=$generalAdjustmentMovementConditions;
              $adjustmentMovementConditions['StockMovement.product_id']=$currentProduct['Product']['id'];
              //pr($adjustmentMovementConditions);
              $adjustmentMovements=$this->StockMovement->find('all',[
                'conditions'=>$adjustmentMovementConditions,
                'recursive'=>-1,
              ]);
              //pr($adjustmentMovements);
              $adjusted=[
                'quantity_up'=>0,
                'value_up'=>0,
                'quantity_down'=>0,
                'value_down'=>0,
              ];
              foreach ($adjustmentMovements as $adjustmentMovement){
                if ($adjustmentMovement['StockMovement']['bool_input']){
                  $adjusted['quantity_up']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_up']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
                else {
                  $adjusted['quantity_down']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_down']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
              }
              
              $relevantProducts[]=[
                'product_name'=>$currentProduct['Product']['name'],
                'product_id'=>$currentProduct['Product']['id'],
                'quantity'=>$currentProduct['0']['Remaining'],
                'value'=>$currentProduct['0']['Saldo'],
                'adjusted'=>$adjusted,
              ];
            }
          }
          $adjustedProducts[$adjustmentName]=[
            'name'=>'Productos Importados',
            'Product'=>$relevantProducts,
          ];
          break;
        case 'RAWMATERIAL':
          $relevantProducts=[];
          foreach ($productTypeProducts[PRODUCT_TYPE_PREFORMA]['Products'] as $currentProduct){
            //pr($currentProduct);
            $productNatureId=$this->Product->getProductNatureId($currentProduct['Product']['id']);
            if ($currentProduct[0]['Remaining'] >0){
              $adjustmentMovementConditions=$generalAdjustmentMovementConditions;
              $adjustmentMovementConditions['StockMovement.product_id']=$currentProduct['Product']['id'];
              //pr($adjustmentMovementConditions);
              $adjustmentMovements=$this->StockMovement->find('all',[
                'conditions'=>$adjustmentMovementConditions,
                'recursive'=>-1,
              ]);
              //pr($adjustmentMovements);
              $adjusted=[
                'quantity_up'=>0,
                'value_up'=>0,
                'quantity_down'=>0,
                'value_down'=>0,
              ];
              foreach ($adjustmentMovements as $adjustmentMovement){
                if ($adjustmentMovement['StockMovement']['bool_input']){
                  $adjusted['quantity_up']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_up']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
                else {
                  $adjusted['quantity_down']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_down']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
              }
              
              $relevantProducts[]=[
                'product_name'=>$currentProduct['Product']['name'],
                'product_id'=>$currentProduct['Product']['id'],
                'quantity'=>$currentProduct['0']['Remaining'],
                'value'=>$currentProduct['0']['Saldo'],
                'adjusted'=>$adjusted,
              ];
            }
          }
          $adjustedProducts[$adjustmentName]=[
            'name'=>'Materia Prima',
            'Product'=>$relevantProducts,
          ];
          break;
        case 'BAGS':
          $relevantProducts=[];
          foreach ($productTypeProducts[PRODUCT_TYPE_CONSUMIBLES]['Products'] as $currentProduct){
            //pr($currentProduct);
            $productNatureId=$this->Product->getProductNatureId($currentProduct['Product']['id']);
            if ($currentProduct[0]['Remaining'] >0){
              $adjustmentMovementConditions=$generalAdjustmentMovementConditions;
              $adjustmentMovementConditions['StockMovement.product_id']=$currentProduct['Product']['id'];
              //pr($adjustmentMovementConditions);
              $adjustmentMovements=$this->StockMovement->find('all',[
                'conditions'=>$adjustmentMovementConditions,
                'recursive'=>-1,
              ]);
              //pr($adjustmentMovements);
              $adjusted=[
                'quantity_up'=>0,
                'value_up'=>0,
                'quantity_down'=>0,
                'value_down'=>0,
              ];
              foreach ($adjustmentMovements as $adjustmentMovement){
                if ($adjustmentMovement['StockMovement']['bool_input']){
                  $adjusted['quantity_up']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_up']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
                else {
                  $adjusted['quantity_down']+=$adjustmentMovement['StockMovement']['product_quantity'];
                  $adjusted['value_down']+=$adjustmentMovement['StockMovement']['product_total_price'];
                }
              }
              
              $relevantProducts[]=[
                'product_name'=>$currentProduct['Product']['name'],
                'product_id'=>$currentProduct['Product']['id'],
                'quantity'=>$currentProduct['0']['Remaining'],
                'value'=>$currentProduct['0']['Saldo'],
                'adjusted'=>$adjusted,
              ];
            }
          }
          $adjustedProducts[$adjustmentName]=[
            'name'=>'Bolsas',
            'Product'=>$relevantProducts,
          ];
          break;
      }
    }
    //pr($adjustedProducts);
    $this->set(compact('adjustedProducts'));
    /*  
    define('PRODUCT_TYPE_ROLL','12');
    define('PRODUCT_TYPE_CONSUMIBLES','13');
    define('PRODUCT_TYPE_POLINDUSTRIAS','15');
    define('PRODUCT_TYPE_LOCAL','16');
    
    define('PRODUCT_TYPE_INJECTION_GRAIN','17');
    define('PRODUCT_TYPE_INJECTION_OUTPUT','18');
    
    define('PRODUCT_NATURE_PRODUCED','1');
    define('PRODUCT_NATURE_BOTTLES_BOUGHT','2');
    define('PRODUCT_NATURE_ACCESORIES','3');
    define('PRODUCT_NATURE_RAW','4');
    define('PRODUCT_NATURE_BAGS','5');
      
    define('PRODUCTION_TYPE_PET','1');
    define('PRODUCTION_TYPE_INJECTION','2');
    define('PRODUCTION_TYPE_FILLING','3');
	  */
		$warehouses=$this->Warehouse->find('list',['order'=>'Warehouse.name',]);
		$this->set(compact('warehouses'));
    
    $productTypes=$this->ProductType->find('list',[
      'fields'=>['ProductType.id','ProductType.name'],
			'conditions'=>['ProductType.id !='=>PRODUCT_TYPE_SERVICE],
      'order'=>'ProductType.name',
		]);
    $this->set(compact('productTypes'));
	}
  
  public function guardarComprobanteAjustes($inventoryDate=null) {
		$this->set(compact('inventoryDate'));
    $exportData=$_SESSION['comprobanteAjustes'];
		$this->set(compact('exportData'));
	}	
  
  public function desactivarLotes(){
    $firstDayOfMonth=date('Y-m-01');
    
    $limitCreationDate=$firstDayOfMonthThreeMonthsAgo=date("Y-m-d",strtotime($firstDayOfMonth."-3 months"));
    $limitDepletionDate=$firstDayOfMonthTwoMonthsAgo=date("Y-m-d",strtotime($firstDayOfMonth."-2 months"));
    
    $stockItemLogQuantity=1;
    //pr($limitCreationDate);
    
    if ($this->request->is('post')){
      if (!empty($this->request->data['setConditions'])) {    
      
        $limitCreationDateArray=$this->request->data['Report']['limit_creation_date'];
        $limitCreationDateString=$limitCreationDateArray['year'].'-'.$limitCreationDateArray['month'].'-'.$limitCreationDateArray['day'];
        $limitCreationDate=date( "Y-m-d", strtotime($limitCreationDateString));
        
        if ($limitCreationDate>$firstDayOfMonthThreeMonthsAgo){
          $limitCreationDate=$firstDayOfMonthThreeMonthsAgo;
        }
        
        $limitDepletionDateArray=$this->request->data['Report']['limit_depletion_date'];
        $limitDepletionDateString=$limitDepletionDateArray['year'].'-'.$limitDepletionDateArray['month'].'-'.$limitDepletionDateArray['day'];
        $limitDepletionDate=date( "Y-m-d", strtotime($limitDepletionDateString));
      
        if ($limitDepletionDate > $firstDayOfMonthTwoMonthsAgo){
          $limitDepletionDate=$firstDayOfMonthTwoMonthsAgo;
        }
        
        $stockItemLogQuantity=$this->request->data['Report']['stock_item_log_quantity'];
        if ($stockItemLogQuantity < 1){
          $stockItemLogQuantity = 1;  
        }  
      }
      elseif (!empty($this->request->data['deactivateStockItems'])) {    
        //pr($this->request->data);
      
        $stockItemIds='';
      
        $datasource=$this->StockItem->getDataSource();
				$datasource->begin();
      
				try {
          foreach ($this->request->data['StockItem'] as $stockItemId=>$stockItemData){
            if ($stockItemData['selector']){
              //pr($stockItemData);
              $stockItemArray=[];
              $stockItemArray=[
                'StockItem'=>[
                  'id'=>$stockItemId,
                  'bool_active'=>false,
                  'stockitem_depletion_date'=>$stockItemData['stockitem_depletion_date']
                ],
              ];
              $this->StockItem->id=$stockItemId;
              //pr($stockItemArray);
              if (!$this->StockItem->save($stockItemArray)) {
                echo "Problema guardando el lote ".$stockItemId;
                pr($this->validateErrors($this->StockItem));
                throw new Exception();
              }
              $stockItemIds.=$stockItemId." ";
            }
          }
          //pr($stockItemIds);
          
          $this->recordUserAction($this->StockItem->id,'desactivarLotes','StockItems');
          $datasource->commit();
					
					// SAVE THE USERLOG FOR THE CHEQUE
					$this->recordUserActivity($this->Session->read('User.username'),"_Se desactivaron los lotes con id ".$stockItemIds);
					$this->Session->setFlash(('Se desactivaron los lotes con id '.$stockItemIds),'default',['class' => 'success']);
					//return $this->redirect(['action' => 'resumenDepositos');
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar el depósito.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
				}  
      }
    }
    $this->set(compact('limitCreationDate','limitDepletionDate','stockItemLogQuantity'));
    
    $stockItemConditions=[
      
      'OR'=>[
        [
          'StockItem.stockitem_creation_date <'=>$limitCreationDate,
          'StockItem.remaining_quantity <='=>0,
          'StockItem.bool_active'=>true,
        ],
        [
          'StockItem.stockitem_creation_date <'=>$limitCreationDate,
          'StockItem.remaining_quantity <='=>0,
          'StockItem.stockitem_depletion_date >'=>date('Y-m-d H:i:s'),
        ],
      ],
    ];
    //pr($stockItemConditions);
    $contain=[
      'Product',
      'ProductionResultCode',
      'RawMaterial',
      'StockItemLog'=>[
        'conditions'=>[
          'StockItemLog.stockitem_date <'=>$limitDepletionDate,
        ],
        'StockMovement'=>[
          'Order',
        ],
        'ProductionMovement'=>[
          'ProductionRun',
        ],
        'limit'=>$stockItemLogQuantity,
        'order'=>'StockItemLog.id DESC'
      ],
      'Warehouse',
    ];
    
    $activeStockItems=$this->StockItem->find('all',[
      'conditions'=>$stockItemConditions,
      'contain'=>$contain,
      'limit'=>150,
      'order'=>'StockItem.stockitem_creation_date ASC',
    ]);
    //pr($activeStockItems);
    //echo "total  count is ".count($activeStockItems)."<br/>";
    $this->set(compact('activeStockItems'));    
  }
  
  public function detalleCostoProducto() {
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductionResultCode');
    
		$inventoryDate = null;
    $finishedProductId=0;
    $rawMaterialId=0;
		if ($this->request->is('post')) {
			$inventoryDateArray=$this->request->data['Report']['inventorydate'];
			$inventoryDateString=$inventoryDateArray['year'].'-'.$inventoryDateArray['month'].'-'.$inventoryDateArray['day'];
			$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
			$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDateString."+1 days"));
      
      $finishedProductId=$this->request->data['Report']['finished_product_id'];
      $rawMaterialId=$this->request->data['Report']['raw_material_id'];
		}
		if (!isset($inventoryDate)){
			$inventoryDate = date("Y-m-d",strtotime(date("Y-m-d")));
			$inventoryDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['inventoryDate']=$inventoryDate;
		$this->set(compact('inventoryDate'));
    $this->set(compact('finishedProductId'));
    $this->set(compact('rawMaterialId'));
		
		$this->StockItem->recursive=-1;
		
		if (!empty($finishedProductId) && !empty($rawMaterialId)){
      $stockItems=$this->StockItem->getInventoryFinishedProduct($finishedProductId,$rawMaterialId,$inventoryDate);
    }
    $this->set(compact('stockItems'));
    
    $finishedProductProductTypeList=$this->ProductType->find('list',array(
      'fields'=>array('ProductType.id'),
      'conditions'=>array(
        'ProductType.product_category_id'=>CATEGORY_PRODUCED,
      ),
    ));
    $rawMaterialProductTypeList=$this->ProductType->find('list',array(
      'fields'=>array('ProductType.id'),
      'conditions'=>array(
        'ProductType.product_category_id'=>CATEGORY_RAW,
      ),
    ));
    
    $finishedProducts=$this->Product->find('list',array(
      'conditions'=>array(
        'Product.product_type_id'=>$finishedProductProductTypeList,
      ),
      'order'=>'Product.name',
    ));
		$this->set(compact('finishedProducts'));
		$rawMaterials=$this->Product->find('list',array(
      'conditions'=>array(
        'Product.product_type_id'=>$rawMaterialProductTypeList,
      ),
      'order'=>'Product.name',
    ));
		$this->set(compact('rawMaterials'));
    
    $productionResultCodes=$this->ProductionResultCode->find('list',array(
      'order'=>'ProductionResultCode.code',
    ));
		$this->set(compact('productionResultCodes'));
    //pr($productionResultCodes);
	}

	public function guardarDetalleCostoProducto() {
		$exportData=$_SESSION['inventoryReport'];
		$this->set(compact('exportData'));
	}	
   
	public function verPdfHojaInventario($inventoryDate=null,$warehouseId=0) {
    $this->loadModel('ProductCategory');
		$this->loadModel('ProductType');
		$this->loadModel('StockItemLog');
		
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    $this->loadModel('PlantProductType');

    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		if ($inventoryDate==null){
			$startDateString=$_SESSION['inventoryDate'];
		}
		else {
			$inventoryDateString=$inventoryDate;
		}
		$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDateString."+1 days"));
		$this->set(compact('inventoryDate','inventoryDatePlusOne'));
		
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    $plantId=0;
    if ($warehouseId > 0){
      $plantId=$this->Warehouse->getPlantId($warehouseId);
    }
    $this->set(compact('plantId'));
    
    $productTypesForPlant=$this->PlantProductType->getProductTypesForPlant($plantId);
    
		$productCategories=$this->ProductCategory->find('all',[
			'contain'=>[
				'ProductType'=>[
          'conditions'=>[
            'ProductType.id'=>array_keys($productTypesForPlant),
          ],
        ],
			],
		]);

		$otherProducts=[];
    for ($pc=0;$pc<count($productCategories);$pc++){
      $categoryOtherTypesArray=[];
      $categoryOtherTypesArray['ProductCategory']=$productCategories[$pc]['ProductCategory'];
			for ($pt=0;$pt<count($productCategories[$pc]['ProductType']);$pt++){
				//pr($productCategories[$pc]['ProductType'][$pt]);
				switch ($productCategories[$pc]['ProductType'][$pt]['id']){
					case PRODUCT_TYPE_PREFORMA:
						$preformas=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
						$this->set(compact('preformas'));
						break;
					case PRODUCT_TYPE_BOTTLE:
						$productos=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
						$this->set(compact('productos'));
						break;
					case PRODUCT_TYPE_CAP:
						$tapones=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
						$this->set(compact('tapones'));
						break;
          default:
            if (!in_array($productCategories[$pc]['ProductType'][$pt]['id'],[PRODUCT_TYPE_SERVICE,PRODUCT_TYPE_ROLL])){
              $productTypeArray=[];
              $productTypeArray['ProductType']=$productCategories[$pc]['ProductType'][$pt];
              $productTypeArray['Products']=$this->StockItem->getInventoryItems($productCategories[$pc]['ProductType'][$pt]['id'],$inventoryDate,$warehouseId,false);
              $categoryOtherTypesArray['ProductLines'][]=$productTypeArray;
            }
				}
			}
      $otherProducts[]=$categoryOtherTypesArray;
		}
		$this->set(compact('otherProducts'));
		
		$filename="Hoja_Inventario_".date('d_m_Y');
		$this->set(compact('filename'));
	}

	
/******************** REPORTES PRODUCTOS Y PRODUCTO *******************/	
	
	public function verReporteProductos($startDate = null,$endDate=null) {
    $model=$this;
    $this->loadModel('Product');
		$this->loadModel('ProductionMovement');
		
    $model->loadModel('Product');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    $this->loadModel('WarehouseProduct');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
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
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		//echo $endDatePlusOne;
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		//echo "session startDate is ".$_SESSION['startDate']."<br/>";
		//echo "session endDate is ".$_SESSION['endDate']."<br/>";
		
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
    
    $productIdsForWarehouse=$this->WarehouseProduct->getProductIdsForWarehouse($warehouseId);
		$allRawMaterials = Cache::remember('stockitem_reporteproductos_productcategory_'.CATEGORY_RAW.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne,$productIdsForWarehouse){
      return $model->Product->find('all',[
    
        'fields'=>'Product.id,Product.name',
        'conditions' => [
          'ProductType.product_category_id'=> CATEGORY_RAW,
          'Product.id'=> $productIdsForWarehouse,
        ],
        'contain'=>[
          'StockMovement'=>[
            // CONDITIONS ADDED 20160202
            'conditions'=>[
              'StockMovement.movement_date >=' => $startDate,
              'StockMovement.movement_date <'=> $endDatePlusOne,
            ],
          ],
          'ProductionRunInput'=>[
          // CONDITIONS ADDED 20160202
            'conditions'=>[
              'ProductionRunInput.production_run_date >='=> $startDate,
              'ProductionRunInput.production_run_date <' => $endDatePlusOne,
            ],
          ],
          'ProductType',
        ],
        'order'=>'Product.name',
      ]);
    }, 'long');  
    /*
		$allRawMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'ProductType.product_category_id ='=> CATEGORY_RAW,
			),
			'contain'=>array(
				'StockMovement'=>array(
					// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'StockMovement.movement_date >=' => $startDate,
						'StockMovement.movement_date <'=> $endDatePlusOne,
					),
				),
				'ProductionRunInput'=>array(
				// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'ProductionRunInput.production_run_date >='=> $startDate,
						'ProductionRunInput.production_run_date <' => $endDatePlusOne,
					),
				),
				'ProductType',
			),
			'order'=>'Product.name',
		));
    */
		$i=0;
		foreach ($allRawMaterials as $rawMaterial){
			$productId=$rawMaterial['Product']['id'];
			$productName=$rawMaterial['Product']['name'];
			
			$productUnitPrice=0;
			$productInitialStock=0;
			$productInitialValue=0;
			$productPurchasedQuantity=0;
			$productPurchasedValue=0;
			$productReclassifiedQuantity=0;
			$productReclassifiedValue=0;
			$productConsumedQuantity=0;
			$productConsumedValue=0;
			$productFinalStock=0;
			$productFinalValue=0;
			foreach ($rawMaterial['StockMovement'] as $stockMovement){			
				if ($stockMovement['bool_input']){
					//echo "recognized as input";
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] < $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							//echo "this is a relevant one";
							$productPurchasedQuantity+=$stockMovement['product_quantity'];
							//echo "summed to purchasedquantity quantity".$productPurchasedQuantity;
							$productPurchasedValue+=$stockMovement['product_total_price'];
						}
						else {
							$productReclassifiedQuantity+=$stockMovement['product_quantity'];
							//echo "summed to purchasedquantity quantity".$productPurchasedQuantity;
							$productReclassifiedValue+=$stockMovement['product_total_price'];
						}
					//}
				} 
			}
			
			foreach ($rawMaterial['ProductionRunInput'] as $productionRun){
				// 20160202 CONDITIONS REMOVED
				//if ($productionRun['production_run_date'] >= $startDate && $productionRun['production_run_date'] <= $endDatePlusOne){
					$productionRunId=$productionRun['id'];
					// RECURSIVE ADDED 20151201
					$model->ProductionMovement->recursive=-1;
          $allProductionMovementsForProductionRun = Cache::remember('stockitem_reporteproductos_productionmovements_input_'.$productionRunId, function() use ($model,$productionRunId){
            return $model->ProductionMovement->find('all',[
              'fields'=>[
                  'ProductionMovement.product_quantity','ProductionMovement.product_unit_price',
                ],
                'conditions' => [
                  'ProductionMovement.production_run_id'=> $productionRunId,
                  'ProductionMovement.bool_input'=> true,
                ]
            ]);
           }, 'long'); 
          /*
					$allProductionMovementsForProductionRun = $this->ProductionMovement->find('all', array(
						//FIELDS ADDED 20151201
						'fields'=>array(
							'ProductionMovement.product_quantity','ProductionMovement.product_unit_price',
						),
						'conditions' => array(
							'ProductionMovement.production_run_id'=> $productionRunId,
							'ProductionMovement.bool_input'=> true,
						)
					));
					*/
					foreach ($allProductionMovementsForProductionRun as $productionMovement){
						//pr($productionMovement);					
						$productConsumedQuantity+=$productionMovement['ProductionMovement']['product_quantity'];
						$productConsumedValue+=$productionMovement['ProductionMovement']['product_unit_price']*$productionMovement['ProductionMovement']['product_quantity'];
					}
				//} 
			}
			$this->StockItem->recursive=-1;
      $allStockItemsForProduct = Cache::remember('stockitem_reporteproductos_stockitems_'.$productId.'_'.$startDate, function() use ($model,$productId,$startDate){
        return $model->StockItem->find('all',[
          'fields'=>'StockItem.id',
          'conditions' => [
            'StockItem.product_id ='=> $productId,
            // CONDITIONS ADDED 20180314
            'StockItem.stockitem_creation_date <'=> $startDate,        
            'StockItem.stockitem_depletion_date >='=> $startDate,
          ],
        ]);
       }, 'long'); 
      // RECURSIVE ADDED 20151201
      /*
			$allStockItemsForProduct = $this->StockItem->find('all', array(
				// FIELDS ADDED 20151201
				'fields'=>'StockItem.id',
				'conditions' => [
          'StockItem.product_id'=> $productId,
          // CONDITIONS ADDED 20180314
          'StockItem.stockitem_creation_date <'=> $startDate,        
          'StockItem.stockitem_depletion_date >='=> $startDate,
				],
			));
			*/
			foreach ($allStockItemsForProduct as $stockItemForProduct){
				//pr($stockItemForProduct);
				$stockitemId=$stockItemForProduct['StockItem']['id'];
				
				//get the last stockitem log before the startdate to determine the initial stock
        $model->StockItem->StockItemLog->recursive=-1;
        $initialStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_initial_'.$stockitemId.'_'.$startDate, function() use ($model,$stockitemId,$startDate){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
            'conditions' => [
              'StockItemLog.stockitem_id ='=> $stockitemId,
              'StockItemLog.stockitem_date <'=>$startDate
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				$this->StockItem->StockItemLog->recursive=-1;
				$initialStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockitemId,
						'StockItemLog.stockitem_date <'=>$startDate
					),
					'order'=>'StockItemLog.id DESC'
				));
        */
				if (!empty($initialStockItemLogForStockItem)){
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productInitialValue+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity']*$initialStockItemLogForStockItem['StockItemLog']['product_unit_price'];
				}
				
				//get the last stockitem log before the startdate to determine the initial stock
				
        $finalStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_final_'.$stockitemId.'_'.$endDatePlusOne, function() use ($model,$stockitemId,$endDatePlusOne){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
            'conditions' => [
              'StockItemLog.stockitem_id ='=> $stockitemId,
              'StockItemLog.stockitem_date <'=>$endDatePlusOne
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*$finalStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockitemId,
						'StockItemLog.stockitem_date <'=>$endDatePlusOne
					),
					'order'=>'StockItemLog.id DESC'
				));
        */
				if (!empty($finalStockItemLogForStockItem)){
					$productFinalStock+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productFinalValue+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity']*$finalStockItemLogForStockItem['StockItemLog']['product_unit_price'];
				}
				
				
				if ($productInitialValue != 0){
					$productUnitPrice=$productInitialStock/$productInitialValue;
				}
			}
			$rawMaterials[$i]['id']=$productId;
			$rawMaterials[$i]['name']=$productName;
			$rawMaterials[$i]['unit_price']=$productUnitPrice;
			$rawMaterials[$i]['initial_quantity']=$productInitialStock;
			$rawMaterials[$i]['initial_value']=$productInitialValue;
			$rawMaterials[$i]['purchased_quantity']=$productPurchasedQuantity;
			$rawMaterials[$i]['purchased_value']=$productPurchasedValue;
			$rawMaterials[$i]['reclassified_quantity']=$productReclassifiedQuantity;
			$rawMaterials[$i]['reclassified_value']=$productReclassifiedValue;
			$rawMaterials[$i]['used_quantity']=$productConsumedQuantity;
			$rawMaterials[$i]['used_value']=$productConsumedValue;
			$rawMaterials[$i]['final_quantity']=$productFinalStock;
			$rawMaterials[$i]['final_value']=$productFinalValue;
			$i++;
		}
		
		/*********************************************************
		PRODUCED MATERIALS
		*********************************************************/
		$allProducedMaterials = Cache::remember('stockitem_reporteproductos_productcategory_'.CATEGORY_PRODUCED.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne,$productIdsForWarehouse){
      return $model->Product->find('all',[
        'fields'=>'Product.id,Product.name',
        'conditions' => [
          'ProductType.product_category_id'=> CATEGORY_PRODUCED,
          'Product.id'=> $productIdsForWarehouse,
        ],
        'contain'=>[
          'ProductType',
          'StockMovement'=>[
            // CONDITIONS ADDED 20160202
            'conditions'=>[
              'StockMovement.movement_date >=' => $startDate,
              'StockMovement.movement_date <'=> $endDatePlusOne,
            ],
          ],
          'ProductionRunOutput'=>[
            // CONDITIONS ADDED 20160202
            'conditions'=>[
              'ProductionRunOutput.production_run_date >='=> $startDate,
              'ProductionRunOutput.production_run_date <' => $endDatePlusOne,
            ],
          ],
        ],
        'order'=>'Product.name',
      ]);
    }, 'long');
    /*
		$allProducedMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'ProductType.product_category_id ='=> CATEGORY_PRODUCED,
			),
			'contain'=>array(
				'ProductType',
				'StockMovement'=>array(
					// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'StockMovement.movement_date >=' => $startDate,
						'StockMovement.movement_date <'=> $endDatePlusOne,
					),
				),
				'ProductionRunOutput'=>array(
					// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'ProductionRunOutput.production_run_date >='=> $startDate,
						'ProductionRunOutput.production_run_date <' => $endDatePlusOne,
					),
				),
			),
			'order'=>'Product.name',
		));
    */
		$i=0;
		foreach ($allProducedMaterials as $producedMaterial){
			//pr($producedMaterial);
			$productId=$producedMaterial['Product']['id'];
			$productName=$producedMaterial['Product']['name'];
			
			$productUnitPrice=0;
			$productInitialStock=0;
			$productInitialValue=0;
			$productProducedQuantity=0;
			$productProducedValue=0;
			$productReclassifiedQuantity=0;
			$productReclassifiedValue=0;
			$productSoldQuantity=0;
			$productSoldValue=0;
			$productReclassifiedQuantity=0;
			$productReclassifiedValue=0;
			$productFinalStock=0;
			$productFinalValue=0;
			
			foreach ($producedMaterial['StockMovement'] as $stockMovement){
				//pr($stockMovement);
				if (!$stockMovement['bool_input']){
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							$productSoldQuantity+=$stockMovement['product_quantity'];
							$linkedStockItem=$this->StockItem->find('first',[
                'fields'=>'StockItem.product_unit_price',
                'conditions'=>[
                  'StockItem.id'=>$stockMovement['stockitem_id']
                ]
              ]);
							$productSoldValue+=$stockMovement['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
						}
						else {
							$productReclassifiedQuantity+=$stockMovement['product_quantity'];
							//if ($productId==13){
							//	echo "summed to product reclassified quantity".$stockMovement['product_quantity'];
							//	echo "resulting reclassified quantity is ".$productReclassifiedQuantity."<br/>";
							//}
							$productReclassifiedValue+=$stockMovement['product_total_price'];
						}
					//}
				} 
				else {
					if ($stockMovement['bool_reclassification']){
						$productReclassifiedQuantity-=$stockMovement['product_quantity'];
						//if ($productId==13){
						//	echo "rested from product reclassified quantity".$stockMovement['product_quantity'];
						//	echo "resulting reclassified quantity is ".$productReclassifiedQuantity."<br/>";
						//}
						$productReclassifiedValue-=$stockMovement['product_total_price'];
					}
				}
			}
			
			
			foreach ($producedMaterial['ProductionRunOutput'] as $productionRun){
				// 20160202 CONDITIONS REMOVED
				//if ($productionRun['production_run_date'] >= $startDate && $productionRun['production_run_date'] <= $endDatePlusOne){
					$productionRunId=$productionRun['id'];
					// RECURSIVE ADDED 20151201
					$model->ProductionMovement->recursive=-1;
          $allProductionMovementsForProductionRun = Cache::remember('stockitem_reporteproductos_productionmovements_'.$productionRunId, function() use ($model,$productionRunId){
            return $model->ProductionMovement->find('all',[
              'fields'=>[
                  'ProductionMovement.product_quantity','ProductionMovement.product_unit_price',
                ],
                'conditions' => [
                  'ProductionMovement.production_run_id ='=> $productionRunId,
                  'ProductionMovement.bool_input ='=> false,
                ]
            ]);
           }, 'long'); 
          /*
					$allProductionMovementsForProductionRun = $this->ProductionMovement->find('all', array(
						//FIELDS ADDED 20151201
						'fields'=>array(
							'ProductionMovement.product_quantity','ProductionMovement.product_unit_price',
						),
						'conditions' => array(
							'ProductionMovement.production_run_id ='=> $productionRunId,
							'ProductionMovement.bool_input ='=> false,
						)
					));
					*/
					foreach ($allProductionMovementsForProductionRun as $productionMovement){					
						$productProducedQuantity+=$productionMovement['ProductionMovement']['product_quantity'];
						$productProducedValue+=$productionMovement['ProductionMovement']['product_unit_price']*$productionMovement['ProductionMovement']['product_quantity'];
					}
				//} 
			}
			
			// RECURSIVE ADDED 20151201
			$model->StockItem->recursive=-1;
      $allStockItemsForProduct = Cache::remember('stockitem_reporteproductos_stockitems_'.$productId.'_'.$startDate, function() use ($model,$productId,$startDate){
        return $model->StockItem->find('all',[
          'fields'=>'StockItem.id',
          'conditions' => [
            'StockItem.product_id ='=> $productId,
            // CONDITIONS ADDED 20180314
            'StockItem.stockitem_creation_date <'=> $startDate,        
            'StockItem.stockitem_depletion_date >='=> $startDate,
          ],
        ]);
       }, 'long'); 
       /*
			$allStockItemsForProduct = $this->StockItem->find('all', array(
				// FIELDS ADDED 20151201
				'fields'=>'StockItem.id',
				'conditions' => [
					'StockItem.product_id ='=> $productId,
          // CONDITIONS ADDED 20180314
          'StockItem.stockitem_creation_date <'=> $startDate,        
          'StockItem.stockitem_depletion_date >='=> $startDate,
				],
			));
			*/
			foreach ($allStockItemsForProduct as $stockItemForProduct){
				$stockitemId=$stockItemForProduct['StockItem']['id'];
				$model->StockItem->StockItemLog->recursive=-1;
        $initialStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_initial_'.$stockitemId.'_'.$startDate, function() use ($model,$stockitemId,$startDate){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
            'conditions' => [
              'StockItemLog.stockitem_id ='=> $stockitemId,
              'StockItemLog.stockitem_date <'=>$startDate
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				$initialStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockitemId,
						'StockItemLog.stockitem_date <'=>$startDate
					),
					'order'=>'StockItemLog.id DESC'
				));
        */
				if (!empty($initialStockItemLogForStockItem)){
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productInitialValue+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity']*$initialStockItemLogForStockItem['StockItemLog']['product_unit_price'];
				}
				
				//get the last stockitem log before the startdate to determine the initial stock
        $finalStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_final_'.$stockitemId.'_'.$endDatePlusOne, function() use ($model,$stockitemId,$endDatePlusOne){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
            'conditions' => [
              'StockItemLog.stockitem_id ='=> $stockitemId,
              'StockItemLog.stockitem_date <'=>$endDatePlusOne
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				$finalStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockitemId,
						'StockItemLog.stockitem_date <'=>$endDatePlusOne
					),
					'order'=>'StockItemLog.id DESC'
				));
        */
				if (!empty($finalStockItemLogForStockItem)){
					$productFinalStock+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productFinalValue+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity']*$finalStockItemLogForStockItem['StockItemLog']['product_unit_price'];
				}
				//pr($stockItemForProduct);
				
				
				if ($productInitialValue != 0){
					$productUnitPrice=$productInitialStock/$productInitialValue;
				}
			}
			$producedMaterials[$i]['id']=$productId;
			$producedMaterials[$i]['name']=$productName;
			$producedMaterials[$i]['unit_price']=$productUnitPrice;
			$producedMaterials[$i]['initial_quantity']=$productInitialStock;
			$producedMaterials[$i]['initial_value']=$productInitialValue;
			$producedMaterials[$i]['produced_quantity']=$productProducedQuantity;
			$producedMaterials[$i]['produced_value']=$productProducedValue;
			$producedMaterials[$i]['reclassified_quantity']=$productReclassifiedQuantity;
			$producedMaterials[$i]['reclassified_value']=$productReclassifiedValue;
			$producedMaterials[$i]['sold_quantity']=$productSoldQuantity;
			$producedMaterials[$i]['sold_value']=$productSoldValue;
			$producedMaterials[$i]['final_quantity']=$productFinalStock;
			$producedMaterials[$i]['final_value']=$productFinalValue;
			$i++;
		}
		
		/*********************************************************
		TAPONES
		*********************************************************/
		$allOtherMaterials = Cache::remember('stockitem_reporteproductos_productcategory_'.CATEGORY_OTHER.'_'.$startDate.'_'.$endDatePlusOne, function() use ($model,$startDate,$endDatePlusOne,$productIdsForWarehouse){
      return $model->Product->find('all',[
        'fields'=>'Product.id,Product.name',
        'conditions' => [
          'ProductType.product_category_id'=> CATEGORY_OTHER,
          'Product.id'=> $productIdsForWarehouse,
        ],
        'contain'=>[
          'ProductType',
          'StockMovement'=>[
            // CONDITIONS ADDED 20160202
            'conditions'=>[
              'StockMovement.movement_date >=' => $startDate,
              'StockMovement.movement_date <'=> $endDatePlusOne,
            ],
          ],
        ],
        'order'=>'Product.name',
      ]);
    }, 'long');
    /*
		$allOtherMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'ProductType.product_category_id ='=> CATEGORY_OTHER,
			),
			'contain'=>array(
				'ProductType',
				'StockMovement'=>array(
					// CONDITIONS ADDED 20160202
					'conditions'=>array(
						'StockMovement.movement_date >=' => $startDate,
						'StockMovement.movement_date <'=> $endDatePlusOne,
					),
				),
			),
			'order'=>'Product.name',
		));
    */
		$i=0;
		foreach ($allOtherMaterials as $otherMaterial){
			$productId=$otherMaterial['Product']['id'];
			$productName=$otherMaterial['Product']['name'];
			
			$productUnitPrice=0;
			$productInitialStock=0;
			$productInitialValue=0;
			$productPurchasedQuantity=0;
			$productPurchasedValue=0;
			$productSoldQuantity=0;
			$productSoldValue=0;
			$productReclassifiedQuantity=0;
			$productReclassifiedValue=0;
			$productFinalStock=0;
			$productFinalValue=0;
			
			foreach ($otherMaterial['StockMovement'] as $stockMovement){
				if ($stockMovement['bool_input']){
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							$productPurchasedQuantity+=$stockMovement['product_quantity'];
							$productPurchasedValue+=$stockMovement['product_total_price'];
						}
					//}
				} 
			}
			
			foreach ($otherMaterial['StockMovement'] as $stockMovement){
				if (!$stockMovement['bool_input']){
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						$productSoldQuantity+=$stockMovement['product_quantity'];
						$productSoldValue+=$stockMovement['product_total_price'];
					//}
				} 
				
				if ($stockMovement['bool_reclassification']){
					// 20160202 CONDITIONS REMOVED
					//if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if ($stockMovement['bool_input']){
							$productReclassifiedQuantity+=$stockMovement['product_quantity'];
							$productReclassifiedValue+=$stockMovement['product_total_price'];
						}
						else {
							$productReclassifiedQuantity-=$stockMovement['product_quantity'];
							$productReclassifiedValue-=$stockMovement['product_total_price'];
						}
					//}
				} 
			}
			
			// RECURSIVE ADDED 20151201
			$model->StockItem->recursive=-1;
      $allStockItemsForProduct = Cache::remember('stockitem_reporteproductos_stockitems_'.$productId.'_'.$startDate, function() use ($model,$productId,$startDate){
        return $model->StockItem->find('all',[
          'fields'=>'StockItem.id',
          'conditions' => [
            'StockItem.product_id ='=> $productId,
            // CONDITIONS ADDED 20180314
            'StockItem.stockitem_creation_date <'=> $startDate,        
            'StockItem.stockitem_depletion_date >='=> $startDate,
          ],
        ]);
       }, 'long'); 
      /*    
			$allStockItemsForProduct = $this->StockItem->find('all', array(
				// FIELDS ADDED 20151201
				'fields'=>'StockItem.id',
				'conditions' => [
					'StockItem.product_id ='=> $productId,
          // CONDITIONS ADDED 20180314
          'StockItem.stockitem_creation_date <'=> $startDate,        
          'StockItem.stockitem_depletion_date >='=> $startDate,
				],
			));
			*/
			$this->loadModel('StockItemLog');
			foreach ($allStockItemsForProduct as $stockItem){
        $stockitemId=$stockItem['StockItem']['id'];
        
				$model->StockItemLog->recursive=-1;
        $initialStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_initial_'.$stockitemId.'_'.$startDate, function() use ($model,$stockitemId,$startDate){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
            'conditions' => [
              'StockItemLog.stockitem_id'=> $stockitemId,
              'StockItemLog.stockitem_date <'=>$startDate
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				$initialStockItemLogs=$this->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions'=>array(
						'StockItemLog.stockitem_id'=>$stockItem['StockItem']['id'],
						'StockItemLog.stockitem_date <'=>$startDate,
					),
					'order'=>'StockItemLog.id DESC'
				));
				*/
				if (!empty($initialStockItemLogForStockItem)){
					//pr ($initialStockItemLogForStockItem);
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productInitialValue+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity']*$initialStockItemLogForStockItem['StockItemLog']['product_unit_price'];
				}
				$this->StockItemLog->recursive=-1;
        
        $finalStockItemLogForStockItem = Cache::remember('stockitem_reporteproductos_stockitemlog_final_'.$stockitemId.'_'.$endDatePlusOne, function() use ($model,$stockitemId,$endDatePlusOne){
          return $model->StockItem->StockItemLog->find('first',[
            'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
            'conditions' => [
              'StockItemLog.stockitem_id ='=> $stockitemId,
              'StockItemLog.stockitem_date <'=>$endDatePlusOne
            ],
            'order'=>'StockItemLog.id DESC'
          ]);
        }, 'long');  
        /*
				
				$finalStockItemLogs=$this->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions'=>array(
						'StockItemLog.stockitem_id'=>$stockItem['StockItem']['id'],
						'StockItemLog.stockitem_date <'=>$endDatePlusOne,
					),
					'order'=>'StockItemLog.id DESC'
				));
				*/

				if (!empty($finalStockItemLogForStockItem)){
					//pr ($finalStockItemLogForStockItem);
					$productFinalStock+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productFinalValue+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity']*$finalStockItemLogForStockItem['StockItemLog']['product_unit_price'];
				}

				if ($productInitialValue != 0){
					$productUnitPrice=$productInitialStock/$productInitialValue;
				}
			}
			$otherMaterials[$i]['id']=$productId;
			$otherMaterials[$i]['name']=$productName;
			$otherMaterials[$i]['unit_price']=$productUnitPrice;
			$otherMaterials[$i]['initial_quantity']=$productInitialStock;
			$otherMaterials[$i]['initial_value']=$productInitialValue;
			$otherMaterials[$i]['purchased_quantity']=$productPurchasedQuantity;
			$otherMaterials[$i]['purchased_value']=$productPurchasedValue;
			$otherMaterials[$i]['sold_quantity']=$productSoldQuantity;
			$otherMaterials[$i]['sold_value']=$productSoldValue;
			$otherMaterials[$i]['reclassified_quantity']=$productReclassifiedQuantity;
			$otherMaterials[$i]['reclassified_value']=$productReclassifiedValue;
			$otherMaterials[$i]['final_quantity']=$productFinalStock;
			$otherMaterials[$i]['final_value']=$productFinalValue;
			$i++;
		}		
		$this->set(compact('rawMaterials','producedMaterials','otherMaterials','startDate','endDate'));
	}
	
	public function verReporteProduccionDetalle() {
		$this->loadModel('ProductType');
		$this->loadModel('Product');
		$this->loadModel('ProductionRun');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		$this->Product->recursive=-1;
		$this->ProductionMovement->recursive=-1;
		$this->StockItemLog->recursive=-1;
		$this->StockMovement->recursive=-1;
		
		$displayOptions=array();
		$displayOptions[0]="Mostrar solo productos con producción para período";
		$displayOptions[1]="Mostrar todos productos con inventario para período";
		
		define('DISPLAY_ONLY_PRODUCED','0');
		define('DISPLAY_ALL_WITH_INVENTORY','1');
		
		$displayOptionId=DISPLAY_ONLY_PRODUCED;
		
		$reportFormats=array();
		$reportFormats[0]="Formato Consolidado";
		$reportFormats[1]="Formato Detallado";
		
		define('FORMAT_CONSOLIDATED','0');
		define('FORMAT_DETAILED','1');
		
		$reportFormatId=FORMAT_CONSOLIDATED;
		
		$sortOptions=array();
		$sortOptions[0]="Ordenar por producto fabricado (botella), luego por preforma";
		$sortOptions[1]="Ordenar por materia prima (preforma), luego por botella";
		
		define('SORT_BY_FINISHED_PRODUCT','0');
		define('SORT_BY_RAW_MATERIAL','1');
		
		$sortOptionId=SORT_BY_RAW_MATERIAL;
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$displayOptionId=$this->request->data['Report']['display_option_id'];
			$reportFormatId=$this->request->data['Report']['report_format_id'];
			//$sortOptionId=$this->request->data['Report']['sort_option_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('displayOptions','displayOptionId'));
		$this->set(compact('reportFormats','reportFormatId'));
		//$this->set(compact('sortOptions','sortOptionId'));

		/*********************************************************
		PRODUCED MATERIALS
		*********************************************************/
		
		$this->ProductionResultCode->recursive=-1;
		$allProductionResultCodes=$this->ProductionResultCode->find('list',array(
			'order'=>'ProductionResultCode.code', 
		));
		$this->set(compact('allProductionResultCodes'));
		
		$producedProductTypes=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array(
				'ProductType.product_category_id'=> CATEGORY_PRODUCED,
			),
		));
		
		$allProducedMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=> $producedProductTypes,
			),
			'order'=>'Product.name',
		));
		
		$relevantProducts=array();
		
		foreach ($allProducedMaterials as $producedMaterial){
			//pr($producedMaterial);
			$productId=$producedMaterial['Product']['id'];
			$productName=$producedMaterial['Product']['name'];
			
			$rawMaterialsForProduct=$this->Product->getAllRawMaterialsUsedEver($productId);
			//pr($rawMaterialsForProduct);
			
			foreach($rawMaterialsForProduct as $rawMaterialId){
				$productionRunConditions=array(
					'ProductionRun.production_run_date >=' => $startDate,
					'ProductionRun.production_run_date <'=> $endDatePlusOne,
					'ProductionRun.finished_product_id'=> $productId,
					'ProductionRun.raw_material_id'=> $rawMaterialId,
				);
				
				$productionRunIds=$this->ProductionRun->find('list',array(
					'fields'=>'ProductionRun.id',
					'conditions'=>$productionRunConditions,
				));
			
				$productionMovementConditions=array(
					'ProductionMovement.movement_date >=' => $startDate,
					'ProductionMovement.movement_date <'=> $endDatePlusOne,
					'ProductionMovement.product_id'=> $productId,
					'ProductionMovement.production_run_id'=> $productionRunIds,
				);
			
				// check for the production movements first
				$productionMovements=$this->ProductionMovement->find('all',array(
					'conditions'=>$productionMovementConditions,
				));
				$relevantProduct=array();
				if (!empty($productionMovements)){
					$relevantProduct['product_id']=$productId;
					$relevantProduct['raw_material_id']=$rawMaterialId;
				}
				else {
					if ($displayOptionId==DISPLAY_ALL_WITH_INVENTORY){
						$boolStockPresent=false;
						
						$stockItemConditions=[
							'StockItem.product_id' => $productId,
							'StockItem.raw_material_id' => $rawMaterialId,
              // CONDITIONS ADDED 20180314
              'StockItem.stockitem_creation_date <'=> $startDate,        
              'StockItem.stockitem_depletion_date >='=> $startDate,
						];						
						$stockItemIds=$this->StockItem->find('list',array(
							'fields'=>array('StockItem.id'),
							'conditions'=>$stockItemConditions,
						));
						if (!empty($stockItemIds)){
							$latestInitialStockItemLog=$this->StockItemLog->find('first',array(
								'conditions'=>array(
									'StockItemLog.stockitem_id'=>$stockItemIds,
									'StockItemLog.stockitem_date <'=>$startDate,
								),
								'order'=>'StockItemLog.id DESC',
							));
							if ($latestInitialStockItemLog['StockItemLog']['product_quantity'] >0){
								$boolStockPresent=true;
							}
							else {
								$latestFinalStockItemLog=$this->StockItemLog->find('first',array(
									'conditions'=>array(
										'StockItemLog.stockitem_id'=>$stockItemIds,
										'StockItemLog.stockitem_date <'=>$endDatePlusOne,
									),
									'order'=>'StockItemLog.id DESC',
								));
								if ($latestFinalStockItemLog['StockItemLog']['product_quantity'] >0){
									$boolStockPresent=true;
								}
							}
						}
						if ($boolStockPresent){
							$relevantProduct=array();
							$relevantProduct['product_id']=$productId;
							$relevantProduct['raw_material_id']=$rawMaterialId;
						}
					}
				}
				if (!empty($relevantProduct)){
					$relevantProducts[]=$relevantProduct;
				}
			}
		}
		//pr($relevantProducts);	
		// now we finally made it to the point that we know which combinations of finished products and raw materials we have to take into account
		// we loop through all combinations for each production result code to retrieve the quantities and values
		
		for ($rp=0;$rp<count($relevantProducts);$rp++){
			$finishedProduct=$this->Product->find('first',array(
				'conditions'=>array(
					'Product.id'=>$relevantProducts[$rp]['product_id'],
				),
			));
			$relevantProducts[$rp]['FinishedProduct']=$finishedProduct['Product'];
			$rawMaterial=$this->Product->find('first',array(
				'conditions'=>array(
					'Product.id'=>$relevantProducts[$rp]['raw_material_id'],
				),
			));
		 if (!empty($rawMaterial)){					
			$relevantProducts[$rp]['RawMaterial']=$rawMaterial['Product'];
		 }
		}
		
		switch ($sortOptionId){
			case SORT_BY_FINISHED_PRODUCT:
				usort($relevantProducts,array($this,'sortByFinishedProduct'));
				break;
			case SORT_BY_RAW_MATERIAL:	
				usort($relevantProducts,array($this,'sortByRawMaterial'));
				break;
		}
		
		//pr($relevantProducts);
		$producedMaterials=array();
		foreach ($relevantProducts as $relevantProduct){
			$producedMaterial=$relevantProduct;
			$productId=$relevantProduct['product_id'];
			$rawMaterialId=$relevantProduct['raw_material_id'];
			
			$productionRunConditions=array(
				'ProductionRun.production_run_date >=' => $startDate,
				'ProductionRun.production_run_date <'=> $endDatePlusOne,
				'ProductionRun.finished_product_id'=> $productId,
				'ProductionRun.raw_material_id'=> $rawMaterialId,
			);
			$productionRunIds=$this->ProductionRun->find('list',array(
				'fields'=>'ProductionRun.id',
				'conditions'=>$productionRunConditions,
			));
			
			$productionResultCodeValues=array();
			foreach ($allProductionResultCodes as $productionResultCodeId=>$productionResultCodeCode){
				//echo "production result code is ".$productionResultCodeId."<br/>";
				$productUnitPrice=0;
				$productInitialStock=0;
				$productInitialValue=0;
				$productProducedQuantity=0;
				$productProducedValue=0;
				$productReclassifiedQuantity=0;
				$productReclassifiedValue=0;
				$productSoldQuantity=0;
				$productSoldValue=0;
				$productReclassifiedQuantity=0;
				$productReclassifiedValue=0;
				$productFinalStock=0;
				$productFinalValue=0;
				
				$stockItemConditions=[
					'StockItem.product_id'=>$productId,
					'StockItem.raw_material_id'=>$rawMaterialId,
					'StockItem.production_result_code_id'=>$productionResultCodeId,
          // CONDITIONS ADDED 20180314
          //'StockItem.stockitem_creation_date <'=> $startDate,        
          //'StockItem.stockitem_depletion_date >='=> $startDate,
          'StockItem.stockitem_creation_date <'=> $endDatePlusOne,        
          'StockItem.stockitem_depletion_date >='=> $endDatePlusOne,
				];
				$stockItemIds=$this->StockItem->find('list',[
					'fields'=>['StockItem.id'],
					'conditions'=>$stockItemConditions,
				]);
        //pr($stockItemIds);
				// 20170202 we exclude transfers from the list for now as it is not really relevant where the products are located
				$stockMovementConditions=array(
					'StockMovement.movement_date >='=>$startDate,
					'StockMovement.movement_date <'=>$endDatePlusOne,
					'StockMovement.stockitem_id'=>$stockItemIds,
					'StockMovement.bool_transfer'=>false,
				);
				$stockMovements=$this->StockMovement->find('all',array(
					'fields'=>array(
						'StockMovement.bool_input','StockMovement.bool_reclassification',
						'StockMovement.product_quantity',
						'StockMovement.product_total_price',
						'StockMovement.stockitem_id',
					),
					'conditions'=>$stockMovementConditions,
				));
				if (!empty($stockMovements)){
					//pr($stockMovements);
					foreach ($stockMovements as $stockMovement){
						//pr($stockMovement);
						if (!$stockMovement['StockMovement']['bool_input']){					
							if (!$stockMovement['StockMovement']['bool_reclassification']){
								$productSoldQuantity+=$stockMovement['StockMovement']['product_quantity'];
								$linkedStockItem=$this->StockItem->find('first',array(
									'conditions'=>array(
										'StockItem.id'=>$stockMovement['StockMovement']['stockitem_id'],
									),
								));
								$productSoldValue+=$stockMovement['StockMovement']['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
							}
							else {
								$productReclassifiedQuantity+=$stockMovement['StockMovement']['product_quantity'];
								$productReclassifiedValue+=$stockMovement['StockMovement']['product_total_price'];
							}
						} 
						else {
							if ($stockMovement['StockMovement']['bool_reclassification']){
								$productReclassifiedQuantity-=$stockMovement['StockMovement']['product_quantity'];
								//if ($productId==13){
								//	echo "rested from product reclassified quantity".$stockMovement['product_quantity'];
								//	echo "resulting reclassified quantity is ".$productReclassifiedQuantity."<br/>";
								//}
								$productReclassifiedValue-=$stockMovement['StockMovement']['product_total_price'];
							}
						}
					}
				}
			
				$productionMovementConditions=array(
					'ProductionMovement.movement_date >=' => $startDate,
					'ProductionMovement.movement_date <'=> $endDatePlusOne,
					'ProductionMovement.production_result_code_id'=> $productionResultCodeId,
					'ProductionMovement.production_run_id'=> $productionRunIds,
					'ProductionMovement.bool_input'=> false,
				);
				
				$productionMovements = $this->ProductionMovement->find('all', array(
						'fields'=>array(
							'ProductionMovement.product_quantity','ProductionMovement.product_unit_price',
						),
						'conditions' => $productionMovementConditions,
					));
				
				if (!empty($productionMovements)){
					foreach ($productionMovements as $productionMovement){					
						$productProducedQuantity+=$productionMovement['ProductionMovement']['product_quantity'];
						$productProducedValue+=$productionMovement['ProductionMovement']['product_unit_price']*$productionMovement['ProductionMovement']['product_quantity'];
					}
				}
			
				foreach ($stockItemIds as $stockItemId){
					//echo "stock item id is ".$stockItemId."<br/>";
					$this->StockItemLog->recursive=-1;
					$initialStockItemLogForStockItem=$this->StockItemLog->find('first',array(
						'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
						'conditions' => array(
							'StockItemLog.stockitem_id ='=> $stockItemId,
							'StockItemLog.stockitem_date <'=>$startDate
						),
						'order'=>'StockItemLog.id DESC'
					));
					if (!empty($initialStockItemLogForStockItem)){
						$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
						$productInitialValue+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity']*$initialStockItemLogForStockItem['StockItemLog']['product_unit_price'];
					}
					$finalStockItemLogForStockItem=$this->StockItemLog->find('first',[
						'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
						'conditions' => [
							'StockItemLog.stockitem_id ='=> $stockItemId,
							'StockItemLog.stockitem_date <'=>$endDatePlusOne
						],
						'order'=>'StockItemLog.id DESC'
					]);
					if (!empty($finalStockItemLogForStockItem)){
						$productFinalStock+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity'];
						$productFinalValue+=$finalStockItemLogForStockItem['StockItemLog']['product_quantity']*$finalStockItemLogForStockItem['StockItemLog']['product_unit_price'];
					}
					//pr($stockItemForProduct);
					
					if ($productInitialValue != 0){
						$productUnitPrice=$productInitialStock/$productInitialValue;
					}
				}
			
				$productionResultCodeValues[$productionResultCodeId]['id']=$productId;
				$productionResultCodeValues[$productionResultCodeId]['name']=$productName;
				$productionResultCodeValues[$productionResultCodeId]['unit_price']=$productUnitPrice;
				$productionResultCodeValues[$productionResultCodeId]['initial_quantity']=$productInitialStock;
				$productionResultCodeValues[$productionResultCodeId]['initial_value']=$productInitialValue;
				$productionResultCodeValues[$productionResultCodeId]['produced_quantity']=$productProducedQuantity;
				$productionResultCodeValues[$productionResultCodeId]['produced_value']=$productProducedValue;
				$productionResultCodeValues[$productionResultCodeId]['reclassified_quantity']=$productReclassifiedQuantity;
				$productionResultCodeValues[$productionResultCodeId]['reclassified_value']=$productReclassifiedValue;
				$productionResultCodeValues[$productionResultCodeId]['sold_quantity']=$productSoldQuantity;
				$productionResultCodeValues[$productionResultCodeId]['sold_value']=$productSoldValue;
				$productionResultCodeValues[$productionResultCodeId]['final_quantity']=$productFinalStock;
				$productionResultCodeValues[$productionResultCodeId]['final_value']=$productFinalValue;
			}
			$producedMaterial['ProductionResultCodeValues']=$productionResultCodeValues;
			$producedMaterials[]=$producedMaterial;
		}
		//pr($producedMaterials);
		$this->set(compact('producedMaterials'));
	}
	
	public function sortByFinishedProduct($firstProduct,$secondProduct){
		//if (($firstTerm['AccountingRegister']['id']==289 ||$secondTerm['AccountingRegister']['id']==289)&&($firstTerm['AccountingRegister']['id']==283 ||$secondTerm['AccountingRegister']['id']==283)){
		//	pr($firstTerm);
		//	pr($secondTerm);
		//}
		if($firstProduct['FinishedProduct']['id'] != $secondProduct['FinishedProduct']['id']){ 		
			return ($firstProduct['FinishedProduct']['name'] < $secondProduct['FinishedProduct']['name']) ? -1 : 1;
		}
		else {
			// finished product name is the same, now compare by raw material name
			return ($firstProduct['RawMaterial']['name'] < $secondProduct['RawMaterial']['name']) ? -1 : 1;
		}
	}
	public function sortByRawMaterial($firstProduct,$secondProduct){
		//if (($firstTerm['AccountingRegister']['id']==289 ||$secondTerm['AccountingRegister']['id']==289)&&($firstTerm['AccountingRegister']['id']==283 ||$secondTerm['AccountingRegister']['id']==283)){
		//	pr($firstTerm);
		//	pr($secondTerm);
		//}
		if (array_key_exists('RawMaterial',$firstProduct) && array_key_exists('RawMaterial',$secondProduct) && $firstProduct['RawMaterial']['id'] != $secondProduct['RawMaterial']['id']){ 		
			return ($firstProduct['RawMaterial']['name'] < $secondProduct['RawMaterial']['name']) ? -1 : 1;
			
		}
		else {
			// rawmaterial name is the same, now compare by finished product name
			return ($firstProduct['FinishedProduct']['name'] < $secondProduct['FinishedProduct']['name']) ? -1 : 1;
		}
	}
	
	public function guardarReporteProduccionDetalle() {
		$exportData=$_SESSION['productionDetailReport'];
		$this->set(compact('exportData'));
	}
		
	public function estadoResultados($startDate = null,$endDate=null) {
		$this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductNature');
    
    $this->loadModel('ThirdParty');
		$this->loadModel('Order');
		$this->loadModel('StockMovement');
    $this->loadModel('ProductionMovement');
    
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
    $this->StockItem->recursive=-1;
		$this->ProductionMovement->recursive=-1;
		
		$this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    $this->loadModel('WarehouseProduct');
    
    $this->loadModel('User');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $startDate = null;
		$endDate = null;
    
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
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
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
		$warehouseProductIds=[];
    $warehouseOrderIds=[];
    if ($warehouseId > 0){
      $warehouseProductIds=$this->WarehouseProduct->getProductIdsForWarehouse($warehouseId);
      $warehouseOrderIds=$this->Order->find('list',[
        'fields'=>'Order.id',
        'conditions'=>[
          'Order.warehouse_id'=>$warehouseId,
          'Order.order_date >=' =>$startDate,
          'Order.order_date <' => $endDatePlusOne,
        ]
      ]);
    }
    //pr($warehouseOrderIds);
    //pr($warehouseProductIds);
    /*********************************************************
		RAW MATERIALS
		**********************************************************/
		$rawConditions=[
      //'Product.product_type_id'=>PRODUCT_TYPE_PREFORMA
      'Product.product_nature_id'=>PRODUCT_NATURE_RAW
    ];
    if ($warehouseId >0){
      $rawConditions['Product.id']=$warehouseProductIds;
    }
    $allRawMaterials=$this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => $rawConditions,
      'order'=>'Product.name',
    ]);
    //pr($allRawMaterials);
    $rawMaterials=[];
    $i=0;
    foreach ($allRawMaterials as $rawMaterial){
      //pr($producedMaterial);
			$productId=$rawMaterial['Product']['id'];
			$productName=$rawMaterial['Product']['name'];
			
			$rawMaterials[$productId]['id']=$productId;
			$rawMaterials[$productId]['name']=$productName;
			$rawMaterials[$productId]['total_quantity']=0;
			$rawMaterials[$productId]['total_price']=0;
			$rawMaterials[$productId]['total_cost']=0;
			$rawMaterials[$productId]['total_gain']=0;
    }
    
    $stockMovementConditions=[
      'StockMovement.bool_input'=>false,
      'StockMovement.movement_date >=' =>$startDate,
      'StockMovement.movement_date <' => $endDatePlusOne,
      'StockMovement.bool_reclassification'=>false,
      'StockMovement.bool_transfer'=>false,
      'StockMovement.product_quantity > '=>0,
      
    ];
    if ($warehouseId >0){
      $stockMovementConditions['StockMovement.order_id']=$warehouseOrderIds;
    }
    
		/*********************************************************
		PRODUCED MATERIALS
		*********************************************************/
		/*
    $producedProductTypes=$this->ProductType->find('list',[
			'fields'=>'ProductType.id',
			'conditions'=>[
				'ProductType.product_category_id'=> CATEGORY_PRODUCED,
			],
		]);
    */
		//pr($producedProductTypes);
    $producedConditions=[
				//'Product.product_type_id'=> $producedProductTypes,
        'Product.product_nature_id'=> PRODUCT_NATURE_PRODUCED,
			];
    if ($warehouseId >0){
      $producedConditions['Product.id']=$warehouseProductIds;
    }
    
		$allProducedMaterials=$this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => $producedConditions,
			'contain'=>[
				'StockMovement'=>[
          'conditions'=>$stockMovementConditions,
          'StockItem',
        ]
			],
		]);
    //pr($allProducedMaterials);
    $producedMaterials=[];
		$i=0;
		foreach ($allProducedMaterials as $producedMaterial){
			//pr($producedMaterial);
			$productId=$producedMaterial['Product']['id'];
			$productName=$producedMaterial['Product']['name'];
			
			$productTotalQuantity=0;
			$productTotalValuePrice=0;
			$productTotalValueCost=0;
			
			foreach ($producedMaterial['StockMovement'] as $stockMovement){
				//pr($stockMovement);
        //20180503 added conditions to stockmovement selection directly
        /*
				if (!$stockMovement['bool_input']){
					if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						if (!$stockMovement['bool_reclassification']){
							$linkedStockItem=$this->StockItem->find('first',array(
								'conditions'=>array(
									'StockItem.id'=>$stockMovement['stockitem_id'],
								),
							));
							$productTotalQuantity+=$stockMovement['product_quantity'];
							$productTotalValueCost+=$stockMovement['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
							$productTotalValuePrice+=$stockMovement['product_total_price'];
						}
					}
				}
        */        
        if ($stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
          $productTotalQuantity+=$stockMovement['product_quantity'];
          $productTotalValueCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
          $productTotalValuePrice+=$stockMovement['product_total_price'];
        }
        $rawMaterialId=$stockMovement['StockItem']['raw_material_id'];
        $rawMaterials[$rawMaterialId]['total_quantity']+=$stockMovement['product_quantity'];
        $rawMaterials[$rawMaterialId]['total_cost']+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
        $rawMaterials[$rawMaterialId]['total_price']+=$stockMovement['product_total_price'];
			}
			
			$producedMaterials[$i]['id']=$productId;
			$producedMaterials[$i]['name']=$productName;
			$producedMaterials[$i]['total_quantity']=$productTotalQuantity;
			$producedMaterials[$i]['total_price']=$productTotalValuePrice;
			$producedMaterials[$i]['total_cost']=$productTotalValueCost;
			$producedMaterials[$i]['total_gain']=$productTotalValuePrice-$productTotalValueCost;
			$i++;
		}
    
    foreach ($rawMaterials as $id=>$rawMaterialData){
      $rawMaterials[$id]['total_gain']=$rawMaterials[$id]['total_price']-$rawMaterials[$id]['total_cost'];
    }
    $this->set(compact('rawMaterials'));
		
		/*********************************************************
		TAPONES
		*********************************************************/
    /*
    $otherProductTypes=$this->ProductType->find('list',[
			'fields'=>'ProductType.id',
			'conditions'=>[
				'ProductType.product_category_id'=> CATEGORY_OTHER,
        'ProductType.id !='=> PRODUCT_TYPE_SERVICE,
			],
		]);
    */  
    $otherConditions=[
      //'Product.product_type_id'=> $otherProductTypes,
      'Product.product_nature_id'=> [PRODUCT_NATURE_BOTTLES_BOUGHT,PRODUCT_NATURE_ACCESORIES],
      'Product.product_type_id !='=> PRODUCT_TYPE_SERVICE,
    ];
		if ($warehouseId > 0){
      $otherConditions['Product.id']=$warehouseProductIds;
    }
		//pr($otherProductTypes);
		$allOtherMaterials=$this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => $otherConditions,
			'contain'=>[
				'StockMovement'=>[
          'conditions'=>$stockMovementConditions,
          'StockItem',
        ],
        'ProductType'=>[
          'fields'=>'ProductType.id,ProductType.name',
        ],
			],
		]);
		$otherMaterials=[];
    $otherMaterialProductTypes=[];
		$i=0;
		foreach ($allOtherMaterials as $otherMaterial){
			$productId=$otherMaterial['Product']['id'];
			$productName=$otherMaterial['Product']['name'];
			
			$productTotalQuantity=0;
			$productTotalValuePrice=0;
			$productTotalValueCost=0;
			
			foreach ($otherMaterial['StockMovement'] as $stockMovement){
        /*
				if (!$stockMovement['bool_input']){
					if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							$linkedStockItem=$this->StockItem->find('first',array(
								'conditions'=>array(
									'StockItem.id'=>$stockMovement['stockitem_id'],
								),
							));
							$productTotalQuantity+=$stockMovement['product_quantity'];
							$productTotalValueCost+=$stockMovement['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
							$productTotalValuePrice+=$stockMovement['product_total_price'];
							
							//if ($stockMovement['product_id']==4){
								//pr($stockMovement);
								//echo $productTotalValueCost;
								//echo $productTotalValuePrice;
							//}
						}
					}
				}
        */
        //pr($stockMovement);
        $productTotalQuantity+=$stockMovement['product_quantity'];
        $productTotalValueCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
        $productTotalValuePrice+=$stockMovement['product_total_price'];
			}
			
			$otherMaterials[$i]['id']=$productId;
			$otherMaterials[$i]['name']=$productName;
			$otherMaterials[$i]['total_quantity']=$productTotalQuantity;
			$otherMaterials[$i]['total_price']=$productTotalValuePrice;
			$otherMaterials[$i]['total_cost']=$productTotalValueCost;
			$otherMaterials[$i]['total_gain']=$productTotalValuePrice-$productTotalValueCost;
			
      
      $productTypeId=$otherMaterial['ProductType']['id'];
      //if ($productTypeId != PRODUCT_TYPE_CAP){
        if (!array_key_exists($productTypeId,$otherMaterialProductTypes)){
          $otherMaterialProductTypes[$productTypeId]['ProductType']=[
            'id'=>$productTypeId,
            'name'=>$otherMaterial['ProductType']['name'],
          ];
          $otherMaterialProductTypes[$productTypeId]['Products']=[];
        }
        $otherMaterialProductTypes[$productTypeId]['Products'][]=$otherMaterials[$i];
      //}
      $i++;
		}
    //pr($otherMaterials);
    //pr($otherMaterialProductTypes);
    /******inicio para productos ingroup *****************/
	    $otherConditions=[
      //'Product.product_type_id'=> $otherProductTypes,
      'Product.product_nature_id'=> [PRODUCT_INGROUP],
      'Product.product_type_id '=> 18,
    ];
		if ($warehouseId > 0){
      $otherConditions['Product.id']=$warehouseProductIds;
    }
		//pr($otherProductTypes);
		$ingroupProducts=$this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => $otherConditions,
			'contain'=>[
				'StockMovement'=>[
          'conditions'=>$stockMovementConditions,
          'StockItem',
        ],
        'ProductType'=>[
          'fields'=>'ProductType.id,ProductType.name',
        ],
			],
		]);
		$productsIngroup=[];
    $otherMaterialProductTypes=[];
		$i=0;
		foreach ($ingroupProducts as $itemIngroup){
			$productId=$itemIngroup['Product']['id'];
			$productName=$itemIngroup['Product']['name'];
			
			$productTotalQuantity=0;
			$productTotalValuePrice=0;
			$productTotalValueCost=0;
			
			foreach ($itemIngroup['StockMovement'] as $stockMovement){
        /*
				if (!$stockMovement['bool_input']){
					if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							$linkedStockItem=$this->StockItem->find('first',array(
								'conditions'=>array(
									'StockItem.id'=>$stockMovement['stockitem_id'],
								),
							));
							$productTotalQuantity+=$stockMovement['product_quantity'];
							$productTotalValueCost+=$stockMovement['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
							$productTotalValuePrice+=$stockMovement['product_total_price'];
							
							//if ($stockMovement['product_id']==4){
								//pr($stockMovement);
								//echo $productTotalValueCost;
								//echo $productTotalValuePrice;
							//}
						}
					}
				}
        */
        //pr($stockMovement);
        $productTotalQuantity+=$stockMovement['product_quantity'];
        $productTotalValueCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
        $productTotalValuePrice+=$stockMovement['product_total_price'];
			}
			
			$productsIngroup[$i]['id']=$productId;
			$productsIngroup[$i]['name']=$productName;
			$productsIngroup[$i]['total_quantity']=$productTotalQuantity;
			$productsIngroup[$i]['total_price']=$productTotalValuePrice;
			$productsIngroup[$i]['total_cost']=$productTotalValueCost;
			$productsIngroup[$i]['total_gain']=$productTotalValuePrice-$productTotalValueCost;
			
      
      $productTypeId=$itemIngroup['ProductType']['id'];
      //if ($productTypeId != PRODUCT_TYPE_CAP){
        if (!array_key_exists($productTypeId,$otherMaterialProductTypes)){
          $otherMaterialProductTypes[$productTypeId]['ProductType']=[
            'id'=>$productTypeId,
            'name'=>$itemIngroup['ProductType']['name'],
          ];
          $otherMaterialProductTypes[$productTypeId]['Products']=[];
        }
        $otherMaterialProductTypes[$productTypeId]['Products'][]=$productsIngroup[$i];
      //}
      $i++;
		}
    /******fin para productos ingroup *****************/
	
	
    $this->set(compact('otherMaterialProductTypes'));
/*********************************************************
		SERVICES
		*********************************************************/
		
		$allServices=$this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' => ['Product.product_type_id'=> PRODUCT_TYPE_SERVICE,],
			'contain'=>[
				'StockMovement'=>
        [
          'conditions'=>$stockMovementConditions,
          'StockItem',
        ],
			],
		]);
		
    $services=[];
		$i=0;
		foreach ($allServices as $service){
			$productId=$service['Product']['id'];
			$productName=$service['Product']['name'];
			
			$productTotalQuantity=0;
			$productTotalValuePrice=0;
			$productTotalValueCost=0;
			
			foreach ($service['StockMovement'] as $stockMovement){
        //pr($stockMovement);-
        $productTotalQuantity+=$stockMovement['product_quantity'];
        $productTotalValueCost+=$stockMovement['product_quantity']*$stockMovement['service_unit_cost'];
        $productTotalValuePrice+=$stockMovement['product_total_price'];
			}
			
			$services[$i]['id']=$productId;
			$services[$i]['name']=$productName;
			$services[$i]['total_quantity']=$productTotalQuantity;
			$services[$i]['total_price']=$productTotalValuePrice;
			$services[$i]['total_cost']=$productTotalValueCost;
			$services[$i]['total_gain']=$productTotalValuePrice-$productTotalValueCost;
			$i++;
		}
    $this->set(compact('services'));
    
		/*********************************************************
		SUMINISTROS
		*********************************************************/
		/*
      $consumibleProductTypes=$this->ProductType->find('list',[
        'fields'=>'ProductType.id',
        'conditions'=>['ProductType.product_category_id'=> CATEGORY_CONSUMIBLE,],
      ]);
    */
    $bagConditions=[
      //'Product.product_type_id'=> $consumibleProductTypes,
      'Product.product_nature_id'=> PRODUCT_NATURE_BAGS,
    ];
    if ($warehouseId >0){
      $bagConditions['Product.id']=$warehouseProductIds;
    }
		
		//pr($consumibleProductTypes);
		$allConsumibleMaterials=$this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
			'conditions' =>$bagConditions,
			'contain'=>[
				'ProductionMovement'=>[
          'conditions'=>[
            'ProductionMovement.bool_input'=>true,
            'ProductionMovement.movement_date >=' =>$startDate,
            'ProductionMovement.movement_date <' => $endDatePlusOne,
            'ProductionMovement.product_quantity > '=>0
          ],
          'StockItem',
        ]
			],
		]);
		
    $consumibleMaterials=[];
		$i=0;
		foreach ($allConsumibleMaterials as $consumibleMaterial){
			$productId=$consumibleMaterial['Product']['id'];
			$productName=$consumibleMaterial['Product']['name'];
			
			$productTotalQuantity=0;
			$productTotalValuePrice=0;
			$productTotalValueCost=0;
			
			foreach ($consumibleMaterial['ProductionMovement'] as $productionMovement){
        $productTotalQuantity+=$productionMovement['product_quantity'];
        $productTotalValueCost+=$productionMovement['product_quantity']*$productionMovement['StockItem']['product_unit_price'];
        $productTotalValuePrice+=$productionMovement['product_unit_price']*$productionMovement['product_quantity'];
			}
			
			$consumibleMaterials[$i]['id']=$productId;
			$consumibleMaterials[$i]['name']=$productName;
			$consumibleMaterials[$i]['total_quantity']=$productTotalQuantity;
			$consumibleMaterials[$i]['total_price']=$productTotalValuePrice;
			$consumibleMaterials[$i]['total_cost']=$productTotalValueCost;
			$consumibleMaterials[$i]['total_gain']=$productTotalValuePrice-$productTotalValueCost;
			$i++;
		}

		$this->set(compact('consumibleMaterials'));
    
		$this->ThirdParty->recursive=0;
		$clients=$this->ThirdParty->find('all',[
			'conditions'=>[
				'ThirdParty.bool_provider'=>false,
				'ThirdParty.bool_active'=>true,
			],
			'order'=>'company_name',
		]);
		//pr($clients);
		$clientutility=[];
		
		for ($i=0;$i<count($clients);$i++){
			$clientutility[$i]['id']=$clients[$i]['ThirdParty']['id'];
			$clientutility[$i]['name']=$clients[$i]['ThirdParty']['company_name'];
			
			$salesClientPeriod=$this->Order->find('list',[
				'fields'=>'Order.id',
				'conditions'=>[
					'Order.third_party_id'=>$clients[$i]['ThirdParty']['id'],
					'Order.order_date >='=>$startDate,
					'Order.order_date <'=>$endDatePlusOne,
          'Order.warehouse_id'=>$warehouseId,
				],
			]);
			//pr($salesClientPeriod);
			
			$productTotalQuantity=0;
			$productTotalValueCost=0;
			$productTotalValuePrice=0;
			
			if(!empty($salesClientPeriod)){
				$bottleSales=$this->StockMovement->find('all',array(
					'fields'=>array('StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.product_total_price'),
					'conditions'=>array(
						'StockMovement.production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
						'StockMovement.order_id'=>$salesClientPeriod,
						'StockMovement.bool_reclassification'=>false,
            'StockMovement.bool_transfer'=>false,
						'StockMovement.bool_input'=>false,
						'Product.product_type_id'=>PRODUCT_TYPE_BOTTLE,
					),
				));
				//pr($bottleSales);
				foreach ($bottleSales as $bottleSale){
					//pr($bottleSale);
					$linkedStockItem=$this->StockItem->find('first',array(
						'conditions'=>array(
							'StockItem.id'=>$bottleSale['StockMovement']['stockitem_id'],
						),
					));
					$productTotalQuantity+=$bottleSale['StockMovement']['product_quantity'];
					$productTotalValueCost+=$bottleSale['StockMovement']['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
					$productTotalValuePrice+=$bottleSale['StockMovement']['product_total_price'];
				}
			}
			
			$clientutility[$i]['bottle_total_quantity']=$productTotalQuantity;
			$clientutility[$i]['bottle_total_price']=$productTotalValuePrice;
			$clientutility[$i]['bottle_total_cost']=$productTotalValueCost;
			$clientutility[$i]['bottle_total_gain']=$productTotalValuePrice-$productTotalValueCost;
			
			$productTotalQuantity=0;
			$productTotalValueCost=0;
			$productTotalValuePrice=0;
			
			if(!empty($salesClientPeriod)){
				$capSales=$this->StockMovement->find('all',array(
					'fields'=>array('StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.product_total_price'),
					'conditions'=>array(
						'StockMovement.order_id'=>$salesClientPeriod,
						'StockMovement.bool_reclassification'=>false,
            'StockMovement.bool_transfer'=>false,
						'StockMovement.bool_input'=>false,
						'Product.product_type_id'=>PRODUCT_TYPE_CAP,
					),
				));
				foreach ($capSales as $capSale){
					$linkedStockItem=$this->StockItem->find('first',array(
						'conditions'=>array(
							'StockItem.id'=>$capSale['StockMovement']['stockitem_id'],
						),
					));
					$productTotalQuantity+=$capSale['StockMovement']['product_quantity'];
					$productTotalValueCost+=$capSale['StockMovement']['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
					$productTotalValuePrice+=$capSale['StockMovement']['product_total_price'];
				}
			}
			
			$clientutility[$i]['cap_total_quantity']=$productTotalQuantity;
			$clientutility[$i]['cap_total_price']=$productTotalValuePrice;
			$clientutility[$i]['cap_total_cost']=$productTotalValueCost;
			$clientutility[$i]['cap_total_gain']=$productTotalValuePrice-$productTotalValueCost;
		}
		
		$this->set(compact('producedMaterials','otherMaterials','startDate','endDate','clientutility'));
    
    $this->StockMovement->virtualFields['total_in_A']=0;
    $stockMovementsAdjustmentsInA=$this->StockMovement->find('all',[
      'fields'=>['SUM(StockMovement.product_quantity) AS StockMovement__total_in_A'],
      'conditions'=>[
        'OR'=>[
          ['bool_adjustment'=>true,],
          ['bool_reclassification'=>true,],
        ],
        'StockMovement.bool_input'=>true,
        'StockMovement.production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
        'StockMovement.movement_date >=' =>$startDate,
        'StockMovement.movement_date <' => $endDatePlusOne,
        'StockMovement.bool_transfer'=>false,
        'StockMovement.product_quantity > '=>0,
      ],
    ]);
    //pr($stockMovementsAdjustmentsInA);
    $this->set(compact('stockMovementsAdjustmentsInA'));
    $this->StockMovement->virtualFields['total_out_A']=0;
    $stockMovementsAdjustmentsOutA=$this->StockMovement->find('all',[
      'fields'=>['SUM(StockMovement.product_quantity) AS StockMovement__total_out_A'],
      'conditions'=>[
        'OR'=>[
          ['bool_adjustment'=>true,],
          ['bool_reclassification'=>true,],
        ],
        'StockMovement.bool_input'=>false,
        'StockMovement.production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
        'StockMovement.movement_date >=' =>$startDate,
        'StockMovement.movement_date <' => $endDatePlusOne,
        'StockMovement.bool_transfer'=>false,
        'StockMovement.product_quantity > '=>0,
      ],
    ]);
    //pr($stockMovementsAdjustmentsOutA);
    $this->set(compact('stockMovementsAdjustmentsOutA'));
    
    $allCapsIds=$this->Product->find('list',[
			'fields'=>'Product.id',
			//'conditions' => ['Product.product_type_id'=> $otherProductTypes,],
      'conditions' => $otherConditions,
    ]);
    
    $this->StockMovement->virtualFields['total_in_caps']=0;
    $stockMovementsAdjustmentsInCaps=$this->StockMovement->find('all',[
      'fields'=>['SUM(StockMovement.product_quantity) AS StockMovement__total_in_caps'],
      'conditions'=>[
        'OR'=>[
          ['bool_adjustment'=>true,],
          ['bool_reclassification'=>true,],
        ],
        'StockMovement.bool_input'=>true,
        'StockMovement.product_id'=>$allCapsIds,
        'StockMovement.movement_date >=' =>$startDate,
        'StockMovement.movement_date <' => $endDatePlusOne,
        'StockMovement.bool_transfer'=>false,
        'StockMovement.product_quantity > '=>0,
      ],
    ]);
    //pr($stockMovementsAdjustmentsInCaps);
    $this->set(compact('stockMovementsAdjustmentsInCaps'));
    $this->StockMovement->virtualFields['total_out_caps']=0;
    $stockMovementsAdjustmentsOutCaps=$this->StockMovement->find('all',[
      'fields'=>['SUM(StockMovement.product_quantity) AS StockMovement__total_out_caps'],
      'conditions'=>[
        'OR'=>[
          ['bool_adjustment'=>true,],
          ['bool_reclassification'=>true,],
        ],
        'StockMovement.bool_input'=>false,
        'StockMovement.product_id'=>$allCapsIds,
        'StockMovement.movement_date >=' =>$startDate,
        'StockMovement.movement_date <' => $endDatePlusOne,
        'StockMovement.bool_transfer'=>false,
        'StockMovement.product_quantity > '=>0,
      ],
    ]);
    //pr($stockMovementsAdjustmentsOutCaps);
    $this->set(compact('stockMovementsAdjustmentsOutCaps'));
    
   
	}
	
	public function guardarReporteEstado() {
		$exportData=$_SESSION['statusReport'];
		$this->set(compact('exportData'));
	}
	
  public function utilidadAnual($startDate = null,$endDate=null) {
    $this->loadModel('Order');
    $this->loadModel('ProductType');
    
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductNature');
    
    $this->loadModel('ThirdParty');
		$this->loadModel('Order');
		$this->loadModel('StockMovement');
    $this->loadModel('ProductionMovement');
    
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
    $this->StockItem->recursive=-1;
		$this->ProductionMovement->recursive=-1;
		
		$this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    $this->loadModel('WarehouseProduct');
    
    $this->loadModel('User');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $productTypes=$this->ProductType->find('list');
    $this->set(compact('productTypes'));
    $productNatures=$this->ProductNature->getProductNatureList([PRODUCT_NATURE_RAW,PRODUCT_NATURE_BAGS]);
    
    $productNatureProducts=[];
    foreach ($productNatures as $currentProductNatureId =>$currentProductNatureName){
      $productNatureProducts[$currentProductNatureId]['Product']=$this->Product->getProductsByProductNature($currentProductNatureId);
    }
    //pr($productNatureProducts);
    $productNatures[-1]='Servicios';
    $this->set(compact('productNatures'));
    $warehouseOptions=[
      0=>'-- Bodegas separadas --',
      1=>'-- Bodegas  combinadas --',
    ];
    $this->set(compact('warehouseOptions'));
    
    define('TOTALS','1');
    define('DETAILS','2');
    $displayOptions=[
      TOTALS=>'-- Totales --',
      DETAILS=>'-- Detalles --',
    ];
    $this->set(compact('displayOptions'));
    
    define('QUANTITY','1');
    define('PROFITCS','2');
    define('PROFITPCT','3');
    define('QUANTITY_PROFITCS','4');
    define('QUANTITY_PROFITPCT','5');
    define('PROFITCS_PROFITPCT','6');
    define('QUANTITY_PROFITCS_PROFITPCT','7');
    $dataOptions=[
      QUANTITY=>'-- Cantidad --',
      PROFITCS=>'-- Utilidad C$ --',
      PROFITPCT=>'-- Utilidad % --',
      QUANTITY_PROFITCS=>'-- Cant + Util C$ --',
      QUANTITY_PROFITPCT=>'-- Cant + Util % --',
      PROFITCS_PROFITPCT=>'-- Util C$ + Util % --',
      QUANTITY_PROFITCS_PROFITPCT=>'-- Cant + Util C$ + Util % --',
    ];
    $this->set(compact('dataOptions'));
    
    //$clients=$this->ThirdParty->getActiveClientList();
    $clients=$this->ThirdParty->getClientList();
    //pr($clients);;
    //echo 'count clients is '.count($clients).'<br/>';
    $startDate = null;
		$endDate = null;
    $displayOptionId=TOTALS;
    $dataOptionId=PROFITCS;
    $warehouseId=0;
    $warehouseOptionId=0;
    
    $productNatureId=0;

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
      
      $warehouseId=$this->request->data['Report']['warehouse_id'];
      $warehouseOptionId=$this->request->data['Report']['warehouse_option_id'];
      $displayOptionId=$this->request->data['Report']['display_option_id'];
      $dataOptionId=$this->request->data['Report']['data_option_id'];
      $productNatureId=$this->request->data['Report']['product_nature_id'];
		}
		else if (!empty($_SESSION['startDateProfit']) && !empty($_SESSION['endDateProfit'])){
			$startDate=$_SESSION['startDateProfit'];
			$endDate=$_SESSION['endDateProfit'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
      $startDate=date("Y-m-d",strtotime(date("Y-m-01")));
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDateProfit']=$startDate;
		$_SESSION['endDateProfit']=$endDate;
    
    $this->set(compact('startDate','endDate'));
    $this->set(compact('warehouseOptionId'));
    $this->set(compact('displayOptionId'));
    $this->set(compact('dataOptionId'));
    $this->set(compact('productNatureId'));
    
    $boolShowQuantity=true;
    $boolShowProfitCs=true;
    $boolShowProfitPercent=true;
    $colspan=3;
    $totalColspan=3;
    
    switch ($dataOptionId){
      case QUANTITY:
        $boolShowQuantity=true;
        $boolShowProfitCs=false;
        $boolShowProfitPercent=false;
        $colspan=1;
        $totalColspan=4;
        break;
      case PROFITCS:
        $boolShowQuantity=false;
        $boolShowProfitCs=true;
        $boolShowProfitPercent=false;
        $colspan=1;
        $totalColspan=4;
        break;
      case PROFITPCT:
        $boolShowQuantity=false;
        $boolShowProfitCs=false;
        $boolShowProfitPercent=true;
        $colspan=1;
        $totalColspan=3;
        break;
      case QUANTITY_PROFITCS:
        $boolShowQuantity=true;
        $boolShowProfitCs=true;
        $boolShowProfitPercent=false;
        $colspan=2;
        $totalColspan=5;
        break;
      case QUANTITY_PROFITPCT:
        $boolShowQuantity=true;
        $boolShowProfitCs=false;
        $boolShowProfitPercent=true;
        $colspan=2;
        $totalColspan=4;
        break;
      case PROFITCS_PROFITPCT:
        $boolShowQuantity=false;
        $boolShowProfitCs=true;
        $boolShowProfitPercent=true;
        $colspan=2;
        $totalColspan=4;
        break;
      case QUANTITY_PROFITCS_PROFITPCT:
      default:
        $boolShowQuantity=true;
        $boolShowProfitCs=true;
        $boolShowProfitPercent=true;
        $colspan=3;
        $totalColspan=5;
    }
    $this->set(compact('boolShowQuantity','boolShowProfitCs','boolShowProfitPercent','colspan','totalColspan'));
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    //elseif (count($warehouses) > 1){
      //if ($warehouseId == 0){
      //  if (!empty($_SESSION['warehouseId'])){
      //    $warehouseId = $_SESSION['warehouseId'];
      //  }
      //  elseif (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
      //    $warehouseId = WAREHOUSE_DEFAULT;
      //  }
      //}
    //}
    
    //$_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    $warehouseArray=[];
    if ($warehouseId > 0){
      $warehouseArray[$warehouseId]=[
        'Warehouse'=>[
          'name'=>$warehouses[$warehouseId],
        ]
      ];  
    }
    else {
      foreach ($warehouses as $currentWarehouseId=>$currentWarehouseName){
        $warehouseArray[$currentWarehouseId]=[
          'Warehouse'=>[
            'name'=>$currentWarehouseName,
          ]
        ];  
      }
      $warehouseArray[0]=[
        'Warehouse'=>[
          'name'=>'Todas Bodegas',
        ],
        'ClientUtility',
      ];     
    }
    
    $monthArray=$this->StockItem->getMonthArray($startDate,$endDate);
		//pr($monthArray);
    
    foreach ($productNatures as $currentProductNatureId=>$productNatureName){
      $productNatureProductIds[$currentProductNatureId]=array_keys($this->Product->getProductsByProductNature($currentProductNatureId));
    }
    //pr($productNatures);
    //pr($productNatureProductIds);
    
    $dataArray=[
      'quantity'=>0,
      'cost'=>0,
      'price'=>0,
      'gain'=>0,
    ];
    if (count($warehouseArray) > 0){
      $warehouseRawUtility=[];
      $warehouseProductUtility=[];
      $warehouseClientUtility=[];
      $warehouseMonthUtility=[];
      foreach ($monthArray as $monthId=>$monthData){
        foreach ($productNatures as $currentProductNatureId =>$productNatureName){
          $warehouseMonthUtility[$monthId]['ProductNature'][$currentProductNatureId]=$dataArray;
        }    
      }
    }
    
    $allWarehousesPeriodTotal=[];
    foreach ($productNatures as $currentProductNatureId =>$productNatureName){
      $allWarehousesPeriodTotal[$currentProductNatureId]=$dataArray;
    }
    
    //$clientCount=0;
    foreach ($warehouses as $currentWarehouseId=>$currentWarehouseName){
      if (array_key_exists($currentWarehouseId,$warehouseArray)){
        $rawUtility=[];
        $rawMonthUtility=[];
        $productUtility=[];
        $clientUtility=[];
        $monthUtility=[];
      
        $warehousePeriodTotal=[];
        foreach ($productNatures as $currentProductNatureId =>$productNatureName){
          $warehousePeriodTotal[$currentProductNatureId]=$dataArray;
        }
        foreach ($monthArray as $monthId=>$monthData){
          $rawMonthUtility[$monthId]=$dataArray;
          foreach ($productNatures as $currentProductNatureId =>$productNatureName){
            $monthUtility[$monthId]['ProductNature'][$currentProductNatureId]=$dataArray;
          }    
        }
        
        $warehouseProductIds=$this->WarehouseProduct->getProductIdsForWarehouse($currentWarehouseId);
        $rawConditions=[
          'Product.product_nature_id'=>PRODUCT_NATURE_RAW,
          'Product.id'=>$warehouseProductIds,
        ];
        $rawMaterials=$this->Product->find('list',[
          'fields'=>'Product.id,Product.name',
          'conditions' => $rawConditions,
          'order'=>'Product.name',
        ]);
        $rawMaterialArray=[];
        
        foreach ($rawMaterials as $currentRawMaterialId=>$rawMaterialName){
          $rawUtility['RawMaterial'][$currentRawMaterialId]['Product']['name']=$rawMaterialName;
          if (!array_key_exists($currentRawMaterialId,$warehouseRawUtility)){
            $warehouseRawUtility['RawMaterial'][$currentRawMaterialId]=[
              'Product'=>['name'=>$rawMaterialName,],
              'Month'=>[],
            ];
          }  
            
          foreach ($monthArray as $monthId=>$monthData){
            
              
            //date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
            $monthStartDate=date('Y-m-d',strtotime($monthData['sale_start_date']));
            $monthEndDatePlusOne=date('Y-m-d',strtotime($monthData['sale_end_date_plus_one']));
          
            $warehouseOrderIds=$this->Order->find('list',[
              'fields'=>['Order.id'],
              'conditions'=>[
                'Order.warehouse_id'=>$currentWarehouseId,
                'Order.order_date >='=>$monthStartDate,
                'Order.order_date <'=>$monthEndDatePlusOne,
                
                'Order.stock_movement_type_id'=> MOVEMENT_SALE,
                'Order.bool_annulled'=> false,
              ],
            ]);
            $productStockMovements=[];
            
            if(!empty($warehouseOrderIds)){
              $rawMaterialStockItemIds=$this->StockItem->getExistingStockItemIdsByRawMaterialIdForDateRange($monthStartDate,$monthEndDatePlusOne,$currentRawMaterialId);
              
              $conditions=[
                'StockMovement.order_id'=>$warehouseOrderIds,
                'StockMovement.bool_input'=>false,
                'StockMovement.bool_reclassification'=>false,
                'StockMovement.bool_transfer'=>false,
                'StockMovement.stockitem_id'=>$rawMaterialStockItemIds,
                'StockMovement.product_quantity >'=>0,
                'StockMovement.production_result_code_id >='=>0,
              ];
              //pr($rawMaterialStockItemIds);
              $productStockMovements=$this->StockMovement->find('all',[
                'fields'=>['StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.product_total_price'],
                'conditions'=>$conditions,
                'contain'=>[
                  'StockItem'=>[
                    'fields'=>['StockItem.product_unit_price'],
                  ],
                ],
              ]);
            }
            $productTotalQuantity=0;
            $productTotalValueCost=0;
            $productTotalValuePrice=0;
            
            if (!empty($productStockMovements)){
              //pr($productStockMovements);
              foreach ($productStockMovements as $stockMovement){
                $productTotalQuantity+=$stockMovement['StockMovement']['product_quantity'];
                $productTotalValueCost+=$stockMovement['StockMovement']['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                $productTotalValuePrice+=$stockMovement['StockMovement']['product_total_price'];
              }  
            
            }
            $rawUtility['RawMaterial'][$currentRawMaterialId]['Month'][$monthId]['quantity']=$productTotalQuantity;        
            $rawUtility['RawMaterial'][$currentRawMaterialId]['Month'][$monthId]['cost']=$productTotalValueCost;
            $rawUtility['RawMaterial'][$currentRawMaterialId]['Month'][$monthId]['price']=$productTotalValuePrice;
            $rawUtility['RawMaterial'][$currentRawMaterialId]['Month'][$monthId]['gain']=$productTotalValuePrice-$productTotalValueCost;
            
            $rawMonthUtility[$monthId]['quantity']+=$productTotalQuantity;        
            $rawMonthUtility[$monthId]['cost']+=$productTotalValueCost;
            $rawMonthUtility[$monthId]['price']+=$productTotalValuePrice;
            $rawMonthUtility[$monthId]['gain']+=$productTotalValuePrice-$productTotalValueCost;
            
            if (!array_key_exists($currentRawMaterialId,$warehouseRawUtility['RawMaterial'])){
              $warehouseRawUtility['RawMaterial'][$currentRawMaterialId]=[
                'Month'=>[],
              ];
            }
            if (!array_key_exists($monthId,$warehouseRawUtility['RawMaterial'][$currentRawMaterialId]['Month'])){
              $warehouseRawUtility['RawMaterial'][$currentRawMaterialId]['Month'][$monthId]=$dataArray;
            }
            $warehouseRawUtility['RawMaterial'][$currentRawMaterialId]['Month'][$monthId]['quantity']+=$productTotalQuantity;        
            $warehouseRawUtility['RawMaterial'][$currentRawMaterialId]['Month'][$monthId]['cost']+=$productTotalValueCost;
            $warehouseRawUtility['RawMaterial'][$currentRawMaterialId]['Month'][$monthId]['price']+=$productTotalValuePrice;
            $warehouseRawUtility['RawMaterial'][$currentRawMaterialId]['Month'][$monthId]['gain']+=$productTotalValuePrice-$productTotalValueCost;
          }
        }    
        //pr($rawUtility);
        //pr($warehouseRawUtility);
        
        
        
        
        
        
        foreach ($productNatures as $currentProductNatureId =>$productNatureName){
          if ($currentProductNatureId >0){
            foreach ($productNatureProducts[$currentProductNatureId]['Product'] as $productId=>$productName){
              $productUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Product']['name']=$productName;
              if (!array_key_exists($productId,$warehouseProductUtility)){
                $warehouseProductUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]=[
                  'Product'=>['name'=>$productName,],
                  'Month'=>[],
                ];
              }  
              foreach ($monthArray as $monthId=>$monthData){
                //date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
                $monthStartDate=date('Y-m-d',strtotime($monthData['sale_start_date']));
                $monthEndDatePlusOne=date('Y-m-d',strtotime($monthData['sale_end_date_plus_one']));
              
                $warehouseOrderIds=$this->Order->find('list',[
                  'fields'=>['Order.id'],
                  'conditions'=>[
                    'Order.warehouse_id'=>$currentWarehouseId,
                    'Order.order_date >='=>$monthStartDate,
                    'Order.order_date <'=>$monthEndDatePlusOne,
                    
                    'Order.stock_movement_type_id'=> MOVEMENT_SALE,
                    'Order.bool_annulled'=> false,
                  ],
                ]);
                $productStockMovements=[];
                if(!empty($warehouseOrderIds)){
                  $conditions=[
                    'StockMovement.order_id'=>$warehouseOrderIds,
                    'StockMovement.bool_input'=>false,
                    'StockMovement.bool_reclassification'=>false,
                    'StockMovement.bool_transfer'=>false,
                    'StockMovement.product_id'=>$productId,
                    'StockMovement.product_quantity >'=>0,
                  ];
                  if ($currentProductNatureId == PRODUCT_NATURE_PRODUCED){
                    $conditions['StockMovement.production_result_code_id']=PRODUCTION_RESULT_CODE_A;
                  }
                  $productStockMovements=$this->StockMovement->find('all',[
                    'fields'=>['StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.product_total_price'],
                    'conditions'=>$conditions,
                    'contain'=>[
                      'StockItem'=>[
                        'fields'=>['StockItem.product_unit_price'],
                      ],
                    ],
                  ]);
                }
                $productTotalQuantity=0;
                $productTotalValueCost=0;
                $productTotalValuePrice=0;
                //pr($conditions);
                if (!empty($productStockMovements)){
                  foreach ($productStockMovements as $stockMovement){
                    $productTotalQuantity+=$stockMovement['StockMovement']['product_quantity'];
                    $productTotalValueCost+=$stockMovement['StockMovement']['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                    $productTotalValuePrice+=$stockMovement['StockMovement']['product_total_price'];
                  }  
                
                }
                $productUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'][$monthId]['quantity']=$productTotalQuantity;        
                $productUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'][$monthId]['cost']=$productTotalValueCost;
                $productUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'][$monthId]['price']=$productTotalValuePrice;
                $productUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'][$monthId]['gain']=$productTotalValuePrice-$productTotalValueCost;
                
                if (!array_key_exists($currentProductNatureId,$warehouseProductUtility['ProductNature'])){
                  $warehouseProductUtility['ProductNature'][$currentProductNatureId]=[
                    'Product'=>[],
                    //'Total'=>$dataArray,
                  ];
                }
                if (!array_key_exists($productId,$warehouseProductUtility['ProductNature'][$currentProductNatureId]['Product'])){
                  $warehouseProductUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]=[
                    'Month'=>[],
                    //'Total'=>$dataArray,
                  ];
                }
                if (!array_key_exists($monthId,$warehouseProductUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'])){
                  $warehouseProductUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'][$monthId]=$dataArray;
                }
                $warehouseProductUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'][$monthId]['quantity']+=$productTotalQuantity;        
                $warehouseProductUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'][$monthId]['cost']+=$productTotalValueCost;
                $warehouseProductUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'][$monthId]['price']+=$productTotalValuePrice;
                $warehouseProductUtility['ProductNature'][$currentProductNatureId]['Product'][$productId]['Month'][$monthId]['gain']+=$productTotalValuePrice-$productTotalValueCost;
              }
            
              
              //$warehouseProductUtility[$productId]['Total']['quantity']+=$periodTotal[$currentProductNatureId]['quantity'];
              //$warehouseProductUtility[$productId]['Total']['cost']+=$periodTotal[$currentProductNatureId]['cost'];
              //$warehouseProductUtility[$productId]['Total']['price']+=$periodTotal[$currentProductNatureId]['price'];
              //$warehouseProductUtility[$productId]['Total']['gain']+=$periodTotal[$currentProductNatureId]['gain'];
            }    
          }  
        }
        //pr($productUtility);
        //pr($warehouseProductUtility);
        
        
        
        foreach ($clients as $clientId=>$clientName){
          $clientUtility[$clientId]['Client']['name']=$clientName;
          
          $periodTotal=[];
          foreach ($productNatures as $currentProductNatureId =>$productNatureName){
            $periodTotal[$currentProductNatureId]=$dataArray;
          }
          
          if (!array_key_exists($clientId,$warehouseClientUtility)){
            $warehouseClientUtility[$clientId]=[
              'Client'=>['name'=>$clientName,],
              'Month'=>[],
              'Total'=>$periodTotal,
            ];
          }
          
          foreach ($monthArray as $monthId=>$monthData){
            //date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
            $monthStartDate=date('Y-m-d',strtotime($monthData['sale_start_date']));
            $monthEndDatePlusOne=date('Y-m-d',strtotime($monthData['sale_end_date_plus_one']));
            
            $clientOrderIds=$this->Order->find('list',[
              'fields'=>['Order.id'],
              'conditions'=>[
                'Order.warehouse_id'=>$currentWarehouseId,
                'Order.third_party_id'=>$clientId,
                'Order.order_date >='=>$monthStartDate,
                'Order.order_date <'=>$monthEndDatePlusOne,
                
                'Order.stock_movement_type_id'=> MOVEMENT_SALE,
                'Order.bool_annulled'=> false,
              ],
            ]);
            
            foreach ($productNatures as $currentProductNatureId=>$productNatureName){
              if(!empty($clientOrderIds)){
                //if ($currentProductNatureId === PRODUCT_NATURE_PRODUCED){
                //  $clientCount++;
                //}
                $conditions=[
                  'StockMovement.order_id'=>$clientOrderIds,
                  'StockMovement.bool_input'=>false,
                  'StockMovement.bool_reclassification'=>false,
                  'StockMovement.bool_transfer'=>false,
                  'StockMovement.product_id'=>$productNatureProductIds[$currentProductNatureId],
                  'StockMovement.product_quantity >'=>0,
                  //'Product.product_type_id'=>PRODUCT_TYPE_BOTTLE,
                ];
                if ($currentProductNatureId == PRODUCT_NATURE_PRODUCED){
                  $conditions['StockMovement.production_result_code_id = ']=PRODUCTION_RESULT_CODE_A;
                }
                if ($currentProductNatureId == -1){
                  $productNatureStockMovements=$this->StockMovement->find('all',[
                    'fields'=>['StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.service_total_cost','StockMovement.product_total_price'],
                    'conditions'=>$conditions,
                    'contain'=>[
                      'StockItem'=>[
                        'fields'=>['StockItem.product_unit_price'],
                      ],
                    ],
                  ]);  
                }
                else {
                  $productNatureStockMovements=$this->StockMovement->find('all',[
                    'fields'=>['StockMovement.stockitem_id','StockMovement.product_quantity','StockMovement.product_total_price'],
                    'conditions'=>$conditions,
                    'contain'=>[
                      'StockItem'=>[
                        'fields'=>['StockItem.product_unit_price'],
                      ],
                    ],
                  ]);
                }
                $productNatureTotalQuantity=0;
                $productNatureTotalValueCost=0;
                $productNatureTotalValuePrice=0;
                //pr($conditions);
                if (!empty($productNatureStockMovements)){
                  //echo "jackpot<br/>";
                  //pr($productNatureStockMovements);
                  if ($currentProductNatureId === -1){
                    foreach ($productNatureStockMovements as $stockMovement){
                      $productNatureTotalQuantity+=$stockMovement['StockMovement']['product_quantity'];
                      $productNatureTotalValueCost+=$stockMovement['StockMovement']['service_total_cost'];
                      $productNatureTotalValuePrice+=$stockMovement['StockMovement']['product_total_price'];
                    }  
                  }
                  else {
                    foreach ($productNatureStockMovements as $stockMovement){
                      $productNatureTotalQuantity+=$stockMovement['StockMovement']['product_quantity'];
                      $productNatureTotalValueCost+=$stockMovement['StockMovement']['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
                      $productNatureTotalValuePrice+=$stockMovement['StockMovement']['product_total_price'];
                    }  
                  }
                }
                $clientUtility[$clientId]['Month'][$monthId]['ProductNature'][$currentProductNatureId]['quantity']=$productNatureTotalQuantity;        
                $clientUtility[$clientId]['Month'][$monthId]['ProductNature'][$currentProductNatureId]['cost']=$productNatureTotalValueCost;
                $clientUtility[$clientId]['Month'][$monthId]['ProductNature'][$currentProductNatureId]['price']=$productNatureTotalValuePrice;
                $clientUtility[$clientId]['Month'][$monthId]['ProductNature'][$currentProductNatureId]['gain']=$productNatureTotalValuePrice-$productNatureTotalValueCost;
                
                if (!array_key_exists($clientId,$warehouseClientUtility)){
                  $warehouseClientUtility[$clientId]=[
                    'Month'=>[],
                    'Total'=>$dataArray,
                  ];
                }
                if (!array_key_exists($monthId,$warehouseClientUtility[$clientId]['Month'])){
                  $warehouseClientUtility[$clientId]['Month'][$monthId]=[
                    'ProductNature'=>[],
                  ];
                }
                if (!array_key_exists($currentProductNatureId,$warehouseClientUtility[$clientId]['Month'][$monthId]['ProductNature'])){
                  $warehouseClientUtility[$clientId]['Month'][$monthId]['ProductNature'][$currentProductNatureId]=$dataArray;
                }
                $warehouseClientUtility[$clientId]['Month'][$monthId]['ProductNature'][$currentProductNatureId]['quantity']+=$productNatureTotalQuantity;        
                $warehouseClientUtility[$clientId]['Month'][$monthId]['ProductNature'][$currentProductNatureId]['cost']+=$productNatureTotalValueCost;
                $warehouseClientUtility[$clientId]['Month'][$monthId]['ProductNature'][$currentProductNatureId]['price']+=$productNatureTotalValuePrice;
                $warehouseClientUtility[$clientId]['Month'][$monthId]['ProductNature'][$currentProductNatureId]['gain']+=$productNatureTotalValuePrice-$productNatureTotalValueCost;
                //echo 'period total before adding <br/>';
                //pr($periodTotal);  
                $periodTotal[$currentProductNatureId]['quantity']+=$productNatureTotalQuantity;
                $periodTotal[$currentProductNatureId]['cost']+=$productNatureTotalValueCost;
                $periodTotal[$currentProductNatureId]['price']+=$productNatureTotalValuePrice;
                $periodTotal[$currentProductNatureId]['gain']+=$productNatureTotalValuePrice-$productNatureTotalValueCost;
                //echo 'period total after adding <br/>';
                //pr($periodTotal);                  
                $warehousePeriodTotal[$currentProductNatureId]['quantity']+=$productNatureTotalQuantity;
                $warehousePeriodTotal[$currentProductNatureId]['cost']+=$productNatureTotalValueCost;
                $warehousePeriodTotal[$currentProductNatureId]['price']+=$productNatureTotalValuePrice;
                $warehousePeriodTotal[$currentProductNatureId]['gain']+=$productNatureTotalValuePrice-$productNatureTotalValueCost;
                
                $monthUtility[$monthId]['ProductNature'][$currentProductNatureId]['quantity']+=$productNatureTotalQuantity;        
                $monthUtility[$monthId]['ProductNature'][$currentProductNatureId]['cost']+=$productNatureTotalValueCost;
                $monthUtility[$monthId]['ProductNature'][$currentProductNatureId]['price']+=$productNatureTotalValuePrice;
                $monthUtility[$monthId]['ProductNature'][$currentProductNatureId]['gain']+=$productNatureTotalValuePrice-$productNatureTotalValueCost;
                
                $warehouseMonthUtility[$monthId]['ProductNature'][$currentProductNatureId]['quantity']+=$productNatureTotalQuantity;        
                $warehouseMonthUtility[$monthId]['ProductNature'][$currentProductNatureId]['cost']+=$productNatureTotalValueCost;
                $warehouseMonthUtility[$monthId]['ProductNature'][$currentProductNatureId]['price']+=$productNatureTotalValuePrice;
                $warehouseMonthUtility[$monthId]['ProductNature'][$currentProductNatureId]['gain']+=$productNatureTotalValuePrice-$productNatureTotalValueCost;  
                
                $warehouseClientUtility[$clientId]['Total'][$currentProductNatureId]['quantity']+=$periodTotal[$currentProductNatureId]['quantity'];
                $warehouseClientUtility[$clientId]['Total'][$currentProductNatureId]['cost']+=$periodTotal[$currentProductNatureId]['cost'];
                $warehouseClientUtility[$clientId]['Total'][$currentProductNatureId]['price']+=$periodTotal[$currentProductNatureId]['price'];
                $warehouseClientUtility[$clientId]['Total'][$currentProductNatureId]['gain']+=$periodTotal[$currentProductNatureId]['gain'];
                
                $allWarehousesPeriodTotal[$currentProductNatureId]['quantity']+=$productNatureTotalQuantity;
                $allWarehousesPeriodTotal[$currentProductNatureId]['cost']+=$productNatureTotalValueCost;
                $allWarehousesPeriodTotal[$currentProductNatureId]['price']+=$productNatureTotalValuePrice;
                $allWarehousesPeriodTotal[$currentProductNatureId]['gain']+=$productNatureTotalValuePrice-$productNatureTotalValueCost;     
              }
            }
            
            //echo 'period total at the end of month loop for client '.$clientName.'<br/>';
            //pr($periodTotal);  
          }
          $clientUtility[$clientId]['Total']=$periodTotal;
          //pr($periodTotal);
          //pr($clientUtility[$clientId]);
          //pr($warehouseClientUtility);
          
        }    
        
        $warehouseArray[$currentWarehouseId]['RawUtility']=$rawUtility;
        $warehouseArray[$currentWarehouseId]['RawMonthUtility']=$rawMonthUtility;
        $warehouseArray[$currentWarehouseId]['ProductUtility']=$productUtility;
        $warehouseArray[$currentWarehouseId]['ClientUtility']=$clientUtility;
        $warehouseArray[$currentWarehouseId]['MonthUtility']=$monthUtility;
        $warehouseArray[$currentWarehouseId]['Total']=$warehousePeriodTotal;
      }
    }
    //pr($warehouseClientUtility);
    
    //echo 'count clients is '.$clientCount.'<br/>';
    if (count($warehouseArray)>1){
      $warehouseArray[0]['ProductUtility']=$warehouseProductUtility;
      $warehouseArray[0]['ClientUtility']=$warehouseClientUtility;
      $warehouseArray[0]['MonthUtility']=$warehouseMonthUtility;
      $warehouseArray[0]['Total']=$allWarehousesPeriodTotal;
    }
    //pr($warehouseArray);
    $this->set(compact('warehouseArray'));
    
    $salesOtherProductTypes=[];
    $grandTotals=[];
    $grandTotals['Price']=[
      'all'=>0,
      'cash'=>0,
      'credit'=>0,
      'produced'=>0,
      'cap'=>0,
      'service'=>0,
      'consumible'=>0,
      'other'=>[],
    ];
    $grandTotals['Cost']=[
      'all'=>0,
      'cash'=>0,
      'credit'=>0,
      'produced'=>0,
      'cap'=>0,
      'service'=>0,
      'consumible'=>0,
      'other'=>[],
    ];
    $grandTotalCredit=0;
    $grandTotalProduced=0;  
    for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
      $period=$monthArray[$salePeriod]['period'];
      $start=$monthArray[$salePeriod]['start'];
      $month=$monthArray[$salePeriod]['month'];
      $nextmonth=($month==12)?1:($month+1);
      $year=$monthArray[$salePeriod]['year'];
      $nextyear=($month==12)?($year+1):$year;
      $saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
      $saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));
			
      $ordersForPeriod=$this->Order->find('all',[
        'fields'=>['Order.id'],
        'conditions' => [
          'Order.stock_movement_type_id'=> MOVEMENT_SALE,
          'Order.order_date >='=> $saleStartDate,
          'Order.order_date <'=> $saleEndDate,
          'Order.bool_annulled'=> false,
        ],
        'contain'=>[
          'StockMovement'=>[
            'fields'=>['StockMovement.id'],
            'conditions'=>[
              'StockMovement.product_quantity >'=>0,
              'production_result_code_id < '=>PRODUCTION_RESULT_CODE_B,
              
              'StockMovement.bool_input'=>false,
              'StockMovement.bool_reclassification'=>false,
              'StockMovement.bool_transfer'=>false,
            ],
          ],
        ],
        'recursive'=>-1,
      ]);
      $orderIds=[];
      // EXCLUDE REMISSIONS
      foreach ($ordersForPeriod as $order){
        if (!empty($order['StockMovement'])){
          $orderIds[]=$order['Order']['id'];
        }
      }
      //pr($orderIds);
      $salesForPeriod=$this->Order->find('all',[
        'fields'=>[],
        'contain'=>[
          'StockMovement'=>[
            'fields'=>['StockMovement.product_quantity','StockMovement.production_result_code_id',
              'StockMovement.product_total_price',
              'StockMovement.service_unit_cost','StockMovement.service_total_cost',
            ],
            'conditions'=>[
              'StockMovement.product_quantity >'=>0,
              
              'production_result_code_id < '=>PRODUCTION_RESULT_CODE_B,
              
              'StockMovement.bool_input'=>false,
              'StockMovement.bool_reclassification'=>false,
              'StockMovement.bool_transfer'=>false,
            ],
            'StockItem'=>[
              'fields'=>['product_unit_price'],
            ],
            'Product'=>[
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
          ],
          'CashReceipt'=>[
            'fields'=>[
              'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
              'CashReceipt.currency_id','CashReceipt.amount',
            ],
          ],
        ],
        'conditions' => [
          'Order.id'=>$orderIds,
        ],
        //'order'=>'order_date DESC,order_code DESC',
      ]);
        
     
      //pr($salesForPeriod);
      $totalQuantityOthers=[];
      $sales=[];
      $rowCounter=0;
      // loop to get extended information
            
      foreach ($salesForPeriod as $sale){
        /*
        $quantityProduced=0;
        $quantityCap=0;
        $quantityService=0;
        $quantityConsumible=0;
      */
        $quantityOthers=[];
        
        $priceProduced=0;
        $priceCap=0;
        $priceService=0;
        $priceConsumible=0;
        $priceOthers=[];
        
        $costProduced=0;
        $costCap=0;
        $costService=0;
        $costConsumible=0;
        $costOthers=[];
        
        $totalCost=0;
        //MOVED UP A LEVEL
        //$salesOtherProductTypes=[];  
        
        foreach ($sale['StockMovement'] as $stockMovement){
          //pr ($stockMovement);
          $qualifiedStockMovement=false;
          if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
            //$quantityProduced+=$stockMovement['product_quantity'];

            $priceProduced+=$stockMovement['product_total_price'];
            $costProduced+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
            
            $qualifiedStockMovement=true;
          }
          elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
            if ($stockMovement['Product']['product_type_id'] == PRODUCT_TYPE_CAP){
              //$quantityCap+=$stockMovement['product_quantity'];
              
              $priceCap+=$stockMovement['product_total_price'];
              $costCap+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
              
              $qualifiedStockMovement=true;
            }
            elseif ($stockMovement['Product']['product_type_id'] == PRODUCT_TYPE_SERVICE){
              //$quantityService+=$stockMovement['product_quantity'];
              
              $priceService+=$stockMovement['product_total_price'];
              $costService+=$stockMovement['product_quantity']*$stockMovement['service_unit_cost'];
              
              $qualifiedStockMovement=true;
            }
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
              $quantityOthers[$productTypeId]+=$stockMovement['product_quantity'];
              $totalQuantityOthers[$productTypeId]+=$stockMovement['product_quantity'];

              $priceOthers[$productTypeId]+=$stockMovement['product_total_price'];
              $costOthers[$productTypeId]+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
              
              $qualifiedStockMovement=true;
            }
          }
          elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_CONSUMIBLE) {
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
        //if ((($quantityProduced+$quantityCap+$quantityService + $summedPriceOthers)>0)||(!empty($sale['Invoice'])&&($sale['Invoice'][0]['bool_annulled']||empty($sale['StockMovement'])))||(empty($sale['Invoice'])&&empty($sale['CashReceipt'])&&empty($sale['StockMovement']))){
          $sales[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
          $sales[$rowCounter]['Order']['price_produced']=$priceProduced;
          $sales[$rowCounter]['Order']['price_cap']=$priceCap;
          $sales[$rowCounter]['Order']['price_service']=$priceService;
          $sales[$rowCounter]['Order']['price_consumible']=$priceConsumible;
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
          $sales[$rowCounter]['Order']['cost_consumible']=$costConsumible;
          if (!empty($costOthers)){
            foreach ($costOthers as $costOtherProductTypeId => $costOther){
              $sales[$rowCounter]['Order']['cost_others'][$costOtherProductTypeId]=$costOther;  
            }
          }
          else {
            $sales[$rowCounter]['Order']['cost_others']=[];
          }
          /*
          $sales[$rowCounter]['Order']['quantity_cap']=$quantityCap;
          $sales[$rowCounter]['Order']['quantity_produced']=$quantityProduced;
          $sales[$rowCounter]['Order']['quantity_service']=$quantityService;
          $sales[$rowCounter]['Order']['quantity_consumible']=$quantityConsumible;
          if (!empty($quantityOthers)){
            foreach ($quantityOthers as $quantityOtherProductTypeId => $quantityOther){
              $sales[$rowCounter]['Order']['quantity_others'][$quantityOtherProductTypeId]=$quantityOther;  
            }
          }
          else {
            $sales[$rowCounter]['Order']['quantity_others']=[];
          }
          */
          if (!empty($totalQuantityOthers)){
          foreach ($totalQuantityOthers as $productTypeId => $totalQuantityOther){
            $sales[$rowCounter]['Order']['total_quantity_others'][$productTypeId]=$totalQuantityOther;  
            if (!array_key_exists($productTypeId,$salesOtherProductTypes)){
              $salesOtherProductTypes[$productTypeId]=$productTypes[$productTypeId];
            }
          }
        }
        else {
          $sales[$rowCounter]['Order']['total_quantity_others']=[];
        }
          
          if (empty($sale['Invoice'])){
            $sales[$rowCounter]['Invoice']['bool_credit']=true;
          }
          else {
            $sales[$rowCounter]['Invoice']['bool_credit']=$sale['Invoice'][0]['bool_credit'];
          }
          $rowCounter++;
        //}
      }
      //pr($salesOtherProductTypes);
      
      $totalPriceCash=0;
      $totalPriceCredit=0;
      $totalCostCash=0;
      $totalCostCredit=0;
      
      $totalPriceProduced=0;
      $totalPriceCap=0;
      $totalPriceService=0;
      $totalPriceConsumible=0;
      $totalPriceOthers=[];
      
      $totalCostProduced=0;
      $totalCostCap=0;
      $totalCostService=0;
      $totalCostConsumible=0;
      $totalCostOthers=[];
      
      $totalProduced=0;
      $totalCap=0;
      $totalService=0;
      $totalConsumible=0;
      $totalOthers=[];
      
      foreach ($sales as $sale){
        //pr($sale);
        
        if ($sale['Invoice']['bool_credit']){
          $totalPriceCredit+=$sale['Order']['total_price'];
          $totalCostCredit+=$sale['Order']['total_cost'];
        }
        else {
          $totalPriceCash+=$sale['Order']['total_price'];
          $totalCostCash+=$sale['Order']['total_cost'];
        }
        
        $totalPriceProduced+=$sale['Order']['price_produced'];
        $totalPriceCap+=$sale['Order']['price_cap'];
        $totalPriceService+=$sale['Order']['price_service'];
        $totalPriceConsumible+=$sale['Order']['price_consumible'];
        
        if (!empty($sale['Order']['price_others'])){
          foreach ($sale['Order']['price_others'] as $productTypeId=>$priceOther){
            if (!array_key_exists($productTypeId,$totalPriceOthers)){
              $totalPriceOthers[$productTypeId]=0;
            }
            $totalPriceOthers[$productTypeId]+=$priceOther;
          }
          //pr($totalPriceOthers);
        }
        
        $totalCostProduced+=$sale['Order']['cost_produced'];
        $totalCostCap+=$sale['Order']['cost_cap'];
        $totalCostService+=$sale['Order']['cost_service'];
        $totalCostConsumible+=$sale['Order']['cost_consumible'];
        
        if (!empty($sale['Order']['cost_others'])){
          foreach ($sale['Order']['cost_others'] as $productTypeId=>$costOther){
            if (!array_key_exists($productTypeId,$totalCostOthers)){
              $totalCostOthers[$productTypeId]=0;
            }
            $totalCostOthers[$productTypeId]+=$costOther;
          }
          //pr($totalCostOthers);
        }
        /*
        $totalpriceproducts+=$sale['Order']['total_price'];
        $totalcost+=$sale['Order']['total_cost'];
        */
      }
      
      $monthArray[$salePeriod]['totalPrice']['all']=$totalPriceCash+$totalPriceCredit;
      $monthArray[$salePeriod]['totalPrice']['cash']=$totalPriceCash;
      $monthArray[$salePeriod]['totalPrice']['credit']=$totalPriceCredit;
      $monthArray[$salePeriod]['totalPrice']['produced']=$totalPriceProduced;
      $monthArray[$salePeriod]['totalPrice']['cap']=$totalPriceCap;
      $monthArray[$salePeriod]['totalPrice']['service']=$totalPriceService;
      $monthArray[$salePeriod]['totalPrice']['consumible']=$totalPriceConsumible;
      $monthArray[$salePeriod]['totalPrice']['other']=$totalPriceOthers;
      
      $monthArray[$salePeriod]['totalCost']['all']=$totalCostCash+$totalCostCredit;
      $monthArray[$salePeriod]['totalCost']['cash']=$totalCostCash;
      $monthArray[$salePeriod]['totalCost']['credit']=$totalCostCredit;
      $monthArray[$salePeriod]['totalCost']['produced']=$totalCostProduced;
      $monthArray[$salePeriod]['totalCost']['cap']=$totalCostCap;
      $monthArray[$salePeriod]['totalCost']['service']=$totalCostService;
      $monthArray[$salePeriod]['totalCost']['consumible']=$totalCostConsumible;
      $monthArray[$salePeriod]['totalCost']['other']=$totalCostOthers;
      
      $grandTotals['Price']['all']+=$totalPriceCash+$totalPriceCredit;
      $grandTotals['Price']['cash']+=$totalPriceCash;
      $grandTotals['Price']['credit']+=$totalPriceCredit;
      $grandTotals['Price']['produced']+=$totalPriceProduced;
      $grandTotals['Price']['cap']+=$totalPriceCap;
      $grandTotals['Price']['service']+=$totalPriceService;
      $grandTotals['Price']['consumible']+=$totalPriceConsumible;
      foreach ($totalPriceOthers as $productTypeId=>$price){
        if (!array_key_exists($productTypeId,$grandTotals['Price']['other'])){
          $grandTotals['Price']['other'][$productTypeId]=0;
        }
        $grandTotals['Price']['other'][$productTypeId]+=$price;
      }
      //$grandTotals['Price']['other'+=[],
      
      $grandTotals['Cost']['all']+=$totalCostCash+$totalCostCredit;
      $grandTotals['Cost']['cash']+=$totalCostCash;
      $grandTotals['Cost']['credit']+=$totalCostCredit;
      $grandTotals['Cost']['produced']+=$totalCostProduced;
      $grandTotals['Cost']['cap']+=$totalCostCap;
      $grandTotals['Cost']['service']+=$totalCostService;
      $grandTotals['Cost']['consumible']+=$totalCostConsumible;
      foreach ($totalCostOthers as $productTypeId=>$cost){
        if (!array_key_exists($productTypeId,$grandTotals['Cost']['other'])){
          $grandTotals['Cost']['other'][$productTypeId]=0;
        }
        $grandTotals['Cost']['other'][$productTypeId]+=$cost;
      }
      //$grandTotals['Cost']['other'+=[],      
    }
    //pr($monthArray); 
    //pr($grandTotals);
    $this->set(compact('monthArray'));
    $this->set(compact('grandTotals'));
    if (!empty($salesOtherProductTypes)){
      asort($salesOtherProductTypes);
    } 
    $this->set(compact('salesOtherProductTypes'));
    
    $shortWarehouses=$this->Warehouse->getShortWarehouseList();
    $this->set(compact('shortWarehouses'));
    
  }
  
	public function guardarUtilidadAnual() {
		$exportData=$_SESSION['utilidadAnual'];
		$this->set(compact('exportData'));
	}
	
	public function guardarReporteProductoMateriaPrima() {
		$exportData=$_SESSION['productReport'];
		$this->set(compact('exportData'));
	}

	public function verReporteProducto($id) {
    $this->loadModel('Product');
    if (!$this->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
    
		$this->loadModel('Order');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('StockItemLog');
    $this->loadModel('StockMovement');
    
    $this->loadModel('PlantProductionResultCode');
    $this->loadModel('PlantProductType');
		
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
    $productTypeId=$this->Product->getProductTypeId($id);
    $plantsForProductType=$this->PlantProductType->getPlantsForProductType($productTypeId);
    //pr($plantsForProductType);
    $plantId=array_keys($plantsForProductType)[0];
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $plantId=$this->request->data['Report']['plant_id'];
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
		
    if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    elseif (count($plants) > 1 && $plantId == 0){
      if (!empty($_SESSION['plantId'])){
        $plantId = $_SESSION['plantId'];
      }
    }
    $_SESSION['plantId']=$plantId;
    $this->set(compact('plantId'));
    
		$productData=$this->Product->find('first',[
      'contain'=>[
        'ProductType'=>['fields'=>'product_category_id'],
      ],
      'conditions' => ['Product.id'=> $id],
    ]);
		
		$this->Product->recursive=0;
		$allFinishedProducts=$this->Product->find('all',[
      'fields'=>['id','name'],
			'conditions'=>[
        'ProductType.product_category_id'=>CATEGORY_PRODUCED
      ],
      'order'=>'name ASC'
    ]);
		$productionResultCodes=$this->PlantProductionResultCode->getProductionResultCodesForPlant($plantId);

		$initialStock=0;
		
		$this->StockItem->recursive=0;
		$initialStockItems=$this->StockItem->find('all',[
			'fields'=>'StockItem.id',
			'conditions'=>[
				'product_id'=>$id,
        // CONDITIONS ADDED 20180314
        'StockItem.stockitem_creation_date <'=> $startDate,        
        'StockItem.stockitem_depletion_date >='=> $startDate,
			],
		]);
		//pr($initialStockItems);
		$this->StockItemLog->recursive=0;
		foreach ($initialStockItems as $initialStockItem){
			$initialStockItemLogs=$this->StockItemLog->find('first',[
				'conditions'=>[
					'StockItemLog.stockitem_id'=>$initialStockItem['StockItem']['id'],
					'StockItemLog.stockitem_date <'=>$startDate,
				],
				'order'=>'StockItemLog.id DESC'
			]);
			
			if (!empty($initialStockItemLogs)){
				//pr ($initialStockItemLogs);
				$initialStock+=$initialStockItemLogs['StockItemLog']['product_quantity'];
			}
		}
		
		$reclassified=0;
		
		$this->StockMovement->virtualFields['total_reclassified']=0;
		$reclassificationStockMovements=$this->StockMovement->find('first',array(
			'fields'=>array('SUM(StockMovement.product_quantity) AS StockMovement__total_reclassified'),
			'conditions'=>array(
				'StockMovement.bool_reclassification'=>true,
				'StockMovement.bool_input'=>true,
				'StockMovement.product_id'=>$id,
				'StockMovement.movement_date >='=>$startDate,
				'StockMovement.movement_date <'=>$endDatePlusOne,
			),
		));
		$rawReclassified=$reclassificationStockMovements['StockMovement']['total_reclassified'];
			
		$finishedReclassified=[];
		foreach ($allFinishedProducts as $finishedProduct){
			foreach ($productionResultCodes as $productionResultCodeId=>$productionResultCodeCode){								
				$reclassificationIncomingStockMovements=$this->StockMovement->find('first',[
					'fields'=>['SUM(StockMovement.product_quantity) AS StockMovement__total_reclassified'],
					'conditions'=>[
						'StockMovement.bool_reclassification'=>true,
						'StockMovement.bool_input'=>true,
						'StockMovement.product_id'=>$finishedProduct['Product']['id'],
						'StockMovement.production_result_code_id'=>$productionResultCodeId,
						'StockMovement.movement_date >='=>$startDate,
						'StockMovement.movement_date <'=>$endDatePlusOne,
						'StockItem.raw_material_id'=>$id,
					],
				]);
				//pr($reclassificationIncomingStockMovements);
				
				$reclassificationOutgoingStockMovements=$this->StockMovement->find('first',[
					'fields'=>['SUM(StockMovement.product_quantity) AS StockMovement__total_reclassified'],
					'conditions'=>[
						'StockMovement.bool_reclassification'=>true,
						'StockMovement.bool_input'=>false,
						'StockMovement.product_id'=>$finishedProduct['Product']['id'],
						'StockMovement.production_result_code_id'=>$productionResultCodeId,
						'StockMovement.movement_date >='=>$startDate,
						'StockMovement.movement_date <'=>$endDatePlusOne,
						'StockItem.raw_material_id'=>$id,
					],
				]);
				//pr($reclassificationOutgoingStockMovements);
			
				if (!empty($reclassificationIncomingStockMovements)){
					if (!empty($reclassificationOutgoingStockMovements)){
						$finishedReclassified[]=$reclassificationIncomingStockMovements['StockMovement']['total_reclassified']-$reclassificationOutgoingStockMovements['StockMovement']['total_reclassified'];
					}
					else {
						$finishedReclassified[]=$reclassificationIncomingStockMovements['StockMovement']['total_reclassified'];
					}
				}
				else {
					if (!empty($reclassificationOutgoingStockMovements)){
						$finishedReclassified[]=0-$reclassificationOutgoingStockMovements['StockMovement']['total_reclassified'];
					}
					else {
						$finishedReclassified[]=0;
					}
				}
			}
		}
		//pr($finishedReclassified);
		
		$stockItemsForPeriodWithProductionRuns=$this->ProductionMovement->find('list',[
			'fields'=>['ProductionMovement.stockitem_id'],
			'conditions'=>[
				'ProductionMovement.product_id'=> $id,
				'ProductionMovement.bool_input'=> true,
				'ProductionMovement.movement_date >='=> $startDate,
				'ProductionMovement.movement_date <'=> $endDatePlusOne,
			],
		]);
		
		$stockItemsWithoutProductionRun=$this->StockItem->find('list', [
			'fields'=>['id'],
			'conditions'=>[
				'StockItem.product_id'=> $id,
				'StockItem.stockitem_creation_date >='=> $startDate,
				'StockItem.stockitem_creation_date <'=> $endDatePlusOne,
			],
		]);
		
		$stockItemsForPeriod=array_merge($stockItemsForPeriodWithProductionRuns,$stockItemsWithoutProductionRun);
		
		//pr($stockItemsForPeriod);
		
		$thisProductOrders=$this->Order->find('all',
			array(
				'fields'=>array('id','order_date','order_code'),
				'contain'=>array(
					'ThirdParty'=>array('fields'=>'company_name'),
					'StockMovement'=>array(
						'fields'=>array('id','movement_date','order_id','stockitem_id','product_quantity','product_unit_price','product_total_price'),
						'conditions' => array(
							'StockMovement.product_id'=> $id,
							'StockMovement.bool_input'=> true,
							'StockMovement.stockitem_id'=> $stockItemsForPeriod,
						),
						'order'=>'movement_date ASC',
						'Product'=>array(
							'fields'=>array('id','packaging_unit'),	
							'ProductType'=>array('fields'=>'product_category_id'),
						),
						
					)
				),
				'order'=>'order_date ASC'
			)
		);
		$productOrderCount=0;
		
		foreach ($thisProductOrders as $productOrder){
			$purchaseMovementCount=0;
			foreach ($productOrder['StockMovement'] as $purchaseMovement){
				$stockitemid=$purchaseMovement['stockitem_id'];
				$productionMovementsForPurchaseMovement=$this->ProductionMovement->find('all',
					array(
						'fields'=>array('id','stockitem_id','product_id','product_quantity','product_unit_price','production_run_id'),
						'conditions' => array('stockitem_id'=> $stockitemid),	
						'contain'=>array(
							'ProductionRun'=>array(
								'fields'=>array('id','production_run_code','production_run_date'),
								'ProductionMovement'=>array(
									'fields'=>array('id','stockitem_id','product_id','product_quantity','product_unit_price','production_run_id'),
									'conditions'=>array(
										'bool_input'=>false,
										'ProductionMovement.movement_date >='=> $startDate,
										'ProductionMovement.movement_date <'=> $endDatePlusOne,
									),
									'StockItem'=>array(
										'fields'=>array('production_result_code_id','product_id')
									)
								)
							)
						)
					)
				);
				
				$productionRunCount=0;
				
				foreach ($productionMovementsForPurchaseMovement as $productionRunMovement){
					$productionRunId=$productionRunMovement['ProductionRun']['id'];
					$produced=[];
					$producedRun=null;
					foreach($productionRunMovement['ProductionRun']['ProductionMovement'] as $movementForRun){
						$producedRun=[];
						
						$productidForMovement=$movementForRun['product_id'];
						$productionresultcodeForMovement=$movementForRun['StockItem']['production_result_code_id'];
						foreach ($allFinishedProducts as $finishedProduct){
							foreach ($productionResultCodes as $productionResultCodeId=>$productionResultCodeCode){								
								if ($productidForMovement==$finishedProduct['Product']['id'] && $productionresultcodeForMovement==$productionResultCodeId){
									$producedRun[]=$movementForRun['product_quantity'];
								}
								else{
									$producedRun[]=0;
								}
							}
						}
									
						foreach (array_keys($producedRun + $produced) as $key) {
							$produced[$key] = (isset($producedRun[$key]) ? $producedRun[$key] : 0) + (isset($produced[$key]) ? $produced[$key] : 0);
						}
					}
					
					array_push($productionMovementsForPurchaseMovement[$productionRunCount]['ProductionRun'],$produced);
					$productionRunCount++;
				}
				array_push($thisProductOrders[$productOrderCount]['StockMovement'][$purchaseMovementCount],$productionMovementsForPurchaseMovement);
				$purchaseMovementCount++;
			}
			$productOrderCount++;
		}
		
		
		$this->set(compact('productData','thisProductOrders','startDate','endDate','endDatePlusOne','allFinishedProducts','productionResultCodes','finalStock','initialStock','rawReclassified','finishedReclassified'));

	}
	
	public function guardarReporteProductos() {
		$exportData=$_SESSION['productsReport'];
		$this->set(compact('exportData'));
	}
	
/******************** CUADRAR LOTES *******************/
	
	public function cuadrarEstadosDeLote(){
		$this->loadModel('StockMovement');
		$this->loadModel('ProductionMovement');
		$this->loadModel('StockItemLog');
		$this->loadModel('ProductType');
		$this->loadModel('ProductCategory');
		$this->loadModel('Product');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $this->loadModel('PlantProductType');
    
    $this->loadModel('Warehouse');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
    $plantId=0;
    
    $startDate = date("Y-m-01");  
    
    $productCategoryId=CATEGORY_RAW;
		$finishedProductId=0;
    
		if ($this->request->is('post')) {
      $plantId=$this->request->data['Report']['plant_id'];
      
      $startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
      
			$productCategoryId=$this->request->data['Report']['product_category_id'];
			$finishedProductId=$this->request->data['Report']['finished_product_id'];
      
		}
		else if ($this->Session->check('productCategoryId')){
			$productCategoryId=$_SESSION['productCategoryId'];
		}
		
    $this->set(compact('startDate'));
    
    $_SESSION['productCategoryId']=$productCategoryId;
		$this->set(compact('productCategoryId'));
    
		$this->set(compact('finishedProductId'));
		
    if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    elseif (count($plants) > 1 && $plantId == 0){
      if (!empty($_SESSION['plantId'])){
        $plantId = $_SESSION['plantId'];
      }
      else {
        $plantId=0;
      }
    }
    $_SESSION['plantId']=$plantId;
    $this->set(compact('plantId'));
    $plantWarehouseIds=$this->Warehouse->getWarehouseIdsForPlantId($plantId);
    //pr($plantWarehouseIds);
   
    $plantProductTypeIds=$this->PlantProductType->getProductTypeIdsForPlant($plantId);
    $productTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>[
        'ProductType.product_category_id'=>$productCategoryId,
        'ProductType.id'=>$plantProductTypeIds,
      ],
    ]);
        
		$productIds=$this->Product->getProductIdsForProductType($productTypeIds);
    $stockItemConditions=[
      'StockItem.product_id'=>$productIds,
      'StockItem.bool_active'=>true,
      'StockItem.stockitem_creation_date >'=>$startDate,
      'StockItem.warehouse_id'=>$plantWarehouseIds,
    ];
    
    if ($productCategoryId == CATEGORY_PRODUCED){
      if (!empty($finishedProductId)){
        $stockItemConditions['Product.id']=$finishedProductId;
        if ($plantId == PLANT_SANDINO){
          $allStockItems=$this->StockItem->find('all',[
            'fields'=>['StockItem.id','StockItem.original_quantity','StockItem.remaining_quantity',],
            'conditions'=>$stockItemConditions,
            'contain'=>[
              'Product'=>[
                'fields'=>['Product.name',],
              ],
              'ProductionResultCode'=>[
                'fields'=>['ProductionResultCode.code',],
              ],
              'RawMaterial'=>[
                'fields'=>['RawMaterial.name',],
              ],
              'Warehouse'=>[
              'fields'=>['Warehouse.name',],
            ],
            ],
            'order'=>'RawMaterial.name, Product.name,ProductionResultCode.code, StockItem.id',
          ]);
        }
        else {
          $allStockItems=$this->StockItem->find('all',[
            'fields'=>['StockItem.id','StockItem.original_quantity', 'StockItem.remaining_quantity'],
            'conditions'=>$stockItemConditions,
            'contain'=>[
              'Product'=>[
                'fields'=>['Product.name',],
              ],
              'ProductionResultCode'=>[
                'fields'=>['ProductionResultCode.code',],
              ],
              'Warehouse'=>[
                'fields'=>['Warehouse.name',],
              ],
            ],
            'order'=>'Product.name, StockItem.id',
          ]);
        }
      }
      if (!empty($allStockItems)){
        for ($i=0;$i<count($allStockItems);$i++){
          $outputProductionMovementTotal=$this->ProductionMovement->find('first',[
            'fields'=>['StockItem.id, SUM(ProductionMovement.product_quantity) AS total_product_quantity'],
            'conditions'=>[
              'ProductionMovement.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
              'bool_input'=>false,
            ],
            'group'=>['StockItem.id'],
          ]);
          $allStockItems[$i]['StockItem']['total_produced_in_production']=(empty($outputProductionMovementTotal[0])?0:$outputProductionMovementTotal[0]['total_product_quantity']);
          
          $reclassificationMovementTotal=$this->StockMovement->find('first',[
            'fields'=>['StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'],
            'conditions'=>[
              'StockMovement.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
              'bool_input'=>true,
              'bool_reclassification'=>true,
            ],
            'group'=>['StockItem.id'],
          ]);
          $allStockItems[$i]['StockItem']['total_produced_in_production']+=(empty($reclassificationMovementTotal[0])?0:$reclassificationMovementTotal[0]['total_product_quantity']);
          
          
          $transferMovementTotal=$this->StockMovement->find('first',[
            'fields'=>['StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'],
            'conditions'=>[
              'StockMovement.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
              'bool_input'=>true,
              'bool_transfer'=>true,
            ],
            'group'=>['StockItem.id'],
          ]);
          $allStockItems[$i]['StockItem']['total_produced_in_production']+=(empty($transferMovementTotal[0])?0:$transferMovementTotal[0]['total_product_quantity']);
          
          
          $adjustmentMovementTotal=$this->StockMovement->find('first',[
            'fields'=>['StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'],
            'conditions'=>[
              'StockMovement.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
              'bool_input'=>true,
              'bool_adjustment'=>true,
            ],
            'group'=>['StockItem.id'],
          ]);
          $allStockItems[$i]['StockItem']['total_produced_in_production']+=(empty($adjustmentMovementTotal[0])?0:$adjustmentMovementTotal[0]['total_product_quantity']);
          
          $outputStockMovementTotal=$this->StockMovement->find('first',[
            'fields'=>['StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'],
            'conditions'=>[
              'StockMovement.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
              'bool_input'=>false,
            ],
            'group'=>['StockItem.id'],
          ]);
          $allStockItems[$i]['StockItem']['total_moved_out']=(empty($outputStockMovementTotal)?0:$outputStockMovementTotal[0]['total_product_quantity']);
          
          
          $lastStockItemLog=$this->StockItemLog->find('first',[
            'fields'=>['StockItemLog.id, StockItemLog.product_quantity'],
            'conditions'=>[
              'StockItemLog.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
            ],
            'order'=>['StockItemLog.id DESC,StockItemLog.stockitem_date DESC'],
          ]);
          if (!empty($lastStockItemLog)){
            $allStockItems[$i]['StockItem']['latest_log_quantity']=$lastStockItemLog['StockItemLog']['product_quantity'];
            $allStockItems[$i]['StockItem']['latest_log_id']=$lastStockItemLog['StockItemLog']['id'];
          }
          else {
            $allStockItems[$i]['StockItem']['latest_log_quantity']=0;
            $allStockItems[$i]['StockItem']['latest_log_id']=0;
          }
        }        
      }
    }
    else {
      $allStockItems=$this->StockItem->find('all',[
        'fields'=>['StockItem.id','StockItem.original_quantity', 'StockItem.remaining_quantity'],
        'conditions'=>$stockItemConditions,
        'contain'=>[
          'Product'=>[
            'fields'=>['Product.name',],
          ],
          'Warehouse'=>[
            'fields'=>['Warehouse.name',],
          ],
        ],
        'order'=>'Product.name, StockItem.id',
      ]);
      for ($i=0;$i<count($allStockItems);$i++){
        if (!empty($allStockItems[$i]['StockItem']['id'])){
          $inputStockMovementTotal=$this->StockMovement->find('first',[
            'fields'=>['StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'],
            'conditions'=>[
              'StockMovement.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
              'bool_input'=>true,
            ],
            'group'=>['StockItem.id'],
          ]);
          $allStockItems[$i]['StockItem']['total_moved_in']=(empty($inputStockMovementTotal)?0:$inputStockMovementTotal[0]['total_product_quantity']);
          $outputStockMovementTotal=$this->StockMovement->find('first',[
            'fields'=>['StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'],
            'conditions'=>[
              'StockMovement.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
              'bool_input'=>false,
            ],
            'group'=>['StockItem.id'],
          ]);
          $allStockItems[$i]['StockItem']['total_moved_out']=(empty($outputStockMovementTotal)?0:$outputStockMovementTotal[0]['total_product_quantity']);
          
          $inputProductionMovementTotal=$this->ProductionMovement->find('first',[
            'fields'=>['StockItem.id, SUM(ProductionMovement.product_quantity) AS total_product_quantity'],
            'conditions'=>[
              'ProductionMovement.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
              'bool_input'=>true,
            ],
            'group'=>['StockItem.id'],
          ]);
          //pr($inputProductionMovementTotal);
          $allStockItems[$i]['StockItem']['total_used_in_production']=(empty($inputProductionMovementTotal)?0:$inputProductionMovementTotal[0]['total_product_quantity']);
          $outputProductionMovementTotal=$this->ProductionMovement->find('first',[
            'fields'=>['StockItem.id, SUM(ProductionMovement.product_quantity) AS total_product_quantity'],
            'conditions'=>[
              'ProductionMovement.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
              'bool_input'=>false,
            ],
            'group'=>['StockItem.id'],
          ]);
          $allStockItems[$i]['StockItem']['total_produced_in_production']=(empty($outputProductionMovementTotal)?0:$outputProductionMovementTotal[0]['total_product_quantity']);
          
          $lastStockItemLog=$this->StockItemLog->find('first',[
            'fields'=>['StockItemLog.id, StockItemLog.product_quantity'],
            'conditions'=>[
              'StockItemLog.stockitem_id'=>$allStockItems[$i]['StockItem']['id'],
            ],
            'order'=>['StockItemLog.id DESC,StockItemLog.stockitem_date DESC'],
          ]);
          
          $allStockItems[$i]['StockItem']['latest_log_quantity']=$lastStockItemLog['StockItemLog']['product_quantity'];
          $allStockItems[$i]['StockItem']['latest_log_id']=$lastStockItemLog['StockItemLog']['id'];
        }
      }
    }
    $this->set(compact('allStockItems'));
    
    $productCategories=$this->ProductCategory->find('list');
		$this->set(compact('productCategories'));
    
    
    $finishedProducts=[];
    
    $finishedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>[
        'ProductType.product_category_id'=>CATEGORY_PRODUCED,
        'ProductType.id'=>$plantProductTypeIds,
      ],
    ]);
    $allFinishedProductIds=$this->Product->getProductIdsForProductType($finishedProductTypeIds);
    
    $stockItemFinishedProductIds=$this->StockItem->find('list',[
      'fields'=>['StockItem.product_id'],
      'conditions'=>[
        'StockItem.product_id'=>$allFinishedProductIds,
        'StockItem.bool_active'=>true,
        'StockItem.stockitem_creation_date >'=>$startDate,
      ],
    ]);
    $finishedProductIds=array_unique($stockItemFinishedProductIds);
    /*
    foreach ($finishedProductTypeIds as $finishedProductTypeId){
      foreach ($plantWarehouseIds as $plantWarehouseId){
        $finishedProductsInventory=$this->StockItem->getInventoryItems($finishedProductTypeId,date('Y-m-d'),$plantWarehouseId,true);  
        foreach ($finishedProductsInventory as $productInventory){
          if ($productInventory[0]['Remaining'] > 0){
            if (!array_key_exists($productInventory['Product']['id'],$finishedProductIds)){
              $finishedProductIds[$productInventory['Product']['id']]=$productInventory['Product']['id'];  
            }
          }
        }
        $finishedProductsInventory=$this->StockItem->getInventoryItems($finishedProductTypeId,$startDate,$plantWarehouseId,false);  
        foreach ($finishedProductsInventory as $productInventory){
          if ($productInventory[0]['Remaining'] > 0){
            if (!array_key_exists($productInventory['Product']['id'],$finishedProductIds)){
              $finishedProductIds[$productInventory['Product']['id']]=$productInventory['Product']['id'];  
            }
          }
        }
      }  
    }
    //pr($finishedProductIds);
    */
    $finishedProducts=$this->Product->find('list',[
      'conditions'=>[
        'Product.id'=>$finishedProductIds,
      ],
      'order'=>'Product.name',
    ]);
    $this->set(compact('finishedProducts'));
  }
	
	public function recreateStockItemLogsForSquaring($id = null) {
		$this->StockItem->id = $id;
		if (!$this->StockItem->exists()) {
			throw new NotFoundException(__('Invalid stock item'));
		}
		$success=$this->recreateStockItemLogs($id);
		if ($success){
			$this->Session->setFlash(__('Los estados de lote han estado recreados para el lote '.$id),'default',array('class' => 'success'));
		}
		else {
			$this->Session->setFlash(__('No se podían recrear los estados de lote para el lote '.$id), 'default',array('class' => 'error-message'));
		}
		return $this->redirect(array('action' => 'cuadrarEstadosDeLote'));
	}
	
	public function recreateAllStockItemLogs() {
		$allStockItems=$this->StockItem->find('list');
		//pr($allStockItems);
		foreach (array_keys($allStockItems) as $stockItemId){
			$success=$this->recreateStockItemLogs($stockItemId);
			if (!$success){
				$this->Session->setFlash(__('No se podían recrear los estados de lote para el lote '.$stockItemId), 'default',['class' => 'error-message']);
				return $this->redirect(['action' => 'cuadrarEstadosDeLote']);
			}
		}
		$this->Session->setFlash(__('Los estados de lote han estado recreados'),'default',['class' => 'success']);
		return $this->redirect(['action' => 'cuadrarEstadosDeLote']);
	}

/******************** RECLASIFICACIONES *******************/
	
	public function resumenReclasificaciones(){
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
		if (!isset($startDate)){
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
		}
		//echo $startDate;
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->loadModel('Product');
		$this->loadModel('StockMovement');
		
		$originReclassificationsCaps=$this->StockMovement->find('all',array(
			'fields'=>array('StockMovement.id','Product.id','Product.name','StockMovement.product_quantity','StockMovement.movement_date','StockMovement.reclassification_code'),
			'conditions'=>array(
				'StockMovement.movement_date >='=>$startDate,
				'StockMovement.movement_date <'=>$endDatePlusOne,
				'StockMovement.bool_reclassification'=>true,
				'Product.product_type_id'=>PRODUCT_TYPE_CAP,
				'StockMovement.bool_input'=>false,
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
				'StockMovement.bool_input'=>false,
				
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
		
		$reclassificationsBottles=[];
		
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
		
		$reclassificationsPreformas=[];
		
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
		
	}	
	public function resumenTransferenciasProductos(){
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
		if (!isset($startDate)){
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
		}
		//echo $startDate;
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->loadModel('StockMovement');
		
		$originTransfersIngroup=$this->StockMovement->find('all',array(
			'fields'=>array('StockMovement.id','Product.id','Product.name','StockMovement.product_quantity','StockMovement.movement_date','StockMovement.transfer_code'),
			'conditions'=>array(
				'StockMovement.movement_date >='=>$startDate,
				'StockMovement.movement_date <'=>$endDatePlusOne,
				'StockMovement.bool_transfer'=>true,
				'Product.product_type_id'=>PRODUCT_TYPE_INJECTION_OUTPUT,
				'StockMovement.bool_input'=>'0',
			),
			'order'=>'DATE(StockMovement.movement_date) DESC, StockMovement.transfer_code DESC',
		));
		
		$transferIngroups=[];
		//debug($originTransfersIngroup[0]['StockMovement']['id']);exit;
		for ($i=0;$i<count($originTransfersIngroup);$i++){
			$destinationMovement=$this->StockMovement->find('first',array(
				'fields'=>array('StockMovement.id','Product.id','Product.name','StockMovement.product_quantity','StockMovement.movement_date','StockMovement.comment'),
				'conditions'=>array(
					'StockMovement.movement_date >='=>$startDate,
					'StockMovement.movement_date <'=>$endDatePlusOne,
					'StockMovement.bool_transfer'=>true,
					'Product.product_type_id'=>PRODUCT_TYPE_PREFORMA,
					'StockMovement.bool_input'=>true,
					'StockMovement.origin_stock_movement_id'=>$originTransfersIngroup[$i]['StockMovement']['id'],
				),
			));
			
			if (!empty($destinationMovement)){
				$transferIngroups[$i]['movement_date']=$originTransfersIngroup[$i]['StockMovement']['movement_date'];
				$transferIngroups[$i]['transfer_code']=$originTransfersIngroup[$i]['StockMovement']['transfer_code'];
				$transferIngroups[$i]['origin_product_id']=$originTransfersIngroup[$i]['Product']['id'];
				$transferIngroups[$i]['origin_product_name']=$originTransfersIngroup[$i]['Product']['name'];
				$transferIngroups[$i]['origin_product_quantity']=$originTransfersIngroup[$i]['StockMovement']['product_quantity'];
				$transferIngroups[$i]['destination_product_id']=$destinationMovement['Product']['id'];
				$transferIngroups[$i]['destination_product_name']=$destinationMovement['Product']['name'];
				$transferIngroups[$i]['destination_product_quantity']=$destinationMovement['StockMovement']['product_quantity'];
                $transferIngroups[$i]['comment']=$destinationMovement['StockMovement']['comment'];
			}
		}
		//debug($transferIngroups);exit;
	    $originTransfersPreformas=$this->StockMovement->find('all',array(
			'fields'=>array('StockMovement.id','Product.id','Product.name','StockMovement.product_quantity','StockMovement.movement_date','StockMovement.transfer_code'),
			'conditions'=>array(
				'StockMovement.movement_date >='=>$startDate,
				'StockMovement.movement_date <'=>$endDatePlusOne,
				'StockMovement.bool_transfer'=>true,
				'Product.product_type_id'=>PRODUCT_TYPE_PREFORMA,
				'StockMovement.bool_input'=>'0',
			),
			'order'=>'DATE(StockMovement.movement_date) DESC, StockMovement.transfer_code DESC',
		));
		 	
		$transfersPreformas=[];
		
		for ($i=0;$i<count($originTransfersPreformas);$i++){
			$destinationMovement=$this->StockMovement->find('first',array(
				'fields'=>array('StockMovement.id','Product.id','Product.name','StockMovement.product_quantity','StockMovement.movement_date','StockMovement.comment'),
				'conditions'=>array(
					'StockMovement.movement_date >='=>$startDate,
					'StockMovement.movement_date <'=>$endDatePlusOne,
					'StockMovement.bool_transfer'=>true,
					'Product.product_type_id'=>PRODUCT_TYPE_INJECTION_OUTPUT,
					'StockMovement.bool_input'=>true,
					'StockMovement.origin_stock_movement_id'=>$originTransfersPreformas[$i]['StockMovement']['id'],
				),
			));
			
			if (!empty($destinationMovement)){
		        
                $transfersPreformas[$i]['movement_date']=$originTransfersPreformas[$i]['StockMovement']['movement_date'];
				$transfersPreformas[$i]['transfer_code']=$originTransfersPreformas[$i]['StockMovement']['transfer_code'];
				$transfersPreformas[$i]['origin_product_id']=$originTransfersPreformas[$i]['Product']['id'];
				$transfersPreformas[$i]['origin_product_name']=$originTransfersPreformas[$i]['Product']['name'];
				$transfersPreformas[$i]['origin_product_quantity']=$originTransfersPreformas[$i]['StockMovement']['product_quantity'];
				$transfersPreformas[$i]['destination_product_id']=$destinationMovement['Product']['id'];
				$transfersPreformas[$i]['destination_product_name']=$destinationMovement['Product']['name'];
				$transfersPreformas[$i]['destination_product_quantity']=$destinationMovement['StockMovement']['product_quantity'];
                $transfersPreformas[$i]['comment']=$destinationMovement['StockMovement']['comment'];



			}
		}
		//debug($transfersPreformas);exit;
		$this->set(compact('transferIngroups','transfersPreformas','startDate','endDate'));
		
	}	

     public function guardarReporteTransferenciasprod() {
		$exportData=$_SESSION['transferData'];
		$this->set(compact('exportData'));
	}
	
	public function guardarReporteReclasificaciones() {
		$exportData=$_SESSION['reclassificationData'];
		$this->set(compact('exportData'));
	}	
	
	public function reclasificarTapones() {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
		
		$allCaps=$this->Product->find('list',['conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_CAP]]);
		
		$capInventory = $this->StockItem->getInventoryTotals(CATEGORY_OTHER,[PRODUCT_TYPE_CAP]);
    
    $plantId=PLANT_SANDINO;
    $this->set(compact('plantId'));
		//pr($capInventory);
		
		if ($this->request->is(array('post'))) {
			//pr($this->request->data);
			$productTypeID=PRODUCT_TYPE_CAP;
			$reclassificationDate=$this->request->data['Reclass']['reclassification_date'];
			$reclassificationCode=$this->request->data['Reclass']['reclassification_code'];
			
			$reclassificationDateString=$reclassificationDate['year'].'-'.$reclassificationDate['month'].'-'.$reclassificationDate['day'];
			$reclassificationDatePlusOne=date("Y-m-d",strtotime($reclassificationDateString."+1 days"));
			
			if ($reclassificationDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha de reclasificación no puede estar en el futuro!  No se realizó la reclasificación.'), 'default',array('class' => 'error-message'));
			}
			else {
				//pr($reclassificationDate);
				$original_cap_id=$this->request->data['Reclass']['original_cap_id'];
				$target_cap_id=$this->request->data['Reclass']['target_cap_id'];
				$quantity_caps=$this->request->data['Reclass']['quantity_caps'];
        $reclassificationComment=$this->request->data['Reclass']['comment'];
				if ($quantity_caps>0 && $original_cap_id>0 && $target_cap_id>0){
					$quantityInStock=0;
					
					$stockItemsForCap=$this->StockItem->find('all',array(
						'conditions'=>array(
							'product_id'=>$original_cap_id,
              'StockItem.stockitem_creation_date <'=> $reclassificationDateString,        
              'StockItem.stockitem_depletion_date >='=> $reclassificationDateString,
						),
					));
					foreach ($stockItemsForCap as $stockItemForCap){
						$lastStockItemLog=$this->StockItemLog->find('first',array(
							'conditions'=>array(
								'StockItemLog.stockitem_id'=>$stockItemForCap['StockItem']['id'],
								'StockItemLog.stockitem_date <'=>$reclassificationDatePlusOne,
							),
							'order'=>'StockItemLog.id DESC'
						));
						//pr($lastStockItemLog);
						if(!empty($lastStockItemLog['StockItemLog'])){
							$quantityInStock+=$lastStockItemLog['StockItemLog']['product_quantity'];
						}
					}
					
					if ($quantityInStock<$quantity_caps){
						$this->Session->setFlash('Intento de reclasificar '.$quantity_caps." ".$allCaps[$original_cap_id]." pero en bodega únicamente hay ".$quantityInStock, 'default',array('class' => 'error-message'));
					}
					else {
						
						$currentdate= new DateTime();
						$usedCapStockItems=$this->StockItem->getOtherMaterialsForSale($original_cap_id,$quantity_caps,$reclassificationDatePlusOne,0,"DESC");
						
						$quantityAvailableForReclassification=0;
						if (count($usedCapStockItems)){
							foreach ($usedCapStockItems as $usedCapStockItem){
								$quantityAvailableForReclassification+=$usedCapStockItem['quantity_present'];
							}
						}
						if ($quantity_caps>$quantityAvailableForReclassification){
							$this->Session->setFlash('Los lotes presentes en el momento de reclasificación ya salieron de bodega', 'default',array('class' => 'error-message'));
						}
						else {
							// reclassify!
							$newlyCreatedStockItems=[];
							//pr($usedCapStockItems);
							$datasource=$this->StockItem->getDataSource();
							$datasource->begin();
							try{
								
								foreach ($usedCapStockItems as $usedCapStockItem){
									$stockitem_id=$usedCapStockItem['id'];
									$quantity_present=$usedCapStockItem['quantity_present'];
									$quantity_used=$usedCapStockItem['quantity_used'];
									$quantity_remaining=$usedCapStockItem['quantity_remaining'];
									$unit_price=$usedCapStockItem['unit_price'];
									if (!$this->StockItem->exists($stockitem_id)) {
										throw new NotFoundException(__('Invalid StockItem'));
									}
									$linkedStockItem=$this->StockItem->read(null,$stockitem_id);
									$message="Reclassified ".$quantity_used." of ".$allCaps[$original_cap_id]." to ".$allCaps[$target_cap_id]." on ".date("d")."-".date("m")."-".date("Y");
								
									// STEP 1: EDIT THE STOCKITEM OF ORIGIN
									$StockItemData['id']=$stockitem_id;
									$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
									$StockItemData['remaining_quantity']=$quantity_remaining;
									
									$this->StockItem->clear();
									$logsuccess=$this->StockItem->save($StockItemData);
									if (!$logsuccess) {
										echo "problema al editor el lote de origen";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									unset($StockItemData);
									
									// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
									
									$StockMovementData['movement_date']=$reclassificationDate;
									$StockMovementData['bool_input']=false;
									$StockMovementData['name']=$message;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=0;
									$StockMovementData['stockitem_id']=$stockitem_id;
									$StockMovementData['product_id']=$original_cap_id;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$unit_price;
									$StockMovementData['product_total_price']=$unit_price*$quantity_used;
									$StockMovementData['bool_reclassification']=true;
									$StockMovementData['reclassification_code']=$reclassificationCode;
                  $StockMovementData['comment']=$reclassificationComment;
									
									$this->StockMovement->clear();
									$this->StockMovement->create();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
									
									// STEP 3: SAVE THE TARGET STOCKITEM
									$StockItemData['name']=$message;
									$StockItemData['description']=$message;
									$StockItemData['stockitem_creation_date']=$reclassificationDate;
									$StockItemData['product_id']=$target_cap_id;
									$StockItemData['product_unit_price']=$unit_price;
									$StockItemData['original_quantity']=$quantity_used;
									$StockItemData['remaining_quantity']=$quantity_used;
									$StockItemData['production_result_code_id']=0;
									
									$this->StockItem->clear();
									$this->StockItem->create();
									// notice that no new stockitem is created because we are taking from an already existing one
									$logsuccess=$this->StockItem->save($StockItemData);
									
									if (!$logsuccess) {
										echo "problema al guardar el lote de destino";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									unset($StockItemData);
									
									// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
									$new_stockitem_id=$this->StockItem->id;
									$newlyCreatedStockItems[]=$new_stockitem_id;
									
									$origin_stock_movement_id=$this->StockMovement->id;
										
										
									$StockMovementData['movement_date']=$reclassificationDate;
									$StockMovementData['bool_input']=true;
									$StockMovementData['name']=$message;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=0;
									$StockMovementData['stockitem_id']=$new_stockitem_id;
									$StockMovementData['product_id']=$target_cap_id;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$unit_price;
									$StockMovementData['product_total_price']=$unit_price*$quantity_used;
									$StockMovementData['bool_reclassification']=true;
									$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
									$StockMovementData['reclassification_code']=$reclassificationCode;
                  $StockMovementData['comment']=$reclassificationComment;
									
									$this->StockMovement->clear();
									$this->StockMovement->create();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
											
									// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
									$this->recordUserActivity($this->Session->read('User.username'),$message);
								}
								$datasource->commit();
								
								
								foreach ($usedCapStockItems as $usedCapStockItem){
									$this->recreateStockItemLogs($usedCapStockItem['id']);
								}
								for ($i=0;$i<count($newlyCreatedStockItems);$i++){
									$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
								}
								
								$this->Session->setFlash(__('Reclasificación exitosa'),'default',array('class' => 'success'));
								return $this->redirect(array('action' => 'resumenReclasificaciones'));
							}
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
							}
						}
					}
				}
				else {
					$warning="";
					if($quantity_caps==0){
						$warning.="Cantidad de tapones debe estar positiva!<br/>";
					}
					if($original_cap_id==0){
						$warning.="Seleccione el color original!<br/>";
					}
					if($target_cap_id==0){
						$warning.="Seleccione el color nuevo!<br/>";
					}
					$this->Session->setFlash($warning, 'default',array('class' => 'error-message'));
				}				
			}
		}
		
		$lastReclassification=$this->StockMovement->find('first',array(
			'fields'=>array('StockMovement.reclassification_code'),
			'conditions'=>array(
				'bool_reclassification'=>true,
			),
			'order'=>array('StockMovement.reclassification_code' => 'desc'),
		));
		$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1;
		$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
	
		$this->set(compact('allCaps','capInventory','reclassificationCode'));
	}	
	
	public function reclasificarBotellas() {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
		
		$allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
		$allBottles=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE)));
		
		$productionResultCodes=$this->ProductionResultCode->find('list');
		
		$bottleInventory = $this->StockItem->getInventoryTotals(CATEGORY_PRODUCED,[PRODUCT_TYPE_BOTTLE]);
		//pr($bottleInventory);
    $plantId=PLANT_SANDINO;
    $this->set(compact('plantId'));
		
		if ($this->request->is(array('post'))) {
			//pr($this->request->data);
			$reclassificationDate=$this->request->data['Reclass']['reclassification_date'];
			$reclassificationCode=$this->request->data['Reclass']['reclassification_code'];
			
			$reclassificationDateString=$reclassificationDate['year'].'-'.$reclassificationDate['month'].'-'.$reclassificationDate['day'];
			$reclassificationDatePlusOne=date("Y-m-d",strtotime($reclassificationDateString."+1 days"));
			
			if ($reclassificationDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha de reclasificación no puede estar en el futuro!  No se realizó la reclasificación.'), 'default',array('class' => 'error-message'));
			}
			else {
				$bottle_id=$this->request->data['Reclass']['bottle_id'];
				$preforma_id=$this->request->data['Reclass']['preforma_id'];
				$original_production_result_code_id=$this->request->data['Reclass']['original_production_result_code_id'];
				$target_production_result_code_id=$this->request->data['Reclass']['target_production_result_code_id'];
				$quantity_bottles=$this->request->data['Reclass']['quantity_bottles'];
        $reclassificationComment=$this->request->data['Reclass']['comment'];
				if ($quantity_bottles>0 && $bottle_id>0 && $preforma_id>0 && $original_production_result_code_id>0 && $target_production_result_code_id>0){
					$quantityInStock=0;
					$stockItemsForBottle=$this->StockItem->find('all',array(
						'conditions'=>array(
							'product_id'=>$bottle_id,
							'raw_material_id'=>$preforma_id,
							'production_result_code_id'=>$original_production_result_code_id,
              'StockItem.stockitem_creation_date <'=> $reclassificationDateString,        
              'StockItem.stockitem_depletion_date >='=> $reclassificationDateString,
						),
					));
					foreach ($stockItemsForBottle as $stockItemForBottle){
						$lastStockItemLog=$this->StockItemLog->find('first',array(
							'conditions'=>array(
								'StockItemLog.stockitem_id'=>$stockItemForBottle['StockItem']['id'],
								'StockItemLog.stockitem_date <'=>$reclassificationDatePlusOne,
							),
							'order'=>'StockItemLog.id DESC'
						));
						//pr($lastStockItemLog);
						if(!empty($lastStockItemLog['StockItemLog'])){
							$quantityInStock+=$lastStockItemLog['StockItemLog']['product_quantity'];
						}
					}
					
					if ($quantityInStock<$quantity_bottles){
						$this->Session->setFlash('Intento de reclasificar '.$quantity_bottles." ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." (".$productionResultCodes[$original_production_result_code_id].") pero en bodega únicamente hay ".$quantityInStock, 'default',array('class' => 'error-message'));
					}
					else {
						// reclassify!
						$currentdate= new DateTime();
						$usedBottleStockItems=$this->StockItem->getFinishedMaterialsForSale($bottle_id,$original_production_result_code_id,$quantity_bottles,$preforma_id,$reclassificationDatePlusOne,0,"DESC");
						$quantityAvailableForReclassification=0;
            //echo "quantity in stock is ".$quantityInStock."<br/>";
            //echo "quantity bottles is ".$quantity_bottles."<br/>";
            //pr($usedBottleStockItems);
						if (count($usedBottleStockItems)){
							foreach ($usedBottleStockItems as $usedBottleStockItem){
								$quantityAvailableForReclassification+=$usedBottleStockItem['quantity_present'];
							}
						}
            //echo "quantity available for reclassification is ".$quantityAvailableForReclassification."<br/>";
						if ($quantity_bottles>$quantityAvailableForReclassification){
							$this->Session->setFlash('Los lotes presentes en el momento de reclasificación ya salieron de bodega', 'default',array('class' => 'error-message'));
						}
						else {
							$newlyCreatedStockItems=[];
							//pr($usedBottleStockItems);
							$datasource=$this->StockItem->getDataSource();
							$datasource->begin();
							try{
								foreach ($usedBottleStockItems as $usedBottleStockItem){
									$stockitem_id=$usedBottleStockItem['id'];
									$quantity_present=$usedBottleStockItem['quantity_present'];
									$quantity_used=$usedBottleStockItem['quantity_used'];
									$quantity_remaining=$usedBottleStockItem['quantity_remaining'];
									$unit_price=$usedBottleStockItem['unit_price'];
									if (!$this->StockItem->exists($stockitem_id)) {
										throw new NotFoundException(__('Invalid StockItem'));
									}
									$linkedStockItem=$this->StockItem->read(null,$stockitem_id);
									$message="Reclassified ".$quantity_used." of ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." from ".$productionResultCodes[$original_production_result_code_id]." to ".$productionResultCodes[$target_production_result_code_id]." on ".date("d")."-".date("m")."-".date("Y");
									
									// STEP 1: EDIT THE STOCKITEM OF ORIGIN
									$StockItemData['id']=$stockitem_id;
									$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
									$StockItemData['remaining_quantity']=$quantity_remaining;
									
									$this->StockItem->clear();
									$logsuccess=$this->StockItem->save($StockItemData);
									if (!$logsuccess) {
										echo "problema al editor el lote de origen";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									unset($StockItemData);
									
									// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
									$StockMovementData['movement_date']=$reclassificationDate;
									$StockMovementData['bool_input']=false;
									$StockMovementData['name']=$message;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=0;
									$StockMovementData['stockitem_id']=$stockitem_id;
									$StockMovementData['product_id']=$bottle_id;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$unit_price;
									$StockMovementData['product_total_price']=$unit_price*$quantity_used;
									$StockMovementData['production_result_code_id']=$original_production_result_code_id;
									$StockMovementData['bool_reclassification']=true;
									$StockMovementData['reclassification_code']=$reclassificationCode;
                  $StockMovementData['comment']=$reclassificationComment;
									
									$this->StockMovement->clear();
									$this->StockMovement->create();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
									
									// STEP 3: SAVE THE TARGET STOCKITEM
									$StockItemData['name']=$message;
									$StockItemData['description']=$message;
									$StockItemData['stockitem_creation_date']=$reclassificationDate;
									$StockItemData['product_id']=$bottle_id;
									$StockItemData['product_unit_price']=$unit_price;
									$StockItemData['original_quantity']=$quantity_used;
									$StockItemData['remaining_quantity']=$quantity_used;
									$StockItemData['production_result_code_id']=$target_production_result_code_id;
									$StockItemData['raw_material_id']=$preforma_id;
									
									$this->StockItem->clear();
									$this->StockItem->create();
									// notice that no new stockitem is created because we are taking from an already existing one
									$logsuccess=$this->StockItem->save($StockItemData);
									
									if (!$logsuccess) {
										echo "problema al guardar el lote de destino";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									unset($StockItemData);
									
									// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
									$new_stockitem_id=$this->StockItem->id;
									$newlyCreatedStockItems[]=$new_stockitem_id;
									
									$origin_stock_movement_id=$this->StockMovement->id;
									
									$StockMovementData['movement_date']=$reclassificationDate;
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
                  $StockMovementData['comment']=$reclassificationComment;
									
									$this->StockMovement->clear();
									$this->StockMovement->create();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
											
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
								
								$this->Session->setFlash(__('Reclasificación exitosa'),'default',array('class' => 'success'));
								return $this->redirect(array('action' => 'resumenReclasificaciones'));
							}
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
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
						$warning.="Seleccione una botella!<br/>";
					}
					if($preforma_id==0){
						$warning.="Seleccione una preforma!<br/>";
					}
					if($original_production_result_code_id==0){
						$warning.="Seleccione la calidad original!<br/>";
					}
					if($target_production_result_code_id==0){
						$warning.="Seleccione la calidad nueva!<br/>";
					}
					$this->Session->setFlash($warning, 'default',array('class' => 'error-message'));
				}
			
			}
		}
		
		$lastReclassification=$this->StockMovement->find('first',array(
			'fields'=>array('StockMovement.reclassification_code'),
			'conditions'=>array(
				'bool_reclassification'=>true,
			),
			'order'=>array('StockMovement.reclassification_code' => 'desc'),
		));
		$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1;
		$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
	
		$this->set(compact('productTypesNotRaw','allPreformas','allBottles','productionResultCodes','bottleInventory','reclassificationCode'));
	}	
	
	public function reclasificarBotellasPreformas() {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
		
		$allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
		$allBottles=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE)));
		
		$productionResultCodes=$this->ProductionResultCode->find('list',array('conditions'=>array('id !='=>PRODUCTION_RESULT_CODE_A)));
		
		$bottleInventory = $this->StockItem->getInventoryTotals(CATEGORY_PRODUCED,[PRODUCT_TYPE_BOTTLE]);
		//pr($bottleInventory);
    $plantId=PLANT_SANDINO;
    $this->set(compact('plantId'));
		
		if ($this->request->is(array('post'))) {
			//pr($this->request->data);
			$reclassificationDate=$this->request->data['Reclass']['reclassification_date'];
			$reclassificationCode=$this->request->data['Reclass']['reclassification_code'];
			
			$reclassificationDateString=$reclassificationDate['year'].'-'.$reclassificationDate['month'].'-'.$reclassificationDate['day'];
			$reclassificationDatePlusOne=date("Y-m-d",strtotime($reclassificationDateString."+1 days"));
			
			if ($reclassificationDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha de reclasificación no puede estar en el futuro!  No se realizó la reclasificación.'), 'default',array('class' => 'error-message'));
			}
			else {
				$bottle_id=$this->request->data['Reclass']['bottle_id'];
				$preforma_id=$this->request->data['Reclass']['preforma_id'];
				$original_production_result_code_id=$this->request->data['Reclass']['original_production_result_code_id'];
				$target_preforma_id=$this->request->data['Reclass']['target_preforma_id'];
				$quantity_bottles=$this->request->data['Reclass']['quantity_bottles'];
        $reclassificationComment=$this->request->data['Reclass']['comment'];
				if ($quantity_bottles>0 && $bottle_id>0 && $preforma_id>0 && $original_production_result_code_id>0 && $target_preforma_id>0){
					$quantityInStock=0;
					$stockItemsForBottle=$this->StockItem->find('all',array(
						'conditions'=>array(
							'product_id'=>$bottle_id,
							'raw_material_id'=>$preforma_id,
							'production_result_code_id'=>$original_production_result_code_id,
              // CONDITIONS ADDED 20180314
              'StockItem.stockitem_creation_date <'=> $reclassificationDateString,        
              'StockItem.stockitem_depletion_date >='=> $reclassificationDateString,
						),
					));
					foreach ($stockItemsForBottle as $stockItemForBottle){
						$lastStockItemLog=$this->StockItemLog->find('first',array(
							'conditions'=>array(
								'StockItemLog.stockitem_id'=>$stockItemForBottle['StockItem']['id'],
								'StockItemLog.stockitem_date <'=>$reclassificationDatePlusOne,
							),
							'order'=>'StockItemLog.id DESC'
						));
						//pr($lastStockItemLog);
						if(!empty($lastStockItemLog['StockItemLog'])){
							$quantityInStock+=$lastStockItemLog['StockItemLog']['product_quantity'];
						}
					}
					
					if ($quantityInStock<$quantity_bottles){
						$this->Session->setFlash('Intento de reclasificar '.$quantity_bottles." ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." (".$productionResultCodes[$original_production_result_code_id].") pero en bodega únicamente hay ".$quantityInStock, 'default',array('class' => 'error-message'));
					}
					else {
						
						$currentdate= new DateTime();
						$usedBottleStockItems=$this->StockItem->getFinishedMaterialsForSale($bottle_id,$original_production_result_code_id,$quantity_bottles,$preforma_id,$reclassificationDatePlusOne,0,"DESC");
						$quantityAvailableForReclassification=0;
						if (count($usedBottleStockItems)){
							foreach ($usedBottleStockItems as $usedBottleStockItem){
								$quantityAvailableForReclassification+=$usedBottleStockItem['quantity_present'];
							}
						}
						if ($quantity_bottles>$quantityAvailableForReclassification){
							$this->Session->setFlash('Los lotes presentes en el momento de reclasificación ya salieron de bodega', 'default',array('class' => 'error-message'));
						}
						else {
							// reclassify!
							$newlyCreatedStockItems=[];
							
							//pr($usedBottleStockItems);
							$datasource=$this->StockItem->getDataSource();
							$datasource->begin();
							try{
								foreach ($usedBottleStockItems as $usedBottleStockItem){
									$stockitem_id=$usedBottleStockItem['id'];
									$quantity_present=$usedBottleStockItem['quantity_present'];
									$quantity_used=$usedBottleStockItem['quantity_used'];
									$quantity_remaining=$usedBottleStockItem['quantity_remaining'];
									$unit_price=$usedBottleStockItem['unit_price'];
									if (!$this->StockItem->exists($stockitem_id)) {
										throw new NotFoundException(__('Invalid StockItem'));
									}
									$linkedStockItem=$this->StockItem->read(null,$stockitem_id);
									$message="Reclassified ".$quantity_used." of ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." from ".$productionResultCodes[$original_production_result_code_id]." to ".$allPreformas[$target_preforma_id]." on ".date("d")."-".date("m")."-".date("Y");
									
									// STEP 1: EDIT THE STOCKITEM OF ORIGIN
									$StockItemData['id']=$stockitem_id;
									$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
									$StockItemData['remaining_quantity']=$quantity_remaining;
									
									$this->StockItem->clear();
									$logsuccess=$this->StockItem->save($StockItemData);
									if (!$logsuccess) {
										echo "problema al editor el lote de origen";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									unset($StockItemData);
									
									// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
									$StockMovementData['movement_date']=$reclassificationDate;
									$StockMovementData['bool_input']=false;
									$StockMovementData['name']=$message;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=0;
									$StockMovementData['stockitem_id']=$stockitem_id;
									$StockMovementData['product_id']=$bottle_id;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$unit_price;
									$StockMovementData['product_total_price']=$unit_price*$quantity_used;
									$StockMovementData['production_result_code_id']=$original_production_result_code_id;
									$StockMovementData['bool_reclassification']=true;
									$StockMovementData['reclassification_code']=$reclassificationCode;
                  $StockMovementData['comment']=$reclassificationComment;
									
									$this->StockMovement->clear();
									$this->StockMovement->create();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
									
									// STEP 3: SAVE THE TARGET STOCKITEM
									$StockItemData['name']=$message;
									$StockItemData['description']=$message;
									$StockItemData['stockitem_creation_date']=$reclassificationDate;
									$StockItemData['product_id']=$target_preforma_id;
									$StockItemData['product_unit_price']=$unit_price;
									$StockItemData['original_quantity']=$quantity_used;
									$StockItemData['remaining_quantity']=$quantity_used;
									$StockItemData['production_result_code_id']=0;
									$StockItemData['raw_material_id']=0;
									
									$this->StockItem->clear();
									$this->StockItem->create();
									// notice that no new stockitem is created because we are taking from an already existing one
									$logsuccess=$this->StockItem->save($StockItemData);
									
									if (!$logsuccess) {
										echo "problema al guardar el lote de destino";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									unset($StockItemData);
									
									// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
									$new_stockitem_id=$this->StockItem->id;
									$newlyCreatedStockItems[]=$new_stockitem_id;
									
									$origin_stock_movement_id=$this->StockMovement->id;
									
									$StockMovementData['movement_date']=$reclassificationDate;
									$StockMovementData['bool_input']=true;
									$StockMovementData['name']=$message;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=0;
									$StockMovementData['stockitem_id']=$new_stockitem_id;
									$StockMovementData['product_id']=$target_preforma_id;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$unit_price;
									$StockMovementData['product_total_price']=$unit_price*$quantity_used;
									$StockMovementData['production_result_code_id']=0;
									$StockMovementData['bool_reclassification']=true;
									$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
									$StockMovementData['reclassification_code']=$reclassificationCode;
                                    $StockMovementData['comment']=$reclassificationComment;
									
									$this->StockMovement->clear();
									$this->StockMovement->create();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
											
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
								
								$this->Session->setFlash(__('Reclasificación exitosa'),'default',array('class' => 'success'));
								return $this->redirect(array('action' => 'resumenReclasificaciones'));
							}
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
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
						$warning.="Seleccione una botella!<br/>";
					}
					if($preforma_id==0){
						$warning.="Seleccione una preforma!<br/>";
					}
					if($original_production_result_code_id==0){
						$warning.="Seleccione la calidad original!<br/>";
					}
					if($target_production_result_code_id==0){
						$warning.="Seleccione la calidad nueva!<br/>";
					}
					$this->Session->setFlash($warning, 'default',array('class' => 'error-message'));
				}
			
			}
		}
		
		$lastReclassification=$this->StockMovement->find('first',array(
			'fields'=>array('StockMovement.reclassification_code'),
			'conditions'=>array(
				'bool_reclassification'=>true,
			),
			'order'=>array('StockMovement.reclassification_code' => 'desc'),
		));
		$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1;
		$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
	
		$this->set(compact('productTypesNotRaw','allPreformas','allBottles','productionResultCodes','bottleInventory','reclassificationCode'));
	}	
	
	public function transferirIngroupPreformas() {
		$this->loadModel('Product');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
		
        $allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
		$allPrefingroup=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_INJECTION_OUTPUT,'Product.name LIKE'=>'preforma%')));
		
		$ingroupInventory = $this->StockItem->getInventoryTotals('',[PRODUCT_TYPE_INJECTION_OUTPUT]);
		//pr($ingroupInventory);exit;
        $plantId=PLANT_SANDINO;
        $this->set(compact('plantId'));
		
		if ($this->request->is(array('post'))) {
			
			//pr($this->request->data);
             //pr($this->request->data);
			$tingroupDate=$this->request->data['Ingrouptrans']['tingroup_date'];
			$tingroupPrefCode=$this->request->data['Ingrouptrans']['tingroup_pref_Code'];
		 
			$tingroupDateString=$tingroupDate['year'].'-'.$tingroupDate['month'].'-'.$tingroupDate['day'];
			$tingroupDatePlusOne=date("Y-m-d",strtotime($tingroupDateString."+1 days"));
			
			if ($tingroupDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha de transferencia no puede estar en el futuro!  No se realizó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			else {
				 
			    $preforma_id_origen=$this->request->data['Ingrouptrans']['preforma_id_origen'];
			    $preforma_id_destino=$this->request->data['Ingrouptrans']['preforma_id_destino'];
				$quantity=$this->request->data['Ingrouptrans']['quantity'];
                $transferenciaingroupComment=$this->request->data['Ingrouptrans']['comment'];
				if ($quantity>0 && $preforma_id_origen>0 && $preforma_id_destino>0){
						
						
						
						
						//iniciamos transferencia ingroup preformas
						$quantityInStock=0;
					
					$stockItemsForCap=$this->StockItem->find('all',array(
						'conditions'=>array(
							'product_id'=>$preforma_id_origen,
              'StockItem.stockitem_creation_date <'=> $tingroupDatePlusOne,        
              'StockItem.stockitem_depletion_date >='=> $tingroupDateString,
						),
					));
					foreach ($stockItemsForCap as $stockItemForCap){
						$lastStockItemLog=$this->StockItemLog->find('first',array(
							'conditions'=>array(
								'StockItemLog.stockitem_id'=>$stockItemForCap['StockItem']['id'],
								'StockItemLog.stockitem_date <'=>$tingroupDatePlusOne,
							),
							'order'=>'StockItemLog.id DESC'
						));
						//pr($lastStockItemLog);
						if(!empty($lastStockItemLog['StockItemLog'])){
							$quantityInStock+=$lastStockItemLog['StockItemLog']['product_quantity'];
						}
					}
					
					if ($quantityInStock<$quantity){
						$this->Session->setFlash('Intento de transferir '.$quantity." ".$allPrefingroup[$preforma_id_origen]." pero en bodega únicamente hay ".$quantityInStock, 'default',array('class' => 'error-message'));
					}
					else {
						
						$currentdate= new DateTime();
						$usedCapStockItems=$this->StockItem->getOtherMaterialsForSale($preforma_id_origen,$quantity,$tingroupDatePlusOne,0,"DESC");
						
						$quantityAvailableForReclassification=0;
						if (count($usedCapStockItems)){
							foreach ($usedCapStockItems as $usedCapStockItem){
								$quantityAvailableForReclassification+=$usedCapStockItem['quantity_present'];
							}
						}
						if ($quantity>$quantityAvailableForReclassification){
							$this->Session->setFlash('Los lotes presentes en el momento de transferencia ya salieron de bodega', 'default',array('class' => 'error-message'));
						}
						else {
							// reclassify!
							$newlyCreatedStockItems=[];
							//pr($usedCapStockItems);
							$datasource=$this->StockItem->getDataSource();
							$datasource->begin();
							try{
								
								foreach ($usedCapStockItems as $usedCapStockItem){
									$stockitem_id=$usedCapStockItem['id'];
									$quantity_present=$usedCapStockItem['quantity_present'];
									$quantity_used=$usedCapStockItem['quantity_used'];
									$quantity_remaining=$usedCapStockItem['quantity_remaining'];
									$unit_price=$usedCapStockItem['unit_price'];
									if (!$this->StockItem->exists($stockitem_id)) {
										throw new NotFoundException(__('Invalid StockItem'));
									}
									$linkedStockItem=$this->StockItem->read(null,$stockitem_id);
									echo "$preforma_id_origen    --   $preforma_id_destino ";
							 //print_r($allPrefingroup);exit;
									$message="Transfered ".$quantity_used." of ".$allPrefingroup[$preforma_id_origen]." to ".$allPreformas[$preforma_id_destino]." on ".date("d")."-".date("m")."-".date("Y");
								
									// STEP 1: EDIT THE STOCKITEM OF ORIGIN
									$StockItemData['id']=$stockitem_id;
									$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
									$StockItemData['remaining_quantity']=$quantity_remaining;
									
									$this->StockItem->clear();
									$logsuccess=$this->StockItem->save($StockItemData);
									
									if (!$logsuccess) {
										echo "problema al editor el lote de origen";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									
									unset($StockItemData);
									
									// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
									 
									$StockMovementData['movement_date']=$tingroupDate;
									$StockMovementData['bool_input']='0';
									$StockMovementData['name']=$message;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=0;
									$StockMovementData['stockitem_id']=$stockitem_id;
									$StockMovementData['product_id']=$preforma_id_origen;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$unit_price;
									$StockMovementData['product_total_price']=$unit_price*$quantity_used;
									$StockMovementData['bool_transfer']=true;
									$StockMovementData['transfer_code']=$tingroupPrefCode;
                                    $StockMovementData['comment']=$transferenciaingroupComment;
									 
									$this->StockMovement->clear();
									$this->StockMovement->create();
									
									 
									$logsuccess=$this->StockMovement->save($StockMovementData);
								//debug($StockMovementData);
								
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
									
									// STEP 3: SAVE THE TARGET STOCKITEM
									$StockItemData['name']=$message;
									$StockItemData['description']=$message;
									$StockItemData['stockitem_creation_date']=$tingroupDate;
									$StockItemData['product_id']=$preforma_id_destino;
									$StockItemData['product_unit_price']=$unit_price;
									$StockItemData['original_quantity']=$quantity_used;
									$StockItemData['remaining_quantity']=$quantity_used;
									$StockItemData['production_result_code_id']=0;
									
									$this->StockItem->clear();
									$this->StockItem->create();
									// notice that no new stockitem is created because we are taking from an already existing one
									$logsuccess=$this->StockItem->save($StockItemData);
									
									if (!$logsuccess) {
										echo "problema al guardar el lote de destino";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									unset($StockItemData);
									
									// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
									$new_stockitem_id=$this->StockItem->id;
									$newlyCreatedStockItems[]=$new_stockitem_id;
									
									$origin_stock_movement_id=$this->StockMovement->id;
										
										
									$StockMovementData['movement_date']=$tingroupDate;
									$StockMovementData['bool_input']=true;
									$StockMovementData['name']=$message;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=0;
									$StockMovementData['stockitem_id']=$new_stockitem_id;
									$StockMovementData['product_id']=$preforma_id_destino;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$unit_price;
									$StockMovementData['product_total_price']=$unit_price*$quantity_used;
									$StockMovementData['bool_transfer']=true;
									$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
									$StockMovementData['transfer_code']=$tingroupPrefCode;
                                    $StockMovementData['comment']=$transferenciaingroupComment;
									
									$this->StockMovement->clear();
									$this->StockMovement->create();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
											
									// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
									$this->recordUserActivity($this->Session->read('User.username'),$message);
								}
								$datasource->commit();
								
								
								foreach ($usedCapStockItems as $usedCapStockItem){
									$this->recreateStockItemLogs($usedCapStockItem['id']);
								}
								for ($i=0;$i<count($newlyCreatedStockItems);$i++){
									$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
								}
								
								$this->Session->setFlash(__('Transferencia exitosa'),'default',array('class' => 'success'));
								return $this->redirect(array('action' => 'resumenTransferenciasProductos'));
							}
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('Transferencia falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
							}
						}
					}
						
						
						
						
						
						//finalizamos transferencia ingroup preformas
						//$this->Session->setFlash(__('Transferencia exitosa'),'default',array('class' => 'success'));
						//return $this->redirect(array('action' => 'resumenTransferenciasProductos'));
				}
                else {
					$warning="";
					
					if($preforma_id_origen==0){
						$warning.="Debe seleccionar el producto ingroup origen!<br/>";
					}
					
					if($preforma_id_destino==0){
						$warning.="Debe seleccionar preforma destino!<br/>";
					}
					if($quantity==0){
						$warning.="Cantidad a transferir debe ser mayor a 0!<br/>";
					}
					
					$this->Session->setFlash($warning, 'default',array('class' => 'error-message'));
				}				
					
			}
		}
		
	/* 	$lastReclassification=$this->StockMovement->find('first',array(
			'fields'=>array('StockMovement.reclassification_code'),
			'conditions'=>array(
				'bool_reclassification'=>true,
			),
			'order'=>array('StockMovement.reclassification_code' => 'desc'),
		));
		$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1; */
		$lastTransIngroupPref=$this->StockMovement->find('first',array(
			'fields'=>array('StockMovement.transfer_code'),
			'conditions'=>array(
				'bool_transfer'=>true,
				'transfer_code LIKE'=> "TRANSINGPREF_%",
			),
			'order'=>array('StockMovement.transfer_code' => 'desc'),
		));
		//debug($lastTransIngroupPref);
		if(isset($lastTransIngroupPref['StockMovement']))
		$transferNumber=substr($lastTransIngroupPref['StockMovement']['transfer_code'],13,6)+1;
	    else
		$transferNumber=1;	
		
		
		$tingroupPrefCode="TRANSINGPREF_".str_pad($transferNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
	
		$this->set(compact('allPreformas','allPrefingroup','ingroupInventory','tingroupPrefCode'));
	}	
	

	public function transferirPreformasIngroup() {
		$this->loadModel('Product');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
		
		$allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
		$allPrefingroup=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_INJECTION_OUTPUT,'Product.name LIKE'=>'preforma%')));
		 
		$preformaInventory = $this->StockItem->getInventoryTotals('',[PRODUCT_TYPE_PREFORMA]);
		//pr($preformaInventory);
        $plantId=PLANT_SANDINO;
        $this->set(compact('plantId'));
		
		if ($this->request->is(array('post'))) {
			
			//pr($this->request->data);
             //pr($this->request->data);
			$tingroupDate=$this->request->data['Ingrouptrans']['tingroup_date'];
			$tingroupPrefCode=$this->request->data['Ingrouptrans']['tingroup_pref_Code'];
		 
			$tingroupDateString=$tingroupDate['year'].'-'.$tingroupDate['month'].'-'.$tingroupDate['day'];
			$tingroupDatePlusOne=date("Y-m-d",strtotime($tingroupDateString."+1 days"));
			
			if ($tingroupDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha de transferencia no puede estar en el futuro!  No se realizó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			else {
				 
			    $preforma_id_origen=$this->request->data['Ingrouptrans']['preforma_id_origen'];
			    $preforma_id_destino=$this->request->data['Ingrouptrans']['preforma_id_destino'];
				$quantity=$this->request->data['Ingrouptrans']['quantity'];
                $transferenciaingroupComment=$this->request->data['Ingrouptrans']['comment'];
				if ($quantity>0 && $preforma_id_origen>0 && $preforma_id_destino>0){
						
						
						
						
						//iniciamos transferencia ingroup preformas
						$quantityInStock=0;
					
					$stockItemsForCap=$this->StockItem->find('all',array(
						'conditions'=>array(
							'product_id'=>$preforma_id_origen,
              'StockItem.stockitem_creation_date <='=> $tingroupDatePlusOne,        
              'StockItem.stockitem_depletion_date >='=> $tingroupDateString,
						),
					));
						//debug($stockItemsForCap);exit; 
					foreach ($stockItemsForCap as $stockItemForCap){
						$lastStockItemLog=$this->StockItemLog->find('first',array(
							'conditions'=>array(
								'StockItemLog.stockitem_id'=>$stockItemForCap['StockItem']['id'],
								'StockItemLog.stockitem_date <'=>$tingroupDatePlusOne,
							),
							'order'=>'StockItemLog.id DESC'
						));
						//pr($lastStockItemLog);
						if(!empty($lastStockItemLog['StockItemLog'])){
							$quantityInStock+=$lastStockItemLog['StockItemLog']['product_quantity'];
						}
					}
			
					if ($quantityInStock<$quantity){
						$this->Session->setFlash('Intento de Transferir '.$quantity." ".$allPreformas[$preforma_id_origen]." pero en bodega únicamente hay ".$quantityInStock, 'default',array('class' => 'error-message'));
					}
					else {
						
						$currentdate= new DateTime();
						$usedCapStockItems=$this->StockItem->getOtherMaterialsForSale($preforma_id_origen,$quantity,$tingroupDatePlusOne,0,"DESC");
						
						$quantityAvailableForReclassification=0;
						if (count($usedCapStockItems)){
							foreach ($usedCapStockItems as $usedCapStockItem){
								$quantityAvailableForReclassification+=$usedCapStockItem['quantity_present'];
							}
						}
						if ($quantity>$quantityAvailableForReclassification){
							$this->Session->setFlash('Los lotes presentes en el momento de transferencia ya salieron de bodega', 'default',array('class' => 'error-message'));
						}
						else {
							// reclassify!
							$newlyCreatedStockItems=[];
							//pr($usedCapStockItems);
							$datasource=$this->StockItem->getDataSource();
							$datasource->begin();
							try{
								
								foreach ($usedCapStockItems as $usedCapStockItem){
									$stockitem_id=$usedCapStockItem['id'];
									$quantity_present=$usedCapStockItem['quantity_present'];
									$quantity_used=$usedCapStockItem['quantity_used'];
									$quantity_remaining=$usedCapStockItem['quantity_remaining'];
									$unit_price=$usedCapStockItem['unit_price'];
									if (!$this->StockItem->exists($stockitem_id)) {
										throw new NotFoundException(__('Invalid StockItem'));
									}
									$linkedStockItem=$this->StockItem->read(null,$stockitem_id);
									
							 
									$message="Transfered ".$quantity_used." of ".$allPreformas[$preforma_id_origen]." to ".$allPrefingroup[$preforma_id_destino]." on ".date("d")."-".date("m")."-".date("Y");
								
									// STEP 1: EDIT THE STOCKITEM OF ORIGIN
									$StockItemData['id']=$stockitem_id;
									$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
									$StockItemData['remaining_quantity']=$quantity_remaining;
									
									$this->StockItem->clear();
									$logsuccess=$this->StockItem->save($StockItemData);
									if (!$logsuccess) {
										echo "problema al editor el lote de origen";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									unset($StockItemData);
									
									// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
									
									$StockMovementData['movement_date']=$tingroupDate;
									$StockMovementData['bool_input']='0';
									$StockMovementData['name']=$message;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=0;
									$StockMovementData['stockitem_id']=$stockitem_id;
									$StockMovementData['product_id']=$preforma_id_origen;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$unit_price;
									$StockMovementData['product_total_price']=$unit_price*$quantity_used;
									$StockMovementData['bool_transfer']=true;
									$StockMovementData['transfer_code']=$tingroupPrefCode;
                                    $StockMovementData['comment']=$transferenciaingroupComment;
									
									$this->StockMovement->clear();
									$this->StockMovement->create();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
									
									// STEP 3: SAVE THE TARGET STOCKITEM
									$StockItemData['name']=$message;
									$StockItemData['description']=$message;
									$StockItemData['stockitem_creation_date']=$tingroupDate;
									$StockItemData['product_id']=$preforma_id_destino;
									$StockItemData['product_unit_price']=$unit_price;
									$StockItemData['original_quantity']=$quantity_used;
									$StockItemData['remaining_quantity']=$quantity_used;
									$StockItemData['production_result_code_id']=0;
									
									$this->StockItem->clear();
									$this->StockItem->create();
									// notice that no new stockitem is created because we are taking from an already existing one
									$logsuccess=$this->StockItem->save($StockItemData);
									
									if (!$logsuccess) {
										echo "problema al guardar el lote de destino";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									unset($StockItemData);
									
									// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
									$new_stockitem_id=$this->StockItem->id;
									$newlyCreatedStockItems[]=$new_stockitem_id;
									
									$origin_stock_movement_id=$this->StockMovement->id;
										
										
									$StockMovementData['movement_date']=$tingroupDate;
									$StockMovementData['bool_input']=true;
									$StockMovementData['name']=$message;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=0;
									$StockMovementData['stockitem_id']=$new_stockitem_id;
									$StockMovementData['product_id']=$preforma_id_destino;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$unit_price;
									$StockMovementData['product_total_price']=$unit_price*$quantity_used;
									$StockMovementData['bool_transfer']=true;
									$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
									$StockMovementData['transfer_code']=$tingroupPrefCode;
                                    $StockMovementData['comment']=$transferenciaingroupComment;
									
									$this->StockMovement->clear();
									$this->StockMovement->create();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									unset($StockMovementData);
											
									// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
									$this->recordUserActivity($this->Session->read('User.username'),$message);
								}
								$datasource->commit();
								
								
								foreach ($usedCapStockItems as $usedCapStockItem){
									$this->recreateStockItemLogs($usedCapStockItem['id']);
								}
								for ($i=0;$i<count($newlyCreatedStockItems);$i++){
									$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
								}
								
								$this->Session->setFlash(__('Transferencia exitosa'),'default',array('class' => 'success'));
								return $this->redirect(array('action' => 'resumenTransferenciasProductos'));
							}
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('Transferencia falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
							}
						}
					}
						
						
						
						
						
						//finalizamos transferencia ingroup preformas
						//$this->Session->setFlash(__('Transferencia exitosa'),'default',array('class' => 'success'));
						//return $this->redirect(array('action' => 'resumenTransferenciasProductos'));
				}
                else {
					$warning="";
					
					
					if($preforma_id_origen==0){
						$warning.="Debe seleccionar preforma origen!<br/>";
					}
					if($preforma_id_destino==0){
						$warning.="Debe seleccionar el producto ingroup destino!<br/>";
					}
					
					if($quantity==0){
						$warning.="Cantidad a transferir debe ser mayor a 0!<br/>";
					}
					
					$this->Session->setFlash($warning, 'default',array('class' => 'error-message'));
				}				
					
			}		
		}
		
		/* $lastReclassification=$this->StockMovement->find('first',array(
			'fields'=>array('StockMovement.reclassification_code'),
			'conditions'=>array(
				'bool_reclassification'=>true,
			),
			'order'=>array('StockMovement.reclassification_code' => 'desc'),
		));
		$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1; */
		
		$lastTransPrefIngroup=$this->StockMovement->find('first',array(
			'fields'=>array('StockMovement.transfer_code'),
			'conditions'=>array(
				'bool_transfer'=>true,
				'transfer_code LIKE'=> "TRANSPREFING_%",
			),
			'order'=>array('StockMovement.transfer_code' => 'desc'),
		));
		//$transferNumber=substr($lastTransPrefIngroup['StockMovement']['transfer_code'],6,6)+1;
		if(isset($lastTransIngroupPref['StockMovement']))
		$transferNumber=substr($lastTransIngroupPref['StockMovement']['transfer_code'],13,6)+1;
		else 
		$transferNumber=1;
	
		$tingroupPrefCode="TRANSPREFING_".str_pad($transferNumber,6,"0",STR_PAD_LEFT)."_".$this->Session->read('User.username');
	    
	
		$this->set(compact('allPreformas','allPrefingroup','preformaInventory','tingroupPrefCode'));
	}	
	

		
	
	
/******************** CUADRAR PRECIOS *******************/
	
	public function cuadrarPreciosBotellas(){
		$this->loadModel('StockMovement');
		$this->loadModel('ProductionMovement');
    $this->loadModel('Product');
		$this->loadModel('StockItemLog');
		$this->loadModel('ProductType');
		
		$this->ProductType->recursive=-1;
    $this->ProductionMovement->recursive=-1;
    
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
				
		$finishedProductTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>CATEGORY_PRODUCED)
		));
		
    $finishedProductIds=$this->Product->find('list',array(
      'fields'=>'Product.id',
      'conditions'=>array(
        'Product.product_type_id'=>$finishedProductTypeIds,
      ),
    ));
    
		// for finished
    // this is what slows down the heap
		$allFinishedStockItems=$this->StockItem->find('all',array(
			'fields'=>array('StockItem.id, StockItem.original_quantity, StockItem.stockitem_creation_date, StockItem.product_unit_price, ProductionResultCode.code, RawMaterial.name, Product.name'),
			'conditions'=>array(
				'StockItem.product_id'=>$finishedProductIds,
        'StockItem.stockitem_creation_date >='=>$startDate,
        'StockItem.stockitem_creation_date <'=>$endDatePlusOne,
			),
			'order'=>'RawMaterial.name, Product.name,ProductionResultCode.code, StockItem.id'
		));
		
		for ($i=0;$i<count($allFinishedStockItems);$i++){
			$productionMovementForStockItem=$this->ProductionMovement->find('first',array(
				'fields'=>array('ProductionMovement.stockitem_id, ProductionMovement.product_unit_price,ProductionMovement.production_run_id','ProductionMovement.id'),
				'conditions'=>array(
					'ProductionMovement.stockitem_id'=>$allFinishedStockItems[$i]['StockItem']['id'],
					'ProductionMovement.bool_input'=>false,
				),
			));
			
			$inputProductionMovementsForStockItem=[];
			if (!empty($productionMovementForStockItem)){
				//pr($productionMovementForStockItem);
				$allFinishedStockItems[$i]['StockItem']['production_movement_id']=$productionMovementForStockItem['ProductionMovement']['id'];
				$allFinishedStockItems[$i]['StockItem']['movement_price']=$productionMovementForStockItem['ProductionMovement']['product_unit_price'];
				$inputProductionMovementsForStockItem=$this->ProductionMovement->find('all',array(
				'fields'=>array('ProductionMovement.stockitem_id, ProductionMovement.product_unit_price, ProductionMovement.product_quantity'),
				'conditions'=>array(
					'ProductionMovement.production_run_id'=>$productionMovementForStockItem['ProductionMovement']['production_run_id'],
					'ProductionMovement.product_quantity >'=>0,
					'ProductionMovement.bool_input'=>true,
				),
			));
			}
			else {
				$allFinishedStockItems[$i]['StockItem']['movement_price']=-1;
			}

			$rightPrice=-1;
			$totalPriceInput=0;
			$totalQuantityInput=0;
			foreach ($inputProductionMovementsForStockItem as $inputProductionMovementForStockItem){
				$totalPriceInput+=$inputProductionMovementForStockItem['ProductionMovement']['product_unit_price']*$inputProductionMovementForStockItem['ProductionMovement']['product_quantity'];
				$totalQuantityInput+=$inputProductionMovementForStockItem['ProductionMovement']['product_quantity'];
			}
			if (!empty($inputProductionMovementsForStockItem)){
				$allFinishedStockItems[$i]['StockItem']['right_price']=$totalPriceInput/$totalQuantityInput;
			}
			else {
				$allFinishedStockItems[$i]['StockItem']['right_price']=0;
			}
		}
		
		$this->set(compact('allFinishedStockItems'));
	}

	public function recreateAllBottleCosts(){}
	
	public function recreateProductionMovementPriceForSquaring($productionmovementid,$rightprice){
		$this->loadModel('ProductionMovement');
		$datasource=$this->ProductionMovement->getDataSource();
		$datasource->begin();
		try {
			$productionMovementData['id']=$productionmovementid;
			$productionMovementData['product_unit_price']=$rightprice;
			$logsuccess=$this->ProductionMovement->save($productionMovementData);
			if (!$logsuccess){
				echo "Error al guardar el movimiento de producción.  No se guardó<br/>";
				pr($this->validateErrors($this->ProductionMovement));
				throw new Exception();
			}
			$datasource->commit();
			$this->Session->setFlash(__('The production movement has been saved.'), 'default',array('class' => 'success'));
			return $this->redirect(array('action' => 'cuadrarPreciosBotellas'));
		}
		catch(Exception $e){
			$datasource->rollback();
			$this->Session->setFlash(__('The production movement could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
		}	
	}
	
	public function recreateStockItemPriceForSquaring($stockitemid,$rightprice){
		$datasource=$this->StockItem->getDataSource();
		$datasource->begin();
		try {
			$stockItemData['id']=$stockitemid;
			$stockItemData['product_unit_price']=$rightprice;
			$logsuccess=$this->StockItem->save($stockItemData);
			if (!$logsuccess){
				echo "Error al guardar el movimiento de producción.  No se guardó<br/>";
				pr($this->validateErrors($this->StockItem));
				throw new Exception();
			}
			$datasource->commit();
			$this->Session->setFlash(__('The stock item has been saved.'), 'default',array('class' => 'success'));
			return $this->redirect(array('action' => 'cuadrarPreciosBotellas'));
		}
		catch(Exception $e){
			$datasource->rollback();
			$this->Session->setFlash(__('The stock item could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
		}
	}
	
  public function index() {
		$this->StockItem->recursive = 0;
		$this->Paginator->settings = array(
			'conditions' => array('StockItem.remaining_quantity > '=> '0')
		);
		$stockItems = $this->Paginator->paginate('StockItem');
		$this->set(compact('stockItems'));
	}

	public function view($id = null) {
		if (!$this->StockItem->exists($id)) {
			throw new NotFoundException(__('Invalid stock item'));
		}
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		$options = [
      'conditions' => ['StockItem.id' => $id],
			'contain'=>[
        'Warehouse'=>[
          'id','name',
        ],
				'ProductionMovement'=>[
					'Product'=>[
						'fields'=> ['name'],
					],
					'ProductionRun'=>[
						'fields'=>['id','production_run_code'],
					],
				],
				'StockMovement'=>[
          'fields'=> [
            'StockMovement.movement_date',
            'StockMovement.bool_input',
            'StockMovement.name',
            'StockMovement.product_unit_price','StockMovement.product_quantity',
            'StockMovement.reclassification_code','StockMovement.transfer_code','StockMovement.adjustment_code',
          ],
					'Product'=>[
						'fields'=> ['name'],
					],
					'Order'=>[
						'fields'=>['order_code'],
					],
				],
				'ProductionResultCode',
				'Product'=>[
          'fields'=> [
            'Product.id','Product.name',
          ],
					'ProductType'=>[
						'fields'=> ['product_category_id'],
					],
				],
			],
			
		];
		$this->set('stockItem', $this->StockItem->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->StockItem->create();
			if ($this->StockItem->save($this->request->data)) {
				$this->Session->setFlash(__('The stock item has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock item could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$productTypes = $this->StockItem->ProductType->find('list');
		$productionResultCodes = $this->StockItem->ProductionResultCode->find('list');
		$this->set(compact('productTypes', 'productionResultCodes'));
	}

	public function edit($id = null) {
		if (!$this->StockItem->exists($id)) {
			throw new NotFoundException(__('Invalid stock item'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->StockItem->save($this->request->data)) {
				$this->Session->setFlash(__('The stock item has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock item could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('StockItem.' . $this->StockItem->primaryKey => $id));
			$this->request->data = $this->StockItem->find('first', $options);
		}
		$productTypes = $this->StockItem->ProductType->find('list');
		$productionResultCodes = $this->StockItem->ProductionResultCode->find('list');
		$this->set(compact('productTypes', 'productionResultCodes'));
	}
	
}


