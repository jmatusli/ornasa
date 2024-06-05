<?php
App::uses('AppController', 'Controller');
/**
 * WarehouseProducts Controller
 *
 * @property WarehouseProduct $WarehouseProduct
 * @property PaginatorComponent $Paginator
 */
class WarehouseProductsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

public function resumen() {
		$this->WarehouseProduct->recursive = -1;
		
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
		
		$warehouseProductCount=	$this->WarehouseProduct->find('count', [
			'fields'=>['WarehouseProduct.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($warehouseProductCount!=0?$warehouseProductCount:1),
		] ;

		$warehouseProducts = $this->Paginator->paginate('WarehouseProduct');
		$this->set(compact('warehouseProducts'));
	}

	public function detalle($id = null) {
		if (!$this->WarehouseProduct->exists($id)) {
			throw new NotFoundException(__('Invalid warehouse product'));
		}
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
		$options = [
      'conditions' => [
        'WarehouseProduct.id' => $id,
      ],
    ];
		$this->set('warehouseProduct', $this->WarehouseProduct->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->WarehouseProduct->create();
			if ($this->WarehouseProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The warehouse product has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
				$this->Session->setFlash(__('The warehouse product could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
		$warehouses = $this->WarehouseProduct->Warehouse->find('list');
		$products = $this->WarehouseProduct->Product->find('list');
		$this->set(compact('warehouses', 'products'));
	}


	public function edit($id = null) {
		if (!$this->WarehouseProduct->exists($id)) {
			throw new NotFoundException(__('Invalid warehouse product'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->WarehouseProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The warehouse product has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} else {
				$this->Session->setFlash(__('The warehouse product could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('WarehouseProduct.id' => $id));
			$this->request->data = $this->WarehouseProduct->find('first', $options);
		}
		$warehouses = $this->WarehouseProduct->Warehouse->find('list');
		$products = $this->WarehouseProduct->Product->find('list');
		$this->set(compact('warehouses', 'products'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->WarehouseProduct->id = $id;
		if (!$this->WarehouseProduct->exists()) {
			throw new NotFoundException(__('Invalid warehouse product'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->WarehouseProduct->delete()) {
			$this->Session->setFlash(__('The warehouse product has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The warehouse product could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
