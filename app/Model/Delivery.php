<?php
App::uses('AppModel', 'Model');

class Delivery extends AppModel {
  public $displayField='delivery_code';
  
  public function getDeliveryCode($warehouseId,$warehouseSeries){
    $lastDelivery = $this->find('first',[
			'fields'=>['delivery_code'],
      'conditions'=>[
        'Delivery.warehouse_id'=>$warehouseId,
      ],
      'recursive'=>-1,
			'order' => ['Delivery.delivery_code' => 'desc'],
		]);
		if ($lastDelivery!= null){
      $lastDeliveryCodeNumber=(int)substr($lastDelivery['Delivery']['delivery_code'],6,12);
      $newDeliveryCodeNumber=$lastDeliveryCodeNumber+1;
      $newDeliveryCode=$warehouseSeries.'_ED_'.str_pad($newDeliveryCodeNumber,6,'0',STR_PAD_LEFT);
		}
		else {
			$newDeliveryCode=$warehouseSeries."_ED_000001";
		}
    return $newDeliveryCode;
  }

  public function getDeliveryById($deliveryId){
    return $this->find('first',[
      'conditions'=>['Delivery.id'=>$deliveryId],
      'recursive'=>-1,
    ]);
  }

	public $validate = [
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
		'sales_order_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'order_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'planned_delivery_datetime' => [
			'datetime' => [
				'rule' => ['datetime'],
			],
		],
		'actual_delivery_datetime' => [
			'datetime' => [
				'rule' => ['datetime'],
			],
		],
		'delivery_status_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'delivery_address' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
			],
		],
		'driver_user_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'vehicle_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
	];

	public $belongsTo = [
		'RegisteringUser' => [
			'className' => 'User',
			'foreignKey' => 'registering_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'SalesOrder' => [
			'className' => 'SalesOrder',
			'foreignKey' => 'sales_order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Order' => [
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'DeliveryStatus' => [
			'className' => 'DeliveryStatus',
			'foreignKey' => 'delivery_status_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'DriverUser' => [
			'className' => 'User',
			'foreignKey' => 'driver_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Vehicle' => [
			'className' => 'Vehicle',
			'foreignKey' => 'vehicle_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'DeliveryRemark' => [
			'className' => 'DeliveryRemark',
			'foreignKey' => 'delivery_id',
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
