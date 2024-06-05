<?php
App::uses('AppModel', 'Model');

class User extends AppModel {

  public $displayField='userName';

	public $actsAs = array('Acl' => array('type' => 'requester', 'enabled' => false));
  
  public function getActiveUserList(){
    $users=$this->find('all',[
      'fields'=>['User.id','User.first_name','User.last_name'],
      'conditions'=>[
        'User.bool_active'=>true,
        'User.bool_show_in_list'=>true,
        'User.bool_test'=>false,
      ],
      'contain'=>[
        'Role'=>['fields'=>'name'],
      ],
      'order'=>'User.first_name ASC',
    ]);
    $userList=[];
    if (!empty($users)){
      foreach ($users as $user){
        $userList[$user['User']['id']]=$user['User']['first_name'].' '.$user['User']['last_name'].' ('.$user['Role']['name'].')';
      }
    }
    return $userList;
  }
  
  public function getActiveUsersForRole($roleId,$warehouseId=0){
    $conditions=[
      'User.bool_active'=>true,
      'User.role_id'=>$roleId,
    ];
    if ($warehouseId>0){
      $userIdsForWarehouse=$this->UserWarehouse->getUserIdsForWarehouse($warehouseId);
      $conditions['User.id']=$userIdsForWarehouse;
    }
    $users=$this->find('all',[
      'conditions'=>$conditions,
      'order'=>'User.username ASC',
    ]);
    $userList=[];
    foreach ($users as $user){
      $userList[$user['User']['id']]=$user['User']['first_name'].' '.$user['User']['last_name'];
    }
    return $userList;
  }
  
  public function getUserList($userIds=[]){
    $conditions=[];
    if (!empty($userIds)){
      $conditions['User.id']=$userIds;
    }
    $users=$this->find('all',[
      'conditions'=>$conditions,
      'recursive'=>-1,
      'order'=>'User.first_name ASC, User.last_name ASC',
    ]);
    $userList=[];
    if (!empty($users)){
      foreach ($users as $user){
        $userList[$user['User']['id']]=$user['User']['first_name'].' '.$user['User']['last_name'];
      }
    }
    return $userList;
  }
  
  public function getAllActiveVendorAdminUsersAllWarehouses($userId = 0){
    $userConditions=[
      'User.bool_active'=>true,
      'User.bool_show_in_list'=>true,
      'User.bool_test'=>false,
      'User.role_id' => [ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING,ROLE_FACTURACION],
    ];
    if (!empty($userId)){
      $userConditions['User.id']=$userId;
    }
    return $this->find('all',[
      'fields'=>['User.id','User.first_name','User.last_name'],
      'conditions'=>$userConditions,
      'recursive'=>-1,
      'order'=>'User.first_name ASC, User.last_name ASC',
    ]);
  }
  
  
  public function getAllActiveVendorAdminUsers($warehouseId=0,$userId = 0){
    $userConditions=[
      'User.bool_active'=>true,
      'User.bool_show_in_list'=>true,
      'User.bool_test'=>false,
      'User.role_id' => [ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING,ROLE_FACTURACION],
    ];
    $userIdsForWarehouse=$this->UserWarehouse->getUserIdsForWarehouse($warehouseId);
    if (!empty($userId)){
      $userConditions['User.id']=$userId;
    }
    elseif ($warehouseId ) {
      $userConditions['User.id']=$userIdsForWarehouse;
    }
    return $this->find('all',[
      'fields'=>['User.id','User.first_name','User.last_name'],
      'conditions'=>$userConditions,
      'recursive'=>-1,
      'order'=>'User.first_name ASC, User.last_name ASC',
    ]);
  }
  
  public function getActiveVendorAdminUserList($warehouseId=0,$userId = 0){
    $users=$this->getAllActiveVendorAdminUsers($warehouseId,$userId);
    $userList=[];
    if (!empty($users)){
      foreach ($users as $user){
        $userList[$user['User']['id']]=$user['User']['first_name'].' '.$user['User']['last_name'];
      }
    }
    return $userList;
  }
  // 20210208 OBSOLETE BUT IN USE FOR SALES ORDERS AND INVOICES
  public function getActiveVendorUserList($warehouseId,$userId = 0){
    return $this->getActiveVendorAdminUserList($warehouseId,$userId);
  }
  
  public function getActiveVendorOnlyUserList($warehouseId,$userId = 0){
    $userConditions=[
      'User.bool_active'=>true,
      'User.bool_show_in_list'=>true,
      'User.bool_test'=>false,
      'User.role_id' => [ROLE_MANAGER,ROLE_SALES,ROLE_FACTURACION],
    ];
    $userIdsForWarehouse=$this->UserWarehouse->getUserIdsForWarehouse($warehouseId);
    if (!empty($userId)){
      $userConditions['User.id']=$userId;
    }
    elseif ($warehouseId > 0) {
      $userConditions['User.id']=$userIdsForWarehouse;
    }
    //pr($userConditions);
    $users=$this->find('all',[
      'fields'=>['User.id','User.first_name','User.last_name'],
      'conditions'=>$userConditions,
      'recursive'=>-1,
      'order'=>'User.first_name ASC, User.last_name ASC',
    ]);
    $userList=[];
    if (!empty($users)){
      foreach ($users as $user){
        $userList[$user['User']['id']]=$user['User']['first_name'].' '.$user['User']['last_name'];
      }
    }
    return $userList;
  }

  public function getUserById($userId){
    return $this->find('first',[
      'conditions'=>['User.id'=>$userId],
      'recursive'=>-1,
    ]);
  }  
  public function getUserRoleId($userId){
    $user=$this->getUserById($userId);
    return empty($user)?0:$user['User']['role_id'];
  }
  public function getUserAbbreviation($userId){
    $user=$this->getUserById($userId);
    //pr($user);
    return empty($user)?"":$user['User']['abbreviation'];
  }
  
  public function getAllActiveVendorAdminUsersForPlant($plantId = 0){
    $userConditions=[
      'User.bool_active'=>true,
      'User.bool_test'=>false,
      'User.role_id' => [ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING,ROLE_FACTURACION],
    ];
    if ($plantId > 0){
      $userIdsForPlant=array_keys($this->UserPlant->getUsersForPlant($plantId));
      $userConditions['User.id']=$userIdsForWarehouse;
    }
    return $this->find('all',[
      'fields'=>['User.id','User.first_name','User.last_name'],
      'conditions'=>$userConditions,
      'recursive'=>-1,
      'order'=>'User.first_name ASC, User.last_name ASC',
    ]);
  }
  public function getActiveVendorAdminUserListForPlant($plantId){
    $users=$this->getAllActiveVendorAdminUsersForPlant($plantId);
    $userList=[];
    if (!empty($users)){
      foreach ($users as $user){
        $userList[$user['User']['id']]=$user['User']['first_name'].' '.$user['User']['last_name'];
      }
    }
    return $userList;
  }
  

	public function bindNode($user) {
		return array('model' => 'Role', 'foreign_key' => $user['User']['role_id']);
	}
	
  public function parentNode() {
        if (!$this->id && empty($this->data)) {
            return null;
        }
        if (isset($this->data['User']['role_id'])) {
            $roleId = $this->data['User']['role_id'];
        } 
		else {
            $roleId = $this->field('role_id');
        }
        if (!$roleId) {
            return null;
        } 
		else {
            return array('Role' => array('id' => $roleId));
        }
    }

	public function beforeSave($options = array()) {
		//echo "executing beforesave<br/>";
		//pr($this->data[$this->alias]);
		
		if (array_key_exists('pwd',$this->data[$this->alias])){
			if (!empty($this->data[$this->alias]['pwd'])){
				//echo "now I am encrypting the password with the AuthComponent for the pwd";
				$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['pwd']);
				//pr($this->request->data[$this->alias]);
			}
			else {
				//if password is not set, auth data is taken into account
        //echo "getting the password from the user id";
				unset($this->data[$this->alias]['password']);
				if(!empty($this->data[$this->alias]['id'])){
					$currentUser=$this->find('first',array(
						'conditions'=>array(
							'User.id'=>$this->data[$this->alias]['id'],
						),
					));
          //pr($currentUser);
					if (!empty($currentUser)){
						$this->data[$this->alias]['password'] = $currentUser[$this->alias]['password'];
					}
          //pr($this->request->data[$this->alias]);
				}
			}
		}
		elseif (array_key_exists('password',$this->data[$this->alias])){
			if (!empty($this->data[$this->alias]['password'])){
				//echo "now I am encrypting the password with the AuthComponent for the pwd";
				$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
				//pr($this->request->data[$this->alias]);
			}
		}
		
		//echo "printing the request data<br/>";
		//pr($this->data[$this->alias]);
        return true;
	}
	
	public $validate = array(
		'username' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		//'password' => array(
		//	'notEmpty' => array(
		//		'rule' => array('notEmpty'),
		//		//'message' => 'Your custom message here',
		//		//'allowEmpty' => false,
		//		//'required' => false,
		//		//'last' => false, // Stop validation after this rule
		//		//'on' => 'create', // Limit validation to 'create' or 'update' operations
		//	),
		//),
		'role_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	
	public $belongsTo = array(
    'Client' => array(
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Role' => array(
			'className' => 'Role',
			'foreignKey' => 'role_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public $hasMany = array(
    'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'UserLog' => [
			'className' => 'UserLog',
			'foreignKey' => 'user_id',
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
    'UserPlant' => [
			'className' => 'UserPlant',
			'foreignKey' => 'user_id',
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
    'UserWarehouse' => [
			'className' => 'UserWarehouse',
			'foreignKey' => 'user_id',
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
    'QuotationRegistered' => [
			'className' => 'Quotation',
			'foreignKey' => 'record_user_id',
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
    'QuotationSelling' => [
			'className' => 'Quotation',
			'foreignKey' => 'vendor_user_id',
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
    'QuotationCreditAuthorizing' => [
			'className' => 'Quotation',
			'foreignKey' => 'credit_authorization_user_id',
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
    'ThirdPartyUser' => [
			'className' => 'ThirdPartyUser',
			'foreignKey' => 'user_id',
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
    'CreatorThirdParties' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'creator_user_id',
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
    'OwnerThirdParties' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'owner_user_id',
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
	);

}
