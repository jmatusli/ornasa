<?php
App::uses('AppModel', 'Model');

class Product extends AppModel {

  var $displayField="name";

  function getProductById($productId){
    $product=$this->find('first',[
      'conditions'=>[
        'Product.id'=>$productId,
      ],
      'recursive'=>-1,
    ]);
    return $product;
  }

  function getProductProductionTypeList($productIds=[]){
		$this->recursive=-1;
		return $productionTypes= $this->find('list', [
      'fields' => ['Product.id','Product.production_type_id'],
      'conditions' => ['Product.id'=>$productIds],
    ]);
	}
  
  function getProductUnitList($productIds=[]){
		$unitIds= $this->find('list', [
      'fields' => ['Product.unit_id'],
      'conditions' => ['Product.id'=>$productIds],
    ]);
    if (empty($unitIds)){
      return [];
    }
    return $this->Unit->getUnitList($unitIds);
	}
  function getUnitIdsForProducts($productIds=[]){
    $conditions=[];
    if (!empty($productIds)){
      $conditions['Product.id']=$productIds;
    }
		return $this->find('list', [
      'fields' => ['Product.unit_id'],
      'conditions' => $conditions,
    ]);
	}
  
	function getProductCategoryId($productId){
		$this->recursive=-1;
		$product= $this->find('first', [
      'fields' => ['Product.product_type_id'],
      'conditions' => ['Product.id'=>$productId],
    ]);
    if (empty($product)){
      echo "no se podía hallar un producto con id ".$productId."<br/>";
      return false;
    }
    else {
      $this->ProductType->recursive=-1;
      $productType= $this->ProductType->find('first',[
        'fields' => ['ProductType.product_category_id'],
        'conditions' => ['ProductType.id'=>$product['Product']['product_type_id']]
      ]);
      return $productType['ProductType']['product_category_id'];
    }
	}
  
  function getProductCategoriesPerProduct(){
    $products=$this->find('all',[
      'fields'=>['Product.id'],
      'contain'=>[
        'ProductType'=>[
          'fields'=>['ProductType.product_category_id'],
        ],
      ],
    ]);
    $productCategoriesPerProduct=[];
    foreach ($products as $product){
      $productCategoriesPerProduct[$product['Product']['id']]=$product['ProductType']['product_category_id'];
    }
    return $productCategoriesPerProduct;
  }
  function getProductTypesPerProduct(){
    return $this->find('list',[
      'fields'=>['Product.id','Product.product_type_id'],
    ]);
    /*
    $products=$this->find('all',[
      'fields'=>['Product.id','Product.product_type_id'],
    ]);
    $productTypesPerProduct=[];
    foreach ($products as $product){
      $productTypesPerProduct[$product['Product']['id']]=$product['Product']['product_type_id'];
    }
    return $productTypesPerProduct;
    */
  }
  
  function getActiveProductsByTypes($productTypeIds){
    $products=$this->find('list',[
			'fields'=>['Product.name'],
			'conditions'=>[
				'Product.bool_active'=>true,
				'Product.product_type_id'=>$productTypeIds,
			],
      'order'=>['Product.volume_ml_max','Product.name'],
		]);
		
		return $products;
  }
  
  function getAllProducts(){
		return $this->find('list',[
			'fields'=>['Product.name'],
			'order'=>['Product.name'],
		]);
	}
  
  function getAllPreformas(){
		$activePreformas=$this->find('list',[
			'fields'=>['Product.name'],
			'conditions'=>[
				'Product.product_type_id'=>PRODUCT_TYPE_PREFORMA,
			],
      'order'=>['Product.weight_g'],
		]);
		
		return $activePreformas;
	}
	
  function getActivePreformas(){
		$activePreformas=$this->find('list',[
			'fields'=>['Product.name'],
			'conditions'=>[
				'Product.bool_active'=>true,
				'Product.product_type_id'=>PRODUCT_TYPE_PREFORMA,
			],
      'order'=>['Product.weight_g'],
		]);
		
		return $activePreformas;
	}
	
  function getActivePreformasAbbreviated(){
		$activePreformas=$this->find('list',[
			'fields'=>['Product.abbreviation'],
			'conditions'=>[
				'Product.bool_active'=>true,
				'Product.product_type_id'=>PRODUCT_TYPE_PREFORMA,
			],
      'order'=>['Product.weight_g'],
		]);
		
		return $activePreformas;
	}
	 
  function getProductsByProductNature($productNatureId,$warehouseId=0){
    if ($productNatureId < 0){
      // WAREHOUSE CONDITION DOES NOT APPLY TO SERVICES
      $conditions=[
        'Product.product_nature_id'=>null,
      ];
    }
    else {  
      $conditions=[
        'Product.product_nature_id'=>$productNatureId,
      ];
    }
    $findOrder=['Product.name ASC'];
    if ($productNatureId == PRODUCT_NATURE_PRODUCED){
      $findOrder=['Product.volume_ml_max ASC','Product.name ASC'];  
    }
    $products=$this->find('list',[
      'conditions'=>$conditions,
      'order'=>$findOrder,
    ]);
    
    if ($warehouseId > 0){
      $warehouseProductModel=ClassRegistry::init('WarehouseProduct');
      foreach ($products as $productId => $productName){
        $warehouseProduct=$this->WarehouseProduct->find('first',[
          'conditions'=>[
            'WarehouseProduct.warehouse_id'=>$warehouseId,
            'WarehouseProduct.product_id'=>$productId,
          ],
          'recursive'=>-1,
          'order'=>'WarehouseProduct.assignment_datetime DESC',
        ]); 
        //pr($warehouseProduct);
        if (empty($warehouseProduct) || !$warehouseProduct['WarehouseProduct']['bool_assigned']){
          unset($products[$productId]);
        }
      }
    }
    
    return $products;
  }
 

  public function getProductListForProductNatures($includedProductNatureIds=[]){
    $conditions=[];
    if (!empty($includedProductNatureIds)){
      $conditions['Product.product_nature_id']=$includedProductNatureIds;
    }
    $productNatures=$this->find('list',[
      'conditions'=>$conditions,
      'order'=>['Product.product_nature_id ASC','Product.name ASC'],
    ]);
    return $productNatures;
  }
	

	
 
  public function getProductIdsForProductType($productTypeIds){
    return $this->find('list',[
      'fields'=>'Product.id',
      'conditions'=>[
        'Product.product_type_id'=>$productTypeIds,
      ],
      'order'=>'Product.name ASC'
    ]);
  }
  
  function getProductsByProductionType($productionTypeId,$productNatureId=0){
    $conditions=[];
    $conditions['Product.production_type_id']=$productionTypeId;
    if ($productNatureId < 0){
      // WAREHOUSE CONDITION DOES NOT APPLY TO SERVICES
      $conditions['Product.product_nature_id']=null;
    }
    elseif ($productNatureId > 0) {  
      $conditions['Product.product_nature_id']=$productNatureId;
    }
    $findOrder=['Product.name ASC'];
    if ($productNatureId == PRODUCT_NATURE_PRODUCED){
      $findOrder=['Product.volume_ml_max ASC','Product.name ASC'];  
    }
    $products=$this->find('list',[
      'conditions'=>$conditions,
      'order'=>$findOrder,
    ]);
    return $products;
  }
 
	function getAllRawMaterialsUsedEver($productId){
		$stockItemModel=ClassRegistry::init('StockItem');
		$stockItemModel->recursive=-1;
		
		$rawMaterialsForProductInStockItems=$stockItemModel->find('all',array(
			'fields'=>array('DISTINCT(StockItem.raw_material_id)'),
			'conditions'=>array(
				'StockItem.product_id'=>$productId,
				'StockItem.original_quantity >'=>0,
			),
		));
		
		$rawMaterials=array();
		
		if (!empty($rawMaterialsForProductInStockItems)){
			foreach($rawMaterialsForProductInStockItems as $stockItem){
				$rawMaterials[]=$stockItem['StockItem']['raw_material_id'];
			}
		}
		return $rawMaterials;
	}
  
  function getBagProductId($productId){
		$this->recursive=-1;
		$product= $this->find('first',[
				'fields' => ['Product.bag_product_id'],
				'conditions' => ['Product.id'=>$productId]
		]);
		return $product['Product']['bag_product_id'];
	}
  function getBagIdsForProducts($productIds=[]){
    $conditions=[];
    if (!empty($productIds)){
      $conditions['Product.id']=$productIds;
    }
		
		return $this->find('list',[
				'fields' => ['Product.id','Product.bag_product_id'],
				'conditions' => $conditions,
		]);
	}
  
  function getProductPackagingUnit($productId){
		$this->recursive=-1;
		$product= $this->find('first',[
				'fields' => ['Product.packaging_unit'],
				'conditions' => ['Product.id'=>$productId]
		]);
		return $product['Product']['bag_product_id'];
	}
  function getPackagingUnitsForProducts($productIds=[]){
		$conditions=[];
    if (!empty($productIds)){
      $conditions['Product.id']=$productIds;
    }
		return $this->find('list',[
				'fields' => ['Product.id','Product.packaging_unit'],
				'conditions' => $conditions,
		]);
	}
  
  function getDefaultCostsForProducts($productIds=[]){
		$conditions=[];
    if (!empty($productIds)){
      $conditions['Product.id']=$productIds;
    }
		return $this->find('list', [
      'fields' => ['Product.default_cost'],
      'conditions' => $conditions,
    ]);
	}
   
  function getProductName($productId){
    $product=$this->getProductById($productId);
    if (empty($product)){
      return $productId; 
    }
    return $product['Product']['name'];
  }
  function getProductTypeId($productId){
    $product=$this->getProductById($productId);
    return $product['Product']['product_type_id'];
  }
  function getProductionTypeId($productId){
    $product=$this->getProductById($productId);
    return $product['Product']['production_type_id'];
  }
  function getProductNatureId($productId){
    $product=$this->getProductById($productId);
    return $product['Product']['product_nature_id'];
  }
  
  function getPreferredRawMaterialId($productId){
    $product=$this->getProductById($productId);
    //pr($product);
    return (empty($product['Product']['preferred_raw_material_id'])?0:$product['Product']['preferred_raw_material_id']);
  }
  function getPreferredRawMaterialsForProducts($productIds=[]){
		return $this->find('list',[
				'fields' => ['Product.id','Product.preferred_raw_material_id'],
				'conditions' => ['Product.id'=>$productIds]
		]);
	}
  function getAvailableProductsForSale ($salesDate,$warehouseId,$boolIncludeServices=false,$finishedProductsForEdit=[],$rawMaterialsForEdit=[]){
    $salesDatePlusOne=date( "Y-m-d", strtotime($salesDate."+1 days"));
    //pr($salesDatePlusOne);
    $stockItemConditions=[
      'StockItem.bool_active'=>true,
      'StockItem.stockitem_creation_date <'=>$salesDatePlusOne,
      'StockItem.warehouse_id'=>$warehouseId,
    ];
    //pr($stockItemConditions);
    $excludedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>['ProductType.product_category_id'=>[CATEGORY_RAW,CATEGORY_CONSUMIBLE]],
    ]);
    
    $productConditions=[];
    if (empty($finishedProductsForEdit)){
      $productConditions=[
        'Product.bool_active'=>true,
        'Product.product_type_id !='=>$excludedProductTypeIds,
      ];
    }
    else {
      $productConditions=[
        'OR'=>[
          [
            'Product.bool_active'=>true,
            'Product.product_type_id !='=>$excludedProductTypeIds,
          ],
          [
            'Product.id'=>$finishedProductsForEdit
          ]
        ],
      ];
    }
    //pr($productConditions);
		$productsAll = $this->find('all',[
			'fields'=>'Product.id,Product.name',
      'conditions'=>$productConditions,
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
			if ($product['StockItem'] != null){
				foreach ($product['StockItem'] as $stockItem){
          //pr($stockItem);
					if ($stockItem['remaining_quantity'] <> 0 || in_array($product['Product']['id'],$finishedProductsForEdit)){
            $productId=$product['Product']['id'];
						$products[$productId]=substr($product['Product']['name'],0,28).(strlen($product['Product']['name'])>28?"...":"");
            //$products[$productId]=$product['Product']['name'];
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
      elseif ($product['ProductType']['id'] == PRODUCT_TYPE_SERVICE && $boolIncludeServices){
        $products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
      }
		}
		//pr($products);
    $preformaConditions=[];
    if (empty($rawMaterialsForEdit)){
      $preformaConditions=[
        'Product.id'=>$rawMaterialIds,
        'Product.bool_active'=>true
      ];
    }
    else {
      $preformaConditions=[
        'OR'=>[
          [
            'Product.id'=>$rawMaterialIds,
            'Product.bool_active'=>true
          ],
          [
            'Product.id'=>$rawMaterialIds,
            'Product.id'=>$rawMaterialsForEdit,
          ]
        ],
      ];
    }
    
    $preformasAll = $this->find('all',[
      'fields'=>['Product.id','Product.name'],
      'conditions' => $preformaConditions,
      'recursive'=>-1,
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
    //pr($products);
    return [
      'products'=>$products,
      'rawMaterialsAvailablePerFinishedProduct'=>$rawMaterialsAvailablePerFinishedProduct,
      'rawMaterials'=>$rawMaterials,
    ];
    
  }
  
  function getAcceptableProductionValue($finishedProductId,$productionRunDate){
    $finishedProduct=$this->find('first',[
      'conditions'=>[
        'Product.id'=>$finishedProductId,
      ],
      'contain'=>[
        'ProductProduction'=>[
          'conditions'=>[
            'ProductProduction.application_date <='=> $productionRunDate,
          ],
          'limit'=>1,
          'order'=>'ProductProduction.application_date DESC,ProductProduction.id DESC'
        ],
      ],
    ]);  
    if (empty($finishedProduct['ProductProduction'])){						
      return 0;
    };
    return $finishedProduct['ProductProduction'][0]['acceptable_production'];
  }
  
  function getAcceptablePriceForProductClientCostQuantityDate($productId,$clientId,$productUnitCost,$productQuantity,$saleDate,$rawMaterialId=0){
    $productTypeId=$this->getProductTypeId($productId);
    if ($productTypeId == PRODUCT_TYPE_BOTTLE){
      $standardPriceInfoCategoryTwo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_TWO,$saleDate);
      //pr($standardPriceInfoCategoryTwo);
      if ($standardPriceInfoCategoryTwo['price'] > 0 && $standardPriceInfoCategoryTwo['price'] > $productUnitCost){
        $acceptablePrice=$standardPriceInfoCategoryTwo['price'];  
      }
      else {
        $standardPriceInfoCategoryGeneral=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_GENERAL,$saleDate);
        //pr($standardPriceInfoCategoryGeneral);
        $acceptablePrice=$standardPriceInfoCategoryGeneral['price'];  
      }
      $clientPriceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$saleDate);
      if ($clientPriceInfo['price'] > 0 && $clientPriceInfo['price'] > $productUnitCost && $clientPriceInfo['price'] < $acceptablePrice){
        $acceptablePrice=$clientPriceInfo['price'];
      }
      
      $productThresholdVolume=$this->ProductThresholdVolume->getCompositeThresholdVolume($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_VOLUME,$saleDate);
      if ($productQuantity >= $productThresholdVolume && $productThresholdVolume > 0){
        $volumePriceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,PRICE_CLIENT_CATEGORY_VOLUME,$saleDate);
        if ($volumePriceInfo['price'] > 0 && $volumePriceInfo['price'] > $productUnitCost && $volumePriceInfo['price'] < $acceptablePrice){
          $acceptablePrice=$volumePriceInfo['price'];
        }
      }
    }
    else {
      $standardPriceInfoCategoryTwo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,PRICE_CLIENT_CATEGORY_TWO,$saleDate);
      //echo 'category two';
      //pr($standardPriceInfoCategoryTwo);
        if ($standardPriceInfoCategoryTwo['price'] > 0 && $standardPriceInfoCategoryTwo['price'] > $productUnitCost){
        $acceptablePrice=$standardPriceInfoCategoryTwo['price'];  
      }
      else {
        $standardPriceInfoCategoryGeneral=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,PRICE_CLIENT_CATEGORY_GENERAL,$saleDate);
        //echo 'category general';
        //pr($standardPriceInfoCategoryGeneral);
        $acceptablePrice=$standardPriceInfoCategoryGeneral['price'];  
      }
      
      $clientPriceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$saleDate);
      if ($clientPriceInfo['price'] > 0 && $clientPriceInfo['price'] > $productUnitCost && $clientPriceInfo['price'] < $acceptablePrice){
        $acceptablePrice=$clientPriceInfo['price'];
      }
      
      $productThresholdVolume=$this->ProductThresholdVolume->getThresholdVolume($productId,PRICE_CLIENT_CATEGORY_VOLUME,$saleDate);
      if ($productQuantity >= $productThresholdVolume  && $productThresholdVolume > 0){
        $volumePriceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,PRICE_CLIENT_CATEGORY_VOLUME,$saleDate);
        if ($volumePriceInfo['price'] > 0 && $volumePriceInfo['price'] > $productUnitCost && $volumePriceInfo['price'] < $acceptablePrice){
          $acceptablePrice=$volumePriceInfo['price'];
        }
      }
    }
    return $acceptablePrice;
  }
  
  function getProductProductionRunUtility($productId,$startDate,$endDate){
    $productionRunUtilityArray=[];
      
    $productionRunModel=ClassRegistry::init('ProductionRun');
		$productionRunIds=$productionRunModel->getProductionRunIdsForProductAndPeriod($productId,$startDate,$endDate);
    //pr($productionRunIds);
    $productionMovementModel=ClassRegistry::init('ProductionMovement');
    $productionMovementData=$productionMovementModel->getProductionMovementDataForProductionRuns($productionRunIds);
    //pr($productionMovementData);
    $stockItemModel=ClassRegistry::init('StockItem');
    foreach ($productionMovementData['outputTotals'] as $rawMaterialId=>$productData){
      //echo 'rawMaterialId is '.$rawMaterialId.'<br/>';
      
      foreach ($productData as $productId=>$productionResultCodeData){
        foreach ($productionResultCodeData as $productionResultCodeId=>$productionData){
          $productionMovementData['outputTotals'][$rawMaterialId][$productId][$productionResultCodeId]['stockMovementData']=$stockItemModel->getStockMovementDataForUtility($productionData['stockItemIds']);
        }  
      }
    }
    
    $productionRunUtilityArray=$productionMovementData;
    //pr($productionRunUtilityArray);
    return $productionRunUtilityArray;
  }
  
  public $validate = [
		'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'product_type_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
    'product_nature_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'packaging_unit' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];

	public $belongsTo = [
		'ProductType' => [
			'className' => 'ProductType',
			'foreignKey' => 'product_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'PreferredRawMaterial' => [
			'className' => 'Product',
			'foreignKey' => 'preferred_raw_material_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'AccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'DefaultCostCurrency' => [
			'className' => 'Currency',
			'foreignKey' => 'default_cost_currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'BagProduct' => [
			'className' => 'Product',
			'foreignKey' => 'bag_product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'ProductNature' => [
			'className' => 'ProductNature',
			'foreignKey' => 'product_nature_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'ProductionType' => [
			'className' => 'ProductionType',
			'foreignKey' => 'production_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Unit' => [
			'className' => 'Unit',
			'foreignKey' => 'unit_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'ProductionRunInput' => [
			'className' => 'ProductionRun',
			'foreignKey' => 'raw_material_id',
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
		'ProductionRunOutput' => [
			'className' => 'ProductionRun',
			'foreignKey' => 'finished_product_id',
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
    
    'ProductPriceLog' => [
			'className' => 'ProductPriceLog',
			'foreignKey' => 'product_id',
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
    'PreviousProductPriceLog' => [
			'className' => 'ProductPriceLog',
			'foreignKey' => 'product_id',
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
    'PriceClientCategoryProductPriceLog' => [
			'className' => 'ProductPriceLog',
			'foreignKey' => 'product_id',
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
    
    'RawMaterialPriceLog' => [
			'className' => 'ProductPriceLog',
			'foreignKey' => 'raw_material_id',
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
			'foreignKey' => 'product_id',
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
		'ProductionMovement' => [
			'className' => 'ProductionMovement',
			'foreignKey' => 'product_id',
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
		'StockItem' => [
			'className' => 'StockItem',
			'foreignKey' => 'product_id',
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
		'MachineProduct' => [
			'className' => 'MachineProduct',
			'foreignKey' => 'product_id',
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
    // acceptable values
		'ProductProduction' => [
			'className' => 'ProductProduction',
			'foreignKey' => 'product_id',
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
    'ProductThresholdVolume' => [
			'className' => 'ProductThresholdVolume',
			'foreignKey' => 'product_id',
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
    /*
    'ProductionTypeProduct' => [
			'className' => 'ProductionTypeProduct',
			'foreignKey' => 'product_id',
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
    'WarehouseProduct' => [
			'className' => 'WarehouseProduct',
			'foreignKey' => 'product_id',
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
