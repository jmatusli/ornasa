<?php
App::uses('AppModel', 'Model');

class Operator extends AppModel {
	var $displayField="name";
  
  public function getOperatorListForPlant($plantId){
    $operators=$this->find('list',[
      'conditions'=>[
        'Operator.bool_active'=>true,
        'Operator.plant_id'=>$plantId,
      ],
      'order'=>'Operator.name ASC',
    ]);
    return $operators;
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
  
  public $belongsTo = [
		'Plant' => [
			'className' => 'Plant',
			'foreignKey' => 'plant_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'ProductionRun' => [
			'className' => 'ProductionRun',
			'foreignKey' => 'operator_id',
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
