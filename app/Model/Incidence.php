<?php
App::uses('AppModel', 'Model');
/**
 * Incidence Model
 *
 * @property CreatingUser $CreatingUser
 */
class Incidence extends AppModel {

  public $displayField="name";
	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
    'creating_user_id' => array(
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

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'CreatingUser' => array(
			'className' => 'User',
			'foreignKey' => 'creating_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
  public $hasMany = array(
		'ProductionRun' => array(
			'className' => 'ProductionRun',
			'foreignKey' => 'incidence_id',
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
