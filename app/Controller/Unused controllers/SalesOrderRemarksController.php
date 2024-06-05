<?php
App::uses('AppController', 'Controller');
/**
 * SalesOrderRemarks Controller
 *
 * @property SalesOrderRemark $SalesOrderRemark
 * @property PaginatorComponent $Paginator
 */
class SalesOrderRemarksController extends AppController {

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
		$this->SalesOrderRemark->recursive = -1;
		
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
		
		$salesOrderRemarkCount=	$this->SalesOrderRemark->find('count', array(
			'fields'=>array('SalesOrderRemark.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($salesOrderRemarkCount!=0?$salesOrderRemarkCount:1),
		);

		$salesOrderRemarks = $this->Paginator->paginate('SalesOrderRemark');
		$this->set(compact('salesOrderRemarks'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->SalesOrderRemark->exists($id)) {
			throw new NotFoundException(__('Invalid sales order remark'));
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
		$options = array('conditions' => array('SalesOrderRemark.' . $this->SalesOrderRemark->primaryKey => $id));
		$this->set('salesOrderRemark', $this->SalesOrderRemark->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->SalesOrderRemark->create();
			if ($this->SalesOrderRemark->save($this->request->data)) {
				$this->Session->setFlash(__('The sales order remark has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sales order remark could not be saved. Please, try again.'));
			}
		}
		$users = $this->SalesOrderRemark->User->find('list');
		$salesOrders = $this->SalesOrderRemark->SalesOrder->find('list');
		$actionTypes = $this->SalesOrderRemark->ActionType->find('list');
		$this->set(compact('users', 'salesOrders', 'actionTypes'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->SalesOrderRemark->exists($id)) {
			throw new NotFoundException(__('Invalid sales order remark'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->SalesOrderRemark->save($this->request->data)) {
				$this->Session->setFlash(__('The sales order remark has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sales order remark could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('SalesOrderRemark.' . $this->SalesOrderRemark->primaryKey => $id));
			$this->request->data = $this->SalesOrderRemark->find('first', $options);
		}
		$users = $this->SalesOrderRemark->User->find('list');
		$salesOrders = $this->SalesOrderRemark->SalesOrder->find('list');
		$actionTypes = $this->SalesOrderRemark->ActionType->find('list');
		$this->set(compact('users', 'salesOrders', 'actionTypes'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->SalesOrderRemark->id = $id;
		if (!$this->SalesOrderRemark->exists()) {
			throw new NotFoundException(__('Invalid sales order remark'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->SalesOrderRemark->delete()) {
			$this->Session->setFlash(__('The sales order remark has been deleted.'));
		} else {
			$this->Session->setFlash(__('The sales order remark could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
