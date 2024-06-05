<?php
App::uses('AppController', 'Controller');
/**
 * QuotationImages Controller
 *
 * @property QuotationImage $QuotationImage
 * @property PaginatorComponent $Paginator
 */
class QuotationImagesController extends AppController {

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
		$this->QuotationImage->recursive = -1;
		
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
		
		$quotationImageCount=	$this->QuotationImage->find('count', array(
			'fields'=>array('QuotationImage.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($quotationImageCount!=0?$quotationImageCount:1),
		);

		$quotationImages = $this->Paginator->paginate('QuotationImage');
		$this->set(compact('quotationImages'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->QuotationImage->exists($id)) {
			throw new NotFoundException(__('Invalid quotation image'));
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
		$options = array('conditions' => array('QuotationImage.' . $this->QuotationImage->primaryKey => $id));
		$this->set('quotationImage', $this->QuotationImage->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->QuotationImage->create();
			if ($this->QuotationImage->save($this->request->data)) {
				$this->Session->setFlash(__('The quotation image has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The quotation image could not be saved. Please, try again.'));
			}
		}
		$quotations = $this->QuotationImage->Quotation->find('list');
		$this->set(compact('quotations'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->QuotationImage->exists($id)) {
			throw new NotFoundException(__('Invalid quotation image'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->QuotationImage->save($this->request->data)) {
				$this->Session->setFlash(__('The quotation image has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The quotation image could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('QuotationImage.' . $this->QuotationImage->primaryKey => $id));
			$this->request->data = $this->QuotationImage->find('first', $options);
		}
		$quotations = $this->QuotationImage->Quotation->find('list');
		$this->set(compact('quotations'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->QuotationImage->id = $id;
		if (!$this->QuotationImage->exists()) {
			throw new NotFoundException(__('Invalid quotation image'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->QuotationImage->delete()) {
			$this->Session->setFlash(__('The quotation image has been deleted.'));
		} else {
			$this->Session->setFlash(__('The quotation image could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
