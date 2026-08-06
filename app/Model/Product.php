<?php
App::uses('AppModel', 'Model');

class Product extends AppModel {

  var $displayField="name";
  public $boolIncludeProductsWithoutStock = false;
  
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
			],
			'order'=>'product_type_id DESC, name ASC',
		]);
		//pr($productsAll);
		$products = [];
		$rawMaterialIds=[];
    $rawMaterialsAvailablePerFinishedProduct=[];
		$boolIncludeProductsWithoutStock=(isset($this->boolIncludeProductsWithoutStock)?$this->boolIncludeProductsWithoutStock:false);

		// Compute the balance for every visible product replicating the current
		// inventory process (sp_inventary/sp_saldo): for each stock item take the
		// last stock item log by date and sum its quantity grouped by product,
		// raw material and production result code (A/B/C).
		$productIds=[];
		foreach ($productsAll as $product){
			$productIds[]=$product['Product']['id'];
		}
		$saldoByProduct=[];
		if (!empty($productIds)){
			$productIdsList=implode(',',$productIds);
			$saldoRows=$this->query("
				SELECT `StockItemLog`.`product_id` AS `product_id`,
				       `StockItem`.`raw_material_id` AS `raw_material_id`,
				       SUM(`StockItemLog`.`product_quantity`) AS `total`,
				       SUM(case when `StockItem`.`production_result_code_id`=1 then `StockItemLog`.`product_quantity` else 0 end) AS `Remaining_A`,
				       SUM(case when `StockItem`.`production_result_code_id`=2 then `StockItemLog`.`product_quantity` else 0 end) AS `Remaining_B`,
				       SUM(case when `StockItem`.`production_result_code_id`=3 then `StockItemLog`.`product_quantity` else 0 end) AS `Remaining_C`
				FROM `orna1114_ornasa`.`stock_item_logs` AS `StockItemLog`
				JOIN `orna1114_ornasa`.`stock_items` AS `StockItem` ON `StockItem`.`id`=`StockItemLog`.`stockitem_id`
				JOIN ( SELECT si.id, sil.id AS idx
				       FROM `orna1114_ornasa`.`stock_items` si
				       INNER JOIN `orna1114_ornasa`.`stock_item_logs` sil ON sil.stockitem_id = si.id
				       LEFT JOIN `orna1114_ornasa`.`stock_item_logs` sil2
				         ON sil2.stockitem_id = sil.stockitem_id
				        AND sil2.stockitem_date < DATE_ADD('".$salesDate."', INTERVAL 1 DAY)
				        AND (sil2.stockitem_date > sil.stockitem_date
				             OR (sil2.stockitem_date = sil.stockitem_date AND sil2.id > sil.id))
				       WHERE sil.stockitem_date < DATE_ADD('".$salesDate."', INTERVAL 1 DAY)
				         AND si.warehouse_id = ".(int)$warehouseId."
				         AND sil2.id IS NULL
				     ) sm ON sm.idx=`StockItemLog`.`id`
				WHERE `StockItemLog`.`product_id` IN (".$productIdsList.")
				  AND `StockItem`.`warehouse_id` = ".(int)$warehouseId."
				  AND `StockItemLog`.`product_quantity` <> 0
				GROUP BY `StockItemLog`.`product_id`, `StockItem`.`raw_material_id`
			");
			foreach ($saldoRows as $saldoRow){
				$rowData=$saldoRow[0];
				$productId=$saldoRow['StockItemLog']['product_id'];
				$rawMaterialId=$saldoRow['StockItem']['raw_material_id'];
				if (!array_key_exists($productId,$saldoByProduct)){
					$saldoByProduct[$productId]=[
						'total'=>0,
						'rawMaterials'=>[],
					];
				}
				$saldoByProduct[$productId]['total']+=(float)$rowData['total'];
				if (!empty($rawMaterialId)){
					if (!array_key_exists($rawMaterialId,$saldoByProduct[$productId]['rawMaterials'])){
						$saldoByProduct[$productId]['rawMaterials'][$rawMaterialId]=[
							'1'=>0,
							'2'=>0,
							'3'=>0,
						];
					}
					$saldoByProduct[$productId]['rawMaterials'][$rawMaterialId]['1']+=(float)$rowData['Remaining_A'];
					$saldoByProduct[$productId]['rawMaterials'][$rawMaterialId]['2']+=(float)$rowData['Remaining_B'];
					$saldoByProduct[$productId]['rawMaterials'][$rawMaterialId]['3']+=(float)$rowData['Remaining_C'];
				}
			}
		}
		//pr($saldoByProduct);

		// When editing an order the finished products (even the ones that are
		// currently out of stock) and their raw materials must keep appearing.
		// Make sure the products that have stock items (regardless of balance)
		// are present in the balance map with their raw materials.
		$stockItemConditions=[
			'StockItem.warehouse_id'=>$warehouseId,
		];
		if (empty($finishedProductsForEdit)){
			$stockItemConditions['StockItem.stockitem_creation_date <']=$salesDatePlusOne;
		}
		$stockItemsForSale=$this->StockItem->find('all',[
			'fields'=>['StockItem.product_id','StockItem.raw_material_id'],
			'conditions'=>$stockItemConditions,
			'recursive'=>-1,
		]);
		$productsWithStockItems=[];
		foreach ($stockItemsForSale as $stockItemForSale){
			$productId=$stockItemForSale['StockItem']['product_id'];
			if (!in_array($productId,$productsWithStockItems)){
				$productsWithStockItems[]=$productId;
			}
			$rawMaterialId=$stockItemForSale['StockItem']['raw_material_id'];
			if (empty($rawMaterialId)){
				continue;
			}
			if (!array_key_exists($productId,$saldoByProduct)){
				$saldoByProduct[$productId]=[
					'total'=>0,
					'rawMaterials'=>[],
				];
			}
			if (!array_key_exists($rawMaterialId,$saldoByProduct[$productId]['rawMaterials'])){
				$saldoByProduct[$productId]['rawMaterials'][$rawMaterialId]=[
					'1'=>0,
					'2'=>0,
					'3'=>0,
				];
			}
		}

		foreach ($productsAll as $product){
			$productId=$product['Product']['id'];
			$boolShowProduct=false;
			if (in_array($productId,$finishedProductsForEdit)){
				$boolShowProduct=true;
			}
			elseif ($boolIncludeProductsWithoutStock && in_array($productId,$productsWithStockItems)){
				$boolShowProduct=true;
			}
			elseif (!$boolIncludeProductsWithoutStock && array_key_exists($productId,$saldoByProduct) && $saldoByProduct[$productId]['total'] != 0){
				$boolShowProduct=true;
			}
			if ($boolShowProduct){
				$products[$productId]=substr($product['Product']['name'],0,28).(strlen($product['Product']['name'])>28?"...":"");
				if (!empty($saldoByProduct[$productId]['rawMaterials'])){
					if (!array_key_exists($productId,$rawMaterialsAvailablePerFinishedProduct)){
						$rawMaterialsAvailablePerFinishedProduct[$productId]=[];
					}
					foreach ($saldoByProduct[$productId]['rawMaterials'] as $rawMaterialId=>$saldoPerRawMaterial){
						// first build list of general raw material ids
						if (!in_array($rawMaterialId,$rawMaterialIds)){
							$rawMaterialIds[]=$rawMaterialId;
						}
						$rawMaterialsAvailablePerFinishedProduct[$productId][$rawMaterialId]=$saldoPerRawMaterial;
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
