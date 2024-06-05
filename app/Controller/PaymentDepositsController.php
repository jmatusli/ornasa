<?php
App::uses('AppController', 'Controller');
/**
 * PaymentDeposits Controller
 *
 * @property PaymentDeposit $PaymentDeposit
 * @property PaginatorComponent $Paginator
 */
class PaymentDepositsController extends AppController {

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
		$this->PaymentDeposit->recursive = -1;
		
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
		
		$paymentDepositCount=	$this->PaymentDeposit->find('count', array(
			'fields'=>array('PaymentDeposit.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($paymentDepositCount!=0?$paymentDepositCount:1),
		);

		$paymentDeposits = $this->Paginator->paginate('PaymentDeposit');
		$this->set(compact('paymentDeposits'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->PaymentDeposit->exists($id)) {
			throw new NotFoundException(__('Invalid payment deposit'));
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
		$options = array('conditions' => array('PaymentDeposit.' . $this->PaymentDeposit->primaryKey => $id));
		$this->set('paymentDeposit', $this->PaymentDeposit->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->PaymentDeposit->create();
			if ($this->PaymentDeposit->save($this->request->data)) {
				$this->Session->setFlash(__('The payment deposit has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The payment deposit could not be saved. Please, try again.'));
			}
		}
		$cashReceipts = $this->PaymentDeposit->CashReceipt->find('list');
		$invoices = $this->PaymentDeposit->Invoice->find('list');
		$transfers = $this->PaymentDeposit->Transfer->find('list');
		$this->set(compact('cashReceipts', 'invoices', 'transfers'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->PaymentDeposit->exists($id)) {
			throw new NotFoundException(__('Invalid payment deposit'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->PaymentDeposit->save($this->request->data)) {
				$this->Session->setFlash(__('The payment deposit has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The payment deposit could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('PaymentDeposit.' . $this->PaymentDeposit->primaryKey => $id));
			$this->request->data = $this->PaymentDeposit->find('first', $options);
		}
		$cashReceipts = $this->PaymentDeposit->CashReceipt->find('list');
		$invoices = $this->PaymentDeposit->Invoice->find('list');
		$transfers = $this->PaymentDeposit->Transfer->find('list');
		$this->set(compact('cashReceipts', 'invoices', 'transfers'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->PaymentDeposit->id = $id;
		if (!$this->PaymentDeposit->exists()) {
			throw new NotFoundException(__('Invalid payment deposit'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->PaymentDeposit->delete()) {
			$this->Session->setFlash(__('The payment deposit has been deleted.'));
		} else {
			$this->Session->setFlash(__('The payment deposit could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
