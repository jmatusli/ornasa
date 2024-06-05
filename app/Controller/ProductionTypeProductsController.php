<?php
App::uses('AppController', 'Controller');
/**
 * ProductionTypeProducts Controller
 *
 * @property ProductionTypeProduct $ProductionTypeProduct
 * @property PaginatorComponent $Paginator
 */
class ProductionTypeProductsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

public function resumen() {
		$this->ProductionTypeProduct->recursive = -1;
		
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
		
		$productionTypeProductCount=	$this->ProductionTypeProduct->find('count', [
			'fields'=>['ProductionTypeProduct.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($productionTypeProductCount!=0?$productionTypeProductCount:1),
		] ;

		$productionTypeProducts = $this->Paginator->paginate('ProductionTypeProduct');
		$this->set(compact('productionTypeProducts'));
	}

	public function detalle($id = null) {
		if (!$this->ProductionTypeProduct->exists($id)) {
			throw new NotFoundException(__('Invalid production type product'));
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
        'ProductionTypeProduct.id' => $id,
      ],
    ];
		$this->set('productionTypeProduct', $this->ProductionTypeProduct->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->ProductionTypeProduct->create();
			if ($this->ProductionTypeProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The production type product has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
				$this->Session->setFlash(__('The production type product could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
		$productionTypes = $this->ProductionTypeProduct->ProductionType->find('list');
		$products = $this->ProductionTypeProduct->Product->find('list');
		$this->set(compact('productionTypes', 'products'));
	}


	public function edit($id = null) {
		if (!$this->ProductionTypeProduct->exists($id)) {
			throw new NotFoundException(__('Invalid production type product'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->ProductionTypeProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The production type product has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} else {
				$this->Session->setFlash(__('The production type product could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('ProductionTypeProduct.id' => $id));
			$this->request->data = $this->ProductionTypeProduct->find('first', $options);
		}
		$productionTypes = $this->ProductionTypeProduct->ProductionType->find('list');
		$products = $this->ProductionTypeProduct->Product->find('list');
		$this->set(compact('productionTypes', 'products'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ProductionTypeProduct->id = $id;
		if (!$this->ProductionTypeProduct->exists()) {
			throw new NotFoundException(__('Invalid production type product'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProductionTypeProduct->delete()) {
			$this->Session->setFlash(__('The production type product has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The production type product could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
