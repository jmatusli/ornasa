<?php
App::uses('AppModel', 'Model');

class PurchaseEstimationProduct extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'PurchaseEstimation' => array(
			'className' => 'PurchaseEstimation',
			'foreignKey' => 'purchase_estimation_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Product' => array(
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
    'RawMaterial' => array(
			'className' => 'Product',
			'foreignKey' => 'raw_material_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'ProductionResultCode' => array(
			'className' => 'ProductionResultCode',
			'foreignKey' => 'production_result_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
