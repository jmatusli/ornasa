<?php
App::uses('AppModel', 'Model');
/**
 * PurchaseOrderState Model
 *
 * @property PurchaseOrder $PurchaseOrder
 */
class PurchaseOrderState extends AppModel {
  
  public $displayField='short_description';
  
  public function getPurchaseOrderStateList($boolOrderAsc=true){
    $purchaseOrderStates=$this->find('list',[
      'order'=>($boolOrderAsc?'list_order ASC':'list_order DESC')
    ]);
    return $purchaseOrderStates;
  }

  public function getPurchaseOrderStateColors(){
    $purchaseOrderColors=$this->find('list',[
      'fields'=>'hex_color',
      'order'=>'list_order ASC',
    ]);
    return $purchaseOrderColors;
  }

	public $validate = [
		'code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'short_description' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'long_description' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'list_order' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'hex_color' => [
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

	public $hasMany = [
		'PurchaseOrder' => [
			'className' => 'PurchaseOrder',
			'foreignKey' => 'purchase_order_state_id',
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
	] ;

}
