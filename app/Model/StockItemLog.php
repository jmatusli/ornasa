<?php
App::uses('AppModel', 'Model');

class StockItemLog extends AppModel {

  public function getLastStockItemLog($stockItemId,$inventoryDate,$warehouseId=0,$boolQuantitiesAtCurrentDate=false){
    $inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
    
    $stockItemLogConditions=[
      'StockItemLog.stockitem_id'=>$stockItemId,
    ];
    if (!$boolQuantitiesAtCurrentDate){
      $stockItemLogConditions['StockItemLog.stockitem_date <=']=$inventoryDatePlusOne;	
    }
    if ($warehouseId>0){
      $stockItemLogConditions['StockItemLog.warehouse_id']=$warehouseId;
    }
    //20180515 REACTIVATED
    $lastStockItemLog=$this->find('first',[
      'fields'=>['StockItemLog.product_quantity','StockItemLog.product_unit_price'],
      'conditions'=>$stockItemLogConditions,
      'recursive'=>-1,
      'order'=>'StockItemLog.id DESC',
    ]);
    return $lastStockItemLog;
  }					

	public function getStockQuantityAtDateForFinishedProduct($productId,$rawMaterialId,$productionResultCodeId,$inventoryDate,$warehouseId=0,$boolReturnQuantityOnCurrentDate=false){
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		$quantityInStock=0;
		//echo "retrieving the stock quantity with product id ".$productId.", raw material id ".$rawMaterialId.", production result code ".$productionResultCodeId." y inventorydate ".$inventoryDate."<br/>";
		$conditions=[
			'StockItem.product_id'=> $productId,
			'StockItem.raw_material_id'=> $rawMaterialId,
			'StockItem.production_result_code_id'=> $productionResultCodeId,
			'StockItem.stockitem_creation_date <='=> $inventoryDate,
			'StockItem.stockitem_depletion_date >='=> $inventoryDatePlusOne,
		];
		if (!empty($warehouseId)){
			$conditions['StockItem.warehouse_id']=$warehouseId;
		}
    //echo "bool current date is ".$boolReturnQuantityOnCurrentDate."<br/>";
    if ($boolReturnQuantityOnCurrentDate){
      $conditions['StockItem.remaining_quantity >']= 0;
    }
    //pr($conditions);
		$stockItemIds=$this->StockItem->find('list',[
			'fields'=>'StockItem.id',
			'conditions' => $conditions,
		]);
		//pr($stockItemIds);
		if (!empty($stockItemIds)){
			foreach($stockItemIds as $id=>$stockitemid){
        $stockItemLogConditions=['StockItemLog.stockitem_id'=>$stockitemid];
        if (!$boolReturnQuantityOnCurrentDate){
          $stockItemLogConditions['StockItemLog.stockitem_date <=']=$inventoryDate;
        }
        //pr($stockItemLogConditions);
				$stockItemLog=$this->find('first',[
					'fields'=>'StockItemLog.product_quantity',
					'conditions'=>$stockItemLogConditions,
					'order'=>'StockItemLog.id DESC',
				]);	
				//pr($stockItemLog);
				if (!empty($stockItemLog)){
					$quantityInStock+=$stockItemLog['StockItemLog']['product_quantity'];
				}
				//echo "quantity in stock is ".$quantityInStock."<br/>";
			}
		};								
		return $quantityInStock;
	}
	
	public function getStockQuantityAtDateForProduct($productId,$inventoryDate,$warehouseId=0,$boolReturnQuantityOnCurrentDate=false){
		$inventoryDatePlusOne=date("Y-m-d",strtotime($inventoryDate."+1 days"));
		$quantityInStock=0;
		$conditions=[
			'StockItem.product_id'=> $productId,
			'StockItem.stockitem_creation_date <='=> $inventoryDate,
			'StockItem.stockitem_depletion_date >='=> $inventoryDatePlusOne,
		];
		if (!empty($warehouseId)){
			$conditions['StockItem.warehouse_id']=$warehouseId;
		}
    //echo "bool current date is ".$boolReturnQuantityOnCurrentDate."<br/>";
    if ($boolReturnQuantityOnCurrentDate){
      $conditions['StockItem.remaining_quantity >']= 0;
    }
		$stockItemIds=$this->StockItem->find('list',array(
			'fields'=>'StockItem.id',
			'conditions' => $conditions,
		));
		if (!empty($stockItemIds)){
			foreach($stockItemIds as $id=>$stockitemid){
        $stockItemLogConditions=['StockItemLog.stockitem_id'=>$stockitemid];
        if (!$boolReturnQuantityOnCurrentDate){
          $stockItemLogConditions['StockItemLog.stockitem_date <=']=$inventoryDate;
        }
        //pr($stockItemLogConditions);
				$stockItemLog=$this->find('first',[
					'fields'=>'StockItemLog.product_quantity',
					'conditions'=>$stockItemLogConditions,
					'order'=>'StockItemLog.id DESC',
				]);
				if (!empty($stockItemLog)){
					$quantityInStock+=$stockItemLog['StockItemLog']['product_quantity'];
				}
			}
		};								
		return $quantityInStock;
	}

	public $validate = [
		'stockitem_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'stockitem_date' => [
			'datetime' => [
				'rule' => ['datetime'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'product_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'product_quantity' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		/*
		'product_unit_price' => [
			'numeric' => [
				'rule' => ['decimal',8],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		*/
	];

	public $belongsTo = [
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'ProductionMovement' => [
			'className' => 'ProductionMovement',
			'foreignKey' => 'production_movement_id',
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
		'StockMovement' => [
			'className' => 'StockMovement',
			'foreignKey' => 'stock_movement_id',
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
}
