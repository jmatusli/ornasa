<?php
App::uses('AppController', 'Controller');
/**
 * QuotationProducts Controller
 *
 * @property QuotationProduct $QuotationProduct
 * @property PaginatorComponent $Paginator
 */
class QuotationProductsController extends AppController {

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
		$this->QuotationProduct->recursive = -1;
		
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
		
		$quotationProductCount=	$this->QuotationProduct->find('count', array(
			'fields'=>array('QuotationProduct.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($quotationProductCount!=0?$quotationProductCount:1),
		);

		$quotationProducts = $this->Paginator->paginate('QuotationProduct');
		$this->set(compact('quotationProducts'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->QuotationProduct->exists($id)) {
			throw new NotFoundException(__('Invalid quotation product'));
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
		$options = array('conditions' => array('QuotationProduct.' . $this->QuotationProduct->primaryKey => $id));
		$this->set('quotationProduct', $this->QuotationProduct->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->QuotationProduct->create();
			if ($this->QuotationProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The quotation product has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The quotation product could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$quotations = $this->QuotationProduct->Quotation->find('list');
		$products = $this->QuotationProduct->Product->find('list');
		$currencies = $this->QuotationProduct->Currency->find('list');
		$this->set(compact('quotations', 'products', 'currencies'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->QuotationProduct->exists($id)) {
			throw new NotFoundException(__('Invalid quotation product'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->QuotationProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The quotation product has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The quotation product could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('QuotationProduct.' . $this->QuotationProduct->primaryKey => $id));
			$this->request->data = $this->QuotationProduct->find('first', $options);
		}
		$quotations = $this->QuotationProduct->Quotation->find('list');
		$products = $this->QuotationProduct->Product->find('list');
		$currencies = $this->QuotationProduct->Currency->find('list');
		$this->set(compact('quotations', 'products', 'currencies'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->QuotationProduct->id = $id;
		if (!$this->QuotationProduct->exists()) {
			throw new NotFoundException(__('Invalid quotation product'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->QuotationProduct->delete()) {
			$this->Session->setFlash(__('The quotation product has been deleted.'));
		} else {
			$this->Session->setFlash(__('The quotation product could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
