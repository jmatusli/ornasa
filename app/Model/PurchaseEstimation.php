<?php
App::uses('AppModel', 'Model');
/**
 * Request Model
 *
 * @property Client $Client
 */
class PurchaseEstimation extends AppModel {

  public function getNewPurchaseEstimationCode($clientId,$purchaseEstimationDateString=""){
    if (empty($requestDate)){
      $purchaseEstimationDay=date('d');
      $purchaseEstimationMonth=date('m');
      $purchaseEstimationYear=date('Y');
      $purchaseEstimationDateString=$purchaseEstimationDay.$purchaseEstimationMonth.$purchaseEstimationYear;
    }
    $newPurchaseEstimationCode="";
    $this->recursive=-1;
		$clientModel=ClassRegistry::init('ThirdParty');
		$client=$clientModel->find('first',[
			'fields'=>['ThirdParty.id','ThirdParty.company_name'],
			'conditions'=>[
				'ThirdParty.id'=>$clientId
      ],    
		]);
    
    if (!empty($client)){
      $newPurchaseEstimationCode=$client['ThirdParty']['company_name']."_".$purchaseEstimationDateString."_";
      $latestPurchaseEstimation=$this->find('first',[
        'conditions'=>[
          'PurchaseEstimation.client_id'=>$clientId
        ],
        'order'=>'PurchaseEstimation.purchase_estimation_code'
      ]);
      if (empty($latestPurchaseEstimation)){
        $newPurchaseEstimationCode.="00001";
      }
      else {
        $latestPurchaseEstimationCodeOrdinalNumber=intval(substr($latestPurchaseEstimation['PurchaseEstimation']['purchase_estimation_code'],(strlen($client['ThirdParty']['company_name'])+8)));
        $newPurchaseEstimationCode.=str_pad(($latestPurchaseEstimationCodeOrdinalNumber+1),5,"0",STR_PAD_LEFT);  
      }
    }
    
    return $newPurchaseEstimationCode;
  }
  
  public function getPurchaseEstimation($clientId,$purchaseEstimationDateString="",$numberOfDays=100){
    if (empty($requestDate)){
      $purchaseEstimationDay=date('d');
      $purchaseEstimationMonth=date('m');
      $purchaseEstimationYear=date('Y');
      $purchaseEstimationDateString=$purchaseEstimationYear.$purchaseEstimationMonth.$purchaseEstimationDay;
    }
    
    $startDate=date("Y-m-d",strtotime($purchaseEstimationDateString."-".$numberOfDays." days"));
    $endDate=date( "Y-m-d", strtotime($purchaseEstimationDateString));
    $this->recursive=-1;
		$orderModel=ClassRegistry::init('Order');
    $conditions=[
      'Order.stock_movement_type_id'=> MOVEMENT_SALE,
      'Order.third_party_id'=>$clientId,
      'Order.order_date >='=> $startDate,
      'Order.order_date <='=> $endDate,
    ];
    //pr($conditions);
		$ordersOfLastXDays=$orderModel->find('all',[
      'fields'=>[
        'Order.order_date',
        'Order.order_code',
      ],
			'conditions'=>$conditions,    
      'contain'=>[
        'StockMovement'=>[
          'fields'=>[
            'StockMovement.order_id',
            'StockMovement.product_id',
            'StockMovement.production_result_code_id',
            'StockMovement.product_quantity',
            'StockMovement.product_unit_price',
            'StockMovement.product_total_price',
          ],
          'conditions'=>['StockMovement.product_quantity >'=>0],
          'Product'=>[
            'fields'=>['Product.name']
          ],
          'ProductionResultCode'=>[
            'fields'=>['ProductionResultCode.code']
          ],
          'StockItem'=>[
            'fields'=>['StockItem.raw_material_id'],
            'RawMaterial'=>[
              'fields'=>['RawMaterial.name']
            ]
          ]
        ]
      ],
      'order'=>'order_date DESC',
		]);
    //pr($ordersOfLastXDays);
    $processedProducts=[];
    //totalQuantity, numberOfTimesBought, lastTimeBoughtDaysAgo, lastUnitPricePaid
    //and then if lastTimeBought<30 or number>=2=>include averageQuantity in estimatedProducts
    $orderIdsProcessed=[];
    foreach ($ordersOfLastXDays as $sale){
      foreach ($sale['StockMovement'] as $stockMovement){
        //pr($stockMovement);
        $productId=$stockMovement['product_id'];
        $rawMaterialId=$stockMovement['StockItem']['raw_material_id'];
        $productionResultCodeId=$stockMovement['production_result_code_id'];
        if (array_key_exists($productId,$processedProducts) && array_key_exists($rawMaterialId,$processedProducts[$productId]) && array_key_exists($productionResultCodeId,$processedProducts[$productId][$rawMaterialId])){
          // array already present, sum up and modify contents
          $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['totalQuantity']+=$stockMovement['product_quantity'];
          if (!in_array($stockMovement['order_id'],$orderIdsProcessed)){
            $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['numberOfTimesBought']+=1;
            $orderIdsProcessed[]=$stockMovement['order_id'];
          }

          $orderDateTime=new DateTime($sale['Order']['order_date']);
          $currentDateTime= new DateTime(date('Y-m-d'));
          $daysAgoDiff=$currentDateTime->diff($orderDateTime);
          $daysAgo=$daysAgoDiff->days;
          if ($daysAgo < $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['lastTimeBoughtDaysAgo']){
            $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['lastTimeBoughtDaysAgo']=$daysAgo;
            $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['lastUnitPricePaid']=$stockMovement['product_unit_price'];
          }
        }
        else {
          $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['productName']=$stockMovement['Product']['name'];
          $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['rawMaterialName']=(!empty($stockMovement['StockItem']['RawMaterial'])?$stockMovement['StockItem']['RawMaterial']['name']:"");
          $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['productionResultCode']=(!empty($stockMovement['ProductionResultCode'])?$stockMovement['ProductionResultCode']['code']:"");
          $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['totalQuantity']=$stockMovement['product_quantity'];
          $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['numberOfTimesBought']=1;

          $orderDateTime=new DateTime($sale['Order']['order_date']);
          $currentDateTime= new DateTime(date('Y-m-d'));
          $daysAgoDiff=$currentDateTime->diff($orderDateTime);
          $daysAgo=$daysAgoDiff->days;          
          $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['lastTimeBoughtDaysAgo']=$daysAgo;
          $processedProducts[$productId][$rawMaterialId][$productionResultCodeId]['lastUnitPricePaid']=$stockMovement['product_unit_price'];
        }
      }
    }
    //pr($processedProducts);
    $estimatedProducts=[];
    //format of request products
    foreach ($processedProducts as $productId=>$rawMaterialArray){
      foreach ($rawMaterialArray as $rawMaterialId=>$productionResultCodeArray){
        foreach ($productionResultCodeArray as $productionResultCodeId=>$productData){
          if ($productData['lastTimeBoughtDaysAgo']<30 || $productData['numberOfTimesBought']>1){
            $estimatedProduct=[
              'product_id'=>$productId,
              'product_name'=>$productData['productName'],
              'raw_material_id'=>$rawMaterialId,
              'raw_material_name'=>$productData['rawMaterialName'],
              'production_result_code_id'=>$productionResultCodeId,
              'production_result_code'=>$productData['productionResultCode'],
              'product_quantity'=>round($productData['totalQuantity']/$productData['numberOfTimesBought']),
              'product_unit_price'=>$productData['lastUnitPricePaid'],
              'product_total_price'=>$productData['lastUnitPricePaid']*round($productData['totalQuantity']/$productData['numberOfTimesBought']),
              'description'=>''
            ];
            $estimatedProducts[]['PurchaseEstimationProduct']=$estimatedProduct;
          }
        }
      }
    }
    
    $estimationResult=[
      'clientId'=>$clientId,
      'startDate'=>$startDate,
      'endDate'=>$endDate,
      'numberOfDays'=>$numberOfDays,
      'pastPurchases'=>$ordersOfLastXDays,
      'processedProducts'=>$processedProducts,
      'estimatedProducts'=>$estimatedProducts
    ];
    
    return $estimationResult;
  }
  
	public $validate = array(
		'bool_annulled' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Client' => array(
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
  
  public $hasMany = [
		'PurchaseEstimationProduct' => [
			'className' => 'PurchaseEstimationProduct',
			'foreignKey' => 'purchase_estimation_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		]
  ];  
}
