<?php
App::uses('AppModel', 'Model');

/**
 * StockItem Model
 *
 * @property StockMovement $StockMovement
 * @property ProductType $ProductType
 * @property ProductionResultCode $ProductionResultCode
 * @property ProductType $ProductType
 * @property ProductionMovement $ProductionMovement
 */
class StockItem extends AppModel {
	
	function getInventoryTotals($productCategoryId,$productTypeIds,$warehouseId=0){
		//echo "warehouse id is ".$warehouseId."<br/>";
		return $this->getInventoryTotalsByDate($productCategoryId,$productTypeIds,date('Y-m-d'),$warehouseId);
	}
	
	function getInventoryTotalsByDate($productCategoryId,$productTypeIds,$inventoryDate,$warehouseId=0){
		//echo "inventory date is ".$inventoryDate."<br/>";
		//echo "warehouse id is ".$warehouseId."<br/>";
		$productsArray=array();
		if ($productCategoryId==CATEGORY_PRODUCED){
			foreach ($productTypeIds as $productTypeId){
				//echo "productTypeId is ".$productTypeId."<br/>";
				$productsOfProductType=$this->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
				//pr($productsOfProductType);
				if (!empty($productsOfProductType)){
					foreach ($productsOfProductType as $retrievedProduct){
						//echo "iteration of products coming out of getInventoryItems: retrievedProduct<br/>";
						//pr($retrievedProduct);
						$thisProductArray=array();
						$thisProductArray['Product']['id']=$retrievedProduct['Product']['id'];
						$thisProductArray['StockItem']['product_id']=$retrievedProduct['Product']['id'];
						//$thisProductArray['StockItem']['production_result_code_id']=$retrievedProduct['StockItem']['production_result_code_id'];
						$thisProductArray['StockItem']['production_result_code_id']=$retrievedProduct['ProductionResultCode']['id'];
						//$thisProductArray['StockItem']['raw_material_id']=$retrievedProduct['StockItem']['id'];
						$thisProductArray['StockItem']['raw_material_id']=$retrievedProduct['RawMaterial']['id'];
						$thisProductArray['RawMaterial']['id']=$retrievedProduct['RawMaterial']['id'];
						$thisProductArray['RawMaterial']['name']=$retrievedProduct['RawMaterial']['name'];
						$thisProductArray['Product']['name']=$retrievedProduct['Product']['name'];
						$thisProductArray['ProductionResultCode']['code']=$retrievedProduct['ProductionResultCode']['code'];
						//switch ($retrievedProduct['StockItem']['production_result_code_id']){
						switch ($retrievedProduct['ProductionResultCode']['id']){
							case PRODUCTION_RESULT_CODE_A:
								$thisProductArray['Product']['inventory_total']=$retrievedProduct['0']['Remaining_A'];
								break;
							case PRODUCTION_RESULT_CODE_B:
								$thisProductArray['Product']['inventory_total']=$retrievedProduct['0']['Remaining_B'];
								break;
							case PRODUCTION_RESULT_CODE_C:
								$thisProductArray['Product']['inventory_total']=$retrievedProduct['0']['Remaining_C'];
								break;
						}
						$thisProductArray['0']['Remaining_A']=$retrievedProduct['0']['Remaining_A'];
						$thisProductArray['0']['Remaining_B']=$retrievedProduct['0']['Remaining_B'];
						$thisProductArray['0']['Remaining_C']=$retrievedProduct['0']['Remaining_C'];
						$thisProductArray['0']['Remaining']=$retrievedProduct['0']['Remaining_A']+$retrievedProduct['0']['Remaining_B']+$retrievedProduct['0']['Remaining_C'];
						$productsArray[]=$thisProductArray;
					}
				}
			}
			return $productsArray;
		}
		else {
			$productsArray=array();
			foreach ($productTypeIds as $productTypeId){
				//echo "productTypeId is ".$productTypeId."<br/>";
				$productsOfProductType=$this->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
				//pr($productsOfProductType);
				foreach ($productsOfProductType as $retrievedProduct){
					//echo "iteration of products coming out of getInventoryItems: retrievedProduct<br/>";
					//pr($retrievedProduct);
					$thisProductArray=array();
					$thisProductArray['Product']['id']=$retrievedProduct['Product']['id'];
					$thisProductArray['StockItem']['product_id']=$retrievedProduct['Product']['id'];
					$thisProductArray['Product']['name']=$retrievedProduct['Product']['name'];
					//$thisProductArray['ProductionResultCode']['code']=$retrievedProduct['ProductionResultCode']['code'];
					$thisProductArray['0']['inventory_total']=$retrievedProduct['0']['Remaining'];
					$thisProductArray['0']['Remaining']=$retrievedProduct['0']['Remaining'];
					//echo "processed product array<br/>";
					//pr($thisProductArray);
					$productsArray[]=$thisProductArray;
				}
			}
			//pr($productsArray);
			return $productsArray;
		}	
	}
	
	function getInventoryItems($productTypeId,$inventoryDate,$warehouseId=0,$boolQuantitiesAtCurrentDate=false){
	  //echo "inventory date is ".$inventoryDate."<br/>";
    //echo "warehouse id is ".$warehouseId."<br/>";
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		//echo "inventoryDatePlusOne is ".$inventoryDatePlusOne."<br/>";

		$this->recursive=-1;
    $productModel=ClassRegistry::init('Product');
    $productIds=$productModel->find('list',array(
      'fields'=>array('Product.id'),
      'conditions'=>array(
        'Product.product_type_id'=>$productTypeId,
      ),
    ));
    
		$conditions=array(
		  'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
		  'StockItem.product_id'=> $productIds,
      'StockItem.bool_active'=> true,
		);
    
		if ($warehouseId>0){
			$conditions[]=array('StockItem.warehouse_id'=>$warehouseId,);
		}
		switch ($productTypeId){
			case PRODUCT_TYPE_PREFORMA:
				$preformaCount=	$this->find('count', array(
					'fields'=>array(
						'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
					),
					'conditions' => $conditions,
					'group'=>'StockItem.product_id',
				));
				$this->recursive=-1;
				$preformas = $this->find('all',array(
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
					'group'=>'StockItem.product_id',
					'limit'=>$preformaCount,
				));
        //pr($preformas[0]);
        usort($preformas,array($this,'sortByProductName'));
				for ($i=0;$i<count($preformas);$i++){
					$stockItemConditions=array(
            'StockItem.product_id'=>$preformas[$i]['Product']['id'],
            'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
            'StockItem.bool_active'=> true,
          );
					if ($warehouseId>0){
						$stockItemConditions[]=array('StockItem.warehouse_id'=>$warehouseId,);
					}
					$allStockItems=$this->find('all',array(
						'fields'=>array('StockItem.id'),
						'conditions'=>$stockItemConditions,
					));
					$totalStockInventoryDate=0;
					$totalValueInventoryDate=0;
					if (count($allStockItems)>0){
						$lastStockItemLog=array();
						foreach ($allStockItems as $stockitem){				
							$this->StockItemLog->recursive=-1;
							$stockItemLogConditions=array(
								'StockItemLog.stockitem_id'=>$stockitem['StockItem']['id'],
							);
							if (!$boolQuantitiesAtCurrentDate){
								$stockItemLogConditions[]=array('StockItemLog.stockitem_date <='=>$inventoryDatePlusOne);	
							}
							if ($warehouseId>0){
								$stockItemLogConditions[]=array('StockItemLog.warehouse_id'=>$warehouseId,);
							}
							//pr($stockItemLogConditions);
							$lastStockItemLog=$this->StockItemLog->find('first',array(
								'fields'=>array(
									'StockItemLog.product_quantity','StockItemLog.product_unit_price',
								),
								'conditions'=>$stockItemLogConditions,
								'order'=>'StockItemLog.id DESC',
							));
							if (count($lastStockItemLog)>0){
								if ($lastStockItemLog['StockItemLog']['product_quantity']>0){
									//pr($lastStockItemLog);
								}
								$totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
								$totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
							}
						}
					}
					$preformas[$i][0]['Remaining']=$totalStockInventoryDate;
					$preformas[$i][0]['Saldo']=$totalValueInventoryDate;
				}
			
				$products=$preformas;
				break;
			case PRODUCT_TYPE_BOTTLE:
        
				$bottleCount =$this->find('count', array(
					'fields'=>array(
						'SUM(StockItem.remaining_quantity) AS Remaining',
					),
					'conditions' => $conditions,
					
					'group'=>'StockItem.product_id,StockItem.raw_material_id',
				));
				$productos = $this->find('all',array(
					'fields'=>array(
						'SUM(StockItem.remaining_quantity) AS Remaining',
						'SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo'
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
							'fields'=>array('RawMaterial.id','RawMaterial.name'),
						),
					),
					'group'=>'StockItem.product_id,StockItem.raw_material_id',
					'limit'=>$bottleCount,
				));
        //pr($productos[0]);
        usort($productos,array($this,'sortByRawMaterialNameFinishedProductName'));
        
				// now overwrite based on StockItemLogs
				for ($i=0;$i<count($productos);$i++){
          if ($productos[$i][0]['Remaining']>0){
            for ($productionresultcode=1;$productionresultcode<4;$productionresultcode++){
              $stockItemConditions=array(
                'StockItem.product_id'=>$productos[$i]['Product']['id'],
                'StockItem.production_result_code_id'=>$productionresultcode,
                'StockItem.raw_material_id'=>$productos[$i]['RawMaterial']['id'],
                'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
                'StockItem.bool_active'=> true,
              );
              if ($warehouseId>0){
                $stockItemConditions[]=array('StockItem.warehouse_id'=>$warehouseId,);
              }
              $allStockItems=$this->find('all',array(
                'fields'=>array('StockItem.id'),
                'conditions'=>$stockItemConditions,
              ));
              $totalStockInventoryDate=0;
              $totalValueInventoryDate=0;
              if (count($allStockItems)>0){
                $lastStockItemLog=array();
                
                foreach ($allStockItems as $stockitem){	
                  $stockItemLogConditions=array(
                    'StockItemLog.stockitem_id'=>$stockitem['StockItem']['id'],									
                  );
                  if (!$boolQuantitiesAtCurrentDate){
                    $stockItemLogConditions[]=array('StockItemLog.stockitem_date <'=>$inventoryDatePlusOne);	
                  }
                  if ($warehouseId>0){
                    $stockItemLogConditions[]=array('StockItemLog.warehouse_id'=>$warehouseId,);
                  }
                  $this->StockItemLog->recursive=-1;
                  $lastStockItemLog=$this->StockItemLog->find('first',array(
                    'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
                    'conditions'=>$stockItemLogConditions,
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
					else {
						$productos[$i][0]['Remaining_A']=0;
						$productos[$i][0]['Saldo_A']=0;
						$productos[$i][0]['Remaining_B']=0;
						$productos[$i][0]['Saldo_B']=0;
						$productos[$i][0]['Remaining_C']=0;
						$productos[$i][0]['Saldo_C']=0;
					}
				}					
			
				$products=$productos;
				//echo "here are the products delivered by getInventoryItems<br/>";
				//pr($products);
				break;
			case PRODUCT_TYPE_CAP:
				$taponesCount= $this->find('count', array(
					'fields'=>array(
						'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
					),
					'conditions' => $conditions,
					'group'=>'StockItem.product_id',
				));
				$this->recursive=-1;
				$tapones = $this->find('all',array(
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
					'group'=>'StockItem.product_id',
					'limit'=>$taponesCount,
				));
				//pr($tapones[0]);
        usort($tapones,array($this,'sortByProductName'));
				for ($i=0;$i<count($tapones);$i++){
					$stockItemConditions=array(
            'StockItem.product_id'=>$tapones[$i]['Product']['id'],
            'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
            'StockItem.bool_active'=> true,
          );
					if ($warehouseId>0){
						$stockItemConditions[]=array('StockItem.warehouse_id'=>$warehouseId,);
					}
					$allStockItems=$this->find('all',array(
						'fields'=>array('StockItem.id'),
						'conditions'=>$stockItemConditions,
					));
					
					$totalStockInventoryDate=0;
					$totalValueInventoryDate=0;
					if (count($allStockItems)>0){
						$lastStockItemLog=array();
						foreach ($allStockItems as $stockitem){		
							$this->StockItemLog->recursive=-1;
							$stockItemLogConditions=array(
								'StockItemLog.stockitem_id'=>$stockitem['StockItem']['id']
							);
							if (!$boolQuantitiesAtCurrentDate){
								$stockItemLogConditions[]=array('StockItemLog.stockitem_date <='=>$inventoryDatePlusOne);
							}
							if ($warehouseId>0){
								$stockItemLogConditions[]=array('StockItemLog.warehouse_id'=>$warehouseId,);
							}
							$lastStockItemLog=$this->StockItemLog->find('first',array(
								'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price'),
								'conditions'=>$stockItemLogConditions,
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
				$products=$tapones;
				break;		
			default:
				$products=array();
				break;					
		}
		//echo "products coming out of getInventoryItems<br/>";
		//pr($products);
		return $products;	
	}
  
  public function sortByProductName($firstTerm,$secondTerm){
		return ($firstTerm['Product']['name'] < $secondTerm['Product']['name']) ? -1 : 1;
	}
	
  public function sortByRawMaterialNameFinishedProductName($firstTerm,$secondTerm){
		if (!empty($firstTerm['RawMaterial']) && !empty($secondTerm['RawMaterial'])){
			if (!empty($firstTerm['RawMaterial']['name']) && !empty($secondTerm['RawMaterial']['name'])){
				return ($firstTerm['RawMaterial']['name'] < $secondTerm['RawMaterial']['name']) ? -1 : 1;
			}
			else if (!empty($firstTerm['RawMaterial']['name'])){
				return 1;
			}
			else if (!empty($secondTerm['RawMaterial']['name'])){
				return -1;
			}				
			else {
				return ($firstTerm['FinishedProduct']['name'] < $secondTerm['FinishedProduct']['name']) ? -1 : 1;
			}
		}
		elseif (!empty($firstTerm['RawMaterial'])){
			if (!empty($firstTerm['RawMaterial']['name'])){
				return 1;
			}
			else {
				return ($firstTerm['FinishedProduct']['name'] < $secondTerm['FinishedProduct']['name']) ? -1 : 1;
			}
		}
		elseif (!empty($secondTerm['RawMaterial'])){
			if (!empty($secondTerm['RawMaterial']['name'])){
				return -1;
			}
			else {
				return ($firstTerm['FinishedProduct']['name'] < $secondTerm['FinishedProduct']['name']) ? -1 : 1;
			}
		}
		else {
			return ($firstTerm['FinishedProduct']['name'] < $secondTerm['FinishedProduct']['name']) ? -1 : 1;
		}	
	}
	
  function getInventoryFinishedProduct($finishedProductId,$rawMaterialId,$inventoryDate,$warehouseId=0){
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		
		$conditions=array(
			'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
			'StockItem.product_id'=> $finishedProductId,
      'StockItem.raw_material_id'=> $rawMaterialId,
      'StockItem.raw_material_id'=> $rawMaterialId,
		);
		if ($warehouseId>0){
			$conditions[]=array('StockItem.warehouse_id'=>$warehouseId);
		}
		$this->recursive=-1;
    
    $stockItems=array();
    
    // GET ALL STOCKITEMS THAT WERE CREATED ON OR BEFORE THE INVENTORY DATE
    $stockItemIds=$this->find('list', array(
      'fields'=>array('StockItem.id'),
      'conditions' => $conditions,
    ));
    //foreach ($stockItemIds as $key=>$value){
    //  pr($value);
    //}
    
    // NOW FOR EACH STOCKITEM CHECK WHAT WAS THE LAST STOCKITEMLOG ON OR BEFORE THE INVENTORY DATE
    if (!empty($stockItemIds)){
      foreach ($stockItemIds as $key=>$stockItemId){
        $stockItemLogModel=ClassRegistry::init('StockItemLog');
        $stockItemLogModel->recursive=-1;
        $stockItemLogConditions=array(
          'StockItemLog.stockitem_id'=>$stockItemId,		
          'StockItemLog.stockitem_date <'=>$inventoryDatePlusOne,
        );
        if ($warehouseId>0){
          $stockItemLogConditions[]=array('StockItemLog.warehouse_id'=>$warehouseId,);
        }
        $this->StockItemLog->recursive=-1;
        $lastStockItemLog=$this->StockItemLog->find('first',array(
          'fields'=>array('StockItemLog.product_quantity','StockItemLog.product_unit_price','StockItemLog.production_result_code_id'),
          'conditions'=>$stockItemLogConditions,
          'order'=>'StockItemLog.id DESC',
        ));
      
        if (!empty($lastStockItemLog)){
          if ($lastStockItemLog['StockItemLog']['product_quantity']>0){
            $stockItems[]=array(
              'stock_item_id'=>$stockItemId,
              'product_quantity'=>$lastStockItemLog['StockItemLog']['product_quantity'],
              'product_unit_price'=>$lastStockItemLog['StockItemLog']['product_unit_price'],
              'production_result_code_id'=>$lastStockItemLog['StockItemLog']['production_result_code_id'],
            );
          }
        }
      }
      
      //NOW THAT THE RELEVANT STOCK ITEMS ARE IN, GET THE REST OF THE DATA: PRODUCTION RUN, ENTRY
      if (!empty($stockItems)){
        $productionMovementModel=ClassRegistry::init('ProductionMovement');
        $productionMovementModel->recursive=-1;
        $stockMovementModel=ClassRegistry::init('StockMovement');
        $stockMovementModel->recursive=-1;
        for ($i=0;$i<count($stockItems);$i++){
          //get the production run data
          $finishedProductProductionMovements=$productionMovementModel->find('all',array(
            'fields'=>array(
              'ProductionMovement.id','ProductionMovement.product_unit_price','ProductionMovement.production_result_code_id'
            ),
            'conditions'=>array(
              'ProductionMovement.stockitem_id'=>$stockItems[$i]['stock_item_id'],
              'ProductionMovement.product_quantity >'=>0,
            ),
            'contain'=>array(
              'ProductionRun'=>array(
                'fields'=>array(
                  'ProductionRun.id','ProductionRun.production_run_code','ProductionRun.production_run_date'
                ),
              ),
            ),
          ));
          if (!empty($finishedProductProductionMovements)){
            $stockItems[$i]['ProductionRun']=$finishedProductProductionMovements[0]['ProductionRun'];
            $rawMaterialProductionMovements=$productionMovementModel->find('all',array(
              'fields'=>array(
                'ProductionMovement.id','ProductionMovement.stockitem_id','ProductionMovement.product_unit_price','ProductionMovement.production_result_code_id'
              ),
              'conditions'=>array(
                'ProductionMovement.bool_input'=>true,
                'ProductionMovement.production_run_id'=>$finishedProductProductionMovements[0]['ProductionRun']['id'],
              )
            ));
            if (!empty($rawMaterialProductionMovements)){
              $rawMaterialStockItemIds=array();
              foreach ($rawMaterialProductionMovements as $productionMovement){
                $rawMaterialStockItemIds[]=$productionMovement['ProductionMovement']['stockitem_id'];
              }
              $rawMaterialStockMovements=$stockMovementModel->find('all',array(
                'fields'=>array(
                  'StockMovement.id','StockMovement.stockitem_id','StockMovement.product_unit_price','StockMovement.production_result_code_id'
                ),
                'conditions'=>array(
                  'StockMovement.bool_input'=>true,
                  'StockMovement.stockitem_id'=>$rawMaterialStockItemIds,
                ),
                'contain'=>array(
                  'Order'=>array(
                    'fields'=>array(
                      'Order.order_date','Order.order_code',
                    ),
                    'ThirdParty'=>array(
                      'fields'=>array(
                        'ThirdParty.id','ThirdParty.company_name',
                      ),
                    ),
                  ),
                ),
              ));
              if (!empty($rawMaterialStockMovements)){
                $entriesArray=array();
                foreach ($rawMaterialStockMovements as $rawMaterialStockMovement){
                  $entriesArray[]=$rawMaterialStockMovement['Order'];
                }
                $stockItems[$i]['Entry']=$entriesArray;
              }
            }
          }
        }
      }
    } 
    
		//pr($stockItems);
		return $stockItems;	
	}
  
	function getRawMaterialsForProductionRun($productid=null,$quantityneeded=0,$productionrundate){
		$rawMaterialsComplete=false;
		$usedRawMaterials=null;
		
		$rawMaterials = $this->find('all', array(
			'fields' => array(
				'StockItem.id',
				'StockItem.name',
				'StockItem.product_unit_price',
				'StockItem.remaining_quantity',
			),
			'conditions' => array(
				'StockItem.product_id'=>$productid,
				'StockItem.remaining_quantity >'=>'>0',
				'StockItem.stockitem_creation_date <='=>$productionrundate,
			),
		));
		//pr($rawMaterials);
		for ($i=0;$i<sizeof($rawMaterials);$i++){
			$rawid=$rawMaterials[$i]['StockItem']['id'];
			$rawname=$rawMaterials[$i]['StockItem']['name'];
			$rawunitprice=$rawMaterials[$i]['StockItem']['product_unit_price'];
			$quantitypresent=$rawMaterials[$i]['StockItem']['remaining_quantity'];
			if ($quantityneeded>$quantitypresent){
				// consume all the materials in the present stockitem and move to the next
				$quantityused=$quantitypresent;
				$quantityremaining=0;
				$quantityneeded-=$quantitypresent;
			}
			else {
				// consume the necessary materials and indicate the raw materials are complete
				$quantityused=$quantityneeded;
				$quantityremaining=$quantitypresent-$quantityneeded;
				$quantityneeded=0;
				$rawMaterialsComplete = true;
			}
			$usedRawMaterials[$i]['id']=$rawid;
			$usedRawMaterials[$i]['name']=$rawname;
			$usedRawMaterials[$i]['unit_price']=$rawunitprice;
			
			$usedRawMaterials[$i]['quantity_present']=$quantitypresent;
			$usedRawMaterials[$i]['quantity_used']=$quantityused;
			$usedRawMaterials[$i]['quantity_remaining']=$quantityremaining;
			if ($rawMaterialsComplete){
				break;
			}
		}
		return $usedRawMaterials;
	}
	
	function getFinishedMaterialsForSale($productid,$resultcodeid,$quantityneeded,$rawmaterialid,$saledate, $warehouseId, $orderby="ASC"){
		$finishedMaterialsComplete=false;
		$usedFinishedMaterials=null;
		
		$conditions=array(
			'StockItem.product_id'=>$productid,
			'StockItem.remaining_quantity >'=>'>0',
			'StockItem.production_result_code_id'=>$resultcodeid,
			'StockItem.raw_material_id'=>$rawmaterialid,
			'StockItem.stockitem_creation_date <='=>$saledate,
		);
		if (!empty($warehouseId)){
			$conditions[]=array(
				'StockItem.warehouse_id'=>$warehouseId,
			);
		}
		$finishedMaterialsType = $this->find('all', array(
			'fields' => array(
				'StockItem.id',
				'StockItem.name',
				'StockItem.product_unit_price',
				'StockItem.remaining_quantity',
			),
			'conditions' => $conditions,
			'order'=>'StockItem.stockitem_creation_date '.$orderby
		));
		for ($i=0;$i<sizeof($finishedMaterialsType);$i++){
			$finishedid=$finishedMaterialsType[$i]['StockItem']['id'];
			$finishedname=$finishedMaterialsType[$i]['StockItem']['name'];
			$finishedunitprice=$finishedMaterialsType[$i]['StockItem']['product_unit_price'];
			$quantitypresent=$finishedMaterialsType[$i]['StockItem']['remaining_quantity'];
			if ($quantityneeded>$quantitypresent){
				// consume all the materials in the present stockitem and move to the next
				$quantityused=$quantitypresent;
				$quantityremaining=0;
				$quantityneeded-=$quantitypresent;
			}
			else {
				// consume the necessary materials and indicate the raw materials are complete
				$quantityused=$quantityneeded;
				$quantityremaining=$quantitypresent-$quantityneeded;
				$quantityneeded=0;
				$finishedMaterialsComplete = true;
			}
			$usedFinishedMaterials[$i]['id']=$finishedid;
			$usedFinishedMaterials[$i]['name']=$finishedname;
			
			$usedFinishedMaterials[$i]['unit_price']=$finishedunitprice;
			
			$usedFinishedMaterials[$i]['quantity_present']=$quantitypresent;
			$usedFinishedMaterials[$i]['quantity_used']=$quantityused;
			$usedFinishedMaterials[$i]['quantity_remaining']=$quantityremaining;
			if ($finishedMaterialsComplete){
				break;
			}
		}
		return $usedFinishedMaterials;
	}
	
	function getOtherMaterialsForSale($productid=null,$quantityneeded=0,$saledate,$warehouseId=0,$orderby="ASC"){
		$conditions=array(
			'StockItem.product_id'=>$productid,
			'StockItem.remaining_quantity >'=>'>0',
			'StockItem.stockitem_creation_date <='=>$saledate,
		);
		if (!empty($warehouseId)){
			$conditions[]=array(
				'StockItem.warehouse_id'=>$warehouseId,
			);
		}
		$otherMaterialsComplete=false;
		$usedOtherMaterials=array();
		$otherMaterialsItems = $this->find('all', array(
			'fields' => array(
				'StockItem.id',
				'StockItem.name',
				'StockItem.product_unit_price',
				'StockItem.remaining_quantity',
			),
			'conditions' => $conditions,
			'order'=>'StockItem.stockitem_creation_date '.$orderby,
		));
		for ($i=0;$i<sizeof($otherMaterialsItems);$i++){
			$otherid=$otherMaterialsItems[$i]['StockItem']['id'];
			$othername=$otherMaterialsItems[$i]['StockItem']['name'];
			$otherunitprice=$otherMaterialsItems[$i]['StockItem']['product_unit_price'];
			$quantitypresent=$otherMaterialsItems[$i]['StockItem']['remaining_quantity'];
			if ($quantityneeded>$quantitypresent){
				// consume all the materials in the present stockitem and move to the next
				$quantityused=$quantitypresent;
				$quantityremaining=0;
				$quantityneeded-=$quantitypresent;
			}
			else {
				// consume the necessary materials and indicate the raw materials are complete
				$quantityused=$quantityneeded;
				$quantityremaining=$quantitypresent-$quantityneeded;
				$quantityneeded=0;
				$otherMaterialsComplete = true;
			}
			$usedOtherMaterials[$i]['id']=$otherid;
			$usedOtherMaterials[$i]['name']=$othername;
			$usedOtherMaterials[$i]['unit_price']=$otherunitprice;
			
			$usedOtherMaterials[$i]['quantity_present']=$quantitypresent;
			$usedOtherMaterials[$i]['quantity_used']=$quantityused;
			$usedOtherMaterials[$i]['quantity_remaining']=$quantityremaining;
			if ($otherMaterialsComplete){
				break;
			}
		}
		return $usedOtherMaterials;
	}
	
	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		/*
		'stock_movement_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		*/
		'product_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		/*
		'product_unit_price' => array(
			'numeric' => array(
				'rule' => array('decimal',8),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		*/
		'original_quantity' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'remaining_quantity' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'production_result_code_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		
		'Product' => array(
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'type'=>'right outer',
			
		),
		'ProductionResultCode' => array(
			'className' => 'ProductionResultCode',
			'foreignKey' => 'production_result_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'RawMaterial' => array(
			'className' => 'Product',
			'foreignKey' => 'raw_material_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Warehouse' => array(
			'className' => 'Warehouse',
			'foreignKey' => 'warehouse_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	public $hasMany = array(
		'ProductionMovement' => array(
			'className' => 'ProductionMovement',
			'foreignKey' => 'stockitem_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'StockMovement' => array(
			'className' => 'StockMovement',
			'foreignKey' => 'stockitem_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'StockItemLog' => array(
			'className' => 'StockItemLog',
			'foreignKey' => 'stockitem_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

}
