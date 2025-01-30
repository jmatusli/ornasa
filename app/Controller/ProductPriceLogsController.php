<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ProductPriceLogsController extends AppController {
	
  public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel');
	
  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('getPriceTableForProduct','deletePricesForProduct','saveProductPriceLog','getproductprice','sortByRawMaterial','guardarResumenPrecios');
	}
  
  public function getPriceTableForProduct(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
    $this->layout = "ajax";// just in case to reduce the error message;
		
    $this->loadModel('User');  
    
    $userRoleId=$this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $priceClientCategoryId=trim($_POST['priceClientCategoryId']);
    $clientId=trim($_POST['clientId']);
    $this->set(compact('priceClientCategoryId','clientId'));
    
    $productId=trim($_POST['productId']);
    $rawMaterialId=trim($_POST['rawMaterialId']);
    
    $productPriceLogConditions=[
      'ProductPriceLog.product_id'=>$productId,
    ];
    if ($rawMaterialId > 0){
      $productPriceLogConditions['ProductPriceLog.raw_material_id']=$rawMaterialId;
    }  
    if ($priceClientCategoryId > 0){
      $productPriceLogConditions['ProductPriceLog.price_client_category_id']=$priceClientCategoryId;
    }
    if ($clientId > 0){
      $productPriceLogConditions['ProductPriceLog.client_id']=$clientId;
    }    
    //pr($productPriceLogConditions);
    $productPriceLogs=$this->ProductPriceLog->find('all',[
      'conditions'=>$productPriceLogConditions,
      'contain'=>[
        'Product',
        'RawMaterial',
        'Client',
        'PriceClientCategory',
        'User',
        'Currency',
      ],
      'order'=>'ProductPriceLog.price_datetime DESC',
    ]);
    $this->set(compact('productPriceLogs'));
	}
  
  public function deletePricesForProduct(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
    $this->autoRender = false; // We don't render a view in this example    
    
    $this->loadModel('User');  
    
    $userRoleId=$this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $priceClientCategoryId=trim($_POST['priceClientCategoryId']);
    $clientId=trim($_POST['clientId']);
    $this->set(compact('priceClientCategoryId','clientId'));
    
    $productId=trim($_POST['productId']);
    $rawMaterialId=trim($_POST['rawMaterialId']);
    
    $productPriceLogConditions=[
      'ProductPriceLog.product_id'=>$productId,
    ];
    if ($rawMaterialId > 0){
      $productPriceLogConditions['ProductPriceLog.raw_material_id']=$rawMaterialId;
    }  
    if ($priceClientCategoryId > 0){
      $productPriceLogConditions['ProductPriceLog.price_client_category_id']=$priceClientCategoryId;
    }
    if ($clientId > 0){
      $productPriceLogConditions['ProductPriceLog.client_id']=$clientId;
    }    
    //pr($productPriceLogConditions);
    $productPriceLogs=$this->ProductPriceLog->find('all',[
      'conditions'=>$productPriceLogConditions,
      'contain'=>[
        'Product',
        'RawMaterial',
        'Client',
        'PriceClientCategory',
        'User',
        'Currency',
      ],
      'order'=>'ProductPriceLog.price_datetime DESC',
    ]);
    
    if ($userRoleId == ROLE_ADMIN){
      if(!empty($productPriceLogs)){
        $datasource=$this->ProductPriceLog->getDataSource();
        $datasource->begin();
        
        try {
          $deletionData='Se eliminaron los siguientes precios: ';
          foreach ($productPriceLogs as $productPriceLog){ 
            $priceDateTime = new DateTime($productPriceLog['ProductPriceLog']['price_datetime']);
        
            $deletionData.=$priceDateTime->format('d-m-Y H:i:s');
            $deletionData.=$productPriceLog['Product']['name'].(empty($productPriceLog['RawMaterial']['id'])?'':(' '.$productPriceLog['RawMaterial']['name'] ));
            if ($priceClientCategoryId > 0){
              $deletionData.=$productPriceLog['PriceClientCategory']['name'];
            }
            if ($clientId > 0){
              $deletionData.=$productPriceLog['Client']['company_name'];
            }  
            $deletionData.=$productPriceLog['Currency']['abbreviation'].' '.$productPriceLog['ProductPriceLog']['price'];
            if (!$this->ProductPriceLog->delete($productPriceLog['ProductPriceLog']['id'])){
              //echo "Problema eliminando el precio del producto ".$productPriceLog['ProductPriceLog']['id'];
              //pr($this->validateErrors($this->ProductPriceLog));
              throw new Exception();
            } 
          }    
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),$deletionData);
          $this->Session->setFlash("Se eliminaron los precios de venta",'default',['class' => 'success']);
          return json_encode([
            'boolSuccess'=>1,
            'errorMessage'=>'',
          ]);
        }
        catch(Exception $e){
          $datasource->rollback();
          //pr($e);
          $this->Session->setFlash("No se podían eliminar los precios de venta", 'default',['class' => 'error-message']);
          return json_encode([
            'boolSuccess'=>0,
            'errorMessage'=>'',
          ]);
        }
      }
      else {
        return json_encode([
          'boolSuccess'=>0,
          'errorMessage'=>'No hay precios para eliminar',
        ]);
      }
    }
    else {
      return json_encode([
        'boolSuccess'=>0,
        'errorMessage'=>'Solo el gerente puede eliminar precios',
      ]);
    }
	}
  
  
  public function getproductprice(){
    $this->layout= "ajax";
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		
		$selectedDay=trim($_POST['selectedDay']);
    $selectedMonth=trim($_POST['selectedMonth']);
    $selectedYear=trim($_POST['selectedYear']);
    $productId=trim($_POST['productId']);
    $rawMaterialId=trim($_POST['rawMaterialId']);
    $clientId=trim($_POST['clientId']);
    
    $selectedDateString=$selectedYear.'-'.$selectedMonth.'-'.$selectedDay;
		$selectedDate=date( "Y-m-d", strtotime($selectedDateString));
		$productPriceForClient=$this->ProductPriceLog->getApplicableProductPriceForClient($productId,$rawMaterialId,$clientId,$selectedDate);
		
		return $productPriceForClient;
  }
  
  public function saveProductPriceLog() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
    $priceClientCategoryId=trim($_POST['priceClientCategoryId']);
    $clientId=trim($_POST['clientId']);
		
    $productId=trim($_POST['productId']);
    $rawMaterialId=trim($_POST['rawMaterialId']);
    
    $userId=trim($_POST['userId']);
    $price=trim($_POST['price']);
		
    if ($price == 0){
      $conditions=[
        'ProductPriceLog.product_id'=>$productId,
        'DATE(ProductPriceLog.price_datetime)'=>date('Y-m-d'),
      ];  
      if (!empty($rawMaterialId)){
        $conditions['ProductPriceLog.raw_material_id']=$rawMaterialId;
      }
      if (!empty($clientId)){
        $conditions['ProductPriceLog.client_id']=$clientId;
      } 
      if (!empty($priceClientCategoryId)){
        $conditions['ProductPriceLog.price_client_category_id']=$priceClientCategoryId;
      }      
      //pr($conditions);
      $productPriceLogForRemoval=$this->ProductPriceLog->find('first',[
        'fields'=>['ProductPriceLog.id'],
        'conditions'=>$conditions,
        'order'=>[
          'ProductPriceLog.id DESC',
        ],
      ]);  
      //pr($productPriceLogForRemoval);
      if (!empty($productPriceLogForRemoval)){
        if (!$this->ProductPriceLog->delete($productPriceLogForRemoval['ProductPriceLog']['id'])){
          echo "Problema al eliminar el precio para producto ".$productId;
          pr($this->validateErrors($this->ProductPriceLog));
          throw new Exception();
        }
        return $productPriceLogForRemoval['ProductPriceLog']['id'];
      }
    }
    else {       
      $productPriceLogArray=[  
        'ProductPriceLog'=>[    
          'price_datetime'=>date('Y-m-d H:i:s'),
          'product_id'=>$productId,
          'raw_material_id'=>$rawMaterialId,
          'client_id'=>(empty($clientId)?null:$clientId),
          'price_client_category_id'=>(empty($priceClientCategoryId)?null:$priceClientCategoryId),
          'user_id'=>$userId,
          'price'=>round($price,4),
          'currency_id'=>CURRENCY_CS,
        ],
      ];          
      $this->ProductPriceLog->create();
      
      if (!$this->ProductPriceLog->save($productPriceLogArray)) {
        echo "Problema guardando el precio";
        pr($this->validateErrors($this->ProductPriceLog));
        throw new Exception();
      }
      $productPriceLogId=$this->ProductPriceLog->id;
    
      $this->recordUserAction($this->ProductPriceLog->id,"crear",null);
      $this->recordUserActivity($this->Session->read('User.username'),"Se registró el precio ".$price." para producto ".$productId." desde la pantalla de resumen de precios");
      
      return $productPriceLogId;
    }
  }
	
  public function listaPrecios(){
    $this->loadModel('ProductNature');
    $this->loadModel('ProductType');
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    
    $this->loadModel('StockItem');
    $this->loadModel('PriceClientCategory');
    
    //$this->loadModel('ThirdParty');
    //$this->loadModel('ClosingDate');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $priceDateTime = date('y-m-d H:i:s');
    $warehouseId=0;
    $selectedProductNatureId=0;
    
    $existenceOptions=[
      0=>'Mostrar solamente productos con existencia',
      1=>'Mostrar todos productos',
    ];
    $this->set(compact('existenceOptions'));
    define('SHOW_EXISTING','0');
    define('SHOW_ALL','1');
    $existenceOptionId=0;
    
    if ($this->request->is('post')) {
      $priceDateTimeArray=$this->request->data['ProductPriceLog']['price_datetime'];
      $priceDateTimeAsString=$this->ProductPriceLog->deconstruct('price_datetime',$this->request->data['ProductPriceLog']['price_datetime']);
      $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString));  
      
      $warehouseId=$this->request->data['ProductPriceLog']['warehouse_id'];
      $selectedProductNatureId=$this->request->data['ProductPriceLog']['selected_product_nature_id'];
      $existenceOptionId=$this->request->data['ProductPriceLog']['existence_option_id'];
		}
    elseif (!empty($_SESSION['priceDateTime'])){
      $priceDateTime=$_SESSION['priceDateTime'];
    }
		else {
			$priceDateTime=date("Y-m-d H:i:s");
		}
    
    $priceDateTimeAsString=$priceDateTime;
    $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString));
    $priceDateTimePlusOne= date( "Y-m-d", strtotime( $priceDateTime."+1 days" ) );
    $_SESSION['priceDateTime']=$priceDateTime;
		//pr($priceDateTime);
    $this->set(compact('priceDateTime'));
    //echo "priceDateTimeAsString is ".$priceDateTimeAsString."<br/>";
    $priceDate=date( "Y-m-d", strtotime($priceDateTimeAsString));
    
    $this->set(compact('selectedProductNatureId'));
    $this->set(compact('existenceOptionId'));
   
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    elseif (count($warehouses) > 1 && $warehouseId == 0){
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      elseif (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
        $warehouseId = WAREHOUSE_DEFAULT;
      }
      else {
        $warehouseId=0;
      }
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    
    $priceClientCategories=$this->PriceClientCategory->find('list',[
      'order'=>'PriceClientCategory.category_number ASC'
    ]);
    $this->set(compact('priceClientCategories'));
    
    $productNatureConditions=[];
    if ($selectedProductNatureId == 0){
      $productNatureConditions['ProductNature.id !=']=[PRODUCT_NATURE_RAW,PRODUCT_NATURE_BAGS];
    }
    else{
      $productNatureConditions['ProductNature.id']=$selectedProductNatureId;
      //$productNatureConditions['ProductNature.id !=']=[PRODUCT_TYPE_SERVICE];
    }
    $selectedProductNatures=$productNatureList=$this->ProductNature->find('list',[
      'conditions'=>$productNatureConditions,
      'order'=>['ProductNature.list_order ASC']
    ]);
    $this->set(compact('selectedProductNatures'));
    $productNatures=[];
    foreach ($productNatureList as $productNatureId=>$productNatureName){
      $existences=$this->StockItem->getAllProductCombinationsByProductNature($warehouseId,$productNatureId,!$existenceOptionId);
      $productNatures[$productNatureId]=[
        'ProductNature'=>[
          'name'=>$productNatureName,
          'productIds'=>$existences['productIds'],
          'rawMaterialIds'=>$existences['rawMaterialIds'],
        ],
        'existences'=>$existences['existences'],
      ];
    }
    
    $productTypes=$this->ProductType->find('list',[
      'fields'=>['id','name'],
    ]);
    //$this->set(compact('productTypes'));
    $productProductTypes=$this->Product->find('list',[
      'fields'=>['id','product_type_id'],
    ]);
    //$this->set(compact('productProductTypes'));
    
    foreach ($productNatures as $currentProductNatureId=>$currentProductNatureData){
      //pr($currentProductNatureData);
      foreach ($currentProductNatureData['existences']['Product'] as $productId=>$productData){
        $productNatures[$currentProductNatureId]['existences']['Product'][$productId]['ProductType']['id']=$productProductTypes[$productId];  
        $productNatures[$currentProductNatureId]['existences']['Product'][$productId]['ProductType']['name']=$productTypes[$productProductTypes[$productId]];  
        if ($currentProductNatureId == PRODUCT_NATURE_PRODUCED){
          foreach($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData['remaining']){
            foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
              $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTimeAsString);
              $productNatures[$currentProductNatureId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['PriceClientCategory'][$priceClientCategoryId]=[
                'price'=>$priceInfo['price'],
                'price_datetime'=>$priceInfo['price_datetime'],
              ];
            }
          }
        }  
        else {
          foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
            $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTimeAsString);
            $productNatures[$currentProductNatureId]['existences']['Product'][$productId]['PriceClientCategory'][$priceClientCategoryId]=[
              'price'=>$priceInfo['price'],
              'price_datetime'=>$priceInfo['price_datetime'],
            ];
          }  
        }
      }  
    }
    //pr($productNatures);   
    $this->set(compact('productNatures'));
    $totalFields=0;
    foreach ($productNatures as $productNatureId=>$productNatureData){
      $rowCount=count($priceClientCategories);
      
      $columnCount=0;
      if ($productNatureId == PRODUCT_NATURE_PRODUCED){
        foreach ($productNatureData['existences']['Product'] as $productId =>$productData){
          $columnCount+=count($productData['RawMaterial']);
        }
      }
      else {
        foreach ($productNatureData['existences']['Product'] as $productId =>$productData){
          $columnCount++;
        }
      }
      //echo 'total rows is '.$rowCount.' and total columns '.$columnCount.'<br/>';
      $totalFields+=($rowCount*$columnCount);
    }  
    $this->set(compact('totalFields'));
    
    $priceClientCategoryColors=$this->PriceClientCategory->find('list',[
      'fields'=>['id','hexcolor'],
    ]);
    $this->set(compact('priceClientCategoryColors'));
  }
  public function guardarListaPrecios($fileName) {
		$exportData=$_SESSION['listaPrecios'];
		$this->set(compact('exportData','fileName'));
	}
  
  public function resumenPrecios($productCategoryId=0){
    $this->loadModel('ClosingDate');
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductCategory');
    $this->loadModel('Currency');
    $this->loadModel('ThirdParty');
    $this->loadModel('PriceClientCategory');
    $this->loadModel('StockItem');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $priceDateTime = date('y-m-d H:i:s');
    $warehouseId=0;
    
    $existenceOptions=[
      0=>'Mostrar solamente productos con existencia',
      1=>'Mostrar todos productos',
    ];
    $this->set(compact('existenceOptions'));
    define('SHOW_EXISTING','0');
    define('SHOW_ALL','1');
    $existenceOptionId=0;
    $initialProductCategoryId=$productCategoryId;
    
    if ($this->request->is('post')) {
      $priceDateTimeArray=$this->request->data['ProductPriceLog']['price_datetime'];
      $priceDateTimeAsString=$this->ProductPriceLog->deconstruct('price_datetime',$this->request->data['ProductPriceLog']['price_datetime']);
      $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString));  
      
      $warehouseId=$this->request->data['ProductPriceLog']['warehouse_id'];
      $productCategoryId=$this->request->data['ProductPriceLog']['product_category_id'];
      $existenceOptionId=$this->request->data['ProductPriceLog']['existence_option_id'];
		}
    elseif (!empty($_SESSION['priceDateTime'])){
      $priceDateTime=$_SESSION['priceDateTime'];
    }
		else {
			$priceDateTime=date("Y-m-d H:i:s");
		}
    
    if ($productCategoryId != 0 && $productCategoryId != $initialProductCategoryId){
      return $this->redirect(['action' => 'resumenPrecios',$productCategoryId]);
    }
    
    $priceDateTimeAsString=$priceDateTime;
    $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString));
    $priceDateTimePlusOne= date( "Y-m-d", strtotime( $priceDateTime."+1 days" ) );
    $_SESSION['priceDateTime']=$priceDateTime;
		//pr($priceDateTime);
    $this->set(compact('priceDateTime'));
    //echo "priceDateTimeAsString is ".$priceDateTimeAsString."<br/>";
    $priceDate=date( "Y-m-d", strtotime($priceDateTimeAsString));
    
    $this->set(compact('productCategoryId'));
    $this->set(compact('existenceOptionId'));
   
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    elseif (count($warehouses) > 1 && $warehouseId == 0){
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      elseif (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
        $warehouseId = WAREHOUSE_DEFAULT;
      }
      else {
        $warehouseId=0;
      }
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    
    $clients=$this->ThirdParty->getNonGenericActiveClientList();
    $this->set(compact('clients'));
    
    $priceClientCategories=$this->PriceClientCategory->find('list',[
      'order'=>'PriceClientCategory.category_number ASC'
    ]);
    $this->set(compact('priceClientCategories'));
    
    $productTypeConditions=[];
    if ($productCategoryId == 0){
      $productTypeConditions['ProductType.product_category_id !=']=[CATEGORY_RAW,CATEGORY_CONSUMIBLE];
    }
    else{
      $productTypeConditions['ProductType.product_category_id']=$productCategoryId;
      $productTypeConditions['ProductType.id !=']=[PRODUCT_TYPE_SERVICE];
    }
    $productTypeList=$this->ProductType->find('list',[
      'conditions'=>$productTypeConditions,
      'order'=>['ProductType.product_category_id ASC','ProductType.id ASC']
    ]);
    $this->set(compact('productTypeList'));
    
    $productTypes=[];
    foreach ($productTypeList as $productTypeId=>$productTypeName){
      $existences=$this->StockItem->getAllProductCombinations($warehouseId,$productTypeId,!$existenceOptionId);
      $productTypes[$productTypeId]=[
        'ProductType'=>[
          'name'=>$productTypeName,
          'productIds'=>$existences['productIds'],
          'rawMaterialIds'=>$existences['rawMaterialIds'],
        ],
        'existences'=>$existences['existences'],
      ];
    }
    
    $boolSaved='0';
    if ($this->request->is('post') && empty($this->request->data['changeDate'])) {	      
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDate=new DateTime($latestClosingDate);
      
      if ($priceDateTimeAsString>date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('Los precios no se pueden registrar en el futuro!  No se guardaron los precios.'), 'default',['class' => 'error-message']);
      }
      elseif ($priceDateTimeAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardaron los precios.'), 'default',['class' => 'error-message']);
      }
      else {
        foreach ($productTypes as $productTypeId=>$productTypeData){
          //pr($productTypeData);
          $clientIdsWithPricesForThisProductType=$this->ProductPriceLog->find('list',[
            'fields'=>'ProductPriceLog.client_id',
            'conditions'=>[
              'ProductPriceLog.product_id'=>$productTypeData['ProductType']['productIds'],
            ],
          ]);
          $clientConditions=[
            'ThirdParty.bool_provider'=>'0',
            'ThirdParty.bool_active'=>true,
            'ThirdParty.id'=>array_values($clientIdsWithPricesForThisProductType),
          ];
          $productTypeClients=$this->ThirdParty->find('list',[
            'conditions'=>$clientConditions,
            'order'=>'ThirdParty.company_name',
          ]);
          foreach ($productTypeClients as $clientId=>$clientName){
            $clientArray=['Product'=>[]];
            foreach ($productTypeData['existences']['Product'] as $productId=>$productData){
              if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                foreach($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData['remaining']){
                  $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateTimeAsString);
                  $productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId]['RawMaterial'][$rawMaterialId]=[
                    'price'=>$priceInfo['price'],
                    'price_datetime'=>$priceInfo['price_datetime'],
                  ];
                }
              }    
              else {
                $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateTimeAsString);
                $productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId]=[
                  'price'=>$priceInfo['price'],
                  'price_datetime'=>$priceInfo['price_datetime'],
                ];
              }
            }
          }
          
          foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
            $priceClientCategoryArray=['Product'=>[]];
            
            foreach ($productTypeData['existences']['Product'] as $productId=>$productData){       
              if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                foreach($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData['remaining']){
                  $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTimeAsString);
                  $productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product'][$productId]['RawMaterial'][$rawMaterialId]=[
                    'price'=>$priceInfo['price'],
                    'price_datetime'=>$priceInfo['price_datetime'],
                    
                  ];
                }
              }  
              else {
                $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTimeAsString);
                $productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product'][$productId]=[
                  'price'=>$priceInfo['price'],
                  'price_datetime'=>$priceInfo['price_datetime'],
                ];
              }
            }
          }  
        }
        
        //pr($productTypes);
        //pr($this->request->data);
        $datasource=$this->ProductPriceLog->getDataSource();
        $datasource->begin();
        
        try {
          foreach ($this->request->data['ProductType'] as $productTypeId=>$productTypeData){  
            if ($productTypeId ==  PRODUCT_TYPE_BOTTLE){
              //pr($productPriceArray);
              foreach ($productTypeData['Client'] as $clientId=>$clientData){
                foreach ($clientData['Product'] as $productId=>$productData){
                  foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                    if (
                      array_key_exists($clientId,$productTypes[$productTypeId]['Client']) 
                      && array_key_exists($productId,$productTypes[$productTypeId]['Client'][$clientId]['Product'])
                      && array_key_exists('RawMaterial',$productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId])
                      && array_key_exists($rawMaterialId,$productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId]['RawMaterial'])
                      && $rawMaterialData['price'] >= 0 
                      && $rawMaterialData['price'] !=  $productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId]['RawMaterial'][$rawMaterialId]['price']
                    ){
                      $priceDateTimeForm= new DateTime($priceDateTimeAsString);
                      $priceDateTimeProduct = new DateTime($productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_datetime']);
                      // IF THE PRICE IS ZERO BUT IT HAS BEEN REGISTERED BEFORE, REGISTER THE 0 OPRICE
                      if ($rawMaterialData['price'] > 0 || $priceDateTimeForm->format('d-m-Y') != $priceDateTimeProduct->format('d-m-Y')){
                        $productPriceLogArray=[
                          'product_id'=>$productId,
                          'raw_material_id'=>$rawMaterialId,
                          'price'=>$rawMaterialData['price'],
                          'client_id'=>$clientId,
                          
                          'price_datetime'=>$priceDateTimeAsString,
                          'currency_id'=>CURRENCY_CS,
                          'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
                         
                        ];
                        //pr($productPriceLogArray);
                        $this->ProductPriceLog->create();
                        if (!$this->ProductPriceLog->save($productPriceLogArray)) {
                          echo "Problema guardando el precio del producto ".$productId;
                          pr($this->validateErrors($this->ProductPriceLog));
                          throw new Exception();
                        }   
                      }
                      else {
                        // THE PRICE IS ZERO
                        $conditions=[
                          'ProductPriceLog.product_id'=>$productId,
                          'ProductPriceLog.raw_material_id'=>$rawMaterialId,
                          'ProductPriceLog.client_id'=>$clientId,
                          'DATE(ProductPriceLog.price_datetime)'=>($priceDateTimeForm->format('Y-m-d')),
                        ];
                        //pr($conditions);
                        $productPriceLogForRemoval=$this->ProductPriceLog->find('first',[
                          'fields'=>['ProductPriceLog.id'],
                          'conditions'=>$conditions,
                          'order'=>[
                            'ProductPriceLog.id DESC',
                          ],
                        ]);  
                        //pr($productPriceLogForRemoval);
                        if (!empty($productPriceLogForRemoval)){
                          if (!$this->ProductPriceLog->delete($productPriceLogForRemoval['ProductPriceLog']['id'])){
                            echo "Problema al eliminar el precio para producto ".$productId;
                            pr($this->validateErrors($this->ProductPriceLog));
                            throw new Exception();
                          }                            
                        }
                      } 
                    }
                  }
                }
              }

              foreach ($productTypeData['PriceClientCategory'] as $priceClientCategoryId=>$priceClientCategoryData){
                foreach ($priceClientCategoryData['Product'] as $productId=>$productData){
                  foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                    if (array_key_exists($productId,$productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product']) 
                      && array_key_exists($rawMaterialId,$productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product'][$productId]['RawMaterial'])
                      && $rawMaterialData['price'] >= 0 
                      && $rawMaterialData['price'] !=  $productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product'][$productId]['RawMaterial'][$rawMaterialId]['price']
                    ){
                      $priceDateTimeForm= new DateTime($priceDateTimeAsString);
                      $priceDateTimeProduct = new DateTime($priceDateTimeClientPriceArray[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_datetime']);
                      //pr($priceDateTimeForm);
                      //pr($priceDateTimeProduct);
                      // IF THE PRICE IS ZERO BUT IT HAS BEEN REGISTERED BEFORE, REGISTER THE 0 OPRICE
                      if ($rawMaterialData['price'] > 0 || $priceDateTimeForm->format('d-m-Y') != $priceDateTimeProduct->format('d-m-Y')){
                        $productPriceLogArray=[
                          'product_id'=>$productId,
                          'raw_material_id'=>$rawMaterialId,
                          'price'=>$rawMaterialData['price'],
                          'price_client_category_id'=>$priceClientCategoryId,
                          
                          'price_datetime'=>$priceDateTimeAsString,
                          'currency_id'=>CURRENCY_CS,
                          'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
                         
                        ];
                        //pr($productPriceLogArray);
                        $this->ProductPriceLog->create();
                        if (!$this->ProductPriceLog->save($productPriceLogArray)) {
                          echo "Problema guardando el precio del producto ".$productId;
                          pr($this->validateErrors($this->ProductPriceLog));
                          throw new Exception();
                        }   
                      }
                      else {
                        // THE PRICE IS ZERO
                        $conditions=[
                          'ProductPriceLog.product_id'=>$productId,
                          'ProductPriceLog.raw_material_id'=>$rawMaterialId,
                          'ProductPriceLog.price_client_category_id'=>$priceClientCategoryId,
                          'DATE(ProductPriceLog.price_datetime)'=>($priceDateTimeForm->format('Y-m-d')),
                        ];
                        pr($conditions);
                        $productPriceLogForRemoval=$this->ProductPriceLog->find('first',[
                          'fields'=>['ProductPriceLog.id'],
                          'conditions'=>$conditions,
                          'order'=>[
                            'ProductPriceLog.id DESC',
                          ],
                        ]);  
                        //pr($productPriceLogForRemoval);
                        if (!empty($productPriceLogForRemoval)){
                          if (!$this->ProductPriceLog->delete($productPriceLogForRemoval['ProductPriceLog']['id'])){
                            echo "Problema al eliminar el precio para producto ".$productId;
                            pr($this->validateErrors($this->ProductPriceLog));
                            throw new Exception();
                          }                            
                        }
                      } 
                    }
                  }
                }
              }

            }
            else {
              foreach ($productTypeData['PriceClientCategory'] as $priceClientCategoryId=>$priceClientCategoryData){
                foreach ($priceClientCategoryData['Product'] as $productId=>$productData){
                  if (array_key_exists($productId,$productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product']) 
                      
                    && $productData['price'] >= 0 
                    && $productData['price'] !=  $productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product'][$productId]['price']
                  ){
                    $priceDateTimeForm= new DateTime($priceDateTimeAsString);
                    $priceDateTimeProduct = new DateTime($productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product'][$productId]['price_datetime']);
                    //pr($priceDateTimeForm);
                    //pr($priceDateTimeProduct);
                    if ($productData['price'] > 0 || $priceDateTimeForm->format('d-m-Y') != $priceDateTimeProduct->format('d-m-Y')){
                      $productPriceLogArray=[
                        'product_id'=>$productId,
                        'price'=>$productData['price'],
                        'price_client_category_id'=>$priceClientCategoryId,
                        
                        'price_datetime'=>$priceDateTimeAsString,
                        'currency_id'=>CURRENCY_CS,
                        'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
                       
                      ];
                      //pr($productPriceLogArray);
                      $this->ProductPriceLog->create();
                      if (!$this->ProductPriceLog->save($productPriceLogArray)) {
                        echo "Problema guardando el precio del producto ".$productId;
                        pr($this->validateErrors($this->ProductPriceLog));
                        throw new Exception();
                      }   
                    }
                    else {
                      // THE PRICE IS ZERO                         
                      $conditions=[
                        'ProductPriceLog.product_id'=>$productId,
                        'ProductPriceLog.price_client_category_id'=>$priceClientCategoryId,
                        'DATE(ProductPriceLog.price_datetime)'=>($priceDateTimeForm->format('Y-m-d')),
                      ];
                      //pr($conditions);
                      $productPriceLogForRemoval=$this->ProductPriceLog->find('first',[
                        'fields'=>['ProductPriceLog.id'],
                        'conditions'=>$conditions,
                        'order'=>[
                          'ProductPriceLog.id DESC',
                        ],
                      ]);  
                      //pr($productPriceLogForRemoval);
                      if (!empty($productPriceLogForRemoval)){
                        if (!$this->ProductPriceLog->delete($productPriceLogForRemoval['ProductPriceLog']['id'])){
                          echo "Problema al eliminar el precio para producto ".$productId;
                          pr($this->validateErrors($this->ProductPriceLog));
                          throw new Exception();
                        }                            
                      }
                    } 
                  }
                }
              }  
            
              if (!empty($productTypeData['Client'])){
                foreach ($productTypeData['Client'] as $clientId=>$clientData){
                  foreach ($clientData['Product'] as $productId=>$productData){
                    if (array_key_exists($productId,$productTypes[$productTypeId]['Client'][$clientId]['Product']) 
                        
                      && $productData['price'] >= 0 
                      && $productData['price'] !=  $productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId]['price']
                    ){
                      $priceDateTimeForm= new DateTime($priceDateTimeAsString);
                      $priceDateTimeProduct = new DateTime($productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId]['price_datetime']);
                      if ($productData['price'] > 0 || $priceDateTimeForm->format('d-m-Y') != $priceDateTimeProduct->format('d-m-Y')){
                        $productPriceLogArray=[
                          'product_id'=>$productId,
                          'price'=>$productData['price'],
                          'client_id'=>$clientId,
                          
                          'price_datetime'=>$priceDateTimeAsString,
                          'currency_id'=>CURRENCY_CS,
                          'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
                         
                        ];
                        $this->ProductPriceLog->create();
                        if (!$this->ProductPriceLog->save($productPriceLogArray)) {
                          echo "Problema guardando el precio del producto ".$productId;
                          pr($this->validateErrors($this->ProductPriceLog));
                          throw new Exception();
                        }   
                      }
                      else {
                        // THE PRICE IS ZERO                         
                        $conditions=[
                          'ProductPriceLog.product_id'=>$productId,
                          'ProductPriceLog.client_id'=>$clientId,
                          'DATE(ProductPriceLog.price_datetime)'=>($priceDateTimeForm->format('Y-m-d')),
                        ];
                        $productPriceLogForRemoval=$this->ProductPriceLog->find('first',[
                          'fields'=>['ProductPriceLog.id'],
                          'conditions'=>$conditions,
                          'order'=>[
                            'ProductPriceLog.id DESC',
                          ],
                        ]);  
                        //pr($productPriceLogForRemoval);
                        if (!empty($productPriceLogForRemoval)){
                          if (!$this->ProductPriceLog->delete($productPriceLogForRemoval['ProductPriceLog']['id'])){
                            echo "Problema al eliminar el precio para producto ".$productId;
                            pr($this->validateErrors($this->ProductPriceLog));
                            throw new Exception();
                          }                            
                        }
                      } 
                    }
                  }
                }  
              }
            }                          
          }                
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),"Se registraron los precios de venta para fecha ".$priceDateTimeAsString);
          $this->Session->setFlash("Se registraron los precios de venta para fecha ".$priceDateTimeAsString,'default',['class' => 'success'],'default',['class' => 'success']);
          $boolSaved=true;
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash("No se podían registrar los precios de venta para fecha ".$priceDateTimeAsString, 'default',['class' => 'error-message']);
        }
      }	
    }
    $this->set(compact('boolSaved'));
    
    $clientPriceArray=[];
    
    $genericClientIds=$this->ThirdParty->getGenericClientIds();
    
    foreach ($productTypes as $productTypeId=>$productTypeData){
      //pr($productTypeData);
      $clientIdsWithPricesForThisProductType=$this->ProductPriceLog->find('list',[
        'fields'=>'ProductPriceLog.client_id',
        'conditions'=>[
          'ProductPriceLog.product_id'=>$productTypeData['ProductType']['productIds'],
          'ProductPriceLog.client_id !='=>$genericClientIds,
        ],
      ]);
      $clientConditions=[
        'ThirdParty.bool_provider'=>'0',
        'ThirdParty.bool_active'=>true,
        'ThirdParty.id'=>array_values($clientIdsWithPricesForThisProductType),
      ];
      $productTypeClients=$this->ThirdParty->find('list',[
        'conditions'=>$clientConditions,
        'order'=>'ThirdParty.company_name',
      ]);
      foreach ($productTypeClients as $clientId=>$clientName){
        $clientArray=['Product'=>[]];
        foreach ($productTypeData['existences']['Product'] as $productId=>$productData){
          if ($productTypeId == PRODUCT_TYPE_BOTTLE){
            foreach($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData['remaining']){
              $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateTimeAsString);
              $productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId]['RawMaterial'][$rawMaterialId]=[
                'price'=>$priceInfo['price'],
                'price_datetime'=>$priceInfo['price_datetime'],
              ];
            }
          }    
          else {
            $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateTimeAsString);
            $productTypes[$productTypeId]['Client'][$clientId]['Product'][$productId]=[
              'price'=>$priceInfo['price'],
              'price_datetime'=>$priceInfo['price_datetime'],
            ];
          }
        }
      }
      
      foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
        $priceClientCategoryArray=['Product'=>[]];
        foreach ($productTypeData['existences']['Product'] as $productId=>$productData){       
          if ($productTypeId == PRODUCT_TYPE_BOTTLE){
            foreach($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData['remaining']){
              $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTimeAsString);
              $productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product'][$productId]['RawMaterial'][$rawMaterialId]=[
                'price'=>$priceInfo['price'],
                'price_datetime'=>$priceInfo['price_datetime'],
                
              ];
            }
          }  
          else {
            $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTimeAsString);
            $productTypes[$productTypeId]['PriceClientCategory'][$priceClientCategoryId]['Product'][$productId]=[
              'price'=>$priceInfo['price'],
              'price_datetime'=>$priceInfo['price_datetime'],
            ];
          }
        }
      }  
    }
    //pr($productTypes);   
    $this->set(compact('productTypes'));
    $totalFields=0;
    foreach ($productTypes as $productTypeId=>$productTypeData){
      $rowCount=count($priceClientCategories);
      if (!empty($productTypeData['Client'])){
        $rowCount+=count($productTypeData['Client']);
      }    
      
      $columnCount=0;
      if ($productTypeId == PRODUCT_TYPE_BOTTLE){
        foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
          $columnCount+=count($productData['RawMaterial']);
        }
      }
      else {
        foreach ($productTypeData['existences']['Product'] as $productId =>$productData){
          $columnCount++;
        }
      }
      //echo 'total rows is '.$rowCount.' and total columns '.$columnCount.'<br/>';
      $totalFields+=($rowCount*$columnCount);
    }  
    $this->set(compact('totalFields'));
    
    $productCategories=$this->ProductCategory->find('list',[
      'conditions'=>[
        'ProductCategory.id !='=>[CATEGORY_RAW,CATEGORY_CONSUMIBLE],
      ],
    ]);
    $this->set(compact('productCategories'));
    
    $currencies=$this->Currency->find('list');
    $this->set(compact('currencies'));
    
    $priceClientCategoryColors=$this->PriceClientCategory->find('list',[
      'fields'=>['id','hexcolor'],
    ]);
    $this->set(compact('priceClientCategoryColors'));
    
    $clientPriceClientCategories=$this->ThirdParty->find('list',[
      'fields'=>['id','price_client_category_id'],
    ]);
    $this->set(compact('clientPriceClientCategories'));
    
    
    $products=$this->Product->getAllProducts();
    $this->set(compact('products'));
    //pr($products);
    
    $rawMaterials=$this->Product->getProductsByProductNature(PRODUCT_NATURE_RAW);
    $this->set(compact('rawMaterials'));
    
    $users=$this->User->getActiveUserList();
    $this->set(compact('users'));
    
    $aco_name="ProductPriceLogs/registrarPreciosCliente";		
		$boolRegistrarPreciosCliente=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolRegistrarPreciosCliente'));
  }
  public function guardarResumenPrecios($fileName) {
		$exportData=$_SESSION['resumenPrecios'];
		$this->set(compact('exportData','fileName'));
	}
  
  public function registrarPreciosCliente($clientId=0){
    $this->loadModel('ClosingDate');
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductCategory');
    $this->loadModel('Currency');
    $this->loadModel('ThirdParty');
    
    $this->loadModel('PriceClientCategory');
    $this->loadModel('StockItem');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
        
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $priceDateTime = date('y-m-d H:i:s');
    $warehouseId=0;
    $productCategoryId=CATEGORY_PRODUCED;
    
    $existenceOptions=[
      0=>'Mostrar solamente productos con existencia',
      1=>'Mostrar todos productos',
    ];
    $this->set(compact('existenceOptions'));
    define('SHOW_EXISTING','0');
    define('SHOW_ALL','1');
    $existenceOptionId=SHOW_EXISTING;
    
    if ($this->request->is('post')) {
      $priceDateTimeArray=$this->request->data['ProductPriceLog']['price_datetime'];
      $priceDateTimeAsString=$this->ProductPriceLog->deconstruct('price_datetime',$this->request->data['ProductPriceLog']['price_datetime']);
      $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString)); 
      
      $warehouseId=$this->request->data['ProductPriceLog']['warehouse_id']; 
      $productCategoryId=$this->request->data['ProductPriceLog']['product_category_id'];
      $existenceOptionId=$this->request->data['ProductPriceLog']['existence_option_id'];
      $clientId=$this->request->data['ProductPriceLog']['client_id'];
		}
    elseif (!empty($_SESSION['priceDateTime'])){
      $priceDateTime=$_SESSION['priceDateTime'];
    }
		else {
			$priceDateTime=date("Y-m-d H:i:s");
		}
    
    $priceDateTimeAsString=$priceDateTime;
    $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString));
    $priceDateTimePlusOne= date( "Y-m-d", strtotime( $priceDateTime."+1 days" ) );
    $_SESSION['priceDateTime']=$priceDateTime;
		//pr($priceDateTime);
    $this->set(compact('priceDateTime'));
    
    $priceDate=date( "Y-m-d", strtotime($priceDateTimeAsString));
    
    $this->set(compact('productCategoryId'));
    $this->set(compact('existenceOptionId'));
    $this->set(compact('clientId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    elseif (count($warehouses) > 1 && $warehouseId == 0){
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      elseif (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
        $warehouseId = WAREHOUSE_DEFAULT;
      }
      else {
        $warehouseId=0;
      }
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    
    $client=$this->ThirdParty->getClientById($clientId);
    $this->set(compact('client'));
    
    $priceClientCategories=$this->PriceClientCategory->find('list',[
      'order'=>'PriceClientCategory.category_number ASC'
    ]);
    $this->set(compact('priceClientCategories'));
    
    $productTypeConditions=[];
    if ($productCategoryId == 0){
      $productTypeConditions['ProductType.product_category_id !=']=[CATEGORY_RAW,CATEGORY_CONSUMIBLE];
    }
    else{
      $productTypeConditions['ProductType.product_category_id']=$productCategoryId;
      $productTypeConditions['ProductType.id !=']=[PRODUCT_TYPE_SERVICE];
    }
    $productTypeList=$this->ProductType->find('list',[
      'conditions'=>$productTypeConditions,
      'order'=>['ProductType.product_category_id ASC','ProductType.id ASC']
    ]);
    $productTypes=[];
    foreach ($productTypeList as $productTypeId=>$productTypeName){
      $existences=$this->StockItem->getAllProductCombinations($warehouseId,$productTypeId,!$existenceOptionId);
      $productTypes[$productTypeId]=[
        'ProductType'=>[
          'name'=>$productTypeName,
          'productIds'=>$existences['productIds'],
          'rawMaterialIds'=>$existences['rawMaterialIds'],
        ],
        'existences'=>$existences['existences'],
      ];
    }
    //pr($productTypes);

    $boolSaved='0';
    if ($this->request->is('post') && empty($this->request->data['changeDate'])) {	      
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDate=new DateTime($latestClosingDate);
      
      if ($priceDateTimeAsString>date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('Los precios no se pueden registrar en el futuro!  No se guardaron los precios.'), 'default',['class' => 'error-message']);
      }
      elseif ($priceDateTimeAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardaron los precios.'), 'default',['class' => 'error-message']);
      }
      else {
        //pr($this->request->data);
        
        $pricePresentForClient=$previousPricePresentForClient='0';     
        foreach ($productTypes as $productTypeId => $productTypeData){
          foreach ($productTypeData['existences']['Product'] as $productId=>$productData){
            if ($productTypeId == PRODUCT_TYPE_BOTTLE){
              foreach($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                $productPriceClientCategoryId=0;
                
                $productPriceLog=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDate);
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_present']=($productPriceLog['price']>0);
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['product_price_log_id']=$productPriceLog['id'];
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price']=$productPriceLog['price'];
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_datetime']=$productPriceLog['price_datetime'];
                
                $priceDateMinusOne= date( "Y-m-d", strtotime( $priceDate."-1 days" ) );
                $previousProductPriceLog=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateMinusOne);
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['previous_price_present']=($previousProductPriceLog['price']>0);
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['previous_price']=$previousProductPriceLog['price'];
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['previous_price_datetime']=$previousProductPriceLog['price_datetime'];
                  
                
                $priceClientCategoryIdForProduct=$client['ThirdParty']['price_client_category_id'];
                $priceClientCategoryForProduct=$this->ProductPriceLog->getBottleClientPriceClientCategory($productId,$rawMaterialId,$priceClientCategoryIdForProduct,$priceDate);
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_client_category_id']=$priceClientCategoryForProduct['price_client_category_id'];
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['category_price']=$priceClientCategoryForProduct['category_price'];
                $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['category_price_datetime']=$priceClientCategoryForProduct['category_price_datetime'];
              }
            }  
            else {
              $productPriceClientCategoryId=0;
              
              $productPriceLog=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDate);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['price_present']=($productPriceLog['price']>0);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['product_price_log_id']=$productPriceLog['id'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['price']=$productPriceLog['price'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['price_datetime']=$productPriceLog['price_datetime'];
              
              $priceDateMinusOne= date( "Y-m-d", strtotime( $priceDate."-1 days" ) );
              $previousProductPriceLog=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateMinusOne);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['previous_price_present']=($previousProductPriceLog['price']>0);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['previous_price']=$previousProductPriceLog['price'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['previous_price_datetime']=$previousProductPriceLog['price_datetime'];
              
              $priceClientCategoryIdForProduct=$client['ThirdParty']['price_client_category_id'];
              $priceClientCategoryForProduct=$this->ProductPriceLog->getNonBottleClientPriceClientCategory($productId,$priceClientCategoryIdForProduct,$priceDate);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['price_client_category_id']=$priceClientCategoryForProduct['price_client_category_id'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['category_price']=$priceClientCategoryForProduct['category_price'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['category_price_datetime']=$priceClientCategoryForProduct['category_price_datetime'];
            }
          }
        }
         
        $datasource=$this->ProductPriceLog->getDataSource();
        $datasource->begin();
        
        try {
          $productlessArray=[
            'price_datetime'=>$priceDateTimeAsString,
            'currency_id'=>CURRENCY_CS,
            'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
            'client_id'=>$this->request->data['ProductPriceLog']['client_id'],
          ];
          // remove bottle prices for which price has been set to 0 (for same date)
          //pr($productTypes);
          
          foreach ($productTypes as $productTypeId=>$productTypeData){
            foreach ($productTypeData['existences']['Product'] as $productId=>$productData){
              if ($productTypeId == PRODUCT_TYPE_BOTTLE){
                foreach ($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
                  if ( 
                    (
                      !array_key_exists($productId,$this->request->data['Product'])
                      || !array_key_exists($rawMaterialId,$this->request->data['Product'][$productId]['RawMaterial'])
                      || $this->request->data['Product'][$productId]['RawMaterial'][$rawMaterialId]['price'] == 0
                    )
                    && $rawMaterialData['price'] > 0
                  ){
                    if (!$this->ProductPriceLog->delete($rawMaterialData['product_price_log_id'])){
                      echo "Problema al eliminar el precio para producto ".$productId;
                      pr($this->validateErrors($this->ProductPriceLog));
                      throw new Exception();
                    }
                  }
                }
              }
              else {
                if ( 
                  (
                  !array_key_exists($productId,$this->request->data['Product'])
                  || $this->request->data['Product'][$productId]['price'] == 0
                  )
                  && $productData['price'] > 0
                ){
                  if (!$this->ProductPriceLog->delete($productData['product_price_log_id'])){
                    echo "Problema al eliminar el precio para producto ".$productId;
                    pr($this->validateErrors($this->ProductPriceLog));
                    throw new Exception();
                  }
                }
              }      
            }
          }
          foreach ($this->request->data['Product'] as $productId=>$productPriceArray){  
            if (isset($productPriceArray['product_type_id']) && $productPriceArray['product_type_id'] ==  PRODUCT_TYPE_BOTTLE){
              foreach ($productPriceArray['RawMaterial'] as $rawMaterialId=>$rawMaterialProductPriceArray){
                // in order to be saved
                // the price should either not exist before or be different from the previous price 
                // and the price must be positive
                if (
                  (
                    !array_key_exists($productId,$productTypes[$productTypeId]['existences']['Product'])
                    || !array_key_exists('RawMaterial',$productTypes[$productTypeId]['existences']['Product'][$productId])
                    || !array_key_exists($rawMaterialId,$productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'])
                    || $rawMaterialProductPriceArray['price'] != $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price']
                    || $rawMaterialProductPriceArray['price'] != $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['previous_price']
                  ) 
                  && $rawMaterialProductPriceArray['price']>0
                ){                  
                  $productPriceData=$productlessArray;
                  $productPriceData['product_id']=$productId;
                  $productPriceData['raw_material_id']=$rawMaterialId;
                  $productPriceData['price']=$rawMaterialProductPriceArray['price'];
                  
                  $this->ProductPriceLog->create();
                  if (!$this->ProductPriceLog->save($productPriceData)) {
                    echo "Problema guardando el precio del producto ".$productId;
                    pr($this->validateErrors($this->ProductPriceLog));
                    throw new Exception();
                  }
                }
              }
            }
            else {
              $productTypeId=$productPriceArray['product_type_id'];
              //echo "productTypeId is ".$productTypeId."<br/>";
              if ($productPriceArray['price'] != $productTypes[$productTypeId]['existences']['Product'][$productId]['price'] && $productPriceArray['price'] > 0){
                $productPriceData=$productlessArray;
                $productPriceData['product_id']=$productId;
                $productPriceData['price']=$productPriceArray['price'];
                //pr($productPriceData);
                
                $this->ProductPriceLog->create();
                if (!$this->ProductPriceLog->save($productPriceData)) {
                  echo "Problema guardando el precio del producto ".$productId;
                  pr($this->validateErrors($this->ProductPriceLog));
                  throw new Exception();
                }                
              }  
            }                          
          }                
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),"Se registraron los precios de venta para fecha ".$priceDateTimeAsString);
          $this->Session->setFlash("Se registraron los precios de venta para fecha ".$priceDateTimeAsString,'default',['class' => 'success'],'default',['class' => 'success']);
          $boolSaved=true;
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash("No se podían registrar los precios de venta para fecha ".$priceDateTimeAsString, 'default',['class' => 'error-message']);
        }
      }	
    
    }
    $this->set(compact('boolSaved'));
    
    $pricePresentForClient=$previousPricePresentForClient='0';     
    if ($clientId > 0){
      foreach ($productTypes as $productTypeId => $productTypeData){
        foreach ($productTypeData['existences']['Product'] as $productId=>$productData){
          if ($productTypeId == PRODUCT_TYPE_BOTTLE){
            foreach($productData['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
              $productPriceClientCategoryId=0;
              
              $productPriceLog=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDate);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_present']=($productPriceLog['price']>0);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['product_price_log_id']=$productPriceLog['id'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price']=$productPriceLog['price'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_datetime']=$productPriceLog['price_datetime'];
              
              $priceDateMinusOne= date( "Y-m-d", strtotime( $priceDate."-1 days" ) );
              $previousProductPriceLog=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateMinusOne);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['previous_price_present']=($previousProductPriceLog['price']>0);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['previous_price']=$previousProductPriceLog['price'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['previous_price_datetime']=$previousProductPriceLog['price_datetime'];
                
              
              $priceClientCategoryIdForProduct=$client['ThirdParty']['price_client_category_id'];
              $priceClientCategoryForProduct=$this->ProductPriceLog->getBottleClientPriceClientCategory($productId,$rawMaterialId,$priceClientCategoryIdForProduct,$priceDate);
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['price_client_category_id']=$priceClientCategoryForProduct['price_client_category_id'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['category_price']=$priceClientCategoryForProduct['category_price'];
              $productTypes[$productTypeId]['existences']['Product'][$productId]['RawMaterial'][$rawMaterialId]['category_price_datetime']=$priceClientCategoryForProduct['category_price_datetime'];
            }
          }  
          else {
            $productPriceClientCategoryId=0;
            
            $productPriceLog=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDate);
            $productTypes[$productTypeId]['existences']['Product'][$productId]['price_present']=($productPriceLog['price']>0);
            $productTypes[$productTypeId]['existences']['Product'][$productId]['product_price_log_id']=$productPriceLog['id'];
            $productTypes[$productTypeId]['existences']['Product'][$productId]['price']=$productPriceLog['price'];
            $productTypes[$productTypeId]['existences']['Product'][$productId]['price_datetime']=$productPriceLog['price_datetime'];
           
            $priceDateMinusOne= date( "Y-m-d", strtotime( $priceDate."-1 days" ) );
            $previousProductPriceLog=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateMinusOne);
            $productTypes[$productTypeId]['existences']['Product'][$productId]['previous_price_present']=($previousProductPriceLog['price']>0);
            $productTypes[$productTypeId]['existences']['Product'][$productId]['previous_price']=$previousProductPriceLog['price'];
            $productTypes[$productTypeId]['existences']['Product'][$productId]['previous_price_datetime']=$previousProductPriceLog['price_datetime'];
           
            $priceClientCategoryIdForProduct=$client['ThirdParty']['price_client_category_id'];
            $priceClientCategoryForProduct=$this->ProductPriceLog->getNonBottleClientPriceClientCategory($productId,$priceClientCategoryIdForProduct,$priceDate);
            $productTypes[$productTypeId]['existences']['Product'][$productId]['price_client_category_id']=$priceClientCategoryForProduct['price_client_category_id'];
            $productTypes[$productTypeId]['existences']['Product'][$productId]['category_price']=$priceClientCategoryForProduct['category_price'];
            $productTypes[$productTypeId]['existences']['Product'][$productId]['category_price_datetime']=$priceClientCategoryForProduct['category_price_datetime'];
          }
        }
      }
      
    }
    $this->set(compact('pricePresentForClient','previousPricePresentForClient'));
    $this->set(compact('productTypes'));
    
    $productCategories=$this->ProductCategory->find('list',[
      'conditions'=>[
        'ProductCategory.id !='=>[CATEGORY_RAW,CATEGORY_CONSUMIBLE],
      ],
    ]);
    $this->set(compact('productCategories'));
    
    $currencies=$this->Currency->find('list');
    $this->set(compact('currencies'));
    
    $priceClientCategoryColors=$this->PriceClientCategory->find('list',[
      'fields'=>['id','hexcolor'],
    ]);
    $this->set(compact('priceClientCategoryColors'));
    
    $clientPriceClientCategories=$this->ThirdParty->find('list',[
      'fields'=>['id','price_client_category_id'],
    ]);
    $this->set(compact('clientPriceClientCategories'));
    
    $clients=$this->ThirdParty->getNonGenericActiveClientList();
    $this->set(compact('clients'));
  }
  public function sortByRawMaterial($a,$b ){ 
	  return ($a['name'] < $b['name']) ? -1 : 1;
	}

  public function registrarPreciosProducto($productId=0,$existenceOptionId=0){
    $this->loadModel('ClosingDate');
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductCategory');
    $this->loadModel('Currency');
    $this->loadModel('ThirdParty');
    
    $this->loadModel('PriceClientCategory');
    $this->loadModel('StockItem');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $priceDateTime = date('y-m-d H:i:s');
    $warehouseId=0;
    
    $existenceOptions=[
      0=>'Mostrar solamente productos con existencia',
      1=>'Mostrar todos productos',
    ];
    $this->set(compact('existenceOptions'));
    define('SHOW_EXISTING','0');
    define('SHOW_ALL','1');
    if ($existenceOptionId==0){
      $existenceOptionId=SHOW_EXISTING;
    }
    
    if ($this->request->is('post')) {
      $priceDateTimeArray=$this->request->data['ProductPriceLog']['price_datetime'];
      $priceDateTimeAsString=$this->ProductPriceLog->deconstruct('price_datetime',$this->request->data['ProductPriceLog']['price_datetime']);
      $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString));  
      
      $warehouseId=$this->request->data['ProductPriceLog']['warehouse_id'];
      $productId=$this->request->data['ProductPriceLog']['product_id'];
      $existenceOptionId=$this->request->data['ProductPriceLog']['existence_option_id'];
      //$clientId=$this->request->data['ProductPriceLog']['client_id'];
		}
    // DO NOT READ DATE FROM SESSION TO AVOID THAT THE HOUR "STICKS"
    //elseif (!empty($_SESSION['priceDateTime'])){
    //  $priceDateTime=$_SESSION['priceDateTime'];
    //}
		else {
			$priceDateTime=date("Y-m-d H:i:s");
		}
    
    $priceDateTimeAsString=$priceDateTime;
    $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString));
    $priceDateTimePlusOne= date( "Y-m-d", strtotime( $priceDateTime."+1 days" ) );
    //$_SESSION['priceDateTime']=$priceDateTime;
		//pr($priceDateTime);
    $this->set(compact('priceDateTime'));
    //echo "priceDateTimeAsString is ".$priceDateTimeAsString."<br/>";
    $priceDate=date( "Y-m-d", strtotime($priceDateTimeAsString));
    
    $this->set(compact('productId'));
    $this->set(compact('existenceOptionId'));
    //$this->set(compact('clientId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    elseif (count($warehouses) > 1 && $warehouseId == 0){
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      elseif (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
        $warehouseId = WAREHOUSE_DEFAULT;
      }
      else {
        $warehouseId=0;
      }
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    
    $product=$this->Product->find('first',[
      'conditions'=>[
        'Product.id'=>$productId
      ],
      'contain'=>[
        'ProductType'
      ],
    ]);
    $this->set(compact('product'));
    
    $priceClientCategories=$this->PriceClientCategory->find('list',[
      'order'=>'PriceClientCategory.category_number ASC'
    ]);
    $this->set(compact('priceClientCategories'));
    
    $clients=$this->ThirdParty->getNonGenericActiveClientList();
    $this->set(compact('clients'));
    
    $existences=$this->StockItem->getAllProductCombinations($warehouseId,$product['ProductType']['id'],!$existenceOptionId,$productId);
    $productType=[
      'name'=>$product['ProductType']['name'],
      'productIds'=>$existences['productIds'],
      'rawMaterialIds'=>$existences['rawMaterialIds'],
    ];
    //pr($existences);  
    $this->set(compact('existences'));
    /*
    $this->StockItem->virtualFields['remaining'] = 0;
    $allProductsRemaining=$this->StockItem->find('all',[
      'fields'=>['product_id','raw_material_id','SUM(`remaining_quantity`) AS StockItem__remaining'],
      'conditions'=>['stockitem_depletion_date >'=>date('Y-m-d')],
      'order'=>['product_id','raw_material_id'],
      'group'=>['product_id','raw_material_id'],
    ]);
    //pr($allProductsRemaining);
    $allProductsExistences=[];
    $onlyExistences=[];
    
    foreach($allProductsRemaining as $productRemaining){
      $remainingProductId=$productRemaining['StockItem']['product_id'];
      $rawMaterialId=(empty($productRemaining['StockItem']['raw_material_id'])?0:$productRemaining['StockItem']['raw_material_id']);
      $remaining=$productRemaining['StockItem']['remaining'];
      $allProductsExistences['Product'][$remainingProductId]['RawMaterial'][$rawMaterialId]['remaining']=$remaining;
      if ($existenceOptionId == SHOW_EXISTING && $remaining>0){
        $onlyExistences[$remainingProductId][$rawMaterialId]['remaining']=$remaining;
      }
    }
    //pr($onlyExistences);    
    
    $rawMaterialTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW],
    ]);
    $rawMaterials=$this->Product->find('list',[
      'conditions'=>['Product.product_type_id'=>$rawMaterialTypeIds,],
      'order'=>'Product.name ASC',
    ]);
    $rawMaterialAbbreviations=$this->Product->find('list',[
      'fields'=>['Product.id','Product.abbreviation'],
      'conditions'=>['Product.product_type_id'=>$rawMaterialTypeIds,],
      'order'=>'Product.name ASC',
    ]);
    $this->set(compact('rawMaterials'));
    */
    
    $priceClientCategoryPriceArray=[];
    $clientPriceArray=[];
    foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
      if ($product['ProductType']['id'] == PRODUCT_TYPE_BOTTLE){
        foreach($existences['existences']['Product'][$productId]['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
          $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTimeAsString);
          $priceClientCategoryPriceArray[$priceClientCategoryId]['RawMaterial'][$rawMaterialId]['price']=$priceInfo['price'];
          $priceClientCategoryPriceArray[$priceClientCategoryId]['RawMaterial'][$rawMaterialId]['price_datetime']=$priceInfo['price_datetime'];
        }
      }
      else {
        $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTimeAsString);
        $priceClientCategoryPriceArray[$priceClientCategoryId]['price']=$priceInfo['price'];
        $priceClientCategoryPriceArray[$priceClientCategoryId]['price_datetime']=$priceInfo['price_datetime'];
      }
    }
    
    foreach ($clients as $clientId=>$clientName){
      if ($product['ProductType']['id'] == PRODUCT_TYPE_BOTTLE){
        foreach($existences['existences']['Product'][$productId]['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
          $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateTimeAsString);
          $clientPriceArray[$clientId]['RawMaterial'][$rawMaterialId]['price']=$priceInfo['price'];
          $clientPriceArray[$clientId]['RawMaterial'][$rawMaterialId]['price_datetime']=$priceInfo['price_datetime'];
        }
      }
      else {
        $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateTimeAsString);
        $clientPriceArray[$clientId]['price']=$priceInfo['price'];
        $clientPriceArray[$clientId]['price_datetime']=$priceInfo['price_datetime'];
      }
    }  
    //pr($clientPriceArray);
    $boolSaved='0';
    if ($this->request->is('post') && empty($this->request->data['changeDate'])) {	      
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDate=new DateTime($latestClosingDate);
      
      if ($priceDateTimeAsString>date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('Los precios no se pueden registrar en el futuro!  No se guardaron los precios.'), 'default',['class' => 'error-message']);
      }
      elseif ($priceDateTimeAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardaron los precios.'), 'default',['class' => 'error-message']);
      }
      else {
        //pr($this->request->data);
        
        foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
          if ($product['ProductType']['id'] == PRODUCT_TYPE_BOTTLE){
            foreach($existences['existences']['Product'][$productId]['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
              $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTimeAsString);
              $priceClientCategoryPriceArray[$priceClientCategoryId]['RawMaterial'][$rawMaterialId]['price']=$priceInfo['price'];
              $priceClientCategoryPriceArray[$priceClientCategoryId]['RawMaterial'][$rawMaterialId]['price_datetime']=$priceInfo['price_datetime'];
            }
          }
          else {
            $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTimeAsString);
            $priceClientCategoryPriceArray[$priceClientCategoryId]['price']=$priceInfo['price'];
            $priceClientCategoryPriceArray[$priceClientCategoryId]['price_datetime']=$priceInfo['price_datetime'];
          }
        }
        //pr($priceClientCategoryPriceArray);
        
        foreach ($clients as $clientId=>$clientName){
          if ($product['ProductType']['id'] == PRODUCT_TYPE_BOTTLE){
            foreach($existences['existences']['Product'][$productId]['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
              $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateTimeAsString);
              $clientPriceArray[$clientId]['RawMaterial'][$rawMaterialId]['price']=$priceInfo['price'];
              $clientPriceArray[$clientId]['RawMaterial'][$rawMaterialId]['price_datetime']=$priceInfo['price_datetime'];
            }
          }
          else {
            $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateTimeAsString);
            $clientPriceArray[$clientId]['price']=$priceInfo['price'];
            $clientPriceArray[$clientId]['price_datetime']=$priceInfo['price_datetime'];
          }
        }  
        
        $datasource=$this->ProductPriceLog->getDataSource();
        $datasource->begin();
        
        try {
          foreach ($this->request->data['PriceClientCategory'] as $requestPriceClientCategoryId=>$requestPriceClientCategoryData){
            if ($product['ProductType']['id'] ==  PRODUCT_TYPE_BOTTLE){
              foreach ($requestPriceClientCategoryData['RawMaterial'] as $requestRawMaterialId=>$requestRawMaterialData){
                //pr ($priceClientCategoryPriceArray);
                //echo 'request price is '.$requestRawMaterialData['price'].'<br/>';
                //echo 'former price is '.$priceClientCategoryPriceArray[$priceClientCategoryId]['RawMaterial'][$requestRawMaterialId]['price'].'<br/>';
                //pr($priceClientCategoryPriceArray[$requestPriceClientCategoryId]);
                //echo 'raw material id is '.$requestRawMaterialId.'<br/>';
                if ($requestRawMaterialData['price'] != $priceClientCategoryPriceArray[$requestPriceClientCategoryId]['RawMaterial'][$requestRawMaterialId]['price']){
                  $priceDateTimeForm= new DateTime($priceDateTimeAsString);
                  $priceDateTimeProduct = new DateTime($priceClientCategoryPriceArray[$requestPriceClientCategoryId]['RawMaterial'][$requestRawMaterialId]['price_datetime']);
                  if ($requestRawMaterialData['price'] > 0 || $priceDateTimeForm->format('d-m-Y') != $priceDateTimeProduct->format('d-m-Y')){
                    $productPriceLogArray=[
                      'product_id'=>$productId,
                      'raw_material_id'=>$requestRawMaterialId,
                      'price'=>$requestRawMaterialData['price'],
                      'client_id'=>null,
                      'price_client_category_id'=>$requestPriceClientCategoryId,
                      'price_datetime'=>$priceDateTimeAsString,
                      'currency_id'=>CURRENCY_CS,
                      'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
                    ];
                    $this->ProductPriceLog->create();
                    //pr($productPriceLogArray);
                    if (!$this->ProductPriceLog->save($productPriceLogArray)) {
                      echo "Problema guardando el precio del producto ".$productId.' para la categoría de precios '.$requestPriceClientCategoryId;
                      pr($this->validateErrors($this->ProductPriceLog));
                      throw new Exception();
                    }
                  }
                  else {
                    // THE PRICE IS ZERO AND THE DATE IS THE SAME
                    $conditions=[
                      'ProductPriceLog.product_id'=>$productId,
                      'ProductPriceLog.raw_material_id'=>$requestRawMaterialId,
                      'ProductPriceLog.price_client_category_id'=>$requestPriceClientCategoryId,
                      'DATE(ProductPriceLog.price_datetime)'=>($priceDateTimeForm->format('Y-m-d')),
                    ];
                    //pr($conditions);
                    $productPriceLogForRemoval=$this->ProductPriceLog->find('first',[
                      'fields'=>['ProductPriceLog.id'],
                      'conditions'=>$conditions,
                      'order'=>[
                        'ProductPriceLog.id DESC',
                      ],
                    ]);  
                    //pr($productPriceLogForRemoval);
                    if (!empty($productPriceLogForRemoval)){
                      if (!$this->ProductPriceLog->delete($productPriceLogForRemoval['ProductPriceLog']['id'])){
                        echo "Problema al eliminar el precio para producto ".$productId;
                        pr($this->validateErrors($this->ProductPriceLog));
                        throw new Exception();
                      }                            
                    }
                  }
                }
              }
            
            }
            else {
              //pr ($priceClientCategoryPriceArray);
              //echo 'request price is '.$requestRawMaterialData['price'].'<br/>';
              //echo 'former price is '.$priceClientCategoryPriceArray[$requestPriceClientCategoryId]['RawMaterial'][$requestRawMaterialId]['price'].'<br/>';
              //pr($priceClientCategoryPriceArray[$requestPriceClientCategoryId]);
              //echo 'raw material id is '.$requestRawMaterialId.'<br/>';
              if ($requestPriceClientCategoryData['price'] != $priceClientCategoryPriceArray[$requestPriceClientCategoryId]['price']){
                $priceDateTimeForm= new DateTime($priceDateTimeAsString);
                $priceDateTimeProduct = new DateTime($priceClientCategoryPriceArray[$requestPriceClientCategoryId]['price_datetime']);
                if ($requestPriceClientCategoryData['price'] > 0 || $priceDateTimeForm->format('d-m-Y') != $priceDateTimeProduct->format('d-m-Y')){
                  $productPriceLogArray=[
                    'product_id'=>$productId,
                    'raw_material_id'=>null,
                    'price'=>$requestPriceClientCategoryData['price'],
                    'client_id'=>null,
                    'price_client_category_id'=>$requestPriceClientCategoryId,
                    'price_datetime'=>$priceDateTimeAsString,
                    'currency_id'=>CURRENCY_CS,
                    'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
                  ];
                  $this->ProductPriceLog->create();
                  //pr($productPriceLogArray);
                  if (!$this->ProductPriceLog->save($productPriceLogArray)) {
                    echo "Problema guardando el precio del producto ".$productId.' para la categoría de precios '.$requestPriceClientCategoryId;
                    pr($this->validateErrors($this->ProductPriceLog));
                    throw new Exception();
                  }
                }
                else {
                  // THE PRICE IS ZERO AND THE DATE IS THE SAME
                  $conditions=[
                    'ProductPriceLog.product_id'=>$productId,
                    'ProductPriceLog.raw_material_id'=>null,
                    'ProductPriceLog.price_client_category_id'=>$requestPriceClientCategoryId,
                    'DATE(ProductPriceLog.price_datetime)'=>($priceDateTimeForm->format('Y-m-d')),
                  ];
                  //pr($conditions);
                  $productPriceLogForRemoval=$this->ProductPriceLog->find('first',[
                    'fields'=>['ProductPriceLog.id'],
                    'conditions'=>$conditions,
                    'order'=>[
                      'ProductPriceLog.id DESC',
                    ],
                  ]);  
                  //pr($productPriceLogForRemoval);
                  if (!empty($productPriceLogForRemoval)){
                    if (!$this->ProductPriceLog->delete($productPriceLogForRemoval['ProductPriceLog']['id'])){
                      echo "Problema al eliminar el precio para producto ".$productId;
                      pr($this->validateErrors($this->ProductPriceLog));
                      throw new Exception();
                    }                            
                  }
                }
              }
            }            
          }
          
          foreach ($this->request->data['Client'] as $requestClientId=>$requestClientData){
            if ($product['ProductType']['id'] ==  PRODUCT_TYPE_BOTTLE){
              foreach ($requestClientData['RawMaterial'] as $requestRawMaterialId=>$requestRawMaterialData){
                //pr ($clientPriceArray);
                //echo 'request price is '.$requestRawMaterialData['price'].'<br/>';
                //echo 'former price is '.$clientPriceArray[$priceClientCategoryId]['RawMaterial'][$requestRawMaterialId]['price'].'<br/>';
                //pr($clientPriceArray[$requestClientId]);
                //echo 'raw material id is '.$requestRawMaterialId.'<br/>';
                if ($requestRawMaterialData['price'] != $clientPriceArray[$requestClientId]['RawMaterial'][$requestRawMaterialId]['price']){
                  $priceDateTimeForm= new DateTime($priceDateTimeAsString);
                  $priceDateTimeProduct = new DateTime($clientPriceArray[$requestClientId]['RawMaterial'][$requestRawMaterialId]['price_datetime']);
                  if ($requestRawMaterialData['price'] > 0 || $priceDateTimeForm->format('d-m-Y') != $priceDateTimeProduct->format('d-m-Y')){
                    $productPriceLogArray=[
                      'product_id'=>$productId,
                      'raw_material_id'=>$requestRawMaterialId,
                      'price'=>$requestRawMaterialData['price'],
                      'client_id'=>$requestClientId,
                      'price_client_category_id'=>null,
                      'price_datetime'=>$priceDateTimeAsString,
                      'currency_id'=>CURRENCY_CS,
                      'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
                    ];
                    $this->ProductPriceLog->create();
                    //pr($productPriceLogArray);
                    if (!$this->ProductPriceLog->save($productPriceLogArray)) {
                      echo "Problema guardando el precio del producto ".$productId.' para la categoría de precios '.$requestClientId;
                      pr($this->validateErrors($this->ProductPriceLog));
                      throw new Exception();
                    }
                  }
                  else {
                    // THE PRICE IS ZERO AND THE DATE IS THE SAME
                    $conditions=[
                      'ProductPriceLog.product_id'=>$productId,
                      'ProductPriceLog.raw_material_id'=>$requestRawMaterialId,
                      'ProductPriceLog.client_id'=>$requestClientId,
                      'DATE(ProductPriceLog.price_datetime)'=>($priceDateTimeForm->format('Y-m-d')),
                    ];
                    //pr($conditions);
                    $productPriceLogForRemoval=$this->ProductPriceLog->find('first',[
                      'fields'=>['ProductPriceLog.id'],
                      'conditions'=>$conditions,
                      'order'=>[
                        'ProductPriceLog.id DESC',
                      ],
                    ]);  
                    //pr($productPriceLogForRemoval);
                    if (!empty($productPriceLogForRemoval)){
                      if (!$this->ProductPriceLog->delete($productPriceLogForRemoval['ProductPriceLog']['id'])){
                        echo "Problema al eliminar el precio para producto ".$productId;
                        pr($this->validateErrors($this->ProductPriceLog));
                        throw new Exception();
                      }                            
                    }
                  }
                }
              }
            
            }
            else {
              //pr ($clientPriceArray);
              //echo 'request price is '.$requestRawMaterialData['price'].'<br/>';
              //echo 'former price is '.$clientPriceArray[$priceClientCategoryId]['RawMaterial'][$requestRawMaterialId]['price'].'<br/>';
              //pr($clientPriceArray[$requestClientId]);
              //echo 'raw material id is '.$requestRawMaterialId.'<br/>';
              if ($requestClientData['price'] != $clientPriceArray[$requestClientId]['price']){
                $priceDateTimeForm= new DateTime($priceDateTimeAsString);
                $priceDateTimeProduct = new DateTime($clientPriceArray[$requestClientId]['price_datetime']);
                if ($requestClientData['price'] > 0 || $priceDateTimeForm->format('d-m-Y') != $priceDateTimeProduct->format('d-m-Y')){
                  $productPriceLogArray=[
                    'product_id'=>$productId,
                    'raw_material_id'=>null,
                    'price'=>$requestClientData['price'],
                    'client_id'=>$requestClientId,
                    'price_client_category_id'=>null,
                    'price_datetime'=>$priceDateTimeAsString,
                    'currency_id'=>CURRENCY_CS,
                    'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
                  ];
                  $this->ProductPriceLog->create();
                  //pr($productPriceLogArray);
                  if (!$this->ProductPriceLog->save($productPriceLogArray)) {
                    echo "Problema guardando el precio del producto ".$productId.' para la categoría de precios '.$requestClientId;
                    pr($this->validateErrors($this->ProductPriceLog));
                    throw new Exception();
                  }
                }
                else {
                  // THE PRICE IS ZERO AND THE DATE IS THE SAME
                  $conditions=[
                    'ProductPriceLog.product_id'=>$productId,
                    'ProductPriceLog.raw_material_id'=>null,
                    'ProductPriceLog.client_id'=>$requestClientId,
                    'DATE(ProductPriceLog.price_datetime)'=>($priceDateTimeForm->format('Y-m-d')),
                  ];
                  //pr($conditions);
                  $productPriceLogForRemoval=$this->ProductPriceLog->find('first',[
                    'fields'=>['ProductPriceLog.id'],
                    'conditions'=>$conditions,
                    'order'=>[
                      'ProductPriceLog.id DESC',
                    ],
                  ]);  
                  //pr($productPriceLogForRemoval);
                  if (!empty($productPriceLogForRemoval)){
                    if (!$this->ProductPriceLog->delete($productPriceLogForRemoval['ProductPriceLog']['id'])){
                      echo "Problema al eliminar el precio para producto ".$productId;
                      pr($this->validateErrors($this->ProductPriceLog));
                      throw new Exception();
                    }                            
                  }
                }
              }
            }            
          }
          
          
          $datasource->commit();
          $this->recordUserAction();
          // SAVE THE USERLOG 
          $this->recordUserActivity($this->Session->read('User.username'),"Se registraron los precios de producto ".$productId."venta para fecha ".$priceDateTimeAsString);
          $this->Session->setFlash("Se registraron los precios de venta para fecha ".$priceDateTimeAsString,'default',['class' => 'success']);
          $boolSaved=true;
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash("No se podían registrar los precios de venta para fecha ".$priceDateTimeAsString, 'default',['class' => 'error-message']);
        }
      }	
    
    }
    $this->set(compact('boolSaved'));
    
    $priceClientCategoryPriceArray=[];
    foreach ($priceClientCategories as $priceClientCategoryId=>$priceClientCategoryName){
      if ($product['ProductType']['id'] == PRODUCT_TYPE_BOTTLE){
        foreach($existences['existences']['Product'][$productId]['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
          $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForPriceClientCategoryBeforeDate($productId,$rawMaterialId,$priceClientCategoryId,$priceDateTimeAsString);
          $priceClientCategoryPriceArray[$priceClientCategoryId]['RawMaterial'][$rawMaterialId]['price']=$priceInfo['price'];
          $priceClientCategoryPriceArray[$priceClientCategoryId]['RawMaterial'][$rawMaterialId]['price_datetime']=$priceInfo['price_datetime'];
        }
      }
      else {
        $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForPriceClientCategoryBeforeDate($productId,$priceClientCategoryId,$priceDateTimeAsString);
        $priceClientCategoryPriceArray[$priceClientCategoryId]['price']=$priceInfo['price'];
        $priceClientCategoryPriceArray[$priceClientCategoryId]['price_datetime']=$priceInfo['price_datetime'];
      }
    }
    $this->set(compact('priceClientCategoryPriceArray'));
    //pr($priceClientCategoryPriceArray);
    foreach ($clients as $clientId=>$clientName){
      if ($product['ProductType']['id'] == PRODUCT_TYPE_BOTTLE){
        foreach($existences['existences']['Product'][$productId]['RawMaterial'] as $rawMaterialId=>$rawMaterialData){
          $priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($productId,$rawMaterialId,$clientId,$priceDateTimeAsString);
          $clientPriceArray[$clientId]['RawMaterial'][$rawMaterialId]['price']=$priceInfo['price'];
          $clientPriceArray[$clientId]['RawMaterial'][$rawMaterialId]['price_datetime']=$priceInfo['price_datetime'];
        }
      }
      else {
        $priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($productId,$clientId,$priceDateTimeAsString);
        $clientPriceArray[$clientId]['price']=$priceInfo['price'];
        $clientPriceArray[$clientId]['price_datetime']=$priceInfo['price_datetime'];
      }
    }  
    /*
    if (array_key_exists('RawMaterial',$clientPriceArray) && count($clientPriceArray['RawMaterial'])>1){      //echo "sorting the preformas for the client price array<br/>";
      uasort($clientPriceArray['RawMaterial'],[$this,'sortByRawMaterial']);  
    }
    */
    $this->set(compact('clientPriceArray'));
    //pr($clientPriceArray);
    
    $excludedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>[
        'ProductType.product_category_id'=>[CATEGORY_RAW,CATEGORY_CONSUMIBLE],
      ],
    ]);
    
    $products=$this->Product->find('list',[
      'conditions'=>[
        'Product.product_type_id !='=>$excludedProductTypeIds,
      ],
      'order'=>['Product.product_type_id ASC','Product.name ASC'],
    ]);
    $this->set(compact('products'));
    
    $currencies=$this->Currency->find('list');
    $this->set(compact('currencies'));
    
    $priceClientCategoryColors=$this->PriceClientCategory->find('list',[
      'fields'=>['id','hexcolor'],
    ]);
    $this->set(compact('priceClientCategoryColors'));
    
    $aco_name="ProductPriceLogs/registrarPreciosCliente";		
		$boolRegistrarPreciosCliente=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolRegistrarPreciosCliente'));
  }
    
  public function reportePreciosPorFactura($clientId=0){
    $this->loadModel('ClosingDate');
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductCategory');
    $this->loadModel('Currency');
    $this->loadModel('ThirdParty');
    $this->loadModel('Order');
    $this->loadModel('StockItem');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    $warehouseId=0;
    $priceDateTime = date('y-m-d H:i:s');
    
    $productCategoryId=CATEGORY_PRODUCED;
    
    //$existenceOptions=[
    //  0=>'Mostrar solamente productos con existencia',
    //  1=>'Mostrar todos productos',
    //];
    //$this->set(compact('existenceOptions'));
    //define('SHOW_EXISTING','0');
    //define('SHOW_ALL','1');
    //$existenceOptionId=0;
    
    if ($this->request->is('post')) {
      $startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
    
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $warehouseId=$this->request->data['ProductPriceLog']['warehouse_id'];
      //$productCategoryId=$this->request->data['ProductPriceLog']['product_category_id'];
      //$existenceOptionId=$this->request->data['ProductPriceLog']['existence_option_id'];
      $clientId=$this->request->data['ProductPriceLog']['client_id'];
		}
    else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
    }
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
    
    $this->set(compact('startDate','endDate'));
		
    //$this->set(compact('productCategoryId'));
    //$this->set(compact('existenceOptionId'));
    $this->set(compact('clientId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
    elseif (count($warehouses) > 1 && $warehouseId == 0){
      if (!empty($_SESSION['warehouseId'])){
        $warehouseId = $_SESSION['warehouseId'];
      }
      elseif (array_key_exists(WAREHOUSE_DEFAULT,$warehouses)){
        $warehouseId = WAREHOUSE_DEFAULT;
      }
      else {
        $warehouseId=0;
      }
    }
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    
    
    //echo "client id is ".$clientId.'<br/>';
    $clients=$this->ThirdParty->getNonGenericActiveClientList();
    //$clients=[];
    $clients=[CLIENTS_VARIOUS => 'Clientes Varios'] + $clients;
    
    //pr($clients);
    $this->set(compact('clients'));
    
    $productTypeIds=$this->ProductType->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>['ProductType.product_category_id'=>$productCategoryId],
    ]);
    $selectedProductIds=$this->Product->find('list',[
      'fields'=>'Product.id',
      'conditions'=>['Product.product_type_id'=>$productTypeIds],
    ]);
    
    $invoiceConditions=[
      'Order.order_date >='=>$startDate,
      'Order.order_date <'=>$endDatePlusOne,
      'Order.stock_movement_type_id'=>MOVEMENT_SALE,
    ];
    if ($clientId>0){
      $invoiceConditions['Order.third_party_id']=$clientId;
    }
    //pr($invoiceConditions);
    $invoices=$this->Order->find('all',[
      'fields'=>['Order.id','Order.order_date','Order.order_code','Order.third_party_id',],
      'conditions'=>$invoiceConditions,
      'contain'=>[
        'StockMovement'=>[
          'fields'=>[
            'StockMovement.id','StockMovement.stockitem_id',
            'StockMovement.product_id','StockMovement.product_unit_price',
          ],
          'conditions'=>[
            'StockMovement.product_id'=>$selectedProductIds,
            'StockMovement.product_quantity >'=>0,
          ],  
          'StockItem'=>[
            'fields'=>['StockItem.id','StockItem.raw_material_id',],
          ],          
        ],
        'ThirdParty'=>[
          'fields'=>['ThirdParty.id','ThirdParty.company_name',],
        ],
      ],
      'order'=>['Order.order_date DESC','Order.order_code DESC'],
    ]);
    //pr($invoices);
    
    $involvedStockItemIds=[];
    $involvedProductIds=[];
    if (!empty($invoices)){
      foreach ($invoices as $invoice){
        if (!empty($invoice['StockMovement'])){
          foreach ($invoice['StockMovement'] as $stockMovement){
            $involvedStockItemIds[]=$stockMovement['StockItem']['id'];
            $involvedProductIds[]=$stockMovement['product_id'];
          }
        }
      }
    }
    //pr($involvedStockItemIds);
    $involvedStockItemIds=array_unique($involvedStockItemIds);
    $involvedProductIds=array_unique($involvedProductIds);
    //pr($involvedStockItemIds);
    $this->StockItem->virtualFields['remaining'] = 0;
    $allProductsRemaining=$this->StockItem->find('all',[
      'fields'=>['product_id','raw_material_id','SUM(`remaining_quantity`) AS StockItem__remaining'],
      'conditions'=>['StockItem.id'=>$involvedStockItemIds],
      'order'=>['product_id','raw_material_id'],
      'group'=>['product_id','raw_material_id'],
    ]);
    //pr($allProductsRemaining);
    $allProductsExistences=[];
    foreach($allProductsRemaining as $productRemaining){
      $productId=$productRemaining['StockItem']['product_id'];
      $rawMaterialId=(empty($productRemaining['StockItem']['raw_material_id'])?0:$productRemaining['StockItem']['raw_material_id']);
      $remaining=$productRemaining['StockItem']['remaining'];
      $allProductsExistences['Product'][$productId]['RawMaterial'][$rawMaterialId]['remaining']=$remaining;
    }
    //pr($allProductsExistences);
    $rawMaterialTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW],
    ]);
    $rawMaterials=$this->Product->find('list',[
      'conditions'=>['Product.product_type_id'=>$rawMaterialTypeIds,],
      'order'=>'Product.name ASC',
    ]);
    $rawMaterialAbbreviations=$this->Product->find('list',[
      'fields'=>['Product.id','Product.abbreviation'],
      'conditions'=>['Product.product_type_id'=>$rawMaterialTypeIds,],
      'order'=>'Product.name ASC',
    ]);
    $this->set(compact('rawMaterials'));
    
    $invoiceProductPriceArray=[];

    $productTypeConditions=[
      'ProductType.product_category_id'=>$productCategoryId,
    ];  
    $productTypes=$this->ProductType->find('all',[
      'conditions'=>$productTypeConditions,
      'contain'=>[
        'Product'=>[
          'fields'=>[
            'Product.id','Product.name','Product.product_type_id',
          ],
          'conditions'=>['Product.id'=>$involvedProductIds],
          'order'=>'Product.name ASC',
        ],
      ],
      'order'=>['ProductType.product_category_id ASC','ProductType.id ASC']
    ]);
    //pr($productTypes);
    
    foreach ($productTypes as $productType){
      $productTypeArray=[
        'name'=>$productType['ProductType']['name'],
        'Product'=>[],
      ];
      
      foreach ($invoices as $invoice){
        //pr($invoice);
        $invoiceArray=[
          'Invoice'=>$invoice['Order'],
          'Product'=>[]];
        foreach ($productType['Product'] as $product){
          //echo "product id is ".$product['id']."<br>";
          if (array_key_exists($product['id'],$allProductsExistences['Product'])){
            //echo "product with id ".$product['id']." is present in allProductsExistences<br>";
            if ($product['product_type_id'] == PRODUCT_TYPE_BOTTLE){
              if (!array_key_exists($product['id'],$productTypeArray['Product'])){
                $productTypeArray['Product'][$product['id']]=[
                  'name'=>$product['name'],
                  'RawMaterial'=>[],
                ];
              }
              
              // HANDLE BOTTLES 
              //if ($product['id']==33){
              //  pr($allProductsExistences['Product'][$product['id']]);
              //}
              //if (!empty($allProductsExistences['Product'][$product['id']]['RawMaterial'])){
                //foreach ($allProductsExistences['Product'][$product['id']]['RawMaterial'] as $rawMaterialId=>$remainingQuantity){
              if (!empty($invoice['StockMovement'])){
                foreach ($invoice['StockMovement'] as $stockMovement){
                  if ($stockMovement['product_id']==$product['id']){ 
                    $invoiceArray['Product'][$product['id']]=[];
                    //echo "raw material id is ".$stockMovement['StockItem']['raw_material_id']."<br/>";  
                    if (!array_key_exists($stockMovement['StockItem']['raw_material_id'],$productTypeArray['Product'][$product['id']]['RawMaterial'])){
                      //echo "raw material id is ".$rawMaterialId."<br/>";  
                      $productTypeArray['Product'][$product['id']]['RawMaterial'][$stockMovement['StockItem']['raw_material_id']]=[
                        'name'=>$rawMaterials[$stockMovement['StockItem']['raw_material_id']],
                        'abbreviation'=>$rawMaterialAbbreviations[$stockMovement['StockItem']['raw_material_id']]
                      ];
                    }
                    
                    if (!array_key_exists('RawMaterial',$invoiceArray['Product'][$product['id']])){
                      $invoiceArray['Product'][$product['id']]['RawMaterial']=[];
                    }
                    //$priceInfo=$this->ProductPriceLog->getLatestPriceAndDateForRawMaterialForClientBeforeDate($product['id'],$stockMovement['StockItem']['raw_material_id'],$clientId,$priceDateTimeAsString);
                    $invoiceArray['Product'][$product['id']]['RawMaterial'][$stockMovement['StockItem']['raw_material_id']]=[
                      'name'=>$rawMaterials[$stockMovement['StockItem']['raw_material_id']],
                      'invoicePrice'=>$stockMovement['product_unit_price'],
                      //'clientPrice'=>$priceInfo['price'],
                      //'clientPriceDatetime'=>$priceInfo['price_datetime'],
                      //'remainingQuantity'=>$remainingQuantity['remaining'],
                    ];
                  }
                }
              }          
            }
            else {
              if (!array_key_exists($product['id'],$productTypeArray['Product'])){
                $productTypeArray['Product'][$product['id']]=[
                  'name'=>$product['name'],
                ];
              }
              if (!empty($invoice['StockMovement'])){
                foreach ($invoice['StockMovement'] as $stockMovement){
                  if ($stockMovement['product_id']==$product['id']){  
                    $invoiceArray['Product'][$product['id']]=[];
                    //$priceInfo=$this->ProductPriceLog->getLatestNonBottlePriceAndDateForClientBeforeDate($product['id'],$clientId,$priceDateTimeAsString);
                    $invoiceArray['Product'][$product['id']]=[
                      'name'=>$product['name'],
                      'invoicePrice'=>$stockMovement['product_unit_price'],
                      //'clientPrice'=>$priceInfo['price'],
                      //'clientPriceDatetime'=>$priceInfo['price_datetime'],
                    ];
                  }
                }
              }    
            }
          }  
        }
        foreach ($invoiceArray['Product'] as $productId=>$productData){
          if (!empty($productData['RawMaterial']) && count($productData['RawMaterial'])>1){
            uasort($invoiceArray['Product'][$productId]['RawMaterial'],[$this,'sortByRawMaterial']);  
          }
        }
        $invoiceProductPriceArray['ProductType'][$productType['ProductType']['id']]['Invoice'][$invoice['Order']['id']]=$invoiceArray;
      }
      
      foreach ($productTypeArray['Product'] as $productId=>$productData){
        if (!empty($productData['RawMaterial']) && count($productData['RawMaterial'])>1){
          uasort($productTypeArray['Product'][$productId]['RawMaterial'],[$this,'sortByRawMaterial']);  
        }
      }
        
      $invoiceProductPriceArray['ProductType'][$productType['ProductType']['id']]['ProductTypeInfo']=$productTypeArray;
    }
    
    //pr($invoiceProductPriceArray);
    
    $this->set(compact('invoiceProductPriceArray'));
    
    $productCategories=$this->ProductCategory->find('list',[
      'conditions'=>[
        'ProductCategory.id !='=>[CATEGORY_RAW,CATEGORY_CONSUMIBLE],
      ],
    ]);
    $this->set(compact('productCategories'));
    
    $currencies=$this->Currency->find('list');
    $this->set(compact('currencies'));
    
    $aco_name="Orders/verVenta";		
		$boolVerVenta=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('boolVerVenta'));
    
    //$aco_name="ProductPriceLogs/registrarPreciosCliente";		
		//$boolRegistrarPreciosCliente=$this->hasPermission($this->Auth->User('id'),$aco_name);
		//$this->set(compact('boolRegistrarPreciosCliente'));
  }
  public function guardarReportePreciosPorFactura($fileName) {
		$exportData=$_SESSION['reportePreciosPorFactura'];
		$this->set(compact('exportData','fileName'));
	}
  
}
