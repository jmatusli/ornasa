<?php
App::uses('AppModel', 'Model');

class DeliveryStatus extends AppModel {

	public $displayField = 'code';
  
  function getDeliveryStatusList($deliveryStatusId=0){
    $conditions=[];
    if ($deliveryStatusId > 0){
      $conditions['DeliveryStatus.id']=$deliveryStatusId;
    }
    //pr($conditions);
    return $this->find('list',[
      'conditions'=>$conditions,
      'order'=>['DeliveryStatus.list_order'=>'ASC'],
    ]);
  }
  function getEffectiveDeliveryStatusList(){
    $conditions=['DeliveryStatus.id >'=>DELIVERY_STATUS_UNASSIGNED,];
    //pr($conditions);
    return $this->find('list',[
      'conditions'=>$conditions,
      'order'=>['DeliveryStatus.list_order'=>'ASC'],
    ]);
  }

  public $hasMany = [
		'Delivery' => [
			'className' => 'Delivery',
			'foreignKey' => 'delivery_status_id',
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
