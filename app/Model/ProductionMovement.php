<?php
App::uses('AppModel', 'Model');

class ProductionMovement extends AppModel {

  function getProductionMovementDataForProductionRuns($productionIdList){
    $productionMovements=$this->find('all',[
      'fields'=>[
        'ProductionMovement.bool_input',
        'ProductionMovement.product_id',
        'ProductionMovement.product_quantity',
        'ProductionMovement.product_unit_price',
        'ProductionMovement.production_result_code_id',
        'ProductionMovement.stockitem_id',
      ],
      'conditions'=>[
        'ProductionMovement.production_run_id'=>$productionIdList,
        'ProductionMovement.product_quantity >'=>0,
      ],
      'contain'=>[
        'StockItem'=>['fields'=>'StockItem.raw_material_id']
      ]
    ]);
    
    $inputTotals=[];
    $outputTotals=[];
    
    foreach ($productionMovements as $productionMovement){
      
      $productId=$productionMovement['ProductionMovement']['product_id'];  
      $productQuantity=$productionMovement['ProductionMovement']['product_quantity'];
      $productTotalCost=$productQuantity*$productionMovement['ProductionMovement']['product_unit_price'];
     
      if ($productionMovement['ProductionMovement']['bool_input']){
        if (array_key_exists($productId,$inputTotals)){
          $inputTotals[$productId]['productQuantity']+=$productQuantity;
          $inputTotals[$productId]['productTotalCost']+=$productTotalCost;
        }
        else {
          $inputTotals[$productId]=[
            'productQuantity'=>$productQuantity,
            'productTotalCost'=>$productTotalCost
          ];  
        }
      }
      else {
        $rawMaterialId=$productionMovement['StockItem']['raw_material_id'];  
        $productionResultCodeId=$productionMovement['ProductionMovement']['production_result_code_id'];
        $stockItemId=$productionMovement['ProductionMovement']['stockitem_id'];
        if (array_key_exists($rawMaterialId,$outputTotals)){
          if (array_key_exists($productId,$outputTotals[$rawMaterialId])){
            if (array_key_exists($productionResultCodeId,$outputTotals[$rawMaterialId][$productId])){  
              $outputTotals[$rawMaterialId][$productId][$productionResultCodeId]['productQuantity']+=$productQuantity;
              $outputTotals[$rawMaterialId][$productId][$productionResultCodeId]['productTotalCost']+=$productTotalCost;
              array_push($outputTotals[$rawMaterialId][$productId][$productionResultCodeId]['stockItemIds'],$stockItemId);
              continue;
            }
          }
        }        
        $outputTotals[$rawMaterialId][$productId][$productionResultCodeId]=[
          'productQuantity'=>$productQuantity,
          'productTotalCost'=>$productTotalCost,
          'stockItemIds'=>[$stockItemId]
        ];  
      }
    }
    
    $productionMovementData=[
      'inputTotals'=>$inputTotals,
      'outputTotals'=>$outputTotals,
    ];
    //pr($productionMovementData);
		return $productionMovementData;
	}

	public $validate = array(
		'description' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'stockitem_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'product_quantity' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		/*'product_unit_price' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),*/
	);

	public $belongsTo = [
		'ProductionRun' => [
			'className' => 'ProductionRun',
			'foreignKey' => 'production_run_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'StockItem' => [
			'className' => 'StockItem',
			'foreignKey' => 'stockitem_id',
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
}
