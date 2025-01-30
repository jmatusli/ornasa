<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class QuotationsController extends AppController {

	public $components = ['Paginator','RequestHandler'];
	public $helpers = ['PhpExcel'];
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('getQuotationCode','getQuotationInfo','getQuotationProducts','getquotationcurrencyid','getquotationiva','getquotationsforclient','generarOrdenDeVenta','detallePdf');		
	}
  public function getQuotationCode(){
		$this->layout= "ajax";
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		
    $userId=trim($_POST['userId']);
		$quotationDateDay=trim($_POST['quotationDateDay']);
		$quotationDateMonth=trim($_POST['quotationDateMonth']);
		$quotationDateYear=trim($_POST['quotationDateYear']);
    $warehouseId=trim($_POST['warehouseId']);
		if (!$userId){
			throw new NotFoundException(__('Usuario no presente'));
		}
    
		$this->loadModel('User');
    $selectedUser=$this->User->find('first',[
			'conditions'=>['User.id'=>$userId,],
		]);
		$userName=$selectedUser['User']['username'];
     $this->loadModel('Warehouse');
    $warehouseSeries=$this->Warehouse->getWarehouseSeries($warehouseId);
    
   return $this->Quotation->getQuotationCode($userName, $warehouseId,$warehouseSeries,$quotationDateDay,$quotationDateMonth,$quotationDateYear);
	}
	
  public function getQuotationInfo() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax'); 
		$this->layout = "ajax";
    
		$quotationId=trim($_POST['quotationId']);
		if (!$quotationId){
			throw new NotFoundException(__('Cotizacion no presente'));
		}
		
		// $this->InvoiceProduct->virtualFields['total_product_quantity']=0;
		$quotation=$this->Quotation->find('first',[
			'fields'=>[
				'Quotation.quotation_code',
        'Quotation.quotation_date',
        'Quotation.client_id',
        'Quotation.client_name','Quotation.client_phone','Quotation.client_email',
        'Quotation.client_ruc','Quotation.client_address',
        'Quotation.client_type_id','Quotation.zone_id',
        'Quotation.currency_id',
        'Quotation.bool_credit',
        //'Quotation.due_date','Quotation.delivery_time',
        'Quotation.vendor_user_id','Quotation.credit_authorization_user_id',
        'Quotation.bool_iva',
        'Quotation.bool_retention',
        'Quotation.retention_number',
			],
			'conditions'=>[
				'Quotation.id'=>$quotationId,
			],
			'contain'=>[
        'Client'=>[
          'fields'=>['bool_generic'],
        ],
				'VendorUser'=>[
					'fields'=>['VendorUser.id','VendorUser.username','VendorUser.first_name','VendorUser.last_name'],
				],
			],
		]);
		return json_encode($quotation);
	}
  
  public function getQuotationProducts() {
		$this->layout = "ajax";
		
		$quotationId=$_POST['quotationId'];
    $warehouseId=$_POST['warehouseId'];
		$currencyId=trim($_POST['currencyId']);
		$exchangeRate=trim($_POST['exchangeRate']);
    
    $salesOrderDay=trim($_POST['salesOrderDay']);
    $salesOrderMonth=trim($_POST['salesOrderMonth']);
    $salesOrderYear=trim($_POST['salesOrderYear']);
    
    $this->set(compact('salesOrderId'));
		$this->set(compact('currencyId','exchangeRate'));
		
		if (!empty($quotationId)){
      $this->loadModel('QuotationProduct');
      $this->loadModel('Product');
      
      $this->loadModel('StockItem');
      
      $salesOrderDateString=$salesOrderYear.'-'.$salesOrderMonth.'-'.$salesOrderDay;
      $salesOrderDate=date( "Y-m-d", strtotime($salesOrderDateString));
      
      $quotationProductConditions=[
        'QuotationProduct.quotation_id'=>$quotationId,
      ];
		
      $this->QuotationProduct->recursive=-1;
      
      $productsForQuotation=$this->QuotationProduct->find('all',[
        'fields'=>[
          'QuotationProduct.id',
            'QuotationProduct.product_id',
            'QuotationProduct.raw_material_id',
            'QuotationProduct.product_unit_price',
            'QuotationProduct.product_quantity',
            'QuotationProduct.product_total_price',
            'QuotationProduct.currency_id',
        ],
        'recursive'=>-1,
        'conditions'=>$quotationProductConditions,
      ]);
      //pr($productsForQuotation);
      if (!empty($productsForQuotation)){
        for ($qp=0;$qp<count($productsForQuotation);$qp++){
          $productId=$productsForQuotation[$qp]['QuotationProduct']['product_id'];
          $rawMaterialId=$productsForQuotation[$qp]['QuotationProduct']['raw_material_id'];
          $productInventory=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$salesOrderDate,$warehouseId,$rawMaterialId);
          $productCost=0;
          if ($productInventory['quantity'] > 0){
            $productCost=round($productInventory['value']/$productInventory['quantity'],2);
          }
          $productsForQuotation[$qp]['QuotationProduct']['product_unit_cost']=$productCost;
        } 
        //pr($productsForQuotation);
      }
    }  
		$this->set(compact('productsForQuotation'));
		
    $this->loadModel('ProductType');
    $this->loadModel('Product');
    $this->loadModel('ProductionResultCode');
    
    $stockItemConditions=[
      'StockItem.bool_active'=>true,
      'StockItem.warehouse_id'=>[$warehouseId,WAREHOUSE_INJECTION],
    ];
    $stockItemConditions[]=[];
    $excludedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>['ProductType.product_category_id'=>[CATEGORY_RAW,CATEGORY_CONSUMIBLE]],
    ]);
    
		$productsAll = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
      'conditions'=>[
        'Product.bool_active'=>true,
        'Product.product_type_id !='=>$excludedProductTypeIds,
      ],
			'contain'=>[
				'ProductType',
				'StockItem'=>[
					'fields'=> ['remaining_quantity','raw_material_id','production_result_code_id'],
          'conditions'=>$stockItemConditions,
				],
			],
			'order'=>'product_type_id DESC, name ASC',
		]);
		$products = [];
		$rawMaterialIds=[];
    $rawMaterialsAvailablePerFinishedProduct=[];

		foreach ($productsAll as $product){
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockItem){
					if ($stockItem['remaining_quantity']>0){
            $productId=$product['Product']['id'];
						$products[$productId]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
            if (!empty($stockItem['raw_material_id'])){
              $rawMaterialId=$stockItem['raw_material_id'];
              $productionResultCodeId=$stockItem['production_result_code_id'];
              if (!in_array($rawMaterialId,$rawMaterialIds)){
                $rawMaterialIds[]=$rawMaterialId;
              }
              if (!array_key_exists($productId,$rawMaterialsAvailablePerFinishedProduct)){
                $rawMaterialsAvailablePerFinishedProduct[$productId]=[];
              }
              if (!array_key_exists($rawMaterialId,$rawMaterialsAvailablePerFinishedProduct[$productId])){
                $rawMaterialsAvailablePerFinishedProduct[$productId][$rawMaterialId]=[
                  '1'=>0,
                  '2'=>0,
                  '3'=>0
                ];
              }
              $rawMaterialsAvailablePerFinishedProduct[$productId][$rawMaterialId][$productionResultCodeId]+=$stockItem['remaining_quantity'];
            }  
          }            
				}
			}
      elseif ($product['ProductType']['id'] == PRODUCT_TYPE_SERVICE){
        $products[$product['Product']['id']]=substr($product['Product']['name'],0,18).(strlen($product['Product']['name'])>18?"...":"");
      }
		}
		$this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    
		$productionResultCodes=$this->ProductionResultCode->find('list',[
      'conditions'=>['ProductionResultCode.id'=>PRODUCTION_RESULT_CODE_A]
    ]);
    
    $this->Product->recursive=-1;
		$preformasAll = $this->Product->find('all',[
      'fields'=>['Product.id','Product.name'],
      'conditions' => [
       'Product.id'=>$rawMaterialIds,
       'Product.bool_active'=>true
      ],
      'order'=>'Product.name',
		]);
    
		$rawMaterials=[];
		foreach ($preformasAll as $preforma){
      $startingPosition=0;
      if (strpos ($preforma['Product']['name'],"PREFORMA") !== false){
        $preforma['Product']['name']=str_replace("PREFORMA ","",$preforma['Product']['name']);
      }
			$rawMaterials[$preforma['Product']['id']]=substr($preforma['Product']['name'],0,18).(strlen($preforma['Product']['name'])>18?"...":"");
		}
    //pr($rawMaterials);
    $this->set(compact('products','rawMaterials','productionResultCodes'));
    // ADDED 20201016 NOT IN USE YET
    $otherProducts=$this->Product->find('list',[
      'fields'=>'Product.id',
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_SERVICE]
    ]);
    $otherProducts=array_values($otherProducts);
    $this->set(compact('otherProducts'));
    // ADDED 20201016 NOT IN USE YET
    $this->loadModel('UserAction');
    $userActions=$this->UserAction->find('all',[
      'conditions'=>[
        'UserAction.controller_name'=>'quotations',
        'UserAction.item_id'=>$quotationId,
      ],
      'contain'=>['User'],
      'limit'=>1,
      'order'=>'action_datetime DESC',
    ]);
    //pr($userActions);
    $lastModifyingUserRoleId = 0;
    if (!empty($userActions) && ($userActions[0]['UserAction']['action_name'] === "crear" || $userActions[0]['UserAction']['action_name'] === "editar")){
        $lastModifyingUserRoleId=$userActions[0]['User']['role_id'];
    }
    $this->set(compact('lastModifyingUserRoleId'));  
	}
	
	public function getquotationcurrencyid(){
		$this->layout= "ajax";
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		
		$this->loadModel('User');
		$quotationId=trim($_POST['quotationid']);
		if (!$quotationId){
			throw new NotFoundException(__('Cotización no presente'));
		}
		
		$quotation=$this->Quotation->find('first',['conditions'=>['Quotation.id'=>$quotationId]]);
		if (!empty($quotation)){
			return $quotation['Quotation']['currency_id'];
		}
		return CURRENCY_CS;
	}
	
	public function getquotationiva(){
		$this->layout= "ajax";
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		
		$this->loadModel('User');
		$quotationId=trim($_POST['quotationid']);
		if (!$quotationId){
			throw new NotFoundException(__('Cotización no presente'));
		}
		
		$quotation=$this->Quotation->find('first',['conditions'=>['Quotation.id'=>$quotationId]]);
		if (!empty($quotation)){
			return $quotation['Quotation']['bool_iva'];
		}
		return true;
	}
	
	public function getquotationsforclient() {
		$this->layout = "ajax";
		
		$clientid=trim($_POST['clientid']);
		$userId=trim($_POST['userid']);
		if (!$clientid){
			throw new NotFoundException(__('Cliente no presente'));
		}
		
		$this->loadModel('Client');
		$quotationConditions=[
			'Quotation.client_id'=>$clientid,
		];
		if ($userId>0){
			$quotationConditions[]=['Quotation.vendor_user_id'=>$userId,];
		}
		
		// $this->InvoiceProduct->virtualFields['total_product_quantity']=0;
		$quotationsForClient=$this->Quotation->find('all',[
			'fields'=>[
				'Quotation.id','Quotation.quotation_code',
			],
			'conditions'=>$quotationConditions,
			'order'=>'Quotation.quotation_code',
		]);
		//pr($quotationsForClient);
		$this->set(compact('quotationsForClient'));
	}

	public function resumen() {
		$this->loadModel('SalesOrder');
    $this->loadModel('ExchangeRate');
    $this->loadModel('Currency');
    
    $this->loadModel('ClientType');
		$this->loadModel('Zone');
		
    $this->loadModel('PriceClientCategory');
    $this->loadModel('Product');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    $this->Quotation->recursive = -1;
		$this->User->recursive = -1;
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $canSeeExecutiveSummary=$this->UserPageRight->hasUserPageRight('VER_RESUMEN_EJECUTIVO',$userRoleId,$loggedUserId,'Quotations','resumen');
    $this->set(compact('canSeeExecutiveSummary'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'Quotations','resumen');
    $this->set(compact('canSeeAllUsers'));
    
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'Quotations','resumen');
    $this->set(compact('canSeeAllVendors'));
    
    define('QUOTATIONS_ALL',0);
    define('QUOTATIONS_WITH_SALESORDER',1);
    define('QUOTATIONS_WITHOUT_SALESORDER',2);
    
    $salesOrderOptions=[
      QUOTATIONS_ALL=>"Mostrar todas cotizaciones (con y sin orden de venta)",
      QUOTATIONS_WITH_SALESORDER=>"Mostrar solamente cotizaciones con ordenes de venta",
      QUOTATIONS_WITHOUT_SALESORDER=>"Mostrar solamente cotizaciones sin ordenes de venta"
    ];
		$this->set(compact('salesOrderOptions'));
		
    define('QUOTATIONS_REJECTED',1);
    define('QUOTATIONS_PENDING',2);
    
		$rejectedOptions=[
      QUOTATIONS_ALL=>"Mostrar todas cotizaciones (caídas y vigentes)",
      QUOTATIONS_REJECTED=>"Mostrar solamente cotizaciones caídas",
      QUOTATIONS_PENDING=>"Mostrar solamente cotizaciones vigentes",
    ];
		$this->set(compact('rejectedOptions'));
    
		$rejectedDisplay=0;
		$salesOrderDisplay=0;
		
    $warehouseId=0;
		$userId=$loggedUserId;
		$currencyId=CURRENCY_USD;
        
    $clientTypeId=0;
    $zoneId=0;
    
    if ($userRoleId == ROLE_ADMIN  || $canSeeAllUsers || $canSeeAllVendors){
      $userId=0;
    }  
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
      $warehouseId=$this->request->data['Report']['warehouse_id'];
			$userId=$this->request->data['Report']['user_id'];
			$currencyId=$this->request->data['Report']['currency_id'];
      
      $clientTypeId=$this->request->data['Report']['client_type_id'];
      $zoneId=$this->request->data['Report']['zone_id'];
			
			$rejectedDisplay=$this->request->data['Report']['rejected_display'];
			$salesOrderDisplay=$this->request->data['Report']['sales_order_display'];
		}		
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			if ($this->Session->check('currencyId')){
				$currencyId=$_SESSION['currencyId'];
			}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$_SESSION['currencyId']=$currencyId;
		$_SESSION['userId']=$userId;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('currencyId','userId'));
    $this->set(compact('clientTypeId'));
    $this->set(compact('zoneId'));
		$this->set(compact('rejectedDisplay','salesOrderDisplay'));
		
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
    
		$conditions=[
			'Quotation.quotation_date >='=>$startDate,
			'Quotation.quotation_date <'=>$endDatePlusOne,
      'Quotation.warehouse_id'=>$warehouseId,
		];
    if ($clientTypeId>0){
      $conditions['Quotation.client_type_id']=$clientTypeId;
    }
    if ($zoneId>0){
      $conditions['Quotation.zone_id']=$zoneId;
    }
		
    $userConditions=['User.bool_active'=>true];
		if ($userRoleId != ROLE_ADMIN && !$canSeeAllUsers && !$canSeeAllVendors){
      $userConditions['User.id']=$loggedUserId;
		}
		$userPeriod=$this->User->find('list',[
      'conditions'=>$userConditions,
      'order'=>'User.username',
    ]);
    foreach ($userPeriod as $key=>$value){
        $userPeriod[$key]=0;
    }
    //pr($userPeriod);
    
    $userPendingCS=$userPendingUSD=$userPeriodCS=$userPeriodUSD=$userPeriod;
    
    if ($userId>0){
      $conditions['Quotation.vendor_user_id']=$userId;
    }
    switch ($rejectedDisplay){
			case QUOTATIONS_ALL:
				break;
			case QUOTATIONS_REJECTED:
				$conditions['Quotation.bool_rejected']=true;
				break;
			case QUOTATIONS_PENDING:
				$conditions['Quotation.bool_rejected']='0';
				break;	
		}
		
		$quotationCount=$this->Quotation->find('count', [
			'fields'=>['Quotation.id'],
			'conditions' => $conditions,
		]);
		
		$this->Paginator->settings = [
			'fields'=>[
				'Quotation.id',
				'Quotation.quotation_date',
				'Quotation.due_date',
				'Quotation.quotation_code',
				'Quotation.price_subtotal','Quotation.price_iva','Quotation.price_total',
				'Quotation.bool_iva',
				'Quotation.bool_rejected',
        'Quotation.client_name',
        'Quotation.bool_sales_order',
			],
			'conditions' => $conditions,
			'contain'=>[				
				'Client'=>[
					'fields'=>['Client.id','Client.company_name','Client.bool_generic',],
				],
        'ClientType'=>[
          'fields'=>['ClientType.id','ClientType.name','ClientType.hex_color',]
        ],
				'Currency'=>[
					'fields'=>['Currency.id','Currency.abbreviation',],
				],
				'SalesOrder'=>[
					'fields'=>['SalesOrder.id','SalesOrder.sales_order_code'],
					'conditions'=>[
						'SalesOrder.bool_annulled'=>'0',
					],
				],
				'VendorUser'=>[
					'fields'=>[
						'VendorUser.id','VendorUser.username',
						'VendorUser.first_name','VendorUser.last_name',
					],
				],
        'QuotationProduct'=>[
					'fields'=>[
						'QuotationProduct.id','QuotationProduct.product_total_price',
					],
				],
				'QuotationRemark'=>[
					'order'=>'QuotationRemark.reminder_date DESC'
				],
			],
			'order'=>'Quotation.quotation_date DESC, Quotation.quotation_code DESC',
			'limit'=>($quotationCount!=0?$quotationCount:1),
		];
		$quotations = $this->Paginator->paginate('Quotation');
		
		//pr($quotations);
		if (!empty($quotations)){
			for ($i=0;$i<count($quotations);$i++){
				// set the exchange rate
				$quotationDate=$quotations[$i]['Quotation']['quotation_date'];
				$quotations[$i]['Quotation']['exchange_rate']=$this->ExchangeRate->getApplicableExchangeRateValue($quotationDate);
				if(array_key_exists($quotations[$i]['VendorUser']['id'],$userPeriod)){
          if ($quotations[$i]['Currency']['id']==CURRENCY_CS){
            $userPeriodCS[$quotations[$i]['VendorUser']['id']]+=$quotations[$i]['Quotation']['price_subtotal'];
            $userPeriodUSD[$quotations[$i]['VendorUser']['id']]+=round($quotations[$i]['Quotation']['price_subtotal']/$quotations[$i]['Quotation']['exchange_rate'],2);
          }
          elseif ($quotations[$i]['Currency']['id']==CURRENCY_USD){
            $userPeriodUSD[$quotations[$i]['VendorUser']['id']]+=$quotations[$i]['Quotation']['price_subtotal'];
            $userPeriodCS[$quotations[$i]['VendorUser']['id']]+=round($quotations[$i]['Quotation']['price_subtotal']*$quotations[$i]['Quotation']['exchange_rate'],2);
          }  
        }				
			}
		}
		
		$this->set(compact('quotations'));
		$this->set(compact('userPeriodCS','userPeriodUSD'));
		// pending quotations from previous months
		
		$quotationIdsWithSalesOrder=$this->SalesOrder->find('list',[
			'fields'=>['SalesOrder.quotation_id'],
			'conditions'=>[
				'SalesOrder.bool_annulled'=>'0',
			],
		]);
		
		$conditions=[
			'Quotation.quotation_date <'=>$startDate,
			'Quotation.bool_rejected'=>'0',
			'Quotation.id !='=>$quotationIdsWithSalesOrder,
		];
		
    if ($userId>0){
      $conditions['Quotation.vendor_user_id']=$userId;
    }
		$pendingQuotationCount=$this->Quotation->find('count', [
			'fields'=>['Quotation.id'],
			'conditions' => $conditions,
		]);
		//echo "pending quotation count is ".$pendingQuotationCount."<br/>";
		
		$this->Paginator->settings = [
			'fields'=>[
				'Quotation.id',
				'Quotation.quotation_date',
				'Quotation.due_date',
				'Quotation.quotation_code',
				'Quotation.price_subtotal','Quotation.price_iva','Quotation.price_total',
				'Quotation.bool_iva',
        'Quotation.bool_rejected',
        'Quotation.client_name',
        'Quotation.bool_sales_order',
			],
			'conditions' => $conditions,
			'contain'=>[				
				'Client'=>[
					'fields'=>['Client.id','Client.company_name','Client.bool_generic',],
				],
        'ClientType'=>[
          'fields'=>['ClientType.id','ClientType.name','ClientType.hex_color',]
        ],
				'Currency'=>[
					'fields'=>['Currency.id','Currency.abbreviation',],
				],
				'SalesOrder'=>[
					'fields'=>['SalesOrder.id','SalesOrder.sales_order_code'],
					'conditions'=>[
						'SalesOrder.bool_annulled'=>'0',
					],
				],
				'VendorUser'=>[
					'fields'=>[
						'VendorUser.id','VendorUser.username',
						'VendorUser.first_name','VendorUser.last_name',
					],
				],
        'QuotationProduct'=>[
					'fields'=>[
						'QuotationProduct.id','QuotationProduct.product_total_price',
					],
				],
				'QuotationRemark'=>[
					'order'=>'QuotationRemark.reminder_date DESC'
				],
			],
			'order'=>'Quotation.quotation_date DESC, Quotation.quotation_code DESC',
			'limit'=>($pendingQuotationCount!=0?$pendingQuotationCount:1),
		];
		$pendingQuotations = $this->Paginator->paginate('Quotation');
		
		if (!empty($pendingQuotations)){
			
			for ($i=0;$i<count($pendingQuotations);$i++){
				//pr($pendingQuotations[$i]['Quotation']);
				// set the exchange rate
				$quotationDate=$pendingQuotations[$i]['Quotation']['quotation_date'];
				$pendingQuotations[$i]['Quotation']['exchange_rate']=$this->ExchangeRate->getApplicableExchangeRateValue($quotationDate);
				if(array_key_exists($pendingQuotations[$i]['VendorUser']['id'],$userPeriod)){
          if ($pendingQuotations[$i]['Currency']['id']==CURRENCY_CS){
            $userPendingCS[$pendingQuotations[$i]['VendorUser']['id']]+=$pendingQuotations[$i]['Quotation']['price_subtotal'];
            $userPendingUSD[$pendingQuotations[$i]['VendorUser']['id']]+=round($pendingQuotations[$i]['Quotation']['price_subtotal']/$pendingQuotations[$i]['Quotation']['exchange_rate'],2);
          }
          elseif ($pendingQuotations[$i]['Currency']['id']==CURRENCY_USD){
            $userPendingUSD[$pendingQuotations[$i]['VendorUser']['id']]+=$pendingQuotations[$i]['Quotation']['price_subtotal'];
            $userPendingCS[$pendingQuotations[$i]['VendorUser']['id']]+=round($pendingQuotations[$i]['Quotation']['price_subtotal']*$pendingQuotations[$i]['Quotation']['exchange_rate'],2);
          }
        }
			}
		}
		//pr($pendingQuotations);
		$this->set(compact('pendingQuotations'));
    $this->set(compact('userPendingCS','userPendingUSD'));
		
		if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      $users=$this->User->getActiveVendorAdminUserList($warehouseId);
    }
    elseif ($canSeeAllVendors) {
      $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    }
    else {
      $users=$this->User->getActiveUserList($loggedUserId);
    }
		$this->set(compact('users'));
		
		$currencies=$this->Currency->find('list');
		$this->set(compact('currencies'));
		
		$clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $clientTypeHexColors=$this->ClientType->getClientTypeHexColors();
    $this->set(compact('clientTypeHexColors'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
    $priceClientCategories=$this->PriceClientCategory->getPriceClientCategoryList();
    $this->set(compact('priceClientCategories'));
    
    $productCategoriesPerProduct=$this->Product->getProductCategoriesPerProduct();
    $this->set(compact('productCategoriesPerProduct'));
    
    $productTypesPerProduct=$this->Product->getProductTypesPerProduct();
    $this->set(compact('productTypesPerProduct'));
    
    $aco_name="SalesOrders/crearOrdenVentaExterna";		
    $bool_crearOrdenVentaExterna_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_crearOrdenVentaExterna_permission'));
		
    $aco_name="Quotations/crear";
    //echo "userid is ".($this->Auth->User('id'))."<br/>";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    //echo 'booladdpermission is '.$bool_add_permission.'<br/>';
		$aco_name="Quotations/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Quotations/detalle";		
		$bool_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_view_permission'));
		
		$aco_name="ThirdParties/index";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/add";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
		$aco_name="SalesOrders/resumen";		
		$bool_salesorder_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_salesorder_index_permission'));
		$aco_name="SalesOrders/crear";		
		$bool_salesorder_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_salesorder_add_permission'));
		$aco_name="Invoices/index";		
		$bool_invoice_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_invoice_index_permission'));
		$aco_name="Invoices/add";		
		$bool_invoice_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_invoice_add_permission'));
	}

	public function guardarResumen() {
		$exportData=$_SESSION['resumenQuotations'];
		$this->set(compact('exportData'));
	}

	public function detalle($id = null) {
		if (!$this->Quotation->exists($id)) {
			throw new NotFoundException(__('Invalid quotation'));
		}
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
		$options = [
			'conditions' => [
				'Quotation.id' => $id,
			],
			'contain'=>[
				'Client'=>[
          'ClientType',
          'Zone',
        ],
        'ClientType'=>[
          'fields'=>['ClientType.id','ClientType.name','ClientType.hex_color',]
        ],
        //'Contact',
				'Currency',
				//'QuotationImage',
				'QuotationProduct'=>[
					'Product',
          'RawMaterial',
					'Currency',
				],
				'QuotationRemark'=>[
					'User',
				],
				'SalesOrder'=>[
					//'InvoiceSalesOrder'=>[
					//	'Invoice'=>[
					//		//'Product',
					//	],
					//],
				],
				'RecordUser',
        'VendorUser',
        'CreditAuthorizationUser',
        'Warehouse',
        'Zone',
			],
		];
		$quotation=$this->Quotation->find('first', $options);
		
		$quotationDate=$quotation['Quotation']['quotation_date'];
		$quotation['Quotation']['exchange_rate']=$this->ExchangeRate->getApplicableExchangeRateValue($quotationDate);
		
		if (!empty($quotation['SalesOrder']['id'])){
			$quotation['Quotation']['bool_sales_order']=true;
		}
		else {
			$quotation['Quotation']['bool_sales_order']='0';
		}
		$this->set(compact('quotation'));
    //pr($quotation);
		
		//$bool_edit_forbidden_because_salesorder_authorized='0';
		if (!empty($quotation['SalesOrder'])){
			//pr($quotation['SalesOrder']);
			//if ($quotation['SalesOrder'][0]['bool_authorized']){
				$bool_edit_permission='0';
			//	$bool_edit_forbidden_because_salesorder_authorized=true;
			//}
		}
		$this->set(compact('bool_edit_permission'));
		//$this->set(compact('bool_edit_forbidden_because_salesorder_authorized'));
		
		$fileName='Cotización_'.$quotation['Quotation']['quotation_code']."_".((empty($quotation['Client']['id']) || $quotation['Client']['bool_generic'])?$quotation['Quotation']['client_name']:$quotation['Client']['company_name']);
		$this->set(compact('fileName'));
    
    $aco_name="SalesOrders/crearOrdenVentaExterna";		
    $bool_crearOrdenVentaExterna_permission=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('bool_crearOrdenVentaExterna_permission'));
		
		$aco_name="Quotations/crear";		
    //echo "userid is ".($this->Auth->User('id'))."<br/>";
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Quotations/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Quotations/detalle";		
		$bool_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_view_permission'));
		
		$aco_name="ThirdParties/index";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/add";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
		$aco_name="SalesOrders/resumen";		
		$bool_salesorder_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_salesorder_index_permission'));
		$aco_name="SalesOrders/crear";		
		$bool_salesorder_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_salesorder_add_permission'));
		$aco_name="Invoices/index";		
		$bool_invoice_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_invoice_index_permission'));
		$aco_name="Invoices/add";		
		$bool_invoice_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_invoice_add_permission'));
	}

	public function detallePdf($id = null) {
		if (!$this->Quotation->exists($id)) {
			throw new NotFoundException(__('Invalid quotation'));
		}

		$options = [
			'conditions' => [
				'Quotation.id' => $id,
			],
			'contain'=>[
				'Client',
				//'Contact',
				'Currency',
				//'QuotationImage',
				'QuotationProduct'=>[
					'Product',
          'RawMaterial',
					'Currency',
				],
				'QuotationRemark'=>[
					'User',
				],
				'SalesOrder'=>[
					//'InvoiceSalesOrder'=>[
					//	'Invoice'=>[
					//		//'Product',
					//	],
					//],
				],
				'RecordUser',
        'VendorUser',
        'CreditAuthorizationUser',
        'Warehouse',
			],
		];
		$quotation=$this->Quotation->find('first', $options);
		$this->set(compact('quotation'));
		//pr($quotation);
    $quotationDate=$quotation['Quotation']['quotation_date'];
		$quotation['Quotation']['exchange_rate']=$this->ExchangeRate->getApplicableExchangeRateValue($quotationDate);
		
		if (!empty($quotation['SalesOrder'])){
			$quotation['Quotation']['bool_sales_order']=true;
		}
		else {
			$quotation['Quotation']['bool_sales_order']='0';
		}
		$this->set(compact('quotation'));
    
		$dueDate= new DateTime($quotation['Quotation']['due_date']);
		$quotationDate= new DateTime($quotation['Quotation']['quotation_date']);
		$daysValid=$quotationDate->diff($dueDate);
		$validityQuotation=(int)$daysValid->format("%r%a");
		$this->set(compact('validityQuotation'));
		
		//$fileName=$quotation['Client']['company_name'].'_'.$quotation['Quotation']['quotation_code'];
		//$this->set(compact('fileName'));
	}
	
	public function crear() {
		$this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductCategory');
    
    $this->loadModel('ClosingDate');
    
		$this->loadModel('QuotationProduct');
		$this->loadModel('QuotationRemark');
		
		$this->loadModel('ThirdParty');
		$this->loadModel('ActionType');
		
    $this->loadModel('ClientType');
		$this->loadModel('Zone');
		
    $this->loadModel('PriceClientCategory');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('UserPageRight');
    
		$loggedUserId=$userId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $canSeeInventoryCost=$this->UserPageRight->hasUserPageRight('VER_COSTO_INVENTARIO',$userRoleId,$loggedUserId,'All','All');
    $this->set(compact('canSeeInventoryCost'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'Quotations','resumen');
    $this->set(compact('canSeeAllUsers'));
    
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'Quotations','resumen');
    $this->set(compact('canSeeAllVendors'));
    
    $warehouseId=0;
    $quotationDate=date( "Y-m-d");
    $clientId=0;
    $currencyId=CURRENCY_CS;
    
    $boolInitialLoad=true;
    $creditAuthorizationUserId=0;
    
    $genericClientIds=$this->ThirdParty->getGenericClientIds();
    $this->set(compact('genericClientIds'));
    
		$requestProducts=[];
    if ($this->request->is('post')) {
      $boolInitialLoad='0';
      foreach ($this->request->data['QuotationProduct'] as $quotationProduct){
				if ($quotationProduct['product_id'] > 0 && $quotationProduct['product_quantity'] > 0 && $quotationProduct['product_unit_price'] > 0){
					$requestProducts[]['QuotationProduct']=$quotationProduct;
				}
			}
      
			$quotationDateArray=$this->request->data['Quotation']['quotation_date'];
			//pr($quotationDateArray);
			$quotationDateString=$quotationDateArray['year'].'-'.$quotationDateArray['month'].'-'.$quotationDateArray['day'];
			$quotationDate=date( "Y-m-d", strtotime($quotationDateString));
      
      $clientId=$this->request->data['Quotation']['client_id'];
      $currencyId=$this->request->data['Quotation']['currency_id'];
      $vendorUserId=$this->request->data['Quotation']['vendor_user_id'];
      $recordUserId=$this->request->data['Quotation']['record_user_id'];
      if (array_key_exists('credit_authorization_user_id',$this->request->data['Quotation'])){
        $creditAuthorizationUserId=$this->request->data['Quotation']['credit_authorization_user_id'];
      }
      $warehouseId=$this->request->data['Quotation']['warehouse_id'];
    }  
    
    $quotationDatePlusOne=date("Y-m-d",strtotime($quotationDate."+1 days"));  
    
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
     
    if ($this->request->is('post') && empty($this->request->data['updateWarehouse']) && empty($this->request->data['changeProductType'])) {
      $clientId=$this->request->data['Quotation']['client_id'];
      $clientName=$this->request->data['Quotation']['client_name'];
      $clientPhone=$this->request->data['Quotation']['client_phone'];
      $clientMail=$this->request->data['Quotation']['client_email'];
      
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
      $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
      $closingDateTime=new DateTime($latestClosingDate);
      
			$boolMultiplicationOK=true;
      $sumProductTotals=0;
      $boolProductPricesRegistered=true;
      $productPriceWarning='';
      $boolProductPriceLessThanDefaultPrice='0';
      $productPriceLessThanDefaultPriceError='';
      $boolProductPriceRepresentsBenefit=true;
      $productPriceBenefitError='';
      if (!empty($this->request->data['QuotationProduct'])){
        foreach ($this->request->data['QuotationProduct'] as $quotationProduct){
          if ($quotationProduct['product_id']>0){
            $acceptableProductPrice=1000;
            $acceptableProductPrice=$this->Product->getAcceptablePriceForProductClientCostQuantityDate($quotationProduct['product_id'],$clientId,$quotationProduct['product_unit_cost'],$quotationProduct['product_quantity'],$quotationDate,$quotationProduct['raw_material_id']);
            
            $productName=$this->Product->getProductName($quotationProduct['product_id']);
            $rawMaterialName=($quotationProduct['raw_material_id'] > 0?($this->Product->getProductName($quotationProduct['raw_material_id'])):'');
            if (!empty($rawMaterialName)){
              $productName.=(' '.$rawMaterialName.' A');
            }
            
            $multiplicationDifference=abs($quotationProduct['product_total_price']-$quotationProduct['product_quantity']*$quotationProduct['product_unit_price']);
            if ($multiplicationDifference>=0.01){
              $boolMultiplicationOK='0';
            };
            if ($quotationProduct['product_id'] != PRODUCT_SERVICE_OTHER){
              if ($quotationProduct['default_product_unit_price'] <=0) {
                $boolProductPricesRegistered='0'; 
                $productPriceWarning='Producto '.$productName.' no tiene registrado un precio de listado entonces no se podía aplicar un control de precios.  Por favor graba un precio para este producto primero.  ';  
              }
              // 20211004 default_product_price could be tricked into accepting volume price by users with bad intentions by increasing and then decreasing prices, that's why the price is calculated afreshe in $acceptableProductPrice
              //if ($quotationProduct['product_unit_price'] < $quotationProduct['default_product_unit_price']) {
              if ($quotationProduct['product_unit_price'] < $acceptableProductPrice) {  
                $boolProductPriceLessThanDefaultPrice=true; 
                //$productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$quotationProduct['product_unit_price'].' pero el precio mínimo establecido es '.$quotationProduct['default_product_unit_price'].'.  No se permite vender abajo del precio mínimo establecido.  ';  
                $productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$quotationProduct['product_unit_price'].' pero el precio mínimo establecido es '.$acceptableProductPrice.'.  No se permite vender abajo del precio mínimo establecido.  ';  
              }
              if ($quotationProduct['product_unit_price'] < $quotationProduct['product_unit_cost']) {
                $boolProductPriceRepresentsBenefit='0'; 
                if ($userRoleId === ROLE_ADMIN){
                  $productPriceBenefitError='Producto '.$productName.' tiene un precio '.$quotationProduct['product_unit_price'].' pero el costo es '.$quotationProduct['product_unit_cost'].'.  No se permite vender con pérdidas.  ';  
                }
                else {
                  $productPriceBenefitError='Precio no autorizado para producto '.$productName.'.  No se guardó la cotización.  ';  
                }
              }
            }
          }
          $sumProductTotals+=$quotationProduct['product_total_price'];
        }
      }
      
      if (!array_key_exists('bool_credit',$this->request->data['Quotation'])){
        $this->request->data['Quotation']['bool_credit']=$this->request->data['bool_credit'];
      }
      if (!array_key_exists('save_allowed',$this->request->data['Quotation'])){
        $this->request->data['Quotation']['save_allowed']=$this->request->data['save_allowed'];
      }
      if (!array_key_exists('credit_authorization_user_id',$this->request->data['Quotation'])){
        $this->request->data['Quotation']['credit_authorization_user_id']=$this->request->data['credit_authorization_user_id'];
      }
      $creditAuthorizationUserId=$this->request->data['Quotation']['credit_authorization_user_id'];
      if (!array_key_exists('retention_allowed',$this->request->data['Quotation'])){
        if (array_key_exists('retention_allowed',$this->request->data)){
          $this->request->data['Quotation']['retention_allowed']=$this->request->data['retention_allowed'];
        }
        else{
          $this->request->data['Quotation']['retention_allowed']=1;
        }
      }
      
      if ($quotationDateString > date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de cotización no puede estar en el futuro!  No se guardó la cotización.'), 'default',['class' => 'error-message']);
			}
			elseif ($this->request->data['Quotation']['save_allowed'] == 0){
        $this->Session->setFlash('No se permite guardar esta cotización de crédito!  Si está el gerente, marca la casilla de permitir guardar venta.  No se guardó la cotización.', 'default',['class' => 'error-message']);
      }
      elseif ($quotationDateString < $latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se guardó la cotización.'), 'default',['class' => 'error-message']);
      }
			elseif (empty($clientId) && empty($clientName)){
        $this->Session->setFlash('Se debe registrar el nombre del cliente.  No se guardó la cotización.', 'default',['class' => 'error-message']);
      }
      elseif (empty($clientId) && empty($clientPhone) && empty($clientMail)){
        $this->Session->setFlash('Se debe registrar el teléfono o el correo electrónico del cliente.  No se guardó la cotización.', 'default',['class' => 'error-message']);
      }
      elseif (!$this->request->data['Quotation']['client_generic'] && empty($this->request->data['Quotation']['client_type_id'])){
        $this->Session->setFlash('Se debe registrar el tipo de cliente.  No se guardó la cotización.', 'default',['class' => 'error-message']);
      }
      elseif (!$this->request->data['Quotation']['client_generic'] && empty($this->request->data['Quotation']['zone_id'])){
        $this->Session->setFlash('Se debe registrar la zona del cliente.  No se guardó la cotización.', 'default',['class' => 'error-message']);
      }
      elseif (!$boolMultiplicationOK){
				$this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
			}
      elseif (abs($sumProductTotals-$this->request->data['Quotation']['price_subtotal']) > 0.01){
        $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$sumProductTotals.' pero el total calculado es '.$this->request->data['Quotation']['price_subtotal'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      elseif (abs($this->request->data['Quotation']['price_total']-$this->request->data['Quotation']['price_iva']-$this->request->data['Quotation']['price_subtotal'])>0.01){
        $this->Session->setFlash('La suma del subtotal '.$this->request->data['Quotation']['price_subtotal'].' y el IVA '.$this->request->data['Quotation']['price_iva'].' no igualan el precio total '.$this->request->data['Quotation']['price_total'].', la diferencia es de '.(abs($this->request->data['Quotation']['price_total']-$this->request->data['Quotation']['price_iva']-$this->request->data['Quotation']['price_subtotal'])).'.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Quotation']['price_total'])){
        $this->Session->setFlash(__('El total de la cotización tiene que ser mayor que cero.  No se guardó la cotización.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['Quotation']['bool_retention'] && strlen($this->request->data['Quotation']['retention_number'])==0){
        $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la cotización.'), 'default',['class' => 'error-message']);
      }
      else if ($this->request->data['Quotation']['bool_retention'] && abs(0.02*$this->request->data['Quotation']['price_subtotal']-$this->request->data['Quotation']['retention_amount']) > 0.01){
        $this->Session->setFlash(__('La retención debería igualar el 2% del subtotal de la cotización!  No se guardó la cotización.'), 'default',['class' => 'error-message']);
      }  
      elseif (!$boolProductPricesRegistered && $userRoleId != ROLE_ADMIN){
        $this->Session->setFlash($productPriceWarning.'No se guardó la cotización.', 'default',['class' => 'error-message']);
      }
      elseif ($boolProductPriceLessThanDefaultPrice && $userRoleId != ROLE_ADMIN){
        $this->Session->setFlash($productPriceLessThanDefaultPriceError.'No se guardó la cotización.', 'default',['class' => 'error-message']);
      }
      elseif (!$boolProductPriceRepresentsBenefit){
        $this->Session->setFlash($productPriceBenefitError.'No se guardó la cotización.', 'default',['class' => 'error-message']);
      }
      else {				
				$datasource=$this->Quotation->getDataSource();
				$datasource->begin();
				try {
					$this->Quotation->create();
          //pr($this->request->data);
					if (!$this->Quotation->save($this->request->data)) {
						echo "Problema guardando la cotización";
						//pr($this->validateErrors($this->Quotation));
						throw new Exception();
					}
					$quotationId=$this->Quotation->id;
					
					foreach ($this->request->data['QuotationProduct'] as $quotationProduct){
						if ($quotationProduct['product_id']>0 && $quotationProduct['product_quantity']>0){
							//pr($quotationProduct);
							$productArray=[];
							$productArray['QuotationProduct']['quotation_id']=$quotationId;
							$productArray['QuotationProduct']['product_id']=$quotationProduct['product_id'];
              $productArray['QuotationProduct']['raw_material_id']=$quotationProduct['raw_material_id'];
							$productArray['QuotationProduct']['product_quantity']=$quotationProduct['product_quantity'];
							$productArray['QuotationProduct']['product_unit_price']=$quotationProduct['product_unit_price'];
							$productArray['QuotationProduct']['product_total_price']=$quotationProduct['product_total_price'];
							//$productArray['QuotationProduct']['bool_iva']=$quotationProduct['bool_iva'];
              $productArray['QuotationProduct']['bool_iva']=true;
							$productArray['QuotationProduct']['currency_id']=$this->request->data['Quotation']['currency_id'];
							
              $this->QuotationProduct->create();
							if (!$this->QuotationProduct->save($productArray)) {
								echo "Problema guardando los productos de la cotización";
								pr($this->validateErrors($this->QuotationProduct));
								throw new Exception();
							}
						}
					}
					
					if (!empty($this->request->data['QuotationRemark']['remark_text'])){
						$quotationRemark=$this->request->data['QuotationRemark'];
						//pr($quotationRemark);
						$quotationRemarkArray=[];
						$quotationRemarkArray['QuotationRemark']['user_id']=$quotationRemark['user_id'];
						$quotationRemarkArray['QuotationRemark']['quotation_id']=$quotationId;
						$quotationRemarkArray['QuotationRemark']['remark_datetime']=date('Y-m-d H:i:s');
						$quotationRemarkArray['QuotationRemark']['remark_text']=$quotationRemark['remark_text'];
						$quotationRemarkArray['QuotationRemark']['working_days_before_reminder']=$quotationRemark['working_days_before_reminder'];
						$quotationRemarkArray['QuotationRemark']['reminder_date']=$quotationRemark['reminder_date'];
						$quotationRemarkArray['QuotationRemark']['action_type_id']=$quotationRemark['action_type_id'];
						$this->QuotationRemark->create();
						if (!$this->QuotationRemark->save($quotationRemarkArray)) {
							echo "Problema guardando las remarcas para la cotización";
							pr($this->validateErrors($this->QuotationRemark));
							throw new Exception();
						}
					}
          
          //if (!$this->request->data['Quotation']['client_generic']){
          if (!in_array($this->request->data['Quotation']['client_id'],$genericClientIds)){  
            $quotationClientData=[
              'id'=>$this->request->data['Quotation']['client_id'],
              'phone'=>$this->request->data['Quotation']['client_phone'],
              'email'=>$this->request->data['Quotation']['client_email'],
              'address'=>$this->request->data['Quotation']['client_address'],
              'ruc_number'=>'',
              'client_type_id'=>$this->request->data['Quotation']['client_type_id'],
              'zone_id'=>$this->request->data['Quotation']['zone_id'],
              
            ];
            if (!$this->ThirdParty->updateClientDataConditionally($quotationClientData,$userRoleId)['success']){
              echo "Problema actualizando los datos del cliente";
              throw new Exception();
            }
          }
					
					$datasource->commit();
					$this->recordUserAction($this->Quotation->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se registró la cotización número ".$this->request->data['Quotation']['quotation_code']);
					
					$this->Session->setFlash(__('The quotation has been saved.'),'default',['class' => 'success']);				
					return $this->redirect(['action' => 'detalle',$quotationId]);
				}
				catch(Exception $e){
					$datasource->rollback();
					// pr($e);
					$this->Session->setFlash(__('No se podía guardar la cotización.'), 'default',['class' => 'error-message']);
				}
			}
		}
		$this->set(compact('requestProducts'));
    $this->set(compact('boolInitialLoad'));
		
    $this->set(compact('clientId'));
    $this->set(compact('currencyId'));
    
    $this->set(compact('vendorUserId'));
    $this->set(compact('recordUserId'));
    $this->set(compact('creditAuthorizationUserId'));
    
    /*
    $clientConditions=[];
    if ($userRoleId != ROLE_ADMIN && $userRoleId != ROLE_ASSISTANT){
      $associatedClientIds=$this->ThirdPartyUser->getAssociatedClientsForUser($this->Auth->User('id'));
      //pr($associatedClientIds);
      $clientConditions['ThirdParty.id']=$associatedClientIds;
    }
    $clients = $this->Quotation->Client->find('list',[
      'conditions'=>$clientConditions,
      'order'=>'ThirdParty.company_name'
    ]);
		//$contacts = $this->Quotation->Contact->find('list',['order'=>'Contact.first_name']);
    $clientConditions['ThirdParty.bool_active']='0';
    $inactiveClients=$this->Quotation->ThirdParty->find('list',[
			'fields'=>['ThirdParty.id'],
			'conditions'=>$clientConditions,
		]);
    $inactiveClients=array_keys($inactiveClients);
    $this->set(compact('inactiveClients'));
    */
    $clients = $this->ThirdParty->getActiveClientList(20);
    $this->set(compact('clients'));
    
		$currencies = $this->Quotation->Currency->find('list');
		$this->set(compact('currencies'));
		
		//$productCategories = $this->ProductCategory->find('list',['order'=>'ProductCategory.name']);
		//$this->set(compact('productCategories'));
		
		$quotationDate=date( "Y-m-d");
		$exchangeRateQuotation=$this->ExchangeRate->getApplicableExchangeRateValue($quotationDate);
		$this->set(compact('exchangeRateQuotation'));
		
		$actionTypes=$this->ActionType->find('list',['order'=>'ActionType.list_order ASC']);
		$this->set(compact('actionTypes'));
    
    $availableProductsForSale=$this->Product->getAvailableProductsForSale($quotationDate,$warehouseId,false);
    $products=$availableProductsForSale['products'];
    //pr($products);
    if ($warehouseId != WAREHOUSE_INJECTION){
      $availableInjectionProductsForSale=$this->Product->getAvailableProductsForSale($quotationDate,WAREHOUSE_INJECTION,false);
      $injectionProducts=$availableInjectionProductsForSale['products'];
      //pr($injectionProducts);
      $products+=$injectionProducts;
    }
    
    $rawMaterialsAvailablePerFinishedProduct=$availableProductsForSale['rawMaterialsAvailablePerFinishedProduct'];
    $rawMaterials=$availableProductsForSale['rawMaterials'];
    $this->set(compact('products'));
    $this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    $this->set(compact('rawMaterials'));
    
    if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      $users=$this->User->getActiveVendorAdminUserList($warehouseId);
    }
    elseif ($canSeeAllVendors) {
      $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    }
    else {
      $users=$this->User->getActiveUserList($loggedUserId);
    }
		$this->set(compact('users'));
    
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
    $priceClientCategories=$this->PriceClientCategory->getPriceClientCategoryList();
    $this->set(compact('priceClientCategories'));
    
    $productCategoriesPerProduct=$this->Product->getProductCategoriesPerProduct();
    $this->set(compact('productCategoriesPerProduct'));
    
    $productTypesPerProduct=$this->Product->getProductTypesPerProduct();
    $this->set(compact('productTypesPerProduct'));
    
    $aco_name="Quotations/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Quotations/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Quotations/detalle";		
		$bool_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_view_permission'));
		
		$aco_name="ThirdParties/index";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/add";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function editar($id = null) {
    if (!$this->Quotation->exists($id)) {
			throw new NotFoundException(__('Invalid quotation'));
		}
    
    $this->loadModel('Product');
		$this->loadModel('ProductType');
    //$this->loadModel('ProductCategory');
    
    $this->loadModel('StockItem');
    
    $this->loadModel('ClosingDate');
    
    $this->loadModel('QuotationProduct');
		$this->loadModel('QuotationRemark');
    
    $this->loadModel('ThirdParty');
		$this->loadModel('ActionType');
		
    $this->loadModel('ClientType');
		$this->loadModel('Zone');
    
    $this->loadModel('PriceClientCategory');
		
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
		
    $this->loadModel('UserPageRight');
    
		$loggedUserId=$userId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $canSeeInventoryCost=$this->UserPageRight->hasUserPageRight('VER_COSTO_INVENTARIO',$userRoleId,$loggedUserId,'All','All');
    $this->set(compact('canSeeInventoryCost'));
    
    $canChangeDueDate=$this->UserPageRight->hasUserPageRight('PUEDE_CAMBIAR_FECHA_LIMITE',$userRoleId,$loggedUserId,'Quotations','editar');
    $this->set(compact('canChangeDueDate'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'Quotations','resumen');
    $this->set(compact('canSeeAllUsers'));
    
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'Quotations','resumen');
    $this->set(compact('canSeeAllVendors'));
    
    $this->loadModel('RejectedReason');
    $this->loadModel('ActionType');
    
    $rejectedOptions=[];
		$rejectedOptions[0]="Activa";
		$rejectedOptions[1]="Caída";
		$this->set(compact('rejectedOptions'));
    
		$boolInitialLoad=true;
    $creditAuthorizationUserId=0;
    
    $genericClientIds=$this->ThirdParty->getGenericClientIds();
    $this->set(compact('genericClientIds'));
    
		$requestProducts=[];
		if ($this->request->is(['post', 'put'])) {
      $boolInitialLoad='0';
      foreach ($this->request->data['QuotationProduct'] as $quotationProduct){
				if ($quotationProduct['product_id'] > 0 && $quotationProduct['product_quantity'] > 0 && $quotationProduct['product_unit_price'] > 0){
					$requestProducts[]['QuotationProduct']=$quotationProduct;
				}
			}
      
			$quotationDateArray=$this->request->data['Quotation']['quotation_date'];
			//pr($quotationDateArray);
			$quotationDateString=$quotationDateArray['year'].'-'.$quotationDateArray['month'].'-'.$quotationDateArray['day'];
			$quotationDate=date( "Y-m-d", strtotime($quotationDateString));
			
      $clientId=$this->request->data['Quotation']['client_id'];
      $currencyId=$this->request->data['Quotation']['currency_id'];
      $vendorUserId=$this->request->data['Quotation']['vendor_user_id'];
      $recordUserId=$this->request->data['Quotation']['record_user_id'];
      if (array_key_exists('credit_authorization_user_id',$this->request->data['Quotation'])){
        $creditAuthorizationUserId=$this->request->data['Quotation']['credit_authorization_user_id'];
      }
      $warehouseId=$this->request->data['Quotation']['warehouse_id'];
     
     if(empty($this->request->data['updateWarehouse']) ){     
        $clientId=$this->request->data['Quotation']['client_id'];
        $clientName=$this->request->data['Quotation']['client_name'];
        $clientPhone=$this->request->data['Quotation']['client_phone'];
        $clientEmail=$this->request->data['Quotation']['client_email'];
        
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDateTime=new DateTime($latestClosingDate);
        
        $boolRejectedOK=true;
        if ($this->request->data['Quotation']['bool_rejected']){
          if (!$this->request->data['Quotation']['rejected_reason_id']){
            $boolRejectedOK='0';
          }
        }
        
        $boolMultiplicationOK=true;
        $sumProductTotals=0;
        $boolProductPricesRegistered=true;
        $productPriceWarning='';
        $boolProductPriceLessThanDefaultPrice='0';
        $productPriceLessThanDefaultPriceError='';
        $boolProductPriceRepresentsBenefit=true;
        $productPriceBenefitError='';
        if (!empty($this->request->data['QuotationProduct'])){
          foreach ($this->request->data['QuotationProduct'] as $quotationProduct){
            if ($quotationProduct['product_id']>0){
              $acceptableProductPrice=1000;
              $acceptableProductPrice=$this->Product->getAcceptablePriceForProductClientCostQuantityDate($quotationProduct['product_id'],$clientId,$quotationProduct['product_unit_cost'],$quotationProduct['product_quantity'],$quotationDate,$quotationProduct['raw_material_id']);
              
              
              $multiplicationDifference=abs($quotationProduct['product_total_price']-$quotationProduct['product_quantity']*$quotationProduct['product_unit_price']);
              if ($multiplicationDifference>=0.01){
                $boolMultiplicationOK='0';
              };
              
              if ($quotationProduct['default_product_unit_price'] <=0) {
                $boolProductPricesRegistered='0'; 
                $productName=$this->Product->getProductName($quotationProduct['product_id']);
                $rawMaterialName=($quotationProduct['raw_material_id'] > 0?($this->Product->getProductName($quotationProduct['raw_material_id'])):'');
                if (!empty($rawMaterialName)){
                  $productName.=(' '.$rawMaterialName.' A');
                }
                $productPriceWarning='Producto '.$productName.' no tiene registrado un precio de listado entonces no se podía aplicar un control de precios.  Por favor graba un precio para este producto primero.  ';  
              }
              // 20211004 default_product_price could be tricked into accepting volume price by users with bad intentions by increasing and then decreasing prices, that's why the price is calculated afreshe in $acceptableProductPrice
              //if ($quotationProduct['product_unit_price'] < $quotationProduct['default_product_unit_price']) {
              if ($quotationProduct['product_unit_price'] < $acceptableProductPrice) { 
                $boolProductPriceLessThanDefaultPrice=true; 
                $productName=$this->Product->getProductName($quotationProduct['product_id']);
                $rawMaterialName=($quotationProduct['raw_material_id'] > 0 ?($this->Product->getProductName($quotationProduct['raw_material_id'])):'');
                if (!empty($rawMaterialName)){
                  $productName.=(' '.$rawMaterialName.' A');
                }
                //$productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$quotationProduct['product_unit_price'].' pero el precio mínimo establecido es '.$quotationProduct['default_product_unit_price'].'.  No se permite vender abajo del precio mínimo establecido.  ';  
                $productPriceLessThanDefaultPriceError='Producto '.$productName.' tiene un precio '.$quotationProduct['product_unit_price'].' pero el precio mínimo establecido es '.$acceptableProductPrice.'.  No se permite vender abajo del precio mínimo establecido.  ';  
              }
              if ($quotationProduct['product_unit_price'] < $quotationProduct['product_unit_cost']) {
                $boolProductPriceRepresentsBenefit='0'; 
                $productName=$this->Product->getProductName($quotationProduct['product_id']);
                $rawMaterialName=($quotationProduct['raw_material_id'] > 0 ?($this->Product->getProductName($quotationProduct['raw_material_id'])):'');
                //echo 'rawMaterialId is '.$quotationProduct['raw_material_id'].'<br>';
                if (!empty($rawMaterialName)){
                  $productName.=(' '.$rawMaterialName.' A');
                }
                if ($userRoleId === ROLE_ADMIN){
                  $productPriceBenefitError='Producto '.$productName.' tiene un precio '.$quotationProduct['product_unit_price'].' pero el costo es '.$quotationProduct['product_unit_cost'].'.  No se permite vender con pérdidas.  ';  
                }
                else {
                  $productPriceBenefitError='Precio no autorizado para producto '.$productName.'.  No se editó la cotización.  ';  
                }  
              }
            }
            $sumProductTotals+=$quotationProduct['product_total_price'];
          }
        }
        
        if (!array_key_exists('bool_credit',$this->request->data['Quotation'])){
          $this->request->data['Quotation']['bool_credit']=$this->request->data['bool_credit'];
        }
        if (!array_key_exists('save_allowed',$this->request->data['Quotation'])){
          $this->request->data['Quotation']['save_allowed']=$this->request->data['save_allowed'];
        }
        if (!array_key_exists('credit_authorization_user_id',$this->request->data['Quotation'])){
          $this->request->data['Quotation']['credit_authorization_user_id']=$this->request->data['credit_authorization_user_id'];
        }
        $creditAuthorizationUserId=$this->request->data['Quotation']['credit_authorization_user_id'];
        if (!array_key_exists('retention_allowed',$this->request->data['Quotation'])){
          if (array_key_exists('retention_allowed',$this->request->data)){
            $this->request->data['Quotation']['retention_allowed']=$this->request->data['retention_allowed'];
          }
          else{
            $this->request->data['Quotation']['retention_allowed']=1;
          }
        }
        
        if ($quotationDateString > date('Y-m-d 23:59:59')){
          $this->Session->setFlash(__('La fecha de cotización no puede estar en el futuro!  No se guardó la cotización.'), 'default',['class' => 'error-message']);
        }
        elseif ($this->request->data['Quotation']['save_allowed'] == 0){
          $this->Session->setFlash('No se permite guardar esta cotización de crédito!  Si está el gerente, marca la casilla de permitir guardar venta.  No se guardó la cotización.', 'default',['class' => 'error-message']);
        }
        elseif ($quotationDateString < $latestClosingDatePlusOne){
          $this->Session->setFlash(__('La última fecha de cierre es '.$closingDateTime->format('d-m-Y').'!  No se guardó la cotización.'), 'default',['class' => 'error-message']);
        }
        elseif (empty($clientId) && empty($clientName)){
          $this->Session->setFlash('Se debe registrar el nombre del cliente.  No se guardó la orden.', 'default',['class' => 'error-message']);
        }
        elseif (empty($clientId) && empty($clientPhone) && empty($clientEmail)){
          $this->Session->setFlash('Se debe registrar el teléfono o el correo electrónico del cliente.  No se guardó la orden.', 'default',['class' => 'error-message']);
        }
        elseif (!$this->request->data['Quotation']['client_generic'] && empty($this->request->data['Quotation']['client_type_id'])){
          $this->Session->setFlash('Se debe registrar el tipo de cliente.  No se guardó la cotización.', 'default',['class' => 'error-message']);
        }
        elseif (!$this->request->data['Quotation']['client_generic'] && empty($this->request->data['Quotation']['zone_id'])){
          $this->Session->setFlash('Se debe registrar la zona del cliente.  No se guardó la cotización.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolMultiplicationOK){
          $this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
        }
        elseif (abs($sumProductTotals-$this->request->data['Quotation']['price_subtotal']) > 0.01){
          $this->Session->setFlash('Si se suman los totales de cada producto se llega a '.$sumProductTotals.' pero el total calculado es '.$this->request->data['Quotation']['price_subtotal'].'.  Verifique que ha indicado cada producto para que dió un precio y vuelve a intentar.  Si esto no tiene efecto, comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (abs($this->request->data['Quotation']['price_total']-$this->request->data['Quotation']['price_iva']-$this->request->data['Quotation']['price_subtotal'])>0.01){
          $this->Session->setFlash('La suma del subtotal '.$this->request->data['Quotation']['price_subtotal'].' y el IVA '.$this->request->data['Quotation']['price_iva'].' no igualan el precio total '.$this->request->data['Quotation']['price_total'].', la diferencia es de '.(abs($this->request->data['Quotation']['price_total']-$this->request->data['Quotation']['price_iva']-$this->request->data['Quotation']['price_subtotal'])).'.  Vuelva a intentar o comuníquese con su administrador.', 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['Quotation']['price_total'])){
          $this->Session->setFlash(__('El total de la cotización tiene que ser mayor que cero.  No se guardó la cotización.'), 'default',['class' => 'error-message']);
        }
        else if ($this->request->data['Quotation']['bool_retention'] && strlen($this->request->data['Quotation']['retention_number'])==0){
          $this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la cotización.'), 'default',['class' => 'error-message']);
        }
        else if ($this->request->data['Quotation']['bool_retention'] && abs(0.02*$this->request->data['Quotation']['price_subtotal']-$this->request->data['Quotation']['retention_amount']) > 0.01){
          $this->Session->setFlash(__('La retención debería igualar el 2% del subtotal de la cotización!  No se guardó la cotización.'), 'default',['class' => 'error-message']);
        }  
        elseif (!$boolProductPricesRegistered && $userRoleId != ROLE_ADMIN){
          $this->Session->setFlash($productPriceWarning.'No se guardó la cotización.', 'default',['class' => 'error-message']);
        }
        elseif ($boolProductPriceLessThanDefaultPrice && $userRoleId != ROLE_ADMIN){
          $this->Session->setFlash($productPriceLessThanDefaultPriceError.'No se guardó la cotización.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolProductPriceRepresentsBenefit){
          $this->Session->setFlash($productPriceBenefitError.'No se guardó la cotización.', 'default',['class' => 'error-message']);
        }
        elseif (!$boolRejectedOK){
          $this->Session->setFlash(__('Se debe indicar una razón de caída para cotizaciones caídas.  No se guardó la cotización.'), 'default',['class' => 'error-message']);
        }
        else {		
          $datasource=$this->Quotation->getDataSource();
          $datasource->begin();
          try {
            //pr($this->request->data);
            $previousQuotation=$this->Quotation->find('first',[
              'conditions'=>[
                'Quotation.id'=>$id,
              ],
              'contain'=>[
                'QuotationProduct',
              ],
            ]);
            // TECHNICALLY IT IS NOT POSSIBLE THAT THERE ARE NO PRODUCTS IN THE SALES ORDER, SO WE SPARE A CHECK ON EMPTY
            foreach ($previousQuotation['QuotationProduct'] as $previousQuotationProduct){
              //pr($previousQuotationProduct);
              $this->QuotationProduct->id=$previousQuotationProduct['id'];
              if (!$this->QuotationProduct->delete($previousQuotationProduct['id'])){
                echo "Problema removiendo los productos anteriores de la cotización";
                pr($this->validateErrors($this->QuotationProduct));
                throw new Exception();
              }
            }
            
            $this->Quotation->id=$id;
            if (!$this->Quotation->save($this->request->data)) {
              echo "Problema guardando la cotización";
              pr($this->validateErrors($this->Quotation));
              throw new Exception();
            }
            $quotationId=$this->Quotation->id;
            
            foreach ($this->request->data['QuotationProduct'] as $quotationProduct){
              if ($quotationProduct['product_id']>0){
                //pr($quotationProduct);
                $productArray=[];
                $productArray['QuotationProduct']['quotation_id']=$quotationId;
                $productArray['QuotationProduct']['product_id']=$quotationProduct['product_id'];
                $productArray['QuotationProduct']['raw_material_id']=$quotationProduct['raw_material_id'];
                $productArray['QuotationProduct']['product_quantity']=$quotationProduct['product_quantity'];
                $productArray['QuotationProduct']['product_unit_price']=$quotationProduct['product_unit_price'];
                $productArray['QuotationProduct']['product_total_price']=$quotationProduct['product_total_price'];
                $productArray['QuotationProduct']['bool_iva']=true;
                $productArray['QuotationProduct']['currency_id']=$this->request->data['Quotation']['currency_id'];
                $this->QuotationProduct->create();
                if (!$this->QuotationProduct->save($productArray)) {
                  echo "Problema guardando los productos de la cotización";
                  pr($this->validateErrors($this->QuotationProduct));
                  throw new Exception();
                }
              }
            }
            
            if (!empty($this->request->data['QuotationRemark']['remark_text'])){
              $quotationRemark=$this->request->data['QuotationRemark'];
              //pr($quotationRemark);
              $quotationRemarkArray=[];
              $quotationRemarkArray['QuotationRemark']['user_id']=$quotationRemark['user_id'];
              $quotationRemarkArray['QuotationRemark']['quotation_id']=$quotationId;
              $quotationRemarkArray['QuotationRemark']['remark_datetime']=date('Y-m-d H:i:s');
              $quotationRemarkArray['QuotationRemark']['remark_text']=$quotationRemark['remark_text'];
              $quotationRemarkArray['QuotationRemark']['working_days_before_reminder']=$quotationRemark['working_days_before_reminder'];
              $quotationRemarkArray['QuotationRemark']['reminder_date']=$quotationRemark['reminder_date'];
              $quotationRemarkArray['QuotationRemark']['action_type_id']=$quotationRemark['action_type_id'];
              $this->QuotationRemark->create();
              if (!$this->QuotationRemark->save($quotationRemarkArray)) {
                echo "Problema guardando los productos de la cotización";
                pr($this->validateErrors($this->QuotationRemark));
                throw new Exception();
              }
            }
            
            //if (!$this->request->data['Quotation']['client_generic']){
            if (!in_array($this->request->data['Quotation']['client_id'],$genericClientIds)){
              $quotationClientData=[
                'id'=>$this->request->data['Quotation']['client_id'],
                'phone'=>$this->request->data['Quotation']['client_phone'],
                'email'=>$this->request->data['Quotation']['client_email'],
                'address'=>$this->request->data['Quotation']['client_address'],
                'ruc_number'=>'',
                'client_type_id'=>$this->request->data['Quotation']['client_type_id'],
                'zone_id'=>$this->request->data['Quotation']['zone_id'],
                
              ];
              if (!$this->ThirdParty->updateClientDataConditionally($quotationClientData,$userRoleId)['success']){
                echo "Problema actualizando los datos del cliente";
                throw new Exception();
              }
            }
            
            $datasource->commit();
            $this->recordUserAction($this->Quotation->id,null,null);
            $this->recordUserActivity($this->Session->read('User.username'),"Se editó la cotización número ".$this->request->data['Quotation']['quotation_code']);
            
            $this->Session->setFlash('Se editó la cotización','default',['class' => 'success']);
            return $this->redirect(['action' => 'detalle',$id]);
          } 
          catch(Exception $e){
            $datasource->rollback();
            //pr($e);
            $this->Session->setFlash(__('No se podía editar la cotización.'), 'default',['class' => 'error-message']);
          }
        }
       
     }
    } 
		else {
			$options = [
				'conditions' => [
					'Quotation.id' => $id,
				],
				'contain'=>[
					'QuotationProduct',
				],
			];
			$this->request->data = $this->Quotation->find('first', $options);
      $quotationDate=$this->request->data['Quotation']['quotation_date']; 
      
      $currencyId=$this->request->data['Quotation']['currency_id'];
      $clientId=$this->request->data['Quotation']['client_id'];
      
      $vendorUserId=$this->request->data['Quotation']['vendor_user_id'];
      $recordUserId=$this->request->data['Quotation']['record_user_id'];
      if (array_key_exists('credit_authorization_user_id',$this->request->data['Quotation'])){
        $creditAuthorizationUserId=$this->request->data['Quotation']['credit_authorization_user_id'];
      }
      
      $warehouseId=$this->request->data['Quotation']['warehouse_id'];
			for ($qp=0;$qp<count($this->request->data['QuotationProduct']);$qp++){
				$productId=$this->request->data['QuotationProduct'][$qp]['product_id'];
        $rawMaterialId=$this->request->data['QuotationProduct'][$qp]['raw_material_id'];
        $productInventory=$this->StockItem->getInventoryTotalQuantityAndValuePerProduct($productId,$quotationDate,$warehouseId,$rawMaterialId);
        $productCost=0;
        if ($productInventory['quantity'] > 0){
          $productCost=round($productInventory['value']/$productInventory['quantity'],2);
        }
        $this->request->data['QuotationProduct'][$qp]['product_unit_cost']=$productCost;
				$requestProducts[]['QuotationProduct']=$this->request->data['QuotationProduct'][$qp];
			}
		}
		$quotationDatePlusOne=date( "Y-m-d", strtotime($quotationDate."+1 days"));
		$this->set(compact('requestProducts'));
    $this->set(compact('boolInitialLoad'));
		
    $this->set(compact('clientId'));
    $this->set(compact('currencyId'));
    
    $this->set(compact('vendorUserId'));
    $this->set(compact('recordUserId'));
    $this->set(compact('creditAuthorizationUserId'));
    
    $this->set(compact('warehouseId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    // WAREHOUSE ID IS DETERMINED BY QUOTATION ITSELF NOW
    
		$this->QuotationRemark->recursive=-1;
		$quotationRemarks=$this->QuotationRemark->find('all',[
			'conditions'=>[
				'QuotationRemark.quotation_id'=>$id,
			],
			'contain'=>[
				'User'
			],
		]);
		$this->set(compact('quotationRemarks'));
		
    $boolChangeDueDate='0';
		if ($userRoleId == ROLE_ADMIN || $canChangeDueDate) { 
			$boolChangeDueDate=true;
		}
		$this->set(compact('boolChangeDueDate'));
    
    
		$clients = $this->ThirdParty->getActiveClientList(20,$clientId);
    $this->set(compact('clients'));
    
		$currencies = $this->Quotation->Currency->find('list');
		$this->set(compact('currencies'));
    
		$rejectedReasons=$this->RejectedReason->find('list',[
			'order'=>'RejectedReason.list_order',
		]);
		$this->set(compact('rejectedReasons'));
		
		$quotationDateAsString=$this->Quotation->deconstruct('quotation_date',$this->request->data['Quotation']['quotation_date']);
		$exchangeRateQuotation=$this->ExchangeRate->getApplicableExchangeRateValue($quotationDateAsString);
		$this->set(compact('exchangeRateQuotation'));
		
		$actionTypes=$this->ActionType->find('list',['order'=>'ActionType.list_order ASC']);
		$this->set(compact('actionTypes'));
		
    $finishedProductsForEdit=[];
    $rawMaterialsForEdit=[];
    foreach ($requestProducts as $requestProduct){
      if (!in_array($requestProduct['QuotationProduct']['product_id'],$finishedProductsForEdit)){
        $finishedProductsForEdit[]=$requestProduct['QuotationProduct']['product_id'];
      }
      if (!in_array($requestProduct['QuotationProduct']['raw_material_id'],$rawMaterialsForEdit)){
        $rawMaterialsForEdit[]=$requestProduct['QuotationProduct']['raw_material_id'];
      }
    }
    $availableProductsForSale=$this->Product->getAvailableProductsForSale($quotationDate,$warehouseId,false);
    $products=$availableProductsForSale['products'];
    if ($warehouseId != WAREHOUSE_INJECTION){
      $availableInjectionProductsForSale=$this->Product->getAvailableProductsForSale($quotationDate,WAREHOUSE_INJECTION,false);
      $injectionProducts=$availableInjectionProductsForSale['products'];
      //pr($injectionProducts);
      $products+=$injectionProducts;    
    }
    
    $rawMaterialsAvailablePerFinishedProduct=$availableProductsForSale['rawMaterialsAvailablePerFinishedProduct'];
    $rawMaterials=$availableProductsForSale['rawMaterials'];
    $this->set(compact('products'));
    $this->set(compact('rawMaterialsAvailablePerFinishedProduct'));
    $this->set(compact('rawMaterials'));
    
    if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      $users=$this->User->getActiveVendorAdminUserList($warehouseId);
    }
    elseif ($canSeeAllVendors) {
      $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    }
    else {
      $users=$this->User->getActiveUserList($loggedUserId);
    }
    $this->set(compact('users'));
     
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
    $priceClientCategories=$this->PriceClientCategory->getPriceClientCategoryList();
    $this->set(compact('priceClientCategories'));
    
    $productCategoriesPerProduct=$this->Product->getProductCategoriesPerProduct();
    $this->set(compact('productCategoriesPerProduct'));
    
    $productTypesPerProduct=$this->Product->getProductTypesPerProduct();
    $this->set(compact('productTypesPerProduct'));
    
    
    $aco_name="Quotations/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Quotations/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Quotations/detalle";		
		$bool_view_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_view_permission'));
		
		$aco_name="ThirdParties/index";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/add";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Quotation->id = $id;
		if (!$this->Quotation->exists()) {
			throw new NotFoundException(__('Invalid quotation'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->Quotation->recursive=-1;
		$quotation=$this->Quotation->find('first',[
			'conditions'=>[
				'Quotation.id'=>$id,
			],
			'contain'=>[
				'QuotationProduct',
        'QuotationRemark',
        'SalesOrder'=>[
					'conditions'=>[
						'SalesOrder.bool_annulled'=>'0',
					],
				],
			],
		]);
		$flashMessage="";
		$boolDeletionAllowed=true;
		
		if (!empty($quotation['SalesOrder']['id'])){
			$boolDeletionAllowed='0';
			$flashMessage.="Esta cotización tiene una orden de venta asociada.  Para poder eliminar la cotización, primero hay que eliminar la orden de venta ".$quotation['SalesOrder']['sales_order_code'].".";
		}
		if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó la cotización.";
			$this->Session->setFlash($flashMessage, 'default',['class' => 'error-message']);
			return $this->redirect(['action' => 'detalle',$id]);
		}
		else {
			$datasource=$this->Quotation->getDataSource();
			$datasource->begin();	
			try {
				//delete all quotation products, quotation remarks and quotation images
				foreach ($quotation['QuotationProduct'] as $quotationProduct){
					if (!$this->Quotation->QuotationProduct->delete($quotationProduct['id'])) {
						echo "Problema al eliminar el producto de la cotización";
						pr($this->validateErrors($this->Quotation->QuotationProduct));
						throw new Exception();
					}
				}
				foreach ($quotation['QuotationRemark'] as $quotationRemark){
					if (!$this->Quotation->QuotationRemark->delete($quotationRemark['id'])) {
						echo "Problema al eliminar la remarca de la cotización";
						pr($this->validateErrors($this->Quotation->QuotationRemark));
						throw new Exception();
					}
				}
				if (!$this->Quotation->delete($id)) {
					echo "Problema al eliminar la cotización";
					pr($this->validateErrors($this->Quotation));
					throw new Exception();
				}
						
				$datasource->commit();
					
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=[];
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$quotation['Quotation']['id'];
				$deletionArray['Deletion']['reference']=$quotation['Quotation']['quotation_code'];
				$deletionArray['Deletion']['type']='Quotation';
				$this->Deletion->save($deletionArray);
				
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la cotización número ".$quotation['Quotation']['quotation_code']);
						
				$this->Session->setFlash(__('Se eliminó la cotización.'),'default',['class' => 'success']);				
				return $this->redirect(['action' => 'resumen']);
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar la cotización.'), 'default',['class' => 'error-message']);
				return $this->redirect(['action' => 'detalle',$id]);
			}
		}
		return $this->redirect(['action' => 'resumen']);
	}

  public function verReporteGestionDeVentas(){
		$this->loadModel('ExchangeRate');
		$this->loadModel('Invoice');
		$this->loadModel('InvoiceSalesOrder');
		$this->loadModel('SalesOrder');
		$this->loadModel('User');
		
		$this->loadModel('RejectedReason');
		
		$this->loadModel('ActionType');
		$this->loadModel('Client');
		$this->loadModel('QuotationRemark');
		$this->loadModel('SalesOrderRemark');
		
		$userId=0;
		$currencyId=CURRENCY_USD;
		$userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleID'));
		if ($userRoleId!=ROLE_ADMIN && $userRoleId!=ROLE_ASSISTANT) { 
			$userId=$this->Auth->User('id');
		}
		//echo "user id is ".$userId."<br/>";
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			//pr($startDateArray);
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$currencyId=$this->request->data['Report']['currency_id'];
			
			$userId=$this->request->data['Report']['user_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			if ($this->Session->check('currencyId')){
				$currencyId=$_SESSION['currencyId'];
			}
			//if (!empty($_SESSION['userId'])){
			//	$userId=$_SESSION['userId'];
			//}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$_SESSION['currencyId']=$currencyId;
		$_SESSION['userId']=$userId;
		//echo "user id is ".$userId."<br/>";
		$this->set(compact('startDate','endDate'));
		$this->set(compact('currencyId','userId'));
		
		$userConditions=['User.bool_active'=>true];
		$this->User->recursive=-1;
		if ($userId>0){
			$userConditions['User.id']=$userId;
		}
		
		$actionTypes=$this->ActionType->find('list');
		//pr($actionTypes);
		$this->set(compact('actionTypes'));
		
		$rejectedReasons=$this->RejectedReason->find('list');
		//pr($rejectedReasons);
		$this->set(compact('rejectedReasons'));
		
		//$clients=$this->Client->find('list',['conditions'=>['Client.bool_active']=>true],'order'=>'Client.company_name ASC']);
    $clients=$this->Client->find('list',[
      'conditions'=>['Client.bool_active'=>true],
      'order'=>'Client.company_name ASC'
    ]);
		//pr($clients);
		$this->set(compact('clients'));
		
		$selectedUsers=$this->User->find('all',[
			'fields'=>['User.id','User.username'],
			'conditions'=>$userConditions,
      'order'=>'User.username ASC'
		]);
		//pr($selectedUsers);
		$this->Quotation->recursive=-1;
		for ($u=0;$u<count($selectedUsers);$u++){
			$quotations=$this->Quotation->find('all',[
				'conditions'=>[
					'Quotation.quotation_date >='=>$startDate,
					'Quotation.quotation_date <'=>$endDatePlusOne,
					'Quotation.vendor_user_id'=>$selectedUsers[$u]['User']['id'],
				],
				'contain'=>[
					'Client',
					'Contact',
					'Currency',
					'SalesOrder'=>[
						'conditions'=>[
							'SalesOrder.bool_annulled'=>'0',
						],
						'InvoiceSalesOrder'=>[
							'Invoice',
						],
					],
					'RejectedReason',
				],
				'order'=>'Quotation.quotation_date,Quotation.quotation_code',
			]);
			//pr($quotations);
			$quotationTotals=[];
			
			$quotationTotals['value_quotations']=0;
			$quotationTotals['quantity_quotations']=0;
			
			$quotationTotals['value_rejected']=0;
			$quotationTotals['quantity_rejected']=0;
			$quotationTotals['value_pending']=0;
			$quotationTotals['quantity_pending']=0;
			
			$quotationTotals['value_sales_orders']=0;
			$quotationTotals['quantity_sales_orders']=0;
			$quotationTotals['value_invoices']=0;
			$quotationTotals['quantity_invoices']=0;
			
			$quotationRejections=[];
			
			foreach ($rejectedReasons as $id=>$name){
				$quotationRejections[$id]=0;
			}
			
			if (!empty($quotations)){
				for ($q=0;$q<count($quotations);$q++){
					$quotationDate=$quotations[$q]['Quotation']['quotation_date'];
					$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($quotationDate);
					$quotations[$q]['Quotation']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
				
					$exchangeRateFactor=1;
					if ($currencyId!=$quotations[$q]['Quotation']['currency_id']){
						if ($currencyId==CURRENCY_CS){
							$exchangeRateFactor=$exchangeRate['ExchangeRate']['rate'];
						}
						else if ($currencyId==CURRENCY_USD){
							$exchangeRateFactor=1/$exchangeRate['ExchangeRate']['rate'];
						}
					}
					
					$quotationTotals['value_quotations']+=round($quotations[$q]['Quotation']['price_subtotal']*$exchangeRateFactor,2);
					$quotationTotals['quantity_quotations']+=1;
					
					if ($quotations[$q]['Quotation']['bool_rejected']){
						$quotationTotals['value_rejected']+=round($quotations[$q]['Quotation']['price_subtotal']*$exchangeRateFactor,2);
						$quotationTotals['quantity_rejected']+=1;
						if (!empty($quotations[$q]['Quotation']['rejected_reason_id'])){
							$quotationRejections[$quotations[$q]['Quotation']['rejected_reason_id']]+=1;
						}
						else {
							//pr($quotations[$q]['Quotation']);
						}
						
					}
					else {
						if (empty($quotations[$q]['SalesOrder'])){
							$quotationTotals['value_pending']+=round($quotations[$q]['Quotation']['price_subtotal']*$exchangeRateFactor,2);
							$quotationTotals['quantity_pending']+=1;
						}
						else {
							for ($qso=0;$qso<count($quotations[$q]['SalesOrder']);$qso++){
								$salesOrderDate=$quotations[$q]['SalesOrder'][$qso]['sales_order_date'];
								$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($salesOrderDate);
								$quotations[$q]['SalesOrder'][$qso]['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
								
								$exchangeRateFactor=1;
								if ($currencyId!=$quotations[$q]['SalesOrder'][$qso]['currency_id']){
									if ($currencyId==CURRENCY_CS){
										$exchangeRateFactor=$exchangeRate['ExchangeRate']['rate'];
									}
									else if ($currencyId==CURRENCY_USD){
										$exchangeRateFactor=1/$exchangeRate['ExchangeRate']['rate'];
									}
								}
								$quotationTotals['value_sales_orders']+=round($quotations[$q]['SalesOrder'][$qso]['price_subtotal']*$exchangeRateFactor,2);
								$quotationTotals['quantity_sales_orders']+=1;
								if (!empty($quotations[$q]['SalesOrder'][$qso]['InvoiceSalesOrder'])){
									for ($i=0;$i<count($quotations[$q]['SalesOrder'][$qso]['InvoiceSalesOrder']);$i++){
										//pr ($quotations[$q]['SalesOrder'][$qso]['InvoiceSalesOrder'][$i]);
										$invoiceDate=$quotations[$q]['SalesOrder'][$qso]['InvoiceSalesOrder'][$i]['Invoice']['invoice_date'];
										$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoiceDate);
										$quotations[$q]['SalesOrder'][$qso]['InvoiceSalesOrder'][$i]['Invoice']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
										
										$exchangeRateFactor=1;
										if ($currencyId!=$quotations[$q]['SalesOrder'][$qso]['InvoiceSalesOrder'][$i]['Invoice']['currency_id']){
											if ($currencyId==CURRENCY_CS){
												$exchangeRateFactor=$exchangeRate['ExchangeRate']['rate'];
											}
											else if ($currencyId==CURRENCY_USD){
												$exchangeRateFactor=1/$exchangeRate['ExchangeRate']['rate'];
											}
										}
										$quotationTotals['value_invoices']+=round($quotations[$q]['SalesOrder'][$qso]['InvoiceSalesOrder'][$i]['Invoice']['price_subtotal']*$exchangeRateFactor,2);
										$quotationTotals['quantity_invoices']+=1;
									}
								}
							}
						}
					}
				}
			}
			
			$this->QuotationRemark->recursive=-1;
			$relevantClients=[];
			foreach ($actionTypes as $id=>$name){
				$actionsForThisActionType=$this->QuotationRemark->find('all',[
					'fields'=>['QuotationRemark.id'],
					'conditions'=>[
						'QuotationRemark.action_type_id'=>$id,
						'QuotationRemark.remark_datetime >='=>$startDate,
						'QuotationRemark.remark_datetime <'=>$endDatePlusOne,
						'QuotationRemark.user_id'=>$selectedUsers[$u]['User']['id'],
					],
					'contain'=>[
						'Quotation'=>[
							'fields'=>['Quotation.client_id'],
						],
					],
				]);
				if (!empty($actionsForThisActionType)){
					foreach ($actionsForThisActionType as $action){
						$relevantClients[]=$action['Quotation']['client_id'];
						//echo "for quotation remark id ".$action['QuotationRemark']['id']." the client id is ".$action['Quotation']['client_id']."<br/>";
					}
				}
				$actionsForThisActionType=$this->SalesOrderRemark->find('all',[
					'fields'=>['SalesOrderRemark.id'],
					'conditions'=>[
						'SalesOrderRemark.action_type_id'=>$id,
						'SalesOrderRemark.remark_datetime >='=>$startDate,
						'SalesOrderRemark.remark_datetime <'=>$endDatePlusOne,
						'SalesOrderRemark.user_id'=>$selectedUsers[$u]['User']['id'],
					],
					'contain'=>[
						'SalesOrder'=>[
							'fields'=>['SalesOrder.id'],
							'Quotation'=>[
								'fields'=>['Quotation.client_id'],
							],
						],
					],
				]);
				if (!empty($actionsForThisActionType)){
					foreach ($actionsForThisActionType as $action){
						if (!empty($action['SalesOrder']['id'])){
							$relevantClients[]=$action['SalesOrder']['Quotation']['client_id'];
							//echo "for quotation remark id ".$action['SalesOrderRemark']['id']." the client id is ".$action['SalesOrder']['Quotation']['client_id']."<br/>";
						}
					}
				}
			}
			//pr($relevantClients);
			$relevantClients=array_unique($relevantClients);
			//pr($relevantClients);
			$clientActions=[];
			foreach ($relevantClients as $clientId){
				$quotationActionsForClient=[];
				$quotationIdsForClient=$this->Quotation->find('list',[
					'fields'=>['Quotation.id'],
					'conditions'=>[
						'Quotation.client_id'=>$clientId,
					],
				]);
				foreach ($actionTypes as $actionTypeId=> $actionTypeName){
					$quotationRemarksForClientAndActionType=$this->QuotationRemark->find('list',[
						'conditions'=>[
							'QuotationRemark.action_type_id'=>$actionTypeId,
							'QuotationRemark.remark_datetime >='=>$startDate,
							'QuotationRemark.remark_datetime <'=>$endDatePlusOne,
							'QuotationRemark.user_id'=>$selectedUsers[$u]['User']['id'],
							'QuotationRemark.quotation_id'=>$quotationIdsForClient,
						],
					]);
					//echo "client id is ".$clientId." and action type id is ".$actionTypeId."<br/>";
					//pr($quotationRemarksForClientAndActionType);
					if (!empty($quotationRemarksForClientAndActionType)){
						$clientActions[$clientId]['quotationActionsForClients'][$actionTypeId]=count($quotationRemarksForClientAndActionType);
					}
					else {
						$clientActions[$clientId]['quotationActionsForClients'][$actionTypeId]=0;
					}
				}
				$salesOrderActionsForClient=[];
				$salesOrderIdsForClient=$this->SalesOrder->find('list',[
					'fields'=>['SalesOrder.id'],
					'conditions'=>[
						'SalesOrder.quotation_id'=>$quotationIdsForClient,
					],
				]);
				foreach ($actionTypes as $actionTypeId=> $actionTypeName){
					$salesOrderRemarksForClientAndActionType=$this->SalesOrderRemark->find('list',[
						'conditions'=>[
							'SalesOrderRemark.action_type_id'=>$actionTypeId,
							'SalesOrderRemark.remark_datetime >='=>$startDate,
							'SalesOrderRemark.remark_datetime <'=>$endDatePlusOne,
							'SalesOrderRemark.user_id'=>$selectedUsers[$u]['User']['id'],
							'SalesOrderRemark.sales_order_id'=>$salesOrderIdsForClient,
						],
					]);
					//pr($salesOrderRemarksForClientAndActionType);
					if (!empty($salesOrderRemarksForClientAndActionType)){
						$clientActions[$clientId]['salesOrderActionsForClients'][$actionTypeId]=count($salesOrderRemarksForClientAndActionType);
					}
					else {
						$clientActions[$clientId]['salesOrderActionsForClients'][$actionTypeId]=0;
					}
				}
			}
			//pr($clientActions);
			
			$clientsCreatedForPeriod=$this->Client->find('all',[
				'conditions'=>[
					'Client.bool_active'=>true,
					'Client.created >='=>$startDate,
					'Client.created <'=>$endDatePlusOne,
					'Client.creating_user_id'=>$selectedUsers[$u]['User']['id'],
				],
			]);
			
			$selectedUsers[$u]['Quotations']=$quotations;
			$selectedUsers[$u]['QuotationTotals']=$quotationTotals;
			$selectedUsers[$u]['QuotationRejections']=$quotationRejections;
			$selectedUsers[$u]['ClientActions']=$clientActions;
			$selectedUsers[$u]['CreatedClients']=$clientsCreatedForPeriod;
			
			$allQuotationIdsForUser=$this->Quotation->find('list',[
				'fields'=>['Quotation.id'],
				'conditions'=>[
					'Quotation.quotation_date <'=>$endDatePlusOne,
					'Quotation.vendor_user_id'=>$selectedUsers[$u]['User']['id'],
				],
			]);
			
			// now get all sales orders for the period, also for quotations outside the period
			$salesOrders=$this->SalesOrder->find('all',[
				'conditions'=>[
					'SalesOrder.sales_order_date >='=>$startDate,
					'SalesOrder.sales_order_date <'=>$endDatePlusOne,
					'SalesOrder.quotation_id'=>$allQuotationIdsForUser,
				],
				'contain'=>[
					'Currency',
					'Quotation'=>[
						'Client',
						'Contact',
					],
					'InvoiceSalesOrder'=>[
						'Invoice',
					],
				],
				'order'=>'SalesOrder.sales_order_date,SalesOrder.sales_order_code',
			]);
			$salesOrderTotals=[];
			$salesOrderTotals['value_sales_orders']=0;
			$salesOrderTotals['quantity_sales_orders']=0;
			
			$salesOrderTotals['value_annulled']=0;
			$salesOrderTotals['quantity_annulled']=0;
			$salesOrderTotals['value_pending']=0;
			$salesOrderTotals['quantity_pending']=0;
			
			$salesOrderTotals['value_invoices']=0;
			$salesOrderTotals['quantity_invoices']=0;
			
			if (!empty($salesOrders)){
				for ($so=0;$so<count($salesOrders);$so++){
					$salesOrderDate=$salesOrders[$so]['SalesOrder']['sales_order_date'];
					$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($salesOrderDate);
					$salesOrders[$so]['SalesOrder']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
					
					$exchangeRateFactor=1;
					if ($currencyId!=$salesOrders[$so]['SalesOrder']['currency_id']){
						if ($currencyId==CURRENCY_CS){
							$exchangeRateFactor=$exchangeRate['ExchangeRate']['rate'];
						}
						else if ($currencyId==CURRENCY_USD){
							$exchangeRateFactor=1/$exchangeRate['ExchangeRate']['rate'];
						}
					}
				
					$salesOrderTotals['value_sales_orders']+=round($salesOrders[$so]['SalesOrder']['price_subtotal']*$exchangeRateFactor,2);
					$salesOrderTotals['quantity_sales_orders']+=1;
					
					if ($salesOrders[$so]['SalesOrder']['bool_annulled']){
						$salesOrderTotals['value_annulled']+=round($salesOrders[$so]['SalesOrder']['price_subtotal']*$exchangeRateFactor,2);
						$salesOrderTotals['quantity_annulled']+=1;
					}
					else {
						if (!empty($salesOrders[$so]['InvoiceSalesOrder'])){
							foreach ($salesOrders[$so]['InvoiceSalesOrder'] as $invoiceSalesOrder){
								$exchangeRateFactorForInvoice=1;
								if ($invoiceSalesOrder['Invoice']['currency_id']!=$salesOrders[$so]['SalesOrder']['currency_id']){
									if ($salesOrders[$so]['SalesOrder']['currency_id']==CURRENCY_CS){
										if ($currencyId==CURRENCY_USD){
											$exchangeRateFactorForInvoice=1;
										}
										else if ($currencyId==CURRENCY_CS){
											$exchangeRateFactorForInvoice=$exchangeRate['ExchangeRate']['rate'];
										}
									}
									elseif ($salesOrders[$so]['SalesOrder']['currency_id']==CURRENCY_USD){
										if ($currencyId==CURRENCY_CS){
											$exchangeRateFactorForInvoice=1;
										}
										else if ($currencyId==CURRENCY_USD){
											$exchangeRateFactorForInvoice=1/$exchangeRate['ExchangeRate']['rate'];
										}
									}
								}
								$salesOrderTotals['value_invoices']+=round($invoiceSalesOrder['Invoice']['price_subtotal']*$exchangeRateFactorForInvoice,2);
								$salesOrderTotals['quantity_invoices']+=1;
							}							
						}
						else {
							$salesOrderTotals['value_pending']+=round($salesOrders[$so]['SalesOrder']['price_subtotal']*$exchangeRateFactor,2);
							$salesOrderTotals['quantity_pending']+=1;
						}
					}
				}
			}
			$selectedUsers[$u]['SalesOrders']=$salesOrders;
			$selectedUsers[$u]['SalesOrderTotals']=$salesOrderTotals;
			
			$allInvoiceIdsForUser=$this->InvoiceSalesOrder->find('list',[
				'fields'=>['InvoiceSalesOrder.invoice_id'],
				'conditions'=>[
					'InvoiceSalesOrder.user_id'=>$selectedUsers[$u]['User']['id'],
				],
			]);
			
			$invoices=$this->Invoice->find('all',[
				'conditions'=>[
					'Invoice.invoice_date >='=>$startDate,
					'Invoice.invoice_date <'=>$endDatePlusOne,
					'Invoice.id'=>$allInvoiceIdsForUser,
				],
				'contain'=>[
					'Currency',
					'Client',
				],
				'order'=>'Invoice.invoice_date,Invoice.invoice_code',
			]);
			$invoiceTotals=[];
			$invoiceTotals['value_invoices']=0;
			$invoiceTotals['quantity_invoices']=0;
			
			$invoiceTotals['value_paid']=0;
			$invoiceTotals['quantity_paid']=0;
			$invoiceTotals['value_payment_pending']=0;
			$invoiceTotals['quantity_payment_pending']=0;
			
			if (!empty($invoices)){
				for ($i=0;$i<count($invoices);$i++){
					$invoiceDate=$invoices[$i]['Invoice']['invoice_date'];
					$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoiceDate);
					$invoices[$i]['Invoice']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
				
					$exchangeRateFactor=1;
					if ($currencyId!=$invoices[$i]['Invoice']['currency_id']){
						if ($currencyId==CURRENCY_CS){
							$exchangeRateFactor=$exchangeRate['ExchangeRate']['rate'];
						}
						else if ($currencyId==CURRENCY_USD){
							$exchangeRateFactor=1/$exchangeRate['ExchangeRate']['rate'];
						}
					}
				
					$invoiceTotals['value_invoices']+=round($invoices[$i]['Invoice']['price_subtotal']*$exchangeRateFactor,2);
					$invoiceTotals['quantity_invoices']+=1;
					
					if ($invoices[$i]['Invoice']['bool_paid']){
						$invoiceTotals['value_paid']+=round($invoices[$i]['Invoice']['price_subtotal']*$exchangeRateFactor,2);
						$invoiceTotals['quantity_paid']+=1;
					}
					else {
						$invoiceTotals['value_payment_pending']+=round($invoices[$i]['Invoice']['price_subtotal']*$exchangeRateFactor,2);
						$invoiceTotals['quantity_payment_pending']+=1;						
					}
				}
			}
			$selectedUsers[$u]['Invoices']=$invoices;
			$selectedUsers[$u]['InvoiceTotals']=$invoiceTotals;
		}
		
		$users=$this->User->find('list',[
      'conditions'=>['bool_active'=>true],
      'order'=>'User.username ASC'
    ]);
		
		$this->set(compact('users','selectedUsers'));	
		
		$this->loadModel('Currency');
		$currencies=$this->Currency->find('list');
		$this->set(compact('currencies'));
	}

	public function guardarReporteGestionDeVentas() {
		$exportData=$_SESSION['reporteGestionDeVentas'];
		$this->set(compact('exportData'));
	}
	
	public function verReporteCotizacionesPorEjecutivo(){
		$userId=0;
		$currencyId=CURRENCY_USD;
		$userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userrole'));
		if ($userRoleId!=ROLE_ADMIN&&$userRoleId!=ROLE_ASSISTANT) { 
			$userId=$this->Auth->User('id');
		}
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			//pr($startDateArray);
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$currencyId=$this->request->data['Report']['currency_id'];
			
			$userId=$this->request->data['Report']['user_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			if ($this->Session->check('currencyId')){
				$currencyId=$_SESSION['currencyId'];
			}
			//if (!empty($_SESSION['userId'])){
			//	$userId=$_SESSION['userId'];
			//}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$_SESSION['currencyId']=$currencyId;
		$_SESSION['userId']=$userId;
		
		$this->set(compact('startDate','endDate','currencyId','userId'));
		$this->loadModel('User');
		$userConditions=[];
		$this->User->recursive=-1;
		
		//if ($userId>0){
		//	$userConditions['User.id']=$userId;
		//}
		
		$selectedUsers=$this->User->find('all',[
			'fields'=>['User.id','User.username'],
			'conditions'=>$userConditions,
			'order'=>'User.username',
		]);
		//pr($selectedUsers);
		$this->Quotation->recursive=-1;
		for ($u=0;$u<count($selectedUsers);$u++){
			$quotations=$this->Quotation->find('all',[
				'conditions'=>[
					'Quotation.quotation_date >='=>$startDate,
					'Quotation.quotation_date <'=>$endDatePlusOne,
					'Quotation.vendor_user_id'=>$selectedUsers[$u]['User']['id'],
				],
				'contain'=>[
					'Client',
					'Contact',
					'Currency',
					'SalesOrder'=>[
						'fields'=>'SalesOrder.id',
						'conditions'=>[
							'SalesOrder.bool_annulled'=>'0',
						],
					],
				],
				'order'=>'Quotation.quotation_date,Quotation.quotation_code',
			]);
			$totalPriceCS=0;
			$totalPriceUSD=0;
			if (!empty($quotations)){
				$this->loadModel('ExchangeRate');
				for ($q=0;$q<count($quotations);$q++){
					$quotationDate=$quotations[$q]['Quotation']['quotation_date'];
					$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($quotationDate);
					$quotations[$q]['Quotation']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
					
					if (!empty($quotations[$q]['SalesOrder'])){
						$quotations[$q]['Quotation']['bool_sales_order']=true;
						$quotations[$q]['Quotation']['dropped']=0;
						$quotations[$q]['Quotation']['sold']=100;
					}
					else {
						$quotations[$q]['Quotation']['bool_sales_order']='0';
						if ($quotations[$q]['Quotation']['bool_rejected']){
							$quotations[$q]['Quotation']['dropped']=100;
							$quotations[$q]['Quotation']['sold']=0;
						}
						else {
							$quotations[$q]['Quotation']['dropped']=0;
							$quotations[$q]['Quotation']['sold']=0;
						}
					}
					if ($quotations[$q]['Quotation']['currency_id']==CURRENCY_CS){
						$totalPriceCS+=$quotations[$q]['Quotation']['price_subtotal'];
						//added calculation of totals in US$
						$totalPriceUSD+=round($quotations[$q]['Quotation']['price_subtotal']/$quotations[$q]['Quotation']['exchange_rate'],2);
					}
					else if ($quotations[$q]['Quotation']['currency_id']==CURRENCY_USD){
						$totalPriceUSD+=$quotations[$q]['Quotation']['price_subtotal'];
						//added calculation of totals in C$
						$totalPriceCS+=round($quotations[$q]['Quotation']['price_subtotal']*$quotations[$q]['Quotation']['exchange_rate'],2);
					}
				}
			}
			$selectedUsers[$u]['Quotations']=$quotations;
			$selectedUsers[$u]['price_subtotal_CS']=$totalPriceCS;
			$selectedUsers[$u]['price_subtotal_USD']=$totalPriceUSD;
		}
		
		if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      $users=$this->User->getActiveVendorAdminUserList($warehouseId);
    }
    elseif ($canSeeAllVendors) {
      $users=$this->User->getActiveVendorOnlyUserList($warehouseId);
    }
    else {
      $users=$this->User->getActiveUserList($loggedUserId);
    }
    
		$this->set(compact('users','selectedUsers'));	
		
		$this->loadModel('Currency');
		$currencies=$this->Currency->find('list');
		$this->set(compact('currencies'));
	}

	public function guardarReporteCotizacionesPorEjecutivo() {
		$exportData=$_SESSION['reporteCotizacionesPorEjecutivo'];
		$this->set(compact('exportData'));
	}

	public function verReporteCotizacionesPorCategoria(){
    $currencyId=CURRENCY_USD;
		$product_category_id=0;
		$product_id=0;
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
      $currencyId=$this->request->data['Report']['currency_id'];
			$product_category_id=$this->request->data['Report']['product_category_id'];
			$product_id=$this->request->data['Report']['product_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			if ($this->Session->check('currencyId')){
				$currencyId=$_SESSION['currencyId'];
			}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$_SESSION['currencyId']=$currencyId;
		
		$this->set(compact('startDate','endDate','currencyId','product_category_id','product_id'));
		
		$this->loadModel('Product');
		$this->loadModel('QuotationProduct');
		$this->loadModel('ProductCategory');
		
		$productCategoryConditions=[];
		$this->ProductCategory->recursive=-1;
		//if ($product_category_id>0){
		//	$productCategoryConditions['ProductCategory.id']=$product_category_id;
		//}
		$selectedProductCategories=$this->ProductCategory->find('all',[
			'fields'=>['ProductCategory.id','ProductCategory.name'],
			'conditions'=>$productCategoryConditions,
			'order'=>'ProductCategory.name',
		]);
		//pr($selectedProductCategories);
		
		$productConditions=['Product.bool_active'=>true];
		$this->Product->recursive=-1;
		if ($product_id>0){
			$productConditions['Product.id']=$product_id;
		}
		$selectedProducts=$this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>$productConditions,
		]);
		//pr($selectedProducts);
		$this->set(compact('selectedProducts'));	
		$selectedProductList=$this->Product->find('list',[
			'fields'=>['Product.id'],
			'conditions'=>$productConditions,
		]);
		//pr($selectedProductList);
		
		$conditions=[
			'Quotation.quotation_date >='=>$startDate,
			'Quotation.quotation_date <'=>$endDatePlusOne,
		];
		$userRoleId = $this->Auth->User('role_id');
		if ($userRoleId!=ROLE_ADMIN&&$userRoleId!=ROLE_ASSISTANT) { 
			$conditions[]=['Quotation.user_id'=>$this->Auth->User('id')];
		}
		
		$this->Quotation->recursive=-1;
		$quotationsInPeriod=$this->Quotation->find('list',[
			'fields'=>'Quotation.id',
			'conditions'=>$conditions,
		]);
		for ($pc=0;$pc<count($selectedProductCategories);$pc++){
			// get the product ids in this product category and the selectedProductList
			$productsInProductCategory=$this->Product->find('list',[
				'fields'=>'Product.id',
				'conditions'=>[
					'Product.product_category_id'=>$selectedProductCategories[$pc]['ProductCategory']['id'],
					'Product.id'=>$selectedProductList,
				]
			]);
			//pr($productsInProductCategory);
			// get the products of the selectedproductcategory and the selectedProductList present in quotations for the selected period
			$quotationProducts=$this->QuotationProduct->find('all',[
				'fields'=>[
					'QuotationProduct.id','QuotationProduct.product_quantity',
					'QuotationProduct.currency_id','QuotationProduct.product_total_price',
				],
				'conditions'=>[
					'QuotationProduct.product_id'=>$productsInProductCategory,
					'QuotationProduct.quotation_id'=>$quotationsInPeriod,
					
				],
				'contain'=>[
					'Currency'=>[
						'fields'=>['Currency.id','Currency.abbreviation']
					],
					'Product'=>[
						'fields'=>['Product.id','Product.name']
					],
					'Quotation'=>[
						'fields'=>[
							'Quotation.id','Quotation.quotation_code','Quotation.quotation_date','Quotation.bool_rejected',
						],
						'Client',
						'Currency',
						'User',
						'SalesOrder'=>[
							'fields'=>['SalesOrder.id'],
							'conditions'=>[
								'SalesOrder.bool_annulled'=>'0',
							],
						],
					],
				],
				'order'=>'Quotation.quotation_date,Quotation.quotation_code',
			]);
			//pr($quotationProducts);
			$totalPriceCS=0;
			$totalPriceUSD=0;
			if (!empty($quotationProducts)){
				$this->loadModel('ExchangeRate');
				for ($qp=0;$qp<count($quotationProducts);$qp++){
					$quotationDate=$quotationProducts[$qp]['Quotation']['quotation_date'];
					$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($quotationDate);
					$quotationProducts[$qp]['QuotationProduct']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
					//$quotationProducts[$qp]['QuotationProduct']['dropped']=$this->QuotationProduct->getDroppedPercentageForQuotationProduct($quotationProducts[$qp]['QuotationProduct']['id']);
					//$quotationProducts[$qp]['QuotationProduct']['sold']=$this->QuotationProduct->getSoldPercentageForQuotationProduct($quotationProducts[$qp]['QuotationProduct']['id']);
					if (!empty($quotationProducts[$qp]['Quotation']['SalesOrder'])){
						$quotationProducts[$qp]['Quotation']['bool_sales_order']=true;
						$quotationProducts[$qp]['QuotationProduct']['dropped']=0;
						$quotationProducts[$qp]['QuotationProduct']['sold']=100;
					}
					else {
						$quotationProducts[$qp]['Quotation']['bool_sales_order']='0';
						if ($quotationProducts[$qp]['Quotation']['bool_rejected']){
							$quotationProducts[$qp]['QuotationProduct']['dropped']=100;
							$quotationProducts[$qp]['QuotationProduct']['sold']=0;
						}
						else {
							$quotationProducts[$qp]['QuotationProduct']['dropped']=0;
							$quotationProducts[$qp]['QuotationProduct']['sold']=0;
						}
					}
					if ($quotationProducts[$qp]['QuotationProduct']['currency_id']==CURRENCY_CS){
						$totalPriceCS+=$quotationProducts[$qp]['QuotationProduct']['product_total_price'];
						//added calculation of totals in US$
						$totalPriceUSD+=round($quotationProducts[$qp]['QuotationProduct']['product_total_price']/$quotationProducts[$qp]['QuotationProduct']['exchange_rate'],2);
					}
					else if ($quotationProducts[$qp]['QuotationProduct']['currency_id']==CURRENCY_USD){
						$totalPriceUSD+=$quotationProducts[$qp]['QuotationProduct']['product_total_price'];
						//added calculation of totals in C$
						$totalPriceCS+=round($quotationProducts[$qp]['QuotationProduct']['product_total_price']*$quotationProducts[$qp]['QuotationProduct']['exchange_rate'],2);
					}
				}
			}
			$selectedProductCategories[$pc]['QuotationProducts']=$quotationProducts;
			$selectedProductCategories[$pc]['total_price_CS']=$totalPriceCS;
			$selectedProductCategories[$pc]['total_price_USD']=$totalPriceUSD;
		}
		//pr($selectedProductCategories);
		$this->set(compact('selectedProductCategories'));
		
		$productCategories=$this->ProductCategory->find('list');
		$this->set(compact('productCategories'));	
		
		$products=$this->Product->find('list',[
      'conditions'=>['Product.bool_active'=>true],
      'order'=>'Product.name ASC'
    ]);
		$this->set(compact('products'));	
		
		$this->loadModel('Currency');
		$currencies=$this->Currency->find('list');
		$this->set(compact('currencies'));
	}
	
	public function guardarReporteCotizacionesPorCategoria() {
		$exportData=$_SESSION['reporteCotizacionesPorCategoria'];
		$this->set(compact('exportData'));
	}

	public function verReporteCotizacionesPorCliente(){
    $userId=$this->Auth->User('id');
		$userRoleId = $this->Auth->User('role_id');
		if ($userRoleId==ROLE_ADMIN||$userRoleId==ROLE_ASSISTANT) { 
			$userId=0;
		}
		
		$currencyId=CURRENCY_USD;
		$client_id=0;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			//pr($startDateArray);
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
      $userId=$this->request->data['Report']['user_id'];
			$currencyId=$this->request->data['Report']['currency_id'];
			$client_id=$this->request->data['Report']['client_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			if ($this->Session->check('userId')){
				$currencyId=$_SESSION['currencyId'];
			}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$_SESSION['currencyId']=$currencyId;
		
		$this->set(compact('startDate','endDate','userId','currencyId','client_id'));
		
    $this->loadModel('ThirdParty');
		$clientConditions=[
      'ThirdParty.bool_active'=>true,
      'ThirdParty.bool_provider'=>'0',
    ];
		$clientCount=	$this->ThirdParty->find('count', [
			'fields'=>['ThirdParty.id'],
			'conditions' => $clientConditions,
		]);
		
		$allClients=$this->ThirdParty->find('all',[
			'conditions' => $clientConditions,
			'contain'=>[				
				'ThirdPartyUser'=>[
					'User',
					'order'=>'ThirdPartyUser.assignment_datetime DESC,ThirdPartyUser.id DESC',
          'limit'=>1,
				],
			],
			'order'=>'ThirdParty.company_name ASC',
			'limit'=>($clientCount!=0?$clientCount:1),
		]);
    //pr($allClients);
    $clientIds=[];	
    for ($c=0;$c<count($allClients);$c++){
			if (empty($userId)||$allClients[$c]['ThirdPartyUser'][0]['bool_assigned']){
        $thisClient=$allClients[$c];
        $clientId=$thisClient['ThirdParty']['id'];
        $clientIds[]=$clientId;        
      }
		}
    $clients=$this->ThirdParty->find('list',[
			'conditions'=>[
				'ThirdParty.id'=>$clientIds,
			],
      'order'=>'ThirdParty.company_name',
		]);
		
    
    $clientConditions=[];
    if ($client_id>0){
      $clientConditions[]=['ThirdParty.id'=>$client_id];
    }
		$selectedClients=$this->ThirdParty->find('all',[
			'fields'=>['ThirdParty.id','ThirdParty.company_name'],
			'conditions'=>$clientConditions,
			'order'=>'ThirdParty.company_name',
		]);
		//pr($selectedUsers);
		$this->Quotation->recursive=-1;
		for ($c=0;$c<count($selectedClients);$c++){
			$conditions=[
				'Quotation.quotation_date >='=>$startDate,
				'Quotation.quotation_date <'=>$endDatePlusOne,
				'Quotation.client_id'=>$selectedClients[$c]['Client']['id'],
			];
			$userRoleId = $this->Auth->User('role_id');
			if ($userRoleId!=ROLE_ADMIN&&$userRoleId!=ROLE_ASSISTANT) { 
				$conditions[]=['Quotation.vendor_user_id'=>$this->Auth->User('id')];
			}
			$quotations=$this->Quotation->find('all',[
				'conditions'=>$conditions,
				'contain'=>[
					'Contact',
					'Currency',
					'User',
					'SalesOrder'=>[
						'fields'=>'SalesOrder.id',
						'conditions'=>[
							'SalesOrder.bool_annulled'=>'0',
						],
					],
				],
				'order'=>'Quotation.quotation_date,Quotation.quotation_code',
			]);
			if ($selectedClients[$c]['Client']['id']==36){
				//pr($quotations);
			}
			$totalPriceCS=0;
			$totalPriceUSD=0;
			if (!empty($quotations)){
				$this->loadModel('ExchangeRate');
				
				for ($q=0;$q<count($quotations);$q++){
					$quotationDate=$quotations[$q]['Quotation']['quotation_date'];
					$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($quotationDate);
					$quotations[$q]['Quotation']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
					
					//$quotations[$q]['Quotation']['dropped']=$this->Quotation->getDroppedPercentageForQuotation($quotations[$q]['Quotation']['id']);
					//$quotations[$q]['Quotation']['sold']=$this->Quotation->getSoldPercentageForQuotation($quotations[$q]['Quotation']['id']);
					
					if (!empty($quotations[$q]['SalesOrder'])){
						$quotations[$q]['Quotation']['bool_sales_order']=true;
						$quotations[$q]['Quotation']['dropped']=0;
						$quotations[$q]['Quotation']['sold']=100;
					}
					else {
						$quotations[$q]['Quotation']['bool_sales_order']='0';
						if ($quotations[$q]['Quotation']['bool_rejected']){
							$quotations[$q]['Quotation']['dropped']=100;
							$quotations[$q]['Quotation']['sold']=0;
						}
						else {
							$quotations[$q]['Quotation']['dropped']=0;
							$quotations[$q]['Quotation']['sold']=0;
						}
					}
					if ($quotations[$q]['Quotation']['currency_id']==CURRENCY_CS){
						$totalPriceCS+=$quotations[$q]['Quotation']['price_subtotal'];
						//added calculation of totals in US$
						$totalPriceUSD+=round($quotations[$q]['Quotation']['price_subtotal']/$quotations[$q]['Quotation']['exchange_rate'],2);
					}
					else if ($quotations[$q]['Quotation']['currency_id']==CURRENCY_USD){
						$totalPriceUSD+=$quotations[$q]['Quotation']['price_subtotal'];
						//added calculation of totals in C$
						$totalPriceCS+=round($quotations[$q]['Quotation']['price_subtotal']*$quotations[$q]['Quotation']['exchange_rate'],2);
					}
				}
			}
			$selectedClients[$c]['Quotations']=$quotations;
			$selectedClients[$c]['subtotal_price_CS']=$totalPriceCS;
			$selectedClients[$c]['subtotal_price_USD']=$totalPriceUSD;
		}
		
		$this->set(compact('clients','selectedClients'));	
		
		$this->loadModel('Currency');
		$currencies=$this->Currency->find('list');
		$this->set(compact('currencies'));
    
    $userConditions=['User.bool_active'=>true];
    if ($userRoleId!=ROLE_ADMIN&&$userRoleId!=ROLE_ASSISTANT){
			$userConditions['User.id']=$this->Auth->User('id');
		}
		$users=$this->User->find('list',[
			'conditions'=>$userConditions,
			'order'=>'User.username'
		]);
		$this->set(compact('users'));  
	}

	public function guardarReporteCotizacionesPorCliente() {
		$exportData=$_SESSION['reporteCotizacionesPorCliente'];
		$this->set(compact('exportData'));
	}

	public function generarOrdenDeVenta($quotationId){
		$this->Quotation->id = $quotationId;
		if (!$this->Quotation->exists()) {
			throw new NotFoundException(__('Invalid quotation'));
		}
		
		$quotation=$this->Quotation->find('first',[
			'conditions'=>[
				'Quotation.id'=>$quotationId,
			],
			'contain'=>[
				'QuotationProduct',
				'SalesOrder',
			]
		]);
		
		$boolGenerationPossible=true;
		$flashMessage="";
		if (count($quotation['SalesOrder'])>0){
			$boolGenerationPossible='0';
			$flashMessage.="Esta cotización ya tiene ordenes de venta correspondientes con números";
			if (count($quotation['SalesOrder'])==1){
				$flashMessage.=$quotation['SalesOrder'][0]['sales_order_code'].".";
			}
			else {
				for ($i=0;$i<count($quotation['SalesOrder']);$i++){
					$flashMessage.=$quotation['SalesOrder'][$i]['sales_order_code'];
					if ($i==count($quotation['SalesOrder'])-1){
						$flashMessage.=".";
					}
					else {
						$flashMessage.=" y ";
					}
				}
			}
		}
		if (!$boolGenerationPossible){
			$flashMessage.=" La generación automática de ordenes de venta ya no está posible para esta cotización.";
			$this->Session->setFlash($flashMessage, 'default',['class' => 'error-message']);
			return $this->redirect(['action' => 'detalle',$id]);
		}
		else {
			$this->loadModel('SalesOrder');
			$datasource=$this->SalesOrder->getDataSource();
			$datasource->begin();
			try {
				$salesOrderArray=[];
				$salesOrderArray['SalesOrder']['quotation_id']=$quotationId;	
				$salesOrderArray['SalesOrder']['sales_order_date']=date('Y-m-d');
				$salesOrderArray['SalesOrder']['sales_order_code']=$quotation['Quotation']['quotation_code'];
				$salesOrderArray['SalesOrder']['bool_annulled']='0';
				$salesOrderArray['SalesOrder']['price_subtotal']=$quotation['Quotation']['price_subtotal'];
				$salesOrderArray['SalesOrder']['currency_id']=$quotation['Quotation']['currency_id'];
				$this->SalesOrder->create();
				if (!$this->SalesOrder->save($salesOrderArray)) {
					echo "Problema al guardar la orden de venta";
					//pr($this->validateErrors($this->SalesOrder));
					throw new Exception();
				}
				$salesOrderId=$this->SalesOrder->id;
				
				foreach ($quotation['QuotationProduct'] as $quotationProduct){
					$salesOrderProductArray=[];
					$salesOrderProductArray['SalesOrderProduct']['sales_order_id']=$salesOrderId;	
					$salesOrderProductArray['SalesOrderProduct']['product_id']=$quotationProduct['product_id'];
					$salesOrderProductArray['SalesOrderProduct']['product_description']=$quotationProduct['product_description'];
					$salesOrderProductArray['SalesOrderProduct']['product_unit_price']=$quotationProduct['product_unit_price'];
					$salesOrderProductArray['SalesOrderProduct']['product_quantity']=$quotationProduct['product_quantity'];
					$salesOrderProductArray['SalesOrderProduct']['product_total_price']=$quotationProduct['product_total_price'];
					$salesOrderProductArray['SalesOrderProduct']['currency_id']=$quotationProduct['currency_id'];
					$salesOrderProductArray['SalesOrderProduct']['sales_order_product_status_id']=PRODUCT_STATUS_REGISTERED;
					$this->SalesOrder->SalesOrderProduct->create();
					if (!$this->SalesOrder->SalesOrderProduct->save($salesOrderProductArray)) {
						echo "Problema al guardar los productos para la orden de venta";
						//pr($this->validateErrors($this->SalesOrder->SalesOrderProduct));
						throw new Exception();
					}
				}
				
				$datasource->commit();
						
				$this->recordUserActivity($this->Session->read('User.username'),"Se generó automáticamente la orden de venta número ".$quotation['Quotation']['quotation_code']);
				$this->Session->setFlash(__('Se generó la orden de venta.'),'default',['class' => 'success']);				
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía generar la cotización.'), 'default',['class' => 'error-message']);
			}
			return $this->redirect(['action' => 'resumen']);
		}
		
	}

}
