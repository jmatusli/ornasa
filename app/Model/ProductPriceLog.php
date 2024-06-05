<?php
App::uses('AppModel', 'Model');

class ProductPriceLog extends AppModel {

  function getLatestPrice($productId,$rawMaterialId=null){
    $latestProductPriceLog=$this->find('first',[
			'fields'=>['ProductPriceLog.price'],
      'conditions'=>[
        'ProductPriceLog.product_id'=>$productId,
        'ProductPriceLog.raw_material_id'=>$rawMaterialId,
      ],
			'order'=>'ProductPriceLog.price_datetime DESC',
		]);
		if (!empty($latestProductPriceLog)){
      return $latestProductPriceLog['ProductPriceLog']['price'];  
		}
    return 0;
	}
  
  function getLatestNonBottlePriceForClient($productId,$clientId){
    $latestProductPriceLog=$this->find('first',[
			'fields'=>['ProductPriceLog.price'],
      'conditions'=>[
        'ProductPriceLog.product_id'=>$productId,
        'ProductPriceLog.client_id'=>$clientId,
      ],
			'order'=>'ProductPriceLog.price_datetime DESC',
		]);
		if (!empty($latestProductPriceLog)){
      return $latestProductPriceLog['ProductPriceLog']['price'];  
		}
    return 0;
	}
  
  function getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateTime){
    $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTime));
    $priceDateTimePlusOne= date( "Y-m-d", strtotime( $priceDateTime."+1 days" ) );
    $conditions=[
      'ProductPriceLog.product_id'=>$productId,
      'ProductPriceLog.client_id'=>$clientId,
      'ProductPriceLog.price_client_category_id'=>null,
      'DATE(ProductPriceLog.price_datetime) <'=>$priceDateTimePlusOne,
    ];
    //pr($conditions);
    $latestProductPriceLog=$this->find('first',[
			'fields'=>['ProductPriceLog.id','ProductPriceLog.price','ProductPriceLog.price_datetime',],
      'conditions'=>$conditions,
			'order'=>'ProductPriceLog.id DESC,ProductPriceLog.id DESC',
		]);
    //pr($latestProductPriceLog);
		if (!empty($latestProductPriceLog)){
      return [
        'id'=>$latestProductPriceLog['ProductPriceLog']['id'],
        'price'=>$latestProductPriceLog['ProductPriceLog']['price'],
        'price_datetime'=>$latestProductPriceLog['ProductPriceLog']['price_datetime'],
      ];
		}
    return [
      'id'=>0,
      'price'=>0,
      'price_datetime'=>date('Y-m-d H:i:s')
    ];
	}
  
  function getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTime){
    $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTime));
    $priceDateTimePlusOne= date( "Y-m-d", strtotime( $priceDateTime."+1 days" ) );
    $conditions=[
      'ProductPriceLog.product_id'=>$productId,
      'ProductPriceLog.client_id'=>null,
      'ProductPriceLog.price_client_category_id'=>$priceClientCategoryId,
      'DATE(ProductPriceLog.price_datetime) <'=>$priceDateTimePlusOne,
    ];
    //pr($conditions);
    $latestProductPriceLog=$this->find('first',[
			'fields'=>['ProductPriceLog.price','ProductPriceLog.price_datetime',],
      'conditions'=>$conditions,
			'order'=>'ProductPriceLog.id DESC',
		]);
    //pr($latestProductPriceLog);
		if (!empty($latestProductPriceLog)){
      return [
        'price'=>$latestProductPriceLog['ProductPriceLog']['price'],
        'price_datetime'=>$latestProductPriceLog['ProductPriceLog']['price_datetime'],
      ];
		}
    return [
      'price'=>0,
      'price_datetime'=>date('Y-m-d H:i:s')
    ];
	}
  
  function getNonBottleClientPriceClientCategory($productId,$clientPriceClientCategoryId,$priceDateTime){
    $resultArray=[
      'price_client_category_id'=>0,
      'category_price'=>0,
      'category_price_datetime'=>null,
    ];
    while ($clientPriceClientCategoryId > 0){
      $priceClientCategoryForProduct=$this->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$clientPriceClientCategoryId,$priceDateTime);
      if (!empty($priceClientCategoryForProduct) && $priceClientCategoryForProduct['price'] > 0){
        $resultArray=[
          'price_client_category_id'=>$clientPriceClientCategoryId,
          'category_price'=>$priceClientCategoryForProduct['price'],
          'category_price_datetime'=>$priceClientCategoryForProduct['price_datetime'],
        ];
        break;
      }
      else {
        $clientPriceClientCategoryId--;
      }
    }
    return $resultArray;
  }  

  function getLatestPriceForRawMaterialForClient($productId,$rawMaterialId,$clientId){
    $latestProductPriceLog=$this->find('first',[
			'fields'=>['ProductPriceLog.price'],
      'conditions'=>[
        'ProductPriceLog.product_id'=>$productId,
        'ProductPriceLog.raw_material_id'=>$rawMaterialId,
        'ProductPriceLog.client_id'=>$clientId,
      ],
			'order'=>'ProductPriceLog.price_datetime DESC',
		]);
		if (!empty($latestProductPriceLog)){
      return $latestProductPriceLog['ProductPriceLog']['price'];  
		}
    return 0;
	}
  
  function getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateTime){
    $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTime));
    $priceDateTimePlusOne= date( "Y-m-d", strtotime( $priceDateTime."+1 days" ) );  
    $latestProductPriceLog=$this->find('first',[
			'fields'=>['ProductPriceLog.id','ProductPriceLog.price','ProductPriceLog.price_datetime',],
      'conditions'=>[
        'ProductPriceLog.product_id'=>$productId,
        'ProductPriceLog.raw_material_id'=>$rawMaterialId,
        'ProductPriceLog.client_id'=>$clientId,
        'ProductPriceLog.price_client_category_id'=>null,
        'DATE(ProductPriceLog.price_datetime) <'=>$priceDateTimePlusOne,
      ],
			'order'=>'ProductPriceLog.price_datetime DESC,ProductPriceLog.id DESC',
		]);
		if (!empty($latestProductPriceLog)){
      return [
        'id'=>$latestProductPriceLog['ProductPriceLog']['id'],
        'price'=>$latestProductPriceLog['ProductPriceLog']['price'],
        'price_datetime'=>$latestProductPriceLog['ProductPriceLog']['price_datetime'],
      ];
		}
    return [
      'id'=>0,
      'price'=>0,
      'price_datetime'=>date('Y-m-d H:i:s')
    ];
	}

  function getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTime){
    $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTime));
    $priceDateTimePlusOne= date( "Y-m-d", strtotime( $priceDateTime."+1 days" ) );  
    $latestProductPriceLog=$this->find('first',[
			'fields'=>['ProductPriceLog.price','ProductPriceLog.price_datetime',],
      'conditions'=>[
        'ProductPriceLog.product_id'=>$productId,
        'ProductPriceLog.raw_material_id'=>$rawMaterialId,
        'ProductPriceLog.client_id'=>null,
        'ProductPriceLog.price_client_category_id'=>$priceClientCategoryId,
        'DATE(ProductPriceLog.price_datetime) <'=>$priceDateTimePlusOne,
      ],
      'recursive'=>-1,
			'order'=>'ProductPriceLog.price_datetime DESC',
		]);
		if (!empty($latestProductPriceLog)){
      return [
        'price'=>round($latestProductPriceLog['ProductPriceLog']['price'],4),
        'price_datetime'=>$latestProductPriceLog['ProductPriceLog']['price_datetime'],
      ];
		}
    return [
      'price'=>0,
      'price_datetime'=>date('Y-m-d H:i:s')
    ];
	}

  function getBottleClientPriceClientCategory($productId,$rawMaterialId,$clientPriceClientCategoryId,$priceDateTime){
    $resultArray=[
      'price_client_category_id'=>0,
      'category_price'=>0,
      'category_price_datetime'=>null,
    ];
    while ($clientPriceClientCategoryId > 0){
      $priceClientCategoryForProduct=$this->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$clientPriceClientCategoryId,$priceDateTime);
      if (!empty($priceClientCategoryForProduct) && $priceClientCategoryForProduct['price'] > 0){
        $resultArray=[
          'price_client_category_id'=>$clientPriceClientCategoryId,
          'category_price'=>$priceClientCategoryForProduct['price'],
          'category_price_datetime'=>$priceClientCategoryForProduct['price_datetime'],
        ];
        break;
      }
      else {
        $clientPriceClientCategoryId--;
      }
    }
    return $resultArray;
  }  

  function getApplicableProductPriceForClient($productId,$rawMaterialId,$clientId,$priceDateTime){
    $applicablePrice=0;
    if ($rawMaterialId>0){
      if ($clientId>0){
        $clientSpecificPriceLog=$this->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateTime);
        if ($clientSpecificPriceLog['price'] > 0){
          return $clientSpecificPriceLog['price'];
        }
      }
      if ($clientId > 0){
        $priceClientCategoryId=$this->Client->getPriceClientCategory($clientId);
      }
      else {
        $priceClientCategoryId=PRICE_CLIENT_CATEGORY_GENERAL;
      }
      //pr($priceClientCategoryId);
      $priceClientCategorySpecificPriceLog=$this->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTime);
      //pr($priceClientCategorySpecificPriceLog);
      if ($priceClientCategorySpecificPriceLog['price'] > 0 || $priceClientCategoryId == PRICE_CLIENT_CATEGORY_GENERAL){
        $applicablePrice=$priceClientCategorySpecificPriceLog['price'];
      }
      else {
        if ($priceClientCategoryId > PRICE_CLIENT_CATEGORY_GENERAL){
          do {
            $priceClientCategoryId-=1;
            $priceClientCategorySpecificPriceLog=$this->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTime);
          }
          while ($priceClientCategorySpecificPriceLog['price'] == 0 && $priceClientCategory > PRICE_CLIENT_CATEGORY_GENERAL);  
          $applicablePrice=$priceClientCategorySpecificPriceLog['price'];  
        }
      }
    }
    else {
      if ($clientId > 0){
        $clientSpecificPriceLog=$this->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateTime);
        if ($clientSpecificPriceLog['price'] > 0){
          return $clientSpecificPriceLog['price'];
        }
      }
      if ($clientId > 0){
        $priceClientCategoryId=$this->Client->getPriceClientCategory($clientId);
      }
      else {
        $priceClientCategoryId=PRICE_CLIENT_CATEGORY_GENERAL;
      }      
      
      $priceClientCategorySpecificPriceLog=$this->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTime);
      if ($priceClientCategorySpecificPriceLog['price'] > 0 || $priceClientCategoryId == PRICE_CLIENT_CATEGORY_GENERAL){
        $applicablePrice=$priceClientCategorySpecificPriceLog['price'];
      }
      else {
        if ($priceClientCategoryId > PRICE_CLIENT_CATEGORY_GENERAL){
          do {
            $priceClientCategoryId-=1;
            $priceClientCategorySpecificPriceLog=$this->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTime);
          }
          while ($priceClientCategorySpecificPriceLog['price'] == 0 && $priceClientCategory > PRICE_CLIENT_CATEGORY_GENERAL);  
          $applicablePrice=$priceClientCategorySpecificPriceLog['price'];  
        }
      }
    }
		return $applicablePrice;
	}

  function getClientAndCategoryPricesForClient($productId,$rawMaterialId,$clientId,$priceDateTime){
    $prices=[];
    $allPriceClientCategories=$this->PriceClientCategory->getPriceClientCategoryList();
    if ($rawMaterialId>0){
      if ($clientId>0){
        $clientSpecificPriceLog=$this->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateTime);
        if ($clientSpecificPriceLog['price'] > 0){
          $prices['client_price']=$clientSpecificPriceLog['price'];
        }
      }
      if (!array_key_exists('client_price',$prices)){
        $prices['client_price']=0;
      }
      
      if (!empty($allPriceClientCategories)){
        foreach ($allPriceClientCategories as $priceClientCategoryId =>$priceClientCategoryName){
          $priceClientCategorySpecificPriceLog=$this->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTime);
          //pr($priceClientCategorySpecificPriceLog);
          $prices['PriceClientCategory'][$priceClientCategoryId]['category_price']=$priceClientCategorySpecificPriceLog['price'];
        }
      }
    }
    else {
      // non fabricated products  
      if ($clientId > 0){
        $clientSpecificPriceLog=$this->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateTime);
        if ($clientSpecificPriceLog['price'] > 0){
          $prices['client_price']=$clientSpecificPriceLog['price'];
        }
      }
      if (!array_key_exists('client_price',$prices)){
        $prices['client_price']=0;
      }
      
      if (!empty($allPriceClientCategories)){
        foreach ($allPriceClientCategories as $priceClientCategoryId =>$priceClientCategoryName){
          $priceClientCategorySpecificPriceLog=$this->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTime);
          //pr($priceClientCategorySpecificPriceLog);
          $prices['PriceClientCategory'][$priceClientCategoryId]['category_price']=$priceClientCategorySpecificPriceLog['price'];
        }
      }
    }
		return $prices;
	}

	public $validate = [
		'price_datetime' => [
			'datetime' => [
				'rule' => ['datetime'],
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
		'user_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'currency_id' => [
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
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Currency' => [
			'className' => 'Currency',
			'foreignKey' => 'currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Client' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
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
	];
}
