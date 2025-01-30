<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class DeliveriesController extends AppController {

	public $components = ['Paginator','RequestHandler'];
	public $helpers = ['PhpExcel'];

  public function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow('getDeliveryCode');		
	}

  public function getDeliveryCode(){
    $this->autoRender = false; 
		$this->request->onlyAllow('ajax'); 
		$this->layout = "ajax";
		$warehouseId=trim($_POST['warehouseId']);
    
    $this->loadModel('Warehouse');
    $warehouseSeries=$this->Warehouse->getWarehouseSeries($warehouseId);
    
    return $this->Delivery->getDeliveryCode($warehouseId,$warehouseSeries);
  }
  
  public function resumen() {
		$this->Delivery->recursive = -1;
		$this->LoadModel('SalesOrder');
    $this->loadModel('Order');
    
    $this->LoadModel('DeliveryStatus');
    $this->loadModel('Vehicle');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('Product');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    //echo "user id in session is ".$_SESSION['userId']."<br/>";
		$loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    
    
    $canSeeExecutiveSummary=$this->UserPageRight->hasUserPageRight('VER_RESUMEN_EJECUTIVO',$userRoleId,$loggedUserId,'Deliveries','resumen');
    $this->set(compact('canSeeExecutiveSummary'));
        
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'Deliveries','resumen');
    $this->set(compact('canSeeAllUsers'));
    
    $deliveryStatusId=0;
    $vehicleId=0;
    $driverUserId=0;
    $warehouseId=0;
    
    if ($userRoleId != ROLE_ADMIN && !$canSeeAllUsers){
      $driverUserId=$loggedUserId;
    }
    
    if ($this->request->is('post')) {
      //pr($this->request->data);
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $deliveryStatusId=$this->request->data['Report']['delivery_status_id'];
      $vehicleId=$this->request->data['Report']['vehicle_id'];
      $driverUserId=$this->request->data['Report']['driver_user_id'];
      //$warehouseId=$this->request->data['Report']['warehouse_id'];
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
    
		$this->set(compact('startDate','endDate'));
		$this->set(compact('deliveryStatusId'));
    $this->set(compact('vehicleId'));
    $this->set(compact('driverUserId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    /*
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
    */
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
    
    $plantId=$this->Warehouse->getPlantId($warehouseId);
    $this->set(compact('plantId'));
    
    $conditions=[];
		if ($vehicleId>0){
      $conditions['Delivery.vehicle_id']=$vehicleId;
    }
    if ($driverUserId>0){
      $conditions['Delivery.driver_user_id']=$driverUserId;
    }
    if ($warehouseId > 0){
      $conditions['Delivery.warehouse_id']=$warehouseId;
    }
    //pr($conditions);
    
    $selectedDeliveryStatuses=$this->DeliveryStatus->getDeliveryStatusList($deliveryStatusId);
    //$this->set(compact('selectedDeliveryStatuses'));
    //pr($selectedDeliveryStatuses);
    $deliveriesByStatus=[];
    foreach ($selectedDeliveryStatuses as $currentDeliveryStatusId=>$currentDeliveryStatusName){
      if ($currentDeliveryStatusId == DELIVERY_STATUS_UNASSIGNED){
        // selection by date, vehicle, driver or warehouse does not make sense
        
        // first get all sales orders with bool_delivery true and no invoice
        $salesOrderConditions=[
          'SalesOrder.bool_delivery'=>true,
          'SalesOrder.delivery_id'=>0,
          'SalesOrder.bool_invoice'=>'0',
        ];
        $salesOrders=$this->SalesOrder->find('all',[
          'conditions'=>$salesOrderConditions,
          'contain'=>['SalesOrderProduct'],
          'order'=>['SalesOrder.sales_order_date'=>'ASC'],
        ]);
        $deliveriesByStatus[DELIVERY_STATUS_UNASSIGNED]['SalesOrder']=$salesOrders;
        $orderConditions=[
          'Order.bool_delivery'=>true,
          'Order.delivery_id'=>0,
          'Order.stock_movement_type_id'=>MOVEMENT_SALE,
        ];
        $orders=$this->Order->find('all',[
          'conditions'=>$orderConditions,
          'contain'=>[
            'Invoice'=>[
              'SalesOrder',
            ],
            'StockMovement',
          ],
          'order'=>['Order.order_date'=>'ASC'],
        ]);
        $deliveriesByStatus[DELIVERY_STATUS_UNASSIGNED]['Order']=$orders;        
      }
      else {
        $deliveryConditions=$conditions;
        $deliveryConditions['Delivery.delivery_status_id']=$currentDeliveryStatusId;
        $deliveryOrder='';
        switch ($currentDeliveryStatusId){
          case DELIVERY_STATUS_PROGRAMMED:
            $deliveryOrder=['Delivery.planned_delivery_datetime'=>'ASC'];
            break;
          case DELIVERY_STATUS_DELIVERED:
            $deliveryConditions['Delivery.actual_delivery_datetime >=']=$startDate;
            $deliveryConditions['Delivery.actual_delivery_datetime <']=$endDatePlusOne;
            $deliveryOrder=['Delivery.actual_delivery_datetime'=>'DESC'];
            break;
          default:  
        }  
        
        //pr($deliveryConditions);
        $deliveryCount=	$this->Delivery->find('count', [
          'fields'=>['Delivery.id'],
          'conditions' => $deliveryConditions,
        ]);
        $deliveries = $this->Delivery->find('all',[
          'conditions' => $deliveryConditions,
          'contain'=>[
            'SalesOrder',
            'Order',
            'DriverUser',
            'Vehicle',
          ],
          'order'=>$deliveryOrder,
          //'limit'=>($deliveryCount!=0?$deliveryCount:1),
        ]);
        $deliveriesByStatus[$currentDeliveryStatusId]['Delivery']=$deliveries;    
      }
    }  
    //pr($deliveriesByStatus);
		$this->set(compact('deliveriesByStatus'));
    
    $deliveryStatuses=$this->DeliveryStatus->getDeliveryStatusList();
    $this->set(compact('deliveryStatuses'));
    
    $driverUsers=$this->User->getActiveUsersForRole(ROLE_DRIVER,$warehouseId);
    $vehicles=$this->Vehicle->getVehicleList($warehouseId);
    $this->set(compact('registeringUsers', 'salesOrders', 'orders', 'deliveryStatuses', 'driverUsers', 'vehicles'));
    
    $workingDays=0;
    $periodStartDate = new DateTime($startDate);    //intialize start date
    $periodEndDate = new DateTime($endDatePlusOne);    //initialize end date

    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($periodStartDate, $interval ,$periodEndDate);
    $holiday=[];
    foreach($dateRange as $date){
      //pr($date->format("N"));
      if($date->format("N") < 7 AND !in_array($date-> format("Y-m-d"),$holiday)){
        //$result[] = $date->format("Y-m-d");
        $workingDays+=1;
      }
    }
    if ($workingDays ==0){
      $workingDays=1;
    }
    $this->set(compact('workingDays'));
    
    // for executive summary
    $bottleProductIds=array_keys($this->Product->getProductListForProductNatures([PRODUCT_NATURE_PRODUCED,PRODUCT_NATURE_BOTTLES_BOUGHT]));
    //pr($bottleProductIds);
    
    $allDeliveredDeliveriesConditions=[
      'Delivery.delivery_status_id'=>DELIVERY_STATUS_DELIVERED,
      'Delivery.actual_delivery_datetime >='=>$startDate,
      'Delivery.actual_delivery_datetime <'=>$endDatePlusOne,
    ];        
    $allDeliveredDeliveries= $this->Delivery->find('all',[
      'conditions' => $allDeliveredDeliveriesConditions,
      'contain'=>[
        'Order'=>[
          'StockMovement'=>[
            'fields'=>['StockMovement.product_id','StockMovement.product_quantity'],
            'conditions'=>[
              'StockMovement.product_id'=>$bottleProductIds,
            ],
          ],
        ],
      ],
    ]);
    // 0 is for totals
    $deliveriesByDriverUser=$deliveriesByVehicle=[
      0=>['quantity_products'=>0],
    ];
    //pr($allDeliveredDeliveries);
    foreach ($allDeliveredDeliveries as $deliveredDelivery){
      //pr($deliveredDelivery);
      if (!array_key_exists($deliveredDelivery['Delivery']['driver_user_id'],$deliveriesByDriverUser)){
        $deliveriesByDriverUser[$deliveredDelivery['Delivery']['driver_user_id']]=['quantity_products'=>0];
      }
      if (!array_key_exists($deliveredDelivery['Delivery']['vehicle_id'],$deliveriesByVehicle)){
        $deliveriesByVehicle[$deliveredDelivery['Delivery']['vehicle_id']]=['quantity_products'=>0];
      }
      if (!empty($deliveredDelivery['Order']['StockMovement'])){
        foreach ($deliveredDelivery['Order']['StockMovement'] as $stockMovement){
          //pr($stockMovement);
          $deliveriesByDriverUser[$deliveredDelivery['Delivery']['driver_user_id']]['quantity_products']+=$stockMovement['product_quantity'];
          $deliveriesByDriverUser[0]['quantity_products']+=$stockMovement['product_quantity'];
          
          $deliveriesByVehicle[$deliveredDelivery['Delivery']['vehicle_id']]['quantity_products']+=$stockMovement['product_quantity'];
          $deliveriesByVehicle[0]['quantity_products']+=$stockMovement['product_quantity'];
        }
      }
    }
    $this->set(compact('deliveriesByDriverUser','deliveriesByVehicle'));
    
    $aco_name="Deliveries/registrarEntregaAlCliente";		
		$boolCanRegisterDelivery=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('boolCanRegisterDelivery'));
    
    $aco_name="SalesOrders/detalle";		
		$boolSalesOrderDetail=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('boolSalesOrderDetail'));
    
    $aco_name="Orders/verVenta";		
		$boolOrderDetail=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('boolOrderDetail'));
    
    $aco_name="Vehicles/detalle";		
		$boolVehicleDetail=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('boolVehicleDetail'));
	}

  public function guardarResumen() {
		$exportData=$_SESSION['resumenEntregasADomicilio'];
		$this->set(compact('exportData'));
	}

	public function detalle($id = null) {
		if (!$this->Delivery->exists($id)) {
			throw new NotFoundException(__('Invalid delivery'));
		}
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
		
		$options = [
      'conditions' => [
        'Delivery.id' => $id,
      ],
      'contain'=>[
        'RegisteringUser',
        'SalesOrder',
        'Order',
        'DeliveryStatus',
        'DriverUser',
        'Vehicle',
        'DeliveryRemark'=>[
          'RegisteringUser',
        ]
      ],
    ];
		$this->set('delivery', $this->Delivery->find('first', $options));
    
    $aco_name="Deliveries/registrarEntregaAlCliente";		
		$boolCanRegisterDelivery=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('boolCanRegisterDelivery'));
    
    $aco_name="SalesOrders/detalle";		
		$boolSalesOrderDetail=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('boolSalesOrderDetail'));
    
    $aco_name="Orders/verVenta";		
		$boolOrderDetail=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('boolOrderDetail'));
    
    $aco_name="Vehicles/detalle";		
		$boolVehicleDetail=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('boolVehicleDetail'));
	}

	public function crear($salesOrderId=0,$orderId=0) {
    $this->loadModel('DeliveryRemark');
    $this->loadModel('DeliveryStatus');
    
    $this->loadModel('SalesOrder');
    $this->loadModel('Order');
    $this->loadModel('Invoice');
    
    $this->loadModel('ThirdParty');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('Vehicle');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $clientId=0;
    $clientName="";
    $clientPhone="";
    $deliveryAddress="";
    
    if ($salesOrderId > 0){
      $salesOrder=$this->SalesOrder->getSalesOrder($salesOrderId);
      $this->set(compact('salesOrder'));
      
      $clientId=$salesOrder['SalesOrder']['client_id'];
      
      $clientName=$salesOrder['SalesOrder']['client_name'];
      $clientPhone=$salesOrder['SalesOrder']['client_phone'];     
      $deliveryAddress=$salesOrder['SalesOrder']['delivery_address'];
      
      if ($orderId == 0){
        $invoicesForSalesOrder=$this->Invoice->find('list',[
          'fields'=>['Invoice.order_id'],
          'conditions'=>[
            'Invoice.sales_order_id'=>$salesOrderId
          ],
        ]);
        if (!empty($invoicesForSalesOrder)){
          //pr($invoicesForSalesOrder);
          $orderId=$invoicesForSalesOrder[array_keys($invoicesForSalesOrder)[0]];
        }
      }
    }
    //echo 'orderId is '.$orderId.'<br/>';
    //echo 'deliveryAddress is '.$deliveryAddress.'<br/>';
    if ($orderId > 0){
      $order=$this->Order->getOrder($orderId);
      $this->set(compact('order'));
      
      $clientId=$order['Order']['third_party_id'];
      
      $clientName=$order['Order']['client_name'];
      $clientPhone=$order['Order']['client_phone']; 
      if (!empty($order['Order']['delivery_address'])){
        $deliveryAddress=$order['Order']['delivery_address'];      
      }
    }
    //echo 'deliveryAddress is '.$deliveryAddress.'<br/>';
    $registeringUserId=$loggedUserId;
    $warehouseId=0;
    
    $clients = $this->ThirdParty->getActiveClientList();
    $this->set(compact('clients'));
    if (empty($clientName) && $clientId > 0){
      $clientName=$clients[$clientId];
    }
    if (empty($clientPhone && $clientId > 0)){
      $clientPhone=$this->ThirdParty->getClientPhone[$clientId];
    }
    
    $this->set(compact('clientId','clientName','clientPhone','registeringUserId'));
    
		if ($this->request->is('post')) {
      $plannedDeliveryDateTimeArray=$this->request->data['Delivery']['planned_delivery_datetime'];
      //pr($plannedDeliveryDateTimeArray);
      $plannedDeliveryDateTimeString=$plannedDeliveryDateTimeArray['year'].'-'.$plannedDeliveryDateTimeArray['month'].'-'.$plannedDeliveryDateTimeArray['day'];
      $plannedDeliveryDateTime=date( "Y-m-d H:i", strtotime($plannedDeliveryDateTimeString));

      $warehouseId=$this->request->data['Delivery']['warehouse_id'];
      $deliveryAddress=$this->request->data['Delivery']['delivery_address'];
    }

    if ($this->request->is('post') && empty($this->request->data['updateWarehouse'])) {
      if (empty($this->request->data['Delivery']['client_id'])){
        $this->Session->setFlash('Se debe especificar el cliente.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['client_name'])){
        $this->Session->setFlash('Se debe especificar el nombre del cliente.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['client_phone'])){
        $this->Session->setFlash('Se debe especificar el teléfono del cliente.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['delivery_address'])){
        $this->Session->setFlash('Se debe registrar la dirección de entrega.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['driver_user_id'])){
        $this->Session->setFlash('Se debe registrar el conductor.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['vehicle_id'])){
        $this->Session->setFlash('Se debe registrar el vehiculo.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      else {
        $datasource=$this->Delivery->getDataSource();
        $datasource->begin();
        try {
          
          $deliveryArray=$this->request->data['Delivery'];
          $deliveryArray['registering_user_id']=$registeringUserId;
          $deliveryArray['sales_order_id']=$salesOrderId;
          $deliveryArray['order_id']=$orderId;
          // pr($deliveryArray);
          
          $this->Delivery->create();
          if (!$this->Delivery->save($deliveryArray)) {
            echo "Problema guardando la orden de entrega";
            pr($this->validateErrors($this->Delivery));
            throw new Exception();
          } 
          $deliveryId=$this->Delivery->id;
          
          if ($salesOrderId > 0){
            $salesOrderArray=[
              'SalesOrder'=>[
                'id'=>$salesOrderId,
                'delivery_id'=>$deliveryId,
              ]
            ];
            $this->SalesOrder->id=$salesOrderId;
            if (!$this->SalesOrder->save($salesOrderArray)) {
              echo "Problema actualizando la orden de venta";
              pr($this->validateErrors($this->SalesOrder));
              throw new Exception();
            } 
          }
          if ($orderId > 0){
            $orderArray=[
              'Order'=>[
                'id'=>$orderId,
                'delivery_id'=>$deliveryId,
              ]
            ];
            $this->Order->id=$orderId;
            if (!$this->Order->save($orderArray)) {
              echo "Problema actualizando la factura";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            } 
          }
        
          if (!empty($this->request->data['Delivery']['remark'])){
            $deliveryRemarkArray=[
              'DeliveryRemark'=>[
                'delivery_id'=>$deliveryId,
                'registering_user_id'=>$registeringUserId,
                'remark_datetime'=>date('Y-m-d H:i'),
                'remark_text'=>$this->request->data['Delivery']['remark'],
              ]
            ];
            // pr($deliveryRemarkArray);
            $this->DeliveryRemark->create();
            if (!$this->DeliveryRemark->save($deliveryRemarkArray)) {
              echo "Problema grabando la observación para la orden de entrega";
              pr($this->validateErrors($this->DeliveryRemark));
              throw new Exception();
            }
          }
        
          $datasource->commit();
          $this->recordUserAction($this->Delivery->id,null,null);
          $this->recordUserActivity($clientName,"Se registró la entrega número ".$this->request->data['Delivery']['delivery_code']);
          
          $flashMessage='Se guardó la orden de entrega.  ';
          
          $this->Session->setFlash($flashMessage,'default',['class' => 'success']);
          return $this->redirect(['action' => 'detalle',$deliveryId]);
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('No se guardó la orden de entrega.'), 'default',['class' => 'error-message']);
        }
      }
		}
    $this->set(compact('salesOrderId','orderId'));
    $this->set(compact('deliveryAddress'));
    
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
    $plantId=$this->Warehouse->getPlantId($warehouseId);
    $this->set(compact('plantId'));
    
		$registeringUsers = $this->Delivery->RegisteringUser->find('list');
		$salesOrders = $this->Delivery->SalesOrder->getPendingSalesOrdersWithDelivery($warehouseId,$salesOrderId);
		$orders = $this->Delivery->Order->getPendingOrdersWithDelivery($warehouseId,$orderId);
    $deliveryStatuses=$this->DeliveryStatus->getEffectiveDeliveryStatusList();
    
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>['ThirdParty.id'=>$clientId],
    ]);
		
    $driverUsers=$this->User->getActiveUsersForRole(ROLE_DRIVER,$warehouseId);
    $vehicles=$this->Vehicle->getVehicleList($warehouseId);
    $this->set(compact('registeringUsers', 'salesOrders', 'orders', 'deliveryStatuses', 'clients','driverUsers', 'vehicles'));
    
    $aco_name="Vehicles/detalle";		
		$boolVehicleDetail=$this->hasPermission($loggedUserId,$aco_name);
		$this->set(compact('boolVehicleDetail'));
	}

	public function editar($id = null) {
		if (!$this->Delivery->exists($id)) {
			throw new NotFoundException(__('Invalid delivery'));
		}
    $this->loadModel('DeliveryStatus');
    $this->loadModel('DeliveryRemark');
    
    $this->loadModel('SalesOrder');
    $this->loadModel('Order');
    
    $this->loadModel('ThirdParty');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('Vehicle');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $registeringUserId=$loggedUserId;
    
    $clientId=0;
    $clientName="";
    $clientPhone="";
    $deliveryAddress="";
    $warehouseId=0;
    
    $delivery=$this->Delivery->getDeliveryById($id);
    $salesOrderId=$delivery['Delivery']['sales_order_id'];
    $orderId=$delivery['Delivery']['order_id'];
    $clientId=$delivery['Delivery']['client_id'];
    $clientName=$delivery['Delivery']['client_name'];
    $clientPhone=$delivery['Delivery']['client_phone'];
   
    $clients = $this->ThirdParty->getActiveClientList();
    $this->set(compact('clients'));
		if ($this->request->is(['post', 'put'])) {
      $plannedDeliveryDateTimeArray=$this->request->data['Delivery']['planned_delivery_datetime'];
      //pr($plannedDeliveryDateTimeArray);
      $plannedDeliveryDateTimeString=$plannedDeliveryDateTimeArray['year'].'-'.$plannedDeliveryDateTimeArray['month'].'-'.$plannedDeliveryDateTimeArray['day'];
      $plannedDeliveryDateTime=date( "Y-m-d H:i", strtotime($plannedDeliveryDateTimeString));

      $actualDeliveryDateTimeArray=$this->request->data['Delivery']['actual_delivery_datetime'];
      //pr($plannedDeliveryDateTimeArray);
      $actualDeliveryDateTimeString=$actualDeliveryDateTimeArray['year'].'-'.$actualDeliveryDateTimeArray['month'].'-'.$actualDeliveryDateTimeArray['day'].' '.$actualDeliveryDateTimeArray['hour'].':'.$actualDeliveryDateTimeArray['min'];
      $actualDeliveryDateTime=date( "Y-m-d H:i", strtotime($actualDeliveryDateTimeString));
    
      //$warehouseId=$this->request->data['Delivery']['warehouse_id'];
      $warehouseId=0;
      
      $deliveryAddress=$this->request->data['Delivery']['delivery_address'];
      
      if (empty($this->request->data['Delivery']['client_id'])){
        $this->Session->setFlash('Se debe especificar el cliente.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['client_name'])){
        $this->Session->setFlash('Se debe especificar el nombre del cliente.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['client_phone'])){
        $this->Session->setFlash('Se debe especificar el teléfono del cliente.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Delivery']['delivery_status_id'] == DELIVERY_STATUS_DELIVERED && $actualDeliveryDateTimeString > date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de la entrega no puede estar en el futuro!  No se guardó la entrega.'), 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['Delivery']['delivery_status_id'] == DELIVERY_STATUS_DELIVERED &&$actualDeliveryDateTimeString < $plannedDeliveryDateTimeString){
        $this->Session->setFlash(__('La fecha de entrega '.$actualDeliveryDateTime.' no puede venir antes de la fecha programada '.$plannedDeliveryDateTime.'!  No se guardó la entrega.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['delivery_address'])){
        $this->Session->setFlash('Se debe registrar la dirección de entrega.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['driver_user_id'])){
        $this->Session->setFlash('Se debe registrar el conductor.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['vehicle_id'])){
        $this->Session->setFlash('Se debe registrar el vehiculo.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      else {
        $datasource=$this->Delivery->getDataSource();
        $datasource->begin();
        try {      
          $deliveryArray=$this->request->data['Delivery'];
          $deliveryArray['registering_user_id']=$registeringUserId;
          $deliveryArray['sales_order_id']=$salesOrderId;
          $deliveryArray['order_id']=$orderId;
          // pr($deliveryArray);
          $this->Delivery->id=$id;
          if (!$this->Delivery->save($deliveryArray)) {
            echo "Problema editando la orden de entrega";
            pr($this->validateErrors($this->Delivery));
            throw new Exception();
          } 
          
          $deliveryId=$id;
        
          if ($salesOrderId > 0){
            $salesOrderArray=[
              'SalesOrder'=>[
                'id'=>$salesOrderId,
                'delivery_id'=>$deliveryId,
              ]
            ];
            $this->SalesOrder->id=$salesOrderId;
            if (!$this->SalesOrder->save($salesOrderArray)) {
              echo "Problema actualizando la orden de venta";
              pr($this->validateErrors($this->SalesOrder));
              throw new Exception();
            } 
          }
          if ($orderId > 0){
            $orderArray=[
              'Order'=>[
                'id'=>$orderId,
                'delivery_id'=>$deliveryId,
              ]
            ];
            $this->Order->id=$orderId;
            if (!$this->Order->save($orderArray)) {
              echo "Problema actualizando la factura";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            } 
          }
        
          if (!empty($this->request->data['Delivery']['remark'])){
            $deliveryRemarkArray=[
              'DeliveryRemark'=>[
                'delivery_id'=>$deliveryId,
                'registering_user_id'=>$registeringUserId,
                'remark_datetime'=>date('Y-m-d H:i'),
                'remark_text'=>$this->request->data['Delivery']['remark'],
              ]
            ];
            // pr($deliveryRemarkArray);
            $this->DeliveryRemark->create();
            if (!$this->DeliveryRemark->save($deliveryRemarkArray)) {
              echo "Problema grabando la observación para la orden de entrega";
              pr($this->validateErrors($this->DeliveryRemark));
              throw new Exception();
            }
          }
        
          $datasource->commit();
          $this->recordUserAction($this->Delivery->id,null,null);
          $this->recordUserActivity($clientName,"Se registró la entrega número ".$this->request->data['Delivery']['delivery_code']);
          
          $flashMessage='Se guardó la orden de entrega.  ';
          
          $this->Session->setFlash($flashMessage,'default',['class' => 'success']);
          return $this->redirect(['action' => 'detalle',$deliveryId]);
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('No se podía editar la orden de entrega.'), 'default',['class' => 'error-message']);
        }
      }
		} 
    else {
			$delivery=$this->request->data = $this->Delivery->find('first', [
        'conditions' => ['Delivery.id' => $id, ],
      ]);
      
      $clientId=$delivery['Delivery']['client_id'];
      $clientName=$delivery['Delivery']['client_name'];
      $clientPhone=$delivery['Delivery']['client_phone'];
      $deliveryAddress=$delivery['Delivery']['delivery_address'];
      $warehouseId=$delivery['Delivery']['warehouse_id'];
      
      $salesOrderId=$delivery['Delivery']['sales_order_id'];
      $orderId=$delivery['Delivery']['order_id'];
		}
    $this->set(compact('salesOrderId','orderId'));
    $this->set(compact('clientId','clientName','clientPhone'));
    
    
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
    $plantId=$this->Warehouse->getPlantId($warehouseId);
    $this->set(compact('plantId'));
    
		$salesOrders = $this->Delivery->SalesOrder->getPendingSalesOrdersWithDelivery($warehouseId, $salesOrderId);
		$orders = $this->Delivery->Order->getPendingOrdersWithDelivery($warehouseId, $orderId);
    $deliveryStatuses=$this->DeliveryStatus->getEffectiveDeliveryStatusList();
    
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>['ThirdParty.id'=>$clientId],
    ]);
		
    $driverUsers=$this->User->getActiveUsersForRole(ROLE_DRIVER,$warehouseId);
    $vehicles=$this->Vehicle->getVehicleList($warehouseId);
    $this->set(compact('registeringUsers', 'salesOrders', 'orders', 'deliveryStatuses', 'clients', 'driverUsers', 'vehicles'));
	}

  public function registrarEntregaAlCliente($id = null) {
		if (!$this->Delivery->exists($id)) {
			throw new NotFoundException(__('Invalid delivery'));
		}
    $this->loadModel('DeliveryStatus');
    $this->loadModel('DeliveryRemark');
    
    $this->loadModel('SalesOrder');
    $this->loadModel('Order');
    
    $this->loadModel('ThirdParty');
    
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $this->loadModel('Vehicle');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $registeringUserId=$loggedUserId;
    
    $clientId=0;
    $clientName="";
    $clientPhone="";
    $deliveryAddress="";
    $warehouseId=0;
    
    $delivery=$this->Delivery->getDeliveryById($id);
    $salesOrderId=$delivery['Delivery']['sales_order_id'];
    $orderId=$delivery['Delivery']['order_id'];
    $clientId=$delivery['Delivery']['client_id'];
    $clientName=$delivery['Delivery']['client_name'];
    $clientPhone=$delivery['Delivery']['client_phone'];
   
    $actualDeliveryDateTime=new DateTime(date("Y-m-d H:i"));
    $actualDeliveryDate=date("Y-m-d H:i");
    //pr($actualDeliveryDateTime);
    //pr($actualDeliveryDate);
   
    $clients = $this->ThirdParty->getActiveClientList();
    $this->set(compact('clients'));
		if ($this->request->is(['post', 'put'])) {
      $plannedDeliveryDateTimeArray=$this->request->data['Delivery']['planned_delivery_datetime'];
      //pr($plannedDeliveryDateTimeArray);
      $plannedDeliveryDateTimeString=$plannedDeliveryDateTimeArray['year'].'-'.$plannedDeliveryDateTimeArray['month'].'-'.$plannedDeliveryDateTimeArray['day'];
      $plannedDeliveryDateTime=date( "Y-m-d H:i", strtotime($plannedDeliveryDateTimeString));

      $actualDeliveryDateTimeArray=$this->request->data['Delivery']['actual_delivery_datetime'];
      //pr($plannedDeliveryDateTimeArray);
      $actualDeliveryDateTimeString=$actualDeliveryDateTimeArray['year'].'-'.$actualDeliveryDateTimeArray['month'].'-'.$actualDeliveryDateTimeArray['day'].' '.$actualDeliveryDateTimeArray['hour'].':'.$actualDeliveryDateTimeArray['min'];
      $actualDeliveryDateTime=date( "Y-m-d H:i", strtotime($actualDeliveryDateTimeString));
      
      $actualDeliveryDate=date( "Y-m-d H:i", strtotime($actualDeliveryDateTimeString));
      
      //$warehouseId=$this->request->data['Delivery']['warehouse_id'];
      $warehouseId=0;
      
      $deliveryAddress=$this->request->data['Delivery']['delivery_address'];
      
      if ($actualDeliveryDateTimeString > date('Y-m-d 23:59:59')){
        $this->Session->setFlash(__('La fecha de la entrega no puede estar en el futuro!  No se guardó la entrega.'), 'default',['class' => 'error-message']);
      }
      elseif ($actualDeliveryDateTimeString < $plannedDeliveryDateTimeString){
        $this->Session->setFlash(__('La fecha de entrega '.$actualDeliveryDateTime.' no puede venir antes de la fecha programada '.$plannedDeliveryDateTime.'!  No se guardó la entrega.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['delivery_address'])){
        $this->Session->setFlash('Se debe registrar la dirección de entrega.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['driver_user_id'])){
        $this->Session->setFlash('Se debe registrar el conductor.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Delivery']['vehicle_id'])){
        $this->Session->setFlash('Se debe registrar el vehiculo.  No se guardó la orden de entrega.', 'default',['class' => 'error-message']);
      }
      else {
        $datasource=$this->Delivery->getDataSource();
        $datasource->begin();
        try {      
          $deliveryArray=$this->request->data['Delivery'];
          $deliveryArray['registering_user_id']=$registeringUserId;
          $deliveryArray['sales_order_id']=$salesOrderId;
          $deliveryArray['order_id']=$orderId;
          // pr($deliveryArray);
          $this->Delivery->id=$id;
          if (!$this->Delivery->save($deliveryArray)) {
            echo "Problema editando la orden de entrega";
            pr($this->validateErrors($this->Delivery));
            throw new Exception();
          } 
          
          $deliveryId=$id;
        
          if ($salesOrderId > 0){
            $salesOrderArray=[
              'SalesOrder'=>[
                'id'=>$salesOrderId,
                'delivery_id'=>$deliveryId,
              ]
            ];
            $this->SalesOrder->id=$salesOrderId;
            if (!$this->SalesOrder->save($salesOrderArray)) {
              echo "Problema actualizando la orden de venta";
              pr($this->validateErrors($this->SalesOrder));
              throw new Exception();
            } 
          }
          if ($orderId > 0){
            $orderArray=[
              'Order'=>[
                'id'=>$orderId,
                'delivery_id'=>$deliveryId,
              ]
            ];
            $this->Order->id=$orderId;
            if (!$this->Order->save($orderArray)) {
              echo "Problema actualizando la factura";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            } 
          }
        
          if (!empty($this->request->data['Delivery']['remark'])){
            $deliveryRemarkArray=[
              'DeliveryRemark'=>[
                'delivery_id'=>$deliveryId,
                'registering_user_id'=>$registeringUserId,
                'remark_datetime'=>date('Y-m-d H:i'),
                'remark_text'=>$this->request->data['Delivery']['remark'],
              ]
            ];
            // pr($deliveryRemarkArray);
            $this->DeliveryRemark->create();
            if (!$this->DeliveryRemark->save($deliveryRemarkArray)) {
              echo "Problema grabando la observación para la orden de entrega";
              pr($this->validateErrors($this->DeliveryRemark));
              throw new Exception();
            }
          }
        
          $datasource->commit();
          $this->recordUserAction($this->Delivery->id,null,null);
          $this->recordUserActivity($clientName,"Se registró la entrega número ".$this->request->data['Delivery']['delivery_code']);
          
          $flashMessage='Se guardó la orden de entrega.  ';
          
          $this->Session->setFlash($flashMessage,'default',['class' => 'success']);
          return $this->redirect(['action' => 'detalle',$deliveryId]);
        }
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('No se podía editar la orden de entrega.'), 'default',['class' => 'error-message']);
        }
      }
		} 
    else {
			$delivery=$this->request->data = $this->Delivery->find('first', [
        'conditions' => ['Delivery.id' => $id, ],
      ]);
      
      $deliveryAddress=$delivery['Delivery']['delivery_address'];
      $warehouseId=$delivery['Delivery']['warehouse_id'];
      
      $salesOrderId=$delivery['Delivery']['sales_order_id'];
      $orderId=$delivery['Delivery']['order_id'];
		}
    $this->set(compact('salesOrderId','orderId'));
    $this->set(compact('clientId','clientName','clientPhone'));
    $this->set(compact('actualDeliveryDateTime'));
    $this->set(compact('actualDeliveryDate'));
    //pr($actualDeliveryDateTime);
    
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
    $plantId=$this->Warehouse->getPlantId($warehouseId);
    $this->set(compact('plantId'));
    
		$salesOrders = $this->Delivery->SalesOrder->getPendingSalesOrdersWithDelivery($warehouseId, $salesOrderId);
		$orders = $this->Delivery->Order->getPendingOrdersWithDelivery($warehouseId, $orderId);
    $deliveryStatuses=$this->DeliveryStatus->getDeliveryStatusList();
    
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>['ThirdParty.id'=>$clientId],
    ]);
		
		
    $driverUsers=$this->User->getActiveUsersForRole(ROLE_DRIVER,$warehouseId);
    $vehicles=$this->Vehicle->getVehicleList($warehouseId);
    $this->set(compact('registeringUsers', 'salesOrders', 'orders', 'deliveryStatuses', 'clients', 'driverUsers', 'vehicles'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Delivery->id = $id;
		if (!$this->Delivery->exists()) {
			throw new NotFoundException(__('Invalid delivery'));
		}
		$this->request->allowMethod('post', 'delete');
    
    $delivery=$this->Delivery->find('first',[
      'conditions'=>['Delivery.id'=>$id],
      'contain'=>[
        'SalesOrder',
        'Order',
        'DeliveryRemark',
      ],
    ]);
    $datasource=$this->Delivery->getDataSource();
    $datasource->begin();
    try {   
      if (!empty($delivery['SalesOrder']['id'])){
        // 20211008 it is assumed that it is only the delivery itself that is removed, not the desirability of the delivery, this should be edited within the salesorder or invoice
        $salesOrderArray=[
          'SalesOrder'=>[
            'id'=>$delivery['SalesOrder']['id'],
            'delivery_id'=>0,
          ],
        ];
        $this->Delivery->SalesOrder->id=$delivery['SalesOrder']['id'];
        if (!$this->Delivery->SalesOrder->save($salesOrderArray)) {
          echo "Problema al remover la información de entrega de la orden de venta";
          pr($this->validateErrors($this->Delivery->SalesOrder));
          throw new Exception();
        }
      }
      if (!empty($delivery['Order']['id'])){
        // 20211008 it is assumed that it is only the delivery itself that is removed, not the desirability of the delivery, this should be edited within the salesorder or invoice
        $orderArray=[
          'Order'=>[
            'id'=>$delivery['Order']['id'],
            'delivery_id'=>0,
          ],
        ];
        $this->Delivery->Order->id=$delivery['Order']['id'];
        if (!$this->Delivery->Order->save($salesOrderArray)) {
          echo "Problema al remover la información de entrega de la factura";
          pr($this->validateErrors($this->Delivery->Order));
          throw new Exception();
        }
      }
      if (!empty($delivery['DeliveryRemark'])){
        foreach ($delivery['DeliveryRemark'] as $deliveryRemark){
          if (!$this->Delivery->DeliveryRemark->delete($deliveryRemark['id'])) {
            echo "Problema al eliminar la remarca de la entrega";
            pr($this->validateErrors($this->Delivery->DeliveryRemark));
            throw new Exception();
          } 
        }
      }  
      if (!$this->Delivery->delete($id)) {
        echo "Problema al eliminar la entrega";
        pr($this->validateErrors($this->Delivery));
        throw new Exception();
      }
      $datasource->commit();
      $this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la entrega número ".$delivery['Delivery']['delivery_code']);
              
      $this->Session->setFlash(__('Se eliminó la entrega.'),'default',['class' => 'success']);				
      return $this->redirect(['action' => 'resumen']);
    }
    catch(Exception $e){
      $datasource->rollback();
      pr($e);
      $this->Session->setFlash(__('No se podía eliminar la entrega.'), 'default',['class' => 'error-message']);
      return $this->redirect(['action' => 'detalle',$id]);
    }
	}
}
