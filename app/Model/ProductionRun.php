<?php
App::uses('AppModel', 'Model');

class ProductionRun extends AppModel {

  public function getProductionRunCode($plantId,$plantShortName){
    $lastProductionRun = $this->find('first',[
			'fields'=>['production_run_code'],
      'conditions'=>[
        'ProductionRun.plant_id'=>$plantId,
      ],
      'recursive'=>-1,
			'order' => ['ProductionRun.created' => 'desc'],
		]);
		//pr($lastProductionRun);
    $newProductionRunCode=($plantId == PLANT_SANDINO? "OR":("PROD_".$plantShortName."_"));
		if (empty($lastProductionRun)){
      $newProductionRunCode.=($plantId == PLANT_SANDINO?"1":"000001");
    }
    else {    
      $lastProductionRunCodeNumber=(int)substr($lastProductionRun['ProductionRun']['production_run_code'],($plantId == PLANT_SANDINO?2:(strlen($plantShortName)+6)));
      //echo 'lastProductionRunCodeNumber is '.$lastProductionRunCodeNumber.'<br/>';
      //echo 'starting position is '.(strlen($plantShortName)+6).'<br/>';
      $newProductionRunCodeNumber=$lastProductionRunCodeNumber+1;
      $newProductionRunCode.=($plantId == PLANT_SANDINO?$newProductionRunCodeNumber:str_pad($newProductionRunCodeNumber,6,'0',STR_PAD_LEFT));
		}
		return $newProductionRunCode;
  }

	public function getEnergyUseForRun($productionRunId){
		$energyConsumption=0;
		$thisProductionRun=$this->find('first',array('conditions'=>array('ProductionRun.id'=>$productionRunId)));
		//echo "year of OR is ".date('Y',strtotime($thisProductionRun['ProductionRun']['production_run_date']))."<br/>";
		//echo "month of OR is ".date('m',strtotime($thisProductionRun['ProductionRun']['production_run_date']))."<br/>";
		//echo "day of OR is ".date('d',strtotime($thisProductionRun['ProductionRun']['production_run_date']))."<br/>";
		$countOfRunsInShift=$this->find('count',array(
			'conditions'=>array(
				'YEAR(ProductionRun.production_run_date)'=>date('Y',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
				'MONTH(ProductionRun.production_run_date)'=>date('m',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
				'DAY(ProductionRun.production_run_date)'=>date('d',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
				'ProductionRun.shift_id'=>$thisProductionRun['ProductionRun']['shift_id'],
			),
		));
		
		if ($countOfRunsInShift==1){
			$lastMeterReading=$this->find('first',array(
				'fields'=>array('MAX(ProductionRun.meter_finish) AS max_meter'),
				'conditions'=>array('ProductionRun.meter_finish <'=>$thisProductionRun['ProductionRun']['meter_finish']),
			));
			$energyConsumption=$thisProductionRun['ProductionRun']['meter_finish']-$lastMeterReading[0]['max_meter'];
		}
		else{
			//echo "calculating weighted average";
			$allProductionRunsInShift=$this->find('all',array(
				'fields'=>array('ProductionRun.raw_material_quantity','ProductionRun.meter_finish'),
				'conditions'=>array(
					'YEAR(ProductionRun.production_run_date)'=>date('Y',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
					'MONTH(ProductionRun.production_run_date)'=>date('m',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
					'DAY(ProductionRun.production_run_date)'=>date('d',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
					'ProductionRun.shift_id'=>$thisProductionRun['ProductionRun']['shift_id'],
				),
			));
			$minimalMeterReading=1000000;
			$maximalMeterReading=0;
			$totalProductsProduced=0;
			foreach ($allProductionRunsInShift as $productionrun){
				if ($productionrun['ProductionRun']['meter_finish']<$minimalMeterReading&&$productionrun['ProductionRun']['meter_finish']>0){
					$minimalMeterReading=$productionrun['ProductionRun']['meter_finish'];
				}
				if ($productionrun['ProductionRun']['meter_finish']>$maximalMeterReading){
					$maximalMeterReading=$productionrun['ProductionRun']['meter_finish'];
				}
				$totalProductsProduced+=$productionrun['ProductionRun']['raw_material_quantity'];
			}
			if ($maximalMeterReading==0){
				$energyConsumption=0;
			}
			else {
				$lastMeterReading=$this->find('first',array(
					'fields'=>array('MAX(ProductionRun.meter_finish) AS max_meter'),
					'conditions'=>array('ProductionRun.meter_finish <'=>$minimalMeterReading),
				));
				
				$energyConsumption=($maximalMeterReading-$lastMeterReading[0]['max_meter'])*$thisProductionRun['ProductionRun']['raw_material_quantity']/$totalProductsProduced;
			}
		}
		return $energyConsumption;
	}

  public function getEditabilityData($productionRunId){
    $boolEditable=true;
    $message='ok';
    
    $productionRun=$this->find('first',[
      'conditions'=>['ProductionRun.id'=>$productionRunId],
      'contain'=>[
        'ProductionMovement'=>[
          'conditions'=>[
            'ProductionMovement.bool_input'=>false,
          ],
          'StockItem'=>[
            'ProductionMovement'=>[
              'conditions'=>[  
                'ProductionMovement.bool_input'=>true,
              ],  
              'ProductionRun',
            ],
            'StockMovement'=>[
              'Order',
            ],
          ]
        ]
      ]
    ]);
    if (empty($productionRun)){
      $boolEditable=false;
      $message='No existe el proceso de producción';
    }
    else {
      foreach ($productionRun['ProductionMovement'] as $productionMovement){
        if (!empty($productionMovement['StockItem']['StockMovement'])){
          $boolEditable=false;
          $message='Los productos del proceso de producción no se pueden editar porque ya se remitieron los productos fabricados en las salidas ';
          foreach ($productionMovement['StockItem']['StockMovement'] as $stockMovement){
            //pr($productionMovement['StockItem']['StockMovement']);
            if (!$stockMovement['bool_transfer']){
              //pr($productionMovement['StockItem']['StockMovement']['Order']);
              $message.=$stockMovement['Order']['order_code']." ";              
            }
            else {
              $message.=$stockMovement['transfer_code']." ";
            }
          }
        }
        elseif (!empty($productionMovement['StockItem']['ProductionMovement'])){
          $boolEditable=false;
          $message='Los productos del proceso de producción no se pueden editar porque el lote de molino se ocupó ya en los procesos de producción ';
          foreach ($productionMovement['StockItem']['ProductionMovement'] as $productionMovement){
            $message.=$productionMovement['ProductionRun']['production_run_code']." ";              
          }
        }
      }  
    }
    return [
      'boolEditable'=>$boolEditable,
      'message'=>$message,
    ];
  }

	public function checkAcceptableProduction($productionRunId,$productionRunDate){
		$productionRunData=$this->find('first',array(
			'fields'=>['ProductionRun.shift_id'],
			'conditions'=>array(
				'ProductionRun.id'=>$productionRunId,
			),
			'contain'=>array(
				'FinishedProduct'=>array(
					'ProductProduction'=>array(
						'fields'=>array('ProductProduction.application_date','ProductProduction.acceptable_production'),
						'conditions'=>array(
							'ProductProduction.application_date <='=> $productionRunDate,
						),
						'order'=>'ProductProduction.application_date DESC,ProductProduction.id DESC'
					),
				),
				'ProductionMovement'=>array(
					'fields'=>array('ProductionMovement.product_quantity'),
					'conditions'=>array(
						'ProductionMovement.product_quantity >'=>0,
						'ProductionMovement.production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
					),
				),
			),
		));
		//pr($productionRunData);
		if (!empty($productionRunData)){    
			$acceptableProductionValue=0;
			if (!empty($productionRunData['FinishedProduct']['ProductProduction'])){						
				$acceptableProductionValue=$productionRunData['FinishedProduct']['ProductProduction'][0]['acceptable_production'];
			}
      $quantityA=0;
      if (!empty($productionRunData['ProductionMovement'])){
        $quantityA=$productionRunData['ProductionMovement'][0]['product_quantity'];
      }
      return $this->checkProduction($acceptableProductionValue,$quantityA,$productionRunDate,$productionRunData['ProductionRun']['shift_id']);
		}
		return false;
	}
  
  public function checkProduction($acceptableProductionValue,$actualProductionQualityAValue,$productionRunDateString,$shiftId){
    //echo "production run date is ".$productionRunDate."<br/>";
    //echo "day of the week for production run date is ".date('w', strtotime($productionRunDate))."<br/>";
    if(date('w', strtotime($productionRunDateString)) == 6){
      //echo "acceptable production value is ".$acceptableProductionValue."<br/>";
      $acceptableProductionValue=$acceptableProductionValue/2;
      //echo "acceptable production value is ".$acceptableProductionValue."<br/>";
    }
    elseif ($shiftId==SHIFT_NIGHT){
      $acceptableProductionValue=$acceptableProductionValue*7/8.5;
    }
    if ($acceptableProductionValue==0||($actualProductionQualityAValue>=$acceptableProductionValue)){
      //echo "quantity is ".$quantityA."<br/>";
      //echo "acceptable production value is ".$acceptableProductionValue."<br/>";
      return true;
    }
    return false;
  }
  
  public function getProductionRunIdsForMachineAndPeriod($machineId,$startDate,$endDate){
    $endDatePlusOne=$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
    $productionRunIds=$this->find('list',[
      'fields'=>'ProductionRun.id',
      'conditions'=> [
        'ProductionRun.machine_id'=>$machineId,
        'ProductionRun.production_run_date >='=>$startDate,
        'ProductionRun.production_run_date <'=>$endDatePlusOne,
        'ProductionRun.bool_annulled'=>false,
      ]
    ]);
    //pr($productionRunIds);
    return $productionRunIds;
  }
  
  public function getProductionRunIdsForProductAndPeriod($finishedProductId,$startDate,$endDate){
    $endDatePlusOne=$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
    $productionRunIds=$this->find('list',[
      'fields'=>'ProductionRun.id',
      'conditions'=> [
        'ProductionRun.finished_product_id'=>$finishedProductId,
        'ProductionRun.production_run_date >='=>$startDate,
        'ProductionRun.production_run_date <'=>$endDatePlusOne,
        'ProductionRun.bool_annulled'=>false,
      ]
    ]);
    //pr($productionRunIds);
    return $productionRunIds;
  }
  
  public function getProductionRun($productionRunId){
    return $this->find('first', [
      'conditions'=>[
        'id'=>$productionRunId,
      ],
      'recursive'=>-1,
		]);
  }
  public function getProductionRunDate($productionRunId){
    $productionRun=$this->getProductionRun($productionRunId);
		if (empty($productionRun)){
      return date("Y-m-d");
    }
    return $productionRun['ProductionRun']['production_run_date'];
  }
  public function getPlantId($productionRunId){
    $productionRun=$this->getProductionRun($productionRunId);
		if (empty($productionRun)){
      return 0;
    }
    return $productionRun['ProductionRun']['plant_id'];
  }
  public function getRecipeId($productionRunId){
    $productionRun=$this->getProductionRun($productionRunId);
		if (empty($productionRun)){
      return 0;
    }
    return $productionRun['ProductionRun']['recipe_id'];
  }
  
  public function getInjectionRawMaterialsNeededForMill($recipeItems,$millConversionQuantity){
    if ($millConversionQuantity == 0){
      return [];
    }
    
    $totalIngredientsForFinishedProduct=0;
    $ingredientsForFinishedProduct=[];
    
    foreach($recipeItems as $recipeItem){
      //pr($recipeItem);
      $ingredientQuantity=$recipeItem['quantity'];
      $ingredientId=$recipeItem['product_id'];

      $totalIngredientsForFinishedProduct+=$ingredientQuantity;
      if (!array_key_exists($ingredientId,$ingredientsForFinishedProduct)){
        $ingredientsForFinishedProduct[$ingredientId]=0;
      }
      $ingredientsForFinishedProduct[$ingredientId]+=$ingredientQuantity;            
    } 
    $ingredientsForMill=[];
    $productCounter=0;
    $subtotalMillIngredients=0;
    foreach ($ingredientsForFinishedProduct as $ingredientId => $ingredientQuantity){
      if ($productCounter < count($ingredientsForFinishedProduct)-1){
        $ingredientsForMill[$ingredientId]=round($millConversionQuantity*$ingredientQuantity/$totalIngredientsForFinishedProduct);
        $subtotalMillIngredients+=round($millConversionQuantity*$ingredientQuantity/$totalIngredientsForFinishedProduct);
      }
      else {
        $ingredientsForMill[$ingredientId]=$millConversionQuantity-$subtotalMillIngredients;
      }
      $productCounter++;
    }
    return $ingredientsForMill;
  }
  
	public $validate = [
		'production_run_code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];

	public $belongsTo = [
		'RawMaterial' => [
			'className' => 'Product',
			'foreignKey' => 'raw_material_id',
			//'conditions' => ['ProductType.product_category_id'=>CATEGORY_RAW],
			'fields' => '',
			'order' => ''
		],
		'FinishedProduct' => [
			'className' => 'Product',
			'foreignKey' => 'finished_product_id',
			//'conditions' => ['ProductType.product_category_id'=>CATEGORY_PRODUCED],
			'fields' => '',
			'order' => ''
		],
		'Machine' => [
			'className' => 'Machine',
			'foreignKey' => 'machine_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Operator' => [
			'className' => 'Operator',
			'foreignKey' => 'operator_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Shift' => [
			'className' => 'Shift',
			'foreignKey' => 'shift_id',
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
    'Incidence' => [
			'className' => 'Incidence',
			'foreignKey' => 'incidence_id',
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
    'Plant' => [
			'className' => 'Plant',
			'foreignKey' => 'plant_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Recipe' => [
			'className' => 'Recipe',
			'foreignKey' => 'recipe_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'ProductionMovement' => [
			'className' => 'ProductionMovement',
			'foreignKey' => 'production_run_id',
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
    'ProductionLoss' => [
			'className' => 'ProductionLoss',
			'foreignKey' => 'production_run_id',
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
