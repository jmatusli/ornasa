<?php
App::uses('AppModel', 'Model');

class ProductNature extends AppModel {

  public $displayField='name';
  
  public function getProductNatureList($excludedProductNatureIds=[]){
    $conditions=[];
    if (!empty($excludedProductNatureIds)){
      $conditions['ProductNature.id !=']=$excludedProductNatureIds;
    }
    $productNatures=$this->find('list',[
      'conditions'=>$conditions,
      'order'=>['ProductNature.list_order ASC','ProductNature.name ASC'],
    ]);
    return $productNatures;
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
  
  public $belongsTo = [
  /*
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'fields' => '',
			'order' => ''
		],
    */
  ];  
  public $hasMany = [
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_nature_id',
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
