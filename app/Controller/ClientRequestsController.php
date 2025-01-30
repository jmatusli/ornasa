<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ClientRequestsController extends AppController {

	public $components = array('Paginator');

	public $helpers = array('PhpExcel'); 

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('setduedate','getnewclientrequestcode');		
	}
  
  public function getnewclientrequestcode(){
		$this->layout= "ajax";
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		
		$clientid=trim($_POST['clientid']);
		$clientrequestdateday=trim($_POST['clientrequestdateday']);
		$clientrequestdatemonth=trim($_POST['clientrequestdatemonth']);
		$clientrequestdateyear=trim($_POST['clientrequestdateyear']);
		if (!$clientid){
			throw new NotFoundException(__('Cliente no presente'));
		}
		
    $requestDateString=$clientrequestdateday.'-'.$clientrequestdatemonth.'-'.$clientrequestdateyear;
		
		$newClientRequestCode=$this->ClientRequest->getNewClientRequestCode($clientid,$requestDateString);
    
		return $newClientRequestCode;
	}
	
	public function index($clientId=0) {
		$this->ClientRequest->recursive = -1;
		
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
  
    if ($roleId == ROLE_CLIENT){
      $clientId=$this->Auth->User('client_id');
    } 
  
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      if ($roleId==ROLE_ADMIN){
        $clientId=$this->request->data['Report']['client_id'];
      }
		}
		
		if (!isset($startDate)){
			$startDate = date("Y-01-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		$this->set(compact('clientId'));
    
    $conditions=[
      'ClientRequest.client_request_date >='=> $startDate,
			'ClientRequest.client_request_date <'=> $endDatePlusOne,
    ];
    if ($clientId>0){
      $conditions['ClientRequest.client_id']=$clientId;
    }
    
		$requestCount=	$this->ClientRequest->find('count', array(
			'fields'=>array('ClientRequest.id'),
			'conditions' => $conditions
		));
    
		$this->Paginator->settings = array(
			'conditions' => $conditions,
			'contain'=>[
        'Client',
        'ClientRequestProduct'
      ],
			'limit'=>($requestCount!=0?$requestCount:1),
		);

		$clientRequests = $this->Paginator->paginate('ClientRequest');
		$this->set(compact('clientRequests'));
    
    $this->loadModel('ThirdParty');
    $this->ThirdParty->recursive=-1;
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>[
        'ThirdParty.bool_provider' => false,
        'ThirdParty.bool_active' => true,
      ],
      'order'=>'company_name ASC',
    ]);
    $this->set(compact('clients'));
	}

  public function guardarResumen() {
		$exportData=$_SESSION['resumenPedidos'];
		$this->set(compact('exportData'));
	}	
	
  public function view($id = null) {
		if (!$this->ClientRequest->exists($id)) {
			throw new NotFoundException(__('Invalid request'));
		}
    
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
		
    $clientRequest=$this->ClientRequest->find('first', [
      'conditions' => ['ClientRequest.id' => $id],
      'contain'=>[
        'Client',
        'ClientRequestProduct'=>[
          'Product',
          'RawMaterial',
          'ProductionResultCode'
        ]
      ]
    ]);
    $this->set(compact('clientRequest'));
	}

	public function add() {
    $this->loadModel('ClientRequestProduct');
		
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    $clientId=0;  
    if ($roleId == ROLE_CLIENT){
      $clientId=$this->Auth->User('client_id');
    } 
    
    $requestProducts=array();
    $subTotal=0;
		if ($this->request->is('post')) {
      $clientId=$this->request->data['ClientRequest']['client_id'];
      $subTotal=$this->request->data['ClientRequest']['subtotal_price'];
        
      $clientRequestDateArray=$this->request->data['ClientRequest']['client_request_date'];
			//pr($clientRequestDateArray);
			$clientRequestDateString=$clientRequestDateArray['year'].'-'.$clientRequestDateArray['month'].'-'.$clientRequestDateArray['day'];
			$clientRequestDate=date( "Y-m-d", strtotime($clientRequestDateString));
			
			$boolMultiplicationOK=true;
			foreach ($this->request->data['ClientRequestProduct'] as $clientRequestProduct){
				if ($clientRequestProduct['product_id']>0){
					$requestProducts[]['ClientRequestProduct']=$clientRequestProduct;
					$multiplicationDifference=abs($clientRequestProduct['product_total_price']-$clientRequestProduct['product_quantity']*$clientRequestProduct['product_unit_price']);
					//pr($clientRequestProduct);
					if ($multiplicationDifference>=0.01){
						$boolMultiplicationOK='0';
					};
				}
			}
      if ($clientRequestDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha del pedido no puede estar en el futuro!  No se guardó el pedido.'), 'default',array('class' => 'error-message'));
			}
			elseif (!$boolMultiplicationOK){
				$this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
			}
			else {				
				$datasource=$this->ClientRequest->getDataSource();
				$datasource->begin();
				try {
          $this->ClientRequest->create();
          if (!$this->ClientRequest->save($this->request->data)) {
            echo "Problema guardando el pedido del cliente";
                pr($this->validateErrors($this->ClientRequest));
                throw new Exception();
            
          }
          $client_request_id=$this->ClientRequest->id;
					foreach ($this->request->data['ClientRequestProduct'] as $clientRequestProduct){
						if ($clientRequestProduct['product_id']>0){
							//pr($clientRequestProduct);
							$productArray=array();
							$productArray['ClientRequestProduct']['client_request_id']=$client_request_id;
							$productArray['ClientRequestProduct']['product_id']=$clientRequestProduct['product_id'];
              $productArray['ClientRequestProduct']['raw_material_id']=$clientRequestProduct['raw_material_id'];
              $productArray['ClientRequestProduct']['production_result_code_id']=$clientRequestProduct['production_result_code_id'];
							$productArray['ClientRequestProduct']['product_quantity']=$clientRequestProduct['product_quantity'];
              $productArray['ClientRequestProduct']['product_unit_price']=$clientRequestProduct['product_unit_price'];
							$productArray['ClientRequestProduct']['product_total_price']=$clientRequestProduct['product_total_price'];
							$productArray['ClientRequestProduct']['currency_id']=CURRENCY_CS;
              $productArray['ClientRequestProduct']['description']=$clientRequestProduct['description'];
							$this->ClientRequestProduct->create();
							if (!$this->ClientRequestProduct->save($productArray)) {
								echo "Problema guardando los productos del pedido del cliente";
								pr($this->validateErrors($this->ClientRequestProduct));
								throw new Exception();
							}
						}
					}
    					
					$datasource->commit();
					$this->recordUserAction($this->ClientRequest->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se registró el pedido número ".$this->request->data['ClientRequest']['client_request_code']);
					
					$this->Session->setFlash(__('The client request has been saved.'),'default',array('class' => 'success'));				
					return $this->redirect(array('action' => 'index'));
				}
				catch(Exception $e){
					$datasource->rollback();
					// pr($e);
					$this->Session->setFlash(__('No se podía guardar el pedido de cliente.'), 'default',array('class' => 'error-message'));
				}
			}
		}
		$this->set(compact('clientId'));
    $this->set(compact('subTotal'));
		$this->set(compact('requestProducts'));
    
    $this->loadModel('ThirdParty');
    $this->ThirdParty->recursive=-1;
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>[
        'ThirdParty.bool_provider' => false,
        'ThirdParty.bool_active' => true,
      ],
      'order'=>'company_name ASC',
    ]);
    $this->set(compact('clients'));
    
    $this->loadModel('Product');
    $productsAll = $this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
      'conditions'=>[
        'Product.bool_active'=>true,
      ],
			'contain'=>array(
				'ProductType'=>[
          'fields'=> ['id','product_category_id'],
        ],
				'StockItem'=>array(
					'fields'=> array('remaining_quantity','raw_material_id','warehouse_id'),
          'conditions'=>array(
            'StockItem.bool_active'=>true,
            //20180428 ADDED RESTRICTION ON QUANTITY
            'StockItem.remaining_quantity >'=>0,
          ),
				),
			),
			'order'=>'product_type_id DESC, name ASC',
		));
		
		$products = [];
		$rawmaterialids=[];
		foreach ($productsAll as $product){
			// only show products that are in inventory AT CURRENT DATE
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
					// in this case the associative array just contains the product_id because otherwise the list would become very long
					if ($stockitem['remaining_quantity']>0){
						//if (!empty($warehouseId)){
						//	if ($stockitem['warehouse_id']==$warehouseId){
						//		$products[$product['Product']['id']]=$product['Product']['name'];
            //    if (!in_array($stockitem['raw_material_id'],$rawmaterialids) && !empty($stockitem['raw_material_id'])){
            //      $rawmaterialids[]=$stockitem['raw_material_id'];
            //    }
						//	}
						//}
						//else {
              //20180421 ONLY SHOW PRODUCTS THAT ARE NOT RAW MATERIALS FOR CLIENT REQUESTS
              if ($product['ProductType']['product_category_id']!=CATEGORY_RAW){
                //pr($product['ProductType']);
                $products[$product['Product']['id']]=$product['Product']['name'];
              }
              //pr($stockitem['raw_material_id']);
              if (!in_array($stockitem['raw_material_id'],$rawmaterialids) && !empty($stockitem['raw_material_id'])){
                $rawmaterialids[]=$stockitem['raw_material_id'];
              }
						//}		
					}
				}
			}
		}
    //pr($products);
		$this->set(compact('products'));
    //pr($rawmaterialids);
    $this->Product->recursive=-1;
		$preformasAll = $this->Product->find('all',array(
      'fields'=>array('Product.id','Product.name'),
      'conditions' => [
        //'Product.product_type_id'=> $rawProductTypeIds,
        'Product.id'=>$rawmaterialids,
        'Product.bool_active'=>true
      ],
      //'contain'=>array(
      //	'ProductType',
      //	//'StockItem'=>array(
      //	//	'fields'=> array('remaining_quantity'),
      //  //  'conditions'=>array(
      //  //    'StockItem.bool_active'=>true,
      //  //  ),
      //	//),
      //),
      'order'=>'Product.name',
		));
    //pr($preformasAll);
    
		$rawMaterials=array();
		foreach ($preformasAll as $preforma){
			$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
		}
    $this->set(compact('rawMaterials'));
    
    $newClientRequestCode=$this->ClientRequest->getNewClientRequestCode($clientId);
    //pr($newClientRequestCode);
    $this->set(compact('newClientRequestCode'));
	}

	public function edit($id = null) {
		if (!$this->ClientRequest->exists($id)) {
			throw new NotFoundException(__('Invalid request'));
		}
    
    $this->loadModel('ClientRequestProduct');
		
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    $requestProducts=array();
		if ($this->request->is(array('post', 'put'))) {
      $clientId=$this->request->data['ClientRequest']['client_id'];
      $subTotal=$this->request->data['ClientRequest']['subtotal_price'];
        
      $clientRequestDateArray=$this->request->data['ClientRequest']['client_request_date'];
			//pr($clientRequestDateArray);
			$clientRequestDateString=$clientRequestDateArray['year'].'-'.$clientRequestDateArray['month'].'-'.$clientRequestDateArray['day'];
			$clientRequestDate=date( "Y-m-d", strtotime($clientRequestDateString));
			
			$boolMultiplicationOK=true;
			foreach ($this->request->data['ClientRequestProduct'] as $clientRequestProduct){
				if ($clientRequestProduct['product_id']>0){
					$requestProducts[]['ClientRequestProduct']=$clientRequestProduct;
					$multiplicationDifference=abs($clientRequestProduct['product_total_price']-$clientRequestProduct['product_quantity']*$clientRequestProduct['product_unit_price']);
					//pr($clientRequestProduct);
					if ($multiplicationDifference>=0.01){
						$boolMultiplicationOK='0';
					};
				}
			}
      if ($clientRequestDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha del pedido no puede estar en el futuro!  No se guardó el pedido.'), 'default',array('class' => 'error-message'));
			}
			elseif (!$boolMultiplicationOK){
				$this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
			}
			else {				
				$datasource=$this->ClientRequest->getDataSource();
				$datasource->begin();
				try {
          $this->ClientRequestProduct->recursive=-1;
					$previousClientRequestProducts=$this->ClientRequestProduct->find('all',array(
						'fields'=>array('ClientRequestProduct.id'),
						'conditions'=>array(
							'ClientRequestProduct.client_request_id'=>$id,
						),
					));
					if (!empty($previousClientRequestProducts)){
						foreach ($previousClientRequestProducts as $previousClientRequestProduct){
							$this->ClientRequestProduct->id=$previousClientRequestProduct['ClientRequestProduct']['id'];
							$this->ClientRequestProduct->delete($previousClientRequestProduct['ClientRequestProduct']['id']);
						}
					}
          
          $this->ClientRequest->id=$id;
          if (!$this->ClientRequest->save($this->request->data)) {
            echo "Problema guardando el pedido del cliente";
                pr($this->validateErrors($this->ClientRequest));
                throw new Exception();
            
          }
          $client_request_id=$this->ClientRequest->id;
					foreach ($this->request->data['ClientRequestProduct'] as $clientRequestProduct){
						if ($clientRequestProduct['product_id']>0){
							//pr($clientRequestProduct);
							$productArray=array();
							$productArray['ClientRequestProduct']['client_request_id']=$client_request_id;
							$productArray['ClientRequestProduct']['product_id']=$clientRequestProduct['product_id'];
              $productArray['ClientRequestProduct']['raw_material_id']=$clientRequestProduct['raw_material_id'];
              $productArray['ClientRequestProduct']['production_result_code_id']=$clientRequestProduct['production_result_code_id'];
							$productArray['ClientRequestProduct']['product_quantity']=$clientRequestProduct['product_quantity'];
              $productArray['ClientRequestProduct']['product_unit_price']=$clientRequestProduct['product_unit_price'];
							$productArray['ClientRequestProduct']['product_total_price']=$clientRequestProduct['product_total_price'];
							$productArray['ClientRequestProduct']['currency_id']=CURRENCY_CS;
              $productArray['ClientRequestProduct']['description']=$clientRequestProduct['description'];
							$this->ClientRequestProduct->create();
							if (!$this->ClientRequestProduct->save($productArray)) {
								echo "Problema guardando los productos del pedido del cliente";
								pr($this->validateErrors($this->ClientRequestProduct));
								throw new Exception();
							}
						}
					}
    					
					$datasource->commit();
					$this->recordUserAction($this->ClientRequest->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se editó el pedido número ".$this->request->data['ClientRequest']['client_request_code']);
					
					$this->Session->setFlash(__('Se modificó el pedido del cliente.'),'default',array('class' => 'success'));				
					return $this->redirect(array('action' => 'index'));
				}
				catch(Exception $e){
					$datasource->rollback();
					// pr($e);
					$this->Session->setFlash(__('No se podía modificar el pedido de cliente.'), 'default',array('class' => 'error-message'));
				}
			}
		} 
    else {
			$this->request->data = $this->ClientRequest->find('first', [
        'conditions' => ['ClientRequest.id' => $id],
        'contain'=>['ClientRequestProduct']
      ]);
      
      for ($qp=0;$qp<count($this->request->data['ClientRequestProduct']);$qp++){
				$requestProducts[]['ClientRequestProduct']=$this->request->data['ClientRequestProduct'][$qp];
			}
    }
		$this->set(compact('requestProducts'));
    
    $this->loadModel('ThirdParty');
    $this->ThirdParty->recursive=-1;
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>[
        'ThirdParty.bool_provider' => false,
        'ThirdParty.bool_active' => true,
      ],
      'order'=>'company_name ASC',
    ]);
    $this->set(compact('clients'));
    
    $this->loadModel('Product');
    $productsAll = $this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
      'conditions'=>[
        'Product.bool_active'=>true,
      ],
			'contain'=>array(
				'ProductType'=>[
          'fields'=> ['id','product_category_id'],
        ],
				'StockItem'=>array(
					'fields'=> array('remaining_quantity','raw_material_id','warehouse_id'),
          'conditions'=>array(
            'StockItem.bool_active'=>true,
            //20180428 ADDED RESTRICTION ON QUANTITY
            'StockItem.remaining_quantity >'=>0,
          ),
				),
			),
			'order'=>'product_type_id DESC, name ASC',
		));
		
		$products = [];
		$rawmaterialids=[];
		foreach ($productsAll as $product){
			// only show products that are in inventory AT CURRENT DATE
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
					// in this case the associative array just contains the product_id because otherwise the list would become very long
					if ($stockitem['remaining_quantity']>0){
            //20180421 ONLY SHOW PRODUCTS THAT ARE NOT RAW MATERIALS FOR CLIENT REQUESTS
            if ($product['ProductType']['product_category_id']!=CATEGORY_RAW){
              //pr($product['ProductType']);
              $products[$product['Product']['id']]=$product['Product']['name'];
            }
            //pr($stockitem['raw_material_id']);
            if (!in_array($stockitem['raw_material_id'],$rawmaterialids) && !empty($stockitem['raw_material_id'])){
              $rawmaterialids[]=$stockitem['raw_material_id'];
            }								
					}
				}
			}
		}
    //pr($products);
    foreach($requestProducts as $requestProduct){
      if (!array_key_exists($requestProduct['ClientRequestProduct']['product_id'],$products)){
        $linkedProduct=$this->Product->find('first',['conditions'=>['Product.id'=>$requestProduct['ClientRequestProduct']['product_id']]]);
        $products[$linkedProduct['Product']['id']]=$linkedProduct['Product']['name'];
        if (!in_array($requestProduct['ClientRequestProduct']['raw_material_id'],$rawmaterialids)){
          $rawmaterialids[]=$requestProduct['ClientRequestProduct']['raw_material_id'];
        }
      }
    }
    
		$this->set(compact('products'));
    //pr($rawmaterialids);
    $this->Product->recursive=-1;
		$preformasAll = $this->Product->find('all',array(
      'fields'=>array('Product.id','Product.name'),
      'conditions' => [
        'Product.id'=>$rawmaterialids,
        'Product.bool_active'=>true
      ],
      'order'=>'Product.name',
		));
    //pr($preformasAll);
    
		$rawMaterials=array();
		foreach ($preformasAll as $preforma){
			$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
		}
    $this->set(compact('rawMaterials'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ClientRequest->id = $id;
		if (!$this->ClientRequest->exists()) {
			throw new NotFoundException(__('Invalid request'));
		}
		$this->request->allowMethod('post', 'delete');
		$this->ClientRequest->recursive=-1;
		$clientRequest=$this->ClientRequest->find('first',array(
			'conditions'=>array(
				'ClientRequest.id'=>$id,
			),
			'contain'=>array(
				'ClientRequestProduct',
        //'SalesOrder'=>array(
				//	'conditions'=>array(
				//		'SalesOrder.bool_annulled'=>'0',
				//	),
				//),
			)
		));
		$flashMessage="";
		$boolDeletionAllowed=true;
		/*
		if (count($quotation['SalesOrder'])>0){
			$boolDeletionAllowed='0';
			$flashMessage.="Esta cotización tiene ordenes de venta correspondientes.  Para poder eliminar la cotización, primero hay que eliminar o modificar las ordenes de venta ";
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
    */
		if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó el pedido.";
			$this->Session->setFlash($flashMessage, 'default',array('class' => 'error-message'));
			return $this->redirect(array('action' => 'view',$id));
		}
		else {
			$datasource=$this->ClientRequest->getDataSource();
			$datasource->begin();	
			try {
				foreach ($clientRequest['ClientRequestProduct'] as $clientRequestProduct){
					if (!$this->ClientRequest->ClientRequestProduct->delete($clientRequestProduct['id'])) {
						echo "Problema al eliminar el producto del pedido";
						pr($this->validateErrors($this->ClientRequest->ClientRequestProduct));
						throw new Exception();
					}
				}
				if (!$this->ClientRequest->delete($id)) {
					echo "Problema al eliminar el pedido";
					pr($this->validateErrors($this->ClientRequest));
					throw new Exception();
				}
						
				$datasource->commit();
					
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=array();
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$clientRequest['ClientRequest']['id'];
				$deletionArray['Deletion']['reference']=$clientRequest['ClientRequest']['client_request_code'];
				$deletionArray['Deletion']['type']='ClientRequest';
				$this->Deletion->save($deletionArray);
				
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó el pedido número ".$clientRequest['ClientRequest']['client_request_code']);
						
				$this->Session->setFlash(__('Se eliminó el pedido.'),'default',array('class' => 'success'));				
				return $this->redirect(array('action' => 'index'));
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar el pedido.'), 'default',array('class' => 'error-message'));
				return $this->redirect(array('action' => 'view',$id));
			}
		}
		return $this->redirect(array('action' => 'index'));
	}
}
