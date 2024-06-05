<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class VehiclesController extends AppController {


	public $components = ['Paginator'];
  public $helpers = ['PhpExcel'];
  
  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('resumen','detalle','crear','editar','editar');		
	}
	
  
  public function resumen() {
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
		$this->Vehicle->recursive = -1;
    
    $loggedUserId=$userId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $warehouseId=0;
    
    if ($this->request->is('post')) {
    /*
      $startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
    */  
      
			$warehouseId=$this->request->data['Report']['warehouse_id'];
		}
		
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
    
    $warehouseVehicles=[];
    
    foreach ($warehouses as $warehouseId=>$warehouseName){
      $warehouseVehicles[$warehouseId]['Warehouse']['name']=$warehouseName;
      $conditions=['Vehicle.warehouse_id'=>$warehouseId];
      $vehicleCount=	$this->Vehicle->find('count', [
        'fields'=>['Vehicle.id'],
        'conditions' => $conditions,
      ]);
      
      $this->Paginator->settings = [
        'conditions' => $conditions,
        'contain'=>[				
        ],
        'recursive'=>-1,
        'order'=>'Vehicle.list_order',
        'limit'=>($vehicleCount!=0?$vehicleCount:1),
      ] ;

      $warehouseVehicles[$warehouseId]['Vehicle'] = $this->Paginator->paginate('Vehicle');
    }
		$this->set(compact('warehouseVehicles'));
	}

	public function detalle($id = null) {
		if (!$this->Vehicle->exists($id)) {
			throw new NotFoundException(__('Invalid vehicle'));
		}
    /*
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		*/
    $options = [
      'conditions' => [
        'Vehicle.id' => $id,
      ],
    ];
		$this->set('vehicle', $this->Vehicle->find('first', $options));
	}

	public function crear() {
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $warehouseId=0;
    
    if ($this->request->is('post')) {
      $warehouseId=$this->request->data['Vehicle']['warehouse_id'];
    
			$this->Vehicle->create();
			if ($this->Vehicle->save($this->request->data)) {
				$this->Session->setFlash(__('The vehicle has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
				$this->Session->setFlash(__('The vehicle could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
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
	}


	public function editar($id = null) {
    if (!$this->Vehicle->exists($id)) {
			throw new NotFoundException(__('Invalid vehicle'));
		}
		
    $this->loadModel('Warehouse');
    $this->loadModel('UserWarehouse');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $warehouseId=0;
		if ($this->request->is(['post', 'put'])) {
      $warehouseId=$this->request->data['Vehicle']['warehouse_id'];
      
			if ($this->Vehicle->save($this->request->data)) {
				$this->Session->setFlash(__('The vehicle has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} else {
				$this->Session->setFlash(__('The vehicle could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('Vehicle.id' => $id));
			$this->request->data = $this->Vehicle->find('first', $options);
		}
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
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Vehicle->id = $id;
		if (!$this->Vehicle->exists()) {
			throw new NotFoundException(__('Invalid vehicle'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Vehicle->delete()) {
			$this->Session->setFlash(__('The vehicle has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The vehicle could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
