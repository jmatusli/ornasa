<?php
App::uses('AppModel', 'Model');

class PlantProductionResultCode extends AppModel {

  public function hasPlant($productionResultCodeId,$plantId){
    $plantProductionResultCode=$this->find('first',[
      'conditions'=>[
        'PlantProductionResultCode.production_result_code_id'=>$productionResultCodeId,
        'PlantProductionResultCode.plant_id'=>$plantId
      ],
      'recursive'=>-1,
      'order'=>'PlantProductionResultCode.id DESC',
    ]);
    if (empty($plantProductionResultCode)){
      return false;
    }
    return $plantProductionResultCode['PlantProductionResultCode']['bool_assigned'];
  }
  
  public function getProductionResultCodesForPlant($plantId){
    $productionResultCodes=$this->ProductionResultCode->find('list');
    foreach ($productionResultCodes as $productionResultCodeId=>$productionResultCodeAbbreviation){
      if (!$this->hasPlant($productionResultCodeId,$plantId)){
        unset($productionResultCodes[$productionResultCodeId]);
      }
    }
    return $productionResultCodes;
    
  }
  
  public function PlantdsForProductionResultCode($productionResultCodeId){
    $plants=$this->Plant->find('list');
    foreach ($plants as $plantId=>$plantName){
      if (!$this->hasPlant($productionResultCodeId,$plantId)){
        unset($plants[$plantId]);
      }
    }
    return $plants;
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
		'production_result_code_id' => [
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
		'ProductionResultCode' => [
			'className' => 'ProductionResultCode',
			'foreignKey' => 'production_result_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
