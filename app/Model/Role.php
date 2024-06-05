<?php
App::uses('AppModel', 'Model');

class Role extends AppModel {
	var $displayField="name";
  public $actsAs = ['Acl' => ['type' => 'requester']];

  public function parentNode() {
      return null;
  }

  public function getRoles(){
    $roles=$this->find('list',[
			'fields'=>[
				'Role.id',
				'Role.name',
			],
			'order'=>'Role.name',			
		]);
    return $roles;
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
		'User' => [
			'className' => 'User',
			'foreignKey' => 'role_id',
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
