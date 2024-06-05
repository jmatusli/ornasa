<?php
App::uses('AppModel', 'Model');
/**
 * DeliveryRemark Model
 *
 * @property Delivery $Delivery
 * @property RegisteringUser $RegisteringUser
 */
class DeliveryRemark extends AppModel {

	public $validate = [
		'delivery_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'registering_user_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'remark_datetime' => [
			'datetime' => [
				'rule' => ['datetime'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'remark_text' => [
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
		'Delivery' => [
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'RegisteringUser' => [
			'className' => 'User',
			'foreignKey' => 'registering_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
