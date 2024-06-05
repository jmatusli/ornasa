<?php
App::uses('AppModel', 'Model');

class ProductionType extends AppModel {
  var $displayField="name";

  public function getProductionTypeList($productionTypeIds=[]){
    $productionTypeConditions=[];
    if (!empty($productionTypeIds)){
      $productionTypeConditions['ProductionType.id']=$productionTypeIds;
    }
    //pr($productionTypeConditions);
    $productionTypes = $this->find('list',[
      'conditions'=>$productionTypeConditions,
      'order'=>'ProductionType.list_order ASC'
    ]);
    return $productionTypes;
  }

  public function getProductionTypes(){
    
    return $this->getProductionTypeList();
  }
  
  public function getProductionTypesForPlant($plantId=0){
    return $this->getProductionTypesForPlants([$plantId]);
  }
  public function getProductionTypesForPlants($plantIds=[]){
    $productionTypes=$this->getProductionTypes();
    if (!empty($plantIds)){
      foreach ($productionTypes as $productionTypeId => $productionTypeName){
        $plantProductionType=$this->PlantProductionType->find('first',[
          'conditions'=>[
            'PlantProductionType.plant_id'=>$plantIds,
            'PlantProductionType.production_type_id'=>$productionTypeId,
          ],
          'recursive'=>-1,
          'order'=>'PlantProductionType.assignment_datetime DESC',
        ]); 
        //pr($plantProductionType);
        if (empty($plantProductionType) || !$plantProductionType['PlantProductionType']['bool_assigned']){
          unset($productionTypes[$productionTypeId]);
        }
      }
    }
    return $productionTypes;
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
		'short_description' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'long_description' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'list_order' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'hex_color' => [
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
  /*
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'fields' => '',
			'order' => ''
		],
    */
  ];  
  public $hasMany = [
		'ProductionRun' => [
			'className' => 'ProductionRun',
			'foreignKey' => 'production_type_id',
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
    'PlantProductionType' => [
			'className' => 'PlantProductionType',
			'foreignKey' => 'production_type_id',
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
