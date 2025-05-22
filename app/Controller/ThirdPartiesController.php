<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ThirdPartiesController extends AppController {

	public $components = array('Paginator');
	public $helpers =['PhpExcel'];
  
  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('saveClient','saveexistingclient','getclientlist','getclientlistforclientname','getclientinfo','getcreditdays','getprovidercreditdays','getcreditstatus','getCreditBlock','guardarResumenClientes');		
	}
	
  public function saveClient() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$clientid=trim($_POST['clientid']);
		$boolnewclient=($_POST['boolnewclient']=="true");
		
		$clientname=trim($_POST['clientname']);
		$clientruc=trim($_POST['clientruc']);
		$clientaddress=trim($_POST['clientaddress']);
		$clientphone=trim($_POST['clientphone']);
		$clientcell=trim($_POST['clientcell']);
		//20170821 ADDED CHECK ON EXISTING CLIENTS
    $boolClientOK=true;
    if ($boolnewclient){
      $this->ThirdParty->recursive=-1;
      $existingClientsWithThisName=$this->ThirdParty->find('all',array(
        'fields'=>array('Client.id'),
        'conditions'=>array(
          'ThirdParty.name'=>$clientname,
        ),
      ));
      if (!empty($existingClientsWithThisName)){
        $boolClientOK='0';
      }
    }
    if (!$boolClientOK){
      return "Ya existe un cliente con el mismo nombre, seleccione de la lista";
    }
    else {
      $datasource=$this->ThirdParty->getDataSource();
      $datasource->begin();
      try {
        $currentDateTime=new DateTime();
        //pr($this->request->data);
        $clientArray=[
          'ThirdParty'=>[
            'name'=>$clientname,
            'ruc'=>$clientruc,
            'address'=>$clientaddress,
            'phone'=>$clientphone,
            'cell'=>$clientcell,
            'bool_active'=>true,
            'creator_user_id'=>$this->Auth->User('id'),
          ],
        ];
        if ($boolnewclient){
          $this->ThirdParty->create();
        }
        else {
          $this->ThirdParty->id=$clientid;
          //pr($clientid);
          if (!$this->ThirdParty->exists($clientid)) {
            throw new Exception(__('Cliente inválido'));
          }				
        }
        if (!$this->ThirdParty->save($clientArray)) {
          echo "Problema guardando el cliente";
          pr($this->validateErrors($this->ThirdParty));
          throw new Exception();
        }
        $clientId=$this->ThirdParty->id;

        $this->loadModel('ThirdPartyUser');
        $this->ThirdPartyUser->create();
        $clientUserData=[
          'ThirdPartyUser'=>[
            'third_party_id'=>$clientId,
            'user_id'=>$this->Auth->User('id'),
            'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
            'bool_assigned'=>true,
          ],
        ];
        if (!$this->ThirdPartyUser->save($clientUserData)) {
          echo "Problema guardando la asociación entre cliente y usuario";
          pr($this->validateErrors($this->ThirdPartyUser));
          throw new Exception();
        }
        
        $datasource->commit();
      
        $this->recordUserAction($this->ThirdParty->id,"add",null);
        $this->recordUserActivity($this->Session->read('User.username'),"Se registró el cliente ".$clientname);
        
        $this->Session->setFlash(__('The client has been saved.'),'default',array('class' => 'success'));
        //return $this->redirect(array('action' => 'index'));
        return true;
      } 
      catch(Exception $e){
        $datasource->rollback();
        //pr($e);
        $this->Session->setFlash(__('The client could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
        return false;
      }
    }
  }
	
  public function saveexistingclient() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$clientId=trim($_POST['clientId']);
		
    $clientFirstName=trim($_POST['clientFirstName']);
    $clientLastName=trim($_POST['clientLastName']);
		
    $clientPhone=trim($_POST['clientPhone']);
		$clientEmail=trim($_POST['clientEmail']);
    $clientRucNumber=trim($_POST['clientRucNumber']);
    
    $clientTypeId=trim($_POST['clientTypeId']);
    $clientZoneId=trim($_POST['clientZoneId']);
    
    $clientAddress=trim($_POST['clientAddress']);
    
		$datasource=$this->ThirdParty->getDataSource();
    $datasource->begin();
    
    try {
      $currentDateTime=new DateTime();
      //pr($this->request->data);
      $clientArray=[
        'ThirdParty'=>[
          'id'=>$clientId,      
          'first_name'=>$clientFirstName,
          'last_name'=>$clientLastName,
          'phone'=>$clientPhone,
          'email'=>$clientEmail,
          'ruc_number'=>$clientRucNumber,
          'client_type_id'=>$clientTypeId,
          'zone_id'=>$clientZoneId,
          'address'=>$clientAddress,
        ],
      ];
      $this->ThirdParty->id=$clientId;
        //pr($clientid);
      if (!$this->ThirdParty->exists($clientId)) {
        throw new Exception(__('Cliente inválido'));
      }				
      if (!$this->ThirdParty->save($clientArray)) {
        echo "Problema guardando el cliente";
        pr($this->validateErrors($this->ThirdParty));
        throw new Exception();
      }
      $datasource->commit();
    
      $this->recordUserAction($this->ThirdParty->id,"edit",null);
      $this->recordUserActivity($this->Session->read('User.username'),"Se editó el cliente con id ".$clientId);
      
      $this->Session->setFlash(__('The client has been saved.'),'default',['class' => 'success']);
      //return $this->redirect(array('action' => 'index'));
      return true;
    } 
    catch(Exception $e){
      $datasource->rollback();
      //pr($e);
      $this->Session->setFlash(__('No se podía guardar el cliente.'), 'default',['class' => 'error-message']);
      return false;
    }
  }
  
	public function getclientlist() {
		$this->layout = "ajax";
		
		$this->Client->recursive=-1;
		$clients=$this->Client->find('all',array(
			'fields'=>array('Client.id','Client.name','Client.bool_active'),
			'order'=>'Client.name',
		));
		//pr($clients);
		$this->set(compact('clients'));
	}
	
	public function getclientlistforclientname() {
		$this->layout = "ajax";
		
		$clientval=trim($_POST['clientval']);
		
		$this->Client->recursive=-1;
		$clients=$this->Client->find('all',array(
			'fields'=>array('Client.id','Client.name','Client.bool_active'),
			'conditions'=>array(
				'Client.name LIKE'=> "%$clientval%",
			),
			'order'=>'Client.name',
		));
		//pr($clients);
		$this->set(compact('clients'));
	}
	
	public function getclientinfo(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->autoRender = false; // We don't render a view in this example    
    
    $clientid=trim($_POST['clientid']);
		
		$this->ThirdParty->recursive=-1;
		$client=$this->ThirdParty->find('first',[
			'fields'=>[
        'ThirdParty.id',
        'ThirdParty.plant_id',
        'ThirdParty.company_name','ThirdParty.bool_generic',
        'ThirdParty.first_name','ThirdParty.last_name',
        'ThirdParty.email','ThirdParty.phone','ThirdParty.address','ThirdParty.ruc_number',
        'ThirdParty.client_type_id','ThirdParty.zone_id',
        'ThirdParty.credit_days',
      ],
			'conditions'=>['ThirdParty.id'=> $clientid],
			//'contain'=>array(
			//	'CreatingUser'=>array(
			//		'fields'=>'username',
			//	),
			//	'Quotation'=>array(
			//		'fields'=>array(
			//			'Quotation.quotation_code',
			//		),	
			//		'order'=>'Quotation.quotation_date DESC',
			//		'limit'=>5,
			//	),
			//),
		]);
    
    return json_encode($client);  
	}

	public function getcreditdays(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->autoRender='0';
		
		$clientId=trim($_POST['clientid']);
		
		if (!$clientId){
			throw new NotFoundException(__('Cliente no está presente'));
		}
		if (!$this->ThirdParty->exists($clientId)) {
			throw new NotFoundException(__('Cliente inválido'));
		}
		
		$creditperiod=$this->ThirdParty->getCreditDays($clientId);
		return $creditperiod;
	}
  
  public function getprovidercreditdays(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->autoRender='0';
		
		$providerId=trim($_POST['providerid']);
		
		if (!$providerId){
			throw new NotFoundException(__('Proveedor no está presente'));
		}
		if (!$this->ThirdParty->exists($providerId)) {
			throw new NotFoundException(__('Proveedor inválido'));
		}
		
		$creditperiod=$this->ThirdParty->getCreditDays($providerId);
		return $creditperiod;
	}
  
  public function getcreditstatus($clientId){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = 'ajax';
    $this->autoRender='0';
		
		if (!$clientId){
			throw new NotFoundException(__('Cliente no está presente'));
		}
		if (!$this->ThirdParty->exists($clientId)) {
			throw new NotFoundException(__('Cliente inválido'));
		}
		
    $clientCreditStatus=$this->ThirdParty->getClientCreditStatus($clientId);
		return json_encode($clientCreditStatus);
	}
  
  public function getCreditBlock(){
	  	$this->request->onlyAllow('ajax'); // No direct access via browser URL
    $this->layout = "ajax";// just in case to reduce the error message;
		 
	
    $this->loadModel('User');  
    $this->loadModel('UserPageRight');
    $userRoleId=$this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
     $loggedUserId=$this->Auth->User('id');
    $clientId=trim($_POST['clientId']);
    $boolCreditApplied=trim($_POST['boolCreditApplied']);
 
       $canApplyCredit=$this->UserPageRight->hasUserPageRight('AUTORIZACION_CREDITO',$userRoleId,$loggedUserId,'orders','crearVenta');
 
    if(isset($_POST['creditAuthorizationUserId'])) {
      $creditAuthorizationUserId=trim($_POST['creditAuthorizationUserId']);
    }
    else {
      $creditAuthorizationUserId=0;
    }
    
    if(isset($_POST['boolCreditAuthorized'])) {
      $boolCreditAuthorized=trim($_POST['boolCreditAuthorized']);
    }
    else { 
 
      if (($this->User->getUserRoleId($creditAuthorizationUserId) == ROLE_ADMIN)){
        $boolCreditAuthorized=1;  
      }
      else {
        $boolCreditAuthorized=0;
        $creditAuthorizationUserId=0;
      }
    }
    
    //if (!$clientId){
		//	throw new NotFoundException(__('Cliente no está presente'));
		//}
		//if (!$this->ThirdParty->exists($clientId)) {
		//	throw new NotFoundException(__('Cliente inválido'));
		//}
    $this->set(compact('canApplyCredit'));
    $this->set(compact('boolCreditApplied'));
    $this->set(compact('boolCreditAuthorized'));
    $this->set(compact('creditAuthorizationUserId'));
    
    $clientCreditStatus=$this->ThirdParty->getClientCreditStatus($clientId);
    $this->set(compact('clientCreditStatus'));
    
    $clientCreditDays=$this->ThirdParty->getCreditDays($clientId);
    $this->set(compact('clientCreditDays'));
    
    $this->loadModel('User');
    $creditAuthorizationUsers=$this->User->getUserList();
    $this->set(compact('creditAuthorizationUsers'));
	}
	
	public function resumenClientes() {
		$this->ThirdParty->recursive = -1;
		$this->loadModel('ThirdPartyUser');
		$this->loadModel('ExchangeRate');
		$this->loadModel('Order');
    
    $this->loadModel('ClientType');
    $this->loadModel('Zone');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    $this->loadModel('Warehouse');
    
    $this->loadModel('User');
    $this->loadModel('UserPageRight');
		
    $loggedUserId=$userId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'ThirdParties','All');
    $canSeeAllVendors=$this->UserPageRight->hasUserPageRight('VER_TODOS_VENDEDORES',$userRoleId,$loggedUserId,'ThirdParties','All');
    $canSeeFinanceData=$this->UserPageRight->hasUserPageRight('VER_DATOS_FINANCIEROS',$userRoleId,$loggedUserId,'ThirdParties','All');
    $this->set(compact('canSeeAllUsers','canSeeAllVendors','canSeeFinanceData'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
    $plantId=0;
    
    if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers) { 
			$userId=0;
		}
    
		$activeDisplayOptions=[
			'0'=>'Mostrar solamente clientes activos',
			'1'=>'Mostrar clientes activos y no activos',
			'2'=>'Mostrar clientes desactivados',
		];
		$this->set(compact('activeDisplayOptions'));
		$aggregateOptions=[
			'0'=>'No mostrar acumulados, ordenar por nombre cliente',
			'1'=>'Mostrar salidas, ordenado por salidas y cliente',
		];
		$this->set(compact('aggregateOptions'));
		
		define('SHOW_CLIENT_ACTIVE_YES','0');
		define('SHOW_CLIENT_ACTIVE_ALL','1');
		define('SHOW_CLIENT_ACTIVE_NO','2');
		
		define('AGGREGATES_NONE','0');
		define('AGGREGATES_ORDERS','1');
		
		$activeDisplayOptionId=SHOW_CLIENT_ACTIVE_YES;
    $clientTypeId=0;
    $zoneId=0;
		$searchTerm="";
		
    //if ($userRoleId == ROLE_ADMIN||$userRoleId == ROLE_ASSISTANT) { 
    if ($userRoleId == ROLE_ADMIN||$canSeeFinanceData) { 
			$aggregateOptionId=AGGREGATES_ORDERS;
		}
    else {
      $aggregateOptionId=AGGREGATES_NONE;
    }
    
    if ($this->request->is('post')) {
      $plantId=$this->request->data['Report']['plant_id'];
      
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$userId=$this->request->data['Report']['user_id'];
			
			$activeDisplayOptionId=$this->request->data['Report']['active_display_option_id'];
			$aggregateOptionId=$this->request->data['Report']['aggregate_option_id'];
      
      $clientTypeId=$this->request->data['Report']['client_type_id'];
      $zoneId=$this->request->data['Report']['zone_id'];
			
			$searchTerm=$this->request->data['Report']['searchterm'];
		}		
		else if (!empty($_SESSION['startDateClient']) && !empty($_SESSION['endDateClient'])){
			$startDate=$_SESSION['startDateClient'];
			$endDate=$_SESSION['endDateClient'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-01-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDateClient']=$startDate;
		$_SESSION['endDateClient']=$endDate;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('userId'));
		$this->set(compact('activeDisplayOptionId','aggregateOptionId'));
    $this->set(compact('clientTypeId'));
    $this->set(compact('zoneId'));
		$this->set(compact('searchTerm'));
    
    if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    /*
    elseif (count($plants) > 1 && $plantId == 0){
      if (!empty($_SESSION['plantId'])){
        $plantId = $_SESSION['plantId'];
      }
      else {
        $plantId=0;
      }
    }
    $_SESSION['plantId']=$plantId;
    */
    $this->set(compact('plantId'));
    //echo 'plantId is '.$plantId.'<br/>';
		$warehousesForPlant=$this->Warehouse->getWarehousesForPlantId($plantId);
    
		$clientConditions=['ThirdParty.bool_provider'=> false];
    
    //$clientConditions['ThirdParty.plant_id'] =($plantId  == 0?array_keys($plants):$plantId);
    if ($clientTypeId>0){
      $clientConditions['ThirdParty.client_type_id']=$clientTypeId;
    }
    if ($zoneId>0){
      $clientConditions['ThirdParty.zone_id']=$zoneId;
    }
    
    $clientUserConditions=[];
    /*
    if ($userRoleId!=ROLE_ADMIN && !$canSeeAllUsers && !$canSeeAllVendors) { 
      // in this case the user_id is set to the logged in user explicitly
      // the clients are limited to those that have at least at one time been associated with the user
    	$clientUserIds=$this->ThirdPartyUser->find('list',[
				'fields'=>['ThirdPartyUser.client_id'],
				'conditions'=>[
          'ThirdPartyUser.user_id'=>$this->Auth->User('id'),
          'ThirdPartyUser.bool_assigned'=>true,
        ],
			]);
		
			$clientConditions['ThirdParty.id']=$clientUserIds;
      $clientUserConditions['ThirdPartyUser.user_id']=$this->Auth->User('id');
		}
		else {
      // case of an admin or assistant // 20190806 or a gerente
			if ($userId>0){
				$clientUserIds=$this->ThirdPartyUser->find('list',[
					'fields'=>['ThirdPartyUser.client_id'],
					'conditions'=>[
            'ThirdPartyUser.user_id'=>$userId,
            'ThirdPartyUser.bool_assigned'=>true,
          ],
				]);
			
				$clientConditions['ThirdParty.id']=$clientUserIds;
        $clientUserConditions['ThirdPartyUser.user_id']=$userId;
			}
		}
    */
		if ($activeDisplayOptionId!=SHOW_CLIENT_ACTIVE_ALL){
			if ($activeDisplayOptionId==SHOW_CLIENT_ACTIVE_YES){
				$clientConditions['ThirdParty.bool_active']=true;
			}
			else {
				$clientConditions['ThirdParty.bool_active']='0';
			}
		}
		
		if (!empty($searchTerm)){
			$clientConditions['ThirdParty.company_name LIKE']='%'.$searchTerm.'%';
		}
    
    //pr($clientConditions);
		$clientCount=	$this->ThirdParty->find('count', [
			'fields'=>['ThirdParty.id'],
			'conditions' => $clientConditions,
		]);
    //pr($clientConditions);
		
		$this->Paginator->settings = [
			'conditions' => $clientConditions,
			'contain'=>[
				'AccountingCode',
        'ClientType',
        'ThirdPartyUser'=>[
          'conditions' => $clientUserConditions,  
					'User',
					'order'=>'ThirdPartyUser.assignment_datetime DESC,ThirdPartyUser.id DESC',
          'limit'=>1,
				],
				'Order'=>[
					'conditions'=>[
						'Order.order_date >='=>$startDate,
						'Order.order_date <'=>$endDatePlusOne,
            'Order.bool_annulled'=>'0',
					],
          'CashReceipt',
          'Invoice',
          'order'=>'Order.client_name ASC,Order.total_price DESC',
				],
        'Plant',
			],
			'order' => ['ThirdParty.company_name'=>'ASC'],
			'limit'=>($clientCount>0?$clientCount:1),
		];

		$allClients = $this->Paginator->paginate('ThirdParty');
    //pr($allClients);
    $clients=[];	
    $genericClients=[];
    $users=[];
		for ($c=0;$c<count($allClients);$c++){
      //20210614 FIX NEEDED ONLY SELECT CLIENTS ASSOCIATED WITH PLANTS FOR USER
      //20210614 FIX NEEDED ONLY SELECT CLIENTS ASSOCIATED WITH USER
			//TODO FIX NEEDED 20180603 NO SELECTION ON ASSIGNED YET
      //if (empty($userId)||$allClients[$c]['ThirdPartyUser'][0]['bool_assigned']){
        // 20180503 LATER A CHECK SHOULD BE ADDED TO ACCELERATE THE PAGE TO ONLY DO THE CALCULUS FOR THE CLIENTS THAT HAVE A RIGHT TO CREDIT
        $thisClient=$allClients[$c];
        if ($thisClient['ThirdParty']['bool_generic']){
          $genericClientId=$thisClient['ThirdParty']['id'];
          if (!array_key_exists($genericClientId,$genericClients)){
            $genericClients[$genericClientId]=[
              'GenericClient'=>$thisClient['ThirdParty'],
              'SpecificClient'=>[],
            ];
          }
          foreach ($thisClient['Order'] as $genericOrder){
            $specificClientName=trim($genericOrder['client_name']);
            if (!array_key_exists($specificClientName,$genericClients[$genericClientId]['SpecificClient'])){
              $genericClients[$genericClientId]['SpecificClient'][$specificClientName]=[
                'name'=>$specificClientName,
                'phone'=>$genericOrder['client_phone'],
                'email'=>$genericOrder['client_email'],
                'address'=>$genericOrder['client_address'],
                'ruc'=>$genericOrder['client_ruc'],
                'client_type_id'=>$genericOrder['total_price'],
                'zone_id'=>$genericOrder['zone_id'],
                'order_total'=>0,
                'quantity_orders'=>0,
                'Order'=>[],
              ];
            }    
            $genericClients[$genericClientId]['SpecificClient'][$specificClientName]['order_total']+=$genericOrder['total_price'];
            $genericClients[$genericClientId]['SpecificClient'][$specificClientName]['quantity_orders']+=1;
            //pr($genericOrder);
            $genericClients[$genericClientId]['SpecificClient'][$specificClientName]['Order'][$genericOrder['id']]=[
              'order_date'=>$genericOrder['order_date'],
              'order_code'=>$genericOrder['order_code'],
              'bool_invoice'=>empty($genericOrder['CashReceipt']['id']),
            ];
          }
        }
        else {
          $orderTotal=0;
          $thisClient['ThirdParty']['pending_payment']=$this->ThirdParty->getCurrentPendingPayment($thisClient['ThirdParty']['id']);
          
          for ($q=0;$q<count($thisClient['Order']);$q++){
            $orderTotal+=$thisClient['Order'][$q]['total_price'];
          }
          $thisClient['Client']['order_total']=$orderTotal;
          $clients[]=$thisClient;        
        }
      //}  
    }
    
    //pr($genericClients);
    
    switch ($aggregateOptionId){
      case AGGREGATES_NONE:
        usort($clients,[$this,'sortByCompanyName']);
        foreach ($genericClients as $genericClientId=>$genericClientData){
          usort($genericClients[$genericClientId]['SpecificClient'],[$this,'sortGenericByCompanyName']);  
        }
        break;
      case AGGREGATES_ORDERS:
        usort($clients,[$this,'sortByOrderTotalCompanyName']);
        foreach ($genericClients as $genericClientId=>$genericClientData){
          usort($genericClients[$genericClientId]['SpecificClient'],[$this,'sortGenericByOrderTotalCompanyName']);  
        }
        break;
    }
    $this->set(compact('clients'));
    $this->set(compact('genericClients'));
    
    if ($userRoleId == ROLE_ADMIN || $canSeeAllUsers){
      foreach ($warehousesForPlant as $warehouseId =>$warehouseName){
        $users=array_replace($users,$this->User->getActiveVendorAdminUserList($warehouseId));
      }
    }
    elseif ($canSeeAllVendors) {
      foreach ($warehousesForPlant as $warehouseId =>$warehouseName){
        $users=array_replace($users,$this->User->getActiveVendorOnlyUserList($warehouseId));
      }
    }
    else {
      $users=$this->User->getActiveUserList($loggedUserId);
    }
    //pr($users);
		$this->set(compact('users'));
    
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_client_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_edit_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
	}
  
  public function sortByCompanyName($a,$b ){ 
		if( $a['ThirdParty']['company_name'] == $b['ThirdParty']['company_name'] ){ 
			return 0 ; 
		} 
		return ($a['ThirdParty']['company_name'] < $b['ThirdParty']['company_name']) ? -1 : 1;
	}
	public function sortByOrderTotalCompanyName($a,$b ){ 
		if( $a['Client']['order_total'] == $b['Client']['order_total'] ){ 			
      if( $a['ThirdParty']['company_name'] == $b['ThirdParty']['company_name'] ){ 
        return 0 ; 
      }
      else {
        return ($a['ThirdParty']['company_name'] < $b['ThirdParty']['company_name']) ? -1 : 1;
      }
		} 
		return ($a['Client']['order_total'] < $b['Client']['order_total']) ? 1 : -1;
	}
	
  public function sortGenericByCompanyName($a,$b ){ 
		if( $a['name'] == $b['name'] ){ 
			return 0 ; 
		} 
		return ($a['name'] < $b['name']) ? -1 : 1;
	}
	public function sortGenericByOrderTotalCompanyName($a,$b ){ 
		if( $a['order_total'] == $b['order_total'] ){ 			
      if( $a['name'] == $b['name'] ){ 
        return 0 ; 
      }
      else {
        return ($a['name'] < $b['name']) ? -1 : 1;
      }
		} 
		return ($a['order_total'] < $b['order_total']) ? 1 : -1;
	}
	
  public function guardarResumenClientes() {
		$exportData=$_SESSION['resumenClientes'];
		$this->set(compact('exportData'));
	}
  
	public function resumenProveedores() {
		$this->ThirdParty->recursive = -1;
    $this->loadModel('User');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $this->loadModel('UserPageRight');
    
    $loggedUserId=$userId=$this->Auth->User('id');
    $this->set(compact('loggedUserId','userId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $canSeeAllUsers=$this->UserPageRight->hasUserPageRight('VER_TODOS_USUARIOS',$userRoleId,$loggedUserId,'ThirdParties','All');
    $canSeeFinanceData=$this->UserPageRight->hasUserPageRight('VER_DATOS_FINANCIEROS',$userRoleId,$loggedUserId,'ThirdParties','All');
    $this->set(compact('canSeeAllUsers','canSeeFinanceData'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
    $plantId=0;
    
    if ($userRoleId==ROLE_ADMIN || $canSeeAllUsers) { 
			$userId=0;
		}
    
		$activeDisplayOptions=[
			'0'=>'Mostrar solamente proveedores activos',
			'1'=>'Mostrar proveedores activos y no activos',
			'2'=>'Mostrar proveedores desactivados',
		];
		$this->set(compact('activeDisplayOptions'));
		$aggregateOptions=[
			'0'=>'No mostrar acumulados, ordenar por nombre proveedor',
			'1'=>'Mostrar ordenes de compra, ordenado por orden y proveedor',
		];
		$this->set(compact('aggregateOptions'));
		
		define('SHOW_PROVIDER_ACTIVE_YES','0');
		define('SHOW_PROVIDER_ACTIVE_ALL','1');
		define('SHOW_PROVIDE_ACTIVE_NO','2');
		
		define('AGGREGATES_NONE','0');
		define('AGGREGATES_ORDERS','1');
		
		$activeDisplayOptionId=SHOW_PROVIDER_ACTIVE_YES;
		$searchTerm="";
		
    //if ($userRoleId == ROLE_ADMIN||$userRoleId == ROLE_ASSISTANT) { 
    if ($userRoleId == ROLE_ADMIN||$canSeeFinanceData) { 
			$aggregateOptionId=AGGREGATES_ORDERS;
		}
    else {
      $aggregateOptionId=AGGREGATES_NONE;
    }
    
    if ($this->request->is('post')) {
      //pr($this->request->data);
      
			$plantId=$this->request->data['Report']['plant_id'];
      
      $startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			//$userId=$this->request->data['Report']['user_id'];
			
			$activeDisplayOptionId=$this->request->data['Report']['active_display_option_id'];
			$aggregateOptionId=$this->request->data['Report']['aggregate_option_id'];
			
			$searchTerm=$this->request->data['Report']['searchterm'];
		}		
		else if (!empty($_SESSION['startDateClient']) && !empty($_SESSION['endDateClient'])){
			$startDate=$_SESSION['startDateClient'];
			$endDate=$_SESSION['endDateClient'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-01-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDateClient']=$startDate;
		$_SESSION['endDateClient']=$endDate;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('userId'));
		$this->set(compact('activeDisplayOptionId','aggregateOptionId'));
		$this->set(compact('searchTerm'));
		
    if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    /*
    elseif (count($plants) > 1 && $plantId == 0){
      if (!empty($_SESSION['plantId'])){
        $plantId = $_SESSION['plantId'];
      }
      else {
        $plantId=0;
      }
    }
    $_SESSION['plantId']=$plantId;
    */
    $this->set(compact('plantId'));
    //echo 'plantId is '.$plantId.'<br/>';
    
		$providerConditions=['ThirdParty.bool_provider'=> true];
    $providerConditions['ThirdParty.plant_id'] =($plantId  == 0?array_keys($plants):$plantId);
    
    if ($activeDisplayOptionId!=SHOW_PROVIDER_ACTIVE_ALL){
			$providerConditions['ThirdParty.bool_active']=($activeDisplayOptionId==SHOW_PROVIDER_ACTIVE_YES?true:false);
		}
		
		if (!empty($searchTerm)){
			$providerConditions['ThirdParty.company_name LIKE']='%'.$searchTerm.'%';
		}
    
		$providerCount=	$this->ThirdParty->find('count', [
			'fields'=>['ThirdParty.id'],
			'conditions' => $providerConditions,
		]);
		$this->Paginator->settings = [
			'conditions' => $providerConditions,
			'contain'=>[
				'AccountingCode',
        'Plant',
        'ProviderNature',
        'PurchaseOrder'=>[
					'conditions'=>[
						'PurchaseOrder.purchase_order_date >='=>$startDate,
						'PurchaseOrder.purchase_order_date <'=>$endDatePlusOne,
            'PurchaseOrder.bool_annulled'=>'0',
					],
				],
			],
			'order' => 'ThirdParty.plant_id ASC, ThirdParty.company_name ASC',
			'limit'=>($providerCount>0?$providerCount:1),
		];
		$allProviders = $this->Paginator->paginate('ThirdParty');
    
    $providers=[];	
		for ($p=0;$p<count($allProviders);$p++){
			$orderTotal=0;
			//TODO FIX NEEDED 20180603 NO SELECTION ON ASSIGNED YET
      //if (empty($userId)||$allClients[$c]['ThirdPartyUser'][0]['bool_assigned']){
        // 20180503 LATER A CHECK SHOULD BE ADDED TO ACCELERATE THE PAGE TO ONLY DO THE CALCULUS FOR THE CLIENTS THAT HAVE A RIGHT TO CREDIT
        $thisProvider=$allProviders[$p];
        //$thisProvider['ThirdParty']['pending_payment']=$this->ThirdParty->getCurrentPendingPayment($thisProvider['ThirdParty']['id']);
        
        for ($q=0;$q<count($thisProvider['PurchaseOrder']);$q++){
          $orderTotal+=$thisProvider['PurchaseOrder'][$q]['cost_total'];
        }
        $thisProvider['ThirdParty']['purchase_order_total']=$orderTotal;
        $providers[]=$thisProvider;        
      //}  
    }
    
    switch ($aggregateOptionId){
      case AGGREGATES_NONE:
        usort($providers,[$this,'sortByCompanyName']);
        break;
      case AGGREGATES_ORDERS:
        usort($providers,[$this,'sortByPurchaseOrderTotalCompanyName']);
        break;
    }
    $this->set(compact('providers'));
		
		$userConditions=[];
		//if ($userRoleId != ROLE_ADMIN && $userRoleId!=ROLE_ASSISTANT){
    if ($userRoleId != ROLE_ADMIN && !$canSeeAllUsers){
			$userConditions=['User.id'=>$loggedUserId];
		}
		$users=$this->User->find('list',[
      'fields'=>'User.username',
			'conditions'=>$userConditions,
			'order'=>'User.username'
		]);
		$this->set(compact('users'));
    
		$aco_name="ThirdParties/crearProveedor";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarProveedor";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/resumenEntradas";		
		$bool_purchase_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_index_permission'));
		$aco_name="Orders/crearEntrada";		
		$bool_purchase_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_add_permission'));
    
    $aco_name="PurchaseOrders/resumen";		
		$bool_purchase_order_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_index_permission'));
		$aco_name="PurchaseOrders/crear";		
		$bool_purchase_order_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_add_permission'));
	}
	
  public function sortByPurchaseOrderTotalCompanyName($a,$b ){ 
		if( $a['ThirdParty']['purchase_order_total'] == $b['ThirdParty']['purchase_order_total'] ){ 			
      if( $a['ThirdParty']['company_name'] == $b['ThirdParty']['company_name'] ){ 
        return 0 ; 
      }
      else {
        return ($a['ThirdParty']['company_name'] < $b['ThirdParty']['company_name']) ? -1 : 1;
      }
		} 
		return ($a['ThirdParty']['purchase_order_total'] < $b['ThirdParty']['purchase_order_total']) ? 1 : -1;
	}
	
  public function guardarResumenProveedores() {
		$exportData=$_SESSION['resumenProveedores'];
		$this->set(compact('exportData'));
	}
  
	public function verCliente($id = null) {
		if (!$this->ThirdParty->exists($id)) {
			throw new NotFoundException(__('Invalid third party'));
		}
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDateClient']) && !empty($_SESSION['endDateClient'])){
			$startDate=$_SESSION['startDateClient'];
			$endDate=$_SESSION['endDateClient'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-01-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDateClient']=$startDate;
		$_SESSION['endDateClient']=$endDate;
		
		$client=$this->ThirdParty->find('first', [
			'conditions'=>[
				'ThirdParty.id'=>$id,
			],
			'contain'=>[
        'AccountingCode',
        'ClientType',
        'CreditCurrency',
        'ThirdPartyUser'=>[
          'order'=>'ThirdPartyUser.assignment_datetime DESC,ThirdPartyUser.id DESC',
					'User',
				],
        'PlantThirdParty'=>[
          'order'=>'PlantThirdParty.assignment_datetime DESC,PlantThirdParty.id DESC',
					'Plant',
				],
				'Order'=>[
					'conditions'=>[
						'Order.order_date >='=>$startDate,
						'Order.order_date <'=>$endDatePlusOne,
					],
					'order'=>'order_date DESC'
				],
        'Plant',
        'PriceClientCategory',
        'Zone',
			]
		]);
		//pr($client);
		$client['ThirdParty']['pending_payment']=$this->ThirdParty->getCurrentPendingPayment($id);
		$this->set(compact('client','startDate','endDate'));
		
    $userIdList=[];
    foreach ($client['ThirdPartyUser'] as $clientUser){
      if (!in_array($clientUser['user_id'],$userIdList)){
        $userIdList[]=$clientUser['user_id'];
      }
    }
    $this->loadModel('User');
    $uniqueUsers=$this->User->find('all',[
      'conditions'=>['User.id'=>$userIdList],
      'contain'=>[					
        'ThirdPartyUser'=>[
          'conditions'=>['ThirdPartyUser.third_party_id'=>$id],
          'order'=>'ThirdPartyUser.assignment_datetime DESC,ThirdPartyUser.id DESC',
        ]
  		],
      'order'=>'User.username'
    ]);
    $this->set(compact('uniqueUsers'));
    
    $plantIdList=[];
    foreach ($client['PlantThirdParty'] as $plantThirdParty){
      if (!in_array($plantThirdParty['plant_id'],$plantIdList)){
        $plantIdList[]=$plantThirdParty['plant_id'];
      }
    }
    
    $this->loadModel('Plant');
    $uniquePlants=$this->Plant->find('all',[
      'conditions'=>['Plant.id'=>$plantIdList],
      'contain'=>[					
        'PlantThirdParty'=>[
          'conditions'=>['PlantThirdParty.third_party_id'=>$id],
          'order'=>'PlantThirdParty.assignment_datetime DESC,PlantThirdParty.id DESC',
        ]
  		],
      'order'=>'Plant.name'
    ]);
    $this->set(compact('uniquePlants'));
		
		$aco_name="ThirdParties/crearCliente";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="ThirdParties/deleteClient";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
    
    $aco_name="Users/edit";		
		$bool_user_edit_permission=$this->hasPermission($this->Session->read('User.id'),$aco_name);
		$this->set(compact('bool_user_edit_permission'));
	}
	public function verProveedor($id = null) {
		if (!$this->ThirdParty->exists($id)) {
			throw new NotFoundException(__('Invalid third party'));
		}
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else{
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$provider=$this->ThirdParty->find('first', [
			'conditions'=>[
				'ThirdParty.id'=>$id,
			],
			'contain'=>[
				'Order'=>[
					'conditions'=>[
						'Order.order_date >='=>$startDate,
						'Order.order_date <'=>$endDatePlusOne,
					],
					'order'=>'order_date DESC'
				],
				'AccountingCode',
        'CreditCurrency',
        'Plant',
        'ProviderNature',
        'PurchaseOrder'=>[
					'conditions'=>[
						'PurchaseOrder.purchase_order_date >='=>$startDate,
						'PurchaseOrder.purchase_order_date <'=>$endDatePlusOne,
					],
					'order'=>'purchase_order_date DESC'
				],
			]
		]);
		//pr($provider);
		
		$this->set(compact('provider','startDate','endDate'));
		
		$aco_name="ThirdParties/crearProveedor";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarProveedor";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="ThirdParties/deleteProvider";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		
		$aco_name="Orders/resumenEntradas";		
		$bool_purchase_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_index_permission'));
		$aco_name="Orders/crearEntrada";		
		$bool_purchase_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_add_permission'));
    
    $aco_name="PurchaseOrders/resumen";		
		$bool_purchase_order_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_index_permission'));
		$aco_name="PurchaseOrders/crear";		
		$bool_purchase_order_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_add_permission'));
	}
	
	public function crearCliente() {
    $this->loadModel('AccountingCode');
		$this->loadModel('User');
    $this->loadModel('PriceClientCategory');
    $this->loadModel('Currency');
    
    $this->loadModel('ClientType');
    $this->loadModel('Zone');
    
    $this->loadModel('Plant');
    $this->loadModel('PlantThirdParty');
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
		$users = $this->User->getAllActiveVendorAdminUsersAllWarehouses();
		$this->set(compact('users'));
	
		$plants=$this->Plant->find('all',['recursive'=>-1,]);
    $this->set(compact('plants'));
    
		if ($this->request->is('post')) {
      $this->request->data['ThirdParty']['company_name']=trim(strtoupper($this->request->data['ThirdParty']['company_name']));
			$previousClientsWithThisName=[];
			$previousClientsWithThisName=$this->ThirdParty->find('all',[
				'conditions'=>['TRIM(UPPER(ThirdParty.company_name))'=>$this->request->data['ThirdParty']['company_name']],
			]);
			
			$allPreviousClients=$this->ThirdParty->find('all',[
				'fields'=>['ThirdParty.company_name'],
			]);
			
			$bool_similar='0';
			$similar_string="";
			foreach ($allPreviousClients as $existingClientName){
				similar_text($this->request->data['ThirdParty']['company_name'],$existingClientName['ThirdParty']['company_name'],$percent);
				if ($percent > 80){
					$bool_similar=true;
					$similar_string=$existingClientName['ThirdParty']['company_name'];
				}
			}
      
      $boolAssociationWithVendorPresent='0';
      $boolAssociationWithPlantPresent='0';
      if (!empty($this->request->data['User'])){
        for ($u=0;$u<count($this->request->data['User']);$u++){
          if ($this->request->data['User'][$u]['id']){
            $boolAssociationWithVendorPresent=true;
          }
        }
      }
      if (!empty($this->request->data['Plant'])){
        for ($p=0;$p<count($this->request->data['Plant']);$p++){
          if ($this->request->data['Plant'][$p]['id']){
            $boolAssociationWithPlantPresent=true;
          }
        }
      }
			
			if (count($previousClientsWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un cliente con este nombre!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			}
			//elseif ($bool_similar){
			//	$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un cliente existente: '.$similar_string.'!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			//}
      elseif (!$this->request->data['ThirdParty']['bool_generic'] && empty($this->request->data['ThirdParty']['client_type_id'])){
				$this->Session->setFlash('Se debe indicar el tipo de cliente!  No se guardó el cliente.', 'default',['class' => 'error-message']);
			}
      elseif (!$this->request->data['ThirdParty']['bool_generic'] && empty($this->request->data['ThirdParty']['zone_id'])){
				$this->Session->setFlash('Se debe indicar la zona!  No se guardó el cliente.', 'default',['class' => 'error-message']);
			}
      //else if (!$boolAssociationWithVendorPresent){
      //  $this->Session->setFlash('El cliente se debe asociar con por lo menos un vendedor!  No se guardó el cliente.', 'default',['class' => 'error-message']);
      //}
      else if (!$boolAssociationWithPlantPresent){
        $this->Session->setFlash('El cliente se debe asociar con por lo menos una planta!  No se guardó el cliente.', 'default',['class' => 'error-message']);
      }
			else {	
        $datasource=$this->ThirdParty->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
          $accountingCodeArray=[
            'code'=>$this->request->data['ThirdParty']['accounting_code_id'],
            'description'=>$this->request->data['ThirdParty']['company_name'],
            'parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
            'bool_main'=>'0',
            'bool_creditor'=>'0',
          ];
          $this->AccountingCode->create();
          if (!$this->AccountingCode->save($accountingCodeArray)) {
            $this->Session->setFlash(__('No se podía guardar la cuenta contable para el cliente nuevo.'), 'default',['class' => 'error-message']);
            pr($this->AccountingCode->validateErrors($this->ThirdParty));
            throw new Exception();
          }
          
          $this->request->data['ThirdParty']['accounting_code_id']=$this->AccountingCode->id;
          $this->request->data['ThirdParty']['bool_provider']='0';
          $this->ThirdParty->create();
          if (!$this->ThirdParty->save($this->request->data)) {
            echo "Problema guardando el cliente";
            pr($this->validateErrors($this->ThirdParty));
            throw new Exception();
          }
          $clientId=$this->ThirdParty->id;
          
          $currentDateTime=new DateTime();
          if (!empty($this->request->data['User'])){
            for ($u=0;$u<count($this->request->data['User']);$u++){
              $this->ThirdParty->ThirdPartyUser->create();
              $clientUserArray=[
                'third_party_id'=>$clientId,
                'user_id'=>$users[$u]['User']['id'],
                'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                'bool_assigned'=>$this->request->data['User'][$u]['id'],
              ];
              if (!$this->ThirdParty->ThirdPartyUser->save($clientUserArray)){
                echo "Problema guardando el vendedor para el cliente";
                pr($this->validateErrors($this->ThirdParty->ThirdPartyUser));
                throw new Exception();
              }							
            }
          }
          if (!empty($this->request->data['Plant'])){
            for ($u=0;$u<count($this->request->data['Plant']);$u++){
              $this->ThirdParty->ThirdPartyUser->create();
              $clientUserArray=[
                'third_party_id'=>$clientId,
                'user_id'=>$users[$u]['User']['id'],
                'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                'bool_assigned'=>$this->request->data['User'][$u]['id'],
              ];
              if (!$this->ThirdParty->ThirdPartyUser->save($clientUserArray)){
                echo "Problema guardando el vendedor para el cliente";
                pr($this->validateErrors($this->ThirdParty->ThirdPartyUser));
                throw new Exception();
              }							
            }
          }
        
          
          $datasource->commit();
          $this->recordUserAction($this->ThirdParty->id,null,null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se registró el cliente ".$this->request->data['ThirdParty']['company_name']);
            
          $this->Session->setFlash(__('Se guardó el cliente.'),'default',['class' => 'success']);
          
          if (!empty($this->request->data['guardarCliente'])){
            return $this->redirect(['action' => 'resumenClientes']);  
          }
          elseif (!empty($this->request->data['guardarClienteYPrecios'])){
            $aco_name="ProductPriceLogs/registrarPreciosCliente";		
            $boolRegistrarPreciosClientePermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
            if ($boolRegistrarPreciosClientePermission){
              return $this->redirect(['controller'=>'ProductPriceLogs','action' => 'registrarPreciosCliente',$clientId]);  
            }
            else {
              return $this->redirect(['action' => 'index']);  
            }
          }          
        }      
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar el cliente. Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
				}
			}  
		}
		
		$newClientAccountingCode=$this->AccountingCode->getNewClientAccountingCode();
		$this->set(compact('newClientAccountingCode'));
		
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
				//'AccountingCode.bool_main'=>'0',
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$accountingCodes[$newClientAccountingCode]=$newClientAccountingCode;
		//pr($accountingCodes);
		$this->set(compact('accountingCodes'));
    
    $creditCurrencies=$this->Currency->find('list');
    $this->set(compact('creditCurrencies'));
    
    $priceClientCategories=$this->PriceClientCategory->find('list');
    $this->set(compact('priceClientCategories'));
    
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
	}
	
  public function sortByValueDesc($a,$b){ 
		if( $a == $b){ 
			return 0; 
		} 
		return $a < $b ? 1 : -1;
	}
  
  public function convertirCliente($clientName) {
    $this->loadModel('Order');
    $this->loadModel('SalesOrder');
    $this->loadModel('Quotation');
    
    $this->loadModel('AccountingCode');
		$this->loadModel('PriceClientCategory');
    $this->loadModel('Currency');
    
    $this->loadModel('ClientType');
    $this->loadModel('Zone');
    
    $this->loadModel('Plant');
    $this->loadModel('PlantThirdParty');
    $this->loadModel('UserPlant');
    
    $this->loadModel('User');
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $this->set(compact('clientName'));
    
    $clientDataPrevalence=[
      'client_phone'=>[],
      'client_email'=>[],
      'client_address'=>[],
      'client_ruc'=>[],
      'client_type_id'=>[],
      'zone_id'=>[],
    ];
    
    $genericClientIds=$this->ThirdParty->getGenericClientIds();
    //pr($genericClientIds);
    
    $clientOrders=$this->Order->getOrdersForClientName($clientName,$genericClientIds);
    
    $genericClientNames=$this->Order->getGenericClientNames($genericClientIds);  
    //pr($genericClientNames);
    $similarGenericClientNames=$this->Order->getSimilarClientNames($genericClientNames,$clientName,70);  
    //pr($similarGenericClientNames);
    $similarClientOrders=[];
    foreach ($similarGenericClientNames as $similarGenericClientName){
      $similarClientOrders[$similarGenericClientName]['Orders']=$this->Order->getOrdersForClientName($similarGenericClientName,$genericClientIds);
    }
    $this->set(compact('similarClientOrders'));
    //pr($similarClientOrders);
    
    $selectedSimilarClientNames=[];
    
    if ($this->request->is('post')) {
      //pr($this->request->data);
      if (!empty($this->request->data['SimilarClient'])){
        foreach ($this->request->data['SimilarClient'] as $similarClientName=>$similarClientApplied){
          if ($similarClientApplied){
            //echo "there are ".count($clientOrders).' orders<br/>';
            $selectedSimilarClientNames[]=$similarClientName;
            $selectedSimilarClientOrders=$this->Order->getOrdersForClientName($similarClientName,$genericClientIds);
            //pr($selectedSimilarClientOrders);
            $clientOrders=array_merge($clientOrders,$selectedSimilarClientOrders);
            //echo "there are ".count($clientOrders).' orders<br/>';
          }
        }
      }
    }  
    $this->set(compact('clientOrders'));
    //pr($clientOrders);
    foreach ($clientOrders as $clientOrder){
      foreach (array_keys($clientDataPrevalence) as $fieldName){ 
        if (!empty($clientOrder['Order'][$fieldName])){
          if (!array_key_exists($clientOrder['Order'][$fieldName],$clientDataPrevalence[$fieldName])){
            $clientDataPrevalence[$fieldName][$clientOrder['Order'][$fieldName]]=0;
          }
          $clientDataPrevalence[$fieldName][$clientOrder['Order'][$fieldName]]+=1;
        }
      }
    }
    //pr($clientDataPrevalence);  
    
    foreach (array_keys($clientDataPrevalence) as $fieldName){
      //usort($clientDataPrevalence[$fieldName],[$this,'sortByValueDesc']);
      arsort($clientDataPrevalence[$fieldName]);
    }
    //pr($clientDataPrevalence);  
    $proposedClientData=[];
    foreach (array_keys($clientDataPrevalence) as $fieldName){
      //usort($clientDataPrevalence[$fieldName],[$this,'sortByValueDesc']);
      $proposedClientData[$fieldName]=empty(array_keys($clientDataPrevalence[$fieldName]))?'':array_keys($clientDataPrevalence[$fieldName])[0];
    }
    $this->set(compact('proposedClientData'));
    
    $registeredClientNames=$this->ThirdParty->getRegisteredClientNames();
    $similarRegisteredClientNames=$this->ThirdParty->getSimilarClientNames($registeredClientNames,$clientName,70);  
    //pr($similarRegisteredClientNames);
    $this->set(compact('similarRegisteredClientNames'));
    $similarRegisteredClients=[];
    if (!empty($similarRegisteredClientNames)){
      foreach (array_keys($similarRegisteredClientNames) as $registeredClientId){
        $similarRegisteredClients[$registeredClientId]=$this->ThirdParty->getClientById($registeredClientId);
      }
    }
    $this->set(compact('similarRegisteredClients'));
    //pr($similarRegisteredClients);
    
		$users = $this->User->getAllActiveVendorAdminUsersAllWarehouses();
		$this->set(compact('users'));
	
		$plants=$this->Plant->find('all',['recursive'=>-1,]);
    $this->set(compact('plants'));
    
		if ($this->request->is('post') && empty($this->request->data['applySimilarClients'])) {
      $this->request->data['ThirdParty']['company_name']=trim(strtoupper($this->request->data['ThirdParty']['company_name']));
			$previousClientsWithThisName=[];
			$previousClientsWithThisName=$this->ThirdParty->find('all',[
				'conditions'=>['TRIM(UPPER(ThirdParty.company_name))'=>$this->request->data['ThirdParty']['company_name']],
			]);
      
      $boolAssociationWithVendorPresent='0';
      $boolAssociationWithPlantPresent='0';
      if (!empty($this->request->data['User'])){
        for ($u=0;$u<count($this->request->data['User']);$u++){
          if ($this->request->data['User'][$u]['id']){
            $boolAssociationWithVendorPresent=true;
          }
        }
      }
      if (!empty($this->request->data['Plant'])){
        for ($p=0;$p<count($this->request->data['Plant']);$p++){
          if ($this->request->data['Plant'][$p]['id']){
            $boolAssociationWithPlantPresent=true;
          }
        }
      }
			/*
      $allPreviousClients=$this->ThirdParty->find('all',[
				'fields'=>['ThirdParty.company_name'],
			]);
			
			$bool_similar='0';
			$similar_string="";
			foreach ($allPreviousClients as $existingClientName){
				similar_text($this->request->data['ThirdParty']['company_name'],$existingClientName['ThirdParty']['company_name'],$percent);
				if ($percent>80){
					$bool_similar=true;
					$similar_string=$existingClientName['ThirdParty']['company_name'];
				}
			}
			*/
			if ($this->request->data['ThirdParty']['id'] == 0 && count($previousClientsWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un cliente con este nombre!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			}
			//elseif ($bool_similar){
			//	$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un cliente existente: '.$similar_string.'!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			//}
      elseif (!$this->request->data['ThirdParty']['bool_generic'] && empty($this->request->data['ThirdParty']['client_type_id'])){
				$this->Session->setFlash('Se debe indicar el tipo de cliente!  No se guardó el cliente.', 'default',['class' => 'error-message']);
			}
      elseif (!$this->request->data['ThirdParty']['bool_generic'] && empty($this->request->data['ThirdParty']['zone_id'])){
				$this->Session->setFlash('Se debe indicar la zona!  No se guardó el cliente.', 'default',['class' => 'error-message']);
			}
      else if (!$boolAssociationWithVendorPresent){
        $this->Session->setFlash('El cliente se debe asociar con por lo menos un vendedor!  No se guardó el cliente.', 'default',['class' => 'error-message']);
      }
      else if (!$boolAssociationWithPlantPresent){
        $this->Session->setFlash('El cliente se debe asociar con por lo menos una planta!  No se guardó el cliente.', 'default',['class' => 'error-message']);
      }
			else {	
        $datasource=$this->ThirdParty->getDataSource();
        $datasource->begin();
        try {
          if ($this->request->data['ThirdParty']['id'] == 0){  
            //pr($this->request->data);
            $accountingCodeArray=[
              'code'=>$this->request->data['ThirdParty']['accounting_code_id'],
              'description'=>$this->request->data['ThirdParty']['company_name'],
              'parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
              'bool_main'=>'0',
              'bool_creditor'=>'0',
            ];
            $this->AccountingCode->create();
            if (!$this->AccountingCode->save($accountingCodeArray)) {
              $this->Session->setFlash(__('No se podía guardar la cuenta contable para el cliente nuevo.'), 'default',['class' => 'error-message']);
              pr($this->AccountingCode->validateErrors($this->ThirdParty));
              throw new Exception();
            }
            
            $this->request->data['ThirdParty']['accounting_code_id']=$this->AccountingCode->id;
          
            $this->request->data['ThirdParty']['bool_provider']='0';
            $this->ThirdParty->create();
          }
          else {
            $this->ThirdParty->id=$this->request->data['ThirdParty']['id'];
          }  
          
          if (!$this->ThirdParty->save($this->request->data)) {
            echo "Problema guardando el cliente";
            pr($this->validateErrors($this->ThirdParty));
            throw new Exception();
          }
          $clientId=$this->ThirdParty->id;
          
          
          $currentDateTime=new DateTime();
          if (!empty($this->request->data['User'])){
            for ($u=0;$u<count($this->request->data['User']);$u++){
              $this->ThirdParty->ThirdPartyUser->create();
              $clientUserArray=[
                'third_party_id'=>$clientId,
                'user_id'=>$users[$u]['User']['id'],
                'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                'bool_assigned'=>$this->request->data['User'][$u]['id'],
              ];
              if (!$this->ThirdParty->ThirdPartyUser->save($clientUserArray)){
                echo "Problema guardando el vendedor para el cliente";
                pr($this->validateErrors($this->ThirdParty->ThirdPartyUser));
                throw new Exception();
              }							
            }
          }
          if (!empty($this->request->data['Plant'])){
            for ($p=0;$p<count($this->request->data['Plant']);$p++){
              $plantThirdPartyArray=[
                'plant_id'=>$plants[$p]['Plant']['id'],
                'third_party_id'=>$clientId,
                'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                'bool_assigned'=>$this->request->data['Plant'][$p]['id'],
              ];
              $this->PlantThirdParty->create();
              if (!$this->PlantThirdParty->save($plantThirdPartyArray)){
                echo "Problema guardando la asociación planta cliente";
                pr($this->validateErrors($this->PlantThirdParty));
                throw new Exception();
              }							
            }
          }
          
          $processedOrders=[];
          $processedSalesOrders=[];
          $processedQuotations=[];
          foreach ($clientOrders as $clientOrder){
            $orderArray=[
              'id'=>$clientOrder['Order']['id'],
              'third_party_id'=>$clientId,
              'client_name'=>$this->request->data['ThirdParty']['company_name'],
              'client_phone'=>$this->request->data['ThirdParty']['phone'],
              'client_email'=>$this->request->data['ThirdParty']['email'],
              'client_address'=>$this->request->data['ThirdParty']['address'],
              'client_ruc'=>$this->request->data['ThirdParty']['ruc_number'],
              'client_type_id'=>$this->request->data['ThirdParty']['client_type_id'],
              'zone_id'=>$this->request->data['ThirdParty']['zone_id'],
            ];
            $this->Order->id=$clientOrder['Order']['id'];
            if (!$this->Order->save($orderArray)){
              echo "Problema actualizando los datos de cliente en la venta";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            }
            $processedOrders[]=$clientOrder['Order']['id'];
            
            $salesOrderId=$this->SalesOrder->getSalesOrderIdByOrderId($clientOrder['Invoice'][0]['id']);
            if ($salesOrderId>0){
              $salesOrderArray=[
                'id'=>$salesOrderId,
                'client_id'=>$clientId,
                'client_name'=>$this->request->data['ThirdParty']['company_name'],
                'client_phone'=>$this->request->data['ThirdParty']['phone'],
                'client_email'=>$this->request->data['ThirdParty']['email'],
                'client_address'=>$this->request->data['ThirdParty']['address'],
                'client_ruc'=>$this->request->data['ThirdParty']['ruc_number'],
                'client_type_id'=>$this->request->data['ThirdParty']['client_type_id'],
                'zone_id'=>$this->request->data['ThirdParty']['zone_id'],
              ];
              $this->SalesOrder->id=$salesOrderId;
              if (!$this->SalesOrder->save($salesOrderArray)){
                echo "Problema actualizando los datos de cliente en la orden de venta id ".$salesOrderId;
                pr($this->validateErrors($this->SalesOrder));
                throw new Exception();
              }
              $processedSalesOrders[]=$salesOrderId;
              
              $quotationId=$this->Quotation->getQuotationIdBySalesOrderId($salesOrderId);
              if ($quotationId > 0){
                $quotationArray=[
                  'id'=>$quotationId,
                  'client_id'=>$clientId,
                  'client_name'=>$this->request->data['ThirdParty']['company_name'],
                  'client_phone'=>$this->request->data['ThirdParty']['phone'],
                  'client_email'=>$this->request->data['ThirdParty']['email'],
                  'client_address'=>$this->request->data['ThirdParty']['address'],
                  'client_ruc'=>$this->request->data['ThirdParty']['ruc_number'],
                  'client_type_id'=>$this->request->data['ThirdParty']['client_type_id'],
                  'zone_id'=>$this->request->data['ThirdParty']['zone_id'],
                ];
                $this->Quotation->id=$quotationId;
                if (!$this->Quotation->save($quotationArray)){
                  echo "Problema actualizando los datos de cliente en la cotización id ".$quotationId;
                  pr($this->validateErrors($this->Quotation));
                  throw new Exception();
                }  
                $processedQuotations[]=$quotationId;
              }
            }
          }
        
          $unprocessedSalesOrders=$this->SalesOrder->getSalesOrdersForClientName($clientName,$genericClientIds,$processedSalesOrders);
          if (!empty($selectedSimilarClientNames)){
            foreach ($selectedSimilarClientNames as $selectedSimilarClientName){
              $selectedSimilarClientSalesOrders=$this->SalesOrder->getSalesOrdersForClientName($selectedSimilarClientName,$genericClientIds,$processedSalesOrders);
              $unprocessedSalesOrders=array_merge($unprocessedSalesOrders,$selectedSimilarClientSalesOrders);          
            }
          }
          if (!empty($unprocessedSalesOrders)){
            foreach ($unprocessedSalesOrders as $unprocessedSalesOrder){
              $salesOrderArray=[
                  'id'=>$unprocessedSalesOrder['SalesOrder']['id'],
                  'client_id'=>$clientId,
                  'client_name'=>$this->request->data['ThirdParty']['company_name'],
                  'client_phone'=>$this->request->data['ThirdParty']['phone'],
                  'client_email'=>$this->request->data['ThirdParty']['email'],
                  'client_address'=>$this->request->data['ThirdParty']['address'],
                  'client_ruc'=>$this->request->data['ThirdParty']['ruc_number'],
                  'client_type_id'=>$this->request->data['ThirdParty']['client_type_id'],
                  'zone_id'=>$this->request->data['ThirdParty']['zone_id'],
                ];
                $this->SalesOrder->id=$unprocessedSalesOrder['SalesOrder']['id'];
                if (!$this->SalesOrder->save($salesOrderArray)){
                  echo "Problema actualizando los datos de cliente en la orden de venta id ".$unprocessedSalesOrder['SalesOrder']['id'];
                  pr($this->validateErrors($this->SalesOrder));
                  throw new Exception();
                }
                $processedSalesOrders[]=$unprocessedSalesOrder['SalesOrder']['id'];
                
                $quotationId=$this->Quotation->getQuotationIdBySalesOrderId($unprocessedSalesOrder['SalesOrder']['id']);
                if ($quotationId > 0){
                  $quotationArray=[
                    'id'=>$quotationId,
                    'client_id'=>$clientId,
                    'client_name'=>$this->request->data['ThirdParty']['company_name'],
                    'client_phone'=>$this->request->data['ThirdParty']['phone'],
                    'client_email'=>$this->request->data['ThirdParty']['email'],
                    'client_address'=>$this->request->data['ThirdParty']['address'],
                    'client_ruc'=>$this->request->data['ThirdParty']['ruc_number'],
                    'client_type_id'=>$this->request->data['ThirdParty']['client_type_id'],
                    'zone_id'=>$this->request->data['ThirdParty']['zone_id'],
                  ];
                  $this->Quotation->id=$quotationId;
                  if (!$this->Quotation->save($quotationArray)){
                    echo "Problema actualizando los datos de cliente en la cotización id ".$quotationId;
                    pr($this->validateErrors($this->Quotation));
                    throw new Exception();
                  }  
                  $processedQuotations[]=$quotationId;
                }
            }                
          }
          
          $unprocessedQuotations=$this->Quotation->getQuotationsForClientName($clientName,$genericClientIds,$processedQuotations);
          if (!empty($selectedSimilarClientNames)){
            foreach ($selectedSimilarClientNames as $selectedSimilarClientName){
              $selectedSimilarClientQuotations=$this->Quotation->getQuotationsForClientName($selectedSimilarClientName,$genericClientIds,$processedQuotations);
              $unprocessedQuotations=array_merge($unprocessedQuotations,$selectedSimilarClientQuotations);          
            }
          }
          if (!empty($unprocessedQuotations)){
            foreach ($unprocessedQuotations as $unprocessedQuotation){                
              $quotationArray=[
                'id'=>$unprocessedQuotation['Quotation']['id'],
                'client_id'=>$clientId,
                'client_name'=>$this->request->data['ThirdParty']['company_name'],
                'client_phone'=>$this->request->data['ThirdParty']['phone'],
                'client_email'=>$this->request->data['ThirdParty']['email'],
                'client_address'=>$this->request->data['ThirdParty']['address'],
                'client_ruc'=>$this->request->data['ThirdParty']['ruc_number'],
                'client_type_id'=>$this->request->data['ThirdParty']['client_type_id'],
                'zone_id'=>$this->request->data['ThirdParty']['zone_id'],
              ];
              $this->Quotation->id=$unprocessedQuotation['Quotation']['id'];
              if (!$this->Quotation->save($quotationArray)){
                echo "Problema actualizando los datos de cliente en la cotización id ".$unprocessedQuotation['Quotation']['id'];
                pr($this->validateErrors($this->Quotation));
                throw new Exception();
              }  
              $processedQuotations[]=$unprocessedQuotation['Quotation']['id'];
            }
          }
          
          $datasource->commit();
          $this->recordUserAction($this->ThirdParty->id,'convertirCliente',null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se convirtió el cliente ".$this->request->data['ThirdParty']['company_name']);
          $this->recordUserActivity($this->Session->read('User.username'),"Se modificaron las facturas ".implode(',',$processedOrders).' las ordenes de venta '.implode(',',$processedSalesOrders).' y las cotizaciones '.implode(',',$processedQuotations));  
          $this->Session->setFlash('Se guardó el cliente.','default',['class' => 'success']);
          
          if (!empty($this->request->data['guardarCliente'])){
            return $this->redirect(['action' => 'verCliente',$clientId]);  
          }
          elseif (!empty($this->request->data['guardarClienteYPrecios'])){
            $aco_name="ProductPriceLogs/registrarPreciosCliente";		
            $boolRegistrarPreciosClientePermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
            if ($boolRegistrarPreciosClientePermission){
              return $this->redirect(['controller'=>'ProductPriceLogs','action' => 'registrarPreciosCliente',$clientId]);  
            }
            else {
              return $this->redirect(['action' => 'index']);  
            }
          }          
        }      
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash('No se podía guardar el cliente. Por favor intente de nuevo.', 'default',['class' => 'error-message']);
        }
      }  
		}
		
    $newClientAccountingCode=$this->AccountingCode->getNewClientAccountingCode();
		$this->set(compact('newClientAccountingCode'));
		
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
				//'AccountingCode.bool_main'=>'0',
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$accountingCodes[$newClientAccountingCode]=$newClientAccountingCode;
		$this->set(compact('accountingCodes'));
    $creditCurrencies=$this->Currency->find('list');
    $this->set(compact('creditCurrencies'));
    
    $priceClientCategories=$this->PriceClientCategory->find('list');
    $clientTypes=$this->ClientType->getClientTypes();
    $zones=$this->Zone->getZones();
    $this->set(compact('priceClientCategories','clientTypes','zones'));
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
	}
	
  
  public function registrarCliente($clientName) {
    $this->loadModel('AccountingCode');
		$this->loadModel('User');
    $this->loadModel('PriceClientCategory');
    $this->loadModel('Currency');
    
    $this->loadModel('ClientType');
    $this->loadModel('Zone');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
		$this->User->recursive=-1;
		$users = $this->User->find('all',[
			'fields'=>['User.id','User.username','User.first_name','User.last_name'],
      'conditions'=>['User.bool_active'=>true],
      'order'=>'User.first_name,User.last_name,User.username',
		]);
		$this->set(compact('users'));
	
		$plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
		$plantId=0;
  
		if ($this->request->is('post')) {
      $plantId=$this->request->data['ThirdParty']['plant_id'];
      	
			$this->request->data['ThirdParty']['company_name']=trim(strtoupper($this->request->data['ThirdParty']['company_name']));
			$previousClientsWithThisName=[];
			$previousClientsWithThisName=$this->ThirdParty->find('all',[
				'conditions'=>['TRIM(UPPER(ThirdParty.company_name))'=>$this->request->data['ThirdParty']['company_name']],
			]);
			
			$allPreviousClients=$this->ThirdParty->find('all',[
				'fields'=>['ThirdParty.company_name'],
			]);
			
			$bool_similar='0';
			$similar_string="";
			foreach ($allPreviousClients as $existingClientName){
				similar_text($this->request->data['ThirdParty']['company_name'],$existingClientName['ThirdParty']['company_name'],$percent);
				if ($percent>80){
					$bool_similar=true;
					$similar_string=$existingClientName['ThirdParty']['company_name'];
				}
			}
			
			if (count($previousClientsWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un cliente con este nombre!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			}
			//elseif ($bool_similar){
			//	$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un cliente existente: '.$similar_string.'!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			//}
      elseif (!$this->request->data['ThirdParty']['bool_generic'] && empty($this->request->data['ThirdParty']['client_type_id'])){
				$this->Session->setFlash('Se debe indicar el tipo de cliente!  No se guardó el cliente.', 'default',['class' => 'error-message']);
			}
      elseif (!$this->request->data['ThirdParty']['bool_generic'] && empty($this->request->data['ThirdParty']['zone_id'])){
				$this->Session->setFlash('Se debe indicar la zona!  No se guardó el cliente.', 'default',['class' => 'error-message']);
			}
			else {	
        $datasource=$this->ThirdParty->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
          $this->AccountingCode->create();
          $accountingCodeArray=[
            'code'=>$this->request->data['ThirdParty']['accounting_code_id'],
            'description'=>$this->request->data['ThirdParty']['company_name'],
            'parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
            'bool_main'=>'0',
            'bool_creditor'=>'0',
          ];
          if (!$this->AccountingCode->save($accountingCodeArray)) {
            $this->Session->setFlash(__('No se podía guardar la cuenta contable para el cliente nuevo.'), 'default',['class' => 'error-message']);
            pr($this->AccountingCode->validateErrors($this->ThirdParty));
            throw new Exception();
          }
          else {
            $this->request->data['ThirdParty']['accounting_code_id']=$this->AccountingCode->id;
            $this->ThirdParty->create();
            $this->request->data['ThirdParty']['bool_provider']='0';
            if (!$this->ThirdParty->save($this->request->data)) {
              echo "Problema guardando el cliente";
              pr($this->validateErrors($this->ThirdParty));
              throw new Exception();
            }
            $clientId=$this->ThirdParty->id;
            
            if (!empty($this->request->data['User'])){
              $currentDateTime=new DateTime();
              for ($u=0;$u<count($this->request->data['User']);$u++){
                $this->ThirdParty->ThirdPartyUser->create();
                $clientUserArray=[
                  'third_party_id'=>$clientId,
                  'user_id'=>$users[$u]['User']['id'],
                  'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                  'bool_assigned'=>$this->request->data['User'][$u]['id'],
                ];
                if (!$this->ThirdParty->ThirdPartyUser->save($clientUserArray)){
                  echo "Problema guardando el vendedor para el cliente";
                  pr($this->validateErrors($this->ThirdParty->ThirdPartyUser));
                  throw new Exception();
                }							
              }
            }
          }
          
          $datasource->commit();
          $this->recordUserAction($this->ThirdParty->id,null,null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se registró el cliente ".$this->request->data['ThirdParty']['company_name']);
            
          $this->Session->setFlash(__('Se guardó el cliente.'),'default',['class' => 'success']);
          
          if (!empty($this->request->data['guardarCliente'])){
            return $this->redirect(['action' => 'resumenClientes']);  
          }
          elseif (!empty($this->request->data['guardarClienteYPrecios'])){
            $aco_name="ProductPriceLogs/registrarPreciosCliente";		
            $boolRegistrarPreciosClientePermission=$this->hasPermission($this->Auth->User('id'),$aco_name);
            if ($boolRegistrarPreciosClientePermission){
              return $this->redirect(['controller'=>'ProductPriceLogs','action' => 'registrarPreciosCliente',$clientId]);  
            }
            else {
              return $this->redirect(['action' => 'index']);  
            }
          }          
        }      
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar el cliente. Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
				}
			}  
		}
		
    $this->set(compact('plantId'));
    
		$newClientAccountingCode=$this->AccountingCode->getNewClientAccountingCode();
		$this->set(compact('newClientAccountingCode'));
		
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
				//'AccountingCode.bool_main'=>'0',
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$accountingCodes[$newClientCode]=$newClientCode;
		//pr($accountingCodes);
		$this->set(compact('accountingCodes'));
    
    $creditCurrencies=$this->Currency->find('list');
    $this->set(compact('creditCurrencies'));
    
    $priceClientCategories=$this->PriceClientCategory->find('list');
    $this->set(compact('priceClientCategories'));
    
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
	}
	
  
	public function crearProveedor() {
		$this->loadModel('AccountingCode');
    $this->loadModel('Currency');
    
    $this->loadModel('ProviderNature');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
		$plantId=0;
  
		if ($this->request->is('post')) {
      $plantId=$this->request->data['ThirdParty']['plant_id'];
      
      $previousProvidersWithThisName=[];
			$previousProvidersWithThisName=$this->ThirdParty->find('all',[
				'conditions'=>[
					'TRIM(LOWER(ThirdParty.company_name))'=>trim(strtolower($this->request->data['ThirdParty']['company_name'])),
				],
			]);
			
			$allPreviousProviders=$this->ThirdParty->find('all',[
				'fields'=>['ThirdParty.company_name'],
			]);
			
			$bool_similar='0';
			$similar_string="";
			foreach ($allPreviousProviders as $existingProviderName){
				similar_text($this->request->data['ThirdParty']['company_name'],$existingProviderName['ThirdParty']['company_name'],$percent);
				if ($percent>80){
					$bool_similar=true;
					$similar_string=$existingProviderName['ThirdParty']['company_name'];
				}
			}
			
			if (count($previousProvidersWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un proveedor con este nombre!  No se guardó el proveedor.'), 'default',['class' => 'error-message']);
			}
			elseif ($bool_similar){
				$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un proveedor existente: '.$similar_string.'!  No se guardó el proveedor.'), 'default',['class' => 'error-message']);
			}
			else {
        $datasource=$this->ThirdParty->getDataSource();
				$datasource->begin();
				try {
          $this->AccountingCode->create();
          $accountingCodeArray=[
            'code'=>$this->request->data['ThirdParty']['accounting_code_id'],
            'description'=>$this->request->data['ThirdParty']['company_name'],
            'parent_id'=>ACCOUNTING_CODE_PROVIDERS,
            'bool_main'=>'0',
            'bool_creditor'=>true,
          ];
          if (!$this->AccountingCode->save($accountingCodeArray)) {
            $this->Session->setFlash(__('No se podía guardar la cuenta contable para el proveedor nuevo.'), 'default',['class' => 'error-message']);
          }
          else {
            $this->request->data['ThirdParty']['accounting_code_id']=$this->AccountingCode->id;
            $this->ThirdParty->create();
            $this->request->data['ThirdParty']['bool_provider']=true;
            if (!$this->ThirdParty->save($this->request->data)) {
              echo "Problema guardando el proveedor";
              pr($this->validateErrors($this->ThirdParty));
              throw new Exception();
            }
            $provider_id=$this->ThirdParty->id;
          }
          $datasource->commit();  
          $this->recordUserAction($this->ThirdParty->id,"add",null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se registró el cliente ".$this->request->data['ThirdParty']['company_name']);
            
          $this->Session->setFlash(__('The provider has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumenProveedores']);
        }      
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar el proveedor.'), 'default',['class' => 'error-message']);
				}  
			}
		}
		
    $this->set(compact('plantId'));
    
		$lastProviderAccountingCode=$this->AccountingCode->find('first',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_PROVIDERS,
			],
			'order'=>'AccountingCode.code DESC',
		]);
		$lastProviderCode=$lastProviderAccountingCode['AccountingCode']['code'];
		$positionLastHyphen=strrpos($lastProviderCode,"-");
		$providerCodeStart=substr($lastProviderCode,0,($positionLastHyphen+1));
		$providerCodeEnding=substr($lastProviderCode,($positionLastHyphen+1));
		$newProviderCodeEnding=str_pad($providerCodeEnding+1,3,'0',STR_PAD_LEFT);
		$newProviderCode=$providerCodeStart.$newProviderCodeEnding;
		$this->set(compact('newProviderCode'));
		
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_PROVIDERS,
				//'AccountingCode.bool_main'=>'0',
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$accountingCodes[$newProviderCode]=$newProviderCode;
		$this->set(compact('accountingCodes'));
    
    $creditCurrencies=$this->Currency->find('list');
    $this->set(compact('creditCurrencies'));
    
    $providerNatures=$this->ProviderNature->getProviderNatureList();
    $this->set(compact('providerNatures'));
   
		$aco_name="ThirdParties/crearProveedor";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarProveedor";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/resumenEntradas";		
		$bool_purchase_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_index_permission'));
		$aco_name="Orders/crearEntrada";		
		$bool_purchase_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_add_permission'));
    
    $aco_name="PurchaseOrders/resumen";		
		$bool_purchase_order_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_index_permission'));
		$aco_name="PurchaseOrders/crear";		
		$bool_purchase_order_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_add_permission'));
	}

	public function editarCliente($id = null) {
		if (!$this->ThirdParty->exists($id)) {
			throw new NotFoundException(__('Invalid third party'));
		}
    $this->loadModel('AccountingCode');
    $this->loadModel('Currency');
		$this->loadModel('PriceClientCategory');
		
    $this->loadModel('ClientType');
    $this->loadModel('Zone');
    
    $this->loadModel('ThirdPartyUser');
		$this->loadModel('User');
    
    $this->loadModel('Plant');
    $this->loadModel('PlantThirdParty');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $userRoleId= $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $users = $this->User->find('all',[
			'fields'=>['User.id','User.username','User.first_name','User.last_name'],
      'conditions'=>['User.bool_active'=>true],
			'contain'=>[
				'ThirdPartyUser'=>[
					'conditions'=>['ThirdPartyUser.third_party_id'=>$id],
					'order'=>'ThirdPartyUser.id DESC',
				],
			],
			'order'=>'User.first_name,User.last_name,User.username',
		]);
		$this->set(compact('users'));
		//pr($users);
		
		//$plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    $plants = $this->Plant->find('all',[
			'fields'=>['Plant.id','Plant.name'],
      'conditions'=>['Plant.bool_active'=>true],
			'contain'=>[
				'PlantThirdParty'=>[
					'conditions'=>['PlantThirdParty.third_party_id'=>$id],
					'order'=>'PlantThirdParty.id DESC',
				],
			],
			'order'=>'Plant.name',
		]);
		$this->set(compact('plants'));
    //pr($plants);
    
    
		if ($this->request->is(['post', 'put'])) {
      
			$previousClientsWithThisName=[];
			$previousClient=$this->ThirdParty->read(null,$id);
			
			$bool_similar='0';
			$similar_string="";
			
			if ($previousClient['ThirdParty']['company_name']!=$this->request->data['ThirdParty']['company_name']){
				$previousClientsWithThisName=$this->ThirdParty->find('all',array(
					'conditions'=>array(
						'TRIM(LOWER(ThirdParty.company_name))'=>trim(strtolower($this->request->data['ThirdParty']['company_name'])),
					),
				));
				
				$allPreviousClients=$this->ThirdParty->find('all',array(
					'fields'=>array('ThirdParty.company_name'),
				));
			}
      
      $boolAssociationWithVendorPresent='0';
      $boolAssociationWithPlantPresent='0';
      if (!empty($this->request->data['User'])){
        for ($u=0;$u<count($this->request->data['User']);$u++){
          if ($this->request->data['User'][$u]['id']){
            $boolAssociationWithVendorPresent=true;
          }
        }
      }
      if (!empty($this->request->data['Plant'])){
        for ($p=0;$p<count($this->request->data['Plant']);$p++){
          if ($this->request->data['Plant'][$p]['id']){
            $boolAssociationWithPlantPresent=true;
          }
        }
      }
			
			if (count($previousClientsWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un cliente con este nombre!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			}
			//elseif ($bool_similar){
			//	$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un cliente existente: '.$similar_string.'!  No se guardó el cliente.'), 'default',array('class' => 'error-message'));
			//}
      elseif (!$this->request->data['ThirdParty']['bool_generic'] && empty($this->request->data['ThirdParty']['client_type_id'])){
				$this->Session->setFlash('Se debe indicar el tipo de cliente!  No se guardó el cliente.', 'default',['class' => 'error-message']);
			}
      elseif (!$this->request->data['ThirdParty']['bool_generic'] && empty($this->request->data['ThirdParty']['zone_id'])){
				$this->Session->setFlash('Se debe indicar la zona!  No se guardó el cliente.', 'default',['class' => 'error-message']);
			}
      //else if (!$boolAssociationWithVendorPresent){
      //  $this->Session->setFlash('El cliente se debe asociar con por lo menos un vendedor!  No se guardó el cliente.', 'default',['class' => 'error-message']);
      //}
      else if (!$boolAssociationWithPlantPresent){
        $this->Session->setFlash('El cliente se debe asociar con por lo menos una planta!  No se guardó el cliente.', 'default',['class' => 'error-message']);
      }
			else {
        $datasource=$this->ThirdParty->getDataSource();
				$datasource->begin();
				try {
				  $this->ThirdParty->ThirdPartyUser->recursive=-1;
          
          $this->request->data['ThirdParty']['id']=$id;
          $this->request->data['ThirdParty']['bool_provider']='0';
          $this->ThirdParty->id=$id;
          if (!$this->ThirdParty->save($this->request->data)) {
						echo "Problema guardando el cliente";
						pr($this->validateErrors($this->ThirdParty));
						throw new Exception();
					}
					$clientId=$this->ThirdParty->id;
					$currentDateTime=new DateTime();					
						
          if (!empty($this->request->data['User'])){
						for ($u=0;$u<count($this->request->data['User']);$u++){
							//pr($this->request->data['User'][$u]);
							$this->ThirdParty->ThirdPartyUser->create();
							$clientUserArray=[
                'third_party_id'=>$clientId,
                'user_id'=>$users[$u]['User']['id'],
                'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                'bool_assigned'=>$this->request->data['User'][$u]['id'],
              ];
							if (!$this->ThirdParty->ThirdPartyUser->save($clientUserArray)){
								echo "Problema guardando el vendedor para el cliente";
								pr($this->validateErrors($this->ThirdParty->ThirdPartyUser));
								throw new Exception();
							}
						}
					}
          if (!empty($this->request->data['Plant'])){
						for ($p=0;$p<count($this->request->data['Plant']);$p++){
							//pr($this->request->data['Plant'][$p]);
							$this->ThirdParty->PlantThirdParty->create();
							$plantThirdPartyArray=[
                'third_party_id'=>$clientId,
                'plant_id'=>$plants[$p]['Plant']['id'],
                'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                'bool_assigned'=>$this->request->data['Plant'][$p]['id'],
              ];
							if (!$this->ThirdParty->PlantThirdParty->save($plantThirdPartyArray)){
								echo "Problema guardando la asociación de planta con el cliente";
								pr($this->validateErrors($this->ThirdParty->PlantThirdParty));
								throw new Exception();
							}
						}
					}
					
          $datasource->commit();
					$this->recordUserAction($this->ThirdParty->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se editó el cliente ".$this->request->data['ThirdParty']['company_name']);
					
					$this->Session->setFlash(__('Se guardó el cliente.'),'default',['class' => 'success']);
					return $this->redirect(['action' => 'resumenClientes']);
        } 
				catch(Exception $e){
					$datasource->rollback();
					//pr($e);
					$this->Session->setFlash(__('No se podía guardar el cliente.'), 'default',['class' => 'error-message']);
				}  
			}
		} 
		else {
			$options = ['conditions' => ['ThirdParty.id'=> $id]];
			$this->request->data = $this->ThirdParty->find('first', $options);
      $plantId=$this->request->data['ThirdParty']['plant_id'];
		}
    
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES,
				//'AccountingCode.bool_main'=>'0',
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$this->set(compact('accountingCodes'));
		
    $creditCurrencies=$this->Currency->find('list');
    $this->set(compact('creditCurrencies'));
    
    $priceClientCategories=$this->PriceClientCategory->find('list');
    $this->set(compact('priceClientCategories'));
    
    $allUsers=$this->User->find('all',[
      'fields'=>['User.id','User.username','User.first_name','User.last_name'],
      'recursive'=>-1,
      'order'=>'User.first_name ASC,User.last_name ASC,User.username ASC',
    ]);
    
    $usersAssociatedWithClient=$this->ThirdPartyUser->getUsersForThirdParty($id);
    $this->set(compact('usersAssociatedWithClient'));
    
    $allPlants=$this->Plant->find('all',[
      'fields'=>['Plant.id','Plant.name'],
      'recursive'=>-1,
      'order'=>'Plant.name ASC',
    ]);
    
    $plantsAssociatedWithClient=[];
    foreach ($allPlants as $plant){
      if ($this->PlantThirdParty->hasPlantThirdParty($id,$plant['Plant']['id'])){
        $plantsAssociatedWithClient[]=$plant;
      }
    }
    $this->set(compact('usersAssociatedWithClient'));
    
    $clientTypes=$this->ClientType->getClientTypes();
    $this->set(compact('clientTypes'));
    $zones=$this->Zone->getZones();
    $this->set(compact('zones'));
    
		$aco_name="ThirdParties/crearCliente";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarCliente";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="ThirdParties/deleteClient";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		
		$aco_name="Orders/resumenVentasRemisiones";		
		$bool_saleremission_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_saleremission_index_permission'));
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
	}
	
	public function editarProveedor($id = null) {
		if (!$this->ThirdParty->exists($id)) {
			throw new NotFoundException(__('Invalid third party'));
		}
    
    $this->loadModel('AccountingCode');
    
    $this->loadModel('ProviderNature');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
    $plantId=0;
  
		if ($this->request->is(['post', 'put'])) {
      $plantId=$this->request->data['ThirdParty']['plant_id'];
      
			$previousProvidersWithThisName=[];
			$previousProvider=$this->ThirdParty->getProviderById($id);
			
			$bool_similar='0';
			$similar_string="";
			
			if ($previousProvider['ThirdParty']['company_name']!=$this->request->data['ThirdParty']['company_name']){
				$previousProvidersWithThisName=$this->ThirdParty->find('all',array(
					'conditions'=>array(
						'TRIM(LOWER(ThirdParty.company_name))'=>trim(strtolower($this->request->data['ThirdParty']['company_name'])),
					),
				));
				
				$allPreviousProviders=$this->ThirdParty->find('all',array(
					'fields'=>array('ThirdParty.company_name'),
				));
        /*  
				foreach ($allPreviousProviders as $existingProviderName){
					similar_text($this->request->data['ThirdParty']['company_name'],$existingProviderName['ThirdParty']['company_name'],$percent);
					if ($percent>80){
						$bool_similar=true;
						$similar_string=$existingProviderName['ThirdParty']['company_name'];
					}
				}
*/				
			}
			
			if (count($previousProvidersWithThisName)>0){
				$this->Session->setFlash(__('Ya se introdujo un proveedor con este nombre!  No se guardó el proveedor.'), 'default',array('class' => 'error-message'));
			}
			elseif ($bool_similar){
				$this->Session->setFlash(__('El nombre que eligió parece demasiado al nombre de un proveedor existente: '.$similar_string.'!  No se guardó el proveedor.'), 'default',array('class' => 'error-message'));
			}
			else {								
			  $datasource=$this->ThirdParty->getDataSource();
				$datasource->begin();
				try {
				  $this->ThirdParty->ThirdPartyUser->recursive=-1;
          
          $this->request->data['ThirdParty']['id']=$id;
          $this->request->data['ThirdParty']['bool_provider']=true;
          $this->ThirdParty->id=$id;
          if (!$this->ThirdParty->save($this->request->data)) {
						echo "Problema guardando el proveedor";
						pr($this->validateErrors($this->ThirdParty));
						throw new Exception();
					}
					$clientId=$this->ThirdParty->id;
					
					
          $datasource->commit();
					$this->recordUserAction($this->ThirdParty->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se editó el proveedor ".$this->request->data['ThirdParty']['company_name']);
					
					$this->Session->setFlash(__('Se guardó el proveedor.'),'default',['class' => 'success']);
					return $this->redirect(['action' => 'resumenProveedores']);
        } 
				catch(Exception $e){
					$datasource->rollback();
					//pr($e);
					$this->Session->setFlash(__('No se podía guardar el proveedor.'), 'default',['class' => 'error-message']);
				}  
			}
		} 
		else {
			$options = ['conditions' => ['ThirdParty.id' => $id]];
			$this->request->data = $this->ThirdParty->find('first', $options);
      $plantId=$this->request->data['ThirdParty']['plant_id'];
		}
		if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    elseif (count($plants) > 1 && $plantId == 0){
      if (!empty($_SESSION['plantId'])){
        $plantId = $_SESSION['plantId'];
      }
      else {
        $plantId=0;
      }
    }
    $_SESSION['plantId']=$plantId;
    $this->set(compact('plantId'));
    
		$accountingCodes=$this->AccountingCode->find('list',[
			'conditions'=>[
				'AccountingCode.parent_id'=>ACCOUNTING_CODE_PROVIDERS,
				//'AccountingCode.bool_main'=>'0',
			],
			'order'=>'AccountingCode.code ASC',
		]);
		$this->set(compact('accountingCodes'));
    
    $providerNatures=$this->ProviderNature->getProviderNatureList();
    $this->set(compact('providerNatures'));
		 
		$aco_name="ThirdParties/crearProveedor";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="ThirdParties/editarProveedor";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="ThirdParties/deleteProvider";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
		
		$aco_name="Orders/resumenEntradas";		
		$bool_purchase_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_index_permission'));
		$aco_name="Orders/crearEntrada";		
		$bool_purchase_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_add_permission'));
    
    $aco_name="PurchaseOrders/resumen";		
		$bool_purchase_order_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_index_permission'));
		$aco_name="PurchaseOrders/crear";		
		$bool_purchase_order_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_purchase_order_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ThirdParty->id = $id;
		if (!$this->ThirdParty->exists()) {
			throw new NotFoundException(__('Invalid third party'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ThirdParty->delete()) {
			$this->Session->setFlash(__('The third party has been deleted.'));
		} else {
			$this->Session->setFlash(__('The third party could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	public function deleteClient($id = null) {
		$this->ThirdParty->id = $id;
		if (!$this->ThirdParty->exists()) {
			throw new NotFoundException(__('Invalid third party'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ThirdParty->delete()) {
			$this->Session->setFlash(__('The client has been deleted.'));
		} else {
			$this->Session->setFlash(__('The third party could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'resumenClientes'));
	}
	public function deleteProvider($id = null) {
		$this->ThirdParty->id = $id;
		if (!$this->ThirdParty->exists()) {
			throw new NotFoundException(__('Invalid third party'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ThirdParty->delete()) {
			$this->Session->setFlash(__('The provider has been deleted.'));
		} else {
			$this->Session->setFlash(__('The third party could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'resumenProveedores'));
	}

	public function reasignarClientes() {
		$this->loadModel('User');
		$this->loadModel('ThirdPartyUser');
		$this->loadModel('ThirdParty');
		$this->ThirdParty->recursive=-1;
		
		$originUserId=0;
		$boolKeepOrigin=true;
		$destinyUserArray=0;
		$clientsAssociatedWithUser=[];
		if ($this->request->is(['post', 'put'])) {
			//pr($this->request->data);
			$originUserId=$this->request->data['Reassign']['origin_user_id'];
			$boolKeepOrigin=$this->request->data['Reassign']['bool_keep_origin'];
			$destinyUserId=$this->request->data['Reassign']['destiny_user_id'];
			if (!empty($this->request->data['Reassign']['origin_user_id'])){
				$clientIdsAssociatedWithUser=$this->ThirdPartyUser->find('list',[
					'fields'=>['ThirdPartyUser.third_party_id'],
					'conditions'=>[
						'ThirdPartyUser.user_id'=>$originUserId,
            'ThirdPartyUser.bool_assigned'=>true,
					],
				]);
				//pr($clientIdsAssociatedWithUser);
				$clientsAssociatedWithUser=$this->ThirdParty->find('all',[
					'conditions'=>[
						'ThirdParty.id'=>$clientIdsAssociatedWithUser,
					],
					'order'=>'ThirdParty.company_name',
				]);	
			}
			if (empty($this->request->data['showclients'])){
				$currentDateTime=new DateTime();
				$datasource=$this->ThirdPartyUser->getDataSource();
				$datasource->begin();
				try {
					//pr($this->request->data);
					$this->ThirdPartyUser->recursive=-1;
					foreach ($this->request->data['Reassign']['Client'] as $clientId=>$clientValue){						
						if ($clientValue['selector']){
							if (!$boolKeepOrigin){
								$clientUserArray=[
                  'third_party_id'=>$clientId,
                  'user_id'=>$originUserId,
                  'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                  'bool_assigned'=>'0',
                ];
								$this->ThirdPartyUser->create();
								if (!$this->ThirdPartyUser->save($clientUserArray)){
									echo "Problema creando la asociación entre cliente y vendedor";
									pr($this->validateErrors($this->ThirdPartyUser));
									throw new Exception();
								}
							}
							foreach ($clientValue['target_user_id'] as $targetUserId){
								//if (empty($clientUserId)){
									$clientUserArray=[
                    'third_party_id'=>$clientId,
                    'user_id'=>$targetUserId,
                    'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                    'bool_assigned'=>true,
                  ];
									$this->ThirdPartyUser->create();
									if (!$this->ThirdPartyUser->save($clientUserArray)){
										echo "Problema creando la asociación entre cliente y vendedor";
										pr($this->validateErrors($this->ThirdPartyUser));
										throw new Exception();
									}
								//}
							}
						}
					}
					$datasource->commit();
					
					$this->recordUserAction(null,'reassignClients','clients');
					$this->recordUserActivity($this->Session->read('User.username'),"Se reasignaron clientes");
					$this->Session->setFlash(__('Se reasignaron los clientes.'),'default',['class' => 'success']);
					
					return $this->redirect(['action' => 'asociarClientesUsuarios']);
				} 
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podían reasignar los clientes.'), 'default',['class' => 'error-message']);
					$this->recordUserActivity($this->Session->read('User.username')," intentó reasignar clientes sin éxito");
				}
			}
		}
		$this->set(compact('originUserId'));
		$this->set(compact('boolKeepOrigin'));
		$this->set(compact('destinyUserArray'));
		$this->set(compact('clientsAssociatedWithUser'));
		
		$targetUsers=$originUsers = $destinyUsers= $users= $this->User->find('list',['fields'=>['User.username'],'order'=>'User.username ASC']);
		$this->set(compact('originUsers','destinyUsers','targetUsers','users'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Session->read('User.id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="Users/index";		
		$bool_user_index_permission=$this->hasPermission($this->Session->read('User.id'),$aco_name);
		$this->set(compact('bool_user_index_permission'));
		
	}
}
