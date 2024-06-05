<?php
App::uses('AppModel', 'Model');


class Warehouse extends AppModel {
	var $displayField="name";

  function getWarehouseSeries($warehouseId){
    $warehouse=$this->find('first',[
      'conditions'=>['Warehouse.id'=>$warehouseId],
      'recursive'=>-1,
    ]);
    return $warehouse['Warehouse']['series'];
  }
  
  function getWarehouseList($warehouseId = 0){
    $conditions=[
      'Warehouse.bool_active'=>true,
    ];
    if ($warehouseId > 0){
      $conditions['Warehouse.id']=$warehouseId;
    }
    $warehouses=$this->find('list',[
      'conditions'=>$conditions,
      'order'=>'Warehouse.series',
    ]);
    return $warehouses;
  }
  function getShortWarehouseList($warehouseId = 0){
    $conditions=[
      'Warehouse.bool_active'=>true,
    ];
    if ($warehouseId > 0){
      $conditions['Warehouse.id']=$warehouseId;
    }
    $warehouses=$this->find('list',[
      'fields'=>['Warehouse.id','Warehouse.short_name'],
      'conditions'=>$conditions,
      'order'=>'Warehouse.series',
    ]);
    return $warehouses;
  }
  
  function getWarehouseById($warehouseId){
    return $this->find('first',[
      'conditions'=>[
        'Warehouse.id'=>$warehouseId,
      ],
      'recursive'=>-1,
    ]);
  }
  function getPlantId($warehouseId){
    $warehouse=$this->getWarehouseById($warehouseId);
    return (empty($warehouse)?0:$warehouse['Warehouse']['plant_id']);
  }
  function getWarehousePlantIds($warehouseIds=[]){
    $warehouseConditions=[];
    if (!empty($warehouseIds)){
      $warehouseConditions['Warehouse.id']=$warehouseIds;
    }
    $warehouses=$this->find('list',[
      'fields'=>['Warehouse.id','Warehouse.plant_id'],
      'conditions'=>$warehouseConditions,
    ]);
    return $warehouses;
  }

  function getWarehousesForPlantId($plantId){
    return $this->find('list',[
      'conditions'=>[
        'Warehouse.plant_id'=>$plantId,
      ],
      'order'=>'Warehouse.name'
    ]);
  }
  function getWarehouseIdsForPlantId($plantId){
    return array_keys($this->getWarehousesForPlantId($plantId));
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
    'series' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
			],
		],
	];

	public $hasMany = [
    'Order' => [
			'className' => 'Order',
			'foreignKey' => 'warehouse_id',
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
    'SalesOrder' => [
			'className' => 'SalesOrder',
			'foreignKey' => 'warehouse_id',
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
			'foreignKey' => 'warehouse_id',
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
		'StockItemLog' => [
			'className' => 'StockItemLog',
			'foreignKey' => 'warehouse_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => '',
		],
    'UserWarehouse' => [
			'className' => 'UserWarehouse',
			'foreignKey' => 'warehouse_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => '',
		],
	];
}
