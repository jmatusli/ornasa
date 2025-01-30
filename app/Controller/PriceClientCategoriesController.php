<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class PriceClientCategoriesController extends AppController {

	public $components = ['Paginator','RequestHandler'];
	public $helpers = ['PhpExcel'];

	public function resumen() {
		$this->PriceClientCategory->recursive = -1;
		
		$priceClientCategoryCount=	$this->PriceClientCategory->find('count', [
			'fields'=>['PriceClientCategory.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [	
			],
			'contain'=>[				
			],
			'limit'=>($priceClientCategoryCount!=0?$priceClientCategoryCount:1),
		];

		$priceClientCategories = $this->Paginator->paginate('PriceClientCategory');
		$this->set(compact('priceClientCategories'));
	}

  public function asociarClientesCategoriasDePrecio(){
    $this->loadModel('ThirdParty');
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    /*
    if ($this->request->is('post')) {
      $priceDateTimeArray=$this->request->data['ProductPriceLog']['price_datetime'];
      $priceDateTimeAsString=$this->ProductPriceLog->deconstruct('price_datetime',$this->request->data['ProductPriceLog']['price_datetime']);
      $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString));  
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
    */
    
    $priceClientCategories=$this->PriceClientCategory->find('list',[
      'order'=>'category_number ASC',
    ]);
    $this->set(compact('priceClientCategories'));
    
    $clients=$this->ThirdParty->getActiveClientList();
    $this->set(compact('clients'));
    
    if ($this->request->is('post')) {	      
      $datasource=$this->ThirdParty->getDataSource();
      $datasource->begin();
        
      try {
        foreach ($this->request->data['Client'] as $clientId=>$clientData){
          //pr($clientData);
          $clientArray=[
            'id'=>$clientId,
            'price_client_category_id'=>$clientData['PriceClientCategory']
          ];
          $this->ThirdParty->id=$clientId;
          //pr($clientArray);
          if (!$this->ThirdParty->save($clientArray)) {
            echo "Problema guardando la categoría de precio para el cliente ".$clientId;
            pr($this->validateErrors($this->ProductPriceLog));
            throw new Exception();
          } 
        }                          
        $datasource->commit();
        $this->recordUserAction();
        // SAVE THE USERLOG 
        $this->recordUserActivity($this->Session->read('User.username'),"Se registraron las categorías de precio para todos clientes");
        $this->Session->setFlash('Se registraron las categorías de precio para los clientes','default',['class' => 'success']);
        $boolSaved=true;
      }
      catch(Exception $e){
        $datasource->rollback();
        pr($e);
        $this->Session->setFlash("No se podían asociar los clientes con las categorías de precio", 'default',['class' => 'error-message']);
      }
    }
    
    $clientCategories=$this->ThirdParty->find('list',[
      'fields'=>['ThirdParty.id','ThirdParty.price_client_category_id'],
      'conditions'=>[
        'ThirdParty.bool_active'=>true,
        'ThirdParty.bool_provider'=>'0',
      ],      
      'order'=>'ThirdParty.company_name ASC',
    ]);
    $this->set(compact('clientCategories'));
    //$aco_name="ProductPriceLogs/registrarPreciosCliente";		
		//$boolRegistrarPreciosCliente=$this->hasPermission($this->Auth->User('id'),$aco_name);
		//$this->set(compact('boolRegistrarPreciosCliente'));
  }
  
  public function guardarAsociacionesClientesCategoriasDePrecio($fileName) {
		$exportData=$_SESSION['asociacionesClientesCategoriasDePrecio'];
		$this->set(compact('exportData','fileName'));
	}

	public function detalle($id = null) {
		if (!$this->PriceClientCategory->exists($id)) {
			throw new NotFoundException(__('Invalid price client category'));
		}
		
		$options = ['conditions' => ['PriceClientCategory.id' => $id]];
		$this->set('priceClientCategory', $this->PriceClientCategory->find('first', $options));
    
    $aco_name="PriceClientCategories/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->PriceClientCategory->create();
			if ($this->PriceClientCategory->save($this->request->data)) {
				$this->Session->setFlash('Se guardó la categoría de precio de clientes.','default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} else {
				$this->Session->setFlash('No se podía grabar la categoría de precio de clientes.','default',['class' => 'error-message']);
			}
		}
	}

	public function editar($id = null) {
		if (!$this->PriceClientCategory->exists($id)) {
			throw new NotFoundException(__('Invalid price client category'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->PriceClientCategory->save($this->request->data)) {
				$this->Session->setFlash('Se guardó la categoría de precio de clientes.','default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} else {
				$this->Session->setFlash('No se podía grabar la categoría de precio de clientes.','default',['class' => 'error-message']);
			}
		} else {
			$options = ['conditions' => ['PriceClientCategory.id' => $id]];
			$this->request->data = $this->PriceClientCategory->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->PriceClientCategory->id = $id;
		if (!$this->PriceClientCategory->exists()) {
			throw new NotFoundException(__('Invalid price client category'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->PriceClientCategory->delete()) {
			$this->Session->setFlash('Se eliminó la categoría de precio de clientes.','default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash('No se podía eliminar la categóría de precios de clientes','default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
