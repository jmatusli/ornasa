<?php
App::uses('AppModel', 'Model');

class UserPageRight extends AppModel {
  
  public function hasUserPageRight($pageRightCode,$roleId,$userId,$controller,$action){
    if (empty($pageRightCode) || empty($controller) || empty($action)){
      return 0;
    }
    $pageRightId=$this->PageRight->getIdByCode($pageRightCode);

    $conditions=[
      'UserPageRight.page_right_id'=>$pageRightId,
      'UserPageRight.controller'=>$controller,
      'UserPageRight.action'=>$action,
    ];
    $userPageRightConditions=$conditions;
    if ($userId > 0){
      $userPageRightConditions['UserPageRight.user_id']=$userId;
      //pr($userPageRightConditions);
      $userPageRight=$this->find('first',[
        'conditions'=>$userPageRightConditions,
        'recursive'=>-1,
        'order'=>'assignment_datetime DESC',
      ]);
    } 
    if (empty($userPageRight)){
      $userPageRightConditions=$conditions;
      if ($roleId > 0){
        $userPageRightConditions['UserPageRight.role_id']=$roleId;
        //pr($userPageRightConditions);
        $userPageRight=$this->find('first',[
          'conditions'=>$userPageRightConditions,
          'recursive'=>-1,
          'order'=>'assignment_datetime DESC',
        ]);
      } 
      if (empty($userPageRight)){
        return 0;
      }
    }
    //pr($userPageRight);
    return $userPageRight['UserPageRight']['bool_allowed'];  
  }

	public $validate = [
		'permission_datetime' => [
			'datetime' => [
				'rule' => ['datetime'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'page_right_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'role_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
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
		'bool_allowed' => [
			'boolean' => [
				'rule' => ['boolean'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'controller' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'action' => [
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

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	public $belongsTo = [
		'PageRight' => [
			'className' => 'PageRight',
			'foreignKey' => 'page_right_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Role' => [
			'className' => 'Role',
			'foreignKey' => 'role_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'LoggingUser' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
