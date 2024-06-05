<?php
App::uses('AppModel', 'Model');

class PlantProductType extends AppModel {

  public function hasPlantProductType($productTypeId,$plantId){
    $plantProductType=$this->find('first',[
      'conditions'=>[
        'PlantProductType.product_type_id'=>$productTypeId,
        'PlantProductType.plant_id'=>$plantId
      ],
      'recursive'=>-1,
      'order'=>'PlantProductType.id DESC',
    ]);
    if (empty($plantProductType)){
      return false;
    }
    return $plantProductType['PlantProductType']['bool_assigned'];
  }
  
  public function getProductTypesForPlant($plantId){
    $productTypes=$this->ProductType->find('list');
    foreach ($productTypes as $productTypeId=>$productTypeName){
      if (!$this->hasPlantProductType($productTypeId,$plantId)){
        unset($productTypes[$productTypeId]);
      }
    }
    return $productTypes;
  }
  public function getProductTypeIdsForPlant($plantId){
    return array_keys($this->getProductTypesForPlant($plantId));
  }
  
  public function getPlantsForProductType($productTypeId){
    $plants=$this->Plant->find('list');
    foreach ($plants as $plantId=>$plantName){
      if (!$this->hasPlantProductType($productTypeId,$plantId)){
        unset($plants[$plantId]);
      }
    }
    return $plants;
  }
  public function getPlantIdsForProductType($productTypeId){
    return array_keys($this->getPlantsForProductType($productTypeId));
  }

	public $validate = [
		'assignment_datetime' => [
			'datetime' => [
				'rule' => ['datetime'],
			],
		],
		'plant_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'product_type_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'bool_assigned' => [
			'boolean' => [
				'rule' => ['boolean'],
			],
		],
	];

	public $belongsTo = [
		'Plant' => [
			'className' => 'Plant',
			'foreignKey' => 'plant_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'ProductType' => [
			'className' => 'ProductType',
			'foreignKey' => 'product_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
