<?php
App::uses('AppModel', 'Model');

class WarehouseProduct extends AppModel {

  public function hasWarehouse($productId,$warehouseId){
    $warehouseProduct=$this->find('first',[
      'conditions'=>[
        'WarehouseProduct.product_id'=>$productId,
        'WarehouseProduct.warehouse_id'=>$warehouseId
      ],
      'recursive'=>-1,
      'order'=>'WarehouseProduct.id DESC',
    ]);
    if (empty($warehouseProduct)){
      return false;
    }
    return $warehouseProduct['WarehouseProduct']['bool_assigned'];
  }
  
  public function getProductIdsForWarehouse($warehouseId){
    $products=$this->Product->find('list',['fields'=>['Product.id']]);
    foreach ($products as $productId){
      if (!$this->hasWarehouse($productId,$warehouseId)){
        unset($products[$productId]);
      }
    }
    return $products;
  }
  
  public function getWarehouseIdsForProduct($productId){
    $warehouses=$this->Warehouse->find('list',['fields'=>['Warehouse.id']]);
    foreach ($warehouses as $warehouseId){
      //echo 'warehouseId is '.$warehouseId.'<br/>';
      //echo 'productId is '.$productId.'<br/>';
      if (!$this->hasWarehouse($productId,$warehouseId)){
        unset($warehouses[$warehouseId]);
      }
    }
    //pr($warehouses);
    return $warehouses;
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
		'warehouse_id' => [
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
		'Warehouse' => [
			'className' => 'Warehouse',
			'foreignKey' => 'warehouse_id',
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
