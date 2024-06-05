<?php
App::uses('AppController', 'Controller');
/**
 * RequestProducts Controller
 *
 * @property RequestProduct $RequestProduct
 * @property PaginatorComponent $Paginator
 */
class ClientRequestProductsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->ClientRequestProduct->recursive = -1;
		
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
		
		$clientRequestProductCount=	$this->ClientRequestProduct->find('count', array(
			'fields'=>array('ClientRequestProduct.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($clientRequestProductCount!=0?$clientRequestProductCount:1),
		);

		$clientRequestProducts = $this->Paginator->paginate('ClientRequestProduct');
		$this->set(compact('clientRequestProducts'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->ClientRequestProduct->exists($id)) {
			throw new NotFoundException(__('Invalid request product'));
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
		$options = array('conditions' => array('ClientRequestProduct.' . $this->ClientRequestProduct->primaryKey => $id));
		$this->set('clientRequestProduct', $this->ClientRequestProduct->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->ClientRequestProduct->create();
			if ($this->ClientRequestProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The request product has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The request product could not be saved. Please, try again.'));
			}
		}
		$requests = $this->ClientRequestProduct->Request->find('list');
		$products = $this->ClientRequestProduct->Product->find('list');
		$productionResultCodes = $this->ClientRequestProduct->ProductionResultCode->find('list');
		$this->set(compact('requests', 'products', 'productionResultCodes'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->ClientRequestProduct->exists($id)) {
			throw new NotFoundException(__('Invalid request product'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ClientRequestProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The request product has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The request product could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ClientRequestProduct.' . $this->ClientRequestProduct->primaryKey => $id));
			$this->request->data = $this->ClientRequestProduct->find('first', $options);
		}
		$requests = $this->ClientRequestProduct->Request->find('list');
		$products = $this->ClientRequestProduct->Product->find('list');
		$productionResultCodes = $this->ClientRequestProduct->ProductionResultCode->find('list');
		$this->set(compact('requests', 'products', 'productionResultCodes'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ClientRequestProduct->id = $id;
		if (!$this->ClientRequestProduct->exists()) {
			throw new NotFoundException(__('Invalid request product'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ClientRequestProduct->delete()) {
			$this->Session->setFlash(__('The request product has been deleted.'));
		} else {
			$this->Session->setFlash(__('The request product could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
