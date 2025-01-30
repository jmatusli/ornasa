<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class PurchaseEstimationsController extends AppController {

	public $components = array('Paginator');

	public $helpers = array('PhpExcel'); 

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('setduedate','getnewpurchaseestimationcode');		
	}
  
  public function getnewpurchaseestimationcode(){
		$this->layout= "ajax";
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		
		$clientid=trim($_POST['clientid']);
		$purchaseestimationdateday=trim($_POST['purchaseestimationdateday']);
		$purchaseestimationdatemonth=trim($_POST['purchaseestimationdatemonth']);
		$purchaseestimationdateyear=trim($_POST['purchaseestimationdateyear']);
		if (!$clientid){
			throw new NotFoundException(__('Cliente no presente'));
		}
		
    $purchaseEstimationDateString=$purchaseestimationdateday.'-'.$purchaseestimationdatemonth.'-'.$purchaseestimationdateyear;
		
		$newPurchaseEstimationCode=$this->PurchaseEstimation->getNewPurchaseEstimationCode($clientid,$purchaseEstimationDateString);
    
		return $newPurchaseEstimationCode;
	}
	
	public function index($clientId=0) {
		$this->PurchaseEstimation->recursive = -1;
		
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
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		$this->set(compact('clientId'));
    
    $conditions=[
      'PurchaseEstimation.purchase_estimation_date >='=> $startDate,
			'PurchaseEstimation.purchase_estimation_date <'=> $endDatePlusOne,
    ];
    if ($clientId>0){
      $conditions['PurchaseEstimation.client_id']=$clientId;
    }
    
		$purchaseEstimationCount=	$this->PurchaseEstimation->find('count', array(
			'fields'=>array('PurchaseEstimation.id'),
			'conditions' => $conditions
		));
    
		$this->Paginator->settings = array(
			'conditions' => $conditions,
			'contain'=>[
        'Client',
        'PurchaseEstimationProduct'
      ],
			'limit'=>($purchaseEstimationCount!=0?$purchaseEstimationCount:1),
		);

		$purchaseEstimations = $this->Paginator->paginate('PurchaseEstimation');
		$this->set(compact('purchaseEstimations'));
    
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
		$exportData=$_SESSION['resumenEstimaciones'];
		$this->set(compact('exportData'));
	}	
	
  public function view($id = null) {
		if (!$this->PurchaseEstimation->exists($id)) {
			throw new NotFoundException(__('Invalid purchase estimation'));
		}
    
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
		
    $purchaseEstimation=$this->PurchaseEstimation->find('first', [
      'conditions' => ['PurchaseEstimation.id' => $id],
      'contain'=>[
        'Client',
        'PurchaseEstimationProduct'=>[
          'Product',
          'RawMaterial',
          'ProductionResultCode'
        ]
      ]
    ]);
    $this->set(compact('purchaseEstimation'));
	}

	public function add() {
    $this->loadModel('PurchaseEstimationProduct');
		
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    $clientId=0;  
    if ($roleId == ROLE_CLIENT){
      $clientId=$this->Auth->User('client_id');
    } 
    
    $requestProducts=array();
    $subTotal=0;
		if ($this->request->is('post')) {
      $clientId=$this->request->data['PurchaseEstimation']['client_id'];
      $subTotal=$this->request->data['PurchaseEstimation']['subtotal_price'];
        
      $purchaseEstimationDateArray=$this->request->data['PurchaseEstimation']['purchase_estimation_date'];
			//pr($purchaseEstimationDateArray);
			$purchaseEstimationDateString=$purchaseEstimationDateArray['year'].'-'.$purchaseEstimationDateArray['month'].'-'.$purchaseEstimationDateArray['day'];
			$purchaseEstimationDate=date( "Y-m-d", strtotime($purchaseEstimationDateString));
			      
      if (empty($this->request->data['loadpurchases'])){
        $boolMultiplicationOK=true;
        foreach ($this->request->data['PurchaseEstimationProduct'] as $purchaseEstimationProduct){
          if ($purchaseEstimationProduct['product_id']>0){
            $requestProducts[]['PurchaseEstimationProduct']=$purchaseEstimationProduct;
            $multiplicationDifference=abs($purchaseEstimationProduct['product_total_price']-$purchaseEstimationProduct['product_quantity']*$purchaseEstimationProduct['product_unit_price']);
            //pr($purchaseEstimationProduct);
            if ($multiplicationDifference>=0.01){
              $boolMultiplicationOK='0';
            };
          }
        }
        
        if ($purchaseEstimationDateString>date('Y-m-d')){
          $this->Session->setFlash(__('La fecha de la estimación de compra no puede estar en el futuro!  No se guardó el pedido.'), 'default',array('class' => 'error-message'));
        }
        elseif (!$boolMultiplicationOK){
          $this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
        }
        else {				
          $datasource=$this->PurchaseEstimation->getDataSource();
          $datasource->begin();
          try {
            $this->PurchaseEstimation->create();
            if (!$this->PurchaseEstimation->save($this->request->data)) {
              echo "Problema guardando la estimación de compra";
                  pr($this->validateErrors($this->PurchaseEstimation));
                  throw new Exception();
              
            }
            $purchase_estimation_id=$this->PurchaseEstimation->id;
            foreach ($this->request->data['PurchaseEstimationProduct'] as $purchaseEstimationProduct){
              if ($purchaseEstimationProduct['product_id']>0){
                //pr($purchaseEstimationProduct);
                $productArray=array();
                $productArray['PurchaseEstimationProduct']['purchase_estimation_id']=$purchase_estimation_id;
                $productArray['PurchaseEstimationProduct']['product_id']=$purchaseEstimationProduct['product_id'];
                $productArray['PurchaseEstimationProduct']['raw_material_id']=$purchaseEstimationProduct['raw_material_id'];
                $productArray['PurchaseEstimationProduct']['production_result_code_id']=$purchaseEstimationProduct['production_result_code_id'];
                $productArray['PurchaseEstimationProduct']['product_quantity']=$purchaseEstimationProduct['product_quantity'];
                $productArray['PurchaseEstimationProduct']['product_unit_price']=$purchaseEstimationProduct['product_unit_price'];
                $productArray['PurchaseEstimationProduct']['product_total_price']=$purchaseEstimationProduct['product_total_price'];
                $productArray['PurchaseEstimationProduct']['currency_id']=CURRENCY_CS;
                $productArray['PurchaseEstimationProduct']['description']=$purchaseEstimationProduct['description'];
                $this->PurchaseEstimationProduct->create();
                if (!$this->PurchaseEstimationProduct->save($productArray)) {
                  echo "Problema guardando los productos de la estimación de compra del cliente";
                  pr($this->validateErrors($this->PurchaseEstimationProduct));
                  throw new Exception();
                }
              }
            }
                
            $datasource->commit();
            $this->recordUserAction($this->PurchaseEstimation->id,null,null);
            $this->recordUserActivity($this->Session->read('User.username'),"Se registró la estimación de compra número ".$this->request->data['PurchaseEstimation']['purchase_estimation_code']);
            
            $this->Session->setFlash(__('The purchase estimation has been saved.'),'default',array('class' => 'success'));				
            return $this->redirect(array('action' => 'index'));
          }
          catch(Exception $e){
            $datasource->rollback();
            // pr($e);
            $this->Session->setFlash(__('No se podía guardar la estimación de compra de cliente.'), 'default',array('class' => 'error-message'));
          }
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
    $productsAll = $this->Product->find('all',[
			'fields'=>'Product.id,Product.name',
      'conditions'=>[
        'Product.bool_active'=>true,
      ],
			'contain'=>[
				'ProductType'=>[
          'fields'=> ['id','product_category_id'],
        ],
        // 20180429 presence in stock is not an issue for the purchase estimations
				'StockItem'=>[
					'fields'=> ['raw_material_id'],
          'conditions'=>[
            'StockItem.bool_active'=>true,
            'StockItem.remaining_quantity >'=>0,
          ],
				],
			],
			'order'=>'product_type_id DESC, name ASC',
		]);
		
		$products = [];
		$rawmaterialids=[];
		foreach ($productsAll as $product){
			// only show products that are in inventory AT CURRENT DATE
      if ($product['ProductType']['product_category_id']!=CATEGORY_RAW){
        //pr($product['ProductType']);
        $products[$product['Product']['id']]=$product['Product']['name'];
      }
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){          
          //pr($stockitem['raw_material_id']);
          if (!in_array($stockitem['raw_material_id'],$rawmaterialids) && !empty($stockitem['raw_material_id'])){
            $rawmaterialids[]=$stockitem['raw_material_id'];
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
      'order'=>'Product.name',
		));
    //pr($preformasAll);
    
		$rawMaterials=array();
		foreach ($preformasAll as $preforma){
			$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
		}
    $this->set(compact('rawMaterials'));
    
    $newPurchaseEstimationCode=$this->PurchaseEstimation->getNewPurchaseEstimationCode($clientId);
    //pr($newPurchaseEstimationCode);
    $this->set(compact('newPurchaseEstimationCode'));
        
    $purchaseEstimation=$this->PurchaseEstimation->getPurchaseEstimation($clientId);
    //pr($purchaseEstimation);
    $this->set(compact('purchaseEstimation'));
    
	}

	public function edit($id = null) {
		if (!$this->PurchaseEstimation->exists($id)) {
			throw new NotFoundException(__('Invalid purchase estimation'));
		}
    
    $this->loadModel('PurchaseEstimationProduct');
		
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    $requestProducts=array();
		if ($this->request->is(array('post', 'put'))) {
      $clientId=$this->request->data['PurchaseEstimation']['client_id'];
      $subTotal=$this->request->data['PurchaseEstimation']['subtotal_price'];
        
      $purchaseEstimationDateArray=$this->request->data['PurchaseEstimation']['purchase_estimation_date'];
			//pr($purchaseEstimationDateArray);
			$purchaseEstimationDateString=$purchaseEstimationDateArray['year'].'-'.$purchaseEstimationDateArray['month'].'-'.$purchaseEstimationDateArray['day'];
			$purchaseEstimationDate=date( "Y-m-d", strtotime($purchaseEstimationDateString));
			
			$boolMultiplicationOK=true;
			foreach ($this->request->data['PurchaseEstimationProduct'] as $purchaseEstimationProduct){
				if ($purchaseEstimationProduct['product_id']>0){
					$requestProducts[]['PurchaseEstimationProduct']=$purchaseEstimationProduct;
					$multiplicationDifference=abs($purchaseEstimationProduct['product_total_price']-$purchaseEstimationProduct['product_quantity']*$purchaseEstimationProduct['product_unit_price']);
					//pr($purchaseEstimationProduct);
					if ($multiplicationDifference>=0.01){
						$boolMultiplicationOK='0';
					};
				}
			}
      if ($purchaseEstimationDateString>date('Y-m-d')){
				$this->Session->setFlash(__('La fecha de la estimación de compra no puede estar en el futuro!  No se guardó la estimación de compra.'), 'default',array('class' => 'error-message'));
			}
			elseif (!$boolMultiplicationOK){
				$this->Session->setFlash(__('Occurrió un problema al multiplicar el precio unitario con la cantidad.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
			}
			else {				
				$datasource=$this->PurchaseEstimation->getDataSource();
				$datasource->begin();
				try {
          $this->PurchaseEstimationProduct->recursive=-1;
					$previousPurchaseEstimationProducts=$this->PurchaseEstimationProduct->find('all',array(
						'fields'=>array('PurchaseEstimationProduct.id'),
						'conditions'=>array(
							'PurchaseEstimationProduct.purchase_estimation_id'=>$id,
						),
					));
					if (!empty($previousPurchaseEstimationProducts)){
						foreach ($previousPurchaseEstimationProducts as $previousPurchaseEstimationProduct){
							$this->PurchaseEstimationProduct->id=$previousPurchaseEstimationProduct['PurchaseEstimationProduct']['id'];
							$this->PurchaseEstimationProduct->delete($previousPurchaseEstimationProduct['PurchaseEstimationProduct']['id']);
						}
					}
          
          $this->PurchaseEstimation->id=$id;
          if (!$this->PurchaseEstimation->save($this->request->data)) {
            echo "Problema guardando la estimación de compra";
                pr($this->validateErrors($this->PurchaseEstimation));
                throw new Exception();
            
          }
          $purchase_estimation_id=$this->PurchaseEstimation->id;
					foreach ($this->request->data['PurchaseEstimationProduct'] as $purchaseEstimationProduct){
						if ($purchaseEstimationProduct['product_id']>0){
							//pr($purchaseEstimationProduct);
							$productArray=array();
							$productArray['PurchaseEstimationProduct']['purchase_estimation_id']=$purchase_estimation_id;
							$productArray['PurchaseEstimationProduct']['product_id']=$purchaseEstimationProduct['product_id'];
              $productArray['PurchaseEstimationProduct']['raw_material_id']=$purchaseEstimationProduct['raw_material_id'];
              $productArray['PurchaseEstimationProduct']['production_result_code_id']=$purchaseEstimationProduct['production_result_code_id'];
							$productArray['PurchaseEstimationProduct']['product_quantity']=$purchaseEstimationProduct['product_quantity'];
              $productArray['PurchaseEstimationProduct']['product_unit_price']=$purchaseEstimationProduct['product_unit_price'];
							$productArray['PurchaseEstimationProduct']['product_total_price']=$purchaseEstimationProduct['product_total_price'];
							$productArray['PurchaseEstimationProduct']['currency_id']=CURRENCY_CS;
              $productArray['PurchaseEstimationProduct']['description']=$purchaseEstimationProduct['description'];
							$this->PurchaseEstimationProduct->create();
							if (!$this->PurchaseEstimationProduct->save($productArray)) {
								echo "Problema guardando los productos de la estimación de compra del cliente";
								pr($this->validateErrors($this->PurchaseEstimationProduct));
								throw new Exception();
							}
						}
					}
    					
					$datasource->commit();
					$this->recordUserAction($this->PurchaseEstimation->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se editó la estimación de compra número ".$this->request->data['PurchaseEstimation']['purchase_estimation_code']);
					
					$this->Session->setFlash(__('Se modificó la estimación de compra.'),'default',array('class' => 'success'));				
					return $this->redirect(array('action' => 'index'));
				}
				catch(Exception $e){
					$datasource->rollback();
					// pr($e);
					$this->Session->setFlash(__('No se podía modificar la estimación de compra de cliente.'), 'default',array('class' => 'error-message'));
				}
			}
		} 
    else {
			$this->request->data = $this->PurchaseEstimation->find('first', [
        'conditions' => ['PurchaseEstimation.id' => $id],
        'contain'=>['PurchaseEstimationProduct']
      ]);
      
      for ($qp=0;$qp<count($this->request->data['PurchaseEstimationProduct']);$qp++){
				$requestProducts[]['PurchaseEstimationProduct']=$this->request->data['PurchaseEstimationProduct'][$qp];
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
					'fields'=> array('raw_material_id'),
          'conditions'=>array(
            'StockItem.bool_active'=>true,
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
      if ($product['ProductType']['product_category_id']!=CATEGORY_RAW){
        //pr($product['ProductType']);
        $products[$product['Product']['id']]=$product['Product']['name'];
      }
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){          
          //pr($stockitem['raw_material_id']);
          if (!in_array($stockitem['raw_material_id'],$rawmaterialids) && !empty($stockitem['raw_material_id'])){
            $rawmaterialids[]=$stockitem['raw_material_id'];
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
		$this->PurchaseEstimation->id = $id;
		if (!$this->PurchaseEstimation->exists()) {
			throw new NotFoundException(__('Invalid request'));
		}
		$this->request->allowMethod('post', 'delete');
		$this->PurchaseEstimation->recursive=-1;
		$purchaseEstimation=$this->PurchaseEstimation->find('first',array(
			'conditions'=>array(
				'PurchaseEstimation.id'=>$id,
			),
			'contain'=>array(
				'PurchaseEstimationProduct',
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
			$flashMessage.=" No se eliminó la estimación de compra.";
			$this->Session->setFlash($flashMessage, 'default',array('class' => 'error-message'));
			return $this->redirect(array('action' => 'view',$id));
		}
		else {
			$datasource=$this->PurchaseEstimation->getDataSource();
			$datasource->begin();	
			try {
				foreach ($purchaseEstimation['PurchaseEstimationProduct'] as $purchaseEstimationProduct){
					if (!$this->PurchaseEstimation->PurchaseEstimationProduct->delete($purchaseEstimationProduct['id'])) {
						echo "Problema al eliminar el producto de la estimación de compra";
						pr($this->validateErrors($this->PurchaseEstimation->PurchaseEstimationProduct));
						throw new Exception();
					}
				}
				if (!$this->PurchaseEstimation->delete($id)) {
					echo "Problema al eliminar la estimación de compra";
					pr($this->validateErrors($this->PurchaseEstimation));
					throw new Exception();
				}
						
				$datasource->commit();
					
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=array();
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$purchaseEstimation['PurchaseEstimation']['id'];
				$deletionArray['Deletion']['reference']=$purchaseEstimation['PurchaseEstimation']['purchase_estimation_code'];
				$deletionArray['Deletion']['type']='PurchaseEstimation';
				$this->Deletion->save($deletionArray);
				
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la estimación de compra número ".$purchaseEstimation['PurchaseEstimation']['purchase_estimation_code']);
						
				$this->Session->setFlash(__('Se eliminó la estimación de compra.'),'default',array('class' => 'success'));				
				return $this->redirect(array('action' => 'index'));
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar la estimación de compra.'), 'default',array('class' => 'error-message'));
				return $this->redirect(array('action' => 'view',$id));
			}
		}
		return $this->redirect(array('action' => 'index'));
	}
}
