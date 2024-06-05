<?php
App::uses('AppModel', 'Model');

class ProductThresholdVolume extends AppModel {

  function getThresholdVolume($productId,$priceClientCategoryId,$volumeDateTime){
    $productThresholdVolume=$this->find('first',[
      'conditions'=>[
        'DATE(volume_datetime) <='=>$volumeDateTime,
        'product_id'=>$productId,
        'price_client_category_id'=>$priceClientCategoryId,
      ],
      'recursive'=>-1,
      'limit'=>1,
      'order'=>['volume_datetime DESC'],
    ]); 
    if (empty($productThresholdVolume)){
      return 0;
    }
    return $productThresholdVolume['ProductThresholdVolume']['threshold_volume'];
  }
  
  function getCompositeThresholdVolume($productId,$rawMaterialId,$priceClientCategoryId,$volumeDateTime){
    $conditions=[
      'DATE(volume_datetime) <='=>$volumeDateTime,
      'product_id'=>$productId,
      'raw_material_id'=>$rawMaterialId,
      'price_client_category_id'=>$priceClientCategoryId,
    ];
    //pr($conditions);
    $productThresholdVolume=$this->find('first',[
      'conditions'=>$conditions,
      'recursive'=>-1,
      'limit'=>1,
      'order'=>['volume_datetime DESC'],
    ]); 
    if (empty($productThresholdVolume)){
      return 0;
    }
    return $productThresholdVolume['ProductThresholdVolume']['threshold_volume'];
  }

	public $belongsTo = [
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'RawMaterial' => [
			'className' => 'Product',
			'foreignKey' => 'raw_material_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'PriceClientCategory' => [
			'className' => 'PriceClientCategory',
			'foreignKey' => 'price_client_category_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
