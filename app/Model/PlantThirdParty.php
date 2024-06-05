<?php
App::uses('AppModel', 'Model');

class PlantThirdParty extends AppModel {

  public function hasPlantThirdParty($thirdPartyId,$plantId){
    $plantThirdParty=$this->find('first',[
      'conditions'=>[
        'PlantThirdParty.third_party_id'=>$thirdPartyId,
        'PlantThirdParty.plant_id'=>$plantId
      ],
      'recursive'=>-1,
      'order'=>'PlantThirdParty.id DESC',
    ]);
    if (empty($plantThirdParty)){
      return false;
    }
    return $plantThirdParty['PlantThirdParty']['bool_assigned'];
  }
   
  public function getThirdPartiesForPlant($plantId){  
    $thirdParties=$this->ThirdParty->find('list');
    foreach ($clients as $clientId=>$clientName){
      if (!$this->hasPlantThirdParty($clientId,$plantId)){
        unset($thirdParties[$thirdPartyId]);
      }
    }
    return $thirdParties;
  }
  public function getClientsForPlant($plantId){
    return $this->getThirdPartiesForPlant($plantId);
  }
  public function getProvidersForPlant($plantId){
    return $this->getThirdPartiesForPlant($plantId);
  }  
  public function getThirdPartyIdsForPlant($plantId){
    return array_keys($this->getThirdPartiesForPlant($plantId));
  }
  
  public function getPlantsForThirdParty($thirdPartyId){
    $plants=$this->Plant->find('list');
    foreach ($plants as $plantId=>$plantName){
      if (!$this->hasPlantThirdParty($thirdPartyId,$plantId)){
        unset($plants[$plantId]);
      }
    }
    return $plants;
  }
  public function getPlantIdsForThirdParty($thirdPartyId){
    return array_keys($this->getPlantsForThirdParty($thirdPartyId));
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
		'third_party_id' => [
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
		'ThirdParty' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'third_party_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
