<?php
App::uses('AppModel', 'Model');

class Unit extends AppModel {

	public $displayField = 'abbreviation';
  
  public function getUnitList($unitIds=[]){
    $conditions=[];
    if (!empty($unitIds)){
      $conditions['Unit.id']=$unitIds; 
    }  
    return $this->find('list',[
      'conditions'=>$conditions,
      'order'=>'list_order ASC',
    ]);  
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
		'abbreviation' => [
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
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'unit_id',
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
