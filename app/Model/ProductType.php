<?php
App::uses('AppModel', 'Model');

class ProductType extends AppModel {
	var $displayField="name";

  public function getProductTypes(){
    $productTypes = $this->find('list',[
      'order'=>'ProductType.name ASC'
    ]);
    return $productTypes;
  }
  
  public function getProductTypeIdsForCategory($productCategoryId){
    return $this->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>[
        'ProductType.product_category_id'=>$productCategoryId,
      ],
      'order'=>'ProductType.name ASC'
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
	];


	public $belongsTo = [
		'ProductCategory' => [
			'className' => 'ProductCategory',
			'foreignKey' => 'product_category_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'AccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_type_id',
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
    'PlantProductType' => [
			'className' => 'PlantProductType',
			'foreignKey' => 'product_type_id',
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
