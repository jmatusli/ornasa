<?php
App::uses('AppController', 'Controller');
/**
 * QuotationRemarks Controller
 *
 * @property QuotationRemark $QuotationRemark
 * @property PaginatorComponent $Paginator
 */
class QuotationRemarksController extends AppController {

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
		$this->QuotationRemark->recursive = -1;
		
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
		
		$quotationRemarkCount=	$this->QuotationRemark->find('count', array(
			'fields'=>array('QuotationRemark.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($quotationRemarkCount!=0?$quotationRemarkCount:1),
		);

		$quotationRemarks = $this->Paginator->paginate('QuotationRemark');
		$this->set(compact('quotationRemarks'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->QuotationRemark->exists($id)) {
			throw new NotFoundException(__('Invalid quotation remark'));
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
		$options = array('conditions' => array('QuotationRemark.' . $this->QuotationRemark->primaryKey => $id));
		$this->set('quotationRemark', $this->QuotationRemark->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->QuotationRemark->create();
			if ($this->QuotationRemark->save($this->request->data)) {
				$this->Session->setFlash(__('The quotation remark has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The quotation remark could not be saved. Please, try again.'));
			}
		}
		$users = $this->QuotationRemark->User->find('list');
		$quotations = $this->QuotationRemark->Quotation->find('list');
		$this->set(compact('users', 'quotations'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->QuotationRemark->exists($id)) {
			throw new NotFoundException(__('Invalid quotation remark'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->QuotationRemark->save($this->request->data)) {
				$this->Session->setFlash(__('The quotation remark has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The quotation remark could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('QuotationRemark.' . $this->QuotationRemark->primaryKey => $id));
			$this->request->data = $this->QuotationRemark->find('first', $options);
		}
		$users = $this->QuotationRemark->User->find('list');
		$quotations = $this->QuotationRemark->Quotation->find('list');
		$this->set(compact('users', 'quotations'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->QuotationRemark->id = $id;
		if (!$this->QuotationRemark->exists()) {
			throw new NotFoundException(__('Invalid quotation remark'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->QuotationRemark->delete()) {
			$this->Session->setFlash(__('The quotation remark has been deleted.'));
		} else {
			$this->Session->setFlash(__('The quotation remark could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
