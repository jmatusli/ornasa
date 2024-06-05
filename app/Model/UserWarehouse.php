<?php
App::uses('AppModel', 'Model');

class UserWarehouse extends AppModel {

  public function getWarehouseListForUser($userId,$warehouseId=0){
    $user=$this->User->find('first',[
      'conditions'=>['User.id'=>$userId],
      'recursive'=>-1,
    ]);
    if (empty($user)) {return null;}
    
    $warehouseConditions=['Warehouse.bool_active'=>true];
    
    if ($user['User']['role_id'] != ROLE_ADMIN){
      if($warehouseId>0){
        $warehouseIds=[
          $warehouseId=>$warehouseId,
        ];
      }
      else {
        $warehouseIds=$this->getAssociatedWarehousesForUser($userId);
      }
      $warehouseConditions['Warehouse.id']=$warehouseIds;
    }
    
    //$warehouseModel=ClassRegistry::init('Warehouse');
    $warehouses=$this->Warehouse->find('list',[
      'fields'=>['Warehouse.id','Warehouse.name'],
      'conditions'=>$warehouseConditions,
      'order'=>'Warehouse.series ASC',
    ]);
    return $warehouses;
  }
  
  public function getAssociatedWarehousesForUser($userId){
    $this->recursive=-1;
		$warehouseIdsAssociatedWithUserAtOneTime=$this->find('list',[
      'fields'=>['UserWarehouse.warehouse_id'],
			'conditions'=>['UserWarehouse.user_id'=>$userId],
			'order'=>'UserWarehouse.id DESC',
		]);
    $warehouseIdsAssociatedWithUserAtOneTime=array_unique($warehouseIdsAssociatedWithUserAtOneTime);
    $this->Warehouse->recursive=-1;
    $uniqueWarehouses=$this->Warehouse->find('all',[
      'conditions'=>['Warehouse.id'=>$warehouseIdsAssociatedWithUserAtOneTime,],
      'contain'=>[					
        'UserWarehouse'=>[
          'conditions'=>['UserWarehouse.user_id'=>$userId,],
          'order'=>'UserWarehouse.assignment_datetime DESC,UserWarehouse.id DESC',
        ],
  		],
    ]);
    $warehouseIdsCurrentlyAssociated=[];
    foreach ($uniqueWarehouses as $warehouse){
      if ($warehouse['UserWarehouse'][0]['bool_assigned']){
        $warehouseIdsCurrentlyAssociated[]=$warehouse['Warehouse']['id'];
      }
    }
		return $warehouseIdsCurrentlyAssociated;
	}

  public function hasUserWarehouse($warehouseId,$userId){
    $userWarehouse=$this->find('first',[
      'conditions'=>[
        'UserWarehouse.warehouse_id'=>$warehouseId,
        'UserWarehouse.user_id'=>$userId
      ],
      'recursive'=>-1,
      'order'=>'UserWarehouse.id DESC',
    ]);
    if (empty($userWarehouse)){
      return false;
    }
    return $userWarehouse['UserWarehouse']['bool_assigned'];
  }
  
  public function getWarehousesForUser($userId){
    $warehouses=$this->Warehouse->find('list');
    foreach ($warehouses as $warehouseId=>$warehouseName){
      if (!$this->hasUserWarehouse($warehouseId,$userId)){
        unset($warehouses[$warehouseId]);
      }
    }
    return $warehouses;
  }
  public function getWarehouseIdsForUser($userId){
    return array_keys($this->getWarehousesForUser($userId));
  }
  
  public function getUsersForWarehouse($warehouseId){
    $users=$this->User->find('list');
    foreach ($users as $userId=>$userName){
      if (!$this->hasUserWarehouse($warehouseId,$userId)){
        unset($users[$userId]);
      }
    }
    return $users;
  }
  public function getUserIdsForWarehouse($warehouseId){
    return array_keys($this->getUsersForWarehouse($warehouseId));
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
		'warehouse_id' => [
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
		'Warehouse' => [
			'className' => 'Warehouse',
			'foreignKey' => 'warehouse_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
