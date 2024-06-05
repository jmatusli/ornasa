<?php
App::uses('AppModel', 'Model');


class Plant extends AppModel {
	var $displayField="name";

  function getPlantSeries($plantId){
    $plant=$this->find('first',[
      'conditions'=>['Plant.id'=>$plantId],
      'recursive'=>-1,
    ]);
    return $plant['Plant']['series'];
  }
  function getPlantShortName($plantId=0){
    if ($plantId == 0){
      return null;
    }
    $plant=$this->find('first',[
      'conditions'=>['Plant.id'=>$plantId],
      'recursive'=>-1,
    ]);
    return $plant['Plant']['short_name'];
  }
  function getPlantList($plantId = 0){
    $conditions=[
      'Plant.bool_active'=>true,
    ];
    if ($plantId > 0){
      $conditions['Plant.id']=$plantId;
    }
    $plants=$this->find('list',[
      'conditions'=>$conditions,
      'order'=>'Plant.name',
    ]);
    return $plants;
  }
  function getShortPlantList($plantId = 0){
    $conditions=[
      'Plant.bool_active'=>true,
    ];
    if ($plantId > 0){
      $conditions['Plant.id']=$plantId;
    }
    $plants=$this->find('list',[
      'fields'=>['Plant.id','Plant.short_name'],
      'conditions'=>$conditions,
      'order'=>'Plant.series',
    ]);
    return $plants;
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
	];

	public $hasMany = [
    'Machine' => [
			'className' => 'Machine',
			'foreignKey' => 'plant_id',
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
    'Operator' => [
			'className' => 'Operator',
			'foreignKey' => 'plant_id',
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
    'PlantProductionResultCode' => [
			'className' => 'PlantProductionType',
			'foreignKey' => 'plant_id',
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
    'PlantProductionType' => [
			'className' => 'PlantProductionType',
			'foreignKey' => 'plant_id',
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
    'PlantProductType' => [
			'className' => 'PlantProductType',
			'foreignKey' => 'plant_id',
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
    'PlantThirdParty' => [
			'className' => 'PlantThirdParty',
			'foreignKey' => 'plant_id',
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
    'ProductionRun' => [
			'className' => 'ProductionRun',
			'foreignKey' => 'plant_id',
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
    'UserPlant' => [
			'className' => 'UserPlant',
			'foreignKey' => 'plant_id',
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
    /*
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
    */
	];
}
