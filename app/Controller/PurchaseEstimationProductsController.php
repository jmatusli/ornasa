<?php
App::uses('AppController', 'Controller');
/**
 * RequestProducts Controller
 *
 * @property RequestProduct $RequestProduct
 * @property PaginatorComponent $Paginator
 */
class PurchaseEstimationProductsController extends AppController {

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
		$this->PurchaseEstimationProduct->recursive = -1;
		
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
		
		$clientRequestProductCount=	$this->PurchaseEstimationProduct->find('count', array(
			'fields'=>array('PurchaseEstimationProduct.id'),
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

		$clientRequestProducts = $this->Paginator->paginate('PurchaseEstimationProduct');
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
		if (!$this->PurchaseEstimationProduct->exists($id)) {
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
		$options = array('conditions' => array('PurchaseEstimationProduct.' . $this->PurchaseEstimationProduct->primaryKey => $id));
		$this->set('clientRequestProduct', $this->PurchaseEstimationProduct->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->PurchaseEstimationProduct->create();
			if ($this->PurchaseEstimationProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The request product has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The request product could not be saved. Please, try again.'));
			}
		}
		$requests = $this->PurchaseEstimationProduct->Request->find('list');
		$products = $this->PurchaseEstimationProduct->Product->find('list');
		$productionResultCodes = $this->PurchaseEstimationProduct->ProductionResultCode->find('list');
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
		if (!$this->PurchaseEstimationProduct->exists($id)) {
			throw new NotFoundException(__('Invalid request product'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->PurchaseEstimationProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The request product has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The request product could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('PurchaseEstimationProduct.' . $this->PurchaseEstimationProduct->primaryKey => $id));
			$this->request->data = $this->PurchaseEstimationProduct->find('first', $options);
		}
		$requests = $this->PurchaseEstimationProduct->Request->find('list');
		$products = $this->PurchaseEstimationProduct->Product->find('list');
		$productionResultCodes = $this->PurchaseEstimationProduct->ProductionResultCode->find('list');
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
		$this->PurchaseEstimationProduct->id = $id;
		if (!$this->PurchaseEstimationProduct->exists()) {
			throw new NotFoundException(__('Invalid request product'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->PurchaseEstimationProduct->delete()) {
			$this->Session->setFlash(__('The request product has been deleted.'));
		} else {
			$this->Session->setFlash(__('The request product could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
