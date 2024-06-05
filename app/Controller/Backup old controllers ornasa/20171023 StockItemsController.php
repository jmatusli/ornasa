<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');


class StockItemsController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 
   
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
		$options = array(
			'contain'=>array(
				'ProductionMovement'=>array(
					'Product'=>array(
						'fields'=> array('name')
					),
					'ProductionRun'=>array(
						'fields'=>array('id','production_run_code')
					),
				),
				'StockMovement'=>array(
					'Product'=>array(
						'fields'=> array('name')
					),
					'Order'=>array(
						'fields'=>array('order_code')
					),
				),
				'ProductionResultCode',
				'Product'=>array(
					'ProductType'=>array(
						'fields'=> array('product_category_id')
					),
				),
			),
			'conditions' => array('StockItem.' . $this->StockItem->primaryKey => $id)
		);
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
	
	public function inventario() {
		$this->loadModel('ProductCategory');
		$this->loadModel('ProductType');
		$this->loadModel('StockItemLog');
		
		$inventoryDate = null;
		$warehouseId=WAREHOUSE_DEFAULT;
		
		if ($this->request->is('post')) {
			$inventoryDateArray=$this->request->data['Report']['inventorydate'];
			$inventoryDateString=$inventoryDateArray['year'].'-'.$inventoryDateArray['month'].'-'.$inventoryDateArray['day'];
			$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
			
			$warehouseId=$this->request->data['Report']['warehouse_id'];
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
		
		$this->ProductCategory->recursive=-1;
		$this->ProductType->recursive=-1;
		
		$productCategories=$this->ProductCategory->find('all',array(
			'contain'=>array(
				'ProductType',
			),
		));
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
		for ($pc=0;$pc<count($productCategories);$pc++){
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
				}
			 }
		}
				
		$this->loadModel('Warehouse');
		$warehouses=$this->Warehouse->find('list',array(
			'order'=>'Warehouse.name',
		));
		$this->set(compact('warehouses'));
		
		$filename="Hoja_Inventario_".date('d_m_Y'); 
		$this->set(compact('filename'));
	}

	public function guardarReporteInventario() {
		$exportData=$_SESSION['inventoryReport'];
		$this->set(compact('exportData'));
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
	
		if ($inventoryDate==null){
			$startDateString=$_SESSION['inventoryDate'];
		}
		else {
			$inventoryDateString=$inventoryDate;
		}
		$inventoryDate=date( "Y-m-d", strtotime($inventoryDateString));
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDateString."+1 days"));
		$this->set(compact('inventoryDate','inventoryDatePlusOne'));
		
		$conditions=array('Product.product_type_id'=> PRODUCT_TYPE_BOTTLE);
		if ($warehouseId>0){
			$conditions[]=array('StockItem.warehouse_id'=>$warehouseId,);
		}
		
		$bottleCount =$this->StockItem->find('count', array(
			'fields'=>array(
				'Product.name',
				'Product.id',
				'Product.packaging_unit',
				'ProductionResultCode.code',
				'RawMaterial.name',
				'ProductionResultCode.id',
				'RawMaterial.name',
				'(SELECT SUM(`stock_items`.`remaining_quantity`) 
					FROM stock_items 
					right outer JOIN `products` ON (`stock_items`.`product_id` = `products`.`id`) 	
					WHERE production_result_code_id="1" 
						AND stock_items.product_id=Product.id
						AND stock_items.raw_material_id = RawMaterial.id
					
				) AS Remaining_A',
				'(SELECT SUM(`stock_items`.`remaining_quantity`) 
					FROM stock_items 
					right outer JOIN `products` 
						ON (`stock_items`.`product_id` = `products`.`id`) 
						WHERE production_result_code_id="2" 
							AND stock_items.product_id=Product.id
							AND stock_items.raw_material_id = RawMaterial.id
				) AS Remaining_B',
			),
			'conditions' => $conditions,
			'group'=>'Product.name,RawMaterial.name',
			'order'=>'ISNULL(RawMaterial.name),RawMaterial.name ASC, Product.name ASC',
		));
		$this->Paginator->settings = array(
			'fields'=>array(
				'Product.name',
				'Product.id',
				'Product.packaging_unit',
				'ProductionResultCode.code',
				'RawMaterial.id',
				'RawMaterial.name',
				'ProductionResultCode.id',
				'RawMaterial.name',
				'(SELECT SUM(`stock_items`.`remaining_quantity`) 
					FROM stock_items 
					right outer JOIN `products` ON (`stock_items`.`product_id` = `products`.`id`) 	
					WHERE production_result_code_id="1" 
						AND stock_items.product_id=Product.id
						AND stock_items.raw_material_id = RawMaterial.id
					
				) AS Remaining_A',
				
				'(SELECT SUM(`stock_items`.`remaining_quantity`) 
					FROM stock_items 
					right outer JOIN `products` 
						ON (`stock_items`.`product_id` = `products`.`id`) 
						WHERE production_result_code_id="2" 
							AND stock_items.product_id=Product.id
							AND stock_items.raw_material_id = RawMaterial.id
				) AS Remaining_B',
			),
			'conditions' => $conditions,
			'group'=>'Product.name,RawMaterial.name',
			'order'=>'ISNULL(RawMaterial.name),RawMaterial.name ASC, Product.name ASC',
			'limit'=>$bottleCount
		);
		$productos = $this->Paginator->paginate('StockItem');
		
		$conditions=array('Product.product_type_id'=> PRODUCT_TYPE_CAP);
		if ($warehouseId>0){
			$conditions[]=array('StockItem.warehouse_id'=>$warehouseId,);
		}
		
		$taponesCount= $this->StockItem->find('count', array(
			'fields'=>array(
				'Product.name','Product.id','Product.packaging_unit',
				'SUM(StockItem.remaining_quantity) AS Remaining', 
				'ProductionResultCode.code','RawMaterial.name'
			),
			'conditions' => $conditions,
			'group'=>'Product.name',
			'order'=>'Saldo DESC'
		));
		$this->Paginator->settings = array(
			'fields'=>array(
				'Product.name','Product.id','Product.packaging_unit',
				'SUM(StockItem.remaining_quantity) AS Remaining', 
				'ProductionResultCode.code','RawMaterial.name'
			),
			'conditions' => $conditions,
			'group'=>'Product.name',
			'limit'=>$taponesCount
		);
		
		$tapones = $this->Paginator->paginate('StockItem');
		
		$this->loadModel('StockItemLog');
		//pr($productos);
		// now overwrite based on StockItemLogs
		for ($i=0;$i<count($productos);$i++){
			for ($productionresultcode=1;$productionresultcode<4;$productionresultcode++){
				$allStockItems=$this->StockItem->find('all',array(
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
						
						$lastStockItemLog=$this->StockItemLog->find('first',array(
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
						break;
					case PRODUCTION_RESULT_CODE_B:
						$productos[$i][0]['Remaining_B']=$totalStockInventoryDate;
						break;
				}
			}
		}
		$this->set(compact('productos'));
		
		// now overwrite based on StockItemLogs
		for ($i=0;$i<count($tapones);$i++){
			$allStockItems=$this->StockItem->find('all',array(
				'conditions'=>array('StockItem.product_id'=>$tapones[$i]['Product']['id']),
			));
			
			$totalStockInventoryDate=0;
			$totalValueInventoryDate=0;
			if (count($allStockItems)>0){
				$lastStockItemLog=array();
				foreach ($allStockItems as $stockitem){				
					$lastStockItemLog=$this->StockItemLog->find('first',array(
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
		}
		
		$this->set(compact('tapones'));
		
		$filename="Hoja_Inventario_".date('d_m_Y');
		$this->set(compact('filename'));
	}

	
/******************** REPORTES PRODUCTOS Y PRODUCTO *******************/	
	
	public function verReporteProductos($startDate = null,$endDate=null) {
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
		
		$this->loadModel('Product');
		$this->loadModel('ProductionMovement');
		
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
					$this->ProductionMovement->recursive=-1;
					$allProductionMovementsForProductionRun = $this->ProductionMovement->find('all', array(
						//FIELDS ADDED 20151201
						'fields'=>array(
							'ProductionMovement.product_quantity','ProductionMovement.product_unit_price',
						),
						'conditions' => array(
							'ProductionMovement.production_run_id ='=> $productionRunId,
							'ProductionMovement.bool_input ='=> true,
						)
					));
					
					foreach ($allProductionMovementsForProductionRun as $productionMovement){
						//pr($productionMovement);					
						$productConsumedQuantity+=$productionMovement['ProductionMovement']['product_quantity'];
						$productConsumedValue+=$productionMovement['ProductionMovement']['product_unit_price']*$productionMovement['ProductionMovement']['product_quantity'];
					}
				//} 
			}
			// RECURSIVE ADDED 20151201
			$this->StockItem->recursive=-1;
			$allStockItemsForProduct = $this->StockItem->find('all', array(
				// FIELDS ADDED 20151201
				'fields'=>'StockItem.id',
				'conditions' => array(
					'StockItem.product_id ='=> $productId,
				),
			));
			
			foreach ($allStockItemsForProduct as $stockItemForProduct){
				//pr($stockItemForProduct);
				$stockitemId=$stockItemForProduct['StockItem']['id'];
				
				//get the last stockitem log before the startdate to determine the initial stock
				$this->StockItem->StockItemLog->recursive=-1;
				$initialStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockitemId,
						'StockItemLog.stockitem_date <'=>$startDate
					),
					'order'=>'StockItemLog.id DESC'
				));
				if (!empty($initialStockItemLogForStockItem)){
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productInitialValue+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity']*$initialStockItemLogForStockItem['StockItemLog']['product_unit_price'];
				}
				
				//get the last stockitem log before the startdate to determine the initial stock
				$this->StockItem->StockItemLog->recursive=-1;
				$finalStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockitemId,
						'StockItemLog.stockitem_date <'=>$endDatePlusOne
					),
					'order'=>'StockItemLog.id DESC'
				));
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
							$linkedStockItem=$this->StockItem->read(null,$stockMovement['stockitem_id']);
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
					$this->ProductionMovement->recursive=-1;
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
					
					foreach ($allProductionMovementsForProductionRun as $productionMovement){					
						$productProducedQuantity+=$productionMovement['ProductionMovement']['product_quantity'];
						$productProducedValue+=$productionMovement['ProductionMovement']['product_unit_price']*$productionMovement['ProductionMovement']['product_quantity'];
					}
				//} 
			}
			
			// RECURSIVE ADDED 20151201
			$this->StockItem->recursive=-1;
			$allStockItemsForProduct = $this->StockItem->find('all', array(
				// FIELDS ADDED 20151201
				'fields'=>'StockItem.id',
				'conditions' => array(
					'StockItem.product_id ='=> $productId,
				),
			));
			
			foreach ($allStockItemsForProduct as $stockItemForProduct){
				$stockitemId=$stockItemForProduct['StockItem']['id'];
				$this->StockItem->StockItemLog->recursive=-1;
				$initialStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockitemId,
						'StockItemLog.stockitem_date <'=>$startDate
					),
					'order'=>'StockItemLog.id DESC'
				));
				if (!empty($initialStockItemLogForStockItem)){
					$productInitialStock+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity'];
					$productInitialValue+=$initialStockItemLogForStockItem['StockItemLog']['product_quantity']*$initialStockItemLogForStockItem['StockItemLog']['product_unit_price'];
				}
				
				//get the last stockitem log before the startdate to determine the initial stock
				$this->StockItem->StockItemLog->recursive=-1;
				$finalStockItemLogForStockItem=$this->StockItem->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions' => array(
						'StockItemLog.stockitem_id ='=> $stockitemId,
						'StockItemLog.stockitem_date <'=>$endDatePlusOne
					),
					'order'=>'StockItemLog.id DESC'
				));
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
			$this->StockItem->recursive=-1;
			$allStockItemsForProduct = $this->StockItem->find('all', array(
				// FIELDS ADDED 20151201
				'fields'=>'StockItem.id',
				'conditions' => array(
					'StockItem.product_id ='=> $productId,
				),
			));
			
			$this->loadModel('StockItemLog');
			foreach ($allStockItemsForProduct as $stockItem){
				$this->StockItemLog->recursive=-1;
				$initialStockItemLogs=$this->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions'=>array(
						'StockItemLog.stockitem_id'=>$stockItem['StockItem']['id'],
						'StockItemLog.stockitem_date <'=>$startDate,
					),
					'order'=>'StockItemLog.id DESC'
				));
				
				if (!empty($initialStockItemLogs)){
					//pr ($initialStockItemLogs);
					$productInitialStock+=$initialStockItemLogs['StockItemLog']['product_quantity'];
					$productInitialValue+=$initialStockItemLogs['StockItemLog']['product_quantity']*$initialStockItemLogs['StockItemLog']['product_unit_price'];
				}
				$this->StockItemLog->recursive=-1;
				$finalStockItemLogs=$this->StockItemLog->find('first',array(
					'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
					'conditions'=>array(
						'StockItemLog.stockitem_id'=>$stockItem['StockItem']['id'],
						'StockItemLog.stockitem_date <'=>$endDatePlusOne,
					),
					'order'=>'StockItemLog.id DESC'
				));
				
				if (!empty($finalStockItemLogs)){
					//pr ($finalStockItemLogs);
					$productFinalStock+=$finalStockItemLogs['StockItemLog']['product_quantity'];
					$productFinalValue+=$finalStockItemLogs['StockItemLog']['product_quantity']*$finalStockItemLogs['StockItemLog']['product_unit_price'];
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
						
						$stockItemConditions=array(
							'StockItem.product_id' => $productId,
							'StockItem.raw_material_id' => $rawMaterialId,
						);						
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
			$relevantProducts[$rp]['RawMaterial']=$rawMaterial['Product'];
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
				
				$stockItemConditions=array(
					'StockItem.product_id'=>$productId,
					'StockItem.raw_material_id'=>$rawMaterialId,
					'StockItem.production_result_code_id'=>$productionResultCodeId,
				);
				$stockItemIds=$this->StockItem->find('list',array(
					'fields'=>array('StockItem.id'),
					'conditions'=>$stockItemConditions,
				));
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
					$finalStockItemLogForStockItem=$this->StockItemLog->find('first',array(
						'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
						'conditions' => array(
							'StockItemLog.stockitem_id ='=> $stockItemId,
							'StockItemLog.stockitem_date <'=>$endDatePlusOne
						),
						'order'=>'StockItemLog.id DESC'
					));
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
		if($firstProduct['RawMaterial']['id'] != $secondProduct['RawMaterial']['id']){ 		
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
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductType');
		$this->loadModel('Product');
		
		$this->StockItem->recursive=-1;
		$this->ProductionMovement->recursive=-1;
		$this->ProductType->recursive=-1;
		$this->Product->recursive=-1;
		/*********************************************************
		PRODUCED MATERIALS
		*********************************************************/
		$producedProductTypes=$this->ProductType->find('list',array(
			'fields'=>'ProductType.id',
			'conditions'=>array(
				'ProductType.product_category_id'=> CATEGORY_PRODUCED,
			),
		));
		//pr($producedProductTypes);
		$allProducedMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=> $producedProductTypes,
			),
			'contain'=>array(
				'StockMovement'
			),
		));
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
			}
			
			$producedMaterials[$i]['id']=$productId;
			$producedMaterials[$i]['name']=$productName;
			$producedMaterials[$i]['total_quantity']=$productTotalQuantity;
			$producedMaterials[$i]['total_price']=$productTotalValuePrice;
			$producedMaterials[$i]['total_cost']=$productTotalValueCost;
			$producedMaterials[$i]['total_gain']=$productTotalValuePrice-$productTotalValueCost;
			$i++;
		}
		
		/*********************************************************
		TAPONES
		*********************************************************/
		
		$otherProductTypes=$this->ProductType->find('list',array(
			'fields'=>'ProductType.id',
			'conditions'=>array(
				'ProductType.product_category_id'=> CATEGORY_OTHER,
			),
		));
		//pr($otherProductTypes);
		$allOtherMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=> $otherProductTypes,
			),
			'contain'=>array(
				'StockMovement'
			),
		));
		
		$i=0;
		foreach ($allOtherMaterials as $otherMaterial){
			$productId=$otherMaterial['Product']['id'];
			$productName=$otherMaterial['Product']['name'];
			
			$productTotalQuantity=0;
			$productTotalValuePrice=0;
			$productTotalValueCost=0;
			
			foreach ($otherMaterial['StockMovement'] as $stockMovement){
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
			}
			
			$otherMaterials[$i]['id']=$productId;
			$otherMaterials[$i]['name']=$productName;
			$otherMaterials[$i]['total_quantity']=$productTotalQuantity;
			$otherMaterials[$i]['total_price']=$productTotalValuePrice;
			$otherMaterials[$i]['total_cost']=$productTotalValueCost;
			$otherMaterials[$i]['total_gain']=$productTotalValuePrice-$productTotalValueCost;
			$i++;
		}

		$this->loadModel('ThirdParty');
		$this->loadModel('Order');
		$this->loadModel('StockMovement');
		$this->ThirdParty->recursive=0;
		$clients=$this->ThirdParty->find('all',array(
			'conditions'=>array(
				'bool_provider'=>false,
				'bool_active'=>true,
			),
			'order'=>'company_name',
		));
		//pr($clients);
		$clientutility=array();
		
		for ($i=0;$i<count($clients);$i++){
			$clientutility[$i]['id']=$clients[$i]['ThirdParty']['id'];
			$clientutility[$i]['name']=$clients[$i]['ThirdParty']['company_name'];
			
			$salesClientPeriod=$this->Order->find('list',array(
				'fields'=>'Order.id',
				'conditions'=>array(
					'Order.third_party_id'=>$clients[$i]['ThirdParty']['id'],
					'Order.order_date >='=>$startDate,
					'Order.order_date <'=>$endDatePlusOne,
				),
			));
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
	}
	
	public function guardarReporteEstado() {
		$exportData=$_SESSION['statusReport'];
		$this->set(compact('exportData'));
	}
	
	public function guardarReporteProductoMateriaPrima() {
		$exportData=$_SESSION['productReport'];
		$this->set(compact('exportData'));
	}

	public function verReporteProducto($id) {
		$this->loadModel('Product');
		$this->loadModel('Order');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('StockItemLog');
		
		if (!$this->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
		
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
		
		$productData=$this->Product->find('first',
			array(
				'contain'=>array(
					'ProductType'=>array('fields'=>'product_category_id')
				),
				'conditions' => array('Product.id'=> $id)
			)
		);
		
		$this->Product->recursive=0;
		$allFinishedProducts=$this->Product->find('all',
			array(
				'fields'=>array('id','name'),
				'conditions'=>array(
					'ProductType.product_category_id'=>CATEGORY_PRODUCED
				),
				'order'=>'name ASC'
			)
		);
		$this->ProductionResultCode->recursive=0;
		$allProductionResultCodes=$this->ProductionResultCode->find('all',
			array('fields'=>array('id','code'))
		);
		
		$initialStock=0;
		
		$this->StockItem->recursive=0;
		$initialStockItems=$this->StockItem->find('all',array(
			'fields'=>'StockItem.id',
			'conditions'=>array(
				'product_id'=>$id,
			)
		));
		//pr($initialStockItems);
		$this->StockItemLog->recursive=0;
		foreach ($initialStockItems as $initialStockItem){
			$initialStockItemLogs=$this->StockItemLog->find('first',array(
				'conditions'=>array(
					'StockItemLog.stockitem_id'=>$initialStockItem['StockItem']['id'],
					'StockItemLog.stockitem_date <'=>$startDate,
				),
				'order'=>'StockItemLog.id DESC'
			));
			
			if (!empty($initialStockItemLogs)){
				//pr ($initialStockItemLogs);
				$initialStock+=$initialStockItemLogs['StockItemLog']['product_quantity'];
			}
		}
		
		$reclassified=0;
		$this->loadModel('StockMovement');
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
		$rawreclassified=$reclassificationStockMovements['StockMovement']['total_reclassified'];
			
		$finishedreclassified=array();
		foreach ($allFinishedProducts as $finishedProduct){
			foreach ($allProductionResultCodes as $productionResultCode){								
				$reclassificationIncomingStockMovements=$this->StockMovement->find('first',array(
					'fields'=>array('SUM(StockMovement.product_quantity) AS StockMovement__total_reclassified'),
					'conditions'=>array(
						'StockMovement.bool_reclassification'=>true,
						'StockMovement.bool_input'=>true,
						'StockMovement.product_id'=>$finishedProduct['Product']['id'],
						'StockMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
						'StockMovement.movement_date >='=>$startDate,
						'StockMovement.movement_date <'=>$endDatePlusOne,
						'StockItem.raw_material_id'=>$id,
					),
				));
				//pr($reclassificationIncomingStockMovements);
				
				$reclassificationOutgoingStockMovements=$this->StockMovement->find('first',array(
					'fields'=>array('SUM(StockMovement.product_quantity) AS StockMovement__total_reclassified'),
					'conditions'=>array(
						'StockMovement.bool_reclassification'=>true,
						'StockMovement.bool_input'=>false,
						'StockMovement.product_id'=>$finishedProduct['Product']['id'],
						'StockMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
						'StockMovement.movement_date >='=>$startDate,
						'StockMovement.movement_date <'=>$endDatePlusOne,
						'StockItem.raw_material_id'=>$id,
					),
				));
				//pr($reclassificationOutgoingStockMovements);
			
				if (!empty($reclassificationIncomingStockMovements)){
					if (!empty($reclassificationOutgoingStockMovements)){
						$finishedreclassified[]=$reclassificationIncomingStockMovements['StockMovement']['total_reclassified']-$reclassificationOutgoingStockMovements['StockMovement']['total_reclassified'];
					}
					else {
						$finishedreclassified[]=$reclassificationIncomingStockMovements['StockMovement']['total_reclassified'];
					}
				}
				else {
					if (!empty($reclassificationOutgoingStockMovements)){
						$finishedreclassified[]=0-$reclassificationOutgoingStockMovements['StockMovement']['total_reclassified'];
					}
					else {
						$finishedreclassified[]=0;
					}
				}
			}
		}
		
		//pr($finishedreclassified);
		
		
		$stockItemsForPeriodWithProductionRuns=$this->ProductionMovement->find('list', array(
			'fields'=>array('ProductionMovement.stockitem_id'),
			'conditions'=>array(
				'ProductionMovement.product_id'=> $id,
				'ProductionMovement.bool_input'=> true,
				'ProductionMovement.movement_date >='=> $startDate,
				'ProductionMovement.movement_date <'=> $endDatePlusOne,
			),
		));
		
		$stockItemsWithoutProductionRun=$this->StockItem->find('list', array(
			'fields'=>array('id'),
			'conditions'=>array(
				'StockItem.product_id'=> $id,
				'StockItem.stockitem_creation_date >='=> $startDate,
				'StockItem.stockitem_creation_date <'=> $endDatePlusOne,
			),
		));
		
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
					$produced=array();
					$producedRun=null;
					foreach($productionRunMovement['ProductionRun']['ProductionMovement'] as $movementForRun){
						$producedRun=array();
						
						$productidForMovement=$movementForRun['product_id'];
						$productionresultcodeForMovement=$movementForRun['StockItem']['production_result_code_id'];
						foreach ($allFinishedProducts as $finishedProduct){
							foreach ($allProductionResultCodes as $productionResultCode){								
								if ($productidForMovement==$finishedProduct['Product']['id'] && $productionresultcodeForMovement==$productionResultCode['ProductionResultCode']['id']){
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
		
		
		$this->set(compact('productData','thisProductOrders','startDate','endDate','endDatePlusOne','allFinishedProducts','allProductionResultCodes','finalStock','initialStock','rawreclassified','finishedreclassified'));

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
		
		//$productCategoryId=CATEGORY_RAW;
		//$finishedProductId=12;
		//if ($this->request->is('post')) {
		//	$productCategoryId=$this->request->data['Report']['product_category_id'];
		//	$finishedProductId=$this->request->data['Report']['finished_product_id'];
		//}
		//else if ($this->Session->check('productCategoryId')){
		//	$productCategoryId=$_SESSION['productCategoryId'];
		//}		
		//$_SESSION['productCategoryId']=$productCategoryId;
		//$this->set(compact('productCategoryId'));
		//$this->set(compact('finishedProductId'));
		
		$productCategories=$this->ProductCategory->find('list');
		$this->set(compact('productCategories'));
		
		$finishedProductTypeIDs=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>CATEGORY_PRODUCED)
		));
		//$finishedProducts=$this->Product->find('list',array(
		//	'conditions'=>array(
		//		'Product.product_type_id'=>$finishedProductTypeIDs,
		//	),
		//	'order'=>'Product.name',
		//));
		//$this->set(compact('finishedProducts'));
		
		$this->ProductType->recursive=-1;
    foreach ($productCategories as $productCategoryId=>$productCategoryName){
      switch ($productCategoryId){
        case CATEGORY_RAW:
          $rawProductTypeIDs=$this->ProductType->find('list',array(
            'fields'=>array('ProductType.id'),
            'conditions'=>array('ProductType.product_category_id'=>CATEGORY_RAW)
          ));
          break;
        case CATEGORY_PRODUCED:
          // 20170410 LOADED ALREADY 10 LINES UP
          
          //$finishedProductTypeIDs=$this->ProductType->find('list',array(
          //	'fields'=>array('ProductType.id'),
          //	'conditions'=>array('ProductType.product_category_id'=>CATEGORY_PRODUCED)
          //));
          
          break;
        case CATEGORY_OTHER:	
          $otherProductTypeIDs=$this->ProductType->find('list',array(
            'fields'=>array('ProductType.id'),
            'conditions'=>array('ProductType.product_category_id'=>CATEGORY_OTHER)
          ));
          break;
      }
      $this->StockItem->recursive=0;
      switch ($productCategoryId){
        case CATEGORY_RAW:
          // for preformas
          $allRawStockItems=$this->StockItem->find('all',array(
            'fields'=>array('StockItem.id, StockItem.original_quantity, StockItem.remaining_quantity, Product.name'),
            'conditions'=>array(
              'Product.product_type_id'=>$rawProductTypeIDs,
              'StockItem.bool_active'=>true,
            ),
            'order'=>'Product.name, StockItem.id'
          ));
          //pr($allRawStockItems);
          
          for ($i=0;$i<count($allRawStockItems);$i++){
            if (!empty($allRawStockItems[$i]['StockItem']['id'])){
              $inputStockMovementTotalForStockItem=$this->StockMovement->find('first',array(
                'fields'=>array('StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'),
                'conditions'=>array(
                  'StockMovement.stockitem_id'=>$allRawStockItems[$i]['StockItem']['id'],
                  'bool_input'=>true,
                ),
                'group'=>array('StockItem.id'),
              ));
              
              // edited 20150302
              //if (!empty($inputStockMovementTotalForStockItem[0]['total_product_quantity'])){
                $allRawStockItems[$i]['StockItem']['total_moved_in']=$inputStockMovementTotalForStockItem[0]['total_product_quantity'];
                $productionMovementTotalForStockItem=$this->ProductionMovement->find('first',array(
                  'fields'=>array('StockItem.id, SUM(ProductionMovement.product_quantity) AS total_product_quantity'),
                  'conditions'=>array(
                    'ProductionMovement.stockitem_id'=>$allRawStockItems[$i]['StockItem']['id'],
                    'bool_input'=>true,
                  ),
                  'group'=>array('StockItem.id'),
                ));
              //}
              // edited 20150302
              //else {
                //$allRawStockItems[$i]['StockItem']['total_moved_in']=-1;
              //}
              if (!empty($productionMovementTotalForStockItem)){
                $allRawStockItems[$i]['StockItem']['total_used_in_production']=$productionMovementTotalForStockItem[0]['total_product_quantity'];
              }
              else {
                $allRawStockItems[$i]['StockItem']['total_used_in_production']=0;
              }
              $stockMovementTotalForStockItem=$this->StockMovement->find('first',array(
                'fields'=>array('StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'),
                'conditions'=>array(
                  'StockMovement.stockitem_id'=>$allRawStockItems[$i]['StockItem']['id'],
                  'bool_input'=>false,
                ),
                'group'=>array('StockItem.id'),
              ));
              if (!empty($stockMovementTotalForStockItem)){
                $allRawStockItems[$i]['StockItem']['total_moved_out']=$stockMovementTotalForStockItem[0]['total_product_quantity'];
              }
              else {
                $allRawStockItems[$i]['StockItem']['total_moved_out']=0;
              }
              $lastStockItemLog=$this->StockItemLog->find('first',array(
                'fields'=>array('StockItemLog.id, StockItemLog.product_quantity'),
                'conditions'=>array(
                  'StockItemLog.stockitem_id'=>$allRawStockItems[$i]['StockItem']['id'],
                ),
                'order'=>array('StockItemLog.id DESC,StockItemLog.stockitem_date DESC'),
              ));
              
              // edited 20150302
              //$allRawStockItems[$i]['StockItem']['latest_log_quantity']=(!empty($lastStockItemLog['StockItemLog']['product_quantity'])?$lastStockItemLog['StockItemLog']['product_quantity']:"N/A");
              //$allRawStockItems[$i]['StockItem']['latest_log_id']=(!empty($lastStockItemLog['StockItemLog']['id'])?$lastStockItemLog['StockItemLog']['id']:"N/A");
              $allRawStockItems[$i]['StockItem']['latest_log_quantity']=$lastStockItemLog['StockItemLog']['product_quantity'];
              $allRawStockItems[$i]['StockItem']['latest_log_id']=$lastStockItemLog['StockItemLog']['id'];
            }
          }
          break;
        case CATEGORY_PRODUCED:
          // for finished
          $conditions=array(
            'Product.product_type_id'=>$finishedProductTypeIDs,
            'StockItem.bool_active'=>true,
          );
          if (!empty($finishedProductId)){
            $conditions[]=array('Product.id'=>$finishedProductId);
          }
          //pr($conditions);
          $allFinishedStockItems=$this->StockItem->find('all',array(
            'fields'=>array('StockItem.id, StockItem.original_quantity, StockItem.remaining_quantity, ProductionResultCode.code, RawMaterial.name, Product.name'),
            'conditions'=>$conditions,
            'order'=>'RawMaterial.name, Product.name,ProductionResultCode.code, StockItem.id'
          ));
          //pr($allFinishedStockItems);
          
          for ($i=0;$i<count($allFinishedStockItems);$i++){
            $productionMovementTotalForStockItem=$this->ProductionMovement->find('first',array(
              'fields'=>array('StockItem.id, SUM(ProductionMovement.product_quantity) AS total_product_quantity'),
              'conditions'=>array(
                'ProductionMovement.stockitem_id'=>$allFinishedStockItems[$i]['StockItem']['id'],
                'bool_input'=>false,
              ),
              'group'=>array('StockItem.id'),
            ));
            
            //echo "productionMovementTotalForStockItem for stockitem id ".$allFinishedStockItems[$i]['StockItem']['id'];
            //pr($productionMovementTotalForStockItem);
            if (!empty($productionMovementTotalForStockItem[0])){
              $allFinishedStockItems[$i]['StockItem']['total_produced_in_production']=$productionMovementTotalForStockItem[0]['total_product_quantity'];
            }
            else {
              $allFinishedStockItems[$i]['StockItem']['total_produced_in_production']=0;
            }
            
            $reclassificationMovementTotalForStockItem=$this->StockMovement->find('first',array(
              'fields'=>array('StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'),
              'conditions'=>array(
                'StockMovement.stockitem_id'=>$allFinishedStockItems[$i]['StockItem']['id'],
                'bool_input'=>true,
                'bool_reclassification'=>true,
              ),
              'group'=>array('StockItem.id'),
            ));
            if (!empty($reclassificationMovementTotalForStockItem[0])){
              $allFinishedStockItems[$i]['StockItem']['total_produced_in_production']+=$reclassificationMovementTotalForStockItem[0]['total_product_quantity'];
            }
            
            $transferMovementTotalForStockItem=$this->StockMovement->find('first',array(
              'fields'=>array('StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'),
              'conditions'=>array(
                'StockMovement.stockitem_id'=>$allFinishedStockItems[$i]['StockItem']['id'],
                'bool_input'=>true,
                'bool_transfer'=>true,
              ),
              'group'=>array('StockItem.id'),
            ));
            if (!empty($transferMovementTotalForStockItem[0])){
              $allFinishedStockItems[$i]['StockItem']['total_produced_in_production']+=$transferMovementTotalForStockItem[0]['total_product_quantity'];
            }
            
            $stockMovementTotalForStockItem=$this->StockMovement->find('first',array(
              'fields'=>array('StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'),
              'conditions'=>array(
                'StockMovement.stockitem_id'=>$allFinishedStockItems[$i]['StockItem']['id'],
                'bool_input'=>false,
              ),
              'group'=>array('StockItem.id'),
            ));
            if (!empty($stockMovementTotalForStockItem)){
              $allFinishedStockItems[$i]['StockItem']['total_moved_out']=$stockMovementTotalForStockItem[0]['total_product_quantity'];
            }
            else {
              $allFinishedStockItems[$i]['StockItem']['total_moved_out']=0;
            }
            
            $lastStockItemLog=$this->StockItemLog->find('first',array(
              'fields'=>array('StockItemLog.id, StockItemLog.product_quantity'),
              'conditions'=>array(
                'StockItemLog.stockitem_id'=>$allFinishedStockItems[$i]['StockItem']['id'],
              ),
              'order'=>array('StockItemLog.id DESC,StockItemLog.stockitem_date DESC'),
            ));
            if (!empty($lastStockItemLog)){
              $allFinishedStockItems[$i]['StockItem']['latest_log_quantity']=$lastStockItemLog['StockItemLog']['product_quantity'];
              $allFinishedStockItems[$i]['StockItem']['latest_log_id']=$lastStockItemLog['StockItemLog']['id'];
            }
            else {
              $allFinishedStockItems[$i]['StockItem']['latest_log_quantity']=0;
              $allFinishedStockItems[$i]['StockItem']['latest_log_id']=0;
            }
          }
          
          
          break;
        case CATEGORY_OTHER:
          // for other
          $allOtherStockItems=$this->StockItem->find('all',array(
            'fields'=>array('StockItem.id, StockItem.original_quantity, StockItem.remaining_quantity, Product.id, Product.product_type_id, Product.name'),
            'conditions'=>array(
              'Product.product_type_id'=>$otherProductTypeIDs,
              'StockItem.bool_active'=>true,
            ),
            'order'=>'Product.name, StockItem.id'
          ));
          //pr($allOtherStockItems);
          for ($i=0;$i<count($allOtherStockItems);$i++){
            $inputStockMovementTotalForStockItem=$this->StockMovement->find('first',array(
              'fields'=>array('StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'),
              'conditions'=>array(
                'StockMovement.stockitem_id'=>$allOtherStockItems[$i]['StockItem']['id'],
                'bool_input'=>true,
              ),
              'group'=>array('StockItem.id'),
            ));
            if (!empty($inputStockMovementTotalForStockItem)){
              $allOtherStockItems[$i]['StockItem']['total_moved_in']=$inputStockMovementTotalForStockItem[0]['total_product_quantity'];
            }
            else {
              $allOtherStockItems[$i]['StockItem']['total_moved_in']=0;
            }
            $productionMovementTotalForStockItem=$this->ProductionMovement->find('first',array(
              'fields'=>array('StockItem.id, SUM(ProductionMovement.product_quantity) AS total_product_quantity'),
              'conditions'=>array(
                'ProductionMovement.stockitem_id'=>$allOtherStockItems[$i]['StockItem']['id'],
                'bool_input'=>true,
              ),
              'group'=>array('StockItem.id'),
            ));
            if (!empty($productionMovementTotalForStockItem)){
              $allOtherStockItems[$i]['StockItem']['total_used_in_production']=$productionMovementTotalForStockItem[0]['total_product_quantity'];
            }
            else {
              $allOtherStockItems[$i]['StockItem']['total_used_in_production']=0;
            }
            $stockMovementTotalForStockItem=$this->StockMovement->find('first',array(
              'fields'=>array('StockItem.id, SUM(StockMovement.product_quantity) AS total_product_quantity'),
              'conditions'=>array(
                'StockMovement.stockitem_id'=>$allOtherStockItems[$i]['StockItem']['id'],
                'bool_input'=>false,
              ),
              'group'=>array('StockItem.id'),
            ));
            if (!empty($stockMovementTotalForStockItem)){
              $allOtherStockItems[$i]['StockItem']['total_moved_out']=$stockMovementTotalForStockItem[0]['total_product_quantity'];
            }
            else {
              $allOtherStockItems[$i]['StockItem']['total_moved_out']=0;
            }
            $lastStockItemLog=$this->StockItemLog->find('first',array(
              'fields'=>array('StockItemLog.id, StockItemLog.product_quantity'),
              'conditions'=>array(
                'StockItemLog.stockitem_id'=>$allOtherStockItems[$i]['StockItem']['id'],
              ),
              'order'=>array('StockItemLog.id DESC,StockItemLog.stockitem_date DESC'),
            ));
            //echo "stockitemlog for stockitem ".$allOtherStockItems[$i]['StockItem']['id']."<br/>";
            //pr($lastStockItemLog);
            if (!empty($lastStockItemLog['StockItemLog'])){
              $allOtherStockItems[$i]['StockItem']['latest_log_quantity']=$lastStockItemLog['StockItemLog']['product_quantity'];
              $allOtherStockItems[$i]['StockItem']['latest_log_id']=$lastStockItemLog['StockItemLog']['id'];
            }
            else {
              $allOtherStockItems[$i]['StockItem']['latest_log_quantity']=0;
              $allOtherStockItems[$i]['StockItem']['latest_log_id']=0;
            }
          }
          break;
      }
    }
    $this->set(compact('allRawStockItems','allFinishedStockItems','allOtherStockItems'));
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
		foreach (array_keys($allStockItems) as $StockItemID){
			$success=$this->recreateStockItemLogs($StockItemID);
			if (!$success){
				$this->Session->setFlash(__('No se podían recrear los estados de lote para el lote '.$StockItemID), 'default',array('class' => 'error-message'));
				return $this->redirect(array('action' => 'cuadrarEstadosDeLote'));
			}
		}
		$this->Session->setFlash(__('Los estados de lote han estado recreados'),'default',array('class' => 'success'));
		return $this->redirect(array('action' => 'cuadrarEstadosDeLote'));
	}

/******************** RECLASIFICACIONES *******************/
	
	public function resumenReclasificaciones(){
		$startDate = null;
		$endDate = null;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Resumen']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Resumen']['enddate'];
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
		
		$reclassificationsCaps=array();
		
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
		
		$allCaps=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_CAP)));
		
		$capInventory = $this->StockItem->getInventoryTotals(CATEGORY_OTHER,array(PRODUCT_TYPE_CAP));
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
							$newlyCreatedStockItems=array();
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
		
		$bottleInventory = $this->StockItem->getInventoryTotals(CATEGORY_PRODUCED,array(PRODUCT_TYPE_BOTTLE));
		//pr($bottleInventory);
		
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
						if (count($usedBottleStockItems)){
							foreach ($usedBottleStockItems as $usedBottleStockItem){
								$quantityAvailableForReclassification+=$usedBottleStockItem['quantity_present'];
							}
						}
						if ($quantity_bottles>$quantityAvailableForReclassification){
							$this->Session->setFlash('Los lotes presentes en el momento de reclasificación ya salieron de bodega', 'default',array('class' => 'error-message'));
						}
						else {
							$newlyCreatedStockItems=array();
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
		
		$bottleInventory = $this->StockItem->getInventoryTotals(CATEGORY_PRODUCED,array(PRODUCT_TYPE_BOTTLE));
		//pr($bottleInventory);
		
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
							$newlyCreatedStockItems=array();
							
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
			
			$inputProductionMovementsForStockItem=array();
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
	
}


