<?php
App::uses('AppModel', 'Model');

class ProductionRunType extends AppModel {
	var $displayField="name";

	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				'message' => 'Se debe especificar el nombre del tipo de producción',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $hasMany = array(
		'ProductionRun' => array(
			'className' => 'ProductionRun',
			'foreignKey' => 'production_run_type_id',
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
	);

}
