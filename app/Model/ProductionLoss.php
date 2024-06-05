<?php
App::uses('AppModel', 'Model');
/**
 * ProductionLoss Model
 *
 * @property ProductionRun $ProductionRun
 * @property Unit $Unit
 * @property ProductionResultCode $ProductionResultCode
 */
class ProductionLoss extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';


	//The Associations below have been created with all possible keys, those that are not needed can be removed

	public $belongsTo = [
		'ProductionRun' => [
			'className' => 'ProductionRun',
			'foreignKey' => 'production_run_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Unit' => [
			'className' => 'Unit',
			'foreignKey' => 'unit_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'ProductionResultCode' => [
			'className' => 'ProductionResultCode',
			'foreignKey' => 'production_result_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
