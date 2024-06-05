<?php
App::uses('AppModel', 'Model');
/**
 * StockMovement Model
 *
 * @property Order $Order
 * @property Product $Product
 * @property StockItem $StockItem
 */
class StockMovement extends AppModel {
  
  function getTransferCode($userName){
    $transferCode="TRANS_000001";
    $lastTransfer=$this->find('first',[
      'fields'=>['StockMovement.transfer_code'],
      'conditions'=>['StockMovement.bool_transfer'=>true,],
      'order'=>['StockMovement.transfer_code' => 'desc'],
    ]);
    if (!empty($lastTransfer)){
      $transferNumber=substr($lastTransfer['StockMovement']['transfer_code'],6,6)+1;
      $transferCode="TRANS_".str_pad($transferNumber,6,"0",STR_PAD_LEFT)."_".$userName;
    }
    return $transferCode;
  }
  
  function getAdjustmentCode($userName){
    $lastAdjustment=$this->find('first',[
			'fields'=>['StockMovement.adjustment_code'],
			'conditions'=>['bool_adjustment'=>true,],
			'order'=>['StockMovement.adjustment_code' => 'desc'],
		]);
    if (!empty($lastAdjustment)){
      $adjustmentNumber=substr($lastAdjustment['StockMovement']['adjustment_code'],4,6)+1;
      $adjustmentCode="AJU_".str_pad($adjustmentNumber,6,"0",STR_PAD_LEFT)."_".$userName;
    }
    else  {
      $adjustmentCode="AJU_000001_".$userName;
    }
		return $adjustmentCode;
  }

	function getTotalMovement($product_category_id = 1, $startdate=null, $enddate = null){
		//$this->recursive=2;
		return $this->find('all', [
			'fields' => [
				'StockMovement.product_id',
				'Product.name',
				'StockMovement.product_quantity',
				'SUM(StockMovement.product_quantity*StockMovement.product_unit_price) AS total_value',
			],
			'conditions' => [
				'StockMovement.movement_date >'=>$startDate,
				'StockMovement.movement_date <='=>$endDate,
			],
			'group' => 'StockMovement.product_id', 
		]);
	}

	public $validate = [
		'movement_date' => [
			'datetime' => [
				'rule' => ['datetime'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'description' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		/*
		'product_quantity' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		*/
		/*
		'product_unit_price' => [
			'numeric' => [
				'rule' => ['decimal',8],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'product_total_price' => [
			'numeric' => [
				'rule' => ['decimal',8],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		*/
	];

	public $belongsTo = [
		'Order' => [
			'className' => 'Order',
			'foreignKey' => 'order_id',
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
		'ProductionResultCode' => [
			'className' => 'ProductionResultCode',
			'foreignKey' => 'production_result_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'StockItem' => [
			'className' => 'StockItem',
			'foreignKey' => 'stockitem_id',
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
    'Unit' => [
			'className' => 'Unit',
			'foreignKey' => 'unit_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];


	public $hasMany = [
		
	];

}
