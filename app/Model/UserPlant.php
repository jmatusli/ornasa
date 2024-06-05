<?php
App::uses('AppModel', 'Model');

class UserPlant extends AppModel {

  public function hasPlant($userId,$plantId){
    $userPlant=$this->find('first',[
      'conditions'=>[
        'UserPlant.user_id'=>$userId,
        'UserPlant.plant_id'=>$plantId
      ],
      'recursive'=>-1,
      'order'=>'UserPlant.id DESC',
    ]);
    if (empty($userPlant)){
      return false;
    }
    return $userPlant['UserPlant']['bool_assigned'];
  }

  public function getPlantListForUser($userId,$plantId=0){
    $user=$this->User->find('first',[
      'conditions'=>['User.id'=>$userId],
      'recursive'=>-1,
    ]);
    if (empty($user)) {return null;}
    
    $plantConditions=['Plant.bool_active'=>true];
    
    if ($user['User']['role_id'] != ROLE_ADMIN){
      if($plantId>0){
        $plantIds=[
          $plantId=>$plantId,
        ];
      }
      else {
        $plantIds=$this->getAssociatedPlantsForUser($userId);
      }
      $plantConditions['Plant.id']=$plantIds;
    }
    //pr($plantConditions);
    $plants=$this->Plant->find('list',[
      'fields'=>['Plant.id','Plant.name'],
      'conditions'=>$plantConditions,
      'order'=>'Plant.series ASC',
    ]);
    return $plants;
  }
  
  public function getAssociatedPlantsForUser($userId){
    $this->recursive=-1;
		$plantIdsAssociatedWithUserAtOneTime=$this->find('list',[
      'fields'=>['UserPlant.plant_id'],
			'conditions'=>['UserPlant.user_id'=>$userId],
			'order'=>'UserPlant.id DESC',
		]);
    $plantIdsAssociatedWithUserAtOneTime=array_unique($plantIdsAssociatedWithUserAtOneTime);
    $this->Plant->recursive=-1;
    $uniquePlants=$this->Plant->find('all',[
      'conditions'=>['Plant.id'=>$plantIdsAssociatedWithUserAtOneTime,],
      'contain'=>[					
        'UserPlant'=>[
          'conditions'=>['UserPlant.user_id'=>$userId,],
          'order'=>'UserPlant.assignment_datetime DESC,UserPlant.id DESC',
        ],
  		],
    ]);
    $plantIdsCurrentlyAssociated=[];
    foreach ($uniquePlants as $plant){
      if ($plant['UserPlant'][0]['bool_assigned']){
        $plantIdsCurrentlyAssociated[]=$plant['Plant']['id'];
      }
    }
		return $plantIdsCurrentlyAssociated;
	}

  public function getUsersForPlant($plantId){
    $users=$this->User->getActiveUserList();
    foreach ($users as $userId=>$userData){
      if (!$this->hasPlant($userId,$plantId)){
        unset($users[$userId]);
      }
    }
    return $users;
  }


	public $validate = [
		'user_id' => [
			'numeric' => [
				'rule' => ['numeric'],
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
		'bool_assigned' => [
			'boolean' => [
				'rule' => ['boolean'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];


	public $belongsTo = [
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
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
	];
}
