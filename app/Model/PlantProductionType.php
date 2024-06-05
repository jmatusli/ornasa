<?php
App::uses('AppModel', 'Model');

class PlantProductionType extends AppModel {

  public function hasPlant($productionTypeId,$plantId){
    $plantProductionType=$this->find('first',[
      'conditions'=>[
        'PlantProductionType.production_type_id'=>$productionTypeId,
        'PlantProductionType.plant_id'=>$plantId
      ],
      'recursive'=>-1,
      'order'=>'PlantProductionType.id DESC',
    ]);
    if (empty($plantProductionType)){
      return false;
    }
    return $plantProductionType['PlantProductionType']['bool_assigned'];
  }
  
  public function getProductionTypesForPlant($plantId){
    $productionTypes=$this->ProductionType->find('list');
    foreach ($productionTypes as $productionTypeId=>$productionTypeName){
      if (!$this->hasPlant($productionTypeId,$plantId)){
        unset($productionTypes[$productionTypeId]);
      }
    }
    return $productionTypes;
  }
  public function getProductionTypeIdsForPlant($plantId){
    return array_keys($this->getProductionTypesForPlant($plantId));
  }
  
  public function getPlantsForProductionType($productionTypeId){
    $plants=$this->Plant->find('list');
    foreach ($plants as $plantId=>$plantName){
      if (!$this->hasPlant($productionTypeId,$plantId)){
        unset($plants[$plantId]);
      }
    }
    return $plants;
  }
  public function getPlantIdsForProductionType($productionTypeId){
    return array_keys($this->getPlantsForProductionType($productionTypeId));
  }

	public $validate = [
		'assignment_datetime' => [
			'datetime' => [
				'rule' => ['datetime'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'plant_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'production_type_id' => [
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

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	public $belongsTo = [
		'Plant' => [
			'className' => 'Plant',
			'foreignKey' => 'plant_id',
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
	];
}
