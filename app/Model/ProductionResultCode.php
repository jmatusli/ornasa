<?php
App::uses('AppModel', 'Model');

class ProductionResultCode extends AppModel {

	var $displayField="code";
  
  function getProductionResultCodeList(){
    return $this->find('list');
  }

	public $validate = [
		'code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
			],
		],
	];

	public $hasMany = [
    'PlantProductionResultCode' => [
			'className' => 'PlantProductionResultCode',
			'foreignKey' => 'production_result_code_id',
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
		'StockItem' => [
			'className' => 'StockItem',
			'foreignKey' => 'production_result_code_id',
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
		'StockMovement' => [
			'className' => 'StockMovement',
			'foreignKey' => 'production_result_code_id',
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
