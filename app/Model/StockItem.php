<?php
App::uses('AppModel', 'Model');

class StockItem extends AppModel {
	
  public function getStockItemById($stockItemId){
    return $this->find('first',[
      'conditions'=>[
        'StockItem.id'=>$stockItemId,
      ],
      'recursive'=>-1,
    ]);
  }
  
   function getResultCodes($productId)
  {
	$codes= $this->Warehouse->query("select distinct  production_result_code_id from `orna1114_ornasa`.`stock_movements` where production_result_code_id is not null;");
 
	return $codes;
 
	  
  }
     
  function getSaldo($productId='0',$warehouseId,$dateInit,$rawMaterialId='0',$returnv=1)
  {
	  
	$result_initial= $this->Warehouse->query("CALL `orna1114_ornasa`.`sp_saldo`($productId,$warehouseId,'$dateInit','$rawMaterialId','$returnv');");
 
	if($rawMaterialId>0){

	$result['total']=(isset($result_initial[0][0]['Remaining'])?$result_initial[0][0]['Remaining']:0);
	$result[1]=(isset($result_initial[0][0]['Remaining_A'])?$result_initial[0][0]['Remaining_A']:0);
	$result[2]=(isset($result_initial[0][0]['Remaining_B'])?$result_initial[0][0]['Remaining_B']:0);
	$result[3]=(isset($result_initial[0][0]['Remaining_C'])?$result_initial[0][0]['Remaining_C']:0);
	}
	else
	{
    $result[0]=(isset($result_initial[0][0]['Remaining'])?$result_initial[0][0]['Remaining']:0);
	$result['total']=(isset($result_initial[0][0]['Remaining'])?$result_initial[0][0]['Remaining']:0);
		
	}
	    
	return $result;
 
	  
  }

	
  function getInventary($type,$date,$warehouseId)
  {
	 $result= $this->Warehouse->query("CALL `orna1114_ornasa`.`sp_inventary`($type,'$date','$warehouseId');");
	 $array_result=array();
		
	if(is_array($result))
	{		
 	    $array_result = array_map(function ($elem) {
		 
		$elem=$elem['0'];
 
		$_products= array(
					"Product"=>array("name"=>(isset($elem['name'])?$elem['name']:''),
								"id"=>(isset($elem['id'])?$elem['id']:''),"packaging_unit"=>(isset($elem['packaging_unit'])?$elem['packaging_unit']:'0'),"product_type_id"=>(isset($elem['product_type_id'])?$elem['product_type_id']:'0')),
								
					"RawMaterial"=>array("name"=>(isset($elem['nameraw'])?$elem['nameraw']:''),
								"id"=>(isset($elem['idraw'])?$elem['idraw']:''),
								"abbreviation"=>(isset($elem['abbreviationraw'])?$elem['abbreviationraw']:''),
								),			
					"ProductionResultCode"=>array("id"=>(isset($elem['idprc'])?$elem['idprc']:''),
					"code"=>(isset($elem['codeprc'])?$elem['codeprc']:''),
								 ),
					'0'=>array(
							"Saldo"=>(isset($elem['Saldo'])?$elem['Saldo']:'0'),
							"Remaining"=>(isset($elem['Remaining'])?$elem['Remaining']:'0'),
							"Remaining_A"=>(isset($elem['Remaining_A'])?$elem['Remaining_A']:'0'),
							"Remaining_B"=>(isset($elem['Remaining_B'])?$elem['Remaining_B']:'0'),
							"Remaining_C"=>(isset($elem['Remaining_C'])?$elem['Remaining_C']:'0'),
							"Saldo_A"=>(isset($elem['Saldo_A'])?$elem['Saldo_A']:'0'),
							"Saldo_B"=>(isset($elem['Saldo_B'])?$elem['Saldo_B']:'0'),
							"Saldo_C"=>(isset($elem['Saldo_C'])?$elem['Saldo_C']:'0'),
					),
		);
		
		return $_products;
}, $result); 
 	 
	}
else 
{

}	
	return $array_result;
  }
  
    	function getInventoryItems($productTypeId,$inventoryDate,$warehouseId=0,$boolQuantitiesAtCurrentDate=false){
    
		switch ($productTypeId){
			case PRODUCT_TYPE_PREFORMA:
		           
				$preformas=$this->getInventary($productTypeId,"$inventoryDate","$warehouseId");  
			    usort($preformas,[$this,'sortByProductName']);
				$products=$preformas;
				
				break;
			case PRODUCT_TYPE_BOTTLE:
         
				$products=$this->getInventary($productTypeId,"$inventoryDate","$warehouseId"); 
				 
     // pr($products[0]);exit;
                usort($products,[$this,'sortByRawMaterialNameFinishedProductName']);
       
		  
				break;
        
      case PRODUCT_TYPE_INJECTION_OUTPUT:
        //pr($conditions);
         $products=$this->getInventary($productTypeId,"$inventoryDate","$warehouseId");  
		 usort($products,[$this,'sortByRawMaterialNameFinishedProductName']);		 
        break;
        
			case PRODUCT_TYPE_CAP:
 
				$products=$this->getInventary($productTypeId,"$inventoryDate","$warehouseId");  
				usort($products,array($this,'sortByProductName'));
				break;		
			default:
                
				$products=$this->getInventary($productTypeId,"$inventoryDate","$warehouseId");  
				 usort($products,[$this,'sortByProductName']);
				//$products=$consumibleProducts;
				break;					
		}
		//echo "products coming out of getInventoryItems<br/>";
		//pr($products);
		return $products;	
	}
  
  
  
  
	function getInventoryTotals($productCategoryId,$productTypeIds,$warehouseId=0){
		//echo "warehouse id is ".$warehouseId."<br/>";
		return $this->getInventoryTotalsByDate($productCategoryId,$productTypeIds,date('Y-m-d'),$warehouseId);
	}
	
  function getStockItemsByProduct($productTypeIds,$inventoryDate,$warehouseId){
    $inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
    $plantId = $this->Warehouse->getPlantId($warehouseId);
    
    $products=[];
    foreach ($productTypeIds as $productTypeId){
      $productModel=ClassRegistry::init('Product');
      
      $productIds=$productModel->find('list',[
        'fields'=>['Product.id'],
        'conditions'=>['Product.product_type_id'=>$productTypeId],    
      ]);
      $fields=[
        'StockItem.id',
        'StockItem.product_unit_price',
        'StockItem.remaining_quantity',
        'StockItem.stockitem_creation_date',
      ];
      $conditions=[
        'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
        'StockItem.stockitem_depletion_date >'=> $inventoryDate,
        'StockItem.warehouse_id'=>$warehouseId,
        'StockItem.remaining_quantity > '=>0,
      ];
      
      if (!empty($productIds)){
        foreach ($productIds as $productId){
          //echo 'productId is '.$productId.'<br/>';
          $stockItemConditions=$conditions;
          $stockItemConditions['StockItem.product_id']=$productId;
          
          $stockItems = $this->find('all',[
            'fields'=>$fields,
            'conditions' => $stockItemConditions,
            'recursive'=>-1,
            'order'=>'StockItem.stockitem_creation_date ASC,StockItem.id ASC',
          ]);
          //pr($stockItems);
          if (!empty($stockItems)){
            $products[$productId]['StockItems']=$stockItems;
          }
        }
      } 
    }
    return $products;
  }
  
	function getInventoryTotalsByDate($productCategoryId,$productTypeIds,$inventoryDate,$warehouseId=0){
		//echo "inventory date is ".$inventoryDate."<br/>";
		//echo "warehouse id is ".$warehouseId."<br/>";
    $plantId = $this->Warehouse->getPlantId($warehouseId);
		$productsArray=[];
		if ($productCategoryId == CATEGORY_PRODUCED && $plantId == PLANT_SANDINO){
			foreach ($productTypeIds as $productTypeId){
				//echo "productTypeId is ".$productTypeId."<br/>";
				$productsOfProductType=$this->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
				//pr($productsOfProductType);
				if (!empty($productsOfProductType)){
          //echo "productTypeId is ".$productTypeId."<br/>";  
					foreach ($productsOfProductType as $retrievedProduct){
						//echo "iteration of products coming out of getInventoryItems: retrievedProduct<br/>";
						
						$thisProductArray=[];
						$thisProductArray['Product']['id']=$retrievedProduct['Product']['id'];
						$thisProductArray['StockItem']['product_id']=$retrievedProduct['Product']['id'];
						//$thisProductArray['StockItem']['production_result_code_id']=$retrievedProduct['StockItem']['production_result_code_id'];
						$thisProductArray['StockItem']['production_result_code_id']=$retrievedProduct['ProductionResultCode']['id'];
            if (empty($retrievedProduct['ProductionResultCode']['id'])){
             // pr($retrievedProduct);
            }
						//$thisProductArray['StockItem']['raw_material_id']=$retrievedProduct['StockItem']['id'];
						$thisProductArray['StockItem']['raw_material_id']=$retrievedProduct['RawMaterial']['id'];
						$thisProductArray['RawMaterial']['id']=$retrievedProduct['RawMaterial']['id'];
						$thisProductArray['RawMaterial']['name']=$retrievedProduct['RawMaterial']['name'];
            $thisProductArray['RawMaterial']['abbreviation']=$retrievedProduct['RawMaterial']['abbreviation'];
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
              case PRODUCTION_RESULT_CODE_MILL:
								$thisProductArray['Product']['inventory_total']=$retrievedProduct['0']['Remaining_Mill'];
								break;
						}
            switch ($productTypeId){
              case PRODUCT_TYPE_BOTTLE:
                $thisProductArray['0']['Remaining_A']=$retrievedProduct['0']['Remaining_A'];
                $thisProductArray['0']['Remaining_B']=$retrievedProduct['0']['Remaining_B'];
                $thisProductArray['0']['Remaining_C']=$retrievedProduct['0']['Remaining_C'];
                $thisProductArray['0']['Remaining']=$retrievedProduct['0']['Remaining_A']+$retrievedProduct['0']['Remaining_B']+$retrievedProduct['0']['Remaining_C'];
                break;
              case PRODUCT_TYPE_INJECTION_OUTPUT:{
                $thisProductArray['0']['Remaining_A']=$retrievedProduct['0']['Remaining_A'];
                $thisProductArray['0']['Remaining']=$retrievedProduct['0']['Remaining_A'];
              }  
            }
            
						$productsArray[]=$thisProductArray;
					}
				}
			}
			return $productsArray;
		}
		else {
			$productsArray=[];
			foreach ($productTypeIds as $productTypeId){
				//echo "productTypeId is ".$productTypeId."<br/>";
				$productsOfProductType=$this->getInventoryItems($productTypeId,$inventoryDate,$warehouseId,false);
				//pr($warehouseId);exit;
				foreach ($productsOfProductType as $retrievedProduct){
					//echo "iteration of products coming out of getInventoryItems: retrievedProduct<br/>";
					//pr($retrievedProduct);
					$thisProductArray=[];
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
	
	function getInventoryItemsPrev($productTypeId,$inventoryDate,$warehouseId=0,$boolQuantitiesAtCurrentDate=false){
		
    $inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		//echo "inventoryDatePlusOne is ".$inventoryDatePlusOne."<br/>";
         /*  $productTypeId=18;
		 $products=$this->exfunction($productTypeId,"$inventoryDate","$warehouseId");   
		  print_r($products);
		  exit; */
		  /* if($productTypeId==18)
			  $condition=['Product.id'=>128];
		  else */
			  $condition=['Product.product_type_id'=>$productTypeId];
		$this->recursive=-1;
		$productModel=ClassRegistry::init('Product');
		$productIds=$productModel->find('list',[
			'fields'=>['Product.id'],
			//'conditions'=>['Product.product_type_id'=>$productTypeId],    
			'conditions'=>$condition,    
		]);
    
		$conditions=[
		  'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
		  'StockItem.stockitem_depletion_date >'=> $inventoryDate,
		  'StockItem.product_id'=> $productIds,
		];
    $containProduced=[
      'Product'=>[
        'fields'=>['Product.name','Product.id','Product.packaging_unit','Product.product_type_id'],
      ],
      'ProductionResultCode'=>[
        'fields'=>['ProductionResultCode.code'],
      ],
      'RawMaterial'=>[
        'fields'=>['RawMaterial.name','RawMaterial.abbreviation'],
      ],
    ];
    $contain=['Product'=>[
        'fields'=>['Product.name','Product.id','Product.packaging_unit','Product.product_type_id'],
      ],
    ];
		if ($warehouseId>0){
			$conditions['StockItem.warehouse_id']=$warehouseId;
		}
		
			
		switch ($productTypeId){
			case PRODUCT_TYPE_PREFORMA:
				$preformaCount=	$this->find('count',[
					'fields'=>[
						'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
					],
					'conditions' => $conditions,
					'group'=>'StockItem.product_id',
				]);
				$this->recursive=-1;
        
        //20180515 REACTIVATED
        $preformas = $this->find('all',[
					'fields'=>[
						'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
					],
					'conditions' => $conditions,
					'contain'=>$contain,
					'group'=>'StockItem.product_id',
					'limit'=>$preformaCount,
				]);
        usort($preformas,[$this,'sortByProductName']);
        //pr($preformas[0]);
				for ($i=0;$i<count($preformas);$i++){
          $stockItemConditions=$conditions;
          $stockItemConditions['StockItem.product_id']=$preformas[$i]['Product']['id'];
					$allStockItems=$this->find('all',[
						'fields'=>['StockItem.id'],
						'conditions'=>$stockItemConditions,
            'recursive'=>-1,
					]);
					$totalStockInventoryDate=0;
					$totalValueInventoryDate=0;
          
					if (count($allStockItems)>0){
						foreach ($allStockItems as $stockItem){				
              $lastStockItemLog=$this->StockItemLog->getLastStockItemLog($stockItem['StockItem']['id'],$inventoryDate,$warehouseId,$boolQuantitiesAtCurrentDate=false);
							if (!empty($lastStockItemLog)){
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
        $bottleCount =$this->find('count', [
					'fields'=>array(
						'SUM(StockItem.remaining_quantity) AS Remaining',
					),
					'conditions' => $conditions,
					'group'=>'StockItem.product_id,StockItem.raw_material_id',
				]);
        
        //20180515 REACTIVATED
				$productos = $this->find('all',[
					'fields'=>[
						'SUM(StockItem.remaining_quantity) AS Remaining',
						'SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo'
					],
					'conditions' => $conditions,
					'contain'=>$containProduced,
					'group'=>'StockItem.product_id,StockItem.raw_material_id',
					'limit'=>$bottleCount,
				]);
        
        //pr($productos[0]);
        usort($productos,[$this,'sortByRawMaterialNameFinishedProductName']);
			
        
				// now overwrite based on StockItemLogs
				for ($i=0;$i<count($productos);$i++){
          for ($productionResultCode=1;$productionResultCode<4;$productionResultCode++){
            $stockItemConditions=$conditions;
            $stockItemConditions['StockItem.product_id']=$productos[$i]['Product']['id'];
            $stockItemConditions['StockItem.production_result_code_id']=$productionResultCode;
			if($productos[$i]['Product']['id']==30)
            $stockItemConditions['StockItem.raw_material_id']=$productos[$i]['RawMaterial']['id'];
            else 
			 $stockItemConditions['StockItem.raw_material_id']=$productos[$i]['RawMaterial']['id'];	
            $allStockItems=$this->find('all',[
              'fields'=>['StockItem.id'],
              'conditions'=>$stockItemConditions,
              'recursive'=>-1,
            ]);
			$this->StockItemLog->condi=$stockItemConditions;
            $totalStockInventoryDate=0;
            $totalValueInventoryDate=0;
            if (count($allStockItems)>0){
              foreach ($allStockItems as $stockItem){	
			        if($productos[$i]['Product']['id']==30)
					{
						file_put_contents("ultimoevaluado.log","evaluando ({$productos[$i]['Product']['id']}) el item ".$stockItem['StockItem']['id']."\n",FILE_APPEND);
						
					}
                $lastStockItemLog=$this->StockItemLog->getLastStockItemLog($stockItem['StockItem']['id'],$inventoryDate,$warehouseId,$boolQuantitiesAtCurrentDate=false);
							  if (!empty($lastStockItemLog)){
                  $totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
                  $totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
                }
              }
            }
            switch ($productionResultCode){
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
				$products=$productos;
				break;
        
      case PRODUCT_TYPE_INJECTION_OUTPUT:
	 
        //pr($conditions);
        $itemCount =$this->find('count', [
					'fields'=>[
						'SUM(StockItem.remaining_quantity) AS Remaining',
					],
					'conditions' => $conditions,
					'group'=>'StockItem.product_id,StockItem.raw_material_id',
				]);
        
        //20180515 REACTIVATED
				$productos = $this->find('all',[
					'fields'=>[
						'SUM(StockItem.remaining_quantity) AS Remaining',
						'SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo'
					],
					'conditions' => $conditions,
					'contain'=>$containProduced,
					'group'=>'StockItem.product_id,StockItem.raw_material_id',
					'limit'=>$itemCount,
				]);
        
        //pr($productos[0]);
        usort($productos,[$this,'sortByRawMaterialNameFinishedProductName']);
        //pr($productos);
				// now overwrite based on StockItemLogs
			
				for ($i=0;$i<count($productos);$i++){
          $injectionProductionResultCodes=[PRODUCTION_RESULT_CODE_A,PRODUCTION_RESULT_CODE_MILL];
          foreach ($injectionProductionResultCodes as $injectionProductionResultCode){
            $stockItemConditions=$conditions;
            $stockItemConditions['StockItem.product_id']=$productos[$i]['Product']['id'];
           // $stockItemConditions['StockItem.production_result_code_id']=$injectionProductionResultCode;
			
            //$stockItemConditions['StockItem.raw_material_id']=$productos[$i]['RawMaterial']['id'];
              
            $allStockItems=$this->find('all',[
              'fields'=>['StockItem.id'],
              'conditions'=>$stockItemConditions,
              'recursive'=>-1,
            ]);
            $totalStockInventoryDate=0;
            $totalValueInventoryDate=0;
				
            if (count($allStockItems)>0){
              foreach ($allStockItems as $stockItem){	
                $lastStockItemLog=$this->StockItemLog->getLastStockItemLog($stockItem['StockItem']['id'],$inventoryDate,$warehouseId,$boolQuantitiesAtCurrentDate=false);
							  if (!empty($lastStockItemLog)){
                  $totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
                  $totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
                }
              }
            }
			 
           // switch ($injectionProductionResultCode){
             // case PRODUCTION_RESULT_CODE_A:
               /*  $productos[$i][0]['Remaining_A']=$totalStockInventoryDate;
                $productos[$i][0]['Saldo_A']=$totalValueInventoryDate; */
				$productos[$i][0]['Remaining']=$totalStockInventoryDate;
                $productos[$i][0]['Saldo']=$totalValueInventoryDate;
              /*   break;
              case PRODUCTION_RESULT_CODE_MILL:
                $productos[$i][0]['Remaining_Mill']=$totalStockInventoryDate;
                $productos[$i][0]['Saldo_Mill']=$totalValueInventoryDate;
                break; */
            //}
          }    
				}					
			//print_r($productos);exit;
				$products=$productos;
        break;
        
			case PRODUCT_TYPE_CAP:
				$taponesCount= $this->find('count', [
					'fields'=>[
						'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
					],
					'conditions' => $conditions,
					'group'=>'StockItem.product_id',
				]);
				$this->recursive=-1;
        
        //20180515 REACTIVATED
              
				$tapones = $this->find('all',[
					'fields'=>[
						'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
					],
					'conditions' => $conditions,
					'contain'=>$contain,
					'group'=>'StockItem.product_id',
					'limit'=>$taponesCount,
				]);
        //pr($tapones[0]);
        usort($tapones,array($this,'sortByProductName'));
				for ($i=0;$i<count($tapones);$i++){
          $stockItemConditions=$conditions;
					$stockItemConditions['StockItem.product_id']=$tapones[$i]['Product']['id'];
					$allStockItems=$this->find('all',[
						'fields'=>['StockItem.id'],
						'conditions'=>$stockItemConditions,
            'recursive'=>-1,
					]);
					
					$totalStockInventoryDate=0;
					$totalValueInventoryDate=0;
				
					if (count($allStockItems)>0){
						foreach ($allStockItems as $stockItem){		
							$lastStockItemLog=$this->StockItemLog->getLastStockItemLog($stockItem['StockItem']['id'],$inventoryDate,$warehouseId,$boolQuantitiesAtCurrentDate=false);
							if (!empty($lastStockItemLog)){
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
		
        //if ($productTypeId === PRODUCT_TYPE_INJECTION_GRAIN){
        //  pr($conditions);
        //}
				$consumibleProductsCount= $this->find('count',[
					'fields'=>[
						'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
					],
					'conditions' => $conditions,
					'group'=>'StockItem.product_id',
				]);
				$this->recursive=-1;
        $consumibleProducts = $this->find('all',[
					'fields'=>[
						'SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo', 
					],
					'conditions' => $conditions,
					'contain'=>$contain,
					'group'=>'StockItem.product_id',
					'limit'=>$consumibleProductsCount,
				]);
					//print_r($consumibleProducts);exit;
        usort($consumibleProducts,[$this,'sortByProductName']);
				for ($i=0;$i<count($consumibleProducts);$i++){
					$stockItemConditions['StockItem.product_id']=$consumibleProducts[$i]['Product']['id'];
          $allStockItems=$this->find('all',[
						'fields'=>['StockItem.id'],
						'conditions'=>$stockItemConditions,
            'recursive'=>-1,
					]);
					//if ($consumibleProducts[$i]['Product']['id']==58){
          //  pr($allStockItems);
          //}
					$totalStockInventoryDate=0;
					$totalValueInventoryDate=0;
					if (count($allStockItems)>0){
						foreach ($allStockItems as $stockItem){		
							$lastStockItemLog=$this->StockItemLog->getLastStockItemLog($stockItem['StockItem']['id'],$inventoryDate,$warehouseId,$boolQuantitiesAtCurrentDate=false);
							if (!empty($lastStockItemLog)){
                //if ($consumibleProducts[$i]['Product']['id']==58 && $lastStockItemLog['StockItemLog']['product_quantity']){
                //  pr($stockItem['StockItem']['id']);
                //  pr($lastStockItemLog);
                //}
								$totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
								$totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
							}
						}
					}
					$consumibleProducts[$i][0]['Remaining']=$totalStockInventoryDate;
					$consumibleProducts[$i][0]['Saldo']=$totalValueInventoryDate;
				}
				$products=$consumibleProducts;
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
				//return ($firstTerm['FinishedProduct']['name'] < $secondTerm['FinishedProduct']['name']) ? -1 : 1;
        return ($firstTerm['Product']['name'] < $secondTerm['Product']['name']) ? -1 : 1;
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
				//return ($firstTerm['FinishedProduct']['name'] < $secondTerm['FinishedProduct']['name']) ? -1 : 1;
        return ($firstTerm['Product']['name'] < $secondTerm['Product']['name']) ? -1 : 1;
			}
		}
		else {
			//return ($firstTerm['FinishedProduct']['name'] < $secondTerm['FinishedProduct']['name']) ? -1 : 1;
      return ($firstTerm['Product']['name'] < $secondTerm['Product']['name']) ? -1 : 1;
		}	
	}
	
  // 20200619 check if this can be removed in favor of new and more maintainable getInventoryTotalQuantityAndValuePerProduct
  public function getInventoryTotalPerProduct($productId,$inventoryDate,$warehouseId=0,$boolQuantitiesAtCurrentDate=false){
    $model=$this;
    
	  //echo "inventory date is ".$inventoryDate."<br/>";
    //echo "warehouse id is ".$warehouseId."<br/>";
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		//echo "inventoryDatePlusOne is ".$inventoryDatePlusOne."<br/>";

		$this->recursive=-1;
    $productModel=ClassRegistry::init('Product');
    $productModel->recursive=-1;
		$conditions=[
		  'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
		  'StockItem.stockitem_depletion_date >'=> $inventoryDate,
      'StockItem.product_id'=> $productId,
		];
    
		if (!empty($warehouseId)){
			$conditions['StockItem.warehouse_id']=$warehouseId;
		}
    
    $product=$productModel->find('first',['conditions'=>['id'=>$productId]]);
    //pr($product);
    $products=[];
		switch ($product['Product']['product_type_id']){
			case PRODUCT_TYPE_PREFORMA:
				$preformaCount=	$this->find('count', [
					'fields'=>['SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo'],
					'conditions' => $conditions,
				]);
				$this->recursive=-1;
				$preformas = $this->find('all',[
					'fields'=>['SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo'],
					'conditions' => $conditions,
					'contain'=>[
						'Product'=>['fields'=>['Product.name','Product.id','Product.packaging_unit','Product.product_type_id']],
					],
          'group'=>'StockItem.product_id',
					'limit'=>$preformaCount,
				]);
        //pr($preformas[0]);
        
				for ($i=0;$i<count($preformas);$i++){
					$stockItemConditions=array(
            'StockItem.product_id'=>$productId,
            'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
            'StockItem.stockitem_depletion_date >'=> $inventoryDate,
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
						$lastStockItemLog=[];
						foreach ($allStockItems as $stockItem){				
							$this->StockItemLog->recursive=-1;
							$stockItemLogConditions=array(
								'StockItemLog.stockitem_id'=>$stockItem['StockItem']['id'],
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
        //20171228 NO REVISADO
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
            for ($productionResultCode=1;$productionResultCode<4;$productionResultCode++){
              $stockItemConditions=array(
                'StockItem.product_id'=>$productos[$i]['Product']['id'],
                'StockItem.production_result_code_id'=>$productionResultCode,
                'StockItem.raw_material_id'=>$productos[$i]['RawMaterial']['id'],
                'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
                'StockItem.stockitem_depletion_date >'=> $inventoryDate,
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
                $lastStockItemLog=[];
                
                foreach ($allStockItems as $stockItem){	
                  $stockItemLogConditions=array(
                    'StockItemLog.stockitem_id'=>$stockItem['StockItem']['id'],									
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
              switch ($productionResultCode){
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
        //20171228 NO REVISADO
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
            'StockItem.stockitem_depletion_date >'=> $inventoryDate,
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
						$lastStockItemLog=[];
						foreach ($allStockItems as $stockItem){		
							$this->StockItemLog->recursive=-1;
							$stockItemLogConditions=array(
								'StockItemLog.stockitem_id'=>$stockItem['StockItem']['id']
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
				$productCount=	$this->find('count', [
					'fields'=>['SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo'],
					'conditions' => $conditions,
				]);
				$this->recursive=-1;
				$products = $this->find('all',[
					'fields'=>['SUM(StockItem.remaining_quantity) AS Remaining','SUM(StockItem.remaining_quantity*StockItem.product_unit_price) AS Saldo'],
					'conditions' => $conditions,
					'contain'=>[
						'Product'=>['fields'=>['Product.name','Product.id','Product.packaging_unit','Product.product_type_id']],
					],
          'group'=>'StockItem.product_id',
					'limit'=>$productCount,
				]);
        //pr($preformas[0]);
        
				for ($i=0;$i<count($products);$i++){
					$stockItemConditions=[
            'StockItem.product_id'=>$productId,
            'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
            'StockItem.stockitem_depletion_date >'=> $inventoryDate,
          ];
					if ($warehouseId>0){
						$stockItemConditions[]=['StockItem.warehouse_id'=>$warehouseId,];
					}
					$allStockItems=$this->find('all',[
						'fields'=>['StockItem.id'],
						'conditions'=>$stockItemConditions,
					]);
					$totalStockInventoryDate=0;
					$totalValueInventoryDate=0;
					if (count($allStockItems)>0){
						$lastStockItemLog=[];
						foreach ($allStockItems as $stockItem){				
							$this->StockItemLog->recursive=-1;
							$stockItemLogConditions=[
								'StockItemLog.stockitem_id'=>$stockItem['StockItem']['id'],
							];
							if (!$boolQuantitiesAtCurrentDate){
								$stockItemLogConditions[]=['StockItemLog.stockitem_date <='=>$inventoryDatePlusOne];	
							}
							if ($warehouseId>0){
								$stockItemLogConditions[]=['StockItemLog.warehouse_id'=>$warehouseId,];
							}
							//pr($stockItemLogConditions);
							$lastStockItemLog=$this->StockItemLog->find('first',[
								'fields'=>[
									'StockItemLog.product_quantity','StockItemLog.product_unit_price',
								],
								'conditions'=>$stockItemLogConditions,
								'order'=>'StockItemLog.id DESC',
							]);
							if (count($lastStockItemLog)>0){
								if ($lastStockItemLog['StockItemLog']['product_quantity']>0){
									//pr($lastStockItemLog);
								}
								$totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
								$totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
							}
						}
					}
					$products[$i][0]['Remaining']=$totalStockInventoryDate;
					$products[$i][0]['Saldo']=$totalValueInventoryDate;
				}
			
				break;					
		}
		//echo "products coming out of getInventoryItems<br/>";
		//pr($products);
		return $products;	
	}
  
  public function getInventoryTotalQuantityAndValuePerProduct($productId,$inventoryDate,$warehouseId,$rawMaterialId=0,$productionResultCodeId=PRODUCTION_RESULT_CODE_A){
    //echo "inventory date is ".$inventoryDate."<br/>";
    //echo "warehouse id is ".$warehouseId."<br/>";
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		//echo "inventoryDatePlusOne is ".$inventoryDatePlusOne."<br/>";
		
    $this->recursive=-1;
    
    $productModel=ClassRegistry::init('Product');
    
    $stockItemConditions=[
		  'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
		  'StockItem.stockitem_depletion_date >'=> $inventoryDate,
      'StockItem.product_id'=> $productId,
      'StockItem.warehouse_id'=>$warehouseId,
		];
    $product=$productModel->find('first',[
      'conditions'=>['Product.id'=>$productId],
      'contain'=>[
        'ProductType',
      ],
    ]);
    
    if ($product['ProductType']['product_category_id'] == CATEGORY_PRODUCED){
      $stockItemConditions['StockItem.production_result_code_id']= $productionResultCodeId;
      $productionResultCodeModel=ClassRegistry::init('ProductionResultCode');
      $productionResultCode=$productionResultCodeModel->find('first',[
        'conditions'=>['id'=>$productionResultCodeId],
        'recursive'=>-1,
      ]);
      
      if ($product['Product']['product_type_id'] != PRODUCT_TYPE_INJECTION_OUTPUT){
        $stockItemConditions['StockItem.raw_material_id']= $rawMaterialId;
      
        $rawMaterial=$productModel->find('first',[
          'conditions'=>['id'=>$rawMaterialId],
          'recursive'=>-1,
        ]);
      }
    }
    
    $allStockItems=$this->find('all',[
      'fields'=>['StockItem.id'],
      'conditions'=>$stockItemConditions,
    ]);
    //pr($allStockItems);
    $totalStockInventoryDate=0;
    $totalValueInventoryDate=0;
    if (count($allStockItems)>0){
      $lastStockItemLog=[];
      foreach ($allStockItems as $stockItem){				
        $this->StockItemLog->recursive=-1;
        $stockItemLogConditions=[
          'StockItemLog.stockitem_id'=>$stockItem['StockItem']['id'],
          'StockItemLog.stockitem_date <='=>$inventoryDatePlusOne,	
          'StockItemLog.warehouse_id'=>$warehouseId
        ];
        $lastStockItemLog=$this->StockItemLog->find('first',[
          'fields'=>[
            'StockItemLog.product_quantity','StockItemLog.product_unit_price',
          ],
          'conditions'=>$stockItemLogConditions,
          'recursive'=>-1,
          'order'=>'StockItemLog.id DESC',
        ]);
        if (count($lastStockItemLog)>0){
          if ($lastStockItemLog['StockItemLog']['product_quantity']>0){
            //pr($lastStockItemLog);
          }
          $totalStockInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity'];
          $totalValueInventoryDate+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
        }
      }
		}
    $product['quantity']=$totalStockInventoryDate;
    $product['value']=$totalValueInventoryDate;
    $product['raw_material_name']=(empty($rawMaterial)?'':$rawMaterial['Product']['name']);
    $product['production_result_code']=(empty($productionResultCode)?'':$productionResultCode['ProductionResultCode']['code']);
		return $product;	
	}
  
  function getInventoryFinishedProduct($finishedProductId,$rawMaterialId,$inventoryDate,$warehouseId=0){
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		
		$conditions=array(
			'StockItem.stockitem_creation_date <'=> $inventoryDatePlusOne,
			'StockItem.stockitem_depletion_date >'=> $inventoryDate,
      'StockItem.product_id'=> $finishedProductId,
      'StockItem.raw_material_id'=> $rawMaterialId,
      'StockItem.raw_material_id'=> $rawMaterialId,
		);
		if ($warehouseId>0){
			$conditions[]=array('StockItem.warehouse_id'=>$warehouseId);
		}
		$this->recursive=-1;
    
    $stockItems=[];
    
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
              $rawMaterialStockItemIds=[];
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
                $entriesArray=[];
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
  
	function getRawMaterialsForProductionRun($productId=null,$quantityNeeded=0,$productionRunDate,$warehouseId){
    //$productionRunDatePlusOne=date("Y-m-d",strtotime($productionrundate."+1 days"));
		$rawMaterialsComplete=false;
		$usedRawMaterials=null;
		
    //echo "product id is ".$productId."<br/>";
    //echo "quantityNeeded is ".$quantityNeeded."<br/>";
    //echo "productionRunDate is ".$productionRunDate."<br/>";
    
		$rawMaterials = $this->find('all', [
			'fields' => [
				'StockItem.id',
				'StockItem.name',
				'StockItem.product_unit_price',
				'StockItem.remaining_quantity',
			],
			'conditions' => [
				'StockItem.product_id'=>$productId,
				'StockItem.remaining_quantity >'=>'>0',
				'StockItem.stockitem_creation_date <='=>$productionRunDate,
        'StockItem.stockitem_depletion_date >'=>$productionRunDate,
        'StockItem.warehouse_id'=>$warehouseId,
      ],
      'order'=>'StockItem.stockitem_creation_date ASC,StockItem.id ASC',
		]);
		//pr($rawMaterials);
		for ($i=0;$i<sizeof($rawMaterials);$i++){
			$rawId=$rawMaterials[$i]['StockItem']['id'];
			$rawName=$rawMaterials[$i]['StockItem']['name'];
			$rawUnitPrice=$rawMaterials[$i]['StockItem']['product_unit_price'];
			$quantityPresent=$rawMaterials[$i]['StockItem']['remaining_quantity'];
			if ($quantityNeeded>$quantityPresent){
				// consume all the materials in the present stockitem and move to the next
				$quantityUsed=$quantityPresent;
				$quantityRemaining=0;
				$quantityNeeded-=$quantityPresent;
			}
			else {
				// consume the necessary materials and indicate the raw materials are complete
				$quantityUsed=$quantityNeeded;
				$quantityRemaining=$quantityPresent-$quantityNeeded;
				$quantityNeeded=0;
				$rawMaterialsComplete = true;
			}
			$usedRawMaterials[$i]['id']=$rawId;
			$usedRawMaterials[$i]['name']=$rawName;
			$usedRawMaterials[$i]['unit_price']=$rawUnitPrice;
			
			$usedRawMaterials[$i]['quantity_present']=$quantityPresent;
			$usedRawMaterials[$i]['quantity_used']=$quantityUsed;
			$usedRawMaterials[$i]['quantity_remaining']=$quantityRemaining;
			if ($rawMaterialsComplete){
				break;
			}
		}
		return $usedRawMaterials;
	}
	
	function getRawMaterialsForInjectionProductionRun($productId=null,$quantityNeeded=0,$quantityNeededMill=0,$productionRunDate,$warehouseId){
    //$productionRunDatePlusOne=date("Y-m-d",strtotime($productionrundate."+1 days"));
		$rawMaterialsComplete=false;
    $rawMaterialsMillComplete=false;
    
		$usedRawMaterials=null;
		$usedRawMaterialsForMill=null;
    //echo "product id is ".$productId."<br/>";
    //echo "quantityNeeded is ".$quantityNeeded."<br/>";
    //echo "productionRunDate is ".$productionRunDate."<br/>";
    
		$rawMaterials = $this->find('all', [
			'fields' => [
				'StockItem.id',
				'StockItem.name',
				'StockItem.product_unit_price',
				'StockItem.remaining_quantity',
			],
			'conditions' => [
				'StockItem.product_id'=>$productId,
				'StockItem.remaining_quantity >'=>'>0',
				'StockItem.stockitem_creation_date <='=>$productionRunDate,
        'StockItem.stockitem_depletion_date >'=>$productionRunDate,
        'StockItem.warehouse_id'=>$warehouseId,
      ],
      'order'=>'StockItem.stockitem_creation_date ASC,StockItem.id ASC',
		]);
		//pr($rawMaterials);
		for ($i=0;$i<sizeof($rawMaterials);$i++){
			$rawStockItemId=$rawMaterials[$i]['StockItem']['id'];
			$rawStockItemName=$rawMaterials[$i]['StockItem']['name'];
			$rawUnitPrice=$rawMaterials[$i]['StockItem']['product_unit_price'];
			$quantityPresent=$rawMaterials[$i]['StockItem']['remaining_quantity'];
      $quantityPresentForMill=$rawMaterials[$i]['StockItem']['remaining_quantity'];
      if (!$rawMaterialsComplete){
        if ($quantityNeeded > $quantityPresent){
				// consume all the materials in the present stockitem and move to the next
          $quantityUsed=$quantityPresent;
          $quantityRemaining=0;
          $quantityNeeded-=$quantityPresent;
        }
        else {
          // consume the necessary materials and indicate the raw materials are complete
          $quantityUsed=$quantityNeeded;
          $quantityRemaining=$quantityPresent-$quantityNeeded;
          $quantityNeeded=0;
          $rawMaterialsComplete = true;
        }
        
        $quantityPresentForMill=$quantityRemaining;
        
        $usedRawMaterials[$i]['final_product']['id']=$rawStockItemId;
        $usedRawMaterials[$i]['final_product']['name']=$rawStockItemName;
        $usedRawMaterials[$i]['final_product']['unit_price']=$rawUnitPrice;
        
        $usedRawMaterials[$i]['final_product']['quantity_present']=$quantityPresent;
        $usedRawMaterials[$i]['final_product']['quantity_used']=$quantityUsed;
        $usedRawMaterials[$i]['final_product']['quantity_remaining']=$quantityRemaining;
      }
			if (!$rawMaterialsMillComplete){
        if ($quantityNeededMill > 0 && $quantityNeededMill > $quantityPresentForMill){
          $quantityUsed=$quantityPresentForMill;
          $quantityRemaining=0;
          $quantityNeededMill-=$quantityPresentForMill;
        }
        else {
          // consume the necessary materials and indicate the raw materials are complete
          $quantityUsed=$quantityNeededMill;
          $quantityRemaining=$quantityPresentForMill-$quantityNeededMill;
          $quantityNeededMill=0;
          $rawMaterialsMillComplete = true;
        }
        if ($quantityUsed > 0){
          $usedRawMaterials[$i]['mill']['id']=$rawStockItemId;
          $usedRawMaterials[$i]['mill']['name']=$rawStockItemName;
          $usedRawMaterials[$i]['mill']['unit_price']=$rawUnitPrice;
          
          $usedRawMaterials[$i]['mill']['quantity_present']=$quantityPresentForMill;
          $usedRawMaterials[$i]['mill']['quantity_used']=$quantityUsed;
          $usedRawMaterials[$i]['mill']['quantity_remaining']=$quantityRemaining;
        }
			}
      if ($rawMaterialsComplete && $rawMaterialsMillComplete){
				break;
			}
		}
		return $usedRawMaterials;
	}
	
  
  function getFinishedMaterialsForSale($productid,$resultcodeid,$quantityneeded,$rawmaterialid,$saledate, $warehouseId, $orderby="ASC"){
		$saleDatePlusOne=date("Y-m-d",strtotime($saledate."+1 days"));
		$finishedMaterialsComplete=false;
		$usedFinishedMaterials=null;
		
		$conditions=array(
			'StockItem.product_id'=>$productid,
      // 20180316 ADD BACK CONDITION AS THE STOCKITEM ALWAYS NEEDS TO HAVE STOCK FOR A SALE
			'StockItem.remaining_quantity >'=>'0',
			'StockItem.production_result_code_id'=>$resultcodeid,
			'StockItem.raw_material_id'=>$rawmaterialid,
			'StockItem.stockitem_creation_date <='=>$saledate,
			'StockItem.stockitem_depletion_date >='=>$saleDatePlusOne,
			
		);
    //pr($conditions);
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
    //pr($finishedMaterialsType);
		for ($i=0;$i<sizeof($finishedMaterialsType);$i++){
			$finishedid=$finishedMaterialsType[$i]['StockItem']['id'];
			$finishedname=$finishedMaterialsType[$i]['StockItem']['name'];
			$finishedunitprice=$finishedMaterialsType[$i]['StockItem']['product_unit_price'];
			$quantitypresent=$finishedMaterialsType[$i]['StockItem']['remaining_quantity'];
      if ($quantitypresent>0){
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
        /*
          $usedFinishedMaterials[$i]['id']=$finishedid;
          $usedFinishedMaterials[$i]['name']=$finishedname;
          
          $usedFinishedMaterials[$i]['unit_price']=$finishedunitprice;
          
          $usedFinishedMaterials[$i]['quantity_present']=$quantitypresent;
          $usedFinishedMaterials[$i]['quantity_used']=$quantityused;
          $usedFinishedMaterials[$i]['quantity_remaining']=$quantityremaining;
        */
        $usedMaterial=[];
        $usedMaterial['id']=$finishedid;
        $usedMaterial['name']=$finishedname;
        
        $usedMaterial['unit_price']=$finishedunitprice;
        
        $usedMaterial['quantity_present']=$quantitypresent;
        $usedMaterial['quantity_used']=$quantityused;
        $usedMaterial['quantity_remaining']=$quantityremaining;
        $usedFinishedMaterials[]=$usedMaterial;
      }
			if ($finishedMaterialsComplete){
				break;
			}
		}
		return $usedFinishedMaterials;
	}
	
	function getOtherMaterialsForSale($productid=null,$quantityneeded=0,$saledate,$warehouseId=0,$orderby="ASC"){
		$saleDatePlusOne=date("Y-m-d",strtotime($saledate."+1 days"));
		$conditions=array(
			'StockItem.product_id'=>$productid,
			'StockItem.remaining_quantity >'=>'0',
			'StockItem.stockitem_creation_date <='=>$saledate,
			'StockItem.stockitem_depletion_date >='=>$saleDatePlusOne,
		);
		if (!empty($warehouseId)){
			$conditions[]=array(
				'StockItem.warehouse_id'=>$warehouseId,
			);
		}
		$otherMaterialsComplete=false;
		$usedOtherMaterials=[];
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

  function getStockMovementDataForUtility($stockItemIds){
    $stockItems=$this->find('all',[
      'fields'=>['StockItem.remaining_quantity','StockItem.product_unit_price'],
      'conditions'=>['StockItem.id'=>$stockItemIds],
      'contain'=>[
        'StockMovement'=>[
          'fields'=>[
            'StockMovement.product_quantity',
            'StockMovement.product_total_price',
            'StockMovement.bool_reclassification',
            'StockMovement.bool_transfer',
          ],
          'conditions'=>[
            'StockMovement.bool_input'=>false,
            'StockMovement.product_quantity >'=>0,
          ],
        ]
      ]
    ]);
    $quantitySold=0;
    $quantityStock=0;
    $quantityReclassified=0;
    $quantityTransferred=0;
    $valueSold=0;
    $valueStock=0;
    $valueReclassified=0;
    $valueTransferred=0;
    
    foreach ($stockItems as $stockItem){
      $quantityStock+=$stockItem['StockItem']['remaining_quantity'];
      $valueStock+=$stockItem['StockItem']['remaining_quantity']*$stockItem['StockItem']['product_unit_price'];
      foreach ($stockItem['StockMovement'] as $stockMovement){
        if ($stockMovement['bool_reclassification']){
          $quantityReclassified+=$stockMovement['product_quantity'];
          $valueReclassified+=$stockMovement['product_total_price'];  
        }
        elseif($stockMovement['bool_transfer']){
          $quantityTransferred+=$stockMovement['product_quantity'];
          $valueTransferred+=$stockMovement['product_total_price'];
        }
        else {
          $quantitySold+=$stockMovement['product_quantity'];
          $valueSold+=$stockMovement['product_total_price'];
        }
      }
    }
    
    $stockMovementData=[
      'quantitySold'=>$quantitySold,
      'quantityStock'=>$quantityStock,
      'quantityReclassified'=>$quantityReclassified,
      'quantityTransferred'=>$quantityTransferred,
      'valueSold'=>$valueSold,
      'valueStock'=>$valueStock,
      'valueReclassified'=>$valueReclassified,
      'valueTransferred'=>$valueTransferred,
    ];
    
    return $stockMovementData;
  }
	
  function getAllProductCombinations($warehouseId,$productTypeId,$boolExistingOnly=false,$productId=0){
    $productModel=ClassRegistry::init('Product');
    $productConditions=[
      'Product.product_type_id'=>$productTypeId,
    ];
    if ($productId>0){
      $productConditions['Product.id']=$productId;
    }
    $productIds=$this->Product->find('list',[
      'fields'=>'Product.id',
      'conditions'=>$productConditions,
    ]);
    
    $conditions=[
      'StockItem.warehouse_id'=>$warehouseId,
      'StockItem.product_id'=>$productIds,
    ];
    if ($boolExistingOnly){
      $conditions['stockitem_depletion_date >']=date('Y-m-d');
    }
    $fields=['product_id','SUM(`remaining_quantity`) AS StockItem__remaining'];
    $order=['StockItem.product_id'];
    $group=['StockItem.product_id'];
    if ($productTypeId == PRODUCT_TYPE_BOTTLE){
      $fields=['product_id','raw_material_id','SUM(`remaining_quantity`) AS StockItem__remaining'];
      $order=['product_id','raw_material_id'];
      $group=['product_id','raw_material_id'];
    }
    $this->virtualFields['remaining'] = 0;
    $stockItemProducts=$this->find('all',[
      'fields'=>$fields,
      'conditions'=>$conditions,
      'recursive'=>-1,
      'order'=>$order,
      'group'=>$group,
    ]);  
    
    $existences=[
      'Product'=>[],
    ];
    $productIds=[];
    $rawMaterialIds=[];
    foreach($stockItemProducts as $stockItemProduct){
      //pr($stockItemProduct);
      if (!array_key_exists($stockItemProduct['StockItem']['product_id'],$existences['Product'])){
        $productIds[$stockItemProduct['StockItem']['product_id']]=$stockItemProduct['StockItem']['product_id'];
        if ($productTypeId == PRODUCT_TYPE_BOTTLE){
          $existences['Product'][$stockItemProduct['StockItem']['product_id']]['RawMaterial']=[];
        }
        else {        
          $existences['Product'][$stockItemProduct['StockItem']['product_id']]['remaining']=0;
        }
      }
      if ($productTypeId == PRODUCT_TYPE_BOTTLE){
        if (!array_key_exists($stockItemProduct['StockItem']['raw_material_id'],$existences['Product'][$stockItemProduct['StockItem']['product_id']]['RawMaterial'])){
          $rawMaterialIds[$stockItemProduct['StockItem']['raw_material_id']]=$stockItemProduct['StockItem']['raw_material_id'];
          $existences['Product'][$stockItemProduct['StockItem']['product_id']]['RawMaterial'][$stockItemProduct['StockItem']['raw_material_id']]['remaining']=0;
          
        }
        $existences['Product'][$stockItemProduct['StockItem']['product_id']]['RawMaterial'][$stockItemProduct['StockItem']['raw_material_id']]['remaining']+=$stockItemProduct['StockItem']['remaining'];  
      }
      else {
        $existences['Product'][$stockItemProduct['StockItem']['product_id']]['remaining']+=$stockItemProduct['StockItem']['remaining'];  
      }      
    }
    // so far we only have existing products
    if (!$boolExistingOnly && $productTypeId == PRODUCT_TYPE_BOTTLE){
       $allBottlePreferredPreformas=$this->Product->find('list',[
        'fields'=>['Product.id','Product.preferred_raw_material_id'],
        'conditions'=>[
          'Product.product_type_id'=>PRODUCT_TYPE_BOTTLE,
        ],
       ]);
       foreach ($allBottlePreferredPreformas as $productId=>$preferredRawMaterialId){
         if (!array_key_exists($productId,$existences['Product'])){
           $existences['Product'][$productId]['RawMaterial'][$preferredRawMaterialId]['remaining']=0;
           $productIds[$productId]=$productId;
           $rawMaterialIds[$preferredRawMaterialId]=$preferredRawMaterialId;
         }
         elseif (!array_key_exists($preferredRawMaterialId,$existences['Product'][$productId]['RawMaterial'])){
          $existences['Product'][$productId]['RawMaterial'][$preferredRawMaterialId]['remaining']=0; 
          $rawMaterialIds[$preferredRawMaterialId]=$preferredRawMaterialId;
         }   
       }
    }
    $products=$this->Product->find('list',[
      'conditions'=>['Product.id'=>$productIds,],  
    ]);
    if ($productTypeId == PRODUCT_TYPE_BOTTLE || $productTypeId == PRODUCT_TYPE_IMPORT){
      $productVolumes=$this->Product->find('list',[
        'fields'=>['Product.id','Product.volume_ml_max'],
        'conditions'=>['Product.id'=>$productIds,],  
      ]);  
    }
    
    if ($productTypeId == PRODUCT_TYPE_BOTTLE){
      $rawMaterials=$this->Product->find('list',[
        'conditions'=>['Product.id'=>$rawMaterialIds],
        'order'=>'Product.name ASC',
      ]);
      $rawMaterialAbbreviations=$this->Product->find('list',[
        'fields'=>['Product.id','Product.abbreviation'],
        'conditions'=>['Product.id'=>$rawMaterialIds],
        'order'=>'Product.name ASC',
      ]);
    }
    // now add the names for sorting
    foreach ($existences['Product'] as $productId=>$productData){
      $existences['Product'][$productId]['name']=$products[$productId];
      if ($productTypeId == PRODUCT_TYPE_BOTTLE || $productTypeId == PRODUCT_TYPE_IMPORT){
        $existences['Product'][$productId]['volume']=$productVolumes[$productId];
      }
      if ($productTypeId == PRODUCT_TYPE_BOTTLE){
        foreach ($existences['Product'][$productId]['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
          $existences['Product'][$productId]['RawMaterial'][$rawMaterialId]['name']=$rawMaterials[$rawMaterialId];
          $existences['Product'][$productId]['RawMaterial'][$rawMaterialId]['abbreviation']=$rawMaterialAbbreviations[$rawMaterialId];
        } 
      }  
    }
    // now sort
    if (!empty($existences['Product'])){
      if ($productTypeId == PRODUCT_TYPE_BOTTLE || $productTypeId == PRODUCT_TYPE_IMPORT){
        uasort($existences['Product'],[$this,'sortByVolume']);  
      }
      else {
        uasort($existences['Product'],[$this,'sortByName']);  
      }
      foreach ($existences['Product'] as $productId=>$productData){
        if (!empty($productData['RawMaterial']) && count($productData['RawMaterial'])>1){
          uasort($existences['Product'][$productId]['RawMaterial'],[$this,'sortByAbbreviation']);  
        }
      }  
    }
    
    return [
      'existences'=>$existences,
      'productIds'=>$productIds,
      'rawMaterialIds'=>$rawMaterialIds,
    ];
    
  }
  
  function getAllProductCombinationsByProductNature($warehouseId,$productNatureId,$boolExistingOnly=false,$productId=0){
    $productModel=ClassRegistry::init('Product');
    $productConditions=[
      'Product.product_nature_id'=>$productNatureId,
    ];
    if ($productId>0){
      $productConditions['Product.id']=$productId;
    }
    $productIds=$this->Product->find('list',[
      'fields'=>'Product.id',
      'conditions'=>$productConditions,
    ]);
    
    $conditions=[
      'StockItem.warehouse_id'=>$warehouseId,
      'StockItem.product_id'=>$productIds,
    ];
    if ($boolExistingOnly){
      $conditions['stockitem_depletion_date >']=date('Y-m-d');
    }
    $fields=['product_id','SUM(`remaining_quantity`) AS StockItem__remaining'];
    $order=['StockItem.product_id'];
    $group=['StockItem.product_id'];
    if ($productNatureId == PRODUCT_NATURE_PRODUCED){
      $fields=['product_id','raw_material_id','SUM(`remaining_quantity`) AS StockItem__remaining'];
      $order=['product_id','raw_material_id'];
      $group=['product_id','raw_material_id'];
    }
    $this->virtualFields['remaining'] = 0;
    $stockItemProducts=$this->find('all',[
      'fields'=>$fields,
      'conditions'=>$conditions,
      'recursive'=>-1,
      'order'=>$order,
      'group'=>$group,
    ]);  
    
    $existences=[
      'Product'=>[],
    ];
    $productIds=[];
    $rawMaterialIds=[];
    foreach($stockItemProducts as $stockItemProduct){
      //pr($stockItemProduct);
      if (!array_key_exists($stockItemProduct['StockItem']['product_id'],$existences['Product'])){
        $productIds[$stockItemProduct['StockItem']['product_id']]=$stockItemProduct['StockItem']['product_id'];
        if ($productNatureId == PRODUCT_NATURE_PRODUCED){
          $existences['Product'][$stockItemProduct['StockItem']['product_id']]['RawMaterial']=[];
        }
        else {        
          $existences['Product'][$stockItemProduct['StockItem']['product_id']]['remaining']=0;
        }
      }
      if ($productNatureId == PRODUCT_NATURE_PRODUCED){
        if (!array_key_exists($stockItemProduct['StockItem']['raw_material_id'],$existences['Product'][$stockItemProduct['StockItem']['product_id']]['RawMaterial'])){
          $rawMaterialIds[$stockItemProduct['StockItem']['raw_material_id']]=$stockItemProduct['StockItem']['raw_material_id'];
          $existences['Product'][$stockItemProduct['StockItem']['product_id']]['RawMaterial'][$stockItemProduct['StockItem']['raw_material_id']]['remaining']=0;
          
        }
        $existences['Product'][$stockItemProduct['StockItem']['product_id']]['RawMaterial'][$stockItemProduct['StockItem']['raw_material_id']]['remaining']+=$stockItemProduct['StockItem']['remaining'];  
      }
      else {
        $existences['Product'][$stockItemProduct['StockItem']['product_id']]['remaining']+=$stockItemProduct['StockItem']['remaining'];  
      }      
    }
    // so far we only have existing products
    if (!$boolExistingOnly && $productNatureId == PRODUCT_NATURE_PRODUCED){
       $allBottlePreferredPreformas=$this->Product->find('list',[
        'fields'=>['Product.id','Product.preferred_raw_material_id'],
        'conditions'=>[
          'Product.product_type_id'=>PRODUCT_NATURE_PRODUCED,
        ],
       ]);
       foreach ($allBottlePreferredPreformas as $productId=>$preferredRawMaterialId){
         if (!array_key_exists($productId,$existences['Product'])){
           $existences['Product'][$productId]['RawMaterial'][$preferredRawMaterialId]['remaining']=0;
           $productIds[$productId]=$productId;
           $rawMaterialIds[$preferredRawMaterialId]=$preferredRawMaterialId;
         }
         elseif (!array_key_exists($preferredRawMaterialId,$existences['Product'][$productId]['RawMaterial'])){
          $existences['Product'][$productId]['RawMaterial'][$preferredRawMaterialId]['remaining']=0; 
          $rawMaterialIds[$preferredRawMaterialId]=$preferredRawMaterialId;
         }   
       }
    }
    $products=$this->Product->find('list',[
      'conditions'=>['Product.id'=>$productIds,],  
    ]);
    if ($productNatureId == PRODUCT_NATURE_PRODUCED || $productNatureId == PRODUCT_NATURE_BOTTLES_BOUGHT){
      $productVolumes=$this->Product->find('list',[
        'fields'=>['Product.id','Product.volume_ml_max'],
        'conditions'=>['Product.id'=>$productIds,],  
      ]);  
    }
    
    if ($productNatureId == PRODUCT_NATURE_PRODUCED){
      $rawMaterials=$this->Product->find('list',[
        'conditions'=>['Product.id'=>$rawMaterialIds],
        'order'=>'Product.name ASC',
      ]);
      $rawMaterialAbbreviations=$this->Product->find('list',[
        'fields'=>['Product.id','Product.abbreviation'],
        'conditions'=>['Product.id'=>$rawMaterialIds],
        'order'=>'Product.name ASC',
      ]);
    }
    // now add the names for sorting
    foreach ($existences['Product'] as $productId=>$productData){
      $existences['Product'][$productId]['name']=$products[$productId];
      if ($productNatureId == PRODUCT_NATURE_PRODUCED || $productNatureId == PRODUCT_NATURE_BOTTLES_BOUGHT){
        $existences['Product'][$productId]['volume']=$productVolumes[$productId];
      }
      if ($productNatureId == PRODUCT_NATURE_PRODUCED){
        foreach ($existences['Product'][$productId]['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
          $existences['Product'][$productId]['RawMaterial'][$rawMaterialId]['name']=$rawMaterials[$rawMaterialId];
          $existences['Product'][$productId]['RawMaterial'][$rawMaterialId]['abbreviation']=$rawMaterialAbbreviations[$rawMaterialId];
        } 
      }  
    }
    // now sort
    if (!empty($existences['Product'])){
      if ($productNatureId == PRODUCT_NATURE_PRODUCED || $productNatureId == PRODUCT_NATURE_BOTTLES_BOUGHT){
        uasort($existences['Product'],[$this,'sortByName']);
        uasort($existences['Product'],[$this,'sortByVolume']);  
      }
      else {
        uasort($existences['Product'],[$this,'sortByName']);  
      }
      foreach ($existences['Product'] as $productId=>$productData){
        if (!empty($productData['RawMaterial']) && count($productData['RawMaterial'])>1){
          uasort($existences['Product'][$productId]['RawMaterial'],[$this,'sortByAbbreviation']);  
        }
      }  
    }
    
    return [
      'existences'=>$existences,
      'productIds'=>$productIds,
      'rawMaterialIds'=>$rawMaterialIds,
    ];
    
  }
  
  public function sortByName($a,$b ){ 
	  return ($a['name'] < $b['name']) ? -1 : 1;
	}
  public function sortByAbbreviation($a,$b ){ 
	  return ($a['abbreviation'] < $b['abbreviation']) ? -1 : 1;
	}
  public function sortByVolume($a,$b ){ 
	  return ($a['volume'] < $b['volume']) ? -1 : 1;
	}
  
  public function getExistingStockItemIdsByRawMaterialIdForDateRange($startDate,$endDate,$rawMaterialId = 0){
    $endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
    $conditions=[
      'StockItem.stockitem_creation_date <'=>$endDatePlusOne,
      'StockItem.stockitem_depletion_date >='=>$startDate,
    ];
    if ($rawMaterialId > 0){
      $conditions['StockItem.raw_material_id']=$rawMaterialId;
    }
    $stockItemIds=$this->find('list',[
      'fields'=>['StockItem.id'],
      'conditions'=>$conditions,
    ]);
    return $stockItemIds;
  }
  public function getExistingStockItemIdsForDateRange($startDate,$endDate,$productId = 0){
    $endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
    $conditions=[
      'StockItem.stockitem_creation_date <'=>$endDatePlusOne,
      'StockItem.stockitem_depletion_date >='=>$startDate,
    ];
    if ($productId > 0){
      $conditions['StockItem.product_id']=$productId;
    }
    $stockItemIds=$this->find('list',[
      'fields'=>['StockItem.id'],
      'conditions'=>$conditions,
    ]);
    return $stockItemIds;
  }
  
	public $validate = [
		'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
			],
		],
		'product_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'original_quantity' => [
			'numeric' => [
				'rule' => ['numeric'],
				
			],
		],
    'remaining_quantity' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
    'production_result_code_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
	];

	public $belongsTo = [
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'type'=>'right outer',
		],
		'ProductionResultCode' => [
			'className' => 'ProductionResultCode',
			'foreignKey' => 'production_result_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'RawMaterial' => [
			'className' => 'Product',
			'foreignKey' => 'raw_material_id',
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
		'Warehouse' => [
			'className' => 'Warehouse',
			'foreignKey' => 'warehouse_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'ProductionMovement' => [
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
		],
		'StockMovement' => [
			'className' => 'StockMovement',
			'foreignKey' => 'stockitem_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'StockItemLog' => [
			'className' => 'StockItemLog',
			'foreignKey' => 'stockitem_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
