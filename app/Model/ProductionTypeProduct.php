<?php
App::uses('AppModel', 'Model');
/**
 * ProductionTypeProduct Model
 *
 * @property ProductionType $ProductionType
 * @property Product $Product
 */
class ProductionTypeProduct extends AppModel {

  public function hasProductionType($productId,$productionTypeId){
    $productionTypeProduct=$this->find('first',[
      'conditions'=>[
        'ProductionTypeProduct.product_id'=>$productId,
        'ProductionTypeProduct.production_type_id'=>$productionTypeId,
      ],
      'recursive'=>-1,
      'order'=>'ProductionTypeProduct.id DESC',
    ]);
    if (empty($productionTypeProduct)){
      return false;
    }
    return $productionTypeProduct['ProductionTypeProduct']['bool_assigned'];
  }

	public $validate = [
		'assignment_datetime' => [
			'datetime' => [
				'rule' => ['datetime'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'production_type_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'product_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	public $belongsTo = [
		'ProductionType' => [
			'className' => 'ProductionType',
			'foreignKey' => 'production_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
