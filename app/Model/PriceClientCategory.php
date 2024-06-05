<?php
App::uses('AppModel', 'Model');

class PriceClientCategory extends AppModel {

	public $displayField = 'name';
  
  public function getPriceClientCategoryList($priceClientCategoryIds=[]){
    $conditions=[];
    if (!empty($priceClientCategoryIds)){
      $conditions['PriceClientCategory.id']=$priceClientCategoryIds;
    }
    $priceClientCategories=$this->find('list',[
      'conditions'=>$conditions,
      'order'=>'category_number',
    ]);
    return $priceClientCategories;
  }

  public $hasMany = [
		'Client' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'price_client_category_id',
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
    'ProductPriceLog' => [
			'className' => 'ProductPriceLog',
			'foreignKey' => 'price_client_category_id',
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
