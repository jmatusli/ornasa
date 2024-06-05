<?php
App::uses('AppController', 'Controller');
/**
 * SalesOrderProductStatuses Controller
 *
 * @property SalesOrderProductStatus $SalesOrderProductStatus
 * @property PaginatorComponent $Paginator
 */
class SalesOrderProductStatusesController extends AppController {

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
		$this->SalesOrderProductStatus->recursive = -1;
		
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
		
		$salesOrderProductStatusCount=	$this->SalesOrderProductStatus->find('count', array(
			'fields'=>array('SalesOrderProductStatus.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($salesOrderProductStatusCount!=0?$salesOrderProductStatusCount:1),
		);

		$salesOrderProductStatuses = $this->Paginator->paginate('SalesOrderProductStatus');
		$this->set(compact('salesOrderProductStatuses'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->SalesOrderProductStatus->exists($id)) {
			throw new NotFoundException(__('Invalid sales order product status'));
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
		$options = array('conditions' => array('SalesOrderProductStatus.' . $this->SalesOrderProductStatus->primaryKey => $id));
		$this->set('salesOrderProductStatus', $this->SalesOrderProductStatus->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->SalesOrderProductStatus->create();
			if ($this->SalesOrderProductStatus->save($this->request->data)) {
				$this->Session->setFlash(__('The sales order product status has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sales order product status could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->SalesOrderProductStatus->exists($id)) {
			throw new NotFoundException(__('Invalid sales order product status'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->SalesOrderProductStatus->save($this->request->data)) {
				$this->Session->setFlash(__('The sales order product status has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sales order product status could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('SalesOrderProductStatus.' . $this->SalesOrderProductStatus->primaryKey => $id));
			$this->request->data = $this->SalesOrderProductStatus->find('first', $options);
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
		$this->SalesOrderProductStatus->id = $id;
		if (!$this->SalesOrderProductStatus->exists()) {
			throw new NotFoundException(__('Invalid sales order product status'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->SalesOrderProductStatus->delete()) {
			$this->Session->setFlash(__('The sales order product status has been deleted.'));
		} else {
			$this->Session->setFlash(__('The sales order product status could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
